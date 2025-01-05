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
 * @package Modules\Notification
 */
class NotificationFactory extends Factory {
	protected $table = 'notification';

	public $user_obj = null;
	public $user_preference_notification_obj = null;
	public $device_ids = null;

	/**
	 * @var bool
	 */
	private $enable_email_notification;

	/**
	 * @var bool
	 */
	private $enable_send_notification;

	/**
	 * @var bool
	 */
	private $enable_push_notification;

	/**
	 * @var bool
	 */
	private $enable_save_notification = true;

	/**
	 * @var bool
	 */
	private $is_background_notification = false;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'sent_status_id' )->setFunctionMap( 'SentStatus' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'priority_id' )->setFunctionMap( 'Priority' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'acknowledged_type_id' )->setFunctionMap( 'AcknowledgedType' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'acknowledged_status_id' )->setFunctionMap( 'AcknowledgedStatus' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'varchar' ),
							TTSCol::new( 'object_type_id' )->setFunctionMap( 'ObjectType' )->setType( 'integer' ),
							TTSCol::new( 'object_id' )->setFunctionMap( 'Object' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'sent_device_id' )->setFunctionMap( 'SentDevice' )->setType( 'integer' ),
							TTSCol::new( 'effective_date' )->setFunctionMap( 'EffectiveDate' )->setType( 'timestamptz' )->setIsNull( false ),
							TTSCol::new( 'title_short' )->setFunctionMap( 'TitleShort' )->setType( 'varchar' ),
							TTSCol::new( 'title_long' )->setFunctionMap( 'TitleLong' )->setType( 'varchar' ),
							TTSCol::new( 'sub_title_short' )->setFunctionMap( 'SubTitleShort' )->setType( 'varchar' ),
							TTSCol::new( 'body_short_text' )->setFunctionMap( 'BodyShortText' )->setType( 'text' ),
							TTSCol::new( 'body_long_text' )->setFunctionMap( 'BodyLongText' )->setType( 'text' ),
							TTSCol::new( 'body_long_html' )->setFunctionMap( 'BodyLongHtml' )->setType( 'text' ),
							TTSCol::new( 'payload_data' )->setFunctionMap( 'PayloadData' )->setType( 'json' ),
							TTSCol::new( 'time_to_live' )->setFunctionMap( 'TimeToLive' )->setType( 'integer' ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_notification' )->setLabel( TTi18n::getText( 'Notification' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'type_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APINotification' )->setMethod( 'getOptions' )->setArg( 'notification_type' ) ),
											TTSField::new( 'created_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Date' ) ),
											TTSField::new( 'title_long' )->setType( 'text' )->setLabel( TTi18n::getText( 'Title' ) ),
											TTSField::new( 'body_long_text' )->setType( 'textarea' )->setLabel( TTi18n::getText( 'Message' ) ),
									)
							)
					)
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'object_type_id' )->setType( 'numeric_list' )->setColumn( 'a.object_type_id' )->setMulti( true ),
							TTSSearchField::new( 'object_id' )->setType( 'uuid_list' )->setColumn( 'a.object_id' )->setMulti( true ),
							TTSSearchField::new( 'title_short' )->setType( 'text' )->setColumn( 'a.title_short' ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'sent_status_id' )->setType( 'numeric_list' )->setColumn( 'a.sent_status_id' )->setMulti( true ),
							TTSSearchField::new( 'sent_device_id' )->setType( 'numeric_list' )->setColumn( 'a.sent_device_id' )->setMulti( true ),
							TTSSearchField::new( 'type_id' )->setType( 'text_list' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'acknowledged_status_id' )->setType( 'numeric_list' )->setColumn( 'a.acknowledged_status_id' )->setMulti( true ),
							TTSSearchField::new( 'effective_date' )->setType( 'timestamp' )->setColumn( 'a.effective_date' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APINotification' )->setMethod( 'getNotification' )
									->setSummary( 'Get notification records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APINotification' )->setMethod( 'setNotification' )
									->setSummary( 'Add or edit notification records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APINotification' )->setMethod( 'deleteNotification' )
									->setSummary( 'Delete notification records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APINotification' )->setMethod( 'getNotification' ) ),
											   ) ),
					)
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param array $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'notification_type':
			case 'type':
				$retval = [
					//Notification Types
					'system'                    	          => TTi18n::gettext( 'System' ), //IE - Database schema out of date, system requirments. Invisible and not in preferences, system only.

					'reminder_punch_normal_in'  	          => TTi18n::gettext( 'Punch Reminder - Start Shift' ),
					'reminder_punch_normal_out' 	          => TTi18n::gettext( 'Punch Reminder - End Shift' ),
					'reminder_punch_transfer' 	              => TTi18n::gettext( 'Punch Reminder - Transfer' ), //Reminder if they haven't punched in X seconds.
					'reminder_punch_break_out'  	          => TTi18n::gettext( 'Punch Reminder - Start Break' ),
					'reminder_punch_break_in'   	          => TTi18n::gettext( 'Punch Reminder - End Break' ),
					'reminder_punch_lunch_out'  	          => TTi18n::gettext( 'Punch Reminder - Start Lunch' ),
					'reminder_punch_lunch_in'   	          => TTi18n::gettext( 'Punch Reminder - End Lunch' ),

					'punch'                                   => TTi18n::gettext( 'Punches' ), //Punch notices like when an error occurs from a mobile app and the punch can't be saved.

					'reminder_pay_period_transaction_date'    => TTi18n::gettext( 'Payroll Reminder - Transaction Date' ),
					'payment_services'    					  => TTi18n::gettext( 'TimeTrex Payment Services' ), //Payment Services errors and communications

					'exception_own_critical'                  => TTi18n::gettext( 'Exceptions (Critical)' ),
					'exception_own_high'                      => TTi18n::gettext( 'Exceptions (High)' ),
					'exception_own_medium'                    => TTi18n::gettext( 'Exceptions (Medium)' ),
					'exception_own_low'                       => TTi18n::gettext( 'Exceptions (Low)' ),

					'exception_child_critical'                => TTi18n::gettext( 'Subordinate Exceptions (Critical)' ),
					'exception_child_high'                    => TTi18n::gettext( 'Subordinate Exceptions (High)' ),
					'exception_child_medium'                  => TTi18n::gettext( 'Subordinate Exceptions (Medium)' ),
					'exception_child_low'                     => TTi18n::gettext( 'Subordinate Exceptions (Low)' ),

					'request'                                 => TTi18n::gettext( 'Requests' ), //Only for employees who submitted the request, or who the request belongs too.
					'request_authorize'                       => TTi18n::gettext( 'Request Authorizations' ), //Only for notifications to superiors when a request is pending authorization.

					'timesheet_verify'                        => TTi18n::gettext( 'TimeSheet Verifications' ), //Only for employees who submitted the timesheet or who the timesheet belongs too.
					'timesheet_authorize'                     => TTi18n::gettext( 'TimeSheet Authorizations' ), //Only for notifications to superiors when a request is pending authorization.

					'schedule'                                => TTi18n::gettext( 'Schedule' ),
					'message'                                 => TTi18n::gettext( 'Messages' ),

					'pay_stub'                                => TTi18n::gettext( 'Pay Stubs' ),
					'pay_period'                              => TTi18n::gettext( 'Pay Periods' ),

					'expense_verify'                          => TTi18n::gettext( 'Expenses' ),  //Only for employees who submitted the expense or who the expense belongs too.
					'expense_authorize'                       => TTi18n::gettext( 'Expense Authorizations' ), //Only for notifications to superiors when a expense is pending authorization.

					'job_application_manager'                 => TTi18n::gettext( 'Job Applications' ), //When new job applications arrive, goes to the Job Vacancy Manager.

					'payroll_remittance_agency_event'         => TTi18n::gettext( 'Remittance Agency Events' ),
					'government_document'                     => TTi18n::gettext( 'Government Documents' ),
				];
				break;
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'Unread' ),
						20 => TTi18n::gettext( 'Read' ),
				];
				break;
			case 'sent_status':
				$retval = [
						10  => TTi18n::gettext( 'Pending' ), //Pending is postdated and retry upon failure attempts.
						50  => TTi18n::gettext( 'Fail' ),    //Hard fail after all retry attempts.
						100 => TTi18n::gettext( 'Success' ), //Hard success immediately or after all retry changes.
				];
				break;
			case 'priority_id':
			case 'priority':
				$retval = [
						1  => TTi18n::gettext( 'Critical' ), //Do everything we can to get the user attention, including ring on their phone? Likely just used for reminders to punch back in.
						2  => TTi18n::gettext( 'High' ),
						5  => TTi18n::gettext( 'Normal' ),
						10 => TTi18n::gettext( 'Low' ),
				];
				break;
			case 'acknowledged_type':
				$retval = [
						10  => TTi18n::gettext( 'Needs' ),
						100 => TTi18n::gettext( 'Not Needs' ),
				];
				break;
			case 'acknowledged_status':
				$retval = [
						10  => TTi18n::gettext( 'No' ),
						100 => TTi18n::gettext( 'Yes' ),
				];
				break;
			case 'object_type': //Link to the specific object factory by object_id uuid.
				$retval = [
							0   => 'System',

							10  => 'ExceptionFactory',
							20  => 'PayStubFactory',
							//30  => '',
							50  => 'RequestFactory',
							60  => 'GovernmentDocumentFactory',
							70  => 'JobApplicationFactory',
							80  => 'MessageControlFactory',
							90  => 'PayPeriodTimeSheetVerifyFactory',
							100 => 'PayrollRemittanceAgencyEventFactory',
							110 => 'UserExpenseFactory',
							120 => 'PunchFactory',
							130 => 'ScheduleFactory',
							140 => 'PayPeriodScheduleFactory',
							150 => 'PayPeriodFactory'
				];
				break;
			case 'devices':
				$retval = [
					//Used as a bitmask to store user notification preference.

					//1			=> TTi18n::gettext( '' ),
					//2			=> TTi18n::gettext( '' ),
					4			=> TTi18n::gettext( 'Web Push' ),
					//8 		=> TTi18n::gettext( '' ),
					//16  		=> TTi18n::gettext( '' ),
					//32 		=> TTi18n::gettext( '' ),
					//64 		=> TTi18n::gettext( '' ),
					//128 		=> TTi18n::gettext( '' ),
					256 		=> TTi18n::gettext( 'Email (Work)' ),
					512 		=> TTi18n::gettext( 'Email (Home)' ),
					//1024		=> TTi18n::gettext( '' ),
					//2048 		=> TTi18n::gettext( '' ),
					//4096 		=> TTi18n::gettext( '' ),
					//8192 		=> TTi18n::gettext( '' ),
					//16384 	=> TTi18n::gettext( '' ),
					32768 		=> TTi18n::gettext( 'App Push Notification' ),
					//65536 	=> TTi18n::gettext( '' ),
					//131072 	=> TTi18n::gettext( '' ),
					//262144 	=> TTi18n::gettext( '' ),
					//1048576 	=> TTi18n::gettext( '' ),
					//2097152 	=> TTi18n::gettext( '' ),
					//4194304 	=> TTi18n::gettext( 'SMS (Work)' ),
					//8388608 	=> TTi18n::gettext( 'SMS (Home)' ),
					//16777216 	=> TTi18n::gettext( '' ),
					//33554432 	=> TTi18n::gettext( '' ),
				];
				break;
			case 'columns':
				$retval = [
						//'-1200-title_short'        => TTi18n::gettext( 'Title (Short)' ),
						'-1202-title_long'        => TTi18n::gettext( 'Title' ),
						'-1210-body_short_text'   => TTi18n::gettext( 'Message' ),
						'-1220-notification_type' => TTi18n::gettext( 'Type' ),
						'-1230-priority' 		  => TTi18n::gettext( 'Priority' ),
						'-2010-effective_date'    => TTi18n::gettext( 'Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'title_long',
						'priority',
						'notification_type',
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
				'id'                     => 'ID',
				'user_id'				 => 'User',
				'type_id'                => 'Type',
				'notification_type'		 => false,
				'status_id'              => 'Status',
				'sent_status_id'         => 'SentStatus',
				'priority_id'            => 'Priority',
				'priority'				 => false,
				'acknowledged_type_id'   => 'AcknowledgedType',
				'acknowledged_status_id' => 'AcknowledgedStatus',
				'object_type_id'         => 'ObjectType',
				'object_id'              => 'Object',
				'effective_date'         => 'EffectiveDate',
				'title_short'            => 'TitleShort',
				'title_long'             => 'TitleLong',
				'sub_title_short'        => 'SubTitleShort',
				'body_short_text'        => 'BodyShortText',
				'body_long_text'         => 'BodyLongText',
				'body_long_html'         => 'BodyLongHtml',
				'payload_data'           => 'PayloadData',
				'time_to_live'           => 'TimeToLive',
				'sent_device_id'		 => 'SentDevice',
				'deleted'				 => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return int
	 */
	function getSentStatus() {
		return $this->getGenericDataValue( 'sent_status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSentStatus( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'sent_status_id', $value );
	}

	/**
	 * @return int
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
	 * @return bool|string
	 */
	function getType() {
		return $this->getGenericDataValue( 'type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return int
	 */
	function getPriority() {
		return $this->getGenericDataValue( 'priority_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPriority( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'priority_id', $value );
	}

	/**
	 * @return int
	 */
	function getAcknowledgedType() {
		return $this->getGenericDataValue( 'acknowledged_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAcknowledgedType( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'acknowledged_type_id', $value );
	}

	/**
	 * @return int
	 */
	function getAcknowledgedStatus() {
		return $this->getGenericDataValue( 'acknowledged_status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAcknowledgedStatus( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'acknowledged_status_id', $value );
	}

	/**
	 * @return int
	 */
	function getObjectType() {
		return $this->getGenericDataValue( 'object_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setObjectType( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'object_type_id', $value );
	}

	/**
	 * @return int
	 */
	function getObject() {
		return $this->getGenericDataValue( 'object_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setObject( $value ) {

		// NotExistID list for System Notifications
		// Purpose is to categorize the different system notifications and if a notification has already been sent

		// ID:     File:            		Used For:
		// 1000 -> APINotification  		License Issue
		// 1010 -> APINotification  		Currently in install mode
		// 1020 -> APINotification  		Maintenance jobs have not run
		// 1030 -> SystemSettingsFactory    System requirement check failed
		// 1040 -> Install					Application version does not match database version
		// 1050 -> Install					Version severely out of date
		// 1060 -> Install					Database schema out of sync
		// 1070 -> Install					System components out of date
		// 1080 -> Install					Hostname does not match config file
		// 1090 -> PayPeriodScheduleFactory Pay periods have not been closed
		// 1100 -> UserFactory				No email found for account
		// 1110 -> UnattentedUpgrade		Automatic upgrade failed
		// 1120 -> UnattentedUpgrade		Your instance has been upgraded


		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'object_id', $value );
	}

	/**
	 * @return int
	 */
	function getEffectiveDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'effective_date' );
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
	 * @param $epoch
	 * @return bool
	 */
	function setEffectiveDate( $value ) {
		return $this->setGenericDataValue( 'effective_date', $value );
	}

	/**
	 * @return int
	 */
	function getTitleShort() {
		return $this->getGenericDataValue( 'title_short' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTitleShort( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'title_short', $value );
	}

	/**
	 * @return int
	 */
	function getTitleLong() {
		return $this->getGenericDataValue( 'title_long' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTitleLong( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'title_long', $value );
	}

	/**
	 * @return int
	 */
	function getSubTitleShort() {
		return $this->getGenericDataValue( 'sub_title_short' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSubTitleShort( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'sub_title_short', $value );
	}

	/**
	 * @return int
	 */
	function getBodyShortText() {
		return $this->getGenericDataValue( 'body_short_text' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBodyShortText( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'body_short_text', $value );
	}

	/**
	 * @return int
	 */
	function getBodyLongText() {
		return $this->getGenericDataValue( 'body_long_text' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBodyLongText( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'body_long_text', $value );
	}

	/**
	 * @return int
	 */
	function getBodyLongHtml() {
		return $this->getGenericDataValue( 'body_long_html' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBodyLongHtml( $value ) {
		//Intended for only email.
		$value = trim( $value );

		return $this->setGenericDataValue( 'body_long_html', $value );
	}

	/**
	 * @return array
	 */
	function getPayloadData() {
		return json_decode( $this->getGenericDataValue( 'payload_data' ), true );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPayloadData( $value ) {
		if ( is_array( $value ) ) {
			return $this->setGenericDataValue( 'payload_data', json_encode( $value ) );
		}

		return $this->setGenericDataValue( 'payload_data', json_encode( [] ) );
	}

	/**
	 * @return int
	 */
	function getTimeToLive() {
		return $this->getGenericDataValue( 'time_to_live' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeToLive( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'time_to_live', $value );
	}

	/**
	 * @return array
	 */
	function getSentDevice() {
		$value = $this->getGenericDataValue( 'sent_device_id' );
		if ( $value !== false ) {
			//getArrayByBitMask returns array or false - other areas expect an array so forcing it to be an array.
			$retarr = Option::getArrayByBitMask( $value, $this->getOptions( 'devices' ) );

			if( $retarr !== false ) {
				return $retarr;
			}
		}

		return [];
	}


	/**
	 * @param $arr
	 * @return bool
	 */
	function setSentDevice( $arr ) {
		$value = Option::getBitMaskByArray( $arr, $this->getOptions( 'devices') );

		return $this->setGenericDataValue( 'sent_device_id', $value );
	}

	/**
	 * @return bool
	 */
	function isNotificationEnabledByUserPreference() {
		//10=user active. Checking if user is active and not terminated, else we don't store/send notification.
		if ( is_object( $this->getUserObject() ) && $this->getUserObject()->getStatus() == 10 && $this->getUserObject()->getEnableLogin() == true ) {
			if ( $this->getType() === 'system' ) {
				//System notifications are always enabled.
				return true;
			}

			//If all notifications are disabled by the user, return false. However system notifications are still allowed above.
			if ( $this->getUserObject()->getUserPreferenceObject()->getNotificationStatus() != 1 ) {
				Debug::Text( '  User notification status is disabled for all notifications...', __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}

			if ( is_object( $this->getUserPreferenceNotificationObject() ) ) {
				if ( $this->getUserPreferenceNotificationObject()->getStatus() == 10 ) {
					//Check to see if the user has permissions that are associated with this notification type.
					if ( empty( UserPreferenceNotificationFactory::filterUserNotificationPreferencesByPermissions( [ [ 'type_id' => $this->getType() ] ], $this->getUserObject() ) ) ) {
						$upnlf = TTnew( 'UserPreferenceNotificationListFactory' ); /** @var UserPreferenceNotificationListFactory $upnlf */
						$upnlf->getByCompanyIdAndUserIdAndType( $this->getUserObject()->getCompanyObject()->getId(), $this->getUserObject()->getId(), $this->getType() );
						if ( $upnlf->getRecordCount() == 1 ) {
							Debug::Text( '  User no longer has permissions to view notifications of this type, disabling preferences...', __FILE__, __LINE__, __METHOD__, 10 );

							//Notification is enabled, but user no longer has permissions that are linked too it, so disable the notification moving forward.
							$upn_obj = $upnlf->getCurrent(); /** @var UserPreferenceNotificationFactory $upnf */
							$upn_obj->setStatus( 20 );
							if ( $upn_obj->isValid() ) {
								$upn_obj->Save();
							}
						} else {
							Debug::Text( '  Notification record does not exist for this user... Type: '. $this->getType(), __FILE__, __LINE__, __METHOD__, 10 );
						}
					} else {
						return true;
					}
				} else {
					Debug::Text( '  All notifications are disabled for this user... Notification Preference ID: '. $this->getUserPreferenceNotificationObject()->getID() .' Type: '. $this->getType(), __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		} else {
			Debug::Text( '  User is not active or logins are disabled...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}


	/**
	 * @return object|bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return mixed|null
	 * @throws DBError
	 * @throws GeneralError
	 * @throws ReflectionException
	 */
	function getUserPreferenceNotificationObject() {
		if ( isset( $this->user_preference_notification_obj ) && is_object( $this->user_preference_notification_obj ) ) {
			return $this->user_preference_notification_obj;
		} else {
			$upnlf = TTnew( 'UserPreferenceNotificationListFactory' ); /* @var UserPreferenceNotificationListFactory $upnlf */
			$upnlf->getByCompanyIdAndUserIdAndType( $this->getUserObject()->getCompany(), $this->getUser(), $this->getType() );
			if ( $upnlf->getRecordCount() == 1 ) {
				$this->user_preference_notification_obj = $upnlf->getCurrent();

				return $this->user_preference_notification_obj;
			} else {
				Debug::text( '  Notification preferences not found ('. $upnlf->getRecordCount() .') for User ID: '. $this->getUser() .', using defaults for Type ID: '. $this->getType(), __FILE__, __LINE__, __METHOD__, 10 );

				//Create default preference notification record if it doesn't already exist for this user.
				// This also helps prevent having to do it from the install schema when new types are added.
				$upnf = TTnew( 'UserPreferenceNotificationFactory' ); /** @var UserPreferenceNotificationFactory $upnf */
				$upnf->setUser( $this->getUser() );
				$data = $upnf->getUserPreferenceNotificationTypeDefaultValues( null );

				if ( is_array( $data ) ) {
					foreach ( $data as $preference_notification_data ) {
						if ( $preference_notification_data['type_id'] == $this->getType() ) {
							unset( $preference_notification_data['id'] );

							$upnf->setStatus( $preference_notification_data['status_id'] );
							$upnf->setDevice( $preference_notification_data['device_id'] );
							$upnf->setType( $preference_notification_data['type_id'] );
							$upnf->setPriority( $preference_notification_data['priority_id'] );

							if ( $upnf->isValid() ) {
								$upnf->Save( false ); //Don't clear object so it can be returned.
								$this->user_preference_notification_obj = $upnf;

								return $this->user_preference_notification_obj;
							} else {
								//Check if there is a race condition and that the user preference record may have already been created by another process, therefore it already exists.
								//  This would cause a 'type_id' validation error, saying a record already exists for this user and type.
								if ( $upnf->Validator->isError('type_id') == true ) {
									Debug::text( '  Notification preference already exists when attempting to insert it, using in-memory default object instead...', __FILE__, __LINE__, __METHOD__, 10 );
									$this->user_preference_notification_obj = $upnf;

									return $this->user_preference_notification_obj;
								} else {
									Debug::text( '  ERROR: Unable to insert new notification preference due to validation error...', __FILE__, __LINE__, __METHOD__, 10 );
								}
							}
						}
					}
				}
			}
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
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return bool
	 */
	function getEnablePushNotification() {
		if ( isset( $this->enable_push_notification ) ) {
			return $this->enable_push_notification;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnablePushNotification( $bool ) {
		$this->enable_push_notification = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getIsBackgroundNotification() {
		if ( isset( $this->is_background_notification ) ) {
			return $this->is_background_notification;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setIsBackgroundNotification( $bool ) {
		$this->is_background_notification = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableSaveNotification() {
		if ( isset( $this->enable_save_notification ) ) {
			return $this->enable_save_notification;
		}

		return true;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableSaveNotification( $bool ) {
		$this->enable_save_notification = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableSendNotification() {
		if ( isset( $this->enable_send_notification ) ) {
			return $this->enable_send_notification;
		}

		return true;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableSendNotification( $bool ) {
		$this->enable_send_notification = (bool)$bool;

		return true;
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotification() {
		if ( isset( $this->enable_email_notification ) ) {
			return $this->enable_email_notification;
		}

		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setEnableEmailNotification( $bool ) {
		$this->enable_email_notification = (bool)$bool;

		return true;
	}

	/**
	 * @return array
	 */
	function getUserEmailAddresses() {
		$email_arr = [];

		//Check if email sending is enabled and notification has not already been sent by a previous partial success send attempt.
		if ( is_object( $this->getUserObject() ) ) {
			if ( in_array( 256, $this->getDeviceIds() ) && !in_array( 256, $this->getSentDevice() ) ) {
				if ( $this->getUserObject()->getWorkEmail() != '' && $this->getUserObject()->getWorkEmailIsValid() == true ) {
					$email_arr[] = Misc::formatEmailAddress( $this->getUserObject()->getWorkEmail(), $this->getUserObject() );
				}
			}

			if ( in_array( 512, $this->getDeviceIds() ) && !in_array( 512, $this->getSentDevice() ) ) {
				if ( $this->getUserObject()->getHomeEmail() != ''  && $this->getUserObject()->getHomeEmailIsValid() == true ) {
					$email_arr[] = Misc::formatEmailAddress( $this->getUserObject()->getHomeEmail(), $this->getUserObject() );
				}
			}
		}

		return $email_arr;
	}

	/**
	 * Sends notifications to users email addresses.
	 * @return bool
	 */
	function sendEmail() {
		$email_to_arr = $this->getUserEmailAddresses();

		if ( empty( $email_to_arr ) ) {
			Debug::Text( 'No valid emails found for user.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$from = '"'. APPLICATION_NAME . ' - ' . Option::getByKey( $this->getType(), $this->getOptions( 'type' ) ) . '" <' . Misc::getEmailLocalPart() . '@' . Misc::getEmailDomain() . '>';

		//Fall back subject and body text if getTitleLong and getBodyLongHtml are empty.
		if ( $this->getTitleLong() != '' ) {
			$subject = $this->getTitleLong();
		} else if ( $this->getTitleShort() != '' ) {
			$subject = $this->getTitleShort();
		} else {
			Debug::Text( 'No subject set for email.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $this->getBodyLongHtml() != '' ) {
			$body = $this->getBodyLongHtml();
		} else if ( $this->getBodyLongText() != '' ) {
			$body = nl2br( $this->getBodyLongText() ); //Since we always send HTML message below, make sure to convert newlines to <br> tags.
		} else if ( $this->getBodyShortText() != '' ) {
			$body = nl2br( $this->getBodyShortText() ); //Since we always send HTML message below, make sure to convert newlines to <br> tags.
		} else {
			Debug::Text( 'No subject set for email.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( getTTProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
			//This prevents spamassassin HTML_IMAGE_ONLY_32 rule from triggering at a default of score of 1 to 2.4
			//  Basically it checks if an HTML image exists with less than 3200 bytes of text in the entire email.
			$body_filler = '<br><br><br><br><div style="display: none"> ' . str_repeat( 'Filler Text Should Not Be Visible. Please Ignore. ', 60 ) . '</div>';
		} else {
			$body_filler = '';
		}

		//Replace variables like #email_sent_date# right before the email is actually sent, otherwise it might include the date when the email was initially *scheduled* to be sent.
		$body = str_replace( [ '#email_sent_date#', '#tracking_html#' ], [ date( 'r', TTDate::getTime() ), $body_filler .'<img src="'. Misc::getURLProtocol() . '://' . Misc::getHostName( true ) . Environment::getAPIBaseURL( 'json' ) .'/api.php?Class=APIAuthentication&Method=markNotificationAsRead&json='. urlencode( json_encode( [ $this->getUserObject()->getId(), $this->getObjectType(), $this->getObject() ] ) ) .'&MessageID='. TTUUID::generateUUID() .'" alt=""/>' ], $body );

		Debug::Text( 'Attempting to send email notification... ID: '. $this->getID() .' Subject: '. $subject, __FILE__, __LINE__, __METHOD__, 10 );

		$headers = [
				'From'    => $from,
				'Subject' => $subject,
				'X-TimeTrex-Notification-ID' => $this->getId(), //Allow for better tracking if we need to diagnose problems with emails.
				//Reply-To/Return-Path are handled in TTMail.
		];

		$mail = new TTMail();
		$mail->setTo( $email_to_arr );
		$mail->setHeaders( $headers );

		@$mail->getMIMEObject()->setHTMLBody( $body );
		$mail->setDefaultTXTBody(); //Makes the TXT body: "This email contains HTML content, please open in a HTML enabled email viewer."
		//$mail->getMIMEObject()->setTXTBody( strip_tags( str_ireplace( [ $body_filler, '<br>' ], [ '', "\n" ], $body ), [ 'a' ] ) ); //This allows them to see text bodies, but causes all kinds of problems with links and such. Likely should just not be supported.

		$mail->setBody( $mail->getMIMEObject()->get( $mail->default_mime_config ) );
		$retval = $mail->Send();

		if ( $retval == true ) {
			if ( $this->getUserObject()->getWorkEmail() != '' && $this->getUserObject()->getWorkEmailIsValid() == true ) {
				$this->setSentDevice( array_merge( $this->getSentDevice(), [ 256 ] ) );
			}

			if ( $this->getUserObject()->getHomeEmail() != '' && $this->getUserObject()->getHomeEmailIsValid() == true ) {
				$this->setSentDevice( array_merge( $this->getSentDevice(), [ 512 ] ) );
			}
		} else {
			$this->setSentStatus( 50 ); //Fail
			Debug::Text( 'Sending notification by email failed.', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;

		//if ( $retval == true ) {
		//	TTLog::addEntry( $this->getId(), 500, TTi18n::getText( 'Email Message to' ) . ': ' . implode( ', ', $email_to_arr ), null, $this->getTable() );
		//}
	}

	/**
	 * @return string
	 */
	static function addEmailFooter( $company_name = null ) {
		$preferences_url = '<a href="' . Misc::getURLProtocol() . '://' . Misc::getHostName( true ) . Environment::getDefaultInterfaceBaseURL() . '">' . TTi18n::gettext( 'Notification Preferences' ) . '</a>';

		$email_footer = "\n\n\n\n";

		if ( !empty( $company_name ) ) {
			//Could also add something like: "This email was sent to john.doe@gmail.com on behalf of ABC Company. "
			$email_footer .= TTi18n::gettext( 'This is a notification email sent on behalf of %1.', [ Misc::escapeHTML( $company_name ) ] ). "\n";
		}

		$email_footer .= TTi18n::gettext( 'To manage your %1 emails, please go to your %2.', [ APPLICATION_NAME, $preferences_url ] ) ."\n";
		$email_footer .= TTi18n::gettext( 'Email sent' ) . ': #email_sent_date#'."\n"; //Use a variable that is replaced immediately before the email is actually sent. Otherwise notifications creates today, to be sent 30 days from now will have a very incorrect date.
		$email_footer .= '#tracking_html#'."\n";

		return $email_footer;
	}

	/**
	 * Gets an array of device tokens that the user has, associated by device type - ios, android, browser.
	 * @param array $device_ids Filter device IDs
	 * @return array
	 */
	function getDeviceTokens( $device_ids = null ) {
		if ( $device_ids == null ) {
			$device_ids = $this->getDeviceIds();
		}

		if ( !is_array( $device_ids ) ) {
			$device_ids = [ $device_ids ];
		}

		$ndtfl = TTnew( 'NotificationDeviceTokenListFactory' ); /** @var NotificationDeviceTokenListFactory $ndtfl */
		$ndtfl->getByUserId( $this->getUser() );

		if ( $ndtfl->getRecordCount() > 0 ) {
			//Check if devices are enabled.
			foreach ( $ndtfl as $ndt_obj ) { /** @var NotificationDeviceTokenFactory $ndt_obj */
				if ( $ndt_obj->getPlatform() == 200 && in_array( 32768, $device_ids ) && !in_array( 32768, $this->getSentDevice() ) ) { //Check iOS
					$device_arr['ios'][] = $ndt_obj->getDeviceToken();
				} else if ( $ndt_obj->getPlatform() == 300 && in_array( 32768, $device_ids ) && !in_array( 32768, $this->getSentDevice() ) ) { //Check Android
					$device_arr['android'][] = $ndt_obj->getDeviceToken();
				} else if ( $ndt_obj->getPlatform() == 100 ) {
					if ( in_array( 4, $this->getDeviceIds() ) || in_array( 8, $device_ids ) && !in_array( 8, $this->getSentDevice() ) ) { //Check browser
						$device_arr['browser'][] = $ndt_obj->getDeviceToken();
					}
				}
			}
		}

		if ( isset( $device_arr ) ) {
			Debug::Arr( [$device_ids, $device_arr ], '  Device Filter/IDs to send notification too: ', __FILE__, __LINE__, __METHOD__, 10 );
			return $device_arr;
		}

		return [];
	}

	/**
	 * This overrides the users preferences on which devices to send notifications to.
	 * Primarily used for scenarios such as multifactor authentication, where we want to send a notification to mobile devices only.
	 * @param $ids
	 * @return bool
	 */
	function setDeviceIds( $ids ) {
		return $this->setGenericTempDataValue( 'device_ids', $ids );
	}

	/**
	 * @return array|null
	 */
	function getDeviceIds() {
		$device_ids = $this->getGenericTempDataValue( 'device_ids' );

		if ( is_array( $device_ids ) ) {
			return $device_ids;
		} else {
			if ( is_object( $this->getUserPreferenceNotificationObject() ) ) {
				$ids = $this->getUserPreferenceNotificationObject()->getDevice();
				$this->setGenericTempDataValue( 'device_ids', $ids );

				return $ids;
			}

			return [];
		}
	}

	/**
	 * Checks what devices are enabled and turns on setters for push notifications and emails.
	 * @return true
	 */
	function checkEnabledDevices() {
		//Check for browser or app push notification in device_ids.
		$device_ids = $this->getDeviceIds();

		if ( in_array( 4, $device_ids ) || in_array( 32768, $device_ids ) ) {
			$this->setEnablePushNotification( true );
		}

		//Check for email home or work notification in device_ids.
		if ( in_array( 256, $device_ids ) || in_array( 512, $device_ids ) ) {
			$this->setEnableEmailNotification( true );
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

		if ( $this->getDeleted() == false ) {
			if ( $this->getUser() != TTUUID::getZeroID() ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'user',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}

			if ( $this->getTitleShort() != '' ) {
				$this->Validator->isLength( 'title_short',
											$this->getTitleShort(),
											TTi18n::gettext( 'Short Title is invalid' ),
											1, 100
				);
			}

			if ( $this->getTitleLong() != '' ) {
				$this->Validator->isLength( 'title_long',
											$this->getTitleLong(),
											TTi18n::gettext( 'Long Title is invalid' ),
											1, 200
				);
			}

			if ( $this->getSubTitleShort() != '' ) {
				$this->Validator->isLength( 'sub_title_short',
											$this->getSubTitleShort(),
											TTi18n::gettext( 'Subtitle is invalid' ),
											1, 100
				);
			}

			// Type
			$this->Validator->inArrayKey( 'type',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);

			// Status
			if ( $this->getStatus() != '' ) {
				$this->Validator->inArrayKey( 'status',
											  $this->getStatus(),
											  TTi18n::gettext( 'Incorrect Status' ),
											  $this->getOptions( 'status' )
				);
			}

			// Sent Status
			if ( $this->getSentStatus() != '' ) {
				$this->Validator->inArrayKey( 'sent_status',
											  $this->getSentStatus(),
											  TTi18n::gettext( 'Incorrect Sent Status' ),
											  $this->getOptions( 'sent_status' )
				);
			}

			// Priority
			if ( $this->getPriority() != '' ) {
				$this->Validator->inArrayKey( 'priority',
											  $this->getPriority(),
											  TTi18n::gettext( 'Incorrect priority' ),
											  $this->getOptions( 'priority' )
				);
			}

			// Effective Date (must always be specified)
			$this->Validator->isDate( 'effective_date',
									  $this->getEffectiveDate(),
									  TTi18n::gettext( 'Invalid effective date' )
			);

			// Time to live
			if ( $this->getTimeToLive() != '' ) {
				$this->Validator->isNumeric( 'time_to_live',
											 $this->getTimeToLive(),
											 TTi18n::gettext( 'Invalid time to live' )
				);
			}

			// Acknowledged
			if ( $this->getAcknowledgedStatus() != '' ) {
				$this->Validator->inArrayKey( 'acknowledged_status',
											  $this->getAcknowledgedStatus(),
											  TTi18n::gettext( 'Incorrect acknowledged status' ),
											  $this->getOptions( 'acknowledged_status' )
				);
			}

			// Acknowledged Type
			if ( $this->getAcknowledgedType() != '' ) {
				// Acknowledged Type
				$this->Validator->inArrayKey( 'acknowledged_type',
											  $this->getAcknowledgedType(),
											  TTi18n::gettext( 'Incorrect acknowledged type' ),
											  $this->getOptions( 'acknowledged_type' )
				);
			}

			// Verify JSON is valid format.
			//if ( $this->getPayloadData() != '' ) {
			//	json_decode( $this->getPayloadData() );
			//	$this->Validator->isTrue( 'payload',
			//			                  (json_last_error() == JSON_ERROR_NONE),
			//							  TTi18n::gettext( 'JSON Payload data for notification was in an invalid format.' ) );
			//}

			//Make sure notification allowed by user preference... This is already handled in Notification::sendNotification(), so lets remove it from here for now.
			//$this->Validator->isTrue( 'preference',
			//						  $this->isNotificationEnabledByUserPreference(),
			//						  TTi18n::gettext( 'Notification not allowed by employee notification preferences.' ) );

			// Object Type
			$this->Validator->inArrayKey( 'object_type',
										  $this->getObjectType(),
										  TTi18n::gettext( 'Incorrect object type' ),
										  $this->getOptions( 'object_type' )
			);
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		if ( $this->getTitleLong() == '' ) {
			$this->setTitleLong( $this->getTitleShort() );
		}

		if ( $this->getTitleShort() == '' ) {
			$this->setTitleShort( $this->getTitleLong() );
		}

		if ( $this->getBodyLongText() == '' ) {
			$this->setBodyLongText( $this->getBodyShortText() );
		}

		if ( $this->getBodyShortText() == '' ) {
			$this->setBodyShortText( $this->getBodyLongText() );
		}

		if ( $this->getStatus() == '' ) {
			$this->setStatus( 10 ); //10=Unread
		}

		if ( $this->getSentStatus() == '' ) {
			$this->setSentStatus( 10 ); //10=Pending
		}

		if ( $this->getAcknowledgedType() == '' ) {
			$this->setAcknowledgedType( 10 ); //10=Needs Acknowledgement
		}

		if ( $this->getAcknowledgedStatus() == '' ) {
			$this->setAcknowledgedStatus( 10 ); //10=No
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getDeleted() == true ) {
			Debug::Text( 'Push notification is being deleted by the user, and we do not need to modify it or send it to users', __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		if( $this->getEnableSendNotification() !== true ) {
			//Checks if the notification should be sent and was moved from postSave() to here to reduce amount of queries we need.
			Debug::Text( 'Not sending push notification as it was disabled, or already sent through the job queue.', __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		if ( $this->getEffectiveDate() != '' && $this->getEffectiveDate() > TTDate::getTime() ) {
			//This notification should be sent in the future and not now.
			Debug::Text( 'Not sending push notification as effective date is in the future.', __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		if ( empty( $this->getDeviceIds() ) ) {
			//User notification preferences have no devices turned on for sending this type of notification.
			Debug::Text( 'Not sending push notifications because no devices turned on for this type of notification.', __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		}

		if ( $this->getEnableSendNotification() == true ) {
			$this->queueSendNotification();
		}

		return true;
	}

	/**
	 * Since we need to be able to handle queued real-time notifications that don't get stored in the notification table, this function must accept all necessary data to send the notification, other than the device IDs
	 * @param $object_array_data array
	 * @param $object_array_tmp_data array
	 * @param $enable_save_notification bool
	 * @param $is_background_notification bool
	 * @return true
	 * @throws DBError
	 * @throws GeneralError
	 */
	static function sendNotificationForJobQueue( $object_array_data, $object_array_tmp_data, $enable_save_notification, $is_background_notification ) {
		Debug::Text( 'Sending queued Push Notifications for: '. $object_array_data['id'] ?? 'N/A' .' Save: '. (int)$enable_save_notification .' Background: '. (int)$is_background_notification, __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr( $object_array_data, 'Object Array Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( PRODUCTION == false ) {
			Debug::Text( 'Not in production mode, not sending notifications...', __FILE__, __LINE__, __METHOD__, 10 );
			return true; //Return true so job queue doesn't retry this job.
		}

		$nf = TTnew( 'NotificationFactory' );
		$nf->data = $nf->old_data = $object_array_data; //We must send the raw $this->data so the system job queue runner can replicate the exact object on its end. Otherwise things like users date format preferences will cause SQL errors and such.
		$nf->tmp_data = $object_array_tmp_data; //This is needed for override of device IDs.

		$nf->setEnableSaveNotification( $enable_save_notification );
		$nf->setIsBackgroundNotification( $is_background_notification );

		//This must come after the getDeleted() == true check in preSave(), otherwise a user could be deleted and failures could occur afterwards.
		//  It should also come after empty( $this->getDeviceIds() ) in preSave() as well, since this requires device IDs to exist to actually do anything.
		$nf->checkEnabledDevices();

		//**NOTE: Many notifications are post-dated, so they are based on the users preferences when first created and not currently checked again before they are finally sent.
		if ( $nf->getEnablePushNotification() ) {
			$nf->sendNotification();
		} else {
			Debug::Text( 'Not sending push notification as it was disabled. Mobile push notification was not in requested device ids.', __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( $nf->getEnableEmailNotification() ) {
			$nf->sendEmail();
		} else {
			Debug::text( 'Not sending email notification as it was disabled. Home and work email was not in requested device ids.', __FILE__, __LINE__, __METHOD__, 10 );
		}

		if ( $nf->getSentStatus() == 50 ) { //Failure
			Debug::Text( 'Attempting to send notification failed...', __FILE__, __LINE__, __METHOD__, 10 );

			if ( $nf->getPayloadData() == '' ) {
				$payload = [];
			} else {
				$payload = $nf->getPayloadData();
			}

			//On failure we record when and how often it has failed for future attempts.
			if ( isset( $payload['retries'] ) ) {
				$payload['retries']['attempts']++;
				$payload['retries']['last_attempt_date'] = TTDate::getTime();
			} else {
				$payload['retries'] = [ 'attempts' => 1, 'last_attempt_date' => TTDate::getTime() ];
			}

			if ( $payload['retries']['attempts'] < 7 ) {
				$nf->setSentStatus( 10 ); //Attempt to send again.
			}

			$nf->setPayloadData( $payload );
		} else {
			Debug::Text( 'Notification created successfully.', __FILE__, __LINE__, __METHOD__, 10 );
			$nf->setSentStatus( 100 ); //Success as nothing went wrong.
		}

		$nf->setEnableSendNotification( false ); //We don't want to send it again in preSave() below, since we just sent it.

		if ( $nf->getIsBackgroundNotification() == false && $nf->getEnableSaveNotification() == true ) {
			if ( $nf->isValid() == true ) {
				Debug::Text( 'Notification ID: ' . $nf->getId() . ' Effective Date: ' . TTDate::getDate( 'DATE+TIME', $nf->getEffectiveDate() ), __FILE__, __LINE__, __METHOD__, 10 );
				$nf->save( true, true );
			} else {
				Debug::Text( 'WARNING: Notification failed validation!', __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( 'Not saving notification, sending it directly instead... Notification ID: ' . $nf->getId() . ' Effective Date: ' . TTDate::getDate( 'DATE+TIME', $nf->getEffectiveDate() ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function queueSendNotification() {
		//Since its queued above, mark it as sent. The background job could mark it as failed if it fails to send.
		//  This should be done before its sent to the job queue so it has this information too.
		$this->setSentStatus( 100 );

		$this->checkEnabledDevices(); //This must come before getEnablePushNotification() and getEnableEmailNotification().
		if ( $this->getEnablePushNotification() == true || $this->getEnableEmailNotification() == true ) {
			//Sending push notifications can be slow in some cases, as its dependent on the 3rd party service.
			//  Therefore to avoid the user having to wait for the notification to be sent, we will add it to the job queue and let the job queue handle it.
			//  This was manifesting itself in request/timesheet authorizations sometimes taking minutes to "save", because it was waiting on the notification to be sent, which itself was waiting on the 3rd party service to respond.
			//
			//These should be one of the highest priority jobs, so they get sent out as soon as possible.
			//  **NOTE: We must send the raw $this->data so the system job queue runner can replicate the exact object on its end. Otherwise things like users date format preferences will cause SQL errors and such.
			SystemJobQueue::Add( TTi18n::getText( 'Sending Notification' ), $this->getId(), 'NotificationFactory', 'sendNotificationForJobQueue', [ $this->data, $this->tmp_data, $this->getEnableSaveNotification(), $this->getIsBackgroundNotification() ], 5, null, null, $this->getUser() );
		} else {
			Debug::Text( 'Not queuing sendNotification as push and email notifications are disabled.', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function sendNotification() {
		$devices = $this->getDeviceTokens( $this->getDeviceIds() );

		if ( empty( $devices ) ) {
			Debug::Text( 'No devices to send push notifications to!', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		} else {
			Debug::Arr( $this->getDeviceIds(), 'Attempting to send push notification to devices. ID: '. $this->getID() .' Title: '. $this->getTitleShort(), __FILE__, __LINE__, __METHOD__, 10 );
		}

		$ttsc = new TimeTrexSoapClient();

		if ( !empty( $devices['browser'] ) ) {
			//Web browsers
			if ( $this->getPriority() <= 2 ) {
				$priority = 'high';
			} else if ( $this->getPriority() == 10 ) {
				$priority = 'low';
			} else {
				$priority = 'normal';
			}

			$retval = $ttsc->sendNotification( [ 'browser' => $devices['browser'] ], $this->getTitleLong(), $this->getBodyShortText(), $this->convertPayloadToBrowser( $this->getPayloadData() ), $this->getTimeToLive(), $priority );
			if ( is_array( $retval ) && isset( $retval['browser'] ) && is_array( $retval['browser'] ) ) {
				$this->setSentDevice( array_merge( $this->getSentDevice(), [ 4 ] ) );
				Debug::Text( 'Sending push notification to browser device successful.', __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				$this->setSentStatus( 50 ); //Fail
				Debug::Arr( $retval, 'Sending push notification to a browser failed.', __FILE__, __LINE__, __METHOD__, 10 );
			}

			//Check and delete any invalid device tokens from response.
			if ( is_array( $retval ) && is_array( $retval['browser'] ) ) {
				$this->checkInvalidTokens( $retval['browser'] );
			}
			unset( $priority );
		}

		if ( !empty( $devices['ios'] ) ) {
			//iOS devices

			// Priority=1 just needs to send the special notification sound that is 30 seconds long and thats about all we can do. If we send a background notification it may never make it to the device.
			$retval = $ttsc->sendNotification( [ 'ios' => $devices['ios'] ], $this->getTitleShort(), $this->getBodyShortText(), $this->convertPayloadToIos( $this->getPayloadData() ), $this->getTimeToLive(), $this->getPriority() );
			if ( is_array( $retval ) && isset( $retval['ios'] ) && is_array( $retval['ios'] ) ) {
				$this->setSentDevice( array_merge( $this->getSentDevice(), [ 32768 ] ) );
				Debug::Text( 'Sending push notification to iOS device successful.', __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				$this->setSentStatus( 50 ); //Fail
				Debug::Arr( $retval, 'Sending push notification to iOS device failed.', __FILE__, __LINE__, __METHOD__, 10 );
			}

			//Check and delete any invalid device tokens from response.
			if ( is_array( $retval ) && is_array( $retval['ios'] ) ) {
				$this->checkInvalidTokens( $retval['ios'] );
			}
		}

		if ( !empty( $devices['android'] ) ) {
			//Android devices

			//For Android devices only: If its a priority=1 (critical) notification, we need to switch it to a background notification that the app will convert to foreground itself
			// and use its special "must get attention" settings (ie: alarm clock/persistent/custom sound) that can't be done remotely or through FCM.
			//  **Unfortunately most of those "must get attention" strategies didn't work out for one reason or another. Still need to use background notification so the app can work some magic though.
			if ( $this->getPriority() == 1 ) { //1=Critical
				Debug::Text( '  Critical priority, switching to background notification for mobile app. Title: '. $this->getTitleShort(), __FILE__, __LINE__, __METHOD__, 10 );
				$tmp_payload_data = $this->getPayloadData();
				$tmp_payload_data['timetrex']['title_short'] = $this->getTitleShort(); //Add title onto payload so it can be extracted in the app.
				$tmp_payload_data['timetrex']['body_short'] = $this->getBodyShortText(); //Add body onto payload so it can be extracted in the app.

				//Background notification (or data only message) must not have a title or body.
				//Now that the title and body was injected into the payload, don't pass it through to the notification, but also don't modify the notification as it still needs to be saved.
				$retval = $ttsc->sendNotification( [ 'android' => $devices['android'] ], '', '', $this->convertPayloadToAndroid( $tmp_payload_data ), $this->getTimeToLive(), $this->getPriority() );
				unset($tmp_payload_data);
			} else {
				$retval = $ttsc->sendNotification( [ 'android' => $devices['android'] ], $this->getTitleShort(), $this->getBodyShortText(), $this->convertPayloadToAndroid( $this->getPayloadData() ), $this->getTimeToLive(), $this->getPriority() );
			}

			if ( is_array( $retval ) && isset( $retval['android'] ) && is_array( $retval['android'] ) ) {
				$this->setSentDevice( array_merge( $this->getSentDevice(), [ 32768 ] ) );
				Debug::Text( 'Sending push notification to Android device successful.', __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				$this->setSentStatus( 50 ); //Fail
				Debug::Arr( $retval, 'Sending push notification to Android device failed.', __FILE__, __LINE__, __METHOD__, 10 );
			}

			//Check and delete any invalid device tokens from response.
			if ( is_array( $retval ) && is_array( $retval['android'] ) ) {
				$this->checkInvalidTokens( $retval['android'] );
			}
		}

		return true;
	}

	/**
	 * Checks for errors in response from proxy and deletes invalid tokens
	 * @param $response
	 */
	function checkInvalidTokens( $response ) {
		if ( is_array( $response ) && !empty( $response ) ) {
			Debug::Arr( $response, 'Response from push notification service: ', __FILE__, __LINE__, __METHOD__, 10);
			$ndtlf = TTnew('NotificationDeviceTokenListFactory' ); /** @var NotificationDeviceTokenListFactory $ndtlf */

			//Loop through sent tokens and match them to the response to see if they are invalid.
			foreach ( $response as $device_token => $result ) {
				if ( is_string( $device_token ) && $result === 'unregister_token' ) {
					Debug::Text( '  Deleting unregistered device. Result: '. $result, __FILE__, __LINE__, __METHOD__, 10 );
					$ndtlf->deleteUnregisteredDeviceTokenByUserIdAndDeviceToken( $this->getUser(), $device_token );
				}
			}
		}

		return true;
	}

	/**
	 * @param $payload
	 * @return mixed
	 */
	function convertPayloadToBrowser( $payload ) {
		if ( !empty( $payload['link'] ) ) {
			$payload['click_action'] = $payload['link'];
		}

		//Browser push payload does not need two references to link.
		unset( $payload['link'] );
		unset( $payload['view_id'] );

		return $payload;
	}

	function convertPayloadEventToMobileApp( $payload ) {
		if ( !isset( $payload['uri'] ) ) {
			$payload['uri'] = '/dashboard/view'; //Default to dashboard unless otherwise specified.
		}

		//[ 'timetrex' => [ 'event' => [ [ 'type' => 'open_view', 'data' => [], 'view_name' => 'InOut' ] ] ] ]
		if ( isset( $payload['timetrex'] ) && isset( $payload['timetrex']['event'] ) && isset( $payload['timetrex']['event'][0]['type'] ) ) {
			if ( $payload['timetrex']['event'][0]['type'] == 'open_view' ) {
				$action_name = $payload['timetrex']['event'][0]['action_name'] ?? 'list';

				$view_name = $payload['timetrex']['event'][0]['view_name'];
				switch ( $view_name ) {
					case 'InOut':
						$payload['uri'] = '/punch/'. $action_name;
						break;
					case 'Request':
						$payload['uri'] = '/request/'. $action_name;
						if ( isset( $payload['timetrex']['event'][0]['data']['id'] ) ) {
							$payload['uri'] .= '?id='. $payload['timetrex']['event'][0]['data']['id'];
						}
						break;
					case 'MessageControl':
					case 'Message':
						$payload['uri'] = '/message/'. $action_name;
						if ( isset( $payload['timetrex']['event'][0]['data']['id'] ) ) {
							$payload['uri'] .= '?id='. $payload['timetrex']['event'][0]['data']['id'];
						}
						break;
					case 'TimeSheet':
						$payload['uri'] = '/timesheet/view';
						if ( isset( $payload['timetrex']['event'][0]['data']['date'] ) ) {
							$payload['uri'] .= '?date='. $payload['timetrex']['event'][0]['data']['date'];
						}
						break;
					case 'PayStub':
						$payload['uri'] = '/pay_stub/'. $action_name;
						if ( isset( $payload['timetrex']['event'][0]['data']['id'] ) ) {
							$payload['uri'] .= '?id='. $payload['timetrex']['event'][0]['data']['id'];
						}
						break;
					case 'GovernmentDocument':
						$payload['uri'] = '/government_document/'. $action_name;
						if ( isset( $payload['timetrex']['event'][0]['data']['id'] ) ) {
							$payload['uri'] .= '?id='. $payload['timetrex']['event'][0]['data']['id'];
						}
						break;
				}
				Debug::Arr( $payload, 'Converted Payload for Mobile App: View Name: '. $view_name .' Action: '. $action_name, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return $payload;
	}

	/**
	 * @param $payload
	 * @return mixed
	 */
	function convertPayloadToIos( $payload ) {
		$payload =  $this->convertPayloadEventToMobileApp( $payload );

		if ( isset( $payload['timetrex']['priority'] ) && (int)$payload['timetrex']['priority'] == 1 ) {
			$payload['sound'] = 'notification_critical.m4a'; //Critical priority notification sound that repeats for up to 30 seconds. **NOTE: file extension of .m4a is required.
		}

		return $payload;
	}

	/**
	 * @param $payload
	 * @return mixed
	 */
	function convertPayloadToAndroid( $payload ) {
		$payload = $this->convertPayloadEventToMobileApp( $payload );

		if ( isset( $payload['timetrex']['priority'] ) && (int)$payload['timetrex']['priority'] == 1 ) {
			$payload['sound'] = 'notification_critical'; //Critical priority notification sound that repeats for up to 30 seconds. **Note: file extension is not required here.
		}

		return $payload;
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
								$this->$function( TTDate::parseDateTime( $data[ $key ] ) );
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
						case 'priority':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'notification_type':
							$data[$variable] = Option::getByKey( $this->getType(), $this->getOptions( $variable ) );
							break;
						case 'effective_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() );
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
	 * Marks notifications as read based on object_id/object_type_id. Used for case where exceptions are corrected/removed and the notifications don't need to be read by users anymore.
	 * @param $object_type_id
	 * @param $object_id
	 * @return bool
	 * @throws DBError
	 */
	static function updateStatusByObjectIdAndObjectTypeId( $object_type_id, $object_id, $user_id = null ) {
		if ( empty( $object_id ) && empty( $user_id ) ) {
			Debug::Text( 'ERROR: Object ID and User ID are empty!', __FILE__, __LINE__, __METHOD__, 10 );
			return false;
		}

		$nf = new NotificationFactory();

		$ph = [
				'object_type_id' => (int)$object_type_id,
		];
		$query = 'UPDATE notification SET status_id = 20, sent_status_id = 100, updated_date = '. time() .' WHERE status_id = 10 AND object_type_id = ? ';

		$query .= ( isset( $object_id ) && !empty( $object_id ) ) ? $nf->getWhereClauseSQL( 'object_id', $object_id, 'uuid_list', $ph ) : null;
		$query .= ( isset( $user_id ) && !empty( $user_id ) ) ? $nf->getWhereClauseSQL( 'user_id', $user_id, 'uuid_list', $ph ) : null;

		$rs = $nf->ExecuteSQL( $query, $ph );

		//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( ' Marked notifications as read: '. $nf->getAffectedRows(), __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}
}