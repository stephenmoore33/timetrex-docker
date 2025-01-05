export class ContributingShiftPolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#contributing_shift_policy_view_container',

			type_array: null,
			include_holiday_type_array: null,
			include_shift_type_array: null,

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
			date_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'ContributingShiftPolicyEditView.html';
		this.permission_id = 'contributing_shift_policy';
		this.viewId = 'ContributingShiftPolicy';
		this.script_name = 'ContributingShiftPolicyView';
		this.table_name_key = 'contributing_shift_policy';
		this.context_menu_name = $.i18n._( 'Contributing Shift Policy' );
		this.navigation_label = $.i18n._( 'Contributing Shift Policy' );
		this.api = TTAPI.APIContributingShiftPolicy;
		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_group_api = TTAPI.APIJobGroup;
			this.job_item_group_api = TTAPI.APIJobItemGroup;
			this.punch_tag_group_api = TTAPI.APIPunchTagGroup;
		}

		this.date_api = TTAPI.APITTDate;
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'include_holiday_type', api: this.api },
			{ option_name: 'include_shift_type', api: this.api },
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
			'tab_contributing_shift_policy': { 'label': $.i18n._( 'Contributing Shift Policy' ) },
			'tab_date_criteria': {
				'label': $.i18n._( 'Date/Time Criteria' ),
				'init_callback': 'initSubDateCriteriaView',
				'html_template': this.getContributingShiftDateCriteriaTabHtml()
			},
			'tab_differential_criteria': {
				'label': $.i18n._( 'Differential Criteria' ),
				'init_callback': 'initSubDifferentialCriteriaView',
				'html_template': this.getContributingShiftDifferentialCriteriaTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIContributingShiftPolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_over_time',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_contributing_shift_policy = this.edit_view_tab.find( '#tab_contributing_shift_policy' );

		var tab_contributing_shift_policy_column1 = tab_contributing_shift_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_contributing_shift_policy_column1 );

		//Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_contributing_shift_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_contributing_shift_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Contributing Pay Code Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIContributingPayCodePolicy,
			allow_multiple_selection: false,
			layout_name: 'global_contributing_pay_code_policy',
			show_search_inputs: true,
			set_empty: true,
			field: 'contributing_pay_code_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Contributing Pay Code Policy' ), form_item_input, tab_contributing_shift_policy_column1 );

		//Tab 1 start
		var tab_date_criteria = this.edit_view_tab.find( '#tab_date_criteria' );

		var tab_date_criteria_column1 = tab_date_criteria.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_date_criteria_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'filter_start_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Leave blank for no start date)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_date_criteria_column1, '', widgetContainer );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'filter_end_date' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Leave blank for no end date)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_date_criteria_column1, '', widgetContainer );

		// Start Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'filter_start_time' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Leave blank for no start time)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Start Time' ), form_item_input, tab_date_criteria_column1, '', widgetContainer );

		// End Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'filter_end_time' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Leave blank for no end time)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Time' ), form_item_input, tab_date_criteria_column1, '', widgetContainer );

		// Effective Days
		var form_item_sun_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_sun_checkbox.TCheckbox( { field: 'sun' } );

		var form_item_mon_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_mon_checkbox.TCheckbox( { field: 'mon' } );

		var form_item_tue_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_tue_checkbox.TCheckbox( { field: 'tue' } );

		var form_item_wed_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_wed_checkbox.TCheckbox( { field: 'wed' } );

		var form_item_thu_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_thu_checkbox.TCheckbox( { field: 'thu' } );

		var form_item_fri_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_fri_checkbox.TCheckbox( { field: 'fri' } );

		var form_item_sat_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_sat_checkbox.TCheckbox( { field: 'sat' } );

		widgetContainer = $( '<div class=\'\'></div>' );

		var sun = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Sun' ) + ' <br> ' + ' </span>' );
		var mon = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Mon' ) + ' <br> ' + ' </span>' );
		var tue = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Tue' ) + ' <br> ' + ' </span>' );
		var wed = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Wed' ) + ' <br> ' + ' </span>' );
		var thu = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Thu' ) + ' <br> ' + ' </span>' );
		var fri = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Fri' ) + ' <br> ' + ' </span>' );
		var sat = $( '<span class=\'widget-top-label\'> ' + $.i18n._( 'Sat' ) + ' <br> ' + ' </span>' );

		sun.append( form_item_sun_checkbox );
		mon.append( form_item_mon_checkbox );
		tue.append( form_item_tue_checkbox );
		wed.append( form_item_wed_checkbox );
		thu.append( form_item_thu_checkbox );
		fri.append( form_item_fri_checkbox );
		sat.append( form_item_sat_checkbox );

		widgetContainer.append( sun );
		widgetContainer.append( mon );
		widgetContainer.append( tue );
		widgetContainer.append( wed );
		widgetContainer.append( thu );
		widgetContainer.append( fri );
		widgetContainer.append( sat );

		this.addEditFieldToColumn( $.i18n._( 'Effective Days' ), [form_item_sun_checkbox, form_item_mon_checkbox, form_item_tue_checkbox, form_item_wed_checkbox, form_item_thu_checkbox, form_item_fri_checkbox, form_item_sat_checkbox], tab_date_criteria_column1, '', widgetContainer, false, true );

		// Holidays
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'include_holiday_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.include_holiday_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Holidays' ), form_item_input, tab_date_criteria_column1 );

		// Holiday Policies
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIHolidayPolicy,
			allow_multiple_selection: true,
			layout_name: 'global_holiday',
			show_search_inputs: true,
			set_empty: true,
			field: 'holiday_policy'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Holiday Policies' ), form_item_input, tab_date_criteria_column1, '', null, true );

		// Include Shift Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'include_shift_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.include_shift_type_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Between above start/end times)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Shift Criteria' ), form_item_input, tab_date_criteria_column1, '', widgetContainer );

		//Minimum Time in this Shift
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_time_in_this_shift', mode: 'time_unit', need_parser_sec: true } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Use 0 for no minimum)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Time In This Shift' ), form_item_input, tab_date_criteria_column1, '', widgetContainer, true );

		//Minimum Time into this Shift
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_time_into_this_shift', mode: 'time_unit', need_parser_sec: true } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(From previous shift. Use 0 for no minimum)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Time Into This Shift' ), form_item_input, tab_date_criteria_column1, '', widgetContainer, true );

		//Maximum Time into Next Shift
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_time_into_next_shift', mode: 'time_unit', need_parser_sec: true } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(From this shift. Use 0 for no maximum)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Time Into Next Shift' ), form_item_input, tab_date_criteria_column1, '', widgetContainer, true );

		//
		// Tab2 start
		//
		var tab_differential_criteria = this.edit_view_tab.find( '#tab_differential_criteria' );

		var tab_differential_criteria_column1 = tab_differential_criteria.find( '.first-column' );

		this.edit_view_tabs[2] = [];

		this.edit_view_tabs[2].push( tab_differential_criteria_column1 );

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

		}
	}

	onFormItemChange( target ) {
		this.is_changed = true;
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		if ( key === 'include_holiday_type_id' ) {
			this.onIncludeHolidayTypeChange();
		}

		if ( key === 'include_shift_type_id' ) {
			this.onIncludeShiftTypeChange();
		}

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
			'punch_tag_selection_type_id': 10,
			'include_holiday_type_id': 10
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
		this.onPunchTagGroupSelectionTypeChange();
		this.onPunchTagSelectionTypeChange();

		this.collectUIDataToCurrentEditRecord();
		this.onIncludeHolidayTypeChange();
		this.onIncludeShiftTypeChange();
		this.setEditViewDataDone();
	}

	onIncludeHolidayTypeChange() {
		if ( this.current_edit_record['include_holiday_type_id'] == 10 ) {
			this.detachElement( 'holiday_policy' );
		} else {
			this.attachElement( 'holiday_policy' );
		}

		this.editFieldResize();
	}

	onIncludeShiftTypeChange() {
		if ( this.current_edit_record['include_shift_type_id'] == 150 ) { //Split Shift (Partial w/Limits)
			this.attachElement( 'minimum_time_in_this_shift' );
			this.attachElement( 'minimum_time_into_this_shift' );
			this.attachElement( 'maximum_time_into_next_shift' );
		} else {
			this.detachElement( 'minimum_time_in_this_shift' );
			this.detachElement( 'minimum_time_into_this_shift' );
			this.detachElement( 'maximum_time_into_next_shift' );
		}

		this.editFieldResize();
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

	initSubDateCriteriaView() {
		if ( Global.getProductEdition() >= 15 ) {
			this.edit_view_tab.find( '#tab_date_criteria' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			this.buildContextMenu( true );
			this.setEditMenu();
		} else {
			this.edit_view_tab.find( '#tab_date_criteria' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}
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
				label: $.i18n._( 'Contributing Pay Code' ),
				in_column: 1,
				field: 'contributing_pay_code_policy_id',
				layout_name: 'global_contributing_pay_code_policy',
				api_class: TTAPI.APIContributingPayCodePolicy,
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

	getContributingShiftDateCriteriaTabHtml() {
		return `<div id="tab_date_criteria" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_date_criteria_content_div">
						<div class="first-column full-width-column"></div>
						<div class="save-and-continue-div permission-defined-div">
							<span class="message permission-message"></span>
						</div>
					</div>
				</div>`;
	}

	getContributingShiftDifferentialCriteriaTabHtml() {
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
