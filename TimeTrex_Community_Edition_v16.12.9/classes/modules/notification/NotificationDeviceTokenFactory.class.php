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
 * @package Modules\Notification
 */
class NotificationDeviceTokenFactory extends Factory {
	protected $table = 'device_token';
	protected $pk_sequence_name = 'device_token_id_seq'; //PK Sequence name

	public $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'platform_id' )->setFunctionMap( 'Platform' )->setType( 'smallint' ),
							TTSCol::new( 'device_token' )->setFunctionMap( 'DeviceToken' )->setType( 'varchar' ),
							TTSCol::new( 'user_agent' )->setFunctionMap( 'UserAgent' )->setType( 'varchar' ),
							TTSCol::new( 'created_date' )->setType( 'integer' ),
							TTSCol::new( 'created_by' )->setType( 'uuid' ),
							TTSCol::new( 'updated_date' )->setType( 'integer' ),
							TTSCol::new( 'updated_by' )->setType( 'uuid' ),
							TTSCol::new( 'deleted_date' )->setType( 'integer' ),
							TTSCol::new( 'deleted_by' )->setType( 'uuid' ),
							TTSCol::new( 'deleted' )->setType( 'integer' )->setIsNull( false )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'platform_id' )->setType( 'smallint' )->setColumn( 'a.platform_id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APINotificationDeviceToken' )->setMethod( 'getNotificationDeviceToken' )
									->setSummary( 'Get notification device token records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APINotificationDeviceToken' )->setMethod( 'setNotificationDeviceToken' )
									->setSummary( 'Add or edit notification device token records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APINotificationDeviceToken' )->setMethod( 'deleteNotificationDeviceToken' )
									->setSummary( 'Delete notification device token records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APINotificationDeviceToken' )->setMethod( 'getNotificationDeviceToken' ) ),
											   ) ),
					)
			);
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
		switch ( $name ) {
			case 'platform':
				$retval = [
					100			=> TTi18n::gettext( 'Web' ),
					200			=> TTi18n::gettext( 'iOS' ),
					300			=> TTi18n::gettext( 'Android' ),
				];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                        => 'ID',
				'platform_id'               => 'Platform',
				'device_token'				=> 'DeviceToken',
				'user_agent'                => 'UserAgent',
				'user_id'					=> 'User',
				'deleted'				 	=> 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|string
	 */
	function getPlatform() {
		return $this->getGenericDataValue( 'platform_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPlatform( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'platform_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getDeviceToken() {
		return (string)$this->getGenericDataValue( 'device_token' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDeviceToken( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'device_token', $value );
	}

	/**
	 * @return bool|string
	 */
	function getUserAgent() {
		return (string)$this->getGenericDataValue( 'user_agent' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserAgent( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_agent', $value );
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
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
	 * @param string $user_id UUID
	 * @param string $device_token
	 * @param string $id      UUID
	 * @return bool
	 */
	function isUnique($user_id, $device_token, $id ) {
		$ph = [
				'id'                          => TTUUID::castUUID( $id ),
				'user_id'                     => TTUUID::castUUID( $user_id ),
				'device_token'                => (string)$device_token
		];

		$query = 'select id from ' . $this->getTable() . ' where id != ? AND user_id = ? AND device_token = ? AND deleted = 0';
		$id = $this->db->GetOne( $query, $ph );

		if ( $id === false ) {
			return true;
		} else {
			if ( $id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Users that clear cache/cookies often (ie: when exiting their browser) will create a new device token every login causing us to attempt to send notifications
	 * to potentially hundreds of devices. To prevent this we limit the number of device tokens per used and platform_id to 10.
	 * @return bool
	 */
	function purgeExcessDeviceTokensForUserAndPlatform( $user_id, $platform_id ) {

		$ph = [
				'user_id'     => TTUUID::castUUID( $user_id ),
				'platform_id' => (string)$platform_id,

				'user_id_b'     => TTUUID::castUUID( $user_id ),
				'platform_id_b' => (string)$platform_id,
		];

		$query = 'UPDATE device_token set deleted = 1, updated_date = '. time() .' WHERE user_id = ? AND platform_id = ? AND deleted = 0 AND id NOT IN ( SELECT id FROM device_token where user_id = ? AND platform_id = ? AND deleted = 0 ORDER BY created_date DESC LIMIT 10 )';
		$this->db->Execute( $query, $ph );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( '  Purging excess device tokens for... User ID: '. $user_id .' Platform ID: '. $platform_id .' Affected Rows: ' . $this->getAffectedRows(), __FILE__, __LINE__, __METHOD__, 10 );
		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		if ( $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
		}

		// Platform
		$this->Validator->inArrayKey( 'platform',
									  $this->getPlatform(),
									  TTi18n::gettext( 'Incorrect Platform for device token.' ),
									  $this->getOptions( 'platform' )
		);

		if ( $this->isUnique($this->getUser(), $this->getDeviceToken(), $this->getID() ) == false ) {
			$this->Validator->isTrue( 'device_id',
									  false,
									  TTi18n::gettext( 'Duplicate device token already exists' ) );
		}

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
	 * @noinspection PhpMissingBreakStatementInspection
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
	 * This is called for every record everytime, and doesn't help much because of that.
	 * This has to be enabled to properly log modifications though.
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Notification Device Token' ) . ' - ' . TTi18n::getText( 'Type' ) . ': ' . Option::getByKey( $this->getPlatform(), $this->getOptions( 'platform' ) ), null, $this->getTable(), $this );
	}
}