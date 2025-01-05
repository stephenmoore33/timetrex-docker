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
class UserPreferenceFactory extends Factory {
	protected $table = 'user_preference';
	protected $pk_sequence_name = 'user_preference_id_seq'; //PK Sequence name

	var $user_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'date_format' )->setFunctionMap( 'DateFormat' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'time_format' )->setFunctionMap( 'TimeFormat' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'time_unit_format' )->setFunctionMap( 'TimeUnitFormat' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'time_zone' )->setFunctionMap( 'TimeZone' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'items_per_page' )->setFunctionMap( 'ItemsPerPage' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'timesheet_view' )->setFunctionMap( 'TimesheetView' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'start_week_day' )->setFunctionMap( 'StartWeekDay' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'language' )->setFunctionMap( 'Language' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'enable_email_notification_exception' )->setFunctionMap( 'EnableEmailNotificationException' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_email_notification_message' )->setFunctionMap( 'EnableEmailNotificationMessage' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_email_notification_home' )->setFunctionMap( 'EnableEmailNotificationHome' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'schedule_icalendar_type_id' )->setFunctionMap( 'ScheduleIcalendarType' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'schedule_icalendar_event_name' )->setFunctionMap( 'ScheduleIcalendarEventName' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'schedule_icalendar_alarm1_working' )->setFunctionMap( 'ScheduleIcalendarAlarm1Working' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'schedule_icalendar_alarm2_working' )->setFunctionMap( 'ScheduleIcalendarAlarm2Working' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'schedule_icalendar_alarm1_absence' )->setFunctionMap( 'ScheduleIcalendarAlarm1Absence' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'schedule_icalendar_alarm2_absence' )->setFunctionMap( 'ScheduleIcalendarAlarm2Absence' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'schedule_icalendar_alarm1_modified' )->setFunctionMap( 'ScheduleIcalendarAlarm1Modified' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'schedule_icalendar_alarm2_modified' )->setFunctionMap( 'ScheduleIcalendarAlarm2Modified' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'enable_save_timesheet_state' )->setFunctionMap( 'EnableSaveTimesheetState' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_always_blank_timesheet_rows' )->setFunctionMap( 'EnableAlwaysBlankTimesheetRows' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_auto_context_menu' )->setFunctionMap( 'EnableAutoContextMenu' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_report_open_new_window' )->setFunctionMap( 'EnableReportOpenNewWindow' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'user_full_name_format' )->setFunctionMap( 'UserFullNameFormat' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'shortcut_key_sequence' )->setFunctionMap( 'ShortcutKeySequence' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'enable_email_notification_pay_stub' )->setFunctionMap( 'EnableEmailNotificationPayStub' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'default_login_screen' )->setFunctionMap( 'DefaultLoginScreen' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'distance_format' )->setFunctionMap( 'DistanceFormat' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'notification_duration' )->setFunctionMap( 'NotificationDuration' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'notification_status_id' )->setFunctionMap( 'NotificationStatus' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'browser_permission_ask_date' )->setFunctionMap( 'BrowserPermissionAskDate' )->setType( 'timestamptz' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_preference' )->setLabel( 'Preferences' )->setFields(
									new TTSFields(
											TTSField::new( 'full_name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Employee' ) )->setWidth( '100%' )->setVisible( [ 'UI' ], false ),
											TTSField::new( 'language' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Language' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getOptions' )->setArg( 'language' ) ),
											TTSField::new( 'date_format' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Date Format' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getOptions' )->setArg( 'date_format' ) ),
											TTSField::new( 'time_format' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Time Format' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getOptions' )->setArg( 'time_format' ) ),
											TTSField::new( 'time_unit_format' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Time Units' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getOptions' )->setArg( 'time_unit_format' ) ),
											TTSField::new( 'distance_format' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Distance Units' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getOptions' )->setArg( 'distance_format' ) ),
											TTSField::new( 'time_zone' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Time Zone' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getOptions' )->setArg( 'time_zone' ) ),
											TTSField::new( 'start_week_day' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Calendar Starts On' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getOptions' )->setArg( 'start_week_day' ) ),
											TTSField::new( 'items_per_page' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Rows per page' ) ),
											TTSField::new( 'default_login_screen' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Default Screen' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getOptions' )->setArg( 'default_login_screen' ) ),
											TTSField::new( 'enable_save_timesheet_state' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Save TimeSheet State' ) ),
									)
							),
							TTSTab::new( 'tab_preferences_notification' )->setLabel( 'Notifications' )->setInitCallback( 'initSubNotificationView' )->setHTMLTemplate( '<div id="tab_preferences_notification" class="edit-view-tab-outside">\n\t\t\t\t\t<div class="edit-view-tab" id="tab_preferences_notification_content_div">\n\t\t\t\t\t\t<div class="first-column full-width-column"></div>\n\t\t\t\t\t\t<div class="inside-editor-div full-width-column"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>' ),
							TTSTab::new( 'tab_schedule_sync' )->setLabel( 'Schedule Synchronization' )->setInitCallback( 'initSubScheduleSyncView' )->setHTMLTemplate( '<div id="tab_schedule_sync" class="edit-view-tab-outside">\n\t\t\t\t\t<div class="edit-view-tab" id="tab_schedule_sync_content_div">\n\t\t\t\t\t\t<div class="first-column full-width-column"></div>\n\t\t\t\t\t\t<div class="save-and-continue-div permission-defined-div">\n\t\t\t\t\t\t\t<span class="message permission-message"></span>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>' ),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'b.id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'status' )->setType( 'text' )->setColumn( 'b.status_id' )->setMulti( true ),
							TTSSearchField::new( 'legal_entity_id' )->setType( 'uuid_list' )->setColumn( 'b.legal_entity_id' )->setMulti( true ),
							TTSSearchField::new( 'include_subgroups' )->setType( 'bool' )->setColumn( '' )->setMulti( false ),
							TTSSearchField::new( 'group_id' )->setType( 'uuid_list' )->setColumn( 'b.group_id' )->setMulti( true ),
							TTSSearchField::new( 'group' )->setType( 'text' )->setColumn( 'ugf.name' )->setMulti( false ),
							TTSSearchField::new( 'default_branch_id' )->setType( 'uuid_list' )->setColumn( 'b.default_branch_id' )->setMulti( true ),
							TTSSearchField::new( 'default_branch' )->setType( 'text' )->setColumn( 'bf.name' )->setMulti( false ),
							TTSSearchField::new( 'default_department_id' )->setType( 'uuid_list' )->setColumn( 'b.default_department_id' )->setMulti( true ),
							TTSSearchField::new( 'default_department' )->setType( 'text' )->setColumn( 'df.name' )->setMulti( false ),
							TTSSearchField::new( 'title_id' )->setType( 'uuid_list' )->setColumn( 'b.title_id' )->setMulti( true ),
							TTSSearchField::new( 'title' )->setType( 'text' )->setColumn( 'utf.name' )->setMulti( false ),
							TTSSearchField::new( 'sex' )->setType( 'text' )->setColumn( 'b.sex_id' )->setMulti( true ),
							TTSSearchField::new( 'first_name' )->setType( 'text_metaphone' )->setColumn( 'b.first_name' )->setMulti( false ),
							TTSSearchField::new( 'last_name' )->setType( 'text_metaphone' )->setColumn( 'b.last_name' )->setMulti( false ),
							TTSSearchField::new( 'home_phone' )->setType( 'phone' )->setColumn( 'b.home_phone' )->setMulti( false ),
							TTSSearchField::new( 'work_phone' )->setType( 'phone' )->setColumn( 'b.work_phone' )->setMulti( false ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'b.country' )->setMulti( false ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'b.province' )->setMulti( false ),
							TTSSearchField::new( 'city' )->setType( 'text' )->setColumn( 'b.city' )->setMulti( false ),
							TTSSearchField::new( 'address1' )->setType( 'text' )->setColumn( 'b.address1' )->setMulti( false ),
							TTSSearchField::new( 'address2' )->setType( 'text' )->setColumn( 'b.address2' )->setMulti( false ),
							TTSSearchField::new( 'postal_code' )->setType( 'text' )->setColumn( 'b.postal_code' )->setMulti( false ),
							TTSSearchField::new( 'employee_number' )->setType( 'numeric' )->setColumn( 'b.employee_number' )->setMulti( false ),
							TTSSearchField::new( 'user_name' )->setType( 'text' )->setColumn( 'b.user_name' )->setMulti( false ),
							TTSSearchField::new( 'sin' )->setType( 'numeric_string' )->setColumn( 'b.sin' )->setMulti( false ),
							TTSSearchField::new( 'work_email' )->setType( 'text' )->setColumn( 'b.work_email' )->setMulti( false ),
							TTSSearchField::new( 'home_email' )->setType( 'text' )->setColumn( 'b.home_email' )->setMulti( false )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserPreference' )->setMethod( 'getUserPreference' )
									->setSummary( 'Get user preference records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserPreference' )->setMethod( 'setUserPreference' )
									->setSummary( 'Add or edit user preference records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserPreference' )->setMethod( 'deleteUserPreference' )
									->setSummary( 'Delete user preference records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserPreference' )->setMethod( 'getUserPreference' ) ),
											   ) ),
							TTSAPI::new( 'APIUserPreference' )->setMethod( 'getUserPreferenceDefaultData' )
									->setSummary( 'Get default user preference data used for creating new user preferences. Use this before calling setUserPreference to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param array $params
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {

			// I18n: No need to use gettext because these options only appear for english.
			case 'date_format':
				$retval = [
						'd-M-y'    => TTi18n::gettext( '25-Feb-01 (dd-mmm-yy)' ),
						'd-M-Y'    => TTi18n::gettext( '25-Feb-2001 (dd-mmm-yyyy)' ),
						//PHP 5.1.2 fails to parse these with strtotime it looks like
						//'d/M/y'	=> '25/Feb/01 (dd/mmm/yy)',
						//'d/M/Y'	=> '25/Feb/2001 (dd/mmm/yyyy)',
						'dMY'      => TTi18n::gettext( '25Feb2001 (ddmmmyyyy)' ),
						'd/m/Y'    => '25/02/2001 (dd/mm/yyyy)',
						'd/m/y'    => '25/02/01 (dd/mm/yy)',
						'd-m-y'    => '25-02-01 (dd-mm-yy)',
						'd-m-Y'    => '25-02-2001 (dd-mm-yyyy)',
						'm/d/y'    => '02/25/01 (mm/dd/yy)',
						'm/d/Y'    => '02/25/2001 (mm/dd/yyyy)',
						'm-d-y'    => '02-25-01 (mm-dd-yy)',
						'm-d-Y'    => '02-25-2001 (mm-dd-yyyy)',
						'Y-m-d'    => '2001-02-25 (yyyy-mm-dd)',
						//'Ymd'			=> '20010225 (yyyymmdd)', //This can't be parsed properly due to all integer values, parseDateTime() thinks its an epoch.
						'M-d-y'    => TTi18n::gettext( 'Feb-25-01 (mmm-dd-yy)' ),
						'M-d-Y'    => TTi18n::gettext( 'Feb-25-2001 (mmm-dd-yyyy)' ),
						'l, F d Y' => TTi18n::gettext( 'Sunday, February 25 2001' ),
						'D, F d Y' => TTi18n::gettext( 'Sun, February 25 2001' ),
						'D, M d Y' => TTi18n::gettext( 'Sun, Feb 25 2001' ),
						'D, d-M-Y' => TTi18n::gettext( 'Sun, 25-Feb-2001' ),
						'D, dMY'   => TTi18n::gettext( 'Sun, 25Feb2001' ),
				];

				if ( defined( 'TIMETREX_API' ) == true && TIMETREX_API == true ) {
					$retval = Misc::addSortPrefix( $retval );
				}
				break;
			// I18n: We use fewer calendar options for non-en langs, as otherwise strtotime chokes.
			case 'date_format_example':
				$retval = [
						'd-M-y'    => TTi18n::gettext( 'dd-mmm-yy' ),
						'd-M-Y'    => TTi18n::gettext( 'dd-mmm-yyyy' ),
						'dMY'      => TTi18n::gettext( 'ddmmmyyyy' ),
						'd/m/Y'    => 'dd/mm/yyyy',
						'd/m/y'    => 'dd/mm/yy',
						'd-m-y'    => 'dd-mm-yy',
						'd-m-Y'    => 'dd-mm-yyyy',
						'm/d/y'    => 'mm/dd/yy',
						'm/d/Y'    => 'mm/dd/yyyy',
						'm-d-y'    => 'mm-dd-yy',
						'm-d-Y'    => 'mm-dd-yyyy',
						'Y-m-d'    => 'yyyy-mm-dd',
						'M-d-y'    => TTi18n::gettext( 'mmm-dd-yy' ),
						'M-d-Y'    => TTi18n::gettext( 'mmm-dd-yyyy' ),
						'l, F d Y' => TTi18n::gettext( 'mmmmmmmm dd yyyy' ),
						'D, F d Y' => TTi18n::gettext( 'mmmmmmmm dd yyyy' ),
						'D, M d Y' => TTi18n::gettext( 'mm dd yyyy' ),
						'D, d-M-Y' => TTi18n::gettext( 'dd-mmm-yy' ),
						'D, dMY'   => TTi18n::gettext( 'ddmmmyyyy' ),
				];
				break;
			case 'other_date_format':
				$retval = [
						'd/m/Y' => '25/02/2001 (dd/mm/yyyy)',
						'd/m/y' => '25/02/01 (dd/mm/yy)',
						'd-m-y' => '25-02-01 (dd-mm-yy)',
						'd-m-Y' => '25-02-2001 (dd-mm-yyyy)',
						'm/d/y' => '02/25/01 (mm/dd/yy)',
						'm/d/Y' => '02/25/2001 (mm/dd/yyyy)',
						'm-d-y' => '02-25-01 (mm-dd-yy)',
						'm-d-Y' => '02-25-2001 (mm-dd-yyyy)',
						'Y-m-d' => '2001-02-25 (yyyy-mm-dd)',
				];

				if ( defined( 'TIMETREX_API' ) == true && TIMETREX_API == true ) {
					$retval = Misc::addSortPrefix( $retval );
				}
				break;
			case 'moment_date_format':
				//NOTICE: When changing these, we may need to update the mobile app as it has them hardcoded there too.
				$retval = [
						'D, F d Y' => 'ddd, MMMM DD YYYY',
						'D, M d Y' => 'ddd, MMM DD YYYY',
						'D, d-M-Y' => 'ddd, DD-MMM-YYYY',
						'D, dMY'   => 'ddd, DDMMMYYYY',
						'M-d-Y'    => 'MMM-DD-YYYY',
						'M-d-y'    => 'MMM-DD-YY',
						'Y-m-d'    => 'YYYY-MM-DD',
						'd-M-Y'    => 'DD-MMM-YYYY',
						'd-M-y'    => 'DD-MMM-YY',
						'd-m-Y'    => 'DD-MM-YYYY',
						'd-m-y'    => 'DD-MM-YY',
						'd/m/Y'    => 'DD/MM/YYYY',
						'd/m/y'    => 'DD/MM/YY',
						'dMY'      => 'DDMMMYYYY',
						'l, F d Y' => 'dddd, MMMM DD YYYY',
						'm-d-Y'    => 'MM-DD-YYYY',
						'm-d-y'    => 'MM-DD-YY',
						'm/d/Y'    => 'MM/DD/YYYY',
						'm/d/y'    => 'MM/DD/YY',
				];
				break;
			case 'time_format':
				$retval = [
						'g:i A'     => TTi18n::gettext( '8:09 PM' ),
						'g:i a'     => TTi18n::gettext( '8:09 pm' ),
						'G:i'       => TTi18n::gettext( '20:09' ),
						'g:i A T'   => TTi18n::gettext( '8:09 PM GMT' ),
						'G:i T'     => TTi18n::gettext( '20:09 GMT' ),

						//Include seconds so they can properly validate rounding policies and such.
						'g:i:s A'   => TTi18n::gettext( '8:09:11 PM' ),
						'g:i:s a'   => TTi18n::gettext( '8:09:11 pm' ),
						'G:i:s'     => TTi18n::gettext( '20:09:11' ),
						'g:i:s A T' => TTi18n::gettext( '8:09:11 PM GMT' ),
						'G:i:s T'   => TTi18n::gettext( '20:09:11 GMT' ),
				];
				break;
			case 'time_format_example':
				$retval = [
						'g:i A'   => TTi18n::gettext( 'HH:MM AM' ),
						'g:i a'   => TTi18n::gettext( 'HH:MM am' ),
						'G:i'     => TTi18n::gettext( 'HH:MM' ),
						'g:i A T' => TTi18n::gettext( 'HH:MM AM TZ' ),
						'G:i T'   => TTi18n::gettext( 'HH:MM TZ' ),

						'g:i:s A'   => TTi18n::gettext( 'HH:MM:SS AM' ),
						'g:i:s a'   => TTi18n::gettext( 'HH:MM:SS am' ),
						'G:i:s'     => TTi18n::gettext( 'HH:MM:SS' ),
						'g:i:s A T' => TTi18n::gettext( 'HH:MM:SS AM TZ' ),
						'G:i:s T'   => TTi18n::gettext( 'HH:MM:SS TZ' ),
				];
				break;
			case 'moment_time_format':
				$retval = [
						'g:i A'     => 'hh:mm A',
						'g:i a'     => 'hh:mm a',
						'G:i'       => 'HH:mm',
						'g:i A T'   => 'hh:mm A Z',
						'G:i T'     => 'HH:mm Z',

						//Include seconds so they can properly validate rounding policies and such.
						'g:i:s A'   => 'hh:mm:ss A',
						'g:i:s a'   => 'hh:mm:ss a',
						'G:i:s'     => 'HH:mm:ss',
						'g:i:s A T' => 'hh:mm:ss A Z',
						'G:i:s T'   => 'HH:mm:ss Z',
				];
				break;
			case 'date_time_format':
				//Merge Date and Time formats together.
				$date_formats = $this->getOptions( 'date_format' );
				$time_formats = $this->getOptions( 'time_format' );
				if ( is_array( $date_formats ) && is_array( $time_formats ) ) {
					foreach ( $date_formats as $date_format => $date_format_name ) {
						foreach ( $time_formats as $time_format => $time_format_name ) {
							//Use "|" as a separate so we can later split them back into separate date/time formats.
							$retval[$date_format . '_' . $time_format] = trim( preg_replace( '/\(.*\)/i', '', $date_format_name ) ) . ' ' . $time_format_name;
						}
					}
				}
				break;
			case 'time_unit_format':
				$retval = [
						10 => TTi18n::gettext( 'hh:mm (2:15)' ),
						12 => TTi18n::gettext( 'hh:mm:ss (2:15:59)' ),
						20 => TTi18n::gettext( 'Hours (2.25)' ),
						22 => TTi18n::gettext( 'Hours (2.141)' ),
						23 => TTi18n::gettext( 'Hours (2.3587)' ),
						30 => TTi18n::gettext( 'Minutes (135)' ),
						40 => TTi18n::gettext( 'Seconds (3600)' ),
				];
				break;
			case 'distance_format':
				$retval = [
						10 => TTi18n::gettext( 'Kilometers' ),
						20 => TTi18n::gettext( 'Miles' ),
						30 => TTi18n::gettext( 'Meters' ),
				];
				break;

			// I18n: These timezones probably should be translated, but doing so would add ~550
			//		 lines to the translator's workload for each lang.	And these are hard to translate.
			//		 Probably better to use an already translated timezone class, if one exists.
			//
			//Commented out timezones do not work in PostgreSQL 8.2, as they hardcode timezone data into versions.
			case 'time_zone':
				$retval = [
						'America/Adak'                   => 'America/Adak',
						'America/Anchorage'              => 'America/Anchorage',
						'America/Anguilla'               => 'America/Anguilla',
						'America/Antigua'                => 'America/Antigua',
						'America/Araguaina'              => 'America/Araguaina',
						'America/Argentina/Buenos_Aires' => 'America/Argentina/Buenos Aires',
						'America/Argentina/Catamarca'    => 'America/Argentina/Catamarca',
						'America/Argentina/Cordoba'      => 'America/Argentina/Cordoba',
						'America/Argentina/Jujuy'        => 'America/Argentina/Jujuy',
						'America/Argentina/La_Rioja'     => 'America/Argentina/La Rioja',
						'America/Argentina/Mendoza'      => 'America/Argentina/Mendoza',
						'America/Argentina/Rio_Gallegos' => 'America/Argentina/Rio_Gallegos',
						'America/Argentina/Salta'        => 'America/Argentina/Salta',
						'America/Argentina/San_Juan'     => 'America/Argentina/San Juan',
						'America/Argentina/San_Luis'     => 'America/Argentina/San Luis',
						'America/Argentina/Tucuman'      => 'America/Argentina/Tucuman',
						'America/Argentina/Ushuaia'      => 'America/Argentina/Ushuaia',
						'America/Aruba'                  => 'America/Aruba',
						'America/Asuncion'               => 'America/Asuncion',
						'America/Atikokan'               => 'America/Atikokan',
						'America/Bahia'                  => 'America/Bahia',
						'America/Bahia_Banderas'         => 'America/Bahia Banderas',
						'America/Barbados'               => 'America/Barbados',
						'America/Belem'                  => 'America/Belem',
						'America/Belize'                 => 'America/Belize',
						'America/Blanc-Sablon'           => 'America/Blanc-Sablon',
						'America/Boa_Vista'              => 'America/Boa Vista',
						'America/Bogota'                 => 'America/Bogota',
						'America/Boise'                  => 'America/Boise',
						'America/Buenos_Aires'           => 'America/Buenos Aires',
						'America/Cambridge_Bay'          => 'America/Cambridge Bay',
						'America/Campo_Grande'           => 'America/Campo Grande',
						'America/Cancun'                 => 'America/Cancun',
						'America/Caracas'                => 'America/Caracas',
						'America/Catamarca'              => 'America/Catamarca',
						'America/Cayenne'                => 'America/Cayenne',
						'America/Cayman'                 => 'America/Cayman',
						'America/Chicago'                => 'America/Chicago',
						'America/Chihuahua'              => 'America/Chihuahua',
						'America/Ciudad_Juarez'          => 'America/Ciudad Juarez',
						'America/Cordoba'                => 'America/Cordoba',
						'America/Costa_Rica'             => 'America/Costa Rica',
						'America/Creston'                => 'America/Creston',
						'America/Cuiaba'                 => 'America/Cuiaba',
						'America/Curacao'                => 'America/Curacao',
						'America/Danmarkshavn'           => 'America/Danmarkshavn',
						'America/Dawson'                 => 'America/Dawson',
						'America/Dawson_Creek'           => 'America/Dawson Creek',
						'America/Denver'                 => 'America/Denver',
						'America/Detroit'                => 'America/Detroit',
						'America/Dominica'               => 'America/Dominica',
						'America/Edmonton'               => 'America/Edmonton',
						'America/Eirunepe'               => 'America/Eirunepe',
						'America/El_Salvador'            => 'America/El Salvador',
						'America/Ensenada'               => 'America/Ensenada',
						'America/Fort_Nelson'            => 'America/Fort Nelson',
						'America/Fort_Wayne'             => 'America/Fort Wayne',
						'America/Fortaleza'              => 'America/Fortaleza',
						'America/Glace_Bay'              => 'America/Glace Bay',
						'America/Godthab'                => 'America/Godthab',
						'America/Goose_Bay'              => 'America/Goose Bay',
						'America/Grand_Turk'             => 'America/Grand Turk',
						'America/Grenada'                => 'America/Grenada',
						'America/Guadeloupe'             => 'America/Guadeloupe',
						'America/Guatemala'              => 'America/Guatemala',
						'America/Guayaquil'              => 'America/Guayaquil',
						'America/Guyana'                 => 'America/Guyana',
						'America/Halifax'                => 'America/Halifax',
						'America/Havana'                 => 'America/Havana',
						'America/Hermosillo'             => 'America/Hermosillo',
						'America/Indiana/Indianapolis'   => 'America/Indiana/Indianapolis',
						'America/Indiana/Knox'           => 'America/Indiana/Knox',
						'America/Indiana/Marengo'        => 'America/Indiana/Marengo',
						'America/Indiana/Petersburg'     => 'America/Indiana/Petersburg',
						'America/Indiana/Tell_City'      => 'America/Indiana/Tell City',
						'America/Indiana/Vevay'          => 'America/Indiana/Vevay',
						'America/Indiana/Vincennes'      => 'America/Indiana/Vincennes',
						'America/Indiana/Winamac'        => 'America/Indiana/Winamac',
						'America/Indianapolis'           => 'America/Indianapolis',
						'America/Inuvik'                 => 'America/Inuvik',
						'America/Iqaluit'                => 'America/Iqaluit',
						'America/Jamaica'                => 'America/Jamaica',
						'America/Jujuy'                  => 'America/Jujuy',
						'America/Juneau'                 => 'America/Juneau',
						'America/Kentucky/Louisville'    => 'America/Kentucky/Louisville',
						'America/Kentucky/Monticello'    => 'America/Kentucky/Monticello',
						'America/Knox_IN'                => 'America/Knox IN',
						'America/Kralendijk'             => 'America/Kralendijk',
						'America/La_Paz'                 => 'America/La Paz',
						'America/Lima'                   => 'America/Lima',
						'America/Los_Angeles'            => 'America/Los Angeles',
						'America/Louisville'             => 'America/Louisville',
						'America/Lower_Princes'          => 'America/Lower Princes',
						'America/Maceio'                 => 'America/Maceio',
						'America/Managua'                => 'America/Managua',
						'America/Manaus'                 => 'America/Manaus',
						'America/Marigot'                => 'America/Marigot',
						'America/Martinique'             => 'America/Martinique',
						'America/Matamoros'              => 'America/Matamoros',
						'America/Mazatlan'               => 'America/Mazatlan',
						'America/Mendoza'                => 'America/Mendoza',
						'America/Menominee'              => 'America/Menominee',
						'America/Merida'                 => 'America/Merida',
						'America/Metlakatla'             => 'America/Metlakatla',
						'America/Mexico_City'            => 'America/Mexico City',
						'America/Miquelon'               => 'America/Miquelon',
						'America/Moncton'                => 'America/Moncton',
						'America/Monterrey'              => 'America/Monterrey',
						'America/Montevideo'             => 'America/Montevideo',
						'America/Montreal'               => 'America/Montreal',
						'America/Montserrat'             => 'America/Montserrat',
						'America/Nassau'                 => 'America/Nassau',
						'America/New_York'               => 'America/New York',
						'America/Nipigon'                => 'America/Nipigon',
						'America/Nome'                   => 'America/Nome',
						'America/Noronha'                => 'America/Noronha',
						'America/North_Dakota/Beulah'    => 'America/North Dakota/Beulah',
						'America/North_Dakota/Center'    => 'America/North Dakota/Center',
						'America/North_Dakota/New_Salem' => 'America/North Dakota/New Salem',
						'America/Nuuk'                   => 'America/Nuuk',
						'America/Ojinaga'                => 'America/Ojinaga',
						'America/Panama'                 => 'America/Panama',
						'America/Pangnirtung'            => 'America/Pangnirtung',
						'America/Paramaribo'             => 'America/Paramaribo',
						'America/Phoenix'                => 'America/Phoenix',
						'America/Port-au-Prince'         => 'America/Port-au-Prince',
						'America/Port_of_Spain'          => 'America/Port of Spain',
						'America/Porto_Acre'             => 'America/Porto Acre',
						'America/Porto_Velho'            => 'America/Porto Velho',
						'America/Puerto_Rico'            => 'America/Puerto Rico',
						'America/Punta_Arenas'           => 'America/Punta Arenas',
						'America/Rainy_River'            => 'America/Rainy River',
						'America/Rankin_Inlet'           => 'America/Rankin Inlet',
						'America/Recife'                 => 'America/Recife',
						'America/Regina'                 => 'America/Regina',
						'America/Resolute'               => 'America/Resolute',
						'America/Rio_Branco'             => 'America/Rio Branco',
						'America/Rosario'                => 'America/Rosario',
						'America/Santarem'               => 'America/Santarem',
						'America/Santiago'               => 'America/Santiago',
						'America/Santo_Domingo'          => 'America/Santo Domingo',
						'America/Sao_Paulo'              => 'America/Sao Paulo',
						'America/Scoresbysund'           => 'America/Scoresbysund',
						'America/Shiprock'               => 'America/Shiprock',
						'America/Sitka'                  => 'America/Sitka',
						'America/St_Barthelemy'          => 'America/St Barthelemy',
						'America/St_Johns'               => 'America/St Johns',
						'America/St_Kitts'               => 'America/St Kitts',
						'America/St_Lucia'               => 'America/St Lucia',
						'America/St_Thomas'              => 'America/St Thomas',
						'America/St_Vincent'             => 'America/St Vincent',
						'America/Swift_Current'          => 'America/Swift Current',
						'America/Tegucigalpa'            => 'America/Tegucigalpa',
						'America/Thule'                  => 'America/Thule',
						'America/Thunder_Bay'            => 'America/Thunder Bay',
						'America/Tijuana'                => 'America/Tijuana',
						'America/Toronto'                => 'America/Toronto',
						'America/Tortola'                => 'America/Tortola',
						'America/Vancouver'              => 'America/Vancouver',
						'America/Virgin'                 => 'America/Virgin',
						'America/Whitehorse'             => 'America/Whitehorse',
						'America/Winnipeg'               => 'America/Winnipeg',
						'America/Yakutat'                => 'America/Yakutat',
						'America/Yellowknife'            => 'America/Yellowknife',

						'Africa/Abidjan'       => 'Africa/Abidjan',
						'Africa/Accra'         => 'Africa/Accra',
						'Africa/Addis_Ababa'   => 'Africa/Addis Ababa',
						'Africa/Algiers'       => 'Africa/Algiers',
						'Africa/Asmara'        => 'Africa/Asmara',
						'Africa/Asmera'        => 'Africa/Asmera',
						'Africa/Bamako'        => 'Africa/Bamako',
						'Africa/Bangui'        => 'Africa/Bangui',
						'Africa/Banjul'        => 'Africa/Banjul',
						'Africa/Bissau'        => 'Africa/Bissau',
						'Africa/Blantyre'      => 'Africa/Blantyre',
						'Africa/Brazzaville'   => 'Africa/Brazzaville',
						'Africa/Bujumbura'     => 'Africa/Bujumbura',
						'Africa/Cairo'         => 'Africa/Cairo',
						'Africa/Casablanca'    => 'Africa/Casablanca',
						'Africa/Ceuta'         => 'Africa/Ceuta',
						'Africa/Conakry'       => 'Africa/Conakry',
						'Africa/Dakar'         => 'Africa/Dakar',
						'Africa/Dar_es_Salaam' => 'Africa/Dar es Salaam',
						'Africa/Djibouti'      => 'Africa/Djibouti',
						'Africa/Douala'        => 'Africa/Douala',
						'Africa/El_Aaiun'      => 'Africa/El Aaiun',
						'Africa/Freetown'      => 'Africa/Freetown',
						'Africa/Gaborone'      => 'Africa/Gaborone',
						'Africa/Harare'        => 'Africa/Harare',
						'Africa/Johannesburg'  => 'Africa/Johannesburg',
						'Africa/Juba'          => 'Africa/Juba',
						'Africa/Kampala'       => 'Africa/Kampala',
						'Africa/Khartoum'      => 'Africa/Khartoum',
						'Africa/Kigali'        => 'Africa/Kigali',
						'Africa/Kinshasa'      => 'Africa/Kinshasa',
						'Africa/Lagos'         => 'Africa/Lagos',
						'Africa/Libreville'    => 'Africa/Libreville',
						'Africa/Lome'          => 'Africa/Lome',
						'Africa/Luanda'        => 'Africa/Luanda',
						'Africa/Lubumbashi'    => 'Africa/Lubumbashi',
						'Africa/Lusaka'        => 'Africa/Lusaka',
						'Africa/Malabo'        => 'Africa/Malabo',
						'Africa/Maputo'        => 'Africa/Maputo',
						'Africa/Maseru'        => 'Africa/Maseru',
						'Africa/Mbabane'       => 'Africa/Mbabane',
						'Africa/Mogadishu'     => 'Africa/Mogadishu',
						'Africa/Monrovia'      => 'Africa/Monrovia',
						'Africa/Nairobi'       => 'Africa/Nairobi',
						'Africa/Ndjamena'      => 'Africa/Ndjamena',
						'Africa/Niamey'        => 'Africa/Niamey',
						'Africa/Nouakchott'    => 'Africa/Nouakchott',
						'Africa/Ouagadougou'   => 'Africa/Ouagadougou',
						'Africa/Porto-Novo'    => 'Africa/Porto-Novo',
						'Africa/Sao_Tome'      => 'Africa/Sao Tome',
						'Africa/Timbuktu'      => 'Africa/Timbuktu',
						'Africa/Tripoli'       => 'Africa/Tripoli',
						'Africa/Tunis'         => 'Africa/Tunis',
						'Africa/Windhoek'      => 'Africa/Windhoek',

						'Asia/Atyrau'        => 'Asia/Atyrau',
						'Asia/Aden'          => 'Asia/Aden',
						'Asia/Almaty'        => 'Asia/Almaty',
						'Asia/Amman'         => 'Asia/Amman',
						'Asia/Anadyr'        => 'Asia/Anadyr',
						'Asia/Aqtau'         => 'Asia/Aqtau',
						'Asia/Aqtobe'        => 'Asia/Aqtobe',
						'Asia/Ashgabat'      => 'Asia/Ashgabat',
						'Asia/Ashkhabad'     => 'Asia/Ashkhabad',
						'Asia/Baghdad'       => 'Asia/Baghdad',
						'Asia/Bahrain'       => 'Asia/Bahrain',
						'Asia/Baku'          => 'Asia/Baku',
						'Asia/Bangkok'       => 'Asia/Bangkok',
						'Asia/Barnaul'       => 'Asia/Barnaul',
						'Asia/Beirut'        => 'Asia/Beirut',
						'Asia/Bishkek'       => 'Asia/Bishkek',
						'Asia/Brunei'        => 'Asia/Brunei',
						'Asia/Calcutta'      => 'Asia/Calcutta',
						'Asia/Chita'         => 'Asia/Chita',
						'Asia/Choibalsan'    => 'Asia/Choibalsan',
						'Asia/Chongqing'     => 'Asia/Chongqing',
						'Asia/Chungking'     => 'Asia/Chungking',
						'Asia/Colombo'       => 'Asia/Colombo',
						'Asia/Dacca'         => 'Asia/Dacca',
						'Asia/Damascus'      => 'Asia/Damascus',
						'Asia/Dhaka'         => 'Asia/Dhaka',
						'Asia/Dili'          => 'Asia/Dili',
						'Asia/Dubai'         => 'Asia/Dubai',
						'Asia/Dushanbe'      => 'Asia/Dushanbe',
						'Asia/Famagusta'     => 'Asia/Famagusta',
						'Asia/Gaza'          => 'Asia/Gaza',
						'Asia/Harbin'        => 'Asia/Harbin',
						'Asia/Hebron'        => 'Asia/Hebron',
						'Asia/Ho_Chi_Minh'   => 'Asia/Ho Chi Minh',
						'Asia/Hong_Kong'     => 'Asia/Hong Kong',
						'Asia/Hovd'          => 'Asia/Hovd',
						'Asia/Irkutsk'       => 'Asia/Irkutsk',
						'Asia/Istanbul'      => 'Asia/Istanbul',
						'Asia/Jakarta'       => 'Asia/Jakarta',
						'Asia/Jayapura'      => 'Asia/Jayapura',
						'Asia/Jerusalem'     => 'Asia/Jerusalem', //Offset 10800
						'Asia/Kabul'         => 'Asia/Kabul',
						'Asia/Kamchatka'     => 'Asia/Kamchatka',
						'Asia/Karachi'       => 'Asia/Karachi',
						'Asia/Kashgar'       => 'Asia/Kashgar',
						'Asia/Kathmandu'     => 'Asia/Kathmandu',
						'Asia/Katmandu'      => 'Asia/Katmandu',
						'Asia/Khandyga'      => 'Asia/Khandyga',
						'Asia/Kolkata'       => 'Asia/Kolkata',
						'Asia/Krasnoyarsk'   => 'Asia/Krasnoyarsk',
						'Asia/Kuala_Lumpur'  => 'Asia/Kuala Lumpur',
						'Asia/Kuching'       => 'Asia/Kuching',
						'Asia/Kuwait'        => 'Asia/Kuwait',
						'Asia/Macao'         => 'Asia/Macao',
						'Asia/Macau'         => 'Asia/Macau',
						'Asia/Magadan'       => 'Asia/Magadan',
						'Asia/Makassar'      => 'Asia/Makassar',
						'Asia/Manila'        => 'Asia/Manila',
						'Asia/Muscat'        => 'Asia/Muscat',
						'Asia/Nicosia'       => 'Asia/Nicosia',
						'Asia/Novokuznetsk'  => 'Asia/Novokuznetsk',
						'Asia/Novosibirsk'   => 'Asia/Novosibirsk',
						'Asia/Omsk'          => 'Asia/Omsk',
						'Asia/Oral'          => 'Asia/Oral',
						'Asia/Phnom_Penh'    => 'Asia/Phnom Penh',
						'Asia/Pontianak'     => 'Asia/Pontianak',
						'Asia/Pyongyang'     => 'Asia/Pyongyang',
						'Asia/Qatar'         => 'Asia/Qatar',
						'Asia/Qostanay'      => 'Asia/Qostanay',
						'Asia/Qyzylorda'     => 'Asia/Qyzylorda',
						'Asia/Rangoon'       => 'Asia/Rangoon',
						'Asia/Riyadh'        => 'Asia/Riyadh',
						'Asia/Saigon'        => 'Asia/Saigon',
						'Asia/Sakhalin'      => 'Asia/Sakhalin',
						'Asia/Samarkand'     => 'Asia/Samarkand',
						'Asia/Seoul'         => 'Asia/Seoul',
						'Asia/Shanghai'      => 'Asia/Shanghai',
						'Asia/Singapore'     => 'Asia/Singapore',
						'Asia/Srednekolymsk' => 'Asia/Srednekolymsk',
						'Asia/Taipei'        => 'Asia/Taipei',
						'Asia/Tashkent'      => 'Asia/Tashkent',
						'Asia/Tbilisi'       => 'Asia/Tbilisi',
						'Asia/Tehran'        => 'Asia/Tehran',
						'Asia/Thimbu'        => 'Asia/Thimbu',
						'Asia/Thimphu'       => 'Asia/Thimphu',
						'Asia/Tokyo'         => 'Asia/Tokyo',
						'Asia/Tomsk'         => 'Asia/Tomsk',
						'Asia/Ulaanbaatar'   => 'Asia/Ulaanbaatar',
						'Asia/Ulan_Bator'    => 'Asia/Ulan Bator',
						'Asia/Urumqi'        => 'Asia/Urumqi',
						'Asia/Ust-Nera'      => 'Asia/Ust-Nera',
						'Asia/Vientiane'     => 'Asia/Vientiane',
						'Asia/Vladivostok'   => 'Asia/Vladivostok',
						'Asia/Yakutsk'       => 'Asia/Yakutsk',
						'Asia/Yangon'        => 'Asia/Yangon',
						'Asia/Yekaterinburg' => 'Asia/Yekaterinburg',
						'Asia/Yerevan'       => 'Asia/Yerevan',

						'Atlantic/Azores'        => 'Atlantic/Azores',
						'Atlantic/Bermuda'       => 'Atlantic/Bermuda',
						'Atlantic/Canary'        => 'Atlantic/Canary',
						'Atlantic/Cape_Verde'    => 'Atlantic/Cape Verde',
						'Atlantic/Faroe'         => 'Atlantic/Faroe',
						'Atlantic/Jan_Mayen'     => 'Atlantic/Jan Mayen',
						'Atlantic/Madeira'       => 'Atlantic/Madeira',
						'Atlantic/Reykjavik'     => 'Atlantic/Reykjavik',
						'Atlantic/South_Georgia' => 'Atlantic/South Georgia',
						'Atlantic/St_Helena'     => 'Atlantic/St Helena',
						'Atlantic/Stanley'       => 'Atlantic/Stanley',
						'Antarctica/Troll'       => 'Antarctica/Troll',

						'Australia/Adelaide'    => 'Australia/Adelaide',
						'Australia/Brisbane'    => 'Australia/Brisbane',
						'Australia/Broken_Hill' => 'Australia/Broken Hill',
						'Australia/Canberra'    => 'Australia/Canberra',
						'Australia/Darwin'      => 'Australia/Darwin',
						'Australia/Eucla'       => 'Australia/Eucla',
						'Australia/Hobart'      => 'Australia/Hobart',
						'Australia/Lindeman'    => 'Australia/Lindeman',
						'Australia/Lord_Howe'   => 'Australia/Lord Howe',
						'Australia/Melbourne'   => 'Australia/Melbourne',
						'Australia/Perth'       => 'Australia/Perth',
						'Australia/Sydney'      => 'Australia/Sydney',
						'Australia/Yancowinna'  => 'Australia/Yancowinna',

						'Europe/Amsterdam'   => 'Europe/Amsterdam',
						'Europe/Andorra'     => 'Europe/Andorra',
						'Europe/Astrakhan'   => 'Europe/Astrakhan',
						'Europe/Athens'      => 'Europe/Athens',
						'Europe/Belfast'     => 'Europe/Belfast',
						'Europe/Belgrade'    => 'Europe/Belgrade',
						'Europe/Berlin'      => 'Europe/Berlin',
						'Europe/Bratislava'  => 'Europe/Bratislava',
						'Europe/Brussels'    => 'Europe/Brussels',
						'Europe/Bucharest'   => 'Europe/Bucharest',
						'Europe/Budapest'    => 'Europe/Budapest',
						'Europe/Busingen'    => 'Europe/Busingen',
						'Europe/Chisinau'    => 'Europe/Chisinau',
						'Europe/Copenhagen'  => 'Europe/Copenhagen',
						'Europe/Dublin'      => 'Europe/Dublin',
						'Europe/Gibraltar'   => 'Europe/Gibraltar',
						'Europe/Guernsey'    => 'Europe/Guernsey',
						'Europe/Helsinki'    => 'Europe/Helsinki',
						'Europe/Isle_of_Man' => 'Europe/Isle of Man',
						'Europe/Istanbul'    => 'Europe/Istanbul',
						'Europe/Jersey'      => 'Europe/Jersey',
						'Europe/Kaliningrad' => 'Europe/Kaliningrad',
						'Europe/Kirov'       => 'Europe/Kirov',
						'Europe/Kiev'        => 'Europe/Kiev', //This was renamed to: Europe/Kyiv in 2022-08-11
						'Europe/Kyiv'        => 'Europe/Kyiv', //This used to be: Europe/Kiev before 2022-08-11 -- Since this is the most recent name and should be used as of PHP v8.1.16 or newer.
						'Europe/Lisbon'      => 'Europe/Lisbon',
						'Europe/Ljubljana'   => 'Europe/Ljubljana',
						'Europe/London'      => 'Europe/London',
						'Europe/Luxembourg'  => 'Europe/Luxembourg',
						'Europe/Madrid'      => 'Europe/Madrid',
						'Europe/Malta'       => 'Europe/Malta',
						'Europe/Mariehamn'   => 'Europe/Mariehamn',
						'Europe/Minsk'       => 'Europe/Minsk',
						'Europe/Monaco'      => 'Europe/Monaco',
						'Europe/Moscow'      => 'Europe/Moscow',
						'Europe/Nicosia'     => 'Europe/Nicosia',
						'Europe/Oslo'        => 'Europe/Oslo',
						'Europe/Paris'       => 'Europe/Paris',
						'Europe/Podgorica'   => 'Europe/Podgorica',
						'Europe/Prague'      => 'Europe/Prague',
						'Europe/Riga'        => 'Europe/Riga',
						'Europe/Rome'        => 'Europe/Rome',
						'Europe/Samara'      => 'Europe/Samara',
						'Europe/San_Marino'  => 'Europe/San Marino',
						'Europe/Sarajevo'    => 'Europe/Sarajevo',
						'Europe/Saratov'     => 'Europe/Saratov',
						'Europe/Simferopol'  => 'Europe/Simferopol',
						'Europe/Skopje'      => 'Europe/Skopje',
						'Europe/Sofia'       => 'Europe/Sofia',
						'Europe/Stockholm'   => 'Europe/Stockholm',
						'Europe/Tallinn'     => 'Europe/Tallinn',
						'Europe/Tirane'      => 'Europe/Tirane',
						'Europe/Tiraspol'    => 'Europe/Tiraspol',
						'Europe/Ulyanovsk'   => 'Europe/Ulyanovsk',
						'Europe/Uzhgorod'    => 'Europe/Uzhgorod',
						'Europe/Vaduz'       => 'Europe/Vaduz',
						'Europe/Vatican'     => 'Europe/Vatican',
						'Europe/Vienna'      => 'Europe/Vienna',
						'Europe/Vilnius'     => 'Europe/Vilnius',
						'Europe/Volgograd'   => 'Europe/Volgograd',
						'Europe/Warsaw'      => 'Europe/Warsaw',
						'Europe/Zagreb'      => 'Europe/Zagreb',
						'Europe/Zaporozhye'  => 'Europe/Zaporozhye',
						'Europe/Zurich'      => 'Europe/Zurich',

						'-1000-Asia/Calcutta' => 'India', //GMT+5:30, same as Asia Calcutta
						'Indian/Antananarivo' => 'Indian/Antananarivo',
						'Indian/Chagos'       => 'Indian/Chagos',
						'Indian/Christmas'    => 'Indian/Christmas',
						'Indian/Cocos'        => 'Indian/Cocos',
						'Indian/Comoro'       => 'Indian/Comoro',
						'Indian/Kerguelen'    => 'Indian/Kerguelen',
						'Indian/Mahe'         => 'Indian/Mahe',
						'Indian/Maldives'     => 'Indian/Maldives',
						'Indian/Mauritius'    => 'Indian/Mauritius',
						'Indian/Mayotte'      => 'Indian/Mayotte',
						'Indian/Reunion'      => 'Indian/Reunion',

						'Pacific/Apia'         => 'Pacific/Apia',
						'Pacific/Auckland'     => 'Pacific/Auckland',
						'Pacific/Bougainville' => 'Pacific/Bougainville',
						'Pacific/Chatham'      => 'Pacific/Chatham',
						'Pacific/Chuuk'        => 'Pacific/Chuuk',
						'Pacific/Easter'       => 'Pacific/Easter',
						'Pacific/Efate'        => 'Pacific/Efate',
						'Pacific/Enderbury'    => 'Pacific/Enderbury',
						'Pacific/Fakaofo'      => 'Pacific/Fakaofo',
						'Pacific/Fiji'         => 'Pacific/Fiji',
						'Pacific/Funafuti'     => 'Pacific/Funafuti',
						'Pacific/Galapagos'    => 'Pacific/Galapagos',
						'Pacific/Gambier'      => 'Pacific/Gambier',
						'Pacific/Guadalcanal'  => 'Pacific/Guadalcanal',
						'Pacific/Guam'         => 'Pacific/Guam',
						'Pacific/Honolulu'     => 'Pacific/Honolulu',
						'Pacific/Johnston'     => 'Pacific/Johnston',
						'Pacific/Kanton'       => 'Pacific/Kanton',
						'Pacific/Kiritimati'   => 'Pacific/Kiritimati',
						'Pacific/Kosrae'       => 'Pacific/Kosrae',
						'Pacific/Kwajalein'    => 'Pacific/Kwajalein',
						'Pacific/Majuro'       => 'Pacific/Majuro',
						'Pacific/Marquesas'    => 'Pacific/Marquesas',
						'Pacific/Midway'       => 'Pacific/Midway',
						'Pacific/Nauru'        => 'Pacific/Nauru',
						'Pacific/Niue'         => 'Pacific/Niue',
						'Pacific/Norfolk'      => 'Pacific/Norfolk',
						'Pacific/Noumea'       => 'Pacific/Noumea',
						'Pacific/Pago_Pago'    => 'Pacific/Pago Pago',
						'Pacific/Palau'        => 'Pacific/Palau',
						'Pacific/Pitcairn'     => 'Pacific/Pitcairn',
						'Pacific/Pohnpei'      => 'Pacific/Pohnpei',
						'Pacific/Ponape'       => 'Pacific/Ponape',
						'Pacific/Port_Moresby' => 'Pacific/Port Moresby',
						'Pacific/Rarotonga'    => 'Pacific/Rarotonga',
						'Pacific/Saipan'       => 'Pacific/Saipan',
						'Pacific/Tahiti'       => 'Pacific/Tahiti',
						'Pacific/Tarawa'       => 'Pacific/Tarawa',
						'Pacific/Tongatapu'    => 'Pacific/Tongatapu',
						'Pacific/Truk'         => 'Pacific/Truk',
						'Pacific/Wake'         => 'Pacific/Wake',
						'Pacific/Wallis'       => 'Pacific/Wallis',
						'Pacific/Yap'          => 'Pacific/Yap',

						'Antarctica/Casey'          => 'Antarctica/Casey',
						'Antarctica/Davis'          => 'Antarctica/Davis',
						'Antarctica/DumontDUrville' => 'Antarctica/DumontDUrville',
						'Antarctica/Macquarie'      => 'Antarctica/Macquarie',
						'Antarctica/Mawson'         => 'Antarctica/Mawson',
						'Antarctica/McMurdo'        => 'Antarctica/McMurdo',
						'Antarctica/Palmer'         => 'Antarctica/Palmer',
						'Antarctica/Rothera'        => 'Antarctica/Rothera',
						'Antarctica/South_Pole'     => 'Antarctica/South Pole',
						'Antarctica/Syowa'          => 'Antarctica/Syowa',
						'Antarctica/Vostok'         => 'Antarctica/Vostok',
						'Arctic/Longyearbyen'       => 'Arctic/Longyearbyen',

						'GMT'        => 'GMT',
						'UTC'        => 'UTC',
				];

				//Add UTC offset suffix (ie: UTC-08:00 ) to each timezone label.
				foreach( $retval as $tmp_tz => $tmp_tz_name ) {
					$tz_offset_seconds = TTDate::getTimeZoneObject( Misc::trimSortPrefix( $tmp_tz ), true )->getOffset( new DateTime() );

					$tz_offset = TTDate::getTimeUnit( $tz_offset_seconds, 10 );
					if ( $tz_offset_seconds >= 0 ) {
						$tz_offset = '+'. $tz_offset;
					}

					$retval[$tmp_tz] = $tmp_tz_name . ' (UTC'. $tz_offset .')'; //No translation on UTC.
				}
				unset( $tmp_tz, $tmp_tz_name, $tz_offset, $tz_offset_seconds );

				if ( defined( 'TIMETREX_API' ) == true && TIMETREX_API == true ) {
					$retval = Misc::addSortPrefix( $retval );
				}
				break;
			case 'deprecated_timezone':
				$retval = [
						'YST9YDT'                  => 'America/Yakutat',
						'SystemV/YST9YDT'          => 'America/Yakutat',
						'AST4ADT'                  => 'Atlantic/Bermuda',
						'SystemV/AST4ADT'          => 'Atlantic/Bermuda',
						'Australia/ACT'            => 'Australia/Sydney',
						'Australia/LHI'            => 'Australia/Lord_Howe',
						'Australia/North'          => 'Australia/Darwin',
						'Australia/NSW'            => 'Australia/Sydney',
						'Australia/Queensland'     => 'Australia/Brisbane',
						'Australia/South'          => 'Australia/Adelaide',
						'Australia/Tasmania'       => 'Australia/Hobart',
						'Australia/Victoria'       => 'Australia/Melbourne',
						'Australia/West'           => 'Australia/Perth',
						'Brazil/Acre'              => 'America/Rio_Branco',
						'Brazil/DeNoronha'         => 'America/Noronha',
						'Brazil/East'              => 'America/Sao_Paulo',
						'Brazil/West'              => 'America/Manaus',
						'Canada/Atlantic'          => 'America/Halifax',
						'Canada/Central'           => 'America/Winnipeg',
						'Canada/Eastern'           => 'America/Toronto',
						'Canada/Mountain'          => 'America/Edmonton',
						'Canada/Newfoundland'      => 'America/St_Johns',
						'Canada/Pacific'           => 'America/Vancouver',
						'Canada/Saskatchewan'      => 'America/Regina',
						'Canada/East-Saskatchewan' => 'America/Regina',
						'Canada/Yukon'             => 'America/Whitehorse',
						'CET'                      => 'Europe/Paris',
						'Chile/Continental'        => 'America/Santiago',
						'Chile/EasterIsland'       => 'Pacific/Easter',
						'CST5CDT'                  => 'America/Chicago', //Bogus timezone that was in use briefly.
						'CST6CDT'                  => 'America/Chicago',
						'Cuba'                     => 'America/Havana',
						'EET'                      => 'Europe/Sofia',
						'Egypt'                    => 'Africa/Cairo',
						'Eire'                     => 'Europe/Dublin',
						'EST'                      => 'America/Cancun',
						'EST5EDT'                  => 'America/New_York',
						'Etc/Greenwich'            => 'GMT',
						'Etc/UCT'                  => 'UTC',
						'Etc/Universal'            => 'UTC',
						'Etc/Zulu'                 => 'UTC',
						'GB'                       => 'Europe/London',
						'GB-Eire'                  => 'Europe/London',
						'GMT+0'                    => 'GMT',
						'GMT0'                     => 'GMT',
						'GMT-0'                    => 'GMT',
						'Greenwich'                => 'GMT',
						'Hongkong'                 => 'Asia/Hong_Kong',
						'HST'                      => 'Pacific/Honolulu',
						'Iceland'                  => 'Atlantic/Reykjavik',
						'Iran'                     => 'Asia/Tehran',
						'Israel'                   => 'Asia/Jerusalem',
						'Jamaica'                  => 'America/Jamaica',
						'Japan'                    => 'Asia/Tokyo',
						'Kwajalein'                => 'Pacific/Kwajalein',
						'Libya'                    => 'Africa/Tripoli',
						'MET'                      => 'Europe/Paris',
						'Mexico/BajaNorte'         => 'America/Tijuana',
						'Mexico/BajaSur'           => 'America/Mazatlan',
						'Mexico/General'           => 'America/Mexico_City',
						'MST'                      => 'America/Phoenix',
						'MST7MDT'                  => 'America/Denver',
						'Navajo'                   => 'America/Denver',
						'NZ'                       => 'Pacific/Auckland',
						'NZ-CHAT'                  => 'Pacific/Chatham',
						'Poland'                   => 'Europe/Warsaw',
						'Portugal'                 => 'Europe/Lisbon',
						'PRC'                      => 'Asia/Shanghai',
						'PST8PDT'                  => 'America/Los_Angeles',
						'ROC'                      => 'Asia/Taipei',
						'ROK'                      => 'Asia/Seoul',
						'Singapore'                => 'Asia/Singapore',
						'Turkey'                   => 'Europe/Istanbul',
						'UCT'                      => 'UTC',
						'Universal'                => 'UTC',
						'US/Alaska'                => 'America/Anchorage',
						'US/Aleutian'              => 'America/Adak',
						'US/Arizona'               => 'America/Phoenix',
						'US/Central'               => 'America/Chicago',
						'US/Eastern'               => 'America/New_York',
						'US/East-Indiana'          => 'America/Indiana/Indianapolis',
						'US/Hawaii'                => 'Pacific/Honolulu',
						'US/Indiana-Starke'        => 'America/Indiana/Knox',
						'US/Michigan'              => 'America/Detroit',
						'US/Mountain'              => 'America/Denver',
						'US/Pacific'               => 'America/Los_Angeles',
						'US/Pacific-New'           => 'America/Los_Angeles',
						'US/Samoa'                 => 'Pacific/Pago_Pago',
						'WET'                      => 'Europe/Lisbon',
						'W-SU'                     => 'Europe/Moscow',
						'Zulu'                     => 'UTC',
						'Etc/GMT'                  => 'GMT',
						'Etc/GMT-0'                => 'GMT',
						'Etc/GMT-1'                => 'Europe/Amsterdam',
						'Etc/GMT-2'                => 'Europe/Athens',
						'Etc/GMT-3'                => 'Europe/Istanbul',
						'Etc/GMT-4'                => 'Europe/Samara',
						'Etc/GMT-5'                => 'Indian/Maldives',
						'Etc/GMT-6'                => 'Asia/Omsk',
						'Etc/GMT-7'                => 'Asia/Bangkok',
						'Etc/GMT-8'                => 'Asia/Hong_Kong',
						'Etc/GMT-9'                => 'Asia/Seoul',
						'Etc/GMT-10'               => 'Asia/Vladivostok',
						'Etc/GMT-11'               => 'Asia/Sakhalin',
						'Etc/GMT-12'               => 'Pacific/Wake',
						'Etc/GMT-13'               => 'Pacific/Tongatapu',
						'Etc/GMT-14'               => 'Pacific/Kiritimati',
						'Etc/GMT+0'                => 'GMT',
						'Etc/GMT+1'                => 'Atlantic/Cape_Verde',
						'Etc/GMT+2'                => 'Atlantic/South_Georgia',
						'Etc/GMT+3'                => 'America/Buenos_Aires',
						'Etc/GMT+4'                => 'America/Halifax',
						'Etc/GMT+5'                => 'America/New_York',
						'Etc/GMT+6'                => 'America/Chicago',
						'Etc/GMT+7'                => 'America/Denver',
						'Etc/GMT+8'                => 'America/Los_Angeles',
						'Etc/GMT+9'                => 'America/Anchorage',
						'Etc/GMT+10'               => 'Pacific/Honolulu',
						'Etc/GMT+11'               => 'Pacific/Pago_Pago',
				];

				//If the parent is specified as a country, use country specific replacements.
				if ( is_array( $params ) && isset( $params['country'] ) && strtolower( $params['country'] ) == 'ca' ) {
					$retval = array_merge(
							$retval,
							[
									'YST9YDT'                  => 'America/Whitehorse',
									'SystemV/YST9YDT'          => 'America/Whitehorse',
									'PST8PDT'                  => 'America/Vancouver',
									'MST7MDT'                  => 'America/Edmonton',
									'CST6CDT'                  => 'America/Winnipeg',
									'CST5CDT'                  => 'America/Winnipeg', //Bogus timezone that was in use briefly.
									'EST5EDT'                  => 'America/Toronto',
									'AST4ADT'                  => 'America/Halifax',
									'SystemV/AST4ADT'          => 'America/Halifax',
							]
					);
				}
				break;
			case 'location_timezone':
				//Country/Province to TimeZone map.
				$retval = [
						'CA' => [
								'AB' => 'America/Edmonton',
								'BC' => 'America/Vancouver',
								'SK' => 'America/Regina',
								'MB' => 'America/Winnipeg',
								'QC' => 'America/Montreal',
								'ON' => 'America/Toronto',
								'NL' => 'America/St_Johns',
								'NB' => 'America/Moncton',
								'NS' => 'America/Halifax',
								'PE' => 'America/Halifax',
								'NT' => 'America/Yellowknife',
								'YT' => 'America/Whitehorse',
								'NU' => 'America/Toronto',
						],
						'US' => [
								'AL' => 'America/Chicago',
								'AK' => 'America/Anchorage', //Hawaii
								'AZ' => 'America/Phoenix',
								'AR' => 'America/Chicago',
								'CA' => 'America/Los_Angeles',
								'CO' => 'America/Denver',
								'CT' => 'America/New_York',
								'DE' => 'America/New_York',
								'DC' => 'America/New_York',
								'FL' => [ 'America/New_York', 'America/Chicago' ], //Most common timezone first.
								'GA' => 'America/New_York',
								'HI' => 'Pacific/Honolulu',
								'ID' => [ 'America/Denver', 'America/Los_Angeles' ], //Most common timezone first.
								'IL' => 'America/Chicago',
								'IN' => [ 'America/New_York', 'America/Chicago' ],
								'IA' => 'America/Chicago',
								'KS' => [ 'America/Chicago', 'America/Denver' ],
								'KY' => [ 'America/New_York', 'America/Chicago' ],
								'LA' => 'America/Chicago',
								'ME' => 'America/New_York',
								'MD' => 'America/New_York',
								'MA' => 'America/New_York',
								'MI' => [ 'America/New_York', 'America/Denver' ],
								'MN' => 'America/Chicago',
								'MS' => 'America/Chicago',
								'MO' => 'America/Chicago',
								'MT' => 'America/Denver',
								'NE' => [ 'America/Chicago', 'America/Denver' ],
								'NV' => 'America/Los_Angeles',
								'NH' => 'America/New_York',
								'NM' => 'America/Denver',
								'NJ' => 'America/New_York',
								'NY' => 'America/New_York',
								'NC' => 'America/New_York',
								'ND' => [ 'America/Chicago', 'America/Denver' ],
								'OH' => 'America/New_York',
								'OK' => 'America/Chicago',
								'OR' => [ 'America/Los_Angeles', 'America/Denver' ],
								'PA' => 'America/New_York',
								'RI' => 'America/New_York',
								'SC' => 'America/New_York',
								'SD' => [ 'America/New_York', 'America/Denver' ],
								'TN' => [ 'America/Chicago', 'America/New_York' ],
								'TX' => [ 'America/Chicago', 'America/Denver' ],
								'UT' => 'America/Denver',
								'VT' => 'America/New_York',
								'VA' => 'America/New_York',
								'WA' => 'America/Los_Angeles',
								'WV' => 'America/New_York',
								'WI' => 'America/Chicago',
								'WY' => 'America/Denver',
						],
						'AG' => 'America/Antigua',
						'AI' => 'America/Anguilla',
						'AW' => 'America/Aruba',
						'BB' => 'America/Barbados',
						'BL' => 'America/St_Barthelemy',
						'BM' => 'Atlantic/Bermuda',
						'BQ' => 'America/Kralendijk',
						'BS' => 'America/Nassau',
						'CU' => 'America/Havana',
						'CW' => 'America/Curacao',
						'DM' => 'America/Dominica',
						'DO' => 'America/Santo_Domingo',
						'GD' => 'America/Grenada',
						'GP' => 'America/Guadeloupe',
						'HT' => 'America/Port-au-Prince',
						'JM' => 'America/Jamaica',
						'KN' => 'America/St_Kitts',
						'KY' => 'America/Cayman',
						'LC' => 'America/St_Lucia',
						'MF' => 'America/Marigot',
						'MQ' => 'America/Martinique',
						'MS' => 'America/Montserrat',
						'MX' => 'America/Mexico_City',
						'PR' => 'America/Puerto_Rico',
						'SX' => 'America/Lower_Princes',
						'TC' => 'America/Grand_Turk',
						'TT' => 'America/Port_of_Spain',
						'VC' => 'America/St_Vincent',
						'VG' => 'America/Tortola',
						'VI' => 'America/St_Thomas',
				];
				break;
			case 'area_code_timezone':
				//Area code to Country/Province/TimeZone map.
				$retval = [
						//See area code recent changes here: https://www.areacodehelp.com/area-code-news/area-code-changes.shtml
						211 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Local community info / referral services
						242 => [ 'country' => 'BS', 'province' => null, 'time_zone' => 'America/New_York' ], //	 Bahamas
						246 => [ 'country' => 'BB', 'province' => null, 'time_zone' => 'America/Nassau' ], //	 Barbados
						264 => [ 'country' => 'AI', 'province' => null, 'time_zone' => 'America/Anguilla' ], //	 Anguilla (split from 809)
						268 => [ 'country' => 'AG', 'province' => null, 'time_zone' => 'America/Antigua' ], //	 Antigua and Barbuda (split from 809)
						284 => [ 'country' => 'VG', 'province' => null, 'time_zone' => 'America/Tortola' ], //	 British Virgin Islands (split from 809)
						311 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Reserved for special applications
						345 => [ 'country' => 'KY', 'province' => null, 'time_zone' => 'America/New_York' ], //	 Cayman Islands
						411 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Reserved for special applications
						441 => [ 'country' => 'BM', 'province' => null, 'time_zone' => 'Atlantic/Bermuda' ], //	 Bermuda (part of what used to be 809)
						456 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Inbound International
						473 => [ 'country' => 'GD', 'province' => null, 'time_zone' => 'America/Grenada' ], //	 Grenada ("new" -- split from 809)
						500 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Personal Communication Service
						511 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Nationwide travel information
						555 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // Reserved for directory assistance applications
						600 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Canadian Services
						611 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Reserved for special applications
						649 => [ 'country' => 'TC', 'province' => null, 'time_zone' => 'America/New_York' ], //	 Turks & Caicos Islands
						664 => [ 'country' => 'MS', 'province' => null, 'time_zone' => 'America/Montserrat' ], //	 Montserrat (split from 809)
						684 => [ 'country' => 'AS', 'province' => null, 'time_zone' => null ], //1	 American Samoa
						700 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Interexchange Carrier Services
						710 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US Government
						711 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Telecommunications Relay Services
						758 => [ 'country' => 'LC', 'province' => null, 'time_zone' => 'America/St_Lucia' ], //	 St. Lucia (split from 809)
						767 => [ 'country' => 'DM', 'province' => null, 'time_zone' => 'America/Dominica' ], //	 Dominica (split from 809)
						784 => [ 'country' => 'VC', 'province' => null, 'time_zone' => 'America/St_Vincent' ], //	 St. Vincent & Grenadines (split from 809)
						800 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US/Canada toll free (see 888, 877, 866, 855, 844, 833, 822)
						809 => [ 'country' => 'DO', 'province' => null, 'time_zone' => 'America/Santo_Domingo' ], //	 Dominican Republic (see splits 264, 268, 284, 340, 441, 473, 664, 758, 767, 784, 868, 876; overlay 829)
						849 => [ 'country' => 'DO', 'province' => null, 'time_zone' => 'America/Santo_Domingo' ], //	 Dominican Republic (see splits 264, 268, 284, 340, 441, 473, 664, 758, 767, 784, 868, 876; overlay 829)
						811 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Reserved for special applications
						822 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US/Canada toll free (proposed, may not be in use yet)
						829 => [ 'country' => 'DO', 'province' => null, 'time_zone' => 'America/Santo_Domingo' ], //	 Dominican Republic (perm 1/31/05; mand 8/1/05; overlaid on 809)
						833 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US/Canada toll free (proposed, may not be in use yet)
						844 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US/Canada toll free (proposed, may not be in use yet)
						855 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US/Canada toll free (proposed, may not be in use yet)
						866 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US/Canada toll free
						868 => [ 'country' => 'TT', 'province' => null, 'time_zone' => 'America/Port_of_Spain' ], //	 Trinidad and Tobago ("new" -- see 809)
						869 => [ 'country' => 'KN', 'province' => null, 'time_zone' => 'America/St_Kitts' ], //	 St. Kitts & Nevis
						876 => [ 'country' => 'JM', 'province' => null, 'time_zone' => 'America/New_York' ], //	 Jamaica (split from 809)
						658 => [ 'country' => 'JM', 'province' => null, 'time_zone' => 'America/New_York' ], //	 Jamaica (split from 876)
						877 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US/Canada toll free
						880 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Paid Toll-Free Service
						881 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Paid Toll-Free Service
						882 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Paid Toll-Free Service
						888 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US/Canada toll free
						898 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // VoIP service
						900 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], // US toll calls -- prices vary with the number called
						911 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Emergency
						976 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Unassigned
						999 => [ 'country' => 'US', 'province' => null, 'time_zone' => null ], //	 Often used by carriers to indicate that the area code information is unavailable for CNID, even though the rest of the number is present
						525 => [ 'country' => 'MX', 'province' => null, 'time_zone' => 'America/Mexico_City' ], //-6	 Mexico: Mexico City area (country code + city code)
						403 => [ 'country' => 'CA', 'province' => 'AB', 'time_zone' => 'America/Edmonton' ], //	 Canada: Southern Alberta (see 780, 867)
						825 => [ 'country' => 'CA', 'province' => 'AB', 'time_zone' => 'America/Edmonton' ], //	 Canada: Southern Alberta
						780 => [ 'country' => 'CA', 'province' => 'AB', 'time_zone' => 'America/Edmonton' ], //	 Canada: Northern Alberta, north of Lacombe (see 403)
						587 => [ 'country' => 'CA', 'province' => 'AB', 'time_zone' => 'America/Edmonton' ], //	 Canada: Alberta
						250 => [ 'country' => 'CA', 'province' => 'BC', 'time_zone' => 'America/Vancouver' ], ///-7	 Canada: British Columbia (see 604)
						236 => [ 'country' => 'CA', 'province' => 'BC', 'time_zone' => 'America/Vancouver' ], ///-7	 Canada: British Columbia (see 604)
						672 => [ 'country' => 'CA', 'province' => 'BC', 'time_zone' => 'America/Vancouver' ], ///-7	 Canada: British Columbia (see 604)
						604 => [ 'country' => 'CA', 'province' => 'BC', 'time_zone' => 'America/Vancouver' ], //	 Canada: British Columbia: Greater Vancouver (overlay 778, perm 11/3/01; see 250)
						778 => [ 'country' => 'CA', 'province' => 'BC', 'time_zone' => 'America/Vancouver' ], //	 Canada: British Columbia: Greater Vancouver (overlaid on 604, per 11/3/01; see also 250)
						204 => [ 'country' => 'CA', 'province' => 'MB', 'time_zone' => 'America/Winnipeg' ], //	 Canada: Manitoba
						431 => [ 'country' => 'CA', 'province' => 'MB', 'time_zone' => 'America/Winnipeg' ], //	 Canada: Manitoba
						506 => [ 'country' => 'CA', 'province' => 'NB', 'time_zone' => 'America/Moncton' ], //	 Canada: New Brunswick
						226 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: SW Ontario: Windsor (overlaid on 519; eff 6/06)
						548 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: SW Ontario
						289 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: S Cent. Ontario: Greater Toronto Area -- Durham, Halton, Hamilton-Wentworth, Niagara, Peel, York, and southern Simcoe County (excluding Toronto -- overlaid on 905, eff 6/9/01)
						742 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: S Cent. Ontario
						416 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: S Cent. Ontario: Toronto (see overlay 647, eff 3/5/01)
						519 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: SW Ontario: Windsor (see overlay 226)
						613 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: SE Ontario: Ottawa
						343 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: SE Ontario
						647 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: S Cent. Ontario: Toronto (overlaid on 416; eff 3/5/01)
						437 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: S Cent. Ontario
						705 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: NE Ontario: Sault Ste. Marie/N Ontario: N Bay, Sudbury
						249 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: NE Ontario
						807 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => null ], ///-6	 Canada: W Ontario: Thunder Bay region to Manitoba border (**NON-SPECIFIC: EST, CST)
						905 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: S Cent. Ontario: Greater Toronto Area -- Durham, Halton, Hamilton-Wentworth, Niagara, Peel, York, and southern Simcoe County (excluding Toronto -- see overlay 289 [eff 6/9/01], splits 416, 647)
						365 => [ 'country' => 'CA', 'province' => 'ON', 'time_zone' => 'America/Toronto' ], //	 Canada: S Cent. Ontario: Greater Toronto Area
						418 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], ///-4	 Canada: NE Quebec: Quebec
						438 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], //	 Canada: SW Quebec: Montreal city (overlaid on 514, [delayed until 6/06] eff 10/10/03, mand 2/7/04)
						450 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], ///-4	 Canada: Southeastern Quebec; suburbs outside metro Montreal
						579 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], ///-4	 Canada: Southeastern Quebec;
						514 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], //	 Canada: SW Quebec: Montreal city (see overlay 438, eff 10/10/03, mand 2/7/04)
						819 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], //	 NW Quebec: Trois Rivieres, Sherbrooke, Outaouais (Gatineau, Hull), and the Laurentians (up to St Jovite / Tremblant) (see 867)
						873 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], //	 NW Quebec
						581 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], //	 Quebec
						367 => [ 'country' => 'CA', 'province' => 'QC', 'time_zone' => 'America/Montreal' ], //	 Quebec
						306 => [ 'country' => 'CA', 'province' => 'SK', 'time_zone' => 'America/Regina' ], ///-7*	 Canada: Saskatchewan
						474 => [ 'country' => 'CA', 'province' => 'SK', 'time_zone' => 'America/Regina' ], ///-7*	 Canada: Saskatchewan
						639 => [ 'country' => 'CA', 'province' => 'SK', 'time_zone' => 'America/Regina' ], ///-7*	 Canada: Saskatchewan
						867 => [ 'country' => 'CA', 'province' => 'YT', 'time_zone' => null ], ///-6/-7/-8	 Canada: Yukon, Northwest Territories, Nunavut (split from 403/819) (**NON-SPECIFIC: CST, MST, PST )
						709 => [ 'country' => 'CA', 'province' => 'NL', 'time_zone' => null ], ///-3.5	 Canada: Newfoundland and Labrador (**NON-SPECIFIC: NST, AST)
						902 => [ 'country' => 'CA', 'province' => 'NS', 'time_zone' => 'America/Halifax' ], //	 Canada: Nova Scotia, Prince Edward Island
						782 => [ 'country' => 'CA', 'province' => 'NS', 'time_zone' => 'America/Halifax' ], //	 Canada: Nova Scotia, Prince Edward Island
						907 => [ 'country' => 'US', 'province' => 'AK', 'time_zone' => 'America/Anchorage' ], //	 Alaska
						205 => [ 'country' => 'US', 'province' => 'AL', 'time_zone' => 'America/Chicago' ], //	 Central Alabama (including Birmingham; excludes the southeastern corner of Alabama and the deep south; see splits 256 and 334)
						659 => [ 'country' => 'US', 'province' => 'AL', 'time_zone' => 'America/Chicago' ], //	 Central Alabama
						251 => [ 'country' => 'US', 'province' => 'AL', 'time_zone' => 'America/Chicago' ], //	 S Alabama: Mobile and coastal areas, Jackson, Evergreen, Monroeville (split from 334, eff 6/18/01; see also 205, 256)
						256 => [ 'country' => 'US', 'province' => 'AL', 'time_zone' => 'America/Chicago' ], //	 E and N Alabama (Huntsville, Florence, Gadsden; split from 205; see also 334)
						938 => [ 'country' => 'US', 'province' => 'AL', 'time_zone' => 'America/Chicago' ], //	 Alabama
						334 => [ 'country' => 'US', 'province' => 'AL', 'time_zone' => 'America/Chicago' ], //	 S Alabama: Auburn/Opelika, Montgomery and coastal areas (part of what used to be 205; see also 256, split 251)
						479 => [ 'country' => 'US', 'province' => 'AR', 'time_zone' => 'America/Chicago' ], //	 NW Arkansas: Fort Smith, Fayetteville, Springdale, Bentonville (SPLIt from 501, perm 1/19/02, mand 7/20/02)
						501 => [ 'country' => 'US', 'province' => 'AR', 'time_zone' => 'America/Chicago' ], //	 Central Arkansas: Little Rock, Hot Springs, Conway (see split 479)
						870 => [ 'country' => 'US', 'province' => 'AR', 'time_zone' => 'America/Chicago' ], //	 Arkansas: areas outside of west/central AR: Jonesboro, etc
						480 => [ 'country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix' ], //*	 Arizona: East Phoenix (see 520; also Phoenix split 602, 623)
						520 => [ 'country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix' ], //*	 SE Arizona: Tucson area (split from 602; see split 928)
						602 => [ 'country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix' ], //*	 Arizona: Phoenix (see 520; also Phoenix split 480, 623)
						623 => [ 'country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix' ], //*	 Arizona: West Phoenix (see 520; also Phoenix split 480, 602)
						928 => [ 'country' => 'US', 'province' => 'AZ', 'time_zone' => 'America/Phoenix' ], //*	 Central and Northern Arizona: Prescott, Flagstaff, Yuma (split from 520)
						209 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 Cent. California: Stockton (see split 559)
						213 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: Los Angeles (see 310, 323, 626, 818)
						310 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: Beverly Hills, West Hollywood, West Los Angeles (see split 562; overlay 424)
						323 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: Los Angeles (outside downtown: Hollywood; split from 213)
						341 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 (overlay on 510; SUSPENDED)
						369 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 Solano County (perm 12/2/00, mand 6/2/01)
						408 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 Cent. Coastal California: San Jose (see overlay 669)
						415 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: San Francisco County and Marin County on the north side of the Golden Gate Bridge, extending north to Sonoma County (see 650)
						424 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: Los Angeles (see split 562; overlaid on 310 mand 7/26/06)
						442 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 Far north suburbs of San Diego (Oceanside, Escondido, SUSPENDED -- originally perm 10/21/00, mand 4/14/01)
						510 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: Oakland, East Bay (see 925)
						530 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 NE California: Eldorado County area, excluding Eldorado Hills itself: incl cities of Auburn, Chico, Redding, So. Lake Tahoe, Marysville, Nevada City/Grass Valley (split from 916)
						559 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 Central California: Fresno (split from 209)
						562 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: Long Beach (split from 310 Los Angeles)
						619 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: San Diego (see split 760; overlay 858, 935)
						626 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 E S California: Pasadena (split from 818 Los Angeles)
						627 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 No longer in use [was Napa, Sonoma counties (perm 10/13/01, mand 4/13/02); now 707]
						628 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 (Region unknown; perm 10/21/00)
						650 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: Peninsula south of San Francisco -- San Mateo County, parts of Santa Clara County (split from 415)
						661 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: N Los Angeles, Mckittrick, Mojave, Newhall, Oildale, Palmdale, Taft, Tehachapi, Bakersfield, Earlimart, Lancaster (split from 805)
						669 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 Cent. Coastal California: San Jose (rejected was: overlaid on 408)
						707 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 NW California: Santa Rosa, Napa, Vallejo, American Canyon, Fairfield
						714 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 North and Central Orange County (see split 949)
						747 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: Los Angeles, Agoura Hills, Calabasas, Hidden Hills, and Westlake Village (see 818; implementation suspended)
						760 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: San Diego North County to Sierra Nevada (split from 619)
						764 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 (overlay on 650; SUSPENDED)
						805 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S Cent. and Cent. Coastal California: Ventura County, Santa Barbara County: San Luis Obispo, Thousand Oaks, Carpinteria, Santa Barbara, Santa Maria, Lompoc, Santa Ynez Valley / Solvang (see 661 split)
						820 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S Cent. and Cent. Coastal California
						818 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: Los Angeles: San Fernando Valley (see 213, 310, 562, 626, 747)
						831 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: central coast area from Santa Cruz through Monterey County
						858 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: San Diego (see split 760; overlay 619, 935)
						909 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: Inland empire: San Bernardino (see split 951), Riverside
						840 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California
						916 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 NE California: Sacramento, Walnut Grove, Lincoln, Newcastle and El Dorado Hills (split to 530)
						279 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 NE California
						925 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: Contra Costa area: Antioch, Concord, Pleasanton, Walnut Creek (split from 510)
						935 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 S California: San Diego (see split 760; overlay 858, 619; assigned but not in use)
						949 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: S Coastal Orange County (split from 714)
						951 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California: W Riverside County (split from 909; eff 7/17/04)
						657 => [ 'country' => 'US', 'province' => 'CA', 'time_zone' => 'America/Los_Angeles' ], //	 California
						303 => [ 'country' => 'US', 'province' => 'CO', 'time_zone' => 'America/Denver' ], //	 Central Colorado: Denver (see 970, also 720 overlay)
						719 => [ 'country' => 'US', 'province' => 'CO', 'time_zone' => 'America/Denver' ], //	 SE Colorado: Pueblo, Colorado Springs
						720 => [ 'country' => 'US', 'province' => 'CO', 'time_zone' => 'America/Denver' ], //	 Central Colorado: Denver (overlaid on 303)
						970 => [ 'country' => 'US', 'province' => 'CO', 'time_zone' => 'America/Denver' ], //	 N and W Colorado (part of what used to be 303)
						203 => [ 'country' => 'US', 'province' => 'CT', 'time_zone' => 'America/New_York' ], //	 Connecticut: Fairfield County and New Haven County; Bridgeport, New Haven (see 860)
						475 => [ 'country' => 'US', 'province' => 'CT', 'time_zone' => 'America/New_York' ], //	 Connecticut: New Haven, Greenwich, southwestern (postponed; was perm 1/6/01; mand 3/1/01???)
						860 => [ 'country' => 'US', 'province' => 'CT', 'time_zone' => 'America/New_York' ], //	 Connecticut: areas outside of Fairfield and New Haven Counties (split from 203, overlay 959)
						959 => [ 'country' => 'US', 'province' => 'CT', 'time_zone' => 'America/New_York' ], //	 Connecticut: Hartford, New London (postponed; was overlaid on 860 perm 1/6/01; mand 3/1/01???)
						202 => [ 'country' => 'US', 'province' => 'DC', 'time_zone' => 'America/New_York' ], //	 Washington, D.C.
						302 => [ 'country' => 'US', 'province' => 'DE', 'time_zone' => 'America/New_York' ], //	 Delaware
						239 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Florida (Lee, Collier, and Monroe Counties, excl the Keys; see 305; eff 3/11/02; mand 3/11/03)
						305 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 SE Florida: Miami, the Keys (see 786, 954; 239)
						321 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Florida: Brevard County, Cape Canaveral area; Metro Orlando (split from 407)
						352 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Florida: Gainesville area, Ocala, Crystal River (split from 904)
						386 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 N central Florida: Lake City (split from 904, perm 2/15/01, mand 11/5/01)
						407 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Central Florida: Metro Orlando (see overlay 689, eff 7/02; split 321)
						561 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 S. Central Florida: Palm Beach County (West Palm Beach, Boca Raton, Vero Beach; see split 772, eff 2/11/02; mand 11/11/02)
						689 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Central Florida: Metro Orlando (see overlay 321; overlaid on 407, assigned but not in use)
						727 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Florida Tampa Metro: Saint Petersburg, Clearwater (Pinellas and parts of Pasco County; split from 813)
						754 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Florida: Broward County area, incl Ft. Lauderdale (overlaid on 954; perm 8/1/01, mand 9/1/01)
						772 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 S. Central Florida: St. Lucie, Martin, and Indian River counties (split from 561; eff 2/11/02; mand 11/11/02)
						786 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 SE Florida, Monroe County (Miami; overlaid on 305)
						813 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 SW Florida: Tampa Metro (splits 727 St. Petersburg, Clearwater, and 941 Sarasota)
						850 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => null ], ///-5	 Florida panhandle, from east of Tallahassee to Pensacola (split from 904); western panhandle (Pensacola, Panama City) are UTC-6 (**NON-SPECIFIC: EST, CST)
						448 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => null ], ///-5	 Florida panhandle, from east of Tallahassee to Pensacola (split from 904); western panhandle (Pensacola, Panama City) are UTC-6 (**NON-SPECIFIC: EST, CST)
						863 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Florida: Lakeland, Polk County (split from 941)
						904 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 N Florida: Jacksonville (see splits 352, 386, 850)
						927 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Florida: Cellular coverage in Orlando area
						941 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 SW Florida: Sarasota and Manatee counties (part of what used to be 813; see split 863)
						954 => [ 'country' => 'US', 'province' => 'FL', 'time_zone' => 'America/New_York' ], //	 Florida: Broward County area, incl Ft. Lauderdale (part of what used to be 305, see overlay 754)
						229 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 SW Georgia: Albany (split from 912; see also 478; perm 8/1/00)
						404 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 N Georgia: Atlanta and suburbs (see overlay 678, split 770)
						470 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 Georgia: Greater Atlanta Metropolitan Area (overlaid on 404/770/678; mand 9/2/01)
						478 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 Central Georgia: Macon (split from 912; see also 229; perm 8/1/00; mand 8/1/01)
						678 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 N Georgia: metropolitan Atlanta (overlay; see 404, 770)
						706 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 N Georgia: Columbus, Augusta (see overlay 762)
						762 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 N Georgia: Columbus, Augusta (overlaid on 706)
						770 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 Georgia: Atlanta suburbs: outside of I-285 ring road (part of what used to be 404; see also overlay 678)
						912 => [ 'country' => 'US', 'province' => 'GA', 'time_zone' => 'America/New_York' ], //	 SE Georgia: Savannah (see splits 229, 478)
						671 => [ 'country' => 'US', 'province' => 'GU', 'time_zone' => 'Pacific/Guam' ],     //0*	 Guam
						808 => [ 'country' => 'US', 'province' => 'HI', 'time_zone' => 'Pacific/Honolulu' ], //0*	 Hawaii
						319 => [ 'country' => 'US', 'province' => 'IA', 'time_zone' => 'America/Chicago' ], //	 E Iowa: Cedar Rapids (see split 563)
						515 => [ 'country' => 'US', 'province' => 'IA', 'time_zone' => 'America/Chicago' ], //	 Cent. Iowa: Des Moines (see split 641)
						563 => [ 'country' => 'US', 'province' => 'IA', 'time_zone' => 'America/Chicago' ], //	 E Iowa: Davenport, Dubuque (split from 319, eff 3/25/01)
						641 => [ 'country' => 'US', 'province' => 'IA', 'time_zone' => 'America/Chicago' ], //	 Iowa: Mason City, Marshalltown, Creston, Ottumwa (split from 515; perm 7/9/00)
						712 => [ 'country' => 'US', 'province' => 'IA', 'time_zone' => 'America/Chicago' ], //	 W Iowa: Council Bluffs
						208 => [ 'country' => 'US', 'province' => 'ID', 'time_zone' => null ], ///-8	 Idaho (**NON-SPECIFIC: America/Denver, America/Los_Angeles)
						986 => [ 'country' => 'US', 'province' => 'ID', 'time_zone' => 'America/Los_Angeles' ], ///-8	 Idaho (**NON-SPECIFIC: America/Denver, America/Los_Angeles)
						217 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Cent. Illinois: Springfield
						447 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Cent. Illinois
						224 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Northern NE Illinois: Evanston, Waukegan, Northbrook (overlay on 847, eff 1/5/02)
						309 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 W Cent. Illinois: Peoria
						312 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Illinois: Chicago (downtown only -- in the loop; see 773; overlay 872)
						331 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 W NE Illinois, western suburbs of Chicago (part of what used to be 708; overlaid on 630; eff 7/07)
						464 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Illinois: south suburbs of Chicago (see 630; overlaid on 708)
						618 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 S Illinois: Centralia
						630 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 W NE Illinois, western suburbs of Chicago (part of what used to be 708; overlay 331)
						708 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Illinois: southern and western suburbs of Chicago (see 630; overlay 464)
						773 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Illinois: city of Chicago, outside the loop (see 312; overlay 872)
						779 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 NW Illinois: Rockford, Kankakee (overlaid on 815; eff 8/19/06, mand 2/17/07)
						815 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 NW Illinois: Rockford, Kankakee (see overlay 779; eff 8/19/06, mand 2/17/07)
						847 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Northern NE Illinois: northwestern suburbs of chicago (Evanston, Waukegan, Northbrook; see overlay 224)
						872 => [ 'country' => 'US', 'province' => 'IL', 'time_zone' => 'America/Chicago' ], //	 Illinois: Chicago (downtown only -- in the loop; see 773; overlaid on 312 and 773)
						219 => [ 'country' => 'US', 'province' => 'IN', 'time_zone' => null ], ///-5	 NW Indiana: Gary (see split 574, 260) (**NON-SPECIFIC: EST, CST)
						260 => [ 'country' => 'US', 'province' => 'IN', 'time_zone' => 'America/New_York' ], //	 NE Indiana: Fort Wayne (see 219)
						317 => [ 'country' => 'US', 'province' => 'IN', 'time_zone' => 'America/New_York' ], //	 Cent. Indiana: Indianapolis (see 765)
						463 => [ 'country' => 'US', 'province' => 'IN', 'time_zone' => 'America/New_York' ], //	 Cent. Indiana
						574 => [ 'country' => 'US', 'province' => 'IN', 'time_zone' => null ], //	 N Indiana: Elkhart, South Bend (split from 219) (**NON-SPECIFIC: EST, CST)
						765 => [ 'country' => 'US', 'province' => 'IN', 'time_zone' => 'America/New_York' ], //	 Indiana: outside Indianapolis (split from 317)
						812 => [ 'country' => 'US', 'province' => 'IN', 'time_zone' => null ], ///-5	 S Indiana: Evansville, Cincinnati outskirts in IN, Columbus, Bloomington (mostly GMT-5) (**NON-SPECIFIC: EST, CST)
						930 => [ 'country' => 'US', 'province' => 'IN', 'time_zone' => 'America/New_York' ], ///-5	 S Indiana
						316 => [ 'country' => 'US', 'province' => 'KS', 'time_zone' => 'America/Chicago' ], //	 S Kansas: Wichita (see split 620)
						620 => [ 'country' => 'US', 'province' => 'KS', 'time_zone' => null ], //	 S Kansas: Wichita (split from 316; perm 2/3/01) (**NON-SPECIFIC: CST, MST)
						785 => [ 'country' => 'US', 'province' => 'KS', 'time_zone' => null ], //	 N & W Kansas: Topeka (split from 913)  (**NON-SPECIFIC: CST, MST)
						913 => [ 'country' => 'US', 'province' => 'KS', 'time_zone' => 'America/Chicago' ], //	 Kansas: Kansas City area (see 785)
						270 => [ 'country' => 'US', 'province' => 'KY', 'time_zone' => null ], //	 W Kentucky: Bowling Green, Paducah (split from 502) (**NON-SPECIFIC: EST, CST)
						502 => [ 'country' => 'US', 'province' => 'KY', 'time_zone' => 'America/New_York' ], //	 N Central Kentucky: Louisville (see 270)
						606 => [ 'country' => 'US', 'province' => 'KY', 'time_zone' => null ], ///-6	 E Kentucky: area east of Frankfort: Ashland (see 859) (**NON-SPECIFIC: EST, CST)
						859 => [ 'country' => 'US', 'province' => 'KY', 'time_zone' => 'America/New_York' ], //	 N and Central Kentucky: Lexington; suburban KY counties of Cincinnati OH metro area; Covington, Newport, Ft. Thomas, Ft. Wright, Florence (split from 606)
						364 => [ 'country' => 'US', 'province' => 'KY', 'time_zone' => null ], // (**NON-SPECIFIC: EST, CST)
						225 => [ 'country' => 'US', 'province' => 'LA', 'time_zone' => 'America/Chicago' ], //	 Louisiana: Baton Rouge, New Roads, Donaldsonville, Albany, Gonzales, Greensburg, Plaquemine, Vacherie (split from 504)
						318 => [ 'country' => 'US', 'province' => 'LA', 'time_zone' => 'America/Chicago' ], //	 N Louisiana: Shreveport, Ruston, Monroe, Alexandria (see split 337)
						337 => [ 'country' => 'US', 'province' => 'LA', 'time_zone' => 'America/Chicago' ], //	 SW Louisiana: Lake Charles, Lafayette (see split 318)
						504 => [ 'country' => 'US', 'province' => 'LA', 'time_zone' => 'America/Chicago' ], //	 E Louisiana: New Orleans metro area (see splits 225, 985)
						985 => [ 'country' => 'US', 'province' => 'LA', 'time_zone' => 'America/Chicago' ], //	 E Louisiana: SE/N shore of Lake Pontchartrain: Hammond, Slidell, Covington, Amite, Kentwood, area SW of New Orleans, Houma, Thibodaux, Morgan City (split from 504; perm 2/12/01; mand 10/22/01)
						339 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 Massachusetts: Boston suburbs, to the south and west (see splits 617, 508; overlaid on 781, eff 5/2/01)
						351 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 Massachusetts: north of Boston to NH, 508, and 781 (overlaid on 978, eff 4/2/01)
						413 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 W Massachusetts: Springfield
						508 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 Cent. Massachusetts: Framingham; Cape Cod (see split 978, overlay 774)
						617 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 Massachusetts: greater Boston (see overlay 857)
						774 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 Cent. Massachusetts: Framingham; Cape Cod (see split 978, overlaid on 508, eff 4/2/01)
						781 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 Massachusetts: Boston surburbs, to the north and west (see splits 617, 508; overlay 339)
						857 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 Massachusetts: greater Boston (overlaid on 617, eff 4/2/01)
						978 => [ 'country' => 'US', 'province' => 'MA', 'time_zone' => 'America/New_York' ], //	 Massachusetts: north of Boston to NH (see split 978 -- this is the northern half of old 508; see overlay 351)
						240 => [ 'country' => 'US', 'province' => 'MD', 'time_zone' => 'America/New_York' ], //	 W Maryland: Silver Spring, Frederick, Gaithersburg (overlay, see 301)
						301 => [ 'country' => 'US', 'province' => 'MD', 'time_zone' => 'America/New_York' ], //	 W Maryland: Silver Spring, Frederick, Camp Springs, Prince George's County (see 240)
						410 => [ 'country' => 'US', 'province' => 'MD', 'time_zone' => 'America/New_York' ], //	 E Maryland: Baltimore, Annapolis, Chesapeake Bay area, Ocean City (see 443)
						443 => [ 'country' => 'US', 'province' => 'MD', 'time_zone' => 'America/New_York' ], //	 E Maryland: Baltimore, Annapolis, Chesapeake Bay area, Ocean City (overlaid on 410)
						207 => [ 'country' => 'US', 'province' => 'ME', 'time_zone' => 'America/New_York' ], //	 Maine
						231 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 W Michigan: Northwestern portion of lower Peninsula; Traverse City, Muskegon, Cheboygan, Alanson
						248 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 Michigan: Oakland County, Pontiac (split from 810; see overlay 947)
						269 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 SW Michigan: Kalamazoo, Saugatuck, Hastings, Battle Creek, Sturgis to Lake Michigan (split from 616)
						278 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 Michigan (overlaid on 734, SUSPENDED)
						313 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 Michigan: Detroit and suburbs (see 734, overlay 679)
						517 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 Cent. Michigan: Lansing (see split 989)
						586 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 Michigan: Macomb County (split from 810; perm 9/22/01, mand 3/23/02)
						616 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 W Michigan: Holland, Grand Haven, Greenville, Grand Rapids, Ionia (see split 269)
						679 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], ///-6	 Michigan: Dearborn area (overlaid on 313; assigned but not in use)
						734 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 SE Michigan: west and south of Detroit -- Ann Arbor, Monroe (split from 313)
						810 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 E Michigan: Flint, Pontiac (see 248; split 586)
						906 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => null ], ///-5	 Upper Peninsula Michigan: Sault Ste. Marie, Escanaba, Marquette (UTC-6 towards the WI border) (**NON-SPECIFIC: EST, CST)
						947 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], ///-6	 Michigan: Oakland County (overlays 248, perm 5/5/01)
						989 => [ 'country' => 'US', 'province' => 'MI', 'time_zone' => 'America/New_York' ], //	 Upper central Michigan: Mt Pleasant, Saginaw (split from 517; perm 4/7/01)
						218 => [ 'country' => 'US', 'province' => 'MN', 'time_zone' => 'America/Chicago' ], //	 N Minnesota: Duluth
						320 => [ 'country' => 'US', 'province' => 'MN', 'time_zone' => 'America/Chicago' ], //	 Cent. Minnesota: Saint Cloud (rural Minn, excl St. Paul/Minneapolis)
						507 => [ 'country' => 'US', 'province' => 'MN', 'time_zone' => 'America/Chicago' ], //	 S Minnesota: Rochester, Mankato, Worthington
						612 => [ 'country' => 'US', 'province' => 'MN', 'time_zone' => 'America/Chicago' ], //	 Cent. Minnesota: Minneapolis (split from St. Paul, see 651; see splits 763, 952)
						651 => [ 'country' => 'US', 'province' => 'MN', 'time_zone' => 'America/Chicago' ], //	 Cent. Minnesota: St. Paul (split from Minneapolis, see 612)
						763 => [ 'country' => 'US', 'province' => 'MN', 'time_zone' => 'America/Chicago' ], //	 Minnesota: Minneapolis NW (split from 612; see also 952)
						952 => [ 'country' => 'US', 'province' => 'MN', 'time_zone' => 'America/Chicago' ], //	 Minnesota: Minneapolis SW, Bloomington (split from 612; see also 763)
						314 => [ 'country' => 'US', 'province' => 'MO', 'time_zone' => 'America/Chicago' ], //	 SE Missouri: St Louis city and parts of the metro area only (see 573, 636, overlay 557)
						417 => [ 'country' => 'US', 'province' => 'MO', 'time_zone' => 'America/Chicago' ], //	 SW Missouri: Springfield
						557 => [ 'country' => 'US', 'province' => 'MO', 'time_zone' => 'America/Chicago' ], //	 SE Missouri: St Louis metro area only (cancelled: overlaid on 314)
						573 => [ 'country' => 'US', 'province' => 'MO', 'time_zone' => 'America/Chicago' ], //	 SE Missouri: excluding St Louis metro area, includes Central/East Missouri, area between St. Louis and Kansas City
						636 => [ 'country' => 'US', 'province' => 'MO', 'time_zone' => 'America/Chicago' ], //	 Missouri: W St. Louis metro area of St. Louis county, St. Charles County, Jefferson County area south (between 314 and 573)
						660 => [ 'country' => 'US', 'province' => 'MO', 'time_zone' => 'America/Chicago' ], //	 N Missouri (split from 816)
						816 => [ 'country' => 'US', 'province' => 'MO', 'time_zone' => 'America/Chicago' ], //	 N Missouri: Kansas City (see split 660, overlay 975)
						975 => [ 'country' => 'US', 'province' => 'MO', 'time_zone' => 'America/Chicago' ], //	 N Missouri: Kansas City (overlaid on 816)
						670 => [ 'country' => 'US', 'province' => 'MP', 'time_zone' => null ], //0*	 Commonwealth of the Northern Mariana Islands (CNMI, US Commonwealth)
						228 => [ 'country' => 'US', 'province' => 'MS', 'time_zone' => 'America/Chicago' ], //	 S Mississippi (coastal areas, Biloxi, Gulfport; split from 601)
						601 => [ 'country' => 'US', 'province' => 'MS', 'time_zone' => 'America/Chicago' ], //	 Mississippi: Meridian, Jackson area (see splits 228, 662; overlay 769)
						662 => [ 'country' => 'US', 'province' => 'MS', 'time_zone' => 'America/Chicago' ], //	 N Mississippi: Tupelo, Grenada (split from 601)
						769 => [ 'country' => 'US', 'province' => 'MS', 'time_zone' => 'America/Chicago' ], //	 Mississippi: Meridian, Jackson area (overlaid on 601; perm 7/19/04, mand 3/14/05)
						406 => [ 'country' => 'US', 'province' => 'MT', 'time_zone' => 'America/Denver' ], //	 Montana
						252 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 E North Carolina (Rocky Mount; split from 919)
						336 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 Cent. North Carolina: Greensboro, Winston-Salem, High Point (split from 910)
						743 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 Cent. North Carolina
						704 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 W North Carolina: Charlotte (see split 828, overlay 980)
						828 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 W North Carolina: Asheville (split from 704)
						910 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 S Cent. North Carolina: Fayetteville, Wilmington (see 336)
						919 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 E North Carolina: Raleigh (see split 252, overlay 984)
						980 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 North Carolina: (overlay on 704; perm 5/1/00, mand 3/15/01)
						984 => [ 'country' => 'US', 'province' => 'NC', 'time_zone' => 'America/New_York' ], //	 E North Carolina: Raleigh (overlaid on 919, perm 8/1/01, mand 2/5/02 POSTPONED)
						701 => [ 'country' => 'US', 'province' => 'ND', 'time_zone' => null ], //	 North Dakota (**NON-SPECIFIC: CST, MST)
						308 => [ 'country' => 'US', 'province' => 'NE', 'time_zone' => null ], //   -7 W Nebraska: North Platte (**NON-SPECIFIC: America/Chicago, America/Denver)
						402 => [ 'country' => 'US', 'province' => 'NE', 'time_zone' => 'America/Chicago' ], //	 E Nebraska: Omaha, Lincoln
						531 => [ 'country' => 'US', 'province' => 'NE', 'time_zone' => 'America/Chicago' ], //	 E Nebraska: Omaha, Lincoln (Similar to 402 Area Code)
						603 => [ 'country' => 'US', 'province' => 'NH', 'time_zone' => 'America/New_York' ], //	 New Hampshire
						201 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 N New Jersey: Jersey City, Hackensack (see split 973, overlay 551)
						551 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 N New Jersey: Jersey City, Hackensack (overlaid on 201)
						609 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 S New Jersey: Trenton (see 856)
						640 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 S New Jersey
						732 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 Cent. New Jersey: Toms River, New Brunswick, Bound Brook (see overlay 848)
						848 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 Cent. New Jersey: Toms River, New Brunswick, Bound Brook (see overlay 732)
						856 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 SW New Jersey: greater Camden area, Mt Laurel (split from 609)
						862 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 N New Jersey: Newark Paterson Morristown (overlaid on 973)
						908 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 Cent. New Jersey: Elizabeth, Basking Ridge, Somerville, Bridgewater, Bound Brook
						973 => [ 'country' => 'US', 'province' => 'NJ', 'time_zone' => 'America/New_York' ], //	 N New Jersey: Newark, Paterson, Morristown (see overlay 862; split from 201)
						505 => [ 'country' => 'US', 'province' => 'NM', 'time_zone' => 'America/Denver' ], //	 North central and northwestern New Mexico (Albuquerque, Santa Fe, Los Alamos; see split 575, eff 10/07/07)
						575 => [ 'country' => 'US', 'province' => 'NM', 'time_zone' => 'America/Denver' ], //	 New Mexico (Las Cruces, Alamogordo, Roswell; split from 505, eff 10/07/07)
						957 => [ 'country' => 'US', 'province' => 'NM', 'time_zone' => 'America/Denver' ], //	 New Mexico (pending; region unknown)
						702 => [ 'country' => 'US', 'province' => 'NV', 'time_zone' => 'America/Los_Angeles' ], //	 S. Nevada: Clark County, incl Las Vegas (see 775)
						725 => [ 'country' => 'US', 'province' => 'NV', 'time_zone' => 'America/Los_Angeles' ], //	 S. Nevada
						775 => [ 'country' => 'US', 'province' => 'NV', 'time_zone' => 'America/Los_Angeles' ], //	 N. Nevada: Reno (all of NV except Clark County area; see 702)
						212 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York City, New York (Manhattan; see 646, 718)
						332 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York City, New York
						315 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 N Cent. New York: Syracuse
						680 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 N Cent. New York
						347 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York (overlay for 718: NYC area, except Manhattan)
						929 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York
						516 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York: Nassau County, Long Island; Hempstead (see split 631)
						518 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 NE New York: Albany
						838 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 NE New York
						585 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 NW New York: Rochester (split from 716)
						607 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 S Cent. New York: Ithaca, Binghamton; Catskills
						631 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York: Suffolk County, Long Island; Huntington, Riverhead (split 516)
						934 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York
						646 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York (overlay 212/917) NYC: Manhattan only
						716 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 NW New York: Buffalo (see split 585)
						718 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York City, New York (Queens, Staten Island, The Bronx, and Brooklyn; see 212, 347)
						845 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York: Poughkeepsie; Nyack, Nanuet, Valley Cottage, New City, Putnam, Dutchess, Rockland, Orange, Ulster and parts of Sullivan counties in New York's lower Hudson Valley and Delaware County in the Catskills (see 914; perm 6/5/00)
						914 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 S New York: Westchester County (see 845)
						917 => [ 'country' => 'US', 'province' => 'NY', 'time_zone' => 'America/New_York' ], //	 New York: New York City (cellular, see 646)
						216 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 Cleveland (see splits 330, 440)
						234 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 NE Ohio: Canton, Akron (overlaid on 330; perm 10/30/00)
						283 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 SW Ohio: Cincinnati (cancelled: overlaid on 513)
						330 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 NE Ohio: Akron, Canton, Youngstown; Mahoning County, parts of Trumbull/Warren counties (see splits 216, 440, overlay 234)
						380 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 Ohio: Columbus (overlaid on 614; assigned but not in use)
						419 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 NW Ohio: Toledo (see overlay 567, perm 1/1/02)
						440 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 Ohio: Cleveland metro area, excluding Cleveland (split from 216, see also 330)
						513 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 SW Ohio: Cincinnati (see split 937; overlay 283 cancelled)
						567 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 NW Ohio: Toledo (overlaid on 419, perm 1/1/02)
						614 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 SE Ohio: Columbus (see overlay 380)
						740 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 SE Ohio (rural areas outside Columbus; split from 614)
						220 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 SE Ohio
						937 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 SW Ohio: Dayton (part of what used to be 513)
						326 => [ 'country' => 'US', 'province' => 'OH', 'time_zone' => 'America/New_York' ], //	 SW Ohio
						405 => [ 'country' => 'US', 'province' => 'OK', 'time_zone' => 'America/Chicago' ], //	 W Oklahoma: Oklahoma City (see 580)
						572 => [ 'country' => 'US', 'province' => 'OK', 'time_zone' => 'America/Chicago' ], //	 W Oklahoma
						580 => [ 'country' => 'US', 'province' => 'OK', 'time_zone' => 'America/Chicago' ], //	 W Oklahoma (rural areas outside Oklahoma City; split from 405)
						918 => [ 'country' => 'US', 'province' => 'OK', 'time_zone' => 'America/Chicago' ], //	 E Oklahoma: Tulsa
						539 => [ 'country' => 'US', 'province' => 'OK', 'time_zone' => 'America/Chicago' ], //	 E Oklahoma:
						503 => [ 'country' => 'US', 'province' => 'OR', 'time_zone' => 'America/Los_Angeles' ], //	 Oregon (see 541, 971)
						541 => [ 'country' => 'US', 'province' => 'OR', 'time_zone' => null ], ///-7	 Oregon: Eugene, Medford (split from 503; 503 retains NW part [Portland/Salem], all else moves to 541; eastern oregon is UTC-7) (**NON-SPECIFIC: MST, PST)
						971 => [ 'country' => 'US', 'province' => 'OR', 'time_zone' => 'America/Los_Angeles' ], //	 Oregon: Metropolitan Portland, Salem/Keizer area, incl Cricket Wireless (see 503; perm 10/1/00)
						458 => [ 'country' => 'US', 'province' => 'OR', 'time_zone' => null ], //	 Oregon: (**NON-SPECIFIC: MST, PST)
						215 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 SE Pennsylvania: Philadelphia (see overlays 267)
						267 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 SE Pennsylvania: Philadelphia (see 215)
						445 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 SE Pennsylvania
						412 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 W Pennsylvania: Pittsburgh (see split 724, overlay 878)
						484 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 SE Pennsylvania: Allentown, Bethlehem, Reading, West Chester, Norristown (see 610)
						570 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 NE and N Central Pennsylvania: Wilkes-Barre, Scranton (see 717)
						223 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 NE and N Central Pennsylvania
						272 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 NE and N Central Pennsylvania
						610 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 SE Pennsylvania: Allentown, Bethlehem, Reading, West Chester, Norristown (see overlays 484, 835)
						717 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 E Pennsylvania: Harrisburg (see split 570)
						724 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 SW Pennsylvania (areas outside metro Pittsburgh; split from 412)
						814 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 Cent. Pennsylvania: Erie
						582 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 Cent. Pennsylvania
						835 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 SE Pennsylvania: Allentown, Bethlehem, Reading, West Chester, Norristown (overlaid on 610, eff 5/1/01; see also 484)
						878 => [ 'country' => 'US', 'province' => 'PA', 'time_zone' => 'America/New_York' ], //	 Pittsburgh, New Castle (overlaid on 412, perm 8/17/01, mand t.b.a.)
						787 => [ 'country' => 'US', 'province' => 'PR', 'time_zone' => 'America/Puerto_Rico' ], //*	 Puerto Rico (see overlay 939, perm 8/1/01)
						939 => [ 'country' => 'US', 'province' => 'PR', 'time_zone' => 'America/Puerto_Rico' ], //*	 Puerto Rico (overlaid on 787, perm 8/1/01)
						401 => [ 'country' => 'US', 'province' => 'RI', 'time_zone' => 'America/New_York' ], //	 Rhode Island
						803 => [ 'country' => 'US', 'province' => 'SC', 'time_zone' => 'America/New_York' ], //	 South Carolina: Columbia, Aiken, Sumter (see 843, 864)
						839 => [ 'country' => 'US', 'province' => 'SC', 'time_zone' => 'America/New_York' ], //	 South Carolina
						843 => [ 'country' => 'US', 'province' => 'SC', 'time_zone' => 'America/New_York' ], //	 South Carolina, coastal area: Charleston, Beaufort, Myrtle Beach (split from 803)
						854 => [ 'country' => 'US', 'province' => 'SC', 'time_zone' => 'America/New_York' ], //	 South Carolina
						864 => [ 'country' => 'US', 'province' => 'SC', 'time_zone' => 'America/New_York' ], //	 South Carolina, upstate area: Greenville, Spartanburg (split from 803)
						605 => [ 'country' => 'US', 'province' => 'SD', 'time_zone' => null ], ///-7	 South Dakota (**NON-SPECIFIC: CST, MST)
						423 => [ 'country' => 'US', 'province' => 'TN', 'time_zone' => null ], //	 E Tennessee, except Knoxville metro area: Chattanooga, Bristol, Johnson City, Kingsport, Greeneville (see split 865; part of what used to be 615) (**NON-SPECIFIC: EST, CST)
						615 => [ 'country' => 'US', 'province' => 'TN', 'time_zone' => 'America/Chicago' ], //	 Northern Middle Tennessee: Nashville metro area (see 423, 931)
						629 => [ 'country' => 'US', 'province' => 'TN', 'time_zone' => 'America/Chicago' ], //	 Northern Middle Tennessee
						731 => [ 'country' => 'US', 'province' => 'TN', 'time_zone' => 'America/Chicago' ], //	 W Tennessee: outside Memphis metro area (split from 901, perm 2/12/01, mand 9/17/01)
						865 => [ 'country' => 'US', 'province' => 'TN', 'time_zone' => 'America/New_York' ], //	 E Tennessee: Knoxville, Knox and adjacent counties (split from 423; part of what used to be 615)
						901 => [ 'country' => 'US', 'province' => 'TN', 'time_zone' => 'America/Chicago' ], //	 W Tennessee: Memphis metro area (see 615, 931, split 731)
						931 => [ 'country' => 'US', 'province' => 'TN', 'time_zone' => null ], //	 Middle Tennessee: semi-circular ring around Nashville (split from 615) (**NON-SPECIFIC: EST, CST)
						210 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 S Texas: San Antonio (see also splits 830, 956)
						726 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 S Texas
						214 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: Dallas Metro (overlays 469/972)
						945 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas
						254 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Central Texas (Waco, Stephenville; split, see 817, 940)
						281 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: Houston Metro (split 713; overlay 832)
						325 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Central Texas: Abilene, Sweetwater, Snyder, San Angelo (split from 915)
						361 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 S Texas: Corpus Christi (split from 512; eff 2/13/99)
						409 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 SE Texas: Galveston, Port Arthur, Beaumont (splits 936, 979)
						430 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 NE Texas: Tyler (overlaid on 903, eff 7/20/02)
						432 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Denver' ], ///-6	 W Texas: Big Spring, Midland, Odessa (split from 915, eff 4/5/03)
						469 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: Dallas Metro (overlays 214/972)
						512 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 S Texas: Austin (see split 361; overlay 737, perm 11/10/01)
						682 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: Fort Worth areas (perm 10/7/00, mand 12/9/00)
						713 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Mid SE Texas: central Houston (split, 281; overlay 832)
						737 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 S Texas: Austin (overlaid on 512, suspended; see also 361)
						806 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Panhandle Texas: Amarillo, Lubbock
						817 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 N Cent. Texas: Fort Worth area (see 254, 940)
						830 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: region surrounding San Antonio (split from 210)
						832 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: Houston (overlay 713/281)
						346 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: Houston
						903 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 NE Texas: Tyler (see overlay 430, eff 7/20/02)
						915 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => null ], ///-6	 W Texas: El Paso (see splits 325 eff 4/5/03; 432, eff 4/5/03) (**NON-SPECIFIC: CST, MST)
						936 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 SE Texas: Conroe, Lufkin, Nacogdoches, Crockett (split from 409, see also 979)
						940 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 N Cent. Texas: Denton, Wichita Falls (split from 254, 817)
						956 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: Valley of Texas area; Harlingen, Laredo (split from 210)
						972 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 Texas: Dallas Metro (overlays 214/469)
						979 => [ 'country' => 'US', 'province' => 'TX', 'time_zone' => 'America/Chicago' ], //	 SE Texas: Bryan, College Station, Bay City (split from 409, see also 936)
						385 => [ 'country' => 'US', 'province' => 'UT', 'time_zone' => 'America/Denver' ], //	 Utah: Salt Lake City Metro (split from 801, eff 3/30/02 POSTPONED; see also 435)
						435 => [ 'country' => 'US', 'province' => 'UT', 'time_zone' => 'America/Denver' ], //	 Rural Utah outside Salt Lake City metro (see split 801)
						801 => [ 'country' => 'US', 'province' => 'UT', 'time_zone' => 'America/Denver' ], //	 Utah: Salt Lake City Metro (see split 385, eff 3/30/02; see also split 435)
						276 => [ 'country' => 'US', 'province' => 'VA', 'time_zone' => 'America/New_York' ], //	 S and SW Virginia: Bristol, Stuart, Martinsville (split from 540; perm 9/1/01, mand 3/16/02)
						434 => [ 'country' => 'US', 'province' => 'VA', 'time_zone' => 'America/New_York' ], //	 E Virginia: Charlottesville, Lynchburg, Danville, South Boston, and Emporia (split from 804, eff 6/1/01; see also 757)
						540 => [ 'country' => 'US', 'province' => 'VA', 'time_zone' => 'America/New_York' ], //	 Western and Southwest Virginia: Shenandoah and Roanoke valleys: Fredericksburg, Harrisonburg, Roanoke, Salem, Lexington and nearby areas (see split 276; split from 703)
						571 => [ 'country' => 'US', 'province' => 'VA', 'time_zone' => 'America/New_York' ], //	 Northern Virginia: Arlington, McLean, Tysons Corner (to be overlaid on 703 3/1/00; see earlier split 540)
						703 => [ 'country' => 'US', 'province' => 'VA', 'time_zone' => 'America/New_York' ], //	 Northern Virginia: Arlington, McLean, Tysons Corner (see split 540; overlay 571)
						757 => [ 'country' => 'US', 'province' => 'VA', 'time_zone' => 'America/New_York' ], //	 E Virginia: Tidewater / Hampton Roads area -- Norfolk, Virginia Beach, Chesapeake, Portsmouth, Hampton, Newport News, Suffolk (part of what used to be 804)
						804 => [ 'country' => 'US', 'province' => 'VA', 'time_zone' => 'America/New_York' ], //	 E Virginia: Richmond (see splits 757, 434)
						340 => [ 'country' => 'US', 'province' => 'VI', 'time_zone' => 'America/St_Thomas' ], //*	 US Virgin Islands (see also 809)
						802 => [ 'country' => 'US', 'province' => 'VT', 'time_zone' => 'America/New_York' ], //	 Vermont
						206 => [ 'country' => 'US', 'province' => 'WA', 'time_zone' => 'America/Los_Angeles' ], //	 W Washington state: Seattle and Bainbridge Island (see splits 253, 360, 425; overlay 564)
						253 => [ 'country' => 'US', 'province' => 'WA', 'time_zone' => 'America/Los_Angeles' ], //	 Washington: South Tier - Tacoma, Federal Way (split from 206, see also 425; overlay 564)
						360 => [ 'country' => 'US', 'province' => 'WA', 'time_zone' => 'America/Los_Angeles' ], //	 W Washington State: Olympia, Bellingham (area circling 206, 253, and 425; split from 206; see overlay 564)
						425 => [ 'country' => 'US', 'province' => 'WA', 'time_zone' => 'America/Los_Angeles' ], //	 Washington: North Tier - Everett, Bellevue (split from 206, see also 253; overlay 564)
						509 => [ 'country' => 'US', 'province' => 'WA', 'time_zone' => 'America/Los_Angeles' ], //	 E and Central Washington state: Spokane, Yakima, Walla Walla, Ellensburg
						564 => [ 'country' => 'US', 'province' => 'WA', 'time_zone' => 'America/Los_Angeles' ], //	 W Washington State: Olympia, Bellingham (overlaid on 360; see also 206, 253, 425; assigned but not in use)
						262 => [ 'country' => 'US', 'province' => 'WI', 'time_zone' => 'America/Chicago' ], //	 SE Wisconsin: counties of Kenosha, Ozaukee, Racine, Walworth, Washington, Waukesha (split from 414)
						414 => [ 'country' => 'US', 'province' => 'WI', 'time_zone' => 'America/Chicago' ], //	 SE Wisconsin: Milwaukee County (see splits 920, 262)
						608 => [ 'country' => 'US', 'province' => 'WI', 'time_zone' => 'America/Chicago' ], //	 SW Wisconsin: Madison
						715 => [ 'country' => 'US', 'province' => 'WI', 'time_zone' => 'America/Chicago' ], //	 N Wisconsin: Eau Claire, Wausau, Superior
						534 => [ 'country' => 'US', 'province' => 'WI', 'time_zone' => 'America/Chicago' ], //	 Wisconsin
						920 => [ 'country' => 'US', 'province' => 'WI', 'time_zone' => 'America/Chicago' ], //	 NE Wisconsin: Appleton, Green Bay, Sheboygan, Fond du Lac (from Beaver Dam NE to Oshkosh, Appleton, and Door County; part of what used to be 414)
						304 => [ 'country' => 'US', 'province' => 'WV', 'time_zone' => 'America/New_York' ], //	 West Virginia
						681 => [ 'country' => 'US', 'province' => 'WV', 'time_zone' => 'America/New_York' ], //	 West Virginia
						307 => [ 'country' => 'US', 'province' => 'WY', 'time_zone' => 'America/Denver' ], //	 Wyoming
				];
				break;
			case 'timesheet_view':
				$retval = [
						10 => TTi18n::gettext( 'Calendar' ),
						20 => TTi18n::gettext( 'List' ),
				];
				break;

			case 'start_week_day':
				$retval = [
						0 => TTi18n::gettext( 'Sunday' ),
						1 => TTi18n::gettext( 'Monday' ),
						2 => TTi18n::gettext( 'Tuesday' ),
						3 => TTi18n::gettext( 'Wednesday' ),
						4 => TTi18n::gettext( 'Thursday' ),
						5 => TTi18n::gettext( 'Friday' ),
						6 => TTi18n::gettext( 'Saturday' ),
				];
				break;
			case 'schedule_icalendar_type':
				$retval = [
						0 => TTi18n::gettext( 'Disabled' ),
						1 => TTi18n::gettext( 'Enabled (Authenticated)' ),
						2 => TTi18n::gettext( 'Enabled (UnAuthenticated)' ),
				];
				break;
			case 'notification_status':
				$retval = [
						//Kill switch to enable or disable sending notifications entirely.
						0 => TTi18n::gettext( 'Disabled' ),
						1 => TTi18n::gettext( 'Enabled' ),
				];
				break;
			case 'default_login_screen':
				$retval = [
						'Home'      => TTi18n::gettext( 'Dashboard' ),
						'TimeSheet' => TTi18n::gettext( 'TimeSheet' ),
						'Schedule'  => TTi18n::gettext( 'Schedule' ),
				];

				global $current_user;
				if ( isset( $current_user ) && is_object( $current_user ) ) {
					$permission = new Permission();
					if ( $permission->Check( 'report', 'enabled', $current_user->getId(), $current_user->getCompany() ) ) {
						$retval['SavedReport'] = TTi18n::gettext( 'Saved Reports' );
					}
				}

				break;
			case 'language':
				$retval = TTi18n::getLanguageArray();

				//Because the array keys are strings, flex needs a sort prefix to maintain the order.
				if ( defined( 'TIMETREX_API' ) == true && TIMETREX_API == true ) {
					$retval = Misc::addSortPrefix( $retval );
				}
				break;
			case 'columns':
				$retval = [
						'-1000-first_name'         => TTi18n::gettext( 'First Name' ),
						'-1002-last_name'          => TTi18n::gettext( 'Last Name' ),
						'-1005-user_status'        => TTi18n::gettext( 'Employee Status' ),
						'-1010-title'              => TTi18n::gettext( 'Title' ),
						'-1020-user_group'         => TTi18n::gettext( 'Group' ),
						'-1030-default_branch'     => TTi18n::gettext( 'Default Branch' ),
						'-1040-default_department' => TTi18n::gettext( 'Default Department' ),

						'-1150-city'     => TTi18n::gettext( 'City' ),
						'-1160-province' => TTi18n::gettext( 'Province/State' ),
						'-1170-country'  => TTi18n::gettext( 'Country' ),

						'-1120-language_display'         => TTi18n::gettext( 'Language' ),
						'-1130-date_format_display'      => TTi18n::gettext( 'Date Format' ),
						'-1140-time_format_display'      => TTi18n::gettext( 'Time Format' ),
						'-1150-time_zone_display'        => TTi18n::gettext( 'Time Zone' ),
						'-1160-time_unit_format_display' => TTi18n::gettext( 'Time Unit Format' ),
						'-1170-distance_format_display'  => TTi18n::gettext( 'Distance Units' ),
						'-1180-items_per_page'           => TTi18n::gettext( 'Items Per Page' ),
						//'-1180-timesheet_view_display' => TTi18n::gettext('TimeSheet View'),
						'-1190-start_week_day_display'   => TTi18n::gettext( 'Start Weekday' ),
						//'-1100-enable_email_notification_exception' => TTi18n::gettext('Email Notification Exception'),
						//'-1110-enable_email_notification_message' => TTi18n::gettext('Email Notification Message'),
						//'-1110-enable_email_notification_pay_stub' => TTi18n::gettext('Email Notification Pay Stub'),
						//'-1120-enable_email_notification_home' => TTi18n::gettext('Email Notification Home'),

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
						'date_format_display',
						'time_format_display',
						'time_unit_format_display',
						'distance_format_display',
						'time_zone_display',
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
				'id'      => 'ID',
				'user_id' => 'User',

				'first_name'            => false,
				'last_name'             => false,
				'user_name'             => false,
				'user_status_id'        => false,
				'user_status'           => false,
				'group_id'              => false,
				'user_group'            => false,
				'title_id'              => false,
				'title'                 => false,
				'default_branch_id'     => false,
				'default_branch'        => false,
				'default_department_id' => false,
				'default_department'    => false,

				'city'     => false,
				'province' => false,
				'country'  => false,

				'language'                 => 'Language',
				'date_format'              => 'DateFormat',
				'time_format'              => 'TimeFormat',
				'time_zone'                => 'TimeZone',
				'time_unit_format'         => 'TimeUnitFormat',
				'distance_format'          => 'DistanceFormat',

				//Ignore when setting.
				'language_display'         => false,
				'date_format_display'      => false,
				'time_format_display'      => false,
				'time_zone_display'        => false,
				'time_unit_format_display' => false,
				'distance_format_display'  => false,

				'items_per_page'                     => 'ItemsPerPage',
				//'timesheet_view' => 'TimeSheetView',
				'start_week_day'                     => 'StartWeekDay',
				'start_week_day_display'             => false,
				'shortcut_key_sequence'              => 'ShortcutKeySequence',
				'enable_always_blank_timesheet_rows' => 'EnableAlwaysBlankTimeSheetRows',
				//'enable_auto_context_menu'           => 'EnableAutoContextMenu',
				'enable_report_open_new_window'      => 'EnableReportOpenNewWindow',

				//'enable_email_notification_exception' => 'EnableEmailNotificationException',
				//'enable_email_notification_message'   => 'EnableEmailNotificationMessage',
				//'enable_email_notification_pay_stub'  => 'EnableEmailNotificationPayStub',
				//'enable_email_notification_home'      => 'EnableEmailNotificationHome',

				//'schedule_icalendar_url' => 'ScheduleIcalendarURL',
				'schedule_icalendar_type_id'          => 'ScheduleIcalendarType',
				//'schedule_icalendar_event_name' => 'ScheduleIcalendarEventName',
				'schedule_icalendar_alarm1_working'   => 'ScheduleIcalendarAlarm1Working',
				'schedule_icalendar_alarm2_working'   => 'ScheduleIcalendarAlarm2Working',
				'schedule_icalendar_alarm1_absence'   => 'ScheduleIcalendarAlarm1Absence',
				'schedule_icalendar_alarm2_absence'   => 'ScheduleIcalendarAlarm2Absence',
				'schedule_icalendar_alarm1_modified'  => 'ScheduleIcalendarAlarm1Modified',
				'schedule_icalendar_alarm2_modified'  => 'ScheduleIcalendarAlarm2Modified',
				'enable_save_timesheet_state'         => 'EnableSaveTimesheetState',
				'default_login_screen'                => 'DefaultLoginScreen',

				'notification_duration'				  => 'NotificationDuration',
				'notification_status_id' 		      => 'NotificationStatus',
				'browser_permission_ask_date'         => 'BrowserPermissionAskDate',

				'deleted'                             => 'Deleted',
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
	function getLanguage() {
		return $this->getGenericDataValue( 'language' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLanguage( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'language', $value );
	}

	/**
	 * @return bool
	 */
	function getDateFormatExample() {
		return Option::getByKey( $this->getDateFormat(), Misc::trimSortPrefix( $this->getOptions( 'date_format_example' ) ) );
	}

	/**
	 * @return bool|string
	 */
	function getJSDateFormat() {
		$js_date_format = Option::getByKey( $this->getDateFormat(), $this->getOptions( 'js_date_format' ) );
		if ( $js_date_format != '' ) {
			Debug::text( 'Javascript Date Format: ' . $js_date_format, __FILE__, __LINE__, __METHOD__, 10 );

			return $js_date_format;
		}

		return '%d-%M-%y';
	}

	/**
	 * @return bool|mixed
	 */
	function getDateFormat() {
		return $this->getGenericDataValue( 'date_format' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateFormat( $value ) {
		$value = trim( $value );
		Debug::text( 'Date Format: ' . $value . ' Type: ' . gettype( $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'date_format', $value );
	}

	/**
	 * @return array|mixed
	 */
	function getTimeFormatExample() {
		return Misc::trimSortPrefix( Option::getByKey( $this->getTimeFormat(), $this->getOptions( 'time_format_example' ) ) );
	}

	/**
	 * @return bool|string
	 */
	function getJSTimeFormat() {
		$js_time_format = Option::getByKey( $this->getTimeFormat(), $this->getOptions( 'js_time_format' ) );
		if ( $js_time_format != '' ) {
			Debug::text( 'Javascript Time Format: ' . $js_time_format, __FILE__, __LINE__, __METHOD__, 10 );

			return $js_time_format;
		}

		return '%l:%M %p';
	}

	/**
	 * @return bool|mixed
	 */
	function getTimeFormat() {
		return $this->getGenericDataValue( 'time_format' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeFormat( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'time_format', $value );
	}

	/**
	 * @param $country
	 * @param $province
	 * @param bool $work_phone
	 * @param bool $home_phone
	 * @param bool $default
	 * @return array|bool|mixed|string
	 */
	function getLocationTimeZone( $country, $province, $work_phone = false, $home_phone = false, $default = false, $enable_auto_detect = true ) {
		$country = strtoupper( $country );
		$province = strtoupper( $province );

		Debug::text( 'Country: ' . $country . ' Province: ' . $province . ' Work Phone: ' . $work_phone . ' Home Phone: ' . $home_phone . ' Default: ' . $default .' Enable AutoDetect: '. (int)$enable_auto_detect, __FILE__, __LINE__, __METHOD__, 9 );
		if ( $enable_auto_detect == false && $default != '' ) {
			return $default;
		}

		$location_timezones = $this->getOptions( 'location_timezone' );
		$area_code_timezone = $this->getOptions( 'area_code_timezone' );

		//Work phone can be the most accurate.
		if ( $work_phone != '' ) {
			$work_area_code = $this->Validator->getPhoneNumberAreaCode( $work_phone );
			//Make sure the area code matches the province, so if a BC province is specified with a ON area code, we use the province instead of area code.
			if ( $work_area_code !== false
					&& isset( $area_code_timezone[$work_area_code] )
					&& $area_code_timezone[$work_area_code]['time_zone'] != null
					&& $area_code_timezone[$work_area_code]['province'] == $province ) {
				Debug::text( 'Using Work Phone for timezone detection...', __FILE__, __LINE__, __METHOD__, 9 );

				return $area_code_timezone[$work_area_code]['time_zone'];
			}
		}

		//Home phone is the next most accurate
		if ( $home_phone != '' ) {
			$home_area_code = $this->Validator->getPhoneNumberAreaCode( $home_phone );
			//Make sure the area code matches the province, so if a BC province is specified with a ON area code, we use the province instead of area code.
			if ( $home_area_code !== false
					&& isset( $area_code_timezone[$home_area_code] )
					&& $area_code_timezone[$home_area_code]['time_zone'] != null
					&& $area_code_timezone[$home_area_code]['province'] == $province ) {
				Debug::text( 'Using Home Phone for timezone detection...', __FILE__, __LINE__, __METHOD__, 9 );

				return $area_code_timezone[$home_area_code]['time_zone'];
			}
		}

		//Country/province is the last option.
		if ( $country != '' && isset( $location_timezones[$country] ) ) {
			if ( $province != '' && is_array( $location_timezones[$country] ) && isset( $location_timezones[$country][$province] ) && $location_timezones[$country][$province] != null ) {
				Debug::text( 'Using Country/Province for timezone detection...', __FILE__, __LINE__, __METHOD__, 9 );
				if ( is_array( $location_timezones[$country][$province] ) && $default == '' ) { //No default specified, so lets guess and use the first item from the array, assuming its the most commonly used.
					return Misc::trimSortPrefix( $location_timezones[$country][$province][0] );
				} else if ( !is_array( $location_timezones[$country][$province] ) ) {
					return Misc::trimSortPrefix( $location_timezones[$country][$province] );
				}
			} else if ( isset( $location_timezones[$country] ) && !is_array( $location_timezones[$country] ) && $location_timezones[$country] != null ) {
				Debug::text( 'Using Country for timezone detection...', __FILE__, __LINE__, __METHOD__, 9 );
				if ( is_array( $location_timezones[$country] ) && $default == '' ) { //No default specified, so lets guess and use the first item from the array, assuming its the most commonly used.
					return Misc::trimSortPrefix( $location_timezones[$country][0] );
				} else if ( !is_array( $location_timezones[$country] ) ) {
					return Misc::trimSortPrefix( $location_timezones[$country] );
				}
			}
		}

		if ( $default != '' ) {
			Debug::text( 'Using Default for timezone detection...', __FILE__, __LINE__, __METHOD__, 9 );

			return $default;
		}

		Debug::text( 'Using GMT timezone detection...', __FILE__, __LINE__, __METHOD__, 9 );

		return 'GMT';
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
	 * @return mixed
	 */
	function getTimeUnitFormatExample() {
		$options = $this->getOptions( 'time_unit_format' );

		return $options[$this->getTimeUnitFormat()];
	}

	/**
	 * @return bool|mixed
	 */
	function getTimeUnitFormat() {
		return $this->getGenericDataValue( 'time_unit_format' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeUnitFormat( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'time_unit_format', $value );
	}

	/**
	 * @param $meters
	 * @param null $format
	 * @return bool|float|int
	 */
	function convertMetersToDistance( $meters, $format = null ) {
		if ( $format == '' ) {
			$format = self::getDistanceFormat();
		}

		switch ( $format ) {
			case 20: //Miles
				$dst_unit = 'mi';
				break;
			case 30: //Meters
				$dst_unit = 'm';
				break;
			case 10: //KM
			default:
				$dst_unit = 'km';
				break;
		}

		return UnitConvert::convert( 'm', $dst_unit, $meters );
	}

	/**
	 * @return bool|mixed
	 */
	function getDistanceFormat() {
		return $this->getGenericDataValue( 'distance_format' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDistanceFormat( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'distance_format', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getItemsPerPage() {
		return $this->getGenericDataValue( 'items_per_page' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setItemsPerPage( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'items_per_page', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNotificationDuration() {
		return $this->getGenericDataValue( 'notification_duration' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNotificationDuration( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'notification_duration', $value );
	}

	/**
	 * A quick function to change just the timezone, without having to change
	 * date formats and such in the process.
	 * @return bool
	 */
	function setTimeZonePreferences() {
		return TTDate::setTimeZone( $this->getTimeZone() );
	}

	/**
	 * @return bool
	 */
	function setDateTimePreferences() {
		//TTDate::setTimeZone( $this->getTimeZone() );
		if ( $this->setTimeZonePreferences() == false ) {
			//In case setting the time zone failed
			return false;
		}

		TTDate::setDateFormat( $this->getDateFormat() );
		TTDate::setTimeFormat( $this->getTimeFormat() );
		TTDate::setTimeUnitFormat( $this->getTimeUnitFormat() );

		return true;
	}

	/**
	 * @return bool|mixed
	 */
	function getTimeSheetView() {
		return $this->getGenericDataValue( 'timesheet_view' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTimeSheetView( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'timesheet_view', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getStartWeekDay() {
		return $this->getGenericDataValue( 'start_week_day' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStartWeekDay( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'start_week_day', $value );
	}

	/**
	 * Used in Flex interface only, currently its hardcoded for now at least. Default: CTRL+ALT
	 * @return bool|mixed
	 */
	function getShortcutKeySequence() {
		return $this->getGenericDataValue( 'shortcut_key_sequence' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setShortcutKeySequence( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'shortcut_key_sequence', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableAlwaysBlankTimeSheetRows() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_always_blank_timesheet_rows' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableAlwaysBlankTimeSheetRows( $value ) {
		return $this->setGenericDataValue( 'enable_always_blank_timesheet_rows', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableAutoContextMenu() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_auto_context_menu' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableAutoContextMenu( $value ) {
		return $this->setGenericDataValue( 'enable_auto_context_menu', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableReportOpenNewWindow() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_report_open_new_window' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableReportOpenNewWindow( $value ) {
		return $this->setGenericDataValue( 'enable_report_open_new_window', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotificationException() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_email_notification_exception' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableEmailNotificationException( $value ) {
		return $this->setGenericDataValue( 'enable_email_notification_exception', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotificationMessage() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_email_notification_message' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableEmailNotificationMessage( $value ) {
		return $this->setGenericDataValue( 'enable_email_notification_message', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotificationPayStub() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_email_notification_pay_stub' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableEmailNotificationPayStub( $value ) {
		return $this->setGenericDataValue( 'enable_email_notification_pay_stub', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getEnableEmailNotificationHome() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_email_notification_home' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableEmailNotificationHome( $value ) {
		return $this->setGenericDataValue( 'enable_email_notification_home', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getScheduleIcalendarType() {
		return $this->getGenericDataValue( 'schedule_icalendar_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleIcalendarType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'schedule_icalendar_type_id', $value );
	}

	/**
	 * Helper functions for dealing with unauthenticated calendar access, required by Google Calendar for now.
	 * @param null $user_name
	 * @param int $type_id ID
	 * @param null $selected_schedule
	 * @return string
	 */
	function getScheduleIcalendarURL( $user_name = null, $type_id = null, $selected_schedule = null ) {
		if ( $user_name == '' ) {
			$user_name = $this->getUserObject()->getUserName();
		}

		if ( $type_id == '' ) {
			$type_id = $this->getScheduleIcalendarType();
		}

		$url_fragments = [];

		if ( $type_id == 2 ) {
			$url_fragments[] = 'u=' . $user_name;
			$url_fragments[] = 'k=' . $this->getScheduleIcalendarKey();
		}

		if ( $selected_schedule != '' ) {
			$url_fragments[] = 's=' . $selected_schedule;
		}

		$retval = Environment::getBaseURL() . 'ical/ical.php';

		if ( count( $url_fragments ) > 0 ) {
			$retval .= '?' . implode( '&', $url_fragments );
		}

		return $retval;
	}

	/**
	 * @param $key
	 * @return bool
	 */
	function checkScheduleICalendarKey( $key ) {
		Debug::text( 'Checking Key: ' . $key . ' Should Match: ' . $this->getScheduleIcalendarKey(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( trim( $key ) == $this->getScheduleIcalendarKey( TTPassword::getPasswordVersion( $key ) ) ) {
			return true;
		}

		return false;
	}

	/**
	 * @param null $version
	 * @return bool|string
	 */
	function getScheduleIcalendarKey( $version = null ) {
		if ( (int)$version == 1 ) {
			$salt = TTPassword::getPasswordSalt();
			$user_id = TTUUID::convertUUIDToInt( $this->getUserObject()->getID() );

			$retval = substr( md5( $this->getScheduleIcalendarEventName() . $salt . $user_id ), 0, 12 );
		} else { //Should be v3.
			$user_name = TTUUID::castUUID( $this->getUserObject()->getUserName() );
			$user_id = TTUUID::castUUID( $this->getUserObject()->getID() );

			//Use the TTPassword class to better handle different versions of the hashed data.
			$retval = strtoupper( substr( TTPassword::encryptPassword( $user_name, $user_id, $this->getScheduleIcalendarEventName(), $version ), 0, 12 ) );
		}

		Debug::text( 'Key: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
	}

	/**
	 * Currently used as part of the unauthenticated key, so if this changes the key to access the calendar changes too.
	 * @return bool
	 */
	function getScheduleIcalendarEventName() {
		return $this->fromBool( $this->getGenericDataValue( 'schedule_icalendar_event_name' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleIcalendarEventName( $value ) {
		return $this->setGenericDataValue( 'schedule_icalendar_event_name', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getScheduleIcalendarAlarm1Working() {
		return (int)$this->getGenericDataValue( 'schedule_icalendar_alarm1_working' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleIcalendarAlarm1Working( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'schedule_icalendar_alarm1_working', $value );
	}

	/**
	 * @return bool|int
	 */
	function getScheduleIcalendarAlarm2Working() {
		return (int)$this->getGenericDataValue( 'schedule_icalendar_alarm2_working' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleIcalendarAlarm2Working( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'schedule_icalendar_alarm2_working', $value );
	}

	/**
	 * @return bool|int
	 */
	function getScheduleIcalendarAlarm1Absence() {
		return (int)$this->getGenericDataValue( 'schedule_icalendar_alarm1_absence' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleIcalendarAlarm1Absence( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'schedule_icalendar_alarm1_absence', $value );
	}

	/**
	 * @return bool|int
	 */
	function getScheduleIcalendarAlarm2Absence() {
		return (int)$this->getGenericDataValue( 'schedule_icalendar_alarm2_absence' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleIcalendarAlarm2Absence( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'schedule_icalendar_alarm2_absence', $value );
	}

	/**
	 * @return bool|int
	 */
	function getScheduleIcalendarAlarm1Modified() {
		return (int)$this->getGenericDataValue( 'schedule_icalendar_alarm1_modified' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleIcalendarAlarm1Modified( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'schedule_icalendar_alarm1_modified', $value );
	}

	/**
	 * @return bool|int
	 */
	function getScheduleIcalendarAlarm2Modified() {
		return (int)$this->getGenericDataValue( 'schedule_icalendar_alarm2_modified' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setScheduleIcalendarAlarm2Modified( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'schedule_icalendar_alarm2_modified', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableSaveTimesheetState() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_save_timesheet_state' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableSaveTimesheetState( $value ) {
		return $this->setGenericDataValue( 'enable_save_timesheet_state', $this->toBool( $value ) );
	}

	/**
	 * @return int
	 */
	function getNotificationStatus() {
		return $this->getGenericDataValue( 'notification_status_id' ); //Don't cast to INT as we need to check for false or 0.
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNotificationStatus( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue('notification_status_id', $value );
	}

	/**
	 * @return int
	 */
	function getBrowserPermissionAskDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'browser_permission_ask_date' );
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
	function setBrowserPermissionAskDate( $epoch ) {
		$value = (int)$epoch;

		return $this->setGenericDataValue('browser_permission_ask_date', $value );
	}

	/**
	 * Default: Home/Dashboard
	 * @return bool|mixed
	 */
	function getDefaultLoginScreen() {
		return $this->getGenericDataValue( 'default_login_screen' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDefaultLoginScreen( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'default_login_screen', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// User
		if ( $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
		}
		// Language
		$language_options = TTi18n::getLanguageArray();
		$this->Validator->inArrayKey( 'language',
									  $this->getLanguage(),
									  TTi18n::gettext( 'Incorrect language' ),
									  $language_options
		);
		// Date format
		if ( $this->getDateFormat() != '' ) {
			$this->Validator->inArrayKey( 'date_format',
										  $this->getDateFormat(),
										  TTi18n::gettext( 'Incorrect date format' ),
										  Misc::trimSortPrefix( $this->getOptions( 'date_format' ) )
			);
		}
		// Time format
		$this->Validator->inArrayKey( 'time_format',
									  $this->getTimeFormat(),
									  TTi18n::gettext( 'Incorrect time format' ),
									  $this->getOptions( 'time_format' )
		);
		// Time zone
		$this->Validator->inArrayKey( 'time_zone',
									  $this->getTimeZone(),
									  TTi18n::gettext( 'Incorrect time zone' ),
									  Misc::trimSortPrefix( $this->getOptions( 'time_zone' ) )
		);
		//Make sure timezone is valid on this system before allowing a user to set it.
		$this->Validator->isTRUE( 'time_zone',
								  TTDate::getTimeZoneObject($this->getTimeZone(), false ),
								  TTi18n::gettext( 'Invalid time zone on this system, please try another one' ) );

		// Time units
		$this->Validator->inArrayKey( 'time_unit_format',
									  $this->getTimeUnitFormat(),
									  TTi18n::gettext( 'Incorrect time units' ),
									  $this->getOptions( 'time_unit_format' )
		);
		// Distance units
		$this->Validator->inArrayKey( 'distance_format',
									  $this->getDistanceFormat(),
									  TTi18n::gettext( 'Incorrect distance units' ),
									  $this->getOptions( 'distance_format' )
		);
		// Items per page
		$items_per_page_min = ( PRODUCTION == false ) ? 1 : 5; //Allow lower numbers to help with testing.
		$items_per_page_max = ( PRODUCTION == false ) ? 10000 : 2000; //Allow higher numbers to help with testing.
		if ( $this->getItemsPerPage() == '' || $this->getItemsPerPage() < $items_per_page_min || $this->getItemsPerPage() > $items_per_page_max ) {
			$this->Validator->isTrue( 'items_per_page',
									  false,
									  TTi18n::gettext( 'Rows per page must be between %1 and %2', [ $items_per_page_min, $items_per_page_max ] )
			);
		}

		// Default TimeSheet view'
		if ( $this->getTimeSheetView() !== false ) {
			$this->Validator->inArrayKey( 'timesheet_view',
										  $this->getTimeSheetView(),
										  TTi18n::gettext( 'Incorrect default TimeSheet view' ),
										  $this->getOptions( 'timesheet_view' )
			);
		}
		// Day to start a week on
		$this->Validator->inArrayKey( 'start_week_day',
									  $this->getStartWeekDay(),
									  TTi18n::gettext( 'Incorrect day to start a week on' ),
									  $this->getOptions( 'start_week_day' )
		);
		// Shortcut key sequence
		if ( $this->getShortcutKeySequence() != '' ) {
			$this->Validator->isLength( 'shortcut_key_sequence',
										$this->getShortcutKeySequence(),
										TTi18n::gettext( 'Shortcut key sequence is too short or too long' ),
										0,
										250
			);
		}
		// Option to enable calendar synchronization
		$this->Validator->inArrayKey( 'schedule_icalendar_type_id',
									  $this->getScheduleIcalendarType(),
									  TTi18n::gettext( 'Incorrect option to enable calendar synchronization' ),
									  $this->getOptions( 'schedule_icalendar_type' )
		);
		// Time for alarm #1
		$this->Validator->isNumeric( 'schedule_icalendar_alarm1_working',
									 $this->getScheduleIcalendarAlarm1Working(),
									 TTi18n::gettext( 'Invalid time for alarm #1' )
		);
		// Time for alarm #2
		$this->Validator->isNumeric( 'schedule_icalendar_alarm2_working',
									 $this->getScheduleIcalendarAlarm2Working(),
									 TTi18n::gettext( 'Invalid time for alarm #2' )
		);
		// Time for alarm #1
		$this->Validator->isNumeric( 'schedule_icalendar_alarm1_absence',
									 $this->getScheduleIcalendarAlarm1Absence(),
									 TTi18n::gettext( 'Invalid time for alarm #1' )
		);
		// Time for alarm #2
		$this->Validator->isNumeric( 'schedule_icalendar_alarm2_absence',
									 $this->getScheduleIcalendarAlarm2Absence(),
									 TTi18n::gettext( 'Invalid time for alarm #2' )
		);
		// Time for alarm #1
		$this->Validator->isNumeric( 'schedule_icalendar_alarm1_modified',
									 $this->getScheduleIcalendarAlarm1Modified(),
									 TTi18n::gettext( 'Invalid time for alarm #1' )
		);
		// Time for alarm #2
		$this->Validator->isNumeric( 'schedule_icalendar_alarm2_modified',
									 $this->getScheduleIcalendarAlarm2Modified(),
									 TTi18n::gettext( 'Invalid time for alarm #2' )
		);
		// Default login screen
		if ( $this->getDefaultLoginScreen() != '' ) {
			$this->Validator->isLength( 'default_login_screen',
										$this->getDefaultLoginScreen(),
										TTi18n::gettext( 'Default login screen is too short or too long' ),
										0,
										250
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getUser() == '' ) {
			$this->Validator->isTRUE( 'user',
									  false,
									  TTi18n::gettext( 'Invalid Employee' ) );
		}

		if ( $this->getDateFormat() == '' ) {
			$this->Validator->isTRUE( 'date_format',
									  false,
									  TTi18n::gettext( 'Incorrect date format' ) );
		}

		// Duration of notification popup in web browser.
		if ( $this->getNotificationDuration() < 0 || $this->getNotificationDuration() > 86400 ) {
			$this->Validator->isTrue( 'notification_duration',
									  false,
									  TTi18n::gettext( 'Notification duration must be between 0 and 86400 seconds' ) );
		}

		// status of push notificstion settings
		$this->Validator->inArrayKey( 'notification_status_id',
									  $this->getNotificationStatus(),
									  TTi18n::gettext( 'Incorrect Notification Status' ),
									  $this->getOptions( 'notification_status' )
		);

		return true;
	}

	/**
	 * @return bool
	 */
	function preValidate() {

		global $config_vars;
		if ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] != 1 ) {
			//These cause SQL errors when upgrading from older schema versions (1113A), so skip them for now.
			if ( $this->getNotificationStatus() === false || $this->getNotificationStatus() === '' ) {
				$this->setNotificationStatus( 1 ); //1=Enabled
			}

			if ( $this->getNotificationDuration() === false || $this->getNotificationStatus() === '' ) {
				$this->setNotificationDuration( 120 );
			}
		}

		//Check the locale, if its not english, we need to make sure the selected date format is correct for the language, or else force it.
		if ( $this->getLanguage() != 'en' ) {
			if ( Option::getByValue( $this->getDateFormat(), $this->getOptions( 'other_date_format' ) ) == false ) {
				//Force a change of date format
				$this->setDateFormat( 'd/m/Y' );
				Debug::text( 'Language changed and date format doesnt match any longer, forcing it to: d/m/Y', __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				Debug::text( 'Date format doesnt need fixing...', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getUser() );
		if ( is_object( $this->getUserObject() ) ) {
			//CompanyFactory->getEncoding() is used to determine report encodings based on data saved here.
			$this->removeCache( 'encoding_' . $this->getUserObject()->getCompany(), 'company' );
		}

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
						case 'user_name':
						case 'user_status_id':
						case 'group_id':
						case 'user_group':
						case 'title_id':
						case 'title':
						case 'default_branch_id':
						case 'default_branch':
						case 'default_department_id':
						case 'default_department':
						case 'city':
						case 'province':
						case 'country':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'user_status':
							$data[$variable] = Option::getByKey( (int)$this->getColumn( 'user_status_id' ), $uf->getOptions( 'status' ) );
							break;

						//Add the *_display element for each of the below fields.
						case 'language_display':
						case 'time_zone_display':
						case 'time_unit_format_display':
						case 'distance_format_display':
						case 'timesheet_view_display':
						case 'start_week_day_display':
							switch ( $variable ) {
								case 'language_display':
									$function = 'getLanguage';
									break;
								//Use Date/Time format example functions below instead.
								//case 'date_format_display':
								//	$function = 'getDateFormat';
								//	break;
								//case 'time_format_display':
								//	$function = 'getTimeFormat';
								//	break;
								case 'time_zone_display':
									$function = 'getTimeZone';
									break;
								case 'time_unit_format_display':
									$function = 'getTimeUnitFormat';
									break;
								case 'distance_format_display':
									$function = 'getDistanceFormat';
									break;
								case 'timesheet_view_display':
									$function = 'getTimeSheetView';
									break;
								case 'start_week_day_display':
									$function = 'getStartWeekDay';
									break;
							}

							$variable = str_replace( '_display', '', $variable );
							if ( method_exists( $this, $function ) ) {
								$data[$variable . '_display'] = Option::getByKey( $this->$function(), Misc::trimSortPrefix( $this->getOptions( $variable ) ) );
							}
							break;
						case 'date_format_display':
							$data[$variable] = $this->getDateFormatExample();
							break;
						case 'time_format_display':
							$data[$variable] = $this->getTimeFormatExample();
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

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		$u_obj = $this->getUserObject();
		if ( is_object( $u_obj ) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee Preferences' ) . ': ' . $u_obj->getFullName( false, true ), null, $this->getTable(), $this );
		}

		return false;
	}
}

?>