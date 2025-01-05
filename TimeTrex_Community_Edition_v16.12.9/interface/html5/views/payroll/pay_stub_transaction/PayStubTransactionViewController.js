export class PayStubTransactionViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#pay_stub_transaction_view_container',

			status_array: null,
			currency_array: null,
			user_status_array: null,
			user_group_array: null,
			type_array: null,

			user_api: null,
			user_group_api: null,
			company_api: null,
			pay_stub_entry_api: null,

			include_entries: true
		} );

		super( options );
	}

	init() {
		//this._super('initialize' );
		this.edit_view_tpl = 'PayStubTransactionEditView.html';
		this.permission_id = 'pay_stub';
		this.viewId = 'PayStubTransaction';
		this.script_name = 'PayStubTransactionView';
		this.table_name_key = 'pay_stub_transaction';
		this.context_menu_name = $.i18n._( 'Pay Stub Transaction' );
		this.navigation_label = $.i18n._( 'Pay Stub Transactions' );

		this.api = TTAPI.APIPayStubTransaction;
		this.currency_api = TTAPI.APICurrency;
		this.remittance_source_account_api = TTAPI.APIRemittanceSourceAccount;
		this.remittance_destination_account_api = TTAPI.APIRemittanceDestinationAccount;
		this.user_api = TTAPI.APIUser;
		this.pay_stub_entry_api = TTAPI.APIPayStubEntry;
		this.user_group_api = TTAPI.APIUserGroup;
		this.company_api = TTAPI.APICompany;
		this.pay_period_api = TTAPI.APIPayPeriod;

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initPermission() {
		super.initPermission();

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}
	}

	initOptions( callBack ) {
		var $this = this;

		this.initDropDownOption( 'status', 'transaction_status_id' );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				'view',
				'edit',
				'mass_edit',
				'save',
				'save_and_continue',
				'save_and_next',
				'cancel',
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
					label: $.i18n._( 'Schedule' ),
					id: 'schedule',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Pay Stubs' ),
					id: 'pay_stub',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Pay Stub Amendments' ),
					id: 'pay_stub_amendment',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Edit Employee' ),
					id: 'edit_employee',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Edit Pay Period' ),
					id: 'edit_pay_period',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				}
			]
		};

		return context_menu_model;
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'pay_stub_transaction':
			case 'pay_stub':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
				break;
			case 'timesheet':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
				break;
			case 'schedule':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
				break;
			case 'pay_stub_amendment':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub_amendment' );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length, 'user' );
				break;
			case 'edit_pay_period':
				this.setDefaultMenuEditPayPeriodIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setDefaultMenuEditPayPeriodIcon( context_btn, grid_selected_length, pId ) {
		if ( !this.editPermissionValidate( 'pay_period_schedule' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length ) {
		if ( !this.editChildPermissionValidate( 'user' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuViewIcon( context_btn, grid_selected_length, pId ) {
		if ( pId === 'punch' || pId === 'schedule' || pId === 'pay_stub_amendment' ) {
			super.setDefaultMenuViewIcon( context_btn, grid_selected_length, pId );
		} else {
			if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
				ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
			}

			if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			} else {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'import_icon':
				this.setEditMenuImportIcon( context_btn );
				break;
			case 'timesheet':
				this.setEditMenuViewIcon( context_btn, 'punch' );
				break;
			case 'schedule':
				this.setEditMenuViewIcon( context_btn, 'schedule' );
				break;
			case 'pay_stub_transaction':
				this.setEditMenuViewIcon( context_btn, 'pay_stub_transaction' );
				break;
			case 'pay_stub_amendment':
				this.setEditMenuViewIcon( context_btn, 'pay_stub_amendment' );
				break;
			case 'edit_employee':
				this.setEditMenuViewIcon( context_btn, 'user' );
				break;
			case 'edit_pay_period':
				this.setEditMenuViewIcon( context_btn, 'pay_period_schedule' );
				break;
		}
	}

	setDefaultMenuGeneratePayStubIcon( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setCurrentEditRecordData() {
		this.include_entries = true;
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}
			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();

		this.edit_view_ui_dic.user_id.setEnabled( false );
		this.edit_view_ui_dic.remittance_source_account_id.setEnabled( false );
		this.edit_view_ui_dic.remittance_destination_account_id.setEnabled( false );
		this.edit_view_ui_dic.currency_id.setEnabled( false );
		this.edit_view_ui_dic.amount.setEnabled( false );
		this.edit_view_ui_dic.confirmation_number.setEnabled( false );
	}

	onSaveClick( ignoreWarning ) {
		if ( this.is_mass_editing ) {
			this.include_entries = false; // Note: not sure if we really need this, as a code search for this variable shows it only set in one other place, but not used. Was in original onSaveClick, so including it here for now.
		}
		super.onSaveClick( ignoreWarning );
	}

	onSaveAndContinue( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.current_edit_record;
		record = this.uniformVariable( record );

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );
			}
		} );
	}

	onSaveAndContinueResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;

			}
			$this.search( false );
			//     $this.editor.show_cover = false;

			$this.onSaveAndContinueDone( result );
		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	}

	// onSaveAndNextResult( result ) {
	// 	var $this = this;
	// 	if ( result.isValid() ) {
	// 		var result_data = result.getResult();
	// 		if ( result_data === true ) {
	// 			$this.refresh_id = $this.current_edit_record.id;
	// 		} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
	// 			$this.refresh_id = result_data;
	// 		}
	// 		//     $this.editor.show_cover = true;
	// 		$this.onRightArrowClick();
	// 		$this.search( false );
	// 		$this.onSaveAndNextDone( result );
	//
	// 	} else {
	// 		$this.setErrorTips( result );
	// 		$this.setErrorMenu();
	// 	}
	// },

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.pay_stub_transaction_date = true;
		column_filter.pay_stub_start_date = true;
		column_filter.pay_stub_end_date = true;
		column_filter.id = true;
		column_filter.status_id = true;
		column_filter.is_owner = true;
		column_filter.user_id = true;
		column_filter.pay_stub_id = true;
		column_filter.pay_period_id = true;
		column_filter.pay_stub_run_id = true;
		column_filter.currency_id = true;
		column_filter.remittance_source_account_type_id = true;
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}

		if ( display_columns ) {
			for ( var i = 0; i < display_columns.length; i++ ) {
				column_filter[display_columns[i].name] = true;
			}
		}
		return column_filter;
	}

	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	validate() {
		var $this = this;
		var record = {};

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}
				}
			}
		} else {
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );
		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );
			}
		} );
	}

	buildEditViewUI() {
		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_pay_stub_transaction': { 'label': $.i18n._( 'Pay Stub Transaction' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIPayStub,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_pay_stub',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_pay_stub_transaction = this.edit_view_tab.find( '#tab_pay_stub_transaction' );
		var tab_pay_stub_transaction_column1 = tab_pay_stub_transaction.find( '.first-column' );
		var form_item_input;
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			show_search_inputs: false,
			set_empty: false,
			field: 'user_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_pay_stub_transaction_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id', set_empty: false } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIRemittanceSourceAccount,
			allow_multiple_selection: false,
			layout_name: 'global_remittance_source_account',
			show_search_inputs: false,
			set_empty: false,
			field: 'remittance_source_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Source Account' ), form_item_input, tab_pay_stub_transaction_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIRemittanceDestinationAccount,
			allow_multiple_selection: false,
			layout_name: 'global_remittance_destination_account',
			show_search_inputs: false,
			set_empty: false,
			field: 'remittance_destination_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Destination Account' ), form_item_input, tab_pay_stub_transaction_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			field: 'currency_id',
			set_empty: false,
			layout_name: 'global_currency',
			allow_multiple_selection: false,
			show_search_inputs: false,
			api_class: TTAPI.APICurrency
		} );
		;
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'amount', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'transaction_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Transaction Date' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'confirmation_number', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Confirmation #' ), form_item_input, tab_pay_stub_transaction_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'note', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_pay_stub_transaction_column1 );
	}

	buildSearchFields() {
		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'transaction_status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Source Account' ),
				in_column: 2,
				field: 'remittance_source_account_id',
				layout_name: 'global_remittance_source_account',
				api_class: TTAPI.APIRemittanceSourceAccount,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: 'global_Pay_period',
				api_class: TTAPI.APIPayPeriod,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_user',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Currency' ),
				in_column: 2,
				field: 'currency_id',
				api_class: TTAPI.APICurrency,
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_currency',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Transaction Date' ),
				in_column: 2,
				field: 'transaction_date',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.DATE_PICKER
			} )

		];
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'timesheet':
			case 'schedule':
			case 'pay_stub_amendment':
			case 'edit_employee':
			case 'generate_pay_stub':
			case 'pay_stub_transaction':
			case 'edit_pay_period':
			case 'pay_stub':
				this.onNavigationClick( id );
				break;
		}
	}

	onViewClick( editId, noRefreshUI ) {
		this.onNavigationClick( 'view' );
	}

	onNavigationClick( iconName ) {
		var $this = this;
		var grid_selected_id_array;
		var filter = {};
		var ids = [];
		var user_ids = [];
		var base_date;
		var pay_period_ids = [];
		var pay_stub_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			pay_period_ids.push( $this.current_edit_record.pay_period_id );
			pay_stub_ids.push( $this.current_edit_record.pay_stub_id );
			base_date = $this.current_edit_record.pay_stub_start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				pay_period_ids.push( grid_selected_row.pay_period_id );
				pay_stub_ids.push( grid_selected_row.pay_stub_id );
				base_date = grid_selected_row.pay_stub_start_date;
			} );
		}

		var args = { filter_data: { id: ids } };

		var post_data;
		switch ( iconName ) {
			case 'pay_stub':
				filter.filter_data = {};
				filter.filter_data.id = { value: pay_stub_ids };
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, $.i18n._( 'Pay Stub Transactions' ), window.location.href );
				IndexViewController.goToView( 'PayStub', filter );
				break;
			case 'edit_employee':
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
			case 'edit_pay_period':
				if ( pay_period_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'PayPeriods', pay_period_ids[0] );
				}
				break;
			case 'timesheet':
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, $.i18n._( 'Pay Stub Transactions' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case 'schedule':
				filter.filter_data = {};
				var include_users = { value: user_ids };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = base_date;
				Global.addViewTab( this.viewId, $.i18n._( 'Pay Stub Transactions' ), window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;
			case 'pay_stub_amendment':
				filter.filter_data = {};
				filter.filter_data.user_id = user_ids[0];
				filter.filter_data.pay_period_id = pay_period_ids[0];
				Global.addViewTab( this.viewId, $.i18n._( 'Pay Stub Transactions' ), window.location.href );
				IndexViewController.goToView( 'PayStubAmendment', filter );
				break;
			case 'view':
				this.setCurrentEditViewState( 'view' );
				this.openEditView();
				filter.filter_data = {};

				var grid_selected_id_array = this.getGridSelectIdArray();
				var selectedId = grid_selected_id_array[0];
				filter.filter_data.id = [selectedId];

				this.api['get' + this.api.key_name]( filter, {
					onResult: function( result ) {
						var result_data = result.getResult();
						if ( !result_data ) {
							result_data = [];
						}

						result_data = result_data[0];

						if ( !result_data ) {
							TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
							$this.onCancelClick();
							return;
						}

						if ( $this.sub_view_mode && $this.parent_key ) {
							result_data[$this.parent_key] = $this.parent_value;
						}

						$this.current_edit_record = result_data;

						$this.initEditView();

					}
				} );
				break;
			case 'pay_stub_transaction':
				IndexViewController.openEditView( this, 'PayStubTransaction', user_ids[0] );
				break;
		}

	}

}

PayStubTransactionViewController.loadView = function() {
	Global.loadViewSource( 'PayStubTransaction', 'PayStubTransactionView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );
		Global.contentContainer().html( template );
	} );

};
