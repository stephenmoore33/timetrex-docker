import '@/global/widgets/formula_builder/FormulaBuilder.js';

export class PayStubEntryAccountViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#pay_stub_entry_account_view_container',

			type_array: null,
			status_array: null,
			accrual_type_array: null,

			api_general_ledger_summary_report: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PayStubEntryAccountEditView.html';
		this.permission_id = 'pay_stub_account';
		this.viewId = 'PayStubEntryAccount';
		this.script_name = 'PayStubEntryAccountView';
		this.table_name_key = 'pay_stub_entry_account';
		this.context_menu_name = $.i18n._( 'Pay Stub Accounts' );
		this.navigation_label = $.i18n._( 'Pay Stub Account' );
		this.api = TTAPI.APIPayStubEntryAccount;
		this.api_general_ledger_summary_report = TTAPI.APIGeneralLedgerSummaryReport;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'status', api: this.api },
			{ option_name: 'accrual_type', api: this.api },
		];

		this.initDropDownOptions( options );
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key === 'type_id' ) {
			this.onTypeChange();
		}
		if ( key === 'accrual_pay_stub_entry_account_id' ) {
			this.onAccrualPayStubEntryAccountChange();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onTypeChange();
		this.onAccrualPayStubEntryAccountChange();
	}

	onTypeChange() {
		if ( this.current_edit_record.type_id == 50 ) {
			this.detachElement( 'accrual_pay_stub_entry_account_id' );
			this.detachElement( 'accrual_type_id' );

		} else {
			this.attachElement( 'accrual_pay_stub_entry_account_id' );
			this.onAccrualPayStubEntryAccountChange();
		}

		this.editFieldResize();
	}

	onAccrualPayStubEntryAccountChange() {
		if ( TTUUID.isUUID( this.current_edit_record.accrual_pay_stub_entry_account_id ) && this.current_edit_record.accrual_pay_stub_entry_account_id != TTUUID.zero_id ) {
			this.attachElement( 'accrual_type_id' );
		} else {
			this.detachElement( 'accrual_type_id' );
		}

		this.editFieldResize();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [
				{
					label: '', //Empty label. vue_icon is displayed instead of text.
					id: 'other_header',
					menu_align: 'right',
					action_group: 'other',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
				},
				{
					label: $.i18n._( 'Migrate PS Accounts' ),
					id: 'migrate_pay_stub_account',
					menu_align: 'right',
					action_group: 'other'
				}
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'migrate_pay_stub_account':
				this.onWizardClick();
				break;

		}
	}

	onWizardClick() {
		var $this = this;
		IndexViewController.openWizard( 'PayStubAccountWizard', null, function() {
		} );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_pay_stub_account': { 'label': $.i18n._( 'Pay Stub Account' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_PayStubAccount',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_pay_stub_account = this.edit_view_tab.find( '#tab_pay_stub_account' );

		var tab_pay_stub_account_column1 = tab_pay_stub_account.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_stub_account_column1 );

		//Status

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id', set_empty: false } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_pay_stub_account_column1, '' );

		//Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_pay_stub_account_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_pay_stub_account_column1 );

		form_item_input.parent().width( '45%' );

		//Order

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'ps_order', width: 359 } );
		this.addEditFieldToColumn( $.i18n._( 'Order' ), form_item_input, tab_pay_stub_account_column1 );

		//Accrual

		var args = {};
		var filter_data = {};
		filter_data.type_id = [50, 80];
		args.filter_data = filter_data;

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: false,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_pay_stub_entry_account_id'

		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Accrual' ), form_item_input, tab_pay_stub_account_column1, '', null, true );
		// Accrual Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'accrual_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.accrual_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Type' ), form_item_input, tab_pay_stub_account_column1, '', null, true );

		// Debit Account Formula builder
		form_item_input = Global.loadWidgetByName( FormItemType.FORMULA_BUILDER );
		form_item_input.FormulaBuilder( {
			field: 'debit_account', width: '350px', field_type: 'text_input', text_label: $.i18n._('Variables'), onFormulaBtnClick:  Global.debounce( function() {
				$this.api_general_ledger_summary_report.getOptions( 'debit_credit_variables', {
					onResult: function( fun_result ) {
						var fun_res_data = fun_result.getResult();

						var default_args = {};
						default_args.do_not_validate = true;
						default_args.do_not_show_functions = true;
						default_args.variables = Global.buildRecordArray( fun_res_data );
						default_args.formula = $this.current_edit_record.debit_account;
						default_args.current_edit_record = Global.clone( $this.current_edit_record );
						default_args.api = $this.api_general_ledger_summary_report;

						IndexViewController.openWizard( 'FormulaBuilderWizard', default_args, function( val ) {
							$this.current_edit_record.debit_account = val;
							$this.edit_view_ui_dic.debit_account.setValue( val );
						} );

					}
				} );
			} )
		} );

		$this.addEditFieldToColumn( $.i18n._( 'Debit Account' ), form_item_input, tab_pay_stub_account_column1, '', null, true, true );

		// Credit Account Formula builder
		form_item_input = Global.loadWidgetByName( FormItemType.FORMULA_BUILDER );
		form_item_input.FormulaBuilder( {
			field: 'credit_account', width: '350px', field_type: 'text_input', text_label: $.i18n._('Variables'), onFormulaBtnClick:  Global.debounce( function() {
				$this.api_general_ledger_summary_report.getOptions( 'debit_credit_variables', {
					onResult: function( fun_result ) {
						var fun_res_data = fun_result.getResult();

						var default_args = {};
						default_args.do_not_validate = true;
						default_args.do_not_show_functions = true;
						default_args.variables = Global.buildRecordArray( fun_res_data );
						default_args.formula = $this.current_edit_record.credit_account;
						default_args.current_edit_record = Global.clone( $this.current_edit_record );
						default_args.api = $this.api_general_ledger_summary_report;

						IndexViewController.openWizard( 'FormulaBuilderWizard', default_args, function( val ) {
							$this.current_edit_record.credit_account = val;
							$this.edit_view_ui_dic.credit_account.setValue( val );
						} );

					}
				} );
			} )
		} );

		$this.addEditFieldToColumn( $.i18n._( 'Credit Account' ), form_item_input, tab_pay_stub_account_column1, '', null, true, true );
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
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Debit Account' ),
				in_column: 1,
				field: 'debit_account',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Credit Account' ),
				in_column: 1,
				field: 'credit_account',
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

}
