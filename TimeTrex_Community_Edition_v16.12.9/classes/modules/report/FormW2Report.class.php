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
class FormW2Report extends Report {
	/**
	 * FormW2Report constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Form W2 Report' );
		$this->file_name = 'form_w2';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_formW2', $user_id, $company_id ) ) {
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
											   '-1100-pdf_form'            => TTi18n::gettext( 'Employee (One Employee/Page)' ),
											   '-1110-pdf_form_government' => TTi18n::gettext( 'Government (Multiple Employees/Page)' ),
											   '-1120-efile'               => TTi18n::gettext( 'eFile' ),
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
					//'-2005-payroll_remittance_agency_id' => TTi18n::gettext('Remittance Agency'),
					'-2010-user_status_id'        => TTi18n::gettext( 'Employee Status' ),
					'-2020-user_group_id'         => TTi18n::gettext( 'Employee Group' ),
					'-2025-policy_group_id'       => TTi18n::gettext( 'Policy Group' ),
					'-2030-user_title_id'         => TTi18n::gettext( 'Employee Title' ),
					'-2040-include_user_id'       => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'       => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-default_branch_id'     => TTi18n::gettext( 'Default Branch' ),
					'-2070-default_department_id' => TTi18n::gettext( 'Default Department' ),
					'-2100-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),

					//'-4020-exclude_ytd_adjustment' => TTi18n::gettext('Exclude YTD Adjustments'),

					'-5000-columns'   => TTi18n::gettext( 'Display Columns' ),
					'-5010-group'     => TTi18n::gettext( 'Group By' ),
					'-5020-sub_total' => TTi18n::gettext( 'SubTotal By' ),
					'-5030-sort'      => TTi18n::gettext( 'Sort By' ),
					'-5040-page_break' => TTi18n::gettext('Page Break On'),
				];
				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions( false ); //Exclude Pay Period options.
				break;
			case 'date_columns':
				$retval = TTDate::getReportDateOptions( null, TTi18n::getText( 'Date' ), 13, true );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'FormW2Report', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'FormW2Report', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'FormW2Report', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'FormW2Report', 'custom_column' );
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
					'-0900-legal_entity_legal_name' => TTi18n::gettext( 'Legal Entity Name' ),
					'-0910-legal_entity_trade_name' => TTi18n::gettext( 'Legal Entity Trade Name' ),

					'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
					'-1001-middle_name'        => TTi18n::gettext( 'Middle Name' ),
					'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
					'-1005-full_name'          => TTi18n::gettext( 'Full Name' ),
					'-1030-employee_number'    => TTi18n::gettext( 'Employee #' ),
					'-1035-sin'                => TTi18n::gettext( 'SIN/SSN' ),
					'-1040-status'             => TTi18n::gettext( 'Status' ),
					'-1050-title'              => TTi18n::gettext( 'Title' ),
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

					'-1510-address1'       => TTi18n::gettext( 'Address 1' ),
					'-1512-address2'       => TTi18n::gettext( 'Address 2' ),
					'-1520-city'           => TTi18n::gettext( 'City' ),
					'-1522-province'       => TTi18n::gettext( 'Province/State' ),
					'-1524-country'        => TTi18n::gettext( 'Country' ),
					'-1526-postal_code'    => TTi18n::gettext( 'Postal Code' ),
					'-1530-work_phone'     => TTi18n::gettext( 'Work Phone' ),
					'-1540-work_phone_ext' => TTi18n::gettext( 'Work Phone Ext' ),
					'-1550-home_phone'     => TTi18n::gettext( 'Home Phone' ),
					'-1560-home_email'     => TTi18n::gettext( 'Home Email' ),
					'-1590-note'           => TTi18n::gettext( 'Note' ),
					'-1595-tag'            => TTi18n::gettext( 'Tags' ),
				];

				$retval = array_merge( $retval, $this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2010-l1'   => TTi18n::gettext( 'Wages (1)' ),
					'-2020-l2'   => TTi18n::gettext( 'Federal Income Tax (2)' ),
					'-2030-l3'   => TTi18n::gettext( 'Social Security Wages (3)' ),
					'-2040-l4'   => TTi18n::gettext( 'Social Security Tax (4)' ),
					'-2040-l7'   => TTi18n::gettext( 'Social Security Tips (7)' ),
					'-2050-l5'   => TTi18n::gettext( 'Medicare Wages (5)' ),
					'-2060-l6'   => TTi18n::gettext( 'Medicare Tax (6)' ),
					'-2070-total_federal_deductions'   => TTi18n::gettext( 'Total Federal Deductions' ),

					'-2110-l16_total'  => TTi18n::gettext( 'State Wages (16)' ),
					'-2120-l17_total'  => TTi18n::gettext( 'State Income Tax (17)' ),
					'-2130-l18_total'  => TTi18n::gettext( 'Local Wages (18)' ),
					'-2140-l19_total'  => TTi18n::gettext( 'Local Income Tax (19)' ),

					'-2270-l8'   => TTi18n::gettext( 'Allocated Tips (8)' ),
					'-2280-l10'  => TTi18n::gettext( 'Dependent Care Benefits (10)' ),
					'-2290-l11'  => TTi18n::gettext( 'Nonqualified Plans (11)' ),

					'-2800-l12a' => TTi18n::gettext( 'Box 12a' ),
					'-2801-l12b' => TTi18n::gettext( 'Box 12b' ),
					'-2802-l12c' => TTi18n::gettext( 'Box 12c' ),
					'-2803-l12d' => TTi18n::gettext( 'Box 12d' ),
					'-2804-l12e' => TTi18n::gettext( 'Box 12e' ),
					'-2805-l12f' => TTi18n::gettext( 'Box 12f' ),
					'-2806-l12g' => TTi18n::gettext( 'Box 12g' ),
					'-2807-l12h' => TTi18n::gettext( 'Box 12h' ),

					'-2900-l14a' => TTi18n::gettext( 'Box 14a' ),
					'-2901-l14b' => TTi18n::gettext( 'Box 14b' ),
					'-2902-l14c' => TTi18n::gettext( 'Box 14c' ),
					'-2903-l14d' => TTi18n::gettext( 'Box 14d' ),
					'-2904-l14e' => TTi18n::gettext( 'Box 14e' ),
					'-2905-l14f' => TTi18n::gettext( 'Box 14f' ),
					'-2906-l14g' => TTi18n::gettext( 'Box 14g' ),
					'-2907-l14h' => TTi18n::gettext( 'Box 14h' ),
				];

				$retval = Misc::trimSortPrefix( $retval ); //Sort prefix is added back at the end.

				//Grab the form config, so we can pull in the Box 12 codes and Box 14 names, and adjust the columns labels to match.
				//FIXME: Do the same for states, so its a different column for each state. However we would have to map "a" to "AL" etc,
				// 		 and if the order of the Tax/Deductions changed, it could completely change the report data. Maybe they saved a report with "state_wages_a" which was "AL", but then they added a new state and now its OR or something.
				$form_data = $this->getCompanyFormConfig();
				if ( is_array( $form_data ) ) {
					foreach ( $form_data as $column => $data ) {
						if ( $column != 'l12' && strpos( $column, 'l12' ) !== false ) {
							if ( isset( $form_data[$column .'_code'] ) && $form_data[$column .'_code'] != '' ) {
								$retval[$column] = TTi18n::gettext( 'Box 12 (%1)', [ $form_data[$column . '_code'] ] );
							} else {
								unset( $retval[$column] );
							}
						} elseif ( $column != 'l14' && strpos( $column, 'l14' ) !== false  ) {
							if ( isset( $form_data[$column .'_name'] ) && $form_data[$column .'_name'] != '' ) {
								$retval[$column] = TTi18n::gettext( 'Box 14 (%1)', [ $form_data[$column . '_name'] ] );
							} else {
								unset( $retval[$column] );
							}
						}
					}
				}

				$retval = Misc::addSortPrefix( $retval );

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
			case 'form_type': //Type of Form to generate.
				$retval = [
						'-1010-w2' => TTi18n::getText( 'W-2' ),
				];

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$retval['-1020-w2c'] = TTi18n::getText( 'W-2C' );
				}
				break;
			case 'kind_of_employer':
				$retval = [
						'-1010-N' => TTi18n::getText( 'None Apply' ),
						'-1020-T' => TTi18n::getText( '501c Non-Gov\'t' ),
						'-1030-S' => TTi18n::getText( 'State/Local Non-501c' ),
						'-1040-Y' => TTi18n::getText( 'State/Local 501c' ),
						'-1050-F' => TTi18n::getText( 'Federal Gov\'t' ),
				];
				break;
			case 'templates':
				$retval = [
					//'-1010-by_month' => TTi18n::gettext('by Month'),
					'-1020-by_employee'             => TTi18n::gettext( 'by Employee' ),
					'-1030-by_branch'               => TTi18n::gettext( 'by Branch' ),
					'-1040-by_department'           => TTi18n::gettext( 'by Department' ),
					'-1050-by_branch_by_department' => TTi18n::gettext( 'by Branch/Department' ),

					//'-1060-by_month_by_employee' => TTi18n::gettext('by Month/Employee'),
					//'-1070-by_month_by_branch' => TTi18n::gettext('by Month/Branch'),
					//'-1080-by_month_by_department' => TTi18n::gettext('by Month/Department'),
					//'-1090-by_month_by_branch_by_department' => TTi18n::gettext('by Month/Branch/Department'),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						case 'default':
							//Proper settings to generate the form.
							//$retval['-1010-time_period']['time_period'] = 'last_quarter';

							$retval['columns'] = $this->getOptions( 'columns' );

							$retval['group'][] = 'date_quarter_month';

							$retval['sort'][] = [ 'date_quarter_month' => 'asc' ];

							$retval['other']['grand_total'] = true;

							break;
						default:
							Debug::Text( ' Parsing template name: ' . $template, __FILE__, __LINE__, __METHOD__, 10 );
							$retval['columns'] = [];
							$retval['-1010-time_period']['time_period'] = 'last_year';

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
										case 'by_month':
											$retval['columns'][] = 'date_month';

											$retval['group'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
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
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_branch';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_branch';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'default_branch' => 'asc' ];
											break;
										case 'by_month_by_department':
											$retval['columns'][] = 'date_month';
											$retval['columns'][] = 'default_department';

											$retval['group'][] = 'date_month';
											$retval['group'][] = 'default_department';

											$retval['sub_total'][] = 'date_month';

											$retval['sort'][] = [ 'date_month' => 'asc' ];
											$retval['sort'][] = [ 'default_department' => 'asc' ];
											break;
										case 'by_month_by_branch_by_department':
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

							$retval['columns'] = array_merge( $retval['columns'], array_keys( Misc::trimSortPrefix( $this->getOptions( 'dynamic_columns' ) ) ) );

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
	function getFW2Object() {
		if ( !isset( $this->form_obj['fw2'] ) || !is_object( $this->form_obj['fw2'] ) ) {
			$this->form_obj['fw2'] = $this->getFormObject()->getFormObject( 'w2', 'US' );

			return $this->form_obj['fw2'];
		}

		return $this->form_obj['fw2'];
	}

	/**
	 * @return bool
	 */
	function clearFW2Object() {
		$this->form_obj['fw2'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getFW2CObject() {
		if ( !isset( $this->form_obj['fw2c'] ) || !is_object( $this->form_obj['fw2c'] ) ) {
			$this->form_obj['fw2c'] = $this->getFormObject()->getFormObject( 'w2c', 'US' );

			return $this->form_obj['fw2c'];
		}

		return $this->form_obj['fw2c'];
	}

	/**
	 * @return bool
	 */
	function clearFW2CObject() {
		$this->form_obj['fw2c'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getFW3Object() {
		if ( !isset( $this->form_obj['fw3'] ) || !is_object( $this->form_obj['fw3'] ) ) {
			$this->form_obj['fw3'] = $this->getFormObject()->getFormObject( 'w3', 'US' );

			return $this->form_obj['fw3'];
		}

		return $this->form_obj['fw3'];
	}

	/**
	 * @return bool
	 */
	function clearFW3Object() {
		$this->form_obj['fw3'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getFW3CObject() {
		if ( !isset( $this->form_obj['fw3c'] ) || !is_object( $this->form_obj['fw3c'] ) ) {
			$this->form_obj['fw3c'] = $this->getFormObject()->getFormObject( 'w3c', 'US' );

			return $this->form_obj['fw3c'];
		}

		return $this->form_obj['fw3c'];
	}

	/**
	 * @return bool
	 */
	function clearFW3CObject() {
		$this->form_obj['fw3c'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getRETURN1040Object() {
		if ( !isset( $this->form_obj['return1040'] ) || !is_object( $this->form_obj['return1040'] ) ) {
			$this->form_obj['return1040'] = $this->getFormObject()->getFormObject( 'RETURN1040', 'US' );

			return $this->form_obj['return1040'];
		}

		return $this->form_obj['return1040'];
	}

	/**
	 * @return bool
	 */
	function clearRETURN1040Object() {
		$this->form_obj['return1040'] = false;

		return true;
	}


	/**
	 * @return array
	 */
	function formatFormConfig() {
		$default_include_exclude_arr = [ 'include_pay_stub_entry_account' => [], 'exclude_pay_stub_entry_account' => [] ];

		$default_arr = [
				'l1'   => $default_include_exclude_arr,
				'l2'   => $default_include_exclude_arr,
				'l3'   => $default_include_exclude_arr,
				'l4'   => $default_include_exclude_arr,
				'l5'   => $default_include_exclude_arr,
				'l6'   => $default_include_exclude_arr,
				'l7'   => $default_include_exclude_arr,
				'l8'   => $default_include_exclude_arr,
				'l9'   => $default_include_exclude_arr,
				'l10'  => $default_include_exclude_arr,
				'l11'  => $default_include_exclude_arr,
				'l12a' => $default_include_exclude_arr,
				'l12b' => $default_include_exclude_arr,
				'l12c' => $default_include_exclude_arr,
				'l12d' => $default_include_exclude_arr,
				'l12e' => $default_include_exclude_arr,
				'l12f' => $default_include_exclude_arr,
				'l12g' => $default_include_exclude_arr,
				'l12h' => $default_include_exclude_arr,
				'l13b' => [ 'company_deduction' ],
				'l13c' => $default_include_exclude_arr,
				'l14'  => $default_include_exclude_arr,
				'l14a' => $default_include_exclude_arr,
				'l14b' => $default_include_exclude_arr,
				'l14c' => $default_include_exclude_arr,
				'l14d' => $default_include_exclude_arr,
				'l14e' => $default_include_exclude_arr,
				'l14f' => $default_include_exclude_arr,
				'l14g' => $default_include_exclude_arr,
				'l14h' => $default_include_exclude_arr,
				'l15'  => $default_include_exclude_arr,
				'l16'  => $default_include_exclude_arr,
				'l17'  => $default_include_exclude_arr,
				'l18'  => $default_include_exclude_arr,
				'l19'  => $default_include_exclude_arr,
				'l20'  => $default_include_exclude_arr,
		];

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );

		return $retarr;
	}

	function getL14BoxByPayStubEntryAccount( $pay_stub_entry_account ) {
		$form_data = $this->formatFormConfig();

		foreach( $form_data as $key => $value ) {
			if ( strpos( $key, 'l14' ) === 0 && isset( $value['include_pay_stub_entry_account'] ) && in_array( $pay_stub_entry_account, (array)$value['include_pay_stub_entry_account'] ) ) {
				return $key;
			}
		}

		return false;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'pay_stub_entry' => [], 'ytd_pay_stub_entry' => [], 'remittance_agency' => [] ];

		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();
		$tax_deductions = [];
		$user_deduction_data = [];

		require_once( Environment::getBasePath() . '/classes/payroll_deduction/PayrollDeduction.class.php' );
		$pd_obj = new PayrollDeduction( 'US', 'WA' ); //State doesn't matter.
		$pd_obj->setDate( $filter_data['end_date'] );

		$social_security_wage_limit = $pd_obj->getSocialSecurityMaximumEarnings();

		//
		//Figure out state/locality wages/taxes.
		//  Make sure state tax/deduction records come before district so they can be matched.
		//
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), [ 10, 20 ], 10, null, [ 'calculation_id' => 'asc', 'calculation_order' => 'asc' ] );
		Debug::Text( ' Company Deduction Total Rows: ' . $cdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach ( $cdlf as $cd_obj ) { /** @var CompanyDeductionFactory $cd_obj */

				if ( in_array( $cd_obj->getCalculation(), [ 82, 84, 200, 300 ] )
						OR ( is_object( $cd_obj->getPayrollRemittanceAgencyObject() ) && $cd_obj->getPayrollRemittanceAgencyObject()->getType() == 20 && $cd_obj->getPayrollRemittanceAgencyObject()->parseAgencyID( $cd_obj->getPayrollRemittanceAgencyObject()->getAgency(), 'id' ) == 10 ) //20=State, ID=10 (State) -- Needed for OR - State Transit Tax.
					) { //Only consider 82=Medicare (Employee), 84=Social Security (Employee) and 200,300=State/District records.
					//Debug::Text('Company Deduction: ID: '. $cd_obj->getID() .' Name: '. $cd_obj->getName(), __FILE__, __LINE__, __METHOD__, 10);
					$tax_deductions[$cd_obj->getId()] = $cd_obj;

					//Need to determine start/end dates for each CompanyDeduction/User pair, so we can break down total wages earned in the date ranges.
					$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
					$udlf->getByCompanyIdAndCompanyDeductionId( $cd_obj->getCompany(), $cd_obj->getId() );
					if ( $udlf->getRecordCount() > 0 ) {
						foreach ( $udlf as $ud_obj ) {
							//if ( $ud_obj->getStartDate() != '' || $ud_obj->getEndDate() != '' ) { //Always include UserDeduction map so we know if a user isn't assigned to it all.
								//Debug::Text('  User Deduction: ID: '. $ud_obj->getID() .' User ID: '. $ud_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10);
								$user_deduction_data[$ud_obj->getCompanyDeduction()][$ud_obj->getUser()] = $ud_obj;

								//Map calculation types to specific CompanyDeduction records for handling social security/medicare start/end dates below.
								$user_deduction_to_calculation_type_map[$cd_obj->getCalculation()][$ud_obj->getUser()] = $ud_obj->getCompanyDeduction();
							//}
						}
					}
				}
			}
			//Debug::Arr($tax_deductions, 'Tax Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
			//Debug::Arr($user_deduction_data, 'User Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
		}
		unset( $cd_obj );

		//Get users assigned to Box 13b (Retirement Plan) tax/deductions.
		// **NOTE: See "Form W-2 Box 13 Retirement Plan Checkbox Decision Chart" in W2 instructions for when this should be checked.
		//         Because it could be checked with $0 in contributions, we can't use contributions as another filter.
		if ( isset( $form_data['l13b']['company_deduction'] ) ) {
			$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
			$udlf->getByCompanyIdAndCompanyDeductionId( $this->getUserObject()->getCompany(), $form_data['l13b']['company_deduction'] );
			if ( $udlf->getRecordCount() > 0 ) {
				foreach ( $udlf as $ud_obj ) {
					if ( ( $ud_obj->getStartDate() == '' || $ud_obj->getStartDate() <= $filter_data['end_date'] )
							&& ( $ud_obj->getEndDate() == '' || $ud_obj->getEndDate() >= $filter_data['start_date'] ) ) {
						$this->form_data['l13_user_deduction_data'][$ud_obj->getUser()] = true;
					}
				}
			}
		}
		unset( $udlf, $ud_obj );


		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pself->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		Debug::Text( ' Pay Stub Entry Total Rows: ' . $pself->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pself->getRecordCount() > 0 ) {
			foreach ( $pself as $key => $pse_obj ) {
				$legal_entity_id = $pse_obj->getColumn( 'legal_entity_id' );
				$user_id = $pse_obj->getColumn( 'user_id' );
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) );
				$pay_stub_entry_name_id = $pse_obj->getPayStubEntryNameId();
				$user_id_legal_entity_map[$user_id] = $legal_entity_id; //Used as an optimization later on so we can easily get the legal entity for any specific user.

				if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp] = [
							'pay_period_start_date'       => strtotime( $pse_obj->getColumn( 'pay_period_start_date' ) ),
							'pay_period_end_date'         => strtotime( $pse_obj->getColumn( 'pay_period_end_date' ) ),
							'pay_period_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ) ),
							'pay_period'                  => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_period_transaction_date' ) ) ),

							'pay_stub_start_date'       => strtotime( $pse_obj->getColumn( 'pay_stub_start_date' ) ),
							'pay_stub_end_date'         => strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) ),
							'pay_stub_transaction_date' => TTDate::getMiddleDayEpoch( strtotime( $pse_obj->getColumn( 'pay_stub_transaction_date' ) ) ), //Some transaction dates could be throughout the day for terminated employees being paid early, so always forward them to the middle of the day to keep group_by working correctly.
					];
				}

				if ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] ) ) {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id], $pse_obj->getColumn( 'amount' ) );
				} else {
					$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['psen_ids'][$pay_stub_entry_name_id] = $pse_obj->getColumn( 'amount' );
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
			unset( $legal_entity_id, $user_id, $date_stamp, $pay_stub_entry_name_id );

			if ( isset( $this->tmp_data['pay_stub_entry'] ) && is_array( $this->tmp_data['pay_stub_entry'] ) ) {
				Debug::Text( ' Pay Stub Entry Total Grouped Rows: ' . count( $this->tmp_data['pay_stub_entry'] ), __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $data_a ) {
					foreach ( $data_a as $date_stamp => $data_b ) {
						if ( !isset( $this->tmp_data['ytd_pay_stub_entry'][$user_id]['l3'] ) ) {
							$this->tmp_data['ytd_pay_stub_entry'][$user_id]['l3'] = 0;
						}

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l1'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l1']['include_pay_stub_entry_account'], $form_data['l1']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l2'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l2']['include_pay_stub_entry_account'], $form_data['l2']['exclude_pay_stub_entry_account'] );

						$social_security_is_active_date = false; //Must default to false and check the mapping for every user, regardless if a start/end date exists. Otherwise it will try to calculate SS on employees are exempt and not assigned to this Tax/Deduction at all.
						if ( isset( $user_deduction_to_calculation_type_map[84] ) && isset( $user_deduction_to_calculation_type_map[84][$user_id] ) && isset( $user_deduction_data ) && isset( $user_deduction_data[$user_deduction_to_calculation_type_map[84][$user_id]] ) && isset( $user_deduction_data[$user_deduction_to_calculation_type_map[84][$user_id]][$user_id] ) ) { //84=Social Security
							$social_security_is_active_date = $cdlf->isActiveDate( $user_deduction_data[$user_deduction_to_calculation_type_map[84][$user_id]][$user_id], $data_b['pay_stub_start_date'], $data_b['pay_stub_end_date'], $data_b['pay_stub_transaction_date'], $data_b['pay_period_start_date'], $data_b['pay_period_end_date'], $data_b['pay_period_transaction_date'] );
							Debug::Text( '  Social Security Start/End Date Filters Found... Is Active: ' . (int)$social_security_is_active_date . ' Date: ' . TTDate::getDate( 'DATE', $data_b['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
						}
						if ( $social_security_is_active_date == true ) {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l3'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l3']['include_pay_stub_entry_account'], $form_data['l3']['exclude_pay_stub_entry_account'] );
						} else {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l3'] = 0;
						}

						//Make sure we cap the social security wages at the maximum amount for the year.
						$tmp_amount_around_limit_arr = TTMath::getAmountAroundLimit( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l3'], $this->tmp_data['ytd_pay_stub_entry'][$user_id]['l3'], $social_security_wage_limit );
						$this->tmp_data['ytd_pay_stub_entry'][$user_id]['l3'] = TTMath::add( $this->tmp_data['ytd_pay_stub_entry'][$user_id]['l3'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l3'] ); //YTD adjustment *must* go above where $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$run_id]['social_security_wages'] is set to $tmp_amount_around_limit_arr['adjusted_amount'], otherwise it will never exceed the SS maximum limit, which we need it to do to handle negative SS taxable wages properly.
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l3'] = $tmp_amount_around_limit_arr['adjusted_amount'];
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l4'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l4']['include_pay_stub_entry_account'], $form_data['l4']['exclude_pay_stub_entry_account'] );

						$medicare_is_active_date = false; //Must default to false and check the mapping for every user, regardless if a start/end date exists. Otherwise it will try to calculate SS on employees are exempt and not assigned to this Tax/Deduction at all.
						if ( isset( $user_deduction_to_calculation_type_map[82] ) && isset( $user_deduction_to_calculation_type_map[82][$user_id] ) && isset( $user_deduction_data ) && isset( $user_deduction_data[$user_deduction_to_calculation_type_map[82][$user_id]] ) && isset( $user_deduction_data[$user_deduction_to_calculation_type_map[82][$user_id]][$user_id] ) ) { //82=Medicare
							$medicare_is_active_date = $cdlf->isActiveDate( $user_deduction_data[$user_deduction_to_calculation_type_map[82][$user_id]][$user_id], $data_b['pay_stub_start_date'], $data_b['pay_stub_end_date'], $data_b['pay_stub_transaction_date'], $data_b['pay_period_start_date'], $data_b['pay_period_end_date'], $data_b['pay_period_transaction_date'] );
							Debug::Text( '  Medicare Start/End Date Filters Found... Is Active: ' . (int)$medicare_is_active_date . ' Date: ' . TTDate::getDate( 'DATE', $data_b['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
						}
						if ( $medicare_is_active_date == true ) {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l5']['include_pay_stub_entry_account'], $form_data['l5']['exclude_pay_stub_entry_account'] );
						} else {
							$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5'] = 0;
						}
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l6'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l6']['include_pay_stub_entry_account'], $form_data['l6']['exclude_pay_stub_entry_account'] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l7'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l7']['include_pay_stub_entry_account'], $form_data['l7']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l8'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l8']['include_pay_stub_entry_account'], $form_data['l8']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l10'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l10']['include_pay_stub_entry_account'], $form_data['l10']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l11'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l11']['include_pay_stub_entry_account'], $form_data['l11']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12a'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12a']['include_pay_stub_entry_account'], $form_data['l12a']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12b'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12b']['include_pay_stub_entry_account'], $form_data['l12b']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12c'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12c']['include_pay_stub_entry_account'], $form_data['l12c']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12d'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12d']['include_pay_stub_entry_account'], $form_data['l12d']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12e'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12e']['include_pay_stub_entry_account'], $form_data['l12e']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12f'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12f']['include_pay_stub_entry_account'], $form_data['l12f']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12g'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12g']['include_pay_stub_entry_account'], $form_data['l12g']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l12h'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l12h']['include_pay_stub_entry_account'], $form_data['l12h']['exclude_pay_stub_entry_account'] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l13c'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l13c']['include_pay_stub_entry_account'], $form_data['l13c']['exclude_pay_stub_entry_account'] ?? [] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14a'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14a']['include_pay_stub_entry_account'], $form_data['l14a']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14b'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14b']['include_pay_stub_entry_account'], $form_data['l14b']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14c'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14c']['include_pay_stub_entry_account'], $form_data['l14c']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14d'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14d']['include_pay_stub_entry_account'], $form_data['l14d']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14e'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14e']['include_pay_stub_entry_account'], $form_data['l14e']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14f'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14f']['include_pay_stub_entry_account'], $form_data['l14f']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14g'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14g']['include_pay_stub_entry_account'], $form_data['l14g']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l14h'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l14h']['include_pay_stub_entry_account'], $form_data['l14h']['exclude_pay_stub_entry_account'] );

						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['total_federal_deductions'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l2'], TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l4'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l6']  ) );

						if ( is_array( $data_b['psen_ids'] ) && empty( $tax_deductions ) == false ) {
							//Support multiple tax/deductions that deposit to the same pay stub account.
							//Also make sure we handle tax/deductions that may not have anything deducted/withheld, but do have wages to be displayed.
							//  For example an employee not earning enough to have State income tax taken off yet.
							//Now that user_deduction supports start/end dates per employee, we could use that to better handle employees switching between Tax/Deduction records mid-year
							//  while still accounting for cases where nothing is deducted/withheld but still needs to be displayed.
							//The only way to do this fully is to only consider the Tax/Deductions when the user is actually assigned to it.
							//   Therefore "if ( $tax_withheld_amount > 0 || in_array( $user_id, (array)$cd_obj->getUser() ) )" does not work,
							//      because $tax_withheld_amount > 0 breaks the case where multiple Tax/Deductions (ie: Local income tax for multiple districts) go to the same PS Account.
							//      Even if the user isn't assigned to say District 2, the amounts going to the same PS Account as District 1 would be included in it.
							//      Only way to properly handle all these cases is with Tax/Deduction Start/End dates.
							//      ** We considered trying to be "smarter" and detecting if multiple Tax/Deductions go to the same pay stub account, then only require those have the user assigned to them, but then we add inconsistency in the setup that could bite customers.
							//         Plus we can never really be sure what wages are associated with each tax if there is any kind of standard deduction amount that is excluded.
							//         Therefore to be consistent, always require that the user be assigned to a Tax/Deduction record, and start/end dates must be used if an employee moves states/localities.
							//
							//Here are some other cases to consider:
							//   1. Employee moves from one state to another. State A needs to stop at a specific date, and State B needs to start. Normally the customer would unassign the employee from State A and assign to State B.
							//         - Even though each state should go to their own PS Account and ideally the W2 would still show State A taxes in that case, it can never be 100% sure what wages go to each state, so we have to require start/end dates for this case.
							//   2. Customer has employees in multiple local districts all going to the same PS Account. Employees may also move from one district to another and need to show both on the W2.
							//         - Since each local district could be going to the same PS Account, must use Start/End dates for this too.
							foreach ( $tax_deductions as $tax_deduction_id => $cd_obj ) { /** @var CompanyDeductionFactory $cd_obj */
								if ( isset( $user_id_legal_entity_map[$user_id] ) && ( $user_id_legal_entity_map[$user_id] == $cd_obj->getLegalEntity() || $user_id_legal_entity_map[$user_id] == TTUUID::getZeroID() ) ) {
									//Found Tax/Deduction associated with this pay stub account.
									$tax_withheld_amount = Misc::calculateMultipleColumns( $data_b['psen_ids'], [ $cd_obj->getPayStubEntryAccount() ] );
									//if ( $tax_withheld_amount > 0 || in_array( $user_id, (array)$cd_obj->getUser() ) ) {  //This breaks cases where multiple Local Income Taxes go to the same Tax/Deduction because they all will have tax withhold > 0.
									if ( in_array( $user_id, (array)$cd_obj->getUser() ) == true ) { //Only check if the user is assigned to a Tax/Deduction here, so we explicitly exclude ones where they are not assigned which could be for a different district. Check for Tax/Deduction Start/End dates next as well.
										Debug::Text( '    Found User ID: ' . $user_id . ' in Tax Deduction Name: ' . $cd_obj->getName() . '(' . $cd_obj->getID() . ') Calculation ID: ' . $cd_obj->getCalculation() . ' Withheld Amount: ' . $tax_withheld_amount, __FILE__, __LINE__, __METHOD__, 10 );

										$is_active_date = false;
										if ( isset( $user_deduction_data ) && isset( $user_deduction_data[$tax_deduction_id] ) && isset( $user_deduction_data[$tax_deduction_id][$user_id] ) ) {
											$is_active_date = $cdlf->isActiveDate( $user_deduction_data[$tax_deduction_id][$user_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_start_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_transaction_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_start_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_transaction_date'] );
											//Debug::Text( '      State/Local Tax Date Restrictions Found... Is Active: ' . (int)$is_active_date . ' Date: ' . TTDate::getDate( 'DATE', $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
										}

										//State records must come before district, so they can be matched up.
										if ( $cd_obj->getCalculation() == 200 && $cd_obj->getProvince() != '' ) {
											//determine how many district/states currently exist for this employee.
											foreach ( range( 'a', 'z' ) as $z ) {
												//Make sure we are able to combine multiple state Tax/Deduction amounts together in the case
												//where they are using different Pay Stub Accounts for the State Income Tax and State Addl. Income Tax PSA's.
												//Need to have per user state detection vs per user/date, so we can make sure the state_id is unique across all possible data.
												if ( !( isset( $this->tmp_data['state_ids'][$user_id]['l17' . $z] ) && isset( $this->tmp_data['state_ids'][$user_id]['l15' . $z . '_state'] ) && $this->tmp_data['state_ids'][$user_id]['l15' . $z . '_state'] != $cd_obj->getProvince() ) ) {
													$state_id = $z;
													break;
												}
											}

											//State Wages/Taxes
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l15' . $state_id . '_state'] = $this->tmp_data['state_ids'][$user_id]['l15' . $state_id . '_state'] = $cd_obj->getProvince();

											if ( $is_active_date == true ) {
												if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16' . $state_id] ) || ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16' . $state_id] ) && $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16' . $state_id] == 0 ) ) {
													$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16' . $state_id] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $cd_obj->getIncludePayStubEntryAccount(), $cd_obj->getExcludePayStubEntryAccount() );
												}
											}
											if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17' . $state_id] ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17' . $state_id] = $this->tmp_data['state_ids'][$user_id]['l17' . $state_id] = 0;
											}
											//Just combine the tax withheld part, not the wages/earnings, as we don't want to double up on that.
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17' . $state_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17' . $state_id], Misc::calculateMultipleColumns( $data_b['psen_ids'], [ $cd_obj->getPayStubEntryAccount() ] ) );
											$this->tmp_data['state_ids'][$user_id]['l17' . $state_id] = TTMath::add( $this->tmp_data['state_ids'][$user_id]['l17' . $state_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17' . $state_id] );
											//Debug::Text('State ID: '. $state_id .' Withheld: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17'. $state_id], __FILE__, __LINE__, __METHOD__, 10);

											if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16_total'] ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16_total'] = 0;
											}

											if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17_total'] ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17_total'] = 0;
											}

											//If we are running the report for a specific state from the Tax Wizard, only include that state in the State/Local Wages/Tax Withheld.
											//  If we are running it federally, include all states.
											// NOTE: If there are more than one state that the employee is assigned too, the wages will double up for them. This is essentially a total of Box 16 and 17.
											if ( !isset( $form_data['efile_state'] ) || ( isset( $form_data['efile_state'] ) && $form_data['efile_state'] == $cd_obj->getProvince() ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16_total'] =  TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16_total'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l16' . $state_id] ?? 0 );
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17_total'] =  TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17_total'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l17' . $state_id] ?? 0 );
											}
										} else if ( $cd_obj->getCalculation() == 300 && $cd_obj->getDistrictName() != '' ) {
											$district_name = $cd_obj->getDistrictName();

											foreach ( range( 'a', 'z' ) as $z ) {
												//Make sure we are able to combine multiple district Tax/Deduction amounts together in the case
												//where they are using different Pay Stub Accounts for the District Income Tax and District Addl. Income Tax PSA's.
												//Need to have per user district detection vs per user/date, so we can make sure the district_id is unique across all possible data.
												//  Make sure we link the district to the state.
												if ( !isset( $this->tmp_data['state_ids'][$user_id]['l15' . $z . '_state'] ) || ( isset( $this->tmp_data['state_ids'][$user_id]['l15' . $z . '_state'] ) && $this->tmp_data['state_ids'][$user_id]['l15' . $z . '_state'] == $cd_obj->getProvince() ) ) {
													if ( !( isset( $this->tmp_data['district_ids'][$user_id]['l19' . $z] ) && isset( $this->tmp_data['district_ids'][$user_id]['l20' . $z . '_district'] ) && $this->tmp_data['district_ids'][$user_id]['l20' . $z . '_district'] != $district_name ) ) {
														$district_id = $z;
														break;
													}
												} else {
													Debug::Text( '      Multi-State employee, skipping mismatched StateID for District: ' . $z . ' Tax State: ' . $cd_obj->getProvince(), __FILE__, __LINE__, __METHOD__, 10 );
												}
											}

											if ( !isset( $district_id ) ) {
												Debug::Text( '      District ID not set, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
												continue;
											}

											//State
											if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l15' . $district_id . '_state'] ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l15' . $district_id . '_state'] = $cd_obj->getProvince();
											}

											//District Wages/Taxes
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l20' . $district_id . '_district'] = $this->tmp_data['district_ids'][$user_id]['l20' . $district_id . '_district'] = $district_name;

											if ( $is_active_date == true ) {
												if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18' . $district_id] ) || ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18' . $district_id] ) && $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18' . $district_id] == 0 ) ) {
													$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18' . $district_id] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $cd_obj->getIncludePayStubEntryAccount(), $cd_obj->getExcludePayStubEntryAccount() );
												}
											}
											if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19' . $district_id] ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19' . $district_id] = $this->tmp_data['district_ids'][$user_id]['l19' . $district_id] = 0;
											}
											//Just combine the tax withheld part, not the wages/earnings, as we don't want to double up on that.
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19' . $district_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19' . $district_id], Misc::calculateMultipleColumns( $data_b['psen_ids'], [ $cd_obj->getPayStubEntryAccount() ] ) );
											$this->tmp_data['district_ids'][$user_id]['l19' . $district_id] = TTMath::add( $this->tmp_data['district_ids'][$user_id]['l19' . $district_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19' . $district_id] );
											//Debug::Text('District Name: '. $district_name .' ID: '. $district_id .' Withheld: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19'. $district_id], __FILE__, __LINE__, __METHOD__, 10);

											if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18_total'] ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18_total'] = 0;
											}

											if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19_total'] ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19_total'] = 0;
											}

											//If we are running the report for a specific state from the Tax Wizard, only include that state in the State/Local Wages/Tax Withheld.
											//  If we are running it federally, include all states.
											// NOTE: If there are more than one local district that the employee is assigned too, the wages will double up for them. This is essentially a total of Box 18 and 19.
											if ( !isset( $form_data['efile_state'] ) || ( isset( $form_data['efile_state'] ) && $form_data['efile_state'] == $cd_obj->getProvince() ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18_total'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18_total'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l18' . $district_id] ?? 0 );
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19_total'] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19_total'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l19' . $district_id] ?? 0 );
											}
										} else {
											$l14_box_code = $this->getL14BoxByPayStubEntryAccount( $cd_obj->getPayStubEntryAccount() );

											//Find L14 boxes that reference this tax/deduction, so we can include the subject/taxable wages for it.
											//  Requried for OR - State Transit Tax.
											if ( $l14_box_code != '' ) {
												Debug::Text( '  Found linked L14 box ('. $l14_box_code.') to Tax/Deduction: ' . $cd_obj->getId() . ' Calculation: ' . $cd_obj->getCalculation() . ' District: ' . $cd_obj->getDistrictName() . ' UserValue5: ' . $cd_obj->getUserValue5() . ' CompanyValue1: ' . $cd_obj->getCompanyValue1(), __FILE__, __LINE__, __METHOD__, 10 );
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp][$l14_box_code .'_subject_wages'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $cd_obj->getIncludePayStubEntryAccount(), $cd_obj->getExcludePayStubEntryAccount() );
											} else {
												Debug::Text( '  Not State or Local income tax: ' . $cd_obj->getId() . ' Calculation: ' . $cd_obj->getCalculation() . ' District: ' . $cd_obj->getDistrictName() . ' UserValue5: ' . $cd_obj->getUserValue5() . ' CompanyValue1: ' . $cd_obj->getCompanyValue1(), __FILE__, __LINE__, __METHOD__, 10 );

											}
										}
									} else {
										Debug::Text( '  User is either not assigned to Tax/Deduction, or they do not have any calculated amounts...', __FILE__, __LINE__, __METHOD__, 10 );
									}
									unset( $tax_withheld_amount );
								} else {
									Debug::Text( '  User not assigned to Legal Entity for this CompanyDeduction record, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
							unset( $state_id, $district_id, $district_name, $tax_deduction_id, $cd_obj );
						}
					}
				}
				unset( $user_id, $date_stamp, $data_a, $data_b, $user_id_legal_entity_map );
			}
		}

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Employee Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->tmp_data['user'][$u_obj->getId()]['user_id'] = $u_obj->getId();
			$this->tmp_data['user'][$u_obj->getId()]['legal_entity_id'] = $u_obj->getLegalEntity();
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get legal entity data for joining.
		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $lelf */
		$lelf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Legal Entity Total Rows: ' . $lelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $lelf->getRecordCount(), null, TTi18n::getText( 'Retrieving Legal Entity Data...' ) );
		if ( $lelf->getRecordCount() > 0 ) {
			foreach ( $lelf as $key => $le_obj ) {
				if ( $format == 'html' || $format == 'pdf' ) {
					$this->tmp_data['legal_entity'][$le_obj->getId()] = Misc::addKeyPrefix( 'legal_entity_', (array)$le_obj->getObjectAsArray( Misc::removeKeyPrefix( 'legal_entity_', $this->getColumnDataConfig() ) ) );
					$this->tmp_data['legal_entity'][$le_obj->getId()]['legal_entity_id'] = $le_obj->getId();
				} else {
					$this->form_data['legal_entity'][$le_obj->getId()] = $le_obj;
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
		}

		//Get remittance agency for joining.
		$filter_data['type_id'] = [ 10, 20, 30 ]; //federal, state and local/city.
		$filter_data['country'] = [ 'US' ];       //US federal
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ralf->getRecordCount(), null, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			foreach ( $ralf as $key => $ra_obj ) {
				if ( $ra_obj->parseAgencyID( null, 'id' ) == 10 ) { //This is looking at the IRS agency only.
					if ( in_array( $ra_obj->getType(), [ 10, 20 ] ) ) { //10=Federal, 20=Province/State
						Debug::Text( '   Adding Remittance Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						$province_id = ( $ra_obj->getType() == 10 ) ? '00' : $ra_obj->getProvince();
						if ( !isset( $this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][$province_id] ) ) {
							$this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][$province_id] = $ra_obj->getId(); //Map province to a specific remittance object below.
						} else {
							Debug::Text( '   WARNING: Remittance Agency already exists, check to ensure none of duplicate "Agency" fields specified... Type: ' . $ra_obj->getType() .' Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( '   Skipping Remittance Agency Based on Type ID: ' . $ra_obj->getType() .' Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					}

					$this->form_data['remittance_agency_obj'][$ra_obj->getId()] = $ra_obj;
				} else {
					Debug::Text( ' Skipping Remittance Agency Based on ID: ' . $ra_obj->parseAgencyID( null, 'id' ) .' Agency ID: '. $ra_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				}
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
			}
			unset( $province_id );
		}

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @param null $format
	 * @return bool
	 */
	function _preProcess( $format = null ) {
		//Merge time data with user data
		$key = 0;
		if ( isset( $this->tmp_data['pay_stub_entry'] ) && isset( $this->tmp_data['user'] ) ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['pay_stub_entry'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

			$sort_columns = $this->getSortConfig();

			foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $level_1 ) {
				if ( isset( $this->tmp_data['user'][$user_id] ) ) {
					foreach ( $level_1 as $date_stamp => $row ) {
						$date_columns = TTDate::getReportDates( null, $date_stamp, false, $this->getUserObject(), [ 'pay_period_start_date' => $row['pay_period_start_date'], 'pay_period_end_date' => $row['pay_period_end_date'], 'pay_period_transaction_date' => $row['pay_period_transaction_date'] ] );
						$processed_data = [];

						$tmp_legal_array = [];
						if ( isset( $this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']] ) ) {
							$tmp_legal_array = $this->tmp_data['legal_entity'][$this->tmp_data['user'][$user_id]['legal_entity_id']];
						}
						$this->data[] = array_merge( $this->tmp_data['user'][$user_id], $row, $date_columns, $processed_data, $tmp_legal_array );

						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
						$key++;
					}
				}
			}
			unset( $this->tmp_data, $row, $date_columns, $processed_data, $tmp_legal_array );

			//Total data per employee for the W2 forms. Just include the columns that are necessary for the form.
			if ( is_array( $this->data ) && !( $format == 'html' || $format == 'pdf' ) ) {
				Debug::Text( 'Calculating Form Data...', __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $this->data as $row ) {
					if ( !isset( $this->form_data['user'][$row['legal_entity_id']][$row['user_id']] ) ) {
						$this->form_data['user'][$row['legal_entity_id']][$row['user_id']] = [ 'user_id' => $row['user_id'] ];
					}

					foreach ( $row as $key => $value ) {
						if ( preg_match( '/^l[0-9]{1,2}[a-z]?_(state|district)$/i', $key ) == true ) { //Static keys
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = $value;
						} else if ( is_numeric( $value ) && ( preg_match( '/^l[0-9]{1,2}[a-z]?$/i', $key ) == true || strpos( $key, '_wages' ) !== false ) ) { //Dynamic keys.
							if ( !isset( $this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] ) ) {
								$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = 0;
							}
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = TTMath::add( $this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key], $value );
						} else if ( isset( $sort_columns[$key] ) ) { //Sort columns only, to help sortFormData() later on.
							$this->form_data['user'][$row['legal_entity_id']][$row['user_id']][$key] = $value;
						}
					}
				}
			}
		}
		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->form_data, 'Form Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @param null $format
	 * @return array|bool
	 */
	function _outputPDFForm( $format = null ) {
		$file_arr = [];
		$show_background = true;
		if ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' || $format == 'efile' ) {
			$show_background = false;
		}
		Debug::Text( 'Generating Form... Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		$current_user = $this->getUserObject();
		$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( stristr( $format, 'government' ) ) {
			$form_type = 'government';
		} else {
			$form_type = 'employee';
		}

		if ( !isset( $setup_data['form_type'] ) ) {
			$setup_data['form_type'] = 'w2';
		}

		if ( isset( $this->form_data['user'] ) && is_array( $this->form_data['user'] ) ) {
			$this->sortFormData(); //Make sure forms are sorted.

			if ( isset( $setup_data['form_type'] ) && $setup_data['form_type'] == 'w2c' && getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
				Debug::Text( '  Handling Form Type: W2C...', __FILE__, __LINE__, __METHOD__, 10 );
				$this->file_name = 'form_w2c';

				//Get W2 forms for the same tax year out of Government Documents, and unserialize them to the FormW2 object.
				$gdlf = TTnew('GovernmentDocumentListFactory'); /** @var GovernmentDocumentListFactory $gdlf */
				$gdlf->getByCompanyIDAndStatusAndTypeAndDate( $this->getUserObject()->getCompany(), 20, 200, TTDate::getEndYearEpoch( $filter_data['start_date'] ) ); //20=Complete, 200=W2
				Debug::Text( '  Government Documents: '. $gdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $gdlf->getRecordCount() > 0 ) {
					foreach( $gdlf as $gd_obj ) {
						$tmp_extra_data = $gd_obj->getExtraData();
						if ( $tmp_extra_data != '' ) {
							$this->getFormObject()->unserialize( $tmp_extra_data );

							foreach( $this->getFormObject()->getForms() as $form_obj ) {
								//On first run, set the main form data, then just add records to it after that.
								if ( !isset( $fw2_prev_obj ) ) {
									$fw2_prev_obj = $form_obj; //This includes the records of the first form, so no need to call setRecords() on the first iteration.
								} else {
									$fw2_prev_obj->setRecords( $form_obj->getRecords() ); //This will merge the records to any existing ones.
								}
							}
							unset( $form_obj );

							$this->getFormObject()->clearForms();
						}
					}
				}
				unset( $gdlf, $gd_obj, $tmp_extra_data );
			}

			Debug::Text( '  Total Users: '. count( $this->form_data['user'] ), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $this->form_data['user'] as $legal_entity_id => $user_rows ) {
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

				$x = 0; //Progress bar only.
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $user_rows ), null, TTi18n::getText( 'Generating Forms...' ) );

				$legal_entity_obj = $this->form_data['legal_entity'][$legal_entity_id];

				if ( is_object( $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']] ) ) {
					$contact_user_obj = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getContactUserObject();
				}
				if ( !isset( $contact_user_obj ) || !is_object( $contact_user_obj ) ) {
					$contact_user_obj = $this->getUserObject();
				}

				if ( $format == 'efile_xml' ) {
					$return1040 = $this->getRETURN1040Object();
					// Ceate the all needed data for Return1040.xsd at here.
					$return1040->return_created_timestamp = TTDate::getDBTimeStamp( TTDate::getTime(), false );
					$return1040->year = TTDate::getYear( $filter_data['start_date'] );
					$return1040->tax_period_begin_date = date( 'Y-m-d', TTDate::getBeginDayEpoch( $filter_data['start_date'] ) );
					$return1040->tax_period_end_date = date( 'Y-m-d', TTDate::getEndDayEpoch( $filter_data['end_date'] ) );
					$return1040->software_id = '';
					$return1040->originator_efin = '';
					$return1040->originator_type_code = '';
					$return1040->pin_type_code = '';
					$return1040->jurat_disclosure_code = '';
					$return1040->pin_entered_by = '';
					$return1040->signature_date = date( 'Y-m-d', TTDate::getTime() );
					$return1040->return_type = '';
					$return1040->ssn = '';
					$return1040->name = $legal_entity_obj->getLegalName();
					$return1040->address1 = $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2();
					$return1040->city = $legal_entity_obj->getCity();
					$return1040->state = $legal_entity_obj->getProvince();
					$return1040->zip_code = $legal_entity_obj->getPostalCode();
					$return1040->ip_address = '';
					$return1040->ip_date = date( 'Y-m-d', TTDate::getTime() );
					$return1040->ip_time = date( 'H:i:s', TTDate::getTime() );
					$return1040->timezone = TTDate::getTimeZone();

					$this->getFormObject()->addForm( $return1040 );
				}

				$fw2 = $this->getFW2Object();  /** @var GovernmentForms_US_W2 $fw2 */

				$fw2->setDebug( false );
				//if ( $format == 'efile' ) {
				//	$fw2->setDebug(TRUE);
				//}
				$fw2->setShowBackground( $show_background );
				$fw2->setType( $form_type );
				$fw2->setShowInstructionPage( true );
				$fw2->year = TTDate::getYear( $filter_data['start_date'] );
				$fw2->kind_of_employer = ( isset( $setup_data['kind_of_employer'] ) && $setup_data['kind_of_employer'] != '' ) ? Misc::trimSortPrefix( $setup_data['kind_of_employer'] ) : 'N';

				$fw2->name = $legal_entity_obj->getLegalName();
				$fw2->trade_name = $legal_entity_obj->getTradeName();
				$fw2->company_address1 = $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2();
				$fw2->company_city = $legal_entity_obj->getCity();
				$fw2->company_state = $legal_entity_obj->getProvince();
				$fw2->company_zip_code = $legal_entity_obj->getPostalCode();

				$fw2->ein = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getPrimaryIdentification(); //Always use EIN from Federal Agency.
				$fw2->efile_user_id = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getTertiaryIdentification(); //Always use SSA eFile User ID

				//Only use the state specific format if its the only agency that is being returned (ie: they are filtering to a specific agency).
				// $setup_data['efile_state'] is set from PayrollRemittanceAgencyEvent->getReport().
				if ( isset( $setup_data['efile_state'] ) && $setup_data['efile_state'] != '' ) {
					$fw2->efile_state = strtoupper( $setup_data['efile_state'] );
					Debug::Text( '    Using State eFile Format: ' . $fw2->efile_state, __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					Debug::Text( '    Using Federal eFile Format...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				//$fw2->efile_state = 'OR'; //Testing only.

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id][$fw2->efile_state] )
						&& isset( $setup_data['efile_district'] ) && $setup_data['efile_district'] == true
						&& isset( $setup_data['payroll_remittance_agency_id'] ) && isset( $this->form_data['remittance_agency_obj'][$setup_data['payroll_remittance_agency_id']] ) ) {
					//$fw2->efile_user_id = $this->form_data['remittance_agency_obj'][$setup_data['payroll_remittance_agency_id']]->getTertiaryIdentification();
					$fw2->efile_agency_id = $this->form_data['remittance_agency_obj'][$setup_data['payroll_remittance_agency_id']]->getAgency();
					Debug::Text( '    Using City eFile Format: ' . $fw2->efile_agency_id, __FILE__, __LINE__, __METHOD__, 10 );
				} else if ( isset( $this->form_data['remittance_agency'][$legal_entity_id][$fw2->efile_state] ) ) {
					$remittance_agency_obj = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$fw2->efile_state]];
					$fw2->state_secondary_id = $remittance_agency_obj->getSecondaryIdentification();
					$fw2->state_tertiary_id = $remittance_agency_obj->getTertiaryIdentification();
					$fw2->efile_agency_id = $remittance_agency_obj->getAgency();

					//Get Agency Event object so we can extract additional information from it to pass along to the W2 form.
					$praelf = TTnew('PayrollRemittanceAgencyEventListFactory'); /** @var PayrollRemittanceAgencyEventListFactory $praelf */
					$praelf->getByRemittanceAgencyIdAndTypeId( $remittance_agency_obj->getId(), 'FW2' );
					Debug::Text( '    State Agency Events: ' . $praelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $praelf->getRecordCount() > 0 ) {
						foreach( $praelf as $prea_obj ) {
							$fw2->state_deposit_frequency = $prea_obj->getFrequency();
						}
					}
					unset( $remittance_agency_obj, $praelf, $prea_obj );
				} else if ( isset( $this->form_data['remittance_agency'][$legal_entity_id]['00'] ) ) {
					//$fw2->efile_user_id = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getTertiaryIdentification();
					$fw2->efile_agency_id = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getAgency();
				} else {
					Debug::Text( '    WARNING: Unable to determine remittance agency to obtain efile_user_id from...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$fw2->contact_name = $contact_user_obj->getFullName();
				$fw2->contact_phone = $contact_user_obj->getWorkPhone();
				$fw2->contact_phone_ext = $contact_user_obj->getWorkPhoneExt();
				$fw2->contact_email = ( $contact_user_obj->getWorkEmail() != '' ) ? $contact_user_obj->getWorkEmail() : ( ( $contact_user_obj->getHomeEmail() != '' ) ? $contact_user_obj->getHomeEmail() : null );

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
									'id'                  => (string)TTUUID::convertStringToUUID( md5( $user_id . $fw2->year . microtime( true ) ) ), //Should be unique for every run so we can differentiate between runs.
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
									'l1'                  => ( $row['l1'] != 0 ) ? $row['l1'] : null,
									'l2'                  => ( $row['l2'] != 0 ) ? $row['l2'] : null,
									'l3'                  => ( $row['l3'] != 0 ) ? $row['l3'] : null,
									'l4'                  => ( $row['l4'] != 0 ) ? $row['l4'] : null,
									'l5'                  => ( $row['l5'] != 0 ) ? $row['l5'] : null,
									'l6'                  => ( $row['l6'] != 0 ) ? $row['l6'] : null,
									'l7'                  => ( $row['l7'] != 0 ) ? $row['l7'] : null,
									'l8'                  => ( $row['l8'] != 0 ) ? $row['l8'] : null,
									'l10'                 => ( $row['l10'] != 0 ) ? $row['l10'] : null,
									'l11'                 => ( $row['l11'] != 0 ) ? $row['l11'] : null,
									'l12a_code'           => null,
									'l12a'                => null,
									'l12b_code'           => null,
									'l12b'                => null,
									'l12c_code'           => null,
									'l12c'                => null,
									'l12d_code'           => null,
									'l12d'                => null,
									'l12e_code'           => null,
									'l12e'                => null,
									'l12f_code'           => null,
									'l12f'                => null,
									'l12g_code'           => null,
									'l12g'                => null,
									'l12h_code'           => null,
									'l12h'                => null,
									'l13a'                => false,
									'l13b'                => false,
									'l13c'                => ( ( $row['l13c'] > 0 ) ? true : false ), //Third Party Sick Pay. Also set to TRUE below if Box 12 Code J is set.
									'l14a_name'           => null,
									'l14a'                => null,
									'l14b_name'           => null,
									'l14b'                => null,
									'l14c_name'           => null,
									'l14c'                => null,
									'l14d_name'           => null,
									'l14d'                => null,
									'l14e_name'           => null,
									'l14e'                => null,
									'l14f_name'           => null,
									'l14f'                => null,
									'l14g_name'           => null,
									'l14g'                => null,
									'l14h_name'           => null,
									'l14h'                => null,
									'states'              => [], //State codes with wages.
							];

							foreach ( range( 'a', 'h' ) as $l12_code ) {
								if ( $row['l12' . $l12_code] > 0 && isset( $setup_data['l12' . $l12_code . '_code'] ) && $setup_data['l12' . $l12_code . '_code'] != '' ) {
									$ee_data['l12' . $l12_code . '_code'] = trim( $setup_data['l12' . $l12_code . '_code'] );
									$ee_data['l12' . $l12_code] = $row['l12' . $l12_code];

									if ( strtoupper( $ee_data['l12' . $l12_code . '_code'] ) == 'J' ) { //J=Non-Taxable Sick Pay. Automatically detect this and check Box 13c.
										$ee_data['l13c'] = true;                                        //Third party sick pay checkbox.
									}
								}
							}
							unset( $l12_code );

							//See comment above where $this->form_data['l13_user_deduction_data'] is populated. We can't check if any contributions exist before checking this, as there are cases where that is valid.
							if ( isset( $this->form_data['l13_user_deduction_data'] ) && isset( $this->form_data['l13_user_deduction_data'][$user_id] ) ) {
								$ee_data['l13b'] = true;
							}

							foreach ( range( 'a', 'h' ) as $l14_name ) {
								if ( $row['l14' . $l14_name] > 0 && isset( $setup_data['l14' . $l14_name . '_name'] ) && $setup_data['l14' . $l14_name . '_name'] != '' ) {
									$ee_data['l14' . $l14_name . '_name'] = $setup_data['l14' . $l14_name . '_name'];
									$ee_data['l14' . $l14_name] = $row['l14' . $l14_name];

									if ( isset( $row['l14' . $l14_name . '_subject_wages'] ) ) {
										$ee_data['l14' . $l14_name . '_subject_wages'] = $row['l14' . $l14_name . '_subject_wages'];
									}
								}
							}
							unset( $l14_name );

							foreach ( range( 'a', 'z' ) as $z ) {
								//Make sure state information is included if its just local income taxes.
								if ( ( isset( $row['l16' . $z] ) || isset( $row['l18' . $z] ) )
										&& ( isset( $row['l15' . $z . '_state'] )
												&& isset( $this->form_data['remittance_agency'][$legal_entity_id][$row['l15' . $z . '_state']] )
												&& isset( $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$row['l15' . $z . '_state']]] )
												&& $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$row['l15' . $z . '_state']]]->getType() == 20 ) ) {
									$ee_data['l15' . $z . '_state_id'] = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$row['l15' . $z . '_state']]]->getPrimaryIdentification();
									//$ee_data['l15' . $z . '_state_control_number'] = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$row['l15' . $z . '_state']]]->getSecondaryIdentification();
									$ee_data['l15' . $z . '_state'] = $row['l15' . $z . '_state'];
								} else {
									$ee_data['l15' . $z . '_state_id'] = null;
									$ee_data['l15' . $z . '_state'] = null;
								}

								//State income tax
								if ( isset( $row['l16' . $z] ) ) {
									$ee_data['l16' . $z] = $row['l16' . $z];
									$ee_data['l17' . $z] = $row['l17' . $z];
								} else {
									$ee_data['l16' . $z] = null;
									$ee_data['l17' . $z] = null;
								}

								//District income tax
								if ( isset( $row['l18' . $z] ) ) {
									$ee_data['l20' . $z . '_district'] = $row['l20' . $z . '_district'];
									$ee_data['l18' . $z] = $row['l18' . $z];
									$ee_data['l19' . $z] = $row['l19' . $z];
								} else {
									$ee_data['l20' . $z . '_district'] = null;
									$ee_data['l18' . $z] = null;
									$ee_data['l19' . $z] = null;
								}

								//Save each state that wages were earned, so we can determine which states need W2/eFiling.
								if ( isset( $row['l15' . $z . '_state'] )
										&& ( ( isset( $row['l16' . $z] ) && $row['l16' . $z] != 0 )
												|| ( isset( $row['l18' . $z] ) && $row['l18' . $z] != 0 ) ) ) {
									//Debug::Text( ' Wages earned in State: '. $row[ 'l15' . $z . '_state' ], __FILE__, __LINE__, __METHOD__, 10 );
									$ee_data['states'][$row['l15' . $z . '_state']] = true;
								}
							}

							//If we are doing State/Local W2s, skip employees who do not have wages or deductions in that state.
							if ( isset( $fw2->efile_state ) && $fw2->efile_state != '' ) {
								if ( !isset( $ee_data['states'][$fw2->efile_state] ) ) {
									Debug::Text( '  No wages in eFile State: ' . $fw2->efile_state . ' Skipping...', __FILE__, __LINE__, __METHOD__, 10 );
									continue;
								}
							}

							$fw2->addRecord( $ee_data );
							unset( $ee_data );

							if ( $format == 'pdf_form_publish_employee' ) {
								// Generate PDF for every employee and assign to each government document records
								if ( isset( $setup_data['form_type'] ) && $setup_data['form_type'] == 'w2c' && isset( $fw2_prev_obj ) ) {
									$this->getFormObject()->addForm( $fw2 );

									$fw2c = $this->getFW2CObject();
									$fw2c->setType( $form_type );
									$fw2c->setShowBackground( $show_background );
									$fw2c->mergeCorrectAndPreviousW2Objects( $fw2, $fw2_prev_obj );
									$this->getFormObject()->clearForms(); //Clears W2 form after the W2C has been generated, so only W2C is published.

									$this->getFormObject()->addForm( $fw2c );

									GovernmentDocumentFactory::addDocument( $user_obj->getId(), 20, 205, TTDate::getEndYearEpoch( $filter_data['start_date'] ), ( ( $fw2c->countRecords() == 1 ) ? $this->getFormObject()->output( 'PDF', false ) : null ), ( ( $fw2c->countRecords() == 1 ) ? $this->getFormObject()->serialize() : null ) );
								} else {
									$this->getFormObject()->addForm( $fw2 );
									GovernmentDocumentFactory::addDocument( $user_obj->getId(), 20, 200, TTDate::getEndYearEpoch( $filter_data['start_date'] ), ( ( $fw2->countRecords() == 1 ) ? $this->getFormObject()->output( 'PDF', false ) : null ), ( ( $fw2->countRecords() == 1 ) ? $this->getFormObject()->serialize() : null ) );
								}

								$this->getFormObject()->clearForms();
								$fw2->clearRecords(); //Clear W2 records so we only ever have 1 W2 record and/or 1x W2C record at any given time.
							}

							$i++;
						}

						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $x );
						$x++;
					}
				}

				$l12a_letters = [ 'd', 'e', 'f', 'g', 'h', 's', 'y', 'aa', 'bb', 'ee' ];

				if ( $format != 'pdf_form_publish_employee' ) {
					if ( isset( $setup_data['form_type'] ) && $setup_data['form_type'] == 'w2c' && isset( $fw2_prev_obj ) && isset( $fw2 ) ) {
						$fw2c = $this->getFW2CObject();
						$fw2c->setType( $form_type );
						$fw2c->setShowBackground( $show_background );
						$fw2c->mergeCorrectAndPreviousW2Objects( $fw2, $fw2_prev_obj );
						$this->getFormObject()->addForm( $fw2c );

						if ( $fw2c->countRecords() > 0 && $form_type == 'government' ) {
							//Handle W3C
							$fw3c = $this->getFW3CObject();
							$fw3c->setShowBackground( $show_background );
							$fw3c->year = $fw2c->year;
							$fw3c->ein = $fw2c->ein;
							$fw3c->name = $fw2c->name;
							$fw3c->trade_name = $fw2c->trade_name;
							$fw3c->company_address1 = $fw2c->company_address1;
							$fw3c->company_address2 = $fw2c->company_address2;
							$fw3c->company_city = $fw2c->company_city;
							$fw3c->company_state = $fw2c->company_state;
							$fw3c->company_zip_code = $fw2c->company_zip_code;

							$fw3c->contact_name = $contact_user_obj->getFullName();
							$fw3c->contact_phone = ( $contact_user_obj->getWorkPhoneExt() != '' ) ? $contact_user_obj->getWorkPhone() . ' x' . $contact_user_obj->getWorkPhoneExt() : $contact_user_obj->getWorkPhone();
							$fw3c->contact_email = $contact_user_obj->getWorkEmail();

							$fw3c->kind_of_payer = '941';
							$fw3c->kind_of_employer = $fw2c->kind_of_employer;
							//$fw3c->third_party_sick_pay = TRUE;

							//Use the home state ID if possible.
							if ( isset( $this->form_data['remittance_agency'][$legal_entity_id][$fw2c->company_state] )
									&& isset( $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$fw2c->company_state]] ) && is_object( $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$fw2c->company_state]] ) ) {
								$fw3c->state_id1 = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$fw2c->company_state]]->getPrimaryIdentification();
							}

							$fw3c->lc = $fw2c->countRecords();

							//Use sumRecords()/getRecordsTotal() so all amounts are capped properly.
							$fw2c->sumRecords();
							$total_row = $fw2c->getRecordsTotal();

							Debug::Arr($total_row, 'Total Row Data: ', __FILE__, __LINE__, __METHOD__, 10);

							if ( is_array( $total_row ) ) {
								$fw3c->l1 = ( isset( $total_row['l1'] ) && $total_row['l1'] != 0 ) ? $total_row['l1'] : null;
								$fw3c->l2 = ( isset( $total_row['l2'] ) && $total_row['l2'] != 0 ) ? $total_row['l2'] : null;
								$fw3c->l3 = ( isset( $total_row['l3'] ) && $total_row['l3'] != 0 ) ? $total_row['l3'] : null;
								$fw3c->l4 = ( isset( $total_row['l4'] ) && $total_row['l4'] != 0 ) ? $total_row['l4'] : null;
								$fw3c->l5 = ( isset( $total_row['l5'] ) && $total_row['l5'] != 0 ) ? $total_row['l5'] : null;
								$fw3c->l6 = ( isset( $total_row['l6'] ) && $total_row['l6'] != 0 ) ? $total_row['l6'] : null;
								$fw3c->l7 = ( isset( $total_row['l7'] ) && $total_row['l7'] != 0 ) ? $total_row['l7'] : null;
								$fw3c->l8 = ( isset( $total_row['l8'] ) && $total_row['l8'] != 0 ) ? $total_row['l8'] : null;
								$fw3c->l10 = ( isset( $total_row['l10'] ) && $total_row['l10'] != 0 ) ? $total_row['l10'] : null;
								$fw3c->l11 = ( isset( $total_row['l11'] ) && $total_row['l11'] != 0 ) ? $total_row['l11'] : null;

								$fw3c->previous_l1 = ( isset( $total_row['previous_l1'] ) && $total_row['previous_l1'] != 0 ) ? $total_row['previous_l1'] : null;
								$fw3c->previous_l2 = ( isset( $total_row['previous_l2'] ) && $total_row['previous_l2'] != 0 ) ? $total_row['previous_l2'] : null;
								$fw3c->previous_l3 = ( isset( $total_row['previous_l3'] ) && $total_row['previous_l3'] != 0 ) ? $total_row['previous_l3'] : null;
								$fw3c->previous_l4 = ( isset( $total_row['previous_l4'] ) && $total_row['previous_l4'] != 0 ) ? $total_row['previous_l4'] : null;
								$fw3c->previous_l5 = ( isset( $total_row['previous_l5'] ) && $total_row['previous_l5'] != 0 ) ? $total_row['previous_l5'] : null;
								$fw3c->previous_l6 = ( isset( $total_row['previous_l6'] ) && $total_row['previous_l6'] != 0 ) ? $total_row['previous_l6'] : null;
								$fw3c->previous_l7 = ( isset( $total_row['previous_l7'] ) && $total_row['previous_l7'] != 0 ) ? $total_row['previous_l7'] : null;
								$fw3c->previous_l8 = ( isset( $total_row['previous_l8'] ) && $total_row['previous_l8'] != 0 ) ? $total_row['previous_l8'] : null;
								$fw3c->previous_l10 = ( isset( $total_row['previous_l10'] ) && $total_row['previous_l10'] != 0 ) ? $total_row['previous_l10'] : null;
								$fw3c->previous_l11 = ( isset( $total_row['previous_l11'] ) && $total_row['previous_l11'] != 0 ) ? $total_row['previous_l11'] : null;

								$fw3c->l12a = null;
								foreach ( range( 'a', 'z' ) as $z ) { //Make sure we loop over all possible Box 12 codes.
									if ( isset( $total_row['l12'. $z .'_code'] ) && in_array( strtolower( $total_row['l12'. $z .'_code'] ), $l12a_letters ) ) {
										$fw3c->l12a = TTMath::add( $fw3c->l12a, $total_row['l12'. $z] );
									}
								}

								$fw3c->previous_l12a = null;
								foreach ( range( 'a', 'z' ) as $z ) { //Make sure we loop over all possible Box 12 codes.
									if ( isset( $total_row['previous_l12'. $z .'_code'] ) && in_array( strtolower( $total_row['previous_l12'. $z .'_code'] ), $l12a_letters ) ) {
										$fw3c->previous_l12a = TTMath::add( $fw3c->previous_l12a, $total_row['previous_l12'. $z] );
									}
								}

								foreach ( range( 'a', 'z' ) as $z ) {
									//State income tax
									if ( isset( $total_row['l16' . $z] ) && $total_row['l16' . $z] != 0 ) {
										$fw3c->l16 = TTMath::add( $fw3c->l16, $total_row['l16' . $z] );
										$fw3c->l17 = TTMath::add( $fw3c->l17, $total_row['l17' . $z] );
									}
									//District income tax
									if ( isset( $total_row['l18' . $z] ) && $total_row['l18' . $z] != 0 ) {
										$fw3c->l18 = TTMath::add( $fw3c->l18, $total_row['l18' . $z] );
										$fw3c->l19 = TTMath::add( $fw3c->l19, $total_row['l19' . $z] );
									}

									//State income tax
									if ( isset( $total_row['previous_l16' . $z] ) && $total_row['previous_l16' . $z] != 0 ) {
										$fw3c->previous_l16 = TTMath::add( $fw3c->previous_l16, $total_row['previous_l16' . $z] );
										$fw3c->previous_l17 = TTMath::add( $fw3c->previous_l17, $total_row['previous_l17' . $z] );
									}
									//District income tax
									if ( isset( $total_row['previous_l18' . $z] ) && $total_row['previous_l18' . $z] != 0 ) {
										$fw3c->previous_l18 = TTMath::add( $fw3c->previous_l18, $total_row['previous_l18' . $z] );
										$fw3c->previous_l19 = TTMath::add( $fw3c->previous_l19, $total_row['previous_l19' . $z] );
									}
								}
							}

							$this->getFormObject()->addForm( $fw3c );
						}
					} else {
						$this->getFormObject()->addForm( $fw2 );

						if ( $fw2->countRecords() > 0 && $form_type == 'government' ) {
							//Handle W3
							$fw3 = $this->getFW3Object();
							$fw3->setShowBackground( $show_background );
							$fw3->year = $fw2->year;
							$fw3->ein = $fw2->ein;
							$fw3->name = $fw2->name;
							$fw3->trade_name = $fw2->trade_name;
							$fw3->company_address1 = $fw2->company_address1;
							$fw3->company_address2 = $fw2->company_address2;
							$fw3->company_city = $fw2->company_city;
							$fw3->company_state = $fw2->company_state;
							$fw3->company_zip_code = $fw2->company_zip_code;

							$fw3->contact_name = $contact_user_obj->getFullName();
							$fw3->contact_phone = ( $contact_user_obj->getWorkPhoneExt() != '' ) ? $contact_user_obj->getWorkPhone() . ' x' . $contact_user_obj->getWorkPhoneExt() : $contact_user_obj->getWorkPhone();
							$fw3->contact_email = $contact_user_obj->getWorkEmail();

							$fw3->kind_of_payer = '941';
							$fw3->kind_of_employer = $fw2->kind_of_employer;
							//$fw3->third_party_sick_pay = TRUE;

							//Use the home state ID if possible.
							if ( isset( $this->form_data['remittance_agency'][$legal_entity_id][$fw2->company_state] )
									&& isset( $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$fw2->company_state]] ) && is_object( $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$fw2->company_state]] ) ) {
								$fw3->state_id1 = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$fw2->company_state]]->getPrimaryIdentification();
							}

							$fw3->lc = $fw2->countRecords();
							$fw3->control_number = ( $fw3->lc + 1 );

							//Use sumRecords()/getRecordsTotal() so all amounts are capped properly.
							$fw2->sumRecords();
							$total_row = $fw2->getRecordsTotal();

							//Debug::Arr($total_row, 'Total Row Data: ', __FILE__, __LINE__, __METHOD__, 10);
							if ( is_array( $total_row ) ) {
								$fw3->l1 = ( isset( $total_row['l1'] ) && $total_row['l1'] != 0 ) ? $total_row['l1'] : null;
								$fw3->l2 = ( isset( $total_row['l2'] ) && $total_row['l2'] != 0 ) ? $total_row['l2'] : null;
								$fw3->l3 = ( isset( $total_row['l3'] ) && $total_row['l3'] != 0 ) ? $total_row['l3'] : null;
								$fw3->l4 = ( isset( $total_row['l4'] ) && $total_row['l4'] != 0 ) ? $total_row['l4'] : null;
								$fw3->l5 = ( isset( $total_row['l5'] ) && $total_row['l5'] != 0 ) ? $total_row['l5'] : null;
								$fw3->l6 = ( isset( $total_row['l6'] ) && $total_row['l6'] != 0 ) ? $total_row['l6'] : null;
								$fw3->l7 = ( isset( $total_row['l7'] ) && $total_row['l7'] != 0 ) ? $total_row['l7'] : null;
								$fw3->l8 = ( isset( $total_row['l8'] ) && $total_row['l8'] != 0 ) ? $total_row['l8'] : null;
								$fw3->l10 = ( isset( $total_row['l10'] ) && $total_row['l10'] != 0 ) ? $total_row['l10'] : null;
								$fw3->l11 = ( isset( $total_row['l11'] ) && $total_row['l11'] != 0 ) ? $total_row['l11'] : null;

								$fw3->l12a = null;
								foreach ( range( 'a', 'z' ) as $z ) { //Make sure we loop over all possible Box 12 codes.
									if ( isset( $total_row['l12'. $z .'_code'] ) && in_array( strtolower( $total_row['l12'. $z .'_code'] ), $l12a_letters ) ) {
										$fw3->l12a = TTMath::add( $fw3->l12a, $total_row['l12'. $z] );
									}
								}

								foreach ( range( 'a', 'z' ) as $z ) {
									//State income tax
									if ( isset( $total_row['l16' . $z] ) ) {
										$fw3->l16 = TTMath::add( $fw3->l16, $total_row['l16' . $z] );
										$fw3->l17 = TTMath::add( $fw3->l17, $total_row['l17' . $z] );
									}
									//District income tax
									if ( isset( $total_row['l18' . $z] ) ) {
										$fw3->l18 = TTMath::add( $fw3->l18, $total_row['l18' . $z] );
										$fw3->l19 = TTMath::add( $fw3->l19, $total_row['l19' . $z] );
									}
								}
							}

							$this->getFormObject()->addForm( $fw3 );
						}
					}

					if ( $format == 'efile' ) {
						$output_format = 'EFILE';
						if ( $fw2->getDebug() == true ) {
							$file_name = $setup_data['form_type'] .'_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.csv';
						} else {
							$file_name = $setup_data['form_type'] .'_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.txt';
						}
						$mime_type = 'applications/octet-stream'; //Force file to download.
					} else if ( $format == 'efile_xml' ) {
						$output_format = 'XML';
						$file_name = $setup_data['form_type'] .'_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.xml';
						$mime_type = 'applications/octet-stream'; //Force file to download.
					} else {
						$output_format = 'PDF';
						$file_name = $this->file_name . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.pdf';
						$mime_type = $this->file_mime_type;
					}

					$output = $this->getFormObject()->output( $output_format );

					$file_arr[] = [ 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output ];

					$this->clearFormObject();
					$this->clearFW2Object();
					$this->clearFW2CObject();
					$this->clearFW3Object();
					$this->clearFW3CObject();
					$this->clearRETURN1040Object();
				}
			}
		}

		if ( $format == 'pdf_form_publish_employee' ) {
			$user_generic_status_batch_id = GovernmentDocumentFactory::saveUserGenericStatus( $current_user->getId() );
			return $user_generic_status_batch_id;
		} else {
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
	}

	/**
	 * Short circuit this function, as no postprocessing is required for exporting the data.
	 * @param null $format
	 * @return bool
	 */
	function _postProcess( $format = null ) {
		if ( ( $format == 'pdf_form' || $format == 'pdf_form_government' ) || ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' ) || $format == 'efile' || $format == 'efile_xml' || $format == 'pdf_form_publish_employee' ) {
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
		if ( ( $format == 'pdf_form' || $format == 'pdf_form_government' ) || ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' ) || $format == 'efile' || $format == 'efile_xml' || $format == 'pdf_form_publish_employee' ) {
			//return $this->_outputPDFForm( 'efile' );
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}
}

?>
