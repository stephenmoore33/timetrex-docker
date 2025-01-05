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
 * @package Modules\Schedule
 */
class RecurringScheduleControlFactory extends Factory {
	protected $table = 'recurring_schedule_control';
	protected $pk_sequence_name = 'recurring_schedule_control_id_seq'; //PK Sequence name

	protected $company_obj = null;
	protected $user_obj = null;
	protected $recurring_schedule_template_obj = null;
	protected $recurring_schedule_template_control_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'recurring_schedule_template_control_id' )->setFunctionMap( 'RecurringScheduleTemplateControl' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'start_week' )->setFunctionMap( 'StartWeek' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'start_date' )->setFunctionMap( 'StartDate' )->setType( 'date' )->setIsNull( false ),
							TTSCol::new( 'end_date' )->setFunctionMap( 'EndDate' )->setType( 'date' )->setIsNull( true ),
							TTSCol::new( 'auto_fill' )->setFunctionMap( 'AutoFill' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'display_weeks' )->setFunctionMap( 'DisplayWeeks' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_recurring_schedule' )->setLabel( TTi18n::getText( 'Recurring Schedule' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'recurring_schedule_template_control_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Template' ) )->setDataSource( TTSAPI::new( 'APIRecurringScheduleTemplate' )->setMethod( 'getAPIRecurringScheduleTemplate' ) ),
											TTSField::new( 'start_week' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Template Start Week' ) ),
											TTSField::new( 'start_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Start Date' ) ),
											TTSField::new( 'end_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'End Date' ) ),
											TTSField::new( 'display_weeks' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Display Weeks' ) ),
											TTSField::new( 'auto_fill' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Auto-Punch' ) ),
											TTSField::new( 'user_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Employees' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
									)
							),
					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'recurring_schedule_template_control_id' )->setType( 'uuid_list' )->setColumn( 'a.recurring_schedule_template_control_id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'b.status_id' )->setMulti( true ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'b.group_id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid_list' )->setColumn( 'b.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'b.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'b.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid_list' )->setColumn( 'b.title_id' )->setMulti( true ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'b.country' ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'b.province' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIRecurringScheduleControl' )->setMethod( 'getRecurringScheduleControl' )
									->setSummary( 'Get recurring schedule control records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIRecurringScheduleControl' )->setMethod( 'setRecurringScheduleControl' )
									->setSummary( 'Add or edit recurring schedule control records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIRecurringScheduleControl' )->setMethod( 'deleteRecurringScheduleControl' )
									->setSummary( 'Delete recurring schedule control records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIRecurringScheduleControl' )->setMethod( 'getRecurringScheduleControl' ) ),
											   ) ),
							TTSAPI::new( 'APIRecurringScheduleControl' )->setMethod( 'getRecurringScheduleControlDefaultData' )
									->setSummary( 'Get default recurring schedule control data used for creating new records. Use this before calling setRecurringScheduleControl to get the correct default data.' ),
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
			case 'columns':
				$retval = [
						'-1010-first_name' => TTi18n::gettext( 'First Name' ),
						'-1020-last_name'  => TTi18n::gettext( 'Last Name' ),

						'-1030-recurring_schedule_template_control'             => TTi18n::gettext( 'Template' ),
						'-1040-recurring_schedule_template_control_description' => TTi18n::gettext( 'Description' ),
						'-1050-start_date'                                      => TTi18n::gettext( 'Start Date' ),
						'-1060-end_date'                                        => TTi18n::gettext( 'End Date' ),
						'-1065-display_weeks'                                   => TTi18n::gettext( 'Display Weeks' ),
						'-1070-auto_fill'                                       => TTi18n::gettext( 'Auto-Punch' ),

						'-1090-title'              => TTi18n::gettext( 'Title' ),
						'-1099-user_group'         => TTi18n::gettext( 'Group' ),
						'-1100-default_branch'     => TTi18n::gettext( 'Branch' ),
						'-1110-default_department' => TTi18n::gettext( 'Department' ),

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
						'recurring_schedule_template_control',
						'recurring_schedule_template_control_description',
						'start_date',
						'end_date',
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
				'id'                                              => 'ID',
				'company_id'                                      => 'Company',
				'user_id'                                         => 'User',
				'first_name'                                      => false,
				'last_name'                                       => false,
				'default_branch'                                  => false,
				'default_department'                              => false,
				'user_group'                                      => false,
				'title'                                           => false,
				'recurring_schedule_template_control_id'          => 'RecurringScheduleTemplateControl',
				'recurring_schedule_template_control'             => false,
				'recurring_schedule_template_control_description' => false,
				'start_week'                                      => 'StartWeek',
				'start_date'                                      => 'StartDate',
				'end_date'                                        => 'EndDate',
				'display_weeks'                                   => 'DisplayWeeks',
				'auto_fill'                                       => 'AutoFill',
				'deleted'                                         => 'Deleted',
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
	function getRecurringScheduleTemplateControlObject() {
		return $this->getGenericObject( 'RecurringScheduleTemplateControlListFactory', $this->getRecurringScheduleTemplateControl(), 'recurring_schedule_template_control_obj' );
	}

	/**
	 * @return bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
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

		return $this->setGenericDataValue( 'company_id', $value );
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
	function getRecurringScheduleTemplateControl() {
		return $this->getGenericDataValue( 'recurring_schedule_template_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRecurringScheduleTemplateControl( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'recurring_schedule_template_control_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getStartWeek() {
		return $this->getGenericDataValue( 'start_week' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartWeek( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'start_week', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
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
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'start_date', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
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
	 * Get the maximum end date based on the current date and the display weeks, up to any specified end date that occurs before the display weeks.
	 * @param $current_epoch
	 * @return bool|float|int
	 */
	function getMaximumEndDate( $current_epoch ) {
		//Make sure its always at least the display weeks based on the end of the current week.
		$maximum_end_date = ( ( TTDate::getEndWeekEpoch( $current_epoch ) + 1 ) + ( $this->getDisplayWeeks() * ( 86400 * 7 ) ) - 1 );
		if ( $this->getEndDate() != '' && $maximum_end_date > $this->getEndDate() ) {
			$maximum_end_date = $this->getEndDate();
		}

		Debug::text( '  Maximum End Date: '. TTDate::getDate( 'DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10 );
		return $maximum_end_date;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'end_date', $value );
	}

	/**
	 * @return bool|int
	 */
	function getDisplayWeeks() {
		return $this->getGenericDataValue( 'display_weeks' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDisplayWeeks( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'display_weeks', $value );
	}

	/**
	 * @return bool
	 */
	function getAutoFill() {
		return $this->fromBool( $this->getGenericDataValue( 'auto_fill' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAutoFill( $value ) {
		return $this->setGenericDataValue( 'auto_fill', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Recurring Schedule Template
		if ( $this->getRecurringScheduleTemplateControl() !== false ) {
			$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' ); /** @var RecurringScheduleTemplateControlListFactory $rstclf */
			$this->Validator->isResultSetWithRows( 'recurring_schedule_template_control_id',
												   $rstclf->getByID( $this->getRecurringScheduleTemplateControl() ),
												   TTi18n::gettext( 'Recurring Schedule Template is invalid' )
			);
		}
		// Start week
		if ( $this->getStartWeek() !== false ) {
			$this->Validator->isGreaterThan( 'start_week',
											 $this->getStartWeek(),
											 TTi18n::gettext( 'Start week must be at least 1' ),
											 1
			);
			if ( $this->Validator->isError( 'start_week' ) == false ) {
				$this->Validator->isNumeric( 'start_week',
											 $this->getStartWeek(),
											 TTi18n::gettext( 'Start week is invalid' )
				);
			}
		}
		// Start date -- Must be specified to avoid SQL error.
		if ( $this->Validator->getValidateOnly() == false && $this->getStartDate() == '' ) {
			$this->Validator->isTRUE( 'start_date',
									  false,
									  TTi18n::gettext( 'Start Date must be specified' ) );
		}

		if ( $this->getStartDate() !== false ) {
			$this->Validator->isDate( 'start_date',
									  $this->getStartDate(),
									  TTi18n::gettext( 'Incorrect start date' )
			);
		}

		// End date
		if ( $this->getEndDate() != '' ) {
			$this->Validator->isDate( 'end_date',
									  $this->getEndDate(),
									  TTi18n::gettext( 'Incorrect end date' )
			);
		}
		if ( $this->getDeleted() == false && $this->Validator->isError( 'end_date' ) == false && $this->getEndDate() != '' && $this->getEndDate() < $this->getStartDate() ) {
			$this->Validator->isTRUE( 'end_date',
									  false,
									  TTi18n::gettext( 'End Date must be after start date' ) );
		}
		// Display Weeks
		if ( $this->Validator->getValidateOnly() == false || $this->getDisplayWeeks() !== false ) {
			$this->Validator->isGreaterThan( 'display_weeks',
											 $this->getDisplayWeeks(),
											 TTi18n::gettext( 'Display Weeks must be at least 1' ),
											 1
			);
			if ( $this->Validator->isError( 'display_weeks' ) == false ) {
				$this->Validator->isLessThan( 'display_weeks',
											  $this->getDisplayWeeks(),
											  TTi18n::gettext( 'Display Weeks cannot exceed 78' ),
											  78
				);
			}
			if ( $this->Validator->isError( 'display_weeks' ) == false ) {
				$this->Validator->isNumeric( 'display_weeks',
											 $this->getDisplayWeeks(),
											 TTi18n::gettext( 'Display weeks is invalid' )
				);
			}
		}

		if ( $this->getUser() == TTUUID::getNotExistID() ) {
			$this->Validator->isTRUE( 'user_id',
									  false,
									  TTi18n::gettext( 'Employee must be selected' ) );
		} else if ( $this->getUser() !== TTUUID::getZeroID() && $this->getUser() !== TTUUID::getNotExistID() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user_id',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Selected Employee is invalid' )
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getStartWeek() < 1 ) {
			$this->setStartWeek( 1 );
		}

		if ( $this->getDisplayWeeks() < 1 ) {
			$this->setDisplayWeeks( 1 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//Handle generating recurring schedule rows, so they are as real-time as possible.
		//In case an issue arises (like holiday not appearing or something) and they need to recalculate schedules, always start from the prior week.
		//  so we at least have a chance of recalculating retroactively to some degree.
		$current_epoch = TTDate::getBeginWeekEpoch( TTDate::incrementDate( TTDate::getBeginWeekEpoch( time() ), -1, 'day' ) );

		global $config_vars;

		if ( ( !isset( $config_vars['other']['enable_job_queue'] ) || $config_vars['other']['enable_job_queue'] == true ) && ( !defined( 'UNIT_TEST_MODE' ) || UNIT_TEST_MODE === false ) ) {
			SystemJobQueue::Add( TTi18n::getText( 'Recalculating Recurring Schedule' ), $this->getAPIMessageID(), 'RecurringScheduleFactory', 'recalculateRecurringScheduleForJobQueue', [ $this->getCompany(), $this->getID(), $this->getUser(), $current_epoch, $this->getMaximumEndDate( $current_epoch ), $this->getDeleted() ], 90 );
		} else {
			//
			//**THIS IS DONE IN recalculateRecurringScheduleForJobQueue, RecurringScheduleControlFactory, RecurringScheduleTemplateControlFactory, HolidayFactory postSave() as well.
			//

			$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
			$rsf->StartTransaction();
			$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $this->getID(), ( $current_epoch - ( 86400 * 720 ) ), ( $current_epoch + ( 86400 * 720 ) ) );

			//If a user has two templates assigned to them that happen to be conflicting with one another, and the recurring schedule for one is deleted, it could leave "blank" shifts on the days where the conflict was.
			//So when deleting recurring schedules OR when removing employees from recurring schedules, try to recalculate *all* recurring schedules of all the users that may be affected to ensure that any conflicting shifts will be recalculated.
			if ( $this->getDeleted() == true ) {
				$rsclf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
				$rsclf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCompany(), [ 'user_id' => [ $this->getUser() ] ] );
				if ( $rsclf->getRecordCount() > 0 ) {
					foreach ( $rsclf as $tmp_rsc_obj ) {
						if ( $this->getID() != $tmp_rsc_obj->getID() ) { //Don't recalculate the current recurring schedule record as thats already done above.
							$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $tmp_rsc_obj->getID(), ( $current_epoch - ( 86400 * 720 ) ), ( $current_epoch + ( 86400 * 720 ) ) );

							//Maximum date must be based on the recurring schedule control record we are recalculating and *not* the one we are deleting, otherwise the schedule will get out of sync.
							//FIXME: Put a cap on this perhaps, as 3mths into the future so we don't spend a ton of time doing this
							//if the user sets it to display 1-2yrs in the future. Leave creating the rest of the rows to the maintenance job?
							//Since things may change we will want to delete all schedules with each change, but only add back in X weeks at most unless from a maintenance job.
							Debug::text( 'Recalculating Recurring Schedule ID: ' . $tmp_rsc_obj->getID() . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $tmp_rsc_obj->getMaximumEndDate( $current_epoch ) ), __FILE__, __LINE__, __METHOD__, 10 );
							$rsf->addRecurringSchedulesFromRecurringScheduleControl( $tmp_rsc_obj->getCompany(), $tmp_rsc_obj->getID(), $current_epoch, $tmp_rsc_obj->getMaximumEndDate( $current_epoch ) );
						} else {
							Debug::text( '  Skipping recalculating ourself again...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				}
			} else {
				Debug::text( 'Recurring Schedule ID: ' . $this->getID() . ' Start Date: '. TTDate::getDate( 'DATE+TIME', $current_epoch ) .' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $this->getMaximumEndDate( $current_epoch ) ), __FILE__, __LINE__, __METHOD__, 10 );
				$rsf->addRecurringSchedulesFromRecurringScheduleControl( $this->getCompany(), $this->getID(), $current_epoch, $this->getMaximumEndDate( $current_epoch ) );
			}

			$rsf->CommitTransaction();
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
						case 'start_date':
						case 'end_date':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
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
		//
		//When using the Recurring Schedule view, it returns the user list for every single row and runs out of memory at about 1000 rows.
		//Need to make the 'user' column explicitly defined instead perhaps?
		//
		$variable_function_map = $this->getVariableToFunctionMap();
		$data = [];
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
							$data[$variable] = ( $this->getColumn( $variable ) == '' ) ? TTi18n::getText( 'OPEN' ) : $this->getColumn( $variable );
							break;
						case 'title':
						case 'user_group':
						case 'default_branch':
						case 'default_department':
						case 'recurring_schedule_template_control':
						case 'recurring_schedule_template_control_description':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'start_date':
						case 'end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->$function() ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}

			//Handle expanded and non-expanded mode. In non-expanded mode we need to get all the users
			//so we can check is_owner/is_child permissions on them.
			if ( $this->getColumn( 'user_id' ) !== false ) {
				$user_ids = $this->getColumn( 'user_id' );
			} else {
				$user_ids = $this->getUser();
			}

			$this->getPermissionColumns( $data, $user_ids, $this->getCreatedBy(), $permission_children_ids, $include_columns );
			//$this->getPermissionColumns( $data, $this->getColumn('user_id'), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		if ( is_object( $this->getUserObject() ) ) {
			$user_full_name = $this->getUserObject()->getFullName();
		} else {
			if ( $this->getUser() == TTUUID::getZeroID() ) {
				$user_full_name = TTi18n::getText( 'OPEN' );
			} else {
				$user_full_name = TTi18n::getText( 'N/A' );
			}
		}
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Recurring Schedule - Employee' ) .': '. $user_full_name, null, $this->getTable(), $this );
	}
}

?>
