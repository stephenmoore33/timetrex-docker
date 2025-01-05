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
 * Class PurgeDatabase
 */
class PurgeDatabase {
	static $parent_table_column_map = [
			'users'           => 'user_id',
			'report_schedule' => 'user_report_data_id',
	];

	static $parent_table_map_array = [
			'absence_policy'                           => [
					'company',
			],
			'accrual'                                  => [
					'users',
			],
			'accrual_balance'                          => [
					'users',
					//'accrual_policy'
					'accrual_policy_account',
			],
			'accrual_policy'                           => [
					'company',
			],
			'accrual_policy_account'                   => [
					'company',
			],
			'accrual_policy_milestone'                 => [
					'accrual_policy',
			],
			'area_policy'                              => [
					'company',
			],
			'area_policy_location'                     => [
					'area_policy',
			],
			//Can't automatically purge this table, as user_id is often NULL for company wide settings.
			//'bank_account' => array(
			//				'company',
			//				'users'
			//				),
			'branch'                                   => [
					'company',
			],
			'bread_crumb'                              => [
					'users',
			],
			'break_policy'                             => [
					'company',
			],
			'client'                                   => [
					'company',
			],
			'client_balance'                           => [
					'client',
			],
			'client_contact'                           => [
					'client',
			],
			'client_group'                             => [
					'company',
			],
			'client_payment'                           => [
					'client',
			],
			'legal_entity'                             => [
					'company',
			],
			'remittance_source_account'                => [
					'legal_entity',
			],
			'remittance_destination_account'           => [
					'users',
			],
			'payroll_remittance_agency'                => [
					'legal_entity',
			],
			'payroll_remittance_agency_event'          => [
					'payroll_remittance_agency',
			],
			'company_deduction'                        => [
					'company',
			],
			'company_deduction_pay_stub_entry_account' => [
					'company_deduction',
			],
			'company_generic_map'                      => [
					'company',
			],
			'company_user_count'                       => [
					'company',
			],
			'contributing_pay_code_policy'             => [
					'company',
			],
			'contributing_shift_policy'                => [
					'company',
			],
			'currency'                                 => [
					'company',
			],
			'currency_rate'                            => [
					'currency',
			],
			'department'                               => [
					'company',
			],
			'government_document'                      => [
					'users',
			],
			'document'                                 => [
					'company',
			],
			'document_attachment'                      => [
					'document',
			],
			'document_group'                           => [
					'company',
			],
			'document_revision'                        => [
					'document',
			],
			'exception'                                => [
					'exception_policy',
					'users',
					'punch',
					'punch_control',
			],
			'exception_policy'                         => [
					'exception_policy_control',
			],
			'exception_policy_control'                 => [
					'company',
			],
			'hierarchy_control'                        => [
					'company',
			],
			'hierarchy_level'                          => [
					'hierarchy_control',
					'users',
			],
			'hierarchy_object_type'                    => [
					'hierarchy_control',
			],
			'hierarchy_user'                           => [
					'hierarchy_control',
					'users',
			],
			'holiday_policy'                           => [
					'company',
			],
			'holiday_policy_recurring_holiday'         => [
					'holiday_policy',
					'recurring_holiday',
			],
			'holidays'                                 => [
					'holiday_policy',
			],
			'invoice'                                  => [
					'client',
			],
			'invoice_config'                           => [
					'company',
			],
			'invoice_district'                         => [
					'company',
			],
			'invoice_transaction'                      => [
					'client',
					'product', //Invoice payments are product_id = 0
					'invoice',
			],
			'job'                                      => [
					'company',
			],
			'job_group'                                => [
					'company',
			],
			'job_item'                                 => [
					'company',
			],
			'job_item_amendment'                       => [
					'job',
			],
			'job_item_group'                           => [
					'company',
			],
			'geo_fence'                                => [
					'company',
			],


			'qualification'       => [
					'company',
			],
			'qualification_group' => [
					'company',
			],
			'user_education'      => [
					'users',
			],
			'user_license'        => [
					'users',
			],
			'user_skill'          => [
					'users',
			],
			'user_language'       => [
					'users',
			],
			'user_membership'     => [
					'users',
			],
			'user_review_control' => [
					'users',
			],
			'user_review'         => [
					'user_review_control',
			],
			'kpi'                 => [
					'company',
			],
			'kpi_group'           => [
					'company',
			],
			'ethnic_group'        => [
					'company',
			],
			'user_contact'        => [
					'users',
			],
			'user_setting'        => [
					'users',
			],
			'company_setting'     => [
					'company',
			],


			'job_vacancy'                  => [
					'company',
			],
			'job_applicant'                => [
					'company',
			],
			'job_application'              => [
					'job_applicant',
					'job_vacancy',
			],
			'job_applicant_location'       => [
					'job_applicant',
			],
			'job_applicant_employment'     => [
					'job_applicant',
			],
			'job_applicant_reference'      => [
					'job_applicant',
			],
			'job_applicant_education'      => [
					'job_applicant',
			],
			'job_applicant_skill'          => [
					'job_applicant',
			],
			'job_applicant_language'       => [
					'job_applicant',
			],
			'job_applicant_membership'     => [
					'job_applicant',
			],
			'job_applicant_license'        => [
					'job_applicant',
			],
			'recruitment_portal_config'    => [
					'company',
			],
			'recruitment_portal_field_map' => [
					'company',
			],

			'expense_policy' => [
					'company',
			],
			'user_expense'   => [
					'users',
					'expense_policy',
			],

			'meal_policy'                         => [
					'company',
			],
			'message_recipient'                   => [
					'users',
					'message_sender',
			],
			'message_sender'                      => [
					'users',
					'message_control',
			],
			'custom_field'                         => [
					'company',
			],
			'over_time_policy'                    => [
					'company',
			],
			'pay_code'                            => [
					'company',
			],
			'pay_formula_policy'                  => [
					'company',
			],
			'pay_period'                          => [
					'company',
					'pay_period_schedule',
			],
			'pay_period_schedule'                 => [
					'company',
			],
			'pay_period_schedule_user'            => [
					'pay_period_schedule',
					'users',
			],
			'pay_period_time_sheet_verify'        => [
					'pay_period',
					'users',
			],
			'pay_stub'                            => [
					'pay_period',
					'users',
			],
			'pay_stub_amendment'                  => [
					'users',
			],
			'pay_stub_entry'                      => [
					'pay_stub',
			],
			'pay_stub_entry_account'              => [
					'company',
			],
			'pay_stub_transaction'                => [
					'pay_stub',
			],
			'pay_stub_entry_account_link'         => [
					'company',
			],
			'payment_gateway'                     => [
					'company',
			],
			'payment_gateway_credit_card_type'    => [
					'payment_gateway',
			],
			'payment_gateway_currency'            => [
					'payment_gateway',
			],
			'permission'                          => [
					'permission_control',
			],
			'permission_control'                  => [
					'company',
			],
			'permission_user'                     => [
					'permission_control',
					'users',
			],
			'policy_group'                        => [
					'company',
			],
			'policy_group_user'                   => [
					'policy_group',
					'users',
			],
			'premium_policy'                      => [
					'company',
			],
			'premium_policy_branch'               => [
					'premium_policy',
					'branch',
			],
			'premium_policy_department'           => [
					'premium_policy',
					'department',
			],
			'premium_policy_job'                  => [
					'premium_policy',
					'job',
			],
			'premium_policy_job_group'            => [
					'premium_policy',
					'job_group',
			],
			'premium_policy_job_item'             => [
					'premium_policy',
					'job_item',
			],
			'premium_policy_job_item_group'       => [
					'premium_policy',
					'job_item_group',
			],
			'product'                             => [
					'company',
			],
			'product_group'                       => [
					'company',
			],
			'product_price'                       => [
					'product',
			],
			'punch'                               => [
					'punch_control',
			],
			'punch_control'                       => [
					'users',
			],
			'recurring_holiday'                   => [
					'company',
			],
			'recurring_ps_amendment'              => [
					'company',
			],
			'recurring_ps_amendment_user'         => [
					'recurring_ps_amendment',
					'users',
			],
			'recurring_schedule_control'          => [
					'company',
			],
			'recurring_schedule_template'         => [
					'recurring_schedule_template_control',
			],
			'recurring_schedule_template_control' => [
					'company',
			],
			//'recurring_schedule_user'             => [
			//		'recurring_schedule_control',
			//		'users',
			//],
			'recurring_schedule'                  => [
					'recurring_schedule_control',
					'users',
					'company',
			],
			'regular_time_policy'                 => [
					'company',
			],
			'report_schedule'                     => [
					'user_report_data',
			],
			'request'                             => [
					'users',
			],
			'request_schedule'                    => [
					'request',
			],

			'roe'                            => [
					'users',
			],
			'round_interval_policy'          => [
					'company',
			],
			'schedule'                       => [
					'users',
					'company',
			],
			'schedule_policy'                => [
					'company',
			],
			'shipping_policy'                => [
					'company',
			],
			'shipping_policy_object'         => [
					'shipping_policy',
			],
			'shipping_table_rate'            => [
					'shipping_policy',
			],
			'station'                        => [
					'company',
			],
			'station_branch'                 => [
					'station',
					'branch',
			],
			'station_department'             => [
					'station',
					'department',
			],
			'station_exclude_user'           => [
					'station',
					'users',
			],
			'station_include_user'           => [
					'station',
					'users',
			],
			'station_user'                   => [
					'station',
					'users',
			],
			'station_user_group'             => [
					'station',
			],
			'system_log'                     => [
					'users',
			],
			'system_log_detail'              => [
					'system_log',
			],
			'tax_policy'                     => [
					'company',
			],
			'tax_policy_object'              => [
					'tax_policy',
			],
			'user_date_total'                => [
					'users',
			],
			'user_deduction'                 => [
					'users',
					'company_deduction',
			],
			'user_default'                   => [
					'company',
			],
			'user_default_company_deduction' => [
					'user_default',
					'company_deduction',
			],
			'user_default_preference_notification' => [
					'user_default',
			],
			//Can't automatically purge this table, as user_id is often NULL for company wide settings.
			//'user_generic_data' => array(
			//				'users',
			//				'company'
			//				),
			'user_generic_status'            => [
					'users',
			],
			'user_group'                     => [
					'company',
			],
			'user_identification'            => [
					'users',
			],
			'user_preference'                => [
					'users',
			],
			'user_preference_notification'   => [
					'users',
			],
			'notification'   => [
					'users',
			],
			'device_token'   => [
					'users',
			],
			//Can't automatically purge this table, as user_id is often NULL for company wide settings.
			//'user_report_data' => array(
			//				'company',
			//				'users'
			//				),
			'report_custom_column'           => [
					'company',
			],
			'user_title'                     => [
					'company',
			],
			'user_wage'                      => [
					'users',
			],
			'users'                          => [
					'company',
			],
			'wage_group'                     => [
					'company',
			],
	];

	/**
	 * @return bool
	 * @noinspection PhpMissingBreakStatementInspection
	 */
	static function Execute() {
		global $db;

		//Make array of tables to purge, and the timeperiod to purge them at.
		Debug::Text( 'Purging database tables: ' . TTDate::getDate( 'DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
		$purge_tables = [
				'user_generic_status'                      => 2,
				'punch'                                    => 60, //Punch must come before punch_control
				'punch_control'                            => 60, //punch_control must come before user_date
				'user_date_total'                          => 60, //user_date_total must come before user_date
				'schedule'                                 => 60, //schedule must come before user_date
				'company'                                  => 120,
				'company_deduction'                        => 120,
				'company_deduction_pay_stub_entry_account' => 120,
				'company_generic_map'                      => 125, //Since we don't bother mapping to all possible other tables, this value should be higher than any other table.
				'company_user_count'                       => 120,
				'legal_entity'                             => 120,
				'remittance_source_account'                => 120,
				'remittance_destination_account'           => 120,
				'payroll_remittance_agency'                => 120,
				'payroll_remittance_agency_event'          => 120,
				'authentication'                           => 2, //Sessions.
				'hierarchy_user'                           => 45,
				'hierarchy_object_type'                    => 45,
				'hierarchy_level'                          => 45,
				'absence_policy'                           => 45,
				'accrual'                                  => 60,
				'accrual_balance'                          => 60, //Doesnt have updated_date column
				'accrual_policy'                           => 45,
				'accrual_policy_milestone'                 => 60,
				'accrual_policy_account'                   => 120,
				'authorizations'                           => 45, //Must go before requests.
				'bank_account'                             => 45,
				'branch'                                   => 45,
				'break_policy'                             => 45,
				'wage_group'                               => 45,
				'cron'                                     => 45,
				'currency'                                 => 120,
				'currency_rate'                            => 120,
				'contributing_pay_code_policy'             => 45,
				'contributing_shift_policy'                => 45,
				'department'                               => 45,
				'exception'                                => 45,
				'exception_policy'                         => 45,
				'exception_policy_control'                 => 45,
				'hierarchy_control'                        => 45,
				'hierarchy_tree'                           => 45,
				'hierarchy_share'                          => 45,
				'holiday_policy'                           => 45,
				'holiday_policy_recurring_holiday'         => 45,
				'holidays'                                 => 45,
				'meal_policy'                              => 45,
				'message'                                  => 45,
				'message_sender'                           => 45,
				'message_recipient'                        => 45,
				'message_control'                          => 45,
				'custom_field'                             => 45,
				'over_time_policy'                         => 45,
				'pay_code'                                 => 90,
				'pay_formula_policy'                       => 90,
				'pay_period'                               => 45,
				'pay_period_schedule'                      => 45,
				'pay_period_schedule_user'                 => 45,
				'pay_period_time_sheet_verify'             => 45,
				'pay_stub'                                 => 120,
				'pay_stub_amendment'                       => 120,
				'pay_stub_entry'                           => 120,
				'pay_stub_entry_account'                   => 120,
				'pay_stub_entry_account_link'              => 120,
				'pay_stub_transaction'                     => 120,
				'permission'                               => 45,
				'permission_control'                       => 45,
				'permission_user'                          => 45,
				'policy_group'                             => 45,
				'policy_group_user'                        => 45,
				'premium_policy'                           => 45,
				'premium_policy_branch'                    => 45,
				'premium_policy_department'                => 45,
				'recurring_holiday'                        => 45,
				'recurring_ps_amendment'                   => 45,
				'recurring_ps_amendment_user'              => 45,
				'recurring_schedule_control'               => 45,
				'recurring_schedule_template'              => 45,
				'recurring_schedule_template_control'      => 45,
				//'recurring_schedule_user'                  => 45,
				'regular_time_policy'                      => 45,
				'request'                                  => 45,
				'roe'                                      => 45,
				'round_interval_policy'                    => 45,
				'schedule_policy'                          => 45,
				'station'                                  => 45,
				'station_user'                             => 45,
				'station_branch'                           => 45,
				'station_department'                       => 45,
				'station_user_group'                       => 45,
				'station_include_user'                     => 45,
				'station_exclude_user'                     => 45,
				'user_deduction'                           => 120, //This is related to payroll, so keep as long as pay stubs exist.
				'user_default'                             => 45,
				'user_default_company_deduction'           => 45,
				'user_default_preference_notification'     => 45,
				'user_generic_data'                        => 45,
				'user_group'                               => 45,
				'user_group_tree'                          => 45,
				'user_identification'                      => 45,
				'user_preference'                          => 45,
				'user_preference_notification'             => 45,
				'user_title'                               => 45,
				'user_wage'                                => 120,
				'user_report_data'                         => 45,
				'user_setting'                             => 120,
				'company_setting'                          => 45,
				'users'                                    => 120,
				'recurring_schedule'                       => 0, //Delete these immediately.
				'bread_crumb'                              => 45,
				'system_log'                               => 45,
				'system_log_detail'                        => 45,
				'company_generic_tag_map'                  => 45,
				'company_generic_tag'                      => 45,
				'qualification'                            => 45,
				'user_education'                           => 45,
				'user_license'                             => 45,
				'user_skill'                               => 45,
				'user_language'                            => 45,
				'user_membership'                          => 45,
				'qualification_group'                      => 45,
				'qualification_group_tree'                 => 45,
				'kpi'                                      => 45,
				'kpi_group'                                => 45,
				'kpi_group_tree'                           => 45,
				'user_review'                              => 45,
				'user_review_control'                      => 45,
				'user_contact'                             => 45,
				'ethnic_group'                             => 45,
				'notification'                             => 21, //Keep notifications for three weeks.
				'device_token'                             => 45,
		];

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			$purge_professional_tables = [
					'request_schedule'     => 45,
					'report_schedule'      => 45,
					'report_custom_column' => 45,
			];

			$purge_tables = array_merge( $purge_tables, $purge_professional_tables );
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			$purge_corporate_tables = [
					'client'                           => 45,
					'client_contact'                   => 45,
					'client_group'                     => 45,
					'client_group_tree'                => 45,
					'client_payment'                   => 45,
					'client_balance'                   => 45,
					'premium_policy_job'               => 45,
					'premium_policy_job_group'         => 45,
					'premium_policy_job_item'          => 45,
					'premium_policy_job_item_group'    => 45,
					'area_policy'                      => 45,
					'area_policy_location'             => 45,
					'government_document'              => 120,
					'document'                         => 45,
					'document_attachment'              => 45,
					'document_group'                   => 45,
					'document_group_tree'              => 45,
					'document_revision'                => 45,
					'invoice'                          => 45,
					'invoice_config'                   => 45,
					'invoice_district'                 => 45,
					'invoice_transaction'              => 90,
					'job'                              => 45,
					'job_user_allow'                   => 45,
					'job_group'                        => 45,
					'job_group_tree'                   => 45,
					'job_item'                         => 45,
					'job_item_allow'                   => 45,
					'job_item_amendment'               => 45,
					'job_item_group'                   => 45,
					'job_item_group_tree'              => 45,
					'geo_fence'                        => 45,
					'payment_gateway'                  => 45,
					'payment_gateway_currency'         => 45,
					'payment_gateway_credit_card_type' => 45,
					'product'                          => 45,
					'product_group'                    => 45,
					'product_group_tree'               => 45,
					'product_price'                    => 45,
					'shipping_policy'                  => 45,
					'shipping_table_rate'              => 45,
					'shipping_policy_object'           => 45,
					'tax_policy'                       => 45,
					'tax_policy_object'                => 45,
			];

			$purge_tables = array_merge( $purge_tables, $purge_corporate_tables );
		}

		if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
			$purge_enterprise_tables = [
					'job_vacancy'                  => 45,
					'job_applicant_location'       => 45,
					'job_applicant_employment'     => 45,
					'job_applicant_reference'      => 45,
					'job_applicant_education'      => 45,
					'job_applicant_skill'          => 45,
					'job_applicant_language'       => 45,
					'job_applicant_membership'     => 45,
					'job_applicant_license'        => 45,
					'job_applicant'                => 45,
					'job_application'              => 45,
					'recruitment_portal_config'    => 45,
					'recruitment_portal_field_map' => 45,
					'expense_policy'               => 45,
					'user_expense'                 => 45,
			];

			$purge_tables = array_merge( $purge_tables, $purge_enterprise_tables );
		}

		$current_tables = $db->MetaTables();
		$total_purged_rows = 0;

		if ( is_array( $purge_tables ) && is_array( $current_tables ) ) {
			foreach ( $purge_tables as $table => $expire_days ) {
				$total_purged_rows_by_table = 0;

				//For testing only, force expire_days to 0.
				//if ( PRODUCTION == FALSE ) {
				//	$expire_days = 0;
				//}

				$db->StartTrans(); //Try to keep the transactions as short lived as possible to avoid replication delays on the database.
				if ( in_array( $table, $current_tables ) ) {
					$query = [];
					switch ( $table ) {
						case 'user_generic_status':
							//Treat the user_generic_status table differently, as rows are never marked as deleted in it.
							//  Therefore use updated_date instead of deleted_date.
							$query[] = 'DELETE FROM ' . $table . ' WHERE updated_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) );
							break;
						case 'system_log':
							//Only delete system_log rows from deleted users, or deleted/cancelled companies
							$query[] = 'DELETE FROM ' . $table . ' as a USING users as b, company as c WHERE a.user_id = b.id AND b.company_id = c.id AND ( b.deleted = 1 OR c.deleted = 1 OR c.status_id = 30 ) AND ( a.date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND b.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND c.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';

							//Quite a few system_log rows are created by user_id=0 (system), they aren't shown on the audit log anyways and don't need to be kept around for long.
							//This also deletes logs that can't be matches to any user, or those that have already been deleted.
							//This includes log entries for the cron system.
							//$query[] = 'DELETE FROM '. $table .' WHERE id in ( select a.id from '. $table .' as a LEFT JOIN users as b ON a.user_id = b.id WHERE b.id is NULL AND ( a.date <= '. (time()-(86400*($expire_days))) .' ) )';
							//NOTE: Make sure NOT EXISTS is a strict join query without any other where clauses, as that can cause unintended results.
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE a.date <= ' . ( time() - ( 86400 * ( ( $expire_days * 2 ) ) ) ) . ' AND NOT EXISTS ( select 1 from users as b WHERE a.user_id = b.id )';

							//Delete non-critical audit log entries that bloat the database
							// 45 Days
							//  - Station ( Allowed )

							// 180 Days
							//  - Authentication
							//  - UserDateTotal ( Notice - Recalculating )
							//  - Exception ( Notice - Emails )
							//  - ReportSchedule ( Notice - Emailing )
							//  - Punch ( Telephone Start records )

							// 1.5 Years
							//  - PayStubAmendment ( Edit )
							//  - PunchControl ( Add )
							//  - Punch ( Add )
							//  - Schedule ( Add )
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE
								(
									a.date <= ' . ( time() - ( 86400 * ( ( $expire_days * 1 ) ) ) ) . '
									AND (
											( table_name = \'station\' AND action_id = 200 )
										)
								)
								OR
								(
									a.date <= ' . ( time() - ( 86400 * ( ( $expire_days * 4 ) ) ) ) . '
									AND (
											( table_name = \'authentication\' )
											OR
											( table_name = \'user_date_total\' AND action_id = 500 )
											OR
											( table_name = \'exception\' AND action_id = 500 )
											OR
											( table_name = \'report_schedule\' AND action_id = 500 )
											OR
											( table_name = \'punch\' AND ( action_id = 500 AND description LIKE \'Telephone Punch Start%\' ) )
										)
								)
								OR
								(
									a.date <= ' . ( time() - ( 86400 * ( ( $expire_days * 12 ) ) ) ) . '
									AND (
											( table_name = \'pay_stub_amendment\' AND action_id = 20 )
											OR
											( table_name = \'punch_control\' AND action_id = 10 )
											OR
											( table_name = \'punch\' AND action_id = 10 )
											OR
											( table_name = \'schedule\' AND action_id = 10 )
										)
								)
								';
							break;
						case 'system_log_detail':
							//Only delete system_log_detail rows when the corresponding system_log rows are already deleted
							//$query[] = 'DELETE FROM '. $table .' WHERE id in ( select a.id from '. $table .' as a LEFT JOIN system_log as b ON a.system_log_id = b.id WHERE b.id is NULL )';
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE NOT EXISTS ( select 1 from system_log as b WHERE a.system_log_id = b.id )';
							break;
						case 'punch':
							//Delete punch rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM ' . $table . ' as a USING punch_control as b, users as d, company as e WHERE a.punch_control_id = b.id AND b.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR b.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( a.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND d.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND e.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';
							break;
						case 'punch_control':
						case 'user_date_total':
						case 'schedule':
						case 'request':
							//Delete punch_control/user_date rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM ' . $table . ' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( a.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND d.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND e.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';
							break;
						case 'exception':
							//Delete exception rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM ' . $table . ' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( a.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND d.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND e.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';

							//Delete exception rows from terminated users after they have been terminated for 3x the regular expire length.
							//  This has to use updated_date rather than deleted_date in this case then.
							$query[] = 'DELETE FROM ' . $table . ' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( d.status_id = 20 ) AND ( a.updated_date <= ' . ( time() - ( 86400 * ( $expire_days * 3 ) ) ) . ' AND d.updated_date <= ' . ( time() - ( 86400 * ( $expire_days * 3 ) ) ) . ' AND e.updated_date <= ' . ( time() - ( 86400 * ( $expire_days * 3 ) ) ) . ')';
							break;
						case 'user_identification':
							//Purge biometric data from terminated employees within 45 days (expire time) of their termination date.
							$query[] = 'UPDATE ' . $table . ' as a SET deleted = 1, updated_date = ' . time() . ', deleted_date = ' . time() . ', deleted_by = ' . $db->qstr( TTUUID::getZeroID() ) . ' FROM users as b WHERE a.user_id = b.id AND a.type_id in (20, 70, 71, 75, 76, 77, 78, 79, 80, 100, 101) AND b.status_id = 20 AND b.termination_date <= ' . $db->qstr( TTDate::getDBTimeStamp( ( time() - ( 86400 * ( $expire_days ) ) ) ) ) . ' AND a.deleted = 0 AND b.deleted = 0';
							//No break here, as it needs to continue through to message_recipient.
						case 'pay_period_time_sheet_verify':
						case 'message_sender':
						case 'message_recipient':
							//Delete rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM ' . $table . ' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( a.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND d.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND e.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';
							break;
						case 'accrual_balance':
							//Delete rows from deleted users, or deleted companies. Accrual Balance table does not have deleted_date column.
							$query[] = 'DELETE FROM ' . $table . ' as a USING users as d, company as e WHERE a.user_id = d.id AND d.company_id = e.id AND ( a.deleted = 1 OR d.deleted = 1 OR e.deleted = 1 ) AND ( d.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND e.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';
							break;
						case 'pay_stub_entry':
							//Only delete pay_stub_entry rows from deleted users, or deleted companies
							$query[] = 'DELETE FROM ' . $table . ' as a USING pay_stub as b WHERE a.pay_stub_id = b.id AND ( a.deleted = 1 OR b.deleted = 1 ) AND a.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) );
							break;
						case 'authorizations':
							//Only delete authorization rows from deleted requests.
							//  Make sure we try still try to purge old authorization type_ids (10,20,30), as they could be left laying around from long-time customers.
							$query[] = 'DELETE FROM ' . $table . ' as a USING request as b WHERE a.object_type_id in (10, 20, 30, 50, 1010, 1020, 1030, 1040, 1100) AND a.object_id = b.id AND ( b.deleted = 1 ) AND ( b.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND a.updated_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';

							$query[] = 'DELETE FROM ' . $table . ' as a WHERE a.object_type_id in (10, 20, 30, 50, 1010, 1020, 1030, 1040, 1100) AND NOT EXISTS ( select 1 from request as b WHERE a.object_id = b.id)';
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE a.object_type_id in (90) AND NOT EXISTS ( select 1 from pay_period_time_sheet_verify as b WHERE a.object_id = b.id)';

							if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
								$query[] = 'DELETE FROM ' . $table . ' as a WHERE a.object_type_id in (200) AND NOT EXISTS ( select 1 from user_expense as b WHERE a.object_id = b.id)';
							}
							break;
						case 'notification':
							$query[] = 'DELETE FROM ' . $table . ' WHERE deleted = 1 AND deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) );

							//Delete *non-deleted* read notifications after $expire_days * 1, and unread notifications after $expire_days * 2. To prevent them from piling up especially if the user hasn't logged in for a while.
							//  Make sure effective date is before $expire_days as well, so the user at least has a chance to read a future dated notification.
							//  Since these are non-deleted records, use updated_date instead of deleted_date.
							$query[] = 'DELETE FROM ' . $table . ' WHERE deleted = 0 AND effective_date <= ' . $db->qstr( TTDate::getDBTimeStamp( ( time() - ( 86400 * ( $expire_days ) ) ) ) ) .' AND ( ( status_id = 10 AND updated_date <= ' . ( time() - ( 86400 * ( $expire_days * 2 ) ) ) .' ) OR ( status_id = 20 AND updated_date <= ' . ( time() - ( 86400 * ( $expire_days * 1 ) ) ) .' ) )'; //10=UnRead, 20=Read
							break;
						case 'station':
							//Delete disabled stations that haven't been used (allowed_date) or updated or used by a punch in over 1 year. Only consider PC/Mobile Browser stations types though.
							//Problem is when a station is created, a punch may be assigned to it, but the allowed_date is update on the wildcard entry instead.
							$query[] = 'UPDATE ' . $table . ' a SET deleted = 1, deleted_date = EXTRACT( EPOCH FROM NOW() ) FROM ( SELECT a.id FROM station a LEFT JOIN ( SELECT station_id, MAX( time_stamp ) as last_punch_time_stamp FROM punch GROUP BY station_id ) p ON a.id = p.station_id WHERE a.status_id = 10 AND a.type_id in ( 10, 26 ) AND a.deleted = 0 AND lower( a.station_id ) != \'any\' AND lower( a.source ) != \'any\' AND ( a.allowed_date is NULL OR a.allowed_date <= ' . ( time() - ( 86400 * ( 366 ) ) ) . ' ) AND ( a.updated_date <= ' . ( time() - ( 86400 * ( 366 ) ) ) . ' ) AND p.last_punch_time_stamp < \'' . TTDate::getDBTimeStamp( ( time() - ( 86400 * ( 366 ) ) ) ) . '\' ) AS tmp WHERE a.id = tmp.id';

							//Updating the punch table isn't required (and is really slow), as station records can't be joined after they deleted anyways.
							//$query[] = 'UPDATE punch as a SET station_id = NULL WHERE station_id IS NOT NULL AND station_id != \'00000000-0000-0000-0000-000000000000\' AND NOT EXISTS ( SELECT 1 FROM station as b WHERE a.station_id = b.id AND b.deleted = 1 )';

							//Delete disabled stations that have never had a punch assigned to them.
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE a.status_id = 10 AND a.type_id in (10, 26) AND ( lower(a.station_id) != \'any\' AND lower(a.source) != \'any\' ) AND NOT EXISTS ( select 1 from punch as b WHERE a.id = b.station_id ) AND ( a.updated_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' ) AND a.deleted = 0';

							//Delete station rows from deleted/cancelled companies
							$query[] = 'DELETE FROM ' . $table . ' as a USING company as e WHERE a.company_id = e.id AND ( a.deleted = 1 OR e.deleted = 1 OR e.status_id = 30 ) AND ( a.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND e.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';

							//Disable iButton/Fingerprint/Barcode stations that have never been used.
							//  Use updated_date instead of deleted_date here as they records haven't been deleted yet.
							$query[] = 'UPDATE ' . $table . ' SET status_id = 10 WHERE type_id in (30, 40, 50) AND status_id = 20 AND allowed_date is NULL AND updated_date <= ' . ( time() - ( 86400 * ( 120 ) ) ) . ' AND ( deleted = 0 )';
							break;
						case 'permission_control':
							// Use updated_date instead of deleted_date only for table "a." as we are deleting records where the company is deleted and not where the record itself may be deleted.
							$query[] = 'DELETE FROM ' . $table . ' as a USING company as c WHERE a.company_id = c.id AND ( c.deleted = 1 ) AND ( a.updated_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ' AND c.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';
							break;
						case 'permission':
							$query[] = 'DELETE FROM ' . $table . ' WHERE id in ( select a.id from ' . $table . ' as a LEFT JOIN permission_control as b ON a.permission_control_id = b.id WHERE b.id is NULL )';
							break;
						case 'message_control':
							$query[] = 'DELETE FROM ' . $table . ' WHERE id in ( select a.id from ' . $table . ' as a LEFT JOIN message_sender as b ON a.id = b.message_control_id WHERE b.id is NULL )';
							break;
						case 'bank_account':
						case 'user_generic_data':
						case 'user_report_data':
							//Delete all rows that are already deleted, since this isn't done in the DEFAULT case statement below
							$query[] = 'DELETE FROM ' . $table . ' WHERE deleted = 1 AND deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) );

							//user_id column can be NULL for company wide data, make sure we leave that alone.
							$query[] = 'DELETE FROM ' . $table . ' as a USING company as b WHERE a.company_id = b.id AND ( b.deleted = 1 AND b.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';
							$query[] = 'DELETE FROM ' . $table . ' as a USING users as b WHERE a.user_id = b.id AND ( b.deleted = 1 AND b.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';

							//Delete rows where the parent table rows are already deleted.
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE NOT EXISTS ( select 1 from company as b WHERE a.company_id = b.id )';

							//bank_account can have user_id is NULL or user_id = 0, we don't want to purge those records in either case.
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE ( a.user_id is NOT NULL AND a.user_id != \'' . TTUUID::getZeroID() . '\' ) AND NOT EXISTS ( select 1 from users as b WHERE a.user_id = b.id )';
							break;
						case 'user_group_tree':
						case 'document_group_tree':
						case 'client_group_tree':
						case 'job_group_tree':
						case 'job_item_group_tree':
						case 'product_group_tree':
						case 'qualification_group_tree':
						case 'kpi_group_tree':
							$parent_table = str_replace( '_tree', '', $table );
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE NOT EXISTS ( select 1 from ' . $parent_table . ' as b WHERE a.object_id = b.id)';
							break;
						case 'authentication':
							$query[] = 'DELETE FROM ' . $table . ' as a USING users as b WHERE a.object_id = b.id AND a.type_id NOT IN (100,110) AND ( b.deleted = 1 AND b.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';
							break;
						case 'company_generic_tag_map':
							//Tag mapping is specific to almost every other major table, so need to handle it one at a time.
							$table_arr = $db->MetaTables(); //Get list of tables, so we don't cause SQL errors if the table doesn't exist.

							$cgtf = TTnew( 'CompanyGenericTagFactory' ); /** @var CompanyGenericTagFactory $cgtf */
							$object_type_arr = $cgtf->getOptions( 'object_type' );
							foreach ( $object_type_arr as $object_type_id => $object_type ) {
								if ( in_array( $object_type, $table_arr ) && isset( $purge_tables[$object_type] ) ) {
									$query[] = 'DELETE FROM ' . $table . ' as a WHERE a.object_type_id = ' . $object_type_id . ' AND NOT EXISTS ( select 1 from ' . $object_type . ' as b WHERE a.object_id = b.id)';
								} else {
									Debug::Text( '  WARNING: Table does not exist or is not in the purge tables array: ' . $object_type, __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
							unset( $cgtf, $object_type_arr, $object_type, $table_arr );
							break;
						case 'company_generic_tag':
							$query[] = 'DELETE FROM ' . $table . ' as a WHERE NOT EXISTS ( select 1 from company_generic_tag_map as b WHERE a.id = b.tag_id)';
							break;
						//Tables that don't require custom queries, but don't have a deleted/updated_date column.
						case 'company_user_count':
						case 'permission_user':
						case 'user_default_company_deduction':
						//case 'recurring_schedule_user':
						case 'recurring_ps_amendment_user':
						case 'hierarchy_user':
						case 'hierarchy_object_type':
						case 'company_deduction_pay_stub_entry_account':
						case 'pay_period_schedule_user':
						case 'bread_crumb':
						case 'holiday_policy_recurring_holiday':
						case 'station_branch':
						case 'station_department':
						case 'station_user_group':
						case 'station_include_user':
						case 'station_exclude_user':
						case 'policy_group_user':
						case 'premium_policy_branch':
						case 'premium_policy_department':
						case 'premium_policy_job':
						case 'premium_policy_job_group':
						case 'premium_policy_job_item':
						case 'premium_policy_job_item_group':
						case 'client_balance':
						case 'tax_policy_object':
						case 'area_policy_location':
						case 'shipping_policy_object':
						case 'payment_gateway_currency':
						case 'payment_gateway_credit_card_type':
						case 'recruitment_portal_field_map':
							break;
						//Purge old tables from previous versions.
						case 'message':
						case 'hierarchy_tree':
						case 'hierarchy_share':
						case 'station_user':
						case 'job_user_allow':
						case 'job_item_allow':
							if ( version_compare( APPLICATION_VERSION, '3.5.0', '>=' ) ) {
								$query[] = 'DELETE FROM ' . $table;
							}
							break;
						default:
							Debug::Text( 'Default Query... Table: ' . $table, __FILE__, __LINE__, __METHOD__, 10 );
							$query[] = 'DELETE FROM ' . $table . ' WHERE deleted = 1 AND deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) );
							break;
					}

					//Handle orphaned data as well, based on the parent table.
					if ( isset( self::$parent_table_map_array[$table] ) ) {
						foreach ( self::$parent_table_map_array[$table] as $parent_table ) {
							if ( isset( self::$parent_table_column_map[$parent_table] ) ) {
								$parent_table_column = self::$parent_table_column_map[$parent_table];
							} else {
								$parent_table_column = $parent_table . '_id';
							}
							Debug::Text( 'Parent Table: ' . $parent_table . ' Parent Table Column: ' . $parent_table_column, __FILE__, __LINE__, __METHOD__, 10 );

							//Skip some tables without deleted columns.
							// remittance_source_account can be assigned to 'ANY' legal entity (Not Exists UUID), so make sure we don't purge data in it.
							if ( !in_array( $table, [ 'remittance_source_account', 'bank_account', 'user_generic_data', 'user_report_data', 'system_log', 'system_log_detail', 'authorizations' ] ) ) {
								//Delete rows where the parent table rows are already marked as deleted.
								$query[] = 'DELETE FROM ' . $table . ' as a USING ' . $parent_table . ' as b WHERE a.' . $parent_table_column . ' = b.id AND ( b.deleted = 1 AND b.deleted_date <= ' . ( time() - ( 86400 * ( $expire_days ) ) ) . ')';

								//Delete rows where the parent table rows are already deleted.
								//Keep records where ID = 0 or NULL as those can still be valid in some cases.
								$query[] = 'DELETE FROM ' . $table . ' as a WHERE a.' . $parent_table_column . ' != \'' . TTUUID::getZeroID() . '\' AND a.' . $parent_table_column . ' != \'' . TTUUID::getNotExistID() . '\' AND a.' . $parent_table_column . ' is NOT NULL AND NOT EXISTS ( SELECT 1 FROM ' . $parent_table . ' as b WHERE a.' . $parent_table_column . ' = b.id )';
							}

							unset( $parent_table_column, $parent_table );
						}
					}

					//FIXME: With new punch method in v3.0 add query to make sure orphaned punches without punch_control rows are cleaned out
					//select a.id, a.deleted, b.id, b.deleted from punch as a LEFT JOIN punch_control as b ON (a.punch_control_id = b.id) WHERE b.id is NULL AND a.deleted = 0;
					if ( isset( $query ) && is_array( $query ) ) {
						$i = 0;
						foreach ( $query as $q ) {
							//echo "Query: ". $q ."\n"; //Help with debugging SQL syntax errors.
							$db->Execute( $q );
							Debug::Text( 'Query: ' . $q, __FILE__, __LINE__, __METHOD__, 10 );
							Debug::Text( 'Table found for purging: ' . $table . '(' . $i . ') Expire Days: ' . $expire_days . ' Purged Rows: ' . $db->Affected_Rows(), __FILE__, __LINE__, __METHOD__, 10 );
							$total_purged_rows += $db->Affected_Rows();
							$total_purged_rows_by_table += $db->Affected_Rows();
							$i++;
						}
					}
					unset( $query );
				} else {
					Debug::Text( 'Table not found for purging: ' . $table, __FILE__, __LINE__, __METHOD__, 10 );
				}

				//$db->FailTrans();
				$db->CompleteTrans();

				//Since deleting a lot of records especially in system_log can cause large planning times of queries that select from that table. This manifested itself in the Audit tab taking 2-3 seconds to load.
				// Therefore force a regular vacuum to run on each table that a significant number of rows were deleted from. We could also tune the autovacuum parameters more for these tables to be extra sure.
				if ( $total_purged_rows_by_table > 10000 ) {
					Debug::text( '  Start VACUUM... Total Purge Rows in Table: '. $total_purged_rows_by_table .' Current Time: '. TTDate::getDate('DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
					$db->Execute( 'VACUUM ANALYZE '. $table );
					Debug::text( '  Done VACUUM... Total Purge Rows in Table: '. $total_purged_rows_by_table .' Current Time: '. TTDate::getDate('DATE+TIME', time() ), __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			//
			//Purge saved punch images.
			//
			if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
				//This doesn't need to be wrapped in a transaction, and can take quite a while due to disk I/O. Doing so can harm the database performance, especially with replication.
				Debug::Text( 'Purging Punch Images...', __FILE__, __LINE__, __METHOD__, 10 );

				$plf = new PunchListFactory();
				$total_records = -1;
				$x = 0;
				$i = 0;
				while ( $total_records == -1 || ( $total_records > 0 && $x <= 250 ) ) { //Limit to 10 batches in total.
					$db->StartTrans();                                                   //Try to keep the transactions as short lived as possible to avoid replication delays on the database.

					$plf->getByHasImageAndCreatedDate( true, ( time() - ( 86400 * 120 ) ), 1000 ); //Expire Days: 120 -- Limit to 500 at a time, as its mostly the file I/O that takes the time here.
					$total_records = $plf->getRecordCount();
					Debug::text( 'Batch: ' . $x . ' Punches with images older than X days: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $plf->getRecordCount() > 0 ) {
						foreach ( $plf as $p_obj ) {
							Debug::text( '  Punch ID: ' . $p_obj->getID() . ' Date: ' . TTDate::getDate( 'DATE+TIME', $p_obj->getTimeStamp() ) . ' Image File Name: ' . $p_obj->getImageFileName(), __FILE__, __LINE__, __METHOD__, 10 );
							$query = 'UPDATE ' . $plf->getTable() . ' SET has_image = 0 WHERE id = \'' . TTUUID::castUUID( $p_obj->getID() ) . '\'';
							if ( $plf->ExecuteSQL( $query ) !== false ) {
								$p_obj->cleanStoragePath();
							} else {
								Debug::text( '  ERROR: Update query to purge has_image failed... ID: ' . $p_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
							}
							$i++;
						}
					}

					//$db->FailTrans();
					$db->CompleteTrans();

					$x++;
				}
				Debug::text( '  Deleted Punch Images: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );
				unset( $plf, $p_obj, $total_records, $x, $i, $query );
			}
		}
		unset( $purge_tables, $current_tables, $query );
		Debug::Text( 'Purging database tables complete: ' . TTDate::getDate( 'DATE+TIME', time() ) .' Total Purged Rows: '. $total_purged_rows, __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/** @noinspection PhpUndefinedFunctionInspection */
	static function getParentTableMap() {
		global $db;

		//Get all tables in a database and their ID columns to aid in creating export/import mapping
		$exclude_columns = [ 'id', 'type_id', 'status_id', 'length_of_service_unit_id', 'apply_frequency_id', 'category_id', 'custom_field', 'ibutton_id', 'manual_id', 'exclusive_id', 'session_id', 'cc_type_id', 'originator_id', 'data_center_id', 'product_edition_id', 'calculation_id', 'severity_id', 'email_notification_id', 'default_schedule_status_id', 'phone_id', 'sex_id' ];

		$table_name_map = [];
		$table_name_map['user'] = 'users';

		$dict = NewDataDictionary( $db );
		$tables = $dict->MetaTables();
		sort( $tables );
		$map = [];
		foreach ( $tables as $table ) {
			$columns = $dict->MetaColumns( $table );

			foreach ( $columns as $column_table ) {
				$column_name = $column_table->name;
				if ( !in_array( $column_name, $exclude_columns ) && stristr( $column_name, '_id' ) ) {
					//Find out where the column maps too.
					$tmp_table_name = str_replace( '_id', '', $column_name );
					if ( isset( $table_name_map[$tmp_table_name] ) ) {
						$tmp_table_name = $table_name_map[$tmp_table_name];
					}

					if ( in_array( $tmp_table_name, $tables ) ) {
						//Found destination table.
						$map[$table][] = $tmp_table_name;
					} else {
						echo "UNABLE TO FIND DESTINATION TABLE FOR: Table: " . $table . " Column: " . $column_name . "<br>\n";
					}
				}
			}
		}
		//echo $out;
		//var_dump($test);
		//asort($map);
		foreach ( $map as $tmp_key => $tmp_val ) {
			echo "'$tmp_key' => array(\n\t\t\t\t'" . implode( "',\n\t\t\t\t'", $tmp_val ) . "'\n\t\t\t\t), \n";
			//echo "'$tmp_key' => '$tmp_val', \n";
		}
	}
}


?>
