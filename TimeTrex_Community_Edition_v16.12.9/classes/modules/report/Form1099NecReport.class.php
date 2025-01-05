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
class Form1099NecReport extends Report {

	protected $user_ids = [];

	/**
	 * Form1099NecReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Form 1099-NEC Report' );
		$this->file_name = 'form_1099nec';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_form1099nec', $user_id, $company_id ) ) {
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
											   //'-1120-efile' => TTi18n::gettext('eFile'),
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
				//$retval = TTDate::getReportDateOptions( NULL, TTi18n::getText('Date'), 13, TRUE );
				$retval = [];
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'Form1099NecReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'Form1099NecReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'Form1099NecReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'Form1099NecReport', 'custom_column' );
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
				];

				$retval = array_merge( $retval, $this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2060-l1' => TTi18n::gettext( 'Nonemployee Compensation (1)' ),
					'-2020-l4' => TTi18n::gettext( 'Federal Income Tax (4)' ),
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
	function getF1099NECObject() {
		if ( !isset( $this->form_obj['f1099nec'] ) || !is_object( $this->form_obj['f1099nec'] ) ) {
			$this->form_obj['f1099nec'] = $this->getFormObject()->getFormObject( '1099nec', 'US' );

			return $this->form_obj['f1099nec'];
		}

		return $this->form_obj['f1099nec'];
	}

	/**
	 * @return bool
	 */
	function clearF1099NECObject() {
		$this->form_obj['f1099nec'] = false;

		return true;
	}

	/**
	 * @return mixed
	 */
	function getF1096Object() {
		if ( !isset( $this->form_obj['f1096'] ) || !is_object( $this->form_obj['f1096'] ) ) {
			$this->form_obj['f1096'] = $this->getFormObject()->getFormObject( '1096', 'US' );

			return $this->form_obj['f1096'];
		}

		return $this->form_obj['f1096'];
	}

	/**
	 * @return bool
	 */
	function clearF1096Object() {
		$this->form_obj['f1096'] = false;

		return true;
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
				'l1' => $default_include_exclude_arr,
				'l4' => $default_include_exclude_arr,
		];

		$retarr = array_merge( $default_arr, (array)$this->getFormConfig() );

		return $retarr;
	}

	/**
	 * Get raw data for report
	 * @param null $format
	 * @return bool
	 */
	function _getData( $format = null ) {
		$this->tmp_data = [ 'pay_stub_entry' => [], 'remittancy_agency' => [] ];

		$filter_data = $this->getFilterConfig();
		$form_data = $this->formatFormConfig();
		$tax_deductions = [];
		$user_deduction_data = [];
		//$tax_deduction_pay_stub_account_id_map = [];

		//
		//Figure out state/locality wages/taxes.
		//
		$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
		$cdlf->getByCompanyIdAndStatusIdAndTypeId( $this->getUserObject()->getCompany(), [ 10, 20 ], 10 );
		if ( $cdlf->getRecordCount() > 0 ) {
			foreach ( $cdlf as $cd_obj ) { /** @var CompanyDeductionFactory $cd_obj */
				$tax_deductions[$cd_obj->getId()] = $cd_obj;

				//Need to determine start/end dates for each CompanyDeduction/User pair, so we can break down total wages earned in the date ranges.
				$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
				$udlf->getByCompanyIdAndCompanyDeductionId( $cd_obj->getCompany(), $cd_obj->getId() );
				if ( $udlf->getRecordCount() > 0 ) {
					foreach ( $udlf as $ud_obj ) {
						//if ( $ud_obj->getStartDate() != '' || $ud_obj->getEndDate() != '' ) { //Always include UserDeduction map so we know if a user isn't assigned to it all.
							//Debug::Text('  User Deduction: ID: '. $ud_obj->getID() .' User ID: '. $ud_obj->getUser(), __FILE__, __LINE__, __METHOD__, 10);
							$user_deduction_data[$ud_obj->getCompanyDeduction()][$ud_obj->getUser()] = $ud_obj;
						//}
					}
				}
			}
			//Debug::Arr($tax_deductions, 'Tax Deductions: ', __FILE__, __LINE__, __METHOD__, 10);
		} else {
			Debug::Text( 'No Tax Deductions: ', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
		$pself->getAPIReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $pself->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		if ( $pself->getRecordCount() > 0 ) {
			foreach ( $pself as $key => $pse_obj ) {
				$legal_entity_id = $pse_obj->getColumn( 'legal_entity_id' );
				$user_id = $this->user_ids[] = $pse_obj->getColumn( 'user_id' );
				$date_stamp = TTDate::strtotime( $pse_obj->getColumn( 'pay_stub_end_date' ) );
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
				foreach ( $this->tmp_data['pay_stub_entry'] as $user_id => $data_a ) {
					foreach ( $data_a as $date_stamp => $data_b ) {
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l1'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l1']['include_pay_stub_entry_account'], $form_data['l1']['exclude_pay_stub_entry_account'] );
						$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l4'] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $form_data['l4']['include_pay_stub_entry_account'], $form_data['l4']['exclude_pay_stub_entry_account'] );

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
										Debug::Text( 'Found User ID: ' . $user_id . ' in Tax Deduction Name: ' . $cd_obj->getName() . '(' . $cd_obj->getId() . ') Calculation ID: ' . $cd_obj->getCalculation() . ' Withheld Amount: ' . $tax_withheld_amount, __FILE__, __LINE__, __METHOD__, 10 );

										$is_active_date = false;
										if ( isset( $user_deduction_data ) && isset( $user_deduction_data[$tax_deduction_id] ) && isset( $user_deduction_data[$tax_deduction_id][$user_id] ) ) {
											$is_active_date = $cdlf->isActiveDate( $user_deduction_data[$tax_deduction_id][$user_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_start_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_stub_transaction_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_start_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_transaction_date'] );
											Debug::Text( '  Date Restrictions Found... Is Active: ' . (int)$is_active_date . ' Date: ' . TTDate::getDate( 'DATE', $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['pay_period_end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
										}

										//State records must come before district, so they can be matched up.
										if ( $cd_obj->getCalculation() == 200 && $cd_obj->getProvince() != '' ) {
											//determine how many district/states currently exist for this employee.
											foreach ( range( 'a', 'z' ) as $z ) {
												//Make sure we are able to combine multiple state Tax/Deduction amounts together in the case
												//where they are using different Pay Stub Accounts for the State Income Tax and State Addl. Income Tax PSA's.
												//Need to have per user state detection vs per user/date, so we can make sure the state_id is unique across all possible data.
												if ( !( isset( $this->tmp_data['state_ids'][$user_id]['l5' . $z] ) && isset( $this->tmp_data['state_ids'][$user_id]['l6' . $z . '_state'] ) && $this->tmp_data['state_ids'][$user_id]['l6' . $z . '_state'] != $cd_obj->getProvince() ) ) {
													$state_id = $z;
													break;
												}
											}

											//State Wages/Taxes
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l6' . $state_id . '_state'] = $this->tmp_data['state_ids'][$user_id]['l6' . $state_id . '_state'] = $cd_obj->getProvince();

											if ( $is_active_date == true ) {
												if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l7' . $state_id] ) || ( isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l7' . $state_id] ) && $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l7' . $state_id] == 0 ) ) {
													$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l7' . $state_id] = Misc::calculateMultipleColumns( $data_b['psen_ids'], $cd_obj->getIncludePayStubEntryAccount(), $cd_obj->getExcludePayStubEntryAccount() );
												}
											}
											if ( !isset( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5' . $state_id] ) ) {
												$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5' . $state_id] = $this->tmp_data['state_ids'][$user_id]['l5' . $state_id] = 0;
											}
											//Just combine the tax withheld part, not the wages/earnings, as we don't want to double up on that.
											$this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5' . $state_id] = TTMath::add( $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5' . $state_id], Misc::calculateMultipleColumns( $data_b['psen_ids'], [ $cd_obj->getPayStubEntryAccount() ] ) );
											$this->tmp_data['state_ids'][$user_id]['l5' . $state_id] = TTMath::add( $this->tmp_data['state_ids'][$user_id]['l5' . $state_id], $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5' . $state_id] );

											//Debug::Text('State ID: '. $state_id .' Withheld: '. $this->tmp_data['pay_stub_entry'][$user_id][$date_stamp]['l5'. $state_id], __FILE__, __LINE__, __METHOD__, 10);

											Debug::Text( '  Not State or Local income tax: ' . $cd_obj->getId() . ' Calculation: ' . $cd_obj->getCalculation() . ' District: ' . $cd_obj->getDistrictName() . ' UserValue5: ' . $cd_obj->getUserValue5() . ' CompanyValue1: ' . $cd_obj->getCompanyValue1(), __FILE__, __LINE__, __METHOD__, 10 );
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

		$this->user_ids = array_unique( $this->user_ids ); //Used to get the total number of employees.

		//Debug::Arr($this->tmp_data['user'], 'User Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->user_ids, 'User IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($this->tmp_data, 'Tmp Raw Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Total Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->tmp_data['user'][$u_obj->getId()]['user_id'] = $u_obj->getId();
			$this->tmp_data['user'][$u_obj->getId()]['legal_entity_id'] = $u_obj->getLegalEntity();
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

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
		$filter_data['type_id'] = [ 10, 20 ];              //federal, state
		$filter_data['country'] = [ 'US' ];                //US federal
		$ralf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $ralf */
		$ralf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Remittance Agency Total Rows: ' . $ralf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $lelf->getRecordCount(), null, TTi18n::getText( 'Retrieving Remittance Agency Data...' ) );
		if ( $ralf->getRecordCount() > 0 ) {
			foreach ( $ralf as $key => $ra_obj ) {
				if ( $ra_obj->parseAgencyID( null, 'id' ) == 10 ) {
					if ( in_array( $ra_obj->getType(), [ 10, 20 ] ) ) {
						$province_id = ( $ra_obj->getType() == 10 ) ? '00' : $ra_obj->getProvince();
						$this->form_data['remittance_agency'][$ra_obj->getLegalEntity()][$province_id] = $ra_obj->getId(); //Map province to a specific remittance object below.
					}

					$this->form_data['remittance_agency_obj'][$ra_obj->getId()] = $ra_obj;
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
						} else if ( is_numeric( $value ) && preg_match( '/^l[0-9]{1,2}[a-z]?$/i', $key ) == true ) { //Dynamic keys.
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
		if ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' ) {
			$show_background = false;
		}
		Debug::Text( 'Generating Form... Format: ' . $format, __FILE__, __LINE__, __METHOD__, 10 );

		$current_user = $this->getUserObject();
		//$setup_data = $this->getFormConfig();
		$filter_data = $this->getFilterConfig();
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( stristr( $format, 'government' ) ) {
			$form_type = 'government';
		} else {
			$form_type = 'employee';
		}
		Debug::Text( 'Form Type: ' . $form_type, __FILE__, __LINE__, __METHOD__, 10 );

		if ( isset( $this->form_data['user'] ) && is_array( $this->form_data['user'] ) ) {
			$this->sortFormData(); //Make sure forms are sorted.

			foreach ( $this->form_data['user'] as $legal_entity_id => $user_rows ) {
				//$total_row = array();

				if ( isset( $this->form_data['legal_entity'][$legal_entity_id] ) == false ) {
					Debug::Text( 'Missing Legal Entity: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id] ) == false ) {
					Debug::Text( 'Missing Remittance Agency: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
				}

				if ( isset( $this->form_data['remittance_agency'][$legal_entity_id]['00'] ) == false ) {
					Debug::Text( 'Missing Federal Remittance Agency: ' . $legal_entity_id, __FILE__, __LINE__, __METHOD__, 10 );
					continue;
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

				$f1099nec = $this->getF1099NECObject();
				$f1099nec->setDebug( false );
				$f1099nec->setShowBackground( $show_background );

				$f1099nec->setType( $form_type );
				$f1099nec->year = TTDate::getYear( $filter_data['start_date'] );

				$f1099nec->name = $legal_entity_obj->getLegalName();
				$f1099nec->trade_name = $legal_entity_obj->getTradeName();
				$f1099nec->company_address1 = $legal_entity_obj->getAddress1() . ' ' . $legal_entity_obj->getAddress2();
				$f1099nec->company_city = $legal_entity_obj->getCity();
				$f1099nec->company_state = $legal_entity_obj->getProvince();
				$f1099nec->company_zip_code = $legal_entity_obj->getPostalCode();
				$f1099nec->company_phone = $legal_entity_obj->getWorkPhone();
				$f1099nec->payer_id = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id]['00']]->getPrimaryIdentification(); //Always use EIN from Federal Agency.

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
									'id'                  => (string)TTUUID::convertStringToUUID( md5( $user_id . $f1099nec->year . microtime( true ) ) ),
									'user_id'             => (string)$user_id,
									'control_number'      => $i + 1,
									'first_name'          => $user_obj->getFirstName(),
									'middle_name'         => $user_obj->getMiddleName(),
									'last_name'           => $user_obj->getLastName(),
									'address1'            => $user_obj->getAddress1(),
									'address2'            => $user_obj->getAddress2(),
									'city'                => $user_obj->getCity(),
									'state'               => $user_obj->getProvince(),
									'employment_province' => $user_obj->getProvince(),
									'zip_code'            => $user_obj->getPostalCode(),
									'recipient_id'        => $user_obj->getSIN(),
									'employee_number'     => $user_obj->getEmployeeNumber(),
									'l1'                  => $row['l1'],
									'l4'                  => $row['l4'],
							];

							foreach ( range( 'a', 'z' ) as $z ) {
								//Make sure state information is included if its just local income taxes.
								if ( ( ( isset( $row['l5' . $z] ) && $row['l5' . $z] > 0 ) || ( isset( $row['l7' . $z] ) && $row['l7' . $z] > 0 ) )
										&& ( isset( $row['l6' . $z . '_state'] )
												&& isset( $this->form_data['remittance_agency'][$legal_entity_id][$row['l6' . $z . '_state']] )
												&& isset( $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$row['l6' . $z . '_state']]] )
												&& $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$row['l6' . $z . '_state']]]->getType() == 20 ) ) {
									$ee_data['l6' . $z . '_state_id'] = $this->form_data['remittance_agency_obj'][$this->form_data['remittance_agency'][$legal_entity_id][$row['l6' . $z . '_state']]]->getPrimaryIdentification();
									$ee_data['l6' . $z] = $row['l6' . $z . '_state'];
									if ( isset( $ee_data['l6' . $z . '_state_id'] ) && $ee_data['l6' . $z . '_state_id'] != '' ) {
										$ee_data['l6' . $z] .= ' / ' . $ee_data['l6' . $z . '_state_id'];
									}
								} else {
									$ee_data['l6' . $z . '_state_id'] = null;
									$ee_data['l6' . $z] = null;
								}

								//State income tax
								if ( isset( $row['l7' . $z] ) ) {
									$ee_data['l7' . $z] = $row['l7' . $z];
									$ee_data['l5' . $z] = $row['l5' . $z];
								} else {
									$ee_data['l7' . $z] = null;
									$ee_data['l5' . $z] = null;
								}
							}

							$f1099nec->addRecord( $ee_data );
							unset( $ee_data );

							if ( $format == 'pdf_form_publish_employee' ) {
								// generate PDF for every employee and assign to each government document records
								$this->getFormObject()->addForm( $f1099nec );
								GovernmentDocumentFactory::addDocument( $user_obj->getId(), 20, 221, TTDate::getEndYearEpoch( $filter_data['start_date'] ), ( ( $f1099nec->countRecords() == 1 ) ? $this->getFormObject()->output( 'PDF', false ) : null ), ( ( $f1099nec->countRecords() == 1 ) ? $this->getFormObject()->serialize() : null ) );
								$this->getFormObject()->clearForms();
							}

							$i++;
						}

						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $x );
						$x++;
					}
				}

				if ( $format != 'pdf_form_publish_employee' ) {
					$this->getFormObject()->addForm( $f1099nec );

					if ( $form_type == 'government' ) {
						//Handle 1096 (Summary)
						$f1096 = $this->getF1096Object();
						$f1096->setShowBackground( $show_background );
						$f1096->year = $f1099nec->year;
						$f1096->ein = $f1099nec->payer_id;
						$f1096->name = $f1099nec->name;
						$f1096->trade_name = $f1099nec->trade_name;
						$f1096->company_address1 = $f1099nec->company_address1;
						$f1096->company_address2 = $f1099nec->company_address2;
						$f1096->company_city = $f1099nec->company_city;
						$f1096->company_state = $f1099nec->company_state;
						$f1096->company_zip_code = $f1099nec->company_zip_code;

						$f1096->contact_name = $contact_user_obj->getFullName();
						$f1096->contact_phone = $contact_user_obj->getWorkPhone();
						$f1096->contact_phone_ext = $contact_user_obj->getWorkPhoneExt();
						$f1096->contact_email = ( $contact_user_obj->getWorkEmail() != '' ) ? $contact_user_obj->getWorkEmail() : ( ( $contact_user_obj->getHomeEmail() != '' ) ? $contact_user_obj->getHomeEmail() : null );

						$f1096->l3 = $f1099nec->countRecords();

						//Use sumRecords()/getRecordsTotal() so all amounts are capped properly.
						$f1099nec->sumRecords();
						$total_row = $f1099nec->getRecordsTotal();

						Debug::Arr( $total_row, 'Total Row Data: ', __FILE__, __LINE__, __METHOD__, 10 );
						if ( is_array( $total_row ) ) {
							$f1096->l4 = ( isset( $total_row['l4'] ) && $total_row['l4'] != 0 ) ? $total_row['l4'] : null;
							$f1096->l5 = TTMath::add( $total_row['l1'], $total_row['l4'] ); //Instructions explain which boxes needed to be totaled here.
						}

						$this->getFormObject()->addForm( $f1096 );
					}

					if ( $format == 'efile' ) {
						$output_format = 'EFILE';
						if ( $f1099nec->getDebug() == true ) {
							$file_name = '1099nec_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.csv';
						} else {
							$file_name = '1099nec_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.txt';
						}
						$mime_type = 'applications/octet-stream'; //Force file to download.
					} else if ( $format == 'efile_xml' ) {
						$output_format = 'XML';
						$file_name = '1099nec_efile_' . date( 'Y_m_d' ) . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.xml';
						$mime_type = 'applications/octet-stream'; //Force file to download.
					} else {
						$output_format = 'PDF';
						$file_name = $this->file_name . '_' . Misc::sanitizeFileName( $this->form_data['legal_entity'][$legal_entity_id]->getTradeName() ) . '.pdf';
						$mime_type = $this->file_mime_type;
					}

					$output = $this->getFormObject()->output( $output_format );

					$file_arr[] = [ 'file_name' => $file_name, 'mime_type' => $mime_type, 'data' => $output ];

					$this->clearFormObject();
					$this->clearF1099NECObject();
					$this->clearF1096Object();
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
		if ( ( $format == 'pdf_form' || $format == 'pdf_form_government' ) || ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' ) || $format == 'efile_xml' || $format == 'pdf_form_publish_employee' ) {
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
		if ( ( $format == 'pdf_form' || $format == 'pdf_form_government' ) || ( $format == 'pdf_form_print' || $format == 'pdf_form_print_government' ) || $format == 'efile_xml' || $format == 'pdf_form_publish_employee' ) {
			return $this->_outputPDFForm( $format );
		} else {
			return parent::_output( $format );
		}
	}
}

?>
