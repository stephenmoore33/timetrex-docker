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
class TaxSummaryReport extends Report {
	/**
	 * @var array
	 */
	private $user_12th_day_of_month_data;
	private $pay_period_to_12th_day_of_month_map;

	/**
	 * TaxSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Tax Summary Report' );
		$this->file_name = 'tax_summary_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_generic_tax_summary', $user_id, $company_id ) ) {
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
						'time_period',
						'columns',
				];
				break;
			case 'setup_fields':
				$retval = [
					//Static Columns - Aggregate functions can't be used on these.
					'-1000-template'                     => TTi18n::gettext( 'Template' ),
					'-1010-time_period'                  => TTi18n::gettext( 'Time Period' ),
					'-1900-legal_entity_id'              => TTi18n::gettext( 'Legal Entity' ),
					'-2000-company_deduction_id'         => TTi18n::gettext( 'Tax' ),
					'-2005-payroll_remittance_agency_id' => TTi18n::gettext( 'Remittance Agency' ),

					'-2010-user_status_id'        => TTi18n::gettext( 'Employee Status' ),
					'-2020-user_group_id'         => TTi18n::gettext( 'Employee Group' ),
					'-2025-policy_group_id'       => TTi18n::gettext( 'Policy Group' ),
					'-2030-user_title_id'         => TTi18n::gettext( 'Employee Title' ),
					'-2035-user_tag'              => TTi18n::gettext( 'Employee Tags' ),
					'-2040-include_user_id'       => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'       => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-default_branch_id'     => TTi18n::gettext( 'Default Branch' ),
					'-2070-default_department_id' => TTi18n::gettext( 'Default Department' ),
					'-3000-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),

					'-2200-pay_stub_status_id' => TTi18n::gettext( 'Pay Stub Status' ),
					'-2205-pay_stub_type_id'   => TTi18n::gettext( 'Pay Stub Type' ),
					'-2210-pay_stub_run_id'    => TTi18n::gettext( 'Payroll Run' ),

					//'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'),

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
						TTDate::getReportDateOptions( 'hire', TTi18n::getText( 'Hire Date' ), 13, false ),
						TTDate::getReportDateOptions( 'termination', TTi18n::getText( 'Termination Date' ), 14, false ),
						TTDate::getReportDateOptions( 'birth', TTi18n::getText( 'Birth Date' ), 15, false ),
						TTDate::getReportDateOptions( 'transaction', TTi18n::getText( 'Transaction Date' ), 16, true ),
						//TTDate::getReportDateOptions( 'filter_start', TTi18n::getText( 'Reporting Period Start Date' ), 17, false ),
						TTDate::getReportDateOptions( 'filter_end', TTi18n::getText( 'Reporting Date' ), 18, false ) //Filter End Date.
				);
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$retval = $this->getCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users', 'user_title'], ['users'] );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'TaxSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'TaxSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'TaxSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'TaxSummaryReport', 'custom_column' );
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
					'-1000-first_name'          => TTi18n::gettext( 'First Name' ),
					'-1001-middle_name'         => TTi18n::gettext( 'Middle Name' ),
					'-1002-last_name'           => TTi18n::gettext( 'Last Name' ),
					'-1005-full_name'           => TTi18n::gettext( 'Full Name' ),
					'-1030-employee_number'     => TTi18n::gettext( 'Employee #' ),
					'-1040-status'              => TTi18n::gettext( 'Status' ),
					'-1050-title'               => TTi18n::gettext( 'Title' ),
					'-1052-ethnic_group'        => TTi18n::gettext( 'Ethnicity' ),
					'-1053-sex'                 => TTi18n::gettext( 'Gender' ),
					'-1054-address1'            => TTi18n::gettext( 'Address 1' ),
					'-1054-address2'            => TTi18n::gettext( 'Address 2' ),
					'-1055-city'                => TTi18n::gettext( 'City' ),
					'-1060-province'            => TTi18n::gettext( 'Province/State' ),
					'-1070-country'             => TTi18n::gettext( 'Country' ),
					'-1075-postal_code'         => TTi18n::gettext( 'Postal Code' ),
					'-1078-home_phone'          => TTi18n::gettext( 'Home Phone' ),
					'-1079-mobile_phone'        => TTi18n::gettext( 'Mobile Phone' ),
					'-1080-home_email'          => TTi18n::gettext( 'Home Email' ),
					'-1082-work_email'     		=> TTi18n::gettext( 'Work Email' ),
					'-1085-user_group'          => TTi18n::gettext( 'Group' ),
					'-1090-default_branch'      => TTi18n::gettext( 'Default Branch' ),
					'-1100-default_department'  => TTi18n::gettext( 'Default Department' ),
					'-1102-default_job'         => TTi18n::gettext( 'Default Job' ),
					'-1104-default_job_item'    => TTi18n::gettext( 'Default Task' ),
					'-1110-currency'            => TTi18n::gettext( 'Currency' ),
					'-1200-permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1210-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1220-policy_group'        => TTi18n::gettext( 'Policy Group' ),

					//Handled in date_columns above.
					//'-1250-pay_period' => TTi18n::gettext('Pay Period'),

					'-1280-sin'  => TTi18n::gettext( 'SIN/SSN' ),
					'-1290-note' => TTi18n::gettext( 'Note' ),
					'-1295-tag'  => TTi18n::gettext( 'Tags' ),

					'-1398-hire_date_age'  => TTi18n::gettext( 'Length of Service' ), //After Hire Date columns
					'-1599-birth_date_age' => TTi18n::gettext( 'Age' ), //After Birth Date columns

					'-1980-user_wage_type'             => TTi18n::gettext( 'Employee Wage Type' ),
					'-1985-user_wage_effective_date'   => TTi18n::gettext( 'Employee Wage Effective Date' ),

					'-2000-payroll_remittance_agency_name' => TTi18n::gettext( 'Remittance Agency' ),
					'-2010-payroll_remittance_agency_primary_identification' => TTi18n::gettext( 'Remittance Agency Primary ID' ),

					'-2110-company_deduction_name' => TTi18n::gettext( 'Tax/Deduction Name' ),
					'-2115-company_deduction_calculation_name' => TTi18n::gettext( 'Tax/Deduction Calculation' ),
					'-2120-company_deduction_district_name' => TTi18n::gettext( 'Tax/Deduction District' ),

					'-2800-pay_stub_status' => TTi18n::gettext( 'Pay Stub Status' ),
					'-2810-pay_stub_type'   => TTi18n::gettext( 'Pay Stub Type' ),
					'-2820-pay_stub_run_id' => TTi18n::gettext( 'Payroll Run' ),
				];

				$retval = array_merge( $retval, $this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users', 'user_title'], ['users'] ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-1987-user_wage_wage'         => TTi18n::gettext( 'Employee Wage' ),
					'-1989-user_wage_hourly_rate'  => TTi18n::gettext( 'Employee Wage Hourly Rate' ),

					'-2030-subject_wages' => TTi18n::gettext( 'Subject Wages' ),
					'-2040-taxable_wages' => TTi18n::gettext( 'Taxable Wages' ),
					'-2045-excess_wages'  => TTi18n::gettext( 'Excess Wages' ),
					'-2050-tax_withheld'  => TTi18n::gettext( 'Tax Withheld' ),

					'-2110-subject_wages_ytd' => TTi18n::gettext( 'Subject Wages YTD' ),
					'-2120-subject_units'     => TTi18n::gettext( 'Subject Units' ),
					'-2130-subject_rate'      => TTi18n::gettext( 'Subject Hourly Rate' ),

					'-2150-taxable_wages_ytd' => TTi18n::gettext( 'Taxable Wages YTD' ),

					'-2210-pay_period_taxable_wages_weeks' => TTi18n::gettext( 'Taxable Wages Weeks' ),
					'-2220-pay_period_tax_withheld_weeks'  => TTi18n::gettext( 'Tax Withheld Weeks' ),
					'-2230-pay_period_weeks'               => TTi18n::gettext( 'Pay Period Weeks' ),

					'-2300-total_user' => TTi18n::gettext( 'Total Employees' ), //Group counter...

					//These are required for Indiana Unemployment.
					'-2350-paid_12th_day_month1' => TTi18n::gettext( 'Paid 12th Day Of Month 1' ), //Received wages for the pay period that spans the 12th day of the 1st month in the quarter (date range).
					'-2351-paid_12th_day_month2' => TTi18n::gettext( 'Paid 12th Day Of Month 2' ), //Received wages for the pay period that spans the 12th day of the 2nd month in the quarter (date range).
					'-2352-paid_12th_day_month3' => TTi18n::gettext( 'Paid 12th Day Of Month 3' ), //Received wages for the pay period that spans the 12th day of the 3rd month in the quarter (date range).

					'-3005-company_deduction_fixed_amount' => TTi18n::gettext( 'Tax/Deduction Fixed Amount' ),
					'-3010-company_deduction_rate' => TTi18n::gettext( 'Tax/Deduction Rate' ),
				];

				$retval = array_merge( $retval, $this->getOptions( 'pay_stub_account_amount_columns', [ 'include_ytd_amount' => true ] ) );
				$retval = array_merge( $retval, $this->getDynamicCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users', 'user_title'], ['users'] ) );
				ksort( $retval );

				break;

			case 'pay_stub_account_amount_columns':
				//Get all pay stub accounts
				$retval = [];

				if ( is_object( $this->getUserObject() ) ) {
					$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
					$pseallf->getByCompanyId( $this->getUserObject()->getCompany() );
					if ( $pseallf->getRecordCount() > 0 ) {
						$pseal_obj = $pseallf->getCurrent();

						$default_linked_columns = [
								$pseal_obj->getTotalGross(),
								$pseal_obj->getTotalNetPay(),
								$pseal_obj->getTotalEmployeeDeduction(),
								$pseal_obj->getTotalEmployerDeduction(),
						];
					} else {
						$default_linked_columns = [];
					}
					unset( $pseallf, $pseal_obj );

					$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
					$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 10, 20, 30, 40, 50, 60, 65, 80 ] );
					if ( $psealf->getRecordCount() > 0 ) {
						$type_options = $psealf->getOptions( 'type' );
						foreach ( $type_options as $key => $val ) {
							$type_options[$key] = str_replace( [ 'Employee', 'Employer', 'Deduction', 'Miscellaneous', 'Total' ], [ 'EE', 'ER', 'Ded', 'Misc', '' ], $val );
						}

						$i = 0;
						foreach ( $psealf as $psea_obj ) {
							//Need to make the PSEA_ID a string so we can array_merge it properly later.
							if ( $psea_obj->getType() == 40 ) { //Total accounts.
								$prefix = null;
							} else {
								$prefix = $type_options[$psea_obj->getType()] . ' - ';
							}

							$retval['-3' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PA:' . $psea_obj->getID()] = $prefix . $psea_obj->getName();

							if ( $psea_obj->getType() == 10 ) { //Earnings only can see units.
								$retval['-4' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PR:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'Rate' ) . ']';
								$retval['-5' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PU:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'Units' ) . ']';
								$retval['-6' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PUY:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'Units YTD' ) . ']';
							}

							//Add units for Total Gross so they can get a total number of hours/units that way too.
							if ( $psea_obj->getType() == 40 && isset( $default_linked_columns[0] ) && $default_linked_columns[0] == $psea_obj->getID() ) {
								$retval['-5' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PU:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'Units' ) . ']';
								$retval['-6' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PUY:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'Units YTD' ) . ']';
							}

							if ( isset( $params['include_ytd_amount'] ) ) { //This is used for Tax/Deduction Custom Formulas.
								if ( $psea_obj->getType() != 50 ) { //Accruals, display balance/YTD amount.
									$retval['-7' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PY:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'YTD' ) . ']';
								}
							}

							if ( $psea_obj->getType() == 50 ) { //Accruals, display balance/YTD amount.
								$retval['-7' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PY:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'Balance' ) . ']';
							}

							$i++;
						}
					}
				} else {
					Debug::Text( ' WARNING: UserObject not defined, unable to get pay stub accounts...', __FILE__, __LINE__, __METHOD__, 10 );
				}
				break;
			case 'pay_stub_account_unit_columns':
				//Units are only good for earnings?
				break;
			case 'pay_stub_account_ytd_columns':
				break;
			case 'columns':
				$retval = array_merge( $this->getOptions( 'static_columns' ), $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) );
				ksort( $retval );
				break;
			case 'column_format':
				//Define formatting function for each column.
				$columns = Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_custom_column' ) ) );
				if ( is_array( $columns ) ) {
					foreach ( $columns as $column => $name ) {
						if ( $column == 'subject_units' || strpos( $column, '_weeks' ) !== false || substr( $column, 0, 2 ) == 'PU' || strpos( $column, 'total_user' ) !== false ) {
							$retval[$column] = 'numeric';
						} else if ( strpos( $column, '_wage' ) !== false || strpos( $column, '_hourly_rate' ) !== false
								|| substr( $column, 0, 2 ) == 'PA' || substr( $column, 0, 2 ) == 'PY' || substr( $column, 0, 2 ) == 'PR'
								|| strpos( $column, '_ytd' ) !== false
								|| $column == 'tax_withheld' || $column == 'subject_rate' || $column == 'company_deduction_fixed_amount' ) {
							$retval[$column] = 'currency';
						} else if ( strpos( $column, '_time' ) || strpos( $column, '_policy' ) ) {
							$retval[$column] = 'time_unit';
						} else if ( $column == 'company_deduction_rate' ) {
							$retval[$column] = 'percent';
						} else if ( strpos( $column, 'paid_12th_day_month' ) !== false ) {
							$retval[$column] = 'int';
						}
					}
				}
				$retval['user_wage_effective_date'] = 'date_stamp';
				break;
			case 'grand_total_metadata':
				$retval['aggregate'] = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, '_hourly_rate' ) !== false || strpos( $column, '_rate' ) !== false || strpos( $column, 'user_wage_wage' ) !== false || substr( $column, 0, 2 ) == 'PR' || $column == 'company_deduction_rate' ) {
									$retval['aggregate'][$column] = 'avg';
								} else if ( strpos( $column, '_ytd' ) !== false ) { //YTD Amounts for just sub-total and grand total still use sum() rather than max()
									$retval['aggregate'][$column] = 'sum';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'sub_total_by_metadata':
				$retval['aggregate'] = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, '_hourly_rate' ) !== false || strpos( $column, '_rate' ) !== false || strpos( $column, 'user_wage_wage' ) !== false || substr( $column, 0, 2 ) == 'PR' || $column == 'company_deduction_rate' ) {
									$retval['aggregate'][$column] = 'avg';
								} else if ( strpos( $column, '_ytd' ) !== false ) { //YTD Amounts for just sub-total and grand total still use sum() rather than max()
									$retval['aggregate'][$column] = 'sum';
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
								if ( strpos( $column, '_hourly_rate' ) !== false || strpos( $column, '_rate' ) !== false || strpos( $column, 'user_wage_wage' ) !== false || substr( $column, 0, 2 ) == 'PR' || $column == 'company_deduction_rate' ) {
									$retval['aggregate'][$column] = 'avg';
								} else if ( strpos( $column, '_ytd' ) !== false || substr( $column, 0, 2 ) == 'PY' || substr( $column, 0, 3 ) == 'PUY' || strpos( $column, 'paid_12th_day_month' ) !== false ) { //YTD Amounts must use "max()" when grouping rather than sum()
									$retval['aggregate'][$column] = 'max';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'templates':
				$retval = [
						'-1010-by_employee+taxes'                                                   => TTi18n::gettext( 'Tax by Employee' ),
						'-1020-by_company_deduction_by_employee+taxes'                              => TTi18n::gettext( 'Tax by Tax/Employee' ),
						'-1030-by_company_deduction_calculation_name_by_company_deduction+taxes'    => TTi18n::gettext( 'Tax by Calculation/Tax' ),
						'-1040-by_payroll_remittance_agency+taxes'                                  => TTi18n::gettext( 'Tax by Remittance Agency' ),
						'-1050-by_payroll_remittance_agency_by_company_deduction+taxes'             => TTi18n::gettext( 'Tax by Remittance Agency/Tax' ),
						'-1060-by_payroll_remittance_agency_by_company_deduction_by_month+taxes'    => TTi18n::gettext( 'Tax by Remittance Agency/Tax/Month' ),
						'-1065-by_payroll_remittance_agency_by_company_deduction_by_quarter+taxes'  => TTi18n::gettext( 'Tax by Remittance Agency/Tax/Quarter' ),
						'-1080-by_payroll_remittance_agency_by_employee+taxes'                      => TTi18n::gettext( 'Tax by Remittance Agency/Employee' ),
						'-1090-by_payroll_remittance_agency_by_company_deduction_by_employee+taxes' => TTi18n::gettext( 'Tax by Remittance Agency/Tax/Employee' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						default:
							Debug::Text( ' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );
							$retval['-1010-time_period']['time_period'] = 'last_quarter';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode( '+', $template );
							if ( is_array( $template_keywords ) ) {
								foreach ( $template_keywords as $template_keyword ) {
									Debug::Text( ' Keyword: ' . $template_keyword, __FILE__, __LINE__, __METHOD__, 10 );

									switch ( $template_keyword ) {
										//Columns
										case 'taxes':
											//$retval['columns'][] = 'PA'.$default_linked_columns[0];
											$retval['columns'][] = 'subject_wages'; //Basically Total Gross.
											$retval['columns'][] = 'taxable_wages';
											$retval['columns'][] = 'tax_withheld';
											break;
										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';
											$retval['group'][] = 'sin';

											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											$retval['sort'][] = [ 'middle_name' => 'asc' ];
											$retval['sort'][] = [ 'sin' => 'asc' ];
											break;
										case 'by_company_deduction':
											$retval['columns'][] = 'company_deduction_name';
											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();
											$retval['group'][] = 'company_deduction_name';
											$retval['sub_total'][] = 'company_deduction_name';
											$retval['sort'][] = [ 'company_deduction_name' => 'asc' ];
											break;
										case 'by_company_deduction_by_employee':
											$retval['columns'][] = 'company_deduction_name';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'company_deduction_name';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';
											$retval['group'][] = 'sin';

											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = [ 'company_deduction_name' => 'asc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											$retval['sort'][] = [ 'middle_name' => 'asc' ];
											$retval['sort'][] = [ 'sin' => 'asc' ];
											break;
										case 'by_payroll_remittance_agency':
											$retval['columns'][] = 'payroll_remittance_agency_name';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';

											$retval['sort'][] = [ 'payroll_remittance_agency_name' => 'asc' ];
											break;
										case 'by_company_deduction_calculation_name_by_company_deduction':
											$retval['columns'][] = 'company_deduction_calculation_name';
											$retval['columns'][] = 'company_deduction_name';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'company_deduction_calculation_name';
											$retval['group'][] = 'company_deduction_name';

											$retval['sub_total'][] = 'company_deduction_calculation_name';
											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = [ 'company_deduction_calculation_name' => 'asc' ];
											$retval['sort'][] = [ 'company_deduction_name' => 'asc' ];
											break;
										case 'by_payroll_remittance_agency_by_company_deduction':
											$retval['columns'][] = 'payroll_remittance_agency_name';
											$retval['columns'][] = 'company_deduction_name';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';
											$retval['group'][] = 'company_deduction_name';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';
											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = [ 'payroll_remittance_agency_name' => 'asc' ];
											$retval['sort'][] = [ 'company_deduction_name' => 'asc' ];
											break;
										case 'by_payroll_remittance_agency_by_company_deduction_by_month':
											$retval['columns'][] = 'payroll_remittance_agency_name';
											$retval['columns'][] = 'company_deduction_name';
											$retval['columns'][] = 'transaction-date_month';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';
											$retval['group'][] = 'company_deduction_name';
											$retval['group'][] = 'transaction-date_month';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';
											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = [ 'payroll_remittance_agency_name' => 'asc' ];
											$retval['sort'][] = [ 'company_deduction_name' => 'asc' ];
											break;
										case 'by_payroll_remittance_agency_by_company_deduction_by_quarter':
											$retval['columns'][] = 'payroll_remittance_agency_name';
											$retval['columns'][] = 'company_deduction_name';
											$retval['columns'][] = 'transaction-date_quarter';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';
											$retval['group'][] = 'company_deduction_name';
											$retval['group'][] = 'transaction-date_quarter';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';
											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = [ 'payroll_remittance_agency_name' => 'asc' ];
											$retval['sort'][] = [ 'company_deduction_name' => 'asc' ];
											break;
										case 'by_payroll_remittance_agency_by_employee':
											$retval['columns'][] = 'payroll_remittance_agency_name';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';
											$retval['group'][] = 'sin';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';

											$retval['sort'][] = [ 'payroll_remittance_agency_name' => 'asc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											$retval['sort'][] = [ 'middle_name' => 'asc' ];
											$retval['sort'][] = [ 'sin' => 'asc' ];
											break;
										case 'by_payroll_remittance_agency_by_company_deduction_by_employee':
											$retval['columns'][] = 'payroll_remittance_agency_name';
											$retval['columns'][] = 'company_deduction_name';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'sin';

											$retval['-2000-company_deduction_id'][] = TTUUID::getZeroID();

											$retval['group'][] = 'payroll_remittance_agency_name';
											$retval['group'][] = 'company_deduction_name';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';
											$retval['group'][] = 'sin';

											$retval['sub_total'][] = 'payroll_remittance_agency_name';
											$retval['sub_total'][] = 'company_deduction_name';

											$retval['sort'][] = [ 'payroll_remittance_agency_name' => 'asc' ];
											$retval['sort'][] = [ 'company_deduction_name' => 'asc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											$retval['sort'][] = [ 'middle_name' => 'asc' ];
											$retval['sort'][] = [ 'sin' => 'asc' ];
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
	 * @param $company_id
	 * @param $filter_data
	 * @param $columns
	 * @return array
	 */
	function getCompanyDeductionData( $company_id, $filter_data, $columns, $report_filter_start_date, $report_filter_end_date ) {
		$company_deduction_data = [];
		$company_deduction_merge_map = [];

		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		//Order by calculation_id DESC so Federal Taxes are always the parent of Fixed Amount records for example.
		$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, null, null, null, [ 'calculation_id' => 'desc', 'calculation_order' => 'asc', 'start_date' => 'desc', 'id' => 'asc' ] );
		Debug::Text( 'Company Deductions: ' . $cdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $cdlf->getRecordCount() > 0 ) {
			$duplicate_pay_stub_entry_account_map = [];

			//Check to see if user records intersect across any of the CompanyDeductions. If they don't we can be confident that the report will still work as if the company deduct column was included.
			//  This is also done in the USPERS reports in a similar way.
			// **NOTE: This doubles up on Federal/State taxes when additional taxes are used as a separate Tax/Deduction record, but same pay stub account.
			$do_company_deductions_intersect = true;
			if ( !isset( $columns['company_deduction_name'] ) ) {
				$do_company_deductions_intersect = false;
				$company_deduction_user_map = [];
				foreach ( $cdlf as $cd_obj ) {
					if ( $cd_obj->canBeCombined() == false ) {
						$do_company_deductions_intersect = true;
						continue;
					}

					$tmp_cd_users = $cd_obj->getUser();
					if ( !is_array( $tmp_cd_users ) ) { //Could be FALSE if no users are assigned.
						$tmp_cd_users = []; //Must an array for array_intersect() to work properly.
					}

					if ( !empty( $company_deduction_user_map ) ) {
						foreach ( $company_deduction_user_map as $tmp_company_deduction_id => $tmp_users ) {
							if ( count( array_intersect( $tmp_cd_users, $tmp_users ) ) > 0 ) {
								Debug::Text( '    Company Deduction: ' . $cd_obj->getName() . ' (' . $cd_obj->getId() . ') intersects with another Company Deduction ('. $tmp_company_deduction_id .'), skipping...', __FILE__, __LINE__, __METHOD__, 10 );
								$do_company_deductions_intersect = true;
								continue 2;
							}
						}
					}

					$company_deduction_user_map[$cd_obj->getId()] = $cd_obj->getUser();
				}
				unset( $company_deduction_user_map, $tmp_cd_users, $tmp_users, $tmp_company_deduction_id, $cd_obj );
			}

			//Splitting or combining data across agency/company deductions is important to prevent a report from duplicating subject wages.
			//  For example if two taxes are selected (ie: 2x Workers Comp) and the employee has both deducted, the subject wages might be doubled up.
			//  However if the employee is assigned to State Income Tax and State Addl. Income Tax both going to the same PS Account, the amounts shouldn't get doubled up.
			if ( isset( $columns['payroll_remittance_agency_name'] ) ) {
				$enable_split_by_payroll_remittance_agency = true;
			} else {
				$enable_split_by_payroll_remittance_agency = false;
			}

			if ( isset( $columns['company_deduction_name'] ) || $do_company_deductions_intersect == false ) {
				//This could cause Tax Withheld amounts to double up if company_deduction_name column is *not* actually specified.
				//   This might be the intention if a user is only assigned to *ONE* of the Tax/Deductions. Thereby preventing them from being doubled-up.
				$enable_split_by_company_deduction = true;
			} else {
				$enable_split_by_company_deduction = false;
			}

			$i = 0;
			foreach ( $cdlf as $cd_obj ) {
				//Check to see if CompanyDeduction is even active during the report filter dates.
				//  If not, we can ignore it completely, and this can help in cases where there might be multiple Tax/Deduction records (ie: State UI) for each year, with different start/end dates.
				if ( $cd_obj->isActiveForAnyUserDates( $report_filter_start_date, $report_filter_end_date ) == false ) {
					Debug::Text( '      No users are active for specified dates, skipping CompanyDeduction: '. $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				//Check to see if its a Tax/Deduction that can be combined.
				//Just State, District/Local taxes can't be combined, due to employees working in multiple jurisdictions and may or may not be taxable. So they could be assigned to multiple Tax/Deductions of the same State.
				//  However when there is State Income Tax and State Addl. Income Tax going to the same PS account, these do need to be combined, otherwise the tax amounts will be doubled (though Subject Wages wouldn't be since Addl. Income Tax doesn't have subject wages typically).
				//  Only if we are splitting the data up company deduction do we ever consider not combining the tax data, otherwise subject wages and tax withheld will always get doubled up.
				if ( $enable_split_by_company_deduction == true && $cd_obj->canBeCombined() == false ) {
					$can_be_combined = false;
				} else {
					$can_be_combined = true;
				}

				if ( $can_be_combined == true && $enable_split_by_payroll_remittance_agency == false ) {
					$tmp_payroll_remittance_agency_id = TTUUID::getZeroID();
				} else {
					$tmp_payroll_remittance_agency_id = $cd_obj->getPayrollRemittanceAgency();
				}

				Debug::Text( '  Company Deduction: ' . $cd_obj->getName() . ' Can Be Combined: ' . (int)$can_be_combined . ' Split: Agency: ' . (int)$enable_split_by_payroll_remittance_agency . ' Deduction: ' . (int)$enable_split_by_company_deduction, __FILE__, __LINE__, __METHOD__, 10 );
				if ( $can_be_combined == true && $enable_split_by_company_deduction == false ) {
					$tmp_company_deduction_id = TTUUID::getZeroID();
				} else {
					if ( $can_be_combined == true && isset( $duplicate_pay_stub_entry_account_map[$tmp_payroll_remittance_agency_id][$cd_obj->getPayStubEntryAccount()] ) ) {
						$tmp_company_deduction_id = $duplicate_pay_stub_entry_account_map[$tmp_payroll_remittance_agency_id][$cd_obj->getPayStubEntryAccount()];
						Debug::Text( '    Merging Company Deductions: Parent: ' . $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->getName() . ' (' . $tmp_company_deduction_id . ') Child: ' . $cd_obj->getName() . ' (' . $cd_obj->getId() . ')', __FILE__, __LINE__, __METHOD__, 10 );

						$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->duplicate_of[] = $cd_obj;

						//Create map so getUserDeductionData() can merge the UserDeduction records properly.
						$company_deduction_merge_map[$cd_obj->getID()] = $tmp_company_deduction_id;
					} else {
						$tmp_company_deduction_id = $cd_obj->getID();
					}
				}

				if ( !isset( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id] ) ) { //Make sure when $enable_split_by_company_deduction == FALSE we don't overwrite the COMPOSITE object.
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id] = $cd_obj;
				}

				if ( !isset( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->include_psea_ids ) ) {
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->include_psea_ids = [];
				}
				$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->include_psea_ids = array_merge( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->include_psea_ids, $cd_obj->getExpandedPayStubEntryAccountIDs( $cd_obj->getIncludePayStubEntryAccount() ) );

				if ( !isset( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->exclude_psea_ids ) ) {
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->exclude_psea_ids = [];
				}
				$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->exclude_psea_ids = array_merge( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->exclude_psea_ids, $cd_obj->getExpandedPayStubEntryAccountIDs( $cd_obj->getExcludePayStubEntryAccount() ) );

				if ( !isset( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->tax_withheld_psea_ids ) ) {
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->tax_withheld_psea_ids = [];
				}
				$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->tax_withheld_psea_ids = array_merge( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->tax_withheld_psea_ids, [ $cd_obj->getPayStubEntryAccount() ] );

				if ( !isset( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->user_ids ) ) {
					$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->user_ids = [];
				}
				$company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->user_ids = array_merge( $company_deduction_data[$tmp_payroll_remittance_agency_id][$tmp_company_deduction_id]->user_ids, (array)$cd_obj->getUser() );

				$duplicate_pay_stub_entry_account_map[$tmp_payroll_remittance_agency_id][$cd_obj->getPayStubEntryAccount()] = $tmp_company_deduction_id;

				$i++;
			}
		}

		//Debug::Arr( $company_deduction_data, 'Company Deductions Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return [ $company_deduction_data, $company_deduction_merge_map ];
	}

	/**
	 * @param $company_id
	 * @param $filter_data
	 * @return array
	 */
	function getUserDeductionData( $company_id, $filter_data, $end_date, $company_deduction_merge_map ) {
		//To help determine MaximumTaxableWages, we need to get the UserDeduction records and call getMaximumPayStubEntryAccountAmount().
		$user_deduction_data = [];
		$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
		$udlf->getByCompanyIdAndCompanyDeductionId( $company_id, $filter_data, null, [ 'cdf.calculation_id' => 'desc', 'cdf.calculation_order' => 'asc', 'cdf.start_date' => 'desc', 'cdf.id' => 'asc' ] );
		if ( $udlf->getRecordCount() > 0 ) {
			foreach ( $udlf as $ud_obj ) {
				$company_deduction_id = $ud_obj->getCompanyDeduction();
				if ( isset( $company_deduction_merge_map[$company_deduction_id] ) ) {
					$company_deduction_id = $company_deduction_merge_map[$company_deduction_id];
				}

				$tmp_maximum_pay_stub_entry_account_amount = $ud_obj->getMaximumPayStubEntryAccountAmount( $end_date );
				if ( ( $tmp_maximum_pay_stub_entry_account_amount != false || $ud_obj->getRate() != false || $ud_obj->getFixedAmount() != false ) || ( $ud_obj->getStartDate() != '' || $ud_obj->getEndDate() != '' ) ) {
					if ( isset( $user_deduction_data[$company_deduction_id] ) && isset( $user_deduction_data[$company_deduction_id][$ud_obj->getUser()] )
							&& ( $user_deduction_data[$company_deduction_id][$ud_obj->getUser()]['maximum_pay_stub_entry_amount'] != $tmp_maximum_pay_stub_entry_account_amount
									|| $user_deduction_data[$company_deduction_id][$ud_obj->getUser()]['rate'] != $ud_obj->getRate()
									|| $user_deduction_data[$company_deduction_id][$ud_obj->getUser()]['fixed_amount'] != $ud_obj->getFixedAmount() ) ) {
						Debug::Text( '        WARNING: User Deduction: ' . $ud_obj->getCompanyDeductionObject()->getName() . ' (' . $ud_obj->getCompanyDeduction() . ') User: ' . $ud_obj->getUserObject()->getFullName() . ' (' . $ud_obj->getUser() . ') has multiple UserDeduction records with different MaximumPayStubEntryAccountAmounts or Rates.', __FILE__, __LINE__, __METHOD__, 10 );
					}
					$user_deduction_data[$company_deduction_id][$ud_obj->getUser()] = [ 'maximum_pay_stub_entry_amount' => $tmp_maximum_pay_stub_entry_account_amount, 'rate' => $ud_obj->getRate(), 'fixed_amount' => $ud_obj->getFixedAmount(), 'obj' => $ud_obj ];
				}
			}
		}

		return $user_deduction_data;
	}

	/**
	 * @param $cd_obj
	 * @param $pse_obj
	 * @param $user_deduction_data
	 * @return bool
	 */
	function addPayStubEntry( $cd_obj, $pse_obj, $user_deduction_data ) {
		$company_deduction_id = $cd_obj->getId();
		$remittance_agency_id = $cd_obj->getPayrollRemittanceAgency();

		$deduction_include_psea_ids = $cd_obj->include_psea_ids; //These should already be from getExpandedPayStubEntryAccountIDs()
		$deduction_exclude_psea_ids = $cd_obj->exclude_psea_ids; //These should already be from getExpandedPayStubEntryAccountIDs()
		$tax_withheld_psea_ids = $cd_obj->tax_withheld_psea_ids;

		//If the deduction amount has no where to go, just exit early as its essentially disabled.
		if ( empty( $tax_withheld_psea_ids ) == true ) {
			return true;
		}

		$user_id = $pse_obj->getColumn( 'user_id' );
		$date_stamp = TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ); //Should match PayStubSummary, RemittanceSummary, TaxSummary, GeneralLedgerSummaryReport, etc... $date_stamp too.
		$run_id = $pse_obj->getColumn( 'pay_stub_run_id' );
		$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

		//If the CompanyDeduction pay stub account does not match this current PSE PayStubEntryAccount, then check if the user is assigned to the CompanyDeduction, and if not we can return early as it doesn't apply to them.
		//  This helps in cases where employees work in multiple states and may have absence time in their resident state, preventing subject wages from being calculated on the absence earnings in foriegn states.
		//  Important: If an employee was taxed part of the year in one state and moved to a different state, they still need to be assigned to both Tax/Deductions, with just start/end dates specified instead. Otherwise subject wages will not be calculated, but tax withheld will still be.
		//             Also need to take into account Workers Comp and multiple rate groups, to ensure those
		if ( in_array( $pay_stub_entry_name_id, $tax_withheld_psea_ids ) == false && in_array( $user_id, (array)$cd_obj->user_ids ) == false ) { //Use user_ids rather than getUser() as they could be merged in getCompanyDeductionData()
			//Debug::Text('    Skipping PSE record: Agency ID: '. $remittance_agency_id .' Deduction ID: '. $company_deduction_id .' PSE Name ID: '. $pay_stub_entry_name_id .' Amount: '. $pse_obj->getColumn('amount'), __FILE__, __LINE__, __METHOD__, 10);
			return true;
		}

		//Debug::Text('    Processing PSE record: Agency ID: '. $remittance_agency_id .' Deduction: '. $cd_obj->getName() .' ('. $company_deduction_id .') PSE Name ID: '. $pay_stub_entry_name_id .' Amount: '. $pse_obj->getColumn('amount'), __FILE__, __LINE__, __METHOD__, 10);
		if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id] ) ) {
			$psf = TTnew( 'PayStubFactory' ); /** @var PayStubFactory $psf */ //For getOptions() below.

			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id] = [
					'pay_period_start_date'       => strtotime( $pse_obj->getColumn( 'pay_period_start_date' ) ),
					'pay_period_end_date'         => strtotime( $pse_obj->getColumn( 'pay_period_end_date' ) ),
					'pay_period_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ) ),
					'pay_period'                  => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ) ),
					'pay_period_id'               => $pse_obj->getColumn( 'pay_period_id' ),

					'pay_stub_status'           => Option::getByKey( $pse_obj->getColumn( 'pay_stub_status_id' ), $psf->getOptions( 'status' ) ),
					'pay_stub_type'             => Option::getByKey( $pse_obj->getColumn( 'pay_stub_type_id' ), $psf->getOptions( 'type' ) ),
					'pay_stub_start_date'       => strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ),
					'pay_stub_end_date'         => strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ),
					'pay_stub_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ) ), //Some transaction dates could be throughout the day for terminated employees being paid early, so always forward them to the middle of the day to keep group_by working correctly.
					'pay_stub_run_id'           => $run_id,

					'paid_12th_day_month1' 		=> 0,
					'paid_12th_day_month2' 		=> 0,
					'paid_12th_day_month3' 		=> 0,
			];

			if ( !empty( $this->user_12th_day_of_month_data ) && !empty( $this->pay_period_to_12th_day_of_month_map ) && isset( $this->pay_period_to_12th_day_of_month_map[$pse_obj->getColumn( 'pay_period_id' )] ) && isset( $this->user_12th_day_of_month_data[$user_id][$pse_obj->getColumn( 'pay_period_id' )] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id][ $this->pay_period_to_12th_day_of_month_map[$pse_obj->getColumn( 'pay_period_id' )] ] = ( $this->user_12th_day_of_month_data[$user_id][$pse_obj->getColumn( 'pay_period_id' )][ $this->pay_period_to_12th_day_of_month_map[$pse_obj->getColumn( 'pay_period_id' )] ] == true ) ? 1 : 0;
			}
		}

		if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PA:' . $pay_stub_entry_name_id] ) ) {
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PA:' . $pay_stub_entry_name_id] = 0;
		}
		$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PA:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PA:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'amount' ) );

		if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PU:' . $pay_stub_entry_name_id] ) ) {
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PU:' . $pay_stub_entry_name_id] = 0;
		}
		$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PU:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PU:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'units' ) );

		if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PUY:' . $pay_stub_entry_name_id] ) ) {
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PUY:' . $pay_stub_entry_name_id] = 0;
		}
		$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PUY:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PUY:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'ytd_units' ) );

		if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PR:' . $pay_stub_entry_name_id] ) ) {
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PR:' . $pay_stub_entry_name_id] = 0;
		}
		$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PR:' . $pay_stub_entry_name_id] = ( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PU:' . $pay_stub_entry_name_id] > 0 ) ? TTMath::div( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PA:' . $pay_stub_entry_name_id], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PU:' . $pay_stub_entry_name_id] ) : 0;

		if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PY:' . $pay_stub_entry_name_id] ) ) {
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PY:' . $pay_stub_entry_name_id] = 0;
		}
		$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PY:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['PY:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'ytd_amount' ) );


		$is_active_date = true;
		if ( isset( $user_deduction_data ) && isset( $user_deduction_data[$company_deduction_id] ) && isset( $user_deduction_data[$company_deduction_id][$user_id] ) ) {
			$is_active_date = $cd_obj->isActiveDate( $user_deduction_data[$company_deduction_id][$user_id]['obj'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_stub_start_date'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_stub_end_date'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_stub_transaction_date'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_period_start_date'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_period_end_date'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_period_transaction_date'] );
			Debug::Text( '      Date Restrictions Found... Is Active: ' . (int)$is_active_date . ' Date: ' . TTDate::getDate( 'DATE', $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( empty( $tax_withheld_psea_ids ) == false && in_array( $pay_stub_entry_name_id, $tax_withheld_psea_ids ) ) {
			//Debug::Text('      Tax Withheld Pay Stub Account: '. $pay_stub_entry_name_id .' Amount: '. $pse_obj->getColumn( 'amount' ), __FILE__, __LINE__, __METHOD__, 10);
			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['tax_withheld'] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['tax_withheld'] = 0;
			}
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['tax_withheld'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['tax_withheld'], $pse_obj->getColumn( 'amount' ) );
		}

		if ( $is_active_date == true ) {
			//Only within the active date do we consider subject/taxable wages and weeks.
			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages'] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages'] = 0;
			}
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'amount' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages_ytd'] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages_ytd'] = 0;
			}
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages_ytd'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages_ytd'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'ytd_amount' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_units'] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_units'] = 0;
			}
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_units'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_units'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'units' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_rate'] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_rate'] = 0;
			}
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_rate'] = ( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_units'] > 0 ) ? TTMath::div( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_units'] ) : 0;


			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] = 0;
			}
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'amount' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );

			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages_ytd'] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages_ytd'] = 0;
			}
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages_ytd'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages_ytd'], Misc::calculateIncludeExcludeAmount( $pse_obj->getColumn( 'ytd_amount' ), $pay_stub_entry_name_id, $deduction_include_psea_ids, $deduction_exclude_psea_ids ) );


			$pay_period_weeks = round( TTDate::getWeeks( ( TTDate::getEndDayEpoch( TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ) ) - TTDate::getBeginDayEpoch( TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ) ) ) ), 2 );

			//For unemployment reports, we need to know the weeks where renumeration was received, so count weeks between start/end date of pay period
			//Set pay period weeks once per transaction date (pay period)
			$first_run_id = min( array_keys( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp] ) );
			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$first_run_id][$user_id]['pay_period_weeks'] ) ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_period_weeks'] = $pay_period_weeks;
			}

			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$first_run_id][$user_id]['pay_period_taxable_wages_weeks'] )
					&& isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] )
					&& $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] > 0 ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_period_taxable_wages_weeks'] = $pay_period_weeks;
			}

			if ( !isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$first_run_id][$user_id]['pay_period_tax_withheld_weeks'] )
					&& isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['tax_withheld'] )
					&& $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['tax_withheld'] > 0 ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_period_tax_withheld_weeks'] = $pay_period_weeks;
			}
			unset( $first_run_id );

			if ( isset( $user_deduction_data[$company_deduction_id][$user_id] ) && $user_deduction_data[$company_deduction_id][$user_id]['fixed_amount'] != false ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['company_deduction_fixed_amount'] = $user_deduction_data[$company_deduction_id][$user_id]['fixed_amount'];
			}

			if ( isset( $user_deduction_data[$company_deduction_id][$user_id] ) && $user_deduction_data[$company_deduction_id][$user_id]['rate'] != false ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['company_deduction_rate'] = $user_deduction_data[$company_deduction_id][$user_id]['rate'];
			}

			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['company_deduction_district_name'] = $cd_obj->getDistrictName();
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['company_deduction_calculation_name'] = $cd_obj->getCalculationName();
		}

		return true;
	}

	function get12thDayOfMonth( $epoch ) {
		//Get the 12th day of the current month.
		$target_date = mktime( 0, 0, 0, TTDate::getMonth( $epoch ), 12, TTDate::getYear( $epoch ) ); //Should be the 12 day of the month.
		Debug::Text( ' 12th of Month Date: ' . TTDate::getDate( 'DATE', $target_date ), __FILE__, __LINE__, __METHOD__, 10 );

		return $target_date;
	}


	/**
	 * Required for some tax reports such as Indiana unemployment insurance to determine if the employee was paid in a pay period that spanned the 12th day of each month in the quarter.
	 * @param $filter_data
	 * @return array
	 * @throws ReflectionException
	 */
	function get12thDayOfMonthData( $filter_data ) {
		$retarr = [];

		//Determine the dates of the first 3 months.
		if ( isset( $filter_data['start_date'] ) && $filter_data['start_date'] != '' && isset( $filter_data['end_date'] ) && $filter_data['end_date'] != '' ) {
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */

			//Loop over the first 3 months.
			$tmp_month_epoch = $filter_data['start_date'];

			for( $i = 1; $i <= 3; $i++ ) {
				$tmp_month_epoch = $this->get12thDayOfMonth( $tmp_month_epoch );
				Debug::Text( ' 12th day of Month Epoch: ' . TTDate::getDate( 'DATE', $tmp_month_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

				//If the 12th of the month is past the end date, skip.
				if ( $tmp_month_epoch > $filter_data['end_date'] ) {
					Debug::Text( ' 12th day of Month Epoch: ' . TTDate::getDate( 'DATE', $tmp_month_epoch ) .' is past filter end date: '. TTDate::getDate( 'DATE', $filter_data['end_date'] ) .' skipping...', __FILE__, __LINE__, __METHOD__, 10 );
					break;
				}

				//Get the pay periods that cover the 12th day of the month.
				$employee_count_pay_periods = $pplf->getIDSByListFactory( $pplf->getByCompanyIdAndOverlapStartDateAndEndDate( $this->getUserObject()->getCompany(), $tmp_month_epoch, $tmp_month_epoch ) );

				if ( !empty( $employee_count_pay_periods ) ) {
					//Map each pay period ID to a column, so we can later determine which column to populate on the report for each user.
					foreach( $employee_count_pay_periods as $tmp_employee_count_pay_period_id ) {
						$this->pay_period_to_12th_day_of_month_map[$tmp_employee_count_pay_period_id] = 'paid_12th_day_month' . $i;
					}

					//Get all the users that were paid in those pay periods.
					$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
					$employee_count_filter_data = $filter_data;
					$employee_count_filter_data['pay_period_id'] = $employee_count_pay_periods;
					unset( $employee_count_filter_data['start_date'], $employee_count_filter_data['end_date'] );
					$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $employee_count_filter_data );
					Debug::Text( ' Pay Stub Rows: ' . $pslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $pslf->getRecordCount() > 0 ) {
						foreach ( $pslf as $ps_obj ) {
							if ( $ps_obj->getStatus() != 25 ) {
								$retarr[$ps_obj->getUser()][$ps_obj->getPayPeriod()]['paid_12th_day_month' . $i] = true; //Used for counting total number of employees.
							}
						}
					}
					unset( $employee_count_date, $employee_count_pay_periods );
				}

				$tmp_month_epoch = TTDate::incrementDate( $tmp_month_epoch, 30, 'day' ); //Move to next month at end of loop. Since its always the 12th of the month, we don't need to worry about 28-31 days.
			}

			unset( $pplf, $pslf );
		}

		return $retarr;
	}

	/**
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'pay_stub_entry' => [], 'user' => [], 'user_wage' => [] ];

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();
		$company_deduction_filter_data = [];

		$currency_convert_to_base = $this->getCurrencyConvertToBase();
		$base_currency_obj = $this->getBaseCurrencyObject();
		$this->handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data );
		$currency_options = $this->getOptions( 'currency' );

		$wage_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		if ( isset( $filter_data['company_deduction_id'] ) ) {
			$company_deduction_filter_data['id'] = $filter_data['company_deduction_id'];
		}

		if ( isset( $filter_data['payroll_remittance_agency_id'] ) ) {
			$company_deduction_filter_data['payroll_remittance_agency_id'] = $filter_data['payroll_remittance_agency_id'];
		}

		if ( isset( $filter_data['legal_entity_id'] ) ) {
			$company_deduction_filter_data['legal_entity_id'] = $filter_data['legal_entity_id'];
		}

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		//Always include date columns, because 'hire-date_stamp' is not recognized by the UserFactory. This greatly slows down the report though.
		$columns['hire_date'] = $columns['termination_date'] = $columns['birth_date'] = true;

		if ( isset( $columns['paid_12th_day_month1'] ) || isset( $columns['paid_12th_day_month2'] ) || isset( $columns['paid_12th_day_month3'] ) ) {
			$this->user_12th_day_of_month_data = $this->get12thDayOfMonthData( $filter_data );
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset( $filter_data['company_deduction_id'] ) == false ) {
				$filter_data['company_deduction_id'] = '';
			}

			[ $company_deduction_data, $company_deduction_merge_map ] = $this->getCompanyDeductionData( $this->getUserObject()->getCompany(), $company_deduction_filter_data, $columns, ( ( isset( $filter_data['start_date'] ) ) ? $filter_data['start_date'] : time() ), ( ( isset( $filter_data['end_date'] ) ) ? $filter_data['end_date'] : time() ) );
			$user_deduction_data = $this->getUserDeductionData( $this->getUserObject()->getCompany(), $filter_data['company_deduction_id'], ( ( isset( $filter_data['end_date'] ) ) ? $filter_data['end_date'] : time() ), $company_deduction_merge_map );
			//Debug::Arr($user_deduction_data, 'User Deduction Maximum Amount Data: ', __FILE__, __LINE__, __METHOD__, 10);

			if ( !isset( $filter_data['exclude_ytd_adjustment'] ) ) {
				$filter_data['exclude_ytd_adjustment'] = false;
			}

			//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

			//We have to use the same PSE records and combine them in different ways for different Tax/Deduction or Remittance Agencies.
			//  For example, the same earnings records are likely to count towards many different Tax/Deduction records.
			$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
			$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pself->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
			if ( $pself->getRecordCount() > 0 ) {
				if ( is_array( $company_deduction_data ) && count( $company_deduction_data ) > 0 ) {
					Debug::Text( 'Found PSE Records: ' . $pself->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					foreach ( $company_deduction_data as $payroll_remittance_agency_id => $payroll_remittance_agency_data ) {
						Debug::Text( '  Processing Remittance Agency: ' . $payroll_remittance_agency_id, __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $payroll_remittance_agency_data as $cd_obj ) {
							Debug::Text( '  Processing Company Deduction: ' . $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
							//Debug::Arr( $cd_obj->include_psea_ids, '    Include PSEA IDs: ', __FILE__, __LINE__, __METHOD__, 10 );
							//Debug::Arr( $cd_obj->exclude_psea_ids, '    Exclude PSEA IDs: ', __FILE__, __LINE__, __METHOD__, 10 );
							//Debug::Arr( $cd_obj->getPayStubEntryAccount(), '   Withheld PSEA IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

							foreach ( $pself as $key => $pse_obj ) {
								$this->addPayStubEntry( $cd_obj, $pse_obj, $user_deduction_data );
								$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
							}
						}
					}
				}

				//Loop through all records and handle maximum amounts.
				//  Do this down here, since with include/exclude amounts, we could reach the maximum amount,
				//	then the next row has an excluded (negative) amount, so now we've capped the amount at a maximum, and its reduced by -XX.XX.
				//  Instead just handle all the include/exclude amount above first, then once that is done go through and cap any necessary amounts.
				if ( count( $user_deduction_data ) > 0 ) {
					foreach ( $this->tmp_data['pay_stub_entry'] as $remittance_agency_id => $level1 ) {
						foreach ( $level1 as $company_deduction_id => $level2 ) {
							foreach ( $level2 as $date_stamp => $level3 ) {
								foreach ( $level3 as $run_id => $level4 ) {
									foreach ( $level4 as $user_id => $row ) {
										//  This can't be easily simplified with Misc::getAmountToLimit() or getAmountDifferenceToLimit().
										if ( isset( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] )
												&& isset( $user_deduction_data[$company_deduction_id][$user_id] )
												&& $user_deduction_data[$company_deduction_id][$user_id]['maximum_pay_stub_entry_amount'] > 0
												&& $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages_ytd'] > $user_deduction_data[$company_deduction_id][$user_id]['maximum_pay_stub_entry_amount']
										) {
											Debug::Text( 'Before Current Taxable Wages: ' . $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] . ' YTD: ' . $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages_ytd'], __FILE__, __LINE__, __METHOD__, 10 );
											//Make sure taxable wages abides by maximum amount properly.
											$tmp_taxable_wages_ytd_diff = TTMath::sub( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages_ytd'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] );
											$tmp_taxable_wages_max_diff = TTMath::sub( $user_deduction_data[$company_deduction_id][$user_id]['maximum_pay_stub_entry_amount'], $tmp_taxable_wages_ytd_diff );
											//Debug::Text('  Taxable Wages YTD Diff: '. $tmp_taxable_wages_ytd_diff .' Max Diff: '. $tmp_taxable_wages_max_diff, __FILE__, __LINE__, __METHOD__, 10);
											if ( $tmp_taxable_wages_ytd_diff < $user_deduction_data[$company_deduction_id][$user_id]['maximum_pay_stub_entry_amount'] ) {
												$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] = $tmp_taxable_wages_max_diff;
											} else {
												$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] = 0;
											}

											$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages_ytd'] = $user_deduction_data[$company_deduction_id][$user_id]['maximum_pay_stub_entry_amount'];

											$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['excess_wages'] = TTMath::sub( $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['subject_wages'], $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['taxable_wages'] );
											unset( $tmp_taxable_wages_ytd_diff, $tmp_taxable_wages_max_diff );
										}
										//Debug::Text('After Current Taxable Wages: '. $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$user_id]['taxable_wages'] .' YTD: '. $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$user_id]['taxable_wages_ytd'], __FILE__, __LINE__, __METHOD__, 10);
									}
								}
							}
						}
					}
					unset( $level1, $level2, $level3, $remittance_agency_id, $date_stamp, $user_id, $row );
				}
			}
		}
		//Debug::Arr($this->tmp_data['pay_stub_entry'], 'Pay Stub Entry Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$this->getColumnDataConfig(), [ 'province' => true, 'hire_date' => true, 'termination_date' => true, 'birth_date' => true, 'title_id' => true ] ) );
			$this->tmp_data['user_wage'][$u_obj->getId()] = [];
			$this->tmp_data['user'][$u_obj->getId()]['total_user'] = 1;
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$utlf = TTnew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
		$utlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' User Title Total Rows: ' . $utlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$user_title_column_config = array_merge( (array)Misc::removeKeyPrefix( 'user_title_', (array)$this->getColumnDataConfig() ), [ 'id' => true, 'name' => true, 'custom_field' => true ] ); //Always include title_id column so we can merge title data.
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $utlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Titles...' ) );
		foreach ( $utlf as $key => $ut_obj ) {
			$this->tmp_data['user_title'][$ut_obj->getId()] = Misc::addKeyPrefix( 'user_title_', (array)$ut_obj->getObjectAsArray( $user_title_column_config ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Get remittance agency data for joining.
		$pralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $pralf */
		$pralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pralf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $pralf as $key => $ra_obj ) {
			$this->tmp_data['payroll_remittance_agency'][$ra_obj->getId()] = Misc::addKeyPrefix( 'payroll_remittance_agency_', (array)$ra_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'primary_identification' => true ] ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Company Deduction data for joining...
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $company_deduction_filter_data );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $cdlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach ( $cdlf as $key => $cd_obj ) {
				$this->tmp_data['company_deduction'][$cd_obj->getId()] = Misc::addKeyPrefix( 'company_deduction_', (array)$cd_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'payroll_remittance_agency_id' => true ] ) );
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		if ( count( array_intersect( [ 'user_wage_type', 'user_wage_effective_date', 'user_wage_wage', 'user_wage_hourly_rate' ], array_keys( $columns ) ) ) > 0 ) {
			//Get user wage data for joining.
			$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
			$filter_data['wage_group_id'] = [ TTUUID::getZeroID() ]; //Use default wage groups only.
			$filter_data['effective_date'] = ( isset( $filter_data['end_date'] ) ? $filter_data['end_date'] : null ); //Only show wages up to the end of the filter date if its specified.
			$filter_data['permission_children_ids'] = $wage_permission_children_ids;
			$uwlf->getAPILastWageSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
			Debug::Text( ' User Wage Rows: ' . $uwlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
			unset( $columns['note'] ); //Prevent wage note from overwriting user note.
			foreach ( $uwlf as $key => $uw_obj ) {
				if ( $this->getPermissionObject()->isPermissionChild( $uw_obj->getUser(), $wage_permission_children_ids ) ) { //This is required in cases where they have 'view'(all) wage permisisons, but only view_child user permissions. As the SQL will return all employees wages, which then need to be filtered out here.
					$this->tmp_data['user_wage'][$uw_obj->getUser()] = Misc::addKeyPrefix( 'user_wage_', (array)$uw_obj->getObjectAsArray( [ 'type' => true, 'effective_date' => true ] ) );
					$this->tmp_data['user_wage'][$uw_obj->getUser()]['user_wage_wage'] = $uw_obj->getWage();              //Get raw unformatted value as columnFormatter() will format it later on.
					$this->tmp_data['user_wage'][$uw_obj->getUser()]['user_wage_hourly_rate'] = $uw_obj->getHourlyRate(); //Get raw unformatted value as columnFormatter() will format it later on.

					if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
						$this->tmp_data['user_wage'][$uw_obj->getUser()]['user_wage_current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
						if ( isset( $this->tmp_data['user'][$uw_obj->getUser()]['user_wage_currency_rate'] ) ) {
							$this->tmp_data['user_wage'][$uw_obj->getUser()]['user_wage_hourly_rate'] = $base_currency_obj->getBaseCurrencyAmount( $uw_obj->getHourlyRate(), $this->tmp_data['user'][$uw_obj->getUser()]['currency_rate'], $currency_convert_to_base );
							$this->tmp_data['user_wage'][$uw_obj->getUser()]['user_wage_wage'] = $base_currency_obj->getBaseCurrencyAmount( $uw_obj->getWage(), $this->tmp_data['user'][$uw_obj->getUser()]['currency_rate'], $currency_convert_to_base );
						}
					}

					$this->tmp_data['user_wage'][$uw_obj->getUser()]['user_wage_effective_date'] = ( isset( $this->tmp_data['user_wage'][$uw_obj->getUser()]['user_wage_effective_date'] ) ) ? TTDate::parseDateTime( $this->tmp_data['user_wage'][$uw_obj->getUser()]['user_wage_effective_date'] ) : null;
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'TMP Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		//Merge time data with user data
		$key = 0;
		if ( isset( $this->tmp_data['pay_stub_entry'] ) ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['pay_stub_entry'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

			$column_keys = array_keys( $this->getColumnDataConfig() );
			$filter_data = $this->getFilterConfig();
			if ( isset( $filter_data['end_date'] ) ) {
				$filter_end_date = $filter_data['end_date'];
			} else {
				$filter_end_date = null;
			}

			//foreach( $this->tmp_data['pay_stub_entry'] as $date_stamp => $level_1 ) {
			foreach ( $this->tmp_data['pay_stub_entry'] as $remittance_agency_id => $level_0 ) {
				foreach ( $this->tmp_data['pay_stub_entry'][$remittance_agency_id] as $company_deduction_id => $level_1 ) {
					foreach ( $level_1 as $date_stamp => $level_2 ) {
						foreach ( $level_2 as $run_id => $level_3 ) {
							foreach ( $level_3 as $user_id => $row ) {
								if ( isset( $this->tmp_data['user'][$user_id] ) ) {
									$date_columns = TTDate::getReportDates( 'transaction', $date_stamp, false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ] );

									if ( isset( $this->tmp_data['user'][$user_id]['title_id'] ) && isset( $this->tmp_data['user_title'][$this->tmp_data['user'][$user_id]['title_id']] ) ) {
										$tmp_user_title = $this->tmp_data['user_title'][$this->tmp_data['user'][$user_id]['title_id']];
									} else {
										$tmp_user_title = [];
									}

									if ( isset( $this->tmp_data['user'][$user_id]['hire_date'] ) ) {
										$hire_date_columns = TTDate::getReportDates( 'hire', TTDate::parseDateTime( $this->tmp_data['user'][$user_id]['hire_date'] ), false, $this->getUserObject(), null, $column_keys );
									} else {
										$hire_date_columns = [];
									}

									if ( isset( $this->tmp_data['user_wage'][$user_id] ) ) {
										$user_wage_columns = $this->tmp_data['user_wage'][$user_id];
									} else {
										$user_wage_columns = [];
									}

									if ( isset( $this->tmp_data['user'][$user_id]['termination_date'] ) ) {
										$termination_date_columns = TTDate::getReportDates( 'termination', TTDate::parseDateTime( $this->tmp_data['user'][$user_id]['termination_date'] ), false, $this->getUserObject(), null, $column_keys );
									} else {
										$termination_date_columns = [];
									}

									if ( isset( $this->tmp_data['user'][$user_id]['birth_date'] ) ) {
										$birth_date_columns = TTDate::getReportDates( 'birth', TTDate::parseDateTime( $this->tmp_data['user'][$user_id]['birth_date'] ), false, $this->getUserObject(), null, $column_keys );
									} else {
										$birth_date_columns = [];
									}

									if ( isset( $filter_end_date ) ) {
										$filter_end_date_columns = TTDate::getReportDates( 'filter_end', $filter_end_date, false, null, null, $column_keys );
									} else {
										$filter_end_date_columns = [];
									}

									$processed_data = [
										//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
										//'pay_stub' => array('sort' => $row['pay_stub_transaction_date'], 'display' => TTDate::getDate('DATE', $row['pay_stub_transaction_date'] ) ),
									];
									//Need to make sure PSEA IDs are strings not numeric otherwise array_merge will re-key them.

									if ( isset( $this->tmp_data['company_deduction'][$company_deduction_id] ) ) {
										$tmp_company_deduction = $this->tmp_data['company_deduction'][$company_deduction_id];
									} else {
										$tmp_company_deduction = [];
									}

									if ( isset( $this->tmp_data['payroll_remittance_agency'][$remittance_agency_id] ) ) {
										$tmp_payroll_remittance_agency = $this->tmp_data['payroll_remittance_agency'][$remittance_agency_id];
									} else {
										$tmp_payroll_remittance_agency = [];
									}

									$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $tmp_company_deduction, $tmp_payroll_remittance_agency, $tmp_user_title, $row, $date_columns, $hire_date_columns, $user_wage_columns, $termination_date_columns, $birth_date_columns, $filter_end_date_columns, $processed_data );

									$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
									$key++;
								}
							}
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
	 * Formats report data for exporting to TimeTrex payment service.
	 * @param $prae_obj
	 * @param $pra_obj
	 * @param $rs_obj
	 * @param $pra_user_obj
	 * @return array|bool
	 */
	function getPaymentServicesData( $prae_obj, $pra_obj, $rs_obj, $pra_user_obj ) {
		//Make sure we have the columns we need.
		$report_data['config'] = $this->getConfig();
		$report_data['config']['columns'] = [ 'payroll_remittance_agency_name', 'transaction-date_stamp', 'subject_wages', 'taxable_wages', 'tax_withheld', 'total_user' ];
		$report_data['config']['group'] = [ 'payroll_remittance_agency_name', 'transaction-date_stamp' ];
		$this->setConfig( (array)$report_data['config'] );

		$output_data = $this->getOutput( 'payment_services' );
		Debug::Arr( $output_data, 'Raw Report data!', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->hasData() ) {
			//Get last Grand Total row.
			$last_row = end( $output_data );

			if ( isset( $last_row['_total'] ) ) {
				$batch_id = date( 'M d', $prae_obj->getEndDate() );

				$amount_due = ( isset( $last_row['tax_withheld'] ) ) ? $last_row['tax_withheld'] : null;

				$retarr = [
						'object'               => __CLASS__,
						'user_success_message' => TTi18n::gettext( 'Payment submitted successfully for $%1', [ TTMath::MoneyRound( $amount_due ) ] ),
						'agency_report_data'   => [
								'total_employees' => ( isset( $last_row['total_user'] ) ) ? (int)$last_row['total_user'] : null,
								'subject_wages'   => ( isset( $last_row['subject_wages'] ) ) ? $last_row['subject_wages'] : null,
								'taxable_wages'   => ( isset( $last_row['taxable_wages'] ) ) ? $last_row['taxable_wages'] : null,
								'amount_withheld' => ( isset( $last_row['tax_withheld'] ) ) ? $last_row['tax_withheld'] : null,
								'amount_due'      => $amount_due,

								'remote_batch_id' => $batch_id,

								//Generate a consistent remote_id based on the exact time period, the remittance agency event, and batch ID.
								//This helps to prevent duplicate records from be created, as well as work across separate or split up batches that may be processed.
								//  This needs to take into account different start/end date periods, so we don't try to overwrite records from last year.
								'remote_id'       => TTUUID::convertStringToUUID( md5( $prae_obj->getId() . $prae_obj->getStartDate() . $prae_obj->getEndDate() ) ),
						],
				];

				return $retarr;
			}
		}

		Debug::Text( 'No report data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _output( $format = null ) {
		//Since eFile format is not supported with this report, but its often used from the Tax Wizard, since some State Income Tax reports (ie: OR) use this instead of the State UI report, just force it to CSV so *something* downloads for the user.
		if ( $format == 'efile' ) {
			$format = 'csv';
		}

		return parent::_output( $format );
	}
}

?>
