<?php
/*********************************************************************************
 *
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 *
 ********************************************************************************/


/**
 * @package Core
 */
class Authentication {
	protected $name = 'SessionID';
	protected $idle_timeout = null; //Max IDLE time
	protected $expire_session;      //When TRUE, cookie is expired when browser closes.
	protected $type_id = 0;       //Default Pre-Login rather than 800 or something, as it has the potential to accidently promote sessions to something higher than it should be.
	protected $mfa_type_id = 0;
	protected $end_point_id = null;
	protected $client_id = null;
	protected $object_id = null;
	protected $session_id = null;
	protected $ip_address = null;
	protected $user_agent = null;
	protected $flags = null;
	protected $created_date = null;
	protected $updated_date = null;
	protected $reauthenticated_date = null;
	protected $other_json = [];
	protected $mfa_start_listen = false;
	public $mfa_timeout_seconds = 900; //15 minutes

	//SmallINT datatype, max of 32767
	protected $session_type_map = [
			'pending_authentication' => 0,
			//
			//Non-Users.
			//
			'job_applicant'          => 100,
			'client_contact'         => 110,

			//
			//Users
			//

			//Other hardware.
			'ibutton'                => 500,
			'barcode'                => 510,
			'finger_print'           => 520,

			//QuickPunch
			'quick_punch_id'         => 600,
			'phone_id'               => 605, //This used to have to be 800 otherwise the Desktop PC app and touch-tone AGI scripts would fail, however that should be resolved now with changes to soap/server.php
			'client_pc'              => 610,

			'api_key'                => 700, //API key created after user_name/password authentication. This should be below any methods that use user_name/password to authenticate each time they login.

			//SSO or alternative methods
			'http_auth'              => 705,
			'sso'                    => 710,

			//Username/Passwords including multifactor.
			'user_name'             => 800,
			'user_name_multifactor' => 810,
	];

	protected $obj = null;
	protected $rl = null;

	/**
	 * @var Cache_Lite_Function|Cache_Lite_Output
	 */
	protected $cache;
	protected $db;

	/**
	 * Authentication constructor.
	 */
	function __construct() {
		global $db, $cache;

		$this->db = $db;
		$this->cache = $cache;

		return true;
	}

	/**
	 * Retrieves the RateLimit object associated with the current user's session.
	 * If the RateLimit object does not exist, it initializes a new one.
	 *
	 * The RateLimit object is used to control the number of requests a user can make
	 * within a certain timeframe, preventing abuse of the system's resources.
	 *
	 * @return RateLimit The RateLimit object for the current session.
	 * @throws ReflectionException If the RateLimit class does not exist or cannot be instantiated.
	 */
	function getRateLimitObject() {
		if ( is_object( $this->rl ) ) {
			return $this->rl;
		} else {
			$this->rl = TTNew( 'RateLimit' );
			$this->rl->setID( 'authentication_' . Misc::getRemoteIPAddress() );
			$this->rl->setAllowedCalls( 20 );
			$this->rl->setTimeFrame( 900 ); //15 minutes

			return $this->rl;
		}
	}

	/**
	 * Resolves a type ID to its associated session cookie name.
	 * Accepts either a numeric type ID or a string identifier, converting the latter to a numeric ID.
	 * Differentiates session types to maintain separate sessions for distinct application parts.
	 *
	 * @param int|string $type_id The type ID or its string identifier.
	 * @return string|false The session cookie name or false if unrecognized.
	 */
	function getNameByTypeId( $type_id ) {
		// Check if the provided type_id is numeric, if not, resolve it to a numeric type ID
		if ( !is_numeric( $type_id ) ) {
			$type_id = $this->getTypeIDByName( $type_id );
		}

		//Seperate session cookie names so if the user logs in with QuickPunch it doesn't log them out of the full interface for example.
		$map = [
				0   => 'SessionID',
				100 => 'SessionID-JA', //Job Applicant
				110 => 'SessionID-CC', //Client Contact

				500 => 'SessionID-HW',
				510 => 'SessionID-HW',
				520 => 'SessionID-HW',

				600 => 'SessionID-QP', //QuickPunch - Web Browser
				605 => 'SessionID',    //QuickPunch - Phone ID (Mobile App expects SessionID)
				610 => 'SessionID-PC', //ClientPC

				700 => 'SessionID',
				705 => 'SessionID',
				710 => 'SessionID',
				800 => 'SessionID',
				810 => 'SessionID',
		];

		if ( isset( $map[$type_id] ) ) {
			return $map[$type_id];
		}

		return false;
	}

	/**
	 * Resolves a session type identifier to its corresponding cookie name.
	 *
	 * Maps a numeric or string session type identifier to a unique cookie name, facilitating
	 * the management of distinct sessions for different parts of the application.
	 *
	 * @param int|string $type_id The session type identifier.
	 * @return string|false The cookie name for the session type, or false if unrecognized.
	 */
	function getName( $type_id = false ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		return $this->getNameByTypeId( $type_id );
	}

	/**
	 * Checks if a session type ID represents a real user session.
	 *
	 * Validates the session type ID against known user-specific session types.
	 * User-specific sessions are necessary for actions like audit logging.
	 * Defaults to the type of the current session if no ID is provided.
	 *
	 * @param int|bool $type_id Session type ID or false to default to current session's type.
	 * @return bool True for a user session, false for non-user sessions.
	 */
	function isUser( $type_id = false ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		//If this is updated, modify PurgeDatabase.class.php for authentication table as well.
		if ( in_array( $type_id, [ 100, 110 ] ) ) {
			return false;
		}

		return true;
	}

	/**
	 * Maps a session type name to its numeric identifier.
	 *
	 * Looks up the numeric type ID associated with a given session type name from the session_type_map.
	 * Numeric type IDs are utilized internally within the application to maintain consistency and improve performance.
	 *
	 * @param string $type Name of the session type to be converted.
	 * @return int|bool Numeric type ID on success, or false if the name is not found in the map.
	 */
	function getTypeIDByName( $type ) {
		$type = strtolower( $type );

		if ( isset( $this->session_type_map[$type] ) ) {
			return (int)$this->session_type_map[$type];
		}

		return false;
	}

	/**
	 * Looks up the name of the session type from its ID.
	 *
	 * Utilizes the session_type_map to resolve a session type ID to its corresponding name.
	 * If the ID is not present in the map, returns false.
	 *
	 * @return string|bool Session type name on success, or false if ID is not mapped.
	 */
	function getTypeName() {
		$type_id_to_name_map = array_flip( $this->session_type_map );
		if ( isset( $type_id_to_name_map[$this->getType()] ) ) {
			return $type_id_to_name_map[$this->getType()];
		}

		return false;
	}

	/**
	 * Obtains the type ID of the current session.
	 *
	 * The type ID is a unique identifier for the session type, influencing access levels and permissions.
	 *
	 * @return int The type ID of the current session.
	 */
	function getType() {
		return $this->type_id;
	}

	/**
	 * Assigns a type ID to the current session.
	 *
	 * Accepts either a numeric ID or a string representing the session type name, which is then
	 * converted to its numeric equivalent. Ensures the session type is valid within the system.
	 *
	 * @param int|string $type_id The session type ID or name.
	 * @return bool True on successful assignment, false on failure.
	 */
	function setType( $type_id ) {
		if ( !is_numeric( $type_id ) ) {
			$type_id = $this->getTypeIDByName( $type_id );
		}

		if ( is_numeric( $type_id ) ) {
			$this->type_id = (int)$type_id;

			return true;
		}

		return false;
	}

	/**
	 * Retrieves the MFA type ID for the session.
	 *
	 * The MFA type ID is a unique identifier for the Multi-Factor Authentication method
	 * applied to the session, enhancing security by requiring additional verification.
	 *
	 * @return int The MFA type ID.
	 */
	function getMFAType() {
		return $this->mfa_type_id;
	}

	/**
	 * Assigns an MFA type ID to the session, dictating the required MFA method.
	 *
	 * @param int $mfa_type_id The identifier for the MFA method to be applied.
	 * @return bool Always true as the MFA type ID is set unconditionally.
	 */
	function setMFAType( $mfa_type_id ) {
		$this->mfa_type_id = (int)$mfa_type_id;

		return true;
	}

	/**
	 * Obtains the IP address from which the current session is accessed.
	 * Useful for security checks, logging, and session validation.
	 *
	 * @return string|null IP address if available, otherwise null.
	 */
	function getIPAddress() {
		return $this->ip_address;
	}

	/**
	 * Assigns a specific or automatically detected IP address to the session.
	 *
	 * Enhances security by associating the session with an IP address, aiding in the prevention of session hijacking.
	 * If the provided IP address is null, the system attempts to determine the client's IP address.
	 *
	 * @param string|null $ip_address The IP address to set. If null, the client's IP is determined and used.
	 * @return bool Whether the IP address was successfully set.
	 */
	function setIPAddress( $ip_address = null ) {
		if ( empty( $ip_address ) ) {
			$ip_address = Misc::getRemoteIPAddress();
		}

		if ( !empty( $ip_address ) ) {
			$this->ip_address = $ip_address;

			return true;
		}

		return false;
	}

	/**
	 * Retrieves the session's idle timeout duration.
	 *
	 * Returns the duration in seconds that a session can be idle before expiring.
	 * Defaults to a configured value, or 4 hours if not specified, to mitigate
	 * security risks associated with indefinitely active sessions.
	 *
	 * @return int Idle timeout in seconds.
	 */
	function getIdleTimeout() {
		if ( $this->idle_timeout == null ) {
			global $config_vars;
			if ( isset( $config_vars['other']['web_session_timeout'] ) && $config_vars['other']['web_session_timeout'] != '' ) {
				$this->idle_timeout = (int)$config_vars['other']['web_session_timeout'];
			} else {
				$this->idle_timeout = 14400; //Default to 4-hours.
			}
		}

		Debug::text( 'Idle Seconds Allowed: ' . $this->idle_timeout, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->idle_timeout;
	}

	/**
	 * Configures the maximum period a session can be inactive before expiring.
	 *
	 * Establishes a session's idle timeout to mitigate risks of long-lived sessions.
	 * Validates the input to ensure a numeric value is used for the timeout duration.
	 *
	 * @param int|string $secs Duration in seconds for the idle timeout, must be numeric.
	 * @return bool Whether the idle timeout was successfully configured.
	 */
	function setIdleTimeout( $secs ) {
		if ( $secs != '' && is_numeric( $secs ) ) {
			$this->idle_timeout = (int)$secs;

			return true;
		}

		return false;
	}

	/**
	 * Determines and formats the endpoint ID for routing API requests.
	 *
	 * Extracts the endpoint ID from the provided argument or defaults to the server's SCRIPT_NAME if not supplied.
	 * It sanitizes the endpoint ID by removing redundant slashes and normalizes it for consistent API endpoint handling.
	 * The formatted endpoint ID is crucial for directing the request to the correct API handler, such as JSON or SOAP.
	 *
	 * @param string|null $end_point_id Optional; the raw endpoint ID to be parsed.
	 * @return string The sanitized and normalized endpoint ID.
	 */
	function parseEndPointID( $end_point_id = null ) {
		if ( $end_point_id == null && isset( $_SERVER['SCRIPT_NAME'] ) && $_SERVER['SCRIPT_NAME'] != '' ) {
			$end_point_id = $_SERVER['SCRIPT_NAME'];
		}

		$end_point_id = Environment::stripDuplicateSlashes( $end_point_id );

		//If the SCRIPT_NAME is something like upload_file.php, or APIGlobal.js.php, assume its the JSON API
		// soap/server.php is a SOAP end-point.
		//   This is also set in parseEndPointID() and getClientIDHeader()
		//   /api/json/api.php should be: json/api
		//   /api/soap/api.php should be: soap/api
		//   /api/report/api.php should be: report/api
		//   /soap/server.php should be: soap/server
		//   See MiscTest::testAuthenticationParseEndPoint() for unit tests.
		if ( $end_point_id == '' || ( strpos( $end_point_id, 'api' ) === false && strpos( $end_point_id, 'soap/server.php' ) === false ) ) {
			$retval = 'json/api';
		} else {
			//$retval = Environment::stripDuplicateSlashes( str_replace( [ dirname( Environment::getAPIBaseURL() ) . '/', '.php' ], '', $end_point_id ) );
			if ( strpos( $end_point_id, 'api/json/api' ) !== false ) {
				$retval = 'json/api';
			} else if ( strpos( $end_point_id, 'api/soap/api' ) !== false ) {
				$retval = 'soap/api';
			} else if ( strpos( $end_point_id, 'api/report/api' ) !== false ) {
				$retval = 'report/api';
			} else if ( strpos( $end_point_id, 'soap/server.php' ) !== false ) {
				$retval = 'soap/server';
			} else if ( strpos( $end_point_id, 'api/time_clock/api' ) !== false ) {
				$retval = 'time_clock/api';
			} else {
				$retval = 'json/api'; //Default to this.
			}
		}

		$retval = strtolower( trim( $retval, '/' ) ); //Strip leading and trailing slashes.
		//Debug::text('End Point: '. $retval .' Input: '. $end_point_id .' API Base URL: '. Environment::getAPIBaseURL(), __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * Retrieves the endpoint ID for the current request.
	 *
	 * Returns the endpoint ID that has been set for the current request or determines
	 * it based on the current environment and request details if not already set.
	 * The endpoint ID is crucial for routing the request to the correct handler.
	 *
	 * @param string|null $end_point_id Optional endpoint ID. If null, determined automatically.
	 * @return string The endpoint ID for the current request.
	 */
	function getEndPointID( $end_point_id = null ) {
		if ( $end_point_id != '' ) {
			$this->end_point_id = $end_point_id;
			Debug::text('Forced End Point: '. $end_point_id, __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( $this->end_point_id == null ) {
			$this->end_point_id = $this->parseEndPointID( $end_point_id );
		}

		return $this->end_point_id;
	}

	/**
	 * Assigns a truncated endpoint ID to the current request.
	 *
	 * Truncates the provided endpoint ID to a maximum length to conform to storage or protocol constraints.
	 *
	 * @param string $value The full endpoint ID to be assigned after truncation.
	 * @return bool True on successful assignment, false if the input is empty.
	 */
	function setEndPointID( $value ) {
		if ( $value != '' ) {
			$this->end_point_id = substr( $value, 0, 30 );

			return true;
		}

		return false;
	}

	/**
	 * Retrieves the client ID from the current session.
	 *
	 * Obtains the client ID that uniquely identifies the client making the request.
	 * The client ID is extracted from the request headers if not already set, ensuring
	 * consistent identification of the client across sessions.
	 *
	 * @return string The lowercase client ID.
	 */
	function getClientID() {
		if ( $this->client_id == null ) {
			$this->client_id = strtolower( $this->getClientIDHeader() );
		}

		return $this->client_id;
	}

	/**
	 * Sets the client ID after sanitizing the input value.
	 * The client ID is converted to lowercase and truncated to a maximum length of 30 characters.
	 *
	 * @param string $value The client ID to be set.
	 * @return bool True if the client ID was set successfully, false if the input value is empty.
	 */
	function setClientID( $value ) {
		if ( $value != '' ) {
			$this->client_id = strtolower( substr( $value, 0, 30 ) );

			return true;
		}

		return false;
	}

	/**
	 * Retrieves the hashed user agent of the current session.
	 *
	 * The user agent is hashed with a salt to ensure privacy and to reduce the length of the string
	 * for storage efficiency. If the user agent has not been previously set, it is obtained from
	 * the HTTP_USER_AGENT server variable and hashed.
	 *
	 * @return string The hashed user agent.
	 */
	function getUserAgent() {
		if ( $this->user_agent == null ) {
			$this->user_agent = sha1( ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null ) . TTPassword::getPasswordSalt() ); //Hash the user agent so its not as long.
		}

		return $this->user_agent;
	}

	/**
	 * Sets the user agent for the current session after optionally hashing it.
	 * The user agent is a string that identifies the client software initiating the request.
	 * Hashing the user agent provides additional security and privacy, and also reduces
	 * the string length for storage efficiency.
	 *
	 * @param string $value The user agent to be set.
	 * @param bool $hash Indicates whether the user agent should be hashed.
	 * @return bool True if the user agent was set successfully, false if the input value is empty.
	 */
	function setUserAgent( $value, $hash = false ) {
		if ( $value != '' ) {
			if ( $hash == true ) {
				$value = sha1( $value . TTPassword::getPasswordSalt() ); //Hash the user agent so its not as long.
			}

			$this->user_agent = substr( $value, 0, 40 );

			return true;
		}

		return false;
	}

	/**
	 * Determines if the session should expire when the browser is closed.
	 *
	 * This setting controls the persistence of the user's session. When enabled, the session will
	 * terminate as soon as the user closes their browser, enhancing security by preventing
	 * unauthorized access to the session after browser closure.
	 *
	 * @return bool True if the session is configured to expire on browser closure, false otherwise.
	 */
	function getEnableExpireSession() {
		return $this->expire_session;
	}

	/**
	 * Sets the session expiration behavior based on browser closure.
	 * When set to true, the session will expire once the browser is closed,
	 * enhancing security by preventing unauthorized access post-closure.
	 *
	 * @param bool $bool The flag to enable or disable session expiration on browser closure.
	 * @return bool Always returns true.
	 */
	function setEnableExpireSession( $bool ) {
		$this->expire_session = (bool)$bool;

		return true;
	}

	/**
	 * Retrieves the session expiration status based on browser closure.
	 * When enabled, ensures sessions terminate after the browser is closed,
	 * providing an additional layer of security.
	 *
	 * @return bool True if session expiration on browser closure is enabled, false otherwise.
	 */
	function setReauthenticatedSession( $force_update = false ) {
		$json_data = $this->getOtherJSON();

		if ( isset( $json_data['mfa'] ) == false ) {
			$json_data['mfa'] = [];
		}

		$json_data['mfa']['one_time_auth'] = true;

		$this->setOtherJSON( $json_data );
		$this->setReauthenticatedDate( TTDate::getTime() );

		if ( $force_update == true ) {
			$this->Update();
		}

		return true;
	}

	/**
	 * Determines the presence of a valid one_time_auth flag in the user's session.
	 * A valid flag permits a single action without the need for reauthentication,
	 * streamlining secure processes that require temporary elevated access.
	 *
	 * @return bool True if a valid one_time_auth flag exists, false otherwise.
	 */
	function isSessionReauthenticated( $allow_impersonation = false ) {
		if ( $this->getType() == 700 ) { // 700 = api_key
			//We do we not check for reauthentication for api key sessions, as its impossible to reauthenticate using an API key.
			return true;
		} else {
			$other_json = $this->getOtherJSON();

			//If the current session is one that is being impersonated from another (ie: support staff logging in as a customer), don't require reauthentication.
			//  This is important so they can import data and such without requiring a password or not their own user record.
			if ( $allow_impersonation == true && isset( $other_json['original_session'] ) && isset( $other_json['original_session']['object_id'] ) && TTUUID::isUUID( $other_json['original_session']['object_id'] ) ) {
				return true;
			}

			if ( $this->getReauthenticatedDate() == false || ( ( TTDate::getTime() - $this->getReauthenticatedDate() ) > $this->mfa_timeout_seconds ) ) {
				return false;
			}

			if ( isset( $other_json['mfa']['one_time_auth'] ) && $other_json['mfa']['one_time_auth'] == true ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return null
	 */
	function getCreatedDate() {
		return $this->created_date;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		if ( $epoch == '' ) {
			$epoch = time();
		}

		if ( is_numeric( $epoch ) ) {
			$this->created_date = $epoch;

			return true;
		}

		return false;
	}

	/**
	 * @return null
	 */
	function getUpdatedDate() {
		return $this->updated_date;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = null ) {
		if ( $epoch == '' ) {
			$epoch = time();
		}

		if ( is_numeric( $epoch ) ) {
			$this->updated_date = $epoch;

			return true;
		}

		return false;
	}

	/**
	 * Retrieves the date and time when the user was last reauthenticated.
	 *
	 * The reauthentication date is stored as an epoch timestamp and represents
	 * the last time the user successfully reauthenticated themselves, which can
	 * be used for security checks and session management.
	 *
	 * @return int|null The epoch timestamp of the last reauthentication, or null if not set.
	 */
	function getReauthenticatedDate() {
		return $this->reauthenticated_date;
	}

	/**
	 * Sets the date and time of the last user reauthentication.
	 *
	 * Assigns the provided epoch timestamp to the reauthenticated_date property.
	 * If no timestamp is provided, the current time is used. The method ensures
	 * that the provided epoch is numeric before setting the date.
	 *
	 * @param int|null $epoch The epoch timestamp of the last reauthentication, or null to use the current time.
	 * @return bool True if the date was set successfully, false otherwise.
	 */
	function setReauthenticatedDate( $epoch = null ) {
		if ( is_numeric( $epoch ) ) {
			$this->reauthenticated_date = $epoch;

			return true;
		}

		return false;
	}

	/**
	 * @return null
	 */
	function getOtherJSON() {
		return $this->other_json ?? []; //Return empty array if not set.
	}

	/**
	 * @param array $data
	 * @return bool
	 */
	function setOtherJSON( $data ) {
		if ( is_array( $data ) == false ) {
			$data = [ $data ];
		}
		$this->other_json = $data;

		return true;
	}

	/**
	 * Registers a permanent API key as a session ID for subsequent API calls.
	 * Eliminates the need for username/password authentication on future requests.
	 *
	 * @param string $user_name The username associated with the API key.
	 * @param string $password The password for the username.
	 * @return bool|string The API key if registration is successful, false otherwise.
	 * @throws DBError If a database error occurs during the registration process.
	 */
	function registerAPIKey( $user_name, $password, $end_point = null ) {
		$login_result = $this->Login( $user_name, $password, 'USER_NAME', false ); //Make sure login succeeds before generating API key. -- Make sure we don't set any cookies when registering an API key, as the cookie that is set will different from what is returned here anyways.
		if ( $login_result === true ) {
			return $this->generateAPIKey( $this->getObjectID(), $end_point );
		}

		Debug::text( 'Password match failed, unable to create API Key session for User ID: ' . $this->getObjectID() . ' Original SessionID: ' . $this->getSessionID(), __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	/**
	 * Generates an API key for a given end point.
	 * The API key serves as a session ID for API calls, allowing for authentication without username/password.
	 *
	 * @param string $end_point The end point for which the API key will be generated.
	 * @return string|null The generated API key or null if the process fails.
	 */
	function registerAPIKeyForCurrentUser( $end_point ) {
		return $this->generateAPIKey( $this->getObjectID(), $end_point );
	}

	/**
	 * Generates an API key for a specified user and end point.
	 * The API key is used for authenticating requests without the need for a username and password.
	 * The key is associated with a user's ID and a specific end point, ensuring access control.
	 *
	 * @param int|string $user_id The unique identifier for the user.
	 * @param string $end_point The end point for which the API key is generated.
	 * @return string|null The generated API key or null if the process fails.
	 */
	protected function generateAPIKey( $user_id, $end_point ) {
		$authentication = new Authentication();
		$authentication->setType( 700 ); //API Key
		$authentication->setSessionID( 'API'. $this->genSessionID() );
		$authentication->setIPAddress();

		if ( $this->getEndPointID( $end_point ) == 'json/api' || $this->getEndPointID( $end_point ) == 'soap/api' || $this->getEndPointID( $end_point ) == 'report/api' ) {
			$authentication->setEndPointID( $this->getEndPointID( $end_point ) ); //json/api, soap/api
		}
		$authentication->setClientID( 'api' );
		$authentication->setUserAgent( 'API KEY' ); //Force the same user agent for all API keys, as its very likely could change across time as these are long-lived keys.
		$authentication->setIdleTimeout( ( 90 * 86400 ) ); //90 Days of inactivity.
		$authentication->setCreatedDate();
		$authentication->setUpdatedDate();
		$authentication->setObjectID( $user_id );

		Debug::text( 'Creating API Key session for User ID: ' . $user_id . ' Original SessionID: ' . $this->getSessionID() .' New SessionID: '. $authentication->getSessionID() . ' DB: ' . $authentication->encryptSessionID( $authentication->getSessionID() ), __FILE__, __LINE__, __METHOD__, 10 );

		//Write data to db.
		$authentication->Write();

		TTLog::addEntry( $this->getObjectID(), 10, TTi18n::getText( 'Registered API Key' ) . ': ' .  $authentication->getSecureSessionID() . ' ' . TTi18n::getText( 'End Point' ) . ': ' . $authentication->getEndPointID(), $this->getObjectID(), 'authentication' ); //Add

		return $authentication->getSessionID();
	}

	/**
	 * Duplicates the current session with a new identifier, allowing for multiple concurrent sessions.
	 * Useful for creating sessions for different users or maintaining parallel sessions for the same user.
	 *
	 * @param string|null $object_id UUID of the user, defaults to current user if null.
	 * @param string|null $ip_address IP for the session, defaults to current session's IP if null.
	 * @param string|null $user_agent User agent string, defaults to current session's agent if null.
	 * @param string|null $client_id Client UUID, defaults to current session's client if null.
	 * @param string|null $end_point_id Endpoint ID, defaults to current session's endpoint if null.
	 * @param int|null $type_id Session type, defaults to current session's type if null.
	 * @return string|null SessionID of the new session, or null on failure.
	 * @throws DBError On database interaction errors.
	 */
	function newSession( $object_id = null, $ip_address = null, $user_agent = null, $client_id = null, $end_point_id = null, $type_id = null ) {
		if ( $object_id == '' && $this->getObjectID() != '' ) {
			$object_id = $this->getObjectID();
		}

		if ( $type_id == null ) {
			$type_id = $this->getType();
		}

		//Allow switching from type_id=700 (API Key) to 800 (username/password) so we can impersonate across API key to browser. Only allow going from 810 to 810 (MFA).
		if ( !( ( $this->getType() == 700 || $this->getType() == 800 || $this->getType() == 810 ) && ( $type_id == 700 || $type_id == 800 || $type_id == 810 ) ) ) {
			Debug::text( ' ERROR: Invalid from/to Type IDs! From Type: ' . $this->getType() . ' To Type: '. $type_id, __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		$new_session_id = $this->genSessionID();
		Debug::text( 'Duplicating session to User ID: ' . $object_id . ' Original SessionID: ' . $this->getSessionID() . ' New Session ID: ' . $new_session_id . ' IP Address: ' . $ip_address . ' Type: ' . $type_id . ' End Point: ' . $end_point_id . ' Client ID: ' . $client_id . ' DB: ' . $this->encryptSessionID( $new_session_id ), __FILE__, __LINE__, __METHOD__, 10 );

		$authentication = new Authentication();
		$authentication->setType( $type_id );
		$authentication->setSessionID( $new_session_id );
		$authentication->setIPAddress( $ip_address );
		$authentication->setEndPointID( $end_point_id );
		$authentication->setClientID( $client_id );
		$authentication->setUserAgent( $user_agent, true ); //Force hash the user agent.
		$authentication->setCreatedDate();
		$authentication->setUpdatedDate();
		$authentication->setObjectID( $object_id );
		$authentication->setOtherJSON( [ 'original_session' => [ 'session_id' => $this->getSessionID(), 'object_id' => $this->getObjectID() ] ] ); //Save original session information so we know this session is being impersonated.

		//Sets session cookie.
		//$authentication->setCookie();

		//Write data to db.
		$authentication->Write();

		//$authentication->UpdateLastLoginDate(); //Don't do this when switching users.

		return $authentication->getSessionID();
	}

	/**
	 * Associates a new object with the current session using the provided UUID.
	 *
	 * The 'object_id' field in the 'authentication' table is updated to link the current session
	 * with the specified user or entity. This enables session context switching. The cache for the
	 * session is also cleared to ensure consistency with the new session context.
	 *
	 * @param string $object_id The UUID of the object to associate with the session.
	 * @return bool True on successful update, false on failure.
	 * @throws DBError If a database interaction error occurs.
	 */
	function changeObject( $object_id ) {
		$this->getObjectById( $object_id );

		$ph = [
				'object_id'  => TTUUID::castUUID( $object_id ),
				'session_id' => $this->encryptSessionID( $this->getSessionID() ),
		];

		try {
			$query = 'UPDATE authentication SET object_id = ? WHERE session_id = ?';
			$this->db->Execute( $query, $ph );
			$this->cache->remove( $ph['session_id'], 'authentication' );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * Retrieves an object by its UUID.
	 *
	 * Attempts to fetch a user or job applicant object based on the provided UUID.
	 * The method first checks if the session is associated with a user, then attempts
	 * to retrieve the user object. If the session type is for a job applicant, it tries
	 * to fetch the job applicant object. If the object is found and is an object, it is returned.
	 *
	 * @param string $id The UUID of the object to retrieve.
	 * @return mixed The object if found, or false if not found or the ID is empty.
	 */
	function getObjectByID( $id ) {
		if ( empty( $id ) ) {
			return false;
		}

		if ( $this->isUser() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByID( $id );
			if ( $ulf->getRecordCount() == 1 ) {
				$retval = $ulf->getCurrent();
			}
		}

		if ( $this->getType() === 100 ) {
			$jalf = TTnew( 'JobApplicantListFactory' ); /** @var JobApplicantListFactory $jalf */
			$jalf->getByID( $id );
			if ( $jalf->getRecordCount() == 1 ) {
				$retval = $jalf->getCurrent();
			}
		}

		if ( isset( $retval ) && is_object( $retval ) ) {
			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|object
	 */
	function getObject() {
		if ( is_object( $this->obj ) ) {
			return $this->obj;
		}

		return false;
	}

	/**
	 * @param $object
	 * @return bool
	 */
	function setObject( $object ) {
		if ( is_object( $object ) ) {
			$this->obj = $object;

			return true;
		}

		return false;
	}

	/**
	 * @return null
	 */
	function getObjectID() {
		return $this->object_id;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setObjectID( $id ) {
		$id = TTUUID::castUUID( $id );
		if ( $id != '' ) {
			$this->object_id = $id;

			return true;
		}

		return false;
	}

	/**
	 * Retrieves a secure, partially obfuscated version of the session ID.
	 * The middle portion of the session ID is replaced with ellipsis to prevent exposing the full ID,
	 * which enhances security by making it more difficult to hijack the session.
	 *
	 * @return string The obfuscated session ID.
	 */
	function getSecureSessionID() {
		return substr_replace( $this->getSessionID(), '...', 7, (int)( strlen( $this->getSessionID() ) - 11 ) );
	}

	/**
	 * Encrypts the session ID using a private salt to enhance security.
	 * The encryption serves as a safeguard against session hijacking by obscuring the session ID in the database.
	 * This makes it difficult for attackers to exploit potential SQL injection vulnerabilities for unauthorized access.
	 * @param string $session_id The session ID to be encrypted.
	 * @return string The encrypted session ID.
	 */
	function encryptSessionID( $session_id ) {
		$retval = sha1( $session_id . TTPassword::getPasswordSalt() );

		return $retval;
	}

	/**
	 * Retrieves the session ID of the current user session.
	 * The session ID is a unique identifier for the user's session and is used for tracking and authentication purposes.
	 *
	 * @return string|null The current session ID, or null if no session ID is set.
	 */
	function getSessionID() {
		return $this->session_id;
	}

	/**
	 * @param string $session_id UUID
	 * @return bool
	 */
	function setSessionID( $session_id ) {
		$validator = new Validator;
		$session_id = $validator->stripNonAlphaNumeric( $session_id );

		if ( !empty( $session_id ) ) {
			$this->session_id = $session_id;

			return true;
		}

		return false;
	}

	/**
	 * Generates a unique session identifier using SHA-1 hashing.
	 *
	 * Utilizes a unique ID to create a session identifier that is then hashed for security purposes.
	 * The SHA-1 algorithm ensures the session ID is unique and not easily predictable.
	 *
	 * @return string A SHA-1 hashed unique session identifier.
	 */
	function genSessionID() {
		return sha1( Misc::getUniqueID() );
	}

	/**
	 * Sets a cookie with session information or company short name based on the provided type ID.
	 * If type_id is true, it sets a cookie for the company short name, otherwise for the session.
	 * The cookie for the session includes the session ID and has an expiration based on session settings.
	 * The company short name cookie helps in identifying the company in multi-tenant environments.
	 *
	 * @param bool $type_id Indicates the type of cookie to set; session ID or company short name.
	 * @param object|null $company_obj The company object to retrieve the short name from, if applicable.
	 * @return bool True if the cookie is set successfully, false otherwise.
	 */
	private function setCookie( $type_id = false, $company_obj = null ) {
		if ( $this->getSessionID() != '' ) {
			$cookie_expires = ( time() + 7776000 ); //90 Days
			if ( $this->getEnableExpireSession() === true ) {
				$cookie_expires = 0; //Expire when browser closes.
			}
			Debug::text( 'Cookie Expires: ' . $cookie_expires . ' Path: ' . Environment::getCookieBaseURL(), __FILE__, __LINE__, __METHOD__, 10 );

			//15-Jun-2016: This should be not be needed anymore as it has been around for several years now.
			//setcookie( $this->getName(), NULL, ( time() + 9999999 ), Environment::getBaseURL(), NULL, Misc::isSSL( TRUE ) ); //Delete old directory cookie as it can cause a conflict if it stills exists.

			//Upon successful login to a cloud hosted server, set the URL to a cookie that can be read from the upper domain to help get the user back to the proper login URL later.
			if ( DEPLOYMENT_ON_DEMAND == true && DEMO_MODE == false ) {
				setcookie( 'LoginURL', Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getBaseURL(), ( time() + 9999999 ), '/', '.' . Misc::getHostNameWithoutSubDomain( Misc::getHostName( false ) ), false ); //Delete old directory cookie as it can cause a conflict if it stills exists.
			}

			//Set cookie in root directory so other interfaces can access it.
			setcookie( $this->getName(), $this->getSessionID(), $cookie_expires, Environment::getCookieBaseURL(), '', Misc::isSSL( true ) );
			if ( is_object( $company_obj ) ) {
				setcookie( 'ShortName', $company_obj->getShortName(), ( time() + 9999999 ), '/', '.' . Misc::getHostNameWithoutSubDomain( Misc::getHostName( false ) ), false ); //Delete old directory cookie as it can cause a conflict if it stills exists.
			}

			return true;
		}

		return false;
	}

	/**
	 * Determines if the cookie can be destroyed.
	 *
	 * @return bool True if the cookie destruction process is initiated, false otherwise.
	 */
	private function destroyCookie() {
		setcookie( $this->getName(), '', ( time() + 9999999 ), Environment::getCookieBaseURL(), '', Misc::isSSL( true ) );

		return true;
	}

	/**
	 * Updates the last login date for a user in the database.
	 * If an object ID is not provided, the method uses the ID of the current session's user.
	 *
	 * @param string|null $object_id The unique identifier of the user, defaults to current user if null.
	 * @return bool Always returns true, indicating the update was successful.
	 * @throws DBError If there is an issue executing the database update.
	 */
	function UpdateLastLoginDate( $object_id = null ) {
		$ph = [
				'last_login_date' => time(),
				'object_id'       => TTUUID::castUUID( $object_id ?? $this->getObjectID() ),
		];

		$query = 'UPDATE users SET last_login_date = ? WHERE id = ?';

		try {
			$this->db->Execute( $query, $ph );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * Destroys the session cookie.
	 *
	 * Clears the session cookie by setting its expiration date in the past,
	 * effectively logging out the user from the current session.
	 *
	 * @return bool Always returns true, indicating the cookie destruction process was initiated.
	 */
	public function Update() {
		try {
			$ph = [
					'updated_date'         => time(),
					'reauthenticated_date' => $this->getReauthenticatedDate(),
					'type_id'              => $this->getType(),
					'other_json'           => json_encode( $this->getOtherJSON() ),
					'session_id'           => $this->encryptSessionID( $this->getSessionID() ),
			];

			Debug::Arr( $ph, 'Updating Session Data for Session ID: '. $this->getSessionID() .' Encrypted Session ID: '. $this->encryptSessionID( $this->getSessionID() ), __FILE__, __LINE__, __METHOD__, 10 );
			$query = 'UPDATE authentication SET updated_date = ?, reauthenticated_date = ?, type_id = ?, other_json = ? WHERE session_id = ?';
			$this->db->Execute( $query, $ph ); //This can cause SQL error: "could not serialize access due to concurrent update" when in READ COMMITTED mode.

			$clear_cache = function() {
				if ( get_class( $this->cache ) == 'Redis_Cache_Lite' ) {
					Debug::Text( '  Clearing cached session data...', __FILE__, __LINE__, __METHOD__, 10 );
					$this->cache->_unlink( $this->cache->_file, true ); //This removes the cache from all read-only (slave) servers, so during MFA there isn't stale cache sitting on a different server causing race conditions and random MFA failures.
				}
			};

			$cached_session = $this->cache->get( $ph['session_id'], 'authentication' );
			if ( is_array( $cached_session ) ) {
				Debug::Text( '  Updating cached session data...', __FILE__, __LINE__, __METHOD__, 10 );
				$cached_session['updated_date'] = $ph['updated_date'];
				$cached_session['reauthenticated_date'] = $ph['reauthenticated_date'];
				$cached_session['type_id'] = $ph['type_id'];
				$cached_session['other_json'] = $ph['other_json'];

				$clear_cache();
				$this->cache->save( $cached_session, $ph['session_id'], 'authentication' );
			} else {
				//Make sure we clear the cache on all *other* servers, even if the current session data is not cached on our current server.
				$clear_cache();
			}
			unset( $cached_session );
		} catch ( Exception $e ) {
			//Ignore any serialization errors, as its not a big deal anyways.
			Debug::text( 'WARNING: SQL query failed, likely due to transaction isolotion: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			//throw new DBError($e);
		}

		return true;
	}

	/**
	 * Initiates the deletion of a session from the authentication table.
	 * Removes the session based on session ID or if the session has exceeded the idle timeout.
	 * Also clears the session from the cache.
	 *
	 * @throws DBError If the deletion process encounters a database error.
	 * @return bool Always returns true upon successful completion.
	 */
	private function Delete() {
		try {
			$ph = [
					'session_id' => $this->encryptSessionID( $this->getSessionID() ),
			];

			$query = 'DELETE FROM authentication WHERE session_id = ? OR (' . time() . ' - updated_date) > idle_timeout';
			$this->db->Execute( $query, $ph );
			$this->cache->remove( $ph['session_id'], 'authentication' );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * Initiates the deletion of a session from the authentication table.
	 * Removes the session based on session ID or if the session has exceeded the idle timeout.
	 * Also clears the session from the cache.
	 *
	 * @throws DBError If the deletion process encounters a database error.
	 * @return bool Always returns true upon successful completion.
	 */
	private function Write() {
		$ph = [
				'session_id'           => $this->encryptSessionID( $this->getSessionID() ),
				'type_id'              => (int)$this->getType(),
				'object_id'            => TTUUID::castUUID( $this->getObjectID() ),
				'ip_address'           => $this->getIPAddress(),
				'idle_timeout'         => $this->getIdleTimeout(),
				'end_point_id'         => $this->getEndPointID(),
				'client_id'            => $this->getClientID(),
				'user_agent'           => $this->getUserAgent(),
				'created_date'         => $this->getCreatedDate(),
				'updated_date'         => $this->getUpdatedDate(),
				'reauthenticated_date' => $this->getReauthenticatedDate(),
				'other_json'           => json_encode( $this->getOtherJSON() ),
		];

		try {
			$query = 'INSERT INTO authentication (session_id, type_id, object_id, ip_address, idle_timeout, end_point_id, client_id, user_agent, created_date, updated_date, reauthenticated_date, other_json ) VALUES( ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ? )';
			$this->db->Execute( $query, $ph );
			$this->cache->save( $ph, $ph['session_id'], 'authentication' );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * Populates the current Authentication object with session data for a specific session ID.
	 * This is particularly useful during API calls where the user is not logged in, or when the session
	 * being authorized is different from the one that is currently logged in. It ensures that operations
	 * such as Read() and Update() act on the correct session data.
	 *
	 * @param string|null $session_id The session ID to use for populating the Authentication object, or null to use the current session.
	 * @return bool True if the object was populated successfully, false otherwise.
	 */
	public function setAndReadForMultiFactor( $session_id = null ) {
		global $config_vars, $db;

		//Check if we are using load balancing or not. If we are force the main $db connection to just the master/write server, otherwise we could be reading old data if the replication is delayed.
		//Every SQL call after this will go to the master server.
		//  The 2nd time this is called, it would no longer be a ADODBLoadBalancer object, so we need to make sure getConnection method always exists first.
		if ( strpos( $config_vars['database']['host'], ',' ) !== false && method_exists( $db, 'getConnection') == true ) {
			$db = $db->getConnection( 'write' );
		}

		//Session ID and EndPoint must set so $this->read() knows which data to read. setObjectFromArray() sets the same data again though, but they should match anyways.
		$this->setSessionID( $session_id ?? $this->getCurrentSessionID( 810 ) );
		$this->setEndPointID( 'json/api' );

		//When the app is authenticating a multifactor request, the session being authenticated is different from the session the app is logged into.
		//Because of this we do not know what client_id it is being authenticated and need to check all the options.
		//If the app is authenticating a browser session, the app only knows the session_id it is trying to authenticate and not the other data.
		$result = $this->Read( [ 0, 800, 810 ], [ 'browser-timetrex', 'app-timetrex', 'app-timetrex-kiosk' ], true );
		if ( is_array( $result ) == true ) {
			return $this->setObjectFromArray( $result );
		}

		return false;
	}

	/**
	 * Populates the Authentication object with data from an array.
	 *
	 * Assigns values from the provided associative array to the corresponding properties
	 * of the Authentication object. If the object is successfully populated with the
	 * required data, it returns true, otherwise false.
	 *
	 * @param array $result Associative array containing session data.
	 * @return bool True if the object is successfully populated, false otherwise.
	 */
	private function setObjectFromArray( $result ) {
		$this->setType( $result['type_id'] );
		$this->setIdleTimeout( $result['idle_timeout'] );
		$this->setEndPointID( $result['end_point_id'] );
		$this->setClientID( $result['client_id'] );
		$this->setUserAgent( $result['user_agent'] );
		$this->setSessionID( $this->getSessionID() ); //Make sure this is *not* the encrypted session_id
		$this->setIPAddress( $result['ip_address'] );
		$this->setCreatedDate( $result['created_date'] );
		$this->setUpdatedDate( $result['updated_date'] );
		$this->setReauthenticatedDate( $result['reauthenticated_date'] ?? null );
		$this->setObjectID( $result['object_id'] );
		$this->setOtherJSON( json_decode( ( $result['other_json'] ?? '[]' ), true ) );
		if ( $this->setObject( $this->getObjectById( $this->getObjectID() ) ) ) {
			return true;
		}

		return false;
	}


	/**
	 * Retrieves an authentication record based on session, endpoint, and type criteria.
	 * Optionally bypasses cache when retrieving the record.
	 *
	 * @param array $type An array of type identifiers to filter the authentication records.
	 * @param array|null $client An array of client identifiers to filter the authentication records, or null to use the current client ID.
	 * @param bool $skip_cache Whether to skip the cache lookup and directly access the database.
	 * @return array|bool The authentication record as an associative array, or false if not found or on failure.
	 */
	private function Read( $type, $client = null, $skip_cache = false ) {
		$ph = [
				'session_id'   => $this->encryptSessionID( $this->getSessionID() ),
				'end_point_id' => $this->getEndPointID(),
				'updated_date' => time(),
		];

		if ( $client === null ) {
			$client = [ $this->getClientID() ];
		}

		$result = false;

		//By using caching here, we don't actually save that much time overall,
		// but in cases where SQL calls are otherwise never executed during a request (ie: isLoggedIn(), ping() ), we can save a connection to the database.
		if ( $skip_cache == false && is_object( $this->cache ) ) {
			$result = $this->cache->get( $ph['session_id'], 'authentication' );
			if ( is_array( $result ) && count( $result ) > 1 ) {
				//Check to make sure cache result matches what we expect.
				if ( in_array( $result['type_id'], $type ) == true && in_array( $result['client_id'], $client ) == true && $result['end_point_id'] == $ph['end_point_id'] && $result['updated_date'] >= ( $ph['updated_date'] - $result['idle_timeout'] ) ) {
					Debug::text( '  Using cached authentication record...', __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Arr( [ $ph, $result ], '  Cached authentication record does not match filter, falling back to SQL! Cache ID: ' . $ph['session_id'], __FILE__, __LINE__, __METHOD__, 10 );
					$result = false;
				}
			} elseif ( is_object( $result ) && get_class( $result ) == 'PEAR_Error' ) {
				Debug::Arr( $result, 'WARNING: Unable to read cache file, likely due to permissions or locking! Cache ID: ' . $ph['session_id'] . ' File: ' . $this->cache->_file, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		if ( empty( $result ) ) {
			//Need to handle IP addresses changing during the session.
			//When using SSL, don't check for IP address changing at all as we use secure cookies.
			//When *not* using SSL, always require the same IP address for the session.
			//However we need to still allow multiple sessions for the same user, using different IPs.

			$type_ph = [];
			$client_ph = [];
			foreach ( $type as $type_id ) {
				$ph[] = $type_id;
				$type_ph[] = '?';
			}

			foreach ( $client as $client_id ) {
				$ph[] = $client_id;
				$client_ph[] = '?';
			}

			$query = 'SELECT type_id, session_id, object_id, ip_address, idle_timeout, end_point_id, client_id, user_agent, created_date, updated_date, reauthenticated_date, other_json FROM authentication WHERE session_id = ? AND end_point_id = ? AND updated_date >= ( ? - idle_timeout ) AND type_id in (' . implode( ',', $type_ph ) . ') AND client_id in (' . implode( ',', $client_ph ) . ')';
			$result = $this->db->GetRow( $query, $ph );
			//Debug::Query($query, $ph, __FILE__, __LINE__, __METHOD__, 10);
			if ( $skip_cache == false && !empty( $result ) ) {
				Debug::Text( '  Caching Session Data: '. $ph['session_id'], __FILE__, __LINE__, __METHOD__, 10 );
				$this->cache->save( $result, $ph['session_id'], 'authentication' );
			}
		}

		if ( is_array( $result ) && count( $result ) > 0 ) {
			return $result;
		} else {
			Debug::text( 'Session ID not found in the DB... End Point: ' . $this->getEndPointID() . ' Client ID: ' . $this->getClientID() . ' Type: ' . implode( ',', $type ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * Authenticates a user based on username and password.
	 * Optionally handles multi-factor authentication, cookie management, and reauthentication logic.
	 *
	 * @param string $user_name The username of the user attempting to log in.
	 * @param string $password The password for the user, may be empty for certain MFA methods.
	 * @param string $type The type of login being performed, defaults to 'USER_NAME'.
	 * @param bool $enable_cookie Determines if a login cookie should be set, defaults to true.
	 * @param bool $reauthenticate_only If true, only reauthentication will be performed.
	 * @param int $mfa_type_id The ID of the multi-factor authentication type, defaults to 0 (none).
	 * @return bool True on successful authentication, false otherwise.
	 * @throws DBError If a database error occurs during authentication.
	 */
	function Login( $user_name, $password, $type = 'USER_NAME', $enable_cookie = true, $reauthenticate_only = false, $mfa_type_id = 0 ) {
		//DO NOT lowercase username, because iButton values are case sensitive.
		$user_name = html_entity_decode( trim( $user_name ) );
		$password = html_entity_decode( trim( $password ) );

		//Checks user_name/password. However password is blank for iButton/Fingerprints often so we can't check that.
		if ( $user_name == '' ) {
			return false;
		}

		$this->setMFAType( $mfa_type_id );

		$type = strtolower( $type );
		Debug::text( 'Login Type: ' . $type, __FILE__, __LINE__, __METHOD__, 10 );
		try {
			//Prevent brute force attacks by IP address.
			//Allowed up to 20 attempts in a 30 min period.
			if ( $this->getRateLimitObject()->check() == false ) {
				Debug::Text( 'Excessive failed password attempts... Preventing login from: ' . Misc::getRemoteIPAddress() . ' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10 );
				sleep( 5 ); //Excessive password attempts, sleep longer.

				return false;
			}

			$uf = new UserFactory();
			if ( preg_match( $uf->username_validator_regex, $user_name ) === 0 ) { //This helps prevent invalid byte sequences on unicode strings.
				Debug::Text( 'Username doesnt match regex: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );

				return false; //No company by that user name.
			}
			unset( $uf );

			$bypass_multi_factor = false;

			switch ( $type ) {
				case 'user_name_multifactor':
				case 'user_name':
					$company_obj = $this->getCompanyObject( $user_name, 'USER' );

					//No password is allowed for webauthn and SAML
					if ( $password == '' && ( $this->getMFAType() == 100 || $this->getMFAType() == 50 ) == false ) {
						return false;
					}

					if ( $this->checkCompanyStatus( $user_name ) == 10 ) { //Active
						//Lowercase regular user_names here only.
						if ( $this->getMFAType() == 50 || $this->getMFAType() == 100 ) { // 50 = SAML, 100 = webauthn
							//Check to prevent community editions from using webauthn or SAML
							if ( getTTProductEdition() < 15 || ( $this->getMFAType() != 50 && version_compare( PHP_VERSION, '8.1.0', '<' ) == 1 ) || is_object( $company_obj ) == false || $company_obj->getProductEdition() < 15 ) {
								$password_result = false;
							} else {
								//SAML and Webauthn login do not require the user's password, and we can skip the password check.
								//However, we still go through the normal login process flow and use checkPassword() to set
								//the current user and object on this authentication record as that is how normal login functions.
								$password_result = $this->checkPassword( $user_name, $password, true );
							}
						} else {
							$password_result = $this->checkPassword( $user_name, $password );
						}
					} else {
						$password_result = false; //No company by that user name.
					}

					if ( $password_result === true ) {
						if ( $type === 'user_name_multifactor' ) {
							if ( $this->getMFAType() == 100 ) { //100 = webauthn always logs in as pending
								$type = 'pending_authentication';
							} else if ( $reauthenticate_only == true ) {
								//reauthenticate_only always does full multifactor authentication process and ignores trusted device
								$type = 'pending_authentication'; //Set type to 0 until multifactor authentication is verified.
								$this->setMfaStartListen( true ); //Reauthenticate always starte and listens for multifactor authentication.
							} else {
								$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
								$ulf->getByUserNameAndCompanyId( $user_name, $company_obj->getId() );
								if ( $ulf->getRecordCount() == 1 ) {
									$user_obj = $ulf->getCurrent();
									if ( $this->isUserAllowedBypassMFA( $user_obj->getId(), $_COOKIE['TrustedDevice'] ?? '' ) ) {
										Debug::Text( 'Bypassing MFA for user: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );
										$bypass_multi_factor = true;
										$this->setMfaStartListen( false );
									} else {
										Debug::Text( 'NOT Bypassing MFA for user: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );
										$type = 'pending_authentication'; //Set type to 0 until multifactor authentication is verified.
										$this->setMfaStartListen( true );
									}
								} else {
									Debug::Text( 'Cannot find user, NOT Bypassing MFA for user: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );
									$type = 'pending_authentication'; //Set type to 0 until multifactor authentication is verified.
								}
							}
						}
					}
					break;
				case 'phone_id': //QuickPunch ID/Password
				case 'quick_punch_id':
					$company_obj = $this->getCompanyObject( $user_name, 'QUICK_PUNCH' );
					if ( getTTProductEdition() > 10 && is_object( $company_obj ) && $company_obj->getProductEdition() > 10 ) {
						$password_result = $this->checkPhonePassword( $user_name, $password );
					} else {
						Debug::text( 'ERROR: Company not found or not active...', __FILE__, __LINE__, __METHOD__, 10 );
						$password_result = false; //No company by that quick punch ID
					}
					unset( $company_obj );
					break;
				case 'ibutton':
					$password_result = $this->checkIButton( $user_name );
					break;
				case 'barcode':
					$password_result = $this->checkBarcode( $user_name, $password );
					break;
				case 'finger_print':
					$password_result = $this->checkFingerPrint( $user_name );
					break;
				case 'client_pc':
					//This is for client application persistent connections, use:
					//Login Type: client_pc
					//Station Type: PC

					$password_result = false;

					//StationID must be set on the URL
					if ( isset( $_GET['StationID'] ) && $_GET['StationID'] != '' ) {
						$slf = new StationListFactory();
						$slf->getByStationID( $_GET['StationID'] );
						if ( $slf->getRecordCount() == 1 ) {
							$station_obj = $slf->getCurrent();
							if ( $station_obj->getStatus() == 20 ) { //Enabled
								$uilf = new UserIdentificationListFactory();
								$uilf->getByCompanyIdAndTypeId( $station_obj->getCompany(), [ 1 ] ); //1=Employee Sequence number.
								if ( $uilf->getRecordCount() > 0 ) {
									foreach ( $uilf as $ui_obj ) {
										if ( (int)$ui_obj->getValue() == (int)$user_name ) {
											$password_result = $this->checkBarcode( $ui_obj->getUser(), $password );
										}
									}
								} else {
									Debug::text( 'UserIdentification match failed: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );
								}
							} else {
								Debug::text( 'Station is DISABLED... UUID: ' . $station_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							}
						} else {
							Debug::text( 'StationID not specifed on URL or not found...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
					break;
				case 'http_auth':
					if ( $this->checkCompanyStatus( $user_name ) == 10 ) { //Active
						//Lowercase regular user_names here only.
						$password_result = $this->checkUsername( $user_name );
					} else {
						$password_result = false; //No company by that user name.
					}
					break;
				case 'job_applicant':
					$company_obj = $this->getCompanyObject( $user_name, 'JOB_APPLICANT' );
					if ( is_object( $company_obj ) && $company_obj->getProductEdition() == 25 && $company_obj->getStatus() == 10 ) { //Active
						$password_result = $this->checkApplicantPassword( $user_name, $password );
					} else {
						Debug::text( 'ERROR: Company not found or not active...', __FILE__, __LINE__, __METHOD__, 10 );
						$password_result = false; //No company by that user name.
					}
					unset( $company_obj );
					break;
				default:
					return false;
			}

			if ( $password_result === true ) {
				if ( $reauthenticate_only === true ) {
					//We are only reauthenticating and do not want to save this as a new session.
					return true;
				}

				$this->setType( $type );
				$this->setSessionID( $this->genSessionID() );
				$this->setIPAddress();

				if ( $this->getClientIDHeader() == 'App-TimeTrex' && Misc::getMobileAppClientVersion() != '' && version_compare( Misc::getMobileAppClientVersion(), '5.0.0', '>=' )  ) {
					$this->setIdleTimeout( ( 60 * 86400 ) ); //Login from Mobile app, use longer (60 day)  idle timeouts so they don't have to login so often.
				} else if ( $this->getClientIDHeader() == 'App-TimeTrex-Kiosk' ) {
					$this->setIdleTimeout( ( 15 * 60 ) ); //Login from Mobile app in kiosk mode, use 15 min session timeout.
				}

				$this->setCreatedDate();
				$this->setUpdatedDate();

				//Sets session cookie.
				if ( $enable_cookie !== false ) {
					$this->setCookie( false, $company_obj ?? null );
				} else {
					Debug::text( '  Not setting session cookie...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				//Write data to db.
				$this->Write();

				Debug::text( 'Login Succesful for User Name: ' . $user_name . ' End Point ID: ' . $this->getEndPointID() . ' Client ID: ' . $this->getClientID() . ' Type: ' . $type . ' Session ID: Cookie: ' . $this->getSessionID() . ' DB: ' . $this->encryptSessionID( $this->getSessionID() ), __FILE__, __LINE__, __METHOD__, 10 );

				//Only update last_login_date when using user_name to login to the web interface.
				if ( $type == 'user_name' || ( $type == 'user_name_multifactor' && $bypass_multi_factor == true ) ) {
					$this->UpdateLastLoginDate();
				}

				if ( $this->isUser() == true ) {
					//Truncate SessionID for security reasons, so someone with access to the audit log can't steal sessions.
					if ( $type == 'pending_authentication' ) { //Pending authentication has not completed the full login process yet, so don't add a "login" audit log entry until login is fully successfull.
						TTLog::addEntry( $this->getObjectID(), 98, TTi18n::getText( 'From' ) . ': ' . Misc::getLocationOfIPAddress( Misc::getRemoteIPAddress() ) . ' (' . Misc::getRemoteIPAddress() . ') ' . TTi18n::getText( 'Type' ) . ': ' . $type . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $this->getSecureSessionID() . ' ' . TTi18n::getText( 'Client' ) . ': ' . $this->getClientID() . ' ' . TTi18n::getText( 'End Point' ) . ': ' . $this->getEndPointID() . ' ' . TTi18n::getText( 'ObjectID' ) . ': ' . $this->getObjectID(), $this->getObjectID(), 'authentication' ); //Login
					} else {
						TTLog::addEntry( $this->getObjectID(), 100, TTi18n::getText( 'From' ) . ': ' . Misc::getLocationOfIPAddress( Misc::getRemoteIPAddress() ) . ' (' . Misc::getRemoteIPAddress() . ') ' . TTi18n::getText( 'Type' ) . ': ' . $type . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $this->getSecureSessionID() . ' ' . TTi18n::getText( 'Client' ) . ': ' . $this->getClientID() . ' ' . TTi18n::getText( 'End Point' ) . ': ' . $this->getEndPointID() . ' ' . TTi18n::getText( 'ObjectID' ) . ': ' . $this->getObjectID(), $this->getObjectID(), 'authentication' ); //Login
					}
				}

				$this->getRateLimitObject()->delete(); //Clear failed password rate limit upon successful login.

				return true;
			}

			Debug::text( 'Login Failed! Attempt: ' . $this->getRateLimitObject()->getAttempts(), __FILE__, __LINE__, __METHOD__, 10 );

			sleep( ceil( $this->getRateLimitObject()->getAttempts() * 0.5 ) ); //If password is incorrect, sleep for some time to slow down brute force attacks.
		} catch ( Exception $e ) {
			//Database not initialized, or some error, redirect to Install page.
			throw new DBError( $e, 'DBInitialize' );
		}

		return false;
	}

	/**
	 * Determines the success of the authentication process.
	 *
	 * @return bool True if authentication was successful, false otherwise.
	 */
	function Logout() {
		$this->destroyCookie();
		$this->Delete();

		if ( $this->isUser() == true ) {
			TTLog::addEntry( $this->getObjectID(), 110, TTi18n::getText( 'From' ) . ': ' . Misc::getLocationOfIPAddress( Misc::getRemoteIPAddress() ) . ' (' . Misc::getRemoteIPAddress() . ') ' . TTi18n::getText( 'Type' ) . ': ' . $this->getTypeName() . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $this->getSecureSessionID() . ' ' . TTi18n::getText( 'Client' ) . ': ' . $this->getClientID() . ' ' . TTi18n::getText( 'End Point' ) . ': ' . $this->getEndPointID() . ' ' . TTi18n::getText( 'ObjectID' ) . ': ' . $this->getObjectID(), $this->getObjectID(), 'authentication' ); //Login
		}

		global $current_user, $current_company;
		$current_user = null; //This helps subsequent functions from thinking we are still logged in. Unset does not work on global variables.
		$current_company = null;

		return true;
	}

	/**
	 * Retrieves the current session ID based on the session type.
	 * The session ID is sourced from COOKIE, POST, or GET variables, in that order of precedence.
	 * @param string $type The type of session to retrieve the ID for.
	 * @return string|bool The session ID if available, or false if not found.
	 */
	function getCurrentSessionID( $type ) {
		$session_name = $this->getName( $type );

		if ( isset( $_COOKIE[$session_name] ) && $_COOKIE[$session_name] != '' ) {
			$session_id = (string)$_COOKIE[$session_name];
		} else if ( isset( $_SERVER[$session_name] ) && $_SERVER[$session_name] != '' ) {
			$session_id = (string)$_SERVER[$session_name];
		} else if ( isset( $_POST[$session_name] ) && $_POST[$session_name] != '' ) {
			$session_id = (string)$_POST[$session_name];
		} else if ( isset( $_GET[$session_name] ) && $_GET[$session_name] != '' ) {
			$session_id = (string)$_GET[$session_name];
		} else {
			$session_id = false;
		}

		Debug::text( 'Session ID: ' . $session_id . ' Encrypted Session ID: '. $this->encryptSessionID( $session_id ) .'  IP Address: ' . Misc::getRemoteIPAddress() . ' URL: ' . $_SERVER['REQUEST_URI'], __FILE__, __LINE__, __METHOD__, 10 );

		return $session_id;
	}

	/**
	 * Checks if the provided session ID corresponds to an API key.
	 *
	 * @param string $session_id The session ID to be checked.
	 * @return bool True if the session ID is an API key, false otherwise.
	 */
	function isSessionIDAPIKey( $session_id ) {
		if ( $session_id != '' && substr( $session_id, 0, 3 ) == 'API' ) {
			return true;
		}

		return false;
	}

	/**
	 * Retrieves an array of multi-factor authentication data if available.
	 * This includes the type of authentication, user and session identifiers,
	 * secret keys, and timestamps related to the authentication process.
	 * If key or login_approved is not set, it indicates that MFA data may have been consumed.
	 *
	 * @return array|false An associative array of MFA data or false if not available.
	 */
	function getMultiFactorData() {
		$other_json = $this->getOtherJSON();

		//If key or login_approved exists then we know that the MFA data has not been fully used yet.
		if ( isset( $other_json['mfa']['key'] ) == false && isset( $other_json['mfa']['login_approved'] ) == false ) {
			Debug::Text( 'ERROR: Cannot retrieve MFA key or login_approved flag, MFA data may have already been consumed.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$key = $other_json['mfa']['key'] ?? false; //Secret key used to encrypt multifactor key (This may be consumed and no longer exist)

		return [
				'type_id'                     => $this->getType(),
				'user_id'                     => $this->getObjectID(),
				'session_id'                  => $this->getSessionID(),
				'key'                         => $key,
				'reauthenticated_date'        => $this->getReauthenticatedDate(),
				'expected_key'                => $key ? sha1( $this->getSessionID() . $this->getObjectID() . $key ) : false,
				'time'                        => $other_json['mfa']['time'] ?? false,
				'is_reauthentication_request' => $other_json['mfa']['is_reauthentication_request'] ?? false,
				'one_time_auth'               => $other_json['mfa']['one_time_auth'] ?? false,
				'login_approved'              => $other_json['mfa']['login_approved'] ?? false,
		];
	}

	/**
	 * Generates a new Multi-Factor Authentication (MFA) key for the user's session.
	 * The generated key is stored in the session data and is used to verify the user's identity
	 * during the authentication process. If reauthentication is required, this is indicated in the session.
	 *
	 * @param bool $is_reauthentication Indicates whether the key is for reauthentication.
	 * @return bool Always returns true, indicating the MFA key was generated successfully.
	 */
	function generateMultiFactorAuthKey( $is_reauthentication ) {
		$secret_key = $this->genSessionID();

		$other_json = array_merge( $this->getOtherJSON(), [ 'mfa' =>  [
				'time'                        => TTDate::getTime(),
				'key'                         => $secret_key,
				'is_reauthentication_request' => $is_reauthentication,
		] ] );

		$this->setOtherJSON( $other_json );

		Debug::Text( 'Generating MFA key for user: ' . $this->getObjectID(), __FILE__, __LINE__, __METHOD__, 10 );

		$this->Update();

		return true;
	}

	/**
	 * Validates the session based on the provided session ID and type.
	 * Optionally updates the session's last accessed date.
	 * Throws a database error if session validation fails.
	 *
	 * @param string|null $session_id The UUID of the session to check, or null to use the current session ID.
	 * @param string|array|null $type The type(s) of session to validate against, or null for default types.
	 * @param bool $touch_updated_date Whether to update the session's last accessed date.
	 * @return bool True if the session is valid, false otherwise.
	 * @throws DBError If session validation fails due to a database error.
	 */
	function Check( $session_id = null, $type = null, $touch_updated_date = true ) {
		global $profiler;
		$profiler->startTimer( "Authentication::Check()" );

		if ( $type == '' ) {
			$type = [ 'USER_NAME', 'USER_NAME_MULTIFACTOR' ];
		}

		if ( is_array( $type ) == false ) {
			$type = [ $type ];
		}

		//Support session_ids passed by cookie, post, and get.
		if ( $session_id == '' ) {
			$session_id = $this->getCurrentSessionID( $type[0] );
		}

		Debug::text( 'Session ID: ' . $session_id . ' Type: ' . implode( ',', $type ) . ' IP Address: ' . Misc::getRemoteIPAddress() . ' URL: ' . $_SERVER['REQUEST_URI'] . ' Touch Updated Date: ' . (int)$touch_updated_date, __FILE__, __LINE__, __METHOD__, 10 );
		//Checks session cookie, returns object_id;
		if ( isset( $session_id ) ) {
			/*
				Bind session ID to IP address to aid in preventing session ID theft,
				if this starts to cause problems
				for users behind load balancing proxies, allow them to choose to
				bind session IDs to just the first 1-3 quads of their IP address
				as well as the SHA1 of their user-agent string.
				Could also use "behind proxy IP address" if one is supplied.
			*/
			try {
				$this->setSessionID( $session_id );
				$this->setIPAddress();

				foreach ( $type as $key => $type_id ) {
					if ( is_numeric( $type_id ) == false ) {
						$type[$key] = $this->getTypeIDByName( $type_id );
					}
				}

				$result = $this->Read( $type );

				if ( is_array( $result ) === true ) {
					if ( PRODUCTION == true && $result['ip_address'] != $this->getIPAddress() ) {
						Debug::text( 'NOTICE: IP Address has changed for existing session... Original IP: ' . $result['ip_address'] . ' Current IP: ' . $this->getIPAddress() . ' isSSL: ' . (int)Misc::isSSL( true ), __FILE__, __LINE__, __METHOD__, 10 );
						//When using SSL, we don't care if the IP address has changed, as the session should still be secure.
						//This allows sessions to work across load balancing routers, or between mobile/wifi connections, which can change 100% of the IP address (so close matches are useless anyways)
						if ( Misc::isSSL( true ) != true ) {
							//When not using SSL there is no 100% method of preventing session hijacking, so just insist that IP addresses match exactly as its as close as we can get.
							Debug::text( 'Not using SSL, IP addresses must match exactly...', __FILE__, __LINE__, __METHOD__, 10 );

							return false;
						}
					}

					//Only check user agent if we know its a web-browser, and definitely not when its an API or Mobile App, as the user agent may change between SOAP/REST libraries or App versions.
					// Found cases where some web browser change the user agent immediately after login. ie:
					//   On Login: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36 Agency/90.8.2537.38
					//   After Login: Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/107.0.0.0 Safari/537.36
					//   **Not sure what the significance of "Agency/90.8.2537.38" at the end means.
					//
					//To trigger this, the user agent and IP address must change to avoid the above case of user agents changing.
					if ( PRODUCTION == true && $result['client_id'] == 'browser-timetrex' && $result['user_agent'] != $this->getUserAgent() ) {
						Debug::text( 'NOTICE: User Agent changed for existing session... Original: ' . $result['user_agent'] . ' Current: ' . $this->getUserAgent(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $result['ip_address'] != $this->getIPAddress() ) {
							Debug::text( '  WARNING: IP Address and User Agent changed. Denying session!', __FILE__, __LINE__, __METHOD__, 10 );
							return false;
						}
					}

					if( $this->setObjectFromArray( $result ) === true ) {
						//touch UpdatedDate in most cases, however when calling PING() we don't want to do this.
						if ( $touch_updated_date !== false ) {
							//Reduce contention and traffic on the session table by only touching the updated_date every 120 +/- rand( 0, 60 ) seconds.
							//Especially helpful for things like the dashboard that trigger many async calls.
							if ( ( time() - $this->getUpdatedDate() ) > ( 120 + rand( 0, 60 ) ) ) {
								Debug::text( '  Touching updated date due to more than 120s...', __FILE__, __LINE__, __METHOD__, 10 );
								$this->Update();
							}
						}

						$profiler->stopTimer( "Authentication::Check()" );

						return true;
					}
				}
			} catch ( Exception $e ) {
				//Database not initialized, or some error, redirect to Install page.
				throw new DBError( $e, 'DBInitialize' );
			}
		}

		$profiler->stopTimer( "Authentication::Check()" );

		return false;
	}

	/**
	 * Logs out all users associated with a given company by deleting their authentication records.
	 * Optionally targets a specific client ID if provided.
	 *
	 * @param string $company_id The unique identifier of the company whose users are to be logged out.
	 * @param string|null $client_id Optional client identifier to target specific client sessions.
	 * @return bool Always returns true, indicating the operation was attempted.
	 * @throws DBError If a database error occurs during the logout process.
	 */
	function logoutCompany( $company_id, $client_id = null ) {
		$ph = [
				'company_id' => TTUUID::castUUID( $company_id ),
				'type_id'    => (int)$this->getTypeIDByName( 'USER_NAME' ),
		];

		try {
			Debug::text( 'Logging out entire company ID: ' . $company_id .' Client ID: '. $client_id, __FILE__, __LINE__, __METHOD__, 10 );
			$query = 'DELETE FROM authentication as a USING users as b WHERE a.object_id = b.id AND b.company_id = ? AND a.type_id = ? ';
			if ( isset( $client_id ) && !empty( $client_id ) ) {
				$ph[] = (string)$client_id;
				$query .= ' AND a.client_id = ? ';
			}

			$this->db->Execute( $query, $ph );
			$this->cache->clean( 'authentication' );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	/**
	 * Logs out all sessions associated with a specific user, typically after a password reset or change.
	 * Can optionally preserve the current session to avoid disrupting the user performing the action.
	 *
	 * @param string $object_id UUID of the user whose sessions are to be logged out.
	 * @param array|int|string|null $type_id Session type(s) to target for logout, defaults to common session types if not specified.
	 * @param bool $ignore_current_session Whether to preserve the session initiating the logout, defaulting to true.
	 * @return bool Always returns true, indicating the logout process was initiated.
	 * @throws DBError If a database error occurs during the logout process.
	 */
	function logoutUser( $object_id, $type_id = [ 800, 810, 0 ], $client_id = [ 'browser-timetrex', 'app-timetrex' ], $ignore_current_session = true ) {
		if ( $type_id !== null && is_array( $type_id ) === false ) {
			$type_id = [ $type_id ];
		}

		if ( $client_id !== null && is_array( $client_id ) === false ) {
			$client_id = [ $client_id ];
		}

		$delete_all_sessions = $type_id === null && $client_id === null;

		$session_id = [];

		if ( $ignore_current_session == true ) {
			//logoutUser() is generally called outside the context/scope of global $authentication and acts as a static method.
			//This is because supervisors can change other users passwords, and then we need to log out the target user.
			//Because of that we need to see if the user being logged out is the currently logged-in user.
			global $current_user;
			if ( is_object( $current_user ) && $current_user->getId() == $object_id ) {
				//If the user being logged out is the currently logged-in user, do not log out their current session or the app session that recently authenticated this session.
				$current_session_id = $this->getCurrentSessionID( $type_id[0] ?? 800 );
				$session_id[] = $this->encryptSessionID( $current_session_id );

				$authentication = new Authentication();
				$authentication->setAndReadForMultiFactor( $current_session_id );
				$other_json = $authentication->getOtherJSON();
				//Make sure we do not log them out if their most recent authenticated app session.
				if ( isset( $other_json['mfa']['recent_authenticated_device_session_id'] ) ) {
					$session_id[] = $other_json['mfa']['recent_authenticated_device_session_id'];
				}
			} else {
				//If the user being logged out is NOT the currently logged-in user, then this is probably a supervisor changing the password of another user.
				//We do not want to log out the app in this scenario if the user has mfa enabled, otherwise we would lock them out.
				if ( is_array( $client_id ) == true && in_array( 'app-timetrex', $client_id ) ) {
					$uflf = TTnew( 'UserListFactory' ); /** @var UserListFactory $uflf */
					$uflf->getById( $object_id );
					if ( $uflf->getRecordCount() > 0 ) {
						$user_obj = $uflf->getCurrent(); /** @var UserFactory $user_obj */
						if ( $user_obj->getMultiFactorType() > 0 ) {
							$array_key = array_search( 'app-timetrex', $client_id );
							if ( $array_key !== false ) {
								unset( $client_id[$array_key] );
							}
						}
					}
				}
			}
		}

		$ph = [
				'object_id'  => TTUUID::castUUID( $object_id )
		];

		try {
			$query = 'DELETE FROM authentication WHERE object_id = ?';

			if ( $delete_all_sessions === false ) { //Delete all deletes but the currently logged in browser and recently authenticated app session.
				if ( is_array( $type_id ) && count( $type_id ) > 0 ) {
					$type_ph = [];
					foreach ( $type_id as $type ) {
						$ph[] = $type;
						$type_ph[] = '?';
					}

					$query .= ' AND type_id in (' . implode( ',', $type_ph ) . ')';
				}

				if ( is_array( $client_id ) && count( $client_id ) > 0 ) {
					$client_ph = [];
					foreach ( $client_id as $client ) {
						$ph[] = $client;
						$client_ph[] = '?';
					}

					$query .= ' AND client_id in (' . implode( ',', $client_ph ) . ')';
				}
			}

			if ( is_array( $session_id ) && count( $session_id ) > 0 ) {
				$session_ph = [];

				foreach ( $session_id as $current_session ) {
					$ph[] = $current_session;
					$session_ph[] = '?';
				}
				$query .= ' AND session_id not in (' . implode( ',', $session_ph ) . ')';
			}

			$this->db->Execute( $query, $ph );
			//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10);
			$this->cache->clean( 'authentication' );
			Debug::text( 'Logging out all sessions for User ID: ' . $object_id . ' Affected Rows: ' . $this->db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10 );
		} catch ( Exception $e ) {
			throw new DBError( $e );
		}

		return true;
	}

	//
	//Functions to help check crendentials.
	//

	/**
	 * Retrieves a company object based on a user's identification and type.
	 * The type parameter determines the method of identification: by user name, user ID, phone ID, or job applicant name.
	 * If a matching record is found, the associated company object is returned.
	 *
	 * @param string $user_name The identifier for the user, which could be a username, user ID, phone ID, or job applicant name.
	 * @param string $type The type of identification to use, defaults to 'USER'. Other possible values are 'USER_ID', 'QUICK_PUNCH', 'JOB_APPLICANT'.
	 * @return mixed|bool The company object if found, or false if no matching record is found.
	 */
	function getCompanyObject( $user_name, $type = 'USER' ) {
		$type = strtoupper( $type );
		if ( $type == 'USER' ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByUserName( TTi18n::strtolower( $user_name ) );
		} else if ( $type == 'USER_ID' ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getById( $user_name );
		} else if ( $type == 'QUICK_PUNCH' ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByPhoneIdAndStatus( TTi18n::strtolower( $user_name ), 10 );
		} else if ( $type == 'JOB_APPLICANT' ) {
			$ulf = TTnew( 'JobApplicantListFactory' ); /** @var JobApplicantListFactory $ulf */
			$ulf->getByUserName( TTi18n::strtolower( $user_name ) );
		}

		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();
			if ( is_object( $u_obj ) ) {
				$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
				$clf->getById( $u_obj->getCompany() );
				if ( $clf->getRecordCount() == 1 ) {
					return $clf->getCurrent();
				}
			}
		}

		return false;
	}

	/**
	 * Checks the company status for a given username.
	 * Retrieves the company object associated with the user and returns its status.
	 * @param string $user_name The username to check the company status for.
	 * @return mixed The status of the company if found, or false otherwise.
	 */
	function checkCompanyStatus( $user_name ) {
		$company_obj = $this->getCompanyObject( $user_name, 'USER' );
		if ( is_object( $company_obj ) ) {
			//Return the actual status so we can do multiple checks.
			Debug::text( 'Company Status: ' . $company_obj->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );

			return $company_obj->getStatus();
		}

		return false;
	}

	/**
	 * Verifies the existence and login status of a user based on the username.
	 * Typically used for HTTP Authentication or Single Sign-On (SSO) mechanisms.
	 *
	 * @param string $user_name The username to check.
	 * @return bool True if the username exists and is enabled for login, false otherwise.
	 */
	function checkUsername( $user_name ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByUserNameAndEnableLogin( $user_name, true ); //Login Enabled
		foreach ( $ulf as $user ) {
			if ( TTi18n::strtolower( $user->getUsername() ) == TTi18n::strtolower( trim( $user_name ) ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Verifies user credentials against stored data.
	 * Optionally skips password verification if $skip_password_check is true.
	 * @param string $user_name The username to authenticate.
	 * @param string $password The password to validate against the username.
	 * @param bool $skip_password_check Whether to bypass password checking.
	 * @return bool True if authentication is successful, false otherwise.
	 */
	function checkPassword( $user_name, $password, $skip_password_check = false ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByUserNameAndEnableLogin( $user_name, true ); //Login Enabled
		foreach ( $ulf as $user ) { /** @var UserFactory $user */
			if ( $skip_password_check == true || $user->checkPassword( $password ) == true ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Validates a phone password against the user's stored phone password.
	 *
	 * @param int $phone_id The unique identifier for the phone.
	 * @param string $password The password to validate.
	 * @return bool True if the password is correct, false otherwise.
	 */
	function checkPhonePassword( $phone_id, $password ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

		$ulf->getByPhoneIdAndStatus( $phone_id, 10 );

		foreach ( $ulf as $user ) {
			if ( $user->checkPhonePassword( $password ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Authenticates a job applicant's password.
	 *
	 * Validates the provided password against the job applicant's stored password.
	 *
	 * @param string $user_name The username of the job applicant.
	 * @param string $password The password to validate.
	 * @return bool True if the password is correct, false otherwise.
	 */
	function checkApplicantPassword( $user_name, $password ) {
		$ulf = TTnew( 'JobApplicantListFactory' ); /** @var JobApplicantListFactory $ulf */

		$ulf->getByUserName( $user_name );

		foreach ( $ulf as $user ) {
			if ( $user->checkPassword( $password ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Checks the validity of an iButton ID and sets user session details if active.
	 *
	 * iButtons are microchips encased in stainless steel used for secure user identification.
	 * Validates the iButton ID against active users in the system, specifically those with a status code of 10.
	 * Upon finding a match, the user's ID and object are stored in the session for subsequent use.
	 *
	 * @param string $id Unique identifier of the iButton.
	 * @return bool True if an active user with the given iButton ID is found, otherwise false.
	 */
	function checkIButton( $id ) {
		$uilf = TTnew( 'UserIdentificationListFactory' ); /** @var UserIdentificationListFactory $uilf */
		$uilf->getByTypeIdAndValue( 10, $id );
		if ( $uilf->getRecordCount() > 0 ) {
			foreach ( $uilf as $ui_obj ) {
				if ( is_object( $ui_obj->getUserObject() ) && $ui_obj->getUserObject()->getStatus() == 10 ) {
					$this->setObjectID( $ui_obj->getUser() );
					$this->setObject( $ui_obj->getUserObject() );

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * Validates an employee's number against a given object ID.
	 *
	 * @param string $object_id The unique identifier of the object, typically a user.
	 * @param string $employee_number The employee number to validate.
	 * @return bool True if the employee number matches the object ID, false otherwise.
	 */
	function checkBarcode( $object_id, $employee_number ) {
		//Use UserFactory to set name.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

		$ulf->getByIdAndStatus( $object_id, 10 );

		foreach ( $ulf as $user ) { /** @var UserFactory $user */
			if ( $user->checkEmployeeNumber( $employee_number ) ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Validates a fingerprint against user records.
	 *
	 * Checks if a user with the given UUID has a status of 10, indicating an active user.
	 * If a match is found, the user's ID and object are set for the session.
	 *
	 * @param string $id The UUID of the user to validate.
	 * @return bool True if the user is valid and active, false otherwise.
	 */
	function checkFingerPrint( $id ) {
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

		$ulf->getByIdAndStatus( $id, 10 );

		foreach ( $ulf as $user ) {
			if ( $user->getId() == $id ) {
				$this->setObjectID( $user->getID() );
				$this->setObject( $user );

				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * Retrieves the client identifier from the HTTP request headers.
	 * The client ID is used to determine the type of client making the request,
	 * which is essential for enabling or disabling CSRF protection accordingly.
	 *
	 * @return string The client ID value from the HTTP header, if set; otherwise, an empty string.
	 */
	function getClientIDHeader() {
		if ( isset( $_SERVER['HTTP_X_CLIENT_ID'] ) && $_SERVER['HTTP_X_CLIENT_ID'] != '' ) {
			return trim( $_SERVER['HTTP_X_CLIENT_ID'] );
		} else if ( isset( $_POST['X-Client-ID'] ) && $_POST['X-Client-ID'] != '' ) { //Need to read X-Client-ID from POST variables so Global.APIFileDownload() works.
			return trim( $_POST['X-Client-ID'] );
		} else if ( Misc::isMobileAppUserAgent() == true ) {
			//Check if its Kiosk or Single Employee.
			$parsed_user_agent = Misc::parseMobileAppUserAgent();
			if ( isset( $parsed_user_agent['station_type'] ) && (int)$parsed_user_agent['station_type'] == 65 ) { //65=Kiosk
				return 'App-TimeTrex-Kiosk'; //Kiosk Mode.
			}

			return 'App-TimeTrex'; //Single Employee
		} else {
			if ( isset( $_SERVER['SCRIPT_NAME'] ) && $_SERVER['SCRIPT_NAME'] != '' ) {
				$script_name = $_SERVER['SCRIPT_NAME'];

				//If the SCRIPT_NAME is something like upload_file.php, or APIGlobal.js.php, assume its the JSON API
				//   This is also set in parseEndPointID() and getClientIDHeader()
				//'api/webauthn' returns browser-timetrex. Note the webauthn API does not go through our normal API path.
				if ( $script_name == '' || strpos( $script_name, 'api/saml' ) == true || strpos( $script_name, 'api/webauthn' ) == true || ( strpos( $script_name, 'api' ) === false && strpos( $script_name, 'soap/server.php' ) === false ) ) {
					return 'Browser-TimeTrex';
				}
			}
		}

		return 'API'; //Default to API Client-ID
	}

	/**
	 * Validates the CSRF token by comparing the token received in the HTTP header with the one stored in the cookie.
	 * Ensures that the request originates from the authenticated user, mitigating the risk of cross-site request forgery attacks.
	 * Utilizes the "Double Submit Cookie" method where the token is sent both as a cookie and a custom HTTP header.
	 *
	 * @see https://en.wikipedia.org/w/index.php?title=Cross-site_request_forgery#Cookie-to-header_token
	 * @see https://cheatsheetseries.owasp.org/cheatsheets/Cross-Site_Request_Forgery_Prevention_Cheat_Sheet.html
	 * @return bool True if the CSRF token is valid, false otherwise.
	 */
	function checkValidCSRFToken() {
		global $config_vars;

		$client_id_header = $this->getClientIDHeader();

		if ( $client_id_header != 'API' && $client_id_header != 'App-TimeTrex' && $client_id_header != 'App-TimeTrex-Kiosk' && $client_id_header != 'App-TimeTrex-AGI'
				&& ( !isset( $config_vars['other']['enable_csrf_validation'] ) || ( isset( $config_vars['other']['enable_csrf_validation'] ) && $config_vars['other']['enable_csrf_validation'] == true ) )
				&& ( !isset( $config_vars['other']['installer_enabled'] ) || ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] != true ) ) //Disable CSRF if installer is enabled, because TTPassword::getPasswordSalt() has the potential to change at anytime.
		) {
			if ( isset( $_SERVER['HTTP_X_CSRF_TOKEN'] ) && $_SERVER['HTTP_X_CSRF_TOKEN'] != '' ) {
				$csrf_token_header = trim( $_SERVER['HTTP_X_CSRF_TOKEN'] );
			} else {
				if ( isset( $_POST['X-CSRF-Token'] ) && $_POST['X-CSRF-Token'] != '' ) { //Global.APIFileDownload() needs to be able to send the token by POST or GET.
					$csrf_token_header = trim( $_POST['X-CSRF-Token'] );
				} else if ( isset( $_GET['X-CSRF-Token'] ) && $_GET['X-CSRF-Token'] != '' ) { //Some send_file.php calls need to be able to send the token by GET.
					$csrf_token_header = trim( $_GET['X-CSRF-Token'] );
				} else {
					$csrf_token_header = false;
				}
			}

			if ( isset( $_COOKIE['CSRF-Token'] ) && $_COOKIE['CSRF-Token'] != '' ) {
				$csrf_token_cookie = trim( $_COOKIE['CSRF-Token'] );
			} else {
				$csrf_token_cookie = false;
			}

			if ( $csrf_token_header != '' && $csrf_token_header == $csrf_token_cookie ) {
				//CSRF token is hashed with a secret key, so full token is: <TOKEN>-<HASHED WITH SECRET KEY TOKEN> -- Therefore make sure that the hashed token matches with our secret key.
				$split_csrf_token = explode( '-', $csrf_token_header ); //0=Token value, 1=Salted token value.
				if ( is_array( $split_csrf_token ) && count( $split_csrf_token ) == 2 && $split_csrf_token[1] == sha1( $split_csrf_token[0] . TTPassword::getPasswordSalt() ) ) {
					return true;
				} else {
					Debug::Text( ' CSRF token value does not match hashed value! Client-ID: ' . $client_id_header . ' CSRF Token: Header: ' . $csrf_token_header . ' Cookie: ' . $csrf_token_cookie, __FILE__, __LINE__, __METHOD__, 10 );

					return false;
				}
			} else {
				Debug::Text( ' CSRF token does not match! Client-ID: ' . $client_id_header . ' CSRF Token: Header: ' . $csrf_token_header . ' Cookie: ' . $csrf_token_cookie .' Total Cookies: '. count( $_COOKIE ), __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		} else {
			return true; //Not a CSRF vulnerable end-point
		}
	}

	/**
	 * Verifies and clears a one-time authentication flag after a critical action.
	 * This ensures that the action cannot be repeated without re-authentication.
	 *
	 * @return bool Always returns true, indicating the flag has been cleared.
	 */
	function reauthenticationActionCompleted() {
		return $this->clearMultiFactorDataFlags( [ 'one_time_auth' ] );
	}

	/**
	 * Clears specified flags from the multi-factor authentication data to prevent replay attacks.
	 * This is typically called after a multi-factor authentication event has been completed.
	 *
	 * @param array $flags_to_clear List of flags to be cleared from the multi-factor data.
	 * @param bool $force_update Whether to force an update to the database regardless of changes.
	 * @return bool Always returns true, indicating the flags were cleared or no action was needed.
	 */
	function clearMultiFactorDataFlags( $flags_to_clear = [], $force_update = false ) {
		//Clearing multifactor data after it has been used to prevent replay attacks and remove one time flags.

		$other_json = $this->getOtherJSON();

		//Remove all data from $other_json other than flags to clear keys
		if ( isset( $other_json['mfa'] ) == true && is_array( $other_json['mfa'] ) == true && is_array( $flags_to_clear ) == true ) {
			$other_json['mfa'] = array_diff_key( $other_json['mfa'], array_flip( $flags_to_clear ) );
			$this->setOtherJSON( $other_json );

			$this->Update();
		}

		if ( $force_update == true ) {
			$this->Update();
		}

		return true;
	}

	/**
	 * Determines if a user is permitted to bypass multi-factor authentication based on device trust.
	 *
	 * @param string $user_id The unique identifier of the user.
	 * @param string $device_id The unique identifier of the user's device.
	 * @return bool True if the user is allowed to bypass MFA, false otherwise.
	 */
	function isUserAllowedBypassMFA( $user_id, $device_id ) {
		global $config_vars;
		if ( isset( $config_vars['other']['disable_mfa'] ) && $config_vars['other']['disable_mfa'] == true ) {
			Debug::Text( '  MFA is disabled, allowing user to bypass MFA...', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		$atdlf = TTnew( 'AuthenticationTrustedDeviceListFactory' ); /** @var AuthenticationTrustedDeviceListFactory $atdlf */
		$atdlf->getByUserIdAndDeviceId( $user_id, $this->encryptSessionID( $device_id ) );
		if ( $atdlf->getRecordCount() == 1 ) { /** @var AuthenticationTrustedDeviceFactory $atd_obj */
			$atd_obj = $atdlf->getCurrent();
			if ( $atd_obj->checkIPAddress( Misc::getRemoteIPAddress(), $atd_obj->getIPAddress() ) === true ) {
				return true;
			} else {
				Debug::Text( '  Trusted device found, but does not match IP address... Current IP: '. Misc::getRemoteIPAddress() .' Trusted Device IP: '. $atd_obj->getIPAddress(), __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( '  Trusted device not found: '. $device_id .' Encrypted: '. $this->encryptSessionID( $device_id ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		//If trusted device cookie exists, but is invalid, delete it. This way the user will be prompted to trust the device again.
		if ( isset( $_COOKIE['TrustedDevice'] ) ) {
			setcookie( 'TrustedDevice', '', null, Environment::getCookieBaseURL(), '', Misc::isSSL( true ) );
		}

		return false;
	}

	/**
	 * Enables or disables the start of multi-factor authentication listening.
	 *
	 * @param bool $enable True to enable, false to disable.
	 * @return void
	 */
	function setMfaStartListen( $enable ) {
		$this->mfa_start_listen = (bool)$enable;
	}

	/**
	 * Retrieves the status of multi-factor authentication start listening.
	 *
	 * @return bool The status indicating whether MFA start listening is enabled.
	 */
	function getMfaStartListen() {
		return $this->mfa_start_listen;
	}


	/**
	 * Sets a trusted device cookie if not already set or if existing cookie is valid.
	 * If the cookie is expired or not present, generates a new device ID, creates a trusted device record,
	 * and sets a new cookie. If the user agent is known, it is stored; otherwise, the raw user agent string is saved.
	 * The trusted device is valid for 30 days.
	 *
	 * @return bool Always returns true, indicating the cookie was set or already valid.
	 */
	function setTrustedDevice() {
		//If the TrustedDevice cookie is already set and valid for this user, don't do anything.
		if ( isset( $_COOKIE['TrustedDevice'] ) ) {
			$atdlf = TTnew( 'AuthenticationTrustedDeviceListFactory' ); /** @var AuthenticationTrustedDeviceListFactory $atdlf */
			$atdlf->getByUserIdAndDeviceId( $this->getObjectID(), $this->encryptSessionID( (string)$_COOKIE['TrustedDevice'] ) );
			if ( $atdlf->getRecordCount() > 0 ) {
				$atd_obj = $atdlf->getCurrent();
				if ( ( TTDate::getTime() - $atd_obj->getCreatedDate() ) < ( 86400 * 30 ) ) { //30 days
					Debug::Text( 'Trusted device cookie already set and valid for this user...', __FILE__, __LINE__, __METHOD__, 10 );
					return true;
				}
			} else {
				//Delete expired cookie.
				setcookie( 'TrustedDevice', '', null, Environment::getCookieBaseURL(), '', Misc::isSSL( true ) );
			}
		}

		//User doesn't have a TrustedDevice cookie set, and we need to create one.
		$device_id = $this->genSessionID();

		$atd_obj = TTnew( 'AuthenticationTrustedDeviceFactory' ); /** @var AuthenticationTrustedDeviceFactory $atd_obj */
		$atd_obj->setUser( $this->getObjectID() );
		$atd_obj->setDeviceID( $this->encryptSessionID( $device_id ) );

		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			require_once( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
			$browser = new Browser( $_SERVER['HTTP_USER_AGENT'] );
			$user_agent = $browser->getBrowser();
			if ( $user_agent === 'unknown' ) {
				$atd_obj->setDeviceUserAgent( $_SERVER['HTTP_USER_AGENT'] );
			} else {
				$atd_obj->setDeviceUserAgent( $browser->getBrowser() .' ('. $browser->getPlatform() .')' );
			}
		}

		$atd_obj->setIPAddress( Misc::getRemoteIPAddress() );
		$atd_obj->setLocation( Misc::getLocationOfIPAddress() );

		if ( $atd_obj->isValid() ) {
			$atd_obj->Save();
			//Valid for 30 days.
			setcookie( 'TrustedDevice', $device_id, ( TTDate::getTime() + ( 86400 * 30 ) ), Environment::getCookieBaseURL(), '', Misc::isSSL( true ) );
		}

		return true;
	}


	/**
	 * Constructs an array of session and authentication details for API response.
	 * @param string $user_action_message Optional message related to user actions during authentication.
	 * @return array Associative array of authentication response data.
	 */
	function getAuthenticationResponseData( $user_action_message = '' )
	{
		return [
				'status'       => true,
				'session_id'   => $this->getSessionId(),
				'session_type' => $this->getTypeName(),
				'mfa'          => [
						'step'                => $this->getMFALoginStep(),
						'type_id'             => $this->getMFAType(),
						'user_action_message' => TTi18n::getText( 'Please check your device to verify your identity' ),
				],
		];
	}

	/**
	 * Retrieves the current multi-factor authentication (MFA) login step.
	 * The step indicates the current stage in the MFA process, such as 'start_listen'.
	 * If MFA is not initiated, it returns false.
	 *
	 * @return string|false The MFA login step or false if MFA is not started.
	 */
	function getMFALoginStep() {
		return $this->getMFAStartListen() ? 'start_listen' : false;
	}

	/**
	 * Determines the next step in the multi-factor authentication process based on the type ID.
	 *
	 * @param int $mfa_type_id The type ID of the multi-factor authentication method.
	 * @return string The step identifier for the multi-factor authentication process.
	 */
	function getMFAReauthenticationStep( $mfa_type_id ) {
		$step = '';

		if ( $mfa_type_id == 100 ) {
			$step = 'webauthn'; //Does not go through our MFA modal, browser will handle it.
		} else if ( $mfa_type_id == 1000 ) {
			$step = 'saml'; //Uses MFA modal to give user information about new tab opening for SAML authentication.
		} else {
			$step = 'password'; //Uses MFA modal to ask for password, and for MFA app will also ask them to verify with the TimeTrex app.
		}

		return $step;
	}

	/**
	 * Constructs the URL for SAML Single Sign-On (SSO) service.
	 * @param string $user_name The username to be included in the SSO request.
	 * @param bool $close_window A flag indicating whether to close the window after SSO completion.
	 * @return string The fully constructed SSO URL.
	 */
	function getSAMLSSOURL( $user_name, $close_window ) {
		return Environment::getAPIURL( 'saml' ) . '?action=sso&user_name=' . $user_name . '&close_window=' . (int)$close_window;
	}
}
?>
