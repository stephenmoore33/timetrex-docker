export class Form1099NecReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

			province_array: null,

			state_field_array: null,

			schedule_deposit_array: null
		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'Form1099NecReport';
		this.viewId = 'Form1099NecReport';
		this.context_menu_name = $.i18n._( 'Form 1099-NEC' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'Form1099NecReportView.html';
		this.api = TTAPI.APIForm1099NecReport;
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

	onReportMenuClick( id ) {
		this.onViewClick( id );
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

	onCustomContextClick( id ) {
		switch ( id ) {
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

	buildFormSetupUI() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Nonemployee compensation (Box 1)
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l1_include_pay_stub_entry_account'
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
			field: 'l1_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Nonemployee compensation (Box 1)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );

		//Federal Income Tax Withheld (Box 4)
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: true,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'l4_include_pay_stub_entry_account'
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
			field: 'l4_exclude_pay_stub_entry_account'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Exclude' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Federal Income Tax Withheld (Box 4)' ), [form_item_input, form_item_input_1], tab3_column1, '', v_box, false, true );
	}

	getFormSetupData() {
		var other = {};

		other.l1 = {
			include_pay_stub_entry_account: this.current_edit_record.l1_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l1_exclude_pay_stub_entry_account
		};

		other.l4 = {
			include_pay_stub_entry_account: this.current_edit_record.l4_include_pay_stub_entry_account,
			exclude_pay_stub_entry_account: this.current_edit_record.l4_exclude_pay_stub_entry_account
		};

		return other;
	}

	/* jshint ignore:start */
	setFormSetupData( res_data ) {
		if ( !res_data ) {
			this.show_empty_message = true;
		} else {
			let batch_get_real_data = this.processFormSetupDataAndAddToBatch( res_data, [
				{ data: _.get(res_data, 'l1'), field_key: 'l1', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
				{ data: _.get(res_data, 'l4'), field_key: 'l4', api: this.api_paystub, api_method: 'getPayStubEntryAccount' },
			] );

			this.getBatchedRealFormDataFromAPI( batch_get_real_data );
		}
	}
	/* jshint ignore:end */
}
