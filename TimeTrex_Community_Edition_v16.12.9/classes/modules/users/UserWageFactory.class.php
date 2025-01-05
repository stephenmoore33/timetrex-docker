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
 * @package Modules\Users
 */
class UserWageFactory extends Factory {
	protected $table = 'user_wage';
	protected $pk_sequence_name = 'user_wage_id_seq'; //PK Sequence name

	var $user_obj = null;
	var $labor_standard_obj = null;
	var $holiday_obj = null;
	var $wage_group_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			//This should be able to replace: getVariableToFunctionMap -- and handle most of getObjectAsArray/setObjectFromArray
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),

							TTSCol::new( 'first_name' )->setObjectAsArrayFunction( 'getColumn' )->setIsSynthetic( true ),
							TTSCol::new( 'last_name' )->setObjectAsArrayFunction( 'getColumn' )->setIsSynthetic( true ),

							TTSCol::new( 'wage_group_id' )->setFunctionMap( 'WageGroup' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'wage_group' )->setObjectAsArrayFunction( 'getColumn' )->setIsSynthetic( true ),

							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'type' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'currency_symbol' )->setObjectAsArrayFunction( 'getColumn' )->setIsSynthetic( true ),

							TTSCol::new( 'wage' )->setFunctionMap( 'Wage' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'hourly_rate' )->setFunctionMap( 'HourlyRate' )->setType( 'numeric' ),
							TTSCol::new( 'labor_burden_hourly_rate' )->setFunctionMap( 'LaborBurdenHourlyRate' )->setType( 'numeric' )->setIsSynthetic( true ),
							TTSCol::new( 'weekly_time' )->setFunctionMap( 'WeeklyTime' )->setType( 'integer' ),
							TTSCol::new( 'labor_burden_percent' )->setFunctionMap( 'LaborBurdenPercent' )->setType( 'numeric' ),
							TTSCol::new( 'effective_date' )->setFunctionMap( 'EffectiveDate' )->setType( 'date' ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'text' ),

							TTSCol::new( 'default_branch' )->setObjectAsArrayFunction( 'getColumn' )->setIsSynthetic( true ),
							TTSCol::new( 'default_department' )->setObjectAsArrayFunction( 'getColumn' )->setIsSynthetic( true ),
							TTSCol::new( 'user_group' )->setObjectAsArrayFunction( 'getColumn' )->setIsSynthetic( true ),
							TTSCol::new( 'title' )->setObjectAsArrayFunction( 'getColumn' )->setIsSynthetic( true ),
					)->addPermission( 'getUser', 'getCreatedBy' )->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_wage' )->setLabel( TTi18n::getText( 'Wage') )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'user_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Employee') )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'wage_group_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Wage Group') )->setDataSource( TTSAPI::new( 'APIWageGroup' )->setMethod( 'getWageGroup' ) ),
											TTSField::new( 'type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Type') )->setDataSource( TTSAPI::new( 'APIUserWage' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'wage' )->setType( 'text' )->setLabel( TTi18n::getText( 'Wage') )->setWidth( 90 ),
											TTSField::new( 'weekly_time' )->setType( 'text' )->setLabel( TTi18n::getText( 'Average Time / Week') )->setSubLabel( '( '. TTi18n::getText( 'ie' ) .': '. TTi18n::getText( '40 hours / week' ) . ' )' )->setWidth( 90 ),
											TTSField::new( 'hourly_rate' )->setType( 'text' )->setLabel( TTi18n::getText( 'Annual Hourly Rate') )->setWidth( 90 ),
											TTSField::new( 'labor_burden_percent' )->setType( 'text' )->setLabel( TTi18n::getText( 'Labor Burden Percent') )->setSubLabel( '% ( '.  TTi18n::getText( 'ie' ) .': '. TTi18n::getText( '25% burden' ) . ' )' )->setWidth( 90 ),
											TTSField::new( 'effective_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Effective Date' ) )->setWidth( 120 ),
											TTSField::new( 'note' )->setType( 'textarea' )->setLabel( TTi18n::getText( 'Note' ) )->setWidth( '100%' ),
									)
							),
					)->addAttachment()->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid' )->setColumn( 'a.user_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'type_id' )->setType( 'integer' )->setColumn( 'a.type_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'wage_group_id' )->setType( 'uuid' )->setColumn( 'a.wage_group_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'b.status_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid' )->setColumn( 'b.group_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid' )->setColumn( 'b.default_branch_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid' )->setColumn( 'b.default_department_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid' )->setColumn( 'b.title_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'country' )->setType( 'varchar' )->setColumn( 'b.country' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'province' )->setType( 'varchar' )->setColumn( 'b.province' )->setVisible( 'AI', true ),
					) );
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							//TODO: Look into expanding how description on how get/set work with effective date and making a new record and not edit old when giving a wage. (only editing when correction)
							TTSAPI::new( 'APIUserWage' )->setMethod( 'getUserWage' )
									->setSummary( 'Get employee wage records. Each employee can have multiple wage records, each with a different effective date. If multiple records are returned, then the wage record with the most recent effective date is the current wage.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' )
									->setModelKeywords( 'raise' ),
							TTSAPI::new( 'APIUserWage' )->setMethod( 'setUserWage' )
									->setSummary( 'Add or edit employee wage records. Each employee can have multiple wage records, each with a different effective date. To give an employee a raise you must first get the employees current wage with getUserWage and then create a new record instead of editing a previous record. Make sure to use the correct type_id and keep the same type_id if giving a wage change. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] )
									->setModelKeywords( 'raise' ),
							TTSAPI::new( 'APIUserWage' )->setMethod( 'getUserWageDefaultData' )
									->setSummary( 'Get default user wage data used for creating new user wages. Use this before calling setUserWage to get the correct default data.' ),
							TTSAPI::new( 'APIUserWage' )->setMethod( 'deleteUserWage' )
									->setSummary( 'Delete employee wage records by passing in an array of UUID\'s.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserWage' )->setMethod( 'getUserWage' ) ),
											   ) ),
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
						10 => TTi18n::gettext( 'Hourly' ),
						12 => TTi18n::gettext( 'Salary (Weekly)' ),
						13 => TTi18n::gettext( 'Salary (Bi-Weekly)' ),
						15 => TTi18n::gettext( 'Salary (Monthly)' ),
						20 => TTi18n::gettext( 'Salary (Annual)' ),
						//											30	=> TTi18n::gettext('Min. Wage + Bonus (Salary)')
				];
				break;
			case 'columns':
				$retval = [
						'-1010-employee_number' => TTi18n::gettext( 'Employee #' ),
						'-1020-first_name' => TTi18n::gettext( 'First Name' ),
						'-1025-last_name'  => TTi18n::gettext( 'Last Name' ),

						'-1030-wage_group'     => TTi18n::gettext( 'Wage Group' ),
						'-1040-type'           => TTi18n::gettext( 'Type' ),
						'-1050-wage'           => TTi18n::gettext( 'Wage' ),
						'-1060-effective_date' => TTi18n::gettext( 'Effective Date' ),

						'-1070-hourly_rate'          => TTi18n::gettext( 'Hourly Rate' ),
						'-1070-labor_burden_percent' => TTi18n::gettext( 'Labor Burden Percent' ),
						'-1080-weekly_time'          => TTi18n::gettext( 'Average Time/Week' ),

						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-user_group'         => TTi18n::gettext( 'Group' ),
						'-1100-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1110-default_department' => TTi18n::gettext( 'Department' ),

						'-1290-note' => TTi18n::gettext( 'Note' ),

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
						'first_name',
						'last_name',
						'wage_group',
						'type',
						'wage',
						'effective_date',
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
				'user_id'                  => 'User',
				'employee_number'          => false,
				'first_name'               => false,
				'last_name'                => false,
				'wage_group_id'            => 'WageGroup',
				'wage_group'               => false,
				'type_id'                  => 'Type',
				'type'                     => false,
				'currency_symbol'          => false,
				'wage'                     => 'Wage',
				'hourly_rate'              => 'HourlyRate',
				'labor_burden_hourly_rate' => 'LaborBurdenHourlyRate',
				'weekly_time'              => 'WeeklyTime',
				'labor_burden_percent'     => 'LaborBurdenPercent',
				'effective_date'           => 'EffectiveDate',
				'enable_recalculate_timesheets' => 'EnableRecalculateTimeSheets',
				'note'                     => 'Note',

				'default_branch'     => false,
				'default_department' => false,
				'user_group'         => false,
				'title'              => false,

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool|null
	 */
	function getWageGroupObject() {
		if ( is_object( $this->wage_group_obj ) ) {
			return $this->wage_group_obj;
		} else {

			$wglf = TTnew( 'WageGroupListFactory' ); /** @var WageGroupListFactory $wglf */
			$wglf->getById( $this->getWageGroup() );

			if ( $wglf->getRecordCount() == 1 ) {
				$this->wage_group_obj = $wglf->getCurrent();

				return $this->wage_group_obj;
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
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
	function getWageGroup() {
		return $this->getGenericDataValue( 'wage_group_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setWageGroup( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Wage Group ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'wage_group_id', $value );
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
	 * @return bool|float
	 */
	function getWage() {
		return (float)$this->getGenericDataValue( 'wage' ); //Needs to return float so TTi18n::NumberFormat() can always handle it properly.
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWage( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'wage', $value );
	}

	/**
	 * @return bool|float
	 */
	function getHourlyRate() {
		return (float)$this->getGenericDataValue( 'hourly_rate' ); //Needs to return float so TTi18n::NumberFormat() can always handle it properly.
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHourlyRate( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'hourly_rate', $value );
	}

	/**
	 * @return bool
	 */
	function getWeeklyTime() {
		return $this->getGenericDataValue( 'weekly_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWeeklyTime( $value ) {
		return $this->setGenericDataValue( 'weekly_time', $value );
	}

	/**
	 * @return bool|float
	 */
	function getLaborBurdenPercent() {
		return (float)$this->getGenericDataValue( 'labor_burden_percent' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLaborBurdenPercent( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'labor_burden_percent', $value );
	}


	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function isValidEffectiveDate( $epoch ) {
		//Check to see if this is the first default wage entry, or if we are editing the first record.
		if ( TTUUID::isUUID( $this->getWageGroup() ) && $this->getWageGroup() != TTUUID::getZeroID() ) { //If we aren't the default wage group, return valid always.
			return true;
		}

		$must_validate = false;

		$uwlf = TTnew( 'UserWageListFactory' ); /** @var UserWageListFactory $uwlf */
		$uwlf->getByUserIdAndGroupIDAndBeforeDate( $this->getUser(), TTUUID::getZeroID(), $epoch, 1, null, null, [ 'effective_date' => 'asc' ] );
		Debug::text( ' Total Rows: ' . $uwlf->getRecordCount() . ' User: ' . $this->getUser() . ' Effective Date: ' . TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $uwlf->getRecordCount() <= 1 ) {
			//If it returns one row, we need to check to see if the returned row is the current record.
			if ( $uwlf->getRecordCount() == 0 ) {
				$must_validate = true;
			} else if ( $uwlf->getRecordCount() == 1 && $this->isNew() == false ) {
				//Check to see if we are editing the current record.
				if ( is_object( $uwlf->getCurrent() ) && $this->getId() == $uwlf->getCurrent()->getId() ) {
					$must_validate = true;
				} else {
					$must_validate = false;
				}
			}
		}

		if ( $must_validate == true ) {
			if ( is_object( $this->getUserObject() ) && $this->getUserObject()->getHireDate() != '' ) {
				//User has hire date, make sure its before or equal to the first wage effective date.
				if ( $epoch <= $this->getUserObject()->getHireDate() ) {
					return true;
				} else {
					return false;
				}
			}
		}

		return true;
	}

	/**
	 * @param int $effective_date EPOCH
	 * @return bool
	 */
	function isUniqueEffectiveDate( $effective_date ) {
		$ph = [
				'user_id'        => TTUUID::castUUID( $this->getUser() ),
				'wage_group_id'  => TTUUID::castUUID( $this->getWageGroup() ),
				'effective_date' => $this->db->BindDate( $effective_date ),
		];

		$query = 'select id from ' . $this->getTable() . ' where user_id = ? AND wage_group_id = ? AND effective_date = ? AND deleted = 0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique Wage Entry: Effective Date: ' . $effective_date, __FILE__, __LINE__, __METHOD__, 10 );

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
	 * @param bool $raw
	 * @return bool
	 */
	function getEffectiveDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'effective_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				if ( !is_numeric( $value ) ) {                                         //Optimization to avoid converting it when run in CalculatePolicy's loops
					$value = TTDate::strtotime( $value ); //Make sure we use middle day epoch when pulling the value from the DB the first time, to match setDateStamp() below. Otherwise setting the datestamp then getting it again before save won't match the same value after its saved to the DB.
					$this->setGenericDataValue( 'effective_date', $value );
				}

				return $value;
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEffectiveDate( $value ) {
		return $this->setGenericDataValue( 'effective_date', TTDate::getISODateStamp( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getNote() {
		return $this->getGenericDataValue( 'note' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNote( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'note', $value );
	}

	/**
	 * @param bool $rate
	 * @return float
	 */
	function getLaborBurdenHourlyRate( $rate = false ) {
		if ( $rate == '' ) {
			$rate = $this->getHourlyRate();
		}
		$hourly_wage = TTMath::mul( $rate, TTMath::add( TTMath::div( $this->getLaborBurdenPercent(), 100 ), 1 ) );

		$retval = TTMath::MoneyRound( $hourly_wage, 2, ( ( is_object( $this->getUserObject() ) && is_object( $this->getUserObject()->getCurrencyObject() ) ) ? $this->getUserObject()->getCurrencyObject() : null ) );

		//return Misc::MoneyRound($hourly_wage);
		//Format in APIUserWage() instead, as this gets passed back into setHourlyRate() and if in a locale that use comma decimal symbol, it will fail.

		return $retval;
	}

	/**
	 * @return bool
	 */
	function getEnableRecalculateTimeSheets() {
		return $this->getGenericTempDataValue( 'enable_recalculate_timesheets' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableRecalculateTimeSheets( $value ) {
		return $this->setGenericTempDataValue( 'enable_recalculate_timesheets', (bool)$value );
	}

	/**
	 * @param $rate
	 * @return bool|float|int
	 */
	function getBaseCurrencyHourlyRate( $rate ) {
		if ( $rate == '' ) {
			return false;
		}

		if ( !is_object( $this->getUserObject() ) ) {
			return false;
		}

		$clf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $clf */
		$clf->getByCompanyIdAndBase( $this->getUserObject()->getCompany(), true );
		if ( $clf->getRecordCount() > 0 ) {
			$base_currency_obj = $clf->getCurrent();

			//If current currency is the base currency, just return the rate.
			if ( $base_currency_obj->getId() == $this->getUserObject()->getCurrency() ) {
				return $rate;
			} else {
				//Debug::text(' Base Currency Rate: '. $base_currency_obj->getConversionRate() .' Hourly Rate: '. $rate, __FILE__, __LINE__, __METHOD__, 10);
				return CurrencyFactory::convertCurrency( $this->getUserObject()->getCurrency(), $base_currency_obj->getId(), $rate );
			}
		}

		return false;
	}

	/**
	 * @return bool|int|string
	 */
	function getAnnualWage() {
		$annual_wage = 0;

		//Debug::text(' Type: '. $this->getType() .' Wage: '. $this->getWage(), __FILE__, __LINE__, __METHOD__, 10);
		switch ( $this->getType() ) {
			case 10: //Hourly
				//Hourly wage type, can't have an annual wage.
				$annual_wage = 0;
				break;
			case 12: //Salary (Weekly)
				$annual_wage = TTMath::mul( $this->getWage(), 52 );
				break;
			case 13: //Salary (Bi-Weekly)
				$annual_wage = TTMath::mul( $this->getWage(), 26 );
				break;
			case 15: //Salary (Monthly)
				$annual_wage = TTMath::mul( $this->getWage(), 12 );
				break;
			case 20: //Salary (Annual)
				$annual_wage = $this->getWage();
				break;
		}

		return $annual_wage;
	}

	/**
	 * @param bool $epoch
	 * @param bool $accurate_calculation
	 * @return float
	 */
	function calcHourlyRate( $epoch = false, $accurate_calculation = false ) {
		if ( $this->getType() == 10 ) {
			$hourly_wage = $this->getWage();
		} else {
			$hourly_wage = $this->getAnnualHourlyRate( $this->getAnnualWage(), $epoch, $accurate_calculation );
		}

		//Use Misc::MoneyRound() still even though we are casting to float(), this just gets rid of extra decimals beyond what we want.
		//  It can still return something like (float)42.3, which still needs to be formatted of course.
		$retval = (float)TTMath::MoneyRound( $hourly_wage, 2, ( ( is_object( $this->getUserObject() ) && is_object( $this->getUserObject()->getCurrencyObject() ) ) ? $this->getUserObject()->getCurrencyObject() : null ) );

		//return Misc::MoneyRound($hourly_wage);
		//Format in APIUserWage() instead, as this gets passed back into setHourlyRate() and if in a locale that use comma decimal symbol, it will fail.

		return $retval;
	}

	/**
	 * @param $annual_wage
	 * @param bool $epoch
	 * @param bool $accurate_calculation
	 * @return bool|int|string
	 */
	function getAnnualHourlyRate( $annual_wage, $epoch = false, $accurate_calculation = false ) {
		if ( $epoch == false ) {
			$epoch = TTDate::getTime();
		}

		if ( $annual_wage == '' ) {
			return false;
		}

		if ( $accurate_calculation == true ) {
			Debug::text( 'EPOCH: ' . $epoch, __FILE__, __LINE__, __METHOD__, 10 );

			$annual_week_days = TTDate::getAnnualWeekDays( $epoch );
			Debug::text( 'Annual Week Days: ' . $annual_week_days, __FILE__, __LINE__, __METHOD__, 10 );

			//Calculate weeks from adjusted annual weekdays
			//We could use just 52 weeks in a year, but that isn't as accurate.
			$annual_work_weeks = TTMath::div( $annual_week_days, 5 );
			Debug::text( 'Adjusted annual work weeks : ' . $annual_work_weeks, __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			$annual_work_weeks = 52;
		}

		$average_weekly_hours = TTDate::getHours( $this->getWeeklyTime() );
		//Debug::text('Average Weekly Hours: '. $average_weekly_hours, __FILE__, __LINE__, __METHOD__, 10);

		if ( $average_weekly_hours == 0 ) {
			//No default schedule, can't pay them.
			$hourly_wage = 0;
		} else {
			//Divide by average hours/day from default schedule?
			$hours_per_year = TTMath::mul( $annual_work_weeks, $average_weekly_hours );
			if ( $hours_per_year > 0 ) {
				$hourly_wage = TTMath::div( $annual_wage, $hours_per_year );
			}
			unset( $hours_per_year );
		}
		//Debug::text('User Wage: '. $this->getWage(), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Annual Hourly Rate: '. $hourly_wage, __FILE__, __LINE__, __METHOD__, 10);

		return $hourly_wage;
	}

	/**
	 * @param $salary
	 * @param int $wage_effective_date      EPOCH
	 * @param int $prev_wage_effective_date EPOCH
	 * @param int $pp_start_date            EPOCH
	 * @param int $pp_end_date              EPOCH
	 * @param bool $hire_date
	 * @param bool $termination_date
	 * @return int|string
	 */
	static function proRateSalary( $salary, $wage_effective_date, $prev_wage_effective_date, $pp_start_date, $pp_end_date, $hire_date = false, $termination_date = false ) {
		$pro_rate_dates_arr = self::proRateSalaryDates( $wage_effective_date, $prev_wage_effective_date, $pp_start_date, $pp_end_date, $hire_date, $termination_date );
		if ( is_array( $pro_rate_dates_arr ) ) {
			Debug::text( 'Salary: ' . $salary . ' Total Pay Period Days: ' . $pro_rate_dates_arr['total_pay_period_days'] . ' Wage Effective Days: ' . $pro_rate_dates_arr['total_wage_effective_days'], __FILE__, __LINE__, __METHOD__, 10 );
			$pro_rate_salary = TTMath::mul( $salary, TTMath::div( $pro_rate_dates_arr['total_wage_effective_days'], $pro_rate_dates_arr['total_pay_period_days'] ) );
		}

		//Final sanaity checks.
		if ( $pro_rate_salary < 0 ) {
			$pro_rate_salary = 0;
		} else if ( $pro_rate_salary > $salary ) {
			$pro_rate_salary = $salary;
		}
		Debug::text( 'Pro Rate Salary: ' . $pro_rate_salary, __FILE__, __LINE__, __METHOD__, 10 );

		return $pro_rate_salary;
	}

	/**
	 * @param int $wage_effective_date      EPOCH
	 * @param int $prev_wage_effective_date EPOCH
	 * @param int $pp_start_date            EPOCH
	 * @param int $pp_end_date              EPOCH
	 * @param bool $hire_date
	 * @param bool $termination_date
	 * @return array
	 */
	static function proRateSalaryDates( $wage_effective_date, $prev_wage_effective_date, $pp_start_date, $pp_end_date, $hire_date = false, $termination_date = false ) {
		$prev_wage_effective_date = (int)$prev_wage_effective_date;

		if ( $wage_effective_date < $pp_start_date ) {
			$wage_effective_date = $pp_start_date;
		}

		if ( $wage_effective_date < $hire_date ) {
			$wage_effective_date = TTDate::getBeginDayEpoch( $hire_date );
		}

		$total_pay_period_days = ceil( TTDate::getDayDifference( $pp_start_date, $pp_end_date ) );

		$retarr = [];

		$retarr['total_pay_period_days'] = $total_pay_period_days;
		if ( $prev_wage_effective_date == 0 ) {
			//ProRate salary to termination date if its in the middle of a pay period. Be sure to assume termination date is at the end of the day (inclusive), not beginning.
			if ( $termination_date != '' && $termination_date > 0 && TTDate::getMiddleDayEpoch( $termination_date ) < TTDate::getMiddleDayEpoch( $pp_end_date ) ) {
				//Debug::text(' Setting PP end date to Termination Date: '. TTDate::GetDate('DATE', $termination_date), __FILE__, __LINE__, __METHOD__, 10);
				$pp_end_date = TTDate::getEndDayEpoch( $termination_date );
			}
			$total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date, $pp_end_date ) );

			//Debug::text(' Using Pay Period End Date: '. TTDate::GetDate('DATE', $pp_end_date), __FILE__, __LINE__, __METHOD__, 10);
			$retarr['start_date'] = $wage_effective_date;
			$retarr['end_date'] = $pp_end_date;
		} else {
			$total_wage_effective_days = ceil( TTDate::getDayDifference( $wage_effective_date, $prev_wage_effective_date ) );

			//Debug::text(' Using Prev Effective Date: '. TTDate::GetDate('DATE', $prev_wage_effective_date ), __FILE__, __LINE__, __METHOD__, 10);
			$retarr['start_date'] = $wage_effective_date;
			$retarr['end_date'] = $prev_wage_effective_date;
		}
		$retarr['total_wage_effective_days'] = $total_wage_effective_days;

		if ( $total_wage_effective_days == $total_pay_period_days ) {
			$retarr['percent'] = 100;
		} else {
			$retarr['percent'] = TTMath::removeTrailingZeros( round( TTMath::mul( TTMath::div( $total_wage_effective_days, $total_pay_period_days ), 100 ), 2 ), 0 );
		}

		//Always need to return an array of dates so proRateSalary() above can use them. However in order to know if any prorating is done or not, we need to return 'percent' = 100 or not.
		return $retarr;
	}

	/**
	 * @param int $date EPOCH
	 * @param $wage_arr
	 * @return bool|mixed
	 */
	static function getWageFromArray( $date, $wage_arr ) {
		if ( !is_array( $wage_arr ) ) {
			return false;
		}

		if ( $date == '' ) {
			return false;
		}

		//Debug::Arr($wage_arr, 'Wage Array: ', __FILE__, __LINE__, __METHOD__, 10);

		foreach ( $wage_arr as $effective_date => $wage ) {
			if ( $effective_date <= $date ) {
				Debug::Text( 'Effective Date: ' . TTDate::getDate( 'DATE+TIME', $effective_date ) . ' Is Less Than: ' . TTDate::getDate( 'DATE+TIME', $date ), __FILE__, __LINE__, __METHOD__, 10 );

				return $wage;
			}
		}

		return false;
	}

	/**
	 * Takes the employees
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @return bool|string
	 */
	static function calculateLaborBurdenPercent( $company_id, $user_id ) {
		if ( $company_id == '' ) {
			return false;
		}
		if ( $user_id == '' ) {
			return false;
		}

		$end_epoch = TTDate::getTime();
		$start_epoch = ( TTDate::getTime() - ( 86400 * 180 ) ); //6mths

		$retval = false;

		$pseallf = TTnew( 'PayStubEntryAccountLinkListFactory' ); /** @var PayStubEntryAccountLinkListFactory $pseallf */
		$pseallf->getByCompanyID( $company_id );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pself = TTnew( 'PayStubEntryListFactory' ); /** @var PayStubEntryListFactory $pself */
			$total_gross = $pself->getAmountSumByUserIdAndEntryNameIdAndStartDateAndEndDate( $user_id, $pseallf->getCurrent()->getTotalGross(), $start_epoch, $end_epoch );
			$total_employer_deductions = $pself->getAmountSumByUserIdAndEntryNameIdAndStartDateAndEndDate( $user_id, $pseallf->getCurrent()->getTotalEmployerDeduction(), $start_epoch, $end_epoch );

			if ( isset( $total_employer_deductions['amount'] ) && isset( $total_gross['amount'] ) ) {
				$retval = TTMath::mul( TTMath::div( $total_employer_deductions['amount'], $total_gross['amount'] ), 100, 2 );
			}
		}

		return $retval;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		if ( $this->getType() == 10 ) { //Hourly
			$this->setWeeklyTime( null );
			$this->setHourlyRate( $this->getWage() ); //Match hourly rate to wage.
		} else {
			//Salary wage types

			//Need to handle case where the user intentionally specifies 0hrs/week and 0 hourly rate.
			// To handle importing salary user wages without any hours/hourly rate specified, that is done in ImportUserWage instead.
			if ( $this->getWeeklyTime() === '' ) {
				$this->setWeeklyTime( 0 );
			}

			if ( $this->getHourlyRate() === '' || $this->getHourlyRate() <= 0 ) {
				$this->setHourlyRate( $this->calcHourlyRate( $this->getWeeklyTime() ) ); //Calculate hourly rate if its not specified.
			}
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
		// Employee
		if ( $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing, but must check when adding a new record..
			if ( $this->getUser() == '' || $this->getUser() == TTUUID::getZeroID() ) {
				$this->Validator->isTRUE( 'user_id',
										  false,
										  TTi18n::gettext( 'No employee specified' )
				);
			}
		}
		if ( $this->getUser() !== false ) {
			if ( $this->Validator->isError( 'user_id' ) == false ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'user_id',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}
		}
		// Group
		if ( $this->getWageGroup() !== false && $this->getWageGroup() != TTUUID::getZeroID() ) {
			$wglf = TTnew( 'WageGroupListFactory' ); /** @var WageGroupListFactory $wglf */
			$this->Validator->isResultSetWithRows( 'wage_group_id',
												   $wglf->getByID( $this->getWageGroup() ),
												   TTi18n::gettext( 'Group is invalid' )
			);
		}
		// Type
		if ( $this->Validator->getValidateOnly() == false || $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}

		// Wage
		$this->Validator->isFloat( 'wage',
								   $this->getWage(),
								   TTi18n::gettext( 'Incorrect Wage' )
		);

		if ( $this->Validator->isError( 'wage' ) == false ) {
			$this->Validator->isLength( 'wage',
										$this->getWage(),
										TTi18n::gettext( 'Wage has too many digits' ),
										0,
										21
			); //Need to include decimal.
		}
		if ( $this->Validator->isError( 'wage' ) == false ) {
			$this->Validator->isLengthBeforeDecimal( 'wage',
													 $this->getWage(),
													 TTi18n::gettext( 'Wage has too many digits before the decimal' ),
													 0,
													 16
			);
		}
		if ( $this->Validator->isError( 'wage' ) == false ) {
			$this->Validator->isLengthAfterDecimal( 'wage',
													$this->getWage(),
													TTi18n::gettext( 'Wage has too many digits after the decimal' ),
													0,
													4
			);
		}
		// Hourly Rate
		if ( $this->getHourlyRate() != '' ) {
			$this->Validator->isFloat( 'hourly_rate',
									   $this->getHourlyRate(),
									   TTi18n::gettext( 'Incorrect Hourly Rate' )
			);
		}
		// Weekly Time
		if ( $this->getWeeklyTime() != '' ) {
			$this->Validator->isNumeric( 'weekly_time',
										 $this->getWeeklyTime(),
										 TTi18n::gettext( 'Incorrect Weekly Time' )
			);
		}
		// Labor Burden Percent
		$this->Validator->isFloat( 'labor_burden_percent',
								   $this->getLaborBurdenPercent(),
								   TTi18n::gettext( 'Incorrect Labor Burden Percent' )
		);
		// Effective Date
		if ( $this->Validator->getValidateOnly() == false || $this->getEffectiveDate() !== false ) { //Ensure an effective date is always specified, but handle mass editing properly too.
			$this->Validator->isDate( 'effective_date',
									  $this->getEffectiveDate(),
									  TTi18n::gettext( 'Incorrect Effective Date' )
			);
			if ( $this->Validator->isError( 'effective_date' ) == false ) {
				$this->Validator->isTrue( 'effective_date',
										  $this->isUniqueEffectiveDate( $this->getEffectiveDate() ),
										  TTi18n::gettext( 'Employee already has a wage entry on this date for the same wage group. Try using a different date instead' )
				);
			}
		}

		// Note
		if ( $this->getNote() != '' ) {
			$this->Validator->isLength( 'note',
										$this->getNote(),
										TTi18n::gettext( 'Note is too long' ),
										1,
										2048
			);

			$this->Validator->isHTML( 'note',
									  $this->getNote(),
									  TTi18n::gettext( 'Note contains invalid special characters' ),
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $ignore_warning == false && $this->Validator->getValidateOnly() == false ) { //Don't check the below when mass editing, but must check when adding a new record..
			if ( $this->getWage() <= 1 ) {
				$this->Validator->Warning( 'wage', TTi18n::gettext( 'Wage may be too low' ) );
			}

			if ( $this->getType() != 10 ) { //Salary
				//Make sure they won't put 0 or 1hr for the weekly time, as that is almost certainly wrong.
				if ( $this->getWeeklyTime() <= 3601 ) {
					$this->Validator->Warning( 'weekly_time', TTi18n::gettext( 'Average Time / Week may be too low, a proper estimated time is critical even for salary wages' ) );
				}

				//Make sure the weekly total time is within reason and hourly rates aren't 1000+/hr.
				if ( $this->getHourlyRate() <= 1 ) {
					$this->Validator->Warning( 'hourly_rate', TTi18n::gettext( 'Annual Hourly Rate may be too low, a proper hourly rate is critical even for salary wages' ) );
				}
				if ( is_object( $this->getUserObject() )
						&& is_object( $this->getUserObject()->getCurrencyObject() )
						&& in_array( $this->getUserObject()->getCurrencyObject()->getISOCode(), [ 'USD', 'CAD', 'EUR' ] )
						&& $this->getHourlyRate() > 500 ) {
					$this->Validator->Warning( 'hourly_rate', TTi18n::gettext( 'Annual Hourly Rate may be too high, a proper hourly rate is critical even for salary wages' ) );
				}
			}

			//If the wage record is added at noon on the hire date, and the employee has already punched in/out and finished their shift, still need to show this warning.
			if ( $this->getEnableRecalculateTimeSheets() == false && TTDate::getMiddleDayEpoch( $this->getEffectiveDate() ) <= TTDate::getMiddleDayEpoch( time() ) ) {
				$this->Validator->Warning( 'effective_date', TTi18n::gettext( 'When changing wages retroactively, you may need to recalculate this employees timesheet for the affected pay period(s)' ) );
			}
		}
		if ( $this->getDeleted() == false ) {
			if ( is_object( $this->getUserObject() ) && $this->getUserObject()->getHireDate() ) {
				$hire_date = $this->getUserObject()->getHireDate();
			} else {
				$hire_date = null;
			}

			//NOTE: Since we don't handle records in a batch (like we do for punches) when calling APIWage->setUserWage(),
			//  if trying to import wage history for employees the end-user will see error messages like:
			// 	   "An employees first wage entry must be effective on or before the employees hire date"
			//  If they just continue, it will import properly as long as the records are in the proper order.
			//  This occurs because during the import wizard during the test/validation step each record is rolled back individually, not as a batch.
			//     So when importing the 2nd record the 1st record doesn't exist, so it will complain the effective date has to be on the hire date as it thinks every record is the first wage record.
			//     When doing the real import though, the 1st record gets committed and will exist and it will work fine.
			$this->Validator->isTrue( 'effective_date',
									  $this->isValidEffectiveDate( $this->getEffectiveDate() ),
									  TTi18n::gettext( 'An employees first wage entry must be effective on or before the employees hire date' ) . ' (' . TTDate::getDate( 'DATE', $hire_date ) . ')' );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( $this->getId() . $this->getUser() ); //Used in some reports.

		if ( $this->getEnableRecalculateTimeSheets() == true ) {
			//Get open pay periods since wage effective date, and recalculate them.
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $ppf */
			$pplf->getByUserIdAndOverlapStartDateAndEndDate( $this->getUser(), $this->getEffectiveDate(), TTDate::incrementDate( time(), 1, 'year' ) );
			if ( $pplf->getRecordCount() ) {
				foreach( $pplf as $pp_obj ) {
					if ( $pp_obj->getIsLocked() == false ) {
						Debug::text( '   Recalculating open Pay Period TimeSheet: User ID: '. $this->getUser() .' Start Date: '. TTDate::getDate('DATE+TIME', $pp_obj->getStartDate() ) .' End Date: '. TTDate::getDate('DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );
						SystemJobQueue::Add( TTi18n::getText( 'ReCalculating TimeSheet' ), $this->getId(), 'CalculatePolicy', 'reCalculateForJobQueue', [ $this->getUser(), 'APITimeSheet', $pp_obj->getStartDate(), $pp_obj->getEndDate() ], 30 );
					} else {
						Debug::text( '   NOT Recalculating locked Pay Period TimeSheet: User ID: '. $this->getUser() .' Start Date: '. TTDate::getDate('DATE+TIME', $pp_obj->getStartDate() ) .' End Date: '. TTDate::getDate('DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			}
			unset( $pplf, $pp_obj );
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
						case 'effective_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						case 'hourly_rate':
						case 'wage':
						case 'labor_burden_percent':
							$this->$function( TTi18n::parseFloat( $data[$key] ) );
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
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'wage_group':
						case 'employee_number':
						case 'first_name':
						case 'last_name':
						case 'title':
						case 'user_group':
						case 'currency':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'currency_symbol':
							$data[$variable] = TTi18n::getCurrencySymbol( $this->getColumn( 'iso_code' ) );
							break;
						case 'effective_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'hourly_rate':
						case 'wage':
							//$data[$variable] = TTi18n::formatNumber( $this->$function(), TRUE, 2, 4 ); //Don't format numbers here, as it could break scripts using the API.
							$data[$variable] = TTMath::removeTrailingZeros( $this->$function(), 2 );
							break;
						case 'labor_burden_percent':
							//$data[$variable] = TTi18n::formatNumber( $this->$function(), TRUE, 0, 4 ); //Don't format numbers here, as it could break scripts using the API.
							$data[$variable] = TTMath::removeTrailingZeros( $this->$function(), 0 );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	function getObjectAsArrayColumn( string $column, $data ) {
		switch ( $column ) {
			case 'currency_symbol':
				$retval = TTi18n::getCurrencySymbol( $this->getColumn( 'iso_code' ) );
				break;
			case 'effective_date':
				$retval = TTDate::getAPIDate( 'DATE', $data );
				break;
			case 'hourly_rate':
			case 'wage':
				//$retval = TTi18n::formatNumber( $this->$function(), TRUE, 2, 4 ); //Don't format numbers here, as it could break scripts using the API.
				$retval = Misc::removeTrailingZeros( $data, 2 );
				break;
			case 'labor_burden_percent':
				//$retval = TTi18n::formatNumber( $this->$function(), TRUE, 0, 4 ); //Don't format numbers here, as it could break scripts using the API.
				$retval = Misc::removeTrailingZeros( $data, 0 );
				break;
			default:
				$retval = $data;
				break;
		}

		return $retval;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object( $u_obj ) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee Wage' ) . ': ' . $u_obj->getFullName( false, true ), null, $this->getTable(), $this );
		}

		return false;
	}
}

?>
