export class T4ASummaryReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			type_array: null
		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'T4ASummaryReport';
		this.viewId = 'T4ASummaryReport';
		this.context_menu_name = $.i18n._( 'T4A Summary' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'T4ASummaryReportView.html';
		this.api = TTAPI.APIT4ASummaryReport;
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
					id: 'e_file_xml',
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
			{ option_name: 'type' },
			{ option_name: 'auto_refresh' },
			{ option_name: 'custom_fields' },
			{ option_name: 'dental_benefit_codes' }
		];

		this.initDropDownOptions( options, function( result ) {
			TTAPI.APICompany.getOptions( 'province', 'CA', {
				onResult: function( provinceResult ) {
					$this.province_array = Global.buildRecordArray( provinceResult.getResult() );

					callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
				}
			} );

		} );
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'e_file_xml': //All report view
				this.onReportMenuClick( 'efile_xml' );
				break;
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

	onFormItemChange( target, doNotValidate ) {
		if ( target && target.getField && target.getField() == 'dental_benefit_code' ) { // cannot read property getField of undefined
			var dental_benefit_custom_field = this.edit_view_ui_dic.dental_benefit_custom_field;
			if ( target.getValue() == 'custom_field' ) {
				dental_benefit_custom_field.css( 'display', 'inline' );
			} else {
				dental_benefit_custom_field.css( 'display', 'none' );
				dental_benefit_custom_field.setValue( '' );
			}
		}

		super.onFormItemChange( target, doNotValidate );
	}

	buildFormSetupUI() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Status

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id', set_empty: false } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab3_column1 );

		//Pension Or Superannuation (Box: 16)
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'pension_include_pay_stub_entry_account'
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
			field: 'pension_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Pension Or Superannuation (Box: 16)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Lump-sum Payments (Box: 18)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'lump_sum_payment_include_pay_stub_entry_account'
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
			field: 'lump_sum_payment_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Lump-sum Payments (Box: 18)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Self-Employed Commisions  (Box: 20)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'self_employed_commission_include_pay_stub_entry_account'
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
			field: 'self_employed_commission_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Self-Employed Commisions  (Box: 20)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Income Tax Deducted (Box: 22)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'income_tax_include_pay_stub_entry_account'
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
			field: 'income_tax_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Income Tax Deducted (Box 22)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Annuities (Box: 27)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'annuities_include_pay_stub_entry_account'
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
			field: 'annuities_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Annuities (Box 24)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Fees for Services (Box: 48)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'service_fees_include_pay_stub_entry_account'
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
			field: 'service_fees_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Fees for Services (Box: 48)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );


		//Dental Benefits Code (Box 45)
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'dental_benefit_code' } );
		form_item_input.setSourceData( $this.dental_benefit_codes_array );

		var h_box = $( '<div class=\'h-box\'></div>' );
		var dental_benefit_custom_field = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		dental_benefit_custom_field.css( 'margin-left', '10px' );
		dental_benefit_custom_field.TComboBox( { field: 'dental_benefit_custom_field' } );
		dental_benefit_custom_field.setSourceData( $this.custom_fields_array );
		h_box.append( form_item_input );
		h_box.append( dental_benefit_custom_field );

		this.addEditFieldToColumn( $.i18n._( 'Dental Benefit Code (Box 45)' ), [form_item_input, dental_benefit_custom_field], tab3_column1, '', h_box, false, true );
		this.setWidgetVisible( [form_item_input, dental_benefit_custom_field] );


		//Box [0]
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'box_0_include_pay_stub_entry_account'
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
			field: 'box_0_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		var custom_label_widget = $( '<div class=\'h-box\'></div>' );
		var label = $( '<span class="edit-view-form-item-label"></span>' );
		var box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'box_0_box', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box [1]
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'box_1_include_pay_stub_entry_account'
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
			field: 'box_1_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'box_1_box', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box [2]
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'box_2_include_pay_stub_entry_account'
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
			field: 'box_2_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'box_2_box', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box [3]
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'box_3_include_pay_stub_entry_account'
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
			field: 'box_3_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'box_3_box', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box [4]
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'box_4_include_pay_stub_entry_account'
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
			field: 'box_4_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'box_4_box', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );

		//Box [5]
		v_box = $( '<div class=\'v-box\'></div>' );

		//Include
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'box_5_include_pay_stub_entry_account'
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
			field: 'box_5_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		custom_label_widget = $( '<div class=\'h-box\'></div>' );
		label = $( '<span class="edit-view-form-item-label"></span>' );
		box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		box.TTextInput( { field: 'box_5_box', width: 50 } );
		box.css( 'float', 'right' );
		box.bind( 'formItemChange', function( e, target ) {
			$this.onFormItemChange( target );
		} );

		label.text( $.i18n._( 'Box' ) );

		this.edit_view_ui_dic[box.getField()] = box;

		custom_label_widget.append( box );
		custom_label_widget.append( label );
``
		this.addEditFieldToColumn( $.i18n._( 'Box' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true, false, false, custom_label_widget );


		//Remittances Paid in Year
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'remittances_paid', width: 120 } );
		this.addEditFieldToColumn( $.i18n._( 'Total Remittances Paid in Year' ) +' $', form_item_input, tab3_column1 );
	}

	getFormSetupData() {
		var other = {};
		other.pension = {
			include_pay_stub_entry_account: this.current_edit_record.pension_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.pension_exclude_pay_stub_entry_account
		};

		other.lump_sum_payment = {
			include_pay_stub_entry_account: this.current_edit_record.lump_sum_payment_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.lump_sum_payment_exclude_pay_stub_entry_account
		};

		other.self_employed_commission = {
			include_pay_stub_entry_account: this.current_edit_record.self_employed_commission_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.self_employed_commission_exclude_pay_stub_entry_account
		};

		other.income_tax = {
			include_pay_stub_entry_account: this.current_edit_record.income_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.income_tax_exclude_pay_stub_entry_account
		};

		other.annuities = {
			include_pay_stub_entry_account: this.current_edit_record.annuities_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.annuities_exclude_pay_stub_entry_account
		};

		other.service_fees = {
			include_pay_stub_entry_account: this.current_edit_record.service_fees_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.service_fees_exclude_pay_stub_entry_account
		};

		other.dental_benefit_custom_field = this.current_edit_record.dental_benefit_custom_field;
		other.dental_benefit_code = this.current_edit_record.dental_benefit_code;

		other.other_box = [];

		other.other_box.push( {
			box: this.current_edit_record.box_0_box,
			include_pay_stub_entry_account: this.current_edit_record.box_0_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.box_0_exclude_pay_stub_entry_account
		} );

		other.other_box.push( {
			box: this.current_edit_record.box_1_box,
			include_pay_stub_entry_account: this.current_edit_record.box_1_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.box_1_exclude_pay_stub_entry_account
		} );

		other.other_box.push( {
			box: this.current_edit_record.box_2_box,
			include_pay_stub_entry_account: this.current_edit_record.box_2_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.box_2_exclude_pay_stub_entry_account
		} );

		other.other_box.push( {
			box: this.current_edit_record.box_3_box,
			include_pay_stub_entry_account: this.current_edit_record.box_3_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.box_3_exclude_pay_stub_entry_account
		} );

		other.other_box.push( {
			box: this.current_edit_record.box_4_box,
			include_pay_stub_entry_account: this.current_edit_record.box_4_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.box_4_exclude_pay_stub_entry_account
		} );

		other.other_box.push( {
			box: this.current_edit_record.box_5_box,
			include_pay_stub_entry_account: this.current_edit_record.box_5_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.box_5_exclude_pay_stub_entry_account
		} );

		other.status_id = this.current_edit_record.status_id;

		other.remittances_paid = this.current_edit_record.remittances_paid;

		return other;
	}

	/* jshint ignore:start */
	setFormSetupData( res_data ) {

		if ( !res_data ) {
			this.show_empty_message = true;
		} else {
			let batch_get_real_data = this.processFormSetupDataAndAddToBatch( res_data, [
				{ data: _.get(res_data, 'pension'), field_key: 'pension', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'lump_sum_payment'), field_key: 'lump_sum_payment', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'self_employed_commission'), field_key: 'self_employed_commission', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'income_tax'), field_key: 'income_tax', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'annuities'), field_key: 'annuities', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'service_fees'), field_key: 'service_fees', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'status_id'), field_key: 'status_id', api: null },
				{ data: _.get(res_data, 'remittances_paid'), field_key: 'remittances_paid', api: null },
				{ data: _.get(res_data, 'dental_benefit_custom_field'), field_key:'dental_benefit_custom_field', api: null },
				{ data: _.get(res_data, 'dental_benefit_code'), field_key:'dental_benefit_code', api: null },
				{ data: _.get(res_data, ['other_box', 0 ]), field_key: 'box_0', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, ['other_box', 1 ]), field_key: 'box_1', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, ['other_box', 2 ]), field_key: 'box_2', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, ['other_box', 3 ]), field_key: 'box_3', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, ['other_box', 4 ]), field_key: 'box_4', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, ['other_box', 5 ]), field_key: 'box_5', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
			] );

			this.getBatchedRealFormDataFromAPI( batch_get_real_data );

			this.onFormItemChange( this.edit_view_ui_dic.dental_benefit_code ); //Make sure we show/hide this field when first loading.
		}
	}

	/* jshint ignore:end */
}
