export class AccrualPolicyAccountViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#wage_group_view_container',


		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'AccrualPolicyAccountEditView.html';
		this.permission_id = 'accrual_policy';
		this.viewId = 'AccrualPolicyAccount';
		this.script_name = 'AccrualPolicyAccountView';
		this.table_name_key = 'accrual_policy_account';
		this.context_menu_name = $.i18n._( 'Accrual Account' );
		this.navigation_label = $.i18n._( 'Accrual Account' );
		this.api = TTAPI.APIAccrualPolicyAccount;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['mass_edit'],
			include: []
		};

		return context_menu_model;
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_accrual_account': { 'label': $.i18n._( 'Accrual Account' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIAccrualPolicyAccount,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_accrual_policy_account',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_accrual_account = this.edit_view_tab.find( '#tab_accrual_account' );

		var tab_accrual_account_column1 = tab_accrual_account.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_accrual_account_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_accrual_account_column1, 'first_last' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_accrual_account_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		//Display Balance on Pay Stub
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_pay_stub_balance_display' } );
		this.addEditFieldToColumn( $.i18n._( 'Display Balance on Pay Stub' ), form_item_input, tab_accrual_account_column1 );
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

AccrualPolicyAccountViewController.html_template = `
<div class="view wage-group-view" id="wage_group_view_container">
	<div class="clear-both-div"></div>
	<div class="grid-div">
		<div class="total-number-div">
			<span class="total-number-span"></span>
		</div>
		<div class="grid-top-border"></div>
		<table id="grid"></table>
		<div class="bottom-div">
			<div class="grid-bottom-border"></div>
		</div>
	</div>
</div>`;