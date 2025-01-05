export class QualificationGroupViewController extends BaseTreeViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#qualification_group_view_container',



			tree_mode: null,
			grid_table_name: null,
			grid_select_id_array: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'QualificationGroupEditView.html';
		this.permission_id = 'qualification';
		this.viewId = 'QualificationGroup';
		this.script_name = 'QualificationGroupView';
		this.table_name_key = 'qualification_group';
		this.context_menu_name = $.i18n._( 'Qualification Groups' );
		this.grid_table_name = $.i18n._( 'Qualification Groups' );
		this.navigation_label = $.i18n._( 'Qualification Group' );

		this.tree_mode = true;
		this.primary_tab_label = $.i18n._( 'Qualification Group' );
		this.primary_tab_key = 'tab_qualification_group';

		this.api = TTAPI.APIQualificationGroup;
		this.grid_select_id_array = [];

		this.render();
		this.buildContextMenu();
		this.initData();
	}

	getCustomContextMenuModel() {
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