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

include_once( 'Net/IPv4.php' );
include_once( 'Net/IPv6.php' );

/**
 * @package Core
 */
class StationFactory extends Factory {
	protected $table = 'station';
	protected $pk_sequence_name = 'station_id_seq'; //PK Sequence name

	public $station_id_validator_regex = '/^[a-zA-Z0-9-\*]{1,100}$/i'; //Need to allow: '-' due to UUID (iOS) and for phone numbers, '*' as its a wildcard. We *DONT* need to allow '.', ',' as those are in the Source field only.

	protected $company_obj = null;

	protected $json_columns = [ 'punch_tag_id' ];

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'station_id' )->setFunctionMap( 'Station' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'source' )->setFunctionMap( 'Source' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'description' )->setFunctionMap( 'Description' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'allowed_date' )->setFunctionMap( 'AllowedDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'branch_id' )->setFunctionMap( 'DefaultBranch' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'department_id' )->setFunctionMap( 'DefaultDepartment' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'time_zone' )->setFunctionMap( 'Time Zone' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_group_selection_type_id' )->setFunctionMap( 'GroupSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'branch_selection_type_id' )->setFunctionMap( 'BranchSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'department_selection_type_id' )->setFunctionMap( 'DepartmentSelectionType' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'port' )->setFunctionMap( 'Port' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'user_name' )->setFunctionMap( 'UserName' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'password' )->setFunctionMap( 'Password' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'poll_frequency' )->setFunctionMap( 'PollFrequency' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'push_frequency' )->setFunctionMap( 'PushFrequency' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'last_punch_time_stamp' )->setFunctionMap( 'LastPunchTimeStamp' )->setType( 'timestamptz' )->setIsNull( true ),
							TTSCol::new( 'last_poll_date' )->setFunctionMap( 'LastPollDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'last_poll_status_message' )->setFunctionMap( 'LastPollStatusMessage' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'last_push_date' )->setFunctionMap( 'LastPushDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'last_push_status_message' )->setFunctionMap( 'LastPushStatusMessage' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value_1' )->setFunctionMap( 'UserValue1' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value_2' )->setFunctionMap( 'UserValue2' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value_3' )->setFunctionMap( 'UserValue3' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value_4' )->setFunctionMap( 'UserValue4' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'user_value_5' )->setFunctionMap( 'UserValue5' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'partial_push_frequency' )->setFunctionMap( 'PartialPushFrequency' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'last_partial_push_date' )->setFunctionMap( 'LastPartialPushDate' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'last_partial_push_status_message' )->setFunctionMap( 'LastPartialPushStatusMessage' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'pull_start_time' )->setFunctionMap( 'PullStartTime' )->setType( 'timestamptz' )->setIsNull( true ),
							TTSCol::new( 'pull_end_time' )->setFunctionMap( 'PullEndTime' )->setType( 'timestamptz' )->setIsNull( true ),
							TTSCol::new( 'push_start_time' )->setFunctionMap( 'PushStartTime' )->setType( 'timestamptz' )->setIsNull( true ),
							TTSCol::new( 'push_end_time' )->setFunctionMap( 'PushEndTime' )->setType( 'timestamptz' )->setIsNull( true ),
							TTSCol::new( 'partial_push_start_time' )->setFunctionMap( 'PartialPushStartTime' )->setType( 'timestamptz' )->setIsNull( true ),
							TTSCol::new( 'partial_push_end_time' )->setFunctionMap( 'PartialPushEndTime' )->setType( 'timestamptz' )->setIsNull( true ),
							TTSCol::new( 'enable_auto_punch_status' )->setFunctionMap( 'EnableAutoPunchStatus' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'mode_flag' )->setFunctionMap( 'ModeFlag' )->setType( 'bigint' )->setIsNull( false ),
							TTSCol::new( 'work_code_definition' )->setFunctionMap( 'WorkCodeDefinition' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'job_id' )->setFunctionMap( 'DefaultJob' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'job_item_id' )->setFunctionMap( 'DefaultJobItem' )->setType( 'uuid' )->setIsNull( true ),
							TTSCol::new( 'default_mode_flag' )->setFunctionMap( 'DefaultModeFlag' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'punch_tag_id' )->setFunctionMap( 'DefaultPunchTag' )->setType( 'jsonb' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_station' )->setLabel( TTi18n::getText( 'Station' ) )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Type' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'station_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Station' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getOptions' )->setArg( 'station' ) ),
											TTSField::new( 'source' )->setType( 'text' )->setLabel( TTi18n::getText( 'Source' ) ),
											TTSField::new( 'description' )->setType( 'text' )->setLabel( TTi18n::getText( 'Description' ) ),
											TTSField::new( 'branch_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Default Branch' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) ),
											TTSField::new( 'department_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Default Department' ) )->setDataSource( TTSAPI::new( 'APIDepartment' )->setMethod( 'getDepartment' ) ),
											TTSField::new( 'include_user' )->setType( 'text' )->setLabel( TTi18n::getText( 'Include Employees' ) ),
											TTSField::new( 'exclude_user' )->setType( 'text' )->setLabel( TTi18n::getText( 'Exclude Employees' ) ),
											TTSField::new( 'password' )->setType( 'text' )->setLabel( TTi18n::getText( 'Password/COMM Key' ) ),
											TTSField::new( 'port' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Port' ) ),
											TTSField::new( 'time_zone' )->setType( 'text' )->setLabel( TTi18n::getText( 'Force Time Zone' ) ),
											TTSField::new( 'enable_auto_punch_status' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Enable Automatic Punch Status' ) ),
											TTSField::new( 'manual_command' )->setType( 'text' )->setLabel( TTi18n::getText( 'Manual Command' ) ),
											TTSField::new( 'last_punch_time_stamp' )->setType( 'datetime' )->setLabel( TTi18n::getText( 'Last Downloaded Punch' ) ),
											TTSField::new( 'mode_flag' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Configuration Modes' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getOptions' )->setArg( 'mode_flag' ) ),
											TTSField::new( 'default_mode_flag' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Default Punch Mode' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getOptions' )->setArg( 'default_mode_flag' ) ),
											TTSField::new( 'user_value_1' )->setType( 'text' )->setLabel( TTi18n::getText( 'Face Recognition Threshold' ) ),
											TTSField::new( 'user_value_2' )->setType( 'text' )->setLabel( TTi18n::getText( 'Face Recognition Matches' ) )
									)
							),
							TTSTab::new( 'tab_employee_criteria' )->setLabel( TTi18n::getText( 'Employee Criteria' ) )->setInitCallback( 'initSubEmployeeCriteriaView' )->setHTMLTemplate( 'this.getBranchEmployeeCriteriaTabHtml' )->setDisplayOnMassEdit( false )->setSubView( true ),
							TTSTab::new( 'tab_time_clock' )->setLabel( TTi18n::getText( 'TimeClock' ) )->setFields(
									new TTSFields(
											TTSField::new( 'password' )->setType( 'text' )->setLabel( TTi18n::getText( 'Password/COMM Key' ) ),
											TTSField::new( 'port' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Port' ) ),
											TTSField::new( 'time_zone' )->setType( 'text' )->setLabel( TTi18n::getText( 'Force Time Zone' ) ),
											TTSField::new( 'enable_auto_punch_status' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Enable Automatic Punch Status' ) ),
											TTSField::new( 'manual_command' )->setType( 'text' )->setLabel( TTi18n::getText( 'Manual Command' ) ),
											TTSField::new( 'last_punch_time_stamp' )->setType( 'datetime' )->setLabel( TTi18n::getText( 'Last Downloaded Punch' ) ),
											TTSField::new( 'mode_flag' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Configuration Modes' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getOptions' )->setArg( 'mode_flag' ) ),
											TTSField::new( 'default_mode_flag' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Default Punch Mode' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getOptions' )->setArg( 'default_mode_flag' ) ),
											TTSField::new( 'user_value_1' )->setType( 'text' )->setLabel( TTi18n::getText( 'Face Recognition Threshold' ) ),
											TTSField::new( 'user_value_2' )->setType( 'text' )->setLabel( TTi18n::getText( 'Face Recognition Matches' ) )
									)
							)
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'a.status_id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'type_id' )->setType( 'integer' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'station_id' )->setType( 'text' )->setColumn( 'a.station_id' ),
							TTSSearchField::new( 'source' )->setType( 'text' )->setColumn( 'a.source' ),
							TTSSearchField::new( 'description' )->setType( 'text' )->setColumn( 'a.description' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIStation' )->setMethod( 'getStation' )
									->setSummary( 'Get station records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIStation' )->setMethod( 'setStation' )
									->setSummary( 'Add or edit station records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIStation' )->setMethod( 'deleteStation' )
									->setSummary( 'Delete station records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIStation' )->setMethod( 'getStation' ) ),
											   ) ),
							TTSAPI::new( 'APIStation' )->setMethod( 'getStationDefaultData' )
									->setSummary( 'Get default station data used for creating new stations. Use this before calling setStation to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $params
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		//Attempt to get the edition of the currently logged in users company, so we can better tailor the columns to them.
		$product_edition_id = Misc::getCurrentCompanyProductEdition();

		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'DISABLED' ),
						20 => TTi18n::gettext( 'ENABLED' ),
				];
				break;
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'PC' ),
				];

				if ( $product_edition_id >= 15 ) {
					$retval[20] = TTi18n::gettext( 'PHONE' );
					$retval[25] = TTi18n::gettext( 'WirelessWeb (WAP)' );
					$retval[26] = TTi18n::gettext( 'Mobile Web Browser' );       //Controls mobile device web browser from quick punch.
					$retval[28] = TTi18n::gettext( 'Mobile App (iOS/Android)' ); //Controls Mobile application
					$retval[30] = TTi18n::gettext( 'iButton' );
					$retval[40] = TTi18n::gettext( 'Barcode' );
					$retval[50] = TTi18n::gettext( 'FingerPrint' );

					if ( PRODUCTION == false ) {
						$retval[60] = TTi18n::gettext( 'Desktop PC' ); //Single user mode desktop app.
						$retval[61] = TTi18n::gettext( 'Kiosk: Desktop PC' );

						//$retval[70]	= TTi18n::gettext('Kiosk: Web Browser'); //PhoneGap app on WebBrowser KIOSK
						//$retval[71]	= TTi18n::gettext('Web Browser App'); //PhoneGap app on WebBrowser
					}
					$retval[65] = TTi18n::gettext( 'Kiosk: Mobile App (iOS/Android)' ); //Mobile app in Kiosk Mode

					$retval[100] = TTi18n::gettext( 'TimeClock: TT-A8' );
					$retval[150] = TTi18n::gettext( 'TimeClock: TT-US100' );
					//$retval[200] = TTi18n::gettext('TimeClock: ACTAtek');
				}
				break;
			case 'station_reserved_word':
				$retval = [ 'any', '*' ];
				break;
			case 'source_reserved_word':
				$retval = [ 'any', '*' ];
				break;
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
			case 'group_selection_type':
				$retval = [
						10 => TTi18n::gettext( 'All Groups' ),
						20 => TTi18n::gettext( 'Only Selected Groups' ),
						30 => TTi18n::gettext( 'All Except Selected Groups' ),
				];
				break;
			case 'poll_frequency':
				$retval = [
						60     => TTi18n::gettext( '1 Minute' ),
						120    => TTi18n::gettext( '2 Minutes' ),
						300    => TTi18n::gettext( '5 Minutes' ),
						600    => TTi18n::gettext( '10 Minutes' ),
						900    => TTi18n::gettext( '15 Minutes' ),
						1800   => TTi18n::gettext( '30 Minutes' ),
						3600   => TTi18n::gettext( '1 Hour' ),
						7200   => TTi18n::gettext( '2 Hours' ),
						10800  => TTi18n::gettext( '3 Hours' ),
						21600  => TTi18n::gettext( '6 Hours' ),
						43200  => TTi18n::gettext( '12 Hours' ),
						86400  => TTi18n::gettext( '24 Hours' ),
						172800 => TTi18n::gettext( '48 Hours' ),
						259200 => TTi18n::gettext( '72 Hours' ),
						604800 => TTi18n::gettext( '1 Week' ),
				];
				break;
			case 'partial_push_frequency':
			case 'push_frequency':
				$retval = [
						60     => TTi18n::gettext( '1 Minute' ),
						120    => TTi18n::gettext( '2 Minutes' ),
						300    => TTi18n::gettext( '5 Minutes' ),
						600    => TTi18n::gettext( '10 Minutes' ),
						900    => TTi18n::gettext( '15 Minutes' ),
						1800   => TTi18n::gettext( '30 Minutes' ),
						3600   => TTi18n::gettext( '1 Hour' ),
						7200   => TTi18n::gettext( '2 Hours' ),
						10800  => TTi18n::gettext( '3 Hours' ),
						21600  => TTi18n::gettext( '6 Hours' ),
						43200  => TTi18n::gettext( '12 Hours' ),
						86400  => TTi18n::gettext( '24 Hours' ),
						172800 => TTi18n::gettext( '48 Hours' ),
						259200 => TTi18n::gettext( '72 Hours' ),
						604800 => TTi18n::gettext( '1 Week' ),
				];
				break;
			case 'time_clock_command':
				$retval = [
						'test_connection'             => TTi18n::gettext( 'Test Connection' ),
						'set_date'                    => TTi18n::gettext( 'Set Date' ),
						'download'                    => TTi18n::gettext( 'Download Data' ),
						'upload'                      => TTi18n::gettext( 'Upload Data' ),
						'update_config'               => TTi18n::gettext( 'Update Configuration' ),
						'delete_data'                 => TTi18n::gettext( 'Delete all Data' ),
						'reset_last_punch_time_stamp' => TTi18n::gettext( 'Reset Last Punch Time' ),
						'clear_last_punch_time_stamp' => TTi18n::gettext( 'Clear Last Punch Time' ),
						'restart'                     => TTi18n::gettext( 'Restart' ),
						'firmware'                    => TTi18n::gettext( 'Update Firmware (CAUTION)' ),
				];
				break;
			case 'mode_flag':
				Debug::Text( 'Mode Flag Type ID: ' . $params, __FILE__, __LINE__, __METHOD__, 10 );
				if ( $params == '' ) {
					$params = 0;
				}
				switch ( (int)$params ) { //Params should be the station type_id.
					case 28: //Mobile App
						$retval[$params] = [
								1     => TTi18n::gettext( '-- Default --' ),
								//2			=> TTi18n::gettext('Punch Mode: Quick Punch'), //Enabled by default.
								//4			=> TTi18n::gettext('Punch Mode: QRCode'),
								//8			=> TTi18n::gettext('Punch Mode: QRCode+Face Detection'),
								//16		=> TTi18n::gettext('Punch Mode: Face Recognition'),
								//32		=> TTi18n::gettext('Punch Mode: Face Recognition+QRCode'),
								//64		=> TTi18n::gettext('Punch Mode: Barcode'),
								//128		=> TTi18n::gettext('Punch Mode: iButton'),
								//256
								//512
								1024  => TTi18n::gettext( 'Disable: Punch' ),
								2048  => TTi18n::gettext( 'Disable: GPS' ),
								4096  => TTi18n::gettext( 'Enable: Punch Images' ),
								//8192	=> TTi18n::gettext('Enable: Screensaver'),
								//16384 => TTi18n::gettext( 'Enable: Auto-Login' ), //No longer used.
								//32768	=> TTi18n::gettext('Enable: WIFI Detection - Punch'),
								//65536	=> TTi18n::gettext('Enable: WIFI Detection - Alert'),

								131072  => TTi18n::gettext( 'QRCodes: Allow Multiple' ), //For single-employee mode scanning.
								262144  => TTi18n::gettext( 'QRCodes: Allow MACROs' ), //For single-employee mode scanning.
								1048576 => TTi18n::gettext( 'Disable: Time Synchronization' ),
								2097152 => TTi18n::gettext( 'Enable: Pre-Punch Message' ),
								4194304 => TTi18n::gettext( 'Enable: Post-Punch Message' ),
								8388608 => TTi18n::gettext( 'Enable: Exception Notice' ), //Shows warning or lock-out notice when an exception is triggered that has it enabled.

								1073741824 => TTi18n::gettext( 'Enable: Diagnostic Logs' ),
						];
						break;
					case 61: //PC Station in KIOSK mode.
					case 65: //Mobile App in KIOSK mode.
						$retval[$params] = [
								1     => TTi18n::gettext( '-- Default --' ),
								2     => TTi18n::gettext( 'Punch Mode: Quick Punch' ),
								4     => TTi18n::gettext( 'Punch Mode: QRCode' ),
								//8		=> TTi18n::gettext('Punch Mode: QRCode+Face Detection'), //Disabled in v4.0 of app, to simplify things.
								16    => TTi18n::gettext( 'Punch Mode: Facial Recognition' ),
								//32		=> TTi18n::gettext('Punch Mode: Facial Recognition+QRCode'),  //Disabled in v4.0 of app, to simplify things.
								//64		=> TTi18n::gettext('Punch Mode: Quick Punch+Facial Recognition'),
								//128
								//256
								//512
								//1024 -- Disable Punch on Single Employee mode.
								2048  => TTi18n::gettext( 'Disable: GPS' ),
								4096  => TTi18n::gettext( 'Enable: Punch Images' ),
								8192  => TTi18n::gettext( 'Disable: Screensaver' ),
								16384 => TTi18n::gettext( 'Disable: Default Transfer On' ), //This is not synchronized with mobile app, its handled server side currently.
								32768 => TTi18n::gettext( 'Disable: Punch Confirmation' ), //This could be converted to two settings to determine the timeout on the punch confirm/punch success screens instead.
								//65536

								131072  => TTi18n::gettext( 'QRCodes: Allow Multiple' ),
								262144  => TTi18n::gettext( 'QRCodes: Allow MACROs' ),
								1048576 => TTi18n::gettext( 'Disable: Time Synchronization' ),
								2097152 => TTi18n::gettext( 'Enable: Pre-Punch Message' ),
								4194304 => TTi18n::gettext( 'Enable: Post-Punch Message' ),
								8388608 => TTi18n::gettext( 'Enable: Exception Notice' ), //Shows warning or lock-out notice when an exception is triggered that has it enabled.

								1073741824 => TTi18n::gettext( 'Enable: Diagnostic Logs' ),
						];
						break;
					case 100: //TimeClock
					case 150: //TimeClock
					default:
						$retval[$params] = [
								1  => TTi18n::gettext( '-- Default --' ),
								2  => TTi18n::gettext( 'Must Select In/Out Status' ),
								//4		=> TTi18n::gettext('Enable Work Code (Mode 1)'),
								//8		=> TTi18n::gettext('Enable Work Code (Mode 2)'),
								4  => TTi18n::gettext( 'Disable Out Status' ),
								8  => TTi18n::gettext( 'Enable: Breaks' ),
								16 => TTi18n::gettext( 'Enable: Lunches' ),
								32 => TTi18n::gettext( 'Enable: Branch' ),
								64 => TTi18n::gettext( 'Enable: Department' ),

								32768  => TTi18n::gettext( 'Authentication: Fingerprint & Password' ),
								65536  => TTi18n::gettext( 'Authentication: Fingerprint & Proximity Card' ),
								131072 => TTi18n::gettext( 'Authentication: PIN & Fingerprint' ),
								262144 => TTi18n::gettext( 'Authentication: Proximity Card & Password' ),

								1048576 => TTi18n::gettext( 'Enable: External Proximity Card Reader' ),
								2097152 => TTi18n::gettext( 'Enable: Pre-Punch Message' ),
								4194304 => TTi18n::gettext( 'Enable: Post-Punch Message' ),

								1073741824 => TTi18n::gettext( 'Enable: Diagnostic Logs' ),
						];
						if ( $product_edition_id >= TT_PRODUCT_CORPORATE ) {
							$retval[$params][128] = TTi18n::gettext( 'Enable: Job' );
							$retval[$params][256] = TTi18n::gettext( 'Enable: Task' );
							$retval[$params][512] = TTi18n::gettext( 'Enable: Quantity' );
							$retval[$params][1024] = TTi18n::gettext( 'Enable: Bad Quantity' );
						}

						break;
				}

				ksort( $retval[$params] );

				//Handle cases where params isn't defined properly.
				if ( $params == 0 ) {
					$retval = $retval[$params];
				}
				break;
			case 'default_mode_flag':
				Debug::Text( 'Mode Flag Type ID: ' . $params, __FILE__, __LINE__, __METHOD__, 10 );
				if ( $params == '' ) {
					$params = 0;
				}
				switch ( (int)$params ) { //Params should be the station type_id.
					case 28: //Mobile App
						$retval[$params] = [];
						break;
					//case 61: //PC Station in KIOSK mode.
					case 65: //Mobile App in KIOSK mode.
						$retval[$params] = [
								2  => TTi18n::gettext( 'Punch Mode: Quick Punch' ),
								4  => TTi18n::gettext( 'Punch Mode: QRCode' ),
								16 => TTi18n::gettext( 'Punch Mode: Facial Recognition' ),
						];
						break;
					case 100: //TimeClock
					case 150: //TimeClock
					default:
						$retval[$params] = [];
						break;
				}

				ksort( $retval[$params] );

				//Handle cases where params isn't defined properly.
				if ( $params == 0 ) {
					$retval = $retval[$params];
				}
				break;
			case 'face_recognition_match_threshold':
				$retval = [
						//NOTE: For legacy purposes, the values that end if .0 must have 1 decimal place.
						'-1000-0'   => TTi18n::gettext( '-- Default --' ),
						'-1910-99.1'   => TTi18n::gettext( '1.0 (Least Accurate, Easiest)' ),
						'-1920-99.2'   => TTi18n::gettext( '2.0' ),
						'-1930-99.3'   => TTi18n::gettext( '3.0' ),
						'-1940-99.4'   => TTi18n::gettext( '4.0' ),
						'-1950-99.5'   => TTi18n::gettext( '5.0' ),
						'-1960-99.6'   => TTi18n::gettext( '6.0' ),
						//'-1961-99.61'   => TTi18n::gettext( '6.1' ),
						'-1962-99.62'   => TTi18n::gettext( '6.2' ),
						//'-1963-99.63'   => TTi18n::gettext( '6.3' ),
						'-1964-99.64'   => TTi18n::gettext( '6.4' ),
						//'-1965-99.65'   => TTi18n::gettext( '6.5' ),
						'-1966-99.66'   => TTi18n::gettext( '6.6' ),
						//'-1967-99.67'   => TTi18n::gettext( '6.7' ),
						'-1968-99.68'   => TTi18n::gettext( '6.8' ),
						//'-1969-99.69'   => TTi18n::gettext( '6.9' ),
						'-1970-99.7'   => TTi18n::gettext( '7.0 (Recommended)' ),
						//'-1971-99.71'   => TTi18n::gettext( '7.1' ),
						'-1972-99.72'   => TTi18n::gettext( '7.2' ),
						//'-1973-99.73'   => TTi18n::gettext( '7.3' ),
						'-1974-99.74'   => TTi18n::gettext( '7.4' ),
						//'-1975-99.75'   => TTi18n::gettext( '7.5' ),
						'-1976-99.76'   => TTi18n::gettext( '7.6' ),
						//'-1977-99.77'   => TTi18n::gettext( '7.7' ),
						'-1978-99.78'   => TTi18n::gettext( '7.8' ),
						//'-1979-99.79'   => TTi18n::gettext( '7.9' ),
						'-1980-99.8'   => TTi18n::gettext( '8.0' ),
						//'-1981-99.81'   => TTi18n::gettext( '8.1' ),
						'-1982-99.82'   => TTi18n::gettext( '8.2' ),
						//'-1983-99.83'   => TTi18n::gettext( '8.3' ),
						'-1984-99.84'   => TTi18n::gettext( '8.4' ),
						//'-1985-99.85'   => TTi18n::gettext( '8.5' ),
						'-1986-99.86'   => TTi18n::gettext( '8.6' ),
						//'-1987-99.87'   => TTi18n::gettext( '8.7' ),
						'-1988-99.88'   => TTi18n::gettext( '8.8' ),
						//'-1989-99.89'   => TTi18n::gettext( '8.9' ),
						'-1990-99.9'   => TTi18n::gettext( '9.0' ),
						'-1991-99.91'   => TTi18n::gettext( '9.1' ),
						'-1992-99.92'   => TTi18n::gettext( '9.2' ),
						'-1993-99.93'   => TTi18n::gettext( '9.3' ),
						'-1994-99.94'   => TTi18n::gettext( '9.4' ),
						'-1995-99.95'   => TTi18n::gettext( '9.5' ),
						'-1996-99.96'   => TTi18n::gettext( '9.6' ),
						'-1997-99.97'   => TTi18n::gettext( '9.7' ),
						'-1998-99.98'   => TTi18n::gettext( '9.8' ),
						'-1999-99.99'   => TTi18n::gettext( '9.9' ),
						'-2000-100'    => TTi18n::gettext( '10.0 (Most Accurate, Hardest)' ),
				];
				break;
			case 'face_recognition_required_matches':
				$retval = [
						'-1000-0'   => TTi18n::gettext( '-- Default --' ),
						'-1001-1'   => TTi18n::gettext( '1 (Least Accurate, Fastest)' ), //Takes around 0.1 seconds to obtain.
						'-1002-2'   => TTi18n::gettext( '2' ),
						'-1003-3'   => TTi18n::gettext( '3 (Recommended)' ), //Takes around 0.5 seconds to obtain.
						'-1004-4'   => TTi18n::gettext( '4' ),
						'-1005-5'   => TTi18n::gettext( '5' ),
						'-1006-6'   => TTi18n::gettext( '6' ),
						'-1007-7'   => TTi18n::gettext( '7' ),
						'-1008-8'   => TTi18n::gettext( '8' ),
						'-1009-9'   => TTi18n::gettext( '9' ),
						'-1010-10'  => TTi18n::gettext( '10' ), //Takes around 1.5 seconds to obtain.
						'-1011-11'  => TTi18n::gettext( '11' ),
						'-1012-12'  => TTi18n::gettext( '12' ),
						'-1013-13'  => TTi18n::gettext( '13' ),
						'-1014-14'  => TTi18n::gettext( '14' ),
						'-1015-15'  => TTi18n::gettext( '15' ),
						'-1016-16'  => TTi18n::gettext( '16' ),
						'-1017-17'  => TTi18n::gettext( '17' ),
						'-1018-18'  => TTi18n::gettext( '18' ),
						'-1019-19'  => TTi18n::gettext( '19' ),
						'-1020-20'  => TTi18n::gettext( '20 (Most Accurate, Slowest)' ), //Takes around 3 seconds to obtain.
				];
				break;
			case 'columns':
				$retval = [
						'-1010-status' => TTi18n::gettext( 'Status' ),
						'-1020-type'   => TTi18n::gettext( 'Type' ),
						'-1030-source' => TTi18n::gettext( 'Source' ),

						'-1140-station_id'  => TTi18n::gettext( 'Station' ),
						'-1150-description' => TTi18n::gettext( 'Description' ),

						'-1160-time_zone' => TTi18n::gettext( 'Time Zone' ),

						//'-1170-branch_selection_type'     => TTi18n::gettext( 'Branch Selection Type' ),
						//'-1180-department_selection_type' => TTi18n::gettext( 'Department Selection Type' ),
						//'-1190-group_selection_type'      => TTi18n::gettext( 'Group Selection Type' ),

						'-1200-last_punch_time_stamp' => TTi18n::gettext( 'Last Punch' ),
						'-1210-last_poll_date' 	      => TTi18n::gettext( 'Last Data Synchronization' ),

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
						'status',
						'type',
						'source',
						'station_id',
						'description',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'station_id',
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
				'id'                               => 'ID',
				'company_id'                       => 'Company',
				'status_id'                        => 'Status',
				'status'                           => false,
				'type_id'                          => 'Type',
				'type'                             => false,
				'station_id'                       => 'Station',
				'source'                           => 'Source',
				'description'                      => 'Description',
				'branch_id'                        => 'DefaultBranch',
				'department_id'                    => 'DefaultDepartment',
				'job_id'                           => 'DefaultJob',
				'job_item_id'                      => 'DefaultJobItem',
				'punch_tag_id'                     => 'DefaultPunchTag',
				'time_zone'                        => 'TimeZone',
				'user_group_selection_type_id'     => 'GroupSelectionType',
				'group_selection_type'             => false,
				'group'                            => false,
				'branch_selection_type_id'         => 'BranchSelectionType',
				'branch_selection_type'            => false,
				'branch'                           => false,
				'department_selection_type_id'     => 'DepartmentSelectionType',
				'department_selection_type'        => false,
				'department'                       => false,
				'include_user'                     => false,
				'exclude_user'                     => false,
				'port'                             => 'Port',
				'user_name'                        => 'UserName',
				'password'                         => 'Password',
				'poll_frequency'                   => 'PollFrequency',
				'push_frequency'                   => 'PushFrequency',
				'partial_push_frequency'           => 'PartialPushFrequency',
				'enable_auto_punch_status'         => 'EnableAutoPunchStatus',
				'mode_flag'                        => 'ModeFlag',
				'default_mode_flag'                => 'DefaultModeFlag',
				'work_code_definition'             => 'WorkCodeDefinition',
				'last_punch_time_stamp'            => 'LastPunchTimeStamp',
				'last_poll_date'                   => 'LastPollDate',
				'last_poll_status_message'         => 'LastPollStatusMessage',
				'last_push_date'                   => 'LastPushDate',
				'last_push_status_message'         => 'LastPushStatusMessage',
				'last_partial_push_date'           => 'LastPartialPushDate',
				'last_partial_push_status_message' => 'LastPartialPushStatusMessage',
				'user_value_1'                     => 'UserValue1',
				'user_value_2'                     => 'UserValue2',
				'user_value_3'                     => 'UserValue3',
				'user_value_4'                     => 'UserValue4',
				'user_value_5'                     => 'UserValue5',
				'allowed_date'                     => 'AllowedDate',
				'deleted'                          => 'Deleted',
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
	 * @return mixed
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
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'status_id', $value );
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
		$value = trim( $value ); //Don't cast to (int) above the Option::getByValue() call, otherwise string types will fail on the TimeTrex Client Application.

		//This needs to be stay as TimeTrex Client application still uses names rather than IDs.
		$key = Option::getByValue( $value, $this->getOptions( 'type' ) );
		if ( $key !== false ) {
			$value = $key;
		}
		$value = (int)$value;

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @param $station
	 * @return bool
	 */
	function isUniqueStation( $station ) {
		$ph = [
				'company_id' => $this->getCompany(),
				'station'    => (string)$station,
		];

		$query = 'select id from ' . $this->table . ' where company_id = ? AND station_id = ? AND deleted=0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique Station: ' . $station, __FILE__, __LINE__, __METHOD__, 10 );

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
	 * @return bool|string
	 */
	function getStation() {
		return (string)$this->getGenericDataValue( 'station_id' ); //Should not be cast to INT!
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setStation( $value = null ) {
		$value = trim( $value );
		if ( empty( $value ) ) {
			$value = $this->genStationID();
		}

		return $this->setGenericDataValue( 'station_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getSource() {
		return $this->getGenericDataValue( 'source' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSource( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'source', $value );
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
	function getDefaultBranch() {
		return $this->getGenericDataValue( 'branch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultBranch( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'branch_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultDepartment() {
		return $this->getGenericDataValue( 'department_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultDepartment( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'department_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultJob() {
		return $this->getGenericDataValue( 'job_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultJob( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Default Job ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'job_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultJobItem() {
		return $this->getGenericDataValue( 'job_item_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultJobItem( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Default Job Item ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = TTUUID::getZeroID();
		}

		return $this->setGenericDataValue( 'job_item_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getDefaultPunchTag() {
		//Always return an array.
		$this->decodeJSONColumn( 'punch_tag_id' );
		$value = $this->getGenericDataValue( 'punch_tag_id' );

		if ( $value == false ) {
			return [];
		}

		return $value;
	}

	/**
	 * @param array|string $value UUID
	 * @return bool
	 */
	function setDefaultPunchTag( $value ) {
		if ( getTTProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
			$value = null;
		}

		if ( $value == TTUUID::getZeroID() || empty( $value ) ) {
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
	 * @return bool|int
	 */
	function getGroupSelectionType() {
		return $this->getGenericDataValue( 'user_group_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setGroupSelectionType( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'user_group_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getGroup() {
		$lf = TTnew( 'StationUserGroupListFactory' ); /** @var StationUserGroupListFactory $lf */
		$lf->getByStationId( $this->getId() );
		$list = [];
		foreach ( $lf as $obj ) {
			$list[] = $obj->getGroup();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setGroup( $ids ) {
		if ( $ids == '' ) {
			$ids = []; //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		if ( !is_array( $ids ) && TTUUID::isUUID( $ids ) ) {
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationUserGroupListFactory' ); /** @var StationUserGroupListFactory $lf_a */
				$lf_a->getByStationId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getGroup();
					Debug::text( 'Group ID: ' . $obj->getGroup() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'UserGroupListFactory' ); /** @var UserGroupListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( $id !== false && ( TTUUID::isUUID( $id ) && ( $id == TTUUID::getNotExistID() || $id != TTUUID::getZeroID() ) ) && !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'StationUserGroupFactory' ); /** @var StationUserGroupFactory $f */
					$f->setStation( $this->getId() );
					$f->setGroup( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'group',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Group is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

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
	 * @return array|bool
	 */
	function getBranch() {
		$lf = TTnew( 'StationBranchListFactory' ); /** @var StationBranchListFactory $lf */
		$lf->getByStationId( $this->getId() );
		$list = [];
		foreach ( $lf as $obj ) {
			$list[] = $obj->getBranch();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setBranch( $ids ) {
		if ( $ids == '' ) {
			$ids = []; //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		if ( !is_array( $ids ) && TTUUID::isUUID( $ids ) ) {
			$ids = [ $ids ];
		}

		//Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Arr($ids, 'IDs: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationBranchListFactory' ); /** @var StationBranchListFactory $lf_a */
				$lf_a->getByStationId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getBranch();
					//Debug::text('Branch ID: '. $obj->getBranch() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( $id !== false && ( TTUUID::isUUID( $id ) && $id != TTUUID::getNotExistID() && $id != TTUUID::getZeroID() ) && !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'StationBranchFactory' ); /** @var StationBranchFactory $f */
					$f->setStation( $this->getId() );
					$f->setBranch( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'branch',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Branch is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
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
	 * @return array|bool
	 */
	function getDepartment() {
		$lf = TTnew( 'StationDepartmentListFactory' ); /** @var StationDepartmentListFactory $lf */
		$lf->getByStationId( $this->getId() );
		$list = [];
		foreach ( $lf as $obj ) {
			$list[] = $obj->getDepartment();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setDepartment( $ids ) {
		if ( $ids == '' ) {
			$ids = []; //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		if ( !is_array( $ids ) && TTUUID::isUUID( $ids ) ) {
			$ids = [ $ids ];
		}

		//Debug::text('Setting IDs...', __FILE__, __LINE__, __METHOD__, 10);
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationDepartmentListFactory' ); /** @var StationDepartmentListFactory $lf_a */
				$lf_a->getByStationId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getDepartment();
					//Debug::text('Department ID: '. $obj->getDepartment() .' ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( $id !== false && ( TTUUID::isUUID( $id ) && $id != TTUUID::getNotExistID() && $id != TTUUID::getZeroID() ) && !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'StationDepartmentFactory' ); /** @var StationDepartmentFactory $f */
					$f->setStation( $this->getId() );
					$f->setDepartment( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'department',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Department is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return array|bool
	 */
	function getIncludeUser() {
		$lf = TTnew( 'StationIncludeUserListFactory' ); /** @var StationIncludeUserListFactory $lf */
		$lf->getByStationId( $this->getId() );
		$list = [];
		foreach ( $lf as $obj ) {
			$list[] = $obj->getIncludeUser();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setIncludeUser( $ids ) {
		if ( $ids == '' ) {
			$ids = []; //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		if ( !is_array( $ids ) && TTUUID::isUUID( $ids ) ) {
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationIncludeUserListFactory' ); /** @var StationIncludeUserListFactory $lf_a */
				$lf_a->getByStationId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getIncludeUser();
					Debug::text( 'IncludeUser ID: ' . $obj->getIncludeUser() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'UserListFactory' ); /** @var UserListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( $id !== false && ( TTUUID::isUUID( $id ) && $id != TTUUID::getNotExistID() && $id != TTUUID::getZeroID() ) && !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'StationIncludeUserFactory' ); /** @var StationIncludeUserFactory $f */
					$f->setStation( $this->getId() );
					$f->setIncludeUser( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'include_user',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Employee is invalid' ) . ' (' . $obj->getFullName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @return array|bool
	 */
	function getExcludeUser() {
		$lf = TTnew( 'StationExcludeUserListFactory' ); /** @var StationExcludeUserListFactory $lf */
		$lf->getByStationId( $this->getId() );
		$list = [];
		foreach ( $lf as $obj ) {
			$list[] = $obj->getExcludeUser();
		}

		if ( empty( $list ) == false ) {
			return $list;
		}

		return false;
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setExcludeUser( $ids ) {
		if ( $ids == '' ) {
			$ids = []; //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		if ( !is_array( $ids ) && TTUUID::isUUID( $ids ) ) {
			$ids = [ $ids ];
		}

		Debug::text( 'Setting IDs...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_array( $ids ) ) {
			$tmp_ids = [];

			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$lf_a = TTnew( 'StationExcludeUserListFactory' ); /** @var StationExcludeUserListFactory $lf_a */
				$lf_a->getByStationId( $this->getId() );

				foreach ( $lf_a as $obj ) {
					$id = $obj->getExcludeUser();
					Debug::text( 'ExcludeUser ID: ' . $obj->getExcludeUser() . ' ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

					//Delete users that are not selected.
					if ( !in_array( $id, $ids ) ) {
						Debug::text( 'Deleting: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$obj->Delete();
					} else {
						//Save ID's that need to be updated.
						Debug::text( 'NOT Deleting : ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
						$tmp_ids[] = $id;
					}
				}
				unset( $id, $obj );
			}

			//Insert new mappings.
			$lf_b = TTnew( 'UserListFactory' ); /** @var UserListFactory $lf_b */

			foreach ( $ids as $id ) {
				if ( $id !== false && ( TTUUID::isUUID( $id ) && $id != TTUUID::getNotExistID() && $id != TTUUID::getZeroID() ) && !in_array( $id, $tmp_ids ) ) {
					$f = TTnew( 'StationExcludeUserFactory' ); /** @var StationExcludeUserFactory $f */
					$f->setStation( $this->getId() );
					$f->setExcludeUser( $id );

					$obj = $lf_b->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'exclude_user',
												   $f->isValid(),
												   TTi18n::gettext( 'Selected Employee is invalid' ) . ' (' . $obj->getFullName() . ')' ) ) {
						$f->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}



	/*

		TimeClock specific fields

	*/
	/**
	 * @return bool|mixed
	 */
	function getPort() {
		return $this->getGenericDataValue( 'port' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPort( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'port', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getUserName() {
		return $this->getGenericDataValue( 'user_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPassword() {
		return $this->getGenericDataValue( 'password' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPassword( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'password', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPollFrequency() {
		return $this->getGenericDataValue( 'poll_frequency' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPollFrequency( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'poll_frequency', $value );
	}


	/**
	 * @return bool|mixed
	 */
	function getPushFrequency() {
		return $this->getGenericDataValue( 'push_frequency' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPushFrequency( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'push_frequency', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPartialPushFrequency() {
		return $this->getGenericDataValue( 'partial_push_frequency' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPartialPushFrequency( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'partial_push_frequency', $value );
	}

	/**
	 * @return bool
	 */
	function getEnableAutoPunchStatus() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_auto_punch_status' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableAutoPunchStatus( $value ) {
		return $this->setGenericDataValue( 'enable_auto_punch_status', $this->toBool( $value ) );
	}

	/**
	 * @return array|bool
	 */
	function getModeFlag() {
		$value = $this->getGenericDataValue( 'mode_flag' );
		if ( $value !== false ) {
			return Option::getArrayByBitMask( $value, $this->getOptions( 'mode_flag', $this->getType() ) );
		}

		return false;
	}

	/**
	 * @param $arr
	 * @return bool
	 */
	function setModeFlag( $arr ) {
		$value = Option::getBitMaskByArray( $arr, $this->getOptions( 'mode_flag', $this->getType() ) );

		return $this->setGenericDataValue( 'mode_flag', $value );
	}

	/**
	 * @return bool|int
	 */
	function getDefaultModeFlag() {
		return $this->getGenericDataValue( 'default_mode_flag' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDefaultModeFlag( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'default_mode_flag', $value );
	}

	/**
	 * Update JUST station last_poll_date AND last_punch_time_stamp without affecting updated_date, and without creating an EDIT entry in the system_log.
	 * @param string $id UUID
	 * @param int $last_poll_date
	 * @param int $last_punch_date
	 * @return bool
	 */
	function updateLastPollDateAndLastPunchTimeStamp( $id, $last_poll_date = 0, $last_punch_date = 0 ) {
		if ( $id == '' ) {
			return false;
		}

		$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = [
					'last_poll_date'  => $last_poll_date,
					'last_punch_date' => $this->db->BindTimeStamp( $last_punch_date ),
					'id'              => $id,
			];
			$query = 'UPDATE ' . $this->getTable() . ' set last_poll_date = ?, last_punch_time_stamp = ? where id = ?';
			$this->ExecuteSQL( $query, $ph );

			return true;
		}

		return false;
	}

	/**
	 * Update JUST station last_poll_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	 * @param string $id UUID
	 * @param int $last_poll_date
	 * @return bool
	 */
	function updateLastPollDate( $id, $last_poll_date = 0 ) {
		if ( $id == '' ) {
			return false;
		}

		$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = [
					'last_poll_date' => $last_poll_date,
					'id'             => $id,
			];
			$query = 'UPDATE ' . $this->getTable() . ' set last_poll_date = ? where id = ?';
			$this->ExecuteSQL( $query, $ph );

			return true;
		}

		return false;
	}

	/**
	 * Update JUST station last_push_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	 * @param string $id UUID
	 * @param int $last_push_date
	 * @return bool
	 */
	function updateLastPushDate( $id, $last_push_date = 0 ) {
		if ( $id == '' ) {
			return false;
		}

		$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = [
					'last_push_date' => $last_push_date,
					'id'             => $id,
			];

			$query = 'UPDATE ' . $this->getTable() . ' set last_push_date = ? where id = ?';
			$this->ExecuteSQL( $query, $ph );

			return true;
		}

		return false;
	}

	/**
	 * Update JUST station last_partial_push_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	 * @param string $id UUID
	 * @param int $last_partial_push_date
	 * @return bool
	 */
	function updateLastPartialPushDate( $id, $last_partial_push_date = 0 ) {
		if ( $id == '' ) {
			return false;
		}

		$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */
		$slf->getById( $id );
		if ( $slf->getRecordCount() == 1 ) {
			$ph = [
					'last_partial_push_date' => $last_partial_push_date,
					'id'                     => $id,
			];

			$query = 'UPDATE ' . $this->getTable() . ' set last_partial_push_date = ? where id = ?';
			$this->ExecuteSQL( $query, $ph );

			return true;
		}

		return false;
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getLastPunchTimeStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'last_punch_time_stamp' );
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
	function setLastPunchTimeStamp( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'last_punch_time_stamp', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastPollDate() {
		return $this->getGenericDataValue( 'last_poll_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLastPollDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'last_poll_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastPollStatusMessage() {
		return $this->getGenericDataValue( 'last_poll_status_message' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastPollStatusMessage( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'last_poll_status_message', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastPushDate() {
		return $this->getGenericDataValue( 'last_push_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLastPushDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'last_push_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastPushStatusMessage() {
		return $this->getGenericDataValue( 'last_push_status_message' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastPushStatusMessage( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'last_push_status_message', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastPartialPushDate() {
		return $this->getGenericDataValue( 'last_partial_push_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLastPartialPushDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'last_partial_push_date', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastPartialPushStatusMessage() {
		return $this->getGenericDataValue( 'last_partial_push_status_message' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastPartialPushStatusMessage( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'last_partial_push_status_message', $value );
	}

	/**
	 * @return string
	 */
	function getUserValue1() {
		return $this->getGenericDataValue( 'user_value_1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue1( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value_1', $value );
	}

	/**
	 * @return string
	 */
	function getUserValue2() {
		return $this->getGenericDataValue( 'user_value_2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue2( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value_2', $value );
	}

	/**
	 * @return string
	 */
	function getUserValue3() {
		return $this->getGenericDataValue( 'user_value_3' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue3( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value_3', $value );
	}

	/**
	 * @return string
	 */
	function getUserValue4() {
		return $this->getGenericDataValue( 'user_value_4' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue4( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value_4', $value );
	}

	/**
	 * @return string
	 */
	function getUserValue5() {
		return $this->getGenericDataValue( 'user_value_5' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserValue5( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_value_5', $value );
	}


	/**
	 * @return string
	 */
	private function genStationID() {
		return md5( uniqid( dechex( mt_rand() ), true ) );
	}

	/**
	 * @return bool
	 */
	function setCookie() {
		if ( $this->getStation() ) {

			setcookie( 'StationID', $this->getStation(), ( time() + 157680000 ), Environment::getCookieBaseURL() );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function destroyCookie() {
		setcookie( 'StationID', '', ( time() + 9999999 ), Environment::getCookieBaseURL() );

		return true;
	}

	/**
	 * Update JUST station allowed_date without affecting updated_date, and without creating an EDIT entry in the system_log.
	 * @param string $id      UUID
	 * @param string $user_id UUID
	 * @return bool
	 */
	function updateAllowedDate( $id, $user_id ) {
		if ( $id == '' ) {
			return false;
		}

		if ( $user_id == '' ) {
			return false;
		}

		if ( TTUUID::isUUID( $id ) == true ) { //Don't bother looking up the station record again as this is only called from one place and we can fairly confident the record already exists.
			Debug::text( '  Updating Allowed Date for Station: ID: ' . $id . ' User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );

			$ph = [
					'allowed_date' => TTDate::getTime(),
					'id'           => $id,
			];

			//If many users are punching in/out at the same time and it happens to be associated with a wildcard station, there would be lock contention on that record.
			//  Since allowed_date is not critically important, skip updating it if the row is already locked.
			//$query = 'UPDATE ' . $this->getTable() . ' set allowed_date = ? where id = ?';
			$query = 'UPDATE ' . $this->getTable() . ' as b set allowed_date = ? WHERE EXISTS ( SELECT null FROM '. $this->getTable() .' as a where b.id = a.id and b.id = ? FOR UPDATE OF A SKIP LOCKED )';
			$this->ExecuteSQL( $query, $ph );
			//Debug::Query( $query, $ph, __FILE__, __LINE__, __METHOD__, 10 );

			TTLog::addEntry( $id, 200, TTi18n::getText( 'Access from station Allowed' ), $user_id, $this->getTable() ); //Allow

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getAllowedDate() {
		return $this->getGenericDataValue( 'allowed_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setAllowedDate( $value = null ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.
		if ( $value == null ) {
			$value = TTDate::getTime();
		}

		return $this->setGenericDataValue( 'allowed_date', $value );
	}

	/**
	 * @param $source
	 * @param string $current_station_id UUID
	 * @return bool
	 */
	function checkSource( $source, $current_station_id ) {
		$source = trim( $source );

		$remote_addr = Misc::getRemoteIPAddress();

		if ( in_array( $this->getType(), [ 10, 25, 26, 28 ] )
				&& (
						preg_match( '/[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}(\/[0-9]{1,2})*/', $source ) //IPv4
						||
						preg_match( '/(((?=(?>.*?(::))(?!.+\3)))\3?|([\dA-F]{1,4}(\3|:(?!$)|$)|\2))(?4){5}((?4){2}|((2[0-4]|1\d|[1-9])?\d|25[0-5])(\.(?7)){3})*/i', $source ) //IPv6
				)
		) {
			Debug::text( 'Source is an IP address!', __FILE__, __LINE__, __METHOD__, 10 );
		} else if ( in_array( $this->getType(), [ 10, 25, 26, 28, 100 ] ) && !in_array( strtolower( $this->getSource() ), $this->getOptions( 'station_reserved_word' ) ) ) {
			//Do hostname lookups for TTA8 timeclocks as well.
			Debug::text( 'Source is NOT an IP address, do hostname lookup: ' . $source, __FILE__, __LINE__, __METHOD__, 10 );

			$hostname_lookup = $this->getCache( 'station_source_dns_' . $this->getCompany() . $source );
			if ( $hostname_lookup === false ) {
				$hostname_lookup = gethostbyname( $source );

				$this->saveCache( $hostname_lookup, 'station_source_dns_' . $this->getCompany() . $source );
			}

			if ( $hostname_lookup == $source ) {
				Debug::text( 'Hostname lookup failed!', __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				Debug::text( 'Hostname lookup succeeded: ' . $hostname_lookup, __FILE__, __LINE__, __METHOD__, 10 );
				$source = $hostname_lookup;
			}
			unset( $hostname_lookup );
		} else {
			Debug::text( 'Source is not internet related', __FILE__, __LINE__, __METHOD__, 10 );
		}

		Debug::text( 'Source: ' . $source . ' Remote IP: ' . $remote_addr, __FILE__, __LINE__, __METHOD__, 10 );
		if ( (
						$current_station_id == $this->getStation()
						|| in_array( strtolower( $this->getStation() ), $this->getOptions( 'station_reserved_word' ) )
				)
				&&
				(
						in_array( strtolower( $this->getSource() ), $this->getOptions( 'source_reserved_word' ) )
						||
						( $source == $remote_addr )
						||
						( $current_station_id == $this->getSource() )
						||
						( strpos( $remote_addr, '.' ) !== false && Net_IPv4::validateIP( $remote_addr ) && strpos( $source, '.' ) !== false && Net_IPv4::ipInNetwork( $remote_addr, $source ) ) //Make sure its a IPv4 address by looking for a '.'
						||
						( strpos( $remote_addr, ':' ) !== false && Net_IPv6::checkIPv6( $remote_addr ) && strpos( $source, ':' ) !== false && strpos( $source, '/' ) !== false && Net_IPv6::isInNetmask( $remote_addr, $source ) ) //isInNetMask requires a netmask to be specified, otherwise it always returns TRUE.
						||
						in_array( $this->getType(), [ 100, 110, 120, 200 ] )
				)

		) {

			Debug::text( 'Returning TRUE', __FILE__, __LINE__, __METHOD__, 10 );

			return true;
		}

		Debug::text( 'Returning FALSE', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param string $user_id            UUID
	 * @param string $current_station_id UUID
	 * @param string $id                 UUID
	 * @param bool $update_allowed_date
	 * @return bool
	 */
	function isAllowed( $user_id = null, $current_station_id = null, $id = null, $update_allowed_date = true ) {
		if ( $user_id == null || $user_id == '' ) {
			global $current_user;
			$user_id = $current_user->getId();
		}
		//Debug::text('User ID: '. $user_id, __FILE__, __LINE__, __METHOD__, 10);

		if ( $current_station_id == null || $current_station_id == '' ) {
			global $current_station;
			$current_station_id = $current_station->getStation();
		}
		//Debug::text('Station ID: '. $current_station_id, __FILE__, __LINE__, __METHOD__, 10);

		//Debug::text('Status: '. $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $this->getStatus() != 20 ) { //Enabled
			return false;
		}

		$retval = false;

		Debug::text( 'User ID: ' . $user_id . ' Station ID: ' . $current_station_id . ' ID: '. $id .' Status: ' . $this->getStatus() . ' Current Station: ' . $this->getStation(), __FILE__, __LINE__, __METHOD__, 10 );

		//Handle IP Addresses/Hostnames
		if ( in_array( $this->getType(), [ 10, 25, 26, 28 ] )
				&& !in_array( strtolower( $this->getSource() ), $this->getOptions( 'source_reserved_word' ) ) ) {

			if ( strpos( $this->getSource(), ',' ) !== false ) {
				//Found list
				$source = explode( ',', $this->getSource() );
			} else {
				//Found single entry
				$source[] = $this->getSource();
			}

			if ( is_array( $source ) ) {
				foreach ( $source as $tmp_source ) {
					if ( $this->checkSource( $tmp_source, $current_station_id ) == true ) {
						$retval = true;
						break;
					}
				}
				unset( $tmp_source );
			}
		} else {
			$source = $this->getSource();

			$retval = $this->checkSource( $source, $current_station_id );
		}

		//Debug::text('Retval: '. (int)$retval, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::text('Current Station ID: '. $current_station_id .' Station ID: '. $this->getStation(), __FILE__, __LINE__, __METHOD__, 10);

		if ( $retval === true ) {
			Debug::text( 'Station IS allowed! ', __FILE__, __LINE__, __METHOD__, 10 );

			//Set last allowed date, so we can track active/inactive stations.
			if ( $id != null && $id != '' && $update_allowed_date == true ) {
				$this->updateAllowedDate( $id, $user_id );
			}

			return true;
		}

		Debug::text( 'Station IS NOT allowed! ', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	/**
	 * A fast way to check many stations if the user is allowed. 10 = PC
	 * @param string $user_id    UUID
	 * @param string $station_id UUID
	 * @param int $type
	 * @param bool $update_allowed_date
	 * @return bool
	 * @parem bool $update_allowed_date Updates the station allowed date, which should only be done when a punch is saved.
	 */
	function checkAllowed( $user_id = null, $station_id = null, $type = 10, $update_allowed_date = true, $return_station_id = false ) {
		if ( $user_id == null || $user_id == '' ) {
			global $current_user;
			$user_id = $current_user->getId();
		}
		Debug::text( 'User ID: ' . $user_id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $station_id == null || $station_id == '' ) {
			global $current_station;
			if ( is_object( $current_station ) ) {
				$station_id = $current_station->getStation();
			} else if ( $this->getId() != '' ) {
				$station_id = $this->getId();
			} else {
				Debug::text( 'Unable to get Station Object! Station ID: ' . $station_id, __FILE__, __LINE__, __METHOD__, 10 );

				return false;
			}
		}

		$slf = TTnew( 'StationListFactory' ); /** @var StationListFactory $slf */
		$slf->getByUserIdAndStatusAndType( $user_id, 20, $type, $station_id ); //Station ID just helps order more specific stations to the top of the list.
		Debug::text( 'Station ID: ' . $station_id . ' Type: ' . $type . ' Found Stations: ' . $slf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $slf->getRecordCount() > 0 ) {
			foreach ( $slf as $station ) { /** @var StationFactory $station */
				Debug::text( 'Checking Station ID: ' . $station->getId(), __FILE__, __LINE__, __METHOD__, 10 );

				if ( $station->isAllowed( $user_id, $station_id, $station->getId(), $update_allowed_date ) === true ) {
					Debug::text( 'Station IS allowed! ' . $station_id . ' - ID: ' . $station->getId() . ' Return Station ID: ' . (int)$return_station_id, __FILE__, __LINE__, __METHOD__, 10 );

					if ( $return_station_id == true ) {
						return $station->getId();
					} else {
						return true;
					}
				}
			}
		}

		return false;
	}

	/**
	 * @param string $station_id UUID
	 * @param string $company_id UUID
	 * @param int $type_id
	 * @param string $description
	 * @param Permission $permission_obj
	 * @param UserFactory $user_obj
	 * @return bool|object|StationFactory|StationListFactory|string
	 * @throws DBError
	 * @throws GeneralError
	 */
	function getOrCreateStation( $station_id, $company_id, $type_id = 10, $description = null, $permission_obj = null, $user_obj = null ) {
		//If station_id is blank (browser doesn't have a station cookie) and its a web browser, use the browser fingerprint.
		if ( $station_id == '' && ( $type_id == 10 || $type_id == 26 ) ) {
			$station_id = Misc::getBrowserFingerprint( true );
		}

		Debug::text( 'Checking for Station ID: ' . $station_id . ' Company ID: ' . $company_id . ' Type: ' . $type_id, __FILE__, __LINE__, __METHOD__, 10 );

		$slf = new StationListFactory();

		$advisory_lock_id = 'getOrCreateStation:' . $station_id . $company_id;

		//Since we don't have unique indexes to prevent duplicate station records, we need to use advisory locks instead to prevent duplicate stations from being created.
		try {
			$this->acquireAdvisoryLock( $advisory_lock_id, false, 3, 0.5 ); //Throws exception on failure.
		} catch ( Exception $e ) {
			Debug::Text( 'ERROR: Unable to acquire advisory lock: ' . $advisory_lock_id . ' Reason: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );
			$slf->Validator->isTrue( 'status_id',
									 false,
									 TTi18n::gettext( 'Unable to determine station at this time, please try again' ) );

			return $slf;
		}

		$slf->getByStationIdandCompanyId( $station_id, $company_id );
		if ( $slf->getRecordCount() == 1 ) {
			//Handle disabled station here, but only for KIOSK type stations.
			//As non-kiosk stations still need to be able to revert back to the wildcard ANY station and check that for access. Where KIOSK stations will not do that.
			if ( $slf->getCurrent()->getStatus() == 10 && in_array( $slf->getCurrent()->getType(), [ 61, 65 ] ) ) { //Disabled
				Debug::text( 'aStation is disabled...' . $station_id, __FILE__, __LINE__, __METHOD__, 10 );
				$slf->Validator->isTrue( 'status_id',
										 false,
										 TTi18n::gettext( 'Waiting for Administrator approval to activate this device' ) );

				$retval = $slf;
			} else if ( $slf->getCurrent()->getStatus() == 10 && in_array( $slf->getCurrent()->getType(), [ 28 ] ) ) {
				//Check isAllowed for any wildcard stations first...
				if ( $slf->getCurrent()->checkAllowed( $user_obj->getId(), $station_id, $type_id ) == true ) {
					$retval = $slf->getCurrent()->getStation();
				} else {
					Debug::text( 'bStation is disabled...' . $station_id, __FILE__, __LINE__, __METHOD__, 10 );
					$slf->Validator->isTrue( 'status_id',
											 false,
											 TTi18n::gettext( 'You are not authorized to punch in or out from this station!' ) );

					$retval = $slf;
				}
			} else {
				$retval = $slf->getCurrent()->getStation();
			}
		} else {
			Debug::text( 'Station ID: ' . $station_id . ' (Type: ' . $type_id . ') does not exist, creating new station', __FILE__, __LINE__, __METHOD__, 10 );

			//Insert new station
			$sf = TTnew( 'StationFactory' ); /** @var StationFactory $sf */
			$sf->setCompany( $company_id );
			$sf->setID( $sf->getNextInsertId() ); //This is required to call setIncludeUser() properly.
			$sf->setType( $type_id );             //Must come before the below switch() statement as things like setModeFlag() require the type.

			switch ( $type_id ) {
				case 10: //PC
				case 26: //Mobile Web Browser
					$status_id = 20; //20=Enabled, but will be set disabled automatically by isActiveForAnyEmployee()

					//Since more and more browsers are using "privacy" mode and clearing tracking cookies or just all cookies between restarts.
					// 	Switch to using a browser fingerprint instead of a cookie.
					//  This should be graceful transition, because it will only use fingerprinting when a cookie does not already exist and a new station is being created.
					//*NOTE: At the end of the day the browser station record is mostly to record the IP address of the browser.
					// 			   Its not actually that helpful outside of that, unless the customer has a firewall with a static IP, then additional fingerprinting is slightly useful.
					//             **See Misc::getBrowserFingerprint() comments for methods to fingerprint browsers indefinitely with an extension.
					//			   **We use https://github.com/thumbmarkjs/thumbmarkjs on the client side to try and generate an at least somewhat unique fingerprint, but fallback to server-side fingerprinting if the client-side fails.
					$station = ( $station_id != '' ) ? $station_id : Misc::getBrowserFingerprint( true ); //Using NULL means we generate our own.
					$description = substr( $_SERVER['HTTP_USER_AGENT'], 0, 250 );
					$source = Misc::getRemoteIPAddress();
					break;
				case 28: //Mobile App (iOS/Android)
				case 60: //Desktop App
					$status_id = 20; //20=Enabled
					if ( $station_id != '' ) {
						$station_id = str_replace( ':', '', $station_id ); //Some iOS6 devices return a MAC address looking ID. This conflicts with BASIC Authentication FCGI workaround as it uses ':' for the separator, see Global.inc.php for more details.

						//Prevent stations from having the type_id appended to the end several times.
						if ( substr( $station_id, ( strlen( $type_id ) * -1 ) ) != $type_id ) {
							$station = $station_id . $type_id;
						} else {
							$station = $station_id;
						}
					} else {
						$station = null; //Can't get UDID on iOS5, but we can on Android. Using NULL means we generate our own.
					}
					$description = TTi18n::getText( 'Mobile Application' ) . ': ' . substr( $_SERVER['HTTP_USER_AGENT'], 0, 250 );
					$source = Misc::getRemoteIPAddress();

					$sf->setPollFrequency( 600 );
					$sf->setEnableAutoPunchStatus( true );

					//If a customer wants to disable punching from all mobile phones (or any other setting), they would need to do that on the wildcard station
					//  then we have to propagate those settings when we create the specific station for each device.
					$allowed_station_id = $slf->checkAllowed( $user_obj->getId(), TTUUID::generateUUID(), $type_id, false, true ); //generateUUID() is a required argument in this case, it just falls back to wild cards anyways.
					if ( TTUUID::isUUID( $allowed_station_id ) ) {
						Debug::text( '  Found allowed wildcard Station ID: ' . $allowed_station_id . ', copying settings from it...', __FILE__, __LINE__, __METHOD__, 10 );
						$slf->getByIdandCompanyId( $allowed_station_id, $company_id );
						if ( $slf->getRecordCount() == 1 ) {
							$default_mode_flag = $slf->getCurrent()->getModeFlag();
						}
					}
					unset( $allowed_station_id );

					if ( isset( $default_mode_flag ) ) {
						$sf->setModeFlag( $default_mode_flag ); //Default
					} else {
						$sf->setModeFlag( [ 1 ] ); //Default
					}

					break;
				case 61: //Kiosk: Desktop
				case 65: //Kiosk: Mobile App (iOS/Android)
					//if ( DEMO_MODE == true || ( is_object( $user_obj ) && $user_obj->getPermissionLevel() >= 50 ) ) {
					if ( DEMO_MODE == true || ( is_object( $permission_obj ) && ( $permission_obj->Check( 'user', 'enroll' ) || $permission_obj->Check( 'user', 'enroll_child' ) || $permission_obj->Check( 'user', 'timeclock_admin' ) ) ) ) {
						$status_id = 20; //Always activate immediately when using demo, or its a supervisor.
					} else {
						$status_id = 10; //Initially create as disabled and admin must manually enable it.
					}
					Debug::Text( 'KIOSK station... Default Status ID: ' . $status_id, __FILE__, __LINE__, __METHOD__, 10 );


					$sf->setType( $type_id ); //Need to set thie before setModeFlag()

					if ( empty( $station_id ) == true ) {
						//If the station didn't pass in the UDID, generate our own Station ID, however this should never happen in the real-world.
						$station = null; //Using NULL means we generate our own.
						Debug::Text( 'ERROR: KIOSK station didnt pass in UDID of device, therefore forced to generate random Station ID. This should be fixed at the remote end!', __FILE__, __LINE__, __METHOD__, 10 );
					} else {
						//Use the passed in station_id, as it will be the UDID and contain the type_id on the end.
						//Add the type_id as the suffix to avoid conflicts if the user switches between kiosk and non-kiosk modes.
						//Prevent stations from having the type_id appended to the end several times.
						if ( substr( $station_id, ( strlen( $type_id ) * -1 ) ) != $type_id ) {
							$station = $station_id . $type_id;
						} else {
							$station = $station_id;
						}
					}

					if ( $description == '' ) {
						$description = TTi18n::getText( 'Automatic KIOSK Setup [Add name/location of device here]' );
					}

					if ( $status_id == 10 ) { //Disabled
						$description = TTi18n::getText( 'PENDING ACTIVATION' ) . ' - ' . $description;
					}

					$source = 'ANY';

					$sf->setPollFrequency( 600 );
					$sf->setEnableAutoPunchStatus( true );

					$sf->setGroupSelectionType( 10 );      //All allowed
					$sf->setBranchSelectionType( 10 );     //All allowed
					$sf->setDepartmentSelectionType( 10 ); //All allowed

					$sf->setModeFlag( [ 2, 4, 16, 2048, 4096, 8192, 2097152, 4194304, 8388608 ] ); //By default enable all punch modes, Capture Punch Images in KIOSK mode, Disable Screensaver, Disable GPS, Enable: Pre-Punch Message, Enable: Post-Punch Message, Enable: Exception Notice
					$sf->setDefaultModeFlag( 16 );                      //Facial Recognition.

					if ( is_object( $sf->getCompanyObject() ) && is_object( $sf->getCompanyObject()->getUserDefaultObject() ) ) {
						$sf->setTimeZone( $sf->getCompanyObject()->getUserDefaultObject()->getTimeZone() );
					} else {
						$sf->setTimeZone( TTUUID::getZeroID() ); //Don't use any default timezone, so it falls back to the users own timezone.
					}

					break;
			}

			//Since we change the station_id (add type_id) for KIOSK stations, check to see if the modified station_id exists and return it.
			if ( in_array( $type_id, [ 28, 60, 61, 65 ] ) ) {
				$slf->getByStationIdandCompanyId( $station, $company_id );
				if ( $slf->getRecordCount() == 1 ) {
					Debug::Text( 'Station already exists with modified station_id, returning that instead.', __FILE__, __LINE__, __METHOD__, 10 );

					$retval = $slf->getCurrent()->getStation();
				} else {
					Debug::Text( 'Station definitely does not exist, attempting to create it...', __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			if ( !isset( $retval ) ) {
				$sf->setStatus( $status_id );
				$sf->setDescription( $description );
				$sf->setStation( $station );
				$sf->setSource( $source );
				if ( $sf->isValid() ) {
					if ( $sf->Save( false, true ) ) {
						$retval = $sf->getStation();
					}
				} else {
					Debug::Text( 'Station is invalid, returning object...', __FILE__, __LINE__, __METHOD__, 10 );
					$retval = $sf;
				}
			}
		}

		$this->releaseAdvisoryLock( $advisory_lock_id ); //Release after the commit to avoid concurrent update failures in higher isolation levels.

		Debug::text( 'Returning StationID: ' . $station_id, __FILE__, __LINE__, __METHOD__, 10 );

		return $retval;
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
		// Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status_id',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}
		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type' )
			);
		}
		// Station ID
		if ( $this->getStation() != '' ) {
			if ( in_array( strtolower( $this->getStation() ), $this->getOptions( 'station_reserved_word' ) ) === false ) {
				$this->Validator->isLength( 'station_id',
											$this->getStation(),
											TTi18n::gettext( 'Incorrect Station ID length' ),
											2, 250
				);

				//Make sure StationID can't be a directory/file name, as it gets included in a log file name during setClientLog() and could potentially cause an exploit.
				$this->Validator->isRegEx( 'station_id',
										   $this->getStation(),
										   TTi18n::gettext( 'Station ID contains invalid characters' ),
										   $this->station_id_validator_regex );

				if ( $this->Validator->isError( 'station_id' ) == false ) {
					$this->Validator->isTrue( 'station_id',
											  $this->isUniqueStation( $this->getStation() ),
											  TTi18n::gettext( 'Station ID already exists' )
					);
				}
			}
		}
		// Source ID
		if ( in_array( strtolower( $this->getSource() ), $this->getOptions( 'source_reserved_word' ) ) === false ) {
			if ( $this->getSource() != null ) {
				$this->Validator->isLength( 'source',
											$this->getSource(),
											TTi18n::gettext( 'Incorrect Source ID length' ),
											2, 250
				);
			}
		}
		// Description
		if ( $this->Validator->getValidateOnly() == false ) {
			if ( $this->getDescription() == '' ) {
				$this->Validator->isTrue( 'description',
										  false,
										  TTi18n::gettext( 'Description must be specified' )
				);
			}
		}
		if ( $this->getDescription() != '' && $this->Validator->isError( 'description' ) == false ) {
			$this->Validator->isLength( 'description',
										$this->getDescription(),
										TTi18n::gettext( 'Description is too short or too long' ),
										0, 255
			);

			$this->Validator->isHTML( 'description',
									  $this->getDescription(),
									  TTi18n::gettext( 'Description contains invalid special characters' ),
			);
		}
		// Default Branch
		if ( $this->getDefaultBranch() !== false && $this->getDefaultBranch() != TTUUID::getZeroID() ) {
			$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
			$this->Validator->isResultSetWithRows( 'branch_id',
												   $blf->getByID( $this->getDefaultBranch() ),
												   TTi18n::gettext( 'Invalid Branch' )
			);
		}
		// Default Department
		if ( $this->getDefaultDepartment() !== false && $this->getDefaultDepartment() != TTUUID::getZeroID() ) {
			$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
			$this->Validator->isResultSetWithRows( 'department_id',
												   $dlf->getByID( $this->getDefaultDepartment() ),
												   TTi18n::gettext( 'Invalid Department' )
			);
		}

		if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
			// Default Job
			if ( $this->getDefaultJob() !== false && $this->getDefaultJob() != TTUUID::getZeroID() ) {
				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$jlf = TTnew( 'JobListFactory' ); /** @var JobListFactory $jlf */
					$this->Validator->isResultSetWithRows( 'job_id',
														   $jlf->getByID( $this->getDefaultJob() ),
														   TTi18n::gettext( 'Invalid Job' )
					);
				}
			}
			// Default Task
			if ( $this->getDefaultJobItem() !== false && $this->getDefaultJobItem() != TTUUID::getZeroID() ) {
				if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$jilf = TTnew( 'JobItemListFactory' ); /** @var JobItemListFactory $jilf */
					$this->Validator->isResultSetWithRows( 'job_item_id',
														   $jilf->getByID( $this->getDefaultJobItem() ),
														   TTi18n::gettext( 'Invalid Task' )
					);
				}
			}
			// Default Punch Tag
			if ( $this->getDefaultPunchTag() !== false && $this->getDefaultPunchTag() != '' && $this->getDefaultPunchTag() != TTUUID::getZeroID() ) {
				$ptlf = TTnew( 'PunchTagListFactory' ); /** @var PunchTagListFactory $ptlf */
				if ( is_array( $this->getDefaultPunchTag() ) ) {
					foreach ( $this->getDefaultPunchTag() as $punch_tag ) {
						$this->Validator->isResultSetWithRows( 'punch_tag_id',
															   $ptlf->getByID( $punch_tag ),
															   TTi18n::gettext( 'Invalid Punch Tag' )
						);
					}
				} else {
					$this->Validator->isResultSetWithRows( 'punch_tag_id',
														   $ptlf->getByID( $this->getDefaultPunchTag() ),
														   TTi18n::gettext( 'Invalid Punch Tag' )
					);
				}
			}
		}

		// Time Zone
		if ( $this->getTimeZone() !== false && $this->getTimeZone() != '' && $this->getTimeZone() != TTUUID::getZeroID() ) {
			$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
			$this->Validator->inArrayKey( 'time_zone',
										  $this->getTimeZone(),
										  TTi18n::gettext( 'Incorrect Time Zone' ),
										  Misc::trimSortPrefix( $upf->getOptions( 'time_zone' ) )
			);
		}
		// Group Selection Type
		if ( $this->getGroupSelectionType() !== false ) {
			$this->Validator->inArrayKey( 'user_group_selection_type',
										  $this->getGroupSelectionType(),
										  TTi18n::gettext( 'Incorrect Group Selection Type' ),
										  $this->getOptions( 'group_selection_type' )
			);
		}

		// Branch Selection Type
		if ( $this->getBranchSelectionType() !== false ) {
			$this->Validator->inArrayKey( 'branch_selection_type',
										  $this->getBranchSelectionType(),
										  TTi18n::gettext( 'Incorrect Branch Selection Type' ),
										  $this->getOptions( 'branch_selection_type' )
			);
		}

		// Department Selection Type
		if ( $this->getDepartmentSelectionType() !== false ) {
			$this->Validator->inArrayKey( 'department_selection_type',
										  $this->getDepartmentSelectionType(),
										  TTi18n::gettext( 'Incorrect Department Selection Type' ),
										  $this->getOptions( 'department_selection_type' )
			);
		}

		// Port
		if ( $this->getPort() != '' ) {
			$this->Validator->isNumeric( 'port',
										 $this->getPort(),
										 TTi18n::gettext( 'Incorrect port' )
			);
		}

		if ( $this->getUserName() != '' ) {
			// User Name
			$this->Validator->isLength( 'user_name',
										$this->getUserName(),
										TTi18n::gettext( 'Incorrect User Name length' ),
										0, 255
			);
		}
		if ( $this->getPassword() != '' ) {
			// Password
			$this->Validator->isLength( 'password',
										$this->getPassword(),
										TTi18n::gettext( 'Incorrect Password length' ),
										0, 255
			);
		}
		// Download Frequency
		if ( $this->getPollFrequency() !== false && $this->getPollFrequency() != '' ) {
			$this->Validator->inArrayKey( 'poll_frequency',
										  $this->getPollFrequency(),
										  TTi18n::gettext( 'Incorrect Download Frequency' ),
										  $this->getOptions( 'poll_frequency' )
			);
		}
		// Upload Frequency
		if ( $this->getPushFrequency() !== false && $this->getPushFrequency() != '' ) {
			$this->Validator->inArrayKey( 'push_frequency',
										  $this->getPushFrequency(),
										  TTi18n::gettext( 'Incorrect Upload Frequency' ),
										  $this->getOptions( 'push_frequency' )
			);
		}
		// Partial Upload Frequency
		if ( $this->getPartialPushFrequency() !== false && $this->getPartialPushFrequency() != '' ) {
			$this->Validator->inArrayKey( 'partial_push_frequency',
										  $this->getPartialPushFrequency(),
										  TTi18n::gettext( 'Incorrect Partial Upload Frequency' ),
										  $this->getOptions( 'push_frequency' )
			);
		}
		if ( $this->getGenericDataValue( 'mode_flag' ) != '' ) { //Need to check on the raw bitmask value.
			// Mode
			$this->Validator->isNumeric( 'mode_flag',
										 $this->getGenericDataValue( 'mode_flag' ),
										 TTi18n::gettext( 'Incorrect Mode' )
			);
		}
		if ( $this->getDefaultModeFlag() != '' ) {
			// Default Mode
			$this->Validator->isNumeric( 'default_mode_flag',
										 $this->getDefaultModeFlag(),
										 TTi18n::gettext( 'Incorrect Default Punch Mode' )
			);
		}

		if ( $this->getLastPunchTimeStamp() != '' ) {
			// last punch date
			$this->Validator->isDate( 'last_punch_time_stamp',
									  $this->getLastPunchTimeStamp(),
									  TTi18n::gettext( 'Incorrect last punch date' )
			);
		}

		if ( $this->getLastPollDate() != '' ) {
			// last poll date
			$this->Validator->isDate( 'last_poll_date',
									  $this->getLastPollDate(),
									  TTi18n::gettext( 'Incorrect last poll date' )
			);
		}
		if ( $this->getLastPollStatusMessage() != '' ) {
			// Status Message
			$this->Validator->isLength( 'last_poll_status_message',
										$this->getLastPollStatusMessage(),
										TTi18n::gettext( 'Incorrect Status Message length' ),
										0, 255
			);
		}
		if ( $this->getLastPushDate() != '' ) {
			// Last Push Date
			$this->Validator->isDate( 'last_push_date',
									  $this->getLastPushDate(),
									  TTi18n::gettext( 'Incorrect last push date' )
			);
		}
		if ( $this->getLastPushStatusMessage() != '' ) {
			// Status Message
			$this->Validator->isLength( 'last_push_status_message',
										$this->getLastPushStatusMessage(),
										TTi18n::gettext( 'Incorrect Status Message length' ),
										0, 255
			);
		}
		if ( $this->getLastPartialPushDate() != '' ) {
			// Last partial push date
			$this->Validator->isDate( 'last_partial_push_date',
									  $this->getLastPartialPushDate(),
									  TTi18n::gettext( 'Incorrect last partial push date' )
			);
		}
		if ( $this->getLastPartialPushStatusMessage() != '' ) {
			// Status Message
			$this->Validator->isLength( 'last_partial_push_status_message',
										$this->getLastPartialPushStatusMessage(),
										TTi18n::gettext( 'Incorrect Status Message length' ),
										0, 255
			);
		}
		// User Value 1
		if ( $this->getUserValue1() != '' ) {
			$this->Validator->isLength( 'user_value_1',
										$this->getUserValue1(),
										TTi18n::gettext( 'User Value 1 is invalid' ),
										1, 255
			);
		}
		// User Value 2
		if ( $this->getUserValue2() != '' ) {
			$this->Validator->isLength( 'user_value_2',
										$this->getUserValue2(),
										TTi18n::gettext( 'User Value 2 is invalid' ),
										1, 255
			);
		}
		// User Value 3
		if ( $this->getUserValue3() != '' ) {
			$this->Validator->isLength( 'user_value_3',
										$this->getUserValue3(),
										TTi18n::gettext( 'User Value 3 is invalid' ),
										1, 255
			);
		}
		// User Value 4
		if ( $this->getUserValue4() != '' ) {
			$this->Validator->isLength( 'user_value_4',
										$this->getUserValue4(),
										TTi18n::gettext( 'User Value 4 is invalid' ),
										1, 255
			);
		}
		// User Value 5
		if ( $this->getUserValue5() != '' ) {
			$this->Validator->isLength( 'user_value_5',
										$this->getUserValue5(),
										TTi18n::gettext( 'User Value 5 is invalid' ),
										1, 255
			);
		}
		if ( $this->getAllowedDate() != '' ) {
			// Allowed date
			$this->Validator->isDate( 'allowed_date',
									  $this->getAllowedDate(),
									  TTi18n::gettext( 'Incorrect allowed date' )
			);
		}
		//
		//
		// ABOVE: Validation code moved from set*() functions.
		//

		if ( is_object( $this->getCompanyObject() ) && $this->getCompanyObject()->getProductEdition() == 10 && $this->getType() > 10 ) {
			$this->Validator->isTrue( 'type_id',
									  false,
									  TTi18n::gettext( 'Type is not available in %1 Community Edition, please contact our sales department for more information', APPLICATION_NAME ) );
		}

		if ( $ignore_warning == false ) {
			if ( $this->getStatus() == 20 && $this->isActiveForAnyEmployee() == false ) {
				$this->Validator->Warning( 'group', TTi18n::gettext( 'Employee Criteria denies access to all employees, if you save this record it will be marked as DISABLED' ) );
			}
		}
																																																																						/* @formatter:off */ if ( $this->getDeleted() == false ) { $obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $obj_function = "\166\x61\154\x69\144\x61\164\145\114\x69\x63\145\x6e\x73\x65"; $obj_error_msg_function = "\x67\x65\x74\x46\x75\154\154\105\162\x72\x6f\x72\115\x65\x73\163\141\x67\x65"; @$obj = new $obj_class; $retval = $obj->{$obj_function}(NULL, array("\x73\x74\x61\x74\x69\x6f\x6e" => $this)); if ( $retval !== TRUE ) { $this->Validator->isTrue( 'status_id', FALSE, $obj->{$obj_error_msg_function}($retval) ); } } /* @formatter:on */
		return TRUE;
	}

	/**
	 * Check to see if this station is active for any employees, if not, we may as well mark it as disabled to speed up queries.
	 * @return bool
	 */
	function isActiveForAnyEmployee() {
		if (
				( $this->getGroupSelectionType() == 20 && $this->getGroup() === false )
				&&
				( $this->getBranchSelectionType() == 20 && $this->getBranch() === false )
				&&
				( $this->getDepartmentSelectionType() == 20 && $this->getDepartment() === false )
				&&
				( $this->getIncludeUser() === false )
		) {
			Debug::text( 'Station is not active for any employees, everyone is denied.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		Debug::text( 'Station IS active for at least some employees...', __FILE__, __LINE__, __METHOD__, 10 );

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		//New stations are deny all by default, so if they haven't
		//set the selection types, default them to only selected, so
		//everyone is denied, because none are selected.
		if ( $this->getGroupSelectionType() == false ) {
			$this->setGroupSelectionType( 20 ); //Only selected.
		}
		if ( $this->getBranchSelectionType() == false ) {
			$this->setBranchSelectionType( 20 ); //Only selected.
		}
		if ( $this->getDepartmentSelectionType() == false ) {
			$this->setDepartmentSelectionType( 20 ); //Only selected.
		}

		if ( $this->getStatus() == 20 && $this->isActiveForAnyEmployee() == false ) {
			$this->setStatus( 10 ); //Disabled
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getStation() );
		$this->removeCache( 'station_source_dns_' . $this->getCompany() . $this->getSource() ); //Clear DNS cache.

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
				if ( isset( $data[ $key ] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						case 'last_punch_time_stamp':
						case 'last_poll_date':
						case 'last_push_date':
						case 'last_partial_push_date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[ $key ] ) );
							}
							break;
						case 'group':
							$this->setGroup( $data[ $key ] );
							break;
						case 'branch':
							$this->setBranch( $data[ $key ] );
							break;
						case 'department':
							$this->setDepartment( $data[ $key ] );
							break;
						case 'include_user':
							$this->setIncludeUser( $data[ $key ] );
							break;
						case 'exclude_user':
							$this->setExcludeUser( $data[ $key ] );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[ $key ] );
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
		$data = array();
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[ $variable ] ) && $include_columns[ $variable ] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'status':
						case 'type':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'last_punch_time_stamp':
						case 'last_poll_date':
						case 'last_push_date':
						case 'last_partial_push_date':
							$data[ $variable ] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() );
							break;
						case 'group':
							$data[ $variable ] = $this->getGroup();
							break;
						case 'branch':
							$data[ $variable ] = $this->getBranch();
							break;
						case 'department':
							$data[ $variable ] = $this->getDepartment();
							break;
						case 'include_user':
							$data[ $variable ] = $this->getIncludeUser();
							break;
						case 'exclude_user':
							$data[ $variable ] = $this->getExcludeUser();
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[ $variable ] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getID(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		if ( !( $log_action == 10 && $this->getType() == 10 ) ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Station' ), null, $this->getTable(), $this );
		}

		return false;
	}
}

?>
