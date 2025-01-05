export class PayFormulaPolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#pay_formula_policy_view_container',

			type_array: null,
			pay_type_array: null,
			wage_source_type_array: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PayFormulaPolicyEditView.html';
		this.permission_id = 'pay_formula_policy';
		this.viewId = 'PayFormulaPolicy';
		this.script_name = 'PayFormulaPolicyView';
		this.table_name_key = 'pay_formula_policy';
		this.context_menu_name = $.i18n._( 'Pay Formula Policy' );
		this.navigation_label = $.i18n._( 'Pay Formula Policy' );
		this.api = TTAPI.APIPayFormulaPolicy;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		var $this = this;
		this.initDropDownOption( 'pay_type' );
		this.initDropDownOption( 'wage_source_type' );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_pay_formula_policy': { 'label': $.i18n._( 'Pay Formula Policy' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIPayFormulaPolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_pay_code',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_pay_formula_policy = this.edit_view_tab.find( '#tab_pay_formula_policy' );

		var tab_pay_formula_policy_column1 = tab_pay_formula_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_formula_policy_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_pay_formula_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_pay_formula_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Pay Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'pay_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.pay_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Pay Type' ), form_item_input, tab_pay_formula_policy_column1 );

		// Wage Source
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'wage_source_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.wage_source_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Wage Source' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );

		//Wage Source Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIContributingShiftPolicy,
			allow_multiple_selection: false,
			layout_name: 'global_contributing_shift_policy',
			show_search_inputs: true,
			set_empty: true,
			field: 'wage_source_contributing_shift_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Wage Source Contributing Shift Policy' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );

		//Time Source Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIContributingShiftPolicy,
			allow_multiple_selection: false,
			layout_name: 'global_contributing_shift_policy',
			show_search_inputs: true,
			set_empty: true,
			field: 'time_source_contributing_shift_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Time Source Contributing Shift Policy' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );


		//Average Days
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'average_days', width: 65 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> (' + $.i18n._( 'days' ) + ')</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Average Rate Over' ), form_item_input, tab_pay_formula_policy_column1, '', widgetContainer, true );


		// Premium
		// Hourly Rate
		// Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'rate', width: 65 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> (' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '1.5 for time and a half' ) + ')</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Rate' ), form_item_input, tab_pay_formula_policy_column1, '', widgetContainer, true );

		// Wage Group
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIWageGroup,
			allow_multiple_selection: false,
			layout_name: 'global_wage_group',
			show_search_inputs: true,
			set_default: true,
			field: 'wage_group_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Wage Group' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );

		// Deposit Accrual Policy Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIAccrualPolicyAccount,
			allow_multiple_selection: false,
			layout_name: 'global_accrual_policy_account',
			show_search_inputs: true,
			set_empty: true,
			field: 'accrual_policy_account_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Account' ), form_item_input, tab_pay_formula_policy_column1, '' );

		// Accrual Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'accrual_rate', width: 100 } );
		this.addEditFieldToColumn( $.i18n._( 'Accrual Rate' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );

		if ( Global.getProductEdition() >= 15 ) {
			// Accrual Balance Threshold
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( {
				field: 'accrual_balance_threshold',
				mode: 'time_unit',
				need_parser_sec: true
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
			label = $( '<span class=\'widget-right-label\'> (' + $.i18n._( 'Maximum Balance' ) + ')</span>' );

			widgetContainer.append( form_item_input );
			widgetContainer.append( label );

			this.addEditFieldToColumn( $.i18n._( 'Accrual Balance Threshold' ), form_item_input, tab_pay_formula_policy_column1, '', widgetContainer, true );

			// Accrual Balance Threshold Fallback Accrual Policy Account
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIAccrualPolicyAccount,
				allow_multiple_selection: false,
				layout_name: 'global_accrual_policy_account',
				show_search_inputs: true,
				set_empty: true,
				field: 'accrual_balance_threshold_fallback_accrual_policy_account_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Accrual Balance Threshold Fallback' ), form_item_input, tab_pay_formula_policy_column1, '', null, true );
		}
	}

	setCurrentEditRecordData() {

		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'pay_type_id': 10,
			'wage_source_type_id': 10
		} );

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

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
		this.onAccrualRateChange();
		this.onPayTypeChange();
		this.onWageSourceTypeChange();
		this.onAccrualAccountChange();

		this.setEditViewDataDone();
	}

	onAccrualRateChange() {
		//Issue #2956 - TypeError: this.edit_view_form_item_dic.accrual_balance_threshold is undefined
		if ( !this.edit_view_form_item_dic['accrual_balance_threshold'] ) {
			return;
		}

		var label;
		if ( Math.sign( this.current_edit_record.accrual_rate ) < 0 ) {
			label = $.i18n._( 'Minimum Balance' );
		} else {
			label = $.i18n._( 'Maximum Balance' );
		}

		this.edit_view_form_item_dic['accrual_balance_threshold'].find( '.widget-right-label' ).text( '(' + label + ')' );
	}

	onPayTypeChange() {
		this.attachElement( 'rate' );
		this.detachElement( 'wage_group_id' );
		this.detachElement( 'wage_source_type_id' );
		this.detachElement( 'wage_source_contributing_shift_policy_id' );
		this.detachElement( 'time_source_contributing_shift_policy_id' );

		if ( this.current_edit_record['pay_type_id'] == 10 ) {
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Rate' ) );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '1.5 for time and a half' ) + ')' );
			this.attachElement( 'wage_group_id' );
			this.attachElement( 'wage_source_type_id' );
		} else if ( this.current_edit_record['pay_type_id'] == 30 || this.current_edit_record['pay_type_id'] == 34 || this.current_edit_record['pay_type_id'] == 40 ) {
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Hourly Rate' ) );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '10.00/hr' ) + ')' );
			this.attachElement( 'wage_group_id' );
			this.attachElement( 'wage_source_type_id' );
		} else if ( this.current_edit_record['pay_type_id'] == 50 ) {
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Premium' ) );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '0.75 for 75 cent/hr' ) + ')' );
			this.attachElement( 'wage_group_id' );
			this.attachElement( 'wage_source_type_id' );
		} else if ( this.current_edit_record['pay_type_id'] == 32 ) {
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Hourly Rate' ) );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '10.00/hr' ) + ')' );
		} else if ( this.current_edit_record['pay_type_id'] == 42 ) {
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Hourly Rate' ) );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '10.00/hr' ) + ')' );
			this.attachElement( 'wage_group_id' );
			this.attachElement( 'wage_source_type_id' );
		} else if ( this.current_edit_record['pay_type_id'] == 60 ) { //60=Daily Rate
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Daily Rate' ) );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '100.00/day' ) + ')' );
			this.attachElement( 'wage_group_id' );
			//this.attachElement( 'wage_source_type_id' ); //Don't attach wage source as that is forced to Wage Group. But we need to specify the Wage Group still.
		} else if ( this.current_edit_record['pay_type_id'] == 70 ) { //70=Daily Rate (Average)
			this.detachElement( 'rate' ); //Rate can't be specified as its all an average.
		} else if ( this.current_edit_record['pay_type_id'] == 200 ) { //200=Piece Rate (per Good Quantity)
			this.edit_view_form_item_dic['rate'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Piece Rate' ) );
			this.edit_view_form_item_dic['rate'].find( '.widget-right-label' ).text( '(' + $.i18n._( 'ie' ) + ': ' + $.i18n._( '$1.00 per Good Quantity' ) + ')' );
		}

		this.editFieldResize();

		this.onWageSourceTypeChange();
	}

	onWageSourceTypeChange() {
		this.detachElement( 'wage_source_contributing_shift_policy_id' );
		this.detachElement( 'time_source_contributing_shift_policy_id' );
		this.detachElement( 'wage_group_id' );
		this.detachElement( 'average_days' );

		//Only display these fields if wage_source_type_id is also displayed.
		if ( this.edit_view_form_item_dic['wage_source_type_id'].css( 'display' ) === 'block' ) {
			if ( this.current_edit_record['wage_source_type_id'] == 10 ) { //10=Wage Group
				this.attachElement( 'wage_group_id' );
			} else if ( this.current_edit_record['wage_source_type_id'] == 30 ) { //30=Averaging
				this.attachElement( 'wage_source_contributing_shift_policy_id' );
				this.attachElement( 'time_source_contributing_shift_policy_id' );
			}
		} else {
			if ( this.current_edit_record['pay_type_id'] == 60 ) { //60=Daily Rate (w/Default). Forced to "Wage Group" wage_source_type_id
				this.attachElement( 'wage_group_id' );
			} else if ( this.current_edit_record['pay_type_id'] == 70 ) { //70=Daily Rate (Averaging)
				this.attachElement( 'wage_source_contributing_shift_policy_id' );
				this.attachElement( 'time_source_contributing_shift_policy_id' );
				this.attachElement( 'average_days' );
			}
		}

		this.editFieldResize();
	}

	onAccrualAccountChange() {
		if ( this.current_edit_record['accrual_policy_account_id'] === false || typeof this.current_edit_record['accrual_policy_account_id'] == 'undefined' || this.current_edit_record['accrual_policy_account_id'] == TTUUID.zero_id ) {
			this.detachElement( 'accrual_rate' );
			this.detachElement( 'accrual_balance_threshold' );
			this.detachElement( 'accrual_balance_threshold_fallback_accrual_policy_account_id' );
		} else {
			this.attachElement( 'accrual_rate' );
			this.attachElement( 'accrual_balance_threshold' );
			this.attachElement( 'accrual_balance_threshold_fallback_accrual_policy_account_id' );
		}

		this.editFieldResize();
	}

	onFormItemChange( target ) {
		this.is_changed = true;
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key === 'accrual_rate' ) {
			this.onAccrualRateChange();
		}

		if ( key === 'pay_type_id' ) {
			this.onPayTypeChange();
		}

		if ( key === 'wage_source_type_id' ) {
			this.onWageSourceTypeChange();
		}

		if ( key === 'accrual_policy_account_id' ) {
			this.onAccrualAccountChange();
		}

		this.validate();
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
				label: $.i18n._( 'Pay Type' ),
				in_column: 1,
				field: 'pay_type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Deposit to Accrual Policy' ),
				in_column: 1,
				field: 'accrual_policy_account_id',
				layout_name: 'global_accrual_policy_account',
				api_class: TTAPI.APIAccrualPolicyAccount,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
//
//			new SearchField( {label: $.i18n._( 'Pay Stub Account' ),
//				in_column: 1,
//				field: 'pay_stub_entry_account_id',
//				layout_name: 'global_PayStubAccount',
//				api_class: TTAPI.APIPayStubEntryAccount,
//				multiple: true,
//				basic_search: true,
//				adv_search: false,
//				form_item_type: FormItemType.AWESOME_BOX} ),

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
