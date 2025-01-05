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
class PayStubSummaryReport extends Report {

	/**
	 * PayStubSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Pay Stub Summary Report' );
		$this->file_name = 'paystub_summary_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_pay_stub_summary', $user_id, $company_id ) ) {
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
	 * @return array|bool|null
	 */
	protected function _getOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'output_format':
				$retval = array_merge( parent::getOptions( 'default_output_format' ),
									   [
											   '-1100-pdf_employee_pay_stub' => TTi18n::gettext( 'Employee Pay Stub' ),
											   '-1110-pdf_employer_pay_stub' => TTi18n::gettext( 'Employer Pay Stub' ),
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
					'-2080-currency_id'           => TTi18n::gettext( 'Currency' ),
					'-2100-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),

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
						TTDate::getReportDateOptions( 'transaction', TTi18n::getText( 'Transaction Date' ), 27, true )
				);
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
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'PayStubSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'PayStubSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'PayStubSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'PayStubSummaryReport', 'custom_column' );
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
					'-1077-home_phone'          => TTi18n::gettext( 'Home Phone' ),
					'-1078-mobile_phone'        => TTi18n::gettext( 'Mobile Phone' ),
					'-1079-home_email'          => TTi18n::gettext( 'Home Email' ),
					'-1080-work_email'     		=> TTi18n::gettext( 'Work Email' ),
					'-1085-user_group'          => TTi18n::gettext( 'Group' ),
					'-1090-default_branch'      => TTi18n::gettext( 'Default Branch' ),
					'-1100-default_department'  => TTi18n::gettext( 'Default Department' ),
					'-1110-currency'            => TTi18n::gettext( 'Currency' ),
					'-1131-current_currency'    => TTi18n::gettext( 'Current Currency' ),
					'-1200-permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1210-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1220-policy_group'        => TTi18n::gettext( 'Policy Group' ),
					//Handled in date_columns above.
					//'-1250-pay_period' => TTi18n::gettext('Pay Period'),

					'-1280-sin'  => TTi18n::gettext( 'SIN/SSN' ),
					'-1290-note' => TTi18n::gettext( 'Note' ),
					'-1295-tag'  => TTi18n::gettext( 'Tags' ),

					'-2000-legal_name' => TTi18n::gettext( 'Legal Entity' ),

					'-2800-pay_stub_status' => TTi18n::gettext( 'Pay Stub Status' ),
					'-2810-pay_stub_type'   => TTi18n::gettext( 'Pay Stub Type' ),
					'-2820-pay_stub_run_id' => TTi18n::gettext( 'Payroll Run' ),
				];

				$retval = array_merge( $retval, (array)$this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users'], ['users'] ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used

					//Take into account wage groups. However hourly_rates for the same hour type, so we need to figure out an average hourly rate for each column?
					//'-2010-hourly_rate' => TTi18n::gettext('Hourly Rate'),
					'-2900-total_pay_stub' => TTi18n::gettext( 'Total Pay Stubs' ), //Group counter...

				];

				$retval = array_merge( $retval, $this->getOptions( 'pay_stub_account_amount_columns', [ 'include_ytd_amount' => true ] ) );
				$retval = array_merge( $retval, $this->getDynamicCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users'], ['users'] ) );
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
						if ( substr( $column, 0, 2 ) == 'PU' ) {
							$retval[$column] = 'numeric';
						} else if ( strpos( $column, '_wage' ) !== false || strpos( $column, '_hourly_rate' ) !== false
								|| substr( $column, 0, 2 ) == 'PA' || substr( $column, 0, 2 ) == 'PY' || substr( $column, 0, 2 ) == 'PR' ) {
							$retval[$column] = 'currency';
						} else if ( strpos( $column, '_time' ) || strpos( $column, '_policy' ) ) {
							$retval[$column] = 'time_unit';
						} else if ( strpos( $column, 'total_pay_stub' ) !== false ) {
							$retval[$column] = 'numeric';
						}
					}
				}
				break;
			case 'grand_total_metadata':
				//Make sure all jobs are sum'd
				$retval['aggregate'] = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, '_hourly_rate' ) !== false || strpos( $column, '_rate' ) !== false || substr( $column, 0, 2 ) == 'PR' ) {
									$retval['aggregate'][$column] = 'avg';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'sub_total_by_metadata':
				//Make sure task estimates are sum'd.
				$retval['aggregate'] = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								if ( strpos( $column, '_hourly_rate' ) !== false || strpos( $column, '_rate' ) !== false || substr( $column, 0, 2 ) == 'PR' ) {
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
								if ( strpos( $column, '_hourly_rate' ) !== false || strpos( $column, '_rate' ) !== false || substr( $column, 0, 2 ) == 'PR' ) {
									$retval['aggregate'][$column] = 'avg';
								} else if ( substr( $column, 0, 2 ) == 'PY' || substr( $column, 0, 3 ) == 'PUY' ) { //YTD Amounts must use "last()" when grouping rather than sum() or max(). Because YTD amount could actually go up or down (especially for Vacation Accruals). This is where order really matters too then.
									$retval['aggregate'][$column] = 'last';
								} else {
									$retval['aggregate'][$column] = 'sum';
								}
						}
					}
				}
				break;
			case 'templates':
				$retval = [
						'-1000-open_pay_stubs' => TTi18n::gettext( 'Pay Stubs Pending Payment' ),

						'-1010-by_employee+totals'                                                           => TTi18n::gettext( 'Totals by Employee' ),
						'-1020-by_employee+earnings'                                                         => TTi18n::gettext( 'Earnings by Employee' ),
						'-1030-by_employee+employee_deductions'                                              => TTi18n::gettext( 'Deductions by Employee' ),
						'-1040-by_employee+employer_deductions'                                              => TTi18n::gettext( 'Employer Contributions by Employee' ),
						'-1050-by_employee+accruals'                                                         => TTi18n::gettext( 'Accruals by Employee' ),
						'-1055-by_employee+accrual_balances'                                                 => TTi18n::gettext( 'Accrual Balances by Employee' ),
						'-1060-by_employee+totals+earnings+employee_deductions+employer_deductions+accruals' => TTi18n::gettext( 'All Accounts by Employee' ),

						'-1110-by_title+totals'                => TTi18n::gettext( 'Totals by Title' ),
						'-1120-by_group+totals'                => TTi18n::gettext( 'Totals by Group' ),
						'-1130-by_branch+totals'               => TTi18n::gettext( 'Totals by Branch' ),
						'-1140-by_department+totals'           => TTi18n::gettext( 'Totals by Department' ),
						'-1150-by_branch_by_department+totals' => TTi18n::gettext( 'Totals by Branch/Department' ),
						'-1160-by_pay_period+totals'           => TTi18n::gettext( 'Totals by Pay Period' ),

						'-1210-by_pay_period_by_employee+totals'             => TTi18n::gettext( 'Totals by Pay Period/Employee' ),
						'-1220-by_employee_by_pay_period+totals'             => TTi18n::gettext( 'Totals by Employee/Pay Period' ),
						'-1230-by_branch_by_pay_period+totals'               => TTi18n::gettext( 'Totals by Branch/Pay Period' ),
						'-1240-by_department_by_pay_period+totals'           => TTi18n::gettext( 'Totals by Department/Pay Period' ),
						'-1250-by_branch_by_department_by_pay_period+totals' => TTi18n::gettext( 'Totals by Branch/Department/Pay Period' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
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

					switch ( $template ) {
						case 'open_pay_stubs':
							$retval['-1010-time_period']['time_period'] = 'last_pay_period';
							$retval['-6000-pay_stub_status_id'] = 25;

							$retval['columns'][] = 'transaction-date_stamp';
							$retval['columns'][] = 'pay_stub_type';
							$retval['columns'][] = 'pay_stub_run_id';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['sort'][] = [ 'transaction-date_stamp' => 'asc' ];
							$retval['sort'][] = [ 'pay_stub_type' => 'asc' ];
							$retval['sort'][] = [ 'pay_stub_run_id' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];

							//Total Columns.
							$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
							$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 40 ] );
							if ( $psealf->getRecordCount() > 0 ) {
								foreach ( $psealf as $psea_obj ) {
									$retval['columns'][] = 'PA:' . $psea_obj->getID();
								}
							}
							break;
						case 'by_employee+accrual_balances':
							$retval['-1010-time_period']['time_period'] = 'all_years';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							$retval['sort'][] = [ 'transaction-date_stamp' => 'asc' ];

							$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
							$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 50 ] );
							if ( $psealf->getRecordCount() > 0 ) {
								foreach ( $psealf as $psea_obj ) {
									$retval['columns'][] = 'PY:' . $psea_obj->getID();
								}
							}
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
										case 'earnings':
											$retval['columns'][] = 'PA:' . $default_linked_columns[0]; //Total Gross
											$retval['columns'][] = 'PA:' . $default_linked_columns[1]; //Net Pay

											$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 10 ] );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach ( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA:' . $psea_obj->getID();
												}
											}
											break;
										case 'employee_deductions':
											$retval['columns'][] = 'PA:' . $default_linked_columns[2]; //Employee Deductions

											$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 20 ] );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach ( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA:' . $psea_obj->getID();
												}
											}
											break;
										case 'employer_deductions':
											$retval['columns'][] = 'PA:' . $default_linked_columns[3]; //Employor Deductions

											$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 30 ] );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach ( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA:' . $psea_obj->getID();
												}
											}
											break;
										case 'totals':
											$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 40 ] );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach ( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA:' . $psea_obj->getID();
												}
											}
											break;
										case 'accruals':
											$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
											$psealf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), 10, [ 50 ] );
											if ( $psealf->getRecordCount() > 0 ) {
												foreach ( $psealf as $psea_obj ) {
													$retval['columns'][] = 'PA:' . $psea_obj->getID();
												}
											}
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
										case 'by_title':
											$retval['columns'][] = 'title';

											$retval['group'][] = 'title';

											$retval['sort'][] = [ 'title' => 'asc' ];
											break;
										case 'by_group':
											$retval['columns'][] = 'user_group';

											$retval['group'][] = 'user_group';

											$retval['sort'][] = [ 'user_group' => 'asc' ];
											break;
										case 'by_branch':
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'default_branch';

											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											break;
										case 'by_department':
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_department';

											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_branch_by_department':
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_pay_period':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'transaction-pay_period';

											$retval['group'][] = 'transaction-pay_period';

											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
											break;
										case 'by_pay_period_by_employee':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'transaction-pay_period';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';

											$retval['group'][] = 'transaction-pay_period';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';

											$retval['sub_total'][] = 'transaction-pay_period';

											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											break;
										case 'by_pay_period_by_branch':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'transaction-pay_period';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'transaction-pay_period';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'transaction-pay_period';

											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											break;
										case 'by_pay_period_by_department':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'transaction-pay_period';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'transaction-pay_period';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'transaction-pay_period';

											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_pay_period_by_branch_by_department':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'transaction-pay_period';
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'transaction-pay_period';
											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'transaction-pay_period';
											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_employee_by_pay_period':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'full_name';
											$retval['columns'][] = 'transaction-pay_period';

											$retval['group'][] = 'full_name';
											$retval['group'][] = 'transaction-pay_period';

											$retval['sub_total'][] = 'full_name';

											$retval['sort'][] = [ 'full_name' => 'asc' ];
											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
											break;
										case 'by_branch_by_pay_period':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'transaction-pay_period';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'transaction-pay_period';

											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
											break;
										case 'by_department_by_pay_period':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'default_department';
											$retval['columns'][] = 'transaction-pay_period';

											$retval['group'][] = 'default_department';
											$retval['group'][] = 'transaction-pay_period';

											$retval['sub_total'][] = 'default_department';

											$retval['sort'][] = [ 'default_department' => 'asc' ];
											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
											break;
										case 'by_branch_by_department_by_pay_period':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';
											$retval['columns'][] = 'transaction-pay_period';

											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';
											$retval['group'][] = 'transaction-pay_period';

											$retval['sub_total'][] = 'default_branch';
											$retval['sub_total'][] = 'default_department';

											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											$retval['sort'][] = [ 'transaction-pay_period' => 'asc' ];
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
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'pay_stub_entry' => [], 'user' => [] ];

		$filter_data = $this->getFilterConfig();

		$currency_convert_to_base = $this->getCurrencyConvertToBase();
		$base_currency_obj = $this->getBaseCurrencyObject();
		$this->handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data );
		$currency_options = $this->getOptions( 'currency' );

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		$psf = TTnew( 'PayStubFactory' ); /** @var PayStubFactory $psf */ //For getOptions() below.

		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$this->setMemoryPerRow( max( 2600, ( 16 * count( $this->getColumnDataConfig() ) ) ) ); //MAX: 5K for each row @ 295 columns = 17 bytes per column, per row. 2596bytes per row at 1 column.
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, $this->getMemoryBasedRowLimit() );
		Debug::Text( ' Total Rows: ' . $pself->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pself->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		if ( $pself->getRecordCount() > 0 ) {
			if ( $this->isMemoryBasedRowLimitValid( $pself->getRecordCount() ) == false ) {
				$this->setMaximumRowsExceeded( true ); //Alert the user that the maximum number of rows was exceeded, but still generate the report with the data we have.
				if ( $this->handleMaximumRowsExceeded( $format ) == false ) {
					return false;
				}
			}

			foreach ( $pself as $key => $pse_obj ) {
				$user_id = $pse_obj->getColumn( 'user_id' );
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ); //Should match PayStubSummary, RemittanceSummary, TaxSummary, GeneralLedgerSummaryReport, etc... $date_stamp too.
				$run_id = $pse_obj->getColumn( 'pay_stub_run_id' );
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();
				$currency_rate = $pse_obj->getColumn( 'currency_rate' );
				$currency_id = $pse_obj->getColumn( 'currency_id' );

				if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id] = [
							'pay_period_start_date'       => strtotime( $pse_obj->getColumn( 'pay_period_start_date' ) ),
							'pay_period_end_date'         => strtotime( $pse_obj->getColumn( 'pay_period_end_date' ) ),
							'pay_period_transaction_date' => strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ),
							'pay_period'                  => strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ),

							'pay_stub_status' => Option::getByKey( $pse_obj->getColumn( 'pay_stub_status_id' ), $psf->getOptions( 'status' ) ),
							'pay_stub_type'   => Option::getByKey( $pse_obj->getColumn( 'pay_stub_type_id' ), $psf->getOptions( 'type' ) ),
							'pay_stub_start_date'       => strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ),
							'pay_stub_end_date'         => strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ),
							'pay_stub_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ) ), //Some transaction dates could be throughout the day for terminated employees being paid early, so always forward them to the middle of the day to keep group_by working correctly.
							'pay_stub_run_id'           => $run_id,
					];
				}
				$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['currency_rate'] = $currency_rate;

				$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['currency'] = $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['current_currency'] = Option::getByKey( $currency_id, $currency_options );

				if ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PA:' . $pay_stub_entry_name_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PA:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PA:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'amount' ) );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PA:' . $pay_stub_entry_name_id] = $pse_obj->getColumn( 'amount' );
				}

				if ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PR:' . $pay_stub_entry_name_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PR:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PR:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'rate' ) );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PR:' . $pay_stub_entry_name_id] = $pse_obj->getColumn( 'rate' );
				}

				if ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PU:' . $pay_stub_entry_name_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PU:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PU:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'units' ) );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PU:' . $pay_stub_entry_name_id] = $pse_obj->getColumn( 'units' );
				}

				if ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PUY:' . $pay_stub_entry_name_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PUY:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PUY:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'ytd_units' ) );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PUY:' . $pay_stub_entry_name_id] = $pse_obj->getColumn( 'ytd_units' );
				}

				if ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PY:' . $pay_stub_entry_name_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PY:' . $pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PY:' . $pay_stub_entry_name_id], $pse_obj->getColumn( 'ytd_amount' ) );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['PY:' . $pay_stub_entry_name_id] = $pse_obj->getColumn( 'ytd_amount' );
				}

				if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
				}

				if ( ( $key % 5000 ) == 0 && $this->isMemoryLimitValid() == false ) { //Check memory requirements while processing this data, as large reports could cause memory overflow within this function that would not be caught by memory checks wrapping this function.
					return false;
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$this->getColumnDataConfig(), [ 'hire_date' => true, 'termination_date' => true, 'birth_date' => true ] ) );
			$this->tmp_data['user'][$u_obj->getId()]['total_pay_stub'] = 1;
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
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

			foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				if ( isset( $this->tmp_data['user'][$user_id] ) ) {
					foreach ( $level_1 as $date_stamp => $level_2 ) {
						foreach ( $level_2 as $row ) {
							$date_columns = TTDate::getReportDates( 'transaction', $date_stamp, false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ] );

							if ( isset( $this->tmp_data['user'][$user_id]['hire_date'] ) ) {
								$hire_date_columns = TTDate::getReportDates( 'hire', TTDate::parseDateTime( $this->tmp_data['user'][$user_id]['hire_date'] ), false, $this->getUserObject(), null, $column_keys );
							} else {
								$hire_date_columns = [];
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

							$processed_data = [
								//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
								//'pay_stub' => array('sort' => $row['pay_stub_transaction_date'], 'display' => TTDate::getDate('DATE', $row['pay_stub_transaction_date'] ) ),
							];

							//Need to make sure PSEA IDs are strings not numeric otherwise array_merge will re-key them.
							$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $hire_date_columns, $termination_date_columns, $birth_date_columns, $processed_data );

							$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
							$key++;
						}
					}
				}
			}
			unset( $this->tmp_data, $row, $date_columns, $hire_date_columns, $termination_date_columns, $birth_date_columns, $processed_data, $level_1 );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @param $format
	 * @return bool
	 */
	function _outputPDFPayStub( $format ) {
		Debug::Text( ' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		$filter_data = $this->getFilterConfig();

		if ( !$this->getPermissionObject()->Check( 'pay_stub', 'enabled', $this->getUserObject()->getId(), $this->getUserObject()->getCompany() )
				|| !( $this->getPermissionObject()->Check( 'pay_stub', 'view', $this->getUserObject()->getId(), $this->getUserObject()->getCompany() ) || $this->getPermissionObject()->Check( 'pay_stub', 'view_own', $this->getUserObject()->getId(), $this->getUserObject()->getCompany() ) || $this->getPermissionObject()->Check( 'pay_stub', 'view_child', $this->getUserObject()->getId(), $this->getUserObject()->getCompany() ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}
		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'pay_stub', 'view', $this->getUserObject()->getId(), $this->getUserObject()->getCompany() );

		Debug::Arr( $filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10 );
		$pslf = TTnew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
		$this->setMemoryPerRow( ( 1024 * 50 ) ); //Each row (PDF pay stub) is about 50,000 bytes
		$pslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, $this->getMemoryBasedRowLimit() );
		Debug::Text( 'Record Count: ' . $pslf->getRecordCount() . ' Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pslf->getRecordCount() > 0 ) {
			if ( $this->isMemoryBasedRowLimitValid( $pslf->getRecordCount() ) == false ) {
				return false;
			}

			$this->getProgressBarObject()->setDefaultKey( $this->getAPIMessageID() );
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pslf->getRecordCount() );
			$pslf->setProgressBarObject( $this->getProgressBarObject() ); //Expose progress bar object to pay stub object.

			$filter_data['hide_employer_rows'] = true;
			if ( $format == 'pdf_employer_pay_stub' || $format == 'pdf_employer_pay_stub_print' ) {
				//Must be false, because if it isn't checked it won't be set.
				$filter_data['hide_employer_rows'] = false;
			}

			$this->form_data = range( 0, $pslf->getRecordCount() ); //Set this so hasData() thinks there is data to report.
			$output = $pslf->getPayStub( $pslf, (bool)$filter_data['hide_employer_rows'] );

			return $output;
		}

		Debug::Text( 'No data to return...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _output( $format = null ) {
		if ( $format == 'pdf_employee_pay_stub' || $format == 'pdf_employee_pay_stub_print'
				|| $format == 'pdf_employer_pay_stub' || $format == 'pdf_employer_pay_stub_print' ) {
			return $this->_outputPDFPayStub( $format );
		} else {
			return parent::_output( $format );
		}
	}
}

?>