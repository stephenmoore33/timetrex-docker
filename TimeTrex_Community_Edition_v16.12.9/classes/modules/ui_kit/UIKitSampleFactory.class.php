<?php
/*
 * $License$
 */

/**
 * @package Modules\UIKit
 */
class UIKitSampleFactory extends Factory {
	protected $table = 'ui_kit';

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {

			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'combo_box' )->setFunctionMap( 'ComboBox' )->setType( 'integer' ),
							TTSCol::new( 'combo_box_parent' )->setFunctionMap( 'ComboBoxParent' )->setType( 'varchar' ),
							TTSCol::new( 'combo_box_child' )->setFunctionMap( 'ComboBoxChild' )->setType( 'varchar' ),
							TTSCol::new( 'awesome_box_single' )->setFunctionMap( 'AwesomeBoxSingle' )->setType( 'uuid' ),
							TTSCol::new( 'textarea' )->setFunctionMap( 'Textarea' )->setType( 'varchar' ),
							TTSCol::new( 'text_input' )->setFunctionMap( 'TextInput' )->setType( 'varchar' ),
							TTSCol::new( 'password_input' )->setFunctionMap( 'PasswordInput' )->setType( 'varchar' ),
							TTSCol::new( 'numeric_input' )->setFunctionMap( 'NumericInput' )->setType( 'numeric' ),
							TTSCol::new( 'time_unit' )->setFunctionMap( 'TimeUnit' )->setType( 'integer' ),
							TTSCol::new( 'checkbox' )->setFunctionMap( 'Checkbox' )->setType( 'smallint' ),
							TTSCol::new( 'wysiwg_text' )->setFunctionMap( 'WYSIWGText' )->setType( 'text' ),
							TTSCol::new( 'date' )->setFunctionMap( 'Date' )->setType( 'date' ),
							TTSCol::new( 'time' )->setFunctionMap( 'Time' )->setType( 'timestamptz' ),
							TTSCol::new( 'tag' )->setFunctionMap( 'Tag' )->setType( 'varchar' ),
							TTSCol::new( 'other_json' )->setType( 'json' ),
							TTSCol::new( 'custom_field' )->setType( 'jsonb' )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setTabs(
					TTSTabs::new(
							TTSTab::new( 'tab_text_content_div' )->setLabel( 'Text / Basic' )->setFields(
									new TTSFields(
											TTSField::new( 'text_input' )->setType( 'text' )->setLabel( 'Text Input (Display Name)' ),
											TTSField::new( 'tag' )->setType( 'tag' )->setLabel( 'Tags' ),
											TTSField::new( 'textarea' )->setType( 'textarea' )->setLabel( 'Textarea' ),
											TTSField::new( 'numeric_input' )->setType( 'numeric' )->setLabel( 'Numeric (20,4)' ),
											TTSField::new( 'time_unit' )->setType( 'time_unit' )->setLabel( 'Time Unit' ),
											TTSField::new( 'password_input' )->setType( 'password' )->setLabel( 'Password' ),
											TTSField::new( 'checkbox' )->setType( 'checkbox' )->setLabel( 'Checkbox' ),
									)
							),
							TTSTab::new( 'tab_dropdowns_content_div' )->setLabel( 'Dropdowns' )->setFields(
									new TTSFields(
											TTSField::new( 'combo_box' )->setType( 'single-dropdown' )->setLabel( 'Combo Box' )->setDataSource( TTSAPI::new( 'APIUIKitSample' )->setMethod( 'getOptions' )->setArg( 'combo_box' ) ),
											TTSField::new( 'combo_box_parent' )->setType( 'single-dropdown' )->setLabel( 'Combo Box Parent' )->setDataSource( TTSAPI::new( 'APICompany' )->setMethod( 'getOptions' )->setArg( 'country' ) ),
											TTSField::new( 'combo_box_child' )->setType( 'single-dropdown' )->setLabel( 'Combo Box Child' )->setDataSource( TTSAPI::new( 'APICompany' )->setMethod( 'getOptions' )->setArg( 'province' )->setArg( '$combo_box_parent' ) ),
											TTSField::new( 'awesome_box_multi' )->setType( 'multi-dropdown' )->setLabel( 'Awesome Box Multiple' )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) ),
											TTSField::new( 'awesome_box_single' )->setType( 'single-dropdown' )->setLabel( 'Awesome Box Single' )->setDataSource( TTSAPI::new( 'APIUser' )->setMethod( 'getUser' ) )
									)
							),
							TTSTab::new( 'tab_date_selectors_content_div' )->setLabel( 'Date / Time' )->setFields(
									new TTSFields(
											TTSField::new( 'date' )->setType( 'date' )->setLabel( 'Date' ),
											TTSField::new( 'date_range' )->setType( 'date_range' )->setLabel( 'Date Range' ),
											TTSField::new( 'time' )->setType( 'time' )->setLabel( 'Time' ),
											TTSField::new( 'time_unit' )->setType( 'time_unit' )->setLabel( 'Time Unit' )
									)
							),
							TTSTab::new( 'tab_image_file_content_div' )->setLabel( 'Pickers' )->setFields(
									new TTSFields(
											TTSField::new( 'file' )->setType( 'file' )->setLabel( 'File' ),
											TTSField::new( 'color' )->setType( 'color' )->setLabel( 'Color' )
									)
							),
							TTSTab::new( 'tab_misc_content_div' )->setLabel( 'Misc' )->setFields(
									new TTSFields(
											TTSField::new( 'formula_builder' )->setType( 'formula_builder' )->setLabel( 'Formula' )
									)
							),
							TTSTab::new( 'tab_sub_view' )->setLabel( 'Sub View' )->setInitCallback( 'initSubUIKitChildView' )->setHTMLTemplate( 'this.getBranchEmployeeCriteriaTabHtml' )->setDisplayOnMassEdit( false )->setSubView( true )
					)->addAudit(),
			);
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'id' )->setType( 'single-dropdown' )->setLabel( 'Include UI Kit Sample' )->setVisible( [ 'UI' ], false )->setDataSource( TTSAPI::new( 'APIUIKitSample' )->setMethod( 'getUIKitSample' ) )
							),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true )->setVisible( [ 'UI' ], false )->setFieldObject(
									TTSField::new( 'exclude_id' )->setType( 'single-dropdown' )->setLabel( 'Exclude UI Kit Sample' )->setVisible( [ 'UI' ], false )->setDataSource( TTSAPI::new( 'APIUIKitSample' )->setMethod( 'getUIKitSample' ) )
							),
							TTSSearchField::new( 'text_input' )->setType( 'text' )->setColumn( 'a.text_input' )->setMulti( true )->setFieldObject(
									TTSField::new( 'text_input' )->setType( 'text' )->setLabel( 'Text Input' )
							),
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUIKitSample' )->setMethod( 'getUIKitSample' )
									->setSummary( 'Get UIKit sample records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUIKitSample' )->setMethod( 'setUIKitSample' )
									->setSummary( 'Add or edit UIKit sample records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUIKitSample' )->setMethod( 'deleteUIKitSample' )
									->setSummary( 'Delete UIKit sample records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUIKitSample' )->setMethod( 'getUIKitSample' ) ),
											   ) ),
							TTSAPI::new( 'APIUIKitSample' )->setMethod( 'getUIKitSampleDefaultData' )
									->setSummary( 'Get default UIKit sample data used for creating new samples. Use this before calling setUIKitSample to get the correct default data.' ),
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
			case 'combo_box':
				$retval = [
						10 => TTi18n::gettext( 'Option 1' ),
						20 => TTi18n::gettext( 'Option 2' ),
						30 => TTi18n::gettext( 'Option 3' ),
						40 => TTi18n::gettext( 'Option 4' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1010-text_input'       => TTi18n::gettext( 'Name' ),
						'-1020-tag'              => TTi18n::gettext( 'Tags' ),
						'-1030-combo_box_parent' => TTi18n::gettext( 'Combo Box Parent' ),
						'-1030-combo_box_child'  => TTi18n::gettext( 'Combo Box Child' ),
						'-1040-date'             => TTi18n::gettext( 'Date' ),
						'-1060-time'             => TTi18n::gettext( 'Time' ),
						'-1070-checkbox'         => TTi18n::gettext( 'Checkbox' ),
						'-1080-numeric_input'    => TTi18n::gettext( 'Numeric' ),

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
						'text_input',
						'tag',
						'combo_box_parent',
						'combo_box_child',
						'date',
						'time',
						'checkbox',
						'numeric_input',

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
				'id'                 => 'ID',
				'company_id'         => 'Company',
				'text_input'         => 'TextInput',
				'password_input'     => 'PasswordInput',
				'numeric_input'      => 'NumericInput',
				'time_unit'          => 'TimeUnit',
				'textarea'           => 'Textarea',
				'checkbox'           => 'Checkbox',
				'wysiwg_text'        => 'WYSIWGText',
				'tag'                => 'Tag',
				'combo_box'          => 'ComboBox',
				'combo_box_parent'   => 'ComboBoxParent',
				'combo_box_child'    => 'ComboBoxChild',
				'awesome_box_multi'  => 'AwesomeBoxMulti',
				'awesome_box_single' => 'AwesomeBoxSingle',
				'date'               => 'Date',
				'date_range'         => 'DateRange',
				'time'               => 'Time',
				'color'              => 'Color',
				'formula_builder'    => 'FormulaBuilder',
				'deleted'            => 'Deleted',
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
	 * @return string
	 */
	function getTextInput() {
		return $this->getGenericDataValue( 'text_input' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTextInput( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'text_input', $value );
	}

	/**
	 * @return string
	 */
	function getPasswordInput() {
		return $this->getGenericDataValue( 'password_input' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPasswordInput( $value ) {
		$password = trim( $value );

		//Check to see if the password is hashed and being passed back into itself from the LogDetailFactory or UIKitSample
		if ( strlen( $password ) > 100 && strpos( $password, ':' ) !== false ) {
			Debug::Text( 'Password is hashed, ignoring: ' . $password, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		return $this->setGenericDataValue( 'password_input', TTPassword::encryptPassword( $password, $this->getCompany() ) );
	}

	/**
	 * @return string
	 */
	function getNumericInput() {
		return (float)$this->getGenericDataValue( 'numeric_input' ); //Needs to return float so TTi18n::NumberFormat() can always handle it properly.
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setNumericInput( $value ) {
		//Pull out only digits and periods.
		$value = $this->Validator->stripNonFloat( $value );

		return $this->setGenericDataValue( 'numeric_input', $value );
	}

	/**
	 * @return integer
	 */
	function getTimeUnit() {
		return (int)$this->getGenericDataValue( 'time_unit' );
	}

	function setTimeUnit( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'time_unit', $value );
	}

	/**
	 * @return string
	 */
	function getTag() {
		return $this->getGenericDataValue( 'tag' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTag( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'tag', $value );
	}

	/**
	 * @return string
	 */
	function getTextarea() {
		return $this->getGenericDataValue( 'textarea' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setTextarea( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'textarea', $value );
	}

	/**
	 * @return string
	 */
	function getWYSIWGText() {
		return $this->getGenericDataValue( 'wysiwg_text' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setWYSIWGText( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'wysiwg_text', $value );
	}

	/**
	 * @param bool $value
	 * @return bool
	 */
	function setCheckbox( $value ) {
		return $this->setGenericDataValue( 'checkbox', $this->toBool( $value ) );
	}

	/**
	 * @return bool
	 */
	function getCheckbox() {
		return $this->fromBool( $this->getGenericDataValue( 'checkbox' ) );
	}

	/**
	 * @return bool|int
	 */
	function getComboBox() {
		return $this->getGenericDataValue( 'combo_box' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setComboBox( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'combo_box', $value );
	}

	/**
	 * @return bool|int
	 */
	function getComboBoxParent() {
		return $this->getGenericDataValue( 'combo_box_parent' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setComboBoxParent( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'combo_box_parent', $value );
	}

	/**
	 * @return bool|int
	 */
	function getComboBoxChild() {
		return $this->getGenericDataValue( 'combo_box_child' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setComboBoxChild( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'combo_box_child', $value );
	}

	/**
	 * @return array
	 */
	function getAwesomeBoxMulti() {
		return $this->getGenericJSONDataValue( 'awesome_box_multi' );
	}

	/**
	 * @param array $value records.
	 * @return bool
	 */
	function setAwesomeBoxMulti( $value ) {

		return $this->setGenericJSONDataValue( 'awesome_box_multi', $value );
	}

	/**
	 * @return array
	 */
	function getAwesomeBoxSingle() {
		return $this->getGenericDataValue( 'awesome_box_single' );
	}

	/**
	 * @param array $value records.
	 * @return bool
	 */
	function setAwesomeBoxSingle( $value ) {
		return $this->setGenericDataValue( 'awesome_box_single', TTUUID::castUUID( $value ) );
	}


	/**
	 * @param bool $raw
	 * @return bool|mixed
	 */
	function getDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'date' );
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
	 * @param $value
	 * @return bool
	 */
	function setDate( $value ) {
		return $this->setGenericDataValue( 'date', TTDate::getISODateStamp( $value ) );
	}

	/**
	 * @return array
	 */
	function getDateRange() {
		return $this->getGenericJSONDataValue( 'date_range' );
	}

	/**
	 * @param array $value records.
	 * @return bool
	 */
	function setDateRange( $value ) {
		return $this->setGenericJSONDataValue( 'date_range', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getTime( $raw = false ) {
		$value = $this->getGenericDataValue( 'time' );
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
	 * @param $value
	 * @return bool
	 */
	function setTime( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'time', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getColor() {
		return $this->getGenericJSONDataValue( 'color' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setColor( $value ) {
		$value = trim( $value );

		return $this->setGenericJSONDataValue( 'color', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getFormulaBuilder() {
		return $this->getGenericJSONDataValue( 'formula_builder' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setFormulaBuilder( $value ) {
		$value = trim( $value );

		return $this->setGenericJSONDataValue( 'formula_builder', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		$this->validateCustomFields( $this->getCompany() );


		//$this->Validator->isTrue( 'time_unit',
		//						  false,
		//						  TTi18n::gettext( 'Sample validation error message-1' )
		//);
		//$this->Validator->isTrue( 'time_unit',
		//						  false,
		//						  TTi18n::gettext( 'Sample validation error message-2' )
		//);

		//$this->Validator->Warning( 'time_unit', TTi18n::gettext( 'Sample validation warning message-1' ) );
		//$this->Validator->Warning( 'time_unit', TTi18n::gettext( 'Sample validation warning message-2' ) );

		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		return true;
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
						case 'time':
						case 'date':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						case 'numeric_input':
							$this->$function( TTi18n::parseFloat( $data[$key] ) );
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
						case 'date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
							}
							break;
						case 'time':
							$data[$variable] = ( defined( 'TIMETREX_API' ) ) ? TTDate::getAPIDate( 'TIME', TTDate::strtotime( $this->$function() ) ) : $this->$function();
							break;
						case 'numeric_input':
							$data[$variable] = TTMath::removeTrailingZeros( $this->$function(), 2 );
							break;
						case 'password_input': //Must not be returned to the API ever due to security risks. Replicating that in this UIkit
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'UI Kit Sample' ), null, $this->getTable(), $this );
	}
}

?>
