export class HierarchyControlViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#hierarchy_control_view_container',



			object_type_array: null,
			editor: null,
			original_hierarchy_data: [],

			hierarchy_level_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'HierarchyControlEditView.html';
		this.permission_id = 'hierarchy';
		this.viewId = 'HierarchyControl';
		this.script_name = 'HierarchyControlView';
		this.table_name_key = 'hierarchy_control';
		this.context_menu_name = $.i18n._( 'Hierarchy' );
		this.navigation_label = $.i18n._( 'Hierarchy' );
		this.api = TTAPI.APIHierarchyControl;
		this.hierarchy_level_api = TTAPI.APIHierarchyLevel;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['mass_edit'],
			include: []
		};

		return context_menu_model;
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'object_type', 'object_type' );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_hierarchy': {
				'label': $.i18n._( 'Hierarchy' ),
				'html_template': this.getHierarchyControlTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIHierarchyControl,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_hierarchy',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_hierarchy = this.edit_view_tab.find( '#tab_hierarchy' );

		var tab_hierarchy_column1 = tab_hierarchy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_hierarchy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_hierarchy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_hierarchy_column1 );

		form_item_input.parent().width( '45%' );

		// Objects

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			allow_multiple_selection: true,
			layout_name: 'global_option_column',
			show_search_inputs: false,
			set_empty: true,
			key: 'value',
			field: 'object_type'
		} );
		form_item_input.setSourceData( $this.object_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Objects' ), form_item_input, tab_hierarchy_column1 );

		// Subordinates

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'user'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Subordinates' ), form_item_input, tab_hierarchy_column1, '' );

		//Inside editor

		var inside_editor_div = tab_hierarchy.find( '.inside-editor-div' );
		var args = {
			level: $.i18n._( 'Level' ),
			superiors: $.i18n._( 'Superiors' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			title: $.i18n._( 'NOTE: Level one denotes the top or last level of the hierarchy and employees at the same level share responsibilities.' ),
			addRow: this.insideEditorAddRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			removeRow: this.insideEditorRemoveRow,
			parent_controller: this,
			render: getRender(),
			render_args: args,
			api: this.hierarchy_level_api,
			render_inline_html: true,
			row_render: getRowRender()
		} );

		function getRender() {
			return `
				<table class="inside-editor-render">
					<tr class="title">
						<td style="width: 50px"><%= level %></td>
						<td style="width: 200px"><%= superiors %></td>
						<td style="width: 25px">
						</td>
						<td style="width: 25px"></td>
					</tr>
				</table>`;
		}

		function getRowRender() {
			return `
			<tr class="inside-editor-row data-row">
				<td class="level cell"></td>
				<td class="superiors awesome-box-cell"></td>
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

		if ( len > 0 ) {
			for ( var i = 0; i < val.length; i++ ) {
				if ( Global.isSet( val[i] ) ) {
					var row = val[i];

					if ( !this.parent_controller.current_edit_record.id ) { //Copy as New, clear the ID field.
						row.id = '';
					}

					this.addRow( row );
				}
			}
		} else {
			this.getDefaultData();
		}
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.initInsideEditorData();
	}

	initInsideEditorData() {
		var $this = this;

		var args = {};
		args.filter_data = {};
		args.filter_data.hierarchy_control_id = this.current_edit_record.id ? this.current_edit_record.id : ( this.copied_record_id ? this.copied_record_id : '' );

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.copied_record_id ) {
			this.editor.addRow();
		} else {
			this.hierarchy_level_api.getHierarchyLevel( args, true, {
				onResult: function( res ) {
					if ( !$this.edit_view ) {
						return;
					}
					var data = res.getResult();

					$this.original_hierarchy_data = _.map(data, _.clone);

					$this.editor.setValue( data );

				}
			} );
		}
	}

	insideEditorRemoveRow( row ) {
		var index = row[0].rowIndex - 1;
		var remove_id = this.rows_widgets_array[index].current_edit_item.id;
		if ( TTUUID.isUUID( remove_id ) && remove_id != TTUUID.zero_id && remove_id != TTUUID.not_exist_id ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		this.rows_widgets_array.splice( index, 1 );
		this.removeLastRowLine();
	}

	insideEditorAddRow( data, index ) {
		if ( !data ) {
			data = {};
		}

		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		//Level
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'level', width: 50 } );
		form_item_input.setValue( data.level ? data.level : 1 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 0 ).append( form_item_input );

		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Superiors
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			width: 132,
			is_static_width: true,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );
		widgets[form_item_input.getField()] = form_item_input;
		form_item_input.setValue( data.user_id ? data.user_id : '' );
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

		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Save current set item
		widgets.current_edit_item = data;

		if ( !this.parent_controller.current_edit_record.id ) {
			widgets.current_edit_item.id = '';
		}

		this.addIconsEvent( row ); //Bind event to add and minus icon
		this.removeLastRowLine();
	}

	insideEditorGetValue( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = { level: row.level.getValue(), user_id: row.user_id.getValue() };
			data.hierarchy_control_id = current_edit_item_id;
			data.id = row.current_edit_item.id ? row.current_edit_item.id : '';
			result.push( data );

		}

		return result;
	}

	onSaveResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;
			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}
			$this.saveInsideEditorData( function() {
				$this.search();
				$this.onSaveDone( result );

				$this.removeEditView();
			} );

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	}

	// onSaveAndContinueResult( result ) {
	// 	var $this = this;
	// 	if ( result.isValid() ) {
	// 		var result_data = result.getResult();
	// 		if ( result_data === true ) {
	// 			$this.refresh_id = $this.current_edit_record.id;
	//
	// 		} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
	// 			$this.refresh_id = result_data;
	//
	// 		}
	//
	// 		$this.saveInsideEditorData( function() {
	//
	// 			$this.search( false );
	// 			$this.onEditClick( $this.refresh_id, true );
	//
	// 			$this.onSaveAndContinueDone( result );
	//
	// 		} );
	//
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	// onSaveAndNewResult: function( result ) {
	// 	var $this = this;
	// 	if ( result.isValid() ) {
	// 		var result_data = result.getResult();
	// 		if ( result_data === true ) {
	// 			$this.refresh_id = $this.current_edit_record.id;
	//
	// 		} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
	// 			$this.refresh_id = result_data;
	// 		}
	//
	// 		$this.saveInsideEditorData( function() {
	// 			$this.search( false );
	// 			$this.onAddClick( true );
	// 		} );
	//
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	onSaveAndCopyResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}
			$this.copied_record_id = $this.refresh_id;

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onCopyAsNewClick();
			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	}

	saveInsideEditorData( callBack ) {

		var $this = this;

		var data = this.editor.getValue( this.refresh_id );

		var remove_ids = this.editor.delete_ids;

		if ( remove_ids.length > 0 ) {
			this.hierarchy_level_api.deleteHierarchyLevel( remove_ids, {
				onResult: function( res ) {
					$this.editor.delete_ids = [];
				}
			} );
		}

		let changed_data = this.getChangedRecords( data, this.original_hierarchy_data, [] );

		if ( Array.isArray( changed_data ) && changed_data.length > 0 ) {
			this.hierarchy_level_api.ReMapHierarchyLevels( data, { //Not sending changed data as the API expects all records.
				onResult: function( res ) {

					var res_data = res.getResult();
					$this.hierarchy_level_api.setHierarchyLevel( res_data, {
						onResult: function( re ) {

							callBack();
						}
					} );
				}
			} );
		} else {
			callBack();
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
				label: $.i18n._( 'Description' ),
				in_column: 1,
				field: 'description',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Superior' ),
				in_column: 1,
				field: 'superior_user_id',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Subordinate' ),
				in_column: 1,
				field: 'user_id',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Object Type' ),
				in_column: 2,
				field: 'object_type',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			//
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	_continueDoCopyAsNew() {
		this.setCurrentEditViewState( 'new' );
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		if ( Global.isSet( this.edit_view ) ) {
			for ( var i = 0; i < this.editor.rows_widgets_array.length; i++ ) {
				this.editor.rows_widgets_array[i].current_edit_item.id = '';
			}
		}
		super._continueDoCopyAsNew();
	}

	onCopyAsNewResult( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];

		this.copied_record_id = result_data.id;
		result_data.id = '';

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	}

	getHierarchyControlTabHtml() {
		return `<div id="tab_hierarchy" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_hierarchy_content_div">
						<div class="first-column full-width-column"></div>
						<div class="inside-editor-div full-width-column">
						</div>
					</div>
				</div>`;
	}

}
