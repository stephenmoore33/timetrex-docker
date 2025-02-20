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
class TimesheetSummaryReport extends Report {

	/**
	 * TimesheetSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'TimeSheet Summary Report' );
		$this->file_name = 'timesheet_summary_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_timesheet_summary', $user_id, $company_id ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	protected function _validateConfig() {
		$config = $this->getConfig();

		//Make sure some time period is selected.
		if ( ( !isset( $config['filter']['time_period'] ) && !isset( $config['filter']['pay_period_id'] ) ) || ( isset( $config['filter']['time_period'] ) && isset( $config['filter']['time_period']['time_period'] ) && $config['filter']['time_period']['time_period'] == TTUUID::getZeroId() ) ) {
			$this->validator->isTrue( 'time_period', false, TTi18n::gettext( 'No time period defined for this report' ) );
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
					'-1000-template'              => TTi18n::gettext( 'Template' ),
					'-1010-time_period'           => TTi18n::gettext( 'Time Period' ),
					'-2000-legal_entity_id'       => TTi18n::gettext( 'Legal Entity' ),
					'-2010-user_status_id'        => TTi18n::gettext( 'Employee Status' ),
					'-2020-user_group_id'         => TTi18n::gettext( 'Employee Group' ),
					'-2025-policy_group_id'       => TTi18n::gettext( 'Policy Group' ),
					'-2030-user_title_id'         => TTi18n::gettext( 'Employee Title' ),
					'-2035-user_tag'              => TTi18n::gettext( 'Employee Tags' ),
					'-2040-include_user_id'       => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'       => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-default_branch_id'     => TTi18n::gettext( 'Default Branch' ),
					'-2070-default_department_id' => TTi18n::gettext( 'Default Department' ),
					'-2080-punch_branch_id'       => TTi18n::gettext( 'Punch Branch' ),
					'-2090-punch_department_id'   => TTi18n::gettext( 'Punch Department' ),
					'-2000-currency_id'           => TTi18n::gettext( 'Currency' ),
					'-2100-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),
					'-2300-punch_tag_id'          => TTi18n::gettext( 'Punch Tag' ),

					'-4010-pay_period_time_sheet_verify_status_id' => TTi18n::gettext( 'TimeSheet Verification' ),
					'-4020-include_no_data_rows'                   => TTi18n::gettext( 'Include Blank Records' ),

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
					];
					$retval = array_merge( $retval, $corporate_edition_setup_fields );
				}
				ksort( $retval );
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( null, TTi18n::getText( 'Date' ), 13, true );
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$retval = $this->getCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['branch', 'department', 'users'], ['users'] );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'TimesheetSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'TimesheetSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'TimesheetSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'TimesheetSummaryReport', 'custom_column' );
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
					'-1080-user_group'         => TTi18n::gettext( 'Employee Group' ),
					'-1090-default_branch'     => TTi18n::gettext( 'Default Branch' ),
					'-1091-default_branch_manual_id' => TTi18n::gettext( 'Default Branch Code' ),
					'-1100-default_department' => TTi18n::gettext( 'Default Department' ),
					'-1100-default_department_manual_id' => TTi18n::gettext( 'Default Department Code' ),
					'-1110-currency'           => TTi18n::gettext( 'Currency' ),
					'-1111-current_currency'   => TTi18n::gettext( 'Current Currency' ),

					//'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
					//'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

					'-1400-permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1410-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1420-policy_group'        => TTi18n::gettext( 'Policy Group' ),

					'-1430-branch_name'          => TTi18n::gettext( 'Branch' ),
					'-1431-branch_manual_id'     => TTi18n::gettext( 'Branch Code' ),
					'-1440-department_name'      => TTi18n::gettext( 'Department' ),
					'-1441-department_manual_id' => TTi18n::gettext( 'Department Code' ),

					//This can't be a secure SIN otherwise it doesn't make much sense putting it on here... But if its here then
					//supervisors can see it and it may be a security concern.
					//'-1480-sin' => TTi18n::gettext('SIN/SSN'),

					'-1490-note' => TTi18n::gettext( 'Note' ),
					'-1495-tag'  => TTi18n::gettext( 'Tags' ),

					//Handled in date_columns above.
					//'-1450-pay_period' => TTi18n::gettext('Pay Period'),

					'-1510-verified_time_sheet'      => TTi18n::gettext( 'Verified TimeSheet' ),
					'-1515-verified_time_sheet_date' => TTi18n::gettext( 'Verified TimeSheet Date' ),
				];

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval['-1105-default_job'] = TTi18n::gettext( 'Default Job' );
					$retval['-1106-default_job_manual_id'] = TTi18n::gettext( 'Default Job Code' );
					$retval['-1107-default_job_item'] = TTi18n::gettext( 'Default Task' );
					$retval['-1108-default_job_item_manual_id'] = TTi18n::gettext( 'Default Task Code' );
				}

				$retval = array_merge( $retval, (array)$this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(),  [ 'branch', 'department', 'users', 'user_title' ], ['users'] ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9100, $this->getUserObject()->getCompany(),  [ 'branch', 'department' ], null, [ 'branch' => [ 'name' => 'default_branch_', 'label' => TTi18n::getText( 'Default Branch' ).' ' ], 'department' => [ 'name' => 'default_department_', 'label' => TTi18n::getText( 'Default Department' ) .' ' ] ] ) ); //Append Default Branch/Department custom fields as well.
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2070-schedule_working'      => TTi18n::gettext( 'Scheduled Time' ),
					'-2072-schedule_working_diff' => TTi18n::gettext( 'Scheduled Time Diff.' ),
					'-2080-schedule_absence'      => TTi18n::gettext( 'Scheduled Absence' ),

					'-2085-worked_days' => TTi18n::gettext( 'Worked Days' ),

					'-3000-worked_time'        => TTi18n::gettext( 'Total Worked Time' ),
					'-3010-regular_time'       => TTi18n::gettext( 'Total Regular Time' ),
					'-3015-overtime_time'      => TTi18n::gettext( 'Total OverTime' ),
					'-3020-absence_time'       => TTi18n::gettext( 'Total Absence Time' ),
					'-3022-absence_taken_time' => TTi18n::gettext( 'Total Absence Time (Taken)' ),
					'-3025-premium_time'       => TTi18n::gettext( 'Total Premium Time' ),
					'-3030-gross_time'         => TTi18n::gettext( 'Total Paid Time' ),
					//'-3030-actual_time' => TTi18n::gettext('Total Actual Time'),
					//'-3035-actual_time_diff' => TTi18n::gettext('Actual Time Difference'),

					'-3090-lunch_time' => TTi18n::gettext( 'Lunch Time (Taken)' ),
					'-3091-break_time' => TTi18n::gettext( 'Break Time (Taken)' ),

					'-3210-regular_wage'  => TTi18n::gettext( 'Total Regular Time Wage' ),
					'-3215-overtime_wage' => TTi18n::gettext( 'Total OverTime Wage' ),
					'-3220-absence_wage'  => TTi18n::gettext( 'Total Absence Time Wage' ),
					'-3225-premium_wage'  => TTi18n::gettext( 'Total Premium Time Wage' ),

					'-3200-gross_wage'             => TTi18n::gettext( 'Gross Wage' ),
					'-3400-gross_wage_with_burden' => TTi18n::gettext( 'Gross Wage w/Burden' ),
					//'-3390-gross_hourly_rate' => TTi18n::gettext('Gross Hourly Rate'),
				];

				$retval = array_merge( $retval, $this->getOptions( 'paycode_columns' ), $this->getDynamicCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['branch', 'department', 'users'], ['users'] )  );
				ksort( $retval );
				break;
			case 'paycode_columns':
				$retval = parent::__getOptions( $name, 3 );
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions( 'static_columns' ), $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) );
				ksort( $retval );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_custom_column' ) );
				if ( is_array( $columns ) ) {
					foreach ( $columns as $column => $name ) {
						if ( strpos( $column, '_wage' ) !== false || strpos( $column, '_hourly_rate' ) !== false ) {
							$retval[$column] = 'currency';
						} else if ( strpos( $column, '_time' ) || strpos( $column, 'schedule_' ) ) {
							$retval[$column] = 'time_unit';
						}
					}
				}
				$retval['verified_time_sheet_date'] = 'time_stamp';
				break;
			case 'aggregates':
				$retval = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, '_hourly_rate' ) !== false ) {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				$retval['verified_time_sheet'] = 'first';
				$retval['verified_time_sheet_date'] = 'first';
				$retval['worked_days'] = 'numeric';
				break;
			case 'templates':
				$retval = [
						'-1050-by_employee+all_time'                           => TTi18n::gettext( 'All Time by Employee' ),
						'-1150-by_date_by_full_name+all_time'                  => TTi18n::gettext( 'All Time by Date/Employee' ),
						'-1200-by_full_name_by_date+all_time'                  => TTi18n::gettext( 'All Time by Employee/Date' ),
						'-1250-by_branch+regular+all_time'                     => TTi18n::gettext( 'All Time by Branch' ),
						'-1300-by_department+all_time'                         => TTi18n::gettext( 'All Time by Department' ),
						'-1350-by_branch_by_department+all_time'               => TTi18n::gettext( 'All Time by Branch/Department' ),
						'-1400-by_pay_period+all_time'                         => TTi18n::gettext( 'All Time by Pay Period' ),
						'-1450-by_pay_period_by_employee+all_time'             => TTi18n::gettext( 'All Time by Pay Period/Employee' ),
						'-1500-by_pay_period_by_branch+all_time'               => TTi18n::gettext( 'All Time by Pay Period/Branch' ),
						'-1550-by_pay_period_by_department+all_time'           => TTi18n::gettext( 'All Time by Pay Period/Department' ),
						'-1600-by_pay_period_by_branch_by_department+all_time' => TTi18n::gettext( 'All Time by Pay Period/Branch/Department' ),
						'-1650-by_employee_by_pay_period+all_time'             => TTi18n::gettext( 'All Time by Employee/Pay Period' ),
						'-1700-by_branch_by_pay_period+all_time'               => TTi18n::gettext( 'All Time by Branch/Pay Period' ),
						'-1850-by_department_by_pay_period+all_time'           => TTi18n::gettext( 'All Time by Department/Pay Period' ),
						'-1900-by_branch_by_department_by_pay_period+all_time' => TTi18n::gettext( 'All Time by Branch/Department/Pay Period' ),

						'-2100-by_employee+regular+all_time+all_wage'                   => TTi18n::gettext( 'All Time+Wage by Employee' ),
						'-2150-by_date_by_full_name+all_time+all_wage'                  => TTi18n::gettext( 'All Time+Wage by Date/Employee' ),
						'-2200-by_full_name_by_date+all_time+all_wage'                  => TTi18n::gettext( 'All Time+Wage by Employee/Date' ),
						'-2250-by_branch+regular+all_time+all_wage'                     => TTi18n::gettext( 'All Time+Wage by Branch' ),
						'-2300-by_department+all_time+all_wage'                         => TTi18n::gettext( 'All Time+Wage by Department' ),
						'-2350-by_branch_by_department+all_time+all_wage'               => TTi18n::gettext( 'All Time+Wage by Branch/Department' ),
						'-2400-by_pay_period+all_time+all_wage'                         => TTi18n::gettext( 'All Time+Wage by Pay Period' ),
						'-2450-by_pay_period_by_employee+all_time+all_wage'             => TTi18n::gettext( 'All Time+Wage by Pay Period/Employee' ),
						'-2500-by_pay_period_by_branch+all_time+all_wage'               => TTi18n::gettext( 'All Time+Wage by Pay Period/Branch' ),
						'-2550-by_pay_period_by_department+all_time+all_wage'           => TTi18n::gettext( 'All Time+Wage by Pay Period/Department' ),
						'-2600-by_pay_period_by_branch_by_department+all_time+all_wage' => TTi18n::gettext( 'All Time+Wage by Pay Period/Branch/Department' ),
						'-2650-by_employee_by_pay_period+all_time+all_wage'             => TTi18n::gettext( 'All Time+Wage by Employee/Pay Period' ),
						'-2700-by_branch_by_pay_period+all_time+all_wage'               => TTi18n::gettext( 'All Time+Wage by Branch/Pay Period' ),
						'-2850-by_department_by_pay_period+all_time+all_wage'           => TTi18n::gettext( 'All Time+Wage by Department/Pay Period' ),
						'-2900-by_branch_by_department_by_pay_period+all_time+all_wage' => TTi18n::gettext( 'All Time+Wage by Branch/Department/Pay Period' ),

						'-3000-by_pay_period_by_employee+verified_time_sheet'                        => TTi18n::gettext( 'Timesheet Verification by Pay Period/Employee' ),
						'-3010-by_verified_time_sheet_by_pay_period_by_employee+verified_time_sheet' => TTi18n::gettext( 'Timesheet Verification by Verification/Pay Period/Employee' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						case 'by_pay_period_by_employee+verified_time_sheet':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'pay_period';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'verified_time_sheet';
							$retval['columns'][] = 'verified_time_sheet_date';

							$retval['group'][] = 'pay_period';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = [ 'pay_period' => 'asc' ];
							$retval['sort'][] = [ 'verified_time_sheet' => 'desc' ];
							$retval['sort'][] = [ 'verified_time_sheet_date' => 'asc' ];
							break;
						case 'by_verified_time_sheet_by_pay_period_by_employee+verified_time_sheet':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';

							$retval['columns'][] = 'verified_time_sheet';
							$retval['columns'][] = 'pay_period';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'verified_time_sheet_date';

							$retval['group'][] = 'verified_time_sheet';
							$retval['group'][] = 'pay_period';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = [ 'verified_time_sheet' => 'desc' ];
							$retval['sort'][] = [ 'pay_period' => 'asc' ];
							$retval['sort'][] = [ 'verified_time_sheet_date' => 'asc' ];
							break;

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
										case 'all_time':
											$retval['columns'][] = 'worked_time';
											$retval['columns'][] = 'regular_time';
											$retval['columns'][] = 'overtime_time';
											$retval['columns'][] = 'absence_time';
											$retval['columns'][] = 'premium_time';
											break;
										case 'all_wage':
											$retval['columns'][] = 'gross_wage';
											$retval['columns'][] = 'regular_wage';
											$retval['columns'][] = 'overtime_wage';
											$retval['columns'][] = 'absence_wage';
											$retval['columns'][] = 'premium_wage';
											break;
										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											break;
										case 'by_branch':
											$retval['columns'][] = 'branch_name';

											$retval['group'][] = 'branch_name';

											$retval['sort'][] = [ 'branch_name' => 'asc' ];
											break;
										case 'by_department':
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'department_name';

											$retval['sort'][] = [ 'department_name' => 'asc' ];
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = [ 'branch_name' => 'asc' ];
											$retval['sort'][] = [ 'department_name' => 'asc' ];
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
											$retval['columns'][] = 'branch_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											$retval['sort'][] = [ 'branch_name' => 'asc' ];
											break;
										case 'by_pay_period_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'pay_period';

											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											$retval['sort'][] = [ 'department_name' => 'asc' ];
											break;
										case 'by_pay_period_by_branch_by_department':
											$retval['columns'][] = 'pay_period';
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';

											$retval['group'][] = 'pay_period';
											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';

											$retval['sub_total'][] = 'pay_period';
											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											$retval['sort'][] = [ 'branch_name' => 'asc' ];
											$retval['sort'][] = [ 'department_name' => 'asc' ];
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
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch_name';

											$retval['sort'][] = [ 'branch_name' => 'asc' ];
											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											break;
										case 'by_department_by_pay_period':
											$retval['columns'][] = 'department_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'department_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'department_name';

											$retval['sort'][] = [ 'department_name' => 'asc' ];
											$retval['sort'][] = [ 'pay_period' => 'asc' ];
											break;
										case 'by_branch_by_department_by_pay_period':
											$retval['columns'][] = 'branch_name';
											$retval['columns'][] = 'department_name';
											$retval['columns'][] = 'pay_period';

											$retval['group'][] = 'branch_name';
											$retval['group'][] = 'department_name';
											$retval['group'][] = 'pay_period';

											$retval['sub_total'][] = 'branch_name';
											$retval['sub_total'][] = 'department_name';

											$retval['sort'][] = [ 'branch_name' => 'asc' ];
											$retval['sort'][] = [ 'department_name' => 'asc' ];
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
										case 'by_full_name_by_date':
											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'date_stamp';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'date_stamp';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = [ 'full_name' => 'asc' ];
											$retval['sort'][] = [ 'date_stamp' => 'asc' ];
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
	 * //Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'user_date_total' => [], 'schedule' => [], 'worked_days' => [], 'user' => [], 'user_title' => [], 'default_branch' => [], 'default_department' => [], 'branch' => [], 'department' => [], 'verified_timesheet' => [] ];

		$columns = $this->getColumnDataConfig();

		$filter_data = $this->getFilterConfig();

		$currency_convert_to_base = $this->getCurrencyConvertToBase();
		$base_currency_obj = $this->getBaseCurrencyObject();
		$this->handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data );
		$currency_options = $this->getOptions( 'currency' );

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$wage_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		$pay_period_ids = [];

		$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
		$this->setMemoryPerRow( max( 2679, ( 11 * count( $this->getColumnDataConfig() ) ) ) );  //MAX: 6K for each row @ 575 columns = 11 bytes per column, per row. 2679 bytes per row at 1 column.
		$udtlf->getTimesheetSummaryReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, $this->getMemoryBasedRowLimit()  );
		Debug::Text( ' Total Rows: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $udtlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		$include_no_data_rows_arr = [];
		if ( $udtlf->getRecordCount() > 0 ) {
			if ( $this->isMemoryBasedRowLimitValid( $udtlf->getRecordCount() ) == false ) {
				$this->setMaximumRowsExceeded( true ); //Alert the user that the maximum number of rows was exceeded, but still generate the report with the data we have.
				if ( $this->handleMaximumRowsExceeded( $format ) == false ) {
					return false;
				}
			}

			foreach ( $udtlf as $key => $udt_obj ) {
				$pay_period_ids[$udt_obj->getColumn( 'pay_period_id' )] = true;

				$user_id = $udt_obj->getColumn( 'user_id' );
				$date_stamp = TTDate::strtotime( $udt_obj->getColumn( 'date_stamp' ) );
				$branch_id = $udt_obj->getColumn( 'branch_id' );
				$department_id = $udt_obj->getColumn( 'department_id' );
				$currency_rate = $udt_obj->getColumn( 'currency_rate' );
				$currency_id = $udt_obj->getColumn( 'currency_id' );

				//With pay codes, paid time makes sense now and is associated with branch/departments too.
				$time_columns = $udt_obj->getTimeCategory( false, $columns ); //Exclude 'total' as its not used in reports anyways, and causes problems when grouping by branch/default branch.

				//Debug::Text('Column: '. $columns .' Total Time: '. $udt_obj->getColumn('total_time') .' Amount: '. $udt_obj->getColumn('total_time_amount') .' Object Type ID: '. $udt_obj->getColumn('object_type_id') .' Rate: '. $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__, 10);
				if ( ( isset( $filter_data['include_no_data_rows'] ) && $filter_data['include_no_data_rows'] == 1 )
						|| ( $date_stamp != '' && count( $time_columns ) > 0 && $udt_obj->getColumn( 'total_time' ) != 0 ) ) {

					$enable_wages = $this->getPermissionObject()->isPermissionChild( $user_id, $wage_permission_children_ids );

					//Split time by user, date, branch, department as that is the lowest level we can split time.
					//We always need to split time as much as possible as it can always be combined together by grouping.
					//Unless we never add columns together that would ever span a single row is of course
					if ( !isset( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id] ) ) {
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id] = [
							//Use branch_id/department_id instead of names as we need to get additional branch/department information like manual_id/other_id fields below
							'branch_id'                   => $udt_obj->getColumn( 'branch_id' ),
							'department_id'               => $udt_obj->getColumn( 'department_id' ),
							'pay_period_start_date'       => strtotime( $udt_obj->getColumn( 'pay_period_start_date' ) ),
							'pay_period_end_date'         => strtotime( $udt_obj->getColumn( 'pay_period_end_date' ) ),
							'pay_period_transaction_date' => strtotime( $udt_obj->getColumn( 'pay_period_transaction_date' ) ),
							'pay_period'                  => strtotime( $udt_obj->getColumn( 'pay_period_transaction_date' ) ),
							'pay_period_id'               => $udt_obj->getColumn( 'pay_period_id' ),
						];

						if ( !isset( $include_no_data_rows_arr[$udt_obj->getColumn( 'pay_period_id' )][$date_stamp] ) ) {
							$include_no_data_rows_arr[$udt_obj->getColumn( 'pay_period_id' )][$date_stamp] = [
									'branch_id'                   => TTUUID::getZeroID(),
									'department_id'               => TTUUID::getZeroID(),
									'pay_period_start_date'       => strtotime( $udt_obj->getColumn( 'pay_period_start_date' ) ),
									'pay_period_end_date'         => strtotime( $udt_obj->getColumn( 'pay_period_end_date' ) ),
									'pay_period_transaction_date' => strtotime( $udt_obj->getColumn( 'pay_period_transaction_date' ) ),
									'pay_period'                  => strtotime( $udt_obj->getColumn( 'pay_period_transaction_date' ) ),
									'pay_period_id'               => $udt_obj->getColumn( 'pay_period_id' ),
							];
						}
					}

					if ( TTUUID::isUUID( $udt_obj->getPayCode() ) && $udt_obj->getPayCode() != TTUUID::getZeroID() && $udt_obj->getPayCode() != TTUUID::getNotExistID() ) { //Make sure we don't set the currency based on worked_time that will always be currency_id=0 and have no pay_code_id associated.
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['currency_rate'] = $currency_rate;
						$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['currency'] = $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['current_currency'] = Option::getByKey( $currency_id, $currency_options );
						if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
						}
					}

					foreach ( $time_columns as $column ) {
						//Debug::Text('bColumn: '. $column .' Total Time: '. $udt_obj->getColumn('total_time') .' Object Type ID: '. $udt_obj->getColumn('object_type_id') .' Rate: '. $udt_obj->getColumn( 'hourly_rate' ), __FILE__, __LINE__, __METHOD__, 10);

						if ( isset( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_time'] ) ) {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_time'] = TTMath::add( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_time'], $udt_obj->getColumn( 'total_time' ) );
						} else {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_time'] = $udt_obj->getColumn( 'total_time' );
						}

						//Gross wage (paid_wage) calculation must go here otherwise it gets doubled up.
						//Worked Time is required for printable TimeSheets. Therefore this report is handled differently from TimeSheetSummary.
						if ( $enable_wages == true && !in_array( $column, [ 'total', 'worked' ] ) && ( $udt_obj->getColumn( 'total_time_amount' ) != 0 || $udt_obj->getColumn( 'total_time_amount_with_burden' ) != 0 ) ) { //Exclude worked time from gross wage total.
							if ( isset( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage'] ) ) {
								$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage'] = TTMath::add( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage'], $udt_obj->getColumn( 'total_time_amount' ) );
								$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage_with_burden'] = TTMath::add( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage_with_burden'], $udt_obj->getColumn( 'total_time_amount_with_burden' ) );
							} else {
								$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage'] = $udt_obj->getColumn( 'total_time_amount' );
								$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage_with_burden'] = $udt_obj->getColumn( 'total_time_amount_with_burden' );
							}

							if ( isset( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_hourly_rate'] ) && $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage'] != 0 && $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_time'] != 0 ) {
								$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_hourly_rate'] = TTMath::div( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage'], TTDate::getHours( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_time'] ) );
								$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_hourly_rate_with_burden'] = TTMath::div( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_wage_with_burden'], TTDate::getHours( $this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_time'] ) );
							} else {
								$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_hourly_rate'] = $udt_obj->getColumn( 'hourly_rate' );
								$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id][$column . '_hourly_rate_with_burden'] = $udt_obj->getColumn( 'hourly_rate_with_burden' );
							}
						}

						//Worked Days is tricky, since if they worked in multiple branches/departments in a single day, is that considered one worked day?
						//How do they find out how many days they worked in each branch/department though? It would add up to more days than they actually worked.
						//If we did some sort of partial day though, then due to rounding it could be thrown off, but either way it woulnd't be that helpful because
						//it would show they worked .33 of a day in one branch if they filtered by that branch.
						if ( $column == 'worked' && $udt_obj->getColumn( 'total_time' ) > 0 && !isset( $this->tmp_data['worked_days'][$user_id . $date_stamp] ) ) {
							$this->tmp_data['user_date_total'][$user_id][$date_stamp][$branch_id][$department_id]['worked_days'] = 1;
							$this->tmp_data['worked_days'][$user_id . $date_stamp] = true;
						}
					}
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}
		//Debug::Arr($this->tmp_data['user_date_total'], 'User Date Total Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $columns['schedule_working'] ) || isset( $columns['schedule_working_diff'] ) || isset( $columns['schedule_absence'] ) ) {
			$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
			//$slf->getDayReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			//$slf->getScheduleSummaryReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			$slf->getSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			if ( $slf->getRecordCount() > 0 ) {
				foreach ( $slf as $s_obj ) {
					$status = strtolower( Option::getByKey( $s_obj->getColumn( 'status_id' ), $s_obj->getOptions( 'status' ) ) );

					//Check if the user worked on any of the scheduled days, if not insert a dummy day so the scheduled time at least appears still.
					if ( !isset( $this->tmp_data['user_date_total'][TTUUID::castUUID( $s_obj->getUser() )][TTDate::strtotime( $s_obj->getColumn( 'date_stamp' ) )][$s_obj->getColumn( 'branch_id' )][$s_obj->getColumn( 'department_id' )] ) ) {
						$this->tmp_data['user_date_total'][TTUUID::castUUID( $s_obj->getUser() )][TTDate::strtotime( $s_obj->getColumn( 'date_stamp' ) )][$s_obj->getColumn( 'branch_id' )][$s_obj->getColumn( 'department_id' )] = [
								'branch_id'                   => $s_obj->getColumn( 'branch_id' ),
								'department_id'               => $s_obj->getColumn( 'department_id' ),
								'pay_period_start_date'       => strtotime( $s_obj->getColumn( 'pay_period_start_date' ) ),
								'pay_period_end_date'         => strtotime( $s_obj->getColumn( 'pay_period_end_date' ) ),
								'pay_period_transaction_date' => strtotime( $s_obj->getColumn( 'pay_period_transaction_date' ) ),
								'pay_period'                  => strtotime( $s_obj->getColumn( 'pay_period_transaction_date' ) ),
								'pay_period_id'               => $s_obj->getColumn( 'pay_period_id' ),
						];
					}

					//Make sure we handle multiple schedules on the same day.
					if ( isset( $this->tmp_data['schedule'][TTUUID::castUUID( $s_obj->getUser() )][TTDate::strtotime( $s_obj->getColumn( 'date_stamp' ) )][$s_obj->getColumn( 'branch_id' )][$s_obj->getColumn( 'department_id' )]['schedule_' . $status] ) ) {
						$this->tmp_data['schedule'][TTUUID::castUUID( $s_obj->getUser() )][TTDate::strtotime( $s_obj->getColumn( 'date_stamp' ) )][$s_obj->getColumn( 'branch_id' )][$s_obj->getColumn( 'department_id' )]['schedule_' . $status] = TTMath::add( $this->tmp_data['schedule'][TTUUID::castUUID( $s_obj->getUser() )][TTDate::strtotime( $s_obj->getColumn( 'date_stamp' ) )][$s_obj->getColumn( 'branch_id' )][$s_obj->getColumn( 'department_id' )]['schedule_' . $status], $s_obj->getColumn( 'total_time' ) );
					} else {
						$this->tmp_data['schedule'][TTUUID::castUUID( $s_obj->getUser() )][TTDate::strtotime( $s_obj->getColumn( 'date_stamp' ) )][$s_obj->getColumn( 'branch_id' )][$s_obj->getColumn( 'department_id' )]['schedule_' . $status] = $s_obj->getColumn( 'total_time' );
					}
				}
			}
			//Debug::Arr($this->tmp_data['schedule'], 'Schedule Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
			unset( $slf, $s_obj, $status );
		}

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$this->getColumnDataConfig(), [ 'title_id' => true, 'default_branch_id' => true, 'default_department_id' => true ] ) );

			if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
				$this->tmp_data['user'][$u_obj->getId()]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
				$this->tmp_data['user'][$u_obj->getId()]['currency_rate'] = $u_obj->getColumn( 'currency_rate' );
			} else {
				$this->tmp_data['user'][$u_obj->getId()]['current_currency'] = $u_obj->getColumn( 'currency' );
			}

			//User doesn't have any UDT rows, add a blank one.
			if ( ( isset( $filter_data['include_no_data_rows'] ) && $filter_data['include_no_data_rows'] == 1 && !isset( $this->tmp_data['user_date_total'][$u_obj->getId()] ) ) ) {
				foreach ( $include_no_data_rows_arr as $tmp_pay_period_id => $tmp_date_stamps ) {
					foreach ( $tmp_date_stamps as $tmp_date_stamp => $tmp_pay_period_data ) {
						$this->tmp_data['user_date_total'][$u_obj->getId()][$tmp_date_stamp][0][0] = $tmp_pay_period_data;
					}
				}
				unset( $tmp_pay_period_id, $tmp_date_stamps, $tmp_date_stamp, $tmp_pay_period_data );
			}

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		unset( $include_no_data_rows_arr );

		$utlf = TTnew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
		$utlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' User Title Total Rows: ' . $utlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$user_title_column_config = array_merge( (array)Misc::removeKeyPrefix( 'user_title_', (array)$this->getColumnDataConfig() ), [ 'id' => true, 'name' => true, 'custom_field' => true ] ); //Always include title_id column so we can merge title data.
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $utlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Titles...' ) );
		foreach ( $utlf as $key => $ut_obj ) {
			$this->tmp_data['user_title'][$ut_obj->getId()] = Misc::addKeyPrefix( 'user_title_', (array)$ut_obj->getObjectAsArray( $user_title_column_config ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' Branch Total Rows: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $blf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $blf as $key => $b_obj ) {
			$this->tmp_data['default_branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'default_branch_', (array)$b_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'manual_id' => true, 'custom_field' => true ] ) );
			$this->tmp_data['branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'branch_', (array)$b_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'manual_id' => true, 'custom_field' => true ] ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['default_branch'], 'Default Branch Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
		$dlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' Department Total Rows: ' . $dlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $dlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $dlf as $key => $d_obj ) {
			$this->tmp_data['default_department'][$d_obj->getId()] = Misc::addKeyPrefix( 'default_department_', (array)$d_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'manual_id' => true, 'custom_field' => true ] ) );
			$this->tmp_data['department'][$d_obj->getId()] = Misc::addKeyPrefix( 'department_', (array)$d_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'manual_id' => true, 'custom_field' => true ] ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['default_department'], 'Default Department Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data['department'], 'Department Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get verified timesheets for all pay periods considered in report.
		$pay_period_ids = array_keys( $pay_period_ids );
		if ( isset( $pay_period_ids ) && count( $pay_period_ids ) > 0 ) {
			$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
			$pptsvlf->getByPayPeriodIdAndCompanyId( $pay_period_ids, $this->getUserObject()->getCompany() );
			if ( $pptsvlf->getRecordCount() > 0 ) {
				foreach ( $pptsvlf as $pptsv_obj ) {
					$this->tmp_data['verified_timesheet'][$pptsv_obj->getUser()][$pptsv_obj->getPayPeriod()] = [
							'status'       => $pptsv_obj->getVerificationStatusShortDisplay(),
							'created_date' => $pptsv_obj->getCreatedDate(),
					];
				}
			}
		}

		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['user_date_total'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

		//Merge time data with user data
		$key = 0;

		if ( isset( $this->tmp_data['user_date_total'] ) ) {
			$pptsvf = TTnew('PayPeriodTimeSheetVerifyFactory');

			$column_keys = array_keys( (array)$this->getColumnDataConfig() );

			foreach ( $this->tmp_data['user_date_total'] as $user_id => $level_1 ) {
				if ( isset( $this->tmp_data['user'][$user_id] ) ) {
					foreach ( $level_1 as $date_stamp => $level_2 ) {
						foreach ( $level_2 as $branch => $level_3 ) {
							foreach ( $level_3 as $department => $row ) {

								$date_columns = TTDate::getReportDates( null, $date_stamp, false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ], $column_keys );
								$processed_data = [];

								if ( isset( $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']] ) ) {
									$processed_data['verified_time_sheet'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['status'];
									$processed_data['verified_time_sheet_date'] = $this->tmp_data['verified_timesheet'][$user_id][$row['pay_period_id']]['created_date'];
								} else {
									$processed_data['verified_time_sheet'] = $pptsvf->getVerificationStatusShortDisplay( null, $row['pay_period_id'] );
									$processed_data['verified_time_sheet_date'] = false;
								}

								if ( !isset( $row['worked_time'] ) ) {
									$row['worked_time'] = 0;
								}

								if ( isset( $this->tmp_data['schedule'][$user_id][$date_stamp][$branch][$department]['schedule_working'] ) ) {
									$processed_data['schedule_working'] = $this->tmp_data['schedule'][$user_id][$date_stamp][$branch][$department]['schedule_working'];
									$processed_data['schedule_working_diff'] = ( $row['worked_time'] - $this->tmp_data['schedule'][$user_id][$date_stamp][$branch][$department]['schedule_working'] );
									//We can only include scheduled_time once per user/date combination. Otherwise its duplicates the amounts and makes it incorrect.
									//So once its used unset it so it can't be used again.
									unset( $this->tmp_data['schedule'][$user_id][$date_stamp][$branch][$department]['schedule_working'] );
								} else {
									$processed_data['schedule_working'] = 0;
									$processed_data['schedule_working_diff'] = $row['worked_time'];
								}
								if ( isset( $this->tmp_data['schedule'][$user_id][$date_stamp][$branch][$department]['schedule_absent'] ) ) {
									$processed_data['schedule_absent'] = $this->tmp_data['schedule'][$user_id][$date_stamp][$branch][$department]['schedule_absent'];
									unset( $this->tmp_data['schedule'][$user_id][$date_stamp][$branch][$department]['schedule_absent'] );
								} else {
									$processed_data['schedule_absent'] = 0;
								}

								if ( isset( $this->tmp_data['user'][$user_id]['default_branch_id'] ) && isset( $this->tmp_data['default_branch'][$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) {
									$tmp_default_branch = $this->tmp_data['default_branch'][$this->tmp_data['user'][$user_id]['default_branch_id']];
								} else {
									$tmp_default_branch = [];
								}
								if ( isset( $this->tmp_data['user'][$user_id]['default_department_id'] ) && isset( $this->tmp_data['default_department'][$this->tmp_data['user'][$user_id]['default_department_id']] ) ) {
									$tmp_default_department = $this->tmp_data['default_department'][$this->tmp_data['user'][$user_id]['default_department_id']];
								} else {
									$tmp_default_department = [];
								}

								if ( isset( $this->tmp_data['user'][$user_id]['title_id'] ) && isset( $this->tmp_data['user_title'][$this->tmp_data['user'][$user_id]['title_id']] ) ) {
									$tmp_user_title = $this->tmp_data['user_title'][$this->tmp_data['user'][$user_id]['title_id']];
								} else {
									$tmp_user_title = [];
								}

								if ( isset( $this->tmp_data['branch'][$row['branch_id']] ) ) {
									$tmp_branch = $this->tmp_data['branch'][$row['branch_id']];
								} else {
									$tmp_branch = [];
								}
								if ( isset( $this->tmp_data['department'][$row['department_id']] ) ) {
									$tmp_department = $this->tmp_data['department'][$row['department_id']];
								} else {
									$tmp_department = [];
								}

								$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $tmp_default_branch, $tmp_default_department, $tmp_branch, $tmp_department, $tmp_user_title, $row, $date_columns, $processed_data );

								$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
								$key++;
							}
						}
					}
				}
			}
			unset( $this->tmp_data, $row, $date_columns, $processed_data, $level_1, $level_2, $level_3 );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}
}

?>