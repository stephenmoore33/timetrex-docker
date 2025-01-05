export class UserTitleViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_title_view_container',
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserTitleEditView.html';
		this.permission_id = 'user_title';
		this.viewId = 'UserTitle';
		this.script_name = 'UserTitleView';
		this.table_name_key = 'user_title';
		this.context_menu_name = $.i18n._( 'Employee Titles' );
		this.navigation_label = $.i18n._( 'Employee Title' );
		this.api = TTAPI.APIUserTitle;

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
			'tab_job_title': { 'label': $.i18n._( 'Employee Title' ) },
			'tab_audit': true
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIUserTitle,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_user_title',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_job_title = this.edit_view_tab.find( '#tab_job_title' );

		var tab_job_title_column1 = tab_job_title.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_job_title_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_job_title_column1, 'first_last' );

		form_item_input.parent().width( '45%' );

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
