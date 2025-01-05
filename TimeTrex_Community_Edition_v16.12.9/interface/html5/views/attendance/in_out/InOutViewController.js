import { getFingerprint as ThumbmarkGetFingerprint, setOption as ThumbmarkSetOption } from '@thumbmarkjs/thumbmarkjs';

export class InOutViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {

			type_array: null,

			job_api: null,
			job_item_api: null,
			punch_tag_api: null,
			user_api: null,
			department_api: null,
			system_job_queue_api: null,

			default_punch_tag: [],
			previous_punch_tag_selection: [],

			old_type_status: {},

			show_job_ui: false,
			show_job_item_ui: false,
			show_punch_tag_ui: false,
			show_branch_ui: false,
			show_department_ui: false,
			show_good_quantity_ui: false,
			show_bad_quantity_ui: false,
			show_transfer_ui: false,
			show_node_ui: false,

			original_note: false,
			new_note: false,

			do_not_prevalidate: true, //Help reduce server load by skipping prevalidation. Enabled if validation fails during save.
		} );

		super( options );
	}

	init( options ) {
		Global.setUINotready( true );

		this.permission_id = 'punch';
		this.viewId = 'InOut';
		this.script_name = 'InOutView';
		this.table_name_key = 'punch';
		this.context_menu_name = $.i18n._( 'In/Out' );
		this.api = TTAPI.APIPunch;
		this.system_job_queue_api = TTAPI.APISystemJobQueue;
		this.event_bus = new TTEventBus( { view_id: this.viewId } );

		//Tried to fix  Cannot call method 'getJobItem' of null. Use ( Global.getProductEdition() >= 20 )
		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.punch_tag_api = TTAPI.APIPunchTag;
			this.user_api = TTAPI.APIUser;
			this.department_api = TTAPI.APIDepartment;
		}

		this.render();
		// this.buildContextMenu(); // #VueContextMenu#EditOnly - Commented out as must happen after initEditViewUI

		this.initPermission();

		this.initData();
		this.is_changed = true;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: ['save', 'cancel']
		};

		return context_menu_model;
	}

	addPermissionValidate( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'punch_in_out' ) ) {
			return true;
		}

		return false;
	}

	jobUIValidate() {
		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( 'punch', 'edit_job' ) ) {
			return true;
		}
		return false;
	}

	jobItemUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_job_item' ) ) {
			return true;
		}
		return false;
	}

	punchTagUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_punch_tag' ) ) {
			return true;
		}
		return false;
	}

	branchUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_branch' ) ) {
			return true;
		}
		return false;
	}

	departmentUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_department' ) ) {
			return true;
		}
		return false;
	}

	goodQuantityUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_quantity' ) ) {
			return true;
		}
		return false;
	}

	badQuantityUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_quantity' ) &&
			PermissionManager.validate( 'punch', 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	}

	transferUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_transfer' ) ) {
			return true;
		}
		return false;
	}

	noteUIValidate() {
		if ( PermissionManager.validate( 'punch', 'edit_note' ) ) {
			return true;
		}
		return false;
	}

	//Speical permission check for views, need override
	initPermission() {
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

		if ( this.goodQuantityUIValidate() ) {
			this.show_good_quantity_ui = true;
		} else {
			this.show_good_quantity_ui = false;
		}

		if ( this.badQuantityUIValidate() ) {
			this.show_bad_quantity_ui = true;
		} else {
			this.show_bad_quantity_ui = false;
		}

		if ( this.transferUIValidate() ) {
			this.show_transfer_ui = true;
		} else {
			this.show_transfer_ui = false;
		}

		if ( this.noteUIValidate() ) {
			this.show_node_ui = true;
		} else {
			this.show_node_ui = false;
		}

		var result = false;

		// Error: Uncaught TypeError: (intermediate value).isBranchAndDepartmentAndJobAndJobItemAndPunchTagEnabled is not a function on line 207
		var company_api = TTAPI.APICompany;
		if ( company_api && _.isFunction( company_api.isBranchAndDepartmentAndJobAndJobItemAndPunchTagEnabled ) ) {
			result = company_api.isBranchAndDepartmentAndJobAndJobItemAndPunchTagEnabled( { async: false } );
		}

		//tried to fix Unable to get property 'getResult' of undefined or null reference, added if(!result)
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

		if ( !this.show_job_ui && !this.show_job_item_ui ) {
			this.show_bad_quantity_ui = false;
			this.show_good_quantity_ui = false;
		}
	}

	render() {
		super.render();
	}

	initOptions( callBack ) {

		var options = [
			{ option_name: 'type' },
			{ option_name: 'status' }
		];

		this.initDropDownOptions( options, function( result ) {
			if ( callBack ) {
				callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
			}
		} );
	}

	getUserPunch( callBack ) {
		var $this = this;

		var station_id = Global.getStationID();

		var api_station = TTAPI.APIStation;

		if ( station_id ) {
			api_station.getCurrentStation( station_id, '10', {
				onResult: function( result ) {
					doNext( result );
				}
			} );
		} else {
			//This is also done in QuickPunchLoginViewController.js and InOutViewController.js
			ThumbmarkSetOption( 'exclude', [ 'audio', 'canvas', 'webgl', 'system.browser.version' ] );
			ThumbmarkGetFingerprint( true ).then( ( browser_fingerprint ) => {
				Debug.Text( 'Browser Fingerprint: ' + browser_fingerprint.hash, 'InOutViewController.js', 'InOutViewController', 'getUserPunch', 10 );
				if ( browser_fingerprint && browser_fingerprint.hash != '' ) {
					station_id = 'BFP-'+ browser_fingerprint.hash;
				}

				api_station.getCurrentStation( station_id, '10', { //BFP = Browser FingerPrint prefix.
					onResult: function( result ) {
						doNext( result );
					}
				} );
			} ).catch( error => {
				api_station.getCurrentStation( '', '10', {
					onResult: function( result ) {
						doNext( result );
					}
				} );
			});
		}

		function doNext( result ) {
			// Error: Uncaught TypeError: undefined is not a function in /interface/html5/#!m=TimeSheet&date=20150324&user_id=36135&sm=InOut line 285
			if ( !$this.api || typeof $this.api['getUserPunch'] !== 'function' ) {
				return;
			}

			//Show error message if getCurrentStation() returns one.
			if ( !result.isValid() ) {
				TAlertManager.showErrorAlert( result );
				$this.onCancelClick( true );
				return;
			}

			var res_data = result.getResult();
			Global.setStationID( res_data );

			$this.api.getUserPunch( {
				onResult: function( result ) {
					var result_data = result.getResult();
					//keep the inout view fields consistent for screenshots in unit test mode
					if ( Global.UNIT_TEST_MODE === true ) {
						result_data.punch_date = 'UNITTEST';
						result_data.punch_time = 'UNITTEST';
					}

					if ( !result.isValid() ) {
						TAlertManager.showErrorAlert( result );
						$this.onCancelClick( true );
						return;
					}

					if ( Global.isSet( result_data ) ) {
						callBack( result_data );

					} else {
						$this.onCancelClick();
					}
				}

			} );

		}
	}

	onCancelClick( force_no_confirm ) {
		this.is_changed = true;
		super.onCancelClick( force_no_confirm );
	}

	openEditView() {
		var $this = this;

		if ( this.edit_only_mode && this.api ) {

			this.initOptions( function( result ) {
				if ( !$this.edit_view ) {
					$this.initEditViewUI( 'InOut', 'InOutEditView.html' );
					$this.buildContextMenu(); // #VueContextMenu#EditOnly - Must happen after initEditViewUI
				}

				$this.getUserPunch( function( result ) {
					// Waiting for the TTAPI.API returns data to set the current edit record.
					$this.current_edit_record = result;

					//keep fields consistent in unit test mode for consistent screenshots
					if ( Global.UNIT_TEST_MODE === true ) {
						$this.current_edit_record.punch_date = 'UNITTEST';
						$this.current_edit_record.punch_time = 'UNITTEST';
					}
					$this.initEditView();

				} );

			} );

		}
	}

	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		doNotValidate = this.do_not_prevalidate; //Help reduce server load. Defaults to true unless validation fails on save.

		switch ( key ) {
			case 'transfer':
				this.onTransferChanged();
				break;
			case 'job_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ), 'job_item_id', {
						status_id: 10,
						job_id: this.current_edit_record.job_id
					} );
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
			case 'branch_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
					this.setJobValueWhenCriteriaChanged( 'job_id', {
						status_id: 10,
						user_id: this.current_edit_record.user_id,
						punch_branch_id: this.current_edit_record.branch_id,
						punch_department_id: this.current_edit_record.department_id
					} );
					this.setDepartmentValueWhenBranchChanged( target.getValue( true ), 'department_id', {
						branch_id: this.current_edit_record.branch_id,
						user_id: this.current_edit_record.user_id
					} );
				}
				break;
			case 'user_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
			case 'department_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
					this.setJobValueWhenCriteriaChanged( 'job_id', {
						status_id: 10,
						user_id: this.current_edit_record.user_id,
						punch_branch_id: this.current_edit_record.branch_id,
						punch_department_id: this.current_edit_record.department_id
					} );
				}
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onJobQuickSearch( key, c_value );
					TTPromise.wait( 'BaseViewController', 'onJobQuickSearch', function() {
						$this.setPunchTagValuesWhenCriteriaChanged( $this.getPunchTagFilterData(), 'punch_tag_id' );
					} );
					//Don't validate immediately as onJobQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
			case 'punch_tag_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onPunchTagQuickSearch( c_value, this.getPunchTagFilterData(), 'punch_tag_id' );

					//Don't validate immediately as onJobQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onTransferChanged( initial_load ) {

		var is_transfer = false;
		if ( this.edit_view_ui_dic && this.edit_view_ui_dic['transfer'] && this.edit_view_ui_dic['transfer'].getValue() == true ) {
			is_transfer = true;
		}

		// type_id_widget is undefined in interface/html5/framework/jquery.min.js?v=9.0.1-20151022-091549 line 2 > eval line 390
		var type_id_widget = this.edit_view_ui_dic['type_id'];
		var status_id_widget = this.edit_view_ui_dic['status_id'];
		if ( is_transfer && type_id_widget && status_id_widget ) {

			type_id_widget.setEnabled( false );
			status_id_widget.setEnabled( false );

			this.old_type_status.type_id = type_id_widget.getValue();
			this.old_type_status.status_id = status_id_widget.getValue();

			type_id_widget.setValue( 10 );
			status_id_widget.setValue( 10 );

			this.current_edit_record.type_id = 10;
			this.current_edit_record.status_id = 10;

		} else if ( type_id_widget && status_id_widget ) {
			type_id_widget.setEnabled( true );
			status_id_widget.setEnabled( true );

			if ( this.old_type_status.hasOwnProperty( 'type_id' ) ) {
				type_id_widget.setValue( this.old_type_status.type_id );
				status_id_widget.setValue( this.old_type_status.status_id );

				this.current_edit_record.type_id = this.old_type_status.type_id;
				this.current_edit_record.status_id = this.old_type_status.status_id;
			}

		}

		if ( is_transfer == true ) {
			if ( this.auto_fill_data && this.auto_fill_data.note ) {
				this.new_note = this.auto_fill_data.note;
				this.edit_view_ui_dic.note.setValue( this.new_note );
				this.current_edit_record.note = this.new_note;
			} else if ( this.original_note == '' ) {
				this.original_note = this.current_edit_record.note;
			} else {
				this.original_note = this.edit_view_ui_dic.note.getValue();
			}
			this.edit_view_ui_dic.note.setValue( this.new_note ? this.new_note : '' );
			this.current_edit_record.note = this.new_note ? this.new_note : '';

		} else if ( typeof initial_load == 'undefined' || initial_load === false ) {

			this.new_note = this.edit_view_ui_dic.note.getValue();
			this.edit_view_ui_dic.note.setValue( this.original_note ? this.original_note : '' );
			this.current_edit_record.note = this.original_note ? this.original_note : '';
		}
	}

	//Make sure this.current_edit_record is updated before validate
	validate() {

		var $this = this;

		var record = {};

		if ( this.is_mass_editing ) {
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
			record = this.current_edit_record;
		}

		record = this.uniformVariable( record );

		this.api.setUserPunch( record, true, {
			onResult: function( result ) {
				$this.validateResult( result );

			}
		} );
	}

	// Overrides BaseViewController
	onSaveClick( ignoreWarning, force_no_confirm ) {
		//this.is_changed = true;
		return super.onSaveClick( ignoreWarning, true );
	}

	// Overrides BaseViewController
	doSaveAPICall( record, ignoreWarning, callback ) {
		var current_api = this.getCurrentAPI();

		if ( !callback ) {
			callback = {
				onResult: function( result ) {
					this.onSaveResult( result );
				}.bind( this )
			};
		}

		current_api.setIsIdempotent( true ); //Force to idempotent API call to avoid duplicate network requests from causing errors displayed to the user.
		return current_api.setUserPunch( record, false, ignoreWarning, callback );
	}

	convertSetUserPunchToGetPunch( record ) {
		record.id = TTUUID.not_exist_id;
		record.longitude = null;
		record.latitude = null;
		record.position_accuracy = null;
		record.pay_period_id = null;
		record.punch_control_id = record.punch_control_id ? record.punch_control_id : null;
		record.tainted = false;
		record.has_image = false;

		return record;
	}

	onSaveResult( result ) {
		let pending_punch = _.clone( this.current_edit_record );
		super.onSaveResult( result );
		//If pending punch is null that means the In/Out view has already been closed. Multiple clicks of save under latency could cause this.
		//Do not continue further or exceptions will be thrown trying to read a null punch.
		if ( result && result.isValid() && pending_punch != null ) {
			LocalCacheData.setLastPunchTime( new Date().getTime() ); //Update last punch time so that we can limit the user from punching in more than once a minute.
			var system_job_queue = result.getAttributeInAPIDetails( 'system_job_queue' );
			if ( system_job_queue ) {
				LocalCacheData.setJobQueuePunchData( this.convertSetUserPunchToGetPunch( pending_punch ) );
				this.event_bus.emit( 'tt_topbar', 'toggle_job_queue_spinner', {
					show: true,
					get_job_data: true
				} );
			}

			if ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' ) {
				LocalCacheData.current_open_primary_controller.search();
			}
		} else {
			this.do_not_prevalidate = false;
		}
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

	getCustomFieldReferenceField() {
		return 'note';
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_punch': { 'label': $.i18n._( 'Punch' ) },
			'tab_audit': false,
		};
		this.setTabModel( tab_model );

		//Tab 0 start

		var tab_punch = this.edit_view_tab.find( '#tab_punch' );

		var tab_punch_column1 = tab_punch.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_punch_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );

		form_item_input.TText( {
			field: 'user_id_readonly'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_punch_column1, '' );

		// Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );

		form_item_input.TTimePicker( { field: 'punch_time' } );

		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1 );

		// Date
//		  punch_date, punch_dates
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'punch_date' } );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1 );

		//Transfer

		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'transfer' } );

		this.addEditFieldToColumn( $.i18n._( 'Transfer' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.show_transfer_ui ) {
			this.detachElement( 'transfer' );
		}

		// Punch

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );

		this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), form_item_input, tab_punch_column1 );

		// In/Out

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'In/Out' ), form_item_input, tab_punch_column1 );

		// Branch

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIBranch,
			allow_multiple_selection: false,
			layout_name: 'global_branch',
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.show_branch_ui ) {
			this.detachElement( 'branch_id' );
		}

		// Department

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIDepartment,
			allow_multiple_selection: false,
			layout_name: 'global_department',
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.show_department_ui ) {
			this.detachElement( 'department_id' );
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
				always_include_columns: ['group_id'],
				setRealValueCallBack: ( function( val ) {

					if ( val ) {
						job_coder.setValue( val.manual_id );
					}
				} ),
				field: 'job_id'
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( { field: 'job_quick_search', disable_keyup_event: true } );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_job_ui ) {
				this.detachElement( 'job_id' );
			}

			// Task

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input.AComboBox( {
				api_class: TTAPI.APIJobItem,
				allow_multiple_selection: false,
				layout_name: 'global_job_item',
				show_search_inputs: true,
				set_empty: true,
				always_include_columns: ['group_id'],
				setRealValueCallBack: ( function( val ) {
					if ( val ) {
						job_item_coder.setValue( val.manual_id );
					}
				} ),
				field: 'job_item_id'
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true } );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_punch_column1, '', widgetContainer, true );

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
				get_real_data_on_multi: true,
				setRealValueCallBack: ( ( punch_tags, get_real_data ) => {
					if ( punch_tags ) {
						this.setPunchTagQuickSearchManualIds( punch_tags, get_real_data );
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
			this.addEditFieldToColumn( $.i18n._( 'Tags' ), [form_item_input, punch_tag_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_punch_tag_ui ) {
				this.detachElement( 'punch_tag_id' );
			}
		}

		// Quantity

		if ( ( Global.getProductEdition() >= 20 ) ) {

			var good = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			good.TTextInput( { field: 'quantity', width: 40 } );
			good.addClass( 'quantity-input' );

			var good_label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Good' ) + ': </span>' );

			var bad = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			bad.TTextInput( { field: 'bad_quantity', width: 40 } );
			bad.addClass( 'quantity-input' );

			var bad_label = $( '<span class=\'widget-right-label\'>/ ' + $.i18n._( 'Bad' ) + ': </span>' );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			widgetContainer.append( good_label );
			widgetContainer.append( good );
			widgetContainer.append( bad_label );
			widgetContainer.append( bad );

			this.addEditFieldToColumn( $.i18n._( 'Quantity' ), [good, bad], tab_punch_column1, '', widgetContainer, true );

			if ( !this.show_bad_quantity_ui && !this.show_good_quantity_ui ) {
				this.detachElement( 'quantity' );
			} else {
				if ( !this.show_bad_quantity_ui ) {
					bad_label.hide();
					bad.hide();
				}

				if ( !this.show_good_quantity_ui ) {
					good_label.hide();
					good.hide();
				}
			}

		}

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( { field: 'note', width: '100%' } );

		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_punch_column1, '', null, true, true );

		form_item_input.parent().width( '45%' );

		if ( !this.show_node_ui ) {
			this.detachElement( 'note' );
		}
	}

	setCurrentEditRecordData() {
		var $this = this;
		// reset old_types, should only be set when type change and transfer is true. fixed bug 1500
		this.old_type_status = {};
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_id_readonly':
						widget.setValue( this.current_edit_record.first_name + ' ' + this.current_edit_record.last_name );
						break;
					case 'job_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							var args = {};
							args.filter_data = {
								status_id: 10,
								user_id: this.current_edit_record.user_id,
								punch_branch_id: this.current_edit_record.branch_id,
								punch_department_id: this.current_edit_record.department_id
							};
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_item_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							var args = {};
							args.filter_data = { status_id: 10, job_id: this.current_edit_record.job_id };
							widget.setDefaultArgs( args );
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
					case 'branch_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							var args = {};
							args.filter_data = { status_id: 10, user_id: this.current_edit_record.user_id };
							widget.setDefaultArgs( args );
						}
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'department_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							var args = {};
							args.filter_data = { status_id: 10, user_id: this.current_edit_record.user_id, branch_id: this.current_edit_record.branch_id };
							widget.setDefaultArgs( args );
						}
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'job_quick_search':
						break;
					case 'job_item_quick_search':
						break;
					case 'punch_tag_quick_search':
						break;
					case 'transfer':
						// do this at last
						break;
					case 'punch_time':
					case 'punch_date':
						widget.setEnabled( false );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in interface/html5/#!m=TimeSheet&date=20151019&user_id=25869&show_wage=0&sm=InOut line 926
		//The API will return if transfer should be enabled/disabled by default.
		if ( this.show_transfer_ui && this.edit_view_ui_dic['transfer'] ) {
			this.edit_view_ui_dic['transfer'].setValue( this.current_edit_record['transfer'] );
		}

		this.onTransferChanged( true );

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.confirm_on_exit = true; //confirm on leaving even if no changes have been made so users can't accidentally not save punches by logging out without clicking save for example
	}
}

InOutViewController.loadView = function() {

	Global.loadViewSource( 'InOut', 'InOutView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		Global.contentContainer().html( template( args ) );
	} );

};
