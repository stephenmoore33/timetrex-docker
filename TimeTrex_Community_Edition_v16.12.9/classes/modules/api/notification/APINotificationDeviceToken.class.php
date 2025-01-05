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
class APINotificationDeviceToken extends APIFactory {
	protected $main_class = 'NotificationDeviceTokenFactory';

	/**
	 * APINotificationDeviceToken constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get device tokens data for one or more device tokenss.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getNotificationDeviceToken( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user_preference', 'view' ) || $this->getPermissionObject()->Check( 'user_preference', 'view_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user_preference', 'view' );

		$ndtlf = TTnew( 'NotificationDeviceTokenListFactory' ); /** @var NotificationDeviceTokenListFactory $ndtlf */
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
	 * Validate device tokens data for one or more device tokenss.
	 * @param array $data device tokens data
	 * @return array
	 */
	function validateNotificationDeviceToken( $data ) {
		return $this->setNotificationDeviceToken( $data, true );
	}

	/**
	 * Set device tokens data for one or more device tokenss.
	 * @param array $data device tokens data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setNotificationDeviceToken( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user_preference', 'edit' ) || $this->getPermissionObject()->Check( 'user_preference', 'edit_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'edit_child' ) || $this->getPermissionObject()->Check( 'user_preference', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' NotificationDeviceTokens', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'NotificationDeviceTokenListFactory' ); /** @var NotificationDeviceTokenListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					$lf->getByIDAndUserID( $row['id'], $this->getCurrentUserObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'user_preference', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'user_preference', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'user_preference', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid( $ignore_warning );
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->setObjectFromArray( $row );
					//Force current User ID.
					$lf->setUser( $this->getCurrentUserObject()->getId() );

					if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
						require_once( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
						$browser = new Browser( $_SERVER['HTTP_USER_AGENT'] );
						$user_agent = $browser->getBrowser();
						if ( $user_agent === 'unknown' ) {
							$lf->setUserAgent( $_SERVER['HTTP_USER_AGENT'] );
						} else {
							$lf->setUserAgent( $browser->getBrowser() );
						}
					}

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
	 * Checks if a device token is registered and sets it if it is not already registered.
	 *   This function is also called from APIClientStationUnAuthenticated->checkAndSetNotificationDeviceToken
	 * @param $device_token
	 * @param $platform_id
	 * @return array|bool
	 */
	function checkAndSetNotificationDeviceToken( $device_token, $platform_id ) {
		if ( $device_token == '' ) {
			return $this->returnHandler( false );
		}

		if ( $platform_id == '' ) {
			return $this->returnHandler( false );
		}

		if ( !is_object( $this->getCurrentUserObject() ) ) {
			Debug::Text( '  User is not logged in...', __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( false );
		}

		$ndtlf = TTnew( 'NotificationDeviceTokenListFactory' ); /** @var NotificationDeviceTokenListFactory $ndtlf */
		$ndtlf->getByUserIdAndDeviceToken( $this->getCurrentUserObject()->getId(), $device_token );
		if ( $ndtlf->getRecordCount() == 0 ) {
			$ndtf = TTnew( 'NotificationDeviceTokenFactory' ); /** @var NotificationDeviceTokenListFactory $ndtlf */
			$ndtf->setUser( $this->getCurrentUserObject()->getId() );
			$ndtf->setPlatform( $platform_id );
			$ndtf->setDeviceToken( $device_token );
			if ( isset( $_SERVER['HTTP_USER_AGENT'] ) ) {
				require_once( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'cbschuld' . DIRECTORY_SEPARATOR . 'browser.php' . DIRECTORY_SEPARATOR . 'src' . DIRECTORY_SEPARATOR . 'Browser.php' );
				$browser = new Browser( $_SERVER['HTTP_USER_AGENT'] );
				$user_agent = $browser->getBrowser();
				if ( $user_agent === 'unknown' ) {
					$ndtf->setUserAgent( $_SERVER['HTTP_USER_AGENT'] );
				} else {
					$ndtf->setUserAgent( $browser->getBrowser() );
				}
			}
			if ( $ndtf->isValid() ) {
				Debug::Text( '  Device token does not exist, saving... Token: '. $device_token .' Platform ID: '. $platform_id, __FILE__, __LINE__, __METHOD__, 10 );

				//Prevent users from registering more than 10 tokens per platform. This helps avoid users who have turned on Clear Cookies on Exit in their web browser and register a new token every day.
				$ndtlf->purgeExcessDeviceTokensForUserAndPlatform( $this->getCurrentUserObject()->getId(), $platform_id );

				return $this->returnHandler( $ndtf->Save() );
			} else {
				Debug::Text( '  Device token does not exist, FAILED saving... Token: '. $device_token .' Platform ID: '. $platform_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			return $this->returnHandler( false );
		} else {
			return $this->returnHandler( true );
		}
	}

	/**
	 * Delete one or more device tokens.
	 * @param array $data device tokens data
	 * @return array|bool
	 */
	function deleteNotificationDeviceToken( $data ) {
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

		Debug::Text( 'Received data for: ' . count( $data ) . ' NotificationDeviceTokens', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'NotificationDeviceTokenListFactory' ); /** @var NotificationDeviceTokenListFactory $lf */
				$lf->StartTransaction();
				if ( $row['device_token'] != '' ) {
					//Modifying existing object.
					//Get device tokens object, so we can only modify just changed data for specific records if needed.
					$lf->getByUserIdAndDeviceToken( $this->getCurrentUserObject()->getId(), $row['device_token'] );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'user_preference', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'user_preference', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true ) ) {
							Debug::Text( 'Record Exists, deleting record ID: ' . $row['device_token'], __FILE__, __LINE__, __METHOD__, 10 );
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

	/**
	 * Delete all device tokens for the current user.
	 * @return array|bool
	 */
	function deleteAllNotificationDeviceTokens() {
		if ( !$this->getPermissionObject()->Check( 'user_preference', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'user_preference', 'delete' ) || $this->getPermissionObject()->Check( 'user_preference', 'delete_own' ) || $this->getPermissionObject()->Check( 'user_preference', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$ndtlf = TTnew( 'NotificationDeviceTokenListFactory' ); /** @var NotificationDeviceTokenListFactory $lf */
		$ndtlf->getByCompanyIdAndUserID( $this->getCurrentCompanyObject()->getId(), $this->getCurrentUserObject()->getId() );
		if ( $ndtlf->getRecordCount() > 0 ) {
			foreach ( $ndtlf as $ndt_obj ) { /** @var NotificationDeviceTokenFactoryFactory $ndt_obj */
				Debug::Text( 'Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10 );
				$ndt_obj->setDeleted( true );
				if ( $ndt_obj->isValid() ) {
					Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
					$ndt_obj->Save();
				}
			}

			return $this->returnHandler( true );
		}

		return $this->returnHandler( false );
	}
}

?>
