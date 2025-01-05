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
class APISchedule extends APIFactory {
	protected $main_class = 'ScheduleFactory';

	/**
	 * APISchedule constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default schedule data for creating new schedulees.
	 *     Also see APIRequestSchedule->getRequestScheduleDefaultData()
	 * @param array $data
	 * @return array
	 */
	function getScheduleDefaultData( $data = null ) {
		Debug::Text( 'Getting schedule default data...', __FILE__, __LINE__, __METHOD__, 10 );

		$retarr = [
				'status_id'          => 10,
				'user_id'            => $this->getCurrentUserObject()->getID(), //prevent '...' in combobox
				'start_time'         => TTDate::getAPIDate( 'TIME', strtotime( '8:00 AM' ) ),
				'end_time'           => TTDate::getAPIDate( 'TIME', strtotime( '5:00 PM' ) ),
				'schedule_policy_id' => TTUUID::getZeroID(),

				//JS will figure out these values based on selected cells.
				'branch_id'          => TTUUID::getNotExistID(),
				'department_id'      => TTUUID::getNotExistID(),
				'job_id'             => TTUUID::getNotExistID(),
				'job_item_id'        => TTUUID::getNotExistID(),
				'punch_tag_id'       => TTUUID::getNotExistID(),
		];

		//Get all user_ids.
		$user_ids = [];
		if ( is_array( $data ) ) {
			$first_date_stamp = $last_date_stamp = false;

			foreach ( $data as $row ) {
				$user_ids[] = ( isset( $row['user_id'] ) ) ? $row['user_id'] : $this->getCurrentUserObject()->getId();

				$date_stamp = ( isset( $row['date'] ) ) ? TTDate::parseDateTime( $row['date'] ) : time();
				if ( $date_stamp < $first_date_stamp || $first_date_stamp == false ) {
					$first_date_stamp = $date_stamp;
				}
				if ( $date_stamp > $last_date_stamp || $last_date_stamp == false ) {
					$last_date_stamp = $date_stamp;
				}
			}

			if ( isset( $user_ids[0] ) ) {
				$retarr['user_id'] = $user_ids[0];
			}

			Debug::Arr( $user_ids, 'First Date Stamp: ' . $first_date_stamp . ' Last: ' . $last_date_stamp . ' User Ids: ', __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			$retarr['date_stamp'] = TTDate::getDate( 'DATE', time() );

			Debug::Text( 'No input data to base defaults on...', __FILE__, __LINE__, __METHOD__, 10 );

			return $retarr;
		}

		//Try to determine most common start/end times to use by default.
		//  Need to use getScheduleArray() for this so it includes both recurring and committed shifts.
		$filter_data = [
				'id'         => $user_ids,
				'start_date' => $first_date_stamp,
				'end_date'   => $last_date_stamp,
		];

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$schedule_arr = $sf->getScheduleArray( $filter_data );
		if ( !is_array( $schedule_arr ) || count( $schedule_arr ) == 0 ) { //If no data was returned, try to look back further.
			$filter_data['start_date'] = TTDate::getBeginWeekEpoch( ( TTDate::getMiddleDayEpoch( $first_date_stamp ) - ( 86400 * 7 ) ) );
			$filter_data['end_date'] = TTDate::getEndWeekEpoch( $last_date_stamp );
			$schedule_arr = $sf->getScheduleArray( $filter_data );
		}

		if ( is_array( $schedule_arr ) && count( $schedule_arr ) > 0 ) {
			$schedule_arr = Misc::flattenArrayOneLevel( (array)$schedule_arr ); //Flattens the array by one level, to remove the ISO date keys.

			//Merge the few fields from $schedule_arr data into the existing $retarr otherwise we overwrite all the default branch/department/job/task data obtained above.
			$retarr = array_merge( $retarr, Misc::arrayCommonValuesForEachKey( $schedule_arr, [ 'start_time', 'end_time', 'schedule_policy_id' ] ) );
		}

		$retarr = $sf->getCustomFieldsDefaultData( $this->getCurrentCompanyObject()->getId(), $retarr );

		Debug::Arr( $retarr, 'Default data...', __FILE__, __LINE__, __METHOD__, 10 );

		return $this->returnHandler( $retarr );
	}

	/**
	 * Get all necessary dates for building the schedule in a single call, this is mainly as a performance optimization.
	 * @param int $base_date EPOCH
	 * @param $type
	 * @param bool $strict
	 * @return array
	 * @internal param array $data filter data
	 */
	function getScheduleDates( $base_date, $type, $strict = true ) {
		$epoch = TTDate::parseDateTime( $base_date );

		if ( $epoch == '' || $epoch < 946728000 || $epoch > ( time() + ( 3650 * 86400 ) ) ) { //Make sure date is after 01-Jan-2000 and before 10 years in the future.
			$epoch = TTDate::getTime();
		}

		if ( empty( $type ) == true ) {
			$type = 'week';
		}

		switch ( strtolower( $type ) ) {
			case 'day':
				if ( $strict == true ) {
					$start_date = TTDate::getBeginDayEpoch( $epoch );
					$end_date = TTDate::getEndDayEpoch( $epoch );
				} else {
					$start_date = TTDate::getBeginDayEpoch( $epoch );
					//$end_date = TTDate::getBeginDayEpoch( ( TTDate::getMiddleDayEpoch( $epoch ) + 86400 ) );
					$end_date = TTDate::getEndDayEpoch( ( TTDate::getMiddleDayEpoch( $epoch ) + 86400 ) );
				}
				break;
			default: //Make Week the default mode just in case.
			case 'week':
				if ( $strict == true ) {
					$start_date = TTDate::getBeginWeekEpoch( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
					$end_date = TTDate::getEndWeekEpoch( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
				} else {
					$start_date = TTDate::getBeginDayEpoch( $epoch );
					//$end_date = TTDate::getBeginDayEpoch( ( TTDate::getMiddleDayEpoch( $epoch ) + (6 * 86400) ) );
					$end_date = TTDate::getEndDayEpoch( ( TTDate::incrementDate( $epoch, 1, 'week' ) - 3600 ) );
				}
				break;
			case 'month':
				if ( $strict == true ) {
					$start_date = TTDate::getBeginWeekEpoch( TTDate::getBeginMonthEpoch( $epoch ), $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
					$end_date = TTDate::getEndWeekEpoch( TTDate::getEndMonthEpoch( $epoch ), $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
				} else {
					//This should be 5 weeks from the base date.
					$start_date = TTDate::getBeginDayEpoch( $epoch );
					$end_date = TTDate::getEndDayEpoch( TTDate::incrementDate( $epoch, 5, 'week' ) );
					//$end_date = TTDate::getEndWeekEpoch( ( TTDate::getMiddleDayEpoch( $epoch ) + (30 * 86400) ), TTDate::getDayOfWeek( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() ) ) + 1;
				}
				break;
			case 'year':
				if ( $strict == true ) {
					$start_date = TTDate::getBeginWeekEpoch( TTDate::getBeginMonthEpoch( $epoch ), $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
					$end_date = TTDate::getEndWeekEpoch( TTDate::getEndMonthEpoch( ( TTDate::getEndMonthEpoch( $epoch ) + ( 86400 * 2 ) ) ), $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
				} else {
					//This should be 2 months from the base date.
					$start_date = TTDate::getBeginDayEpoch( $epoch );
					//$end_date = TTDate::getBeginDayEpoch( ( TTDate::getMiddleDayEpoch( $epoch ) + (62 * 86400) ), TTDate::getDayOfWeek( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() ) ) + 1;
					$end_date = TTDate::getEndDayEpoch( TTDate::incrementDate( $epoch, 2, 'month' ) );
				}
				break;
		}

		$retarr = [
				'base_date'          => $epoch,
				'start_date'         => $start_date,
				'end_date'           => $end_date,
				'base_display_date'  => TTDate::getAPIDate( 'DATE', $epoch ),
				'start_display_date' => TTDate::getAPIDate( 'DATE', $start_date ),
				'end_display_date'   => TTDate::getAPIDate( 'DATE', $end_date ),
		];

		Debug::Arr( $retarr, 'Schedule Dates: Base Date: ' . $base_date . ' Type: ' . $type . ' Strict: ' . (int)$strict, __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * Get combined recurring schedule and committed schedule data for one or more schedulees.
	 * @param array $data    filter data
	 * @param int $base_date EPOCH
	 * @param null $type
	 * @param null $strict
	 * @return array|bool
	 */
	function getCombinedSchedule( $data = null, $base_date = null, $type = null, $strict = null ) {
		if ( !$this->getPermissionObject()->Check( 'schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'schedule', 'view' ) || $this->getPermissionObject()->Check( 'schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'schedule', 'view_child' ) ) ) {
			Debug::Text( 'aPermission Denied!...', __FILE__, __LINE__, __METHOD__, 10 );

			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( !isset( $data['filter_data'] ) ) {
			$data['filter_data'] = [];
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		//Use the SQL based permission checks to avoid bugs with 'schedule' -> 'view' == TRUE and 'schedule' -> 'edit_child' == TRUE not allowing the user to edit any records.
		$data['filter_data'] = array_merge( (array)$data['filter_data'], $this->getPermissionObject()->getPermissionFilterData( 'schedule', 'view' ) );

		//Get Permission Hierarchy Children for wages first, as this can be used for viewing, or editing.
		$data['filter_data']['wage_permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view' );

		if ( $base_date != '' ) {
			$schedule_dates = $this->getScheduleDates( $base_date, $type, $strict );
			$data['filter_data']['start_date'] = $schedule_dates['start_date'];
			$data['filter_data']['end_date'] = $schedule_dates['end_date'];
		}

		//If we don't have permissions to view open shifts, exclude user_id = 0;
		if ( $this->getPermissionObject()->Check( 'schedule', 'view_open' ) == true
				&& ( $this->getPermissionObject()->Check( 'schedule', 'view_own' ) == true || $this->getPermissionObject()->Check( 'schedule', 'view_child' ) == true ) ) {
			$data['filter_data']['permission_is_id'] = TTUUID::getZeroID(); //Always allow this user_id to be returned.
		} else if ( $this->getPermissionObject()->Check( 'schedule', 'view_open' ) == false ) {
			$data['filter_data']['exclude_id'] = [ TTUUID::getZeroID() ];
		}

		//Pass items per page through to getScheduleArray()
		//This must come before initializeFilterAndPager()
		if ( isset( $data['filter_items_per_page'] ) ) {
			$data['filter_data']['filter_items_per_page'] = $data['filter_items_per_page'];
		}

		$data = $this->initializeFilterAndPager( $data );

		$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */

		if ( DEPLOYMENT_ON_DEMAND == true ) {
			$sf->setQueryStatementTimeout( 60000 );
		}

		$sf->setAPIMessageID( $this->getAPIMessageID() );

		$retarr = $sf->getScheduleArray( $data['filter_data'] );
		//Hide wages if the user doesn't have permission to see them.
		if ( is_array( $retarr ) ) {
			foreach ( $retarr as $date_stamp => $shifts ) {
				foreach ( $shifts as $key => $row ) {
					//Hide wages if the user doesn't have permission to see them.
					if ( !( $this->getPermissionObject()->Check( 'wage', 'view' ) == true
							|| ( $this->getPermissionObject()->Check( 'wage', 'view_own' ) == true && $this->getPermissionObject()->isOwner( false, $row['user_id'] ) == true )
							|| ( $this->getPermissionObject()->Check( 'wage', 'view_child' ) == true && $this->getPermissionObject()->isChild( $row['user_id'], $data['filter_data']['wage_permission_children_ids'] ) == true )
					) ) {
						$retarr[$date_stamp][$key]['hourly_rate'] = $retarr[$date_stamp][$key]['total_time_wage'] = 0;
					}
					//$sf->getPermissionColumns( $retarr[$date_stamp][$key], $row['user_id'], $row['created_by_id'], $data['filter_data']['permission_children_ids'], $data['filter_columns'] ); //This is handled in SQL from getScheduleArray() now, no need to pass in children IDs.
				}
			}
		}

		if ( isset( $schedule_dates ) ) {
			//
			//Get holiday data.
			//
			$holiday_data = [];
			$hlf = TTnew( 'HolidayListFactory' ); /** @var HolidayListFactory $hlf */
			$hlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), [ 'start_date' => $schedule_dates['start_date'], 'end_date' => $schedule_dates['end_date'] ], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
			Debug::Text( 'Holiday Record Count: ' . $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $hlf->getRecordCount() > 0 ) {
				foreach ( $hlf as $h_obj ) {
					$holiday_data[] = $h_obj->getObjectAsArray();
				}
			}
			unset( $hlf, $h_obj );

			$retarr = [
					'schedule_dates' => $schedule_dates,
					'holiday_data'   => $holiday_data,
					'schedule_data'  => $retarr,
			];
		}

		//Debug::Arr($retarr, 'RetArr: ', __FILE__, __LINE__, __METHOD__, 10);
		return $this->returnHandler( $retarr );
	}


	/**
	 * Get schedule data for one or more schedulees.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getSchedule( $data = null, $disable_paging = false ) {
		if ( !$this->getPermissionObject()->Check( 'schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'schedule', 'view' ) || $this->getPermissionObject()->Check( 'schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'schedule', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		//No filter data, restrict to last pay period as a performance optimization when hundreds of thousands of schedules exist.
		//The issue with this though is that the API doesn't know what the filter criteria is, so it can't display this to the user.
		//if ( count($data['filter_data']) == 0 ) {
		if ( !isset( $data['filter_data']['id'] ) && !isset( $data['filter_data']['pay_period_ids'] ) && !isset( $data['filter_data']['pay_period_id'] ) && ( !isset( $data['filter_data']['start_date'] ) && !isset( $data['filter_data']['end_date'] ) ) ) {
			Debug::Text( 'Adding default filter data...', __FILE__, __LINE__, __METHOD__, 10 );
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$pplf->getByCompanyIdAndEndDate( $this->getCurrentCompanyObject()->getId(), time() ); //Use pay periods that are todays date falls within. Don't limit it to anything as they could have multiple pay period schedules.
			if ( $pplf->getRecordCount() > 0 ) {
				$data['filter_data']['pay_period_ids'] = (array)$pplf->getIDSByListFactory( $pplf );
			} else {
				//Get most recent two pay periods if there isn't a pay period that covers todays date.
				$pplf->getByCompanyId( $this->getCurrentCompanyObject()->getId(), 2 ); //Limit=2
				$pay_period_ids = array_keys( (array)$pplf->getArrayByListFactory( $pplf, false, false ) );
				if ( isset( $pay_period_ids[0] ) && isset( $pay_period_ids[1] ) ) {
					$data['filter_data']['pay_period_ids'] = [ $pay_period_ids[0], $pay_period_ids[1] ];
				}
				unset( $pay_period_ids );
			}
			unset( $pplf );
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'schedule', 'view' );

		//If we don't have permissions to view open shifts, exclude user_id = 0;
		if ( $this->getPermissionObject()->Check( 'schedule', 'view_open' ) == false ) {
			$data['filter_data']['exclude_id'] = [ TTUUID::getZeroID() ];
		} else if ( is_array( $data['filter_data']['permission_children_ids'] ) && count( $data['filter_data']['permission_children_ids'] ) > 0 ) {
			//If schedule, view_open is allowed but they are also only allowed to see their subordinates (which they have some of), add "open" employee as if they are a subordinate.
			$data['filter_data']['permission_children_ids'][] = TTUUID::getZeroID();
		}

		$blf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $blf */
		if ( DEPLOYMENT_ON_DEMAND == true ) {
			$blf->setQueryStatementTimeout( 60000 );
		}
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $blf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $blf->getRecordCount() );

			$this->setPagerObject( $blf );

			//Make sure if hourly_rates are ever exposed in ScheduleFactory that the proper permissions are checked here.
			$retarr = [];
			foreach ( $blf as $b_obj ) {
				$retarr[] = $b_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $blf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			//Debug::Arr($retarr, 'Schedule Data: ', __FILE__, __LINE__, __METHOD__, 10);

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Export wage data to csv
	 * @param string $format file format (csv)
	 * @param array $data    filter data
	 * @param bool $disable_paging
	 * @return array
	 */
	function exportSchedule( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getSchedule( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_schedule', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonScheduleData( $data ) {
		//Trying to Mass Edit only recurring schedule shifts (gray font) will cause an empty 'id' array to be passed and all schedules to be returned, causing things to really slow down.
		//This can be removed after the HTML5 bug is fixed to avoid sending the bogus filter data. 01-Jul-15.
		if ( isset( $data['filter_data']['id'] ) && is_array( $data['filter_data']['id'] ) && count( $data['filter_data']['id'] ) == 0 ) {
			return $this->returnHandler( true ); //No records returned.
		}

		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getSchedule( $data, true ) ) );
	}

	/**
	 * Validate schedule data for one or more schedulees.
	 * @param array $data schedule data
	 * @return array
	 */
	function validateSchedule( $data ) {
		return $this->setSchedule( $data, true );
	}

	/**
	 * Set schedule data for one or more schedulees.
	 * @param array $data schedule data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setSchedule( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getCurrentUserObject()->getStatus() != 10 ) { //10=Active -- Make sure user record is active as well.
			return $this->getPermissionObject()->PermissionDenied( false, TTi18n::getText( 'Employee status must be Active to modify schedules' ) );
		}

		if ( !$this->getPermissionObject()->Check( 'schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'schedule', 'edit' ) || $this->getPermissionObject()->Check( 'schedule', 'edit_own' ) || $this->getPermissionObject()->Check( 'schedule', 'edit_child' ) || $this->getPermissionObject()->Check( 'schedule', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
			$permission_children_ids = [];
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		//If they have permissions to view open shifts, assume "0" is one of their subordinates.
		if ( $this->getPermissionObject()->Check( 'schedule', 'view_open' ) == true ) {
			$permission_children_ids[] = TTUUID::getZeroID();
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Schedules', __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr($data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);

		global $config_vars;
		if ( isset( $config_vars['other']['import_maximum_records_limit'] ) && $config_vars['other']['import_maximum_records_limit'] != '' ) {
			$maximum_record_limit = $config_vars['other']['import_maximum_records_limit'];
		} else {
			if ( DEPLOYMENT_ON_DEMAND == true ) {
				$maximum_record_limit = 1000;
			} else {
				$maximum_record_limit = 0;
			}
		}
		if ( $this->getCurrentCompanyObject()->getProductEdition() == 10 ) {
			$maximum_record_limit = 50;
		}
		if ( $maximum_record_limit > 0 && $total_records > $maximum_record_limit ) {
			Debug::Text( 'Maximum Records allowed for Import: ' . $maximum_record_limit . ' Validate Only: ' . (int)$validate_only, __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Maximum records per batch of %1 exceeded. Please reduce the number of records and try again.', [ $maximum_record_limit ] ) );
		}

		if ( $total_records > 6 && Misc::isSystemLoadValid() == false ) { //Check system load before allow any batch operations. Must continue to allow editing of a single record though.
			Debug::Text( 'ERROR: System load exceeded, preventing batch processes from starting...', __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Please try again later, or reduce the number of records in your batch...' ) );
		}

		//If more than 50 records will be edited or created and user selected "Notify Employee" let them know this is not currently supported.
		if ( $total_records > 50 ) {
			if ( is_array( $data ) && in_array( true, array_column( $data, 'notify_user_schedule_change' ) ) ) {
				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Notify Employee setting cannot be used when more than 50 shifts are created or modified.' ) );
			}
		}

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $row ) {
				$transaction_function = function () use ( $row, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key, $total_records, $permission_children_ids ) {
					$primary_validator = new Validator();

					$lf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $lf */
					if ( $validate_only == false ) {                  //Only switch into serializable mode when actually saving the record.
						$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing duplicate user records or duplicate employee number/user_names.
					}
					$lf->StartTransaction();

					if ( isset( $row['id'] ) && $row['id'] != '' && $row['id'] != TTUUID::getZeroID() && $row['id'] != TTUUID::getNotExistID() ) {
						//Modifying existing object.
						//Get schedule object, so we can only modify just changed data for specific records if needed.
						$lf->getByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
						if ( $lf->getRecordCount() == 1 ) {
							//Object exists, check edit permissions
							if (
									$validate_only == true
									||
									(
											$this->getPermissionObject()->Check( 'schedule', 'edit' )
											|| ( $this->getPermissionObject()->Check( 'schedule', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
											|| ( $this->getPermissionObject()->Check( 'schedule', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true )
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
						if ( !( $validate_only == true
								||
								( $this->getPermissionObject()->Check( 'schedule', 'add' )
										&&
										(
												$this->getPermissionObject()->Check( 'schedule', 'edit' )
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'schedule', 'edit_own' ) && $this->getPermissionObject()->isOwner( false, $row['user_id'] ) === true ) //We don't know the created_by of the user at this point, but only check if the user is assigned to the logged in person.
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'schedule', 'edit_child' ) && $this->getPermissionObject()->isChild( $row['user_id'], $permission_children_ids ) === true )
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'schedule', 'view_open' ) && TTUUID::castUUID( $row['user_id'] ) == TTUUID::getZeroID() )
										)
								)
						) ) {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Add permission denied' ) );
						}
						$row['id'] = $this->getNextInsertID();
					}
					Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

					$is_valid = $primary_validator->isValid();
					if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.

						$row['company_id'] = $this->getCurrentCompanyObject()->getId();     //This prevents a validation error if company_id is FALSE.

						$lf->setObjectFromArray( $row );

						Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

						$lf->Validator->setValidateOnly( $validate_only );

						$is_valid = $lf->isValid( $ignore_warning );
						if ( $is_valid == true ) {
							Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
							$lf->setEnableTimeSheetVerificationCheck( true ); //Unverify timesheet if its already verified.
							$lf->setEnableReCalculateDay( true );             //Need to recalculate absence time when editing a schedule, in case schedule policy changed.

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

						$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ], ( $total_records > 1 && is_object( $lf->getUserObject() ) ? $lf->getUserObject()->getFullName() : null ) );
					} else if ( $validate_only == true ) {
						$lf->FailTransaction();
					}

					$lf->CommitTransaction();
					$lf->setTransactionMode(); //Back to default isolation level.

					return [ $validator, $validator_stats, $key, $save_result ];
				};

				[ $validator, $validator_stats, $key, $save_result ] = $this->getMainClassObject()->RetryTransaction( $transaction_function );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more schedules.
	 * @param array $data schedule data
	 * @return array|bool
	 */
	function deleteSchedule( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( !$this->getPermissionObject()->Check( 'schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'schedule', 'delete' ) || $this->getPermissionObject()->Check( 'schedule', 'delete_own' ) || $this->getPermissionObject()->Check( 'schedule', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();
		//If they have permissions to view open shifts, assume "0" is one of their subordinates.
		if ( $this->getPermissionObject()->Check( 'schedule', 'view_open' ) == true ) {
			$permission_children_ids[] = TTUUID::getZeroID();
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Schedules', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator = $save_result = []; $key = false;
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			foreach ( $data as $key => $id ) {
				$transaction_function = function () use ( $data, $validator_stats, $validator, $save_result, $key, $total_records, $id, $permission_children_ids ) {
					$primary_validator = new Validator();
					$lf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $lf */
					$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing incorrect calculations in user_date_total table.
					$lf->StartTransaction();
					if ( $id != '' ) {
						//Modifying existing object.
						//Get schedule object, so we can only modify just changed data for specific records if needed.
						$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
						if ( $lf->getRecordCount() == 1 ) {
							//Object exists, check edit permissions
							if ( $this->getPermissionObject()->Check( 'schedule', 'delete' )
									|| ( $this->getPermissionObject()->Check( 'schedule', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getUser() ) === true )
									|| ( $this->getPermissionObject()->Check( 'schedule', 'delete_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getUser(), $permission_children_ids ) === true ) ) {
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
						Debug::Arr( $lf->data, 'Current Data: ', __FILE__, __LINE__, __METHOD__, 10 );
						$lf->setDeleted( true );

						$is_valid = $lf->isValid();
						if ( $is_valid == true ) {
							$lf->setEnableReCalculateDay( true ); //Need to remove absence time when deleting a schedule.

							Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
							$save_result[$key] = $lf->Save();
							$validator_stats['valid_records']++;
						}
					}

					if ( $is_valid == false ) {
						Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

						$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

						$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf ], ( $total_records > 1 && is_object( $lf->getUserObject() ) ? $lf->getUserObject()->getFullName() : null ) );
					}

					$lf->CommitTransaction();
					$lf->setTransactionMode(); //Back to default isolation level.

					return [ $validator, $validator_stats, $key, $save_result ];
				};

				[ $validator, $validator_stats, $key, $save_result ] = $this->getMainClassObject()->RetryTransaction( $transaction_function );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Get schedule total time
	 * @param integer $start              Start date epoch
	 * @param integer $end                End date epoch
	 * @param integer $schedule_policy_id Schedule policy ID
	 * @param string $user_id             UUID
	 * @return array|bool
	 */
	function getScheduleTotalTime( $start, $end, $schedule_policy_id = null, $user_id = null ) {
		Debug::text( 'Calculating total time for scheduled shift... Start: ' . $start . ' End: ' . $end, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $start == '' ) {
			return $this->returnHandler( false );
		}

		if ( $end == '' ) {
			return $this->returnHandler( false );
		}

		$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */

		//This helps calculate the schedule total time based on schedule policy or policy groups.
		$sf->setCompany( $this->getCurrentCompanyObject()->getId() );
		if ( !is_array( $user_id ) && $user_id != '' ) {
			$sf->setUser( $user_id );
		}

		//Recurring Schedule Templates and Schedules should all send Dates & Times, otherwise 24hr integer format (ie: 0600) will not get parsed properly (due to being integer) and will result in schedule total time being incorrect.
		$sf->setStartTime( TTDate::parseDateTime( $start ) );
		$sf->setEndTime( TTDate::parseDateTime( $end ) );

		$sf->setSchedulePolicyId( $schedule_policy_id );

		$sf->Validator->setValidateOnly( true );

		$sf->preValidate();

		return $this->returnHandler( $sf->getTotalTime() );
	}

	/**
	 * Swap schedules with one another. This doesn't work with recurring schedules, and is not used by Flex currently.
	 * @param array $src_ids Source schedule IDs
	 * @param array $dst_ids Destination schedule IDs
	 * @return array
	 */
	function swapSchedule( $src_ids, $dst_ids ) {
		$src_ids = (array)$src_ids;
		$dst_ids = (array)$dst_ids;

		$data = array_merge( $src_ids, $dst_ids );
		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		Debug::Text( 'Received data for: ' . count( $data ) . ' Schedules', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$src_rows = $this->stripReturnHandler( $this->getSchedule( [ 'filter_data' => [ 'id' => $data ] ], true ) );
		if ( is_array( $src_rows ) && count( $src_rows ) == count( $data ) ) {
			//Debug::Arr($src_rows, 'SRC Rows: ', __FILE__, __LINE__, __METHOD__, 10);

			$id_to_row_map = [];
			//Map returned rows to ids so we can reference them directly.
			foreach ( $src_rows as $key => $row ) {
				$id_to_row_map[$row['id']] = $key;
			}
			Debug::Arr( $id_to_row_map, 'ID to Row Map: ', __FILE__, __LINE__, __METHOD__, 10 );

			//Handle swapping several schedules all at once.
			//Loop through each src ID and swap it with the same dst_id.
			$dst_rows = [];
			foreach ( $src_ids as $src_key => $src_id ) {
				$dst_id = $dst_ids[$src_key];

				$src_row_key = $id_to_row_map[$src_id];
				$dst_row_key = $id_to_row_map[$dst_id];
				Debug::Text( 'SRC Key: ' . $src_key . ' SRC ID: ' . $src_id . ' DST ID: ' . $dst_id, __FILE__, __LINE__, __METHOD__, 10 );

				//Leave IDs in tact, so the audit trail reflects an edit. Basically we are just swapping the date_stamp, start/end, branch, department, policy fields.
				$dst_rows[$src_row_key] = $src_rows[$dst_row_key];
				$dst_rows[$src_row_key]['id'] = $src_rows[$src_row_key]['id'];
				$dst_rows[$src_row_key]['user_id'] = $src_rows[$src_row_key]['user_id'];
				//Need to set columns like user_date_id to NULL so its not overridden in setScheduel().
				$dst_rows[$src_row_key]['start_date'] = $dst_rows[$src_row_key]['end_date'] = $dst_rows[$src_row_key]['date_stamp'] = $dst_rows[$src_row_key]['pay_period_id'] = null;

				$dst_rows[$dst_row_key] = $src_rows[$src_row_key];
				$dst_rows[$dst_row_key]['id'] = $src_rows[$dst_row_key]['id'];
				$dst_rows[$dst_row_key]['user_id'] = $src_rows[$dst_row_key]['user_id'];
				//Need to set columns like user_date_id to NULL so its not overridden in setScheduel().
				$dst_rows[$dst_row_key]['start_date'] = $dst_rows[$dst_row_key]['end_date'] = $dst_rows[$dst_row_key]['date_stamp'] = $dst_rows[$dst_row_key]['pay_period_id'] = null;
			}

			//Debug::Arr($dst_rows, 'DST Rows: ', __FILE__, __LINE__, __METHOD__, 10);
			if ( is_array( $dst_rows ) ) {
				return $this->setSchedule( $dst_rows );
			}
		}

		return $this->returnHandler( false );
	}

	/**
	 * Creates punches from an array of scheduled shift ids.
	 *
	 * @param array $schedule_arr should have 2 sub arrays of ids, one labeled 'schedule', one labeled 'recurring'
	 * @return array|bool
	 */
	function addPunchesFromScheduledShifts( $schedule_arr ) {
		if ( $this->getCurrentCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			if ( !( $this->getPermissionObject()->Check( 'punch', 'edit' ) || $this->getPermissionObject()->Check( 'punch', 'edit_own' ) || $this->getPermissionObject()->Check( 'punch', 'edit_child' ) || $this->getPermissionObject()->Check( 'punch', 'add' ) ) ) {
				return $this->getPermissionObject()->PermissionDenied();
			}

			if ( !$this->getPermissionObject()->Check( 'schedule', 'enabled' )
					|| !( $this->getPermissionObject()->Check( 'schedule', 'view' ) || $this->getPermissionObject()->Check( 'schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'schedule', 'view_child' ) )
			) {
				return $this->getPermissionObject()->PermissionDenied();
			}

			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' );

			if ( isset( $schedule_arr['recurring'] ) && is_array( $schedule_arr['recurring'] ) && count( $schedule_arr['recurring'] ) > 0 ) {
				$data['filter_data']['id'] = $schedule_arr['recurring'];
				$rslf = TTnew( 'RecurringScheduleListFactory' ); /** @var RecurringScheduleListFactory $rslf */
				$rslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'] );

				if ( $rslf->getRecordCount() > 0 ) {
					foreach ( $rslf as $rs_obj ) {
						if ( $this->getPermissionObject()->Check( 'punch', 'add' )
								&& ( $this->getPermissionObject()->Check( 'punch', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'punch', 'edit_own' ) && $this->getPermissionObject()->isOwner( $rs_obj->getCurrent()->getCreatedBy(), $rs_obj->getCurrent()->getUser() ) === true )
										|| ( $this->getPermissionObject()->Check( 'punch', 'edit_child' ) && $this->getPermissionObject()->isChild( $rs_obj->getCurrent()->getUser(), $data['filter_data']['permission_children_ids'] ) === true ) )
						) {
							$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
							$result = $sf->addPunchFromScheduleObject( $rs_obj );
							unset( $sf );

							//Don't try to punch open schedules.
							if ( $rs_obj->getUserObject() == false ) {
								UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext( 'OPEN' ), 10, TTi18n::gettext( 'Unable to auto-punch OPEN scheduled shift.' ) . '...' );
							} else {
								$user_full_name = $rs_obj->getUserObject()->getFullName();
								$date = TTDate::getDate( 'DATE', $rs_obj->getDateStamp() );
								$start_time = TTDate::getDate( 'TIME', $rs_obj->getStartTime() );
								$end_time = TTDate::getDate( 'TIME', $rs_obj->getEndTime() );
								if ( $result == true ) {
									//success
									UserGenericStatusFactory::queueGenericStatus( $user_full_name, 30, $date . ' - ' . TTi18n::gettext( 'In' ) . ': ' . $start_time . ' ' . TTi18n::gettext( 'Out' ) . ': ' . $end_time, true );
								} else {
									//error
									UserGenericStatusFactory::queueGenericStatus( $user_full_name, 20, $date . ' - ' . TTi18n::gettext( 'In' ) . ': ' . $start_time . ' ' . TTi18n::gettext( 'Out' ) . ': ' . $end_time . ' - ' . TTi18n::gettext( 'Unable to create punches, they may already exist' ) . '...', null );
								}
							}
						}
						unset( $user_full_name, $date, $start_time, $end_time );
					}
				}
				unset( $rslf );
			}

			if ( isset( $schedule_arr['schedule'] ) && is_array( $schedule_arr['schedule'] ) && count( $schedule_arr['schedule'] ) > 0 ) {
				$data['filter_data']['id'] = $schedule_arr['schedule'];
				$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
				$slf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'] );

				if ( $slf->getRecordCount() > 0 ) {
					foreach ( $slf as $s_obj ) {
						if ( $this->getPermissionObject()->Check( 'punch', 'add' )
								&& ( $this->getPermissionObject()->Check( 'punch', 'edit' )
										|| ( $this->getPermissionObject()->Check( 'punch', 'edit_own' ) && $this->getPermissionObject()->isOwner( $s_obj->getCurrent()->getCreatedBy(), $s_obj->getCurrent()->getUser() ) === true )
										|| ( $this->getPermissionObject()->Check( 'punch', 'edit_child' ) && $this->getPermissionObject()->isChild( $s_obj->getCurrent()->getUser(), $data['filter_data']['permission_children_ids'] ) === true ) )
						) {
							$result = $s_obj->addPunchFromScheduleObject( $s_obj );

							//don't try to punch open schedules.
							if ( $s_obj->getUserObject() == false ) {
								UserGenericStatusFactory::queueGenericStatus( TTi18n::gettext( 'OPEN' ), 10, TTi18n::gettext( 'Unable to auto-punch OPEN scheduled shift.' ) . '...' );
							} else {
								$user_full_name = $s_obj->getUserObject()->getFullName();
								$date = TTDate::getDate( 'DATE', $s_obj->getDateStamp() );
								$start_time = TTDate::getDate( 'TIME', $s_obj->getStartTime() );
								$end_time = TTDate::getDate( 'TIME', $s_obj->getEndTime() );
								if ( $result == true ) {
									//success
									UserGenericStatusFactory::queueGenericStatus( $user_full_name, 30, $date . ' - ' . TTi18n::gettext( 'In' ) . ': ' . $start_time . ' ' . TTi18n::gettext( 'Out' ) . ': ' . $end_time, true );
								} else {
									//error
									UserGenericStatusFactory::queueGenericStatus( $user_full_name, 20, $date . ' - ' . TTi18n::gettext( 'In' ) . ': ' . $start_time . ' ' . TTi18n::gettext( 'Out' ) . ': ' . $end_time . ' - ' . TTi18n::gettext( 'Unable to create punches, they may already exist' ) . '...', null );
								}
							}
						}
						unset( $user_full_name, $date, $start_time, $end_time );
					}
				}
				unset( $slf );
			}
			unset( $data );

			if ( UserGenericStatusFactory::isStaticQueue() == true ) {
				$ugsf = TTnew( 'UserGenericStatusFactory' ); /** @var UserGenericStatusFactory $ugsf */
				$ugsf->setUser( $this->getCurrentUserObject()->getId() );
				$ugsf->setBatchID( $ugsf->getNextBatchId() );
				$ugsf->setQueue( UserGenericStatusFactory::getStaticQueue() );
				$ugsf->saveQueue();
				$user_generic_status_batch_id = $ugsf->getBatchID();
			} else {
				$user_generic_status_batch_id = false;
			}
			unset( $ugsf );

			return $this->returnHandler( true, true, false, false, false, $user_generic_status_batch_id );
		}

		return $this->returnHandler( true );
	}

	/**
	 * Get overlapping schedule data for one or more schedulees.
	 * @param array $data
	 * @return array|bool
	 */
	function getOverlappingShifts( $data = null ) {
		if ( !$this->getPermissionObject()->Check( 'schedule', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'schedule', 'view' ) || $this->getPermissionObject()->Check( 'schedule', 'view_own' ) || $this->getPermissionObject()->Check( 'schedule', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( !isset( $data['user_id'] ) || TTUUID::isUUID( $data['user_id'] ) == false ) {
			return $this->returnHandler( false );
		}

		$permission_children_ids = $this->getPermissionChildren();

		if ( ( $this->getPermissionObject()->Check( 'schedule', 'view' )
						|| ( isset( $data['user_id'] ) && $this->getPermissionObject()->Check( 'schedule', 'view_own' ) && $this->getPermissionObject()->isOwner( false, $data['user_id'] ) === true ) //check if the user is assigned to the logged in person.
						|| ( isset( $data['user_id'] ) && $this->getPermissionObject()->Check( 'schedule', 'view_child' ) && $this->getPermissionObject()->isChild( $data['user_id'], $permission_children_ids ) === true ) )
				=== false ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( isset( $data['start_date'] ) && $data['start_date'] != '' && isset( $data['end_date'] ) && $data['end_date'] != '' ) {
			$filter_data = [
					'start_date'       => TTDate::getBeginDayEpoch( TTDate::parseDateTime( $data['start_date'] ) ),
					'end_date'         => TTDate::getEndDayEpoch( TTDate::parseDateTime( $data['end_date'] ) ),
					'include_user_ids' => $data['user_id'],
			];

			//As the user is entering start/end dates, they could change the year to like 2010 to 2023, which would cause this function to take too long to process.
			//  So put a cap on the maximum number of days we accept to prevent run away processing.
			if ( abs( TTDate::getDayDifference( $filter_data['start_date'], $filter_data['end_date'] ) ) > 90 ) {
				Debug::Text( 'ERROR: Date range exceeds 90 days, not returning any overlapping shifts...', __FILE__, __LINE__, __METHOD__, 10 );
				return $this->returnHandler( [] );
			}

			$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
			$combined_shifts = $sf->getScheduleArray( $filter_data );

			//No overlap are shifts that do not overlap with the specified dates and are added as is without any modifications or splits.
			$retarr = [ 'original' => [], 'split' => [], 'no_overlap' => [], 'open_replaced' => [] ];

			if ( is_array( $combined_shifts ) && count( $combined_shifts ) > 0 ) {
				foreach ( $combined_shifts as $combined_shift_days ) {
					foreach ( $combined_shift_days as $combined_shift ) {

						$schedule_day = [
								'start_time_stamp' => $combined_shift['start_time_stamp'],
								'end_time_stamp'   => $combined_shift['end_time_stamp'],
								'date_stamp'       => $combined_shift['date_stamp'],
								'id'               => $combined_shift['id'],
								'start_date'       => $combined_shift['start_date'],
								'end_date'         => $combined_shift['end_date'],
								'total_time'       => (int)$combined_shift['total_time'],
						];

						if ( $data['requested_days'][TTDate::getDayOfWeek( $combined_shift['start_time_stamp'] )] === true ) {
							$schedule_day['do_not_split'] = false;
						} else {
							$schedule_day['do_not_split'] = true; //Not a day of the week that the user requested to split.
						}

						if ( isset( $schedule_day['id'] ) == false || $schedule_day['id'] == false || $schedule_day['id'] == TTUUID::getZeroID() ) {
							$schedule_day['comitted_shift'] = false;
							//Need unique ID for recurring to properly match split shifts with the original scheduled recurring shift.
							$schedule_day['id'] = $combined_shift['recurring_schedule_id'];
						} else {
							$schedule_day['comitted_shift'] = true;
						}

						$retarr['original'][] = $schedule_day;

						if ( TTDate::doesRangeSpanMidnight( $schedule_day['start_time_stamp'], $schedule_day['end_time_stamp'] ) == true
								&& $data['requested_days'][TTDate::getDayOfWeek( $schedule_day['end_time_stamp'] )] === true ) {
							//To make sure a shift that spans two days has the second day split, add it to the original array if the second
							//day is a day that the user requested to split. With the split time being on the second day.
							$schedule_day = [
									'start_time_stamp' => $combined_shift['start_time_stamp'],
									'end_time_stamp'   => $combined_shift['end_time_stamp'],
									'date_stamp'       => TTDate::getDate( 'DATE', $schedule_day['end_time_stamp'] ),
									'id'               => $combined_shift['id'],
									'start_date'       => $combined_shift['start_date'],
									'end_date'         => $combined_shift['end_date'],
									'total_time'       => (int)$combined_shift['total_time'],
									'comitted_shift'   => $schedule_day['comitted_shift'],
									'not_unique'       => true,
							];
							$retarr['original'][] = $schedule_day;
						}
					}
				}

				$retarr['split'] = TTDate::splitTimesByStartAndEndTime( $retarr['original'], $data['start_time'], $data['end_time'] );
			}

			//If request does not overlap with existing schedule (created splits) merge it into array as request still needs to be added.
			foreach ( TTDate::getDatePeriod( ( TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $data['start_date'] ) ) ), ( TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $data['end_date'] ) ) ), 'P1D' ) as $date ) {
				$dow = TTDate::getDayOfWeek( $date );
				if ( $data['requested_days'][$dow] === true ) {
					$add_request_as_shift = true;
					$split_start_time_stamp = TTDate::parseDateTime( TTDate::getDate( 'DATE', $date ) . ' ' . $data['start_time'] );
					$split_end_time_stamp = TTDate::parseDateTime( TTDate::getDate( 'DATE', $date ) . ' ' . $data['end_time'] );

					if ( $split_end_time_stamp <= $split_start_time_stamp ) {
						$split_end_time_stamp = strtotime( '+1 day', $split_end_time_stamp );
					}
					foreach ( $retarr['split'] as $shift ) {
						$overlap_status = TTDate::getTimeOverLap( $shift['start_time_stamp'], $shift['end_time_stamp'], $split_start_time_stamp, $split_end_time_stamp );
						if ( $overlap_status !== false ) {
							$add_request_as_shift = false;
						}
					}
					if ( $add_request_as_shift == true ) {
						$retarr['no_overlap'][] = [
								'start_date'     => TTDate::getDate( 'DATE', $split_start_time_stamp ),
								'end_date'       => TTDate::getDate( 'DATE', $split_end_time_stamp ),
								'start_time'     => $data['start_time'],
								'end_time'       => $data['end_time'],
								'id'             => TTUUID::getNotExistID(),
								'comitted_shift' => false,
								'split_state'    => 'no_overlap',
								'split_parent'   => TTUUID::getNotExistID(),
								'total_time'     => (int)$this->stripReturnHandler( $this->getScheduleTotalTime( $split_start_time_stamp, $split_end_time_stamp, $data['schedule_policy_id'], $data['user_id'] ) ),
						];
					}
				}
			}

			//Formatting the timestamp into a readable format for the end user.
			if ( !empty( $retarr['split'] ) ) {
				foreach ( $retarr['split'] as $key => $split_shift ) {
					$retarr['split'][$key]['start_date'] = TTDate::getDate( 'DATE', $split_shift['start_time_stamp'] );
					$retarr['split'][$key]['end_date'] = TTDate::getDate( 'DATE', $split_shift['end_time_stamp'] );
					$retarr['split'][$key]['start_time'] = TTDate::getDate( 'TIME', $split_shift['start_time_stamp'] );
					$retarr['split'][$key]['end_time'] = TTDate::getDate( 'TIME', $split_shift['end_time_stamp'] );
					$retarr['split'][$key]['total_time'] = (int)$this->stripReturnHandler( $this->getScheduleTotalTime( $split_shift['start_time_stamp'], $split_shift['end_time_stamp'], $data['schedule_policy_id'], $data['user_id'] ) );

					if ( $split_shift['split_state'] == 'new' || $split_shift['split_state'] == 'replaced' ) {
						$retarr['open_replaced'][] = $retarr['split'][$key];
						$retarr['split'][$key]['is_absence'] = true;
					}
				}
			}
			if ( !empty( $retarr['original'] ) ) {
				foreach ( $retarr['original'] as $key => $original_shift ) {
					$retarr['original'][$key]['start_date'] = TTDate::getDate( 'DATE', $original_shift['start_time_stamp'] );
					$retarr['original'][$key]['end_date'] = TTDate::getDate( 'DATE', $original_shift['end_time_stamp'] );
					$retarr['original'][$key]['start_time'] = TTDate::getDate( 'TIME', $original_shift['start_time_stamp'] );
					$retarr['original'][$key]['end_time'] = TTDate::getDate( 'TIME', $original_shift['end_time_stamp'] );
					$retarr['original'][$key]['total_time'] = (int)$original_shift['total_time'];
				}
			}

			return $this->returnHandler( $retarr );
		} else {
			return $this->returnHandler( [] );
		}

	}

}

?>
