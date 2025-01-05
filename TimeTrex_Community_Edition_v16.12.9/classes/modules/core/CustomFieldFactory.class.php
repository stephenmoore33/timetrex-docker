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
 * @package Core
 */
class CustomFieldFactory extends Factory {
	protected $table = 'custom_field_control';
	protected $pk_sequence_name = 'custom_field_id_seq'; //PK Sequence name

	protected $company_obj = null;

	protected $dropdown_select_regex = '/^[a-zA-Z0-9_.:\-]+$/'; //Can't allow commas because of implode/explode on import/export for multiselect dropdowns. Import->parseCustomFieldOptions().

	protected $json_columns = [ 'meta_data', 'default_value' ];

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'status' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),

							TTSCol::new( 'parent_table' )->setFunctionMap( 'ParentTable' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'type_id' )->setFunctionMap( 'Type' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'type' )->setObjectAsArrayFunction( 'Option::getByKey' )->setIsSynthetic( true ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'display_order' )->setFunctionMap( 'DisplayOrder' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'default_value' )->setFunctionMap( 'DefaultValue' )->setType( 'jsonb' )->setIsNull( true ),
							TTSCol::new( 'width' )->setFunctionMap( 'Width' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'height' )->setFunctionMap( 'Height' )->setType( 'smallint' )->setIsNull( true ),
							TTSCol::new( 'is_required' )->setFunctionMap( 'IsRequired' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'is_unique' )->setFunctionMap( 'IsUnique' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'enable_search' )->setFunctionMap( 'EnableSearch' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'is_range_search' )->setFunctionMap( 'IsRangeSearch' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'is_advanced_validation' )->setFunctionMap( 'IsAdvancedValidation' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'help_text' )->setFunctionMap( 'HelpText' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'comment_text' )->setFunctionMap( 'CommentText' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'meta_data' )->setFunctionMap( 'CustomFieldMetaData' )->setType( 'json' )->setIsNull( true ),
							TTSCol::new( 'legacy_other_field_id' )->setFunctionMap( 'LegacyOtherFieldId' )->setType( 'smallint' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_custom_field' )->setLabel( 'Custom Field' )->setFields(
									new TTSFields(
											TTSField::new( 'id' )->setType( 'uuid' )->setVisible( [ 'UI' ], false ), //Hidden from UI, but visible to API and AI.
											TTSField::new( 'status_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Status' ) )->setDataSource( TTSAPI::new( 'APICustomField' )->setMethod( 'getOptions' )->setArg( 'status' ) ),
											TTSField::new( 'name' )->setType( 'text' )->setLabel( TTi18n::getText( 'Name' ) )->setWidth( '100%' ),
											TTSField::new( 'parent_table' )->setType( 'text' )->setLabel( TTi18n::getText( 'Object Type' ) )->setWidth( '100%' ),
											TTSField::new( 'type_id' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Field Type' ) )->setDataSource( TTSAPI::new( 'APICustomField' )->setMethod( 'getOptions' )->setArg( 'type' ) ),
											TTSField::new( 'display_order' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Display Order' ) ),
											TTSField::new( 'enable_search' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Enable Search' ) ),
											TTSField::new( 'default_value' )->setType( 'text' )->setLabel( TTi18n::getText( 'Default Value' ) ),
											TTSField::new( 'is_required' )->setType( 'checkbox' )->setLabel( TTi18n::getText( 'Required' ) ),
											TTSField::new( 'validate_min_length' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Minimum Length' ) ),
											TTSField::new( 'validate_max_length' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Maximum Length' ) ),
											TTSField::new( 'validate_decimal_places' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Decimal Places' ) ),
											TTSField::new( 'validate_min_amount' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Minimum Amount' ) ),
											TTSField::new( 'validate_max_amount' )->setType( 'numeric' )->setLabel( TTi18n::getText( 'Maximum Amount' ) ),
											TTSField::new( 'validate_min_time_unit' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Minimum Amount' ) ),
											TTSField::new( 'validate_max_time_unit' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Maximum Amount' ) ),
											TTSField::new( 'validate_min_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Minimum Date' ) ),
											TTSField::new( 'validate_max_date' )->setType( 'date' )->setLabel( TTi18n::getText( 'Maximum Date' ) ),
											TTSField::new( 'validate_min_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Minimum time' ) ),
											TTSField::new( 'validate_max_time' )->setType( 'time' )->setLabel( TTi18n::getText( 'Maximum time' ) ),
											TTSField::new( 'validate_multi_select_min_amount' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Multi-select Minimum Selected' ) ),
											TTSField::new( 'validate_multi_select_max_amount' )->setType( 'integer' )->setLabel( TTi18n::getText( 'Multi-select Maximum Selected' ) ),
											TTSField::new( 'validate_min_datetime' )->setType( 'datetime' )->setLabel( TTi18n::getText( 'Minimum Date' ) ),
											TTSField::new( 'validate_max_datetime' )->setType( 'datetime' )->setLabel( TTi18n::getText( 'Maximum Date' ) )
									)
							)->setHTMLTemplate( "<div id=\"tab_custom_field\" class=\"edit-view-tab-outside\">\n\t\t\t\t\t<div class=\"edit-view-tab\" id=\"tab_custom_field_content_div\">\n\t\t\t\t\t\t<div class=\"first-column\"></div>\n\t\t\t\t\t\t<div class=\"second-column\"></div>\n\t\t\t\t\t\t<div class=\"inside-editor-div full-width-column\" id=\"dropdown-editor\">\n\t\t\t\t\t\t</div>\n\t\t\t\t\t</div>\n\t\t\t\t</div>" )
					)->addAudit()
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( 'AI', true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' )->setVisible( 'AI', true ),
							TTSSearchField::new( 'type_id' )->setType( 'numeric_list' )->setColumn( 'a.type_id' ),

							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'a.status_id' ),

							TTSSearchField::new( 'parent_table' )->setType( 'text_list' )->setColumn( 'a.parent_table' ),
							TTSSearchField::new( 'exclude_parent_table' )->setType( 'not_text_list' )->setColumn( 'a.parent_table' ),
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APICustomField' )->setMethod( 'getCustomField' )
									->setSummary( 'Get custom field records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APICustomField' )->setMethod( 'setCustomField' )
									->setSummary( 'Add or edit custom field records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APICustomField' )->setMethod( 'deleteCustomField' )
									->setSummary( 'Delete custom field records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APICustomField' )->setMethod( 'getCustomField' ) ),
											   ) ),
							TTSAPI::new( 'APICustomField' )->setMethod( 'getCustomFieldDefaultData' )
									->setSummary( 'Get default custom field data used for creating new custom fields. Use this before calling setCustomField to get the correct default data.' ),
					),
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null $params
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
			case 'type_id':
				$retval = [
						100 => TTi18n::gettext( 'Text' ),
				];

				if ( Misc::getCurrentCompanyProductEdition() >= TT_PRODUCT_PROFESSIONAL ) {
					$retval[110] = TTi18n::gettext( 'Textarea' ); //Password
					//$retval[120] = TTi18n::gettext( 'Hidden Text' ); //Password
					//$retval[180]  = TTi18n::gettext( 'Formula' );
					//$retval[190] = TTi18n::gettext( 'WYSIWYG' ); //HTML

					//$retval[200] = TTi18n::gettext( 'Tags' ); //Is this required?

					//$retval[300] = TTi18n::gettext( 'Link/URL' );

					$retval[400] = TTi18n::gettext( 'Integer' );  //Up to 64bit integer (long)
					$retval[410] = TTi18n::gettext( 'Decimal' );  //Variable precision (they chooose how many decimals max up to 10 decimal places)
					$retval[420] = TTi18n::gettext( 'Currency' ); //(Based on users currency. Variable decimal places, depending on record it's different.) Formats on output. User chooses specific currency they can have different decimal places?

					$retval[500] = TTi18n::gettext( 'Checkbox' );
					//$retval[600 = TTi18n::gettext( 'Radio' );

					//$retval[700 = TTi18n::gettext( 'Attachment' ); File Upload
					//$retval[800 = TTi18n::gettext( 'Image' );
					//$retval[900 = TTi18n::gettext( 'Color Picker' );

					$retval[1000] = TTi18n::gettext( 'Date' );
					$retval[1010] = TTi18n::gettext( 'Date Range' );

					$retval[1100] = TTi18n::gettext( 'Time' );
					//$retval[1110  = TTi18n::gettext( 'Time Range' );

					$retval[1200] = TTi18n::gettext( 'Datetime' );
					//$retval[1210 = TTi18n::gettext( 'Datetime Range' );

					$retval[1300] = TTi18n::gettext( 'Time Unit' );

					//$retval[2000]  = TTi18n::gettext( 'Single-select Dropdown (Simple)' ); //ComboBox
					//$retval[2010] = TTi18n::gettext( 'Multi-select Dropdown (Simple)' ); //ComboBox

					$retval[2100] = TTi18n::gettext( 'Single-select Dropdown' ); //AComboBox
					$retval[2110] = TTi18n::gettext( 'Multi-select Dropdown' );  //AComboBox

					//$retval[2200] = TTi18n::gettext( 'Dynamic Dropdown' ); //Links to existing list such as employee, job, department, etc.
				}

				//Punch control has different allowed types of custom fields, so we need to be able to filter them out based on product edition.
				if ( isset( $params['parent_table'] ) && $params['parent_table'] == 'punch_control' ) {
					if ( Misc::getCurrentCompanyProductEdition() <= TT_PRODUCT_PROFESSIONAL ) {
						//Professional Edition and lower only support text for punch control.
						$retval = array_intersect_key( $retval, [ 100 => true ] );
					} else {
						//Corporate edition allows for more punch control custom fields, but not all of them.
						unset(
								$retval[1000], //Date
								$retval[1010], //Date Range
								$retval[1100], //Time
								$retval[1200], //Datetime
						);
					}
				}
				break;
			case 'parent':
			case 'parent_table':
				$retval = [
						'company'      => TTi18n::gettext( 'Company' ),
						'branch'       => TTi18n::gettext( 'Branch' ),
						'department'   => TTi18n::gettext( 'Department' ),
						'ethnic_group' => TTi18n::gettext( 'Ethnicity' ),
						'users'        => TTi18n::gettext( 'Employee' ),
						'user_title'   => TTi18n::gettext( 'Employee Title' ),
						'user_contact' => TTi18n::gettext( 'Employee Contact' ),
						'schedule'     => TTi18n::gettext( 'Schedule' ),
						'legal_entity' => TTi18n::gettext( 'Legal Entities' ),
						'pay_stub_entry_account' => TTi18n::gettext( 'Pay Stub Account' ),
				];

				if ( Misc::getCurrentCompanyProductEdition() >= TT_PRODUCT_CORPORATE ) {
					$retval['punch_control'] = TTi18n::gettext( 'Punch' );
					$retval['job'] = TTi18n::gettext( 'Job' );
					$retval['job_item'] = TTi18n::gettext( 'Task' );
					$retval['client'] = TTi18n::gettext( 'Client' );
					$retval['client_contact'] = TTi18n::gettext( 'Client Contact' );
					$retval['product'] = TTi18n::gettext( 'Product' );
					$retval['invoice'] = TTi18n::gettext( 'Invoice' );
					$retval['document'] = TTi18n::gettext( 'Document' );
				} else {
					if ( Misc::getFeatureFlag( 'custom_field_punch' ) == true ) {
						$retval['punch_control'] = TTi18n::gettext( 'Punch' );
					}
				}

				if ( PRODUCTION == false && defined( 'UNIT_TEST_MODE' ) && UNIT_TEST_MODE == true ) {
					$retval['ui_kit'] = TTi18n::gettext( 'UI Kit Sample' );
				}
				break;
			case 'legacy_type_to_parent_table':
				$retval = [
						2  => 'company',
						4  => 'branch',
						5  => 'department',
						10 => 'users',
						12 => 'user_title',
						15 => 'punch_control',
						18 => 'schedule',
						20 => 'job',
						30 => 'job_item',
						50 => 'client',
						55 => 'client_contact',
						60 => 'product',
						70 => 'invoice',
						80 => 'document',
				];
				break;
			case 'conversion_field_types':
				$retval = [
						500  => true,  //Checkbox
						1000 => true, //Date
						1010 => true, //Date range
						//1300 => true, //Time Unit
						2100 => true, //Single-select Dropdown
						2110 => true, //Multi-select Dropdown
				];
				break;
			case 'columns':
				$retval = [
						'-1010-status' => TTi18n::gettext( 'Status' ),
						'-1030-parent' => TTi18n::gettext( 'Object Type' ),
						'-1020-type'   => TTi18n::gettext( 'Field Type' ),
						'-1040-name'   => TTi18n::gettext( 'Name' ),

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
						'parent',
						'name',
						'type',
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
				'status_id'             => 'Status',
				'status'                => false,
				'type_id'               => 'Type',
				'type'                  => false,
				'parent_table'          => 'ParentTable',
				'parent'                => false,
				'name'                  => 'Name',
				'display_order'         => 'DisplayOrder',
				'default_value'         => 'DefaultValue',
				'is_required'           => 'IsRequired',
				'enable_search'         => 'EnableSearch',
				'meta_data'             => 'CustomFieldMetaData',
				'legacy_other_field_id' => 'LegacyOtherFieldId',
				'deleted'               => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return null
	 */
	function getCompanyObject() {
		if ( is_object( $this->company_obj ) ) {
			return $this->company_obj;
		} else {
			$clf = TTnew( 'CompanyListFactory' );
			/** @var CompanyListFactory $clf */
			$this->company_obj = $clf->getById( $this->getCompany() )->getCurrent();

			return $this->company_obj;
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
		$value = (int)trim( $value );
		Debug::text( 'Attempting to set Type To: ' . $value, __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericDataValue( 'type_id', $value );
	}

	/**
	 * @return bool|string
	 */
	function getParentTable() {
		return $this->getGenericDataValue( 'parent_table' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setParentTable( $value ) {
		$value = trim( $value );
		return $this->setGenericDataValue( 'parent_table', $value );
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
	 * @return mixed
	 */
	function getDisplayOrder() {
		return $this->getGenericDataValue( 'display_order' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDisplayOrder( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'display_order', $value );
	}

	/**
	 * @return mixed
	 */
	function getDefaultValue() {
		$this->decodeJSONColumn( 'default_value' );

		$value = $this->getGenericDataValue( 'default_value' );

		return $value;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDefaultValue( $value ) {
		if ( is_array( $value ) == false ) {
			$value = trim( $value );
		}

		return $this->setGenericDataValue( 'default_value', $value );
	}

	/**
	 * @return bool
	 */
	function getIsRequired() {
		return $this->fromBool( $this->getGenericDataValue( 'is_required' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setIsRequired( $value ) {
		return $this->setGenericDataValue( 'is_required', $this->toBool( $value ) );
	}

	/**
	 * @return mixed
	 */
	function getEnableSearch() {
		return $this->fromBool( $this->getGenericDataValue( 'enable_search' ) );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setEnableSearch( $value ) {
		return $this->setGenericDataValue( 'enable_search', $this->toBool( $value ) );
	}

	/**
	 * @return array
	 */
	function getCustomFieldMetaData() {
		$this->decodeJSONColumn( 'meta_data' );

		$value = $this->getGenericDataValue( 'meta_data' );

		if ( $value == false ) {
			return $this->getDefaultMetaData();
		}

		return $value;
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCustomFieldMetaData( $value ) {
		return $this->setGenericDataValue( 'meta_data', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getLegacyOtherFieldId() {
		return $this->getGenericDataValue( 'legacy_other_field_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setLegacyOtherFieldId( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'legacy_other_field_id', $value );
	}

	/**
	 * @return array
	 */
	function getDefaultMetaData() {
		return [
				'validation' => []
		];
	}

	/**
	 * Process data to correct formats (Date => ISO format: YYYY-MM-DD, etc) if required.
	 * @param int $type_id
	 * @return mixed
	 *
	 */
	function castToSQL( $type_id, $data ) {
		switch ( $type_id ) {
			case 400: //Integer
				if ( $data === null || is_bool( $data ) == true ) {
					$data = '';
				}
				$data = $data === '' ? $data : (int)$data; //We need to allow blank values for integers, in scenario where empty string becomes 0 and then fails validation.
				break;
			case 410: //Decimal
				if ( $data === null || is_bool( $data ) == true ) {
					$data = '';
				}
				$data = $data === '' ? $data : (float)$data;  //We need to allow blank values for decimals, in scenario where empty string becomes 0 and then fails validation.
				break;
			case 420: //Currency
				$data = (string)$data; //Cannot cast to float due to lack of precision.
				break;
			case 500: //Checkbox boolean
				$data = (bool)$data;
				break;
			case 1000: //Date ISO format: YYYY-MM-DD
				$data = TTDate::getISODateStamp( TTDate::parseDateTime( $data ), false ); //Don't default to todays date when $data is blank/empty. This allows the date field to be cleared out.
				break;
			case 1010: //Array of Date ISO format: YYYY-MM-DD
				if ( is_array( $data ) == false && strpos( $data, ' - ' ) !== false ) { //Range might be passed as a string or an array.
					$data = explode( ' - ', $data );
				}
				if ( is_array( $data ) == true ) {
					foreach ( $data as $key => $value ) {
						$data[$key] = TTDate::getISODateStamp( TTDate::parseDateTime( $value ), false ); //Don't default to todays date when $data is blank/empty. This allows the date field to be cleared out.
					}
				}
				break;
			case 1100: //ISO time format, 24hrs: 23:59:59
				$data = TTDate::getISOTime( TTDate::parseDateTime( $data ) );
				break;
			case 1200: //Epoch
				$data = TTDate::parseDateTime( $data );
				break;
			case 2100: //Single-select
				//If it happens to be an array, convert it to a string instead. There was a bug where saving a record *without* changing the custom field would save it as an array. When changing it would save as a string.
				if ( is_array( $data ) == true ) {
					$data = $data[array_key_first( $data )];
				}
				break;
			case 2110: //Multi-select
				if ( is_array( $data ) == false ) {
					$data = [$data];
				}
				break;
			default:
				$data = (string)$data;
				break;
		}

		return $data;
	}


	/**
	 * @param $type_id
	 * @param $data
	 * @param $meta_data
	 * @param $human_readable
	 * @return mixed
	 */
	function castFromSQL( $type_id, $data, $meta_data = [], $human_readable = false ) {
		switch ( $type_id ) {
			case 400: //Integer
				if ( $data === null || is_bool( $data ) == true ) {
					$data = '';
				}
				$data = $data === '' ? $data : (int)$data; //We need to allow blank values for integers, in scenario where empty string becomes 0 and then fails validation.
				break;
			case 410: //Decimal
				if ( $data === null || is_bool( $data ) == true ) {
					$data = '';
				}
				$data = $data === '' ? $data : (float)$data;  //We need to allow blank values for decimals, in scenario where empty string becomes 0 and then fails validation.
				break;
			case 420: //Currency
				$data = (string)$data; //Cannot cast to float due to lack of precision.
				break;
			case 500: //Checkbox boolean
				$data = (bool)$data;
				if ( $human_readable == true ) {
					$data = Misc::HumanBoolean( $data );
				}
				break;
			case 1000:
				$data = TTDate::getDate( 'DATE', TTDate::parseDateTime( $data ) );
				break;
			case 1010:
				if ( is_array( $data ) ) {
					foreach ( $data as $key => $value ) {
						$data[$key] = TTDate::getDate( 'DATE', TTDate::parseDateTime( $value ) );
					}
				}

				if ( $human_readable && is_array( $data ) ) {
					$data = implode( ' - ', $data );
				}
				break;
			case 1100:
				$data = TTDate::getDate( 'TIME', TTDate::parseDateTime( $data ) );
				break;
			case 1200:
				$data = TTDate::getDate( 'DATE+TIME', $data );
				break;
			//case 1300: //Time Unit (This should always return seconds to avoid breaking reports)
				//if ( $human_readable == true ) {
				//	$data = TTDate::getTimeUnit( $data );
				//}
				//break;
			case 2100: //Single-select
			case 2110: //Multi-select
				if ( !is_array( $data ) ) {
					$data = [ $data ];
				}

				if ( $human_readable == true && isset( $meta_data['validation']['multi_select_items'] ) && is_array( $meta_data['validation']['multi_select_items'] ) ) {
					$multi_select_labels = [];
					foreach ( $data as $select_item ) {
						foreach ( $meta_data['validation']['multi_select_items'] as $multi_select_item ) {
							if ( isset( $multi_select_item['id'] ) == false ) {
								Debug::Arr( $data, 'Invalid multi-select item detected, skipping item. Custom Field Data: ', __FILE__, __LINE__, __METHOD__, 10 );
								continue;
							}

							if ( $multi_select_item['id'] == $select_item ) {
								$multi_select_labels[] = $multi_select_item['label'];
								break;
							}
						}
					}
					$data = implode( ', ', $multi_select_labels );
				}
				break;
			default:
				$data = (string)$data;
				break;
		}

		return $data;
	}

	/**
	 * @return string
	 */
	function getPrefixedCustomFieldID( $use_parent_table = false ) {
		if ( $use_parent_table == true ) {
			return $this->getParentTable() . '_custom_field-' . $this->getId();
		} else {
			return 'custom_field-' . $this->getId();
		}
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {

		if ( $this->isNew() == true ) {
			$this->Validator->isTrue( 'status_id',
					( Misc::getCurrentCompanyProductEdition() >= TT_PRODUCT_PROFESSIONAL ),
									  TTi18n::gettext( 'Unable to create new custom fields, as this functionality has been deprecated in the Community Edition' ) );
		}

		// Company
		$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
		$this->Validator->isResultSetWithRows( 'company',
											   $clf->getByID( $this->getCompany() ),
											   TTi18n::gettext( 'Company is invalid' )
		);
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is too short or too long' ),
									2,
									100
		);

		$this->Validator->isHTML( 'name',
								  $this->getName(),
								  TTi18n::gettext( 'Name contains invalid special characters' ),
		);

		if ( $this->getDeleted() == false && $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name is already in use for this object type' )
			);
		}
		// Type
		if ( $this->getType() !== false ) {
			$this->Validator->inArrayKey( 'type_id',
										  $this->getType(),
										  TTi18n::gettext( 'Incorrect Type' ),
										  $this->getOptions( 'type_id' )
			);
		}

		//Limited types when parent table is punch
		if ( $this->getParentTable() === 'punch_control' ) {
			$this->Validator->isTrue( 'type_id',
									  in_array( $this->getType(), [
											  100, //Text
											  110, //Textarea
											  400, //Integer
											  410, //Decimal
											  420, //Currency
											  500, //Checkbox
											  1100, //Time
											  1300, //Time Unit
											  2100, //Single-select
											  2110, //Multi-select
									  ] ),
									  TTi18n::gettext( 'Incorrect Type for Punch' )
			);

			if ( $ignore_warning == false && $this->isNew() == true ) {
				$this->Validator->Warning( 'parent_table', TTi18n::gettext( 'To view this custom field, you must modify the permissions groups to allow "Punch -> Edit Custom Field (%1)" permissions', $this->getName() ) );
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

		// Display
		if ( $this->getDisplayOrder() != '' ) {
			$this->Validator->isNumeric( 'display_order',
										 $this->getDisplayOrder(),
										 TTi18n::gettext( 'Invalid Display Order' )
			);
		}

		$data_diff = $this->getDataDifferences();
		if ( $this->isDataDifferent( 'type_id', $data_diff ) == true ) {
			$this->Validator->isTrue( 'type_id',
									  false,
									  TTi18n::gettext( 'Unable to change type of a custom field' ) );
		}

		if ( $this->isDataDifferent( 'parent_table', $data_diff ) == true ) {
			$this->Validator->isTrue( 'parent_table',
									  false,
									  TTi18n::gettext( 'Unable to change object type of a custom field' ) );
		}

		//  ***** Meta Data Validation Rules *****

		$meta_data = $this->getCustomFieldMetaData();
		if ( is_array( $meta_data ) && array_key_exists( 'validation', $meta_data ) ) {
			$validation_rules = $meta_data['validation'];
		} else {
			$validation_rules = [];
		}

		//Min Length
		if ( isset( $validation_rules[ 'validate_min_length'] ) && $validation_rules[ 'validate_min_length'] != '' ) {
			$this->Validator->isNumeric( 'validate_min_length',
										 $this->getCustomFieldMetaData()['validation']['validate_min_length'],
										 TTi18n::gettext( 'Minimum length must only be digits' )
			);
		}

		if ( isset( $validation_rules[ 'validate_decimal_places'] ) && $validation_rules[ 'validate_decimal_places'] != '' ) {
			$this->Validator->isNumeric( 'validate_decimal_places',
										 $this->getCustomFieldMetaData()['validation']['validate_decimal_places'],
										 TTi18n::gettext( 'Decimal places must only be digits' )
			);

			$this->Validator->isGreaterThan( 'validate_decimal_places',
											 $this->getCustomFieldMetaData()['validation']['validate_decimal_places'],
											 TTi18n::gettext( 'Minimum decimal places must be 1 or greater' ),
											 1
			);

			$this->Validator->isLessThan( 'validate_decimal_places',
											 $this->getCustomFieldMetaData()['validation']['validate_decimal_places'],
											 TTi18n::gettext( 'Minimum decimal places must be 8 or less' ),
											 8
			);
		}

		if ( isset( $validation_rules[ 'validate_min_length'] ) && $validation_rules[ 'validate_min_length'] != '' ) {
			$this->Validator->isGreaterThan( 'validate_min_length',
										 $this->getCustomFieldMetaData()['validation']['validate_min_length'],
										 TTi18n::gettext( 'Minimum length must be a positive value' ),
										 0
			);
		}

		//Max Length
		if ( isset( $validation_rules[ 'validate_max_length'] ) && $validation_rules[ 'validate_max_length'] !== '' && $validation_rules[ 'validate_max_length'] !== false ) {
			$this->Validator->isNumeric( 'validate_max_length',
										 $this->getCustomFieldMetaData()['validation']['validate_max_length'],
										 TTi18n::gettext( 'Maximum length must only be digits' )
			);
			$this->Validator->isGreaterThan( 'validate_max_length',
										  $this->getCustomFieldMetaData()['validation']['validate_max_length'],
										  TTi18n::gettext( 'Maximum length must be greater than minimum length' ),
										  $this->getCustomFieldMetaData()['validation']['validate_min_length']
			);
			$this->Validator->isGreaterThan( 'validate_max_length',
										  $this->getCustomFieldMetaData()['validation']['validate_max_length'],
										  TTi18n::gettext( 'Maximum length must be a positive value' ),
										  1
			);
		}

		//Min amount
		if ( isset( $validation_rules[ 'validate_min_amount'] ) && $validation_rules[ 'validate_min_amount'] != '' ) {
			$this->Validator->isNumeric( 'validate_min_amount',
										 $this->getCustomFieldMetaData()['validation']['validate_min_amount'],
										 TTi18n::gettext( 'Minimum amount must only be digits' )
			);
		}

		//Max amount
		if ( isset( $validation_rules[ 'validate_max_amount'] ) && $validation_rules[ 'validate_max_amount'] != '' ) {
			$this->Validator->isNumeric( 'validate_max_amount',
										 $this->getCustomFieldMetaData()['validation']['validate_max_amount'],
										 TTi18n::gettext( 'Maximum amount must only be digits' )
			);
			$this->Validator->isGreaterThan( 'validate_max_amount',
										  $this->getCustomFieldMetaData()['validation']['validate_max_amount'],
										  TTi18n::gettext( 'Maximum amount must be greater than minimum amount' ),
										  $this->getCustomFieldMetaData()['validation']['validate_min_amount']
			);
		}

		//Min time_unit
		if ( isset( $validation_rules[ 'validate_min_time_unit'] ) && $validation_rules[ 'validate_min_time_unit'] != '' ) {
			$this->Validator->isNumeric( 'validate_min_time_unit',
										 $this->getCustomFieldMetaData()['validation']['validate_min_time_unit'],
										 TTi18n::gettext( 'Minimum time unit must only be digits' )
			);
		}

		//Max time_unit
		if ( isset( $validation_rules[ 'validate_max_time_unit'] ) && $validation_rules[ 'validate_max_time_unit'] != '' && $validation_rules[ 'validate_min_time_unit'] != '' ) {
			$this->Validator->isNumeric( 'validate_max_time_unit',
										 $this->getCustomFieldMetaData()['validation']['validate_max_time_unit'],
										 TTi18n::gettext( 'Maximum time unit must only be digits' )
			);
			$this->Validator->isGreaterThan( 'validate_max_time_unit',
											 $this->getCustomFieldMetaData()['validation']['validate_max_time_unit'],
											 TTi18n::gettext( 'Maximum time unit must be greater than minimum time unit' ),
											 $this->getCustomFieldMetaData()['validation']['validate_min_time_unit']
			);
		}

		//Min date
		if ( isset( $validation_rules[ 'validate_min_date'] ) && $validation_rules[ 'validate_min_date'] != '' ) {
			$this->Validator->isDate( 'validate_min_date',
									  TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_min_date'] ),
									  TTi18n::gettext( 'Minimum date is not a valid date' )
			);
		}

		//Max date
		if ( isset( $validation_rules[ 'validate_max_date'] ) && $validation_rules[ 'validate_max_date'] != '' ) {
			$this->Validator->isDate( 'validate_max_date',
									  TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_max_date'] ),
									  TTi18n::gettext( 'Maximum date is not a valid date' )
			);
			$this->Validator->isGreaterThan( 'validate_max_date',
											 TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_max_date'] ),
											 TTi18n::gettext( 'Maximum date must be greater than minimum date' ),
											 TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_min_date'] )
			);
		}

		//Min datetime
		if ( isset( $validation_rules[ 'validate_min_datetime'] ) && $validation_rules[ 'validate_min_datetime'] != '' ) {
			$this->Validator->isDate( 'validate_min_datetime',
									  TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_min_datetime'] ),
									  TTi18n::gettext( 'Minimum date is not a valid date' )
			);
		}

		//Max datetime
		if ( isset( $validation_rules[ 'validate_max_datetime'] ) && $validation_rules[ 'validate_max_datetime'] != '' ) {
			$this->Validator->isDate( 'validate_max_datetime',
									  TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_max_datetime'] ),
									  TTi18n::gettext( 'Maximum date is not a valid date' )
			);
			$this->Validator->isGreaterThan( 'validate_max_datetime',
											 TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_max_datetime'] ),
											 TTi18n::gettext( 'Maximum date must be greater than minimum date' ),
											 TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_min_datetime'] )
			);
		}

		//Min time
		if ( isset( $validation_rules[ 'validate_min_time'] ) && $validation_rules[ 'validate_min_time'] != '' ) {
			$this->Validator->isDate( 'validate_min_time',
									  TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_min_time'] ),
									  TTi18n::gettext( 'Minimum time is not a valid time format' )
			);
		}

		//Max time
		if ( isset( $validation_rules[ 'validate_max_time'] ) && $validation_rules[ 'validate_max_time'] != '' ) {
			$this->Validator->isDate( 'validate_max_time',
									  TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_max_time'] ),
									  TTi18n::gettext( 'Maximum time is not a valid time format' )
			);
			$this->Validator->isGreaterThan( 'validate_max_time',
											 TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_max_time'] ),
											 TTi18n::gettext( 'Maximum time must be greater than minimum time' ),
											 TTDate::parseDateTime( $this->getCustomFieldMetaData()['validation']['validate_min_time'] )
			);
		}

		// Multi-select minimum amount of options selected
		if ( isset( $validation_rules[ 'validate_multi_select_min_amount'] ) && $validation_rules[ 'validate_multi_select_min_amount'] != '' ) {
			$this->Validator->isNumeric( 'validate_multi_select_min_amount',
										 $this->getCustomFieldMetaData()['validation']['validate_multi_select_min_amount'],
										 TTi18n::gettext( 'Invalid Multi-select Minimum' )
			);
		}

		// Multi-select maximum amount of options selected
		if ( isset( $validation_rules[ 'validate_multi_select_max_amount'] ) && $validation_rules[ 'validate_multi_select_max_amount'] != '' ) {
			$this->Validator->isNumeric( 'validate_multi_select_max_amount',
										 $this->getCustomFieldMetaData()['validation']['validate_multi_select_max_amount'],
										 TTi18n::gettext( 'Invalid Multi-select Maximum' )
			);
			$this->Validator->isGreaterThan( 'validate_multi_select_max_amount',
											 $this->getCustomFieldMetaData()['validation']['validate_multi_select_max_amount'],
											 TTi18n::gettext( 'Maximum amount must be greater than minimum amount' ),
											 $this->getCustomFieldMetaData()['validation']['validate_multi_select_min_amount']
			);
		}

		// Multi-select items
		if ( $this->Validator->getValidateOnly() == false && isset( $validation_rules['multi_select_items'] ) && ( $this->getType() == 2100 || $this->getType() == 2110 ) ) {
			foreach ( $this->getCustomFieldMetaData()['validation']['multi_select_items'] as $item ) {
				if ( $item['id'] !== TTUUID::getZeroID() ) {
					if ( trim( $item['id'] ) == '' || trim( $item['label'] ) == '' ) {
						$this->Validator->isTrue( 'multi_select_items',
												  false,
												  TTi18n::gettext( 'Invalid Multi-select item, value and display label cannot be blank' )
						);
						break;
					}
					$this->Validator->isRegEx( 'multi_select_items',
											   $item['id'],
											   TTi18n::gettext( 'Incorrect characters in select item value, must be only contain alpha numeric characters and ".", "_", ":", "-"' ),
											   $this->dropdown_select_regex
					);
				}
			}
			$this->Validator->isTrue( 'multi_select_items',
										empty( $this->getCustomFieldMetaData()['validation']['multi_select_items'] ) == false,
										TTi18n::gettext( 'Must create at least 1 select item' )
			);
			$this->Validator->isTrue( 'multi_select_items',
					( count( array_column( $this->getCustomFieldMetaData()['validation']['multi_select_items'], 'id' ) ) == count( array_unique( array_column( $this->getCustomFieldMetaData()['validation']['multi_select_items'], 'id' ) ) ) ),
									  TTi18n::gettext( 'Select item values must be unique' )
			);
			$this->Validator->isTrue( 'multi_select_items',
					( count( array_column( $this->getCustomFieldMetaData()['validation']['multi_select_items'], 'label' ) ) == count( array_unique( array_column( $this->getCustomFieldMetaData()['validation']['multi_select_items'], 'label' ) ) ) ),
									  TTi18n::gettext( 'Select item display labels must be unique' )
			);

		}

		$this->Validator->isTrue( 'legacy_other_id',
								  $this->isUniqeLegacyOtherId( $this->getLegacyOtherFieldId() ),
								  TTi18n::gettext( 'Legacy other field id is already in use' )
		);

		// Validate default_value against validation rules to help prevent impossible defaults
		$this->ValidateData( $this->getDefaultValue(), $this->Validator, 'default_value' );

		return true;
	}


	/**
	 * Validate custom field data or default_value against user created validation rules.
	 * @param $data
	 * @param Validator $validator
	 * @param bool $validation_field
	 * @return bool
	 */
	function ValidateData( $data, $validator, $validation_field = null ) {
		//Only validate default_value if the user entered a value. Otherwise, the user would be forced to create a default_value if they created validation rules.
		if ( $validation_field == 'default_value' && ( empty( $data ) == true || $data == TTUUID::getZeroID() ) ) {
			return true;
		}

		//Do not validate empty strings if the field is not required, such as an empty string value for an int field.
		if ( $this->getIsRequired() == false && $data === '' ) {
			return true;
		}

		if ( is_array( $this->getCustomFieldMetaData() ) && array_key_exists( 'validation', $this->getCustomFieldMetaData() ) ) {
			$validation_rules = $this->getCustomFieldMetaData()['validation'];
		} else {
			$validation_rules = [];
		}

		if ( $this->getIsRequired() == true && ( empty( $data ) == true || ( is_array( $data ) === false && $data === TTUUID::getZeroID() ) ) ) {
			$validator->isTrue( $validation_field ?? $this->getPrefixedCustomFieldID(),
								false,
								TTi18n::gettext( '%1 must be specified', $this->getName() ) );
		}

		//If a field is not specified at all based on the above "isTrue" validation check, don't bother checking other validation rules on it.
		if ( $validator->isError( $validation_field ?? $this->getPrefixedCustomFieldID() ) == false ) {
			if ( in_array( $this->getType(), [ 400, 410, 420 ] ) && $data != '' ) {
				$validator->isNumeric( $validation_field ?? $this->getPrefixedCustomFieldID(),
									   $data,
									   TTi18n::gettext( '%1 must be numeric', $this->getName() ) );
			}

			if ( $this->getType() == 1000 ) {
				if ( $data != '' ) {
					$validator->isDate( $validation_field ?? $this->getPrefixedCustomFieldID(),
										TTDate::parseDateTime( $data ),
										TTi18n::gettext( '%1 must be a valid date', $this->getName() ) );
				}

				//Min date
				if ( array_key_exists( 'validate_min_date', $validation_rules ) && $validation_rules['validate_min_date'] != '' ) {
					$validator->isGreaterThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
											   TTDate::parseDateTime( $data ),
											   TTi18n::gettext( 'Must be between %1 and %2', [ TTDate::getDate( 'DATE', TTDate::parseDateTime( $validation_rules['validate_min_date'] ) ), TTDate::getDate( 'DATE', TTDate::parseDateTime( $validation_rules['validate_max_date'] ) ) ] ),
											   TTDate::parseDateTime( $validation_rules['validate_min_date'] )
					);
				}

				//Max date
				if ( array_key_exists( 'validate_max_date', $validation_rules ) && $validation_rules['validate_max_date'] != '' ) {
					$validator->isLessThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
											TTDate::parseDateTime( $data ),
											TTi18n::gettext( 'Must be between %1 and %2', [ TTDate::getDate( 'DATE', TTDate::parseDateTime( $validation_rules['validate_min_date'] ) ), TTDate::getDate( 'DATE', TTDate::parseDateTime( $validation_rules['validate_max_date'] ) ) ] ),
											TTDate::parseDateTime( $validation_rules['validate_max_date'] )
					);
				}
			}

			if ( $this->getType() == 1010 && $data != '' ) {
				if ( is_string( $data ) && strpos( $data, ' - ' ) == false ) {
					$validator->isDate( $validation_field ?? $this->getPrefixedCustomFieldID(),
										TTDate::parseDateTime( $data ),
										TTi18n::gettext( '%1 must be a valid date range', $this->getName() ) );
				} else if ( array_key_exists( 'validate_min_date', $validation_rules ) && $validation_rules['validate_min_date'] != '' && $validation_rules['validate_max_date'] != '' ) {
					$dates = is_array( $data ) ? $data : explode( ' - ', $data );

					foreach ( $dates as $date ) {
						//Min date
						$min_result = $validator->isGreaterThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
																 TTDate::parseDateTime( $date ),
																 TTi18n::gettext( 'Date range be between %1 and %2', [ TTDate::getDate( 'DATE', TTDate::parseDateTime( $validation_rules['validate_min_date'] ) ), TTDate::getDate( 'DATE', TTDate::parseDateTime( $validation_rules['validate_max_date'] ) ) ] ),
																 TTDate::parseDateTime( $validation_rules['validate_min_date'] )
						);

						if ( $min_result == false ) {
							break; //Only want to show one error if date range is invalid.
						}

						//Max date
						$max_result = $validator->isLessThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
															  TTDate::parseDateTime( $date ),
															  TTi18n::gettext( 'Date range must be between %1 and %2', [ TTDate::getDate( 'DATE', TTDate::parseDateTime( $validation_rules['validate_min_date'] ) ), TTDate::getDate( 'DATE', TTDate::parseDateTime( $validation_rules['validate_max_date'] ) ) ] ),
															  TTDate::parseDateTime( $validation_rules['validate_max_date'] )
						);

						if ( $max_result == false ) {
							break; //Only want to show one error if date range is invalid.
						}
					}
				}
			}

			if ( $this->getType() == 1200 ) {
				$validator->isDate( $validation_field ?? $this->getPrefixedCustomFieldID(),
									TTDate::parseDateTime( $data ),
									TTi18n::gettext( '%1 must be a valid date/time', $this->getName() ) );
			}

			//Min and Max Length
			if ( array_key_exists( 'validate_min_length', $validation_rules ) && $validation_rules['validate_min_length'] != '' && $validation_rules['validate_max_length'] != '' ) {
				$validator->isGreaterThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										   strlen( $data ),
										   TTi18n::gettext( 'Must be %1 or more characters', [ $validation_rules['validate_min_length'] ] ),
										   $validation_rules['validate_min_length']
				);

				$validator->isLessThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										strlen( $data ),
										TTi18n::gettext( 'Must be %1 or less characters', [ $validation_rules['validate_max_length'] ] ),
										$validation_rules['validate_max_length']
				);
			}

			//Min amount
			if ( array_key_exists( 'validate_min_amount', $validation_rules ) && $validation_rules['validate_min_amount'] != '' ) {
				//If validation rules are set to validate decimal places, round the data to that number of decimal places.
				if ( array_key_exists( 'validate_decimal_places', $validation_rules ) && $validation_rules['validate_decimal_places'] != '' ) {
					$amount = round( (float)$data, $validation_rules['validate_decimal_places'] );
				} else {
					$amount = $data;
				}
				$validator->isGreaterThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										   $amount,
										   TTi18n::gettext( 'Must be %1 or greater', [ $validation_rules['validate_min_amount'] ] ),
										   $validation_rules['validate_min_amount']
				);
			}

			//Max amount
			if ( array_key_exists( 'validate_max_amount', $validation_rules ) && $validation_rules['validate_max_amount'] != '' ) {
				//If validation rules are set to validate decimal places, round the data to that number of decimal places.
				if ( array_key_exists( 'validate_decimal_places', $validation_rules ) && $validation_rules['validate_decimal_places'] != '' ) {
					$amount = round( (float)$data, $validation_rules['validate_decimal_places'] );
				} else {
					$amount = $data;
				}

				$validator->isLessThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										$amount,
										TTi18n::gettext( 'Must be %1 or less', [ $validation_rules['validate_max_amount'] ] ),
										$validation_rules['validate_max_amount']
				);
			}

			//Min time_unit
			if ( array_key_exists( 'validate_min_time_unit', $validation_rules ) && $validation_rules['validate_min_time_unit'] != '' ) {
				$validator->isGreaterThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										   $data,
										   TTi18n::gettext( 'Must be %1 or greater', [ TTDate::convertSecondsToHMS( $validation_rules['validate_min_time_unit'] ) ] ),
										   $validation_rules['validate_min_time_unit']
				);
			}

			//Max time_unit
			if ( array_key_exists( 'validate_max_time_unit', $validation_rules ) && $validation_rules['validate_max_time_unit'] != '' ) {
				$validator->isLessThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										$data,
										TTi18n::gettext( 'Must be %1 or less', [ TTDate::convertSecondsToHMS( $validation_rules['validate_max_time_unit'] ) ] ),
										$validation_rules['validate_max_time_unit']
				);
			}

			//Min datetime
			if ( array_key_exists( 'validate_min_datetime', $validation_rules ) && $validation_rules['validate_min_datetime'] != '' ) {
				$validator->isGreaterThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										   TTDate::parseDateTime( $data ),
										   TTi18n::gettext( 'Must be between %1 and %2', [ TTDate::getDate( 'DATE+TIME', TTDate::parseDateTime( $validation_rules['validate_min_datetime'] ) ), TTDate::getDate( 'DATE+TIME', TTDate::parseDateTime( $validation_rules['validate_max_datetime'] ) ) ] ),
										   TTDate::parseDateTime( $validation_rules['validate_min_datetime'] )
				);
			}

			//Max datetime
			if ( array_key_exists( 'validate_max_datetime', $validation_rules ) && $validation_rules['validate_max_datetime'] != '' ) {
				$validator->isLessThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										TTDate::parseDateTime( $data ),
										TTi18n::gettext( 'Must be between %1 and %2', [ TTDate::getDate( 'DATE+TIME', TTDate::parseDateTime( $validation_rules['validate_min_datetime'] ) ), TTDate::getDate( 'DATE+TIME', TTDate::parseDateTime( $validation_rules['validate_max_datetime'] ) ) ] ),
										TTDate::parseDateTime( $validation_rules['validate_max_datetime'] )
				);
			}

			//Min time
			if ( array_key_exists( 'validate_min_time', $validation_rules ) && $validation_rules['validate_min_time'] != '' ) {
				$validator->isGreaterThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										   TTDate::parseDateTime( $data ),
										   TTi18n::gettext( 'Must be between %1 and %2', [ TTDate::getDate( 'TIME', TTDate::parseDateTime( $validation_rules['validate_min_time'] ) ), TTDate::getDate( 'TIME', TTDate::parseDateTime( $validation_rules['validate_max_time'] ) ) ] ),
										   TTDate::parseDateTime( $validation_rules['validate_min_time'] )
				);
			}

			//Max time
			if ( array_key_exists( 'validate_max_time', $validation_rules ) && $validation_rules['validate_max_time'] != '' ) {
				$validator->isLessThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										TTDate::parseDateTime( $data ),
										TTi18n::gettext( 'Must be between %1 and %2', [ TTDate::getDate( 'TIME', TTDate::parseDateTime( $validation_rules['validate_min_time'] ) ), TTDate::getDate( 'TIME', TTDate::parseDateTime( $validation_rules['validate_max_time'] ) ) ] ),
										TTDate::parseDateTime( $validation_rules['validate_max_time'] )
				);
			}

			//Min amount
			if ( array_key_exists( 'validate_multi_select_min_amount', $validation_rules ) && $validation_rules['validate_multi_select_min_amount'] != '' ) {
				$validator->isGreaterThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										   count( is_array( $data ) ? $data : [] ),
										   TTi18n::gettext( 'Must select more than %1', [ $validation_rules['validate_multi_select_min_amount'] ] ),
										   $validation_rules['validate_multi_select_min_amount']
				);
			}

			//Max amount
			if ( array_key_exists( 'validate_multi_select_max_amount', $validation_rules ) && $validation_rules['validate_multi_select_max_amount'] != '' ) {
				$validator->isLessThan( $validation_field ?? $this->getPrefixedCustomFieldID(),
										count( is_array( $data ) ? $data : [] ),
										TTi18n::gettext( 'Must select %1 or less items', [ $validation_rules['validate_multi_select_max_amount'] ] ),
										$validation_rules['validate_multi_select_max_amount']
				);
			}
		}

		return true;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function isUniqeLegacyOtherId( $id ) {
		$ph = [
				'company_id'            => TTUUID::castUUID( $this->getCompany() ),
				'parent_table'          => $this->getParentTable(),
				'legacy_other_field_id' => (string)$id,
		];

		//get next legacy_id and make sure does not exist

		$query = 'SELECT legacy_other_field_id FROM ' . $this->getTable() . ' WHERE company_id = ? AND parent_table = ? AND legacy_other_field_id = ?';
		$legacy_other_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $legacy_other_id, 'Unique Legacy ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $legacy_other_id === false ) {
			return true;
		} else {
			if ( $legacy_other_id == $this->getLegacyOtherFieldId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @return int
	 */
	function generateLegacyOtherId() {
		$ph = [
				'company_id'            => TTUUID::castUUID( $this->getCompany() ),
				'parent_table'          => $this->getParentTable()
		];

		//Get the highest existing legacy_other_id for this parent_table and increment it by 1.
		$query = 'SELECT MAX(legacy_other_field_id) as max_id FROM ' . $this->getTable() . ' WHERE company_id = ? AND parent_table = ?';

		$result = $this->db->GetOne( $query, $ph );
		if ( $result == null ) {
			return 1;
		}

		return (int)$result + 1;
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		$ph = [
				'company_id'   => TTUUID::castUUID( $this->getCompany() ),
				'name'         => TTi18n::strtolower( trim( $name ) ),
				'parent_table' => $this->getParentTable(),
		];

		$query = 'select id from ' . $this->getTable() . ' where company_id = ? AND lower(name) = ? AND parent_table = ? AND deleted = 0';
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
	 * @return bool
	 */
	function preValidate() {
		$default_value = $this->getDefaultValue();

		if ( empty( $default_value ) == false ) {
			$this->setDefaultValue( $this->castToSQL( $this->getType(), $default_value ) );
		}

		if ( $this->isNew() ) {
			$this->setLegacyOtherFieldId( $this->generateLegacyOtherId() );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//Remove cache for custom field by parent table and company
		$this->removeCache( 'custom_field-' . $this->getCompany() . $this->getParentTable() );
		$this->removeCache( 'custom_field-' . $this->getCompany() );

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
						case 'type':
							$data[$variable] = Option::getByKey( $this->getType(), $this->getOptions( $variable ) );
							break;
						case 'parent':
							$data[$variable] = Option::getByKey( $this->getParentTable(), $this->getOptions( $variable ) );
							break;
						case 'status':
							$data[$variable] = Option::getByKey( $this->getStatus(), $this->getOptions( $variable ) );
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Custom Fields' ), null, $this->getTable(), $this );
	}
}

?>
