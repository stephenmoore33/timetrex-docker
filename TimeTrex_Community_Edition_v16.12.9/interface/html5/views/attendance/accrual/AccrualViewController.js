export class AccrualViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#accrual_view_container',
			type_array: null,

			user_group_api: null,
			user_group_array: null,
			user_type_array: null,
			system_type_array: null,
			delete_type_array: null,
			date_api: null,

			edit_enabled: false,
			delete_enabled: false,

			is_trigger_add: false,

			sub_view_grid_data: null,

			hide_search_field: false,

			api_accrual_balance: null,

//	  parent_filter: null,

		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'AccrualEditView.html';
		this.permission_id = 'accrual';
		this.viewId = 'Accrual';
		this.script_name = 'AccrualView';
		this.table_name_key = 'accrual';
		this.context_menu_name = $.i18n._( 'Accruals' );
		this.navigation_label = $.i18n._( 'Accrual' );

		this.api = TTAPI.APIAccrual;
		this.api_accrual_balance = TTAPI.APIAccrualBalance;

		this.initPermission();
		this.render();

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}
		TTPromise.resolve( 'AccrualViewController', 'init' );
	}

	initPermission() {

		super.initPermission();

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.hide_search_field = false;
		} else {
			this.hide_search_field = true;
		}
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'user_type', null, null, function( res ) {
			var result = res.getResult();
			$this.user_type_array = result;

		} );
		this.initDropDownOption( 'delete_type', null, null, function( res ) {
			var result = res.getResult();
			$this.delete_type_array = result;

		} );

		this.initDropDownOption( 'type', null, null, function( res ) {
			var result = res.getResult();
			$this.system_type_array = result;
			if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['type_id'] ) {
				$this.basic_search_field_ui_dic['type_id'].setSourceData( Global.buildRecordArray( result ) );
			}
		} );

		TTAPI.APIUserGroup.getUserGroup( '', false, false, {
			onResult: function( res ) {

				res = res.getResult();
				res = Global.buildTreeRecord( res );

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				}

				$this.user_group_array = res;

			}
		} );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_accrual': { 'label': $.i18n._( 'Accrual' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIAccrual,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_accrual_accrual',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_accrual = this.edit_view_tab.find( '#tab_accrual' );

		var tab_accrual_column1 = tab_accrual.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_accrual_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Employee

		if ( this.sub_view_mode && ( this.parent_edit_record === undefined || _.isEmpty( this.parent_edit_record ) === false ) ) {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'full_name' } );
			this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_accrual_column1, '' );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIUser,
				allow_multiple_selection: true,
				layout_name: 'global_user',
				show_search_inputs: true,
				set_empty: true,
				field: 'user_id'
			} );

			var default_args = {};
			default_args.permission_section = 'accrual';
			form_item_input.setDefaultArgs( default_args );
			this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_accrual_column1, '' );
		}

		// Accrual Policy Account

		if ( this.sub_view_mode && ( this.parent_edit_record === undefined || _.isEmpty( this.parent_edit_record ) === false ) ) {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'accrual_policy_account' } );
			this.addEditFieldToColumn( $.i18n._( 'Accrual Account' ), form_item_input, tab_accrual_column1 );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIAccrualPolicyAccount,
				allow_multiple_selection: false,
				layout_name: 'global_accrual_policy_account',
				show_search_inputs: true,
				set_empty: true,
				field: 'accrual_policy_account_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Accrual Account' ), form_item_input, tab_accrual_column1 );

		}

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.user_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_accrual_column1 );

		// Amount
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'amount', width: 120, mode: 'time_unit' } );

		var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var release_balance_button = $( '<input id=\'release-balance-button\' class=\'t-button\' style=\'margin-left: 5px\' type=\'button\' value=\'' + $.i18n._( 'Available Balance' ) + '\'>' );
		release_balance_button.click( function() {
			$this.getAvailableBalance();
		} );
		if ( this.is_viewing ) {
			release_balance_button.css( 'display', 'none' );
		}

		widgetContainer.append( form_item_input );
		widgetContainer.append( release_balance_button );

		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_accrual_column1, '', widgetContainer );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'time_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_accrual_column1, '', null );

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( {
			field: 'note'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_accrual_column1, '', null, null, true );
	}

	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		// Error: TypeError: this.current_edit_record is null in interface/html5/framework/jquery.min.js?v=9.0.5-20151222-094938 line 2 > eval line 1409
		if ( !this.current_edit_record ) {
			return;
		}
		this.current_edit_record[key] = c_value;
		switch ( key ) {
			case 'amount':
				this.current_edit_record[key] = Global.parseTimeUnit( c_value );
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	buildSearchFields() {
		super.buildSearchFields();

		var default_args = {};
		default_args.permission_section = 'accrual';

		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				field: 'user_id',
				in_column: 1,
				default_args: default_args,
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Accrual Account' ),
				field: 'accrual_policy_account_id',
				in_column: 1,
				layout_name: 'global_accrual_policy_account',
				api_class: TTAPI.APIAccrualPolicyAccount,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 1,
				multiple: true,
				field: 'group_id',
				layout_name: 'global_tree_column',
				tree_mode: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: 'global_branch',
				api_class: TTAPI.APIBranch,
				multiple: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				in_column: 2,
				field: 'default_department_id',
				layout_name: 'global_department',
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: !this.hide_search_field,
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
				basic_search: !this.hide_search_field,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	setEditViewData() {
		//use the user_type_array in edit mode and new mode, use the system_type_array in view mode
		//this prevents users from choosing type_ids that are for system use only but can see the system type_ids when viewing
		if ( this.is_viewing ) {
			this.edit_view_ui_dic.type_id.setSourceData( this.system_type_array );
		} else {
			this.edit_view_ui_dic.type_id.setSourceData( this.user_type_array );
		}

		super.setEditViewData(); //Set Navigation

		if ( !this.sub_view_mode ) {
			var widget = this.edit_view_ui_dic['user_id'];
			if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.is_mass_editing ) {
				widget.setAllowMultipleSelection( true );
			} else {
				widget.setAllowMultipleSelection( false );
			}
		}
	}

	uniformVariable( records ) {

		var record_array = [];
		if ( $.type( records.user_id ) === 'array' ) {

			if ( records.user_id.length === 0 ) {
				records.user_id = false;
				return records;
			}

			for ( var key in records.user_id ) {
				var new_record = Global.clone( records );
				new_record.user_id = records.user_id[key];
				record_array.push( new_record );
			}
		}

		if ( record_array.length > 0 ) {
			records = record_array;
		}

		return records;
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'full_name':
						if ( this.current_edit_record['first_name'] ) {
							widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						}
						break;
					case 'amount':
						var result = Global.getTimeUnit( this.current_edit_record[key] );
						widget.setValue( result );
						if ( !this.is_viewing ) {
							$( '#release-balance-button' ).css( 'display', '' );
						}
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.type_id = true;
		if ( this.sub_view_mode ) {
			column_filter.accrual_policy_account = true;
			column_filter.accrual_policy_account_id = true;
			column_filter.user_id = true;
		}
		return this._getFilterColumnsFromDisplayColumns( column_filter, true );
	}

	onGridSelectAll() {
		this.edit_enabled = this.editEnabled();
		this.delete_enabled = this.deleteEnabled();
		this.setDefaultMenu();
	}

	deleteEnabled() {
		var grid_selected_id_array = this.getGridSelectIdArray();
		if ( grid_selected_id_array.length > 0 ) {
			for ( var i = grid_selected_id_array.length - 1; i >= 0; i-- ) {
				var selected_item = this.getRecordFromGridById( grid_selected_id_array[i] );
				if ( Global.isSet( this.delete_type_array[selected_item.type_id] ) ) {
					return true;
				}
			}
		}
		return false;
	}

	editEnabled() {
		var grid_selected_id_array = this.getGridSelectIdArray();
		if ( grid_selected_id_array.length > 0 ) {
			for ( var i = grid_selected_id_array.length - 1; i >= 0; i-- ) {
				var selected_item = this.getRecordFromGridById( grid_selected_id_array[i] );
				if ( Global.isSet( this.user_type_array[selected_item.type_id] ) ) {
					return true;
				}
			}
		}
		return false;
	}

	onGridSelectRow() {

		var selected_item = null;
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;

		if ( grid_selected_length > 0 ) {
			selected_item = this.getRecordFromGridById( grid_selected_id_array[0] );

			this.edit_enabled = this.editEnabled();
			this.delete_enabled = this.deleteEnabled();
		}

		this.setDefaultMenu();
	}

	setDefaultMenuEditIcon( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

		if ( grid_selected_length === 1 && this.editOwnerOrChildPermissionValidate( pId ) ) {
			if ( this.edit_enabled ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			} else {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			if ( grid_selected_length !== 0 ) {
				// This ensures the edit icon is still visible when nothing is selected, but should still be disabled. (to keep consistency with old design)
				ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		}
	}

	setDefaultMenuMassEditIcon( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length > 1 ) {
			if ( this.edit_enabled ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			} else {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
	}

	setDefaultMenuDeleteIcon( context_btn, grid_selected_length, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length >= 1 && this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			if ( this.delete_enabled ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			} else {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuEditIcon( context_btn, pId ) {
		if ( !this.editPermissionValidate( pId ) || this.edit_only_mode || this.is_mass_editing ) {

			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.edit_enabled && this.editOwnerOrChildPermissionValidate( pId ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			if ( !this.is_viewing ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteIcon( context_btn, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.delete_enabled && this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteAndNextIcon( context_btn, pId ) {
		if ( !this.deletePermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.delete_enabled && this.deleteOwnerOrChildPermissionValidate( pId ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['save_and_continue'],
			include: [
				{
					label: $.i18n._( 'Jump To' ),
					id: 'jump_to_header',
					menu_align: 'right',
					action_group: 'jump_to',
					action_group_header: true,
					permission_result: false // to hide it in legacy context menu and avoid errors in legacy parsers.
				},
				{
					label: $.i18n._( 'TimeSheet' ),
					id: 'timesheet',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Import' ),
					id: 'import_icon',
					menu_align: 'right',
					action_group: 'import_export',
					group: 'other',
					vue_icon: 'tticon tticon-file_download_black_24dp',
					sort_order: 9010
				}
			]
		};

		return context_menu_model;
	}

	getGridSetup() {
		var $this = this;

		var grid_setup = {
			container_selector: this.sub_view_mode ? '.edit-view-tab' : '.view',
			sub_grid_mode: this.sub_view_mode,
			onSelectRow: function() {
				$this.onGridSelectRow();
			},
			onCellSelect: function() {
				$this.onGridSelectRow();
			},
			onSelectAll: function() {
				$this.onGridSelectAll();
			},
			ondblClickRow: function( e ) {
				$this.onGridDblClickRow( e );
			},
			onRightClickRow: function( rowId ) {
				var id_array = $this.getGridSelectIdArray();
				if ( id_array.indexOf( rowId ) < 0 ) {
					$this.grid.grid.resetSelection();
					$this.grid.grid.setSelection( rowId );
					$this.onGridSelectRow();
				}
			},
		};

		return grid_setup;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'timesheet':
				this.onNavigationClick();
				break;
			case 'import_icon':
				this.onImportClick();
				break;
		}
	}

	onImportClick() {
		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'Accrual', function() {
			$this.search();
		} );
	}

	reSelectLastSelectItems() {
		super.reSelectLastSelectItems();

		//Need to check edit_enabled and delete_enabled after re-selecting grid items to prevent issues when returning to list view and
		//context menu buttons not being enabled when they should be.
		this.edit_enabled = this.editEnabled();
		this.delete_enabled = this.deleteEnabled();

		if ( !this.edit_view ) {
			this.setDefaultMenu();
		}
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'timesheet':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
				break;
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'timesheet':
				// Prevent user clicking timesheet from new accrual page by disabling the icon
				this.setDefaultMenuViewIcon( context_btn, 'punch' );
				break;
		}
	}

	onNavigationClick() {
		var $this = this;
		var filter = { filter_data: {} };
		var label = this.sub_view_mode ? $.i18n._( 'Accrual Balances' ) : $.i18n._( 'Accruals' );

		if ( Global.isSet( this.current_edit_record ) ) {

			filter.user_id = this.current_edit_record.user_id;
			filter.base_date = this.current_edit_record.time_stamp;

			Global.addViewTab( this.viewId, label, window.location.href );
			IndexViewController.goToView( 'TimeSheet', filter );

		} else {
			var accrual_filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];

				accrual_filter.filter_data = {};
				accrual_filter.filter_data.id = [selectedId];

				TTAPI.APIAccrual.getAccrual( accrual_filter, {
					onResult: function( result ) {

						var result_data = result.getResult();

						if ( !result_data ) {
							result_data = [];
						}

						result_data = result_data[0];

						filter.user_id = result_data.user_id;
						filter.base_date = result_data.time_stamp;

						Global.addViewTab( $this.viewId, label, window.location.href );
						IndexViewController.goToView( 'TimeSheet', filter );

					}
				} );
			}

		}
	}

	getSubViewFilter( filter ) {
		if ( this.parent_edit_record && this.parent_edit_record.user_id && this.parent_edit_record.accrual_policy_account_id ) {
			filter.user_id = this.parent_edit_record.user_id;
			filter.accrual_policy_account_id = this.parent_edit_record.accrual_policy_account_id;
		}
		return filter;
	}

	onAddResult( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;

		if ( $this.sub_view_mode ) {
			result_data['user_id'] = $this.parent_edit_record['user_id'];
			result_data['first_name'] = $this.parent_edit_record['first_name'];
			result_data['last_name'] = $this.parent_edit_record['last_name'];
			result_data['accrual_policy_account_id'] = $this.parent_edit_record['accrual_policy_account_id'];
			result_data['accrual_policy_account'] = $this.parent_edit_record['accrual_policy_account'];
		}

		$this.current_edit_record = result_data;

		$this.initEditView();
	}

	searchDone() {
		var $this = this;

		//When Attendance -> Accrual Balance, New icon is clicked, open the Balance view first, then trigger the New icon to create a new accrual entry from there.
		if ( Global.isSet( $this.is_trigger_add ) && $this.is_trigger_add ) {
			$this.onAddClick();
			$this.is_trigger_add = false;
		}

		if ( this.sub_view_mode ) {
			TTPromise.resolve( 'initSubAccrualView', 'init' );

			var result_data = this.grid.getGridParam( 'data' );
			if ( !Global.isArray( result_data ) || result_data.length < 1 ) {
				this.onCancelClick();
				if ( this.parent_view_controller ) {
					this.parent_view_controller.search();
				}
			}
		}

		super.searchDone();
	}

	getAvailableBalance() {
		if ( this.is_viewing ) {
			return;
		}

		var $this = this;

		this.api_accrual_balance.getAccrualBalanceAndRelease( this.current_edit_record.accrual_policy_account_id, this.current_edit_record.user_id, this.current_edit_record.type_id, {
				onResult: function( result ) {
					$this.releaseBalance( result.getResult() );
				}
			}
		);
	}

	releaseBalance( balance ) {
		//If the balance can be fully displayed in the preferred time unit, is that format.
		//Otherwise we need to release fractions of a minute, so force through "HH:MM:SS" and wrap it in quotes so it doesn't get rounded.
		if ( Global.parseTimeUnit( '"'+  Global.getTimeUnit( balance ) +'"' ) == balance ) {
			this.edit_view_ui_dic['amount'].setValue( Global.getTimeUnit( balance ) );
		} else {
			this.edit_view_ui_dic['amount'].setValue( '"' + Global.getTimeUnit( balance, 12 ) + '"' ); //12="HH:MM:SS"
		}
		this.edit_view_ui_dic['amount'].trigger( 'change' ); //Trigger change event to properly update the amount and trigger validation.
	}

}

AccrualViewController.loadView = function() {

	Global.loadViewSource( 'Accrual', 'AccrualView.html', function( result ) {

		TTPromise.wait( 'BaseViewController', 'initialize', function() {

			var args = {};
			var template = _.template( result );

			Global.contentContainer().html( template( args ) );
		} );
	} );

};

AccrualViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'Accrual', 'SubAccrualView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				TTPromise.add( 'AccrualViewController', 'init' );
				TTPromise.wait( 'AccrualViewController', 'init', function() {
					afterViewLoadedFun( sub_accrual_view_controller );
				} );
			}
		}
	} );
};