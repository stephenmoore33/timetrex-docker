export class HolidayPolicyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#holiday_policy_view_container',

			type_array: null,
			default_schedule_status_array: null,
			shift_on_holiday_type_array: null,
			worked_scheduled_days_array: null,
			date_api: null,
			sub_holiday_view_controller: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'HolidayPolicyEditView.html';
		this.permission_id = 'holiday_policy';
		this.viewId = 'HolidayPolicy';
		this.script_name = 'HolidayPolicyView';
		this.table_name_key = 'holiday_policy';
		this.context_menu_name = $.i18n._( 'Holiday Policy' );
		this.navigation_label = $.i18n._( 'Holiday Policy' );
		this.api = TTAPI.APIHolidayPolicy;
		this.date_api = TTAPI.APITTDate;
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	/* jshint ignore:start */
	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'average_time_frequency_type', api: this.api },
			{ option_name: 'default_schedule_status', api: this.api },
			{ option_name: 'shift_on_holiday_type', api: this.api },
		];

		this.initDropDownOptions( options );

		this.initDropDownOption( 'scheduled_day', 'worked_scheduled_days', null, function( res ) {
			res = res.getResult();
			$this.worked_scheduled_days_array = $.extend( {}, res ); //	 Convert Array to Object
		} );
	}

	/* jshint ignore:end */
	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_holiday_policy': { 'label': $.i18n._( 'Holiday Policy' ) },
			'tab_eligibility': { 'label': $.i18n._( 'Eligibility' ) },
			'tab_holiday_time': { 'label': $.i18n._( 'Holiday Time' ) },
			'tab_holidays': {
				'label': $.i18n._( 'Holidays' ),
				'init_callback': 'initSubHolidayView',
				'display_on_mass_edit': false
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIHolidayPolicy,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_holiday',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		var tab_holiday_policy = this.edit_view_tab.find( '#tab_holiday_policy' );

		//Tab 0 start

		var tab_holiday_policy_column1 = tab_holiday_policy.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_holiday_policy_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_holiday_policy_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_holiday_policy_column1, '', null, null, true );

		form_item_input.parent().width( '45%' );

		// Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_holiday_policy_column1 );

		// Default Schedules Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'default_schedule_status_id' } );
		form_item_input.setSourceData( $this.default_schedule_status_array );
		this.addEditFieldToColumn( $.i18n._( 'Default Schedule Status' ), form_item_input, tab_holiday_policy_column1 );

		// Recurring Holidays
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIRecurringHoliday,
			allow_multiple_selection: true,
			layout_name: 'global_recurring_holiday',
			show_search_inputs: true,
			set_empty: true,
			field: 'recurring_holiday_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Recurring Holidays' ), form_item_input, tab_holiday_policy_column1, '' );

		// Display Days
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'holiday_display_days', width: 50 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Days in Advance)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Display Holidays' ), form_item_input, tab_holiday_policy_column1, '', widgetContainer );

		// tab 1 start
		var tab_eligibility = this.edit_view_tab.find( '#tab_eligibility' );

		var tab_eligibility_column1 = tab_eligibility.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_eligibility_column1 );

		// Minimum Employed Days
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'minimum_employed_days', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Employed Days' ), form_item_input, tab_eligibility_column1, '' );

		// Employee Must Work at Least
		var form_item_minimum_worked_days_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_minimum_worked_days_input.TTextInput( { field: 'minimum_worked_days', width: 30 } );

		var form_item_minimum_worked_period_days_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_minimum_worked_period_days_input.TTextInput( { field: 'minimum_worked_period_days', width: 30 } );

		var form_item_worked_scheduled_days_combobox = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_worked_scheduled_days_combobox.TComboBox( { field: 'worked_scheduled_days' } );
		form_item_worked_scheduled_days_combobox.setSourceData( $this.worked_scheduled_days_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

		var label_1 = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'of the' ) + ' </span>' );
		var label_2 = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'prior to the holiday' ) + ' </span>' );
		var label_3 = $( '<span class=\'widget-right-label\'> ' + ' ' + ' </span>' );

		widgetContainer.append( form_item_minimum_worked_days_input );
		widgetContainer.append( label_1 );
		widgetContainer.append( form_item_minimum_worked_period_days_input );
		widgetContainer.append( label_3 );
		widgetContainer.append( form_item_worked_scheduled_days_combobox );
		widgetContainer.append( label_2 );

		this.addEditFieldToColumn( $.i18n._( 'Employee Must Work at Least' ), [form_item_minimum_worked_days_input, form_item_minimum_worked_period_days_input, form_item_worked_scheduled_days_combobox], tab_eligibility_column1, '', widgetContainer, true );

		// Default Schedules Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'shift_on_holiday_type_id' } );
		form_item_input.setSourceData( $this.shift_on_holiday_type_array );
		this.addEditFieldToColumn( $.i18n._( 'On the Holiday, the Employee' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Employee Must Work at Least
		form_item_minimum_worked_days_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_minimum_worked_days_input.TTextInput( { field: 'minimum_worked_after_days', width: 30 } );

		form_item_minimum_worked_period_days_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_minimum_worked_period_days_input.TTextInput( {
			field: 'minimum_worked_after_period_days',
			width: 30
		} );

		form_item_worked_scheduled_days_combobox = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_worked_scheduled_days_combobox.TComboBox( { field: 'worked_after_scheduled_days' } );
		form_item_worked_scheduled_days_combobox.setSourceData( $this.worked_scheduled_days_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

		label_1 = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'of the' ) + ' </span>' );
		label_2 = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'following the holiday' ) + ' </span>' );
		label_3 = $( '<span class=\'widget-right-label\'> ' + ' ' + ' </span>' );

		widgetContainer.append( form_item_minimum_worked_days_input );
		widgetContainer.append( label_1 );
		widgetContainer.append( form_item_minimum_worked_period_days_input );
		widgetContainer.append( label_3 );
		widgetContainer.append( form_item_worked_scheduled_days_combobox );
		widgetContainer.append( label_2 );

		this.addEditFieldToColumn( $.i18n._( 'Employee Must Work at Least' ), [form_item_minimum_worked_days_input, form_item_minimum_worked_period_days_input, form_item_worked_scheduled_days_combobox], tab_eligibility_column1, '', widgetContainer, true );

		// Eligible Contributing Shift
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIContributingShiftPolicy,
			allow_multiple_selection: false,
			layout_name: 'global_contributing_shift_policy',
			show_search_inputs: true,
			set_empty: true,
			field: 'eligible_contributing_shift_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Contributing Shift Policy' ), form_item_input, tab_eligibility_column1, '', null, true );

		// tab 2 start
		var tab_holiday_time = this.edit_view_tab.find( '#tab_holiday_time' );

		var tab_holiday_time_column1 = tab_holiday_time.find( '.first-column' );

		this.edit_view_tabs[2] = [];

		this.edit_view_tabs[2].push( tab_holiday_time_column1 );

		// Total Time over
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'average_time_days', width: 30 } );

		var form_item_average_time_frequency_combobox = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_average_time_frequency_combobox.TComboBox( { field: 'average_time_frequency_type_id' } );
		form_item_average_time_frequency_combobox.setSourceData( $this.average_time_frequency_type_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( form_item_average_time_frequency_combobox );

		this.addEditFieldToColumn( $.i18n._( 'Total Time Over' ), [form_item_input, form_item_average_time_frequency_combobox], tab_holiday_time_column1, '', widgetContainer, true );

		// Average Time over
		var form_item_average_time_worked_days_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_average_time_worked_days_checkbox.TCheckbox( { field: 'average_time_worked_days' } );

		var form_item_average_days_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_average_days_input.TTextInput( { field: 'average_days', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box \'></div>' );

		label_1 = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'Worked Days Only' ) + ' </span>' );
		label_2 = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'or' ) + ' </span>' );
		label_3 = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'days' ) + ' </span>' );

		widgetContainer.append( label_1 );
		widgetContainer.append( form_item_average_time_worked_days_checkbox );
		widgetContainer.append( label_2 );
		widgetContainer.append( form_item_average_days_input );
		widgetContainer.append( label_3 );

		this.average_days_widgets = [label_2, form_item_average_days_input, label_3];

		this.addEditFieldToColumn( $.i18n._( 'Average Time Over' ), [form_item_average_time_worked_days_checkbox, form_item_average_days_input], tab_holiday_time_column1, '', widgetContainer, true );

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
		this.addEditFieldToColumn( $.i18n._( 'Contributing Shift Policy' ), form_item_input, tab_holiday_time_column1, '', null, true );

		// Holiday Time
		// Minimum Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_time', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Holiday Time' ), form_item_input, tab_holiday_time_column1, '', null, true );

		// Maximum Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_time', mode: 'time_unit', need_parser_sec: true } );

		this.addEditFieldToColumn( $.i18n._( 'Maximum Time' ), form_item_input, tab_holiday_time_column1, '', null, true );

		// Always Apply Over Time/Premium Policies
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'force_over_time_policy' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label \'> (' + $.i18n._( 'Even if they are not eligible for holiday pay' ) + ')</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Always Apply Over Time/Premium Policies' ), form_item_input, tab_holiday_time_column1, '', widgetContainer, true );

		// Rounding Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIRoundIntervalPolicy,
			allow_multiple_selection: false,
			layout_name: 'global_round_interval',
			show_search_inputs: true,
			set_empty: true,
			field: 'round_interval_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Rounding Policy' ), form_item_input, tab_holiday_time_column1, '', null, true );

		// Absence Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIAbsencePolicy,
			allow_multiple_selection: false,
			layout_name: 'global_absences',
			show_search_inputs: true,
			set_empty: true,
			field: 'absence_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Absence Policy' ), form_item_input, tab_holiday_time_column1, '' );
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;
		switch ( key ) {
			case 'type_id':
				this.onTypeChange();
				break;
			case 'average_time_worked_days':
				this.onWorkedDaysChange();
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onTypeChange();
		this.onWorkedDaysChange();
	}

	onWorkedDaysChange() {
		if ( this.current_edit_record.average_time_worked_days === true ) {
			this.average_days_widgets[0].hide();
			this.average_days_widgets[1].hide();
			this.average_days_widgets[2].hide();
		} else {
			this.average_days_widgets[0].show();
			this.average_days_widgets[1].show();
			this.average_days_widgets[2].show();
		}
	}

	onTypeChange() {
		if ( this.current_edit_record['type_id'] == 10 ) {
			this.detachElement( 'minimum_worked_days' );
			this.detachElement( 'shift_on_holiday_type_id' );
			this.detachElement( 'minimum_worked_after_days' );

			this.detachElement( 'average_time_days' );
			this.detachElement( 'average_time_worked_days' );
			this.edit_view_form_item_dic['minimum_time'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Holiday Time' ) );

			this.detachElement( 'maximum_time' );
			this.detachElement( 'force_over_time_policy' );
			this.detachElement( 'round_interval_policy_id' );
			this.detachElement( 'eligible_contributing_shift_policy_id' );
			this.detachElement( 'contributing_shift_policy_id' );

		} else if ( this.current_edit_record['type_id'] == 20 ) {
			this.attachElement( 'minimum_worked_days' );
			this.attachElement( 'shift_on_holiday_type_id' );
			this.attachElement( 'minimum_worked_after_days' );

			this.detachElement( 'average_time_days' );
			this.detachElement( 'average_time_worked_days' );
			this.edit_view_form_item_dic['minimum_time'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Holiday Time' ) );

			this.detachElement( 'maximum_time' );
			this.detachElement( 'force_over_time_policy' );
			this.detachElement( 'round_interval_policy_id' );
			this.attachElement( 'eligible_contributing_shift_policy_id' );
			this.detachElement( 'contributing_shift_policy_id' );

		} else if ( this.current_edit_record['type_id'] == 30 ) {
			this.attachElement( 'minimum_worked_days' );
			this.attachElement( 'shift_on_holiday_type_id' );
			this.attachElement( 'minimum_worked_after_days' );

			this.attachElement( 'average_time_days' );
			this.attachElement( 'average_time_worked_days' );
			this.edit_view_form_item_dic['minimum_time'].find( '.edit-view-form-item-label' ).text( $.i18n._( 'Minimum Time' ) );

			this.attachElement( 'maximum_time' );
			this.attachElement( 'force_over_time_policy' );
			this.attachElement( 'round_interval_policy_id' );
			this.attachElement( 'eligible_contributing_shift_policy_id' );
			this.attachElement( 'contributing_shift_policy_id' );
		}

		this.editFieldResize();
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
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
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

	initSubHolidayView() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		if ( this.sub_holiday_view_controller ) {
			this.sub_holiday_view_controller.buildContextMenu( true );
			this.sub_holiday_view_controller.setDefaultMenu();
			$this.sub_holiday_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_holiday_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_holiday_view_controller.initData();
			return;
		}

		Global.loadScript( 'views/policy/holiday/HolidayViewController.js', function() {

			var tab_holiday_policy = $this.edit_view_tab.find( '#tab_holidays' );
			var firstColumn = tab_holiday_policy.find( '.first-column-sub-view' );

			Global.trackView( 'Sub' + 'Holiday' + 'View' );
			HolidayViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {
			$this.sub_holiday_view_controller = subViewController;
			$this.sub_holiday_view_controller.parent_key = 'holiday_policy_id';
			$this.sub_holiday_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_holiday_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_holiday_view_controller.parent_view_controller = $this;
			$this.sub_holiday_view_controller.initData();
		}
	}

	removeEditView() {
		super.removeEditView();
		this.sub_holiday_view_controller = null;
	}
}
