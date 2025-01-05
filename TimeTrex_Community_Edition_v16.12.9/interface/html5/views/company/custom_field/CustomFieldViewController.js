export class CustomFieldViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#custom_field_view_container',
			type_array: null,
			parent_table_array: null,
			status_array: null,

			original_custom_field_select_items: [],
			authentication_api: null,
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'CustomFieldEditView.html';
		this.permission_id = 'custom_field';
		this.viewId = 'CustomField';
		this.script_name = 'CustomFieldView';
		this.table_name_key = 'custom_field_control';
		this.context_menu_name = $.i18n._( 'Custom Field' );
		this.navigation_label = $.i18n._( 'Custom Field' );
		this.api = TTAPI.APICustomField;
		this.authentication_api = TTAPI.APIAuthentication;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['copy', 'mass_edit'],
			include: []
		};

		return context_menu_model;
	}

	initOptions( callBack ) {
		var options = [
			// { option_name: 'type_id', field_name: 'type_id', api: this.api },
			{ option_name: 'type', api: this.api },
			{ option_name: 'status', api: this.api },
			{ option_name: 'parent_table', field_name: 'parent_table', api: this.api }
		];

		this.initDropDownOptions( options );
	}

	searchDone( result ) {

		super.searchDone();
		Global.refreshCustomFieldCache();
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_custom_field': {
				'label': $.i18n._( 'Custom Field' ),
				'is_multi_column': true,
				'html_template': this.getCustomFieldTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APICustomField,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_custom_field',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_custom_field = this.edit_view_tab.find( '#tab_custom_field' );

		var tab_custom_field_column1 = tab_custom_field.find( '.first-column' );
		var tab_custom_field_column2 = tab_custom_field.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_custom_field_column1 );

		//Status
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_custom_field_column1, '' );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_custom_field_column1, '' );

		// Parent Table
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'parent_table' } );
		form_item_input.setSourceData( $this.parent_table_array );
		this.addEditFieldToColumn( $.i18n._( 'Object Type' ), form_item_input, tab_custom_field_column1, '' );

		// Type
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Field Type' ), form_item_input, tab_custom_field_column1, '', null, true );

		//Display Order
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'display_order' } );
		this.addEditFieldToColumn( $.i18n._( 'Display Order' ), form_item_input, tab_custom_field_column1, '' );

		//Enable Search
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_search' } );
		this.addEditFieldToColumn( $.i18n._( 'Enable Search' ), form_item_input, tab_custom_field_column1, '', null, true );

		//Second Column
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Validation Rules' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_custom_field_column2, '', null, true, false );

		//Default Value
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'default_value' } );
		form_item_input.attr( 'id', 'custom-field-default-value' );
		this.addEditFieldToColumn( $.i18n._( 'Default Value' ), form_item_input, tab_custom_field_column2, '' );

		//Is required
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'is_required' } );
		this.addEditFieldToColumn( $.i18n._( 'Required' ), form_item_input, tab_custom_field_column2, '' );

		//Min Length
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_min_length' } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Length' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Max Length
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_max_length' } );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Length' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Precision (Decimal Places)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_decimal_places' } );
		this.addEditFieldToColumn( $.i18n._( 'Decimal Places' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Min Amount
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_min_amount' } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Amount' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Max Amount
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_max_amount' } );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Amount' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Min Time Unit
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_min_time_unit', mode: 'time_unit', need_parser_sec: true } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Amount' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Max Time Unit
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_max_time_unit', mode: 'time_unit', need_parser_sec: true } );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Amount' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Min Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'validate_min_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Date' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Max Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'validate_max_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Date' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Min time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'validate_min_time', validation_field: 'time_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum time' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Max time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'validate_max_time', validation_field: 'time_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Maximum time' ), form_item_input, tab_custom_field_column2, '', null, true );

		// Multi-select Minimum Amount
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_multi_select_min_amount' } );
		this.addEditFieldToColumn( $.i18n._( 'Multi-select Minimum Selected' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Multi-select Maximum Amount
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'validate_multi_select_max_amount' } );
		this.addEditFieldToColumn( $.i18n._( 'Multi-select Maximum Selected' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Min Datetime
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'validate_min_datetime', mode: 'date_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Date' ), form_item_input, tab_custom_field_column2, '', null, true );

		//Max Datetime
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'validate_max_datetime', mode: 'date_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Date' ), form_item_input, tab_custom_field_column2, '', null, true );

		//
		//Inside editor for dropdowns
		//
		var inside_editor_div = tab_custom_field.find( '.inside-editor-div' );
		var args = {
			id: $.i18n._( 'Item Value' ),
			label: $.i18n._( 'Item Display Label' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			render: getRender(),
			render_args: args,
			render_inline_html: true,
			row_render: getRowRender(),
			parent_controller: this
		} );

		function getRender() {
			return `
			<table class="inside-editor-render">
				<tr class="title">
					<td style="width: 200px"><%= id %></td>
					<td style="width: 200px"><%= label %></td>
					<td style="width: 25px"></td>
					<td style="width: 25px"></td>
				</tr>
			</table>`;
		}

		function getRowRender() {
			return `
			<tr class="inside-editor-row data-row">
				<td class="id cell"></td>
				<td class="label cell"></td>
				<td class="cell control-icon">
					<button class="plus-icon" onclick=""></button>
				</td>
				<td class="cell control-icon">
					<button class="minus-icon " onclick=""></button>
				</td>
			</tr>`;
		}

		inside_editor_div.append( this.editor );
	}


	insideEditorSetValue( val ) {
		var len = val.length;

		this.removeAllRows();
		for ( var i = 0; i < val.length; i++ ) {
			if ( Global.isSet( val[i] ) ) {
				var row = val[i];
				if ( Global.isSet( this.parent_id ) ) {
					row['id'] = '';
				}
				this.addRow( row );
			}

		}
	}

	insideEditorAddRow( data, index ) {
		let is_existing_row = false;

		if ( !data ) {
			data = {};
		} else {
			is_existing_row = true;
		}

		var row_id = data.id ? data.id : TTUUID.generateUUID();

		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		var form_item_input;

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'id', width: 175 } );
		form_item_input.setValue( data.id ? data.id : '' );
		form_item_input.attr( 'item_id', row_id );
		form_item_input.bind( 'formItemChange', function( e, target, doNotValidate ) {
			this.parent_controller.createMetaData( this.parent_controller.current_edit_record.type_id );
			this.parent_controller.updateCustomFieldDropdowns();
		}.bind( this ) );

		if ( is_existing_row && this.parent_controller.is_add == false) {
			form_item_input.setEnabled( false );
		}

		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 0 ).append( form_item_input );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'label', width: 175 } );
		form_item_input.setValue( data.label ? data.label : '' );
		form_item_input.bind( 'formItemChange', function( e, target, doNotValidate ) {
			this.parent_controller.onFormItemChange( target, doNotValidate );
			this.parent_controller.updateCustomFieldDropdowns();
		}.bind( this ) );

		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 1 ).append( form_item_input );

		if ( typeof index != 'undefined' ) {
			row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
			this.rows_widgets_array.splice( ( index ), 0, widgets );

		} else {
			$( render ).append( row );
			this.rows_widgets_array.push( widgets );
		}

		if ( this.parent_controller.is_viewing ) {
			row.find( '.control-icon' ).hide();
		}

		this.addIconsEvent( row ); //Bind event to add and minus icon
		this.removeLastRowLine();
	}

	insideEditorRemoveRow( row ) {
		var index = row[0].rowIndex - 1;
		row.remove();
		this.rows_widgets_array.splice( index, 1 );
		this.removeLastRowLine();

		this.parent_controller.updateCustomFieldDropdowns();
		this.parent_controller.createMetaData( this.parent_controller.current_edit_record.type_id );
	}

	insideEditorGetValue( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];

			var data = {
				label: row.label.getValue(),
				id: row.id.getValue()
			};

			result.push( data );

		}

		return result;
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onTypeChange( true );
		this.initInsideEditorData( true );
	}

	initInsideEditorData( reset_rows ) {
		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( reset_rows ) {
			//On initial open make sure to reset rows, else if user is using navigation arrows the select items from the old record will be shown
			this.editor.removeAllRows();
		}

		if ( this.mass_edit || ( !this.current_edit_record.meta_data.validation.multi_select_items || !this.current_edit_record.meta_data.validation.multi_select_items.length > 0 ) || !this.current_edit_record ) {
			$this.editor.removeAllRows();
			$this.editor.addRow();
			$this.original_custom_field_select_items = [];
		} else {
			$this.original_custom_field_select_items = [];
			for ( var i = 0; i < $this.current_edit_record.meta_data.validation.multi_select_items.length; i++ ) {
				var item = $this.current_edit_record.meta_data.validation.multi_select_items[i];
				if ( item.id !== TTUUID.zero_id && item.id !== TTUUID.not_exist_id ) {
					$this.editor.addRow( item );
					$this.original_custom_field_select_items.push( item );
				}
			}
		}
	}

	saveInsideEditorData( callBack ) {
		this.createMetaData();

		if ( callBack ) {
			callBack();
		}
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'type_id':
				this.onTypeChange( false );
				break;
			case 'parent_table':
				this.onParentTableChange();
				break;
			case 'value':
				this.onSelectValueChange( target[0], c_value );
				break;
		}

		this.createMetaData( this.current_edit_record.type_id );

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onTypeChange( set_default ) {
		this.hideValidationFields();

		if ( this.current_edit_record.type_id == 1010 ) {
			this.detachElement( 'enable_search' );
		} else {
			this.attachElement( 'enable_search' );
		}

		if ( this.current_edit_record.type_id == 100 || this.current_edit_record.type_id == 110 ) {
			this.attachElement( 'validate_min_length' );
			this.attachElement( 'validate_max_length' );
		} else if ( this.current_edit_record.type_id == 400 || this.current_edit_record.type_id == 410 || this.current_edit_record.type_id == 420 ) {
			this.attachElement( 'validate_min_amount' );
			this.attachElement( 'validate_max_amount' );
			if ( this.current_edit_record.type_id == 410 ) {
				this.attachElement( 'validate_decimal_places' );
			}
		} else if ( this.current_edit_record.type_id == 1000 || this.current_edit_record.type_id == 1010 ) {
			this.attachElement( 'validate_min_date' );
			this.attachElement( 'validate_max_date' );
		} else if ( this.current_edit_record.type_id == 1100 ) {
			this.attachElement( 'validate_min_time' );
			this.attachElement( 'validate_max_time' );
		} else if ( this.current_edit_record.type_id == 1200 ) {
			this.attachElement( 'validate_min_datetime' );
			this.attachElement( 'validate_max_datetime' );
		} else if ( this.current_edit_record.type_id == 1300 ) {
			this.attachElement( 'validate_min_time_unit' );
			this.attachElement( 'validate_max_time_unit' );
		} else if ( this.current_edit_record.type_id == 2100 ) {
			$( '#tab_custom_field_content_div #dropdown-editor').show();
		} else if ( this.current_edit_record.type_id == 2110 ) {
			$( '#tab_custom_field_content_div #dropdown-editor').show()
			this.attachElement( 'validate_multi_select_min_amount' );
			this.attachElement( 'validate_multi_select_max_amount' );
		}

		this.changeDefaultValueFormItemType( this.current_edit_record.type_id, set_default );
		this.editFieldResize();
	}

	onParentTableChange() {
		this.setPunchFieldTypes();
	}

	onSelectValueChange(element, value) {
		element.id = value;
	}

	changeDefaultValueFormItemType( new_type_id, set_default ) {
		let target_column = this.edit_view_tab.find( '#tab_custom_field .second-column' );

		let form_array = this.getCustomFieldFormInputByType( new_type_id, 'default_value', this.current_edit_record.meta_data );
		let form_item_input = form_array[0];
		let widget_container = form_array[1];

		form_item_input.attr( 'id', 'custom-field-default-value-new' );
		this.edit_view_ui_dic['default_value'] = form_item_input;
		this.addEditFieldToColumn( $.i18n._( 'Default Value' ), form_item_input, target_column, '', widget_container );
		form_item_input.css( 'opacity', 1 );

		let old_input = document.querySelector( '#custom-field-default-value' );
		let new_input = document.querySelector( '#custom-field-default-value-new' );

		//Swap the old default value input with the new one, then delete the old one.
		let temp_node = document.createElement( "div" );
		old_input.parentNode.insertBefore( temp_node, old_input );
		new_input.parentNode.insertBefore( old_input, new_input );
		temp_node.parentNode.insertBefore( new_input, temp_node );
		temp_node.parentNode.removeChild( temp_node );
		old_input.parentNode.parentNode.remove();

		//Set ID of the new default value input to the old one.
		new_input.id = 'custom-field-default-value';

		if ( set_default ) {
			form_item_input.setValue( this.current_edit_record.default_value );
		} else {
			this.current_edit_record.default_value = '';
		}
	}

	hideValidationFields() {
		this.detachElement( 'validate_min_length' );
		this.detachElement( 'validate_max_length' );
		this.detachElement( 'validate_min_amount' );
		this.detachElement( 'validate_max_amount' );
		this.detachElement( 'validate_decimal_places' );
		this.detachElement( 'validate_min_date' );
		this.detachElement( 'validate_max_date' );
		this.detachElement( 'validate_min_time_unit' );
		this.detachElement( 'validate_max_time_unit' );
		this.detachElement( 'validate_min_time' );
		this.detachElement( 'validate_max_time' );
		this.detachElement( 'validate_min_datetime' );
		this.detachElement( 'validate_max_datetime' );
		this.detachElement( 'validate_multi_select_min_amount' );
		this.detachElement( 'validate_multi_select_max_amount' );

		$( '#tab_custom_field_content_div #dropdown-editor').hide()
	}

	createMetaData( type_id ) {

		type_id = parseInt( type_id ); //Switch is strict on type, so we need to parseInt()

		switch ( type_id ) {
			case 100: //Text
			case 110: //Textarea
				this.current_edit_record.meta_data.validation = {
					'validate_min_length': this.current_edit_record.validate_min_length,
					'validate_max_length': this.current_edit_record.validate_max_length,
				};
				break;
			case 400: //Integer
			case 420: //Currency
				this.current_edit_record.meta_data.validation = {
					'validate_min_amount': this.current_edit_record.validate_min_amount,
					'validate_max_amount': this.current_edit_record.validate_max_amount,
				};
				break;
			case 410: //Decimal
				this.current_edit_record.meta_data.validation = {
					'validate_min_amount': this.current_edit_record.validate_min_amount,
					'validate_max_amount': this.current_edit_record.validate_max_amount,
					'validate_decimal_places': this.current_edit_record.validate_decimal_places,
				};
				break;
			case 1000: //Date
			case 1010: //Date range
				this.current_edit_record.meta_data.validation = {
					'validate_min_date': this.current_edit_record.validate_min_date,
					'validate_max_date': this.current_edit_record.validate_max_date,
				};
				break;
			case 1100: //Time
				this.current_edit_record.meta_data.validation = {
					'validate_min_time': this.current_edit_record.validate_min_time,
					'validate_max_time': this.current_edit_record.validate_max_time,
				};
				break;
			case 1200: //Datetike
				this.current_edit_record.meta_data.validation = {
					'validate_min_datetime': this.current_edit_record.validate_min_datetime,
					'validate_max_datetime': this.current_edit_record.validate_max_datetime,
				};
				break;
			case 1300: //Time Unit
				this.current_edit_record.meta_data.validation = {
					'validate_min_time_unit': this.current_edit_record.validate_min_time_unit,
					'validate_max_time_unit': this.current_edit_record.validate_max_time_unit,
				};
				break;
			case 2100: //Single-select
				this.current_edit_record.meta_data.validation = {
					'multi_select_items': this.editor.getValue().filter( ( item ) => item.id !== '' || item.id !== '' ),
				};
				break;
			case 2110: //Multi-select
				this.current_edit_record.meta_data.validation = {
					'validate_multi_select_min_amount': this.current_edit_record.validate_multi_select_min_amount,
					'validate_multi_select_max_amount': this.current_edit_record.validate_multi_select_max_amount,
					'multi_select_items': this.editor.getValue().filter( ( item ) => item.id !== '' || item.id !== '' ),
				};
				break;
			default:
				this.current_edit_record.meta_data.validation = {}; //Clear all validation fields
				break;
		}
	}

	updateCustomFieldDropdowns() {
		if ( this.current_edit_record.type_id == 2100 || this.current_edit_record.type_id == 2110 ) {
			let source_data = this.editor.getValue();
			let default_data = this.current_edit_record.default_value;
			this.edit_view_ui_dic['default_value'].setSourceData( source_data );
			if ( Array.isArray( source_data ) && source_data.some( item => item.id == default_data ) ) {
				this.edit_view_ui_dic['default_value'].setValue( this.current_edit_record.default_value );
			} else {
				//If more than one item is selected, unselect the removed item only. Otherwise set dropdown to null for no item selected.
				if ( Array.isArray( default_data ) && default_data.length > 1 ) {
					this.edit_view_ui_dic['default_value'].setValue( default_data.filter( default_item => source_data.some( source_item => source_item.id == default_item ) ) );
				} else {
					this.edit_view_ui_dic['default_value'].setValue( null );
				}
			}
		}
	}

	setCurrentEditRecordData() {
		this.parseMetaDataToCurrentEditRecord();
		super.setCurrentEditRecordData();

		if ( this.is_add == false ) {
			this.edit_view_ui_dic['type_id'].setEnabled( false );
			this.edit_view_ui_dic['parent_table'].setEnabled( false );
		}

		this.setPunchFieldTypes();
	}

	setPunchFieldTypes() {

		var punch_allowed_types_api_params = { 'parent_table': this.current_edit_record.parent_table };
		var punch_allowed_types = this.api.getOptions( 'type_id', punch_allowed_types_api_params, { async: false } ).getResult();

		this.edit_view_ui_dic.type_id.setSourceData( punch_allowed_types );

		if ( punch_allowed_types[this.current_edit_record.type_id] ) {
			//Changing source data visually changes the selected item, so we need to set it back to the current value.
			this.edit_view_ui_dic.type_id.setValue( this.current_edit_record.type_id );
		} else {
			//Selected type is not allowed for this parent table. Set to first allowed type.
			this.edit_view_ui_dic.type_id.setValue( Object.keys( punch_allowed_types )[0] );
			this.current_edit_record.type_id = Object.keys( punch_allowed_types )[0];
		}
	}

	parseMetaDataToCurrentEditRecord() {
		for ( var rule in this.current_edit_record.meta_data.validation ) {
			this.current_edit_record[rule] = this.current_edit_record.meta_data.validation[rule];
		}
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Field Type' ),
				in_column: 2,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Object Type' ),
				in_column: 2,
				field: 'parent_table',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
		];
	}

	setDefaultMenuAddIcon( context_btn, grid_selected_length, pId ) {
		//Community edition cannot add custom fields.
		if ( Global.getProductEdition() == 10 ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		} else {
			super.setDefaultMenuAddIcon( context_btn, grid_selected_length, pId );
		}
	}

	getCustomFieldTabHtml() {
		return `<div id="tab_custom_field" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_custom_field_content_div">
						<div class="first-column"></div>
						<div class="second-column"></div>
						<div class="inside-editor-div full-width-column" id="dropdown-editor">
						</div>
					</div>
				</div>`;
	}

	copyAsNewResetIds( data ) {
		if ( this.edit_view_ui_dic['type_id'] && this.edit_view_ui_dic['parent_table'] ) {
			//If copying as new while on a record instead of list view, these may already be disabled.
			//Copy as new does not build a new edit view and instead resets the current_edit_record.id.
			this.edit_view_ui_dic['type_id'].setEnabled( true );
			this.edit_view_ui_dic['parent_table'].setEnabled( true );
		}
		return super.copyAsNewResetIds( data );
	}

	onSaveDone( result ) {
		//Clearing cache to prevent issues with report display columns showing outdated cached results.
		Global.clearCache( 'getOptions_columns' );
		Global.clearCache( 'getOptions_debit_credit_variables' ); //PayStubEntryAccount variable selector
		Global.clearCache( 'getOptions_formula_columns' ); //Custom columns variable selector
		super.onSaveDone( result );
	}

}
