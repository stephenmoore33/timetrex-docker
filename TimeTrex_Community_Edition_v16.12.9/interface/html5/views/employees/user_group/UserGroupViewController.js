export class UserGroupViewController extends BaseTreeViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_group_view_container',



			tree_mode: null,
			grid_table_name: null,
			grid_select_id_array: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserGroupEditView.html';
		this.permission_id = 'user_group';
		this.viewId = 'UserGroup';
		this.script_name = 'UserGroupView';
		this.table_name_key = 'user_group';
		this.context_menu_name = $.i18n._( 'Employee Groups' );
		this.grid_table_name = $.i18n._( 'Employee Groups' );
		this.navigation_label = $.i18n._( 'Employee Groups' );

		this.tree_mode = true;
		this.primary_tab_label = $.i18n._( 'Employee Group' );
		this.primary_tab_key = 'tab_employee_group';

		this.api = TTAPI.APIUserGroup;
		this.grid_select_id_array = [];

		this.render();
		this.buildContextMenu();
		this.initData();
	}

	getCustomContextMenuModel() {
		//We currently exclude *_and_next context icons due to unintended interactions with BaseTreeViewController views.
		//Navigation arrows have been fixed to work as expected. However, using delete_and_next to delete a parent record also deletes the child records
		//which means the attempt to go to the "next" record will fail as it no exists.
		var context_menu_model = {
			exclude: [
				'copy',
				'mass_edit',
				'delete_and_next',
				'save_and_continue',
				'save_and_next',
				'export_excel'
			],
			include: []
		};

		return context_menu_model;
	}

}