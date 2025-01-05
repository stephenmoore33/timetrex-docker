export class FormW2ReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			kind_of_employer_array: null,
			form_type_array: null
		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'FormW2Report';
		this.viewId = 'FormW2Report';
		this.context_menu_name = $.i18n._( 'Form W2/W3' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'FormW2ReportView.html';
		this.api = TTAPI.APIFormW2Report;
		this.api_paystub = TTAPI.APIPayStubEntryAccount;
		this.include_form_setup = true;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				form: {
					label: $.i18n._( 'Form' ),
					id: this.viewId + 'Form'
				}
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Form' ),
					id: 'view_print',
					action_group_header: true,
					action_group: 'view_form',
					group: 'form',
					menu_align: 'right',
					icon: 'view-35x35.png',
					type: 2
				},
				{
					label: $.i18n._( 'View: Government (Multiple Employees/Page)' ),
					id: 'pdf_form_government',
					action_group: 'view_form',
					group: 'form',
					menu_align: 'right',
					icon: 'view-35x35.png',
					type: 2,
					sort_order: 10100
				},
				{
					label: $.i18n._( 'View: Employee (One Employee/Page)' ),
					id: 'pdf_form',
					action_group: 'view_form',
					group: 'form',
					menu_align: 'right',
					icon: 'view-35x35.png',
					type: 2,
					sort_order: 10200
				},
				{
					label: $.i18n._( 'eFile' ),
					id: 'efile',
					action_group: 'view_form',
					group: 'form',
					menu_align: 'right',
					sort_order: 10300
				},
				{
					label: $.i18n._( 'Save Setup' ),
					id: 'save_setup',
					action_group: 'view_form',
					group: 'form',
					menu_align: 'right',
					sort_order: 10400
				},
			]
		};

		if ( ( Global.getProductEdition() >= 15 ) ) {
			context_menu_model.include.push( {
				label: $.i18n._( 'Publish Employee Forms' ),
				id: 'pdf_form_publish_employee',
				action_group: 'view_form',
				menu_align: 'right',
				sort_order: 10250
			} );
		}

		return context_menu_model;
	}

	initOptions( callBack ) {
		var $this = this;
		var options = [
			{ option_name: 'page_orientation' },
			{ option_name: 'font_size' },
			{ option_name: 'chart_display_mode' },
			{ option_name: 'chart_type' },
			{ option_name: 'templates' },
			{ option_name: 'setup_fields' },
			{ option_name: 'kind_of_employer' },
			{ option_name: 'form_type' },
			{ option_name: 'auto_refresh' }
		];

		this.initDropDownOptions( options, function( result ) {
			callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
		} );
	}

	onSaveSetup( label ) {
		//Since Form Setup determines the column labels, clear the cache when form setup is saved.
		Global.clearCache( 'getOptions_columns' );
		super.onSaveSetup( label );
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'efile':
			case 'pdf_form':
			case 'pdf_form_government':
			case 'pdf_form_publish_employee':
				this.onReportMenuClick( id );
				break;
			default:
				return false; //FALSE tells onContextMenuClick() to keep processing.
		}

		return true;
	}

	onReportMenuClick( id ) {
		this.onViewClick( id );
	}

	buildFormSetupUI() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Form (W2/W2c)
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'form_type', set_empty: false } );
		form_item_input.setSourceData( $this.form_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Form' ), form_item_input, tab3_column1 );

		//Kind of Employer
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'kind_of_employer', set_empty: false } );
		form_item_input.setSourceData( $this.kind_of_employer_array );
		this.addEditFieldToColumn( $.i18n._( 'Kind of Employer' ), form_item_input, tab3_column1 );

		//Wages, Tips, Other Compensation (Box 1)
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l1_include_pay_stub_entry_account'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l1_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Wages, Tips, Other Compensation (Box 1)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Federal Income Tax Withheld (Box 2)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l2_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l2_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Federal Income Tax Withheld (Box 2)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Wages (Box 3)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l3_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l3_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Wages (Box 3)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Tax Withheld (Box 4)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l4_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l4_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Tax Withheld (Box 4)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Wages and Tips (Box 5)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l5_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l5_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Wages and Tips (Box 5)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Tax Withheld (Box 6)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l6_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l6_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Tax Withheld (Box 6)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Tips (Box 7)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l7_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l7_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Tips (Box 7)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Allocated Tips (Box 8)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l8_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l8_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Allocated Tips (Box 8)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Dependent Care Benefits (Box 10)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l10_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l10_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Dependent Care Benefits (Box 10)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Nonqualified Plans (Box 11)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l11_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l11_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Nonqualified Plans (Box 11)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );


		//Box 12a:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12a_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12a_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		var custom_label_widget = $( '<div class=\'h-box\'></div>' );
		var label = $( '<span class="edit-view-form-item-label"></span>' );
		var box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12a_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 12: Code' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12b:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12b_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12b_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12b_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 12: Code' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12c:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12c_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12c_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12c_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 12: Code' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12d:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12d_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12d_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12d_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 12: Code' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12e:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12e_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12e_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12e_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 12: Code' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12f:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12f_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12f_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12f_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 12: Code' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12g:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12g_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12g_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12g_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 12: Code' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 12h:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12h_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l12h_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l12h_code', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 12: Code' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 13 (Retirement Plan)
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APICompanyDeduction,
			allow_multiple_selection: true,
			layout_name: 'global_deduction',
			show_search_inputs: true,
			set_empty: true,
			field: 'l13b_company_deduction'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Retirement Plans (Box 13)' ), form_item_input, tab3_column1 );

		//Box 14a:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14a_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14a_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14a_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14b:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14b_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14b_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14b_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14c:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14c_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14c_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14c_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14d:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14d_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14d_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14d_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );







		//Box 14e:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14e_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14e_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14e_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14f:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14f_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14f_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14f_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14g:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14g_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14g_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14g_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box 14h:
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14h_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Exclude
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l14h_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'l14h_name', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target, true );
		} );

		label.text( $.i18n._( 'Box 14 (Other): Name' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );


		//Third Party Sick Pay included in Box 1 (Used to check Third Party Sick Pay box)
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l13c_include_pay_stub_entry_account'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Third Party Sick Pay Included in Box 1, 3, 5' ), form_item_input, tab3_column1 );
	}

	getFormSetupData() {
		var other = {};

		other.form_type = this.current_edit_record.form_type;
		other.kind_of_employer = this.current_edit_record.kind_of_employer;

		other.l1 = {
			include_pay_stub_entry_account: this.current_edit_record.l1_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l1_exclude_pay_stub_entry_account
		};

		other.l2 = {
			include_pay_stub_entry_account: this.current_edit_record.l2_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l2_exclude_pay_stub_entry_account
		};

		other.l3 = {
			include_pay_stub_entry_account: this.current_edit_record.l3_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l3_exclude_pay_stub_entry_account
		};

		other.l4 = {
			include_pay_stub_entry_account: this.current_edit_record.l4_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l4_exclude_pay_stub_entry_account
		};

		other.l5 = {
			include_pay_stub_entry_account: this.current_edit_record.l5_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l5_exclude_pay_stub_entry_account
		};

		other.l6 = {
			include_pay_stub_entry_account: this.current_edit_record.l6_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l6_exclude_pay_stub_entry_account
		};

		other.l7 = {
			include_pay_stub_entry_account: this.current_edit_record.l7_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l7_exclude_pay_stub_entry_account
		};

		other.l8 = {
			include_pay_stub_entry_account: this.current_edit_record.l8_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l8_exclude_pay_stub_entry_account
		};

		other.l10 = {
			include_pay_stub_entry_account: this.current_edit_record.l10_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l10_exclude_pay_stub_entry_account
		};

		other.l11 = {
			include_pay_stub_entry_account: this.current_edit_record.l11_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l11_exclude_pay_stub_entry_account
		};

		other.l12a = {
			include_pay_stub_entry_account: this.current_edit_record.l12a_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12a_exclude_pay_stub_entry_account
		};

		other.l12b = {
			include_pay_stub_entry_account: this.current_edit_record.l12b_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12b_exclude_pay_stub_entry_account
		};

		other.l12c = {
			include_pay_stub_entry_account: this.current_edit_record.l12c_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12c_exclude_pay_stub_entry_account
		};

		other.l12d = {
			include_pay_stub_entry_account: this.current_edit_record.l12d_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12d_exclude_pay_stub_entry_account
		};

		other.l12e = {
			include_pay_stub_entry_account: this.current_edit_record.l12e_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12e_exclude_pay_stub_entry_account
		};

		other.l12f = {
			include_pay_stub_entry_account: this.current_edit_record.l12f_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12f_exclude_pay_stub_entry_account
		};

		other.l12g = {
			include_pay_stub_entry_account: this.current_edit_record.l12g_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12g_exclude_pay_stub_entry_account
		};

		other.l12h = {
			include_pay_stub_entry_account: this.current_edit_record.l12h_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l12h_exclude_pay_stub_entry_account
		};

		other.l13c = {
			include_pay_stub_entry_account: this.current_edit_record.l13c_include_pay_stub_entry_account,
			//exclude_pay_stub_entry_account: this.current_edit_record.l13c_exclude_pay_stub_entry_account
		};

		other.l13b = {
			company_deduction: this.current_edit_record.l13b_company_deduction
		};

		other.l14a = {
			include_pay_stub_entry_account: this.current_edit_record.l14a_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14a_exclude_pay_stub_entry_account
		};

		other.l14b = {
			include_pay_stub_entry_account: this.current_edit_record.l14b_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14b_exclude_pay_stub_entry_account
		};

		other.l14c = {
			include_pay_stub_entry_account: this.current_edit_record.l14c_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14c_exclude_pay_stub_entry_account
		};

		other.l14d = {
			include_pay_stub_entry_account: this.current_edit_record.l14d_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14d_exclude_pay_stub_entry_account
		};

		other.l14e = {
			include_pay_stub_entry_account: this.current_edit_record.l14e_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14e_exclude_pay_stub_entry_account
		};

		other.l14f = {
			include_pay_stub_entry_account: this.current_edit_record.l14f_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14f_exclude_pay_stub_entry_account
		};

		other.l14g = {
			include_pay_stub_entry_account: this.current_edit_record.l14g_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14g_exclude_pay_stub_entry_account
		};

		other.l14h = {
			include_pay_stub_entry_account: this.current_edit_record.l14h_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l14h_exclude_pay_stub_entry_account
		};

		other.l12a_code = this.current_edit_record.l12a_code;
		other.l12b_code = this.current_edit_record.l12b_code;
		other.l12c_code = this.current_edit_record.l12c_code;
		other.l12d_code = this.current_edit_record.l12d_code;
		other.l12e_code = this.current_edit_record.l12e_code;
		other.l12f_code = this.current_edit_record.l12f_code;
		other.l12g_code = this.current_edit_record.l12g_code;
		other.l12h_code = this.current_edit_record.l12h_code;
		other.l14a_name = this.current_edit_record.l14a_name;
		other.l14b_name = this.current_edit_record.l14b_name;
		other.l14c_name = this.current_edit_record.l14c_name;
		other.l14d_name = this.current_edit_record.l14d_name;
		other.l14e_name = this.current_edit_record.l14e_name;
		other.l14f_name = this.current_edit_record.l14f_name;
		other.l14g_name = this.current_edit_record.l14g_name;
		other.l14h_name = this.current_edit_record.l14h_name;

		return other;
	}

	/* jshint ignore:start */
	setFormSetupData( res_data ) {
		if ( !res_data ) {
			this.show_empty_message = true;
		} else {
			let batch_get_real_data = this.processFormSetupDataAndAddToBatch( res_data, [
				{ data: _.get(res_data, 'form_type'), field_key: 'form_type', api: null },
				{ data: _.get(res_data, 'kind_of_employer'), field_key: 'kind_of_employer', api: null },
				{ data: _.get(res_data, 'l1'), field_key: 'l1', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l2'), field_key: 'l2', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l3'), field_key: 'l3', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l4'), field_key: 'l4', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l5'), field_key: 'l5', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l6'), field_key: 'l6', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l7'), field_key: 'l7', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l8'), field_key: 'l8', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l10'), field_key: 'l10', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l11'), field_key: 'l11', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12a'), field_key: 'l12a', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12b'), field_key: 'l12b', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12c'), field_key: 'l12c', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12d'), field_key: 'l12d', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12e'), field_key: 'l12e', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12f'), field_key: 'l12f', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12g'), field_key: 'l12g', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12h'), field_key: 'l12h', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data:  _.get(res_data, ['l13b', 'company_deduction']), field_key: 'l13b_company_deduction', api: null },
				{ data: _.get(res_data, 'l13c'), field_key: 'l13c', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l14a'), field_key: 'l14a', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l14b'), field_key: 'l14b', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l14c'), field_key: 'l14c', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l14d'), field_key: 'l14d', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l14e'), field_key: 'l14e', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l14f'), field_key: 'l14f', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l14g'), field_key: 'l14g', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l14h'), field_key: 'l14h', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l12a_code'), field_key: 'l12a_code', api: null },
				{ data: _.get(res_data, 'l12b_code'), field_key: 'l12b_code', api: null },
				{ data: _.get(res_data, 'l12c_code'), field_key: 'l12c_code', api: null },
				{ data: _.get(res_data, 'l12d_code'), field_key: 'l12d_code', api: null },
				{ data: _.get(res_data, 'l12e_code'), field_key: 'l12e_code', api: null },
				{ data: _.get(res_data, 'l12f_code'), field_key: 'l12f_code', api: null },
				{ data: _.get(res_data, 'l12g_code'), field_key: 'l12g_code', api: null },
				{ data: _.get(res_data, 'l12h_code'), field_key: 'l12h_code', api: null },
				{ data: _.get(res_data, 'l14a_name'), field_key: 'l14a_name', api: null },
				{ data: _.get(res_data, 'l14b_name'), field_key: 'l14b_name', api: null },
				{ data: _.get(res_data, 'l14c_name'), field_key: 'l14c_name', api: null },
				{ data: _.get(res_data, 'l14d_name'), field_key: 'l14d_name', api: null },
				{ data: _.get(res_data, 'l14e_name'), field_key: 'l14e_name', api: null },
				{ data: _.get(res_data, 'l14f_name'), field_key: 'l14f_name', api: null },
				{ data: _.get(res_data, 'l14g_name'), field_key: 'l14g_name', api: null },
				{ data: _.get(res_data, 'l14h_name'), field_key: 'l14h_name', api: null },
			] );

			this.getBatchedRealFormDataFromAPI( batch_get_real_data );
		}
	}

	/* jshint ignore:end */
}
