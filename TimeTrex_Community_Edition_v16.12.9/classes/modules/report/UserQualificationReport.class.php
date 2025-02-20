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
 * @package Core
 */
class UserQualificationReport extends Report {

	/**
	 * UserQualificationReport constructor.
	 */
	function __construct() {
		$this->title = TTi18n::getText( 'Qualification Summary Report' );
		$this->file_name = 'qualification_summary_report';

		parent::__construct();

		return true;
	}

	/**
	 * @param string $user_id    UUID
	 * @param string $company_id UUID
	 * @return bool
	 */
	protected function _checkPermissions( $user_id, $company_id ) {
		if ( $this->getPermissionObject()->Check( 'hr_report', 'enabled', $user_id, $company_id )
				&& $this->getPermissionObject()->Check( 'hr_report', 'user_qualification', $user_id, $company_id ) ) {
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
						//'time_period',
						'columns',
				];
				break;
			case 'setup_fields':
				$retval = [
					//Static Columns - Aggregate functions can't be used on these.
					'-1000-template'              => TTi18n::gettext( 'Template' ),
					//'-1010-time_period' => TTi18n::gettext('Time Period'),
					//'-2000-user_id' => TTi18n::gettext('Employees'),
					'-2000-legal_entity_id'       => TTi18n::gettext( 'Legal Entity' ),
					'-2010-user_status_id'        => TTi18n::gettext( 'Employee Status' ),
					'-2020-user_group_id'         => TTi18n::gettext( 'Employee Group' ),
					'-2025-policy_group_id'       => TTi18n::gettext( 'Policy Group' ),
					'-2030-user_title_id'         => TTi18n::gettext( 'Employee Title' ),
					//'-2035-user_tag' => TTi18n::gettext('Employee Tags'),
					'-2040-include_user_id'       => TTi18n::gettext( 'Employee Include' ),
					'-2050-exclude_user_id'       => TTi18n::gettext( 'Employee Exclude' ),
					'-2060-default_branch_id'     => TTi18n::gettext( 'Default Branch' ),
					'-2070-default_department_id' => TTi18n::gettext( 'Default Department' ),
					//'-2080-punch_branch_id' => TTi18n::gettext('Punch Branch'),
					//'-2090-punch_department_id' => TTi18n::gettext('Punch Department'),

					'-2080-qualification_group_id'  => TTi18n::gettext( 'Qualification Group' ),
					'-2085-qualification_type_id'   => TTi18n::gettext( 'Qualification Type' ),
					'-2090-qualification_id'        => TTi18n::gettext( 'Qualifications' ),
					'-2100-proficiency_id'          => TTi18n::gettext( 'Skill Proficiency' ),
					'-2140-fluency_id'              => TTi18n::gettext( 'Language Fluency' ),
					'-2150-competency_id'           => TTi18n::gettext( 'Language Competency' ),
					'-2170-ownership_id'            => TTi18n::gettext( 'Membership Ownership' ),
					'-2200-membership_renewal_date' => TTi18n::gettext( 'Membership Renewal Date' ),
					'-2250-skill_expiry_date'       => TTi18n::gettext( 'Skill Expiry Date' ),
					'-2300-license_expiry_date'     => TTi18n::gettext( 'License Expiry Date' ),
					'-2400-education_graduate_date' => TTi18n::gettext( 'Education Graduation Date' ),

					'-3000-custom_filter' => TTi18n::gettext( 'Custom Filter' ),

					'-5000-columns'    => TTi18n::gettext( 'Display Columns' ),
					'-5010-group'      => TTi18n::gettext( 'Group By' ),
					'-5020-sub_total'  => TTi18n::gettext( 'SubTotal By' ),
					'-5030-sort'       => TTi18n::gettext( 'Sort By' ),
					'-5040-page_break' => TTi18n::gettext( 'Page Break On' ),
				];
				break;
			//case 'time_period':
			case 'membership_renewal_date':
			case 'skill_expiry_date':
			case 'license_expiry_date':
			case 'education_graduate_date':
				$retval = TTDate::getTimePeriodOptions();
				break;
			case 'date_columns':
				$retval = array_merge(
						TTDate::getReportDateOptions( 'user.hire', TTi18n::getText( 'Hire Date' ), 15, false ),
						TTDate::getReportDateOptions( 'user.termination', TTi18n::getText( 'Termination Date' ), 16, false ),
						TTDate::getReportDateOptions( 'user.birth', TTi18n::getText( 'Birth Date' ), 17, false )
				);
				break;
			case 'custom_columns':
				//Get custom fields for report data.
				$retval = $this->getCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), [ 'users' ], [ 'users'], [ 'users' => [ 'name' => 'user.' ] ]  );
				break;
			case 'report_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					// Because the Filter type is just only a filter criteria and not need to be as an option of Display Columns, Group By, Sub Total, Sort By dropdowns.
					// So just get custom columns with Selection and Formula.
					$custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), null, 'UserQualificationReport', 'custom_column' );
					if ( is_array( $custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $custom_column_labels, 9500 );
					}
				}
				break;
			case 'report_custom_filters':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$retval = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'filter_column_type_ids' ), null, 'UserQualificationReport', 'custom_column' );
				}
				break;
			case 'report_dynamic_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_dynamic_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'dynamic_format_ids' ), 'UserQualificationReport', 'custom_column' );
					if ( is_array( $report_dynamic_custom_column_labels ) ) {
						$retval = Misc::addSortPrefix( $report_dynamic_custom_column_labels, 9700 );
					}
				}
				break;
			case 'report_static_custom_column':
				if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$rcclf = TTnew( 'ReportCustomColumnListFactory' ); /** @var ReportCustomColumnListFactory $rcclf */
					$report_static_custom_column_labels = $rcclf->getByCompanyIdAndTypeIdAndFormatIdAndScriptArray( $this->getUserObject()->getCompany(), $rcclf->getOptions( 'display_column_type_ids' ), $rcclf->getOptions( 'static_format_ids' ), 'UserQualificationReport', 'custom_column' );
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
					'-1000-user.first_name'  => TTi18n::gettext( 'First Name' ),
					'-1001-user.middle_name' => TTi18n::gettext( 'Middle Name' ),
					'-1002-user.last_name'   => TTi18n::gettext( 'Last Name' ),
					'-1005-user.full_name'   => TTi18n::gettext( 'Full Name' ),

					'-1010-user.user_name' => TTi18n::gettext( 'User Name' ),
					'-1020-user.phone_id'  => TTi18n::gettext( 'PIN/Phone ID' ),

					'-1030-user.employee_number' => TTi18n::gettext( 'Employee #' ),

					'-1040-user.status'             => TTi18n::gettext( 'Employee Status' ),
					'-1050-user.title'              => TTi18n::gettext( 'Title' ),
					'-1060-user.province'           => TTi18n::gettext( 'Province/State' ),
					'-1070-user.country'            => TTi18n::gettext( 'Country' ),
					'-1080-user.user_group'         => TTi18n::gettext( 'Employee Group' ),
					'-1090-user.default_branch'     => TTi18n::gettext( 'Branch' ), //abbreviate for space
					'-1100-user.default_department' => TTi18n::gettext( 'Department' ), //abbreviate for space
					//'-1110-currency' => TTi18n::gettext('Currency'),

					'-1200-user.permission_control'  => TTi18n::gettext( 'Permission Group' ),
					'-1210-user.pay_period_schedule' => TTi18n::gettext( 'Pay Period Schedule' ),
					'-1220-user.policy_group'        => TTi18n::gettext( 'Policy Group' ),

					'-1310-user.sex'      => TTi18n::gettext( 'Gender' ),
					'-1320-user.address1' => TTi18n::gettext( 'Address 1' ),
					'-1330-user.address2' => TTi18n::gettext( 'Address 2' ),

					'-1340-user.city'                      => TTi18n::gettext( 'City' ),
					//'-1350-province' => TTi18n::gettext('Province/State'),
					//'-1360-country' => TTi18n::gettext('Country'),
					'-1370-user.postal_code'               => TTi18n::gettext( 'Postal Code' ),
					'-1380-user.work_phone'                => TTi18n::gettext( 'Work Phone' ),
					'-1391-user.work_phone_ext'            => TTi18n::gettext( 'Work Phone Ext' ),
					'-1400-user.home_phone'                => TTi18n::gettext( 'Home Phone' ),
					'-1410-user.mobile_phone'              => TTi18n::gettext( 'Mobile Phone' ),
					'-1420-user.fax_phone'                 => TTi18n::gettext( 'Fax Phone' ),
					'-1430-user.home_email'                => TTi18n::gettext( 'Home Email' ),
					'-1440-user.work_email'                => TTi18n::gettext( 'Work Email' ),
					'-1480-user.sin'                       => TTi18n::gettext( 'SIN/SSN' ),
					'-1490-user.note'                      => TTi18n::gettext( 'Employee Note' ),

					//'-1495-tag' => TTi18n::gettext('Tags'),
					'-1499-user.hierarchy_control_display' => TTi18n::gettext( 'Hierarchy' ),

					//Date columns handles these.
					//'-1500-hire_date' => TTi18n::gettext('Hire Date'),
					//'-1600-termination_date' => TTi18n::gettext('Termination Date'),
					//'-1700-birth_date' => TTi18n::gettext('Birth Date'),

					//'-1500-institution' => TTi18n::gettext('Bank Institution'),
					//'-1510-transit' => TTi18n::gettext('Bank Transit/Routing'),
					//'-1520-account' => TTi18n::gettext('Bank Account'),

					'-1820-user_wage.type'           => TTi18n::gettext( 'Wage Type' ),
					'-1840-user_wage.effective_date' => TTi18n::gettext( 'Wage Effective Date' ),
					'-1850-user_wage.note'           => TTi18n::gettext( 'Wage Note' ),

					'-1900-user_preference.language_display'         => TTi18n::gettext( 'Language' ),
					'-1910-user_preference.date_format_display'      => TTi18n::gettext( 'Date Format' ),
					'-1920-user_preference.time_format_display'      => TTi18n::gettext( 'Time Format' ),
					'-1930-user_preference.time_unit_format_display' => TTi18n::gettext( 'Time Units' ),
					'-1940-user_preference.time_zone_display'        => TTi18n::gettext( 'Time Zone' ),
					'-1950-user_preference.items_per_page'           => TTi18n::gettext( 'Rows Per page' ),

					'-2060-user.password_updated_date'       => TTi18n::gettext( 'Password Updated Date' ),
					'-2080-qualification.type'               => TTi18n::gettext( 'Qualification Type' ),
					'-2010-qualification'                    => TTi18n::gettext( 'Qualification' ), // // It's allowed to be reduplicative, so the prefix is not necessary .
					'-2020-qualification.group'              => TTi18n::gettext( 'Qualification Group' ),
					'-2030-user_skill.proficiency'           => TTi18n::gettext( 'Skill Proficiency' ),
					'-2040-user_skill.experience'            => TTi18n::gettext( 'Skill Experience' ),
					'-2050-user_skill.first_used_date'       => TTi18n::gettext( 'Skill First Used Date' ),
					'-2060-user_skill.last_used_date'        => TTi18n::gettext( 'Skill Last Used Date' ),
					'-2070-user_skill.expiry_date'           => TTi18n::gettext( 'Skill Expiry Date' ),
					'-2090-user_education.institute'         => TTi18n::gettext( 'Institute' ),
					'-2100-user_education.major'             => TTi18n::gettext( 'Major/Specialization' ),
					'-2110-user_education.minor'             => TTi18n::gettext( 'Minor' ),
					'-2120-user_education.graduate_date'     => TTi18n::gettext( 'Graduation Date' ),
					'-2130-user_education.grade_score'       => TTi18n::gettext( 'Grade/Score' ),
					'-2140-user_education.start_date'        => TTi18n::gettext( 'Education Start Date' ),
					'-2150-user_education.end_date'          => TTi18n::gettext( 'Education End Date' ),
					'-2160-user_license.license_number'      => TTi18n::gettext( 'License Number' ),
					'-2170-user_license.license_issued_date' => TTi18n::gettext( 'License Issued Date' ),
					'-2180-user_license.license_expiry_date' => TTi18n::gettext( 'License Expiry Date' ),
					'-2190-user_language.fluency'            => TTi18n::gettext( 'Language Fluency' ),
					'-2200-user_language.competency'         => TTi18n::gettext( 'Language Competency' ),
					'-2210-user_membership.ownership'        => TTi18n::gettext( 'Membership Ownership' ),
					'-2240-user_membership.start_date'       => TTi18n::gettext( 'Membership Start Date' ),
					'-2250-user_membership.renewal_date'     => TTi18n::gettext( 'Membership Renewal Date' ),
				];

				$retval = array_merge( $retval, $this->getOptions( 'date_columns' ), (array)$this->getOptions( 'report_static_custom_column' ) );
				$retval = array_merge( $retval, $this->getStaticCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), [ 'users' ], [ 'users' ], [ 'users' => [ 'name' => 'user.' ] ] ) );
				ksort( $retval );
				break;
			case 'dynamic_columns':
				$retval = [
					//Dynamic - Aggregate functions can be used
					'-1830-user_wage.wage'        => TTi18n::gettext( 'Wage' ),
					'-1835-user_wage.hourly_rate' => TTi18n::gettext( 'Hourly Rate' ),

					'-2220-user_membership.amount' => TTi18n::gettext( 'Membership Amount' ),

					'-2900-total_user' => TTi18n::gettext( 'Total Employees' ), //Group counter...
				];

				$retval = array_merge( $retval, $this->getDynamicCustomFieldColumns( 9000, $this->getUserObject()->getCompany(), [ 'users' ], [ 'users' ], [ 'users' => [ 'name' => 'user.' ] ] ) );
				ksort( $retval );
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
						if ( strpos( $column, 'amount' ) !== false || strpos( $column, 'wage' ) !== false || strpos( $column, 'hourly_rate' ) !== false ) {
							$retval[$column] = 'currency';
						} else if ( strpos( $column, 'total_user' ) !== false ) {
							$retval[$column] = 'numeric';
						}
					}
				}
				$retval['user.password_updated_date'] = 'time_stamp';
				break;
			case 'aggregates':
				$retval = [];
				$dynamic_columns = array_keys( Misc::trimSortPrefix( array_merge( $this->getOptions( 'dynamic_columns' ), (array)$this->getOptions( 'report_dynamic_custom_column' ) ) ) );
				if ( is_array( $dynamic_columns ) ) {
					foreach ( $dynamic_columns as $column ) {
						switch ( $column ) {
							case 'user_membership.amount':
								$retval[$column] = 'sum';
								break;
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

						'-1250-by_employee+language'                  => TTi18n::gettext( 'Language Information by Employee' ),
						'-1252-by_qualification_by_employee+language' => TTi18n::gettext( 'Language Information by Language/Employee' ),

						'-1260-by_employee+membership'                  => TTi18n::gettext( 'Membership Information by Employee' ),
						'-1262-by_qualification_by_employee+membership' => TTi18n::gettext( 'Membership Information by Membership/Employee' ),
						'-1264-by_employee+membership_renewal'          => TTi18n::gettext( 'Membership Renewals by Employee' ),

						'-1270-by_employee+license'            => TTi18n::gettext( 'License Information by Employee' ),
						'-1272-by_license_by_employee+license' => TTi18n::gettext( 'License Information by License/Employee' ),
						'-1274-by_employee+license_renewal'    => TTi18n::gettext( 'License Renewals by Employee' ),

						'-1280-by_employee+education'           => TTi18n::gettext( 'Education Information by Employee' ),
						'-1282-by_course_by_employee+education' => TTi18n::gettext( 'Education Information by Course/Employee' ),

						'-1290-by_employee+skill'          => TTi18n::gettext( 'Skills Information by Employee' ),
						'-1292-by_skill_by_employee+skill' => TTi18n::gettext( 'Skills Information by Skill/Employee' ),
						'-1294-by_employee+skill_renewal'  => TTi18n::gettext( 'Skill Renewals by Employee' ),
				];

				break;
			case 'template_config':
				$template = strtolower( Misc::trimSortPrefix( $params['template'] ) );
				if ( isset( $template ) && $template != '' ) {
					switch ( $template ) {
						//language
						case 'by_employee+language':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'qualification';
							$retval['columns'][] = 'user_language.fluency';
							$retval['columns'][] = 'user_language.competency';

							$retval['-2085-qualification_type_id'] = [ 40 ];

							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user_language.fluency' => 'asc' ];
							$retval['sort'][] = [ 'user_language.competency' => 'asc' ];
							break;
						case 'by_qualification_by_employee+language':
							$retval['columns'][] = 'qualification';
							$retval['columns'][] = 'user_language.fluency';
							$retval['columns'][] = 'user_language.competency';
							$retval['columns'][] = 'user.full_name';

							$retval['-2085-qualification_type_id'] = [ 40 ];

							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user_language.fluency' => 'asc' ];
							$retval['sort'][] = [ 'user_language.competency' => 'asc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							break;
						//membership
						case 'by_employee+membership':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'qualification';
							$retval['columns'][] = 'user_membership.ownership';
							$retval['columns'][] = 'user_membership.amount';
							$retval['columns'][] = 'user_membership.start_date';
							$retval['columns'][] = 'user_membership.renewal_date';

							$retval['-2085-qualification_type_id'] = [ 50 ];

							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							$retval['sort'][] = [ 'qualification' => 'asc' ];
							break;
						case 'by_qualification_by_employee+membership':
							$retval['columns'][] = 'qualification';
							$retval['columns'][] = 'user_membership.ownership';
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_membership.amount';

							$retval['columns'][] = 'user_membership.renewal_date';
							$retval['columns'][] = 'user_membership.start_date';

							$retval['-2085-qualification_type_id'] = [ 50 ];

							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user_membership.ownership' => 'asc' ];
							$retval['sort'][] = [ 'user_membership.renewal_date' => 'asc' ];
							break;
						case 'by_employee+membership_renewal':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'qualification';
							$retval['columns'][] = 'user_membership.ownership';
							$retval['columns'][] = 'user_membership.amount';
							$retval['columns'][] = 'user_membership.renewal_date';

							$retval['-2085-qualification_type_id'] = [ 50 ];

							$retval['sort'][] = [ 'user_membership.renewal_date' => 'asc' ];
							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							break;
						//license
						case 'by_employee+license':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'qualification';

							$retval['columns'][] = 'user_license.license_number';
							$retval['columns'][] = 'user_license.license_issued_date';
							$retval['columns'][] = 'user_license.license_expiry_date';

							$retval['-2085-qualification_type_id'] = [ 30 ];

							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							$retval['sort'][] = [ 'qualification' => 'asc' ];
							break;
						case 'by_license_by_employee+license':
							$retval['columns'][] = 'qualification';
							$retval['columns'][] = 'user.full_name';

							$retval['columns'][] = 'user_license.license_number';
							$retval['columns'][] = 'user_license.license_issued_date';
							$retval['columns'][] = 'user_license.license_expiry_date';

							$retval['-2085-qualification_type_id'] = [ 30 ];

							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user_license.license_expiry_date' => 'asc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							break;
						case 'by_employee+license_renewal':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'qualification';

							$retval['columns'][] = 'user_license.license_number';
							$retval['columns'][] = 'user_license.license_issued_date';
							$retval['columns'][] = 'user_license.license_expiry_date';

							$retval['-2085-qualification_type_id'] = [ 30 ];

							$retval['sort'][] = [ 'user_license.license_expiry_date' => 'asc' ];
							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							break;
						//education
						case 'by_employee+education':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'qualification';

							$retval['columns'][] = 'user_education.institute';
							$retval['columns'][] = 'user_education.major';
							$retval['columns'][] = 'user_education.minor';
							$retval['columns'][] = 'user_education.start_date';
							$retval['columns'][] = 'user_education.end_date';
							$retval['columns'][] = 'user_education.graduate_date';
							$retval['columns'][] = 'user_education.grade_score';

							$retval['-2085-qualification_type_id'] = [ 20 ];

							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							$retval['sort'][] = [ 'qualification' => 'asc' ];
							break;
						case 'by_course_by_employee+education':
							$retval['columns'][] = 'qualification';
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'user_education.grade_score';
							$retval['columns'][] = 'user_education.institute';
							$retval['columns'][] = 'user_education.major';
							$retval['columns'][] = 'user_education.minor';
							$retval['columns'][] = 'user_education.start_date';
							$retval['columns'][] = 'user_education.end_date';
							$retval['columns'][] = 'user_education.graduate_date';

							$retval['-2085-qualification_type_id'] = [ 20 ];

							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user_education.grade_score' => 'desc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							break;
						//skill
						case 'by_employee+skill':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'qualification';

							$retval['columns'][] = 'user_skill.proficiency';
							$retval['columns'][] = 'user_skill.experience';
							$retval['columns'][] = 'user_skill.first_used_date';
							$retval['columns'][] = 'user_skill.last_used_date';
							$retval['columns'][] = 'user_skill.expiry_date';

							$retval['-2085-qualification_type_id'] = [ 10 ];

							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							$retval['sort'][] = [ 'qualification' => 'asc' ];
							break;
						case 'by_skill_by_employee+skill':
							$retval['columns'][] = 'qualification';
							$retval['columns'][] = 'user.full_name';

							$retval['columns'][] = 'proficiency';
							$retval['columns'][] = 'user_skill.experience';
							$retval['columns'][] = 'user_skill.first_used_date';
							$retval['columns'][] = 'user_skill.last_used_date';
							$retval['columns'][] = 'user_skill.expiry_date';

							$retval['-2085-qualification_type_id'] = [ 10 ];


							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user_skill.proficiency' => 'desc' ];
							$retval['sort'][] = [ 'user_skill.experience' => 'desc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							break;
						case 'by_employee+skill_renewal':
							$retval['columns'][] = 'user.full_name';
							$retval['columns'][] = 'qualification';

							$retval['columns'][] = 'user_skill.proficiency';
							$retval['columns'][] = 'user_skill.experience';
							$retval['columns'][] = 'user_skill.first_used_date';
							$retval['columns'][] = 'user_skill.last_used_date';
							$retval['columns'][] = 'user_skill.expiry_date';

							$retval['-2085-qualification_type_id'] = [ 10 ];

							$retval['sort'][] = [ 'user_skill.expiry_date' => 'asc' ];
							$retval['sort'][] = [ 'user.full_name' => 'asc' ];
							$retval['sort'][] = [ 'qualification' => 'asc' ];
							$retval['sort'][] = [ 'user_skill.proficiency' => 'desc' ];
							$retval['sort'][] = [ 'user_skill.experience' => 'desc' ];
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
				'user'            => [],
				'user_preference' => [],
				'user_wage'       => [],
				'user_deduction'  => [],
				'total_user'      => [],
				'qualification'   => [],
				'user_skill'      => [],
				'user_education'  => [],
				'user_license'    => [],
				'user_language'   => [],
				'user_membership' => [],
		];

		$columns = $this->getColumnDataConfig();
		$filter_data = $this->getFilterConfig();

		$filter_data['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'user', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$wage_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'wage', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$user_skill_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'user_skill', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$user_education_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'user_education', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$user_license_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'user_license', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$user_language_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'user_language', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );
		$user_membership_permission_children_ids = $this->getPermissionObject()->getPermissionChildren( 'user_membership', 'view', $this->getUserObject()->getID(), $this->getUserObject()->getCompany() );

		//Always include date columns, because 'hire-date_stamp' is not recognized by the UserFactory.
		$columns['hire_date'] = $columns['termination_date'] = $columns['birth_date'] = true;
		$columns['custom_field'] = true;

		//Get user data for joining.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ulf as $key => $u_obj ) {
			//We used to just get return the entire $u_obj->data array, but this wouldn't include tags and other columns that required some additional processing.
			//Not sure why this was done that way... I think because we had problems with the multiple date fields (Hire Date/Termination Date/Birth Date, etc...)
			$this->tmp_data['user'][$u_obj->getId()] = Misc::addKeyPrefix( 'user.', (array)$u_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user.', $columns ) ) );
			$this->tmp_data['user'][$u_obj->getId()]['total_user'] = 1;
			//$this->tmp_data['user'][$u_obj->getId()] = (array)$u_obj->data;
			//$this->tmp_data['user'][$u_obj->getId()]['status'] = Option::getByKey( $u_obj->getStatus(), $u_obj->getOptions( 'status' ) );
			$this->tmp_data['user_preference'][$u_obj->getId()] = [];
			$this->tmp_data['user_wage'][$u_obj->getId()] = [];
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}
		//Debug::Arr($this->tmp_data['user'], 'TMP User Data: ', __FILE__, __LINE__, __METHOD__, 10);

		//Get user preference data for joining.
		$uplf = TTnew( 'UserPreferenceListFactory' ); /** @var UserPreferenceListFactory $uplf */
		$uplf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Preference Rows: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $uplf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $uplf as $key => $up_obj ) {
			$this->tmp_data['user_preference'][$up_obj->getUser()] = Misc::addKeyPrefix( 'user_preference.', (array)$up_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_preference.', $columns ) ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		unset( $filter_data['permission_children_ids'] );

		//Get user wage data for joining.
		$filter_data['wage_group_id'] = [ TTUUID::getZeroID() ]; //Use default wage groups only.
		//$filter_data['permission_children_ids'] = $wage_permission_children_ids;  //This is checked in each foreach() loop using isPermissionChild().
		$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
		$uwlf->getAPILastWageSearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Wage Rows: ' . $uwlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $uwlf as $key => $uw_obj ) {
			if ( $this->getPermissionObject()->isPermissionChild( $uw_obj->getUser(), $wage_permission_children_ids ) ) {
				$this->tmp_data['user_wage'][$uw_obj->getUser()] = Misc::addKeyPrefix( 'user_wage.', (array)$uw_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_wage.', $columns ) ) );
				$this->tmp_data['user_wage'][$uw_obj->getUser()]['wage'] = $uw_obj->getWage();              //Get raw unformatted value as columnFormatter() will format it later on.
				$this->tmp_data['user_wage'][$uw_obj->getUser()]['hourly_rate'] = $uw_obj->getHourlyRate(); //Get raw unformatted value as columnFormatter() will format it later on.
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$qlf = TTnew( 'QualificationListFactory' ); /** @var QualificationListFactory $qlf */
		$qlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' Qualification Rows: ' . $qlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $qlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $qlf as $key => $q_obj ) {
			$this->tmp_data['qualification'][$q_obj->getId()] = Misc::addKeyPrefix( 'qualification.', (array)$q_obj->getObjectAsArray( Misc::removeKeyPrefix( 'qualification.', $columns ) ) );
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$uslf = TTnew( 'UserSkillListFactory' ); /** @var UserSkillListFactory $uslf */
		$uslf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Skill Rows: ' . $uslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $uslf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $uslf as $key => $us_obj ) {
			if ( $this->getPermissionObject()->isPermissionChild( $us_obj->getUser(), $user_skill_permission_children_ids ) ) {
				$this->tmp_data['user_skill'][$us_obj->getQualification()][$us_obj->getUser()][] = Misc::addKeyPrefix( 'user_skill.', (array)$us_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_skill.', $columns ) ), [ 'qualification' ] );
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$uelf = TTnew( 'UserEducationListFactory' ); /** @var UserEducationListFactory $uelf */
		$uelf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Education Rows: ' . $uelf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $uelf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $uelf as $key => $ue_obj ) {
			if ( $this->getPermissionObject()->isPermissionChild( $ue_obj->getUser(), $user_education_permission_children_ids ) ) {
				$this->tmp_data['user_education'][$ue_obj->getQualification()][$ue_obj->getUser()][] = Misc::addKeyPrefix( 'user_education.', (array)$ue_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_education.', $columns ) ), [ 'qualification' ] );
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		$ullf = TTnew( 'UserLicenseListFactory' ); /** @var UserLicenseListFactory $ullf */
		$ullf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User License Rows: ' . $ullf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ullf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ullf as $key => $ul_obj ) {
			if ( $this->getPermissionObject()->isPermissionChild( $ul_obj->getUser(), $user_license_permission_children_ids ) ) {
				$this->tmp_data['user_license'][$ul_obj->getQualification()][$ul_obj->getUser()][] = Misc::addKeyPrefix( 'user_license.', (array)$ul_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_license.', $columns ) ), [ 'qualification' ] );
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}


		$ullf = TTnew( 'UserLanguageListFactory' ); /** @var UserLanguageListFactory $ullf */
		$ullf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Language Rows: ' . $ullf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ullf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $ullf as $key => $ul_obj ) {
			if ( $this->getPermissionObject()->isPermissionChild( $ul_obj->getUser(), $user_language_permission_children_ids ) ) {
				$this->tmp_data['user_language'][$ul_obj->getQualification()][$ul_obj->getUser()][] = Misc::addKeyPrefix( 'user_language.', (array)$ul_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_language.', $columns ) ), [ 'qualification' ] );
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}


		$umlf = TTnew( 'UserMembershipListFactory' ); /** @var UserMembershipListFactory $umlf */
		$umlf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), $filter_data );
		Debug::Text( ' User Membership Rows: ' . $umlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), $umlf->getRecordCount(), null, TTi18n::getText( 'Retrieving Data...' ) );
		foreach ( $umlf as $key => $um_obj ) {
			if ( $this->getPermissionObject()->isPermissionChild( $um_obj->getUser(), $user_membership_permission_children_ids ) ) {
				$this->tmp_data['user_membership'][$um_obj->getQualification()][$um_obj->getUser()][] = Misc::addKeyPrefix( 'user_membership.', (array)$um_obj->getObjectAsArray( Misc::removeKeyPrefix( 'user_membership.', $columns ) ), [ 'qualification' ] );
			}
			$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
		}

		//Debug::Arr($this->tmp_data['user_preference'], 'TMP Data: ', __FILE__, __LINE__, __METHOD__, 10);
		return true;
	}

	/**
	 * PreProcess data such as calculating additional columns from raw data etc...
	 * @return bool
	 */
	function _preProcess() {
		$this->getProgressBarObject()->start( $this->getAPIMessageID(), count( $this->tmp_data['qualification'] ), null, TTi18n::getText( 'Pre-Processing Data...' ) );
		if ( isset( $this->tmp_data['qualification'] ) ) {
			$column_keys = array_keys( $this->getColumnDataConfig() );

			//getReportDates() is quite time consuming, so run it on just the users, rather than qualification records which can be 10-100x more.
			foreach ( $this->tmp_data['user'] as $user_id => $user ) {
				if ( isset( $user['user.hire_date'] ) ) {
					$hire_date_columns = TTDate::getReportDates( 'user.hire', TTDate::parseDateTime( $user['user.hire_date'] ), false, $this->getUserObject(), null, $column_keys );
				} else {
					$hire_date_columns = [];
				}

				if ( isset( $user['user.termination_date'] ) ) {
					$termination_date_columns = TTDate::getReportDates( 'user.termination', TTDate::parseDateTime( $user['user.termination_date'] ), false, $this->getUserObject(), null, $column_keys );
				} else {
					$termination_date_columns = [];
				}
				if ( isset( $user['user.birth_date'] ) ) {
					$birth_date_columns = TTDate::getReportDates( 'user.birth', TTDate::parseDateTime( $user['user.birth_date'] ), false, $this->getUserObject(), null, $column_keys );
				} else {
					$birth_date_columns = [];
				}

				$this->tmp_data['user'][$user_id] = array_merge( $this->tmp_data['user'][$user_id], $hire_date_columns, $termination_date_columns, $birth_date_columns );
			}
			unset( $user, $user_id, $hire_date_columns, $termination_date_columns, $birth_date_columns );

			$key = 0;
			foreach ( $this->tmp_data['qualification'] as $qualification_id => $row ) {
				if ( isset( $this->tmp_data['user'] ) ) {
					foreach ( $this->tmp_data['user'] as $user_id => $user ) {
						$processed_data = [];
						if ( isset( $this->tmp_data['user_preference'][$user_id] ) ) {
							$processed_data = array_merge( $processed_data, (array)$this->tmp_data['user_preference'][$user_id] );
						}
						if ( isset( $this->tmp_data['user_wage'][$user_id] ) ) {
							$processed_data = array_merge( $processed_data, (array)$this->tmp_data['user_wage'][$user_id] );
						}
						if ( isset( $this->tmp_data['user_skill'][$qualification_id][$user_id] ) ) {
							foreach ( $this->tmp_data['user_skill'][$qualification_id][$user_id] as $user_skill ) {
								if ( isset( $user_skill['user_skill.first_used_date'] ) ) {
									$user_skill['user_skill.first_used_date'] = [ 'sort' => TTDate::parseDateTime( $user_skill['user_skill.first_used_date'] ), 'display' => $user_skill['user_skill.first_used_date'] ];
								}
								if ( isset( $user_skill['user_skill.last_used_date'] ) ) {
									$user_skill['user_skill.last_used_date'] = [ 'sort' => TTDate::parseDateTime( $user_skill['user_skill.last_used_date'] ), 'display' => $user_skill['user_skill.last_used_date'] ];
								}
								if ( isset( $user_skill['user_skill.expiry_date'] ) ) {
									$user_skill['user_skill.expiry_date'] = [ 'sort' => TTDate::parseDateTime( $user_skill['user_skill.expiry_date'] ), 'display' => $user_skill['user_skill.expiry_date'] ];
								}
								$this->data[] = array_merge( (array)$row, (array)$user, $processed_data, (array)$user_skill );
							}
						}
						if ( isset( $this->tmp_data['user_education'][$qualification_id][$user_id] ) ) {
							foreach ( $this->tmp_data['user_education'][$qualification_id][$user_id] as $user_education ) {
								if ( isset( $user_education['user_education.start_date'] ) ) {
									$user_education['user_education.start_date'] = [ 'sort' => TTDate::parseDateTime( $user_education['user_education.start_date'] ), 'display' => $user_education['user_education.start_date'] ];
								}
								if ( isset( $user_education['user_education.end_date'] ) ) {
									$user_education['user_education.end_date'] = [ 'sort' => TTDate::parseDateTime( $user_education['user_education.end_date'] ), 'display' => $user_education['user_education.end_date'] ];
								}
								if ( isset( $user_education['user_education.graduate_date'] ) ) {
									$user_education['user_education.graduate_date'] = [ 'sort' => TTDate::parseDateTime( $user_education['user_education.graduate_date'] ), 'display' => $user_education['user_education.graduate_date'] ];
								}
								$this->data[] = array_merge( (array)$row, (array)$user, $processed_data, (array)$user_education );
							}
						}
						if ( isset( $this->tmp_data['user_license'][$qualification_id][$user_id] ) ) {
							foreach ( $this->tmp_data['user_license'][$qualification_id][$user_id] as $user_license ) {
								if ( isset( $user_license['user_license.license_issued_date'] ) ) {
									$user_license['user_license.license_issued_date'] = [ 'sort' => TTDate::parseDateTime( $user_license['user_license.license_issued_date'] ), 'display' => $user_license['user_license.license_issued_date'] ];
								}
								if ( isset( $user_license['user_license.license_expiry_date'] ) ) {
									$user_license['user_license.license_expiry_date'] = [ 'sort' => TTDate::parseDateTime( $user_license['user_license.license_expiry_date'] ), 'display' => $user_license['user_license.license_expiry_date'] ];
								}
								$this->data[] = array_merge( (array)$row, (array)$user, $processed_data, (array)$user_license );
							}
						}
						if ( isset( $this->tmp_data['user_language'][$qualification_id][$user_id] ) ) {
							foreach ( $this->tmp_data['user_language'][$qualification_id][$user_id] as $user_language ) {
								$this->data[] = array_merge( (array)$row, (array)$user, $processed_data, (array)$user_language );
							}
						}
						if ( isset( $this->tmp_data['user_membership'][$qualification_id][$user_id] ) ) {
							foreach ( $this->tmp_data['user_membership'][$qualification_id][$user_id] as $user_membership ) {
								if ( isset( $user_membership['user_membership.start_date'] ) ) {
									$user_membership['user_membership.start_date'] = [ 'sort' => TTDate::parseDateTime( $user_membership['user_membership.start_date'] ), 'display' => $user_membership['user_membership.start_date'] ];
								}
								if ( isset( $user_membership['user_membership.renewal_date'] ) ) {
									$user_membership['user_membership.renewal_date'] = [ 'sort' => TTDate::parseDateTime( $user_membership['user_membership.renewal_date'] ), 'display' => $user_membership['user_membership.renewal_date'] ];
								}
								$this->data[] = array_merge( (array)$row, (array)$user, $processed_data, (array)$user_membership );
							}
						}
					}
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				$key++;
			}
			unset( $this->tmp_data, $row, $processed_data );
		}

		//Debug::Arr($this->data, 'preProcess Data: ', __FILE__, __LINE__, __METHOD__, 10);

		return true;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _setFilterConfig( $data ) {
		if ( isset( $data['skill_expiry_date'] ) ) {
			$data = array_merge( $data, (array)$this->convertTimePeriodToStartEndDate( $data['skill_expiry_date'], 'skill_expiry_' ) );
		}

		if ( isset( $data['membership_renewal_date'] ) ) {
			$data = array_merge( $data, (array)$this->convertTimePeriodToStartEndDate( $data['membership_renewal_date'], 'membership_renewal_' ) );
		}

		if ( isset( $data['license_expiry_date'] ) ) {
			$data = array_merge( $data, (array)$this->convertTimePeriodToStartEndDate( $data['license_expiry_date'], 'license_expiry_' ) );
		}

		if ( isset( $data['education_graduate_date'] ) ) {
			$data = array_merge( $data, (array)$this->convertTimePeriodToStartEndDate( $data['education_graduate_date'], 'education_graduate_' ) );
		}

		return $data;
	}

}

?>
