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

//
// See https://github.com/giftcards/FixedWidth or https://packagist.org/packages/devvoh/fixedwidth for handling fixed with files.
//
// https://support.na.sage.com/selfservice/viewContent.do?externalId=32877&sliceId=1
//  Florida and California have their own XML formats. Ohio supports ICESA and XML. So maybe start with ICESA?
//
// State Supplemental Information:
//  https://support.na.sage.com/selfservice/viewdocument.do?externalId=32880&sliceId=1&cmd=&ViewedDocsListHelper=com.kanisa.apps.common.BaseViewedDocsListHelperImpl&noCount=true
//
// SSA MMREF-1 format with unemployment info: (Record: RE, RS) https://edd.ca.gov/siteassets/files/pdf_pub_ctr/de8300.pdf
// ICESA Standard format for all states: https://esdorchardstorage.blob.core.windows.net/esdwa/Default/ESDWAGOV/employer-Taxes/EAMS-bulk-filing-specifications.pdf
//ICESA File Format Requirements
//The following are the ICESA fle format requirements:
//• IBM compatible.
//• Must be recorded in American Standard Code for Information Interchange (ASCII) format.
//• Uncompressed mode.
//• Data must be written in UPPERCASE letters only.
//• Filename: ICESA.
//• 275 position record length.
//
//

/**
 * @package Modules\Report
 */
class USStateUnemploymentReport extends Report {
	/**
	 * @var array
	 */
	private $user_12th_day_of_month_data;
	private $pay_period_to_12th_day_of_month_map;

	/**
	 * USUnemploymentReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'US State Unemployment Report' );
		$this->file_name = 'us_state_unemployment_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_us_state_unemployment', $user_id, $company_id ) ) {
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
				$retval = array_merge( parent::getOptions( 'default_output_format' ),
									   [
											   '-1120-efile'           => TTi18n::gettext( 'eFile' ),
									   ]
				);
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
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'USUnemploymentReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'USUnemploymentReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'USUnemploymentReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'USUnemploymentReport', 'custom_column' );
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

					'-3010-company_deduction_rate' => TTi18n::gettext( 'Tax/Deduction Rate' ),
				];

				$retval = array_merge( $retval, $this->getOptions( 'pay_stub_account_amount_columns', [ 'include_ytd_amount' => true ] ) );
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
							}

							//Add units for Total Gross so they can get a total number of hours/units that way too.
							if ( $psea_obj->getType() == 40 && isset( $default_linked_columns[0] ) && $default_linked_columns[0] == $psea_obj->getID() ) {
								$retval['-5' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PU:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'Units' ) . ']';
							}

							if ( isset( $params['include_ytd_amount'] ) ) { //This is used for Tax/Deduction Custom Formulas.
								if ( $psea_obj->getType() != 50 ) { //Accruals, display balance/YTD amount.
									$retval['-6' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PY:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'YTD' ) . ']';
								}
							}

							if ( $psea_obj->getType() == 50 ) { //Accruals, display balance/YTD amount.
								$retval['-6' . str_pad( $i, 3, 0, STR_PAD_LEFT ) . '-PY:' . $psea_obj->getID()] = $prefix . $psea_obj->getName() . ' [' . TTi18n::getText( 'Balance' ) . ']';
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
								|| $column == 'tax_withheld' || $column == 'subject_rate' ) {
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
								} else if ( strpos( $column, '_ytd' ) !== false || substr( $column, 0, 2 ) == 'PY' || strpos( $column, 'paid_12th_day_month' ) !== false ) { //YTD Amounts must use "max()" when grouping rather than sum()
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
						'-1060-by_payroll_remittance_agency_by_employee+taxes'                      => TTi18n::gettext( 'Tax by Remittance Agency/Employee' ),
						'-1070-by_payroll_remittance_agency_by_company_deduction_by_employee+taxes' => TTi18n::gettext( 'Tax by Remittance Agency/Tax/Employee' ),
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
			case 'form_setup_states':
				$retval = Misc::prependArray( [ 0 => TTi18n::getText( '-- Please Choose --' ) ], $this->getUserObject()->getCompanyObject()->getOptions( 'province', 'US' ) );
				break;
			case 'form_setup_state_codes':
				$retval = [
						0                                    => TTi18n::gettext( '-- Other --' ),
				];

				$cflf = TTnew( 'CustomFieldListFactory' ); /** @var CustomFieldListFactory $cflf */

				//Put a colon or underscore in the name, thats how we know it needs to be replaced.

				$branch_options = $cflf->getByCompanyIdAndParentTableCustomPrefixArray( $this->getUserObject()->getCompany(), ['branch'], '-1000-branch_', TTi18n::getText( 'Branch' ) . ': ' );
				if ( !is_array( $branch_options ) ) {
					$branch_options = [];
				}
				$department_options = $cflf->getByCompanyIdAndParentTableCustomPrefixArray( $this->getUserObject()->getCompany(), ['department'], '-2000-department_', TTi18n::getText( 'Department' ) . ': ' );
				if ( !is_array( $department_options ) ) {
					$department_options = [];
				}
				$title_options = $cflf->getByCompanyIdAndParentTableCustomPrefixArray( $this->getUserObject()->getCompany(), ['user_title'], '-3000-user_title_', TTi18n::getText( 'Employee Title' ) . ': ' );
				if ( !is_array( $title_options ) ) {
					$title_options = [];
				}
				$employee_options = $cflf->getByCompanyIdAndParentTableCustomPrefixArray( $this->getUserObject()->getCompany(), ['users'], '-4000-user_', TTi18n::getText( 'Employee' ) . ': ' );
				if ( !is_array( $employee_options ) ) {
					$employee_options = [];
				}

				$retval = array_merge( $retval, (array)$branch_options, (array)$department_options, (array)$title_options, (array)$employee_options );
				break;
			default:
				//Call report parent class options function for options valid for all reports.
				$retval = $this->__getOptions( $name );
				break;
		}

		return $retval;
	}

	/**
	 * @return mixed
	 */
	function getFormObject() {
		if ( !isset( $this->form_obj['gf'] ) || !is_object( $this->form_obj['gf'] ) ) {
			//
			//Get all data for the form.
			//
			require_once( Environment::getBasePath() . '/classes/GovernmentForms/GovernmentForms.class.php' );

			$gf = new GovernmentForms();

			$this->form_obj['gf'] = $gf;

			return $this->form_obj['gf'];
		}

		return $this->form_obj['gf'];
	}

	/**
	 * @return bool
	 */
	function clearFormObject() {
		$this->form_obj['gf'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getUSStateUIObject() {
		if ( !isset( $this->form_obj['us_state_ui'] ) || !is_object( $this->form_obj['us_state_ui'] ) ) {
			$this->form_obj['us_state_ui'] = $this->getFormObject()->getFormObject( 'state_ui', 'US' );

			return $this->form_obj['us_state_ui'];
		}

		return $this->form_obj['us_state_ui'];
	}

	/**
	 * @return bool
	 */
	function clearUSStateUIObject() {
		$this->form_obj['us_state_ui'] = false;

		return true;
	}

	/**
	 * @param $company_id
	 * @param $filter_data
	 * @param $columns
	 * @return array
	 */
	function getCompanyDeductionData( $company_id, $filter_data, $columns, $report_filter_start_date, $report_filter_end_date ) {
		$company_deduction_data = [];

		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		//Order by calculation_id DESC so Federal Taxes are always the parent of Fixed Amount records for example.
		//  To better handle cases where multiple tax/deductions exist for each year (ie: UI - 2022, UI - 2023) order the most recent year first. As its likely prior years will be excluded by elibility dates anyways.
		$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, $filter_data, null, null, null, [ 'calculation_id' => 'desc', 'calculation_order' => 'asc', 'start_date' => 'desc' ] );
		Debug::Text( 'Company Deductions: ' . $cdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $cdlf->getRecordCount() > 0 ) {
			$duplicate_pay_stub_entry_account_map = [];

			//Splitting or combining data across agency/company deductions is important to prevent a report from duplicating subject wages.
			//  For example if two taxes are selected (ie: 2x Workers Comp) and the employee has both deducted, the subject wages might be doubled up.
			//  However if the employee is assigned to State Income Tax and State Addl. Income Tax both going to the same PS Account, the amounts shouldn't get doubled up.
			if ( isset( $columns['payroll_remittance_agency_name'] ) ) {
				$enable_split_by_payroll_remittance_agency = true;
			} else {
				$enable_split_by_payroll_remittance_agency = false;
			}

			if ( isset( $columns['company_deduction_name'] ) ) {
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
				if ( $enable_split_by_company_deduction == true &&
						strtoupper( $cd_obj->getCountry() ) == 'US' && in_array( $cd_obj->getCalculation(), [ 200, 300 ] ) ) {
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
		return $company_deduction_data;
	}

	/**
	 * @param $company_id
	 * @param $filter_data
	 * @return array
	 */
	function getUserDeductionData( $company_id, $filter_data, $end_date ) {
		//To help determine MaximumTaxableWages, we need to get the UserDeduction records and call getMaximumPayStubEntryAccountAmount().
		$user_deduction_data = [];
		$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
		$udlf->getByCompanyIdAndCompanyDeductionId( $company_id, $filter_data, null, [ 'cdf.calculation_id' => 'desc', 'cdf.calculation_order' => 'asc', 'cdf.start_date' => 'desc', 'cdf.id' => 'asc' ] );
		if ( $udlf->getRecordCount() > 0 ) {
			foreach ( $udlf as $ud_obj ) {
				$tmp_maximum_pay_stub_entry_account_amount = $ud_obj->getMaximumPayStubEntryAccountAmount( $end_date );
				if ( ( $tmp_maximum_pay_stub_entry_account_amount != false || $ud_obj->getRate() != false ) || ( $ud_obj->getStartDate() != '' || $ud_obj->getEndDate() != '' ) ) {
					$user_deduction_data[$ud_obj->getCompanyDeduction()][$ud_obj->getUser()] = [ 'maximum_pay_stub_entry_amount' => $tmp_maximum_pay_stub_entry_account_amount, 'rate' => $ud_obj->getRate(), 'obj' => $ud_obj ];
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
					'pay_period_transaction_date' => strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ),
					'pay_period'                  => strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ),
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
			Debug::Text( '      Date Restrictions Found... User: '. $user_id .' Is Active: ' . (int)$is_active_date . ' Date: ' . TTDate::getDate( 'DATE', $this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
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

			if ( isset( $user_deduction_data[$company_deduction_id][$user_id] ) && $user_deduction_data[$company_deduction_id][$user_id]['rate'] != false ) {
				$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['company_deduction_rate'] = $user_deduction_data[$company_deduction_id][$user_id]['rate'];
			}

			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['company_deduction_calculation_name'] = $cd_obj->getCalculationName();
			$this->tmp_data['pay_stub_entry'][$remittance_agency_id][$company_deduction_id][$date_stamp][$run_id][$user_id]['company_deduction_district_name'] = $cd_obj->getDistrictName();
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

		//Always include 12th day of month data when eFiling, as some state file formats require it.
		if ( $format == 'efile' || isset( $columns['paid_12th_day_month1'] ) || isset( $columns['paid_12th_day_month2'] ) || isset( $columns['paid_12th_day_month3'] ) ) {
			$this->user_12th_day_of_month_data = $this->get12thDayOfMonthData( $filter_data );
		}

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		if ( $ulf->getRecordCount() > 0 ) {
			if ( isset( $filter_data['company_deduction_id'] ) == false ) {
				$filter_data['company_deduction_id'] = '';
			}

			$company_deduction_data = $this->getCompanyDeductionData( $this->getUserObject()->getCompany(), $company_deduction_filter_data, $columns, ( ( isset( $filter_data['start_date'] ) ) ? $filter_data['start_date'] : time() ), ( ( isset( $filter_data['end_date'] ) ) ? $filter_data['end_date'] : time() ) );
			$user_deduction_data = $this->getUserDeductionData( $this->getUserObject()->getCompany(), $filter_data['company_deduction_id'], ( ( isset( $filter_data['end_date'] ) ) ? $filter_data['end_date'] : time() ) );
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
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$this->getColumnDataConfig(), [ 'legal_entity_id' => true, 'province' => true, 'hire_date' => true, 'termination_date' => true, 'birth_date' => true, 'title_id' => true ] ) );
			$this->tmp_data['user'][$u_obj->getId()] = array_merge( $this->tmp_data['user'][$u_obj->getId()], Misc::addKeyPrefix( 'user_', (array)$u_obj->getObjectAsArray( [ 'custom_field' => true ] ) ) );
			$this->tmp_data['user'][$u_obj->getId()]['user_id'] = $u_obj->getId();
			$this->tmp_data['user_wage'][$u_obj->getId()] = [ 'user_wage_wage' => 0, 'user_wage_hourly_rate' => 0 ];
			$this->tmp_data['user'][$u_obj->getId()]['total_user'] = 1;

			//$this->form_data['user'][$u_obj->getLegalEntity()][$u_obj->getId()] = $this->tmp_data['user'][$u_obj->getId()];
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Get legal entity data for joining.
		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
		$lelf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), ( isset( $filter_data['legal_entity_id'] ) ? [ 'id' => $filter_data['legal_entity_id'] ] : [] ) );
		Debug::Text( ' Legal Entity Total Rows: ' . $lelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $lelf->getRecordCount(), null, TTi18n::getText( 'Retrieving Legal Entity Data...' ) );
		if ( $lelf->getRecordCount() > 0 ) {
			foreach ( $lelf as $key => $le_obj ) {
				$this->tmp_data['legal_entity'][$le_obj->getId()] = $this->form_data['legal_entity'][$le_obj->getId()] = $le_obj;
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
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

		//Get federal remittance agency for joining. Do this separate from state agencies, as those need to filter based on the specific company_deduction_id's, which would never include federal.
		$federal_pra_filter_data = $filter_data;
		unset( $federal_pra_filter_data['company_deduction_id'] );
		$federal_pra_filter_data['type_id'] = [ 10 ]; //10=Federal
		$federal_pra_filter_data['country'] = [ 'US' ];
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $federal_pra_filter_data );
		Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $lelf->getRecordCount(), null, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			foreach ( $ralf as $key => $ra_obj ) {
				if ( $ra_obj->parseAgencyID( null, 'id' ) == 10 ) { //Only consider 10=IRS
					if ( $ra_obj->getType() == 10 ) { //10=Federal
						Debug::Text( '   Adding Remittance Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( !isset( $this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][ '00'] ) ) {
							$this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][ '00'] = $ra_obj->getId(); //Map province to a specific remittance object below.
						} else {
							Debug::Text( '   WARNING: Remittance Agency already exists, check to ensure none of duplicate "Agency" fields specified... Type: ' . $ra_obj->getType() .' Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( '   Skipping Remittance Agency Based on Type ID: ' . $ra_obj->getType() .' Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					}

					$this->form_data['remittance_agency_obj'][$ra_obj->getId()] = $ra_obj;

					$this->tmp_data['payroll_remittance_agency'][$ra_obj->getId()] = Misc::addKeyPrefix( 'payroll_remittance_agency_', (array)$ra_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'primary_identification' => true ] ) );
				} else {
					Debug::Text( ' Skipping Remittance Agency Based on ID: ' . $ra_obj->parseAgencyID( null, 'id' ) .' Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}
		unset( $federal_pra_filter_data );

		//Get state remittance agency for joining.
		$state_pra_filter_data = $filter_data;
		$state_pra_filter_data['type_id'] = [ 20 ]; //20=State.
		$state_pra_filter_data['country'] = [ 'US' ];
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $state_pra_filter_data );
		Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $lelf->getRecordCount(), null, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			foreach ( $ralf as $key => $ra_obj ) {
				if ( $ra_obj->getType() == 20 ) { //20=Province/State -- Some states combined tax with UI, so we need to include all state agencies related to the company_deduction_ids
					Debug::Text( '   Adding Remittance Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( !isset( $this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][$ra_obj->getProvince()] ) ) {
						$this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][$ra_obj->getProvince()] = $ra_obj->getId(); //Map province to a specific remittance object below.
					} else {
						Debug::Text( '   WARNING: Remittance Agency already exists, check to ensure none of duplicate "Agency" fields specified... Type: ' . $ra_obj->getType() .' Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '   Skipping Remittance Agency Based on Type ID: ' . $ra_obj->getType() .' Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				}

				$this->form_data['remittance_agency_obj'][$ra_obj->getId()] = $ra_obj;

				$this->tmp_data['payroll_remittance_agency'][$ra_obj->getId()] = Misc::addKeyPrefix( 'payroll_remittance_agency_', (array)$ra_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'primary_identification' => true ] ) );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		//Company Deduction data for joining...
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $company_deduction_filter_data );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $cdlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach ( $cdlf as $key => $cd_obj ) {
				$this->tmp_data['company_deduction'][$cd_obj->getId()] = Misc::addKeyPrefix( 'company_deduction_', (array)$cd_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'calculation_id' => true, 'province' => true, 'payroll_remittance_agency_id' => true ] ) );

				//Find the tax rate specifed in the CompanyDeduction record.
				//  Some states like WA may have multiple tax/deduction records, so we need filter to the exact one of course.
				$this->form_data['company_deduction'][$cd_obj->getLegalEntity()][$cd_obj->getId()] = $this->tmp_data['company_deduction'][$cd_obj->getId()];
				if ( is_object( $cd_obj->getPayrollRemittanceAgencyObject() ) ) {
					$pra_obj = $cd_obj->getPayrollRemittanceAgencyObject();
					if ( $pra_obj->parseAgencyID( null, 'type_id' ) == 20 && $cd_obj->getUserValue1() != 0 ) { //20=Unemployment Insurance
						$this->form_data['company_deduction'][$cd_obj->getLegalEntity()]['ui_tax_rate'] = $cd_obj->getUserValue1(); //FIXME: This must narrow down the CompanyDeduction record to specific US - State UI calculation type so we can get the proper rate.
					}
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		if ( $format == 'efile' || count( array_intersect( [ 'user_wage_type', 'user_wage_effective_date', 'user_wage_wage', 'user_wage_hourly_rate' ], array_keys( $columns ) ) ) > 0 ) {
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
	function _preProcess( $format ) {
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

			//Total data per employee for the UI reports. Just include the columns that are necessary for the form.
			if ( is_array( $this->data ) && !( $format == 'html' || $format == 'pdf' ) ) {
				Debug::Text( 'Calculating Form Data...', __FILE__, __LINE__, __METHOD__, 10 );

				//List columns that need to be totaled or used last for each row.
				$columns_to_total = [ 'tax_withheld', 'subject_wages', 'subject_units', 'taxable_wages', 'excess_wages', 'paid_12th_day_month1', 'paid_12th_day_month2', 'paid_12th_day_month3', 'pay_period_taxable_wages_weeks', 'pay_period_tax_withheld_weeks', 'pay_period_weeks' ];
				$columns_to_use_last = [ 'subject_wages_ytd', 'taxable_wages_ytd' ];

				foreach ( $this->data as $row ) {
					if ( !isset( $this->form_data['user'][$row['legal_entity_id']][$row['company_deduction_id']][$row['user_id']] ) ) {
						$this->form_data['user'][$row['legal_entity_id']][$row['company_deduction_id']][$row['user_id']] = array_merge( [ 'user_id' => $row['user_id'] ], Misc::preSetArrayValues( [], [ 'tax_withheld', 'subject_wages', 'subject_units', 'taxable_wages', 'excess_wages', 'paid_12th_day_month1', 'paid_12th_day_month2', 'paid_12th_day_month3', 'pay_period_taxable_wages_weeks', 'pay_period_tax_withheld_weeks' ], 0 ), $row );
					} else {
						foreach ( $columns_to_total as $key ) {
							if ( isset( $row[$key] ) ) {
								$this->form_data['user'][$row['legal_entity_id']][$row['company_deduction_id']][$row['user_id']][$key] = TTMath::add( $this->form_data['user'][$row['legal_entity_id']][$row['company_deduction_id']][$row['user_id']][$key] ?? 0, $row[$key] );
							}
						}

						foreach ( $columns_to_use_last as $key ) {
							if ( isset( $row[$key] ) ) {
								$this->form_data['user'][$row['legal_entity_id']][$row['company_deduction_id']][$row['user_id']][$key] = $row[$key];
							}
						}

						//Subject Rate is essentially an average over the total of all rows subject_wages / subject_units, so it needs to be handled in a special way.
						$this->form_data['user'][$row['legal_entity_id']][$row['company_deduction_id']][$row['user_id']]['subject_rate'] = TTMath::div( $this->form_data['user'][$row['legal_entity_id']][$row['company_deduction_id']][$row['user_id']]['subject_wages'], $this->form_data['user'][$row['legal_entity_id']][$row['company_deduction_id']][$row['user_id']]['subject_units'] );
					}
				}
			}
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}


	function _flattenFormDataByCompanyDeduction( $data ) {
		//Need to take each CompanyDeduction layer of the array and flatten it to each user so we can access the data combined.
		//  ie: For California, we need to get subject wages and tax withheld for PIT (income tax) and UI (SDI/ETT) separately.
		//
		//  So loop through each company_deduction and prefix the data to that specific tax, ie: 210_CA_subject_wages = UI, 200_CA_subject_wages = Tax.

		$retarr = [];

		if ( isset( $this->form_data['company_deduction'] ) ) {
			foreach( $this->form_data['company_deduction'] as $legal_entity_id => $company_deductions ) {
				foreach ( $company_deductions as $company_deduction_id => $company_deduction_data ) {
					if ( TTUUID::isUUID( $company_deduction_id ) && isset( $data[$company_deduction_id] ) ) {
						foreach ( $data[$company_deduction_id] as $user_id => $user_rows ) {
							if ( !isset( $retarr[$user_id] ) ) {
								$retarr[$user_id] = [];
							}

							$company_deduction_prefix = $company_deduction_data['company_deduction_calculation_id'];
							if ( $company_deduction_data['company_deduction_province'] != '' ) {
								$company_deduction_prefix .= '_'. $company_deduction_data['company_deduction_province'];
							}

							$company_deduction_prefixed_data = [
									$company_deduction_prefix.'_taxable_wages' => $user_rows['taxable_wages'] ?? null,
									$company_deduction_prefix.'_taxable_wages_ytd' => $user_rows['taxable_wages_ytd'] ?? null,

									$company_deduction_prefix.'_tax_withheld' => $user_rows['tax_withheld'] ?? null,

									$company_deduction_prefix.'_subject_wages' => $user_rows['subject_wages'] ?? null,
									$company_deduction_prefix.'_subject_wages_ytd' => $user_rows['subject_wages_ytd'] ?? null,

									$company_deduction_prefix.'_subject_units' => $user_rows['subject_units'] ?? null,
									$company_deduction_prefix.'_excess_wages' => $user_rows['excess_wages'] ?? null,
							];

							$retarr[$user_id] = array_merge( $user_rows, $retarr[$user_id], $company_deduction_prefixed_data );
						}
					}
				}
			}
		}

		return $retarr;
	}

	function getFormSetupField( $setup_data, $row, $default_value = null ) {
		$retval = $default_value;

		if ( is_string( $setup_data ) && $setup_data != '' ) {
			if ( strpos( $setup_data, 'custom_field-' ) === false && $setup_data != '' ) { //Static value for entire report.
				$retval = $setup_data;
			} else if ( isset( $row[$setup_data .'_id'] ) ) { //Custom field dropdown box
				$retval = current( $row[$setup_data .'_id'] ); //Get first array value.
			} elseif ( isset( $row[$setup_data] ) && $row[$setup_data] != '' ) { //Custom field text
				$retval = $row[$setup_data];
			}
		}

		return $retval;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _outputEFile( $format = null ) {
		$file_arr = [];
		$show_background = true;
		if ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' || $format == 'efile' ) {
			$show_background = false;
		}
		Debug::Text( 'Generating Form... Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $this->form_data['user'] ) && is_array( $this->form_data['user'] ) ) {
			$this->sortFormData(); //Make sure forms are sorted.

			Debug::Text( '  Total Users: '. count( $this->form_data['user'] ), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $this->form_data['user'] as $legal_entity_id => $company_deduction_rows ) {
				if ( isset( $this->form_data['legal_entity'][$legal_entity_id] ) == false ) {
					Debug::Text( 'Missing Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue; //Skips the entire legal entity and all users assigned to it.
				}

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id] ) == false ) {
					Debug::Text( 'Missing Remittance Agencies for Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue; //Skips the entire legal entity and all users assigned to it.
				}

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id]['00'] ) == false ) {
					Debug::Text( 'Missing Federal Remittance Agency for Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue; //Skips the entire legal entity and all users assigned to it.
				}


				//Need to take each CompanyDeduction layer of the array and flatten it to each user so we can access the data combined.
				$user_rows = $this->_flattenFormDataByCompanyDeduction( $company_deduction_rows );

				$x = 0; //Progress bar only.
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $user_rows ), null, TTi18n::getText( 'Generating Forms...' ) );

				$legal_entity_obj = $this->form_data['legal_entity'][$legal_entity_id];

				if ( is_object( $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']] ) ) {
					$contact_user_obj = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getContactUserObject();
				}
				if ( !isset( $contact_user_obj ) || !is_object( $contact_user_obj ) ) {
					$contact_user_obj = $this->getUserObject();
				}

				$state_ui = $this->getUSStateUIObject();  /** @var GovernmentForms_US_StateUI $state_ui */

				$state_ui->setDebug( false );
				//if ( $format == 'efile' ) {
				//	$state_ui->setDebug(TRUE);
				//}
				$state_ui->setShowBackground( $show_background );
				//$state_ui->setType( $form_type );
				$state_ui->month_of_year = TTDate::getMonth( $filter_data['end_date'] );
				$state_ui->quarter_of_year = TTDate::getYearQuarter( $filter_data['end_date'] );
				$state_ui->year = TTDate::getYear( $filter_data['start_date'] );
				//$state_ui->kind_of_employer = ( isset( $setup_data['kind_of_employer'] ) && $setup_data['kind_of_employer'] != '' ) ? Misc::trimSortPrefix( $setup_data['kind_of_employer'] ) : 'N';

				$state_ui->name = $legal_entity_obj->getLegalName();
				$state_ui->trade_name = $legal_entity_obj->getTradeName();
				$state_ui->company_address1 = $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2();
				$state_ui->company_city = $legal_entity_obj->getCity();
				$state_ui->company_state = $legal_entity_obj->getProvince();
				$state_ui->company_zip_code = $legal_entity_obj->getPostalCode();

				$state_ui->ein = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getPrimaryIdentification(); //Always use EIN from Federal Agency.
				//$state_ui->efile_user_id = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getTertiaryIdentification(); //Always use SSA eFile User ID

				//Only use the state specific format if its the only agency that is being returned (ie: they are filtering to a specific agency).
				// $setup_data['efile_state'] is set from PayrollRemittanceAgencyEvent->getReport().
				if ( isset( $setup_data['efile_state'] ) && $setup_data['efile_state'] != '' ) {
					$state_ui->efile_state = strtoupper( $setup_data['efile_state'] );
					Debug::Text( '    Using State eFile Format: ' . $state_ui->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Text( '    No state specified, unable to determine format...', __FILE__, __LINE__, __METHOD__, 10 );
				}


				//Force state for testing.
				//$state_ui->efile_state = 'LA';

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id][$state_ui->efile_state] ) ) {
					$remittance_agency_obj = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$state_ui->efile_state]];
					$state_ui->state_primary_id = $remittance_agency_obj->getPrimaryIdentification();
					$state_ui->state_secondary_id = $remittance_agency_obj->getSecondaryIdentification();
					$state_ui->state_tertiary_id = $remittance_agency_obj->getTertiaryIdentification();
					$state_ui->efile_agency_id = $remittance_agency_obj->getAgency();

					$state_ui->tax_rate = $this->form_data['company_deduction'][$legal_entity_id]['ui_tax_rate'] ?? 0;

					//Get Agency Event object so we can extract additional information from it to pass along to the W2 form.
					$praelf = TTnew('PayrollRemittanceAgencyEventListFactory'); /** @var PayrollRemittanceAgencyEventListFactory $praelf */
					$praelf->getByRemittanceAgencyIdAndTypeId( $remittance_agency_obj->getId(), 'FW2' );
					Debug::Text( '    State Agency Events: ' . $praelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $praelf->getRecordCount() > 0 ) {
						foreach( $praelf as $prea_obj ) {
							$state_ui->state_deposit_frequency = $prea_obj->getFrequency();
						}
					}
					unset( $remittance_agency_obj, $praelf, $prea_obj );
				} else if ( isset( $this->form_data['remittance_agency'][$legal_entity_id]['00'] ) ) {
					//$state_ui->efile_user_id = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getTertiaryIdentification();
					$state_ui->efile_agency_id = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getAgency();
				} else {
					Debug::Text( '    WARNING: Unable to determine remittance agency to obtain efile_user_id from...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				//Add state specific data to at the employer level
				if ( isset( $state_ui->efile_state ) && $state_ui->efile_state != '' ) {
					switch ( $state_ui->efile_state ) {
						case 'LA':
							$state_ui->is_multiple_county_industry = ( ( in_array( $setup_data[$state_ui->efile_state]['is_multiple_county_industry'] ?? null, [ 'Y', 'Yes', '1', 1 ], true ) ) ? true : false );
							$state_ui->is_multiple_worksite_location = ( ( in_array( $setup_data[$state_ui->efile_state]['is_multiple_worksite_location'] ?? null, [ 'Y', 'Yes', '1', 1 ], true ) ) ? true : false );
							$state_ui->is_multiple_worksite_indicator = ( ( in_array( $setup_data[$state_ui->efile_state]['is_multiple_worksite_indicator'] ?? null, [ 'Y', 'Yes', '1', 1 ], true ) ) ? true : false );
							break;
					}
				}

				$state_ui->contact_name = $contact_user_obj->getFullName();
				$state_ui->contact_phone = $contact_user_obj->getWorkPhone();
				$state_ui->contact_phone_ext = $contact_user_obj->getWorkPhoneExt();
				$state_ui->contact_email = ( $contact_user_obj->getWorkEmail() != '' ) ? $contact_user_obj->getWorkEmail() : ( ( $contact_user_obj->getHomeEmail() != '' ) ? $contact_user_obj->getHomeEmail() : null );

				if ( isset( $this->form_data ) && count( $this->form_data ) > 0 ) {
					$i = 0;
					foreach ( $user_rows as $user_id => $row ) {
						if ( !isset( $user_id ) || TTUUID::isUUID( $user_id ) == false ) {
							Debug::Text( 'User ID not set!', __FILE__, __LINE__, __METHOD__, 10 );
							continue;
						}

						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getById( TTUUID::castUUID( $user_id ) );
						if ( $ulf->getRecordCount() == 1 ) {
							$user_obj = $ulf->getCurrent();

							$ee_data = [
									'id'                  => (string)TTUUID::convertStringToUUID( md5( $user_id . $state_ui->year . microtime( true ) ) ), //Should be unique for every run so we can differentiate between runs.
									'user_id'             => (string)$user_id, //Helps with handling W2C forms.
									'control_number'      => ( $i + 1 ),
									'first_name'          => $user_obj->getFirstName(),
									'middle_name'         => $user_obj->getMiddleName(),
									'last_name'           => $user_obj->getLastName(),
									'address1'            => $user_obj->getAddress1(),
									'address2'            => $user_obj->getAddress2(),
									'city'                => $user_obj->getCity(),
									'state'               => $user_obj->getProvince(),
									'employment_province' => $user_obj->getProvince(),
									'zip_code'            => $user_obj->getPostalCode(),
									'ssn'                 => $user_obj->getSIN(),
									'employee_number'     => $user_obj->getEmployeeNumber(),

									'hire_date'           => $user_obj->getHireDate(),
									'termination_date'    => $user_obj->getTerminationDate(),

									'user_wage_wage'         => $row['user_wage_wage'] ?? null,
									'user_wage_hourly_rate'  => $row['user_wage_hourly_rate'] ?? null,
									'subject_rate'           => $row['subject_rate'] ?? null, //Hourly Rate

									'pay_period_taxable_wages_weeks' => $row['pay_period_taxable_wages_weeks'] ?? null,
									'pay_period_tax_withheld_weeks'  => $row['pay_period_tax_withheld_weeks'] ?? null,
									'pay_period_weeks'               => $row['pay_period_weeks'] ?? null,

									'paid_12th_day_month1' => min( (int)floor( $row['paid_12th_day_month1'] ?? null ), 1), //Should never be higher than 1 for an individual employee. Could occur if they have multiple payroll runs in the same pay period.
									'paid_12th_day_month2' => min( (int)floor( $row['paid_12th_day_month2'] ?? null ), 1), //Should never be higher than 1 for an individual employee. Could occur if they have multiple payroll runs in the same pay period.
									'paid_12th_day_month3' => min( (int)floor( $row['paid_12th_day_month3'] ?? null ), 1), //Should never be higher than 1 for an individual employee. Could occur if they have multiple payroll runs in the same pay period.
							];

							//210 = State Unemployment
							if ( isset( $row['210_'. $state_ui->efile_state .'_subject_wages'] ) ) {
								$ee_data = array_merge( $ee_data, [
										'subject_units' => $row['210_' . $state_ui->efile_state . '_subject_units'],
										'subject_wages' => $row['210_' . $state_ui->efile_state . '_subject_wages'],
										'taxable_wages' => $row['210_' . $state_ui->efile_state . '_taxable_wages'],
										'excess_wages'  => $row['210_' . $state_ui->efile_state . '_excess_wages'],
										'tax_withheld'  => $row['210_' . $state_ui->efile_state . '_tax_withheld'],
								] );
							} else if ( isset( $row['15_subject_wages'] ) ) {
								//Still allow advanced percent calculations to carry through if no specific State UI CompanyDeduction is assigned for eFiling purposes.
								// Some governement/crown corporations need to report all earnings, without any wage base or percent.
								// See Ticket #862532.
								$ee_data = array_merge( $ee_data, [
									'subject_units'       => $row['15_subject_units'],
									'subject_wages' 	  => $row['15_subject_wages'],
									'taxable_wages' 	  => $row['15_taxable_wages'],
									'excess_wages'  	  => $row['15_excess_wages'],
									'tax_withheld'  	  => $row['15_tax_withheld'],
								] );
							} else {
								$ee_data = array_merge( $ee_data, [
										'subject_units'       => 0,
										'subject_wages' 	  => 0,
										'taxable_wages' 	  => 0,
										'excess_wages'  	  => 0,
										'tax_withheld'  	  => 0,
								] );
							}

							//Add state specific data to each employee record.
							switch ( $state_ui->efile_state ) {
								case 'IA':
									$ee_data['reporting_unit_number'] = $this->getFormSetupField( $setup_data[$state_ui->efile_state]['reporting_unit_number'] ?? null , $row, '1' );
									break;
								case 'IN':
									$ee_data['occupation_classification_code'] = $this->getFormSetupField( $setup_data[$state_ui->efile_state]['occupation_classification_code'] ?? null , $row, '000000' );
									$ee_data['designation'] = $this->getFormSetupField( $setup_data[$state_ui->efile_state]['designation'] ?? null , $row, 'FT' );
									break;
								case 'LA':
									$ee_data['occupation_classification_code'] = $this->getFormSetupField( $setup_data[$state_ui->efile_state]['occupation_classification_code'] ?? null , $row, '' ); //Since this can be alpha numeric or 6 digit code, default to blank string.
									break;
								case 'MI':
									$ee_data['multi_unit_number'] = $this->getFormSetupField( $setup_data[$state_ui->efile_state]['multi_unit_number'] ?? null , $row, '000' );
									break;
								case 'MN':
									$ee_data['reporting_unit_number'] = $this->getFormSetupField( $setup_data[$state_ui->efile_state]['reporting_unit_number'] ?? null , $row, '0' );
									break;
								case 'TX':
									$ee_data['county_code'] = $this->getFormSetupField( $setup_data[$state_ui->efile_state]['county_code'] ?? null , $row, '000' );
									break;
								case 'FL':
									//Out-of-State wages.
									break;
								case 'CA':
									//Need to tax PIT (state tax) wages and witheld amounts for California separately.
									if ( isset( $row['200_'. $state_ui->efile_state .'_subject_wages'] ) ) {
										$ee_data = array_merge( $ee_data, [
												'state_income_tax_subject_units' => $row['200_' . $state_ui->efile_state . '_subject_units'],
												'state_income_tax_subject_wages' => $row['200_' . $state_ui->efile_state . '_subject_wages'],
												'state_income_tax_taxable_wages' => $row['200_' . $state_ui->efile_state . '_taxable_wages'],
												'state_income_tax_excess_wages'  => $row['200_' . $state_ui->efile_state . '_excess_wages'],
												'state_income_tax_tax_withheld'  => $row['200_' . $state_ui->efile_state . '_tax_withheld'],

												'branch_code' => $this->getFormSetupField( $setup_data[$state_ui->efile_state]['branch_code'] ?? null, $row, '000' ),
												'wage_plan_code' => $this->getFormSetupField( $setup_data[$state_ui->efile_state]['wage_plan_code'] ?? null, $row, 'S' ),
										] );
									}
									break;
								case 'CO':
									$ee_data['branch_code'] = $this->getFormSetupField( $setup_data[$state_ui->efile_state]['branch_code'] ?? null, $row, '000' );
									$ee_data['is_seasonal'] = ( ( in_array( $this->getFormSetupField( $setup_data[$state_ui->efile_state]['is_seasonal'] ?? null, $row, false ), [ 'S', 'Y', 'Yes', '1', 1 ], true ) ) ? true : false );
									break;
							}

							$state_ui->addRecord( $ee_data );
							unset( $ee_data );

							$i++;
						}

						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $x );
						$x++;
					}
				}


				//
				//Handle state specific data on a per report (company wide) basis.
				//
				switch ( $state_ui->efile_state ) {
					case 'TX':
						//Get the county code with the most employees.
						$popular_county_codes = array_count_values( Misc::arrayColumn( $state_ui->getRecords(), 'county_code' ) );
						if ( is_array( $popular_county_codes ) ) {
							arsort( $popular_county_codes );
							$state_ui->county_code = key( $popular_county_codes ); //First record from array as it is the most common.
							Debug::Text( '  County Code with most employees: ' . $state_ui->county_code, __FILE__, __LINE__, __METHOD__, 10 );
						}
						unset( $popular_county_codes );

						break;
				}

				$this->getFormObject()->addForm( $state_ui );
				$output = $this->getFormObject()->output( 'EFILE' );

				$base_file_name = strtolower( $state_ui->efile_state ) . '_ui_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( ( ( $this->form_data['legal_entity'][$legal_entity_id]->getShortName() != '' ) ? $this->form_data['legal_entity'][$legal_entity_id]->getShortName() : $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) ); //Texas Quick File program requires the file name to be less than 50 chars.
				$mime_type = 'applications/octet-stream'; //Force file to download.
				switch ( $state_ui->efile_state ) {
					case 'CA': //Requires each file to be zipped.
						$file_name = $base_file_name . '.txt';
						$file_arr[] = Misc::zip( [ [ 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output ] ], $base_file_name .'.zip', false ); //ZIP each individual file for CA.
						break;
					default:
						$file_name = $base_file_name . '.txt';
						$file_arr[] = [ 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output ];
						break;
				}

				$this->clearFormObject();
			}
		}

		if ( isset( $file_name ) && $file_name != '' ) {
			$zip_filename = explode( '.', $file_name );
			if ( isset( $zip_filename[( count( $zip_filename ) - 1 )] ) ) {
				$zip_filename = str_replace( '.', '', str_replace( $zip_filename[( count( $zip_filename ) - 1 )], '', $file_name ) ) . '.zip';
			} else {
				$zip_filename = str_replace( '.', '', $file_name ) . '.zip';
			}

			return Misc::zip( $file_arr, $zip_filename, true );
		}

		Debug::Text( ' Returning FALSE!', __FILE__, __LINE__, __METHOD__, 10 );
		return false;

	}

	/**
	 * Short circuit this function, as no postprocessing is required for exporting the data.
	 * @param null $format
	 * @return bool
	 */
	function _postProcess( $format = null ) {
		if ( ( $format == 'efile' || $format == 'payment_services' ) ) {
			Debug::Text( 'Skipping postProcess! Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else {
			return parent::_postProcess( $format );
		}
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _output( $format = null ) {
		if ( ( $format == 'efile' || $format == 'payment_services' ) ) {
			return $this->_outputEFile( $format );
		} else {
			return parent::_output( $format );
		}
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
		////Make sure we have the columns we need.
		//$report_data['config'] = $this->getConfig();
		//$report_data['config']['columns'] = [ 'payroll_remittance_agency_name', 'transaction-date_stamp', 'subject_wages', 'taxable_wages', 'tax_withheld', 'total_user' ];
		//$report_data['config']['group'] = [ 'payroll_remittance_agency_name', 'transaction-date_stamp' ];
		//$this->setConfig( (array)$report_data['config'] );
		//
		//$output_data = $this->getOutput( 'payment_services' );
		//Debug::Arr( $output_data, 'Raw Report data!', __FILE__, __LINE__, __METHOD__, 10 );
		//
		//if ( $this->hasData() ) {
		//	//Get last Grand Total row.
		//	$last_row = end( $output_data );
		//
		//	if ( isset( $last_row['_total'] ) ) {
		//		$batch_id = date( 'M d', $prae_obj->getEndDate() );
		//
		//		$amount_due = ( isset( $last_row['tax_withheld'] ) ) ? $last_row['tax_withheld'] : null;
		//
		//		$retarr = [
		//				'object'               => __CLASS__,
		//				'user_success_message' => TTi18n::gettext( 'Payment submitted successfully for $%1', [ Misc::MoneyRound( $amount_due ) ] ),
		//				'agency_report_data'   => [
		//						'total_employees' => ( isset( $last_row['total_user'] ) ) ? (int)$last_row['total_user'] : null,
		//						'subject_wages'   => ( isset( $last_row['subject_wages'] ) ) ? $last_row['subject_wages'] : null,
		//						'taxable_wages'   => ( isset( $last_row['taxable_wages'] ) ) ? $last_row['taxable_wages'] : null,
		//						'amount_withheld' => ( isset( $last_row['tax_withheld'] ) ) ? $last_row['tax_withheld'] : null,
		//						'amount_due'      => $amount_due,
		//
		//						'remote_batch_id' => $batch_id,
		//
		//						//Generate a consistent remote_id based on the exact time period, the remittance agency event, and batch ID.
		//						//This helps to prevent duplicate records from be created, as well as work across separate or split up batches that may be processed.
		//						//  This needs to take into account different start/end date periods, so we don't try to overwrite records from last year.
		//						'remote_id'       => TTUUID::convertStringToUUID( md5( $prae_obj->getId() . $prae_obj->getStartDate() . $prae_obj->getEndDate() ) ),
		//				],
		//		];
		//
		//		return $retarr;
		//	}
		//}

		Debug::Text( 'No report data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}
}

?>
