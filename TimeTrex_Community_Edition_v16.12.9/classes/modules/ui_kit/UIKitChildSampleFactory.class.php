<?php
/*
 * $License$
 */

/**
 * @package Modules\UIKit
 */
class UIKitChildSampleFactory extends Factory {
	protected $table = 'ui_kit_child';

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'parent_id' )->setFunctionMap( 'Parent' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'company_id' )->setFunctionMap( 'Company' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'combo_box' )->setFunctionMap( 'ComboBox' )->setType( 'integer' )->setIsNull( true ),
							TTSCol::new( 'text_input' )->setFunctionMap( 'TextInput' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'checkbox' )->setFunctionMap( 'Checkbox' )->setType( 'smallint' )->setIsNull( true )
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No Search Fields.
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIUIKitChildSample' )->setMethod( 'getUIKitChildSample' )
									->setSummary( 'Get UIKitChildSample records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIUIKitChildSample' )->setMethod( 'setUIKitChildSample' )
									->setSummary( 'Add or edit UIKitChildSample records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIUIKitChildSample' )->setMethod( 'deleteUIKitChildSample' )
									->setSummary( 'Delete UIKitChildSample records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIUIKitChildSample' )->setMethod( 'getUIKitChildSample' ) ),
											   ) ),
							TTSAPI::new( 'APIUIKitChildSample' )->setMethod( 'getUIKitChildSampleDefaultData' )
									->setSummary( 'Get default UIKitChildSample data used for creating new UIKitChildSamples. Use this before calling setUIKitChildSample to get the correct default data.' ),
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
						'-1010-text_input' => TTi18n::gettext( 'Name' ),
						'-1030-combo_box'  => TTi18n::gettext( 'Combo Box' ),
						'-1070-checkbox'   => TTi18n::gettext( 'Checkbox' ),

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
						'text_input',
						'combo_box',
						'checkbox',

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
				'checkbox'           => 'Checkbox',
				'combo_box'          => 'ComboBox',
				'parent_id'          => 'Parent',
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
	 * Parent ID is the main ui kit sample ID.
	 * @return bool
	 */
	function getParent() {
		return $this->getGenericDataValue( 'parent_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setParent( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'parent_id', $value );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
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
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'UI Kit Child Sample' ), null, $this->getTable(), $this );
	}
}

?>
