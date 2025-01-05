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
class RegularTimePolicyFactory extends Factory {
	protected $table = 'regular_time_policy';
	protected $pk_sequence_name = 'regular_time_policy_id_seq'; //PK Sequence name

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
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'contributing_shift_policy_id' )->setFunctionMap( 'ContributingShiftPolicy' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'calculation_order' )->setFunctionMap( 'CalculationOrder' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'pay_formula_policy_id' )->setFunctionMap( 'PayFormulaPolicy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'pay_code_id' )->setFunctionMap( 'PayCode' )->setType( 'uuid' )->setIsNull( true ),
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
							TTSTab::new( 'tab_regular_time_policy' )->setLabel( TTi18n::getText( 'Regular Time Policy' ) )->setFields(
									new TTSFields(
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) )->setWidth( '100%' ),
											TTSField::new( 'contributing_shift_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Contributing Shift Policy' ) )->setDataSource( TTSAPI::new( 'APIContributingShiftPolicy' )->setMethod( 'getContributingShiftPolicy' ) ),
											TTSField::new( 'pay_code_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Code' ) )->setDataSource( TTSAPI::new( 'APIPayCode' )->setMethod( 'getPayCode' ) ),
											TTSField::new( 'pay_formula_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Formula Policy' ) )->setDataSource( TTSAPI::new( 'APIPayFormulaPolicy' )->setMethod( 'getPayFormulaPolicy' ) ),
											TTSField::new( 'calculation_order' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Calculation Order' ) ),
									)
							),
							TTSTab::new( 'tab_differential_criteria' )->setLabel( TTi18n::getText( 'Differential Criteria' ) )->setInitCallback( 'initSubDifferentialCriteriaView' )->setHTMLTemplate( '<div id="tab_differential_criteria" class="edit-view-tab-outside">
                <div class="edit-view-tab" id="tab_differential_criteria_content_div">
                    <div class="first-column full-width-column"></div>
                    <div class="save-and-continue-div permission-defined-div">
                        <span class="message permission-message"></span>
                    </div>
                </div>
            </div>' )->setDisplayOnMassEdit( false )->setSubView( true ),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' ),
							TTSSearchField::new( 'description' )->setType( 'text' )->setColumn( 'a.description' ),

							TTSSearchField::new( 'contributing_shift_policy_id' )->setType( 'uuid' )->setColumn( 'a.contributing_shift_policy_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_code_id' )->setType( 'uuid' )->setColumn( 'a.pay_code_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_formula_policy_id' )->setType( 'uuid' )->setColumn( 'a.pay_formula_policy_id' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIRegularTimePolicy' )->setMethod( 'getRegularTimePolicy' )
									->setSummary( 'Get regular time policy records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIRegularTimePolicy' )->setMethod( 'setRegularTimePolicy' )
									->setSummary( 'Add or edit regular time policy records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIRegularTimePolicy' )->setMethod( 'deleteRegularTimePolicy' )
									->setSummary( 'Delete regular time policy records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIRegularTimePolicy' )->setMethod( 'getRegularTimePolicy' ) ),
											   ) ),
							TTSAPI::new( 'APIRegularTimePolicy' )->setMethod( 'getRegularTimePolicyDefaultData' )
									->setSummary( 'Get default regular time policy data used for creating new regular time policies. Use this before calling setRegularTimePolicy to get the correct default data.' ),
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
						'-1010-name'        => TTi18n::gettext( 'Name' ),
						'-1020-description' => TTi18n::gettext( 'Description' ),

						'-1030-calculation_order' => TTi18n::gettext( 'Calculation Order' ),

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
				'name'        => 'Name',
				'description' => 'Description',

				'contributing_shift_policy_id' => 'ContributingShiftPolicy',
				'contributing_shift_policy'    => false,

				'pay_code_id'           => 'PayCode',
				'pay_code'              => false,
				'pay_formula_policy_id' => 'PayFormulaPolicy',
				'pay_formula_policy'    => false,

				'calculation_order' => 'CalculationOrder',

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
	 * Last regular time policy gets all remaining worked time.
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
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 581, $this->getID() );
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setBranch( $ids ) {
		Debug::text( 'Setting Branch IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 581, $this->getID(), (array)$ids );
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
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 582, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setDepartment( $ids ) {
		Debug::text( 'Setting Department IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 582, $this->getID(), (array)$ids );
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
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 583, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobGroup( $ids ) {
		Debug::text( 'Setting Job Group IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 583, $this->getID(), (array)$ids );
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
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 584, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJob( $ids ) {
		Debug::text( 'Setting Job IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 584, $this->getID(), (array)$ids );
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
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 585, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobItemGroup( $ids ) {
		Debug::text( 'Setting Task Group IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 585, $this->getID(), (array)$ids );
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
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 586, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setJobItem( $ids ) {
		Debug::text( 'Setting Task IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 586, $this->getID(), (array)$ids );
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
		return $this->getCompanyGenericMapData( $this->getCompany(), 587, $this->getID(), 'punch_tag_group' );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setPunchTagGroup( $ids ) {
		Debug::text( 'Setting Punch Tag Group IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 587, $this->getID(), (array)$ids );
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
		return $this->getCompanyGenericMapData( $this->getCompany(), 588, $this->getID(), 'punch_tag' );
	}

	/**
	 * @param array $value UUID
	 * @return bool
	 */
	function setPunchTag( $ids ) {
		Debug::text( 'Setting Punch Tag IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 588, $this->getID(), (array)$ids );
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
		// Calculation Order
		if ( $this->getCalculationOrder() !== false ) {
			$this->Validator->isNumeric( 'calculation_order',
										 $this->getCalculationOrder(),
										 TTi18n::gettext( 'Invalid Calculation Order' )
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
			if ( $this->getPayFormulaPolicy() == TTUUID::getZeroID() && ( TTUUID::isUUID( $this->getPayCode() ) && $this->getPayCode() != TTUUID::getZeroID() && $this->getPayCode() != TTUUID::getNotExistID() ) && ( !is_object( $this->getPayCodeObject() ) || ( is_object( $this->getPayCodeObject() ) && $this->getPayCodeObject()->getPayFormulaPolicy() == TTUUID::getZeroID() ) ) ) {
				$this->Validator->isTRUE( 'pay_formula_policy_id',
										  false,
										  TTi18n::gettext( 'Selected Pay Code does not have a Pay Formula Policy defined' ) );
			}

			if ( $ignore_warning == false ) {
				$rtplf = new RegularTimePolicyListFactory(); /* @var RegularTimePolicyListFactory $rtplf */
				$rtplf->getByCompanyIdAndContributingShiftIdAndPayCodeIdAndPayFormulaPolicyId( $this->getCompany(), $this->getContributingShiftPolicy(), $this->getPayCode(), $this->getPayFormulaPolicy() );
				if ( $rtplf->getRecordCount() > 0 ) {
					foreach ( $rtplf as $rtp_obj ) {
						if ( $this->getId() != $rtp_obj->getId() ) {
							$duplicate_rtp_policies[] = $rtp_obj->getName();
						}
					}

					if ( isset( $duplicate_rtp_policies ) && count( $duplicate_rtp_policies ) > 0 ) {
						$this->Validator->Warning( 'pay_code_id',
												   TTi18n::gettext( 'Duplicate regular time policy found with the same Contributing Shift Policy, Pay Code, and Pay Formula Policy. Please consider using it instead. (%1)', implode( ', ', $duplicate_rtp_policies ) )
						);
					}
				}
				unset( $rtplf, $rtp_obj, $duplicate_rtp_policies );
			}
		}

		if ( $this->getDeleted() == true ) {
			//Check to make sure nothing else references this policy, so we can be sure its okay to delete it.
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), [ 'regular_time_policy' => $this->getId() ], 1 );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Regular Time Policy' ) .': '. $this->getName(), null, $this->getTable(), $this );
	}
}

?>
