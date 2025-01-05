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
 * @package API\Schedule
 */
class APIRecurringScheduleControl extends APIFactory {
	protected $main_class = 'RecurringScheduleControlFactory';

	/**
	 * APIRecurringScheduleControl constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default recurring_schedule_control data for creating new recurring_schedule_controles.
	 * @return array
	 */
	function getRecurringScheduleControlDefaultData() {
		$company_obj = $this->getCurrentCompanyObject();

		//Default the display weeks to the closest value (min/max) to the average display weeks of any recurring schedule. That way its somewhat configurable.
		//We could also default it to the maximum amount of time between now and the further committed schedule, or some average thereof.
		$rsclf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
		$default_display_weeks = $rsclf->getMostCommonDisplayWeeksByCompanyId( $company_obj->getId() );
		Debug::Text( 'Most Common Display Weeks: ' . $default_display_weeks, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $default_display_weeks < 4 ) {
			$default_display_weeks = 4;
		}

		$data = [
				'company_id'    => $company_obj->getId(),
				'start_week'    => 1,
				'start_date'    => TTDate::getAPIDate( 'DATE', TTDate::getBeginWeekEpoch( TTDate::getTime() ) ),
				'end_date'      => null,
				'display_weeks' => $default_display_weeks,
				'user_id'       => TTUUID::getNotExistID(), //None
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get recurring_schedule_control data for one or more recurring_schedule_controles.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getRecurringScheduleControl( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'recurring_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'recurring_schedule', 'view' ) || $this->getPermissionObject()->Check( 'recurring_schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'recurring_schedule', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'recurring_schedule', 'view' );

		//If we don't have permissions to view open shifts, exclude user_id = 0;
		//FIXME: Make separate permissions for viewing OPEN recurring schedules?
		if ( $this->getPermissionObject()->Check( 'schedule', 'view_open' ) == false ) {
			$data['filter_data']['exclude_id'] = [ TTUUID::getZeroID() ];
		} else if ( is_array( $data['filter_data']['permission_children_ids'] ) && count( $data['filter_data']['permission_children_ids'] ) > 0 ) {
			//If schedule, view_open is allowed but they are also only allowed to see their subordinates (which they have some of), add "open" employee as if they are a subordinate.
			$data['filter_data']['permission_children_ids'][] = TTUUID::getZeroID();
		}

		$blf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			$retarr = [];
			foreach ( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Export data to csv
	 * @param string $format file format (csv)
	 * @param array $data    filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function exportRecurringScheduleControl( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getRecurringScheduleControl( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_recurring_schedule', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonRecurringScheduleControlData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getRecurringScheduleControl( $data, true ) ) );
	}

	/**
	 * Validate recurring_schedule_control data for one or more recurring_schedule_controles.
	 * @param array $data recurring_schedule_control data
	 * @return array
	 */
	function validateRecurringScheduleControl( $data ) {
		return $this->setRecurringScheduleControl( $data, true );
	}

	/**
	 * Set recurring_schedule_control data for one or more recurring_schedule_controles.
	 * @param array $data recurring_schedule_control data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setRecurringScheduleControl( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'recurring_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'recurring_schedule', 'edit' ) || $this->getPermissionObject()->Check( 'recurring_schedule', 'edit_own' ) || $this->getPermissionObject()->Check( 'recurring_schedule', 'edit_child' ) || $this->getPermissionObject()->Check( 'recurring_schedule', 'add' ) ) ) {
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
		Debug::Text( 'Received data for: ' . $total_records . ' RecurringScheduleControls', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get recurring_schedule_control object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'recurring_schedule', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'recurring_schedule', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
										//When checking isChild() - $lf->getCurrent()->getUser() against $permission_children_ids, that checks who *used* to be assigned to the record, not who is currently assigned in $row['user'].
										// But in cases where a Subordinate Only permissions group user has a subordinate one day with a recurring schedule assigned to them as part of a larger batch, then that person gets swithced into a new hierarchy,
										// if the recurring schedule is edited, that subordinate will get removed and unscheduled most likely unless we check the permissions based on the previous user_ids instead of the new $row['user'] user_ids.
										|| ( $this->getPermissionObject()->Check( 'recurring_schedule', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
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
					//Adding new object, check ADD permissions.
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'recurring_schedule', 'add' ), TTi18n::gettext( 'Add permission denied' ) );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

					//Force Company ID to current company.
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();

					$lf->setObjectFromArray( $row );
					$lf->Validator->setValidateOnly( $validate_only );

					$is_valid = $lf->isValid( $ignore_warning );
					if ( $is_valid == true ) {
						Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
						if ( $validate_only == true ) {
							$save_result[$key] = true;
						} else {
							$save_result[$key] = $lf->Save( true, true );
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

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result, false, true ); //Enable System Job Queue status update.
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more recurring_schedule_controls.
	 * @param array $data recurring_schedule_control data
	 * @return array|bool
	 */
	function deleteRecurringScheduleControl( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'recurring_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'recurring_schedule', 'delete' ) || $this->getPermissionObject()->Check( 'recurring_schedule', 'delete_own' ) || $this->getPermissionObject()->Check( 'recurring_schedule', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text( 'Received data for: ' . count( $data ) . ' RecurringScheduleControls', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			$i = 0;
			foreach ( $data as $key => $tmp_id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $lf */
				$lf->StartTransaction();

				//Need to support deleting the entire recurring schedule, or just one user from it.
				if ( is_array( $tmp_id ) ) {
					$id = $key;
					$user_id = $tmp_id;
					Debug::Arr( $tmp_id, 'ID is an array, with User ID specified as well, deleting just this one user: ID: ' . $id . ' User IDs: ', __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					$id = $tmp_id;
					$user_id = false;
				}

				if ( $id != '' ) {
					//Modifying existing object.
					//Get recurring_schedule_control object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						if ( $this->getPermissionObject()->Check( 'recurring_schedule', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'recurring_schedule', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
								|| ( $this->getPermissionObject()->Check( 'recurring_schedule', 'delete_child' ) && $this->getPermissionObject()->isChild( $user_id, $permission_children_ids ) === true ) ) {
							//if ( $this->getPermissionObject()->Check('recurring_schedule', 'delete')
							//		OR ( $this->getPermissionObject()->Check('recurring_schedule', 'delete_own') AND $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === TRUE ) ) {
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
					if ( $user_id != '' ) {
						//Remove this user_id from the user array.
						$new_user_ids = array_diff( (array)$lf->getUser(), (array)$user_id );
						Debug::Arr( $new_user_ids, 'Removing individual users from schedule, remaining users are: ', __FILE__, __LINE__, __METHOD__, 10 );
						if ( count( $new_user_ids ) > 0 ) {
							$lf->setUser( $new_user_ids );
						} else {
							//No users left, delete the entire recurring schedule.
							Debug::Text( 'No users left in schedule, removing entire schedule...', __FILE__, __LINE__, __METHOD__, 10 );
							$lf->setDeleted( true );
						}
						unset( $new_user_ids );
					} else {
						$lf->setDeleted( true );
					}

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

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $i );

				$i++;
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result, false, true ); //Enable System Job Queue status update.
		}

		return $this->returnHandler( false );
	}

	/**
	 * Copy one or more recurring_schedule_controles.
	 * @param array $data recurring_schedule_control IDs
	 * @return array
	 */
	function copyRecurringScheduleControl( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' RecurringScheduleControls', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getRecurringScheduleControl( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) > 0 ) {
			Debug::Arr( $src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $src_rows as $key => $row ) {
				unset( $src_rows[$key]['id'] ); //Clear fields that can't be copied
			}
			unset( $row ); //code standards
			//Debug::Arr($src_rows, 'bSRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->setRecurringScheduleControl( $src_rows ); //Save copied rows
		}

		return $this->returnHandler( false );
	}
}

?>
