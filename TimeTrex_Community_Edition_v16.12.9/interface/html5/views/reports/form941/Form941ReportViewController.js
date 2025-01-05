export class Form941ReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

			return_type_array: null,
			exempt_payment_array: null,
			state_array: null,
			province_array: null,
			schedule_deposit_array: null
		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'Form941Report';
		this.viewId = 'Form941Report';
		this.context_menu_name = $.i18n._( 'Form 941' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'Form941ReportView.html';
		this.api = TTAPI.APIForm941Report;
		this.api_paystub = TTAPI.APIPayStubEntryAccount;
		this.include_form_setup = true;
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
			{ option_name: 'schedule_deposit' },
			{ option_name: 'auto_refresh' }
		];

		this.initDropDownOptions( options, function( result ) {
			TTAPI.APICompany.getOptions( 'province', 'US', {
				onResult: function( provinceResult ) {
					$this.province_array = Global.buildRecordArray( provinceResult.getResult() );

					callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
				}
			} );

		} );
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
					label: $.i18n._( 'View' ),
					id: 'view_form',
					action_group: 'view_form',
					group: 'form',
					menu_align: 'right',
					},
				{
					label: $.i18n._( 'Save Setup' ),
					id: 'save_setup',
					action_group: 'view_form',
					group: 'form',
					menu_align: 'right',
					}
			]
		};

		return context_menu_model;
	}

	buildFormSetupUI() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		//Schedule Depositor
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input = form_item_input.AComboBox( {
			field: 'deposit_schedule',
			allow_multiple_selection: false,
			layout_name: 'global_option_column',
			key: 'value'
		} );

		form_item_input.setSourceData( $this.schedule_deposit_array );
		this.addEditFieldToColumn( $.i18n._( 'Schedule Depositor' ), form_item_input, tab3_column1, '' );

		//Total Deposits For This Quarter
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'quarter_deposit' } );
		this.addEditFieldToColumn( $.i18n._( 'Total Deposits for the Quarter' ) +' $', form_item_input, tab3_column1 );

		//Wages, tips and other compensation (Line 2)
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'wages_include_pay_stub_entry_account'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'wages_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Wages, tips and other compensation (Line 2)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Income Tax (Line 3)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
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

		//Selection
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

		this.addEditFieldToColumn( $.i18n._( 'Income Tax (Line 3)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Social Security Wages (Line 5a)
		v_box = $( '<div class=\'v-box\'></div>' );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection Type
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_wages_include_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );
		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX ); //Selection
		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_wages_exclude_pay_stub_entry_account'
		} );
		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );
		v_box.append( form_item );
		this.addEditFieldToColumn( $.i18n._( 'Taxable Social Security Wages (Line 5a)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );


		//Social Security Taxes Withheld
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Taxes Withheld' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Social Security Taxes - Employer
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_employer_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tax_employer_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Social Security Employer' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Social Security Tips (Line 5b)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tips_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'social_security_tips_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Taxable Social Security Tips (Line 5b)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Taxable Medicare Wages (Line 5c)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_wages_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_wages_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Taxable Medicare Wages (Line 5c)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Taxes Withheld
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_exclude_pay_stub_entry_account'

		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Taxes Withheld' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Medicare Taxes - Employer
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_employer_include_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Include' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'medicare_tax_employer_exclude_pay_stub_entry_account'

		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Medicare Employer' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );


		//Third Party Sick Pay adjustment (Line 8) -- This should be a negative adjustment.
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'third_party_sick_pay_adjustment_include_pay_stub_entry_account'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Adjustments for Third Party Sick Pay (Line 8)' ), form_item_input, tab3_column1 );
	}

	getFormSetupData() {
		var other = {};

		other.deposit_schedule = this.current_edit_record.deposit_schedule;
		other.quarter_deposit = this.current_edit_record.quarter_deposit;

		other.wages = {
			include_pay_stub_entry_account: this.current_edit_record.wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.wages_exclude_pay_stub_entry_account
		};

		other.income_tax = {
			include_pay_stub_entry_account: this.current_edit_record.income_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.income_tax_exclude_pay_stub_entry_account
		};

		other.social_security_wages = {
			include_pay_stub_entry_account: this.current_edit_record.social_security_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_wages_exclude_pay_stub_entry_account
		};

		other.social_security_tax = {
			include_pay_stub_entry_account: this.current_edit_record.social_security_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_tax_exclude_pay_stub_entry_account
		};

		other.social_security_tax_employer = {
			include_pay_stub_entry_account: this.current_edit_record.social_security_tax_employer_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_tax_employer_exclude_pay_stub_entry_account
		};

		other.social_security_tips = {
			include_pay_stub_entry_account: this.current_edit_record.social_security_tips_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.social_security_tips_exclude_pay_stub_entry_account
		};

		other.medicare_wages = {
			include_pay_stub_entry_account: this.current_edit_record.medicare_wages_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.medicare_wages_exclude_pay_stub_entry_account
		};

		other.medicare_tax = {
			include_pay_stub_entry_account: this.current_edit_record.medicare_tax_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.medicare_tax_exclude_pay_stub_entry_account
		};

		other.medicare_tax_employer = {
			include_pay_stub_entry_account: this.current_edit_record.medicare_tax_employer_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.medicare_tax_employer_exclude_pay_stub_entry_account
		};

		other.third_party_sick_pay_adjustment = {
			include_pay_stub_entry_account: this.current_edit_record.third_party_sick_pay_adjustment_include_pay_stub_entry_account,
			//exclude_pay_stub_entry_account: this.current_edit_record.third_party_sick_pay_adjustment_exclude_pay_stub_entry_account
		};

		return other;
	}

	/* jshint ignore:start */
	setFormSetupData( res_data ) {

		if ( !res_data ) {
			this.show_empty_message = true;
		} else {
			let batch_get_real_data = this.processFormSetupDataAndAddToBatch( res_data, [
				{ data: _.get(res_data, 'deposit_schedule'), field_key: 'deposit_schedule', api: null },
				{ data: _.get(res_data, 'quarter_deposit'), field_key: 'quarter_deposit', api: null },
				{ data: _.get(res_data, 'wages'), field_key: 'wages', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'income_tax'), field_key: 'income_tax', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'social_security_wages'), field_key: 'social_security_wages', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'social_security_tax'), field_key: 'social_security_tax', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'social_security_tax_employer'), field_key: 'social_security_tax_employer', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'social_security_tips'), field_key: 'social_security_tips', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'medicare_wages'), field_key: 'medicare_wages', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'medicare_tax'), field_key: 'medicare_tax', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'medicare_tax_employer'), field_key: 'medicare_tax_employer', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'third_party_sick_pay_adjustment'), field_key: 'third_party_sick_pay_adjustment', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
			] );

			this.getBatchedRealFormDataFromAPI( batch_get_real_data );
		}
	}

	/* jshint ignore:end */

}
