export class UserEducationViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_education_view_container',



			document_object_type_id: null,

			qualification_group_array: null,
			source_type_array: null,

			qualification_group_api: null,
			qualification_api: null,

			sub_view_grid_autosize: true
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'UserEducationEditView.html';
		this.permission_id = 'user_education';
		this.viewId = 'UserEducation';
		this.script_name = 'UserEducationVoew';
		this.table_name_key = 'user_education';
		this.context_menu_name = $.i18n._( 'Education' );
		this.navigation_label = $.i18n._( 'Education' );
		this.api = TTAPI.APIUserEducation;
		this.qualification_api = TTAPI.APIQualification;
		this.qualification_group_api = TTAPI.APIQualificationGroup;

		this.document_object_type_id = 126;
		this.render();

		if ( !this.sub_view_mode ) {
			this.buildContextMenu();
			this.initData();
		}
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'source_type' );

		this.qualification_group_api.getQualificationGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.qualification_group_array = res;

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
					$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );

		var args = {};
		var filter_data = {};
		filter_data.type_id = [20];
		filter_data.visibility_type_id = [10, 100]; //10=Internal Only, 100=Both
		args.filter_data = filter_data;
		this.qualification_api.getQualification( args, {
			onResult: function( res ) {
				res = res.getResult();
				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['qualification_id'] ) {
					$this.basic_search_field_ui_dic['qualification_id'].setSourceData( res );
					$this.adv_search_field_ui_dic['qualification_id'].setSourceData( res );
				}

			}
		} );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['copy'],
			include: [
				{
					label: $.i18n._( 'Import' ),
					id: 'import_icon',
					menu_align: 'right',
					action_group: 'import_export',
					group: 'other',
					vue_icon: 'tticon tticon-file_download_black_24dp',
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVEmployeeEducation' ),
					permission: null,
					sort_order: 9010
				}
			]
		};

		return context_menu_model;
	}

	setDefaultMenuImportIcon( context_btn, grid_selected_length, pId ) {
		if ( PermissionManager.checkTopLevelPermission( 'ImportCSVEmployeeEducation' ) === true ) {
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
		IndexViewController.openWizard( 'ImportCSVWizard', 'UserEducation', function() {
			$this.search();
		} );
	}

	showNoResultCover( show_new_btn ) {
		super.showNoResultCover( ( this.sub_view_mode ) ? true : false );
	}

	onGridSelectRow() {
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
			this.cancelOtherSubViewSelectedStatus();
		} else {
			this.buildContextMenu();
		}
		this.setDefaultMenu();
	}

	onGridSelectAll() {
		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
			this.cancelOtherSubViewSelectedStatus();
		}
		this.setDefaultMenu();
	}

	cancelOtherSubViewSelectedStatus() {
		switch ( true ) {
			case typeof ( this.parent_view_controller.sub_user_skill_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_skill_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_license_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_license_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_membership_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_membership_view_controller.unSelectAll();
			case typeof ( this.parent_view_controller.sub_user_language_view_controller ) !== 'undefined':
				this.parent_view_controller.sub_user_language_view_controller.unSelectAll();
				break;
		}
	}

	onAddClick() {

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		}

		super.onAddClick();
	}

	onMassEditClick() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		$this.openEditView();
		var filter = {};
		var grid_selected_id_array = this.getGridSelectIdArray();
		var grid_selected_length = grid_selected_id_array.length;
		this.mass_edit_record_ids = [];

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				$this.unique_columns = {};

				$this.linked_columns = {};

				if ( !result_data ) {
					result_data = [];
				}

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = result_data;
				$this.initEditView();

			}
		} );
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_education': { 'label': $.i18n._( 'Education' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIUserEducation,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_user_education',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_education = this.edit_view_tab.find( '#tab_education' );

		var tab_education_column1 = tab_education.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_education_column1 );

		// Employee
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: 'global_user',
			field: 'user_id',
			set_empty: true,
			show_search_inputs: true
		} );
		var default_args = {};
		default_args.permission_section = 'user_education';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_education_column1, '' );

		// Course
		var args = {};
		var filter_data = {};
		filter_data.type_id = [20];
		filter_data.visibility_type_id = [10, 100]; //10=Internal Only, 100=Both
		args.filter_data = filter_data;

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIQualification,
			allow_multiple_selection: false,
			layout_name: 'global_qualification',
			show_search_inputs: true,
			set_empty: true,
			field: 'qualification_id'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Course' ), form_item_input, tab_education_column1 );

		//Institute
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'institute', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Institute' ), form_item_input, tab_education_column1 );

		//Major/Specialization
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'major', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Major/Specialization' ), form_item_input, tab_education_column1 );

		//Minor
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'minor', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Minor' ), form_item_input, tab_education_column1 );

		//
		// Grade/Score
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'grade_score', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Grade/Score' ), form_item_input, tab_education_column1 );

		// Graduation Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'graduate_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Graduation Date' ), form_item_input, tab_education_column1, '', null );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'start_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_education_column1, '', null );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'end_date' } );

		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_education_column1, '', null );

		//Tags
		form_item_input = Global.loadWidgetByName( FormItemType.TAG_INPUT );

		form_item_input.TTagInput( { field: 'tag', object_type_id: 252 } );
		this.addEditFieldToColumn( $.i18n._( 'Tags' ), form_item_input, tab_education_column1, '', null, null, true );
	}

	buildSearchFields() {

		super.buildSearchFields();

		var default_args = {};
		default_args.permission_section = 'user_education';

		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				default_args: default_args,
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Course' ),
				in_column: 1,
				field: 'qualification_id',
				layout_name: 'global_qualification',
				api_class: TTAPI.APIQualification,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Institute' ),
				in_column: 1,
				field: 'institute',
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
				object_type_id: 252,
				form_item_type: FormItemType.TAG_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 2,
				multiple: true,
				field: 'group_id',
				layout_name: 'global_tree_column',
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Source' ),
				in_column: 2,
				multiple: true,
				field: 'source_type_id',
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Major/Specialization' ),
				in_column: 2,
				field: 'major',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Minor' ),
				in_column: 2,
				field: 'minor',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Graduation Date' ),
				in_column: 3,
				field: 'graduate_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'Start Date' ),
				in_column: 3,
				field: 'start_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'End Date' ),
				in_column: 3,
				field: 'end_date',
				tree_mode: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} )
		];
	}

	searchDone() {
		super.searchDone();
		TTPromise.resolve( 'Employee_Qualifications_Tab', 'UserEducationViewController' );
	}

	uniformVariable( records ) {
		if ( Global.isArray( records.user_id ) && records.user_id.length > 0 ) {
			let new_records = [];
			for ( let key in records.user_id ) {
				new_records.push( Object.assign( {}, records, { user_id: records.user_id[key] } ) );
			}
			return new_records;
		}
		return records;
	}
}

UserEducationViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {
	Global.loadViewSource( 'UserEducation', 'SubUserEducationView.html', function( result ) {
		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}
		if ( Global.isSet( container ) ) {
			container.html( template( args ) );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_user_education_view_controller );
			}
		}
	} );
};