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
 * @package Modules\PayPeriod
 */
class PayPeriodScheduleFactory extends Factory {
	protected $table = 'pay_period_schedule';
	protected $pk_sequence_name = 'pay_period_schedule_id_seq'; //PK Sequence name

	protected $holiday_epochs = []; //Cached holiday epochs for getNextPayPeriod

	protected $create_initial_pay_periods = false;
	protected $enable_create_initial_pay_periods = true;

	/**
	 * @var bool
	 */
	private $next_primary;
	/**
	 * @var bool|int|null
	 */
	private $next_transaction_date;
	/**
	 * @var bool|int|null
	 */
	private $next_end_date;
	/**
	 * @var bool|int
	 */
	private $next_start_date;
	/**
	 * @var string|null
	 */
	private $original_time_zone;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'primary_date_ldom' )->setFunctionMap( 'PrimaryDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'primary_transaction_date_ldom' )->setFunctionMap( 'PrimaryTransactionDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'primary_transaction_date_bd' )->setFunctionMap( 'PrimaryTransactionDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'secondary_date_ldom' )->setFunctionMap( 'SecondaryDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'secondary_transaction_date_ldom' )->setFunctionMap( 'SecondaryTransactionDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'secondary_transaction_date_bd' )->setFunctionMap( 'SecondaryTransactionDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'anchor_date' )->setFunctionMap( 'AnchorDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'primary_date' )->setFunctionMap( 'PrimaryDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'primary_transaction_date' )->setFunctionMap( 'PrimaryTransactionDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'secondary_date' )->setFunctionMap( 'SecondaryDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'secondary_transaction_date' )->setFunctionMap( 'SecondaryTransactionDate' )->setType( 'timestamp' )->setIsNull( true ),
							TTSCol::new( 'day_start_time' )->setFunctionMap( 'DayStartTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'day_continuous_time' )->setFunctionMap( 'ContinuousTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'start_week_day_id' )->setFunctionMap( 'StartWeekDay' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'start_day_of_week' )->setFunctionMap( 'StartDayOfWeek' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'transaction_date' )->setFunctionMap( 'TransactionDate' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'primary_day_of_month' )->setFunctionMap( 'PrimaryDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'secondary_day_of_month' )->setFunctionMap( 'SecondaryDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'primary_transaction_day_of_month' )->setFunctionMap( 'PrimaryTransactionDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'secondary_transaction_day_of_month' )->setFunctionMap( 'SecondaryTransactionDayOfMonth' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'transaction_date_bd' )->setFunctionMap( 'TransactionDateBusinessDay' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'time_zone' )->setFunctionMap( 'TimeZone' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'new_day_trigger_time' )->setFunctionMap( 'NewDayTriggerTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'maximum_shift_time' )->setFunctionMap( 'MaximumShiftTime' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'shift_assigned_day_id' )->setFunctionMap( 'ShiftAssignedDay' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'timesheet_verify_before_end_date' )->setFunctionMap( 'TimeSheetVerifyBeforeEndDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'timesheet_verify_before_transaction_date' )->setFunctionMap( 'TimeSheetVerifyBeforeTransactionDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'timesheet_verify_notice_before_transaction_date' )->setFunctionMap( 'TimeSheetVerifyNoticeBeforeTransactionDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'timesheet_verify_notice_email' )->setFunctionMap( 'TimeSheetVerifyNoticeEmail' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'annual_pay_periods' )->setFunctionMap( 'AnnualPayPeriods' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'timesheet_verify_type_id' )->setFunctionMap( 'TimeSheetVerifyType' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'create_days_in_advance' )->setFunctionMap( 'CreateDaysInAdvance' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'auto_close_after_days' )->setFunctionMap( 'AutoCloseAfterDays' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'timesheet_verify_agreement' )->setFunctionMap( 'TimesheetVerifyAgreement' )->setType( 'text' )->setIsNull( true ),
							TTSCol::new( 'total_users' )->setFunctionMap( 'Employees' )->setIsSynthetic( true ),
							TTSCol::new( 'user' )->setFunctionMap( 'User' )->setIsSynthetic( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_pay_period_schedule' )->setLabel( TTi18n::getText( 'Pay Period Schedule' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) ),
											TTSField::new( 'type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIPayPeriodSchedule' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'primary_day_of_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Pay Period Start Day of Month' ) ),
											TTSField::new( 'primary_transaction_day_of_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Transaction Day Of Month' ) ),
											TTSField::new( 'secondary_day_of_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Pay Period Start Day of Month' ) ),
											TTSField::new( 'secondary_transaction_day_of_month' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Transaction Day Of Month' ) ),
											TTSField::new( 'annual_pay_periods' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Annual Pay Periods' ) ),
											TTSField::new( 'start_day_of_week' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Period Starts On' ) ),
											TTSField::new( 'transaction_date' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Transaction Date' ) ),
											TTSField::new( 'transaction_date_bd' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Transaction Always on Business Day' ) ),
											TTSField::new( 'anchor_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Create Initial Pay Periods From' ) ),
											TTSField::new( 'user' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Employees' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'start_week_day_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Overtime Week' ) ),
											TTSField::new( 'time_zone' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Time Zone' ) ),
											TTSField::new( 'new_day_trigger_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Minimum Time-Off Between Shifts' ) ),
											TTSField::new( 'maximum_shift_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Maximum Shift Time' ) ),
											TTSField::new( 'shift_assigned_day_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Assign Shifts To' ) ),
											TTSField::new( 'create_days_in_advance' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Create Pay Periods' ) ),
											TTSField::new( 'auto_close_after_days' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Auto Close' ) ),
											TTSField::new( 'timesheet_verify_type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'TimeSheet Verification' ) ),
											TTSField::new( 'timesheet_verify_before_end_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Verification Window Starts' ) ),
											TTSField::new( 'timesheet_verify_before_transaction_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Verification Window Ends' ) ),
											TTSField::new( 'timesheet_verify_agreement' )->setType( 'text' )->setLabel( TTi18n::getText( 'Agreement' ) )
									)
							),
							TTSTab::new( 'tab_pay_period' )->setLabel( TTi18n::getText( 'Pay Periods' ) )->setInitCallback( 'initSubPayPeriodsView' )->setDisplayOnMassEdit( false )->setSubView( true ),
					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'a.status_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'manual_id' )->setType( 'numeric' )->setColumn( 'a.manual_id' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'type' )->setType( 'text' )->setColumn( 'a.type' ),
							TTSSearchField::new( 'description' )->setType( 'text' )->setColumn( 'a.description' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIPayPeriodSchedule' )->setMethod( 'getPayPeriodSchedule' )
									->setSummary( 'Get pay period schedule records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIPayPeriodSchedule' )->setMethod( 'setPayPeriodSchedule' )
									->setSummary( 'Add or edit pay period schedule records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIPayPeriodSchedule' )->setMethod( 'deletePayPeriodSchedule' )
									->setSummary( 'Delete pay period schedule records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIPayPeriodSchedule' )->setMethod( 'getPayPeriodSchedule' ) ),
											   ) ),
							TTSAPI::new( 'APIPayPeriodSchedule' )->setMethod( 'getPayPeriodScheduleDefaultData' )
									->setSummary( 'Get default pay period schedule data used for creating new pay period schedules. Use this before calling setPayPeriodSchedule to get the correct default data.' ),
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
				/*
				 * Set type to MANUAL to disable PP schedule. Rather then add a status_id field.
				 */
				$retval = [
						5  => TTi18n::gettext( 'Manual' ),
						10 => TTi18n::gettext( 'Weekly (52/year)' ),
						20 => TTi18n::gettext( 'Bi-Weekly (26/year)' ),
						30 => TTi18n::gettext( 'Semi-Monthly (24/year)' ),
						//40  => TTi18n::gettext('Monthly + Advance'), //Handled with monthly PP schedule and Tax / Deduction to automatically enter advance each month. Advances are paid manually.
						50 => TTi18n::gettext( 'Monthly (12/year)' ), //Must have this here, for ROEs

						100 => TTi18n::gettext( 'Weekly (53/year)' ),
						200 => TTi18n::gettext( 'Bi-Weekly (27/year)' ),
				];
				break;
			case 'annual_pay_periods_per_type':
				//Mostly used for ROEs to determine pay period schedule type from manual pay period schedules.
				$retval = [
						52 => 10, //Weekly (52/year)
						26 => 20, //Bi-Weekly (26/year)
						24 => 30, //Semi-Monthly (24/year)
						12 => 50, //Monthly (12/year)
						53 => 100, //Weekly (53/year)
						27 => 200, //Bi-Weekly (27/year)
				];
				break;
			case 'annual_pay_periods_maximum_days':
				//Mostly used for ROEs to determine the maximum number of days from the final pay period ending date and the last day for which paid.
				$retval = [
						52 => 7, //Weekly (52/year)
						26 => 14, //Bi-Weekly (26/year)
						24 => 16, //Semi-Monthly (24/year)
						12 => 31, //Monthly (12/year)
						53 => 7, //Weekly (53/year)
						27 => 14, //Bi-Weekly (27/year)
				];

				break;

			case 'start_week_day':
				$retval = [
						0 => TTi18n::gettext( 'Sunday-Saturday' ),
						1 => TTi18n::gettext( 'Monday-Sunday' ),
						2 => TTi18n::gettext( 'Tuesday-Monday' ),
						3 => TTi18n::gettext( 'Wednesday-Tuesday' ),
						4 => TTi18n::gettext( 'Thursday-Wednesday' ),
						5 => TTi18n::gettext( 'Friday-Thursday' ),
						6 => TTi18n::gettext( 'Saturday-Friday' ),
				];
				break;
			case 'shift_assigned_day':
				$retval = [
						10 => TTi18n::gettext( 'Day They Start On' ),
						20 => TTi18n::gettext( 'Day They End On' ),
						30 => TTi18n::gettext( 'Day w/Most Time Worked' ),
						40 => TTi18n::gettext( 'Each Day (Split at Midnight)' ),
				];
				break;
			case 'transaction_date':
				for ( $i = 1; $i <= 31; $i++ ) {
					$retval[$i] = $i;
				}
				break;
			case 'transaction_date_business_day':
				$retval = [
					//Adjust Transaction Date To:
					0 => TTi18n::gettext( 'No' ),
					1 => TTi18n::gettext( 'Yes - Previous Business Day' ),
					2 => TTi18n::gettext( 'Yes - Next Business Day' ),
					3 => TTi18n::gettext( 'Yes - Closest Business Day' ),
				];
				break;
			case 'timesheet_verify_type':
				$retval = [
						10 => TTi18n::gettext( 'Disabled' ),
						20 => TTi18n::gettext( 'Employee Only' ),
						30 => TTi18n::gettext( 'Superior Only' ),
						40 => TTi18n::gettext( 'Employee & Superior' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-type'                 => TTi18n::gettext( 'Type' ),
						'-1020-name'                 => TTi18n::gettext( 'Name' ),
						'-1030-description'          => TTi18n::gettext( 'Description' ),
						'-1040-total_users'          => TTi18n::gettext( 'Employees' ),
						'-1050-start_week_day'       => TTi18n::gettext( 'Overtime Week' ),
						'-1060-shift_assigned_day'   => TTi18n::gettext( 'Assign Shifts To' ),
						'-1070-time_zone'            => TTi18n::gettext( 'Time Zone' ),
						'-1080-new_day_trigger_time' => TTi18n::gettext( 'Minimum Time Off Between Shifts' ),
						'-1090-maximum_shift_time'   => TTi18n::gettext( 'Maximum Shift Time' ),

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
						'total_users',
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
				'id'                    => 'ID',
				'company_id'            => 'Company',
				'type_id'               => 'Type',
				'type'                  => false,
				'start_week_day_id'     => 'StartWeekDay',
				'start_week_day'        => false,
				'shift_assigned_day_id' => 'ShiftAssignedDay',
				'shift_assigned_day'    => false,
				'name'                  => 'Name',
				'description'           => 'Description',
				'start_day_of_week'     => 'StartDayOfWeek',
				'transaction_date'      => 'TransactionDate',

				'primary_day_of_month'               => 'PrimaryDayOfMonth',
				'secondary_day_of_month'             => 'SecondaryDayOfMonth',
				'primary_transaction_day_of_month'   => 'PrimaryTransactionDayOfMonth',
				'secondary_transaction_day_of_month' => 'SecondaryTransactionDayOfMonth',

				'create_days_in_advance'                   => 'CreateDaysInAdvance',
				'auto_close_after_days'                    => 'AutoCloseAfterDays',
				'transaction_date_bd'                      => 'TransactionDateBusinessDay',
				'anchor_date'                              => 'AnchorDate',
				'day_start_time'                           => 'DayStartTime',
				'time_zone'                                => 'TimeZone',
				'day_continuous_time'                      => 'ContinuousTime',
				'new_day_trigger_time'                     => 'NewDayTriggerTime',
				'maximum_shift_time'                       => 'MaximumShiftTime',
				'annual_pay_periods'                       => 'AnnualPayPeriods',
				'timesheet_verify_type_id'                 => 'TimeSheetVerifyType',
				'timesheet_verify_before_end_date'         => 'TimeSheetVerifyBeforeEndDate',
				'timesheet_verify_before_transaction_date' => 'TimeSheetVerifyBeforeTransactionDate',
				'timesheet_verify_agreement'			   => 'TimesheetVerifyAgreement',
				//TimeSheet verification email notices are no longer required, as its handled with exceptions now.
				//'timesheet_verify_notice_before_transaction_date' => 'TimeSheetVerifyNoticeBeforeTransactionDate',
				//'timesheet_verify_notice_email' => 'TimeSheetVerifyNoticeEmail',
				'total_users'                              => false,
				'user'                                     => 'User',
				'deleted'                                  => 'Deleted',
		];

		return $variable_function_map;
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
	 * @return bool|int
	 */
	function getType() {
		//Have to return the KEY because it should always be a drop down box.
		//return Option::getByKey($this->data['status_id'], $this->getOptions('status') );
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
	 * @return bool|int
	 */
	function getStartWeekDay() {
		return $this->getGenericDataValue( 'start_week_day_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartWeekDay( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'start_week_day_id', $value );
	}

	/**
	 * @return bool|int
	 */
	function getShiftAssignedDay() {
		return $this->getGenericDataValue( 'shift_assigned_day_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setShiftAssignedDay( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'shift_assigned_day_id', $value );
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
		$pay_period_schedule_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $pay_period_schedule_id, 'Unique Pay Period Schedule ID: ' . $pay_period_schedule_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $pay_period_schedule_id === false ) {
			return true;
		} else {
			if ( $pay_period_schedule_id == $this->getId() ) {
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
	 * @param bool $raw
	 * @return bool
	 */
	function getStartDayOfWeek( $raw = false ) {
		return $this->getGenericDataValue( 'start_day_of_week' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartDayOfWeek( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'start_day_of_week', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTransactionDate() {
		return $this->getGenericDataValue( 'transaction_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTransactionDate( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'transaction_date', $value );
	}

	/**
	 * @param $val
	 * @return int
	 */
	function convertLastDayOfMonth( $val ) {
		if ( $val == -1 ) {
			return 31;
		}

		return $val;
	}

	/**
	 * @return bool|mixed
	 */
	function getPrimaryDayOfMonth() {
		return $this->getGenericDataValue( 'primary_day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPrimaryDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'primary_day_of_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSecondaryDayOfMonth() {
		return $this->getGenericDataValue( 'secondary_day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSecondaryDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'secondary_day_of_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPrimaryTransactionDayOfMonth() {
		return $this->getGenericDataValue( 'primary_transaction_day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPrimaryTransactionDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'primary_transaction_day_of_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSecondaryTransactionDayOfMonth() {
		return $this->getGenericDataValue( 'secondary_transaction_day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSecondaryTransactionDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'secondary_transaction_day_of_month', $value );
	}

	/**
	 * @return bool|int
	 */
	function getCreateDaysInAdvance() {
		return (int)$this->getGenericDataValue( 'create_days_in_advance' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCreateDaysInAdvance( $value ) {
		$value = (int)$this->Validator->stripNonNumeric( trim( $value ) );

		return $this->setGenericDataValue( 'create_days_in_advance', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAutoCloseAfterDays() {
		return (int)$this->getGenericDataValue( 'auto_close_after_days' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAutoCloseAfterDays( $value ) {
		$value = (int)$value; // Must be able to accept -1 as an input.

		return $this->setGenericDataValue( 'auto_close_after_days', $value );
	}

	/**
	 * @return bool|int
	 */
	function getTransactionDateBusinessDay() {
		return (int)$this->getGenericDataValue( 'transaction_date_bd' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTransactionDateBusinessDay( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'transaction_date_bd', $value );
	}
	/*
		function getTransactionDateBusinessDay() {
			if ( isset($this->data['transaction_date_bd']) ) {
				return $this->fromBool( $this->data['transaction_date_bd'] );
			}

			return FALSE;
		}
		function setTransactionDateBusinessDay($bool) {
			$this->data['transaction_date_bd'] = $this->toBool($bool);

			return TRUE;
		}
	*/
	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getAnchorDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'anchor_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value ); //Need to use TTDate::strtotime so it can return a saved epoch properly when its set and returned without saving inbetween.
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setAnchorDate( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'anchor_date', $value );
	}

	/**
	 * @return bool|int
	 */
	function getDayStartTime() {
		return (int)$this->getGenericDataValue( 'day_start_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDayStartTime( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'day_start_time', $value );
	}

	/**
	 * @return mixed
	 */
	function getTimeZoneOptions() {
		$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */

		return $upf->getOptions( 'time_zone' );
	}

	/**
	 * @return bool|mixed
	 */
	function getTimeZone() {
		return $this->getGenericDataValue( 'time_zone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeZone( $value ) {
		$value = Misc::trimSortPrefix( trim( $value ) );

		return $this->setGenericDataValue( 'time_zone', $value );
	}

	/**
	 * @return bool
	 */
	function setOriginalTimeZone() {
		if ( isset( $this->original_time_zone ) ) {
			return TTDate::setTimeZone( $this->original_time_zone );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function setPayPeriodTimeZone() {
		$this->original_time_zone = TTDate::getTimeZone();

		return TTDate::setTimeZone( $this->getTimeZone() );
	}
	/*
		//Continuous time from the first punch of the day to the last
		//So if continuous time is set to 18hrs, and someone punches in for the first time at
		//11pm. All punches from 11pm + 18hrs are considered for the same day.
		function getContinuousTime() {
			if ( isset($this->data['day_continuous_time']) ) {
				return (int)$this->data['day_continuous_time'];
			}
			return FALSE;
		}
		function setContinuousTime($int) {
			$int = (int)$int;

			if	(	$this->Validator->isNumeric(		'continuous_time',
														$int,
														TTi18n::gettext('Incorrect continuous time')) ) {
				$this->setGenericDataValue( 'day_continuous_time', $int );

				return TRUE;
			}

			return FALSE;
		}
	*/

	/**
	 * Instead of daily continuous time, use minimum time-off between shifts that triggers a new day to start.
	 * @return bool|int
	 */
	function getNewDayTriggerTime() {
		return (int)$this->getGenericDataValue( 'new_day_trigger_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNewDayTriggerTime( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'new_day_trigger_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getMaximumShiftTime() {
		return $this->getGenericDataValue( 'maximum_shift_time' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMaximumShiftTime( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'maximum_shift_time', $value );
	}

	/**
	 * @return bool|int
	 */
	function getAnnualPayPeriods() {
		return (int)$this->getGenericDataValue( 'annual_pay_periods' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAnnualPayPeriods( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'annual_pay_periods', $value );
	}

	/**
	 * @return bool|int
	 */
	function getTimeSheetVerifyType() {
		return $this->getGenericDataValue( 'timesheet_verify_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeSheetVerifyType( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'timesheet_verify_type_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getTimeSheetVerifyBeforeEndDate() {
		$value = $this->getGenericDataValue( 'timesheet_verify_before_end_date' );
		if ( $value !== false ) {
			return TTMath::removeTrailingZeros( round( TTDate::getDays( (int)$value ), 3 ), 0 );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeSheetVerifyBeforeEndDate( $value ) {
		$value = (float)$value; // Do not cast to INT, need to support partial days.

		if ( abs( $value ) < 86400 ) { //When handling the audit log, seconds can be passed into here and days. So if its less than 1 day in seconds assume, days and convert to seconds, otherwise don't convert.
			$value = ( $value * 86400 ); //Convert to seconds to support partial days. Do not cast to INT!
		}

		return $this->setGenericDataValue( 'timesheet_verify_before_end_date', $value );
	}

	/**
	 * @return bool|string
	 */
	function getTimeSheetVerifyBeforeTransactionDate() {
		$value = $this->getGenericDataValue( 'timesheet_verify_before_transaction_date' );
		if ( $value !== false ) {
			return TTMath::removeTrailingZeros( round( TTDate::getDays( (int)$value ), 3 ), 0 );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeSheetVerifyBeforeTransactionDate( $value ) {
		$value = (float)$value; // Do not cast to INT, need to support partial days.

		if ( abs( $value ) < 86400 ) { //When handling the audit log, seconds can be passed into here and days. So if its less than 1 day in seconds assume, days and convert to seconds, otherwise don't convert.
			$value = ( $value * 86400 ); //Convert to seconds to support partial days. Do not cast to INT!
		}

		return $this->setGenericDataValue( 'timesheet_verify_before_transaction_date', $value );
	}

	/**
	 * Notices are no longer required with TimeSheet not verified exception.
	 * @return bool|int
	 */
	function getTimeSheetVerifyNoticeBeforeTransactionDate() {
		return (int)$this->getGenericDataValue( 'timesheet_verify_notice_before_transaction_date' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeSheetVerifyNoticeBeforeTransactionDate( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'timesheet_verify_notice_before_transaction_date', $value );
	}

	/**
	 * @return bool
	 */
	function getTimeSheetVerifyNoticeEmail() {
		return $this->fromBool( $this->getGenericDataValue( 'timesheet_verify_notice_email' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeSheetVerifyNoticeEmail( $value ) {
		return $this->setGenericDataValue( 'timesheet_verify_notice_email', $this->toBool( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getTimeSheetVerifyAgreement() {
		return $this->getGenericDataValue( 'timesheet_verify_agreement' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeSheetVerifyAgreement( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'timesheet_verify_agreement', $value );
	}

	/**
	 * @return array|bool
	 */
	function getUser() {
		$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' ); /** @var PayPeriodScheduleUserListFactory $ppsulf */
		$ppsulf->getByPayPeriodScheduleId( $this->getId() );
		$user_list = [];
		foreach ( $ppsulf as $pay_period_schedule ) {
			$user_list[] = $pay_period_schedule->getUser();
		}

		if ( empty( $user_list ) == false ) {
			return $user_list;
		}

		return false;
	}

	/**
	 * @param string|string[] $ids UUID
	 * @return bool
	 */
	function setUser( $ids ) {
		if ( !is_array( $ids ) ) {
			$ids = [ $ids ];
		}

		if ( is_array( $ids ) ) {
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' ); /** @var PayPeriodScheduleUserListFactory $ppsulf */
				$ppsulf->getByPayPeriodScheduleId( $this->getId() );

				$user_ids = [];
				foreach ( $ppsulf as $pay_period_schedule ) {
					$user_id = $pay_period_schedule->getUser();
					Debug::text( 'Schedule ID: ' . $pay_period_schedule->getPayPeriodSchedule() . ' User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $user_id, $ids ) ) {
						Debug::text( 'Deleting User: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
						$pay_period_schedule->Delete();
					} else {
						//Save user ID's that need to be updated.
						Debug::text( 'NOT Deleting User: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );
						$user_ids[] = $user_id;
					}
				}
			}

			//Insert new mappings.
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */

			foreach ( $ids as $id ) {
				if ( $id != '' && isset( $user_ids ) && !in_array( $id, $user_ids ) ) {
					$ppsuf = TTnew( 'PayPeriodScheduleUserFactory' ); /** @var PayPeriodScheduleUserFactory $ppsuf */
					$ppsuf->setPayPeriodSchedule( $this->getId() );
					$ppsuf->setUser( $id );

					$ulf->getById( $id );
					if ( $ulf->getRecordCount() > 0 ) {
						$obj = $ulf->getCurrent();

						if ( $this->Validator->isTrue( 'user',
													   $ppsuf->isValid(),
													   TTi18n::gettext( 'Selected Employee is already assigned to another Pay Period' ) . ' (' . $obj->getFullName() . ')' )
						) {
							$ppsuf->save();
						}
					}
				}
			}

			return true;
		}

		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return int
	 */
	function getTransactionBusinessDay( $epoch ) {
		Debug::Text( 'Epoch: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		$holiday_epochs = [];

		//Need to normalize the date range so its not different for every $epoch value as we get the next pay periods in a loop.
		$start_date = ( TTDate::getBeginYearEpoch( $epoch ) - ( 86400 * 14 ) );
		$end_date = ( TTDate::getEndYearEpoch( ( TTDate::getEndYearEpoch( $epoch ) + 86400 ) ) + ( 86400 * 14 ) ); //Look through to the end of the NEXT year + 14 days in advance to make better use of caching, especially for calculating remaining pay periods in the year, which always extends a few days into the next year.

		$cache_id = md5( $this->getId() . $start_date . $end_date ); //Don't cache user_ids, as its only in memory caching for now and we use the current pay_period_schedule_id to prevent conflicts.
		if ( !isset( $this->holiday_epochs[$cache_id] ) ) {
			$user_ids = $this->getUser();
			if ( is_array( $user_ids ) && count( $user_ids ) > 0 ) {
				$hlf = TTnew( 'HolidayListFactory' ); /** @var HolidayListFactory $hlf */
				$hlf->getByPolicyGroupUserIdAndStartDateAndEndDate( $user_ids, $start_date, $end_date );
				if ( $hlf->getRecordCount() > 0 ) {
					foreach ( $hlf as $h_obj ) { /** @var HolidayFactory $h_obj */
						Debug::Text( 'Found Holiday Epoch: ' . TTDate::getDate( 'DATE+TIME', $h_obj->getDateStamp() ) . ' Name: ' . $h_obj->getName(), __FILE__, __LINE__, __METHOD__, 10 );
						$holiday_epochs[] = $h_obj->getDateStamp();
					}
					//Debug::Arr($holiday_epochs, 'Holiday Epochs: ', __FILE__, __LINE__, __METHOD__, 10);
				}

				$this->holiday_epochs[$cache_id] = $holiday_epochs; //Save cache, just in memory as it would be pretty complicated to clear it in so many different places.
			}
		} else {
			$holiday_epochs = $this->holiday_epochs[$cache_id];
		}

		$epoch = TTDate::getNearestWeekDay( $epoch, $this->getTransactionDateBusinessDay(), $holiday_epochs );

		return $epoch;
	}


	/**
	 * @param $company_id
	 * @param $pay_period_schedule_ids
	 * @param $end_date_offset
	 * @param null $starting_pay_period_obj
	 * @return Generator
	 */
	static function getNextPayPeriods( $company_id, $pay_period_schedule_ids, $end_date_offset, $starting_pay_period_obj = null ) {
		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getByCompanyId( $company_id );
		foreach ( $ppslf as $pay_period_schedule_obj ) { /** @var PayPeriodScheduleFactory $pay_period_schedule_obj */
			//If filtering by pay period schedules, only create dynamic pay periods for those pay period schedules.
			if ( !empty( $pay_period_schedule_ids ) && !in_array( $pay_period_schedule_obj->getId(), $pay_period_schedule_ids, true ) ) {
				continue;
			}

			if ( $starting_pay_period_obj === null ) {
				//No starting pay period provided, find last pay period.
				$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
				$pplf->getByPayPeriodScheduleId( $pay_period_schedule_obj->getId(), 1, null, null, [ 'start_date' => 'desc' ] );

				if ( $pplf->getRecordCount() > 0 ) {
					$previous_pay_period = $pplf->getCurrent();
				} else {
					$previous_pay_period = null;
				}
			} else {
				//Starting pay period was supplied. Build future pay periods after it.
				$previous_pay_period = $starting_pay_period_obj;
			}

			$repeat_pay_period_creation = true;

			$i = 1;
			while ( $repeat_pay_period_creation === true ) {
				//Repeat function until returns false. (No more pay periods to create)
				$next_pay_period_obj = $pay_period_schedule_obj->createNextPayPeriod( null, $end_date_offset, false, true, $previous_pay_period );

				//Return result can be a bool or the next dynamically generated pay period. Break loop when data is no longer returned.
				if ( is_bool( $next_pay_period_obj ) ) {
					break;
				} else {
					$next_pay_period_obj->setId( TTUUID::getNotExistID( $i ) );
					$next_pay_period_obj->setGenericDataValue( 'period_id', 100 ); //100=Future
					$next_pay_period_obj->setGenericDataValue( 'type_id', $pay_period_schedule_obj->getType() );

					$previous_pay_period = $next_pay_period_obj;

					$tmp_pay_period = $next_pay_period_obj->getObjectAsArray();
					$tmp_pay_period['pay_period_schedule'] = $pay_period_schedule_obj->getName();
					$tmp_pay_period['transaction_date_epoch'] = $next_pay_period_obj->getTransactionDate(); // Creating transaction_date_epoch for sorting as epoch instead of date string.

					$i++;

					yield $tmp_pay_period;
				}
			}
		}
	}


	/**
	 * @param int $end_date EPOCH
	 * @param object $previous_dynamic_pay_period
	 * @return bool
	 */
	function getNextPayPeriod( $end_date = null, $previous_dynamic_pay_period = null ) {
		if ( !$this->Validator->isValid() ) {
			return false;
		}

		//Manual Pay Period Schedule, skip repeating...
		if ( $this->getType() == 5 ) {
			return false;
		}

		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */

		//Debug::text('PP Schedule ID: '. $this->getId(), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('PP Schedule Name: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);
		Debug::text( 'PP Schedule Type (' . $this->getType() . '): ' . Option::getByKey( $this->getType(), $this->getOptions( 'type' ) ), __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::text('Anchor Date: '. $this->getAnchorDate() ." - ". TTDate::getDate('DATE+TIME', $this->getAnchorDate() ), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Primary Date: '. $this->getPrimaryDate() ." - ". TTDate::getDate('DATE+TIME', $this->getPrimaryDate() ), __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Secondary Date: '. $this->getSecondaryDate() ." - ". TTDate::getDate('DATE+TIME', $this->getPrimaryDate() ), __FILE__, __LINE__, __METHOD__, 10);

		if ( $end_date != '' && $end_date != 0 ) {
			Debug::text( 'End Date is set: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );
			$last_pay_period_end_date = $end_date;
		} else {
			Debug::text( 'Checking for Previous pay periods...', __FILE__, __LINE__, __METHOD__, 10 );
			//Get the last pay period schedule in the database if a previous_dynamic_pay_period was not provided.
			if ( $previous_dynamic_pay_period === null ) {
				$pplf->getByPayPeriodScheduleId( $this->getId(), null, null, null, [ 'start_date' => 'desc' ] );
				$last_pay_period = $pplf->getCurrent();
			} else {
				//Dynamically generating future pay periods requires the previous pay period to build on.
				//Because we do not save these dynamic pay periods we must pass the previous one in.
				$last_pay_period = $previous_dynamic_pay_period;
			}
			if ( $previous_dynamic_pay_period === null && $last_pay_period->isNew() ) {

				Debug::text( 'No Previous pay periods...', __FILE__, __LINE__, __METHOD__, 10 );

				//Do this so a rollover doesn't happen while we're calculating.
				//$last_pay_period_end_date = TTDate::getTime();
				//This causes the pay period schedule to jump ahead one month. So set this to be beginning of the month.
				$last_pay_period_end_date = TTDate::getBeginMonthEpoch();
			} else {
				Debug::text( 'Previous pay periods found... ID: ' . $last_pay_period->getId(), __FILE__, __LINE__, __METHOD__, 10 );
				$last_pay_period_end_date = $last_pay_period->getEndDate();
			}
			unset( $last_pay_period, $pplf );
		}
		Debug::text( 'aLast Pay Period End Date: ' . TTDate::getDate( 'DATE+TIME', $last_pay_period_end_date ) . ' (' . $last_pay_period_end_date . ')', __FILE__, __LINE__, __METHOD__, 10 );

		//FIXME: This breaks having pay periods with different daily start times.
		//However, without it, I think DST breaks pay periods.
		//$last_pay_period_end_date = TTDate::getEndDayEpoch( $last_pay_period_end_date + 1 ) - 86400;
		$last_pay_period_end_date = TTDate::getEndDayEpoch( $last_pay_period_end_date - 43200 );
		Debug::text( 'bLast Pay Period End Date: ' . TTDate::getDate( 'DATE+TIME', $last_pay_period_end_date ) . ' (' . $last_pay_period_end_date . ')', __FILE__, __LINE__, __METHOD__, 10 );

		/*
		//This function isn't support currently, so skip it.
		if ( $this->getDayStartTime() != 0 ) {
			Debug::text('Daily Start Time is set, adjusting Last Pay Period End Date by: '. TTDate::getHours( $this->getDayStartTime() ), __FILE__, __LINE__, __METHOD__, 10);
			//Next adjust last_pay_period_end_date (which becomes the start date) to DayStartTime because then there could be a gap if they
			//change this mid-schedule. The End Date will take care of it after the first pay period.
			$last_pay_period_end_date = TTDate::getTimeLockedDate( TTDate::getBeginDayEpoch($last_pay_period_end_date) + $this->getDayStartTime(), $last_pay_period_end_date);
			Debug::text('cLast Pay Period End Date: '. TTDate::getDate('DATE+TIME', $last_pay_period_end_date) .' ('.$last_pay_period_end_date .')', __FILE__, __LINE__, __METHOD__, 10);
		}
		*/

		$insert_pay_period = 1; //deprecate primary pay periods.
		switch ( $this->getType() ) {
			case 10: //Weekly
			case 100: //Weekly (53)
			case 20: //Bi-Weekly
			case 200: //Bi-Weekly (27)
				$last_pay_period_end_day_of_week = TTDate::getDayOfWeek( $last_pay_period_end_date );
				Debug::text( 'Last Pay Period End Day Of Week: ' . $last_pay_period_end_day_of_week . ' Start Day Of Week: ' . $this->getStartDayOfWeek(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $last_pay_period_end_day_of_week != $this->getStartDayOfWeek() ) {
					Debug::text( 'zTmp Pay Period End Date: ' . 'next ' . TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek() ), __FILE__, __LINE__, __METHOD__, 10 );
					//$tmp_pay_period_end_date = strtotime('next '. TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek() ), $last_pay_period_end_date )-1;
					$tmp_pay_period_end_date = strtotime( 'next ' . TTDate::getDayOfWeekByInt( $this->getStartDayOfWeek(), false ), $last_pay_period_end_date );

					//strtotime doesn't keep time when using "next", it resets it to midnight on the day, so we need to adjust for that.
					$tmp_pay_period_end_date = ( TTDate::getTimeLockedDate( ( TTDate::getBeginDayEpoch( $tmp_pay_period_end_date ) + $this->getDayStartTime() ), $tmp_pay_period_end_date ) - 1 );
				} else {
					$tmp_pay_period_end_date = $last_pay_period_end_date;

					//This should fix a bug where if they are creating a new pay period schedule
					//starting on Monday with the anchor date of 01-Jul-08, it would start on 01-Jul-08 (Tue)
					//rather moving back to the Monday.
					if ( TTDate::getDayOfMonth( $tmp_pay_period_end_date ) != TTDate::getDayOfMonth( $tmp_pay_period_end_date + 1 ) ) {
						Debug::text( 'Right on day boundary, minus an additional second to account for difference...', __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_pay_period_end_date--;
					}
				}
				Debug::text( 'aTmp Pay Period End Date: ' . TTDate::getDate( 'DATE+TIME', $tmp_pay_period_end_date ) . ' (' . $tmp_pay_period_end_date . ')', __FILE__, __LINE__, __METHOD__, 10 );

				$start_date = ( $tmp_pay_period_end_date + 1 );

				if ( $this->getType() == 10 || $this->getType() == 100 ) {                                   //Weekly
					$tmp_pay_period_end_date = ( TTDate::getMiddleDayEpoch( $start_date ) + ( 86400 * 7 ) ); //Add one week
				} else if ( $this->getType() == 20 || $this->getType() == 200 ) {                             //Bi-Weekly
					$tmp_pay_period_end_date = ( TTDate::getMiddleDayEpoch( $start_date ) + ( 86400 * 14 ) ); //Add two weeks
				}

				//Use Begin Day Epoch to nullify DST issues.
				$end_date = ( TTDate::getBeginDayEpoch( $tmp_pay_period_end_date ) - 1 );
				$transaction_date = TTDate::getMiddleDayEpoch( ( TTDate::getMiddleDayEpoch( $end_date ) + ( $this->getTransactionDate() * 86400 ) ) );

				break;
			case 30: //Semi-monthly
				$tmp_last_pay_period_end_day_of_month = TTDate::getDayOfMonth( $last_pay_period_end_date + 1 );
				Debug::text( 'bLast Pay Period End Day Of Month: ' . $tmp_last_pay_period_end_day_of_month, __FILE__, __LINE__, __METHOD__, 10 );

				if ( $tmp_last_pay_period_end_day_of_month == $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) {
					$insert_pay_period = 1;
					$primary = true;
				} else if ( $tmp_last_pay_period_end_day_of_month == $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) {
					$insert_pay_period = 2;
					$primary = false;
				} else {
					Debug::text( 'Finding if Primary or Secondary is closest...', __FILE__, __LINE__, __METHOD__, 10 );

					$primary_date_offset = ( TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, null, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) - $last_pay_period_end_date );
					$secondary_date_offset = ( TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, null, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) - $last_pay_period_end_date );
					Debug::text( 'Primary Date Offset: ' . TTDate::getDays( $primary_date_offset ) . ' Secondary Date Offset: ' . TTDate::getDays( $secondary_date_offset ), __FILE__, __LINE__, __METHOD__, 10 );

					if ( $primary_date_offset <= $secondary_date_offset ) {
						$insert_pay_period = 1;
						$primary = true;

						$last_pay_period_end_date = TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, null, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) );
					} else {
						$insert_pay_period = 2;
						$primary = false;

						$last_pay_period_end_date = TTDate::getDateOfNextDayOfMonth( $last_pay_period_end_date, null, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) );
					}
					$last_pay_period_end_date = ( TTDate::getBeginDayEpoch( $last_pay_period_end_date ) - 1 );
				}
				unset( $tmp_last_pay_period_end_day_of_month );
				Debug::text( 'cLast Pay Period End Date: ' . TTDate::getDate( 'DATE+TIME', $last_pay_period_end_date ) . ' (' . $last_pay_period_end_date . ') Primary: ' . (int)$primary, __FILE__, __LINE__, __METHOD__, 10 );

				$start_date = ( $last_pay_period_end_date + 1 );

				if ( $primary == true ) {
					$end_date = ( TTDate::getBeginDayEpoch( TTDate::getDateOfNextDayOfMonth( $start_date, null, $this->convertLastDayOfMonth( $this->getSecondaryDayOfMonth() ) ) ) - 1 );
					$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( TTDate::getMiddleDayEpoch( $end_date ), null, $this->convertLastDayOfMonth( $this->getPrimaryTransactionDayOfMonth() ) ) );
				} else {
					$end_date = ( TTDate::getBeginDayEpoch( TTDate::getDateOfNextDayOfMonth( $start_date, null, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) ) ) - 1 );
					$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( TTDate::getMiddleDayEpoch( $end_date ), null, $this->convertLastDayOfMonth( $this->getSecondaryTransactionDayOfMonth() ) ) );
				}

				break;
			case 50: //Monthly
				$start_date = ( $last_pay_period_end_date + 1 );

				//Use Begin Day Epoch to nullify DST issues.
				$end_date = TTDate::getDateOfNextDayOfMonth( TTDate::getBeginDayEpoch( $start_date + ( 86400 + 3600 ) ), null, $this->convertLastDayOfMonth( $this->getPrimaryDayOfMonth() ) );
				$end_date = ( TTDate::getBeginDayEpoch( TTDate::getBeginMinuteEpoch( $end_date ) ) - 1 );

				$transaction_date = TTDate::getMiddleDayEpoch( TTDate::getDateOfNextDayOfMonth( $end_date, null, $this->convertLastDayOfMonth( $this->getPrimaryTransactionDayOfMonth() ) ) );

				break;
		}

		if ( $this->getDayStartTime() != 0 ) {
			Debug::text( 'Daily Start Time is set, adjusting End Date by: ' . TTDate::getHours( $this->getDayStartTime() ) . ' Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ), __FILE__, __LINE__, __METHOD__, 10 );

			//We already account for DayStartTime in weekly/bi-weekly start_date cases above, so skip applying it again here.
			if ( $this->getType() != 10 && $this->getType() != 20 ) {
				$start_date = ( $start_date + $this->getDayStartTime() );
			}
			$end_date = ( $end_date + $this->getDayStartTime() );

			//Need to do this, otherwise transaction date could be earlier then end date.
			$transaction_date = ( $transaction_date + $this->getDayStartTime() );
		}

		Debug::text( 'aStart Date(' . $start_date . '): ' . TTDate::getDate( 'DATE+TIME', $start_date ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'aEnd Date(' . $end_date . '): ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'aPay Date(' . $transaction_date . '): ' . TTDate::getDate( 'DATE+TIME', $transaction_date ), __FILE__, __LINE__, __METHOD__, 10 );


		//Handle last day of the month flag for primary and secondary dates here
		if ( ( $this->getType() == 30
						&& (
								( $insert_pay_period == 1
										&& ( $this->getPrimaryDayOfMonth() == 31
												|| $this->getPrimaryDayOfMonth() == -1 )
								)
								|| ( $insert_pay_period == 2
										&& ( $this->getSecondaryDayOfMonth() == 31
												|| $this->getSecondaryDayOfMonth() == -1 )
								)
						)
				)
				||
				(
						$this->getType() == 50 && ( $this->getPrimaryDayOfMonth() == 31 || $this->getPrimaryDayOfMonth() == -1 )
				) ) {

			Debug::text( 'Last day of the month set for start date: ', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->getDayStartTime() > 0 ) {
				//Minus one day, THEN add daily start time, otherwise it will go past the month boundary
				$end_date = ( ( TTDate::getEndMonthEpoch( $end_date ) - 86400 ) + $this->getDayStartTime() ); //End month epoch is 23:59:59, so don't minus one.
			} else {
				$end_date = ( TTDate::getEndMonthEpoch( $end_date ) + $this->getDayStartTime() ); //End month epoch is 23:59:59, so don't minus one.
			}
		}

		//Handle "last day of the month" for transaction dates.
		if ( $this->getPrimaryDayOfMonth() == 31 || $this->getPrimaryDayOfMonth() == -1 ) {
			//Debug::text('LDOM set for Primary: ', __FILE__, __LINE__, __METHOD__, 10);
			$transaction_date = TTDate::getEndMonthEpoch( $transaction_date );
		}

		//Handle "always business day" flag for transaction dates here.
		if ( $this->getTransactionDateBusinessDay() == true ) {
			$transaction_date = $this->getTransactionBusinessDay( $transaction_date );
		}

		if ( $transaction_date < $end_date ) {
			$transaction_date = $end_date;
		}

		Debug::text( 'Start Date: ' . TTDate::getDate( 'DATE+TIME', $start_date ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );
		Debug::text( 'Pay Date: ' . TTDate::getDate( 'DATE+TIME', $transaction_date ), __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::text("<br><br>\n\n", __FILE__, __LINE__, __METHOD__, 10);

		$this->next_start_date = $start_date;
		$this->next_end_date = $end_date;
		$this->next_transaction_date = $transaction_date;

		//Its a primary pay period
		if ( $insert_pay_period == 1 ) {
			$this->next_primary = true;
		} else {
			$this->next_primary = false;
		}

		return true;
	}

	/**
	 * @param $pp_obj
	 * @param null $start_date
	 * @param null $end_date
	 * @return int
	 */
	function countFuturePayPeriods( $pp_obj, $start_date = null, $end_date = null ) {
		if ( $start_date == null ) {
			$start_date = TTDate::getTime();
		}

		if ( $end_date == null ) {
			$end_date = TTDate::getEndYearEpoch();
		}

		Debug::text( 'Counting Future Pay Periods between: Start: '. TTDate::getDate( 'DATE+TIME', $start_date ) .' End: '. TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

		//How many days remain till the end date (generally end of year). Used to calculate how far into the future to check pay periods for.
		$days_left = ceil( TTDate::getDayDifference( TTDate::getTime(), $end_date ) ); //Must go from current date rather than start_date, as getNextPayPeriods() uses an offset rather that start date.
		$offset = ( $days_left * 86400 );

		//Get number of pay periods projected until end date (generally till end of year) based on the first pay period.
		$total_pay_periods = 1; //Total is already 1 as it includes the first pay period which already exists.
		foreach ( PayPeriodScheduleFactory::getNextPayPeriods( $this->getCompany(), [ $this->getId() ], $offset, $pp_obj ) as $pay_period ) { /** @var PayPeriodFactory $pay_period */
			if ( TTDate::getMiddleDayEpoch( $pay_period['transaction_date_epoch'] ) <= TTDate::getMiddleDayEpoch( $end_date ) ) { //Must filter based on $end_date rather than $offset, otherwise it will exit early.
				Debug::text( '   Pay Period: Start: '. $pay_period['start_date'] .' End: '. $pay_period['end_date'] .' Transaction: '. $pay_period['transaction_date'], __FILE__, __LINE__, __METHOD__, 10 );
				$total_pay_periods++;
			} else {
				Debug::text( '   Dynamically generated pay period transaction date outside of end date, skipping... Transaction: '. $pay_period['transaction_date'], __FILE__, __LINE__, __METHOD__, 10 );
				break;
			}
		}

		return $total_pay_periods;
	}

	/**
	 * Add pay periods for a single company from job queue.
	 * @param $company_id
	 * @return bool
	 * @throws DBError
	 * @throws GeneralError
	 * @throws ReflectionException
	 */
	static function createPayPeriodsForJobQueue( $company_id ) {
		if ( TTUUID::isUUID( $company_id ) ) {
			$current_epoch = TTDate::getTime();

			//Get all pay period schedules.
			$ppslf = new PayPeriodScheduleListFactory();
			$ppslf->getByCompanyId( $company_id );
			foreach ( $ppslf as $pay_period_schedule_obj ) { /** @var PayPeriodScheduleFactory $pay_period_schedule_obj */
				Debug::text( '  Adding Pay Periods for Pay Period Schedule: ' . $pay_period_schedule_obj->getId() .' Company ID: '. $company_id, __FILE__, __LINE__, __METHOD__, 10 );

				$end_date = null;

				//Create pay periods X days in the future as set in Pay Period Schedule by the user.
				$offset = ( $pay_period_schedule_obj->getCreateDaysInAdvance() * 86400 );

				$i = 0;
				$max = 53; //Never create more than this number of pay periods in a row.
				$repeat_pay_period_creation = true;
				while ( $i <= $max && $repeat_pay_period_creation === true ) {
					//Repeat function until returns false. (No more pay periods to create)
					$repeat_pay_period_creation = $pay_period_schedule_obj->createNextPayPeriod( $end_date, $offset );

					$i++;
				}

				if ( PRODUCTION == true && DEMO_MODE == false ) {
					$pay_period_schedule_obj->forceClosePreviousPayPeriods( $current_epoch );
				}

				unset( $pay_period_schedule_obj );
			}
		}

		return true;
	}

	/**
	 * @param null $end_date
	 * @param null $offset
	 * @param bool $enable_import_data
	 * @param bool $enable_dynamic_pay_period
	 * @param PayPeriodFactory $previous_pay_period_obj
	 * @return bool|object|PayPeriodFactory
	 * @throws DBError
	 * @throws GeneralError
	 * @throws ReflectionException
	 */
	function createNextPayPeriod( $end_date = null, $offset = null, $enable_import_data = true, $enable_dynamic_pay_period = false, $previous_pay_period_obj = null ) {
		if ( $end_date == null || $end_date == '' ) {
			$end_date = null;
		}

		if ( $offset == null || $offset == '' ) {
			$offset = 86400; //24hrs
		}

		if ( $this->getType() == 5 ) {
			return false;
		}

		$retval = false;

		//If not a dynamic pay period. Need to run in a transaction so we don't get duplicate pay periods created when using SQL replication due to the "getNextPayPeriod()" check below that could use a read-only replica.
		if ( $enable_dynamic_pay_period === false ) {
			$this->acquireAdvisoryLock( 'createNextPayPeriod:'. $this->getId(), false, 3, 0.5 ); //Throws exception on failure.
			$this->StartTransaction();
		}

		Debug::text( 'Current TimeZone: ' . TTDate::getTimeZone(), __FILE__, __LINE__, __METHOD__, 10 );
		//Handle timezones in this function rather then getNextPayPeriod()
		//Because if we set the timezone back to the original in that function, it
		//gets written to the database in the "original" timezone, not the proper timezone.
		$this->setPayPeriodTimeZone();
		Debug::text( 'Pay Period TimeZone: ' . TTDate::getTimeZone(), __FILE__, __LINE__, __METHOD__, 10 );

		Debug::text( 'End Date (' . $end_date . '): ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

		$this->getNextPayPeriod( $end_date, $previous_pay_period_obj );

		Debug::text( 'Next pay period starts: ' . TTDate::getDate( 'DATE+TIME', $this->getNextStartDate() ), __FILE__, __LINE__, __METHOD__, 10 );

		//If the start date is within 24hrs of now, insert the next pay period.
		if ( $this->getNextStartDate() <= ( TTDate::getTime() + $offset ) ) {
			Debug::text( 'Insert new pay period. Start Date: ' . $this->getNextStartDate() . ' End Date: ' . $this->getNextEndDate(), __FILE__, __LINE__, __METHOD__, 10 );
			$ppf = TTnew( 'PayPeriodFactory' ); /** @var PayPeriodFactory $ppf */
			$ppf->setCompany( $this->getCompany() );
			$ppf->setPayPeriodSchedule( $this->getId() );
			$ppf->setStartDate( $this->getNextStartDate() );
			$ppf->setEndDate( $this->getNextEndDate() );
			$ppf->setTransactionDate( $this->getNextTransactionDate() );
			$ppf->setPrimary( $this->getNextPrimary() );
			$ppf->setEnableImportOrphanedData( $enable_import_data ); //Import orphaned punches when creating new pay periods.

			if ( $enable_dynamic_pay_period === true ) {
				$ppf->setStatus( 50 );
				$ppf->setId( TTUUID::getZeroID() );
			} else {
				$ppf->setStatus( 10 );
			}

			//If generating future pay periods return the data without saving the pay period to the database.
			if ( $enable_dynamic_pay_period === true ) {
				$this->setOriginalTimeZone();

				return $ppf;
			} else {
				if ( $ppf->isValid() ) {
					$new_pay_period_id = $ppf->Save();
					Debug::text( 'New Pay Period ID: ' . $new_pay_period_id, __FILE__, __LINE__, __METHOD__, 10 );

					if ( $new_pay_period_id != '' ) {
						$retval = true;
					} else {
						Debug::text( 'aSaving Pay Period Failed!', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::text( 'bSaving Pay Period Failed!', __FILE__, __LINE__, __METHOD__, 10 );
				}

			}
		} else {
			Debug::text( '***NOT inserting or changing status of new pay period yet, not within offset.', __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( $enable_dynamic_pay_period === false ) {
			$this->CommitTransaction();
			$this->releaseAdvisoryLock( 'createNextPayPeriod:'. $this->getId() ); //Release after the commit to avoid concurrent update failures in higher isolation levels.
		}

		$this->setOriginalTimeZone();

		return $retval;
	}


	/**
	 * @return bool
	 */
	function getNextStartDate() {
		if ( isset( $this->next_start_date ) ) {
			return $this->next_start_date;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getNextEndDate() {
		if ( isset( $this->next_end_date ) ) {
			return $this->next_end_date;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getNextTransactionDate() {
		if ( isset( $this->next_transaction_date ) ) {
			return $this->next_transaction_date;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getNextAdvanceEndDate() {
		if ( isset( $this->next_advance_end_date ) ) {
			return $this->next_advance_end_date;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getNextAdvanceTransactionDate() {
		if ( isset( $this->next_advance_transaction_date ) ) {
			return $this->next_advance_transaction_date;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getNextPrimary() {
		if ( isset( $this->next_primary ) ) {
			return $this->next_primary;
		}

		return false;
	}


	/**
	 * Get the annual pay periods adjusted for the employees hire date.
	 * This is *required* in CompanyDeduction FormulaType=20 situations.
	 * @param int $epoch     EPOCH Must be a TRANSACTION DATE of a Pay Period
	 * @param int $hire_date EPOCH Hire date of employee
	 * @return bool|float|int|mixed
	 */
	function getHireAdjustedAnnualPayPeriods( $epoch = null, $hire_date = null ) {
		//According to the CRA computer formula document, this should always be the total pay periods in the year (ie: 26), and only the divisor is changed to always start at 1 from the hire date.
		//  This avoids the case of someone getting hired in the last month or two of the year and not having any taxes deducted because S=(4/1), and the annual income is showing less than the minimum claim amount.
		//  Whenever its a partial year due to hire date, always assume they are getting paid that amount for the full year.
		//  Updated unit tests such as: testHireAdjustedPayPeriodNumberA(), testHireAdjustedPayPeriodNumberB()
		//  FIXME: When a user is re-hired mid-year, so they worked the first 5 months, laid off for 2 months, worked the remaining 5 months, and have the hire date set to sometime in August,
		//         it will calculate an annualizing factor that is too high and therefore deduct too much taxes.
		//         Whenever the user has a gap in earnings like this, it complicates things for this calculation formula. Not sure the best approach to fix this though.
		$retval = $this->getAnnualPayPeriods();

//		$hired_pay_period_number = $this->getHiredPayPeriodNumberAdjustment( $epoch, $hire_date );
//		$retval = ( $this->getAnnualPayPeriods() - $hired_pay_period_number );
//		if ( $retval < 1 ) {
//			$retval = 1;
//		}

		return $retval;
	}

	/**
	 * Get the current pay period number adjusted for the employees hire date.
	 * This is *required* in CompanyDeduction FormulaType=20 situations.
	 * @param int $epoch          EPOCH Must be a TRANSACTION DATE of a Pay Period
	 * @param int $end_date_epoch EPOCH Must be a END DATE of a Pay Period
	 * @param int $hire_date      EPOCH Hire date of employee
	 * @return bool|float|int|mixed
	 */
	function getHireAdjustedCurrentPayPeriodNumber( $epoch = null, $end_date_epoch = null, $hire_date = null ) {
		$hired_pay_period_number = $this->getHiredPayPeriodNumberAdjustment( $epoch, $hire_date );
		$current_pay_period_number = $this->getCurrentPayPeriodNumber( $epoch, $end_date_epoch );
		$retval = ( $current_pay_period_number - $hired_pay_period_number );
		if ( $retval < 1 ) {
			$retval = 1;
		}

		return $retval;
	}

	/**
	 * Get the pay period number that the employee was hired on. This starts at 0 so it can be added to getHireAdjustedCurrentPayPeriodNumber() to get the total annual pay periods.
	 * This is *required* in CompanyDeduction FormulaType=20 situations.
	 * @param int $epoch     EPOCH Must be a TRANSACTION DATE of a Pay Period
	 * @param int $hire_date EPOCH Hire date of employee
	 * @return bool|float|int|mixed
	 */
	function getHiredPayPeriodNumberAdjustment( $epoch = null, $hire_date = null ) {
		//Determine if they were hired in this year as we will need to adjust the pay period counter so its specific to this employee.
		// For example, it might be 12 of 24 pay periods in the year, but if the employee was hired near the beginning of the year, for them it might be 1 of 20.
		// This is required to properly calculate FormulaType=20 situations, specifically for new hires.
		$retval = 0;
		if ( $hire_date != '' && $hire_date > TTDate::getBeginYearEpoch( $epoch ) ) { //Use ">", because we only care if they will only work a partial year, not exactly one year.
			$retval = $this->getCurrentPayPeriodNumber( $epoch, $hire_date );
			Debug::text( '  Hire Date: ' . TTDate::getDate( 'DATE', $hire_date ) . ' Hired Pay Period Number: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
		} else {
			Debug::text( '  Hire Date is not in this year, returning: ' . $retval . ' Epoch: ' . TTDate::getDate( 'DATE', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	/**
	 * Pay period number functionality is deprecated, it causes too many problems for little or no benefit.
	 * Its also impossible to properly handle in custom situations where pay periods may be adjusted.
	 * However it is *required* in CompanyDeduction FormulaType=20 situations.
	 * @param int $epoch          EPOCH Must be a TRANSACTION DATE of a Pay Period
	 * @param int $end_date_epoch EPOCH Must be a END DATE of a Pay Period
	 * @return bool|float|int|mixed
	 */
	function getCurrentPayPeriodNumber( $epoch = null, $end_date_epoch = null ) {
		//EPOCH MUST BE TRANSACTION DATE!!!
		//End Date Epoch must be END DATE of pay period

		//If its a manual pay period schedule, just guess based on percentage through the year so far.
		if ( $this->getType() == 5 ) {
			$retval = round( ( TTDate::getDayOfYear( $epoch ) / TTDate::getDaysInYear( $epoch ) ) * $this->getAnnualPayPeriods() );

			return $retval;
		}

		//FIXME: Turn this query in to a straight count(*) query for even more speed.
		if ( $epoch == null || $epoch == '' ) {
			$epoch = TTDate::getTime();
		}
		//Debug::text('Epoch: '. TTDate::getDate('DATE+TIME', $epoch) .' - End Date Epoch: '. TTDate::getDate('DATE+TIME', $end_date_epoch), __FILE__, __LINE__, __METHOD__, 10);

		//Half Fixed method here. We cache the results so to speed it up, but there still might be a faster way to do this.
		//FIXME: Perhaps some type of hybrid system like the above unless they have less then a years worth of
		//pay periods, then use this method below?
		$cache_id = $this->getId() . $epoch . $end_date_epoch;
		$group_id = $this->getTable( true ) . $this->getCompany();

		$retval = $this->getCache( $cache_id, $group_id );
		if ( $retval === false ) {
			//FIXME: I'm sure there is a quicker way to do this.
			$next_transaction_date = 0;
			$next_end_date = $end_date_epoch;
			$end_year_epoch = TTDate::getEndYearEpoch( $epoch );
			$i = 0;

			$pp_schedule_obj = clone $this; //Clone the PP schedule object as getNextPayPeriod() changes data in-place and could affect things outside this function.

			while ( $next_transaction_date <= $end_year_epoch && $i < 60 ) {
				Debug::text( 'I: ' . $i . ' Looping: Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $next_transaction_date ) . ' - End Year Epoch: ' . TTDate::getDate( 'DATE+TIME', $end_year_epoch ), __FILE__, __LINE__, __METHOD__, 10 );
				$pp_schedule_obj->getNextPayPeriod( $next_end_date );

				$next_transaction_date = $pp_schedule_obj->getNextTransactionDate();
				$next_end_date = $pp_schedule_obj->getNextEndDate();

				if ( $next_transaction_date <= $end_year_epoch ) {
					$i++;
				}
			}

			$retval = ( $pp_schedule_obj->getAnnualPayPeriods() - $i );
			Debug::text( 'Current Pay Period: ' . $retval . ' Annual PPs: ' . $pp_schedule_obj->getAnnualPayPeriods() . ' I: ' . $i, __FILE__, __LINE__, __METHOD__, 10 );

			//Cache results
			$this->saveCache( $retval, $cache_id, $group_id );
		}

		return $retval;
	}

	/**
	 * @param int $type_id ID
	 * @return bool|float|int
	 */
	function calcAnnualPayPeriods( $type_id = null ) {
		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		switch ( $type_id ) {
			case 5:
				//We need the annual number of pay periods calculated for manual pay period schedules if we
				//are to have any hope of calculating taxes correctly.
				//Get all the pay periods, take the first day, last day, and the total number to figure out an average
				//number of days per period.
				//Alternatively have them manually specify the number, but this required adding a field to the table.
				$retval = false;

				if ( TTUUID::isUUID( $this->getId() ) && $this->getId() != TTUUID::getZeroID() && $this->getId() != TTUUID::getNotExistID() ) {
					$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
					$retarr = $pplf->getFirstStartDateAndLastEndDateByPayPeriodScheduleId( $this->getId() );
					if ( is_array( $retarr ) && isset( $retarr['first_start_date'] ) && isset( $retarr['last_end_date'] ) ) {
						$retarr['first_start_date'] = TTDate::strtotime( $retarr['first_start_date'] );
						$retarr['last_end_date'] = TTDate::strtotime( $retarr['last_end_date'] );

						$days_per_period = ( ( ( $retarr['last_end_date'] - $retarr['first_start_date'] ) / $retarr['total'] ) / 86400 );
						$retval = floor( 365 / round( $days_per_period ) );
						Debug::text( 'First Start Date: ' . TTDate::getDate( 'DATE+TIME', $retarr['first_start_date'] ) . ' Last End Date: ' . TTDate::getDate( 'DATE+TIME', $retarr['last_end_date'] ) . ' Total PP: ' . $retarr['total'] . ' Average Days/Period: ' . $days_per_period . '(' . round( $days_per_period ) . ') Annual Pay Periods: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );
					}
					unset( $pplf, $retarr );
				}

				break;
			case 10:
				//Needs to take into account years, where 53 weeks may occur.
				//Will need to get the day weeks start on and the year for this to calculate properly.
				//I believe 2015 is the next time this will occur.
				//Not sure if we can automatically handle this as holidays could push the pay period transaction date forward/backwards
				//depending on what the employer may want to do. In addition to that it could be a last minute change, and this may affect
				//salary calculations which could catch them off guard too.
				$retval = 52;
				break;
			case 100:
				$retval = 53;
				break;
			case 20:
				$retval = 26;
				break;
			case 200:
				$retval = 27;
				break;
			case 30:
				$retval = 24; //Semi-monthly
				break;
			case 40:
				$retval = 12; //Monthly + advance, deductions only once per month
				break;
			case 50:
				$retval = 12;
				break;
			default:
				return false;
				break;
		}

		return $retval;
	}

	/**
	 * Given a single start date and the type of pay period schedule, try to determine all other dates.
	 * @param int $type_id
	 * @param int $start_date EPOCH
	 * @return array|bool
	 */
	function detectPayPeriodScheduleDates( $type_id, $start_date ) {
		$retarr = [];

		$max = 4;

		$start_date = TTDate::getMiddleDayEpoch( TTDate::parseDateTime( $start_date ) ); //Handle DST by using middle day epoch.

		switch ( $type_id ) {
			case 5: //Manual
				break;
			case 10: //Weekly
				for ( $i = 0; $i < $max; $i++ ) {
					$end_date = ( $start_date + ( 86400 * 6 ) );
					$transaction_date = ( $end_date + ( 86400 * 5 ) );

					$retarr[] = [
							'start_date'       => TTDate::getDate( 'DATE', $start_date ),
							'end_date'         => TTDate::getDate( 'DATE', $end_date ),
							'transaction_date' => TTDate::getDate( 'DATE', $transaction_date ),
					];

					$start_date = ( $end_date + 86400 );
				}
				break;
			case 20: //BiWeekly
				for ( $i = 0; $i < $max; $i++ ) {
					$end_date = ( $start_date + ( 86400 * 13 ) );
					$transaction_date = ( $end_date + ( 86400 * 5 ) );

					$retarr[] = [
							'start_date'       => TTDate::getDate( 'DATE', $start_date ),
							'end_date'         => TTDate::getDate( 'DATE', $end_date ),
							'transaction_date' => TTDate::getDate( 'DATE', $transaction_date ),
					];

					$start_date = ( $end_date + 86400 );
				}
				break;
			case 30: //Semi-monthly
				for ( $i = 0; $i < $max; $i++ ) {
					$end_date = ( $start_date + ( 86400 * 14 ) );

					//If we're within 4 days of the last day of the month, use the last day.
					if ( abs( ( $end_date - TTDate::getEndMonthEpoch( $start_date ) ) ) < ( 86400 * 4 ) ) {
						$end_date = TTDate::getEndMonthEpoch( $start_date );
					}
					$transaction_date = $end_date;

					$retarr[] = [
							'start_date'       => TTDate::getDate( 'DATE', $start_date ),
							'end_date'         => TTDate::getDate( 'DATE', $end_date ),
							'transaction_date' => TTDate::getDate( 'DATE', $transaction_date ),
					];

					$start_date = ( $end_date + 86400 );
				}
				break;
			case 50: //Monthly
				for ( $i = 0; $i < $max; $i++ ) {
					$end_date = TTDate::getEndMonthEpoch( $start_date );
					$transaction_date = ( $end_date + ( 86400 * 0 ) );

					$retarr[] = [
							'start_date'       => TTDate::getDate( 'DATE', $start_date ),
							'end_date'         => TTDate::getDate( 'DATE', $end_date ),
							'transaction_date' => TTDate::getDate( 'DATE', $transaction_date ),
					];

					$start_date = ( $end_date + 86400 );
				}
				break;
			default:
				return false;
				break;
		}

		return $retarr;
	}

	/**
	 * This function given the pay period schedule type and example dates will attempt to determine the pay period schedule settings.
	 * This will automatically configure the current object to be saved.
	 * Base create initial pay period functionality on the first pay period date, otherwise we need additional data for bi-weekly pay periods.
	 * @param int $type_id
	 * @param int $example_dates EPOCH
	 * @return bool
	 */
	function detectPayPeriodScheduleSettings( $type_id, $example_dates ) {
		Debug::Arr( $example_dates, 'Pay Period Type: ' . $type_id . ' Example Dates: ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $type_id == '' ) {
			$type_id = $this->getType();
		}

		if ( !is_array( $example_dates ) ) {
			$example_dates = [];
		}

		if ( !isset( $example_dates[0]['start_date'] ) || ( isset( $example_dates[0]['start_date'] ) && $example_dates[0]['start_date'] == '' ) ) {
			Debug::Text( 'Example dates not specified properly, skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$this->setType( $type_id );
		if ( isset( $example_dates[0]['start_date'] ) ) {
			$this->setAnchorDate( ( TTDate::parseDateTime( $example_dates[0]['start_date'] ) - 86400 ) ); //Anchor date one day before first start date.
		}

		switch ( $type_id ) {
			case 5: //Manual
				break;
			case 10: //Weekly
			case 100: //Weekly (53)
			case 20: //BiWeekly
			case 200: //BiWeekly (27)
				//Need at least one example.
				$start_dow = [];
				$transaction_days = [];
				foreach ( $example_dates as $example_date ) {
					$start_dow[] = TTDate::getDayOfWeek( TTDate::parseDateTime( $example_date['start_date'] ) );
					$transaction_days[] = (int)round( TTDate::getDays( ( TTDate::parseDateTime( $example_date['transaction_date'] ) - TTDate::parseDateTime( $example_date['end_date'] ) ) ) );
				}
				Debug::Arr( $start_dow, 'Start DOW: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( $transaction_days, 'Transaction Days: ', __FILE__, __LINE__, __METHOD__, 10 );

				//Get the most common values from arrays.
				$start_day_of_week = Misc::arrayCommonValue( $start_dow );
				Debug::Arr( $start_dow, 'Start Day Of Week: ' . $start_day_of_week . ' Start DOW Count: ', __FILE__, __LINE__, __METHOD__, 10 );

				$transaction_date = Misc::arrayCommonValue( $transaction_days );
				Debug::Arr( $transaction_days, 'Transaction Date: ' . $transaction_date . ' Transaction Days Count: ', __FILE__, __LINE__, __METHOD__, 10 );

				$this->setStartDayOfWeek( $start_day_of_week );
				$this->setTransactionDate( $transaction_date );

				break;
			case 30: //Semi-monthly
				//Need at least three examples?
				$i = 0;
				$primary_start_dom = [];
				$primary_transaction_dom = [];
				$secondary_start_dom = [];
				$secondary_transaction_dom = [];
				foreach ( $example_dates as $example_date ) {
					if ( ( $i % 2 ) == 0 ) {
						$primary_start_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['start_date'] ) );
						$primary_transaction_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['transaction_date'] ) );
					} else {
						$secondary_start_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['start_date'] ) );
						$secondary_transaction_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['transaction_date'] ) );
					}

					$i++;
				}
				Debug::Arr( $primary_start_dom, 'Primary Start DOM: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( $primary_transaction_dom, 'Primary Transaction DOM: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( $secondary_start_dom, 'Secondary Start DOM: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( $secondary_transaction_dom, 'Secondary Transaction DOM: ', __FILE__, __LINE__, __METHOD__, 10 );

				$primary_dom = Misc::arrayCommonValue( $primary_start_dom );
				$primary_transaction_dom = Misc::arrayCommonValue( $primary_transaction_dom );
				$secondary_dom = Misc::arrayCommonValue( $secondary_start_dom );
				$secondary_transaction_dom = Misc::arrayCommonValue( $secondary_transaction_dom );
				Debug::Text( 'Primary: ' . $primary_dom . ' Trans: ' . $primary_transaction_dom . ' Secondary: ' . $secondary_dom . ' Trans: ' . $secondary_transaction_dom, __FILE__, __LINE__, __METHOD__, 10 );

				$this->setPrimaryDayOfMonth( $primary_dom );
				$this->setSecondaryDayOfMonth( $secondary_dom );
				$this->setPrimaryTransactionDayOfMonth( $primary_transaction_dom );
				$this->setSecondaryTransactionDayOfMonth( $secondary_transaction_dom );
				break;
			case 50: //Monthly
				//Need at least one example.
				foreach ( $example_dates as $example_date ) {
					$primary_start_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['start_date'] ) );
					$primary_transaction_dom[] = TTDate::getDayOfMonth( TTDate::parseDateTime( $example_date['transaction_date'] ) );
				}
				Debug::Arr( $primary_start_dom, 'Primary Start DOM: ', __FILE__, __LINE__, __METHOD__, 10 );
				Debug::Arr( $primary_transaction_dom, 'Primary Transaction DOM: ', __FILE__, __LINE__, __METHOD__, 10 );
				$primary_dom = Misc::arrayCommonValue( $primary_start_dom );
				$primary_transaction_dom = Misc::arrayCommonValue( $primary_transaction_dom );

				$this->setPrimaryDayOfMonth( $primary_dom );
				$this->setPrimaryTransactionDayOfMonth( $primary_transaction_dom );
				break;
			default:
				return false;
				break;
		}

		Debug::Arr( $this->data, 'PP Schedule Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @param int $start_epoch EPOCH
	 * @param int $end_epoch   EPOCH
	 * @param bool $shift_assigned_day_id
	 * @return mixed
	 */
	function getShiftAssignedDate( $start_epoch, $end_epoch, $shift_assigned_day_id = false ) {
		if ( $shift_assigned_day_id == '' ) {
			$shift_assigned_day_id = $this->getShiftAssignedDay();
		}

		//
		//FIXME: During testing always force start_date which is existing behaivor.
		//		 Once this is tested more it can be changed.
		//return $start_epoch;

		switch ( $shift_assigned_day_id ) {
			default:
			case 10: //Day they start on
			case 40: //Split at midnight
				//Debug::Text('Assign Shifts to the day they START on... Date: '. TTDate::getDate('DATE', $start_epoch ), __FILE__, __LINE__, __METHOD__, 10);
				$retval = $start_epoch;
				break;
			case 20: //Day they end on
				//Debug::Text('Assign Shifts to the day they END on... Date: '. TTDate::getDate('DATE', $end_epoch ), __FILE__, __LINE__, __METHOD__, 10);
				$retval = $end_epoch;
				break;
			case 30: //Day with most time worked
				$day_with_most_time = TTDate::getDayWithMostTime( $start_epoch, $end_epoch );
				//Debug::Text('Assign Shifts to the day they WORK MOST on... Date: '. TTDate::getDate('DATE', $day_with_most_time ), __FILE__, __LINE__, __METHOD__, 10);
				$retval = $day_with_most_time;
				break;
		}

		return $retval;
	}

	/**
	 * @param $date_stamp
	 * @param int $total_pay_periods
	 * @return array|bool
	 */
	function getStartAndEndDateRangeFromPastPayPeriods( $date_stamp, $total_pay_periods = 1 ) {
		$pplf = TTNew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getByPayPeriodScheduleIdAndEndDateBefore( $this->getId(), $date_stamp, $total_pay_periods );
		if ( $pplf->getRecordCount() > 0 ) {
			$filter_start_date = false;
			$filter_end_date = false;
			foreach ( $pplf as $pp_obj ) {
				if ( $filter_start_date == false || $pp_obj->getStartDate() < $filter_start_date ) {
					$filter_start_date = $pp_obj->getStartDate();
				}
				if ( $filter_end_date == false || $pp_obj->getEndDate() > $filter_end_date ) {
					$filter_end_date = $pp_obj->getEndDate();
				}
			}
			Debug::text( 'Total time over Pay Periods: ' . $total_pay_periods . ' Found Pay Periods: ' . $pplf->getRecordCount() . ' Start Date: ' . TTDate::getDate( 'DATE', $filter_start_date ) . ' End Date: ' . TTDate::getDate( 'DATE', $filter_end_date ), __FILE__, __LINE__, __METHOD__, 10 );

			$retval = [ 'start_date' => $filter_start_date, 'end_date' => $filter_end_date ];
		} else {
			Debug::text( 'WARNING: No pay period found...', __FILE__, __LINE__, __METHOD__, 10 );
			$retval = false;
		}
		unset( $pplf, $pp_obj );

		return $retval;
	}

	/**
	 * Returns shift data according to the pay period schedule criteria for use
	 * in determining which day punches belong to.
	 * @param int $date_stamp EPOCH
	 * @param string $user_id UUID
	 * @param int $epoch      EPOCH
	 * @param null $filter
	 * @param object $tmp_punch_control_obj
	 * @param null $maximum_shift_time
	 * @param null $new_shift_trigger_time
	 * @param null $plf
	 * @param bool $ignore_future_punches
	 * @return array|bool
	 */
	function getShiftData( $date_stamp = null, $user_id = null, $epoch = null, $filter = null, $tmp_punch_control_obj = null, $maximum_shift_time = null, $new_shift_trigger_time = null, $plf = null, $ignore_future_punches = false ) {
		global $profiler;
		$profiler->startTimer( 'PayPeriodScheduleFactory::getShiftData()' );

		if ( is_numeric( $date_stamp ) && $date_stamp > 0 ) {
			$epoch = null;
		}

		if ( $date_stamp == '' && $user_id == '' && $epoch == '' ) {
			return false;
		}

		if ( $maximum_shift_time === null ) {
			$maximum_shift_time = $this->getMaximumShiftTime();
		}

		//Debug::text('User Date ID: '. $user_date_id .' User ID: '. $user_id .' TimeStamp: '. TTDate::getDate('DATE+TIME', $epoch), __FILE__, __LINE__, __METHOD__, 10);
		if ( $new_shift_trigger_time === null ) {
			$new_shift_trigger_time = $this->getNewDayTriggerTime();
		}

		if ( !is_object( $plf ) ) {
			$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
			if ( $date_stamp != '' ) {
				$plf->getByUserIdAndDateStamp( $user_id, $date_stamp );
			} else {
				//Get punches by time stamp.
				$punch_control_id = TTUUID::getZeroID();
				if ( is_object( $tmp_punch_control_obj ) ) {
					$punch_control_id = $tmp_punch_control_obj->getId();
				}

				//We need to double the maximum shift time when searching for punches.
				//Assuming a maximum punch time of 14hrs:
				// In: 10:00AM Out: 2:00PM
				// In: 6:00PM Out: 6:00AM (next day)
				// The above scenario when adding the last 6:00AM punch on the next day will only look back 14hrs and not find the first
				// punch pair, therefore allowing more than 14hrs on the same day.
				// So we need to extend the maximum shift time just when searching for punches and let getShiftData() sort out the proper maximum shift time itself.
				//
				// $ignore_future_punches is used for getPunchDefaultSettings(), so we can ignore auto-punch shifts that may have been entered in the future already that cause the default settings to be incorrect.
				$plf->getShiftPunchesByUserIDAndEpoch( $user_id, $epoch, $punch_control_id, ( $maximum_shift_time * 2 ), $ignore_future_punches );
				unset( $punch_control_id );
			}
		}

		Debug::text( 'Punch Rows: ' . $plf->getRecordCount() . ' UserID: ' . $user_id . ' Date: ' . TTDate::getDate( 'DATE+TIME', $epoch ) . '(' . $epoch . ') MaximumShiftTime: ' . $maximum_shift_time . ' NewShiftTrigger: ' . $new_shift_trigger_time . ' Filter: ' . $filter . ' Ignore Future Punches: ' . (int)$ignore_future_punches, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $plf->getRecordCount() > 0 ) {
			$shift = 0;
			$i = 0;
			$x = 0;
			$nearest_shift = 0;
			$nearest_punch_difference = false;
			$shift_data = [];
			$prev_punch_arr = [];
			$punch_control_id_shift_map = [];
			foreach ( $plf as $p_obj ) {
				//Debug::text('Shift: '. $shift .' Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

				//If we're editing a punch, we need to use the object passed to this function instead of the one
				//from the database.

				if ( $epoch == null ) { //If user_date_id is passed without epoch, set epoch to the first punch we find.
					$epoch = $p_obj->getTimeStamp();
				}

				if ( empty( $prev_punch_arr ) == false && $p_obj->getTimeStamp() > $prev_punch_arr['time_stamp'] ) {
					//Make sure $x resets itself after each shift.
					$shift_data[$shift]['previous_punch_key'] = ( $x - 1 );
					if ( $shift_data[$shift]['previous_punch_key'] < 0 ) {
						$shift_data[$shift]['previous_punch_key'] = null;
					}
				}

				//Determine if a non-saved PunchControl object was passed, and if so, match the IDs to use that instead.
				if ( is_object( $tmp_punch_control_obj ) && $p_obj->getPunchControlID() == $tmp_punch_control_obj->getId() ) {
					Debug::text( 'Passed non-saved punch control object that matches, using that instead... Using ID: ' . $tmp_punch_control_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
					$punch_control_obj = $tmp_punch_control_obj;
				} else {
					$punch_control_obj = $p_obj->getPunchControlObject();
					if ( !is_object( $punch_control_obj ) ) {
						Debug::text( 'WARNING: Unable to find PunchControlObject, skipping... Punch ID: ' . $p_obj->getId(), __FILE__, __LINE__, __METHOD__, 10 );
						continue;
					}
				}

				//Can't use PunchControl object total_time because the record may not be saved yet when editing
				//an already existing punch.
				//When editing, simply pass the existing PunchControl object to this function so we can
				//use it instead of the one in the database perhaps?
				$total_time = $punch_control_obj->getTotalTime();

				//Make sure punches of the same punch_control_id are always on the same shift.
				//  This helps catch cases where a 1P - 11P punch pair is created, then a 12P punch, then a 1:13P punch (which clearly falls within the first punch pair and should be rejected).
				if ( isset( $punch_control_id_shift_map[$p_obj->getPunchControlId()] ) ) {
					//Debug::text( 'Found Punch Control ID already assigned to a shift... Punch Control ID: ' . $p_obj->getPunchControlID() . ' Shift: ' . $shift, __FILE__, __LINE__, __METHOD__, 10 );
					$shift = $punch_control_id_shift_map[$p_obj->getPunchControlId()];
				} else {
					//We can't skip records with total_time == 0, because then when deleting one of the two
					//punches in a pair, the remaining punch is ignored and causing punches to jump around between days in some cases.
					if ( $i > 0 && isset( $shift_data[$shift]['last_out'] )
							&& ( $p_obj->getStatus() == 10 || $p_obj->getStatus() == $prev_punch_arr['status_id'] ) ) {
						//Debug::text( 'Checking for new shift... This Control ID: ' . $p_obj->getPunchControlID() . ' Last Out Control ID: ' . $shift_data[$shift]['last_out']['punch_control_id'] . ' Last Out Time: ' . TTDate::getDate( 'DATE+TIME', $shift_data[$shift]['last_out']['time_stamp'] ), __FILE__, __LINE__, __METHOD__, 10 );
						//Assume that if two punches are assigned to the same punch_control_id are the same shift, even if the time between
						//them exceeds the new_shift_trigger_time. This helps fix the bug where you could add a In punch then add a Out
						//punch BEFORE the In punch as long as it was more than the Maximum Shift Time before the In Punch.
						//ie: Add: In Punch 10-Dec-09 @ 8:00AM, Add: Out Punch 09-Dec-09 @ 5:00PM.
						//Basically it just helps the validation checks to determine the error.
						//
						//It used to be that if shifts are split at midnight, new_shift_trigger_time must be 0, so the "split" punch can occur at midnight.
						//However we have since added a check to see if punches span midnight and trigger a new shift based on that, regardless of the new shift trigger time.
						//As the new_shift_trigger_time of 0 also affected lunch/break automatic detection by Punch Time, since an Out punch and a In punch of any time
						//would trigger a new shift, and it wouldn't be detected as lunch/break.
						//
						//What happens when the employee takes lunch/break over midnight? Lunch out at 11:30PM Lunch IN at 12:30AM
						//	We need to split those into two lunches, or two breaks? But then that can affect those policies if they are only allowed one break.
						//	Or do we not split the shift at all when this occurs? Currently we don't split at all.
						if ( $p_obj->getPunchControlID() != $shift_data[$shift]['last_out']['punch_control_id']
								&& (
										(
												( $p_obj->getType() == 10 || $shift_data[$shift]['last_out']['type_id'] == 10 ) //Handle cases where there is a Normal Out punch, then Lunch In punch about 12-15hrs later. Treat this as a new shift.
												&& ( ( $p_obj->getTimeStamp() != $shift_data[$shift]['last_out']['time_stamp'] )
														|| ( $new_shift_trigger_time == 0 && isset( $shift_data[$shift]['first_in']['time_stamp'] ) && TTDate::doesRangeSpanMidnight( $shift_data[$shift]['first_in']['time_stamp'], $p_obj->getTimeStamp(), true ) == true ) ) //Don't allow transfer punches to cause a new shift to start, *unless* new shift trigger time is 0 and the shift spans midnight, so we can have 24hr shifts for an entire week with no gap between them, but each assigned to their own day (ie: live-in care).
												&& ( $p_obj->getTimeStamp() - $shift_data[$shift]['last_out']['time_stamp'] ) >= $new_shift_trigger_time
										)
										||
										(
												isset( $prev_punch_arr['time_stamp'] )
												&& $prev_punch_arr['punch_control_id'] != $p_obj->getPunchControlId()
												&& abs( ( $prev_punch_arr['time_stamp'] - $p_obj->getTimeStamp() ) ) > $maximum_shift_time
										) //If two punches ever exceed the maximum shift time, consider it a new shift. Specifically when a Normal Out punches then a Lunch In punch happen outside maximum shift time.
										||
										(
												$this->getShiftAssignedDay() == 40 //Shifts split at midnight.
												//Only split shifts on NORMAL punches.
												&& ( $p_obj->getType() == 10 || $shift_data[$shift]['last_out']['type_id'] == 10 ) //Handle cases where there is a Normal Out punch, then Lunch In punch about 12-15hrs later. Treat this as a new shift.
												&& $shift_data[$shift]['last_out']['type_id'] == 10
												&& TTDate::doesRangeSpanMidnight( $shift_data[$shift]['last_out']['time_stamp'], $p_obj->getTimeStamp(), true ) == true
										)
								)
						) {
							Debug::Text( '	 New shift because of normal punches... Punch Time: ' . $p_obj->getTimeStamp() . ' Last Out: ' . $shift_data[$shift]['last_out']['time_stamp'] . ' New Shift: ' . $new_shift_trigger_time . ' ShiftAssignedType: ' . $this->getShiftAssignedDay(), __FILE__, __LINE__, __METHOD__, 10 );
							$shift++;
							$x = 0;
						}
					} else if ( $i > 0
							&& isset( $prev_punch_arr['time_stamp'] )
							&& $prev_punch_arr['punch_control_id'] != $p_obj->getPunchControlId()
							&& abs( ( $prev_punch_arr['time_stamp'] - $p_obj->getTimeStamp() ) ) > $maximum_shift_time ) {
						//Debug::text('	 New shift because two punch_control records exist and punch timestamp exceed maximum shift time.', __FILE__, __LINE__, __METHOD__, 10);
						$shift++;
						$x = 0;
					}

					$punch_control_id_shift_map[$p_obj->getPunchControlId()] = $shift;
				}

				if ( !isset( $shift_data[$shift]['total_time'] ) ) {
					$shift_data[$shift]['total_time'] = 0;
				}

				$punch_day_epoch = TTDate::getBeginDayEpoch( $p_obj->getTimeStamp() );
				if ( !isset( $shift_data[$shift]['total_time_per_day'][$punch_day_epoch] ) ) {
					$shift_data[$shift]['total_time_per_day'][$punch_day_epoch] = 0;
				}

				//Determine which shift is closest to the given epoch.
				$punch_difference_from_epoch = abs( ( $epoch - $p_obj->getTimeStamp() ) );
				if ( $nearest_punch_difference === false || $punch_difference_from_epoch <= $nearest_punch_difference ) {
					//Debug::text( 'Nearest Shift Determined to be: ' . $shift . ' Nearest Punch Diff: ' . (int)$nearest_punch_difference . ' Punch Diff: ' . $punch_difference_from_epoch . ' Epoch: ' . TTDate::getDate( 'DATE+TIME', $epoch ) . ' Current Punch: ' . TTDate::getDate( 'DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

					//If two punches have the same timestamp, use the shift that matches the passed punch control object, which is usually the one we are currently editing...
					//This is for splitting shifts at exactly midnight.
					if ( $punch_difference_from_epoch != $nearest_punch_difference
							|| ( $punch_difference_from_epoch == $nearest_punch_difference && ( is_object( $tmp_punch_control_obj ) && $tmp_punch_control_obj->getId() == $p_obj->getPunchControlID() ) ) ) {
						//Debug::text('Found two punches with the same timestamp... Tmp Punch Control: '.$tmp_punch_control_obj->getId() .' Punch Control: '. $p_obj->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10);
						$nearest_shift = $shift;
						$nearest_punch_difference = $punch_difference_from_epoch;
					}
				}

				$punch_arr = [
						'id'               => $p_obj->getId(),
						'punch_control_id' => $p_obj->getPunchControlId(),
						'user_id'          => $punch_control_obj->getUser(),
						'date_stamp'       => $punch_control_obj->getDateStamp(),
						'time_stamp'       => $p_obj->getTimeStamp(),
						'status_id'        => $p_obj->getStatus(),
						'type_id'          => $p_obj->getType(),
				];

				$shift_data[$shift]['punches'][] = $punch_arr;
				$shift_data[$shift]['punch_control_ids'][] = $p_obj->getPunchControlId();
				if ( $punch_control_obj->getDateStamp() != false ) {
					$shift_data[$shift]['date_stamps'][] = $punch_control_obj->getDateStamp();
				}

				if ( !isset( $shift_data[$shift]['span_midnight'] ) ) {
					$shift_data[$shift]['span_midnight'] = false;
				}
				if ( !isset( $shift_data[$shift]['first_in'] ) && $p_obj->getStatus() == 10 ) {
					//Debug::text('First In -- Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					$shift_data[$shift]['first_in'] = $punch_arr;
				} else if ( $p_obj->getStatus() == 20 ) {
					//Debug::text('Last Out -- Punch ID: '. $p_obj->getID() .' Punch Control ID: '. $p_obj->getPunchControlID() .' TimeStamp: '. TTDate::getDate('DATE+TIME', $p_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10);

					$shift_data[$shift]['last_out'] = $punch_arr;

					//Debug::text('Total Time: '. $total_time, __FILE__, __LINE__, __METHOD__, 10);
					$shift_data[$shift]['total_time'] += $total_time;

					//Check to see if the previous punch was on a different day then the current punch.
					if ( empty( $prev_punch_arr ) == false
							&& ( $p_obj->getStatus() == 20 && $prev_punch_arr['status_id'] != 20 )
							&& TTDate::doesRangeSpanMidnight( $prev_punch_arr['time_stamp'], $p_obj->getTimeStamp(), true ) == true ) {
						Debug::text( 'Punch PAIR DOES span midnight', __FILE__, __LINE__, __METHOD__, 10 );
						$shift_data[$shift]['span_midnight'] = true;

						$total_time_for_each_day_arr = TTDate::calculateTimeOnEachDayBetweenRange( $prev_punch_arr['time_stamp'], $p_obj->getTimeStamp() );
						if ( is_array( $total_time_for_each_day_arr ) ) {
							foreach ( $total_time_for_each_day_arr as $begin_day_epoch => $day_total_time ) {
								if ( !isset( $shift_data[$shift]['total_time_per_day'][$begin_day_epoch] ) ) {
									$shift_data[$shift]['total_time_per_day'][$begin_day_epoch] = 0;
								}
								$shift_data[$shift]['total_time_per_day'][$begin_day_epoch] += $day_total_time;
							}
						}
						unset( $total_time_for_each_day_arr, $begin_day_epoch, $day_total_time );
					} else {
						$shift_data[$shift]['total_time_per_day'][$punch_day_epoch] += $total_time;
					}
				}

				//Count meals and how long they have been in total, this is required for PunchFactory->getNextType() to properly detect the next type.
				if ( !isset( $shift_data[$shift]['lunch'] ) ) {
					$shift_data[$shift]['lunch'] = [ 'total' => 0, 'total_time' => 0 ];
				}
				if ( $p_obj->getType() == 20 ) { //20=Lunch
					if ( $p_obj->getStatus() == 20 ) { //20=Out
						$shift_data[$shift]['lunch']['total']++;
					} else if ( isset( $prev_punch_arr['time_stamp'] ) ) { //In
						$shift_data[$shift]['lunch']['total_time'] += ( $p_obj->getTimeStamp() - $prev_punch_arr['time_stamp'] );
					}
				}

				//Count breaks and how long they have been in total, this is required for PunchFactory->getNextType() to properly detect the next type.
				if ( !isset( $shift_data[$shift]['break'] ) ) {
					$shift_data[$shift]['break'] = [ 'total' => 0, 'total_time' => 0 ];
				}
				if ( $p_obj->getType() == 30 ) { //30=Break
					if ( $p_obj->getStatus() == 20 ) { //20=Out
						$shift_data[$shift]['break']['total']++;
					} else if ( isset( $prev_punch_arr['time_stamp'] ) ) { //In
						$shift_data[$shift]['break']['total_time'] += ( $p_obj->getTimeStamp() - $prev_punch_arr['time_stamp'] );
					}
				}

				//Keep instead of last punch for each shift so we can easily get to it.
				end( $shift_data[$shift]['punches'] );
				$shift_data[$shift]['last_punch_key'] = key( $shift_data[$shift]['punches'] );

				$prev_punch_arr = $punch_arr;
				$i++;
				$x++;
			}

			//Debug::Arr($shift_data, 'aShift Data:', __FILE__, __LINE__, __METHOD__, 10);

			if ( empty( $shift_data ) == false ) {
				//Loop through each shift to determine the day with the most time.
				foreach ( $shift_data as $tmp_shift_key => $tmp_shift_data ) {
					krsort( $shift_data[$tmp_shift_key]['total_time_per_day'] ); //Sort by day first
					arsort( $shift_data[$tmp_shift_key]['total_time_per_day'] ); //Sort by total time per day.
					reset( $shift_data[$tmp_shift_key]['total_time_per_day'] );
					$shift_data[$tmp_shift_key]['day_with_most_time'] = key( $shift_data[$tmp_shift_key]['total_time_per_day'] );

					$shift_data[$tmp_shift_key]['punch_control_ids'] = array_unique( $shift_data[$tmp_shift_key]['punch_control_ids'] );
					if ( isset( $shift_data[$tmp_shift_key]['date_stamps'] ) ) {
						$shift_data[$tmp_shift_key]['date_stamps'] = array_unique( $shift_data[$tmp_shift_key]['date_stamps'] );
					}
				}
				unset( $tmp_shift_key, $tmp_shift_data );

				if ( $filter == 'first_shift' ) {
					//Only return first shift.
					$shift_data = $shift_data[0];
				} else if ( $filter == 'last_shift' ) {
					//Only return last shift.
					$shift_data = $shift_data[$shift];
				} else if ( $filter == 'nearest_shift' ) {
					$shift_data = $shift_data[$nearest_shift];
					//Check to make sure the nearest shift is within the new shift trigger time of EPOCH.
					if ( isset( $shift_data['first_in']['time_stamp'] ) ) {
						$first_in = $shift_data['first_in']['time_stamp'];
					} else if ( isset( $shift_data['last_out']['time_stamp'] ) ) {
						$first_in = $shift_data['last_out']['time_stamp'];
					}

					if ( isset( $shift_data['last_out']['time_stamp'] ) ) {
						$last_out = $shift_data['last_out']['time_stamp'];
					} else if ( isset( $shift_data['first_in']['time_stamp'] ) ) {
						$last_out = $shift_data['first_in']['time_stamp'];
					}

					//The check below must occur so if the user attempts to add an In punch that occurs AFTER the Out punch, this function
					//still returns the shift data, so the validation checks can occur in PunchControl factory.
					if ( $first_in > $last_out ) {
						//It appears that the first in punch has occurred after the OUT punch, so swap first_in and last_out, so we don't return FALSE in this case.
						[ $first_in, $last_out ] = [ $last_out, $first_in ];
					}

					//First check if the current punch_control is being deleted and the if the nearest shift is assigned to the date it was also assigned too.
					//   This helps with the case where a shift assigned to Dec 25th from 11P to 7A, and a single punch on Dec 26th @ 7:30A, with a new_shift_trigger_time=0, when deleting this punch it matches the previous shift and reassigns that shift to the 26th.
					if ( is_object( $tmp_punch_control_obj ) && $tmp_punch_control_obj->getDeleted() == true && in_array( $tmp_punch_control_obj->getDateStamp(), $shift_data['date_stamps'] ) == false ) {
						Debug::Text( ' Punch Control is being deleted, and nearest shift is not on the date it was on... Punch Control Date: ' . TTDate::getDate( 'DATE+TIME', $tmp_punch_control_obj->getDateStamp() ) . ' First In: ' . TTDate::getDate( 'DATE+TIME', $first_in ) . ' Last Out: ' . TTDate::getDate( 'DATE+TIME', $last_out ) . ' New Shift Trigger: ' . $new_shift_trigger_time . ' Prev Punch Key: ' . ( isset( $shift_data['previous_punch_key'] ) ? $shift_data['previous_punch_key'] : 'N/A' ), __FILE__, __LINE__, __METHOD__, 10 );
						return false;
					} else if ( $first_in != $last_out
							&& ( isset( $shift_data['punches'][$shift_data['last_punch_key']] ) && $shift_data['punches'][$shift_data['last_punch_key']]['status_id'] == 20 && $shift_data['punches'][$shift_data['last_punch_key']]['type_id'] == 10 )
							&& TTDate::doesRangeSpanMidnight( $shift_data['punches'][$shift_data['last_punch_key']]['time_stamp'], $epoch ) == true
							&& TTDate::isTimeOverLap( $epoch, $epoch, ( $first_in - $new_shift_trigger_time ), ( $last_out + $new_shift_trigger_time ) ) == false
					) {
						//This comment is for the above ELSE IF statement:
						//Only check overlap if the last punch in the shift is an OUT punch (shift has ended basically), and the first/last punches don't match.
						//Only check against NORMAL OUT punches though, that way if new_shift_trigger_time=0, if the employee goes for lunch it thinks they are starting a new shift.
						//However meal policies based on Punch Time, the last punch is always a Normal Out, so if new_shift_trigger_time=0 it will never detect lunches properly.
						//  Switch this back to checking if the shift spans midnight for new shifts to be triggered.
						Debug::Text( 'Nearest shift is outside the new shift trigger time... Epoch: ' . TTDate::getDate( 'DATE+TIME', $epoch ) . ' First In: ' . TTDate::getDate( 'DATE+TIME', $first_in ) . ' Last Out: ' . TTDate::getDate( 'DATE+TIME', $last_out ) . ' New Shift Trigger: ' . $new_shift_trigger_time . ' Prev Punch Key: ' . $shift_data['previous_punch_key'], __FILE__, __LINE__, __METHOD__, 10 );
						return false;
					}
					unset( $first_in, $last_out );
				} else {
					Debug::Text( 'ERROR: invalid filter used: ' . $filter, __FILE__, __LINE__, __METHOD__, 10 );
				}

				$profiler->stopTimer( 'PayPeriodScheduleFactory::getShiftData()' );

				//Debug::Arr($shift_data, 'bShift Data:', __FILE__, __LINE__, __METHOD__, 10);
				return $shift_data;
			}
		}

		$profiler->stopTimer( 'PayPeriodScheduleFactory::getShiftData()' );

		Debug::Text( 'No Shift Data returned...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param bool $user_ids
	 * @return bool
	 */
	function importData( $user_ids = false ) {
		$epoch = TTDate::getMiddleDayEpoch( time() );

		//Get all OPEN pay periods that have a transaction date *after* today, and import data into them.
		//  Only OPEN pay periods. Definitely not post-adjustment ones incase they have switched pay period schedules and have to go back to make a correction.
		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getByCompanyIdAndStatus( $this->getCompany(), [ 10 ] );
		Debug::Text( 'Found Open Pay Periods: ' . $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				Debug::Text( '  Pay Period: ID: ' . $pp_obj->getID() . ' Transaction Date: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getTransactionDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				if ( TTDate::getMiddleDayEpoch( $pp_obj->getTransactionDate() ) >= $epoch ) {
					$pp_obj->importData( $user_ids, $pp_obj->getID() );
				} else {
					Debug::Text( '  OPEN Pay Period with Transaction Date before today, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableInitialPayPeriods() {
		if ( isset( $this->enable_create_initial_pay_periods ) ) {
			return $this->enable_create_initial_pay_periods;
		}

		return false;
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function setEnableInitialPayPeriods( $val ) {
		$this->enable_create_initial_pay_periods = (bool)$val;

		return true;
	}

	/**
	 * @return bool
	 */
	function getCreateInitialPayPeriods() {
		if ( isset( $this->create_initial_pay_periods ) ) {
			return $this->create_initial_pay_periods;
		}

		return false;
	}

	/**
	 * @param $val
	 * @return bool
	 */
	function setCreateInitialPayPeriods( $val ) {
		$this->create_initial_pay_periods = (bool)$val;

		return true;
	}

	/**
	 * Close pay periods that were left open.
	 * Pay period schedule must be at least 45 days old so we don't close pay periods on new customers right away.
	 * Only close OPEN/Locked pay periods that have passed the transaction date by 5 days.
	 * Get OPEN pay periods with transaction dates at least 48hrs before the given date?
	 * Or should we just prevent customers from generating pay stubs in a pay period that has a previous pay period that is still open? Both.
	 * @param int $date EPOCH
	 * @return bool
	 */
	function forceClosePreviousPayPeriods( $date = null ) {
		if ( $date == '' ) {
			$date = time();
		}

		if ( $this->getAutoCloseAfterDays() == -1 ) {
			return false;
		}

		$date = ( $date - ( 86400 * $this->getAutoCloseAfterDays() ) ); //Grace period after the transaction date. Defaults to 3 days but can be changed by the user.
		$pplf = TTNew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$pplf->getByCompanyIDAndPayPeriodScheduleIdAndStatusAndStartTransactionDateAndEndTransactionDate( $this->getCompany(), $this->getID(), [ 10, 12 ], 1, $date );
		Debug::text( 'Closing Open/Locked Pay Periods: ' . $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				if ( is_object( $pp_obj->getPayPeriodScheduleObject() ) && $pp_obj->getPayPeriodScheduleObject()->getCreatedDate() < ( $date - ( 86400 * 45 ) ) ) {
					Debug::text( 'Closing Pay Period ID: ' . $pp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
					$pp_obj->setStatus( 20 ); //Closed
					if ( $pp_obj->isValid() ) {
						//Make log entry as person who last updated the pay period schedule so they can see it in the audit log at least.
						TTLog::addEntry( $pp_obj->getId(), 500, TTi18n::getText( 'Force closing OPEN/Locked pay period' ) . ': ' . TTDate::getDate( 'DATE', $pp_obj->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $pp_obj->getEndDate() ), $pp_obj->getPayPeriodScheduleObject()->getUpdatedBy(), $pp_obj->getTable() );

						$pp_obj->Save();
					}
				}
			}
		}

		//Also force close any Post-Adjustment pay periods that haven't been updated in 65days. (two months at least, in case they are monthly pay periods)
		$pplf->getByCompanyIDAndPayPeriodScheduleIdAndStatusAndStartTransactionDateAndEndTransactionDate( $this->getCompany(), $this->getID(), [ 30 ], 1, $date );
		Debug::text( 'Closing Post-Adjustment Pay Periods: ' . $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				if ( $pp_obj->getUpdatedDate() < ( $date - ( 86400 * 65 ) ) ) {
					Debug::text( 'Closing Pay Period ID: ' . $pp_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
					$pp_obj->setStatus( 20 ); //Closed
					if ( $pp_obj->isValid() ) {
						//Make log entry as person who last updated the pay period schedule so they can see it in the audit log at least.
						TTLog::addEntry( $pp_obj->getId(), 500, TTi18n::getText( 'Force closing Post-Adjustment pay period' ) . ': ' . TTDate::getDate( 'DATE', $pp_obj->getStartDate() ) . ' -> ' . TTDate::getDate( 'DATE', $pp_obj->getEndDate() ), $pp_obj->getPayPeriodScheduleObject()->getUpdatedBy(), $pp_obj->getTable() );

						$pp_obj->Save();
					}
				}
			}
		}

		return true;
	}

	/**
	 * @return bool|int
	 */
	function getFirstPayPeriodStartDate() {
		$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
		$retarr = $pplf->getFirstStartDateAndLastEndDateByPayPeriodScheduleId( $this->getId() );
		if ( is_array( $retarr ) && isset( $retarr['first_start_date'] ) ) {
			$retval = TTDate::strtotime( $retarr['first_start_date'] );
			Debug::text( 'First Pay Period Start Date: ' . TTDate::getDate( 'DATE', $retval ), __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		$this->setDayStartTime( 0 ); //Since this isn't support, force DayStartTime to midnight always.

		if ( $this->getShiftAssignedDay() == false ) {
			$this->setShiftAssignedDay( 10 ); //Day shifts start on
		} //elseif ( $this->getShiftAssignedDay() == 40 ) { //Split at midnight
		//We now support a minimum time-off setting when shifts are set to split at midnight.
		//$this->setNewDayTriggerTime( 0 ); //Minimum Time-off between shifts must be 0 in these cases.
		//}

		if ( empty( $this->getCreateDaysInAdvance() ) ) {
			$this->setCreateDaysInAdvance( 16 );
		}

		if ( empty( $this->getAutoCloseAfterDays() ) ) {
			$this->setAutoCloseAfterDays( 3 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		$this->StartTransaction();

		if ( $this->isNew() == true ) {
			$this->setCreateInitialPayPeriods( true );
		}

		if ( $this->getType() != 5 ) { //If schedule is other then manual, automatically calculate annual pay periods
			$this->setAnnualPayPeriods( $this->calcAnnualPayPeriods() );
		}

		if ( $this->getDeleted() == true ) {
			//Delete pay periods assigned to this schedule.
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$pplf->getByPayPeriodScheduleId( $this->getId() );
			if ( $pplf->getRecordCount() > 0 ) {
				Debug::text( 'Delete Pay Periods: ' . $pplf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				foreach ( $pplf as $pp_obj ) {
					$pp_obj->setDeleted( true );
					$pp_obj->Save();
				}
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
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Type
		$this->Validator->inArrayKey( 'type',
									  $this->getType(),
									  TTi18n::gettext( 'Incorrect Type' ),
									  $this->getOptions( 'type' )
		);

		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is invalid' ),
									2, 50
		);

		$this->Validator->isHTML( 'name',
								  $this->getName(),
								  TTi18n::gettext( 'Name contains invalid special characters' ),
		);

		if ( $this->Validator->isError( 'name' ) == false ) {
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
										2, 255
			);

			$this->Validator->isHTML( 'description',
									  $this->getDescription(),
									  TTi18n::gettext( 'Description contains invalid special characters' ),
			);
		}

		// Start Week Day
		if ( $this->getStartWeekDay() !== false ) {
			$this->Validator->inArrayKey( 'start_week_day',
										  $this->getStartWeekDay(),
										  TTi18n::gettext( 'Incorrect Start Week Day' ),
										  $this->getOptions( 'start_week_day' )
			);
		}

		// Shift Assigned Day
		if ( $this->getShiftAssignedDay() !== false ) {
			$this->Validator->inArrayKey( 'shift_assigned_day_id',
										  $this->getShiftAssignedDay(),
										  TTi18n::gettext( 'Incorrect Shift Assigned Day' ),
										  $this->getOptions( 'shift_assigned_day' )
			);
		}

		// Start day of week
		if ( $this->getStartDayOfWeek() !== false ) {
			$this->Validator->inArrayKey( 'start_day_of_week',
										  $this->getStartDayOfWeek(),
										  TTi18n::gettext( 'Incorrect start day of week' ),
										  TTDate::getDayOfWeekArray()
			);
		}

		// Transaction date
		if ( !empty( $this->getTransactionDate() ) ) {
			$this->Validator->inArrayKey( 'transaction_date',
										  $this->getTransactionDate(),
										  TTi18n::gettext( 'Incorrect transaction date' ),
										  TTDate::getDayOfMonthArray()
			);
		}
		// Primary day of month
		if ( $this->getPrimaryDayOfMonth() != -1 && $this->getPrimaryDayOfMonth() != 0 && $this->getPrimaryDayOfMonth() != '' ) {
			$this->Validator->inArrayKey( 'primary_day_of_month',
										  $this->getPrimaryDayOfMonth(),
										  TTi18n::getText( 'Incorrect primary day of month' ),
										  TTDate::getDayOfMonthArray()
			);
		}
		// Secondary day of month
		if ( $this->getSecondaryDayOfMonth() != -1 && $this->getSecondaryDayOfMonth() != 0 && $this->getSecondaryDayOfMonth() != '' ) {
			$this->Validator->inArrayKey( 'secondary_day_of_month',
										  $this->getSecondaryDayOfMonth(),
										  TTi18n::gettext( 'Incorrect secondary day of month' ),
										  TTDate::getDayOfMonthArray()
			);
		}
		// Primary transaction day of month
		if ( $this->getPrimaryTransactionDayOfMonth() != -1 && $this->getPrimaryTransactionDayOfMonth() != 0 && $this->getPrimaryTransactionDayOfMonth() != '' ) {
			$this->Validator->inArrayKey( 'primary_transaction_day_of_month',
										  $this->getPrimaryTransactionDayOfMonth(),
										  TTi18n::gettext( 'Incorrect primary transaction day of month' ),
										  TTDate::getDayOfMonthArray()
			);
		}
		// Secondary transaction day of month
		if ( $this->getSecondaryTransactionDayOfMonth() != -1 && $this->getSecondaryTransactionDayOfMonth() != 0 && $this->getSecondaryTransactionDayOfMonth() != '' ) {
			$this->Validator->inArrayKey( 'secondary_transaction_day_of_month',
										  $this->getSecondaryTransactionDayOfMonth(),
										  TTi18n::gettext( 'Incorrect secondary transaction day of month' ),
										  TTDate::getDayOfMonthArray()
			);
		}

		if ( $this->getType() == 30 ) { //30=Semi-Monthly
			if ( $this->Validator->isError( 'secondary_day_of_month' ) == false && $this->getPrimaryDayOfMonth() == $this->getSecondaryDayOfMonth() ) {
				$this->Validator->isTrue( 'secondary_day_of_month',
										  false,
										  TTi18n::gettext( 'Primary and Secondary Day of Month cannot match' )
				);
			}

			if ( $this->Validator->isError( 'secondary_transaction_day_of_month' ) == false && $this->getPrimaryTransactionDayOfMonth() == $this->getSecondaryTransactionDayOfMonth() ) {
				$this->Validator->isTrue( 'secondary_transaction_day_of_month',
										  false,
										  TTi18n::gettext( 'Primary and Secondary Transaction Day of Month cannot match' )
				);
			}

		}

		// Transaction date adjustment
		$this->Validator->inArrayKey( 'transaction_date_bd',
									  $this->getTransactionDateBusinessDay(),
									  TTi18n::gettext( 'Incorrect transaction date adjustment' ),
									  $this->getOptions( 'transaction_date_business_day' )
		);
		// Anchor (start) date
		if ( $this->getAnchorDate() !== false ) {
			$this->Validator->isDate( 'anchor_date',
									  $this->getAnchorDate(),
									  TTi18n::gettext( 'Incorrect start date' )
			);
		}

		// Day start time
		$this->Validator->isNumeric( 'day_start_time',
									 $this->getDayStartTime(),
									 TTi18n::gettext( 'Incorrect day start time' )
		);
		// Time zone
		$this->Validator->inArrayKey( 'time_zone',
									  $this->getTimeZone(),
									  TTi18n::gettext( 'Incorrect time zone' ),
									  Misc::trimSortPrefix( $this->getTimeZoneOptions() )
		);
		// Minimum Time-Off Between Shifts
		$this->Validator->isNumeric( 'new_day_trigger_time',
									 $this->getNewDayTriggerTime(),
									 TTi18n::gettext( 'Incorrect Minimum Time-Off Between Shifts' )
		);
		// Maximum Shift Time
		if ( $this->getMaximumShiftTime() !== false ) {
			$this->Validator->isNumeric( 'maximum_shift_time',
										 $this->getMaximumShiftTime(),
										 TTi18n::gettext( 'Incorrect Maximum Shift Time' )
			);
			if ( $this->Validator->isError( 'maximum_shift_time' ) == false ) {
				$this->Validator->isLessThan( 'maximum_shift_time',
											  $this->getMaximumShiftTime(),
											  TTi18n::gettext( 'Maximum Shift Time is too long' ),
											  691200
				);
			}
			if ( $this->Validator->isError( 'maximum_shift_time' ) == false ) {
				$this->Validator->isGreaterThan( 'maximum_shift_time',
												 $this->getMaximumShiftTime(),
												 TTi18n::gettext( 'Maximum Shift Time is too short' ),
												 14400
				);
			}
		}

		if ( $this->getAnnualPayPeriods() !== false ) {
			// Annual Pay Periods
			$this->Validator->isNumeric( 'annual_pay_periods',
										 $this->getAnnualPayPeriods(),
										 TTi18n::gettext( 'Incorrect Annual Pay Periods' )
			);
		}

		if ( $this->getTimeSheetVerifyType() !== false ) {
			// TimeSheet Verification Type
			$this->Validator->inArrayKey( 'timesheet_verify_type_id',
										  $this->getTimeSheetVerifyType(),
										  TTi18n::gettext( 'Incorrect TimeSheet Verification Type' ),
										  $this->getOptions( 'timesheet_verify_type' )
			);
		}

		// value for timesheet verification before/after end date
		if ( $this->getTimeSheetVerifyType() != 10 && $this->getTimeSheetVerifyBeforeEndDate() !== false ) {
			$this->Validator->isNumeric( 'timesheet_verify_before_end_date',
										 $this->getTimeSheetVerifyBeforeEndDate(),
										 TTi18n::gettext( 'Incorrect value for timesheet verification before/after end date' )
			);

			if ( $this->Validator->isError( 'timesheet_verify_before_end_date' ) == false ) {
				$this->Validator->isLessThan( 'timesheet_verify_before_end_date',
											  $this->getTimeSheetVerifyBeforeEndDate(),
											  TTi18n::gettext( 'Verification Window Starts is too large' ),
											  999
				);
			}
			if ( $this->Validator->isError( 'timesheet_verify_before_end_date' ) == false ) {
				$this->Validator->isGreaterThan( 'timesheet_verify_before_end_date',
												 $this->getTimeSheetVerifyBeforeEndDate(),
												 TTi18n::gettext( 'Verification Window Starts is too small' ),
												 -999
				);
			}
		}

		// value for timesheet verification before/after transaction date
		if ( $this->getTimeSheetVerifyType() != 10 && $this->getTimeSheetVerifyBeforeTransactionDate() !== false ) {
			$this->Validator->isNumeric( 'timesheet_verify_before_transaction_date',
										 $this->getTimeSheetVerifyBeforeTransactionDate(),
										 TTi18n::gettext( 'Incorrect value for timesheet verification before/after transaction date' )
			);
			if ( $this->Validator->isError( 'timesheet_verify_before_transaction_date' ) == false ) {
				$this->Validator->isLessThan( 'timesheet_verify_before_transaction_date',
											  $this->getTimeSheetVerifyBeforeTransactionDate(),
											  TTi18n::gettext( 'Verification Window Ends is too large' ),
											  999
				);
			}
			if ( $this->Validator->isError( 'timesheet_verify_before_transaction_date' ) == false ) {
				$this->Validator->isGreaterThan( 'timesheet_verify_before_transaction_date',
												 $this->getTimeSheetVerifyBeforeTransactionDate(),
												 TTi18n::gettext( 'Verification Window Ends is too small' ),
												 -999
				);
			}
			// value for timesheet verification notice before/after transaction date
			$this->Validator->isNumeric( 'timesheet_verify_notice_before_transaction_date',
										 $this->getTimeSheetVerifyNoticeBeforeTransactionDate(),
										 TTi18n::gettext( 'Incorrect value for timesheet verification notice before/after transaction date' )
			);
		}

		// Days ahead to display: 1 to 546 days. Note 546 days is the maximum future date for holidays and recurring schedules to populate.
		if ( $this->getCreateDaysInAdvance() == '' || $this->getCreateDaysInAdvance() < 0 || $this->getCreateDaysInAdvance() > 546 ) {
			$this->Validator->isTrue( 'create_days_in_advance',
									  false,
									  TTi18n::gettext( 'Create Pay Periods in Advance must be between 1 and 546' )
			);
		}

		// Auto close after X days. -1 to 396 days (1yr + 31 days). A value of -1 means never auto close.
		if ( $this->getAutoCloseAfterDays() == '' || $this->getAutoCloseAfterDays() < -1 || $this->getAutoCloseAfterDays() > 396 ) {
			$this->Validator->isTrue( 'auto_close_after_days',
									  false,
									  TTi18n::gettext( 'Auto Close Days must be between 0 and 396' ) //Don't display "-1" as it really shouldn't be used much if at all.
			);
		}

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getDeleted() == true ) {
			return true;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );
		$this->removeCache( null, $this->getTable( true ) . $this->getCompany() ); //Clear cached getCurrentPayPeriodNumber()

		if ( $this->getEnableInitialPayPeriods() == true && $this->getCreateInitialPayPeriods() == true ) {
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$pay_period_schedule_obj = $ppslf->getById( $this->getId() )->getCurrent();

			$pay_period_schedule_obj->createNextPayPeriod( $pay_period_schedule_obj->getAnchorDate() );
			Debug::text( 'New Pay Period Schdule, creating pay periods start from (' . $pay_period_schedule_obj->getAnchorDate() . '): ' . TTDate::getDate( 'DATE+TIME', $pay_period_schedule_obj->getAnchorDate() ), __FILE__, __LINE__, __METHOD__, 10 );

			//Create pay periods up until now, at most 260. (5yrs of weekly ones)
			for ( $i = 0; $i <= 260; $i++ ) {
				if ( $pay_period_schedule_obj->createNextPayPeriod() == false ) {
					Debug::text( 'createNextPayPeriod returned false, stopping loop.', __FILE__, __LINE__, __METHOD__, 10 );
					break;
				}
			}
		}

		if ( $this->getDeleted() == true ) {
			//Delete all users assigned to this pay period. This helps prevent duplicate rows from appearing in recurring schedules and such, since joins may match on these, before they get to the pay period schedule row to detect if its deleted or not.
			$ppsulf = TTnew( 'PayPeriodScheduleUserListFactory' ); /** @var PayPeriodScheduleUserListFactory $ppsulf */
			$ppsulf->getByPayPeriodScheduleId( $this->getID() );
			if ( $ppsulf->getRecordCount() > 0 ) {
				$ppsulf->bulkDelete( $this->getIDSByListFactory( $ppsulf ) ); //Can't use setDeleted() here as the PP schedule user table doesn't have a deleted column.
			}
			unset( $ppsulf );

			//Delete all pay periods related to this pay period schedule. This will not delete data within those pay periods though.
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$pplf->getByPayPeriodScheduleId( $this->getID() );
			if ( $pplf->getRecordCount() > 0 ) {
				foreach ( $pplf as $pp_obj ) {
					$pp_obj->setDeleted( true );
					if ( $pp_obj->isValid() ) {
						$pp_obj->Save();
					}
				}
			}
			unset( $pplf, $pp_obj );

			//Remove from User Defaults.
			$udlf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $udlf */
			$udlf->getByCompanyId( $this->getCompany() );
			if ( $udlf->getRecordCount() > 0 ) {
				foreach ( $udlf as $udf_obj ) {
					$udf_obj->setPayPeriodSchedule( TTUUID::getZeroID() );
					if ( $udf_obj->isValid() ) {
						$udf_obj->Save();
					}
				}
			}
			unset( $udlf, $udf_obj );
		}

		$this->CommitTransaction();

		return true;
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
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
						case 'anchor_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
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
						case 'total_users':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'start_week_day':
							$data[$variable] = Option::getByKey( $this->getStartWeekDay(), $this->getOptions( $variable ) );
							break;
						case 'shift_assigned_day':
							$data[$variable] = Option::getByKey( $this->getShiftAssignedDay(), $this->getOptions( $variable ) );
							break;
						case 'anchor_date':
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Pay Period Schedule' ), null, $this->getTable(), $this );
	}

	/**
	 * Checks if pay periods past their transaction dates have not been closed and sends a notification to users that need to be notified.
	 * @return void
	 */
	static function checkPayPeriodClosed() {
		if ( DEMO_MODE == false && PRODUCTION == true ) {
			$per_user_check_function = function ( $u_obj ) { /* @var UserFactory $u_obj */
				if ( $u_obj->getStatus() == 10 && $u_obj->getPermissionObject()->Check( 'pay_period_schedule', 'enabled', $u_obj->getId(), $u_obj->getCompany() ) && $u_obj->getPermissionObject()->Check( 'pay_period_schedule', 'view', $u_obj->getId(), $u_obj->getCompany() ) ) {
					return ['status' => true, 'notification_data' => [] ];
				}

				return ['status' => false, 'notification_data' => [] ];
			};

			$per_company_check_function = function ( $c_obj ) { /* @var CompanyFactory $c_obj */
				$retval = ['status' => false, 'notification_data' => [] ];

				if ( $c_obj->getStatus() == 10 ) { //10=Active
					$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
					$pplf->getByCompanyIdAndStatusAndTransactionDate( $c_obj->getId(), [ 10, 12, 30 ], TTDate::getBeginDayEpoch( time() ) ); //Open or Locked or Post Adjustment pay periods.
					if ( $pplf->getRecordCount() > 0 ) {
						foreach ( $pplf as $pp_obj ) {
							if ( is_object( $pp_obj->getPayPeriodScheduleObject() ) && $pp_obj->getPayPeriodScheduleObject()->getCreatedDate() < ( time() - ( 86400 * 40 ) ) ) { //Ignore pay period schedules newer than 40 days. They automatically start being closed after 45 days.
								$retval['status'] = true;
								break;
							}
						}
					}
				}

				return $retval;
			};

			$notification_data = [
					'type_id'        => 'pay_period',
					'object_id'      => TTUUID::getNotExistID( 1090 ),
					'object_type_id' => 0,
					'title_short'    => TTi18n::getText( 'WARNING: Pay periods have not been closed.' ),
					'body_short'     => TTi18n::getText( 'Pay periods past their transaction date have not been closed yet. It\'s critical that these pay periods are closed to prevent data loss, click here to close them now.' ),
					'body_long_html' => TTi18n::getText( 'Pay periods past their transaction date have not been closed yet. It\'s critical that these pay periods are closed to prevent data loss, click here to close them now.' ), //Use this to append email footer.
					'payload'        => [ 'link' => Misc::getURLProtocol() . '://' . Misc::getHostName() . Environment::getDefaultInterfaceBaseURL() . 'html5/#!m=PayPeriods' ],
			];

			Notification::sendNotificationToAllUsers( 80, $per_user_check_function, $per_company_check_function, $notification_data, ( 7 * 86400 ) ); //This is run from maintenance jobs and has a per company check, so it should go to all companies.
		}
	}

}

?>
