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
class MealPolicyFactory extends Factory {
	protected $table = 'meal_policy';
	protected $pk_sequence_name = 'meal_policy_id_seq'; //PK Sequence name

	protected $company_obj = null;
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
							TTSCol::new( 'amount' )->setFunctionMap( 'Meal Time' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'trigger_time' )->setFunctionMap( 'Active After' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'start_window' )->setFunctionMap( 'Start Window' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'window_length' )->setFunctionMap( 'Window Length' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'include_lunch_punch_time' )->setFunctionMap( 'Include Lunch Punch' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'auto_detect_type_id' )->setFunctionMap( 'Auto Detect Meals By' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'minimum_punch_time' )->setFunctionMap( 'Minimum Punch Time' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'maximum_punch_time' )->setFunctionMap( 'Maximum Punch Time' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'pay_code_id' )->setFunctionMap( 'Pay Code' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'pay_formula_policy_id' )->setFunctionMap( 'Pay Formula Policy' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'branch_id' )->setFunctionMap( 'Branch' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'department_id' )->setFunctionMap( 'Department' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'job_id' )->setFunctionMap( 'Job' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'job_item_id' )->setFunctionMap( 'Job Item' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'allocation_type_id' )->setFunctionMap( 'Allocation Type' )->setType( 'smallint' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_meal_policy' )->setLabel( TTi18n::getText( 'Meal Policy' ) )->setFields(
									new TTSFields(
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) )->setWidth( '100%' ),
											TTSField::new( 'type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIMealPolicy' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'trigger_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Active After' ) ),
											TTSField::new( 'amount' )->setType( 'time' )->setLabel( TTi18n::getText( 'Deduction/Addition Time' ) ),
											TTSField::new( 'auto_detect_type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Auto-Detect Meals By' ) )->setDataSource( TTSAPI::new( 'APIMealPolicy' )->setMethod( 'getOptions' )->setArg( 'auto_detect_type' ) ),
											TTSField::new( 'minimum_punch_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Minimum Punch Time' ) ),
											TTSField::new( 'maximum_punch_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Maximum Punch Time' ) ),
											TTSField::new( 'start_window' )->setType( 'time' )->setLabel( TTi18n::getText( 'Start Window' ) ),
											TTSField::new( 'window_length' )->setType( 'time' )->setLabel( TTi18n::getText( 'Window Length' ) ),
											TTSField::new( 'include_lunch_punch_time' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Include Any Punched Time for Meal' ) ),
											TTSField::new( 'allocation_type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Allocation Type' ) )->setDataSource( TTSAPI::new( 'APIMealPolicy' )->setMethod( 'getOptions' )->setArg( 'allocation_type' ) ),
											TTSField::new( 'pay_code_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Code' ) )->setDataSource( TTSAPI::new( 'APIPayCode' )->setMethod( 'getPayCode' ) ),
											TTSField::new( 'pay_formula_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Formula Policy' ) )->setDataSource( TTSAPI::new( 'APIPayFormulaPolicy' )->setMethod( 'getPayFormulaPolicy' ) ),
									)
							),
					)->addAudit()
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
							TTSSearchField::new( 'pay_code_id' )->setType( 'uuid' )->setColumn( 'a.pay_code_id' )->setMulti( true ),
							TTSSearchField::new( 'pay_formula_policy_id' )->setType( 'uuid' )->setColumn( 'a.pay_formula_policy_id' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIMealPolicy' )->setMethod( 'getMealPolicy' )
									->setSummary( 'Get meal policy records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIMealPolicy' )->setMethod( 'setMealPolicy' )
									->setSummary( 'Add or edit meal policy records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIMealPolicy' )->setMethod( 'deleteMealPolicy' )
									->setSummary( 'Delete meal policy records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIMealPolicy' )->setMethod( 'getMealPolicy' ) ),
											   ) ),
							TTSAPI::new( 'APIMealPolicy' )->setMethod( 'getMealPolicyDefaultData' )
									->setSummary( 'Get default meal policy data used for creating new meal policies. Use this before calling setMealPolicy to get the correct default data.' ),
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
						10 => TTi18n::gettext( 'Auto-Deduct' ),
						15 => TTi18n::gettext( 'Auto-Add' ),
						20 => TTi18n::gettext( 'Normal' ),
				];
				break;
			case 'auto_detect_type':
				$retval = [
						10 => TTi18n::gettext( 'Time Window' ),
						20 => TTi18n::gettext( 'Punch Time (Proactive)' ), //Pro-actively attempts to detect lunch, required for Lunch reminders.
						25 => TTi18n::gettext( 'Punch Time (Reactive)' ), //Re-Actively detects lunch, therefore lunch reminders can't work unless the employee manually forces the punch to lunch.
				];
				break;
			case 'allocation_type':
				$retval = [
						10 => TTi18n::gettext( 'Proportional Distribution' ),
						100 => TTi18n::gettext( 'At Active After Time' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-type'         => TTi18n::gettext( 'Type' ),
						'-1020-name'         => TTi18n::gettext( 'Name' ),
						'-1025-description'  => TTi18n::gettext( 'Description' ),
						'-1030-amount'       => TTi18n::gettext( 'Meal Time' ),
						'-1040-trigger_time' => TTi18n::gettext( 'Active After' ),

						'-1050-auto_detect_type' => TTi18n::gettext( 'Auto Detect Meals By' ),
						//'-1060-start_window' => TTi18n::gettext('Start Window'),
						//'-1070-window_length' => TTi18n::gettext('Window Length'),
						//'-1080-minimum_punch_time' => TTi18n::gettext('Minimum Punch Time'),
						//'-1090-maximum_punch_time' => TTi18n::gettext('Maximum Punch Time'),

						'-1100-include_lunch_punch_time' => TTi18n::gettext( 'Include Lunch Punch' ),
						'-1200-allocation_type' => TTi18n::gettext( 'Allocation Type' ),

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
						'amount',
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
				'id'                       => 'ID',
				'company_id'               => 'Company',
				'type_id'                  => 'Type',
				'type'                     => false,
				'name'                     => 'Name',
				'description'              => 'Description',
				'trigger_time'             => 'TriggerTime',
				'amount'                   => 'Amount',
				'auto_detect_type_id'      => 'AutoDetectType',
				'auto_detect_type'         => false,
				'start_window'             => 'StartWindow',
				'window_length'            => 'WindowLength',
				'minimum_punch_time'       => 'MinimumPunchTime',
				'maximum_punch_time'       => 'MaximumPunchTime',
				'include_lunch_punch_time' => 'IncludeLunchPunchTime',
				'allocation_type_id'      => 'AllocationType',
				'allocation_type'         => false,

				'pay_code_id'           => 'PayCode',
				'pay_code'              => false,
				'pay_formula_policy_id' => 'PayFormulaPolicy',
				'pay_formula_policy'    => false,

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
	function getAmount() {
		return $this->getGenericDataValue( 'amount' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAmount( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'amount', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAutoDetectType() {
		return $this->getGenericDataValue( 'auto_detect_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAutoDetectType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'auto_detect_type_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAllocationType() {
		return $this->getGenericDataValue( 'allocation_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAllocationType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'allocation_type_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getStartWindow() {
		return $this->getGenericDataValue( 'start_window' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartWindow( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'start_window', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getWindowLength() {
		return $this->getGenericDataValue( 'window_length' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWindowLength( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'window_length', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getMinimumPunchTime() {
		return $this->getGenericDataValue( 'minimum_punch_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinimumPunchTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'minimum_punch_time', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getMaximumPunchTime() {
		return $this->getGenericDataValue( 'maximum_punch_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumPunchTime( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'maximum_punch_time', $value );
	}

	/*
		This takes into account any lunch punches when calculating the meal policy.
		If enabled for:
			Auto-Deduct:	It will only deduct the amount that is not taken in lunch time.
							So if they auto-deduct 60mins, and an employee takes 30mins of lunch,
							it will deduct the remaining 30mins to equal 60mins. If they don't
							take any lunch, it deducts the full 60mins.
			Auto-Include:	It will include the amount taken in lunch time, up to the amount given.
							So if they auto-include 30mins and an employee takes a 60min lunch
							only 30mins will be included, and 30mins is automatically deducted
							as a regular lunch punch.
							If they don't take a lunch, it doesn't include any time.

		If not enabled for:
		Auto-Deduct: Always deducts the amount.
		Auto-Inlcyde: Always includes the amount.
	*/
	/**
	 * @return bool
	 */
	function getIncludeLunchPunchTime() {
		return $this->fromBool( $this->getGenericDataValue( 'include_lunch_punch_time' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeLunchPunchTime( $value ) {
		return $this->setGenericDataValue( 'include_lunch_punch_time', $this->toBool( $value ) );
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
		// Trigger Time
		if ( $this->getTriggerTime() !== false ) {
			$this->Validator->isNumeric( 'trigger_time',
										 $this->getTriggerTime(),
										 TTi18n::gettext( 'Incorrect Trigger Time' )
			);
		}
		// Deduction Amount
		if ( $this->getAmount() !== false ) {
			$this->Validator->isNumeric( 'amount',
										 $this->getAmount(),
										 TTi18n::gettext( 'Incorrect Deduction Amount' )
			);
		}
		// Auto-Detect Type
		if ( $this->getAutoDetectType() !== false ) {
			$this->Validator->inArrayKey( 'auto_detect_type',
										  $this->getAutoDetectType(),
										  TTi18n::gettext( 'Incorrect Auto-Detect Type' ),
										  $this->getOptions( 'auto_detect_type' )
			);
		}
		// Allocation Type
		if ( $this->getAllocationType() !== false ) {
			$this->Validator->inArrayKey( 'allocation_type',
										  $this->getAllocationType(),
										  TTi18n::gettext( 'Incorrect Allocation Type' ),
										  $this->getOptions( 'allocation_type' )
			);
		}
		// Start Window
		if ( $this->getStartWindow() != '' ) {
			$this->Validator->isNumeric( 'start_window',
										 $this->getStartWindow(),
										 TTi18n::gettext( 'Incorrect Start Window' )
			);
		}
		// Window Length
		if ( $this->getWindowLength() != '' ) {
			$this->Validator->isNumeric( 'window_length',
										 $this->getWindowLength(),
										 TTi18n::gettext( 'Incorrect Window Length' )
			);
		}
		// Minimum Punch Time
		if ( $this->getMinimumPunchTime() != '' ) {
			$this->Validator->isNumeric( 'minimum_punch_time',
										 $this->getMinimumPunchTime(),
										 TTi18n::gettext( 'Incorrect Minimum Punch Time' )
			);
		}
		// Maximum Punch Time
		if ( $this->getMaximumPunchTime() != '' ) {
			$this->Validator->isNumeric( 'maximum_punch_time',
										 $this->getMaximumPunchTime(),
										 TTi18n::gettext( 'Incorrect Maximum Punch Time' )
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

		//
		// ABOVE: Validation code moved from set*() functions.
		//


		if ( $this->getDeleted() != true && $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing.
			if ( $ignore_warning == false ) { //Don't check the below when mass editing, but must check when adding a new record..
				if ( abs( $this->getAmount() ) > 9000 ) { //Breaks longer than 2.5hrs should trigger warning
					$this->Validator->Warning( 'amount', TTi18n::gettext( 'Meal Time may be too high' ) );
				}
			}

			if ( $this->getPayCode() == TTUUID::getZeroID() ) {
				$this->Validator->isTRUE( 'pay_code_id',
										  false,
										  TTi18n::gettext( 'Please choose a Pay Code' ) );
			}

			//Make sure Pay Formula Policy is defined somewhere.
			if ( $this->getPayFormulaPolicy() == TTUUID::getZeroID()
					&& ( TTUUID::isUUID( $this->getPayCode() ) && $this->getPayCode() != TTUUID::getZeroID() && $this->getPayCode() != TTUUID::getNotExistID() )
					&& ( !is_object( $this->getPayCodeObject() ) || ( is_object( $this->getPayCodeObject() ) && $this->getPayCodeObject()->getPayFormulaPolicy() == TTUUID::getZeroID() ) ) ) {
				$this->Validator->isTRUE( 'pay_formula_policy_id',
										  false,
										  TTi18n::gettext( 'Selected Pay Code does not have a Pay Formula Policy defined' ) );
			}
		}

		if ( $this->getDeleted() == true ) {
			//Check to make sure nothing else references this policy, so we can be sure its okay to delete it.
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), [ 'meal_policy' => $this->getId() ], 1 );
			if ( $pglf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This policy is currently in use' ) . ' ' . TTi18n::gettext( 'by policy groups' ) );
			}

			$splf = TTnew( 'SchedulePolicyListFactory' ); /** @var SchedulePolicyListFactory $splf */
			$splf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), [ 'meal_policy_id' => $this->getId() ], 1 );
			if ( $splf->getRecordCount() > 0 ) {
				$this->Validator->isTRUE( 'in_use',
										  false,
										  TTi18n::gettext( 'This policy is currently in use' ) . ' ' . TTi18n::gettext( 'by schedule policies' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getAutoDetectType() == false ) {
			$this->setAutoDetectType( 10 );
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
						case 'type':
						case 'auto_detect_type':
						case 'allocation_type':
							$function = 'get' . str_replace( '_', '', $variable );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Meal Policy' ) .': '. $this->getName(), null, $this->getTable(), $this );
	}
}

?>
