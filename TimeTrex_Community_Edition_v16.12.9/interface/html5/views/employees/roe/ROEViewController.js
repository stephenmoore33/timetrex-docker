export class ROEViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#roe_view_container', //Must set el here and can only set string, so events can work
			user_api: null,
			company_api: null,
			pay_period_schedule_api: null,
			user_status_array: null,
			status_array: null,
			code_array: null,
			type_array: null,
			user_termination_status_array: null,

			user_generic_data_api: null,

			form_setup_item: null,


		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );
		this.permission_id = 'roe';
		this.viewId = 'ROE';
		this.edit_view_tpl = 'ROEEditView.html';
		this.script_name = 'ROEView';
		this.table_name_key = 'roe';
		this.context_menu_name = $.i18n._( 'Record Of Employment' );
		this.navigation_label = $.i18n._( 'Record Of Employment' );
		this.api = TTAPI.APIROE;
		this.report_api = TTAPI.APIROEReport;
		this.user_api = TTAPI.APIUser;
		this.company_api = TTAPI.APICompany;
		this.pay_period_schedule_api = TTAPI.APIPayPeriodSchedule;
		this.user_generic_data_api = TTAPI.APIUserGenericData;

		this.render();
		this.buildContextMenu();
		this.initData();
	}

	initOptions() {
		var $this = this;
		var options = [
			{ option_name: 'status', api: this.api },
			{ option_name: 'user_status', api: this.api },
			{ option_name: 'user_termination_status', api: this.api },
			{ option_name: 'code', api: this.api },
			{ option_name: 'type', field_name: 'pay_period_type_id', api: this.pay_period_schedule_api },
		];

		this.initDropDownOptions( options, () => {
			if ( $this.type_array ) {
				$this.type_array.shift();
			}
		} );
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.user_id = true;

		return this._getFilterColumnsFromDisplayColumns( column_filter, true );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['view'],
			include: [
				{
					label: $.i18n._( 'Form' ),
					id: 'form_header',
					menu_align: 'right',
					action_group: 'form',
					action_group_header: true,
					permission_result: false // to hide it in legacy context menu and avoid errors in legacy parsers.
				},
				{
					label: $.i18n._( 'View' ),
					id: 'view_roe', //Don't bother with constant here, as its only used once.
					menu_align: 'right',
					action_group: 'form',
					show_on_right_click: true,
				},
				{
					label: $.i18n._( 'eFile' ),
					id: 'e_file',
					menu_align: 'right',
					action_group: 'form',
				},
				{
					label: $.i18n._( 'Save Setup' ),
					id: 'save_setup',
					menu_align: 'right',
					action_group: 'form',
				},
				{
					label: $.i18n._( 'Jump To' ),
					id: 'jump_to_header',
					menu_align: 'right',
					action_group: 'jump_to',
					action_group_header: true,
					permission_result: false // to hide it in legacy context menu and avoid errors in legacy parsers.
				},
				{
					label: $.i18n._( 'Pay Stubs' ),
					id: 'pay_stub',
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
					label: $.i18n._( 'TimeSheet' ),
					id: 'timesheet',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					}
			]
		};

		if ( ( Global.getProductEdition() >= 15 ) ) {
			var publish = {
				label: $.i18n._( 'Publish' ),
				id: 'publish_roe', //Don't bother with constant here, as its only used once.
				menu_align: 'right',
				action_group: 'form',
				sort_order: 2100
			};

			context_menu_model.include.unshift( publish );
		}

		return context_menu_model;
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'view_roe':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
				break;
			case 'publish_roe':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length );
				break;
			case 'print':
				this.setDefaultMenuPrintIcon( context_btn, grid_selected_length );
				break;
			case 'e_file':
				this.setDefaultMenuEfileIcon( context_btn, grid_selected_length );
				break;
			case 'save_setup':
				this.setDefaultMenuSaveSetupIcon( context_btn, grid_selected_length );
				break;
			case 'pay_stub':
				this.setDefaultMenuPayStubIcon( context_btn, grid_selected_length );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
				break;
			case 'timesheet':
				this.setDefaultMenuTimesheetIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setDefaultMenuPrintIcon( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuEfileIcon( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuSaveSetupIcon( context_btn, grid_selected_length, pId ) {

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuPayStubIcon( context_btn, grid_selected_length, pId ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayStub' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length, pId ) {

		if ( !this.editPermissionValidate( 'user' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuTimesheetIcon( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuViewIcon( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditRoeViewIcon( context_btn, grid_selected_length, pId ) {
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setEditMenuPublishIcon( context_btn, grid_selected_length, pId ) {
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'view_roe':
				this.setEditMenuViewIcon( context_btn );
				break;
			case 'publish_roe':
				this.setEditMenuPublishIcon( context_btn );
				break;
			case 'print':
				this.setEditMenuPrintIcon( context_btn );
				break;
			case 'e_file':
				this.setEditMenuEfileIcon( context_btn );
				break;
			case 'save_setup':
				this.setEditMenuSaveSetupIcon( context_btn );
				break;
			case 'pay_stub':
				this.setEditMenuPayStubIcon( context_btn );
				break;
			case 'edit_employee':
				this.setEditMenuEditEmployeeIcon( context_btn );
				break;
			case 'timesheet':
				this.setEditMenuTimeSheetIcon( context_btn );
				break;
		}
	}

	setEditMenuPrintIcon( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuEfileIcon( context_btn, pId ) {
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setEditMenuSaveSetupIcon( context_btn, pId ) {

//		if ( !this.current_edit_record || !this.current_edit_record.id ) {
//			context_btn.addClass( 'disable-image' );
//		}
	}

	setEditMenuPayStubIcon( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuEditEmployeeIcon( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuTimeSheetIcon( context_btn, pId ) {

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	buildSearchFields() {
		super.buildSearchFields();
		var default_args = { permission_section: 'roe' };
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				multiple: true,
				basic_search: true,
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Reason' ),
				in_column: 1,
				field: 'code_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Pay Period Type' ),
				in_column: 1,
				field: 'pay_period_type_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Comments' ),
				field: 'comments',
				basic_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'First Name' ),
				in_column: 2,
				field: 'first_name',
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Last Name' ),
				in_column: 2,
				field: 'last_name',
				basic_search: true,
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
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	search( set_default_menu, page_action, page_number, callBack ) {
		if ( !this.form_setup_item ) {
			this.initFormSetup( () => {
				super.search( set_default_menu, page_action, page_number, callBack );
			} );
		} else {
			super.search( set_default_menu, page_action, page_number, callBack );
		}
	}

	setCurrentEditRecordData() {

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country': //popular case
						this.setCountryValue( widget, key );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.setFormSetupData();
		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_roe': {
				'label': $.i18n._( 'ROE' )
			},
			'tab_form_setup': { 'label': $.i18n._( 'Form Setup' ), 'on_exit_callback': 'checkFormSetupSaved' },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIROE,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_roe',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_roe = this.edit_view_tab.find( '#tab_roe' );
		var tab_form_setup = this.edit_view_tab.find( '#tab_form_setup' );

		var tab_roe_column1 = tab_roe.find( '.first-column' );
		var tab_form_setup_column1 = tab_form_setup.find( '.first-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[0].push( tab_roe_column1 );
		this.edit_view_tabs[1].push( tab_form_setup_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			field: 'user_id',
			show_search_inputs: true,
			set_empty: true
		} );

		var default_args = {};
		default_args.permission_section = 'roe';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_roe_column1, '' );

		// Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.user_status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_roe_column1 );

		// Reason
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'code_id' } );
		form_item_input.setSourceData( $this.code_array );
		this.addEditFieldToColumn( $.i18n._( 'Reason' ), form_item_input, tab_roe_column1 );

		// Pay Period Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'pay_period_type_id' } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Pay Period Type' ), form_item_input, tab_roe_column1 );

		// First Day Worked
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'first_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + '(' + $.i18n._( 'Or first day since last ROE' ) + ')' + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'First Day Worked' ), form_item_input, tab_roe_column1, '', widgetContainer );

		// Last Day For Which Paid
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'last_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + '(' + $.i18n._( 'Last day worked or received insurable earnings' ) + ')' + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Last Day For Which Paid' ), form_item_input, tab_roe_column1, '', widgetContainer );

		//Final Pay Period Ending Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'pay_period_end_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + '(' + $.i18n._( 'Pay period end date after Last Day For Which Paid' ) + ')' + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Final Pay Period Ending Date' ), form_item_input, tab_roe_column1, '', widgetContainer );

		// Expected Date of Recall
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'recall_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Expected Date of Recall' ), form_item_input, tab_roe_column1 );

		// Serial No
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'serial', width: 100 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + '(' + $.i18n._( 'Optional' ) + ')' + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Serial No' ), form_item_input, tab_roe_column1, '', widgetContainer );

		// Comments
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'comments', width: 400 } );
		this.addEditFieldToColumn( $.i18n._( 'Comments' ), form_item_input, tab_roe_column1 );

		// Release All Accruals
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'release_accruals' } );
		this.addEditFieldToColumn( $.i18n._( 'Release All Accruals' ), form_item_input, tab_roe_column1 );

		// Generate Final Pay Stub
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'generate_pay_stub' } );
		this.addEditFieldToColumn( $.i18n._( 'Generate Final Pay Stub' ), form_item_input, tab_roe_column1, '' );

		// Set User Termination Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'user_termination_status_id' } );
		form_item_input.setSourceData( $this.user_termination_status_array );
		this.addEditFieldToColumn( $.i18n._( 'Change Employee Status To' ), form_item_input, tab_roe_column1 );

		//Final Pay Stub End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'final_pay_stub_end_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + '(' + $.i18n._( 'May be after Final Pay Period Ending Date if vacation/severance is paid separately' ) + ')' + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Final Pay Stub End Date' ), form_item_input, tab_roe_column1, '', widgetContainer );

		//Final Pay Stub Transaction Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'final_pay_stub_transaction_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Final Pay Stub Transaction Date' ), form_item_input, tab_roe_column1 );

		// Insurable Absence Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIAbsencePolicy,
			allow_multiple_selection: true,
			layout_name: 'global_absences',
			field: 'absence_policy_ids',
			show_search_inputs: true,
			set_empty: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Insurable Absence Policies' ), form_item_input, tab_form_setup_column1, '' );

		var args = {};
		args.filter_data = {};
		args.filter_data.type_id = [10, 30, 40, 80];
		args.filter_data.status_id = 10;

		// Insurable Earnings (Box 15B)
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			field: 'insurable_earnings_psea_ids',
			show_search_inputs: true,
			set_empty: true
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Insurable Earnings (Box 15B)' ), form_item_input, tab_form_setup_column1, '' );

		// Vacation Pay (Box 17A)
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			field: 'vacation_psea_ids',
			show_search_inputs: true,
			set_empty: true
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Vacation Pay (Box 17A)' ), form_item_input, tab_form_setup_column1, '' );

		// Other Monies (Box 17C)
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			field: 'other_psea_ids',
			show_search_inputs: true,
			set_empty: true
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Other Monies (Box 17C)' ), form_item_input, tab_form_setup_column1, '' );

		//Date all employees were issues ROEs from a previous payroll system.
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'min_first_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + '(' + $.i18n._( 'Date all employees were issued an ROE' ) + ')' + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Earliest First Day Since Last ROE' ), form_item_input, tab_form_setup_column1, '', widgetContainer );
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'download':
				this.onDownloadClick();
				break;
			case 'pay_stub':
			case 'edit_employee':
			case 'timesheet':
			case 'view_roe':
			case 'print':
			case 'e_file':
			case 'publish_roe':
			case 'export_excel':
				this.onNavigationClick( id );
				break;
			case 'save_setup':
				this.onSaveSetup();
		}
	}

	initFormSetup( callBack ) {
		var args = {};
		var $this = this;
		args.filter_data = {};
		args.filter_data.script = 'roe';
		args.filter_data.user_id = TTUUID.zero_id;
		args.filter_data.is_default = true;

		this.user_generic_data_api.getUserGenericData( args, {
			onResult: function( result ) {

				var result_data = result.getResult();

				if ( result_data && result_data.length > 0 ) {
					$this.form_setup_item = result_data[0];
				} else {
					$this.form_setup_item = {};
				}

				if ( callBack ) {
					callBack();
				}

			}
		} );
	}

	setFormSetupData() {
		if ( this.form_setup_item.data ) {
			this.edit_view_ui_dic.absence_policy_ids.setValue( this.form_setup_item.data.absence_policy_ids );
			this.edit_view_ui_dic.insurable_earnings_psea_ids.setValue( this.form_setup_item.data.insurable_earnings_psea_ids );
			this.edit_view_ui_dic.vacation_psea_ids.setValue( this.form_setup_item.data.vacation_psea_ids );
			this.edit_view_ui_dic.other_psea_ids.setValue( this.form_setup_item.data.other_psea_ids );
			this.edit_view_ui_dic.min_first_date.setValue( this.form_setup_item.data.min_first_date );
		}
	}

	getFormSetupData( form_item ) {

		//Error: TypeError: form_item is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141117-091433 line 2 > eval line 1015
		if ( !form_item ) {
			form_item = {};
		}

		form_item.form = {};

		form_item.form.absence_policy_ids = this.edit_view_ui_dic.absence_policy_ids.getValue();
		form_item.form.insurable_earnings_psea_ids = this.edit_view_ui_dic.insurable_earnings_psea_ids.getValue();
		form_item.form.vacation_psea_ids = this.edit_view_ui_dic.vacation_psea_ids.getValue();
		form_item.form.other_psea_ids = this.edit_view_ui_dic.other_psea_ids.getValue();
		form_item.form.min_first_date = this.edit_view_ui_dic.min_first_date.getValue();

		return form_item;
	}

	onSaveSetup() {
		var $this = this;
		var form_setup = this.form_setup_item;

		form_setup.user_id = TTUUID.zero_id;
		form_setup.is_default = true;

		if ( !form_setup.id ) {
			form_setup.script = 'roe';
			form_setup.name = 'form';
		}

		form_setup.data = this.getFormSetupData( {} ).form;

		$this.form_setup_item = form_setup;
		this.user_generic_data_api.setUserGenericData( form_setup, {
			onResult: function( result ) {

				if ( result && result.isValid() ) {
					if ( typeof $this.form_setup_item.id == 'undefined' && TTUUID.isUUID( result.getResult() ) ) {
						$this.form_setup_item.id = result.getResult();
					}
					TAlertManager.showAlert( $.i18n._( 'Form Setup has been saved successfully' ) );
				} else {
					TAlertManager.showAlert( $.i18n._( 'Form Setup save failed, Please try again' ) );
				}

			}
		} );
	}

	onNavigationClick( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var user_ids = [];

		var ids = [];

		var base_date;

		if ( $this.edit_view && $this.current_edit_record.id ) {
			user_ids.push( $this.current_edit_record.user_id );
			base_date = $this.current_edit_record.last_date;
			ids.push( $this.current_edit_record.id );
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				user_ids.push( grid_selected_row.user_id );
				base_date = grid_selected_row.last_date;
				ids.push( grid_selected_row.id );
			} );
		}

		var args = { roe_id: ids };

		if ( !$this.edit_view ) {
			if ( this.form_setup_item.data ) {
				args.form = this.form_setup_item.data;
			}
		} else {
			args.form = this.getFormSetupData( this.current_edit_record ).form;
		}

		var post_data;

		switch ( iconName ) {
			case 'edit_employee':
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
			case 'pay_stub':
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( $this.viewId, $.i18n._( 'Record of Employment' ), window.location.href );
					IndexViewController.goToView( 'PayStub', filter );

				}
				break;
			case 'timesheet':
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, $.i18n._( 'Record of Employment' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );

				}
				break;
			case 'view_roe':
				post_data = { 0: args, 1: 'pdf_form' };
				this.doFormIFrameCall( post_data );
				break;
			case 'publish_roe':
				this.report_api.getROEReport( args, 'pdf_form_publish_employee', {
					onResult: function( result ) {
						if ( result && result.isValid() ) {
							var retval = result.getResult();
							if ( retval ) {
								UserGenericStatusWindowController.open( retval, LocalCacheData.getLoginUser().id );
							}
						} else {
							TAlertManager.showErrorAlert( result );
						}
					}
				} );
				break;
			case 'print':
				post_data = { 0: args, 1: 'pdf_form_print' };
				this.doFormIFrameCall( post_data );
				break;
			case 'e_file':
				post_data = { 0: args, 1: 'efile_xml' };
				this.doFormIFrameCall( post_data );

				//Refresh grid within 5 seconds, hopefully the file has been downloaded by then.
				$this = this;
				setTimeout( function() {
					$this.search();
				}, 5000 );
				break;
			case 'export_excel':
				this.onExportClick( 'export' + this.api.key_name );
				break;

		}
	}

	doFormIFrameCall( postData ) {
		Global.APIFileDownload( 'APIROEReport', 'getROEReport', postData );
	}

	onSaveResult( result ) {
		super.onSaveResult( result );
		if ( result && result.isValid() ) {
			this.showStatusReport( result, this.refresh_id );
		}
	}

	onSaveAndNewResult( result ) {
		super.onSaveAndNewResult( result );
		if ( result && result.isValid() ) {
			this.showStatusReport( result, this.refresh_id );
		}
	}

	onSaveAndContinueResult( result ) {
		super.onSaveAndContinueResult( result );
		if ( result && result.isValid() ) {
			this.showStatusReport( result, this.refresh_id );
		}
	}

	onSaveAndNextResult( result ) {
		super.onSaveAndNextResult( result );
		if ( result && result.isValid() ) {
			this.showStatusReport( result, this.refresh_id );
		}
	}

	onSaveAndCopyResult( result ) {
		super.onSaveAndCopyResult( result );
		if ( result && result.isValid() ) {
			this.showStatusReport( result, this.refresh_id );
		}
	}

	showStatusReport( result, id ) {
		var user_ids = id;
		var user_generic_status_batch_id = result.getAttributeInAPIDetails( 'user_generic_status_batch_id' );
		if ( user_generic_status_batch_id && TTUUID.isUUID( user_generic_status_batch_id ) && user_generic_status_batch_id != TTUUID.zero_id && user_generic_status_batch_id != TTUUID.not_exist_id ) {
			UserGenericStatusWindowController.open( user_generic_status_batch_id, user_ids );
		}
	}

	/**
	 * Originally copied from same function name in ReportBaseViewController
	 * FIXME: refactor to base class when needed in other children
	 * @param label
	 */
	checkFormSetupSaved( label ) {
		var $this = this;

		label = $.i18n._( 'Form Setup' );

		if ( this.form_setup_changed ) {
			$this.form_setup_changed = false;
			TAlertManager.showConfirmAlert( $.i18n._( 'You have modified' ) + ' ' + label + ' ' + $.i18n._( 'data without saving, would you like to save your data now?' ), '', function( flag ) {
				if ( flag ) {
					$this.onSaveSetup( label );
				}
			} );
		}
	}

	onFormItemChange( target, doNotValidate ) {
		if ( this.getEditViewTabIndex() == 1 ) {
			this.form_setup_changed = true;
		}

		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		switch ( key ) {
			case 'user_id':
				this.api['get' + this.api.key_name + 'DefaultData']( c_value, {
					onResult: function( res ) {
						var result = res.getResult();
						$this.edit_view_ui_dic['first_date'].setValue( result.first_date );
						$this.edit_view_ui_dic['last_date'].setValue( result.last_date );
						$this.edit_view_ui_dic['pay_period_end_date'].setValue( result.pay_period_end_date );
						$this.edit_view_ui_dic['final_pay_stub_end_date'].setValue( result.final_pay_stub_end_date );
						$this.edit_view_ui_dic['final_pay_stub_transaction_date'].setValue( result.final_pay_stub_transaction_date );
						$this.edit_view_ui_dic['pay_period_type_id'].setValue( result.pay_period_type_id );
						$this.edit_view_ui_dic['release_accruals'].setValue( result.release_accruals );
						$this.edit_view_ui_dic['generate_pay_stub'].setValue( result.generate_pay_stub );

						$this.current_edit_record.first_date = result.first_date;
						$this.current_edit_record.last_date = result.last_date;
						$this.current_edit_record.pay_period_end_date = result.pay_period_end_date;
						$this.current_edit_record.final_pay_stub_end_date = result.final_pay_stub_end_date;
						$this.current_edit_record.final_pay_stub_transaction_date = result.final_pay_stub_transaction_date;
						$this.current_edit_record.pay_period_type_id = result.pay_period_type_id;
						$this.current_edit_record.release_accruals = result.release_accruals;
						$this.current_edit_record.generate_pay_stub = result.generate_pay_stub;
						$this.current_edit_record[key] = c_value;
						if ( !doNotValidate ) {
							$this.validate();
						}
					}
				} );
				break;
			default:
				this.current_edit_record[key] = c_value;
				if ( !doNotValidate ) {
					this.validate();
				}
				break;
		}
	}

	uniformVariable( records ) {

		records.form = this.getFormSetupData( records ).form;

		return records;
	}

}