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
 * @package API\PayPeriod
 */
class APIPayPeriodSchedule extends APIFactory {
	protected $main_class = 'PayPeriodScheduleFactory';

	/**
	 * APIPayPeriodSchedule constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get options for dropdown boxes.
	 * @param bool|string $name Name of options to return, ie: 'columns', 'type', 'status'
	 * @param mixed $parent     Parent name/ID of options to return if data is in hierarchical format. (ie: Province)
	 * @return bool|array
	 */
	function getOptions( $name = false, $parent = null ) {
		if ( $name == 'columns'
				&& ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
						|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'view' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_child' ) ) ) ) {
			$name = 'list_columns';
		}

		return parent::getOptions( $name, $parent );
	}

	/**
	 * Get default data for creating pay period schedules.
	 * @return array
	 */
	function getPayPeriodScheduleDefaultData() {
		Debug::Text( 'Getting user default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$data = [
				'company_id'                               => $this->getCurrentCompanyObject()->getId(),
				'anchor_date'                              => TTDate::getAPIDate( 'DATE', TTDate::getBeginMonthEpoch( time() ) ),
				'shift_assigned_day_id'                    => 10,
				'day_start_time'                           => 0,
				'new_day_trigger_time'                     => ( 3600 * 4 ),
				'maximum_shift_time'                       => ( 3600 * 16 ),
				'time_zone'                                => $this->getCurrentUserPreferenceObject()->getTimeZone(),
				'type_id'                                  => 20,
				'start_week_day_id'                        => 0,
				'start_day_of_week'                        => 0,
				'timesheet_verify_type_id'                 => 10, //Disabled
				'timesheet_verify_before_end_date'         => 0,
				'timesheet_verify_before_transaction_date' => 0,
				'create_days_in_advance'                   => 16,
				'auto_close_after_days'                    => 3,
		];

		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( $data );
	}

	/**
	 * Get user data for one or more users.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function getPayPeriodSchedule( $data = null, $disable_paging = false ) {

		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'view' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'view_child' ) ) ) {
			//return $this->getPermissionObject()->PermissionDenied();
			$data['filter_columns'] = $this->handlePermissionFilterColumns( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null, Misc::trimSortPrefix( $this->getOptions( 'list_columns' ) ) );
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_period_schedule', 'view' );

		//Allow getting users from other companies, so we can change admin contacts when using the master company.
		if ( isset( $data['filter_data']['company_id'] )
				&& TTUUID::isUUID( $data['filter_data']['company_id'] ) && $data['filter_data']['company_id'] != TTUUID::getZeroID() && $data['filter_data']['company_id'] != TTUUID::getNotExistID()
				&& ( $this->getPermissionObject()->Check( 'company', 'enabled' ) && $this->getPermissionObject()->Check( 'company', 'view' ) ) ) {
			$company_id = $data['filter_data']['company_id'];
		} else {
			$company_id = $this->getCurrentCompanyObject()->getId();
		}

		$ulf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $ulf->getRecordCount() > 0 ) {
			$this->setPagerObject( $ulf );

			$retarr = [];
			foreach ( $ulf as $u_obj ) {
				$retarr[] = $u_obj->getObjectAsArray( $data['filter_columns'] );
			}

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
	function exportPayPeriodSchedule( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getPayPeriodSchedule( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_pay_period_schedule', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPayPeriodScheduleData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPayPeriodSchedule( $data, true ) ) );
	}

	/**
	 * Validate user data for one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function validatePayPeriodSchedule( $data ) {
		return $this->setPayPeriodSchedule( $data, true );
	}

	/**
	 * Set user data for one or more users.
	 * @param array $data user data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setPayPeriodSchedule( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_child' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'add' ) ) ) {
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
		Debug::Text( 'Received data for: ' . $total_records . ' PayPeriodSchedules', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $row ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $lf */
				$lf->StartTransaction();
				if ( isset( $row['id'] ) && $row['id'] != '' ) {
					//Modifying existing object.
					//Get user object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						//Debug::Text('PayPeriodSchedule ID: '. $row['id'] .' Created By: '. $lf->getCurrent()->getCreatedBy() .' Is Owner: '. (int)$this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) .' Is Child: '. (int)$this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ), __FILE__, __LINE__, __METHOD__, 10);
						if (
								$validate_only == true
								||
								(
										$this->getPermissionObject()->Check( 'pay_period_schedule', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
										|| ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === true )
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
					$primary_validator->isTrue( 'permission', $this->getPermissionObject()->Check( 'pay_period_schedule', 'add' ), TTi18n::gettext( 'Add permission denied' ) );

					//Because this class has sub-classes that depend on it, when adding a new record we need to make sure the ID is set first,
					//so the sub-classes can depend on it. We also need to call Save( TRUE, TRUE ) to force a lookup on isNew()
					$row['id'] = $lf->getNextInsertId();

					//Make sure initial pay periods are created when adding a new pay period schedule.
					$lf->setEnableInitialPayPeriods( true );
					$lf->setCreateInitialPayPeriods( true );
				}
				Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				$is_valid = $primary_validator->isValid();
				if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
					Debug::Text( 'Attempting to save data...', __FILE__, __LINE__, __METHOD__, 10 );

					//Force Company ID to current company.
					$row['company_id'] = $this->getCurrentCompanyObject()->getId();

					$lf->setObjectFromArray( $row );

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
			}

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more users.
	 * @param array $data user data
	 * @return array|bool
	 */
	function deletePayPeriodSchedule( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'pay_period_schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete_own' ) || $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text( 'Received data for: ' . count( $data ) . ' PayPeriodSchedules', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			foreach ( $data as $key => $id ) {
				$primary_validator = new Validator();
				$lf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $lf */
				$lf->StartTransaction();
				if ( $id != '' ) {
					//Modifying existing object.
					//Get user object, so we can only modify just changed data for specific records if needed.
					$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
					if ( $lf->getRecordCount() == 1 ) {
						//Object exists, check edit permissions
						//Debug::Text('PayPeriodSchedule ID: '. $user['id'] .' Created By: '. $lf->getCurrent()->getCreatedBy() .' Is Owner: '. (int)$this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) .' Is Child: '. (int)$this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ), __FILE__, __LINE__, __METHOD__, 10);
						if ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete' )
								|| ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getID() ) === true )
								|| ( $this->getPermissionObject()->Check( 'pay_period_schedule', 'delete_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getId(), $permission_children_ids ) === true ) ) {

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

	/**
	 * Copy one or more users.
	 * @param array $data user data
	 * @return array
	 */
	function copyPayPeriodSchedule( $data ) {
		//Can only Copy as New, not just a regular copy, as too much data needs to be changed,
		//such as username, password, employee_number, SIN, first/last name address...
		return $this->returnHandler( false );
	}

	/**
	 * @param int $type_id
	 * @param int $example_dates EPOCH
	 * @return array|bool
	 */
	function detectPayPeriodScheduleSettings( $type_id, $example_dates ) {
		$ppsf = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $ppsf */

		//Start with default data...
		$ppsf->setObjectFromArray( $this->stripReturnHandler( $this->getPayPeriodScheduleDefaultData() ) );

		$ppsf->setCompany( $this->getCurrentCompanyObject()->getId() );

		if ( $ppsf->detectPayPeriodScheduleSettings( $type_id, $example_dates ) == true ) {
			$retarr = $ppsf->getObjectAsArray();

			//Set name here so it doesn't go through the validation check prior to Flex submitting it back to the API. This avoid the name field being sent through as "false".
			$retarr['name'] = TTi18n::getText( 'Default' ) . ' [' . rand( 10, 99 ) . ']';

			Debug::Arr( $retarr, 'Detected settings: ', __FILE__, __LINE__, __METHOD__, 10 );

			return $retarr;
		}

		return $this->returnHandler( false ); //return true so Flex doesn't display an error message.
	}

	/**
	 * @param int $type_id
	 * @param int $start_date EPOCH
	 * @return array|bool
	 */
	function detectPayPeriodScheduleDates( $type_id, $start_date ) {
		$ppsf = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $ppsf */
		$retval = $ppsf->detectPayPeriodScheduleDates( $type_id, $start_date );
		Debug::Arr( $retval, 'Dates: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( $retval );
	}

}

?>
