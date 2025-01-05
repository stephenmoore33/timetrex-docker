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
 * @package Modules\Report
 */
class ScheduleSummaryReport extends Report {

	var $special_output_format = [
			'pdf_schedule_group_combined',
			'pdf_schedule_group_combined_print',
			'pdf_schedule_group',
			'pdf_schedule_group_print',
			'pdf_schedule_group_pagebreak', //Insert page breaks after each branch/department.
			'pdf_schedule_group_pagebreak_print',
			'pdf_schedule',
			'pdf_schedule_print',
	];

	/**
	 * ScheduleSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Schedule Summary Report' );
		$this->file_name = 'schedule_summary_report';

		parent::__construct();

		return true;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check( 'report', 'enabled', $user_id, $company_id )
				&& $this->getPermissionObject()->Check( 'report', 'view_schedule_summary', $user_id, $company_id ) ) { //Piggyback on timesheet summary permissions.
			return true;
		}

		if ( $this->getPermissionObject()->Check( 'schedule', 'view', $user_id, $company_id ) == false
				&& $this->getPermissionObject()->Check( 'schedule', 'view_child', $user_id, $company_id ) == false
				&& $this->getPermissionObject()->Check( 'schedule', 'view_own', $user_id, $company_id ) == true ) {
			Debug::Text( 'Regular employee viewing their own timesheet...', __FILE__, __LINE__, __METHOD__, 10 );
			//Regular employee printing timesheet for themselves. Force specific config options.
			//Get current pay period from config, then overwrite it with
			$filter_config = $this->getFilterConfig();
			if ( !isset( $filter_config['time_period'] ) ) {
				$filter_config['time_period'] = 'custom_date';
			}

			if ( !isset( $filter_config['start_date'] ) || !isset( $filter_config['end_date'] ) ) {
				$filter_config['start_date'] = TTDate::getBeginWeekEpoch( time() );
				$filter_config['end_date'] = TTDate::getEndWeekEpoch( time() );
			}

			$this->setFilterConfig( [ 'include_user_id' => [ $user_id ], 'time_period' => $filter_config['time_period'], 'start_date' => $filter_config['start_date'], 'end_date' => $filter_config['end_date'] ] );
		} else {
			Debug::Text( 'Supervisor employee not restricting schedule...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return array|bool|mixed|null
	 */
	protected function _getOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'output_format':
				$retval = parent::getOptions( 'default_output_format' );
				break;
			case 'default_setup_fields':
				$retval = [
						'template',
						'time_period',
						'columns',
				];
				break;
			case 'setup_fields':
				$retval = [
					//Static Columns - Aggregate functions can't be used on these.
					'-1000-template'               => TTi18n::gettext( 'Template' ),
					'-1010-time_period'            => TTi18n::gettext( 'Time Period' ),
					'-2000-legal_entity_id'        => TTi18n::gettext( 'Legal Entity' ),
					'-2010-user_status_id'         => TTi18n::gettext( 'Employee Status' ),
					'-2020-user_group_id'          => TTi18n::gettext( 'Employee Group' ),
					'-2025-policy_group_id'        => TTi18n::gettext( 'Policy Group' ),
					'-2030-user_title_id'          => TTi18n::gettext( 'Employee Title' ),
					'-2035-user_tag'               => TTi18n::gettext( 'Employee Tags' ),
					'-2040-include_user_id'        => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'        => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-default_branch_id'      => TTi18n::gettext( 'Default Branch' ),
					'-2070-default_department_id'  => TTi18n::gettext( 'Default Department' ),
					'-2080-schedule_branch_id'     => TTi18n::gettext( 'Schedule Branch' ),
					'-2090-schedule_department_id' => TTi18n::gettext( 'Schedule Department' ),
					'-2100-custom_filter'          => TTi18n::gettext( 'Custom Filter' ),

					'-3000-status_id'         => TTi18n::gettext( 'Schedule Status' ),
					'-3100-absence_policy_id' => TTi18n::gettext( 'Absence Policy' ),

					'-5000-columns'    => TTi18n::gettext( 'Display Columns' ),
					'-5010-group'      => TTi18n::gettext( 'Group By' ),
					'-5020-sub_total'  => TTi18n::gettext( 'SubTotal By' ),
					'-5030-sort'       => TTi18n::gettext( 'Sort By' ),
					'-5040-page_break' => TTi18n::gettext( 'Page Break On' ),
				];

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$corporate_edition_setup_fields = [
							'-2075-default_job_id'      => TTi18n::gettext( 'Default Job' ),
							'-2076-default_job_item_id' => TTi18n::gettext( 'Default Task' ),
							'-2077-default_punch_tag_id' => TTi18n::gettext( 'Default Punch Tag' ),

							'-2300-punch_tag_id'          => TTi18n::gettext( 'Punch Tag' ),

							'-2510-job_status_id'  => TTi18n::gettext( 'Job Status' ),
							'-2520-job_group_id'   => TTi18n::gettext( 'Job Group' ),
							'-2530-include_job_id' => TTi18n::gettext( 'Include Job' ),
							'-2540-exclude_job_id' => TTi18n::gettext( 'Exclude Job' ),

							'-2610-job_item_group_id'   => TTi18n::gettext( 'Task Group' ),
							'-2620-include_job_item_id' => TTi18n::gettext( 'Include Task' ),
							'-2630-exclude_job_item_id' => TTi18n::gettext( 'Exclude Task' ),
					];
					$retval = array_merge( $retval, $corporate_edition_setup_fields );
				}
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( null, TTi18n::getText( 'Date' ), 15, true );
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$retval = $this->getCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users'], ['users'] );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'ScheduleSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'ScheduleSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'ScheduleSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'ScheduleSummaryReport', 'custom_column' );
					if ( is_array( $report_static_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_static_custom_column_labels, 9700 );
					}
				}
				break;
			case 'formula_columns':
				$retval = TTMathFormula::formatFormulaColumns( array_merge( array_diff( $this->getOptions( 'static_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) ), $this->getOptions( 'dynamic_columns' ) ) );
				break;
			case 'filter_columns':
				$retval = TTMathFormula::formatFormulaColumns( array_merge( $this->getOptions( 'static_columns' ), $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) );
				break;
			case 'static_columns':
				$retval = [
					//Static Columns - Aggregate functions can't be used on these.
					'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
					'-1001-middle_name'        => TTi18n::gettext( 'Middle Name' ),
					'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
					'-1005-full_name'          => TTi18n::gettext( 'Full Name' ),
					'-1030-employee_number'    => TTi18n::gettext( 'Employee #' ),
					'-1040-status'             => TTi18n::gettext( 'Status' ),
					'-1050-title'              => TTi18n::gettext( 'Title' ),
					'-1055-city'               => TTi18n::gettext( 'City' ),
					'-1060-province'           => TTi18n::gettext( 'Province/State' ),
					'-1070-country'            => TTi18n::gettext( 'Country' ),
					'-1080-user_group'         => TTi18n::gettext( 'Group' ),
					'-1090-default_branch'     => TTi18n::gettext( 'Default Branch' ),
					'-1100-default_department' => TTi18n::gettext( 'Default Department' ),
					'-1110-currency'           => TTi18n::gettext( 'Currency' ),

					'-1200-permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1210-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1220-policy_group'        => TTi18n::gettext( 'Policy Group' ),

					//Handled in date_columns above.
					//'-1230-pay_period' => TTi18n::gettext('Pay Period'),

					'-1290-note' => TTi18n::gettext( 'Employee Note' ),
					'-1295-tag'  => TTi18n::gettext( 'Employee Tags' ),

					'-1600-branch'          => TTi18n::gettext( 'Branch' ),
					'-1610-department'      => TTi18n::gettext( 'Department' ),
					'-1620-schedule_policy' => TTi18n::gettext( 'Schedule Policy' ),
					//'-1630-schedule_type' => TTi18n::gettext('Schedule Type'),
					'-1640-schedule_status' => TTi18n::gettext( 'Schedule Status' ),
					'-1650-absence_policy'  => TTi18n::gettext( 'Absence Policy' ),
					//'-1660-date_stamp' => TTi18n::gettext('Date'),

					'-1690-start_end_time'   => TTi18n::gettext( 'Shift Times (Start & End)' ),

					'-5000-schedule_note' => TTi18n::gettext( 'Note' ),

					'-2000-scheduled_hour_of_day' => TTi18n::gettext( 'Hour Of Day' ),
				];

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$corporate_edition_static_columns = [
						//Static Columns - Aggregate functions can't be used on these.
						'-1101-default_job'      => TTi18n::gettext( 'Default Job' ),
						'-1102-default_job_item' => TTi18n::gettext( 'Default Task' ),

						'-1810-job'                  => TTi18n::gettext( 'Job' ),
						'-1820-job_manual_id'        => TTi18n::gettext( 'Job Code' ),
						'-1830-job_description'      => TTi18n::gettext( 'Job Description' ),
						'-1840-job_status'           => TTi18n::gettext( 'Job Status' ),
						'-1850-job_branch'           => TTi18n::gettext( 'Job Branch' ),
						'-1860-job_department'       => TTi18n::gettext( 'Job Department' ),
						'-1870-job_group'            => TTi18n::gettext( 'Job Group' ),
						'-1910-job_item'             => TTi18n::gettext( 'Task' ),
						'-1920-job_item_manual_id'   => TTi18n::gettext( 'Task Code' ),
						'-1930-job_item_description' => TTi18n::gettext( 'Task Description' ),
						'-1940-job_item_group'       => TTi18n::gettext( 'Task Group' ),
						'-1970-punch_tag'            => TTi18n::gettext( 'Punch Tags' ),
					];
					$retval = array_merge( $retval, $corporate_edition_static_columns );
				}

				$retval = array_merge( $retval, (array)$this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users'], ['users'] ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used

					'-1670-start_time' => TTi18n::gettext( 'Start Time' ),
					'-1680-end_time'   => TTi18n::gettext( 'End Time' ),

					'-2005-scheduled_hour_of_day_total' => TTi18n::gettext( 'Scheduled Employees/Hour' ),

					//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
					'-2010-hourly_rate'                 => TTi18n::gettext( 'Hourly Rate' ),

					'-2100-total_time'                  => TTi18n::gettext( 'Total Time' ),
					'-2110-total_time_wage'             => TTi18n::gettext( 'Total Time Wage' ),
					'-2112-total_time_wage_burden'      => TTi18n::gettext( 'Total Time Wage Burden' ),
					'-2114-total_time_wage_with_burden' => TTi18n::gettext( 'Total Time Wage w/Burden' ),

					'-4000-total_shift' => TTi18n::gettext( 'Total Shifts' ), //Group counter...
				];
				$retval = array_merge( $retval, $this->getDynamicCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users'], ['users'] ) );
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions( 'static_columns' ), $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) );
				ksort( $retval );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = Misc::trimSortPrefix( $this->getOptions( 'columns' ) );
				if ( is_array( $columns ) ) {
					foreach ( $columns as $column => $name ) {
						if ( $column == 'absence_policy' || $column == 'schedule_policy' ) {
							//Make sure these columns aren't formatted as they are strings.
							unset( $retval[$column] );
						} else if ( strpos( $column, 'start_end_time' ) !== false ) {
							$retval[$column] = 'string';
						} else if ( strpos( $column, 'start_time' ) !== false || strpos( $column, 'end_time' ) !== false ) {
							$retval[$column] = 'time';
						} else if ( strpos( $column, '_wage' ) !== false || strpos( $column, '_hourly_rate' ) !== false || strpos( $column, 'hourly_rate' ) !== false ) {
							$retval[$column] = 'currency';
						} else if ( strpos( $column, '_time' ) || strpos( $column, '_policy' ) ) {
							$retval[$column] = 'time_unit';
						} else if ( strpos( $column, 'total_shift' ) !== false ) {
							$retval[$column] = 'numeric';
						}
					}
				}
				$retval['scheduled_hour_of_day'] = 'time';
				$retval['scheduled_hour_of_day_total'] = 'numeric';

				break;
			case 'sub_total_by_metadata':
			case 'grand_total_metadata':
				$retval['aggregate'] = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, '_hourly_rate' ) !== false || strpos( $column, 'hourly_rate' ) !== false || $column == 'scheduled_hour_of_day_total' ) {
									$retval['aggregate'][$column] = 'avg';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'group_by_metadata':
				$retval['aggregate'] = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, '_hourly_rate' ) !== false || strpos( $column, 'hourly_rate' ) !== false ) {
									$retval['aggregate'][$column] = 'avg';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'templates':
				$retval = [
						'-1010-by_employee+work+total_time'                 => TTi18n::gettext( 'Work Time by Employee' ),
						'-1020-by_employee+work+total_time+total_time_wage' => TTi18n::gettext( 'Work Time+Wage by Employee' ),
						'-1030-by_title+work+total_time+total_time_wage'    => TTi18n::gettext( 'Work Time+Wage by Title' ),

						'-1110-by_date_by_full_name+work+total_time+total_time_wage' => TTi18n::gettext( 'Work Time+Wage by Date/Employee' ),
						'-1120-by_full_name_by_date+work+total_time+total_time_wage' => TTi18n::gettext( 'Work Time+Wage by Employee/Date' ),

						'-1210-by_branch+work+total_time+total_time_wage'               => TTi18n::gettext( 'Work Time+Wage by Branch' ),
						'-1220-by_department+work+total_time+total_time_wage'           => TTi18n::gettext( 'Work Time+Wage by Department' ),
						'-1230-by_branch_by_department+work+total_time+total_time_wage' => TTi18n::gettext( 'Work Time+Wage by Branch/Department' ),

						'-1310-by_pay_period+work+total_time+total_time_wage'                         => TTi18n::gettext( 'Work Time+Wage by Pay Period' ),
						'-1320-by_pay_period_by_employee+work+total_time+total_time_wage'             => TTi18n::gettext( 'Work Time+Wage by Pay Period/Employee' ),
						'-1330-by_pay_period_by_branch+work+total_time+total_time_wage'               => TTi18n::gettext( 'Work Time+Wage by Pay Period/Branch' ),
						'-1340-by_pay_period_by_department+work+total_time+total_time_wage'           => TTi18n::gettext( 'Work Time+Wage by Pay Period/Department' ),
						'-1350-by_pay_period_by_branch_by_department+work+total_time+total_time_wage' => TTi18n::gettext( 'Work Time+Wage by Pay Period/Branch/Department' ),

						'-1410-by_employee_by_pay_period+work+total_time+total_time_wage'             => TTi18n::gettext( 'Work Time+Wage by Employee/Pay Period' ),
						'-1420-by_branch_by_pay_period+work+total_time+total_time_wage'               => TTi18n::gettext( 'Work Time+Wage by Branch/Pay Period' ),
						'-1430-by_department_by_pay_period+work+total_time+total_time_wage'           => TTi18n::gettext( 'Work Time+Wage by Department/Pay Period' ),
						'-1440-by_branch_by_department_by_pay_period+work+total_time+total_time_wage' => TTi18n::gettext( 'Work Time+Wage by Branch/Department/Pay Period' ),

						'-1510-by_title_by_start_time+work+total_time+total_time_wage+total_shift' => TTi18n::gettext( 'Work Time+Wage+Total Shifts by Title/Start Time' ),
						'-1520-by_date_by_title+work+total_time+total_time_wage+total_shift'       => TTi18n::gettext( 'Work Time+Wage+Total Shifts by Date/Title' ),

						'-2010-by_employee+absence+total_time'                 => TTi18n::gettext( 'Absence Time by Employee' ),
						'-2020-by_employee+absence+total_time+total_time_wage' => TTi18n::gettext( 'Absence Time+Wage by Employee' ),
						'-2030-by_title+absence+total_time+total_time_wage'    => TTi18n::gettext( 'Absence Time+Wage by Title' ),

						'-2110-by_date_by_full_name+absence+total_time+total_time_wage' => TTi18n::gettext( 'Absence Time+Wage by Date/Employee' ),
						'-2120-by_full_name_by_date+absence+total_time+total_time_wage' => TTi18n::gettext( 'Absence Time+Wage by Employee/Date' ),

						'-2210-by_branch+absence+total_time+total_time_wage'               => TTi18n::gettext( 'Absence Time+Wage by Branch' ),
						'-2220-by_department+absence+total_time+total_time_wage'           => TTi18n::gettext( 'Absence Time+Wage by Department' ),
						'-2230-by_branch_by_department+absence+total_time+total_time_wage' => TTi18n::gettext( 'Absence Time+Wage by Branch/Department' ),

						'-2310-by_pay_period+absence+total_time+total_time_wage'                         => TTi18n::gettext( 'Absence Time+Wage by Pay Period' ),
						'-2320-by_pay_period_by_employee+absence+total_time+total_time_wage'             => TTi18n::gettext( 'Absence Time+Wage by Pay Period/Employee' ),
						'-2330-by_pay_period_by_branch+absence+total_time+total_time_wage'               => TTi18n::gettext( 'Work Time+Wage by Pay Period/Branch' ),
						'-2340-by_pay_period_by_department+absence+total_time+total_time_wage'           => TTi18n::gettext( 'Work Time+Wage by Pay Period/Department' ),
						'-2350-by_pay_period_by_branch_by_department+absence+total_time+total_time_wage' => TTi18n::gettext( 'Work Time+Wage by Pay Period/Branch/Department' ),

						'-2410-by_employee_by_pay_period+absence+total_time+total_time_wage'             => TTi18n::gettext( 'Absence Time+Wage by Employee/Pay Period' ),
						'-2420-by_branch_by_pay_period+absence+total_time+total_time_wage'               => TTi18n::gettext( 'Absence Time+Wage by Branch/Pay Period' ),
						'-2430-by_department_by_pay_period+absence+total_time+total_time_wage'           => TTi18n::gettext( 'Absence Time+Wage by Department/Pay Period' ),
						'-2440-by_branch_by_department_by_pay_period+absence+total_time+total_time_wage' => TTi18n::gettext( 'Absence Time+Wage by Branch/Department/Pay Period' ),


				];

				if ( is_object( $this->getUserObject()->getCompanyObject() ) && $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$retval['-5000-by_date_by_scheduled_hour_of_day'] = TTi18n::gettext( 'Total Employees Scheduled by Date/Hour of Day' );
					$retval['-5010-by_date_dow_by_scheduled_hour_of_day'] = TTi18n::gettext( 'Total Employees Scheduled by Day of Week/Hour of Day' );
				}

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						//case 'by_employee+actual_time':
						//	break;
						default:
							Debug::Text( ' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode( '+', $template );
							if ( is_array( $template_keywords ) ) {
								foreach ( $template_keywords as $template_keyword ) {
									Debug::Text( ' Keyword: ' . $template_keyword, __FILE__, __LINE__, __METHOD__, 10 );

									switch ( $template_keyword ) {
										//Columns
										case 'total_time':
											$retval['columns'][] = 'total_time';
											break;
										case 'total_time_wage':
											$retval['columns'][] = 'total_time_wage';
											break;
										case 'absence_policy':
											$retval['columns'][] = 'absence_policy';
											break;
										//Filter
										case 'work':
											$retval['filter']['status_id'] = [ 10 ];
											break;
										case 'absence':
											$retval['filter']['status_id'] = [ 20 ];
											break;
										case 'total_shift':
											$retval['columns'][] = 'total_shift';
											break;

										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'last_name';
											$retval['group'][] = 'first_name';

											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											break;
										case 'by_title':
											$retval['columns'][] = 'title';

											$retval['group'][] = 'title';

											$retval['sort'][] = [ 'title' => 'asc' ];
											break;
										case 'by_branch':
											$retval['columns'][] = 'branch';

											$retval['group'][] = 'branch';

											$retval['sort'][] = [ 'branch' => 'asc' ];
											break;
										case 'by_department':
											$retval['columns'][] = 'department';

											$retval['group'][] = 'department';

											$retval['sort'][] = [ 'department' => 'asc' ];
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = [ 'branch' => 'asc' ];
											$retval['sort'][] = [ 'department' => 'asc' ];
											break;
										case 'by_pay_period':
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'pay_period';

											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											break;
										case 'by_pay_period_by_employee':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											break;
										case 'by_pay_period_by_branch':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											$retval['sort'][] = [ 'branch' => 'asc' ];
											break;
										case 'by_pay_period_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											$retval['sort'][] = [ 'department' => 'asc' ];
											break;
										case 'by_pay_period_by_branch_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';

											$retval['sub_total'][] = 'pay_period';
											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											$retval['sort'][] = [ 'branch' => 'asc' ];
											$retval['sort'][] = [ 'department' => 'asc' ];
											break;
										case 'by_employee_by_pay_period':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = [ 'full_name' => 'asc' ];
											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											break;
										case 'by_branch_by_pay_period':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch';

											$retval['sort'][] = [ 'branch' => 'asc' ];
											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											break;
										case 'by_department_by_pay_period':
											$retval['columns'][] = 'department';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'department';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'department';

											$retval['sort'][] = [ 'department' => 'asc' ];
											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											break;
										case 'by_branch_by_department_by_pay_period':
											$retval['columns'][] = 'branch';
											$retval['columns'][] = 'department';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch';
											$retval['group'][] = 'department';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch';
											$retval['sub_total'][] = 'department';

											$retval['sort'][] = [ 'branch' => 'asc' ];
											$retval['sort'][] = [ 'department' => 'asc' ];
											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											break;
										case 'by_date_by_full_name':
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'full_name';

											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'full_name';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = [ 'date_stamp' => 'asc' ];
											$retval['sort'][] = [ 'full_name' => 'asc' ];
											break;
										case 'by_date_by_title':
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'title';

											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'title';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = [ 'date_stamp' => 'asc' ];
											$retval['sort'][] = [ 'title' => 'asc' ];
											break;
										case 'by_full_name_by_date':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'date_stamp';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'date_stamp';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = [ 'full_name' => 'asc' ];
											$retval['sort'][] = [ 'date_stamp' => 'asc' ];
											break;
										case 'by_title_by_start_time':
											$retval['columns'][] = 'title';
											$retval['columns'][] = 'start_time';
											$retval['columns'][] = 'total_shift';

											$retval['group'][] = 'title';
											$retval['group'][] = 'start_time';

											$retval['sub_total'][] = 'title';

											$retval['sort'][] = [ 'title' => 'asc' ];
											$retval['sort'][] = [ 'start_time' => 'asc' ];
											break;

										case 'by_date_by_scheduled_hour_of_day':
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'scheduled_hour_of_day';
											$retval['columns'][] = 'scheduled_hour_of_day_total';

											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'scheduled_hour_of_day';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = [ 'date_stamp' => 'asc' ];
											$retval['sort'][] = [ 'scheduled_hour_of_day' => 'asc' ];
											break;
										case 'by_date_dow_by_scheduled_hour_of_day':
											$retval['columns'][] = 'date_dow';
											$retval['columns'][] = 'scheduled_hour_of_day';
											$retval['columns'][] = 'scheduled_hour_of_day_total';

											$retval['group'][] = 'date_dow';
											$retval['group'][] = 'scheduled_hour_of_day';

											$retval['sub_total'][] = 'date_dow';

											$retval['sort'][] = [ 'date_dow' => 'asc' ];
											$retval['sort'][] = [ 'scheduled_hour_of_day' => 'asc' ];
											break;
									}
								}
							}
							break;
					}
				}

				//Set the template dropdown as well.
				$retval['-1000-template'] = $template;

				//Add sort prefixes so Flex can maintain order.
				if ( isset( $retval['filter'] ) ) {
					$retval['-5000-filter'] = $retval['filter'];
					unset( $retval['filter'] );
				}
				if ( isset( $retval['columns'] ) ) {
					$retval['-5010-columns'] = $retval['columns'];
					unset( $retval['columns'] );
				}
				if ( isset( $retval['group'] ) ) {
					$retval['-5020-group'] = $retval['group'];
					unset( $retval['group'] );
				}
				if ( isset( $retval['sub_total'] ) ) {
					$retval['-5030-sub_total'] = $retval['sub_total'];
					unset( $retval['sub_total'] );
				}
				if ( isset( $retval['sort'] ) ) {
					$retval['-5040-sort'] = $retval['sort'];
					unset( $retval['sort'] );
				}
				Debug::Arr( $retval, ' Template Config for: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );

				break;
			default:
				//Call report parent class options function for options valid for all reports.
				$retval = $this->__getOptions( $name );
				break;
		}

		return $retval;
	}

	/**
	 * This function takes worked time for a single day and multiplies it by each hour worked.
	 * @param $row
	 * @param $dynamic_columns
	 * @return array
	 */
	function splitDataByHoursWorked( $row, $dynamic_columns ) {
		$retval = [];
		if ( isset( $row['raw_start_time'] ) && isset( $row['raw_end_time'] ) && $row['raw_start_time'] > 0 && $row['raw_end_time'] > 0 ) {
			$total_hour_rows = ceil( ( TTDate::roundTime( $row['raw_end_time'], 3600, 30 ) - TTDate::roundTime( $row['raw_start_time'], 3600, 10 ) ) / 3600 );
			if ( $total_hour_rows == 0 ) {
				$total_hour_rows = 1;
			}

			$raw_start_time = TTDate::roundTime( $row['raw_start_time'], 3600, 10 );
			//If the employee punches out exact at 5:00PM, minus 1 second from that time so its recorded as an hour for 4:00PM and not 5:00PM.
			$raw_end_time = TTDate::roundTime( ( $row['raw_end_time'] - 1 ), 3600, 10 );

			//Debug::Text('Total Hours: '. $total_hour_rows .' Start Time: '. TTDate::getDATE('DATE+TIME', $raw_start_time ) .' End Time: '. TTDate::getDATE('DATE+TIME', $raw_end_time ), __FILE__, __LINE__, __METHOD__, 10);
			$x = 0;
			for ( $i = $raw_start_time; $i <= $raw_end_time; $i += 3600 ) {
				//Debug::Text('Hour: '. TTDate::getDate('DATE+TIME', $i ) .'('. $i .') Total Hour Rows: '. $total_hour_rows, __FILE__, __LINE__, __METHOD__, 10);
				$retval[$i]['scheduled_hour_of_day'] = $i;

				//FIXME: Since we don't know exactly when meals and breaks are taken, its makes almost impossible to properly break down the time scheduled per hour.
				// If we do it like below (similar to TimeSheetDetailReport), it will show more hours than they are actually scheduled.
				// The only real hope is to somehow add up all meal/break time and take it off in the middle of the day. Or wait until we can somehow schedule actual start/end times for meals/breaks.
//				if ( $row['raw_start_time'] > $i AND ( $row['raw_start_time'] - $i ) < 3600 ) {
//					$partial_hour = 3600 - ( $row['raw_start_time'] - $i );
//				} elseif( $row['raw_end_time'] > $i AND ( $row['raw_end_time'] - $i ) < 3600 ) {
//					$partial_hour = ( $row['raw_end_time'] - $i );
//				} else {
//					$partial_hour = 3600;
//				}

				$retval[$i]['scheduled_hour_of_day_total'] = 1.00;

				foreach ( $row as $column => $value ) {
					if ( isset( $dynamic_columns[$column] ) && is_numeric( $value ) && !in_array( $column, [ 'raw_start_time', 'raw_end_time' ] ) ) {
						$retval[$i][$column] = ( $value / $total_hour_rows );

//						if ( $column == 'total_time' ) {
//							$retval[ $i ][ $column ] = $partial_hour;
//						} elseif ( $column == 'total_time_wage' ) {
//							$retval[ $i ][ $column ] = Misc::MoneyRound( TTMath::mul( TTDate::getHours( $partial_hour ), $row['hourly_rate'] ) );
//						} elseif ( $column == 'total_time_wage_burden' ) {
//							$retval[ $i ][ $column ] = Misc::MoneyRound( TTMath::mul( TTDate::getHours( $partial_hour ), TTMath::mul( $row['hourly_rate'], TTMath::div( $row['labor_burden_percent'], 100 ) ) ) );
//						} elseif ( $column == 'total_time_wage_with_burden' ) {
//							$retval[ $i ][ $column ] = Misc::MoneyRound( TTMath::mul( TTDate::getHours( $partial_hour ), TTMath::mul( $row['hourly_rate'], TTMath::add( TTMath::div( $row['labor_burden_percent'], 100 ), 1) ) ) );
//						} else {
//							$retval[ $i ][ $column ] = ( $value / $total_hour_rows ); //Since we aggreate the user_date_total rows to min/max punch time, for anything other than worked time we can just divide it up between the min/max punch time for that row.
//						}
					} else {
						$retval[$i][$column] = $value;
					}
				}

				$x++;
			}
		}

		if ( !isset( $retval ) ) {
			$retval[0] = $row;
		}

		return $retval;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'schedule' => [], 'user' => [], 'total_shift' => [] ];

		$filter_data = $this->getFilterConfig();

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'schedule', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$wage_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
			$job_status_options = $jlf->getOptions( 'status' );
		} else {
			$job_status_options = [];
		}

		//If we don't have permissions to view open shifts, exclude user_id = 0;
		if ( $this->getPermissionObject()->Check( 'schedule', 'view_open' ) == false ) {
			$filter_data['exclude_user_id'] = [ TTUUID::getZeroID() ];
		}

		if ( strpos( $format, 'schedule' ) === false ) { //Avoid running these queries when printing out the schedule.
			$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
			$this->setMemoryPerRow( max( 5000, ( 349 * count( $this->getColumnDataConfig() ) ) ) );  //MAX: 29K for each row @ 83 columns = 349 bytes per column, per row. 5000 bytes per row at 1 column.
			$slf->getSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, $this->getMemoryBasedRowLimit(), null, null, [ 'last_name' => 'asc' ] ); //Sort by last name mainly for the PDF schedule for printing.
			Debug::Text( ' Total Rows: ' . $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $slf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
			if ( $slf->getRecordCount() > 0 ) {
				if ( $this->isMemoryBasedRowLimitValid( $slf->getRecordCount() ) == false ) {
					$this->setMaximumRowsExceeded( true ); //Alert the user that the maximum number of rows was exceeded, but still generate the report with the data we have.
					if ( $this->handleMaximumRowsExceeded( $format ) == false ) {
						return false;
					}
				}

				foreach ( $slf as $key => $s_obj ) { /** @var ScheduleFactory $s_obj */
					$enable_wages = $this->getPermissionObject()->isPermissionChild( $s_obj->getUser(), $wage_permission_children_ids );

					$hourly_rate = 0;
					if ( $enable_wages ) {
						$hourly_rate = $s_obj->getColumn( 'user_wage_hourly_rate' );
					}

					$date_stamp_epoch = $s_obj->getDateStamp();

					$shift_arr = [
							'user_id'              => $s_obj->getUser(),
							'status_id'            => $s_obj->getColumn( 'status_id' ),
							'group'                => $s_obj->getColumn( 'group' ),
							'default_branch'       => $s_obj->getColumn( 'default_branch' ),
							'default_department'   => $s_obj->getColumn( 'default_department' ),
							'default_job'          => $s_obj->getColumn( 'default_job' ),
							'default_job_item'     => $s_obj->getColumn( 'default_job_item' ),
							'branch'               => $s_obj->getColumn( 'branch' ),
							'department'           => $s_obj->getColumn( 'department' ),
							'job'                  => $s_obj->getColumn( 'job' ),
							'job_status_id'        => $s_obj->getColumn( 'job_status_id' ),
							'job_status'           => Option::getByKey( $s_obj->getColumn( 'job_status_id' ), $job_status_options, null ),
							'job_manual_id'        => $s_obj->getColumn( 'job_manual_id' ),
							'job_description'      => $s_obj->getColumn( 'job_description' ),
							'job_branch'           => $s_obj->getColumn( 'job_branch' ),
							'job_department'       => $s_obj->getColumn( 'job_department' ),
							'job_group'            => $s_obj->getColumn( 'job_group' ),
							'job_item'             => $s_obj->getColumn( 'job_item' ),
							'job_item_manual_id'   => $s_obj->getColumn( 'job_item_manual_id' ),
							'job_item_description' => $s_obj->getColumn( 'job_item_description' ),
							'job_item_group'       => $s_obj->getColumn( 'job_item_group' ),
							'punch_tag'            => ( ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) ? $s_obj->getPunchTagDisplay() : null ), //Prevent calling when incorrect edition.
							'quantity'             => $s_obj->getColumn( 'quantity' ),
							'bad_quantity'         => $s_obj->getColumn( 'bad_quantity' ),

							'total_time'                  => $s_obj->getColumn( 'total_time' ),
							'total_time_wage'             => TTMath::MoneyRound( TTMath::mul( TTDate::getHours( $s_obj->getColumn( 'total_time' ) ), $hourly_rate ) ), //This is also calculated in: splitDataByHoursWorked()
							'total_time_wage_burden'      => TTMath::MoneyRound( TTMath::mul( TTDate::getHours( $s_obj->getColumn( 'total_time' ) ), TTMath::mul( $hourly_rate, TTMath::div( $s_obj->getColumn( 'user_labor_burden_percent' ), 100 ) ) ) ), //This is also calculated in: splitDataByHoursWorked()
							'total_time_wage_with_burden' => TTMath::MoneyRound( TTMath::mul( TTDate::getHours( $s_obj->getColumn( 'total_time' ) ), TTMath::mul( $hourly_rate, TTMath::add( TTMath::div( $s_obj->getColumn( 'user_labor_burden_percent' ), 100 ), 1 ) ) ) ), //This is also calculated in: splitDataByHoursWorked()

							'date_stamp' => $date_stamp_epoch,

							'schedule_policy' => $s_obj->getColumn( 'schedule_policy' ),
							'absence_policy'  => $s_obj->getColumn( 'absence_policy' ),

							//'schedule_type' => Option::getByKey( $s_obj->getType(), $s_obj->getOptions('type'), NULL ), //Recurring/Scheduled?
							'schedule_status' => Option::getByKey( $s_obj->getStatus(), $s_obj->getOptions( 'status' ), null ),

							'raw_start_time'      => TTDate::strtotime( $s_obj->getColumn( 'start_time' ) ), //This is required in: splitDataByHoursWorked()
							'raw_end_time'        => TTDate::strtotime( $s_obj->getColumn( 'end_time' ) ), //This is required in: splitDataByHoursWorked()

							//Normalize the timestamps to the same day, otherwise min/max aggregates will always use what times are on the first/last days.
							//						'start_time' => TTDate::strtotime( $s_obj->getColumn('start_time') ),
							//						'end_time' => TTDate::strtotime( $s_obj->getColumn('end_time') ),
							'start_time'      => TTDate::getTimeLockedDate( TTDate::strtotime( $s_obj->getColumn( 'start_time' ) ), 86400 ), //This is required in: splitDataByHoursWorked()
							'end_time'        => TTDate::getTimeLockedDate( TTDate::strtotime( $s_obj->getColumn( 'end_time' ) ), 86400 ), //This is required in: splitDataByHoursWorked()
							'start_end_time'  => [ 'sort' => TTDate::getTimeLockedDate( TTDate::strtotime( $s_obj->getColumn( 'start_time' ) ), 86400 ) . TTDate::getTimeLockedDate( TTDate::strtotime( $s_obj->getColumn( 'end_time' ) ), 86400 ), 'display' => TTDate::getDate( 'TIME', TTDate::strtotime( $s_obj->getColumn( 'start_time' ) ) ) . ' - '. TTDate::getDate( 'TIME',  TTDate::strtotime( $s_obj->getColumn( 'end_time' ) ) ) ],

							'user_wage_id'         => $s_obj->getColumn( 'user_wage_id' ),
							'hourly_rate'          => TTMath::MoneyRound( $hourly_rate ), //This is required in: splitDataByHoursWorked()
							'labor_burden_percent' => $s_obj->getColumn( 'user_labor_burden_percent' ), //This is required in: splitDataByHoursWorked()

							'pay_period_start_date'       => strtotime( $s_obj->getColumn( 'pay_period_start_date' ) ),
							'pay_period_end_date'         => strtotime( $s_obj->getColumn( 'pay_period_end_date' ) ),
							'pay_period_transaction_date' => strtotime( $s_obj->getColumn( 'pay_period_transaction_date' ) ),
							'pay_period'                  => strtotime( $s_obj->getColumn( 'pay_period_transaction_date' ) ),
							'pay_period_id'               => $s_obj->getColumn( 'pay_period_id' ),

							'schedule_note' => $s_obj->getColumn( 'note' ),

							'total_shift' => 1,
					];
					unset( $hourly_rate );

					$job_custom_fields = json_decode( $s_obj->getColumn( 'job_custom_field' ), true );

					if ( is_array( $job_custom_fields ) && count( $job_custom_fields ) > 0 ) {
						foreach ( $job_custom_fields as $custom_field_id => $custom_field ) {
							$shift_arr['job_custom_field-' . $custom_field_id] = $custom_field;
						}
					}

					$this->tmp_data['schedule'][$s_obj->getUser()][] = $shift_arr;

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				}
			}
			//Debug::Arr($this->tmp_data['schedule'], 'Schedule Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($this->form_data, 'Schedule Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		unset( $filter_data['status_id'] ); //This is for schedule status, not user status.

		//If we're printing the schedule, make sure we include the required columns.
		if ( in_array( $format, $this->special_output_format ) ) {
			$this->config['columns_data']['first_name'] = true;
			$this->config['columns_data']['last_name'] = true;
		}

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = $this->form_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Add OPEN user to the list so it can printed on schedules.
		$this->tmp_data['user'][TTUUID::getZeroID()] = $this->form_data['user'][TTUUID::getZeroID()] = [
				'first_name' => TTi18n::getText( 'OPEN' ),
				'last_name'  => '',
		];

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @param null $format
	 * @return bool
	 */
	function _preProcess( $format = null ) {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['schedule'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

		$columns = $this->getColumnDataConfig();
		$dynamic_columns = Misc::trimSortPrefix( $this->getOptions( 'dynamic_columns' ) );

		$split_data_by_hours_worked = false;
		if ( strpos( $format, 'pdf_' ) === false && isset( $columns['scheduled_hour_of_day'] ) ) {
			$split_data_by_hours_worked = true;
		}
		unset( $columns );

		//Merge time data with user data
		//$key = 0;
		if ( isset( $this->tmp_data['schedule'] ) ) {
			$column_keys = array_keys( $this->getColumnDataConfig() );

			foreach ( $this->tmp_data['schedule'] as $user_id => $level_1 ) {
				if ( isset( $this->tmp_data['user'][$user_id] ) ) {
					foreach ( $level_1 as $key => $level_2 ) {
						if ( $split_data_by_hours_worked == true ) {
							$level_3 = $this->splitDataByHoursWorked( $level_2, $dynamic_columns );
						} else {
							$level_3[0] = $level_2;
						}

						foreach ( $level_3 as $row ) {
							$date_columns = TTDate::getReportDates( null, $row['date_stamp'], false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ], $column_keys );
							$processed_data = [//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
							];

							$this->data[] = array_merge( $row, $this->tmp_data['user'][$user_id], $date_columns, $processed_data );

							$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
							//$key++;
						}
					}
				}
			}

			unset( $this->tmp_data, $row, $date_columns, $processed_data, $level_1 );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @param null $branch
	 * @param null $department
	 * @param null $job
	 * @param null $job_item
	 * @param null $user
	 * @param bool $new_page
	 * @return bool
	 */
	function scheduleHeader( $branch = null, $department = null, $job = null, $job_item = null, $user = null, $new_page = true ) {

		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 16 ) );
		if ( $new_page == true ) {
			$this->pdf->Cell( 0, $this->_pdf_scaleSize( 5 ), TTi18n::getText( 'Schedule' ), 0, 0, 'C' );
			$this->pdf->Ln( $this->_pdf_scaleSize( 6 ) );
		}

		$config = $this->getFilterConfig();
		if ( $new_page == true && isset( $config['start_date'] ) && isset( $config['end_date'] ) ) {
			$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 6 ) );
			$this->pdf->setY( $this->pdf->getY() + 4 );
			$this->pdf->Cell( 0, $this->_pdf_fontSize( 3 ), TTDate::getDate( 'DATE', $config['start_date'] ) . ' - ' . TTDate::getDate( 'DATE', $config['end_date'] ), 0, 0, 'C' );
			$this->pdf->Ln();
		}
		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 16 ) );

		$label = [];
		if ( $branch !== 0 && $branch != '' ) { //This is a name, not a INT or UUID.
			$label[] = TTi18n::getText( 'Branch' ) . ': ' . $branch;
		}
		if ( $department !== 0 && $department != '' ) { //This is a name, not a INT or UUID.
			$label[] = TTi18n::getText( 'Department' ) . ': ' . $department;
		} else {
			if ( $branch !== 0 && $branch != '' ) { //This is a name, not a INT or UUID.
				$label[] = TTi18n::getText( 'Department' ) . ': N/A';
			}
		}

		if ( $job !== 0 && $job != '' ) { //This is a name, not a INT or UUID.
			$label[] = TTi18n::getText( 'Job' ) . ': ' . $job;
		}
		if ( $job_item !== 0 && $job_item != '' ) { //This is a name, not a INT or UUID.
			$label[] = TTi18n::getText( 'Task' ) . ': ' . $job_item;
		} else {
			if ( $job !== 0 && $job != '' ) { //This is a name, not a INT or UUID.
				$label[] = TTi18n::getText( 'Task' ) . ': N/A';
			}
		}

		if ( $user !== 0 && $user != '' ) { //This is a name, not a INT or UUID.
			$label[] = TTi18n::getText( 'Employee' ) . ': ' . $user;
		}

		//Debug::Arr($label, 'Label: Branch: '. $branch .' Department: '. $department .' New Page: '. (int)$new_page, __FILE__, __LINE__, __METHOD__, 10);

		if ( count( $label ) > 0 ) {
			if ( $new_page == false ) {
				$this->pdf->setY( $this->pdf->getY() + 2 );
			}
			$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );
			$this->pdf->Cell( 0, $this->_pdf_scaleSize( 4 ), implode( str_repeat( ' ', 24 ), $label ), 0, 0, 'C', 0, null, 1 );
			$this->pdf->Ln();
		} else {
			$this->pdf->Ln( $this->_pdf_scaleSize( 2 ) );
		}


		return true;
	}

	/**
	 * @return bool
	 */
	function scheduleFooter() {
		$margins = $this->pdf->getMargins();

		//Don't scale footer text lines as they aren't that important anyways.
		$this->pdf->SetFont( $this->config['other']['default_font'], '', 8 );

		//Save x, y and restore after footer is set.
		$x = $this->pdf->getX();
		$y = $this->pdf->getY();

		//Jump to end of page.
		if ( ( $this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 15 ) > $y ) {
			$this->pdf->setY( ( $this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 15 ) );
		}

		$company_obj = $this->getUserObject()->getCompanyObject();
		if ( is_object( $company_obj ) && $company_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$url = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getBaseURL() . 'ical/ical.php';

			$this->pdf->Cell( ( $this->pdf->getPageWidth() - $margins['right'] - $margins['left'] ), 5, TTi18n::getText( 'Synchronize this schedule to your desktop/mobile phone calendar application' ) . ': ' . $url, 1, 0, 'C', 0, null, 1 );
			$this->pdf->Ln();
		}

		$this->pdf->SetFont( $this->config['other']['default_font'], '', 8 );
		$this->pdf->Cell( ( $this->pdf->getPageWidth() - $margins['right'] ), 5, TTi18n::getText( 'Page' ) . ' ' . $this->pdf->PageNo() . ' of ' . $this->pdf->getAliasNbPages(), 0, 0, 'C', 0 );
		$this->pdf->Ln();

		$this->pdf->SetFont( $this->config['other']['default_font'], '', 6 );
		$this->pdf->Cell( ( $this->pdf->getPageWidth() - $margins['right'] ), 5, TTi18n::gettext( 'Report Generated By' ) . ' ' . APPLICATION_NAME . ' v' . APPLICATION_VERSION . ' @ ' . TTDate::getDate( 'DATE+TIME', $this->start_time ), 0, 0, 'C', 0 );

		$this->pdf->setX( $x );
		$this->pdf->setY( $y );

		return true;
	}

	/**
	 * @return bool
	 */
	function scheduleNoData() {
		$this->pdf->AddPage();
		$this->pdf->Ln( 50 );
		$this->pdf->setTextColor( 255, 0, 0 );
		$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 18 ) );
		$this->pdf->Cell( $this->pdf->getPageWidth(), $this->_pdf_scaleSize( 10 ), TTi18n::getText( 'NO DATA MATCHES CRITERIA' ), 0, 0, 'C', 0, '', 1 );
		$this->pdf->Ln( 100 );

		return true;
	}

	/**
	 * @return bool
	 */
	function scheduleAddPage() {
		$this->scheduleFooterWeek();
		$this->scheduleFooter();
		$this->pdf->AddPage();

		return true;
	}

	/**
	 * @param $height
	 * @param bool $add_page
	 * @return bool
	 */
	function scheduleCheckPageBreak( $height, $add_page = true ) {
		$margins = $this->pdf->getMargins();

		if ( ( $this->pdf->getY() + $height ) > ( $this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 10 ) ) {
			//Debug::Text('Detected Page Break needed... Y: '. $this->pdf->getY() .' Height: '. $height .' Page Height: '. ($this->pdf->getPageHeight() - $margins['bottom'] - $margins['top'] - 10), __FILE__, __LINE__, __METHOD__, 10);
			$this->scheduleAddPage();

			return true;
		}

		return false;
	}

	/**
	 * @param $start_week_day
	 * @param array $column_widths
	 * @param $format
	 * @return bool
	 */
	function scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format ) {
		if ( isset( $column_widths['day'] ) ) {
			$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );

			if ( $format != 'pdf_schedule' && $format != 'pdf_schedule_print' ) {
				$this->pdf->Cell( $column_widths['label'], $this->_pdf_scaleSize( 4 ), TTi18n::getText( 'Employee' ), 1, 0, 'C', 0, null, 1 );
			}

			$calendar_header = TTDate::getDayOfWeekArrayByStartWeekDay( $start_week_day );
			foreach ( $calendar_header as $header_name ) {
				$this->pdf->Cell( $column_widths['day'], $this->_pdf_scaleSize( 4 ), $header_name, 1, 0, 'C', 0, null, 1 );
			}

			$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 8 ) );

			$this->pdf->Ln();

			return true;
		}

		return false;
	}

	/**
	 * @param $calendar_array
	 * @param array $column_widths
	 * @param $format
	 * @param bool $new_page
	 * @return bool
	 */
	function scheduleWeekHeader( $calendar_array, $column_widths, $format, $new_page = false ) {
		if ( is_array( $calendar_array ) && isset( $column_widths['day'] ) ) {
			$this->pdf->setFillColor( 220, 220, 220 );

			$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );

			if ( $format != 'pdf_schedule' && $format != 'pdf_schedule_print' ) {
				$this->pdf->Cell( $column_widths['label'], $this->_pdf_scaleSize( 4 ), '', 1, 0, 'C', 1 );
			}
			$i = 0;
			foreach ( $calendar_array as $calendar_day ) {
				if ( ( $i == 0 && $new_page == true ) || $calendar_day['is_new_month'] == true ) {
					$this->pdf->Cell( ( $column_widths['day'] * 0.75 ), $this->_pdf_scaleSize( 4 ), $calendar_day['month_name'], 'TBL', 0, 'L', 1, null, 1 );
				} else {
					$this->pdf->Cell( ( $column_widths['day'] * 0.75 ), $this->_pdf_scaleSize( 4 ), '', 'TBL', 0, 'L', 1 );
				}
				$this->pdf->Cell( ( $column_widths['day'] * 0.25 ), $this->_pdf_scaleSize( 4 ), $calendar_day['day_of_month'], 'TBR', 0, 'R', 1, null, 1 );

				$i++;
			}

			$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 8 ) );

			$this->pdf->Ln();

			$this->pdf->setFillColor( 255, 255, 255 );

			return true;
		}

		return false;
	}

	/**
	 * @param $schedule_data
	 * @param $calendar_array
	 * @param $start_week_day
	 * @param array $column_widths
	 * @param $format
	 * @param int $row
	 * @return bool
	 */
	function scheduleUserWeek( $schedule_data, $calendar_array, $start_week_day, $column_widths, $format, $row = 0 ) {
		if ( is_array( $calendar_array ) && isset( $column_widths['day'] ) && is_array( $schedule_data ) && count( $schedule_data ) > 0 ) {
			if ( ( $row % 2 ) == 0 ) {
				$row_bg_color_arr = [ 255, 255, 255 ];
			} else {
				$row_bg_color_arr = [ 240, 240, 240 ];
			}

			//Check to see if the employee is scheduled at all this week.
			switch ( $format ) {
				case 'pdf_schedule_group':
				case 'pdf_schedule_group_print':
				case 'pdf_schedule_group_pagebreak':
				case 'pdf_schedule_group_pagebreak_print':
					//
					// Group - Separate (branch/department on their own pages)
					//
					$s = 0;
					$max_shifts_per_day = 0;
					foreach ( $calendar_array as $calendar_day ) {
						if ( isset( $schedule_data[$calendar_day['date_stamp']] ) ) {
							$shifts_per_day = count( $schedule_data[$calendar_day['date_stamp']] );
							if ( $shifts_per_day > $max_shifts_per_day ) {
								$max_shifts_per_day = $shifts_per_day;
							}
							$s++;
						}
					}

					if ( $s > 0 ) {
						$schedule_key = key( $schedule_data );
						$user_id = $schedule_data[$schedule_key][0]['user_id'];
						if ( isset( $this->form_data['user'][$user_id] ) ) {
							$user_data = $this->form_data['user'][$user_id];

							$i = 0;
							$row_height = $this->_pdf_fontSize( 5 );
							$max_row_height = ( $row_height * $max_shifts_per_day );

							foreach ( $calendar_array as $calendar_day ) {
								$date_stamp = $calendar_day['date_stamp'];

								//Handle split shifts in the same day, count how many shifts exist.
								//Debug::Text('User ID: '. $user_id .' Date: '. $date_stamp .' Total Shifts: '. $max_shifts_per_day .' Height: '. $max_row_height .' Max Shifts: '. $max_shifts_per_day, __FILE__, __LINE__, __METHOD__, 10);
								if ( $i == 0 ) {
									$row_top_y = $this->pdf->getY();
									$row_bottom_y = ( $this->pdf->getY() + $max_row_height );
									$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );
									$this->pdf->Cell( $column_widths['label'], $max_row_height, $user_data['first_name'] . ' ' . $user_data['last_name'], 'LR', 0, 'C', true, null, 1 );
									$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 8 ) );
								}

								if ( isset( $schedule_data[$date_stamp] ) ) {

									$shifts_per_day = 0;
									foreach ( $schedule_data[$date_stamp] as $schedule_data_shift ) {
										if ( isset( $schedule_data_shift['status_id'] ) && $schedule_data_shift['status_id'] == 20 ) {
											$this->pdf->setTextColor( 255, 0, 0 );
											$this->pdf->Cell( $column_widths['day'], $row_height, ( $schedule_data_shift['absence_policy'] != '' ) ? $schedule_data_shift['absence_policy'] : TTi18n::getText( 'N/A' ), 'LR', 2, 'C', true, null, 1 );
											$this->pdf->setTextColor( 0, 0, 0 );
										} else {
											$this->pdf->Cell( $column_widths['day'], $row_height, $schedule_data_shift['start_time'] . ' - ' . $schedule_data_shift['end_time'], 'LR', 2, 'C', true, null, 1 );
										}
										$shifts_per_day++;
									}

									if ( $shifts_per_day < $max_shifts_per_day ) {
										$this->pdf->Cell( $column_widths['day'], ( ( $max_shifts_per_day - $shifts_per_day ) * $row_height ), '', 'LR', 2, 'C', 1 );
									}
								} else {
									//Debug::Text('	  No Shifts: User ID: '. $user_id .' Date: '. $date_stamp .' Height: '. $max_row_height, __FILE__, __LINE__, __METHOD__, 10);
									$this->pdf->Cell( $column_widths['day'], $max_row_height, '', 'LR', 2, 'C', 1 );
								}

								$this->pdf->setXY( ( $this->pdf->getX() + $column_widths['day'] ), $row_top_y );
								$i++;
							}

							$this->pdf->setY( $row_bottom_y ); //Last shift, set Y to end of cell so horizontal line can be drawn in the correct place.
						}

						return true;
					}
					break;
				case 'pdf_schedule':
				case 'pdf_schedule_print':
				case 'pdf_schedule_group_combined':
				case 'pdf_schedule_group_combined_print':
					//
					// Group - Combined Branch/Departments
					//
					$user_id = false;
					$s = 0;
					$max_lines_per_day = 0;

					//Keep track of unique branch/departments, if it exceeds 1 then always display them
					//**This needs to be done across the entire week, as someone could have the first shift in the default branch, and the last shift in another branch
					//  and the lines_per_day count would be wrong until it gets to the last branch.
					$unique_branch = [];
					$unique_department = [];
					$unique_job_item = [];
					$unique_job = [];
					foreach ( $calendar_array as $calendar_day ) {
						$date_stamp = $calendar_day['date_stamp'];
						if ( isset( $schedule_data[$date_stamp] ) ) {
							$unique_branch = ( $unique_branch + array_flip( array_keys( $schedule_data[$date_stamp] ) ) ); //Don't use array_merge here, as it breaks due to integer keys not being overwritten but combined/added.
							foreach ( $schedule_data[$date_stamp] as $branch => $level_2 ) {
								$unique_department = ( $unique_department + array_flip( array_keys( $level_2 ) ) );
								foreach ( $level_2 as $department => $level_3 ) {
									$unique_job = ( $unique_job + array_flip( array_keys( $level_3 ) ) );
									foreach ( $level_3 as $job => $level_4 ) {
										$unique_job_item = ( $unique_job_item + array_flip( array_keys( $level_4 ) ) );
									}
								}
							}
						}
					}
					unset( $date_stamp );

					//Loop over all shifts again to count the lines_per_day.
					foreach ( $calendar_array as $calendar_day ) {
						$date_stamp = $calendar_day['date_stamp'];
						$lines_per_day = 0;
						if ( isset( $schedule_data[$date_stamp] ) ) {
							$lines_per_day += count( $schedule_data[$date_stamp] ); //This only counts the immediate number of branches
							foreach ( $schedule_data[$date_stamp] as $branch => $level_2 ) {
								$lines_per_day += count( $level_2 );
								foreach ( $level_2 as $department => $level_3 ) {
									$lines_per_day += count( $level_3 );
									foreach ( $level_3 as $job => $level_4 ) {
										$lines_per_day += count( $level_4 );
										foreach ( $level_4 as $job_item => $level_5 ) {
											$lines_per_day += count( $level_5 );
											if ( $user_id == false && isset( $level_5[0]['user_id'] ) ) {
												$user_id = $level_5[0]['user_id'];
											}

											if ( ( count( $unique_job_item ) == 1 && isset( $level_5[0]['default_job_item'] ) && $job_item == $level_5[0]['default_job_item'] ) ) {
												$lines_per_day--;
											}

											//Debug::Text('aLines Per Day: '. $lines_per_day .' Max: '. $max_lines_per_day .' Branch: '. $branch .' Department: '. $department .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
											$s++;
										}

										if ( ( count( $unique_job ) == 1 && isset( $level_5[0]['default_job'] ) && $job == $level_5[0]['default_job'] ) ) {
											$lines_per_day--;
										}
										//Debug::Text('bLines Per Day: '. $lines_per_day .' Max: '. $max_lines_per_day .' Branch: '. $branch .' Department: '. $department .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
									}

									if ( ( count( $unique_department ) == 1 && isset( $level_5[0]['default_department'] ) && $department == $level_5[0]['default_department'] ) ) {
										$lines_per_day--;
									}
									//Debug::Text('cLines Per Day: '. $lines_per_day .' Max: '. $max_lines_per_day .' Branch: '. $branch .' Department: '. $department .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
								}

								//Remove lines if they match default branch/department to save space.
								if ( ( count( $unique_branch ) == 1 && isset( $level_5[0]['default_branch'] ) && $branch == $level_5[0]['default_branch'] ) ) {
									$lines_per_day--;
								}
								//Debug::Text('dLines Per Day: '. $lines_per_day .' Max: '. $max_lines_per_day .' Branch: '. $branch .' Department: '. $department .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
							}

							if ( $lines_per_day > $max_lines_per_day ) {
								$max_lines_per_day = $lines_per_day;
							}
						}
					}
					unset( $date_stamp );
					//Debug::Text('Max Lines Per Day: '. $max_lines_per_day .' User ID: '. $user_id .' Row: '. $row, __FILE__, __LINE__, __METHOD__, 10);

					//Track if the user is assigned to multiple branches/departments, if we are going to display even one in the week, we may as well
					//display them all as it doesn't take up anymore space.
					$multiple_branches = false;
					if ( isset( $unique_branch ) && count( $unique_branch ) > 1 ) {
						$multiple_branches = true;
						unset( $unique_branch );
					}

					$multiple_departments = false;
					if ( isset( $unique_department ) && count( $unique_department ) > 1 ) {
						$multiple_departments = true;
						unset( $unique_department );
					}

					$multiple_jobs = false;
					if ( isset( $unique_job ) && count( $unique_job ) > 1 ) {
						$multiple_jobs = true;
						unset( $unique_job );
					}

					$multiple_job_items = false;
					if ( isset( $unique_job_item ) && count( $unique_job_item ) > 1 ) {
						$multiple_job_items = true;
						unset( $unique_job_item );
					}

					if ( $s > 0 ) {
						if ( isset( $this->form_data['user'][$user_id] ) ) {
							$user_data = $this->form_data['user'][$user_id];

							$i = 0;
							$row_height = $this->_pdf_fontSize( 5 );
							if ( $this->scheduleCheckPageBreak( ( ( $row_height * $max_lines_per_day ) + 5 ), true ) == true ) {
								$this->scheduleHeader();
								$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );
								$this->scheduleWeekHeader( $calendar_array, $column_widths, $format, true );
							}

							$top_y = $this->pdf->getY();

							foreach ( $calendar_array as $calendar_day ) {
								$date_stamp = $calendar_day['date_stamp'];

								//Handle split shifts in the same day, count how many shifts exist.
								//Debug::Text('User ID: '. $user_data['first_name']	 .'('.$user_id.') Date: '. $date_stamp .' Total Shifts: '. $max_lines_per_day .' Row: '. $row, __FILE__, __LINE__, __METHOD__, 10);

								if ( $i == 0 && ( $format != 'pdf_schedule' && $format != 'pdf_schedule_print' ) ) {
									$this->pdf->setFillColorArray( $row_bg_color_arr );
									$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );
									$this->pdf->Cell( $column_widths['label'], ( $row_height * $max_lines_per_day ), $user_data['first_name'] . ' ' . $user_data['last_name'], 'BLR', 0, 'C', true, null, 1 );
									$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 8 ) );
								}

								//$this->pdf->setLineWidth( 0.4 );
								$this->pdf->setFillColor( 0, 0, 0 );
								$this->pdf->Line( $this->pdf->getX(), $this->pdf->getY(), ( $this->pdf->getX() + $column_widths['day'] ), $this->pdf->getY() );
								$this->pdf->setFillColor( 255, 255, 255 );

								$x = 0;
								if ( isset( $schedule_data[$date_stamp] ) ) {
									foreach ( $schedule_data[$date_stamp] as $branch => $level_2 ) {
										if ( $branch !== 0 && ( $multiple_branches == true || $branch != $level_5[0]['default_branch'] ) ) { //Branch is a name, NOT a UUID! Don't display the employees default branch to save space.
											$this->pdf->setFillColor( 215, 215, 215 );
											$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );
											$this->pdf->Cell( $column_widths['day'], $row_height, $branch, 'LR', 2, 'C', true, null, 1 );
											$x++;
										}
										foreach ( $level_2 as $department => $level_3 ) {
											if ( $department !== 0 && ( $multiple_departments == true || $department != $level_5[0]['default_department'] ) ) { //Department is a name, NOT a UUID!  Don't display the employees default branch to save space.
												$this->pdf->setFillColor( 230, 230, 230 );
												$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );
												$this->pdf->Cell( $column_widths['day'], $row_height, $department, 'LR', 2, 'C', true, null, 1 );
												$x++;
											}

											foreach ( $level_3 as $job => $level_4 ) {
												if ( $job !== 0 && ( $multiple_jobs == true || $job != $level_5[0]['default_job'] ) ) { //Don't display the employees default branch to save space.
													$this->pdf->setFillColor( 230, 230, 230 );
													$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );
													$this->pdf->Cell( $column_widths['day'], $row_height, $job, 'LR', 2, 'C', true, null, 1 );
													$x++;
												}

												foreach ( $level_4 as $job_item => $level_5 ) {
													if ( $job_item !== 0 && ( $multiple_job_items == true || $job_item != $level_5[0]['default_job_item'] ) ) { //Don't display the employees default branch to save space.
														$this->pdf->setFillColor( 230, 230, 230 );
														$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( 8 ) );
														$this->pdf->Cell( $column_widths['day'], $row_height, $job_item, 'LR', 2, 'C', true, null, 1 );
														$x++;
													}

													//$this->pdf->setFillColor(255, 255, 255);
													$this->pdf->setFillColorArray( $row_bg_color_arr );
													$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 8 ) );

													foreach ( $level_5 as $schedule_data_shift ) {
														if ( isset( $schedule_data_shift['status_id'] ) && $schedule_data_shift['status_id'] == 20 ) {
															$this->pdf->setTextColor( 255, 0, 0 );
															$this->pdf->Cell( $column_widths['day'], $row_height, ( $schedule_data_shift['absence_policy'] != '' ) ? $schedule_data_shift['absence_policy'] : TTi18n::getText( 'N/A' ), 'LR', 2, 'C', true, null, 1 );
															$this->pdf->setTextColor( 0, 0, 0 );
														} else {
															$this->pdf->Cell( $column_widths['day'], $row_height, $schedule_data_shift['start_time'] . ' - ' . $schedule_data_shift['end_time'], 'LR', 2, 'C', true, null, 1 );
														}

														$x++;
													}
												}
											}
										}
									}
								}

								for ( $y = $x; $y < $max_lines_per_day; $y++ ) {
									$this->pdf->setFillColorArray( $row_bg_color_arr );
									$this->pdf->Cell( $column_widths['day'], $row_height, '', 'LR', 2, 'C', 1 );
								}

								$this->pdf->setXY( ( $this->pdf->getX() + $column_widths['day'] ), $top_y ); //Setting Y by itself resets X.

								$i++;
							}

							$this->pdf->Ln( $row_height * $max_lines_per_day );
						}

						return true;
					}

					break;
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function scheduleFooterWeek() {
		$week_width = ( $this->pdf->getPageWidth() - $this->config['other']['left_margin'] );
		$this->pdf->Line( $this->pdf->getX(), $this->pdf->getY(), $week_width, $this->pdf->getY() );

		return true;
	}

	/**
	 * @param $format
	 * @return bool|string
	 */
	function _outputPDFSchedule( $format ) {
		Debug::Text( ' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		$current_company = $this->getUserObject()->getCompanyObject();
		if ( !is_object( $current_company ) ) {
			Debug::Text( 'Invalid company object...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$filter_data = $this->getFilterConfig();

		//Required fields
		// 'first_name', 'last_name', 'branch', 'department', 'start_time', 'end_time'
		$start_week_day = 0;
		if ( is_object( $this->getUserObject() ) && is_object( $this->getUserObject()->getUserPreferenceObject() ) ) {
			$start_week_day = $this->getUserObject()->getUserPreferenceObject()->getStartWeekDay();
		}

		//Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10);
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), 2, null, TTi18n::getText( 'Querying Database...' ) ); //Iterations need to be 2, otherwise progress bar is not created.
		$this->getProgressBarObject()->set( $this->getAPIMessageID(), 2 );

		$sf = TTNew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */

		//getScheduleArray() doesn't accept pay_period_ids, so no data is returned if a time period of "last_pay_period" is selected.
		if ( isset( $filter_data['pay_period_id'] ) ) {
			unset( $filter_data['pay_period_id'] );

			$filter_data['start_date'] = TTDate::getBeginDayEpoch( ( time() - ( 86400 * 14 ) ) ); //Default to the last 14days.
			$filter_data['end_date'] = TTDate::getEndDayEpoch( ( time() - 86400 ) );
		}

		//If we don't have permissions to view open shifts, exclude user_id = 0;
		if ( $this->getPermissionObject()->Check( 'schedule', 'view_open' ) == false ) {
			$filter_data['exclude_id'] = [ TTUUID::getZeroID() ];
		}

		$raw_schedule_shifts = $sf->getScheduleArray( $filter_data );

		if ( is_array( $raw_schedule_shifts ) ) {
			//Debug::Arr($raw_schedule_shifts, 'Raw Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $raw_schedule_shifts, COUNT_RECURSIVE ), null, TTi18n::getText( 'Retrieving Data...' ) );
			$key = 0;
			foreach ( $raw_schedule_shifts as $date_stamp => $day_schedule_shifts ) {
				foreach ( $day_schedule_shifts as $shift_arr ) {
					//$this->form_data['schedule_by_branch'][$shift_arr['branch']][$shift_arr['department']][$shift_arr['last_name'].$shift_arr['first_name']][$date_stamp][] = $shift_arr;
					$this->form_data['schedule_by_branch'][$shift_arr['branch']][$shift_arr['department']][$shift_arr['job']][$shift_arr['job_item']][$shift_arr['last_name'] . $shift_arr['first_name']][$date_stamp][] = $shift_arr;

					//Need to be able to sort employees by last name first. Use names as keys instead of user_ids.
					//$this->form_data['schedule_by_user'][$shift_arr['last_name'].'_'.$shift_arr['first_name']][$date_stamp][$shift_arr['branch']][$shift_arr['department']][] = $shift_arr;
					$this->form_data['schedule_by_user'][$shift_arr['last_name'] . '_' . $shift_arr['first_name']][$date_stamp][$shift_arr['branch']][$shift_arr['department']][$shift_arr['job']][$shift_arr['job_item']][] = $shift_arr;

					if ( !isset( $this->form_data['dates']['start_date'] ) || $this->form_data['dates']['start_date'] > $date_stamp ) {
						$this->form_data['dates']['start_date'] = $date_stamp;
					}
					if ( !isset( $this->form_data['dates']['end_date'] ) || $this->form_data['dates']['end_date'] < $date_stamp ) {
						$this->form_data['dates']['end_date'] = $date_stamp;
					}

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
					$key++;
				}
			}
			unset( $date_stamp, $raw_schedule_shifts, $day_schedule_shifts );
		} else {
			Debug::Text( 'No schedule shifts returned...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		//Initialize array element if it doesn't exist to prevent PHP warning.
		if ( !isset( $this->form_data['schedule_by_user'] ) ) {
			$this->form_data['schedule_by_user'] = [];
		}
		//Debug::Arr($this->form_data['schedule_by_branch'], '2Raw Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		//Debug::Arr($this->form_data['dates'], 'Dates: ', __FILE__, __LINE__, __METHOD__, 10);
		//If pay periods are requested, we need to convert those to start/end dates.
		if ( isset( $this->form_data['dates']['start_date'] ) && isset( $this->form_data['dates']['end_date'] ) && ( !isset( $filter_data['start_date'] ) || !isset( $filter_data['end_date'] ) ) ) {
			$filter_data['start_date'] = strtotime( $this->form_data['dates']['start_date'] );
			$filter_data['end_date'] = strtotime( $this->form_data['dates']['end_date'] );
		}

		if ( isset( $filter_data['start_date'] ) && isset( $filter_data['end_date'] ) ) {
			$this->pdf = new TTPDF( $this->config['other']['page_orientation'], 'mm', $this->config['other']['page_format'], $this->getUserObject()->getCompanyObject()->getEncoding() );

			$this->pdf->SetAuthor( APPLICATION_NAME );
			$this->pdf->SetTitle( $this->title );
			$this->pdf->SetSubject( APPLICATION_NAME . ' ' . TTi18n::getText( 'Report' ) );

			$this->pdf->setMargins( $this->config['other']['left_margin'], $this->config['other']['top_margin'], $this->config['other']['right_margin'] ); //Margins are ignored because we use setXY() to force the coordinates before each drawing and therefore ignores margins.
			//Debug::Arr($this->config['other'], 'Margins: ', __FILE__, __LINE__, __METHOD__, 10);

			$this->pdf->SetAutoPageBreak( false, 0 );

			$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( 10 ) );

			//Debug::Arr($this->form_data, 'zabUser Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

			$calendar_array = TTDate::getCalendarArray( $filter_data['start_date'], $filter_data['end_date'], $start_week_day );
			//Debug::Arr($calendar_array, 'Calendar Array: ', __FILE__, __LINE__, __METHOD__, 10);

			switch ( $format ) {
				case 'pdf_schedule_group':
				case 'pdf_schedule_group_print':
				case 'pdf_schedule_group_pagebreak':
				case 'pdf_schedule_group_pagebreak_print':
					//
					// Group - Separate (branch/department on their own pages)
					//

					//Start displaying dates/times here. Start with header.
					$column_widths = [
							'line'  => 5,
							'label' => 30,
							'day'   => ( ( $this->pdf->getPageWidth() - $this->config['other']['left_margin'] - $this->config['other']['right_margin'] - 30 ) / 7 ),
					];

					if ( isset( $this->form_data['schedule_by_branch'] ) ) {
						$this->pdf->AddPage( $this->config['other']['page_orientation'], 'LETTER' );

						$n = 0;
						$x = 0;
						ksort( $this->form_data['schedule_by_branch'] );
						foreach ( $this->form_data['schedule_by_branch'] as $branch => $level_2 ) {
							ksort( $level_2 );
							foreach ( $level_2 as $department => $level_3 ) {
								ksort( $level_3 );
								foreach ( $level_3 as $job => $level_4 ) {
									ksort( $level_4 );
									foreach ( $level_4 as $job_item => $level_5 ) {
										ksort( $level_5 );

										if ( $format == 'pdf_schedule_group_pagebreak' || $format == 'pdf_schedule_group_pagebreak_print' ) {
											//Insert page breaks after each branch/department in this mode.
											if ( $n > 0 ) {
												$this->pdf->AddPage( $this->config['other']['page_orientation'], 'LETTER' );
											}
											$page_break = true;
										} else {
											$page_break = ( $x == 0 ) ? true : $this->scheduleCheckPageBreak( $this->_pdf_scaleSize( 30 ), true );
											//Debug::Arr($this->form_data, 'zabUser Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
										}

										//If we are within 30mm of end of page, where a scheduleHeader/DayOfWeekHeader will just barely fit
										// but no data will be able to fit after it on the page, just start a new page instead.
										$this->scheduleCheckPageBreak( $this->_pdf_scaleSize( 30 ), true );
										$this->scheduleHeader( $branch, $department, $job, $job_item, null, $page_break );
										$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );

										//FIXME: Find a better way to determine how many iterations there will be in this loop.
										$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $calendar_array ), null, TTi18n::getText( 'Generating Schedules...' ) );
										$key = 0;
										$i = 0;
										foreach ( $calendar_array as $calendar_day ) {

											if ( ( $i % 7 ) == 0 ) {
												$calendar_week_array = array_slice( $calendar_array, $i, 7 );
												if ( $i != 0 ) {
													$this->scheduleFooterWeek();
												}

												$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format );

												$s = 0;
												foreach ( $level_5 as $user_schedule ) {
													if ( $this->_pdf_checkMaximumPageLimit() == false ) {
														Debug::Text( 'Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__, 10 );
														//Exceeded maximum pages, stop processing.
														$this->_pdf_displayMaximumPageLimitError();
														break 4;
													}

													//Handle page break.
													if ( $this->scheduleCheckPageBreak( $this->_pdf_scaleSize( 5 ), true ) == true ) {
														$this->scheduleHeader( $branch, $department, $job, $job_item );
														$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );
														$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format, true );
													}

													if ( ( $s % 2 ) == 0 ) {
														$this->pdf->setFillColor( 255, 255, 255 );
													} else {
														$this->pdf->setFillColor( 245, 245, 245 );
													}

													if ( $this->scheduleUserWeek( $user_schedule, $calendar_week_array, $start_week_day, $column_widths, $format, $key ) == true ) {
														$s++;
													}
												}
											}

											$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
											if ( ( $key % 25 ) == 0 && $this->isSystemLoadValid() == false ) {
												return false;
											}
											$key++;
											$i++;
										}
										unset( $calendar_day );
										$this->scheduleFooterWeek();
										$x++;
									}
								}
							}

							$n++;
						}

						$this->scheduleFooter();
					} else {
						$this->scheduleNoData();
					}

					break;
				case 'pdf_schedule_group_combined':
				case 'pdf_schedule_group_combined_print':
					ksort( $this->form_data['schedule_by_user'] );
					//Start displaying dates/times here. Start with header.
					$column_widths = [
							'line'  => 5,
							'label' => 30,
							'day'   => ( ( $this->pdf->getPageWidth() - $this->config['other']['left_margin'] - $this->config['other']['right_margin'] - 30 ) / 7 ),
					];


					$this->getProgressBarObject()->start( $this->getAPIMessageID(), ( count( $this->form_data['schedule_by_user'] ) * ( count( $calendar_array ) / 7 ) ), null, TTi18n::getText( 'Generating Schedules...' ) );

					$this->pdf->AddPage( $this->config['other']['page_orientation'], 'LETTER' );

					$this->scheduleHeader();
					$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );

					$key = 0;
					$i = 0;
					foreach ( $calendar_array as $calendar_day ) {
						if ( ( $i % 7 ) == 0 ) {
							$calendar_week_array = array_slice( $calendar_array, $i, 7 );
							if ( $i != 0 ) {
								$this->scheduleFooterWeek();
							}

							$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format );

							foreach ( $this->form_data['schedule_by_user'] as $user_schedule ) {
								if ( $this->_pdf_checkMaximumPageLimit() == false ) {
									Debug::Text( 'Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__, 10 );
									//Exceeded maximum pages, stop processing.
									$this->_pdf_displayMaximumPageLimitError();
									break 2;
								}

								//Handle page break.
								if ( $this->scheduleCheckPageBreak( $this->_pdf_scaleSize( 5 ), true ) == true ) {
									$this->scheduleFooterWeek();
									$this->scheduleHeader();
									$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );
									$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format, true );
								}

								$this->pdf->setFillColor( 255, 255, 255 );
								$this->scheduleUserWeek( $user_schedule, $calendar_week_array, $start_week_day, $column_widths, $format, $key );

								$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
								if ( ( $key % 25 ) == 0 && $this->isSystemLoadValid() == false ) {
									return false;
								}
								$key++;
							}

							$this->scheduleFooterWeek();
						}

						$i++;
					}

					$this->scheduleFooter();

					break;
				case 'pdf_schedule':
				case 'pdf_schedule_print':
					ksort( $this->form_data['schedule_by_user'] );
					//Start displaying dates/times here. Start with header.
					$column_widths = [
							'line'  => 5,
							'label' => 0,
							'day'   => ( $this->pdf->getPageWidth() - $this->config['other']['left_margin'] - $this->config['other']['right_margin'] - 0 ) / 7,
					];

					if ( isset( $this->form_data['schedule_by_user'] ) && count( $this->form_data['schedule_by_user'] ) > 0 ) {
						$this->getProgressBarObject()->start( $this->getAPIMessageID(), ( count( $this->form_data['schedule_by_user'] ) * ( count( $calendar_array ) / 7 ) ), null, TTi18n::getText( 'Generating Schedules...' ) );
						$key = 0;

						foreach ( $this->form_data['schedule_by_user'] as $user_full_name => $user_schedule ) {
							$this->pdf->AddPage( $this->config['other']['page_orientation'], 'LETTER' );

							$split_name = explode( '_', $user_full_name );
							$this->scheduleHeader( null, null, null, null, $split_name[1] . ' ' . $split_name[0] );
							unset( $split_name );

							$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );

							$i = 0;
							foreach ( $calendar_array as $calendar_day ) {
								if ( ( $i % 7 ) == 0 ) {
									if ( $this->_pdf_checkMaximumPageLimit() == false ) {
										Debug::Text( 'Exceeded maximum page count...', __FILE__, __LINE__, __METHOD__, 10 );
										//Exceeded maximum pages, stop processing.
										$this->_pdf_displayMaximumPageLimitError();
										break 2;
									}

									$calendar_week_array = array_slice( $calendar_array, $i, 7 );
									if ( $i != 0 ) {
										$this->scheduleFooterWeek();
									}

									$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format );

									//Handle page break.
									$page_break_height = $this->_pdf_scaleSize( 5 );
									if ( $this->scheduleCheckPageBreak( $page_break_height, true ) == true ) {
										$this->scheduleHeader();
										$this->scheduleDayOfWeekNameHeader( $start_week_day, $column_widths, $format );
										$this->scheduleWeekHeader( $calendar_week_array, $column_widths, $format, true );
									}

									$this->pdf->setFillColor( 255, 255, 255 );
									$this->scheduleUserWeek( $user_schedule, $calendar_week_array, $start_week_day, $column_widths, $format, $key );
								}

								$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
								if ( ( $key % 25 ) == 0 && $this->isSystemLoadValid() == false ) {
									return false;
								}
								$key++;
								$i++;
							}

							$this->scheduleFooterWeek();
							$this->scheduleFooter();
						}
					} else {
						$this->scheduleNoData();
					}
					break;
			}

			$output = $this->pdf->Output( '', 'S' );

			return $output;
		} else {
			Debug::Text( 'No start/end date specified...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		Debug::Text( 'No data to return...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param null $format
	 * @return array|bool|string
	 */
	function _output( $format = null ) {
		//Individual Schedules
		//Group - Combined (all branch/department combined together)
		//Group - Separated (branch/department all separated.)
		if ( in_array( $format, $this->special_output_format ) ) {
			return $this->_outputPDFSchedule( $format );
		} else {
			return parent::_output( $format );
		}
	}
}

?>
