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
class ActiveShiftReport extends Report {

	/**
	 * ActiveShiftReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Whos In Summary' );
		$this->file_name = 'whos_in_summary';

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
				&& $this->getPermissionObject()->Check( 'report', 'view_active_shift', $user_id, $company_id ) ) {
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
						//'time_period',
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

					'-5000-columns'    => TTi18n::gettext( 'Display Columns' ),
					'-5010-group'      => TTi18n::gettext( 'Group By' ),
					'-5020-sub_total'  => TTi18n::gettext( 'SubTotal By' ),
					'-5030-sort'       => TTi18n::gettext( 'Sort By' ),
					'-5040-page_break' => TTi18n::gettext( 'Page Break On' ),
				];

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$professional_edition_setup_fields = [
							'-2510-job_status_id'  => TTi18n::gettext( 'Job Status' ),
							'-2520-job_group_id'   => TTi18n::gettext( 'Job Group' ),
							'-2530-include_job_id' => TTi18n::gettext( 'Include Job' ),
							'-2540-exclude_job_id' => TTi18n::gettext( 'Exclude Job' ),

							'-2610-job_item_group_id'   => TTi18n::gettext( 'Task Group' ),
							'-2620-include_job_item_id' => TTi18n::gettext( 'Include Task' ),
							'-2630-exclude_job_item_id' => TTi18n::gettext( 'Exclude Task' ),
					];
					$retval = array_merge( $retval, $professional_edition_setup_fields );
				}

				break;
			case 'time_period':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				/*
				$retval = array_merge(
									TTDate::getReportDateOptions( 'time_stamp', TTi18n::getText('Punch Time'), 19, FALSE ),
									array()
								);
				*/
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
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'ActiveShiftReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'ActiveShiftReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'ActiveShiftReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'ActiveShiftReport', 'custom_column' );
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

					'-1050-title'              => TTi18n::gettext( 'Title' ),
					//'-1060-province' => TTi18n::gettext('Province/State'),
					//'-1070-country' => TTi18n::gettext('Country'),
					'-1080-user_group'         => TTi18n::gettext( 'Group' ),
					'-1090-default_branch'     => TTi18n::gettext( 'Branch' ), //abbreviate for space
					'-1100-default_department' => TTi18n::gettext( 'Department' ), //abbreviate for space
					'-1110-currency'           => TTi18n::gettext( 'Currency' ),

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
					//'-1450-note' => TTi18n::gettext('Note'), //Employee note.

					'-1495-tag' => TTi18n::gettext( 'Tags' ),

					'-1740-time_zone_display' => TTi18n::gettext( 'Time Zone' ),

					'-1801-type'                => TTi18n::gettext( 'Type' ),
					'-1802-status'              => TTi18n::gettext( 'Status' ),
					'-1810-branch'              => TTi18n::gettext( 'Branch' ),
					'-1820-department'          => TTi18n::gettext( 'Department' ),
					'-1830-station_type'        => TTi18n::gettext( 'Station Type' ),
					'-1840-station_station_id'  => TTi18n::gettext( 'Station ID' ),
					'-1850-station_source'      => TTi18n::gettext( 'Station Source' ),
					'-1860-station_description' => TTi18n::gettext( 'Station Description' ),

					'-1900-time_stamp'        => TTi18n::gettext( 'Punch Time' ),
					'-1910-actual_time_stamp' => TTi18n::gettext( 'Actual Punch Time' ),
					'-2010-punch_note'        => TTi18n::gettext( 'Punch Note' ), //Punch note rather than employee note.
				];

				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$professional_edition_static_columns = [
						//Static Columns - Aggregate functions can't be used on these.
						'-1825-job'      => TTi18n::gettext( 'Job' ),
						'-1826-job_item' => TTi18n::gettext( 'Task' ),
					];
					$retval = array_merge( $retval, $professional_edition_static_columns, (array)$this->getOptions( 'report_static_custom_column' ) );
				}

				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), ['users'], ['users'] ) );
				break;
			case 'dynamic_columns':
				$retval = [
						'-2000-total_user' => TTi18n::gettext( 'Total Employees' ), //Group counter...
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
						} else if ( strpos( $column, 'total_user' ) !== false ) {
							$retval[$column] = 'numeric';
						}
					}
				}
				$retval['time_stamp'] = $retval['actual_time_stamp'] = 'time_stamp';
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
						'-1010-by_status_by_employee'         => TTi18n::gettext( 'Punches by Status' ),
						'-1020-by_type_by_employee'           => TTi18n::gettext( 'Punches by Type' ),
						'-1030-by_status_by_type_by_employee' => TTi18n::gettext( 'Punches by Status/Type' ),
						'-1040-by_type_by_status_by_employee' => TTi18n::gettext( 'Punches by Type/Status' ),

						'-1050-by_employee' => TTi18n::gettext( 'Punches by Employee' ),

						'-1060-by_default_branch_by_employee'                       => TTi18n::gettext( 'Punches by Default Branch' ),
						'-1070-by_default_department_by_employee'                   => TTi18n::gettext( 'Punches by Default Department' ),
						'-1080-by_default_branch_by_default_department_by_employee' => TTi18n::gettext( 'Punches by Default Branch/Department' ),

						'-1090-by_branch_by_employee'               => TTi18n::gettext( 'Punches by Branch' ),
						'-1100-by_department_by_employee'           => TTi18n::gettext( 'Punches by Department' ),
						'-1110-by_branch_by_department_by_employee' => TTi18n::gettext( 'Punches by Branch/Department' ),

						'-1120-by_station_by_employee'      => TTi18n::gettext( 'Punches by Station' ),
						'-1130-by_station_type_by_employee' => TTi18n::gettext( 'Punches by Station Type' ),

						//'-1230-by_branch+total_user' => TTi18n::gettext('Total Employees By Branch'),
				];
				if ( $this->getUserObject()->getCompanyObject()->getProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$professional_edition_setup_fields = [
							'-1112-by_job_by_job_item_by_employee' => TTi18n::gettext( 'Punches by Job/Task' ),
					];

					$retval = array_merge( $retval, $professional_edition_setup_fields );
				}

				break;
			case 'template_config':
				$retval['-1010-time_period']['time_period'] = 'last_24_hours'; //Default to last 24hrs to speed up query as well as handle shifts that span midnight. This used to be 'today', but that would fail for shifts spanning midnight.
				$retval['filter']['user_status_id'] = [ 10 ];                  //Only show active employees.

				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						case 'by_employee':
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;

						case 'by_default_branch_by_employee':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_default_department_by_employee':
							$retval['columns'][] = 'default_department';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_default_branch_by_default_department_by_employee':
							$retval['columns'][] = 'default_branch';
							$retval['columns'][] = 'default_department';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'default_branch' => 'asc' ];
							$retval['sort'][] = [ 'default_department' => 'asc' ];
							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;


						case 'by_branch_by_employee':
							$retval['columns'][] = 'branch';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'branch' => 'asc' ];
							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_department_by_employee':
							$retval['columns'][] = 'department';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'department' => 'asc' ];
							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_branch_by_department_by_employee':
							$retval['columns'][] = 'branch';
							$retval['columns'][] = 'department';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'branch' => 'asc' ];
							$retval['sort'][] = [ 'department' => 'asc' ];
							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_job_by_job_item_by_employee':
							$retval['columns'][] = 'job';
							$retval['columns'][] = 'job_item';
							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'job' => 'asc' ];
							$retval['sort'][] = [ 'job_item' => 'asc' ];
							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_status_by_employee':
							$retval['columns'][] = 'status';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_type_by_employee':
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'type' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_status_by_type_by_employee':
							$retval['columns'][] = 'status';
							$retval['columns'][] = 'type';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'type' => 'desc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_type_by_status_by_employee':
							$retval['columns'][] = 'type';
							$retval['columns'][] = 'status';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'type' => 'desc' ];
							$retval['sort'][] = [ 'status' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;

						case 'by_station_by_employee':
							$retval['columns'][] = 'station_description';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'status';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'station_description' => 'asc' ];
							$retval['sort'][] = [ 'last_name' => 'asc' ];
							$retval['sort'][] = [ 'first_name' => 'asc' ];
							break;
						case 'by_station_type_by_employee':
							$retval['columns'][] = 'station_type';

							$retval['columns'][] = 'first_name';
							$retval['columns'][] = 'last_name';

							$retval['columns'][] = 'type';
							$retval['columns'][] = 'status';
							$retval['columns'][] = 'time_stamp';

							$retval['sort'][] = [ 'station_type' => 'asc' ];
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
		$this->tmp_data = [ 'user' => [], 'user_preference' => [], 'punch' => [], 'total_user' => [] ];

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		//
		//FIXME: Figure out way to only show users with punches if they specify that. Perhaps some sort of array intersect?
		//

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->getObjectAsArray( $columns );
			unset( $this->tmp_data['user'][$u_obj->getId()]['status'], $this->tmp_data['user'][$u_obj->getId()]['status_id'] ); //Status field conflicts with punch status.

			$this->tmp_data['user_preference'][$u_obj->getId()] = [];

			$this->tmp_data['user'][$u_obj->getId()]['total_user'] = 1;

			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}


		//Get user preference data for joining.
		$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
		$uplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Preference Rows: ' . $uplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $uplf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $uplf as $key => $up_obj ) {
			$this->tmp_data['user_preference'][$up_obj->getUser()] = (array)$up_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user_preference'], 'TMP Data: ', __FILE__, __LINE__, __METHOD__, 10);

		$filter_data['punch_end_date'] = time(); //Ignore any punches in the future. So if an employee is on auto-punch and already punched out for the day, it won't show them as punched out until the wall-clock passes their out time.

		//Get last punch (active shift) data for joining with users. That way we can full data from both tables.
		$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
		$plf->getAPIActiveShiftReportByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Active Shift Rows: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $plf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $plf as $key => $p_obj ) {
			$this->tmp_data['punch'][$p_obj->getUser()] = (array)$p_obj->getObjectAsArray( $this->getColumnDataConfig() );
			$this->tmp_data['punch'][$p_obj->getUser()]['time_stamp'] = $p_obj->getTimeStamp();
			$this->tmp_data['punch'][$p_obj->getUser()]['_status_id'] = $p_obj->getStatus();         //Always pass status_id so we can base BG color on that, for example in the Whos In/Out dashlet.
			$this->tmp_data['punch'][$p_obj->getUser()]['punch_note'] = $p_obj->getColumn( 'note' ); //Always pass status_id so we can base BG color on that, for example in the Whos In/Out dashlet.
			if ( $p_obj->getStatus() == 10 ) {
				$this->tmp_data['punch'][$p_obj->getUser()]['_bgcolor'] = [ 225, 255, 225 ];
				//$this->tmp_data['punch'][$p_obj->getUser()]['_fontcolor'] = array(25, 225, 25); //Green
			} else {
				$this->tmp_data['punch'][$p_obj->getUser()]['_bgcolor'] = [ 255, 225, 225 ];
				//$this->tmp_data['punch'][$p_obj->getUser()]['_fontcolor'] = array(225, 25, 25); //Red
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data['punch'], 'TMP Data (punch): ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['punch'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );

		//Use the punch data is the primary dataset and merge user/user preference data to it. This will make it
		//so the report only shows employees with punches within the time period specified.
		//If the user wants to see more employees they can increase the time period to "All".
		$key = 0;
		if ( isset( $this->tmp_data['punch'] ) ) {
			foreach ( $this->tmp_data['punch'] as $user_id => $row ) {
				$processed_data = [];
				if ( isset( $this->tmp_data['user_preference'][$user_id] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user_preference'][$user_id] );
				}
				if ( isset( $this->tmp_data['user'][$user_id] ) ) {
					$processed_data = array_merge( $processed_data, $this->tmp_data['user'][$user_id] );
				} else {
					continue; //Skip user if their data can't be found, otherwise filtering by user criteria does nothing.
				}

				$this->data[] = array_merge( $row, $processed_data );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				$key++;
			}
			unset( $this->tmp_data, $row, $user_id, $processed_data );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}
}

?>
