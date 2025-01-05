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
 * @package Modules\Policy
 */
class AccrualPolicyFactory extends Factory {
	protected $table = 'accrual_policy';
	protected $pk_sequence_name = 'accrual_policy_id_seq'; //PK Sequence name

	protected $company_obj = null;
	protected $milestone_objs = null;
	protected $contributing_shift_policy_obj = null;
	protected $eligible_contributing_shift_policy_obj = null;
	protected $user_modifier_obj = null;
	protected $length_of_service_contributing_pay_code_policy_obj = null;
	protected $is_catch_up_mode = false;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'minimum_time' )->setFunctionMap( 'MinimumTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'maximum_time' )->setFunctionMap( 'MaximumTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_id' )->setFunctionMap( 'ApplyFrequency' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_month' )->setFunctionMap( 'ApplyFrequencyMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_day_of_month' )->setFunctionMap( 'ApplyFrequencyDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_day_of_week' )->setFunctionMap( 'ApplyFrequencyDayOfWeek' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'milestone_rollover_hire_date' )->setFunctionMap( 'MilestoneRolloverHireDate' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'milestone_rollover_month' )->setFunctionMap( 'MilestoneRolloverMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'milestone_rollover_day_of_month' )->setFunctionMap( 'MilestoneRolloverDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'minimum_employed_days' )->setFunctionMap( 'MinimumEmployedDays' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'minimum_employed_days_catchup' )->setFunctionMap( 'MinimumEmployedDaysCatchup' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'enable_pay_stub_balance_display' )->setFunctionMap( 'EnablePayStubBalanceDisplay' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'apply_frequency_hire_date' )->setFunctionMap( 'ApplyFrequencyHireDate' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'contributing_shift_policy_id' )->setFunctionMap( 'ContributingShiftPolicy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'length_of_service_contributing_pay_code_policy_id' )->setFunctionMap( 'LengthOfServiceContributingPayCodePolicy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'accrual_policy_account_id' )->setFunctionMap( 'AccrualPolicyAccount' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'apply_frequency_quarter_month' )->setFunctionMap( 'ApplyFrequencyQuarterMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'enable_pro_rate_initial_period' )->setFunctionMap( 'EnableProRateInitialPeriod' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'enable_opening_balance' )->setFunctionMap( 'EnableOpeningBalance' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'excess_rollover_accrual_policy_account_id' )->setFunctionMap( 'ExcessRolloverAccrualPolicyAccount' )->setType( 'uuid' )->setIsNull( true ),

							TTSCol::new( 'eligible_period_id' )->setFunctionMap( 'EligibilityPeriod' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'minimum_eligible_time' )->setFunctionMap( 'MinimumEligibleTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'minimum_eligible_apply_retroactive' )->setFunctionMap( 'MinimumEligibleTimeApplyRetroactive' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'maximum_eligible_time' )->setFunctionMap( 'MaximumEligibleTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'eligible_contributing_shift_policy_id' )->setFunctionMap( 'EligibilityContributingShiftPolicy' )->setType( 'uuid' )->setIsNull( true ),

					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_accrual_policy' )->setLabel( TTi18n::getText( 'Accrual Policy' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ),
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) )->setWidth( '100%' ),
											TTSField::new( 'type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIAccrualPolicy' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'contributing_shift_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Contributing Shift Policy' ) )->setDataSource( TTSAPI::new( 'APIContributingShiftPolicy' )->setMethod( 'getContributingShiftPolicy' ) ),
											TTSField::new( 'accrual_policy_account_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Accrual Account' ) )->setDataSource( TTSAPI::new( 'APIAccrualPolicyAccount' )->setMethod( 'getAccrualPolicyAccount' ) ),
											TTSField::new( 'milestone_rollover_hire_date' )->setType( 'checkbox' )->setLabel( TTi18n::getText( "Employee's Hire Date" ) ),
											TTSField::new( 'milestone_rollover_month' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Month' ) ),
											TTSField::new( 'milestone_rollover_day_of_month' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Day of Month' ) ),
											TTSField::new( 'excess_rollover_accrual_policy_account_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Move Excess Rollover Time To' ) )->setDataSource( TTSAPI::new( 'APIAccrualPolicyAccount' )->setMethod( 'getAccrualPolicyAccount' ) ),
											TTSField::new( 'apply_frequency_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Frequency' ) )->setDataSource( TTSAPI::new( 'AccrualPolicy' )->setMethod( 'getOptions' )->setArg( 'apply_frequency' ) ),
											TTSField::new( 'apply_frequency_hire_date' )->setType( 'checkbox' )->setLabel( TTi18n::getText( "Employee's Hire Date" ) ),
											TTSField::new( 'apply_frequency_month' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Month' ) ),
											TTSField::new( 'apply_frequency_day_of_month' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Day of Month' ) ),
											TTSField::new( 'apply_frequency_day_of_week' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Day Of Week' ) ),
											TTSField::new( 'apply_frequency_quarter_month' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Month of Quarter' ) ),
											TTSField::new( 'minimum_employed_days' )->setType( 'integer' )->setLabel( TTi18n::getText( 'After Minimum Employed Days' ) ),
											TTSField::new( 'enable_opening_balance' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Enable Opening Balance' ) ),
											TTSField::new( 'enable_pro_rate_initial_period' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Prorate Initial Accrual Amount' ) )
									)
							),
							TTSTab::new( 'tab_eligibility' )->setLabel( TTi18n::getText( 'Eligibility' ) )->setFields(
									new TTSFields(
											TTSField::new( 'eligible_period_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Eligibility Period' ) )->setDataSource( TTSAPI::new( 'AccrualPolicy' )->setMethod( 'getOptions' )->setArg( 'eligible_period' ) ),
											TTSField::new( 'minimum_eligible_time' )->setType( 'integer' )->setLabel( TTi18n::getText( 'After Minimum Time' ) ),
											TTSField::new( 'minimum_eligible_apply_retroactive' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Apply Retroactively' ) ),
											TTSField::new( 'maximum_eligible_time' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Before Maximum Time' ) ),
											TTSField::new( 'eligible_contributing_shift_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Contributing Shift Policy' ) )->setDataSource( TTSAPI::new( 'APIContributingShiftPolicy' )->setMethod( 'getContributingShiftPolicy' ) ),
									)
							),
							TTSTab::new( 'tab_length_of_service_milestones' )->setLabel( TTi18n::getText( 'Length Of Service Milestones' ) )->setHTMLTemplate( '<div id="tab_length_of_service_milestones" class="edit-view-tab-outside">\n\t\t\t\t\t<div class="edit-view-tab" id="tab_length_of_service_milestones_content_div">\n\t\t\t\t\t\t<div class="first-column full-width-column"></div>\n\t\t\t\t\t\t<div class="inside-editor-div full-width-column">\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>' ),
							TTSTab::new( 'tab_employee_settings' )->setLabel( TTi18n::getText( 'Employee Settings' ) )->setInitCallback( 'initSubAccrualPolicyUserModifier' )->setDisplayOnMassEdit( false ),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'type_id' )->setType( 'numeric' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' ),
							TTSSearchField::new( 'description' )->setType( 'text' )->setColumn( 'a.description' ),
							TTSSearchField::new( 'length_of_service_contributing_pay_code_policy_id' )->setType( 'uuid' )->setColumn( 'a.length_of_service_contributing_pay_code_policy_id' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIAccrualPolicy' )->setMethod( 'getAccrualPolicy' )
									->setSummary( 'Get accrual policy records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIAccrualPolicy' )->setMethod( 'setAccrualPolicy' )
									->setSummary( 'Add or edit accrual policy records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIAccrualPolicy' )->setMethod( 'deleteAccrualPolicy' )
									->setSummary( 'Delete accrual policy records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIAccrualPolicy' )->setMethod( 'getAccrualPolicy' ) ),
											   ) ),
							TTSAPI::new( 'APIAccrualPolicy' )->setMethod( 'getAccrualPolicyDefaultData' )
									->setSummary( 'Get default accrual policy data used for creating new accrual policies. Use this before calling setAccrualPolicy to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'type':
				$retval = [
					//10 => TTi18n::gettext('Standard'), //No longer required after v8.0
					20 => TTi18n::gettext( 'Calendar Based' ),
					30 => TTi18n::gettext( 'Hour Based' ),
				];
				break;
			case 'apply_frequency':
				$retval = [
						10 => TTi18n::gettext( 'each Pay Period' ),
						20 => TTi18n::gettext( 'Annually' ),
						25 => TTi18n::gettext( 'Quarterly' ),
						30 => TTi18n::gettext( 'Monthly' ),
						40 => TTi18n::gettext( 'Weekly' ),
				];
				break;
			case 'eligible_period':
				$retval = [
						0 => TTi18n::gettext( '-- Always Eligible --' ),
						10 => TTi18n::gettext( 'each Pay Period' ),
						//20 => TTi18n::gettext( 'Annually' ),
						//25 => TTi18n::gettext( 'Quarterly' ),
						//30 => TTi18n::gettext( 'Monthly' ),
						40 => TTi18n::gettext( 'Weekly' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-type'        => TTi18n::gettext( 'Type' ),
						'-1030-name'        => TTi18n::gettext( 'Name' ),
						'-1035-description' => TTi18n::gettext( 'Description' ),

						'-1900-in_use' => TTi18n::gettext( 'In Use' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'type',
						'name',
						'description',
						'updated_date',
						'updated_by',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
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
				'type_id'                                           => 'Type',
				'type'                                              => false,
				'accrual_policy_account_id'                         => 'AccrualPolicyAccount',
				'accrual_policy_account'                            => false,
				'contributing_shift_policy_id'                      => 'ContributingShiftPolicy',
				'contributing_shift_policy'                         => false,
				'length_of_service_contributing_pay_code_policy_id' => 'LengthOfServiceContributingPayCodePolicy',
				'length_of_service_contributing_pay_code_policy'    => false,
				'name'                                              => 'Name',
				'description'                                       => 'Description',
				'enable_pay_stub_balance_display'                   => 'EnablePayStubBalanceDisplay',
				'minimum_time'                                      => 'MinimumTime',
				'maximum_time'                                      => 'MaximumTime',
				'apply_frequency'                                   => 'ApplyFrequency',
				'apply_frequency_id'                                => 'ApplyFrequency', //Must go after apply_frequency, so its set last.
				'apply_frequency_month'                             => 'ApplyFrequencyMonth',
				'apply_frequency_day_of_month'                      => 'ApplyFrequencyDayOfMonth',
				'apply_frequency_day_of_week'                       => 'ApplyFrequencyDayOfWeek',
				'apply_frequency_quarter_month'                     => 'ApplyFrequencyQuarterMonth',
				'apply_frequency_hire_date'                         => 'ApplyFrequencyHireDate',
				'enable_opening_balance'                            => 'EnableOpeningBalance',
				'enable_pro_rate_initial_period'                    => 'EnableProRateInitialPeriod',
				'milestone_rollover_hire_date'                      => 'MilestoneRolloverHireDate',
				'milestone_rollover_month'                          => 'MilestoneRolloverMonth',
				'milestone_rollover_day_of_month'                   => 'MilestoneRolloverDayOfMonth',
				'excess_rollover_accrual_policy_account_id'         => 'ExcessRolloverAccrualPolicyAccount',
				'excess_rollover_accrual_policy_account'            => false,
				'minimum_employed_days'                             => 'MinimumEmployedDays',
				'eligible_period_id'                                => 'EligiblePeriod',
				'eligible_contributing_shift_policy_id'             => 'EligibleContributingShiftPolicy',
				'minimum_eligible_time'                             => 'MinimumEligibleTime',
				'minimum_eligible_apply_retroactive'                => 'MinimumEligibleApplyRetroactive',
				'maximum_eligible_time'                             => 'MaximumEligibleTime',
				'in_use'                                            => false,
				'deleted'                                           => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getCompanyObject() {
		return $this->getGenericObject( 'CompanyListFactory', $this->getCompany(), 'company_obj' );
	}

	/**
	 * @return bool
	 */
	function getContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getContributingShiftPolicy(), 'contributing_shift_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getEligibleContributingShiftPolicyObject() {
		return $this->getGenericObject( 'ContributingShiftPolicyListFactory', $this->getEligibleContributingShiftPolicy(), 'eligible_contributing_shift_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getLengthOfServiceContributingPayCodePolicyObject() {
		return $this->getGenericObject( 'ContributingPayCodePolicyListFactory', $this->getLengthOfServiceContributingPayCodePolicy(), 'length_of_service_contributing_pay_code_policy_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getCompany() {
		return $this->getGenericDataValue( 'company_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );

		Debug::Text( 'Company ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|int
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
	 * @return bool|mixed
	 */
	function getAccrualPolicyAccount() {
		return $this->getGenericDataValue( 'accrual_policy_account_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setAccrualPolicyAccount( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'accrual_policy_account_id', $value );
	}

	/**
	 * This is the contributing shifts used for Hour Based accrual policies.
	 * @return bool|mixed
	 */
	function getContributingShiftPolicy() {
		return $this->getGenericDataValue( 'contributing_shift_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setContributingShiftPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'contributing_shift_policy_id', $value );
	}

	/**
	 * This is strictly used to determine milestones with active after X hours.
	 * @return bool|mixed
	 */
	function getLengthOfServiceContributingPayCodePolicy() {
		return $this->getGenericDataValue( 'length_of_service_contributing_pay_code_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setLengthOfServiceContributingPayCodePolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'length_of_service_contributing_pay_code_policy_id', $value );
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

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . ' where company_id = ? AND lower(name) = ? AND deleted=0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $id === false ) {
			return true;
		} else {
			if ( $id == $this->getId() ) {
				return true;
			}
		}

		return false;
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
	 * @return bool
	 */
	function getEnablePayStubBalanceDisplay() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_pay_stub_balance_display' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnablePayStubBalanceDisplay( $value ) {
		return $this->setGenericDataValue( 'enable_pay_stub_balance_display', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumTime() {
		return (int)$this->getGenericDataValue( 'minimum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumTime( $value ) {
		$value = trim( $value );

		if ( empty( $value ) ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'minimum_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMaximumTime() {
		return (int)$this->getGenericDataValue( 'maximum_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumTime( $value ) {
		$value = trim( $value );

		if ( empty( $value ) ) {
			$value = 0;
		}

		return $this->setGenericDataValue( 'maximum_time', $value );
	}

	//
	// Calendar
	//

	/**
	 * @return bool|int
	 */
	function getApplyFrequency() {
		return $this->getGenericDataValue( 'apply_frequency_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setApplyFrequency( $value ) {
		$value = (int)trim( $value );

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
	 * @return bool
	 */
	function getApplyFrequencyHireDate() {
		return $this->fromBool( $this->getGenericDataValue( 'apply_frequency_hire_date' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setApplyFrequencyHireDate( $value ) {
		return $this->setGenericDataValue( 'apply_frequency_hire_date', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableProRateInitialPeriod() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_pro_rate_initial_period' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableProRateInitialPeriod( $value ) {
		return $this->setGenericDataValue( 'enable_pro_rate_initial_period', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableOpeningBalance() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_opening_balance' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableOpeningBalance( $value ) {
		return $this->setGenericDataValue( 'enable_opening_balance', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getMilestoneRolloverHireDate() {
		return $this->fromBool( $this->getGenericDataValue( 'milestone_rollover_hire_date' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMilestoneRolloverHireDate( $value ) {
		return $this->setGenericDataValue( 'milestone_rollover_hire_date', $this->toBool( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getMilestoneRolloverMonth() {
		return $this->getGenericDataValue( 'milestone_rollover_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMilestoneRolloverMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'milestone_rollover_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getMilestoneRolloverDayOfMonth() {
		return $this->getGenericDataValue( 'milestone_rollover_day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMilestoneRolloverDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'milestone_rollover_day_of_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getExcessRolloverAccrualPolicyAccount() {
		return $this->getGenericDataValue( 'excess_rollover_accrual_policy_account_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setExcessRolloverAccrualPolicyAccount( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'excess_rollover_accrual_policy_account_id', $value );
	}


	/**
	 * @return bool|int
	 */
	function getMinimumEmployedDays() {
		return $this->getGenericDataValue( 'minimum_employed_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumEmployedDays( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'minimum_employed_days', (int)$this->Validator->stripNon32bitInteger( $value ) );
	}

	function getMinimumEmployedDaysAdjustedHireDate( $hire_date ) {
		if ( $this->is_catch_up_mode == true ) {
			return TTDate::getMiddleDayEpoch( $hire_date );
		} else {
			return TTDate::getMiddleDayEpoch( ( TTDate::getMiddleDayEpoch( $hire_date ) + ( $this->getMinimumEmployedDays() * 86400 ) ) );
		}
	}




	//Eligibility
	/**
	 * @return bool|int
	 */
	function getEligiblePeriod() {
		return $this->getGenericDataValue( 'eligible_period_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEligiblePeriod( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'eligible_period_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMinimumEligibleTime() {
		return $this->getGenericDataValue( 'minimum_eligible_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumEligibleTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'minimum_eligible_time', (int)$this->Validator->stripNon32bitInteger( $value ) );
	}

	/**
	 * @return bool
	 */
	function getMinimumEligibleApplyRetroactive() {
		return $this->fromBool( $this->getGenericDataValue( 'minimum_eligible_apply_retroactive' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumEligibleApplyRetroactive( $value ) {
		return $this->setGenericDataValue( 'minimum_eligible_apply_retroactive', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getMaximumEligibleTime() {
		return $this->getGenericDataValue( 'maximum_eligible_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumEligibleTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'maximum_eligible_time', (int)$this->Validator->stripNon32bitInteger( $value ) );
	}

	/**
	 * This is the contributing shifts used for Hour Based accrual policies.
	 * @return bool|mixed
	 */
	function getEligibleContributingShiftPolicy() {
		return $this->getGenericDataValue( 'eligible_contributing_shift_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setEligibleContributingShiftPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'eligible_contributing_shift_policy_id', $value );
	}

	/**
	 * @param object $u_obj
	 * @param object $modifier_obj
	 * @return bool
	 */
	function getModifiedHireDate( $u_obj, $modifier_obj = null ) {
		if ( !is_object( $u_obj ) ) {
			return false;
		}

		if ( !is_object( $modifier_obj ) ) {
			$modifier_obj = $this->getAccrualPolicyUserModifierObject( $u_obj );
		}

		if ( is_object( $modifier_obj ) && method_exists( $modifier_obj, 'getLengthOfServiceDate' ) && $modifier_obj->getLengthOfServiceDate() != '' ) {
			$user_hire_date = $modifier_obj->getLengthOfServiceDate();
			//Debug::Text('Using Modifier LengthOfService Date: '. TTDate::getDate('DATE+TIME', $user_hire_date ) .' Hire Date: '. TTDate::getDate('DATE+TIME', $u_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10);
		} else {
			$user_hire_date = $u_obj->getHireDate();
			//Debug::Text('Hire Date: '. TTDate::getDate('DATE+TIME', $user_hire_date ), __FILE__, __LINE__, __METHOD__, 10);
		}

		return $user_hire_date;
	}

	/**
	 * @param object $u_obj
	 * @param object $modifier_obj
	 * @return bool|false|int
	 */
	function getMilestoneRolloverDate( $u_obj = null, $modifier_obj = null ) {
		if ( !is_object( $u_obj ) ) {
			return false;
		}

		$user_hire_date = $this->getModifiedHireDate( $u_obj, $modifier_obj );

		if ( $this->getMilestoneRolloverHireDate() == true ) {
			$retval = $user_hire_date;
		} else {
			$user_hire_date_arr = getdate( $user_hire_date );
			$retval = mktime( $user_hire_date_arr['hours'], $user_hire_date_arr['minutes'], $user_hire_date_arr['seconds'], $this->getMilestoneRolloverMonth(), $this->getMilestoneRolloverDayOfMonth(), $user_hire_date_arr['year'] );
		}

		Debug::Text( 'Milestone Rollover Date: ' . TTDate::getDate( 'DATE+TIME', $retval ) . ' Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_hire_date ), __FILE__, __LINE__, __METHOD__, 10 );

		return TTDate::getBeginDayEpoch( $retval ); //Some hire dates might be at noon, so make sure they are all at midnight.
	}

	/**
	 * @param int $epoch EPOCH
	 * @param object $u_obj
	 * @param bool $use_previous_year_date
	 * @return bool|false|int
	 */
	function getCurrentMilestoneRolloverDate( $epoch, $u_obj = null, $use_previous_year_date = false ) {
		if ( !is_object( $u_obj ) ) {
			return false;
		}

		$user_hire_date = $this->getModifiedHireDate( $u_obj );

		$base_rollover_date = $this->getMilestoneRolloverDate( $u_obj );
		$rollover_date = mktime( 0, 0, 0, TTDate::getMonth( $base_rollover_date ), TTDate::getDayOfMonth( $base_rollover_date ), TTDate::getYear( $epoch ) );

		if ( $rollover_date < $user_hire_date ) {
			$rollover_date = $user_hire_date;
		}

		//If milestone rollover date comes after the current epoch, back date it by one year.
		if ( $use_previous_year_date == true && $rollover_date > $epoch ) {
			$rollover_date = mktime( 0, 0, 0, TTDate::getMonth( $rollover_date ), TTDate::getDayOfMonth( $rollover_date ), ( TTDate::getYear( $epoch ) - 1 ) );
		}

		Debug::Text( 'Current Milestone Rollover Date: ' . TTDate::getDate( 'DATE+TIME', $rollover_date ) . ' Hire Date: ' . TTDate::getDate( 'DATE+TIME', $user_hire_date ), __FILE__, __LINE__, __METHOD__, 10 );

		return $rollover_date;
	}

	/**
	 * @param $accrual_rate
	 * @param null $annual_pay_periods
	 * @return bool|float|string
	 */
	function getAccrualRatePerTimeFrequency( $accrual_rate, $annual_pay_periods = null ) {
		$retval = false;
		switch ( $this->getApplyFrequency() ) {
			case 10: //Pay Period
				if ( empty( $annual_pay_periods ) ) {
					return false;
				}
				$retval = TTMath::div( $accrual_rate, $annual_pay_periods, 0 );
				break;
			case 20: //Year
				$retval = $accrual_rate;
				break;
			case 25: //Quarter
				$retval = TTMath::div( $accrual_rate, 4, 0 );
				break;
			case 30: //Month
				$retval = TTMath::div( $accrual_rate, 12, 0 );
				break;
			case 40: //Week
				$retval = TTMath::div( $accrual_rate, 52, 0 );
				break;
		}

		//Round to nearest minute, or 15mins?
		//Well, if they accrue 99hrs/year on a weekly basis, rounding to the nearest minute means 98.8hrs/year...
		//Should round to the nearest second instead then.
		//$retval = TTDate::roundTime( $retval, 60, 20 );
		$retval = round( $retval, 0 );

		Debug::Text( 'Accrual Rate Per Frequency: ' . $retval . ' Accrual Rate: ' . $accrual_rate . ' Pay Periods: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @param int $current_epoch         EPOCH
	 * @param $offset
	 * @param object $u_obj
	 * @param int $pay_period_start_date EPOCH
	 * @return bool
	 */
	function inRolloverFrequencyWindow( $current_epoch, $offset, $u_obj, $pay_period_start_date = null ) {
		//Use current_epoch mainly for Yearly cases where the rollover date is 01-Nov and the hire date is always right after it, 10-Nov in the next year.
		$rollover_date = $this->getCurrentMilestoneRolloverDate( $current_epoch, $u_obj, false );
		Debug::Text( 'Rollover Date: ' . TTDate::getDate( 'DATE+TIME', $rollover_date ) . ' Current Epoch: ' . TTDate::getDate( 'DATE+TIME', $current_epoch ) . ' Hire Date: ' . TTDate::getDate( 'DATE+TIME', $u_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $rollover_date >= ( $current_epoch - $offset ) && $rollover_date <= $current_epoch ) {
			//Don't consider the employees (first) hire date to be in the rollover frequency window
			// This should avoid cases where the employee is hired, accrues time on their hire date by working and being assigned to a hour-based accrual policy
			// then the rollover occurs on their hire date and zeros out that time.
			// We still need to calculate other accruals on hire dates though, just not rollover.
			if ( TTDate::getBeginDayEpoch( $rollover_date ) > TTDate::getBeginDayEpoch( $u_obj->getHireDate() ) ) {
				Debug::Text( 'In rollover frequency window...', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			} else {
				Debug::Text( 'In rollover frequency window, but on user first hire date, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		Debug::Text( 'NOT in rollover frequency window...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param int $current_epoch    EPOCH
	 * @param $offset
	 * @param array $pay_period_dates EPOCH
	 * @param object $u_obj
	 * @return array|bool
	 */
	function getApplyFrequencyWindowDates( $current_epoch, $offset, $pay_period_dates = null, $u_obj = null ) {
		$hire_date = $this->getMilestoneRolloverDate( $u_obj );

		$retval = false;
		switch ( $this->getApplyFrequency() ) {
			case 10: //Pay Period
				if ( isset( $pay_period_dates['start_date'] ) && $pay_period_dates['end_date'] ) {
					$retval = [ 'start_date' => $pay_period_dates['start_date'], 'end_date' => $pay_period_dates['end_date'] ];
				}
				break;
			case 20: //Year
				if ( $this->getApplyFrequencyHireDate() == true ) {
					Debug::Text( 'Hire Date: ' . TTDate::getDate( 'DATE', $hire_date ), __FILE__, __LINE__, __METHOD__, 10 );
					$year_epoch = mktime( 0, 0, 0, TTDate::getMonth( $hire_date ), TTDate::getDayOfMonth( $hire_date ), TTDate::getYear( $current_epoch ) );
				} else {
					Debug::Text( 'Static Date', __FILE__, __LINE__, __METHOD__, 10 );
					$year_epoch = mktime( 0, 0, 0, $this->getApplyFrequencyMonth(), $this->getApplyFrequencyDayOfMonth(), TTDate::getYear( $current_epoch ) );
					if ( TTDate::getMiddleDayEpoch( $year_epoch ) < $this->getMinimumEmployedDaysAdjustedHireDate( $u_obj->getHireDate() ) ) {
						$year_epoch = strtotime( '+1 year', $year_epoch );
					}
				}
				Debug::Text( 'Year EPOCH: ' . TTDate::getDate( 'DATE+TIME', $year_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

				$retval = [ 'start_date' => strtotime( '-1 year', $year_epoch ), 'end_date' => $year_epoch ];
				break;
			case 25: //Quarter
				$apply_frequency_day_of_month = $this->getApplyFrequencyDayOfMonth();

				//Make sure if they specify the day of month to be 31, that is still works for months with 30, or 28-29 days, assuming 31 basically means the last day of the month
				if ( $apply_frequency_day_of_month > TTDate::getDaysInMonth( $current_epoch ) ) {
					$apply_frequency_day_of_month = TTDate::getDaysInMonth( $current_epoch );
					Debug::Text( 'Apply frequency day of month exceeds days in this month, using last day of the month instead: ' . $apply_frequency_day_of_month, __FILE__, __LINE__, __METHOD__, 10 );
				}

				$tmp_epoch = TTDate::getBeginDayEpoch( $current_epoch - $offset );
				$month_offset = ( $this->getApplyFrequencyQuarterMonth() - 1 );
				$year_quarters = array_reverse( TTDate::getYearQuarters( $tmp_epoch, null, $apply_frequency_day_of_month ), true );
				foreach ( $year_quarters as $quarter => $quarter_dates ) {
					$tmp_quarter_end_date = ( TTDate::getEndDayEpoch( $quarter_dates['end'] ) + 1 );
					//Debug::Text('Quarter: '. $quarter .' Month Offset: '. $month_offset .' Start: '. TTDate::getDate('DATE+TIME', $quarter_dates['start']) .' End: '. TTDate::getDate('DATE+TIME', $tmp_quarter_end_date), __FILE__, __LINE__, __METHOD__, 10);
					if ( $tmp_epoch >= $quarter_dates['start'] && $tmp_epoch <= $tmp_quarter_end_date ) {
						$start_date_month_epoch = mktime( 0, 0, 0, ( TTDate::getMonth( $quarter_dates['start'] ) - $month_offset ), 1, TTDate::getYear( $quarter_dates['start'] ) );
						$end_date_month_epoch = mktime( 0, 0, 0, ( TTDate::getMonth( $tmp_quarter_end_date ) - $month_offset ), 1, TTDate::getYear( $tmp_quarter_end_date ) );

						$retval = [
								'start_date' => mktime( 0, 0, 0, ( TTDate::getMonth( $start_date_month_epoch ) ), ( $this->getApplyFrequencyDayOfMonth() > TTDate::getDaysInMonth( $start_date_month_epoch ) ) ? TTDate::getDaysInMonth( $start_date_month_epoch ) : $this->getApplyFrequencyDayOfMonth(), TTDate::getYear( $start_date_month_epoch ) ),
								'end_date'   => mktime( 0, 0, 0, ( TTDate::getMonth( $end_date_month_epoch ) ), ( $this->getApplyFrequencyDayOfMonth() > TTDate::getDaysInMonth( $end_date_month_epoch ) ) ? TTDate::getDaysInMonth( $end_date_month_epoch ) : $this->getApplyFrequencyDayOfMonth(), TTDate::getYear( $end_date_month_epoch ) ),
						];
						unset( $start_date_month_epoch, $end_date_month_epoch );
						break;
					}
				}
				break;
			case 30: //Month
				$apply_frequency_day_of_month = $this->getApplyFrequencyDayOfMonth();

				//Make sure if they specify the day of month to be 31, that is still works for months with 30, or 28-29 days, assuming 31 basically means the last day of the month
				if ( $apply_frequency_day_of_month > TTDate::getDaysInMonth( $current_epoch ) ) {
					$apply_frequency_day_of_month = TTDate::getDaysInMonth( $current_epoch );
					Debug::Text( 'Apply frequency day of month exceeds days in this month, using last day of the month instead: ' . $apply_frequency_day_of_month, __FILE__, __LINE__, __METHOD__, 10 );
				}

				$month_epoch = mktime( 0, 0, 0, TTDate::getMonth( $current_epoch ), $apply_frequency_day_of_month, TTDate::getYear( $current_epoch ) );
				if ( TTDate::getMiddleDayEpoch( $month_epoch ) < $this->getMinimumEmployedDaysAdjustedHireDate( $u_obj->getHireDate() ) ) {
					$month_epoch = strtotime( '+1 month', $month_epoch );
				}

				Debug::Text( 'Month EPOCH: ' . TTDate::getDate( 'DATE+TIME', $month_epoch ) . '(' . $month_epoch . ')', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = [ 'start_date' => strtotime( '-1 month', $month_epoch ), 'end_date' => $month_epoch ];
				break;
			case 40: //Week
				$week_epoch = strtotime( 'this ' . TTDate::getDayOfWeekByInt( $this->getApplyFrequencyDayOfWeek() ), ( $current_epoch ) );
				Debug::Text( 'Current Day Of Week: ' . TTDate::getDayOfWeekByInt( TTDate::getDayOfWeek( $current_epoch ) ) . ' Accrual Day of Week: ' . TTDate::getDayOfWeekByInt( $this->getApplyFrequencyDayOfWeek() ), __FILE__, __LINE__, __METHOD__, 10 );
				$retval = [ 'start_date' => strtotime( '-1 week', $week_epoch ), 'end_date' => $week_epoch ];
				break;
		}

		if ( is_array( $retval ) ) {
			Debug::Text( 'Epoch: ' . TTDate::getDate( 'DATE+TIME', $current_epoch ) . ' Window Start Date: ' . TTDate::getDate( 'DATE+TIME', $retval['start_date'] ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $retval['end_date'] ) . ' Offset: ' . $offset, __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			Debug::Text( 'Start Date: FALSE End Date: FALSE Offset: ' . $offset, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param $input_amount
	 * @param int $current_epoch    EPOCH
	 * @param $offset
	 * @param int $pay_period_dates EPOCH
	 * @param object $u_obj
	 * @return float
	 */
	function getProRateInitialFrequencyWindow( $input_amount, $current_epoch, $offset, $pay_period_dates = null, $u_obj = null ) {
		$apply_frequency_dates = $this->getApplyFrequencyWindowDates( $current_epoch, $offset, $pay_period_dates, $u_obj );
		if ( isset( $apply_frequency_dates['start_date'] ) && isset( $apply_frequency_dates['end_date'] ) ) {
			Debug::Text( 'ProRate Based On: Start Date: ' . TTDate::getDate( 'DATE+TIME', $apply_frequency_dates['start_date'] ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $apply_frequency_dates['end_date'] ) . ' Hire Date: ' . TTDate::getDate( 'DATE+TIME', $this->getMinimumEmployedDaysAdjustedHireDate( $u_obj->getHireDate() ) ), __FILE__, __LINE__, __METHOD__, 10 );
			$pro_rate_multiplier = ( ( TTDate::getMiddleDayEpoch( $apply_frequency_dates['end_date'] ) - $this->getMinimumEmployedDaysAdjustedHireDate( $u_obj->getHireDate() ) ) / max( ( TTDate::getMiddleDayEpoch( $apply_frequency_dates['end_date'] ) - TTDate::getMiddleDayEpoch( $apply_frequency_dates['start_date'] ) ), 1 ) ); //Use max() to avoid divide by zero error.
			if ( $pro_rate_multiplier <= 0 || $pro_rate_multiplier > 1 ) {
				$pro_rate_multiplier = 1;
			}
			$amount = round( $input_amount * $pro_rate_multiplier ); //Round to nearest second.
			Debug::Text( 'ProRated Amount: ' . $amount . ' ProRate Multiplier: ' . $pro_rate_multiplier . ' Input Amount: ' . $input_amount, __FILE__, __LINE__, __METHOD__, 10 );

			return $amount;
		}

		return $input_amount;
	}

	/**
	 * @param int $current_epoch    EPOCH
	 * @param $offset
	 * @param int $pay_period_dates EPOCH
	 * @param object $u_obj
	 * @return bool
	 */
	function isInitialApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates = null, $u_obj = null ) {
		$apply_frequency_dates = $this->getApplyFrequencyWindowDates( $current_epoch, $offset, $pay_period_dates, $u_obj );
		if ( isset( $apply_frequency_dates['start_date'] ) && isset( $apply_frequency_dates['end_date'] ) ) {
			$minimum_employed_days_hire_date = $this->getMinimumEmployedDaysAdjustedHireDate( $u_obj->getHireDate() );
			if ( is_object( $u_obj ) && $minimum_employed_days_hire_date >= TTDate::getMiddleDayEpoch( $apply_frequency_dates['start_date'] ) && $minimum_employed_days_hire_date <= TTDate::getMiddleDayEpoch( $apply_frequency_dates['end_date'] ) ) {
				Debug::Text( 'Initial apply frequency window...', __FILE__, __LINE__, __METHOD__, 10 );

				return true;
			}
		}

		Debug::Text( 'Not initial apply frequency window...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param int $current_epoch    EPOCH
	 * @param $offset
	 * @param int $pay_period_dates EPOCH
	 * @param object $u_obj
	 * @return bool
	 */
	function inApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates = null, $u_obj = null ) {
		$apply_frequency_dates = $this->getApplyFrequencyWindowDates( $current_epoch, $offset, $pay_period_dates, $u_obj );
		if ( isset( $apply_frequency_dates['start_date'] ) && isset( $apply_frequency_dates['end_date'] ) ) {
			//Pay Period frequencies end on 31-Dec @ 11:59:59, but all other frequencies end on 12:00AM.
			//  The effective date of the accrual record has always been inserted on the day after the pay period end date though, so keep that consistent.
			//  **Keep in mind maintenance jobs run today, with a $current_epoch of yesterday, so its always a day delayed and depending on the system timezone (ie: EST) and user timezone (ie: PST) it could be two days delayed.
			$apply_frequency_date = TTDate::getBeginDayEpoch( ( $apply_frequency_dates['end_date'] + 7200 ) );
			Debug::Text( 'Epoch: ' . TTDate::getDate( 'DATE+TIME', $current_epoch ) . ' Adjusted Apply Frequency Date: ' . TTDate::getDate( 'DATE+TIME', $apply_frequency_date ) . ' Original: ' . TTDate::getDate( 'DATE+TIME', $apply_frequency_dates['end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );

			if ( TTDate::getMiddleDayEpoch( $current_epoch ) == TTDate::getMiddleDayEpoch( $apply_frequency_date ) ) {
				//Make sure that if enable opening balance is FALSE, we never apply on the hire date.
				//  What if the frequency is monthly on the 1st and the hire date is also the 1st (employee record was created in advance, hire date post dated to the 1st?)
				//  However to make this work we need to ensure that if the employee record is created on the 1st with the 1st being the hire date, it still accrues when the maintenance jobs run the next day.
				//I think we should accrue on all frequency dates as long as the criteria (ie: minimum employed days) is met.
				//  If they don't want to accrue on the hire date in this case they could just set the minimum employed days to 1.
				//  Unfortunately in the opposite case, where they want to accure on the hire date if its a normal frequency date, there is no work-around.
				//  See #2334
				Debug::Text( '    In Apply Frequency...', __FILE__, __LINE__, __METHOD__, 10 );

				return true;

//				if ( $this->getEnableOpeningBalance() == FALSE
//					AND ( is_object($u_obj) AND TTDate::getMiddleDayEpoch( $u_obj->getHireDate() ) == TTDate::getMiddleDayEpoch( $current_epoch ) )
//					AND $this->isInitialApplyFrequencyWindow( $current_epoch, $offset, $pay_period_dates, $u_obj ) == TRUE ) {
//					return FALSE;
//				} else {
//					return TRUE;
//				}
			}
		}

		Debug::Text( '    NOT In Apply Frequency...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @return bool|int
	 */
	function getEligibleTimeByUserIdAndEndDate( $user_id, $start_date = null, $end_date = null ) {
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

		if ( is_object( $this->getEligibleContributingShiftPolicyObject() ) ) {
			$pay_code_policy_obj = $this->getEligibleContributingShiftPolicyObject()->getContributingPayCodePolicyObject();
			if ( is_object( $pay_code_policy_obj ) ) {
				$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
				$retval = $udtlf->getTotalTimeSumByUserIDAndPayCodeIDAndStartDateAndEndDate( $user_id, $pay_code_policy_obj->getPayCode(), $start_date, $end_date );
			}
		}

		Debug::Text( 'Eligible Seconds: ' . (int)$retval . ' Start: '. TTDate::getDate( 'DATE+TIME', $start_date ) .' End: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
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
	 * Determine if any milestones have an hour based length of service.
	 * @return bool
	 */
	function isHourBasedLengthOfService() {
		//Cache milestones to speed up getting projected balances.
		if ( !isset( $this->milestone_objs[$this->getID()] ) ) {
			$this->milestone_objs[$this->getID()] = TTnew( 'AccrualPolicyMilestoneListFactory' );
			$this->milestone_objs[$this->getID()]->getByAccrualPolicyId( $this->getId(), null, [ 'length_of_service_days' => 'desc' ] );
		}
		Debug::Text( '  Total Accrual Policy Milestones: ' . (int)$this->milestone_objs[$this->getID()]->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->milestone_objs[$this->getID()]->getRecordCount() > 0 ) {
			foreach ( $this->milestone_objs[$this->getID()] as $apm_obj ) {
				if ( $apm_obj->getLengthOfServiceUnit() == 50 && $apm_obj->getLengthOfService() > 0 ) {
					Debug::Text( '  Milestone is in Hours...', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		}

		Debug::Text( '  No HourBased length of service Milestones...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param object $u_obj
	 * @return bool|null
	 */
	function getAccrualPolicyUserModifierObject( $u_obj ) {
		if ( !is_object( $u_obj ) ) {
			return false;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			if ( isset( $this->user_modifier_obj ) && is_object( $this->user_modifier_obj ) && $this->user_modifier_obj->getUser() == $u_obj->getID() && $this->user_modifier_obj->getAccrualPolicy() == $this->getID() ) {
				return $this->user_modifier_obj;
			} else {
				$apumlf = TTNew( 'AccrualPolicyUserModifierListFactory' ); /** @var AccrualPolicyUserModifierListFactory $apumlf */
				$apumlf->getByUserIdAndAccrualPolicyId( $u_obj->getId(), $this->getId() );
				if ( $apumlf->getRecordCount() == 1 ) {
					$this->user_modifier_obj = $apumlf->getCurrent();
					Debug::Text( '  Found Accrual Policy User Modifier: Length of Service: ' . TTDate::getDate( 'DATE+TIME', $this->user_modifier_obj->getLengthOfServiceDate() ) . ' Accrual Rate: ' . $this->user_modifier_obj->getAccrualRateModifier(), __FILE__, __LINE__, __METHOD__, 10 );

					return $this->user_modifier_obj;
				}
			}
		}

		return false;
	}

	/**
	 * @param object $u_obj
	 * @param int $epoch EPOCH
	 * @param int $worked_time
	 * @param bool $modifier_obj
	 * @return object|bool
	 */
	function getActiveMilestoneObject( $u_obj, $epoch = null, $worked_time = 0, $modifier_obj = false ) {
		if ( !is_object( $u_obj ) ) {
			return false;
		}

		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		$milestone_obj = false;

		if ( !is_object( $modifier_obj ) ) {
			$modifier_obj = $this->getAccrualPolicyUserModifierObject( $u_obj ); /** @var AccrualPolicyUserModifierFactory $modifier_obj */
		}

		//Cache milestones to speed up getting projected balances.
		if ( !isset( $this->milestone_objs[$this->getID()] ) ) {
			$this->milestone_objs[$this->getID()] = TTnew( 'AccrualPolicyMilestoneListFactory' );
			$this->milestone_objs[$this->getID()]->getByAccrualPolicyId( $this->getId(), null, [ 'length_of_service_days' => 'desc', 'length_of_service_unit_id' => 'desc', 'length_of_service' => 'desc', 'id' => 'desc' ] ); //In case length of service days happens to be the same for two milestones (ie: 6.00 and 6.01 months), they both convert to 182 days and therefore order is lost. So sort by raw length_of_service as well just in case.
		}
		$total_milestones = $this->milestone_objs[$this->getID()]->getRecordCount();

		Debug::Text( '  Total Accrual Policy MileStones: ' . (int)$total_milestones, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $total_milestones > 0 ) {
			$worked_time = null;
			$milestone_rollover_date = null;

			$i = 1;
			foreach ( $this->milestone_objs[$this->getID()] as $apm_obj ) { /** @var AccrualPolicyMilestoneFactory $apm_obj */
				if ( is_object( $modifier_obj ) ) {
					$apm_obj = $modifier_obj->getAccrualPolicyMilestoneObjectAfterModifier( $apm_obj );
					if ( $apm_obj->getLengthOfService() === false ) { //Make sure we allow a 0 accrual rate, as some customers just want to automatically zero out time banks on a certain date.
						Debug::Text( '  MileStone after modifier has no length of service, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
						$i++;
						continue;
					}
				}

				if ( $apm_obj->getLengthOfServiceUnit() == 50 && $apm_obj->getLengthOfService() > 0 ) {
					Debug::Text( '  MileStone is by Hours...', __FILE__, __LINE__, __METHOD__, 10 );
					//Hour based
					if ( $worked_time == null ) {
						//Get users worked time.
						$worked_time = TTDate::getHours( $this->getWorkedTimeByUserIdAndEndDate( $u_obj->getId(), $apm_obj->getLengthOfService(), $epoch ) );
						Debug::Text( '  Worked Time: ' . $worked_time . 'hrs', __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( $worked_time >= $apm_obj->getLengthOfService() ) {
						Debug::Text( '  bLength Of Service: ' . $apm_obj->getLengthOfService() . 'hrs', __FILE__, __LINE__, __METHOD__, 10 );
						$milestone_obj = $apm_obj;
						break;
					} else {
						Debug::Text( '  Skipping Milestone...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '  MileStone is by Calendar (days)...', __FILE__, __LINE__, __METHOD__, 10 );
					//Calendar based
					$milestone_rollover_date = $apm_obj->getLengthOfServiceDate( $this->getMilestoneRolloverDate( $u_obj, $modifier_obj ) );

					//When a milestone first rolls-over, the Maximum rollover won't apply in many cases as it uses the new milestone rollover
					//at that time which often has a higher rollover amount. This only happens the first time the milestone rolls-over.
					//We could avoid this by using just ">" comparison below, but then that affects annual accruals as it will take two years
					//to see the milestone rollover after one year, so that won't work either.
					//if ( $length_of_service_days >= $apm_obj->getLengthOfServiceDays() ) {
					if ( $epoch >= $milestone_rollover_date ) {
						$milestone_obj = $apm_obj;
						Debug::Text( '  Using MileStone due to Active After Days: ' . $apm_obj->getLengthOfServiceDays() . ' or Date: ' . TTDate::getDate( 'DATE+TIME', $milestone_rollover_date ), __FILE__, __LINE__, __METHOD__, 10 );
						break;
					} else if ( $i == $total_milestones && $apm_obj->getLengthOfService() == 0 ) {
						//If we are on the first milestone by length of service date (last milestone in this loop) and its length of service is 0, it should always apply.
						//  The case we need to handle is as follows:
						//    Milestone Rollover: July 1st 2022.
						//    Employee Hire Date: January 1st 2022
						//      However a hire date of Dec 31st 2021 would apply, because the milestone date would be July 1st 2021 and the employee is a year ahead technically.
						//    Apply Frequency every month on the 28th.
						//  Since the 1st milestone date would be July 1st 2022, thats *after* the hire date, so no milestones would apply at all unless this check is here.
						Debug::Text( '  Defaulting to first milestone active after 0 days length of service...', __FILE__, __LINE__, __METHOD__, 10 );
						$milestone_obj = $apm_obj;
						break;
					} else {
						Debug::Text( '  Skipping MileStone...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}

				$i++;
			}
		}
		unset( $apm_obj );

		return $milestone_obj;
	}

	/**
	 * @param string $user_id                   UUID
	 * @param string $accrual_policy_account_id UUID
	 * @return bool|int
	 */
	function getCurrentAccrualBalance( $user_id, $accrual_policy_account_id = null ) {
		if ( $user_id == '' ) {
			return false;
		}

		if ( $accrual_policy_account_id == '' ) {
			$accrual_policy_account_id = $this->getAccrualPolicyAccount();
		}

		//Check min/max times of accrual policy.
		$ablf = TTnew( 'AccrualBalanceListFactory' ); /** @var AccrualBalanceListFactory $ablf */
		$ablf->getByUserIdAndAccrualPolicyAccount( $user_id, $accrual_policy_account_id );
		if ( $ablf->getRecordCount() > 0 ) {
			$accrual_balance = $ablf->getCurrent()->getBalance();
		} else {
			$accrual_balance = 0;
		}

		Debug::Text( '  Current Accrual Balance: ' . $accrual_balance, __FILE__, __LINE__, __METHOD__, 10 );

		return $accrual_balance;
	}

	/**
	 * @param object $milestone_obj
	 * @param $total_time
	 * @param $annual_pay_periods
	 * @return bool|float|int|string
	 */
	function calcAccrualAmount( $milestone_obj, $total_time, $annual_pay_periods ) {
		if ( !is_object( $milestone_obj ) ) {
			return false;
		}

		$accrual_rate = number_format( $milestone_obj->getAccrualRate(), 10, '.', ''); //Convert to string in case its so low that its using scientific notation. -- Was using "bcscale()" instead of "10", however PHP <8.0 does not support passing 0 arguments.

		$accrual_amount = 0;
		if ( $this->getType() == 30 && $total_time > 0 ) {
			//Calculate the fixed amount based off the rate.
			$accrual_amount = TTMath::mul( $accrual_rate, $total_time, 4 );
		} else if ( $this->getType() == 20 ) {
			$accrual_amount = $this->getAccrualRatePerTimeFrequency( $accrual_rate, $annual_pay_periods );
		}
		Debug::Text( '  Accrual Amount: ' . $accrual_amount . ' Total Time: ' . $total_time . ' Rate: ' . $accrual_rate . ' Annual Pay Periods: ' . $annual_pay_periods, __FILE__, __LINE__, __METHOD__, 10 );

		return $accrual_amount;
	}

	/**
	 * Returns an array of pay period start/end dates between a given start/end date.
	 * @param object $pps_obj
	 * @param object $u_obj
	 * @param int $start_epoch EPOCH
	 * @param int $end_epoch   EPOCH
	 * @return array
	 */
	function getPayPeriodArray( $pps_obj, $u_obj, $start_epoch, $end_epoch ) {
		$retarr = [];

		$pp_end_date = $end_epoch;

		$pplf = TTNew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getByUserIdAndOverlapStartDateAndEndDate( $u_obj->getId(), $start_epoch, $end_epoch );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				$retarr[] = [ 'start_date' => $pp_obj->getStartDate(), 'end_date' => $pp_obj->getEndDate() ];
			}
			$pp_end_date = $pp_obj->getEndDate(); //Use end date from last iteration.
		}
		unset( $pplf, $pp_obj );

		Debug::Text( 'Last already created Pay Period End Date: ' . TTDate::getDate( 'DATE+TIME', $pp_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $pps_obj->getType() != 5 ) { //5=Manual. No point trying to project balances with a manual pay period schedule.
			//$end_epoch is in the future, so continue to try and find pay period schedule dates.
			if ( $pp_end_date <= $end_epoch ) {
				//$pps_obj->setPayPeriodTimeZone();
				$i = 0;
				while ( $pp_end_date > 0 && $pp_end_date < $end_epoch && $i < 106 ) { //106=Is two years worth of weekly pay periods.
					$pps_obj->getNextPayPeriod( $pp_end_date );
					$retarr[] = [ 'start_date' => $pps_obj->getNextStartDate(), 'end_date' => $pps_obj->getNextEndDate() ];
					$pp_end_date = $pps_obj->getNextEndDate();

					$i++;
				}
				//$pps_obj->setOriginalTimeZone();
			}
		}

		//Debug::Arr($retarr, 'Pay Period array between Start: '.  TTDate::getDate('DATE+TIME', $start_epoch ) .' End: '.  TTDate::getDate('DATE+TIME', $end_epoch ), __FILE__, __LINE__, __METHOD__, 10);

		return $retarr;
	}

	/**
	 * @param $pay_period_arr
	 * @param int $epoch EPOCH
	 * @return bool|mixed
	 */
	function getPayPeriodDatesFromArray( $pay_period_arr, $epoch ) {
		if ( is_array( $pay_period_arr ) ) {
			foreach ( $pay_period_arr as $pp_dates ) {
				if ( $epoch >= $pp_dates['start_date'] && $epoch <= $pp_dates['end_date'] ) {
					return $pp_dates;
				}
			}
		}

		return false;
	}

	/**
	 * $current_amount is the amount of time currently being entered.
	 * $previous_amount is the old amount that is currently be edited.
	 * @param object $u_obj
	 * @param int $epoch EPOCH
	 * @param $current_time
	 * @param int $previous_time
	 * @param bool $other_policy_balance_arr
	 * @return array
	 */
	function getAccrualBalanceWithProjection( $u_obj, $epoch, $current_time, $previous_time = 0, $other_policy_balance_arr = false ) {
		// Available Balance:			   10hrs
		// Current Time:					8hrs
		// Remaining Balance:				2hrs
		//
		// Projected Balance by 01-Jul-12: 15hrs
		// Projected Remaining Balance:		7hrs

		//Debug::Arr($other_policy_balance_arr, 'Current Time: '. TTDate::getHours( $current_time ) .' Previous Time: '. TTDate::getHours( $previous_time ) .' Other Policy Balance Arr: ', __FILE__, __LINE__, __METHOD__, 10);
		$current_balance = $this->getCurrentAccrualBalance( $u_obj->getID(), $this->getAccrualPolicyAccount() );

		//Now that multiple Accrual Policies can deposit to the same account, we need to loop through all accrual policies that affect
		//any given account and add the projected balances together.
		//  However getProjectedAccrualAmount() starts with the current balance, and accrues from there. So the current balance would be counted for every iteration of the outer loop calling this function.
		//     Therefore on all but the first iteration we need to remove the accrual current balance from the projected balance.
		$other_policy_projected_balance = 0;
		if ( is_array( $other_policy_balance_arr ) && isset( $other_policy_balance_arr['projected_balance'] ) ) {
			$other_policy_projected_balance = TTMath::sub( $other_policy_balance_arr['projected_balance'], $current_balance );
			Debug::Text( 'Other Policy Projected Balance: ' . TTDate::getHours( $other_policy_projected_balance ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		//Previous time is time already taken into account in the balance, so subtract it here (opposite of adding lower down in remaining balance)
		$available_balance = TTMath::sub( $current_balance, $previous_time );
		$projected_accrual = TTMath::add( ( $this->getProjectedAccrualAmount( $u_obj, time(), $epoch ) ), $other_policy_projected_balance );

		$retarr = [
				'available_balance'           => $available_balance,
				'current_time'                => $current_time,
				'remaining_balance'           => TTMath::add( $available_balance, $current_time ),
				'projected_balance'           => $projected_accrual,
				'projected_remaining_balance' => TTMath::add( $projected_accrual, TTMath::sub( $current_time, $previous_time ) ),
		];

		Debug::Arr( $retarr, 'Projected Accrual Arr: ', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( 'Remaining Balance: ' . TTDate::getHours( $retarr['remaining_balance'] ) . ' Projected Remaining Balance: ' . TTDate::getHours( $retarr['projected_remaining_balance'] ), __FILE__, __LINE__, __METHOD__, 10 );

		return $retarr;
	}

	/**
	 * @param object $u_obj
	 * @param int $start_epoch EPOCH
	 * @param int $end_epoch   EPOCH
	 * @return bool|float|int|string
	 */
	function getProjectedAccrualAmount( $u_obj, $start_epoch, $end_epoch ) {
		$start_epoch = TTDate::getMiddleDayEpoch( $start_epoch );
		$end_epoch = TTDate::getMiddleDayEpoch( $end_epoch );

		$offset = 79200;

		$accrual_amount = 0;

		Debug::Text( 'Start Date ' . TTDate::getDate( 'DATE+TIME', $start_epoch ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $end_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		$ppslf = TTNew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
		$ppslf->getByCompanyIdAndUserId( $u_obj->getCompany(), $u_obj->getId() );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$initial_accrual_balance = $this->getCurrentAccrualBalance( $u_obj->getID(), $this->getAccrualPolicyAccount() );

			$pay_period_arr = [];
			if ( $this->getApplyFrequency() == 10 ) {
				$pay_period_arr = $this->getPayPeriodArray( $pps_obj, $u_obj, $start_epoch, $end_epoch );
			}

			$accrual_amount = $initial_accrual_balance; //Make the first accrual_amount match the initial accrual balance.
			foreach ( TTDate::getDatePeriod( $start_epoch, $end_epoch, 'P1D' ) as $epoch ) {
				$epoch = ( TTDate::getBeginDayEpoch( $epoch ) + 7200 ); //This is required because the epoch has to be slightly AFTER the pay period end date, which is 11:59PM.

				//Make sure we pass the returned accrual_amount back into calcAccrualPolicyTime() as the new balance so rollover/maximum balances are all properly handled.
				$accrual_amount = TTMath::add( $accrual_amount, $this->calcAccrualPolicyTime( $u_obj, $epoch, $offset, $pps_obj, $pay_period_arr, $accrual_amount, false ) );
			}

			Debug::Text( 'Projected Accrual Amount: ' . TTDate::getHours( $accrual_amount ) .' Initial Balance: '. TTDate::getHours( $initial_accrual_balance ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $accrual_amount;
	}

	/**
	 * @param int $epoch
	 * @param object $pps_obj
	 * @param object|array $pay_period_arr
	 * @return array|bool|mixed
	 */
	function getEligibleDates( $epoch, $offset, $pps_obj, $pay_period_arr ) {
		$retarr = false;

		if ( $this->getEligiblePeriod() == 40 ) { //40=Weekly
			$retarr = [];
			$retarr['start_date'] = TTDate::getBeginWeekEpoch( $epoch, $pps_obj->getStartWeekDay() );
			$retarr['end_date'] = TTDate::getEndWeekEpoch( $epoch, $pps_obj->getStartWeekDay() );
		} else if ( $this->getEligiblePeriod() == 10 ) { //10=each Pay Period
			if ( is_object( $pay_period_arr ) ) { //Handle different if its a pay period object itself, rather than an array.
				$retarr = [];
				$retarr['start_date'] = $pay_period_arr->getStartDate();
				$retarr['end_date'] = $pay_period_arr->getEndDate();
			} else if ( is_array( $pay_period_arr ) && !empty( $pay_period_arr ) ) {
				$retarr = $this->getPayPeriodDatesFromArray( $pay_period_arr, ( $epoch - $offset ) );
			}
		}

		Debug::Text( '  Eligiblity Period: '. $this->getEligiblePeriod() .' Start Date: '. TTDate::getDate('DATE', $retarr['start_date'] ?? null ) .' End Date: '. TTDate::getDate('DATE', $retarr['end_date'] ?? null ), __FILE__, __LINE__, __METHOD__, 10 );
		return $retarr;
	}

	function isEligible( $u_obj, $epoch, $offset, $pps_obj, $pay_period_arr ) {
		if ( $this->getEligiblePeriod() == 0 ) { //Always eligible
			$retval = true;
		} else {
			//Get eligiblity time over the specified period.
			$eligible_dates = $this->getEligibleDates( $epoch, $offset, $pps_obj, $pay_period_arr );
			if ( is_array( $eligible_dates ) && isset( $eligible_dates['start_date'] ) && isset( $eligible_dates['end_date'] ) ) {
				$eligible_total_time = $this->getEligibleTimeByUserIdAndEndDate( $u_obj->getId(), $eligible_dates['start_date'], $eligible_dates['end_date'] );
				Debug::Text( '  Eligiblity Period: ' . $this->getEligiblePeriod() . ' Start Date: ' . TTDate::getDate( 'DATE', $eligible_dates['start_date'] ) . ' End Date: ' . TTDate::getDate( 'DATE', $eligible_dates['end_date'] ) . ' Eligible Time: ' . $eligible_total_time, __FILE__, __LINE__, __METHOD__, 10 );

				if ( ( $this->getMinimumEligibleTime() == 0 || $eligible_total_time >= $this->getMinimumEligibleTime() ) && ( $this->getMaximumEligibleTime() == 0 || $eligible_total_time <= $this->getMaximumEligibleTime() ) ) {
					$retval = true;
				} else {
					$retval = false;
				}
			} else {
				Debug::Text( '  Eligible Start/End Date not available, was pay period unable to be determined?', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = false;
			}
		}

		Debug::Text( '  Eligible: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10 );
		return $retval;
	}
	/**
	 * Calculate the accrual amount based on a given user/time.
	 * @param object $u_obj
	 * @param int $epoch EPOCH
	 * @param $offset
	 * @param object $pps_obj
	 * @param $pay_period_arr
	 * @param float $accrual_balance
	 * @param bool $update_records
	 * @return bool|float|int|string
	 */
	function calcAccrualPolicyTime( $u_obj, $epoch, $offset, $pps_obj, $pay_period_arr, $accrual_balance, $update_records = true ) {
		$retval = 0;

		Debug::Text( 'User: ' . $u_obj->getFullName() . ' Status: ' . $u_obj->getStatus() . ' Epoch: ' . TTDate::getDate( 'DATE+TIME', $epoch ) . ' Hire Date: ' . TTDate::getDate( 'DATE+TIME', $u_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		//Make sure only active employees accrue time *after* their hire date.
		//Will this negatively affect Employees who may be on leave?
		if ( $u_obj->getStatus() == 10
				&& TTDate::getMiddleDayEpoch( $epoch ) >= TTDate::getMiddleDayEpoch( $u_obj->getHireDate() )
				&& ( $this->getMinimumEmployedDays() == 0
						|| TTDate::getMiddleDayEpoch( $epoch ) >= $this->getMinimumEmployedDaysAdjustedHireDate( $u_obj->getHireDate() ) ) ) {
			Debug::Text( '  User is active and has been employed long enough.', __FILE__, __LINE__, __METHOD__, 10 );

			$annual_pay_periods = $pps_obj->getAnnualPayPeriods();
			$in_apply_frequency_window = false;
			$in_apply_rollover_window = false;
			$pay_period_start_date = null;
			$total_catch_up_accrual_amount = 0;
			$catch_up_note = null;

			if ( $this->getType() == 30 ) {
				Debug::Text( '  Accrual policy is hour based, real-time window.', __FILE__, __LINE__, __METHOD__, 10 );

				//Hour based, apply frequency is real-time.
				$in_apply_frequency_window = true;
			} else {
				$pay_period_dates = false;
				if ( $this->getApplyFrequency() == 10 ) {
					$pay_period_dates = $this->getPayPeriodDatesFromArray( $pay_period_arr, ( $epoch - $offset ) );
					if ( is_array( $pay_period_dates ) ) {
						Debug::Text( '   Pay Period Start Date: ' . TTDate::getDate( 'DATE+TIME', $pay_period_dates['start_date'] ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $pay_period_dates['end_date'] ), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $this->inApplyFrequencyWindow( $epoch, $offset, $pay_period_dates ) == true ) {
							$in_apply_frequency_window = true;

							$pay_period_start_date = $pay_period_dates['start_date']; //Used for inRolloverFrequencyWindow
						} else {
							Debug::Text( '  User not in Apply Frequency Window: ', __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Arr( $pay_period_dates, '   No Pay Period Dates Found.', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else if ( $this->inApplyFrequencyWindow( $epoch, $offset, null, $u_obj ) == true ) {
					Debug::Text( '  User IS in NON-PayPeriod Apply Frequency Window.', __FILE__, __LINE__, __METHOD__, 10 );
					$in_apply_frequency_window = true;
				} else {
					//Debug::Text('  User is not in Apply Frequency Window.', __FILE__, __LINE__, __METHOD__, 10);
					$in_apply_frequency_window = false;
				}

				if ( $this->getEnableOpeningBalance() == true
						&& $this->getMinimumEmployedDaysAdjustedHireDate( $u_obj->getHireDate() ) == TTDate::getMiddleDayEpoch( $epoch )
						&& $this->isInitialApplyFrequencyWindow( $epoch, $offset, $pay_period_dates, $u_obj ) == true ) {
					Debug::Text( '  Epoch is users hire date, and opening balances is enabled...', __FILE__, __LINE__, __METHOD__, 10 );
					$in_apply_frequency_window = true;

					if ( $this->is_catch_up_mode == false && $this->getMinimumEmployedDays() > 0 ) {
						$this->is_catch_up_mode = true;

						//Catch-up
						$catch_up_start_epoch = TTDate::getMiddleDayEpoch( $u_obj->getHireDate() ); //Start on day after the hire date, as that is when the accruals get recorded anyways.
						$catch_up_end_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $epoch ), -1, 'day' );

						$catch_up_pay_period_arr = [];
						if ( $this->getApplyFrequency() == 10 ) {
							$catch_up_pay_period_arr = $this->getPayPeriodArray( $pps_obj, $u_obj, $catch_up_start_epoch, $catch_up_end_epoch );
						}

						Debug::Text( '  Calculating Catch-Up Accruals for Start: ' . TTDate::getDate( 'DATE+TIME', $catch_up_start_epoch ) .' End: '. TTDate::getDate( 'DATE+TIME', $catch_up_end_epoch ), __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( TTDate::getDatePeriod( $catch_up_start_epoch, $catch_up_end_epoch, 'P1D' ) as $catch_up_epoch ) {
							$catch_up_epoch = ( TTDate::getBeginDayEpoch( $catch_up_epoch ) + 7200 ); //This is required because the epoch has to be slightly AFTER the pay period end date, which is 11:59PM.

							//Make sure we pass the returned accrual_amount back into calcAccrualPolicyTime() as the new balance so rollover/maximum balances are all properly handled.
							$catch_up_accrual_amount = $this->calcAccrualPolicyTime( $u_obj, $catch_up_epoch, $offset, $pps_obj, $catch_up_pay_period_arr, $accrual_balance, false );
							Debug::Text( '    Catch-Up Accrual Amount: ' . $catch_up_accrual_amount . ' Epoch: ' . TTDate::getDate( 'DATE+TIME', $catch_up_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

							if ( $catch_up_accrual_amount != 0 ) {
								$catch_up_note .= TTi18n::getText( 'Includes %1 from %2' . "\n", [ TTDate::getTimeUnit( $catch_up_accrual_amount, 12 ), TTDate::getDate( 'DATE', $catch_up_epoch ) ] );
							}

							$total_catch_up_accrual_amount += $catch_up_accrual_amount;
						}
						Debug::Text( '  Final Catch-Up Accrual Total Amount: ' . $total_catch_up_accrual_amount, __FILE__, __LINE__, __METHOD__, 10 );

						unset( $catch_up_start_epoch, $catch_up_end_epoch, $catch_up_epoch, $catch_up_accrual_amount, $catch_up_pay_period_arr );
						$this->is_catch_up_mode = false;
					}
				}
			}

			if ( $in_apply_frequency_window == true && $this->isEligible( $u_obj, $epoch, $offset, $pps_obj, $pay_period_arr ) == false ) {
				Debug::Text( '   Not eligible...', __FILE__, __LINE__, __METHOD__, 10 );
				$in_apply_frequency_window = false;
			}

			if ( $this->inRolloverFrequencyWindow( $epoch, $offset, $u_obj, $pay_period_start_date ) ) {
				Debug::Text( '   In rollover window...', __FILE__, __LINE__, __METHOD__, 10 );
				$in_apply_rollover_window = true;
			}

			if ( $in_apply_frequency_window == true || $in_apply_rollover_window == true ) {
				$milestone_obj = $this->getActiveMilestoneObject( $u_obj, $epoch );
			}

			if ( $in_apply_rollover_window == true && ( isset( $milestone_obj ) && is_object( $milestone_obj ) ) ) {
				//Handle maximum rollover adjustments before continuing.
				if ( $accrual_balance > $milestone_obj->getRolloverTime() ) {
					$rollover_accrual_adjustment = TTMath::sub( $milestone_obj->getRolloverTime(), $accrual_balance ); //Allow decimal points here, as changing from an hour based accrual to calendar based, the rollover might need to use decimals.
					Debug::Text( '   Adding rollover adjustment of: ' . $rollover_accrual_adjustment, __FILE__, __LINE__, __METHOD__, 10 );

					//Check to make sure there isn't an identical entry already made.
					//Ignore rollover adjustment is another adjustment of any amount has been made on the same day.
					$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */
					if ( $update_records == true ) {
						$alf->getByCompanyIdAndUserIdAndAccrualPolicyAccountAndTypeIDAndTimeStamp( $u_obj->getCompany(), $u_obj->getID(), $this->getAccrualPolicyAccount(), 60, TTDate::getMiddleDayEpoch( $epoch ) );
					}
					if ( $alf->getRecordCount() == 0 ) {
						//Get effective date, try to use the current milestone rollover date to make things more clear.
						$current_milestone_rollover_date = $this->getCurrentMilestoneRolloverDate( $epoch, $u_obj, true ); //If milestone rollover date comes after the current epoch, back date it by one year.

						if ( $update_records == true ) {
							$apalf = TTnew( 'AccrualPolicyAccountListFactory' ); /** @var AccrualPolicyAccountListFactory $aalf */
							$accrual_policy_account_list = $apalf->getByCompanyIdArray( $this->getCompany(), false );

							//Don't round to the nearest minute, as that can cause too much error on weekly frequencies.
							$af = TTnew( 'AccrualFactory' ); /** @var AccrualFactory $af */
							$af->setUser( $u_obj->getID() );
							$af->setType( 60 ); //Rollover Adjustment
							$af->setAccrualPolicyAccount( $this->getAccrualPolicyAccount() );
							$af->setAccrualPolicy( $this->getId() );
							$af->setAmount( $rollover_accrual_adjustment );
							$af->setTimeStamp( TTDate::getMiddleDayEpoch( $current_milestone_rollover_date ) );
							$af->setNote( TTi18n::getText('Rollover due to Accrual Policy: %1 To Account: %2', [ $this->getName(), $accrual_policy_account_list[$this->getExcessRolloverAccrualPolicyAccount()] ?? TTi18n::getText('Unknown') ] ) );
							$af->setEnableCalcBalance( true );
							if ( $af->isValid() ) {
								$af->Save();
							}
							unset( $af );

							//Check to make sure there isn't an identical entry already made.
							//Ignore rollover adjustment if another adjustment of any amount has been made on the same day.
							//  **NOTE: We need to support multiple policies rolling over to the same accrual account, on the same day. So when checking for duplicates, include the source accrual policy as well.
							$alf->getByCompanyIdAndUserIdAndAccrualPolicyAccountAndAccrualPolicyAndTypeIDAndTimeStamp( $u_obj->getCompany(), $u_obj->getID(), $this->getExcessRolloverAccrualPolicyAccount(), $this->getId(), 65, TTDate::getMiddleDayEpoch( $epoch ) );
							if ( $alf->getRecordCount() == 0 ) {
								//Handle moving excess rollover balance to a different account.
								if ( $this->getExcessRolloverAccrualPolicyAccount() != TTUUID::getZeroID() ) {
									$af = TTnew( 'AccrualFactory' ); /** @var AccrualFactory $af */
									$af->setUser( $u_obj->getID() );
									$af->setType( 65 ); //Excess Rollover Adjustment
									$af->setAccrualPolicyAccount( $this->getExcessRolloverAccrualPolicyAccount() );
									$af->setAccrualPolicy( $this->getId() );
									$af->setAmount( TTMath::mul( $rollover_accrual_adjustment, -1 ) ); //Opposite sign from above rollover adjustment.
									$af->setTimeStamp( TTDate::getMiddleDayEpoch( $current_milestone_rollover_date ) );
									$af->setNote( TTi18n::getText('Rollover due to Accrual Policy: %1 From Account: %2', [ $this->getName(), $accrual_policy_account_list[$this->getAccrualPolicyAccount()] ?? TTi18n::getText('Unknown') ] ) );
									$af->setEnableCalcBalance( true );
									if ( $af->isValid() ) {
										$af->Save();
									}
								}
							} else {
								Debug::Text( '   Found duplicate excess rollover accrual entry, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $af, $apalf, $accrual_policy_account_list  );
						} else {
							Debug::Text( '   NOT UPDATING RECORDS...', __FILE__, __LINE__, __METHOD__, 10 );
							$retval = $rollover_accrual_adjustment;
						}

						//Make sure we get updated balance after rollover adjustment was made.
						$accrual_balance += $rollover_accrual_adjustment;

						unset( $current_milestone_rollover_date );
					} else {
						Debug::Text( '   Found duplicate rollover accrual entry, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '   Balance hasnt exceeded rollover adjustment... Balance: ' . $accrual_balance . ' Milestone Rollover Time: ' . $milestone_obj->getRolloverTime(), __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $rollover_accrual_adjustment, $alf );
			}

			if ( $in_apply_frequency_window === true ) {
				if ( isset( $milestone_obj ) && is_object( $milestone_obj ) ) {
					Debug::Text( '  Found Matching Milestone, Accrual Rate: (ID: ' . $milestone_obj->getId() . ') ' . $milestone_obj->getAccrualRate() . '/year', __FILE__, __LINE__, __METHOD__, 10 );

					//Make sure we get updated balance after rollover adjustment was made.
					if ( $accrual_balance < $milestone_obj->getMaximumTime() ) {
						if ( $total_catch_up_accrual_amount > 0 ) { //If catch up amount is specified, just use it.
							//When the employee is hired on the same date that the accruals are recorded, we need to add that with the catch up accrual amount.
							if ( $this->inApplyFrequencyWindow( $epoch, $offset, null, $u_obj ) == true ) {
								$accrual_amount = $this->calcAccrualAmount( $milestone_obj, 0, $annual_pay_periods );
								$catch_up_note .= TTi18n::getText( 'Includes %1 from %2' . "\n", [ TTDate::getTimeUnit( $accrual_amount, 12 ), TTDate::getDate( 'DATE', $epoch ) ] );
							} else {
								$accrual_amount = 0;
							}

							$accrual_amount += $total_catch_up_accrual_amount;
						} else {
							$accrual_amount = $this->calcAccrualAmount( $milestone_obj, 0, $annual_pay_periods );

							//Check if this is the initial period and pro-rate the accrual amount.
							// Don't pro-rate if catch-up amount is specified, as it will already take into account the pro-rated amount from the hire date.
							if ( $this->getType() == 20 //Calendar based only
									&&
									(
											( $this->getEnableOpeningBalance() == false && $this->getEnableProRateInitialPeriod() == true && $this->isInitialApplyFrequencyWindow( $epoch, $offset, $pay_period_dates, $u_obj ) == true )
											||
											( $this->getEnableOpeningBalance() == true && $this->getEnableProRateInitialPeriod() == true && $this->getMinimumEmployedDaysAdjustedHireDate( $u_obj->getHireDate() ) == TTDate::getMiddleDayEpoch( $epoch ) )
									)
							) {
								$accrual_amount = $this->getProRateInitialFrequencyWindow( $accrual_amount, $epoch, $offset, $pay_period_dates, $u_obj );
							}
						}

						if ( $accrual_amount > 0 ) {
							$new_accrual_balance = TTMath::add( $accrual_balance, $accrual_amount );

							//If Maximum time is set to 0, make that unlimited.
							if ( $milestone_obj->getMaximumTime() > 0 && $new_accrual_balance > $milestone_obj->getMaximumTime() ) {
								$accrual_amount = TTMath::sub( $milestone_obj->getMaximumTime(), $accrual_balance, 0 );
							}
							Debug::Text( '   Min/Max Adjusted Accrual Amount: ' . $accrual_amount . ' Limits: Max: ' . $milestone_obj->getMaximumTime(), __FILE__, __LINE__, __METHOD__, 10 );

							//Check to make sure there isn't an identical entry already made.
							$alf = TTnew( 'AccrualListFactory' ); /** @var AccrualListFactory $alf */
							if ( $update_records == true ) {
								$alf->getByCompanyIdAndUserIdAndAccrualPolicyAccountAndTimeStampAndAmount( $u_obj->getCompany(), $u_obj->getID(), $this->getAccrualPolicyAccount(), TTDate::getMiddleDayEpoch( $epoch ), $accrual_amount );
							}
							if ( $alf->getRecordCount() == 0 ) {
								if ( $update_records == true ) {
									Debug::Text( '   UPDATING RECORDS...', __FILE__, __LINE__, __METHOD__, 10 );
									//Round to nearest 1min
									$af = TTnew( 'AccrualFactory' ); /** @var AccrualFactory $af */
									$af->setUser( $u_obj->getID() );
									$af->setType( 75 ); //Accrual Policy
									$af->setAccrualPolicyAccount( $this->getAccrualPolicyAccount() );
									$af->setAccrualPolicy( $this->getId() );
									$af->setAmount( $accrual_amount );
									$af->setTimeStamp( TTDate::getMiddleDayEpoch( $epoch ) );
									$af->setNote( $catch_up_note );
									$af->setEnableCalcBalance( true );
									if ( $af->isValid() ) {
										$af->Save();
									}
								} else {
									Debug::Text( '   NOT UPDATING RECORDS...', __FILE__, __LINE__, __METHOD__, 10 );
									$retval += $accrual_amount;
								}
							} else {
								Debug::Text( '   Found duplicate accrual entry, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $accrual_amount, $accrual_balance, $new_accrual_balance );
						} else {
							Debug::Text( '   Accrual Amount is 0...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( '   Accrual Balance is outside Milestone Range. Skipping...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '  DID NOT Find Matching Milestone.', __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $milestone_obj );
			} else {
				Debug::Text( '  NOT in apply frequency window...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( '  User is not active (Status: ' . $u_obj->getStatus() . ') or has only been employed: ' . TTDate::getDays( ( $epoch - $u_obj->getHireDate() ) ) . ' Days, not enough. Hire Date: ' . TTDate::getDATE( 'DATE+TIME', $u_obj->getHireDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( $update_records == true ) {
			return true;
		} else {
			Debug::Text( 'Retval: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}
	}

	/**
	 * @param int $epoch  EPOCH
	 * @param int $offset 79200 = 22hr offset
	 * @param bool $user_ids
	 * @return bool
	 */
	function addAccrualPolicyTime( $epoch = null, $offset = 79200, $user_ids = false ) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		Debug::Text( 'Accrual Policy ID: ' . $this->getId() . ' Current EPOCH: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */

		$pglf->StartTransaction();

		$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), [ 'accrual_policy_id' => [ $this->getId() ] ] );
		if ( $pglf->getRecordCount() > 0 ) {
			foreach ( $pglf as $pg_obj ) {
				Debug::Text( 'Found Policy Group: ' . $pg_obj->getName() . ' Company ID: ' . $pg_obj->getCompany(), __FILE__, __LINE__, __METHOD__, 10 );
				//Get all users assigned to this policy group.
				if ( is_array( $user_ids ) && count( $user_ids ) > 0 && !in_array( TTUUID::getNotExistID(), $user_ids ) ) {
					Debug::Text( 'Using users passed in by filter...', __FILE__, __LINE__, __METHOD__, 10 );
					$policy_group_users = array_intersect( (array)$pg_obj->getUser(), (array)$user_ids );
				} else {
					Debug::Text( 'Using users assigned to policy group...', __FILE__, __LINE__, __METHOD__, 10 );
					$policy_group_users = $pg_obj->getUser();
				}
				if ( is_array( $policy_group_users ) && count( $policy_group_users ) > 0 ) {
					Debug::Text( 'Found Policy Group Users: ' . count( $policy_group_users ), __FILE__, __LINE__, __METHOD__, 10 );
					foreach ( $policy_group_users as $user_id ) {
						Debug::Text( 'Policy Group User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );

						//Get User Object
						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getByIDAndCompanyID( $user_id, $this->getCompany() );
						if ( $ulf->getRecordCount() == 1 ) {
							$u_obj = $ulf->getCurrent();

							//This is an optimization to detect inactive employees sooner.
							if ( $u_obj->getStatus() != 10 ) {
								Debug::Text( '  Employee is not active, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
								continue;
							}

							//Switch to users timezone so rollover adjustments are handled on the proper date.
							$user_obj_prefs = $u_obj->getUserPreferenceObject();
							if ( is_object( $user_obj_prefs ) ) {
								$user_obj_prefs->setTimeZonePreferences();
							} else {
								//Use system timezone.
								TTDate::setTimeZone();
							}

							//Optmization to make sure we can quickly skip days outside the employment period.
							if ( $u_obj->getHireDate() != '' && TTDate::getBeginDayEpoch( $epoch ) < TTDate::getBeginDayEpoch( $u_obj->getHireDate() ) ) {
								Debug::Text( '  Before employees hire date, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
								continue;
							}
							if ( $u_obj->getTerminationDate() != '' && TTDate::getBeginDayEpoch( $epoch ) > TTDate::getBeginDayEpoch( $u_obj->getTerminationDate() ) ) {
								Debug::Text( '  After employees termination date, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
								continue;
							}

							$ppslf = TTNew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
							$ppslf->getByCompanyIdAndUserId( $u_obj->getCompany(), $u_obj->getId() );
							if ( $ppslf->getRecordCount() > 0 ) {
								$pps_obj = $ppslf->getCurrent();

								$accrual_balance = $this->getCurrentAccrualBalance( $u_obj->getID(), $this->getAccrualPolicyAccount() );

								$pay_period_arr = [];
								if ( $this->getApplyFrequency() == 10 || $this->getEligiblePeriod() == 10 ) { //10=Pay Period
									$pay_period_arr = $this->getPayPeriodArray( $pps_obj, $u_obj, ( $epoch - $offset ), ( $epoch - $offset ) );
								}

								$this->calcAccrualPolicyTime( $u_obj, $epoch, $offset, $pps_obj, $pay_period_arr, $accrual_balance, true );
							}
						} else {
							Debug::Text( 'No User Found. Company ID: ' . $this->getCompany(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				}
			}
		}

		$pglf->CommitTransaction();

		return true;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		if ( $this->getEligiblePeriod() == '' ) {
			$this->setEligiblePeriod( 0 ); //0=Always Eligible.
		}

		if ( $this->getMinimumEligibleTime() == '' ) {
			$this->setMinimumEligibleTime( 0 );
		}

		if ( $this->getMaximumEligibleTime() == '' ) {
			$this->setMaximumEligibleTime( 0 );
		}

		if ( $this->getEligibleContributingShiftPolicy() == '' ) {
			$this->setEligibleContributingShiftPolicy( TTUUID::getZeroID() );
		}

		return true;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}
		// Accrual Account
		if ( $this->getAccrualPolicyAccount() !== false ) {
			$apaplf = TTnew( 'AccrualPolicyAccountListFactory' ); /** @var AccrualPolicyAccountListFactory $apaplf */
			$this->Validator->isResultSetWithRows( 'accrual_policy_account_id',
												   $apaplf->getByID( $this->getAccrualPolicyAccount() ),
												   TTi18n::gettext( 'Accrual Account is invalid' )
			);
		}
		// Contributing Shift Policy
		if ( $this->getContributingShiftPolicy() !== false && $this->getContributingShiftPolicy() != TTUUID::getZeroID() ) {
			$csplf = TTnew( 'ContributingShiftPolicyListFactory' ); /** @var ContributingShiftPolicyListFactory $csplf */
			$this->Validator->isResultSetWithRows( 'contributing_shift_policy_id',
												   $csplf->getByID( $this->getContributingShiftPolicy() ),
												   TTi18n::gettext( 'Contributing Shift Policy is invalid' )
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
		// Name
		if ( $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing, but must check when adding a new record.
			if ( $this->getName() == '' ) {
				$this->Validator->isTRUE( 'name',
										  false,
										  TTi18n::gettext( 'Please specify a name' ) );
			}
		}

		if ( $this->getName() !== false ) {
			if ( $this->getName() != '' && $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isLength( 'name',
											$this->getName(),
											TTi18n::gettext( 'Name is too short or too long' ),
											2, 50
				);

				$this->Validator->isHTML( 'name',
										  $this->getName(),
										  TTi18n::gettext( 'Name contains invalid special characters' ),
				);
			}
			if ( $this->getName() != '' && $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  $this->isUniqueName( $this->getName() ),
										  TTi18n::gettext( 'Name is already in use' )
				);
			}
		}
		// Description
		if ( $this->getDescription() != '' ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is too short or too long' ),
										1, 2048
			);

			$this->Validator->isHTML( 'description',
									  $this->getDescription(),
									  TTi18n::gettext( 'Description contains invalid special characters' ),
			);
		}
		// Minimum Time
		$this->Validator->isNumeric( 'minimum_time',
									 $this->getMinimumTime(),
									 TTi18n::gettext( 'Incorrect Minimum Time' )
		);
		// Maximum Time
		$this->Validator->isNumeric( 'maximum_time',
									 $this->getMaximumTime(),
									 TTi18n::gettext( 'Incorrect Maximum Time' )
		);
		// Frequency
		if ( $this->getApplyFrequency() != '' ) {
			$this->Validator->inArrayKey( 'apply_frequency_id',
										  $this->getApplyFrequency(),
										  TTi18n::gettext( 'Incorrect frequency' ),
										  $this->getOptions( 'apply_frequency' )
			);
		}

		// Eligible Period
		if ( $this->getEligiblePeriod() != '' ) {
			$this->Validator->inArrayKey( 'eligible_period_id',
										  $this->getEligiblePeriod(),
										  TTi18n::gettext( 'Incorrect eligible period' ),
										  $this->getOptions( 'eligible_period' )
			);
		}

		// Minimum Eligible Time
		if ( $this->getMinimumEligibleTime() !== false ) {
			$this->Validator->isNumeric( 'minimum_eligible_time',
										 $this->getMinimumEligibleTime(),
										 TTi18n::gettext( 'Incorrect Minimum Eligible Time' )
			);
		}

		// Maximum Eligible Time
		if ( $this->getMinimumEligibleTime() !== false ) {
			$this->Validator->isNumeric( 'maximum_eligible_time',
										 $this->getMaximumEligibleTime(),
										 TTi18n::gettext( 'Incorrect Maximum Eligible Time' )
			);
		}

		if ( $this->getEligiblePeriod() !== 0 && ( empty( $this->getEligibleContributingShiftPolicy() ) || $this->getEligibleContributingShiftPolicy() == TTUUID::getZeroID() ) ) { //30=Hour Based
			$this->Validator->isTRUE( 'eligible_contributing_shift_policy_id',
									  false,
									  TTi18n::gettext( 'Eligible Contributing Shift Policy must be specified' ) );
		}


		if ( $this->getDeleted() == false ) {
			// Frequency month
			if ( $this->getApplyFrequencyMonth() != '' ) {
				$this->Validator->inArrayKey( 'apply_frequency_month',
											  $this->getApplyFrequencyMonth(),
											  TTi18n::gettext( 'Incorrect frequency month' ),
											  TTDate::getMonthOfYearArray()
				);
			}
			// Frequency day of month
			if ( $this->getApplyFrequencyDayOfMonth() != '' ) {
				$this->Validator->inArrayKey( 'apply_frequency_day_of_month',
											  $this->getApplyFrequencyDayOfMonth(),
											  TTi18n::gettext( 'Incorrect frequency day of month' ),
											  TTDate::getDayOfMonthArray()
				);
			}
			// Frequency day of week
			if ( $this->getApplyFrequencyDayOfWeek() != '' ) {
				$this->Validator->inArrayKey( 'apply_frequency_day_of_week',
											  $this->getApplyFrequencyDayOfWeek(),
											  TTi18n::gettext( 'Incorrect frequency day of week' ),
											  TTDate::getDayOfWeekArray()
				);
			}
			// Frequency quarter month
			if ( $this->getApplyFrequencyQuarterMonth() != '' ) {
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
			// Milestone rollover month
			if ( $this->getMilestoneRolloverMonth() != '' ) {
				$this->Validator->inArrayKey( 'milestone_rollover_month',
											  $this->getMilestoneRolloverMonth(),
											  TTi18n::gettext( 'Incorrect milestone rollover month' ),
											  TTDate::getMonthOfYearArray()
				);
			}
			// Milestone rollover day of month
			if ( $this->getMilestoneRolloverDayOfMonth() != '' ) {
				$this->Validator->inArrayKey( 'milestone_rollover_day_of_month',
											  $this->getMilestoneRolloverDayOfMonth(),
											  TTi18n::gettext( 'Incorrect milestone rollover day of month' ),
											  TTDate::getDayOfMonthArray()
				);
			}

			if ( $ignore_warning == false && is_object( $this->getCompanyObject() ) && $this->getCompanyObject()->getProductEdition() >= 15 && $this->getEligiblePeriod() != 0 ) { //0=Always Eligible.
				if ( $this->getType() == 20 ) { //20=Calendar
					if ( $this->getEligiblePeriod() != $this->getApplyFrequency() ) {
						$this->Validator->Warning( 'eligible_period_id', TTi18n::gettext( 'Eligibility Period does not match Apply Frequency, this may result in unexpected behavior' ) );
					}
				} else if ( $this->getType() == 30 ) { //30=Hour
					if ( $this->getContributingShiftPolicy() != $this->getEligibleContributingShiftPolicy() ) {
						$this->Validator->Warning( 'eligible_contributing_shift_policy_id', TTi18n::gettext( 'Contributing Shift Policy does not match Eligibility Contributing Shift Policy, this may result in unexpected behavior' ) );
					}
				}
			}
		}

		//Excess Rollover Accrual Account
		if ( $this->getExcessRolloverAccrualPolicyAccount() !== false && $this->getExcessRolloverAccrualPolicyAccount() !== TTUUID::getZeroID() ) {
			$apaplf = TTnew( 'AccrualPolicyAccountListFactory' ); /** @var AccrualPolicyAccountListFactory $apaplf */
			$this->Validator->isResultSetWithRows( 'excess_rollover_accrual_policy_account_id',
												   $apaplf->getByID( $this->getExcessRolloverAccrualPolicyAccount() ),
												   TTi18n::gettext( 'Excess Rollover Accrual Account is invalid' )
			);

			if ( $this->getAccrualPolicyAccount() == $this->getExcessRolloverAccrualPolicyAccount() ) {
				$this->Validator->isTrue( 'excess_rollover_accrual_policy_account_id',
										  false,
										  TTi18n::gettext( 'Excess Rollover Accrual Account can not be the same as Accrual Account' ) );
			}
		}

		// Minimum Employed days
		if ( $this->getMinimumEmployedDays() !== false ) {
			$this->Validator->isNumeric( 'minimum_employed_days',
										 $this->getMinimumEmployedDays(),
										 TTi18n::gettext( 'Incorrect Minimum Employed days' )
			);
			$this->Validator->isGreaterThan( 'minimum_employed_days',
										  $this->getMinimumEmployedDays(),
										  TTi18n::gettext( 'Minimum employed days cannot be less than 0' ),
										  0
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		//NOTE: Opening Balance feature now supports minimum employed days to be something greater than 0.
		//if ( $this->getEnableOpeningBalance() == true && $this->getMinimumEmployedDays() != 0 ) {
		//	$this->Validator->isTRUE( 'minimum_employed_days',
		//							  false,
		//							  TTi18n::gettext( 'Minimum Employed Days must be set to 0 when Opening Balance is Enabled' ) );
		//}

		if ( $this->getType() == 30 && ( empty( $this->getContributingShiftPolicy() ) || $this->getContributingShiftPolicy() == TTUUID::getZeroID() ) ) { //30=Hour Based
			$this->Validator->isTRUE( 'contributing_shift_policy_id',
									  false,
									  TTi18n::gettext( 'Contributing Shift Policy must be specified' ) );
		}

		/*
		//They need to be able to delete accrual policies while still keeping records originally created by the accrual policy.
		if ( $this->getDeleted() == TRUE ) {
			//Check to make sure there are no hours using this accrual policy.
			$alf = TTnew( 'AccrualListFactory' );
			$alf->getByAccrualPolicyID( $this->getId(), 1 ); //Limit 1
			if ( $alf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE(	'in_use',
											FALSE,
											TTi18n::gettext('This accrual policy is in use'));

			}
		}
		*/

		if ( $this->getDeleted() == true ) {
			//Check to make sure nothing else references this policy, so we can be sure its okay to delete it.
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), [ 'accrual_policy' => $this->getId() ], 1 );
			if ( $pglf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This policy is currently in use' ) . ' ' . TTi18n::gettext( 'by policy groups' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == true ) {
			Debug::Text( 'UnAssign Accruals records from Policy: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			$af = TTnew( 'AccrualFactory' ); /** @var AccrualFactory $af */

			$query = 'update ' . $af->getTable() . ' set accrual_policy_id = \'' . TTUUID::getZeroID() . '\' where accrual_policy_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );
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
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'in_use':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'apply_frequency':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'eligible_period':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}


		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Accrual Policy' ) .': '. $this->getName(), null, $this->getTable(), $this );
	}
}

?>
