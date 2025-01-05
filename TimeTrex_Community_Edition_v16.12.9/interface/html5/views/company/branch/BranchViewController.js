export class BranchViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#branch_view_container',

			status_array: null,
			country_array: null,
			province_array: null,

			e_province_array: null,

			user_group_selection_type_id_array: null,
			user_title_selection_type_id_array: null,
			user_default_branch_selection_type_id_array: null,
			user_default_department_selection_type_id_array: null,

			user_group_array: null,

			company_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'BranchEditView.html';
		this.permission_id = 'branch';
		this.viewId = 'Branch';
		this.script_name = 'BranchView';
		this.table_name_key = 'branch';
		this.context_menu_name = $.i18n._( 'Branch' );
		this.navigation_label = $.i18n._( 'Branch' );
		this.api = TTAPI.APIBranch;
		this.company_api = TTAPI.APICompany;

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
			{ option_name: 'status', api: this.api },
			{ option_name: 'country', field_name: 'country', api: this.company_api },
		];

		if ( ( Global.getProductEdition() >= 20 ) ) {

			options.push(
				{ option_name: 'user_group_selection_type_id', api: this.api },
				{ option_name: 'user_title_selection_type_id', api: this.api },
				{ option_name: 'user_default_branch_selection_type_id', api: this.api },
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
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVBranch' ),
					permission: null,
					sort_order: 9010
				}
			]
		};

		return context_menu_model;
	}

	setDefaultMenuImportIcon( context_btn, grid_selected_length, pId ) {
		if ( PermissionManager.checkTopLevelPermission( 'ImportCSVBranch' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onSetSearchFilterFinished() {
		var combo;
		var select_value;
		if ( this.search_panel.getSelectTabIndex() === 0 ) {
			combo = this.basic_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setProvince( select_value );
		} else if ( this.search_panel.getSelectTabIndex() === 1 ) {
			combo = this.adv_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setProvince( select_value );
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
		IndexViewController.openWizard( 'ImportCSVWizard', 'Branch', function() {
			$this.search();
		} );
	}

	onBuildAdvUIFinished() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	onBuildBasicUIFinished() {
		this.basic_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.basic_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.basic_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		switch ( key ) {
			case 'country':
				var widget = this.edit_view_ui_dic['province'];
				widget.setValue( null );
				break;
		}

		this.current_edit_record[key] = target.getValue();

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( ( Global.getProductEdition() >= 20 ) ) {
			if ( key === 'user_group_selection_type_id' ) {
				this.onEmployeeGroupSelectionTypeChange();
			}
			if ( key === 'user_title_selection_type_id' ) {
				this.onEmployeeTitleSelectionTypeChange();
			}
			if ( key === 'user_default_branch_selection_type_id' ) {
				this.onEmployeeDefaultBranchSelectionTypeChange();
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

	onEmployeeDefaultBranchSelectionTypeChange() {
		if ( this.current_edit_record['user_default_branch_selection_type_id'] == 10 ) {
			this.edit_view_ui_dic['user_default_branch_ids'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_default_branch_ids'].setEnabled( true );
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
			'tab_branch': { 'label': $.i18n._( 'Branch' ) },
			'tab_employee_criteria': {
				'label': $.i18n._( 'Employee Criteria' ),
				'init_callback': 'initSubEmployeeCriteriaView',
				'html_template': this.getBranchEmployeeCriteriaTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;

		this.navigation.AComboBox( {
			api_class: TTAPI.APIBranch,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_branch',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_branch = this.edit_view_tab.find( '#tab_branch' );

		var tab_branch_column1 = tab_branch.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_branch_column1 );

		//Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_branch_column1, '' );

		// Name

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_branch_column1 );

		form_item_input.parent().width( '45%' );

		// Code

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'manual_id', width: 65 } );
		this.addEditFieldToColumn( $.i18n._( 'Code' ), form_item_input, tab_branch_column1 );

		// Address1

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'address1', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Address (Line 1)' ), form_item_input, tab_branch_column1 );

		form_item_input.parent().width( '45%' );

		// Address2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'address2', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Address (Line 2)' ), form_item_input, tab_branch_column1 );

		form_item_input.parent().width( '45%' );

		// city

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'city', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'City' ), form_item_input, tab_branch_column1 );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( $this.country_array );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_branch_column1 );

		//Province / State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_branch_column1 );

		//City
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'postal_code', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Postal/ZIP Code' ), form_item_input, tab_branch_column1 );

		// Phone

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'work_phone', width: 149 } );
		this.addEditFieldToColumn( $.i18n._( 'Phone' ), form_item_input, tab_branch_column1 );

		// Fax
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'fax_phone', width: 149 } );

		this.addEditFieldToColumn( $.i18n._( 'Fax' ), form_item_input, tab_branch_column1 );

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
			this.addEditFieldToColumn( $.i18n._( 'Allowed GEO Fences' ), form_item_input, tab_branch_column1 );
		}

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 110 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_branch_column1, '', null, null, true );

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

			// User Default Branches
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'user_default_branch_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.user_default_branch_selection_type_id_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: TTAPI.Branch,
				allow_multiple_selection: true,
				layout_name: 'global_user_default_branch',
				show_search_inputs: true,
				set_empty: true,
				field: 'user_default_branch_ids'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

			v_box.append( form_item );

			// Include Default Branch
			var form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );

			form_item_input_2.TCheckbox( { field: 'include_user_default_branch_id' } );

			form_item = this.putInputToInsideFormItem( form_item_input_2, $.i18n._( 'Include This Branch' ) );

			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Default Branch' ), [form_item_input, form_item_input_1, form_item_input_2], tab_employee_criteria_column1, '', v_box, false, true );

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

			this.addEditFieldToColumn( $.i18n._( 'Default Department' ), [form_item_input, form_item_input_1], tab_employee_criteria_column1, '', v_box, false, true );

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

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
			this.basic_search_field_ui_dic['province'].setSourceData( [] );

		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );
					$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );
					$this.basic_search_field_ui_dic['province'].setSourceData( $this.province_array );

				}
			} );
		}
	}

	eSetProvince( val, refresh ) {
		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}
					$this.e_province_array = Global.buildRecordArray( res );
					if ( refresh && $this.e_province_array.length > 0 ) {
						$this.current_edit_record.province = $this.e_province_array[0].value;
						province_widget.setValue( $this.current_edit_record.province );
					}
					province_widget.setSourceData( $this.e_province_array );

				}
			} );
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
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Tags' ),
				field: 'tag',
				basic_search: true,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TAG_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Phone' ),
				field: 'work_phone',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				object_type_id: 110,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Fax' ),
				field: 'fax_phone',
				basic_search: false,
				adv_search: true,
				in_column: 1,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Address (Line1)' ),
				field: 'address1',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Address (Line2)' ),
				field: 'address2',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Postal/ZIP Code' ),
				field: 'postal_code',
				basic_search: false,
				adv_search: true,
				in_column: 2,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Country' ),
				in_column: 2,
				field: 'country',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 2,
				field: 'province',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'City' ),
				field: 'city',
				basic_search: true,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Code' ),
				field: 'manual_id',
				basic_search: true,
				adv_search: true,
				in_column: 3,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 3,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 3,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();

		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.onEmployeeGroupSelectionTypeChange();
			this.onEmployeeTitleSelectionTypeChange();
			this.onEmployeeDefaultBranchSelectionTypeChange();
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

	getBranchEmployeeCriteriaTabHtml() {
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