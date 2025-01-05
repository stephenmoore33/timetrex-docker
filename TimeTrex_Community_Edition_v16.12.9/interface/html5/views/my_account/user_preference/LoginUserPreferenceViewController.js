import { Global } from '@/global/Global';

export class LoginUserPreferenceViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

			language_array: null,
			date_format_array: null,

			time_format_array: null,

			time_unit_format_array: null,

			distance_format_array: null,

			notification_status_array: null,

			notification_type_array: null,

			priority_array: null,
			original_user_preference_notification_data: [],

			time_zone_array: null,
			start_week_day_array: null,

			schedule_icalendar_type_array: null,

			saved_schedules: null,

			selected_schedule_uri_fragment: null,

			date_api: null,
			currentUser_api: null,

			user_preference_api: null,
			user_preference_notification_api: null
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );

		this.permission_id = 'user_preference';
		this.viewId = 'LoginUserPreference';
		this.script_name = 'LoginUserPreferenceView';
		this.table_name_key = 'user_preference';
		this.context_menu_name = $.i18n._( 'Preferences' );
		this.api = TTAPI.APIUserPreference;
		this.date_api = TTAPI.APITTDate;
		this.currentUser_api = TTAPI.APIAuthentication;
		this.user_preference_api = TTAPI.APIUserPreference;
		this.generic_user_data_api = TTAPI.APIUserGenericData;
		this.saved_schedules = [];
		this.selected_schedule_uri_fragment = '';
		this.user_preference_notification_api = TTAPI.APIUserPreferenceNotification;
		this.notification_api = TTAPI.APINotification;
		this.event_bus = new TTEventBus({ view_id: this.viewId });

		this.render();

		this.initData();
	}

	render() {
		super.render();
	}

	initOptions( callBack ) {

		var options = [
			{ option_name: 'language', field_name: null, api: this.api },
			{ option_name: 'date_format', field_name: null, api: this.api },
			{ option_name: 'time_format', field_name: null, api: this.api },
			{ option_name: 'time_unit_format', field_name: null, api: this.api },
			{ option_name: 'distance_format', field_name: null, api: this.api },
			{ option_name: 'time_zone', field_name: null, api: this.api },
			{ option_name: 'start_week_day', field_name: null, api: this.api },
			{ option_name: 'schedule_icalendar_type', field_name: null, api: this.api },
			{ option_name: 'default_login_screen', field_name: null, api: this.api },
			{ option_name: 'notification_status', field_name: null, api: this.api },
			{ option_name: 'notification_type', field_name: null, api: this.user_preference_notification_api },
			{ option_name: 'priority', field_name: null, api: this.user_preference_notification_api }
		];

		this.initDropDownOptions( options, function( result ) {
			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}
		} );
	}

	detectTimeZone() {
		if ( Intl ) {
			var detected_tz = Intl.DateTimeFormat().resolvedOptions().timeZone;

			if ( detected_tz && detected_tz.length > 0 ) {
				Debug.Text( 'Detected TimeZone: ' + detected_tz, 'UserPreferenceViewController.js', 'UserPreferenceViewController', 'detectTimeZone', 10 );
				this.current_edit_record['time_zone'] = detected_tz;

				var widget = this.edit_view_ui_dic['time_zone'];
				widget.setValue( this.current_edit_record['time_zone'] );
				widget.trigger( 'formItemChange', [widget] );
				this.is_changed = true;
				TAlertManager.showAlert( $.i18n._( 'Time Zone detected as' ) + ' ' + detected_tz );
			} else {
				TAlertManager.showAlert( $.i18n._( 'Unable to determine time zone' ) );
			}
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				'save',
				'cancel',
				{
					label: '', //Empty label. vue_icon is displayed instead of text.
					id: 'other_header',
					menu_align: 'right',
					action_group: 'other',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
				},
				{
					label: $.i18n._( 'Enable Browser Notifications' ),
					id: 'enable_browser_notifications',
					menu_align: 'right',
					action_group: 'other',
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Disable Browser Notifications' ),
					id: 'disable_browser_notifications',
					menu_align: 'right',
					action_group: 'other',
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Disable All Device Notifications' ),
					id: 'disable_all_notifications',
					menu_align: 'right',
					action_group: 'other',
					permission_result: true,
					permission: null
				},
				{
					label: $.i18n._( 'Send Test Notification' ),
					id: 'send_test_notification',
					menu_align: 'right',
					action_group: 'other',
					permission_result: true,
					permission: null
				},
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id, context_btn ) {
		switch ( id ) {
			case 'enable_browser_notifications':
				this.enableBrowserNotifications();
				break;
			case 'disable_browser_notifications':
				this.disableBrowserNotifications();
				break;
			case 'disable_all_notifications':
				this.disableAllNotifications();
				break;
			case 'send_test_notification':
				this.sendTestNotification();
				break;
		}
	}

	enableBrowserNotifications() {
		//If browser permissions are disabled, this will ask the user to enable permissions again. Otherwise it will refresh the service push notifications and send a new device token to the server.
		NotificationConsumer.setupUser( true, true );
	}

	disableBrowserNotifications() {
		//Delete the FCM device token from the server.
		NotificationConsumer.deleteToken();

		//Unregister the FCM service worker.
		if ( window.navigator && navigator.serviceWorker ) {
			navigator.serviceWorker.getRegistrations()
				.then( function( registrations ) {
					for ( let registration of registrations ) {
						if ( registration.active && registration.active.scriptURL && registration.active.scriptURL.includes( 'firebase-messaging-sw.js' ) ) {
							registration.unregister();
						}
					}
				} );
		}

		//Set a cookie so that FCM does not automatically create a new token and reregister a new service worker on the next page refresh.
		//This is because as soon as the browser accepted push notifications FCM will always set itself up unless we specifically opt out on our end.
		let notification_denied_cookie = NotificationConsumer.getNotificationDeniedCookie();
		notification_denied_cookie.asked_count = 2; //Set the cookie to max asked count so that notifications are disabled locally.
		NotificationConsumer.setNotificationDeniedCookie( notification_denied_cookie );

		TAlertManager.showAlert( $.i18n._( 'Push Notifications Disabled' ), $.i18n._( 'Push Notifications' ) );
	}

	sendTestNotification() {
		let $this = this;
		let data = {};
		data.user_id = LocalCacheData.getLoginUser().id;
		data.device_id = [4, 256, 512, 32768];
		data.type_id = 'system';
		data.title_short = $.i18n._( 'Test Notification' );
		data.body_short = $.i18n._( 'Sent at: '+ (new Date()).toLocaleTimeString() );
		data.priority = 2; //High priority.

		this.notification_api.sendNotification( data, {
			onResult: function( result ) {
				//Force badge count to be updated just in case its a non-production instance and notifications aren't actually received.
				$this.event_bus.emit( 'tt_topbar', 'profile_pending_counts', {
					object_types: []
				} );

				TAlertManager.showAlert( $.i18n._( 'Test Notification Sent To All Devices' ), $.i18n._( 'Push Notifications' ) );
			}
		} );
	}

	disableAllNotifications() {
		NotificationConsumer.deleteAllTokens();

		TAlertManager.showAlert( $.i18n._( 'Push Notifications Disabled For All Devices' ), $.i18n._( 'Push Notifications' ) );
	}

	getUserPreferenceData( callBack ) {
		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_data.user_id = LocalCacheData.loginUser.id;

		$this.api['get' + $this.api.key_name]( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();

				if ( Global.isArray( result_data ) && Global.isSet( result_data[0] ) ) {
					callBack( result_data[0] );
				} else {
					$this.api['get' + $this.api.key_name + 'DefaultData']( {
						onResult: function( newResult ) {
							var result_data = newResult.getResult();
							callBack( result_data );

						}
					} );
				}

			}
		} );
	}

	getSavedScheduleSearchAndLayout( callBack ) {
		var data = {};
		data.filter_data = { script: 'ScheduleView', deleted: false };

		this.generic_user_data_api.getUserGenericData( data, {
			onResult: function( result ) {
				var result_data = result.getResult();

				// Remove -- Default -- (multiple languages) option from list as it can change everytime a user searches and is not relevant.
				for ( var i = result_data.length - 1; i >= 0; i-- ) {
					if ( result_data[i].name === BaseViewController.default_layout_name ) {
						result_data.splice( i, 1 );
					}
				}

				callBack( result_data );
			}
		} );
	}

	initInsideEditorData() {
		var $this = this;

		var filter = {};
		filter.filter_data = {};
		filter.filter_data.user_id = LocalCacheData.loginUser.id;

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
				array_data = array_data.sort( ( a, b ) => {
					return $this.notification_type_array.findIndex( p => p.id === a.type_id ) - $this.notification_type_array.findIndex( p => p.id === b.type_id );
				} );

				$this.original_user_preference_notification_data =  _.map(array_data, _.clone);

				$this.editor.setValue( array_data );

			}
		} );
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
		this.initInsideEditorData();
	}

	openEditView() {
		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {

				$this.getSavedScheduleSearchAndLayout( function( result ) {

					$this.saved_schedules.push( { value: 0, label: $.i18n._( 'My Schedule (Default)' ) } );
					for ( var i = 0; i < result.length; i++ ) {
						$this.saved_schedules.push( { value: result[i].id, label: result[i].name } );
					}

					$this.getUserPreferenceData( function( result ) {

						// $this.buildContextMenu(); // Commenting out as this will now be done later in the buildEditViewUI stage, to allow the edit_view_tabs to be available for context menu build.

						if ( !$this.edit_view ) {
							$this.initEditViewUI( 'LoginUserPreference', 'LoginUserPreferenceEditView.html' );
						}

						if ( !result.id ) {
							result.first_name = LocalCacheData.loginUser.first_name;
							result.last_name = LocalCacheData.loginUser.last_name;
							result.user_id = LocalCacheData.loginUser.id;
						}

						// Waiting for the API returns data to set the current edit record.
						$this.current_edit_record = result;

						$this.initEditView();

					} );

				} );

			} );

		}
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		if ( key === 'schedule_icalendar_type_id' ) {
			this.onStatusChange();
			this.setCalendarURL();
		} else if ( key === 'schedule_saved_search' ) {
			this.onSavedScheduleChange( target.getValue(), target.getLabel() );
			this.setCalendarURL();
		} else if ( key === 'notification_status_id' ) {
			this.initSubNotificationView();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onStatusChange() {

		if ( this.current_edit_record.schedule_icalendar_type_id == 0 ) {
			this.detachElement( 'schedule_saved_search' );
			this.detachElement( 'calendar_url' );
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
			this.setCalendarURL();
			this.attachElement( 'schedule_saved_search' );
			this.attachElement( 'calendar_url' );
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

	onSavedScheduleChange( value, label ) {
		if ( value === 0 ) {
			this.selected_schedule_uri_fragment = '';
		} else {
			this.selected_schedule_uri_fragment = label;
		}
	}

	setCalendarURL( widget ) {

		if ( !Global.isSet( widget ) ) {
			widget = this.edit_view_ui_dic['calendar_url'];
		}

		this.api['getScheduleIcalendarURL']( this.current_edit_record.user_name, this.current_edit_record.schedule_icalendar_type_id, this.selected_schedule_uri_fragment, {
			onResult: function( result ) {
				var result_data = result.getResult();
				widget.setValue( ServiceCaller.root_url + result_data );

				widget.unbind( 'click' ); // First unbind all click events, otherwise, when we change the schedule icalendar type this will trigger several times click events.

				widget.click( function() {
					window.open( widget.text() );
				} );

			}
		} );
	}

	initSubScheduleSynchronizationView() {
		if ( Global.getProductEdition() >= 15 ) {
			this.edit_view_tab.find( '#tab_schedule_synchronization' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
			this.buildContextMenu( true );
			this.setEditMenu();
		} else {
			this.edit_view_tab.find( '#tab_schedule_synchronization' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}
	}

	initSubNotificationView() {
		if ( this.current_edit_record.notification_status_id == 0 ) {
			this.edit_view_tab.find( '#tab_preferences_notification' ).find( '.inside-editor-div' ).css( 'display', 'none' );
			this.edit_view_ui_dic['notification_duration'].parent().parent().parent().css( 'display', 'none' );

			this.edit_view.find( '#tab_preferences_notification' ).find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '#tab_preferences_notification' ).find( '.permission-message' ).text( $.i18n._( 'Enable notifications to edit these settings.' ) );
		} else {
			this.edit_view_tab.find( '#tab_preferences_notification' ).find( '.inside-editor-div' ).css( 'display', 'block' );
			this.edit_view_ui_dic['notification_duration'].parent().parent().parent().css( 'display', 'block' );

			this.edit_view.find( '#tab_preferences_notification' ).find( '.permission-defined-div' ).css( 'display', 'none' );
			this.edit_view.find( '#tab_preferences_notification' ).find( '.permission-message' ).text( $.i18n._( 'Enable notifications to edit these settings.' ) );
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
		form_item_input.setSourceData( this.parent_controller.notification_type_array );
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

			$this.saveInsideEditorData( function() {
				$this.onSaveDone( result );
			} );

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
		var $this = this;
		if ( result && result.isValid() ) {
			Global.setLanguageCookie( this.current_edit_record.language );
			LocalCacheData.setI18nDic( null );

			Global.updateUserPreference( function() {
				var current_edit_record_diff = Global.ArrayDiffAssoc( $this.old_current_edit_record, $this.current_edit_record ); //Must go before removeEditView() below.

				$this.removeEditView();

				//Only reload the entire page if certain fields changed that require it.
				if ( current_edit_record_diff['language'] || current_edit_record_diff['time_zone'] || current_edit_record_diff['date_format'] || current_edit_record_diff['time_format'] || current_edit_record_diff['time_unit_format'] | current_edit_record_diff['start_week_day']  ) {
					window.location.reload( true );
				}
			}, $.i18n._( 'Updating preferences, reloading' ) + '...' );

			return true;
		} else {
			return false;
		}
	}

	setEditMenuSaveIcon( context_btn, pId ) {
	}

	setErrorMenu() {

		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var len = context_menu_array.length;

		for ( var i = 0; i < len; i++ ) {
			let context_btn = context_menu_array[i];
			let id = context_menu_array[i].id;
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );

			switch ( id ) {
				case 'cancel':
					break;
				default:
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
					break;
			}

		}
	}

	buildEditViewUI() {
		var $this = this;
		super.buildEditViewUI();

		var tab_model = {
			'tab_preferences': { 'label': $.i18n._( 'Preferences' ) },
			'tab_preferences_notification': {
				'label': $.i18n._( 'Notifications' ),
				'init_callback': 'initSubNotificationView',
				'html_template': this.getPreferencesNotificationTabHtml()
			},
			'tab_schedule_synchronization': {
				'label': $.i18n._( 'Schedule Synchronization' ),
				'init_callback': 'initSubScheduleSynchronizationView',
				'html_template': this.getScheduleSyncronizationTabHtml()
			},
		};
		this.setTabModel( tab_model );

		var form_item_input;
		var widgetContainer;

		//Tab 0 start

		var tab_preferences = this.edit_view_tab.find( '#tab_preferences' );

		var tab_preferences_column1 = tab_preferences.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_preferences_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'full_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_preferences_column1, '' );

		// Language
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'language' } );
		form_item_input.setSourceData( $this.language_array );
		this.addEditFieldToColumn( $.i18n._( 'Language' ), form_item_input, tab_preferences_column1 );

		// Date Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'date_format' } );
		form_item_input.setSourceData( $this.date_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Date Format' ), form_item_input, tab_preferences_column1 );

		// Time Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_format' } );
		form_item_input.setSourceData( $this.time_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Time Format' ), form_item_input, tab_preferences_column1 );

		// Time Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_unit_format' } );
		form_item_input.setSourceData( $this.time_unit_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Time Units' ), form_item_input, tab_preferences_column1 );

		// Distance Units
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'distance_format' } );
		form_item_input.setSourceData( $this.distance_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Distance Units' ), form_item_input, tab_preferences_column1 );

		// Time Zone
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_zone', set_empty: true } );
		form_item_input.setSourceData( $this.time_zone_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var autodetect_tz_btn = $( '<input class=\'t-button\' style=\'margin-left: 5px; height: 25px;\' type=\'button\' value=\'' + $.i18n._( 'Auto-Detect' ) + '\'></input>' );
		autodetect_tz_btn.click( function() {
			$this.detectTimeZone();
		} );

		widgetContainer.append( form_item_input );
		widgetContainer.append( autodetect_tz_btn );
		this.addEditFieldToColumn( $.i18n._( 'Time Zone' ), form_item_input, tab_preferences_column1, '', widgetContainer );

		// Start Weeks on
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'start_week_day' } );
		form_item_input.setSourceData( $this.start_week_day_array );
		this.addEditFieldToColumn( $.i18n._( 'Calendar Starts On' ), form_item_input, tab_preferences_column1 );

		// Rows per page
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'items_per_page', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Rows per page' ), form_item_input, tab_preferences_column1 );

		// Default Login Screen
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'default_login_screen' } );
		form_item_input.setSourceData( $this.default_login_screen_array );
		this.addEditFieldToColumn( $.i18n._( 'Default Screen' ), form_item_input, tab_preferences_column1 );

		// Save TimeSheet State
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_save_timesheet_state' } );
		this.addEditFieldToColumn( $.i18n._( 'Save TimeSheet State' ), form_item_input, tab_preferences_column1 );


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

		var inside_editor_div = tab_preferences_notification.find( '.inside-editor-div' );
		var args = {
			enabled: $.i18n._( 'Enabled' ),
			name: $.i18n._( 'Type' ),
			priority: $.i18n._( 'Priority' ),
			web: $.i18n._( 'Browser' ),
			email_work: $.i18n._( 'Work Email' ),
			email_home: $.i18n._( 'Home Email' ),
			app: $.i18n._( 'Mobile App' ),
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

		//Tab 2 start

		var tab_schedule_synchronization = this.edit_view_tab.find( '#tab_schedule_synchronization' );

		var tab_schedule_synchronization_column1 = tab_schedule_synchronization.find( '.first-column' );

		this.edit_view_tabs[2] = [];

		this.edit_view_tabs[2].push( tab_schedule_synchronization_column1 );

		// Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'schedule_icalendar_type_id' } );
		form_item_input.setSourceData( $this.schedule_icalendar_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_schedule_synchronization_column1, '' );

		// Saved Schedule View
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'schedule_saved_search' } );
		form_item_input.setSourceData( $this.saved_schedules );
		this.addEditFieldToColumn( $.i18n._( 'Schedule Saved Search' ), form_item_input, tab_schedule_synchronization_column1, '', null, true, false, 'schedule_saved_search' );

		// Calendar URL
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'calendar_url' } );
		form_item_input.addClass( 'link' );

		var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var copy_icon = $( ' <img style="margin-left: 5px; cursor: pointer; width: 20px; height: 20px;" src="' + Global.getRealImagePath( 'css/global/widgets/ribbon/icons/' + 'copy-35x35.png' ) + '">' );
		copy_icon.click( function() {
			$this.copyCalendarUrl();
		} );

		widgetContainer.append( form_item_input );
		widgetContainer.append( copy_icon );
		this.addEditFieldToColumn( $.i18n._( 'Calendar URL' ), form_item_input, tab_schedule_synchronization_column1, '', widgetContainer, true );

		// Shifts Scheduled to Work
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Shifts Scheduled to Work' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_synchronization_column1, '', null, true, false, 'shifts_scheduled_to_work' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm1_working', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_synchronization_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm2_working', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_synchronization_column1, '', widgetContainer, true );

		// Shifts Scheduled Absent

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Shifts Scheduled Absent' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_synchronization_column1, '', null, true, false, 'shifts_scheduled_absent' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm1_absence', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_synchronization_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm2_absence', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_synchronization_column1, '', widgetContainer, true );

		// Modified Shifts

		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Modified Shifts' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_schedule_synchronization_column1, '', null, true, false, 'modified_shifts' );

		// Alarm 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm1_modified', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 1' ), form_item_input, tab_schedule_synchronization_column1, '', widgetContainer, true );

		// Alarm 2

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'schedule_icalendar_alarm2_modified', width: 90, need_parser_sec: true } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'before schedule start time' ) + ' )</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Alarm 2' ), form_item_input, tab_schedule_synchronization_column1, '', widgetContainer, true );
	}

	getPreferencesNotificationTabHtml() {
		//Style top: 200px; required so user can still use dropdown to enable/disable notifications. Otherwise divs overlap.
		var html_template = `<div id="tab_preferences_notification" class="edit-view-tab-outside">
								<div class="edit-view-tab" id="tab_preferences_notification_content_div">
									<div class="first-column full-width-column"></div>
									<div class="inside-editor-div full-width-column"></div>
									<div class="save-and-continue-div permission-defined-div" style="top: 200px">
										<span class="message permission-message"></span>
									</div>
								</div>
							</div>`;

		return html_template;
	}

	getScheduleSyncronizationTabHtml() {
		var html_template = `<div id="tab_schedule_synchronization" class="edit-view-tab-outside">
								<div class="edit-view-tab" id="tab_schedule_synchronization_content_div">
									<div class="first-column full-width-column"></div>
									<div class="save-and-continue-div permission-defined-div">
										<span class="message permission-message"></span>
									</div>
								</div>
							</div>`;

		return html_template;
	}

	copyCalendarUrl() {
		var calendar_url = this.edit_view_ui_dic['calendar_url'][0];

		// Create a temporary textarea to copy text from as cannot use select() on a span.
		var text_area = document.createElement( 'textarea' );
		text_area.value = calendar_url.textContent;
		document.body.appendChild( text_area );
		text_area.select();
		document.execCommand( 'Copy' );
		text_area.remove();

		TAlertManager.showAlert( $.i18n._( 'Calendar URL copied to clipboard.' ) );
	}

}
