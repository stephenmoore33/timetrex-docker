export class UserDefaultViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_default_view_container',

			company_api: null,
			user_preference_api: null,
			hierarchy_control_api: null,

			country_array: null,
			province_array: null,

			notification_type_array: null,
			priority_array: null,
			original_user_preference_notification_data: [],

			e_province_array: null,
			language_array: null,
			date_format_array: null,
			time_format_array: null,
			time_unit_format_array: null,
			distance_format_array: null,
			time_zone_array: null,
			start_week_day_array: null,

			user_preference_notification_api: null,
			user_default_preference_notification_api: null
		} );

		//Community editions can only have 1 new hire default. For those editions do not show the list view.
		if ( Global.getProductEdition() == 10 ) {
			delete options.el;
		}

		super( options );
	}

	init( options ) {

		//Community editions can only have 1 new hire default. For those editions do not show the list view.
		if ( Global.getProductEdition() > 10 ) {
			this.edit_view_tpl = 'UserDefaultEditView.html';
		}

		this.permission_id = 'user_default';
		this.viewId = 'UserDefault';
		this.script_name = 'UserDefaultView';
		this.table_name_key = 'user_default';
		this.context_menu_name = $.i18n._( 'New Hire Defaults' );
		this.api = TTAPI.APIUserDefault;
		this.company_api = TTAPI.APICompany;
		this.user_preference_api = TTAPI.APIUserPreference;
		this.hierarchy_control_api = TTAPI.APIHierarchyControl;
		this.select_company_id = LocalCacheData.getCurrentCompany().id;
		this.user_preference_notification_api = TTAPI.APIUserPreferenceNotification;
		this.user_default_preference_notification_api = TTAPI.APIUserDefaultPreferenceNotification;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	render() {
		super.render();
	}

	initOptions( callBack ) {

		var options = [
			{ option_name: 'language', field_name: 'language', api: this.user_preference_api },
			{ option_name: 'date_format', field_name: 'date_format', api: this.user_preference_api },
			{ option_name: 'time_format', field_name: 'time_format', api: this.user_preference_api },
			{ option_name: 'time_unit_format', field_name: 'time_unit_format', api: this.user_preference_api },
			{ option_name: 'distance_format', field_name: 'distance_format', api: this.user_preference_api },
			{ option_name: 'time_zone', field_name: 'time_zone', api: this.user_preference_api },
			{ option_name: 'start_week_day', field_name: 'start_week_day', api: this.user_preference_api },
			{ option_name: 'country', field_name: 'country', api: this.company_api },
			{ option_name: 'notification_type', field_name: null, api: this.user_preference_notification_api },
			{ option_name: 'priority', field_name: null, api: this.user_preference_notification_api }
		];

		this.initDropDownOptions( options, function( result ) {
			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}
		} );
	}

	initInsideEditorData() {
		var $this = this;

		var filter = {};
		filter.filter_data = {};
		filter.filter_data.user_default_id = $this.current_edit_record.id;

		$this.user_default_preference_notification_api.getUserDefaultPreferenceNotification( filter, true, {
			onResult: function( res ) {

				var data = res.getResult();
				var array_data = [];
				for ( var key in data ) {

					if ( !data.hasOwnProperty( key ) ) {
						continue;
					}

					array_data.push( data[key] );
				}
				array_data = array_data.sort( function( a, b ) {
					return $this.notification_type_array.findIndex( p => p.id === a.type_id ) - $this.notification_type_array.findIndex( p => p.id === b.type_id );
				} );

				$this.original_user_preference_notification_data =  _.map(array_data, _.clone);

				$this.editor.setValue( array_data );

			}
		} );
	}

	insideEditorSetValue( val ) {
		var len = val.length;

		if ( len === 0 ) {
			return;
		}

		this.removeAllRows();
		for ( var i = 0; i < val.length; i++ ) {
			if ( Global.isSet( val[i] ) ) {
				var row = val[i];
				//converting status_id and device_id into boolean values to be used for checkboxes
				row.enabled = row.status_id == 10;
				row.web_push_enabled = row.device_id.includes( 4 );
				row.email_work_enabled = row.device_id.includes( 256 );
				row.email_home_enabled = row.device_id.includes( 512 );
				row.app_push_enabled = row.device_id.includes( 32768 );
				if ( row.type_id.startsWith( 'reminder_' ) ) {
					row.reminder_delay = row.reminder_delay;
				}
				this.addRow( row );
			}
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

		//Enabled
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enabled' } );
		form_item_input.setValue( data.enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 0 ).append( form_item_input );
		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Type
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( this.parent_controller.notification_type_array ) );
		form_item_input.setValue( data.type_id );
		form_item_input.setEnabled( false );
		form_item_input.css( 'text-align-last', 'center' );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 1 ).append( form_item_input );

		//Priority
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'priority_id', set_empty: false } );
		form_item_input.setSourceData( this.parent_controller.priority_array );
		form_item_input.setValue( data.priority_id );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 2 ).append( form_item_input );

		//Web Push
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'web_push_enabled' } );
		form_item_input.setValue( data.web_push_enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 3 ).append( form_item_input );

		//Work Email
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'email_work_enabled' } );
		form_item_input.setValue( data.email_work_enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 4 ).append( form_item_input );

		//Home Email
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'email_home_enabled' } );
		form_item_input.setValue( data.email_home_enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 5 ).append( form_item_input );

		//App Push
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'app_push_enabled' } );
		form_item_input.setValue( data.app_push_enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 6 ).append( form_item_input );

		//Settings
		if ( data.type_id.startsWith( 'reminder_' ) ) {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'reminder_delay', need_parser_sec: true, mode: 'time_unit' } );
			form_item_input.setValue( data.reminder_delay );
			widgets[form_item_input.getField()] = form_item_input;

			var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
			var label = $( '<span class=\'widget-left-label\'> ' + $.i18n._( 'Delay' ) + ': </span>' );

			widgetContainer.append( label );
			widgetContainer.append( form_item_input );

			row.children().eq( 7 ).append( widgetContainer );
		}

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
			data.priority_id = row.priority_id.getValue();
			data.status_id = row.enabled.getValue() === true ? 10 : 20;
			data.device_id = [];
			if ( row.web_push_enabled.getValue() === true )
				data.device_id.push( 4 );
			if ( row.email_work_enabled.getValue() === true )
				data.device_id.push( 256 );
			if ( row.email_home_enabled.getValue() === true )
				data.device_id.push( 512 );
			if ( row.app_push_enabled.getValue() === true )
				data.device_id.push( 32768 );

			if ( data.type_id.startsWith( 'reminder_' ) && row.reminder_delay ) {
				data.reminder_delay = row.reminder_delay.getValue();
			}

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
				$this.onSaveDone( result );
			} );

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	}

	onSaveDone( result ) {
		if ( result && result.isValid() ) {
			if ( Global.getProductEdition() > 10 ) {
				this.search();
			}
			this.removeEditView();
			return true;
		} else {
			return false;
		}
	}

	saveInsideEditorData( callBack ) {

		var data = this.editor.getValue( this.refresh_id );

		for ( var i = 0; i < data.length; i++ ) {
			data[i].user_default_id = this.refresh_id;
		}

		let changed_data = this.getChangedRecords( data, this.original_user_preference_notification_data, ['enabled', 'web_push_enabled', 'email_work_enabled', 'email_home_enabled', 'app_push_enabled'] );

		if ( Array.isArray( changed_data ) && changed_data.length > 0 ) {
			this.user_default_preference_notification_api.setUserDefaultPreferenceNotification( changed_data, {
				onResult: function( res ) {

					if ( res && res.isValid() ) {
						if ( Global.isSet( callBack ) ) {
							callBack();
						}
					} else {
						TAlertManager.showErrorAlert( res );
					}
				}
			} );
		} else {
			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model;
		//Community editions can only have 1 new hire default. For those editions do not show the list view.
		if ( Global.getProductEdition() == 10 ) {
			context_menu_model = {
				exclude: ['default'],
				include: [
					'save',
					'cancel'
				]
			};
		} else {
			context_menu_model = {
				exclude: ['mass_edit'],
				include: [
					'save',
					'cancel'
				]
			};
		}

		return context_menu_model;
	}

	getUserDefaultData( callBack ) {
		var $this = this;

		// First to get current company's user default data, if no have any data to get the default data which has been set up in TTAPI.APIUserDefault.

		$this.api['get' + $this.api.key_name]( {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( Global.isSet( result_data[0] ) ) {
					callBack( result_data[0] );
				} else {
					$this.api['get' + $this.api.key_name + 'DefaultData']( {
						onResult: function( result ) {
							var result_data = result.getResult();
							callBack( result_data );
						}
					} );
				}

			}
		} );
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.initInsideEditorData();
	}

	openEditView() {

		if ( Global.getProductEdition() > 10 ) {
			super.openEditView();
			return;
		}

		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {

				if ( !$this.edit_view ) {
					$this.initEditViewUI( 'UserDefault', 'UserDefaultEditView.html' );
				}

				$this.getUserDefaultData( function( result ) {
					// Waiting for the TTAPI.API returns data to set the current edit record.
					$this.current_edit_record = result;

					$this.initEditView();

				} );

			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( 'UserTitle', 'UserTitleEditView.html' );
			}

		}
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		if ( key === 'country' ) {
			var widget = this.edit_view_ui_dic['province'];
			widget.setValue( null );
			this.onCountryChange();
			return;
		}

		if ( key === 'legal_entity_id' ) {
			var widget = this.edit_view_ui_dic['company_deduction'];
			if ( this.current_edit_record && this.current_edit_record.legal_entity_id !== null ) {
				//Setting setSourceData to false allows the AComboBox to make the API call for getCompanyDeduction with
				//the correct new filter_data. Without setSourceData( false ) the AComboBox will only show the results
				//for the previous API call and potentially the wrong company_deduction data.
				widget.setSourceData( false );
				var args = {};
				args.filter_data = { legal_entity_id: this.current_edit_record.legal_entity_id };
				widget.setDefaultArgs( args );
				widget.setValue( false );
			} else {
				widget.setValue( false );
			}
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setErrorMenu() {

		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var len = context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			let context_btn = context_menu_array[i];
			let id = context_menu_array[i].id;
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );

			switch ( id ) {
				case 'cancel':
					break;
				default:
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
					break;
			}

		}
	}

	onSetSearchFilterFinished() {
		var combo;
		var select_value;
		if ( this.search_panel.getSelectTabIndex() === 0 ) {
			combo = this.basic_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setSearchFilterProvince( select_value );
		}
	}

	onBuildBasicUIFinished() {
		this.basic_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.basic_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setSearchFilterProvince( selectVal );

			this.basic_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	setSearchFilterProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.basic_search_field_ui_dic['province'].setSourceData( [] );

		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );
					$this.basic_search_field_ui_dic['province'].setSourceData( $this.province_array );

				}
			} );
		}
	}

	hierarchyPermissionValidate( p_id, selected_item ) {

		if ( PermissionManager.validate( 'hierarchy', 'edit' ) ||
			PermissionManager.validate( 'user', 'edit_hierarchy' ) ) {

			return true;
		}

		return false;
	}

	checkTabPermissions( tab ) {
		var retval = false;

		switch ( tab ) {
			case 'tab_hierarchy':
				if ( this.select_company_id === LocalCacheData.getCurrentCompany().id ) {
					retval = true;
				}
				break;
			default:
				retval = super.checkTabPermissions( tab );
				break;
		}

		return retval;
	}

	setCurrentEditRecordData() {
		var dont_set_dic = {};
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) || key === 'hierarchy_control' ) {
				switch ( key ) {
					case 'country':
						this.setCountryValue( widget, key );
						break;
					case 'hierarchy_control':
						if ( this.show_hierarchy ) {
							for ( var h_key in this.current_edit_record.hierarchy_control ) {
								var value = this.current_edit_record.hierarchy_control[h_key];
								if ( this.edit_view_ui_dic[h_key] ) {
									widget = this.edit_view_ui_dic[h_key];
									dont_set_dic[h_key] = true;
									widget.setValue( value );
								}
							}
						}
						break;
					case 'company_deduction':
						var args = {};
						args.filter_data = { legal_entity_id: this.current_edit_record.legal_entity_id };
						widget.setDefaultArgs( args );
						widget.setValue( this.current_edit_record.company_deduction );
						break;
					default:
						if ( !dont_set_dic[key] ) {
							widget.setValue( this.current_edit_record[key] );
							break;
						}
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	initDropDownOptions( options, callBack ) {
		var $this = this;
		var len = options.length + 1;
		var complete_count = 0;
		var option_result = [];
		if ( this.hierarchyPermissionValidate() ) {

			$this.hierarchy_control_api.getOptions( 'object_type', {
				onResult: function( res_1 ) {
					var data_1 = res_1.getResult();
					if ( data_1 ) {
						var array = [];

						for ( var key in data_1 ) {
							array.push( { id: Global.removeSortPrefix( key ), value: data_1[key] } );
						}

						$this.hierarchy_ui_model = array;
					}

					complete_count = complete_count + 1;
					if ( complete_count === len ) {
						callBack( option_result );
					}
				}
			} );

		} else {
			this.show_hierarchy = false;
			complete_count = complete_count + 1;
		}

		for ( var i = 0; i < len - 1; i++ ) {
			var option_info = options[i];

			this.initDropDownOption( option_info.option_name, option_info.field_name, option_info.api, onGetOptionResult );

		}

		function onGetOptionResult( result ) {

			option_result.push( result );

			complete_count = complete_count + 1;

			if ( complete_count === len ) {

				callBack( option_result );
			}
		}
	}

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];

		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );

				}
			} );
		}
	}

	eSetProvince( val, refresh ) {
		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.e_province_array = Global.buildRecordArray( res );
					if ( refresh && $this.e_province_array.length > 0 ) {
						$this.current_edit_record.province = $this.e_province_array[0].value;
						province_widget.setValue( $this.current_edit_record.province );
					}
					province_widget.setSourceData( $this.e_province_array );

				}
			} );
		}
	}

	buildEditViewUI() {
		var $this = this;
		super.buildEditViewUI();

		var tab_model = {
			'tab_new_hire_default': { 'label': $.i18n._( 'New Hire Defaults' ) },
			'tab_employee_id': { 'label': $.i18n._( 'Employee Identification' ) },
			'tab_contact_info': { 'label': $.i18n._( 'Contact Information' ) },
			'tab_hierarchy': {
				'label': $.i18n._( 'Hierarchy' ),
				'html_template': this.getHierarchyTabHtml()
			},
			'tab_tax_deduction': { 'label': $.i18n._( 'Taxes & Deductions' ) },
			'tab_employee_preference': { 'label': $.i18n._( 'Preferences' ) },
			'tab_preferences_notification': {
				'label': $.i18n._( 'Notifications' ),
				'html_template': this.getPreferencesNotificationTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		if ( Global.getProductEdition() > 10 ) {
			this.navigation.AComboBox( {
				api_class: TTAPI.APIUserDefault,
				id: this.script_name + '_navigation',
				allow_multiple_selection: false,
				layout_name: 'global_user_default',
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();
		}

		//Tab 0 start
		var tab_new_hire_default = this.edit_view_tab.find( '#tab_new_hire_default' );
		var tab_new_hire_default_column1 = tab_new_hire_default.find( '.first-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_new_hire_default );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_new_hire_default_column1, '' );

		//Display Order
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'display_order' } );
		this.addEditFieldToColumn( $.i18n._( 'Display Order' ), form_item_input, tab_new_hire_default_column1, '' );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'created_by_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Created By' ), form_item_input, tab_new_hire_default_column1 );

		//Tab 1 start
		var tab_employee_id = this.edit_view_tab.find( '#tab_employee_id' );
		var tab_employee_id_column1 = tab_employee_id.find( '.first-column' );
		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_employee_id_column1 );

		//Legal Entity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APILegalEntity,
			allow_multiple_selection: false,
			layout_name: 'global_legal_entity',
			show_search_inputs: true,
//			set_empty: true,
			field: 'legal_entity_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_employee_id_column1 );

		//Permission Group
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPermissionControl,
			allow_multiple_selection: false,
			layout_name: 'global_permission_control',
			set_empty: true,
			show_search_inputs: true,
			field: 'permission_control_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Permission Group' ), form_item_input, tab_employee_id_column1, '' );

		//Terminated Permission Group
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPermissionControl,
			allow_multiple_selection: false,
			layout_name: 'global_permission_control',
			set_empty: true,
			show_search_inputs: true,
			field: 'terminated_permission_control_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Terminated Permission Group' ), form_item_input, tab_employee_id_column1, '' );

		// Pay Period Schedule
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayPeriodSchedule,
			allow_multiple_selection: false,
			layout_name: 'global_pay_period_schedule',
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_period_schedule_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Schedule' ), form_item_input, tab_employee_id_column1 );

		//Policy Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPolicyGroup,
			allow_multiple_selection: false,
			layout_name: 'global_policy_group',
			show_search_inputs: true,
			set_empty: true,
			field: 'policy_group_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Policy Group' ), form_item_input, tab_employee_id_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			field: 'recurring_schedule',
			api_class: TTAPI.APIRecurringScheduleTemplateControl,
			allow_multiple_selection: true,
			layout_name: 'global_recurring_template_control',
			show_search_inputs: true,
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Recurring Schedule' ), form_item_input, tab_employee_id_column1, 'first_last' );

		//Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APICurrency,
			allow_multiple_selection: false,
			layout_name: 'global_currency',
			show_search_inputs: true,
			set_empty: true,
			field: 'currency_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_employee_id_column1 );

		//Title
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUserTitle,
			allow_multiple_selection: false,
			layout_name: 'global_job_title',
			show_search_inputs: true,
			set_empty: true,
			field: 'title_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Title' ), form_item_input, tab_employee_id_column1 );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIBranch,
			allow_multiple_selection: false,
			layout_name: 'global_branch',
			show_search_inputs: true,
			set_empty: true,
			field: 'default_branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Branch' ), form_item_input, tab_employee_id_column1 );

		//Default Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIDepartment,
			allow_multiple_selection: false,
			layout_name: 'global_department',
			show_search_inputs: true,
			set_empty: true,
			field: 'default_department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Department' ), form_item_input, tab_employee_id_column1, '' );

		//Tab 2 start
		var tab_contact_info = this.edit_view_tab.find( '#tab_contact_info' );
		var tab_contact_info_column1 = tab_contact_info.find( '.first-column' );
		this.edit_view_tabs[2] = [];
		this.edit_view_tabs[2].push( tab_contact_info_column1 );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'city', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_contact_info_column1, '' );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( $this.country_array );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_contact_info_column1 );

		//Province / State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'Province / State' ), form_item_input, tab_contact_info_column1 );

		//Work Phone
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'work_phone', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone' ), form_item_input, tab_contact_info_column1 );

		//Work Phone Ext
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'work_phone_ext' } );
		form_item_input.css( 'width', '50' );
		this.addEditFieldToColumn( $.i18n._( 'Work Phone Ext' ), form_item_input, tab_contact_info_column1 );

		//Work Email
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'work_email', width: 219 } );
		this.addEditFieldToColumn( $.i18n._( 'Work Email' ), form_item_input, tab_contact_info_column1, '' );

		//Tab 3 start
		var tab_hierarchy = this.edit_view_tab.find( '#tab_hierarchy' );
		var tab_hierarchy_column1 = tab_hierarchy.find( '.first-column' );
		this.edit_view_tabs[3] = [];
		this.edit_view_tabs[3].push( tab_hierarchy_column1 );

		if ( this.hierarchyPermissionValidate() ) {
			var res = this.hierarchy_control_api.getHierarchyControlOptions( { async: false } );
			$this.hierarchy_options_dic = {};
			var data = res.getResult();
			for ( var key in data ) {
				if ( parseInt( key ) === 200 && Global.getProductEdition() != 25 ) {
					continue;
				}
				$this.hierarchy_options_dic[key] = Global.buildRecordArray( data[key] );
			}
			if ( _.size( $this.hierarchy_options_dic ) > 0 ) {
				$this.show_hierarchy = true;
			} else {
				$this.show_hierarchy = false;
			}
		}

		if ( this.show_hierarchy && this.hierarchy_ui_model ) {
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).css( 'display', 'none' );
			var len = this.hierarchy_ui_model.length;
			for ( var i = 0; i < len; i++ ) {
				var ui_model = this.hierarchy_ui_model[i];
				var options = this.hierarchy_options_dic[ui_model.id];
				if ( options ) {
					form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
					form_item_input.TComboBox( { field: ui_model.id } );
					form_item_input.setSourceData( options );
					this.addEditFieldToColumn( ui_model.value, form_item_input, tab_hierarchy_column1 );
				}
			}
		} else {
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).NoHierarchyBox( { related_view_controller: this } );
			this.edit_view_tab.find( '#tab_hierarchy' ).find( '.hierarchy-div' ).css( 'display', 'block' );
		}

		//Tab 4 start
		var tab_tax_deduction = this.edit_view_tab.find( '#tab_tax_deduction' );
		var tab_tax_deduction_column1 = tab_tax_deduction.find( '.first-column' );
		this.edit_view_tabs[4] = [];
		this.edit_view_tabs[4].push( tab_tax_deduction_column1 );

		// Tax / Deductions
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			field: 'company_deduction',
			layout_name: 'global_deduction',
			api_class: TTAPI.APICompanyDeduction,
			allow_multiple_selection: true,
			set_empty: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Tax & Deductions' ), form_item_input, tab_tax_deduction_column1, 'first_last' );

		//Tab 5 start
		var tab_employee_preference = this.edit_view_tab.find( '#tab_employee_preference' );
		var tab_employee_preference_column1 = tab_employee_preference.find( '.first-column' );
		this.edit_view_tabs[5] = [];
		this.edit_view_tabs[5].push( tab_employee_preference_column1 );

		// Language
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'language', set_empty: true } );
		form_item_input.setSourceData( $this.language_array );
		this.addEditFieldToColumn( $.i18n._( 'Language' ), form_item_input, tab_employee_preference_column1, '' );

		// Date Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'date_format', set_empty: true } );
		form_item_input.setSourceData( $this.date_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Date Format' ), form_item_input, tab_employee_preference_column1 );

		// Time Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_format', set_empty: true } );
		form_item_input.setSourceData( $this.time_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Time Format' ), form_item_input, tab_employee_preference_column1 );

		// Time Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_unit_format', set_empty: true } );
		form_item_input.setSourceData( $this.time_unit_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Time Units' ), form_item_input, tab_employee_preference_column1 );

		// Distance Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'distance_format', set_empty: true } );
		form_item_input.setSourceData( $this.distance_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Distance Units' ), form_item_input, tab_employee_preference_column1 );

		// Time Zone
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_zone', set_empty: true } );
		form_item_input.setSourceData( $this.time_zone_array );
		this.addEditFieldToColumn( $.i18n._( 'Time Zone' ), form_item_input, tab_employee_preference_column1 );

		// Time Zone Auto Detect
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_time_zone_auto_detect' } );
		this.addEditFieldToColumn( $.i18n._( 'Enable Time Zone Auto-Detect' ), form_item_input, tab_employee_preference_column1, '' );

		// Start Weeks on
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'start_week_day' } );
		form_item_input.setSourceData( $this.start_week_day_array );
		this.addEditFieldToColumn( $.i18n._( 'Calendar Starts On' ), form_item_input, tab_employee_preference_column1 );

		// Rows per page
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'items_per_page', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Rows per page' ), form_item_input, tab_employee_preference_column1, '' );

		//Tab 6 start
		var tab_preferences_notification = this.edit_view_tab.find( '#tab_preferences_notification' );
		var tab_preferences_notification_column1 = tab_preferences_notification.find( '.first-column' );
		this.edit_view_tabs[6] = [];
		this.edit_view_tabs[6].push( tab_preferences_notification_column1 );

		var inside_editor_div = tab_preferences_notification.find( '.inside-editor-div' );
		var args = {
			enabled: $.i18n._( 'Enabled' ),
			name: $.i18n._( 'Type' ),
			web: $.i18n._( 'Browser' ),
			email_work: $.i18n._( 'Work Email' ),
			email_home: $.i18n._( 'Home Email' ),
			app: $.i18n._( 'Mobile App' ),
			priority: $.i18n._( 'Priority' ),
			settings: $.i18n._( 'Settings' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			title: '',
			addRow: this.insideEditorAddRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			parent_controller: this,
			render: getRender(),
			render_args: args,
			render_inline_html: true,
			row_render: getRowRender()
		} );

		function getRender() {
			return `
			<table class="inside-editor-render">
				<tr class="title">
					<td style="width: 50px"><%= enabled %></td>
					<td style="width: 400px"><%= name %></td>
					<td style="width: 150px"><%= priority %></td>
					<td style="width: 110px"><%= web %></td>
					<td style="width: 110px"><%= email_work %></td>
					<td style="width: 110px"><%= email_home %></td>
					<td style="width: 110px"><%= app %></td>
					<td style="width: 200px"><%= settings %></td>
				</tr>
			</table>`;
		}

		function getRowRender() {
			return `
			<tr class="inside-editor-row data-row">
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
			</tr>`;
		}

		inside_editor_div.append( this.editor );
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
				label: $.i18n._( 'Country' ),
				in_column: 1,
				field: 'country',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 1,
				field: 'province',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'City' ),
				field: 'city',
				basic_search: true,
				adv_search: false,
				in_column: 1,
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
				script_name: 'EmployeeView',
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
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	convertHierarchyData() {
		this.current_edit_record.hierarchy_control = {
			80: this.current_edit_record['80'] || TTUUID.zero_id,
			90: this.current_edit_record['90'] || TTUUID.zero_id,
			100: this.current_edit_record['100'] || TTUUID.zero_id,
			200: this.current_edit_record['200'] || TTUUID.zero_id,
			1010: this.current_edit_record['1010'] || TTUUID.zero_id,
			1020: this.current_edit_record['1020'] || TTUUID.zero_id,
			1030: this.current_edit_record['1030'] || TTUUID.zero_id,
			1040: this.current_edit_record['1040'] || TTUUID.zero_id,
			1100: this.current_edit_record['1100'] || TTUUID.zero_id
		};
	}

	validate( api ) {
		this.convertHierarchyData();
		super.validate( api );
	}

	onSaveClick( ignoreWarning ) {
		this.convertHierarchyData();
		super.onSaveClick( ignoreWarning );
	}

	getPreferencesNotificationTabHtml() {
		return `<div id="tab_preferences_notification" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_preferences_notification_content_div">
						<div class="first-column full-width-column"></div>
						<div class="inside-editor-div full-width-column"></div>
					</div>
				</div>`;
	}

	getHierarchyTabHtml() {
		return `<div id="tab_hierarchy" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_hierarchy_content_div">
						<div class="first-column full-width-column"></div>
						<div class="hierarchy-div">
							<span class="message"></span>
							<div class="save-and-continue-button-div">
								<button class="tt-button p-button p-component" type="button">
									<span class="icon"></span>
									<span class="p-button-label"></span>
								</button>
							</div>
						</div>
					</div>
				</div>`;
	}

}
