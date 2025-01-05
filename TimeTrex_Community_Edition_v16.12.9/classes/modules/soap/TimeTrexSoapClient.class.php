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
 * Add custom soap client that automatically retries calls on network errors like "Could not connect to host"
 * Class TTSoapClient
 */
class TTSoapClient extends SoapClient {
	public $_initial_location = null;
	private $_soap_options = null;

	public function __construct( $wsdl, $options = [] ) {
		//Pull out the location and save it so it can be used later to fall back to non-SSL.
		if ( isset( $options['location'] ) ) {
			$this->_initial_location = $options['location'];
		}

		$this->_soap_options = $options; //Save original SOAP options so we can modify them and re-use them later.

		return parent::__construct( $wsdl, $options );
	}

	/**
	 * @param string $function_name
	 * @param array $arguments
	 * @return mixed|SoapFault
	 */
	#[\ReturnTypeWillChange]
	public function __call( $function_name, $arguments ) {
		$max_retries = 5;
		$retry_count = 0;

		while ( $retry_count <= $max_retries ) {
			if ( $retry_count == ( $max_retries - 1 ) && isset( $this->_initial_location ) ) {
				//On 2nd last retry, relax SSL checking as a root certificate could be expired and the operating system needs to be updated.
				$this->_soap_options['stream_context'] = stream_context_create( [
														  'ssl' => [
																  'verify_peer'       => false,
																  'verify_peer_name'  => false,
																  'allow_self_signed' => true,
														  ],
												  ] );
				Debug::Text( '  WARNING: Due to failed connection attempts ('. $retry_count .'), relaxing SSL SOAP communication: ' . $this->_initial_location, __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( $retry_count == $max_retries && isset( $this->_initial_location ) ) {
				//On last retry, attempt to fall back to HTTP rather than HTTPS
				$this->__setLocation( str_replace( 'https://', 'http://', $this->_initial_location ) );
				Debug::Text( '  WARNING: Due to failed connection attempts ('. $retry_count .'), falling back to non-SSL SOAP communication: ' . $this->_initial_location, __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( Debug::getEnable() == true && Debug::getVerbosity() >= 11 ) {
				$result = parent::__call( $function_name, $arguments );
			} else {
				$result = @parent::__call( $function_name, $arguments );
			}

			if ( is_soap_fault( $result ) && ( $result->faultstring == 'Could not connect to host' || $result->faultstring == 'Error Fetching http headers' || $result->faultstring == 'Failed Sending HTTP SOAP request' ) ) {
				Debug::Text( 'SOAP connection failed: \''. $result->faultstring .'\' retrying ('. $retry_count .')... Timeouts: Socket: '. ini_get( 'default_socket_timeout' ) .' Connection: '. $this->_soap_options['connection_timeout'], __FILE__, __LINE__, __METHOD__, 10 );

				if ( $retry_count <= $max_retries ) {
					$this->_soap_options['connection_timeout'] = max( 2, pow( ( $retry_count + 1 ), 2 ) ); //Expontial backoff on connection timeout.
					sleep( $this->_soap_options['connection_timeout'] ); //Expontial backoff on the retry timeout.
					parent::__construct( null, $this->_soap_options );
				}

				if ( $retry_count >= $max_retries ) { //Only check this on failure, never on success.
					return new SoapFault( 'HTTP', 'Could not connect to host after maximum retry attempts.' );
				}

				$retry_count++;
			} else {
				if ( $retry_count > 0 ) {
					Debug::Text( 'SOAP connection succeed upon retry ('. $retry_count .')... Timeouts: Socket: '. ini_get( 'default_socket_timeout' ) .' Connection: '. $this->_soap_options['connection_timeout'], __FILE__, __LINE__, __METHOD__, 10 );
				}
				break;
			}
		}

		return $result;
	}
}

/**
 * @package Modules\SOAP
 */
class TimeTrexSoapClient {
	var $soap_client_obj = null;

	/**
	 * TimeTrexSoapClient constructor.
	 */
	function __construct( $validate_ssl = true ) {
		$this->getSoapObject( $validate_ssl );

		return true;
	}

	/**
	 * @return null|TTSoapClient
	 */
	function getSoapObject( $validate_ssl = true, $force = false ) {
		if ( $this->soap_client_obj == null || $force == true ) {
			if ( function_exists( 'openssl_encrypt' ) ) {
				$location = 'https://';
			} else {
				$location = 'http://';
			}

			$location .= 'coreapi.timetrex.com/ext_soap/server.php?MessageID='. TTUUID::generateUUID(); //Unique message ID for each SoapClientObject session (not every call), so specific requests can be easily differentiated.

			//Testing against internal API.
			//$validate_ssl = false;
			//$location .= 'coreapi.trunk.timetrex.com/ext_soap/server.php?MessageID='. TTUUID::generateUUID(); //ZZZ REMOVE ME

			//  Use this to allow self-signed certificates.
			if ( $validate_ssl === false ) {
				$context = stream_context_create( [
														  'ssl' => [
																  'verify_peer'       => false,
																  'verify_peer_name'  => false,
																  'allow_self_signed' => true,
														  ],
														  'http' => [
																  'follow_location' => 1,
														  ],
												  ] );
			} else {
				$context = stream_context_create( [
						'http' => [
							'follow_location' => 1,
						]
				] );
			}

			$this->soap_client_obj = new TTSoapClient( null, [
																   'location'           => $location,
																   'uri'                => 'urn:test',
																   'style'              => SOAP_RPC,
																   'use'                => SOAP_ENCODED,
																   'encoding'           => 'UTF-8',
																   'connection_timeout' => 2, //This gets automatically increased on each retry in __call() above.
																   'keep_alive'         => false, //This should help prevent "Error fetching HTTP headers" or "errno=10054 An existing connection was forcibly closed by the remote host." SOAP errors.
																   'trace'              => 1,
																   'exceptions'         => 0,
																   'stream_context' 	=> $context,
														   ]
			);

			Debug::Text( 'SOAP Client initialized with URL: '. $location, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $this->soap_client_obj;
	}

	/**
	 * @param $data
	 * @param string $format
	 * @return null
	 */
	function convertDocument( $data, $format = 'pdf' ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			return $this->getSoapObject()->convertDocument( $data, $format, $company_data );
		}

		return null; //Return NULL when no data available, and FALSE to try again later.
	}

	/**
	 * @param $receipt_data
	 * @param $file_name
	 * @return null
	 */
	function parseReceipt( $receipt_data, $file_name, $mime_type, $type_id, $extra_data ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			return $this->getSoapObject()->parseReceipt( $receipt_data, $file_name, $mime_type, $type_id, $company_data, $extra_data );
		}

		return null; //Return NULL when no data available, and FALSE to try again later.
	}
	/**
	 * @param $resume_data
	 * @param $file_name
	 * @return null
	 */
	function parseResume( $resume_data, $file_name ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			return $this->getSoapObject()->parseResume( $resume_data, $file_name, $company_data );
		}

		return null; //Return NULL when no data available, and FALSE to try again later.
	}


	function printSoapDebug() {
		echo "<pre>\n";
		echo "Request :\n" . htmlspecialchars( $this->getSoapObject()->__getLastRequest() ) . "\n";
		echo "Response :\n" . htmlspecialchars( $this->getSoapObject()->__getLastResponse() ) . "\n";
		echo "</pre>\n";
	}

	/**
	 * @return mixed
	 */
	function ping( $ping_key = null ) {
		$retval = $this->getSoapObject()->ping( $ping_key );
		if ( is_object( $retval ) && get_class( $retval ) == 'SoapFault' ) {
			Debug::Arr( $this->getSoapObject()->__getLastResponse(), 'ERROR: Last Response: ', __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Arr( $retval, 'ERROR: ping() failed!', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		} else {
			return $retval;
		}
	}

	/**
	 * @return bool
	 */
	function isUpdateNotifyEnabled() {
		global $config_vars;
		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL && DEPLOYMENT_ON_DEMAND == true && isset( $config_vars['other']['enable_update_notify'] ) && $config_vars['other']['enable_update_notify'] == false ) {
			return false; //Disabled with On-Demand service.
		}

		if ( getTTProductEdition() == 10 ) {
			$value = SystemSettingFactory::getSystemSettingValueByKey( 'update_notify' );
			if ( $value == 0 ) {
				return false;
			}
		}

		return true;
	}

	/**
	 * @return array|bool
	 */
	function getPrimaryCompanyData() {
		global $config_vars, $db;
																																																																											/* @formatter:off */ $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; @$obj = new $obj_class; $hardware_id = $obj->getHardwareID(); unset( $obj, $obj_class ); /* @formatter:on*/
		//Make sure a database connection has been established at least, otherwise this can cause FATAL error
		//which during installation (before any database exists) is bad.
		if ( !isset( $db ) || !is_object( $db ) || ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == true ) ) {
			//If installer is enabled, check to make sure "company" table exists, as a PHP error being triggered and sending an email before the database schema is setup could prevent error reports from being sent.
			$install_obj = new Install();
			$install_obj->setDatabaseConnection( $db ); //Default Connection
			if ( $install_obj->checkTableExists('company') == false ) {
				Debug::Text( 'WARNING: No database connection or company table not created yet...', __FILE__, __LINE__, __METHOD__, 10 );
				$company_data = [
						'system_version'            => APPLICATION_VERSION,
						'application_version_date'  => APPLICATION_VERSION_DATE,
						'registration_key'          => 'N/A',
						'hardware_id'               => ( ( isset( $hardware_id ) ) ? $hardware_id : 'N/A' ),
						'product_edition_id'        => getTTProductEdition(),
						'product_edition_available' => getTTProductEdition(),
						'name'                      => '<Company Table Not Exists>',
						'short_name'                => 'N/A',
				];

				return $company_data;
			}
		}

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		if ( !isset( $config_vars['other']['primary_company_id'] ) || ( isset( $config_vars['other']['primary_company_id'] ) && TTUUID::isUUID( $config_vars['other']['primary_company_id'] ) == false ) ) {
			//Find the first created company that is still active.
			Debug::Text( 'WARNING: Primary company is not defined in .ini file, attempting to guess...', __FILE__, __LINE__, __METHOD__, 10 );
			$clf->getAll( 1, null, [ 'status_id' => '= 10' ], [ 'created_date' => 'asc' ] ); //Limit 1
			if ( $clf->getRecordCount() == 1 ) {
				$config_vars['other']['primary_company_id'] = $clf->getCurrent()->getId();
			}
		}

		try {
			$clf->getById( $config_vars['other']['primary_company_id'] );
			if ( $clf->getRecordCount() > 0 ) {
				foreach ( $clf as $c_obj ) {
					$company_data = [
							'system_version'            => APPLICATION_VERSION,
							'application_version_date'  => APPLICATION_VERSION_DATE,
							'registration_key'          => $this->getLocalRegistrationKey(),
							'hardware_id'               => ( ( isset( $hardware_id ) ) ? $hardware_id : 'N/A' ),
							'product_edition_id'        => $c_obj->getProductEdition(),
							'product_edition_available' => getTTProductEdition(),
							'name'                      => $c_obj->getName(),
							'short_name'                => $c_obj->getShortName(),
							'work_phone'                => $c_obj->getWorkPhone(),
							'city'                      => $c_obj->getCity(),
							'country'                   => $c_obj->getCountry(),
							'province'                  => $c_obj->getProvince(),
							'postal_code'               => $c_obj->getPostalCode(),
					];
				}

				return $company_data;
			} else {
				Debug::Text( 'ERROR: Primary company does not exist: ' . $config_vars['other']['primary_company_id'], __FILE__, __LINE__, __METHOD__, 10 );
			}
		} catch ( Exception $e ) {
			unset( $e ); //code standards
			Debug::Text( 'ERROR: Cant get company data for downloading upgrade file, database is likely down...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function isLatestVersion( $company_id ) {
		$version = SystemSettingFactory::getSystemSettingValueByKey( 'system_version' );
		if ( $version !== false ) {
			$retval = $this->getSoapObject()->isLatestVersion( $this->getLocalRegistrationKey(), $company_id, $version, getTTProductEdition() );
			if ( is_object( $retval ) && get_class( $retval ) == 'SoapFault' ) {
				Debug::Arr( $retval, 'ERROR: Cant check for latest version, SOAP connection failed!', __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				Debug::Text( ' Current Version: ' . $version . ' Retval: ' . (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );

				return $retval;
			}
		}

		return true; //Default to TRUE (already running latest version) in the event that something goes wrong.
	}

	/**
	 * @param $key
	 * @return bool
	 */
	function isValidRegistrationKey( $key ) {
		$key = trim( $key );
		if ( strlen( $key ) == 32 || strlen( $key ) == 40 ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getLocalRegistrationKey() {
		$key = SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' );

		//If key is invalid, attempt to obtain a new one.
		if ( $this->isValidRegistrationKey( $key ) == false ) {
			$this->saveRegistrationKey();

			return false;
		}

		return $key;
	}

	/**
	 * @return mixed
	 */
	function getRegistrationKey() {
		return $this->getSoapObject()->generateRegistrationKey();
	}

	/**
	 * @return bool
	 */
	function saveRegistrationKey() {
		$sslf = TTnew( 'SystemSettingListFactory' ); /** @var SystemSettingListFactory $sslf */
		$sslf->getByName( 'registration_key' );

		$get_new_key = false;
		if ( $sslf->getRecordCount() > 1 ) {
			Debug::Text( 'Too many registration keys, removing them...', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $sslf as $ss_obj ) {
				$ss_obj->Delete();
			}
			$get_new_key = true;
		} else if ( $sslf->getRecordCount() == 1 ) {
			$key = $sslf->getCurrent()->getValue();
			if ( $this->isValidRegistrationKey( $key ) == false ) {
				foreach ( $sslf as $ss_obj ) {
					$ss_obj->Delete();
				}
				$get_new_key = true;
			}
		}

		if ( $get_new_key == true || $sslf->getRecordCount() == 0 ) {
			//Get registration key from TimeTrex server.
			$key = trim( $this->getRegistrationKey() );
			Debug::Text( 'Registration Key from server: ' . $key, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $this->isValidRegistrationKey( $key ) == false ) {
				$key = md5( uniqid( '', true ) );
				Debug::Text( 'Failed getting registration key from server...', __FILE__, __LINE__, __METHOD__, 10 );
			}

			$sslf->setName( 'registration_key' );
			$sslf->setValue( $key );
			if ( $sslf->isValid() == true ) {
				$sslf->Save();
			}

			return true;
		} else {
			Debug::Text( 'Registration key is valid, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function sendCompanyVersionData( $company_id ) {
		Debug::Text( 'Sending Company Version Data...', __FILE__, __LINE__, __METHOD__, 10 );
		$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */

		$tt_version_data = [];
		$tt_version_data['registration_key'] = $this->getLocalRegistrationKey();
		$tt_version_data['company_id'] = $company_id;

		$tt_version_data['system_version'] = SystemSettingFactory::getSystemSettingValueByKey( 'system_version' );
		$tt_version_data['tax_engine_version'] = SystemSettingFactory::getSystemSettingValueByKey( 'tax_engine_version' );
		$tt_version_data['tax_data_version'] = SystemSettingFactory::getSystemSettingValueByKey( 'tax_data_version' );
		$tt_version_data['schema_version']['A'] = SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_A' );
		$tt_version_data['schema_version']['B'] = SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_B' );
		$tt_version_data['schema_version']['C'] = SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_C' );
		$tt_version_data['schema_version']['D'] = SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_D' );
		$tt_version_data['schema_version']['T'] = SystemSettingFactory::getSystemSettingValueByKey( 'schema_version_group_T' );

		if ( empty( $tt_version_data['system_version'] ) ) { //Just in case the version was not set in the system setting table for some reason.
			$tt_version_data['system_version'] = APPLICATION_VERSION;
		}

		if ( isset( $_SERVER['SERVER_SOFTWARE'] ) ) {
			$server_software = $_SERVER['SERVER_SOFTWARE'];
		} else {
			$server_software = 'N/A';
		}
		$server_name = Misc::getHostName( true );

		$db_server_info = $cf->db->ServerInfo();
		$sys_version_data = [
				'php_version'          => phpversion(),
				'zend_version'         => zend_version(),
				'web_server'           => $server_software,
				'database_type'        => $cf->db->databaseType,
				'database_version'     => $db_server_info['version'],
				'database_description' => $db_server_info['description'],
				'server_name'          => $server_name,
				'base_url'             => Environment::getBaseURL(),
				'php_os'               => PHP_OS,
				'system_information'   => php_uname(),
		];

		$version_data = array_merge( $tt_version_data, $sys_version_data );

		if ( isset( $version_data ) && is_array( $version_data ) ) {
			$retval = $this->getSoapObject()->saveCompanyVersionData( $version_data );
			Debug::Text( 'Sent Company Version Data!', __FILE__, __LINE__, __METHOD__, 10 );

			if ( $retval == false ) {
				Debug::Text( 'Server failed saving data!', __FILE__, __LINE__, __METHOD__, 10 );
			}

			//$this->printSoapDebug();

			return $retval;
		}
		Debug::Text( 'NOT Sending Company Version Data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function sendCompanyUserCountData( $company_id ) {
		$cuclf = TTnew( 'CompanyUserCountListFactory' ); /** @var CompanyUserCountListFactory $cuclf */
		$cuclf->getActiveUsers();
		$user_counts = [];
		if ( $cuclf->getRecordCount() > 0 ) {
			foreach ( $cuclf as $cuc_obj ) {
				$user_counts[$cuc_obj->getColumn( 'company_id' )]['active'] = $cuc_obj->getColumn( 'total' );
			}
		}

		$cuclf->getInActiveUsers();
		if ( $cuclf->getRecordCount() > 0 ) {
			foreach ( $cuclf as $cuc_obj ) {
				$user_counts[$cuc_obj->getColumn( 'company_id' )]['inactive'] = $cuc_obj->getColumn( 'total' );
			}
		}

		$cuclf->getDeletedUsers();
		if ( $cuclf->getRecordCount() > 0 ) {
			foreach ( $cuclf as $cuc_obj ) {
				$user_counts[$cuc_obj->getColumn( 'company_id' )]['deleted'] = $cuc_obj->getColumn( 'total' );
			}
		}

		if ( isset( $user_counts[$company_id] ) ) {
			$user_counts[$company_id]['registration_key'] = $this->getLocalRegistrationKey();
			$user_counts[$company_id]['company_id'] = $company_id;

			return $this->getSoapObject()->saveCompanyUserCountData( $user_counts[$company_id] );
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @return bool
	 */
	function sendCompanyUserLocationData( $company_id ) {
		if ( $company_id == '' ) {
			return false;
		}

		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getById( $company_id );
		if ( $clf->getRecordCount() > 0 ) {

			$location_data = [];
			$location_data['registration_key'] = $this->getLocalRegistrationKey();
			$location_data['company_id'] = $company_id;

			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$ulf->getByCompanyId( $company_id );
			if ( $ulf->getRecordCount() > 0 ) {
				foreach ( $ulf as $u_obj ) {

					$key = str_replace( ' ', '', strtolower( $u_obj->getCity() . $u_obj->getCity() . $u_obj->getCountry() ) );

					$location_data['location_data'][$key] = [
							'city'     => $u_obj->getCity(),
							'province' => $u_obj->getProvince(),
							'country'  => $u_obj->getCountry(),
					];
				}

				if ( isset( $location_data['location_data'] ) ) {
					return $this->getSoapObject()->saveCompanyUserLocationData( $location_data );
				}
			}
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param bool $force
	 * @return bool
	 */
	function sendCompanyData( $company_id, $force = false ) {
		Debug::Text( 'Sending Company Data... ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $company_id == '' ) {
			return false;
		}

		//Check for anonymous update notifications
		$anonymous_update_notify = 0;
		if ( $force == false || getTTProductEdition() == 10 ) {
			$anonymous_update_notify = (int)SystemSettingFactory::getSystemSettingValueByKey( 'anonymous_update_notify' );
		}
																																																																						/* @formatter:off */ $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; @$obj = new $obj_class; $hardware_id = $obj->getHardwareID(); unset($obj, $obj_class); /* @formatter:on */
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$clf->getById( $company_id );
		$company_data = array();
		if ( $clf->getRecordCount() > 0 ) {
			foreach ( $clf as $c_obj ) {
				$company_data['id'] = $c_obj->getId();
				$company_data['production'] = PRODUCTION;
				$company_data['registration_key'] = $this->getLocalRegistrationKey();
				$company_data['hardware_id'] = $hardware_id;
				$company_data['status_id'] = $c_obj->getStatus();
				$company_data['application_name'] = APPLICATION_NAME;
				$company_data['product_edition_id'] = $c_obj->getProductEdition();
				$company_data['is_professional_edition_available'] = getTTProductEdition();
				$company_data['product_edition_available'] = getTTProductEdition();
				$company_data['system_version'] = APPLICATION_VERSION;
				$company_data['industry_id'] = $c_obj->getIndustry();

				if ( $anonymous_update_notify == 0 ) {
					$company_data['name'] = $c_obj->getName();
					$company_data['short_name'] = $c_obj->getShortName();
					$company_data['business_number'] = $c_obj->getBusinessNumber();
					$company_data['address1'] = $c_obj->getAddress1();
					$company_data['address2'] = $c_obj->getAddress2();
					$company_data['work_phone'] = $c_obj->getWorkPhone();
					$company_data['fax_phone'] = $c_obj->getFaxPhone();

					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					if ( $c_obj->getBillingContact() != '' ) {
						$ulf->getById( $c_obj->getBillingContact() );
						if ( $ulf->getRecordCount() == 1 ) {
							$u_obj = $ulf->getCurrent();
							if ( $u_obj->getWorkEmail() != '' ) {
								$email = $u_obj->getWorkEmail();
							} else {
								$email = $u_obj->getHomeEmail();
							}
							$company_data['billing_contact'] = '"' . $u_obj->getFullName() . '" <' . $email . '>';
						}
					}
					if ( $c_obj->getAdminContact() != '' ) {
						$ulf->getById( $c_obj->getAdminContact() );
						if ( $ulf->getRecordCount() == 1 ) {
							$u_obj = $ulf->getCurrent();
							if ( $u_obj->getWorkEmail() != '' ) {
								$email = $u_obj->getWorkEmail();
							} else {
								$email = $u_obj->getHomeEmail();
							}
							$company_data['admin_contact'] = '"' . $u_obj->getFullName() . '" <' . $email . '>';
						}
					}
					if ( $c_obj->getSupportContact() != '' ) {
						$ulf->getById( $c_obj->getSupportContact() );
						if ( $ulf->getRecordCount() == 1 ) {
							$u_obj = $ulf->getCurrent();
							if ( $u_obj->getWorkEmail() != '' ) {
								$email = $u_obj->getWorkEmail();
							} else {
								$email = $u_obj->getHomeEmail();
							}
							$company_data['support_contact'] = '"' . $u_obj->getFullName() . '" <' . $email . '>';
						}
					}

					$logo_file = $c_obj->getLogoFileName( $c_obj->getId(), false ); //Ignore default logo
					if ( $logo_file != '' && file_exists( $logo_file ) ) {
						$company_data['logo'] = array('file_name' => $logo_file, 'data' => base64_encode( file_get_contents( $logo_file ) ));
					}
				}

				$company_data['city'] = $c_obj->getCity();
				$company_data['country'] = $c_obj->getCountry();
				$company_data['province'] = $c_obj->getProvince();
				$company_data['postal_code'] = $c_obj->getPostalCode();

				//Get Last user login date.
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getByCompanyId( $company_id, 1, null, array('last_login_date' => 'is not null'), array('last_login_date' => 'desc') );
				if ( $ulf->getRecordCount() == 1 ) {
					$company_data['last_login_date'] = $ulf->getCurrent()->getLastLoginDate();
				}
				//Get Last Punch Date (before today). Use PunchControl table only as its much faster.
				$plf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $plf */
				$plf->getByCompanyId( $company_id, 1, null, array(array('date_stamp' => ">= '" . $plf->db->BindTimeStamp( TTDate::getBeginDayEpoch( time() - ( 86400 * 30 ) ) ) . "'"), array('date_stamp' => "<= '" . $plf->db->BindTimeStamp( TTDate::getEndDayEpoch( time() ) ) . "'")), array('date_stamp' => 'desc') );
				if ( $plf->getRecordCount() == 1 ) {
					$company_data['last_punch_date'] = $plf->getCurrent()->getDateStamp();
				}
				//Get Last Schedule Date (before today)
				$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
				$slf->getByCompanyId( $company_id, 1, null, array(array('date_stamp' => ">= '" . $slf->db->BindTimeStamp( TTDate::getBeginDayEpoch( time() - ( 86400 * 30 ) ) ) . "'"), array('date_stamp' => "<= '" . $slf->db->BindTimeStamp( TTDate::getEndDayEpoch( time() ) ) . "'")), array('date_stamp' => 'desc') );
				if ( $slf->getRecordCount() == 1 ) {
					$company_data['last_schedule_date'] = $slf->getCurrent()->getStartTime();
				}
				//Get Last Pay Stub Date (before today)
				$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
				$pslf->getByCompanyId( $company_id, 1, null, array(array('a.start_date' => ">= '" . $pslf->db->BindTimeStamp( TTDate::getBeginDayEpoch( time() - ( 86400 * 30 ) ) ) . "'"), array('a.start_date' => "<= '" . $pslf->db->BindTimeStamp( TTDate::getEndDayEpoch( time() ) ) . "'")), array('a.start_date' => 'desc') );
				if ( $pslf->getRecordCount() == 1 ) {
					$company_data['last_pay_stub_date'] = $pslf->getCurrent()->getEndDate();
				}
				//Get Last Review Date (before today)
				$rclf = TTnew( 'UserReviewControlListFactory' ); /** @var UserReviewControlListFactory $rclf */
				$rclf->getByCompanyId( $company_id, 1, null, array(array('a.created_date' => ">= " . TTDate::getBeginDayEpoch( time() - ( 86400 * 30 ) )), array('a.created_date' => "<= " . TTDate::getEndDayEpoch( time() ))), array('a.created_date' => 'desc') );
				if ( $rclf->getRecordCount() == 1 ) {
					$company_data['last_user_review_date'] = $rclf->getCurrent()->getCreatedDate();
				}

				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					//Get Last Government Document Date (before today) -- Go back up to 1 year + 60 days to catch T4 filing deadline, otherwise this will be reset/blank mid-year for most companies.
					$gdlf = TTnew( 'GovernmentDocumentListFactory' ); /** @var UserReviewControlListFactory $rclf */
					$gdlf->getByCompanyId( $company_id, 1, null, array(array('a.created_date' => ">= " . TTDate::getBeginDayEpoch( time() - ( 86400 * 425 ) )), array('a.created_date' => "<= " . TTDate::getEndDayEpoch( time() ))), array('a.created_date' => 'desc') );
					if ( $gdlf->getRecordCount() == 1 ) {
						$company_data['last_government_document_date'] = $gdlf->getCurrent()->getCreatedDate();
					}
				}

				$retval = $this->getSoapObject()->saveCompanyData( $company_data );
				//$this->printSoapDebug();
				Debug::Text( '  Sent Company Data...', __FILE__, __LINE__, __METHOD__, 10 );

				if ( is_array( $retval ) ) {
					foreach ( $retval as $command => $command_data ) { //Must be v7.3.x or higher.
						Debug::Text( '    Running Command: ' . $command, __FILE__, __LINE__, __METHOD__, 10 );
						switch ( strtolower( $command ) ) {
							case 'system_settings':
								if ( is_array( $command_data ) ) {
									foreach ( $command_data as $name => $value ) {
										Debug::Text( 'Defining System Setting: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );
										SystemSettingFactory::setSystemSetting( $name, $value );
									}
									unset( $name, $value );
								}
								break;
							case 'config_file':
								if ( is_array( $command_data ) ) {
									Debug::Arr( $command_data, 'Defining Config File Settings: ', __FILE__, __LINE__, __METHOD__, 10 );
									$install_obj = new Install();
									$install_obj->writeConfigFile( $command_data );
									unset( $install_obj );
								}
								break;
							default:
								break;
						}
					}

					return true;
				} else {
					if ( $retval == false ) {
						Debug::Text( 'Server failed saving data!', __FILE__, __LINE__, __METHOD__, 10 );
					}

					return $retval;
				}
			}
		}

		return false;
	}

	//
	// Currency Data Feed functions
	//

	/**
	 * @param string $company_id UUID
	 * @param $currency_arr
	 * @param $base_currency
	 * @return array|bool
	 */
	function getCurrencyExchangeRates( $company_id, $currency_arr, $base_currency ) {
		/*

			Contact info@timetrex.com to request adding custom currency data feeds.

		*/
		if ( $company_id == '' ) {
			return false;
		}

		if ( !is_array( $currency_arr ) ) {
			return false;
		}

		if ( $base_currency == '' ) {
			return false;
		}

		$currency_rates = $this->getSoapObject()->getCurrencyExchangeRates( $this->getLocalRegistrationKey(), $company_id, $currency_arr, $base_currency );
		if ( isset( $currency_rates ) && is_array( $currency_rates ) && count( $currency_rates ) > 0 ) {
			return $currency_rates;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param $currency_arr
	 * @param $base_currency
	 * @param int $start_date EPOCH
	 * @param int $end_date EPOCH
	 * @return bool|array
	 */
	function getCurrencyExchangeRatesByDate( $company_id, $currency_arr, $base_currency, $start_date = null, $end_date = null ) {
		/*

			Contact info@timetrex.com to request adding custom currency data feeds.

		*/
		if ( $company_id == '' ) {
			return false;
		}

		if ( !is_array( $currency_arr ) ) {
			return false;
		}

		if ( $base_currency == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			$start_date = time();
		}

		if ( $end_date == '' ) {
			$end_date = time();
		}

		$currency_rates = $this->getSoapObject()->getCurrencyExchangeRatesByDate( $this->getLocalRegistrationKey(), $company_id, $currency_arr, $base_currency, $start_date, $end_date );

		if ( isset( $currency_rates ) && is_array( $currency_rates ) && count( $currency_rates ) > 0 ) {
			return $currency_rates;
		}

		return false;
	}

	/**
	 * @param bool $force
	 * @return bool
	 */
	function isNewVersionReadyForUpgrade( $force = false ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			$company_data['force'] = $force;

			$retval = $this->getSoapObject()->isNewVersionReadyForUpgrade( $company_data );
			Debug::Arr( array($company_data, $retval), 'Checking for new version based on this data: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	function getPreUpgradeStage( $pre_upgrade_file_name ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			$retval = $this->getSoapObject()->getPreUpgradeStage( $company_data, [ 'product_edition' => getTTProductEdition(), 'system_version' => APPLICATION_VERSION, 'php_version' => PHP_VERSION, 'php_os' => PHP_OS ] );
			Debug::Arr( array($company_data), 'Checking for pre-Upgrade Stage based on this data: ', __FILE__, __LINE__, __METHOD__, 10 );

			if ( !is_bool( $retval ) && is_string( $retval ) && strlen( $retval ) > 7 ) {
				Debug::Text( '  Writing pre-upgrade stage to disk...', __FILE__, __LINE__, __METHOD__, 10 );
				@unlink( $pre_upgrade_file_name );
				file_put_contents( $pre_upgrade_file_name, $retval );
			} else {
				Debug::Text( '  No pre-upgrade stage for this version...', __FILE__, __LINE__, __METHOD__, 10 );
			}

			return true;
		}

		return false;
	}

	/**
	 * @param bool $force
	 * @return bool
	 */
	function getUpgradeFileURL( $force = false ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			$company_data['force'] = $force;

			$retval = $this->getSoapObject()->getUpgradeFileURL( $company_data );

			return $retval;
		}

		return false;
	}

	//
	// Email relay through SOAP
	//

	/**
	 * @param $email
	 * @return bool
	 */
	function validateEmail( $email ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) && $email != '' ) {
			return $this->getSoapObject()->validateEmail( $email, $company_data );
		}

		return false;
	}

	/**
	 * @param $to
	 * @param $headers
	 * @param $body
	 * @return bool
	 */
	function sendEmail( $to, $headers, $body ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) && $to != '' && $body != '' ) {
			$retval = $this->getSoapObject()->sendEmail( $to, $headers, $body, $company_data );
			//Debug::Arr( $retval, 'TimeTrexSoapClient::sendEmail() response: ', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $retval === 'unsubscribe' ) {
				UserFactory::UnsubscribeEmail( $to );
				$retval = false;
			}

			return $retval;
		}

		return false;
	}

	function syncNotifications( $last_check_epoch, $force = false  ) {
		if ( PRODUCTION == false && $force !== true ) {
			Debug::Text( 'Not in production mode, not syncing notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		if ( DEMO_MODE == true ) {
			Debug::Text( 'In DEMO mode, not syncing notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) { //Must allow no title/body here for sending background push notifications.
			$retval = $this->getSoapObject()->syncNotifications( $last_check_epoch, $company_data );

			return $retval;
		}

		return false;
	}

	/**
	 * @param $device_tokens
	 * @param $title
	 * @param $body
	 * @param null $payload_data
	 * @param int $ttl
	 * @param string $priority
	 * @param bool $force
	 * @return bool
	 */
	function sendNotification( $device_tokens, $title, $body, $payload_data = null, $ttl = 0, $priority = 'normal', $force = false  ) {
		if ( PRODUCTION == false && $force !== true ) {
			Debug::Text( 'Not in production mode, not sending notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Text( '  Title: '. $title .' Body: '. $body, __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		if ( DEMO_MODE == true ) {
			Debug::Text( 'In DEMO mode, not sending notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) && $device_tokens != '' ) { //Must allow no title/body here for sending background push notifications.
			$retval = $this->getSoapObject()->sendNotification( $device_tokens, $title, $body, $payload_data, $ttl, $priority, $company_data );

			return $retval;
		}

		return false;
	}

	//
	// GEO Coding
	//

	/**
	 * @param $address1
	 * @param $address2
	 * @param $city
	 * @param $province
	 * @param $country
	 * @param $postal_code
	 * @return null
	 */
	function getGeoCodeByAddress( $address1, $address2, $city, $province, $country, $postal_code ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) && $city != '' && $country != '' ) {
			return $this->getSoapObject()->getGeoCodeByAddress( $address1, $address2, $city, $province, $country, $postal_code, $company_data );
		}

		return null; //Return NULL when no data available, and FALSE to try again later.
	}

	/**
	 * @param $ip_address
	 * @return null
	 */
	function getGeoIPData( $ip_address ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) && $ip_address != '' ) {
			return $this->getSoapObject()->getGeoIPData( $ip_address, $company_data );
		}

		return null; //Return NULL when no data available, and FALSE to try again later.
	}

	/**
	 * @param $rating
	 * @param $message
	 * @param object $u_obj
	 * @return null
	 */
	function sendUserFeedback( $rating, $message, $u_obj ) {
		$company_data = $this->getPrimaryCompanyData();
		if ( is_array( $company_data ) ) {
			$user_data = array('company_id' => $u_obj->getCompany(), 'host_name' => Misc::getHostName( false ), 'user_id' => $u_obj->getId(), 'permission_level' => $u_obj->getPermissionLevel(), 'company_name' => $u_obj->getCompanyObject()->getName(), 'full_name' => $u_obj->getFullName(), 'work_phone' => $u_obj->getWorkPhone(), 'work_email' => $u_obj->getWorkEmail(), 'home_email' => $u_obj->getHomeEmail(), 'user_ip_address' => Misc::getRemoteIPAddress(), 'user_agent' => ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : null ));

			return $this->getSoapObject()->sendUserFeedback( $rating, $message, $user_data, $company_data );
		}

		return null; //Return NULL when no data available, and FALSE to try again later.
	}
}

?>
