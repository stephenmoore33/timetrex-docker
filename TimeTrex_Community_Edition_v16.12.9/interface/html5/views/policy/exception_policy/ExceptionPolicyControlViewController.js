export class ExceptionPolicyControlViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#exception_policy_control_view_container',

			severity_array: null,
			email_notification_array: null,
			punch_notification_array: null,
			original_exception_data: [],
			editor: null,
			api_exception_policy: null,
			date_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'ExceptionPolicyControlEditView.html';
		this.permission_id = 'exception_policy';
		this.viewId = 'ExceptionPolicyControl';
		this.script_name = 'ExceptionPolicyControlView';
		this.table_name_key = 'exception_policy_control';
		this.context_menu_name = $.i18n._( 'Exception Policy' );
		this.navigation_label = $.i18n._( 'Exception Policy' );
		this.api = TTAPI.APIExceptionPolicyControl;
		this.api_exception_policy = TTAPI.APIExceptionPolicy;
		this.date_api = TTAPI.APITTDate;

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

		this.initDropDownOption( 'severity', 'severity_id', this.api_exception_policy );
		this.initDropDownOption( 'punch_notification', 'punch_notification_id', this.api_exception_policy );
		this.initDropDownOption( 'email_notification', 'email_notification_id', this.api_exception_policy );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_exception_policy': {
				'label': $.i18n._( 'Exception Policy' ),
				'html_template': this.getExceptionPolicyTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIExceptionPolicyControl,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_hierarchy',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_exception_policy = this.edit_view_tab.find( '#tab_exception_policy' );

		var tab_exception_policy_column1 = tab_exception_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_exception_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_exception_policy_column1, 'first_last' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_exception_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Inside editor

		var inside_editor_div = tab_exception_policy.find( '.inside-editor-div' );
		var args = {
			active: $.i18n._( 'Active' ),
			code: $.i18n._( 'Code' ),
			name: $.i18n._( 'Name' ),
			severity: $.i18n._( 'Severity' ),
			grace: $.i18n._( 'Grace' ),
			watch_window: $.i18n._( 'Watch Window' ),
			demerit: $.i18n._( 'Demerit Points' ),
			punch_notification: $.i18n._( 'Punch Notice' ),
			email_notification: $.i18n._( 'Notify' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			title: '',
			addRow: this.insideEditorAddRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			updateAllRows: this.insideEditorUpdateAllRows,
			parent_controller: this,
			render: getRender(),
			render_args: args,
			render_inline_html: true,
			row_render: getRowRender(),
		} );

		function getRender() {
			return `
			<table class="inside-editor-render">
				<tr class="title">
					<td style="width: 50px"><%= active %></td>
					<td style="width: 50px"><%= code %></td>
					<td style="width: 250px"><%= name %></td>
					<td style="width: 90px"><%= severity %></td>
					<td style="width: 90px"><%= grace %></td>
					<td style="width: 140px"><%= watch_window %></td>
					<td style="width: 140px"><%= demerit %></td>
					<td style="width: 140px"><%= punch_notification %></td>
					<td style="width: 140px"><%= email_notification %></td>
				</tr>
			</table>`;
		}

		function getRowRender() {
			return `
			<tr class="inside-editor-row data-row">
				<td class="level cell"></td>
				<td class="code cell"></td>
				<td class="name cell"></td>
				<td class="severity cell"></td>
				<td class="grace cell"></td>
				<td class="watch-window cell"></td>
				<td class="demerit cell"></td>
				<td class="punch-notification cell"></td>
				<td class="email-notification cell"></td>
			</tr>`;
		}

		inside_editor_div.append( this.editor );
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

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.initInsideEditorData();
	}

	initInsideEditorData() {
		var $this = this;
		var args = {};
		args.filter_data = {};

		var exception_control_id = this.current_edit_record.id ? this.current_edit_record.id : this.copied_record_id;
		this.copied_record_id = '';

		if ( !exception_control_id ) {

			this.api_exception_policy.getExceptionPolicyDefaultData( args, true, {
				onResult: function( res ) {

					if ( !$this.edit_view ) {
						return;
					}

					var data = res.getResult();
					var array_data = [];
					for ( var key in data ) {

						if ( !data.hasOwnProperty( key ) ) {
							continue;
						}

						data[key].id = '';
						array_data.push( data[key] );
					}
					array_data = array_data.sort( function( a, b ) {
						return Global.compare( a, b, 'type_id' );
					} );

					$this.original_exception_data = _.map(array_data, _.clone);
					$this.editor.setValue( array_data );

				}
			} );

		} else {

			args.filter_data.exception_policy_control_id = exception_control_id;

			this.api_exception_policy.getExceptionPolicyDefaultData( args, true, {
				onResult: function( res ) {

					if ( !$this.edit_view ) {
						return;
					}

					var data = res.getResult();
					var array_data = [];

					for ( var key in data ) {

						if ( !data.hasOwnProperty( key ) ) {
							continue;
						}

						data[key].id = '';
						array_data.push( data[key] );
					}

					array_data = array_data.sort( function( a, b ) {
						return Global.compare( a, b, 'type_id' );
					} );

					$this.original_exception_data = _.map(array_data, _.clone);
					$this.editor.setValue( array_data );

					var ep_filter = {};
					ep_filter.filter_data = { exception_policy_control_id: exception_control_id };

					$this.api_exception_policy.getExceptionPolicy( ep_filter, true, {
						onResult: function( ep_res ) {

							if ( !$this.edit_view ) {
								return;
							}

							var data = ep_res.getResult();
							var array_data = [];
							for ( var key in data ) {

								if ( !data.hasOwnProperty( key ) ) {
									continue;
								}

								array_data.push( data[key] );
							}

							array_data = array_data.sort( function( a, b ) {
								return Global.compare( a, b, 'type_id' );
							} );

							$this.original_exception_data = _.map(array_data, _.clone);
							$this.editor.setValue( array_data );

						}
					} );

				}
			} );
		}
	}

	insideEditorUpdateAllRows( val ) {
		var len = this.rows_widgets_array.length;
		for ( var i = 0; i < len; i++ ) {
			var c_row = this.rows_widgets_array[i];
			var c_row_data = c_row.current_edit_item;

			var len1 = val.length;

			for ( var j = 0; j < len1; j++ ) {
				var new_row = val[j];

				if ( new_row.type_id === c_row_data.type_id ) {
					c_row.current_edit_item = new_row;

					if ( !this.parent_controller.current_edit_record.id ) {
						c_row.current_edit_item.id = '';
					}

					c_row.active.setValue( new_row.active );
					c_row.severity_id.setValue( new_row.severity_id );

					if ( new_row.is_enabled_grace ) {
						c_row.grace.setValue( new_row.grace );
					}

					if ( new_row.is_enabled_watch_window ) {
						c_row.watch_window.setValue( new_row.watch_window );
					}

					c_row.demerit.setValue( new_row.demerit );

					if ( new_row.is_enabled_punch_notice ) {
						c_row.punch_notification_id.setValue( new_row.punch_notification_id );
					}

					c_row.email_notification_id.setValue( new_row.email_notification_id );

					val.splice( j, 1 );

					break;

				}
			}

		}
	}

	insideEditorSetValue( val ) {
		var len = val.length;

		if ( len === 0 ) {
			return;
		}

		if ( !val[0].id ) {
			this.removeAllRows();
			for ( var i = 0; i < val.length; i++ ) {
				if ( Global.isSet( val[i] ) ) {
					var row = val[i];
					this.addRow( row );
				}
			}
		} else {
			this.updateAllRows( val );
		}
	}

	insideEditorAddRow( data, index ) {
		if ( !data ) {
			data = {};
		}

		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		//Active
		var form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'active' } );
		form_item_input.setValue( data.active );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 0 ).append( form_item_input );
		form_item_input.attr( 'exception_policy_id', ( data.id && this.parent_controller.current_edit_record.id ) ? data.id : '' );
		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Code
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'type_id' } );
		form_item_input.setValue( data.type_id );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 1 ).append( form_item_input );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'name' } );
		form_item_input.setValue( data.name );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 2 ).append( form_item_input );

		//Severity
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'severity_id', set_empty: false } );
		this.setWidgetEnableBaseOnParentController( form_item_input );
		form_item_input.setSourceData( this.parent_controller.severity_array );
		form_item_input.setValue( data.severity_id );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 3 ).append( form_item_input );

		if ( data.is_enabled_grace ) {
			//Grace
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'grace', width: 90, need_parser_sec: true } );
			form_item_input.setValue( data.grace );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 4 ).append( form_item_input );
			this.setWidgetEnableBaseOnParentController( form_item_input );
		}

		if ( data.is_enabled_watch_window ) {
			//Watch Window
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'watch_window', width: 90, need_parser_sec: true } );
			form_item_input.setValue( data.watch_window );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 5 ).append( form_item_input );
			this.setWidgetEnableBaseOnParentController( form_item_input );
		}

		//Demerits
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'demerit', width: 50, need_parser_sec: false } );
		form_item_input.setValue( data.demerit );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 6 ).append( form_item_input );
		this.setWidgetEnableBaseOnParentController( form_item_input );

		if ( data.is_enabled_punch_notice ) {
			//Punch Notification
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'punch_notification_id', set_empty: false } );
			form_item_input.setSourceData( this.parent_controller.punch_notification_array );
			form_item_input.setValue( data.punch_notification_id );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 7 ).append( form_item_input );
			this.setWidgetEnableBaseOnParentController( form_item_input );
		}

		//Email Notification
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'email_notification_id', set_empty: false } );
		form_item_input.setSourceData( this.parent_controller.email_notification_array );
		form_item_input.setValue( data.email_notification_id );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 8 ).append( form_item_input );
		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Save current set item
		widgets.current_edit_item = data;

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

		this.removeLastRowLine();
	}

	insideEditorGetValue( current_edit_item_id ) {

		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = row.current_edit_item;
			data.exception_policy_control_id = current_edit_item_id;
			data.active = row.active.getValue();
			data.severity_id = row.severity_id.getValue();
			if ( data.is_enabled_grace ) {
				data.grace = row.grace.getValue();
			}

			if ( data.is_enabled_watch_window ) {
				data.watch_window = row.watch_window.getValue();
			}

			data.demerit = row.demerit.getValue();

			if ( data.is_enabled_punch_notice ) {
				data.punch_notification_id = row.punch_notification_id.getValue();
			}

			data.email_notification_id = row.email_notification_id.getValue();

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

	onSaveAndCopyResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}

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

		var data = this.editor.getValue( this.refresh_id );

		let changed_data = this.getChangedRecords( data, this.original_exception_data, [] );

		if ( Array.isArray( changed_data ) && changed_data.length > 0 ) {
			this.api_exception_policy.setExceptionPolicy( changed_data, {
				onResult: function( res ) {
					if ( Global.isSet( callBack ) ) {
						callBack();
					}
				}
			} );
		} else {
			if ( Global.isSet( callBack ) ) {
				callBack();
			}
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

	getExceptionPolicyTabHtml() {
		return `<div id="tab_exception_policy" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_exception_policy_content_div">
						<div class="first-column full-width-column"></div>
						<div class="inside-editor-div full-width-column">
						</div>
					</div>
				</div>`;
	}

}
