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
class AuditTrailReport extends Report {

	/**
	 * AuditTrailReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Audit Trail Report' );
		$this->file_name = 'audit_trail_report';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_system_log', $user_id, $company_id ) ) {
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
					'-2100-custom_filter'         => TTi18n::gettext( 'Custom Filter' ),

					//'-3500-pay_period_id' => TTi18n::gettext('Pay Period'),
					'-3600-log_action_id'         => TTi18n::gettext( 'Action' ),
					'-3700-log_table_name_id'     => TTi18n::gettext( 'Object' ),

					//'-4020-include_no_data_rows' => TTi18n::gettext('Include Blank Records'),

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
				/*$retval = array_merge(
									TTDate::getReportDateOptions( 'start', TTi18n::getText('Start Date'), 16, FALSE ),
									TTDate::getReportDateOptions( 'end', TTi18n::getText('End Date'), 17, FALSE )
								);*/
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'AuditTrailReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'AuditTrailReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'AuditTrailReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'AuditTrailReport', 'custom_column' );
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

					'-1040-user_status'        => TTi18n::gettext( 'Employee Status' ),
					'-1050-title'              => TTi18n::gettext( 'Employee Title' ),
					'-1060-province'           => TTi18n::gettext( 'Province/State' ),
					'-1070-country'            => TTi18n::gettext( 'Country' ),
					'-1080-user_group'         => TTi18n::gettext( 'Employee Group' ),
					'-1090-default_branch'     => TTi18n::gettext( 'Branch' ), //abbreviate for space
					'-1100-default_department' => TTi18n::gettext( 'Department' ), //abbreviate for space

					'-2000-date'        => TTi18n::gettext( 'Date' ),
					'-2100-object'      => TTi18n::gettext( 'Object' ),
					'-2150-action'      => TTi18n::gettext( 'Action' ),
					'-2200-description' => TTi18n::gettext( 'Description' ),

					'-2300-display_field' => TTi18n::gettext( 'Field' ),
					'-2310-old_value' => TTi18n::gettext( 'Before Value' ),
					'-2312-new_value' => TTi18n::gettext( 'After Value' ),
				];

				//$retval = array_merge( $retval, $this->getOptions('date_columns') );
				$retval = array_merge( $retval, (array)$this->getOptions( 'report_static_custom_column' ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-2500-total_log' => TTi18n::gettext( 'Total' ), //Group counter...
				];

				break;
			case 'columns':
				//$retval = array_merge( $this->getOptions('static_columns'), $this->getOptions('dynamic_columns') );
				$retval = array_merge( $this->getOptions( 'static_columns' ), $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) );
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

				$retval['date'] = 'time_stamp';
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
						'-1200-by_date+audit'                               => TTi18n::gettext( 'Audit by Date' ),
						'-1210-by_employee+audit'                           => TTi18n::gettext( 'Audit by Employee' ),
						'-1220-by_object+audit'                             => TTi18n::gettext( 'Audit by Object' ),
						'-1230-by_action+audit'                             => TTi18n::gettext( 'Audit by Action' ),
						'-1240-by_object_by_action_by_employee+audit_total' => TTi18n::gettext( 'Audit Records by Object/Action/Employee' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					$retval['-1010-time_period']['time_period'] = 'last_7_days'; //Always default to the last 7 days to keep the report small and fast.

					switch ( $template ) {
						case 'by_date+audit':
							$retval['columns'][] = 'date';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'object';
							$retval['columns'][] = 'action';
							$retval['columns'][] = 'description';

							$retval['sort'][] = [ 'date' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							$retval['sort'][] = [ 'object' => 'asc' ];
							$retval['sort'][] = [ 'action' => 'asc' ];
							break;
						case 'by_employee+audit':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'date';
							$retval['columns'][] = 'object';
							$retval['columns'][] = 'action';
							$retval['columns'][] = 'description';

							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							$retval['sort'][] = [ 'date' => 'desc' ];
							$retval['sort'][] = [ 'object' => 'asc' ];
							$retval['sort'][] = [ 'action' => 'asc' ];
							break;
						case 'by_object+audit':
							$retval['columns'][] = 'object';

							$retval['columns'][] = 'date';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'action';
							$retval['columns'][] = 'description';

							$retval['sort'][] = [ 'object' => 'asc' ];
							$retval['sort'][] = [ 'date' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							$retval['sort'][] = [ 'action' => 'asc' ];

							break;
						case 'by_action+audit':
							$retval['columns'][] = 'action';
							$retval['columns'][] = 'date';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'object';
							$retval['columns'][] = 'description';

							$retval['sort'][] = [ 'action' => 'asc' ];
							$retval['sort'][] = [ 'date' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							$retval['sort'][] = [ 'object' => 'asc' ];

							//$retval['filter']['-1050-log_action_id'] = array();
							break;
						case 'by_object_by_action_by_employee+audit_total':
							$retval['columns'][] = 'object';
							$retval['columns'][] = 'action';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';
							$retval['columns'][] = 'total_log';

							$retval['group'][] = 'object';
							$retval['group'][] = 'action';
							$retval['group'][] = 'first_name';
							$retval['group'][] = 'last_name';

							$retval['sub_total'][] = 'object';
							$retval['sub_total'][] = 'action';

							$retval['sort'][] = [ 'object' => 'asc' ];
							$retval['sort'][] = [ 'action' => 'asc' ];
							$retval['sort'][] = [ 'total_log' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
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
				'user' => [],
				'log'  => [],
		];

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		if ( isset( $columns['display_field'] ) || isset( $columns['old_value'] ) || isset( $columns['new_value'] ) ) {
			$include_details = true;
		} else {
			$include_details = false;
		}

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

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

		//Get system log data for joining.
		if ( count( $this->tmp_data['user'] ) > 0 ) {
			$filter_data['user_id'] = array_keys( $this->tmp_data['user'] ); //Filter only selected users, otherwise too many rows can be returned that wont be displayed.

			$llf = TTnew( 'LogListFactory' ); /** @var LogListFactory $llf */

			$this->setMemoryPerRow( ( 1024 * 4 ) );  //3464 bytes per row
			if ( $include_details == true ) { //Switch to a more complex query that includes details.
				$llf->getAPISearchWithDetailsByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, $this->getMemoryBasedRowLimit() );
			} else {
				$llf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data, $this->getMemoryBasedRowLimit() );
			}

			if ( $llf->getRecordCount() > 0 ) {
				if ( $this->isMemoryBasedRowLimitValid( $llf->getRecordCount() ) == false ) {
					$this->setMaximumRowsExceeded( true ); //Alert the user that the maximum number of rows was exceeded, but still generate the report with the data we have.
					if ( $this->handleMaximumRowsExceeded( $format ) == false ) {
						return false;
					}
				}

				Debug::Text( ' Log Rows: ' . $llf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->getProgressBarObject()->start( $this->getAPIMessageID(), $llf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
				foreach ( $llf as $key => $l_obj ) {
					$tmp_user_data = array_merge( (array)$l_obj->getObjectAsArray( $columns ), [ 'total_log' => 1 ] );
					if ( isset( $tmp_user_data['date'] ) ) {
						$tmp_user_data['date'] = TTDate::parseDateTime( $tmp_user_data['date'] ); //Convert date to a epoch so we can sort by it.
					}

					$this->tmp_data['log'][$l_obj->getUser()][] = $tmp_user_data;

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				}
			}
			//Debug::Arr($this->tmp_data['log'], 'TMP Log Data: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['log'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );
		if ( isset( $this->tmp_data['user'] ) ) {
			$key = 0;
			if ( isset( $this->tmp_data['log'] ) ) {
				foreach ( $this->tmp_data['log'] as $user_id => $level_2 ) {
					if ( isset( $this->tmp_data['user'][$user_id] ) ) {
						foreach ( $level_2 as $row ) {
							$this->data[] = array_merge( $row, $this->tmp_data['user'][$user_id] );
						}
					}

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
					$key++;
				}
			}
			unset( $this->tmp_data, $row );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}
}

?>
