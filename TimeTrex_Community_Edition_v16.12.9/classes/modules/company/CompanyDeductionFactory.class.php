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
 * @package Modules\Company
 */
class CompanyDeductionFactory extends Factory {
	protected $table = 'company_deduction';
	protected $pk_sequence_name = 'company_deduction_id_seq'; //PK Sequence name

	var $company_obj = null;
	var $legal_entity_obj = null;
	var $payroll_remittance_agency_obj = null;
	var $pay_stub_entry_account_link_obj = null;
	var $pay_stub_entry_account_obj = null;
	var $users = null;

	var $include_psea_ids = []; //Used in TaxSummaryReport
	var $exclude_psea_ids = []; //Used in TaxSummaryReport
	var $tax_withheld_psea_ids = []; //Used in TaxSummaryReport
	var $user_ids = []; //Used in TaxSummaryReport
	var $duplicate_of = null; //Used in TaxSummaryReport

	var $lookback_pay_stub_lf = null;

	var $country_calculation_ids = [ '100', '200', '210', '300' ];
	var $province_calculation_ids = [ '200', '210', '300' ];
	var $district_calculation_ids = [ '300' ];

	protected $length_of_service_multiplier = [
			0  => 0,
			10 => 1,
			20 => 7,
			30 => 30.4167,
			40 => 365.25,
			50 => 0.04166666666666666667, //1/24th of a day.
	];

	protected $account_amount_type_map = [
			10 => 'amount',
			20 => 'units',
			30 => 'ytd_amount',
			40 => 'ytd_units',
	];

	protected $account_amount_type_ps_entries_map = [
			10 => 'current',
			20 => 'current',
			30 => 'previous+ytd_adjustment',
			40 => 'previous+ytd_adjustment',
	];

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'status' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'type' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'calculation_id' )->setFunctionMap( 'Calculation' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'calculation' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'payroll_remittance_agency_id' )->setFunctionMap( 'PayrollRemittanceAgency' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'payroll_remittance_agency' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'legal_entity_id' )->setFunctionMap( 'LegalEntity' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'legal_entity_legal_name' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),

							TTSCol::new( 'filter_date_type_id' )->setFunctionMap( 'FilterDateType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'filter_date_type' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'start_date' )->setFunctionMap( 'StartDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'end_date' )->setFunctionMap( 'EndDate' )->setType( 'timestamp' )->setIsNull( true ),

							TTSCol::new( 'minimum_length_of_service_unit_id' )->setFunctionMap( 'MinimumLengthOfServiceUnit' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'minimum_length_of_service_days' )->setFunctionMap( 'MinimumLengthOfServiceDays' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'minimum_length_of_service' )->setFunctionMap( 'MinimumLengthOfService' )->setType( 'numeric' )->setIsNull( true ),

							TTSCol::new( 'maximum_length_of_service_unit_id' )->setFunctionMap( 'MaximumLengthOfServiceUnit' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'maximum_length_of_service_days' )->setFunctionMap( 'MaximumLengthOfServiceDays' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'maximum_length_of_service' )->setFunctionMap( 'MaximumLengthOfService' )->setType( 'numeric' )->setIsNull( true ),

							TTSCol::new( 'length_of_service_contributing_pay_code_policy_id' )->setFunctionMap( 'LengthOfServiceContributingPayCodePolicy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'length_of_service_contributing_pay_code_policy' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'minimum_user_age' )->setFunctionMap( 'MinimumUserAge' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'maximum_user_age' )->setFunctionMap( 'MaximumUserAge' )->setType( 'numeric' )->setIsNull( true ),

							TTSCol::new( 'apply_frequency_id' )->setFunctionMap( 'ApplyFrequency' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_month' )->setFunctionMap( 'ApplyFrequencyMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_day_of_month' )->setFunctionMap( 'ApplyFrequencyDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_day_of_month1' )->setFunctionMap( 'ApplyFrequencyDayOfMonth' )->setType( 'smallint' )->setIsNull( true )->setIsSynthetic( true ),
							TTSCol::new( 'apply_frequency_day_of_month2' )->setFunctionMap( 'ApplyFrequencyMonth' )->setType( 'smallint' )->setIsNull( true )->setIsSynthetic( true ),
							TTSCol::new( 'apply_frequency_day_of_week' )->setFunctionMap( 'ApplyFrequencyDayOfWeek' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_quarter_month' )->setFunctionMap( 'ApplyFrequencyQuarterMonth' )->setType( 'smallint' )->setIsNull( true ),

							TTSCol::new( 'apply_payroll_run_type_id' )->setFunctionMap( 'ApplyPayrollRunType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'pay_stub_entry_description' )->setFunctionMap( 'PayStubEntryDescription' )->setType( 'varchar' )->setIsNull( true ),

							TTSCol::new( 'calculation_order' )->setFunctionMap( 'CalculationOrder' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'country' )->setFunctionMap( 'Country' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'province' )->setFunctionMap( 'Province' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'district' )->setFunctionMap( 'District' )->setType( 'varchar' )->setIsNull( true ),

							TTSCol::new( 'company_value1' )->setFunctionMap( 'CompanyValue1' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value2' )->setFunctionMap( 'CompanyValue2' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value3' )->setFunctionMap( 'CompanyValue3' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value4' )->setFunctionMap( 'CompanyValue4' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value5' )->setFunctionMap( 'CompanyValue5' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value6' )->setFunctionMap( 'CompanyValue6' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value7' )->setFunctionMap( 'CompanyValue7' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value8' )->setFunctionMap( 'CompanyValue8' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value9' )->setFunctionMap( 'CompanyValue9' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'company_value10' )->setFunctionMap( 'CompanyValue10' )->setType( 'text' )->setIsNull( true ),

							TTSCol::new( 'user_value1' )->setFunctionMap( 'UserValue1' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value2' )->setFunctionMap( 'UserValue2' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value3' )->setFunctionMap( 'UserValue3' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value4' )->setFunctionMap( 'UserValue4' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value5' )->setFunctionMap( 'UserValue5' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value6' )->setFunctionMap( 'UserValue6' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value7' )->setFunctionMap( 'UserValue7' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value8' )->setFunctionMap( 'UserValue8' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value9' )->setFunctionMap( 'UserValue9' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value10' )->setFunctionMap( 'UserValue10' )->setType( 'varchar' )->setIsNull( true ),

							TTSCol::new( 'lock_user_value1' )->setFunctionMap( 'LockUserValue1' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value2' )->setFunctionMap( 'LockUserValue2' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value3' )->setFunctionMap( 'LockUserValue3' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value4' )->setFunctionMap( 'LockUserValue4' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value5' )->setFunctionMap( 'LockUserValue5' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value6' )->setFunctionMap( 'LockUserValue6' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value7' )->setFunctionMap( 'LockUserValue7' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value8' )->setFunctionMap( 'LockUserValue8' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value9' )->setFunctionMap( 'LockUserValue9' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'lock_user_value10' )->setFunctionMap( 'LockUserValue10' )->setType( 'smallint' )->setIsNull( false ),

							TTSCol::new( 'pay_stub_entry_account_id' )->setFunctionMap( 'PayStubEntryAccount' )->setType( 'uuid' )->setIsNull( false ),

							TTSCol::new( 'include_account_amount_type_id' )->setFunctionMap( 'IncludeAccountAmountType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'include_pay_stub_entry_account' )->setFunctionMap( 'IncludePayStubEntryAccount' )->setType( 'uuid' )->setIsSynthetic( true ),

							TTSCol::new( 'exclude_account_amount_type_id' )->setFunctionMap( 'ExcludeAccountAmountType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'exclude_pay_stub_entry_account' )->setFunctionMap( 'ExcludePayStubEntryAccount' )->setType( 'uuid' )->setIsSynthetic( true ),

							TTSCol::new( 'user' )->setType( 'uuid' )->setIsNull( true )->setIsSynthetic( true ),
							TTSCol::new( 'total_users' )->setType( 'integer' )->setIsNull( true )->setIsSynthetic( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_tax_deductions' )->setLabel( TTi18n::getText( 'Tax / Deductions' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'legal_entity_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Legal Entity' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'legal_entity' ) ),
											TTSField::new( 'payroll_remittance_agency_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Remittance Agency' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'payroll_remittance_agency' ) ),
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) )->setWidth( '100%' ),
											TTSField::new( 'pay_stub_entry_description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Pay Stub Note (Public)' ) )->setWidth( '100%' ),
											TTSField::new( 'calculation_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Calculation' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'calculation' ) ),
											TTSField::new( 'df_15' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_15' ) )->setWidth( '100%' ),
											TTSField::new( 'country' )->setType( 'text' )->setLabel( TTi18n::getText( 'Country' ) )->setWidth( '100%' ),
											TTSField::new( 'province' )->setType( 'text' )->setLabel( TTi18n::getText( 'Province/State' ) )->setWidth( '100%' ),
											TTSField::new( 'district' )->setType( 'text' )->setLabel( TTi18n::getText( 'District' ) )->setWidth( '100%' ),
											TTSField::new( 'df_0' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_0' ) )->setWidth( '100%' ),
											TTSField::new( 'df_20' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_20' ) )->setWidth( '100%' ),
											TTSField::new( 'df_14' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_14' ) )->setWidth( '100%' ),
											TTSField::new( 'df_1' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_1' ) )->setWidth( '100%' ),
											TTSField::new( 'df_2' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_2' ) )->setWidth( '100%' ),
											TTSField::new( 'df_3' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_3' ) )->setWidth( '100%' ),
											TTSField::new( 'df_4' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_4' ) )->setWidth( '100%' ),
											TTSField::new( 'df_5' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_5' ) )->setWidth( '100%' ),
											TTSField::new( 'df_6' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_6' ) )->setWidth( '100%' ),
											TTSField::new( 'df_7' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_7' ) )->setWidth( '100%' ),
											TTSField::new( 'df_8' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_8' ) )->setWidth( '100%' ),
											TTSField::new( 'df_9' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_9' ) )->setWidth( '100%' ),
											TTSField::new( 'df_10' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_10' ) )->setWidth( '100%' ),
											TTSField::new( 'df_11' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_11' ) )->setWidth( '100%' ),
											TTSField::new( 'df_21' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_21' ) )->setWidth( '100%' ),
											TTSField::new( 'df_22' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_22' ) )->setWidth( '100%' ),
											TTSField::new( 'df_23' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_23' ) )->setWidth( '100%' ),
											TTSField::new( 'df_24' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_24' ) )->setWidth( '100%' ),
											TTSField::new( 'df_25' )->setType( 'text' )->setLabel( TTi18n::getText( 'df_25' ) )->setWidth( '100%' ),
											TTSField::new( 'pay_stub_entry_account_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Pay Stub Account' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'pay_stub_entry_account' ) ),
											TTSField::new( 'calculation_order' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Calculation Order' ) ),
											TTSField::new( 'user' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Employees' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'apply_frequency_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Apply Frequency' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'apply_frequency' ) ),
											TTSField::new( 'apply_frequency_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Month' ) ),
											TTSField::new( 'apply_frequency_day_of_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Day of Month' ) ),
											TTSField::new( 'apply_frequency_day_of_month1' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Primary Day of Month' ) ),
											TTSField::new( 'apply_frequency_day_of_month2' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Secondary Day of Month' ) ),
											TTSField::new( 'apply_frequency_quarter_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Month of Quarter' ) ),
											TTSField::new( 'apply_payroll_run_type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Payroll Run Type' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'apply_payroll_run_type' ) ),
											TTSField::new( 'start_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Start Date' ) ),
											TTSField::new( 'end_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'End Date' ) ),
											TTSField::new( 'filter_date_type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Start/End Dates Based On' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'filter_date_type' ) ),
											TTSField::new( 'length_of_service_contributing_pay_code_policy_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Length Of Service Hours Based On' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'length_of_service_unit' ) ),
											TTSField::new( 'minimum_user_age' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Minimum Employee Age' ) ),
											TTSField::new( 'maximum_user_age' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Maximum Employee Age' ) ),
											TTSField::new( 'company_tax_deduction_ids' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Taxes / Deductions' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'company_tax_deduction' ) ),
									)
							),
							TTSTab::new( 'tab_eligibility' )->setLabel( TTi18n::getText( 'Eligibility' ) )->setFields(
									new TTSFields(
											TTSField::new( 'apply_frequency_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Apply Frequency' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'apply_frequency' ) ),
											TTSField::new( 'apply_frequency_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Month' ) ),
											TTSField::new( 'apply_frequency_day_of_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Day of Month' ) ),
											TTSField::new( 'apply_frequency_day_of_month1' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Primary Day of Month' ) ),
											TTSField::new( 'apply_frequency_day_of_month2' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Secondary Day of Month' ) ),
											TTSField::new( 'apply_frequency_quarter_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Month of Quarter' ) ),
											TTSField::new( 'apply_payroll_run_type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Payroll Run Type' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'apply_payroll_run_type' ) ),
											TTSField::new( 'start_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Start Date' ) ),
											TTSField::new( 'end_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'End Date' ) ),
											TTSField::new( 'filter_date_type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Start/End Dates Based On' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'filter_date_type' ) ),
											TTSField::new( 'length_of_service_contributing_pay_code_policy_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Length Of Service Hours Based On' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getOptions' )->setArg( 'length_of_service_unit' ) ),
											TTSField::new( 'minimum_user_age' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Minimum Employee Age' ) ),
											TTSField::new( 'maximum_user_age' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Maximum Employee Age' ) ),
									)
							),
							TTSTab::new( 'tab_employee_setting' )->setLabel( TTi18n::getText( 'Employee Settings' ) )->setInitCallback( 'initEmployeeSetting' )->setHTMLTemplate( '<div id="tab_employee_setting" class="edit-view-tab-outside">
								<div class="edit-view-tab" id="tab_employee_setting_content_div">
									<div class="inside-editor-div full-width-column">
										<div class="grid-div employee-setting-grid-div">
											<table id="employee_setting_grid"></table>
										</div>
									</div>
									<div class="save-and-continue-div">
										<span class="message"></span>
										<div class="save-and-continue-button-div">
											<button class="tt-button p-button p-component" type="button">
												<span class="icon"></span>
												<span class="p-button-label"></span>
											</button>
										</div>
									</div>
								</div>
							</div>' )->setDisplayOnMassEdit( false )->setSubView( true ),
					)->addAttachment()->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'payroll_remittance_agency_id' )->setType( 'uuid' )->setColumn( 'a.payroll_remittance_agency_id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'include_user_id' )->setType( 'uuid' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'exclude_user_id' )->setType( 'uuid' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'status' )->setType( 'text' )->setColumn( 'a.status_id' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'type_id' )->setType( 'numeric' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'calculation_id' )->setType( 'numeric' )->setColumn( 'a.calculation_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_stub_entry_name_id' )->setType( 'uuid' )->setColumn( 'a.pay_stub_entry_account_id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid' )->setColumn( 'a.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'a.country' ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'a.province' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getCompanyDeduction' )
									->setSummary( 'Get company deduction records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'setCompanyDeduction' )
									->setSummary( 'Add or edit company deduction records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'deleteCompanyDeduction' )
									->setSummary( 'Delete company deduction records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getCompanyDeduction' ) ),
											   ) ),
							TTSAPI::new( 'APICompanyDeduction' )->setMethod( 'getCompanyDeductionDefaultData' )
									->setSummary( 'Get default company deduction data used for creating new company deductions. Use this before calling setCompanyDeduction to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null $params
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'Enabled' ),
						20 => TTi18n::gettext( 'Disabled' ),
				];
				break;
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Tax' ),
						20 => TTi18n::gettext( 'Deduction' ),
						30 => TTi18n::gettext( 'Other' ),
				];
				break;
			case 'calculation':
				//**NOTE: When updating these, also update the column meta data map.
				$retval = [
						10 => TTi18n::gettext( 'Percent' ),

						15 => TTi18n::gettext( 'Advanced Percent' ),
						16 => TTi18n::gettext( 'Advanced Percent (w/Target)' ), //Use two variables, one for Amount Target, and one for YTD Amount Target.
						17 => TTi18n::gettext( 'Advanced Percent (Range Bracket)' ),
						18 => TTi18n::gettext( 'Advanced Percent (Tax Bracket)' ),
						19 => TTi18n::gettext( 'Advanced Percent (Tax Bracket Alt.)' ),
						20 => TTi18n::gettext( 'Fixed Amount' ),
						30 => TTi18n::gettext( 'Fixed Amount (Range Bracket)' ),

						//Accrual/YTD formulas. - This requires custom Withdraw From/Deposit To accrual feature in PS account.
						//50 => TTi18n::gettext('Accrual/YTD Percent'),
						52 => TTi18n::gettext( 'Fixed Amount (w/Target)' ),

						//US - Custom Formulas
						69 => TTi18n::gettext( 'Custom Formula' ),

						82  => TTi18n::gettext( 'US - Medicare Formula (Employee)' ),
						83  => TTi18n::gettext( 'US - Medicare Formula (Employer)' ),
						84  => TTi18n::gettext( 'US - Social Security Formula (Employee)' ),
						85  => TTi18n::gettext( 'US - Social Security Formula (Employer)' ),
						89  => TTi18n::gettext( 'US - Federal Unemployment Insurance (Employer)' ),

						//Canada - Custom Formulas CPP and EI
						90  => TTi18n::gettext( 'Canada - CPP Formula' ),
						91  => TTi18n::gettext( 'Canada - EI Formula' ),
						92  => TTi18n::gettext( 'Canada - CPP2 Formula' ),

						//Federal
						100 => TTi18n::gettext( 'Federal Income Tax Formula' ),

						//Province/State
						200 => TTi18n::gettext( 'Province/State Income Tax Formula' ),
						210 => TTi18n::gettext( 'US - State Unemployment Insurance (Employer)' ),

						//Sub-State/Tax Area - Local, City, School District, County, etc...
						300 => TTi18n::gettext( 'Local (City/District/County) Income Tax Formula' ),

						//500 => TTi18n::gettext( 'Negative Net Pay Resolution' ),
				];
				break;
			case 'calculation_type_column_meta_data': //Meta data for each field based on the calculation type.
				if ( isset( $params['calculation_id'] ) ) {
					switch ( $params['calculation_id'] ) {
						case 10: // => TTi18n::gettext( 'Percent' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Percent' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
							];
							break;
						case 15: // => TTi18n::gettext( 'Advanced Percent' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Percent' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
									'user_value2' => [ 'name' => TTi18n::gettext( 'Annual Wage Base/Maximum Earnings' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
									'user_value3' => [ 'name' => TTi18n::gettext( 'Annual Deduction Amount' ), 'dynamic_field_id' => 'df_2', 'type_id' => 420 ],
							];
							break;
						case 16: // => TTi18n::gettext( 'Advanced Percent (w/Target)' ), //Use two variables, one for Amount Target, and one for YTD Amount Target.
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Percent' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
									'user_value2' => [ 'name' => TTi18n::gettext( 'Target Amount Limit' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
									'user_value3' => [ 'name' => TTi18n::gettext( 'Target YTD Limit/Balance' ), 'dynamic_field_id' => 'df_2', 'type_id' => 420 ],
							];
							break;
						case 17: // => TTi18n::gettext( 'Advanced Percent (Range Bracket)' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Percent' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
									'user_value2' => [ 'name' => TTi18n::gettext( 'Annual Amount Greater Than' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
									'user_value3' => [ 'name' => TTi18n::gettext( 'Annual Amount Less Than' ), 'dynamic_field_id' => 'df_2', 'type_id' => 420 ],
									'user_value4' => [ 'name' => TTi18n::gettext( 'Annual Deduction Amount' ), 'dynamic_field_id' => 'df_3', 'type_id' => 420 ],
									'user_value5' => [ 'name' => TTi18n::gettext( 'Annual Fixed Amount' ), 'dynamic_field_id' => 'df_4', 'type_id' => 420 ],
							];
							break;
						case 18: // => TTi18n::gettext( 'Advanced Percent (Tax Bracket)' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Percent' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
									'user_value2' => [ 'name' => TTi18n::gettext( 'Annual Wage Base/Maximum Earnings' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
									'user_value3' => [ 'name' => TTi18n::gettext( 'Annual Exempt Amount' ), 'dynamic_field_id' => 'df_2', 'type_id' => 420 ],
									'user_value4' => [ 'name' => TTi18n::gettext( 'Annual Deduction Amount' ), 'dynamic_field_id' => 'df_3', 'type_id' => 420 ],
							];
							break;
						case 19: // => TTi18n::gettext( 'Advanced Percent (Tax Bracket Alt.)' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Percent' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
									'user_value2' => [ 'name' => TTi18n::gettext( 'Annual Amount Greater Than' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
									'user_value3' => [ 'name' => TTi18n::gettext( 'Annual Amount Less Than' ), 'dynamic_field_id' => 'df_2', 'type_id' => 420 ],
									'user_value4' => [ 'name' => TTi18n::gettext( 'Annual Deduction Amount' ), 'dynamic_field_id' => 'df_3', 'type_id' => 420 ],
									'user_value5' => [ 'name' => TTi18n::gettext( 'Annual Fixed Amount' ), 'dynamic_field_id' => 'df_4', 'type_id' => 420 ],
							];
							break;
						case 20: // => TTi18n::gettext( 'Fixed Amount' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Amount' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
							];
							break;
						case 30: // => TTi18n::gettext( 'Fixed Amount (Range Bracket)' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Amount' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
									'user_value2' => [ 'name' => TTi18n::gettext( 'Annual Amount Greater Than' ), 'dynamic_field_id' => 'df_2', 'type_id' => 420 ],
									'user_value3' => [ 'name' => TTi18n::gettext( 'Annual Amount Less Than' ), 'dynamic_field_id' => 'df_3', 'type_id' => 420 ],
									'user_value4' => [ 'name' => TTi18n::gettext( 'Annual Deduction Amount' ), 'dynamic_field_id' => 'df_4', 'type_id' => 420 ],
							];
							break;
						case 52: // => TTi18n::gettext( 'Fixed Amount (w/Target)' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Amount' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
									'user_value2' => [ 'name' => TTi18n::gettext( 'Target YTD Limit/Balance' ), 'dynamic_field_id' => 'df_2', 'type_id' => 420 ],
							];
							break;
						case 69: // => TTi18n::gettext( 'Custom Formula' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Custom Variable 1' ), 'dynamic_field_id' => 'df_1', 'type_id' => 100 ],
									'user_value2' => [ 'name' => TTi18n::gettext( 'Custom Variable 2' ), 'dynamic_field_id' => 'df_2', 'type_id' => 100 ],
									'user_value3' => [ 'name' => TTi18n::gettext( 'Custom Variable 3' ), 'dynamic_field_id' => 'df_3', 'type_id' => 100 ],
									'user_value4' => [ 'name' => TTi18n::gettext( 'Custom Variable 4' ), 'dynamic_field_id' => 'df_4', 'type_id' => 100 ],
									'user_value5' => [ 'name' => TTi18n::gettext( 'Custom Variable 5' ), 'dynamic_field_id' => 'df_5', 'type_id' => 100 ],
									'user_value6' => [ 'name' => TTi18n::gettext( 'Custom Variable 6' ), 'dynamic_field_id' => 'df_6', 'type_id' => 100 ],
									'user_value7' => [ 'name' => TTi18n::gettext( 'Custom Variable 7' ), 'dynamic_field_id' => 'df_7', 'type_id' => 100 ],
									'user_value8' => [ 'name' => TTi18n::gettext( 'Custom Variable 8' ), 'dynamic_field_id' => 'df_8', 'type_id' => 100 ],
									'user_value9' => [ 'name' => TTi18n::gettext( 'Custom Variable 9' ), 'dynamic_field_id' => 'df_9', 'type_id' => 100 ],
									'user_value10' => [ 'name' => TTi18n::gettext( 'Custom Variable 10' ), 'dynamic_field_id' => 'df_10', 'type_id' => 100 ],
							];
							break;
						case 82: //  => TTi18n::gettext( 'US - Medicare Formula (Employee)' ),
							$retval = [];
							break;
						case 83: //  => TTi18n::gettext( 'US - Medicare Formula (Employer)' ),
							$retval = [];
							break;
						case 84: //  => TTi18n::gettext( 'US - Social Security Formula (Employee)' ),
							$retval = [];
							break;
						case 85: //  => TTi18n::gettext( 'US - Social Security Formula (Employer)' ),
							$retval = [];
							break;
						case 89: //  => TTi18n::gettext( 'US - Federal Unemployment Insurance (Employer)' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Unemployment Rate' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
							];
							break;
						case 90: //  => TTi18n::gettext( 'Canada - CPP Formula' ),
						case 92: //  => TTi18n::gettext( 'Canada - CPP2 Formula' ),
							$retval = [];
							break;
						case 91: //  => TTi18n::gettext( 'Canada - EI Formula' ),
							$retval = [];
							break;
						case 100: // => TTi18n::gettext( 'Federal Income Tax Formula' ),
							if ( isset( $params['country'] ) ) {
								switch( strtoupper( $params['country'] ) ) {
									case 'CA':
										$retval = [
												'user_value1' => [ 'name' => TTi18n::gettext( 'Claim Amount' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
										];
										break;
									case 'CR':
										$retval = [
												'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_basic_filing_status')  ],
												'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
										];
										break;
									case 'US':
										$retval = [
												'user_value9' => [ 'name' => TTi18n::gettext( 'Form W-4<br>Version' ), 'dynamic_field_id' => 'df_20', 'type_id' => 2100, 'width' => 60, 'default_value' => 2019, 'multi_select_items' => $this->getOptions('form_w4_version') ],

												'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'width' => 100, 'multi_select_items' => $this->getOptions('federal_filing_status') ],
												'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400, 'width' => 50 ],

												'user_value3' => [ 'name' => TTi18n::gettext( 'Multiple Jobs or<br> Spouse Works' ), 'dynamic_field_id' => 'df_21', 'type_id' => 2100, 'width' => 60, 'multi_select_items' => $this->getOptions('yes_no') ],
												'user_value4' => [ 'name' => TTi18n::gettext( 'Claim<br>Dependents' ), 'dynamic_field_id' => 'df_22', 'type_id' => 420, 'width' => 50 ],
												'user_value5' => [ 'name' => TTi18n::gettext( 'Other<br>Income' ), 'dynamic_field_id' => 'df_23', 'type_id' => 420, 'width' => 50 ],
												'user_value6' => [ 'name' => TTi18n::gettext( 'Deductions' ), 'dynamic_field_id' => 'df_24', 'type_id' => 420, 'width' => 50 ],
												'user_value7' => [ 'name' => TTi18n::gettext( 'Extra<br>Withholding' ), 'dynamic_field_id' => 'df_25', 'type_id' => 420, 'width' => 50 ],

												//This is not displayed on CompanyDeduction view, but only Employee Settings view.
												//'user_value10' => [ 'name' => TTi18n::gettext( 'Exempt' ), 'dynamic_field_id' => 'df_1', 'type_id' => 2100, 'width' => 30, 'multi_select_items' => $this->getOptions('yes_no') ],
										];
										break;
								}
							}
							break;
						case 200: // => TTi18n::gettext( 'Province/State Income Tax Formula' ),
							if ( isset( $params['country'] ) ) {
								switch( strtoupper( $params['country'] ) ) {
									case 'CA':
										$retval = [
												'user_value1' => [ 'name' => TTi18n::gettext( 'Claim Amount' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
										];
										break;
									case 'US':
										if ( isset( $params['province'] ) ) {
											switch( strtoupper( $params['province'] ) ) {
												case 'AL':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_al_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Dependents' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'AK': //No state tax.
													break;
												case 'AZ':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Percent' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
													];
													break;
												case 'CA':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_ca_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'CO':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('federal_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Annual Allowance' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ], //Dollars
													];
													break;
												case 'CT':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_ct_filing_status') ],
													];
													break;
												case 'DC':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_dc_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'DE':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_de_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'FL': //No state tax.
													break;
												case 'NJ':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_nj_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'NM':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('federal_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'NC':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_nc_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'MA':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_ma_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'ME':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_basic_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'MD':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_dc_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
															'user_value3' => [ 'name' => TTi18n::gettext( 'County Rate' ), 'dynamic_field_id' => 'df_2', 'type_id' => 410 ],
													];
													break;
												case 'MO':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'width' => 100, 'multi_select_items' => $this->getOptions('federal_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400, 'width' => 50 ],
													];
													break;
												case 'MS':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_dc_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Exemption Claimed' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'MT':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'width' => 100, 'multi_select_items' => $this->getOptions('federal_filing_status') ],
															//'user_value7' => [ 'name' => TTi18n::gettext( 'Extra<br>Withholding' ), 'dynamic_field_id' => 'df_25', 'type_id' => 420, 'width' => 50 ], //Handle with Additional withholding Tax/Deduction instead.
													];
													break;
												case 'OK':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_basic_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'GA':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_ga_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Employee / Spouse Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
															'user_value3' => [ 'name' => TTi18n::gettext( 'Dependent Allowances' ), 'dynamic_field_id' => 'df_2', 'type_id' => 400 ],
													];
													break;
												case 'IL':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'IL-W-4 Line 1' ), 'dynamic_field_id' => 'df_1', 'type_id' => 420 ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'IL-W-4 Line 2' ), 'dynamic_field_id' => 'df_2', 'type_id' => 420 ],
													];
													break;
												case 'KY':
													break;
												case 'AR':
												case 'IA':
												case 'MI':
												case 'SC':
												case 'OH':
													$retval = [
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'VA':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Age 65/Blind' ), 'dynamic_field_id' => 'df_2', 'type_id' => 400 ],
													];
													break;
												case 'IN':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Personal Exemptions' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Dependent Exemptions' ), 'dynamic_field_id' => 'df_2', 'type_id' => 400 ],
													];
													break;
												case 'LA':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_la_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Exemptions' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
															'user_value3' => [ 'name' => TTi18n::gettext( 'Dependents' ), 'dynamic_field_id' => 'df_2', 'type_id' => 400 ],
													];
													break;
												case 'NV':
													break;
												case 'NH':
													break;
												case 'PA':
													break;
												case 'SD':
													break;
												case 'TN':
													break;
												case 'TX':
													break;
												case 'WA':
													break;
												case 'HI':
												case 'ID':
												case 'MN':
												case 'NE':
												case 'NY':
												case 'ND':
												case 'OK':
												case 'OR':
												case 'RI':
												case 'UT':
												case 'VT':
												case 'WI':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_basic_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Exemptions' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'KS':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_ks_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Total Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'WV':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_wv_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												case 'WY': //No state tax.
													break;
												default:
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
											}
										}

										//This is not displayed on CompanyDeduction view, but only Employee Settings view.
										//$retval['user_value10'] = [ 'name' => TTi18n::gettext( 'Exempt' ), 'type_id' => 2100, 'width' => 30, 'multi_select_items' => $this->getOptions('yes_no') ];
										break;
								}
							}
							break;
						case 210: // => TTi18n::gettext( 'US - State Unemployment Insurance (Employer)' ),
							$retval = [
									'user_value1' => [ 'name' => TTi18n::gettext( 'Unemployment Rate' ), 'dynamic_field_id' => 'df_0', 'type_id' => 410 ],
							];
							break;
						case 300: // => TTi18n::gettext( 'Local (City/District/County) Income Tax Formula' ),
							if ( isset( $params['country'] ) ) {
								switch( strtoupper( $params['country'] ) ) {
									case 'US':
										if ( isset( $params['province'] ) ) {
											switch( strtoupper( $params['province'] ) ) {
												case 'IN':
													$retval = [
															'company_value1' => [ 'name' => TTi18n::gettext( 'District / County Name' ), 'dynamic_field_id' => 'df_1', 'type_id' => 100 ],
															'user_value1' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_2', 'type_id' => 400 ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Dependents' ), 'dynamic_field_id' => 'df_3', 'type_id' => 400 ],
															'user_value3' => [ 'name' => TTi18n::gettext( 'County Rate' ), 'dynamic_field_id' => 'df_4', 'type_id' => 410 ],
													];
													break;
												case 'MD':
													$retval = [
															'company_value1' => [ 'name' => TTi18n::gettext( 'District / County Name' ), 'dynamic_field_id' => 'df_1', 'type_id' => 100 ],
															'user_value1' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_2', 'type_id' => 400 ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'County Rate' ), 'dynamic_field_id' => 'df_3', 'type_id' => 410 ],
													];
													break;
												case 'NY':
													$retval = [
															'user_value1' => [ 'name' => TTi18n::gettext( 'Filing Status' ), 'dynamic_field_id' => 'df_14', 'type_id' => 2100, 'multi_select_items' => $this->getOptions('state_filing_status') ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'Allowances' ), 'dynamic_field_id' => 'df_1', 'type_id' => 400 ],
													];
													break;
												default:
													$retval = [
															'company_value1' => [ 'name' => TTi18n::gettext( 'District / County Name' ), 'dynamic_field_id' => 'df_1', 'type_id' => 100 ],
															'user_value2' => [ 'name' => TTi18n::gettext( 'District / County Rate' ), 'dynamic_field_id' => 'df_2', 'type_id' => 410 ],
													];
													break;
											}
										}

										//This is not displayed on CompanyDeduction view, but only Employee Settings view.
										//$retval['user_value10'] = [ 'name' => TTi18n::gettext( 'Exempt' ), 'type_id' => 2100, 'width' => 30, 'multi_select_items' => $this->getOptions('yes_no') ];
										break;
								}
							}
							break;
					}

				}
				//Debug::Arr( $retval, 'Tax/Deduction Field Meta Data: ', __FILE__, __LINE__, __METHOD__, 10 );

				break;
			case 'length_of_service_unit':
				// This needs to update the mapping from unit IDs to interval name for TTDate::incrementDate() in isActiveLengthOfService()
				$retval = [
						10 => TTi18n::gettext( 'Day(s)' ),
						20 => TTi18n::gettext( 'Week(s)' ),
						30 => TTi18n::gettext( 'Month(s)' ),
						40 => TTi18n::gettext( 'Year(s)' ),
						50 => TTi18n::gettext( 'Hour(s)' ),
				];
				break;
			case 'apply_frequency':
				$retval = [
						10  => TTi18n::gettext( 'each Pay Period' ),
						12  => TTi18n::gettext( 'each Pay Period (excl. Hire Date)' ),
						//14  => TTi18n::gettext( 'each Pay Period (excl. Hire Date Anniversary)' ),
						16  => TTi18n::gettext( 'each Pay Period (excl. Termination Date)' ),
						20  => TTi18n::gettext( 'Annually' ),
						25  => TTi18n::gettext( 'Quarterly' ),
						30  => TTi18n::gettext( 'Monthly' ),
						35  => TTi18n::gettext( 'Semi-Monthly' ),
						//40 => TTi18n::gettext('Weekly'),
						100 => TTi18n::gettext( 'Hire Date' ),
						110 => TTi18n::gettext( 'Hire Date (Anniversary)' ),
						120 => TTi18n::gettext( 'Termination Date' ),
						130 => TTi18n::gettext( 'Birth Date (Anniversary)' ),
						140 => TTi18n::gettext( 'Length of Service Date' ),
						150 => TTi18n::gettext( 'Length of Service Date (Anniversary)' ),
				];
				break;
			case 'apply_payroll_run_type':
				$psf = TTnew( 'PayStubFactory' ); /** @var PayStubFactory $psf */
				$retval = $psf->getOptions( 'type' );
				break;
			case 'filter_date_type':
				$retval = [
						100  => TTi18n::gettext( 'Pay Stub Start Date' ),
						110  => TTi18n::gettext( 'Pay Stub End Date' ),
						120  => TTi18n::gettext( 'Pay Stub Transaction Date' ), //Default for new Tax/Deduction records.

						//Pay Period Dates might be useful, for cases where an employee is terminated and issued a pay stub that ends of their termination date
						//  But that could cause health insurance to be deducted twice. Whereas if they used Pay Period End Date it might be far enough into the future to cause a double deduction.
						//  ie: PP Start: July 20th End: Aug 5th Trans: Aug 10th. If the employee was terminated on July 29th, they might have insurance deducted even though they had it deducted already in the first PP of the month?
						200  => TTi18n::gettext( 'Pay Period Start Date' ),
						210  => TTi18n::gettext( 'Pay Period End Date' ), //Default for transition
						220  => TTi18n::gettext( 'Pay Period Transaction Date' ),
				];
				break;
			case 'look_back_unit':
				$retval = [
						10 => TTi18n::gettext( 'Day(s)' ),
						20 => TTi18n::gettext( 'Week(s)' ),
						30 => TTi18n::gettext( 'Month(s)' ),
						40 => TTi18n::gettext( 'Year(s)' ),
						//50 => TTi18n::gettext('Hour(s)'),
						//100 => TTi18n::gettext('Pay Period(s)'), //How do you handle employees switching between pay period schedules? This has too many issues for now.
				];
				break;
			case 'account_amount_type':
				$retval = [
						10 => TTi18n::gettext( 'Amount' ),
						20 => TTi18n::gettext( 'Units/Hours' ),
						30 => TTi18n::gettext( 'YTD Amount' ),
						40 => TTi18n::gettext( 'YTD Units/Hours' ),
				];
				break;
			case 'tax_formula_type':
				$retval = [
						0  => TTi18n::gettext( 'Based on Payroll Run Type' ), //Best fit formula depending on payroll run type (Regular or Bonus/Correction)
						10 => TTi18n::gettext( 'Always Regular (Non-Averaging)' ),
						20 => TTi18n::gettext( 'Always Special (Cummulative Averaging)' ),
				];
				break;
			case 'yes_no':
				$retval = [
						0 => TTi18n::gettext( 'No' ),
						1 => TTi18n::gettext( 'Yes' ),
				];
				break;
			case 'form_w4_version':
				$retval = [
						2019 => TTi18n::gettext( '2019 or earlier' ),
						2020 => TTi18n::gettext( '2020 or later' ),
				];
				break;
			case 'federal_filing_status': //US
				$retval = [
						10 => TTi18n::gettext( 'Single or Married Filing Separately' ),
						20 => TTi18n::gettext( 'Married Filing Jointly' ),
						40 => TTi18n::gettext( 'Head of Household' ),
				];
				break;
			case 'state_basic_filing_status': //US
				$retval = [
						10 => TTi18n::gettext( 'Single' ),
						20 => TTi18n::gettext( 'Married' ),
				];
				break;
			case 'state_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Single' ),
						20 => TTi18n::gettext( 'Married - Spouse Works' ),
						30 => TTi18n::gettext( 'Married - Spouse does not Work' ),
						40 => TTi18n::gettext( 'Head of Household' ),
				];
				break;
			case 'state_ca_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Single or Married (with two or more incomes)' ),
						30 => TTi18n::gettext( 'Married (one income)' ),
						40 => TTi18n::gettext( 'Head of Household' ),
				];
				break;
			case 'state_ga_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Single' ),
						20 => TTi18n::gettext( 'Married - Filing Separately' ),
						30 => TTi18n::gettext( 'Married - Joint One Income' ),
						40 => TTi18n::gettext( 'Married - Joint Two Incomes' ),
						50 => TTi18n::gettext( 'Head of Household' ),
				];
				break;
			case 'state_nj_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Rate "A"' ),
						20 => TTi18n::gettext( 'Rate "B"' ),
						30 => TTi18n::gettext( 'Rate "C"' ),
						40 => TTi18n::gettext( 'Rate "D"' ),
						50 => TTi18n::gettext( 'Rate "E"' ),
				];
				break;
			case 'state_nc_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Single' ),
						20 => TTi18n::gettext( 'Married - Filing Jointly or Qualified Widow(er)' ),
						30 => TTi18n::gettext( 'Married - Filing Separately' ),
						40 => TTi18n::gettext( 'Head of Household' ),
				];
				break;
			case 'state_ma_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Regular' ),
						20 => TTi18n::gettext( 'Head of Household' ),
						30 => TTi18n::gettext( 'Blind' ),
						40 => TTi18n::gettext( 'Head of Household and Blind' ),
				];
				break;
			case 'state_al_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Status "S"' ),
						20 => TTi18n::gettext( 'Status "M"' ),
						30 => TTi18n::gettext( 'Status "0"' ),
						40 => TTi18n::gettext( 'Status "H"' ),
						50 => TTi18n::gettext( 'Status "MS"' ),
				];
				break;
			case 'state_ct_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Status "A"' ),
						20 => TTi18n::gettext( 'Status "B"' ),
						30 => TTi18n::gettext( 'Status "C"' ),
						40 => TTi18n::gettext( 'Status "D"' ),
						//50 => TTi18n::gettext('Status "E"'), //Doesn't exist.
						60 => TTi18n::gettext( 'Status "F"' ),
				];
				break;
			case 'state_wv_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Standard' ),
						20 => TTi18n::gettext( 'Optional Two Earners' ),
				];
				break;
			case 'state_de_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Single' ),
						20 => TTi18n::gettext( 'Married (Filing Jointly)' ),
						30 => TTi18n::gettext( 'Married (Filing Separately)' ),
				];
				break;
			case 'state_dc_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Single' ),
						20 => TTi18n::gettext( 'Married (Filing Jointly)' ),
						30 => TTi18n::gettext( 'Married (Filing Separately)' ),
						40 => TTi18n::gettext( 'Head of Household' ),
				];
				break;
			case 'state_la_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Single' ),
						20 => TTi18n::gettext( 'Married (Filing Jointly)' ),
				];
				break;
			case 'state_ks_filing_status':
				$retval = [
						10 => TTi18n::gettext( 'Single' ),
						20 => TTi18n::gettext( 'Joint' ),
				];
				break;
			case 'formula_variables':
				$retval = [

						'-1010-#pay_stub_amount#'     => TTi18n::getText( 'Pay Stub Amount' ),
						'-1020-#pay_stub_ytd_amount#' => TTi18n::getText( 'Pay Stub YTD Amount' ),
						'-1030-#pay_stub_units#'      => TTi18n::getText( 'Pay Stub Units' ),
						'-1040-#pay_stub_ytd_units#'  => TTi18n::getText( 'Pay Stub YTD Units' ),

						'-1050-#include_pay_stub_amount#'     => TTi18n::getText( 'Include Pay Stub Amount' ),
						'-1060-#include_pay_stub_ytd_amount#' => TTi18n::getText( 'Include Pay Stub YTD Amount' ),
						'-1070-#include_pay_stub_units#'      => TTi18n::getText( 'Include Pay Stub Units' ),
						'-1080-#include_pay_stub_ytd_units#'  => TTi18n::getText( 'Include Pay Stub YTD Units' ),
						'-1090-#exclude_pay_stub_amount#'     => TTi18n::getText( 'Exclude Pay Stub Amount' ),
						'-1100-#exclude_pay_stub_ytd_amount#' => TTi18n::getText( 'Exclude Pay Stub YTD Amount' ),
						'-1110-#exclude_pay_stub_units#'      => TTi18n::getText( 'Exclude Pay Stub Units' ),
						'-1120-#exclude_pay_stub_ytd_units#'  => TTi18n::getText( 'Exclude Pay Stub YTD Units' ),

						'-1130-#employee_hourly_rate#'               => TTi18n::getText( 'Employee Hourly Rate' ),
						'-1132-#employee_annual_wage#'               => TTi18n::getText( 'Employee Annual Wage' ),
						'-1134-#employee_wage_average_weekly_hours#' => TTi18n::getText( 'Employee Average Weekly Hours' ),

						'-1140-#custom_value1#'  => TTi18n::getText( 'Custom Variable 1' ),
						'-1150-#custom_value2#'  => TTi18n::getText( 'Custom Variable 2' ),
						'-1160-#custom_value3#'  => TTi18n::getText( 'Custom Variable 3' ),
						'-1170-#custom_value4#'  => TTi18n::getText( 'Custom Variable 4' ),
						'-1180-#custom_value5#'  => TTi18n::getText( 'Custom Variable 5' ),
						'-1190-#custom_value6#'  => TTi18n::getText( 'Custom Variable 6' ),
						'-1200-#custom_value7#'  => TTi18n::getText( 'Custom Variable 7' ),
						'-1210-#custom_value8#'  => TTi18n::getText( 'Custom Variable 8' ),
						'-1220-#custom_value9#'  => TTi18n::getText( 'Custom Variable 9' ),
						'-1230-#custom_value10#' => TTi18n::getText( 'Custom Variable 10' ),

						'-1240-#annual_pay_periods#'          => TTi18n::getText( 'Annual Pay Periods' ),
						'-1242-#pay_period_start_date#'       => TTi18n::getText( 'Pay Period - Start Date' ),
						'-1243-#pay_period_end_date#'         => TTi18n::getText( 'Pay Period - End Date' ),
						'-1244-#pay_period_transaction_date#' => TTi18n::getText( 'Pay Period - Transaction Date' ),
						'-1245-#pay_period_total_days#'       => TTi18n::getText( 'Pay Period - Total Days' ),
						'-1246-#pay_period_worked_days#'      => TTi18n::getText( 'Pay Period - Total Worked Days' ),
						'-1247-#pay_period_paid_days#'        => TTi18n::getText( 'Pay Period - Total Paid Days' ),
						'-1248-#pay_period_worked_weeks#'     => TTi18n::getText( 'Pay Period - Total Worked Weeks' ),
						'-1249-#pay_period_paid_weeks#'       => TTi18n::getText( 'Pay Period - Total Paid Weeks' ),
						'-1250-#pay_period_worked_time#'      => TTi18n::getText( 'Pay Period - Total Worked Time' ),
						'-1251-#pay_period_paid_time#'        => TTi18n::getText( 'Pay Period - Total Paid Time' ),

						'-1260-#employee_hire_date#'        => TTi18n::getText( 'Employee Hire Date' ),
						'-1261-#employee_termination_date#' => TTi18n::getText( 'Employee Termination Date' ),
						'-1270-#employee_birth_date#'       => TTi18n::getText( 'Employee Birth Date' ),

						'-1300-#currency_iso_code#'        => TTi18n::getText( 'Currency ISO Code' ),
						'-1305-#currency_conversion_rate#' => TTi18n::getText( 'Currency Conversion Rate' ),

						'-1510-#lookback_total_pay_stubs#' => TTi18n::getText( 'Lookback - Total Pay Stubs' ),
						'-1520-#lookback_start_date#'      => TTi18n::getText( 'Lookback - Start Date' ),
						'-1522-#lookback_end_date#'        => TTi18n::getText( 'Lookback - End Date' ),
						'-1523-#lookback_total_days#'      => TTi18n::getText( 'Lookback - Total Days' ),

						'-1530-#lookback_first_pay_stub_start_date#'       => TTi18n::getText( 'Lookback - First Pay Stub Start Date' ),
						'-1532-#lookback_first_pay_stub_end_date#'         => TTi18n::getText( 'Lookback - First Pay Stub End Date' ),
						'-1534-#lookback_first_pay_stub_transaction_date#' => TTi18n::getText( 'Lookback - First Pay Stub Transaction Date' ),
						'-1540-#lookback_last_pay_stub_start_date#'        => TTi18n::getText( 'Lookback - Last Pay Stub Start Date' ),
						'-1542-#lookback_last_pay_stub_end_date#'          => TTi18n::getText( 'Lookback - Last Pay Stub End Date' ),
						'-1544-#lookback_last_pay_stub_transaction_date#'  => TTi18n::getText( 'Lookback - Last Pay Stub Transaction Date' ),

						'-1545-#lookback_pay_stub_total_days#'  => TTi18n::getText( 'Lookback - Pay Period Total Days' ),
						'-1546-#lookback_pay_stub_worked_days#' => TTi18n::getText( 'Lookback - Pay Period Worked Days' ),
						'-1547-#lookback_pay_stub_paid_days#'   => TTi18n::getText( 'Lookback - Pay Period Paid Days' ),
						'-1548-#lookback_pay_stub_worked_weeks#'=> TTi18n::getText( 'Lookback - Pay Period Worked Weeks' ),
						'-1549-#lookback_pay_stub_paid_weeks#'  => TTi18n::getText( 'Lookback - Pay Period Paid Weeks' ),
						'-1550-#lookback_pay_stub_worked_time#' => TTi18n::getText( 'Lookback - Pay Period Worked Time' ),
						'-1551-#lookback_pay_stub_paid_time#'   => TTi18n::getText( 'Lookback - Pay Period Paid Time' ),

						'-1610-#lookback_pay_stub_amount#'     => TTi18n::getText( 'Lookback - Pay Stub Amount' ),
						'-1620-#lookback_pay_stub_ytd_amount#' => TTi18n::getText( 'Lookback - Pay Stub YTD Amount' ),
						'-1630-#lookback_pay_stub_units#'      => TTi18n::getText( 'Lookback - Pay Stub Units' ),
						'-1640-#lookback_pay_stub_ytd_units#'  => TTi18n::getText( 'Lookback - Pay Stub YTD Units' ),

						'-1650-#lookback_include_pay_stub_amount#'     => TTi18n::getText( 'Lookback - Include Pay Stub Amount' ),
						'-1660-#lookback_include_pay_stub_ytd_amount#' => TTi18n::getText( 'Lookback - Include Pay Stub YTD Amount' ),
						'-1670-#lookback_include_pay_stub_units#'      => TTi18n::getText( 'Lookback - Include Pay Stub Units' ),
						'-1680-#lookback_include_pay_stub_ytd_units#'  => TTi18n::getText( 'Lookback - Include Pay Stub YTD Units' ),
						'-1690-#lookback_exclude_pay_stub_amount#'     => TTi18n::getText( 'Lookback - Exclude Pay Stub Amount' ),
						'-1700-#lookback_exclude_pay_stub_ytd_amount#' => TTi18n::getText( 'Lookback - Exclude Pay Stub YTD Amount' ),
						'-1710-#lookback_exclude_pay_stub_units#'      => TTi18n::getText( 'Lookback - Exclude Pay Stub Units' ),
						'-1720-#lookback_exclude_pay_stub_ytd_units#'  => TTi18n::getText( 'Lookback - Exclude Pay Stub YTD Units' ),
				];

				$retval = array_merge( $retval, (array)$this->getOptions( 'formula_dynamic_variables' ) );
				ksort( $retval );
				break;
			case 'formula_dynamic_variables':
				$retval = [];

				global $current_user;
				$ps_summary_report_obj = TTnew( 'PayStubSummaryReport' );
				$ps_summary_report_obj->setUserObject( $current_user );

				$psa_variables = $ps_summary_report_obj->getOptions( 'pay_stub_account_amount_columns', [ 'include_ytd_amount' => true ] );
				foreach ( $psa_variables as $psa_variable => $label ) {
					$psa_variable = str_replace( '-P', '-#P', $psa_variable ) . '#'; //Add # to the beginning/end of the variable name so it can be used in a custom formula
					$retval[$psa_variable] = $label;
				}
				unset( $ps_summary_report_obj, $psa_variables, $psa_variable, $label );

				break;
			case 'columns':
				$retval = [
						'-1010-status'                    => TTi18n::gettext( 'Status' ),
						'-1020-type'                      => TTi18n::gettext( 'Type' ),
						'-1030-legal_entity_legal_name'   => TTi18n::gettext( 'Legal Entity' ),
						'-1040-payroll_remittance_agency' => TTi18n::gettext( 'Remittance Agency' ),
						'-1050-name'                      => TTi18n::gettext( 'Name' ),
						'-1055-description'               => TTi18n::gettext( 'Description' ),
						'-1060-calculation'               => TTi18n::gettext( 'Calculation' ),

						'-1070-start_date'    => TTi18n::gettext( 'Start Date' ),
						'-1080-end_date' => TTi18n::gettext( 'End Date' ),

						'-1090-calculation_order' => TTi18n::gettext( 'Calculation Order' ),

						'-1100-total_users' => TTi18n::gettext( 'Employees' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				//Don't show the total_users column here, as its primarily used for Edit Employee -> Tax tab.
				$list_columns = [
						'status',
						'type',
						'legal_entity_id',
						'legal_entity_legal_name',
						'name',
						'calculation',
				];

				$retval = Misc::arrayIntersectByKey( $list_columns, Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'status',
						'type',
						'legal_entity_legal_name',
						'name',
						'calculation',
						'total_users',
						'payroll_remittance_agency',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'calculation_id',
						'country',
						'province',
						'district',
						'company_value1',
						'company_value2',
						'company_value3',
						'company_value4',
						'company_value5',
						'company_value6',
						'company_value7',
						'company_value8',
						'company_value9',
						'company_value10',
						'user_value1',
						'user_value2',
						'user_value3',
						'user_value4',
						'user_value5',
						'user_value6',
						'user_value7',
						'user_value8',
						'user_value9',
						'user_value10',
						'name',
						'country',
						'province',
						'district',
						'calculation_order',
						'pay_stub_entry_account_id',
						'include_account_amount_type_id',
						'exclude_account_amount_type_id',
						'exclude_pay_stub_entry_account',
						'include_pay_stub_entry_account',
						'df_0',
						'df_1',
						'df_2',
						'df_3',
						'df_4',
						'df_5',
						'df_6',
						'df_7',
						'df_8',
						'df_9',
						'df_10',
						'df_11',
						'df_12',
						'df_13',
						'df_14',
						'df_15',
						'df_16',
						'df_17',
						'df_18',
						'df_19',
						'df_20',
						'df_21',
						'df_22',
						'df_23',
						'df_24',
						'df_25',

				];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [
						'country',
						'province',
						'district',
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
				'id'                                                => 'ID',
				'company_id'                                        => 'Company',
				'status_id'                                         => 'Status',
				'status'                                            => false,
				'type_id'                                           => 'Type',
				'type'                                              => false,
				'calculation_id'                                    => 'Calculation', //Should go fairly early so we can change behavior based on calculation type.
				'calculation'                                       => false,
				'payroll_remittance_agency_id'                      => 'PayrollRemittanceAgency', //Set this before name, so unique names can be determined by legal entity.
				'payroll_remittance_agency'                         => false,
				'legal_entity_id'                                   => 'LegalEntity',
				'legal_entity_legal_name'                           => false,
				'name'                                              => 'Name',
				'description'                                       => 'Description',
				'filter_date_type_id'                               => 'FilterDateType',
				'filter_date_type'                                  => false,
				'start_date'                                        => 'StartDate',
				'end_date'                                          => 'EndDate',
				'minimum_length_of_service_unit_id'                 => 'MinimumLengthOfServiceUnit', //Must go before minimum_length_of_service_days, for calculations to not fail.
				'minimum_length_of_service_days'                    => 'MinimumLengthOfServiceDays',
				'minimum_length_of_service'                         => 'MinimumLengthOfService',
				'maximum_length_of_service_unit_id'                 => 'MaximumLengthOfServiceUnit', //Must go before maximum_length_of_service_days, for calculations to not fail.
				'maximum_length_of_service_days'                    => 'MaximumLengthOfServiceDays',
				'maximum_length_of_service'                         => 'MaximumLengthOfService',
				'length_of_service_contributing_pay_code_policy_id' => 'LengthOfServiceContributingPayCodePolicy',
				'length_of_service_contributing_pay_code_policy'    => false,
				'minimum_user_age'                                  => 'MinimumUserAge',
				'maximum_user_age'                                  => 'MaximumUserAge',
				'apply_frequency_id'                                => 'ApplyFrequency',
				'apply_frequency_month'                             => 'ApplyFrequencyMonth',
				'apply_frequency_day_of_month'                      => 'ApplyFrequencyDayOfMonth',
				'apply_frequency_day_of_month1'                     => 'ApplyFrequencyDayOfMonth', //Linked to ApplyFrequencyDayOfMonth
				'apply_frequency_day_of_month2'                     => 'ApplyFrequencyMonth', //Linked to ApplyFrequencyMonth
				'apply_frequency_day_of_week'                       => 'ApplyFrequencyDayOfWeek',
				'apply_frequency_quarter_month'                     => 'ApplyFrequencyQuarterMonth',
				'apply_payroll_run_type_id'                         => 'ApplyPayrollRunType',
				'pay_stub_entry_description'                        => 'PayStubEntryDescription',
				'calculation_order'                                 => 'CalculationOrder',
				'country'                                           => 'Country',
				'province'                                          => 'Province',
				'district'                                          => 'District',
				'company_value1'                                    => 'CompanyValue1',
				'company_value2'                                    => 'CompanyValue2',
				'company_value3'                                    => 'CompanyValue3',
				'company_value4'                                    => 'CompanyValue4',
				'company_value5'                                    => 'CompanyValue5',
				'company_value6'                                    => 'CompanyValue6',
				'company_value7'                                    => 'CompanyValue7',
				'company_value8'                                    => 'CompanyValue8',
				'company_value9'                                    => 'CompanyValue9',
				'company_value10'                                   => 'CompanyValue10',
				'user_value1'                                       => 'UserValue1',
				'user_value2'                                       => 'UserValue2',
				'user_value3'                                       => 'UserValue3',
				'user_value4'                                       => 'UserValue4',
				'user_value5'                                       => 'UserValue5',
				'user_value6'                                       => 'UserValue6',
				'user_value7'                                       => 'UserValue7',
				'user_value8'                                       => 'UserValue8',
				'user_value9'                                       => 'UserValue9',
				'user_value10'                                      => 'UserValue10',
				'pay_stub_entry_account_id'                         => 'PayStubEntryAccount',
				'lock_user_value1'                                  => 'LockUserValue1',
				'lock_user_value2'                                  => 'LockUserValue2',
				'lock_user_value3'                                  => 'LockUserValue3',
				'lock_user_value4'                                  => 'LockUserValue4',
				'lock_user_value5'                                  => 'LockUserValue5',
				'lock_user_value6'                                  => 'LockUserValue6',
				'lock_user_value7'                                  => 'LockUserValue7',
				'lock_user_value8'                                  => 'LockUserValue8',
				'lock_user_value9'                                  => 'LockUserValue9',
				'lock_user_value10'                                 => 'LockUserValue10',
				'include_account_amount_type_id'                    => 'IncludeAccountAmountType',
				'include_pay_stub_entry_account'                    => 'IncludePayStubEntryAccount',
				'exclude_account_amount_type_id'                    => 'ExcludeAccountAmountType',
				'exclude_pay_stub_entry_account'                    => 'ExcludePayStubEntryAccount',
				'user'                                              => 'User',
				'total_users'                                       => 'TotalUsers',
				'deleted'                                           => 'Deleted',
		];

		//When upgrading from prior to 1093A schema version, ignore the filter_date_type_id column as it doesn't exist yet.
		global $COMPANY_DEDUCTION_FILTER_DATE_TYPE_ID_COLUMN;
		if ( $COMPANY_DEDUCTION_FILTER_DATE_TYPE_ID_COLUMN == false ) {
			unset( $variable_function_map['filter_date_type_id'] );
		}

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool|null
	 */
	function getPayStubEntryAccountLinkObject() {
		if ( is_object( $this->pay_stub_entry_account_link_obj ) ) {
			return $this->pay_stub_entry_account_link_obj;
		} else {
			$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
			$pseallf->getByCompanyId( $this->getCompany() );
			if ( $pseallf->getRecordCount() > 0 ) {
				$this->pay_stub_entry_account_link_obj = $pseallf->getCurrent();

				return $this->pay_stub_entry_account_link_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool
	 */
	function getPayStubEntryAccountObject() {
		return $this->getGenericObject( 'PayStubEntryAccountListFactory', $this->getPayStubEntryAccount(), 'pay_stub_entry_account_obj' );
	}

	/**
	 * @return bool
	 */
	function getLengthOfServiceContributingPayCodePolicyObject() {
		return $this->getGenericObject( 'ContributingPayCodePolicyListFactory', $this->getLengthOfServiceContributingPayCodePolicy(), 'length_of_service_contributing_pay_code_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getPayrollRemittanceAgencyObject() {
		return $this->getGenericObject( 'PayrollRemittanceAgencyListFactory', $this->getPayrollRemittanceAgency(), 'payroll_remittance_agency_obj' );
	}


	/**
	 * @return bool
	 */
	function getLegalEntityObject() {
		return $this->getGenericObject( 'LegalEntityListFactory', $this->getLegalEntity(), 'legal_entity_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getLegalEntity() {
		return $this->getGenericDataValue( 'legal_entity_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLegalEntity( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Legal Entity ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'legal_entity_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Company ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayrollRemittanceAgency() {
		return $this->getGenericDataValue( 'payroll_remittance_agency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayrollRemittanceAgency( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( $value == '' ) {
			//Must allow this to be NONE for upgrading purposes and for cases where the Tax/Deduction is not remitted at all.
			$value = TTUUID::getZeroID();
		}
		Debug::Text( 'Payroll Remittance Agency ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'payroll_remittance_agency_id', $value );
	}

	/**
	 * @return int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return int
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		//Names must be unique across company, legal entity and payroll remittance agency.
		//  Otherwise in Canada, two legal entities in one province can't have duplicate "AB - Vacation Accrual" records that don't go to any agency.
		$ph = [
				'company_id'                   => TTUUID::castUUID( $this->getCompany() ),
				'legal_entity_id'              => TTUUID::castUUID( $this->getLegalEntity() ),
				'payroll_remittance_agency_id' => TTUUID::castUUID( $this->getPayrollRemittanceAgency() ),
				'name'                         => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . ' where company_id = ? AND legal_entity_id = ? AND payroll_remittance_agency_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne( $query, $ph );
		//Debug::Arr($id, 'Unique Pay Stub Account: '. $name, __FILE__, __LINE__, __METHOD__, 10);

		if ( $id === false ) {
			return true;
		} else {
			if ( $id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	//Generates column key for Import wizard field mapping.
	function getUniqueKeyPrefix( $suffix = '' ) {
		$parts = [ $this->getCalculation() ];
		if ( $this->getCountry() != '' && in_array( $this->getCalculation(), [ 100, 200, 300 ] ) ) {
			$parts[] = $this->getCountry();
		}
		if ( $this->getProvince() != '' && in_array( $this->getCalculation(), [ 200, 300 ] ) && $this->getCountry() != 'CA' ) { //Canada doesn't need separate fields for every province.
			$parts[] = $this->getProvince();
		}
		if ( $this->getDistrict() != '' && in_array( $this->getCalculation(), [ 300 ] ) ) {
			$parts[] = $this->getDistrict();
		}

		$retval = implode( '-', $parts ) . '-'. $suffix;

		return $retval;
	}

	//Generates name for Import wizard field mapping.
	function getUniqueNamePrefix( $suffix = '' ) {
		//Name needs to include Country/Province/District if specified as well.
		$name_parts = [];
		if ( $this->getCountry() != '' && in_array( $this->getCalculation(), [ 100, 200, 300 ] ) ) {
			$name_parts[] = $this->getCountry();
		}
		if ( $this->getProvince() != '' && in_array( $this->getCalculation(), [ 200, 300 ] ) && $this->getCountry() != 'CA' ) { //Canada doesn't need separate fields for every province.
			$name_parts[] = $this->getProvince();
		}
		if ( $this->getDistrict() != '' && in_array( $this->getCalculation(), [ 300 ] ) ) {
			$name_parts[] = $this->getDistrict();
		}

		$name_parts[] = Option::getByKey( $this->getCalculation(), $this->getOptions('calculation') );

		$retval = implode( ' - ', $name_parts )  . ' - '. str_replace( '<br>', ' ', $suffix );

		return $retval;

	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
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
	function getPayStubEntryDescription() {
		return $this->getGenericDataValue( 'pay_stub_entry_description' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayStubEntryDescription( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'pay_stub_entry_description', $value );
	}

	/**
	 * @return int
	 */
	function getFilterDateType() {
		return $this->getGenericDataValue( 'filter_date_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFilterDateType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'filter_date_type_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getStartDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'start_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int|mixed
	 */
	function getEndDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'end_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'end_date', $value );
	}

	function getFilterDate( $ps_start_date = null, $ps_end_date = null, $ps_transaction_date = null, $pp_start_date = null, $pp_end_date = null, $pp_transaction_date = null ) {
		switch ( $this->getFilterDateType() ) {
			case 100: //Pay Stub Start Date
				$retval = $ps_start_date;
				break;
			case 110: //Pay Stub End Date
				$retval = $ps_end_date;
				break;
			case 120: //Pay Stub Transaction Date
				$retval = $ps_transaction_date;
				break;
			case 200: //Pay Period Start Date
				$retval = $pp_start_date;
				break;
			case 210: //Pay Period End Date
				$retval = $pp_end_date;
				break;
			case 220: //Pay Period Transaction Date
				$retval = $pp_transaction_date;
				break;
			default:
				$retval = $pp_end_date; //Just in case, default to (210) original value for backwards compatiblity.
				break;
		}

		$retval = TTDate::getBeginDayEpoch( $retval );

		Debug::text( 'Epoch: ' . TTDate::getDate( 'DATE+TIME', $retval ) . ' of Date Type: '. $this->getFilterDateType(), __FILE__, __LINE__, __METHOD__, 10 );
		return $retval;
	}

	/**
	 * Check if this date is within the effective date range
	 * @param object $ud_obj
	 * @param int $pp_end_date         EPOCH
	 * @param int $pp_transaction_date EPOCH
	 * @return bool
	 */
	function isActiveDate( $ud_obj, $ps_start_date = null, $ps_end_date = null, $ps_transaction_date = null, $pp_start_date = null, $pp_end_date = null, $pp_transaction_date = null ) {
		if ( $ud_obj->getStartDate() == '' && $ud_obj->getEndDate() == '' && $this->getStartDate() == '' && $this->getEndDate() == '' ) {
			return true;
		}

		//If user specific settings are not defined, fall back to company deduction specific settings.
		$filter_start_date = (int)( ( $ud_obj->getStartDate() == '' ) ? $this->getStartDate() : $ud_obj->getStartDate() );
		$filter_end_date = (int)( ( $ud_obj->getEndDate() == '' ) ? $this->getEndDate() : $ud_obj->getEndDate() );

		if ( ( $this->getCalculation() == 90 || $this->getCalculation() == 92 ) && $pp_transaction_date != '' ) { //CPP & CPP2
			if ( ( empty( $filter_start_date ) || TTDate::getEndDayEpoch( $pp_transaction_date ) > TTDate::getEndMonthEpoch( $filter_start_date ) )
					&& ( empty( $filter_end_date ) || TTDate::getEndDayEpoch( $pp_transaction_date ) <= TTDate::getEndMonthEpoch( $filter_end_date ) ) ) {
				Debug::text( 'CPP: Within Start/End Date.', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}

			Debug::text( 'CPP: Epoch: ' . TTDate::getDate( 'DATE+TIME', $pp_transaction_date ) . ' is outside Start: ' . TTDate::getDate( 'DATE+TIME', $filter_start_date ) . ' and End Date: ' . TTDate::getDate( 'DATE+TIME', $filter_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		} else {
			$epoch = TTDate::getMiddleDayEpoch( $this->getFilterDate( $ps_start_date, $ps_end_date, $ps_transaction_date, $pp_start_date, $pp_end_date, $pp_transaction_date ) );

			if ( ( empty( $filter_start_date ) || $epoch >= TTDate::getMiddleDayEpoch( $filter_start_date ) )
					&& ( empty( $filter_end_date ) || $epoch <= TTDate::getMiddleDayEpoch( $filter_end_date ) ) ) {
				Debug::text( 'Within Start/End Date.', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}

			Debug::text( 'Epoch: ' . TTDate::getDate( 'DATE+TIME', $epoch ) . ' of Date Type: '. $this->getFilterDateType() .' is outside Start: ' . TTDate::getDate( 'DATE+TIME', $filter_start_date ) . ' and End Date: ' . TTDate::getDate( 'DATE+TIME', $filter_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
	}

	function isActiveForAnyUserDates( $filter_start_date, $filter_end_date, $pp_end_date = null, $pp_transaction_date = null ) {
		//If the no start/end date filters are specified on the CompanyDeduction record, they aren't enabled for the users, so exit early.
		//  Also most Tax Reports filter based on Transaction Date, so if this Tax/Deduction filters on some other date, it likely won't match up anyways, so allow it through.
		if ( ( $this->getStartDate() == '' && $this->getEndDate() == '' ) || !in_array( $this->getFilterDateType(), [ 120, 220 ] ) ) { //120=Pay Stub Transaction Date, 220=Pay Period Transaction Date
			return true;
		}

		$retval = false;

		$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
		$udlf->getByCompanyIdAndCompanyDeductionId( $this->getCompany(), $this->getId() );
		if ( $udlf->getRecordCount() > 0 ) {
			foreach( $udlf as $ud_obj ) {
				//If user specific settings are not defined, fall back to company deduction specific settings.
				$start_date = (int)( ( $ud_obj->getStartDate() == '' ) ? $this->getStartDate() : $ud_obj->getStartDate() );
				$end_date = (int)( ( $ud_obj->getEndDate() == '' ) ? $this->getEndDate() : $ud_obj->getEndDate() );
				if ( empty( $end_date ) ) { //If no end date is specified, we need to use something that will always match, or is way out in the future.
					$end_date = $filter_end_date;
				}

				if ( TTDate::isTimeOverLapStandard( $filter_start_date, $filter_end_date, $start_date, $end_date, true ) == true ) {
					Debug::text( 'Found UserDeduction that is active for dates: User ID: '. $ud_obj->getUser() .' Filter Start Date: ' . TTDate::getDate( 'DATE+TIME', $filter_start_date ) .' Filter End Date: '. TTDate::getDate( 'DATE+TIME', $filter_end_date ) .' is inside UD Start: ' . TTDate::getDate( 'DATE+TIME', $start_date ) . ' and UD End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

					$retval = true;
					break;
				}
			}
		}

		Debug::text( 'Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );
		return $retval;
	}

	/**
	 * @return float
	 */
	function getMinimumLengthOfServiceDays() {
		return (float)$this->getGenericDataValue( 'minimum_length_of_service_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumLengthOfServiceDays( $value ) {
		$value = (float)trim( $value );
		Debug::text( 'aLength of Service Days: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $value >= 0 ) {
			return $this->setGenericDataValue( 'minimum_length_of_service_days', TTMath::mul( $value, $this->length_of_service_multiplier[(int)$this->getMinimumLengthOfServiceUnit()], 4 ) );
		}

		return false;
	}

	/**
	 * @return float
	 */
	function getMinimumLengthOfService() {
		return (float)$this->getGenericDataValue( 'minimum_length_of_service' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumLengthOfService( $value ) {
		$value = (float)trim( $value );

		Debug::text( 'bLength of Service: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $value >= 0 ) {
			return $this->setGenericDataValue( 'minimum_length_of_service', $value );
		}

		return false;
	}

	/**
	 * @return int
	 */
	function getMinimumLengthOfServiceUnit() {
		return (int)$this->getGenericDataValue( 'minimum_length_of_service_unit_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumLengthOfServiceUnit( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'minimum_length_of_service_unit_id', $value );
	}

	/**
	 * @return float
	 */
	function getMaximumLengthOfServiceDays() {
		return (float)$this->getGenericDataValue( 'maximum_length_of_service_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumLengthOfServiceDays( $value ) {
		$value = (float)trim( $value );
		Debug::text( 'aLength of Service Days: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'maximum_length_of_service_days', TTMath::mul( $value, $this->length_of_service_multiplier[(int)$this->getMaximumLengthOfServiceUnit()], 4 ) );
	}

	/**
	 * @return float
	 */
	function getMaximumLengthOfService() {
		return (float)$this->getGenericDataValue( 'maximum_length_of_service' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumLengthOfService( $value ) {
		$value = (float)trim( $value );
		Debug::text( 'bLength of Service: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'maximum_length_of_service', $value );
	}

	/**
	 * @return int
	 */
	function getMaximumLengthOfServiceUnit() {
		return (int)$this->getGenericDataValue( 'maximum_length_of_service_unit_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumLengthOfServiceUnit( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'maximum_length_of_service_unit_id', $value );
	}

	/**
	 * @return float
	 */
	function getMinimumUserAge() {
		return (float)$this->getGenericDataValue( 'minimum_user_age' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumUserAge( $value ) {
		$value = (float)trim( $value );
		Debug::text( 'Minimum User Age: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $value >= 0 ) {
			return $this->setGenericDataValue( 'minimum_user_age', $value );
		}

		return false;
	}

	/**
	 * @return float
	 */
	function getMaximumUserAge() {
		return (float)$this->getGenericDataValue( 'maximum_user_age' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumUserAge( $value ) {
		$value = (float)trim( $value );
		Debug::text( 'Maximum User Age: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $value >= 0 ) {
			return $this->setGenericDataValue( 'maximum_user_age', $value );
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getLengthOfServiceContributingPayCodePolicy() {
		return $this->getGenericDataValue( 'length_of_service_contributing_pay_code_policy_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLengthOfServiceContributingPayCodePolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'length_of_service_contributing_pay_code_policy_id', $value );
	}

	//
	// Calendar
	//
	/**
	 * @return int
	 */
	function getApplyFrequency() {
		return (int)$this->getGenericDataValue( 'apply_frequency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setApplyFrequency( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'apply_frequency_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getApplyFrequencyMonth() {
		return $this->getGenericDataValue( 'apply_frequency_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setApplyFrequencyMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'apply_frequency_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getApplyFrequencyDayOfMonth() {
		return $this->getGenericDataValue( 'apply_frequency_day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setApplyFrequencyDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'apply_frequency_day_of_month', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getApplyFrequencyDayOfMonth1() {
		return $this->getGenericDataValue( 'apply_frequency_day_of_month' );
	}
	/**
	 * @return bool|mixed
	 */
	function getApplyFrequencyDayOfMonth2() {
		return $this->getGenericDataValue( 'apply_frequency_month' ); //Linked to 'apply_frequency_month' rather than create a new column in DB.
	}


	/**
	 * @return bool|mixed
	 */
	function getApplyFrequencyDayOfWeek() {
		return $this->getGenericDataValue( 'apply_frequency_day_of_week' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setApplyFrequencyDayOfWeek( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'apply_frequency_day_of_week', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getApplyFrequencyQuarterMonth() {
		return $this->getGenericDataValue( 'apply_frequency_quarter_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setApplyFrequencyQuarterMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'apply_frequency_quarter_month', $value );
	}

	/**
	 * @return int
	 */
	function getApplyPayrollRunType() {
		return (int)$this->getGenericDataValue( 'apply_payroll_run_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setApplyPayrollRunType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'apply_payroll_run_type_id', $value );
	}

	/**
	 * @param int $pay_period_start_date EPOCH
	 * @param int $pay_period_end_date   EPOCH
	 * @param int $hire_date             EPOCH
	 * @param int $termination_date      EPOCH
	 * @param int $birth_date            EPOCH
	 * @return bool
	 */
	function inApplyFrequencyWindow( $pay_period_start_date, $pay_period_end_date, $hire_date = null, $termination_date = null, $birth_date = null, $length_of_service_date = null ) {
		if ( $this->getApplyFrequency() == false || $this->getApplyFrequency() == 10 ) { //Each pay period
			return true;
		}

		if ( $this->getApplyFrequency() == 12 ) { //12=each Pay Period (excl. Hire Date)
			if ( $hire_date >= $pay_period_start_date && $hire_date <= $pay_period_end_date ) {
				return false;
			}

			return true;
		} else if ( $this->getApplyFrequency() == 14 ) { //14=each Pay Period (excl. Termination Date)
			if ( $termination_date >= $pay_period_start_date && $termination_date <= $pay_period_end_date ) {
				return false;
			}

			return true;
		}

		$frequency_criteria = [
				'month'         => $this->getApplyFrequencyMonth(),
				'day_of_month'  => $this->getApplyFrequencyDayOfMonth(),
				'quarter_month' => $this->getApplyFrequencyQuarterMonth(),

				'day_of_month1' => $this->getApplyFrequencyDayOfMonth1(), //Linked to DayOfMonth
				'day_of_month2' => $this->getApplyFrequencyDayOfMonth2(), //Linked to Month
		];

		$frequency_id = $this->getApplyFrequency();
		switch ( $this->getApplyFrequency() ) {
			case 100: //Hire Date
				$frequency_criteria['date'] = $hire_date;
				$frequency_id = 100; //Specific date
				break;
			case 110: //Hire Date anniversary.
				$frequency_criteria['month'] = TTDate::getMonth( $hire_date );
				$frequency_criteria['day_of_month'] = TTDate::getDayOfMonth( $hire_date );
				$frequency_id = 20; //Annually
				break;
			case 120:
				$frequency_criteria['date'] = $termination_date;
				$frequency_id = 100; //Specific date
				break;
			case 130: //Birth Date anniversary.
				$frequency_criteria['month'] = TTDate::getMonth( $birth_date );
				$frequency_criteria['day_of_month'] = TTDate::getDayOfMonth( $birth_date );
				$frequency_id = 20; //Annually
				break;
			case 140: //Length of Service Date
				$frequency_criteria['date'] = $length_of_service_date;
				$frequency_id = 100; //Specific date
				break;
			case 150: //Length of Service Date anniversary.
				$frequency_criteria['month'] = TTDate::getMonth( $length_of_service_date );
				$frequency_criteria['day_of_month'] = TTDate::getDayOfMonth( $length_of_service_date );
				$frequency_id = 20; //Annually
				break;
		}

		$retval = TTDate::inApplyFrequencyWindow( $frequency_id, $pay_period_start_date, $pay_period_end_date, $frequency_criteria );
		Debug::Arr( $frequency_criteria, 'Frequency: ' . $this->getApplyFrequency() . ' Retval: ' . (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @param int $type_id
	 * @return bool
	 */
	function inApplyPayrollRunType( $type_id ) {
		if ( $this->getApplyPayrollRunType() == 0 || $type_id == $this->getApplyPayrollRunType() ) {
			return true;
		}

		Debug::Text( 'Apply Payroll Run Type: ' . $this->getApplyPayrollRunType() . ' Type: ' . (int)$type_id, __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @return bool|int
	 */
	function getWorkedTimeByUserIdAndEndDate( $user_id, $start_date = null, $end_date = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $start_date == '' ) {
			$start_date = 1; //Default to beginning of time if hire date is not specified.
		}

		if ( $end_date == '' ) {
			return false;
		}

		$retval = 0;

		$pay_code_policy_obj = $this->getLengthOfServiceContributingPayCodePolicyObject();
		if ( is_object( $pay_code_policy_obj ) ) {
			$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
			$retval = $udtlf->getTotalTimeSumByUserIDAndPayCodeIDAndStartDateAndEndDate( $user_id, $pay_code_policy_obj->getPayCode(), $start_date, $end_date );
		}

		Debug::Text( 'Worked Seconds: ' . (int)$retval . ' Before: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * Check to see if user length of service eligibility criteria are actually specified for this Tax/Deduction record.
	 * @return bool
	 */
	function isUserLengthOfServiceEligibility() {
		if ( $this->getMinimumLengthOfService() != 0 || $this->getMaximumLengthOfService() != 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * @param object $ud_obj
	 * @param int $epoch EPOCH
	 * @param bool $pay_period_start_date
	 * @return bool
	 */
	function isActiveLengthOfService( $ud_obj, $epoch, $pay_period_start_date = false ) {
		//Epoch will normally be pay period end date.

		$worked_time = 0;

		//Check if we are dealing with hours, or calendar days, so we know what data to grab.
		if ( ( $this->getMinimumLengthOfServiceUnit() == 50 && $this->getMinimumLengthOfService() > 0 )
				|| ( $this->getMaximumLengthOfServiceUnit() == 50 && $this->getMaximumLengthOfService() > 0 ) ) {
			//Hour based length of service, get users hours up until this period.
			$worked_time = TTDate::getHours( $this->getWorkedTimeByUserIdAndEndDate( $ud_obj->getUser(), $ud_obj->getLengthOfServiceDate(), $epoch ) );
			Debug::Text( '  Worked Time: ' . $worked_time . 'hrs', __FILE__, __LINE__, __METHOD__, 10 );
		}

		//Map unit IDs to labels for TTDate::incrementDate()
		$unit_id_to_name_map = [
				10 => 'day',
				20 => 'week',
				30 => 'month',
				40 => 'year',
				50 => 'hour',
		];
		$minimum_length_of_service_unit_name = ( isset( $unit_id_to_name_map[$this->getMinimumLengthOfServiceUnit()] ) ) ? $unit_id_to_name_map[$this->getMinimumLengthOfServiceUnit()] : 'day';
		$maximum_length_of_service_unit_name = ( isset( $unit_id_to_name_map[$this->getMaximumLengthOfServiceUnit()] ) ) ? $unit_id_to_name_map[$this->getMaximumLengthOfServiceUnit()] : 'day';

		//Need to make sure that a min/max length of service of "3 years" is actually 3 years, and not 1095/1096 days. So use TTDate::incrementDate() for this purpose.
		//  It also means that non-whole numbers (ie: 2.999) will be rounded to the nearest whole number.
		//  UPDATE: 19-Aug-24: incrementDate() now supports non-whole numbers. So to keep 2.999 from having a "gap" between 2.999 and 3.000, round values to the nearest 1/12th (1 month of 1 year).
		//					   non-whole numbers for anything other than a year doesn't make much sense, ie: 0.5 months, or 0.5 weeks, etc...
		//					   But we do want to be able to support 1.25 years, or 1.5 years.
		$minimum_length_of_service_date = TTDate::incrementDate( $ud_obj->getLengthOfServiceDate(), ( round( $this->getMinimumLengthOfService() * 12 ) / 12 ), $minimum_length_of_service_unit_name );
		$maximum_length_of_service_date = TTDate::incrementDate( $ud_obj->getLengthOfServiceDate(), ( round( $this->getMaximumLengthOfService() * 12 ) / 12 ), $maximum_length_of_service_unit_name );
		Debug::Text( '  Current Date: '. TTDate::getDate('DATE+TIME', $epoch ) .' Must be >= ' . TTDate::getDate('DATE+TIME', $minimum_length_of_service_date ) .' < '. TTDate::getDate('DATE+TIME', $maximum_length_of_service_date ) .'('. $maximum_length_of_service_date .') Units: '. $minimum_length_of_service_unit_name .' / '. $maximum_length_of_service_unit_name .''.' Base On Length of Service Date: ' . TTDate::getDate( 'DATE+TIME', $ud_obj->getLengthOfServiceDate() ), __FILE__, __LINE__, __METHOD__, 10 );

		$minimum_length_of_service_result = false;
		$maximum_length_of_service_result = false;
		//Check minimum length of service
		if ( $this->getMinimumLengthOfService() == 0
				|| ( $this->getMinimumLengthOfServiceUnit() != 50 && $epoch >= $minimum_length_of_service_date ) //For minimum date, use '>=', so it starts exactly when they would expect. Make sure we don't compare against partial days, as its possible for a pay stub to fall between brackets (ie: 5.0 -> 7.999, 8.0 -> 9.999)
				|| ( $this->getMinimumLengthOfServiceUnit() == 50 && $worked_time >= $this->getMinimumLengthOfService() ) ) {
			$minimum_length_of_service_result = true;
		}


		if ( $this->getMaximumLengthOfServiceDays() < 0 ) {
			if ( $ud_obj->getEndDate() != '' ) {
				$length_of_service_date = $ud_obj->getEndDate();
			} else {
				$length_of_service_date = $ud_obj->getUserObject()->getTerminationDate();
			}

			if ( $length_of_service_date != '' ) {
				//Disable when the length of service date falls within the pay period, or if its before the start the of the pay period (so it doesn't trigger on post-termination pay stubs)
				//This is useful for disabling a Tax/Deduction record on the employees final pay stub.
				if ( ( $length_of_service_date >= $pay_period_start_date && $length_of_service_date <= $epoch ) || $length_of_service_date <= $pay_period_start_date ) {
					Debug::Text( '   Final Pay Stub, disabling due to negative maximum length of service. Based On: ' . TTDate::getDate( 'DATE+TIME', $ud_obj->getLengthOfServiceDate() ), __FILE__, __LINE__, __METHOD__, 10 );
					$maximum_length_of_service_result = false;
				} else {
					$maximum_length_of_service_result = true;
				}
			} else {
				$maximum_length_of_service_result = true; //No end date specified, so assume its always valid.
			}
		} else {
			//Check maximum length of service.
			if ( $this->getMaximumLengthOfService() == 0
					|| ( $this->getMaximumLengthOfServiceUnit() != 50 && $epoch < $maximum_length_of_service_date ) //For maximum date use '<', so its harder for the user to mistakenly cause overlapping brackets. Max of 7.999 and min of the next bracket 8.000 should never match. Make sure we don't compare against partial days as its possible for a pay stub to fall between brackets (ie: 5.0 -> 7.999, 8.0 -> 9.999)
					|| ( $this->getMaximumLengthOfServiceUnit() == 50 && $worked_time < $this->getMaximumLengthOfService() ) ) {
				$maximum_length_of_service_result = true;
			}
		}

		//Debug::Text('   Min Result: '. (int)$minimum_length_of_service_result .' Max Result: '. (int)$maximum_length_of_service_result, __FILE__, __LINE__, __METHOD__, 10);
		if ( $minimum_length_of_service_result == true && $maximum_length_of_service_result == true ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $birth_date          EPOCH
	 * @param int $pp_transaction_date EPOCH
	 * @return bool
	 */
	function isCPPAgeEligible( $birth_date, $pp_transaction_date = null ) {
		//CPP starts on the first transaction date *after* the month they turn 18, and ends on the first transaction date after they turn 70.
		//  Basically so pro-rating is for whole months rather than partial months.
		//http://www.cra-arc.gc.ca/tx/bsnss/tpcs/pyrll/clcltng/cpp-rpc/prrtng/xmpls-eng.html#xmpl_1
		// If they are 18 on Feb 16, and the PP runs: Feb 2nd to Feb 17th Transact: Feb 25, CPP would NOT be deducted, as they turned 18 in the same month.
		// If they are 18 on Feb 16, and the PP runs: Feb 15nd to Feb 28th Transact: Mar 2nd, CPP would be deducted, as they turned 18 in the month befor they were paid.
		// If they are 70 on Feb 16, and the PP runs: Feb 2nd to Feb 17th Transact: Feb 25, CPP would be deducted, as they turned 18 in the same month.
		// If they are 70 on Feb 16, and the PP runs: Feb 15nd to Feb 28th Transact: Mar 2nd, CPP would NOT be deducted, as they turned 18 in the month befor they were paid.
		if ( $pp_transaction_date != '' ) { //CPP
			$user_age = TTDate::getYearDifference( $birth_date, $pp_transaction_date );
			Debug::Text( 'User Age: ' . $user_age . ' Min: ' . $this->getMinimumUserAge() . ' Max: ' . $this->getMaximumUserAge(), __FILE__, __LINE__, __METHOD__, 10 );

			//Make sure if no age is specified (no birth date), that we just always assume CPP is being deducted.
			if ( $user_age == '' || $this->getMinimumUserAge() == 0 || $this->getMaximumUserAge() == 0 ) {
				return true;
			} else if ( $user_age >= ( $this->getMinimumUserAge() - 0.25 ) && $user_age <= ( $this->getMaximumUserAge() + 0.25 ) ) {
				//Check to see if they are within a few months of the min/max ages
				if ( abs( $user_age - $this->getMinimumUserAge() ) <= 0.25 ) {
					$birth_month = TTDate::getBirthDateAtAge( $birth_date, $this->getMinimumUserAge() );
					Debug::Text( '  aBirth Date: ' . TTDate::getDate( 'DATE', $birth_date ) . ' Birth Month: ' . TTDate::getDate( 'DATE+TIME', $birth_month ) . ' Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $pp_transaction_date ), __FILE__, __LINE__, __METHOD__, 10 );

					//Start deducting CPP
					if ( TTDate::getEndDayEpoch( $pp_transaction_date ) > TTDate::getEndMonthEpoch( $birth_month ) ) {
						Debug::Text( 'Transaction date is after the end of the birth month, eligible...', __FILE__, __LINE__, __METHOD__, 10 );

						return true;
					} else {
						Debug::Text( 'Transaction date is before the end of the birth month, skipping...', __FILE__, __LINE__, __METHOD__, 10 );

						return false;
					}
				} else if ( abs( $user_age - $this->getMaximumUserAge() ) <= 0.25 ) {
					$birth_month = TTDate::getBirthDateAtAge( $birth_date, $this->getMaximumUserAge() );
					Debug::Text( '  bBirth Date: ' . TTDate::getDate( 'DATE', $birth_date ) . ' Birth Month: ' . TTDate::getDate( 'DATE+TIME', $birth_month ) . ' Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $pp_transaction_date ), __FILE__, __LINE__, __METHOD__, 10 );

					//Stop deducting CPP.
					if ( TTDate::getEndDayEpoch( $pp_transaction_date ) > TTDate::getEndMonthEpoch( $birth_month ) ) {
						Debug::Text( 'Transaction date is after the end of the birth month, skipping...', __FILE__, __LINE__, __METHOD__, 10 );

						return false;
					} else {
						Debug::Text( 'Transaction date is before the end of the birth month, eligible...', __FILE__, __LINE__, __METHOD__, 10 );

						return true;
					}
				} else {
					Debug::Text( 'Not within 1 year of Min/Max age, assuming always eligible...', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		} else {
			Debug::Text( 'ERROR: Transaction date not specified...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * Check to see if user age eligibility criteria are actually specified for this Tax/Deduction record.
	 * @return bool
	 */
	function isUserAgeEligibility() {
		if ( $this->getMinimumUserAge() != 0 || $this->getMaximumUserAge() != 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $birth_date          EPOCH
	 * @param int $pp_end_date         EPOCH
	 * @param int $pp_transaction_date EPOCH
	 * @return bool
	 */
	function isActiveUserAge( $birth_date, $pp_end_date = null, $pp_transaction_date = null ) {
		//If no user age elibibility criteria is defined, return TRUE as if they are eligible.
		if ( $this->isUserAgeEligibility() == false ) {
			return true;
		}

		$user_age = TTDate::getYearDifference( $birth_date, $pp_end_date );
		Debug::Text( 'User Age: ' . $user_age . ' Min: ' . $this->getMinimumUserAge() . ' Max: ' . $this->getMaximumUserAge(), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $this->getCalculation() == 90 || $this->getCalculation() == 92 ) { //CPP & CPP2
			return $this->isCPPAgeEligible( $birth_date, $pp_transaction_date );
		} else {
			if ( ( $this->getMinimumUserAge() == 0 || $user_age >= $this->getMinimumUserAge() ) && ( $this->getMaximumUserAge() == 0 || $user_age < $this->getMaximumUserAge() ) ) { //Use >= and < to match length of service behavior.
				return true;
			}
		}

		return false;
	}

	/**
	 * @param string $calculation_id UUID
	 * @return bool
	 */
	function isCountryCalculationID( $calculation_id ) {
		if ( in_array( $calculation_id, $this->country_calculation_ids ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $calculation_id UUID
	 * @return bool
	 */
	function isProvinceCalculationID( $calculation_id ) {
		if ( in_array( $calculation_id, $this->province_calculation_ids ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $calculation_id UUID
	 * @return bool
	 */
	function isDistrictCalculationID( $calculation_id ) {
		if ( in_array( $calculation_id, $this->district_calculation_ids ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return int
	 */
	function getCalculation() {
		return (int)$this->getGenericDataValue( 'calculation_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCalculation( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'calculation_id', $value );
	}

	//Make function get calculation name based on below function.
	function getCalculationName() {
		return Option::getByKey( $this->getCalculation(), $this->getOptions( 'calculation' ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getCalculationOrder() {
		return $this->getGenericDataValue( 'calculation_order' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCalculationOrder( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'calculation_order', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCountry() {
		return $this->getGenericDataValue( 'country' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCountry( $value ) {
		$value = strtoupper( trim( $value ) );
		if ( $value == TTUUID::getZeroID() ) {
			$value = '';
		}

		return $this->setGenericDataValue( 'country', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getProvince() {
		return $this->getGenericDataValue( 'province' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setProvince( $value ) {
		Debug::Text( 'Country: ' . $this->getCountry() . ' Province: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'province', strtoupper( trim( $value ) ) );
	}

	/**
	 * Used for getting district name on W2's
	 * @return bool|mixed|null
	 */
	function getDistrictName() {
		$retval = null;

		if ( $this->getDistrict() == 'ALL' || $this->getDistrict() == '00' || $this->getDistrict() == '' ) {
			//Used to use UserValue5, but I think that has all been switched to CompanyValue1.
			//if ( $this->getUserValue5() != '' ) {
			//	$retval = $this->getUserValue5();
			//}

			if ( $this->getCompanyValue1() != '' ) {
				$retval = $this->getCompanyValue1();
			}
		} else {
			$retval = $this->getDistrict();
		}

		return $retval;
	}

	/**
	 * @return bool|mixed
	 */
	function getDistrict() {
		return strtoupper( $this->getGenericDataValue( 'district' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDistrict( $value ) {
		$value = strtoupper( trim( $value ) );
		Debug::Text( 'Country: ' . $this->getCountry() . ' District: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'district', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue1() {
		return $this->getGenericDataValue( 'company_value1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue1( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value1', $value );
	}

	/**
	 * @return bool
	 */
	function getCompanyValue1Options() {
		//Debug::Text('Calculation: '. $this->getCalculation(), __FILE__, __LINE__, __METHOD__, 10);
		switch ( $this->getCalculation() ) {
			case 100:
			case 200:
			case 300:
				$options = $this->getOptions( 'tax_formula_type' );
				break;
		}

		if ( isset( $options ) ) {
			return $options;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue2() {
		return $this->getGenericDataValue( 'company_value2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue2( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value2', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue3() {
		return $this->getGenericDataValue( 'company_value3' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue3( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value3', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue4() {
		return $this->getGenericDataValue( 'company_value4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue4( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value4', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue5() {
		return $this->getGenericDataValue( 'company_value5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue5( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value5', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue6() {
		return $this->getGenericDataValue( 'company_value6' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue6( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value6', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue7() {
		return $this->getGenericDataValue( 'company_value7' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue7( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value7', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue8() {
		return $this->getGenericDataValue( 'company_value8' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue8( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value8', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue9() {
		return $this->getGenericDataValue( 'company_value9' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue9( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value9', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompanyValue10() {
		return $this->getGenericDataValue( 'company_value10' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCompanyValue10( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'company_value10', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getUserValue1() {
		return $this->getGenericDataValue( 'user_value1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue1( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value1', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue2() {
		return $this->getGenericDataValue( 'user_value2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue2( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value2', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue3() {
		return $this->getGenericDataValue( 'user_value3' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue3( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value3', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue4() {
		return $this->getGenericDataValue( 'user_value4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue4( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value4', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue5() {
		return $this->getGenericDataValue( 'user_value5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue5( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value5', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue6() {
		return $this->getGenericDataValue( 'user_value6' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue6( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value6', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue7() {
		return $this->getGenericDataValue( 'user_value7' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue7( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value7', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue8() {
		return $this->getGenericDataValue( 'user_value8' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue8( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value8', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue9() {
		return $this->getGenericDataValue( 'user_value9' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue9( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value9', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserValue10() {
		$retval = $this->getGenericDataValue( 'user_value10' );

		//When its a Federal/Province/State/District income tax calculation, default to "0" or "Not Exempt"
		//We do this so we don't have to add a "Exempt" dropdown box to the main Tax/Deduction edit screen, as no company will have everyone as exempt by default.
		if ( $retval == '' && in_array( $this->getCalculation(), [ 100, 200, 300 ] ) && $this->getCountry() == 'US' ) {
			$retval = 0;
		}

		return $retval;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue10( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value10', $value );
	}

	/**
	 * @return bool
	 */
	function getUserValue1Options() {
		//Debug::Text('Calculation: '. $this->getCalculation(), __FILE__, __LINE__, __METHOD__, 10);
		switch ( $this->getCalculation() ) {
			case 100:
				//Debug::Text('Country: '. $this->getCountry(), __FILE__, __LINE__, __METHOD__, 10);
				//if ( $this->getCountry() == 'CA' ) {
				//} else
				if ( $this->getCountry() == 'US' ) {
					$options = $this->getOptions( 'federal_filing_status' );
				}
				break;
			case 200:
				//Debug::Text('Country: '. $this->getCountry(), __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Text('Province: '. $this->getProvince(), __FILE__, __LINE__, __METHOD__, 10);
				//if ( $this->getCountry() == 'CA' ) {
				//} else
				if ( $this->getCountry() == 'US' ) {
					$state_options_var = strtolower( 'state_' . $this->getProvince() . '_filing_status_options' );
					//Debug::Text('Specific State Variable Name: '. $state_options_var, __FILE__, __LINE__, __METHOD__, 10);
					if ( isset( $this->$state_options_var ) ) {
						//Debug::Text('Specific State Options: ', __FILE__, __LINE__, __METHOD__, 10);
						$options = $this->getOptions( $state_options_var );
					} else if ( $this->getProvince() == 'IL' ) {
						$options = false;
					} else {
						//Debug::Text('Default State Options: ', __FILE__, __LINE__, __METHOD__, 10);
						$options = $this->getOptions( 'state_filing_status' );
					}
				}

				break;
		}

		if ( isset( $options ) ) {
			return $options;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getPayStubEntryAccount() {
		return $this->getGenericDataValue( 'pay_stub_entry_account_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayStubEntryAccount( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'pay_stub_entry_account_id', $value );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue1() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value1' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue1( $value ) {
		return $this->setGenericDataValue( 'lock_user_value1', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue2() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value2' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue2( $value ) {
		return $this->setGenericDataValue( 'lock_user_value2', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue3() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value3' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue3( $value ) {
		return $this->setGenericDataValue( 'lock_user_value3', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue4() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value4' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue4( $value ) {
		return $this->setGenericDataValue( 'lock_user_value4', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue5() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value5' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue5( $value ) {
		return $this->setGenericDataValue( 'lock_user_value5', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue6() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value6' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue6( $value ) {
		return $this->setGenericDataValue( 'lock_user_value6', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue7() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value7' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue7( $value ) {
		return $this->setGenericDataValue( 'lock_user_value7', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue8() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value8' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue8( $value ) {
		return $this->setGenericDataValue( 'lock_user_value8', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue9() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value9' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue9( $value ) {
		return $this->setGenericDataValue( 'lock_user_value9', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getLockUserValue10() {
		return $this->fromBool( $this->getGenericDataValue( 'lock_user_value10' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLockUserValue10( $value ) {
		return $this->setGenericDataValue( 'lock_user_value10', $this->toBool( $value ) );
	}

	/**
	 * @param string $id UUID
	 * @return mixed|string
	 */
	function getAccountAmountTypeMap( $id ) {
		if ( isset( $this->account_amount_type_map[$id] ) ) {
			return $this->account_amount_type_map[$id];
		}

		Debug::text( 'Unable to find Account Amount mapping... ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		return 'amount'; //Default to amount.
	}

	/**
	 * @param string $id UUID
	 * @return mixed|string
	 */
	function getAccountAmountTypePSEntriesMap( $id ) {
		if ( isset( $this->account_amount_type_ps_entries_map[$id] ) ) {
			return $this->account_amount_type_ps_entries_map[$id];
		}

		Debug::text( 'Unable to find Account Amount PS Entries mapping... ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		return 'current'; //Default to current entries.
	}


	/**
	 * @return bool|mixed
	 */
	function getIncludeAccountAmountType() {
		return $this->getGenericDataValue( 'include_account_amount_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeAccountAmountType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'include_account_amount_type_id', $value );
	}

	/**
	 * @return array|bool|mixed|null
	 */
	function getIncludePayStubEntryAccount() {
		$cache_id = 'include_pay_stub_entry-' . $this->getId();
		$list = $this->getCache( $cache_id );
		if ( $list === false ) {
			//Debug::text('Caching Include IDs: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$cdpsealf = TTnew( 'CompanyDeductionPayStubEntryAccountListFactory' ); /** @var CompanyDeductionPayStubEntryAccountListFactory $cdpsealf */
			$cdpsealf->getByCompanyDeductionIdAndTypeId( $this->getId(), 10 );

			$list = null;
			foreach ( $cdpsealf as $obj ) {
				$list[] = $obj->getPayStubEntryAccount();
			}
			$this->saveCache( $list, $cache_id );
		} //else { //Debug::text('Reading Cached Include IDs: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($list, 'Include IDs: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $list ) && is_array( $list ) ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setIncludePayStubEntryAccount( $ids ) {
		Debug::text( 'Setting Include IDs : ', __FILE__, __LINE__, __METHOD__, 10 );
		if ( !is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		if ( is_array( $ids ) ) {
			$tmp_ids = [];
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$cdpsealf = TTnew( 'CompanyDeductionPayStubEntryAccountListFactory' ); /** @var CompanyDeductionPayStubEntryAccountListFactory $cdpsealf */
				$cdpsealf->getByCompanyDeductionIdAndTypeId( $this->getId(), 10 );

				foreach ( $cdpsealf as $obj ) {
					$id = $obj->getPayStubEntryAccount();
					//Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						//Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						//Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */

			foreach ( $ids as $id ) {
				if ( $id != false && isset( $ids ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID() && !in_array( $id, $tmp_ids ) ) {
					$cdpseaf = TTnew( 'CompanyDeductionPayStubEntryAccountFactory' ); /** @var CompanyDeductionPayStubEntryAccountFactory $cdpseaf */
					$cdpseaf->setCompanyDeduction( $this->getId() );
					$cdpseaf->setType( 10 ); //Include
					$cdpseaf->setPayStubEntryAccount( $id );

					$obj = $psealf->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'include_pay_stub_entry_account',
												   $cdpseaf->isValid(),
												   TTi18n::gettext( 'Include Pay Stub Account is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$cdpseaf->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getExcludeAccountAmountType() {
		return $this->getGenericDataValue( 'exclude_account_amount_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeAccountAmountType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'exclude_account_amount_type_id', $value );
	}

	/**
	 * @return array|bool|mixed|null
	 */
	function getExcludePayStubEntryAccount() {
		$cache_id = 'exclude_pay_stub_entry-' . $this->getId();
		$list = $this->getCache( $cache_id );
		if ( $list === false ) {
			//Debug::text('Caching Exclude IDs: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
			$cdpsealf = TTnew( 'CompanyDeductionPayStubEntryAccountListFactory' ); /** @var CompanyDeductionPayStubEntryAccountListFactory $cdpsealf */
			$cdpsealf->getByCompanyDeductionIdAndTypeId( $this->getId(), 20 );

			$list = null;
			foreach ( $cdpsealf as $obj ) {
				$list[] = $obj->getPayStubEntryAccount();
			}

			$this->saveCache( $list, $cache_id );
		} //else { //Debug::text('Reading Cached Exclude IDs: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);

		if ( isset( $list ) && is_array( $list ) ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setExcludePayStubEntryAccount( $ids ) {
		Debug::text( 'Setting Exclude IDs : ', __FILE__, __LINE__, __METHOD__, 10 );
		if ( !is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		if ( is_array( $ids ) ) {
			$tmp_ids = [];
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$cdpsealf = TTnew( 'CompanyDeductionPayStubEntryAccountListFactory' ); /** @var CompanyDeductionPayStubEntryAccountListFactory $cdpsealf */
				$cdpsealf->getByCompanyDeductionIdAndTypeId( $this->getId(), 20 );
				foreach ( $cdpsealf as $obj ) {
					$id = $obj->getPayStubEntryAccount();
					//Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						//Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						//Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */

			foreach ( $ids as $id ) {
				if ( $id != false && isset( $ids ) && $id != TTUUID::getZeroID() && $id != TTUUID::getNotExistID() && !in_array( $id, $tmp_ids ) ) {
					$cdpseaf = TTnew( 'CompanyDeductionPayStubEntryAccountFactory' ); /** @var CompanyDeductionPayStubEntryAccountFactory $cdpseaf */
					$cdpseaf->setCompanyDeduction( $this->getId() );
					$cdpseaf->setType( 20 ); //Include
					$cdpseaf->setPayStubEntryAccount( $id );

					$obj = $psealf->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'exclude_pay_stub_entry_account',
												   $cdpseaf->isValid(),
												   TTi18n::gettext( 'Exclude Pay Stub Account is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$cdpseaf->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return array|bool
	 */
	function getUser() {
		//Cache the user list to help performance in TaxSummaryReport
		if ( $this->users === null ) {
			$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
			$udlf->getByCompanyIdAndCompanyDeductionId( $this->getCompany(), $this->getId() );

			$this->users = [];
			foreach ( $udlf as $obj ) {
				$this->users[] = $obj->getUser();
			}

			if ( empty( $this->users ) == false ) {
				return $this->users;
			}

			return false;
		} else {
			return $this->users;
		}
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setUser( $ids ) {
		if ( !is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		if ( is_array( $ids ) ) {
			$tmp_ids = [];
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
				$udlf->getByCompanyIdAndCompanyDeductionId( $this->getCompany(), $this->getId() );
				foreach ( $udlf as $obj ) {
					$id = $obj->getUser();
					//Debug::text('ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						//Debug::text('Deleting: '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						//Debug::text('NOT Deleting : '. $id, __FILE__, __LINE__, __METHOD__, 10);
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			foreach ( $ids as $id ) {
				if ( $id != false && isset( $ids ) && !in_array( $id, $tmp_ids ) ) {
					$udf = TTnew( 'UserDeductionFactory' ); /** @var UserDeductionFactory $udf */
					$udf->setUser( $id );
					$udf->setCompanyDeduction( $this->getId() );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ( $this->Validator->isTrue( 'user',
													   $udf->isValid(),
													   TTi18n::gettext( 'Selected employee is invalid' ) . ' (' . $obj->getFullName() . ')' ) ) {
							$udf->save();
						}
					}
				}
			}

			$this->users = null; //Clear cache.

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return mixed
	 */
	function getTotalUsers() {
		$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */
		$total_users = $udlf->getTotalByCompanyDeductionId( $this->getId() );

		return $total_users;
	}

	/**
	 * Expands Total PS accounts (ie: Total Gross, Total Deduction, Total Employer Contributions, Net Pay) into their individual PS accounts.
	 * @param string $ids           UUID
	 * @param bool $include_net_pay Check to see if Net Pay PS account is in $ids, and expand all earnings and deductions.
	 * @return array
	 */
	function getExpandedPayStubEntryAccountIDs( $ids, $include_net_pay = false ) {
		//Debug::Arr($ids, 'Total Gross ID: '. $this->getPayStubEntryAccountLinkObject()->getTotalGross() .' IDs:', __FILE__, __LINE__, __METHOD__, 10);
		$ids = (array)$ids;
		$type_ids = [];

		if ( $include_net_pay == true ) {
			//If Net Pay is included
			$net_pay_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalNetPay(), $ids );
			if ( $net_pay_key !== false ) {
				$type_ids[] = 10;
				$type_ids[] = 20;
				unset( $ids[$net_pay_key] );
			}
			unset( $net_pay_key );
		}


		$total_gross_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $ids );
		if ( $total_gross_key !== false ) {
			$type_ids[] = 10;
			//$type_ids[] = 60; //Automatically inlcude Advance Earnings here?
			unset( $ids[$total_gross_key] );
		}
		unset( $total_gross_key );

		$total_employee_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $ids );
		if ( $total_employee_deduction_key !== false ) {
			$type_ids[] = 20;
			unset( $ids[$total_employee_deduction_key] );
		}
		unset( $total_employee_deduction_key );

		$total_employer_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $ids );
		if ( $total_employer_deduction_key !== false ) {
			$type_ids[] = 30;
			unset( $ids[$total_employer_deduction_key] );
		}
		unset( $total_employer_deduction_key );

		$psea_ids_from_type_ids = [];
		if ( empty( $type_ids ) == false ) {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
			$psea_ids_from_type_ids = $psealf->getByCompanyIdAndStatusIdAndTypeIdArray( $this->getCompany(), [ 10, 20 ], $type_ids, false );
			if ( is_array( $psea_ids_from_type_ids ) ) {
				$psea_ids_from_type_ids = array_keys( $psea_ids_from_type_ids );
			}
		}

		$retval = array_unique( array_merge( $ids, $psea_ids_from_type_ids ) );

		//Debug::Arr($retval, 'Retval: ', __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @param PayStubFactory $pay_stub_obj
	 * @param string|array $ids UUID
	 * @param string $ps_entries
	 * @param string $return_value
	 * @return bool|string
	 */
	function getPayStubEntryAmountSum( $pay_stub_obj, $ids, $ps_entries = 'current', $return_value = 'amount' ) {
		if ( !is_object( $pay_stub_obj ) ) {
			return false;
		}

		if ( !is_array( $ids ) ) {
			return false;
		}

		$type_ids = [];

		$total_gross_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalGross(), $ids );
		if ( $total_gross_key !== false ) {
			$type_ids[] = 10;
			$type_ids[] = 60; //Automatically inlcude Advance Earnings here?
			unset( $ids[$total_gross_key] );
		}
		unset( $total_gross_key );

		$total_employee_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployeeDeduction(), $ids );
		if ( $total_employee_deduction_key !== false ) {
			$type_ids[] = 20;
			unset( $ids[$total_employee_deduction_key] );
		}
		unset( $total_employee_deduction_key );

		$total_employer_deduction_key = array_search( $this->getPayStubEntryAccountLinkObject()->getTotalEmployerDeduction(), $ids );
		if ( $total_employer_deduction_key !== false ) {
			$type_ids[] = 30;
			unset( $ids[$total_employer_deduction_key] );
		}
		unset( $total_employer_deduction_key );

		$type_amount_arr = [];
		$type_amount_arr[$return_value] = 0;

		if ( empty( $type_ids ) == false ) {
			$type_amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( $ps_entries, $type_ids );
		}
		$amount_arr = [];
		$amount_arr[$return_value] = 0;
		if ( count( $ids ) > 0 ) {
			//Still other IDs left to total.
			$amount_arr = $pay_stub_obj->getSumByEntriesArrayAndTypeIDAndPayStubAccountID( $ps_entries, null, $ids );
		}

		$retval = TTMath::add( $type_amount_arr[$return_value], $amount_arr[$return_value] );

		Debug::text( 'Type Amount: ' . $type_amount_arr[$return_value] . ' Regular Amount: ' . $amount_arr[$return_value] . ' Total: ' . $retval . ' Return Value: ' . $return_value . ' PS Entries: ' . $ps_entries, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @param PayStubFactory $pay_stub_obj
	 * @param int $include_account_amount_type_id ID
	 * @param int $exclude_account_amount_type_id ID
	 * @return bool|string
	 */
	function getCalculationPayStubAmount( $pay_stub_obj, $include_account_amount_type_id = null, $exclude_account_amount_type_id = null ) {
		if ( !is_object( $pay_stub_obj ) ) {
			return false;
		}

		$include_ids = $this->getIncludePayStubEntryAccount();
		$exclude_ids = $this->getExcludePayStubEntryAccount();

		$is_included = false;
		$is_excluded = false;

		//This totals up the includes, and minuses the excludes.
		if ( isset( $include_account_amount_type_id ) ) {
			$include_account_amount_type = $include_account_amount_type_id;
			$is_included = true;
		} else {
			$include_account_amount_type = $this->getIncludeAccountAmountType();
		}

		if ( isset( $exclude_account_amount_type_id ) ) {
			$exclude_account_amount_type = $exclude_account_amount_type_id;
			$is_excluded = true;
		} else {
			$exclude_account_amount_type = $this->getExcludeAccountAmountType();
		}

		$include = $this->getPayStubEntryAmountSum( $pay_stub_obj, $include_ids, $this->getAccountAmountTypePSEntriesMap( $include_account_amount_type ), $this->getAccountAmountTypeMap( $include_account_amount_type ) );
		$exclude = $this->getPayStubEntryAmountSum( $pay_stub_obj, $exclude_ids, $this->getAccountAmountTypePSEntriesMap( $exclude_account_amount_type ), $this->getAccountAmountTypeMap( $exclude_account_amount_type ) );
		Debug::text( 'Include Amount: ' . $include . ' Exclude Amount: ' . $exclude, __FILE__, __LINE__, __METHOD__, 10 );

		//Allow negative values to be returned, as we need to do calculation on accruals and such that may be negative values.
		if ( $is_included == true && $is_excluded == true ) {
			$amount = TTMath::sub( $include, $exclude );
		} else if ( $is_included == true ) {
			$amount = $include;
		} else if ( $is_excluded == true ) {
			$amount = $exclude;
		} else {
			$amount = TTMath::sub( $include, $exclude );
		}

		Debug::text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

		return $amount;
	}

	/**
	 * Parse the formula and pull out any pay stub accounts that should be considered "required" accounts for purposes of determing the calculation order.
	 */
	function getFormulaPayStubAccounts() {
		$retarr = [];

		if ( $this->getCalculation() == 69 ) { //69=Custom Formula

			$formula = $this->getCompanyValue1();
			if ( $formula != '' ) {
				$columns = TTMathFormula::parseColumnsFromFormula( $formula );
				if ( is_array( $columns ) && count( $columns ) > 0 ) {
					foreach ( $columns as $column => $value ) {
						if ( strpos( $column, 'lookback_' ) !== false ) {
							continue;
						}

						if ( strpos( $column, '_ytd_' ) !== false ) { //Ignore any YTD columns, as we have those from the prior pay stub, and they shouldn't affect the calculation order because of that.
							continue;
						}

						//Check for include/exclude, or if its just a single pay stub account.
						if ( strpos( $column, 'include_pay_stub_' ) !== false ) {
							$retarr = array_merge( $retarr, (array)$this->getIncludePayStubEntryAccount() );
						} else if ( strpos( $column, 'exclude_pay_stub_' ) !== false ) {
							$retarr = array_merge( $retarr, (array)$this->getExcludePayStubEntryAccount() );
						} else {
							//PA (Amount), PR (Rate), PU (Units), PUY (Units YTD), PY (Amount YTD)
							//  PUY and PY are YTD amounts, so they need to be excluded, so they don't affect calculation order. YTD amounts we get from the prior pay stub.
							if ( preg_match( '/P[ARU]:([a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12})/', $column, $matches ) ) {
								if ( TTUUID::isUUID( $matches[1] ) == true ) {
									$retarr[] = $matches[1];
								}
							}
						}
					}
				}

				$retarr = array_unique( $retarr );
			}
		}

		return $retarr;
	}

	//
	// Lookback functions.
	//
	/**
	 * @return bool
	 */
	function isLookbackCalculation() {
		if ( $this->getCalculation() == 69 && isset( $this->length_of_service_multiplier[(int)$this->getCompanyValue3()] ) && $this->getCompanyValue2() > 0 ) {
			return true;
		}

		return false;
	}

	/**
	 * @param int $include_account_amount_type_id ID
	 * @param int $exclude_account_amount_type_id ID
	 * @return int|string
	 */
	function getLookbackCalculationPayStubAmount( $include_account_amount_type_id = null, $exclude_account_amount_type_id = null ) {
		$amount = 0;
		if ( isset( $this->lookback_pay_stub_lf ) && $this->lookback_pay_stub_lf->getRecordCount() > 0 ) {
			foreach ( $this->lookback_pay_stub_lf as $pay_stub_obj ) {
				$pay_stub_obj->loadCurrentPayStubEntries();
				$amount = TTMath::add( $amount, $this->getCalculationPayStubAmount( $pay_stub_obj, $include_account_amount_type_id, $exclude_account_amount_type_id ) );
			}
		}

		Debug::text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

		return $amount;
	}

	/**
	 * Handle look back period, which is always based on the transaction date *before* the current pay periods transaction date.
	 * @param object $pay_period_obj
	 * @return array
	 */
	function getLookbackStartAndEndDates( $pay_period_obj ) {
		$retarr = [
				'start_date' => false,
				//Make sure we don't include the current transaction date, as we can always access the current amounts with other variables.
				//This also allows us to calculate lookbacks first and avoid circular dependancies in other calculations.
				'end_date'   => TTDate::getEndDayEpoch( ( (int)$pay_period_obj->getTransactionDate() - 86400 ) ),
		];
		if ( $this->getCompanyValue3() == 100 ) { //Pay Periods
			//Not implemented for now, as it has many issues, things like gaps between pay periods, employees switching between pay period schedules, etc...
			//We could just count the number of pay stubs, but this has issues with employees leaving and returning and such.
			unset( $pay_period_obj );             //Satisfy Coding Standards
		} else {
			$length_of_service_days = TTMath::mul( (float)$this->getCompanyValue2(), $this->length_of_service_multiplier[(int)$this->getCompanyValue3()], 4 );
			$retarr['start_date'] = TTDate::getBeginDayEpoch( ( (int)$pay_period_obj->getTransactionDate() - ( $length_of_service_days * 86400 ) ) );
		}

		Debug::text( 'Start Date: ' . TTDate::getDate( 'DATE+TIME', $retarr['start_date'] ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $retarr['end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * @param string $user_id UUID
	 * @param object $pay_period_obj
	 * @return array
	 */
	function getLookbackPayStubs( $user_id, $pay_period_obj ) {
		$lookback_dates = $this->getLookbackStartAndEndDates( $pay_period_obj );

		$pslf = TTNew( 'PayStubListFactory' ); /** @var PayStubListFactory $pslf */
		$this->lookback_pay_stub_lf = $pslf->getByUserIdAndStartDateAndEndDate( $user_id, $lookback_dates['start_date'], $lookback_dates['end_date'] );

		$retarr = [];
		if ( $this->lookback_pay_stub_lf->getRecordCount() > 0 ) {
			//Get lookback first pay and last pay period dates.
			$retarr['first_pay_stub_start_date'] = $this->lookback_pay_stub_lf->getCurrent()->getStartDate();
			$retarr['first_pay_stub_end_date'] = $this->lookback_pay_stub_lf->getCurrent()->getEndDate();
			$retarr['first_pay_stub_transaction_date'] = $this->lookback_pay_stub_lf->getCurrent()->getTransactionDate();

			$this->lookback_pay_stub_lf->rs->MoveLast();

			$retarr['last_pay_stub_start_date'] = $this->lookback_pay_stub_lf->getCurrent()->getStartDate();
			$retarr['last_pay_stub_end_date'] = $this->lookback_pay_stub_lf->getCurrent()->getEndDate();
			$retarr['last_pay_stub_transaction_date'] = $this->lookback_pay_stub_lf->getCurrent()->getTransactionDate();

			$retarr['total_pay_stubs'] = $this->lookback_pay_stub_lf->getRecordCount();
			Debug::text( 'Total Pay Stubs: ' . $retarr['total_pay_stubs'] . ' First Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $retarr['first_pay_stub_transaction_date'] ) . ' Last Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $retarr['last_pay_stub_transaction_date'] ), __FILE__, __LINE__, __METHOD__, 10 );

			$this->lookback_pay_stub_lf->rs->MoveFirst();
		} else {
			$retarr = false;
		}

		return $retarr;
	}

	/**
	 * @param PayStubFactory $pay_stub_obj
	 * @return bool|int|string
	 */
	function getCalculationYTDAmount( $pay_stub_obj ) {
		if ( !is_object( $pay_stub_obj ) ) {
			return false;
		}

		//This totals up the includes, and minuses the excludes.
		$include_ids = $this->getIncludePayStubEntryAccount();
		$exclude_ids = $this->getExcludePayStubEntryAccount();

		//Use current YTD amount because if we only include previous pay stub YTD amounts we won't include YTD adjustment PS amendments on the current PS.
		$include = $this->getPayStubEntryAmountSum( $pay_stub_obj, $include_ids, 'previous+ytd_adjustment', 'ytd_amount' );
		$exclude = $this->getPayStubEntryAmountSum( $pay_stub_obj, $exclude_ids, 'previous+ytd_adjustment', 'ytd_amount' );

		$amount = TTMath::sub( $include, $exclude );

		if ( $amount < 0 ) {
			$amount = 0;
		}

		Debug::text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

		return $amount;
	}

	/**
	 * @param PayStubFactory $pay_stub_obj
	 * @return bool|int|string
	 */
	function getPayStubEntryAccountYTDAmount( $pay_stub_obj ) {
		if ( !is_object( $pay_stub_obj ) ) {
			return false;
		}

		//Use current YTD amount because if we only include previous pay stub YTD amounts we won't include YTD adjustment PS amendments on the current PS.
		$previous_amount = $this->getPayStubEntryAmountSum( $pay_stub_obj, [ $this->getPayStubEntryAccount() ], 'previous+ytd_adjustment', 'ytd_amount' );
		$current_amount = $this->getPayStubEntryAmountSum( $pay_stub_obj, [ $this->getPayStubEntryAccount() ], 'current', 'amount' );

		$amount = TTMath::add( $previous_amount, $current_amount );
		if ( $amount < 0 ) {
			$amount = 0;
		}

		Debug::text( 'Amount: ' . $amount, __FILE__, __LINE__, __METHOD__, 10 );

		return $amount;
	}

	/**
	 * @return string
	 */
	function getJavaScriptArrays() {
		$output = 'var fields = ' . Misc::getJSArray( $this->calculation_id_fields, 'fields', true );

		$output .= 'var country_calculation_ids = ' . Misc::getJSArray( $this->country_calculation_ids );
		$output .= 'var province_calculation_ids = ' . Misc::getJSArray( $this->province_calculation_ids );
		$output .= 'var district_calculation_ids = ' . Misc::getJSArray( $this->district_calculation_ids );

		return $output;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $type_id
	 * @param $name
	 * @return bool
	 */
	static function getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, $type_id, $name ) {
		$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
		$psealf->getByCompanyIdAndTypeAndFuzzyName( $company_id, $type_id, $name );
		if ( $psealf->getRecordCount() > 0 ) {
			return $psealf->getCurrent()->getId();
		}

		return false;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Legal Entity
		if ( $this->getLegalEntity() !== false ) {
			if ( $this->getLegalEntity() != TTUUID::getZeroID() && $this->getLegalEntity() != TTUUID::getNotExistID() ) {
				$clf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $clf */
				$this->Validator->isResultSetWithRows( 'legal_entity_id',
													   $clf->getByID( $this->getLegalEntity() ),
													   TTi18n::gettext( 'Legal Entity is invalid' )
				);
			}
		}

		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);

		// Payroll remittance agency
		if ( $this->getPayrollRemittanceAgency() !== false && $this->getPayrollRemittanceAgency() != TTUUID::getZeroID() ) {
			$clf = TTnew( 'PayrollRemittanceAgencyListFactory' ); /** @var PayrollRemittanceAgencyListFactory $clf */
			$this->Validator->isResultSetWithRows( 'payroll_remittance_agency_id',
												   $clf->getByID( $this->getPayrollRemittanceAgency() ),
												   TTi18n::gettext( 'Payroll remittance agency is invalid' )
			);
		}

		// Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status_id',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}
		// Name
		if ( $this->getName() !== false ) {
			if ( $this->getName() == '' ) {
				$this->Validator->isTrue( 'name',
										  false,
										  TTi18n::gettext( 'Name not specified' ) );
			}

			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isLength( 'name',
											$this->getName(),
											TTi18n::gettext( 'Name is too short or too long' ),
											2,
											100
				);

				$this->Validator->isHTML( 'name',
										  $this->getName(),
										  TTi18n::gettext( 'Name contains invalid special characters' ),
				);
			}

			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  $this->isUniqueName( $this->getName() ),
										  TTi18n::gettext( 'Name is already in use' )
				);
			}
		}

		// Pay Stub Entry Description
		if ( strlen( $this->getPayStubEntryDescription() ) != 0 ) {
			$this->Validator->isLength( 'pay_stub_entry_description',
										$this->getPayStubEntryDescription(),
										TTi18n::gettext( 'Description is too short or too long' ),
										0,
										100
			);

			$this->Validator->isHTML( 'pay_stub_entry_description',
									  $this->getPayStubEntryDescription(),
									  TTi18n::gettext( 'Description contains invalid special characters' ),
			);
		}
		// Start date
		if ( $this->getStartDate() != null ) {
			$this->Validator->isDate( 'start_date',
									  $this->getStartDate(),
									  TTi18n::gettext( 'Incorrect start date' )
			);
		}
		// End Date
		if ( $this->getEndDate() != null ) {
			$this->Validator->isDate( 'end_date',
									  $this->getEndDate(),
									  TTi18n::gettext( 'Incorrect end date' )
			);
		}
		// Minimum length of service days
		if ( $this->getMinimumLengthOfServiceDays() !== false && $this->getMinimumLengthOfServiceDays() >= 0 ) {
			$this->Validator->isFloat( 'minimum_length_of_service_days',
									   $this->getMinimumLengthOfServiceDays(),
									   TTi18n::gettext( 'Minimum length of service days is invalid' )
			);
		}
		// Maximum length of service days
		if ( $this->getMaximumLengthOfServiceDays() !== false ) {
			$this->Validator->isFloat( 'maximum_length_of_service_days',
									   $this->getMaximumLengthOfServiceDays(),
									   TTi18n::gettext( 'Maximum length of service days is invalid' )
			);
		}
		//  minimum length of service unit
		if ( !empty( $this->getMinimumLengthOfServiceUnit() ) ) {
			$this->Validator->inArrayKey( 'minimum_length_of_service_unit_id',
										  $this->getMinimumLengthOfServiceUnit(),
										  TTi18n::gettext( 'Incorrect minimum length of service unit' ),
										  $this->getOptions( 'length_of_service_unit' )
			);
		}
		// maximum length of service unit
		if ( !empty( $this->getMaximumLengthOfServiceUnit() ) ) {
			$this->Validator->inArrayKey( 'maximum_length_of_service_unit_id',
										  $this->getMaximumLengthOfServiceUnit(),
										  TTi18n::gettext( 'Incorrect maximum length of service unit' ),
										  $this->getOptions( 'length_of_service_unit' )
			);
		}
		// Minimum length of service
		if ( $this->getMinimumLengthOfService() !== false && $this->getMinimumLengthOfService() >= 0 ) {
			$this->Validator->isFloat( 'minimum_length_of_service',
									   $this->getMinimumLengthOfService(),
									   TTi18n::gettext( 'Minimum length of service is invalid' )
			);
		}
		// Maximum length of service
		if ( $this->getMaximumLengthOfService() !== false ) {
			$this->Validator->isFloat( 'maximum_length_of_service',
									   $this->getMaximumLengthOfService(),
									   TTi18n::gettext( 'Maximum length of service is invalid' )
			);
		}

		// Minimum employee age
		if ( $this->getMinimumUserAge() !== false && $this->getMinimumUserAge() >= 0 ) {
			$this->Validator->isFloat( 'minimum_user_age',
									   $this->getMinimumUserAge(),
									   TTi18n::gettext( 'Minimum employee age is invalid' )
			);
		}

		// Maximum employee age
		if ( $this->getMaximumUserAge() !== false && $this->getMaximumUserAge() >= 0 ) {
			$this->Validator->isFloat( 'maximum_user_age',
									   $this->getMaximumUserAge(),
									   TTi18n::gettext( 'Maximum employee age is invalid' )
			);
		}

		// Contributing Pay Code Policy
		if ( $this->getLengthOfServiceContributingPayCodePolicy() !== false && $this->getLengthOfServiceContributingPayCodePolicy() != TTUUID::getZeroID() ) {
			$csplf = TTnew( 'ContributingPayCodePolicyListFactory' ); /** @var ContributingPayCodePolicyListFactory $csplf */
			$this->Validator->isResultSetWithRows( 'length_of_service_contributing_pay_code_policy_id',
												   $csplf->getByID( $this->getLengthOfServiceContributingPayCodePolicy() ),
												   TTi18n::gettext( 'Contributing Pay Code Policy is invalid' )
			);
		}

		// Apply Frequency
		if ( !empty( $this->getApplyFrequency() ) ) {
			$this->Validator->inArrayKey( 'apply_frequency_id',
										  $this->getApplyFrequency(),
										  TTi18n::gettext( 'Incorrect frequency' ),
										  $this->getOptions( 'apply_frequency' )
			);
		}

		if ( $this->getApplyFrequency() == 35 ) { //35=Semi-Monthly
			// Frequency day of month1
			if ( !empty( $this->getApplyFrequencyDayOfMonth() ) ) {
				$this->Validator->inArrayKey( 'apply_frequency_day_of_month1',
											  $this->getApplyFrequencyDayOfMonth1(),
											  TTi18n::gettext( 'Incorrect frequency primary day of month' ),
											  TTDate::getDayOfMonthArray()
				);
			}

			// Frequency day of month1
			if ( !empty( $this->getApplyFrequencyDayOfMonth() ) ) {
				$this->Validator->inArrayKey( 'apply_frequency_day_of_month2',
											  $this->getApplyFrequencyDayOfMonth2(),
											  TTi18n::gettext( 'Incorrect frequency secondary day of month' ),
											  TTDate::getDayOfMonthArray()
				);
			}

		} else {
			// Frequency Month
			if ( !empty( $this->getApplyFrequencyMonth() ) && $this->getApplyFrequency() != 35 ) { //35=Semi-Monthly
				$this->Validator->inArrayKey( 'apply_frequency_month',
											  $this->getApplyFrequencyMonth(),
											  TTi18n::gettext( 'Incorrect frequency month' ),
											  TTDate::getMonthOfYearArray()
				);
			}

			// Frequency day of month
			if ( !empty( $this->getApplyFrequencyDayOfMonth() ) ) {
				$this->Validator->inArrayKey( 'apply_frequency_day_of_month',
											  $this->getApplyFrequencyDayOfMonth(),
											  TTi18n::gettext( 'Incorrect frequency day of month' ),
											  TTDate::getDayOfMonthArray()
				);
			}

			// Frequency day of week
			if ( !empty( $this->getApplyFrequencyDayOfWeek() ) ) {
				$this->Validator->inArrayKey( 'apply_frequency_day_of_week',
											  $this->getApplyFrequencyDayOfWeek(),
											  TTi18n::gettext( 'Incorrect frequency day of week' ),
											  TTDate::getDayOfWeekArray()
				);
			}

			//  Frequency quarter month
			if ( !empty( $this->getApplyFrequencyQuarterMonth() ) ) {
				$this->Validator->isGreaterThan( 'apply_frequency_quarter_month',
												 $this->getApplyFrequencyQuarterMonth(),
												 TTi18n::gettext( 'Incorrect frequency quarter month' ),
												 1
				);
				if ( $this->Validator->isError( 'apply_frequency_quarter_month' ) == false ) {
					$this->Validator->isLessThan( 'apply_frequency_quarter_month',
												  $this->getApplyFrequencyQuarterMonth(),
												  TTi18n::gettext( 'Incorrect frequency quarter month' ),
												  3
					);
				}
			}
		}


		// Payroll Run Type
		if ( !empty( $this->getApplyPayrollRunType() ) ) {
			$this->Validator->inArrayKey( 'apply_payroll_run_type_id',
										  $this->getApplyPayrollRunType(),
										  TTi18n::gettext( 'Incorrect payroll run type' ),
										  $this->getOptions( 'apply_payroll_run_type' )
			);
		}

		// Calculation
		//This is always cast to an int, and during mass edit will be 0
		if ( $this->getCalculation() !== 0 ) {
			$this->Validator->inArrayKey( 'calculation_id',
										  $this->getCalculation(),
										  TTi18n::gettext( 'Incorrect Calculation' ),
										  $this->getOptions( 'calculation' )
			);
		}

		// Calculation Order
		if ( $this->getCalculationOrder() !== false ) {
			$this->Validator->isNumeric( 'calculation_order',
										 $this->getCalculationOrder(),
										 TTi18n::gettext( 'Invalid Calculation Order' )
			);
		}

		$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
		// Country
		if ( $this->getCountry() != '' ) {
			$this->Validator->inArrayKey( 'country',
										  $this->getCountry(),
										  TTi18n::gettext( 'Invalid Country' ),
										  $cf->getOptions( 'country' )
			);
		}

		// Province
		if ( $this->getProvince() != '' ) {
			$options_arr = $cf->getOptions( 'province' );
			if ( isset( $options_arr[$this->getCountry()] ) ) {
				$options = $options_arr[$this->getCountry()];
			} else {
				$options = [];
			}
			$this->Validator->inArrayKey( 'province',
										  $this->getProvince(),
										  TTi18n::gettext( 'Invalid Province/State' ),
										  $options
			);
			unset( $options, $options_arr );
		}

		// District
		if ( $this->getDistrict() != '' && $this->getDistrict() != '00' && $this->getDistrict() != TTUUID::getZeroID() ) {
			$options_arr = $cf->getOptions( 'district' );
			if ( isset( $options_arr[$this->getCountry()][$this->getProvince()] ) ) {
				$options = $options_arr[$this->getCountry()][$this->getProvince()];
			} else {
				$options = [];
			}
			$this->Validator->inArrayKey( 'district',
										  $this->getDistrict(),
										  TTi18n::gettext( 'Invalid District' ),
										  $options
			);
			unset( $options, $options_arr );
		}

		// Company Value 1
		if ( $this->getCompanyValue1() != '' ) {
			$this->Validator->isLength( 'company_value1',
										$this->getCompanyValue1(),
										TTi18n::gettext( 'Company Value 1 is too short or too long' ),
										1,
										4096
			);
		}
		// Company Value 2
		if ( $this->getCompanyValue2() != '' ) {
			$this->Validator->isLength( 'company_value2',
										$this->getCompanyValue2(),
										TTi18n::gettext( 'Company Value 2 is too short or too long' ),
										1,
										20
			);
		}
		// Company Value 3
		if ( $this->getCompanyValue3() != '' ) {
			$this->Validator->isLength( 'company_value3',
										$this->getCompanyValue3(),
										TTi18n::gettext( 'Company Value 3 is too short or too long' ),
										1,
										20
			);
		}
		// Company Value 4
		if ( $this->getCompanyValue4() != '' ) {
			$this->Validator->isLength( 'company_value4',
										$this->getCompanyValue4(),
										TTi18n::gettext( 'Company Value 4 is too short or too long' ),
										1,
										20
			);
		}
		// Company Value 5
		if ( $this->getCompanyValue5() != '' ) {
			$this->Validator->isLength( 'company_value5',
										$this->getCompanyValue5(),
										TTi18n::gettext( 'Company Value 5 is too short or too long' ),
										1,
										20
			);
		}
		// Company Value 6
		if ( $this->getCompanyValue6() != '' ) {
			$this->Validator->isLength( 'company_value6',
										$this->getCompanyValue6(),
										TTi18n::gettext( 'Company Value 6 is too short or too long' ),
										1,
										20
			);
		}
		// Company Value 7
		if ( $this->getCompanyValue7() != '' ) {
			$this->Validator->isLength( 'company_value7',
										$this->getCompanyValue7(),
										TTi18n::gettext( 'Company Value 7 is too short or too long' ),
										1,
										20
			);
		}
		// Company Value 8
		if ( $this->getCompanyValue8() != '' ) {
			$this->Validator->isLength( 'company_value8',
										$this->getCompanyValue8(),
										TTi18n::gettext( 'Company Value 8 is too short or too long' ),
										1,
										20
			);
		}
		// Company Value 9
		if ( $this->getCompanyValue9() != '' ) {
			$this->Validator->isLength( 'company_value9',
										$this->getCompanyValue9(),
										TTi18n::gettext( 'Company Value 9 is too short or too long' ),
										1,
										20
			);
		}
		// Company Value 10
		if ( $this->getCompanyValue10() != '' ) {
			$this->Validator->isLength( 'company_value10',
										$this->getCompanyValue10(),
										TTi18n::gettext( 'Company Value 10 is too short or too long' ),
										1,
										20
			);
		}

		// User Value 1
		if ( $this->getUserValue1() != '' ) {
			$this->Validator->isLength( 'user_value1',
										$this->getUserValue1(),
										TTi18n::gettext( 'User Value 1 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 2
		if ( $this->getUserValue2() != '' ) {
			$this->Validator->isLength( 'user_value2',
										$this->getUserValue2(),
										TTi18n::gettext( 'User Value 2 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 3
		if ( $this->getUserValue3() != '' ) {
			$this->Validator->isLength( 'user_value3',
										$this->getUserValue3(),
										TTi18n::gettext( 'User Value 3 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 4
		if ( $this->getUserValue4() != '' ) {
			$this->Validator->isLength( 'user_value4',
										$this->getUserValue4(),
										TTi18n::gettext( 'User Value 4 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 5
		if ( $this->getUserValue5() != '' ) {
			$this->Validator->isLength( 'user_value5',
										$this->getUserValue5(),
										TTi18n::gettext( 'User Value 5 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 6
		if ( $this->getUserValue6() != '' ) {
			$this->Validator->isLength( 'user_value6',
										$this->getUserValue6(),
										TTi18n::gettext( 'User Value 6 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 7
		if ( $this->getUserValue7() != '' ) {
			$this->Validator->isLength( 'user_value7',
										$this->getUserValue7(),
										TTi18n::gettext( 'User Value 7 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 8
		if ( $this->getUserValue8() != '' ) {
			$this->Validator->isLength( 'user_value8',
										$this->getUserValue8(),
										TTi18n::gettext( 'User Value 8 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 9
		if ( $this->getUserValue9() != '' ) {
			$this->Validator->isLength( 'user_value9',
										$this->getUserValue9(),
										TTi18n::gettext( 'User Value 9 is too short or too long' ),
										1,
										20
			);
		}
		// User Value 10
		if ( $this->getUserValue10() != '' ) {
			$this->Validator->isLength( 'user_value10',
										$this->getUserValue10(),
										TTi18n::gettext( 'User Value 10 is too short or too long' ),
										1,
										20
			);
		}

		// Pay Stub Account
		if ( $this->getPayStubEntryAccount() != TTUUID::getZeroID() && $this->getPayStubEntryAccount() !== false ) {
			$psealf = TTnew( 'PayStubEntryAccountListFactory' ); /** @var PayStubEntryAccountListFactory $psealf */
			$this->Validator->isResultSetWithRows( 'pay_stub_entry_account',
												   $psealf->getByID( $this->getPayStubEntryAccount() ),
												   TTi18n::gettext( 'Pay Stub Account is invalid' )
			);
		}

		// Include account amount type
		if ( $this->getIncludeAccountAmountType() !== false ) {
			$this->Validator->inArrayKey( 'include_account_amount_type_id',
										  $this->getIncludeAccountAmountType(),
										  TTi18n::gettext( 'Incorrect include account amount type' ),
										  $this->getOptions( 'account_amount_type' )
			);
		}

		//  Exclude account amount type
		if ( $this->getExcludeAccountAmountType() !== false ) {
			$this->Validator->inArrayKey( 'exclude_account_amount_type_id',
										  $this->getExcludeAccountAmountType(),
										  TTi18n::gettext( 'Incorrect exclude account amount type' ),
										  $this->getOptions( 'account_amount_type' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL && $this->getCalculation() == 69 ) {
			$valid_formula = TTMathFormula::ValidateFormula( TTMathFormula::translateVariables( $this->getCompanyValue1(), TTMathFormula::clearVariables( Misc::trimSortPrefix( $this->getOptions( 'formula_variables' ) ) ) ) );

			if ( $valid_formula != false ) {
				$this->Validator->isTrue( 'company_value1',
										  false,
										  implode( "\n", $valid_formula ) );
			}
		}

		return true;
	}

	/**
	 * @return bool|mixed|string
	 */
	function getPayrollRemittanceAgencyIdByNameOrCalculation() {
		//Based on current object information, guess what payroll remittance agency it should be assigned to.

		$retval = false;

		$calculation_id = $this->getCalculation();
		$name = $this->getName();
		Debug::text( 'Search Name: ' . $name . ' Calculation ID: ' . $calculation_id, __FILE__, __LINE__, __METHOD__, 10 );

		//Try to base decision off the calculation type first.
		$calculation_type_map = [ 82, 83, 84, 85, 90, 91, 92, 100, 200, 210, 300 ];
		if ( in_array( $calculation_id, $calculation_type_map ) ) {
			Debug::text( 'Using calculation search...', __FILE__, __LINE__, __METHOD__, 10 );
			switch ( $calculation_id ) {
				case 82: //US - Medicare Formula (Employee)'),
				case 83: //US - Medicare Formula (Employer)'),
				case 84: //US - Social Security Formula (Employee)'),
				case 85: //US - Social Security Formula (Employer)'),
				case 89: //US - Federal Unemployment Insurance,
					$retval = '10:US:00:00:0010';
					break;
				case 90: //Canada - Custom Formulas CPP and EI
				case 91: //Canada - Custom Formulas CPP and EI
				case 92: //Canada - Custom Formulas CPP2
					$retval = '10:CA:00:00:0010';
					break;
				case 100: //Federal Income Tax Formula
					$retval = '10:' . $this->getCountry() . ':00:00:0010';
					break;
				case 200: //Province/State Income Tax Formula
					switch ( strtolower( $this->getCountry() ) ) {
						case 'ca':
							$retval = '10:CA:00:00:0010';
							break;
						default:
							$retval = '20:' . $this->getCountry() . ':' . $this->getProvince() . ':00:0010';
							break;
					}
					break;
				case 210: //US State Unemployment Insurance
					switch ( strtolower( $this->getCountry() ) ) {
						case 'us':
							$retval = '20:' . $this->getCountry() . ':' . $this->getProvince() . ':00:0020';
							break;
						default:
							break;
					}
					break;
				case 300: //District/County Income Tax Formula
					$retval = '20:' . $this->getCountry() . ':' . $this->getProvince() . ':' . $this->getDistrict() . ':0010';
					break;
			}
		} else {
			Debug::text( 'Using name search...', __FILE__, __LINE__, __METHOD__, 10 );
			//Fall back to name as a last resort.
			$name_map = [
				//Canada
				'CA - Addl. Income Tax'         => '10:CA:00:00:0010',
				'CPP - Employer'                => '10:CA:00:00:0010',
				'EI - Employer'                 => '10:CA:00:00:0010',

				//US
				'US - Addl. Income Tax'         => '10:US:00:00:0010',
				'Additional Federal Income Tax' => '10:US:00:00:0010',

				'US - Federal Unemployment Insurance'    => '10:US:00:00:0010',
				'Federal Unemployment Insurance'         => '10:US:00:00:0010',
				'FUTA'                                   => '10:US:00:00:0010',

				//US State Addl Income Tax
				'AL - Addl. Income Tax'                  => '20:US:AL:00:0010',
				'AK - Addl. Income Tax'                  => '20:US:AK:00:0010',
				'AZ - Addl. Income Tax'                  => '20:US:AZ:00:0010',
				'AR - Addl. Income Tax'                  => '20:US:AR:00:0010',
				//'CA - Addl. Income Tax' => '20:US:CA:00:0010', //This is a duplicate with Canada "CA - Addl. Income Tax"
				'CO - Addl. Income Tax'                  => '20:US:CO:00:0010',
				'CT - Addl. Income Tax'                  => '20:US:CT:00:0010',
				'DE - Addl. Income Tax'                  => '20:US:DE:00:0010',
				'DC - Addl. Income Tax'                  => '20:US:DC:00:0010',
				'FL - Addl. Income Tax'                  => '20:US:FL:00:0010',
				'GA - Addl. Income Tax'                  => '20:US:GA:00:0010',
				'HI - Addl. Income Tax'                  => '20:US:HI:00:0010',
				'ID - Addl. Income Tax'                  => '20:US:ID:00:0010',
				'IL - Addl. Income Tax'                  => '20:US:IL:00:0010',
				'IN - Addl. Income Tax'                  => '20:US:IN:00:0010',
				'IA - Addl. Income Tax'                  => '20:US:IA:00:0010',
				'KS - Addl. Income Tax'                  => '20:US:KS:00:0010',
				'KY - Addl. Income Tax'                  => '20:US:KY:00:0010',
				'LA - Addl. Income Tax'                  => '20:US:LA:00:0010',
				'ME - Addl. Income Tax'                  => '20:US:ME:00:0010',
				'MD - Addl. Income Tax'                  => '20:US:MD:00:0010',
				'MA - Addl. Income Tax'                  => '20:US:MA:00:0010',
				'MI - Addl. Income Tax'                  => '20:US:MI:00:0010',
				'MN - Addl. Income Tax'                  => '20:US:MN:00:0010',
				'MS - Addl. Income Tax'                  => '20:US:MS:00:0010',
				'MO - Addl. Income Tax'                  => '20:US:MO:00:0010',
				'MT - Addl. Income Tax'                  => '20:US:MT:00:0010',
				'NE - Addl. Income Tax'                  => '20:US:NE:00:0010',
				'NV - Addl. Income Tax'                  => '20:US:NV:00:0010',
				'NH - Addl. Income Tax'                  => '20:US:NH:00:0010',
				'NM - Addl. Income Tax'                  => '20:US:NM:00:0010',
				'NJ - Addl. Income Tax'                  => '20:US:NJ:00:0010',
				'NY - Addl. Income Tax'                  => '20:US:NY:00:0010',
				'NC - Addl. Income Tax'                  => '20:US:NC:00:0010',
				'ND - Addl. Income Tax'                  => '20:US:ND:00:0010',
				'OH - Addl. Income Tax'                  => '20:US:OH:00:0010',
				'OK - Addl. Income Tax'                  => '20:US:OK:00:0010',
				'OR - Addl. Income Tax'                  => '20:US:OR:00:0010',
				'PA - Addl. Income Tax'                  => '20:US:PA:00:0010',
				'RI - Addl. Income Tax'                  => '20:US:RI:00:0010',
				'SC - Addl. Income Tax'                  => '20:US:SC:00:0010',
				'SD - Addl. Income Tax'                  => '20:US:SD:00:0010',
				'TN - Addl. Income Tax'                  => '20:US:TN:00:0010',
				'TX - Addl. Income Tax'                  => '20:US:TX:00:0010',
				'UT - Addl. Income Tax'                  => '20:US:UT:00:0010',
				'VT - Addl. Income Tax'                  => '20:US:VT:00:0010',
				'VA - Addl. Income Tax'                  => '20:US:VA:00:0010',
				'WA - Addl. Income Tax'                  => '20:US:WA:00:0010',
				'WV - Addl. Income Tax'                  => '20:US:WV:00:0010',
				'WI - Addl. Income Tax'                  => '20:US:WI:00:0010',
				'WY - Addl. Income Tax'                  => '20:US:WY:00:0010',
				'AL - State Addl. Income Tax'            => '20:US:AL:00:0010',
				'AK - State Addl. Income Tax'            => '20:US:AK:00:0010',
				'AZ - State Addl. Income Tax'            => '20:US:AZ:00:0010',
				'AR - State Addl. Income Tax'            => '20:US:AR:00:0010',
				'CA - State Addl. Income Tax'            => '20:US:CA:00:0010',
				'CO - State Addl. Income Tax'            => '20:US:CO:00:0010',
				'CT - State Addl. Income Tax'            => '20:US:CT:00:0010',
				'DE - State Addl. Income Tax'            => '20:US:DE:00:0010',
				'DC - State Addl. Income Tax'            => '20:US:DC:00:0010',
				'FL - State Addl. Income Tax'            => '20:US:FL:00:0010',
				'GA - State Addl. Income Tax'            => '20:US:GA:00:0010',
				'HI - State Addl. Income Tax'            => '20:US:HI:00:0010',
				'ID - State Addl. Income Tax'            => '20:US:ID:00:0010',
				'IL - State Addl. Income Tax'            => '20:US:IL:00:0010',
				'IN - State Addl. Income Tax'            => '20:US:IN:00:0010',
				'IA - State Addl. Income Tax'            => '20:US:IA:00:0010',
				'KS - State Addl. Income Tax'            => '20:US:KS:00:0010',
				'KY - State Addl. Income Tax'            => '20:US:KY:00:0010',
				'LA - State Addl. Income Tax'            => '20:US:LA:00:0010',
				'ME - State Addl. Income Tax'            => '20:US:ME:00:0010',
				'MD - State Addl. Income Tax'            => '20:US:MD:00:0010',
				'MA - State Addl. Income Tax'            => '20:US:MA:00:0010',
				'MI - State Addl. Income Tax'            => '20:US:MI:00:0010',
				'MN - State Addl. Income Tax'            => '20:US:MN:00:0010',
				'MS - State Addl. Income Tax'            => '20:US:MS:00:0010',
				'MO - State Addl. Income Tax'            => '20:US:MO:00:0010',
				'MT - State Addl. Income Tax'            => '20:US:MT:00:0010',
				'NE - State Addl. Income Tax'            => '20:US:NE:00:0010',
				'NV - State Addl. Income Tax'            => '20:US:NV:00:0010',
				'NH - State Addl. Income Tax'            => '20:US:NH:00:0010',
				'NM - State Addl. Income Tax'            => '20:US:NM:00:0010',
				'NJ - State Addl. Income Tax'            => '20:US:NJ:00:0010',
				'NY - State Addl. Income Tax'            => '20:US:NY:00:0010',
				'NC - State Addl. Income Tax'            => '20:US:NC:00:0010',
				'ND - State Addl. Income Tax'            => '20:US:ND:00:0010',
				'OH - State Addl. Income Tax'            => '20:US:OH:00:0010',
				'OK - State Addl. Income Tax'            => '20:US:OK:00:0010',
				'OR - State Addl. Income Tax'            => '20:US:OR:00:0010',
				'PA - State Addl. Income Tax'            => '20:US:PA:00:0010',
				'RI - State Addl. Income Tax'            => '20:US:RI:00:0010',
				'SC - State Addl. Income Tax'            => '20:US:SC:00:0010',
				'SD - State Addl. Income Tax'            => '20:US:SD:00:0010',
				'TN - State Addl. Income Tax'            => '20:US:TN:00:0010',
				'TX - State Addl. Income Tax'            => '20:US:TX:00:0010',
				'UT - State Addl. Income Tax'            => '20:US:UT:00:0010',
				'VT - State Addl. Income Tax'            => '20:US:VT:00:0010',
				'VA - State Addl. Income Tax'            => '20:US:VA:00:0010',
				'WA - State Addl. Income Tax'            => '20:US:WA:00:0010',
				'WV - State Addl. Income Tax'            => '20:US:WV:00:0010',
				'WI - State Addl. Income Tax'            => '20:US:WI:00:0010',
				'WY - State Addl. Income Tax'            => '20:US:WY:00:0010',

				//US State Unemployment Insurance
				'AL - Unemployment Insurance'            => '20:US:AL:00:0020',
				'AK - Unemployment Insurance'            => '20:US:AK:00:0020',
				'AZ - Unemployment Insurance'            => '20:US:AZ:00:0020',
				'AR - Unemployment Insurance'            => '20:US:AR:00:0020',
				'CA - Unemployment Insurance'            => '20:US:CA:00:0010', //Combined with State.
				'CO - Unemployment Insurance'            => '20:US:CO:00:0020',
				'CT - Unemployment Insurance'            => '20:US:CT:00:0020',
				'DE - Unemployment Insurance'            => '20:US:DE:00:0020',
				'DC - Unemployment Insurance'            => '20:US:DC:00:0020',
				'FL - Unemployment Insurance'            => '20:US:FL:00:0020',
				'GA - Unemployment Insurance'            => '20:US:GA:00:0020',
				'HI - Unemployment Insurance'            => '20:US:HI:00:0020',
				'ID - Unemployment Insurance'            => '20:US:ID:00:0020',
				'IL - Unemployment Insurance'            => '20:US:IL:00:0020',
				'IN - Unemployment Insurance'            => '20:US:IN:00:0020',
				'IA - Unemployment Insurance'            => '20:US:IA:00:0020',
				'KS - Unemployment Insurance'            => '20:US:KS:00:0020',
				'KY - Unemployment Insurance'            => '20:US:KY:00:0020',
				'LA - Unemployment Insurance'            => '20:US:LA:00:0020',
				'ME - Unemployment Insurance'            => '20:US:ME:00:0020',
				'MD - Unemployment Insurance'            => '20:US:MD:00:0020',
				'MA - Unemployment Insurance'            => '20:US:MA:00:0020',
				'MI - Unemployment Insurance'            => '20:US:MI:00:0020',
				'MN - Unemployment Insurance'            => '20:US:MN:00:0020',
				'MS - Unemployment Insurance'            => '20:US:MS:00:0020',
				'MO - Unemployment Insurance'            => '20:US:MO:00:0020',
				'MT - Unemployment Insurance'            => '20:US:MT:00:0020',
				'NE - Unemployment Insurance'            => '20:US:NE:00:0020',
				'NV - Unemployment Insurance'            => '20:US:NV:00:0020',
				'NH - Unemployment Insurance'            => '20:US:NH:00:0020',
				'NM - Unemployment Insurance'            => '20:US:NM:00:0010', //Combined with State.
				'NJ - Unemployment Insurance'            => '20:US:NJ:00:0020',
				'NY - Unemployment Insurance'            => '20:US:NY:00:0010', //Combined with State.
				'NC - Unemployment Insurance'            => '20:US:NC:00:0020',
				'ND - Unemployment Insurance'            => '20:US:ND:00:0020',
				'OH - Unemployment Insurance'            => '20:US:OH:00:0020',
				'OK - Unemployment Insurance'            => '20:US:OK:00:0020',
				'OR - Unemployment Insurance'            => '20:US:OR:00:0010', //Combined with State.
				'PA - Unemployment Insurance'            => '20:US:PA:00:0020',
				'RI - Unemployment Insurance'            => '20:US:RI:00:0020',
				'SC - Unemployment Insurance'            => '20:US:SC:00:0020',
				'SD - Unemployment Insurance'            => '20:US:SD:00:0020',
				'TN - Unemployment Insurance'            => '20:US:TN:00:0020',
				'TX - Unemployment Insurance'            => '20:US:TX:00:0020',
				'UT - Unemployment Insurance'            => '20:US:UT:00:0020',
				'VT - Unemployment Insurance'            => '20:US:VT:00:0020',
				'VA - Unemployment Insurance'            => '20:US:VA:00:0020',
				'WA - Unemployment Insurance'            => '20:US:WA:00:0020',
				'WV - Unemployment Insurance'            => '20:US:WV:00:0020',
				'WI - Unemployment Insurance'            => '20:US:WI:00:0020',
				'WY - Unemployment Insurance'            => '20:US:WY:00:0020',

				//US State Unemployment Insurance - Employer
				'AL - Unemployment Insurance - Employer' => '20:US:AL:00:0020',
				'AK - Unemployment Insurance - Employer' => '20:US:AK:00:0020',
				'AZ - Unemployment Insurance - Employer' => '20:US:AZ:00:0020',
				'AR - Unemployment Insurance - Employer' => '20:US:AR:00:0020',
				'CA - Unemployment Insurance - Employer' => '20:US:CA:00:0010', //Combined with State.
				'CO - Unemployment Insurance - Employer' => '20:US:CO:00:0020',
				'CT - Unemployment Insurance - Employer' => '20:US:CT:00:0020',
				'DE - Unemployment Insurance - Employer' => '20:US:DE:00:0020',
				'DC - Unemployment Insurance - Employer' => '20:US:DC:00:0020',
				'FL - Unemployment Insurance - Employer' => '20:US:FL:00:0020',
				'GA - Unemployment Insurance - Employer' => '20:US:GA:00:0020',
				'HI - Unemployment Insurance - Employer' => '20:US:HI:00:0020',
				'ID - Unemployment Insurance - Employer' => '20:US:ID:00:0020',
				'IL - Unemployment Insurance - Employer' => '20:US:IL:00:0020',
				'IN - Unemployment Insurance - Employer' => '20:US:IN:00:0020',
				'IA - Unemployment Insurance - Employer' => '20:US:IA:00:0020',
				'KS - Unemployment Insurance - Employer' => '20:US:KS:00:0020',
				'KY - Unemployment Insurance - Employer' => '20:US:KY:00:0020',
				'LA - Unemployment Insurance - Employer' => '20:US:LA:00:0020',
				'ME - Unemployment Insurance - Employer' => '20:US:ME:00:0020',
				'MD - Unemployment Insurance - Employer' => '20:US:MD:00:0020',
				'MA - Unemployment Insurance - Employer' => '20:US:MA:00:0020',
				'MI - Unemployment Insurance - Employer' => '20:US:MI:00:0020',
				'MN - Unemployment Insurance - Employer' => '20:US:MN:00:0020',
				'MS - Unemployment Insurance - Employer' => '20:US:MS:00:0020',
				'MO - Unemployment Insurance - Employer' => '20:US:MO:00:0020',
				'MT - Unemployment Insurance - Employer' => '20:US:MT:00:0020',
				'NE - Unemployment Insurance - Employer' => '20:US:NE:00:0020',
				'NV - Unemployment Insurance - Employer' => '20:US:NV:00:0020',
				'NH - Unemployment Insurance - Employer' => '20:US:NH:00:0020',
				'NM - Unemployment Insurance - Employer' => '20:US:NM:00:0010', //Combined with State.
				'NJ - Unemployment Insurance - Employer' => '20:US:NJ:00:0020',
				'NY - Unemployment Insurance - Employer' => '20:US:NY:00:0010', //Combined with State.
				'NC - Unemployment Insurance - Employer' => '20:US:NC:00:0020',
				'ND - Unemployment Insurance - Employer' => '20:US:ND:00:0020',
				'OH - Unemployment Insurance - Employer' => '20:US:OH:00:0020',
				'OK - Unemployment Insurance - Employer' => '20:US:OK:00:0020',
				'OR - Unemployment Insurance - Employer' => '20:US:OR:00:0010', //Combined with State.
				'PA - Unemployment Insurance - Employer' => '20:US:PA:00:0020',
				'RI - Unemployment Insurance - Employer' => '20:US:RI:00:0020',
				'SC - Unemployment Insurance - Employer' => '20:US:SC:00:0020',
				'SD - Unemployment Insurance - Employer' => '20:US:SD:00:0020',
				'TN - Unemployment Insurance - Employer' => '20:US:TN:00:0020',
				'TX - Unemployment Insurance - Employer' => '20:US:TX:00:0020',
				'UT - Unemployment Insurance - Employer' => '20:US:UT:00:0020',
				'VT - Unemployment Insurance - Employer' => '20:US:VT:00:0020',
				'VA - Unemployment Insurance - Employer' => '20:US:VA:00:0020',
				'WA - Unemployment Insurance - Employer' => '20:US:WA:00:0020',
				'WV - Unemployment Insurance - Employer' => '20:US:WV:00:0020',
				'WI - Unemployment Insurance - Employer' => '20:US:WI:00:0020',
				'WY - Unemployment Insurance - Employer' => '20:US:WY:00:0020',

				//Other
				'AL - Employment Security Assessment'    => '20:US:AL:00:0020',
				'AZ - Job Training Surcharge'            => '20:US:AZ:00:0020',
				'CA - Disability Insurance'              => '20:US:CA:00:0010',
				'CA - State Disability Insurance'        => '20:US:CA:00:0010',
				'CA - Employee Training Tax'             => '20:US:CA:00:0010', //Same as Employment Training Tax below.
				'CA - Employment Training Tax'           => '20:US:CA:00:0010',
				'DC - Administrative Assessment'         => '20:US:DC:00:0020',
				'GA - Administrative Assessment'         => '20:US:GA:00:0020',
				'HI - E&T Assessment'                    => '20:US:HI:00:0020',
				//'HI - Health Insurance' => '20:US:HI:00:0020', //Needs confirmation.
				'HI - Disability Insurance'              => '20:US:HI:00:0020',
				'ID - Administrative Reserve'            => '20:US:ID:00:0020',
				'ID - Workforce Development'             => '20:US:ID:00:0020',
				'IA - Reserve Fund'                      => '20:US:IA:00:0020',
				'IA - Surcharge'                         => '20:US:IA:00:0020',
				'ME - Competitive Skills'                => '20:US:ME:00:0020',
				'MA - Health Insurance'                  => '20:US:MA:00:0020',
				'MA - Workforce Training Fund'           => '20:US:MA:00:0020',
				'MN - Workforce Enhancement Fee'         => '20:US:MA:00:0020',
				'MS - Training Contribution'             => '20:US:MS:00:0020',
				'MT - Administrative Fund'               => '20:US:MT:00:0020',
				'NE - SUIT'                              => '20:US:NE:00:0020',
				'NV - Career Enhancement'                => '20:US:NV:00:0020',
				'NH - Administrative Contribution'       => '20:US:NH:00:0020',
				'NJ - Disability Insurance - Employee'   => '20:US:NJ:00:0020',
				'NJ - Disability Insurance - Employer'   => '20:US:NJ:00:0020',
				'NJ - Workforce Development - Employee'  => '20:US:NJ:00:0020',
				'NJ - Workforce Development - Employer'  => '20:US:NJ:00:0020',
				'NJ - Healthcare Subsidy - Employee'     => '20:US:NJ:00:0020',
				'NJ - Healthcare Subsidy - Employer'     => '20:US:NJ:00:0020',
				'NJ - Family Leave Insurance'            => '20:US:NJ:00:0020',
				'NM - State Trust Fund'                  => '20:US:NM:00:0010',
				'NY - Reemployment Service Fund'         => '20:US:NY:00:0010',
				//'NY - Disability Insurance' => '20:US:NY:00:0010', //Private or State Agency
				//'NY - Disability Insurance - Male' => '20:US:NY:00:0010', //Private or State Agency
				//'NY - Disability Insurance - Female' => '20:US:NY:00:0010', //Private or State Agency
				'NY - Metropolitan Commuter Tax'         => '20:US:NY:00:0010',
				'NY - New York City Income Tax'          => '20:US:NY:00:0010',
				'NY - Yonkers Income Tax'                => '20:US:NY:00:0010',
				'OR - Workers Benefit - Employee'        => '20:US:OR:00:0010',
				'OR - Workers Benefit - Employer'        => '20:US:OR:00:0010',
				'OR - Tri-Met Transit District'          => '20:US:OR:00:0010',
				'OR - Lane Transit District'             => '20:US:OR:00:0010',
				'OR - Special Payroll Tax Offset'        => '20:US:OR:00:0010',
				'RI - Employment Security'               => '20:US:RI:00:0020',
				'RI - Job Development Fund'              => '20:US:RI:00:0020',
				'RI - Temporary Disability Insurance'    => '20:US:RI:00:0020',
				'SC - Contingency Assessment'            => '20:US:SC:00:0020',
				'SD - Investment Fee'                    => '20:US:SD:00:0020',
				'SD - UI Surcharge'                      => '20:US:SD:00:0020',
				'TN - Job Skills Fee'                    => '20:US:TN:00:0020',
				'TX - Employment & Training'             => '20:US:TX:00:0020',
				'TX - UI Obligation Assessment'          => '20:US:TX:00:0020',
				'WA - Industrial Insurance - Employee'   => '20:US:WA:00:0020',
				'WA - Industrial Insurance - Employer'   => '20:US:WA:00:0020',
				'WA - Employment Admin Fund'             => '20:US:WA:00:0020',
				'WY - Employment Support Fund'           => '20:US:WY:00:0020',
			];

			if ( is_object( $this->getLegalEntityObject() ) && $this->getLegalEntityObject()->getCountry() != '' ) {
				Debug::text( '  Adding country specific names...', __FILE__, __LINE__, __METHOD__, 10 );
				$country_name_map = [];
				switch ( strtolower( $this->getLegalEntityObject()->getCountry() ) ) {
					case 'ca':
						$country_name_map = [
								'Additional Income Tax'           => '10:CA:00:00:0010',
								'Workers Compensation - Employer' => '20:CA:' . strtoupper( $this->getLegalEntityObject()->getProvince() ) . ':00:0100',
								'WCB - Employer'                  => '20:CA:' . strtoupper( $this->getLegalEntityObject()->getProvince() ) . ':00:0100',
								'Workers Compensation'            => '20:CA:' . strtoupper( $this->getLegalEntityObject()->getProvince() ) . ':00:0100', //Wildcard
								'WCB'                             => '20:CA:' . strtoupper( $this->getLegalEntityObject()->getProvince() ) . ':00:0100', //Wildcard
								'WSIB'                            => '20:CA:ON:00:0100', //Wildcard
								'Child Support'                   => '20:CA:' . strtoupper( $this->getLegalEntityObject()->getProvince() ) . ':00:0040',
						];
						break;
					case 'us':
						$country_name_map = [
								'Workers Compensation' => '20:US:' . strtoupper( $this->getLegalEntityObject()->getProvince() ) . ':00:0100',
								'Child Support'        => '20:US:' . strtoupper( $this->getLegalEntityObject()->getProvince() ) . ':00:0040',
						];
						break;
					default:
						break;
				}

				$name_map = array_merge( $name_map, $country_name_map );
			}

			foreach ( $name_map as $tmp_name => $agency_id ) {
				if ( stripos( $name, $tmp_name ) !== false ) { //$tmp_name must be the needle so we can do wildcard search like 'WCB'
					$retval = $agency_id;
					break;
				}
			}
		}

		Debug::text( 'Retval: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @param int $date EPOCH
	 * @return bool
	 */
	function updateCompanyDeductionForTaxYear( $date ) {
		require_once( Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'payroll_deduction' . DIRECTORY_SEPARATOR . 'PayrollDeduction.class.php' );

		$c_obj = $this->getCompanyObject();
		if ( is_object( $c_obj ) && $c_obj->getStatus() != 30 ) {
			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */
			$cdlf->getAPISearchByCompanyIdAndArrayCriteria( $c_obj->getID(), [ 'calculation_id' => [ 100, 200 ], 'country' => 'CA' ] );
			Debug::text( 'Company: ' . $c_obj->getName() . ' Date: ' . TTDate::getDate( 'DATE+TIME', $date ) . ' Tax/Deduction Records to update: ' . $cdlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 9 );
			if ( $cdlf->getRecordCount() > 0 ) {
				foreach ( $cdlf as $cd_obj ) {
					$pd_obj = new PayrollDeduction( $cd_obj->getCountry(), $cd_obj->getProvince() ); /** @var PayrollDeduction_CA_Data $pd_obj */
					$pd_obj->setDate( $date );

					if ( $cd_obj->getCalculation() == 100 ) { //Federal
						//$pd_obj->setFederalTotalClaimAmount( $cd_obj->getUserValue1() );
						//$claim_amount = $pd_obj->getFederalTotalClaimAmount();

						//Force the claim amount to the basic no matter what. This avoids problems with claim amounts increasing/decreasing throughout the years and possibly being wrong for prolonged periods.
						//  **Note that this is only updating the CompanyDeduction record default values, so it only affects employees who are using default values and haven't had custom values entered.
						$claim_amount = $pd_obj->getBasicFederalClaimCodeAmount();
					} else if ( $cd_obj->getCalculation() == 200 ) { //Provincial
						//$pd_obj->setProvincialTotalClaimAmount( $cd_obj->getUserValue1() );
						//$claim_amount = $pd_obj->getProvincialTotalClaimAmount();

						$claim_amount = $pd_obj->getBasicProvinceClaimCodeAmount();
					}

					if ( (float)$cd_obj->getUserValue1() != (float)$claim_amount ) {
						Debug::text( 'Updating claim amounts... Old: ' . $cd_obj->getUserValue1() . ' New: ' . $claim_amount, __FILE__, __LINE__, __METHOD__, 9 );
						//Use a SQL query instead of modifying the CompanyDeduction class, as that can cause errors when we add columns to the table later on.
						$query = 'UPDATE ' . $cd_obj->getTable() . ' set user_value1 = ' . (float)$claim_amount . ' where id = \'' . TTUUID::castUUID( $cd_obj->getId() ) . '\'';
						$this->ExecuteSQL( $query );
					} else {
						Debug::text( 'Amount matches, no changes needed... Old: ' . $cd_obj->getUserValue1() . ' New: ' . $claim_amount, __FILE__, __LINE__, __METHOD__, 9 );
					}
				}
				Debug::text( 'Done updating claim amounts...', __FILE__, __LINE__, __METHOD__, 9 );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getStatus() == '' ) {
			$this->setStatus( 10 );
		}
		if ( $this->getType() == '' ) {
			$this->setType( 10 );
		}
		if ( $this->getFilterDateType() == '' ) {
			$this->setFilterDateType( 120 );
		}

		if ( $this->getName() == '' ) {
			$this->setName( '' );
		}

		if ( $this->getIncludeAccountAmountType() == '' ) {
			$this->setIncludeAccountAmountType( 10 );
		}
		if ( $this->getExcludeAccountAmountType() == '' ) {
			$this->setExcludeAccountAmountType( 10 );
		}

		//Set Length of service in days.
		$this->setMinimumLengthOfServiceDays( $this->getMinimumLengthOfService() );
		$this->setMaximumLengthOfServiceDays( $this->getMaximumLengthOfService() );

		if ( $this->getApplyFrequency() == '' ) {
			$this->setApplyFrequency( 10 ); //Each pay period.
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( 'include_pay_stub_entry-' . $this->getId() );
		$this->removeCache( 'exclude_pay_stub_entry-' . $this->getId() );

		if ( $this->getDeleted() == true ) {
			//Check if any users are assigned to this, if so, delete mappings.
			$udlf = TTnew( 'UserDeductionListFactory' ); /** @var UserDeductionListFactory $udlf */

			$udlf->StartTransaction();
			$udlf->getByCompanyIdAndCompanyDeductionId( $this->getCompany(), $this->getId() );
			if ( $udlf->getRecordCount() ) {
				foreach ( $udlf as $ud_obj ) {
					$ud_obj->setDeleted( true );
					if ( $ud_obj->isValid() ) {
						$ud_obj->Save();
					}
				}
			}
			$udlf->CommitTransaction();
		}

		return true;
	}

	/**
	 * Parse user values coming directly from the user, for handling things like parseFloat() which can be different for each calculation.
	 * This is also called from UserDeductionFactory.
	 * @param $calculation_id
	 * @param $data
	 * @return mixed
	 */
	function parseUserValues( $calculation_id, $data ) {
		switch ( $calculation_id ) {
			case 10: //Basic Percent
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				break;
			case 15: //Advanced Percent
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				if ( isset( $data['user_value2'] ) ) {
					$data['user_value2'] = ( $data['user_value2'] != '' ) ? TTi18n::parseFloat( $data['user_value2'] ) : $data['user_value2'];
				}
				if ( isset( $data['user_value3'] ) ) {
					$data['user_value3'] = ( $data['user_value3'] != '' ) ? TTi18n::parseFloat( $data['user_value3'] ) : $data['user_value3'];
				}
				break;
			case 16: //Advanced Percent (w/Target)
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				if ( isset( $data['user_value2'] ) ) {
					$data['user_value2'] = ( $data['user_value2'] != '' ) ? TTi18n::parseFloat( $data['user_value2'] ) : $data['user_value2'];
				}
				if ( isset( $data['user_value3'] ) ) {
					$data['user_value3'] = ( $data['user_value3'] != '' ) ? TTi18n::parseFloat( $data['user_value3'] ) : $data['user_value3'];
				}
				break;
			case 17: //Advanced Percent (Range Bracket)
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				if ( isset( $data['user_value2'] ) ) {
					$data['user_value2'] = ( $data['user_value2'] != '' ) ? TTi18n::parseFloat( $data['user_value2'] ) : $data['user_value2'];
				}
				if ( isset( $data['user_value3'] ) ) {
					$data['user_value3'] = ( $data['user_value3'] != '' ) ? TTi18n::parseFloat( $data['user_value3'] ) : $data['user_value3'];
				}
				if ( isset( $data['user_value4'] ) ) {
					$data['user_value4'] = ( $data['user_value4'] != '' ) ? TTi18n::parseFloat( $data['user_value4'] ) : $data['user_value4'];
				}
				break;
			case 18: //Advanced Percent (Tax Bracket)
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				if ( isset( $data['user_value2'] ) ) {
					$data['user_value2'] = ( $data['user_value2'] != '' ) ? TTi18n::parseFloat( $data['user_value2'] ) : $data['user_value2'];
				}
				if ( isset( $data['user_value3'] ) ) {
					$data['user_value3'] = ( $data['user_value3'] != '' ) ? TTi18n::parseFloat( $data['user_value3'] ) : $data['user_value3'];
				}
				if ( isset( $data['user_value4'] ) ) {
					$data['user_value4'] = ( $data['user_value4'] != '' ) ? TTi18n::parseFloat( $data['user_value4'] ) : $data['user_value4'];
				}
				break;
			case 19: //Advanced Percent (Tax Bracket Alternate)
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				if ( isset( $data['user_value2'] ) ) {
					$data['user_value2'] = ( $data['user_value2'] != '' ) ? TTi18n::parseFloat( $data['user_value2'] ) : $data['user_value2'];
				}
				if ( isset( $data['user_value3'] ) ) {
					$data['user_value3'] = ( $data['user_value3'] != '' ) ? TTi18n::parseFloat( $data['user_value3'] ) : $data['user_value3'];
				}
				if ( isset( $data['user_value4'] ) ) {
					$data['user_value4'] = ( $data['user_value4'] != '' ) ? TTi18n::parseFloat( $data['user_value4'] ) : $data['user_value4'];
				}
				if ( isset( $data['user_value5'] ) ) {
					$data['user_value5'] = ( $data['user_value5'] != '' ) ? TTi18n::parseFloat( $data['user_value5'] ) : $data['user_value5'];
				}
				break;
			case 20: //Fixed amount
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				break;
			case 30: //Fixed Amount (Range Bracket)
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				if ( isset( $data['user_value2'] ) ) {
					$data['user_value2'] = ( $data['user_value2'] != '' ) ? TTi18n::parseFloat( $data['user_value2'] ) : $data['user_value2'];
				}
				if ( isset( $data['user_value3'] ) ) {
					$data['user_value3'] = ( $data['user_value3'] != '' ) ? TTi18n::parseFloat( $data['user_value3'] ) : $data['user_value3'];
				}
				if ( isset( $data['user_value4'] ) ) {
					$data['user_value4'] = ( $data['user_value4'] != '' ) ? TTi18n::parseFloat( $data['user_value4'] ) : $data['user_value4'];
				}
				break;
			case 52: //Fixed Amount (w/Limit)
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				if ( isset( $data['user_value2'] ) ) {
					$data['user_value2'] = ( $data['user_value2'] != '' ) ? TTi18n::parseFloat( $data['user_value2'] ) : $data['user_value2'];
				}
				break;
			case 89: //US - Federal Unemployment Insurance (Employer)
			case 210: //US - State Unemployment Insurance (Employer)
				if ( isset( $data['user_value1'] ) ) {
					$data['user_value1'] = ( $data['user_value1'] != '' ) ? TTi18n::parseFloat( $data['user_value1'] ) : $data['user_value1'];
				}
				break;
			case 300: //Local (City/District/County) Income Tax Formula.
				if ( isset( $data['user_value2'] ) ) { //District / County Rate.
					$data['user_value2'] = ( $data['user_value2'] != '' ) ? TTi18n::parseFloat( $data['user_value2'] ) : $data['user_value2'];
				}
				break;
		}

		return $data;
	}

	function canBeCombined() {
		if ( strtoupper( $this->getCountry() ) == 'US' && in_array( $this->getCalculation(), [ 200, 300 ] ) ) {
			return false;
		}

		return true;
	}

	/**
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
						case 'calculation_id':
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );

								//As soon as we set the calculation_id, parse the UserValues before they are set later on.
								$data = $this->parseUserValues( $data['calculation_id'], $data );
							}
							break;
						case 'start_date':
						case 'end_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						case 'apply_frequency_day_of_month1':
						case 'apply_frequency_day_of_month2':
							//Make sure we don't save these values unless we are on Semi-Monthly frequency type, otherwise they overwrite the Monthly frequency "Day of Month" value preventing it from being saved properly.
							if ( $this->getApplyFrequency() == 35 ) { //35=Semi-Monthly
								if ( method_exists( $this, $function ) ) {
									$this->$function( $data[$key] );
								}
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @param bool $include_user_id
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false, $include_user_id = false ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'legal_entity_legal_name':
						case 'payroll_remittance_agency':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
						case 'type':
						case 'filter_date_type':
						case 'calculation':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_date':
						case 'end_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
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
			//When using the Edit Employee -> Tax tab, API::getCompanyDeduction() is called with include_user_id filter,
			//Since we only return the company deduction records, we have to pass this in separately so we can determine
			//if a child is assigned to a company deduction record.
			$this->getPermissionColumns( $data, $include_user_id, $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Tax / Deduction' ), null, $this->getTable(), $this );
	}
}

?>
