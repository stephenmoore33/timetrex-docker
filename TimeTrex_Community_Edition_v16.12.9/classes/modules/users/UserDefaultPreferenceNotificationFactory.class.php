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
 * @package Modules\Users
 */
class UserDefaultPreferenceNotificationFactory extends Factory {
	protected $table = 'user_default_preference_notification';
	protected $pk_sequence_name = 'user_default_preference_notification_id_seq'; //PK Sequence name

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_default_id' )->setFunctionMap( 'UserDefault' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'priority_id' )->setFunctionMap( 'Priority' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'device_id' )->setFunctionMap( 'Device' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'other_json' )->setType( 'json' )->setIsNull( true )
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

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserDefaultPreferenceNotification' )->setMethod( 'getUserDefaultPreferenceNotification' )
									->setSummary( 'Get user default preference notification records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserDefaultPreferenceNotification' )->setMethod( 'setUserDefaultPreferenceNotification' )
									->setSummary( 'Add or edit user default preference notification records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserDefaultPreferenceNotification' )->setMethod( 'getUserDefaultPreferenceNotificationDefaultData' )
									->setSummary( 'Get default user default preference notification data used for creating new records. Use this before calling setUserDefaultPreferenceNotification to get the correct default data.' ),
					),
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
			case 'notification_type':
			case 'type':
				$nf = TTNew( 'NotificationFactory' );
				$retval = $nf->getOptions( 'type' );
				break;
			case 'devices':
				$upnf = TTNew( 'UserPreferenceNotificationFactory' );
				$retval = $upnf->getOptions( 'devices' );
				break;
			case 'status':
				$upnf = TTNew( 'UserPreferenceNotificationFactory' );
				$retval = $upnf->getOptions( 'status' );
				break;
			case 'priority':
				$upnf = TTNew( 'UserPreferenceNotificationFactory' );
				$retval = $upnf->getOptions( 'priority' );
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
				'id'                   => 'ID',
				'user_id'              => 'User',
				'type_id'              => 'Type',
				'priority_id'          => 'Priority',
				'status_id'            => 'Status',
				'device_id'            => 'Device',
				'reminder_delay'       => 'ReminderDelay',
				'user_default_id'      => 'UserDefault',
				'deleted'              => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|mixed
	 */
	function getUserDefault() {
		return $this->getGenericDataValue( 'user_default_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUserDefault( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'user_default_id', $value );
	}

	/**
	 * @return int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return int
	 */
	function getPriority() {
		return $this->getGenericDataValue( 'priority_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPriority( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'priority_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return array
	 */
	function getDevice() {

		$value = $this->getGenericDataValue( 'device_id' );
		if ( $value !== false ) {
			//getArrayByBitMask returns array or false - but frontend and other areas expect an array.
			//Because of that I am making sure to always return an array.
			$retarr = Option::getArrayByBitMask( $value, $this->getOptions( 'devices' ) );

			if ( $retarr !== false ) {
				return $retarr;
			}
		}

		return [];
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDevice( $arr ) {
		$value = Option::getBitMaskByArray( $arr, $this->getOptions( 'devices' ) );

		return $this->setGenericDataValue( 'device_id', $value );
	}

	/**
	 * @return int
	 */
	function getReminderDelay() {
		return $this->getGenericJSONDataValue( 'reminder_delay' );
	}

	/**
	 * @param int $value Seconds.
	 * @return bool
	 */
	function setReminderDelay( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericJSONDataValue( 'reminder_delay', $value );
	}

	/**
	 * @param string $user_default_id UUID
	 * @param string $type_id
	 * @param string $id              UUID
	 * @return bool
	 */
	function isUnique( $user_default_id, $type_id ) {
		$ph = [
				'user_default_id' => TTUUID::castUUID( $user_default_id ),
				'type_id'         => (string)$type_id,
		];

		$query = 'select id from ' . $this->getTable() . ' where user_default_id = ? AND type_id = ? AND deleted = 0';
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
	 * @return bool
	 */
	function Validate() {

		if ( $this->getDeleted() == false ) {
			$ulf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user_default',
												   $ulf->getByID( $this->getUserDefault() ),
												   TTi18n::gettext( 'Invalid User Default' )
			);
		}

		// Type
		$this->Validator->inArrayKey( 'type',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);

		// Status

		$this->Validator->inArrayKey( 'status',
									  $this->getStatus(),
									  TTi18n::gettext( 'Incorrect Status.' ),
									  $this->getOptions( 'status' )
		);

		// Priority
		if ( $this->getPriority() != '' ) {
			$this->Validator->inArrayKey( 'priority',
										  $this->getPriority(),
										  TTi18n::gettext( 'Incorrect Priority.' ),
										  $this->getOptions( 'priority' )
			);
		}

		if ( $this->getReminderDelay() != '' ) {
			$this->Validator->isNumeric( 'reminder_delay',
										 $this->getReminderDelay(),
										 TTi18n::gettext( 'Reminder Delay must only be digits' )
			);
		}

		if ( $this->isUnique( $this->getUserDefault(), $this->getType() ) == false ) {
			$this->Validator->isTrue( 'type_id',
									  false,
									  TTi18n::gettext( 'Type already exists for this user default notification' ) );
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
}

?>
