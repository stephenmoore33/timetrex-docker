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
class ScheduleFactory extends Factory {
	protected $table = 'schedule';
	protected $pk_sequence_name = 'schedule_id_seq'; //PK Sequence name

	protected $user_obj = null;
	protected $user_date_obj = null;
	protected $schedule_policy_obj = null;
	protected $absence_policy_obj = null;
	protected $branch_obj = null;
	protected $department_obj = null;
	protected $pay_period_obj = null;
	protected $pay_period_schedule_obj = null;

	protected $json_columns = [ 'punch_tag_id' ];

	private $notifications;
	private $timesheet_verification_check;
	private $notify_user_schedule_change;
	private $split_at_midnight;

	/**
	 * @var bool
	 */
	private $overwrite;
	private $recalc_day;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'pay_period_id' )->setFunctionMap( 'PayPeriod' )->setType( 'uuid' )->setIsNull( false ),
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
							TTSCol::new( 'replaced_id' )->setFunctionMap( 'ReplacedId' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'recurring_schedule_template_control_id' )->setFunctionMap( 'RecurringScheduleTemplateControl' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'auto_fill' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'punch_tag_id' )->setFunctionMap( 'PunchTag' )->setType( 'jsonb' )->setIsNull( true ),
							TTSCol::new( 'custom_field' )->setType( 'jsonb' )->setIsNull( true ),
							TTSCol::new( 'other_id1' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'other_id2' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'other_id3' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'other_id4' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'other_id5' )->setType( 'varchar' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_schedule' )->setLabel( TTi18n::getText( 'Schedule' ) )->setFields(
									new TTSFields(
											TTSField::new( 'user_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Employee' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'user_ids' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Employee' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APISchedule' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'absence_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Absence Policy' ) )->setDataSource( TTSAPI::new( 'APIAbsencePolicy' )->setMethod( 'getAbsencePolicy' ) ),
											TTSField::new( 'available_balance' )->setType( 'text' )->setLabel( TTi18n::getText( 'Available Balance' ) ),
											TTSField::new( 'start_date_stamp' )->setType( 'date' )->setLabel( TTi18n::getText( 'Date' ) ),
											TTSField::new( 'start_date_stamps' )->setType( 'multi-date' )->setLabel( TTi18n::getText( 'Date' ) ),
											TTSField::new( 'start_dates' )->setType( 'multi-date' )->setLabel( TTi18n::getText( 'Date' ) ),
											TTSField::new( 'start_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'In' ) ),
											TTSField::new( 'end_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Out' ) ),
											TTSField::new( 'total_time' )->setType( 'text' )->setLabel( TTi18n::getText( 'Total' ) ),
											TTSField::new( 'schedule_policy_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Schedule Policy' ) )->setDataSource( TTSAPI::new( 'APISchedulePolicy' )->setMethod( 'getSchedulePolicy' ) ),
											TTSField::new( 'branch_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Branch' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) ),
											TTSField::new( 'department_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Department' ) )->setDataSource( TTSAPI::new( 'APIDepartment' )->setMethod( 'getDepartment' ) ),
											TTSField::new( 'note' )->setType( 'text' )->setLabel( TTi18n::getText( 'Note' ) ),
											TTSField::new( 'notify_user_schedule_change' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Notify Employee' ) )
									)
							),
							TTSTab::new( 'tab_audit' )->setLabel( TTi18n::getText( 'Audit' ) )->setInitCallback( 'initSubLogView' )->setDisplayOnMassEdit( false )
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
							TTSSearchField::new( 'group_id' )->setType( 'uuid' )->setColumn( 'd.group_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid' )->setColumn( 'd.legal_entity_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid' )->setColumn( 'd.default_branch_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid' )->setColumn( 'd.default_department_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid' )->setColumn( 'd.title_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'branch_id' )->setType( 'uuid' )->setColumn( 'a.branch_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'department_id' )->setType( 'uuid' )->setColumn( 'a.department_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'a.status_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'schedule_policy_id' )->setType( 'uuid' )->setColumn( 'a.schedule_policy_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'absence_policy_id' )->setType( 'uuid' )->setColumn( 'a.absence_policy_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'pay_period_id' )->setType( 'uuid' )->setColumn( 'a.pay_period_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'job_status_id' )->setType( 'integer' )->setColumn( 'w.status_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'include_job_id' )->setType( 'uuid' )->setColumn( 'a.job_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_job_id' )->setType( 'uuid' )->setColumn( 'a.job_id' )->setMulti( true ),

							TTSSearchField::new( 'job_group_id' )->setType( 'uuid' )->setColumn( 'w.group_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'job_item_id' )->setType( 'uuid' )->setColumn( 'a.job_item_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'punch_tag_id' )->setType( 'jsonb_uuid_array' )->setColumn( 'a.punch_tag_id' )->setMulti( true )->setVisible( 'AI', true ),

							TTSSearchField::new( 'date_stamp' )->setType( 'date_range_datestamp' )->setColumn( 'a.date_stamp' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'start_date' )->setType( 'start_datestamp' )->setColumn( 'a.date_stamp' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'end_date' )->setType( 'end_datestamp' )->setColumn( 'a.date_stamp' )->setVisible( 'AI', true ),

							TTSSearchField::new( 'start_time' )->setType( 'start_timestamp' )->setColumn( 'a.start_time' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'end_time' )->setType( 'end_timestamp' )->setColumn( 'a.end_time' )->setVisible( 'AI', true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APISchedule' )->setMethod( 'getSchedule' )
									->setSummary( 'Get employee schedule records.' )
									->setModelKeywords( 'schedule employee working punch shift absence sick pto vacation' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APISchedule' )->setMethod( 'setSchedule' )
									->setSummary( 'Add or edit schedule records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APISchedule' )->setMethod( 'deleteSchedule' )
									->setSummary( 'Delete schedule records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APISchedule' )->setMethod( 'getSchedule' ) ),
											   ) ),
							TTSAPI::new( 'APISchedule' )->setMethod( 'getScheduleDefaultData' )
									->setSummary( 'Get default schedule data used for creating new schedules. Use this before calling setSchedule to get the correct default data.' ),
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
			case 'type':
				$retval = [

					//10  => TTi18n::gettext('OPEN'), //Available to be covered/overridden.
					//20 => TTi18n::gettext('Manual'),
					//30 => TTi18n::gettext('Recurring')
					//90  => TTi18n::gettext('Replaced'), //Replaced by another shift. Set replaced_id

					//Not displayed on schedules, used to overwrite recurring schedule if we want to change a 8AM-5PM recurring schedule
					//with a 6PM-11PM schedule? Although this can be done with an absence shift as well...
					//100 => TTi18n::gettext('Hidden'),
				];
				break;
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

				$retval = $this->getCustomFieldsColumns( $retval, null );

				if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
					$retval['-1180-job'] = TTi18n::gettext( 'Job' );
					$retval['-1190-job_item'] = TTi18n::gettext( 'Task' );
					$retval['-1300-punch_tag'] = TTi18n::gettext( 'Tags' );
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
				'pay_period_id'         => 'PayPeriod',
				'replaced_id'           => 'ReplacedId',

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
				'punch_tag'			    => false,
				'note'                  => 'Note',
				'total_time'            => 'TotalTime',

				'overwrite'                   => 'EnableOverwrite',
				'notify_user_schedule_change' => 'EnableNotifyUserScheduleChange',

				'recurring_schedule_template_control_id' => 'RecurringScheduleTemplateControl',

				'note' => 'Note',

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|UserFactory
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
			} else if ( TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID()
					&& TTUUID::isUUID( $this->getCompany() ) && $this->getCompany() != TTUUID::getZeroID() && $this->getCompany() != TTUUID::getNotExistID() ) {
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
		Debug::Text( 'Company ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

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
	function getPayPeriod() {
		return $this->getGenericDataValue( 'pay_period_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayPeriod( $value = null ) {
		if ( $value == null && $this->getUser() != '' && $this->getUser() != TTUUID::getZeroID() ) { //Don't attempt to find pay period if user_id is not specified.
			$value = PayPeriodListFactory::findPayPeriod( $this->getUser(), $this->getDateStamp() );
		}
		$value = TTUUID::castUUID( $value );
		//Allow NULL pay period, incase its an absence or something in the future.
		//Cron will fill in the pay period later.
		return $this->setGenericDataValue( 'pay_period_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getReplacedId() {
		return $this->getGenericDataValue( 'replaced_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setReplacedId( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'replaced_id', $value );
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
				if ( !is_numeric( $value ) ) {                                         //Optimization to avoid converting it when run in CalculatePolicy's loops
					$value = TTDate::getMiddleDayEpoch( TTDate::strtotime( $value ) ); //Make sure we use middle day epoch when pulling the value from the DB the first time, to match setDateStamp() below. Otherwise setting the datestamp then getting it again before save won't match the same value after its saved to the DB.
					$this->setGenericDataValue( 'date_stamp', $value );
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
	function setDateStamp( $value ) {
		$value = (int)$value;

		if ( $value > 0 ) {
			//Use middle day epoch to help avoid confusion with different timezones/DST. -- getDateStamp() needs to use middle day epoch too then.
			//See comments about timezones in CalculatePolicy->_calculate().
			$retval = $this->setGenericDataValue( 'date_stamp', TTDate::getMiddleDayEpoch( $value ) );

			$this->setPayPeriod(); //Force pay period to be set as soon as the date is.

			return $retval;
		}

		return false;
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
		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultBranch();
			Debug::Text( 'Using Default Branch: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}

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

		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultDepartment();
			Debug::Text( 'Using Default Department: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}

		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}

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
		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultJob();
			Debug::Text( 'Using Default Job: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

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
		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && $value == TTUUID::getNotExistID() ) { //Find default
			$value = $this->getUserObject()->getDefaultJobItem();
			Debug::Text( 'Using Default Job Item: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		}
		//If $id = -1 (default) makes it to this step (likely due to being an OPEN shift), force it to 0.
		if ( $value == TTUUID::getNotExistID() ) {
			$value = TTUUID::getZeroID();
		}
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

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
	 * @param string | array $value UUID
	 * @return bool
	 */
	function setPunchTag( $value ) {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = null;
		}

		if ( $this->getUser() != '' && is_object( $this->getUserObject() ) && ( $value == TTUUID::getNotExistID() || ( is_array( $value ) && in_array( TTUUID::getNotExistID(), $value, true ) ) ) ) { //Find default
			$value = $this->getUserObject()->getDefaultPunchTag();
			Debug::Text( 'Using Default Punch Tag: ' . implode( ',', (array)$value ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( ( $this->getUser() == TTUUID::getZeroID() && $value == TTUUID::getNotExistID() ) || $value == TTUUID::getZeroID() || empty( $value ) || ( is_array( $value ) && count( $value ) == 1 && isset( $value[0] ) && $value[0] == TTUUID::getZeroID() ) ) {
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
	 * Find the difference between $epoch and the schedule time, so we can determine the best schedule that fits.
	 * *NOTE: This returns FALSE when it doesn't match, so make sure you do an exact comparison using ===
	 * @param int $epoch EPOCH
	 * @param bool $status_id
	 * @return bool|int
	 */
	function inScheduleDifference( $epoch, $status_id = false ) {
		if ( $epoch >= $this->getStartTime() && $epoch <= $this->getEndTime() ) {
			Debug::text( 'Within Schedule: ' . $epoch, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $this->getStatus() == 10 ) { //10=Working
				//Need to handle two schedule shifts like: Feb 15th: 7A - 7A (24hr shift), then a Feb 16th: 7A - 7P (12hr shift immediately after)
				// If its a OUT punch on Feb 16th at 7A then it should match the first schedule, and if its an IN punch on Feb 16th, it should match the 2nd schedule.
				if ( ( $status_id == 10 && $epoch == $this->getEndTime() ) || ( $status_id == 20 && $epoch == $this->getStartTime() ) ) {
					$retval = 0.5; //Epoch matches exact start/end schedule time, but the status doesn't quite match, so make it slightly more than 0 in case there is an exact match on a different scheduled shift.
				} else {
					$retval = 0; //Within schedule start/end time, no difference.
				}
			} else if ( $this->getStatus() == 20 ) { //20=Absence
				//Handle case where scheduled 7A-9A (Absent), 9A - 4P (Working), and they punch In at 8:45AM. It should be matched to the Working shift, not the absent shift.
				// Also the case where scheduled 9A - 4P (Working), 4P-5P (Absent) and they punch Out at 4:15PM. It should be matched to the Working shift, not the absent shift.
				//  It will do this by returning the same difference as what would be calculated for the working shift, and therefore the working shift will take priority.
				if ( ( $status_id == false || $status_id == 10 ) && $this->inStopWindow( $epoch ) == true ) { //Punching In near stop window of Absence schedule shift.
					$retval = ( $this->getEndTime() - $epoch );
				} else if ( ( $status_id == false || $status_id == 20 ) && $this->inStartWindow( $epoch ) == true ) { //Punching Out near start window of Absence schedule shift.
					$retval = ( $epoch - $this->getStartTime() );
				} else {
					$retval = 0; //Within schedule start/end time and not near start/stop window, no difference.
				}
			}
		} else {
			if ( ( $status_id == false || $status_id == 10 ) && $epoch < $this->getStartTime() && $this->inStartWindow( $epoch ) == true ) {
				$retval = ( $this->getStartTime() - $epoch );
			} else if ( ( $status_id == false || $status_id == 20 ) && $epoch > $this->getEndTime() && $this->inStopWindow( $epoch ) == true ) {
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
	 * @return int
	 */
	function getStartStopWindow() {
		if ( is_object( $this->getSchedulePolicyObject() ) ) {
			$start_stop_window = (int)$this->getSchedulePolicyObject()->getStartStopWindow();
		} else {
			$start_stop_window = ( 3600 * 2 ); //Default to 2hr to help avoid In Late exceptions when they come in too early.
		}

		return $start_stop_window;
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

		$start_stop_window = $this->getStartStopWindow();
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

		$start_stop_window = $this->getStartStopWindow();
		if ( $epoch >= ( $this->getEndTime() - $start_stop_window ) && $epoch <= ( $this->getEndTime() + $start_stop_window ) ) {
			Debug::text( ' Within Start/Stop window: ' . $start_stop_window, __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		//Debug::text(' NOT Within Stop window. Epoch: '. $epoch .' Window: '. $start_stop_window, __FILE__, __LINE__, __METHOD__, 10);
		return false;
	}

	/**
	 * @param $schedule_shifts
	 * @param $recurring_schedule_shifts
	 * @return mixed
	 */
	function mergeScheduleArray( $schedule_shifts, $recurring_schedule_shifts ) {
		//Debug::text('Merging Schedule, and Recurring Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		$ret_arr = $schedule_shifts;

		//Debug::Arr($schedule_shifts, '(c) Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( is_array( $recurring_schedule_shifts ) && count( $recurring_schedule_shifts ) > 0 ) {
			foreach ( $recurring_schedule_shifts as $date_stamp => $day_shifts_arr ) {
				//Debug::text('----------------------------------', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::text('Date Stamp: '. TTDate::getDate('DATE+TIME', $date_stamp). ' Epoch: '. $date_stamp, __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($schedule_shifts[$date_stamp], 'Date Arr: ', __FILE__, __LINE__, __METHOD__, 10);
				foreach ( $day_shifts_arr as $shift_arr ) {

					if ( isset( $ret_arr[$date_stamp] ) ) {
						//Debug::text('Already Schedule Shift on this day: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);

						//Loop through each shift on this day, and check for overlaps
						//Only include the recurring shift if ALL times DO NOT overlap
						$overlap = 0;
						foreach ( $ret_arr[$date_stamp] as $tmp_shift_arr ) {
							if ( TTDate::isTimeOverLap( $shift_arr['start_time'], $shift_arr['end_time'], $tmp_shift_arr['start_time'], $tmp_shift_arr['end_time'] ) ) {
								//Debug::text('Times OverLap: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
								$overlap++;
							} //else { //Debug::text('Times DO NOT OverLap: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
						}

						if ( $overlap == 0 ) {
							//Debug::text('NO Times OverLap, using recurring schedule: '. TTDate::getDate('DATE+TIME', $shift_arr['start_time']), __FILE__, __LINE__, __METHOD__, 10);
							$ret_arr[$date_stamp][] = $shift_arr;
						}
					} else {
						//Debug::text('No Schedule Shift on this day: '. TTDate::getDate('DATE', $date_stamp), __FILE__, __LINE__, __METHOD__, 10);
						$ret_arr[$date_stamp][] = $shift_arr;
					}
				}
			}
		}

		return $ret_arr;
	}

	/**
	 * @param $filter_data
	 * @return array|bool
	 */
	function getScheduleArray( $filter_data ) {
		global $current_user;

		//Get all schedule data by general filter criteria.
		//Debug::Arr($filter_data, 'Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

		if ( !isset( $filter_data['start_date'] ) || $filter_data['start_date'] == '' ) {
			return false;
		}

		if ( !isset( $filter_data['end_date'] ) || $filter_data['end_date'] == '' ) {
			return false;
		}

		$filter_data['start_date'] = TTDate::getBeginDayEpoch( $filter_data['start_date'] );
		$filter_data['end_date'] = TTDate::getEndDayEpoch( $filter_data['end_date'] );

		$pcf = TTnew( 'PayCodeFactory' ); /** @var PayCodeFactory $pcf */
		$absence_policy_paid_type_options = $pcf->getOptions( 'paid_type' );

		$max_i = 0;

		$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		if ( isset( $filter_data['filter_items_per_page'] ) ) {
			if ( !isset( $filter_data['filter_page'] ) ) {
				$filter_data['filter_page'] = 1;
			}
			$slf->getSearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data, $filter_data['filter_items_per_page'], $filter_data['filter_page'] );
		} else {
			$slf->getSearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data );
		}
		Debug::text( 'Found Scheduled Rows: ' . $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr($absence_policy_paid_type_options, 'Paid Absences: ', __FILE__, __LINE__, __METHOD__, 10);
		$scheduled_user_ids = [];
		if ( $slf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $slf->getRecordCount(), null, TTi18n::getText( 'Processing Committed Shifts...' ) );

			$schedule_shifts = [];
			$i = 0;
			foreach ( $slf as $s_obj ) {
				if ( TTUUID::castUUID( $s_obj->getUser() ) == TTUUID::getZeroID() && ( getTTProductEdition() == TT_PRODUCT_COMMUNITY || $current_user->getCompanyObject()->getProductEdition() == 10 ) ) {
					continue;
				}

				//Debug::text('Schedule ID: '. $s_obj->getId() .' User ID: '. $s_obj->getUser() .' Start Time: '. $s_obj->getStartTime(), __FILE__, __LINE__, __METHOD__, 10);
				if ( TTUUID::isUUID( $s_obj->getAbsencePolicyID() ) && $s_obj->getAbsencePolicyID() != TTUUID::getZeroID() && $s_obj->getAbsencePolicyID() != TTUUID::getNotExistID() ) {
					$absence_policy_name = $s_obj->getColumn( 'absence_policy' );
				} else {
					$absence_policy_name = null; //Must be NULL for it to appear as "N/A" in legacy interface.
				}

				if ( $s_obj->getStatus() == 20 //Absence
						&&
						(
								$s_obj->getAbsencePolicyID() == TTUUID::getZeroID()
								||
								(
										TTUUID::isUUID( $s_obj->getAbsencePolicyID() ) && $s_obj->getAbsencePolicyID() != TTUUID::getZeroID() && $s_obj->getAbsencePolicyID() != TTUUID::getNotExistID()
										&& is_object( $s_obj->getAbsencePolicyObject() )
										&& is_object( $s_obj->getAbsencePolicyObject()->getPayCodeObject() )
										&& in_array( $s_obj->getAbsencePolicyObject()->getPayCodeObject()->getType(), $absence_policy_paid_type_options ) == false
								)
						) ) {
					//UnPaid Absence.
					$total_time_wage = TTMath::MoneyRound( 0 );
				} else {
					$total_time_wage = TTMath::MoneyRound( TTMath::mul( TTDate::getHours( $s_obj->getColumn( 'total_time' ) ), $s_obj->getColumn( 'user_wage_hourly_rate' ) ) );
				}

				//v11.5.0 change ISO date stamp to have dashes in it, but that caused problems with existing versions of the app which were expecting no dashes.
				// So we need to return the old ISO date format for old app versions.
				$iso_date_stamp = TTDate::getISODateStamp( $s_obj->getDateStamp() );
				if ( isset( $_SERVER['HTTP_USER_AGENT'] ) && $_SERVER['HTTP_USER_AGENT'] != '' ) {
					if ( stripos( $_SERVER['HTTP_USER_AGENT'], 'App: v' ) !== false && version_compare( substr( $_SERVER['HTTP_USER_AGENT'], ( stripos( $_SERVER['HTTP_USER_AGENT'], 'App: v' ) + 6 ) ), '4.0.26', '<=' ) ) {
						$iso_date_stamp = date( 'Ymd', $s_obj->getDateStamp() );
					}
				}

				if ( TTUUID::isUUID( $s_obj->getUser() ) && $s_obj->getUser() != TTUUID::getZeroID() && $s_obj->getUser() != TTUUID::getNotExistID() ) {
					$first_name = $s_obj->getColumn( 'first_name' );
					$user_full_name = Misc::getFullName( $first_name, null, $s_obj->getColumn( 'last_name' ), false, false );
				} else {
					$first_name = $user_full_name = TTi18n::getText( 'OPEN' );
				}

				$schedule_shifts[$iso_date_stamp][$i] = [
						'id'                    => TTUUID::castUUID( $s_obj->getID() ),
						'replaced_id'           => TTUUID::castUUID( $s_obj->getReplacedID() ),
						'recurring_schedule_id' => $s_obj->getColumn( 'recurring_schedule_id' ),
						'pay_period_id'         => $s_obj->getColumn( 'pay_period_id' ),
						'user_id'               => TTUUID::castUUID( $s_obj->getUser() ),
						'user_created_by'       => $s_obj->getColumn( 'user_created_by' ),
						'user_full_name'        => $user_full_name,
						'first_name'            => $first_name,
						'last_name'             => $s_obj->getColumn( 'last_name' ),
						'title_id'              => $s_obj->getColumn( 'title_id' ),
						'title'                 => $s_obj->getColumn( 'title' ),
						'group_id'              => $s_obj->getColumn( 'group_id' ),
						'group'                 => $s_obj->getColumn( 'group' ),
						'default_branch_id'     => $s_obj->getColumn( 'default_branch_id' ),
						'default_branch'        => $s_obj->getColumn( 'default_branch' ),
						'default_department_id' => $s_obj->getColumn( 'default_department_id' ),
						'default_department'    => $s_obj->getColumn( 'default_department' ),
						'default_job_id'        => $s_obj->getColumn( 'default_job_id' ),
						'default_job'           => $s_obj->getColumn( 'default_job' ),
						'default_job_item_id'   => $s_obj->getColumn( 'default_job_item_id' ),
						'default_job_item'      => $s_obj->getColumn( 'default_job_item' ),
						'default_punch_tag_id'  => json_decode( $s_obj->getColumn( 'default_punch_tag_id' ), true ),

						'job_id'            => TTUUID::castUUID( $s_obj->getColumn( 'job_id' ) ),
						'job'               => $s_obj->getColumn( 'job' ),
						'job_status_id'     => (int)$s_obj->getColumn( 'job_status_id' ),
						'job_manual_id'     => (int)$s_obj->getColumn( 'job_manual_id' ),
						'job_branch_id'     => $s_obj->getColumn( 'job_branch_id' ),
						'job_department_id' => $s_obj->getColumn( 'job_department_id' ),
						'job_group_id'      => $s_obj->getColumn( 'job_group_id' ),

						'job_address1'      => $s_obj->getColumn( 'job_address1' ),
						'job_address2'      => $s_obj->getColumn( 'job_address2' ),
						'job_city'          => $s_obj->getColumn( 'job_city' ),
						'job_country'       => $s_obj->getColumn( 'job_country' ),
						'job_province'      => $s_obj->getColumn( 'job_province' ),
						'job_postal_code'   => $s_obj->getColumn( 'job_postal_code' ),
						'job_longitude'     => $s_obj->getColumn( 'job_longitude' ),
						'job_latitude'      => $s_obj->getColumn( 'job_latitude' ),
						'job_location_note' => $s_obj->getColumn( 'job_location_note' ),

						'job_item_id' => TTUUID::castUUID( $s_obj->getColumn( 'job_item_id' ) ),
						'job_item'    => $s_obj->getColumn( 'job_item' ),

						'punch_tag_id' => json_decode( $s_obj->getColumn( 'punch_tag_id' ), true ),
						'punch_tag' => $s_obj->getColumn( 'punch_tag' ),

						'type_id'   => 10, //Committed
						'status_id' => (int)$s_obj->getStatus(),

						'date_stamp'       => TTDate::getAPIDate( 'DATE', $s_obj->getDateStamp() ), //Date the schedule is displayed on
						'start_date_stamp' => TTDate::getAPIDate( 'DATE', $s_obj->getStartTime() ), //Date the schedule starts on.
						'start_date'       => TTDate::getAPIDate( 'DATE+TIME', $s_obj->getStartTime() ),
						'end_date'         => TTDate::getAPIDate( 'DATE+TIME', $s_obj->getEndTime() ),
						'end_date_stamp'   => TTDate::getAPIDate( 'DATE', $s_obj->getEndTime() ), //Date the schedule ends on.
						'start_time'       => TTDate::getAPIDate( 'TIME', $s_obj->getStartTime() ),
						'end_time'         => TTDate::getAPIDate( 'TIME', $s_obj->getEndTime() ),

						'start_time_stamp' => $s_obj->getStartTime(),
						'end_time_stamp'   => $s_obj->getEndTime(),

						'total_time' => $s_obj->getTotalTime(),

						'hourly_rate'     => TTMath::MoneyRound( $s_obj->getColumn( 'user_wage_hourly_rate' ) ),
						'total_time_wage' => $total_time_wage,

						'note' => $s_obj->getColumn( 'note' ),

						'schedule_policy_id' => TTUUID::castUUID( $s_obj->getSchedulePolicyID() ),
						'absence_policy_id'  => TTUUID::castUUID( $s_obj->getAbsencePolicyID() ),
						'absence_policy'     => $absence_policy_name,
						'branch_id'          => TTUUID::castUUID( $s_obj->getBranch() ),
						'branch'             => $s_obj->getColumn( 'branch' ),
						'department_id'      => TTUUID::castUUID( $s_obj->getDepartment() ),
						'department'         => $s_obj->getColumn( 'department' ),

						'recurring_schedule_template_control_id' => $s_obj->getRecurringScheduleTemplateControl(),

						'created_by_id' => TTUUID::castUUID( $s_obj->getCreatedBy() ),
						'created_date'  => $s_obj->getCreatedDate(),
						'updated_date'  => $s_obj->getUpdatedDate(),

						'is_owner' => (bool)$s_obj->getColumn( 'is_owner' ),
						'is_child' => (bool)$s_obj->getColumn( 'is_child' ),
				];

				//Make sure we add in permission columns. They come from SQL now, so we don't need to use getPermissionColumns() at all anymore, let alone pass in $permission_children_ids
				//$this->getPermissionColumns( $schedule_shifts[$iso_date_stamp][$i], TTUUID::castUUID($s_obj->getUser()), $s_obj->getCreatedBy(), $permission_children_ids );

				unset( $absence_policy_name );

				if ( isset( $filter_data['include_all_users'] ) && $filter_data['include_all_users'] == true ) {
					$scheduled_user_ids[] = TTUUID::castUUID( $s_obj->getUser() ); //Used below if
				}

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $slf->getCurrentRow() );

				$i++;
			}
			$max_i = $i;
			unset( $i );

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			//Debug::Arr($schedule_shifts, 'Committed Schedule Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
			Debug::text( 'Processed Scheduled Rows: ' . $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			$schedule_shifts = [];
		}
		unset( $slf );

		//Include employees without scheduled shifts.
		if ( isset( $filter_data['include_all_users'] ) && $filter_data['include_all_users'] == true ) {
			if ( !isset( $filter_data['exclude_id'] ) ) {
				$filter_data['exclude_id'] = [];
			}

			//If the user is searching for scheduled branch/departments, convert that to default branch/departments when Show All Employees is enabled.
			if ( isset( $filter_data['branch_ids'] ) && !isset( $filter_data['default_branch_ids'] ) ) {
				$filter_data['default_branch_ids'] = $filter_data['branch_ids'];
			}
			if ( isset( $filter_data['department_ids'] ) && !isset( $filter_data['default_department_ids'] ) ) {
				$filter_data['default_department_ids'] = $filter_data['department_ids'];
			}

			$scheduled_user_ids = ( empty( $scheduled_user_ids ) == false ) ? array_unique( $scheduled_user_ids ) : [];
			$filter_data['exclude_id'] = array_merge( $filter_data['exclude_id'], $scheduled_user_ids );
			if ( isset( $filter_data['exclude_id'] ) ) {
				//Debug::Arr($filter_data['exclude_id'], 'Including all employees. Excluded User Ids: ', __FILE__, __LINE__, __METHOD__, 10);
				//Debug::Arr($filter_data, 'All Filter Data: ', __FILE__, __LINE__, __METHOD__, 10);

				//Only include active employees without any scheduled shifts.
				$filter_data['status_id'] = 10;

				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getAPISearchByCompanyIdAndArrayCriteria( $current_user->getCompany(), $filter_data );
				Debug::text( 'Found blank employees: ' . $ulf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $ulf->getRecordCount() > 0 ) {
					$this->getProgressBarObject()->start( $this->getAPIMessageID(), $ulf->getRecordCount(), null, TTi18n::getText( 'Processing Employees...' ) );

					$i = $max_i;
					foreach ( $ulf as $u_obj ) {
						//Create dummy shift arrays with no start/end time.
						//$schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$u_obj->getID().TTDate::getBeginDayEpoch($filter_data['start_date'])] = array(
						$schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$i] = [
							//'id' => TTUUID::castUUID($u_obj->getID()),
							'pay_period_id'         => false,
							'user_id'               => TTUUID::castUUID( $u_obj->getID() ),
							'user_created_by'       => TTUUID::castUUID( $u_obj->getCreatedBy() ),
							'user_full_name'        => Misc::getFullName( $u_obj->getFirstName(), null, $u_obj->getLastName(), false, false ),
							'first_name'            => $u_obj->getFirstName(),
							'last_name'             => $u_obj->getLastName(),
							'title_id'              => $u_obj->getTitle(),
							'title'                 => $u_obj->getColumn( 'title' ),
							'group_id'              => $u_obj->getColumn( 'group_id' ),
							'group'                 => $u_obj->getColumn( 'group' ),
							'default_branch_id'     => $u_obj->getColumn( 'default_branch_id' ),
							'default_branch'        => $u_obj->getColumn( 'default_branch' ),
							'default_department_id' => $u_obj->getColumn( 'default_department_id' ),
							'default_department'    => $u_obj->getColumn( 'default_department' ),
							'default_job_id'        => $u_obj->getColumn( 'default_job_id' ),
							'default_job'           => $u_obj->getColumn( 'default_job' ),
							'default_job_item_id'   => $u_obj->getColumn( 'default_job_item_id' ),
							'default_job_item'      => $u_obj->getColumn( 'default_job_item' ),
							'default_punch_tag_id'  => json_decode( $u_obj->getColumn( 'default_punch_tag_id' ), true ),

							'branch_id'     => TTUUID::castUUID( $u_obj->getDefaultBranch() ),
							'branch'        => $u_obj->getColumn( 'default_branch' ),
							'department_id' => TTUUID::castUUID( $u_obj->getDefaultDepartment() ),
							'department'    => $u_obj->getColumn( 'default_department' ),

							'job_id'     => TTUUID::castUUID( $u_obj->getDefaultJob() ),
							'job'        => $u_obj->getColumn( 'default_job' ),
							'job_item_id' => TTUUID::castUUID( $u_obj->getDefaultJobItem() ),
							'job_item'    => $u_obj->getColumn( 'default_job_item' ),

							'punch_tag_id'    => $u_obj->getDefaultPunchTag(),

							'created_by_id' => $u_obj->getCreatedBy(),
							'created_date'  => $u_obj->getCreatedDate(),
							'updated_date'  => $u_obj->getUpdatedDate(),
						];

						//Make sure we add in permission columns.
						$this->getPermissionColumns( $schedule_shifts[TTDate::getISODateStamp( $filter_data['start_date'] )][$i], TTUUID::castUUID( $u_obj->getID() ), $u_obj->getCreatedBy() );

						$this->getProgressBarObject()->set( $this->getAPIMessageID(), $ulf->getCurrentRow() );

						$i++;
					}

					$this->getProgressBarObject()->stop( $this->getAPIMessageID() );
				}
			}
			//Debug::Arr($schedule_shifts, 'Final Scheduled Shifts: ', __FILE__, __LINE__, __METHOD__, 10);
		}

		if ( isset( $schedule_shifts ) ) {
			return $schedule_shifts;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getEnableSplitAtMidnight() {
		if ( isset( $this->split_at_midnight ) ) {
			return $this->split_at_midnight;
		}

		return true;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableSplitAtMidnight( $bool ) {
		$this->split_at_midnight = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableReCalculateDay() {
		if ( isset( $this->recalc_day ) ) {
			return $this->recalc_day;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableReCalculateDay( $bool ) {
		$this->recalc_day = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableOverwrite() {
		if ( isset( $this->overwrite ) ) {
			return $this->overwrite;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableOverwrite( $bool ) {
		$this->overwrite = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableTimeSheetVerificationCheck() {
		if ( isset( $this->timesheet_verification_check ) ) {
			return $this->timesheet_verification_check;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableTimeSheetVerificationCheck( $bool ) {
		$this->timesheet_verification_check = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableNotifications() {
		if ( isset( $this->notifications ) ) {
			return $this->notifications;
		}

		return true; //Enable by default.
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableNotifications( $bool ) {
		$this->notifications = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableNotifyUserScheduleChange() {
		if ( isset( $this->notify_user_schedule_change ) ) {
			return $this->notify_user_schedule_change;
		}

		return false; //Disabled by default.
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableNotifyUserScheduleChange( $bool ) {
		$this->notify_user_schedule_change = $bool;

		return true;
	}

	function doesSpanMidnight() {
		if ( $this->getStartTime() != '' && $this->getEndTime() != '' && TTDate::doesRangeSpanMidnight( $this->getStartTime(), $this->getEndTime() ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function handleDayBoundary() {
		//Debug::Text('Start Time '.TTDate::getDate('DATE+TIME', $this->getStartTime()) .'('.$this->getStartTime().')  End Time: '. TTDate::getDate('DATE+TIME', $this->getEndTime()).'('.$this->getEndTime().')', __FILE__, __LINE__, __METHOD__, 10);

		//This used to be done in Validate, but needs to be done in preSave too.
		//Allow 12:00AM to 12:00AM schedules for a total of 24hrs.
		if ( $this->getStartTime() != '' && $this->getEndTime() != '' && $this->getEndTime() <= $this->getStartTime() ) { //This cannot use $this->doesSpanMidnight() as this is only used when initially inserting for the first time, then the start/end timestamps are on different days.
			//Since the initial end time is the same date as the start time, we need to see if DST affects between that end time and one day later. NOT the start time.
			//Due to DST, always pay the employee based on the time they actually worked,
			//which is handled automatically by simple epoch math.
			//Therefore in fall they get paid one hour more, and spring one hour less.
			//$this->setEndTime( $this->getEndTime() + ( 86400 + (TTDate::getDSTOffset( $this->getEndTime(), ($this->getEndTime() + 86400) ) ) ) ); //End time spans midnight, add 24hrs.
			$this->setEndTime( strtotime( '+1 day', (int)$this->getEndTime() ) ); //Using strtotime handles DST properly, whereas adding 86400 causes strange behavior.
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
		$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getConflictingByCompanyIdAndUserIdAndStartDateAndEndDate( $this->getCompany(), $this->getUser(), $this->getStartTime(), $this->getEndTime(), TTUUID::castUUID( $this->getID() ) );
		if ( $slf->getRecordCount() > 0 ) {
			foreach ( $slf as $conflicting_schedule_shift_obj ) {
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
	 * Add punches to match a schedule shift object
	 * @param object $rs_obj
	 * @return bool
	 */
	function addPunchFromScheduleObject( $rs_obj ) {
		//Make sure they are working for Auto-fill to apply.
		Debug::text( 'Adding punch from schedule object...', __FILE__, __LINE__, __METHOD__, 10 );

		if ( TTUUID::isUUID( $rs_obj->getUser() ) && $rs_obj->getUser() != TTUUID::getZeroID() && $rs_obj->getUser() != TTUUID::getNotExistID() ) {
			$permission_obj = new Permission();

			//Check permissions to see which timesheet the user has access too, so we know if we are actually punching, or filling in a manual timesheet.
			//  The user must have permissions to view the manual time sheet. Then if they are a supervisor and can view punch timesheets too, they must not have permissions to punch in/out for the manual timesheet to be populated.
			if ( ( $permission_obj->Check( 'punch', 'manual_timesheet', $rs_obj->getUser(), $rs_obj->getUserObject()->getCompany() ) == true && ( $permission_obj->Check( 'punch', 'punch_timesheet', $rs_obj->getUser(), $rs_obj->getUserObject()->getCompany() ) == false || $permission_obj->Check( 'punch', 'punch_in_out', $rs_obj->getUser(), $rs_obj->getUserObject()->getCompany() ) == false ) ) ) {
				$transaction_function = function () use ( $rs_obj ) {
					//Manual TimeSheet.
					$udt = new UserDateTotalFactory();
					$udt->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing duplicate user records or duplicate employee number/user_names.
					$udt->StartTransaction();

					$udt->setUser( $rs_obj->getUser() );
					$udt->setObjectType( 10 ); //10=Worked
					$udt->setDateStamp( $rs_obj->getDateStamp() );

					//Specifying the start/end time seems to cause it to double up regular time and generally have the wrong total time.
					//$udt->setStartType( 10 ); //10=Normal
					//$udt->setStartTimeStamp( $rs_obj->getStartTime() );
					//$udt->setEndType( 10 ); //10=Normal
					//$udt->setEndTimeStamp( $rs_obj->getEndTime() );
					//$udt->setTotalTime( $udt->calcTotalTime() );

					$udt->setTotalTime( $rs_obj->getTotalTime() );
					$udt->setOverride( true );

					$udt->setBranch( TTUUID::castUUID( $rs_obj->getBranch() ) );
					$udt->setDepartment( TTUUID::castUUID( $rs_obj->getDepartment() ) );
					$udt->setJob( TTUUID::castUUID( $rs_obj->getJob() ) );
					$udt->setJobItem( TTUUID::castUUID( $rs_obj->getJobItem() ) );
					$udt->setPunchTag( $rs_obj->getPunchTag() );

					$udt->setEnableCalcSystemTotalTime( true );
					$udt->setEnableCalcWeeklySystemTotalTime( true );
					$udt->setEnableCalcException( true );

					if ( $udt->isValid() ) {
						Debug::text( 'Adding Manual TimeSheet Hours: '. $udt->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10 );
						$udt->Save( true, true );
						$retval = true;
					} else {
						Debug::Text( 'NOTICE: Validation failed!', __FILE__, __LINE__, __METHOD__, 10 );
						$retval = false;
					}

					$udt->CommitTransaction();
					$udt->setTransactionMode(); //Back to default isolation level.

					unset( $udt );

					return [ $retval ];
				};
			} else {
				$transaction_function = function () use ( $rs_obj ) {
					//Punch TimeSheet
					$commit_punch_transaction = false;

					$pf_in = new PunchFactory();
					$pf_in->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing duplicate user records or duplicate employee number/user_names.
					$pf_in->StartTransaction();

					$pf_in->setUser( $rs_obj->getUser() );
					$pf_in->setType( 10 );   //Normal
					$pf_in->setStatus( 10 ); //In
					$pf_in->setTimeStamp( $rs_obj->getStartTime(), true );
					$pf_in->setPunchControlID( $pf_in->findPunchControlID() );
					$pf_in->setActualTimeStamp( $pf_in->getTimeStamp() );
					$pf_in->setOriginalTimeStamp( $pf_in->getTimeStamp() );

					if ( $pf_in->isValid() ) {
						Debug::text( 'Punch In: Valid!', __FILE__, __LINE__, __METHOD__, 10 );
						$pf_in->setEnableCalcTotalTime( false );
						$pf_in->setEnableCalcSystemTotalTime( false );
						$pf_in->setEnableCalcUserDateTotal( false );
						$pf_in->setEnableCalcException( false );

						$pf_in->Save( false );
					} else {
						Debug::text( 'Punch In: InValid!', __FILE__, __LINE__, __METHOD__, 10 );
					}

					Debug::text( 'Punch Out: ' . TTDate::getDate( 'DATE+TIME', $rs_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );
					$pf_out = new PunchFactory();
					$pf_out->setUser( $rs_obj->getUser() );
					$pf_out->setType( 10 );   //Normal
					$pf_out->setStatus( 20 ); //Out
					$pf_out->setTimeStamp( $rs_obj->getEndTime(), true );
					$pf_out->setPunchControlID( $pf_in->findPunchControlID() ); //Use the In punch object to find the punch_control_id.
					$pf_out->setActualTimeStamp( $pf_out->getTimeStamp() );
					$pf_out->setOriginalTimeStamp( $pf_out->getTimeStamp() );

					if ( $pf_out->isValid() ) {
						Debug::text( 'Punch Out: Valid!', __FILE__, __LINE__, __METHOD__, 10 );
						$pf_out->setEnableCalcTotalTime( true );
						$pf_out->setEnableCalcSystemTotalTime( true );
						$pf_out->setEnableCalcUserDateTotal( true );
						$pf_out->setEnableCalcException( true );

						$pf_out->Save( false );
					} else {
						Debug::text( 'Punch Out: InValid!', __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( $pf_in->isValid() == true || $pf_out->isValid() == true ) {
						Debug::text( 'Punch In and Out succeeded, saving punch control!', __FILE__, __LINE__, __METHOD__, 10 );

						$pcf = new PunchControlFactory();
						$pcf->setId( $pf_in->getPunchControlID() );

						if ( $pf_in->isValid() == true ) {
							$pcf->setPunchObject( $pf_in );
						} else if ( $pf_out->isValid() == true ) {
							$pcf->setPunchObject( $pf_out );
						}

						$pcf->setBranch( TTUUID::castUUID( $rs_obj->getBranch() ) );
						$pcf->setDepartment( TTUUID::castUUID( $rs_obj->getDepartment() ) );
						$pcf->setJob( TTUUID::castUUID( $rs_obj->getJob() ) );
						$pcf->setJobItem( TTUUID::castUUID( $rs_obj->getJobItem() ) );
						$pcf->setPunchTag( $rs_obj->getPunchTag() );

						$pcf->setEnableRequiredFieldCheck( false ); //Ignore required custom fields for now, as they can't be filled out because its an auto-punch and the user isn't triggering it.
						$pcf->setEnableStrictJobValidation( false ); //Disable strict job validation, so punches are still created if the customer disallows a user from the job/task, as its difficult for them to realize the problem. At least this way it might trigger an exception (ie: Not allowed on job) instead, or continue the autopunch with incorrect job/task, so they can fix it later.
						$pcf->setEnableCalcUserDateID( true );
						$pcf->setEnableCalcTotalTime( true );
						$pcf->setEnableCalcSystemTotalTime( true );
						$pcf->setEnableCalcUserDateTotal( true );
						$pcf->setEnableCalcException( true );
						$pcf->setEnablePreMatureException( false ); //Disable pre-mature exceptions at this point.

						if ( $pcf->isValid() ) {
							$pcf->Save( true, true );

							$commit_punch_transaction = true;
						}
					} else {
						Debug::text( 'Punch In and Out failed, not saving punch control!', __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( $commit_punch_transaction == true ) {
						Debug::text( 'Committing Punch Transaction!', __FILE__, __LINE__, __METHOD__, 10 );
						$retval = true;
					} else {
						Debug::text( 'Rolling Back Punch Transaction!', __FILE__, __LINE__, __METHOD__, 10 );
						$pf_in->FailTransaction();
						$retval = false;
					}

					$pf_in->CommitTransaction();
					$pf_in->setTransactionMode(); //Back to default isolation level.

					unset( $pf_in, $pf_out, $pcf );

					return [ $retval ];
				};
			}

			[ $retval ] = $this->RetryTransaction( $transaction_function );

			return $retval;
		} else {
			Debug::text( 'Skipping... User ID is invalid.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
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
			if ( $this->getUser() != '' && $this->getUser() != TTUUID::getZeroID() ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'user',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}
			// Pay Period
			if ( $this->getPayPeriod() !== false && $this->getPayPeriod() != TTUUID::getZeroID() ) {
				$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
				$this->Validator->isResultSetWithRows( 'pay_period',
													   $pplf->getByID( $this->getPayPeriod() ),
													   TTi18n::gettext( 'Invalid Pay Period' )
				);
			}
			// Scheduled Shift to replace.
			// Note: There was a bug where replaced shifts would be deleted due to the conflict check below. Causing the shift that replaced it to throw this validation error if it was later modified.
			//       To replicate it, create a committed OPEN shift, then using Find Available fill it. Then copy an identical shift from the previous day to day the shift was just filled on, and it would delete the replaced shift in the background.
			//       That should be resolved now that we do better checks around the replaced shifts.
			if ( $this->getReplacedId() !== false && $this->getID() != $this->getReplacedId() && $this->getReplacedId() != TTUUID::getZeroID() ) {
				//Make sure we don't replace ourselves.
				$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
				$this->Validator->isResultSetWithRows( 'date_stamp',
													   $slf->getByID( $this->getReplacedId() ),
													   TTi18n::gettext( 'Scheduled Shift to replace does not exist' )
				);
			}
			// Date
			if ( $this->getDateStamp() != '' ) {
				$this->Validator->isDate( 'date_stamp',
										  $this->getDateStamp(),
										  TTi18n::gettext( 'Incorrect date' ) . ' (a)'
				);
				if ( $this->Validator->isError( 'date_stamp' ) == false ) {
					if ( $this->getDateStamp() <= 0 ) {
						$this->Validator->isTRUE( 'date_stamp',
												  false,
												  TTi18n::gettext( 'Incorrect date' ) . ' (b)'
						);
					}
				}
			}
			// Status
			if ( $this->getStatus() != '' ) {
				$this->Validator->inArrayKey( 'status',
											  $this->getStatus(),
											  TTi18n::gettext( 'Incorrect Status' ),
											  $this->getOptions( 'status' )
				);
			}

			// Start time
			if ( $this->getStartTime() != '' ) {
				$this->Validator->isDate( 'start_time',
										  $this->getStartTime(),
										  TTi18n::gettext( 'Incorrect start time' )
				);
			}

			// End time
			if ( $this->getEndTime() != '' ) {
				$this->Validator->isDate( 'end_time',
										  $this->getEndTime(),
										  TTi18n::gettext( 'Incorrect end time' )
				);
				if ( $this->Validator->isError( 'end_time' ) == false && $this->getEndTime() < $this->getStartTime() ) {
					$this->Validator->isTRUE( 'end_time',
											  false,
											  TTi18n::gettext( 'End time must be after start time' ) );
				}
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
			if ( $this->getAbsencePolicyID() != '' && $this->getSchedulePolicyID() != TTUUID::getZeroID() ) {
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
			if ( $this->getBranch() != '' && $this->getBranch() != TTUUID::getZeroID() ) {
				$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
				$this->Validator->isResultSetWithRows( 'branch',
													   $blf->getByID( $this->getBranch() ),
													   TTi18n::gettext( 'Branch does not exist' )
				);
			}
			// Department
			if ( $this->getDepartment() != '' && $this->getDepartment() != TTUUID::getZeroID() ) {
				$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
				$this->Validator->isResultSetWithRows( 'department',
													   $dlf->getByID( $this->getDepartment() ),
													   TTi18n::gettext( 'Department does not exist' )
				);
			}

			if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
				// Job
				if ( $this->getJob() != '' && $this->getJob() != TTUUID::getZeroID() ) {
					$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
					$this->Validator->isResultSetWithRows( 'job',
														   $jlf->getByID( $this->getJob() ),
														   TTi18n::gettext( 'Job does not exist' )
					);
				}
				// Task
				if ( $this->getJobItem() != '' && $this->getJobItem() != TTUUID::getZeroID() ) {
					$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
					$this->Validator->isResultSetWithRows( 'job_item',
														   $jilf->getByID( $this->getJobItem() ),
														   TTi18n::gettext( 'Task does not exist' )
					);
				}
				// Punch Tag
				if ( $this->getPunchTag() !== false && $this->getPunchTag() != '' && $this->getPunchTag() != TTUUID::getZeroID() ) {
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

			// Recurring Schedule Template
			if ( $this->getRecurringScheduleTemplateControl() !== false && $this->getRecurringScheduleTemplateControl() != TTUUID::getZeroID() ) {
				$rstclf = TTnew( 'RecurringScheduleTemplateControlListFactory' ); /** @var RecurringScheduleTemplateControlListFactory $rstclf */
				$this->Validator->isResultSetWithRows( 'recurring_schedule_template_control_id',
													   $rstclf->getByID( $this->getRecurringScheduleTemplateControl() ),
													   TTi18n::gettext( 'Invalid Recurring Schedule Template' )
				);
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

			$this->validateCustomFields( $this->getCompany() );

			//
			// ABOVE: Validation code moved from set*() functions.
			//
		}

		Debug::Text( 'User ID: ' . $this->getUser() . ' DateStamp: ' . $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );

		$this->handleDayBoundary();
		$this->findUserDate();

		if ( $this->getUser() === false && $this->Validator->getValidateOnly() == false ) { //Use === so we still allow OPEN shifts (user_id=0)
			$this->Validator->isTRUE( 'user_id',
									  false,
									  TTi18n::gettext( 'Employee is not specified' ) );
		}

		if ( $this->getDateStamp() == false && $this->Validator->getValidateOnly() == false ) {
			Debug::Text( 'DateStamp is INVALID! ID: ' . $this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->Validator->isTrue( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Date/Time is incorrect, or pay period does not exist for this date. Please create a pay period schedule and assign this employee to it if you have not done so already' ) );
		}

		if ( $this->getDateStamp() != false && $this->getStartTime() == '' && $this->Validator->getValidateOnly() == false ) {
			$this->Validator->isTrue( 'start_time',
									  false,
									  TTi18n::gettext( 'In Time not specified' ) );
		}
		if ( $this->getDateStamp() != false && $this->getEndTime() == '' && $this->Validator->getValidateOnly() == false ) {
			$this->Validator->isTrue( 'end_time',
									  false,
									  TTi18n::gettext( 'Out Time not specified' ) );
		}

		//Make sure schedules aren't being added after the employees termination date.
		//We must allow deleting schedules after their termination date so schedules can be cleaned up if necessary.
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
			} else if ( $this->getUserObject()->getStatus() != 10 && $this->getUserObject()->getTerminationDate() == '' ) {
				$this->Validator->isTRUE( 'user_id',
										  false,
										  TTi18n::gettext( 'Employee is not currently active' ) );
			}

			if ( $this->getStatus() == 20 && TTUUID::castUUID( $this->getAbsencePolicyID() ) != TTUUID::getZeroID() && ( $this->getDateStamp() != false
							&& TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID() ) ) {
				$pglf = TTNew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
				$pglf->getAPISearchByCompanyIdAndArrayCriteria( $this->getUserObject()->getCompany(), [ 'user_id' => [ $this->getUser() ], 'absence_policy' => [ $this->getAbsencePolicyID() ] ] );
				if ( $pglf->getRecordCount() == 0 ) {
					$this->Validator->isTRUE( 'absence_policy_id',
											  false,
											  TTi18n::gettext( 'This absence policy is not available for this employee' ) );
				}
			}
		}

		//Make sure we check if the pay period is locked when adding/editing/deleting scheduled shifts,
		// as this can affect the timesheet and in cases where the we allow schedules to be adjusted but the timesheet is locked, things can get out of sync.
		if ( $this->getDateStamp() != false && is_object( $this->getPayPeriodObject() ) && $this->getPayPeriodObject()->getIsLocked() == true ) {
			$this->Validator->isTRUE( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Pay Period is Currently Locked' ) );
		}

		//Ignore conflicting time check when EnableOverwrite is set, as we will just be deleting any conflicting shift anyways.
		//Also ignore when setting OPEN shifts to allow for multiple.
		if ( $this->getEnableOverwrite() == false && $this->getDeleted() == false && ( $this->getDateStamp() != false
						&& TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID() ) ) {
			$this->Validator->isTrue( 'start_time',
									  !$this->isConflicting(), //Reverse the boolean.
									  TTi18n::gettext( 'Conflicting start/end time, schedule already exists for this employee' ) );
		} else {
			Debug::text( 'Not checking for conflicts... DateStamp: ' . (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );
		}

		//Check to see if the pay formula policy does not have a fallback accrual account specified, and if not ensure that the balance never exceeds the threshold.
		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL
				&& $this->getDeleted() == false && is_object( $this->getAbsencePolicyObject() ) && is_object( $this->getAbsencePolicyObject()->getPayFormulaPolicyObject() )
				&& $this->getAbsencePolicyObject()->getPayFormulaPolicyObject()->getAccrualPolicyAccount() != TTUUID::getZeroID() //Make sure the pay formula policy is linked to an accrual account first.
				&& $this->getAbsencePolicyObject()->getPayFormulaPolicyObject()->getAccrualBalanceThresholdFallbackAccrualPolicyAccount() == TTUUID::getZeroID() ) {
			$data_diff = $this->getDataDifferences();

			//Make sure we get the proper previous amount so it can be adjusted for in when modifying an existing record and calculating the current balance
			if ( isset( $this->is_new ) && $this->is_new == true ) {
				$previous_amount = 0;
			} else {
				$previous_amount = $this->getTotalTime();
				if ( $this->isDataDifferent( 'total_time', $data_diff ) == true ) {
					$previous_amount = $data_diff['total_time'];
				}
			}

			$adjusted_amount_arr = $this->getAbsencePolicyObject()->getPayFormulaPolicyObject()->getAmountAfterBalanceThreshold( $this->getUser(), TTMath::mul( $this->getTotalTime(), $this->getAbsencePolicyObject()->getPayFormulaPolicyObject()->getAccrualRate() ), $previous_amount );
			if ( isset( $adjusted_amount_arr['amount_remaining'] ) && $adjusted_amount_arr['amount_remaining'] != 0 ) {
				$this->Validator->isTRUE( 'end_time',
										  false,
										  TTi18n::gettext( 'Total time exceeds available balance threshold limit' ) );
			}
			unset( $adjusted_amount_arr, $previous_amount );
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			if ( $this->getUser() != TTUUID::getZeroID() && TTUUID::isUUID( $this->getJob() ) && $this->getJob() != TTUUID::getZeroID() && $this->getJob() != TTUUID::getNotExistID() && $this->getJob() != -2 ) {
				$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
				$jlf->getById( $this->getJob() );
				if ( $jlf->getRecordCount() > 0 ) {
					$j_obj = $jlf->getCurrent();

					if ( $this->getDateStamp() != false && $j_obj->isAllowedUser( $this->getUser(), $this->getBranch(), $this->getDepartment()  ) == false ) {
						$this->Validator->isTRUE( 'job',
												  false,
												  TTi18n::gettext( 'Employee is not assigned to this job' ) );
					}

					if ( $j_obj->isAllowedItem( $this->getJobItem() ) == false ) {
						$this->Validator->isTRUE( 'job_item',
												  false,
												  TTi18n::gettext( 'Task is not assigned to this job' ) );
					}
				}
			}
		}

		if ( $ignore_warning == false ) {
			//Warn users if they are trying to insert schedules too far in the future.
			if ( $this->getDateStamp() != false && $this->getDateStamp() > ( time() + ( 86400 * 366 ) ) ) {
				$this->Validator->Warning( 'date_stamp', TTi18n::gettext( 'Date is more than one year in the future' ) );
			}

			if ( $this->getDateStamp() != false
					&& is_object( $this->getPayPeriodObject() )
					&& is_object( $this->getPayPeriodObject()->getPayPeriodScheduleObject() ) ) {

				if ( $this->getTotalTime() > $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime() ) {
					$this->Validator->Warning( 'end_time', TTi18n::gettext( 'Schedule total time exceeds maximum shift time of' ) . ' ' . TTDate::getTimeUnit( $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getMaximumShiftTime() ) . ' ' . TTi18n::getText( 'hrs set for this pay period schedule' ) );
				}

				if ( $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
					//Find out if timesheet is verified or not.
					$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
					$pptsvlf->getByPayPeriodIdAndUserId( $this->getPayPeriod(), $this->getUser() );
					if ( $pptsvlf->getRecordCount() > 0 ) {
						$this->Validator->Warning( 'date_stamp', TTi18n::gettext( 'Pay period is already verified, saving these changes will require it to be reverified' ) );
					}
				}
			}
		}
																																																																						/* @formatter:off */ if ( $this->Validator->isValid() == TRUE && $this->isNew() == TRUE ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); } } /* @formatter:on */
		return TRUE;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		//Remember if this is a new user for validate() and postSave()
		if ( $this->isNew( true ) == true ) {
			$this->is_new = true;
		}

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
			$this->findUserDate(); //This must be called in preValidate(), otherwise it will change data and trigger validate to be called again, potentially causing a validation error in postSave().

			if ( $this->getPayPeriod() == false ) {
				$this->setPayPeriod();
			}

			if ( $this->getTotalTime() == false ) {
				$this->setTotalTime( $this->calcTotalTime() );
			}

			if ( $this->getStatus() == 10 ) {
				$this->setAbsencePolicyID( null );
			} else if ( $this->getStatus() == false ) {
				$this->setStatus( 10 ); //Default to working.
			}
		}

		if ( $this->getEnableOverwrite() == true ) {
			$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */

			//When overwriting OPEN shifts, always check based on branch/department/job/task, as there could be multiple OPEN shifts that are duplicate it from one another.
			//  I don't see the point in overwriting OPEN shifts to begin with, but its possible the user may do it without fulling understanding.
			if ( $this->getUser() == TTUUID::getZeroID() ) {
				Debug::Text( 'Looking for Conflicting OPEN Shifts...', __FILE__, __LINE__, __METHOD__, 10 );
				$slf->getConflictingOpenShiftSchedule( $this->getCompany(), $this->getUser(), $this->getStartTime(), $this->getEndTime(), $this->getBranch(), $this->getDepartment(), $this->getJob(), $this->getJobItem(), $this->getAbsencePolicyID(), $this->getReplacedId(), 1 ); //Limit 1;
			} else {
				//Delete any conflicting schedule shift before saving.
				$slf->getConflictingByCompanyIdAndUserIdAndStartDateAndEndDate( $this->getCompany(), $this->getUser(), $this->getStartTime(), $this->getEndTime(), $this->getId() ); //Don't consider the current record to be conflicting with itself (by passing id argument)
			}

			if ( $slf->getRecordCount() > 0 ) {
				Debug::Text( 'Found Conflicting Shift!!', __FILE__, __LINE__, __METHOD__, 10 );
				//Delete shifts.
				foreach ( $slf as $s_obj ) {
					Debug::Text( '  Deleting Schedule Shift ID: ' . $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$s_obj->setDeleted( true );
					if ( $s_obj->isValid() ) {
						$s_obj->Save();
					} else {
						Debug::Text( '  ERROR: Unable to delete Schedule Shift ID: ' . $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					}

					//When dealing with OPEN shifts, only delete the first one. Especially important if we are overwriting OPEN shifts where there could be multiple conflicting ones, and the reality is we don't know specifically which one to delete.
					//  Other than OPEN shifts, there should never be more than one record anyways, since records should never overlap or conflict with one another.
					//
					//  When not handling open shifts, we have to delete all overlapping shifts, as a employee could have scheduled shifts 8A-5P and 6P-8P, then submit a schedule adjustment request to work 8A-7P (overlaps both)
					//    So in this case we need to delete all overlapping shifts so the new one can be created.
					if ( $this->getUser() == TTUUID::getZeroID() ) {
						break;
					}
				}
			} else {
				Debug::Text( 'NO Conflicting Shift found...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		//Since Add Request icon was added to Attendance -> Schedule, a user could request to fill a *committed* open shift, and once the request is authorized, that open shift will still be there.
		//The same thing could happen if adding a new shift that was identical to the OPEN shift just with an employee assigned to it.
		//  So instead of deleting or overwriting the original OPEN shift, simply set "replaced_id" of the current shift to the OPEN shift ID, so we know it was replaced and therefore won't be displayed anymore.
		//    Now if the shift is deleted, the original OPEN shift will reappear, just like what would happen if it was a OPEN recurring schedule.
		//However, there is still the case of the user editing an OPEN shift and simply changing the employee to someone else, in this case the original OPEN shift would not be preseverd.
		//  Also need to handle the case of filling an OPEN shift, then editing the filled shift to change the start/end times or branch/department/job/task, that should no longer fill the OPEN shift.
		// 		But if they are changed back, it should refill the shift, because this acts the most similar to existing recurring schedule open shifts.
		if ( $this->getDeleted() == false && $this->Validator->getValidateOnly() == false
				&& TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID() ) { //Don't check for conflicting OPEN shifts when editing/saving an OPEN shift.
			$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
			$slf->getConflictingOpenShiftSchedule( $this->getCompany(), $this->getUser(), $this->getStartTime(), $this->getEndTime(), $this->getBranch(), $this->getDepartment(), $this->getJob(), $this->getJobItem(), $this->getAbsencePolicyID(), $this->getReplacedId(), 1 ); //Limit 1;
			if ( $slf->getRecordCount() > 0 ) {
				Debug::Text( 'Found Conflicting OPEN Shift!!', __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $slf as $s_obj ) {
					if ( $this->getID() != $s_obj->getID() ) {
						if ( $s_obj->getUser() == TTUUID::getZeroID() //Make sure we aren't replacing the same record as we are editing.
								&& ( $this->getStatus() == $s_obj->getStatus() && ( $this->getStatus() == 10 || ( $this->getStatus() == 20 && $this->getAbsencePolicyID() == $s_obj->getAbsencePolicyID() ) ) ) ) { //Absence shifts can only fill OPEN shifts if the absence policy matches so customers can use OPEN shifts for On-Call Absence scheduling. In most cases though, if a working shift that is filling an open shift changes to absence, the open shift should be unfilled.
							Debug::Text( 'Replacing Schedule OPEN Shift ID: ' . $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
							$this->setReplacedId( $s_obj->getId() );
						} else {
							Debug::Text( 'ERROR: Returned conflicting shift that is not OPEN! ID: ' . $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::Text( '  Not setting the replace_id to the same record that is being edited...' . $s_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
			} else {
				Debug::Text( 'NO Conflicting OPEN Shift found...', __FILE__, __LINE__, __METHOD__, 10 );
				$this->setReplacedId( TTUUID::getZeroID() );
			}
		} else if ( $this->getUser() == TTUUID::getZeroID() ) {
			$this->setReplacedId( TTUUID::getZeroID() ); //Force this whenever its an OPEN shift.
		} else if ( $this->getStatus() == 20 && $this->getUser() != TTUUID::getZeroID() ) {
			$this->setReplacedId( TTUUID::getZeroID() ); //Force this whenever it gets changed to a Absence shift, as they should never fill ANY open shift (recurring or committed), unless they are filled by the OPEN user itself.
		}

		return true;
	}

	function preSave() {
		if ( $this->is_new == true
				&& $this->getStatus() == 10
				&& $this->getEnableSplitAtMidnight() == true
				&& $this->doesSpanMidnight() == true
				&& ( is_object( $this->getPayPeriodScheduleObject() ) && $this->getPayPeriodScheduleObject()->getShiftAssignedDay() == 40 ) ) {
			$split_range_at_midnight = TTDate::splitDateRangeAtMidnight( $this->getStartTime(), $this->getEndTime() );
			if ( is_array( $split_range_at_midnight ) )  {
				$i = 0;
				foreach( $split_range_at_midnight as $split_range_arr ) {
					Debug::Text( '  Split Range: Start: '. TTDate::getDate('DATE+TIME', $split_range_arr['start_time_stamp'] ) .' End: '. TTDate::getDate('DATE+TIME', $split_range_arr['end_time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10 );

					if ( $i == 0 ) {
						$this->setStartTime( $split_range_arr['start_time_stamp'] );
						$this->setEndTime( $split_range_arr['end_time_stamp'] );
						$this->setTotalTime( $this->calcTotalTime() );
					} else {
						Debug::text( '    Split Schedule onto next day...', __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_schedule_obj = clone $this;
						$tmp_schedule_obj->setId( TTUUID::generateUUID() ); //Make sure to use a new ID.
						$tmp_schedule_obj->setEnableSplitAtMidnight( false );
						$tmp_schedule_obj->setStartTime( $split_range_arr['start_time_stamp'] );
						$tmp_schedule_obj->setEndTime( $split_range_arr['end_time_stamp'] );
						$tmp_schedule_obj->setTotalTime( $tmp_schedule_obj->calcTotalTime() );

						$tmp_schedule_obj->setEnableTimeSheetVerificationCheck( $this->getEnableTimeSheetVerificationCheck() ); //Unverify timesheet if its already verified.
						$tmp_schedule_obj->setEnableReCalculateDay( $this->getEnableReCalculateDay() );

						if ( $tmp_schedule_obj->isValid() ) {
							$tmp_schedule_obj->Save( true, true );
						}
						unset( $tmp_schedule_obj );
					}

					$i++;
				}
			}
			unset( $split_range_at_midnight, $split_range_arr );
		}
	}

	/**
	 * @return bool
	 */
	function postSave() {
		if ( $this->getEnableTimeSheetVerificationCheck() ) {
			//Check to see if schedule is verified, if so unverify it on modified punch.
			//Make sure exceptions are calculated *after* this so TimeSheet Not Verified exceptions can be triggered again.
			if ( $this->getDateStamp() != false
					&& is_object( $this->getPayPeriodObject() )
					&& is_object( $this->getPayPeriodObject()->getPayPeriodScheduleObject() )
					&& $this->getPayPeriodObject()->getPayPeriodScheduleObject()->getTimeSheetVerifyType() != 10 ) {
				//Find out if timesheet is verified or not.
				$pptsvlf = TTnew( 'PayPeriodTimeSheetVerifyListFactory' ); /** @var PayPeriodTimeSheetVerifyListFactory $pptsvlf */
				$pptsvlf->getByPayPeriodIdAndUserId( $this->getPayPeriod(), $this->getUser() );
				if ( $pptsvlf->getRecordCount() > 0 ) {
					//Pay period is verified, delete all records and make log entry.
					//These can be added during the maintenance jobs, so the audit records are recorded as user_id=0, check those first.
					Debug::text( 'Pay Period is verified, deleting verification records: ' . $pptsvlf->getRecordCount() . ' User ID: ' . $this->getUser() . ' Pay Period ID: ' . $this->getPayPeriod(), __FILE__, __LINE__, __METHOD__, 10 );
					foreach ( $pptsvlf as $pptsv_obj ) {
						TTLog::addEntry( $pptsv_obj->getId(), 500, TTi18n::getText( 'Schedule Modified After Verification' ) . ': ' . UserListFactory::getFullNameById( $this->getUser() ) . ' ' . TTi18n::getText( 'Schedule' ) . ': ' . TTDate::getDate( 'DATE', $this->getStartTime() ), null, $pptsvlf->getTable() );
						$pptsv_obj->setDeleted( true );
						if ( $pptsv_obj->isValid() ) {
							$pptsv_obj->Save();
						}
					}
				}
			}
		}

		if ( $this->getEnableReCalculateDay() == true ) {
			$data_diff = $this->getDataDifferences();

			//When comparing data_diff with timestamp columns in the DB, we need to convert them to epoch then compare again to make sure they are in fact different.
			if ( $this->isDataDifferent( 'date_stamp', $data_diff, 'date' ) == true ) {
				$data_diff['date_stamp'] = TTDate::parseDateTime( $data_diff['date_stamp'] );
			} else {
				$data_diff['date_stamp'] = null;
			}


			if ( !isset( $data_diff['user_id'] ) ) {
				$data_diff['user_id'] = null;
			}

			//If a schedule is deleted, or changed from an absence to working, or a record is being overwritten from a schedule request, we need to force recalculate the day.
			if ( $this->getDeleted() == true || ( isset( $data_diff['status_id'] ) && $data_diff['status_id'] == 20 ) || $this->getEnableOverwrite() == true ) {
				$recalculate_is_deleted = true;
			} else {
				$recalculate_is_deleted = false;
			}

			//Calculate total time. Mainly for docked.
			//Calculate entire week as Over Schedule (Weekly) OT policy needs to be reapplied if the schedule changes.
			if ( $this->getDateStamp() != false && is_object( $this->getUserObject() ) ) {
				//When shifts are assigned to different days, we need to calculate both days the schedule touches, as the shift could be assigned to either of them.
				UserDateTotalFactory::reCalculateDay( $this->getUserObject(), [ $this->getDateStamp(), $data_diff['date_stamp'], $this->getStartTime(), $this->getEndTime() ], true, false, true, false, $recalculate_is_deleted );
			}

			if ( TTUUID::isUUID( $data_diff['user_id'] ) && $data_diff['user_id'] != TTUUID::getZeroID() ) { //This needs to be outside the above is_object( $this->getUserObject() ) when switching a schedule from a user to OPEN shift, as is_object() fails in that case.
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getById( $data_diff['user_id'] );
				if ( $ulf->getRecordCount() == 1 ) {
					$old_user_obj = $ulf->getCurrent();
					Debug::text( '  Recalculating Old User ID: ' . $old_user_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					UserDateTotalFactory::reCalculateDay( $old_user_obj, [ $this->getDateStamp(), $data_diff['date_stamp'], $this->getStartTime(), $this->getEndTime() ], true, false, true, false, $recalculate_is_deleted );
				}
				unset( $ulf, $old_user_obj );
			}
		}

		//Needs to be called even for deleted schedule shifts, so the reminder can be deleted also.
		$this->handleReminderNotifications();
		$this->handleFutureTimeSheetRecalculationForExceptions();
		$this->handleScheduleChangedNotifications();

		return true;
	}

	function handleFutureTimeSheetRecalculationForExceptions() {
		global $config_vars;
		if ( isset( $config_vars['other']['enable_job_queue'] ) && $config_vars['other']['enable_job_queue'] != true ) {
			return false;
		}

		//Make sure the schedule isn't in the past, or too far in the future.
		// However even when creating schedules that have already ended, we may need to still trigger Out Late exceptions and such with a large grace period.
		if ( $this->getEndTime() <= ( TTDate::getTime() - 7200 ) ) {
			Debug::text( '  Schedule start time is after current time, or too far in the past...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		if ( $this->getDeleted() == false ) {
			if ( $this->getStatus() != 10 ) {
				Debug::text( '  Schedule is an absence, not queueing recalculation...', __FILE__, __LINE__, __METHOD__, 10 );

				//When modifying a schedule, delete any queued recalculations linked to it, so they can be re-added
				if ( !isset( $this->is_new ) || $this->is_new == false ) {
					SystemJobQueue::DeletePending( 'CalculatePolicy', 'reCalculateForJobQueue', $this->getId(), $this->getUser() );
				}

				return false;
			}

			if ( $this->getUser() == TTUUID::getZeroID() ) {
				Debug::text( '  Open shift, not queueing recalculation...', __FILE__, __LINE__, __METHOD__, 10 );

				$data_diff = $this->getDataDifferences();
				if ( $this->isDataDifferent( 'user_id', $data_diff ) == true ) {
					SystemJobQueue::DeletePending( 'CalculatePolicy', 'reCalculateForJobQueue', $this->getId(), $data_diff['user_id'] );
				}

				return false;
			}

			//Only schedule recalculations when creating a new schedule, or modifying user_id/start/end/status columns.
			$data_diff = $this->getDataDifferences();
			if ( !( ( isset( $this->is_new ) && $this->is_new == true ) || ( $this->isDataDifferent( 'user_id', $data_diff ) == true || $this->isDataDifferent( 'status_id', $data_diff ) == true || $this->isDataDifferent( 'start_time', $data_diff, 'time_stamp' ) == true || $this->isDataDifferent( 'end_time', $data_diff, 'time_stamp' ) == true ) ) ) {
				Debug::text( '  Schedule is being modified without changing keys fields, not queueing recalculation...', __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}

			$delay_after_trigger_time = 1; //1 second.

			$eplf = TTNew('ExceptionPolicyListFactory');
			$eplf->getByPolicyGroupUserIdAndTypeAndActive( $this->getUser(), [ 'S4', 'S6', 'S8' ], true );
			if ( $eplf->getRecordCount() > 0 ) {
				//When modifying a schedule, delete any queued recalculations linked to it, so they can be re-added
				if ( !isset( $this->is_new ) || $this->is_new == false ) {
					SystemJobQueue::DeletePending( 'CalculatePolicy', 'reCalculateForJobQueue', $this->getId(), $this->getUser() );

					if ( $this->isDataDifferent( 'user_id', $data_diff ) == true ) {
						SystemJobQueue::DeletePending( 'CalculatePolicy', 'reCalculateForJobQueue', $this->getId(), $data_diff['user_id'] );
					}
				}

				foreach( $eplf as $ep_obj ) { /** @var ExceptionFactory $ep_obj */
					switch ( $ep_obj->getType() ) {
						case 'S4':
							$effective_date = ( $this->getStartTime() + $ep_obj->getGrace() + $delay_after_trigger_time );
							Debug::text( '   Exception Type: '. $ep_obj->getType() .' Effective Date: '. TTDate::getDate('DATE', $effective_date ) .' Schedule Start: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ) .' Grace: '. $ep_obj->getGrace(), __FILE__, __LINE__, __METHOD__, 10 );
							SystemJobQueue::Add( TTi18n::getText( 'ReCalculate Quick Exceptions' ), $this->getId(), 'CalculatePolicy', 'reCalculateForJobQueue', [ $this->getUser(), 'calcQuickExceptions', TTDate::getMiddleDayEpoch( $this->getStartTime() ), TTDate::getMiddleDayEpoch( $this->getEndTime() ) ], 10, null, $effective_date, $this->getUser() );
							break;
						case 'S6':
							$effective_date = ( $this->getEndTime() + $ep_obj->getGrace() + $delay_after_trigger_time );
							Debug::text( '   Exception Type: '. $ep_obj->getType() .' Effective Date: '. TTDate::getDate('DATE', $effective_date ) .' Schedule End: '. TTDate::getDate('DATE+TIME', $this->getEndTime() ) .' Grace: '. $ep_obj->getGrace(), __FILE__, __LINE__, __METHOD__, 10 );
							SystemJobQueue::Add( TTi18n::getText( 'ReCalculate Quick Exceptions' ), $this->getId(), 'CalculatePolicy', 'reCalculateForJobQueue', [ $this->getUser(), 'calcQuickExceptions', TTDate::getMiddleDayEpoch( $this->getStartTime() ), TTDate::getMiddleDayEpoch( $this->getEndTime() ) ], 10, null, $effective_date, $this->getUser() );
							break;
						case 'S8': //Is triggered as pre-mature exception between schedule start/end time, only after schedule end time does it trigger fully.
							$effective_date = ( $this->getStartTime() + $delay_after_trigger_time );
							Debug::text( '   Exception Type: '. $ep_obj->getType() .' Effective Date: '. TTDate::getDate('DATE', $effective_date ) .' Schedule Start: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10 );
							SystemJobQueue::Add( TTi18n::getText( 'ReCalculate Quick Exceptions' ), $this->getId(), 'CalculatePolicy', 'reCalculateForJobQueue', [ $this->getUser(), 'calcQuickExceptions', TTDate::getMiddleDayEpoch( $this->getStartTime() ), TTDate::getMiddleDayEpoch( $this->getEndTime() ) ], 10, null, $effective_date, $this->getUser() );
							break;
					}
				}
			}
		} else {
			//Deleting schedule, remove any pending recalculations.
			SystemJobQueue::DeletePending( 'CalculatePolicy', 'reCalculateForJobQueue', $this->getId(), $this->getUser() );
		}

		return true;
	}

	function handleReminderNotifications() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return false;
		}

		if ( $this->getEnableNotifications() == false ) {
			return false;
		}

		//Be sure to handle cases where the schedule is switched from working to absent, and from one user to another, and from one day/start time to another.
		//Delete any existing notification attached to this schedule, in case they are modifying the start time or deleting the shift.
		$data_diff = $this->getDataDifferences();
		if ( $this->isDataDifferent('user_id', $data_diff) ) {
			Notification::deletePendingNotifications( ['reminder_punch_normal_in'], TTUUID::castUUID( $data_diff['user_id'] ), $this->getId(), null );
		} else {
			Notification::deletePendingNotifications( ['reminder_punch_normal_in'], $this->getUser(), $this->getId(), null );
		}

		//Make sure the schedule isn't in the past, or too far in the future.
		if ( $this->getStartTime() <= TTDate::getTime() ) {
			Debug::text( '  Schedule start time is after current time, or too far in the past...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		if ( $this->getDeleted() == false ) {
			if ( $this->getStatus() != 10 ) {
				Debug::text( '  Schedule is an absence, not setting reminder...', __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}

			if ( $this->getUser() == TTUUID::getZeroID() ) {
				Debug::text( '  Open shift, not setting reminder...', __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}

			$payload = [ 'timetrex' => [ 'event' => [ [ 'type' => 'open_view', 'data' => [], 'view_name' => 'InOut', 'action_name' => 'add' ] ] ] ]; //Open In/Out view for punching.

			//Add reminder for start of shift.
			Debug::text( ' Add post-dated notification for start of shift at: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10 );
			$notification_title_short = TTi18n::getText( 'Reminder: Punch In.' );
			$notification_title_long = TTi18n::getText( 'Reminder: Punch In by %1', TTDate::getDate('TIME', $this->getStartTime() ) );
			$notification_body = TTi18n::getText( 'Punch in to start your shift by '. TTDate::getDate('TIME', $this->getStartTime() ) );

			$notification_data = [
					'object_id'      => $this->getId(),
					'user_id'        => $this->getUser(),
					'priority_id'    => 1, //1=Critical
					'type_id'        => 'reminder_punch_normal_in',
					'object_type_id' => 130, //130=ScheduleFactory
					'effective_date' => $this->getStartTime(),
					'title_short'    => $notification_title_short,
					'title_long'     => $notification_title_long,
					'body_short'     => $notification_body,
					'payload' 		 => $payload,
			];

			Notification::sendNotification( $notification_data );
		}

		return true;
	}

	function handleScheduleChangedNotifications() {
		if ( $this->getEnableNotifyUserScheduleChange() == false ) {
			return false;
		}

		//No notification if schedule is in the past, or too far in the future (90 days).
		if ( $this->getStartTime() <= TTDate::getTime() || $this->getStartTime() > (TTDate::getTime() + (86400 * 90) ) ) {
			Debug::text( '  Schedule start time is after current time, or too far in the past...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		if ( $this->getDeleted() == false ) {
			if ( $this->getUser() == TTUUID::getZeroID() ) {
				Debug::text( 'Open shift, not notifying user...', __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}

			Debug::text( ' Adding notification that a scheduled shift was added or changed... Start Time: '. TTDate::getDate('DATE+TIME', $this->getStartTime() ), __FILE__, __LINE__, __METHOD__, 10 );

			$schedule_link = Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=Schedule&a=view&id=' . $this->getId() . '&tab=Schedule';

			if ( isset( $this->is_new ) && $this->is_new == true ) {
				$notification_title_short = TTi18n::getText( 'New %1 on %2', [
						$this->getStatus() == 10 ? TTi18n::getText( 'Shift' ) :  TTi18n::getText( 'Absence' ),
						TTDate::getDate('DATE', $this->getStartTime() )
				] );

				$notification_body = TTi18n::getText( '%1: %2: %3 - %4 Total Time: %5', [
						$this->getStatus() == 10 ? TTi18n::getText( 'Working' ) : TTi18n::getText( 'Absent' ),
						TTDate::getDate('DATE', $this->getStartTime() ),
						TTDate::getDate('TIME', $this->getStartTime() ),
						TTDate::getDate('TIME', $this->getEndTime() ),
						TTDate::convertSecondsToHMS( $this->getTotalTime() )
				] );

			} else {
				$notification_title_short = TTi18n::getText( 'Modified %1 on %2', [
						$this->getStatus() == 10 ? TTi18n::getText( 'Shift' ) :  TTi18n::getText( 'Absence' ),
						TTDate::getDate('DATE', $this->getStartTime() )
				]);

				$notification_body = TTi18n::getText( 'Shift Change - %1: %2: %3 - %4 Total Time: %5<br>Was: %6: %7: %8 - %9 Total Time: %10', [
						$this->getStatus() == 10 ? TTi18n::getText( 'Working' ) : TTi18n::getText( 'Absent' ),
						TTDate::getDate('DATE', $this->getStartTime() ),
						TTDate::getDate('TIME', $this->getStartTime() ),
						TTDate::getDate('TIME', $this->getEndTime() ),
						TTDate::convertSecondsToHMS( $this->getTotalTime() ),
						$this->getGenericOldDataValue( 'status_id' ) == 10 ? TTi18n::getText( 'Working' ) : TTi18n::getText( 'Absent' ),
						TTDate::getDate('DATE', $this->getGenericOldDataValue( 'start_time' ) ),
						TTDate::getDate('TIME', $this->getGenericOldDataValue( 'start_time' ) ),
						TTDate::getDate('TIME', $this->getGenericOldDataValue( 'end_time' ) ),
						TTDate::convertSecondsToHMS( $this->getGenericOldDataValue( 'total_time' ) )
				]  );
			}

			$notification_data = [
					'object_id'      => $this->getId(),
					'user_id'        => $this->getUser(),
					'priority_id'    => 5, //5=Normal
					'type_id'        => 'schedule',
					'object_type_id' => 130, //130=ScheduleFactory
					'title_short'    => $notification_title_short,
					'body_short'     => $notification_body,
					'payload'        => [ 'link' => $schedule_link ],
			];
			//Debug::Arr( $notification_data, ' Notification Data: ', __FILE__, __LINE__, __METHOD__, 10 );

			Notification::sendNotification( $notification_data );
		}

		return true;
	}

	/**
	 * @return string
	 */
	function getPunchTagDisplay() {
		return PunchTagListFactory::getStringByIDs( json_decode( $this->getColumn( 'punch_tag_id' ), true ) );
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$data = $this->parseCustomFieldsFromArray( $data );

			/*
			 *			//Use date_stamp is determined from StartTime and EndTime now automatically, due to schedules honoring the "assign shifts to" setting
						//We need to set the UserDate as soon as possible.
						//Consider mass editing shifts, where user_id is not sent but user_date_id is. We need to prevent the shifts from being assigned to the OPEN user.
						if ( isset($data['user_id']) AND ( $data['user_id'] !== '' AND $data['user_id'] !== FALSE )
								AND isset($data['date_stamp']) AND $data['date_stamp'] != ''
								AND isset($data['start_time']) AND $data['start_time'] != '' ) {
							Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'] .' Start Time: '. $data['start_time'], __FILE__, __LINE__, __METHOD__, 10);
							$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'].' '.$data['start_time'] ) );
						} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] >= 0 ) {
							Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__, 10);
							$this->setUserDateID( $data['user_date_id'] );
						} else {
							Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__, 10);
						}
			*/


			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[ $key ] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$this->setUser( $data[ $key ] );
							break;
						case 'user_date_id': //Ignore explicitly set user_date_id here as its set above.
						case 'total_time': //If they try to specify total time, just skip it, as it gets calculated later anyways.
							break;
						case 'date_stamp':
							$this->$function( TTDate::parseDateTime( $data[ $key ] ) );
							break;
						case 'start_time':
							if ( method_exists( $this, $function ) ) {
								Debug::text( '..Setting start time from EPOCH: "' . $data[ $key ] . '"', __FILE__, __LINE__, __METHOD__, 10 );

								if ( isset( $data['start_date_stamp'] ) && $data['start_date_stamp'] != '' && isset( $data[ $key ] ) && $data[ $key ] != '' ) {
									Debug::text( ' aSetting start time... "' . $data['start_date_stamp'] . ' ' . $data[ $key ] . '"', __FILE__, __LINE__, __METHOD__, 10 );
									$this->$function( TTDate::parseDateTime( $data['start_date_stamp'] . ' ' . $data[ $key ] ) ); //Prefix date_stamp onto start_time
								} else if ( isset( $data[ $key ] ) && $data[ $key ] != '' ) {
									//When start_time is provided as a full timestamp. Happens with audit log detail.
									Debug::text( ' aaSetting start time...: ' . $data[ $key ], __FILE__, __LINE__, __METHOD__, 10 );
									$this->$function( TTDate::parseDateTime( $data[ $key ] ) );
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
								Debug::text( '..xSetting end time from EPOCH: "' . $data[ $key ] . '"', __FILE__, __LINE__, __METHOD__, 10 );

								if ( isset( $data['start_date_stamp'] ) && $data['start_date_stamp'] != '' && isset( $data[ $key ] ) && $data[ $key ] != '' ) {
									Debug::text( ' aSetting end time... "' . $data['start_date_stamp'] . ' ' . $data[ $key ] . '"', __FILE__, __LINE__, __METHOD__, 10 );
									$this->$function( TTDate::parseDateTime( $data['start_date_stamp'] . ' ' . $data[ $key ] ) ); //Prefix date_stamp onto end_time
								} else if ( isset( $data[ $key ] ) && $data[ $key ] != '' ) {
									Debug::text( ' aaSetting end time...: ' . $data[ $key ], __FILE__, __LINE__, __METHOD__, 10 );
									//When end_time is provided as a full timestamp. Happens with audit log detail.
									$this->$function( TTDate::parseDateTime( $data[ $key ] ) );
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
								$this->$function( $data[ $key ] );
							}
							break;
					}
				}
			}

			$this->handleDayBoundary(); //Make sure we handle day boundary before calculating total time.
			$this->setTotalTime( $this->calcTotalTime() ); //Calculate total time immediately after. This is required for proper audit logging too.
			$this->setEnableReCalculateDay( true ); //This is needed for Absence schedules to carry over to the timesheet.
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
		$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[ $variable ] ) && $include_columns[ $variable ] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'first_name':
						case 'last_name':
							if ( TTUUID::isUUID( $this->getColumn( 'user_id' ) ) && $this->getColumn( 'user_id' ) != TTUUID::getZeroID() && $this->getColumn( 'user_id' ) != TTUUID::getNotExistID() ) {
								$data[ $variable ] = $this->getColumn( $variable );
							} else {
								$data[ $variable ] = TTi18n::getText( 'OPEN' );
							}
							break;
						case 'user_id':
							//Make sure getUser() returns the proper user_id, otherwise mass edit will always assign shifts to OPEN employee.
							//We have to make sure the 'user_id' function map is FALSE as well, so we don't get a SQL error when getting the empty record set.
							$data[ $variable ] = $this->tmp_data['user_id'] = TTUUID::castUUID( $this->getColumn( $variable ) );
							break;
						case 'user_status_id':
						case 'group_id':
						case 'title_id':
						case 'default_branch_id':
						case 'default_department_id':
							$data[ $variable ] = TTUUID::castUUID( $this->getColumn( $variable ) );
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
							$data[ $variable ] = $this->getColumn( $variable );
							break;
						case 'punch_tag_id':
							$data[ $variable ] = json_decode( $this->getColumn( $variable ), true );
							break;
						case 'punch_tag': //Punch Tags for display purposes.
							if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE && $this->isClientFriendly() == true || ( isset( $include_columns[$variable] ) AND $include_columns[$variable] == TRUE ) ) {
								$data[ $variable ] = $this->getPunchTagDisplay();
							}
							break;
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'user_status':
							$data[ $variable ] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;
						case 'date_stamp':
							$data[ $variable ] = TTDate::getAPIDate( 'DATE', $this->getDateStamp() );
							break;
						case 'start_date_stamp':
							$data[ $variable ] = TTDate::getAPIDate( 'DATE', $this->getStartTime() ); //Include both date+time
							break;
						case 'start_date':
							$data[ $variable ] = TTDate::getAPIDate( 'DATE+TIME', $this->getStartTime() ); //Include both date+time
							break;
						case 'end_date':
							$data[ $variable ] = TTDate::getAPIDate( 'DATE+TIME', $this->getEndTime() ); //Include both date+time
							break;
						case 'start_time_stamp':
							$data[ $variable ] = $this->getStartTime(); //Include start date/time in epoch format for sorting...
							break;
						case 'end_time_stamp':
							$data[ $variable ] = $this->getEndTime(); //Include end date/time in epoch format for sorting...
							break;
						case 'start_time':
						case 'end_time':
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = TTDate::getAPIDate( 'TIME', $this->$function() ); //Just include time, so Mass Edit sees similar times without dates
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getColumn( 'user_id' ), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );

			//$this->getCompany() is not always set, but we need the company_id to get the proper data from the API.
			//Such as when viewing a scheduled shift.
			if ( TTUUID::isUUID( $this->getCompany() ) == true ) {
				$data = $this->getCustomFields( $this->getCompany(), $data, $include_columns );
			} else {
				global $current_company;
				if ( isset( $current_company ) && is_object( $current_company ) ) {
					$data = $this->getCustomFields( $current_company->getId(), $data, $include_columns );
				}
			}
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		if ( $this->getUser() == TTUUID::getZeroID() ) {
			$employee_name = TTi18n::getText( 'OPEN' );
		} else {
			$employee_name = UserListFactory::getFullNameById( $this->getUser() );
		}

		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Schedule - Employee' ) . ': ' . $employee_name . ' ' . TTi18n::getText( 'Start Time' ) . ': ' . TTDate::getDate( 'DATE+TIME', $this->getStartTime() ) . ' ' . TTi18n::getText( 'End Time' ) . ': ' . TTDate::getDate( 'DATE+TIME', $this->getEndTime() ), null, $this->getTable(), $this );
	}

}

?>