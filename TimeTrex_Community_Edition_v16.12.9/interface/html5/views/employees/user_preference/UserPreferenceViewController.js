export class UserPreferenceViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#user_preference_view_container',

			date_format_array: null,
			other_date_format_array: null,
			js_date_format_array: null,
			flex_date_format_array: null,
			jquery_date_format_array: null,
			time_format_array: null,
			js_time_format_array: null,
			flex_time_format_array: null,
			date_time_format_array: null,
			time_unit_format_array: null,
			distance_format_array: null,
			time_zone_array: null,
			location_timezone_array: null,
			area_code_timezone_array: null,
			timesheet_view_array: null,
			start_week_day_array: null,
			schedule_icalendar_type_array: null,
			language_array: null,
			user_group_array: null,
			country_array: null,
			province_array: null,

			notification_status_array: null,
			notification_type_array: null,
			priority_array: null,
			original_user_preference_notification_data: [],

			e_province_array: null,
			user_api: null,
			user_group_api: null,
			company_api: null,

			api_date: null,

			user_preference_notification_api: null
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'UserPreferenceEditView.html';
		this.permission_id = 'user_preference';
		this.viewId = 'UserPreference';
		this.script_name = 'UserPreferenceView';
		this.table_name_key = 'user_preference';
		this.context_menu_name = $.i18n._( 'Preferences' );
		this.navigation_label = $.i18n._( 'Employees' );
		this.api = TTAPI.APIUserPreference;
		this.api_date = TTAPI.APITTDate;
		this.user_api = TTAPI.APIUser;
		this.user_group_api = TTAPI.APIUserGroup;
		this.company_api = TTAPI.APICompany;
		this.user_preference_notification_api = TTAPI.APIUserPreferenceNotification;

		this.render();

		this.buildContextMenu();

		//call init data in parent view

		this.initData();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [
				'add',
				'copy',
				'copy_as_new',
				'save_and_copy',
				'save_and_new',
				'delete_icon',
				'delete_and_next'
			],
			include: []
		};

		return context_menu_model;
	}

	initOptions() {

		var $this = this;

		var options = [
			{ option_name: 'status', api: this.user_api },
			{ option_name: 'country', field_name: 'country', api: this.company_api },
			{ option_name: 'language', api: this.api },
			{ option_name: 'date_format', api: this.api },
			{ option_name: 'time_format', api: this.api },
			{ option_name: 'time_unit_format', api: this.api },
			{ option_name: 'distance_format', api: this.api },
			{ option_name: 'time_zone', api: this.api },
			{ option_name: 'start_week_day', api: this.api },
			{ option_name: 'schedule_icalendar_type', api: this.api },
			{ option_name: 'default_login_screen', api: this.api },
			{ option_name: 'notification_status', api: this.api },
			{ option_name: 'notification_type', api: this.user_preference_notification_api },
			{ option_name: 'priority', api: this.user_preference_notification_api },
		];

		this.initDropDownOptions( options );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();
				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;
				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
					$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );
	}

	initInsideEditorData() {
		var $this = this;

		var filter = {};
		filter.filter_data = {};
		filter.filter_data.user_id = this.current_edit_record.user_id;

		$this.user_preference_notification_api.getUserPreferenceNotification( filter, true, {
			onResult: function( res ) {

				var data = res.getResult();
				var array_data = [];
				for ( var key in data ) {

					if ( !data.hasOwnProperty( key ) ) {
						continue;
					}

					array_data.push( data[key] );
				}
				array_data = array_data.sort( function( a, b ) {
					return $this.notification_type_array.findIndex( p => p.id === a.type_id ) - $this.notification_type_array.findIndex( p => p.id === b.type_id );
				} );

				$this.original_user_preference_notification_data =  _.map(array_data, _.clone);

				$this.editor.setValue( array_data );

			}
		} );
	}

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );
					$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );

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

	onSetSearchFilterFinished() {

		if ( this.search_panel.getSelectTabIndex() === 1 ) {
			var combo = this.adv_search_field_ui_dic['country'];
			var select_value = combo.getValue();
			this.setProvince( select_value );
		}
	}

	onBuildAdvUIFinished() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	buildSearchFields() {

		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
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
				label: $.i18n._( 'First Name' ),
				in_column: 1,
				field: 'first_name',
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Last Name' ),
				in_column: 1,
				field: 'last_name',
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Employee Number' ),
				in_column: 1,
				field: 'employee_number',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
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
				label: $.i18n._( 'Default Branch' ),
				in_column: 2,
				field: 'default_branch_id',
				layout_name: 'global_branch',
				api_class: TTAPI.APIBranch,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				field: 'default_department_id',
				in_column: 2,
				layout_name: 'global_department',
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 2,
				layout_name: 'global_job_title',
				api_class: TTAPI.APIUserTitle,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Country' ),
				in_column: 3,
				field: 'country',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 3,
				field: 'province',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		if ( key === 'schedule_icalendar_type_id' ) {

			this.onStatusChange();
		} else if ( key === 'notification_status_id' ) {
			this.initSubNotificationView();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_preference': { 'label': $.i18n._( 'Preferences' ) },
			'tab_preferences_notification': {
				'label': $.i18n._( 'Notifications' ),
				'init_callback': 'initSubNotificationView',
				'html_template': this.getPreferencesNotificationTabHtml()
			},
			'tab_schedule_sync': {
				'label': $.i18n._( 'Schedule Synchronization' ),
				'init_callback': 'initSubScheduleSyncView',
				'html_template': this.getScheduleSyncronizationTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			api_class: TTAPI.APIUserPreference,
			allow_multiple_selection: false,
			layout_name: 'global_user_preference',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_preference = this.edit_view_tab.find( '#tab_preference' );

		var tab_preference_column1 = tab_preference.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_preference_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		if ( !this.is_mass_editing ) {
			// Employee
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'full_name' } );
			this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_preference_column1, '' );
		}

		// Language
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'language', set_empty: true } );
		form_item_input.setSourceData( $this.language_array );
		this.addEditFieldToColumn( $.i18n._( 'Language' ), form_item_input, tab_preference_column1 );

		// Date Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'date_format', set_empty: true } );
		form_item_input.setSourceData( $this.date_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Date Format' ), form_item_input, tab_preference_column1 );

		// Time Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_format', set_empty: true } );
		form_item_input.setSourceData( $this.time_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Time Format' ), form_item_input, tab_preference_column1 );

		// Time Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_unit_format', set_empty: true } );
		form_item_input.setSourceData( $this.time_unit_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Time Units' ), form_item_input, tab_preference_column1 );

		// Distance Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'distance_format', set_empty: true } );
		form_item_input.setSourceData( $this.distance_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Distance Units' ), form_item_input, tab_preference_column1 );

		// Time Zone
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_zone', set_empty: true } );
		form_item_input.setSourceData( $this.time_zone_array );
		this.addEditFieldToColumn( $.i18n._( 'Time Zone' ), form_item_input, tab_preference_column1 );

		// Start Weeks on
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'start_week_day' } );
		form_item_input.setSourceData( $this.start_week_day_array );
		this.addEditFieldToColumn( $.i18n._( 'Calendar Starts On' ), form_item_input, tab_preference_column1 );

		// Rows per page
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'items_per_page', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Rows per page' ), form_item_input, tab_preference_column1 );

		// Default Login Screen
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'default_login_screen' } );
		form_item_input.setSourceData( $this.default_login_screen_array );
		this.addEditFieldToColumn( $.i18n._( 'Default Screen' ), form_item_input, tab_preference_column1 );

		// Save TimeSheet State
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_save_timesheet_state' } );
		this.addEditFieldToColumn( $.i18n._( 'Save TimeSheet State' ), form_item_input, tab_preference_column1 );


		//Tab 1 start
		var tab_preferences_notification = this.edit_view_tab.find( '#tab_preferences_notification' );

		var tab_preferences_notification_column1 = tab_preferences_notification.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_preferences_notification_column1 );

		// Push Notifications Enabled
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'notification_status_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( $this.notification_status_array ) );
		this.addEditFieldToColumn( $.i18n._( 'Notification Status' ), form_item_input, tab_preferences_notification_column1 );

		// Notification duration
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'notification_duration', width: 50 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'Seconds (Use 0 for infinite)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Display Notifications For' ), form_item_input, tab_preferences_notification_column1, '', widgetContainer );

		if ( this.is_mass_editing == false ) {
			var inside_editor_div = tab_preferences_notification.find( '.inside-editor-div' );
			var args = {
				enabled: $.i18n._( 'Enabled' ),
				name: $.i18n._( 'Type' ),
				web: $.i18n._( 'Browser' ),
				email_work: $.i18n._( 'Work Email' ),
				email_home: $.i18n._( 'Home Email' ),
				app: $.i18n._( 'Mobile App' ),
				priority: $.i18n._( 'Priority' ),
				settings: $.i18n._( 'Settings' )
			};

			this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );

			this.editor.InsideEditor( {
				title: '',
				addRow: this.insideEditorAddRow,
				getValue: this.insideEditorGetValue,
				setValue: this.insideEditorSetValue,
				parent_controller: this,
				render: getRender(),
				render_args: args,
				render_inline_html: true,
				row_render: getRowRender()
			} );

			function getRender() {
				return `
			<table class="inside-editor-render">
				<tr class="title">
					<td style="width: 50px"><%= enabled %></td>
					<td style="width: 400px"><%= name %></td>
					<td style="width: 150px"><%= priority %></td>
					<td style="width: 110px"><%= web %></td>
					<td style="width: 110px"><%= email_work %></td>
					<td style="width: 110px"><%= email_home %></td>
					<td style="width: 110px"><%= app %></td>
					<td style="width: 200px"><%= settings %></td>
				</tr>
			</table>`;
			}

			function getRowRender() {
				return `
			<tr class="inside-editor-row data-row">
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
				<td class="cell"></td>
			</tr>`;
			}

			inside_editor_div.append( this.editor );
		}

		//Tab 2 start

		var tab_schedule_sync = this.edit_view_tab.find( '#tab_schedule_sync' );

		var tab_schedule_sync_column1 = tab_schedule_sync.find( '.first-column' );

		this.edit_view_tabs[2] = [];

		this.edit_view_tabs[2].push( tab_schedule_sync_column1 );

		//// schedule icalendar type
		//form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		//form_item_input.TComboBox( {field: 'schedule_icalendar_type_id' } );
		//form_item_input.setSourceData( $this.schedule_icalendar_type_array );
		//this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_schedule_sync_column1, 'first-last' );
		//
		//// Calendar URL
		//form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		//form_item_input.TText( {field: 'calendar_url' } );
		//form_item_input.addClass( 'link' );
		//this.addEditFieldToColumn( $.i18n._( 'Calendar URL' ), form_item_input, tab_schedule_sync_column1, '', null, true );

		// Shifts Scheduled to Work
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Shifts Scheduled to Work' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_sync_column1, '', null, true, false, 'shifts_scheduled_to_work' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm1_working', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm2_working', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Shifts Scheduled Absent

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Shifts Scheduled Absent' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_sync_column1, '', null, true, false, 'shifts_scheduled_absent' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm1_absence', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm2_absence', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Modified Shifts

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Modified Shifts' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_sync_column1, '', null, true, false, 'modified_shifts' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm1_modified', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm2_modified', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_sync_column1, '', widgetContainer, true );
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

				if ( !result_data ) {
					result_data = [];
				}

				$this.unique_columns = [];
				$this.linked_columns = [];

				$this.current_edit_record = result_data;
				$this.initEditView();

			}
		} );
	}

	onStatusChange() {

		if ( this.current_edit_record.schedule_icalendar_type_id == 0 ) {
			//this.edit_view_form_item_dic['calendar_url'].css( 'display', 'none' );
			this.detachElement( 'schedule_icalendar_alarm1_working' );
			this.detachElement( 'schedule_icalendar_alarm2_working' );
			this.detachElement( 'schedule_icalendar_alarm1_absence' );
			this.detachElement( 'schedule_icalendar_alarm2_absence' );
			this.detachElement( 'schedule_icalendar_alarm1_modified' );
			this.detachElement( 'schedule_icalendar_alarm2_modified' );
			this.detachElement( 'shifts_scheduled_to_work' );
			this.detachElement( 'shifts_scheduled_absent' );
			this.detachElement( 'modified_shifts' );

		} else {
			//this.setCalendarURL();
			//this.edit_view_form_item_dic['calendar_url'].css( 'display', 'block' );
			this.attachElement( 'schedule_icalendar_alarm1_working' );
			this.attachElement( 'schedule_icalendar_alarm2_working' );
			this.attachElement( 'schedule_icalendar_alarm1_absence' );
			this.attachElement( 'schedule_icalendar_alarm2_absence' );
			this.attachElement( 'schedule_icalendar_alarm1_modified' );
			this.attachElement( 'schedule_icalendar_alarm2_modified' );
			this.attachElement( 'shifts_scheduled_to_work' );
			this.attachElement( 'shifts_scheduled_absent' );
			this.attachElement( 'modified_shifts' );
		}

		this.editFieldResize();
	}

	setCurrentEditRecordData() {

		//Set current edit record data to all widgets

		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'full_name':
						widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						break;
//					case 'schedule_icalendar_alarm1_working':
//					case 'schedule_icalendar_alarm2_working':
//					case 'schedule_icalendar_alarm1_absence':
//					case 'schedule_icalendar_alarm2_absence':
//					case 'schedule_icalendar_alarm1_modified':
//					case 'schedule_icalendar_alarm2_modified':
//						var result = Global.getTimeUnit( this.current_edit_record[key] )
//						widget.setValue( result );
//						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();

		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onStatusChange();
		if ( this.is_mass_editing == false ) {
			this.initInsideEditorData();
		}
	}

	setCalendarURL( widget ) {

		if ( !Global.isSet( widget ) ) {
			widget = this.edit_view_ui_dic['calendar_url'];
		}

		if ( this.is_mass_editing ) {
			widget.setValue( 'Not available when mass editing' );
			return;
		}

		this.api['getScheduleIcalendarURL']( this.current_edit_record.user_name, this.current_edit_record.schedule_icalendar_type_id, {
			onResult: function( result ) {
				var result_data = result.getResult();
				widget.setValue( ServiceCaller.root_url + result_data, true );

				widget.unbind( 'click' ); // First unbind all click events, otherwise, when we change the schedule icalendar type this will trigger several times click events.

				widget.click( function() {
					window.open( widget.text() );
				} );

			}
		} );
	}

	initSubScheduleSyncView() {
		if ( Global.getProductEdition() >= 15 ) {
			this.edit_view_tab.find( '#tab_schedule_sync' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			this.buildContextMenu( true );
			this.setEditMenu();
		} else {
			this.edit_view_tab.find( '#tab_schedule_sync' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}
	}

	initSubNotificationView() {
		if ( this.current_edit_record.notification_status_id == 0 ) {
			this.edit_view_tab.find( '#tab_preferences_notification' ).find( '.inside-editor-div' ).css( 'display', 'none' );
			this.edit_view_ui_dic['notification_duration'].parent().parent().parent().css( 'display', 'none' );
		} else {
			this.edit_view_tab.find( '#tab_preferences_notification' ).find( '.inside-editor-div' ).css( 'display', 'block' );
			this.edit_view_ui_dic['notification_duration'].parent().parent().parent().css( 'display', 'block' );
		}
	}

	insideEditorSetValue( val ) {
		var len = val.length;

		if ( len === 0 ) {
			return;
		}

		this.removeAllRows();
		for ( var i = 0; i < val.length; i++ ) {
			if ( Global.isSet( val[i] ) ) {
				var row = val[i];
				//converting status_id and device_id into boolean values to be used for checkboxes
				row.enabled = row.status_id == 10;
				row.web_push_enabled = row.device_id.includes( 4 );
				row.email_work_enabled = row.device_id.includes( 256 );
				row.email_home_enabled = row.device_id.includes( 512 );
				row.app_push_enabled = row.device_id.includes( 32768 );
				if ( row.type_id.startsWith( 'reminder_' ) ) {
					row.reminder_delay = row.reminder_delay;
				}
				this.addRow( row );
			}
		}
	}

	insideEditorAddRow( data, index ) {
		if ( !data ) {
			data = {};
		}

		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets

		//Build row widgets

		//Enabled
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enabled' } );
		form_item_input.setValue( data.enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 0 ).append( form_item_input );
		this.setWidgetEnableBaseOnParentController( form_item_input );

		//Type
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( Global.addFirstItemToArray( this.parent_controller.notification_type_array ) );
		form_item_input.setValue( data.type_id );
		form_item_input.setEnabled( false );
		form_item_input.css( 'text-align-last', 'center' );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 1 ).append( form_item_input );

		//Priority
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'priority_id', set_empty: false } );
		form_item_input.setSourceData( this.parent_controller.priority_array );
		form_item_input.setValue( data.priority_id );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 2 ).append( form_item_input );

		//Web Push
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'web_push_enabled' } );
		form_item_input.setValue( data.web_push_enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 3 ).append( form_item_input );

		//Work Email
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'email_work_enabled' } );
		form_item_input.setValue( data.email_work_enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 4 ).append( form_item_input );

		//Home Email
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'email_home_enabled' } );
		form_item_input.setValue( data.email_home_enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 5 ).append( form_item_input );

		//App Push
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'app_push_enabled' } );
		form_item_input.setValue( data.app_push_enabled );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 6 ).append( form_item_input );

		//Settings
		if ( data.type_id.startsWith( 'reminder_' ) ) {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'reminder_delay', need_parser_sec: true, mode: 'time_unit' } );
			form_item_input.setValue( data.reminder_delay );
			widgets[form_item_input.getField()] = form_item_input;

			var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
			var label = $( '<span class=\'widget-left-label\'> ' + $.i18n._( 'Delay' ) + ': </span>' );

			widgetContainer.append( label );
			widgetContainer.append( form_item_input );

			row.children().eq( 7 ).append( widgetContainer );
		}

		//Save current set item
		widgets.current_edit_item = data;

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

		this.removeLastRowLine();
	}

	insideEditorGetValue( current_edit_item_id ) {

		var len = this.rows_widgets_array.length;

		var result = [];

		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			var data = row.current_edit_item;
			data.priority_id = row.priority_id.getValue();
			data.status_id = row.enabled.getValue() === true ? 10 : 20;
			data.device_id = [];
			if ( row.web_push_enabled.getValue() === true )
				data.device_id.push( 4 );
			if ( row.email_work_enabled.getValue() === true )
				data.device_id.push( 256 );
			if ( row.email_home_enabled.getValue() === true )
				data.device_id.push( 512 );
			if ( row.app_push_enabled.getValue() === true )
				data.device_id.push( 32768 );

			if ( data.type_id.startsWith( 'reminder_' ) && row.reminder_delay ) {
				data.reminder_delay = row.reminder_delay.getValue();
			}

			result.push( data );
		}

		return result;
	}

	onSaveResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}

			if ( this.is_mass_editing == false ) {
				$this.saveInsideEditorData( function() {
					var on_save_done_result = $this.onSaveDone( result );
					if ( on_save_done_result == undefined || on_save_done_result == true ) {
						$this.removeEditView();
					}
				} );
			} else {
				$this.removeEditView();
			}

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );
		}
	}

	saveInsideEditorData( callBack ) {
		var data = this.editor.getValue( this.refresh_id );

		let changed_data = this.getChangedRecords( data, this.original_user_preference_notification_data, ['enabled', 'web_push_enabled', 'email_work_enabled', 'email_home_enabled', 'app_push_enabled'] );

		if ( Array.isArray( changed_data ) && changed_data.length > 0 ) {
			this.user_preference_notification_api.setUserPreferenceNotification( changed_data, {
				onResult: function( res ) {
					if ( Global.isSet( callBack ) ) {
						callBack();
					}
				}
			} );
		} else {
			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}
	}

	onSaveDone( result ) {
		// #2224 - Don't use current_edit_record here, use getSelectedItem() instead, or it causes:
		// Uncaught Error From: UserPreferenceView Error: Unable to get property 'id' of undefined or null reference
		var user_id = this.getSelectedItem().id;

		if ( user_id === LocalCacheData.getLoginUser().id ) {
			Global.updateUserPreference();
		}
	}

	onSaveAndContinueDone( result ) {
		this.onSaveDone( result );
		this.initInsideEditorData();
	}

	onSaveAndNextDone( result ) {
		this.onSaveDone( result );
	}

	validate() {
		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}
				}
			}
			if ( this.mass_edit_record_ids.length > 0 ) {
				record.id = this.mass_edit_record_ids[0];
			}
		} else {
			record = this.current_edit_record;
		}
		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {

				$this.validateResult( result );

			}
		} );
	}

	getPreferencesNotificationTabHtml() {
		return `<div id="tab_preferences_notification" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_preferences_notification_content_div">
						<div class="first-column full-width-column"></div>
						<div class="inside-editor-div full-width-column"></div>
					</div>
				</div>`;
	}

	getScheduleSyncronizationTabHtml() {
		return `<div id="tab_schedule_sync" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_schedule_sync_content_div">
						<div class="first-column full-width-column"></div>
						<div class="save-and-continue-div permission-defined-div">
							<span class="message permission-message"></span>
						</div>
					</div>
				</div>`;
	}

	setSubLogViewFilter() {
		if ( !this.sub_log_view_controller ) {
			return false;
		}

		this.sub_log_view_controller.getSubViewFilter = function( filter ) {
			filter['table_name_object_id'] = {
				'user_preference': [this.parent_edit_record.id],
				'user_preference_notification': this.parent_view_controller.editor.getValue( this.refresh_id ).map( preference => preference.id )
			};

			return filter;
		};

		return true;
	}

}

UserPreferenceViewController.loadView = function() {
	Global.loadViewSource( 'UserPreference', 'UserPreferenceView.html', function( result ) {
		var args = {};
		//var template = _.template( result, args );

		Global.contentContainer().html( result );
	} );
};