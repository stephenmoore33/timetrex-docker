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
class UserPreferenceNotificationFactory extends Factory {
	protected $table = 'user_preference_notification';
	protected $pk_sequence_name = 'user_preference_notification_id_seq'; //PK Sequence name

	public $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'priority_id' )->setFunctionMap( 'Priority' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'device_id' )->setFunctionMap( 'Device' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'other_json' )->setFunctionMap( 'OtherJson' )->setType( 'json' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'status' )->setType( 'text' )->setColumn( 'b.status_id' ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'b.status_id' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserPreferenceNotification' )->setMethod( 'getUserPreferenceNotification' )
									->setSummary( 'Get user preference notification records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserPreferenceNotification' )->setMethod( 'setUserPreferenceNotification' )
									->setSummary( 'Add or edit user preference notification records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserPreferenceNotification' )->setMethod( 'deleteUserPreferenceNotification' )
									->setSummary( 'Delete user preference notification records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserPreferenceNotification' )->setMethod( 'getUserPreferenceNotification' ) ),
											   ) ),
							TTSAPI::new( 'APIUserPreferenceNotification' )->setMethod( 'getUserPreferenceNotificationDefaultData' )
									->setSummary( 'Get default user preference notification data used for creating new records. Use this before calling setUserPreferenceNotification to get the correct default data.' ),
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
			case 'device':
			case 'devices':
				$retval = [
					//Used as a bitmask to store user notification preference.

					//1			=> TTi18n::gettext( '' ),
					//2			=> TTi18n::gettext( '' ),
					4			=> TTi18n::gettext( 'Web Push' ),
					//8 		=> TTi18n::gettext( '' ),
					//16  		=> TTi18n::gettext( '' ),
					//32 		=> TTi18n::gettext( '' ),
					//64 		=> TTi18n::gettext( '' ),
					//128 		=> TTi18n::gettext( '' ),
					256 		=> TTi18n::gettext( 'Email (Work)' ),
					512 		=> TTi18n::gettext( 'Email (Home)' ),
					//1024		=> TTi18n::gettext( '' ),
					//2048 		=> TTi18n::gettext( '' ),
					//4096 		=> TTi18n::gettext( '' ),
					//8192 		=> TTi18n::gettext( '' ),
					//16384 	=> TTi18n::gettext( '' ),
					32768 	=> TTi18n::gettext( 'App Push Notification' ),
					//65536 	=> TTi18n::gettext( '' ),
					//131072 	=> TTi18n::gettext( '' ),
					//262144 	=> TTi18n::gettext( '' ),
					//1048576 	=> TTi18n::gettext( '' ),
					//2097152 	=> TTi18n::gettext( '' ),
					4194304 	=> TTi18n::gettext( 'SMS (Work)' ),
					8388608 	=> TTi18n::gettext( 'SMS (Home)' ),
					//16777216 	=> TTi18n::gettext( '' ),
					//33554432 	=> TTi18n::gettext( '' ),
				];
				break;
			case 'status':
				//If the notification itself is enabled or disabled.
				$retval = [
						10		=> TTi18n::gettext( 'Enabled' ),
						20		=> TTi18n::gettext( 'Disabled' ),
				];
				break;
			case 'priority_id':
			case 'priority':
				$retval = [
						1  => TTi18n::gettext( 'Critical' ), //Do everything we can to get the user attention, including ring on their phone? Likely just used for reminders to punch back in.
						2  => TTi18n::gettext( 'High' ),
						5  => TTi18n::gettext( 'Normal' ),
						10 => TTi18n::gettext( 'Low' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-status'      => TTi18n::gettext( 'Status' ),
						'-1010-type'        => TTi18n::gettext( 'Type' ),
						'-1020-device'      => TTi18n::gettext( 'Device' ),
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
				'id'                          => 'ID',
				'user_id'					  => 'User',
				'type_id'                     => 'Type',
				'priority_id'                 => 'Priority',
				'type'                        => false,
				'status_id'                   => 'Status',
				'status'                      => false,
				'device_id'                   => 'Device',
				'device'                      => false,
				'reminder_delay'              => 'ReminderDelay',
				'deleted'                     => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @param $exclude_preference_notifications
	 * @return array
	 */
	function getUserPreferenceNotificationTypeDefaultValues( $exclude_preference_notifications ) {
		if ( !is_array( $exclude_preference_notifications ) ) {
			$exclude_preference_notifications = [];
		}
		$type_options = $this->getTypeOptions();

		$retarr = [];

		foreach ( $type_options as $type_id => $preference_notification_name ) {
			//Skip excluded notifications
			if ( in_array( $type_id, $exclude_preference_notifications ) ) {
				continue;
			}

			switch ( $type_id ) {
				//Disable these notifications by default.
				case 'reminder_punch_normal_in': //Reminder - Start of Shift
				case 'reminder_punch_normal_out': //Reminder - End of Shift
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 10, //Enabled
							'type_id'                 => $type_id,
							'priority_id'             => 2, //High, not critical, but still try to alert the user that they should be punching.
							'device_id'               => [4, 32768], //Do not send email notifications.
							'reminder_delay'          => ( 60 * 15 ),
					];
					break;
				case 'reminder_punch_transfer': //Reminder - End of Shift
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 20, //Disabled
							'type_id'                 => $type_id,
							'priority_id'             => 2, //High, not critical, but still try to alert the user that they should be punching.
							'device_id'               => [4, 32768], //Do not send email notifications.
							'reminder_delay'          => ( 60 * 120 ), //2hrs
					];
					break;
				case 'reminder_punch_break_out': //Reminder - Start Break
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 20, //Disabled
							'type_id'                 => $type_id,
							'priority_id'             => 2, //High, not critical, but still try to alert the user that they should be punching.
							'device_id'               => [4, 32768], //Do not send email notifications.
							'reminder_delay'          => ( 60 * 2 ),
					];
					break;
				case 'reminder_punch_lunch_out': //Reminder - Start Lunch
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 20, //Disabled
							'type_id'                 => $type_id,
							'priority_id'             => 2, //High, not critical, but still try to alert the user that they should be punching.
							'device_id'               => [4, 32768], //Do not send email notifications.
							'reminder_delay'          => ( 60 * 5 ),
					];
					break;
				case 'reminder_punch_break_in': //Reminder - End Break
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 10, //Enabled
							'type_id'                 => $type_id,
							'priority_id'             => 1, //Critical, try to get users attention immediately so they can punch back in, as we can be quite certain the user is forgotten.
							'device_id'               => [4, 32768], //Do not send email notifications.
							'reminder_delay'          => ( 60 * 1 ),
					];
					break;
				case 'reminder_punch_lunch_in': //Reminder - End Lunch
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 10, //Enabled
							'type_id'                 => $type_id,
							'priority_id'             => 1, //Critical, try to get users attention immediately so they can punch back in, as we can be quite certain the user is forgotten.
							'device_id'               => [4, 32768], //Do not send email notifications.
							'reminder_delay'          => ( 60 * 2 ),
					];
					break;
				case 'reminder_pay_period_transaction_date': //Reminder - Pay Period Transaction
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 10, //Enabled
							'type_id'                 => $type_id,
							'priority_id'             => 2, //2=High
							'device_id'               => [4, 256, 512, 32768],
							'reminder_delay'          => ( 86400 * -2 ), //-2 Days
					];
					break;
				case 'payment_services': //Payment Services notifications/errors.
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 10, //Enabled
							'type_id'                 => $type_id,
							'priority_id'             => 1, //1=Critical
							'device_id'               => [4, 256, 512, 32768],
					];
					break;
				case 'punch':
				case 'schedule':
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 10, //Enabled
							'type_id'                 => $type_id,
							'priority_id'             => 2, //High
							'device_id'               => [4, 256, 512, 32768],
					];
					break;
				case 'exception_own_critical':
				case 'exception_child_critical':
				$retarr[$type_id] = [
						'id'                      => TTUUID::getNotExistID(),
						'status_id'               => 10, //Enabled
						'type_id'                 => $type_id,
						'priority_id'             => 2, //High priority, so it draws a little more attention and possibly gets to the app a little sooner.
						'device_id'               => [4, 256, 32768],
				];
					break;
				case 'exception_own_high':
				case 'exception_child_high':
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 10, //Enabled
							'type_id'                 => $type_id,
							'priority_id'             => 5, //Normal priority, as likely the user has to submit a request anyways, so its not urgent.
							'device_id'               => [4, 256, 32768],
					];
					break;
				default:
					$retarr[$type_id] = [
							'id'                      => TTUUID::getNotExistID(),
							'status_id'               => 10, //Enabled
							'type_id'                 => $type_id,
							'priority_id'             => 5, //Normal
							'device_id'               => [4, 256, 32768],
					];
					break;
			}
		}
		unset( $preference_notification_name ); // code standards

		return $retarr;
	}

	/**
	 * @return bool
	 */
	function getName() {
		return Option::getByKey( $this->getType(), $this->getTypeOptions( getTTProductEdition() ) );
	}

	/**
	 * @return bool
	 */
	function getTypeOptions() {
		$options = $this->getOptions( 'type' );

		return $options;
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
	 * @return array
	 */
	function getDevice() {

		$value = $this->getGenericDataValue( 'device_id' );
		if ( $value !== false ) {
			//getArrayByBitMask returns array or false - but frontend and other areas expect an array.
			//Because of that I am making sure to always return an array.
			$retarr = Option::getArrayByBitMask( $value, $this->getOptions( 'devices' ) );

			if( $retarr !== false ) {
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
		$value = Option::getBitMaskByArray( $arr, $this->getOptions( 'devices') );

		return $this->setGenericDataValue( 'device_id', $value );
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
	 * @param string $user_id UUID
	 * @param string $type_id
	 * @param string $id      UUID
	 * @return bool
	 */
	function isUnique( $user_id, $type_id, $id ) {
		$ph = [
				'id'                          => TTUUID::castUUID( $id ),
				'user_id'                     => TTUUID::castUUID( $user_id ),
				'type_id'                     => (string)$type_id
		];

		$query = 'select id from ' . $this->getTable() . ' where id != ? AND user_id = ? AND type_id = ? AND deleted = 0';
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

		if ( $this->isUnique( $this->getUser(), $this->getType(), $this->getID() ) == false ) {
			$this->Validator->isTrue( 'type_id',
									  false,
									  TTi18n::gettext( 'Type already exists for this employee' ) );
		}

		// Priority
		if ( $this->getPriority() != '' ) {
			$this->Validator->inArrayKey( 'priority',
										  $this->getPriority(),
										  TTi18n::gettext( 'Incorrect priority' ),
										  $this->getOptions( 'priority' )
			);
		}

		if ( $this->getReminderDelay() != '' ) {
			$this->Validator->isNumeric( 'reminder_delay',
										 $this->getReminderDelay(),
										 TTi18n::gettext( 'Reminder Delay must only be digits' )
			);
		}

		return true;
	}

	function postSave() {
		$this->removeCache( $this->getUser() .'_'. $this->getType() );

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
	 * This is called for every record everytime, and doesn't help much because of that.
	 * This has to be enabled to properly log modifications though.
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee Preference Notification' ) . ' - ' . TTi18n::getText( 'Type' ) . ': ' . Option::getByKey( $this->getType(), $this->getOptions( 'type' ) ), null, $this->getTable(), $this );
	}

	/**
	 * Parses which preferences to show to the user. For example this way a user without the ability to view job applications wont see a notification preference for them.
	 * @param array $notification_preference_data_array
	 * @param object $u_obj
	 * @return array
	 */
	static function filterUserNotificationPreferencesByPermissions( $notification_preference_data_array, $u_obj ) {
		$retarr = [];
		foreach ( $notification_preference_data_array as $notification_preference_data ) {
			$type_id = $notification_preference_data['type_id'];

			switch ( $type_id ) {
				case 'request':
					if ( $u_obj->getPermissionObject()->Check( 'request', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| ( $u_obj->getPermissionObject()->Check( 'request', 'view', $u_obj->getId(), $u_obj->getCompany() ) ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'request_authorize':
					if ( $u_obj->getPermissionObject()->Check( 'authorization', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							&& $u_obj->getPermissionObject()->Check( 'authorization', 'view', $u_obj->getId(), $u_obj->getCompany() )
							&& $u_obj->getPermissionObject()->Check( 'request', 'authorize', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'timesheet_verify':
					if ( $u_obj->getPermissionObject()->Check( 'punch', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							&& $u_obj->getPermissionObject()->Check( 'punch', 'view_own', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;;
					}
					break;
				case 'timesheet_authorize':
					if ( $u_obj->getPermissionObject()->Check( 'punch', 'view_child', $u_obj->getId(), $u_obj->getCompany() )
							&& $u_obj->getPermissionObject()->getPermissionChildren( 'punch', 'view', $u_obj->getId(), $u_obj->getCompany() ) !== false ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'expense_verify':
					if ( $u_obj->getPermissionObject()->Check( 'user_expense', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'user_expense', 'view', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'user_expense', 'view_own', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'user_expense', 'view_child', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'expense_authorize':
					if ( $u_obj->getPermissionObject()->Check( 'authorization', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							&& $u_obj->getPermissionObject()->Check( 'authorization', 'view', $u_obj->getId(), $u_obj->getCompany() )
							&& $u_obj->getPermissionObject()->Check( 'user_expense', 'authorize', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'reminder_punch_normal_in': //Reminder - Start of Shift
				case 'reminder_punch_normal_out': //Reminder - End of Shift
				case 'reminder_punch_transfer': //Reminder - Transfer
				case 'reminder_punch_break_out': //Reminder - Start Break
				case 'reminder_punch_break_in': //Reminder - End Break
				case 'reminder_punch_lunch_out': //Reminder - Start Lunch
				case 'reminder_punch_lunch_in': //Reminder - End Lunch
				case 'punch':
					if ( getTTProductEdition() > TT_PRODUCT_COMMUNITY && $u_obj->getPermissionObject()->Check( 'punch', 'punch_in_out', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'reminder_pay_period_transaction_date':
					if ( $u_obj->getPermissionObject()->Check( 'pay_period_schedule', 'edit', $u_obj->getId(), $u_obj->getCompany() ) && $u_obj->getPermissionObject()->Check( 'pay_stub', 'view', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'payment_services': //Who is processing payroll.
					if ( $u_obj->getPermissionObject()->Check( 'pay_period_schedule', 'edit', $u_obj->getId(), $u_obj->getCompany() ) && $u_obj->getPermissionObject()->Check( 'pay_stub', 'view', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'exception_own_critical':
				case 'exception_own_high':
				case 'exception_own_medium':
				case 'exception_own_low':
					if ( $u_obj->getPermissionObject()->Check( 'punch', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							&&
							( $u_obj->getPermissionObject()->Check( 'punch', 'view', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'punch', 'view_own', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'punch', 'view_child', $u_obj->getId(), $u_obj->getCompany() ) ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'exception_child_critical':
				case 'exception_child_high':
				case 'exception_child_medium':
				case 'exception_child_low':
					if ( $u_obj->getPermissionObject()->Check( 'punch', 'view_child', $u_obj->getId(), $u_obj->getCompany() )
							&& $u_obj->getPermissionObject()->getPermissionChildren( 'punch', 'view', $u_obj->getId(), $u_obj->getCompany() ) !== false ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'schedule':
					if ( $u_obj->getPermissionObject()->Check( 'schedule', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'schedule', 'view', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'schedule', 'view_own', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'schedule', 'view_child', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'message':
					if ( $u_obj->getPermissionObject()->Check( 'message', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'message', 'view', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'message', 'view_own', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'message', 'view_child', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'pay_stub':
					if ( $u_obj->getPermissionObject()->Check( 'pay_stub', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'pay_stub', 'view', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'pay_stub', 'view_own', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'pay_stub', 'view_child', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'pay_period':
					if ( $u_obj->getPermissionObject()->Check( 'pay_period', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'pay_period', 'edit', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'pay_period', 'edit_own', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'pay_period', 'edit_child', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'payroll_remittance_agency_event':
					if ( $u_obj->getPermissionObject()->Check( 'payroll_remittance_agency', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'payroll_remittance_agency', 'view', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'payroll_remittance_agency', 'view_own', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'government_document':
					if ( $u_obj->getPermissionObject()->Check( 'government_document', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'government_document', 'view', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'government_document', 'view_own', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'government_document', 'view_child', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				case 'job_application_manager':
					if ( $u_obj->getPermissionObject()->Check( 'job_applicant', 'enabled', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'job_applicant', 'view', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'job_applicant', 'view_own', $u_obj->getId(), $u_obj->getCompany() )
							|| $u_obj->getPermissionObject()->Check( 'job_applicant', 'view_child', $u_obj->getId(), $u_obj->getCompany() ) ) {
						$retarr[] = $notification_preference_data;
					}
					break;
				default:
					//Do nothing
					break;
			}
		}

		return $retarr;
	}
}