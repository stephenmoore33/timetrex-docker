export class RegularTimePolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#regular_time_policy_view_container',

			type_array: null,

			branch_selection_type_array: null,
			department_selection_type_array: null,

			job_group_selection_type_array: null,
			job_selection_type_array: null,

			job_group_array: null,
			job_item_group_array: null,
			punch_tag_group_array: null,

			job_item_group_selection_type_array: null,
			job_item_selection_type_array: null,

			punch_tag_group_selection_type_array: null,
			punch_tag_selection_type_array: null,

			job_group_api: null,
			job_item_group_api: null,
			punch_tag_group_api: null,
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RegularTimePolicyEditView.html';
		this.permission_id = 'regular_time_policy';
		this.viewId = 'RegularTimePolicy';
		this.script_name = 'RegularTimePolicyView';
		this.table_name_key = 'regular_time_policy';
		this.context_menu_name = $.i18n._( 'Regular Time Policy' );
		this.navigation_label = $.i18n._( 'Regular Time Policy' );
		this.api = TTAPI.APIRegularTimePolicy;

		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_group_api = TTAPI.APIJobGroup;
			this.job_item_group_api = TTAPI.APIJobItemGroup;
			this.punch_tag_group_api = TTAPI.APIPunchTagGroup;
		}

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'branch_selection_type', api: this.api },
			{ option_name: 'department_selection_type', api: this.api },
			{ option_name: 'job_group_selection_type', api: this.api },
			{ option_name: 'job_selection_type', api: this.api },
			{ option_name: 'job_item_group_selection_type', api: this.api },
			{ option_name: 'job_item_selection_type', api: this.api },
			{ option_name: 'punch_tag_group_selection_type', api: this.api },
			{ option_name: 'punch_tag_selection_type', api: this.api },
		];

		this.initDropDownOptions( options );

		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_group_api.getJobGroup( '', false, false, {
				onResult: function( res ) {
					res = res.getResult();
					res = Global.buildTreeRecord( res );
					$this.job_group_array = res;
				}
			} );

			this.job_item_group_api.getJobItemGroup( '', false, false, {
				onResult: function( res ) {
					res = res.getResult();
					res = Global.buildTreeRecord( res );
					$this.job_item_group_array = res;
				}
			} );

			this.punch_tag_group_api.getPunchTagGroup( '', false, false, {
				onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );
					$this.punch_tag_group_array = res;

				}
			} );
		}
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_regular_time_policy': { 'label': $.i18n._( 'Regular Time Policy' ) },
			'tab_differential_criteria': {
				'label': $.i18n._( 'Differential Criteria' ),
				'init_callback': 'initSubDifferentialCriteriaView',
				'html_template': this.getRegularTimeDifferentialTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIRegularTimePolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_regular_time',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_regular_time_policy = this.edit_view_tab.find( '#tab_regular_time_policy' );

		var tab_regular_time_policy_column1 = tab_regular_time_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_regular_time_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_regular_time_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_regular_time_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIContributingShiftPolicy,
			allow_multiple_selection: false,
			layout_name: 'global_contributing_shift_policy',
			show_search_inputs: true,
			set_empty: true,
			field: 'contributing_shift_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Contributing Shift Policy' ), form_item_input, tab_regular_time_policy_column1 );

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
		this.addEditFieldToColumn( $.i18n._( 'Pay Code' ), form_item_input, tab_regular_time_policy_column1 );

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
		this.addEditFieldToColumn( $.i18n._( 'Pay Formula Policy' ), form_item_input, tab_regular_time_policy_column1 );

		//Calculation Order
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'calculation_order', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Calculation Order' ), form_item_input, tab_regular_time_policy_column1, '' );

		//
		// Tab2 start
		//
		var tab_differential_criteria = this.edit_view_tab.find( '#tab_differential_criteria' );

		var tab_differential_criteria_column1 = tab_differential_criteria.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_differential_criteria_column1 );

		// Branches
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'branch_selection_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.branch_selection_type_array );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIBranch,
			allow_multiple_selection: true,
			layout_name: 'global_branch',
			show_search_inputs: true,
			set_empty: true,
			field: 'branch'
		} );

		var form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

		v_box.append( form_item );

		// Exclude Default
		var form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );

		form_item_input_2.TCheckbox( { field: 'exclude_default_branch' } );

		form_item = this.putInputToInsideFormItem( form_item_input_2, $.i18n._( 'Exclude Default' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Branches' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );

		// Departments
		v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'department_selection_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.department_selection_type_array );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIDepartment,
			allow_multiple_selection: true,
			layout_name: 'global_department',
			show_search_inputs: true,
			set_empty: true,
			field: 'department'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

		v_box.append( form_item );

		// Exclude Default
		form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );

		form_item_input_2.TCheckbox( { field: 'exclude_default_department' } );

		form_item = this.putInputToInsideFormItem( form_item_input_2, $.i18n._( 'Exclude Default' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Departments' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );

		if ( ( Global.getProductEdition() >= 20 ) ) {

			// Job Groups
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'job_group_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.job_group_selection_type_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				tree_mode: true,
				allow_multiple_selection: true,
				layout_name: 'global_tree_column',
				set_empty: true,
				field: 'job_group'
			} );

			form_item_input_1.setSourceData( $this.job_group_array );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Job Groups' ), [form_item_input, form_item_input_1], tab_differential_criteria_column1, '', v_box, false, true );

			// Jobs
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'job_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.job_selection_type_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: TTAPI.APIJob,
				allow_multiple_selection: true,
				layout_name: 'global_job',
				show_search_inputs: true,
				set_empty: true,
				field: 'job'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

			v_box.append( form_item );

			// Exclude Default
			form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_input_2.TCheckbox( { field: 'exclude_default_job' } );
			form_item = this.putInputToInsideFormItem( form_item_input_2, $.i18n._( 'Exclude Default' ) );
			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Jobs' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );

			// Task Groups
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'job_item_group_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.job_item_group_selection_type_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				tree_mode: true,
				allow_multiple_selection: true,
				layout_name: 'global_tree_column',
				set_empty: true,
				field: 'job_item_group'
			} );

			form_item_input_1.setSourceData( $this.job_item_group_array );
			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );
			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Task Groups' ), [form_item_input, form_item_input_1], tab_differential_criteria_column1, '', v_box, false, true );

			// Tasks
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'job_item_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.job_item_selection_type_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: TTAPI.APIJobItem,
				allow_multiple_selection: true,
				layout_name: 'global_job_item',
				show_search_inputs: true,
				set_empty: true,
				field: 'job_item'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );
			v_box.append( form_item );

			// Exclude Default
			form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_input_2.TCheckbox( { field: 'exclude_default_job_item' } );
			form_item = this.putInputToInsideFormItem( form_item_input_2, $.i18n._( 'Exclude Default' ) );
			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Tasks' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );

			// Punch Tag Groups
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'punch_tag_group_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.punch_tag_group_selection_type_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				tree_mode: true,
				allow_multiple_selection: true,
				layout_name: 'global_tree_column',
				set_empty: true,
				field: 'punch_tag_group'
			} );

			form_item_input_1.setSourceData( $this.punch_tag_group_array );
			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );
			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Punch Tag Groups' ), [form_item_input, form_item_input_1], tab_differential_criteria_column1, '', v_box, false, true );

			// Punch Tags
			v_box = $( '<div class=\'v-box\'></div>' );

			//Selection Type
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'punch_tag_selection_type_id', set_empty: false } );
			form_item_input.setSourceData( $this.punch_tag_selection_type_array );

			form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

			v_box.append( form_item );
			v_box.append( '<div class=\'clear-both-div\'></div>' );

			//Selection
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: TTAPI.APIPunchTag,
				allow_multiple_selection: true,
				layout_name: 'global_job_item',
				show_search_inputs: true,
				set_empty: true,
				field: 'punch_tag'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );
			v_box.append( form_item );

			// Exclude Default
			form_item_input_2 = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_input_2.TCheckbox( { field: 'exclude_default_punch_tag' } );
			form_item = this.putInputToInsideFormItem( form_item_input_2, $.i18n._( 'Exclude Default' ) );
			v_box.append( form_item );

			this.addEditFieldToColumn( $.i18n._( 'Punch Tags' ), [form_item_input, form_item_input_1, form_item_input_2], tab_differential_criteria_column1, '', v_box, false, true );

			// Contributing Pay Code Policy
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIContributingPayCodePolicy,
				allow_multiple_selection: false,
				layout_name: 'global_contributing_pay_code_policy',
				show_search_inputs: true,
				set_any: true,
				field: 'contributing_pay_code_policy_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Pay Code Policy' ), form_item_input, tab_differential_criteria_column1 );
		}
	}

	onFormItemChange( target ) {
		this.is_changed = true;
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;
		if ( key === 'branch_selection_type_id' ) {
			this.onBranchSelectionTypeChange();
		}
		if ( key === 'department_selection_type_id' ) {
			this.onDepartmentSelectionTypeChange();
		}
		if ( key === 'job_group_selection_type_id' ) {
			this.onJobGroupSelectionTypeChange();
		}
		if ( key === 'job_selection_type_id' ) {
			this.onJobSelectionTypeChange();
		}
		if ( key === 'job_item_group_selection_type_id' ) {
			this.onJobItemGroupSelectionTypeChange();
		}
		if ( key === 'job_item_selection_type_id' ) {
			this.onJobItemSelectionTypeChange();
		}
		if ( key === 'punch_tag_group_selection_type_id' ) {
			this.onPunchTagGroupSelectionTypeChange();
		}
		if ( key === 'punch_tag_selection_type_id' ) {
			this.onPunchTagSelectionTypeChange();
		}
		this.validate();
	}

	setCurrentEditRecordData() {

		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'branch_selection_type_id': 10,
			'department_selection_type_id': 10,
			'job_group_selection_type_id': 10,
			'job_selection_type_id': 10,
			'job_item_group_selection_type_id': 10,
			'job_item_selection_type_id': 10,
			'punch_tag_group_selection_type_id': 10,
			'punch_tag_selection_type_id': 10
		} );

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.onBranchSelectionTypeChange();
		this.onDepartmentSelectionTypeChange();
		this.onJobGroupSelectionTypeChange();
		this.onJobSelectionTypeChange();
		this.onJobItemGroupSelectionTypeChange();
		this.onJobItemSelectionTypeChange();
		this.collectUIDataToCurrentEditRecord();
		this.onPunchTagGroupSelectionTypeChange();
		this.onPunchTagSelectionTypeChange();

		this.setEditViewDataDone();
	}

	onBranchSelectionTypeChange() {
		if ( this.current_edit_record['branch_selection_type_id'] == 10 ) {
			this.edit_view_ui_dic['branch'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['branch'].setEnabled( true );
		}
	}

	onDepartmentSelectionTypeChange() {
		if ( this.current_edit_record['department_selection_type_id'] == 10 ) {
			this.edit_view_ui_dic['department'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['department'].setEnabled( true );
		}
	}

	onJobGroupSelectionTypeChange() {

		if ( ( Global.getProductEdition() >= 20 ) ) {

			if ( this.current_edit_record['job_group_selection_type_id'] == 10 || this.is_viewing ) {
				this.edit_view_ui_dic['job_group'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['job_group'].setEnabled( true );
			}
		}
	}

	onJobSelectionTypeChange() {
		if ( ( Global.getProductEdition() >= 20 ) ) {
			if ( this.current_edit_record['job_selection_type_id'] == 10 || this.is_viewing ) {
				this.edit_view_ui_dic['job'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['job'].setEnabled( true );
			}
		}
	}

	onJobItemGroupSelectionTypeChange() {
		if ( ( Global.getProductEdition() >= 20 ) ) {
			if ( this.current_edit_record['job_item_group_selection_type_id'] == 10 || this.is_viewing ) {
				this.edit_view_ui_dic['job_item_group'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['job_item_group'].setEnabled( true );
			}
		}
	}

	onJobItemSelectionTypeChange() {
		if ( ( Global.getProductEdition() >= 20 ) ) {
			if ( this.current_edit_record['job_item_selection_type_id'] == 10 || this.is_viewing ) {
				this.edit_view_ui_dic['job_item'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['job_item'].setEnabled( true );
			}
		}
	}

	onPunchTagGroupSelectionTypeChange() {
		if ( ( Global.getProductEdition() >= 20 ) ) {
			if ( this.current_edit_record['punch_tag_group_selection_type_id'] == 10 || this.is_viewing ) {
				this.edit_view_ui_dic['punch_tag_group'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['punch_tag_group'].setEnabled( true );
			}
		}
	}

	onPunchTagSelectionTypeChange() {
		if ( ( Global.getProductEdition() >= 20 ) ) {
			if ( this.current_edit_record['punch_tag_selection_type_id'] == 10 || this.is_viewing ) {
				this.edit_view_ui_dic['punch_tag'].setEnabled( false );
			} else {
				this.edit_view_ui_dic['punch_tag'].setEnabled( true );
			}
		}
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

	initSubDifferentialCriteriaView() {
		if ( Global.getProductEdition() >= 15 ) {
			this.edit_view_tab.find( '#tab_differential_criteria' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			this.buildContextMenu( true );
			this.setEditMenu();
		} else {
			this.edit_view_tab.find( '#tab_differential_criteria' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}
	}

	getRegularTimeDifferentialTabHtml() {
		return `<div id="tab_differential_criteria" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_differential_criteria_content_div">
						<div class="first-column full-width-column"></div>
						<div class="save-and-continue-div permission-defined-div">
							<span class="message permission-message"></span>
						</div>
					</div>
				</div>`;
	}
}
