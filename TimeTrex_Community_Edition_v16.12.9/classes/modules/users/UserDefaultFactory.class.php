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
class UserDefaultFactory extends Factory {
	protected $table = 'user_default';
	protected $pk_sequence_name = 'user_default_id_seq'; //PK Sequence name

	protected $company_obj = null;
	protected $title_obj = null;

	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'()\[\]#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'pay_period_schedule_id' )->setFunctionMap( 'PayPeriodSchedule' )->setType( 'uuid' ),
							TTSCol::new( 'policy_group_id' )->setFunctionMap( 'PolicyGroup' )->setType( 'uuid' ),
							TTSCol::new( 'employee_number' )->setFunctionMap( 'EmployeeNumber' )->setType( 'varchar' ),
							TTSCol::new( 'city' )->setFunctionMap( 'City' )->setType( 'varchar' ),
							TTSCol::new( 'province' )->setFunctionMap( 'Province' )->setType( 'varchar' ),
							TTSCol::new( 'country' )->setFunctionMap( 'Country' )->setType( 'varchar' ),
							TTSCol::new( 'work_email' )->setFunctionMap( 'WorkEmail' )->setType( 'varchar' ),
							TTSCol::new( 'work_phone' )->setFunctionMap( 'WorkPhone' )->setType( 'varchar' ),
							TTSCol::new( 'work_phone_ext' )->setFunctionMap( 'WorkPhoneExt' )->setType( 'varchar' ),
							TTSCol::new( 'hire_date' )->setFunctionMap( 'HireDate' )->setType( 'integer' ),
							TTSCol::new( 'title_id' )->setFunctionMap( 'Title' )->setType( 'uuid' ),
							TTSCol::new( 'default_branch_id' )->setFunctionMap( 'DefaultBranch' )->setType( 'uuid' ),
							TTSCol::new( 'default_department_id' )->setFunctionMap( 'DefaultDepartment' )->setType( 'uuid' ),
							TTSCol::new( 'date_format' )->setFunctionMap( 'DateFormat' )->setType( 'varchar' ),
							TTSCol::new( 'time_format' )->setFunctionMap( 'TimeFormat' )->setType( 'varchar' ),
							TTSCol::new( 'time_unit_format' )->setFunctionMap( 'TimeUnitFormat' )->setType( 'varchar' ),
							TTSCol::new( 'time_zone' )->setFunctionMap( 'TimeZone' )->setType( 'varchar' ),
							TTSCol::new( 'items_per_page' )->setFunctionMap( 'ItemsPerPage' )->setType( 'integer' ),
							TTSCol::new( 'start_week_day' )->setFunctionMap( 'StartWeekDay' )->setType( 'integer' ),
							TTSCol::new( 'language' )->setFunctionMap( 'Language' )->setType( 'varchar' ),
							TTSCol::new( 'currency_id' )->setFunctionMap( 'Currency' )->setType( 'uuid' ),
							TTSCol::new( 'permission_control_id' )->setFunctionMap( 'PermissionControl' )->setType( 'uuid' ),
							TTSCol::new( 'enable_email_notification_exception' )->setFunctionMap( 'EnableEmailNotificationException' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_email_notification_message' )->setFunctionMap( 'EnableEmailNotificationMessage' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_email_notification_home' )->setFunctionMap( 'EnableEmailNotificationHome' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_email_notification_pay_stub' )->setFunctionMap( 'EnableEmailNotificationPayStub' )->setType( 'smallint' ),
							TTSCol::new( 'distance_format' )->setFunctionMap( 'DistanceFormat' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'legal_entity_id' )->setFunctionMap( 'LegalEntity' )->setType( 'uuid' ),
							TTSCol::new( 'terminated_permission_control_id' )->setFunctionMap( 'TerminatedPermissionControl' )->setType( 'uuid' ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'hierarchy_control' )->setFunctionMap( 'HierarchyControl' )->setType( 'json' ),
							TTSCol::new( 'recurring_schedule' )->setFunctionMap( 'RecurringSchedule' )->setType( 'json' ),
							TTSCol::new( 'display_order' )->setFunctionMap( 'DisplayOrder' )->setType( 'integer' ),
							TTSCol::new( 'enable_time_zone_auto_detect' )->setFunctionMap( 'EnableTimeZoneAutoDetect' )->setType( 'smallint' )->setIsNull( false )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_new_hire_default' )->setLabel( TTi18n::getText( 'New Hire Defaults' ) )->setFields(
									new TTSFields(
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) ),
											TTSField::new( 'display_order' )->setType( 'text' )->setLabel( TTi18n::getText( 'Display Order' ) ),
											TTSField::new( 'created_by_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Created By' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'legal_entity_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Legal Entity' ) )->setDataSource( TTSAPI::new( 'APILegalEntity' )->setMethod( 'getLegalEntity' ) ),
											TTSField::new( 'permission_control_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Permission Group' ) )->setDataSource( TTSAPI::new( 'APIPermissionControl' )->setMethod( 'getPermissionControl' ) ),
											TTSField::new( 'terminated_permission_control_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Terminated Permission Group' ) )->setDataSource( TTSAPI::new( 'APIPermissionControl' )->setMethod( 'getPermissionControl' ) ),
											TTSField::new( 'pay_period_schedule_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Pay Period Schedule' ) )->setDataSource( TTSAPI::new( 'APIPayPeriodSchedule' )->setMethod( 'getPayPeriodSchedule' ) ),
											TTSField::new( 'policy_group_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Policy Group' ) )->setDataSource( TTSAPI::new( 'APIPolicyGroup' )->setMethod( 'getPolicyGroup' ) ),
											TTSField::new( 'recurring_schedule' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Recurring Schedule' ) )->setDataSource( TTSAPI::new( 'APIRecurringSchedule' )->setMethod( 'getRecurringSchedule' ) ),
											TTSField::new( 'currency_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Currency' ) )->setDataSource( TTSAPI::new( 'APICurrency' )->setMethod( 'getCurrency' ) ),
											TTSField::new( 'title_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Title' ) )->setDataSource( TTSAPI::new( 'APIUserTitle' )->setMethod( 'getUserTitle' ) ),
											TTSField::new( 'default_branch_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Default Branch' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) ),
											TTSField::new( 'default_department_id' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Default Department' ) )->setDataSource( TTSAPI::new( 'APIDepartment' )->setMethod( 'getDepartment' ) ),
									)
							),
							TTSTab::new( 'tab_contact_info' )->setLabel( TTi18n::getText( 'Contact Information' ) )->setFields(
									new TTSFields(
											TTSField::new( 'city' )->setType( 'text' )->setLabel( TTi18n::getText( 'City' ) ),
											TTSField::new( 'country' )->setType( 'text' )->setLabel( TTi18n::getText( 'Country' ) ),
											TTSField::new( 'province' )->setType( 'text' )->setLabel( TTi18n::getText( 'Province / State' ) ),
											TTSField::new( 'work_phone' )->setType( 'text' )->setLabel( TTi18n::getText( 'Work Phone' ) ),
											TTSField::new( 'work_phone_ext' )->setType( 'text' )->setLabel( TTi18n::getText( 'Work Phone Ext' ) ),
											TTSField::new( 'work_email' )->setType( 'text' )->setLabel( TTi18n::getText( 'Work Email' ) ),
									)
							),
							TTSTab::new( 'tab_hierarchy' )->setLabel( TTi18n::getText( 'Hierarchy' ) )->setHTMLTemplate( '<div id="tab_hierarchy" class="edit-view-tab-outside">\n\t\t\t\t\t<div class="edit-view-tab" id="tab_hierarchy_content_div">\n\t\t\t\t\t\t<div class="first-column full-width-column"></div>\n\t\t\t\t\t\t<div class="hierarchy-div">\n\t\t\t\t\t\t\t<span class="message"></span>\n\t\t\t\t\t\t\t<div class="save-and-continue-button-div">\n\t\t\t\t\t\t\t\t<button class="tt-button p-button p-component" type="button">\n\t\t\t\t\t\t\t\t\t<span class="icon"></span>\n\t\t\t\t\t\t\t\t\t<span class="p-button-label"></span>\n\t\t\t\t\t\t\t\t</button>\n\t\t\t\t\t\t\t</div>\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>' ),
							TTSTab::new( 'tab_tax_deduction' )->setLabel( TTi18n::getText( 'Taxes & Deductions' ) )->setFields(
									new TTSFields(
											TTSField::new( 'company_deduction' )->setType( 'text' )->setLabel( TTi18n::getText( 'Tax & Deductions' ) ),
									)
							),
							TTSTab::new( 'tab_employee_preference' )->setLabel( TTi18n::getText( 'Preferences' ) )->setFields(
									new TTSFields(
											TTSField::new( 'language' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Language' ) ),
											TTSField::new( 'date_format' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Date Format' ) ),
											TTSField::new( 'time_format' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Time Format' ) ),
											TTSField::new( 'time_unit_format' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Time Units' ) ),
											TTSField::new( 'distance_format' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Distance Units' ) ),
											TTSField::new( 'time_zone' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Time Zone' ) ),
											TTSField::new( 'enable_time_zone_auto_detect' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Enable Time Zone Auto-Detect' ) ),
											TTSField::new( 'start_week_day' )->setType( 'single-dropdown' )->setLabel( TTi18n::getText( 'Calendar Starts On' ) ),
											TTSField::new( 'items_per_page' )->setType( 'text' )->setLabel( TTi18n::getText( 'Rows per page' ) ),
									)
							),
							TTSTab::new( 'tab_preferences_notification' )->setLabel( TTi18n::getText( 'Notifications' ) )->setHTMLTemplate( '<div id="tab_preferences_notification" class="edit-view-tab-outside">\n\t\t\t\t\t<div class="edit-view-tab" id="tab_preferences_notification_content_div">\n\t\t\t\t\t\t<div class="first-column full-width-column"></div>\n\t\t\t\t\t\t<div class="inside-editor-div full-width-column"></div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>' ),
					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'a.country' ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'a.province' ),
							TTSSearchField::new( 'city' )->setType( 'text' )->setColumn( 'a.city' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserDefault' )->setMethod( 'getUserDefault' )
									->setSummary( 'Get user default records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserDefault' )->setMethod( 'setUserDefault' )
									->setSummary( 'Add or edit user default records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserDefault' )->setMethod( 'deleteUserDefault' )
									->setSummary( 'Delete user default records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserDefault' )->setMethod( 'getUserDefault' ) ),
											   ) ),
							TTSAPI::new( 'APIUserDefault' )->setMethod( 'getUserDefaultDefaultData' )
									->setSummary( 'Get default user default data used for creating new user defaults. Use this before calling setUserDefault to get the correct default data.' ),
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
						'-1080-name'                          => TTi18n::gettext( 'Name' ),
						'-1100-display_order'                 => TTi18n::gettext( 'Display Order' ),
						//'-1108-permission_control'            => TTi18n::gettext( 'Permission Group' ),
						//'-1109-terminated_permission_control' => TTi18n::gettext( 'Terminated Permission Group' ),
						//'-1110-pay_period_schedule'           => TTi18n::gettext( 'Pay Period Schedule' ),
						//'-1112-policy_group'                  => TTi18n::gettext( 'Policy Group' ),
						'-1150-city'                          => TTi18n::gettext( 'City' ),
						'-1160-province'                      => TTi18n::gettext( 'Province/State' ),
						'-1170-country'                       => TTi18n::gettext( 'Country' ),
						'-2000-created_by'                    => TTi18n::gettext( 'Created By' ),
						'-2010-created_date'                  => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'                    => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date'                  => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'name',
						'display_order',
						'city',
						'province',
						'country',
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
				'id'                                  => 'ID',
				'company_id'                          => 'Company',
				'name'                                => 'Name',
				'display_order'                       => 'DisplayOrder',
				'legal_entity_id'                     => 'LegalEntity',
				'permission_control_id'               => 'PermissionControl',
				'terminated_permission_control_id'    => 'TerminatedPermissionControl',
				'pay_period_schedule_id'              => 'PayPeriodSchedule',
				'policy_group_id'                     => 'PolicyGroup',
				'employee_number'                     => 'EmployeeNumber',
				'title_id'                            => 'Title',
				'default_branch_id'                   => 'DefaultBranch',
				'default_department_id'               => 'DefaultDepartment',
				'currency_id'                         => 'Currency',
				'city'                                => 'City',
				'country'                             => 'Country',
				'province'                            => 'Province',
				'work_phone'                          => 'WorkPhone',
				'work_phone_ext'                      => 'WorkPhoneExt',
				'work_email'                          => 'WorkEmail',
				'hire_date'                           => 'HireDate',
				'language'                            => 'Language',
				'date_format'                         => 'DateFormat',
				'time_format'                         => 'TimeFormat',
				'time_zone'                           => 'TimeZone',
				'enable_time_zone_auto_detect'        => 'EnableTimeZoneAutoDetect',
				'time_unit_format'                    => 'TimeUnitFormat',
				'distance_format'                     => 'DistanceFormat',
				'items_per_page'                      => 'ItemsPerPage',
				'start_week_day'                      => 'StartWeekDay',
				'enable_email_notification_exception' => 'EnableEmailNotificationException',
				'enable_email_notification_message'   => 'EnableEmailNotificationMessage',
				'enable_email_notification_pay_stub'  => 'EnableEmailNotificationPayStub',
				'enable_email_notification_home'      => 'EnableEmailNotificationHome',
				'company_deduction'                   => 'CompanyDeduction',
				'hierarchy_control'                   => 'HierarchyControl',
				'recurring_schedule'                  => 'RecurringSchedule',
				'created_by'                          => 'CreatedBy', //Needed to change the "owner" of the template for permission purposes.
				'deleted'                             => 'Deleted',
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
	function getTitleObject() {
		return $this->getGenericObject( 'UserTitleListFactory', $this->getTitle(), 'title_obj' );
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
	function getLegalEntity() {
		return $this->getGenericDataValue( 'legal_entity_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setLegalEntity( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Legal Entity ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'legal_entity_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPermissionControl() {
		return $this->getGenericDataValue( 'permission_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPermissionControl( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'permission_control_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTerminatedPermissionControl() {
		return $this->getGenericDataValue( 'terminated_permission_control_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setTerminatedPermissionControl( $value ) {
		return $this->setGenericDataValue( 'terminated_permission_control_id', TTUUID::castUUID( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getPayPeriodSchedule() {
		return $this->getGenericDataValue( 'pay_period_schedule_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPayPeriodSchedule( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'pay_period_schedule_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getPolicyGroup() {
		return $this->getGenericDataValue( 'policy_group_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setPolicyGroup( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'policy_group_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getEmployeeNumber() {
		return $this->getGenericDataValue( 'employee_number' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEmployeeNumber( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'employee_number', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getTitle() {
		return $this->getGenericDataValue( 'title_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setTitle( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Title ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'title_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultBranch() {
		return $this->getGenericDataValue( 'default_branch_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultBranch( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Branch ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'default_branch_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDefaultDepartment() {
		return $this->getGenericDataValue( 'default_department_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setDefaultDepartment( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Department ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'default_department_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCurrency() {
		return $this->getGenericDataValue( 'currency_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setCurrency( $value ) {
		$value = TTUUID::castUUID( $value );
		Debug::Text( 'Currency ID: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'currency_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCity() {
		return $this->getGenericDataValue( 'city' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCity( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'city', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCountry() {
		return $this->getGenericDataValue( 'country' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCountry( $value ) {
		return $this->setGenericDataValue( 'country', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getProvince() {
		return $this->getGenericDataValue( 'province' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setProvince( $value ) {
		Debug::Text( 'Country: ' . $this->getCountry() . ' Province: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		//If country isn't set yet, accept the value and re-validate on save.
		return $this->setGenericDataValue( 'province', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getWorkPhone() {
		return $this->getGenericDataValue( 'work_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkPhone( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'work_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getWorkPhoneExt() {
		return $this->getGenericDataValue( 'work_phone_ext' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkPhoneExt( $value ) {
		$value = $this->Validator->stripNonNumeric( trim( $value ) );

		return $this->setGenericDataValue( 'work_phone_ext', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getWorkEmail() {
		return $this->getGenericDataValue( 'work_email' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWorkEmail( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'work_email', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getHireDate() {
		return $this->getGenericDataValue( 'hire_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setHireDate( $value ) {
		return $this->setGenericDataValue( 'hire_date', $value );
	}

	/*

		User Preferences

	*/
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

		return $this->setGenericDataValue( 'date_format', $value );
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
		$value = trim( $value );

		return $this->setGenericDataValue( 'items_per_page', $value );
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
	 * @return bool
	 */
	function getEnableTimeZoneAutoDetect() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_time_zone_auto_detect' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableTimeZoneAutoDetect( $value ) {
		return $this->setGenericDataValue( 'enable_time_zone_auto_detect', $this->toBool( $value ) );
	}


	/*

		Company Deductions

	*/
	/**
	 * @return array|bool
	 */
	function getCompanyDeduction() {
		$udcdlf = TTnew( 'UserDefaultCompanyDeductionListFactory' ); /** @var UserDefaultCompanyDeductionListFactory $udcdlf */
		$udcdlf->getByUserDefaultId( $this->getId() );

		$list = [];
		foreach ( $udcdlf as $obj ) {
			$list[] = $obj->getCompanyDeduction();
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
	function setCompanyDeduction( $ids ) {
		Debug::text( 'Setting Company Deduction IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		if ( $ids == '' ) {
			$ids = []; //This is for the API, it sends FALSE when no branches are selected, so this will delete all branches.
		}

		if ( is_array( $ids ) ) {
			$tmp_ids = [];
			if ( !$this->isNew() ) {
				//If needed, delete mappings first.
				$udcdlf = TTnew( 'UserDefaultCompanyDeductionListFactory' ); /** @var UserDefaultCompanyDeductionListFactory $udcdlf */
				$udcdlf->getByUserDefaultId( $this->getId() );
				foreach ( $udcdlf as $obj ) {
					$id = $obj->getCompanyDeduction();
					Debug::text( 'ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

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
			//$lf = TTnew( 'UserListFactory' );
			$cdlf = TTnew( 'CompanyDeductionListFactory' ); /** @var CompanyDeductionListFactory $cdlf */

			foreach ( $ids as $id ) {
				if ( $id != false && isset( $ids ) && !in_array( $id, $tmp_ids ) ) {
					$udcdf = TTnew( 'UserDefaultCompanyDeductionFactory' ); /** @var UserDefaultCompanyDeductionFactory $udcdf */
					$udcdf->setUserDefault( $this->getId() );
					$udcdf->setCompanyDeduction( $id );

					$obj = $cdlf->getById( $id )->getCurrent();

					if ( $this->Validator->isTrue( 'company_deduction',
												   $udcdf->isValid(),
												   TTi18n::gettext( 'Deduction is invalid' ) . ' (' . $obj->getName() . ')' ) ) {
						$udcdf->save();
					}
				}
			}

			return true;
		}

		Debug::text( 'No IDs to set.', __FILE__, __LINE__, __METHOD__, 10 );

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
	function getHierarchyControl() {
		return json_decode( $this->getGenericDataValue( 'hierarchy_control' ), true );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHierarchyControl( $value ) {
		return $this->setGenericDataValue( 'hierarchy_control', json_encode( $value ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getRecurringSchedule() {
		return json_decode( $this->getGenericDataValue( 'recurring_schedule' ), true );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRecurringSchedule( $value ) {
		return $this->setGenericDataValue( 'recurring_schedule', json_encode( $value ) );
	}

	/**
	 * @return int
	 */
	function getDisplayOrder() {
		return $this->getGenericDataValue( 'display_order' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDisplayOrder( $value ) {
		$value = $this->Validator->stripNonNumeric( trim( $value ) );

		return $this->setGenericDataValue( 'display_order', $value );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		Debug::Arr( $this->getCompany(), 'Company: ', __FILE__, __LINE__, __METHOD__, 10 );
		if ( $this->getCompany() == false ) {
			return false;
		}

		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		$ph = [
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
				'name'       => TTi18n::strtolower( $name ),
		];

		$query = 'select id from ' . $this->getTable() . '
					where company_id = ?
						AND lower(name) = ?
						AND deleted = 0';
		$name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $name_id === false ) {
			return true;
		} else {
			if ( $name_id == $this->getId() ) {
				return true;
			}
		}

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
		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Legal entity
		$lelf = TTnew( 'LegalEntityListFactory' ); /** @var LegalEntityListFactory $clf */
		$this->Validator->isResultSetWithRows( 'legal_entity_id',
											   $lelf->getByID( $this->getLegalEntity() ),
											   TTi18n::gettext( 'Legal entity is invalid' )
		);
		// Permission Group
		if ( $this->getPermissionControl() != '' && $this->getPermissionControl() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'permission_control_id',
												   $pclf->getByID( $this->getPermissionControl() ),
												   TTi18n::gettext( 'Permission Group is invalid' )
			);
		}

		// Termianted Permission Group
		if ( $this->getTerminatedPermissionControl() != '' && $this->getTerminatedPermissionControl() != TTUUID::getZeroID() ) {
			$pclf = TTnew( 'PermissionControlListFactory' ); /** @var PermissionControlListFactory $pclf */
			$this->Validator->isResultSetWithRows( 'terminated_permission_control_id',
												   $pclf->getByID( $this->getTerminatedPermissionControl() ),
												   TTi18n::gettext( 'Terminated Permission Group is invalid' )
			);
		}

		// Pay Period schedule
		if ( $this->getPayPeriodSchedule() != '' && $this->getPayPeriodSchedule() != TTUUID::getZeroID() ) {
			$ppslf = TTnew( 'PayPeriodScheduleListFactory' ); /** @var PayPeriodScheduleListFactory $ppslf */
			$this->Validator->isResultSetWithRows( 'pay_period_schedule_id',
												   $ppslf->getByID( $this->getPayPeriodSchedule() ),
												   TTi18n::gettext( 'Pay Period schedule is invalid' )
			);
		}
		// Policy Group
		if ( $this->getPolicyGroup() != '' && $this->getPolicyGroup() != TTUUID::getZeroID() ) {
			$pglf = TTnew( 'PolicyGroupListFactory' ); /** @var PolicyGroupListFactory $pglf */
			$this->Validator->isResultSetWithRows( 'policy_group_id',
												   $pglf->getByID( $this->getPolicyGroup() ),
												   TTi18n::gettext( 'Policy Group is invalid' )
			);
		}
		// Employee number
		if ( $this->getEmployeeNumber() != '' ) {
			$this->Validator->isLength( 'employee_number',
										$this->getEmployeeNumber(),
										TTi18n::gettext( 'Employee number must be less than 18 digits' ), //Should fit within 64bit integer.
										1,
										18
			);
			if ( $this->Validator->isError( 'employee_number' ) == false ) {
				$this->Validator->isTrue( 'employee_number',
										  ( (int)$this->getEmployeeNumber() !== 0 && $this->Validator->stripNon64bitInteger( $this->getEmployeeNumber() ) === 0 ) ? false : true,
										  TTi18n::gettext( 'Employee number is invalid, maximum value exceeded' )
				);
			}
		}
		// Title
		if ( $this->getTitle() != '' && $this->getTitle() != TTUUID::getZeroID() ) {
			$utlf = TTnew( 'UserTitleListFactory' ); /** @var UserTitleListFactory $utlf */
			$this->Validator->isResultSetWithRows( 'title',
												   $utlf->getByID( $this->getTitle() ),
												   TTi18n::gettext( 'Title is invalid' )
			);
		}
		// Default Branch
		if ( $this->getDefaultBranch() != '' && $this->getDefaultBranch() != TTUUID::getZeroID() ) {
			$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
			$this->Validator->isResultSetWithRows( 'default_branch',
												   $blf->getByID( $this->getDefaultBranch() ),
												   TTi18n::gettext( 'Invalid Default Branch' )
			);
		}
		// Default Department
		if ( $this->getDefaultDepartment() != '' && $this->getDefaultDepartment() != TTUUID::getZeroID() ) {
			$dlf = TTnew( 'DepartmentListFactory' ); /** @var DepartmentListFactory $dlf */
			$this->Validator->isResultSetWithRows( 'default_department',
												   $dlf->getByID( $this->getDefaultDepartment() ),
												   TTi18n::gettext( 'Invalid Default Department' )
			);
		}
		// Currency
		if ( $this->getCurrency() != '' && $this->getCurrency() != TTUUID::getZeroID() ) {
			$culf = TTnew( 'CurrencyListFactory' ); /** @var CurrencyListFactory $culf */
			$this->Validator->isResultSetWithRows( 'currency',
												   $culf->getByID( $this->getCurrency() ),
												   TTi18n::gettext( 'Invalid Currency' )
			);
		}
		// City
		if ( $this->getCity() != '' ) {
			$this->Validator->isRegEx( 'city',
									   $this->getCity(),
									   TTi18n::gettext( 'City contains invalid characters' ),
									   $this->city_validator_regex
			);
			if ( $this->Validator->isError( 'city' ) == false ) {
				$this->Validator->isLength( 'city',
											$this->getCity(),
											TTi18n::gettext( 'City name is too short or too long' ),
											2,
											250
				);
			}
		}

		// Display order
		if ( $this->getDisplayOrder() !== false ) {
			$this->Validator->isNumeric( 'display_order',
										 $this->getDisplayOrder(),
										 TTi18n::gettext( 'Display order is invalid' )
			);
		}

		if ( $this->getDisplayOrder() == '' || $this->getDisplayOrder() < 0 || $this->getDisplayOrder() > 1000000 ) {
			$this->Validator->isTrue( 'display_order',
									  false,
									  TTi18n::gettext( 'Display Order must be between 0 and 1,000,000' )
			);
		}


		//Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is too short or too long' ),
									2,
									250
		);

		$this->Validator->isHTML( 'name',
								  $this->getName(),
								  TTi18n::gettext( 'Name contains invalid special characters' ),
		);

		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'New Hire Default name already exists' ) );
		}

		// Country
		$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
		$this->Validator->inArrayKey( 'country',
									  $this->getCountry(),
									  TTi18n::gettext( 'Invalid Country' ),
									  $cf->getOptions( 'country' )
		);
		// Province/State
		if ( $this->getCountry() !== false ) {
			$options_arr = $cf->getOptions( 'province' );
			if ( isset( $options_arr[$this->getCountry()] ) ) {
				$options = $options_arr[$this->getCountry()];
			} else {
				$options = [];
			}
			$this->Validator->inArrayKey( 'province',
										  $this->getProvince(),
										  TTi18n::gettext( 'Invalid Province/State' ),
										  $options
			);
		}
		// Work phone
		if ( $this->getWorkPhone() != '' ) {
			$this->Validator->isPhoneNumber( 'work_phone',
											 $this->getWorkPhone(),
											 TTi18n::gettext( 'Work phone number is invalid' )
			);
		}
		// Work phone number extension
		if ( $this->getWorkPhoneExt() != '' ) {
			$this->Validator->isLength( 'work_phone_ext',
										$this->getWorkPhoneExt(),
										TTi18n::gettext( 'Work phone number extension is too short or too long' ),
										2,
										10
			);
		}
		// Work Email address
		if ( $this->getWorkEmail() != '' ) {
			$this->Validator->isEmail( 'work_email',
									   $this->getWorkEmail(),
									   TTi18n::gettext( 'Work Email address is invalid' )
			);
		}
		// Hire date
		if ( $this->getHireDate() != '' ) {
			$this->Validator->isDate( 'hire_date',
									  $this->getHireDate(),
									  TTi18n::gettext( 'Hire date is invalid' )
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
		$upf = TTnew( 'UserPreferenceFactory' ); /** @var UserPreferenceFactory $upf */
		$this->Validator->inArrayKey( 'date_format',
									  $this->getDateFormat(),
									  TTi18n::gettext( 'Incorrect date format' ),
									  Misc::trimSortPrefix( $upf->getOptions( 'date_format' ) )
		);
		// Time format
		$this->Validator->inArrayKey( 'time_format',
									  $this->getTimeFormat(),
									  TTi18n::gettext( 'Incorrect time format' ),
									  $upf->getOptions( 'time_format' )
		);
		// Time zone
		$this->Validator->inArrayKey( 'time_zone',
									  $this->getTimeZone(),
									  TTi18n::gettext( 'Incorrect time zone' ),
									  Misc::trimSortPrefix( $upf->getOptions( 'time_zone' ) )
		);
		// time units
		$this->Validator->inArrayKey( 'time_unit_format',
									  $this->getTimeUnitFormat(),
									  TTi18n::gettext( 'Incorrect time units' ),
									  $upf->getOptions( 'time_unit_format' )
		);
		// Distance units
		$this->Validator->inArrayKey( 'distance_format',
									  $this->getDistanceFormat(),
									  TTi18n::gettext( 'Incorrect distance units' ),
									  $upf->getOptions( 'distance_format' )
		);
		// Items per page
		$min = ( PRODUCTION == false ) ? 1 : 5; //Allow lower numbers to help with testing.
		if ( $this->getItemsPerPage() == '' || $this->getItemsPerPage() < $min || $this->getItemsPerPage() > 2000 ) {
			$this->Validator->isTrue( 'items_per_page',
									  false,
									  TTi18n::gettext( 'Rows per page must be between %1 and %2', [ $min, 2000 ] )
			);
		}
		// Day to start a week on
		$this->Validator->inArrayKey( 'start_week_day',
									  $this->getStartWeekDay(),
									  TTi18n::gettext( 'Incorrect day to start a week on' ),
									  $upf->getOptions( 'start_week_day' )
		);

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->getCompany() == false ) {
			$this->Validator->isTrue( 'company',
									  false,
									  TTi18n::gettext( 'Company is invalid' ) );
		}

		if ( $this->isNew( true ) == true && Misc::getCurrentCompanyProductEdition() == TT_PRODUCT_COMMUNITY ) {
			$udlf = TTnew( 'UserDefaultListFactory' ); /** @var UserDefaultListFactory $udlf */
			$udlf->getByCompanyId( $this->getCompany(), 1 );
			if ( $udlf->getRecordCount() >= 1 ) {
				$this->Validator->isTrue( 'name',
										  false,
										  TTi18n::gettext( 'Multiple new hire default templates are only available in Professional, Corporate or Enterprise Editions.' ) );
			}
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		if ( $this->getDeleted() == true ) {
			//Delete any data from related child tables.
			$udpnlf = TTnew( 'UserDefaultPreferenceNotificationListFactory' ); /** @var UserDefaultPreferenceNotificationListFactory $udpnlf */
			$udpnlf->getByUserDefaultId( $this->getId() );
			if ( $udpnlf->getRecordCount() > 0 ) {
				foreach ( $udpnlf as $udpn_obj ) { /** @var UserDefaultPreferenceNotificationFactory $udpn_obj */
					$udpn_obj->setDeleted( true );

					if ( $udpn_obj->isValid() ) {
						$udpn_obj->Save();
					}
				}
			}
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
						case 'hire_date':
							$this->setHireDate( TTDate::parseDateTime( $data['hire_date'] ) );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data, $variable_function_map );

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
						case 'hire_date':
							$data[$variable] = TTDate::getAPIDate( 'DATE', $this->getHireDate() );
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			$this->getPermissionColumns( $data, $this->getCreatedBy(), $this->getCreatedBy(), false, $include_columns );
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee Default Information' ), null, $this->getTable(), $this );
	}

}

?>
