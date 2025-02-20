export class ContributingPayCodePolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#contributing_pay_code_policy_view_container',

			sub_document_view_controller: null,
			document_object_type_id: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'ContributingPayCodePolicyEditView.html';
		this.permission_id = 'contributing_pay_code_policy';
		this.viewId = 'ContributingPayCodePolicy';
		this.script_name = 'ContributingPayCodePolicyView';
		this.table_name_key = 'contributing_pay_code_policy';
		this.context_menu_name = $.i18n._( 'Contributing Pay Code Policy' );
		this.navigation_label = $.i18n._( 'Contributing Pay Code Policy' );
		this.api = TTAPI.APIContributingPayCodePolicy;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	buildEditViewUI() {
		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_contributing_pay_code_policy': { 'label': $.i18n._( 'Contributing Pay Code Policy' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIContributingPayCodePolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_contributing_pay_code_policy',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_contributing_pay_code_policy = this.edit_view_tab.find( '#tab_contributing_pay_code_policy' );

		var tab_contributing_pay_code_policy_column1 = tab_contributing_pay_code_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_contributing_pay_code_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_contributing_pay_code_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_contributing_pay_code_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Pay Codes
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayCode,
			allow_multiple_selection: true,
			layout_name: 'global_pay_code',
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_code'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Pay Codes' ), form_item_input, tab_contributing_pay_code_policy_column1 );
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
				label: $.i18n._( 'Pay Codes' ),
				in_column: 1,
				field: 'pay_code',
				layout_name: 'global_pay_code',
				api_class: TTAPI.APIPayCode,
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
