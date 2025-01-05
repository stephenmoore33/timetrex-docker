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
class ExceptionReport extends Report {

	/**
	 * ExceptionReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Exception Summary Report' );
		$this->file_name = 'exception_summary_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_exception_summary', $user_id, $company_id ) ) {
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

					'-3000-exception_policy_type_id'     => TTi18n::gettext( 'Exception' ),
					'-3050-exception_policy_severity_id' => TTi18n::gettext( 'Severity' ),
					'-4000-pay_period_id'                => TTi18n::gettext( 'Pay Period' ),

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
						TTDate::getReportDateOptions( null, TTi18n::gettext( 'Date' ), 16, true )
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
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'ExceptionReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'ExceptionReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'ExceptionReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'ExceptionReport', 'custom_column' );
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
					'-1020-phone_id'  => TTi18n::gettext( 'PIN/Phone ID' ),

					'-1030-employee_number' => TTi18n::gettext( 'Employee #' ),

					'-1040-user_status'        => TTi18n::gettext( 'Employee Status' ),
					'-1050-title'              => TTi18n::gettext( 'Employee Title' ),
					'-1060-province'           => TTi18n::gettext( 'Province/State' ),
					'-1070-country'            => TTi18n::gettext( 'Country' ),
					'-1080-user_group'         => TTi18n::gettext( 'Employee Group' ),
					'-1090-default_branch'     => TTi18n::gettext( 'Branch' ), //abbreviate for space
					'-1100-default_department' => TTi18n::gettext( 'Department' ), //abbreviate for space
					'-1130-permission_group'   => TTi18n::gettext( 'Permission Group' ),

					'-1150-severity'                 => TTi18n::gettext( 'Severity' ),
					'-1160-exception_policy_type'    => TTi18n::gettext( 'Exception' ),
					'-1180-policy_group'             => TTi18n::gettext( 'Policy Group' ),
					'-1190-pay_period_schedule'      => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1170-exception_policy_type_id' => TTi18n::gettext( 'Code' ),
				];

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval['-1120-default_job'] = TTi18n::gettext( 'Job' );
					$retval['-1125-default_job_item'] = TTi18n::gettext( 'Task' );
				}

				$retval = array_merge( $retval, (array)$this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users'], ['users'] ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2040-demerit'         => TTi18n::gettext( 'Demerit Points' ),
					'-2050-total_exception' => TTi18n::gettext( 'Total Exceptions' ),
				];

				$retval = array_merge( $retval, $this->getDynamicCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users'], ['users'] ) );
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
						} else if ( strpos( $column, 'amount' ) !== false ) {
							$retval[$column] = 'time_unit';
						} else if ( strpos( $column, 'total_exception' ) !== false || strpos( $column, 'demerit' ) !== false ) {
							$retval[$column] = 'numeric';
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
								if ( strpos( $column, 'hourly_rate' ) !== false || strpos( $column, 'wage' ) !== false ) {
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
						'-1250-by_severity'                     => TTi18n::gettext( 'Exceptions by Severity' ),
						'-1260-by_exception'                    => TTi18n::gettext( 'Exceptions by Name' ),
						'-1270-by_pay_period'                   => TTi18n::gettext( 'Exceptions by Pay Period' ),
						'-1280-by_employee_by_severity_by_name' => TTi18n::gettext( 'Exceptions by Employee/Severity/Name' ),
						'-1282-by_severity_by_name_by_employee' => TTi18n::gettext( 'Exceptions by Severity/Name/Employee' ),
						'-1300-by_severity_by_name_by_dow'      => TTi18n::gettext( 'Exceptions by Severity/Name/Day of Week' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					$retval['-1010-time_period']['time_period'] = 'last_pay_period';

					switch ( $template ) {
						case 'by_severity':
							$retval['columns'][] = 'severity';

							$retval['columns'][] = 'exception_policy_type_id';
							$retval['columns'][] = 'exception_policy_type';
							$retval['columns'][] = 'demerit';
							$retval['columns'][] = 'total_exception';

							$retval['group'][] = 'severity';
							$retval['group'][] = 'exception_policy_type_id';
							$retval['group'][] = 'exception_policy_type';

							$retval['sub_total'][] = 'severity';

							$retval['sort'][] = [ 'severity' => 'asc', 'total_exception' => 'desc' ];
							break;
						case 'by_exception':
							$retval['columns'][] = 'severity';
							$retval['columns'][] = 'exception_policy_type_id';
							$retval['columns'][] = 'exception_policy_type';
							$retval['columns'][] = 'demerit';
							$retval['columns'][] = 'total_exception';

							$retval['group'][] = 'severity';
							$retval['group'][] = 'exception_policy_type_id';
							$retval['group'][] = 'exception_policy_type';

							$retval['sort'][] = [ 'total_exception' => 'desc', 'severity' => 'asc' ];
							break;
						case 'by_pay_period':
							$retval['columns'][] = 'pay_period';
							$retval['columns'][] = 'severity';

							$retval['columns'][] = 'exception_policy_type_id';
							$retval['columns'][] = 'exception_policy_type';
							$retval['columns'][] = 'demerit';
							$retval['columns'][] = 'total_exception';

							$retval['group'][] = 'pay_period';
							$retval['group'][] = 'severity';
							$retval['group'][] = 'exception_policy_type_id';
							$retval['group'][] = 'exception_policy_type';

							$retval['sub_total'][] = 'pay_period';
							$retval['sub_total'][] = 'severity';

							$retval['sort'][] = [ 'pay_period' => 'desc', 'severity' => 'asc', 'total_exception' => 'desc' ];
							break;
						case 'by_employee_by_severity_by_name':
							$retval['columns'][] = 'full_name';
							$retval['columns'][] = 'severity';

							$retval['columns'][] = 'exception_policy_type_id';
							$retval['columns'][] = 'exception_policy_type';
							$retval['columns'][] = 'demerit';
							$retval['columns'][] = 'total_exception';

							$retval['group'][] = 'full_name';
							$retval['group'][] = 'severity';
							$retval['group'][] = 'exception_policy_type_id';
							$retval['group'][] = 'exception_policy_type';

							$retval['sub_total'][] = 'full_name';
							$retval['sub_total'][] = 'severity';

							$retval['sort'][] = [ 'full_name' => 'asc', 'severity' => 'asc', 'total_exception' => 'desc' ];
							break;
						case 'by_severity_by_name_by_employee':
							$retval['columns'][] = 'severity';
							$retval['columns'][] = 'exception_policy_type_id';
							$retval['columns'][] = 'exception_policy_type';
							$retval['columns'][] = 'full_name';
							$retval['columns'][] = 'demerit';
							$retval['columns'][] = 'total_exception';


							$retval['group'][] = 'severity';
							$retval['group'][] = 'exception_policy_type_id';
							$retval['group'][] = 'exception_policy_type';
							$retval['group'][] = 'full_name';

							$retval['sub_total'][] = 'severity';
							$retval['sub_total'][] = 'exception_policy_type';
							$retval['sub_total'][] = 'full_name';

							$retval['sort'][] = [ 'severity' => 'asc', 'total_exception' => 'desc' ];
							break;
						case 'by_severity_by_name_by_dow':
							$retval['columns'][] = 'severity';
							$retval['columns'][] = 'exception_policy_type_id';
							$retval['columns'][] = 'exception_policy_type';
							$retval['columns'][] = 'date_dow';
							$retval['columns'][] = 'demerit';
							$retval['columns'][] = 'total_exception';

							$retval['group'][] = 'severity';
							$retval['group'][] = 'exception_policy_type_id';
							$retval['group'][] = 'exception_policy_type';
							$retval['group'][] = 'date_dow';

							$retval['sub_total'][] = 'severity';
							$retval['sub_total'][] = 'exception_policy_type';

							$retval['sort'][] = [ 'severity' => 'asc', 'exception_policy_type_id' => 'asc', 'exception_policy_type' => 'asc', 'total_exception' => 'desc' ];
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
				'user'      => [],
				'exception' => [],
		];

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $columns );
			$this->tmp_data['user'][$u_obj->getId()]['user_status'] = Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions( 'status' ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'TMP User Data: ', __FILE__, __LINE__, __METHOD__, 10);
		$columns['pay_period_start_date'] = $columns['pay_period_end_date'] = $columns['pay_period_transaction_date'] = true;

		$filter_data['type_id'] = [ 50 ]; //Exclude pre-mature exceptions.

		//Get Exception data for joining .
		$elf = TTnew( 'ExceptionListFactory' ); /** @var ExceptionListFactory $elf */
		$elf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Exception Rows: ' . $elf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $elf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $elf as $key => $e_obj ) {
			$user_id = $e_obj->getUser();
			$this->tmp_data['exception'][$user_id][$e_obj->getID()] = (array)$e_obj->getObjectAsArray( array_merge( [ 'date_stamp' => true ], $columns ) );
			$this->tmp_data['exception'][$user_id][$e_obj->getID()]['demerit'] = $e_obj->getColumn( 'demerit' );
			$this->tmp_data['exception'][$user_id][$e_obj->getID()]['total_exception'] = 1;
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data['exception'], 'TMP Exception Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['exception'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );
		if ( isset( $this->tmp_data['user'] ) ) {
			$key = 0;
			if ( isset( $this->tmp_data['exception'] ) ) {
				foreach ( $this->tmp_data['exception'] as $user_id => $level_1 ) {
					foreach ( $level_1 as $level_2 ) {
						$date_columns = TTDate::getReportDates( null, TTDate::parseDateTime( $level_2['date_stamp'] ), false, $this->getUserObject(), [ 'pay_period_start_date' => strtotime( $level_2['pay_period_start_date'] ), 'pay_period_end_date' => strtotime( $level_2['pay_period_end_date'] ), 'pay_period_transaction_date' => strtotime( $level_2['pay_period_transaction_date'] ) ] );

						if ( isset( $this->tmp_data['user'][$user_id] ) ) {
							$this->data[] = array_merge( $level_2, $this->tmp_data['user'][$user_id], $date_columns );
						}

						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
						$key++;
					}
				}
			}
			unset( $this->tmp_data, $level_1 );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}
}

?>
