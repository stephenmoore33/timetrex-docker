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
 * @package Modules\Punch
 */
class PunchFactory extends Factory {
	protected $table = 'punch';
	protected $pk_sequence_name = 'punch_id_seq'; //PK Sequence name

	var $punch_control_obj = null;
	var $previous_punch_obj = null;
	protected $schedule_obj = null;
	protected $user_obj = null;
	protected $station_obj = null;
	protected $break_policy_arr = null;
	protected $meal_policy_arr = null;

	private $split_punch_control;
	private $split_at_midnight;
	private $auto_transfer;
	private $calc_total_time;
	private $calc_user_date_id;
	private $calc_user_date_total;
	private $premature_exception;
	private $calc_exception;
	private $calc_weekly_system_total_time;
	private $calc_system_total_time;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'punch_control_id' )->setFunctionMap( 'PunchControlID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'station_id' )->setFunctionMap( 'Station' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'time_stamp' )->setFunctionMap( 'TimeStamp' )->setType( 'timestamptz' )->setIsNull( false ),
							TTSCol::new( 'original_time_stamp' )->setFunctionMap( 'OriginalTimeStamp' )->setType( 'timestamptz' )->setIsNull( false ),
							TTSCol::new( 'actual_time_stamp' )->setFunctionMap( 'ActualTimeStamp' )->setType( 'timestamptz' )->setIsNull( false ),
							TTSCol::new( 'transfer' )->setFunctionMap( 'Transfer' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'longitude' )->setFunctionMap( 'Longitude' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'latitude' )->setFunctionMap( 'Latitude' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'position_accuracy' )->setFunctionMap( 'PositionAccuracy' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'has_image' )->setFunctionMap( 'HasImage' )->setType( 'smallint' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_punch' )->setLabel( TTi18n::getText( 'Punch' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'punch_control_id' )->setType( 'uuid' )->setVisible( [ 'UI', 'AI' ], false )->setDataSource( TTSAPI::new( 'APIPunchControl' )->setMethod( 'getPunchControl' ) ),  //Hidden from UI, but visible to API and AI.
											TTSField::new( 'user_id' )->setType( 'uuid' )->setLabel( TTi18n::getText( 'Employee' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'punch_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Time' ) )->setVisible( [ 'AI' ], false ),
											TTSField::new( 'time_stamp' )->setType( 'time' )->setLabel( TTi18n::getText( 'Time' ) )->setVisible( [ 'UI' ], false ),
											TTSField::new( 'punch_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Date' ) )->setVisible( [ 'AI' ], false ),
											TTSField::new( 'punch_dates' )->setType( 'date' )->setLabel( TTi18n::getText( 'Date' ) )->setVisible( [ 'AI' ], false ),
											TTSField::new( 'transfer' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Transfer' ) ),
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'In/Out' ) )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'branch_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Branch' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) ),
											TTSField::new( 'department_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Department' ) )->setDataSource( TTSAPI::new( 'APIDepartment' )->setMethod( 'getDepartment' ) ),
											TTSField::new( 'note' )->setType( 'text' )->setLabel( TTi18n::getText( 'Note' ) ),
											TTSField::new( 'station_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Station' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getStation' ) ),
											TTSField::new( 'split_punch_control' )->setType( 'text' )->setLabel( TTi18n::getText( 'Split Existing Punches' ) ),
											TTSField::new( 'punch_image' )->setType( 'image' )->setLabel( TTi18n::getText( 'Image' ) )
									)
							),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Include Punch' )->setVisible( [ 'UI' ], false )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getPunch' ) )
							),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'exclude_id' )->setType( 'single-dropdown' )->setLabel( 'Exclude Punch' )->setVisible( [ 'UI' ], false )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getPunch' ) )
							),

							TTSSearchField::new( 'user_status_id' )->setType( 'integer' )->setColumn( 'd.status_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'user_status_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Employee Status' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getOptions' )->setArg( 'status' ) )
							),
							TTSSearchField::new( 'pay_period_id' )->setType( 'integer' )->setColumn( 'b.pay_period_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'pay_period_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Pay Period' ) )->setDataSource( TTSAPI::new( 'APIPayPeriod' )->setMethod( 'getPayPeriod' ) )
							),
							TTSSearchField::new( 'start_date' )->setType( 'date' )->setColumn( 'b.date_stamp' )->setFieldObject(
									TTSField::new( 'start_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Start Date' ) )
							),
							TTSSearchField::new( 'end_date' )->setType( 'date' )->setColumn( 'b.date_stamp' )->setFieldObject(
									TTSField::new( 'end_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'End Date' ) )
							),
							TTSSearchField::new( 'user_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'user_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Employee' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) )
							),
							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'a.status_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'status_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'In/Out' ) )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getOptions' )->setArg( 'status' ) )
							),
							TTSSearchField::new( 'type_id' )->setType( 'integer' )->setColumn( 'a.type_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'type_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getOptions' )->setArg( 'type' ) )
							),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid' )->setColumn( 'd.default_branch_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'default_branch_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Default Branch' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) )
							),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid' )->setColumn( 'd.default_department_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'default_department_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Default Department' ) )->setDataSource( TTSAPI::new( 'APIDepartment' )->setMethod( 'getDepartment' ) )
							),
							TTSSearchField::new( 'group_id' )->setType( 'uuid' )->setColumn( 'd.group_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'group_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Group' ) )->setDataSource( TTSAPI::new( 'APIUserGroup' )->setMethod( 'getUserGroup' ) )
							),
							TTSSearchField::new( 'title_id' )->setType( 'uuid' )->setColumn( 'd.title_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'title_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Title' ) )->setDataSource( TTSAPI::new( 'APIUserTitle' )->setMethod( 'getUserTitle' ) )
							),
							TTSSearchField::new( 'punch_branch_id' )->setType( 'uuid' )->setColumn( 'b.branch_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'punch_branch_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Branch' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) )
							),
							TTSSearchField::new( 'punch_department_id' )->setType( 'uuid' )->setColumn( 'b.department_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'punch_department_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Department' ) )->setDataSource( TTSAPI::new( 'APIDepartment' )->setMethod( 'getDepartment' ) )
							),
							TTSSearchField::new( 'job_id' )->setType( 'uuid' )->setColumn( 'b.job_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Task' )->setDataSource( TTSAPI::new( 'APIJobItem' )->setMethod( 'getJobItem' ) )
							),

							TTSSearchField::new( 'include_job_id' )->setType( 'uuid' )->setColumn( 'b.job_id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Include Job' )->setVisible( [ 'ui' ], false )->setDataSource( TTSAPI::new( 'APIJob' )->setMethod( 'getJob' ) )
							),
							TTSSearchField::new( 'exclude_job_id' )->setType( 'uuid' )->setColumn( 'b.job_id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Exclude Job' )->setVisible( [ 'ui' ], false )->setDataSource( TTSAPI::new( 'APIJob' )->setMethod( 'getJob' ) )
							),

							TTSSearchField::new( 'job_item_id' )->setType( 'uuid' )->setColumn( 'b.job_item_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Task' )->setDataSource( TTSAPI::new( 'APIJobItem' )->setMethod( 'getJobItem' ) )
							),

							TTSSearchField::new( 'include_item_id' )->setType( 'uuid' )->setColumn( 'b.job_item_id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Include Task' )->setVisible( [ 'ui' ], false )->setDataSource( TTSAPI::new( 'APIJobItem' )->setMethod( 'getJobItem' ) )
							),
							TTSSearchField::new( 'exclude_item_id' )->setType( 'uuid' )->setColumn( 'b.job_item_id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Exclude Task' )->setVisible( [ 'ui' ], false )->setDataSource( TTSAPI::new( 'APIJobItem' )->setMethod( 'getJobItem' ) )
							),

							TTSSearchField::new( 'job_group_id' )->setType( 'uuid' )->setColumn( 'x.group_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'job_group_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Job Group' ) )->setDataSource( TTSAPI::new( 'APIJobGroup' )->setMethod( 'getJobGroup' ) )
							),
							TTSSearchField::new( 'punch_tag_id' )->setType( 'multi-dropdown' )->setColumn( 'b.punch_tag_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'punch_tag_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Punch Tags' ) )->setDataSource( TTSAPI::new( 'APIPunchTag' )->setMethod( 'getPunchTag' ) )
							),
							TTSSearchField::new( 'created_by' )->setType( 'uuid' )->setColumn( 'a.created_by' )->setFieldObject(
									TTSField::new( 'created_by' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Created By' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) )
							),
							TTSSearchField::new( 'updated_by' )->setType( 'uuid' )->setColumn( 'a.updated_by' )->setFieldObject(
									TTSField::new( 'updated_by' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Updated By' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) )
							),
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			//NOTE: The description and instruction combined cannot be more than 1000 characters or OpenAI will throw an error. We must be concise with our punch descriptions.
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIPunch' )->setMethod( 'getPunch' )
									->setSummary( 'Get punch records.' )
									->setCommonDescription('Guidelines for Punches and Timesheets:

(1) Employee timesheets consist of paired punches, always an In punch then an Out punch. A typical day begins with a Normal In punch and concludes with a Normal Out punch. Break and lunch punches are optional, and can occur once or multiple times a day, and will deduct from the total work time.
(2) When trying to solve a punch related task you should always get the punches for the related day using APIPunch-getPunch. Getting the punches first is important and allows you to analyze the punches for that day before editing or creating a new punch.
(3) To analyze timesheets and punches effectively, it is crucial you know the datasource ID values for punch types and statuses. Here are the values for each:
Punch Types - type_id:
10 = Normal
20 = Lunch
30 = Break
Punch Statuses - status_id:
10 = In
20 = Out')

/* Set functions are currently disabled and this further description is not required.
Now I will share some examples of how typical timesheets and punches should look, and how to fix them when they are broken.

Correct example for a days worth of punches:
In: 8:00AM [Normal]
Out : 10:00AM [Break]
In: 10:15AM [Break]
Out: 12:00PM [Lunch]
In: 1:00PM [Lunch]
Out: 5:00PM [Normal]

Broken example with a missing lunch punch:
In: 8:00AM [Normal]
Out: 10:00AM [Break]
In: 10:15AM [Break]
Out: 12:00PM [Lunch]
In:
Out: 5:00PM [Normal]

In the above example a Lunch in punch is missing and needs to be created.

Further Examples:
(1) There may be situations where an employee fails to record several punches. In such cases, it\'s your responsibility to create all the missing punches. For instance, if an employee neglects to log their entire lunch or break period, you would need to create both the Out and In punches.
(2) If your task includes modifying specific values of an existing punch, like type, status, or time, retrieve the corresponding punch and apply the required modifications. This might involve actions like converting a break punch to a lunch punch or changing the time associated with a punch. Always ensure that you are updating the correct punch.

Correct example for a days worth of punches:
In: 8:00AM [Normal]
Out : 10:00AM [Break]
In: 10:15AM [Break]
Out: 12:00PM [Lunch]
In: 1:00PM [Lunch]
Out: 5:00PM [Normal]

Broken example with a missing lunch punch:
In: 8:00AM [Normal]
Out: 10:00AM [Break]
In: 10:15AM [Break]
Out: 12:00PM [Lunch]
In:
Out: 5:00PM [Normal]

In the above example a Lunch in punch is missing and needs to be created.

Further Examples:
(1) There may be situations where an employee fails to record several punches. In such cases, it\'s your responsibility to create all the missing punches. For instance, if an employee neglects to log their entire lunch or break period, you would need to create both the Out and In punches.
(2) If your task includes modifying specific values of an existing punch, like type, status, or time, retrieve the corresponding punch and apply the required modifications. This might involve actions like converting a break punch to a lunch punch or changing the time associated with a punch. Always ensure that you are updating the correct punch.
									 */
									->setCommonLinks( [
															  [
																	  'class'  => 'APIPunch',
																	  'method' => 'setPunch',
															  ],
															  [
																	  'class'  => 'APIPunch',
																	  'method' => 'getPunchDefaultData',
															  ],

													  ] )
									->setModelKeywords( 'lunch break timesheet' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIPunch' )->setMethod( 'setPunch' )
									->setSummary( 'Add or edit punch records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setDescription( 'You should use APIPunch-getPunch to get punches for the day first so you can analyze the employees curent punches before editing punches or creating new ones.' )
									->setModelKeywords( 'lunch break timesheet' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIPunch' )->setMethod( 'deletePunch' )
									->setSummary( 'Delete punch records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getPunch' ) ),
											   ) ),
							TTSAPI::new( 'APIPunch' )->setMethod( 'getPunchDefaultData' )
									->setModelKeywords( 'lunch break timesheet' )
									->setSummary( 'Get default punch data used for creating new punches. When creating a new punch you should always use this before calling APIPunch-setPunch to get the correct punch type, time, and any associated data for the next punch.' )
									->setDescription( 'Provide as much data as possible. If specific arguments, such as the punch_control_id, aren\'t known, you\'re still required to include them, but set them as null.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'user_id' )->setType( 'uuid' )->setLabel( TTi18n::getText( 'Employee' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
													   TTSField::new( 'date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Date' ) ),
													   TTSField::new( 'punch_control_id' )->setType( 'uuid' )->setLabel( TTi18n::getText( 'Punch Control' ) )->setDataSource( TTSAPI::new( 'APIPunchControl' )->setMethod( 'getPunchControl' ) )->setModelDescription( 'This is the id for a punch pair. If you are fixing a punch pair by adding a missing out this would be the id of the existing in punch before the gap.' ),
													   TTSField::new( 'previous_punch_id' )->setType( 'uuid' )->setLabel( TTi18n::getText( 'Previous Punch' ) )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getPunch' ) ),
													   TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'In/Out' ) )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
													   TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIPunch' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											   ) )
					)
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
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'In' ),
						20 => TTi18n::gettext( 'Out' ),
				];
				break;
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Normal' ),
						20 => TTi18n::gettext( 'Lunch' ),
						30 => TTi18n::gettext( 'Break' ),
				];
				break;
			case 'transfer':
				$retval = [
						0 => TTi18n::gettext( 'No' ),
						1 => TTi18n::gettext( 'Yes' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
						'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
						//'-1005-user_status' => TTi18n::gettext('Employee Status'),
						'-1010-title'              => TTi18n::gettext( 'Title' ),
						'-1039-group'              => TTi18n::gettext( 'Group' ),
						'-1040-default_branch'     => TTi18n::gettext( 'Default Branch' ),
						'-1050-default_department' => TTi18n::gettext( 'Default Department' ),
						'-1160-branch'             => TTi18n::gettext( 'Branch' ),
						'-1170-department'         => TTi18n::gettext( 'Department' ),

						'-1200-type'              => TTi18n::gettext( 'Type' ),
						'-1202-status'            => TTi18n::gettext( 'Status' ),
						'-1210-date_stamp'        => TTi18n::gettext( 'Date' ),
						'-1220-time_stamp'        => TTi18n::gettext( 'Time' ),
						'-1224-actual_time_stamp' => TTi18n::gettext( 'Time (Actual)' ),
						'-1225-actual_time_diff'  => TTi18n::gettext( 'Actual Time Difference' ),

						'-1230-tainted' => TTi18n::gettext( 'Tainted' ),

						'-1310-station_station_id'  => TTi18n::gettext( 'Station ID' ),
						'-1320-station_type'        => TTi18n::gettext( 'Station Type' ),
						'-1330-station_source'      => TTi18n::gettext( 'Station Source' ),
						'-1340-station_description' => TTi18n::gettext( 'Station Description' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];

				$retval = $this->getCustomFieldsColumns( $retval, null );

				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval = array_merge( [
												   '-1180-job'      => TTi18n::gettext( 'Job' ),
												   '-1190-job_item' => TTi18n::gettext( 'Task' ),

												   '-1155-default_job'      => TTi18n::gettext( 'Default Job' ),
												   '-1156-default_job_item' => TTi18n::gettext( 'Default Task' ),

												   '-1228-punch_tag'  => TTi18n::gettext( 'Tags' ),
										   ],
										   $retval
					);
					ksort( $retval );
				}
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'first_name',
						'last_name',
						'type',
						'status',
						'date_stamp',
						'time_stamp',
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
				'id' => 'ID',

				'user_id'             => false, //This is coming from PunchControl factory.
				'split_punch_control' => false, //Must come before transfer, so a punch control can be split to allow a transfer to occur.
				'transfer'            => 'Transfer',
				'type_id'             => 'Type',
				'type'                => false,
				'status_id'           => 'Status',
				'status'              => false,
				'time_stamp'          => 'TimeStamp',
				'raw_time_stamp'      => false,
				'punch_date'          => false,
				'punch_time'          => false,
				'punch_control_id'    => 'PunchControlID',
				'actual_time_stamp'   => 'ActualTimeStamp',
				'actual_time_diff'    => false,
				'original_time_stamp' => 'OriginalTimeStamp',
				'schedule_id'         => 'ScheduleID',

				'station_id'          => 'Station',
				'station_station_id'  => false,
				'station_type_id'     => false,
				'station_type'        => false,
				'station_source'      => false,
				'station_description' => false,

				'longitude'         => 'Longitude',
				'latitude'          => 'Latitude',
				'position_accuracy' => 'PositionAccuracy',

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
				'default_job_id' => false,
				'default_job'    => false,
				'default_job_item_id' => false,
				'default_job_item'    => false,

				'date_stamp'    => false,
				'user_date_id'  => false,
				'pay_period_id' => false,
				'total_time'    => false, //Used for Map, Distance tab.

				'branch_id'          => false,
				'branch'             => false,
				'department_id'      => false,
				'department'         => false,
				'job_id'             => false,
				'job'                => false,
				'job_manual_id'      => false,
				'job_item_id'        => false,
				'job_item'           => false,
				'job_item_manual_id' => false,
				'punch_tag_id'       => false,
				'punch_tag'			 => false,
				'quantity'           => false,
				'bad_quantity'       => false,
				'meal_policy_id'     => false,
				'note'               => false,

				'tainted'   => 'Tainted',
				'has_image' => 'HasImage',

				'deleted' => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return PunchControlFactory|bool
	 */
	function getPunchControlObject() {
		return $this->getGenericObject( 'PunchControlListFactory', $this->getPunchControlID(), 'punch_control_obj' );
	}

	/**
	 * @return ScheduleFactory|bool
	 */
	function getScheduleObject() {
		return $this->getGenericObject( 'ScheduleListFactory', $this->getScheduleID(), 'schedule_obj' );
	}

	/**
	 * @return UserFactory|bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return StationFactory|bool
	 */
	function getStationObject() {
		return $this->getGenericObject( 'StationListFactory', $this->getStation(), 'station_obj' );
	}

	/**
	 * @param int $epoch EPOCH
	 * @param bool $user_id
	 * @param bool $ignore_future_punches
	 * @return bool|null
	 */
	function getPreviousPunchObject( $epoch, $user_id = false, $ignore_future_punches = false ) {
		if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}

		if ( is_object( $this->previous_punch_obj ) ) {
			return $this->previous_punch_obj;
		} else {
			//Use getShiftData() to better detect the previous punch based on the shift time.
			//This should make our maximum shift time setting based on the shift start time rather then the last punch that happens to exist.
			//If no Normal In punch exists in the shift, use the first punch time to base the Maximum Shift Time on.
			$ppslf = new PayPeriodScheduleListFactory();
			$ppslf->getByUserId( $user_id );
			if ( $ppslf->getRecordCount() == 1 ) {
				$pps_obj = $ppslf->getCurrent();
				$maximum_shift_time = $pps_obj->getMaximumShiftTime();
			} else {
				$pps_obj = TTnew( 'PayPeriodScheduleFactory' ); /** @var PayPeriodScheduleFactory $pps_obj */
				$maximum_shift_time = ( 3600 * 16 );
				$pps_obj->setMaximumShiftTime( $maximum_shift_time );
			}
			$shift_data = $pps_obj->getShiftData( null, $user_id, $epoch, 'nearest_shift', null, null, null, null, $ignore_future_punches );
			unset( $pps_obj );

			$last_punch_id = false;
			if ( isset( $shift_data ) && is_array( $shift_data ) ) {
				//If we check against the first punch, then split shifts like: 10AM -> 11AM, then 11PM -> 8AM (next day) won't match properly,
				// as the 8AM would need an almost 24hr maximum shift time, when the shift was only 1hr prior to last out punch.
				// Instead maybe check from the last punch minus the maximum shift time minus the total time of the shift, that way when the 8AM
				// punch out specified in the above case is being entered it would be 10hr maximum shift time, not a 22hr maximum shift time.

//				if ( isset($shift_data['punches']) AND $shift_data['punches'][0]['time_stamp'] >= ( $epoch - $maximum_shift_time ) ) {
//					$last_punch_id = $shift_data['punches'][( count($shift_data['punches']) - 1 )]['id'];
//				}
				if ( isset( $shift_data['punches'] ) && isset( $shift_data['last_punch_key'] ) && isset( $shift_data['punches'][$shift_data['last_punch_key']] )
						&& $shift_data['punches'][$shift_data['last_punch_key']]['time_stamp'] >= ( $epoch - ( $maximum_shift_time - $shift_data['total_time'] ) ) ) {
					$last_punch_id = $shift_data['punches'][$shift_data['last_punch_key']]['id'];
				} else {
					Debug::Text( ' Shift didnt start within maximum shift time...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				Debug::Text( ' No shift data...', __FILE__, __LINE__, __METHOD__, 10 );
			}
			//Debug::Arr($shift_data, ' Shift Data: Last Punch ID: '. $last_punch_id, __FILE__, __LINE__, __METHOD__, 10);

			if ( TTUUID::isUUID( $last_punch_id ) && $last_punch_id != TTUUID::getZeroID() && $last_punch_id != TTUUID::getNotExistID() ) {
				$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
				$plf->getById( $last_punch_id );
				if ( $plf->getRecordCount() > 0 ) {
					$previous_punch_obj = $plf->getCurrent();

					return $previous_punch_obj;
				}
			}

			return false;
		}
	}

	/**
	 * @return bool|mixed
	 */
	function getNextPunchControlID() {
		//This is normally the PREVIOUS punch,
		//so if it was IN (10), return its punch control ID
		//so the next OUT punch is a new punch_control_id.
		if ( $this->getStatus() == 10 ) {
			return $this->getPunchControlID();
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUser( $value ) {
		return $this->setGenericDataValue( 'user_id', TTUUID::castUUID( $value ) );//Make sure this isn't an array.
	}

	/**
	 * @return bool|mixed
	 */
	function findPunchControlID() {
		if ( $this->getPunchControlID() != false ) {
			$retval = $this->getPunchControlID();
		} else {
			$pclf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $pclf */
			Debug::Text( 'Checking for incomplete punch control... User: ' . $this->getUser() . ' TimeStamp: ' . $this->getTimeStamp() . ' Status: ' . $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );

			//Need to make sure the punch is rounded before we can get the proper punch_control_id. However
			// roundTimeStamp requires punch_control_id before it can round properly.
			$retval = $pclf->getInCompletePunchControlIdByUserIdAndEpoch( $this->getUser(), $this->getTimeStamp(), $this->getStatus() );
			if ( $retval == false ) {
				Debug::Text( 'Couldnt find already existing PunchControlID, generating new one...', __FILE__, __LINE__, __METHOD__, 10 );
				$retval = $pclf->getNextInsertId();
			}
		}

		Debug::Text( 'Punch Control ID: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * @return bool|mixed
	 */
	function getPunchControlID() {
		return $this->getGenericDataValue( 'punch_control_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPunchControlID( $value ) {
		$value = trim( $value );
		//Can't check to make sure the PunchControl row exists, as it may be inserted later. So just
		//make sure its an non-zero INT.
		/*
				if (  $this->Validator->isResultSetWithRows(	'punch_control',
																$pclf->getByID($id),
																TTi18n::gettext('Invalid Punch Control ID')
																) ) {
					$this->setGenericDataValue( 'punch_control_id', $id );

					return TRUE;
				}
		*/

		return $this->setGenericDataValue( 'punch_control_id', $value );
	}

	/**
	 * @return bool
	 */
	function getTransfer() {
		return $this->fromBool( $this->getGenericDataValue( 'transfer' ) );
	}

	/**
	 * @param $value
	 * @param null $time_stamp
	 * @return bool
	 */
	function setTransfer( $value, $time_stamp = null ) {
		//If a timestamp is passed, check for the previous punch, if one does NOT exist, transfer can not be enabled.
		if ( $value == true && $time_stamp != '' && $this->isNew() && $this->getEnableSplitPunchControl() == false ) { //If the punch isn't a new one, always accept the transfer flag so we don't mistakenly round punches that are transfer punches when an administrator edits them.
			$prev_punch_obj = $this->getPreviousPunchObject( $time_stamp, $this->getUser(), true );
			//Make sure we check that the previous punch wasn't an out punch from the last shift.
			if ( !is_object( $prev_punch_obj ) || ( is_object( $prev_punch_obj ) && $prev_punch_obj->getStatus() == 20 ) ) {
				Debug::Text( 'Previous punch does not exist, or it was an OUT punch, transfer cannot be enabled. EPOCH: ' . $time_stamp, __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		}

		return $this->setGenericDataValue( 'transfer', $this->toBool( $value ) );
	}

	/**
	 * @return int
	 */
	function getNextStatus() {
		if ( $this->getStatus() == 10 ) {
			return 20;
		}

		return 10;
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
		$value = (int)trim( $value );
		Debug::text( ' Status: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return int
	 */
	function getNextType( $epoch = null ) {
		if ( $this->getStatus() == 10 ) { //In
			$next_type = 10; //Normal
		} else { //20 = Out
			$next_type = $this->getType();
		}

		//$this object should always be the previous punch.
		if ( $epoch > 0 && TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() && $this->getUser() != TTUUID::getNotExistID() ) {
			Debug::Text( ' Previous Punch Type: ' . $this->getType() . ' Status: ' . $this->getStatus() . ' Epoch: ' . $epoch . ' User ID: ' . $this->getUser(), __FILE__, __LINE__, __METHOD__, 10 );

			//Optimization to only call findScheduleID() once.
			if ( ( $next_type != 30 && $this->getType() != 30 ) || ( $next_type != 20 && $this->getType() != 20 ) ) {
				//Make sure we send $status_id=FALSE so when outside of the schedule start/end time we can still match to a schedule.
				//  Otherwise findScheduleID() uses $this->getStatus() and since when calling getNextType() its often on the previous punch object,
				//  the status will be 10=In when the user is attempting to Punch Out now.
				$this->setScheduleID( $this->findScheduleID( $epoch, null, false ) );
			}

			//Check for break policy window.
			//With Time Window auto-detection, ideally we would filter on $this->getStatus() != 20, so we don't try to detect explicit OUT punches.
			//However with Punch Time auto-detection, ideally we would filter on $this->getStatus() != 10 so we don't try to detect explicity IN punches.
			//So because of the above, we can't filter based on status at all until we know what the break/lunch policy requires.
			//Do the filter in inBreakPolicyWindow()/inMealPolicyWindow().
			//if ( $next_type != 30 AND ( $this->getStatus() != 20 AND $this->getType() != 30 ) ) {
			if ( $next_type != 30 && $this->getType() != 30 ) {
				if ( $this->inBreakPolicyWindow( $epoch, $this->getTimeStamp(), $this->getStatus() ) == true ) {
					Debug::Text( ' Setting Type to Break...', __FILE__, __LINE__, __METHOD__, 10 );
					$next_type = 30;
				}
			}

			//Check for meal policy window.
			//if ( $next_type != 20 AND ( $this->getStatus() != 20 AND $this->getType() != 20 ) ) {
			if ( $next_type != 20 && $this->getType() != 20 ) {
				if ( $this->inMealPolicyWindow( $epoch, $this->getTimeStamp(), $this->getStatus() ) == true ) {
					Debug::Text( ' Setting Type to Lunch...', __FILE__, __LINE__, __METHOD__, 10 );
					$next_type = 20;
				}
			}

			//To help with punch reminder notifications, try to be smarter about what might likely be a break/lunch punch when the user has already started their shift and is punching out.
			//  For example if the user is punching out near the beginning or end of their shift, assume it might be a break? A break policy must be specified too.
			//  If they are punching near the middle of their shift assume it might be a lunch? A meal policy must be specified too.
			//
			//  Do this by trying to predict each punch in the day based on the policies, for example:
			//  Start: 8AM
			//  Break 1: 10:00AM
			//  Break 1: 10:15AM
			//  Lunch: 12PM
			//  Lunch: 1PM
			//  Break 2: 3:00PM
			//  Break 2: 3:15PM
			//  End: 5PM
			//    Then try to figure out which punch we are closest too and use that as the next punch type.
			if ( $next_type == 10 &&
					(
						( $this->getStatus() == 10 ) //Previous Punch is Normal/Lunch/Break In -- Required to handle cases where they maybe went for two breaks in a row.
						||
						( $this->getStatus() == 20 && ( $this->getType() == 20 || $this->getType() == 30 ) ) //Previous Punch is Break/Lunch Out
					)
				) {

				//If no meal/break policies exist, it can't be any type other than a normal punch by default.
				$meal_policy_arr = $this->getMealPolicies();
				$mplf = $meal_policy_arr['lf'];
				Debug::Text( 'Meal Policy Record Count: ' . $mplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

				$break_policy_arr = $this->getBreakPolicies();
				$bplf = $break_policy_arr['lf'];
				Debug::Text( 'Break Policy Record Count: ' . $bplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $mplf->getRecordCount() > 0 || $bplf->getRecordCount() > 0 ) {
					$time_stamps_to_match = [];

					//Start time should be the shift start time, not the previous punch start time.
					//Get shift data here.
					if ( is_object( $this->getPunchControlObject() ) ) {
						$this->getPunchControlObject()->setPunchObject( $this );
						$shift_data = $this->getPunchControlObject()->getShiftData();
						if ( is_array( $shift_data ) && isset( $shift_data['first_in'] ) ) {
							Debug::Text( ' Shift First In Punch: ' . TTDate::getDate( 'DATE+TIME', $shift_data['first_in']['time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10 );
							$start_epoch = $shift_data['first_in']['time_stamp'];
						}
					}

					if ( is_object( $this->getScheduleObject() ) ) {
						if ( !isset( $start_epoch ) ) {
							$start_epoch = $this->getScheduleObject()->getStartTime(); //Keep these here in case PunchControlObject can't be determined.
						}
						$time_stamps_to_match[] = [ 'time_stamp' => $this->getScheduleObject()->getStartTime(), 'type_id' => 10 ]; //10=Normal
						$time_stamps_to_match[] = [ 'time_stamp' => $this->getScheduleObject()->getEndTime(), 'type_id' => 10 ]; //10=Normal

						//These are needed to determine if we are outside the schedule time.
						$shift_start_time = $this->getScheduleObject()->getStartTime();
						$shift_end_time = $this->getScheduleObject()->getEndTime();
					} else {
						if ( !isset( $start_epoch ) ) {
							$start_epoch = $epoch; //Keep these here in case PunchControlObject can't be determined.
						}

						//No Schedule found, try to figure out common shifts that the employee has worked in the past.
						$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
						$start_shift_most_common_data = $plf->getMostCommonPunchDataByCompanyIdAndUserAndTypeAndStatusAndStartDateAndEndDate( $this->getUserObject()->getCompany(), $this->getUser(), 10, 10, TTDate::getBeginWeekEpoch( $epoch ), $epoch, true ); //Normal In
						if ( count( (array)$start_shift_most_common_data ) == 0 ) { //Extend the date range to find some value.
							Debug::Text( 'No punches to get common default from, extend range back one more week...', __FILE__, __LINE__, __METHOD__, 10 );
							$start_shift_most_common_data = $plf->getMostCommonPunchDataByCompanyIdAndUserAndTypeAndStatusAndStartDateAndEndDate( $this->getUserObject()->getCompany(), $this->getUser(), 10, 10, TTDate::getBeginWeekEpoch( ( TTDate::getMiddleDayEpoch( $epoch ) - ( 86400 * 7 ) ) ), $epoch, true ); //Normal In
						}
						if ( isset( $start_shift_most_common_data['time_stamp'] ) ) {
							$shift_start_time = TTDate::getTimeLockedDate( TTDate::strtotime( $start_shift_most_common_data['time_stamp'] ), $epoch ); //These are needed to determine if we are outside the schedule time.
							$time_stamps_to_match[] = [ 'time_stamp' => $shift_start_time, 'type_id' => 10 ]; //10=Normal
						} else {
							$shift_start_time = $shift_data['first_in']['time_stamp'];
							$time_stamps_to_match[] = [ 'time_stamp' => $shift_start_time, 'type_id' => 10 ]; //10=Normal
						}

						$end_shift_most_common_data = $plf->getMostCommonPunchDataByCompanyIdAndUserAndTypeAndStatusAndStartDateAndEndDate( $this->getUserObject()->getCompany(), $this->getUser(), 10, 20, TTDate::getBeginWeekEpoch( $epoch ), $epoch, true ); //Normal Out
						if ( count( (array)$end_shift_most_common_data ) == 0 ) { //Extend the date range to find some value.
							Debug::Text( 'No punches to get common default from, extend range back one more week...', __FILE__, __LINE__, __METHOD__, 10 );
							$end_shift_most_common_data = $plf->getMostCommonPunchDataByCompanyIdAndUserAndTypeAndStatusAndStartDateAndEndDate( $this->getUserObject()->getCompany(), $this->getUser(), 10, 20, TTDate::getBeginWeekEpoch( ( TTDate::getMiddleDayEpoch( $epoch ) - ( 86400 * 7 ) ) ), $epoch, true ); //Normal Out
						}
						if ( isset( $end_shift_most_common_data['time_stamp'] ) ) {
							$shift_end_time = TTDate::getTimeLockedDate( TTDate::strtotime( $end_shift_most_common_data['time_stamp'] ), $epoch ); //These are needed to determine if we are outside the schedule time.
							$time_stamps_to_match[] = [ 'time_stamp' => $shift_end_time, 'type_id' => 10 ]; //10=Normal
						} else if ( isset( $shift_data['first_in']['time_stamp'] ) ) {
							//No past out shifts at all, so just assume an 8hr + 1hr lunch = 9hr shift.
							//  If we don't have an end of shift estimate, than a meal/break policy in the middle the day will always be the closet matches and throw off the next type detection.
							$shift_end_time = ( $shift_data['first_in']['time_stamp'] + ( 3600 * 9 ) ); //These are needed to determine if we are outside the schedule time.
							$time_stamps_to_match[] = [ 'time_stamp' => $shift_end_time, 'type_id' => 10 ]; //10=Normal
						}
						unset( $plf, $start_shift_most_common_data, $end_shift_most_common_data );
					}

					//Find a schedule first and determine if we are inside the start/end time.
					// If Yes:
					//   Lookup meal/break policies and determine which we are closest too.
					// If No:
					//   No need to lookup meal/break policies, just assume Type=Normal
					if ( $epoch >= $shift_start_time && $epoch <= $shift_end_time ) {
						$has_include_multiple_break_policies = false;
						$min_threshold_for_non_break = false;

						//Check break policies that apply and see if it autodetects by Punch Time.
						if ( $bplf->getRecordCount() > 0 ) {
							//Do one pass first to see if we need to ever combine multiple breaks.
							foreach( $bplf as $bp_obj ) {
								if ( $bp_obj->getIncludeMultipleBreaks() == true ) {
									$has_include_multiple_break_policies = true;
									break;
								}
							}

							//Only consider breaks if multiple breaks are combined, or the user hasn't punched out for enough breaks yet.
							//Once a user has taken a break, remove it from the list for detection.
							if ( $has_include_multiple_break_policies == true || ( isset( $shift_data ) && isset( $shift_data['break']['total'] ) && $shift_data['break']['total'] < $bplf->getRecordCount() ) ) {
								$b = 1;
								foreach ( $bplf as $bp_obj ) {
									//How do we handle cases where the employee is never expected to punch in/out for break, but it should just be auto-deducted every shift.
									//  There may be cases where the employee ends their shift early and it will detect that as a Break Out punch, when the employee should really never be punching for break.
									//  This is where 20=ProActive and 25=Reactive detection types come into play.
									if ( $has_include_multiple_break_policies == true || ( isset( $shift_data ) && isset( $shift_data['break']['total'] ) && $shift_data['break']['total'] < $b ) ) {
										if ( $bp_obj->getAutoDetectType() == 20 && $bp_obj->getAmount() > 0 && $bp_obj->getMaximumPunchTime() > 0 ) {  //20=Auto-Detect by Punch Time (ProActive) -- See inBreakPolicyWindow() for how other break policies are detected.
											$time_stamps_to_match[] = [ 'time_stamp' => ( $start_epoch + $bp_obj->getTriggerTime() ), 'type_id' => 30 ];                                  //30=Break -- Start
											$time_stamps_to_match[] = [ 'time_stamp' => ( $start_epoch + $bp_obj->getTriggerTime() + $bp_obj->getMaximumPunchTime() ), 'type_id' => 30 ]; //30=Break -- End

											if ( $has_include_multiple_break_policies == true ) {
												$min_threshold_for_non_break = ( $bp_obj->getMaximumPunchTime() * 2 ); //Double the maximum punch time for break. Mainly needed in case no meal policy exists.
											}
											Debug::Text( '  Break Window Time Stamp: Start: ' . TTDate::getDate( 'DATE+TIME', ( $start_epoch + $bp_obj->getTriggerTime() ) ) . ' End: ' . TTDate::getDate( 'DATE+TIME', ( $start_epoch + $bp_obj->getTriggerTime() + $bp_obj->getMaximumPunchTime() ) ) .' Min Threshold: '. $min_threshold_for_non_break, __FILE__, __LINE__, __METHOD__, 10 );
										}
									}

									$b++;
								}
							}
						}
						unset( $bplf );

						//If lunch has already been taken, don't bother trying to detect it again.
						if ( isset( $shift_data ) && isset( $shift_data['lunch']['total'] ) && $shift_data['lunch']['total'] == 0 ) {
							//Check meal policy that applies and see if it autodetects by Punch Time.
							if ( $mplf->getRecordCount() > 0 ) {
								foreach( $mplf as $mp_obj ) { /** @var MealPolicyFactory $mp_obj */
									//How do we handle cases where the employee is never expected to punch in/out for lunch, but it should just be auto-deducted every shift.
									//  There may be cases where the employee ends their shift early and it will detect that as a Lunch Out punch, when the employee should really never be punching for lunch.
									//  This is where 20=ProActive and 25=Reactive detection types come into play.
									if ( $mp_obj->getAutoDetectType() == 20 && $mp_obj->getAmount() > 0 && $mp_obj->getMaximumPunchTime() > 0 ) { //20=Auto-Detect by Punch Time (ProActive) -- See inMealPolicyWindow() for how other meal policies are detected.
										$time_stamps_to_match[] = [ 'time_stamp' => ( $start_epoch + $mp_obj->getTriggerTime() ), 'type_id' => 20 ];                                  //20=Lunch -- Start
										$time_stamps_to_match[] = [ 'time_stamp' => ( $start_epoch + $mp_obj->getTriggerTime() + $mp_obj->getMaximumPunchTime() ), 'type_id' => 20 ]; //20=Lunch -- End

										if ( $has_include_multiple_break_policies == true ) {
											$min_threshold_for_non_break = ( $mp_obj->getMaximumPunchTime() / 2 ); //One half of the maximum punch time allowed for lunch.
										}
										Debug::Text( '  Lunch Window Time Stamp: Start: ' . TTDate::getDate( 'DATE+TIME', ( $start_epoch + $mp_obj->getTriggerTime() ) ) . ' End: ' . TTDate::getDate( 'DATE+TIME', ( $start_epoch + $mp_obj->getTriggerTime() + $mp_obj->getMaximumPunchTime() ) ) .' Min Threshold: '. $min_threshold_for_non_break, __FILE__, __LINE__, __METHOD__, 10 );
									}
								}
							}
							unset( $mplf );
						}

						$time_stamps_to_match = Sort::Multisort( $time_stamps_to_match, 'time_stamp', null, 'asc', null );
						Debug::Arr( $time_stamps_to_match, 'TimeStamps to Match: Epoch: '. TTDate::getDate('DATE+TIME', $epoch ) .' Min Threshold: '. $min_threshold_for_non_break, __FILE__, __LINE__, __METHOD__, 10 );
						$best_match_arr = false;
						$best_diff = false;
						foreach( $time_stamps_to_match as $key => $time_stamp_match_arr ) {
							$next_key = ($key + 1);

							$diff = abs( $epoch - $time_stamp_match_arr['time_stamp'] );

							if ( isset( $time_stamps_to_match[$next_key] ) ) { //This is not the last timestamp.
								$next_time_stamp_match_arr = $time_stamps_to_match[$next_key];
								if ( in_array( $time_stamp_match_arr['type_id'], [ 20, 30 ] ) && $time_stamp_match_arr['type_id'] == $next_time_stamp_match_arr ) { //Next timestamp matches same type as this one, so its likely the end of the break/shift
									if ( $epoch >= $time_stamp_match_arr['time_stamp'] && $epoch <= $next_time_stamp_match_arr['time_stamp'] ) {
										$diff = 0; //Within break/meal time.
									}
								}
							}

							if ( $min_threshold_for_non_break === false || $time_stamp_match_arr['type_id'] == 30 || ( $time_stamp_match_arr['type_id'] != 30 && $diff <= $min_threshold_for_non_break ) ) {
								if ( $best_diff === false || $diff < $best_diff ) {
									$best_diff = $diff;
									$best_match_arr = $time_stamp_match_arr;
								}
							} else {
								Debug::Text( '  Difference is outside min. threshold: Diff: '. $diff .' Threshold: ' . $min_threshold_for_non_break .' Type: '. $time_stamp_match_arr['type_id'], __FILE__, __LINE__, __METHOD__, 10 );
							}
						}

						if ( is_array( $best_match_arr ) ) {
							Debug::Text( 'Best Match Data: TimeStamp: '. TTDate::getDate('DATE+TIME', $best_match_arr['time_stamp'] ) .' Type: '. $best_match_arr['type_id'], __FILE__, __LINE__, __METHOD__, 10 );
							$next_type = $best_match_arr['type_id'];
						}
					} else {
						//Outside schedule start/end time, so assume its a normal punch.
						$next_type = 10; //10=Normal
					}
				} else {
					Debug::Text( 'No Meal/Break Policies, assuming Type: Normal...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		Debug::Text( ' Next Type: '. $next_type, __FILE__, __LINE__, __METHOD__, 10 );
		return (int)$next_type;
	}

	/**
	 * @return bool|string
	 */
	function getTypeCode() {
		if ( $this->getType() != 10 ) {
			$options = $this->getOptions( 'type' );

			return substr( $options[$this->getType()], 0, 1 );
		}

		return false;
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
	function getStation() {
		return $this->getGenericDataValue( 'station_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStation( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'station_id', $value );
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool|int|mixed
	 */
	function roundTimeStamp( $epoch ) {
		if ( $epoch == '' ) {
			return false;
		}

		$original_epoch = $epoch;

		//$is_transfer = ( ( $this->getTransfer() == TRUE OR $this->getEnableAutoTransfer() ) ? TRUE : FALSE ); //This broke rounding on timeclocks for the first IN punch of the day when a Branch/Department/Job/Task was specified.
		$is_transfer = $this->getTransfer();

		//Punch control is no longer used for rounding.
		//Check for rounding policies.
		$riplf = TTnew( 'RoundIntervalPolicyListFactory' ); /** @var RoundIntervalPolicyListFactory $riplf */
		$type_id = $riplf->getPunchTypeFromPunchStatusAndType( $this->getStatus(), $this->getType(), $is_transfer );
		Debug::text( ' Rounding Timestamp: ' . TTDate::getDate( 'DATE+TIME', $epoch ) . '(' . $epoch . ') Status ID: ' . $this->getStatus() . ' Type ID: ' . $this->getType() . ' Round Policy Type: ' . $type_id . ' Is Transfer: ' . (int)$is_transfer, __FILE__, __LINE__, __METHOD__, 10 );

		$riplf->getByPolicyGroupUserIdAndTypeId( $this->getUser(), $type_id );
		Debug::text( ' Round Interval Punch Type: ' . $type_id . ' User: ' . $this->getUser() . ' Total Records: ' . $riplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $riplf->getRecordCount() > 0 ) {
			$is_rounded_timestamp = false;
			//Loop over each rounding policy testing the conditionals and rounding the punch if necessary.
			foreach ( $riplf as $round_policy_obj ) { /** @var RoundIntervalPolicyFactory $round_policy_obj */
				Debug::text( ' Found Rounding Policy: ' . $round_policy_obj->getName() . '(' . $round_policy_obj->getId() . ') Punch Type: ' . $round_policy_obj->getPunchType() . ' Conditional Type: ' . $round_policy_obj->getConditionType(), __FILE__, __LINE__, __METHOD__, 10 );

				//FIXME: It will only do proper total rounding if they edit the Lunch Out punch.
				//We need to account for cases when they edit just the Lunch In Punch.
				if ( $round_policy_obj->getPunchType() == 100 ) {
					Debug::text( 'Lunch Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

					//On Lunch Punch In (back from lunch) do the total rounding.
					if ( $this->getStatus() == 10 && $this->getType() == 20 ) {
						Debug::text( 'bLunch Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
						//If strict is set, round to scheduled lunch time?
						//Find Lunch Punch In.

						//Make sure we include both lunch and normal punches when searching for the previous punch, because with Punch Time detection meal policies
						//the previous punch will never be lunch, just normal, but with Time Window meal policies it will be lunch. This is critical for Lunch rounding
						//with Punch Time detection meal policies.
						//There was a bug where if Lunch Total rounding is enabled, and auto-detect punches by Punch Time is also enabled,
						//this won't round the Lunch In punch because the Lunch Out punch hasn't been designated until changePreviousPunchType() is called in PunchControlFactory::preSave().
						//which doesn't happen until later.
						$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
						$plf->getPreviousPunchByUserIdAndStatusAndTypeAndEpoch( $this->getUser(), 20, [ 10, 20 ], $epoch );
						if ( $plf->getRecordCount() == 1 ) {
							Debug::text( 'Found Lunch Punch Out: ' . TTDate::getDate( 'DATE+TIME', $plf->getCurrent()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

							$total_lunch_time = ( $epoch - $plf->getCurrent()->getTimeStamp() );
							Debug::text( 'Total Lunch Time: ' . $total_lunch_time, __FILE__, __LINE__, __METHOD__, 10 );

							//Test rounding condition, needs to happen after we attempt to get the schedule at least.
							if ( $round_policy_obj->isConditionTrue( $total_lunch_time, false ) == false ) {
								continue;
							}

							//Set the ScheduleID
							$has_schedule = $this->setScheduleID( $this->findScheduleID( $epoch ) );

							//Combine all break policies together.
							$meal_policy_time = 0;
							if ( is_object( $this->getScheduleObject() ) && is_object( $this->getScheduleObject()->getSchedulePolicyObject() ) ) {
								$meal_policy_ids = $this->getScheduleObject()->getSchedulePolicyObject()->getMealPolicy();
								if ( is_array( $meal_policy_ids ) ) {
									$meal_policy_data = [];
									foreach ( $meal_policy_ids as $meal_policy_id ) {
										$meal_policy_obj = $this->getScheduleObject()->getSchedulePolicyObject()->getMealPolicyObject( $meal_policy_id );
										if ( is_object( $meal_policy_obj ) ) {
											$meal_policy_data[$meal_policy_obj->getTriggerTime()] = $meal_policy_obj->getAmount();
										}
									}
									krsort( $meal_policy_data );

									if ( is_array( $meal_policy_data ) ) {
										foreach ( $meal_policy_data as $meal_policy_trigger_time => $tmp_meal_policy_time ) {
											Debug::text( 'Checking Meal Policy Trigger Time: ' . $meal_policy_trigger_time . ' Schedule Time: ' . $this->getScheduleObject()->getTotalTime(), __FILE__, __LINE__, __METHOD__, 10 );
											if ( $this->getScheduleObject()->getTotalTime() >= $meal_policy_trigger_time ) {
												$meal_policy_time = $tmp_meal_policy_time;
												break;
											}
										}
									}
								}
							}
							unset( $meal_policy_id, $meal_policy_ids, $meal_policy_data, $meal_policy_trigger_time, $tmp_meal_policy_time );
							Debug::text( 'Meal Policy Time: ' . $meal_policy_time, __FILE__, __LINE__, __METHOD__, 10 );

							if ( $has_schedule == true && $round_policy_obj->getGrace() > 0 ) {
								Debug::text( ' Applying Grace Period: ', __FILE__, __LINE__, __METHOD__, 10 );
								$total_lunch_time = TTDate::graceTime( $total_lunch_time, $round_policy_obj->getGrace(), $meal_policy_time );
								Debug::text( 'After Grace: ' . $total_lunch_time, __FILE__, __LINE__, __METHOD__, 10 );
							}

							if ( $round_policy_obj->getInterval() > 0 ) {
								Debug::Text( ' Rounding to interval: ' . $round_policy_obj->getInterval(), __FILE__, __LINE__, __METHOD__, 10 );
								$total_lunch_time = TTDate::roundTime( $total_lunch_time, $round_policy_obj->getInterval(), $round_policy_obj->getRoundType(), $round_policy_obj->getGrace() );
								Debug::text( 'After Rounding: ' . $total_lunch_time, __FILE__, __LINE__, __METHOD__, 10 );
							}

							if ( $has_schedule == true && $round_policy_obj->getStrict() == true ) {
								Debug::Text( ' Snap Time: Round Type: ' . $round_policy_obj->getRoundType(), __FILE__, __LINE__, __METHOD__, 10 );
								if ( $round_policy_obj->getRoundType() == 10 ) {
									Debug::Text( ' Snap Time DOWN ', __FILE__, __LINE__, __METHOD__, 10 );
									$total_lunch_time = TTDate::snapTime( $total_lunch_time, $meal_policy_time, 'DOWN' );
								} else if ( $round_policy_obj->getRoundType() == 30 ) {
									Debug::Text( ' Snap Time UP', __FILE__, __LINE__, __METHOD__, 10 );
									$total_lunch_time = TTDate::snapTime( $total_lunch_time, $meal_policy_time, 'UP' );
								} else {
									Debug::Text( ' Not Snaping Time', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}

							$epoch = ( $plf->getCurrent()->getTimeStamp() + $total_lunch_time );
							Debug::text( 'Epoch after total rounding is: ' . $epoch . ' - ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
							$is_rounded_timestamp = true;
						} else {
							Debug::text( 'DID NOT Find Lunch Punch Out: ' . TTDate::getDate( 'DATE+TIME', $plf->getCurrent()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::text( 'Skipping Lunch Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else if ( $round_policy_obj->getPunchType() == 110 ) { //Break Total
					Debug::text( 'Break Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

					//On break Punch In (back from break) do the total rounding.
					if ( $this->getStatus() == 10 && $this->getType() == 30 ) {
						Debug::text( 'bbreak Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
						//If strict is set, round to scheduled break time?
						//Find break Punch In.

						//Make sure we include both break and normal punches when searching for the previous punch, because with Punch Time detection meal policies
						//the previous punch will never be break, just normal, but with Time Window meal policies it will be break. This is critical for break rounding
						//with Punch Time detection meal policies.
						$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
						$plf->getPreviousPunchByUserIdAndStatusAndTypeAndEpoch( $this->getUser(), 20, [ 10, 30 ], $epoch );
						if ( $plf->getRecordCount() == 1 ) {
							Debug::text( 'Found break Punch Out: ' . TTDate::getDate( 'DATE+TIME', $plf->getCurrent()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

							$total_break_time = ( $epoch - $plf->getCurrent()->getTimeStamp() );
							Debug::text( 'Total break Time: ' . $total_break_time, __FILE__, __LINE__, __METHOD__, 10 );

							//Test rounding condition, needs to happen after we attempt to get the schedule at least.
							if ( $round_policy_obj->isConditionTrue( $total_break_time, false ) == false ) {
								continue;
							}

							//Set the ScheduleID
							$has_schedule = $this->setScheduleID( $this->findScheduleID( $epoch ) );

							//Combine all break policies together.
							$break_policy_time = 0;
							if ( is_object( $this->getScheduleObject() ) && is_object( $this->getScheduleObject()->getSchedulePolicyObject() ) ) {
								$break_policy_ids = $this->getScheduleObject()->getSchedulePolicyObject()->getBreakPolicy();
								if ( is_array( $break_policy_ids ) ) {
									foreach ( $break_policy_ids as $break_policy_id ) {
										if ( is_object( $this->getScheduleObject()->getSchedulePolicyObject()->getBreakPolicyObject( $break_policy_id ) ) ) {
											$break_policy_time += $this->getScheduleObject()->getSchedulePolicyObject()->getBreakPolicyObject( $break_policy_id )->getAmount();
										}
									}
								}
							}
							unset( $break_policy_id, $break_policy_ids );
							Debug::text( 'Break Policy Time: ' . $break_policy_time, __FILE__, __LINE__, __METHOD__, 10 );

							if ( $has_schedule == true && $round_policy_obj->getGrace() > 0 ) {
								Debug::text( ' Applying Grace Period: ', __FILE__, __LINE__, __METHOD__, 10 );
								$total_break_time = TTDate::graceTime( $total_break_time, $round_policy_obj->getGrace(), $break_policy_time );
								Debug::text( 'After Grace: ' . $total_break_time, __FILE__, __LINE__, __METHOD__, 10 );
							}

							if ( $round_policy_obj->getInterval() > 0 ) {
								Debug::Text( ' Rounding to interval: ' . $round_policy_obj->getInterval(), __FILE__, __LINE__, __METHOD__, 10 );
								$total_break_time = TTDate::roundTime( $total_break_time, $round_policy_obj->getInterval(), $round_policy_obj->getRoundType(), $round_policy_obj->getGrace() );
								Debug::text( 'After Rounding: ' . $total_break_time, __FILE__, __LINE__, __METHOD__, 10 );
							}

							if ( $has_schedule == true && $round_policy_obj->getStrict() == true ) {
								Debug::Text( ' Snap Time: Round Type: ' . $round_policy_obj->getRoundType(), __FILE__, __LINE__, __METHOD__, 10 );
								if ( $round_policy_obj->getRoundType() == 10 ) {
									Debug::Text( ' Snap Time DOWN ', __FILE__, __LINE__, __METHOD__, 10 );
									$total_break_time = TTDate::snapTime( $total_break_time, $break_policy_time, 'DOWN' );
								} else if ( $round_policy_obj->getRoundType() == 30 ) {
									Debug::Text( ' Snap Time UP', __FILE__, __LINE__, __METHOD__, 10 );
									$total_break_time = TTDate::snapTime( $total_break_time, $break_policy_time, 'UP' );
								} else {
									Debug::Text( ' Not Snaping Time', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}

							$epoch = ( $plf->getCurrent()->getTimeStamp() + $total_break_time );
							Debug::text( 'Epoch after total rounding is: ' . $epoch . ' - ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

							$is_rounded_timestamp = true;
						} else {
							Debug::text( 'DID NOT Find break Punch Out: ' . TTDate::getDate( 'DATE+TIME', $plf->getCurrent()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::text( 'Skipping break Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else if ( $round_policy_obj->getPunchType() == 120 ) { //Day Total Rounding
					Debug::text( 'Day Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $this->getStatus() == 20 && $this->getType() == 10 ) { //Out, Type Normal
						Debug::text( 'bDay Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

						//If strict is set, round to scheduled time?
						$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
						$plf->getPreviousPunchByUserIdAndEpochAndNotPunchIDAndMaximumShiftTime( $this->getUser(), $epoch, $this->getId() );
						if ( $plf->getRecordCount() == 1 ) {
							Debug::text( 'Found Previous Punch In: ' . TTDate::getDate( 'DATE+TIME', $plf->getCurrent()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

							//Get day total time prior to this punch control.
							$pclf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $pclf */
							$pclf->getByUserIdAndDateStamp( $this->getUser(), $plf->getCurrent()->getPunchControlObject()->getDateStamp() );
							if ( $pclf->getRecordCount() > 0 ) {
								$day_total_time = ( $epoch - $plf->getCurrent()->getTimeStamp() );
								Debug::text( 'aDay Total Time: ' . $day_total_time . ' Current Punch Control ID: ' . $this->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10 );

								foreach ( $pclf as $pc_obj ) {
									if ( $plf->getCurrent()->getPunchControlID() != $pc_obj->getID() ) {
										Debug::text( 'Punch Control Total Time: ' . $pc_obj->getTotalTime() . ' ID: ' . $pc_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
										$day_total_time += $pc_obj->getTotalTime();
									}
								}

								//Take into account paid meal/breaks when doing day total rounding...
								//  Since we don't know what time will be added/deducted when creating a new punch, but it has already happened when editing an existing punch,
								//  the calculations should be based on the RAW time taken only. So we need to add back in any meal/break time taken but not auto-deducted/added.
								//  **If the Meal/Break policy active after setting is greater than the first part of the shift before lunch, then when creating a new punch the rounding will not work, because the policy hasn't been calculated yet.
								//      So if the rounding isn't being calculated correctly, they may need to set the active after setting lower, to ensure the meal policy is calculated before the last punch of the day.
								//  See testRoundingDayTotal*() unit tests.
								$meal_and_break_adjustment = 0;
								$udtlf = TTnew( 'UserDateTotalListFactory' ); /** @var UserDateTotalListFactory $udtlf */
								$udtlf->getByUserIdAndDateStampAndObjectType( $this->getUser(), $plf->getCurrent()->getPunchControlObject()->getDateStamp(), [ 100, 110 ] ); //Only consider time calculated from meal/break policies. Not time taken.
								if ( $udtlf->getRecordCount() > 0 ) {
									foreach ( $udtlf as $udt_obj ) {
										$meal_and_break_adjustment += $udt_obj->getTotalTime();
									}
									Debug::text( 'Meal and Break Adjustment: ' . $meal_and_break_adjustment . ' Records: ' . $udtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
								}
								$day_total_time += $meal_and_break_adjustment;

								$original_day_total_time = $day_total_time;

								Debug::text( 'cDay Total Time: ' . $day_total_time, __FILE__, __LINE__, __METHOD__, 10 );
								if ( $day_total_time > 0 ) {
									//Need to handle split shifts properly, so just like we get all punches for the user_date_id, get all schedules too.
									$has_schedule = false;
									$schedule_day_total_time = 0;

									//Test rounding condition, needs to happen after we attempt to get the schedule at least.
									if ( $round_policy_obj->isConditionTrue( $day_total_time, false ) == false ) {
										continue;
									}

									$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
									//$slf->getByUserDateId( $plf->getCurrent()->getPunchControlObject()->getUserDateID() );
									$slf->getByUserIdAndDateStamp( $this->getUser(), $plf->getCurrent()->getPunchControlObject()->getDateStamp() );
									if ( $slf->getRecordCount() > 0 ) {
										$has_schedule = true;
										foreach ( $slf as $s_obj ) {
											//Because auto-deduct meal/break policies are already accounted for in the total schedule time, they will be automatically
											//deducted once the punch is saved. So if we don't add them back in here they will be deducted twice.
											//The above happens when adding new punches, but editing existing punches need to account for any already deducted meal/break time.
											//  This was causing problems with auto-deduct meal policies where the user punched for part of the meal time, and it auto-deducted the rest.
											//  Since we don't know what time will be added/deducted when creating a new punch, but it has already happened when editing an existing punch,
											//  the calculations should be based on the RAW time only.
											//$schedule_day_total_time += ( $s_obj->getTotalTime() + abs($s_obj->getMealPolicyDeductTime( $s_obj->calcRawTotalTime(), 10 )) + abs($s_obj->getBreakPolicyDeductTime( $s_obj->calcRawTotalTime(), 10 )) + $meal_and_break_adjustment );
											$schedule_day_total_time += $s_obj->calcRawTotalTime();
										}
										Debug::text( 'Before Grace: ' . $day_total_time . ' Schedule Day Total: ' . $schedule_day_total_time, __FILE__, __LINE__, __METHOD__, 10 );
										$day_total_time = TTDate::graceTime( $day_total_time, $round_policy_obj->getGrace(), $schedule_day_total_time );
										Debug::text( 'After Grace: ' . $day_total_time, __FILE__, __LINE__, __METHOD__, 10 );
									}
									unset( $slf, $s_obj );

									if ( $round_policy_obj->getInterval() > 0 ) {
										Debug::Text( ' Rounding to interval: ' . $round_policy_obj->getInterval(), __FILE__, __LINE__, __METHOD__, 10 );
										$day_total_time = TTDate::roundTime( $day_total_time, $round_policy_obj->getInterval(), $round_policy_obj->getRoundType(), $round_policy_obj->getGrace() );
										Debug::text( 'After Rounding: ' . $day_total_time, __FILE__, __LINE__, __METHOD__, 10 );
									}

									if ( $has_schedule == true && $round_policy_obj->getStrict() == true
											&& $schedule_day_total_time > 0 ) {
										Debug::Text( ' Snap Time: Round Type: ' . $round_policy_obj->getRoundType(), __FILE__, __LINE__, __METHOD__, 10 );
										if ( $round_policy_obj->getRoundType() == 10 ) {
											Debug::Text( ' Snap Time DOWN ', __FILE__, __LINE__, __METHOD__, 10 );
											$day_total_time = TTDate::snapTime( $day_total_time, $schedule_day_total_time, 'DOWN' );
										} else if ( $round_policy_obj->getRoundType() == 30 ) {
											Debug::Text( ' Snap Time UP', __FILE__, __LINE__, __METHOD__, 10 );
											$day_total_time = TTDate::snapTime( $day_total_time, $schedule_day_total_time, 'UP' );
										} else {
											Debug::Text( ' Not Snaping Time', __FILE__, __LINE__, __METHOD__, 10 );
										}
									}

									Debug::text( 'cDay Total Time: ' . $day_total_time, __FILE__, __LINE__, __METHOD__, 10 );

									$day_total_time_diff = ( $day_total_time - $original_day_total_time );
									Debug::text( 'Day Total Diff: ' . $day_total_time_diff, __FILE__, __LINE__, __METHOD__, 10 );

									$epoch = ( $original_epoch + $day_total_time_diff );

									$is_rounded_timestamp = true;
								}
							}
						} else {
							Debug::text( 'DID NOT Find Normal Punch Out: ' . TTDate::getDate( 'DATE+TIME', $plf->getCurrent()->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::text( 'Skipping Lunch Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::text( 'NOT Total Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

					$in_schedule_window = $this->inScheduleStartStopWindow( $epoch, $this->getStatus() );
					//Test rounding condition, needs to happen after we attempt to get the schedule at least, which inScheduleStartStopWindow() does for us.
					// But this also needs to ahppen before we apply the grace period below,
					//   otherwise the epoch will already be adjusted, and makes it hard to predict what the condition will be testing against.
					if ( $round_policy_obj->isConditionTrue( $epoch, $this->getScheduleWindowTime() ) == false ) {
						continue;
					}

					if ( $in_schedule_window == true && $round_policy_obj->getGrace() > 0 ) {
						Debug::text( ' Applying Grace Period: ', __FILE__, __LINE__, __METHOD__, 10 );
						$epoch = TTDate::graceTime( $epoch, $round_policy_obj->getGrace(), $this->getScheduleWindowTime() );
						Debug::text( 'After Grace: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $in_schedule_window );

					$grace_time = $round_policy_obj->getGrace();
					//If strict scheduling is enabled, handle grace times differently.
					//Only apply them above if we are near the schedule start/stop time.
					//This allows for grace time to apply if an employee punches in late,
					//but afterwards not apply at all.
					if ( $round_policy_obj->getStrict() == true ) {
						$grace_time = 0;
					}

					if ( $round_policy_obj->getInterval() > 0 ) {
						Debug::Text( ' Rounding to interval: ' . $round_policy_obj->getInterval(), __FILE__, __LINE__, __METHOD__, 10 );
						$epoch = TTDate::roundTime( $epoch, $round_policy_obj->getInterval(), $round_policy_obj->getRoundType(), $grace_time );
						Debug::text( 'After Rounding: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
					}

					//ONLY perform strict rounding on Normal punches, not break/lunch punches?
					//Modify the UI to restrict this as well perhaps?
					if ( $round_policy_obj->getStrict() == true && $this->getScheduleWindowTime() !== false ) {
						Debug::Text( ' Snap Time: Round Type: ' . $round_policy_obj->getRoundType(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $round_policy_obj->getRoundType() == 10 ) {
							Debug::Text( ' Snap Time DOWN ', __FILE__, __LINE__, __METHOD__, 10 );
							$epoch = TTDate::snapTime( $epoch, $this->getScheduleWindowTime(), 'DOWN' );
						} else if ( $round_policy_obj->getRoundType() == 30 ) {
							Debug::Text( ' Snap Time UP', __FILE__, __LINE__, __METHOD__, 10 );
							$epoch = TTDate::snapTime( $epoch, $this->getScheduleWindowTime(), 'UP' );
						} else {
							//If its an In Punch, snap up, if its out punch, snap down?
							Debug::Text( ' Average rounding type, automatically determining snap direction.', __FILE__, __LINE__, __METHOD__, 10 );
							if ( $this->getStatus() == 10 ) {
								Debug::Text( ' Snap Time UP', __FILE__, __LINE__, __METHOD__, 10 );
								$epoch = TTDate::snapTime( $epoch, $this->getScheduleWindowTime(), 'UP' );
							} else {
								Debug::Text( ' Snap Time DOWN ', __FILE__, __LINE__, __METHOD__, 10 );
								$epoch = TTDate::snapTime( $epoch, $this->getScheduleWindowTime(), 'DOWN' );
							}
						}
					}

					$is_rounded_timestamp = true;
				}

				//In cases where employees transfer between jobs, then have rounding on just In or Out punches,
				//its possible for a punch in to be at 3:04PM and a later Out punch at 3:07PM to be rounded down to 3:00PM,
				//causing a conflict and the punch not to be saved at all.
				//In these cases don't round the punch.
				//Don't implement just yet...
				/*
				$plf = TTnew( 'PunchListFactory' );
				$plf->getPreviousPunchByUserIdAndStatusAndTypeAndEpoch( $this->getUser(), 10, array(10, 20, 30), $original_epoch );
				if ( $plf->getRecordCount() == 1 ) {
					if ( $epoch <= $plf->getCurrent()->getTimeStamp() ) {
						Debug::text(' Rounded TimeStamp is before previous punch, not rounding at all! Previous Punch: '. TTDate::getDate('DATE+TIME', $plf->getCurrent()->getTimeStamp() ) .' Rounded Time: '. TTDate::getDate('DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10);
						$epoch = $original_epoch;
					}
				}
				unset($plf, $p_obj);
				*/
			}

			//In cases where there may be one or more conditional rounding policies, if none of the conditions match, the punch still needs to be rounded down to the nearest minute,
			//otherwise its recorded to the nearest second when they may not be expecting that.
			// So when we know there are rounding policies, if none of them get applied, treat it as if there aren't any policies at all and still round to the minute.
			if ( $is_rounded_timestamp == false ) {
				Debug::text( ' Rounding Policy(s) found, but due to conditions none applied...', __FILE__, __LINE__, __METHOD__, 10 );
				$epoch = TTDate::roundTime( $epoch, 60, 10 ); //Round down.
			}
		} else {
			Debug::text( ' NO Rounding Policy(s) Found', __FILE__, __LINE__, __METHOD__, 10 );

			//Even when rounding policies don't exist,
			//  Always round *down* to the nearest minute, no matter what. Even on a transfer punch.
			//  We used to round to the nearest minute (average), however in v10.5.0 this was changed.
			//  Instead this mimics what a wall clock would show, for example 8:00:59 shows as 8:00, so if we do average rounding it would record as 8:01 which they may not expect.
			//  So if we always round down its still fair and consistent, for both IN and OUT punches, but it more closely matches a clock that they may be looking at, assuming its close to the server time at least.
			//
			//  **To disable rounding completely and record punches to the exact second, they must create a rounding policy with a interval of 0 and no grace time.
			//
			//  This is also done in setTimeStamp() when rounding policies are disabled.
			$epoch = TTDate::roundTime( $epoch, 60, 10 ); //Round down.
		}

		Debug::text( ' Rounded TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $epoch ) . '(' . $epoch . ') Original TimeStamp: ' . TTDate::getDate( 'DATE+TIME', $original_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		return $epoch;
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getTimeStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'time_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @param bool $enable_rounding
	 * @return bool
	 */
	function setTimeStamp( $value, $enable_rounding = true ) {
		$value = trim( $value );

		//We can't disable rounding if its the first IN punch and no transfer actually needs to occur.
		//Have setTransfer check to see if there is a previous punch and if not, don't allow it to be set.
		if ( $enable_rounding == true ) {
			$value = $this->roundTimeStamp( $value );
		} else {
			Debug::text( ' Rounding policies disabled... ', __FILE__, __LINE__, __METHOD__, 10 );

			//When rounding is disabled, that just disables rounding policies.
			//  Still always round *down* to the nearest minute, no matter what. Even on a transfer punch.
			//  We used to round to the nearest minute (average), however in v10.5.0 this was changed.
			//  Instead this mimics what a wall clock would show, for example 8:00:59 shows as 8:00, so if we do average rounding it would record as 8:01 which they may not expect.
			//  So if we always round down its still fair and consistent, for both IN and OUT punches, but it more closely matches a clock that they may be looking at, assuming its close to the server time at least.
			//
			//  **To disable rounding completely and record punches to the exact second, they must create a rounding policy with a interval of 0 and no grace time.
			//
			//  This is also done in roundTimeStamp() when no rounding policies apply.
			$value = TTDate::roundTime( $value, 60, 10 ); //Round down.
		}
		Debug::text( ' Set: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'time_stamp', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getOriginalTimeStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'original_time_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setOriginalTimeStamp( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'original_time_stamp', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getActualTimeStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'actual_time_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				//return $this->db->UnixTimeStamp( $this->data['start_date'] );
				//strtotime is MUCH faster than UnixTimeStamp
				//Must use ADODB for times pre-1970 though.
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setActualTimeStamp( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'actual_time_stamp', $value );
	}

	/**
	 * @return bool|float
	 */
	function getLongitude() {
		return TTMath::removeTrailingZeros( $this->getGenericDataValue( 'longitude' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLongitude( $value ) {
		if ( is_numeric( $value ) && $value != 0 ) {                           //Since we don't obtain coordinates via GEO coding, its just passed from the device, ignore 0 values as some versions of the mobile app pass in 0 values.
			$value = TTMath::removeTrailingZeros( round( (float)$value, 6 ) ); //Always use 6 decimal places as that is to 0.11m accuracy, this also prevents audit logging 0 vs 0.000000000 -- Don't use parseFloat() here as it should never be a user input value with commas as decimal symbols.
		} else {
			$value = null; //Allow $value=NULL so the coordinates can be cleared. Also make sure if FALSE is passed in here we assume NULL so it doesn't get cast to integer and saved in DB.
		}

		return $this->setGenericDataValue( 'longitude', $value );
	}

	/**
	 * @return bool|float
	 */
	function getLatitude() {
		return TTMath::removeTrailingZeros( $this->getGenericDataValue( 'latitude' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLatitude( $value ) {
		if ( is_numeric( $value ) && $value != 0 ) {                           //Since we don't obtain coordinates via GEO coding, its just passed from the device, ignore 0 values as some versions of the mobile app pass in 0 values.
			$value = TTMath::removeTrailingZeros( round( (float)$value, 6 ) ); //Always use 6 decimal places as that is to 0.11m accuracy, this also prevents audit logging 0 vs 0.000000000 -- Don't use parseFloat() here as it should never be a user input value with commas as decimal symbols.
		} else {
			$value = null; //Allow $value=NULL so the coordinates can be cleared. Also make sure if FALSE is passed in here we assume NULL so it doesn't get cast to integer and saved in DB.
		}

		return $this->setGenericDataValue( 'latitude', $value );
	}

	/**
	 * @return bool|float
	 */
	function getPositionAccuracy() {
		return $this->getGenericDataValue( 'position_accuracy' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPositionAccuracy( $value ) {
		if ( is_numeric( $value ) ) {
			$value = round( (float)trim( $value ) ); //This in whole meters.
		} else {
			$value = null; //If no position accuracy is sent, leave NULL.
		}

		return $this->setGenericDataValue( 'position_accuracy', $value );
	}

	/**
	 * @return bool
	 */
	function getScheduleID() {
		return $this->getGenericTempDataValue( 'schedule_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleID( $value ) {
		if ( $value != false ) {
			//Each time this is called, clear the ScheduleObject() cache.
			$this->schedule_obj = null;

			return $this->setGenericTempDataValue( 'schedule_id', $value );
		}

		return false;
	}

	/**
	 * @param int $epoch      EPOCH
	 * @param string $user_id UUID
	 * @return bool
	 */
	function findScheduleID( $epoch = null, $user_id = null, $status_id = null ) {
		//Debug::text(' aFinding SchedulePolicyID for this Punch: '. $epoch .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);
		if ( $epoch == '' ) {
			$epoch = $this->getTimeStamp();
		}

		if ( $epoch == false ) {
			return false;
		}

		if ( $user_id == '' && $this->getUser() == '' ) {
			Debug::text( ' User ID not specified, cant find schedule... ', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		} else if ( $user_id == '' ) {
			$user_id = $this->getUser();
		}
		//Debug::text(' bFinding SchedulePolicyID for this Punch: '. $epoch .' User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $status_id === null ) {
			$status_id = $this->getStatus();
		}

		//Check to see if this punch is within the start/stop window for the schedule.
		//We need to make sure we get schedules within about a 24hr
		//window of this punch, because if punch is at 11:55AM and the schedule starts at 12:30AM it won't
		//be found by a user_date_id.
		//In cases where an absence shift ends at the exact same time as working shift begins (Absence: 11:30PM to 7:00AM, WORKING: 7:00AM-3:00PM),
		//order the working shift first so its used instead of the absence shift.
		//These two functions are almost identical: PunchFactory::findScheduleId() and ScheduleListFactory::getScheduleObjectByUserIdAndEpoch()
		$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
		$slf->getByUserIdAndStartDateAndEndDate( $user_id, ( $epoch - 43200 ), ( $epoch + 43200 ), null, [ 'a.date_stamp' => 'asc', 'a.status_id' => 'asc', 'a.start_time' => 'desc' ] );
		if ( $slf->getRecordCount() > 0 ) {
			$retval = false;
			$best_diff = false;
			//Check for schedule policy
			foreach ( $slf as $s_obj ) {
				Debug::text( ' Checking Schedule ID: ' . $s_obj->getID() . ' Start: ' . TTDate::getDate( 'DATE+TIME', $s_obj->getStartTime() ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $s_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );

				//If the Start/Stop window is large (ie: 6-8hrs) we need to find the closest schedule.
				$schedule_diff = $s_obj->inScheduleDifference( $epoch, $status_id );
				if ( $schedule_diff === 0 ) {
					Debug::text( ' Within schedule times. ', __FILE__, __LINE__, __METHOD__, 10 );

					return $s_obj->getId();
				} else {
					if ( $schedule_diff > 0 && ( $best_diff === false || $schedule_diff < $best_diff ) ) {
						Debug::text( ' Within schedule start/stop time by: ' . $schedule_diff . ' Prev Best Diff: ' . $best_diff, __FILE__, __LINE__, __METHOD__, 10 );
						$best_diff = $schedule_diff;
						$retval = $s_obj->getId();
					}
				}
			}

			Debug::text( ' Final Schedule ID: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		} else {
			Debug::text( ' Did not find Schedule...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcSystemTotalTime() {
		if ( isset( $this->calc_system_total_time ) ) {
			return $this->calc_system_total_time;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcSystemTotalTime( $bool ) {
		$this->calc_system_total_time = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcWeeklySystemTotalTime() {
		if ( isset( $this->calc_weekly_system_total_time ) ) {
			return $this->calc_weekly_system_total_time;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcWeeklySystemTotalTime( $bool ) {
		$this->calc_weekly_system_total_time = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcException() {
		if ( isset( $this->calc_exception ) ) {
			return $this->calc_exception;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcException( $bool ) {
		$this->calc_exception = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnablePreMatureException() {
		if ( isset( $this->premature_exception ) ) {
			return $this->premature_exception;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnablePreMatureException( $bool ) {
		$this->premature_exception = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcUserDateTotal() {
		if ( isset( $this->calc_user_date_total ) ) {
			return $this->calc_user_date_total;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcUserDateTotal( $bool ) {
		$this->calc_user_date_total = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcUserDateID() {
		if ( isset( $this->calc_user_date_id ) ) {
			return $this->calc_user_date_id;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcUserDateID( $bool ) {
		$this->calc_user_date_id = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableCalcTotalTime() {
		if ( isset( $this->calc_total_time ) ) {
			return $this->calc_total_time;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableCalcTotalTime( $bool ) {
		$this->calc_total_time = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableAutoTransfer() {
		if ( isset( $this->auto_transfer ) ) {
			return $this->auto_transfer;
		}

		return false; //Default to FALSE otherwise roundTimeStamp() will treat it as a transfer punch and only apply the transfer rounding policy type to it.
		//return TRUE;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableAutoTransfer( $bool ) {
		$this->auto_transfer = $bool;

		return true;
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
	function getEnableSplitPunchControl() {
		if ( isset( $this->split_punch_control ) ) {
			return $this->split_punch_control;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableSplitPunchControl( $bool ) {
		$this->split_punch_control = $bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getScheduleWindowTime() {
		return $this->getGenericTempDataValue( 'schedule_window_time' );
	}

	/**
	 * @param int $epoch EPOCH
	 * @param int $status_id
	 * @return bool
	 */
	function inScheduleStartStopWindow( $epoch, $status_id ) {
		if ( $epoch == '' ) {
			return false;
		}

		$this->setScheduleID( $this->findScheduleID( $epoch ) );

		if ( $this->getScheduleObject() == false ) {
			return false;
		}

		//If the Start/Stop window is excessively long (like 6-8hrs) with strict rounding and an user punches in AND out within that time,
		//we have to return the schedule time in accordance to the punch status (In/Out) to prevent rounding Out punches to the schedule start time
		if ( $status_id == 10 && $this->getScheduleObject()->inStartWindow( $epoch ) == true ) { //Consider In punches only.
			Debug::text( ' Within Start window... Schedule Policy ID: ' . $this->getScheduleObject()->getSchedulePolicyID(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->setGenericTempDataValue( 'schedule_window_time', $this->getScheduleObject()->getStartTime() );

			return true;
		} else if ( $status_id == 20 && $this->getScheduleObject()->inStopWindow( $epoch ) == true ) { //Consider Out punches only.
			Debug::text( ' Within Start window... Schedule Policy ID: ' . $this->getScheduleObject()->getSchedulePolicyID(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->setGenericTempDataValue( 'schedule_window_time', $this->getScheduleObject()->getEndTime() );

			return true;
		} else {
			Debug::text( ' NOT Within Start/Stop window.', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * Gets any break policies that may apply to this punch.
	 * @return array
	 * @throws ReflectionException
	 */
	function getMealPolicies() {
		if ( $this->meal_policy_arr !== null ) {
			return $this->meal_policy_arr;
		} else {
			$is_schedule = false;

			$mplf = TTnew( 'MealPolicyListFactory' ); /** @var MealPolicyListFactory $mplf */
			if ( is_object( $this->getScheduleObject() )
					&& is_object( $this->getScheduleObject()->getSchedulePolicyObject() )
					&& $this->getScheduleObject()->getSchedulePolicyObject()->isUsePolicyGroupMealPolicy() == false ) {
				$policy_group_meal_policy_ids = $this->getScheduleObject()->getSchedulePolicyObject()->getMealPolicy();
				$mplf->getByIdAndCompanyId( $policy_group_meal_policy_ids, $this->getUserObject()->getCompany() );
				$is_schedule = true;
			} else {
				$mplf->getByPolicyGroupUserId( $this->getUser() );
			}

			$this->meal_policy_arr = [ 'lf' => $mplf, 'is_schedule' => $is_schedule ];
			return $this->meal_policy_arr;
		}
	}

	/**
	 * Run this function on the previous punch object normally.
	 * @param int $current_epoch  EPOCH
	 * @param int $previous_epoch EPOCH
	 * @param null $previous_punch_status
	 * @return bool
	 */
	function inMealPolicyWindow( $current_epoch, $previous_epoch, $previous_punch_status = null ) {
		Debug::Text( ' Checking if we are in meal policy window/punch time...', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $current_epoch == '' ) {
			return false;
		}

		if ( $previous_epoch == '' ) {
			return false;
		}

		$meal_policy_arr = $this->getMealPolicies();
		$mplf = $meal_policy_arr['lf'];
		if ( $meal_policy_arr['is_schedule'] == true ) {
			$start_epoch = $this->getScheduleObject()->getStartTime(); //Keep these here in case PunchControlObject can't be determined.
		} else {
			$start_epoch = $previous_epoch; //Keep these here in case PunchControlObject can't be determined.
		}

		Debug::Text( 'Meal Policy Record Count: ' . $mplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $mplf->getRecordCount() > 0 ) {
			$mp_obj = $mplf->getCurrent();
		}
		unset( $mplf );

		//Start time should be the shift start time, not the previous punch start time.
		//Get shift data here.
		if ( is_object( $this->getPunchControlObject() ) ) {
			$this->getPunchControlObject()->setPunchObject( $this );
			$shift_data = $this->getPunchControlObject()->getShiftData();
			if ( is_array( $shift_data ) && isset( $shift_data['first_in'] ) ) {
				Debug::Text( ' Shift First In Punch: ' . TTDate::getDate( 'DATE+TIME', $shift_data['first_in']['time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10 );
				$start_epoch = $shift_data['first_in']['time_stamp'];
			}
		}

		if ( isset( $mp_obj ) && is_object( $mp_obj ) ) {
			if ( $mp_obj->getAutoDetectType() == 10 ) { //Meal window
				Debug::Text( ' Auto Detect Type: Meal Window...', __FILE__, __LINE__, __METHOD__, 10 );

				//Make we sure ignore meals if the previous punch status was OUT.
				if ( $previous_punch_status != 20
						&& $current_epoch >= ( $start_epoch + $mp_obj->getStartWindow() )
						&& $current_epoch <= ( $start_epoch + $mp_obj->getStartWindow() + $mp_obj->getWindowLength() ) ) {
					Debug::Text( ' aPunch is in meal policy window! Current Epoch: ' . TTDate::getDate( 'DATE+TIME', $current_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			} else { //Punch time.
				Debug::Text( ' Auto Detect Type: Punch Time...', __FILE__, __LINE__, __METHOD__, 10 );
				//Make we sure ignore meals if the previous punch status was IN.
				if ( $previous_punch_status != 10
						&& ( $current_epoch - $previous_epoch ) >= $mp_obj->getMinimumPunchTime()
						&& ( $current_epoch - $previous_epoch ) <= $mp_obj->getMaximumPunchTime() ) {
					Debug::Text( ' bPunch is in meal policy window!', __FILE__, __LINE__, __METHOD__, 10 );

					return true;
				}
			}
		} else {
			Debug::Text( ' Unable to find meal policy object...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * Gets any break policies that may apply to this punch.
	 * @return array
	 * @throws ReflectionException
	 */
	function getBreakPolicies() {
		if ( $this->break_policy_arr !== null ) {
			return $this->break_policy_arr;
		} else {
			$is_schedule = false;

			$bplf = TTnew( 'BreakPolicyListFactory' ); /** @var BreakPolicyListFactory $bplf */
			if ( is_object( $this->getScheduleObject() )
					&& is_object( $this->getScheduleObject()->getSchedulePolicyObject() )
					&& $this->getScheduleObject()->getSchedulePolicyObject()->isUsePolicyGroupBreakPolicy() == false ) {
				$policy_group_break_policy_ids = $this->getScheduleObject()->getSchedulePolicyObject()->getBreakPolicy();
				$bplf->getByIdAndCompanyId( $policy_group_break_policy_ids, $this->getUserObject()->getCompany() );
				$is_schedule = true;
			} else {
				$bplf->getByPolicyGroupUserId( $this->getUser() );
			}

			$this->break_policy_arr = [ 'lf' => $bplf, 'is_schedule' => $is_schedule ];
			return $this->break_policy_arr;
		}
	}

	/**
	 * Run this function on the previous punch object normally.
	 * @param int $current_epoch  EPOCH
	 * @param int $previous_epoch EPOCH
	 * @param null $previous_punch_status
	 * @return bool
	 */
	function inBreakPolicyWindow( $current_epoch, $previous_epoch, $previous_punch_status = null ) {
		Debug::Text( ' Checking if we are in break policy window/punch time... Current: ' . TTDate::getDate( 'DATE+TIME', $current_epoch ) . ' Previous: ' . TTDate::getDate( 'DATE+TIME', $previous_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		if ( $current_epoch == '' ) {
			return false;
		}

		if ( $previous_epoch == '' ) {
			return false;
		}

		$break_policy_arr = $this->getBreakPolicies();
		$bplf = $break_policy_arr['lf'];
		if ( $break_policy_arr['is_schedule'] == true ) {
			$start_epoch = $this->getScheduleObject()->getStartTime(); //Keep these here in case PunchControlObject can't be determined.
		} else {
			$start_epoch = $previous_epoch; //Keep these here in case PunchControlObject can't be determined.
		}

		$bp_objs = [];
		if ( $bplf->getRecordCount() > 0 ) {
			foreach ( $bplf as $bp_obj ) {
				$bp_objs[] = $bp_obj;
			}
		}
		unset( $bplf );

		//Start time should be the shift start time, not the previous punch start time.
		//Get shift data here.
		if ( is_object( $this->getPunchControlObject() ) ) {
			$this->getPunchControlObject()->setPunchObject( $this );
			$shift_data = $this->getPunchControlObject()->getShiftData();
			if ( is_array( $shift_data ) && isset( $shift_data['first_in'] ) ) {
				Debug::Text( ' Shift First In Punch: ' . TTDate::getDate( 'DATE+TIME', $shift_data['first_in']['time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10 );
				$start_epoch = $shift_data['first_in']['time_stamp'];
			}
		}

		if ( empty( $bp_objs ) == false ) {
			foreach ( $bp_objs as $bp_obj ) {
				if ( $bp_obj->getAutoDetectType() == 10 ) { //Break window
					Debug::Text( ' Auto Detect Type: Break Window... Start Epoch: ' . TTDate::getDate( 'DATE+TIME', $start_epoch ), __FILE__, __LINE__, __METHOD__, 10 );

					//Make we sure ignore breaks if the previous punch status was OUT.
					if ( $previous_punch_status != 20
							&& $current_epoch >= ( $start_epoch + $bp_obj->getStartWindow() )
							&& $current_epoch <= ( $start_epoch + $bp_obj->getStartWindow() + $bp_obj->getWindowLength() ) ) {
						Debug::Text( ' aPunch is in break policy (ID:' . $bp_obj->getId() . ') window!', __FILE__, __LINE__, __METHOD__, 10 );

						return true;
					}
				} else { //Punch time.
					//Make we sure ignore breaks if the previous punch status was IN.
					Debug::Text( ' Auto Detect Type: Punch Time...', __FILE__, __LINE__, __METHOD__, 10 );
					if ( $previous_punch_status != 10
							&& ( $current_epoch - $previous_epoch ) >= $bp_obj->getMinimumPunchTime()
							&& ( $current_epoch - $previous_epoch ) <= $bp_obj->getMaximumPunchTime() ) {
						Debug::Text( ' bPunch is in break policy (ID:' . $bp_obj->getId() . ') window!', __FILE__, __LINE__, __METHOD__, 10 );

						return true;
					}
				}
			}
		} else {
			Debug::Text( ' Unable to find break policy object...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param $user_id UUID
	 * @param $id UUID
	 * @return bool
	 * @throws ReflectionException
	 */
	function isAllowedBranch( $user_id, $id ) {
		$lf = TTNew( 'BranchListFactory' );
		$lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$retval = $lf->getCurrent()->isAllowedUser( $user_id );
		} else {
			$retval = false;
		}

		if ( $retval == false ) {
			Debug::Text( ' User is not allowed on Branch: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param $user_id UUID
	 * @param $branch_id UUID
	 * @param $id UUID
	 * @return bool
	 * @throws ReflectionException
	 */
	function isAllowedDepartment( $user_id, $branch_id, $id ) {
		$lf = TTNew( 'DepartmentListFactory' );
		$lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$retval = $lf->getCurrent()->isAllowedUser( $user_id, $branch_id );
		} else {
			$retval = false;
		}

		if ( $retval == false ) {
			Debug::Text( ' User is not allowed on Department: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param $user_id UUID
	 * @param $branch_id UUID
	 * @param $department_id UUID
	 * @param $id UUID
	 * @return bool
	 * @throws ReflectionException
	 */
	function isAllowedJob( $user_id, $branch_id, $department_id, $id ) {
		$lf = TTNew( 'JobListFactory' );
		$lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			$retval = $lf->getCurrent()->isAllowedUser( $user_id, $branch_id, $department_id );
		} else {
			$retval = false;
		}

		if ( $retval == false ) {
			Debug::Text( ' User is not allowed on Job: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param $user_id UUID
	 * @param $job_id UUID
	 * @param $id UUID
	 * @return bool
	 * @throws ReflectionException
	 */
	function isAllowedJobItem( $user_id, $job_id, $id ) {
		$lf = TTNew( 'JobListFactory' );
		$lf->getById( $job_id );
		if ( $lf->getRecordCount() > 0 ) {
			$retval = $lf->getCurrent()->isAllowedItem( $id );
		} else {
			$retval = false;
		}

		if ( $retval == false ) {
			Debug::Text( ' Task is not allowed on job: Job: '. $job_id .' Task: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * @param $user_id
	 * @param $branch_id
	 * @param $department_id
	 * @param $job_id
	 * @param $job_item_id
	 * @param $id
	 * @return array|false
	 * @throws ReflectionException
	 */
	function isAllowedPunchTag( $user_id, $branch_id, $department_id, $job_id, $job_item_id, $id ) {
		$lf = TTNew( 'PunchTagListFactory' );
		$lf->getById( $id );
		if ( $lf->getRecordCount() > 0 ) {
			foreach( $lf as $punch_tag_obj ) {
				if ( $punch_tag_obj->isAllowedUser( $user_id, $branch_id, $department_id, $job_id, $job_item_id ) == true ) {
					$retval[] = $punch_tag_obj->getId();
				} else {
					Debug::Text( ' Tag is not allowed on Branch/Department/Job/Task: Tag: ' . $punch_tag_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		if ( !isset( $retval ) ) {
			$retval = false;
		}

		return $retval;
	}

	/**
	 * @param object $user_obj
	 * @param int $epoch EPOCH
	 * @param object $station_obj
	 * @param object $permission_obj
	 * @param float $latitude
	 * @param float $longitude
	 * @param float $position_accuracy
	 * @return array
	 */
	function getDefaultPunchSettings( $user_obj, $epoch, $station_obj = null, $permission_obj = null, $latitude = null, $longitude = null, $position_accuracy = null ) {
		$branch_id = $department_id = $job_id = $job_item_id = TTUUID::getZeroID();
		$punch_tag_id = [];
		$transfer = false;
		$is_previous_punch = false;

		//Ignore future punches, so auto-punch shifts in the future don't mess up default punch settings.
		$prev_punch_obj = $this->getPreviousPunchObject( $epoch, $user_obj->getId(), true ); /** @var PunchFactory $prev_punch_obj */
		if ( is_object( $prev_punch_obj ) ) {
			$is_previous_punch = true;

			$prev_punch_obj->setUser( $user_obj->getId() );
			Debug::Text( ' Found Previous Punch within Continuous Time from now: ' . TTDate::getDate( 'DATE+TIME', $prev_punch_obj->getTimeStamp() ) . ' ID: ' . $prev_punch_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );

			//Due to split shifts or multiple schedules on a single day that are close to one another, we have to be smarter about how we default punch settings.
			//We only base default punch settings on the previous punch if it was *NOT* a Normal Out punch, with the idea that the employee
			//would likely want to continue working on the same job after they come back from lunch/break, or if they haven't punched out for the end of this shift yet.
			//if ( ( is_object( $prev_punch_obj ) AND ( ( $prev_punch_obj->getStatus() == 10 AND $prev_punch_obj->getType() == 10 ) OR ( $prev_punch_obj->getStatus() == 20 AND $prev_punch_obj->getType() > 10 ) ) ) ) {
			if ( is_object( $prev_punch_obj ) && !( $prev_punch_obj->getStatus() == 20 && $prev_punch_obj->getType() == 10 ) ) {
				$branch_id = $prev_punch_obj->getPunchControlObject()->getBranch();
				$department_id = $prev_punch_obj->getPunchControlObject()->getDepartment();
				$job_id = $prev_punch_obj->getPunchControlObject()->getJob();
				$job_item_id = $prev_punch_obj->getPunchControlObject()->getJobItem();
				$punch_tag_id = $prev_punch_obj->getPunchControlObject()->getPunchTag();
			} else {
				Debug::Text( ' Not using Previous Punch settings... Prev Status: ' . $prev_punch_obj->getStatus() . ' Type: ' . $prev_punch_obj->getType(), __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( ' DID NOT Find Previous Punch within Continuous Time from now: ', __FILE__, __LINE__, __METHOD__, 10 );
		}


		if ( $branch_id == '' || empty( $branch_id ) || $branch_id == TTUUID::getZeroID()
				|| $department_id == '' || empty( $department_id ) || $department_id == TTUUID::getZeroID()
				|| $job_id == '' || empty( $job_id ) || $job_id == TTUUID::getZeroID()
				|| $job_item_id == '' || empty( $job_item_id ) || $job_item_id == TTUUID::getZeroID()
				|| $punch_tag_id == '' || empty( $punch_tag_id ) || $punch_tag_id == TTUUID::getZeroID() ) {
			Debug::Text( ' NULL values: Branch: ' . $branch_id . ' Department: ' . $department_id . ' Job: ' . $job_id . ' Task: ' . $job_item_id, __FILE__, __LINE__, __METHOD__, 10 );

			$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
			$s_obj = $slf->getScheduleObjectByUserIdAndEpoch( $user_obj->getId(), $epoch );

			//Try to handle cases where the employee forgets to punch in at 8AM then they punch at 5PM (as that should be the OUT punch) and it would become a 5PM IN punch instead.
			// Then the next morning for their 8AM punch it thinks they are continuing their shift from yesterday, and the issue persists across multiple days.
			//
			// However we also need to handle cases where a schedule like this exists:
			//   Absent: 1a - 8a Working: 8a - 5pm and the employee punches at 7:45AM, it should be a IN punch assigned to the working schedule, not assigned to the absent shift.
			// 		This would exist if they had recurring schedules and swapped an employee from day shift to night shift (where they don't overlap)
			//      and have to make one of them an absent (with no absence policy) to get it off the schedule.
			// There is also the on-call case of:
			//      Working: 8a - 5p, Absent (oncall): 5p - 8a, and the employee getting called in around 11p or around 6a?
			//      We want to default branch/department/job/task to that of the oncall schedule not the working schedule if at all possible.
			if ( is_object( $s_obj ) ) {
				//Determine if we are closer to the schedule start time, or end time.
				if ( abs( $epoch - $s_obj->getEndTime() ) < abs( $epoch - $s_obj->getStartTime() ) && abs( $epoch - $s_obj->getEndTime() ) <= $s_obj->getStartStopWindow() ) {
					//Closer to end time.
					Debug::Text( '    Current punch is closer to schedule end time and within the schedule start/stop window. Schedule: Start: ' . TTDate::getDate( 'DATE+TIME', $s_obj->getStartTime() ) .' End: ' . TTDate::getDate( 'DATE+TIME', $s_obj->getEndTime() ) .' Window: '. $s_obj->getStartStopWindow(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $is_previous_punch == false ) {
						Debug::Text( '      NOTICE: No previous punch exists, defaulting to OUT.', __FILE__, __LINE__, __METHOD__, 10 );
						$next_status_id = 20; //Out
					} elseif ( isset( $prev_punch_obj ) && is_object( $prev_punch_obj ) && $prev_punch_obj->getTimeStamp() < ( $s_obj->getStartTime() - $s_obj->getStartStopWindow() ) && TTDate::getDayOfYear( $prev_punch_obj->getTimeStamp() ) != TTDate::getDayOfYear( $epoch ) ) {
						Debug::Text( '      NOTICE: Previous punch exists before the schedule start time - window, assuming missed IN punch and defaulting to Out.', __FILE__, __LINE__, __METHOD__, 10 );
						$next_status_id = 20; //Out
						$prev_punch_obj = false; //Clear previous punch object so none of its data carries over to the current punch.
					}
				} else {
					//Closer to start time.
					Debug::Text( '    Current punch is closer to schedule start time and within the schedule start/stop window. Schedule: Start: ' . TTDate::getDate( 'DATE+TIME', $s_obj->getStartTime() ) .' End: ' . TTDate::getDate( 'DATE+TIME', $s_obj->getEndTime() ) .' Window: '. $s_obj->getStartStopWindow(), __FILE__, __LINE__, __METHOD__, 10 );
					if ( $is_previous_punch == false ) {
						Debug::Text( '      NOTICE: No previous punch exists, defaulting to IN.', __FILE__, __LINE__, __METHOD__, 10 );
						$next_status_id = 10; //In
					} elseif ( isset( $prev_punch_obj ) && is_object( $prev_punch_obj ) && $prev_punch_obj->getTimeStamp() < ( $s_obj->getStartTime() - $s_obj->getStartStopWindow() ) && TTDate::getDayOfYear( $prev_punch_obj->getTimeStamp() ) != TTDate::getDayOfYear( $epoch ) ) {
						Debug::Text( '      NOTICE: Previous punch exists before the schedule start time - window, assuming missed OUT punch and defaulting to IN.', __FILE__, __LINE__, __METHOD__, 10 );
						$next_status_id = 10; //In
						$prev_punch_obj = false; //Clear previous punch object so none of its data carries over to the current punch.
					}
				}
			}

			//Get all GEO fences that this coordinates fall within.
			if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
				$gflf = TTnew( 'GEOFenceListFactory' ); /** @var GEOFenceListFactory $gflf */
				$geo_fence_default_object_ids = $gflf->getBranchAndDepartmentAndJobAndJobItemAndPunchTagArrayByCompanyIdAndGEOLocation( $user_obj->getCompany(), $latitude, $longitude, $position_accuracy );
			}

			if ( $branch_id == '' || empty( $branch_id ) || $branch_id == TTUUID::getZeroID() ) {
				if ( is_object( $station_obj ) && $station_obj->getDefaultBranch() !== false && $station_obj->getDefaultBranch() != TTUUID::getZeroID() ) {
					$branch_id = $station_obj->getDefaultBranch();
					//Debug::Text(' aOverriding branch to: '. $branch_id, __FILE__, __LINE__, __METHOD__, 10);
				} else if ( isset( $geo_fence_default_object_ids ) && isset( $geo_fence_default_object_ids['branch_id'] ) && isset( $geo_fence_default_object_ids['branch_id'][0] ) && count( $geo_fence_default_object_ids['branch_id'] ) == 1 && $this->isAllowedBranch( $user_obj->getId(), $geo_fence_default_object_ids['branch_id'][0] ) ) { //If more than one record matches GEO coordinates, ignore it since we will never know which one to choose.
					$branch_id = $geo_fence_default_object_ids['branch_id'][0];
					//Debug::Text(' bOverriding branch to: '. $branch_id, __FILE__, __LINE__, __METHOD__, 10);
				} else if ( is_object( $s_obj ) && $s_obj->getBranch() != TTUUID::getZeroID() ) {
					$branch_id = $s_obj->getBranch();
					//Debug::Text(' cOverriding branch to: '. $branch_id, __FILE__, __LINE__, __METHOD__, 10);
				} else if ( $user_obj->getDefaultBranch() != TTUUID::getZeroID() ) {
					$branch_id = $user_obj->getDefaultBranch();
					//Debug::Text(' dOverriding branch to: '. $branch_id, __FILE__, __LINE__, __METHOD__, 10);
				}
				Debug::Text( ' Overriding branch to: ' . $branch_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( $department_id == '' || empty( $department_id ) || $department_id == TTUUID::getZeroID() ) {
				if ( is_object( $station_obj ) && $station_obj->getDefaultDepartment() !== false && $station_obj->getDefaultDepartment() != TTUUID::getZeroID() ) {
					$department_id = $station_obj->getDefaultDepartment();
				} else if ( isset( $geo_fence_default_object_ids ) && isset( $geo_fence_default_object_ids['department_id'] ) && isset( $geo_fence_default_object_ids['department_id'][0] ) && count( $geo_fence_default_object_ids['department_id'] ) == 1 && $this->isAllowedDepartment( $user_obj->getId(), $branch_id, $geo_fence_default_object_ids['department_id'][0] ) ) {
					$department_id = $geo_fence_default_object_ids['department_id'][0];
				} else if ( is_object( $s_obj ) && $s_obj->getDepartment() != TTUUID::getZeroID() ) {
					$department_id = $s_obj->getDepartment();
				} else if ( $user_obj->getDefaultDepartment() != TTUUID::getZeroID() ) {
					$department_id = $user_obj->getDefaultDepartment();
				}
				Debug::Text( ' Overriding department to: ' . $department_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( $job_id == '' || empty( $job_id ) || $job_id == TTUUID::getZeroID() ) {
				if ( is_object( $station_obj ) && $station_obj->getDefaultJob() !== false && $station_obj->getDefaultJob() != TTUUID::getZeroID() ) {
					$job_id = $station_obj->getDefaultJob();
				} else if ( isset( $geo_fence_default_object_ids ) && isset( $geo_fence_default_object_ids['job_id'] ) && isset( $geo_fence_default_object_ids['job_id'][0] ) && count( $geo_fence_default_object_ids['job_id'] ) == 1 && $this->isAllowedJob( $user_obj->getId(), $branch_id, $department_id, $geo_fence_default_object_ids['job_id'][0] ) ) {
					$job_id = $geo_fence_default_object_ids['job_id'][0];
				} else if ( is_object( $s_obj ) && $s_obj->getJob() != TTUUID::getZeroID() ) {
					$job_id = $s_obj->getJob();
				} else if ( $user_obj->getDefaultJob() != TTUUID::getZeroID() ) {
					$job_id = $user_obj->getDefaultJob();
				}
				Debug::Text( ' Overriding job to: ' . $job_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( $job_item_id == '' || empty( $job_item_id ) || $job_item_id == TTUUID::getZeroID() ) {
				if ( is_object( $station_obj ) && $station_obj->getDefaultJobItem() !== false && $station_obj->getDefaultJobItem() != TTUUID::getZeroID() ) {
					$job_item_id = $station_obj->getDefaultJobItem();
				} else if ( isset( $geo_fence_default_object_ids ) && isset( $geo_fence_default_object_ids['job_item_id'] ) && isset( $geo_fence_default_object_ids['job_item_id'][0] ) && count( $geo_fence_default_object_ids['job_item_id'] ) == 1 && $this->isAllowedJobItem( $user_obj->getId(), $job_id, $geo_fence_default_object_ids['job_item_id'][0] ) ) {
					$job_item_id = $geo_fence_default_object_ids['job_item_id'][0];
				} else if ( is_object( $s_obj ) && $s_obj->getJobItem() != TTUUID::getZeroID() ) {
					$job_item_id = $s_obj->getJobItem();
				} else if ( $user_obj->getDefaultJobItem() != TTUUID::getZeroID() ) {
					$job_item_id = $user_obj->getDefaultJobItem();
				}
				Debug::Text( ' Overriding task to: ' . $job_item_id, __FILE__, __LINE__, __METHOD__, 10 );
			}

			if ( $punch_tag_id == '' || empty( $punch_tag_id ) || $punch_tag_id == TTUUID::getZeroID() ) {
				if ( is_object( $station_obj ) && empty( $station_obj->getDefaultPunchTag() ) != true && $station_obj->getDefaultPunchTag() !== false && $station_obj->getDefaultPunchTag() != TTUUID::getZeroID() ) {
					$punch_tag_id = $station_obj->getDefaultPunchTag();
				} else if ( isset( $geo_fence_default_object_ids ) && isset( $geo_fence_default_object_ids['punch_tag_id'] ) && isset( $geo_fence_default_object_ids['punch_tag_id'] ) && is_array( $geo_fence_default_object_ids['punch_tag_id'] ) && is_array( $allowed_punch_tags = $this->isAllowedPunchTag( $user_obj->getId(), $branch_id, $department_id, $job_id, $job_item_id, $geo_fence_default_object_ids['punch_tag_id'] ) ) ) {
					$punch_tag_id = $allowed_punch_tags; //Allowed punch tags is defined in the IF statement above, to avoid running isAllowedPunchTag() twice, as its fairly resource intensive.
				} else if ( is_object( $s_obj ) && empty( $s_obj->getPunchTag() ) !== true && $s_obj->getPunchTag() != TTUUID::getZeroID() ) {
					$punch_tag_id = $s_obj->getPunchTag();
				} else if ( empty( $user_obj->getDefaultPunchTag() ) !== true && $user_obj->getDefaultPunchTag() !== TTUUID::getZeroID() ) {
					$punch_tag_id = $user_obj->getDefaultPunchTag();
				}
				Debug::Arr( $punch_tag_id, ' Overriding punch tag to: ', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		if ( $is_previous_punch == true && is_object( $prev_punch_obj ) ) {
			//Don't enable transfer by default if the previous punch was any OUT punch.
			//Transfer does the OUT punch for them, so if the previous punch is an OUT punch
			//we don't gain anything anyways.
			$default_transfer_on = ( is_object( $permission_obj ) ) ? $permission_obj->Check( 'punch', 'default_transfer', $user_obj->getId(), $user_obj->getCompany() ) : false; //User/Company need to be passed into here otherwise its always false if called from APIClientStationUnAuthenticated

			//Check for "Disable: Default Transfer On" station flag and set default_transfer_on = FALSE if it is set.
			if ( $default_transfer_on == true ) {
				$mode_flags = $station_obj->getModeFlag();
				if ( is_array( $mode_flags ) && in_array( 16384, $mode_flags ) ) {
					$default_transfer_on = false;
					Debug::Text( ' Turning off Transfer due to Station Flag: Disable: Default Transfer On ...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			if ( $default_transfer_on == true && ( isset( $prev_punch_obj ) && is_object( $prev_punch_obj ) && $prev_punch_obj->getStatus() == 10 ) ) {
				//Check to see if the employee is scheduled, if they are past their scheduled out time, then don't default to transfer.
				//If they are not scheduled default to transfer though.
				if ( !isset( $s_obj ) ) {
					$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
					$s_obj = $slf->getScheduleObjectByUserIdAndEpoch( $user_obj->getId(), $epoch );
				}
				if ( !is_object( $s_obj ) || ( is_object( $s_obj ) && $epoch < $s_obj->getEndTime() ) ) {
					$transfer = true;
				}
			} else {
				Debug::Text( ' Not setting Transfer: On...', __FILE__, __LINE__, __METHOD__, 10 );
			}

			$next_type = (int)$prev_punch_obj->getNextType( $epoch ); //Detects breaks/lunches too.

			if ( $prev_punch_obj->getNextStatus() == 10 ) {
				//In punch - Carry over just certain data
				$data = [
						'user_id'          => $user_obj->getId(),
						//'user_full_name' => $user_obj->getFullName(),
						//'time_stamp' => $epoch,
						//'date_stamp' => $epoch,
						'transfer'         => $transfer,
						'branch_id'        => TTUUID::castUUID( $branch_id ),
						'department_id'    => TTUUID::castUUID( $department_id ),
						'job_id'           => TTUUID::castUUID( $job_id ),
						'job_item_id'      => TTUUID::castUUID( $job_item_id ),
						'punch_tag_id'     => $punch_tag_id,
						'quantity'         => 0,
						'bad_quantity'     => 0,
						'status_id'        => (int)$prev_punch_obj->getNextStatus(),
						'type_id'          => (int)$next_type,
						'punch_control_id' => $prev_punch_obj->getNextPunchControlID(),
						'note'             => '', //Must be null.
				];
				$pcf = TTNew('PunchControlFactory'); /** @var PunchControlFactory $pcf */
				$data = $pcf->getCustomFieldsDefaultData( $user_obj->getCompany(), $data, true );
			} else {
				//Out punch
				$data = [
						'user_id'          => $user_obj->getId(),
						//'user_full_name' => $user_obj->getFullName(),
						//'time_stamp' => $epoch,
						//'date_stamp' => $epoch,
						'transfer'         => $transfer,
						'branch_id'        => TTUUID::castUUID( $branch_id ),
						'department_id'    => TTUUID::castUUID( $department_id ),
						'job_id'           => TTUUID::castUUID( $job_id ),
						'job_item_id'      => TTUUID::castUUID( $job_item_id ),
						'punch_tag_id'     => $punch_tag_id,
						'quantity'         => (float)$prev_punch_obj->getPunchControlObject()->getQuantity(),
						'bad_quantity'     => (float)$prev_punch_obj->getPunchControlObject()->getBadQuantity(),
						'status_id'        => (int)$prev_punch_obj->getNextStatus(),
						'type_id'          => (int)$next_type,
						'punch_control_id' => $prev_punch_obj->getNextPunchControlID(),
						'note'             => (string)$prev_punch_obj->getPunchControlObject()->getNote(), //Must be null.
				];

				$data = $prev_punch_obj->getPunchControlObject()->getCustomFields( $user_obj->getCompany(), $data );
			}
		} else {
			$data = [
					'user_id'       => $user_obj->getId(),
					//'user_full_name' => $user_obj->getFullName(),
					//'time_stamp' => $epoch,
					//'date_stamp' => $epoch,
					'transfer'      => false,
					'branch_id'     => TTUUID::castUUID( $branch_id ),
					'department_id' => TTUUID::castUUID( $department_id ),
					'job_id'        => TTUUID::castUUID( $job_id ),
					'job_item_id'   => TTUUID::castUUID( $job_item_id ),
					'punch_tag_id'  => $punch_tag_id,
					'quantity'      => 0,
					'bad_quantity'  => 0,
					'status_id'     => ( ( isset($next_status_id) && $next_status_id != '' ) ? $next_status_id : 10 ), //In
					'type_id'       => 10, //Normal
					'note'          => '', //Must be null.
			];

			$pcf = TTNew('PunchControlFactory'); /** @var PunchControlFactory $pcf */
			$data = $pcf->getCustomFieldsDefaultData( $user_obj->getCompany(), $data, true );
		}

		Debug::Arr( $data, ' Default Punch Settings: ', __FILE__, __LINE__, __METHOD__, 10 );

		return $data;
	}

	/**
	 * Determine if the punch was manually created (without punching in/out) or modified by someone other than the person who punched in/out.
	 * Allow for employees manually entering in their own punches (and editing) without that being marked as tainted.
	 *
	 * @return bool
	 */
	function getTainted() {
		if ( $this->getColumn( 'tainted' ) !== false ) {
			return (bool)$this->getColumn( 'tainted' );
		}

		return false;
	}


	/**
	 * @return bool
	 */
	function getHasImage() {
		return $this->fromBool( $this->getGenericDataValue( 'has_image' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHasImage( $value ) {
		return $this->setGenericDataValue( 'has_image', $this->toBool( $value ) );
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param string $punch_id   UUID
	 * @return bool
	 */
	function isImageExists( $company_id = null, $user_id = null, $punch_id = null, $file_name = null ) {
		if ( empty( $file_name ) ) {
			$file_name = $this->getImageFileName( $company_id, $user_id, $punch_id );
		}

		if ( $this->getHasImage() && file_exists( $file_name ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param string $punch_id   UUID
	 * @return bool|int
	 */
	function saveImage( $company_id = null, $user_id = null, $punch_id = null ) {
		$image_data = $this->getImage();
		if ( $image_data != '' ) {
			$file_name = $this->getImageFileName( $company_id, $user_id, $punch_id );
			if ( $file_name != '' ) {
				//Use retry loop because sometimes transient file system errors may be encountered.
				$retry_function = function () use ( $image_data, $file_name ) {
					$dir = dirname( $file_name );

					if ( file_exists( $dir ) == false ) {
						$mkdir_result = @mkdir( $dir, 0700, true );
						if ( $mkdir_result == false ) {
							Debug::Text( 'ERROR: Unable to create storage file directory: ' . $dir .' Message: '. Debug::getLastPHPErrorMessage(), __FILE__, __LINE__, __METHOD__, 10 );
							throw new Exception( 'ERROR: Unable to create storage file directory: ' . $dir .' Message: '. Debug::getLastPHPErrorMessage() );
						}
					}

					Debug::Text( 'Saving Image File Name: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
					$file_put_contents_result = file_put_contents( $file_name, $image_data, LOCK_EX );
					if ( $file_put_contents_result == true ) {
						return true;
					} else {
						throw new Exception( 'ERROR: Unable to save data to file: ' . $file_name );
					}
				};

				return Misc::Retry( $retry_function, 3, 0.25 ); //If needed, could potentially continue on error here and the punch would get saved without an image.
			}
		} else {
			//Debug::Arr( $image_data, 'NOT Saving Image File Name: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
			Debug::Text( '  No Punch Image recevied...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param string $punch_id   UUID
	 * @return bool|string
	 */
	function getImageFileName( $company_id = null, $user_id = null, $punch_id = null ) {
		if ( $company_id == '' && is_object( $this->getUserObject() ) ) {
			$company_id = $this->getUserObject()->getCompany();
		}

		if ( $user_id == '' && $this->getUser() != '' ) {
			$user_id = $this->getUser();
		}

		if ( $punch_id == '' ) {
			$punch_id = $this->getID();
		}

		if ( $company_id == '' || TTUUID::isUUID( $company_id ) == false ) {
			Debug::Text( 'No Company... Company ID: ' . $company_id . ' User ID: ' . $user_id . ' Punch ID: ' . $punch_id, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $user_id == '' || TTUUID::isUUID( $user_id ) == false ) {
			Debug::Text( 'No User... Company ID: ' . $company_id . ' User ID: ' . $user_id . ' Punch ID: ' . $punch_id, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $punch_id == '' || TTUUID::isUUID( $punch_id ) == false ) {
			Debug::Text( 'No Punch... Company ID: ' . $company_id . ' User ID: ' . $user_id . ' Punch ID: ' . $punch_id, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$hash_dir = [];
		$hash = crc32( $company_id . $user_id . $punch_id );
		$hash_dir[0] = substr( $hash, 0, 2 );
		$hash_dir[1] = substr( $hash, 2, 2 );
		$hash_dir[2] = substr( $hash, 4, 2 );

		$base_name = Environment::getStorageBasePath() . DIRECTORY_SEPARATOR . 'punch_images' . DIRECTORY_SEPARATOR . $company_id . DIRECTORY_SEPARATOR . $user_id . DIRECTORY_SEPARATOR . $hash_dir[0] . DIRECTORY_SEPARATOR . $hash_dir[1] . DIRECTORY_SEPARATOR . $hash_dir[2] . DIRECTORY_SEPARATOR;

		$punch_image_file_name = $base_name . $punch_id . '.jpg'; //Should be JPEG 75% quality, about 10K in size.
		//Debug::Text( 'Punch Image File Name: ' . $punch_image_file_name . ' Company ID: ' . $company_id . ' User ID: ' . $user_id . ' Punch ID: ' . $punch_id . ' CRC32: ' . $hash, __FILE__, __LINE__, __METHOD__, 10 );

		return $punch_image_file_name;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param string $punch_id   UUID
	 * @return bool
	 */
	function cleanStoragePath( $company_id = null, $user_id = null, $punch_id = null ) {
		$file_name = $this->getImageFileName( $company_id, $user_id, $punch_id );
		if ( $file_name != '' && $this->isImageExists( $company_id, $user_id, $punch_id, $file_name ) ) {
			Debug::Text( 'Deleting Image... File Name: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
			@unlink( $file_name );
			Misc::deleteEmptyParentDirectory( dirname( $file_name ), 4 ); //Recurse to $user_id parent level and remove empty directories.
		}

		return true;
	}

	/**
	 * @param string $company_id UUID
	 * @param string $user_id    UUID
	 * @param string $punch_id   UUID
	 * @return bool|string
	 */
	function getImage( $company_id = null, $user_id = null, $punch_id = null ) {
		$value = $this->getGenericTempDataValue( 'image' );
		if ( $value !== false && $value != '' ) {
			return $value;
		} else if ( !isset( $this->is_new ) || $this->is_new == false ) { //Don't bother checking if image exists on the file system when its a new punch.
			$file_name = $this->getImageFileName( $company_id, $user_id, $punch_id );
			if ( $this->isImageExists() ) {
				return file_get_contents( $file_name );
			}
		}

		return false;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setImage( $data ) {
		if ( $data != '' ) {
			$this->setGenericTempDataValue( 'image', $data );
			$this->setHasImage( true );

			return true;
		}

		Debug::Text( 'Not setting Image data...', __FILE__, __LINE__, __METHOD__, 10 );

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
		// Punch Control ID
		$this->Validator->isUUID( 'punch_control',
								  $this->getPunchControlID(),
								  TTi18n::gettext( 'Invalid Punch Control ID' )
		);
		// Status
		$this->Validator->inArrayKey( 'status_id',
									  $this->getStatus(),
									  TTi18n::gettext( 'Incorrect Status' ),
									  $this->getOptions( 'status' )
		);
		// Type
		$this->Validator->inArrayKey( 'type_id',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);
		// Time stamp
		$this->Validator->isDate( 'punch_time',
								  $this->getTimeStamp(),
								  TTi18n::gettext( 'Incorrect time stamp' )
		);
		// Original time stamp
		if ( $this->getOriginalTimeStamp() !== false ) {
			$this->Validator->isDate( 'original_time_stamp',
									  $this->getOriginalTimeStamp(),
									  TTi18n::gettext( 'Incorrect original time stamp' )
			);
		}
		// Actual time stamp
		if ( $this->getActualTimeStamp() !== false ) {
			$this->Validator->isDate( 'actual_time_stamp',
									  $this->getActualTimeStamp(),
									  TTi18n::gettext( 'Incorrect actual time stamp' )
			);
		}
		// Longitude
		if ( $this->getLongitude() != '' ) {
			$this->Validator->isFloat( 'longitude',
									   $this->getLongitude(),
									   TTi18n::gettext( 'Longitude is invalid' )
			);
		}
		// Latitude
		if ( $this->getLatitude() != '' ) {
			$this->Validator->isFloat( 'latitude',
									   $this->getLatitude(),
									   TTi18n::gettext( 'Latitude is invalid' )
			);
		}
		// Position Accuracy
		if ( $this->getPositionAccuracy() != '' ) {
			$this->Validator->isNumeric( 'position_accuracy',
										 (int)$this->getPositionAccuracy(),
										 TTi18n::gettext( 'Position Accuracy is invalid' )
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getStatus() == false ) {
			$this->Validator->isTRUE( 'status_id',
									  false,
									  TTi18n::getText( 'Status not specified' ) );
		}
		if ( $this->getType() == false ) {
			$this->Validator->isTRUE( 'type_id',
									  false,
									  TTi18n::getText( 'Type not specified' ) );
		}

		if ( $this->Validator->hasError( 'time_stamp' ) == false && $this->getTimeStamp() == false ) {
			$this->Validator->isTRUE( 'time_stamp',
									  false,
									  TTi18n::getText( 'Time stamp not specified' ) );
		}

		if ( $this->Validator->hasError( 'punch_control' ) == false
				&& $this->getPunchControlID() == false ) {
			$this->Validator->isTRUE( 'punch_control',
									  false,
									  TTi18n::getText( 'Invalid Punch Control ID' ) );
		}

		if ( is_object( $this->getPunchControlObject() )
				&& is_object( $this->getPunchControlObject()->getPayPeriodObject() )
				&& $this->getPunchControlObject()->getPayPeriodObject()->getIsLocked() == true ) {
			$this->Validator->isTRUE( 'pay_period',
									  false,
									  TTi18n::getText( 'Pay Period is Currently Locked' ) );
		}

		//Make sure two punches with the same status are not in the same punch pair.
		//This has to be done here rather than PunchControlFactory because of the unique index and punches are saved before the PunchControl record.
		if ( is_object( $this->getPunchControlObject() ) ) {
			$plf = $this->getPunchControlObject()->getPLFByPunchControlID();
			if ( $plf->getRecordCount() > 0 ) {
				foreach ( $plf as $p_obj ) {
					if ( $p_obj->getId() !== $this->getID() ) {
						if ( $p_obj->getStatus() == $this->getStatus() ) {
							if ( $p_obj->getStatus() == 10 ) {
								$this->Validator->isTRUE( 'time_stamp',
														  false,
														  TTi18n::gettext( 'In punches cannot occur twice in the same punch pair, you may want to make this an out punch instead' ) );
							} else {
								$this->Validator->isTRUE( 'time_stamp',
														  false,
														  TTi18n::gettext( 'Out punches cannot occur twice in the same punch pair, you may want to make this an in punch instead' ) );
							}
						}
					}
				}
			}
			unset( $plf, $p_obj );
		}
																																																																						/* @formatter:off */ if ( $this->Validator->isValid() == TRUE && $this->isNew() == TRUE ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'lic_obj', FALSE, $obj->{$obj_error_msg_function}($retval) ); } } /* @formatter:on */
		return TRUE;
	}

	/**
	 * Saves the punch object when splitting punches at midnight.
	 * @param $epoch int EPOCH
	 * @param $status_id int
	 * @param $punch_control_id string UUID
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 * @throws ReflectionException
	 */
	function saveSplitAtMidnightPunch( $epoch, $status_id, $punch_control_id ) {
		//Since we need to change the PunchControlID, copy the current punch_control object to work around getGenericObject() checking the
		//IDs and not returning the object anymore.
		$tmp_punch_control_obj = $this->getPunchControlObject();

		//
		//Punch out just before midnight
		//The issue with this is that if rounding is enabled this will ignore it, and the shift for this day may total: 3.98hrs.
		//when they want it to total 4.00hrs. Why don't we split shifts at exactly midnight with no gap at all?
		//Split shifts right at midnight causes additional issues when editing those punches, TimeTrex will want to combine them on the same day again.
		$pf = TTnew( 'PunchFactory' ); /** @var PunchFactory $pf */
		$pf->setUser( $this->getUser() );
		$pf->setEnableSplitAtMidnight( false );
		$pf->setTransfer( false );
		$pf->setEnableAutoTransfer( false );

		$pf->setType( 10 ); //Normal
		$pf->setStatus( $status_id );

		//We used to have to make this punch 60seconds before midnight, but getShiftData() was modified to support punch at exactly midnight.
		$midnight_timestamp = ( TTDate::getBeginDayEpoch( $epoch ) );
		$pf->setTimeStamp( $midnight_timestamp, false ); //Disable rounding.
		$pf->setActualTimeStamp( $midnight_timestamp );
		//$pf->setOriginalTimeStamp( $midnight_timestamp ); //set in preSave()

		Debug::text( ' Split Punch: Punching in/out at midnight: '. TTDate::getDate('DATE+TIME', $midnight_timestamp ), __FILE__, __LINE__, __METHOD__, 10 );

		$pf->setPunchControlID( $punch_control_id );
		if ( $pf->isValid() ) {
			if ( $pf->Save( false ) == true ) {
				$pcf = TTnew( 'PunchControlFactory' ); /** @var PunchControlFactory $pcf */
				$pcf->setId( $pf->getPunchControlID() );
				$pcf->setPunchObject( $pf );

				$pcf->setBranch( $tmp_punch_control_obj->getBranch() );
				$pcf->setDepartment( $tmp_punch_control_obj->getDepartment() );
				$pcf->setJob( $tmp_punch_control_obj->getJob() );
				$pcf->setJobItem( $tmp_punch_control_obj->getJobItem() );
				$pcf->setPunchTag( $tmp_punch_control_obj->getPunchTag() );
				$pcf->setCustomFields( $tmp_punch_control_obj->getCustomFields( $tmp_punch_control_obj->getUserObject()->getCompany(), [] ) );
				$pcf->setQuantity( $tmp_punch_control_obj->getQuantity() );
				$pcf->setBadQuantity( $tmp_punch_control_obj->getBadQuantity() );
				$pcf->setNote( $tmp_punch_control_obj->getNote() );

				$pcf->setEnableStrictJobValidation( true );
				$pcf->setEnableCalcUserDateID( true );
				$pcf->setEnableCalcTotalTime( true );
				$pcf->setEnableCalcSystemTotalTime( true );
				$pcf->setEnableCalcWeeklySystemTotalTime( true );
				$pcf->setEnableCalcUserDateTotal( true );
				$pcf->setEnableCalcException( true );

				if ( $pcf->isValid() == true ) {
					$pcf->Save( false, true ); //Force isNEW() lookup.
				}
			}
		}
		unset( $pf, $pcf );

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		$this->is_new = false;
		if ( $this->isNew() == true ) {
			$this->is_new = true;
		}

		if ( $this->is_new ) {
			Debug::text( ' Setting Original TimeStamp: ' . $this->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10 );
			$this->setOriginalTimeStamp( $this->getTimeStamp() );
		}

		if ( $this->getDeleted() == false ) {

			if ( $this->is_new && $this->getEnableSplitPunchControl() == true ) {
				Debug::text( ' Split Punch Control to insert inbetween if needed...', __FILE__, __LINE__, __METHOD__, 10 );

				$plf = TTNew('PunchListFactory');
				$tmp_punch_control_id = $plf->getCompletePunchControlIdByUserIdAndEpoch( $this->getUser(), $this->getTimeStamp() );
				if ( TTUUID::isUUID( $tmp_punch_control_id ) ) {
					Debug::text( ' Found Punch Control ID to split: '. $tmp_punch_control_id, __FILE__, __LINE__, __METHOD__, 10 );
					$is_punch_control_split = PunchControlFactory::splitPunchControl( $tmp_punch_control_id );
					if ( $is_punch_control_split == true ) {
						$this->setPunchControlID( false ); //Clear PunchControlID so findPunchControlId() will actually execute.
						$this->setPunchControlID( $this->findPunchControlID() );
						Debug::text( '   Split Punch Control ID: '. $tmp_punch_control_id .' Result: '. (int)$is_punch_control_split, __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::text( '   No Punch Control to split...', __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $plf, $tmp_punch_control_id, $is_punch_control_split );
			}

			if ( $this->is_new && $this->getTransfer() == true && $this->getEnableAutoTransfer() == true ) {
				Debug::text( ' Transfer is Enabled, automatic punch out of last punch pair...', __FILE__, __LINE__, __METHOD__, 10 );

				//Use actual time stamp, not rounded timestamp. This should only be called on new punches as well, otherwise Actual Time Stamp could be incorrect.
				//$prev_punch_obj = $this->getPreviousPunchObject( $this->getActualTimeStamp() );
				$prev_punch_obj = $this->getPreviousPunchObject( $this->getActualTimeStamp(), $this->getUser(), true ); //Ignore future punches, so mass adding transfer punches at the end of the day can still work.
				if ( is_object( $prev_punch_obj ) ) {
					Debug::text( ' Found Last Punch: ID: '. $prev_punch_obj->getID() .' Timestamp: '. TTDate::getDate('DATE+TIME', $prev_punch_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

					if ( $prev_punch_obj->getStatus() == 10 ) {
						Debug::text( ' Last Punch was in. Auto Punch Out now: ', __FILE__, __LINE__, __METHOD__, 10 );
						//Make sure the current punch status is IN
						$this->setStatus( 10 ); //In
						$this->setType( 10 ); //Normal (can't transfer in/out of lunches?)

						$pf = TTnew( 'PunchFactory' ); /** @var PunchFactory $pf */
						$pf->setUser( $this->getUser() );
						$pf->setEnableAutoTransfer( false );
						$pf->setPunchControlID( $prev_punch_obj->getPunchControlID() );
						$pf->setTransfer( true );
						$pf->setType( $prev_punch_obj->getNextType() );
						$pf->setStatus( 20 ); //Out
						$pf->setTimeStamp( $this->getTimeStamp() ); //Use the exact timestamp from the previous punch so they always match now that transfer punches can round.
						$pf->setActualTimeStamp( $this->getTimeStamp() );
						//$pf->setOriginalTimeStamp( $this->getTimeStamp() ); //set in preSave()
						$pf->setLongitude( $this->getLongitude() );
						$pf->setLatitude( $this->getLatitude() );
						$pf->setPositionAccuracy( $this->getPositionAccuracy() );
						if ( $pf->isValid() ) {
							if ( $pf->Save( false ) == true ) {
								$prev_punch_obj->getPunchControlObject()->setPunchObject( $pf );
								$prev_punch_obj->getPunchControlObject()->setEnableCalcTotalTime( true );
								$prev_punch_obj->getPunchControlObject()->setEnableCalcSystemTotalTime( true );
								$prev_punch_obj->getPunchControlObject()->setEnableCalcUserDateTotal( true );
								$prev_punch_obj->getPunchControlObject()->setEnableCalcException( true );
								$prev_punch_obj->getPunchControlObject()->setEnablePreMatureException( true );
								if ( $prev_punch_obj->getPunchControlObject()->isValid() ) {
									$prev_punch_obj->getPunchControlObject()->Save();
								} else {
									Debug::text( ' aError saving auto out punch...', __FILE__, __LINE__, __METHOD__, 10 );
								}
							} else {
								Debug::text( ' bError saving auto out punch...', __FILE__, __LINE__, __METHOD__, 10 );
							}
						} else {
							Debug::text( ' cError saving auto out punch...', __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						Debug::text( ' Last Punch was out. No Auto Punch out needed, removing transfer flag from this punch...', __FILE__, __LINE__, __METHOD__, 10 );
						$this->setTransfer( false );
					}
				}
				unset( $prev_punch_obj, $pf );
			}

			//Split punch at midnight.
			//This has to be an Out punch, and the previous punch has to be an in punch in order for the split to occur.
			//Check to make sure there is an open punch pair.
			//Make sure this punch isn't right at midnight either, as no point in splitting a punch at that time.
			//FIXME: What happens if a supervisor edits a 11:30PM punch and makes it 5:00AM the next day?
			//		We can't split punches when editing existing punches, because we have to split punch_control_ids prior to saving etc...
			//		But we can split when supervisors are adding new punches.
			//Debug::text('Split at Midnight Enabled: '. $this->getEnableSplitAtMidnight() .' IsNew: '. $this->is_new .' Status: '. $this->getStatus() .' TimeStamp: '. $this->getTimeStamp() .' Punch Control ID: '. $this->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
			if ( $this->is_new == true
					&& $this->getStatus() == 20
					&& $this->getEnableSplitAtMidnight() == true
					&& $this->getTimeStamp() != TTDate::getBeginDayEpoch( $this->getTimeStamp() )
					&& ( is_object( $this->getPunchControlObject() )
							&& is_object( $this->getPunchControlObject()->getPayPeriodScheduleObject() )
							&& $this->getPunchControlObject()->getPayPeriodScheduleObject()->getShiftAssignedDay() == 40 ) ) {

				$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
				$plf->getPreviousPunchByUserIdAndEpoch( $this->getUser(), $this->getTimeStamp() );
				if ( $plf->getRecordCount() > 0 ) {
					$prev_punch_obj = $plf->getCurrent();
					Debug::text( ' Found Last Punch... ID: ' . $prev_punch_obj->getId() . ' Timestamp: ' . $prev_punch_obj->getTimeStamp(), __FILE__, __LINE__, __METHOD__, 10 );

					if ( $prev_punch_obj->getStatus() == 10 && TTDate::doesRangeSpanMidnight( $this->getTimeStamp(), $prev_punch_obj->getTimeStamp() ) ) {
						Debug::text( ' Last Punch was in and this is an out punch that spans midnight. Split Punch at midnight now: ', __FILE__, __LINE__, __METHOD__, 10 );

						//Make sure the current punch status is OUT
						//But we can split LUNCH/Break punches, because someone could punch in at 8PM, then out for lunch at 1:00AM, this would need to be split.
						$this->setStatus( 20 ); //Out

						//Reduce the out punch by 60 seconds, and increase the current punch by 60seconds so no time is lost.
						$this->setTimeStamp( $this->getTimeStamp() ); //FIXME: May need to use ActualTimeStamp here so we aren't double rounding.

						$split_range_at_midnight = TTDate::splitDateRangeAtMidnight( $prev_punch_obj->getTimeStamp(), $this->getTimeStamp() );
						if ( is_array( $split_range_at_midnight ) )  {

							$i = 0;
							$max = ( count( $split_range_at_midnight ) - 1 );
							foreach( $split_range_at_midnight as $split_range_arr ) {
								Debug::Text( '  Split Range: Start: '. TTDate::getDate('DATE+TIME', $split_range_arr['start_time_stamp'] ) .' End: '. TTDate::getDate('DATE+TIME', $split_range_arr['end_time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10 );

								if ( $i == 0 ) {
									Debug::text( '    Split Punch: Punching out just before midnight after previous punch...', __FILE__, __LINE__, __METHOD__, 10 );
									$this->saveSplitAtMidnightPunch( $split_range_arr['end_time_stamp'], 20, $prev_punch_obj->getPunchControlID() ); //Out
								}

								if ( $i > 0 && $i < $max ) {
									$new_punch_control_id = $this->getPunchControlObject()->getNextInsertId();

									Debug::text( '    Split Punch: Punching in just before midnight on a middle day...', __FILE__, __LINE__, __METHOD__, 10 );
									$this->saveSplitAtMidnightPunch( $split_range_arr['start_time_stamp'], 10, $new_punch_control_id ); //In

									Debug::text( '    Split Punch: Punching out just before midnight on a middle day...', __FILE__, __LINE__, __METHOD__, 10 );
									$this->saveSplitAtMidnightPunch( $split_range_arr['end_time_stamp'], 20, $new_punch_control_id ); //Out
									unset( $new_punch_control_id );
								}

								if ( $i == $max ) {
									$new_punch_control_id = $this->getPunchControlObject()->getNextInsertId();

									Debug::text( '    Split Punch: Punching in just at midnight on last day...', __FILE__, __LINE__, __METHOD__, 10 );
									$this->saveSplitAtMidnightPunch( $split_range_arr['end_time_stamp'], 10, $new_punch_control_id ); //In

									$this->setPunchControlID( $new_punch_control_id );
									unset( $new_punch_control_id );
								}

								$i++;
							}
						}
						unset( $split_range_at_midnight, $split_range_arr );
					} else {
						Debug::text( ' Last Punch was out. No Auto Punch ', __FILE__, __LINE__, __METHOD__, 10 );
					}
				}
				unset( $plf, $prev_punch_obj );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {

		if ( $this->getDeleted() == true ) {
			$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
			$plf->getByPunchControlId( $this->getPunchControlID() );
			if ( $plf->getRecordCount() === 0 ) { //=== is needed here to ensure its not FALSE.
				//Check to see if any other punches are assigned to this punch_control_id
				Debug::text( ' Deleted Last Punch for Punch Control Object: ' . $this->getPunchControlObject()->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->getPunchControlObject()->setDeleted( true );
			}

			//Make sure we recalculate system time.
			$this->getPunchControlObject()->setPunchObject( $this );
			$this->getPunchControlObject()->setEnableCalcUserDateID( true );
			$this->getPunchControlObject()->setEnableCalcSystemTotalTime( $this->getEnableCalcSystemTotalTime() );
			$this->getPunchControlObject()->setEnableCalcWeeklySystemTotalTime( $this->getEnableCalcWeeklySystemTotalTime() );
			$this->getPunchControlObject()->setEnableCalcException( $this->getEnableCalcException() );
			$this->getPunchControlObject()->setEnablePreMatureException( $this->getEnablePreMatureException() );
			$this->getPunchControlObject()->setEnableCalcUserDateTotal( $this->getEnableCalcUserDateTotal() );
			$this->getPunchControlObject()->setEnableCalcTotalTime( $this->getEnableCalcTotalTime() );
			if ( $this->getPunchControlObject()->isValid() ) {
				//Saving the punch control object clears it, so even if the punch was Save(FALSE) the punch control object will be cleared and not accessible.
				//This can affect things like drag and drop.
				$this->getPunchControlObject()->Save();
				Debug::text( ' Punch Control Object Saved...', __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				//Something went wrong, rollback the entire transaction.
				$this->FailTransaction();
			}

			if ( $this->getHasImage() == true ) {
				$this->cleanStoragePath();
			}
		} else {
			$this->saveImage();

			$this->handleReminderNotifications();
			$this->handleFutureTimeSheetRecalculationForExceptions();
		}

		return true;
	}

	function handleFutureTimeSheetRecalculationForExceptions() {
		global $config_vars;
		if ( isset( $config_vars['other']['enable_job_queue'] ) && $config_vars['other']['enable_job_queue'] != true ) {
			return false;
		}

		if ( $this->is_new != true ) {
			Debug::text( '  Not a new punch, ignore reminder notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		if ( $this->getEnablePreMatureException() == false ) {
			Debug::text( '  User is not punching themselves, ignore reminder notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		//Make sure the current punch isn't in the past, or too far in the future.
		if ( $this->getTimeStamp() > ( TTDate::getTime() + 7200 ) || $this->getTimeStamp() < ( TTDate::getTime() - 7200 ) ) {
			Debug::text( '  Punch does not appear to be real-time, ignore reminder notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		$this->setScheduleID( $this->findScheduleID( $this->getTimeStamp() ) );

		$delay_after_trigger_time = 1; //1 second.

		$start_date = $this->getTimeStamp();
		$end_date = $start_date;

		$eplf = TTNew('ExceptionPolicyListFactory');
		$eplf->getByPolicyGroupUserIdAndTypeAndActive( $this->getUser(), [ 'C1', 'L4' ], true );
		if ( $eplf->getRecordCount() > 0 ) {
			switch( $this->getStatus() ) {
				case 10: //In
					//Handle 'C1' on every IN punch, so when punching back in from Lunch/Break it triggers within the check-in grace time.
					foreach( $eplf as $ep_obj ) { /** @var ExceptionFactory $ep_obj */
						switch ( $ep_obj->getType() ) {
							case 'C1':
								$effective_date = ( $this->getTimeStamp() + $ep_obj->getGrace() + $delay_after_trigger_time );
								Debug::text( '   Exception Type: '. $ep_obj->getType() .' Effective Date: '. TTDate::getDate('DATE+TIME', $effective_date ) .' Punch Time: '. TTDate::getDate('DATE+TIME', $this->getTimeStamp() ) .' Grace: '. $ep_obj->getGrace(), __FILE__, __LINE__, __METHOD__, 10 );
								SystemJobQueue::Add( TTi18n::getText( 'ReCalculate Quick Exceptions' ), $this->getId(), 'CalculatePolicy', 'reCalculateForJobQueue', [ $this->getUser(), 'calcQuickExceptions', $start_date, $end_date ], 10, null, $effective_date, $this->getUser() );
								break;
							case 'L4':
								if ( $this->getType() == 10 && $this->getTransfer() == false ) { //Only schedule recalculation for Normal In punches that are *not* transfer punches.
									$meal_policy_arr = $this->getMealPolicies();
									$mplf = $meal_policy_arr['lf'];
									if ( $mplf->getRecordCount() > 0 ) {
										$mplf->rewind(); //Other code above loops through meal policies, so we always want to rewind to the start and get the first one.
										$mp_obj = $mplf->getCurrent();

										$effective_date = ( $this->getTimeStamp() + $ep_obj->getGrace() + $mp_obj->getTriggerTime() );
										Debug::text( '   Exception Type: '. $ep_obj->getType() .' Effective Date: '. TTDate::getDate('DATE+TIME', $effective_date ) .' Punch Time: '. TTDate::getDate('DATE+TIME', $this->getTimeStamp() ) .' Grace: '. $ep_obj->getGrace(), __FILE__, __LINE__, __METHOD__, 10 );
										SystemJobQueue::Add( TTi18n::getText( 'ReCalculate Quick Exceptions' ), $this->getId(), 'CalculatePolicy', 'reCalculateForJobQueue', [ $this->getUser(), 'calcQuickExceptions', $start_date, $end_date ], 10, null, $effective_date, $this->getUser() );
									}
									unset( $meal_policy_arr, $mplf, $mp_obj );
								}
								break;
						}
					}

					//switch( $this->getType() ) {
					//	case 10: //Normal
					//		break;
					//	case 20: //Lunch
					//		//When returning from lunch/break, its possible that the previous punch (lunch/break out) could be the wrong status and needs to be changed. So we need to clear all lunch/break/transfer punch reminders here.
					//		break;
					//	case 30: //Break
					//		//When returning from lunch/break, its possible that the previous punch (lunch/break out) could be the wrong status and needs to be changed. So we need to clear all lunch/break/transfer punch reminders here.
					//		break;
					//}
					break;
				case 20: //Out
					//switch( $this->getType() ) {
					//	case 10: //Normal
					//		//Clear any end shift notifications in the future. As well as any lunch/break reminders that still might be pending.
					//		// **NOTE: This is *not* called when a transfer punch is saved and the Out punch is being created.
					//		break;
					//	case 20: //Lunch
					//		break;
					//	case 30: //Break
					//		break;
					//}
					break;
			}
		}

		return true;
	}

	/**
	 * Adds a transfer punch reminder notification
	 * @return bool
	 */
	function addPunchReminderTransfer( $payload ) {
		//Add Transfer Punch Reminder
		Debug::text( ' Add post-dated notification for transfer punch at: '. TTDate::getDate('DATE+TIME', $this->getTimeStamp() ) .' plus delay.', __FILE__, __LINE__, __METHOD__, 10 );
		$notification_subject = TTi18n::getText( 'Reminder: Transfer Punch.' );
		$notification_body = TTi18n::getText('Transfer punch.' );

		$notification_data = [
				'object_id'      => $this->getId(),
				'user_id'        => $this->getUser(),
				'priority_id'    => 2, //High - Don't use 1 here, as we shouldn't send a "must get attention" reminder for transfer as its a guess at best.
				'type_id'        => 'reminder_punch_transfer',
				'object_type_id' => 120, //120=PunchFactory
				'effective_date' => $this->getTimeStamp(),
				'title_short'    => $notification_subject,
				'body_short'     => $notification_body,
				'payload'        => $payload,
		];

		return Notification::sendNotification( $notification_data );
	}

	/**
	 * Adds/Removes notifications for punch reminders based on the current action.
	 * @return false
	 * @throws ReflectionException
	 */
	function handleReminderNotifications() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return false;
		}

		if ( $this->is_new != true ) {
			Debug::text( '  Not a new punch, ignore reminder notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		if ( $this->getEnablePreMatureException() == false ) {
			Debug::text( '  User is not punching themselves, ignore reminder notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		//Make sure the current punch isn't in the past, or too far in the future.
		if ( $this->getTimeStamp() > ( TTDate::getTime() + 7200 ) || $this->getTimeStamp() < ( TTDate::getTime() - 7200 ) ) {
			Debug::text( '  Punch does not appear to be real-time, ignore reminder notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		$this->setScheduleID( $this->findScheduleID( $this->getTimeStamp() ) );

		$payload = [ 'timetrex' => [ 'event' => [ [ 'type' => 'open_view', 'data' => [], 'view_name' => 'InOut', 'action_name' => 'add' ] ] ] ]; //Open In/Out view for punching.

		switch( $this->getStatus() ) {
			case 10: //In
				switch( $this->getType() ) {
					case 10: //Normal
						Notification::deletePendingNotifications( [ 'reminder_punch_normal_in', 'reminder_punch_transfer' ], $this->getUser(), null, $this->getTimeStamp() );

						//Add Transfer Punch Reminder
						$this->addPunchReminderTransfer( $payload );

						if ( $this->getTransfer() == false ) {
							Notification::deletePendingNotifications( [ 'reminder_punch_normal_out', 'reminder_punch_lunch_out', 'reminder_punch_break_out' ], $this->getUser(), null, $this->getTimeStamp() );

							//Add reminder for end of shift.
							$slf = TTnew( 'ScheduleListFactory' ); /** @var ScheduleListFactory $slf */
							$s_obj = $slf->getScheduleObjectByUserIdAndEpoch( $this->getUser(), $this->getTimeStamp() );  /** @var ScheduleFactory $s_obj */
							if ( is_object( $s_obj ) ) {
								Debug::text( ' Add post-dated notification for end of shift at: '. TTDate::getDate('DATE+TIME', $s_obj->getEndTime() ), __FILE__, __LINE__, __METHOD__, 10 );
								$notification_title_short = TTi18n::getText( 'Reminder: Punch Out.' );
								$notification_title_long = TTi18n::getText( 'Reminder: Punch Out by %1', TTDate::getDate('TIME', $s_obj->getEndTime() ) );
								$notification_body = TTi18n::getText( 'Punch out for the end of your shift by '. TTDate::getDate('TIME', $s_obj->getEndTime() ) );

								$notification_data = [
										'object_id'      => $this->getId(),
										'user_id'        => $this->getUser(),
										'priority_id'    => 2, //High - Don't use 1 here, as we shouldn't send a "must get attention" reminder for end of shift, as often people will work past.
										'type_id'        => 'reminder_punch_normal_out',
										'object_type_id' => 120, //120=PunchFactory
										'effective_date' => $s_obj->getEndTime(),
										'title_short'    => $notification_title_short,
										'title_long'     => $notification_title_long,
										'body_short'     => $notification_body,
										'payload'        => $payload,
								];

								Notification::sendNotification( $notification_data );
							} else {
								Debug::text( ' No schedule, unable to add post-dated reminder notification for end of shift...', __FILE__, __LINE__, __METHOD__, 10 );
							}

							//Add reminder for start of lunch.
							$meal_policy_arr = $this->getMealPolicies();
							$mplf = $meal_policy_arr['lf'];
							Debug::Text( 'Meal Policy Record Count: ' . $mplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
							if ( $mplf->getRecordCount() > 0 ) {
								//FIXME: If they have multiple meal policies, how do we determine which one to send a reminder on?
								foreach( $mplf as $mp_obj ) {
									$start_lunch_time = ( $this->getTimeStamp() + $mp_obj->getTriggerTime() );

									Debug::text( ' Add post-dated notification for start of lunch at: '. TTDate::getDate('DATE+TIME', $start_lunch_time ), __FILE__, __LINE__, __METHOD__, 10 );
									$notification_title_short = TTi18n::getText( 'Reminder: Punch Out for Lunch.' );
									$notification_title_long = TTi18n::getText( 'Reminder: Punch Out for Lunch by %1', TTDate::getDate('TIME', $start_lunch_time ) );
									$notification_body = TTi18n::getText( 'Punch out for lunch by '. TTDate::getDate('TIME', $start_lunch_time ) );

									$notification_data = [
											'object_id'      => $this->getId(),
											'user_id'        => $this->getUser(),
											'priority_id'    => 2, //High
											'type_id'        => 'reminder_punch_lunch_out',
											'object_type_id' => 120, //120=PunchFactory
											'effective_date' => $start_lunch_time,
											'title_short'    => $notification_title_short,
											'title_long'     => $notification_title_long,
											'body_short'     => $notification_body,
											'payload'        => $payload,
									];

									Notification::sendNotification( $notification_data );
								}
							} else {
								Debug::text( ' No meal policy, unable to add post-dated reminder notification for start of lunch...', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $mplf );

							//Add reminder for start of break(s).
							$break_policy_arr = $this->getBreakPolicies();
							$bplf = $break_policy_arr['lf'];
							Debug::Text( 'Break Policy Record Count: ' . $bplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
							if ( $bplf->getRecordCount() > 0 ) {
								$prev_start_break_time = null;
								foreach( $bplf as $bp_obj ) {
									//If they include multiple breaks, there is likely too much flexibility for this to be useful, so just disable reminders in this case.
									if ( $bp_obj->getIncludeMultipleBreaks() == true ) {
										continue;
									}

									$start_break_time = ( $this->getTimeStamp() + $bp_obj->getTriggerTime() );

									//Skip duplicate notifications.
									if ( $prev_start_break_time == $start_break_time ) {
										Debug::text( '   Skipping duplicate break notification at: '. $start_break_time, __FILE__, __LINE__, __METHOD__, 10 );
										continue;
									}

									Debug::text( ' Add post-dated notification for start of break at: '. TTDate::getDate('DATE+TIME', $start_break_time ), __FILE__, __LINE__, __METHOD__, 10 );
									$notification_title_short = TTi18n::getText( 'Reminder: Punch Out for Break.' );
									$notification_title_long = TTi18n::getText( 'Reminder: Punch Out for Break by %1', TTDate::getDate('TIME', $start_break_time ) );
									$notification_body = TTi18n::getText( 'Punch out for break by '. TTDate::getDate('TIME', $start_break_time ) );

									$notification_data = [
											'object_id'      => $this->getId(),
											'user_id'        => $this->getUser(),
											'priority_id'    => 2, //High
											'type_id'        => 'reminder_punch_break_out',
											'object_type_id' => 120, //120=PunchFactory
											'effective_date' => $start_break_time,
											'title_short'    => $notification_title_short,
											'title_long'     => $notification_title_long,
											'body_short'     => $notification_body,
											'payload'        => $payload,
									];

									Notification::sendNotification( $notification_data );

									$prev_start_break_time = $start_break_time;
								}
							} else {
								Debug::text( ' No break policy, unable to add post-dated reminder notification for start of break(s)...', __FILE__, __LINE__, __METHOD__, 10 );
							}
							unset( $bplf );
						}

						break;
					case 20: //Lunch
						//When returning from lunch/break, its possible that the previous punch (lunch/break out) could be the wrong status and needs to be changed. So we need to clear all lunch/break/transfer punch reminders here.
						Debug::text( ' Delete post-dated notification for end of lunch, end of break, and punch transfer...', __FILE__, __LINE__, __METHOD__, 10 );
						Notification::deletePendingNotifications( [ 'reminder_punch_lunch_in', 'reminder_punch_break_in', 'reminder_punch_transfer' ], $this->getUser(), null, $this->getTimeStamp() );

						$this->addPunchReminderTransfer( $payload );
						break;
					case 30: //Break
						//When returning from lunch/break, its possible that the previous punch (lunch/break out) could be the wrong status and needs to be changed. So we need to clear all lunch/break/transfer punch reminders here.
						Debug::text( ' Delete post-dated notification for end of break, end of lunch, and punch transfer...', __FILE__, __LINE__, __METHOD__, 10 );
						Notification::deletePendingNotifications( [ 'reminder_punch_lunch_in', 'reminder_punch_break_in', 'reminder_punch_transfer' ], $this->getUser(), null, $this->getTimeStamp() );

						$this->addPunchReminderTransfer( $payload );
						break;
				}
				break;
			case 20: //Out
				switch( $this->getType() ) {
					case 10: //Normal
						//Clear any end shift notifications in the future. As well as any lunch/break reminders that still might be pending.
						// **NOTE: This is *not* called when a transfer punch is saved and the Out punch is being created.
						Debug::text( ' Delete post-dated notification for end of shift at...', __FILE__, __LINE__, __METHOD__, 10 );
						Notification::deletePendingNotifications( [ 'reminder_punch_normal_out', 'reminder_punch_lunch_out', 'reminder_punch_break_out', 'reminder_punch_transfer' ], $this->getUser(), null, $this->getTimeStamp() );
						break;
					case 20: //Lunch
						Notification::deletePendingNotifications( [ 'reminder_punch_lunch_out', 'reminder_punch_transfer' ], $this->getUser(), null, $this->getTimeStamp() );

						//Add reminder for end of lunch.
						$meal_policy_arr = $this->getMealPolicies();
						$mplf = $meal_policy_arr['lf'];

						Debug::Text( 'Meal Policy Record Count: ' . $mplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
						if ( $mplf->getRecordCount() > 0 ) {
							$mp_obj = $mplf->getCurrent();

							$end_lunch_time = ( $this->getTimeStamp() + $mp_obj->getAmount() );

							Debug::text( ' Add post-dated notification for end of lunch by: '. TTDate::getDate('DATE+TIME', $end_lunch_time ), __FILE__, __LINE__, __METHOD__, 10 );
							$notification_title_short = TTi18n::getText( 'Reminder: Punch In from Lunch.' );
							$notification_title_long = TTi18n::getText( 'Reminder: Punch In from Lunch by %1', TTDate::getDate('TIME', $end_lunch_time ));
							$notification_body = TTi18n::getText( 'Punch in from lunch at '. TTDate::getDate('TIME', $end_lunch_time ) );

							$notification_data = [
									'object_id'      => $this->getId(),
									'user_id'        => $this->getUser(),
									'priority_id'    => 1, //Critical, likely a missed punch scenario, try to get attention.
									'type_id'        => 'reminder_punch_lunch_in',
									'object_type_id' => 120, //120=PunchFactory
									'effective_date' => $end_lunch_time,
									'title_short'    => $notification_title_short,
									'title_long'     => $notification_title_long,
									'body_short'     => $notification_body,
									'payload'        => $payload,
							];

							Notification::sendNotification( $notification_data );
						} else {
							Debug::text( ' No meal policy, unable to add post-dated reminder notification for end of lunch...', __FILE__, __LINE__, __METHOD__, 10 );
						}
						unset( $mplf );

						break;
					case 30: //Break
						Notification::deletePendingNotifications( [ 'reminder_punch_break_out', 'reminder_punch_transfer' ], $this->getUser(), null, $this->getTimeStamp() );

						//Add reminder for end of break.
						$break_policy_arr = $this->getBreakPolicies();
						$bplf = $break_policy_arr['lf'];

						//FIXME: How do we handle multiple breaks of different lengths, or combined breaks?
						if ( $bplf->getRecordCount() > 0 ) {
							foreach ( $bplf as $bp_obj ) {
								if ( $bp_obj->getAutoDetectType() == 20 ) { //20=Punch Time
									$end_break_time = ( $this->getTimeStamp() + $bp_obj->getMaximumPunchTime() ); //Max punch time could be different than break time, so this instead of getAmount().
								} else {
									$end_break_time = ( $this->getTimeStamp() + $bp_obj->getAmount() );
								}
								Debug::text( ' Add post-dated notification for end of break at: '. TTDate::getDate('DATE+TIME', $end_break_time ), __FILE__, __LINE__, __METHOD__, 10 );
								$notification_title_short = TTi18n::getText( 'Reminder: Punch in from Break.' );
								$notification_title_long = TTi18n::getText( 'Reminder: Punch in from Break by %1', TTDate::getDate('TIME', $end_break_time ) );
								$notification_body = TTi18n::getText( 'Punch in from break by '. TTDate::getDate('TIME', $end_break_time ) );

								$notification_data = [
										'object_id'      => $this->getId(),
										'user_id'        => $this->getUser(),
										'priority_id'    => 1, //Critical, likely a missed punch scenario, try to get attention.
										'type_id'        => 'reminder_punch_break_in',
										'object_type_id' => 120, //120=PunchFactory
										'effective_date' => $end_break_time,
										'title_short'    => $notification_title_short,
										'title_long'     => $notification_title_long,
										'body_short'     => $notification_body,
										'payload'        => $payload,
								];

								Notification::sendNotification( $notification_data );
								break; //Stop after first break policy.
							}
						} else {
							Debug::text( ' No break policy, unable to add post-dated reminder notification for end of break...', __FILE__, __LINE__, __METHOD__, 10 );
						}
						unset( $bplf );

						break;
				}
				break;
		}

	}

	/**
	 * Takes Punch rows and calculates the total breaks/lunches and how long each is.
	 * @param $data
	 * @return array|bool
	 */
	static function calcMealAndBreakTotalTime( $data ) {

		if ( is_array( $data ) && count( $data ) > 0 ) {
			$date_break_totals = array();
			$tmp_date_break_totals = array();
			//Sort data by date_stamp at the top, so it works for multiple days at a time.
			foreach ( $data as $row ) {
				if ( $row['type_id'] != 10 ) {
					if ( $row['status_id'] == 20 ) {
						$tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['prev'] = $row['raw_time_stamp'];
					} else if ( isset( $tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['prev'] ) ) {
						if ( !isset( $tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_time'] ) ) {
							$tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_time'] = 0;
						}

						$tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_time'] = TTMath::add( $tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_time'], TTMath::sub( $row['raw_time_stamp'], $tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['prev'] ) );
						if ( !isset( $tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_breaks'] ) ) {
							$tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_breaks'] = 0;
						}
						$tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_breaks']++;

						if ( $tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_time'] > 0 ) {
							if ( $row['type_id'] == 20 ) {
								$break_name = TTi18n::gettext( 'Lunch (Taken)' );
							} else {
								$break_name = TTi18n::gettext( 'Break (Taken)' );
							}

							$date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ] = array(
									'break_name'   => $break_name,
									'total_time'   => $tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_time'],
									'total_breaks' => $tmp_date_break_totals[ $row['date_stamp'] ][ $row['type_id'] ]['total_breaks'],
							);
							unset( $break_name );
						}
					}
				}
			}

			if ( empty( $date_break_totals ) == false ) {
				return $date_break_totals;
			}
		}

		return false;
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
			//We need to set the UserID as soon as possible.
			if ( isset( $data['user_id'] ) && $data['user_id'] != '' ) {
				Debug::text( 'Setting User ID: ' . $data['user_id'], __FILE__, __LINE__, __METHOD__, 10 );
				$this->setUser( $data['user_id'] );
			}


			/*
						//We need to set the UserDate as soon as possible.
						if ( isset($data['user_id']) AND $data['user_id'] != ''
								AND isset($data['date_stamp']) AND $data['date_stamp'] != ''
								AND isset($data['start_time']) AND $data['start_time'] != '' ) {
							Debug::text('Setting User Date ID based on User ID:'. $data['user_id'] .' Date Stamp: '. $data['date_stamp'] .' Start Time: '. $data['start_time'], __FILE__, __LINE__, __METHOD__, 10);
							$this->setUserDate( $data['user_id'], TTDate::parseDateTime( $data['date_stamp'].' '.$data['start_time'] ) );
						} elseif ( isset( $data['user_date_id'] ) AND $data['user_date_id'] > 0 ) {
							Debug::text(' Setting UserDateID: '. $data['user_date_id'], __FILE__, __LINE__, __METHOD__, 10);
							$this->setUserDateID( $data['user_date_id'] );
						} else {
							Debug::text(' NOT CALLING setUserDate or setUserDateID!', __FILE__, __LINE__, __METHOD__, 10);
						}

						if ( isset($data['overwrite']) ) {
							$this->setEnableOverwrite( TRUE );
						}
			*/

			/*
				ORDER IS EXTREMELY IMPORTANT FOR THIS FUNCTION:
				1. $pf->setUser();
				1b. $pf->setTransfer() //include timestamp for this.
				2. $pf->setType();
				3. $pf->setStatus();
				4. $pf->setTimeStamp();
				5. $pf->setPunchControlID();

				All these related fields MUST be passed to this function as well, even if they are blank.
			*/

			//Parse time stamp above loop so we don't have to do it twice.
			if ( isset( $data['epoch'] ) && $data['epoch'] != '' ) {
				$full_time_stamp = $data['epoch'];
			} else if ( isset( $data['punch_date'] ) && $data['punch_date'] != '' && isset( $data['punch_time'] ) && $data['punch_time'] != '' ) {
				$full_time_stamp = TTDate::parseDateTime( $data['punch_date'] . ' ' . $data['punch_time'] );
				//Debug::text('Setting Punch Time/Date: Date Stamp: '. $data['punch_date'] .' Time Stamp: '. $data['punch_time'] .' Full Time Stamp: '. $data['full_time_stamp'] .' Parsed: '. TTDate::getDate('DATE+TIME', $full_time_stamp ), __FILE__, __LINE__, __METHOD__, 10);
			} else if ( isset( $data['time_stamp'] ) && $data['time_stamp'] != '' ) {
				$full_time_stamp = TTDate::parseDateTime( $data['time_stamp'] );
			} else {
				$full_time_stamp = null;
			}

			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[ $key ] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						case 'transfer':
							$this->$function( $data[ $key ], $full_time_stamp ); //Assume time_stamp contains date as well.
							if ( $this->getTransfer() == true ) {
								$this->setEnableAutoTransfer( true );
							}
							break;
						case 'split_punch_control':
							$this->setEnableSplitPunchControl( $data[ $key ] );
							break;
						case 'time_stamp':
							if ( method_exists( $this, $function ) ) {
								if ( isset( $data['disable_rounding'] ) && $data['disable_rounding'] == true ) {
									$enable_rounding = false;
								} else {
									$enable_rounding = true;
								}

								$this->$function( $full_time_stamp, $enable_rounding ); //Assume time_stamp contains date as well.
							}
							break;
						case 'actual_time_stamp': //Ignore actual/original timestamps.
						case 'original_time_stamp':
							break;
						case 'punch_control_id':
							//If this is a new punch or punch_contol_id is not being set, find a new one to use.
							if ( $data['punch_control_id'] == '' || $data['punch_control_id'] == TTUUID::getZeroID() ) {
								$this->setPunchControlID( $this->findPunchControlID() );
								Debug::text( 'Setting Punch Control ID: ' . $this->getPunchControlID() . ' Was passed: ' . $data['punch_control_id'], __FILE__, __LINE__, __METHOD__, 10 );
							} else {
								Debug::text( 'Valid Punch Control ID passed...', __FILE__, __LINE__, __METHOD__, 10 );
								$this->$function( $data[ $key ] );
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

			//Handle actual/original timestamp at the end, as we need to make sure we have the full_time_stamp set first.
			if ( $this->isNew() == true && $full_time_stamp != null ) {
				Debug::text( 'Setting actual/original timestamp: ' . $full_time_stamp, __FILE__, __LINE__, __METHOD__, 10 );
				$this->setActualTimeStamp( $full_time_stamp );
				//$this->setOriginalTimeStamp( $this->getTimeStamp() ); //set in preSave()
			} else {
				Debug::text( 'NOT setting actual/original timestamp...', __FILE__, __LINE__, __METHOD__, 10 );
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
		$sf = TTnew( 'StationFactory' ); /** @var StationFactory $sf */

		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[ $variable ] ) && $include_columns[ $variable ] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'user_id':
						case 'first_name':
						case 'last_name':
						case 'user_status_id':
						case 'group_id':
						case 'group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'default_job_id':
						case 'default_job':
						case 'default_job_item_id':
						case 'default_job_item':
						case 'pay_period_id':
						case 'total_time': //Used for Map, Distance tab.
						case 'branch_id':
						case 'branch':
						case 'department_id':
						case 'department':
						case 'job_id':
						case 'job':
						case 'job_manual_id':
						case 'job_item_id':
						case 'job_item':
						case 'job_item_manual_id':
						case 'quantity':
						case 'bad_quantity':
						case 'user_date_id':
						case 'meal_policy_id':
						case 'note':
						case 'station_type_id':
						case 'station_station_id':
						case 'station_source':
						case 'station_description':
							$data[ $variable ] = $this->getColumn( $variable );
							break;
						case 'punch_tag_id':
							$data[ $variable ] = json_decode( $this->getColumn( $variable ), true );
							break;
						case 'punch_tag': //Punch Tags for display purposes.
							//These functions are slow to obtain (especially in a large loop), so make sure the column is requested explicitly before we include it.
							//Flex currently doesn't specify these fields in the Edit view though, so this breaks Flex.
							if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE && ( $this->isClientFriendly() == true || ( isset( $include_columns[$variable] ) AND $include_columns[$variable] == TRUE ) ) ) {
								$data[ $variable ] = $this->getPunchTagDisplay();
							}
							break;
						case 'status':
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'date_stamp': //Date the punch falls on for timesheet generation. The punch itself may have a different date.
							$data[ $variable ] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'date_stamp' ) ) );
							break;
						case 'time_stamp': //Full date/time of the punch itself.
							$data[ $variable ] = TTDate::getAPIDate( 'DATE+TIME', TTDate::strtotime( $this->getColumn( 'time_stamp' ) ) );
							break;
						case 'raw_time_stamp':
							//Need a epoch value of the timestamp, so PunchFactory::calcMealAndBreakTotalTime() can use it for calculating Lunch/Break (Taken) when seconds are involved.
							// Otherwise returning getAPIDate() excludes the seconds if the users time format doesn't include them, and they can mismatch.
							$data[ $variable ] = TTDate::strtotime( $this->getColumn( 'time_stamp' ) );
							break;
						case 'punch_date': //Just date portion of the punch
							$data[ $variable ] = TTDate::getAPIDate( 'DATE', TTDate::strtotime( $this->getColumn( 'time_stamp' ) ) );
							break;
						case 'punch_time': //Just the time portion of the punch
							$data[ $variable ] = TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->getColumn( 'time_stamp' ) ) );
							break;
						case 'original_time_stamp':
							$data[ $variable ] = TTDate::getAPIDate( 'DATE+TIME', TTDate::strtotime( $this->getColumn( 'original_time_stamp' ) ) );
							break;
						case 'actual_time_stamp':
							$data[ $variable ] = TTDate::getAPIDate( 'DATE+TIME', TTDate::strtotime( $this->getColumn( 'actual_time_stamp' ) ) );
							break;
						case 'actual_time':
							$data[ $variable ] = TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->getColumn( 'actual_time_stamp' ) ) );
							break;
						case 'actual_time_diff':
							$data[ $variable ] = TTDate::getTimeUnit( $this->getColumn( 'actual_time_diff' ) );
							break;
						case 'station_type':
							$data[ $variable ] = Option::getByKey( $this->getColumn( 'station_type_id' ), $sf->getOptions( 'type' ) );
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

			if ( $this->isCustomFieldsIncluded( $include_columns ) == true && is_object( $this->getUserObject() ) ) {
				$data = $this->getCustomFields( $this->getUserObject()->getCompany(), $data, $include_columns );
			}
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Punch - Employee' ) . ': ' . UserListFactory::getFullNameById( $this->getUser() ) . ' ' . TTi18n::getText( 'Timestamp' ) . ': ' . TTDate::getDate( 'DATE+TIME', $this->getTimeStamp() ), null, $this->getTable(), $this );
	}

	/**
	 * Save real-time punches through the JobQueue
	 * @param $data
	 * @param $validate_only
	 * @param $ignore_warning
	 * @param $validator_stats
	 * @param $validator
	 * @param $save_result
	 * @param $key
	 * @return array
	 * @throws DBError
	 * @throws ReflectionException
	 */
	static function setUserPunchForJobQueue( $data, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key ) {
		//**IMPORTANT** The users timezone/preferences should all be set before this is called. Currently that is done in SystemJobQueueFactory::run()

		Debug::Text( '  Saving Punch from job queue...', __FILE__, __LINE__, __METHOD__, 10 );
		$pf = TTnew('PunchFactory');
		[ $validator, $validator_stats, $key, $save_result ] = $pf->setUserPunch( $data, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key );

		//Since we are saving the punch in the background, we need to send a notification if their is a validation failure.
		if ( !( $validator_stats['valid_records'] > 0 && $validator_stats['total_records'] == $validator_stats['valid_records'] ) ) {
			Debug::Arr( $validator, '  Punch failed validation check!', __FILE__, __LINE__, __METHOD__, 10 );

			$validator_obj = new Validator();
			$notification_data = [
					'object_id'      => TTUUID::getZeroID(),
					'user_id'        => $data['user_id'],
					'type_id'        => 'punch',
					'object_type_id' => 120,
					'priority' 		 => 2, //2=High
					'title_short'    => TTi18n::getText( 'Punch Save Failed: %1', [ TTDate::getDate('DATE+TIME', $data['epoch'] ) ] ),
					'body_short'     => $validator_obj->getTextErrors( false, $validator ),
					'payload'        => [ 'link' => Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=TimeSheet' ],
			];

			Notification::sendNotification( $notification_data );
		}

		//Send a direct background notification to tell the browser to refresh the job queue. Do this even if the punch fails to save.
		SystemJobQueue::sendNotificationToBrowser( $data['user_id'] );

		return [ $validator, $validator_stats, $key, $save_result ];
	}

	/**
	 * Helper function to save real-time punches from both the API and the JobQueue.
	 * @param $data
	 * @param $validate_only
	 * @param $ignore_warning
	 * @param $validator_stats
	 * @param $validator
	 * @param $save_result
	 * @param $key
	 * @return mixed|null
	 * @throws DBError
	 */
	function setUserPunch( $data, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key ) {
		$transaction_function = function () use ( $data, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key ) {
			$lf = TTnew( 'PunchFactory' ); /** @var PunchFactory $lf */
			if ( $validate_only == false ) {                  //Only switch into serializable mode when actually saving the record.
				$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing incorrect calculations in user_date_total table.
			}
			$lf->StartTransaction();

			//We can't set the PunchControlID from $data, otherwise findPunchControlID() will never work.
			//This causes transfer punches to fail.
			if ( isset( $data['punch_control_id'] ) ) {
				$tmp_punch_control_id = $data['punch_control_id'];
				unset( $data['punch_control_id'] );
			}

			$lf->setObjectFromArray( $data );

			//Checking the station here prevents the user from bypassing the station allowed checks.
			//  It also delays updating the station record allowed_date until the punch is saved, turning getUserPunch() into a 100% read-only SQL queries and deferring all writes to the job queue on save in high load cases.
			$is_station_allowed = false;
			if ( is_object( $lf->getStationObject() ) && isset( $data['_server_remote_addr'] ) ) {
				$_SERVER['REMOTE_ADDR'] = $data['_server_remote_addr']; //Overwrite this global variable so Misc::getRemoteIPAddress() can obtain it in $station_obj->checkAllowed() below.

				$station_obj = $lf->getStationObject();

				//Don't update allowed_date due to concurrency issues with many users from the same station record.
				//  Also for PC stations its not that helpful anyways.
				//	postgres9 error: [-29: ERROR:  could not serialize access due to concurrent update] in EXECUTE("UPDATE station as b set allowed_date = ? WHERE EXISTS ( SELECT null FROM station as a where b.id = a.id and b.id = ? FOR UPDATE OF A SKIP LOCKED )")
				if ( $station_obj->checkAllowed( $lf->getUser(), $station_obj->getStation(), $station_obj->getType(), false ) == true ) {
					$is_station_allowed = true;
				}
			}

			if ( $is_station_allowed == true ) {
				if ( isset( $data['status_id'] ) && $data['status_id'] == 20 && isset( $tmp_punch_control_id ) && $tmp_punch_control_id != '' ) {
					$lf->setPunchControlID( $tmp_punch_control_id );
				} else {
					$lf->setPunchControlID( $lf->findPunchControlID() );
				}
				unset( $tmp_punch_control_id );

				$lf->setEnablePreMatureException( true ); //Enable pre-mature exceptions at this point.

				$key = 0;
				$is_valid = $lf->isValid( $ignore_warning );
				if ( $is_valid == true ) {
					Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
					if ( $validate_only == true ) {
						$save_result[$key] = true;
						$validator_stats['valid_records']++;
					} else {
						//Save Punch object and start on PunchControl
						if ( $save_result[$key] = $lf->Save( false ) == true ) {
							unset( $data['id'] ); //ID must be removed so it doesn't get confused with PunchControlID
							Debug::Text( 'Saving PCF data... Punch Control ID: ' . $lf->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10 );
							$pcf = TTnew( 'PunchControlFactory' ); /** @var PunchControlFactory $pcf */

							$pcf->setId( $lf->getPunchControlID() );
							$pcf->setPunchObject( $lf );

							$pcf->setObjectFromArray( $data );

							$pcf->setEnableStrictJobValidation( true );
							$pcf->setEnableCalcUserDateID( true );
							$pcf->setEnableCalcTotalTime( true );
							$pcf->setEnableCalcSystemTotalTime( true );
							$pcf->setEnableCalcWeeklySystemTotalTime( true );
							$pcf->setEnableCalcUserDateTotal( true );
							$pcf->setEnableCalcException( true );
							$pcf->setEnablePreMatureException( true ); //Enable pre-mature exceptions at this point.

							Debug::Arr( $lf->data, 'Punch Object: ', __FILE__, __LINE__, __METHOD__, 10 );
							Debug::Arr( $pcf->data, 'Punch Control Object: ', __FILE__, __LINE__, __METHOD__, 10 );
							if ( $pcf->isValid() ) {
								$validator_stats['valid_records']++;
								if ( $pcf->Save( true, true ) != true ) { //Force isNew() lookup.
									$is_valid = $pcf_valid = false;
								}
							} else {
								$is_valid = $pcf_valid = false;
							}
						}
					}
				}

				if ( $is_valid == false ) {
					Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

					$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

					Debug::Text( 'PF Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );
					$validator[$key] = $lf->Validator->getErrorsArray();
					//Merge PCF validation errors onto array.
					if ( isset( $pcf ) && $pcf_valid == false ) {
						Debug::Text( 'PCF Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );
						$validator[$key] += $pcf->Validator->getErrorsArray();
					}
				}
			} else {
				Debug::Text( 'Station IS NOT Allowed! ID: ' .  $lf->getStation() . ' User ID: ' . $lf->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
				$lf->Validator->isTrue( 'user_name', false, TTi18n::gettext( 'You are not authorized to punch in or out from this station!' ) );
				$validator[$key] = $lf->Validator->getErrorsArray();
			}

			$lf->CommitTransaction();
			$lf->setTransactionMode(); //Back to default isolation level.

			return [ $validator, $validator_stats, $key, $save_result ];
		};

		return $this->RetryTransaction( $transaction_function );
	}
}

?>
