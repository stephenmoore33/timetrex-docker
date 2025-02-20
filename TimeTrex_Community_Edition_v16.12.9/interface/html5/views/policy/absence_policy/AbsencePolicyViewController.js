export class AbsencePolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#absence_policy_view_container',


		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'AbsencePolicyEditView.html';
		this.permission_id = 'absence_policy';
		this.viewId = 'AbsencePolicy';
		this.script_name = 'AbsencePolicyView';
		this.table_name_key = 'absence_policy';
		this.context_menu_name = $.i18n._( 'Absence Policy' );
		this.navigation_label = $.i18n._( 'Absence Policy' );
		this.api = TTAPI.APIAbsencePolicy;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		var $this = this;
		//this.initDropDownOption( 'type' );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_absence_policy': { 'label': $.i18n._( 'Absence Policy' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIAbsencePolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_absences',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_absence_policy = this.edit_view_tab.find( '#tab_absence_policy' );

		var tab_absence_policy_column1 = tab_absence_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_absence_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_absence_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_absence_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Pay Code
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayCode,
			allow_multiple_selection: false,
			layout_name: 'global_pay_code',
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_code_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Code' ), form_item_input, tab_absence_policy_column1 );

		//Pay Formula Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayFormulaPolicy,
			allow_multiple_selection: false,
			layout_name: 'global_pay_formula_policy',
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_formula_policy_id',
			custom_first_label: $.i18n._( '-- Defined by Pay Code --' ),
			added_items: [
				{ value: TTUUID.zero_id, label: $.i18n._( '-- Defined by Pay Code --' ) }
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Formula Policy' ), form_item_input, tab_absence_policy_column1 );
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;
		/*
		 switch ( key ) {
		 case 'type_id':
		 case 'accrual_policy_id':
		 this.onTypeChange();
		 break;

		 }
		 */
		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setEditViewDataDone() {
		var $this = this;
		super.setEditViewDataDone();

		this.collectUIDataToCurrentEditRecord();
		this.onTypeChange();
	}

	onTypeChange() {
		/*
		 if ( this.current_edit_record['type_id'] == 20 ) {

		 this.edit_view_form_item_dic['rate'].css( 'display', 'none' );
		 this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'none' );
		 this.edit_view_form_item_dic['pay_stub_entry_account_id'].css( 'display', 'none' );

		 } else {
		 this.edit_view_form_item_dic['rate'].css( 'display', 'block' );
		 this.edit_view_form_item_dic['wage_group_id'].css( 'display', 'block' );
		 this.edit_view_form_item_dic['pay_stub_entry_account_id'].css( 'display', 'block' );
		 }

		 if ( this.current_edit_record.accrual_policy_id ) {
		 this.edit_view_form_item_dic['accrual_rate'].css( 'display', 'block' );

		 } else {
		 this.edit_view_form_item_dic['accrual_rate'].css( 'display', 'none' );
		 }
		 */
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
				label: $.i18n._( 'Pay Code' ),
				in_column: 1,
				field: 'pay_code_id',
				layout_name: 'global_pay_code',
				api_class: TTAPI.APIPayCode,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Pay Formula Policy' ),
				in_column: 1,
				field: 'pay_formula_policy_id',
				layout_name: 'global_pay_formula_policy',
				api_class: TTAPI.APIPayFormulaPolicy,
				multiple: true,
				basic_search: true,
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
