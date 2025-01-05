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
class APIUserDefaultPreferenceNotification extends APIFactory {
	protected $main_class = 'UserDefaultPreferenceNotificationFactory';

	/**
	 * APIUserDefaultPreferenceNotification constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default UserDefaultPreferenceNotification data for creating new UserDefaultPreferenceNotificationes.
	 * @return array
	 */
	function getUserDefaultPreferenceNotificationDefaultData() {
		Debug::Text( 'Getting DefaultPreferenceNotification default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$upnf = TTnew( 'UserPreferenceNotificationFactory' ); /** @var UserPreferenceNotificationFactory $upnf */
		$data = $upnf->getUserPreferenceNotificationTypeDefaultValues( [ 'system' ] );

		return $this->returnHandler( $data );
	}

	/**
	 * Get UserDefaultPreferenceNotification data for one or more UserDefaultPreferenceNotificationes.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getUserDefaultPreferenceNotification( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( isset( $data['filter_data']['user_default_id'] ) && TTUUID::isUUID( $data['filter_data']['user_default_id'] ) ) {
			$udpnlf = TTnew( 'UserDefaultPreferenceNotificationListFactory' ); /** @var UserDefaultPreferenceNotificationListFactory $udpnlf */
			$udpnlf->getByUserDefaultId( $data['filter_data']['user_default_id'] );
			Debug::Text( 'Record Count: ' . $udpnlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $udpnlf->getRecordCount() > 0 ) {
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), $udpnlf->getRecordCount() );

				$this->setPagerObject( $udpnlf );

				$retarr = [];
				foreach ( $udpnlf as $udpn ) {
					$retarr[] = $udpn->getObjectAsArray( $data['filter_columns'] );

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $udpnlf->getCurrentRow() );
				}

				//If there is no record for the preference notification type, add with default values set.
				$all_notification_preferences = $this->stripReturnHandler( $this->getUserDefaultPreferenceNotificationDefaultData() );
				foreach ( $all_notification_preferences as $preference ) {
					if ( !in_array( $preference['type_id'], array_column( $retarr, 'type_id' ) ) ) {
						$retarr[] = $preference;
					};
				}

				$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

				return $this->returnHandler( $retarr );
			}
		}

		// No user default notification preferences found, returning default values.
		return $this->getUserDefaultPreferenceNotificationDefaultData();
	}

	/**
	 * Validate UserDefaultPreferenceNotification data for one or more UserDefaultPreferenceNotificationes.
	 * @param array $data UserDefaultPreferenceNotification data
	 * @return array
	 */
	function validateUserDefaultPreferenceNotification( $data ) {
		return $this->setUserDefaultPreferenceNotification( $data, true );
	}

	/**
	 * Set UserDefaultPreferenceNotification data for one or more UserDefaultPreferenceNotificationes.
	 * @param array $data UserDefaultPreferenceNotification data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setUserDefaultPreferenceNotification( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'user', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user', 'edit' ) || $this->getPermissionObject()->Check( 'user', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' UserDefaultPreferenceNotifications', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'UserDefaultPreferenceNotificationListFactory' ); /** @var UserDefaultPreferenceNotificationListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' && $row['id'] != -1 && $row['id'] != TTUUID::getNotExistID() ) {
					//Modifying existing object.
					//Get UserDefaultPreferenceNotification object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
								$this->getPermissionObject()->Check( 'user', 'edit' )
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
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'user', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->setObjectFromArray( $row );

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

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

}

?>
