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
 * @package Modules\Company
 */
class BranchFactory extends Factory {
	protected $table = 'branch';
	protected $pk_sequence_name = 'branch_id_seq'; //PK Sequence name

	protected $address_validator_regex = '/^[a-zA-Z0-9-,_\/\.\'#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';
	protected $city_validator_regex = '/^[a-zA-Z0-9-,_\.\'()\[\]#\ |\x{0080}-\x{FFFF}]{1,250}$/iu';

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			//This should be able to replace: getVariableToFunctionMap -- and handle most of getObjectAsArray/setObjectFromArray
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'status' )->setObjectAsArrayFunction( 'Option::getByKey' )->setType( 'integer' )->setIsSynthetic( true ),
							TTSCol::new( 'manual_id' )->setFunctionMap( 'ManualID' )->setType( 'integer' ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' ),
							TTSCol::new( 'name_metaphone' )->setFunctionMap( 'NameMetaphone' )->setType( 'varchar' )->setIsUserVisible( false ),

							TTSCol::new( 'address1' )->setFunctionMap( 'Address1' )->setType( 'varchar' ),
							TTSCol::new( 'address2' )->setFunctionMap( 'Address2' )->setType( 'varchar' ),
							TTSCol::new( 'city' )->setFunctionMap( 'City' )->setType( 'varchar' ),
							TTSCol::new( 'country' )->setFunctionMap( 'Country' )->setType( 'varchar' ),
							TTSCol::new( 'province' )->setFunctionMap( 'Province' )->setType( 'varchar' ),
							TTSCol::new( 'postal_code' )->setFunctionMap( 'PostalCode' )->setType( 'varchar' ),
							TTSCol::new( 'work_phone' )->setFunctionMap( 'WorkPhone' )->setType( 'varchar' ),
							TTSCol::new( 'fax_phone' )->setFunctionMap( 'FaxPhone' )->setType( 'varchar' ),

							TTSCol::new( 'longitude' )->setFunctionMap( 'Longitude' )->setType( 'numeric' ),
							TTSCol::new( 'latitude' )->setFunctionMap( 'Latitude' )->setType( 'numeric' ),

							TTSCol::new( 'geo_fence_ids' )->setFunctionMap( 'GEOFenceIds' )->setType( 'uuid' )->setIsSynthetic( true ),

							//TODO: These will be removed once they are removed from the database. Search for all references to these fields and remove them.
							TTSCol::new( 'other_id1' )->setFunctionMap( 'OtherID1' )->setType( 'varchar' ),
							TTSCol::new( 'other_id2' )->setFunctionMap( 'OtherID2' )->setType( 'varchar' ),
							TTSCol::new( 'other_id3' )->setFunctionMap( 'OtherID3' )->setType( 'varchar' ),
							TTSCol::new( 'other_id4' )->setFunctionMap( 'OtherID4' )->setType( 'varchar' ),
							TTSCol::new( 'other_id5' )->setFunctionMap( 'OtherID5' )->setType( 'varchar' ),

							TTSCol::new( 'user_group_selection_type_id' )->setFunctionMap( 'UserGroupSelectionType' )->setType( 'smallint' ),
							TTSCol::new( 'user_title_selection_type_id' )->setFunctionMap( 'UserTitleSelectionType' )->setType( 'smallint' ),
							TTSCol::new( 'user_default_branch_selection_type_id' )->setFunctionMap( 'UserDefaultBranchSelectionType' )->setType( 'smallint' ),
							TTSCol::new( 'include_user_default_branch_id' )->setFunctionMap( 'IncludeUserDefaultBranch' )->setType( 'smallint' ),
							TTSCol::new( 'user_default_department_selection_type_id' )->setFunctionMap( 'UserDefaultDepartmentSelectionType' )->setType( 'smallint' ),

							TTSCol::new( 'custom_field' )->setFunctionMap( 'CustomField' )->setType( 'jsonb' ),
							TTSCol::new( 'tag' )->setFunctionMap( 'Tag' )->setType( 'string' )->setIsSynthetic( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_branch' )->setLabel( TTi18n::getText( 'Branch') )->setFields(
									TTSFields::new(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status') )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getOptions' )->setArg( 'status', 'name' ) ),
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name') )->setWidth( '100%' ),
											TTSField::new( 'manual_id' )->setType( 'text' )->setLabel( TTi18n::getText( 'Code') )->setWidth( 65 ),

											TTSField::new( 'address1' )->setType( 'text' )->setLabel( TTi18n::getText( 'Home Address (Line 1)') )->setWidth( '100%' ),
											TTSField::new( 'address2' )->setType( 'text' )->setLabel( TTi18n::getText( 'Home Address (Line 2)') )->setWidth( '100%' ),
											TTSField::new( 'city' )->setType( 'text' )->setLabel( TTi18n::getText( 'City') )->setWidth( '100%' ),
											TTSField::new( 'country' )->setType( 'text' )->setLabel( TTi18n::getText( 'Country') )->setWidth( 149 )->setDataSource( TTSAPI::new( 'APICompany' )->setMethod( 'getOptions' )->setArg( 'country', 'name' ) ),
											TTSField::new( 'province' )->setType( 'text' )->setLabel( TTi18n::getText( 'Province/State') )->setWidth( '100%' )->setDataSource( TTSAPI::new( 'APICompany' )->setMethod( 'getOptions' )->setArg( 'province', 'name' ) ),
											TTSField::new( 'postal_code' )->setType( 'text' )->setLabel( TTi18n::getText( 'Postal/ZIP Code') )->setWidth( '100%' ),

											TTSField::new( 'work_phone' )->setType( 'text' )->setLabel( TTi18n::getText( 'Work Phone') )->setWidth( 149 ),
											TTSField::new( 'fax_phone' )->setType( 'text' )->setLabel( TTi18n::getText( 'Fax Phone') )->setWidth( 149 ),

											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( TTi18n::getText( 'Tags' ) ),
									)
							),
							TTSTab::new( 'tab_employee_criteria' )->setLabel( TTi18n::getText( 'Employee Criteria') )->setInitCallback( 'initSubEmployeeCriteriaView' )->setHTMLTemplate( 'this.getBranchEmployeeCriteriaTabHtml' )->setDisplayOnMassEdit( false )->setSubView( true ),
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'UI', false )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Include Branch' )->setVisible( [ 'UI' ], false )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) )
							),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'exclude_id' )->setType( 'multi-dropdown' )->setLabel( 'Exclude Branch' )->setVisible( [ 'UI' ], false )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) )
							),

							TTSSearchField::new( 'status_id' )->setType( 'integer' )->setColumn( 'a.status_id' )->setMulti( true )->setFieldObject(
									TTSField::new( 'status_id' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getOptions' )->setArg( 'status', 'name' ) )
							),

							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' )->setFieldObject(
									TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )
							),
							TTSSearchField::new( 'manual_id' )->setType( 'numeric' )->setColumn( 'a.manual_id' )->setFieldObject(
									TTSField::new( 'manual_id' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Code' ) )
							),

							TTSSearchField::new( 'country' )->setType( 'text' )->setColumn( 'a.country' )->setFieldObject(
									TTSField::new( 'country' )->setType( 'text' )->setLabel( TTi18n::getText( 'Country' ) )->setDataSource( TTSAPI::new( 'APICompany' )->setMethod( 'getOptions' )->setArg( 'country', 'name' ) )
							),
							TTSSearchField::new( 'province' )->setType( 'text' )->setColumn( 'a.province' )->setFieldObject(
									TTSField::new( 'province' )->setType( 'text' )->setLabel( TTi18n::getText( 'Province/State' ) )->setDataSource( TTSAPI::new( 'APICompany' )->setMethod( 'getOptions' )->setArg( 'province', 'name' ) )
							),
							TTSSearchField::new( 'city' )->setType( 'text' )->setColumn( 'a.city' )->setFieldObject(
									TTSField::new( 'city' )->setType( 'text' )->setLabel( TTi18n::getText( 'City' ) )
							),
							TTSSearchField::new( 'address1' )->setType( 'text' )->setColumn( 'a.address1' )->setFieldObject(
									TTSField::new( 'address1' )->setType( 'text' )->setLabel( TTi18n::getText( 'Address 1' ) )
							),
							TTSSearchField::new( 'address2' )->setType( 'text' )->setColumn( 'a.address2' )->setFieldObject(
									TTSField::new( 'address2' )->setType( 'text' )->setLabel( TTi18n::getText( 'Address 2' ) )
							),
							TTSSearchField::new( 'postal_code' )->setType( 'text' )->setColumn( 'a.postal_code' )->setFieldObject(
									TTSField::new( 'postal_code' )->setType( 'text' )->setLabel( TTi18n::getText( 'Postal Code' ) )
							),
							TTSSearchField::new( 'work_phone' )->setType( 'text' )->setColumn( 'a.work_phone' )->setFieldObject(
									TTSField::new( 'work_phone' )->setType( 'text' )->setLabel( TTi18n::getText( 'Work Phone' ) )
							),

							TTSSearchField::new( 'tag' )->setType( 'tag' )->setColumn( 'a.id' )->setFieldObject(
									TTSField::new( 'tag' )->setType( 'tag' )->setLabel( TTi18n::getText( 'Tags' ) )
							),

							TTSSearchField::new( 'created_by' )->setType( 'uuid' )->setColumn( 'a.created_by' )->setFieldObject(
									TTSField::new( 'created_by' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Created By' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) )
							),
							TTSSearchField::new( 'updated_by' )->setType( 'uuid' )->setColumn( 'a.updated_by' )->setFieldObject(
									TTSField::new( 'updated_by' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'Updated By' ) )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) )
							),
					) );
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' )
									->setSummary( 'Get branch records. Branches are often but not always physical locations like a city.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIBranch' )->setMethod( 'setBranch' )
									->setSummary( 'Add or edit branch records. Branches are often but not always physical locations like a city. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIBranch' )->setMethod( 'deleteBranch' )
									->setSummary( 'Delete branch records by passing in an array of UUID\'s.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIBranch' )->setMethod( 'getBranch' ) ),
											   ) ),
							TTSAPI::new( 'APIBranch' )->setMethod( 'getBranchDefaultData' )
									->setSummary( 'Get default branch data used for creating new branches. Use this before calling setBranch to get the correct default data.' ),
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

		$retval = null;
		switch ( $name ) {
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'ENABLED' ),
						20 => TTi18n::gettext( 'DISABLED' ),
				];
				break;
			case 'user_group_selection_type_id':
				$retval = [
						10 => TTi18n::gettext( 'All Groups' ),
						20 => TTi18n::gettext( 'Only Selected Groups' ),
						30 => TTi18n::gettext( 'All Except Selected Groups' ),
				];
				break;
			case 'user_title_selection_type_id':
				$retval = [
						10 => TTi18n::gettext( 'All Titles' ),
						20 => TTi18n::gettext( 'Only Selected Titles' ),
						30 => TTi18n::gettext( 'All Except Selected Titles' ),
				];
				break;
			case 'user_default_branch_selection_type_id':
				$retval = [
						10 => TTi18n::gettext( 'All Default Branches' ),
						20 => TTi18n::gettext( 'Only Selected Default Branches' ),
						30 => TTi18n::gettext( 'All Except Selected Default Branches' ),
				];
				break;
			case 'user_default_department_selection_type_id':
				$retval = [
						10 => TTi18n::gettext( 'All Default Departments' ),
						20 => TTi18n::gettext( 'Only Selected Default Departments' ),
						30 => TTi18n::gettext( 'All Except Selected Default Departments' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-status'    => TTi18n::gettext( 'Status' ),
						'-1020-manual_id' => TTi18n::gettext( 'Code' ),
						'-1030-name'      => TTi18n::gettext( 'Name' ),

						'-1140-address1'    => TTi18n::gettext( 'Address 1' ),
						'-1150-address2'    => TTi18n::gettext( 'Address 2' ),
						'-1160-city'        => TTi18n::gettext( 'City' ),
						'-1170-province'    => TTi18n::gettext( 'Province/State' ),
						'-1180-country'     => TTi18n::gettext( 'Country' ),
						'-1190-postal_code' => TTi18n::gettext( 'Postal Code' ),
						'-1200-work_phone'  => TTi18n::gettext( 'Work Phone' ),
						'-1210-fax_phone'   => TTi18n::gettext( 'Fax Phone' ),

						'-1300-tag' => TTi18n::gettext( 'Tags' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];

				$retval = $this->getCustomFieldsColumns( $retval, null );

				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'manual_id',
						'name',
						'city',
						'province',
				];
				break;
			case 'unique_columns': //Columns that are unique, and disabled for mass editing.
				$retval = [
						'name',
						'manual_id',
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
				'id'             => 'ID',
				'company_id'     => 'Company',
				'status_id'      => 'Status',
				'status'         => false,
				'manual_id'      => 'ManualID',
				'name'           => 'Name',
				'name_metaphone' => 'NameMetaphone',
				'address1'       => 'Address1',
				'address2'       => 'Address2',
				'city'           => 'City',
				'country'        => 'Country',
				'province'       => 'Province',
				'postal_code'    => 'PostalCode',
				'work_phone'     => 'WorkPhone',
				'fax_phone'      => 'FaxPhone',
				'longitude'      => 'Longitude',
				'latitude'       => 'Latitude',
				'geo_fence_ids'  => 'GEOFenceIds',
				'tag'            => 'Tag',

				'user_group_selection_type_id' => 'UserGroupSelectionType',
				'user_group_ids'               => 'UserGroup',
				'include_user_ids'             => 'IncludeUser',
				'exclude_user_ids'             => 'ExcludeUser',

				'user_title_selection_type_id' => 'UserTitleSelectionType',
				'user_title_ids'               => 'UserTitle',

				'user_default_branch_selection_type_id' => 'UserDefaultBranchSelectionType',
				'user_default_branch_ids'               => 'UserDefaultBranch',
				'include_user_default_branch_id'        => 'IncludeUserDefaultBranch',

				'user_default_department_selection_type_id' => 'UserDefaultDepartmentSelectionType',
				'user_default_department_ids'               => 'UserDefaultDepartment',

				'deleted'        => 'Deleted',
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
	 * @param $value
	 * @return bool
	 */
	function setCompany( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'company_id', $value );
	}

	/**
	 * @return bool|mixed
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
	 * @param string $id UUID
	 * @return bool
	 */
	function isUniqueManualID( $id ) {
		if ( $this->getCompany() == false ) {
			return false;
		}

		$ph = [
				'manual_id'  => (int)$id,
				'company_id' => TTUUID::castUUID( $this->getCompany() ),
		];

		$query = 'select id from ' . $this->getTable() . ' where manual_id = ? AND company_id = ? AND deleted=0';
		$id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $id, 'Unique Code: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

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
	 * @param string $company_id UUID
	 * @return int
	 */
	function getNextAvailableManualId( $company_id = null ) {
		global $current_company;

		if ( $company_id == '' && is_object( $current_company ) ) {
			$company_id = $current_company->getId();
		} else if ( $company_id == '' && isset( $this ) && is_object( $this ) ) {
			$company_id = $this->getCompany();
		}

		$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
		$blf->getHighestManualIDByCompanyId( $company_id );
		if ( $blf->getRecordCount() > 0 ) {
			$next_available_manual_id = ( $blf->getCurrent()->getManualId() + 1 );
		} else {
			$next_available_manual_id = 1;
		}

		return $next_available_manual_id;
	}

	/**
	 * @return bool|mixed
	 */
	function getManualID() {
		return $this->getGenericDataValue( 'manual_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setManualID( $value ) {
		$value = $this->Validator->stripNonNumeric( trim( $value ) );

		return $this->setGenericDataValue( 'manual_id', $value );
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
		$this->setNameMetaphone( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getNameMetaphone() {
		return $this->getGenericDataValue( 'name_metaphone' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNameMetaphone( $value ) {
		$value = metaphone( trim( $value ) );

		return $this->setGenericDataValue( 'name_metaphone', $value );
	}

	/**
	 * @return bool|mixed
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
	 * @return bool|mixed
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
	function getProvince() {
		return $this->getGenericDataValue( 'province' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setProvince( $value ) {
		return $this->setGenericDataValue( 'province', strtoupper( trim( $value ) ) );
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
	 * @return bool|float
	 */
	function getLongitude() {
		return $this->getGenericDataValue( 'longitude' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLongitude( $value ) {
		if ( is_numeric( $value ) ) {
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
		return $this->getGenericDataValue( 'latitude' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLatitude( $value ) {
		if ( is_numeric( $value ) ) {
			$value = TTMath::removeTrailingZeros( round( (float)$value, 6 ) ); //Always use 6 decimal places as that is to 0.11m accuracy, this also prevents audit logging 0 vs 0.000000000 -- Don't use parseFloat() here as it should never be a user input value with commas as decimal symbols.
		} else {
			$value = null; //Allow $value=NULL so the coordinates can be cleared. Also make sure if FALSE is passed in here we assume NULL so it doesn't get cast to integer and saved in DB.
		}

		return $this->setGenericDataValue( 'latitude', $value );
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
	 * @return array|bool
	 */
	function getGEOFenceIds() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 4000, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setGEOFenceIds( $ids ) {
		Debug::text( 'Setting GEO Fence IDs...', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 4000, $this->getID(), (array)$ids );
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
		} else if ( TTUUID::isUUID( $this->getCompany() ) && $this->getCompany() != TTUUID::getZeroID() && $this->getCompany() != TTUUID::getNotExistID()
				&& TTUUID::isUUID( $this->getID() ) && $this->getID() != TTUUID::getZeroID() && $this->getID() != TTUUID::getNotExistID() ) {
			return CompanyGenericTagMapListFactory::getStringByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 110, $this->getID() );
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
	 * @param string $id UUID
	 * @return bool
	 */
	function isAllowedUser( $id ) {
		if ( $this->isNew() ) {
			return true; //We don't have anything to go on	yet
		} else {
			//Don't bother going to the database for expensive isAllowed check if all the selection types are allowed.
			if ( $this->getUserGroupSelectionType() == 10 && $this->getUserDefaultBranchSelectionType() == 10 && $this->getUserDefaultDepartmentSelectionType() == 10 && $this->getUserTitleSelectionType() == 10 && $this->getExcludeUser() == false ) {
				return true;
			} else {
				//Debug::text('Checking database for Branch ID: '. $id, __FILE__, __LINE__, __METHOD__, 10);
				$blf = TTnew( 'BranchListFactory' ); /** @var BranchListFactory $blf */
				$blf->getByCompanyIdAndIdAndUserId( $this->getCompany(), $this->getID(), $id );
				if ( $blf->getRecordCount() == 1 ) {
					return true;
				}
			}
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getUserGroupSelectionType() {
		return (int)$this->getGenericDataValue( 'user_group_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserGroupSelectionType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_group_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getUserGroup() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 7000, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setUserGroup( $ids ) {
		Debug::text( 'Setting User Group IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 7000, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getIncludeUser() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 7010, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setIncludeUser( $ids ) {
		Debug::text( 'Setting Include User IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 7010, $this->getID(), $ids );
	}

	/**
	 * @return array|bool
	 */
	function getExcludeUser() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 7020, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setExcludeUser( $ids ) {
		Debug::text( 'Setting Exclude User IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 7020, $this->getID(), $ids );
	}

	/**
	 * @return bool|int
	 */
	function getUserTitleSelectionType() {
		return (int)$this->getGenericDataValue( 'user_title_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserTitleSelectionType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_title_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getUserTitle() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 7030, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setUserTitle( $ids ) {
		Debug::text( 'Setting User Title IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 7030, $this->getID(), $ids );
	}

	/**
	 * @return bool|int
	 */
	function getUserDefaultBranchSelectionType() {
		return (int)$this->getGenericDataValue( 'user_default_branch_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserDefaultBranchSelectionType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_default_branch_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getUserDefaultBranch() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 7040, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setUserDefaultBranch( $ids ) {
		Debug::text( 'Setting User Default Branch IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 7040, $this->getID(), $ids );
	}

	/**
	 * @return bool
	 */
	function getIncludeUserDefaultBranch() {
		return $this->fromBool( $this->getGenericDataValue( 'include_user_default_branch_id' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIncludeUserDefaultBranch( $value ) {
		return $this->setGenericDataValue( 'include_user_default_branch_id', $this->toBool( $value ) );
	}

	/**
	 * @return bool|int
	 */
	function getUserDefaultDepartmentSelectionType() {
		return (int)$this->getGenericDataValue( 'user_default_department_selection_type_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setUserDefaultDepartmentSelectionType( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'user_default_department_selection_type_id', $value );
	}

	/**
	 * @return array|bool
	 */
	function getUserDefaultDepartment() {
		return CompanyGenericMapListFactory::getArrayByCompanyIDAndObjectTypeIDAndObjectID( $this->getCompany(), 7050, $this->getID() );
	}

	/**
	 * @param string $ids UUID
	 * @return bool
	 */
	function setUserDefaultDepartment( $ids ) {
		Debug::text( 'Setting User Default Department IDs : ', __FILE__, __LINE__, __METHOD__, 10 );

		return CompanyGenericMapFactory::setMapIDs( $this->getCompany(), 7050, $this->getID(), $ids );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		//Status
		if ( $this->getStatus() !== false ) {
			$this->Validator->inArrayKey( 'status',
										  $this->getStatus(),
										  TTi18n::gettext( 'Incorrect Status' ),
										  $this->getOptions( 'status' )
			);
		}

		//Manual ID
		if ( $this->getManualID() !== false ) {
			$this->Validator->isNumeric( 'manual_id',
										 $this->getManualID(),
										 TTi18n::gettext( 'Code is invalid' )
			);
			if ( $this->Validator->isError( 'manual_id' ) == false ) {
				$this->Validator->isLength( 'manual_id',
											$this->getManualID(),
											TTi18n::gettext( 'Code has too many digits' ),
											0,
											10 );
			}
			if ( $this->Validator->getValidateOnly() == false && $this->Validator->isError( 'manual_id' ) == false ) {
				$this->Validator->isTrue( 'manual_id',
										  ( (int)$this->getManualID() === 0 ) ? false : true,
										  TTi18n::gettext( 'Code is invalid, must not be 0' )
				);
			}
			if ( $this->Validator->isError( 'manual_id' ) == false ) {
				$this->Validator->isTrue( 'manual_id',
										  ( (int)$this->getManualID() !== 0 && $this->Validator->stripNon32bitInteger( $this->getManualID() ) === 0 ) ? false : true,
										  TTi18n::gettext( 'Code is invalid, maximum value exceeded' ) );
			}
			if ( $this->Validator->isError( 'manual_id' ) == false ) {
				$this->Validator->isTrue( 'manual_id',
										  $this->isUniqueManualID( $this->getManualID() ),
										  TTi18n::gettext( 'Code is already in use, please enter a different one' ) );
			}
		}

		//Name
		if ( $this->getName() !== false ) {
			$this->Validator->isLength( 'name',
										$this->getName(),
										TTi18n::gettext( 'Name is too short or too long' ),
										2,
										100 );

			$this->Validator->isHTML( 'name',
									  $this->getName(),
									  TTi18n::gettext( 'Name contains invalid special characters' ),
			);

			if ( $this->Validator->isError( 'name' ) == false ) {
				$this->Validator->isTrue( 'name',
										  $this->isUniqueName( $this->getName() ),
										  TTi18n::gettext( 'Branch name already exists' ) );
			}
		}

		//Address1
		if ( $this->getAddress1() != '' ) {
			$this->Validator->isRegEx( 'address1',
									   $this->getAddress1(),
									   TTi18n::gettext( 'Address1 contains invalid characters' ),
									   $this->address_validator_regex );
			if ( $this->Validator->isError( 'address1' ) == false ) {
				$this->Validator->isLength( 'address1',
											$this->getAddress1(),
											TTi18n::gettext( 'Address1 is too short or too long' ),
											2,
											250 );
			}
		}

		//Address2
		if ( $this->getAddress2() != '' ) {
			$this->Validator->isRegEx( 'address2',
									   $this->getAddress2(),
									   TTi18n::gettext( 'Address2 contains invalid characters' ),
									   $this->address_validator_regex );
			if ( $this->Validator->isError( 'address2' ) == false ) {
				$this->Validator->isLength( 'address2',
											$this->getAddress2(),
											TTi18n::gettext( 'Address2 is too short or too long' ),
											2,
											250 );
			}
		}

		//City
		if ( $this->getCity() !== false ) {
			$this->Validator->isRegEx( 'city',
									   $this->getCity(),
									   TTi18n::gettext( 'City contains invalid characters' ),
									   $this->city_validator_regex );
			if ( $this->Validator->isError( 'city' ) == false ) {
				$this->Validator->isLength( 'city',
											$this->getCity(),
											TTi18n::gettext( 'City name is too short or too long' ),
											2,
											250 );
			}
		}

		//Province
		if ( $this->getProvince() !== false ) {
			$cf = TTnew( 'CompanyFactory' ); /** @var CompanyFactory $cf */
			$options_arr = $cf->getOptions( 'province' );
			if ( isset( $options_arr[$this->getCountry()] ) ) {
				$options = $options_arr[$this->getCountry()];
			} else {
				$options = [];
			}
			$this->Validator->inArrayKey( 'province',
										  $this->getProvince(),
										  TTi18n::gettext( 'Invalid Province/State' ),
										  $options );
			unset( $options, $options_arr );
		}

		//Country
		if ( $this->getCountry() !== false ) {
			$this->Validator->inArrayKey( 'country',
										  $this->getCountry(),
										  TTi18n::gettext( 'Invalid Country' ),
										  $cf->getOptions( 'country' ) );
		}

		//Postal Code
		if ( $this->getPostalCode() != '' ) {
			$this->Validator->isPostalCode( 'postal_code',
											$this->getPostalCode(),
											TTi18n::gettext( 'Postal/ZIP Code contains invalid characters, invalid format, or does not match Province/State' ),
											$this->getCountry(), $this->getProvince() );
			if ( $this->Validator->isError( 'postal_code' ) == false ) {
				$this->Validator->isLength( 'postal_code',
											$this->getPostalCode(),
											TTi18n::gettext( 'Postal/ZIP Code is too short or too long' ),
											1,
											10 );
			}
		}

		//Longitude
		if ( $this->getLongitude() != 0 ) {
			$this->Validator->isFloat( 'longitude',
									   $this->getLongitude(),
									   TTi18n::gettext( 'Longitude is invalid' ) );
		}

		//Latitude
		if ( $this->getLongitude() != 0 ) {
			$this->Validator->isFloat( 'latitude',
									   $this->getLatitude(),
									   TTi18n::gettext( 'Latitude is invalid' ) );
		}

		//Work Phone
		if ( $this->getWorkPhone() != '' ) {
			$this->Validator->isPhoneNumber( 'work_phone',
											 $this->getWorkPhone(),
											 TTi18n::gettext( 'Work phone number is invalid' ) );
		}

		//Fax Phone
		if ( $this->getFaxPhone() != '' ) {
			$this->Validator->isPhoneNumber( 'fax_phone',
											 $this->getFaxPhone(),
											 TTi18n::gettext( 'Fax phone number is invalid' ) );
		}

		$this->validateCustomFields( $this->getCompany() );

		//
		// ABOVE: Validation code moved from set*() functions.
		//


		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getStatus() == false ) {
			$this->setStatus( 10 );
		}

		if ( $this->getManualID() == false ) {
			$this->setManualID( $this->getNextAvailableManualId( $this->getCompany() ) );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$data_diff = $this->getDataDifferences();
		$this->removeCache( $this->getId() );

		//Remove cache for checking if a user is allowed or not. isAllowedUser()
		$this->removeCache( null, 'branch_user_is_allowed_' . $this->getCompany() );

		if ( $this->getDeleted() == false ) {
			CompanyGenericTagMapFactory::setTags( $this->getCompany(), 110, $this->getID(), $this->getTag() );

			$this->clearGeoCode( $data_diff ); //Clear Lon/Lat coordinates when address has changed.
		}

		if ( $this->getDeleted() == true ) {
			Debug::Text( 'UnAssign Hours from Branch: ' . $this->getId(), __FILE__, __LINE__, __METHOD__, 10 );
			//Unassign hours from this branch.
			$pcf = TTnew( 'PunchControlFactory' ); /** @var PunchControlFactory $pcf */
			$query = 'update ' . $pcf->getTable() . ' set branch_id = \'' . TTUUID::getZeroID() . '\' where branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$udtf = TTnew( 'UserDateTotalFactory' ); /** @var UserDateTotalFactory $udtf */
			$query = 'update ' . $udtf->getTable() . ' set branch_id = \'' . TTUUID::getZeroID() . '\' where branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$sf_b = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf_b */
			$query = 'update ' . $sf_b->getTable() . ' set branch_id = \'' . TTUUID::getZeroID() . '\' where branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$uf = TTnew( 'UserFactory' ); /** @var UserFactory $uf */
			$query = 'update ' . $uf->getTable() . ' set default_branch_id = \'' . TTUUID::getZeroID() . '\' where company_id = \'' . TTUUID::castUUID( $this->getCompany() ) . '\' AND default_branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$udf = TTnew( 'UserDefaultFactory' ); /** @var UserDefaultFactory $udf */
			$query = 'update ' . $udf->getTable() . ' set default_branch_id = \'' . TTUUID::getZeroID() . '\' where company_id = \'' . TTUUID::castUUID( $this->getCompany() ) . '\' AND default_branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$sf = TTnew( 'StationFactory' ); /** @var StationFactory $sf */
			$query = 'update ' . $sf->getTable() . ' set branch_id = \'' . TTUUID::getZeroID() . '\' where company_id = \'' . TTUUID::castUUID( $this->getCompany() ) . '\' AND branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$sbf = TTnew( 'StationBranchFactory' ); /** @var StationBranchFactory $sbf */
			$query = 'delete from ' . $sbf->getTable() . ' where branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$rstf = TTnew( 'RecurringScheduleTemplateFactory' ); /** @var RecurringScheduleTemplateFactory $rstf */
			$query = 'update ' . $rstf->getTable() . ' set branch_id = \'' . TTUUID::getZeroID() . '\' where branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
			$query = 'update ' . $rsf->getTable() . ' set branch_id = \'' . TTUUID::getZeroID() . '\' where branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
			$this->ExecuteSQL( $query );

			if ( getTTProductEdition() >= TT_PRODUCT_CORPORATE ) {
				$jf = TTNew( 'JobFactory' ); /** @var JobFactory $jf */
				$query = 'update ' . $jf->getTable() . ' set branch_id = \'' . TTUUID::getZeroID() . '\' where branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
				$this->ExecuteSQL( $query );

				//Job employee criteria
				$cgmlf = TTnew( 'CompanyGenericMapListFactory' ); /** @var CompanyGenericMapListFactory $cgmlf */
				$cgmlf->getByCompanyIDAndObjectTypeAndMapID( $this->getCompany(), 1010, $this->getID() );
				if ( $cgmlf->getRecordCount() > 0 ) {
					foreach ( $cgmlf as $cgm_obj ) {
						Debug::text( 'Deleting from Company Generic Map: ' . $cgm_obj->getID(), __FILE__, __LINE__, __METHOD__, 10 );
						$cgm_obj->Delete();
					}
				}
			}

			if ( getTTProductEdition() >= TT_PRODUCT_ENTERPRISE ) {
				$uef = TTNew( 'UserExpenseFactory' ); /** @var UserExpenseFactory $uef */
				$query = 'update ' . $uef->getTable() . ' set branch_id = \'' . TTUUID::getZeroID() . '\' where branch_id = \'' . TTUUID::castUUID( $this->getId() ) . '\'';
				$this->ExecuteSQL( $query );
			}
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
						case 'status':
							$function = 'get' . $variable;
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = Option::getByKey( $this->$function(), $this->getOptions( $variable ) );
							}
							break;
						case 'name_metaphone':
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
			$data = $this->getCustomFields( $this->getCompany(), $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Branch' ) . ': ' . $this->getName(), null, $this->getTable(), $this );
	}

}

?>
