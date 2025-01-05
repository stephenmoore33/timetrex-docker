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
class UserSummaryReport extends Report {

	/**
	 * UserSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Employee Summary Report' );
		$this->file_name = 'employee_summary_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_user_information', $user_id, $company_id ) ) {
			return true;
		}

		return false;
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
						//'time_period',
						'columns',
				];
				break;
			case 'setup_fields':
				$retval = [
					//Static Columns - Aggregate functions can't be used on these.
					'-1000-template'                     => TTi18n::gettext( 'Template' ),
					'-1010-time_period'                  => TTi18n::gettext( 'Employed Time Period (Full)' ), //Employed within the entire start/end date.
					'-1015-partial_employed_time_period' => TTi18n::gettext( 'Employed Time Period (Partial)' ), //Employed within part of the start/end date.
					'-1017-wage_effective_time_period'   => TTi18n::gettext( 'Wage Effective Time Period' ), //Wages effective as of the End Date.
					'-1020-hire_time_period'             => TTi18n::gettext( 'Hired Time Period' ), //Hired within this start/end date.
					'-1030-termination_time_period'      => TTi18n::gettext( 'Terminated Time Period' ), //Terminated within this start/end date.
					'-1040-birth_time_period'            => TTi18n::gettext( 'Birth Date Time Period' ), //Born within this start/end date
					'-1090-last_login_time_period'       => TTi18n::gettext( 'Last Sign In Time Period' ), //Last login within this start/end date.
					'-1095-password_time_period'         => TTi18n::gettext( 'Password Time Period' ), //Password change within this start/end date.
					//'-1098-last_wage_time_period' => TTi18n::gettext('Last Wage Time Period'), //Wage change effective within this start/end date.

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
					'-2000-currency_id'           => TTi18n::gettext( 'Currency' ),
					'-2100-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),

					'-5000-columns'    => TTi18n::gettext( 'Display Columns' ),
					'-5010-group'      => TTi18n::gettext( 'Group By' ),
					'-5020-sub_total'  => TTi18n::gettext( 'SubTotal By' ),
					'-5030-sort'       => TTi18n::gettext( 'Sort By' ),
					'-5040-page_break' => TTi18n::gettext( 'Page Break On' ),
				];
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = array_merge(
						TTDate::getReportDateOptions( 'hire', TTi18n::getText( 'Hire Date' ), 17, false ),
						TTDate::getReportDateOptions( 'termination', TTi18n::getText( 'Termination Date' ), 18, false ),
						TTDate::getReportDateOptions( 'birth', TTi18n::getText( 'Birth Date' ), 19, false ),
						TTDate::getReportDateOptions( 'created', TTi18n::getText( 'Created Date' ), 20, false ),
						TTDate::getReportDateOptions( 'updated', TTi18n::getText( 'Updated Date' ), 21, false )
				);
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$retval = $this->getCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users', 'user_title', 'branch', 'department', 'job', 'job_item'], ['users'] );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'UserSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'UserSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'UserSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'UserSummaryReport', 'custom_column' );
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
					'-1000-legal_name'  => TTi18n::gettext( 'Legal Entity Name' ),
					'-1001-trade_name'  => TTi18n::gettext( 'Legal Entity Trade Name' ),
					'-1002-first_name'  => TTi18n::gettext( 'First Name' ),
					'-1003-middle_name' => TTi18n::gettext( 'Middle Name' ),
					'-1004-last_name'   => TTi18n::gettext( 'Last Name' ),
					'-1005-full_name'   => TTi18n::gettext( 'Full Name' ),

					'-1010-user_name' => TTi18n::gettext( 'User Name' ),
					'-1020-phone_id'  => TTi18n::gettext( 'Quick Punch ID' ),

					'-1030-employee_number'         => TTi18n::gettext( 'Employee #' ),
					'-1032-employee_number_barcode' => TTi18n::gettext( 'Barcode' ),
					'-1034-employee_number_qrcode'  => TTi18n::gettext( 'QRcode' ),

					'-1038-user_photo' => TTi18n::gettext( 'Photo' ),

					'-1040-status'     => TTi18n::gettext( 'Status' ),
					'-1050-title'      => TTi18n::gettext( 'Title' ),
					//'-1060-province' => TTi18n::gettext('Province/State'),
					//'-1070-country' => TTi18n::gettext('Country'),
					'-1080-user_group' => TTi18n::gettext( 'Group' ),

					'-1090-default_branch'               => TTi18n::gettext( 'Branch' ), //abbreviate for space
					'-1091-default_branch_manual_id'     => TTi18n::gettext( 'Branch Code' ),
					'-1100-default_department'           => TTi18n::gettext( 'Department' ), //abbreviate for space
					'-1101-default_department_manual_id' => TTi18n::gettext( 'Department Code' ),
					'-1190-ethnic_group'                 => TTi18n::gettext( 'Ethnicity' ),

					'-1200-permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1210-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1220-policy_group'        => TTi18n::gettext( 'Policy Group' ),

					'-1310-sex'      => TTi18n::gettext( 'Gender' ),
					'-1320-address1' => TTi18n::gettext( 'Address 1' ),
					'-1330-address2' => TTi18n::gettext( 'Address 2' ),

					'-1340-city'           => TTi18n::gettext( 'City' ),
					'-1350-province'       => TTi18n::gettext( 'Province/State' ),
					'-1360-country'        => TTi18n::gettext( 'Country' ),
					'-1370-postal_code'    => TTi18n::gettext( 'Postal Code' ),
					'-1380-work_phone'     => TTi18n::gettext( 'Work Phone' ),
					'-1391-work_phone_ext' => TTi18n::gettext( 'Work Phone Ext' ),
					'-1400-home_phone'     => TTi18n::gettext( 'Home Phone' ),
					'-1410-mobile_phone'   => TTi18n::gettext( 'Mobile Phone' ),
					'-1420-fax_phone'      => TTi18n::gettext( 'Fax Phone' ),
					'-1430-home_email'     => TTi18n::gettext( 'Home Email' ),
					'-1440-work_email'     => TTi18n::gettext( 'Work Email' ),
					'-1480-sin'            => TTi18n::gettext( 'SIN/SSN' ),
					'-1490-note'           => TTi18n::gettext( 'Note' ),

					'-1495-tag'                       => TTi18n::gettext( 'Tags' ),
					'-1499-hierarchy_control_display' => TTi18n::gettext( 'Hierarchy' ),
					'-1499-hierarchy_level_display'   => TTi18n::gettext( 'Hierarchy Superiors' ),

					'-1500-institution' => TTi18n::gettext( 'Bank Institution' ),
					'-1510-transit'     => TTi18n::gettext( 'Bank Transit/Routing' ),
					'-1520-account'     => TTi18n::gettext( 'Bank Account' ),

					'-1619-currency'         => TTi18n::gettext( 'Currency' ),
					'-1620-current_currency' => TTi18n::gettext( 'Current Currency' ),
					'-1625-type'             => TTi18n::gettext( 'Wage Type' ),
					'-1640-effective_date'   => TTi18n::gettext( 'Wage Effective Date' ),

					'-1650-language_display'         => TTi18n::gettext( 'Language' ),
					'-1660-date_format_display'      => TTi18n::gettext( 'Date Format' ),
					'-1665-time_format_display'      => TTi18n::gettext( 'Time Format' ),
					'-1670-time_unit_format_display' => TTi18n::gettext( 'Time Units' ),
					'-1680-time_zone_display'        => TTi18n::gettext( 'Time Zone' ),
					'-1690-items_per_page'           => TTi18n::gettext( 'Rows Per page' ),

					'-1695-last_login_date'       => TTi18n::gettext( 'Last Sign In Date' ),
					'-1696-max_punch_time_stamp'  => TTi18n::gettext( 'Last Punch Time' ),
					'-1697-password_updated_date' => TTi18n::gettext( 'Password Updated Date' ),

					'-1699-hire_date_age'  => TTi18n::gettext( 'Length of Service' ),
					'-1899-birth_date_age' => TTi18n::gettext( 'Age' ),

					'-2205-created_by' => TTi18n::gettext( 'Created By' ),
					'-2215-updated_by' => TTi18n::gettext( 'Updated By' ),
				];

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval['-1120-default_job'] = TTi18n::gettext( 'Job' );
					$retval['-1121-default_job_manual_id'] = TTi18n::gettext( 'Job Code' );
					$retval['-1125-default_job_item'] = TTi18n::gettext( 'Task' );
					$retval['-1126-default_job_item_manual_id'] = TTi18n::gettext( 'Task Code' );
				}

				$retval = array_merge( $retval, (array)$this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users', 'user_title', 'branch', 'department', 'job', 'job_item'], ['users'] ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-1630-wage'                     => TTi18n::gettext( 'Wage' ),
					'-1635-hourly_rate'              => TTi18n::gettext( 'Hourly Rate' ),
					'-1636-labor_burden_hourly_rate' => TTi18n::gettext( 'Hourly Rate w/Burden' ),
					'-1637-labor_burden_percent'     => TTi18n::gettext( 'Labor Burden Percent' ),

					'-2000-total_user' 				 => TTi18n::gettext( 'Total Employees' ), //Group counter...
					'-2010-total_user_hired_before_period' => TTi18n::gettext( 'Total Employees (Hired before Period)' ), //Group counter: Hire Date is before filter Start Date.
					'-2020-total_user_hired_in_period' => TTi18n::gettext( 'Total Employees (Hired in Period)' ), //Group counter: Hire Date is after filter Start Date.
					'-2030-total_user_terminated_in_period'=> TTi18n::gettext( 'Total Employees (Terminated in Period)' ), //Group counter: Terminated Date is after filter Start Date and before filter End Date
					'-2040-total_user_employed_entire_period'=> TTi18n::gettext( 'Total Employees (Employed Entire Period)' ), //Group counter: Hired Date is before filter Start Date and Terminated Date is blank.
					'-2100-retention_rate' => TTi18n::gettext( 'Employee Retention Rate' ),
					'-2110-turnover_rate' => TTi18n::gettext( 'Employee Turnover Rate' ),
				];

				$retval = array_merge( $retval, $this->getDynamicCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users', 'user_title', 'branch', 'department', 'job', 'job_item'], ['users'] ) );
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
						if ( strpos( $column, 'wage' ) !== false || strpos( $column, 'hourly_rate' ) !== false ) {
							$retval[$column] = 'currency';
						} else if ( strpos( $column, 'labor_burden_percent' ) !== false || strpos( $column, 'retention_rate' ) !== false || strpos( $column, 'turnover_rate' ) !== false ) {
							$retval[$column] = 'percent';
						} else if ( strpos( $column, 'total_user' ) !== false ) {
							$retval[$column] = 'numeric';
						}
					}
				}
				$retval['password_updated_date'] = $retval['max_punch_time_stamp'] = 'time_stamp';
				$retval['effective_date'] = $retval['last_login_date'] = 'date_stamp';
				$retval['user_photo'] = $retval['employee_number_qrcode'] = $retval['employee_number_barcode'] = 'html';
				break;
			case 'aggregates':
				$retval = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, 'hourly_rate' ) !== false || strpos( $column, 'wage' ) !== false || strpos( $column, 'labor_burden_percent' ) !== false || strpos( $column, 'retention_rate' ) !== false || strpos( $column, 'turnover_rate' ) !== false ) {
									$retval[$column] = 'avg';
								} else {
									$retval[$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'templates':
				$retval = [
						'-1010-by_employee+contact' => TTi18n::gettext( 'Contact Information by Employee' ),

						'-1020-by_employee+employment' => TTi18n::gettext( 'Employment Information by Employee' ), //Branch, Department, Title, Group, Hire Date?
						'-1025-by_employee+new_hire'   => TTi18n::gettext( 'New Hires by Employee' ),

						'-1030-by_employee+address' => TTi18n::gettext( 'Addresses by Employee' ),
						'-1040-by_employee+wage'    => TTi18n::gettext( 'Wages by Employee' ),

						'-1050-by_employee+bank'       => TTi18n::gettext( 'Bank Information by Employee' ),
						'-1060-by_employee+preference' => TTi18n::gettext( 'Preferences by Employee' ),
						//'-1020-by_employee+deduction' => TTi18n::gettext('Deductions by Employee'),
						'-1070-by_employee+birth_date' => TTi18n::gettext( 'Birthdays by Employee' ),

						'-1080-by_branch_by_employee+contact' => TTi18n::gettext( 'Contact Information by Branch/Employee' ),
						'-1090-by_branch_by_employee+address' => TTi18n::gettext( 'Addresses by Branch/Employee' ),
						'-1110-by_branch_by_employee+wage'    => TTi18n::gettext( 'Wages by Branch/Employee' ),
						'-1120-by_branch+total_user'          => TTi18n::gettext( 'Total Employees by Branch' ),

						'-1130-by_department_by_employee+contact' => TTi18n::gettext( 'Contact Information by Department/Employee' ),
						'-1140-by_department_by_employee+address' => TTi18n::gettext( 'Addresses by Department/Employee' ),
						'-1150-by_department_by_employee+wage'    => TTi18n::gettext( 'Wages by Department/Employee' ),
						'-1160-by_department+total_user'          => TTi18n::gettext( 'Total Employees by Department' ),

						'-1170-by_branch_by_department_by_employee+contact' => TTi18n::gettext( 'Contact Information by Branch/Department/Employee' ),
						'-1180-by_branch_by_department_by_employee+address' => TTi18n::gettext( 'Addresses by Branch/Department/Employee' ),
						'-1190-by_branch_by_department_by_employee+wage'    => TTi18n::gettext( 'Wages by Branch/Department/Employee' ),
						'-1200-by_branch_by_department+total_user'          => TTi18n::gettext( 'Total Employees by Branch/Department' ),

						'-1205-by_hierarchy_by_branch_by_department_by_employee+contact' => TTi18n::gettext( 'Contact Information by Hierarchy/Branch/Department/Employee' ),

						'-1210-by_type_by_employee+wage' => TTi18n::gettext( 'Wages by Type/Employee' ),
						'-1220-by_type+total_user'       => TTi18n::gettext( 'Total Employees by Wage Type' ),


						'-1230-by_hired_month+total_user'       => TTi18n::gettext( 'Total Employees Hired by Month' ),
						'-1240-by_termination_month+total_user' => TTi18n::gettext( 'Total Employees Terminated by Month' ),
						'-1250-by_branch_by_department+retention_rate+turnover_rate' => TTi18n::gettext( 'Retention/Turnover by Branch/Department' ),

						'-1300-by_sex_ethnic_group_user_title+total_user' => TTi18n::gettext( 'Total Employees by Gender/Ethnicity/Title' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {

						//Contact
						case 'by_employee+contact':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_branch_by_employee+contact':
							$retval['columns'][] = 'default_branch';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_department_by_employee+contact':
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_branch_by_department_by_employee+contact':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_hierarchy_by_branch_by_department_by_employee+contact':
							$retval['columns'][] = 'hierarchy_control_display';
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'work_phone';
							$retval['columns'][] = 'work_phone_ext';
							$retval['columns'][] = 'work_email';
							$retval['columns'][] = 'mobile_phone';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'home_email';

							$retval['sort'][] = [ 'hierarchy_control_display' => 'asc' ];
							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;

						//Birth Dates
						case 'by_employee+birth_date':
							$retval['columns'][] = 'birth-date_month';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'birth-date_stamp';
							$retval['columns'][] = 'birth_date_age';

							$retval['sub_total'][] = 'birth-date_month';

							$retval['sort'][] = [ 'birth-date_month' => 'asc' ];
							$retval['sort'][] = [ 'birth-date_dom' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;

						//Employment
						case 'by_employee+employment':
							$retval['columns'][] = 'status';
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';
							$retval['columns'][] = 'title';
							$retval['columns'][] = 'user_group';
							$retval['columns'][] = 'ethnic_group';
							$retval['columns'][] = 'sex';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'birth_date_age';

							$retval['columns'][] = 'hire-date_stamp';
							$retval['columns'][] = 'termination-date_stamp';
							$retval['columns'][] = 'hire_date_age';

							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'title' => 'asc' ];
							$retval['sort'][] = [ 'user_group' => 'asc' ];
							$retval['sort'][] = [ 'ethnic_group' => 'asc' ];
							$retval['sort'][] = [ 'sex' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							//$retval['sort'][] = array('hire-date_month' => 'asc');
							break;
						case 'by_hired_month+total_user':
							$retval['columns'][] = 'hire-date_year';
							$retval['columns'][] = 'hire-date_month_year';
							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'hire-date_year';
							$retval['group'][] = 'hire-date_month_year';

							$retval['sub_total'][] = 'hire-date_year';

							$retval['sort'][] = [ 'hire-date_year' => 'desc' ];
							$retval['sort'][] = [ 'hire-date_month_year' => 'desc' ];
							$retval['sort'][] = [ 'total_user' => 'desc' ];
							break;
						case 'by_termination_month+total_user':
							$retval['columns'][] = 'termination-date_year';
							$retval['columns'][] = 'termination-date_month_year';
							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'termination-date_year';
							$retval['group'][] = 'termination-date_month_year';

							$retval['sub_total'][] = 'termination-date_year';

							$retval['sort'][] = [ 'termination-date_year' => 'desc' ];
							$retval['sort'][] = [ 'termination-date_month_year' => 'desc' ];
							$retval['sort'][] = [ 'total_user' => 'desc' ];
							break;
						case 'by_sex_ethnic_group_user_title+total_user': //Similar to US EEO-1 without component 2 data.
							$retval['partial_employed_time_period']['time_period'] = 'custom_pay_period';

							$retval['columns'][] = 'sex';
							$retval['columns'][] = 'ethnic_group';
							$retval['columns'][] = 'title';
							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'sex';
							$retval['group'][] = 'ethnic_group';
							$retval['group'][] = 'title';

							$retval['sub_total'][] = 'sex';
							$retval['sub_total'][] = 'ethnic_group';
							$retval['sub_total'][] = 'title';

							$retval['sort'][] = [ 'sex' => 'asc' ];
							$retval['sort'][] = [ 'ethnic_group' => 'asc' ];
							$retval['sort'][] = [ 'title' => 'asc' ];
							break;

						//Address
						case 'by_employee+address':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'postal_code';

							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_branch_by_employee+address':
							$retval['columns'][] = 'default_branch';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'postal_code';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_department_by_employee+address':
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'postal_code';

							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_branch_by_department_by_employee+address':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'postal_code';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;

						//Wage
						case 'by_employee+wage':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'wage' => 'desc' ];
							break;
						case 'by_branch_by_employee+wage':
							$retval['columns'][] = 'default_branch';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'wage' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_department_by_employee+wage':
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'wage' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_branch_by_department_by_employee+wage':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'wage' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_type_by_employee+wage':
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'wage';
							$retval['columns'][] = 'hourly_rate';
							$retval['columns'][] = 'effective_date';

							$retval['sub_total'][] = 'type';

							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'wage' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;

						//Bank Account
						case 'by_employee+bank':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'transit';
							$retval['columns'][] = 'account';
							$retval['columns'][] = 'institution';

							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;

						//Preferences
						case 'by_employee+preference':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'date_format_display';
							$retval['columns'][] = 'time_format_display';
							$retval['columns'][] = 'time_unit_format_display';
							$retval['columns'][] = 'time_zone_display';
							$retval['columns'][] = 'language_display';
							$retval['columns'][] = 'items_per_page';

							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;

						//Other
						case 'by_branch+total_user':
							$retval['columns'][] = 'default_branch';

							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'default_branch';

							$retval['sort'][] = [ 'total_user' => 'desc' ];
							break;
						case 'by_department+total_user':
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'default_department';

							$retval['sort'][] = [ 'total_user' => 'desc' ];
							break;
						case 'by_branch_by_department+total_user':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'default_branch';
							$retval['group'][] = 'default_department';

							$retval['sub_total'][] = 'default_branch';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							//$retval['sort'][] = array('' => 'asc');
							$retval['sort'][] = [ 'total_user' => 'desc' ];
							break;
						case 'by_branch_by_department+retention_rate+turnover_rate':
							$retval['partial_employed_time_period']['time_period'] = 'last_year';

							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';

							$retval['columns'][] = 'retention_rate';
							$retval['columns'][] = 'turnover_rate';

							$retval['group'][] = 'default_branch';
							$retval['group'][] = 'default_department';

							$retval['sub_total'][] = 'default_branch';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'default_department' => 'asc' ];
							break;
						case 'by_type+total_user':
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'total_user';

							$retval['group'][] = 'type';

							$retval['sub_total'][] = 'type';

							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'total_user' => 'desc' ];
							break;
						//Employment
						case 'by_employee+new_hire':
							$retval['-1003-hire_time_period']['time_period'] = 'last_14_days';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'middle_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'sin';
							$retval['columns'][] = 'address1';
							$retval['columns'][] = 'address2';
							$retval['columns'][] = 'city';
							$retval['columns'][] = 'province';
							$retval['columns'][] = 'country';
							$retval['columns'][] = 'postal_code';
							$retval['columns'][] = 'home_phone';
							$retval['columns'][] = 'sex';
							$retval['columns'][] = 'title';

							$retval['columns'][] = 'birth-date_stamp';
							$retval['columns'][] = 'hire-date_stamp';

							//Wage information
							$retval['columns'][] = 'pay_period_schedule';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'wage';

							$retval['columns'][] = 'user_group';
							$retval['columns'][] = 'tag';

							$retval['sort'][] = [ 'hire-date_stamp' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							break;
						default:
							Debug::Text( ' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );
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
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'user' => [], 'user_preference' => [], 'user_wage' => [], 'user_bank' => [], 'branch' => [], 'department' => [], 'job' => [], 'job_item' => [], 'total_user' => [] ];

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		$currency_convert_to_base = $this->getCurrencyConvertToBase();
		$base_currency_obj = $this->getBaseCurrencyObject();
		$this->handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data );
		$currency_options = $this->getOptions( 'currency' );

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$wage_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		//Rename start/end_date to employed_start/end_date, this prevents other reports like JobDetail from sending the start/end to the UserListFactory which may cause no records to be returned.
		if ( isset( $filter_data['start_date'] ) ) {
			$filter_data['employed_start_date'] = $filter_data['start_date'];
		}
		if ( isset( $filter_data['end_date'] ) ) {
			$filter_data['employed_end_date'] = $filter_data['end_date'];
		}

		if ( isset( $filter_data['partial_employed_start_date'] ) ) {
			$total_user_start_date = $filter_data['partial_employed_start_date'];
		} else if ( isset( $filter_data['employed_start_date'] ) ) {
			$total_user_start_date = $filter_data['employed_start_date'];
		} else if ( isset( $filter_data['hire_start_date'] ) ) {
			$total_user_start_date = $filter_data['hire_start_date'];
		} else if ( isset( $filter_data['termination_start_date'] ) ) {
			$total_user_start_date = $filter_data['termination_start_date'];
		} else {
			$total_user_start_date = 0; //Epoch start time (ie: 1969)
		}

		if ( isset( $filter_data['partial_employed_end_date'] ) ) {
			$total_user_end_date = $filter_data['partial_employed_end_date'];
		} else if ( isset( $filter_data['employed_end_date'] ) ) {
			$total_user_end_date = $filter_data['employed_end_date'];
		} else if ( isset( $filter_data['hire_end_date'] ) ) {
			$total_user_end_date = $filter_data['hire_end_date'];
		} else if ( isset( $filter_data['termination_end_date'] ) ) {
			$total_user_end_date = $filter_data['termination_end_date'];
		} else {
			$total_user_end_date = time();
		}

		//Always include date columns, because 'hire-date_stamp' is not recognized by the UserFactory. This greatly slows down the report though.
		$columns['effective_date'] = $columns['hire_date'] = $columns['termination_date'] = $columns['birth_date'] = $columns['created_date'] = $columns['updated_date'] = true;

		$include_last_punch_time = ( isset( $columns['max_punch_time_stamp'] ) ) ? true : false;

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, null, null, null, null, $include_last_punch_time );
		Debug::Text( ' User Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			//We used to just get return the entire $u_obj->data array, but this wouldn't include tags and other columns that required some additional processing.
			//Not sure why this was done that way... I think because we had problems with the multiple date fields (Hire Date/Termination Date/Birth Date, etc...)
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$columns, [ 'title_id' => true, 'default_branch_id' => true, 'default_department_id' => true, 'default_job_id' => true, 'default_job_item_id' => true ] ) );

			if ( isset( $this->tmp_data['user'][$u_obj->getId()]['last_login_date'] ) ) {
				$this->tmp_data['user'][$u_obj->getId()]['last_login_date'] = TTDate::parseDateTime( $this->tmp_data['user'][$u_obj->getId()]['last_login_date'] );
			}
			if ( isset( $this->tmp_data['user'][$u_obj->getId()]['max_punch_time_stamp'] ) ) {
				$this->tmp_data['user'][$u_obj->getId()]['max_punch_time_stamp'] = TTDate::parseDateTime( $this->tmp_data['user'][$u_obj->getId()]['max_punch_time_stamp'] );
			}

			if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
				$this->tmp_data['user'][$u_obj->getId()]['currency_rate'] = $u_obj->getColumn( 'currency_rate' );
			}

			$this->tmp_data['user'][$u_obj->getId()]['employee_number'] = isset( $columns['employee_number'] ) ? $this->tmp_data['user'][$u_obj->getId()]['employee_number'] : $u_obj->getEmployeeNumber();
			if ( isset( $columns['employee_number_barcode'] ) ) {
				$this->tmp_data['user'][$u_obj->getId()]['employee_number_barcode'] = new ReportCellBarcode( $this, 'U' . $this->tmp_data['user'][$u_obj->getId()]['employee_number'] );
			}
			if ( isset( $columns['employee_number_qrcode'] ) ) {
				$this->tmp_data['user'][$u_obj->getId()]['employee_number_qrcode'] = new ReportCellQRcode( $this, 'U' . $this->tmp_data['user'][$u_obj->getId()]['employee_number'] );
			}
			if ( isset( $columns['user_photo'] ) && $u_obj->isPhotoExists() ) {
				$this->tmp_data['user'][$u_obj->getId()]['user_photo'] = new ReportCellImage( $this, array( 'local_file' => $u_obj->getPhotoFileName( $u_obj->getCompany(), $u_obj->getID(), false ), 'url' => Environment::getBaseURL() . '/send_file.php?object_type=user_photo&object_id=' . $u_obj->getId() ) );
			}

			$this->tmp_data['user_preference'][$u_obj->getId()] = [];
			$this->tmp_data['user_wage'][$u_obj->getId()] = [];

			$this->tmp_data['user'][$u_obj->getId()]['total_user'] = 1;
			$this->tmp_data['user'][$u_obj->getId()]['total_user_hired_before_period'] = ( !empty( $u_obj->getHireDate() ) && $u_obj->getHireDate() < $total_user_start_date ) ? 1 : 0;
			$this->tmp_data['user'][$u_obj->getId()]['total_user_hired_in_period'] = ( !empty( $u_obj->getHireDate() ) && $u_obj->getHireDate() >= $total_user_start_date && $u_obj->getHireDate() <= $total_user_end_date ) ? 1 : 0;
			$this->tmp_data['user'][$u_obj->getId()]['total_user_terminated_in_period'] = ( !empty( $u_obj->getTerminationDate() ) && $u_obj->getTerminationDate() >= $total_user_start_date && $u_obj->getTerminationDate() <= $total_user_end_date ) ? 1 : 0;;
			$this->tmp_data['user'][$u_obj->getId()]['total_user_employed_entire_period'] = ( !empty( $u_obj->getHireDate() ) && $u_obj->getHireDate() < $total_user_start_date && ( empty( $u_obj->getTerminationDate() ) || $u_obj->getTerminationDate() > $total_user_end_date ) ) ? 1 : 0;

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'TMP User Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user preference data for joining.
		$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
		$uplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Preference Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $uplf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $uplf as $key => $up_obj ) {
			$this->tmp_data['user_preference'][$up_obj->getUser()] = (array)$up_obj->getObjectAsArray( $columns );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$rdalf = TTnew( 'RemittanceDestinationAccountListFactory' ); /** @var RemittanceDestinationAccountListFactory $rdalf */
		$rda_filter_data = $filter_data;
		$rda_filter_data['status_id'] = 10;                                                                                                                    //Enabled
		$rda_filter_data['type_id'] = 3000;                                                                                                                    //EFT payment methods only.
		//FIXME: This report can only display a single bank account. We need to add a new report for just Employee Payment Methods in the future.
		$rdalf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $rda_filter_data, null, null, null, [ 'priority' => 'desc' ] ); //Order priority DESC first so highest priority account comes last and overwrites any previous account.
		Debug::Text( ' Remittance Destination Account Rows: ' . $rdalf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $rdalf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $rdalf as $key => $rda_obj ) {
			$this->tmp_data['user_bank'][$rda_obj->getUser()] = (array)$rda_obj->getObjectAsArray( [ 'user_id' => true, 'value1' => true, 'value2' => true, 'value3' => true ] );

			//Map new RDA columns to legacy institution/transit/account columns.
			$this->tmp_data['user_bank'][$rda_obj->getUser()]['institution'] = ( isset( $this->tmp_data['user_bank'][$rda_obj->getUser()]['value1'] ) ) ? $this->tmp_data['user_bank'][$rda_obj->getUser()]['value1'] : null;
			$this->tmp_data['user_bank'][$rda_obj->getUser()]['transit'] = ( isset( $this->tmp_data['user_bank'][$rda_obj->getUser()]['value2'] ) ) ? $this->tmp_data['user_bank'][$rda_obj->getUser()]['value2'] : null;
			$this->tmp_data['user_bank'][$rda_obj->getUser()]['account'] = ( isset( $this->tmp_data['user_bank'][$rda_obj->getUser()]['value3'] ) ) ? $this->tmp_data['user_bank'][$rda_obj->getUser()]['value3'] : null;

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		unset( $rdalf, $rda_filter_data, $key, $rda_obj );


		$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' Branch Total Rows: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $blf->getRecordCount(), null, TTi18n::getText( 'Retrieving Branches...' ) );
		foreach ( $blf as $key => $b_obj ) {
			//$this->tmp_data['default_branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'default_branch_', (array)$b_obj->getObjectAsArray( array('id' => TRUE, 'name' => TRUE, 'manual_id' => TRUE, 'custom_field' => TRUE ) ) );
			$this->tmp_data['branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'branch_', (array)$b_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'manual_id' => true, 'custom_field' => true ] ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['default_branch'], 'Default Branch Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
		$dlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' Department Total Rows: ' . $dlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $dlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Departments...' ) );
		foreach ( $dlf as $key => $d_obj ) {
			$this->tmp_data['department'][$d_obj->getId()] = Misc::addKeyPrefix( 'department_', (array)$d_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'manual_id' => true, 'custom_field' => true ] ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$utlf = TTnew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
		$utlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' User Title Total Rows: ' . $dlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$user_title_column_config = array_merge( (array)Misc::removeKeyPrefix( 'user_title_', (array)$this->getColumnDataConfig() ), [ 'id' => true ] ); //Always include title_id column so we can merge title data.
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $utlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Titles...' ) );
		foreach ( $utlf as $key => $ut_obj ) {
			$this->tmp_data['user_title'][$ut_obj->getId()] = Misc::addKeyPrefix( 'user_title_', (array)$ut_obj->getObjectAsArray( $user_title_column_config ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			//Get job data for joining.
			$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
			$jlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			Debug::Text( ' Job Total Rows: ' . $jlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $jlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Jobs...' ) );
			$job_column_config = array_merge( (array)Misc::removeKeyPrefix( 'job_', (array)$this->getColumnDataConfig() ), [ 'client_id' => true ] ); //Always include client_id column so we can merge client data.
			$this->tmp_data['job'][TTUUID::getZeroID()] = [ 'name' => TTi18n::getText( 'No Job' ), 'description' => TTi18n::getText( 'No Job' ), 'job_manual_id' => 0 ];
			foreach ( $jlf as $key => $j_obj ) {
				$this->tmp_data['job'][$j_obj->getId()] = (array)Misc::addKeyPrefix( 'job_', (array)$j_obj->getObjectAsArray( $job_column_config ) );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
			unset( $jlf, $j_obj, $key );

			$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
			$jilf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			Debug::Text( ' Job Item Total Rows: ' . $jilf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $jilf->getRecordCount(), null, TTi18n::getText( 'Retrieving Tasks...' ) );
			$job_item_column_config = Misc::removeKeyPrefix( 'job_item_', (array)$this->getColumnDataConfig() );
			$this->tmp_data['job_item'][TTUUID::getZeroID()] = [ 'name' => TTi18n::getText( 'No Task' ), 'description' => TTi18n::getText( 'No Task' ), 'job_item_manual_id' => 0 ];
			foreach ( $jilf as $key => $ji_obj ) {
				$this->tmp_data['job_item'][$ji_obj->getId()] = (array)Misc::addKeyPrefix( 'job_item_', (array)$ji_obj->getObjectAsArray( $job_item_column_config ) );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
			unset( $jilf, $ji_obj, $key );
		}

		//Get user wage data for joining.
		$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
		$filter_data['wage_group_id'] = [ TTUUID::getZeroID() ]; //Use default wage groups only.
		$filter_data['permission_children_ids'] = $wage_permission_children_ids;
		if ( isset( $filter_data['wage_effective_end_date'] ) ) {
			$filter_data['effective_date'] = $filter_data['wage_effective_end_date'];
		}
		$uwlf->getAPILastWageSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Wage Rows: ' . $uwlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		unset( $columns['note'] ); //Prevent wage note from overwriting user note.
		foreach ( $uwlf as $key => $uw_obj ) {
			if ( $this->getPermissionObject()->isPermissionChild( $uw_obj->getUser(), $wage_permission_children_ids ) ) { //This is required in cases where they have 'view'(all) wage permisisons, but only view_child user permissions. As the SQL will return all employees wages, which then need to be filtered out here.
				$this->tmp_data['user_wage'][$uw_obj->getUser()] = (array)$uw_obj->getObjectAsArray( $columns );
				$this->tmp_data['user_wage'][$uw_obj->getUser()]['wage'] = $uw_obj->getWage();              //Get raw unformatted value as columnFormatter() will format it later on.
				$this->tmp_data['user_wage'][$uw_obj->getUser()]['hourly_rate'] = $uw_obj->getHourlyRate(); //Get raw unformatted value as columnFormatter() will format it later on.

				if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
					$this->tmp_data['user_wage'][$uw_obj->getUser()]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
					if ( isset( $this->tmp_data['user'][$uw_obj->getUser()]['currency_rate'] ) ) {
						$this->tmp_data['user_wage'][$uw_obj->getUser()]['hourly_rate'] = $base_currency_obj->getBaseCurrencyAmount( $uw_obj->getHourlyRate(), $this->tmp_data['user'][$uw_obj->getUser()]['currency_rate'], $currency_convert_to_base );
						$this->tmp_data['user_wage'][$uw_obj->getUser()]['wage'] = $base_currency_obj->getBaseCurrencyAmount( $uw_obj->getWage(), $this->tmp_data['user'][$uw_obj->getUser()]['currency_rate'], $currency_convert_to_base );
					}
				}

				$this->tmp_data['user_wage'][$uw_obj->getUser()]['effective_date'] = ( isset( $this->tmp_data['user_wage'][$uw_obj->getUser()]['effective_date'] ) ) ? TTDate::parseDateTime( $this->tmp_data['user_wage'][$uw_obj->getUser()]['effective_date'] ) : null;
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data['user_preference'], 'TMP Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['user'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

		$key = 0;
		if ( isset( $this->tmp_data['user'] ) ) {
			$column_keys = array_keys( $this->getColumnDataConfig() );

			foreach ( $this->tmp_data['user'] as $user_id => $row ) {
				if ( isset( $row['hire_date'] ) ) {
					$hire_date_columns = TTDate::getReportDates( 'hire', TTDate::parseDateTime( $row['hire_date'] ), false, $this->getUserObject(), null, $column_keys );
				} else {
					$hire_date_columns = [];
				}

				if ( isset( $row['termination_date'] ) ) {
					$termination_date_columns = TTDate::getReportDates( 'termination', TTDate::parseDateTime( $row['termination_date'] ), false, $this->getUserObject(), null, $column_keys );
				} else {
					$termination_date_columns = [];
				}
				if ( isset( $row['birth_date'] ) ) {
					$birth_date_columns = TTDate::getReportDates( 'birth', TTDate::parseDateTime( $row['birth_date'] ), false, $this->getUserObject(), null, $column_keys );
				} else {
					$birth_date_columns = [];
				}

				if ( isset( $row['created_date'] ) ) {
					$created_date_columns = TTDate::getReportDates( 'created', TTDate::parseDateTime( $row['created_date'] ), false, $this->getUserObject(), null, $column_keys );
				} else {
					$created_date_columns = [];
				}
				if ( isset( $row['updated_date'] ) ) {
					$updated_date_columns = TTDate::getReportDates( 'updated', TTDate::parseDateTime( $row['updated_date'] ), false, $this->getUserObject(), null, $column_keys );
				} else {
					$updated_date_columns = [];
				}

				$processed_data = [];
				if ( isset( $this->tmp_data['user_preference'][$user_id] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_preference'][$user_id] );
				}
				if ( isset( $this->tmp_data['user_bank'][$user_id] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_bank'][$user_id] );
				}
				if ( isset( $this->tmp_data['user_wage'][$user_id] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_wage'][$user_id] );
				}

				if ( isset( $this->tmp_data['branch'][$row['default_branch_id']] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['branch'][$row['default_branch_id']] );
				}
				if ( isset( $this->tmp_data['department'][$row['default_department_id']] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['department'][$row['default_department_id']] );
				}
				if ( isset( $this->tmp_data['job'][$row['default_job_id']] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['job'][$row['default_job_id']] );
				}
				if ( isset( $this->tmp_data['job_item'][$row['default_job_item_id']] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['job_item'][$row['default_job_item_id']] );
				}
				if ( isset( $this->tmp_data['user_title'][$row['title_id']] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_title'][$row['title_id']] );
				}

				$this->data[] = array_merge( $row, $hire_date_columns, $termination_date_columns, $birth_date_columns, $created_date_columns, $updated_date_columns, $processed_data );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				$key++;
			}
			unset( $this->tmp_data, $row, $user_id, $hire_date_columns, $termination_date_columns, $birth_date_columns, $processed_data );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * Calculate retention and turnover rates.
	 * @return bool
	 */
	function _postGroup() {
		$columns = $this->getColumnDataConfig();

		if ( is_array( $this->data ) && ( isset( $columns['retention_rate'] ) || isset( $columns['turnover_rate'] ) ) ) {
			//Calculate Retention/Turnover rate for just grouped rows, as it can't be done accurately on an individual row (employee) basis.
			foreach ( $this->data as $key => $row ) {
				if ( isset( $row['total_user_hired_before_period'] ) && isset( $row['total_user_employed_entire_period'] ) && $row['total_user_hired_before_period'] > 0 ) {
					$this->data[$key]['retention_rate'] = round( TTMath::mul( TTMath::div( $row['total_user_employed_entire_period'], $row['total_user_hired_before_period'] ), 100 ), 2 );
				} else {
					$this->data[$key]['retention_rate'] = 0;
				}

				if ( isset( $row['total_user_hired_before_period'] ) && isset( $row['total_user_hired_in_period'] ) && isset( $row['total_user_terminated_in_period'] ) ) {
					$tmp_average_users_in_period = TTMath::div( TTMath::add( $row['total_user_hired_before_period'], TTMath::sub( TTMath::add( $row['total_user_hired_before_period'], $row['total_user_hired_in_period'] ), $row['total_user_terminated_in_period'] ) ), 2 );
					if ( $tmp_average_users_in_period >= 1 ) { //When calculated row-by-row for individual employees, if the employee is terminated it would show 200% turnover instead of 100%.
						$this->data[$key]['turnover_rate'] = round( TTMath::mul( TTMath::div( $row['total_user_terminated_in_period'], $tmp_average_users_in_period ), 100 ), 2 );
					} else if ( $row['total_user_terminated_in_period'] > 0 ) {
						$this->data[$key]['turnover_rate'] = 100;
					} else {
						$this->data[$key]['turnover_rate'] = 0;
					}
				} else {
					$this->data[$key]['turnover_rate'] = 100;
				}
			}
			unset( $tmp_average_users_in_period );
		}

		return true;
	}
}

?>
