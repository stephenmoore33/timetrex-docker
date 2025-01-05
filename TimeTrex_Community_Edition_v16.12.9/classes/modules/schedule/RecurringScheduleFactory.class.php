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
class RecurringScheduleFactory extends Factory {
	protected $table = 'recurring_schedule';
	protected $pk_sequence_name = 'recurring_schedule_id_seq'; //PK Sequence name

	protected $user_obj = null;
	protected $user_date_obj = null;
	protected $schedule_policy_obj = null;
	protected $absence_policy_obj = null;
	protected $branch_obj = null;
	protected $department_obj = null;
	protected $pay_period_schedule_obj = null;

	protected $json_columns = [ 'punch_tag_id' ];

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'recurring_schedule_control_id' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'date_stamp' )->setFunctionMap( 'DateStamp' )->setType( 'date' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'start_time' )->setFunctionMap( 'StartTime' )->setType( 'timestamptz' )->setIsNull( false ),
							TTSCol::new( 'end_time' )->setFunctionMap( 'EndTime' )->setType( 'timestamptz' )->setIsNull( false ),
							TTSCol::new( 'schedule_policy_id' )->setFunctionMap( 'SchedulePolicyID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'absence_policy_id' )->setFunctionMap( 'AbsencePolicyID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'branch_id' )->setFunctionMap( 'Branch' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'department_id' )->setFunctionMap( 'Department' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'job_id' )->setFunctionMap( 'Job' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'job_item_id' )->setFunctionMap( 'JobItem' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'total_time' )->setFunctionMap( 'TotalTime' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'recurring_schedule_template_control_id' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'auto_fill' )->setFunctionMap( 'AutoFill' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'created_date' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'created_by' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'updated_date' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'updated_by' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'deleted_date' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'deleted_by' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'deleted' )->setFunctionMap( 'Deleted' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'other_id1' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'other_id2' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'other_id3' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'other_id4' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'other_id5' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'punch_tag_id' )->setType( 'jsonb' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_recurring_schedule' )->setLabel( TTi18n::getText( 'Recurring Schedule' ) )->setFields(
									new TTSFields(
											TTSField::new( 'recurring_schedule_template_control_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Template' ) ),
											TTSField::new( 'start_week' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Template Start Week' ) ),
											TTSField::new( 'start_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Start Date' ) ),
											TTSField::new( 'end_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'End Date' ) ),
											TTSField::new( 'display_weeks' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Display Weeks' ) ),
											TTSField::new( 'auto_fill' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Auto-Punch' ) ),
											TTSField::new( 'user_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Employees' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
									)
							),
							TTSTab::new( 'tab_audit' )->setLabel( TTi18n::getText( 'Audit' ) )->setInitCallback( 'initSubLogView' )->setDisplayOnMassEdit( false ),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'user_id' )->setType( 'uuid' )->setColumn( 'a.user_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_user_id' )->setType( 'uuid' )->setColumn( 'a.user_id' )->setMulti( true ),

							TTSSearchField::new( 'user_status_id' )->setType( 'integer' )->setColumn( 'd.status_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid' )->setColumn( 'd.legal_entity_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'group_id' )->setType( 'uuid' )->setColumn( 'd.group_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid' )->setColumn( 'd.default_branch_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid' )->setColumn( 'd.default_department_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid' )->setColumn( 'd.title_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'branch_id' )->setType( 'uuid' )->setColumn( 'a.branch_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'department_id' )->setType( 'uuid' )->setColumn( 'a.department_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'a.status_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'schedule_policy_id' )->setType( 'uuid' )->setColumn( 'a.schedule_policy_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'absence_policy_id' )->setType( 'uuid' )->setColumn( 'a.absence_policy_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'include_job_id' )->setType( 'uuid' )->setColumn( 'a.job_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_job_id' )->setType( 'uuid' )->setColumn( 'a.job_id' )->setMulti( true ),
							TTSSearchField::new( 'job_group_id' )->setType( 'uuid' )->setColumn( 'w.group_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'job_item_id' )->setType( 'uuid' )->setColumn( 'a.job_item_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'date_stamp' )->setType( 'date' )->setColumn( 'a.date_stamp' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'start_date' )->setType( 'date' )->setColumn( 'a.date_stamp' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'end_date' )->setType( 'date' )->setColumn( 'a.date_stamp' )->setVisible( 'AI', true ),

							TTSSearchField::new( 'start_time' )->setType( 'time' )->setColumn( 'a.start_time' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'end_time' )->setType( 'time' )->setColumn( 'a.end_time' )->setVisible( 'AI', true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIRecurringSchedule' )->setMethod( 'getRecurringSchedule' )
									->setSummary( 'Get recurring schedule records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIRecurringSchedule' )->setMethod( 'setRecurringSchedule' )
									->setSummary( 'Add or edit recurring schedule records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIRecurringSchedule' )->setMethod( 'deleteRecurringSchedule' )
									->setSummary( 'Delete recurring schedule records by passing in an array of UUID\'s.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIRecurringSchedule' )->setMethod( 'getRecurringSchedule' ) ),
											   ) ),
							TTSAPI::new( 'APIRecurringSchedule' )->setMethod( 'getRecurringScheduleDefaultData' )
									->setSummary( 'Get default recurring schedule data used for creating new recurring schedules. Use this before calling setRecurringSchedule to get the correct default data.' ),
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

		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
					//If user_id = 0 then the schedule is assumed to be open. That way its easy to assign recurring schedules
					//to user_id=0 for open shifts too.
					10 => TTi18n::gettext( 'Working' ),
					20 => TTi18n::gettext( 'Absent' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
						'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
						'-1005-user_status'        => TTi18n::gettext( 'Employee Status' ),
						'-1010-title'              => TTi18n::gettext( 'Title' ),
						'-1039-group'              => TTi18n::gettext( 'Group' ),
						'-1040-default_branch'     => TTi18n::gettext( 'Default Branch' ),
						'-1050-default_department' => TTi18n::gettext( 'Default Department' ),
						'-1160-branch'             => TTi18n::gettext( 'Branch' ),
						'-1170-department'         => TTi18n::gettext( 'Department' ),
						'-1200-status'             => TTi18n::gettext( 'Status' ),
						'-1210-schedule_policy'    => TTi18n::gettext( 'Schedule Policy' ),
						'-1212-absence_policy'     => TTi18n::gettext( 'Absence Policy' ),
						'-1215-date_stamp'         => TTi18n::gettext( 'Date' ),
						'-1220-start_time'         => TTi18n::gettext( 'Start Time' ),
						'-1230-end_time'           => TTi18n::gettext( 'End Time' ),
						'-1240-total_time'         => TTi18n::gettext( 'Total Time' ),
						'-1250-note'               => TTi18n::gettext( 'Note' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];

				if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
					$retval['-1180-job'] = TTi18n::gettext( 'Job' );
					$retval['-1190-job_item'] = TTi18n::gettext( 'Task' );
				}
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'status',
						'date_stamp',
						'start_time',
						'end_time',
						'total_time',
				];
				break;
			case 'group_columns': //Columns available for grouping on the schedule.
				$retval = [
						'title',
						'group',
						'default_branch',
						'default_department',
						'branch',
						'department',
				];

				if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
					$retval[] = 'job';
					$retval[] = 'job_item';
				}
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
				'id'                    => 'ID',
				'company_id'            => 'Company',
				'user_id'               => 'User',
				'date_stamp'            => 'DateStamp',

				//'user_id' => FALSE,
				'first_name'            => false,
				'last_name'             => false,
				'user_status_id'        => false,
				'user_status'           => false,
				'group_id'              => false,
				'group'                 => false,
				'title_id'              => false,
				'title'                 => false,
				'default_branch_id'     => false,
				'default_branch'        => false,
				'default_department_id' => false,
				'default_department'    => false,

				//'date_stamp' => FALSE,
				'start_date_stamp'      => false,
				'pay_period_id'         => false,
				'status_id'             => 'Status',
				'status'                => false,
				'start_date'            => false,
				'end_date'              => false,
				'start_time_stamp'      => false,
				'end_time_stamp'        => false,
				'start_time'            => 'StartTime',
				'end_time'              => 'EndTime',
				'schedule_policy_id'    => 'SchedulePolicyID',
				'schedule_policy'       => false,
				'absence_policy_id'     => 'AbsencePolicyID',
				'absence_policy'        => false,
				'branch_id'             => 'Branch',
				'branch'                => false,
				'department_id'         => 'Department',
				'department'            => false,
				'job_id'                => 'Job',
				'job'                   => false,
				'job_item_id'           => 'JobItem',
				'job_item'              => false,
				'punch_tag_id'          => 'PunchTag',
				'total_time'            => 'TotalTime',

				'note'      => 'Note',
				'auto_fill' => 'AutoFill',

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
	 * @return bool
	 */
	function getPayPeriodObject() {
		return $this->getGenericObject( 'PayPeriodListFactory', $this->getPayPeriod(), 'pay_period_obj' );
	}

	/**
	 * @return bool
	 */
	function getSchedulePolicyObject() {
		return $this->getGenericObject( 'SchedulePolicyListFactory', $this->getSchedulePolicyID(), 'schedule_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getAbsencePolicyObject() {
		return $this->getGenericObject( 'AbsencePolicyListFactory', $this->getAbsencePolicyID(), 'absence_policy_obj' );
	}

	/**
	 * @return bool
	 */
	function getBranchObject() {
		return $this->getGenericObject( 'BranchListFactory', $this->getBranch(), 'branch_obj' );
	}

	/**
	 * @return bool
	 */
	function getDepartmentObject() {
		return $this->getGenericObject( 'DepartmentListFactory', $this->getDepartment(), 'department_obj' );
	}

	/**
	 * @return bool|null
	 */
	function getPayPeriodScheduleObject() {
		if ( is_object( $this->pay_period_schedule_obj ) ) {
			return $this->pay_period_schedule_obj;
		} else {
			if ( TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID() ) {
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
				$ppslf->getByUserId( $this->getUser() );
				if ( $ppslf->getRecordCount() == 1 ) {
					$this->pay_period_schedule_obj = $ppslf->getCurrent();

					return $this->pay_period_schedule_obj;
				}
			} else if ( $this->getUser() == TTUUID::getZeroID() && $this->getCompany() != TTUUID::getZeroID() ) {
				//OPEN SHIFT, try to find pay period schedule for the company
				$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
				$ppslf->getByCompanyId( $this->getCompany() );
				if ( $ppslf->getRecordCount() == 1 ) {
					Debug::Text( 'Using Company ID: ' . $this->getCompany(), __FILE__, __LINE__, __METHOD__, 10 );
					$this->pay_period_schedule_obj = $ppslf->getCurrent();

					return $this->pay_period_schedule_obj;
				}
			}

			return false;
		}
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

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getRecurringScheduleControl() {
		return $this->getGenericDataValue( 'recurring_schedule_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setRecurringScheduleControl( $value ) {
		$value = TTUUID::castUUID( $value );

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		return $this->setGenericDataValue( 'recurring_schedule_control_id', $value );
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

		//Need to be able to support user_id=0 for open shifts. But this can cause problems with importing punches with user_id=0.
		return $this->setGenericDataValue( 'recurring_schedule_template_control_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
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
	function setDateStamp( $value ) {
		$value = (int)$value;
		if ( $value > 0 ) {
			$value = TTDate::getMiddleDayEpoch( $value );
		}

		return $this->setGenericDataValue( 'date_stamp', $value );
	}

	/**
	 * FIXME: The problem with assigning schedules to other dates than what they start on, is that employees can get confused
	 *          as to what day their shift actually starts on, especially when looking at iCal schedules, or printed schedules.
	 *          It can even be different for some employees if they are assigned to other pay period schedules.
	 *          However its likely they may already know this anyways, due to internal termination, if they call a Monday shift one that starts Sunday night for example.
	 * @return bool
	 */
	function findUserDate() {
		//Must allow user_id=0 for open shifts.

		/*
		This needs to be able to run before Validate is called, so we can validate the pay period schedule.
		*/
		if ( $this->getDateStamp() == false ) {
			$this->setDateStamp( $this->getStartTime() );
		}

		//Debug::Text(' Finding User Date ID: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ) .' User: '. $this->getUser(), __FILE__, __LINE__, __METHOD__, 10);
		if ( is_object( $this->getPayPeriodScheduleObject() ) ) {
			$user_date_epoch = $this->getPayPeriodScheduleObject()->getShiftAssignedDate( $this->getStartTime(), $this->getEndTime(), $this->getPayPeriodScheduleObject()->getShiftAssignedDay() );
		} else {
			$user_date_epoch = $this->getStartTime();
		}

		if ( isset( $user_date_epoch ) && $user_date_epoch > 0 ) {
			//Debug::Text('Found DateStamp: '. $user_date_epoch .' Based On: '. TTDate::getDate('DATE+TIME', $user_date_epoch ), __FILE__, __LINE__, __METHOD__, 10);

			return $this->setDateStamp( $user_date_epoch );
		}

		Debug::Text( 'Not using timestamp only: ' . TTDate::getDate( 'DATE+TIME', $this->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getStartTime( $raw = false ) {
		$value = $this->getGenericDataValue( 'start_time' );
		if ( $value !== false ) {
			return TTDate::strtotime( $value );
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setStartTime( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'start_time', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getEndTime( $raw = false ) {
		$value = $this->getGenericDataValue( 'end_time' );
		if ( $value !== false ) {
			return TTDate::strtotime( $value );
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setEndTime( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'end_time', $value );
	}

	/**
	 * @param $day_total_time
	 * @param bool $filter_type_id
	 * @return int
	 */
	function getMealPolicyDeductTime( $day_total_time, $filter_type_id = false ) {
		$total_time = 0;

		$mplf = TTnew( 'MealPolicyListFactory' ); /** @var MealPolicyListFactory $mplf */
		if ( is_object( $this->getSchedulePolicyObject() ) && $this->getSchedulePolicyObject()->isUsePolicyGroupMealPolicy() == false ) {
			$policy_group_meal_policy_ids = $this->getSchedulePolicyObject()->getMealPolicy();
			$mplf->getByIdAndCompanyId( $policy_group_meal_policy_ids, $this->getCompany() );
		} else {
			$mplf->getByPolicyGroupUserId( $this->getUser() );
		}

		//Debug::Text('Meal Policy Record Count: '. $mplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $mplf->getRecordCount() > 0 ) {
			foreach ( $mplf as $meal_policy_obj ) {
				if ( ( $filter_type_id == false && ( $meal_policy_obj->getType() == 10 || $meal_policy_obj->getType() == 20 ) )
						||
						( $filter_type_id == $meal_policy_obj->getType() )
				) {
					if ( $day_total_time > $meal_policy_obj->getTriggerTime() ) {
						$total_time = $meal_policy_obj->getAmount(); //Only consider a single meal policy per shift, so don't add here.
					}
				}
			}
		}

		$total_time = ( $total_time * -1 );
		Debug::Text( 'Meal Policy Deduct Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10 );

		return $total_time;
	}

	/**
	 * @param $day_total_time
	 * @param bool $filter_type_id
	 * @return int
	 */
	function getBreakPolicyDeductTime( $day_total_time, $filter_type_id = false ) {
		$total_time = 0;

		$bplf = TTnew( 'BreakPolicyListFactory' ); /** @var BreakPolicyListFactory $bplf */
		if ( is_object( $this->getSchedulePolicyObject() ) && $this->getSchedulePolicyObject()->isUsePolicyGroupBreakPolicy() == false ) {
			$policy_group_break_policy_ids = $this->getSchedulePolicyObject()->getBreakPolicy();
			$bplf->getByIdAndCompanyId( $policy_group_break_policy_ids, $this->getCompany() );
		} else {
			$bplf->getByPolicyGroupUserId( $this->getUser() );
		}

		//Debug::Text('Break Policy Record Count: '. $bplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $bplf->getRecordCount() > 0 ) {
			foreach ( $bplf as $break_policy_obj ) {
				if ( ( $filter_type_id == false && ( $break_policy_obj->getType() == 10 || $break_policy_obj->getType() == 20 ) )
						||
						( $filter_type_id == $break_policy_obj->getType() )
				) {
					if ( $day_total_time > $break_policy_obj->getTriggerTime() ) {
						$total_time += $break_policy_obj->getAmount();
					}
				}
			}
		}

		$total_time = ( $total_time * -1 );
		Debug::Text( 'Break Policy Deduct Time: ' . $total_time, __FILE__, __LINE__, __METHOD__, 10 );

		return $total_time;
	}

	/**
	 * @return bool|int
	 */
	function calcRawTotalTime() {
		if ( $this->getStartTime() > 0 && $this->getEndTime() > 0 ) {
			//Due to DST, always pay the employee based on the time they actually worked,
			//which is handled automatically by simple epoch math.
			//Therefore in fall they get paid one hour more, and spring one hour less.
			$total_time = ( $this->getEndTime() - $this->getStartTime() ); // + TTDate::getDSTOffset( $this->getStartTime(), $this->getEndTime() );
			//Debug::Text('Start Time '.TTDate::getDate('DATE+TIME', $this->getStartTime()) .'('.$this->getStartTime().')  End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().') Total Time: '. TTDate::getHours( $total_time ), __FILE__, __LINE__, __METHOD__, 10);

			return $total_time;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function calcTotalTime() {
		if ( $this->getStartTime() > 0 && $this->getEndTime() > 0 ) {
			$total_time = $this->calcRawTotalTime();

			$total_time += $this->getMealPolicyDeductTime( $total_time );
			$total_time += $this->getBreakPolicyDeductTime( $total_time );

			return $total_time;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getTotalTime() {
		return $this->getGenericDataValue( 'total_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTotalTime( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'total_time', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getSchedulePolicyID() {
		return $this->getGenericDataValue( 'schedule_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setSchedulePolicyID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'schedule_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getAbsencePolicyID() {
		return $this->getGenericDataValue( 'absence_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setAbsencePolicyID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'absence_policy_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getBranch() {
		return $this->getGenericDataValue( 'branch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setBranch( $value ) {
		$value = TTUUID::castUUID( $value );

		//Allow setting to Default (-1) so we can update it in real-time for the regular schedule view.
		return $this->setGenericDataValue( 'branch_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDepartment() {
		return $this->getGenericDataValue( 'department_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDepartment( $value ) {
		$value = TTUUID::castUUID( $value );

		//Allow setting to Default (-1) so we can update it in real-time for the regular schedule view.
		return $this->setGenericDataValue( 'department_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getJob() {
		return $this->getGenericDataValue( 'job_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setJob( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

		//Allow setting to Default (-1) so we can update it in real-time for the regular schedule view.
		return $this->setGenericDataValue( 'job_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getJobItem() {
		return $this->getGenericDataValue( 'job_item_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setJobItem( $value ) {
		$value = TTUUID::castUUID( $value );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

		//Allow setting to Default (-1) so we can update it in real-time for the regular schedule view.
		return $this->setGenericDataValue( 'job_item_id', $value );
	}

	/**
	 * @return array
	 */
	function getPunchTag() {
		//Always return an array.
		$this->decodeJSONColumn( 'punch_tag_id' );
		$value = $this->getGenericDataValue( 'punch_tag_id' );

		if ( $value == false ) {
			return [];
		}

		return $value;
	}

	/**
	 * @param array $value UUID
	 * @return bool
	 */
	function setPunchTag( $value ) {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = null;
		}

		if ( $value == TTUUID::getZeroID() || empty( $value ) || ( is_array( $value ) && count( $value ) == 1 && isset( $value[0] ) && $value[0] == TTUUID::getZeroID() ) ) {
			$value = null;
		}

		if ( !is_array( $value ) && TTUUID::isUUID( $value ) ) {
			$value = [ $value ];
		}

		return $this->setGenericDataValue( 'punch_tag_id', $value );
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
	 * Find the difference between $epoch and the schedule time, so we can determine the best schedule that fits.
	 * *NOTE: This returns FALSE when it doesn't match, so make sure you do an exact comparison using ===
	 * @param int $epoch EPOCH
	 * @param bool $status_id
	 * @return bool|int
	 */
	function inScheduleDifference( $epoch, $status_id = false ) {
		if ( $epoch >= $this->getStartTime() && $epoch <= $this->getEndTime() ) {
			Debug::text( 'Within Schedule: ' . $epoch, __FILE__, __LINE__, __METHOD__, 10 );

			//Need to handle two schedule shifts like: Feb 15th: 7A - 7A (24hr shift), then a Feb 16th: 7A - 7P (12hr shift immediately after)
			// If its a OUT punch on Feb 16th at 7A then it should match the first schedule, and if its an IN punch on Feb 16th, it should match the 2nd schedule.
			if ( ( $status_id == 10 && $epoch == $this->getEndTime() ) || ( $status_id == 20 && $epoch == $this->getStartTime() ) ) {
				$retval = 0.5; //Epoch matches exact start/end schedule time, but the status doesn't quite match, so make it slightly more than 0 in case there is an exact match.
			} else {
				$retval = 0; //Within schedule start/end time, no difference.
			}
		} else {
			if ( ( $status_id == false || $status_id == 10 ) && $epoch < $this->getStartTime() && $this->inStartWindow( $epoch ) ) {
				$retval = ( $this->getStartTime() - $epoch );
			} else if ( ( $status_id == false || $status_id == 20 ) && $epoch > $this->getEndTime() && $this->inStopWindow( $epoch ) ) {
				$retval = ( $epoch - $this->getEndTime() );
			} else {
				$retval = false; //Not within start/stop window at all, return FALSE.
			}
		}

		//Debug::text('Difference from schedule: "'. $retval .'" Epoch: '. $epoch .' Status: '. $status_id, __FILE__, __LINE__, __METHOD__, 10);
		return $retval;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function inSchedule( $epoch ) {
		if ( $epoch >= $this->getStartTime() && $epoch <= $this->getEndTime() ) {
			Debug::text( 'aWithin Schedule: ' . $epoch, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		} else if ( $this->inStartWindow( $epoch ) || $this->inStopWindow( $epoch ) ) {
			Debug::text( 'bWithin Schedule: ' . $epoch, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function inStartWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $epoch == '' ) {
			return false;
		}

		if ( is_object( $this->getSchedulePolicyObject() ) ) {
			$start_stop_window = (int)$this->getSchedulePolicyObject()->getStartStopWindow();
		} else {
			$start_stop_window = ( 3600 * 2 ); //Default to 2hr to help avoid In Late exceptions when they come in too early.
		}

		if ( $epoch >= ( $this->getStartTime() - $start_stop_window ) && $epoch <= ( $this->getStartTime() + $start_stop_window ) ) {
			Debug::text( ' Within Start/Stop window: ' . $start_stop_window, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		//Debug::text(' NOT Within Start window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function inStopWindow( $epoch ) {
		//Debug::text(' Epoch: '. $epoch, __FILE__, __LINE__, __METHOD__, 10);

		if ( $epoch == '' ) {
			return false;
		}

		if ( is_object( $this->getSchedulePolicyObject() ) ) {
			$start_stop_window = (int)$this->getSchedulePolicyObject()->getStartStopWindow();
		} else {
			$start_stop_window = ( 3600 * 2 ); //Default to 2hr
		}

		if ( $epoch >= ( $this->getEndTime() - $start_stop_window ) && $epoch <= ( $this->getEndTime() + $start_stop_window ) ) {
			Debug::text( ' Within Start/Stop window: ' . $start_stop_window, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		//Debug::text(' NOT Within Stop window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return false;
	}

	/**
	 * @return bool
	 */
	function handleDayBoundary() {
		//Debug::Text('Start Time '.TTDate::getDate('DATE+TIME', $this->getStartTime()) .'('.$this->getStartTime().')  End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().')', __FILE__, __LINE__, __METHOD__, 10);

		//This used to be done in Validate, but needs to be done in preSave too.
		//Allow 12:00AM to 12:00AM schedules for a total of 24hrs.
		if ( $this->getStartTime() != '' && $this->getEndTime() != '' && $this->getEndTime() <= $this->getStartTime() ) {
			//Since the initial end time is the same date as the start time, we need to see if DST affects between that end time and one day later. NOT the start time.
			//Due to DST, always pay the employee based on the time they actually worked,
			//which is handled automatically by simple epoch math.
			//Therefore in fall they get paid one hour more, and spring one hour less.
			//$this->setEndTime( $this->getEndTime() + ( 86400 + (TTDate::getDSTOffset( $this->getEndTime(), ($this->getEndTime() + 86400) ) ) ) ); //End time spans midnight, add 24hrs.
			$this->setEndTime( strtotime( '+1 day', $this->getEndTime() ) ); //Using strtotime handles DST properly, whereas adding 86400 causes strange behavior.
			Debug::Text( 'EndTime spans midnight boundary! Bump to next day... New End Time: ' . TTDate::getDate( 'DATE+TIME', $this->getEndTime() ) . '(' . $this->getEndTime() . ')', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function isConflicting() {
		Debug::Text( 'User ID: ' . $this->getUser() . ' DateStamp: ' . $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );
		//Make sure we're not conflicting with any other schedule shifts.
		$rslf = TTnew( 'RecurringScheduleListFactory' ); /** @var RecurringScheduleListFactory $rslf */
		$rslf->getConflictingByCompanyIdAndUserIdAndStartDateAndEndDate( $this->getCompany(), $this->getUser(), $this->getStartTime(), $this->getEndTime(), TTUUID::castUUID( $this->getID() ) );
		if ( $rslf->getRecordCount() > 0 ) {
			foreach ( $rslf as $conflicting_schedule_shift_obj ) {
				if ( $conflicting_schedule_shift_obj->isNew() === false
						&& $conflicting_schedule_shift_obj->getId() != $this->getId() ) {
					Debug::text( 'Conflicting Schedule Shift ID: ' . $conflicting_schedule_shift_obj->getId() . ' Schedule Shift ID: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function Validate() {
		if ( $this->getDeleted() == false ) {
			//
			// BELOW: Validation code moved from set*() functions.
			//

			// Company
			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$this->Validator->isResultSetWithRows( 'company',
												   $clf->getByID( $this->getCompany() ),
												   TTi18n::gettext( 'Company is invalid' )
			);
			// User
			if ( $this->getUser() != TTUUID::getZeroID() ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'user',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}
			// Recurring Schedule
			if ( $this->getRecurringScheduleControl() != TTUUID::getZeroID() ) {
				$rsclf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
				$this->Validator->isResultSetWithRows( 'recurring_schedule_control_id',
													   $rsclf->getByID( $this->getRecurringScheduleControl() ),
													   TTi18n::gettext( 'Invalid Recurring Schedule' )
				);
			}
			// Recurring Schedule Template
			if ( $this->getRecurringScheduleTemplateControl() != TTUUID::getZeroID() ) {
				$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' ); /** @var RecurringScheduleTemplateControlListFactory $rstclf */
				$this->Validator->isResultSetWithRows( 'recurring_schedule_template_control_id',
													   $rstclf->getByID( $this->getRecurringScheduleTemplateControl() ),
													   TTi18n::gettext( 'Invalid Recurring Schedule Template' )
				);
			}
			// Date
			if ( $this->getDateStamp() != '' ) {
				$this->Validator->isDate( 'date_stamp',
										  $this->getDateStamp(),
										  TTi18n::gettext( 'Incorrect date' ) . '(a)'
				);
				if ( $this->Validator->isError( 'date_stamp' ) == false ) {
					if ( $this->getDateStamp() <= 0 ) {
						$this->Validator->isTRUE( 'date_stamp',
												  false,
												  TTi18n::gettext( 'Incorrect date' ) . '(b)'
						);
					}
				}
			}
			// Status
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
			// Start time
			$this->Validator->isDate( 'start_time',
									  $this->getStartTime(),
									  TTi18n::gettext( 'Incorrect start time' )
			);
			// End Time
			$this->Validator->isDate( 'end_time',
									  $this->getEndTime(),
									  TTi18n::gettext( 'Incorrect end time' )
			);
			if ( $this->Validator->isError( 'end_time' ) == false && $this->getEndTime() < $this->getStartTime() ) {
				$this->Validator->isTRUE( 'end_time',
										  false,
										  TTi18n::gettext( 'End time must be after start time' ) );
			}
			// Total time
			if ( $this->getTotalTime() != '' ) {
				$this->Validator->isNumeric( 'total_time',
											 $this->getTotalTime(),
											 TTi18n::gettext( 'Incorrect total time' )
				);
				if ( $this->Validator->isError( 'total_time' ) == false ) {
					$this->Validator->isGreaterThan( 'total_time',
													 $this->getTotalTime(),
													 TTi18n::gettext( 'Total Time must be greater than 0' ),
													 0
					);
				}
			}
			// Schedule Policy
			if ( $this->getSchedulePolicyID() != TTUUID::getZeroID() ) {
				$splf = TTnew( 'SchedulePolicyListFactory' ); /** @var SchedulePolicyListFactory $splf */
				$this->Validator->isResultSetWithRows( 'schedule_policy',
													   $splf->getByID( $this->getSchedulePolicyID() ),
													   TTi18n::gettext( 'Schedule Policy is invalid' )
				);
			}
			// Absence Policy
			if ( $this->getAbsencePolicyID() != '' && $this->getAbsencePolicyID() != TTUUID::getZeroID() ) {
				$aplf = TTnew( 'AbsencePolicyListFactory' ); /** @var AbsencePolicyListFactory $aplf */
				$this->Validator->isResultSetWithRows( 'absence_policy',
													   $aplf->getByID( $this->getAbsencePolicyID() ),
													   TTi18n::gettext( 'Invalid Absence Policy' )
				);
			}
			// Branch
			if ( $this->getBranch() != '' && $this->getBranch() != TTUUID::getNotExistID() && $this->getBranch() != TTUUID::getZeroID() ) {
				$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
				$this->Validator->isResultSetWithRows( 'branch',
													   $blf->getByID( $this->getBranch() ),
													   TTi18n::gettext( 'Branch does not exist' )
				);
			}
			// Department
			if ( $this->getDepartment() != '' && $this->getDepartment() != TTUUID::getNotExistID() && $this->getDepartment() != TTUUID::getZeroID() ) {
				$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
				$this->Validator->isResultSetWithRows( 'department',
													   $dlf->getByID( $this->getDepartment() ),
													   TTi18n::gettext( 'Department does not exist' )
				);
			}

			if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
				// Job
				if ( $this->getJob() != '' && $this->getJob() != TTUUID::getNotExistID() && $this->getJob() != TTUUID::getZeroID() ) {
					$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
					$this->Validator->isResultSetWithRows( 'job',
														   $jlf->getByID( $this->getJob() ),
														   TTi18n::gettext( 'Job does not exist' )
					);
				}
				// Task
				if ( $this->getJobItem() != '' && $this->getJobItem() != TTUUID::getNotExistID() && $this->getJobItem() != TTUUID::getZeroID() ) {
					$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
					$this->Validator->isResultSetWithRows( 'job_item',
														   $jilf->getByID( $this->getJobItem() ),
														   TTi18n::gettext( 'Task does not exist' )
					);
				}
				// Punch Tag
				if ( $this->getPunchTag() != '' && ( ( $this->getPunchTag() != TTUUID::getZeroID() && $this->getPunchTag() != TTUUID::getNotExistID() ) && ( !is_array( $this->getPunchTag() ) || ( is_array( $this->getPunchTag() ) && !in_array( TTUUID::getZeroID(), $this->getPunchTag(), true ) && !in_array( TTUUID::getNotExistID(), $this->getPunchTag(), true ) ) ) ) ) {
					$ptlf = TTnew( 'PunchTagListFactory' ); /** @var PunchTagListFactory $ptlf */
					if ( is_array( $this->getPunchTag() ) ) {
						foreach ( $this->getPunchTag() as $punch_tag ) {
							$this->Validator->isResultSetWithRows( 'punch_tag_id',
																   $ptlf->getByID( $punch_tag ),
																   TTi18n::gettext( 'Invalid Punch Tag' )
							);
						}
					} else {
						$this->Validator->isResultSetWithRows( 'punch_tag_id',
															   $ptlf->getByID( $this->getPunchTag() ),
															   TTi18n::gettext( 'Invalid Punch Tag' )
						);
					}
				}
			}

			// Note
			if ( $this->getNote() != '' ) {
				$this->Validator->isLength( 'note',
											$this->getNote(),
											TTi18n::gettext( 'Note is too short or too long' ),
											0,
											1024
				);

				$this->Validator->isHTML( 'note',
										  $this->getNote(),
										  TTi18n::gettext( 'Note contains invalid special characters' ),
				);
			}
			//
			// ABOVE: Validation code moved from set*() functions.
			//

			$this->handleDayBoundary();
			$this->findUserDate();
			Debug::Text( 'User ID: ' . $this->getUser() . ' DateStamp: ' . $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );

			if ( $this->getUser() === false ) { //Use === so we still allow OPEN shifts (user_id=0)
				$this->Validator->isTRUE( 'user_id',
										  false,
										  TTi18n::gettext( 'Employee is not specified' ) );
			}

			if ( $this->getCompany() == false ) {
				$this->Validator->isTrue( 'company_id',
										  false,
										  TTi18n::gettext( 'Company is invalid' ) );
			}

			if ( $this->getDateStamp() == false ) {
				Debug::Text( 'DateStamp is INVALID! ID: ' . $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->Validator->isTrue( 'date_stamp',
										  false,
										  TTi18n::gettext( 'Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already' ) );
			}

			if ( $this->getDateStamp() != false && $this->getStartTime() == '' ) {
				$this->Validator->isTrue( 'start_time',
										  false,
										  TTi18n::gettext( 'In Time not specified' ) );
			}
			if ( $this->getDateStamp() != false && $this->getEndTime() == '' ) {
				$this->Validator->isTrue( 'end_time',
										  false,
										  TTi18n::gettext( 'Out Time not specified' ) );
			}

			if ( $this->getDeleted() == false && $this->getDateStamp() != false && is_object( $this->getUserObject() ) ) {
				if ( $this->getUserObject()->getHireDate() != '' && TTDate::getBeginDayEpoch( $this->getDateStamp() ) < TTDate::getBeginDayEpoch( $this->getUserObject()->getHireDate() ) ) {
					$this->Validator->isTRUE( 'date_stamp',
											  false,
											  TTi18n::gettext( 'Shift is before employees hire date' ) );
				}

				if ( $this->getUserObject()->getTerminationDate() != '' && TTDate::getEndDayEpoch( $this->getDateStamp() ) > TTDate::getEndDayEpoch( $this->getUserObject()->getTerminationDate() ) ) {
					$this->Validator->isTRUE( 'date_stamp',
											  false,
											  TTi18n::gettext( 'Shift is after employees termination date' ) );
				}
			}

			if ( $this->getStatus() == 20 && TTUUID::castUUID( $this->getAbsencePolicyID() ) != TTUUID::getZeroID() && ( $this->getDateStamp() != false && is_object( $this->getUserObject() ) ) ) {
				$pglf = TTNew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
				$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [ 'user_id' => [ $this->getUser() ], 'absence_policy' => [ $this->getAbsencePolicyID() ] ] );
				if ( $pglf->getRecordCount() == 0 ) {
					$this->Validator->isTRUE( 'absence_policy_id',
											  false,
											  TTi18n::gettext( 'This absence policy is not available for this employee' ) );
				}
			}

			//Ignore conflicting time check when EnableOverwrite is set, as we will just be deleting any conflicting shift anyways.
			//Also ignore when setting OPEN shifts to allow for multiple.
			if ( $this->getDeleted() == false && ( $this->getDateStamp() != false
							&& TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID() ) ) {
				$this->Validator->isTrue( 'start_time',
										  !$this->isConflicting(), //Reverse the boolean.
										  TTi18n::gettext( 'Conflicting start/end time, schedule already exists for this employee' ) );
			} else {
				Debug::text( 'Not checking for conflicts... DateStamp: ' . (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		if ( $this->getDeleted() == false ) {
			if ( $this->getSchedulePolicyID() === false ) {
				$this->setSchedulePolicyID( TTUUID::getZeroID() );
			}

			if ( $this->getAbsencePolicyID() === false ) {
				$this->setAbsencePolicyID( TTUUID::getZeroID() );
			}

			if ( $this->getBranch() === false ) {
				$this->setBranch( TTUUID::getZeroID() );
			}

			if ( $this->getDepartment() === false ) {
				$this->setDepartment( TTUUID::getZeroID() );
			}

			if ( $this->getJob() === false ) {
				$this->setJob( TTUUID::getZeroID() );
			}

			if ( $this->getJobItem() === false ) {
				$this->setJobItem( TTUUID::getZeroID() );
			}

			$this->handleDayBoundary();
			$this->findUserDate();

			if ( $this->getTotalTime() == false ) {
				$this->setTotalTime( $this->calcTotalTime() );
			}

			if ( $this->getStatus() == 10 ) {
				$this->setAbsencePolicyID( null );
			} else if ( $this->getStatus() == false ) {
				$this->setStatus( 10 ); //Default to working.
			}
		}

		return true;
	}

	/**
	 * @param string $user_id UUID
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @return bool
	 */
	function recalculateRecurringSchedules( $user_id, $start_date, $end_date ) {
		//global $api_message_id;

		//Used in UserFactory->postSave() to update recurring schedules immediately after employees are terminated/re-hired.

		$current_epoch = TTDate::getBeginWeekEpoch( TTDate::getBeginWeekEpoch( time() ) - 86400 );

		$start_date = TTDate::getBeginDayEpoch( $start_date );
		$end_date = TTDate::getEndDayEpoch( $end_date );
		Debug::text( 'Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

		$rsclf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
		$rsclf->getByUserIDAndStartDateAndEndDate( $user_id, $start_date, $end_date );
		if ( $rsclf->getRecordCount() > 0 ) {
			global $config_vars;
			foreach ( $rsclf as $rsc_obj ) {
				if ( !isset( $config_vars['other']['enable_job_queue'] ) || $config_vars['other']['enable_job_queue'] == true ) {
					SystemJobQueue::Add( TTi18n::getText( 'Recalculating Recurring Schedule' ), $this->getAPIMessageID(), 'RecurringScheduleFactory', 'recalculateRecurringScheduleForJobQueue', [ $rsc_obj->getCompany(), $rsc_obj->getID(), null, $current_epoch, $rsc_obj->getMaximumEndDate( $current_epoch ), $this->getDeleted() ], 90 );
				} else {
					$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
					$rsf->StartTransaction();
					$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), ( $current_epoch - ( 86400 * 720 ) ), ( $current_epoch + ( 86400 * 720 ) ) );
					if ( $this->getDeleted() == false ) {
						Debug::text( 'Recurring Schedule ID: ' . $rsc_obj->getID() . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $rsc_obj->getMaximumEndDate( $current_epoch ) ), __FILE__, __LINE__, __METHOD__, 10 );
						$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $current_epoch, $rsc_obj->getMaximumEndDate( $current_epoch ) );
					}
					$rsf->CommitTransaction();
				}
			}
		}

		return true;
	}

	/**
	 * Recalculate recurring schedules through the job queue.
	 * @param $company_id
	 * @param $recurring_schedule_control_id
	 * @param $user_id
	 * @param $start_date
	 * @param $end_date
	 * @param $deleted
	 * @param bool $strict_range Used for only recalculating a specific date range like a single date for a holiday.
	 * @return bool
	 * @throws DBError
	 * @throws ReflectionException
	 */
	static function recalculateRecurringScheduleForJobQueue( $company_id, $recurring_schedule_control_id, $user_id, $start_date, $end_date, $deleted = false, $strict_range = false ) {
		//
		//**THIS IS DONE IN recalculateRecurringScheduleForJobQueue, RecurringScheduleControlFactory, RecurringScheduleTemplateControlFactory, HolidayFactory postSave() as well.
		//
		Debug::text( 'Recurring Schedule ID: ' . $recurring_schedule_control_id . ' Start Date: '. TTDate::getDate( 'DATE+TIME', $start_date ) .' End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ) .' Deleted: '. (int)$deleted, __FILE__, __LINE__, __METHOD__, 10 );

		//Handle generating recurring schedule rows, so they are as real-time as possible.
		//In case an issue arises (like holiday not appearing or something) and they need to recalculate schedules, always start from the prior week.
		//  so we at least have a chance of recalculating retroactively to some degree.

		//Only recalculate the exact range specified. Otherwise all recurring schedules are deleted and re-added for only the specific date range.
		//  This is used when recalculating for just a specific date like a recurring holiday.
		if ( $strict_range == true ) {
			$clear_start_date = $start_date;
			$clear_end_date = $end_date;
		} else {
			$start_date = TTDate::getBeginWeekEpoch( TTDate::incrementDate( TTDate::getBeginWeekEpoch( $start_date ), -1, 'day' ) );
			$clear_start_date = ( $start_date - ( 86400 * 720 ) );
			$clear_end_date = ( $start_date + ( 86400 * 720 ) );
		}

		$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
		$rsf->StartTransaction();
		$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $recurring_schedule_control_id, $clear_start_date, $clear_end_date );

		if ( $start_date <= $end_date ) {
			if ( $deleted == true && !empty( $user_id ) ) {
				Debug::text( '  Recurring Schedule is deleted, rebuild all other recurring schedules for this user in case some were conflicting before...', __FILE__, __LINE__, __METHOD__, 10 );

				//If a user has two templates assigned to them that happen to be conflicting with one another, and the recurring schedule for one is deleted, it could leave "blank" shifts on the days where the conflict was.
				//So when deleting recurring schedules OR when removing employees from recurring schedules, try to recalculate *all* recurring schedules of all the users that may be affected to ensure that any conflicting shifts will be recalculated.
				$rsclf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
				$rsclf->getAPISearchByCompanyIdAndArrayCriteria( $company_id, [ 'user_id' => [ $user_id ] ] );
				if ( $rsclf->getRecordCount() > 0 ) {
					foreach ( $rsclf as $tmp_rsc_obj ) {
						if ( $recurring_schedule_control_id != $tmp_rsc_obj->getID() ) { //Don't recalculate the current recurring schedule record as thats already done above.
							$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $tmp_rsc_obj->getID(), $clear_start_date, $clear_end_date );

							//Maximum date must be based on the recurring schedule control record we are recalculating and *not* the one we are deleting, otherwise the schedule will get out of sync.
							//FIXME: Put a cap on this perhaps, as 3mths into the future so we don't spend a ton of time doing this
							//if the user sets it to display 1-2yrs in the future. Leave creating the rest of the rows to the maintenance job?
							//Since things may change we will want to delete all schedules with each change, but only add back in X weeks at most unless from a maintenance job.
							Debug::text( '    Recalculating Recurring Schedule ID: ' . $tmp_rsc_obj->getID() . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ) . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $tmp_rsc_obj->getMaximumEndDate( $start_date ) ), __FILE__, __LINE__, __METHOD__, 10 );
							$rsf->addRecurringSchedulesFromRecurringScheduleControl( $tmp_rsc_obj->getCompany(), $tmp_rsc_obj->getID(), $start_date, $tmp_rsc_obj->getMaximumEndDate( $start_date ) );
						} else {
							Debug::text( '    Skipping recalculating ourself again...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				}
			} else {
				Debug::text( 'Recurring Schedule ID: ' . $recurring_schedule_control_id . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ) . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );
				$rsf->addRecurringSchedulesFromRecurringScheduleControl( $company_id, $recurring_schedule_control_id, $start_date, $end_date );
			}
		} else {
			Debug::text( '  WARNING: End date is before start date, not recalculating...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		$rsf->CommitTransaction();

		SystemJobQueue::sendNotificationToBrowser();

		return true;
	}

	/**
	 * This is run by AddRecurringScheduleShift maintenance job.
	 * @param $company_id
	 * @param $current_epoch
	 * @param $initial_start_date
	 * @param $initial_end_date
	 * @return bool
	 * @throws DBError
	 * @throws ReflectionException
	 */
	static function addRecurringScheduleShiftForJobQueue( $company_id, $current_epoch, $initial_start_date, $initial_end_date ) {
		Debug::text( 'Company: ID: ' . $company_id, __FILE__, __LINE__, __METHOD__, 10 );

		//
		// Purge recurring schedules control rows 90 days after end date has passed.
		//
		$rsclf = new RecurringScheduleControlListFactory();
		$rsclf->getByCompanyIdAndEndDate( $company_id, ( $current_epoch - ( 86400 * 91 ) ) );
		Debug::text( '  Recurring Schedule Control records 90days past its end date: ' . $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $rsclf->getRecordCount() ) {
			foreach ( $rsclf as $rsc_obj ) {
				Debug::text( '    Deleting RSC... ID: ' . $rsc_obj->getID() . ' End Date: ' . TTDate::getDate( 'DATE', $rsc_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				$rsc_obj->setDeleted( true );
				if ( $rsc_obj->isValid() ) {
					$rsc_obj->Save();
				}
			}
		}
		unset( $rsc_obj );

		// FIXME: Purge recurring schedule control rows if all users assigned to them are inactive or past their termination date by 90days?


		//
		// Add new recurring schedules.
		//
		$rsclf->getByCompanyIdAndStartDateAndEndDate( $company_id, $initial_start_date, $initial_end_date );
		if ( $rsclf->getRecordCount() > 0 ) {
			Debug::text( 'Recurring Schedule Control List Record Count: ' . $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $rsclf as $rsc_obj ) {
				$rsclf->StartTransaction(); // Wrap each individual schedule in its own transaction instead.

				//Since cron jobs run in system timezone (ie: 'America/Vancouver') and date_stamp and start_date/end_date (timestamptz) columns being different data types
				//we need to try to switch into a timezone at least within the same day as the final timezone before we get the recurring schedules.
				//Once we do something with the date_stamp column or store timezones, we can remove this, as its not a 100% fix.
				$rstc_obj = $rsc_obj->getRecurringScheduleTemplateControlObject();
				if ( is_object( $rstc_obj ) ) {
					Debug::text( 'Recurring Schedule Template Control last updated by: ' . $rstc_obj->getUpdatedBy(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( TTUUID::isUUID( $rstc_obj->getUpdatedBy() ) && $rstc_obj->getUpdatedBy() != TTUUID::getZeroID() ) {
						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getById( $rstc_obj->getUpdatedBy() );
						if ( $ulf->getRecordCount() == 1 ) {
							$ulf->getCurrent()->getUserPreferenceObject()->setTimeZonePreferences();
						}
					}
				}

				//Default to system timezone if no other timezone is specified.
				if ( !isset( $ulf ) || ( isset( $ulf ) && $ulf->getRecordCount() != 1 ) ) {
					TTDate::setTimeZone(); //Use system timezone.
				}
				unset( $ulf );

				//Make sure its always at least the display weeks based on the end of the current week.
				$maximum_end_date = $rsc_obj->getMaximumEndDate( $current_epoch );
				Debug::text( 'Recurring Schedule ID: ' . $rsc_obj->getID() . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
				$rslf = TTNew( 'RecurringScheduleListFactory' ); /** @var RecurringScheduleListFactory $rslf */

				//Clear out recurring schedules for anything older than 1 week to keep the recurring schedule table small as historical recurring schedules really don't matter once they have been committed anyways.
				$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), ( $current_epoch - ( 86400 * 720 ) ), TTDate::getEndWeekEpoch( ( TTDate::getBeginWeekEpoch( $current_epoch ) - ( 86400 * 8 ) ) ) );

				//Grab the earliest last day of the recurring schedule, so we can start from there and add the next week.
				//We actually want to get the last day of each recurring schedule, and just add to that. Rather then rebuilding the entire schedule.
				//$minimum_start_date = TTDate::getBeginDayEpoch( ( TTDate::getMiddleDayEpoch( $rslf->getMinimumStartTimeByRecurringScheduleControlID( $rsc_obj->getID() ) + 86400 ) ) );
				$minimum_start_date = $rslf->getMinimumStartTimeByRecurringScheduleControlID( $rsc_obj->getID() );
				if ( $minimum_start_date != '' ) {
					//$new_week_start_date = TTDate::getBeginWeekEpoch( ( TTDate::getEndWeekEpoch( TTDate::getMiddleDayEpoch( $minimum_start_date ) ) + 86400 ) );
					//Use the exact date that we left off with the last recurring schedule.
					//This should fix a bug where if the recurring schedule last shift was on Mon Jun 12th, it would skip a week due to taking that time and starting in the next week.
					//I think we always need to overlap by at least a week in cases where the last schedule ends on a Wed or Mon or something.
					$new_week_start_date = TTDate::getBeginWeekEpoch( $minimum_start_date );
					Debug::text( '  Starting from where we last left off: ' . TTDate::getDate( 'DATE+TIME', $new_week_start_date ), __FILE__, __LINE__, __METHOD__, 10 );
				} else {
					//$new_week_start_date = TTDate::getBeginWeekEpoch( ( TTDate::getMiddleDayEpoch( $initial_start_date ) - (86400 * 8) ) );
					$new_week_start_date = TTDate::getBeginWeekEpoch( $initial_start_date );
					Debug::text( '  Setting new week start date to: ' . TTDate::getDate( 'DATE+TIME', $new_week_start_date ), __FILE__, __LINE__, __METHOD__, 10 );
				}
				$new_week_end_date = TTDate::getEndWeekEpoch( $new_week_start_date );
				Debug::text( '  Start Date: ' . TTDate::getDate( 'DATE+TIME', $new_week_start_date ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $new_week_end_date ) . ' Maximum End Date: ' . TTDate::getDate( 'DATE+TIME', $maximum_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				if ( $new_week_end_date <= $maximum_end_date ) {
					//Add new schedules for the upcoming week.
					//$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), $new_week_start_date, $new_week_end_date );
					//$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $new_week_start_date, $new_week_end_date );

					//Rather than just add new schedules for one week, bring them up to the maximum end date, which in most cases should be just 1 week unless some kind of issue has occurred.
					//This will help in cases where maintenance jobs haven't run for a while, or in other strange cases too maybe.
					$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getID(), $new_week_start_date, $maximum_end_date );
					$rsf->addRecurringSchedulesFromRecurringScheduleControl( $rsc_obj->getCompany(), $rsc_obj->getID(), $new_week_start_date, $maximum_end_date ); //Always create schedules out to the maximum end date each time.
				} else {
					Debug::text( '  Already past maximum end date of: ' . TTDate::getDate( 'DATE+TIME', $maximum_end_date ) . ' Display Weeks: ' . $rsc_obj->getDisplayWeeks() . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $rsc_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				}

				//$rsclf->FailTransaction();
				$rsclf->CommitTransaction();
			}
		}

		return true;
	}

	/**
	 * @param string|string[] $id UUID
	 * @param int $start_date     EPOCH
	 * @param int $end_date       EPOCH
	 * @return bool
	 */
	function clearRecurringSchedulesFromRecurringScheduleControl( $id, $start_date, $end_date ) {
		//global $api_message_id;
		$start_date = TTDate::getBeginDayEpoch( $start_date );
		$end_date = TTDate::getEndDayEpoch( $end_date );

		if ( $end_date < $start_date ) {
			Debug::text( '  WARNING: End date is before start date, not clearing...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		//$id can be an array, as HolidayFactory uses that to recalculate schedules on holidays.

		$rslf = TTnew( 'RecurringScheduleListFactory' ); /** @var RecurringScheduleListFactory $rslf */
		$rslf->getByRecurringScheduleControlIDAndStartDateAndEndDate( $id, $start_date, $end_date );
		if ( $rslf->getRecordCount() > 0 ) {
			Debug::Arr( $id, 'Recurring Schedule Control ID: Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ) . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ) . ' Deleting: ' . $rslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $rslf as $rs_obj ) {
				$rs_obj->setDeleted( true );
				if ( $rs_obj->isValid() ) {
					$rs_obj->Save();
				}
			}
		} else {
			Debug::text( 'No records to delete...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		Debug::text( 'Done...', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @param string $company_id  UUID
	 * @param string|string[] $id UUID
	 * @param int $start_date     EPOCH
	 * @param int $end_date       EPOCH
	 * @return bool
	 */
	function addRecurringSchedulesFromRecurringScheduleControl( $company_id, $id, $start_date, $end_date ) {
		global $api_message_id, $profiler;
		$current_epoch = time();
		$start_date = TTDate::getBeginDayEpoch( $start_date );
		$end_date = TTDate::getEndDayEpoch( $end_date );

		if ( $end_date < $start_date ) {
			Debug::text( '  WARNING: End date is before start date, not adding recurring schedules...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		//Get holidays
		//Make sure holiday policies are segragated by policy_group_id, otherwise all policies apply to all employees.
		$holiday_data = [];
		$hlf = TTnew( 'HolidayListFactory' ); /** @var HolidayListFactory $hlf */
		$hlf->getByCompanyIdAndStartDateAndEndDate( $company_id, $start_date, $end_date );
		Debug::text( 'Found Holiday Rows: ' . $hlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		foreach ( $hlf as $h_obj ) {
			//If there are conflicting holidays, one being absent and another being working, don't override the working one.
			//That way we default to working just in case.
			if ( !isset( $holiday_data[$h_obj->getColumn( 'policy_group_id' )][TTDate::getISODateStamp( $h_obj->getDateStamp() )] ) && is_object( $h_obj->getHolidayPolicyObject() ) ) {
				if ( (int)$h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus() == 0 ) { //0=Use Schedule
					$holiday_data[$h_obj->getColumn( 'policy_group_id' )][TTDate::getISODateStamp( $h_obj->getDateStamp() )] = [ 'status_id' => (int)$h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus() ]; //0=Use Schedule
				} else if ( is_object( $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject() ) && is_object( $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayCodeObject() ) ) {
					$holiday_data[$h_obj->getColumn( 'policy_group_id' )][TTDate::getISODateStamp( $h_obj->getDateStamp() )] = [ 'status_id' => (int)$h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus(), 'absence_policy_id' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyID(), 'type_id' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getPayCodeObject()->getType(), 'absence_policy' => $h_obj->getHolidayPolicyObject()->getAbsencePolicyObject()->getName() ];
				} else {
					$holiday_data[$h_obj->getColumn( 'policy_group_id' )][TTDate::getISODateStamp( $h_obj->getDateStamp() )] = [ 'status_id' => 10 ]; //Working
				}
			} else {
				$holiday_data[$h_obj->getColumn( 'policy_group_id' )][TTDate::getISODateStamp( $h_obj->getDateStamp() )] = [ 'status_id' => 10 ]; //Working
			}
		}
		unset( $hlf );

		$rsclf = TTnew( 'RecurringScheduleControlListFactory' ); /** @var RecurringScheduleControlListFactory $rsclf */
		$rsclf->getByCompanyIdAndIDAndStartDateAndEndDate( $company_id, $id, $start_date, $end_date );
		if ( $rsclf->getRecordCount() > 0 ) {
			Debug::text( 'Recurring Schedule Control List Record Count: ' . $rsclf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			foreach ( $rsclf as $rsc_obj ) {
				//Since cron jobs run in system timezone (ie: 'America/Vancouver') and date_stamp and start_date/end_date (timestamptz) columns being different data types
				//we need to try to switch into a timezone at least within the same day as the final timezone before we get the recurring schedules.
				//Once we do something with the date_stamp column or store timezones, we can remove this, as its not a 100% fix.
				$rstc_obj = $rsc_obj->getRecurringScheduleTemplateControlObject();
				if ( is_object( $rstc_obj ) ) {
					Debug::text( 'Recurring Schedule Template Control last updated by: ' . $rstc_obj->getUpdatedBy(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( TTUUID::isUUID( $rstc_obj->getUpdatedBy() ) && $rstc_obj->getUpdatedBy() != TTUUID::getZeroID() ) {
						$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
						$ulf->getById( $rstc_obj->getUpdatedBy() );
						if ( $ulf->getRecordCount() == 1 ) {
							$ulf->getCurrent()->getUserPreferenceObject()->setTimeZonePreferences();
						}
					}
				}

				//Default to system timezone if no other timezone is specified.
				if ( !isset( $ulf ) || ( isset( $ulf ) && $ulf->getRecordCount() != 1 ) ) {
					TTDate::setTimeZone(); //Use system timezone.
				}
				unset( $ulf );

				$display_weeks_end_date = ( TTDate::getEndWeekEpoch( $current_epoch + ( 86400 * 7 ) ) + ( $rsc_obj->getDisplayWeeks() * ( 86400 * 7 ) ) );
				if ( $end_date > $display_weeks_end_date ) {
					$end_date = $display_weeks_end_date;
					Debug::text( '  Adjusting End Date to: ' . TTDate::getDate( 'DATE', $display_weeks_end_date ), __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $display_weeks_end_date );
				//$rsclf->StartTransaction(); Wrap each individual schedule in its own transaction instead.

				Debug::text( 'Recurring Schedule ID: ' . $rsc_obj->getID() . ' Start Date: ' . TTDate::getDate( 'DATE', $start_date ) . ' End Date: ' . TTDate::getDate( 'DATE', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );
				//Debug::Arr($rsc_obj->getUser(), 'Users assigned to Schedule', __FILE__, __LINE__, __METHOD__, 10);

				$max_i = 0;
				$open_shift_conflict_index = [];
				$schedule_shifts = [];
				$schedule_shifts_index = [];

				$rstlf = TTnew( 'RecurringScheduleTemplateListFactory' ); /** @var RecurringScheduleTemplateListFactory $rstlf */
				$rstlf->getByRecurringScheduleControlIdAndStartDateAndEndDate( $rsc_obj->getId(), $start_date, $end_date );
				if ( $rstlf->getRecordCount() > 0 ) {
					$this->getProgressBarObject()->start( $api_message_id, $rstlf->getRecordCount(), null, TTi18n::getText( 'ReCalculating Templates...' ) );

					Debug::Text( 'Total Templates: ' . $rstlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
					foreach ( $rstlf as $rst_obj ) {
						$rst_obj->getShifts( $start_date, $end_date, $holiday_data, $max_i, $schedule_shifts, $schedule_shifts_index, $open_shift_conflict_index );
						$this->getProgressBarObject()->set( $api_message_id, $rstlf->getCurrentRow() );
					}

					$this->getProgressBarObject()->stop( $api_message_id );
				}
				ksort( $schedule_shifts ); //Sort the shifts so they are always created in chronological order.
				//Debug::Arr($schedule_shifts, 'Recurring Schedule Shifts', __FILE__, __LINE__, __METHOD__, 10);

				if ( is_array( $schedule_shifts ) && count( $schedule_shifts ) > 0 ) {
					$i = 0;
					$key = 0;
					$this->getProgressBarObject()->start( $api_message_id, count( $schedule_shifts ), null, TTi18n::getText( 'ReCalculating Shifts...' ) );

					foreach ( $schedule_shifts as $date_stamp => $recurring_schedule_shifts ) {
						Debug::text( 'Recurring Schedule Shift Date Stamp: ' . $date_stamp . ' Total Shifts: ' . count( $recurring_schedule_shifts ), __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $recurring_schedule_shifts as $recurring_schedule_shift ) {
							//Date is formatted as per date preferences, so make sure we parse it properly here
							$recurring_schedule_shift_start_time = TTDate::parseDateTime( $recurring_schedule_shift['start_date'] );
							$recurring_schedule_shift_end_time = TTDate::parseDateTime( $recurring_schedule_shift['end_date'] );

							Debug::text( '(After User TimeZone)Recurring Schedule Shift Start Time: ' . TTDate::getDate( 'DATE+TIME', $recurring_schedule_shift_start_time ) . '(' . $recurring_schedule_shift['start_date'] . ') End Time: ' . TTDate::getDate( 'DATE+TIME', $recurring_schedule_shift_end_time ), __FILE__, __LINE__, __METHOD__, 10 );
							//Make sure punch pairs fall within limits

							//Debug::text('Recurring Schedule Shift Start Time falls within Limits: '. TTDate::getDate('DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10);

							//Need to support recurring scheduled absences.
							$status_id = $recurring_schedule_shift['status_id'];
							$absence_policy_id = $recurring_schedule_shift['absence_policy_id'];

							//Make sure we not already added this schedule shift.
							//And that no schedule shifts overlap this one.
							//Use the isValid() function for this
							$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */

							//$sf->StartTransaction(); //Transactions here may cause SQL upgrades to fail due to v1067

							$rsf->setCompany( $company_id );
							$rsf->setUser( $recurring_schedule_shift['user_id'] );
							$rsf->setRecurringScheduleControl( $rsc_obj->getID() );
							$rsf->setRecurringScheduleTemplateControl( $rsc_obj->getRecurringScheduleTemplateControl() );

							//Find the date that the shift will be assigned to so we know if its a holiday or not.
							if ( is_object( $rsf->getPayPeriodScheduleObject() ) ) {
								$date_stamp = $rsf->getPayPeriodScheduleObject()->getShiftAssignedDate( $recurring_schedule_shift_start_time, $recurring_schedule_shift_end_time, $rsf->getPayPeriodScheduleObject()->getShiftAssignedDay() );
							} else {
								$date_stamp = $recurring_schedule_shift_start_time;
							}

							//Is this a holiday?
							$hlf = new HolidayListFactory();
							$hlf->getByPolicyGroupUserIdAndDate( $recurring_schedule_shift['user_id'], TTDate::getBeginDayEpoch( $date_stamp ) );
							if ( $hlf->getRecordCount() > 0 ) {
								$h_obj = $hlf->getCurrent();
								Debug::text( 'Found Holiday! Name: ' . $h_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
								//Ignore after holiday eligibility when scheduling, since it will always fail.
								if ( $h_obj->isEligible( $recurring_schedule_shift['user_id'], true ) ) {
									Debug::text( 'User is Eligible...', __FILE__, __LINE__, __METHOD__, 10 );

									//Get Holiday Policy info
									if ( $h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus() != 0 ) { //0=Use Schedule Default
										$status_id = $h_obj->getHolidayPolicyObject()->getDefaultScheduleStatus();
									}
									if ( $h_obj->getHolidayPolicyObject()->getAbsencePolicyID() != TTUUID::getZeroID() ) {
										$absence_policy_id = $h_obj->getHolidayPolicyObject()->getAbsencePolicyID();
									}
									Debug::text( 'Default Schedule Status: ' . $status_id, __FILE__, __LINE__, __METHOD__, 10 );
								} else {
									Debug::text( 'User is NOT Eligible...', __FILE__, __LINE__, __METHOD__, 10 );
								}
							} else {
								Debug::text( 'No Holidays on this day: ', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $hlf, $h_obj );
							Debug::text( 'Schedule Status ID: ' . $status_id, __FILE__, __LINE__, __METHOD__, 10 );

							$profiler->startTimer( 'Add Schedule' );

							$rsf->setStatus( $status_id ); //Working
							$rsf->setStartTime( $recurring_schedule_shift_start_time );
							$rsf->setEndTime( $recurring_schedule_shift_end_time );
							$rsf->setSchedulePolicyID( TTUUID::castUUID( $recurring_schedule_shift['schedule_policy_id'] ) );

							if ( isset( $absence_policy_id ) && TTUUID::isUUID( $absence_policy_id ) && $absence_policy_id != TTUUID::getZeroID() && $absence_policy_id != TTUUID::getNotExistID() ) {
								$rsf->setAbsencePolicyID( TTUUID::castUUID( $absence_policy_id ) );
							}
							unset( $absence_policy_id );

							$rsf->setBranch( TTUUID::castUUID( $recurring_schedule_shift['branch_id'] ) );
							$rsf->setDepartment( TTUUID::castUUID( $recurring_schedule_shift['department_id'] ) );
							$rsf->setJob( TTUUID::castUUID( $recurring_schedule_shift['job_id'] ) );
							$rsf->setJobItem( TTUUID::castUUID( $recurring_schedule_shift['job_item_id'] ) );
							$rsf->setPunchTag( $recurring_schedule_shift['punch_tag_id'] );

							$rsf->setAutoFill( (int)$rsc_obj->getAutoFill() );

							//This causes confusion when debugging issues, they should only be set to the currently logged in user if triggered by them,
							//otherwise it can be set by the cron job.
							//$rsf->setUpdatedDate( $recurring_schedule_shift['updated_date'] );
							//$rsf->setCreatedDate( $recurring_schedule_shift['created_date'] );
							//if ( $recurring_schedule_shift['created_by_id'] > 0 ) {
							//	$rsf->setCreatedBy( $recurring_schedule_shift['created_by_id'] );
							//}

							if ( $rsf->isValid() ) {
								$rsf->Save();
								//$sf->CommitTransaction();
							} else {
								//$sf->FailTransaction();
								//$sf->CommitTransaction();
								Debug::text( 'Bad or conflicting Schedule: ' . TTDate::getDate( 'DATE+TIME', $recurring_schedule_shift_start_time ), __FILE__, __LINE__, __METHOD__, 10 );
							}

							$profiler->stopTimer( 'Add Schedule' );

							$i++;
						}

						$this->getProgressBarObject()->set( $api_message_id, $key );
						$key++;
					}
					Debug::text( 'Total Recurring Shifts added: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );

					$this->getProgressBarObject()->stop( $api_message_id );
				} else {
					Debug::text( 'No Recurring Schedule Days To Add!', __FILE__, __LINE__, __METHOD__, 10 );
				}

				//Set timezone back to default before we loop to the next user.
				//Without this the next start/end date will be in the last users timezone
				//and cause schedules to be included.
				//TTDate::setTimeZone();

				unset( $rsf );
			}
		}

		Debug::text( 'Done...', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @param int $start_date
	 * @param int $end_date
	 * @return bool
	 * @throws ReflectionException
	 */
	static function addScheduleFromRecurringScheduleForJobQueue( $company_id, $start_date, $end_date ) {
		$clf = TTNew('CompanyListFactory');
		$clf->getById( $company_id );
		if ( $clf->getRecordCount() == 1 ) {
			$c_obj = $clf->getCurrent();
			$rsf = TTNew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
			return $rsf->addScheduleFromRecurringSchedule( $c_obj, $start_date, $end_date );
		}

		return false;
	}

	/**
	 * @param object $company_obj
	 * @param int $start_date EPOCH
	 * @param int $end_date   EPOCH
	 * @return bool
	 */
	function addScheduleFromRecurringSchedule( $company_obj, $start_date, $end_date ) {
		$current_epoch = time();

		$company_id = $company_obj->getID();

		$rslf = TTNew( 'RecurringScheduleListFactory' ); /** @var RecurringScheduleListFactory $rslf */
		$rslf->getByCompanyIDAndStartDateAndEndDateAndNoConflictingSchedule( $company_id, $start_date, $end_date );
		Debug::text( 'Recurring Schedules Pending Commit: ' . $rslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $rslf->getRecordCount() > 0 ) {
			foreach ( $rslf as $rs_obj ) {
				Debug::text( '  Processing Recurring Schedule: User: ' . $rs_obj->getUser() . ' Start Time: ' . TTDate::getDate( 'DATE+TIME', $rs_obj->getStartTime() ) . ' End Time: ' . TTDate::getDate( 'DATE+TIME', $rs_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( TTUUID::isUUID( $rs_obj->getUser() ) && $rs_obj->getUser() != TTUUID::getZeroID() && $rs_obj->getUser() != TTUUID::getNotExistID() ) {
					$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
					$ulf->getById( $rs_obj->getUser() );
					if ( $ulf->getRecordCount() > 0 ) {
						$ulf->getCurrent()->getUserPreferenceObject()->setTimeZonePreferences();
					} else {
						//Use system timezone.
						TTDate::setTimeZone();
					}
				} else {
					//Use system timezone.
					TTDate::setTimeZone();
				}

				$transaction_function = function () use ( $current_epoch, $company_id, $company_obj, $rs_obj, $ulf ) {
					$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
					$sf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing duplicate user records or duplicate employee number/user_names.
					$sf->StartTransaction();

					$sf->setCompany( $company_id );
					$sf->setUser( $rs_obj->getUser() );
					//$sf->setRecurringScheduleControl( $rs_obj->getRecurringScheduleControl() );
					$sf->setRecurringScheduleTemplateControl( $rs_obj->getRecurringScheduleTemplateControl() );

					//Find the date that the shift will be assigned to so we know if its a holiday or not.
					//This is already determined in addRecurringSchedulesFromRecurringScheduleControl() above, no need to do it again?
					//if ( is_object( $sf->getPayPeriodScheduleObject() ) ) {
					//	$date_stamp = $sf->getPayPeriodScheduleObject()->getShiftAssignedDate( $rs_obj->getStartTime(), $rs_obj->getEndTime(), $sf->getPayPeriodScheduleObject()->getShiftAssignedDay() );
					//} else {
					//	$date_stamp = $rs_obj->getDateStamp();
					//}

					$sf->setStatus( $rs_obj->getStatus() );
					$sf->setStartTime( $rs_obj->getStartTime() );
					$sf->setEndTime( $rs_obj->getEndTime() );
					$sf->setSchedulePolicyID( TTUUID::castUUID( $rs_obj->getSchedulePolicyID() ) );
					$sf->setAbsencePolicyID( TTUUID::castUUID( $rs_obj->getAbsencePolicyID() ) );

					$sf->setBranch( TTUUID::castUUID( $rs_obj->getBranch() ) );
					$sf->setDepartment( TTUUID::castUUID( $rs_obj->getDepartment() ) );
					$sf->setJob( TTUUID::castUUID( $rs_obj->getJob() ) );
					$sf->setJobItem( TTUUID::castUUID( $rs_obj->getJobItem() ) );
					$sf->setPunchTag( $rs_obj->getPunchTag() );

					//Disable notifications when using auto-fill.
					if ( $rs_obj->getAutoFill() == true ) {
						$sf->setEnableNotifications( false );
					} else {
						$sf->setEnableNotifications( true );
					}

					if ( $sf->isValid() ) {
						//Recalculate if its a absence schedule, so the holiday
						//policy takes effect.
						//Always re-calculate, this way it automatically applies dock time and holiday time.
						//Recalculate at the end of the day in a cronjob.
						//Part of the reason is that if they have a dock policy, it will show up as
						//docking them time during the entire day.
						//$sf->setEnableReCalculateDay(FALSE);

						//Only for holidays do we calculate the day right away.
						//So they don't have to wait 24hrs to see stat time.
						//Also need to recalculate if the schedule was added after the schedule has already started.
						if ( ( $rs_obj->getStatus() == 20 && $rs_obj->getAutoFill() == false )
								|| $rs_obj->getStartTime() <= $current_epoch ) {
							$sf->setEnableReCalculateDay( true );
						} else {
							$sf->setEnableReCalculateDay( false ); //Don't need to re-calc right now?
						}

						$schedule_result = $sf->Save( false );
						Debug::text( '  Saving Committed Schedule: ' . $schedule_result . ' User: ' . $sf->getUser() . ' Start Time: ' . TTDate::getDate( 'DATE+TIME', $sf->getStartTime() ) . ' End Time: ' . TTDate::getDate( 'DATE+TIME', $sf->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						$schedule_result = false;
						$sf->FailTransaction();
						Debug::text( 'Bad or conflicting Schedule: ' . TTDate::getDate( 'DATE+TIME', $rs_obj->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10 );
					}

					$sf->CommitTransaction();
					$sf->setTransactionMode(); //Back to default isolation level.

					return [ $schedule_result ];
				};

				[ $schedule_result ] = $this->RetryTransaction( $transaction_function );

				//Add punch outside transaction for adding the schedule, just in case something fails with the punch the schedule will still exist.
				if ( $schedule_result == true
						&& TTUUID::isUUID( $rs_obj->getUser() ) && $rs_obj->getUser() != TTUUID::getZeroID() && $rs_obj->getUser() != TTUUID::getNotExistID()
						&& ( $rs_obj->getAutoFill() == true && $company_obj->getProductEdition() >= TT_PRODUCT_PROFESSIONAL )
						&& $rs_obj->getStatus() == 10 ) {

					$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
					$sf->addPunchFromScheduleObject( $rs_obj );
				} else {
					Debug::text( '  Not processing AutoFill... AutoFill: ' . (int)$rs_obj->getAutoFill() . ' Status: ' . $rs_obj->getStatus() . ' Edition: ' . $company_obj->getProductEdition(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
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
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$this->setUser( $data[$key] );
							break;
						case 'user_date_id': //Ignore explicitly set user_date_id here as its set above.
						case 'total_time': //If they try to specify total time, just skip it, as it gets calculated later anyways.
							break;
						case 'date_stamp':
							$this->$function( TTDate::parseDateTime( $data[$key] ) );
							break;
						case 'start_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text( '..Setting start time from EPOCH: "' . $data[$key] . '"', __FILE__, __LINE__, __METHOD__, 10 );

								if ( isset( $data['start_date_stamp'] ) && $data['start_date_stamp'] != '' && isset( $data[$key] ) && $data[$key] != '' ) {
									Debug::text( ' aSetting start time... "' . $data['start_date_stamp'] . ' ' . $data[$key] . '"', __FILE__, __LINE__, __METHOD__, 10 );
									$this->$function( TTDate::parseDateTime( $data['start_date_stamp'] . ' ' . $data[$key] ) ); //Prefix date_stamp onto start_time
								} else if ( isset( $data[$key] ) && $data[$key] != '' ) {
									//When start_time is provided as a full timestamp. Happens with audit log detail.
									Debug::text( ' aaSetting start time...: ' . $data[$key], __FILE__, __LINE__, __METHOD__, 10 );
									$this->$function( TTDate::parseDateTime( $data[$key] ) );
									//} elseif ( is_object( $this->getUserDateObject() ) ) {
									//	Debug::text(' aaaSetting start time...: '. $this->getUserDateObject()->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10);
									//	$this->$function( TTDate::parseDateTime( TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key] ) );
								} else {
									Debug::text( ' Not setting start time...', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
							break;
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text( '..xSetting end time from EPOCH: "' . $data[$key] . '"', __FILE__, __LINE__, __METHOD__, 10 );

								if ( isset( $data['start_date_stamp'] ) && $data['start_date_stamp'] != '' && isset( $data[$key] ) && $data[$key] != '' ) {
									Debug::text( ' aSetting end time... "' . $data['start_date_stamp'] . ' ' . $data[$key] . '"', __FILE__, __LINE__, __METHOD__, 10 );
									$this->$function( TTDate::parseDateTime( $data['start_date_stamp'] . ' ' . $data[$key] ) ); //Prefix date_stamp onto end_time
								} else if ( isset( $data[$key] ) && $data[$key] != '' ) {
									Debug::text( ' aaSetting end time...: ' . $data[$key], __FILE__, __LINE__, __METHOD__, 10 );
									//When end_time is provided as a full timestamp. Happens with audit log detail.
									$this->$function( TTDate::parseDateTime( $data[$key] ) );
									//} elseif ( is_object( $this->getUserDateObject() ) ) {
									//	Debug::text(' bbbSetting end time... "'. TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key]	 .'"', __FILE__, __LINE__, __METHOD__, 10);
									//	$this->$function( TTDate::parseDateTime( TTDate::getDate('DATE', $this->getUserDateObject()->getDateStamp() ) .' '. $data[$key] ) );
								} else {
									Debug::text( ' Not setting end time...', __FILE__, __LINE__, __METHOD__, 10 );
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

			$this->handleDayBoundary();                    //Make sure we handle day boundary before calculating total time.
			$this->setTotalTime( $this->calcTotalTime() ); //Calculate total time immediately after. This is required for proper audit logging too.
			$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @param bool $permission_children_ids
	 * @return mixed
	 */
	function getObjectAsArray( $include_columns = null, $permission_children_ids = false ) {
		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
							if ( TTUUID::isUUID( $this->getColumn( 'user_id' ) ) && $this->getColumn( 'user_id' ) != TTUUID::getZeroID() && $this->getColumn( 'user_id' ) != TTUUID::getNotExistID() ) {
								$data[$variable] = $this->getColumn( $variable );
							} else {
								$data[$variable] = TTi18n::getText( 'OPEN' );
							}
							break;
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$data[$variable] = $this->tmp_data['user_id'] = TTUUID::castUUID( $this->getColumn( $variable ) );
							break;
						case 'user_status_id':
							$data[$variable] = (int)$this->getColumn( $variable );
							break;
						case 'group_id':
						case 'title_id':
						case 'default_branch_id':
						case 'default_department_id':
						case 'pay_period_id':
							$data[$variable] = TTUUID::castUUID( $this->getColumn( $variable ) );
							break;
						case 'group':
						case 'title':
						case 'default_branch':
						case 'default_department':
						case 'schedule_policy':
						case 'absence_policy':
						case 'branch':
						case 'department':
						case 'job':
						case 'job_item':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'start_date_stamp':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getStartTime() ); //Include both date+time
							break;
						case 'start_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getStartTime() ); //Include both date+time
							break;
						case 'end_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->getEndTime() ); //Include both date+time
							break;
						case 'start_time_stamp':
							$data[$variable] = $this->getStartTime(); //Include start date/time in epoch format for sorting...
							break;
						case 'end_time_stamp':
							$data[$variable] = $this->getEndTime(); //Include end date/time in epoch format for sorting...
							break;
						case 'start_time':
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'TIME', $this->$function() ); //Just include time, so Mass Edit sees similar times without dates
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
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}
}

?>