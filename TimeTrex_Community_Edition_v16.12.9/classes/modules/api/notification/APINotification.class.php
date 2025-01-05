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
 * @package API\Notification
 */
class APINotification extends APIFactory {
	protected $main_class = 'NotificationFactory';

	/**
	 * APINotification constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get notification data for one or more notifications.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getNotification( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		// Notifications are always enabled and do not require permissions.
		//if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
		//		|| !( $this->getPermissionObject()->Check( 'user_preference', 'view' ) || $this->getPermissionObject()->Check( 'user_preference', 'view_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'view_child' ) ) ) {
		//	return $this->getPermissionObject()->PermissionDenied();
		//}

		//No need to check for permission_children, as the logged in user can only view their own notification anyways.
		$data['filter_data']['current_user_id'] = $this->getCurrentUserObject()->getId();

		$ndtlf = TTnew( 'NotificationListFactory' ); /** @var NotificationListFactory $ndtlf */
		$ndtlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $ndtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $ndtlf->getRecordCount() > 0 ) {
			$this->setPagerObject( $ndtlf );

			$retarr = [];
			foreach ( $ndtlf as $ndt_obj ) {
				$retarr[] = $ndt_obj->getObjectAsArray( $data['filter_columns'] );
			}

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Returns an int count of unread notifications for current user.
	 * @return int|bool
	 */
	function getUnreadNotifications() {

		// Notifications are always enabled and do not require permissions.
		//if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
		//		|| !( $this->getPermissionObject()->Check( 'user_preference', 'view' ) || $this->getPermissionObject()->Check( 'user_preference', 'view_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'view_child' ) ) ) {
		//	return $this->getPermissionObject()->PermissionDenied();
		//}

		//No need to check for permission_children as the logged in user can read and delete their own notification anyways.
		$ndtlf = TTnew( 'NotificationListFactory' ); /** @var NotificationListFactory $ndtlf */
		$unread_notifications = $ndtlf->getUnreadCountByUserIdAndCompanyId( $this->getCurrentUserObject()->getId(), $this->getCurrentUserObject()->getCompany() );

		return $this->returnHandler( $unread_notifications );
	}

	/**
	 * Set notification data for one or more notifications.
	 * @param array $data notification data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setNotification( $data, $validate_only = false, $ignore_warning = true ) {

		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getCurrentUserObject()->getStatus() != 10 ) {
			return $this->getPermissionObject()->PermissionDenied( false, TTi18n::getText( 'Employee is not active.' ) );
		}

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'edit_own' ) || $this->getPermissionObject()->Check( 'user', 'edit_child' ) || $this->getPermissionObject()->Check( 'user', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
			$permission_children_ids = false;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Notifications', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'NotificationListFactory' ); /** @var NotificationListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['user_id'] ) && $row['user_id'] != '' ) {
					unset( $row['id'] ); //ID could be '-1', so simply unset it so it doesn't try to update a non-existing record.
					//Adding new object, check ADD permissions.

					if (
							$validate_only == true
							||
							(
									$this->getPermissionObject()->Check( 'user', 'view' )
									|| ( $this->getPermissionObject()->Check( 'user', 'view_own' ) && $this->getPermissionObject()->isOwner( false, $row['user_id'] ) === true )
									|| ( $this->getPermissionObject()->Check( 'user', 'view_child' ) && $this->getPermissionObject()->isChild( $row['user_id'], $permission_children_ids ) === true )
							) ) {
					} else {
						$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Add permission denied' ) );
					}
					//$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'user', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->setObjectFromArray( $row );

					$lf->Validator->setValidateOnly( $validate_only );

					$is_valid = $lf->isValid( $ignore_warning );
					if ( $is_valid == true ) {
						Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
						if ( $validate_only == true ) {
							$save_result[$key] = true;
						} else {
							$save_result[$key] = $lf->Save();
						}
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ] );
				} else if ( $validate_only == true ) {
					$lf->FailTransaction();
				}

				$lf->CommitTransaction();
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Validate notification data for one or more notifications.
	 * @param array $data notification data
	 * @return array
	 */
	function validateNotification( $data ) {
		return $this->setNotification( $data, true );
	}

	/**
	 * Delete one or more notifications.
	 * @param array $data notification data
	 * @return array|bool
	 */
	function deleteNotification( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		// no reason to check for user permissions as notifications are always enabled and users can only interact with their own notifications.
		//if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
		//		|| !( $this->getPermissionObject()->Check( 'user_preference', 'delete' ) || $this->getPermissionObject()->Check( 'user_preference', 'delete_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'delete_child' ) ) ) {
		//	return $this->getPermissionObject()->PermissionDenied();
		//}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Notifications', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'NotificationListFactory' ); /** @var NotificationListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get notification object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndUserId( $id, $this->getCurrentUserObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						$lf = $lf->getCurrent();
						//Object exists don't need to check permissions as notifications are always enabled and users can only interact with their own.
						//if ( $this->getPermissionObject()->Check( 'user_preference', 'delete' )
						//		|| ( $this->getPermissionObject()->Check( 'user_preference', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
						//	Debug::Text( 'Record Exists, deleting record ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						//	$lf = $lf->getCurrent();
						//} else {
						//	$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Delete permission denied' ) );
						//}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
					}
				} else {
					$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
				}

				//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10 );
					$lf->setDeleted( true );

					$is_valid = $lf->isValid();
					if ( $is_valid == true ) {
						Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
						$save_result[$key] = $lf->Save();
						$validator_stats['valid_records']++;
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ] );
				}

				$lf->CommitTransaction();
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * @param array $mark_read_notification_ids UUID
	 * @param int $status_id
	 * @return array|bool
	 */
	function setNotificationStatus( $mark_read_notification_ids, $status_id ) {
		Debug::Arr( $mark_read_notification_ids, 'Marking notification as read: ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $status_id == 10 ) {
			// if marking messages unread, search for read messages
			$search_status_id = 20;
		} else {
			// if marking messages read, search for unread
			$search_status_id = 10;
		}

		$nlf = TTnew( 'NotificationListFactory' ); /** @var NotificationListFactory $nlf */
		// no reason to check for permissions as notifications are always enabled and users can only interact with their own.
		$nlf->getByCompanyIdAndUserIdAndIdAndStatus( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId(), $mark_read_notification_ids, $search_status_id );
		if ( $nlf->getRecordCount() > 0 ) {
			foreach ( $nlf as $n_obj ) { /** @var NotificationFactory $n_obj */
				$n_obj->setStatus( $status_id ); //Read
				if ( $status_id == 20 ) {
					// If user has read the notification, we mark it as a success even if retries are pending as they do not need to be notified again
					$n_obj->setSentStatus( 100 );
				}
				$n_obj->setEnableSendNotification( false );
				if ( $n_obj->isValid() ) {
					$n_obj->Save();
				}
			}
		}

		return $this->returnHandler( true );
	}

	/**
	 * Send a notification to a user.
	 * @param $data_array
	 * @param $created_before_timestamp
	 * @return array|bool
	 */
	function sendNotification( $data_array, $created_before_timestamp = 0 ) {
		if ( !isset( $data_array['user_id']) ) {
			return $this->returnHandler( false );
		}

		if ( !isset( $data_array['device_id'] ) ) {
			return $this->returnHandler( false );
		}

		$api_user = TTnew('APIUser');
		$result = $this->stripReturnHandler( $api_user->getUser( [ 'filter_data' => [ 'id' => $data_array['user_id'] ], 'filter_columns' => [ 'id' => true ] ] ) );
		if ( isset( $result[0] ) && count( $result[0] ) > 0 ) {
			$data_array['user_id'] = [];
			foreach( $result as $tmp_row ) {
				$data_array['user_id'] = $tmp_row['id'];
				break; //Only support a single user_id for now.
			}
			$retval = Notification::sendNotification( $data_array, $created_before_timestamp );

			return $this->returnHandler( $retval );
		} else {
			return $this->getPermissionObject()->PermissionDenied();
		}
	}

	/**
	 * Returns array of system generated notifications message to be displayed to the user.
	 * @param bool|string $action Action that is being performed, possible values: 'login', 'preference', 'notification', 'pay_period'
	 * @return array|bool
	 */
	function getSystemNotification( $action = false ) {
		if ( DEMO_MODE == true || PRODUCTION == false ) {
			return $this->returnHandler( 0 );
		}

		global $config_vars, $disable_database_connection;

		$system_triggered_notifications = 0;

		switch ( strtolower( $action ) ) {
			case 'login':
				//Skip this step if disable_database_connection is enabled or the user is going through the installer still
				if ( ( !isset( $disable_database_connection ) || ( isset( $disable_database_connection ) && $disable_database_connection != true ) )
						&& ( !isset( $config_vars['other']['installer_enabled'] ) || ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] != true ) ) ) {
					//Get all system settings, so they can be used even if the user isn't logged in, such as the login page.
					$sslf = new SystemSettingListFactory();
					$system_settings = $sslf->getAllArray();
				}
				unset( $sslf );

				//Check license validity (login check happens for users >= 40 permission level) MiscDaily cron checks for other conditions.
				if ( $this->getPermissionObject()->getLevel() >= 40 && ( isset( $config_vars['other']['primary_company_id'] ) && $this->getCurrentCompanyObject()->getId() == $config_vars['other']['primary_company_id'] ) && getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					if ( !isset( $system_settings['license'] ) ) {
						$system_settings['license'] = null;
					}

					$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $license = @new $obj_class;
					$license_validate = $license->validateLicense( $system_settings['license'], null );
					$license_message = $license->getFullErrorMessage( $license_validate, true );
					if ( $license_message != '' ) {
						if ( $license_validate === true ) {
							if ( $license->getExpireDays() <= 2 ) { // Send to all emplyees if expiring within 2 days. DailyMisc crom handles all other cases
								$notification_subject = TTi18n::getText( 'WARNING: %1 license expiring soon.', APPLICATION_NAME );
							}
						} else {
							$notification_subject = TTi18n::getText( 'WARNING: %1 license error.', APPLICATION_NAME );
						}

						if ( isset( $notification_subject ) ) {
							$notification_data = [
									'object_id'      => TTUUID::getNotExistID( 1000 ),
									'user_id'        => $this->getCurrentUserObject()->getId(),
									'priority_id'    => 2, //High
									'type_id'        => 'system',
									'object_type_id' => 0,
									'title_short'    => $notification_subject,
									'body_short'     => $license_message,
							];

							$created_before_timestamp = ( TTDate::getTime() - ( 2 * 86400 ) ); // Check if users already received notification in last 2 days
							Notification::sendNotification( $notification_data, $created_before_timestamp );
							$system_triggered_notifications++;
						}

						unset( $notification_data, $created_before_timestamp );
					}
					unset( $license, $license_validate, $license_message );
				}

				// Check hostname specified in .ini file matches the hostname used to login to TimeTrex.
				if ( isset( $_SERVER['HTTP_HOST'] ) && $_SERVER['HTTP_HOST'] != '' && isset( $config_vars['other']['hostname'] ) && $config_vars['other']['hostname'] != '' ) {
					if ( stripos( $_SERVER['HTTP_HOST'], $config_vars['other']['hostname'] ) === false ) {
						$notification_data = [
								'object_id'      => TTUUID::getNotExistID( 1080 ),
								'user_id'        => $this->getCurrentUserObject()->getId(),
								'priority_id'	 => 2, //High
								'type_id'        => 'system',
								'object_type_id' => 0,
								'title_short'    => TTi18n::getText( 'WARNING: Hostname setting misconfigured.' ),
								'body_short'     => TTi18n::getText( 'Hostname specified in %1 config file does not match the accessed URL. Please go to your timetrex.ini.php file and set "hostname" to "'. $_SERVER['HTTP_HOST'] .'".', APPLICATION_NAME ),
						];

						$created_before_timestamp = ( TTDate::getTime() - ( 7 * 86400 ) ); // Check if users already received notification in last 7 days
						Notification::sendNotification( $notification_data, $created_before_timestamp );
						$system_triggered_notifications++;

						unset( $notification_data, $created_before_timestamp );
					}
				}

				//Make sure CronJobs are running correctly.
				$cjlf = new CronJobListFactory();
				$cjlf->getMostRecentlyRun();
				if ( $cjlf->getRecordCount() > 0 ) {
					//Is last run job more then 48hrs old?
					$cj_obj = $cjlf->getCurrent();

					if ( PRODUCTION == true
							&& DEMO_MODE == false
							&& $cj_obj->getLastRunDate() < ( time() - 172800 )
							&& $cj_obj->getCreatedDate() < ( time() - 172800 ) ) {

						$notification_data = [
								'object_id'      => TTUUID::getNotExistID( 1020 ),
								'user_id'        => $this->getCurrentUserObject()->getId(),
								'priority_id'	 => 2, //High
								'type_id'        => 'system',
								'object_type_id' => 0,
								'title_short'    => TTi18n::getText( 'WARNING: Maintenance jobs have not run.' ),
								'body_short'     => TTi18n::getText( 'Critical maintenance jobs have not run in the last 48hours. Please contact your %1 administrator immediately.', APPLICATION_NAME ),
						];

						$created_before_timestamp = ( TTDate::getTime() - 86400 ); // Check if users already received notification in last 1 day
						Notification::sendNotification( $notification_data, $created_before_timestamp );
						$system_triggered_notifications++;

						unset( $notification_data, $created_before_timestamp );
					}
				}
				unset( $cjlf, $cj_obj );

				if ( PRODUCTION == true
						&& DEMO_MODE == false ) {
					//Make sure SystemJobQueues are being processed in a timely manner.
					$sjqlf = TTNew( 'SystemJobQueueListFactory' );
					$sjqlf->getOldestPendingJob( ( 3600 * 3 ) ); //Is oldest pending job more than 3hrs old?
					if ( $sjqlf->getRecordCount() > 0 ) {
						$sjq_obj = $sjqlf->getCurrent();
						Debug::Text( 'Sending notification of old SystemJobQueue record: ' . $sjq_obj->getId() . ' Effective Date: ' . $sjq_obj->getEffectiveDate(), __FILE__, __LINE__, __METHOD__, 10 );

						$notification_data = [
								'object_id'      => TTUUID::getNotExistID( 1020 ),
								'user_id'        => $this->getCurrentUserObject()->getId(),
								'priority_id'    => 2, //High
								'type_id'        => 'system',
								'object_type_id' => 0,
								'title_short'    => TTi18n::getText( 'WARNING: Maintenance jobs have not run (queue).' ),
								'body_short'     => TTi18n::getText( 'Critical maintenance jobs have not run in the last 48hours. Please contact your %1 administrator immediately. (queue)', APPLICATION_NAME ),
						];

						$created_before_timestamp = ( TTDate::getTime() - 86400 ); // Check if users already received notification in last 1 day
						Notification::sendNotification( $notification_data, $created_before_timestamp );
						$system_triggered_notifications++;

						unset( $notification_data, $created_before_timestamp );
					}

					unset( $sjqlf, $sjq_obj );
				}

				break;
			default:
				break;
		}

		return $this->returnHandler( $system_triggered_notifications );
	}

}

?>
