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
 * @package Modules\AuthenticationTrustedDevice
 */
class AuthenticationTrustedDeviceFactory extends Factory {
	protected $table = 'authentication_trusted_device';
	protected $pk_sequence_name = 'trusted_device_id_seq'; //PK Sequence name

	public $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'device_id' )->setFunctionMap( 'DeviceID' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'device_user_agent' )->setFunctionMap( 'DeviceUserAgent' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'ip_address' )->setFunctionMap( 'IPAddress' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'location' )->setFunctionMap( 'Location' )->setType( 'varchar' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No Search Fields.
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			//No API Methods.
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'         => 'ID',
				'user_id'    => 'User',
				'device_id'  => 'DeviceID',
				'ip_address' => 'IPAddress',
				'location'   => 'Location',
				'deleted'    => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return string
	 */
	function getDeviceID() {
		return $this->getGenericDataValue( 'device_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDeviceID( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'device_id', $value );
	}

	/**
	 * @return string
	 */
	function getDeviceUserAgent() {
		return $this->getGenericDataValue( 'device_user_agent' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDeviceUserAgent( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'device_user_agent', $value );
	}

	/**
	 * @return string
	 */
	function getIPAddress() {
		return $this->getGenericDataValue( 'ip_address' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIPAddress( $value ) {
		$value = trim( $value );

		if ( empty( $value ) == true ) {
			$value = Misc::getRemoteIPAddress();
		}

		return $this->setGenericDataValue( 'ip_address', $value );
	}

	/**
	 * @return string
	 */
	function getLocation() {
		return $this->getGenericDataValue( 'location' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLocation( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'location', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$this->Validator->isResultSetWithRows( 'user_id',
											   $ulf->getByID( $this->getUser() ),
											   TTi18n::gettext( 'Invalid Employee' )
		);

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * Checks if the trusted IP address matches the current IP address or is within
	 *  a specified "acceptable" range. Supports both IPv4 and IPv6 addresses.
	 *
	 * @param $ip_address
	 * @param $trusted_ip_address
	 * @return bool
	 */
	function checkIPAddress( $ip_address, $trusted_ip_address ) {
		if ( $this->getIPAddress() == $ip_address ) {
			Debug::Text( '  IP address is an exact match... Current IP: ' . $ip_address . ' Trusted Device IP: ' . $trusted_ip_address, __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		} else if ( ( strpos( $ip_address, ':' ) === false && Misc::isIPinRange( $ip_address, $trusted_ip_address, 24 ) ) ) { //IPv4
			Debug::Text( '  IP address is within /24 range... Current IP: ' . $ip_address . ' Trusted Device IP: ' . $trusted_ip_address, __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		} else if ( ( strpos( $ip_address, ':' ) !== false && Misc::isIPinRange( $ip_address, $trusted_ip_address, 64 ) ) ) { //IPv6 -- These can often have "privacy IPs", so they change constantly.
			Debug::Text( '  IP address is within /64 range... Current IP: ' . $ip_address . ' Trusted Device IP: ' . $trusted_ip_address, __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		} else {
			Debug::Text( '  IP address does not match, and not within IPv4/24 or IPv6/64 range... Current IP: '. $ip_address .' Trusted Device IP: '. $trusted_ip_address, __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}
	}
}