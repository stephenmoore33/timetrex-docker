export class RequestAuthorizationViewController extends RequestViewCommonController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#request_authorization_view_container',

			type_array: null,
			overlap_type_array: null,
			hierarchy_level_array: null,

			messages: null,

			authorization_api: null,
			api_request: null,
			api_absence_policy: null,
			message_control_api: null,
			schedule_api: null,

			punch_tag_api: null,
			default_punch_tag: [],
			previous_punch_tag_selection: [],

			authorization_history_columns: [],

			authorization_history_default_display_columns: [],

			authorization_history_grid: null,
			pre_request_schedule: true,

			overlapping_shift_data: {},
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RequestAuthorizationEditView.html';
		this.permission_id = 'request';
		this.viewId = 'RequestAuthorization';
		this.script_name = 'RequestAuthorizationView';
		this.table_name_key = 'request';
		this.context_menu_name = $.i18n._( 'Request (Authorizations)' );
		this.navigation_label = $.i18n._( 'Requests' );
		this.api = TTAPI.APIRequest;
		this.authorization_api = TTAPI.APIAuthorization;
		this.api_request = TTAPI.APIRequest;
		this.api_absence_policy = TTAPI.APIAbsencePolicy;
		this.message_control_api = TTAPI.APIMessageControl;

		if( ( Global.getProductEdition() >= 15 ) ) {
			this.schedule_api = TTAPI.APISchedule;
		}
		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.punch_tag_api = TTAPI.APIPunchTag;
		}
		this.message_control_api = TTAPI.APIMessageControl;
		this.event_bus = new TTEventBus({ view_id: this.viewId });

		this.initPermission();
		this.render();

		this.buildContextMenu( true );

		this.initData();
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'type', api: this.api }
		];

		if ( ( Global.getProductEdition() >= 15 ) ) {
			options.push( { option_name: 'overlap_type', api: TTAPI.APIRequestSchedule } );
		}

		this.initDropDownOptions( options );

		var result = this.api.getHierarchyLevelOptions( [-1], { async: false } );
		if ( result && result.isValid() ) {
			var data = result.getResult();
			$this['hierarchy_level_array'] = Global.buildRecordArray( data );
			if ( Global.isSet( $this.basic_search_field_ui_dic['hierarchy_level'] ) ) {
				$this.basic_search_field_ui_dic['hierarchy_level'].setSourceData( Global.buildRecordArray( data ) );
			}
		}
	}

	initPermission() {
		super.initPermission();

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

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				action: {
					label: $.i18n._( 'Action' ),
					id: this.script_name + 'action'
				},
				authorization: {
					label: $.i18n._( 'Authorization' ),
					id: this.script_name + 'authorization'
				},
				objects: {
					label: $.i18n._( 'Objects' ),
					id: this.script_name + 'objects'
				}
			},
			exclude: ['default'],
			include: [
				{
					label: $.i18n._( 'View' ),
					id: 'view',
					group: 'action',
					vue_icon: 'tticon tticon-visibility_black_24dp',
					show_on_right_click: true,
					sort_order: 1011
				},
				{
					label: $.i18n._( 'Reply' ),
					id: 'edit',
					group: 'action',
					vue_icon: 'tticon tticon-reply_black_24dp',
					sort_order: 1021
				},
				{
					label: $.i18n._( 'Send' ),
					id: 'send',
					group: 'action',
					vue_icon: 'tticon tticon-send_black_24dp',
					sort_order: 1031
				},
				{
					label: $.i18n._( 'Cancel' ),
					id: 'cancel',
					group: 'action',
					sort_order: 1131
				},
				{
					label: $.i18n._( 'Authorize' ),
					id: 'authorization',
					vue_icon: 'tticon tticon-thumb_up_black_24dp',
					menu_align: 'center'
				},
				{
					label: $.i18n._( 'Pass' ),
					id: 'pass',
					vue_icon: 'tticon tticon-redo_black_24dp',
					menu_align: 'center'
				},
				{
					label: $.i18n._( 'Decline' ),
					id: 'decline',
					vue_icon: 'tticon tticon-thumb_down_black_24dp',
					menu_align: 'center'
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
					group: 'other',
					vue_icon: 'tticon tticon-file_upload_black_24dp',
					menu_align: 'right'
				},
				{
					label: $.i18n._( 'Edit Employee' ),
					id: 'edit_employee',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					},
			]
		};

		return context_menu_model;
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'send':
				this.setDefaultMenuSaveIcon( context_btn, grid_selected_length );
				break;
			case 'authorization':
				this.setDefaultMenuAuthorizationIcon( context_btn, grid_selected_length );
				break;
			case 'pass':
				this.setDefaultMenuPassIcon( context_btn, grid_selected_length );
				break;
			case 'decline':
				this.setDefaultMenuDeclineIcon( context_btn, grid_selected_length );
				break;
			case 'authorization_request':
				this.setDefaultMenuAuthorizationRequestIcon( context_btn, grid_selected_length );
				break;
			case 'authorization_timesheet':
				this.setDefaultMenuAuthorizationTimesheetIcon( context_btn, grid_selected_length );
				break;
			case 'timesheet':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
				break;
			case 'schedule':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
				break;
			case 'authorization_expense':
				this.setDefaultMenuAuthorizationExpenseIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setDefaultMenuAuthorizationExpenseIcon( context_btn, grid_selected_length ) {

		if ( !( Global.getProductEdition() >= 25 ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'send':
				this.setEditMenuSaveIcon( context_btn );
				break;
			case 'authorization':
				this.setEditMenuAuthorizationIcon( context_btn );
				break;
			case 'pass':
				this.setEditMenuPassIcon( context_btn );
				break;
			case 'decline':
				this.setEditMenuDeclineIcon( context_btn );
				break;
			case 'authorization_request':
				this.setEditMenuAuthorizationRequestIcon( context_btn );
				break;
			case 'authorization_timesheet':
				this.setEditMenuAuthorizationTimesheetIcon( context_btn );
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
			case 'authorization_expense':
				this.setEditMenuAuthorizationExpenseIcon( context_btn );
				break;
		}
	}

	setEditMenuAuthorizationExpenseIcon( context_btn ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'send':
				this.onSaveClick();
				break;
			case 'authorization':
				this.onAuthorizationClick();
				break;
			case 'pass':
				this.onPassClick();
				break;
			case 'decline':
				this.onDeclineClick();
				break;
			case 'authorization_request':
				this.onAuthorizationRequestClick();
				break;
			case 'authorization_timesheet':
				this.onAuthorizationTimesheetClick();
				break;
			case 'authorization_expense':
				this.onAuthorizationExpenseClick();
				break;
			case 'timesheet':
			case 'schedule':
			case 'edit_employee':
				this.onNavigationClick( id );
				break;
		}
	}

	onViewclick() {
		super.onViewclick();
		AuthorizationHistory.init( this );
	}

	handleAuthorizationButtons( enable = true ) {
		if ( enable == true ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, 'authorization', true );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, 'pass', true );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, 'decline', true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, 'authorization', false );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, 'pass', false );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, 'decline', false );
		}
	}

	onAuthorizationExpenseClick() {
		IndexViewController.goToView( 'ExpenseAuthorization' );
	}

	onSaveClick( ignoreWarning ) {
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		if ( this.is_edit ) {

			var $this = this;
			var record;
			this.is_add = false;

			record = this.current_edit_record;

			record = this.uniformVariable( record );

			EmbeddedMessage.reply( record, ignoreWarning, function( result ) {
				if ( result && result.isValid() ) {
					var id = $this.current_edit_record.id;

					//see #2224 - Unable to get property 'find' of undefined
					$this.removeEditView();
					$this.onViewClick( id );
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}
			} );
		}
	}

	onAuthorizationClick( ignoreWarning ) {
		var $this = this;

		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}

		//Error: TypeError: $this.current_edit_record is null in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 629
		if ( !$this.current_edit_record ) {
			return;
		}

		var request_data;
		if ( Global.getProductEdition() >= 15 && ( this.current_edit_record.type_id == 30 || this.current_edit_record.type_id == 40 ) ) {
			request_data = $this.buildDataForAPI( this.current_edit_record );
		} else {
			request_data = this.getSelectedItem();
		}

		LocalCacheData.current_doing_context_action = 'authorize';

		$this.handleAuthorizationButtons( false ); //Disable the authorization buttons while the authorization is being processed.

		//Check if Edit permissions exist, if not, only authorize the request to avoid a API permission error.
		if ( this.enable_edit_view_ui == true ) {
			$this.api_request['setRequest']( request_data, false, ignoreWarning, {
				onResult: function( res ) {
					if ( res.getResult() != false ) {
						authorizeRequest();
					} else {
						$this.handleAuthorizationButtons( true ); //Enable the authorization buttons while the authorization is being processed.

						$this.setErrorMenu();
						$this.setErrorTips( res, true );
					}
				},
				onError: function( result ) {
					$this.handleAuthorizationButtons( true ); //Enable the authorization buttons while the authorization is being processed.
				}
			} );
		} else {
			authorizeRequest();
		}

		function authorizeRequest() {
			if ( $this.current_edit_record ) {
				var filter = {};
				filter.authorized = true;
				filter.object_id = $this.current_edit_record.id;
				filter.object_type_id = $this.current_edit_record.hierarchy_type_id;

				$this.authorization_api['setAuthorization']( [filter], {
					onResult: function( result ) {
						if ( result && result.isValid() ) {
							$this.is_changed = false;
							$this.updateBadgeCount();
							$this.onRightArrowClick( function() {
								// Note: if side effects occur here, previously the search(false) function was accidentally called as a evaluated param (run each time, parallel), rather than callback (run at the end, on last record)
								$this.search( false );
								$().TFeedback( {
									source: 'Authorize'
								} );
							} );
						} else {
							$this.setErrorMenu();
							$this.setErrorTips( result, true );
						}

						$this.handleAuthorizationButtons( true ); //Enable the authorization buttons while the authorization is being processed.
					},
					onError: function( result ) {
						$this.handleAuthorizationButtons( true ); //Enable the authorization buttons while the authorization is being processed.
					}
				} );
			}
		}
	}

	onPassClick() {
		var $this = this;

		function doNext() {
			$this.onRightArrowClick( function() {
				$this.search();
				$().TFeedback( {
					source: 'Pass'
				} );
			} );
		}

		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
				if ( flag === true ) {
					doNext();
				}
			} );
		} else {
			doNext();
		}
	}

	onAuthorizationRequestClick() {
		this.search( false );
	}

	onDeclineClick() {
		var $this = this;

		function doNext() {
			var filter = {};

			filter.authorized = false;
			filter.object_id = $this.current_edit_record.id;
			filter.object_type_id = $this.current_edit_record.hierarchy_type_id;

			$this.handleAuthorizationButtons( false ); //Disable the authorization buttons while the authorization is being processed.

			$this.authorization_api['setAuthorization']( [filter], {
				onResult: function( res ) {
					$this.updateBadgeCount();
					$this.onRightArrowClick( function() {
						$this.search( false );
						$().TFeedback( {
							source: 'Decline'
						} );
					} );

					$this.handleAuthorizationButtons( true ); //Enable the authorization buttons while the authorization is being processed.
				},
				onError: function( result ) {
					$this.handleAuthorizationButtons( true ); //Enable the authorization buttons while the authorization is being processed.
				}
			} );
		}

		if ( this.is_changed ) {
			TAlertManager.showConfirmAlert( Global.modify_alert_message, null, function( flag ) {
				if ( flag === true ) {
					doNext();
				}
			} );
		} else {
			doNext();
		}
	}

	onAuthorizationTimesheetClick() {
		IndexViewController.goToView( 'TimeSheetAuthorization' );
	}

	uniformVariable( records ) {
		if ( this.is_edit ) {
			return this.uniformMessageVariable( records );
		}
		return records;
	}

	onGridDblClickRow() {

		ProgressBar.showOverlay();
		this.onViewClick();
	}

	onSaveResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			//Setting is_add false too early can cause determineContextMenuMountAttributes() to have unexpected side effects. However not setting it here might have other side effects.
			//$this.is_add = false;
			var result_data = result.getResult();
			if ( !this.edit_only_mode ) {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id;
				} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
					$this.refresh_id = result_data;
				}

				$this.search();
			}

			$this.onSaveDone( result );

			if ( $this.is_edit ) {
				$this.onViewClick( $this.current_edit_record.id );
			} else {

				$this.removeEditView();
			}

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	}

	setEditMenuAuthorizationIcon( context_btn ) {
		if ( this.is_edit ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setEditMenuPassIcon( context_btn ) {
		if ( this.is_edit ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setEditMenuDeclineIcon( context_btn ) {
		if ( this.is_edit ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setEditMenuAuthorizationRequestIcon( context_btn ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setEditMenuAuthorizationTimesheetIcon( context_btn ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setEditMenuSaveIcon( context_btn ) {
		if ( this.is_edit ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuSaveIcon( context_btn, grid_selected_length ) {
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuEditIcon( context_btn, grid_selected_length ) {
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length ) {
		if ( !this.editPermissionValidate( 'user' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuAuthorizationIcon( context_btn, grid_selected_length ) {
		if ( !this.is_viewing ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuPassIcon( context_btn, grid_selected_length ) {
		if ( !this.is_viewing ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuDeclineIcon( context_btn, grid_selected_length ) {
		if ( !this.is_viewing ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuAuthorizationRequestIcon( context_btn, grid_selected_length ) {
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
	}

	setDefaultMenuAuthorizationTimesheetIcon( context_btn, grid_selected_length ) {
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
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
				multiple: true,
				field: 'type_id',
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
				label: $.i18n._( 'Hierarchy Level' ),
				in_column: 2,
				multiple: false,
				field: 'hierarchy_level',
				basic_search: true,
				adv_search: false,
				set_any: false,
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

	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;
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
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
			case 'punch_tag_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onPunchTagQuickSearch( c_value, this.getPunchTagFilterData(), 'punch_tag_id' );

					//Don't validate immediately as onPunchTagQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
			case 'type_id':
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
				$this.getScheduleTotalTime();
				$this.onStartDateChanged();
				$this.current_edit_record.start_date = $this.edit_view_ui_dic.start_date.getValue();
				$this.current_edit_record.date_stamp = $this.edit_view_ui_dic.start_date.getValue();
				if ( $this.edit_view_ui_dic.date_stamp ) {
					$this.edit_view_ui_dic.date_stamp.setValue( $this.edit_view_ui_dic.start_date.getValue() );
				}
				if ( ( Global.getProductEdition() >= 15 ) ) {
					$this.getOverlappingShifts();
				}
				break;
			case 'end_date':
				$this.getScheduleTotalTime();
				$this.current_edit_record.end_date = $this.edit_view_ui_dic.end_date.getValue();
				if ( ( Global.getProductEdition() >= 15 ) ) {
					$this.getOverlappingShifts();
				}
				break;
			case 'sun':
			case 'mon':
			case 'tue':
			case 'wed':
			case 'thu':
			case 'fri':
			case 'sat':
				$this.getScheduleTotalTime();
				if ( ( Global.getProductEdition() >= 15 ) ) {
					this.getOverlappingShifts();
				}
				break;
			case 'start_time':
			case 'end_time':
			case 'schedule_policy_id':
				if ( ( Global.getProductEdition() >= 15 ) ) {
					this.getOverlappingShifts();
				}
				break;
			case'absence_policy_id':
				this.selected_absence_policy_record = this.edit_view_ui_dic.absence_policy_id.getValue();
				this.getAvailableBalance();
				break;
			case 'is_replace_with_open_shift':
				if ( ( Global.getProductEdition() >= 15 ) ) {
					this.getOverlappingShifts();
				}
				break;
		}

		if ( key === 'date_stamp' ||
			key === 'start_date_stamps' ||
			key === 'start_date_stamp' ||
			key === 'start_time' ||
			key === 'end_time' ||
			key === 'schedule_policy_id' ||
			key === 'absence_policy_id' ) {

			if ( this.current_edit_record['date_stamp'] !== '' &&
				this.current_edit_record['start_time'] !== '' &&
				this.current_edit_record['end_time'] !== '' ) {

				this.getScheduleTotalTime();

			} else {
				this.onAvailableBalanceChange();
			}

		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode() {
		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			widget.css( 'opacity', 1 );
			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean_dic[tab_id] ) {
					did_clean_dic[tab_id] = true;
				}
			}
			if ( Global.isSet( widget.setEnabled ) ) {
				widget.setEnabled( this.enable_edit_view_ui );
			}
		}
	}

	onAvailableBalanceChange() {
		this.getAvailableBalance();
	}

	setURL() {

		if ( LocalCacheData.current_doing_context_action === 'edit' ) {
			LocalCacheData.current_doing_context_action = '';
			return;
		}

		super.setURL();
	}

	getSubViewFilter( filter ) {
		if ( filter.length === 0 ) {
			filter = {};
		}

		if ( !Global.isSet( filter.type_id ) ) {
			filter['type_id'] = [-1];
		}

		if ( !Global.isSet( filter.hierarchy_level ) ) {
			filter['hierarchy_level'] = 1;
			this.filter_data['hierarchy_level'] = {
				field: 'hierarchy_level',
				id: '',
				value: this.basic_search_field_ui_dic['hierarchy_level'].getValue( true )
			};
		}

		return filter;
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

	search( set_default_menu, page_action, page_number, callBack ) {
		this.refresh_id = null;
		this.updateBadgeCount();
		super.search( set_default_menu, page_action, page_number, callBack );
	}

	updateBadgeCount() {
		this.event_bus.emit( 'tt_topbar', 'profile_pending_counts', { //Update all "My Profile" badges.
			object_types: [ 'notification', 'request_authorization' ]
		} );
	}

	setCurrentEditRecordData() {
		var $this = this;
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'full_name':
						widget.setValue( this.current_edit_record['first_name'] + ' ' + this.current_edit_record['last_name'] );
						break;
					case 'subject':
						widget.setValue( this.current_edit_record[key] );
						if ( this.is_edit ) {
							widget.setValue( 'Re: ' + this.messages[0].subject );
						}

						break;
					case 'punch_tag_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							widget.setValue( this.current_edit_record[key] );
							this.previous_punch_tag_selection = this.current_edit_record[key];

							var args = {};
							args.filter_data = $this.getPunchTagFilterData();
							widget.setDefaultArgs( args );
						}
						break;
					case 'punch_tag_quick_search':
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}
		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		var $this = this;
		super.setEditViewDataDone();
		if ( !this.is_viewing ) {
			if ( Global.isSet( $this.messages ) ) {
				$this.messages = null;
			}
		}

		if ( ( Global.getProductEdition() >= 15 ) ) {
			$this.getOverlappingShifts();
		}
	}

	//Make sure this.current_edit_record is updated before validate
	validate() {
		var $this = this;

		var record = {};

		if ( this.is_edit ) {
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}

				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						record[key] = widget.getValue();
					}

				}
			}

		} else {
			record = this.buildDataForAPI( this.current_edit_record );
		}

		record = this.uniformVariable( record );
		if ( this.is_edit ) {
			this.message_control_api['validate' + this.message_control_api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		} else if ( this.is_viewing ) {
			this.api_request['validate' + this.api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		}
	}

	openAuthorizationView() {
		if ( !this.edit_view ) {
			this.initEditViewUI( this.viewId, this.edit_view_tpl );
		}
	}
}