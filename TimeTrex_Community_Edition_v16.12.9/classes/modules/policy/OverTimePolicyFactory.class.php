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
class OverTimePolicyFactory extends Factory {
	protected $table = 'over_time_policy';
	protected $pk_sequence_name = 'over_time_policy_id_seq'; //PK Sequence name

	protected $company_obj = null;
	protected $contributing_shift_policy_obj = null;
	protected $pay_code_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'trigger_time' )->setFunctionMap( 'TriggerTime' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'rate' )->setFunctionMap( 'Rate' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'accrual_policy_id' )->setFunctionMap( 'AccrualPolicy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'accrual_rate' )->setFunctionMap( 'AccrualRate' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'pay_stub_entry_account_id' )->setFunctionMap( 'PayStubEntryAccount' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'wage_group_id' )->setFunctionMap( 'WageGroup' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'pay_code_id' )->setFunctionMap( 'PayCode' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'pay_formula_policy_id' )->setFunctionMap( 'PayFormulaPolicy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'contributing_shift_policy_id' )->setFunctionMap( 'ContributingShiftPolicy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'branch_selection_type_id' )->setFunctionMap( 'BranchSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'exclude_default_branch' )->setFunctionMap( 'ExcludeDefaultBranch' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'department_selection_type_id' )->setFunctionMap( 'DepartmentSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'exclude_default_department' )->setFunctionMap( 'ExcludeDefaultDepartment' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'job_group_selection_type_id' )->setFunctionMap( 'JobGroupSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'job_selection_type_id' )->setFunctionMap( 'JobSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'exclude_default_job' )->setFunctionMap( 'ExcludeDefaultJob' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'job_item_group_selection_type_id' )->setFunctionMap( 'JobItemGroupSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'job_item_selection_type_id' )->setFunctionMap( 'JobItemSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'exclude_default_job_item' )->setFunctionMap( 'ExcludeDefaultJobItem' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'trigger_time_adjust_contributing_shift_policy_id' )->setFunctionMap( 'TriggerTimeAdjustContributingShiftPolicy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'punch_tag_group_selection_type_id' )->setFunctionMap( 'PunchTagGroupSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'punch_tag_selection_type_id' )->setFunctionMap( 'PunchTagSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'exclude_default_punch_tag' )->setFunctionMap( 'ExcludeDefaultPunchTag' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'contributing_pay_code_policy_id' )->setFunctionMap( 'ContributingPayCodePolicy' )->setType( 'uuid' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_overtime_policy' )->setLabel( TTi18n::getText( 'Overtime Policy' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) )->setWidth( '100%' ),
											TTSField::new( 'contributing_shift_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Contributing Shift Policy' ) )->setDataSource( TTSAPI::new( 'APIContributingShiftPolicy' )->setMethod( 'getContributingShiftPolicy' ) ),
											TTSField::new( 'type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIOverTimePolicy' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'pay_code_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Code' ) )->setDataSource( TTSAPI::new( 'APIPayCode' )->setMethod( 'getPayCode' ) ),
											TTSField::new( 'pay_formula_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Formula Policy' ) )->setDataSource( TTSAPI::new( 'APIPayFormulaPolicy' )->setMethod( 'getPayFormulaPolicy' ) ),
									)
							),
							TTSTab::new( 'tab_differential_criteria' )->setLabel( TTi18n::getText( 'Differential Criteria' ) )->setInitCallback( 'initSubDifferentialCriteriaView' )->setHTMLTemplate( '<div id="tab_differential_criteria" class="edit-view-tab-outside">\n\t\t\t\t\t<div class="edit-view-tab" id="tab_differential_criteria_content_div">\n\t\t\t\t\t\t<div class="first-column full-width-column"></div>\n\t\t\t\t\t\t<div class="save-and-continue-div permission-defined-div">\n\t\t\t\t\t\t\t<span class="message permission-message"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>' )->setDisplayOnMassEdit( false )->setSubView( true ),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'type_id' )->setType( 'numeric' )->setColumn( 'a.type_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'description' )->setType( 'text' )->setColumn( 'a.description' )->setVisible( 'AI', true ),

							TTSSearchField::new( 'contributing_shift_policy_id' )->setType( 'uuid' )->setColumn( 'a.contributing_shift_policy_id' ),
							TTSSearchField::new( 'pay_code_id' )->setType( 'uuid' )->setColumn( 'a.pay_code_id' ),
							TTSSearchField::new( 'pay_formula_policy_id' )->setType( 'uuid' )->setColumn( 'a.pay_formula_policy_id' ),
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIOverTimePolicy' )->setMethod( 'getOverTimePolicy' )
									->setSummary( 'Get overtime policy records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIOverTimePolicy' )->setMethod( 'setOverTimePolicy' )
									->setSummary( 'Add or edit overtime policy records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIOverTimePolicy' )->setMethod( 'deleteOverTimePolicy' )
									->setSummary( 'Delete overtime policy records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIOverTimePolicy' )->setMethod( 'getOverTimePolicy' ) ),
											   ) ),
							TTSAPI::new( 'APIOverTimePolicy' )->setMethod( 'getOverTimePolicyDefaultData' )
									->setSummary( 'Get default overtime policy data used for creating new policies. Use this before calling setOverTimePolicy to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * Use the ordering of Type_ID
	 * We basically convert all types to Daily OT prior to calculation.
	 * Daily time always takes precedence, because more then 12hrs in a day deserves double time.
	 *    Then Weekly time
	 *    Then Bi Weekly
	 *    Then Day Of Week
	 * @param $name
	 * @param null $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'type':
				$retval = [
						10  => TTi18n::gettext( 'Daily' ),
						20  => TTi18n::gettext( 'Weekly' ),
						30  => TTi18n::gettext( 'Bi-Weekly' ), //Need to recalculate two weeks ahead, instead of just one.

						//38 => TTi18n::gettext('Pay Period'), //Need to recalculate in the future as necessary. Handling dates in the middle of a week may be a problem too.
						//39 => TTi18n::gettext('Monthly'), //Need to recalculate in the future as necessary. Handling dates in the middle of a week may be a problem too.
						40  => TTi18n::gettext( 'Sunday' ),
						50  => TTi18n::gettext( 'Monday' ),
						60  => TTi18n::gettext( 'Tuesday' ),
						70  => TTi18n::gettext( 'Wednesday' ),
						80  => TTi18n::gettext( 'Thursday' ),
						90  => TTi18n::gettext( 'Friday' ),
						100 => TTi18n::gettext( 'Saturday' ),

						150 => TTi18n::gettext( '2 Or More Days/Week Consecutively Worked' ),
						151 => TTi18n::gettext( '3 Or More Days/Week Consecutively Worked' ),
						152 => TTi18n::gettext( '4 Or More Days/Week Consecutively Worked' ),
						153 => TTi18n::gettext( '5 Or More Days/Week Consecutively Worked' ),
						154 => TTi18n::gettext( '6 Or More Days/Week Consecutively Worked' ),
						155 => TTi18n::gettext( '7 Or More Days/Week Consecutively Worked' ),

						180 => TTi18n::gettext( 'Holiday' ), //Handled in conjunction with Contributing Shift Policies and Daily OT policies.
						200 => TTi18n::gettext( 'Over Schedule (Daily) / No Schedule' ),
						210 => TTi18n::gettext( 'Over Schedule (Weekly) / No Schedule' ),
						//220 => TTi18n::gettext('Over Schedule (Pay Period) / No Schedule'),
						//230 => TTi18n::gettext('Over Schedule (Monthly) / No Schedule'),

						300 => TTi18n::gettext( '2 Or More Days Consecutively Worked' ),
						301 => TTi18n::gettext( '3 Or More Days Consecutively Worked' ),
						302 => TTi18n::gettext( '4 Or More Days Consecutively Worked' ),
						303 => TTi18n::gettext( '5 Or More Days Consecutively Worked' ),
						304 => TTi18n::gettext( '6 Or More Days Consecutively Worked' ),
						305 => TTi18n::gettext( '7 Or More Days Consecutively Worked' ),

						350 => TTi18n::gettext( '2nd Consecutive Day Worked' ),
						351 => TTi18n::gettext( '3rd Consecutive Day Worked' ),
						352 => TTi18n::gettext( '4th Consecutive Day Worked' ),
						353 => TTi18n::gettext( '5th Consecutive Day Worked' ),
						354 => TTi18n::gettext( '6th Consecutive Day Worked' ),
						355 => TTi18n::gettext( '7th Consecutive Day Worked' ),

						//This has to be just by week, otherwise there is no boundary to figure it out?
						400 => TTi18n::gettext( '2 Or More Days/Week Worked' ),
						401 => TTi18n::gettext( '3 Or More Days/Week Worked' ),
						402 => TTi18n::gettext( '4 Or More Days/Week Worked' ),
						403 => TTi18n::gettext( '5 Or More Days/Week Worked' ),
						404 => TTi18n::gettext( '6 Or More Days/Week Worked' ),
						405 => TTi18n::gettext( '7 Or More Days/Week Worked' ),

						503 => TTi18n::gettext( 'Every 3 Weeks' ), //Need to recalculate two weeks ahead, instead of just one.
						504 => TTi18n::gettext( 'Every 4 Weeks' ),
						505 => TTi18n::gettext( 'Every 5 Weeks' ),
						506 => TTi18n::gettext( 'Every 6 Weeks' ),
						507 => TTi18n::gettext( 'Every 7 Weeks' ),
						508 => TTi18n::gettext( 'Every 8 Weeks' ),
						509 => TTi18n::gettext( 'Every 9 Weeks' ),
						510 => TTi18n::gettext( 'Every 10 Weeks' ),
						511 => TTi18n::gettext( 'Every 11 Weeks' ),
						512 => TTi18n::gettext( 'Every 12 Weeks' ),
				];
				break;
			case 'calculation_order':
				//Use the ordering of Type_ID
				//We basically convert all types to Daily OT prior to calculation.
				//1. Day Of Week (Since it should activate after 0hrs usually)
				//2. Daily time (because more then 12hrs in a day deserves double time)
				//3. Weekly time (Make sure Weekly >40, >50 can be stacked)
				//4. Bi-Weekly
				//5. >2 Weeks

				$retval = [
						10 => 170, //Daily
						20 => 200, //Weekly
						30 => 300, //Bi-Weekly

						503 => 353, //Every 3 Weeks
						504 => 354, //Every 4 Weeks
						505 => 355, //Every 5 Weeks
						506 => 356, //Every 6 Weeks
						507 => 357, //Every 7 Weeks
						508 => 358, //Every 8 Weeks
						509 => 359, //Every 9 Weeks
						510 => 360, //Every 10 Weeks
						511 => 361, //Every 11 Weeks
						512 => 362, //Every 12 Weeks

						40  => 20, //Sunday
						50  => 30, //Monday
						60  => 40, //Tuesday
						70  => 50, //Wednesday
						80  => 60, //Thursday
						90  => 70, //Friday
						100 => 80, //Saturday

						150 => 92, //After 2-Days/Week Consecutive Worked
						151 => 91, //After 3-Days/Week Consecutive Worked
						152 => 90, //After 4-Days/Week Consecutive Worked
						153 => 89, //After 5-Days/Week Consecutive Worked
						154 => 88, //After 6-Days/Week Consecutive Worked
						155 => 87, //After 7-Days/Week Consecutive Worked

						300 => 98, //After 2-Days Consecutive Worked
						301 => 97, //After 3-Days Consecutive Worked
						302 => 96, //After 4-Days Consecutive Worked
						303 => 95, //After 5-Days Consecutive Worked
						304 => 94, //After 6-Days Consecutive Worked
						305 => 93, //After 7-Days Consecutive Worked

						//Since these are specific to certain days, they should be calculated before above consecutive policies.
						350 => 86, //2nd Consecutive Day Worked
						351 => 85, //3rd Consecutive Day Worked
						352 => 84, //4th Consecutive Day Worked
						353 => 83, //5th Consecutive Day Worked
						354 => 82, //6th Consecutive Day Worked
						355 => 81, //7th Consecutive Day Worked

						//This these are not consecutive, they should be calculated after consecutive policies.
						400 => 105, //After 2-Days/Week Worked
						401 => 104, //After 3-Days/Week Worked
						402 => 103, //After 4-Days/Week Worked
						403 => 102, //After 5-Days/Week Worked
						404 => 101, //After 6-Days/Week Worked
						405 => 100, //After 7-Days/Week Worked

						180 => 190, //Holiday - This must come after all Daily types, as this usually applies >0hrs and Daily >8 hrs should still apply too.
						200 => 180, //Over Schedule (Daily) / No Schedule
						210 => 210, //Over Schedule (Weekly) / No Schedule
				];
				break;
			case 'branch_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Branches' ),
						20 => TTi18n::gettext( 'Only Selected Branches' ),
						30 => TTi18n::gettext( 'All Except Selected Branches' ),
				];
				break;
			case 'department_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Departments' ),
						20 => TTi18n::gettext( 'Only Selected Departments' ),
						30 => TTi18n::gettext( 'All Except Selected Departments' ),
				];
				break;
			case 'job_group_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Job Groups' ),
						20 => TTi18n::gettext( 'Only Selected Job Groups' ),
						30 => TTi18n::gettext( 'All Except Selected Job Groups' ),
				];
				break;
			case 'job_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Jobs' ),
						20 => TTi18n::gettext( 'Only Selected Jobs' ),
						30 => TTi18n::gettext( 'All Except Selected Jobs' ),
				];
				break;
			case 'job_item_group_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Task Groups' ),
						20 => TTi18n::gettext( 'Only Selected Task Groups' ),
						30 => TTi18n::gettext( 'All Except Selected Task Groups' ),
				];
				break;
			case 'job_item_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Tasks' ),
						20 => TTi18n::gettext( 'Only Selected Tasks' ),
						30 => TTi18n::gettext( 'All Except Selected Tasks' ),
				];
				break;
			case 'punch_tag_group_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Punch Tag Groups' ),
						20 => TTi18n::gettext( 'Only Selected Punch Tag Groups' ),
						30 => TTi18n::gettext( 'All Except Selected Punch Tag Groups' ),
				];
				break;
			case 'punch_tag_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Punch Tags' ),
						20 => TTi18n::gettext( 'Only Selected Punch Tags' ),
						30 => TTi18n::gettext( 'All Except Selected Punch Tags' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-type'        => TTi18n::gettext( 'Type' ),
						'-1020-name'        => TTi18n::gettext( 'Name' ),
						'-1025-description' => TTi18n::gettext( 'Description' ),

						'-1030-trigger_time' => TTi18n::gettext( 'Active After' ),
						'-1040-rate'         => TTi18n::gettext( 'Rate' ),
						'-1050-accrual_rate' => TTi18n::gettext( 'Accrual Rate' ),

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
						'name',
						'description',
						'type',
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
				'id'          => 'ID',
				'company_id'  => 'Company',
				'type_id'     => 'Type',
				'type'        => false,
				'name'        => 'Name',
				'description' => 'Description',

				'trigger_time'                                     => 'TriggerTime',
				'trigger_time_adjust_contributing_shift_policy_id' => 'TriggerTimeAdjustContributingShiftPolicy',
				'trigger_time_adjust_contributing_shift_policy'    => false,

				'contributing_shift_policy_id' => 'ContributingShiftPolicy',
				'contributing_shift_policy'    => false,
				'pay_code_id'                  => 'PayCode',
				'pay_code'                     => false,
				'pay_formula_policy_id'        => 'PayFormulaPolicy',
				'pay_formula_policy'           => false,

				'branch'                           => 'Branch',
				'branch_selection_type_id'         => 'BranchSelectionType',
				'branch_selection_type'            => false,
				'exclude_default_branch'           => 'ExcludeDefaultBranch',
				'department'                       => 'Department',
				'department_selection_type_id'     => 'DepartmentSelectionType',
				'department_selection_type'        => false,
				'exclude_default_department'       => 'ExcludeDefaultDepartment',
				'job_group'                        => 'JobGroup',
				'job_group_selection_type_id'      => 'JobGroupSelectionType',
				'job_group_selection_type'         => false,
				'job'                              => 'Job',
				'job_selection_type_id'            => 'JobSelectionType',
				'job_selection_type'               => false,
				'exclude_default_job'              => 'ExcludeDefaultJob',
				'job_item_group'                   => 'JobItemGroup',
				'job_item_group_selection_type_id' => 'JobItemGroupSelectionType',
				'job_item_group_selection_type'    => false,
				'job_item'                         => 'JobItem',
				'job_item_selection_type_id'       => 'JobItemSelectionType',
				'job_item_selection_type'          => false,
				'exclude_default_job_item'         => 'ExcludeDefaultJobItem',
				'punch_tag_group_selection_type_id'=> 'PunchTagGroupSelectionType',
				'punch_tag_group'                  => 'PunchTagGroup',
				'punch_tag_selection_type_id'      => 'PunchTagSelectionType',
				'punch_tag'                        => 'PunchTag',
				'exclude_default_punch_tag'        => 'ExcludeDefaultPunchTag',
				'contributing_pay_code_policy_id'  => 'ContributingPayCodePolicy',

				'in_use'  => false,
				'deleted' => 'Deleted',
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
	function getPayCodeObject() {
		return $this->getGenericObject( 'PayCodeListFactory', $this->getPayCode(), 'pay_code_obj' );
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
	 * @return bool|int
	 */
	function getTriggerTime() {
		return $this->getGenericDataValue( 'trigger_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTriggerTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'trigger_time', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTriggerTimeAdjustContributingShiftPolicy() {
		return $this->getGenericDataValue( 'trigger_time_adjust_contributing_shift_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setTriggerTimeAdjustContributingShiftPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'trigger_time_adjust_contributing_shift_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayCode() {
		return $this->getGenericDataValue( 'pay_code_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayCode( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'pay_code_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayFormulaPolicy() {
		return $this->getGenericDataValue( 'pay_formula_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayFormulaPolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'pay_formula_policy_id', $value );
	}

	/**
	 * @return bool
	 */
	function isDifferentialCriteriaDefined() {
		if ( $this->getBranchSelectionType() == 10 && $this->getDepartmentSelectionType() == 10 && $this->getJobGroupSelectionType() == 10 && $this->getJobSelectionType() == 10 && $this->getJobItemGroupSelectionType() == 10 && $this->getJobItemSelectionType() == 10
				&& $this->getExcludeDefaultBranch() == false && $this->getExcludeDefaultDepartment() == false && $this->getExcludeDefaultJob() == false && $this->getExcludeDefaultJobItem() == false ) {
			return false;
		}

		return true;
	}

	/*

	Branch/Department/Job/Task filter functions

	*/
	/**
	 * @return bool|int
	 */
	function getBranchSelectionType() {
		return $this->getGenericDataValue( 'branch_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBranchSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'branch_selection_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultBranch() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_branch' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultBranch( $value ) {
		return $this->setGenericDataValue( 'exclude_default_branch', $this->toBool( $value ) );
	}

	/**
	 * @return array|bool
	 */
	function getBranch() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 591, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setBranch( $ids ) {
		Debug::text( 'Setting Branch IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 591, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool|int
	 */
	function getDepartmentSelectionType() {
		return $this->getGenericDataValue( 'department_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDepartmentSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'department_selection_type_id', $value );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultDepartment() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_department' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultDepartment( $value ) {
		return $this->setGenericDataValue( 'exclude_default_department', $this->toBool( $value ) );
	}

	/**
	 * @return array|bool
	 */
	function getDepartment() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 592, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setDepartment( $ids ) {
		Debug::text( 'Setting Department IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 592, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool|int
	 */
	function getJobGroupSelectionType() {
		return $this->getGenericDataValue( 'job_group_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setJobGroupSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'job_group_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getJobGroup() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 593, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobGroup( $ids ) {
		Debug::text( 'Setting Job Group IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 593, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool|int
	 */
	function getJobSelectionType() {
		return $this->getGenericDataValue( 'job_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setJobSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'job_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getJob() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 594, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJob( $ids ) {
		Debug::text( 'Setting Job IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 594, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultJob() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_job' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultJob( $value ) {
		return $this->setGenericDataValue( 'exclude_default_job', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getJobItemGroupSelectionType() {
		return $this->getGenericDataValue( 'job_item_group_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setJobItemGroupSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'job_item_group_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getJobItemGroup() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 595, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobItemGroup( $ids ) {
		Debug::text( 'Setting Task Group IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 595, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool|int
	 */
	function getJobItemSelectionType() {
		return $this->getGenericDataValue( 'job_item_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setJobItemSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'job_item_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getJobItem() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 596, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobItem( $ids ) {
		Debug::text( 'Setting Task IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 596, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultJobItem() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_job_item' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultJobItem( $value ) {
		return $this->setGenericDataValue( 'exclude_default_job_item', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getPunchTagGroupSelectionType() {
		return $this->getGenericDataValue( 'punch_tag_group_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPunchTagGroupSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'punch_tag_group_selection_type_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getPunchTagGroup() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 597, $this->getID(), 'punch_tag_group' );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setPunchTagGroup( $ids ) {
		Debug::text( 'Setting Punch Tag Group IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 597, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool|int
	 */
	function getPunchTagSelectionType() {
		return $this->getGenericDataValue( 'punch_tag_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPunchTagSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'punch_tag_selection_type_id', $value );
	}

	/**
	 * @return mixed
	 */
	function getPunchTag() {
		return $this->getCompanyGenericMapData( $this->getCompany(), 598, $this->getID(), 'punch_tag' );
	}

	/**
	 * @param array $value UUID
	 * @return bool
	 */
	function setPunchTag( $ids ) {
		Debug::text( 'Setting Punch Tag IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 598, $this->getID(), (array)$ids );
	}

	/**
	 * @return bool
	 */
	function getExcludeDefaultPunchTag() {
		return $this->fromBool( $this->getGenericDataValue( 'exclude_default_punch_tag' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExcludeDefaultPunchTag( $value ) {
		return $this->setGenericDataValue( 'exclude_default_punch_tag', $this->toBool( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getContributingPayCodePolicy() {
		return $this->getGenericDataValue( 'contributing_pay_code_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setContributingPayCodePolicy( $value ) {
		$value = TTUUID::castUUID( $value );

		//If anything but a UUID is passed in, it will get cast to a zero UUID. But we expect a NotExistID as its implied to be "ANY" in that case rather than "NONE".
		if ( $value == TTUUID::getZeroID() ) {
			$value = TTUUID::getNotExistID();
		}

		return $this->setGenericDataValue( 'contributing_pay_code_policy_id', $value );
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
		// Name
		if ( $this->Validator->getValidateOnly() == false ) {
			if ( $this->getName() == '' ) {
				$this->Validator->isTRUE( 'name',
										  false,
										  TTi18n::gettext( 'Please specify a name' ) );
			}
		}
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
		// Contributing Shift Policy
		if ( $this->getContributingShiftPolicy() !== false ) {
			$csplf = TTnew( 'ContributingShiftPolicyListFactory' ); /** @var ContributingShiftPolicyListFactory $csplf */
			$this->Validator->isResultSetWithRows( 'contributing_shift_policy_id',
												   $csplf->getByID( $this->getContributingShiftPolicy() ),
												   TTi18n::gettext( 'Contributing Shift Policy is invalid' )
			);
		}

		// Trigger Time
		if ( $this->Validator->getValidateOnly() == false && ( $this->getTriggerTime() === false || $this->getTriggerTime() === '' || $this->getTriggerTime() === null ) ) { //Must allow 0
			$this->Validator->isTRUE( 'trigger_time',
									  false,
									  TTi18n::gettext( 'Active After time must be specified' )
			);
		}
		if ( $this->getTriggerTime() !== false ) {
			$this->Validator->isNumeric( 'trigger_time',
										 $this->getTriggerTime(),
										 TTi18n::gettext( 'Incorrect Active After Time' )
			);
		}
		$this->Validator->isLessThan( 'trigger_time',
									  $this->getTriggerTime(),
									  TTi18n::gettext( 'Active After Time must be between 0 and 2000 hours' ),
									  7200000 //2000 hours
		);
		$this->Validator->isGreaterThan( 'trigger_time',
									  $this->getTriggerTime(),
									  TTi18n::gettext( 'Active After Time must be between 0 and 2000 hours' ),
									  0 //0 seconds
		);
		// Adjusting Contributing Shift Policy
		if ( $this->getTriggerTimeAdjustContributingShiftPolicy() !== false && $this->getTriggerTimeAdjustContributingShiftPolicy() != TTUUID::getZeroID() ) {
			$csplf = TTnew( 'ContributingShiftPolicyListFactory' ); /** @var ContributingShiftPolicyListFactory $csplf */
			$this->Validator->isResultSetWithRows( 'trigger_time_adjust_contributing_shift_policy_id',
												   $csplf->getByID( $this->getTriggerTimeAdjustContributingShiftPolicy() ),
												   TTi18n::gettext( 'Adjusting Contributing Shift Policy is invalid' )
			);
		}
		// Pay Code
		if ( $this->getPayCode() !== false && $this->getPayCode() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PayCodeListFactory' ); /** @var PayCodeListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'pay_code_id',
												   $pclf->getById( $this->getPayCode() ),
												   TTi18n::gettext( 'Invalid Pay Code' )
			);
		}
		// Pay Formula Policy
		if ( $this->getPayFormulaPolicy() !== false && $this->getPayFormulaPolicy() != TTUUID::getZeroID() ) {
			$pfplf = TTnew( 'PayFormulaPolicyListFactory' ); /** @var PayFormulaPolicyListFactory $pfplf */
			$this->Validator->isResultSetWithRows( 'pay_formula_policy_id',
												   $pfplf->getByID( $this->getPayFormulaPolicy() ),
												   TTi18n::gettext( 'Pay Formula Policy is invalid' )
			);
		}
		// Branch Selection Type
		if ( $this->getBranchSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'branch_selection_type',
										  $this->getBranchSelectionType(),
										  TTi18n::gettext( 'Incorrect Branch Selection Type' ),
										  $this->getOptions( 'branch_selection_type' )
			);
		}
		// Department Selection Type
		if ( $this->getDepartmentSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'department_selection_type',
										  $this->getDepartmentSelectionType(),
										  TTi18n::gettext( 'Incorrect Department Selection Type' ),
										  $this->getOptions( 'department_selection_type' )
			);
		}
		// Job Group Selection Type
		if ( $this->getJobGroupSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'job_group_selection_type',
										  $this->getJobGroupSelectionType(),
										  TTi18n::gettext( 'Incorrect Job Group Selection Type' ),
										  $this->getOptions( 'job_group_selection_type' )
			);
		}
		// Job Selection Type
		if ( $this->getJobSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'job_selection_type',
										  $this->getJobSelectionType(),
										  TTi18n::gettext( 'Incorrect Job Selection Type' ),
										  $this->getOptions( 'job_selection_type' )
			);
		}
		// Task Group Selection Type
		if ( $this->getJobItemGroupSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'job_item_group_selection_type',
										  $this->getJobItemGroupSelectionType(),
										  TTi18n::gettext( 'Incorrect Task Group Selection Type' ),
										  $this->getOptions( 'job_item_group_selection_type' )
			);
		}
		// Task Selection Type
		if ( $this->getJobItemSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'job_item_selection_type',
										  $this->getJobItemSelectionType(),
										  TTi18n::gettext( 'Incorrect Task Selection Type' ),
										  $this->getOptions( 'job_item_selection_type' )
			);
		}
		// Punch Tag Group Selection Type
		if ( $this->getPunchTagGroupSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'punch_tag_group_selection_type_id',
										  $this->getPunchTagGroupSelectionType(),
										  TTi18n::gettext( 'Incorrect Punch Tag Group Selection Type' ),
										  $this->getOptions( 'punch_tag_group_selection_type' )
			);
		}
		// Punch Tag Selection Type
		if ( $this->getPunchTagSelectionType() != '' ) {
			$this->Validator->inArrayKey( 'punch_tag_selection_type_id',
										  $this->getPunchTagSelectionType(),
										  TTi18n::gettext( 'Incorrect Punch Tag Selection Type' ),
										  $this->getOptions( 'punch_tag_selection_type' )
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() != true && $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing.
			if ( $this->getPayCode() == TTUUID::getZeroID() ) {
				$this->Validator->isTRUE( 'pay_code_id',
										  false,
										  TTi18n::gettext( 'Please choose a Pay Code' ) );
			}

			//Make sure Pay Formula Policy is defined somewhere.
			// if ( $this->getPayFormulaPolicy() == 0 AND $this->getPayCode() > 0 AND ( !is_object( $this->getPayCodeObject() ) OR ( is_object( $this->getPayCodeObject() ) AND $this->getPayCodeObject()->getPayFormulaPolicy() == 0 ) ) ) {
			if ( $this->getPayFormulaPolicy() == TTUUID::getZeroID() && ( TTUUID::isUUID( $this->getPayCode() ) && $this->getPayCode() != TTUUID::getZeroID() && $this->getPayCode() != TTUUID::getNotExistID() ) && ( !is_object( $this->getPayCodeObject() ) || ( is_object( $this->getPayCodeObject() ) && $this->getPayCodeObject()->getPayFormulaPolicy() == TTUUID::getZeroID() ) ) ) {
				$this->Validator->isTRUE( 'pay_formula_policy_id',
										  false,
										  TTi18n::gettext( 'Selected Pay Code does not have a Pay Formula Policy defined' ) );
			}
		}

		if ( $this->getDeleted() == true ) {
			//Check to make sure nothing else references this policy, so we can be sure its okay to delete it.
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), [ 'over_time_policy' => $this->getId() ], 1 );
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
	function preSave() {
		$this->data['rate'] = $this->data['accrual_rate'] = 0; //This is required until the schema removes the NOT NULL constraint.

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'OverTime Policy' ) .': '. $this->getName(), null, $this->getTable(), $this );
	}
}

?>
