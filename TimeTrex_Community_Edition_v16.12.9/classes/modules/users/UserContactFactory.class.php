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
class UserContactFactory extends Factory {
	protected $table = 'user_contact';
	protected $pk_sequence_name = 'user_contact_id_seq'; //PK Sequence name

	protected $user_obj = null;
	protected $permission_obj = null;

	protected $name_validator_regex = '/^[a-zA-Z- ,\.\'()\[\]|\x{0080}-\x{FFFF}]{1,250}$/iu'; //Allow ()/[] so nicknames can be specified. Allow "," so names can be: Doe, Jr. or: Doe, III
	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'()\[\]#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'ethnic_group_id' )->setFunctionMap( 'EthnicGroup' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'first_name' )->setFunctionMap( 'FirstName' )->setType( 'varchar' ),
							TTSCol::new( 'middle_name' )->setFunctionMap( 'MiddleName' )->setType( 'varchar' ),
							TTSCol::new( 'last_name' )->setFunctionMap( 'LastName' )->setType( 'varchar' ),
							TTSCol::new( 'sex_id' )->setFunctionMap( 'Sex' )->setType( 'integer' ),
							TTSCol::new( 'address1' )->setFunctionMap( 'Address1' )->setType( 'varchar' ),
							TTSCol::new( 'address2' )->setFunctionMap( 'Address2' )->setType( 'varchar' ),
							TTSCol::new( 'city' )->setFunctionMap( 'City' )->setType( 'varchar' ),
							TTSCol::new( 'country' )->setFunctionMap( 'Country' )->setType( 'varchar' ),
							TTSCol::new( 'province' )->setFunctionMap( 'Province' )->setType( 'varchar' ),
							TTSCol::new( 'postal_code' )->setFunctionMap( 'PostalCode' )->setType( 'varchar' ),
							TTSCol::new( 'work_phone' )->setFunctionMap( 'WorkPhone' )->setType( 'varchar' ),
							TTSCol::new( 'work_phone_ext' )->setFunctionMap( 'WorkPhoneExt' )->setType( 'varchar' ),
							TTSCol::new( 'home_phone' )->setFunctionMap( 'HomePhone' )->setType( 'varchar' ),
							TTSCol::new( 'mobile_phone' )->setFunctionMap( 'MobilePhone' )->setType( 'varchar' ),
							TTSCol::new( 'fax_phone' )->setFunctionMap( 'FaxPhone' )->setType( 'varchar' ),
							TTSCol::new( 'home_email' )->setFunctionMap( 'HomeEmail' )->setType( 'varchar' ),
							TTSCol::new( 'work_email' )->setFunctionMap( 'WorkEmail' )->setType( 'varchar' ),
							TTSCol::new( 'birth_date' )->setFunctionMap( 'BirthDate' )->setType( 'integer' ),
							TTSCol::new( 'sin' )->setFunctionMap( 'SIN' )->setType( 'varchar' ),
							TTSCol::new( 'note' )->setFunctionMap( 'Note' )->setType( 'text' ),
							TTSCol::new( 'first_name_metaphone' )->setFunctionMap( 'FirstNameMetaphone' )->setType( 'varchar' ),
							TTSCol::new( 'last_name_metaphone' )->setFunctionMap( 'LastNameMetaphone' )->setType( 'varchar' ),
							TTSCol::new( 'custom_field' )->setFunctionMap( 'CustomField' )->setType( 'jsonb' )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_employee_contact' )->setLabel( 'Employee Contact' )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'user_id' )->setType( 'single-dropdown' )->setLabel( 'Employee' )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( 'Status' )->setDataSource( TTSAPI::new( 'APIUserContact' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( 'Type' )->setDataSource( TTSAPI::new( 'APIUserContact' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'first_name' )->setType( 'text' )->setLabel( 'First Name' )->setWidth( '100%' ),
											TTSField::new( 'middle_name' )->setType( 'text' )->setLabel( 'Middle Name' )->setWidth( '100%' ),
											TTSField::new( 'last_name' )->setType( 'text' )->setLabel( 'Last Name' )->setWidth( '100%' ),
											TTSField::new( 'sex_id' )->setType( 'integer' )->setLabel( 'Gender' ),
											TTSField::new( 'ethnic_group_id' )->setType( 'integer' )->setLabel( 'Ethnicity' )->setDataSource( TTSAPI::new( 'APIEthnicGroup' )->setMethod( 'getEthnicGroup' ) ),
											TTSField::new( 'address1' )->setType( 'text' )->setLabel( 'Home Address (Line 1)' )->setWidth( '100%' ),
											TTSField::new( 'address2' )->setType( 'text' )->setLabel( 'Home Address (Line 2)' )->setWidth( '100%' ),
											TTSField::new( 'city' )->setType( 'text' )->setLabel( 'City' )->setWidth( '100%' ),
											TTSField::new( 'country' )->setType( 'text' )->setLabel( 'Country' )->setWidth( '100%' ),
											TTSField::new( 'province' )->setType( 'text' )->setLabel( 'Province/State' )->setWidth( '100%' ),
											TTSField::new( 'postal_code' )->setType( 'text' )->setLabel( 'Postal/ZIP Code' )->setWidth( '100%' ),
											TTSField::new( 'work_phone' )->setType( 'text' )->setLabel( 'Work Phone' )->setWidth( '100%' ),
											TTSField::new( 'work_phone_ext' )->setType( 'text' )->setLabel( 'Work Phone Ext' )->setWidth( '100%' ),
											TTSField::new( 'home_phone' )->setType( 'text' )->setLabel( 'Home Phone' )->setWidth( '100%' ),
											TTSField::new( 'mobile_phone' )->setType( 'text' )->setLabel( 'Mobile Phone' )->setWidth( '100%' ),
											TTSField::new( 'fax_phone' )->setType( 'text' )->setLabel( 'Fax' )->setWidth( '100%' ),
											TTSField::new( 'work_email' )->setType( 'text' )->setLabel( 'Work Email' )->setWidth( '100%' ),
											TTSField::new( 'home_email' )->setType( 'text' )->setLabel( 'Home Email' )->setWidth( '100%' ),
											TTSField::new( 'birth_date' )->setType( 'date' )->setLabel( 'Birth Date' )->setWidth( '100%' ),
											TTSField::new( 'sin' )->setType( 'text' )->setLabel( 'SIN / SSN' )->setWidth( '100%' ),
											TTSField::new( 'note' )->setType( 'text' )->setLabel( 'Note' )->setWidth( '100%' ),
											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( 'Tags' )->setWidth( '100%' )
									)
							),
					)->addAttachment()->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'permission_children_ids' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'id' )->setType( 'uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'not_uuid_list' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'status' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true ),
							TTSSearchField::new( 'type' )->setType( 'numeric_list' )->setColumn( 'a.type_id' )->setMulti( true ),
							TTSSearchField::new( 'sex' )->setType( 'numeric_list' )->setColumn( 'a.sex_id' )->setMulti( true ),
							TTSSearchField::new( 'first_name' )->setType( 'text_metaphone' )->setColumn( 'a.first_name' ),
							TTSSearchField::new( 'last_name' )->setType( 'text_metaphone' )->setColumn( 'a.last_name' ),
							TTSSearchField::new( 'full_name' )->setType( 'text_metaphone' )->setColumn( 'a.last_name' ),
							TTSSearchField::new( 'home_phone' )->setType( 'phone' )->setColumn( 'a.home_phone' ),
							TTSSearchField::new( 'work_phone' )->setType( 'phone' )->setColumn( 'a.work_phone' ),
							TTSSearchField::new( 'any_phone' )->setType( 'phone' )->setColumn( [ 'a.work_phone', 'a.home_phone', 'a.mobile_phone' ] ),
							TTSSearchField::new( 'country' )->setType( 'upper_text_list' )->setColumn( 'a.country' ),
							TTSSearchField::new( 'province' )->setType( 'upper_text_list' )->setColumn( 'a.province' ),
							TTSSearchField::new( 'city' )->setType( 'text' )->setColumn( 'a.city' ),
							TTSSearchField::new( 'address1' )->setType( 'text' )->setColumn( 'a.address1' ),
							TTSSearchField::new( 'address2' )->setType( 'text' )->setColumn( 'a.address2' ),
							TTSSearchField::new( 'postal_code' )->setType( 'text' )->setColumn( 'a.postal_code' ),
							TTSSearchField::new( 'sin' )->setType( 'numeric_string' )->setColumn( 'a.sin' ),
							TTSSearchField::new( 'work_email' )->setType( 'text' )->setColumn( 'a.work_email' ),
							TTSSearchField::new( 'home_email' )->setType( 'text' )->setColumn( 'a.home_email' ),
							TTSSearchField::new( 'any_email' )->setType( 'text' )->setColumn( [ 'a.work_email', 'a.home_email' ] ),
							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' ),
							TTSSearchField::new( 'created_date' )->setType( 'date_range' )->setColumn( 'a.created_date' ),
							TTSSearchField::new( 'updated_date' )->setType( 'date_range' )->setColumn( 'a.updated_date' )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUserContact' )->setMethod( 'getUserContact' )
									->setSummary( 'Get contact records for an employee. Note that this is different from the employees\'s own contact information. Instead, this refers to a list of contacts associated with an employee, which could, for example, include dependents, emergency, siblings, spouses, children, etc.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUserContact' )->setMethod( 'setUserContact' )
									->setSummary( 'Add or edit employee contact records. Will return the record UUID upon success, or a validation error if there is a problem. Note that this is different from the employee\'s own contact information. Instead, this refers to a list of contacts associated with an employee, which could, for example, include dependents, emergency, siblings, spouses, children, etc.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUserContact' )->setMethod( 'deleteUserContact' )
									->setSummary( 'Delete user contact records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUserContact' )->setMethod( 'getUserContact' ) ),
											   ) ),
							TTSAPI::new( 'APIUserContact' )->setMethod( 'getUserContactDefaultData' )
									->setSummary( 'Get default employee contact data used for creating new employee contacts. Use this before calling setUserContact to get the correct default data.' ),
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
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'ENABLED' ),
						20 => TTi18n::gettext( 'DISABLED' ),
				];
				break;
			case 'type':
				$retval = [
						10 => TTi18n::gettext( 'Spouse/Partner' ),
						20 => TTi18n::gettext( 'Parent/Guardian' ),
						30 => TTi18n::gettext( 'Sibling' ),
						40 => TTi18n::gettext( 'Child' ),
						50 => TTi18n::gettext( 'Relative' ),
						60 => TTi18n::gettext( 'Dependant' ),
						70 => TTi18n::gettext( 'Emergency Contact' ),
				];
				break;
			case 'sex':
				$retval = [
						5  => TTi18n::gettext( 'Unspecified' ),
						10 => TTi18n::gettext( 'Male' ),
						20 => TTi18n::gettext( 'Female' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-employee_first_name'  => TTi18n::gettext( 'Employee First Name' ),
						'-1100-employee_middle_name' => TTi18n::gettext( 'Employee Middle Name' ),
						'-1020-employee_last_name'   => TTi18n::gettext( 'Employee Last Name' ),
						'-1030-employee_number'      => TTi18n::gettext( 'Employee #' ),
						'-1040-title'                => TTi18n::gettext( 'Employee Title' ),
						'-1050-user_group'           => TTi18n::gettext( 'Employee Group' ),
						'-1060-default_branch'       => TTi18n::gettext( 'Employee Branch' ),
						'-1070-default_department'   => TTi18n::gettext( 'Employee Department' ),

						'-1100-first_name'  => TTi18n::gettext( 'First Name' ),
						'-1101-middle_name' => TTi18n::gettext( 'Middle Name' ),
						'-1102-last_name'   => TTi18n::gettext( 'Last Name' ),
						'-1120-status'      => TTi18n::gettext( 'Status' ),
						'-1130-type'        => TTi18n::getText( 'Type' ),

						'-1150-sex'          => TTi18n::gettext( 'Gender' ),
						'-1160-ethnic_group' => TTi18n::gettext( 'Ethnic Group' ),

						'-1166-address1' => TTi18n::gettext( 'Address 1' ),
						'-1167-address2' => TTi18n::gettext( 'Address 2' ),

						'-1168-city'           => TTi18n::gettext( 'City' ),
						'-1169-province'       => TTi18n::gettext( 'Province/State' ),
						'-1170-country'        => TTi18n::gettext( 'Country' ),
						'-1180-postal_code'    => TTi18n::gettext( 'Postal Code' ),
						'-1190-work_phone'     => TTi18n::gettext( 'Work Phone' ),
						'-1191-work_phone_ext' => TTi18n::gettext( 'Work Phone Ext' ),
						'-1200-home_phone'     => TTi18n::gettext( 'Home Phone' ),
						'-1210-mobile_phone'   => TTi18n::gettext( 'Mobile Phone' ),
						'-1220-fax_phone'      => TTi18n::gettext( 'Fax Phone' ),
						'-1230-home_email'     => TTi18n::gettext( 'Home Email' ),
						'-1240-work_email'     => TTi18n::gettext( 'Work Email' ),
						'-1250-birth_date'     => TTi18n::gettext( 'Birth Date' ),
						'-1280-sin'            => TTi18n::gettext( 'SIN/SSN' ),
						'-1290-note'           => TTi18n::gettext( 'Note' ),
						'-1300-tag'            => TTi18n::gettext( 'Tags' ),
						'-2000-created_by'     => TTi18n::gettext( 'Created By' ),
						'-2010-created_date'   => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'     => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date'   => TTi18n::gettext( 'Updated Date' ),
				];

				$retval = $this->getCustomFieldsColumns( $retval, null );

				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
					'employee_first_name',
					'employee_last_name',
					'type',
					'first_name',
					'last_name',
					'home_phone',
					'home_email',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'sin',
				];
				break;
			case 'linked_columns': //Columns that are linked together, mainly for Mass Edit, if one changes, they all must.
				$retval = [
						'country',
						'province',
						'postal_code',
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
				'id'                   => 'ID',
				'user_id'              => 'User',
				'status_id'            => 'Status',
				'status'               => false,
				'type_id'              => 'Type',
				'type'                 => false,
				'employee_first_name'  => false,
				'employee_middle_name' => false,
				'employee_last_name'   => false,
				'employee_number'      => false,
				'default_branch'       => false,
				'default_department'   => false,
				'user_group'           => false,
				'title'                => false,
				'first_name'           => 'FirstName',
				'first_name_metaphone' => 'FirstNameMetaphone',
				'middle_name'          => 'MiddleName',
				'last_name'            => 'LastName',
				'last_name_metaphone'  => 'LastNameMetaphone',
				'sex_id'               => 'Sex',
				'sex'                  => false,
				'ethnic_group_id'      => 'EthnicGroup',
				'ethnic_group'         => false,
				'address1'             => 'Address1',
				'address2'             => 'Address2',
				'city'                 => 'City',
				'country'              => 'Country',
				'province'             => 'Province',
				'postal_code'          => 'PostalCode',
				'work_phone'           => 'WorkPhone',
				'work_phone_ext'       => 'WorkPhoneExt',
				'home_phone'           => 'HomePhone',
				'mobile_phone'         => 'MobilePhone',
				'fax_phone'            => 'FaxPhone',
				'home_email'           => 'HomeEmail',
				'work_email'           => 'WorkEmail',
				'birth_date'           => 'BirthDate',
				'sin'                  => 'SIN',
				'note'                 => 'Note',
				'tag'                  => 'Tag',
				'deleted'              => 'Deleted',
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
	 * @return Permission|null
	 */
	function getPermissionObject() {
		if ( isset( $this->permission_obj ) && is_object( $this->permission_obj ) ) {
			return $this->permission_obj;
		} else {
			$this->permission_obj = new Permission();

			return $this->permission_obj;
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
		if ( isset( $this->data['type_id'] ) ) {
			return $this->data['type_id'];
		}

		return false;
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
	function getFirstName() {
		return $this->getGenericDataValue( 'first_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFirstName( $value ) {
		$value = trim( $value );
		$this->setFirstNameMetaphone( $value );

		return $this->setGenericDataValue( 'first_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFirstNameMetaphone() {
		return $this->getGenericDataValue( 'first_name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFirstNameMetaphone( $value ) {
		$value = metaphone( trim( $value ) );

		if ( $value != '' ) {
			$this->setGenericDataValue( 'first_name_metaphone', $value );

			return true;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getMiddleName() {
		return $this->getGenericDataValue( 'middle_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMiddleName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'middle_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastName() {
		return $this->getGenericDataValue( 'last_name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastName( $value ) {
		$value = trim( $value );
		$this->setLastNameMetaphone( $value );

		return $this->setGenericDataValue( 'last_name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLastNameMetaphone() {
		return $this->getGenericDataValue( 'last_name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLastNameMetaphone( $value ) {
		$value = metaphone( trim( $value ) );

		if ( $value != '' ) {
			$this->setGenericDataValue( 'last_name_metaphone', $value );

			return true;
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function getMiddleInitial() {
		if ( $this->getMiddleName() != '' ) {
			$middle_name = $this->getMiddleName();

			return $middle_name[0];
		}

		return false;
	}

	/**
	 * @param bool $reverse
	 * @param bool $include_middle
	 * @return bool|string
	 */
	function getFullName( $reverse = false, $include_middle = true ) {
		return Misc::getFullName( $this->getFirstName(), $this->getMiddleInitial(), $this->getLastName(), $reverse, $include_middle );
	}

	/**
	 * @return bool|int
	 */
	function getSex() {
		return $this->getGenericDataValue( 'sex_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSex( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'sex_id', $value );
	}

	/**
	 * @return bool
	 */
	function getEthnicGroup() {
		return $this->getGenericDataValue( 'ethnic_group_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setEthnicGroup( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'ethnic_group_id', $value );
	}

	/**
	 * @return bool
	 */
	function getAddress1() {
		return $this->getGenericDataValue( 'address1' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAddress1( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'address1', $value );
	}

	/**
	 * @return bool
	 */
	function getAddress2() {
		return $this->getGenericDataValue( 'address2' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setAddress2( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'address2', $value );
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
		//Debug::Text('Country: '. $this->getCountry() .' Province: '. $province, __FILE__, __LINE__, __METHOD__, 10);
		//If country isn't set yet, accept the value and re-validate on save.
		return $this->setGenericDataValue( 'province', strtoupper( trim( $value ) ) );
	}

	/**
	 * @return bool|mixed
	 */
	function getPostalCode() {
		return $this->getGenericDataValue( 'postal_code' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPostalCode( $value ) {
		$value = strtoupper( $this->Validator->stripSpaces( $value ) );

		return $this->setGenericDataValue( 'postal_code', $value );
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
	function getHomePhone() {
		return $this->getGenericDataValue( 'home_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHomePhone( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'home_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getMobilePhone() {
		return $this->getGenericDataValue( 'mobile_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMobilePhone( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'mobile_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFaxPhone() {
		return $this->getGenericDataValue( 'fax_phone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFaxPhone( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'fax_phone', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getHomeEmail() {
		return $this->getGenericDataValue( 'home_email' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHomeEmail( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'home_email', $value );
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
	function getBirthDate() {
		return $this->getGenericDataValue( 'birth_date' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setBirthDate( $value ) {
		//Allow for negative epochs, for birthdates less than 1960's
		return $this->setGenericDataValue( 'birth_date', ( $value != 0 && $value != '' ) ? TTDate::getMiddleDayEpoch( $value ) : '' ); //Allow blank birthdate.
	}

	/**
	 * @param null $sin
	 * @param bool $force_secure Force the SIN to always be secure regardless of permissions.
	 * @return bool|string
	 */
	function getSecureSIN( $sin = null, $force_secure = false ) {
		if ( $sin == '' ) {
			$sin = $this->getSIN();
		}

		if ( $sin != '' ) {
			global $current_user;
			if ( $force_secure == false && isset( $current_user ) && is_object( $current_user ) ) {
				if ( $this->getPermissionObject()->Check( 'user_contact', 'view_sin', $current_user->getId(), $current_user->getCompany() ) == true ) {
					return $sin;
				}
			}

			return Misc::censorString( $sin, '*', null, 1, 4, 4 );
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getSIN() {
		return $this->getGenericDataValue( 'sin' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setSIN( $value ) {
		//If *'s are in the SIN number, skip setting it
		//This allows them to change other data without seeing the SIN number.
		if ( stripos( $value, '*' ) !== false ) {
			return false;
		}

		$value = $this->Validator->stripNonNumeric( trim( $value ) );
		if ( $value != '' ) {
			return $this->setGenericDataValue( 'sin', $value );
		}

		return false;
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
	 * @return bool|string
	 */
	function getTag() {
		//Check to see if any temporary data is set for the tags, if not, make a call to the database instead.
		//postSave() needs to get the tmp_data.
		$value = $this->getGenericTempDataValue( 'tags' );
		if ( $value !== false ) {
			return $value;
		} else if ( is_object( $this->getUserObject() )
				&& TTUUID::isUUID( $this->getUserObject()->getCompany() ) && $this->getUserObject()->getCompany() != TTUUID::getZeroID() && $this->getUserObject()->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getUserObject()->getCompany(), 230, $this->getID() );
		}

		return false;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTag( $value ) {
		$value = trim( $value );

		//Save the tags in temporary memory to be committed in postSave()
		return $this->setGenericTempDataValue( 'tags', $value );
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
		if ( $this->getUser() !== false ) {
			$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
			$this->Validator->isResultSetWithRows( 'user_id',
												   $ulf->getByID( $this->getUser() ),
												   TTi18n::gettext( 'Invalid Employee' )
			);
		}
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
		// First name
		if ( $this->getFirstName() !== false ) {
			$this->Validator->isRegEx( 'first_name',
									   $this->getFirstName(),
									   TTi18n::gettext( 'First name contains invalid characters' ),
									   $this->name_validator_regex
			);
			if ( $this->Validator->isError( 'first_name' ) == false ) {
				$this->Validator->isLength( 'first_name',
											$this->getFirstName(),
											TTi18n::gettext( 'First name is too short or too long' ),
											2,
											50
				);
			}
		}
		// Middle name
		if ( $this->getMiddleName() != '' ) {
			$this->Validator->isRegEx( 'middle_name',
									   $this->getMiddleName(),
									   TTi18n::gettext( 'Middle name contains invalid characters' ),
									   $this->name_validator_regex
			);
			if ( $this->Validator->isError( 'middle_name' ) == false ) {
				$this->Validator->isLength( 'middle_name',
											$this->getMiddleName(),
											TTi18n::gettext( 'Middle name is too short or too long' ),
											1,
											50
				);
			}
		}
		// Last name
		if ( $this->getLastName() !== false ) {
			$this->Validator->isRegEx( 'last_name',
									   $this->getLastName(),
									   TTi18n::gettext( 'Last name contains invalid characters' ),
									   $this->name_validator_regex
			);
			if ( $this->Validator->isError( 'last_name' ) == false ) {
				$this->Validator->isLength( 'last_name',
											$this->getLastName(),
											TTi18n::gettext( 'Last name is too short or too long' ),
											2,
											50
				);
			}
		}
		// gender
		if ( $this->getSex() !== false ) {
			$this->Validator->inArrayKey( 'sex_id',
										  $this->getSex(),
										  TTi18n::gettext( 'Invalid gender' ),
										  $this->getOptions( 'sex' )
			);
		}
		// Ethnic Group
		if ( $this->getEthnicGroup() !== false && $this->getEthnicGroup() != TTUUID::getZeroID() ) {
			$eglf = TTnew( 'EthnicGroupListFactory' ); /** @var EthnicGroupListFactory $eglf */
			$this->Validator->isResultSetWithRows( 'ethnic_group',
												   $eglf->getById( $this->getEthnicGroup() ),
												   TTi18n::gettext( 'Ethnic Group is invalid' )
			);
		}
		// Address1
		if ( $this->getAddress1() != '' ) {
			$this->Validator->isRegEx( 'address1',
									   $this->getAddress1(),
									   TTi18n::gettext( 'Address1 contains invalid characters' ),
									   $this->address_validator_regex
			);
			if ( $this->Validator->isError( 'address1' ) == false ) {
				$this->Validator->isLength( 'address1',
											$this->getAddress1(),
											TTi18n::gettext( 'Address1 is too short or too long' ),
											2,
											250
				);
			}
		}
		// Address2
		if ( $this->getAddress2() != '' ) {
			$this->Validator->isRegEx( 'address2',
									   $this->getAddress2(),
									   TTi18n::gettext( 'Address2 contains invalid characters' ),
									   $this->address_validator_regex
			);
			if ( $this->Validator->isError( 'address2' ) == false ) {
				$this->Validator->isLength( 'address2',
											$this->getAddress2(),
											TTi18n::gettext( 'Address2 is too short or too long' ),
											2,
											250
				);
			}
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
		// Country
		if ( $this->getCountry() !== false ) {
			$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
			$this->Validator->inArrayKey( 'country',
										  $this->getCountry(),
										  TTi18n::gettext( 'Invalid Country' ),
										  $cf->getOptions( 'country' )
			);
			// Province/State
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
			unset( $options, $options_arr );
		}
		// Postal/ZIP Code
		if ( $this->getPostalCode() != '' ) {
			$this->Validator->isPostalCode( 'postal_code',
											$this->getPostalCode(),
											TTi18n::gettext( 'Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State' ),
											$this->getCountry(), $this->getProvince()
			);
			if ( $this->Validator->isError( 'postal_code' ) == false ) {
				$this->Validator->isLength( 'postal_code',
											$this->getPostalCode(),
											TTi18n::gettext( 'Postal/ZIP Code is too short or too long' ),
											1,
											10
				);
			}
		}
		// Work phone number
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
		// Home phone number
		if ( $this->getHomePhone() != '' ) {
			$this->Validator->isPhoneNumber( 'home_phone',
											 $this->getHomePhone(),
											 TTi18n::gettext( 'Home phone number is invalid' )
			);
		}
		// Mobile phone number
		if ( $this->getMobilePhone() != '' ) {
			$this->Validator->isPhoneNumber( 'mobile_phone',
											 $this->getMobilePhone(),
											 TTi18n::gettext( 'Mobile phone number is invalid' )
			);
		}
		// Fax phone number
		if ( $this->getFaxPhone() != '' ) {
			$this->Validator->isPhoneNumber( 'fax_phone',
											 $this->getFaxPhone(),
											 TTi18n::gettext( 'Fax phone number is invalid' )
			);
		}
		// Home Email address
		if ( $this->getHomeEmail() != '' ) {
			$error_threshold = 7; //No DNS checks.
			if ( PRODUCTION === true && DEMO_MODE === false ) {
				$error_threshold = 0; //DNS checks on email address.
			}
			$this->Validator->isEmailAdvanced( 'home_email',
											   $this->getHomeEmail(),
											   TTi18n::gettext( 'Home Email address is invalid' ),
											   $error_threshold
			);
		}
		// Work Email address
		if ( $this->getWorkEmail() != '' ) {
			$error_threshold = 7; //No DNS checks.
			if ( PRODUCTION === true && DEMO_MODE === false ) {
				$error_threshold = 0; //DNS checks on email address.
			}
			$this->Validator->isEmailAdvanced( 'work_email',
											   $this->getWorkEmail(),
											   TTi18n::gettext( 'Work Email address is invalid' ),
											   $error_threshold
			);
		}
		// Birth date
		if ( $this->getBirthDate() != '' ) {
			$this->Validator->isDate( 'birth_date',
									  $this->getBirthDate(),
									  TTi18n::gettext( 'Birth date is invalid, try specifying the year with four digits' )
			);
			if ( $this->Validator->isError( 'birth_date' ) == false ) {
				$this->Validator->isTRUE( 'birth_date',
										  ( TTDate::getMiddleDayEpoch( $this->getBirthDate() ) <= TTDate::getMiddleDayEpoch( ( time() + ( 365 * 86400 ) ) ) ) ? true : false,
										  TTi18n::gettext( 'Birth date can not be more than one year in the future' )
				);
			}
		}
		// SIN
		if ( $this->getSIN() != '' ) {
			$this->Validator->isLength( 'sin',
										$this->getSIN(),
										TTi18n::gettext( 'SIN is invalid' ),
										6,
										20
			);
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

		$this->validateCustomFields( is_object( $this->getUserObject() ) ? $this->getUserObject()->getCompany() : null );

		//
		// ABOVE: Validation code moved from set*() functions.
		//
		//When doing a mass edit of employees, user name is never specified, so we need to avoid this validation issue.

		//Re-validate the province just in case the country was set AFTER the province.
		//$this->setProvince( $this->getProvince() );
		return true;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		if ( $this->getStatus() == false ) {
			$this->setStatus( 10 ); //ENABLE
		}

		if ( $this->getType() == false ) {
			$this->setType( 10 ); //10=Spouse/Partner
		}

		if ( $this->getSex() == false ) {
			$this->setSex( 5 ); //UnSpecified
		}

		if ( $this->getEthnicGroup() == false ) {
			$this->setEthnicGroup( TTUUID::getZeroID() );
		}

		//Remember if this is a new user for postSave()
		if ( $this->isNew() ) {
			$this->is_new = true;
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		if ( $this->getDeleted() == false ) {
			Debug::text( 'Setting Tags...', __FILE__, __LINE__, __METHOD__, 10 );
			CompanyGenericTagMapFactory::setTags( $this->getUserObject()->getCompany(), 230, $this->getID(), $this->getTag() );
		}

		return true;
	}

	/**
	 * @return bool|string
	 */
	function getMapURL() {
		return Misc::getMapURL( $this->getAddress1(), $this->getAddress2(), $this->getCity(), $this->getProvince(), $this->getPostalCode(), $this->getCountry() );
	}

	/**
	 * Support setting created_by, updated_by especially for importing data.
	 * Make sure data is set based on the getVariableToFunctionMap order.
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$data = $this->parseCustomFieldsFromArray( $data );
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						case 'birth_date':
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
						case 'employee_first_name':
						case 'employee_middle_name':
						case 'employee_last_name':
						case 'employee_number':
						case 'title':
						case 'user_group':
						case 'ethnic_group':
						case 'default_branch':
						case 'default_department':
							$data[$variable] = $this->getColumn( $variable );
							break;
						case 'full_name':
							$data[$variable] = $this->getFullName( true );
							break;
						case 'status':
						case 'type':
						case 'sex':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'sin':
							$data[$variable] = $this->getSecureSIN(); //getSecureSIN() will display the full SIN if permissions allow.
							break;
						case 'birth_date':
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
				unset( $function );
			}
			$this->getPermissionColumns( $data, $this->getUser(), $this->getCreatedBy(), $permission_children_ids, $include_columns );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Employee Contact' ) . ': ' . $this->getFullName( false, true ), null, $this->getTable(), $this );
	}
}

?>
