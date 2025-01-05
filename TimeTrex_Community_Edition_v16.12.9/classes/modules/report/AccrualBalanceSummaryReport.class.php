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
class AccrualBalanceSummaryReport extends Report {

	/**
	 * AccrualBalanceSummaryReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Accrual Balance Summary Report' );
		$this->file_name = 'accrual_balance_summary_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_accrual_balance_summary', $user_id, $company_id ) ) {
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
					'-2035-user_tag'              => TTi18n::gettext( 'Employee Tags' ),
					'-2040-include_user_id'       => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'       => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-default_branch_id'     => TTi18n::gettext( 'Default Branch' ),
					'-2070-default_department_id' => TTi18n::gettext( 'Default Department' ),
					'-2100-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),

					'-3000-accrual_policy_account_id' => TTi18n::gettext( 'Accrual Account' ),
					'-3050-accrual_type_id'           => TTi18n::gettext( 'Accrual Type' ),
					'-3080-accrual_policy_type_id'    => TTi18n::gettext( 'Accrual Policy Type' ),

					//'-4020-include_no_data_rows' => TTi18n::gettext('Include Blank Records'),

					'-5000-columns'    => TTi18n::gettext( 'Display Columns' ),
					'-5010-group'      => TTi18n::gettext( 'Group By' ),
					'-5020-sub_total'  => TTi18n::gettext( 'SubTotal By' ),
					'-5030-sort'       => TTi18n::gettext( 'Sort By' ),
					'-5040-page_break' => TTi18n::gettext( 'Page Break On' ),
				];
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions( false );
				break;
			case 'date_columns':
				$retval = array_merge(
						TTDate::getReportDateOptions( 'hire', TTi18n::getText( 'Hire Date' ), 13, false ),
						TTDate::getReportDateOptions( 'termination', TTi18n::getText( 'Termination Date' ), 14, false ),
						TTDate::getReportDateOptions( null, TTi18n::getText( 'Date' ), 19, false )
				);
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$retval = $this->getCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), [ 'branch', 'department', 'users', 'user_title'], [ 'users' ] );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'AccrualBalanceSummaryReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'AccrualBalanceSummaryReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'AccrualBalanceSummaryReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'AccrualBalanceSummaryReport', 'custom_column' );
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
					'-1000-first_name'  => TTi18n::gettext( 'First Name' ),
					'-1001-middle_name' => TTi18n::gettext( 'Middle Name' ),
					'-1002-last_name'   => TTi18n::gettext( 'Last Name' ),
					'-1005-full_name'   => TTi18n::gettext( 'Full Name' ),

					'-1010-user_name' => TTi18n::gettext( 'User Name' ),
					'-1020-phone_id'  => TTi18n::gettext( 'Quick Punch ID' ),

					'-1030-employee_number' => TTi18n::gettext( 'Employee #' ),

					'-1040-user_status'                  => TTi18n::gettext( 'Employee Status' ),
					'-1050-title'                        => TTi18n::gettext( 'Employee Title' ),
					'-1060-province'                     => TTi18n::gettext( 'Province/State' ),
					'-1070-country'                      => TTi18n::gettext( 'Country' ),
					'-1080-user_group'                   => TTi18n::gettext( 'Employee Group' ),
					'-1090-default_branch'               => TTi18n::gettext( 'Branch' ), //abbreviate for space
					'-1091-default_branch_manual_id'     => TTi18n::gettext( 'Branch Code' ),
					'-1100-default_department'           => TTi18n::gettext( 'Department' ), //abbreviate for space
					'-1101-default_department_manual_id' => TTi18n::gettext( 'Department Code' ),

					'-1110-currency'         => TTi18n::gettext( 'Currency' ),
					'-1112-current_currency' => TTi18n::gettext( 'Current Currency' ),

					'-1399-hire_date_age' => TTi18n::gettext( 'Length of Service' ),

					'-1820-accrual_policy_account' => TTi18n::gettext( 'Accrual Account' ),
					'-1830-type'                   => TTi18n::gettext( 'Accrual Type' ),
					//'-1160-date_stamp' => TTi18n::gettext('Date'), //Date stamp is combination of time_stamp and user_date.date_stamp columns.

					'-1850-accrual_policy'      => TTi18n::gettext( 'Accrual Policy' ),
					'-1860-accrual_policy_type' => TTi18n::gettext( 'Accrual Policy Type' ),

					'-3010-note' => TTi18n::gettext( 'Accrual Note' ),
				];

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$corporate_edition_static_columns = [
							'-1102-default_job'               => TTi18n::gettext( 'Job' ), //abbreviate for space
							'-1103-default_job_manual_id'     => TTi18n::gettext( 'Job Code' ),
							'-1104-default_job_item'           => TTi18n::gettext( 'Task' ), //abbreviate for space
							'-1105-default_job_item_manual_id' => TTi18n::gettext( 'Task Code' ),
					];
					$retval = array_merge( $retval, $corporate_edition_static_columns );
				}

				$retval = array_merge( $retval, (array)$this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), [ 'branch', 'department', 'users', 'user_title'], [ 'users' ] ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9100, $this->getUserObject()->getCompany(), ['job', 'job_item'], [] ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2020-positive_amount' => TTi18n::gettext( 'Time Accrued' ),
					'-2022-negative_amount' => TTi18n::gettext( 'Time Taken' ),

					'-2050-amount' => TTi18n::gettext( 'Accrual Time' ),
					//'-2120-running_total_amount' => TTi18n::gettext('Running Total'), //Need to handle this in an aggregate?

					'-2635-hourly_rate'  => TTi18n::gettext( 'Hourly Rate' ),
					'-2640-accrual_wage' => TTi18n::gettext( 'Accrual Wage' ),
				];

				$retval = array_merge( $retval, $this->getDynamicCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), [ 'branch', 'department', 'users', 'user_title'], [ 'users' ] ) );
				$retval = array_merge( $retval, $this->getDynamicCustomFieldColumns( 9100, $this->getUserObject()->getCompany(), ['job', 'job_item'], [] ) );
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
						}
						if ( strpos( $column, 'amount' ) !== false ) {
							$retval[$column] = 'time_unit';
						}
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
								if ( strpos( $column, 'hourly_rate' ) !== false ) {
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
						'-1250-by_policy+accrual'                     => TTi18n::gettext( 'Accruals by Account' ),
						'-1260-by_type+accrual'                       => TTi18n::gettext( 'Accruals by Type' ),
						'-1270-by_type_by_employee+accrual'           => TTi18n::gettext( 'Accruals by Type/Employee' ),
						'-1275-by_policy_by_employee+accrual'         => TTi18n::gettext( 'Accruals by Account/Employee' ),
						'-1280-by_policy_by_type_by_employee+accrual' => TTi18n::gettext( 'Accruals by Account/Type/Employee' ),
						'-1290-by_employee_by_date+accrual'           => TTi18n::gettext( 'Accruals by Account/Type/Employee/Date' ),
						'-1300-by_date+accrual'                       => TTi18n::gettext( 'Accruals by Account/Type/Date' ),

						'-1310-by_employee_by_policy+accrual' => TTi18n::gettext( 'Accruals by Employee/Account' ),

						'-1320-overall_balance_to_date' => TTi18n::gettext( 'Overall Balance To Date' ),
						'-1350-overall_balance'         => TTi18n::gettext( 'Overall Balance' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					$retval['-1010-time_period']['time_period'] = 'all_years';

					switch ( $template ) {
						case 'by_policy+accrual':
							$retval['columns'][] = 'accrual_policy_account';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'amount';

							$retval['group'][] = 'accrual_policy_account';
							$retval['sort'][] = [ 'accrual_policy_account' => 'asc' ];
							break;
						case 'by_type+accrual':
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'accrual_policy_account';
							$retval['columns'][] = 'amount';

							$retval['group'][] = 'type';

							$retval['sort'][] = [ 'type' => 'asc' ];
							break;
						case 'by_type_by_employee+accrual':
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'accrual_policy_account';
							$retval['columns'][] = 'amount';

							$retval['group'][] = 'type';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sub_total'][] = 'type';

							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_policy_by_employee+accrual':
							$retval['columns'][] = 'accrual_policy_account';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'amount';

							$retval['group'][] = 'accrual_policy_account';
							$retval['group'][] = 'last_name';
							$retval['group'][] = 'first_name';

							$retval['sub_total'][] = 'accrual_policy_account';

							$retval['sort'][] = [ 'accrual_policy_account' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_employee_by_policy+accrual':
							$retval['columns'][] = 'full_name';
							$retval['columns'][] = 'accrual_policy_account';

							$retval['columns'][] = 'amount';

							$retval['group'][] = 'full_name';
							$retval['group'][] = 'accrual_policy_account';

							$retval['sub_total'][] = 'full_name';

							$retval['sort'][] = [ 'full_name' => 'asc' ];
							$retval['sort'][] = [ 'accrual_policy_account' => 'asc' ];
							break;
						case 'by_policy_by_type_by_employee+accrual':
							$retval['columns'][] = 'accrual_policy_account';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'amount';

							$retval['group'][] = 'accrual_policy_account';
							$retval['group'][] = 'type';
							$retval['group'][] = 'last_name';
							$retval['group'][] = 'first_name';

							$retval['sub_total'][] = 'accrual_policy_account';
							$retval['sub_total'][] = 'type';

							$retval['sort'][] = [ 'accrual_policy_account' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_date+accrual':
							$retval['columns'][] = 'accrual_policy_account';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'date_stamp';

							$retval['columns'][] = 'amount';

							$retval['group'][] = 'accrual_policy_account';
							$retval['group'][] = 'type';
							$retval['group'][] = 'date_stamp';

							$retval['sub_total'][] = 'accrual_policy_account';
							$retval['sub_total'][] = 'type';

							$retval['sort'][] = [ 'accrual_policy_account' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'date_stamp' => 'asc' ];
							break;
						case 'by_employee_by_date+accrual':
							$retval['columns'][] = 'accrual_policy_account';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'date_stamp';

							$retval['columns'][] = 'amount';

							$retval['group'][] = 'accrual_policy_account';
							$retval['group'][] = 'type';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';
							$retval['group'][] = 'date_stamp';

							$retval['sub_total'][] = 'type';

							$retval['sort'][] = [ 'accrual_policy_account' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							$retval['sort'][] = [ 'date_stamp' => 'asc' ];
							break;
						case 'overall_balance_to_date':
							$retval['-1010-time_period']['time_period'] = 'to_today';

							$retval['columns'][] = 'full_name';

							$retval['columns'][] = 'accrual_policy_account';
							$retval['columns'][] = 'amount';

							$retval['group'][] = 'full_name';
							$retval['group'][] = 'accrual_policy_account';

							$retval['sub_total'][] = 'full_name';

							$retval['sort'][] = [ 'full_name' => 'asc' ];
							break;
						case 'overall_balance':
							$retval['-1010-time_period']['time_period'] = 'all_years';

							$retval['columns'][] = 'full_name';

							$retval['columns'][] = 'accrual_policy_account';
							$retval['columns'][] = 'amount';

							$retval['group'][] = 'full_name';
							$retval['group'][] = 'accrual_policy_account';

							$retval['sub_total'][] = 'full_name';

							$retval['sort'][] = [ 'full_name' => 'asc' ];
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
		$this->tmp_data = [
				'user'           => [],
				'user_title' 	 => [],
				'user_wage'      => [],
				'accrual'        => [],
				'accrual_policy' => [],
		];

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		$currency_convert_to_base = $this->getCurrencyConvertToBase();
		$base_currency_obj = $this->getBaseCurrencyObject();
		$this->handleReportCurrency( $currency_convert_to_base, $base_currency_obj, $filter_data );
		$currency_options = $this->getOptions( 'currency' );

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$wage_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$accrual_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'accrual', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( array_merge( (array)$columns, [ 'hire_date' => true, 'termination_date' => true, 'title_id' => true, 'default_branch_id' => true, 'default_department_id' => true, 'default_job_id' => true, 'default_job_item_id' => true ] ) );
			$this->tmp_data['user'][$u_obj->getId()]['user_status'] = Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions( 'status' ) );

			$this->tmp_data['user_wage'][$u_obj->getId()] = [];

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'TMP User Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user wage data for joining.
		$filter_data['wage_group_id'] = [ TTUUID::getZeroID() ]; //Use default wage groups only.
		if ( isset( $filter_data['end_date'] ) ) {
			$filter_data['effective_date'] = $filter_data['end_date'];
		}
		$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
		$uwlf->getAPILastWageSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Wage Rows: ' . $uwlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $uwlf as $key => $uw_obj ) {
			if ( $this->getPermissionObject()->isPermissionChild( $uw_obj->getUser(), $wage_permission_children_ids ) ) {
				$this->tmp_data['user_wage'][$uw_obj->getUser()] = (array)$uw_obj->getObjectAsArray( [ 'hourly_rate' => true, 'current_currency' => true, 'effective_date' => true ] ); //Force specific columns, otherwise if hourly_rate is not included wage cant be calculated.

				$this->tmp_data['user_wage'][$uw_obj->getUser()]['wage'] = $uw_obj->getWage();              //Get raw unformatted value as columnFormatter() will format it later on.
				$this->tmp_data['user_wage'][$uw_obj->getUser()]['hourly_rate'] = $uw_obj->getHourlyRate(); //Get raw unformatted value as columnFormatter() will format it later on.

				if ( $currency_convert_to_base == true && is_object( $base_currency_obj ) ) {
					$this->tmp_data['user_wage'][$uw_obj->getUser()]['current_currency'] = Option::getByKey( $base_currency_obj->getId(), $currency_options );
					if ( isset( $this->tmp_data['user'][$uw_obj->getUser()]['currency_rate'] ) ) {
						$this->tmp_data['user_wage'][$uw_obj->getUser()]['hourly_rate'] = $base_currency_obj->getBaseCurrencyAmount( $uw_obj->getHourlyRate(), $this->tmp_data['user'][$uw_obj->getUser()]['currency_rate'], $currency_convert_to_base );
					}
				}

				$this->tmp_data['user_wage'][$uw_obj->getUser()]['effective_date'] = ( isset( $this->tmp_data['user_wage'][$uw_obj->getUser()]['effective_date'] ) ) ? TTDate::parseDateTime( $this->tmp_data['user_wage'][$uw_obj->getUser()]['effective_date'] ) : null;
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user_wage'], 'TMP User Wage Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get accrual data for joining .
		$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */
		$this->setMemoryPerRow( 755 ); //755bytes per row, includes all columns as they are static.
		$alf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Accrual Rows: ' . $alf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $alf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );

		if ( !isset( $columns['date_stamp'] ) ) { //Always include the date_stamp column so other date columns can be calculated.
			$columns['date_stamp'] = true;
		}

		if ( $alf->getRecordCount() > 0 ) {
			if ( $this->isMemoryBasedRowLimitValid( $alf->getRecordCount() ) == false ) {
				$this->setMaximumRowsExceeded( true ); //Alert the user that the maximum number of rows was exceeded, but still generate the report with the data we have.
				if ( $this->handleMaximumRowsExceeded( $format ) == false ) {
					return false;
				}
			}

			foreach ( $alf as $key => $a_obj ) {
				if ( $this->getPermissionObject()->isPermissionChild( $a_obj->getUser(), $accrual_permission_children_ids ) ) {
					$tmp_data = (array)$a_obj->getObjectAsArray( $columns );
					if ( isset( $tmp_data['amount'] ) ) {
						if ( $tmp_data['amount'] < 0 ) {
							$tmp_data['negative_amount'] = $tmp_data['amount'];
						} else {
							$tmp_data['positive_amount'] = $tmp_data['amount'];
						}
					}
					$this->tmp_data['accrual'][$a_obj->getUser()][$a_obj->getAccrualPolicyAccount()][] = $tmp_data;
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
			unset( $tmp_data );
		}
		//Debug::Arr($this->tmp_data['accrual'], 'TMP Accrual Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
		$blf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' Branch Total Rows: ' . $blf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $blf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $blf as $key => $b_obj ) {
			$this->tmp_data['branch'][$b_obj->getId()] = Misc::addKeyPrefix( 'branch_', (array)$b_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'province' => true, 'manual_id' => true, 'custom_field' => true ] ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
		$dlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' Department Total Rows: ' . $dlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $dlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $dlf as $key => $d_obj ) {
			$this->tmp_data['department'][$d_obj->getId()] = Misc::addKeyPrefix( 'department_', (array)$d_obj->getObjectAsArray( [ 'id' => true, 'name' => true, 'manual_id' => true, 'province' => true, 'custom_field' => true ] ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$utlf = TTnew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
		$utlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] ); //Dont send filter data as permission_children_ids intended for users corrupts the filter
		Debug::Text( ' User Title Total Rows: ' . $dlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$user_title_column_config = array_merge( (array)Misc::removeKeyPrefix( 'user_title_', (array)$this->getColumnDataConfig() ), [ 'id' => true, 'name' => true, 'custom_field' => true ] ); //Always include title_id column so we can merge title data.
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $utlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Titles...' ) );
		foreach ( $utlf as $key => $ut_obj ) {
			$this->tmp_data['user_title'][$ut_obj->getId()] = Misc::addKeyPrefix( 'user_title_', (array)$ut_obj->getObjectAsArray( $user_title_column_config ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user_title'],'user_title_data', __FILE__, __LINE__, __METHOD__, 10);

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			//Get job data for joining.
			$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
			$jlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] );
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
			$jilf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [] );
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

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['accrual'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );
		if ( isset( $this->tmp_data['user'] ) ) {
			$column_keys = array_keys( $this->getColumnDataConfig() );

			$key = 0;
			if ( isset( $this->tmp_data['accrual'] ) ) {

				$accrual_keys = array_keys( $this->tmp_data['accrual'] ); //Memory optimization
				foreach ( $accrual_keys as $user_id ) {
					$level_2 = $this->tmp_data['accrual'][$user_id];
					if ( isset( $this->tmp_data['user'][$user_id] ) ) {
						foreach ( $level_2 as $rows ) {
							foreach ( $rows as $row ) {
								if ( isset( $row['date_stamp'] ) ) {
									$date_columns = TTDate::getReportDates( null, TTDate::parseDateTime( $row['date_stamp'] ), false, $this->getUserObject(), null, array_keys( $this->getColumnDataConfig() ) );
								} else {
									$date_columns = [];
								}

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

								if ( isset( $this->tmp_data['user'][$user_id]['default_branch_id'] ) && isset( $this->tmp_data['branch'][$this->tmp_data['user'][$user_id]['default_branch_id']] ) ) {
									$tmp_default_branch = $this->tmp_data['branch'][$this->tmp_data['user'][$user_id]['default_branch_id']];
								} else {
									$tmp_default_branch = [];
								}

								if ( isset( $this->tmp_data['user'][$user_id]['default_department_id'] ) && isset( $this->tmp_data['department'][$this->tmp_data['user'][$user_id]['default_department_id']] ) ) {
									$tmp_default_department = $this->tmp_data['department'][$this->tmp_data['user'][$user_id]['default_department_id']];
								} else {
									$tmp_default_department = [];
								}

								if ( isset( $this->tmp_data['user'][$user_id]['title_id'] ) && isset( $this->tmp_data['user_title'][$this->tmp_data['user'][$user_id]['title_id']] ) ) {
									$tmp_user_title = $this->tmp_data['user_title'][$this->tmp_data['user'][$user_id]['title_id']];
								} else {
									$tmp_user_title = [];
								}

								if ( isset( $this->tmp_data['user'][$user_id]['default_job_id'] ) && isset( $this->tmp_data['job'][$this->tmp_data['user'][$user_id]['default_job_id']] ) ) {
									$tmp_default_job = $this->tmp_data['job'][$this->tmp_data['user'][$user_id]['default_job_id']];
								} else {
									$tmp_default_job = [];
								}

								if ( isset( $this->tmp_data['user'][$user_id]['default_job_item_id'] ) && isset( $this->tmp_data['job_item'][$this->tmp_data['user'][$user_id]['default_job_item_id']] ) ) {
									$tmp_default_job_item = $this->tmp_data['job_item'][$this->tmp_data['user'][$user_id]['default_job_item_id']];
								} else {
									$tmp_default_job_item = [];
								}

								if ( !isset( $this->tmp_data['user_wage'][$user_id] ) ) {
									$this->tmp_data['user_wage'][$user_id] = [];
								}

								if ( isset( $row['amount'] ) && isset( $this->tmp_data['user_wage'][$user_id]['hourly_rate'] ) ) {
									$this->tmp_data['user_wage'][$user_id]['accrual_wage'] = TTMath::mul( TTMath::div( $row['amount'], 3600 ), $this->tmp_data['user_wage'][$user_id]['hourly_rate'] );
								} else {
									$this->tmp_data['user_wage'][$user_id]['accrual_wage'] = null;
								}

								//Merge $row after user_wage so user_wage.type column isn't passed through.
								$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $this->tmp_data['user_wage'][$user_id], $tmp_default_branch, $tmp_default_department, $tmp_user_title, $tmp_default_job, $tmp_default_job_item, $row, $date_columns, $hire_date_columns, $termination_date_columns );

								if ( ( $key % 5000 ) == 0 && $this->isMemoryLimitValid() == false ) { //Check memory requirements while processing this data, as large reports could cause memory overflow within this function that would not be caught by memory checks wrapping this function.
									return false;
								}

								$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
								$key++;
							}
						}
					}

					unset( $this->tmp_data['accrual'][$user_id] ); //Memory optimization
				}
			}
			unset( $this->tmp_data, $accrual_keys, $row, $date_columns, $hire_date_columns, $termination_date_columns, $level_2, $rows );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}
}

?>
