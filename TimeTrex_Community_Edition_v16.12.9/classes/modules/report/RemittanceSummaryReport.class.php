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
class RemittanceSummaryReport extends Report {

	protected $user_ids = [];

	/**
	 * RemittanceSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Remittance Summary Report' );
		$this->file_name = 'remittance_summary';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_remittance_summary', $user_id, $company_id ) ) {
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
					'-1000-template'              => TTi18n::gettext( 'Template' ),
					'-1010-time_period'           => TTi18n::gettext( 'Time Period' ),
					'-2000-legal_entity_id'       => TTi18n::gettext( 'Legal Entity' ),
					'-2010-user_status_id'        => TTi18n::gettext( 'Employee Status' ),
					'-2020-user_group_id'         => TTi18n::gettext( 'Employee Group' ),
					'-2025-policy_group_id'       => TTi18n::gettext( 'Policy Group' ),
					'-2030-user_title_id'         => TTi18n::gettext( 'Employee Title' ),
					'-2040-include_user_id'       => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'       => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-default_branch_id'     => TTi18n::gettext( 'Default Branch' ),
					'-2070-default_department_id' => TTi18n::gettext( 'Default Department' ),
					'-2100-custom_filter' => TTi18n::gettext( 'Custom Filter' ),

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
				$retval = TTDate::getReportDateOptions( null, TTi18n::getText( 'Transaction Date' ), 13, true );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'RemittanceSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'RemittanceSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'RemittanceSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'RemittanceSummaryReport', 'custom_column' );
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
					'-1035-sin'                => TTi18n::gettext( 'SIN/SSN' ),
					'-1040-status'             => TTi18n::gettext( 'Status' ),
					'-1050-title'              => TTi18n::gettext( 'Title' ),
					'-1060-province'           => TTi18n::gettext( 'Province/State' ),
					'-1070-country'            => TTi18n::gettext( 'Country' ),
					'-1080-group'              => TTi18n::gettext( 'Group' ),
					'-1090-default_branch'     => TTi18n::gettext( 'Default Branch' ),
					'-1100-default_department' => TTi18n::gettext( 'Default Department' ),
					'-1110-currency'           => TTi18n::gettext( 'Currency' ),
					//'-1111-current_currency' => TTi18n::gettext('Current Currency'),

					//'-1110-verified_time_sheet' => TTi18n::gettext('Verified TimeSheet'),
					//'-1120-pending_request' => TTi18n::gettext('Pending Requests'),

					//Handled in date_columns above.
					//'-1450-pay_period' => TTi18n::gettext('Pay Period'),

					'-1400-permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1410-pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1420-policy_group'        => TTi18n::gettext( 'Policy Group' ),

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
					'-2060-total' => TTi18n::gettext( 'Total Deductions' ),

					'-2070-ei_total_earnings'      => TTi18n::gettext( 'EI Insurable Earnings' ),
					'-2071-ei_total'               => TTi18n::gettext( 'EI' ),
					'-2072-expected_ei_total'      => TTi18n::gettext( 'Expected EI' ),
					'-2073-expected_ei_total_diff' => TTi18n::gettext( 'Expected EI Difference' ),

					'-2080-cpp_total_earnings'      => TTi18n::gettext( 'CPP Pensionable Earnings' ),
					'-2081-cpp_total'               => TTi18n::gettext( 'CPP' ),
					'-2082-expected_cpp_total'      => TTi18n::gettext( 'Expected CPP' ),
					'-2083-expected_cpp_total_diff' => TTi18n::gettext( 'Expected CPP Difference' ),

					'-2084-cpp2_total'               => TTi18n::gettext( 'CPP2' ),
					'-2085-expected_cpp2_total'      => TTi18n::gettext( 'Expected CPP2' ),
					'-2086-expected_cpp2_total_diff' => TTi18n::gettext( 'Expected CPP2 Difference' ),

					'-2090-cpp_combined_total'               => TTi18n::gettext( 'Total Combined CPP' ),
					'-2091-expected_cpp_combined_total'      => TTi18n::gettext( 'Expected Combined CPP' ),
					'-2092-expected_cpp_combined_total_diff' => TTi18n::gettext( 'Expected Combined CPP Difference' ),

					'-2090-tax_total'           => TTi18n::gettext( 'Tax' ),
					'-2100-gross_payroll'       => TTi18n::gettext( 'Gross Pay' ),
					'-2200-expected_total_diff' => TTi18n::gettext( 'Expected Difference' ),
				];
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
						$retval[$column] = 'currency';
					}
				}
				break;
			case 'aggregates':
				$retval = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							default:
								$retval[$column] = 'sum';
						}
					}
				}

				break;
			case 'schedule_deposit':
				$retval = [
						10 => TTi18n::gettext( 'Monthly' ),
						20 => TTi18n::gettext( 'Semi-Weekly' ),
				];
				break;
			case 'templates':
				$retval = [
						'-1005-by_pay_period_by_employee' => TTi18n::gettext( 'by Pay Period/Employee' ),

						'-1010-by_employee'             => TTi18n::gettext( 'by Employee' ),
						'-1020-by_pay_period'           => TTi18n::gettext( 'by Pay Period' ),
						'-1030-by_month'                => TTi18n::gettext( 'by Month' ),
						'-1040-by_branch'               => TTi18n::gettext( 'by Branch' ),
						'-1050-by_department'           => TTi18n::gettext( 'by Department' ),
						'-1060-by_branch_by_department' => TTi18n::gettext( 'by Branch/Department' ),

						'-1110-by_month_by_employee'             => TTi18n::gettext( 'by Month/Employee' ),
						'-1120-by_month_by_branch'               => TTi18n::gettext( 'by Month/Branch' ),
						'-1130-by_month_by_department'           => TTi18n::gettext( 'by Month/Department' ),
						'-1140-by_month_by_branch_by_department' => TTi18n::gettext( 'by Month/Branch/Department' ),

						'-2000-pier' => TTi18n::gettext( 'Pensionable & Insurable Earnings Review (PIER)' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );

				$retval['columns'] = [];

				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						case 'default':
							//Proper settings to generate the form.
							$retval['-1010-time_period']['time_period'] = 'last_month';

							$retval['columns'] = $this->getOptions( 'columns' );

							$retval['group'][] = 'date_month';

							$retval['sort'][] = [ 'date_month' => 'asc' ];

							$retval['other']['grand_total'] = true;

							break;
						default:
							Debug::Text( ' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );
							$retval['columns'] = [];
							$retval['-1010-time_period']['time_period'] = 'last_month';

							//Parse template name, and use the keywords separated by '+' to determine settings.
							$template_keywords = explode( '+', $template );
							if ( is_array( $template_keywords ) ) {
								foreach ( $template_keywords as $template_keyword ) {
									Debug::Text( ' Keyword: ' . $template_keyword, __FILE__, __LINE__, __METHOD__, 10 );

									switch ( $template_keyword ) {
										//Columns

										//Filter
										//Group By
										//SubTotal
										//Sort
										case 'pier':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';
											$retval['columns'][] = 'ei_total_earnings';
											$retval['columns'][] = 'ei_total';
											$retval['columns'][] = 'expected_ei_total';
											$retval['columns'][] = 'expected_ei_total_diff';
											$retval['columns'][] = 'cpp_total_earnings';
											$retval['columns'][] = 'cpp_total';
											$retval['columns'][] = 'expected_cpp_total';
											$retval['columns'][] = 'expected_cpp_total_diff';
											$retval['columns'][] = 'cpp2_total';
											$retval['columns'][] = 'expected_cpp2_total';
											$retval['columns'][] = 'expected_cpp2_total_diff';
											$retval['columns'][] = 'cpp_combined_total';

											//$retval['columns'][] = 'expected_total_diff';
											$retval['columns'][] = 'gross_payroll';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';

											$retval['sort'][] = [ 'expected_total_diff' => 'desc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											$retval['sort'][] = [ 'middle_name' => 'asc' ];
											break;
										case 'by_pay_period':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_stamp';

											$retval['group'][] = 'date_stamp';

											$retval['sort'][] = [ 'date_stamp' => 'asc' ];
											break;

										case 'by_pay_period_by_employee':
											$retval['columns'][] = 'date_stamp';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';

											$retval['group'][] = 'date_stamp';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';

											$retval['sub_total'][] = 'date_stamp';

											$retval['sort'][] = [ 'date_stamp' => 'asc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											$retval['sort'][] = [ 'middle_name' => 'asc' ];
											break;
										case 'by_employee':
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';

											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';

											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											$retval['sort'][] = [ 'middle_name' => 'asc' ];
											break;
										case 'by_month':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_month';

											$retval['group'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
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
										case 'by_month_by_employee':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'first_name';
											$retval['columns'][] = 'last_name';
											$retval['columns'][] = 'middle_name';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'first_name';
											$retval['group'][] = 'last_name';
											$retval['group'][] = 'middle_name';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'last_name' => 'asc' ];
											$retval['sort'][] = [ 'first_name' => 'asc' ];
											$retval['sort'][] = [ 'middle_name' => 'asc' ];
											break;
										case 'by_month_by_branch':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											break;
										case 'by_month_by_department':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_month_by_branch_by_department':
											$retval['-1010-time_period']['time_period'] = 'this_year';

											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';
											$retval['sub_total'][] = 'default_branch';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
									}
								}
							}

							if ( $template_keyword != 'pier' ) {
								//$retval['columns'] = array_merge( $retval['columns'], array_keys( Misc::trimSortPrefix( $this->getOptions('dynamic_columns') ) ) );
								$retval['columns'] = array_merge( $retval['columns'], [ 'total', 'ei_total', 'cpp_total', 'cpp2_total', 'cpp_combined_total', 'tax_total', 'gross_payroll' ] );
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
	 * @return array
	 */
	function formatFormConfig() {
		$default_include_exclude_arr = [ 'include_pay_stub_entry_account' => [], 'exclude_pay_stub_entry_account' => [] ];

		$default_arr = [
				'ei'   => $default_include_exclude_arr,
				'cpp'  => $default_include_exclude_arr,
				'cpp2' => $default_include_exclude_arr,
				'tax'  => $default_include_exclude_arr,
		];

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );

		//Due to upgrade only setting the CPP2 include accounts, make sure the exclude is defined as well to prevent PHP warnings.
		if ( !isset( $retarr['cpp2']['exclude_pay_stub_entry_account']) ) {
			$retarr['cpp2']['exclude_pay_stub_entry_account'] = [];
		}

		return $retarr;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'user' => [], 'pay_stub_entry' => [], 'pay_period' => [] ];

		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();

		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
		$pseallf->getByCompanyId( $this->getUserObject()->getCompany() );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();
		}

		$this->user_ids = [];
		$gross_payroll_psea_ids = [];
		if ( isset( $form_data['gross_payroll']['include_pay_stub_entry_account'] ) && is_array( $form_data['gross_payroll']['include_pay_stub_entry_account'] ) ) {
			$gross_payroll_psea_ids['include'] = $form_data['gross_payroll']['include_pay_stub_entry_account'];
			$gross_payroll_psea_ids['exclude'] = $form_data['gross_payroll']['exclude_pay_stub_entry_account'];
		} else {
			$gross_payroll_psea_ids['include'] = (array)$pseal_obj->getTotalGross();
			$gross_payroll_psea_ids['exclude'] = [];
		}

		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), [ 10, 20 ], 10 );
		$tax_deductions = [];
		$tax_deduction_users = [];
		$user_deduction_data = [];
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach ( $cdlf as $cd_obj ) {
				//Debug::Text('Company Deduction: ID: '. $cd_obj->getID() .' Name: '. $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
				if ( in_array( $cd_obj->getCalculation(), [ 90, 91 ] ) ) { //Only consider EI/CPP Formulas
					$tax_deductions[$cd_obj->getId()] = $cd_obj;
					$tax_deduction_users[$cd_obj->getId()] = $cd_obj->getUser(); //Optimization so we don't have to get assigned users more than once per obj, as its used lower down in a tighter loop.

					//Need to determine start/end dates for each CompanyDeduction/User pair, so we can break down total wages earned in the date ranges.
					$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
					$udlf->getByCompanyIdAndCompanyDeductionId( $cd_obj->getCompany(), $cd_obj->getId() );
					if ( $udlf->getRecordCount() > 0 ) {
						foreach ( $udlf as $ud_obj ) {
							//Debug::Text('  User Deduction: ID: '. $ud_obj->getID() .' User ID: '. $ud_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10);
							$user_deduction_data[$ud_obj->getCompanyDeduction()][$ud_obj->getUser()] = $ud_obj;
						}
					}
				}
			}
			//Debug::Arr($tax_deductions, 'Tax Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($user_deduction_data, 'User Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset( $cdlf, $cd_obj, $udlf, $ud_obj );

		$ei_employer_rate = [];
		if ( isset( $form_data['ei']['include_pay_stub_entry_account'] ) && is_array( $form_data['ei']['include_pay_stub_entry_account'] ) ) {
			//Get EI - Employer rate for each employee, as some companies can have a special rate (ie: 117.7%) if they have 3rd party sick/disability insurance.
			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
			$cdlf->getByCompanyIdAndPayStubEntryAccountIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), $form_data['ei']['include_pay_stub_entry_account'], [ 10 ], 10 ); //10=Active only.
			if ( $cdlf->getRecordCount() > 0 ) {
				foreach ( $cdlf as $cd_obj ) {
					if ( in_array( $cd_obj->getPayStubEntryAccount(), $form_data['ei']['include_pay_stub_entry_account'] ) && $cd_obj->getCalculation() == 10 && (float)$cd_obj->getUserValue1() >= 100 ) { //We only want to consider the PERCENT calculations, as they are a percent of employee. Ignore the EI specific formulas.
						Debug::Text( 'EI - Employer Company Deduction ID: '. $cd_obj->getId() .' Rate: '. (float)$cd_obj->getUserValue1(), __FILE__, __LINE__, __METHOD__, 10);
						$user_ids = $cd_obj->getUser();
						if ( is_array( $user_ids ) ) {
							foreach ( $user_ids as $user_id ) {
								if ( !isset( $ei_employer_rate[$user_id] ) ) {
									$ei_employer_rate[$user_id] = TTMath::div( (float)$cd_obj->getUserValue1(), 100 );
								} else {
									Debug::Text( 'NOTICE: User assigned to two EI - Employer Company Deductions ID: '. $cd_obj->getId(), __FILE__, __LINE__, __METHOD__, 10);
								}
							}
						}
					}
				}
			}
			unset( $cdlf, $cd_obj, $user_ids );
		}

		$psf = TTnew( 'PayStubFactory' ); /** @var PayStubFactory $psf */ //For getOptions() below.

		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		if ( $pself->getRecordCount() > 0 ) {
			$final_date_stamp = false; //Used for PayrollDeduction class below.
			foreach ( $pself as $pse_obj ) {
				$user_id = $pse_obj->getColumn( 'user_id' );
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ); //Should match PayStubSummary, RemittanceSummary, TaxSummary, GeneralLedgerSummaryReport, etc... $date_stamp too.
				$run_id = $pse_obj->getColumn( 'pay_stub_run_id' );
				if ( $date_stamp > $final_date_stamp || $final_date_stamp == false ) {
					$final_date_stamp = $date_stamp;
				}
				//$branch = $pse_obj->getColumn('default_branch');
				//$department = $pse_obj->getColumn('default_department');
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();

				$this->tmp_data['pay_period_ids']['all'][$user_id][] = $pse_obj->getColumn( 'pay_period_id' );
				if ( !isset( $this->tmp_data['pay_period_ids']['cpp'][$user_id] ) ) {
					$this->tmp_data['pay_period_ids']['cpp'][$user_id] = [];
				}

				if ( (int)$pse_obj->getColumn( 'pay_stub_type_id' ) == 100 ) { //100=YTD Balance
					$this->tmp_data['ytd_balance'][$user_id] = strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) );
				}

				if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = [
							'date_stamp' => strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ),
							'birth_date' => strtotime( $pse_obj->getColumn( 'birth_date' ) ),
							'hire_date' => strtotime( $pse_obj->getColumn( 'hire_date' ) ),

							'pay_period_start_date'       => strtotime( $pse_obj->getColumn( 'pay_period_start_date' ) ),
							'pay_period_end_date'         => strtotime( $pse_obj->getColumn( 'pay_period_end_date' ) ),
							'pay_period_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ) ),
							'pay_period'                  => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ) ),
							'pay_period_id'               => $pse_obj->getColumn( 'pay_period_id' ),

							'pay_stub_status' 			=> Option::getByKey( $pse_obj->getColumn( 'pay_stub_status_id' ), $psf->getOptions( 'status' ) ),
							'pay_stub_type_id'   		=> $pse_obj->getColumn( 'pay_stub_type_id' ),
							'pay_stub_type'   			=> Option::getByKey( $pse_obj->getColumn( 'pay_stub_type_id' ), $psf->getOptions( 'type' ) ),
							'pay_stub_start_date'       => strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ),
							'pay_stub_end_date'         => strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ),
							'pay_stub_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ) ), //Some transaction dates could be throughout the day for terminated employees being paid early, so always forward them to the middle of the day to keep group_by working correctly.
							'pay_stub_run_id'           => $run_id,
					];

					$this->form_data['pay_period'][] = strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) );
				}

				if ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn( 'amount' ) );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = $pse_obj->getColumn( 'amount' );
				}
			}

			if ( isset( $this->tmp_data['pay_stub_entry'] ) && is_array( $this->tmp_data['pay_stub_entry'] ) ) {
				foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $data_a ) {
					foreach ( $data_a as $date_stamp => $data_b ) {
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['ei']['include_pay_stub_entry_account'], $form_data['ei']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['cpp']['include_pay_stub_entry_account'], $form_data['cpp']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp2_total'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['cpp2']['include_pay_stub_entry_account'], $form_data['cpp2']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_combined_total'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp2_total'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['tax_total'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['tax']['include_pay_stub_entry_account'], $form_data['tax']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['total'] = TTMath::add( TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_combined_total'] ), $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['tax_total'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['gross_payroll'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $gross_payroll_psea_ids['include'], $gross_payroll_psea_ids['exclude'] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total_earnings'] = 0;
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total_earnings'] = 0;

						//If we exclude earnings when no CPP/EI deductions are calculated, this improves accuracy for employees who
						//  opt in/out of EI/CPP throughout the year (ie: not eligible due to age), however it doesn't catch
						//  cases when the employee should have had something deducted but didnt.
						//  It also makes cases where the employee earns a small amount on some pay stubs and CPP is not deducted on those due to too low of amount,
						//  but does on other amounts.
						//  There is no way to be accurate for both cases at this stage, so we will ignore age eligiblity for now.
						//  Taking into account the user deduction start/end dates will be the best way to handle CPP/EI start/end calculations.
						if ( is_array( $data_b['psen_ids'] ) && empty( $tax_deductions ) == false && isset( $user_deduction_data ) ) {
							//Support multiple tax/deductions that deposit to the same pay stub account.
							//Also make sure we handle tax/deductions that may not have anything deducted/withheld, but do have wages to be displayed.
							//  For example an employee not earning enough to have tax taken off yet.
							//Now that user_deduction supports start/end dates per employee, we could use that to better handle employees switching between Tax/Deduction records mid-year
							//  while still accounting for cases where nothing is deducted/withheld but still needs to be displayed.
							foreach ( $tax_deductions as $tax_deduction_id => $tax_deduction_obj ) {
								//Found Tax/Deduction associated with this pay stub account.
								if ( in_array( $user_id, (array)$tax_deduction_users[$tax_deduction_id] ) && isset( $user_deduction_data[$tax_deduction_id][$user_id] ) ) {
									//Debug::Text('Found User ID: '. $user_id .' in Tax Deduction Name: '. $tax_deduction_obj->getName() .'('.$tax_deduction_obj->getID().') Calculation ID: '. $tax_deduction_obj->getCalculation(), __FILE__, __LINE__, __METHOD__, 10);

									if ( $tax_deduction_obj->isActiveDate( $user_deduction_data[$tax_deduction_id][$user_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_start_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_transaction_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_start_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_transaction_date'] ) == true
											&& $tax_deduction_obj->isActiveLengthOfService( $user_deduction_data[$tax_deduction_id][$user_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_start_date'] ) == true
											&& $tax_deduction_obj->isActiveUserAge( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['birth_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_transaction_date'] ) == true ) {
										//Debug::Text('  Is Eligible... Date: '. TTDate::getDate('DATE', $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10);

										if ( $tax_deduction_obj->getCalculation() == 90 && in_array( $tax_deduction_obj->getPayStubEntryAccount(), (array)$form_data['cpp']['include_pay_stub_entry_account'] ) && ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total_earnings'] ) || ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total_earnings'] ) && $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total_earnings'] == 0 ) ) ) {
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total_earnings'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $tax_deduction_obj->getIncludePayStubEntryAccount(), $tax_deduction_obj->getExcludePayStubEntryAccount() );

											//If the pay stub is generated as a out-of-cycle, the CPP exemption is ignored for it, so don't count it here either.
											if ( (int)$data_b['pay_stub_type_id'] != 20 ) {//20=Bonus/Correction
												$this->tmp_data['pay_period_ids']['cpp'][$user_id][] = $data_b['pay_period_id']; //Only count pay periods with CPP earnings.
											}
										}

										if ( $tax_deduction_obj->getCalculation() == 91 && in_array( $tax_deduction_obj->getPayStubEntryAccount(), (array)$form_data['ei']['include_pay_stub_entry_account'] ) && ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total_earnings'] ) || ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total_earnings'] ) && $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total_earnings'] == 0 ) ) ) {
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total_earnings'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $tax_deduction_obj->getIncludePayStubEntryAccount(), $tax_deduction_obj->getExcludePayStubEntryAccount() );
										}
										//Debug::Text('Total Earnings: CPP '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['cpp_total_earnings'] .' EI: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['ei_total_earnings'], __FILE__, __LINE__, __METHOD__, 10);
									} else {
										Debug::Text( '  NOT Eligible... Date: ' . TTDate::getDate( 'DATE', $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
									}
								}
							}
						}

						//Only count users who have some gross payroll or deductions.
						if ( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['total'] > 0 || isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['gross_payroll'] ) && $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['gross_payroll'] > 0 ) {
							$this->user_ids[] = $user_id;
						}
					}
				}
				unset( $tax_deductions, $tax_deduction_users, $tax_deduction_id, $tax_deduction_obj, $user_deduction_data, $user_id, $data_a, $data_b );
			}

			$this->user_ids = array_unique( $this->user_ids );

			//Debug::Arr($this->tmp_data['pay_period_ids'], 'Per User Pay Periods: ', __FILE__, __LINE__, __METHOD__, 10);

			//Get PayPeriodSchedule data for each employee.
			$ppslf = TTNew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$ppslf->getByCompanyIdAndUserId( $this->getUserObject()->getCompany(), $this->user_ids );
			Debug::Text( ' Pay Periods: ' . $ppslf->getRecordCount() .' User IDs: '. count( $this->user_ids ), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $ppslf->getRecordCount() > 0 ) {
				foreach ( $ppslf as $pps_obj ) {
					$this->tmp_data['pay_period_schedule'][$pps_obj->getColumn( 'user_id' )] = [ 'annual' => $pps_obj->getAnnualPayPeriods(), 'days' => (int)Option::getByKey( $pps_obj->getAnnualPayPeriods(), $pps_obj->getOptions( 'annual_pay_periods_maximum_days' ) ) ];
				}
			}

			require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );
			$pd_obj = new PayrollDeduction( 'CA', 'BC' ); //Province doesn't matter as its just for federal calculations.
			$pd_obj->setDate( $final_date_stamp );
			Debug::Text( ' Payroll Deduction Date: ' . TTDate::getDate( 'DATE+TIME', $final_date_stamp ) . ' EI Max Earnings: ' . $pd_obj->getEIMaximumEarnings(), __FILE__, __LINE__, __METHOD__, 10 );

			//Calculate expected EI/CPP values only for the very last pay period.
			if ( isset( $this->tmp_data['pay_stub_entry'] ) && is_array( $this->tmp_data['pay_stub_entry'] ) ) {
				foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $data_a ) {
					ksort( $data_a );

					//$first_date_stamp = array_shift( array_keys( $data_a ) ); //These cause: "Only variables should be passed by reference" warnings in PHP v7
					//$last_date_stamp = array_pop( array_keys( $data_a ) ); //These cause: "Only variables should be passed by reference" warnings in PHP v7
					$first_date_stamp = key( array_slice( $data_a, 0, 1, true ) );
					$last_date_stamp = key( array_slice( $data_a, -1, 1, true ) );

					$tmp_total_cpp_pay_periods = count( array_unique( $this->tmp_data['pay_period_ids']['cpp'][$user_id] ) );

					if ( !isset( $this->tmp_data['pay_period_schedule'][$user_id] ) ) {
						$this->tmp_data['pay_period_schedule'][$user_id] = [ 'annual' => $tmp_total_cpp_pay_periods, 'days' => (int)Option::getByKey( $tmp_total_cpp_pay_periods, $ppslf->getOptions( 'annual_pay_periods_maximum_days' ) ) ];
					}
					$tmp_cpp_pro_rate = TTMath::div( $tmp_total_cpp_pay_periods, $this->tmp_data['pay_period_schedule'][$user_id]['annual'] );
					if ( $tmp_cpp_pro_rate > 1 ) {
						$tmp_cpp_pro_rate = 1; //This can happen if last year they had 27PP and this year they have 26.
					}

					//If the employee has a opening balance pay stub, we need to calculate the estimated pay periods to the YTD balance, so we can properly pro-rate the standard deduction.
					//   *NOTE: This assumes the employee got a pay stub every pay period leading up to the YTD balance. So if they didn't this would be incorrect. But short of importing every pay stub separately, there is nothing we can do about this.
					//   *NOTE: If the employee has pay stubs that are small amounts (ie: less than the 3500 deduction / 26 PPs) and CPP was not deducted, this will also cause CPP amounts to be off.
					//   *NOTE: Do take into account the employees hire date though, in case they were hired mid-year, and only employed for a short period of time before migrating to TimeTrex and importing opening balances.
					//   *NOTE: To be slightly more accurate, find the transaction day of week, which helps for weekly/bi-weekly pay periods at least. If the employee is hired on a Mon, then it counts from that Fri for example if thats when they get paid next.
					if ( isset( $this->tmp_data['ytd_balance'][$user_id] ) ) {
						if ( $this->tmp_data['pay_period_schedule'][$user_id]['days'] == 14 || $this->tmp_data['pay_period_schedule'][$user_id]['days'] == 7 ) { //Only sync to day-of-week for Weekly and BiWeekly pay period schedules.
							$estimated_pay_periods_to_ytd_balance = ceil( TTMath::div( TTDate::getDayDifference( max( TTDate::getDateOfNextDayOfWeek( TTDate::getBeginYearEpoch( $this->tmp_data['ytd_balance'][$user_id] ), $first_date_stamp ), TTDate::getDateOfNextDayOfWeek( $this->tmp_data['pay_stub_entry'][$user_id][$first_date_stamp]['hire_date'], $first_date_stamp ) ), $this->tmp_data['ytd_balance'][$user_id], true ), $this->tmp_data['pay_period_schedule'][$user_id]['days'] ) );
						} else {
							$estimated_pay_periods_to_ytd_balance = ceil( TTMath::div( TTDate::getDayDifference( max( TTDate::getBeginYearEpoch( $this->tmp_data['ytd_balance'][$user_id] ), $this->tmp_data['pay_stub_entry'][$user_id][$first_date_stamp]['hire_date'] ), $this->tmp_data['ytd_balance'][$user_id], true ), $this->tmp_data['pay_period_schedule'][$user_id]['days'] ) );
						}
						//$estimated_pay_periods_to_ytd_balance = ceil( ( TTDate::getDayDifference( TTDate::getBeginYearEpoch( $this->tmp_data['ytd_balance'][$user_id] ), $this->tmp_data['ytd_balance'][$user_id], true ) / $this->tmp_data['pay_period_schedule'][$user_id]['days'] ) );
						$tmp_cpp_pro_rate = min( 1, ( TTMath::div( ( ( ( $tmp_total_cpp_pay_periods - 1 ) + $estimated_pay_periods_to_ytd_balance ) ), $this->tmp_data['pay_period_schedule'][$user_id]['annual'] ) ) ); //Make sure its never less than 1. Also minus 1 from $tmp_total_cpp_pay_periods because that is the YTD balance pay stub, which would already be included in the $estimated_pay_periods_to_ytd_balance.
					} else {
						$estimated_pay_periods_to_ytd_balance = 0;
					}

					$tmp_gross_payroll = array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'gross_payroll' ) );
					$tmp_cpp_total_earnings = array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'cpp_total_earnings' ) );
					$tmp_ei_total_earnings = array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'ei_total_earnings' ) );
					Debug::Text( ' User ID: ' . $user_id . ' PPs: ' . $tmp_total_cpp_pay_periods . '/' . $this->tmp_data['pay_period_schedule'][$user_id]['annual'] . ' (PPs to YTD: '. $estimated_pay_periods_to_ytd_balance .') ProRate: '. round( $tmp_cpp_pro_rate, 4 ) .' First Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $first_date_stamp ) . ' Last Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $last_date_stamp ) . ' Hire Date: '. TTDate::getDate( 'DATE', $this->tmp_data['pay_stub_entry'][$user_id][$first_date_stamp]['hire_date'] ) .' CPP Earnings: ' . $tmp_cpp_total_earnings . ' CPP ProRate: ' . $tmp_cpp_pro_rate, __FILE__, __LINE__, __METHOD__, 10 );

					//CPP - Calculate both Employee and Employer amounts, so thats why we multiply by 2.
					$tmp_cpp_total = TTMath::mul( TTMath::sub( $tmp_cpp_total_earnings, TTMath::mul( $pd_obj->getCPPBasicExemption(), $tmp_cpp_pro_rate ) ), $pd_obj->getCPPEmployeeRate() );
					if ( $tmp_cpp_total < 0 ) {
						$tmp_cpp_total = 0;
					}

					$tmp_cpp_total_deducted = TTMath::MoneyRound( array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'cpp_total' ) ) );
					if ( $tmp_cpp_total_deducted > 0 ) { //If nothing was deducted, assume they are exempt.
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp_total'] = TTMath::MoneyRound( TTMath::mul( ( $tmp_cpp_total > $pd_obj->getCPPEmployeeMaximumContribution() ? $pd_obj->getCPPEmployeeMaximumContribution() : $tmp_cpp_total ), 2 ) );
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp_total_diff'] = TTMath::sub( $this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp_total'], $tmp_cpp_total_deducted );
					} else {
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp_total'] = $this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp_total_diff'] = null;
					}

					//CPP2 - Calculate both Employee and Employer amounts, so thats why we multiply by 2.
					if ( $tmp_cpp_total_earnings > $pd_obj->getCPPMaximumEarnings() ) {
						$tmp_cpp2_total_earnings = min( TTMath::sub( $tmp_cpp_total_earnings, $pd_obj->getCPPMaximumEarnings() ), TTMath::sub( $pd_obj->getCPP2EmployeeMaximumPensionableEarnings(), $pd_obj->getCPPMaximumEarnings() ) );
					} else {
						$tmp_cpp2_total_earnings = 0;
					}

					$tmp_cpp2_total = TTMath::mul( $tmp_cpp2_total_earnings, $pd_obj->getCPP2EmployeeRate() );
					if ( $tmp_cpp2_total < 0 ) {
						$tmp_cpp2_total = 0;
					}

					$tmp_cpp2_total_deducted = TTMath::MoneyRound( array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'cpp2_total' ) ) );
					if ( $tmp_cpp2_total_deducted > 0 ) { //If nothing was deducted, assume they are exempt.
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp2_total'] = TTMath::MoneyRound( TTMath::mul( ( $tmp_cpp2_total > $pd_obj->getCPP2EmployeeMaximumContribution() ? $pd_obj->getCPP2EmployeeMaximumContribution() : $tmp_cpp2_total ), 2 ) );
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp2_total_diff'] = TTMath::sub( $this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp2_total'], $tmp_cpp2_total_deducted );
					} else {
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp2_total'] = $this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp2_total_diff'] = null;
					}

					//Combine CPP and CPP2
					$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp_combined_total'] = TTMath::add( array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'expected_cpp_total' ) ), array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'expected_cpp2_total' ) ) );
					$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp_combined_total_diff'] = TTMath::add( array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'expected_cpp_total_diff' ) ), array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'expected_cpp2_total_diff' ) ) );


					//EI
					$tmp_ei_total = TTMath::mul( $tmp_ei_total_earnings, $pd_obj->getEIEmployeeRate() );
					$tmp_ei_total_deducted = TTMath::MoneyRound( array_sum( Misc::arrayColumn( $this->tmp_data['pay_stub_entry'][$user_id], 'ei_total' ) ) );
					if ( $tmp_ei_total_deducted > 0 ) { //If nothing was deducted, assume they are exempt.
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_ei_total'] = TTMath::MoneyRound( TTMath::mul( ( $tmp_ei_total > $pd_obj->getEIEmployeeMaximumContribution() ? $pd_obj->getEIEmployeeMaximumContribution() : $tmp_ei_total ), ( 1 + ( isset( $ei_employer_rate[$user_id] ) ? $ei_employer_rate[$user_id] : $pd_obj->getEIEmployerRate() ) ) ) );
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_ei_total_diff'] = TTMath::sub( $this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_ei_total'], $tmp_ei_total_deducted );
					} else {
						$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_ei_total'] = $this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_ei_total_diff'] = null;
					}

					$this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_total_diff'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_cpp_combined_total_diff'], $this->tmp_data['pay_stub_entry'][$user_id][$last_date_stamp]['expected_ei_total_diff'] );
				}
				unset( $first_date_stamp, $last_date_stamp, $tmp_cpp_pro_rate, $tmp_gross_payroll, $tmp_cpp_total_earnings, $tmp_ei_total_earnings, $tmp_cpp_total, $tmp_ei_total, $tmp_cpp_total_deducted, $tmp_ei_total_deducted, $user_id, $data_a );
			}
		}
		//Debug::Arr($this->tmp_data['pay_stub_entry'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

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

			foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				//UserListFactory can filter on policy_group_id, which PayStubEntryListFactory does not, so there can be cases where $this->tmp_data['user'][$user_id] does not exist. Skip those records.
				if ( !isset( $this->tmp_data['user'][$user_id] ) ) {
					continue;
				}

				foreach ( $level_1 as $date_stamp => $row ) {
					$date_columns = TTDate::getReportDates( null, $date_stamp, false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ] );
					$processed_data = [//'pay_period' => array('sort' => $row['pay_period_start_date'], 'display' => TTDate::getDate('DATE', $row['pay_period_start_date'] ).' -> '. TTDate::getDate('DATE', $row['pay_period_end_date'] ) ),
					];

					$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data );

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
					$key++;
				}
			}
			unset( $this->tmp_data, $row, $date_columns, $processed_data, $level_1 );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @param int $transaction_epoch EPOCH
	 * @param $avg_monthly_remittance
	 * @return bool|false|int
	 */
	function getRemittanceDueDate( $transaction_epoch, $avg_monthly_remittance ) {
		Debug::text( 'Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $transaction_epoch ), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $transaction_epoch > 0 ) {
			if ( $avg_monthly_remittance < 15000 ) {
				Debug::text( 'Regular Monthly', __FILE__, __LINE__, __METHOD__, 10 );
				//15th of the month FOLLOWING transaction_epoch.
				$due_date = mktime( 0, 0, 0, ( date( 'n', $transaction_epoch ) + 1 ), 15, date( 'y', $transaction_epoch ) );
			} else if ( $avg_monthly_remittance >= 15000 && $avg_monthly_remittance < 49999.99 ) {
				Debug::text( 'Accelerated Threshold 1', __FILE__, __LINE__, __METHOD__, 10 );
				/*
				Amounts you deduct or withhold from remuneration paid in the first 15 days of the month
				are due by the 25th of the same month. Amounts you withhold from the 16th to the end of
				the month are due by the 10th day of the following month.
				*/
				if ( date( 'j', $transaction_epoch ) <= 15 ) {
					$due_date = mktime( 0, 0, 0, date( 'n', $transaction_epoch ), 25, date( 'y', $transaction_epoch ) );
				} else {
					$due_date = mktime( 0, 0, 0, ( date( 'n', $transaction_epoch ) + 1 ), 10, date( 'y', $transaction_epoch ) );
				}
			} else if ( $avg_monthly_remittance > 50000 ) {
				Debug::text( 'Accelerated Threshold 2', __FILE__, __LINE__, __METHOD__, 10 );

				/*
				Amounts you deduct or withhold from remuneration you pay any time during the month are due by the third working day (not counting Saturdays, Sundays, or holidays) after the end of the following periods:

				* from the 1st through the 7th day of the month;
				* from the 8th through the 14th day of the month;
				* from the 15th through the 21st day of the month; and
				* from the 22nd through the last day of the month.
				*/

				return false;
			}
		} else {
			$due_date = false;
		}

		return $due_date;
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
		$this->getOutput( 'raw' ); //Generate the report so getSummaryTableData() has data to work with.
		if ( $this->hasData() ) {
			$output_data = $this->getSummaryTableData( 'raw' );

			$batch_id = date( 'M d', $prae_obj->getEndDate() );

			$amount_due = ( isset( $output_data['total'] ) ) ? $output_data['total'] : null;

			//NOTE: This is mostly for the tax wizard data, some of this data is overridden in PayStubTransactionFactory::exportPayStubRemittanceAgencyReports().
			$retarr = [
					'object'               => __CLASS__,
					'user_success_message' => TTi18n::gettext( 'Payment submitted successfully for $%1', [ TTMath::MoneyRound( $amount_due ) ] ),

					'agency_report_data' => [
							'type_id'         => 'P', //P=Payment
							'total_employees' => ( isset( $output_data['employees'] ) ) ? (int)$output_data['employees'] : null,
							'subject_wages'   => ( isset( $output_data['gross_payroll'] ) ) ? $output_data['gross_payroll'] : null,
							'taxable_wages'   => ( isset( $output_data['gross_payroll'] ) ) ? $output_data['gross_payroll'] : null,
							'amount_withheld' => $amount_due,
							'amount_due'      => $amount_due,
							'due_date'        => $prae_obj->getDueDate(),
							'extra_data'      => $output_data,

							'remote_batch_id' => $batch_id,

							//Generate a consistent remote_id based on the exact time period, the remittance agency event, and batch ID.
							//This helps to prevent duplicate records from be created, as well as work across separate or split up batches that may be processed.
							//  This needs to take into account different start/end date periods, so we don't try to overwrite records from last year.
							'remote_id'       => TTUUID::convertStringToUUID( md5( $prae_obj->getId() . $prae_obj->getStartDate() . $prae_obj->getEndDate() ) ),
					],
			];

			return $retarr;
		}

		Debug::Text( 'No report data!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * @param null $format
	 * @return null
	 */
	function getSummaryTableData( $format = null ) {
		$form_data = $this->formatFormConfig();
		$filter_data = $this->getFilterConfig();

		//These are handled by Remittance Agency Events now.
		//Get the earliest transaction date of all pay periods.
		//$this->form_data['pay_period'] = array_unique( (array)$this->form_data['pay_period'] );
		//ksort( $this->form_data['pay_period'] );
		//$transaction_date = current( (array)$this->form_data['pay_period'] );
		//Debug::Text( 'Transaction Date: ' . TTDate::getDate( 'DATE', $transaction_date ) . '(' . $transaction_date . ')', __FILE__, __LINE__, __METHOD__, 10 );

		$summary_table_data = $this->total_row;
		if ( isset($form_data['this_payment']) && trim( $form_data['this_payment'] ) != '' ) {
			$summary_table_data['raw_total'] = TTMath::MoneyRound( $summary_table_data['total'] );
			$summary_table_data['total_override'] = true;
			$summary_table_data['total'] = TTMath::MoneyRound( trim( $form_data['this_payment'] ) );
		}

		$summary_table_data['cpp_total'] = ( ( isset( $summary_table_data['cpp_combined_total'] ) ) ? $summary_table_data['cpp_combined_total'] : 0 );
		$summary_table_data['ei_total'] = ( ( isset( $summary_table_data['ei_total'] ) ) ? $summary_table_data['ei_total'] : 0 );
		$summary_table_data['tax_total'] = ( ( isset( $summary_table_data['tax_total'] ) ) ? $summary_table_data['tax_total'] : 0 );
		$summary_table_data['total'] = ( ( isset( $summary_table_data['total'] ) ) ? $summary_table_data['total'] : 0 );
		$summary_table_data['gross_payroll'] = ( ( isset( $summary_table_data['gross_payroll'] ) ) ? $summary_table_data['gross_payroll'] : 0 );
		$summary_table_data['employees'] = count( $this->user_ids );

		//These are handled by Remittance Agency Events now.
		//$remittance_due_date = $this->getRemittanceDueDate( $transaction_date, ( isset( $summary_table_data['total'] ) ) ? $summary_table_data['total'] : 0 );
		//$summary_table_data['due_date'] = ( $remittance_due_date > 0 ) ? TTDate::getDate( 'DATE', $remittance_due_date ) : TTi18n::getText( "N/A" );

		$summary_table_data['end_remitting_period'] = ( isset( $filter_data['end_date'] ) && $filter_data['end_date'] > 0 ) ? TTDate::getDate( 'DATE', $filter_data['end_date'] ) : TTi18n::getText( "N/A" );

		if ( $format != 'raw' ) {
			$summary_table_data['cpp_total'] = TTi18n::formatCurrency( $summary_table_data['cpp_total'] );
			$summary_table_data['ei_total'] = TTi18n::formatCurrency( $summary_table_data['ei_total'] );
			$summary_table_data['tax_total'] = TTi18n::formatCurrency( $summary_table_data['tax_total'] );
			$summary_table_data['total'] = TTi18n::formatCurrency( $summary_table_data['total'] );
			$summary_table_data['gross_payroll'] = TTi18n::formatCurrency( $summary_table_data['gross_payroll'] );
		}

		return $summary_table_data;
	}

	/**
	 * @return bool
	 */
	function _pdf_Header() {
		if ( $this->pdf->getPage() == 1 ) {
			//Draw separate table at the top showing the summarized data specifically for the form.
			$column_options = [
					'cpp_total'            => TTi18n::getText( 'CPP Contributions' ),
					'ei_total'             => TTi18n::getText( 'EI Premiums' ),
					'tax_total'            => TTi18n::getText( 'Tax Deductions' ),
					'total'                => TTi18n::getText( 'This Payment' ),
					'gross_payroll'        => TTi18n::getText( 'Gross Payroll' ),
					'employees'            => TTi18n::getText( 'Total Employees' ),
					'end_remitting_period' => TTi18n::getText( 'End of Period' ),
					//'due_date'             => TTi18n::getText( 'Due Date' ),
			];
			$columns = [
					'cpp_total'            => true,
					'ei_total'             => true,
					'tax_total'            => true,
					'total'                => true,
					'gross_payroll'        => true,
					'employees'            => true,
					'end_remitting_period' => true,
					//'due_date'             => true,
			];

			$header_layout = $this->config['other']['layout']['header'];

			$margins = $this->pdf->getMargins();
			$page_width = ( $this->pdf->getPageWidth() - $margins['right'] );

			//Draw report information
			if ( $this->pdf->getPage() > 1 ) {
				$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.
			}

			if ( is_array( $columns ) && count( $columns ) > 0 ) {
				$this->pdf->SetFont( $this->config['other']['default_font'], 'B', $this->_pdf_fontSize( $this->config['other']['table_header_font_size'] ) );
				$this->pdf->setTextColor( 0 );
				$this->pdf->setDrawColor( 0 );
				$this->pdf->setFillColor( 240 ); //Grayscale only.

				$column_widths = $this->_pdf_getTableColumnWidths( $this->getLargestColumnData( array_intersect_key( $column_options, (array)$columns ) ), $this->config['other']['layout']['header'] ); //Table largest column data;
				//$column_widths['due_date'] -= 1;                                                                                                                                                         //Fix bug with column header extending too far.
				foreach ( $columns as $column => $tmp ) {
					if ( isset( $column_options[$column] ) && isset( $column_widths[$column] ) ) {
						$cell_width = $column_widths[$column];
						if ( ( $this->pdf->getX() + $cell_width ) > $page_width ) {
							Debug::Text( ' Page not wide enough, it should be at least: ' . ( $this->pdf->getX() + $cell_width ) . ' Page Width: ' . $page_width, __FILE__, __LINE__, __METHOD__, 10 );
							$this->pdf->Ln();
						}
						$this->pdf->Cell( $cell_width, $this->_pdf_fontSize( $header_layout['height'] ), $column_options[$column], $header_layout['border'], 0, $header_layout['align'], $header_layout['fill'], '', $header_layout['stretch'] );
						//Wrapping shouldn't be needed as the cell widths should expand to at least fit the header. Wrapping may be needed on regular rows though.
						//$this->pdf->MultiCell( $cell_width, $cell_height, $column_options[$column], 0, $header_layout['align'], $header_layout['fill'], 0 );
					} else {
						Debug::Text( ' Invalid Column: ' . $column, __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
				unset( $tmp ); //code standards
				$this->pdf->Ln();

				$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.


				//Reset all styles/fills after page break.
				$this->pdf->SetFont( $this->config['other']['default_font'], '', $this->_pdf_fontSize( $this->config['other']['table_row_font_size'] ) );
				$this->pdf->SetTextColor( 0 );
				$this->pdf->SetDrawColor( 0 );
				$this->pdf->setFillColor( 255 );

				//Draw data
				$border = 0;

				$row_layout = [
						'max_width'    => 30,
						'cell_padding' => 2,
						'height'       => 5,
						'align'        => 'R',
						'border'       => 0,
						'fill'         => 1,
						'stretch'      => 1,
				];

				$summary_table_data = $this->getSummaryTableData();

				foreach ( $columns as $column => $tmp ) {
					$value = $summary_table_data[$column];
					$cell_width = ( isset( $column_widths[$column] ) ) ? $column_widths[$column] : 30;

					if ( $column == 'total' ) { //Highlight current payment.
						$this->pdf->setTextColor( 255, 0, 0 );

						if ( isset($summary_table_data['total_override']) && $summary_table_data['total_override'] == true ) {
							$value .= '*'; //Append asterisk and add note so its clear this is an override.
						}
					}
					$this->pdf->Cell( $cell_width, $this->_pdf_fontSize( $row_layout['height'] ), $value, $border, 0, $row_layout['align'], $row_layout['fill'], '', $row_layout['stretch'] );
					$this->pdf->setTextColor( 0 );
				}
				$this->pdf->Ln();
				$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.

				if ( isset($summary_table_data['total_override']) && $summary_table_data['total_override'] == true ) {
					$this->pdf->setTextColor( 255, 0, 0 );
					$this->pdf->Cell( $page_width, $this->_pdf_fontSize( $row_layout['height'] ), '*' . TTi18n::getText( 'WARNING: This Payment amount has been overridden from its calculated amount of' ) . ': ' . TTi18n::formatCurrency( $summary_table_data['raw_total'] ), $border, 0, 'C', 0, '', 1 );
					$this->pdf->setTextColor( 0 );
				}

				$this->pdf->Ln( 1.0 );

				$this->pdf->Ln();
				$this->_pdf_drawLine( 0.75 ); //Slightly smaller than first/last lines.
			}
		}

		parent::_pdf_Header();

		return true;
	}

	/**
	 * @return bool
	 */
	function _html_Header() {
		$column_options = [
				'cpp_total'            => TTi18n::getText( 'CPP Contributions' ),
				'ei_total'             => TTi18n::getText( 'EI Premiums' ),
				'tax_total'            => TTi18n::getText( 'Tax Deductions' ),
				'total'                => TTi18n::getText( 'This Payment' ),
				'gross_payroll'        => TTi18n::getText( 'Gross Payroll' ),
				'employees'            => TTi18n::getText( 'Total Employees' ),
				'end_remitting_period' => TTi18n::getText( 'End of Period' ),
				//'due_date'             => TTi18n::getText( 'Due Date' ),
		];
		$columns = [
				'cpp_total'            => true,
				'ei_total'             => true,
				'tax_total'            => true,
				'total'                => true,
				'gross_payroll'        => true,
				'employees'            => true,
				'end_remitting_period' => true,
				//'due_date'             => true,
		];


		if ( is_array( $columns ) && count( $columns ) > 0 ) {

			$this->html .= '<style type="text/css">';
			$this->html .= '.pay-online{ border-top: 5px solid #000000; text-align: center; font-size: ' . $this->_html_fontSize( 200 ) . '% }';
			$this->html .= '.pay-online a{ border: 5px solid red; width: 40%; display: block; margin: 2 auto; }';
			$this->html .= '</style>';

			$this->html .= '<table class="content">';
			$this->html .= '<thead>';
			$this->html .= '<tr class="content-thead content-header">';
			foreach ( $columns as $column => $tmp ) {
				if ( isset( $column_options[$column] ) ) {
					$this->html .= '<th>' . wordwrap( $column_options[$column], $this->config['other']['table_header_word_wrap'], '<br>' ) . '</th>';
				} else {
					$this->html .= '<th>&nbsp;</th>';
					Debug::Text( ' Invalid Column: ' . $column, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
			unset( $tmp ); //code standards
			$this->html .= '</tr>';

			$summary_table_data = $this->getSummaryTableData();

			$this->html .= '<tr>';
			foreach ( $columns as $column => $tmp ) {
				$value = $summary_table_data[$column];
				if ( $column == 'total' ) {
					if ( isset($summary_table_data['total_override']) && $summary_table_data['total_override'] == true ) {
						$value .= '*'; //Append asterisk and add note so its clear this is an override.
					}
					$this->html .= '<th style="color: rgb(255, 0, 0);">' . wordwrap( $value, $this->config['other']['table_header_word_wrap'], '<br>' ) . '</th>';
				} else {
					$this->html .= '<th>' . wordwrap( $value, $this->config['other']['table_header_word_wrap'], '<br>' ) . '</th>';
				}
			}
			$this->html .= '</tr>';

			//$this->html .= '<tr><th class="pay-online" colspan="' . count($columns) . '"><a href="https://coreapi.timetrex.com/r.php?id=10100" target="_blank">PAY ONLINE NOW</a></th></tr>';
			if ( isset($summary_table_data['total_override']) && $summary_table_data['total_override'] == true ) {
				$this_payment_override = '<tr><th style="text-align: center; color: rgb(255, 0, 0);" colspan="' . count( $columns ) . '">*'. TTi18n::getText('WARNING: This Payment amount has been overridden from its calculated amount of' ) .': '. TTi18n::formatCurrency( $summary_table_data['raw_total'] ) . '</th></tr>';
			} else {
				$this_payment_override = '<br>';
			}
			$this->html .= '<tr><th class="content-tbody bg-white top-border-bold font-weight-td" colspan="' . count( $columns ) . '">'. $this_payment_override .'</th></tr>';

			$this->html .= '</thead>';
			$this->html .= '</table>';
		}

		parent::_html_Header();

		return true;
	}
}

?>
