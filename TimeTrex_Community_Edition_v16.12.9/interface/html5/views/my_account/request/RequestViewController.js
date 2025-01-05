export class RequestViewController extends RequestViewCommonController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#request_view_container',

			type_array: null,
			request_type_array: null,
			status_array: null,

			api_request_schedule: null,
			authorization_api: null,
			schedule_api: null,
			hierarchy_type_id: false,

			punch_tag_api: null,
			default_punch_tag: [],
			previous_punch_tag_selection: [],

			overlapping_shift_data: {},

			messages: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options);
		this.edit_view_tpl = 'RequestEditView.html';
		this.permission_id = 'request';
		this.viewId = 'Request';
		this.script_name = 'RequestView';
		this.table_name_key = 'request';
		this.context_menu_name = $.i18n._( 'Requests' );
		this.navigation_label = $.i18n._( 'Request' );
		this.api = TTAPI.APIRequest;
		this.api_absence_policy = TTAPI.APIAbsencePolicy;
		this.api_schedule = TTAPI.APISchedule;
		this.message_control_api = TTAPI.APIMessageControl;

		if ( ( Global.getProductEdition() >= 15 ) ) {
			this.api_request_schedule = TTAPI.APIRequestSchedule;
			this.schedule_api = TTAPI.APISchedule;
		}
		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.punch_tag_api = TTAPI.APIPunchTag;
		}

		this.authorization_api = TTAPI.APIAuthorization;

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	// override allows a callback after initOptions when run as sub view (from EmployeeViewController)
	initOptions( callBack ) {

		var options = [
			{ option_name: 'status' },
			{ option_name: 'type' }
		];

		if ( ( Global.getProductEdition() >= 15 ) ) {
			options.push( { option_name: 'overlap_type', api: TTAPI.APIRequestSchedule } );
		}

		this.initDropDownOptions( options, function( result ) {

			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}

		} );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var tab_model = {
			'tab_request': { 'label': $.i18n._( 'Message' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		//This hides the audit tab as this view is always used for creating/replying to an existing request.
		//For some reason removing 'tab_audit' from the model above results in a blank tab appearing.
		var tab_audit_label = this.edit_view.find( 'a[ref=tab_audit]' );
		tab_audit_label.css( 'display', 'none' );

		//Tab 0 start

		var tab_request = this.edit_view_tab.find( '#tab_request' );
		var tab_request_column1 = tab_request.find( '.first-column' );
		var tab_request_column2 = tab_request.find( '.second-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_request_column1 );

		// Subject
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'subject', width: 359 } );
		this.addEditFieldToColumn( $.i18n._( 'Subject' ), form_item_input, tab_request_column1, '' );

		// Body
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( { field: 'body', width: 600, height: 400 } );

		this.addEditFieldToColumn( $.i18n._( 'Body' ), form_item_input, tab_request_column1, '', null, null, true );

		tab_request_column2.css( 'display', 'none' );
	}

	buildAddViewUI() {
		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_request': { 'label': $.i18n._( 'Request' ), 'is_multi_column': true },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		//This hides the audit tab as this view is always used for creating/replying to an existing request.
		//For some reason removing 'tab_audit' from the model above results in a blank tab appearing.
		var tab_audit_label = this.edit_view.find( 'a[ref=tab_audit]' );
		tab_audit_label.css( 'display', 'none' );

		//Tab 0 start
		var tab_request = this.edit_view_tab.find( '#tab_request' );
		var tab_request_column1 = tab_request.find( '.first-column' );
		var tab_request_column2 = tab_request.find( '.second-column' );

		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_request_column1 );
		this.edit_view_tabs[0].push( tab_request_column2 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'full_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_request_column1, '' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_request_column1 );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'date_stamp' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( '(Use the first or only date affected by this request)' ) + '</span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_request_column1, '', widgetContainer );

		if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'request', 'add_advanced' ) ) {
			//Working Status
			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'request_schedule_status_id', set_empty: false } );
			form_item_input.setSourceData( { 10: 'Working', 20: 'Absent' } );
			this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_request_column1 );

			//Absence Policy
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIAbsencePolicy,
				customSearchFilter: function() {
					return { filter_data: { user_id: LocalCacheData.getLoginUser().id } };
				},
				allow_multiple_selection: false,
				layout_name: 'global_absences',
				set_empty: true,
				field: 'absence_policy_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Absence Policy' ), form_item_input, tab_request_column1 );

			//Available Balance
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'available_balance' } );
			widgetContainer = $( '<div class=\'widget-h-box available-balance-h-box\'></div>' );
			this.available_balance_info = $( '<span class="available-balance-info tticon tticon-info_black_24dp"></span>' );
			widgetContainer.append( form_item_input );
			widgetContainer.append( this.available_balance_info );
			this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), form_item_input, tab_request_column1, '', widgetContainer, true );

			//Start Date
			form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
			form_item_input.TDatePicker( { field: 'start_date' } );
			this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_request_column1, '' );

			//End  Date
			form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
			form_item_input.TDatePicker( { field: 'end_date' } );
			this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_request_column1, '' );

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

			widgetContainer = $( '<div></div>' );

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

			widgetContainer.addClass( 'request_edit_view_effective_days' );

			this.addEditFieldToColumn( $.i18n._( 'Effective Days' ), [form_item_sun_checkbox, form_item_mon_checkbox, form_item_tue_checkbox, form_item_wed_checkbox, form_item_thu_checkbox, form_item_fri_checkbox, form_item_sat_checkbox], tab_request_column1, '', widgetContainer, false, true );

			//Start time
			form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
			form_item_input.TTimePicker( { field: 'start_time' } );
			this.addEditFieldToColumn( $.i18n._( 'In' ), form_item_input, tab_request_column1 );

			//End  time
			form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
			form_item_input.TTimePicker( { field: 'end_time' } );
			this.addEditFieldToColumn( $.i18n._( 'Out' ), form_item_input, tab_request_column1 );

			// Total
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'total_time' } );
			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
			label = $( '<span class=\'widget-right-label\' id=\'total_info\'></span>' );
			widgetContainer.append( form_item_input );
			widgetContainer.append( label );
			this.addEditFieldToColumn( $.i18n._( 'Total' ), form_item_input, tab_request_column1, '', widgetContainer );

			if ( ( Global.getProductEdition() >= 15 ) && this.overlappingShiftUIValidate() == true ) {
				//Override / Split shift
				var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( { field: 'overlap_type_id', set_empty: false } );
				form_item_input.setSourceData( this.overlap_type_array );
				widgetContainer = $( '<div class=\'widget-h-box overlapping-shift-h-box\'></div>' );
				this.overlapping_shift_info = $( '<span style="position: relative; top: -3px; left: 3px;" id="overlapping-shift-total"></span><span id="overlapping-shift-icon" class="overlapping-shift-info tticon tticon-info_black_24dp"></span>' );
				widgetContainer.append( form_item_input );
				widgetContainer.append( this.overlapping_shift_info );
				this.addEditFieldToColumn( $.i18n._( 'Overlapping Shift(s)' ), form_item_input, tab_request_column1, '', widgetContainer, true );
			}

			//Schedule Policy
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APISchedulePolicy,
				allow_multiple_selection: false,
				layout_name: 'global_schedule',
				show_search_inputs: true,
				set_empty: true,
				field: 'schedule_policy_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Schedule Policy' ), form_item_input, tab_request_column1 );

			//branch
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIBranch,
				allow_multiple_selection: false,
				layout_name: 'global_branch',
				show_search_inputs: true,
				set_empty: true,
				field: 'branch_id'
			} );
			if ( this.show_branch_ui ) {
				this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_request_column1 );
			}

			//Dept
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIDepartment,
				allow_multiple_selection: false,
				layout_name: 'global_department',
				show_search_inputs: true,
				set_empty: true,
				field: 'department_id'
			} );

			if ( this.show_department_ui ) {
				this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_request_column1 );
			}

			if ( ( Global.getProductEdition() >= 20 ) ) {
				//Job
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: TTAPI.APIJob,
					allow_multiple_selection: false,
					layout_name: 'global_job',
					show_search_inputs: true,
					set_empty: true,
					setRealValueCallBack: ( function( val ) {

						if ( val ) {
							job_coder.setValue( val.manual_id );
						}
					} ),
					field: 'job_id',
					added_items: [
						{ value: '-1', label: Global.default_item },
						{ value: '-2', label: Global.selected_item }
					]
				} );

				widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

				var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				job_coder.TTextInput( { field: 'job_quick_search', disable_keyup_event: true } );
				job_coder.addClass( 'job-coder' );

				widgetContainer.append( job_coder );
				widgetContainer.append( form_item_input );
				this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_request_column1, '', widgetContainer, true );

				if ( !this.show_job_ui ) {
					//invalid permissions
					this.detachElement( 'job_id' );
				}
				//Job Item
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: TTAPI.APIJobItem,
					allow_multiple_selection: false,
					layout_name: 'global_job_item',
					show_search_inputs: true,
					set_empty: true,
					setRealValueCallBack: ( function( val ) {

						if ( val ) {
							job_item_coder.setValue( val.manual_id );
						}
					} ),
					field: 'job_item_id',
					added_items: [
						{ value: '-1', label: Global.default_item },
						{ value: '-2', label: Global.selected_item }
					]
				} );

				widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

				var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true } );
				job_item_coder.addClass( 'job-coder' );

				widgetContainer.append( job_item_coder );
				widgetContainer.append( form_item_input );
				this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_request_column1, '', widgetContainer, true );

				if ( !this.show_job_item_ui ) {
					//invalid permissions
					this.detachElement( 'job_item_id' );
				}

				//Punch Tag
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

				form_item_input.AComboBox( {
					api_class: TTAPI.APIPunchTag,
					allow_multiple_selection: true,
					layout_name: 'global_punch_tag',
					show_search_inputs: true,
					set_empty: true,
					setRealValueCallBack: ( ( punch_tags ) => {
						if ( punch_tags ) {
							this.setPunchTagQuickSearchManualIds( punch_tags );
						}
					} ),
					field: 'punch_tag_id'
				} );

				widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

				var punch_tag_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				punch_tag_coder.TTextInput( { field: 'punch_tag_quick_search', disable_keyup_event: true } );
				punch_tag_coder.addClass( 'job-coder' );

				widgetContainer.append( punch_tag_coder );
				widgetContainer.append( form_item_input );
				this.addEditFieldToColumn( $.i18n._( 'Tags' ), [form_item_input, punch_tag_coder], tab_request_column1, '', widgetContainer, true );

				if ( !this.show_punch_tag_ui ) {
					//invalid permissions
					this.detachElement( 'punch_tag_id' );
				}
			}
		}

		// Message
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'message', width: 400, height: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Reason / Message' ), form_item_input, tab_request_column1, '', null, null, true );

		//hide initially hidden fields.
		//tab_request_column2.css( 'display', 'none' );
		if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'request', 'add_advanced' ) ) {
			this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
			this.hideAdvancedFields();
		}

		this.onTypeChanged();
		this.onWorkingStatusChanged();
	}

	buildSearchFields() {
		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
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
				label: $.i18n._( 'Start Date' ),
				in_column: 1,
				field: 'start_date',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'End Date' ),
				in_column: 1,
				field: 'end_date',
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 2,
				field: 'status_id',
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

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				{
					label: $.i18n._( 'New' ),
					id: 'add',
					group: 'editor',
					vue_icon: 'tticon tticon-add_black_24dp',
					show_on_right_click: true,
				},
				{
					label: $.i18n._( 'View' ),
					id: 'view',
					group: 'editor',
					vue_icon: 'tticon tticon-visibility_black_24dp',
					show_on_right_click: true,
					sort_order: 1010
				},
				{
					label: $.i18n._( 'Reply' ),
					id: 'edit',
					vue_icon: 'tticon tticon-reply_black_24dp'
				},
				{
					label: $.i18n._( 'Send' ),
					id: 'send',
					group: 'editor',
					vue_icon: 'tticon tticon-send_black_24dp'
				},
				{
					label: $.i18n._( 'Delete' ),
					id: 'delete_icon',
					action_group: 'delete',
					group: 'editor',
					vue_icon: 'tticon tticon-delete_black_24dp',
					show_on_right_click: true,
				},
				{
					label: $.i18n._( 'Delete & Next' ),
					id: 'delete_and_next',
					action_group: 'delete',
					group: 'editor',
					vue_icon: 'tticon tticon-delete_black_24dp'
				},
				{
					label: $.i18n._( 'Cancel' ),
					id: 'cancel',
					group: 'editor',
					sort_order: 1990
				},
				{
					label: $.i18n._( 'Jump To' ),
					id: 'jump_to_header',
					menu_align: 'right',
					action_group: 'jump_to',
					action_group_header: true,
					permission_result: false // to hide it in legacy context menu and avoid errors in legacy parsers.
				},
				{
					label: $.i18n._( 'TimeSheet' ),
					id: 'timesheet',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					},
				{
					label: $.i18n._( 'Schedule' ),
					id: 'schedule',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					},
				{
					label: $.i18n._( 'Export' ),
					id: 'export_excel',
					menu_align: 'right',
					vue_icon: 'tticon tticon-file_upload_black_24dp',
					sort_order: 100
				},
				{
					label: $.i18n._( 'Edit Employee' ),
					id: 'edit_employee',
					menu_align: 'right',
					action_group: 'jump_to',
				},
			]
		};

		return context_menu_model;
	}

	setCurrentEditRecordData( current_edit_record ) {
		var $this = this;
		if ( current_edit_record ) {
			this.current_edit_record = current_edit_record;
		}

		if ( !this.current_edit_record ) {
			this.current_edit_record = {};
		}
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'full_name':
						if ( this.is_add ) {
							widget.setValue( LocalCacheData.loginUser.first_name + ' ' + LocalCacheData.loginUser.last_name );
						} else if ( this.is_viewing ) {
							widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						}
						break;
					case 'subject':
						// Error: Uncaught TypeError: Cannot read property '0' of null in /interface/html5/#!m=Request&a=view&id=13185&tab=Request line 505
						if ( this.is_edit && this.messages ) {
							widget.setValue( 'Re: ' + this.messages[0].subject );
						} else if ( this.is_viewing ) {
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'punch_tag_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							widget.setValue( this.current_edit_record[key] );
							this.previous_punch_tag_selection = this.current_edit_record[key];

							var punch_tag_widget = widget;
							TTPromise.wait( null, null, function() {
								//Update default args for punch tags AComboBox last as they rely on data from job, job item and related fields.
								var args = {};
								args.filter_data = $this.getPunchTagFilterData();
								punch_tag_widget.setDefaultArgs( args );
							} );
						}
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();

		//a new request comes from the current user.
		if ( this.is_add ) {
			this.current_edit_record.user_id = LocalCacheData.loginUser.id;
		}

		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		var $this = this;
		super.setEditViewDataDone();
		if ( this.is_viewing ) {

		} else {
			if ( Global.isSet( $this.messages ) ) {
				$this.messages = null;
			}
		}
		if ( ( Global.getProductEdition() >= 15 ) ) {
			$this.getOverlappingShifts();
		}
	}

	initViewingView() {
		super.initViewingView();
		if ( this.edit_view_ui_dic.message ) {
			this.edit_view_ui_dic.message.parents( '.edit-view-form-item-div' ).hide();
		}
		if ( Global.getProductEdition() >= 15 && ( this.edit_view_ui_dic.type_id == 30 || this.edit_view_ui_dic.type_id == 40 ) ) {
			var dow = ['sun', 'mon', 'tue', 'wed', 'thu', 'fri', 'sat'];
			var one_selected = false;
			for ( var key in this.current_edit_record ) {
				if ( dow.indexOf( key ) > -1 ) {
					for ( var i = 0; i < 7; i++ ) {
						if ( this.current_edit_record[key] !== 0 && this.current_edit_record[key] !== false ) {
							one_selected = true;
							break;
						}
					}

					if ( one_selected ) {
						this.edit_view_ui_dic['sun'].parents( '.edit-view-form-item-div' ).show();
					} else {
						this.edit_view_ui_dic['sun'].parents( '.edit-view-form-item-div' ).hide();
					}
				}
			}
		}
	}

	setURL() {

		if ( LocalCacheData.current_doing_context_action === 'edit' ) {
			LocalCacheData.current_doing_context_action = '';
			return;
		}

		super.setURL();
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'send':
				this.setDefaultMenuSendIcon( context_btn, grid_selected_length );
				break;
			case 'timesheet':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
				break;
			case 'schedule':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length, 'user' );
				break;
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'send':
				this.setEditMenuSaveIcon( context_btn );
				break;
			case 'timesheet':
				this.setEditMenuNavViewIcon( context_btn, 'punch' );
				break;
			case 'schedule':
				this.setEditMenuNavViewIcon( context_btn, 'schedule' );
				break;
			case 'edit_employee':
				this.setEditMenuNavEditIcon( context_btn, 'user' );
				break;
		}
	}

	setDefaultMenuCancelIcon( context_btn, grid_selected_length, pId ) {
		if ( !this.sub_view_mode && !this.is_viewing ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuExportIcon( context_btn, grid_selected_length, pId ) {
		if ( this.edit_only_mode || this.is_viewing || this.is_edit || this.is_add ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else if ( grid_selected_length == 0 || this.grid == undefined ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuAddIcon( context_btn, pId ) {
		super.setEditMenuAddIcon( context_btn, pId );

		if ( this.is_edit || this.is_add ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteAndNextIcon( context_btn, pId ) {
		if ( this.is_edit || this.is_add ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuNavViewIcon( context_btn, pId ) {
		if ( this.is_edit || this.is_add ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteIcon( context_btn, pId ) {
		if ( this.is_edit || this.is_add ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onTypeChanged( arg ) {
		if ( this.current_edit_record && Global.getProductEdition() >= 15 && PermissionManager.validate( 'request', 'add_advanced' ) && ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) ) { //schedule adjustment || absence selected

			if ( this.edit_view_ui_dic.date_stamp ) {
				this.edit_view_ui_dic.date_stamp.parents( '.edit-view-form-item-div' ).hide();
			}
			if ( this.current_edit_record.date_stamp != undefined && this.current_edit_record.start_date == '' ) {
				this.edit_view_ui_dic.start_date.setValue( this.current_edit_record.date_stamp );
				this.current_edit_record.start_date = this.current_edit_record.date_stamp;
			}
			if ( this.current_edit_record.date_stamp != undefined && this.current_edit_record.end_date == '' ) {
				this.edit_view_ui_dic.end_date.setValue( this.current_edit_record.date_stamp );
				this.current_edit_record.end_date = this.current_edit_record.date_stamp;
			}
			this.showAdvancedFields( false ); //Don't update schedule total time as it will be done lower down in setRequestFormDefaultData();

			if ( this.edit_view_ui_dic.type_id.getValue() == 30 ) {
				this.edit_view_ui_dic.request_schedule_status_id.setValue( 20 );
			} else if ( this.edit_view_ui_dic.type_id.getValue() == 40 ) {
				this.edit_view_ui_dic.request_schedule_status_id.setValue( 10 );
			}

			if ( arg ) {
				arg.request_schedule_status_id = this.edit_view_ui_dic.request_schedule_status_id.getValue(); //arg is passed on setRequestFormDefaultData() below, so make sure it matches the UI here.
			}

			this.onWorkingStatusChanged();

			var $this = this;
			this.setRequestFormDefaultData( arg, function() {
				$this.getAvailableBalance();
				$this.setCurrentEditRecordData( $this.current_edit_record );
				$this.getScheduleTotalTime();
			} );
		} else {

			this.hideAdvancedFields();
			if ( this.edit_view_ui_dic.date_stamp ) {
				this.edit_view_ui_dic.date_stamp.parents( '.edit-view-form-item-div' ).show();
			}
			this.onWorkingStatusChanged();
		}
	}

	setRequestFormDefaultData( data, callback_function ) {
		if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'request', 'add_advanced' ) && ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) ) {
			if ( data == undefined ) {
				var $this = this;
				this.enable_validation = false;
				this.setUIWidgetFieldsToCurrentEditRecord();
				var filter = this.uniformVariable( this.buildDataForAPI( this.current_edit_record ) );

				this.api_request_schedule.getRequestScheduleDefaultData( filter, {
					onResult: function( res ) {

						data = res.getResult();
						data.request_schedule_status_id = data.status_id;
						data.date_stamp = data.start_date;

						//force = true is required to set the current_edit_record and populate edit_view_ui_dic
						$this.setDefaultData( data, true );
						if ( callback_function ) {
							callback_function();
						}
					}
				} );
			} else {
				data.date_stamp = data.start_date;
				data = this.buildDataFromAPI( data );
				//force = true is required to set the current_edit_record and populate edit_view_ui_dic
				this.setDefaultData( data, true );
				if ( callback_function ) {
					callback_function();
				}
			}
		}
	}

	onAvailableBalanceChange() {
		if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'request', 'add_advanced' ) ) {
			if ( this.edit_view_ui_dic && this.edit_view_ui_dic.absence_policy_id && this.edit_view_ui_dic.request_schedule_status_id.getValue() == 20 && this.edit_view_ui_dic.absence_policy_id.getValue() != TTUUID.zero_id ) {
				this.getAvailableBalance();
			} else if ( this.edit_view_ui_dic && this.edit_view_ui_dic.available_balance ) {
				this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
			}
		}
	}

	//post hook for onSaveResult
	onSaveDone( result ) {
		var retval;
		if ( this.is_edit ) {
			this.onViewClick( this.current_edit_record.id );
			retval = false;
		} else {
			retval = true;
		}
		$().TFeedback( {
			source: 'Send'
		} );
		//If request was added from the TimeSheet view, refresh the TimeSheet.
		if ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
			LocalCacheData.current_open_primary_controller.search();
		}
		return retval;
	}

	uniformVariable( records ) {
		if ( typeof records === 'object' ) {
			records.user_id = LocalCacheData.loginUser.id;
			records.first_name = LocalCacheData.loginUser.first_name;
			records.last_name = LocalCacheData.loginUser.last_name;
		}

		if ( this.is_add ) {
			records = this.buildDataForAPI( records );
		} else if ( this.is_edit ) {
			var msg = this.uniformMessageVariable( records );

			if ( records && records.request_schedule ) {
				msg.request_schedule = records.request_schedule;
			}

			return msg;
		}

		return records;
	}

	onSaveClick( ignoreWarning ) {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'save';
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}

		for ( var key in $this.current_edit_record ) {
			if ( $this.edit_view_ui_dic[key] != undefined ) {
				$this.current_edit_record[key] = $this.edit_view_ui_dic[key].getValue();
			}
		}

		if ( this.is_add ) {
			// //format data as expected by API
			record = this.uniformVariable( $this.current_edit_record );

			this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
				onResult: function( result ) {
					$this.onSaveResult( result );
				}
			} );
		} else if ( this.is_edit ) {
			var record = {};
			this.is_add = false;
			this.setCurrentEditRecordData();
			record = this.uniformVariable( this.current_edit_record );
			EmbeddedMessage.reply( [record], ignoreWarning, function( result ) {
					if ( result && result.isValid() ) {
						var id = $this.current_edit_record.id;
						//see #2224 - Unable to get property 'find' of undefined
						$this.removeEditView();
						$this.onViewClick( id );
					} else {
						$this.setErrorTips( result );
						$this.setErrorMenu();
					}
				}
			);
		}
	}

	search( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = null;
		super.search( set_default_menu, page_action, page_number, callBack );
	}

	setEditMenuEditIcon( context_btn ) {
		if ( !this.editPermissionValidate( 'request' ) || this.edit_only_mode || this.is_mass_editing ) {
			//Not shown in edit only mode or mass edit. Mass edit should only show mass edit (need to set that part in mass edit icon).
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

		if ( !this.is_viewing || !this.editOwnerOrChildPermissionValidate( 'request' ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuEditIcon( context_btn, grid_selected_length ) {
		if ( !this.editPermissionValidate( 'request' ) || this.edit_only_mode || ( !this.is_edit || !this.is_viewing ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuScheduleIcon( context_btn, grid_selected_length, pId ) {
		if ( !PermissionManager.checkTopLevelPermission( 'Schedule' ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuSendIcon( context_btn, grid_selected_length, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode || ( !this.is_edit || !this.is_viewing ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length ) {
		if ( !this.editChildPermissionValidate( 'user' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'timesheet':
			case 'schedule':
			case 'edit_employee':
				this.onNavigationClick( id );
				break;
			case 'send':
				this.onSaveClick();
				break;
		}
	}

	setEditViewWidgetsMode() {
		var did_clean = false;
		for ( var key in this.edit_view_ui_dic ) {
			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );
			var column = widget.parent().parent().parent();
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean ) {
					did_clean = true;
				}
			}
			if ( this.is_viewing ) {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( false );
				}
			} else {
				if ( Global.isSet( widget.setEnabled ) ) {
					widget.setEnabled( true );
				}
			}
		}
	}

	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.collectUIDataToCurrentEditRecord();
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;
		var needs_callback = false;

		switch ( key ) {
			case 'job_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ), 'job_item_id' );
					this.edit_view_ui_dic['job_quick_search'].setCheckBox( true );
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
			case 'job_item_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
			case 'punch_tag_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					if ( c_value !== TTUUID.zero_id && c_value !== false && c_value.length > 0 ) {
						this.setPunchTagQuickSearchManualIds( target.getSelectItems() );
					} else {
						this.edit_view_ui_dic['punch_tag_quick_search'].setValue( '' );
					}
					$this.previous_punch_tag_selection = c_value;
					//Reset source data to make sure correct punch tags are always shown.
					this.edit_view_ui_dic['punch_tag_id'].setSourceData( null );
				}
				break;
			case 'user_id':
			case 'branch_id':
			case 'department_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onJobQuickSearch( key, c_value, 'job_id', 'job_item_id' );
					TTPromise.wait( 'BaseViewController', 'onJobQuickSearch', function() {
						$this.setPunchTagValuesWhenCriteriaChanged( $this.getPunchTagFilterData(), 'punch_tag_id' );
					} );
				}
				break;
			case 'punch_tag_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onPunchTagQuickSearch( c_value, this.getPunchTagFilterData(), 'punch_tag_id' );

					//Don't validate immediately as onJobQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
			case 'type_id':
				// Set absence_policy_id to zero id to prevent available balance field showing when switching to a none absence request type.
				// Also prevents available balance sometimes showing the wrong value due to mismatch in select menu and current_edit_record.
				if ( this.edit_view_ui_dic.absence_policy_id && ( this.current_edit_record.type_id === 30 || this.current_edit_record.type_id === 40 ) ) {
					this.edit_view_ui_dic.absence_policy_id.setValue( TTUUID.zero_id );
					this.current_edit_record.absence_policy_id = TTUUID.zero_id;
				}
				doNotValidate = true;
				this.onTypeChanged();
				break;
			case 'overlap_type_id':
				if ( ( Global.getProductEdition() >= 15 ) ) {
					this.getOverlappingShifts();
				}
				break;
			case 'date_stamp':
				this.onDateStampChanged();
				break;
			case 'request_schedule_status_id':
				this.onWorkingStatusChanged();
				break;
			case 'start_date':
				this.current_edit_record.start_date = this.edit_view_ui_dic.start_date.getValue();
				this.current_edit_record.date_stamp = this.edit_view_ui_dic.start_date.getValue();
				this.onStartDateChanged();
				this.setRequestFormDefaultData( null, function() {
					finishFormItemChange();
					if ( ( Global.getProductEdition() >= 15 ) ) {
						$this.getOverlappingShifts();
					}
				} );
				needs_callback = true;
				break;
			case 'end_date':
				this.current_edit_record.end_date = this.edit_view_ui_dic.end_date.getValue();
				this.setRequestFormDefaultData( null, function() {
					finishFormItemChange();
					if ( ( Global.getProductEdition() >= 15 ) ) {
						$this.getOverlappingShifts();
					}
				} );
				needs_callback = true;
				break;
			case 'start_time':
			case 'end_time':
				if ( ( Global.getProductEdition() >= 15 ) ) {
					this.getOverlappingShifts();
				}
				break;
			case 'sun':
			case 'mon':
			case 'tue':
			case 'wed':
			case 'thu':
			case 'fri':
			case 'sat':
				this.getScheduleTotalTime();
				if ( ( Global.getProductEdition() >= 15 ) ) {
					this.getOverlappingShifts();
				}
				break;
			case 'schedule_policy_id':
				if ( ( Global.getProductEdition() >= 15 ) ) {
					$this.getOverlappingShifts();
				}
				break;
			case'absence_policy_id':
				this.selected_absence_policy_record = this.edit_view_ui_dic.absence_policy_id.getValue();
				break;
			case 'is_replace_with_open_shift':
				if ( ( Global.getProductEdition() >= 15 ) ) {
					this.getOverlappingShifts();
				}
				break;
		}

		if ( !needs_callback ) {
			finishFormItemChange();
		}

		function finishFormItemChange() {
			if ( key === 'date_stamp' ||
				key === 'start_date_stamps' ||
				key === 'start_date' ||
				key === 'end_date' ||
				key === 'start_date_stamp' ||
				key === 'start_time' ||
				key === 'end_time' ||
				key === 'schedule_policy_id' ||
				key === 'absence_policy_id' ) {

				if ( $this.current_edit_record['date_stamp'] !== '' &&
					$this.current_edit_record['start_time'] !== '' &&
					$this.current_edit_record['end_time'] !== '' ) {

					$this.getScheduleTotalTime();
				} else {
					$this.onAvailableBalanceChange();
				}

			}

			if ( !doNotValidate ) {
				$this.validate();
			}
			$this.setEditMenu();
		}
	}

	validate() {
		var $this = this;
		var record = this.current_edit_record;
		record = this.uniformVariable( record );
		var api = this.message_control_api;
		if ( this.is_add ) {
			record = this.buildDataForAPI( record );
			api = this.api;
		}

		api['validate' + api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );
			}
		} );
	}

	onAddClick( data ) {
		TTPromise.add( 'Request', 'add' );
		TTPromise.wait();
		var $this = this;
		if ( this.edit_view ) {
			this.removeEditView();
		}
		this.setCurrentEditViewState( 'new' );
		this.openEditView();
		this.buildAddViewUI();
		//Error: Uncaught TypeError: undefined is not a function in /interface/html5/views/BaseViewController.js?v=8.0.0-20141117-111140 line 897
		if ( $this.api && typeof $this.api['get' + $this.api.key_name + 'DefaultData'] === 'function' ) {
			$this.api['get' + $this.api.key_name + 'DefaultData']( {
				onResult: function( result ) {
					if ( data ) {
						//data passed should overwrite the default data from the API.
						result = $.extend( {}, result.getResult(), data );
					}
					$this.onAddResult( result );
					$this.onDateStampChanged();
					if ( result.type_id ) {
						$this.onTypeChanged( result );
					} else {
						$this.getScheduleTotalTime(); //This is called as part of onTypeChanged(), so it doesn't need to be done twice.
					}

					TTPromise.resolve( 'Request', 'add' );
				}
			} );
		}
	}

	//To be called only by external scripts creating requests (timesheet and schedule at this time)
	openAddView( data_array ) {
		this.sub_view_mode = true;
		this.edit_only_mode = true;
		var $this = this;
		this.initOptions( function() {
			$this.onAddClick( data_array );
		} );
	}
}