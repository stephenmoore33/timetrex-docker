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
class LogFactory extends Factory {
	protected $table = 'system_log';
	protected $pk_sequence_name = 'system_log_id_seq'; //PK Sequence name

	var $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'object_id' )->setFunctionMap( 'Object' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'table_name' )->setFunctionMap( 'TableName' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'action_id' )->setFunctionMap( 'Action' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'date' )->setFunctionMap( 'Date' )->setType( 'integer' )->setIsNull( false )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'include_user_id' )->setType( 'uuid' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_user_id' )->setType( 'uuid' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'action' )->setType( 'text' )->setColumn( 'a.action_id' )->setMulti( true ),
							TTSSearchField::new( 'object_id' )->setType( 'uuid' )->setColumn( 'a.object_id' )->setMulti( true ),
							TTSSearchField::new( 'table_name' )->setType( 'text' )->setColumn( 'a.table_name' )->setMulti( true ),
							TTSSearchField::new( 'date' )->setType( 'date_range' )->setColumn( 'a.date' )->setMulti( true ),
							TTSSearchField::new( 'start_date' )->setType( 'start_date' )->setColumn( 'a.date' )->setMulti( true ),
							TTSSearchField::new( 'end_date' )->setType( 'end_date' )->setColumn( 'a.date' )->setMulti( true ),
							TTSSearchField::new( 'first_name' )->setType( 'text' )->setColumn( 'uf.first_name' ),
							TTSSearchField::new( 'last_name' )->setType( 'text' )->setColumn( 'uf.last_name' ),
							TTSSearchField::new( 'table_name_object_id' )->setType( 'table_name_object_id' )->setColumn( 'a.table_name' )->setMulti( true ),
							TTSSearchField::new( 'description' )->setType( 'text' )->setColumn( 'a.description' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APILog' )->setMethod( 'getLog' )
									->setSummary( 'Get log records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APILog' )->setMethod( 'setLog' )
									->setSummary( 'Add or edit log records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APILog' )->setMethod( 'deleteLog' )
									->setSummary( 'Delete log records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APILog' )->setMethod( 'getLog' ) ),
											   ) ),
							TTSAPI::new( 'APILog' )->setMethod( 'getLogDefaultData' )
									->setSummary( 'Get default log data used for creating new logs. Use this before calling setLog to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $params
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'action':
				$retval = [
						5   => TTi18n::gettext( 'View' ), //For special cases like viewing documents. We don't want to log every view action as that would be hugely overkill.
						10  => TTi18n::gettext( 'Add' ),
						20  => TTi18n::gettext( 'Edit' ),
						30  => TTi18n::gettext( 'Delete' ),
						31  => TTi18n::gettext( 'Delete (F)' ), //Full Delete (Only used by PurgeDatabase, but must be left in otherwise blank actions may be visible.)
						40  => TTi18n::gettext( 'UnDelete' ),

						98  => TTi18n::gettext( 'Pre-Sign In' ), //Multifactor login, first factor
						100 => TTi18n::gettext( 'Sign In' ), //Full login successful.
						102 => TTi18n::gettext( 'ReAuthenticate' ), //ReAuthenticated
						110 => TTi18n::gettext( 'Sign Out' ),

						200 => TTi18n::gettext( 'Allow' ),
						210 => TTi18n::gettext( 'Deny' ),
						500 => TTi18n::gettext( 'Notice' ),
						510 => TTi18n::gettext( 'Warning' ),
						900 => TTi18n::gettext( 'Other' ),
				];
				break;
			case 'table_name':
				$retval = [
						'authentication'       => TTi18n::getText( 'Authentication' ),
						'company'              => TTi18n::getText( 'Company' ),
						'branch'               => TTi18n::getText( 'Branch' ),
						'department'           => TTi18n::getText( 'Department' ),
						'currency'             => TTi18n::getText( 'Currency' ),
						'currency_rate'        => TTi18n::getText( 'Currency Rate' ),
						'accrual'              => TTi18n::getText( 'Accrual' ),
						'authorizations'       => TTi18n::getText( 'Authorizations' ),
						'request'              => TTi18n::getText( 'Request' ),
						'request_schedule'     => TTi18n::getText( 'Request - Schedule' ),
						//'message'							=> TTi18n::getText('Messages'), //Old version
						'message_control'      => TTi18n::getText( 'Messages' ),
						'holidays'             => TTi18n::getText( 'Holidays' ),
						'bank_account'         => TTi18n::getText( 'Bank Account' ),
						'roe'                  => TTi18n::getText( 'Record of Employment' ),
						'station'              => TTi18n::getText( 'Station' ),
						'station_user_group'   => TTi18n::getText( 'Station Employee Group' ),
						'station_branch'       => TTi18n::getText( 'Station Branch' ),
						'station_department'   => TTi18n::getText( 'Station Department' ),
						'station_include_user' => TTi18n::getText( 'Station Include Employee' ),
						'station_exclude_user' => TTi18n::getText( 'Station Exclude Employee' ),
						'punch'                => TTi18n::getText( 'Punch' ),
						'punch_control'        => TTi18n::getText( 'Punch Control' ),
						'exception'            => TTi18n::getText( 'Exceptions' ),
						'schedule'             => TTi18n::getText( 'Schedule' ),
						'custom_field_control' => TTi18n::getText( 'Custom Field' ),
						'system_setting'       => TTi18n::getText( 'System Setting' ),
						'cron'                 => TTi18n::getText( 'Maintenance Jobs' ),
						'permission_control'   => TTi18n::getText( 'Permission Groups' ),
						'permission_user'      => TTi18n::getText( 'Permission Employees' ),
						'permission'           => TTi18n::getText( 'Permissions' ),

						'policy_group'                     => TTi18n::getText( 'Policy Group' ),
						'policy_group_user'                => TTi18n::getText( 'Policy Group Employees' ),
						'schedule_policy'                  => TTi18n::getText( 'Schedule Policy' ),
						'round_interval_policy'            => TTi18n::getText( 'Rounding Policy' ),
						'meal_policy'                      => TTi18n::getText( 'Meal Policy' ),
						'break_policy'                     => TTi18n::getText( 'Break Policy' ),
						'accrual_policy_account'           => TTi18n::getText( 'Accrual Account' ),
						'accrual_policy'                   => TTi18n::getText( 'Accrual Policy' ),
						'accrual_policy_milestone'         => TTi18n::getText( 'Accrual Policy Milestone' ),
						'accrual_policy_user_modifier'     => TTi18n::getText( 'Accrual Policy Employee Modifier' ),
						'over_time_policy'                 => TTi18n::getText( 'Overtime Policy' ),
						'premium_policy'                   => TTi18n::getText( 'Premium Policy' ),
						'premium_policy_branch'            => TTi18n::getText( 'Premium Policy Branch' ),
						'premium_policy_department'        => TTi18n::getText( 'Premium Policy Department' ),
						'premium_policy_job_group'         => TTi18n::getText( 'Premium Policy Job Group' ),
						'premium_policy_job'               => TTi18n::getText( 'Premium Policy Job' ),
						'premium_policy_job_item_group'    => TTi18n::getText( 'Premium Policy Task Group' ),
						'premium_policy_job_item'          => TTi18n::getText( 'Premium Policy Task' ),
						'absence_policy'                   => TTi18n::getText( 'Absense Policy' ),
						'exception_policy_control'         => TTi18n::getText( 'Exception Policy (Control)' ),
						'exception_policy'                 => TTi18n::getText( 'Exception Policy' ),
						'holiday_policy'                   => TTi18n::getText( 'Holiday Policy' ),
						'holiday_policy_recurring_holiday' => TTi18n::getText( 'Holiday Policy (Recurring Holiday)' ),
						'regular_time_policy'              => TTi18n::getText( 'Regular Time Policy' ),
						'pay_formula_policy'               => TTi18n::getText( 'Pay Formula Policy' ),
						'contributing_pay_code_policy'     => TTi18n::getText( 'Contributing Pay Code Policy' ),
						'contributing_shift_policy'        => TTi18n::getText( 'Contributing Shift Policy' ),
						'pay_code'                         => TTi18n::getText( 'Pay Code' ),

						'pay_period'                   => TTi18n::getText( 'Pay Period' ),
						'pay_period_schedule'          => TTi18n::getText( 'Pay Period Schedule' ),
						'pay_period_schedule_user'     => TTi18n::getText( 'Pay Period Schedule Employees' ),
						'pay_period_time_sheet_verify' => TTi18n::getText( 'TimeSheet Verify' ),

						'pay_stub'                    => TTi18n::getText( 'Pay Stub' ),
						'pay_stub_entry'              => TTi18n::getText( 'Pay Stub Entry' ),
						'pay_stub_transaction'        => TTi18n::getText( 'Pay Stub Transaction' ),
						'government_document'         => TTi18n::getText( 'Government Document' ),
						'pay_stub_amendment'          => TTi18n::getText( 'Pay Stub Amendment' ),
						'pay_stub_entry_account'      => TTi18n::getText( 'Pay Stub Account' ),
						'pay_stub_entry_account_link' => TTi18n::getText( 'Pay Stub Account Linking' ),

						'recurring_holiday'                   => TTi18n::getText( 'Recurring Holiday' ),
						'recurring_ps_amendment'              => TTi18n::getText( 'Recurring PS Amendment' ),
						'recurring_ps_amendment_user'         => TTi18n::getText( 'Recurring PS Amendment Employees' ),
						'recurring_schedule_control'          => TTi18n::getText( 'Recurring Schedule' ),
						//'recurring_schedule_user'             => TTi18n::getText( 'Recurring Schedule Employees' ),
						'recurring_schedule_template_control' => TTi18n::getText( 'Recurring Schedule Template' ),
						'recurring_schedule_template'         => TTi18n::getText( 'Recurring Schedule Week' ),

						'user_date_total'                          => TTi18n::getText( 'Employee TimeSheet/Hours' ),
						'user_default'                             => TTi18n::getText( 'New Hire Defaults' ),
						'user_generic_data'                        => TTi18n::getText( 'Employee Generic Data' ),
						'user_preference'                          => TTi18n::getText( 'Employee Preference' ),
						'user_preference_notification'             => TTi18n::getText( 'Employee Preference Notification' ),
						'ui_kit'                                   => TTi18n::getText( 'UI Kit Sample' ),
						'ui_kit_child'                             => TTi18n::getText( 'UI Kit Child Sample' ),
						'users'                                    => TTi18n::getText( 'Employee' ),
						'user_identification'                      => TTi18n::getText( 'Employee Identification' ),
						'company_deduction'                        => TTi18n::getText( 'Tax / Deduction' ),
						'company_deduction_pay_stub_entry_account' => TTi18n::getText( 'Tax / Deduction PS Accounts' ),
						'user_deduction'                           => TTi18n::getText( 'Employee Deduction' ),
						'user_title'                               => TTi18n::getText( 'Employee Title' ),
						'user_wage'                                => TTi18n::getText( 'Employee Wage' ),

						'hierarchy_control'     => TTi18n::getText( 'Hierarchy' ),
						'hierarchy_object_type' => TTi18n::getText( 'Hierarchy Object Type' ),
						'hierarchy_user'        => TTi18n::getText( 'Hierarchy Subordinate' ),
						'hierarchy_level'       => TTi18n::getText( 'Hierarchy Superior' ),
						'hierarchy'             => TTi18n::getText( 'Hierarchy Tree' ),

						'user_report_data'     => TTi18n::getText( 'Reports' ),
						'report_schedule'      => TTi18n::getText( 'Report Schedule' ),
						'report_custom_column' => TTi18n::getText( 'Report Custom Column' ),

						'job'                             => TTi18n::getText( 'Job' ),
						'job_user_branch'                 => TTi18n::getText( 'Job Branch' ),
						'job_user_department'             => TTi18n::getText( 'Job Department' ),
						'job_user_group'                  => TTi18n::getText( 'Job Group' ),
						'job_include_user'                => TTi18n::getText( 'Job Include Employee' ),
						'job_exclude_user'                => TTi18n::getText( 'Job Exclude Employee' ),
						'job_job_item_group'              => TTi18n::getText( 'Job Task Group' ),
						'job_include_job_item'            => TTi18n::getText( 'Job Include Task' ),
						'job_exclude_job_item'            => TTi18n::getText( 'Job Exclude Task' ),
						'job_item'                        => TTi18n::getText( 'Job Task' ),
						'job_item_amendment'              => TTi18n::getText( 'Job Task Amendment' ),
						'punch_tag'                       => TTi18n::getText( 'Punch Tag' ),
						'punch_tag_group'                 => TTi18n::getText( 'Punch Tag Group' ),
						'document'                        => TTi18n::getText( 'Document' ),
						'document_revision'               => TTi18n::getText( 'Document Revision' ),
						'client'                          => TTi18n::getText( 'Client' ),
						'client_contact'                  => TTi18n::getText( 'Client Contact' ),
						'client_payment'                  => TTi18n::getText( 'Client Payment' ),
						'invoice'                         => TTi18n::getText( 'Invoice' ),
						'invoice_config'                  => TTi18n::getText( 'Invoice Settings' ),
						'invoice_transaction'             => TTi18n::getText( 'Invoice Transaction' ),
						'product'                         => TTi18n::getText( 'Product' ),
						'product_price'                   => TTi18n::getText( 'Product Price Bracket' ),
						'product_tax_policy'              => TTi18n::getText( 'Product Tax Policy' ),
						'tax_area_policy'                 => TTi18n::getText( 'Invoice Tax Area Policy' ),
						'tax_policy'                      => TTi18n::getText( 'Invoice Tax Policy' ),
						'user_contact'                    => TTi18n::getText( 'Employee Contact' ),
						'user_expense'                    => TTi18n::getText( 'Expense' ),
						'expense_policy'                  => TTi18n::getText( 'Expense Policy' ),
						'user_review'                     => TTi18n::getText( 'Review' ),
						'user_review_control'             => TTi18n::getText( 'Review (Control)' ),
						'kpi'                             => TTi18n::getText( 'Key Performance Indicator' ),
						'qualification'                   => TTi18n::getText( 'Qualification' ),
						'user_skill'                      => TTi18n::getText( 'Skill' ),
						'user_education'                  => TTi18n::getText( 'Education' ),
						'user_membership'                 => TTi18n::getText( 'Memberships' ),
						'user_license'                    => TTi18n::getText( 'Licenses' ),
						'user_language'                   => TTi18n::getText( 'Languages' ),
						'job_vacancy'                     => TTi18n::getText( 'Job Vacancy' ),
						'job_applicant'                   => TTi18n::getText( 'Job Applicant' ),
						'job_application'                 => TTi18n::getText( 'Job Application' ),
						'job_applicant_location'          => TTi18n::getText( 'Job Applicant Location' ),
						'job_applicant_employment'        => TTi18n::getText( 'Job Applicant Employment' ),
						'job_applicant_reference'         => TTi18n::getText( 'Job Applicant Reference' ),
						'job_applicant_skill'             => TTi18n::getText( 'Job Applicant Skill' ),
						'job_applicant_education'         => TTi18n::getText( 'Job Applicant Education' ),
						'job_applicant_license'           => TTi18n::getText( 'Job Applicant Licenses' ),
						'job_applicant_language'          => TTi18n::getText( 'Job Applicant Languages' ),
						'job_applicant_membership'        => TTi18n::getText( 'Job Applicant Memberships' ),
						'ethnic_group'                    => TTi18n::getText( 'Ethnic Group' ),
						'legal_entity'                    => TTi18n::getText( 'Legal Entity' ),
						'payroll_remittance_agency'       => TTi18n::getText( 'Payroll Remittance Agency' ),
						'payroll_remittance_agency_event' => TTi18n::getText( 'Payroll Remittance Agency Event' ),
						'remittance_source_account'       => TTi18n::getText( 'Remittance Source Account' ),
						'remittance_destination_account'  => TTi18n::getText( 'Employee Payment Method' ),
						'geo_fence'                       => TTi18n::getText( 'GEO Fence' ),
				];
				asort( $retval ); //Sort by name so its easier to find objects.
				break;
			case 'table_name_permission_map':
				$retval = [
						'authentication'       => [ 'user' ],
						'company'              => [ 'company' ],
						'branch'               => [ 'branch' ],
						'department'           => [ 'department' ],
						'currency'             => [ 'currency' ],
						'currency_rate'        => [ 'currency' ],
						'accrual'              => [ 'accrual' ],
						'authorizations'       => [ 'user' ],
						'request'              => [ 'request' ],
						'request_schedule'     => [ 'request' ],
						'message'              => [ 'message' ],
						'message_control'      => [ 'message' ],
						'holidays'             => [ 'holiday_policy' ],
						'bank_account'         => [ 'user' ],
						'roe'                  => [ 'user' ],
						'station'              => [ 'station' ],
						'station_user_group'   => [ 'station' ],
						'station_branch'       => [ 'station' ],
						'station_department'   => [ 'station' ],
						'station_include_user' => [ 'station' ],
						'station_exclude_user' => [ 'station' ],
						'punch'                => [ 'punch' ],
						'punch_control'        => [ 'punch' ],
						'exception'            => [ 'punch' ],
						'schedule'             => [ 'schedule' ],
						'custom_field_control' => [ 'company' ],
						'system_setting'       => [ 'company' ],
						'cron'                 => [ 'company' ],
						'permission_control'   => [ 'permission' ],
						'permission_user'      => [ 'permission' ],
						'permission'           => [ 'permission' ],

						'policy_group'                     => [ 'policy_group' ],
						'policy_group_user'                => [ 'policy_group' ],
						'schedule_policy'                  => [ 'schedule_policy' ],
						'round_interval_policy'            => [ 'round_policy' ],
						'meal_policy'                      => [ 'meal_policy' ],
						'break_policy'                     => [ 'break_policy' ],
						'accrual_policy_account'           => [ 'accrual_policy' ],
						'accrual_policy'                   => [ 'accrual_policy' ],
						'accrual_policy_milestone'         => [ 'accrual_policy' ],
						'accrual_policy_user_modifier'     => [ 'accrual_policy' ],
						'over_time_policy'                 => [ 'over_time_policy' ],
						'premium_policy'                   => [ 'premium_policy' ],
						'premium_policy_branch'            => [ 'premium_policy' ],
						'premium_policy_department'        => [ 'premium_policy' ],
						'premium_policy_job_group'         => [ 'premium_policy' ],
						'premium_policy_job'               => [ 'premium_policy' ],
						'premium_policy_job_item_group'    => [ 'premium_policy' ],
						'premium_policy_job_item'          => [ 'premium_policy' ],
						'absence_policy'                   => [ 'absence_policy' ],
						'exception_policy_control'         => [ 'exception_policy' ],
						'exception_policy'                 => [ 'exception_policy' ],
						'holiday_policy'                   => [ 'holiday_policy' ],
						'holiday_policy_recurring_holiday' => [ 'holiday_policy' ],
						'regular_time_policy'              => [ 'regular_time_policy' ],
						'pay_formula_policy'               => [ 'pay_formula_policy' ],
						'contributing_pay_code_policy'     => [ 'contributing_pay_code_policy' ],
						'contributing_shift_policy'        => [ 'contributing_shift_policy' ],
						'pay_code'                         => [ 'pay_code' ],

						'pay_period'                   => [ 'pay_period_schedule' ],
						'pay_period_schedule'          => [ 'pay_period_schedule' ],
						'pay_period_schedule_user'     => [ 'pay_period_schedule' ],
						'pay_period_time_sheet_verify' => [ 'user' ],

						'pay_stub'                    => [ 'pay_stub' ],
						'pay_stub_entry'              => [ 'pay_stub' ],
						'pay_stub_transaction'        => [ 'pay_stub' ],
						'government_document'         => [ 'government_document' ],
						'pay_stub_amendment'          => [ 'pay_stub_amendment' ],
						'pay_stub_entry_account'      => [ 'pay_stub_account' ],
						'pay_stub_entry_account_link' => [ 'pay_stub_account' ],

						'recurring_holiday'                   => [ 'pay_stub_amendment' ],
						'recurring_ps_amendment'              => [ 'pay_stub_amendment' ],
						'recurring_ps_amendment_user'         => [ 'pay_stub_amendment' ],
						'recurring_schedule_control'          => [ 'recurring_schedule' ],
						//'recurring_schedule_user'             => [ 'recurring_schedule' ],
						'recurring_schedule_template_control' => [ 'recurring_schedule_template' ],
						'recurring_schedule_template'         => [ 'recurring_schedule_template' ],

						'user_date_total'                          => [ 'punch' ],
						'user_default'                             => [ 'company' ],
						'user_generic_data'                        => [ 'user' ],
						'user_preference'                          => [ 'user_preference' ],
						'user_preference_notification'             => [ 'user_preference' ],
						'ui_kit'                                   => [ 'user_preference' ],
						'ui_kit_child'                             => [ 'user_preference' ],
						'users'                                    => [ 'user' ],
						'user_group'                               => [ 'user' ],
						'user_identification'                      => [ 'user' ],
						'company_deduction'                        => [ 'company_tax_deduction' ],
						'company_deduction_pay_stub_entry_account' => [ 'company_tax_deduction' ],
						'user_deduction'                           => [ 'user_tax_deduction' ],
						'user_title'                               => [ 'user' ],
						'user_wage'                                => [ 'wage' ],

						'hierarchy_control'     => [ 'hierarchy' ],
						'hierarchy_object_type' => [ 'hierarchy' ],
						'hierarchy_user'        => [ 'hierarchy' ],
						'hierarchy_level'       => [ 'hierarchy' ],
						'hierarchy'             => [ 'hierarchy' ],

						'user_report_data'     => [ 'user' ],
						'report_schedule'      => [ 'user' ],
						'report_custom_column' => [ 'report_custom_column' ],

						'job'                             => [ 'job' ],
						'job_group'                       => [ 'job' ],
						'job_user_branch'                 => [ 'job' ],
						'job_user_department'             => [ 'job' ],
						'job_user_group'                  => [ 'job' ],
						'job_include_user'                => [ 'job' ],
						'job_exclude_user'                => [ 'job' ],
						'job_job_item_group'              => [ 'job' ],
						'job_include_job_item'            => [ 'job' ],
						'job_exclude_job_item'            => [ 'job' ],
						'job_item'                        => [ 'job_item' ],
						'job_item_group'                  => [ 'job_item' ],
						'job_item_amendment'              => [ 'job' ], //This is part of the Edit Job view.
						'punch_tag'                       => [ 'punch_tag' ],
						'punch_tag_group'                 => [ 'punch_tag_group' ],
						'document'                        => [ 'document' ],
						'document_group'                  => [ 'document' ],
						'document_revision'               => [ 'document' ],
						'client'                          => [ 'client' ],
						'client_group'                    => [ 'client' ],
						'client_contact'                  => [ 'client' ],
						'client_payment'                  => [ 'client_payment' ],
						'invoice'                         => [ 'invoice' ],
						'invoice_config'                  => [ 'invoice_config' ],
						'invoice_transaction'             => [ 'invoice' ],
						'product'                         => [ 'product' ],
						'product_group'                   => [ 'product' ],
						'product_price'                   => [ 'product' ],
						'product_tax_policy'              => [ 'product' ],
						'tax_area_policy'                 => [ 'area_policy' ],
						'tax_policy'                      => [ 'tax_policy' ],
						'transaction'                     => [ 'transaction' ],
						'user_contact'                    => [ 'user_contact' ],
						'user_expense'                    => [ 'user_expense' ],
						'expense_policy'                  => [ 'expense_policy' ],
						'user_review'                     => [ 'user_review' ],
						'user_review_control'             => [ 'user_review' ],
						'kpi'                             => [ 'kpi' ],
						'kpi_group'                       => [ 'kpi' ],
						'qualification'                   => [ 'qualification' ],
						'qualification_group'             => [ 'qualification' ],
						'user_skill'                      => [ 'user_skill' ],
						'user_education'                  => [ 'user_education' ],
						'user_membership'                 => [ 'user_membership' ],
						'user_license'                    => [ 'user_license' ],
						'user_language'                   => [ 'user_language' ],
						'job_vacancy'                     => [ 'job_vacancy' ],
						'job_applicant'                   => [ 'job_applicant' ],
						'job_application'                 => [ 'job_application' ],
						'job_applicant_location'          => [ 'job_applicant' ],
						'job_applicant_employment'        => [ 'job_applicant' ],
						'job_applicant_reference'         => [ 'job_applicant' ],
						'job_applicant_skill'             => [ 'job_applicant' ],
						'job_applicant_education'         => [ 'job_applicant' ],
						'job_applicant_license'           => [ 'job_applicant' ],
						'job_applicant_language'          => [ 'job_applicant' ],
						'job_applicant_membership'        => [ 'job_applicant' ],
						'ethnic_group'                    => [ 'user' ],
						'legal_entity'                    => [ 'legal_entity' ],
						'payroll_remittance_agency'       => [ 'payroll_remittance_agency' ],
						'payroll_remittance_agency_event' => [ 'payroll_remittance_agency' ],
						'remittance_source_account'       => [ 'remittance_source_account' ],
						'remittance_destination_account'  => [ 'remittance_destination_account' ],
						'geo_fence'                       => [ 'geo_fence' ],
				];

				break;
			case 'columns':
				$retval = [
						'-1010-first_name'  => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'   => TTi18n::gettext( 'Last Name' ),
						'-1100-date'        => TTi18n::gettext( 'Date' ),
						'-1110-object'      => TTi18n::gettext( 'Object' ),
						'-1120-action'      => TTi18n::gettext( 'Action' ),
						'-1130-description' => TTi18n::gettext( 'Description' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'date',
						'object',
						'action',
						'description',
				];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'         => 'ID',
				'user_id'    => 'User',
				'first_name' => false,
				'last_name'  => false,
				'object_id'  => 'Object',
				'table_name' => 'TableName',
				'object'     => false, //Actually the display table name.

				'action_id'   => 'Action',
				'action'      => false,
				'description' => 'Description',
				'date'        => 'Date',

				'display_field' => false,
				'old_value' => false,
				'new_value' => false,
				'details' => 'Details',

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return null
	 */
	function getUserObject() {
		if ( is_object( $this->user_obj ) ) {
			return $this->user_obj;
		} else {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->user_obj = $ulf->getById( $this->getUser() )->getCurrent();

			return $this->user_obj;
		}
	}

	/**
	 * @return bool|string
	 */
	function getLink() {

		$link = false;

		//Only show links on add/edit/allow actions.
		if ( !in_array( $this->getAction(), [ 10, 20, 200 ] ) ) {
			return $link;
		}

		switch ( $this->getTableName() ) {
			case 'authentication':
				break;
			case 'company':
				$link = 'company/EditCompany.php?id=' . $this->getObject();
				break;
			case 'branch':
				$link = 'branch/EditBranch.php?id=' . $this->getObject();
				break;
			case 'department':
				$link = 'department/EditDepartment.php?id=' . $this->getObject();
				break;
			case 'currency':
				$link = 'currency/EditCurrency.php?id=' . $this->getObject();
				break;
			case 'accrual':
				//$link = 'currency/EditCurrency.php?id='. $this->getObject();
				break;
			case 'authorizations':
				break;
			case 'request':
				$link = 'request/ViewRequest.php?id=' . $this->getObject();
				break;
			case 'permission_control':
				$link = 'permission/EditPermissionControl.php?id=' . $this->getObject();
				break;
			case 'holidays':
				break;
			case 'bank_account':
				break;
			case 'roe':
				break;
			case 'station':
				$link = 'station/EditStation.php?id=' . $this->getObject();
				break;
			case 'punch':
				break;
			case 'custom_field_control':
				break;
			case 'system_setting':
				break;
			case 'cron':
				break;
			case 'policy_group':
				$link = 'policy/EditPolicyGroup.php?id=' . $this->getObject();
				break;
			case 'schedule_policy':
				$link = 'policy/EditSchedulePolicy.php?id=' . $this->getObject();
				break;
			case 'round_interval_policy':
				$link = 'policy/EditRoundIntervalPolicy.php?id=' . $this->getObject();
				break;
			case 'meal_policy':
				$link = 'policy/EditMealPolicy.php?id=' . $this->getObject();
				break;
			case 'accrual_policy':
				$link = 'policy/EditAccrualPolicy.php?id=' . $this->getObject();
				break;
			case 'over_time_policy':
				$link = 'policy/EditOverTimePolicy.php?id=' . $this->getObject();
				break;
			case 'premium_policy':
				$link = 'policy/EditPremiumTimePolicy.php?id=' . $this->getObject();
				break;
			case 'absence_policy':
				$link = 'policy/EditAbsencePolicy.php?id=' . $this->getObject();
				break;
			case 'exception_policy_control':
				$link = 'policy/EditExceptionControlPolicy.php?id=' . $this->getObject();
				break;
			case 'holiday_policy':
				$link = 'policy/EditHolidayPolicy.php?id=' . $this->getObject();
				break;
			case 'pay_period':
				$link = 'payperiod/ViewPayPeriod.php?pay_period_id=' . $this->getObject();
				break;
			case 'pay_period_schedule':
				$link = 'payperiod/EditPayPeriodSchedule.php?id=' . $this->getObject();
				break;
			case 'pay_period_time_sheet_verify':
				break;
			case 'pay_stub':
				break;
			case 'pay_stub_amendment':
				$link = 'pay_stub_amendment/EditPayStubAmendment.php?id=' . $this->getObject();
				break;
			case 'pay_stub_entry_account':
				$link = 'pay_stub/EditPayStubEntryAccount.php?id=' . $this->getObject();
				break;
			case 'pay_stub_entry_account_link':
				break;
			case 'recurring_holiday':
				$link = 'policy/EditRecurringHoliday.php?id=' . $this->getObject();
				break;
			case 'recurring_ps_amendment':
				$link = 'pay_stub_amendment/EditRecurringPayStubAmendment.php?id=' . $this->getObject();
				break;
			case 'recurring_schedule_control':
				$link = 'schedule/EditRecurringSchedule.php?id=' . $this->getObject();
				break;
			case 'recurring_schedule_template_control':
				$link = 'schedule/EditRecurringScheduleTemplate.php?id=' . $this->getObject();
				break;
			case 'user_date_total':
				break;
			case 'user_default':
				$link = 'users/EditUserDefault.php?id=' . $this->getObject();
				break;
			case 'user_generic_data':
				break;
			case 'user_preference':
				$link = 'users/EditUserPreference.php?user_id=' . $this->getObject();
				break;
			case 'users':
				$link = 'users/EditUser.php?id=' . $this->getObject();
				break;
			case 'company_deduction':
				$link = 'company/EditCompanyDeduction.php?id=' . $this->getObject();
				break;
			case 'user_deduction':
				$link = 'users/EditUserDeduction.php?id=' . $this->getObject();
				break;
			case 'user_title':
				$link = 'users/EditUserTitle.php?id=' . $this->getObject();
				break;
			case 'user_wage':
				$link = 'users/EditUserWage.php?id=' . $this->getObject();
				break;
			case 'job':
				$link = 'job/EditJob.php?id=' . $this->getObject();
				break;
			case 'job_item':
				$link = 'job_item/EditJobItem.php?id=' . $this->getObject();
				break;
			case 'job_item_amendment':
				$link = 'job_item/EditJobItemAmendment.php?id=' . $this->getObject();
				break;
			case 'document':
				$link = 'document/EditDocument.php?document_id=' . $this->getObject();
				break;
			case 'document_revision':
				break;
			case 'client':
				$link = 'client/EditClient.php?client_id=' . $this->getObject();
				break;
			case 'client_contact':
				$link = 'client/EditClientContact.php?id=' . $this->getObject();
				break;
			case 'client_payment':
				$link = 'client/EditClientPayment.php?id=' . $this->getObject();
				break;
			case 'invoice':
				$link = 'invoice/EditInvoice.php?id=' . $this->getObject();
				break;
			case 'invoice_config':
				$link = 'invoice/EditInvoiceConfig.php';
				break;
			case 'invoice_transaction':
				$link = 'invoice/EditTransaction.php?id=' . $this->getObject();
				break;
			case 'product':
				$link = 'product/EditProduct.php?id=' . $this->getObject();
				break;
			case 'tax_area_policy':
				$link = 'invoice_policy/EditTaxAreaPolicy.php?id=' . $this->getObject();
				break;
			case 'tax_policy':
				$link = 'invoice_policy/EditTaxPolicy.php?id=' . $this->getObject();
				break;
		}

		if ( $link !== false ) {
			$link = Environment::getBaseURL() . $link;
		}

		return $link;
	}

	/**
	 * @return mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getObject() {
		return $this->getGenericDataValue( 'object_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setObject( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'object_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTableName() {
		return $this->getGenericDataValue( 'table_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTableName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'table_name', $value );
	}

	/**
	 * @return int
	 */
	function getAction() {
		return $this->getGenericDataValue( 'action_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAction( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'action_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getDescription() {
		return $this->getGenericDataValue( 'description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'description', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDate() {
		return $this->getGenericDataValue( 'date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value == '' ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'date', $value );
	}

	/**
	 * @return array|bool|mixed
	 */
	function getDetails( $field = null, $old_value = null, $new_value = null ) {
		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL && $this->isNew() == false && is_object( $this->getUserObject() ) ) {
			//Get class for this table
			Debug::Text( 'Table: ' . $this->getTableName(), __FILE__, __LINE__, __METHOD__, 10 );

			global $global_table_map;
			require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'TableMap.inc.php' );
			if ( isset( $global_table_map[$this->getTableName()] ) ) {
				$table_class = $global_table_map[$this->getTableName()];
				$class = new $table_class;
				Debug::Text( 'Table Class: ' . $table_class, __FILE__, __LINE__, __METHOD__, 10 );

				//Allow passing field/old_value/new_value and converting those into human readable values directly. This is used in the Audit Report for optimization purposes.
				if ( $field !== null || $old_value !== null || $new_value !== null ) {
					$detail_row = [
							'display_field' => LogDetailDisplay::getDisplayField( $class, $field ),
							'old_value'     => LogDetailDisplay::getDisplayOldValue( $class, $field, $old_value ),
							'new_value'     => LogDetailDisplay::getDisplayNewValue( $class, $field, $new_value ),
					];

					return $detail_row;
				} else {
					$ldlf = TTnew( 'LogDetailListFactory' ); /** @var LogDetailListFactory $ldlf */
					$ldlf->getBySystemLogIdAndCompanyId( $this->getID(), $this->getUserObject()->getCompany() );
					if ( $ldlf->getRecordCount() > 0 ) {
						$detail_row = [];
						foreach ( $ldlf as $ld_obj ) {
							if ( TTUUID::isUUID( $this->getObject() ) && $this->getObject() != TTUUID::getZeroID() && $this->getObject() != TTUUID::getNotExistID() ) {
								$class->setID( $this->getObject() ); //Set the object id of the class so we can reference it later if needed.
							}
							if ( $ld_obj->getField() === 'custom_field' && $ld_obj->getNewValue() != null ) {
								//Issue #3340 - This is a backwards compatible fix for system_log_detail records created <= v16.2.5.
								//Add record logs were logging the entire custom field json string instead of each individual field.
								//This code detects that scenario and outputs each individual custom field display row.
								$custom_fields = json_decode( $ld_obj->getNewValue(), true );
								if ( is_array( $custom_fields ) ) {
									foreach ( $custom_fields as $id => $value ) {
										$field = 'custom_field-' . $id;
										$detail_row[] = [
												'field'         => 'custom_field-' . $field,
												'display_field' => LogDetailDisplay::getDisplayField( $class, $field ),
												'old_value'     => LogDetailDisplay::getDisplayOldValue( $class, $field, $ld_obj->getOldValue() ),
												'new_value'     => LogDetailDisplay::getDisplayNewValue( $class, $field, $value ),
										];
									}
								}
							} else {
								$detail_row[] = [
										'field'         => $ld_obj->getField(),
										'display_field' => LogDetailDisplay::getDisplayField( $class, $ld_obj->getField() ),
										'old_value'     => LogDetailDisplay::getDisplayOldValue( $class, $ld_obj->getField(), $ld_obj->getOldValue() ),
										'new_value'     => LogDetailDisplay::getDisplayNewValue( $class, $ld_obj->getField(), $ld_obj->getNewValue() ),
								];
							}
						}

						$detail_row = Sort::multiSort( $detail_row, 'display_field' );

						//Debug::Arr( $detail_row, 'Detail Row: ', __FILE__, __LINE__, __METHOD__, 10);

						return $detail_row;
					}
				}
			}


		}

		Debug::Text( 'No Log Details... ID: ' . $this->getID(), __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * Don't allow remote API calls to set audit trail records.
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			//$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getDate() );
							break;
						case 'object':
							$data[$variable] = Option::getByKey( $this->getTableName(), $this->getOptions( 'table_name' ) );
							break;
						case 'action':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'display_field':
						case 'old_value':
						case 'new_value':
							if ( !isset( $details ) ) {
								$details = $this->getDetails( $this->getColumn( 'field' ), $this->getColumn( 'old_value' ), $this->getColumn( 'new_value' ) );
							}
							$data[$variable] = $details[$variable] ?? null;
							break;
						case 'details':
							if ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) {
								$data[$variable] = $this->getDetails();
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			//$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * This table doesn't have any of these columns, so overload the functions.
	 * @return bool
	 */
	function getDeleted() {
		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = null ) {
		return false;
	}


	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User
		if ( $this->getUser() !== false && $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'User is invalid' )
			);
		}
		// Object
		$this->Validator->isUUID( 'object',
								  $this->getObject(),
								  TTi18n::gettext( 'Object is invalid' )
		);
		// Table
		$this->Validator->isLength( 'table',
									$this->getTableName(),
									TTi18n::gettext( 'Table is invalid' ),
									2,
									250
		);
		// Action
		$this->Validator->inArrayKey( 'action',
									  $this->getAction(),
									  TTi18n::gettext( 'Incorrect Action' ),
									  $this->getOptions( 'action' )
		);
		// Description
		$this->Validator->isLength( 'description',
									$this->getDescription(),
									TTi18n::gettext( 'Description is too short or too long' ),
									2,
									2000
		);
		// Date
		$this->Validator->isDate( 'date',
								  $this->getDate(),
								  TTi18n::gettext( 'Date is invalid' )
		);
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getDate() === false ) {
			$this->setDate();
		}

		return true;
	}
}

?>
