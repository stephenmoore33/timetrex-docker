import { Global } from '@/global/Global';

export class RequestViewCommonController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			authorization_history: null,
			selected_absence_policy_record: null,
			enable_edit_view_ui: false
		} );

		super( options );
	}

	setGridCellBackGround() {
		//Error: Unable to get property 'getGridParam' of undefined or null reference
		if ( !this.grid ) {
			return;
		}

		var data = this.grid.getGridParam( 'data' );
		//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;
		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.status_id == 30 ) {
				$( 'tr#' + item.id ).addClass( 'bolder-request' );
			}
		}
	}

	onCancelClick( force, cancel_all, callback ) {
		TTPromise.add( 'base', 'onCancelClick' );
		var $this = this;

		//#2571 - Unable to get property 'id' of undefined or null reference
		if ( this.current_edit_record && this.current_edit_record.id ) {
			var $record_id = this.current_edit_record.id;
		}

		LocalCacheData.current_doing_context_action = 'cancel';
		if ( this.is_changed && !force ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {

				if ( flag === true ) {
					doNext();
				}

			} );
		} else {
			doNext();
		}

		function doNext() {
			if ( !$this.edit_view && $this.parent_view_controller && $this.sub_view_mode ) {
				$this.parent_view_controller.is_changed = false;
				$this.parent_view_controller.buildContextMenu( true );
				$this.parent_view_controller.onCancelClick();

			} else {
				if ( $this.is_edit && $record_id ) {
					ContextMenuManager.unmountContextMenu( $this.determineContextMenuMountAttributes().id );
					$this.setCurrentEditViewState( 'view' );
					$this.onViewClick( $record_id, true );
					$this.setEditMenu();
				} else {
					$this.removeEditView();
				}

			}
			if ( callback ) {
				callback();
			}

			$this.search( false ); //Refresh the grid, as we don't do that during authorize/decline clicks anymore.

			Global.setUIInitComplete();
			ProgressBar.closeOverlay();

			TTPromise.resolve( 'base', 'onCancelClick' );

		}

	}

	overlappingShiftUIValidate() {
		//Same permissions as APISchedule->getOverlappingShifts()
		if ( !PermissionManager.validate( 'schedule', 'enabled' )
			|| !( PermissionManager.validate( 'schedule', 'view' ) || PermissionManager.validate( 'schedule', 'view_own' ) || PermissionManager.validate( 'schedule', 'view_child' ) ) ) {
			return false;
		}

		return true;
	}

	onCloseIconClick() {
		this.onCancelClick();
	}

	buildDataForAPI( data ) {
		var afn = this.getAdvancedFieldNames();

		if ( this.viewId == 'RequestAuthorization' && ( !TTUUID.isUUID( data.request_schedule_id ) || data.request_schedule_id == TTUUID.zero_id ) ) {
			//Make sure we clear any advanced request fields so data from previous records doesn't mistakenly get carried over.
			afn.forEach( key => {
				if ( data.hasOwnProperty( key ) ) {
					delete data[key];
				}
			} );

			return data;
		}

		var user_id = LocalCacheData.loginUser.id;
		if ( Global.isSet( this.current_edit_record.user_id ) ) {
			user_id = this.current_edit_record.user_id;
		}
		var data_for_api = { 'user_id': user_id };
		var request_schedule = {};

		var request_schedule_keys = '';

		for ( var key in this.current_edit_record ) {
			if ( key == 'start_date' && this.edit_view_ui_dic[key] ) {
				data_for_api.date_stamp = this.edit_view_ui_dic[key].getValue();
			}

			if ( afn.indexOf( key ) > -1 ) {
				if ( key == 'request_schedule_id' ) {
					request_schedule['id'] = this.current_edit_record.request_schedule_id;
				} else if ( key == 'request_schedule_status_id' ) {
					//this case is for when asking for default data
					request_schedule['status_id'] = this.edit_view_ui_dic.request_schedule_status_id.getValue();
				} else if ( this.edit_view_ui_dic[key] ) {
					request_schedule[key] = this.edit_view_ui_dic[key].getValue();
				}
			} else if ( key == 'available_balance' || key == 'job_item_quick_search' || key == 'job_quick_search' ) {
				//ignore. these fields do not need to be saved and break the insert sql.
			} else {
				data_for_api[key] = this.current_edit_record[key];
			}
		}

		//There is a case where a regular employee has access to submit advanced requests, but the supervisor does not.
		// In that case we still need to allow advanced requests for authorization.
		if ( Global.getProductEdition() >= 15 && ( PermissionManager.validate( 'request', 'add_advanced' ) || Global.isEmpty( request_schedule ) == false ) && ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) ) {
			data_for_api.request_schedule = request_schedule;
		}
		return data_for_api;
	}

	buildDataFromAPI( data ) {
		if ( Global.isSet( data ) && Global.isSet( data.request_schedule ) ) {
			for ( var key in data.request_schedule ) {
				if ( key == 'id' ) {
					data['request_schedule_id'] = data.request_schedule.id;
				} else if ( key == 'status_id' ) {
					data['request_schedule_status_id'] = data.request_schedule.status_id;
				} else if ( typeof ( data[key] ) == 'undefined' ) {
					data[key] = data.request_schedule[key];
				} else {
					//Debug.Text('Not overwriting: '+key+' request_schedule: '+data.request_schedule[key]+' request: '+data[key], 'RequestViewCommonController.js', 'RequestViewCommonController','buildDataFromAPI' ,10)
				}

			}
			delete data.request_schedule;
			this.pre_request_schedule = false; //is this a request from before request schedule was added? we need to know if this is an "old version" request
		} else {
			this.pre_request_schedule = true;
		}

		//var retval = $.extend( this.current_edit_record, data ); //current_edit_record can only be data from the previous record at this point when switching between records. Why do we want to append new data to it rather than replace it entirely? We need to keep separation from records.
		var retval = data;

		return retval;
	}

	showAdvancedFields( update_schedule_total_time ) {
		if (
			Global.getProductEdition() >= 15 &&
			( PermissionManager.validate( 'request', 'add_advanced' )
				|| ( TTUUID.isUUID( this.current_edit_record.request_schedule_id ) && this.current_edit_record.request_schedule_id != TTUUID.zero_id && this.current_edit_record.request_schedule_id != TTUUID.not_exist_id ) )
			&& ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) && ( !this.pre_request_schedule || this.is_add )
		) {
			var advanced_field_names = this.getAdvancedFieldNames();
			if ( this.edit_view_ui_dic ) {
				for ( var i = 0; i < advanced_field_names.length; i++ ) {
					if ( advanced_field_names[i] == 'absence_policy_id' && this.edit_view_ui_dic.request_schedule_status_id && this.edit_view_ui_dic.request_schedule_status_id.getValue() != 20 ) {
						this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						continue;
					}
					if ( this.edit_view_ui_dic[advanced_field_names[i]] ) {
						if ( advanced_field_names[i] == 'branch_id' && !this.show_branch_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'department_id' && !this.show_department_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'job_id' && !this.show_job_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'job_item_id' && !this.show_job_item_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'punch_tag_id' && !this.show_punch_tag_ui ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else if ( advanced_field_names[i] == 'available_balance' && !this.is_viewing ) {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
						} else {
							this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).show();
						}
					}
				}

				if ( this.edit_view_ui_dic.date ) {
					this.edit_view_ui_dic.date_stamp.parents( '.edit-view-form-item-div' ).hide();
				}

				if ( this.edit_view_ui_dic.available_balance ) {
					if ( this.is_viewing == true && this.viewId == 'Request' ) {
						this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
					}
				}

				if ( this.current_edit_record.type_id != 30 && this.current_edit_record.type_id != 40 ) {
					if ( this.edit_view_ui_dic.total_time ) {
						this.edit_view_ui_dic.total_time.parents( '.edit-view-form-item-div' ).hide();
						this.edit_view_ui_dic.overlap_type_id.parents( '.edit-view-form-item-div' ).hide();
						this.edit_view_ui_dic.is_replace_with_open_shift.parents( '.edit-view-form-item-div' ).hide();
					}
				} else {
					if ( update_schedule_total_time != false ) {
						this.getScheduleTotalTime();
					}
				}
			}
		} else {
			if ( this.edit_view_ui_dic.date_stamp ) {
				this.edit_view_ui_dic.date_stamp.parents( '.edit-view-form-item-div' ).show();
			}
			this.hideAdvancedFields();
		}
	}

	hideAdvancedFields() {
		var advanced_field_names = this.getAdvancedFieldNames();
		if ( this.edit_view_ui_dic ) {
			if ( this.edit_view_ui_dic.request_schedule_status_id ) {
				this.edit_view_ui_dic.request_schedule_status_id.setValue( 10 ); //Force to 10=Working, so the absence policy field doesn't appear. Important for request authorization when switching from a advanced request to a basic request.
			}

			for ( var i = 0; i < advanced_field_names.length; i++ ) {
				if ( this.edit_view_ui_dic[advanced_field_names[i]] ) {
					this.edit_view_ui_dic[advanced_field_names[i]].parents( '.edit-view-form-item-div' ).hide();
				}
			}
			if ( this.edit_view_ui_dic.date ) {
				this.edit_view_ui_dic.date.parents( '.edit-view-form-item-div' ).show();
			}
		}
	}

	getAdvancedFieldNames() {
		return [
			'request_id',
			'request_schedule_status_id',
			'request_schedule_id',
			'start_date',
			'end_date',

			'sun',
			'mon',
			'tue',
			'wed',
			'thu',
			'fri',
			'sat',

			'start_time',
			'end_time',
			'total_time',

			'schedule_policy_id',
			'absence_policy_id',
			'branch_id',
			'department_id',
			'job_id',
			'job_item_id',
			'punch_tag_id',

			'schedule_policy',
			'absence_policy',
			'branch',
			'department',
			'job',
			'job_item',
			'available_balance',

			'overlap_type_id',
			'is_replace_with_open_shift'
		];
	}

	getScheduleTotalTime() {
		if ( Global.getProductEdition() >= 15
			&& ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 )
			&& ( this.edit_view_ui_dic && this.edit_view_ui_dic['total_time'] )
		) {

			var start_time = false;
			if ( this.current_edit_record['start_date'] && this.current_edit_record['start_time'] ) {
				start_time = this.current_edit_record['start_date'] + ' ' + this.current_edit_record['start_time'];
			}

			var end_time = false;
			if ( this.current_edit_record['start_date'] && this.current_edit_record['end_time'] ) {
				end_time = this.current_edit_record['start_date'] + ' ' + this.current_edit_record['end_time'];
			}

			var schedulePolicyId = ( this.current_edit_record['schedule_policy_id'] ) ? this.current_edit_record['schedule_policy_id'] : null;
			var user_id = this.current_edit_record.user_id;

			if ( typeof user_id == 'undefined' && LocalCacheData.getLoginUser().id ) {
				user_id = LocalCacheData.getLoginUser().id;
			}

			if ( start_time && end_time ) {
				var schedule_api = TTAPI.APISchedule;
				var result = schedule_api.getScheduleTotalTime( start_time, end_time, schedulePolicyId, user_id, { async: false } );
				if ( result && result.isValid() && result.getResult() ) {
					this.total_time = result.getResult();
				} else {
					this.total_time = 0;
				}

				var days = 1;
				if ( this.current_edit_record.start_date != this.current_edit_record.end_date ) {
					days = Global.getDaysInSpan( this.current_edit_record.start_date, this.current_edit_record.end_date, this.current_edit_record.sun, this.current_edit_record.mon, this.current_edit_record.tue, this.current_edit_record.wed, this.current_edit_record.thu, this.current_edit_record.fri, this.current_edit_record.sat );
				}

				var overall_total_time = this.total_time * days;
				$('#total_info').text( $.i18n._( 'x %s Day(s) = %s', days, Global.getTimeUnit( overall_total_time ) ) );

				this.current_edit_record['total_time'] = this.total_time;
				var total_time = Global.getTimeUnit( this.total_time );
				this.edit_view_ui_dic['total_time'].setValue( total_time );
				this.edit_view_ui_dic.total_time.parents( '.edit-view-form-item-div' ).show();
			} else {
				if ( this.edit_view_ui_dic.total_time ) {
					this.edit_view_ui_dic.total_time.parents( '.edit-view-form-item-div' ).hide();
				}
			}
		} else {
			if ( this.edit_view_ui_dic.total_time ) {
				this.edit_view_ui_dic.total_time.parents( '.edit-view-form-item-div' ).hide();
			}
		}

		this.onAvailableBalanceChange();
	}

	onWorkingStatusChanged() {
		if ( Global.getProductEdition() >= 15 ) {
			this.showAbsencePolicyField();
			this.showCreateOpenShift();
		}
	}

	showCreateOpenShift() {
		if ( PermissionManager.checkTopLevelPermission( 'RequestAuthorization' ) && this.current_edit_record && this.current_edit_record.request_schedule_status_id == 20 ) {
			this.attachElement( 'is_replace_with_open_shift' );
		} else {
			this.detachElement( 'is_replace_with_open_shift' );
		}
	}

	showAbsencePolicyField( type_id ) {
		if ( this.edit_view_ui_dic.request_schedule_status_id && this.edit_view_ui_dic.absence_policy_id ) {
			var type_id = this.edit_view_ui_dic.type_id ? this.edit_view_ui_dic.type_id.getValue() : this.current_edit_record.type_id;
			var request_schedule_status_id = this.edit_view_ui_dic.request_schedule_status_id.getValue()

			if ( request_schedule_status_id == 20 && ( type_id == 30 || type_id == 40 ) ) {
				this.edit_view_ui_dic.absence_policy_id.parents( '.edit-view-form-item-div' ).show();
				if ( ( this.viewId == 'Request' && this.is_viewing ) == false ) {
					this.onAvailableBalanceChange();
				}
			} else {
				this.edit_view_ui_dic.absence_policy_id.parents( '.edit-view-form-item-div' ).hide();
				this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
			}
		}
	}

	onDateStampChanged() {
		if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'request', 'add_advanced' ) ) {
			this.edit_view_ui_dic.start_date.setValue( this.current_edit_record.date_stamp );
			this.current_edit_record.start_date = this.current_edit_record.date_stamp;
		}
	}

	onStartDateChanged() {
		this.edit_view_ui_dic.date_stamp.setValue( this.current_edit_record.start_date );
		this.current_edit_record.date_stamp = this.current_edit_record.start_date;
	}

	getAvailableBalance() {
		if ( ( this.is_viewing && this.viewId == 'Request' ) || Global.isSet( this.current_edit_record ) == false ) {
			return;
		}

		if ( ( this.viewId != 'Request' || this.is_viewing == false ) &&
			this.current_edit_record.absence_policy_id &&
			( PermissionManager.validate( 'request', 'add_advanced' ) || ( TTUUID.isUUID( this.current_edit_record.request_schedule_id ) && this.current_edit_record.request_schedule_id != TTUUID.zero_id && this.current_edit_record.request_schedule_id != TTUUID.not_exist_id ) ) &&
			LocalCacheData.loginUser.id &&
			this.current_edit_record.total_time &&
			this.current_edit_record.total_time != 0 &&
			this.current_edit_record.start_date ) {

			var days = 1;
			if ( this.current_edit_record.start_date != this.current_edit_record.end_date ) {
				days = Global.getDaysInSpan( this.current_edit_record.start_date, this.current_edit_record.end_date, this.current_edit_record.sun, this.current_edit_record.mon, this.current_edit_record.tue, this.current_edit_record.wed, this.current_edit_record.thu, this.current_edit_record.fri, this.current_edit_record.sat );
			}

			var $this = this;
			var user_id = this.current_edit_record.user_id;
			var total_time = this.current_edit_record.total_time * days;
			var date_stamp = this.current_edit_record.date_stamp;
			var policy_id = this.current_edit_record.absence_policy_id ? this.current_edit_record.absence_policy_id : TTUUID.zero_id;

			if ( user_id && date_stamp && total_time ) {
				this.api_absence_policy.getProjectedAbsencePolicyBalance(
					policy_id,
					user_id,
					date_stamp,
					total_time,
					{
						onResult: function( result ) {
							if ( $this.edit_view_ui_dic && $this.edit_view_ui_dic.available_balance ) {
								$this.getBalanceHandler( result, date_stamp );
								if ( result && $this.selected_absence_policy_record ) {
									$this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).show();
								} else {
									$this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
								}
							}
						}
					}
				);
			}
			// If unset or set to --None--...
		} else if ( this.current_edit_record.absence_policy_id == false || this.current_edit_record.absence_policy_id == TTUUID.zero_id ) {
			if ( this.edit_view_ui_dic.available_balance ) {
				this.edit_view_ui_dic.available_balance.parents( '.edit-view-form-item-div' ).hide();
			}
		}
	}

	getFilterColumnsFromDisplayColumns( authorization_history ) {
		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( authorization_history ) {
			if ( this.authorization_history.authorization_history_grid ) {
				display_columns = AuthorizationHistory.getAuthorizationHistoryDefaultDisplayColumns();
			}
		} else {
			if ( this.grid ) {
				display_columns = this.grid.getGridParam( 'colModel' );
			}
		}
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.user_id = true;
		column_filter.status_id = true;

		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	}

	jobUIValidate() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( 'punch', 'edit_job' ) ) {
			return true;
		}
		return false;
	}

	jobItemUIValidate() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'punch', 'edit_job_item' ) ) {
			return true;
		}
		return false;
	}

	punchTagUIValidate() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'punch', 'edit_punch_tag' ) ) {
			return true;
		}
		return false;
	}

	branchUIValidate() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'punch', 'edit_branch' ) ) {
			return true;
		}
		return false;
	}

	departmentUIValidate() {
		//use punch permission section rather than schedule permission section as that's what they can see when they're creating punches
		if ( PermissionManager.validate( 'punch', 'edit_department' ) ) {
			return true;
		}
		return false;
	}

	processAPICallbackResult( result_data ) {
		this.current_edit_record = this.buildDataFromAPI( result_data[0] );
		if ( this.current_edit_record && this.current_edit_record.total_time ) {
			this.current_edit_record.total_time = Global.getTimeUnit( this.current_edit_record.total_time );
		}

		return result_data;
	}

	doViewClickResult( result_data ) {
		if ( Global.isSet( this.current_edit_record.start_date ) && this.edit_view_tab ) {
			this.edit_view_tab.find( '#tab_request' ).find( '.third-column' ).show();
		}

		this.initEditView();
		this.initViewingView();

		//This line is required to avoid problems with the absence policy box not showing properly on initial load.
		this.onWorkingStatusChanged();

		var $this = this;
		EmbeddedMessage.init( this.current_edit_record.id, 50, this, this.edit_view, this.edit_view_tab, this.edit_view_ui_dic, function() {
			$this.authorization_history = AuthorizationHistory.init( $this );
			$this.setEditMenu();
		} );
		return this.clearCurrentSelectedRecord();
	}

	onViewClick( edit_record, clear_edit_view ) {
		if ( clear_edit_view ) {
			this.clearEditView();
		}
		super.onViewClick( edit_record );
	}

	setSubLogViewFilter() {
		if ( !this.sub_log_view_controller ) {
			return false;
		}

		this.sub_log_view_controller.getSubViewFilter = function( filter ) {
			filter['table_name_object_id'] = {
				'request': [this.parent_edit_record.id],
				'request_schedule': [this.parent_edit_record.request_schedule_id]
			};

			return filter;
		};

		return true;
	}

	/**
	 * This function exists because the edit form is not actually an edit mode form, so we need to do some
	 * stuff differently in view mode than in edit mode.
	 */
	initViewingView() {
		this.showAdvancedFields();
	}

	initEditViewUI( view_id, edit_view_file_name ) {
		Global.setUINotready();
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();
		var $this = this;

		if ( this.edit_view ) {
			this.edit_view.remove();
		}

		this.edit_view = $( Global.loadViewSource( view_id, edit_view_file_name, null, true ) );
		this.edit_view_tab = $( this.edit_view.find( '.edit-view-tab-bar' ) );

		//Give edt view tab a id, so we can load it when put right click menu on it
		this.edit_view_tab.attr( 'id', this.ui_id + '_edit_view_tab' );

		// Moved into generic BaseView.initEditViewTabs
		// this.setTabOVisibility( false );
		// this.edit_view_tab = this.edit_view_tab.tabs( {
		// 	activate: function( e, ui ) {
		// 		$this.onTabShow( e, ui );
		// 	}
		// } );
		// Note: Check the 'tabsselect' replaced with generic 'click' in baseview does not cause issue.
		// this.edit_view_tab.bind( 'tabsselect', function( e, ui ) {
		// 	$this.onTabIndexChange( e, ui );
		// } );

		Global.contentContainer().append( this.edit_view );
		this.initRightClickMenu( RightClickMenuType.EDITVIEW );

		if ( this.is_viewing ) {
			LocalCacheData.current_doing_context_action = 'view';
			this.buildViewUI();
		} else if ( this.is_edit ) {
			LocalCacheData.current_doing_context_action = 'edit';
			this.buildEditViewUI();
			ContextMenuManager.unmountContextMenu( this.determineContextMenuMountAttributes().id );
			this.buildContextMenu( true );
		}

		$this.setEditViewTabHeight();
	}

	initEditViewTabs() {
		var $this = this;
		var tab_options = {
			activate: function( e, ui ) {
				$this.onTabShow( e, ui );
			}
		};

		super.initEditViewTabs( tab_options );
	}

	onEditClick( editId, noRefreshUI ) {
		this.setCurrentEditViewState( 'edit' );
		this.initEditViewUI( this.viewId, this.edit_view_tpl );
		this.initEditView();
		//Clear last sent message body value.
		this.edit_view_ui_dic.body.setValue( '' );
		//ensure send button is available
		this.setEditMenu();
	}

	buildViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_request': {
				'label': $.i18n._( 'Request' ),
				'html_template': this.getRequestTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIRequest,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_request',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		var form_item_input;
		var widgetContainer;
		var label;

		//Tab 0 first column start
		var tab_request = this.edit_view_tab.find( '#tab_request' );
		var tab_request_column1 = tab_request.find( '.first-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_request_column1 );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'full_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_request_column1 );

		// Type
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'type', set_empty: false } );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_request_column1 );

		// Date
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_request_column1 );

		if ( Global.getProductEdition() >= 15 ) {

			//Working Status
			var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'request_schedule_status_id', set_empty: false } );
			form_item_input.setSourceData( { 10: 'Working', 20: 'Absent' } );
			this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_request_column1 );
			form_item_input.bind( 'change', function( e ) {
				$this.onWorkingStatusChanged();
			} );

			//Absence Policy
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIAbsencePolicy,
				allow_multiple_selection: false,
				layout_name: 'global_absences',
				set_empty: true,
				field: 'absence_policy_id',
				customSearchFilter: function( filter ) {
					return $this.setAbsencePolicyFilter( filter );
				},
				setRealValueCallBack: function( value ) {
					// #2135 fix for cases where user is removed from absence policies between creating request and approval
					$this.selected_absence_policy_record = value;
					$this.onAvailableBalanceChange();
				}
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

				form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
				form_item_input.TCheckbox( { field: 'is_replace_with_open_shift' } );

				widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
				label = $( '<span class=\'widget-right-label\'>( ' + $.i18n._( 'For Absences' ) + ' )</span>' );
				widgetContainer.append( form_item_input );
				widgetContainer.append( label );

				this.addEditFieldToColumn( $.i18n._( 'Create Open Shift' ), form_item_input, tab_request_column1, '', widgetContainer, true );

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

			//Branch
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIBranch,
				allow_multiple_selection: false,
				layout_name: 'global_branch',
				show_search_inputs: true,
				set_empty: true,
				field: 'branch_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_request_column1 );
			if ( !this.show_branch_ui ) {
				this.detachElement( 'branch_id' );
			}

			//Department
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIDepartment,
				allow_multiple_selection: false,
				layout_name: 'global_department',
				show_search_inputs: true,
				set_empty: true,
				field: 'department_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_request_column1 );
			if ( !this.show_department_ui ) {
				this.detachElement( 'department_id' );
			}

			if ( Global.getProductEdition() >= 20 ) {
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
				this.addEditFieldToColumn( $.i18n._( 'Punch Tags' ), [form_item_input, punch_tag_coder], tab_request_column1, '', widgetContainer, true );

				if ( !this.show_punch_tag_ui ) {
					this.detachElement( 'punch_tag_id' );
				}
			}
		}

		EmbeddedMessage.initUI( this, tab_request );
	}

	setAbsencePolicyFilter( filter ) {
		if ( !filter.filter_data ) {
			filter.filter_data = {};
		}
		filter.filter_data.user_id = this.current_edit_record.user_id;

		if ( filter.filter_columns ) {
			filter.filter_columns.absence_policy = true;
		}
		return filter;
	}

	needShowNavigation() {
		if ( this.is_viewing && this.current_edit_record && Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
			return true;
		} else {
			return false;
		}
	}

	onNavigationClick( iconName ) {

		var $this = this;
		var filter;
		var temp_filter;
		var grid_selected_id_array;
		var grid_selected_length;

		var selectedId;
		/* jshint ignore:start */
		switch ( iconName ) {
			case 'timesheet':
				filter = { filter_data: {} };
				if ( Global.isSet( this.current_edit_record ) ) {

					filter.user_id = this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id;
					filter.base_date = this.current_edit_record.date_stamp;

					Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - Request' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );

				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = { user_id: true, date_stamp: true };
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function( result ) {
								var result_data = result.getResult();

								if ( !result_data ) {
									result_data = [];
								}

								result_data = result_data[0];

								filter.user_id = result_data.user_id;
								filter.base_date = result_data.date_stamp;
								Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - Request' ), window.location.href );
								IndexViewController.goToView( 'TimeSheet', filter );

							}
						} );
					}

				}

				break;

			case 'edit_employee':
				filter = { filter_data: {} };
				if ( Global.isSet( this.current_edit_record ) ) {
					IndexViewController.openEditView( this, 'Employee', this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = { user_id: true };
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function( result ) {
								var result_data = result.getResult();

								if ( !result_data ) {
									result_data = [];
								}

								result_data = result_data[0];

								IndexViewController.openEditView( $this, 'Employee', result_data.user_id );

							}
						} );
					}

				}
				break;
			case 'schedule':

				filter = { filter_data: {} };

				var include_users = null;

				if ( Global.isSet( this.current_edit_record ) ) {

					include_users = [this.current_edit_record.user_id ? this.current_edit_record.user_id : LocalCacheData.loginUser.id];
					filter.filter_data.include_user_ids = { value: include_users };
					filter.select_date = this.current_edit_record.date_stamp;

					Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - Request' ), window.location.href );
					IndexViewController.goToView( 'Schedule', filter );

				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_columns = { user_id: true, date_stamp: true };
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function( result ) {
								var result_data = result.getResult();

								if ( !result_data ) {
									result_data = [];
								}

								result_data = result_data[0];

								include_users = [result_data.user_id];

								filter.filter_data.include_user_ids = include_users;
								filter.select_date = result_data.date_stamp;

								Global.addViewTab( $this.viewId, $.i18n._( 'Authorization - Request' ), window.location.href );
								IndexViewController.goToView( 'Schedule', filter );

							}
						} );
					}

				}
				break;
		}

		/* jshint ignore:end */
	}

	initPermission() {
		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}

		if ( this.jobUIValidate() ) {
			this.show_job_ui = true;
		} else {
			this.show_job_ui = false;
		}

		if ( this.jobItemUIValidate() ) {
			this.show_job_item_ui = true;
		} else {
			this.show_job_item_ui = false;
		}

		if ( this.punchTagUIValidate() ) {
			this.show_punch_tag_ui = true;
		} else {
			this.show_punch_tag_ui = false;
		}

		if ( this.branchUIValidate() ) {
			this.show_branch_ui = true;
		} else {
			this.show_branch_ui = false;
		}

		if ( this.departmentUIValidate() ) {
			this.show_department_ui = true;
		} else {
			this.show_department_ui = false;
		}

		// Error: Uncaught TypeError: (intermediate value).isBranchAndDepartmentAndJobAndJobItemAndPunchTagEnabled is not a function on line 207
		var company_api = TTAPI.APICompany;
		if ( company_api && _.isFunction( company_api.isBranchAndDepartmentAndJobAndJobItemAndPunchTagEnabled ) ) {
			var result = company_api.isBranchAndDepartmentAndJobAndJobItemAndPunchTagEnabled( { async: false } );
		}

		if ( !result ) {
			this.show_branch_ui = false;
			this.show_department_ui = false;
			this.show_job_ui = false;
			this.show_job_item_ui = false;
			this.show_punch_tag_ui = false;
		} else {
			result = result.getResult();
			if ( !result.branch ) {
				this.show_branch_ui = false;
			}

			if ( !result.department ) {
				this.show_department_ui = false;
			}

			if ( !result.job ) {
				this.show_job_ui = false;
			}

			if ( !result.job_item ) {
				this.show_job_item_ui = false;
			}

			if ( !result.punch_tag ) {
				this.show_punch_tag_ui = false;
			}
		}

	}

	setEditMenuEditIcon( context_btn ) {
		if ( !this.editPermissionValidate( 'request' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		//If edit_child is FALSE and this is a child record, inputs should be read-only.
		if ( this.editOwnerOrChildPermissionValidate( 'request' ) ) {
			this.enable_edit_view_ui = true;
		} else {
			this.enable_edit_view_ui = false;
		}

		if ( !this.editOwnerOrChildPermissionValidate( 'request' ) || this.is_add || this.is_edit ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	getOverlappingShifts() {
		let $this = this;

		if ( !this.current_edit_record || !this.current_edit_record.type_id ) {
			return;
		}

		//Do not get overlapping shifts if user does not have schedule permissions, is on a lower product edition or request is not a schedule change.
		if ( this.overlappingShiftUIValidate() == false || ( Global.getProductEdition() < 15 ) || ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) == false ) {
			return;
		}

		let data = {};
		data.start_date = this.current_edit_record.start_date;
		data.end_date = this.current_edit_record.end_date;
		data.user_id = this.current_edit_record.user_id;
		data.start_time = this.current_edit_record.start_time;
		data.end_time = this.current_edit_record.end_time;

		data.overlap_type_id = this.current_edit_record.overlap_type_id;

		data.schedule_policy_id = this.current_edit_record.schedule_policy_id;
		data.absence_policy_id = this.current_edit_record.absence_policy_id;
		data.request_schedule_status_id = this.current_edit_record.request_schedule_status_id;

		data.requested_days = {};

		data.requested_days[0] = this.current_edit_record.sun;
		data.requested_days[1] = this.current_edit_record.mon;
		data.requested_days[2] = this.current_edit_record.tue;
		data.requested_days[3] = this.current_edit_record.wed;
		data.requested_days[4] = this.current_edit_record.thu;
		data.requested_days[5] = this.current_edit_record.fri;
		data.requested_days[6] = this.current_edit_record.sat;

		data.is_replace_with_open_shift = this.current_edit_record.is_replace_with_open_shift;

		this.schedule_api.getOverlappingShifts( data, {
			onResult: function( result ) {
				let result_data = result.getResult();

				if ( !result_data || !result_data.split ) {
					return;
				}

				$this.overlapping_shift_data = result_data;

				$this.buildOverlappingShiftInfo();
			}
		} );
	}

	buildOverlappingShiftInfo() {
		let $this = this;

		if ( !this.current_edit_record ) {
			//Issue #3216 - Error: Uncaught TypeError: Cannot read properties of null (reading 'overlap_type_id')
			//This could happen under race conditions or opening a request and then quickly closing it when experiencing latency.
			return;
		}

		let overlapping_shifts_html = '';
		let split_shifts = this.overlapping_shift_data['split'];
		let shifts_after = 0;
		let shifts_before = 0;
		let shifts_after_total_time = 0;
		let shifts_before_total_time = 0;

		let suggested_overlap_type_id = 10; //Replace

		shifts_after = this.overlapping_shift_data['no_overlap'].length;
		shifts_after_total_time += this.overlapping_shift_data['no_overlap'].reduce( ( a, b ) => parseFloat( a ) + parseFloat( b.total_time ), 0 );
		if ( this.current_edit_record.overlap_type_id == 10 ) { //Replace
			shifts_after += this.overlapping_shift_data['open_replaced'].length;
			shifts_after_total_time += this.overlapping_shift_data['open_replaced'].reduce( ( a, b ) => parseFloat( a ) + parseFloat( b.total_time ), 0 );
		} else if ( this.current_edit_record.overlap_type_id == 20 ) { //Split
			shifts_after += split_shifts.length;
			shifts_after_total_time += split_shifts.reduce( ( a, b ) => a + b.total_time, 0 );
		}
		if ( this.current_edit_record.is_replace_with_open_shift == true ) {
			shifts_after += this.overlapping_shift_data['open_replaced'].length;
			shifts_after_total_time += this.overlapping_shift_data['open_replaced'].reduce( ( a, b ) => parseFloat( a ) + parseFloat( b.total_time ), 0 );
		}
		shifts_before = this.overlapping_shift_data['original'].filter( shift => !shift.not_unique && !shift.do_not_split ).length;
		shifts_before_total_time = this.overlapping_shift_data['original'].filter( shift => !shift.not_unique && !shift.do_not_split ).reduce( ( a, b ) => parseFloat( a ) + parseFloat( b.total_time ), 0 );
		$( '#overlapping-shift-total' ).text( '( ' + shifts_before + ' / ' + shifts_after + ' )' );
		//Group modified shifts by parent (specific starting shift)
		if ( Array.isArray( split_shifts ) ) {
			let shift_data_map = {};
			for ( let i = 0; i < split_shifts.length; i++ ) {
				if ( split_shifts[i].split_state === 'no_split' ) {
					//No change to this shift.
					continue;
				}
				if ( !shift_data_map[split_shifts[i].split_parent] ) {
					shift_data_map[split_shifts[i].split_parent] = [];
				}
				shift_data_map[split_shifts[i].split_parent].push( split_shifts[i] );

				//Check if split overlaps < 50% of parent (original) shift. Only check if split is not already suggested.
				if ( suggested_overlap_type_id == 10 ) { //Replace
					let parent_shift = this.overlapping_shift_data['original'].find( shift => shift.id === split_shifts[i].split_parent );
					if ( parent_shift ) {
						let split_shift_percent = ( parseFloat( split_shifts[i].total_time ) / parseFloat( parent_shift.total_time ) ) * 100;
						if ( split_shift_percent < 50 ) {
							suggested_overlap_type_id = 20; //Split
						}
					}
				}
			}

			overlapping_shifts_html += '<table class="overlapping-info-table">';

			if ( this.current_edit_record.overlap_type_id != suggested_overlap_type_id ) {
				let suggested_overlap_label = suggested_overlap_type_id == 10 ? $.i18n._( 'Replace' ) : $.i18n._( 'Split' );
				overlapping_shifts_html += '<tr><th colspan="3" style="text-align: center; color: red;">' + $.i18n._( 'Suggested Overlapping Shift(s) Mode' ) + ': <strong>' + suggested_overlap_label + '</strong></th></tr>';
				//Make the overlapping icon red if the suggested overlap type is different from the current selected overlap type.
				//This is to draw the users' attention.
				$( '#overlapping-shift-icon' ).css( 'color', 'red' );
			} else {
				$( '#overlapping-shift-icon' ).css( 'color', '' );
			}

			if ( this.current_edit_record.overlap_type_id == 20 && split_shifts.length > 0 ) {
				overlapping_shifts_html += '<th colspan="3" style="text-align: center">' + $.i18n._( 'Overlapping Shifts' ) + '</th></tr>';
			} else if ( this.current_edit_record.overlap_type_id == 10 && this.overlapping_shift_data['open_replaced'].length > 0 ) {
				overlapping_shifts_html += '<tr><th colspan="3" style="text-align: center">' + $.i18n._( 'Replaced Shifts' ) + '</th></tr>';
			}

			if ( _.size( shift_data_map ) > 0 ) {
				overlapping_shifts_html += this.getOverlappingShiftHeaderHtml();
			}

			for ( let shift_data in shift_data_map ) {
				let parent_data = this.overlapping_shift_data['original'].find( shift => shift.id === shift_data_map[shift_data][0].split_parent );
				overlapping_shifts_html += '<tr>';
				overlapping_shifts_html += '<td>' + parent_data.start_date + '</td>';
				overlapping_shifts_html += '<td>' + parent_data.start_time + ' - ' + parent_data.end_time + ' = ' + Global.getTimeUnit( parent_data.total_time ) + '</td>';

				let table_row_break = false;
				for ( let i = 0; i < shift_data_map[shift_data].length; i++ ) {
					if ( this.current_edit_record.overlap_type_id == 20 || ( shift_data_map[shift_data][i].split_state === 'new' || shift_data_map[shift_data][i].split_state === 'replaced' ) ) { //Split
						if ( table_row_break === true ) {
							overlapping_shifts_html += '</tr>';
							overlapping_shifts_html += '<tr>';
							overlapping_shifts_html += '<td>' + shift_data_map[shift_data][i].start_date + '</td>';
							overlapping_shifts_html += '<td></td>';
						}
						let modified_label = shift_data_map[shift_data][i].split_state === 'new' ? $.i18n._( 'New' ) : $.i18n._( 'Modified' );
						let cell_class = shift_data_map[shift_data][i].is_absence ? 'overlap-absence' : '';
						overlapping_shifts_html += '<td class ="' + cell_class + '">' + shift_data_map[shift_data][i].start_time + ' - ' + shift_data_map[shift_data][i].end_time + ' = ' + Global.getTimeUnit( shift_data_map[shift_data][i].total_time ) + ' [' + modified_label + '] </td>';
						if ( table_row_break === true ) {
							overlapping_shifts_html += '</tr>';
						}
						table_row_break = true;
					}
				}
				overlapping_shifts_html += '</tr>';
			}

			if ( Array.isArray( this.overlapping_shift_data['no_overlap'] ) && this.overlapping_shift_data['no_overlap'].length > 0 ) {
				overlapping_shifts_html += '<tr style="border-bottom: 15px solid transparent;"></tr>'; //blank row for spacing
				overlapping_shifts_html += '<tr><th colspan="3" style="text-align: center">' + $.i18n._( 'New Shifts (Not Overlapping)' ) + '</th></tr>';

				if ( _.size( shift_data_map ) === 0 ) {
					overlapping_shifts_html += this.getOverlappingShiftHeaderHtml();
				}

				for ( let i = 0; i < this.overlapping_shift_data['no_overlap'].length; i++ ) {
					let tr_class = this.current_edit_record.request_schedule_status_id == 20 ? 'overlap-absence' : '';
					overlapping_shifts_html += '<tr class="' + tr_class + '">';
					overlapping_shifts_html += '<td>' + this.overlapping_shift_data['no_overlap'][i].start_date + '</td>';
					overlapping_shifts_html += '<td></td>';
					overlapping_shifts_html += '<td>' + this.overlapping_shift_data['no_overlap'][i].start_time + ' - ' + this.overlapping_shift_data['no_overlap'][i].end_time + ' = ' + Global.getTimeUnit( this.overlapping_shift_data['no_overlap'][i].total_time ) + '</td>';
					overlapping_shifts_html += '<tr>';
				}
			}

			if ( this.current_edit_record.is_replace_with_open_shift == true && Array.isArray( this.overlapping_shift_data['open_replaced'] ) && this.overlapping_shift_data['open_replaced'].length > 0 ) {
				overlapping_shifts_html += '<tr style="border-bottom: 15px solid transparent;"></tr>'; //blank row for spacing
				overlapping_shifts_html += '<tr><th colspan="3" style="text-align: center">' + $.i18n._( 'New Open Shifts' ) + '</th></tr>';
				for ( let i = 0; i < this.overlapping_shift_data['open_replaced'].length; i++ ) {
					overlapping_shifts_html += '<tr>';
					overlapping_shifts_html += '<td>' + this.overlapping_shift_data['open_replaced'][i].start_date + '</td>';
					overlapping_shifts_html += '<td></td>';
					overlapping_shifts_html += '<td>' + this.overlapping_shift_data['open_replaced'][i].start_time + ' - ' + this.overlapping_shift_data['open_replaced'][i].end_time + ' = ' + Global.getTimeUnit( this.overlapping_shift_data['open_replaced'][i].total_time ) + '</td>';
					overlapping_shifts_html += '<tr>';
				}
			}

			if ( this.overlapping_shift_data['original'].length > 0 || this.overlapping_shift_data['no_overlap'].length > 0 ) {
				overlapping_shifts_html += '<tr style="border-bottom: 15px solid transparent;"></tr>'; //blank row for spacing
				overlapping_shifts_html += '<tr style="text-align: center"> <th>' + $.i18n._( 'Total Shifts' ) + '</th><th>' + shifts_before + ' = ' + Global.getTimeUnit( shifts_before_total_time ) + '</th> <th>' + shifts_after + ' = ' + Global.getTimeUnit( shifts_after_total_time ) + '</th></tr>';
			} else {
				overlapping_shifts_html += '<tr><th colspan="3" style="text-align: center">' + $.i18n._( 'No New or Overlapping Shifts' ) + '</th>' + overlapping_shifts_html + '</tr>';
			}

			overlapping_shifts_html += '</table>';
		}

		if ( $this.overlapping_shift_info ) {
			$this.overlapping_shift_info.qtip( {
				show: {
					event: 'click',
					delay: 10,
					effect: true
				},
				hide: {
					event: ['unfocus click'],
				},
				style: {
					width: 525, //Dynamically changing the width causes display bugs when switching between Absence Policies and thereby widths.
					classes: 'overlapping-info-display'
				},
				content: overlapping_shifts_html,
			} );
		}
	}

	getOverlappingShiftHeaderHtml() {
		return `<tr>
					<th style="text-align: center">` + $.i18n._( 'Date' ) + `</th>
					<th style="text-align: center">` + $.i18n._( 'Before' ) + `</th>
					<th style="text-align: center">` + $.i18n._( 'After' ) + `</th>
				</tr>
				`;
	}

	// Creates the record shipped to the API at setMesssage
	uniformMessageVariable( records ) {
		var msg = {};

		msg.subject = this.edit_view_ui_dic['subject'].getValue();
		msg.body = this.edit_view_ui_dic['body'].getValue();
		msg.object_id = this.current_edit_record['id'];
		msg.object_type_id = 50;

		return msg;
	}

	getRequestTabHtml() {
		return `
		<div id="tab_request" class="edit-view-tab-outside">
			<div class="edit-view-tab" id="tab_request_content_div">
				<div class="first-column full-width-column"></div>
				<div class="authorization-grid-div inside-grid full-width-column">
					<div class="grid-top-border"></div>
					<div class="grid-title separated-box"></div>
					<table id="grid"></table>
					<div class="bottom-div">
						<div class="grid-bottom-border"></div>
					</div>
				</div>
				<div class="separate full-width-column"></div>
				<div class="second-column embedded-message-template full-width-column" style="margin-left: 0"></div>
			</div>
		</div>`;
	}
}
