<?php
//TimeTrex Schema data class.
class TTS {
	private ?string $table = null;
	private ?factory $factory = null; //Related factory object, such as UserFactory, CompanyFactory, etc...
	private ?array $record_data = null; //Record data that can used to evaluate expressions for conditional fields.
	private ?Permission $permission_obj = null;

	private ?TTSCols $columns = null;
	private ?TTSTabs $tabs = null;
	private ?TTSSearchFields $search_fields = null;
	private ?TTSAPIs $api_methods = null;

	function __construct( ?Factory $factory = null ) {
		$this->factory = $factory;

		$this->permission_obj = new Permission();

		return $this;
	}

	function getBaseClassName(): ?string {
		if ( !isset( $this->factory ) ) {
			return null;
		}

		return str_replace( 'Factory', '', $this->factory::class ?? '' );
	}

	function getFactory(): ?Factory {
		return $this->factory;
	}

	function setTable( string $name ) {
		$this->table = $name;
		return $this;
	}

	function getTable() : ?string {
		return $this->table;
	}

	function setColumns( TTSCols $columns ) : self {
		$this->columns = $columns;
		return $this;
	}

	function getColumns() : ?TTSCols {
		return $this->columns;
	}

	function setTabs( TTSTabs $tabs ) : self {
		$this->tabs = $tabs;
		return $this;
	}

	function getTabs() : ?TTSTabs {
		return $this->tabs;
	}

	//Get all fields without their tabs.
	function getFields(): ?TTSFields {
		$tabs = $this->getTabs();
		if ( $tabs instanceof TTSTabs ) {
			$fields = new TTSFields();
			foreach ( $tabs as $tab ) { /** @var TTSTab $tab */
				$tab_fields = $tab->getFields();
				if ( !empty( $tab_fields ) ) {
					foreach ( $tab_fields as $tab_field ) {
						$fields[$tab_field->getName()] = $tab_field;
					}
				}
			}

			return $fields;
		}

		return null;
	}

	function setSearchFields( TTSSearchFields $search_fields ) : self {
		$this->search_fields = $search_fields;
		return $this;
	}

	function getSearchFields() : ?TTSSearchFields {
		return $this->search_fields;
	}

	function setAPIMethods( TTSAPIs $api_methods ) : self {
		$this->api_methods = $api_methods;
		return $this;
	}

	function getAPIMethods() : ?TTSAPIs {
		return $this->api_methods;
	}

	function setRecordData( ?array $data ) : self {
		$this->record_data = $data;

		//TODO: Clear handler results.
		$fields = $this->getFields();
		if ( !empty( $fields ) ) {
			foreach ( $fields as $field ) {

				$handlers = $field->getHandlers();
				foreach ( $handlers as $name => $handler ) {
					if ( is_object( $handler ) && $field->getHandlerResult( $name ) !== null ) {
						$field->setHandlerResult( $name, null );
					}
				}
			}
		}

		return $this;
	}

	function getRecordData() : ?array {
		return $this->record_data;
	}

	function setPermissionObject( Permission $obj ) : self {
		$this->permission_obj = $obj;
		return $this;
	}
	function getPermissionObject() : ?Permission {
		return $this->permission_obj;
	}

	function executeFieldHandlers( ?string $filter_name = null ) {
		$fields = $this->getFields();
		foreach( $fields as $field ) {

			$handlers = $field->getHandlers( $filter_name );
			foreach( $handlers as $name => $handler ) {
				if ( is_object( $handler ) && $field->getHandlerResult( $name ) === null ) {
					$handler_retval = $handler->eval( $this->getRecordData() );
					$field->setHandlerResult( $name, $handler_retval );
				}
			}
		}

		return true;
	}

	function applyFieldFilters( string $filter_name ) {
		$this->executeFieldHandlers( $filter_name );

		$retarr = [];

		$fields = $this->getFields();
		foreach( $fields as $key => $field ) {
			if ( $field->getHandlerResult( $filter_name ) !== false ) { //Only remove field if the handler result is FALSE, as it could be NULL too.
				//unset( $fields[$key] );
				$retarr[$key] = $field;
			}
		}

		return $retarr;
	}

	function serializeForModel( ?object $objects, ?string $filter = null ) : array {
		$retarr = [];
		if ( $objects instanceof TTSAPIs ) {
			foreach( $objects as $object ) {
				$retarr[$object->getMethod()] = $object->serializeForModel( $filter );
			}
		} else if ( $objects instanceof TTSTabs ) {
			foreach ( $objects as $object ) {
				$retarr[$object->getName()] = $object->serializeForModel( $filter );
			}
		} else if ( $objects instanceof TTSSearchFields ) {
			foreach ( $objects as $object ) {
				$retarr[$object->getName()] = $object->serializeForModel( $filter );
			}
		}

		return $retarr;
	}

	function serializeSchemaData( string $format = 'json' ): string {
		$data = [];

		$data['table'] = $this->getTable();
		$data['tabs'] = $this->serializeForModel( $this->getTabs() );
		$data['search_fields'] = $this->serializeForModel( $this->getSearchFields() );

		return json_encode( $data ); //TODO: Add other formats.
	}
}

class TTSCols extends ArrayObject {
	public function __construct( TTSCol ...$cols ) {
		parent::__construct( $cols );
	}

	static function new( TTSCol ...$cols ) : self {
		return new TTSCols( ...$cols );
	}

	public function append( $value ) : void {
		if ( $value instanceof TTSCol ) {
			parent::append( $value );
		} else {
			throw new Exception( 'Cannot append non Column to a ' . __CLASS__ );
		}
	}

	public function offsetSet( $index, $newval ) : void {
		if ( $newval instanceof TTSCol ) {
			parent::offsetSet( $index, $newval );
		} else {
			throw new Exception( 'Cannot add a non Column value to a ' . __CLASS__ );
		}
	}


	/*
	 * Helper function to add commonly used columns.
	 */

	//Eventually custom fields should be treated like any other field, so we can completely build the UI from a single data structure
	public function addCustomFields() : self {
		return $this;
	}

	//public function addTag() : self {
	//	$this->append( TTSCol::new( 'tag' )->setFunctionMap( 'Tag' )->setIsSynthetic( true ) );
	//
	//	return $this;
	//}

	public function addPermission( string $object_user_id_func, string $created_by_id_func ) : self {
		$this->append( TTSCol::new( 'is_owner' )->setType( 'bool' )->setIsSynthetic( true )->addEventFunction( 'getObjectUserID', $object_user_id_func )->addEventFunction( 'getCreatedBy', $created_by_id_func ) );
		$this->append( TTSCol::new( 'is_child' )->setType( 'bool' )->setIsSynthetic( true )->addEventFunction( 'getObjectUserID', $object_user_id_func )->addEventFunction( 'getCreatedBy', $created_by_id_func ) );

		return $this;
	}

	public function addCreatedAndUpdated() : self {
		$this->append( TTSCol::new( 'created_by' )->setType( 'string' ) ); //Fullname - Specially handled in getObjectAsArray
		//$this->append( TTSCol::new( 'created_by_id' )->setFunctionMap( 'CreatedBy' )->setType( 'uuid' ) );
		//$this->append( TTSCol::new( 'created_date' )->setFunctionMap( 'CreatedDate' )->setType( 'integer' ) );

		$this->append( TTSCol::new( 'created_by_id' )->setType( 'uuid' ) );
		$this->append( TTSCol::new( 'created_date' )->setType( 'integer' ) );

		$this->append( TTSCol::new( 'updated_by' )->setType( 'string' ) ); //Fullname - Specially handled in getObjectAsArray
		$this->append( TTSCol::new( 'updated_by_id' )->setType( 'uuid' ) );
		$this->append( TTSCol::new( 'updated_date' )->setType( 'integer' ) );

		return $this;
	}

	public function addDeleted( bool $add_by_and_date = true, bool $deleted_is_integer = false ): self {
		//TODO:Some tables use integer for deleted instead of a smallint. Until we fix that we need to be able to handle both.
		$this->append( TTSCol::new( 'deleted' )->setFunctionMap( 'Deleted' )->setType( $deleted_is_integer ? 'integer' : 'smallint' )->setDefault( 0 )->setIsNull( false ) );

		if ( $add_by_and_date ) {
			$this->append( TTSCol::new( 'deleted_by' )->setType( 'string' ) ); //Fullname - Specially handled in getObjectAsArray
			$this->append( TTSCol::new( 'deleted_by_id' )->setType( 'uuid' ) );
			$this->append( TTSCol::new( 'deleted_date' )->setType( 'integer' ) );
		}

		return $this;
	}

}

class TTSAPIs extends ArrayObject {
	public function __construct( TTSAPI ...$fields ) {
		parent::__construct( $fields );
	}

	static function new( TTSAPI ...$fields ) : self {
		return new TTSAPIs( ...$fields );
	}

	public function append( $value ) : void {
		if ( $value instanceof TTSAPI ) {
			parent::append( $value );
		} else {
			throw new Exception( 'Cannot append non API method to a ' . __CLASS__ );
		}
	}

	public function offsetSet( $index, $newval ) : void {
		if ( $newval instanceof TTSAPI ) {
			parent::offsetSet( $index, $newval );
		} else {
			throw new Exception( 'Cannot add a non API method value to a ' . __CLASS__ );
		}
	}
}

class TTSSearchFields extends ArrayObject {
	public function __construct( TTSSearchField ...$fields ) {
		parent::__construct( $fields );
	}

	static function new( TTSSearchField ...$fields ) : self {
		return new TTSSearchFields( ...$fields );
	}

	public function append( $value ) : void {
		if ( $value instanceof TTSSearchField ) {
			parent::append( $value );
		} else {
			throw new Exception( 'Cannot append non Search Field to a ' . __CLASS__ );
		}
	}

	public function offsetSet( $index, $newval ) : void {
		if ( $newval instanceof TTSSearchField ) {
			parent::offsetSet( $index, $newval );
		} else {
			throw new Exception( 'Cannot add a non Search Field value to a ' . __CLASS__ );
		}
	}
}

class TTSFields extends ArrayObject {
	public function __construct( TTSField ...$fields ) {
		parent::__construct( $fields );
	}

	static function new( TTSField ...$fields ) : self {
		return new TTSFields( ...$fields );
	}

	public function append( $value ) : void {
		if ( $value instanceof TTSField ) {
			parent::append( $value );
		} else {
			throw new Exception( 'Cannot append non Field to a ' . __CLASS__ );
		}
	}

	public function offsetSet( $index, $newval ) : void {
		if ( $newval instanceof TTSField ) {
			parent::offsetSet( $index, $newval );
		} else {
			throw new Exception( 'Cannot add a non Field value to a ' . __CLASS__ );
		}
	}

	//Eventually custom fields should be treated like any other field, so we can completely build the UI from a single data structure
	public function addCustomFields() : self {
		return $this;
	}

}

class TTSTabs extends ArrayObject {
	public function __construct( TTSTab ...$tabs ) {
		parent::__construct( $tabs );
	}

	static function new( TTSTab ...$tabs ) : self {
		return new TTSTabs( ...$tabs );
	}

	public function append( $value ) : void {
		if ( $value instanceof TTSTab ) {
			parent::append( $value );
		} else {
			throw new Exception( 'Cannot append non Tab to a ' . __CLASS__ );
		}
	}

	public function offsetSet( $index, $newval ) : void {
		if ( $newval instanceof TTSTab ) {
			parent::offsetSet( $index, $newval );
		} else {
			throw new Exception( 'Cannot add a non Tab value to a ' . __CLASS__ );
		}
	}

	public function addAttachment() : self {
		$this->append( TTSTab::new( 'tab_attachment' ) );

		return $this;
	}
	public function addAudit() : self {
		$this->append( TTSTab::new( 'tab_audit' ) );

		return $this;
	}

}

class TTSCol {
	private ?string $name = null;
	private ?string $type = null; //Must default null for Synthetic columns (ie: 'status', 'type' )
	private ?bool $is_nullable = true;
	private ?bool $is_synthetic = false; //Synthetic means the column doesn't actually exist in the database and generated on-the-fly or handled in some other way.
	private ?bool $is_user_visible = true; //Determines if its returned in getObjectAsArray()
	private ?bool $is_explicit_request = false; //These columns must be explicitly requested in the API call as part of $include_columns, they are not returned by default.
	private ?bool $is_writable = true; //Determines if a column is writable or not. Can be used for RecurringScheduleTemplateControlFactory for handling setCreatedBy for example.
	private ?int $length = null;
	private ?string $default = null;

	private bool|string $function_suffix = false; //ie: 'Status' for get/setStatus

	private bool|string|array $getter = false; //ie: 'getOptionByKey','getColumn'
	private bool|string|array $setter = false; //ie: 'Status' for get/setStatus

	private ?array $event_functions = null; //Event => Function map for generic functions that can be linked to this column. Specifically used for permission columns like is_owner/is_child

	function __construct( $name ) {
		$this->setName( $name );
		return $this;
	}

	static function new( $name ) : self {
		return new TTSCol( $name );
	}


	function setName( string $value ) : self {
		$this->name = $value;
		return $this;
	}

	function getName() : string {
		return $this->name;
	}

	function setType( string $value ) : self {
		$this->type = $value;
		return $this;
	}

	function getType() : string {
		return $this->type;
	}

	function setIsNull( bool $value ) : self {
		$this->is_nullable = $value;
		return $this;
	}

	function getIsNull() : bool {
		return $this->is_nullable;
	}

	function setIsSynthetic( bool $value ) : self {
		$this->is_synthetic = $value;
		return $this;
	}

	function getIsSynthetic() : bool {
		return $this->is_synthetic;
	}

	function setIsUserVisible( bool $value ) : self {
		$this->is_user_visible = $value;
		return $this;
	}

	function getIsUserVisible() : bool {
		return $this->is_user_visible;
	}

	function setIsExplicitRequest( bool $value ) : self {
		$this->is_explicit_request = $value;
		return $this;
	}

	function getIsExplicitRequest() : bool {
		return $this->is_explicit_request;
	}

	function setIsWritable( bool $value ) : self {
		$this->is_writable = $value;
		return $this;
	}

	function getIsWritable() : bool {
		return $this->is_writable;
	}

	function setLength( int $value ) : self {
		$this->length = $value;
		return $this;
	}

	function getLength() : int {
		return $this->length;
	}

	function setDefault( string $value ) : self {
		$this->default = $value;
		return $this;
	}

	function getDefault() : string {
		return $this->default;
	}

	function setFunctionMap( bool|string $value ) : self {
		$this->function_suffix = $value;
		return $this;
	}

	function getFunctionMap() : bool|string {
		return $this->function_suffix;
	}


	function setObjectFromArrayFunction( bool|string|Callable $value ) : self {
		$this->setter = $value;
		return $this;
	}

	function getObjectFromArrayFunction() : bool|string|Callable {
		return $this->setter;
	}

	function setObjectAsArrayFunction( bool|string|array $value ) : self {
		$this->getter = $value;
		return $this;
	}

	function getObjectAsArrayFunction() : bool|string|array {
		return $this->getter;
	}

	function addEventFunction( $event, string|TTSPHPCallback $value ) {
		$this->event_functions[$event] = $value;
		return $this;
	}

	function getEventFunction( $event ) : bool|string {
		if ( isset( $this->event_functions[$event] ) ) {
			return $this->event_functions[$event];
		}

		return false;
	}
}

class TTSSearchField {
	private ?string $name = null;
	private ?string $type = null;
	private ?string $width = null; //Width in px or % of the edit field.
	private string|array $col = '';  //DB column name
	private ?string $label = null;  //Edit Field Label
	private ?string $sub_label = null;  //Edit field right-label
	private ?string $description = null;  //Edit field additional description

	private ?string $list_label = null;  //List Field Label

	private ?int $order = 1000;

	private bool $is_multi = false; //Supports passing an array for multi-selection
	private array $visible = [ 'AI' => true, 'UI' => true, 'API' => true ]; //Array like: [ 'UI', 'AI', 'API' ] to determine where this field is visible.

	private ?TTSField $field_obj = null;

	function __construct( $name ) {
		$this->setName( $name );
		$this->setColumn( $name ); //Default database column to field name.
		return $this;
	}

	static function new( $name ) : self {
		return new TTSSearchField( $name );
	}

	function setName( string $value ) : self {
		$this->name = $value;
		return $this;
	}

	function getName() : string {
		return $this->name;
	}

	function setType( string $value ) : self {
		//text
		//int
		//single-dropdown
		//multi-dropdown
		//parent
		//child
		$this->type = $value;
		return $this;
	}

	function getType() : string {
		return $this->type;
	}

	function setWidth( string $value ) : self {
		$this->width = $value;
		return $this;
	}

	function getWidth() : ?string {
		return $this->width;
	}

	function setColumn( string|array $value ) : self {
		$this->col = $value;
		return $this;
	}

	function getColumn() : string|array {
		return $this->col;
	}

	function setLabel( string $value ) : self {
		$this->label = $value;
		return $this;
	}

	function getLabel() : ?string {
		$retval = $this->label;

		if ( $retval === null && $this->getFieldObject() !== null ) {
			$retval = $this->getFieldObject()->getLabel();
		}

		return $retval;
	}

	function setListLabel( string $value ) : self {
		$this->list_label = $value;
		return $this;
	}

	function getListLabel() : ?string {
		return $this->list_label;
	}

	function setSubLabel( string $value ) : self {
		$this->sub_label = $value;
		return $this;
	}

	function getSubLabel() : ?string {
		return $this->sub_label;
	}

	function setDescription( string $value ) : self {
		$this->description = $value;
		return $this;
	}

	function getDescription() : ?string {
		return $this->description;
	}

	function setOrder( int $value ) : self {
		$this->order = $value;
		return $this;
	}

	function getOrder() : int {
		return $this->order;
	}

	function setMulti( bool $value ) : self {
		$this->is_multi = $value;
		return $this;
	}

	function getMulti() : bool {
		return $this->is_multi;
	}

	function setVisible( string|array $namespace, bool $value = true ): self {
		if ( !is_array( $namespace ) ) {
			$namespace = [ strtoupper( $namespace ) ];
		}

		foreach ( $namespace as $tmp_namespace ) {
			$this->visible[strtoupper( $tmp_namespace )] = $value;
		}

		return $this;
	}

	function getVisible() : array {
		return $this->visible;
	}

	function isVisible( $namespace ) {
		if ( isset( $this->visible[$namespace] ) ) {
			return $this->visible[$namespace];
		}

		return false;
	}

	function setFieldObject( TTSField $value ): self {
		$tts_field_visibility = $value->getVisible();
		$tts_search_field_visibility = $this->getVisible();

		ksort( $tts_field_visibility );
		ksort( $tts_search_field_visibility );

		if ( $tts_field_visibility !== $tts_search_field_visibility ) {
			throw new Exception( 'Search field visibility must match the field object visibility.' );
		}

		$this->field_obj = $value;

		return $this;
	}

	function getFieldObject() : ?TTSField {
		return $this->field_obj;
	}

	function serializeForModel( $filter = null ) : array {
		$retarr = [
				'name' => $this->getName(),
				'type' => $this->getType(),
				'col' => $this->getColumn(),
				'label' => $this->getLabel(),
				'list_label' => $this->getListLabel(),
				'sub_label' => $this->getSubLabel(),
				'description' => $this->getDescription(),
				'order' => $this->getOrder(),
				'width' => $this->getWidth(),
				'visible' => $this->getVisible(),
				'is_multi' => $this->getMulti(),
				'field_obj' => $this->getFieldObject()?->serializeForModel(),
		];

		return $retarr;
	}
}

class TTSField {
	private ?string $name = null;
	private ?string $type = null;
	private ?string $width = null; //Width in px or % of the edit field.
	private ?string $col = null;  //DB column name
	private ?string $label = null;  //Edit Field Label
	private ?string $sub_label = null;  //Edit field right-label
	private ?string $description = null;  //Edit field additional description
	private ?string $model_description = null;

	private ?string $list_label = null;  //List Field Label
	private array $visible = [ 'AI' => true, 'UI' => true, 'API' => true ]; //Array like: [ 'UI', 'AI', 'API' ] to determine where this field is visible.

	private ?int $order = 1000;

	private ?TTSAPI $data_source = null;

	private ?array $handlers = [];         //Array of callback functions. ie: 'readonly' => 'isReadOnly()', 'visible' => 'isVisible()', 'onchange' => 'onChange()', ...
	private ?array $handler_results = [];  //Results from executed handlers.

	private array $contexts = [ 'edit' ]; //Edit, List or View (multiple)

	//Array of groups
	private array $groups = []; //Array of groups this field belows to, ie: 'default_display_column', 'model_default_display_column'

	function __construct( $name ) {
		$this->setName( $name );
		$this->setColumn( $name ); //Default database column to field name.
		return $this;
	}

	static function new( $name ) : self {
		return new TTSField( $name );
	}

	function setName( string $value ) : self {
		$this->name = $value;
		return $this;
	}

	function getName() : ?string {
		return $this->name;
	}

	function setType( string $value ) : self {
		//text
		//int
		//single-dropdown
		//multi-dropdown
		//parent
		//child

		$white_list = [
				'uuid', //This could be changed to something else like text? Right now it's mostly for the ID field of a record.
				'text',
				'textarea',
				'checkbox',
				'password',
				'email',
				'integer',
				'numeric',
				'currency', //Should this just be numeric?
				'time',
				'time_unit',
				'date',
				'date_range',
				'multi-date', //Separate from a range, but a UI field that allows multiple dates to be selected.
				'datetime',
				'datetime_range',
				'file',
				'image',
				'color',
				'formula_builder',
				'tag',
				'single-dropdown',
				'multi-dropdown'
		];

		if ( !in_array( $value, $white_list ) ) {
			throw new Exception( 'Invalid field type: ' . $value );
		}

		$this->type = $value;
		return $this;
	}

	function getType() : ?string {
		return $this->type;
	}

	function setVisible( string|array $namespace, bool $value = true ): self {
		if ( !is_array( $namespace ) ) {
			$namespace = [ strtoupper( $namespace ) ];
		}

		foreach ( $namespace as $tmp_namespace ) {
			$this->visible[strtoupper( $tmp_namespace )] = $value;
		}

		return $this;
	}

	function getVisible() : ?array {
		return $this->visible;
	}

	function isVisible( $namespace ) {
		if ( isset( $this->visible[$namespace] ) ) {
			return $this->visible[$namespace];
		}

		return false;
	}

	function setWidth( string $value ) : self {
		$this->width = $value;
		return $this;
	}

	function getWidth() : ?string {
		return $this->width;
	}

	function setColumn( string $value ) : self {
		$this->col = $value;
		return $this;
	}

	function getColumn() : ?string {
		return $this->col;
	}

	function setLabel( string $value ) : self {
		$this->label = $value;
		return $this;
	}

	function getLabel() : ?string {
		return $this->label;
	}

	function setListLabel( string $value ) : self {
		$this->list_label = $value;
		return $this;
	}

	function getListLabel() : ?string {
		return $this->list_label;
	}

	function setSubLabel( string $value ) : self {
		$this->sub_label = $value;
		return $this;
	}

	function getSubLabel() : ?string {
		return $this->sub_label;
	}

	function setModelDescription( string $value ) : self {
		$this->model_description = $value;
		return $this;
	}

	function getModelDescription() : ?string {
		return $this->model_description;
	}

	function setDescription( string $value ) : self {
		$this->description = $value;
		return $this;
	}

	function getDescription() : ?string {
		return $this->description;
	}

	function setOrder( int $value ) : self {
		$this->order = $value;
		return $this;
	}

	function getOrder() : int {
		return $this->order;
	}

	function setDataSource( TTSAPI $value ): self {
		if ( $value->getFilterColumns() === null ) {
			//If TTSAPI does not have default display columns, get them it from the related factory.
			if ( class_exists( $value->getNonAPIClass(), true ) == true ) {
				$factory = TTnew( $value->getNonAPIClass() );

				$factory_filter_columns = $factory->getOptions( 'default_display_columns' );

				//In cases such as PunchControl that does not have 'default_display_columns' at all, false is returned. In this case we cannot automatically set the default display columns from the factory as there are none.
				if ( is_array( $factory_filter_columns ) ) {
					$value->setFilterColumns( $factory->getOptions( 'default_display_columns' ) );
				}
			}
		}

		$this->data_source = $value;

		return $this;
	}

	function getDataSource() : ?TTSAPI {
		return $this->data_source;
	}

	function setHandlerResult( string $name, ?bool $result ) {
		$this->handler_results[$name] = $result;
		return $this;
	}

	function getHandlerResult( string $name ) : ?bool {
		if ( isset( $this->handler_results[$name] ) ) {
			return $this->handler_results[$name];
		}

		return null;
	}

	function setHandler( string $name, TTSLogical|TTSComparison $handler ) : self {
		$this->handlers[$name] = $handler;
		return $this;

	}
	function setHandlers( array $handlers ) : self {
		$this->handlers = $handlers;
		return $this;
	}

	function getHandlers( string $filter_name = null ) : ?array {
		if ( $filter_name != '' ) {
			if ( isset( $this->handlers[$filter_name] ) ) {
				return [ $filter_name => $this->handlers[$filter_name] ] ;
			} else {
				return [];
			}
		} else {
			return $this->handlers;
		}
	}

	function setContexts( string|array $value ) : self {
		if ( is_string( $value ) ) {
			$value = [ $value ];
		}

		$this->contexts = $value;
		return $this;
	}

	function getContexts() : ?array {
		return $this->contexts;
	}

	function serializeForModel( $filter = null ) : array {
		$retarr = [
				'name'              => $this->getName(),
				'type'              => $this->getType(),
				'col'               => $this->getColumn(),
				'label'             => $this->getLabel(),
				'list_label'        => $this->getListLabel(),
				'sub_label'         => $this->getSubLabel(),
				'model_description' => $this->getModelDescription(),
				'description'       => $this->getDescription(),
				'order'             => $this->getOrder(),
				'width'             => $this->getWidth(),
				'visible'           => $this->getVisible(),
				'contexts'          => $this->getContexts(),
				'data_source'       => $this->getDataSource()?->serializeForModel(),
		];

		return $retarr;
	}
}

class TTSTab {
	private ?string $name = null;
	private ?string $label = null;
	private ?int $order = 1000;

	private ?string $html_template = null;

	private bool $is_sub_view = false;
	private bool $is_multi_column = false; //Two column tab, or one column.
	private bool $display_on_mass_edit = true; //Two column tab, or one column.
	private bool $is_show_permission = false; //Shows permission DIV

	private bool $is_visible = true; //Default visible status
	private ?string $func_visible = null; //JS function to call if visible or not

	private array $api_criteria = []; //API criteria to use to get data for this tab.

	private ?string $func_init_callback = null; //JS function to call to get HTML template for this tab.

	private ?TTSFields $fields = null; //Array of TTMFields objects.

	function __construct( $name ) {
		$this->setName( $name );
		return $this;
	}

	static function new( $name ) : self {
		return new TTSTab( $name );
	}

	function setName( string $value ) : self {
		$this->name = $value;
		return $this;
	}

	function getName() : ?string {
		return $this->name;
	}

	function setLabel( string $value ) : self {
		$this->label = $value;
		return $this;
	}
	function getLabel() : ?string {
		return $this->label;
	}

	function setVisbile( bool $value ) : self {
		$this->is_visible = $value;
		return $this;
	}

	function getVisible() : bool {
		return $this->is_visible;
	}

	function setAPICriteria( string $class, string $method, array $args = [] ): self {
		$this->api_criteria = [
				'class'  => $class,
				'method' => $method,
				'args'   => $args,
		];

		return $this;
	}

	function getAPICriteria(): array {
		return $this->api_criteria;
	}

	function setMultiColumn( bool $value ) : self {
		$this->is_multi_column = $value;
		return $this;
	}

	function getMultiColumn() : bool {
		return $this->is_multi_column;
	}

	function getDisplayOnMassEdit() : bool {
		return $this->display_on_mass_edit;
	}

	function setDisplayOnMassEdit( bool $value ) : self {
		$this->display_on_mass_edit = $value;
		return $this;
	}

	function setSubView( bool $value ) : self {
		$this->is_sub_view = $value;
		return $this;
	}

	function getSubView() : bool {
		return $this->is_sub_view;
	}

	function setShowPermission( bool $value ) : self {
		$this->is_show_permission = $value;
		return $this;
	}

	function getShowPermission() : bool {
		return $this->is_show_permission;
	}

	function setInitCallback( string $value ) : self {
		$this->func_init_callback = $value;
		return $this;
	}

	function getInitCallback() : ?string {
		return $this->func_init_callback;
	}

	function setHTMLTemplate( string $value ) : self {
		$this->html_template = $value;
		return $this;
	}

	function getHTMLTemplate() : ?string {
		return $this->html_template;
	}

	function setFields( TTSFields $value ) : self {
		$this->fields = $value;
		return $this;
	}

	function getFields() : ?TTSFields {
		return $this->fields;
	}

	function serializeForModel( $filter = null ) : array {
		$retarr = [
				'name' => $this->getName(),
				'label' => $this->getLabel(),
				'is_visible' => $this->getVisible(),
				'is_multi_column' => $this->getMultiColumn(),
				'is_sub_view' => $this->getSubView(),
				'is_show_permission' => $this->getShowPermission(),
				'func_init_callback' => $this->getInitCallback(),
				'html_template' => $this->getHTMLTemplate(),
				'api_criteria' => $this->getAPICriteria(),
				'fields' => []
		];

		foreach ( $this->getFields() ?? [] as $field ) {
			$retarr['fields'][] = $field->serializeForModel();
		}

		return $retarr;
	}
}

class TTSAPI {
	private ?string $class = null;
	private ?string $method = null;
	private array $args = [];
	private ?array $filter_columns = null;
	private ?array $system_filter_columns = [ 'id', 'is_owner', 'is_child'];

	private ?string $summmary = null;           //Basic summary of what the API method does.
	private ?string $description = null;        //Full description of how the API method works and more in-depth information.
	private ?string $common_description = null; //Common description which can be shared across multiple API methods.
	private array $common_links = [];           //Link which methods share a common description.
	private ?string $model_keywords = null;
	private ?string $args_model_description = null;
	private ?array $prompt_example = null;


	function __construct( $class ) {
		$this->setClass( $class );
		return $this;
	}

	static function new( $class ) : self {
		return new TTSAPI( $class );
	}

	function setClass( string $value ) : self {
		$this->class = $value;
		return $this;
	}

	function getClass() : string {
		return $this->class;
	}

	function getNonAPIClass() : string {
		return str_replace( 'API', '', $this->getClass() ) . 'Factory';
	}

	function getClassShortName(): string {
		return str_replace( 'API', '', $this->getClass() );
	}

	function setMethod( string $value ) : self {
		$this->method = $value;
		return $this;
	}

	function getMethod() : string {
		return $this->method;
	}

	//Set a single arg.
	function setArg( string|TTSSearchField|TTSField $value, $key = null ) : self {
		if ( $key === null ) {
			$current_key = key( $this->args );
			if ( is_int( $current_key ) ) {
				$key = ( $current_key + 1 );
			} else if ( $current_key === null ) {
				$key = 0;
			} else {
				throw new Exception( 'Key must be set when setting a single arg.' );
			}
		}

		$this->args[$key] = $value;
		return $this;
	}

	/**
	 * @return array
	 */
	function getArg() : string {
		return $this->args;
	}

	//Set all args at once.
	function setArgs( array|TTSSearchFields|TTSTabs|TTSFields $value ) : self {
		$new_value = [];

		if ( $value instanceof TTSSearchFields || $value instanceof TTSFields ) {
			foreach( $value as $object ) {
				$new_value[] = $object;
			}
		} else {
			$new_value = $value;
		}

		$this->args = $new_value;
		return $this;
	}

	function getArgs() : array {
		return $this->args;
	}

	function setFilterColumns( ?array $value ) : self {
		$this->filter_columns = $value;
		return $this;
	}

	function getFilterColumns() : ?array {
		return $this->filter_columns;
	}

	function setSystemFilterColumns( array $value ) : self {
		$this->system_filter_columns = $value;
		return $this;
	}

	function getSystemFilterColumns() : ?array {
		return $this->system_filter_columns;
	}

	function setSummary( string $value ) : self {
		$this->summmary = $value;
		return $this;
	}

	function getSummary() : ?string {
		return $this->summmary;
	}

	function setDescription( string $value ) : self {
		$this->description = $value;
		return $this;
	}

	function getDescription() : ?string {
		return $this->description;
	}

	function setCommonDescription( string $value ) : self {
		$this->common_description = $value;
		return $this;
	}

	function getCommonDescription() : ?string {
		return $this->common_description;
	}

	function setCommonLinks( array $value ): self {
		if ( $this->getCommonDescription() === null ) {
			//Currently only support one way links. From a tool with a common description to a different tool with a common description.
			//This may or may not change in the future. There are benefits to quickly seeing all tools which share a common description, rather than searching for links in both directions.
			throw new Exception( 'Common description must be set before setting common links.' );
		}

		$value[] = [ 'class' => $this->getClass(), 'method' => $this->getMethod() ]; //Add the current tool to the list of linked tools.

		$this->common_links = $value;
		return $this;
	}

	function getCommonLinks() : array {
		return $this->common_links;
	}

	function setModelKeywords( string $value ) : self {
		$this->model_keywords = $value;
		return $this;
	}

	function getModelKeywords() : ?string {
		return $this->model_keywords;
	}

	function setArgsModelDescription( string $value ) : self {
		$this->args_model_description = $value;
		return $this;
	}

	function getArgsModelDescription() : ?string {
		return $this->args_model_description;
	}

	function setPromptExample( array $value ) : self {
		$this->prompt_example = $value;
		return $this;
	}

	function getPromptExample() : ?array {
		return $this->prompt_example;
	}

	function _serailizeArg( $args ) {
		if ( !is_iterable( $args ) ) {
			$args = [ $args ];
		}

		foreach( $args as $arg ) {
			if ( is_string( $arg ) ) {
				$retarr[] = [
						'name'        => $arg,
						'type'        => 'string',
						'label'       => $arg,
						'allow_array' => false,
						//'description' => $arg->getModelDescription(),
				];
			} else {
				$retarr[] = [
						'name'        => $arg->getName(),
						'type'        => $arg->getType(),
						'label'       => $arg->getLabel(),
						'allow_array' => ( $arg instanceof TTSSearchField ) ? $arg->getMulti() : false,
						//'description' => $arg->getModelDescription(),
				];
			}
		}

		if ( count($retarr) == 1 ) {
			$retarr = $retarr[0];
		}

		return $retarr;
	}

	function serializeForModel( $filter = null ): array {
		$retarr = [];

		if ( $filter == 'summary' || $filter == null ) {
			$retarr = [
					'class'                 => $this->getClass(),
					'method'                => $this->getMethod(),
					'description'           => $this->getSummary(),
					'prompt_example'        => implode( "\n", $this->getPromptExample() ?? [] ),
					'filter_columns'        => $this->getFilterColumns(),
					'system_filter_columns' => $this->getSystemFilterColumns(),
			];
		}

		if ( $filter == 'args' || $filter == null ) {
			$retarr_args = [];

			$args = $this->getArgs();
			foreach ( $args as $key => $arg ) {
				$retarr_args[$key] = $this->_serailizeArg( $arg );
			}

			$retarr['description'] = $this->getArgsModelDescription();
			$retarr['args'] = $retarr_args;
		}

		return $retarr;
	}
}

class TTSPHPCallback {
	private string|array $method = ''; //Can be an array of [ class, method ]
	private string|array $args = '';

	function __construct( $method ) {
		$this->setMethod( $method );
		return $this;
	}

	static function new( $method ) : self {
		return new TTSPHPCallback( $method );
	}

	function setMethod( string|array $value ) : self {
		$this->method = $value;
		return $this;
	}

	function getMethod() : string|array {
		return $this->method;
	}

	function setArgs( string|array $value ) : self {
		$this->args = $value;
		return $this;
	}

	function getArgs() : string|array {
		return $this->args;
	}



	function exec() {
		return call_user_func( $this->getMethod(), ...$this->getArgs() );
	}
}

class TTSJSCallback {
	private string|array $method = ''; //Can be an array of [ class, method ]
	private string|array $args = '';

	function __construct( $method ) {
		$this->setMethod( $method );
		return $this;
	}

	static function new( $method ) : self {
		return new TTSJSCallback( $method );
	}

	function setMethod( string|array $value ) : self {
		$this->method = $value;
		return $this;
	}

	function getMethod() : string|array {
		return $this->method;
	}

	function setArgs( string|array $value ) : self {
		$this->args = $value;
		return $this;
	}

	function getArgs() : string|array {
		return $this->args;
	}
}

class TTSData {
	private $key = null;

	function __construct( $key ) {
		$this->setKey( $key );
		return $this;
	}

	static function new( $key ) : self {
		return new TTSData( $key );
	}

	static function get( $key ) : self {
		return new TTSData( $key );
	}

	function setKey( $name ) {
		$this->key = $name;
		return $this;
	}

	function getKey() {
		return $this->key;
	}

	function eval( $data ) { //$data = current record data array.
		if ( isset( $data[$this->key])) {
			return $data[$this->key];
		}

		return null;
	}
}

class TTSPermissionCheck {
	private $section;
	private $name;

	public function __construct( string $section, string $name ) {
		$this->section = $section;
		$this->name = $name;

		return $this;
	}

	static function new( string $section, string $name ) : self {
		return new TTSPermissionCheck( $section, $name );
	}

	public function eval( ?array $data = [], ?string $user_id = null, ?string $company_id = null ) : bool {
		//$data is used to get is_owner, is_child variables if needed.
		$permission = new Permission();
		return $permission->Check( $this->section, $this->name, $user_id, $company_id );
	}
}

class TTSComparison {
	private $left;
	private $operator;
	private $right;

	public function __construct( $left, string $operator, $right ) {
		$this->left = $left;
		$this->operator = $operator;
		$this->right = $right;

		return $this;
	}

	static function new( $left, string $operator, $right ) : self {
		return new TTSComparison( $left, $operator, $right );
	}

	public function eval( ?array $data = null ) : bool {
		if ( $this->left instanceof TTSComparison || $this->left instanceof TTSLogical || $this->left instanceof TTSData || $this->left instanceof TTSPermissionCheck ) {
			$left = $this->left->eval( $data );
		} else {
			$left = $this->left;
		}

		if ( $this->right instanceof TTSComparison || $this->right instanceof TTSLogical || $this->right instanceof TTSData || $this->right instanceof TTSPermissionCheck ) {
			$right = $this->right->eval( $data );
		} else {
			$right = $this->right;
		}

		switch ( strtolower( $this->operator ) ) {
			case '==':
				return $left == $right;
			case '!=':
			case '<>':
				return $left != $right;
			case '===':
				return $left === $right;
			case '!==':
				return $left !== $right;
			case '<':
				return $left < $right;
			case '>':
				return $left > $right;
			case '<=':
				return $left <= $right;
			case '>=':
				return $left >= $right;
			case '<=>':
				return $left <=> $right;
			case 'in_array':
				return in_array( $left, $right );
			case 'and':
			case '&&':
			case 'or':
			case '||':
			case 'not':
			case '!':
				return TTSLogical::new( $this->operator, $left, $right )->eval( $data );
			default:
				throw new Exception( 'Invalid operator: ' . $this->operator );
		}
	}
}

class TTSLogical {
	private $operands;
	private $operator;

	public function __construct( string $operator, ...$operands ) {
		$this->operator = $operator;
		$this->operands = $operands;

		return $this;
	}

	static function new( string $operator, ...$operands ) : self {
		return new TTSLogical( $operator, ...$operands );
	}

	public function eval( ?array $data = null ) : bool {
		switch ( strtolower( $this->operator ) ) {
			case 'and':
			case '&&':
				return array_reduce( $this->operands, function ( $carry, $operand ) use ( $data ) {
					if ( $operand instanceof TTSLogical || $operand instanceof TTSComparison || $operand instanceof TTSData || $operand instanceof TTSPermissionCheck ) {
						return ( $carry && $operand->eval( $data ) );
					} else {
						return ( $carry && $operand );
					}
				},                   true );
			case 'or':
			case '||':
				return array_reduce( $this->operands, function ( $carry, $operand ) use ( $data ) {
					if ( $operand instanceof TTSLogical || $operand instanceof TTSComparison || $operand instanceof TTSData || $operand instanceof TTSPermissionCheck ) {
						return ( $carry || $operand->eval( $data ) );
					} else {
						return ( $carry || $operand );
					}
				},                   false );
			case 'not':
				if ( $this->operands[0] instanceof TTSLogical || $this->operands[0] instanceof TTSCOmparison || $this->operands[0] instanceof TTSData || $operand instanceof TTSPermissionCheck ) {
					return !$this->operands[0]->eval( $data );
				} else {
					return !$this->operands[0];
				}
			default:
				if ( count( $this->operands ) != 2 ) {
					throw new Exception( "Comparison operators require exactly two operands." );
				}

				return TTSComparison::new( $this->operands[0], $this->operator, $this->operands[1] )->eval( $data );
		}
	}
}

?>
