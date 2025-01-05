export class DepartmentViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#department_view_container',

			user_group_selection_type_id_array: null,
			user_title_selection_type_id_array: null,
			user_punch_branch_selection_type_id_array: null,
			user_default_department_selection_type_id_array: null,

			user_group_array: null,

			status_array: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'DepartmentEditView.html';
		this.permission_id = 'department';
		this.viewId = 'Department';
		this.script_name = 'DepartmentView';
		this.table_name_key = 'department';
		this.context_menu_name = $.i18n._( 'Departments' );
		this.navigation_label = $.i18n._( 'Department' );
		this.api = TTAPI.APIDepartment;
		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.user_group_api = TTAPI.APIUserGroup;
		}

		this.render();

		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		var $this = this;

		let options = [
			{ option_name: 'status', api: this.api }
		];


		if ( ( Global.getProductEdition() >= 20 ) ) {

			options.push(
				{ option_name: 'user_group_selection_type_id', api: this.api },
				{ option_name: 'user_title_selection_type_id', api: this.api },
				{ option_name: 'user_punch_branch_selection_type_id', api: this.api },
				{ option_name: 'user_default_department_selection_type_id', api: this.api },
			);

			this.user_group_api.getUserGroup( '', false, false, {
				onResult: function( res ) {
					res = res.getResult();
					res = Global.buildTreeRecord( res );
					$this.user_group_array = res;
				}
			} );
		}

		this.initDropDownOptions( options );
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		this.current_edit_record[key] = target.getValue();

		if ( ( Global.getProductEdition() >= 20 ) ) {
			if ( key === 'user_group_selection_type_id' ) {
				this.onEmployeeGroupSelectionTypeChange();
			}
			if ( key === 'user_title_selection_type_id' ) {
				this.onEmployeeTitleSelectionTypeChange();
			}
			if ( key === 'user_punch_branch_selection_type_id' ) {
				this.onEmployeePunchBranchSelectionTypeChange();
			}
			if ( key === 'user_default_department_selection_type_id' ) {
				this.onEmployeeDefaultDepartmentSelectionTypeChange();
			}
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onEmployeeGroupSelectionTypeChange() {
		if ( this.current_edit_record['user_group_selection_type_id'] == 10 ) {
			this.edit_view_ui_dic['user_group_ids'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_group_ids'].setEnabled( true );
		}
	}

	onEmployeeTitleSelectionTypeChange() {
		if ( this.current_edit_record['user_title_selection_type_id'] == 10 ) {
			this.edit_view_ui_dic['user_title_ids'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_title_ids'].setEnabled( true );
		}
	}

	onEmployeePunchBranchSelectionTypeChange() {
		if ( this.current_edit_record['user_punch_branch_selection_type_id'] == 10 ) {
			this.edit_view_ui_dic['user_punch_branch_ids'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_punch_branch_ids'].setEnabled( true );
		}
	}

	onEmployeeDefaultDepartmentSelectionTypeChange() {
		if ( this.current_edit_record['user_default_department_selection_type_id'] == 10 ) {
			this.edit_view_ui_dic['user_default_department_ids'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_default_department_ids'].setEnabled( true );
		}
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_department': { 'label': $.i18n._( 'Department' ) },
			'tab_employee_criteria': {
				'label': $.i18n._( 'Employee Criteria' ),
				'init_callback': 'initSubEmployeeCriteriaView',
				'html_template': this.getDepartmentEmployeeCriteriaTabHtml(),
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIDepartment,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_department',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_department = this.edit_view_tab.find( '#tab_department' );

		var tab_department_column1 = tab_department.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_department_column1 );

		//Status

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_department_column1, '' );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_department_column1 );
		form_item_input.parent().width( '45%' );

		// Code

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'manual_id', width: 65 } );
		this.addEditFieldToColumn( $.i18n._( 'Code' ), form_item_input, tab_department_column1 );

		//Allowed GEO Fences
		if ( Global.getProductEdition() >= 20 ) {
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIGEOFence,
				allow_multiple_selection: true,
				layout_name: 'global_geo_fence',
				show_search_inputs: true,
				set_empty: true,
				field: 'geo_fence_ids'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Allowed GEO Fences' ), form_item_input, tab_department_column1 );
		}

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 120 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_department_column1, '', null, null, true );

		if ( ( Global.getProductEdition() >= 20 ) ) {
			//Tab 1 start employee criteria
			var tab_employee_criteria = this.edit_view_tab.find( '#tab_employee_criteria' );
			var tab_employee_criteria_column1 = tab_employee_criteria.find( '.first-column' );
			this.edit_view_tabs[1] = [];
			this.edit_view_tabs[1].push( tab_employee_criteria_column1 );

			//User Groups
			var v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'user_group_selection_type_id' } );
			form_item_input.setSourceData( $this.user_group_selection_type_id_array );

			var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				tree_mode: true,
				allow_multiple_selection: true,
				layout_name: 'global_tree_column',
				set_empty: true,
				field: 'user_group_ids'
			} );
			form_item_input_1.setSourceData( $this.user_group_array );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Employee Groups' ), [form_item_input, form_item_input_1], tab_employee_criteria_column1, '', v_box, false, true );

			// User Titles
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'user_title_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.user_title_selection_type_id_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: TTAPI.UserTitle,
				allow_multiple_selection: true,
				layout_name: 'global_user_title',
				show_search_inputs: true,
				set_empty: true,
				field: 'user_title_ids'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Employee Titles' ), [form_item_input, form_item_input_1], tab_employee_criteria_column1, '', v_box, false, true );

			// User Punch Branches
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'user_punch_branch_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.user_punch_branch_selection_type_id_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: TTAPI.Branch,
				allow_multiple_selection: true,
				layout_name: 'global_user_punch_branch',
				show_search_inputs: true,
				set_empty: true,
				field: 'user_punch_branch_ids'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Punch Branch' ), [form_item_input, form_item_input_1], tab_employee_criteria_column1, '', v_box, false, true );

			// User Default Department
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'user_default_department_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.user_default_department_selection_type_id_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: TTAPI.Department,
				allow_multiple_selection: true,
				layout_name: 'global_user_default_department',
				show_search_inputs: true,
				set_empty: true,
				field: 'user_default_department_ids'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

			v_box.append( form_item );

			// Include Default Department
			var form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );

			form_item_input_2.TCheckbox( { field: 'include_user_default_department_id' } );

			form_item = this.putInputToInsideFormItem( form_item_input_2, $.i18n._( 'Include This Department' ) );

			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Default Department' ), [form_item_input, form_item_input_1, form_item_input_2], tab_employee_criteria_column1, '', v_box, false, true );

			// Include Employees
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIUser,
				allow_multiple_selection: true,
				layout_name: 'global_user',
				show_search_inputs: true,
				set_empty: true,
				field: 'include_user_ids'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Include Employees' ), form_item_input, tab_employee_criteria_column1 );

			// Exclude Employees
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIUser,
				allow_multiple_selection: true,
				layout_name: 'global_user',
				show_search_inputs: true,
				set_empty: true,
				field: 'exclude_user_ids'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Exclude Employees' ), form_item_input, tab_employee_criteria_column1, '' );
		}
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				in_column: 1,
				form_item_type: FormItemType.TAG_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Code' ),
				field: 'manual_id',
				basic_search: true,
				in_column: 2,
				object_type_id: 120,
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

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Import' ),
					id: 'import_icon',
					menu_align: 'right',
					action_group: 'import_export',
					group: 'other',
					vue_icon: 'tticon tticon-file_download_black_24dp',
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVDepartment' ),
					sort_order: 9010
				}
			]
		};

		return context_menu_model;
	}

	setDefaultMenuImportIcon( context_btn, grid_selected_length, pId ) {
		if ( PermissionManager.checkTopLevelPermission( 'ImportCSVDepartment' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'import_icon':
				this.onImportClick();
				break;
		}
	}

	onImportClick() {
		var $this = this;

		IndexViewController.openWizard( 'ImportCSVWizard', 'Department', function() {
			$this.search();
		} );
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();

		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.onEmployeeGroupSelectionTypeChange();
			this.onEmployeeTitleSelectionTypeChange();
			this.onEmployeePunchBranchSelectionTypeChange();
			this.onEmployeeDefaultDepartmentSelectionTypeChange();
		}
	}

	initSubEmployeeCriteriaView() {
		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.edit_view_tab.find( '#tab_employee_criteria' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
		} else {
			this.edit_view_tab.find( '#tab_employee_criteria' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}
	}

	getDepartmentEmployeeCriteriaTabHtml() {
		return `
		<div id="tab_employee_criteria" class="edit-view-tab-outside">
			<div class="edit-view-tab" id="tab_employee_criteria_content_div">
				<div class="first-column full-width-column"></div>
				<div class="save-and-continue-div permission-defined-div">
					<span class="message permission-message"></span>
				</div>
			</div>
		</div>`;
	}
}
