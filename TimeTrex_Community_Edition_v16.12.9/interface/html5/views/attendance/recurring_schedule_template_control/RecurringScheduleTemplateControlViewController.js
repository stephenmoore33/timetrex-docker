export class RecurringScheduleTemplateControlViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#recurring_schedule_template_control_view_container',


			sub_document_view_controller: null,

			document_object_type_id: null,

			recurring_schedule_template_api: null,

			schedule_api: null,

			recurring_schedule_status_array: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RecurringScheduleTemplateControlEditView.html';
		this.permission_id = 'recurring_schedule_template';
		this.viewId = 'RecurringScheduleTemplateControl';
		this.script_name = 'RecurringScheduleTemplateControlView';
		this.table_name_key = 'recurring_schedule_template_control';
		this.context_menu_name = $.i18n._( 'Recurring Templates' );
		this.navigation_label = $.i18n._( 'Recurring Template' );
		this.api = TTAPI.APIRecurringScheduleTemplateControl;
		this.schedule_api = TTAPI.APISchedule;
		this.recurring_schedule_template_api = TTAPI.APIRecurringScheduleTemplate;

		this.document_object_type_id = 10;
		this.event_bus = new TTEventBus({ view_id: this.viewId });

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status', null, this.recurring_schedule_template_api, function( res ) {
			res = res.getResult();
			$this.recurring_schedule_status_array = Global.buildRecordArray( res );
		} );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_recurring_template': {
				'label': $.i18n._( 'Recurring Template' ),
				'html_template': this.getRecurringTemplateTabHtml()
			},
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;

		this.navigation.AComboBox( {
			api_class: TTAPI.APIRecurringScheduleTemplateControl,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_recurring_template_control',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//These width changes can cause crashes when tab is hidden on selenium screenshot unit test mode.
		//Left menu would not be clickable due to elements overlapping.
		if ( Global.UNIT_TEST_MODE == false ){
			this.edit_view_tab.css( 'max-width', 'none' );

			if ( Global.getProductEdition() >= 20 ) {
				this.edit_view_tab.css( 'min-width', '1250px' );
			} else if ( Global.getProductEdition() >= 15 ) {
				this.edit_view_tab.css( 'min-width', '1050px' );
			} else {
				this.edit_view_tab.css( 'min-width', '950px' );
			}
		}

		//Tab 0 start

		var tab_recurring_template = this.edit_view_tab.find( '#tab_recurring_template' );

		var tab_recurring_template_column1 = tab_recurring_template.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_recurring_template_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_recurring_template_column1, '' );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_recurring_template_column1 );

		form_item_input.parent().width( '45%' );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'created_by_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Created By' ), form_item_input, tab_recurring_template_column1 );

		form_item_input.parent().width( '45%' );

		//Inside editor

		var inside_editor_div = tab_recurring_template.find( '.inside-editor-div' );

		var args = {
			week: $.i18n._( 'Week' ),
			status: $.i18n._( 'Status' ),
			week_names: 'S&nbsp;&nbsp;M&nbsp;&nbsp;T&nbsp;&nbsp;W&nbsp;&nbsp;T&nbsp&nbsp;F&nbsp;&nbsp;S',
			shift_time: $.i18n._( 'Shift Time' ),
			total: $.i18n._( 'Total' ),
			schedule_policy: $.i18n._( 'Schedule Policy' ),
			branch_department: $.i18n._( 'Branch/Department' ),
			job_task: $.i18n._( 'Job/Task/Tags' ),
			open_shift_multiplier: $.i18n._( 'Open Shift Multiplier' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

		this.editor.InsideEditor( {
			title: $.i18n._( 'NOTE: To set different In/Out times for each day of the week, add additional weeks all with the same week number.' ),
			addRow: this.insideEditorAddRow,
			removeRow: this.insideEditorRemoveRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			parent_controller: this,
			api: this.recurring_schedule_template_api,
			render: getRender(),
			render_args: args,
			render_inline_html: true,
			row_render: getRowRender()

		} );

		function getRender() {
			var render = ''

			if ( Global.getProductEdition() >= 20 ) {
				render = `
				<table class="inside-editor-render">
					<tr class="title">
						<td style="width: 50px"><%= week %></td>
						<td style="width: 100px"><%= status %></td>
						<td style="width: 153px"><%= week_names %></td>
						<td style="width: 140px"><%= shift_time %></td>
						<td style="width: 45px"><%= total %></td>
						<td style="width: 140px"><%= schedule_policy %></td>
						<td style="width: 230px"><%= branch_department %></td>
						<td style="width: 190px"><%= job_task %></td>
						<td style="width: 100px"><%= open_shift_multiplier %></td>
					</tr>
				</table>`;
			} else if ( Global.getProductEdition() >= 15 ) {
				render = `
				<table class="inside-editor-render">
					<tr class="title">
						<td style="width: 50px"><%= week %></td>
						<td style="width: 100px"><%= status %></td>
						<td style="width: 153px"><%= week_names %></td>
						<td style="width: 140px"><%= shift_time %></td>
						<td style="width: 45px"><%= total %></td>
						<td style="width: 140px"><%= schedule_policy %></td>
						<td style="width: 230px"><%= branch_department %></td>
						<td style="width: 100px"><%= open_shift_multiplier %></td>
					</tr>
				</table>`;
			} else {
				render = `
				<table class="inside-editor-render">
					<tr class="title">
						<td style="width: 50px"><%= week %></td>
						<td style="width: 100px"><%= status %></td>
						<td style="width: 153px"><%= week_names %></td>
						<td style="width: 140px"><%= shift_time %></td>
						<td style="width: 45px"><%= total %></td>
						<td style="width: 140px"><%= schedule_policy %></td>
						<td style="width: 230px"><%= branch_department %></td>
					</tr>
				</table>`;
			}
			return render;
		}

		function getRowRender() {
			var render = '';

			if ( Global.getProductEdition() >= 20 ) {
				render = `<tr class="inside-editor-row data-row">
							<td class=""></td>
							<td class=""></td>
							<td class="week-cell"></td>
							<td class=""></td>
							<td class=""></td>
							<td class=""></td>
							<td class=""></td>
							<td class=""></td>
							<td class=""></td>
							<td class="cell control-icon">
								<button class="plus-icon" onclick=""></button>
							</td>
							<td class="cell control-icon">
								<button class="minus-icon " onclick=""></button>
							</td>
						</tr>`;
			} else if ( Global.getProductEdition() >= 15 ) {
				render = `<tr class="inside-editor-row data-row">
							<td class=""></td>
							<td class=""></td>
							<td class="week-cell"></td>
							<td class=""></td>
							<td class=""></td>
							<td class=""></td>
							<td class=""></td>
							<td class=""></td>
							<td class="cell control-icon">
								<button class="plus-icon" onclick=""></button>
							</td>
							<td class="cell control-icon">
								<button class="minus-icon " onclick=""></button>
							</td>
						</tr>`;
			} else {
				render = `
					<tr class="inside-editor-row data-row">
						<td class=""></td>
						<td class=""></td>
						<td class="week-cell"></td>
						<td class=""></td>
						<td class=""></td>
						<td class=""></td>
						<td class=""></td>
						<td class="cell control-icon">
							<button class="plus-icon" onclick=""></button>
						</td>
						<td class="cell control-icon">
							<button class="minus-icon " onclick=""></button>
						</td>
					</tr>`;
			}
			return render;
		}

		inside_editor_div.append( this.editor );
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		if ( !this.is_mass_editing ) {
			this.initInsideEditorData();
			this.edit_view.find( '.inside-editor-div' ).show();
		} else {
			this.edit_view.find( '.inside-editor-div' ).hide();
		}
	}

	initInsideEditorData() {
		var $this = this;
		var args = {};
		args.filter_data = {};

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) && !this.copied_record_id ) {
			$this.editor.removeAllRows();
			$this.editor.getDefaultData();

		} else {

			args.filter_data.recurring_schedule_template_control_id = this.current_edit_record.id ? this.current_edit_record.id : this.copied_record_id;
			this.copied_record_id = '';
			$this.recurring_schedule_template_api['get' + $this.recurring_schedule_template_api.key_name]( args, {
				onResult: function( res ) {
					if ( !$this.edit_view ) {
						return;
					}
					var data = res.getResult();
					$this.editor.setValue( data );

				}
			} );

		}
	}

	insideEditorAddRow( data, index ) {

		var form_item_input;

		var $this = this;
		if ( !data ) {
			this.getDefaultData( index );
		} else {
			var form_item_input;
			var widgetContainer;

			var row_id = ( data.id && this.parent_controller.current_edit_record.id ) ? data.id : TTUUID.generateUUID();
			var row = this.getRowRender(); //Get Row render
			var render = this.getRender(); //get render, should be a table
			var widgets = {}; //Save each row's widgets

			//Build row widgets

			// Week
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'week', width: 40 } );
			form_item_input.setValue( data.week ? data.week : 1 );
			widgets[form_item_input.getField()] = form_item_input;
			row.children().eq( 0 ).append( form_item_input );
			form_item_input.attr( 'recurring_schedule_template_id', row_id );
			form_item_input.attr( 'date_stamp', data.date_stamp ); //Needed to prepend start/end times with so they can be parsed properly and calculate Total Time.

			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Status
			widgetContainer = $( '<div class=\'recurring-template-status-div\'></div>' );

			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'status_id' } );
			form_item_input.setSourceData( this.parent_controller.recurring_schedule_status_array );
			form_item_input.setValue( data.status_id ? data.status_id : 10 );
			widgets[form_item_input.getField()] = form_item_input;

			form_item_input.bind( 'formItemChange', function( e, target ) {
				if ( target.getValue() == 10 ) {
					widgets['absence_policy_id'].hide();
				} else if ( target.getValue() == 20 ) {
					widgets['absence_policy_id'].show();
				}
			} );

			widgetContainer.append( form_item_input );

			this.setWidgetEnableBaseOnParentController( form_item_input );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APIAbsencePolicy,
				width: 132,
				allow_multiple_selection: false,
				layout_name: 'global_absences',
				show_search_inputs: true,
				set_empty: true,
				field: 'absence_policy_id'
			} );

			form_item_input.css( 'position', 'absolute' );
			form_item_input.css( 'left', '0' );
			form_item_input.css( 'top', '30px' );
			form_item_input.css( 'z-index', '1' ); //For some reason if this overlaps with the "checkboxes", it goes behind that div and makes the down arrow unclickable.
			form_item_input.setValue( data.absence_policy_id ? data.absence_policy_id : '' );
			widgets[form_item_input.getField()] = form_item_input;
			this.setWidgetEnableBaseOnParentController( form_item_input );
			widgetContainer.append( form_item_input );

			row.children().eq( 1 ).append( widgetContainer );

			// sun mon tue wed thu fri sat
			var widgetContainer2 = $( '<div class=\'widget-h-box\'></div>' );
			// Sun
			var form_item_sun_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_sun_checkbox.TCheckbox( { field: 'sun' } );
			form_item_sun_checkbox.setValue( data.sun ? data.sun : false );
			widgets[form_item_sun_checkbox.getField()] = form_item_sun_checkbox;
			widgetContainer2.append( form_item_sun_checkbox );

			this.setWidgetEnableBaseOnParentController( form_item_sun_checkbox );
			// Mon
			var form_item_mon_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_mon_checkbox.TCheckbox( { field: 'mon' } );
			form_item_mon_checkbox.setValue( data.mon ? data.mon : false );
			widgets[form_item_mon_checkbox.getField()] = form_item_mon_checkbox;
			widgetContainer2.append( form_item_mon_checkbox );

			this.setWidgetEnableBaseOnParentController( form_item_mon_checkbox );
			// Tue
			var form_item_tue_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_tue_checkbox.TCheckbox( { field: 'tue' } );
			form_item_tue_checkbox.setValue( data.tue ? data.tue : false );
			widgets[form_item_tue_checkbox.getField()] = form_item_tue_checkbox;
			widgetContainer2.append( form_item_tue_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_tue_checkbox );
			// Wed
			var form_item_wed_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_wed_checkbox.TCheckbox( { field: 'wed' } );
			form_item_wed_checkbox.setValue( data.wed ? data.wed : false );
			widgets[form_item_wed_checkbox.getField()] = form_item_wed_checkbox;
			widgetContainer2.append( form_item_wed_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_wed_checkbox );
			// Thu
			var form_item_thu_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_thu_checkbox.TCheckbox( { field: 'thu' } );
			form_item_thu_checkbox.setValue( data.thu ? data.thu : false );
			widgets[form_item_thu_checkbox.getField()] = form_item_thu_checkbox;
			widgetContainer2.append( form_item_thu_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_thu_checkbox );
			// Fri
			var form_item_fri_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_fri_checkbox.TCheckbox( { field: 'fri' } );
			form_item_fri_checkbox.setValue( data.fri ? data.fri : false );
			widgets[form_item_fri_checkbox.getField()] = form_item_fri_checkbox;
			widgetContainer2.append( form_item_fri_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_fri_checkbox );
			// Sat
			var form_item_sat_checkbox = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_sat_checkbox.TCheckbox( { field: 'sat' } );
			form_item_sat_checkbox.setValue( data.sat ? data.sat : false );
			widgets[form_item_sat_checkbox.getField()] = form_item_sat_checkbox;
			widgetContainer2.append( form_item_sat_checkbox );
			this.setWidgetEnableBaseOnParentController( form_item_sat_checkbox );

			row.children().eq( 2 ).append( widgetContainer2 );

			// Shift Time
			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var divContainer1 = $( '<div style=\'text-align: left; \'></div>' );

			var label_1 = $( '<span class=\'widget-right-label recurring-template-widget-right-label\' style=\'display: inline-block; width: 28px; vertical-align: middle;\'> ' + $.i18n._( 'In' ) + ' </span>' );
			form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
			form_item_input.TTimePicker( { field: 'start_time' } );
			form_item_input.setValue( data.start_time ? data.start_time : '' );

			form_item_input.bind( 'formItemChange', function( e, target ) {

				var rows_widgets = $this.rows_widgets_array[target.parent().parent().parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets );
			} );

			widgets[form_item_input.getField() + row_id] = form_item_input;
			this.parent_controller.edit_view_ui_validation_field_dic[form_item_input.getField() + row_id] = form_item_input;

			divContainer1.append( label_1 );
			divContainer1.append( form_item_input );

			widgetContainer.append( divContainer1 );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			var divContainer2 = $( '<div style=\'text-align: left; margin-top: 5px;\'></div>' );

			var label_2 = $( '<span class=\'widget-right-label recurring-template-widget-right-label\' style=\'display: inline-block; width: 28px; vertical-align: middle;\' > ' + $.i18n._( 'Out' ) + ' </span>' );
			form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
			form_item_input.TTimePicker( { field: 'end_time' } );
			form_item_input.setValue( data.end_time ? data.end_time : '' );

			form_item_input.bind( 'formItemChange', function( e, target ) {
				var rows_widgets = $this.rows_widgets_array[target.parent().parent().parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets );
			} );

			widgets[form_item_input.getField() + row_id] = form_item_input;
			this.parent_controller.edit_view_ui_validation_field_dic[form_item_input.getField() + row_id] = form_item_input;

			divContainer2.append( label_2 );
			divContainer2.append( form_item_input );

			widgetContainer.append( divContainer2 );

			row.children().eq( 3 ).append( widgetContainer );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Total
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'total_time' } );
			form_item_input.setValue( data.total_time ? Global.getTimeUnit( data.total_time ) : '' ); //
			widgets[form_item_input.getField()] = form_item_input;

			row.children().eq( 4 ).append( form_item_input );

			// Schedule Policy

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APISchedulePolicy,
				width: 80,
				allow_multiple_selection: false,
				layout_name: 'global_schedule',
				show_search_inputs: true,
				set_empty: true,
				field: 'schedule_policy_id'
			} );

			form_item_input.setValue( data.schedule_policy_id ? data.schedule_policy_id : '' );
			widgets[form_item_input.getField()] = form_item_input;

			row.children().eq( 5 ).append( form_item_input );

			form_item_input.bind( 'formItemChange', function( e, target ) {
				var rows_widgets = $this.rows_widgets_array[target.parent().parent().index() - 1];

				$this.parent_controller.onRowChanges( rows_widgets );
			} );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Branch / Department

			widgetContainer = $( '<div class=\'widget-h-box recurring-template-widget-h-box\'></div>' );

			divContainer1 = $( '<div></div>' );

			label_1 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 74px;\'> ' + $.i18n._( 'Branch' ) + ' </span>' );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APIBranch,
				width: 80,
				allow_multiple_selection: false,
				layout_name: 'global_branch',
				show_search_inputs: true,
				set_any: true,
				field: 'branch_id',
				custom_first_label: Global.default_item
			} );

			if ( data.branch_id.toUpperCase() === TTUUID.not_exist_id.toUpperCase() ) {
				form_item_input.set_default_value = true;
			} else {
				form_item_input.setValue( data.branch_id ? data.branch_id : '' );
			}
			widgets[form_item_input.getField()] = form_item_input;

			divContainer1.append( label_1 );
			divContainer1.append( form_item_input );

			widgetContainer.append( divContainer1 );

			divContainer2 = $( '<div style=\'margin-top: 5px; float: left\'></div>' );

			label_2 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 74px;\'> ' + $.i18n._( 'Department' ) + ' </span>' );

			this.setWidgetEnableBaseOnParentController( form_item_input );

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APIDepartment,
				width: 80,
				allow_multiple_selection: false,
				layout_name: 'global_department',
				show_search_inputs: true,
				set_any: true,
				field: 'department_id',
				custom_first_label: Global.default_item
			} );
			form_item_input.setValue( ( data.department_id ) ? data.department_id : '' );
			widgets[form_item_input.getField()] = form_item_input;

			divContainer2.append( label_2 );
			divContainer2.append( form_item_input );

			widgetContainer.append( divContainer2 );

			row.children().eq( 6 ).append( widgetContainer );
			this.setWidgetEnableBaseOnParentController( form_item_input );

			// Job/Task/Punch Tag

			if ( ( Global.getProductEdition() >= 20 ) ) {

				widgetContainer = $( '<div class=\'widget-h-box recurring-template-widget-h-box\'></div>' );

				divContainer1 = $( '<div></div>' );

				label_1 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 32px;\'> ' + $.i18n._( 'Job' ) + ' </span>' );

				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: TTAPI.APIJob,
					width: 80,
					allow_multiple_selection: false,
					layout_name: 'global_job',
					show_search_inputs: true,
					set_any: true,
					field: 'job_id',
					custom_first_label: Global.default_item
				} );
				form_item_input.setValue( data.job_id ? data.job_id : '' );
				widgets[form_item_input.getField()] = form_item_input;

				divContainer1.append( label_1 );
				divContainer1.append( form_item_input );

				widgetContainer.append( divContainer1 );

				divContainer2 = $( '<div style=\'margin-top: 5px; float: left\'></div>' );

				label_2 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 32px;\'> ' + $.i18n._( 'Task' ) + ' </span>' );
				this.setWidgetEnableBaseOnParentController( form_item_input );

				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: TTAPI.APIJobItem,
					width: 80,
					allow_multiple_selection: false,
					layout_name: 'global_job_item',
					show_search_inputs: true,
					set_any: true,
					field: 'job_item_id',
					custom_first_label: Global.default_item
				} );
				form_item_input.setValue( data.job_item_id ? data.job_item_id : '' );
				widgets[form_item_input.getField()] = form_item_input;

				divContainer2.append( label_2 );
				divContainer2.append( form_item_input );

				widgetContainer.append( divContainer2 );

				var divContainer3 = $( '<div style=\'margin-top: 5px; float: left\'></div>' );

				var label_3 = $( '<span class=\'widget-right-label\' style=\'float: left; height: 24px; line-height: 24px; min-width: 32px;\'> ' + $.i18n._( 'Tags' ) + ' </span>' );
				this.setWidgetEnableBaseOnParentController( form_item_input );

				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: TTAPI.APIPunchTag,
					width: 80,
					allow_multiple_selection: true,
					layout_name: 'global_punch_tag',
					show_search_inputs: true,
					set_any: true,
					setRealValueCallBack: ( ( punch_tags ) => {
						if ( punch_tags ) {
							this.parent_controller.setPunchTagQuickSearchManualIds( punch_tags );
						}
					} ),
					field: 'punch_tag_id',
					custom_first_label: Global.default_item
				} );
				form_item_input.setValue( data.punch_tag_id ? data.punch_tag_id : '' );
				widgets[form_item_input.getField()] = form_item_input;

				divContainer3.append( label_3 );
				divContainer3.append( form_item_input );

				widgetContainer.append( divContainer3 );

				row.children().eq( 7 ).append( widgetContainer );
				this.setWidgetEnableBaseOnParentController( form_item_input );

			}

			if ( Global.getProductEdition() >= 15 ) {
				// Open Shift Multiplier
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: 'open_shift_multiplier', width: 20 } );
				form_item_input.setValue( data.open_shift_multiplier ? data.open_shift_multiplier : 1 );
				widgets[form_item_input.getField()] = form_item_input;

				if ( Global.getProductEdition() <= 15 ) {
					row.children().eq( 7 ).append( form_item_input );
				} else {
					row.children().eq( 8 ).append( form_item_input );
				}

				this.setWidgetEnableBaseOnParentController( form_item_input );
			}

			if ( typeof index != 'undefined' ) {

				row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
				this.rows_widgets_array.splice( ( index ), 0, widgets );

			} else {
				$( render ).append( row );
				this.rows_widgets_array.push( widgets );
			}

			if ( this.parent_controller.is_viewing ) {
				row.find( '.control-icon' ).hide();
			}

			if ( widgets.status_id.getValue() == 10 ) {
				widgets.absence_policy_id.css( 'display', 'none' );
			} else if ( widgets.status_id.getValue() == 20 ) {
				widgets.absence_policy_id.css( 'display', 'block' );
			}

			this.addIconsEvent( row ); //Bind event to add and minus icon
			this.removeLastRowLine();
		}
	}

	onRowChanges( row_widgets ) {
		var recurring_schedule_template_id = row_widgets.week.attr( 'recurring_schedule_template_id' );
		var date_stamp = row_widgets.week.attr( 'date_stamp' );

		if ( recurring_schedule_template_id ) {
			var startTime = date_stamp +' '+ row_widgets['start_time' + recurring_schedule_template_id].getValue();
			var endTime = date_stamp +' '+ row_widgets['end_time' + recurring_schedule_template_id].getValue();
			var schedulePolicyId = row_widgets.schedule_policy_id.getValue();

			if ( startTime !== '' && endTime !== '' && schedulePolicyId !== '' ) {
				var result = this.schedule_api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, { async: false } );
				if ( result.isValid() && result.getResult() ) {
					var total_time = result.getResult();

					row_widgets.total_time.setValue( Global.getTimeUnit( total_time ) );
				}
			}

			this.validate();
		}
	}

	insideEditorGetValue( current_edit_item_id ) {
		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];

			var recurring_schedule_template_id = row.week.attr( 'recurring_schedule_template_id' );
			var date_stamp = row.week.attr( 'date_stamp' );

			if ( recurring_schedule_template_id ) {
				var data = {
					id: recurring_schedule_template_id,

					week: row.week.getValue(),
					status_id: row.status_id.getValue(),

					mon: row.mon.getValue(),
					tue: row.tue.getValue(),
					wed: row.wed.getValue(),
					thu: row.thu.getValue(),
					fri: row.fri.getValue(),
					sat: row.sat.getValue(),
					sun: row.sun.getValue(),

					date_stamp: date_stamp,
					start_time: row['start_time' + recurring_schedule_template_id].getValue(),
					end_time: row['end_time' + recurring_schedule_template_id].getValue(),
					total_time: null,

					branch_id: row.branch_id.getValue(),
					department_id: row.department_id.getValue(),

					absence_policy_id: row.absence_policy_id.getValue(),
					schedule_policy_id: row.schedule_policy_id.getValue()
				};

				if ( Global.getProductEdition() >= 15 ) {
					data.open_shift_multiplier = row.open_shift_multiplier.getValue();
				}

				if ( Global.getProductEdition() >= 20 ) {
					data.job_id = row.job_id.getValue();
					data.job_item_id = row.job_item_id.getValue();
					data.punch_tag_id = row.punch_tag_id.getValue();
				}

				data.recurring_schedule_template_control_id = current_edit_item_id;
				result.push( data );
			}
		}

		return result;
	}

	insideEditorSetValue( val ) {
		var len = val.length;
		this.removeAllRows();

		if ( len > 0 ) {
			for ( var i = 0; i < val.length; i++ ) {
				if ( Global.isSet( val[i] ) ) {
					var row = val[i];
					this.addRow( row );
				}
			}
		} else {
			this.getDefaultData();
		}
	}

	insideEditorRemoveRow( row ) {
		var index = row[0].rowIndex - 1;
		var remove_id = this.rows_widgets_array[index].week.attr( 'recurring_schedule_template_id' );
		if ( TTUUID.isUUID( remove_id ) && remove_id != TTUUID.zero_id && remove_id != TTUUID.not_exist_id ) {
			this.delete_ids.push( remove_id );
		}
		row.remove();
		this.rows_widgets_array.splice( index, 1 );

		this.removeLastRowLine();
	}

	uniformVariable( records ) {
		if ( !this.is_mass_editing ) {
			records.recurring_schedule_template = this.editor.getValue( this.refresh_id );
		}
		return records;
	}

	renameObjectKey( obj, old_key, new_key ) {
		if ( old_key !== new_key ) {
			Object.defineProperty( obj, new_key,
				Object.getOwnPropertyDescriptor( obj, old_key ) );
			delete obj[old_key];
		}
	}

	_continueDoCopyAsNew() {
		this.setCurrentEditViewState( 'new' );
		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {
			for ( var i = 0; i < this.editor.rows_widgets_array.length; i++ ) {
				//Fix JS exception: Uncaught TypeError: Cannot read property 'getValue' of undefined
				//start_time,end_time object keys are appended with the recurring_schedule_template_id, so when copying records we need to rename them to use the new recurring_schedule_template_id
				var new_uuid = TTUUID.generateUUID();
				var old_recurring_schedule_template_id = this.editor.rows_widgets_array[i].week.attr( 'recurring_schedule_template_id' );

				this.renameObjectKey( this.editor.rows_widgets_array[i], 'start_time' + old_recurring_schedule_template_id, 'start_time' + new_uuid );
				this.renameObjectKey( this.editor.rows_widgets_array[i], 'end_time' + old_recurring_schedule_template_id, 'end_time' + new_uuid );

				this.editor.rows_widgets_array[i].week.attr( 'recurring_schedule_template_id', new_uuid );
			}
		}
		super._continueDoCopyAsNew();
	}

	onCopyAsNewResult( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
			$this.onCancelClick();
			return;
		}

		$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

		result_data = result_data[0];
		this.copied_record_id = result_data.id;
		result_data.id = '';
		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
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
				label: $.i18n._( 'Description' ),
				in_column: 1,
				field: 'description',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),

			new SearchField( {
				label: $.i18n._( 'Template' ),
				in_column: 1,
				field: 'id',
				layout_name: 'global_recurring_template_control',
				api_class: TTAPI.APIRecurringScheduleTemplateControl,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Schedule Policy' ),
				in_column: 2,
				field: 'schedule_policy_id',
				layout_name: 'global_schedule',
				api_class: TTAPI.APISchedulePolicy,
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

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Jump To' ),
					id: 'jump_to_header',
					menu_align: 'right',
					action_group: 'jump_to',
					action_group_header: true,
					permission_result: false // to hide it in legacy context menu and avoid errors in legacy parsers.
				},
				{
					label: $.i18n._( 'Recurring Schedules' ),
					id: 'recurring_schedule',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				}]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'recurring_schedule':
				this.onNavigationClick( id );
				break;
		}
	}

	onNavigationClick( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = { filter_data: {} };

		var recurring_schedule_template_control_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			recurring_schedule_template_control_ids.push( $this.current_edit_record.id );
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				recurring_schedule_template_control_ids.push( grid_selected_row.id );
			} );
		}

		filter.filter_data.recurring_schedule_template_control_id = recurring_schedule_template_control_ids;

		switch ( iconName ) {
			case 'recurring_schedule':
				Global.addViewTab( this.viewId, $.i18n._( 'Recurring Templates' ), window.location.href );
				IndexViewController.goToView( 'RecurringScheduleControl', filter );
				break;
		}
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'recurring_schedule':
				this.setDefaultMenuRecurringScheduleIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setDefaultMenuRecurringScheduleIcon( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission( 'RecurringScheduleControl' ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'recurring_schedule':
				this.setEditMenuRecurringScheduleIcon( context_btn );
				break;
		}
	}

	setEditMenuRecurringScheduleIcon( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

	}

	onSaveResult( result ) {
		super.onSaveResult( result );
		if ( result && result.isValid() ) {
			var system_job_queue = result.getAttributeInAPIDetails( 'system_job_queue' );
			if ( system_job_queue ) {
				this.event_bus.emit( 'tt_topbar', 'toggle_job_queue_spinner', {
					show: true,
					get_job_data: true
				} );
			}
		}
	}

	getRecurringTemplateTabHtml() {
		return `<div id="tab_recurring_template" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_recurring_template_content_div">
						<div class="first-column full-width-column"></div>
						<div class="inside-editor-div full-width-column">
						</div>
					</div>
				</div>`;
	}
}
