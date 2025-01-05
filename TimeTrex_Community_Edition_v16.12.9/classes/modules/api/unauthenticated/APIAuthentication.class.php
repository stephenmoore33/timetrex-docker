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
 * @package API\UnAuthenticated
 */
class APIAuthentication extends APIFactory {
	protected $main_class = 'Authentication';

	/**
	 * APIAuthentication constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * @param null $user_name
	 * @param null $password
	 * @return array
	 */
	function PunchLogin( $user_name = null, $password = null ) {
		global $config_vars, $authentication;
		Debug::Text( 'Quick Punch ID: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == 1 ) || ( isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] == 1 ) ) {
			Debug::text( 'WARNING: Installer is enabled... Normal logins are disabled!', __FILE__, __LINE__, __METHOD__, 10 );
			//When installer is enabled, just display down for maintenance message to user if they try to login.
			$error_message = TTi18n::gettext( '%1 is currently undergoing maintenance. We apologize for any inconvenience this may cause, please try again later. (%2)', [ APPLICATION_NAME, ( ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == 1 ) ? 'INSTALL' : 'MAINT' ) ] );
			$validator_obj = new Validator();
			$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];
			$validator_obj->isTrue( 'user_name', false, $error_message );
			$validator = [];
			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
		}
		if ( isset( $config_vars['other']['web_session_expire'] ) && $config_vars['other']['web_session_expire'] != '' ) {
			$authentication->setEnableExpireSession( (int)$config_vars['other']['web_session_expire'] );
		}

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getByPhoneID( $user_name );
		if ( $clf->getRecordCount() == 1 ) {
			$c_obj = $clf->getCurrent();

			if ( isset( $c_obj ) && is_object( $c_obj ) && $c_obj->getProductEdition() == 10 ) {
				$error_message = TTi18n::gettext( 'Quick Punch functionality is only available in %1 Professional, Corporate or Enterprise Editions.', [ APPLICATION_NAME ] );
				$validator_obj = new Validator();
				$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];
				//$validator_obj->isTrue( 'quick_punch_password', false, $error_message );
				$validator_obj->isTrue( 'quick_punch_id', false, $error_message );
				$validator = [];
				$validator[0] = $validator_obj->getErrorsArray();

				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
			}
		} else {
			$c_obj = false;
		}

		//Checks user_name/password
		$password_result = false;
		$user_name = trim( $user_name );
		if ( $user_name != '' && $password != '' && ( is_object( $c_obj ) && in_array( $c_obj->getStatus(), [ 10, 20 ] ) && getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL && $c_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) ) { //Allow QuickPunch logins when company is on hold so employees can still punch in/out.
			$password_result = $authentication->Login( $user_name, $password, 'QUICK_PUNCH_ID' );
		}

		if ( $password_result === true ) {
			//Creating stations is handled in javascript instead now, as we need to pass in browser fingerprint data in some cases.
			//$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			//$clf->getByID( $authentication->getObject()->getCompany() );
			//$current_company = $clf->getCurrent();
			//unset( $clf );
			//
			//$create_new_station = false;
			////If this is a new station, insert it now.
			//if ( isset( $_COOKIE['StationID'] ) ) {
			//	Debug::text( 'Station ID Cookie found! ' . $_COOKIE['StationID'], __FILE__, __LINE__, __METHOD__, 10 );
			//
			//	$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */
			//	$slf->getByStationIdandCompanyId( $_COOKIE['StationID'], $current_company->getId() );
			//	$current_station = $slf->getCurrent();
			//	unset( $slf );
			//
			//	if ( $current_station->isNew() ) {
			//		Debug::text( 'Station ID is NOT IN DB!! ' . $_COOKIE['StationID'], __FILE__, __LINE__, __METHOD__, 10 );
			//		$create_new_station = true;
			//	}
			//} else {
			//	$create_new_station = true;
			//}

			//if ( $create_new_station == true ) {
			//	//Insert new station
			//	$sf = TTnew( 'StationFactory' ); /** @var StationFactory $sf */
			//
			//	$sf->setCompany( $current_company->getId() );
			//	$sf->setStatus( 20 ); //Enabled
			//	if ( Misc::detectMobileBrowser() == false ) {
			//		Debug::text( 'PC Station device...', __FILE__, __LINE__, __METHOD__, 10 );
			//		$sf->setType( 10 ); //PC
			//	} else {
			//		$sf->setType( 26 ); //Mobile device web browser
			//		Debug::text( 'Mobile Station device...', __FILE__, __LINE__, __METHOD__, 10 );
			//	}
			//	$sf->setSource( Misc::getRemoteIPAddress() );
			//	$sf->setStation();
			//	$sf->setDescription( substr( $_SERVER['HTTP_USER_AGENT'], 0, 250 ) );
			//	if ( $sf->isValid() ) { //Standard Edition can't save mobile stations.
			//		if ( $sf->Save( false ) ) {
			//			$sf->setCookie();
			//		}
			//	}
			//}

			return [ 'SessionID' => $authentication->getSessionId() ];
		} else {
			$validator_obj = new Validator();
			$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];

			$error_column = 'quick_punch_id'; // match the correct input field in the html
			$error_message = TTi18n::gettext( 'Quick Punch ID or Password is incorrect' );
			//Get company status from user_name, so we can display messages for ONHOLD/Cancelled accounts.
			if ( is_object( $c_obj ) ) {
				//Allow QuickPunch when company is ON HOLD.
//				if ( $c_obj->getStatus() == 20 ) {
//					$error_message = TTi18n::gettext('Sorry, your company\'s account has been placed ON HOLD, please contact customer support immediately');
//				} else
				if ( $c_obj->getStatus() == 23 ) {
					$error_message = TTi18n::gettext( 'Sorry, your trial period has expired, please contact our sales department to reactivate your account' );
				} else if ( $c_obj->getStatus() == 28 ) {
					if ( $c_obj->getMigrateURL() != '' ) {
						$error_message = TTi18n::gettext( 'To better serve our customers your account has been migrated, please update your bookmarks to use the following URL from now on' ) . ': ' . 'http://' . $c_obj->getMigrateURL();
					} else {
						$error_message = TTi18n::gettext( 'To better serve our customers your account has been migrated, please contact customer support immediately.' );
					}
				} else if ( $c_obj->getStatus() == 30 ) {
					$error_message = TTi18n::gettext( 'Sorry, your company\'s account has been CANCELLED, please contact customer support if you believe this is an error' );
				}
			}
			$validator_obj->isTrue( $error_column, false, $error_message );
			$validator = [];
			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
		}
	}

	/**
	 * @param $user_name
	 * @param bool $close_window
	 * @return array|bool
	 */
	function getSessionTypeForLogin( $user_name, $close_window = false ) {
		global $config_vars;

		//Default login type
		$retval = [
				'session_type' => 'user_name',
				'mfa_type_id'  => 0,
				'user_id'      => TTUUID::getNotExistID(),
				'redirect_url' => ''
		];

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByUserName( $user_name );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
			$retval['user_id'] = $u_obj->getId();
			if ( ( isset( $config_vars['other']['disable_mfa'] ) && $config_vars['other']['disable_mfa'] == true ) || getTTProductEdition() < TT_PRODUCT_PROFESSIONAL ) {
				//Login is forced to use user_name/password and mfa_type_id 0 when MFA is disabled by the ini product edition is lower than PROFESSIONAL.
				Debug::Text( 'MFA is disabled. User has MFA enabled but the session has been forced to user_name (800)', __FILE__, __LINE__, __METHOD__, 10 );
			} else if ( $u_obj->getMultiFactorType() > 0 ) { //0 = Disabled
				$retval['session_type'] = 'user_name_multifactor';
				$retval['mfa_type_id'] = $u_obj->getMultiFactorType();
			}
		}

		if ( $retval['mfa_type_id'] == 100 ) {
			//If mfa_type_id is 100 set a cookie to mark that this devices has used passwordless login
			setcookie( 'PasskeyUsername', $user_name, ( time() + 9999999 ), Environment::getCookieBaseURL(), '', Misc::isSSL( true ) );
		}

		if ( $retval['mfa_type_id'] == 1000 ) {
			global $authentication;
			$retval['redirect_url'] = $authentication->getSAMLSSOURL( $user_name, $close_window );
		}

		return $this->returnHandler( $retval );
	}

	/**
	 * @return array|bool
	 */
	function sendMultiFactorNotification() {
		$authentication = new Authentication();
		$authentication->setAndReadForMultiFactor();

		$multi_factor_data = $authentication->getMultiFactorData();

		if ( $multi_factor_data == false ) {
			Debug::Text( 'No multifactor data found for session ID: ' . $authentication->getSessionId(), __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( false );
		}

		if ( $multi_factor_data['time'] === '' || ( TTDate::getTime() - $multi_factor_data['time'] ) > $authentication->mfa_timeout_seconds ) {
			Debug::Text( 'Multifactor data is expired, not sending notification', __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( false );
		}

		$user_agent = null;
		$user_agent_display = null;
		if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
			require_once( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
			$browser = new Browser( $_SERVER['HTTP_USER_AGENT'] );
			$user_agent = $browser->getBrowser();
			if ( $user_agent === 'unknown' ) {
				$user_agent_display = $_SERVER['HTTP_USER_AGENT'];
			} else {
				$agent_arr = [];

				if ( $browser->getBrowser() !== 'unknown' ) {
					$agent_arr[] = $browser->getBrowser();
				}

				if ( $browser->getVersion() !== 'unknown' ) {
					$agent_arr[] = 'v' . $browser->getVersion();
				}

				if ( $browser->getPlatform() !== 'unknown' ) {
					$agent_arr[] = TTi18n::getText( 'on ' ) . $browser->getPlatform();
				}

				$user_agent_display = implode( ' ', $agent_arr );
			}
			unset( $browser );
		}

		$payload = [
				'timetrex' => [
						'event' => [
								[
										'type' => 'multi_factor_authenticate',
										'data' => [
												'user_id'                 => $multi_factor_data['user_id'],
												'session_id'              => $multi_factor_data['session_id'],
												'key'                     => $multi_factor_data['key'],
												'login_timestamp'         => TTDate::getTime(),
												'login_timestamp_display' => TTDate::getDate( 'DATE+TIME', TTDate::getTime() ),
												'user_agent'              => $user_agent,
												'user_agent_display'      => $user_agent_display,
												'ip_address'              => Misc::getRemoteIPAddress(),
												'location'                => Misc::getLocationOfIPAddress(),
										],
								],
						],
				],
				'uri'      => 'multi_factor_authenticate', //Required for app to trigger proper event on its side.
		];

		Debug::Arr( $payload, '  Sending background notification to users app to authenticate multifactor...', __FILE__, __LINE__, __METHOD__, 10 );
		$notification_data = [
				'object_id'         => $authentication->getSessionId(),
				'user_id'           => $multi_factor_data['user_id'],
				'type_id'           => 'system',
				'object_type_id'    => 0,
				'priority'          => 1, //1=Highest
				'title_short'       => TTi18n::getText( 'Verify your identity' ),
				'body_short'        => TTi18n::getText( 'Are you logging into TimeTrex currently?' ),
				'payload'           => $payload,
				'time_to_live'      => $authentication->mfa_timeout_seconds,
				'device_id'         => [ 32768 ], //App Only.
				'save_notification' => false,
		];
		Notification::sendNotification( $notification_data );

		return $this->returnHandler( true );
	}

	/**
	 * Starts a 30 second listen loop waiting for a multifactor authentication response from validateMultiFactor().
	 * This loop runs for the device trying to log and that waits for a multifactor approval from another device or API request.
	 * @return bool
	 * @throws DBError
	 */
	public function listenForMultiFactorAuthentication( $enable_trusted_device = false ) {
		global $config_vars, $db, $cache;

		// When using memoryCaching, this long-running process will never get an updated cache from a different process during the listen loop.
		// But if technically caching is *always* enabled, even when disabled we switch to memoryCaching only mode.
		// So when caching is disabled in the .ini, disable it fully.
		// When caching is enabled, just disable memoryCaching, so cache invalidations can be picked up in this long-running process.
		// Note: $authentication->setAndReadForMultiFactor() already skips cache. But keeping this here as a safety net.
		if ( !isset( $config_vars['cache']['enable'] ) || $config_vars['cache']['enable'] == false ) {
			$cache->_caching = false;
		}
		$cache->_memoryCaching = false;

		Debug::Text( 'Multifactor enabled! Listening for auth response.', __FILE__, __LINE__, __METHOD__, 10 );
		$config_vars['database']['persistent_connections'] = true; //Force persistent connections so LISTEN/NOTIFY works properly.

		$authentication = new Authentication();
		$authentication->setAndReadForMultiFactor();
		$multi_factor_data = $authentication->getMultiFactorData();
		if ( $multi_factor_data == false ) {
			Debug::Text( 'No multifactor data found for session ID: ' . $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( [ 'status' => 'cancelled' ] );
		} else {
			Debug::Arr( $multi_factor_data, ' Initial Multifactor data for Session ID: ' . $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( isset( $multi_factor_data['time'] ) && ( TTDate::getTime() - $multi_factor_data['time'] < $authentication->mfa_timeout_seconds ) ) {
			//If current multifactor data is less than X minutes old and already authenticated, we not need to start a listen.
			if ( isset( $multi_factor_data['login_approved'] ) == true && $multi_factor_data['login_approved'] == true ) {
				Debug::Text( 'Multifactor data is already authenticated, not starting listen', __FILE__, __LINE__, __METHOD__, 10 );
				//login_approved is a one time flag to know if this session has already been authenticated, and if it has, we don't need to start a listen. The flag can now be cleared so future listen requests can start a new listen.
				$authentication->clearMultiFactorDataFlags( [ 'login_approved' ] );

				return $this->returnHandler( [ 'status' => 'completed' ] );
			}
		}

		Debug::text( '  Listening for Multifactor authentication. Started at: ' . TTDate::getTime(), __FILE__, __LINE__, __METHOD__, 10 );
		$db->Execute( 'LISTEN "multi_auth:' . $db->qStr( TTUUID::castUUID( $multi_factor_data['user_id'] ) ) . '"' );
		$i = 0;

		while ( $i < 30 ) {
			$_SERVER['REQUEST_TIME_FLOAT'] = microtime( true ); //This restarts the DEBUG time on each loop as if its a separate request. This helps prevent long request WARNING from being triggered like in reports.

			$auth_status = pg_get_notify( $db->_connectionID );
			if ( isset( $auth_status['payload'] ) && $auth_status['payload'] != '' ) {
				$mfa_response_payload = json_decode( $auth_status['payload'], true );

				if ( $mfa_response_payload['success'] == true ) {
					Debug::Text( ' PG Notify Auth Payload Received: ' . $auth_status['payload'], __FILE__, __LINE__, __METHOD__, 10 );
					if ( $mfa_response_payload['key'] == $multi_factor_data['expected_key'] ) {
						//Can continue with login.
						Debug::text( '  SUCCESS: Notify auth request accepted, login allowed. Received at: ' . TTDate::getTime(), __FILE__, __LINE__, __METHOD__, 10 );
						//Authenticaiton may have outdated data depending on how long listen has been running. Need to re-read it.
						$authentication = new Authentication();
						$authentication->setAndReadForMultiFactor();

						//Authentication may have outdated data depending on how long listen has been running. We may need to re-read it.
						//  **NOTE: When using database replication, its possible this data could still be out-dated. So we need to make sure we aren't writing any data to the authentication table here.
						//          If we do, then it should be wrapped in a transaction so its always reads/writes to/from the master database node.

						if ( $enable_trusted_device == true ) {
							$authentication->setTrustedDevice();
						}

						return $this->returnHandler( [ 'status' => 'completed' ] );
					} else {
						Debug::Text( '  FAILURE: Notify auth request failed as keys did not match, login denied', __FILE__, __LINE__, __METHOD__, 10 );

						return $this->returnHandler( [ 'status' => 'cancelled' ] );
					}
				} else {
					if ( isset( $mfa_response_payload['other_data']['restart_listen'] ) && $mfa_response_payload['other_data']['restart_listen'] == true ) {
						Debug::text( '  Listen cancelled early as a restart listen request was received...', __FILE__, __LINE__, __METHOD__, 10 );

						return $this->returnHandler( [ 'status' => 'restart_listen' ] );
					}

					Debug::Text( '  FAILURE: Notify auth request denied, login denied', __FILE__, __LINE__, __METHOD__, 10 );

					return $this->returnHandler( [ 'status' => 'cancelled' ] );
				}
			}

			$i++;
			sleep( 1 );
		}

		return $this->returnHandler( [ 'status' => 'restart_listen' ] );
	}

	/**
	 * Listens on the current user_id for a reauthentication request.
	 * Unlike listenForMultiFactorAuthentication, this only listens for a reauthentication request and returns the status.
	 * It does not log the user in or perform any other actions. This can be used to notify the frontend when a
	 * reauthentication has taken place, such as when a user re-authenticates via SAML in a separate tab.
	 * @return array|bool
	 */
	public function listenForReauthentication() {
		global $config_vars, $db;

		if ( $this->isLoggedIn() == false ) {
			Debug::Text( '  FAILURE: User is not logged in, listen loop denied', __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( [ 'status' => 'cancelled' ] );
		}

		$config_vars['database']['persistent_connections'] = true; //Force persistent connections so LISTEN/NOTIFY works properly.

		Debug::text( '  Listening for reauthentication. Started at: ' . TTDate::getTime(), __FILE__, __LINE__, __METHOD__, 10 );
		$db->Execute( 'LISTEN "reauth:' . $db->qStr( TTUUID::castUUID( $this->getCurrentUserObject()->getId() ) ) . '"' );
		$i = 0;

		while ( $i < 30 ) {
			$_SERVER['REQUEST_TIME_FLOAT'] = microtime( true ); //This restarts the DEBUG time on each loop as if its a separate request. This helps prevent long request WARNING from being triggered like in reports.

			$auth_status = pg_get_notify( $db->_connectionID );
			if ( isset( $auth_status['payload'] ) && $auth_status['payload'] != '' ) {
				$response_payload = json_decode( $auth_status['payload'], true );

				if ( $response_payload['success'] == true ) {
					return $this->returnHandler( [ 'status' => 'completed' ] );
				} else {
					Debug::Text( '  FAILURE: Notify reauth request denied.', __FILE__, __LINE__, __METHOD__, 10 );
					return $this->returnHandler( [ 'status' => 'cancelled' ] );
				}
			}

			$i++;
			sleep( 1 );
		}

		return $this->returnHandler( [ 'status' => 'restart_listen' ] );
	}

	/**
	 * This is called by the app/device that approves or denies the multi-factor authentication request.
	 * It then sends a SQL Notify message to listenForMultiFactorAuthentication() to let it know if the request was approved or denied.
	 * @param $validation_allowed bool
	 * @param $session_id string
	 * @param $key string
	 * @param null $other_data
	 * @return array|bool
	 * @throws DBError
	 */
	public function validateMultiFactor( $validation_allowed, $session_id, $key, $other_data = null ) {
		global $db;

		Debug::Text( 'Attempting to validate multifactor.', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( 'App sent a validation allowed response of: ' . Misc::HumanBoolean( $validation_allowed ), __FILE__, __LINE__, __METHOD__, 10 );

		$authentication = new Authentication();
		$authentication->setAndReadForMultiFactor( $session_id );

		$multi_factor_data = $authentication->getMultiFactorData();
		$other_json = $authentication->getOtherJSON();

		if ( $multi_factor_data !== false ) { //There is a pending multinfactor auth request.
			Debug::Arr( $multi_factor_data, 'Multifactor data: ', __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Arr( $other_json, 'Other JSON data: ', __FILE__, __LINE__, __METHOD__, 10 );

			$error_message = null;

			//App neede to be logged in to validate a session and the user_id needs to match for both sessions.
			if ( ( $this->isLoggedIn() == false || $this->getCurrentUserObject()->getId() != $multi_factor_data['user_id'] ) ) {
				Debug::Text( '  FAILURE: Session is not allowed to be validated, as the session attempting to validate is not logged in or user mismatch.', __FILE__, __LINE__, __METHOD__, 10 );
				$validation_allowed = false; //Not allowed to validate if the app is not logged in or users mismatch.

				//If we are requesting listen to be restarted for any reason, we do not need to send a validation error for user not being logged in.
				//This is because there is a legitimate scenario where the user is not yet logged in but the listen needs to be restarted.
				if ( isset( $other_data['restart_listen'] ) == false || $other_data['restart_listen'] == false ) {
					//Restart listen was not requested or is false, so send a validation error that the user is not logged in.
					$error_message = TTi18n::getText( 'Unable to validate identity, please login and try again.' ); //Session is not allowed to be validated, as the session attempting to validate is not logged in or user mismatch.
				}
			}

			if ( ( $key !== $multi_factor_data['expected_key'] || $multi_factor_data['expected_key'] == false ) ) {
				Debug::Text( '  FAILURE: Session is not allowed to be validated, as the key does not match or is invalid.', __FILE__, __LINE__, __METHOD__, 10 );
				$error_message = TTi18n::getText( 'Unable to validate identity due to key mismatch, please try again.' ); //Session is not allowed to be validated, there was a authentication mismatch.
				$validation_allowed = false;
			}

			if ( ( $multi_factor_data['time'] == false || ( TTDate::getTime() - $multi_factor_data['time'] ) > $authentication->mfa_timeout_seconds ) ) {
				Debug::Text( '  FAILURE: Session is not allowed to be validated, as the the multifactor attempt has expired.', __FILE__, __LINE__, __METHOD__, 10 );
				$error_message = TTi18n::getText( 'Identity validation request has expired, please login again.' ); //Session is not allowed to be validated, as the the multifactor attempt has expired.
				$validation_allowed = false;
			}

			if ( $validation_allowed == true ) {
				//If login is already approved we do not reauthenticate or modify the session as it already has been authenticated.
				if ( ( isset( $other_json['mfa']['login_approved'] ) == false || $other_json['mfa']['login_approved'] == false ) ) {
					Debug::text( 'Sending multifactor response: SUCCESS', __FILE__, __LINE__, __METHOD__, 10 );
					$other_json['mfa']['login_approved'] = true; //One time flag for API to check before starting listen, incase it was already approved.
					$other_json['mfa']['recent_authenticated_device_session_id'] = $authentication->encryptSessionID( $authentication->getCurrentSessionID( 800 ) ); //Store the session ID of the device that just authenticated this request.
					$authentication->setOtherJSON( $other_json );

					if ( $multi_factor_data['is_reauthentication_request'] === true ) { //This may just be a setup test or reauthentication, we do not want to upgrade the session type.
						$authentication->setReauthenticatedSession();
						TTLog::addEntry( $authentication->getObjectID(), 102, TTi18n::getText( 'From' ) . ': ' . Misc::getLocationOfIPAddress( $authentication->getIPAddress() ) . ' (' . $authentication->getIPAddress() . ') ' . TTi18n::getText( 'Type' ) . ': user_name_multifactor ' . TTi18n::getText( 'SessionID' ) . ': ' . $authentication->getSecureSessionID() . ' ' . TTi18n::getText( 'Client' ) . ': ' . $authentication->getClientID() . ' ' . TTi18n::getText( 'End Point' ) . ': ' . $authentication->getEndPointID() . ' ' . TTi18n::getText( 'ObjectID' ) . ': ' . $authentication->getObjectID() . ' ' . TTi18n::getText( 'MFA Type ID' ) . ': ' . $authentication->getMFAType(), $authentication->getObjectID(), 'authentication' ); //ReAuthenticate
					} else if ( $multi_factor_data['type_id'] == 0 || $multi_factor_data['type_id'] == 800 ) {
						$authentication->setType( 810 );

						//This updates the user table and not authentication table.
						$authentication->UpdateLastLoginDate( $multi_factor_data['user_id'] );

						//Login is fully successful, so add "Login" audit log entry.
						TTLog::addEntry( $authentication->getObjectID(), 100, TTi18n::getText( 'From' ) . ': ' . Misc::getLocationOfIPAddress( $authentication->getIPAddress() ) . ' (' . $authentication->getIPAddress() . ') ' . TTi18n::getText( 'Type' ) . ': user_name_multifactor ' . TTi18n::getText( 'SessionID' ) . ': ' . $authentication->getSecureSessionID() . ' ' . TTi18n::getText( 'Client' ) . ': ' . $authentication->getClientID() . ' ' . TTi18n::getText( 'End Point' ) . ': ' . $authentication->getEndPointID() . ' ' . TTi18n::getText( 'ObjectID' ) . ': ' . $authentication->getObjectID() . ' ' . TTi18n::getText( 'MFA Type ID' ) . ': ' . $authentication->getMFAType(), $authentication->getObjectID(), 'authentication' ); //Login
					} else {
						Debug::text( 'Not modifying session, as not reauthenticating and not valid Type ID to promote: ' . $multi_factor_data['type_id'], __FILE__, __LINE__, __METHOD__, 10 );
					}

					$authentication->clearMultiFactorDataFlags( [ 'key' ] ); //Key has been consumed and is no longer valid and cannot be re-used.
				} else {
					Debug::text( 'Login has alrady been approved, not updating session...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::text( 'NOTICE: $validation_allowed is false, not updating session!', __FILE__, __LINE__, __METHOD__, 10 );
			}

			$response = [
					'success'    => (bool)$validation_allowed,
					'key'        => preg_replace( '/[^a-zA-Z0-9]/', '', (string)$key ), //Sanitizing the key to only contain alphanumeric characters to help prevent SQL injection.
					'other_data' => [],
			];

			//Other data is optional but can contain further information/commands for various multifactor types.
			//It contains only specific data and does not allow arbitrary data to be passed in.
			if ( isset( $other_data['restart_listen'] ) && $other_data['restart_listen'] == true ) {
				$response['other_data']['restart_listen'] = true;
			}

			Debug::Arr( $response, 'Sending multifactor notify message.', __FILE__, __LINE__, __METHOD__, 10 );
			$db->Execute( 'NOTIFY "multi_auth:' . $db->qStr( TTUUID::castUUID( $multi_factor_data['user_id'] ) ) . '", ' . $db->qStr( json_encode( $response ) ) );

			if ( $error_message == null ) {
				return $this->returnHandler( true );
			} else {
				return $this->returnHandler( false, 'VALIDATION', $error_message );
			}
		} else {
			Debug::text( 'No multifactor data for session...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Failed to validate multifactor as the request may have expired.' ) );
	}

	/**
	 * Default username=NULL to prevent argument warnings messages if its not passed from the API.
	 * @param null $user_name
	 * @param null $password
	 * @param string $type
	 * @param bool $reauthenticate_only
	 * @return array|bool
	 */
	function Login( $user_name = null, $password = null, $type = 'USER_NAME', $reauthenticate_only = false ) {
		global $config_vars, $authentication;

		//Prevent: NOTICE(8): Array to string conversion below.
		if ( is_array( $user_name ) ) {
			$user_name = '';
		}

		if ( is_array( $password ) ) {
			$password = '';
		}

		Debug::text( 'User Name: ' . $user_name . ' Password Length: ' . strlen( $password ) . ' Type: ' . $type, __FILE__, __LINE__, __METHOD__, 10 );

		//Check if mobile app version is out-of-date and show a message to upgrade.
		if ( Misc::getMobileAppClientVersion() !== false ) {
			$api_client_station = new APIClientStationUnAuthenticated();
			if ( $api_client_station->isOutOfDateClientVersion() == true ) {
				//When installer is enabled, just display down for maintenance message to user if they try to login.
				$error_message = TTi18n::gettext( 'ERROR: App version is out-of-date and no longer supported, please upgrade...' );
				$validator_obj = new Validator();
				$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];
				$validator_obj->isTrue( 'user_name', false, $error_message );
				$validator = [];
				$validator[0] = $validator_obj->getErrorsArray();

				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
			}
			unset( $api_client_station );
		}

		if ( ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == 1 ) || ( isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] == 1 ) ) {
			Debug::text( 'WARNING: Installer is enabled... Normal logins are disabled!', __FILE__, __LINE__, __METHOD__, 10 );
			//When installer is enabled, just display down for maintenance message to user if they try to login.
			$error_message = TTi18n::gettext( '%1 is currently undergoing maintenance. We apologize for any inconvenience this may cause, please try again later. (%2)', [ APPLICATION_NAME, ( ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == 1 ) ? 'INSTALL' : 'MAINT' ) ] );
			$validator_obj = new Validator();
			$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];
			$validator_obj->isTrue( 'user_name', false, $error_message );
			$validator = [];
			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
		}

		if ( isset( $config_vars['other']['web_session_expire'] ) && $config_vars['other']['web_session_expire'] != '' ) {
			$authentication->setEnableExpireSession( (int)$config_vars['other']['web_session_expire'] );
		}

		$login_method = $this->stripReturnHandler( $this->getSessionTypeForLogin( $user_name ) );

		if ( strtolower( $type ) === 'user_name' ) {
			//Don't allow users to circumvent the multifactor auth process, by forcing 'user_name' login type when MFA is enabled.
			$type = $login_method['session_type'];
		}

		if ( $login_method['mfa_type_id'] == 50 ) {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'SAML authentication cannot be used through this API.' ) );
		}
		else if ( $login_method['mfa_type_id'] == 100 ) {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Passkeys authentication cannot be used through this API.' ) );
		}

		//Delete PasskeyUsername cookie if it exists, and user is logging in with a username/password.
		if ( isset( $_COOKIE['PasskeyUsername'] ) && ( strtolower( $type ) == 'user_name' || strtolower( $type ) == 'user_name_multifactor' ) ) {
			setcookie( 'PasskeyUsername', '', ( time() - 9999999 ), Environment::getCookieBaseURL(), '', Misc::isSSL( true ) );
		}

		if ( strtolower( $type ) === 'user_name_multifactor' && $authentication->getClientIDHeader() == 'App-TimeTrex' && Misc::getMobileAppClientVersion() != '' && version_compare( Misc::getMobileAppClientVersion(), '5.1.10', '<' ) == true ) {
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Please upgrade to a newer version of the app that supports multifactor authentication.' ) );
		}

		//Only allow reauthentication for the currently logged-in user.
		if ( $reauthenticate_only && ( $this->isLoggedIn() === false || $this->getCurrentUserObject()->checkUsername( $user_name ) === false ) ) {
			$validator_obj = new Validator();
			$validator_obj->isTrue( 'user_name', false, TTi18n::gettext( 'Authenticating for invalid employee.' ) );
			$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];
			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
		}

		if ( $authentication->Login( $user_name, $password, $type, true, $reauthenticate_only, $login_method['mfa_type_id'] ) === true ) {

			if ( strtolower( $type ) === 'user_name_multifactor' ) {
				Debug::text( 'MFA Login Success, Session ID: ' . $authentication->getSessionId(), __FILE__, __LINE__, __METHOD__, 10 );

				$authentication->generateMultiFactorAuthKey( $reauthenticate_only );

				return $this->returnHandler( $authentication->getAuthenticationResponseData( TTi18n::getText( 'Please check your device to verify your identity' ) ) );
			} else {
				Debug::text( 'Login Success, Session ID: ' . $authentication->getSessionId(), __FILE__, __LINE__, __METHOD__, 10 );

				//Return different data stucture to end-points that support MFA. Only Browser and Mobile App >=5.1.10 for now.
				if ( $authentication->getClientIDHeader() == 'Browser-TimeTrex' || ( $authentication->getClientIDHeader() == 'App-TimeTrex' && Misc::getMobileAppClientVersion() != '' && version_compare( Misc::getMobileAppClientVersion(), '5.1.10', '>=' ) == true ) ) {
					if ( $reauthenticate_only == true ) {
						$authentication->setReauthenticatedSession( true );
					}

					return $this->returnHandler( $authentication->getAuthenticationResponseData() );
				} else {
					Debug::text( '  Returning raw Session ID to legacy API/App: ' . $authentication->getSessionId(), __FILE__, __LINE__, __METHOD__, 10 );
					return $authentication->getSessionId(); //Legacy app versions don't support MFA, so just return the session ID.
				}
			}
		} else {
			$validator_obj = new Validator();
			$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];

			$error_column = 'user_name';
			if ( $reauthenticate_only == true ) {
				$error_message = TTi18n::gettext( 'Password is incorrect' );
			} else {
				$error_message = TTi18n::gettext( 'User Name or Password is incorrect' );
			}

			//Get company status from user_name, so we can display messages for ONHOLD/Cancelled accounts.
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$clf->getByUserName( $user_name );
			if ( $clf->getRecordCount() > 0 ) {
				$c_obj = $clf->getCurrent();
				if ( $c_obj->getStatus() == 20 ) {
					$error_message = TTi18n::gettext( 'Sorry, your company\'s account has been placed ON HOLD, please contact customer support immediately' );
				} else if ( $c_obj->getStatus() == 23 ) {
					$error_message = TTi18n::gettext( 'Sorry, your trial period has expired, please contact our sales department to reactivate your account' );
				} else if ( $c_obj->getStatus() == 28 ) {
					if ( $c_obj->getMigrateURL() != '' ) {
						$migrate_url = ( Misc::isSSL() == true ) ? 'https://' . $c_obj->getMigrateURL() : 'http://' . $c_obj->getMigrateURL();
						$error_message = TTi18n::gettext( 'To better serve our customers your account has been migrated, please update your bookmarks to use the following URL from now on' ) . ': ' . '<a href="' . $migrate_url . '">' . $migrate_url . '</a>';
					} else {
						$error_message = TTi18n::gettext( 'To better serve our customers your account has been migrated, please contact customer support immediately.' );
					}
				} else if ( $c_obj->getStatus() == 30 ) {
					$error_message = TTi18n::gettext( 'Sorry, your company\'s account has been CANCELLED, please contact customer support if you believe this is an error' );
				} else {
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getByUserName( $user_name );
					if ( $ulf->getRecordCount() == 1 ) {
						$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */

						if ( $u_obj->checkPassword( $password, false, false ) == true ) {
							if ( $u_obj->getEnableLogin() == false ) {
								$error_message = TTi18n::gettext( 'Sorry, your login is currently disabled, please contact your supervisor or manager to request access' );
							} else {
								if ( $u_obj->isFirstLogin() == true && $u_obj->isCompromisedPassword() == true ) {
									$error_message = TTi18n::gettext( 'Welcome to %1, since this is your first time logging in, we ask that you change your password to something more secure', [ APPLICATION_NAME ] );
									$error_column = 'password';
								} else if ( $u_obj->isCompromisedPassword() == true && $u_obj->isFirstLogin() == false ) {
									$error_message = TTi18n::gettext( 'Due to your company\'s password policy, your password must be changed immediately' );
									$error_column = 'password';
								} else if ( $u_obj->isPasswordPolicyEnabled() == true ) {
									if ( $u_obj->checkPasswordAge() == false ) {
										//Password policy is enabled, confirm users password has not exceeded maximum age.
										//Make sure we confirm that the password is in fact correct, but just expired.
										$error_message = TTi18n::gettext( 'Your password has exceeded its maximum age specified by your company\'s password policy and must be changed immediately' );
										$error_column = 'password';
									}
								} else {
									Debug::text( '  Password matches, but other criteria denied...', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
						} else {
							if ( $u_obj->checkLoginPermissions() == false ) {
								$error_message = TTi18n::gettext( 'Sorry, you don\'t have permission to login, please contact your supervisor or manager to request access' );
							}
						}
					} else {
						Debug::text( '  User Name: ' . $user_name .' record count: '. $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $ulf, $u_obj );
				}
			} else {
				Debug::text( '  User Name: ' . $user_name .' linked to company, record count: '. $clf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			}

			$validator_obj->isTrue( $error_column, false, $error_message );

			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
		}
	}

	/**
	 * Register permanent API key for the given username/password to be used for all subsequent API calls without needing a username/password.
	 * @param string $user_name
	 * @param string $password
	 * @return bool|string
	 */
	function registerAPIKey( $user_name, $password, $end_point = null ) {
		$session_type = $this->stripReturnHandler( $this->getSessionTypeForLogin( $user_name ) );

		if ( $session_type['mfa_type_id'] > 0 ) {
			//This function is not available for MFA enabled users, or when SAML is enabled.
			return false;
		}

		//Always require UserName/Password when registering an API key, as from a security stand-point this is similar to changing passwords.
		//  If its a master administrator, only need to register an API key for the master administrator employee, then they can switchUser() to any other user as needed with that same key.
		if ( $user_name != '' && $password != '' ) {
			$authentication = new Authentication();
			$api_key = $authentication->registerAPIKey( $user_name, $password, $end_point );
			Debug::text( 'User Name: ' . $user_name .' API Key: '. $api_key .' End Point: '. $end_point, __FILE__, __LINE__, __METHOD__, 10 );

			return $api_key; //Don't wrap in return handler.
		}

		return false;
	}


	/**
	 * Register permanent API key for the currently logged in user, requiring reauthentication checks.
	 * @param $end_point
	 * @return array|bool
	 */
	function registerAPIKeyForCurrentUser( $end_point = 'json/api' ) {
		global $authentication;

		if ( $this->isLoggedIn() == false ) {
			return $this->returnHandler( false );
		}

		if ( $authentication->isSessionReauthenticated() === false ) {
			return $this->getPermissionObject()->ReauthenticationRequired( $this->getCurrentUserObject() );
		}

		$api_key = $authentication->registerAPIKeyForCurrentUser( $end_point );

		//One time auth is used to verify single actions that require re-authentication and needs to be removed after used.
		$authentication->reauthenticationActionCompleted();

		return $this->returnHandler( $api_key );
	}

	/**
	 * @param string $user_id                           UUID
	 * @param string $invoice_invoice_invoice_client_id UUID
	 * @param string $ip_address
	 * @param string $user_agent
	 * @param string $client_id                         UUID
	 * @param string $end_point_id
	 * @param null $type_id
	 * @return array|bool
	 * @throws DBError
	 * @throws ReflectionException
	 */
	function newSession( $user_id, $invoice_invoice_invoice_client_id = null, $ip_address = null, $user_agent = null, $client_id = null, $end_point_id = null, $type_id = null ) {
		global $authentication;

		if ( is_object( $authentication ) && $authentication->getSessionID() != '' ) {
			Debug::text( 'Session ID: ' . $authentication->getSessionID() .' Encrypted: '. $authentication->encryptSessionID( $authentication->getSessionID() ) .' Type ID: '. $authentication->getType(), __FILE__, __LINE__, __METHOD__, 10 );

			if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=API Key
				return $this->getPermissionObject()->AuthenticationTypeDenied();
			}

			if ( $this->getPermissionObject()->Check( 'company', 'view' ) && $this->getPermissionObject()->Check( 'company', 'login_other_user' ) ) {
				if ( TTUUID::isUUID( $user_id ) == false ) { //If username is used, lookup user_id
					Debug::Text( 'Lookup User ID by UserName: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getByUserName( trim( $user_id ) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_id = $ulf->getCurrent()->getID();
					}
				}

				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getByIdAndStatus( TTUUID::castUUID( $user_id ), 10 );  //Can only switch to Active employees
				if ( $ulf->getRecordCount() == 1 ) {
					$new_session_user_obj = $ulf->getCurrent();

					if ( $client_id == '' ) {
						$client_id = 'browser-timetrex';
					}

					if ( $end_point_id == '' ) {
						$end_point_id = 'json/api';
					}

					if ( $user_agent == '' ) {
						$user_agent = isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null;
					}

					Debug::Text( 'Login as different user: ' . $user_id . ' IP Address: ' . $ip_address . ' Client ID: ' . $client_id . ' End Point ID: ' . $end_point_id . ' Type ID: '. $type_id .' User Agent: ' . $user_agent, __FILE__, __LINE__, __METHOD__, 10 );
					$new_session_id = $authentication->newSession( $user_id, $ip_address, $user_agent, $client_id, $end_point_id, $type_id );

					$retarr = [
							'session_id'      => $new_session_id,
							'url'             => Misc::getHostName( false ) . Environment::getBaseURL(), //Don't include the port in the hostname, otherwise it can cause problems when forcing port 443 but not using 'https'.
							'cookie_base_url' => Environment::getCookieBaseURL(),
					];

					//Add entry in source *AND* destination user log describing who logged in.
					//Source user log, showing that the source user logged in as someone else.
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 100, TTi18n::getText( 'Override Login' ) . ': ' . TTi18n::getText( 'SourceIP' ) . ': ' . $authentication->getIPAddress() . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $authentication->getSecureSessionID() . ' ' . TTi18n::getText( 'To Employee' ) . ': ' . $new_session_user_obj->getFullName() . ' (' . $user_id . ')', $this->getCurrentUserObject()->getId(), 'authentication' );

					//Destination user log, showing the destination user was logged in *by* someone else.
					TTLog::addEntry( $user_id, 100, TTi18n::getText( 'Override Login' ) . ': ' . TTi18n::getText( 'SourceIP' ) . ': ' . $authentication->getIPAddress() . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $authentication->getSecureSessionID() . ' ' . TTi18n::getText( 'by Employee' ) . ': ' . $this->getCurrentUserObject()->getFullName() . ' (' . $user_id . ')', $user_id, 'authentication' );

					return $this->returnHandler( $retarr );
				}
			} else {
				Debug::text( '  ERROR: Permission check failed for logging in as another user...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}

	/**
	 * Accepts user_id or user_name.
	 * @param string $user_id UUID
	 * @return bool
	 */
	function switchUser( $user_id ) {
		global $authentication;

		if ( is_object( $authentication ) && $authentication->getSessionID() != '' ) {
			Debug::text( 'Session ID: ' . $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10 );

			if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=API Key
				return $this->getPermissionObject()->AuthenticationTypeDenied();
			}

			if ( $this->getPermissionObject()->Check( 'company', 'view' ) && $this->getPermissionObject()->Check( 'company', 'login_other_user' ) ) {
				if ( TTUUID::isUUID( $user_id ) == false ) { //If username is used, lookup user_id
					Debug::Text( 'Lookup User ID by UserName: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getByUserName( trim( $user_id ) );
					if ( $ulf->getRecordCount() == 1 ) {
						$user_id = $ulf->getCurrent()->getID();
					}
				}

				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getByIdAndStatus( TTUUID::castUUID( $user_id ), 10 );  //Can only switch to Active employees
				if ( $ulf->getRecordCount() == 1 ) {
					Debug::Text( 'Login as different user: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
					$authentication->changeObject( $user_id );

					//Add entry in source *AND* destination user log describing who logged in.
					//Source user log, showing that the source user logged in as someone else.
					TTLog::addEntry( $this->getCurrentUserObject()->getId(), 100, TTi18n::getText( 'Override Login' ) . ': ' . TTi18n::getText( 'SourceIP' ) . ': ' . $authentication->getIPAddress() . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $authentication->getSecureSessionID() . ' ' . TTi18n::getText( 'To Employee' ) . ': ' . $authentication->getObject()->getFullName() . ' (' . $user_id . ')', $this->getCurrentUserObject()->getId(), 'authentication' );

					//Destination user log, showing the destination user was logged in *by* someone else.
					TTLog::addEntry( $user_id, 100, TTi18n::getText( 'Override Login' ) . ': ' . TTi18n::getText( 'SourceIP' ) . ': ' . $authentication->getIPAddress() . ' ' . TTi18n::getText( 'SessionID' ) . ': ' . $authentication->getSecureSessionID() . ' ' . TTi18n::getText( 'by Employee' ) . ': ' . $this->getCurrentUserObject()->getFullName() . ' (' . $user_id . ')', $user_id, 'authentication' );

					return true;
				} else {
					Debug::Text( 'User is likely not active: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::text( '  ERROR: Permission check failed for switching users...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function Logout() {
		global $authentication;

		if ( is_object( $authentication ) && $authentication->getSessionID() != '' ) {
			Debug::text( 'Logging out session ID: ' . $authentication->getSessionID(), __FILE__, __LINE__, __METHOD__, 10 );

			return $authentication->Logout();
		}

		return false;
	}

	/**
	 * @return int
	 */
	function getSessionIdle() {
		global $authentication;

		if ( !is_object( $authentication ) ) {
			$authentication = new Authentication();
		}

		return $authentication->getIdleTimeout();
	}

	/**
	 * @param bool $touch_updated_date
	 * @param string|array $type
	 * @return bool
	 */
	function isLoggedIn( $touch_updated_date = true, $type = [ 'USER_NAME', 'USER_NAME_MULTIFACTOR' ] ) {
		global $authentication;

		if ( is_array( $type ) == false ) {
			$type = [ $type ];
		}

		$session_id = getSessionID( $type[0] );

		if ( $session_id != '' ) {
			$authentication = new Authentication();
			Debug::text( 'Session ID: ' . $session_id . ' Source IP: ' . Misc::getRemoteIPAddress() . ' Touch Updated Date: ' . (int)$touch_updated_date, __FILE__, __LINE__, __METHOD__, 10 );
			if ( $authentication->Check( $session_id, $type, $touch_updated_date ) === true ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return array
	 */
	function getCurrentUserName() {
		if ( is_object( $this->getCurrentUserObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getUserName() );
		}

		return $this->returnHandler( false );
	}

	/**
	 * @return array
	 */
	function getCurrentUser() {
		if ( is_object( $this->getCurrentUserObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getObjectAsArray( [ 'id' => true, 'company_id' => true, 'currency_id' => true, 'permission_control_id' => true, 'pay_period_schedule_id' => true, 'policy_group_id' => true, 'default_branch_id' => true, 'default_department_id' => true, 'default_job_id' => true, 'default_job_item_id' => true, 'employee_number' => true, 'user_name' => true, 'phone_id' => true, 'first_name' => true, 'middle_name' => true, 'last_name' => true, 'full_name' => true, 'city' => true, 'province' => true, 'country' => true, 'longitude' => true, 'latitude' => true, 'work_phone' => true, 'home_phone' => true, 'work_email' => true, 'home_email' => true, 'feedback_rating' => true, 'prompt_for_feedback' => true, 'last_login_date' => true, 'created_date' => true, 'is_owner' => true, 'is_child' => true ] ) );
		}

		return $this->returnHandler( false );
	}

	/**
	 * @return array
	 */
	function getCurrentCompany() {
		if ( is_object( $this->getCurrentCompanyObject() ) ) {
			return $this->returnHandler( $this->getCurrentCompanyObject()->getObjectAsArray( [ 'id' => true, 'product_edition_id' => true, 'name' => true, 'short_name' => true, 'industry' => true, 'city' => true, 'province' => true, 'country' => true, 'work_phone' => true, 'application_build' => true, 'is_setup_complete' => true, 'total_active_days' => true, 'created_date' => true, 'latitude' => true, 'longitude' => true ] ) );
		}

		return $this->returnHandler( false );
	}

	/**
	 * @return array
	 */
	function getCustomFieldData() {
		$cflf = TTNew( 'CustomFieldListFactory' ); /** @var CustomFieldListFactory $cflf */

		$retarr = [
				'parent_tables'         => $cflf->getUniqueParentTableByCompanyId( $this->getCurrentCompanyObject()->getId() ),
				'conversion_field_types' => $cflf->getOptions( 'conversion_field_types' ),
		];

		return $this->returnHandler( $retarr );
	}

	/**
	 * @return array
	 */
	function getCurrentUserPreference() {
		if ( is_object( $this->getCurrentUserObject() ) && is_object( $this->getCurrentUserObject()->getUserPreferenceObject() ) ) {
			return $this->returnHandler( $this->getCurrentUserObject()->getUserPreferenceObject()->getObjectAsArray() );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Functions that can be called before the API client is logged in.
	 * Mainly so the proper loading/login page can be displayed.
	 * @return bool
	 */
	function getProduction() {
		return PRODUCTION;
	}

	/**
	 * @return string
	 */
	function getApplicationName() {
		return APPLICATION_NAME;
	}

	/**
	 * @return string
	 */
	function getApplicationVersion() {
		return APPLICATION_VERSION;
	}

	/**
	 * @return int
	 */
	function getApplicationVersionDate() {
		return APPLICATION_VERSION_DATE;
	}

	/**
	 * @return bool
	 */
	function getSystemVersionInstallDate() {
		return SystemSettingFactory::getSystemSettingValueByKey( 'system_version_install_date' );
	}

	/**
	 * @return string
	 */
	function getApplicationBuild() {
		return APPLICATION_BUILD;
	}

	/**
	 * @return string
	 */
	function getOrganizationName() {
		return ORGANIZATION_NAME;
	}

	/**
	 * @return string
	 */
	function getOrganizationURL() {
		return ORGANIZATION_URL;
	}

	/**
	 * @return bool
	 */
	function isApplicationBranded() {
		global $config_vars;

		if ( isset( $config_vars['branding']['application_name'] ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function isPoweredByLogoEnabled() {
		global $config_vars;

		if ( isset( $config_vars['branding']['disable_powered_by_logo'] ) && $config_vars['branding']['disable_powered_by_logo'] == true ) {
			return false;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function isAnalyticsEnabled() {
		global $config_vars;

		if ( isset( $config_vars['other']['disable_google_analytics'] ) && $config_vars['other']['disable_google_analytics'] == true ) {
			return false;
		}

		return true;
	}

	/**
	 * @return string
	 */
	function getAnalyticsTrackingCode() {
		global $config_vars;

		if ( isset( $config_vars['other']['analytics_tracking_code'] ) && $config_vars['other']['analytics_tracking_code'] != '' ) {
			return $config_vars['other']['analytics_tracking_code'];
		}

		return 'G-4MSFN7PM0H'; //GA4 - OnSite
	}

	function getUITrackingCode() {
		global $config_vars;

		if ( isset( $config_vars['other']['ui_tracking_code'] ) && $config_vars['other']['ui_tracking_code'] != '' ) {
			return $config_vars['other']['ui_tracking_code'];
		}

		return 'i1adc6wac8'; //Clarity - OnSite
	}

	/**
	 * @param bool $name
	 * @return int|string
	 */
	function getTTProductEdition( $name = false ) {
		if ( $name == true ) {
			$edition = getTTProductEditionName();
		} else {
			$edition = getTTProductEdition();
		}

		Debug::text( 'Edition: ' . $edition, __FILE__, __LINE__, __METHOD__, 10 );

		return $edition;
	}

	/**
	 * @return bool
	 */
	function getDeploymentOnDemand() {
		return DEPLOYMENT_ON_DEMAND;
	}

	/**
	 * @return bool
	 */
	function getRegistrationKey() {
		return SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' );
	}

	/**
	 * @param null $language
	 * @param null $country
	 * @return null
	 */
	function getLocale( $language = null, $country = null ) {
		$language = Misc::trimSortPrefix( $language );
		if ( $language == '' && is_object( $this->getCurrentUserObject() ) && is_object( $this->getCurrentUserObject()->getUserPreferenceObject() ) ) {
			$language = $this->getCurrentUserObject()->getUserPreferenceObject()->getLanguage();
		}
		if ( $country == '' && is_object( $this->getCurrentUserObject() ) ) {
			$country = $this->getCurrentUserObject()->getCountry();
		}

		if ( $language != '' ) {
			TTi18n::setLanguage( $language );
		}
		if ( $country != '' ) {
			TTi18n::setCountry( $country );
		}
		TTi18n::setLocale(); //Sets master locale

		//$retval = str_replace('.UTF-8', '', TTi18n::getLocale() );
		$retval = TTi18n::getNormalizedLocale();

		Debug::text( 'Locale: ' . $retval . ' Language: ' . $language, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @return int|mixed
	 */
	function getSystemLoad() {
		return Misc::getSystemLoad();
	}

	/**
	 * @return mixed
	 */
	function getHTTPHost() {
		return $_SERVER['HTTP_HOST'];
	}

	/**
	 * @return bool
	 */
	function getCompanyName() {
		//Get primary company data needs to be used when user isn't logged in as well.
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getByID( PRIMARY_COMPANY_ID );
		Debug::text( 'Primary Company ID: ' . PRIMARY_COMPANY_ID, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $clf->getRecordCount() == 1 ) {
			return $clf->getCurrent()->getName();
		}

		Debug::text( '  ERROR: Primary Company does not exist!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * Returns all login data required in a single call for optimization purposes.
	 * @param null $api
	 * @return array
	 */
	function getPreLoginData( $api = null, $authentication_type = [ 'USER_NAME', 'USER_NAME_MULTIFACTOR'] ) {
		global $config_vars;

		//Get browser information from user agent. Used below to get vendor and if browser is mobil or not.
		$browser = new Browser( ( ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) ? $_SERVER['HTTP_USER_AGENT'] : null ) );

		//Basic settings that *do not* require a DB connection.
		$retarr = [
				'primary_company_id'                  => PRIMARY_COMPANY_ID, //Needed for some branded checks.
				'primary_company_name'                => null, //Requires DB connection.
				'base_url'                            => Environment::getBaseURL(),
				'cookie_base_url'                     => Environment::getCookieBaseURL( 'json' ),
				'api_url'                             => Environment::getAPIURL( $api ),
				'api_base_url'                        => Environment::getAPIBaseURL( $api ),
				'api_json_url'                        => Environment::getAPIURL( 'json' ),
				'images_url'                          => Environment::getImagesURL(),
				'application_name'                    => $this->getApplicationName(),
				'organization_name'                   => $this->getOrganizationName(),
				'organization_url'                    => $this->getOrganizationURL(),
				'copyright_notice'                    => COPYRIGHT_NOTICE,
				'product_edition'                     => $this->getTTProductEdition( false ),
				'product_edition_name'                => $this->getTTProductEdition( true ),
				'deployment_on_demand'                => $this->getDeploymentOnDemand(),
				'web_session_expire'                  => ( isset( $config_vars['other']['web_session_expire'] ) && $config_vars['other']['web_session_expire'] != '' ) ? (bool)$config_vars['other']['web_session_expire'] : false, //If TRUE then session expires when browser closes.
				'analytics_enabled'                   => $this->isAnalyticsEnabled(),
				'analytics_tracking_code'             => $this->getAnalyticsTrackingCode(),
				'ui_tracking_code'             		  => $this->getUITrackingCode(),
				'registration_key'                    => null, //Requires DB connection.
				'http_host'                           => $this->getHTTPHost(),
				'is_ssl'                              => Misc::isSSL(),
				'production'                          => $this->getProduction(),
				'demo_mode'                           => DEMO_MODE,
				'application_version'                 => $this->getApplicationVersion(),
				'application_version_date'            => $this->getApplicationVersionDate(),
				'application_version_install_date'    => $this->getSystemVersionInstallDate(),
				'application_build'                   => $this->getApplicationBuild(),
				'installer_enabled'                   => ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] != '' ) ? (bool)$config_vars['other']['installer_enabled'] : false,
				'is_logged_in'                        => false, //Requires DB connection.
				'session_idle_timeout'                => $this->getSessionIdle(),
				'footer_left_html'                    => ( isset( $config_vars['other']['footer_left_html'] ) && $config_vars['other']['footer_left_html'] != '' ) ? $config_vars['other']['footer_left_html'] : false,
				'footer_right_html'                   => ( isset( $config_vars['other']['footer_right_html'] ) && $config_vars['other']['footer_right_html'] != '' ) ? $config_vars['other']['footer_right_html'] : false,
				'disable_feedback'                    => ( isset( $config_vars['other']['disable_feedback'] ) && $config_vars['other']['disable_feedback'] != '' ) ? (bool)$config_vars['other']['disable_feedback'] : false,
				'support_email'                       => ( isset( $config_vars['other']['support_email'] ) ) ? $config_vars['other']['support_email'] : 'support@timetrex.com', //Allow this to be defined as empty to disable the Email Support icon.
				'language_options'                    => Misc::addSortPrefix( TTi18n::getLanguageArray() ),
				//Make sure locale is set properly before this function is called, either in api.php or APIGlobal.js.php for example.
				'enable_default_language_translation' => ( isset( $config_vars['other']['enable_default_language_translation'] ) ) ? $config_vars['other']['enable_default_language_translation'] : false,
				'language'                            => TTi18n::getLanguage(),
				'locale'                              => TTi18n::getNormalizedLocale(), //Needed for HTML5 interface to load proper translation file.

				'map_provider'    => isset( $config_vars['map']['provider'] ) ? $config_vars['map']['provider'] : 'mapbox',
				'map_api_key'     => ( isset( $config_vars['map']['api_key'] ) && $config_vars['map']['api_key'] != '' ) ? $config_vars['map']['api_key'] : 'pk.eyJ1IjoidGltZXRyZXgiLCJhIjoiY2t1OHBxejEyNXI3ajJwcGk5d3cxNGJkeSJ9.4O1x-ULp4DuSRbeXJTIL3w', //On-Site.
																																																																																																																								/* @formatter:off */ 'product_edition_match' => (new Install())->checkProductEditionMatch(), /* @formatter:on */
				//Registration key for the map servers must be added in JS because of the url formats
				'map_tile_url'    => isset( $config_vars['map']['tile_url'] ) ? rtrim( $config_vars['map']['tile_url'], '/' ) : 'https://map-tiles.timetrex.com',
				'map_routing_url' => isset( $config_vars['map']['routing_url'] ) ? rtrim( $config_vars['map']['routing_url'], '/' ) : 'https://map-routing.timetrex.com',
				'map_geocode_url' => isset( $config_vars['map']['geocode_url'] ) ? rtrim( $config_vars['map']['geocode_url'], '/' ) : 'https://map-geocode.timetrex.com',

				'sandbox_url' => ( isset( $config_vars['other']['sandbox_url'] ) && $config_vars['other']['sandbox_url'] != '' ) ? $config_vars['other']['sandbox_url'] : false,
				'sandbox'     => ( isset( $config_vars['other']['sandbox'] ) && $config_vars['other']['sandbox'] != '' ) ? $config_vars['other']['sandbox'] : false,
				'uuid_seed'   => TTUUID::getSeed( true ),

				'user_agent_data' 	=> [ 'browser' => $browser->getBrowser(), 'is_mobile' => $browser->isMobile() ],
				'feature_flags' 	=> Misc::parseFeatureFlags(),
		];

		if ( ( isset( $_GET['disable_db'] ) && $_GET['disable_db'] == 1 )
				|| ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == 1 )
				|| ( isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] == true ) ) {
			Debug::text( 'WARNING: Installer/Down For Maintenance is enabled... Normal logins are disabled!', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			//Only data that requires a DB connection to obtain here.
			$retarr['company_name'] = $this->getCompanyName();
			if ( $retarr['company_name'] == '' ) {
				$retarr['company_name'] = 'N/A';
			}

			$retarr['registration_key'] = $this->getRegistrationKey();
			$retarr['is_logged_in'] = $this->isLoggedIn( true, $authentication_type );
		}

		return $retarr;
	}
	/**
	 * Returns all post login data required in a single call for optimization purposes.
	 * @param array $data
	 * @return array | bool
	 */
	function getPostLoginData( $data = [] ) {

		if ( !is_object( $this->getCurrentUserObject() ) ) {
			return false;
		}

		$retarr = [];

		$retarr['user_data'] = $this->stripReturnHandler( $this->getCurrentUser() );
		$retarr['user_preference'] = $this->stripReturnHandler( $this->getCurrentUserPreference() );
		$retarr['company_data'] = $this->stripReturnHandler( $this->getCurrentCompany() );
		$retarr['locale'] = $this->getLocale( $data['selected_language'] ?? null );

		$retarr['custom_field_data'] = $this->stripReturnHandler( $this->getCustomFieldData() );

		$permission = new Permission();
		$retarr['permissions'] = $permission->getPermissions( $this->getCurrentUserObject()->getId(), $this->getCurrentCompanyObject()->getId() );

		$clf = TTnew( 'CurrencyListFactory' );
		$clf->getByCompanyIdAndUserId( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
		if ( $clf->getRecordCount() > 0 ) {
			$c_obj = $clf->getCurrent();
			$retarr['currency_symbol'] = $c_obj->getSymbol();
		} else {
			$retarr['currency_symbol'] = '$';
		}

		$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$retarr['unique_country'] = $ulf->getUniqueCountryByCompanyId( $this->getCurrentCompanyObject()->getId() );

		$api_user_preference = new APIUserPreference();
		$retarr['moment_date_format'] = $api_user_preference->getOptions( 'moment_date_format' );
		$retarr['moment_time_format'] = $api_user_preference->getOptions( 'moment_time_format' );

		if ( !$retarr['user_preference'] || !isset( $retarr['user_preference']['date_format'] ) ) {
			//Get default user preferences if user does not have any set.
			$retarr['user_preference'] = $this->stripReturnHandler( $api_user_preference->getUserPreferenceDefaultData() );
		}

		$retarr['feature_flags'] = Misc::parseFeatureFlags();

		$retarr['logout_settings'] = ['slo_url' => null];

		if ( isset($this->getCurrentCompanyObject()->getSAMLSpJSON()['saml_authentication_type_id']) && $this->getCurrentCompanyObject()->getSAMLSpJSON()['saml_authentication_type_id'] > 10 ) {
			$retarr['logout_settings']['slo_url'] = $this->getCurrentCompanyObject()->getSAMLSpJSON()['saml_idp_slo_url'] ?? null;
		}

		return $retarr;
	}

	/**
	 * Function that HTML5 interface can call when an irrecoverable error or uncaught exception is triggered.
	 * @param null $data
	 * @param null $screenshot
	 * @return string
	 */
	function sendErrorReport( $data = null, $screenshot = null ) {
		$rl = TTNew( 'RateLimit' ); /** @var RateLimit $rl */
		$rl->setID( 'error_report_' . Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( 20 );
		$rl->setTimeFrame( 900 ); //15 minutes
		if ( $rl->check() == false ) {
			Debug::Text( 'Excessive error reports... Preventing error reports from: ' . Misc::getRemoteIPAddress() . ' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10 );

			return APPLICATION_BUILD;
		}

		$attachments = null;
		if ( $screenshot != '' ) {
			$attachments[] = [ 'file_name' => 'screenshot.png', 'mime_type' => 'image/png', 'data' => base64_decode( $screenshot ) ];
		}

		$subject = 'HTML5 Error Report'; //Don't translate this, as it breaks filters.

		$data = 'IP Address: ' . Misc::getRemoteIPAddress() . "\nServer Version: " . APPLICATION_BUILD . "\nUser Agent: " . ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A' ) . "\n\n" . $data;

		Misc::sendSystemMail( $subject, $data, $attachments ); //Do not send if PRODUCTION=FALSE.

		//return APPLICATION_BUILD so JS can check if its correct and notify the user to refresh/clear cache.
		return APPLICATION_BUILD;
	}

	/**
	 * Allows user who isn't logged in to change their password.
	 * @param string $user_name
	 * @param string $current_password
	 * @param string $new_password
	 * @param string $new_password2
	 * @return array|bool
	 * @internal param string $type
	 */
	function changePassword( $user_name, $current_password = null, $new_password = null, $new_password2 = null ) {
		$rl = TTNew( 'RateLimit' ); /** @var RateLimit $rl */
		$rl->setID( 'authentication_' . Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( 20 );
		$rl->setTimeFrame( 900 ); //15 minutes

		if ( $rl->check() == false ) {
			Debug::Text( 'Excessive failed password attempts... Preventing password change from: ' . Misc::getRemoteIPAddress() . ' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10 );
			sleep( 5 ); //Excessive password attempts, sleep longer.
			$u_obj = TTnew( 'UserListFactory' ); /** @var UserListFactory $u_obj */
			$u_obj->Validator->isTrue( 'current_password', false, TTi18n::gettext( 'Current User Name or Password is incorrect' ) );

			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $u_obj->Validator->getErrorsArray(), [ 'total_records' => 1, 'valid_records' => 0 ] );
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByUserName( $user_name );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();
			if ( is_object( $u_obj->getCompanyObject() ) && $u_obj->getCompanyObject()->getStatus() == 10 ) {
				Debug::text( 'Attempting to change password for: ' . $user_name, __FILE__, __LINE__, __METHOD__, 10 );

				$u_obj->setIsRequiredCurrentPassword( true );
				$u_obj->setCurrentPassword( $current_password );

				if ( $current_password != '' ) {
					if ( $u_obj->checkPassword( $current_password, false ) !== true ) { //Disable password policy checking on current password.
						Debug::text( 'Password check failed! Attempt: ' . $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10 );
						sleep( (int)( $rl->getAttempts() * 0.5 ) ); //If password is incorrect, sleep for some time to slow down brute force attacks.
						//setCurrentPassword() above handles this validation error message now.
//						$u_obj->Validator->isTrue( 'current_password',
//												   FALSE,
//												   TTi18n::gettext( 'Current User Name or Password is incorrect' ) );
					}
				} else {
					Debug::Text( 'Current password not specified', __FILE__, __LINE__, __METHOD__, 10 );
					$u_obj->Validator->isTrue( 'current_password',
											   false,
											   TTi18n::gettext( 'Current User Name or Password is incorrect' ) );
				}

				if ( $current_password == $new_password ) {
					$u_obj->Validator->isTrue( 'password',
											   false,
											   TTi18n::gettext( 'New password must be different than current password' ) );
				} else {
					if ( $new_password != '' || $new_password2 != '' ) {
						if ( $new_password == $new_password2 ) {
							$u_obj->setPassword( $new_password );
						} else {
							$u_obj->Validator->isTrue( 'password',
													   false,
													   TTi18n::gettext( 'Passwords don\'t match' ) );
						}
					} else {
						$u_obj->Validator->isTrue( 'password',
												   false,
												   TTi18n::gettext( 'Passwords don\'t match' ) );
					}
				}

				//This should force the updated_by field to match the user changing their password,
				//  so we know not to ask the user to change their password again, since they were the last ones to do so.
				//$current_user must be set above $u_obj->isValid() so it can properly validate things like hierarchy and such in UserFactory.
				global $current_user;
				$current_user = $u_obj;

				if ( $u_obj->isValid() ) {
					if ( DEMO_MODE == true ) {
						//Return TRUE even in demo mode, but nothing happens.
						return $this->returnHandler( true );
					} else {
						TTLog::addEntry( $u_obj->getID(), 20, TTi18n::getText( 'Password - Web (Password Policy)' ), null, $u_obj->getTable() );
						$rl->delete(); //Clear failed password rate limit upon successful login.

						$u_obj->setPasswordUpdatedDate( time() ); //Since the user isn't logged in, we have to manually force the PasswordUpdatedDate here to ensure its no longer -1 (compromised)
						$retval = $u_obj->Save( false ); //UserID is needed below.

						//Logout all other sessions for this user.
						$authentication = TTNew( 'Authentication' ); /** @var Authentication $authentication */
						$authentication->logoutUser( $u_obj->getID() );

						$current_user = null; //unset( $current_user ); -- unset() doesn't work on global variables.

						return $this->returnHandler( $retval ); //Single valid record
					}
				}
			} else {
				$u_obj = TTnew( 'UserListFactory' ); /** @var UserListFactory $u_obj */
				$u_obj->Validator->isTrue( 'current_password', false, TTi18n::gettext( 'Sorry, your company\'s account is not currently ACTIVE, please contact customer support' ) );
			}
		} else {
			//Issue #2225 - Be sure to return the same error message even if username is not valid to avoid user enumeration attacks.
			$u_obj = TTnew( 'UserListFactory' ); /** @var UserListFactory $u_obj */
			$u_obj->Validator->isTrue( 'current_password', false, TTi18n::gettext( 'Current User Name or Password is incorrect' ) );
		}

		sleep( (int)( $rl->getAttempts() * 0.5 ) ); //If password is incorrect, sleep for some time to slow down brute force attacks.
		Debug::Text( 'Failed username/password... Attempt: ' . $rl->getAttempts() . ' Sleeping...', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $u_obj->Validator->getErrorsArray(), [ 'total_records' => 1, 'valid_records' => 0 ] );
	}

	/**
	 * @param $email
	 * @return array
	 */
	function resetPassword( $email ) {
		//Debug::setVerbosity( 11 );
		$rl = TTNew( 'RateLimit' ); /** @var RateLimit $rl */
		$rl->setID( 'password_reset_' . Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( 10 );
		$rl->setTimeFrame( 900 ); //15 minutes

		$validator = new Validator();

		Debug::Text( 'Email: ' . $email, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $validator->isEmail( 'email', $email, TTi18n::getText( 'Please enter a valid email address' ) ) == true ) { //Make sure they at least enter a valid formatted email address before bothering to check the database.
			if ( $rl->check() == false ) {
				Debug::Text( 'Excessive reset password attempts... Preventing resets from: ' . Misc::getRemoteIPAddress() . ' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10 );
				sleep( 5 ); //Excessive password attempts, sleep longer.
				$validator->isTrue( 'email', false, TTi18n::getText( 'Email address was not found in our database (z)' ) );
			} else {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getByHomeEmailOrWorkEmail( $email );
				if ( $ulf->getRecordCount() == 1 ) {
					$user_obj = $ulf->getCurrent(); /** @var UserFactory $user_obj */
					if ( $user_obj->getEnableLogin() == true ) { //Only allow password resets when logins are enabled.
						//Check if company is using LDAP authentication or SAML, if so deny password reset.
						if ( $user_obj->getCompanyObject()->getLDAPAuthenticationType() == 0 && $user_obj->getMultiFactorType() < 1000 ) { //1000 = SAML
							if ( $user_obj->sendPasswordResetEmail() == true ) {
								Debug::Text( 'Found USER! ', __FILE__, __LINE__, __METHOD__, 10 );

								//Logout *current* session is they are currently logged in. Its a rare case of a user trying to reset their password while already logged in,
								// but it has happened because they don't remember their password stored in a password manager.
								$authentication = TTNew( 'Authentication' ); /** @var Authentication $authentication */
								$authentication->setSessionID( getSessionID() );
								$authentication->logout();

								$rl->delete(); //Clear password reset rate limit upon successful login.

								return $this->returnHandler( [ 'email_sent' => 1, 'email' => $email ] );
							} else {
								Debug::Text( 'ERROR: Unable to send password reset email, perhaps user record is invalid, or production mode is disabled?', __FILE__, __LINE__, __METHOD__, 10 );
								$validator->isTrue( 'email', false, TTi18n::getText( 'Unable to reset password, please contact your administrator for more information' ) );
							}
						} else {
							Debug::Text( 'LDAP or SAML Authentication is enabled, password reset is disabled! ', __FILE__, __LINE__, __METHOD__, 10 );
							$validator->isTrue( 'email', false, TTi18n::getText( 'Please contact your administrator for instructions on changing your password' ) . ' (LDAP / SAML)' );
						}
					} else {
						$validator->isTrue( 'email', false, TTi18n::getText( 'Email address was not found in our database (b)' ) );
					}
				} else {
					//Error
					Debug::Text( 'DID NOT FIND USER! Returned: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					$validator->isTrue( 'email', false, TTi18n::getText( 'Email address was not found in our database (a)' ) );

					//If password was incorrect, sleep for some specified period of time to help delay brute force attacks.
					if ( PRODUCTION == true ) {
						Debug::Text( 'Email address for password reset was incorrect, sleeping for random amount of time...', __FILE__, __LINE__, __METHOD__, 10 );
						usleep( rand( 750000, 1500000 ) );
					}
				}

				Debug::text( 'Reset Password Failed! Attempt: ' . $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10 );
				sleep( (int)( $rl->getAttempts() * 0.5 ) ); //If email is incorrect, sleep for some time to slow down brute force attacks.
			}
		}

		return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), [ 'error' => $validator->getErrorsArray() ], [ 'total_records' => 1, 'valid_records' => 0 ] );
	}

	/**
	 * Reset the password if users forgotten their password
	 * @param $key
	 * @param $password
	 * @param $password2
	 * @return array
	 */
	function passwordReset( $key, $password, $password2 ) {
		$rl = TTNew( 'RateLimit' ); /** @var RateLimit $rl */
		$rl->setID( 'password_reset_' . Misc::getRemoteIPAddress() );
		$rl->setAllowedCalls( 10 );
		$rl->setTimeFrame( 900 ); //15 minutes

		$validator = new Validator();
		if ( $rl->check() == false ) {
			Debug::Text( 'Excessive password reset attempts... Preventing resets from: ' . Misc::getRemoteIPAddress() . ' for up to 15 minutes...', __FILE__, __LINE__, __METHOD__, 10 );
			sleep( 5 ); //Excessive password attempts, sleep longer.
		} else {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			Debug::Text( 'Key: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
			$ulf->getByPasswordResetKey( $key );
			if ( $ulf->getRecordCount() == 1 ) {
				Debug::Text( 'FOUND Password reset key! ', __FILE__, __LINE__, __METHOD__, 10 );
				$user_obj = $ulf->getCurrent(); /** @var UserFactory $user_obj */
				if ( $user_obj->checkPasswordResetKey( $key ) == true ) {
					//Logout *current* session is they are currently logged in. Its a rare case of a user trying to reset their password while already logged in,
					// but it has happened because they don't remember their password stored in a password manager.
					$authentication = TTNew( 'Authentication' ); /** @var Authentication $authentication */
					$authentication->setSessionID( getSessionID() );
					$authentication->logout();

					//Make sure passwords match
					Debug::Text( 'Change Password Key: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );
					if ( $password != '' && trim( $password ) === trim( $password2 ) ) {
						//Change password
						$user_obj->setPassword( $password ); //Password reset key is cleared when password is changed.
						$user_obj->setPasswordUpdatedDate( time() ); //Since the user isn't logged in, we have to manually force the PasswordUpdatedDate here to ensure its no longer -1 (compromised)
						$user_obj->setEnableClearPasswordResetData( true ); //Clear any outstanding password reset key now that it has been used successfully.
						$user_obj->setMultiFactorType( 0 ); //Disable MFA for this user when resetting password.
						if ( $user_obj->isValid() ) {
							$user_obj->Save( false );
							Debug::Text( 'Password Change succesful!', __FILE__, __LINE__, __METHOD__, 10 );
							TTLog::addEntry( $user_obj->getID(), 20, TTi18n::getText( 'Password Reset - Web (Completed)' ), $user_obj->getID(), $user_obj->getTable() );

							//Logout all sessions for this user when password is successfully reset.
							$authentication = TTNew( 'Authentication' ); /** @var Authentication $authentication */
							$authentication->logoutUser( $user_obj->getId() );
							unset( $user_obj );

							return $this->returnHandler( true );
						} else {
							$validator->merge( $user_obj->Validator ); //Make sure we display any validation errors like password too weak.
						}
					} else {
						$validator->isTrue( 'password', false, TTi18n::getText( 'Passwords do not match' ) );
					}
					//Do this once a successful key is found, so the user can get as many password change attempts as needed.
					$rl->delete(); //Clear password reset rate limit upon successful reset.
				} else {
					Debug::Text( 'DID NOT FIND Valid Password reset key!', __FILE__, __LINE__, __METHOD__, 10 );
					$validator->isTrue( 'password', false, TTi18n::getText( 'Password reset key is invalid, please try resetting your password again.' ) );
				}
			} else {
				Debug::Text( 'DID NOT FIND Valid Password reset key! (b)', __FILE__, __LINE__, __METHOD__, 10 );
				$validator->isTrue( 'password', false, TTi18n::getText( 'Password reset key is invalid, please try resetting your password again.' ) . ' (b)' );
			}

			Debug::text( 'Password Reset Failed! Attempt: ' . $rl->getAttempts(), __FILE__, __LINE__, __METHOD__, 10 );
			sleep( (int)( $rl->getAttempts() * 0.5 ) ); //If email is incorrect, sleep for some time to slow down brute force attacks.
		}

		return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), [ 'error' => $validator->getErrorsArray() ], [ 'total_records' => 1, 'valid_records' => 0 ] );
	}


	/**
	 * Sends a refreshed CSRF token cookie in case it expires prior to the user clicking the login button. This helps avoid showing an error message and triggering a full browser refresh.
	 */
	function sendCSRFTokenCookie() {
		return $this->returnHandler( sendCSRFTokenCookie() );
	}

	/**
	 * Ping function is also in APIMisc for when the session timesout is valid.
	 * Ping no longer can tell if the session is timed-out, must use "isLoggedIn(FALSE)" instead.
	 * @return bool
	 */
	function Ping() {
		return true;
	}

	/**
	 * Used to mark notifications as read from things like tracking pixels in notification emails.
	 *    Authentication is not required for this, but the user_id, type_id and object_id must all match and would be very unlikely to guess. Plus its pretty benign even if they do.
	 * @param string $user_id UUID
	 * @param int $type_id INT
	 * @param string $object_id UUID
	 * @return bool
	 * @throws DBError
	 */
	function markNotificationAsRead( $user_id, $type_id, $object_id ) {
		if ( TTUUID::isUUID( $user_id ) == false || TTUUID::isUUID( $object_id ) == false || empty( $type_id ) ) {
			return false;
		}

		return NotificationFactory::updateStatusByObjectIdAndObjectTypeId( (int)$type_id, TTUUID::castUUID( $object_id ), TTUUID::castUUID( $user_id ) );
	}

	/**
	 * @return array|bool
	 */
	function removeAllTrustedDevices() {
		if ( $this->isLoggedIn() == false ) {
			return $this->returnHandler( false );
		}

		$atdlf = TTnew( 'AuthenticationTrustedDeviceListFactory' ); /** @var AuthenticationTrustedDeviceListFactory $atdlf */
		$atdlf->getByUserId( $this->getCurrentUserObject()->getId() );
		if ( $atdlf->getRecordCount() > 0 ) {
			foreach ( $atdlf as $atd_obj ) { /** @var AuthenticationTrustedDeviceFactory $atd_obj */
				$atd_obj->setDeleted( true );
				if ( $atd_obj->isValid() ) {
					$atd_obj->Save();
				}
			}
		}

		if ( isset( $_COOKIE['TrustedDevice'] ) ) {
			//Destroy the current TrustedDevice cookie.
			setcookie( 'TrustedDevice', '', ( time() - ( 3600 * 34 ) ), Environment::getCookieBaseURL() );
		}

		return $this->returnHandler( true );
	}


	/**
	 * @param $delete_all_sessions
	 * @return array|bool
	 */
	function logoutAllSessions( $delete_all_sessions = false ) {
		if ( $this->isLoggedIn() == false ) {
			return $this->returnHandler( false );
		}

		global $authentication;

		if ( $delete_all_sessions === true ) {
			$authentication->logoutUser( $this->getCurrentUserObject()->getId(), null, null );
		} else {
			$authentication->logoutUser( $this->getCurrentUserObject()->getId() );
		}


		return $this->returnHandler( true );
	}
}

?>
