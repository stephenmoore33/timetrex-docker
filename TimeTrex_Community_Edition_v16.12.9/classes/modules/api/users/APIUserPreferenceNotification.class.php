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
 * @package API\Users
 */
class APIUserPreferenceNotification extends APIFactory {
	protected $main_class = 'UserPreferenceNotificationFactory';

	/**
	 * APIUserPreferenceNotification constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default preference notification data for creating new preference notifications.
	 * Filters the return data by users permissions so only relevant data is returned.
	 * @return array|bool
	 */
	function getUserPreferenceNotificationDefaultData( $data ) {
		Debug::Text( 'Getting user preference notification default data...', __FILE__, __LINE__, __METHOD__, 10 );

		if ( isset( $data['filter_data']['user_id'] ) && $data['filter_data']['user_id'] !== $this->current_user->getId() ) {
			if ( $this->getPermissionObject()->Check( 'user_preference', 'enabled' )
					&& 	(
							$this->getPermissionObject()->Check( 'user_preference', 'view' ) === true
							|| ( $this->getPermissionObject()->Check( 'user_preference', 'view_own' ) && $this->getPermissionObject()->isOwner( false, $data['filter_data']['user_id'] ) === true )
							|| ( $this->getPermissionObject()->Check( 'user_preference', 'view_child' ) && $this->getPermissionObject()->isChild( $data['filter_data']['user_id'], $this->getPermissionChildren() ) === true )
						)
				) {

				// if getting preference for a different user, we need to load their user and preference data
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getById( $data['filter_data']['user_id'] );
				if ( $ulf->getRecordCount() == 1 ) {
					$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
				}
			} else {
				return $this->getPermissionObject()->PermissionDenied();
			}
		} else {
			$u_obj = $this->current_user;
		}

		$upnf = TTnew( 'UserPreferenceNotificationFactory' ); /** @var UserPreferenceNotificationFactory $upnf */
		$notification_preference_data_array = $upnf->getUserPreferenceNotificationTypeDefaultValues( null );

		$retarr = UserPreferenceNotificationFactory::filterUserNotificationPreferencesByPermissions( $notification_preference_data_array, $u_obj );

		// if user uses these defaults to set notification preferences, the user ID is required
		foreach ( $retarr as $key => $data ) {
			$retarr[$key]['user_id'] = $u_obj->getId();
		}

		return $this->returnHandler( $retarr );
	}

	/**
	 * Get preference notification data for one or more preference notifications.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getUserPreferenceNotification( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user_preference', 'view' ) || $this->getPermissionObject()->Check( 'user_preference', 'view_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user_preference', 'view' );

		$upnlf = TTnew( 'UserPreferenceNotificationListFactory' ); /** @var UserPreferenceNotificationListFactory $upnlf */
		$upnlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $upnlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $upnlf->getRecordCount() > 0 ) {
			$this->setPagerObject( $upnlf );

			if ( isset( $data['filter_data']['user_id'] ) && $data['filter_data']['user_id'] !== $this->current_user->getId() ) {
				// if getting preference for a different user, we need to load their user and preference data
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getById( $data['filter_data']['user_id'] );
				if ( $ulf->getRecordCount() == 1 ) {
					$u_obj = $ulf->getCurrent(); /** @var UserFactory $u_obj */
				}
			} else {
				$u_obj = $this->current_user;
			}

			$notification_preference_data_array = [];
			foreach ( $upnlf as $upn_obj ) { /** @var UserPreferenceNotificationFactory $upn_obj */
				$notification_preference_data_array[] = $upn_obj->getObjectAsArray();
			}

			//If user does not have a record for the preference notification type, add it so the user can see it with default values set.
			$all_notification_preferences = $this->stripReturnHandler( $this->getUserPreferenceNotificationDefaultData( $data ) );
			if ( is_array( $all_notification_preferences ) ) {
				foreach ( $all_notification_preferences as $preference ) {
					if ( !in_array( $preference['type_id'], array_column( $notification_preference_data_array, 'type_id' ) ) ) {
						$notification_preference_data_array[] = $preference;
					}
				}

				$retarr = UserPreferenceNotificationFactory::filterUserNotificationPreferencesByPermissions( $notification_preference_data_array, $u_obj );

				return $this->returnHandler( $retarr );
			}
		}

		// no notification preferences found for user, return default values so that notification preferences are not empty in my account preferences
		return $this->getUserPreferenceNotificationDefaultData( $data );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonUserPreferenceNotificationData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getUserPreferenceNotification( $data, true ) ) );
	}

	/**
	 * Validate preference notification data for one or more preference notifications.
	 * @param array $data preference notification data
	 * @return array
	 */
	function validateUserPreferenceNotification( $data ) {
		return $this->setUserPreferenceNotification( $data, true );
	}

	/**
	 * Set preference notification data for one or more preference notifications.
	 * @param array $data preference notification data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setUserPreferenceNotification( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user_preference', 'edit' ) || $this->getPermissionObject()->Check( 'user_preference', 'edit_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'edit_child' ) || $this->getPermissionObject()->Check( 'user_preference', 'edit_own' ) ) ) {
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
		Debug::Text( 'Received data for: ' . $total_records . ' UserPreferenceNotifications', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserPreferenceNotificationListFactory' ); /** @var UserPreferenceNotificationListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' && $row['id'] != -1 && $row['id'] != TTUUID::getNotExistID() ) {
					//Modifying existing object.
					//Get preference notification object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'user_preference', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'user_preference', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
										|| ( $this->getPermissionObject()->Check( 'user_preference', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
								) ) {

							Debug::Text( 'Row Exists, getting current data for ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );
							$lf = $lf->getCurrent();
							$row = array_merge( $lf->getObjectAsArray(), $row );
						} else {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Edit permission denied' ) );
						}
					} else {
						//Object doesn't exist.
						$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Edit permission denied, record does not exist' ) );
					}
				} else {
					unset( $row['id'] ); //ID could be '-1', so simply unset it so it doesn't try to update a non-existing record.
					//Always allow the currently logged in user to create preferences in case the record isn't there.
					if ( !( isset( $row['user_id'] ) && $row['user_id'] == $this->getCurrentUserObject()->getId() ) ) {
						//Adding new object, check ADD permissions.
						$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'user', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
					}
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
	 * Delete one or more preference notifications.
	 * @param array $data preference notification data
	 * @return array|bool
	 */
	function deleteUserPreferenceNotification( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user_preference', 'delete' ) || $this->getPermissionObject()->Check( 'user_preference', 'delete_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' UserPreferenceNotifications', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserPreferenceNotificationListFactory' ); /** @var UserPreferenceNotificationListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get preference notification object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'user_preference', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'user_preference', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
							Debug::Text( 'Record Exists, deleting record ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
							$lf = $lf->getCurrent();
						} else {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Delete permission denied' ) );
						}
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
}

?>
