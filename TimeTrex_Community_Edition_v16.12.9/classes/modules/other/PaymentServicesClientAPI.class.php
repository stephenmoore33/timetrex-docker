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
 * Class PaymentServicesClientAPI
 */
class PaymentServicesClientAPI {
	protected $url = 'https://paymentservices.timetrex.com/api/soap/api.php';

	protected $user_name = null;
	protected $password = null;

	protected $cookies = [];

	//protected $session_id = NULL;
	//protected $session_hash = NULL; //Used to determine if we need to login again because the URL or Session changed.
	protected $class_factory = null;
	protected $namespace = 'urn:api';
	protected $protocol_version = 1;

	protected $soap_obj = null; //Persistent SOAP object.

	/**
	 * PaymentServicesClientAPI constructor.
	 * @param null $class
	 * @param null $user_name
	 * @param null $password
	 * @param null $url
	 * @param null $cookies
	 */
	function __construct( $class = null, $user_name = null, $password = null, $url = null, $cookies = null ) {
		global $PAYMENTSERVICES_URL, $PAYMENTSERVICES_COOKIES, $PAYMENTSERVICES_USER, $PAYMENTSERVICES_PASSWORD;

		ini_set( 'default_socket_timeout', 3600 );

		if ( $url == '' ) {
			$url = $PAYMENTSERVICES_URL;
		}

		if ( $cookies == '' && ( isset( $PAYMENTSERVICES_COOKIES ) && is_array( $PAYMENTSERVICES_COOKIES ) ) ) {
			$cookies = $PAYMENTSERVICES_COOKIES;
		}

		if ( $url == '' ) {
			$url = $PAYMENTSERVICES_URL;
		}

		if ( $user_name == '' ) {
			$user_name = $PAYMENTSERVICES_USER;
		}

		if ( $password == '' ) {
			$password = $PAYMENTSERVICES_PASSWORD;
		}

		$this->setCookies( $cookies );
		$this->setUsername( $user_name );
		$this->setPassword( $password );
		$this->setURL( $url );
		$this->setClass( $class );

		return true;
	}

	/**
	 * @return SoapClient
	 */
	function getSoapClientObject() {
		$url_pieces[] = 'Class=' . $this->class_factory;

		if ( strpos( $this->url, '?' ) === false ) {
			$url_separator = '?';
		} else {
			$url_separator = '&';
		}

		$url = $this->url . $url_separator . 'v=' . $this->protocol_version . '&' . implode( '&', $url_pieces );

		//Try to maintain existing SOAP object as there could be cookies associated with it.
		if ( !is_object( $this->soap_obj ) ) {
			if ( PRODUCTION == false ) {
				//Allow self-signed certificates to be accepted when not in production mode.
				$stream_context_options = [
						'ssl' => [
							// set some SSL/TLS specific options
							'verify_peer'       => false,
							'verify_peer_name'  => false,
							'allow_self_signed' => true,
						],
				];
			} else {
				$stream_context_options = [];
			}
			$steam_context = stream_context_create( $stream_context_options );

			$retval = new SoapClient( null, [
												  'stream_context'     => $steam_context,
												  'location'           => $url,
												  'uri'                => $this->namespace,
												  'encoding'           => 'UTF-8',
												  'style'              => SOAP_RPC,
												  'use'                => SOAP_ENCODED,
												  'login'              => $this->user_name, //Username
												  'password'           => $this->password, //API Key
												  'connection_timeout' => 120,
												  'request_timeout'    => 7200,
												  'compression'        => SOAP_COMPRESSION_ACCEPT | SOAP_COMPRESSION_GZIP,
												  'trace'              => 1,
												  'exceptions'         => 0,
										  ]
			);

			if ( is_array( $this->cookies ) ) {
				foreach ( $this->cookies as $key => $value ) {
					$retval->__setCookie( $key, $value );
				}
			}
		} else {
			$retval = $this->soap_obj;
			$retval->__setLocation( $url );
		}

		return $retval;
	}

	/**
	 * @param $user_name
	 * @return bool
	 */
	function setUserName( $user_name ) {
		if ( $user_name != '' ) {
			$this->user_name = $user_name;

			return true;
		}

		return false;
	}

	/**
	 * @param $password
	 * @return bool
	 */
	function setPassword( $password ) {
		if ( $password != '' ) {
			$this->password = $password;

			return true;
		}

		return false;
	}

	/**
	 * @param $cookies
	 * @return bool
	 */
	function setCookies( $cookies ) {
		if ( is_array( $cookies ) ) {
			$this->cookies = $cookies;

			return true;
		}

		return false;
	}

	/**
	 * @param $url
	 * @return bool
	 */
	function setURL( $url ) {
		if ( $url != '' ) {
			$this->url = $url;

			return true;
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setClass( $value ) {
		$this->class_factory = trim( $value );

		return true;
	}

	/**
	 * @param $result
	 * @return mixed
	 */
	function isFault( $result ) {
		return $this->getSoapClientObject()->is_soap_fault( $result );
	}

	/**
	 * @param $function_name
	 * @param array $args
	 * @return bool|PaymentServicesClientAPIReturnHandler
	 */
	function __call( $function_name, $args = [] ) {
		if ( is_object( $this->getSoapClientObject() ) ) {
			$retval = call_user_func_array( [ $this->getSoapClientObject(), $function_name ], $args );

			if ( is_soap_fault( $retval ) ) {
				//trigger_error( 'SOAP Fault: (Code: ' . $retval->faultcode . ', String: ' . $retval->faultstring . ') - Request: ' . $this->getSoapClientObject()->__getLastRequest() . ' Response: ' . $this->getSoapClientObject()->__getLastResponse(), E_USER_NOTICE );
				//Debug::Arr( array('last_request' => $this->getSoapClientObject()->__getLastRequest(), 'last_response' => $this->getSoapClientObject()->__getLastResponse()), 'SOAP Fault: '. $retval->faultstring .' Code: '. $retval->faultcode, __FILE__, __LINE__, __METHOD__, 10);
				throw new Exception( 'SOAP Fault: ' . $retval->faultstring . ' (Code: ' . $retval->faultcode . ')', (int)$retval->faultcode );

				//return false;
			}

			return new PaymentServicesClientAPIReturnHandler( $function_name, $args, $retval );
		}

		return false;
	}
}

/**
 * @package API\PaymentServicesClientAPI
 */
class PaymentServicesClientAPIReturnHandler {
	/*
	'api_retval' => $retval,
	'api_details' => array(
					'code' => $code,
					'description' => $description,
					'record_details' => array(
											'total' => $validator_stats['total_records'],
											'valid' => $validator_stats['valid_records'],
											'invalid' => ($validator_stats['total_records']-$validator_stats['valid_records'])
											),
					'details' =>  $details,
					)
	*/
	protected $function_name = null;
	protected $args = null;
	protected $result_data = false;

	/**
	 * PaymentServicesClientAPIReturnHandler constructor.
	 * @param $function_name
	 * @param $args
	 * @param $result_data
	 */
	function __construct( $function_name, $args, $result_data ) {
		$this->function_name = $function_name;
		$this->args = $args;
		$this->result_data = $result_data;

		return true;
	}

	/**
	 * @return bool
	 */
	function getResultData() {
		return $this->result_data;
	}

	/**
	 * @return null
	 */
	function getFunction() {
		return $this->function_name;
	}

	/**
	 * @return null
	 */
	function getArgs() {
		return $this->args;
	}

	/**
	 * @return string
	 */
	function __toString() {
		$eol = "<br>\n";

		$output = [];
		$output[] = '=====================================';
		$output[] = 'Function: ' . $this->getFunction() . '()';
		if ( is_object( $this->getArgs() ) || is_array( $this->getArgs() ) ) {
			$output[] = 'Args: ' . count( $this->getArgs() );
		} else {
			$output[] = 'Args: ' . $this->getArgs();
		}
		$output[] = '-------------------------------------';
		$output[] = 'Returned:';
		$output[] = ( $this->isValid() === true ) ? 'IsValid: YES' : 'IsValid: NO';
		if ( $this->isValid() === true ) {
			$output[] = 'Return Value: ' . $this->getResult();
		} else {
			$output[] = 'Code: ' . $this->getCode();
			$output[] = 'Description: ' . $this->getDescription();
			$output[] = 'Details: ';

			$details = $this->getDetails();
			if ( is_array( $details ) ) {
				foreach ( $details as $row => $detail ) {
					$output[] = 'Row: ' . $row;
					foreach ( $detail as $field => $msgs ) {
						$output[] = '--Field: ' . $field;
						foreach ( $msgs as $key => $msg ) {
							$output[] = '----Message: ' . $msg;
						}
					}
				}
			}
		}
		$output[] = '=====================================';
		$output[] = '';

		return implode( $eol, $output );
	}

	/**
	 * @return bool
	 */
	function isValid() {
		if ( isset( $this->result_data['api_retval'] ) ) {
			return (bool)$this->result_data['api_retval'];
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function isError() { //Opposite of isValid()
		if ( isset( $this->result_data['api_retval'] ) ) {
			if ( $this->result_data['api_retval'] === false ) {
				return true;
			} else {
				return false;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getCode() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['code'] ) ) {
			return $this->result_data['api_details']['code'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getDescription() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['description'] ) ) {
			return $this->result_data['api_details']['description'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getDetails() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['details'] ) ) {
			return $this->result_data['api_details']['details'];
		}

		return false;
	}

	/**
	 * @return bool|string
	 */
	function getDetailsDescription() {
		$details = $this->getDetails();
		if ( is_array( $details ) ) {
			$retval = [];

			foreach ( $details as $key => $row_details ) {
				foreach ( $row_details as $field => $field_details ) {
					foreach ( $field_details as $detail ) {
						$retval[] = '[' . $field . '] ' . $detail;
					}
				}
			}

			return implode( ' ', $retval );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getRecordDetails() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['record_details'] ) ) {
			return $this->result_data['api_details']['record_details'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getTotalRecords() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['record_details'] ) && isset( $this->result_data['api_details']['record_details']['total_records'] ) ) {
			return $this->result_data['api_details']['record_details']['total_records'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getValidRecords() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['record_details'] ) && isset( $this->result_data['api_details']['record_details']['valid_records'] ) ) {
			return $this->result_data['api_details']['record_details']['valid_records'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getInValidRecords() {
		if ( isset( $this->result_data['api_details'] ) && isset( $this->result_data['api_details']['record_details'] ) && isset( $this->result_data['api_details']['record_details']['invalid_records'] ) ) {
			return $this->result_data['api_details']['record_details']['invalid_records'];
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getResult() {
		if ( isset( $this->result_data['api_retval'] ) ) {
			return $this->result_data['api_retval'];
		} else {
			return $this->result_data;
		}
	}
}

?>