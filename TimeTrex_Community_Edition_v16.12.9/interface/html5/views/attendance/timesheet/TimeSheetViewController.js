import '@/global/widgets/filebrowser/TImage';
import TTVueUtils from '@/services/TTVueUtils';
import TimeSheetControlBar from '@/components/timesheet/TimeSheetControlBar';
import TimeSheetNote from '@/components/timesheet/TimeSheetNote';
import { TTUUID } from '@/global/TTUUID';
import { Global } from '@/global/Global';

export class TimeSheetViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#timesheet_view_container', //Must set el here and can only set string, so events can work
			// _required_files: {
			// 	10: ['TImage'],
			// 	15: ['leaflet-timetrex']
			// },
			status_array: null,
			type_array: null,
			employee_nav: null,
			start_date_picker: null,
			full_timesheet_data: null, //full timesheet data
			full_format: 'ddd-MMM-DD-YYYY',
			weekly_format: 'ddd, MMM DD',
			day_format: 'ddd',
			date_format: 'MMM DD',
			start_date: null,
			end_date: null,
			select_cells_Array: [], //Timesheet grid
			select_punches_array: [], //Timesheet grid.
			absence_select_cells_Array: [], //Absence grid
			accumulated_time_cells_array: [],
			premium_cells_array: [],
			timesheet_data_source: null,
			accumulated_time_source: null,
			accumulated_time_grid: null,
			accumulated_time_source_map: null,
			branch_grid: null,
			branch_source_map: null,
			branch_source: null,
			department_grid: null,
			department_source_map: null,
			department_source: null,
			job_grid: null,
			job_source_map: null,
			job_source: null,
			job_item_grid: null,
			job_item_source_map: null,
			job_item_source: null,
			punch_tag_source_map: null,
			punch_tag_source: null,
			premium_grid: null,
			premium_source_map: null,
			premium_source: null,
			absence_grid: null,
			absence_source: null,
			absence_original_source: null,
			accumulated_total_grid: null,
			accumulated_total_grid_source_map: null,
			accumulated_total_grid_source: null,
			punch_note_grid: null,
			punch_note_grid_source: null,
			verification_grid: null,
			verification_grid_source: null,
			grid_dic: null,
			pay_period_map: null,
			pay_period_data: null,
			timesheet_verify_data: null,
			api_timesheet: null,
			api_user_date_total: null,
			api_date: null,
			api_station: null,
			api_punch: null,
			absence_model: false,
			select_drag_menu_id: '', //Do drag move or copy
			is_mass_adding: false,
			department_cell_count: 0,
			branch_cell_count: 0,
			premium_cell_count: 0,
			job_cell_count: 0,
			task_cell_count: 0,
			punch_tag_cell_count: 0,
			absence_cell_count: 0,
			punch_note_account: 0,
			show_navigation_box: true,
			station: null,
			scroll_position: 0,
			job_api: null,
			job_item_api: null,
			user_group_id: null,
			punch_tag_api: null,
			user_api: null,
			department_api: null,
			default_punch_tag: [],
			previous_punch_tag_selection: [],
			api_absence_policy: null,
			pre_total_time: null,
			absence_available_balance_dataList: {},
			available_balance_info: null,
			show_job_ui: false,
			show_job_item_ui: false,
			show_punch_tag_ui: false,
			show_branch_ui: false,
			show_department_ui: false,
			show_good_quantity_ui: false,
			show_bad_quantity_ui: false,
			show_note_ui: false,
			show_station_ui: false,
			show_absence_job_ui: false,
			show_absence_job_item_ui: false,
			show_absence_punch_tag_ui: false,
			show_absence_branch_ui: false,
			show_absence_department_ui: false,
			holiday_data_dic: {},
			grid_div: null,
			actual_time_label: null,
			column_maps: null,
			accmulated_order_map: {},
			url_args_before_set_date_url: {},
			allow_auto_switch: true,
			vue_control_bar_id: '',
			previous_absence_policy_id: false,
			events: {},
			//Issue #3286 - Users without permission to display "Current View" dropdown still need to load select layout from user generic data
			//This is to ensure the API attempts to update the current layout and not create a new one causing a validation error.
			force_get_select_layout: true,
			location_mass_edit_check_box: null,
			manual_note_component_ids: [],
			marked_regular_row: {},
		} );

		super( options );
	}

	init( options ) {
		////this._super('initialize', options );
		this.permission_id = 'punch';
		this.viewId = 'TimeSheet';
		this.script_name = 'TimeSheetView';
		this.context_menu_name = $.i18n._( 'TimeSheet' );
		this.navigation_label = $.i18n._( 'TimeSheet' );
		this.api = TTAPI.APIPunch;
		this.api_timesheet = TTAPI.APITimeSheet;
		this.api_user_date_total = TTAPI.APIUserDateTotal;
		this.api_date = TTAPI.APITTDate;
		this.api_station = TTAPI.APIStation;
		this.api_punch = TTAPI.APIPunch;
		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.punch_tag_api = TTAPI.APIPunchTag;
			this.department_api = TTAPI.APIDepartment;
		}
		this.api_absence_policy = TTAPI.APIAbsencePolicy;
		this.scroll_position = 0;
		this.grid_dic = {};
		this.event_bus = new TTEventBus({ view_id: this.viewId });

		this.initPermission();
		this.render();
		this.buildContextMenu();
		this.initData();
	}

	initEditView() {
		TTPromise.resolve( 'TimeSheetViewController', 'addclick' );
		super.initEditView();
	}

	onSubViewRemoved( is_cancel ) {
		if ( !is_cancel ) {
			this.search();
		}

		if ( !this.edit_view ) {
			this.setDefaultMenu();
		} else {
			this.setEditMenu();
		}
	}

	setScrollPosition() {
		if ( this.scroll_position > 0 ) {
			this.grid_div.scrollTop( this.scroll_position );
		}
	}

	punchModeValidate( p_id ) {
		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'punch_timesheet' ) &&
			PermissionManager.validate( p_id, 'manual_timesheet' ) ) {
			return true;
		}
		return false;
	}

	getPunchPermissionType() {
		return this.absence_model ? 'absence' : 'punch';
	}

	jobUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if (Global.getProductEdition() >= 20 && PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) ) {
			return true;
		}
		return false;
	}

	jobItemUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if (Global.getProductEdition() >= 20 && PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	}

	punchTagUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if (Global.getProductEdition() >= 20 && PermissionManager.validate( p_id, 'edit_punch_tag' ) ) {
			return true;
		}
		return false;
	}

	//Refresh to clear warnning messages after saving from employee edit view
	updateSelectUserAndRefresh( new_item ) {

		this.employee_nav.updateSelectItem( new_item );

		this.search();
	}

	branchUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	}

	departmentUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	}

	goodQuantityUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) ) {
			return true;
		}
		return false;
	}

	badQuantityUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_quantity' ) &&
			PermissionManager.validate( p_id, 'edit_bad_quantity' ) ) {
			return true;
		}
		return false;
	}

	locationUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_location' ) ) {
			return true;
		}
		return false;
	}

	noteUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_note' ) ) {
			return true;
		}
		return false;
	}

	stationValidate() {
		if ( PermissionManager.validate( 'station', 'enabled' ) ) {
			return true;
		}
		return false;
	}

	/* jshint ignore:start */

	//Special permission check for views, need override
	initPermission() {
		super.initPermission();

		if ( !PermissionManager.validate( 'punch', 'view' ) && !PermissionManager.validate( 'punch', 'view_child' ) ) {
			this.show_navigation_box = false;
			this.show_search_tab = false;
		} else {
			this.show_navigation_box = true;
			this.show_search_tab = true;
		}

		if ( this.punchModeValidate() ) {
			this.show_punch_mode_ui = true;
		} else {
			this.show_punch_mode_ui = false;
		}

		this.allow_auto_switch && this.show_punch_mode_ui && ( this.is_auto_switch = true );

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

		if ( this.noteUIValidate() ) {
			this.show_note_ui = true;
		} else {
			this.show_note_ui = false;
		}

		if ( this.locationUIValidate() ) {
			this.show_location_ui = true;
		} else {
			this.show_location_ui = false;
		}

		if ( this.stationValidate() ) {
			this.show_station_ui = true;
		} else {
			this.show_station_ui = false;
		}

		if ( this.jobUIValidate( 'absence' ) ) {
			this.show_absence_job_ui = true;
		} else {
			this.show_absence_job_ui = false;
		}

		if ( this.jobItemUIValidate( 'absence' ) ) {
			this.show_absence_job_item_ui = true;
		} else {
			this.show_absence_job_item_ui = false;
		}

		if ( this.punchTagUIValidate( 'absence' ) ) {
			this.show_absence_punch_tag_ui = true;
		} else {
			this.show_absence_punch_tag_ui = false;
		}

		if ( this.branchUIValidate( 'absence' ) ) {
			this.show_absence_branch_ui = true;
		} else {
			this.show_absence_branch_ui = false;
		}

		if ( this.departmentUIValidate( 'absence' ) ) {
			this.show_absence_department_ui = true;
		} else {
			this.show_absence_department_ui = false;
		}
	}

	/* jshint ignore:end */

	ownerOrChildPermissionValidate( p_id, permission_name, selected_item ) {
		var field;
		if ( permission_name && permission_name.indexOf( 'child' ) > -1 ) {
			field = 'is_child';
		} else {
			field = 'is_owner';
		}

		var user = this.getSelectEmployee( true );

		if ( PermissionManager.validate( p_id, permission_name ) && ( !user || !Global.isSet( user[field] ) || ( user && user[field] ) ) ) {
			return true;
		}

		return false;
	}

	initOptions() {
		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'status', api: this.api },
		];

		this.initDropDownOptions( options);
	}
	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				drag_and_drop: {
					label: $.i18n._( 'Drag & Drop' ),
					id: this.viewId + 'drag_and_drop'
				}
			},
			exclude: [
				'export_excel',
				'add',
				'copy',
				'copy_as_new'
			],
			include: [
				{
					label: $.i18n._( 'New Punch' ),
					id: 'add_punch',
					action_group: 'new',
					group: 'editor',
					vue_icon: 'tticon tticon-add_black_24dp',
					show_on_right_click: true,
					sort_order: 910
				},
				{
					label: $.i18n._( 'New Absence' ),
					id: 'add_absence',
					action_group: 'new',
					group: 'editor',
					vue_icon: 'tticon tticon-add_black_24dp',
					show_on_right_click: true,
					sort_order: 920
				},
				{
					label: $.i18n._( 'In/Out' ),
					id: 'in_out',
					action_group: 'in_out',
					group: 'editor',
					vue_icon: 'tticon tticon-timer_black_24dp',
					show_on_right_click: true,
					sort_order: 1050
				},
				{
					label: $.i18n._( 'Drag & Drop: Move' ),
					id: 'move',
					menu_align: 'right',
					action_group: 'move_copy',
					multi_select_group: 1
				},
				{
					label: $.i18n._( 'Drag & Drop: Copy' ),
					id: 'drag_copy',
					menu_align: 'right',
					action_group: 'move_copy',
					multi_select_group: 1
				},
			]
		};

		if ( PermissionManager.validate( 'request', 'add' ) ) {
			context_menu_model.include.push( {
				label: $.i18n._( 'Add Request' ),
				id: 'AddRequest',
				vue_icon: 'tticon tticon-post_add_black_24dp',
				menu_align: 'right',
				permission_result: true,
				permission: true,
				show_on_right_click: true,
				sort_order: 1000
			} );
		}

		if ( ( Global.getProductEdition() >= 15 ) ) {
			context_menu_model.include.push(
				{
					label: $.i18n._( 'Map' ),
					id: 'map',
					menu_align: 'right',
					vue_icon: 'tticon tticon-map_black_24dp',
					show_on_right_click: true,
					sort_order: 2000,
				}
			);
		}

		context_menu_model.include.push(
			{
				label: $.i18n._( 'Print' ),
				id: 'print',
				action_group_header: true,
				action_group: 'print_menu',
				sort_order: 7000,
				menu_align: 'right',
				type: 2,
				permission_result: true,
				permission: true
			},
			{
				label: $.i18n._( 'Summary' ),
				id: 'print_summary',
				action_group: 'print_menu',
				sort_order: 7000,
				menu_align: 'right'
			},
			{
				label: $.i18n._( 'Detailed' ),
				id: 'print_detailed',
				action_group: 'print_menu',
				sort_order: 7000,
				menu_align: 'right'
			},
			{
				label: $.i18n._( 'Jump To' ),
				id: 'jump_to_header',
				menu_align: 'right',
				action_group: 'jump_to',
				sort_order: 8000,
				action_group_header: true,
				permission_result: false // to hide it in legacy context menu and avoid errors in legacy parsers.
			},
			{
				label: $.i18n._( 'Schedules' ),
				id: 'schedule',
				menu_align: 'right',
				action_group: 'jump_to',
				sort_order: 8000
				},
			{
				label: $.i18n._( 'Pay Stubs' ),
				id: 'pay_stub',
				menu_align: 'right',
				action_group: 'jump_to',
				sort_order: 8000
				},
			{
				label: $.i18n._( 'Edit Employee' ),
				id: 'edit_employee',
				menu_align: 'right',
				action_group: 'jump_to',
				sort_order: 8000
				},
			{
				label: $.i18n._( 'Edit Pay Period' ),
				id: 'edit_pay_period',
				menu_align: 'right',
				action_group: 'jump_to',
				sort_order: 8000
				},
			{
				label: $.i18n._( 'Accumulated Time' ),
				id: 'accumulated_time',
				menu_align: 'right',
				action_group: 'jump_to',
				sort_order: 8000
				},
			{
				label: '', //Empty label. vue_icon is displayed instead of text.
				id: 'other_header',
				menu_align: 'right',
				action_group: 'other',
				action_group_header: true,
				vue_icon: 'tticon tticon-more_vert_black_24dp',
			},
			{
				label: $.i18n._( 'ReCalculate TimeSheet' ),
				id: 're_calculate_timesheet',
				menu_align: 'right',
				action_group: 'other',
				},
			{
				label: $.i18n._( 'Generate Pay Stub' ),
				id: 'generate_pay_stub',
				menu_align: 'right',
				action_group: 'other',
				},
		);

		return context_menu_model;
	}

	parseCustomContextModelForEditViews( context_menu_model ) {

		context_menu_model = super.parseCustomContextModelForEditViews( context_menu_model );

		if( this.determineContextMenuMountAttributes().menu_type === 'editview_contextmenu' ) {
			context_menu_model.exclude.push(
				'move',
				'drag_copy',
				're_calculate_timesheet',
				'generate_pay_stub',
				'print',
				'print_detailed',
				'print_summary',
			)
		}

		return context_menu_model;
	}

	openEditView() {
		//#2295 - Re-initialize previous_absence_policy_id to ensure that previously saved values are passed correctly into the estimation of projected available balance.
		this.previous_absence_policy_id = false;

		Global.setUINotready();
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();
		if ( !this.edit_view ) {
			this.is_edit = true;
			this.initEditViewUI( 'TimeSheet', 'TimeSheetEditView.html' );
		}
	}

	/* jshint ignore:start */

	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode() {
		var did_clean = false;
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			var widgetContainer = this.edit_view_form_item_dic[key];
			var column = widget.parent().parent().parent();
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean ) {
					did_clean = true;
				}
			}
			if ( this.absence_model ) {
				switch ( key ) {
					case 'punch_date':
					case 'punch_time':
					case 'status_id':
					case 'type_id':
					case 'quantity':
					case 'station_id':
					case 'has_image':
					case 'latitude':
					case 'split_punch_control':
						this.detachElement( key );
						widget.css( 'opacity', 0 );
						break;
					case 'punch_dates':
						if ( this.is_mass_adding ) {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
						} else {
							this.detachElement( key );
							widget.css( 'opacity', 0 );
						}
						break;
					case 'date_stamp':
						if ( this.is_mass_adding ) {
							this.detachElement( key );
							widget.css( 'opacity', 0 );
						} else {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
						}
						break;
					case 'total_time':
					case 'src_object_id':
					case 'override':
						this.attachElement( key );
						widget.css( 'opacity', 1 );
						break;
					case 'available_balance':
						this.detachElement( key );
						widget.css( 'opacity', 1 );
						break;
					default:
						widget.css( 'opacity', 1 );
						break;

				}

			} else {
				switch ( key ) {
					case 'punch_dates':
						if ( this.is_mass_adding ) {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
						} else {
							this.detachElement( key );
							widget.css( 'opacity', 0 );
						}
						break;
					case 'punch_date':
						if ( this.is_mass_adding ) {
							this.detachElement( key );
							widget.css( 'opacity', 0 );
						} else {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
						}
						break;
					case 'quantity':
						if ( this.show_good_quantity_ui && this.show_bad_quantity_ui ) {
							this.attachElement( key );
							widget.css( 'opacity', 1 );
						}
						break;
					case 'station':
						this.attachElement( key );
						widget.css( 'opacity', 1 );
						break;
					case 'punch_time':
					case 'status_id':
					case 'type_id':
					case 'has_image':
					case 'latitude':
						this.attachElement( key );
						widget.css( 'opacity', 1 );
						break;
					case 'date_stamp':
					case 'total_time':
					case 'src_object_id':
					case 'override':
						this.detachElement( key );
						widget.css( 'opacity', 0 );
						break;
					default:
						widget.css( 'opacity', 1 );
						break;

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

	getCustomFieldReferenceField() {
		return 'note';
	}

	setCustomFields() {
		//Custom fields are only shown on punch types and not on absence types.
		if ( this.getPunchPermissionType() === 'punch' ) {
			super.setCustomFields();
		}
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_punch': {
				'label': this.absence_model ? $.i18n._( 'Absence' ) : $.i18n._( 'Punch' )
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;
		var widgetContainer;

		//Tab 0 start

		var tab_punch = this.edit_view_tab.find( '#tab_punch' );

		var tab_punch_column1 = tab_punch.find( '.first-column' );

		//Employee

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'first_last_name' } );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_punch_column1, '' );

		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'punch_time', validation_field: 'time_stamp' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		this.actual_time_label = $( '<span class=\'widget-right-label\'></span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( this.actual_time_label );
		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Absence Model
		//Absence Policy Type
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIAbsencePolicy,
			allow_multiple_selection: false,
			layout_name: 'global_absences',
			show_search_inputs: true,
			set_empty: true,
			field: 'src_object_id',
			validation_field: 'absence_policy_id'
		} );

		form_item_input.customSearchFilter = function( filter ) {
			return $this.setAbsencePolicyFilter( filter );
		};

		this.addEditFieldToColumn( $.i18n._( 'Absence Policy' ), form_item_input, tab_punch_column1, '', null, true );

		//Available Balance
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'available_balance' } );

		widgetContainer = $( '<div class=\'widget-h-box available-balance-h-box\'></div>' );
		this.available_balance_info = $( '<span class="available-balance-info tticon tticon-info_black_24dp"></span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( this.available_balance_info );

		this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), [form_item_input], tab_punch_column1, '', widgetContainer, true );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'punch_date', validation_field: 'date_stamp' } );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TRangePicker( { field: 'punch_dates', validation_field: 'date_stamp' } );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Absence Model
		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'date_stamp' } );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Absence Model
		//Time
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'total_time', mode: 'time_unit' } );

		var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var release_balance_button = $( '<input id=\'release-balance-button\' class=\'t-button\' style=\'margin-left: 5px\' type=\'button\' value=\'' + $.i18n._( 'Available Balance' ) + '\'>' );
		release_balance_button.css( 'display', 'none' );

		release_balance_button.click( function() {
			$this.getAvailableBalance( true );
		} );

		widgetContainer.append( form_item_input );
		widgetContainer.append( release_balance_button );
		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', widgetContainer, true );

		//Punch Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
		check_box.TCheckbox( { field: 'disable_rounding' } );
		var label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Disable Rounding' ) + '</span>' );
		widgetContainer.append( form_item_input );

		// Check if view only mode. To prevent option appearing but disabled, as disabled checkboxes are not very clear - same in PunchViewController
		if ( this.is_viewing || PermissionManager.validate( 'punch', 'edit_disable_rounding' ) == false ) {
			// dev-note: not sure if we need to pass widgetContainer here, or if we can omit if its only one element now (due to the if is_viewing).
			// to be safe, will continue to use widgetContainer for this case. We only want to affect viewing mode (hide rounding checkbox), less risk of regression to keep widget container in.
			this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), form_item_input, tab_punch_column1, '', widgetContainer, true );
		} else {
			widgetContainer.append( label );
			widgetContainer.append( check_box );
			this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), [form_item_input, check_box], tab_punch_column1, '', widgetContainer, true );
		}

		//In Out (Status)
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'In/Out' ), form_item_input, tab_punch_column1, '', null, true );

		//Default Branch
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

		if ( !this.absence_model ) {
			if ( !this.show_branch_ui ) {
				this.detachElement( 'branch_id' );
			}
		} else {
			if ( !this.show_absence_branch_ui ) {
				this.detachElement( 'branch_id' );
			}
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
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_punch_column1, '', null, true );

		if ( !this.absence_model ) {
			if ( !this.show_department_ui ) {
				this.detachElement( 'department_id' );
			}
		} else {
			if ( !this.show_absence_department_ui ) {
				this.detachElement( 'department_id' );
			}
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
				field: 'job_id'
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( { field: 'job_quick_search', disable_keyup_event: true } );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.absence_model ) {
				if ( !this.show_job_ui ) {
					this.detachElement( 'job_id' );
				}
			} else {
				if ( !this.show_absence_job_ui ) {
					this.detachElement( 'job_id' );
				}
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
				field: 'job_item_id'
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true } );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_punch_column1, '', widgetContainer, true );

			if ( !this.absence_model ) {
				if ( !this.show_job_item_ui ) {
					this.detachElement( 'job_item_id' );
				}
			} else {
				if ( !this.show_absence_job_item_ui ) {
					this.detachElement( 'job_item_id' );
				}
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

			if ( !this.absence_model ) {
				if ( !this.show_punch_tag_ui ) {
					this.detachElement( 'punch_tag_id' );
				}
			} else {
				if ( !this.show_absence_punch_tag_ui ) {
					this.detachElement( 'punch_tag_id' );
				}
			}
		}

		if ( ( Global.getProductEdition() >= 20 ) ) {

			//Quanitity

			var good = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			good.TTextInput( { field: 'quantity' } );
			good.addClass( 'quantity-input' );

			var good_label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Good' ) + ': </span>' );

			var bad = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			bad.TTextInput( { field: 'bad_quantity' } );
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

		if ( !this.show_note_ui ) {
			this.detachElement( 'note' );
		}

		//Absence Mode
		//Override
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'override' } );
		this.addEditFieldToColumn( $.i18n._( 'Override' ), form_item_input, tab_punch_column1, '', null, true, true );

		//Location
		if ( Global.getProductEdition() >= 15 ) {

			var latitude = Global.loadWidgetByName( FormItemType.TEXT );
			latitude.TText( { field: 'latitude' } );
			var longitude = Global.loadWidgetByName( FormItemType.TEXT );
			longitude.TText( { field: 'longitude' } );
			widgetContainer = $( '<div class=\'widget-h-box link-widget-box\'></div>' );
			var accuracy = Global.loadWidgetByName( FormItemType.TEXT );
			accuracy.TText( { field: 'position_accuracy' } );
			label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Accuracy' ) + ':</span>' );

			var map_icon = $( '<img class="widget-h-box-mapIcon" src="framework/leaflet/images/marker-icon-red.png" >' );

			this.location_wrapper = $( '<div class="widget-h-box-mapLocationWrapper"></div>' );
			widgetContainer.append( map_icon );
			widgetContainer.append( this.location_wrapper );
			this.location_wrapper.append( latitude );
			this.location_wrapper.append( $( '<span>, </span>' ) );
			this.location_wrapper.append( longitude );
			this.location_wrapper.append( label );
			this.location_wrapper.append( accuracy );
			this.location_wrapper.append( $( '<span>m</span>' ) );

			if ( this.is_mass_editing ) {
				this.location_mass_edit_check_box = $( ' <div class="mass-edit-checkbox-wrapper"><input type="checkbox" class="mass-edit-checkbox"></input>' +
					'<label for="checkbox-input-1" class="input-helper input-helper--checkbox"></label></div>' );

				this.location_mass_edit_check_box.insertBefore( $( map_icon ) );
				this.location_mass_edit_check_box.change( () => {
					this.trigger( 'formItemChange', [this.edit_view_ui_dic.longitude] );
					this.trigger( 'formItemChange' [this.edit_view_ui_dic.latitude] );
					this.trigger( 'position_accuracy' [this.edit_view_ui_dic.position_accuracy] );
				} );

				//The location field is actually 3 fields in one, longitude, latitude and accuracy.
				//Therefore, in mass edit mode it's the widgetContainer that is marked as changed.

				let isChecked = () => this.location_mass_edit_check_box.find( '.mass-edit-checkbox' )[0].checked;
				let getEnabled = () => true;

				//These 3 widgets inside the widgetContainer represent one field and share the same checkbox and enabled state.
				longitude.isChecked = latitude.isChecked = accuracy.isChecked = isChecked;
				longitude.getEnabled = latitude.getEnabled = accuracy.getEnabled = getEnabled;
			}
			this.addEditFieldToColumn( $.i18n._( 'Location' ), [latitude, longitude, accuracy], tab_punch_column1, '', widgetContainer, true );
			widgetContainer.click( ( event ) => {
				if ( event.target.className != 'mass-edit-checkbox' ) {
					this.onMapClick();
				}
			} );

			// #2117 - Manual location only supported in edit because we need a punch record to append the data to.
			if ( ( !this.is_edit && !this.is_viewing && !this.is_mass_editing ) || !this.show_location_ui ) {
				widgetContainer.parents( '.edit-view-form-item-div' ).hide();
			}
		}

		//Station
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'station_id' } );

		this.addEditFieldToColumn( $.i18n._( 'Station' ), form_item_input, tab_punch_column1, '', null, true, true );

		form_item_input.click( function() {
			if ( $this.current_edit_record.station_id && $this.show_station_ui ) {
				IndexViewController.openEditView( $this, 'Station', $this.current_edit_record.station_id );
			}

		} );

		//Split Punch Control
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'split_punch_control' } );
		this.addEditFieldToColumn( $.i18n._( 'Split Existing Punches' ), form_item_input, tab_punch_column1, '', null, true, true );
		if ( this.is_mass_adding == false ) {
			this.detachElement( 'split_punch_control' );
		}

		//Punch Image
		form_item_input = Global.loadWidgetByName( FormItemType.IMAGE );
		form_item_input.TImage( { field: 'punch_image' } );
		this.addEditFieldToColumn( $.i18n._( 'Image' ), form_item_input, tab_punch_column1, '', null, true, true );
	}

	/* jshint ignore:end */

	onEditStationDone() {
		this.setStation();
	}

	setAbsencePolicyFilter( filter ) {
		if ( !filter.filter_data ) {
			filter.filter_data = {};
		}

		filter.filter_data.user_id = this.current_edit_record?.user_id;

		if ( filter.filter_columns ) {
			filter.filter_columns.absence_policy = true;
		}

		return filter;
	}

	onSetSearchFilterFinished() {
	}

	onBuildBasicUIFinished() {
	}

	onBuildAdvUIFinished() {
	}

	parserDatesRange( date ) {
		var dates = date.split( ' - ' );
		var resultArray = [];
		var beginDate = Global.strToDate( dates[0] );
		var endDate = Global.strToDate( dates[1] );

		var nextDate = beginDate;

		while ( nextDate.getTime() < endDate.getTime() ) {
			resultArray.push( nextDate.format() );
			nextDate = new Date( new Date( nextDate.getTime() ).setDate( nextDate.getDate() + 1 ) );
		}

		resultArray.push( dates[1] );

		return resultArray;
	}

	validate() {
		var $this = this;
		var record = this.current_edit_record;
		var i;
		if ( this.is_mass_editing ) {
			record = [];
			var len = this.mass_edit_record_ids.length;
			for ( var i = 0; i < len; i++ ) {
				var temp_item = Global.clone( this.current_edit_record );
				temp_item.id = this.mass_edit_record_ids[i];
				record.push( temp_item );
			}
		}

		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		if ( !this.absence_model ) {

			this.api['validate' + this.api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );

		} else {

			this.api_user_date_total['validate' + this.api_user_date_total.key_name]( record, {
				onResult: function( result ) {
					$this.clearErrorTips(); //Always clear error

					if ( result && result.isValid() ) {
						$this.setEditMenu();
					} else {
						$this.setErrorMenu();
						$this.setErrorTips( result );
					}

				}
			} );

		}
	}

	/* jshint ignore:start */
	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		// Error: TypeError: this.current_edit_record is null in interface/html5/framework/jquery.min.js?v=9.0.5-20151222-094938 line 2 > eval line 1409
		if ( !this.current_edit_record ) {
			return;
		}
		this.current_edit_record[key] = c_value;
		switch ( key ) {
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
						user_id:   this.current_edit_record.user_id
					} );
				}
				break;
			case 'user_id':
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
			case 'punch_dates':
				this.setEditMenu();
				break;

		}

		if ( this.absence_model ) {
			if ( key === 'total_time' ) {

				if ( this.current_edit_record ) {
					this.current_edit_record[key] = Global.parseTimeUnit( c_value );
					// parsed_total_time_obj = this.api_date.parseTimeUnit( c_value, { async: false } );
					// if ( parsed_total_time_obj ) {
					// 	this.current_edit_record[key] = parsed_total_time_obj.getResult();
					// }

					//When handling absences, always remove the start/end time stamps otherwise they may be incorrect and trigger a validation error, as the user doesn't see them anyways.
					// The API will automatically calculated these on save anyways.
					this.current_edit_record['start_time_stamp'] = false;
					this.current_edit_record['end_time_stamp'] = false;
				}
			}

			if ( key !== 'override' && this.edit_view_ui_dic['override'] ) {
				this.edit_view_ui_dic['override'].setValue( true );
				this.current_edit_record['override'] = true;
			}
		} else {
			this.current_edit_record[key] = c_value;
		}

		if ( !doNotValidate ) {
			if ( this.absence_model ) {
				if ( key === 'total_time' ||
					key === 'date_stamp' ||
					key === 'punch_dates' ||
					key === 'src_object_id' ) {
					this.onAvailableBalanceChange();
				}
			}
			this.validate();
		}
	}

	/* jshint ignore:end */

	buildSearchAndLayoutUI() {
		var layout_div = this.search_panel.find( 'div #saved_layout_content_div' );

		var form_item = $( $.fn.SearchPanel.html.form_item );
		var form_item_label = form_item.find( '.form-item-label' );
		var form_item_input_div = form_item.find( '.form-item-input-div' );

		form_item_label.text( $.i18n._( 'Save Search As' ) );

		this.save_search_as_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		this.save_search_as_input.TTextInput();

		var save_btn = $( '<button class="tt-button p-button p-component small-search-panel-button" type="button">\n' +
			'<span class="tticon tticon-save_black_24dp"></span>\n' +
			'<span class="p-button-label">' + $.i18n._( 'Save' ) + '</span>\n' +
			'</button>' );

		form_item_input_div.append( this.save_search_as_input );
		form_item_input_div.append( save_btn );

		var $this = this;
		save_btn.click( function() {
			$this.onSaveNewLayout();
		} );

		//Previous Saved Layout

		this.previous_saved_layout_div = $( '<div class=\'previous-saved-layout-div\'></div>' );

		form_item_input_div.append( this.previous_saved_layout_div );

		form_item_label = $( '<span style=\'margin-left: 5px\' >' + $.i18n._( 'Previous Saved Searches' ) + ':</span>' );
		this.previous_saved_layout_div.append( form_item_label );

		this.previous_saved_layout_selector = $( '<select style=\'margin-left: 5px\' class=\'t-select\'>' );
		var update_btn = $( '<button class="tt-button p-button p-component small-search-panel-button" type="button">\n' +
			'<span class="tticon tticon-save_black_24dp"></span>\n' +
			'<span class="p-button-label">' + $.i18n._( 'Update' ) + '</span>\n' +
			'</button>' );

		var del_btn = $( '<button class="tt-button p-button p-component small-search-panel-button" type="button">\n' +
			'<span class="tticon tticon-delete_black_24dp"></span>\n' +
			'<span class="p-button-label">' + $.i18n._( 'Delete' ) + '</span>\n' +
			'</button>' );

		update_btn.click( function() {
			$this.onUpdateLayout();
		} );

		del_btn.click( function() {
			$this.onDeleteLayout();
		} );

		this.previous_saved_layout_div.append( this.previous_saved_layout_selector );
		this.previous_saved_layout_div.append( update_btn );
		this.previous_saved_layout_div.append( del_btn );

		layout_div.append( form_item );

		this.previous_saved_layout_div.css( 'display', 'none' );
	}

	checkTimesheetData() {
		if ( this.full_timesheet_data === true ) {
			return false;
		}

		return true;
	}

	render() {

		var $this = this;
		super.render();

		// Init Vue control bar
		this.vue_control_bar_id = 'vue-timesheet-control-bar';

		// Add callbacks here to inject into Vue, for button/menu interactions from Vue back to TT.
		var root_props = {
			onPunchModeChange: function() {
				//Prevent invalid mode state from being set
				if ( !$this.getPunchMode() && $this.toggle_button ) {
					$this.toggle_button.setValue( LocalCacheData.getAllURLArgs().mode ? LocalCacheData.getAllURLArgs().mode : 'punch' );
				}

				$this.onWageOrModeChange( 'manual' );
				$this.onSearch( true );
			},
			onShowWageClick: function() {
				$this.onWageOrModeChange( 'wage' );
				$this.onSearch( true );
			},
			onTimezoneClick: function() {
				$this.onWageOrModeChange( 'timezone' );
				$this.onSearch( true );
			}
		};

		// Proof of concept. Future work should use TTEventBus instead of vue_return, as passing by reference and direct access like this is a Vue anti-pattern.
		// Carefully use the return objects in vue_return, as interactions between Vue and legacy should be carefully controlled to avoid spagetti code / vue anti patterns.
		var vue_return = TTVueUtils.mountComponent( this.vue_control_bar_id, TimeSheetControlBar, root_props );

		var date_chooser_div = $( '.time-sheet-view .date-chooser-div' );
		var employee_nav_div = $( '.time-sheet-view .employee-nav-div' );

		//Issue #3097 - TypeError: Cannot read properties of undefined (reading 'getPunchMode')
		//The Vue TimeSheetControlBar may already contain dom elements from previous renders (cached?) and needs to be removed.
		//The parent div needs all children removed to prevent multiple date pickers from being added.
		date_chooser_div.empty();

		if ( !this.show_navigation_box ) {
			employee_nav_div.css( 'display', 'none' );
		} else {
			employee_nav_div.css( 'display', '' );
		}

		this.wage_btn = {
			id: this.vue_control_bar_id, // TODO: Do we need this still?
			show: function() {},
			hide: function() {},
			getValue: function() {
				if ( vue_return && vue_return._vue_component_instance ) {
					return vue_return._vue_component_instance.getTimesheetSettingsState( 'show_wages' );
				}
			},
			setValue: function( new_value ) {
				if ( vue_return && vue_return._vue_component_instance ) {
					vue_return._vue_component_instance.setTimesheetSettingsState( 'show_wages', new_value );
				}
			},
			// TODO Cant use this as a remove, as it would remove the whole control bar. We only want to hide/remove the toggle buttons for mode.
			remove: function() {
				// return TTVueUtils.unmountComponent( this.vue_control_bar_id )
			}
		};

		this.timezone_btn = {
			id: this.vue_control_bar_id,
			show: function() {},
			hide: function() {},
			getValue: function() {
				if ( vue_return && vue_return._vue_component_instance ) {
					return vue_return._vue_component_instance.getTimesheetSettingsState( 'use_employee_timezone' );
				}
			},
			setValue: function( new_value ) {
				if ( vue_return && vue_return._vue_component_instance ) {
					vue_return._vue_component_instance.setTimesheetSettingsState( 'use_employee_timezone', new_value );
				}
			},
			// TODO Cant use this as a remove, as it would remove the whole control bar. We only want to hide/remove the toggle buttons for mode.
			remove: function() {
				// return TTVueUtils.unmountComponent( this.vue_control_bar_id )
			}
		};

		//Create Start Date Picker
		this.start_date_picker = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		this.start_date_picker.TDatePicker( { field: 'start_date' } );
		var date_chooser = $( '<span class=\'label\'>' + $.i18n._( 'Date' ) + ':</span>' +
			'<img class=\'left-arrow arrow\' src=' + Global.getRealImagePath( 'images/left_arrow.svg' ) + '>' +
			'<div class=\'date-picker-div\'></div>' +
			'<img class=\'right-arrow arrow\' src=' + Global.getRealImagePath( 'images/right_arrow.svg' ) + '>' );

		date_chooser_div.append( date_chooser );
		date_chooser_div.find( '.date-picker-div' ).append( this.start_date_picker );

		var date_left_arrow = date_chooser_div.find( '.left-arrow' );
		var date_right_arrow = date_chooser_div.find( '.right-arrow' );

		date_left_arrow.bind( 'click', function() {
			//Error: TypeError: $this.timesheet_columns is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141230-125919 line 2 > eval line 1569
			if ( !$this.checkTimesheetData() || !$this.timesheet_columns ) {
				return;
			}

			var select_date = Global.strToDate( ( ( $this.getSelectDate() ) ? $this.getSelectDate() : new Date().format() ) );
			var new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 7 ) ).format();
			$this.first_build = true;
			continueChangeDate( new_date );

			//see #2224 Cannot read property 'date' of undefined
			$this.setDefaultMenu();
		} );

		date_right_arrow.bind( 'click', function() {
			//Error: TypeError: $this.timesheet_columns is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141230-125919 line 2 > eval line 1569
			if ( !$this.checkTimesheetData() || !$this.timesheet_columns ) {
				return;
			}

			var select_date = Global.strToDate( ( ( $this.getSelectDate() ) ? $this.getSelectDate() : new Date().format() ) );
			var new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 7 ) ).format();
			$this.first_build = true;
			continueChangeDate( new_date );

			//see #2224 Cannot read property 'date' of undefined
			$this.setDefaultMenu();
		} );

		this.start_date_picker.bind( 'formItemChange', function() {
			var select_date = $this.getSelectDate() ? $this.getSelectDate() : new Date().format();
			$this.first_build = true;
			continueChangeDate( select_date );
		} );

		function continueChangeDate( new_date ) {
			$this.doNextIfNoValueChangeInManualGrid( doNext, reset );

			function reset() {
				$this.setDatePickerValue( LocalCacheData.last_timesheet_selected_date );
			}

			function doNext() {
				$this.setDatePickerValue( new_date );
				$this.search();
			}
		}

		//Create Employee Navigation

		var label = employee_nav_div.find( '.navigation-label' );
		var left_click = employee_nav_div.find( '.left-click' );
		var right_click = employee_nav_div.find( '.right-click' );
		var navigation_widget_div = employee_nav_div.find( '.navigation-widget-div' );

		//Issue #3097 - TypeError: Cannot read properties of undefined (reading 'getPunchMode')
		//The Vue TimeSheetControlBar may already contain dom elements from previous renders (cached?) and needs to be removed.
		//The parent div needs all children removed to prevent multiple employee selectors from being added to the dom.
		navigation_widget_div.empty();

		this.employee_nav = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		var default_args = { permission_section: 'punch' };
		this.employee_nav = this.employee_nav.AComboBox( {
			id: 'employee_navigation',
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			init_data_immediately: true,
			default_args: default_args,
			show_search_inputs: true,
			always_include_columns: ['default_branch_id', 'default_department_id', 'default_job_id', 'default_job_item_id', 'default_punch_tag_id'],
			width: 200,
			is_static_width: true, //Use static width so the left/right navigation arrows don't move around based on the length of the employees name.
			setRealValueCallBack: ( function( val ) {
				$this.userValueSet( val );
			} )
		} );

		navigation_widget_div.append( this.employee_nav );
		navigation_widget_div.bind( 'onClose', () => {
			this.setEmployeeNavArrowsStatus();
		} );
		this.employee_nav.bind( 'formItemChange', function() {
			$this.doNextIfNoValueChangeInManualGrid( doNext, reset );
			Global.triggerAnalyticsEditViewNavigation( 'navigation', $this.viewId );

			function doNext() {
				var selected_user_id = $this.getSelectEmployee();
				if ( !$this.edit_view ) {
					$this.reSetURL();
				}
				$this.allow_auto_switch && ( $this.is_auto_switch = true );
				$this.first_build = true;
				/* jshint ignore:start */
				if ( LocalCacheData.last_timesheet_selected_user != selected_user_id ) {
					$this.search();
				}
				/* jshint ignore:end */
				$this.absence_model = false;
				$this.setDefaultMenu();
			}

			function reset() {
				$this.employee_nav.setValue( LocalCacheData.last_timesheet_selected_user );
			}
		} );

		this.employee_nav.bind( 'initSourceComplete', function() {
			$this.setEmployeeNavArrowsStatus();
		} );

		left_click.attr( 'src', Global.getRealImagePath( 'images/left_arrow.svg' ) );
		right_click.attr( 'src', Global.getRealImagePath( 'images/right_arrow.svg' ) );
		right_click.click( function() {
			if ( right_click.hasClass( 'disabled' ) ) {
				return;
			}
			$this.doNextIfNoValueChangeInManualGrid( doNext );
			Global.triggerAnalyticsEditViewNavigation( 'right-arrow', $this.viewId );

			function doNext() {
				var selected_index = $this.employee_nav.getSelectIndex();
				var source_data = $this.employee_nav.getSourceData();
				var current_open_page = $this.employee_nav.getCurrentOpenPage();
				var next_select_item;
				if ( source_data && selected_index < ( source_data.length - 1 ) ) {
					next_select_item = $this.employee_nav.getItemByIndex( ( selected_index + 1 ) );
					$this.employee_nav.setValue( next_select_item );
					doNextDone();
				} else if ( source_data && selected_index === source_data.length - 1 ) {
					//onADropDownSearch() makes async calls, so we need to have a doNextDone() callback to trigger to avoid race conditions.
					$this.employee_nav.onADropDownSearch( 'unselect_grid', ( current_open_page + 1 ), 'first', doNextDone, false ); //Skip triggering FormItemChange as we call search() ourself below anyways, and doing both can cause race conditions and incorrect data to be displayed.
				} else {
					next_select_item = $this.employee_nav.getItemByIndex( 0 );
					$this.employee_nav.setValue( next_select_item );
					doNextDone();
				}
			}

			function doNextDone() {
				if ( !$this.edit_view ) {
					$this.reSetURL();
				}
				$this.allow_auto_switch && ( $this.is_auto_switch = true );
				$this.first_build = true;
				$this.search();
				$this.setEmployeeNavArrowsStatus();
			}

		} );

		left_click.click( function() {
			if ( left_click.hasClass( 'disabled' ) ) {
				return;
			}
			$this.doNextIfNoValueChangeInManualGrid( doNext );
			Global.triggerAnalyticsEditViewNavigation( 'left-arrow', $this.viewId );

			function doNext() {
				var selected_index = $this.employee_nav.getSelectIndex();
				//var source_data = $this.employee_nav.getSourceData();
				var current_open_page = $this.employee_nav.getCurrentOpenPage();
				var next_select_item;
				if ( selected_index > 0 ) {
					next_select_item = $this.employee_nav.getItemByIndex( ( selected_index - 1 ) );
					$this.employee_nav.setValue( next_select_item );
					doNextDone();
				} else if ( current_open_page > 1 ) {
					//onADropDownSearch() makes async calls, so we need to have a doNextDone() callback to trigger to avoid race conditions.
					$this.employee_nav.onADropDownSearch( 'unselect_grid', ( current_open_page - 1 ), 'last', doNextDone, false ); //Skip triggering FormItemChange as we call search() ourself below anyways, and doing both can cause race conditions and incorrect data to be displayed.
				} else {
					// Error: TypeError: source_data is null in /interface/html5/framework/jquery.min.js?v=8.0.6-20150417-084000 line 2 > eval line 1691
					next_select_item = $this.employee_nav.getItemByIndex( 0 );
					$this.employee_nav.setValue( next_select_item );
					doNextDone();
				}

				function doNextDone() {
					if ( !$this.edit_view ) {
						$this.reSetURL();
					}
					$this.allow_auto_switch && ( $this.is_auto_switch = true );
					$this.first_build = true;
					$this.search();
					$this.setEmployeeNavArrowsStatus();
				}
			}

		} );

		label.text( $.i18n._( 'Employee' ) );

		// Create Vue timesheet mode toggle buttons

		if( this.show_punch_mode_ui ) {
			// Create pseudo element to pose as jQuery object but is actually just an interface for the Vue component.
			// TODO: Once more/view component has been converted to Vue, we want to refactor this toggle button logic to make use of more streamlined Vue data features like two way binding.

			this.toggle_button = {
				id: this.vue_control_bar_id,
				getValue: function() {
					if ( vue_return && vue_return._vue_component_instance ) {
						return vue_return._vue_component_instance.getPunchMode; // its done as a getter function on the Vue side, so no need for brackets.
					}
				},
				setValue: function( new_value ) {
					if ( vue_return && vue_return._vue_component_instance ) {
						vue_return._vue_component_instance.setPunchMode( new_value );
					}
				},
				// TODO Cant use this as a remove, as it would remove the whole control bar. We only want to hide/remove the toggle buttons for mode.
				remove: function() {
					return TTVueUtils.unmountComponent( this.vue_control_bar_id )
				}
			};
		} else {

			let mode = 'punch';

			const toggle_div = document.querySelector( '.punch-manual' );

			if ( toggle_div ) {
				toggle_div.style.display = 'none'; //Hide toggle mode button
			}

			//If they have manual permission, show manual mode otherwise punch
			if ( Global.getProductEdition() >= 15 && PermissionManager.validate( this.permission_id, 'manual_timesheet' ) ) {
				mode = 'manual';
			}

			this.toggle_button = {
				getValue: () => mode, //Always return the mode the user has permission to view.
				setValue: function( new_value ) {
					//User without permissions cannot change punch mode.
				},
			};
		}
	}

	doNextIfNoValueChangeInManualGrid( doNext, reset, mode ) {
		!mode && ( mode = 'manual' );
		var $this = this;
		if ( this.getPunchMode() === mode && this.editor ) {
			var records = this.editor.getValue();
			if ( records.length > 0 ) {
				TAlertManager.showConfirmAlert( Global.modify_alert_message, '', function( flag ) {
					if ( flag ) {
						$this.wait_auto_save && clearTimeout( $this.wait_auto_save );
						doNext();
					} else {
						reset && reset();
					}
				} );
			} else {
				doNext();
			}
		} else {
			doNext();
		}
	}

	getPunchMode() {
		//Mode toggle does not exist if the user doesn't have access to it.
		if ( this.toggle_button ) {
			return this.toggle_button.getValue();
		} else {
			return 'punch';
		}
	}

	handleOverrideUserPreferenceCookie( user_id ) {
		if ( this.timezone_btn.getValue() == true ) {
			setCookie( 'OverrideUserPreference', window.btoa( '{ "user_id": "' + user_id + '"}' ) ); //Base64 encode.
		} else {
			deleteCookie( 'OverrideUserPreference' );
		}
	}

	onWageOrModeChange( id ) {
		this.first_build = true;
		var $this = this;
		if ( id === 'wage' ) {
			this.doNextIfNoValueChangeInManualGrid( doNext, resetWage );
		} else if ( id === 'timezone' ) {
			this.handleOverrideUserPreferenceCookie( this.getSelectEmployee() );
			this.doNextIfNoValueChangeInManualGrid( doNext, resetTimeZone );
		} else if ( id === 'manual' ) {
			this.doNextIfNoValueChangeInManualGrid( doNext, resetManual, 'punch' );
		}

		function resetWage() {
			$this.wage_btn.setValue( !$this.wage_btn.getValue( true ) );
		}

		function resetTimeZone() {
			$this.timezone_btn.setValue( !$this.timezone_btn.getValue( true ) );
		}

		function resetManual() {
			$this.toggle_button.setValue( 'manual' );
		}

		function doNext() {
			if ( !$this.edit_view ) {
				$this.reSetURL();
			}
			$this.search();
			$this.setDefaultMenu();
		}
	}

	setEmployeeNavArrowsStatus() {
		var $this = this;
		var employee_nav_div = $( this.el ).find( '.employee-nav-div' );
		var left_click = employee_nav_div.find( '.left-click' );
		var right_click = employee_nav_div.find( '.right-click' );
		var selected_index = $this.employee_nav.getSelectIndex();
		var source_data = $this.employee_nav.getSourceData();

		right_click.removeClass( 'disabled' );
		left_click.removeClass( 'disabled' );

		var pager_data = $this.employee_nav.getPagerData();
		var current_open_page = $this.employee_nav.getCurrentOpenPage();

		//Error: Uncaught TypeError: Cannot read property 'length' of null in /interface/html5/#!m=TimeSheet&date=20150102&user_id=null line 1698
		if ( !source_data || ( selected_index === source_data.length - 1 && current_open_page === pager_data.last_page_number ) ) {
			right_click.addClass( 'disabled' );
		}

		if ( !source_data || ( selected_index === 0 && current_open_page === 1 ) ) {
			left_click.addClass( 'disabled' );
		}
	}

	onClearSearch() {
		var do_update = false;
		var default_layout_id;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains(\'' + BaseViewController.default_layout_name + '\')' ).attr( 'value' );
			var layout_name = BaseViewController.default_layout_name;
			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;
			do_update = true;

		} else {

			this.clearSearchPanel();
			this.filter_data = null;
			this.temp_adv_filter_data = null;
			this.temp_basic_filter_data = null;

			//Error: Uncaught TypeError: Cannot read property 'setSelectGridData' of null in /interface/html5/#!m=TimeSheet&date=20141213&user_id=29715 line 1738
			if ( this.column_selector ) {
				this.column_selector.setSelectGridData( this.default_display_columns );
			}

			//Error: Uncaught TypeError: Cannot read property 'setValue' of null in /interface/html5/#!m=TimeSheet&date=20150125&user_id=53288 line 1742
			if ( this.sort_by_selector ) {
				this.sort_by_selector.setValue( null );
			}

			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;

		}

		var filter_data =  Global.convertLayoutFilterToAPIFilter( { data: { filter_data: this.getValidSearchFilter() } } );

		var args;
		if ( do_update ) {
			args = {};
			args.id = default_layout_id;
			args.data = {};
			args.data.filter_data = filter_data;

		}

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res && res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );
	}

	onUpdateLayout() {

		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );
		var layout_name = $( this.previous_saved_layout_selector ).children( 'option:selected' ).text();

		var filter_data =  Global.convertLayoutFilterToAPIFilter( { data: { filter_data: this.getValidSearchFilter() } } );

		var args = {};
		args.id = selectId;
		args.data = {};
		args.data.filter_data = filter_data;
		args.data.mode = this.toggle_button.getValue();
		args.data.show_wage =  this.wage_btn.getValue();
		args.data.use_employee_timezone = this.timezone_btn.getValue();

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {
				if ( res && res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				}

			}
		} );
	}

	onSaveNewLayout( default_layout_name ) {
		var layout_name;
		if ( Global.isSet( default_layout_name ) ) {
			layout_name = default_layout_name;
		} else {
			layout_name = this.save_search_as_input.getValue();
		}

		if ( !layout_name || layout_name.length < 1 ) {
			return;
		}

		var filter_data =  Global.convertLayoutFilterToAPIFilter( { data: { filter_data: this.getValidSearchFilter() } } );

		var args = {};
		args.script = this.script_name;
		args.name = layout_name;
		args.is_default = false;
		args.data = {};
		args.data.filter_data = filter_data;
		args.data.mode = this.toggle_button.getValue();
		args.data.show_wage =  this.wage_btn.getValue();
		args.data.use_employee_timezone = this.timezone_btn.getValue();

		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res && res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					$this.initLayout();
				} else {
					TAlertManager.showErrorAlert( res );
				}

			}
		} );
	}

	onSearch( save_only ) {
		Global.setUINotready();
		TTPromise.add( 'init', 'init' );
		TTPromise.wait();

		this.temp_adv_filter_data = null;
		this.temp_basic_filter_data = null;

		this.getSearchPanelFilter();
		var default_layout_id;
		var layout_name;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains(\'' + BaseViewController.default_layout_name + '\')' ).attr( 'value' );
			layout_name = BaseViewController.default_layout_name;

			if ( !default_layout_id ) {
				this.onSaveNewLayout( BaseViewController.default_layout_name );
				return;
			}
		} else {
			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;
		}

		var filter_data =  Global.convertLayoutFilterToAPIFilter( { data: { filter_data: this.getValidSearchFilter() } } );

		var args = {};
		args.id = default_layout_id;
		args.data = {};
		args.data.filter_data = filter_data;
		args.data.mode = this.toggle_button.getValue();
		args.data.show_wage =  this.wage_btn.getValue();
		args.data.use_employee_timezone = this.timezone_btn.getValue();

		ProgressBar.showOverlay();
		var $this = this;
		this.user_generic_data_api.setUserGenericData( args, {
			onResult: function( res ) {

				if ( res && res.isValid() ) {
					$this.clearViewLayoutCache();
					$this.need_select_layout_name = layout_name;
					if ( !save_only ) {
                        $this.initLayout();
                    }
				}

			}
		} );
	}

	updateManualGrid() {
		var $this = this;
		var start_date_string = this.start_date_picker.getValue();
		var user_id = this.getSelectEmployee();
		ProgressBar.noProgressForNextCall();

		$this.handleOverrideUserPreferenceCookie( user_id );
		$this.api_timesheet.getTimeSheetData( user_id, start_date_string, {
			onResult: function( result ) {
				ProgressBar.removeNanobar();
				$this.full_timesheet_data = result.getResult();
				if ( $this.full_timesheet_data === true || !$this.full_timesheet_data.hasOwnProperty( 'timesheet_dates' ) ) {
					return;
				}
				$this.full_timesheet_data = $this.mergeJobQueueIntoTimeSheetData( $this.full_timesheet_data );
				$this.start_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.start_display_date );
				$this.end_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.end_display_date );
				$this.setDefaultMenu();
				$this.initInsideEditorData( true );
				$this.accumulated_time_source_map = {};
				$this.branch_source_map = {};
				$this.department_source_map = {};
				$this.job_source_map = {};
				$this.job_item_source_map = {};
				$this.punch_tag_source_map = {};
				$this.premium_source_map = {};
				$this.accumulated_total_grid_source_map = {};
				$this.accumulated_time_source = [];
				$this.branch_source = [];
				$this.department_source = [];
				$this.job_source = [];
				$this.job_item_source = [];
				$this.punch_tag_source = [];
				$this.premium_source = [];
				$this.accumulated_total_grid_source = [];
				$this.verification_grid_source = [];
				$this.onReloadSubGridResult( result );
			}
		} );
	}

	// Dev Note: TODO/REFACTOR: search() params here differ from BaseViewController.search() this could cause confusion or issues,
	// Currently means any search() calls in baseview using the callback param will not work here in TimeSheet.
	search( setDefaultMenu, force ) {

		this.accumulated_time_cells_array = []; //reset array since the select cell is clean
		this.premium_cells_array = []; //reset array since the select cell is clean
		this.accumulated_time_source_map = {};
		this.branch_source_map = {};
		this.department_source_map = {};
		this.job_source_map = {};
		this.job_item_source_map = {};
		this.punch_tag_source_map = {};
		this.premium_source_map = {};
		this.accumulated_total_grid_source_map = {};
		this.accumulated_time_source = [];
		this.branch_source = [];
		this.department_source = [];
		this.job_source = [];
		this.job_item_source = [];
		this.punch_tag_source = [];
		this.premium_source = [];
		this.absence_source = [];
		this.accumulated_total_grid_source = [];
		this.punch_note_grid_source = [];
		this.verification_grid_source = [];
		this.select_cells_Array = [];
		this.select_punches_array = [];
		this.branch_cell_count = 0;
		this.department_cell_count = 0;
		this.premium_cell_count = 0;
		this.job_cell_count = 0;
		this.task_cell_count = 0;
		this.punch_tag_cell_count = 0;
		this.absence_cell_count = 0;
		this.punch_note_account = 0;
		this.select_punches_array = [];

		var $this = this;
		var filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		var start_date_string = ( this.start_date_picker ) ? this.start_date_picker.getValue() : '';
		var user_id = this.getSelectEmployee();
		if ( !force ) {
			this.doNextIfNoValueChangeInManualGrid( doNext, reset );
		} else {
			doNext();
		}

		function reset() {
			$( '.button-rotate' ).removeClass( 'button-rotate' );
		}

		function doNext() {
			LocalCacheData.last_timesheet_selected_date = start_date_string;
			LocalCacheData.last_timesheet_selected_user = $this.getSelectEmployee();

			if ( LocalCacheData.last_timesheet_selected_user ) {
				LocalCacheData.last_timesheet_selected_show_wage = ( $this.wage_btn ) ? $this.wage_btn.getValue( true ) : false;
				LocalCacheData.last_timesheet_selected_timezone = ( $this.timezone_btn ) ? $this.timezone_btn.getValue( true ) : false;
				if ( $this.toggle_button ) {
					LocalCacheData.last_timesheet_selected_punch_mode = $this.toggle_button.getValue();
				}
				var args = { filter_data: filter_data };
				ProgressBar.showOverlay();
				//Error: TypeError: this.api_timesheet.getTimeSheetData is not a function in /interface/html5/framework/jquery.min.js?v=8.0.0-20141117-155153 line 2 > eval line 1885
				if ( !$this.api_timesheet || !$this.api_timesheet || typeof ( $this.api_timesheet.getTimeSheetData ) !== 'function' ) {
					return;
				}

				if ( user_id ) {
					$this.handleOverrideUserPreferenceCookie( user_id );
					$this.api_timesheet.getTimeSheetData( user_id, start_date_string, args, {
						onResult: function( result ) {
							$this.full_timesheet_data = result.getResult();
							if ( $this.full_timesheet_data === true || !$this.full_timesheet_data.hasOwnProperty( 'timesheet_dates' ) ) {
								return;
							}
							$this.full_timesheet_data = $this.mergeJobQueueIntoTimeSheetData( $this.full_timesheet_data );
							$this.start_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.start_display_date );
							$this.end_date = Global.strToDate( $this.full_timesheet_data.timesheet_dates.end_display_date );

							$this.buildCalendars();

							if ( setDefaultMenu ) {
								$this.setDefaultMenu( true );
							}
							$this.searchDone();
						}
					} );
				}
			}
		}
	}

	buildVerificationGrid() {
		var $this = this;

		var columns = [];
		if ( !Global.isSet( this.verification_grid ) ) {
			var grid = $( this.el ).find( '#verification_grid' );
			grid.attr( 'id', this.ui_id + '_verification_grid' );  //Grid's id is ScriptName + _grid
		}
		var grid_id = this.ui_id + '_verification_grid';

		var column = {
			name: 'pay_period',
			index: 'pay_period',
			label: $.i18n._( 'Pay Period' ),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column );

		column = {
			name: 'verification',
			index: 'verification',
			label: $.i18n._( 'Window' ),
			width: 100,
			sortable: false,
			title: false
		};
		columns.push( column );

		if ( this.verification_grid ) {
			this.verification_grid.unload();
			this.verification_grid = null;
			this.grid_dic.verification_grid = null;
		}

		this.verification_grid = this.grid_dic.verification_grid = new TTGrid( grid_id, {
			hoverrows: false,
			multiselectPosition: 'none',
			verticalResize: false,
			onResizeGrid: false,
			height: 0,
		}, columns );
	}

	buildPunchNoteGrid() {
		var $this = this;

		var columns = [];
		if ( !Global.isSet( this.punch_note_grid ) ) {
			var grid = $( this.el ).find( '#punch_note_grid' );

			//Grid's id is ScriptName + _grid
			grid.attr( 'id', this.ui_id + '_punch_note_grid' );
		}

		//if only put one column in grid. There is a UI bug
		var first_column = {
			name: 'invisible_column',
			index: 'invisible_column',
			label: ' ',
			width: 1,
			sortable: false,
			title: false,
			hidden: true
		};
		columns.push( first_column );

		var second_column = {
			name: 'note',
			index: 'note',
			label: ' ',
			width: 100,
			sortable: false,
			title: false,
			cellattr: function( index, value ) {
				return 'title="' + value + '"';
			}
		};
		columns.push( second_column );
		var grid_id = this.ui_id + '_punch_note_grid';
		if ( this.punch_note_grid ) {
			this.punch_note_grid.unload();
			this.punch_note_grid = null;
			this.grid_dic.punch_note_grid = null;
		}

		this.punch_note_grid = this.grid_dic.punch_note_grid = new TTGrid( grid_id, {
			hoverrows: false,
			multiselectPosition: 'none',
			verticalResize: false,
			height: 0,
			onResizeGrid: function() {
				$this.setPunchNoteGridWidth();
			}
		}, columns );

		this.setGridHeaderBar( 'punch_note_grid', 'Punch Notes' );

		// setGridHeaderBar() sets the width to 100vw (and sets grid title), but this causes issue as the grid is not full width of the screen unlike the others. Therefore, overide the default width of 100vw with 100%.
		// Related to issue #2712, and (via bisect) appears to be caused by 4e92c7ab463d9b3418735c0db302a50efc43e8bf when jquery.jqgrid.extend.js was removed in a JS upgrade fix.
		var table = $( this.grid_dic.punch_note_grid.grid ).parents( '.ui-jqgrid-view' ).find( '.ui-jqgrid-hbox table' ); //grab the hbox
		table.css( 'width', '100%' );
	}

	getAccumulatedTotalGridPayperiodHeader() {
		this.pay_period_header = $.i18n._( 'No Pay Period' );

		var pay_period_id = this.timesheet_verify_data.pay_period_id;

		if ( pay_period_id && this.pay_period_data ) {

			for ( var key in this.pay_period_data ) {
				var pay_period = this.pay_period_data[key];
				if ( pay_period.id === pay_period_id ) {
					var start_date = Global.strToDate( pay_period.start_date ).format();
					var end_date = Global.strToDate( pay_period.end_date ).format();
					this.pay_period_header = start_date + ' ' + $.i18n._( 'to' ) + ' ' + end_date;
					break;
				}
			}
		}
	}

	buildAccumulatedTotalGrid() {
		var $this = this;

		var columns = [];

		if ( !Global.isSet( this.accumulated_total_grid ) ) {
			var grid = $( this.el ).find( '#accumulated_total_grid' );

			grid.attr( 'id', this.ui_id + '_accumulated_total_grid' );	//Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_accumulated_total_grid' );
		}

		var width = 500;
		if ( this.wage_btn.getValue( true ) ) {
			width = 600;
		}

		var punch_column_width = 100;
		if ( this.wage_btn.getValue( true ) ) {
			punch_column_width = null;
		}

		var punch_in_out_column = {
			name: 'punch_info',
			index: 'punch_info',
			label: ' ',
			width: punch_column_width,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		columns.push( punch_in_out_column );

		var start_date_str = this.start_date.format( Global.getLoginUserDateFormat() );
		var end_date_str = this.end_date.format( Global.getLoginUserDateFormat() );

		this.getAccumulatedTotalGridPayperiodHeader();

		var column_width = 100;
		if ( this.wage_btn.getValue( true ) ) {
			column_width = 150;
		}
		var column_1 = {
			name: 'week',
			index: 'week',
			label: start_date_str + ' ' + $.i18n._( 'to' ) + ' ' + end_date_str,
			width: column_width,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};
		var column_2 = {
			name: 'pay_period',
			index: 'pay_period',
			label: this.pay_period_header,
			width: column_width,
			sortable: false,
			title: false,
			formatter: this.onCellFormat
		};

		columns.push( column_1 );
		columns.push( column_2 );

		var grid_id = this.ui_id + '_accumulated_total_grid';
		if ( Global.isSet( this.accumulated_total_grid ) == true ) {
			this.accumulated_total_grid.unload();
			this.accumulated_total_grid = null;
			this.grid_dic.accumulated_total_grid = null;
		}

		this.accumulated_total_grid = this.grid_dic.accumulated_total_grid = new TTGrid( grid_id, {
			hoverrows: false,
			multiselectPosition: 'none',
			verticalResize: false,
			onResizeGrid: false,
			width: width,
			height: 0,
		}, columns );

		var accumulated_total_grid_title = $( this.el ).find( '.accumulated-total-grid-title' );
		accumulated_total_grid_title.css( 'display', 'block' );
		this.setAccumulatedTotalGridPayPeriodHeaders( width );
	}

	//Override and disable as bindTimeSheetGridColumnEvents() is used at a different point instead.
	bindGridColumnEvents() {
	}

	//Bind column click event to change sort type and save columns to t_grid_header_array to use to set column style (asc or desc)
	bindTimeSheetGridColumnEvents() {
		var display_columns = this.grid.getGridParam( 'colModel' );

		//Exception taht display column not existed, not sure when this will happen, but may there will be a second time load if this happen
		if ( !display_columns ) {
			return;
		}

		var len = display_columns.length;

		this.t_grid_header_array = [];

		for ( var i = 0; i < len; i++ ) {
			var column_info = display_columns[i];
			var column_header = $( $( this.el ).find( '#gbox_' + this.ui_id + '_grid' ).find( 'div #jqgh_' + this.ui_id + '_grid_' + column_info.name ) );

			this.t_grid_header_array.push( column_header.TGridHeader() );
			column_header.bind( 'click', onColumnHeaderClick );
		}

		var $this = this;

		function onColumnHeaderClick( e ) {
			var field = $( this ).attr( 'id' );
			field = field.substring( 10 + $this.ui_id.length + 1, field.length );

			if ( field === 'cb' || field === 'punch_info' ) { //first column, check box column.
				return;
			}

			var date = Global.strToDate( field, $this.full_format );

			if ( date && date.getYear() > 0 ) {
				$this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
				$this.highLightSelectDay();
				//reLoadSubGridsSource() calls getTimeSheetTotalData and would cause duplicate API requests.
				//The reLoadSubGridsSource call is not needed required here, because it will be called in onCellSelect() which is triggered from the click event below.
				// $this.reLoadSubGridsSource();
				//select first punch cell when clicking the header row
				$( $( '.timesheet-grid tr#1 td' )[$( 'th.highlight-header' ).index()] ).click();
			}

		}
	}

	checkIsSelectedAbsenceCell( row_id, cell_index ) {
		for ( var i = 0, m = this.absence_select_cells_Array.length; i < m; i++ ) {
			var cell = this.absence_select_cells_Array[i];
			if ( cell.row_id == row_id && cell.cell_index === cell_index ) {
				return true;
			}
		}

		return false;
	}

	buildAbsenceGrid() {
		var $this = this;

		var grid_id = 'absence_grid';
		var title = $.i18n._( 'Absence' );

		if ( this[grid_id] ) {
			this[grid_id].unload();
			this[grid_id] = null;
			this.grid_dic[grid_id] = null;
		} else {
			var grid = $( this.el ).find( '#absence_grid' );

			grid.attr( 'id', this.ui_id + '_absence_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_absence_grid' );
		}

		this[grid_id] = this.grid_dic[grid_id] = new TTGrid( this.ui_id + '_absence_grid', {
			sortable: false,
			hoverrows: false,
			height: 0, //Start the grid height at 0 instead of parent height to avoid transient scrollbars from causing the width to be inconsistent across tables.
			ondblClickRow: function() {
				$this.onGridDblClickRow( 'absence' );
			},
			onRightClickRow: function( row_id, iRow, cell_index, e ) {
				if ( !$this.checkIsSelectedAbsenceCell( row_id, cell_index ) ) {
					var cell_val = $( e.target ).closest( 'td,th' ).html();
					$this.onCellSelect( 'absence_grid', row_id, cell_index, cell_val, this, e );
					$this.onSelectRow( 'absence_grid', row_id, this );

					if ( $( '.edit-view:visible' ).length == 0 ) {
						$this.setDefaultMenu();
					} else {
						$this.setEditMenu();
					}
				}
			},
			onCellSelect: function( row_id, cell_index, cell_val, e ) {
				if ( $( '.edit-view:visible' ).length == 0 ) {
					$this.setDefaultMenu();
				} else {
					$this.setEditMenu();
				}
			},
			onResizeGrid: function() {
				if ( $this.absence_grid && $this.absence_grid.getGridWidth() != $this.getTimeSheetWidth() ) {
					$this.absence_grid.setGridWidth( $this.getTimeSheetWidth() );
				}
			},
			beforeSelectRow: function( row_id, e ) {
				e.preventDefault();

				var cell_index = 0;
				if ( $( e.target ).attr( 'role' ) == 'gridcell' ) {
					cell_index = $( e.target ).index();
				} else {
					cell_index = $( e.target ).parents( 'td' ).index();
				}

				var cell_val = $( e.target ).text();

				$this.onCellSelect( 'absence_grid', row_id, cell_index, cell_val, this, e );
				$this.onSelectRow( 'absence_grid', row_id, this );

				return false;
			},
			multiselectPosition: 'none',
			winMultiSelect: false,
			verticalResize: false

		}, this.timesheet_columns, $.i18n._( 'Absence' ) );

		this.bindTimeSheetGridColumnEvents();
		this.setGridHeaderBar( grid_id, title );

		if ( this.grid_dic[grid_id] && this.grid_dic[grid_id].grid ) {
			this.grid_dic.absence_grid.grid.setGridWidth( this.getTimeSheetWidth() );
		}
	}

	checkIsSelectedPunchCell( row_id, cell_index ) {
		for ( var i = 0, m = this.select_cells_Array.length; i < m; i++ ) {
			var cell = this.select_cells_Array[i];
			if ( cell.row_id == row_id && cell.cell_index == cell_index ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * Paired secondary row coloring for timesheet
	 */
	colorTimeSheetRows() {
		var $trs = $( '.timesheet-grid-div .timesheet-grid tr' );
		var skips = 0;
		var colored = 0;
		for ( var i = 1; i < $trs.length - 1; i++ ) {
			if ( skips == 2 && colored < 2 ) {
				$( $trs[i] ).addClass( 'ui-priority-secondary' );
				colored++;
			} else if ( colored == 2 ) {
				skips = 1; //Resets to 1 because we're skipping this iteration.
				colored = 0;
			} else {
				skips++;
			}
		}
	}

	buildTimeSheetGrid() {
		var grid_id = this.ui_id + '_grid';
		var $this = this;

		if ( this.timesheet_grid && !this.first_build ) {
			$.jgrid.guid = 1;
		} else {
			if ( this.timesheet_grid ) {
				this.timesheet_grid.unload();
				this.timesheet_grid = null;
				$.jgrid.guid = 1;
			}
			var grid = $( this.el ).find( '#timesheet_grid' );
			grid.attr( 'id', grid_id );	//Grid's id is ScriptName + _grid

			var grid_setup_data = {
				container_selector: '.context-border',
				altRows: false,
				sortable: false,
				hoverrows: false,
				height: 0, //Start the grid height at 0 instead of parent height to avoid transient scrollbars from causing the width to be inconsistent across tables.
				ondblClickRow: function( row_id, row_index, cell_index, e ) {
					var row = $this.getRowData( 'timesheet_grid', row_id );

					//Make sure double click event doesn't get triggered on a request row (authorized/pending/declined)
					// as we are already in the process of navigating to that view, so it causes edit view and navigation operations to occur at the same time.
					if ( row.type !== TimeSheetViewController.REQUEST_ROW ) {
						$this.onGridDblClickRow();
					}
				},
				onRightClickRow: function( row_id, iRow, cell_index, e ) {
					if ( !$this.checkIsSelectedPunchCell( row_id, cell_index ) ) {
						var cell_val = $( e.target ).closest( 'td,th' ).html();
						$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );
						$this.onSelectRow( 'timesheet_grid', row_id, this );

						if ( $( '.edit-view:visible' ).length == 0 ) {
							$this.setDefaultMenu();
						} else {
							$this.setEditMenu();
						}
					}
				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					if ( $( '.edit-view:visible' ).length == 0 ) {
						$this.setDefaultMenu();
					} else {
						$this.setEditMenu();
					}
				},
				onResizeGrid: function() {
					if ( $this.getPunchMode() === 'manual' ) {
						if ( $this.editor ) {
							$this.setManualTimeSheetGridSize();
						}
					}

					$this.setGridSize();
					$this.setTimeSheetGridPayPeriodHeaders();
					$this.setTimeSheetGridHolidayHeaders();
				},
				gridComplete: function() {
					$this.colorTimeSheetRows();
				},
				beforeSelectRow: function( row_id, e ) {
					e.preventDefault();

					var cell_index = 0;
					if ( $( e.target ).attr( 'role' ) == 'gridcell' ) {
						cell_index = $( e.target ).index();
					} else {
						cell_index = $( e.target ).parents( 'td' ).index();
					}

					var cell_val = $( e.target ).text();

					$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );
					$this.onSelectRow( 'timesheet_grid', row_id, this );

					return false;
				},
				multiselectPosition: 'none',
				winMultiSelect: false,
				verticalResize: false
			};

			this.timesheet_grid = this.grid_dic.timesheet_grid = this.grid = new TTGrid( grid_id, grid_setup_data, this.timesheet_columns );
			this.grid_dic.timesheet_grid.grid.setGridWidth( this.getTimeSheetWidth() );
		}

		this.grid_div.scroll( function( e ) {
			$this.scroll_position = $this.grid_div.scrollTop();
		} );
	}

	onGridDblClickRow( name ) {
		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var len = context_menu_array.length;
		var need_break = false;
		for ( var i = 0; i < len; i++ ) {
			if ( need_break ) {
				break;
			}
			let id = context_menu_array[i].id;
			switch ( id ) {
				case 'edit':
					if ( !context_menu_array[i].disabled && context_menu_array[i].visible ) {
						ProgressBar.showOverlay();
						this.onEditClick();
						return;
					}
					break;
			}
		}
		for ( var i = 0; i < len; i++ ) {
			if ( need_break ) {
				break;
			}
			let id = context_menu_array[i].id;
			switch ( id ) {
				case 'view':
					need_break = true;
					if ( !context_menu_array[i].disabled && context_menu_array[i].visible ) {
						ProgressBar.showOverlay();
						this.onViewClick();
						return;
					}
					break;
			}
		}
		for ( var i = 0; i < len; i++ ) {
			let id = context_menu_array[i].id;
			switch ( id ) {
				case 'add_absence':
				case 'add':
				case 'add_punch':
					// There are 2 add icons, one for punch and one for absence.
					// We must ensure to check the right one to provide permissions for the add click or absence will be allowed based on the punch permissions
					if ( name == 'absence' && id != 'add_absence' ) {
						continue;
					}

					if ( !context_menu_array[i].disabled && context_menu_array[i].visible ) {
						if ( this.isPunchCells() ) {
							ProgressBar.showOverlay();
							this.onAddClick();
						}
						return;
					}
					break;
			}
		}
		if ( !this.addPermissionValidate( 'punch' ) ) {
			//Regular employees open In/Out view when clicking a timesheet cell.
			MenuManager.openSelectView( 'InOut' );
			return;
		}
	}

	isPunchCells() {
		var result = false;
		var cell = this.select_cells_Array && this.select_cells_Array.length > 0 && this.select_cells_Array[0];
		var row = cell && this.timesheet_data_source[parseInt( cell.row_id ) - 1];
		if ( row && row.type === TimeSheetViewController.PUNCH_ROW ) {
			result = true;
		} else if ( this.absence_select_cells_Array && this.absence_select_cells_Array.length > 0 ) {
			result = true;
		}
		return result;
	}

	buildAccumulatedGrid() {
		var $this = this;

		var grid_id = 'accumulated_time_grid';
		var title = $.i18n._( 'Accumulated Time' );

		if ( this[grid_id] ) {
			//Commenting out the next line replicates TypeError: Failed to execute 'replaceChild' on 'Node': parameter 2 is not of type 'Node'. when using Date left arrow on timesheet.
			//  Also happens when clicking the top-right in-app refresh button too.
			this[grid_id].unload();
			this[grid_id] = null;
			this.grid_dic[grid_id] = null;
		}

		this[grid_id] = this.grid_dic[grid_id] = new TTGrid( 'accumulated_time_grid', {
			sortable: false,
			hoverrows: false,
			height: 0, //Start the grid height at 0 instead of parent height to avoid transient scrollbars from causing the width to be inconsistent across tables.
			ondblClickRow: function() {
				$this.onAccumulatedTimeClick();
			},
			onRightClickRow: function( row_id, iRow, cell_index, e ) {
				var cell_val = $( e.target ).closest( 'td,th' ).html();
				$this.onCellSelect( 'accumulated_grid', row_id, cell_index, cell_val, this, e );
				$this.onSelectRow( 'accumulated_grid', row_id, this );
			},
			onCellSelect: function( row_id, cell_index, cell_val, e ) {
				$this.setDefaultMenu();
			},
			onResizeGrid: function() {
				if ( $this.grid_dic['accumulated_time_grid'] ) {
					$this.grid_dic['accumulated_time_grid'].grid.setGridWidth( $this.getTimeSheetWidth() );
				}
			},
			beforeSelectRow: function( row_id, e ) {
				e.preventDefault();

				var cell_index = 0;
				if ( $( e.target ).attr( 'role' ) == 'gridcell' ) {
					cell_index = $( e.target ).index();
				} else {
					cell_index = $( e.target ).parents( 'td' ).index();
				}

				var cell_val = $( e.target ).text();

				$this.onCellSelect( 'accumulated_grid', row_id, cell_index, cell_val, this, e );
				$this.onSelectRow( 'accumulated_grid', row_id, this );

				return false;
			},
			multiselectPosition: 'none',
			winMultiSelect: false,
			verticalResize: false

		}, this.timesheet_columns );

		this.setGridHeaderBar( grid_id, title );

		if ( this.grid_dic[grid_id] && this.grid_dic[grid_id].grid ) {
			this.grid_dic[grid_id].grid.setGridWidth( this.getTimeSheetWidth() );
		}
	}

	buildSubGrid( grid_id, title ) {
		var $this = this;

		var html_grid_id = this.ui_id + '_' + grid_id;
		if ( !Global.isSet( this[grid_id] ) ) {
			var grid = $( this.el ).find( '#' + grid_id );
			grid.attr( 'id', html_grid_id );	//Grid's id is ScriptName + _grid
		}

		if ( this[grid_id] ) {
			this[grid_id].unload();
			this[grid_id] = null;
			this.grid_dic[grid_id] = null;
		}

		if ( grid_id === 'premium_grid' ) {
			this[grid_id] = this.grid_dic[grid_id] = new TTGrid( html_grid_id, {
				hoverrows: false,
				multiselectPosition: 'none',
				winMultiSelect: false,
				height: 0, //Start the grid height at 0 instead of parent height to avoid transient scrollbars from causing the width to be inconsistent across tables.
				onSelectRow: function( row_id, flag, e ) {
					$this.onSelectRow( 'premium_grid', row_id, this );
				},
				onRightClickRow: function( row_id, iRow, cell_index, e ) {
					var cell_val = $( e.target ).closest( 'td,th' ).html();
					$this.onCellSelect( 'premium_grid', row_id, cell_index, cell_val, this, e );
					$this.onSelectRow( 'premium_grid', row_id, this );
				},
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					$this.unsetSelectedCells( 'timesheet_grid' );
					$this.unsetSelectedCells( 'absence_grid' );
					$this.onCellSelect( 'premium_grid', row_id, cell_index, cell_val, this, e );
					$this.select_punches_array = [];
					$this.setDefaultMenu();
				},
				ondblClickRow: function() {
					if ( grid_id === 'premium_grid' ) {
						$this.onAccumulatedTimeClick();
					}
				},
				onResizeGrid: function() {
					if ( $this.grid_dic[grid_id] ) {
						$this.grid_dic[grid_id].grid.setGridWidth( $this.getTimeSheetWidth() );
					}
				},
				verticalResize: false
			}, this.timesheet_columns );

			//subgrids might not be rendered, so we need to check for them in the grid_dic first.
			if ( this[grid_id] && this[grid_id].grid ) {
				this[grid_id].grid.addClass( 'premium-grid' );
			}
		} else {
			this[grid_id] = this.grid_dic[grid_id] = new TTGrid( html_grid_id, {
				hoverrows: false,
				multiselectPosition: 'none',
				winMultiSelect: false,
				height: 0, //Start the grid height at 0 instead of parent height to avoid transient scrollbars from causing the width to be inconsistent across tables.
				onCellSelect: function( row_id, cell_index, cell_val, e ) {
					$this.unsetSelectedCells( 'timesheet_grid' );
					$this.unsetSelectedCells( 'absence_grid' );

					$this.onCellSelect( grid_id, row_id, cell_index, cell_val, this, e );
					$this.select_punches_array = [];
					$this.setDefaultMenu();
				},
				onResizeGrid: function() {
					if ( $this.grid_dic[grid_id] ) {
						$this.grid_dic[grid_id].grid.setGridWidth( $this.getTimeSheetWidth() );
					}
				},
				verticalResize: false
			}, this.timesheet_columns );
		}

		this.setGridHeaderBar( grid_id, title );

		//this loop hits all possible grids. subgrids might not be rendered, so we need to check for them in the grid_dic first.
		if ( this.grid_dic[grid_id] && this.grid_dic[grid_id].grid ) {
			this.grid_dic[grid_id].grid.setGridWidth( this.getTimeSheetWidth() );
		}
	}

	setGridSExpendOrCollapseStatus( grid_id, title ) {
		if ( this.grid_dic[grid_id] ) {
			var grid = this.grid_dic[grid_id].grid;
			var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
			var title_bar = table.find( '.title-bar' );
			this.setGridHeight( grid_id );

			if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] !== true ) {
				grid.setGridHeight( 0 );
			}

			this.updateGridHeaderBar( grid_id, title );
		}
	}

	//Show expend and collapse button in grid title bar
	setGridExpendButton( grid_id, title ) {
		var $this = this;
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		var title_bar = table.find( '.title-bar' );

		if ( title_bar.find( '.grid-expend-btn' ).length === 0 ) { //prevent doubling up of expand arrows.
			var img = $( '<img>' );
			img.addClass( 'grid-expend-btn' );

			if ( !Global.isSet( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] ) ||
				LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ) {

				img.attr( 'src', Global.getRealImagePath( 'images/big_collapse.png' ) );
				LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = true;

			} else {
				img.attr( 'src', Global.getRealImagePath( 'images/big_expand.png' ) );
				LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = false;
			}

			title_bar.append( img );

			this.setGridSExpendOrCollapseStatus( grid_id, title );

			img.click( function( e ) {

				if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ) {
					$( this ).attr( 'src', Global.getRealImagePath( 'images/big_expand.png' ) );
					LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = false;
					$this.setGridSExpendOrCollapseStatus( grid_id, title );
				} else {
					$( this ).attr( 'src', Global.getRealImagePath( 'images/big_collapse.png' ) );
					LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] = true;
					$this.setGridSExpendOrCollapseStatus( grid_id, title );

				}
			} );
		}
	}

	updateGridHeaderBar( grid_id, description ) {
		var label = description;
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_' + grid_id + ']' )[0] );
		var title_span = table.find( '.title-span' );
		var count = 0;

		if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] !== true ) {
			switch ( grid_id ) {
				case 'branch_grid':
					label = label + ' (' + ( this.branch_cell_count ) + ')';
					break;
				case 'department_grid':
					label = label + ' (' + ( this.department_cell_count ) + ')';
					break;
				case 'job_item_grid':
					label = label + ' (' + ( this.task_cell_count ) + ')';
					break;
				case 'punch_tag_grid':
					label = label + ' (' + ( this.punch_tag_cell_count ) + ')';
					break;
				case 'job_grid':
					label = label + ' (' + ( this.job_cell_count ) + ')';
					break;
				case 'premium_grid':
					label = label + ' (' + ( this.premium_cell_count ) + ')';
					break;
				case 'absence_grid':
					label = label + ' (' + ( this.absence_cell_count ) + ')';
					break;
				case 'punch_note_grid':
					label = label + ' (' + ( this.punch_note_account ) + ')';
					break;
			}
		}

		title_span.text( label );
	}

	setGridHeaderBar( grid_id, label ) {
		var table = $( this.grid_dic[grid_id].grid ).parents( '.ui-jqgrid-view' ).find( '.ui-jqgrid-hbox table' ); //grab the hbox
		//table.empty();
		table.css( 'width', '100vw' ); //set default width of timesheet tables to the width of the screen to set the header widths.

		var label = $.i18n._( label );

		table.find( 'tr:first' ).hide();
		var title_bar = $( '<div class=\'title-bar\'><span class=\'title-span\'>' + label + '</span></div>' );
		table.append( title_bar );
	}

	buildManualTimeSheetsColumns() {
		this.day_dic = {};
		for ( var i = 0; i < 7; i++ ) {
			var current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );
			var day_text = current_date.format( this.day_format );
			var date_text = current_date.format( this.date_format );
			this.day_dic['day_' + i] = { value: day_text + '<br>' + date_text, field: current_date.format() };
		}
	}

	getManualTimeSheetData( callBack ) {
		var $this = this;

		var user_id = this.getSelectEmployee();
		if ( user_id ) {
			var args = {};
			args.filter_data = {
				user_id: user_id,
				object_type_id: 10,
				start_date: this.start_date.format(),
				end_date: this.end_date.format()
			};
			args.filter_columns = {
				'id': true,
				'date_stamp': true,
				'total_time': true,
				'object_type': true,
				'name': true,
				'branch_id': true,
				'department_id': true,
				'branch': true,
				'department': true,
				'job': true,
				'job_item': true,
				'job_id': true,
				'job_item_id': true,
				'punch_tag': true,
				'punch_tag_id': true,
				'note': true,
				'override': true
			};
			this.api_user_date_total.getUserDateTotal( args, true, {
				onResult: function( result ) {
					$this.manual_timesheet_data = result.getResult();
					callBack();
				}
			} );
		}
	}

	buildManualTimeSheetData() {
		this.time_sheet_data_overrode_true_map = {};
		this.time_sheet_data_overrode_false_map = {};
		var sort_by_fields = ['branch_id', 'department_id', 'job_id', 'job_item_id', 'punch_tag_id'],
			manual_timesheet_data_group_array = [],
			override_true_array = [],
			override_false_array = [];
		this.manual_timesheet_data.sort( Global.m_sort_by( sort_by_fields ) );
		manual_timesheet_data_group_array = _.groupBy( this.manual_timesheet_data, 'override' );
		override_true_array = manual_timesheet_data_group_array[true];
		override_false_array = manual_timesheet_data_group_array[false];
		doNext( override_true_array, this.time_sheet_data_overrode_true_map );
		doNext( override_false_array, this.time_sheet_data_overrode_false_map );

		function doNext( manual_timesheet_data, target_map ) {
			!manual_timesheet_data && ( manual_timesheet_data = [] );
			for ( var i = 0, m = manual_timesheet_data.length; i < m; i++ ) {
				var data = manual_timesheet_data[i];

				var key = data.branch_id + '-' + data.department_id + '-' + data.job_id + '-' + data.job_item_id;
				if ( !target_map[key] ) {
					target_map[key] = {};
					target_map[key].branch_id = data.branch_id;
					target_map[key].department_id = data.department_id;
					target_map[key].job_id = data.job_id;
					target_map[key].job_item_id = data.job_item_id;
					target_map[key].punch_tag_id = data.punch_tag_id;
					target_map[key].branch = data.branch;
					target_map[key].department = data.department;
					target_map[key].job = data.job;
					target_map[key].job_item = data.job_item;
					target_map[key].punch_tag = data.punch_tag;
					target_map[key].override = data.override;
					target_map[key][data.date_stamp] = data;
				} else if ( target_map[key][data.date_stamp] ) {
					// If already has data in this day, create next row.
					var j = 1;
					while ( true ) {
						if ( !target_map[key + '-' + j] ) {
							target_map[key + '-' + j] = {};
							target_map[key + '-' + j].branch_id = data.branch_id;
							target_map[key + '-' + j].department_id = data.department_id;
							target_map[key + '-' + j].job_id = data.job_id;
							target_map[key + '-' + j].job_item_id = data.job_item_id;
							target_map[key + '-' + j].punch_tag_id = data.punch_tag_id;
							target_map[key + '-' + j].branch = data.branch;
							target_map[key + '-' + j].department = data.department;
							target_map[key + '-' + j].job = data.job;
							target_map[key + '-' + j].punch_tag = data.punch_tag;
							target_map[key + '-' + j].override = data.override;
							target_map[key + '-' + j][data.date_stamp] = data;
							break;
						} else if ( !target_map[key + '-' + j][data.date_stamp] ) {
							target_map[key + '-' + j][data.date_stamp] = data;
							break;
						} else {
							j = j + 1;
						}
					}
				} else {
					target_map[key][data.date_stamp] = data;
				}
			}

		}
	}

	buildManualNotes( widgets, data ) {
		for ( let widget_key in widgets ) {
			let item = widgets[widget_key];

			//Notes may be placed on days that do not have a user_date_total record yet. We need to check this is a day widget and that no user_date_total record exists for that day.
			//In which case we need to create a user_date_total for that day and in the appropriate place.
			let has_no_matching_user_date_total_record = false;
			for ( let i = 0; i < 7; i++ ) {
				let current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) ).format();

				if ( typeof item.getField === 'function' && item.getField() === current_date ) {
					if ( !data[current_date] ) {
						has_no_matching_user_date_total_record = true;
					}
					break;
				}
			}

			if ( typeof item.getField === 'function' && item.getEnabled() && ( has_no_matching_user_date_total_record || ( data[item.getField()] && data[item.getField()].id ) ) ) {
				let id = '';
				if ( has_no_matching_user_date_total_record ) {
					id = 'id-' + TTUUID.generateUUID();
				} else {
					id = 'id-' + data[item.getField()].id;
				}

				if ( document.getElementById( id ) ) {
					continue; //Note component already mounted.
				}
				let note = '';
				if ( !has_no_matching_user_date_total_record ) {
					note = data[item.getField()].note || '';
				}

				let note_mount_point = document.createElement( 'div' );
				note_mount_point.id = id;
				note_mount_point.className = 'timesheet-manual-note';

				item[0].parentNode.prepend( note_mount_point );
				item[0].style.position = 'inherit';

				TTVueUtils.mountComponent( id, TimeSheetNote, {
					id: id,
					starting_note: note,
					field_reference: item
				} );

				this.manual_note_component_ids.push( id );

				this.event_bus.on( id, 'saveNote', ( event_data ) => {
					if ( !data[item.getField()] ) {
						//Note is being added to a cell with no user_date_total record.
						data[item.getField()] = {};
					}
					data[item.getField()].note = event_data.note;
					widgets.is_changed = true;
					this.onSaveClick();
				}, TTEventBusStatics.AUTO_CLEAR_ON_EXIT );
			}
		}
	}

	initInsideEditorData( updateExistedCell ) {
		if ( !updateExistedCell && this.manual_note_component_ids.length > 0 ) {
			this.manual_note_component_ids.forEach( ( id ) => {
				TTVueUtils.unmountComponent( id );
			} );
		}

		var $this = this;
		for ( var key in this.day_dic ) {
			this.$( '#' + key + '_date' ).html( this.day_dic[key].value );
			this.$( '#' + key + '_date' ).addClass( 'manual_grid_day_' + Global.strToDate( this.day_dic[key].field ).format( this.full_format ) );
			this.$( '#' + key + '_date' ).attr( 'current_date', 'manual_grid_day_' + Global.strToDate( this.day_dic[key].field ).format( this.full_format ) );
			this.$( '#' + key + '_date' ).unbind( 'click' ).bind( 'click', function( e ) {
				var target = e.currentTarget;
				var field = $( target ).attr( 'current_date' );
				field = field.substring( 16, field.length );
				var date = Global.strToDate( field, $this.full_format );
				if ( date && date.getYear() > 0 ) {
					$this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
					$this.highLightSelectDay();
					$this.reLoadSubGridsSource();
				}
			} );
			this.$( '.is-saving-manual-grid' ).removeClass( 'is-saving-manual-grid' );
		}
		if ( this.is_auto_switch ) {
			this.is_auto_switch = false;
			doNext();
		} else {
			this.getManualTimeSheetData( function() {
				doNext();
			} );
		}

		function doNext() {
			$this.is_saving_manual_grid = false;
			$this.setDefaultMenu();
			if ( !updateExistedCell ) {
				$this.editor.removeAllRows();
				if ( $this.manual_timesheet_data.length > 0 ) {
					$this.buildManualTimeSheetData();
					_.map( $this.time_sheet_data_overrode_false_map, function( data ) {
						$this.editor.addRow( data );
					} );
					_.map( $this.time_sheet_data_overrode_true_map, function( data ) {
						$this.editor.addRow( data );
					} );
					if ( _.isEmpty( $this.time_sheet_data_overrode_true_map ) ) {
						$this.editor.addRow();
					}
				} else {
					$this.editor.addRow();
				}
			} else {
				if ( $this.manual_timesheet_data.length > 0 ) {
					$this.buildManualTimeSheetData();
					for ( var map_key in $this.time_sheet_data_overrode_true_map ) {
						var data = $this.time_sheet_data_overrode_true_map[map_key];
						for ( var map_key_2 in data ) {
							var item = data[map_key_2];
							if ( Array.isArray( item ) || !_.isObject( item ) ) {
								continue;
							}

							var key = $this.generateManualTimeSheetRecordKey( item );
							var item_id_key = item.id + '-' + key;

							//Check to see if the record with no ID (pre-save) exists, and if so update it, or replace it with the saved record.
							if ( $this.manual_grid_records_map[item_id_key] ) {
								$this.manual_grid_records_map[item_id_key][item.date_stamp].setValue( item.total_time );
								$this.buildManualNotes( $this.manual_grid_records_map[item_id_key], data );
							} else if ( $this.manual_grid_records_map[key] ) {
								$this.manual_grid_records_map[key].current_edit_item[item.date_stamp] = item;
								$this.manual_grid_records_map[key][item.date_stamp].setValue( item.total_time );
								$this.buildManualNotes( $this.manual_grid_records_map[key], data );
							}
						}
					}
				}
			}
			if ( $this.save_manual_grid_after_save ) {
				$this.autoSaveManualPunch();
				$this.save_manual_grid_after_save = false;
				return;
			}
		}
	}

	insideEditorAddRow( data, index ) {
		var $this = this;
		if ( Global.getProductEdition() >= 20 ) {
			var job_item_api = TTAPI.APIJobItem;
			var job_api = TTAPI.APIJob;
			var punch_tag_api = TTAPI.APIPunchTag;
			var department_api = TTAPI.APIDepartment;
		}
		var args;
		if ( !data ) {
			data = {};
			if ( index >= 0 ) {
				var widget_row = this.rows_widgets_array[index - 3];
				if ( this.parent_controller.show_branch_ui ) {
					if ( widget_row.branch_id ) {
						data.branch_id = widget_row.branch_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.branch_id ) {
						data.branch_id = widget_row.current_edit_item.branch_id;
					} else {
						data.branch_id = false;
					}
				}
				if ( this.parent_controller.show_department_ui ) {
					if ( widget_row.department_id ) {
						data.department_id = widget_row.department_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.department_id ) {
						data.department_id = widget_row.current_edit_item.department_id;
					} else {
						data.department_id = false;
					}
				}
				if ( this.parent_controller.show_job_ui && Global.getProductEdition() >= 20 ) {
					if ( widget_row.job_id ) {
						data.job_id = widget_row.job_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.job_id ) {
						data.job_id = widget_row.current_edit_item.job_id;
					} else {
						data.job_id = false;
					}
				}
				if ( this.parent_controller.show_job_item_ui && Global.getProductEdition() >= 20 ) {
					if ( widget_row.job_item_id ) {
						data.job_item_id = widget_row.job_item_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.job_item_id ) {
						data.job_item_id = widget_row.current_edit_item.job_item_id;
					} else {
						data.job_item_id = false;
					}
				}
				if ( this.parent_controller.show_punch_tag_ui && Global.getProductEdition() >= 20 ) {
					if ( widget_row.punch_tag_id ) {
						data.punch_tag_id = widget_row.punch_tag_id.getValue();
					} else if ( widget_row.current_edit_item && widget_row.current_edit_item.punch_tag_id ) {
						data.punch_tag_id = widget_row.current_edit_item.punch_tag_id;
					} else {
						data.punch_tag_id = false;
					}
				}
			}

		}
		var row = this.getRowRender(); //Get Row render
		var render = this.getRender(); //get render, should be a table
		var widgets = {}; //Save each row's widgets
		var form_item_input;
		//Build row widgets
		//Branch
		if ( this.parent_controller.show_branch_ui ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( { field: 'branch' } );
				form_item_input.setValue( data.branch );
				row.children().eq( 2 ).append( form_item_input );
			} else {
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					api_class: TTAPI.APIBranch,
					width: 90,
					layout_name: 'global_branch',
					show_search_inputs: true,
					set_empty: true,
					field: 'branch_id',
					is_static_width: true
				} );
				widgets[form_item_input.getField()] = form_item_input;
				args = {};
				args.filter_data = { user_id: this.parent_controller.getSelectEmployee() };
				form_item_input.setDefaultArgs( args );
				var branch_id = data.hasOwnProperty( 'branch_id' ) ? data.branch_id : this.parent_controller.getSelectEmployee( true ).default_branch_id;
				form_item_input.setValue( data.hasOwnProperty( 'branch_id' ) ? data.branch_id : this.parent_controller.getSelectEmployee( true ).default_branch_id );
				row.children().eq( 2 ).append( form_item_input );
			}
		} else {
			row.children().eq( 2 ).hide();
		}
		//Department
		if ( this.parent_controller.show_department_ui ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( { field: 'department' } );
				form_item_input.setValue( data.department );
				row.children().eq( 3 ).append( form_item_input );
			} else {
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					api_class: TTAPI.APIDepartment,
					width: 90,
					layout_name: 'global_department',
					show_search_inputs: true,
					set_empty: true,
					field: 'department_id',
					is_static_width: true
				} );
				widgets[form_item_input.getField()] = form_item_input;
				args = {};
				args.filter_data = { user_id: this.parent_controller.getSelectEmployee(), branch_id: branch_id };
				form_item_input.setDefaultArgs( args );
				var department_id = data.hasOwnProperty( 'department_id' ) ? data.department_id : this.parent_controller.getSelectEmployee( true ).default_department_id;
				form_item_input.setValue( data.hasOwnProperty( 'department_id' ) ? data.department_id : this.parent_controller.getSelectEmployee( true ).default_department_id );
				row.children().eq( 3 ).append( form_item_input );
			}
		} else {
			row.children().eq( 3 ).hide();
		}
		//Job
		if ( this.parent_controller.show_job_ui && Global.getProductEdition() >= 20 ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( { field: 'job' } );
				form_item_input.setValue( data.job );
				row.children().eq( 4 ).append( form_item_input );
			} else {
				var job_form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				job_form_item_input.AComboBox( {
					api_class: TTAPI.APIJob,
					width: 90,
					layout_name: 'global_job',
					show_search_inputs: true,
					set_empty: true,
					always_include_columns: ['group_id'],
					field: 'job_id',
					is_static_width: true,
					setRealValueCallBack: ( function( val ) {
						if ( val ) {
							job_coder.setValue( val.manual_id );
						}
					} )
				} );
				widgets[job_form_item_input.getField()] = job_form_item_input;
				// Set default args
				args = {};
				args.filter_data = { status_id: 10, user_id: this.parent_controller.getSelectEmployee() };
				job_form_item_input.setDefaultArgs( args );
				var job_id = data.hasOwnProperty( 'job_id' ) ? data.job_id : this.parent_controller.getSelectEmployee( true ).default_job_id;
				job_form_item_input.setValue( job_id );
				var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				job_coder.TTextInput( { field: 'job_quick_search', disable_keyup_event: true, width: 30 } );
				job_coder.css( 'display', 'inline-block' );
				job_form_item_input.css( 'display', 'inline-block' );
				row.children().eq( 4 ).append( job_coder );
				row.children().eq( 4 ).append( job_form_item_input );
				job_coder.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					onJobQuickSearch( target.getField(), target.getValue() );
				} );
			}
		} else {
			row.children().eq( 4 ).hide();
		}

		//Task
		if ( this.parent_controller.show_job_item_ui && Global.getProductEdition() >= 20 ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( { field: 'job_item' } );
				form_item_input.setValue( data.job_item );
				row.children().eq( 5 ).append( form_item_input );
			} else {
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					api_class: TTAPI.APIJobItem,
					width: 90,
					layout_name: 'global_job_item',
					show_search_inputs: true,
					set_empty: true,
					always_include_columns: ['group_id'],
					field: 'job_item_id',
					is_static_width: true,
					setRealValueCallBack: ( function( val ) {
						if ( val ) {
							job_item_coder.setValue( val.manual_id );
						}
					} )
				} );
				args = {};
				args.filter_data = { status_id: 10, job_id: job_id };
				form_item_input.setDefaultArgs( args );
				var job_item_id = data.hasOwnProperty( 'job_item_id' ) ? data.job_item_id : this.parent_controller.getSelectEmployee( true ).default_job_item_id;
				form_item_input.setValue( data.hasOwnProperty( 'job_item_id' ) ? data.job_item_id : this.parent_controller.getSelectEmployee( true ).default_job_item_id );
				var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true, width: 30 } );
				widgets[form_item_input.getField()] = form_item_input;
				job_item_coder.css( 'display', 'inline-block' );
				form_item_input.css( 'display', 'inline-block' );
				row.children().eq( 5 ).append( job_item_coder );
				row.children().eq( 5 ).append( form_item_input );
				job_item_coder.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					onJobQuickSearch( target.getField(), target.getValue() );
				} );
			}
		} else {
			row.children().eq( 5 ).hide();
		}

		//Punch Tag
		if ( this.parent_controller.show_punch_tag_ui && Global.getProductEdition() >= 20 ) {
			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
				form_item_input.TText( { field: 'punch_tag' } );
				form_item_input.setValue( data.punch_tag );
				row.children().eq( 6 ).append( form_item_input );
			} else {
				form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
				form_item_input.AComboBox( {
					api_class: TTAPI.APIPunchTag,
					allow_multiple_selection: true,
					width: 90,
					layout_name: 'global_punch_tag',
					show_search_inputs: true,
					set_empty: true,
					always_include_columns: ['group_id'],
					field: 'punch_tag_id',
					is_static_width: true
/*					setRealValueCallBack: ( function( val ) {
						if ( val ) {
							punch_tag_coder.setValue( val.manual_id );
						}
					} )*/
				} );
				args = {};
				args.filter_data = {
					status_id: 10,
					user_id: this.parent_controller.getSelectEmployee( true ).id,
					branch_id: branch_id,
					department_id: department_id,
					job_id: job_id,
					job_item_id: job_item_id
				};
				form_item_input.setDefaultArgs( args );
				form_item_input.setValue( data.hasOwnProperty( 'punch_tag_id' ) ? data.punch_tag_id : this.parent_controller.getSelectEmployee( true ).default_punch_tag_id );
				//var punch_tag_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				//punch_tag_coder.TTextInput( { field: 'punch_tag_quick_search', disable_keyup_event: true, width: 30 } );
				widgets[form_item_input.getField()] = form_item_input;
				//punch_tag_coder.css( 'display', 'inline-block' );
				form_item_input.css( 'display', 'inline-block' );
				//row.children().eq( 6 ).append( punch_tag_coder );
				row.children().eq( 6 ).append( form_item_input );
/*				punch_tag_coder.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
					onPunchTagQuickSearch( target.getField(), target.getValue() );
				} );*/
			}
		} else {
			row.children().eq( 6 ).hide();
		}

		//day 0
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_0'].field,
			width: 55,
			mode: 'time_unit',
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 7 ).append( form_item_input );

		//day 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_1'].field,
			width: 55,
			mode: 'time_unit',
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 8 ).append( form_item_input );

		//day 2
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_2'].field,
			width: 55,
			mode: 'time_unit',
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 9 ).append( form_item_input );

		//day 3
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_3'].field,
			width: 55,
			mode: 'time_unit',
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 10 ).append( form_item_input );

		//day 4
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_4'].field,
			width: 55,
			mode: 'time_unit',
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 11 ).append( form_item_input );

		//day 5
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_5'].field,
			width: 55,
			mode: 'time_unit',
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 12 ).append( form_item_input );

		//day 6
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( {
			field: this.parent_controller.day_dic['day_6'].field,
			width: 55,
			mode: 'time_unit',
			need_parser_sec: true,
			do_validate: false
		} );
		form_item_input.setValue( data[form_item_input.getField()] ? data[form_item_input.getField()].total_time : 0 );
		widgets[form_item_input.getField()] = form_item_input;
		row.children().eq( 13 ).append( form_item_input );
		if ( data.hasOwnProperty( 'override' ) && !data.override ) {
			row.children().eq( 1 ).children().hide();
		}

		var disable_plus = false;
		var disable_minus = false;
		var disable_dropdowns = false;

		for ( var key in widgets ) {
			var item = widgets[key];

			//disable if all pay_periods visible are not open.
			var invalid = 0;

			for ( var n in this.parent_controller.full_timesheet_data.pay_period_data ) {
				if ( this.parent_controller.full_timesheet_data.pay_period_data[n].status_id != 10 && this.parent_controller.full_timesheet_data.pay_period_data[n].status_id != 30 ) {
					invalid++;
				}
			}

			if ( invalid > 0 && invalid == Object.keys( this.parent_controller.full_timesheet_data.pay_period_data ).length ) {
				if ( this.parent_controller.full_timesheet_data.punch_data.length != 0 ) {
					disable_plus = true;
					disable_minus = true;
					if ( Object.keys( data ).length == 0 ) {
						return; //don't show a blank row if all pay periods are closed
					}
				}
			}

			//disable fields under closed pay periods
			if ( this.parent_controller.full_timesheet_data.pay_period_data[this.parent_controller.full_timesheet_data.timesheet_dates.pay_period_date_map[item.getField()]] ) {
				var field_pay_period_status = parseInt( this.parent_controller.full_timesheet_data.pay_period_data[this.parent_controller.full_timesheet_data.timesheet_dates.pay_period_date_map[item.getField()]].status_id );
				if ( field_pay_period_status != 10 && field_pay_period_status != 30 ) {
					item.setEnabled && item.setEnabled( false );
					if ( item.getField() != 'branch_id' && item.getField() != 'department_id' && item.getField() != 'job_id' && item.getField() != 'job_item_id' && item.getField() != 'punch_tag_id' && typeof data[item.getField()] != 'undefined' ) {
						//only disable dropdowns if there is data in the disabled fields
						disable_dropdowns = true;
						disable_minus = true;
					}
				}
			}

			if ( data.hasOwnProperty( 'override' ) && !data.override ) {
				item.setEnabled && item.setEnabled( false );
				item.getValue() > 0 && item.hasClass( 't-text-input' ) >= 0 && item.css( 'color', 'red' );
			}
			item.unbind( 'formItemChange' ).bind( 'formItemChange', function( e, target ) {
				target.is_changed = true;
				if ( target.getField() === 'job_id' ) {
					widgets.is_changed = true;
					job_coder.setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					setJobItemValueWhenJobChanged( target.getValue( true ) );
					setPunchTagValuesWhenCriteriaChanged( data );
				} else if ( target.getField() === 'job_item_id' ) {
					widgets.is_changed = true;
					job_item_coder.setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					setPunchTagValuesWhenCriteriaChanged( data );
				} else if ( target.getField() === 'branch_id' ) {
					widgets.is_changed = true;
					if ( ( Global.getProductEdition() >= 20 ) ) {
						setDepartmentValueWhenBranchChanged( data );
					}
				} else if ( target.getField() === 'department_id' ) {
					widgets.is_changed = true;
				} else if ( target.getField() === 'punch_tag_id' ) {
					widgets.is_changed = true;
				}
				$this.parent_controller.autoSaveManualPunch();
			} );
		}

		if ( disable_dropdowns ) {
			for ( var key in widgets ) {
				var item = widgets[key];
				if ( item.getField() == 'branch_id' || item.getField() == 'department_id' || item.getField() == 'job_id' || item.getField() == 'job_item_id' ) {
					if ( item.getField() == 'job_id' || item.getField() == 'job_item_id' || item.getField() == 'punch_tag_id' ) {
						item.parents( 'td' ).find( 'input[type="text"]' ).hide(); //hide job and task lookup box
					}
					item.setEnabled && item.setEnabled( false );
				}
			}
		}

		if ( disable_plus == true ) {
			row.find( '.plus-icon' ).hide();
		}

		if ( disable_minus == true ) {
			row.find( '.minus-icon' ).hide();
		}

		if ( typeof index != 'undefined' ) {
			row.insertAfter( $( render ).find( 'tr' ).eq( index ) );
			this.rows_widgets_array.splice( ( index ), 0, widgets );

		} else {
			$( render ).append( row );
			this.rows_widgets_array.push( widgets );
		}

		$this.parent_controller.buildManualNotes( widgets, data );

		//Save current set item
		widgets.current_edit_item = data;
		this.addIconsEvent( row ); //Bind event to add and minus icon

		function onJobQuickSearch( key, value ) {
			var args = {};
			if ( key === 'job_quick_search' ) {
				args.filter_data = {
					manual_id: value,
					user_id: $this.parent_controller.getSelectEmployee(),
					status_id: '10'
				};
				job_api.getJob( args, {
					onResult: function( result ) {
						var result_data = result.getResult();
						widgets.is_changed = true;
						$this.parent_controller.autoSaveManualPunch();
						if ( result_data.length > 0 ) {
							widgets['job_id'].setValue( result_data[0].id );
							setJobItemValueWhenJobChanged( result_data[0] );
						} else {
							widgets['job_id'].setValue( '' );
							setJobItemValueWhenJobChanged( false );
						}

					}
				} );
			} else if ( key === 'job_item_quick_search' ) {
				args.filter_data = { manual_id: value, job_id: widgets['job_id'].getValue(), status_id: '10' };
				job_item_api.getJobItem( args, {
					onResult: function( result ) {
						var result_data = result.getResult();
						widgets.is_changed = true;
						$this.parent_controller.autoSaveManualPunch();
						if ( result_data.length > 0 ) {
							widgets['job_item_id'].setValue( result_data[0].id );

						} else {
							widgets['job_item_id'].setValue( '' );
						}

					}
				} );
			}
		}

		function setJobItemValueWhenJobChanged( job ) {
			var job_item_widget = widgets['job_item_id'];
			if ( !job_item_widget ) {
				return;
			}
			var current_job_item_id = job_item_widget.getValue();
			job_item_widget.setSourceData( null );
			var args = {};
			args.filter_data = { status_id: 10, job_id: widgets['job_id'].getValue() };
			job_item_widget.setDefaultArgs( args );
			if ( current_job_item_id ) {
				var new_arg = Global.clone( args );
				new_arg.filter_data.id = current_job_item_id;
				new_arg.filter_columns = job_item_widget.getColumnFilter();
				job_item_api.getJobItem( new_arg, {
					onResult: function( task_result ) {
						var data = task_result.getResult();
						if ( data.length > 0 ) {
							job_item_widget.setValue( current_job_item_id );
						} else {
							setDefaultData();
						}
					}
				} );

			} else {
				setDefaultData();
			}

			function setDefaultData() {
				if ( widgets['job_id'].getValue() ) {
					job_item_widget.setValue( job.default_item_id );
					if ( job.default_item_id === false || job.default_item_id === 0 ) {
						job_item_coder.setValue( '' );
					}

				} else {
					job_item_widget.setValue( '' );
					job_item_coder.setValue( '' );
				}
			}
		}

		function setDepartmentValueWhenBranchChanged() {
			var department_widget = widgets['department_id'];
			if ( !department_widget ) {
				return;
			}

			department_widget.setSourceData( null );
			var args = {};
			args.filter_data = {
				user_id: $this.parent_controller.getSelectEmployee( true ).id,
				branch_id: widgets['branch_id'].getValue()
			};
			var current_department_id = department_widget.getValue();
			department_widget.setDefaultArgs( args );

			if ( current_department_id ) {
				var new_arg = Global.clone( args );

				new_arg.filter_columns = department_widget.getColumnFilter();
				department_api.getDepartment( new_arg, {
					onResult: function( department_result ) {

						var data = department_result.getResult();

						if ( data.length > 0 && data.some( department => department.id === current_department_id ) ) {
							department_widget.setValue( current_department_id );
						} else {
							setDefaultData();
						}
					}
				} );

			} else {
				setDefaultData();
			}

			function setDefaultData() {
				department_widget.setValue( '' );
			}
		}

		function setPunchTagValuesWhenCriteriaChanged() {
			if ( ( Global.getProductEdition() <= 15 ) ) {
				return;
			}

			var punch_tag_widget = widgets['punch_tag_id'];
			if ( !punch_tag_widget ) {
				return;
			}
			var current_punch_tag_ids = punch_tag_widget.getValue();
			punch_tag_widget.setSourceData( null );
			var args = {};
			args.filter_data = {
				status_id: 10,
				user_id: $this.parent_controller.getSelectEmployee( true ).id,
				branch_id: widgets['branch_id'] ? widgets['branch_id'].getValue() : TTUUID.zero_id,
				department_id: widgets['department_id'] ? widgets['department_id'].getValue() : TTUUID.zero_id,
				job_id: widgets['job_id'] ? widgets['job_id'].getValue() : TTUUID.zero_id,
				job_item_id: widgets['job_item_id'] ? widgets['job_item_id'].getValue() : TTUUID.zero_id
			};
			punch_tag_widget.setDefaultArgs( args );

			if ( current_punch_tag_ids && current_punch_tag_ids.length > 0 ) {
				var new_arg = Global.clone( args );

				new_arg.manual_id = current_punch_tag_ids;
				punch_tag_api.getPunchTag( new_arg, {
					onResult: function( punch_tag_result ) {
						var data = punch_tag_result.getResult();

						if ( data.length > 0 ) {
							if ( current_punch_tag_ids !== TTUUID.zero_id && current_punch_tag_ids.length > 0 && shouldUpdatePunchTags( current_punch_tag_ids, data ) ) {
								//Compare current selected punch tags and the list of punch tags from the API and remove invalid punch tags.
								var intersected_values = current_punch_tag_ids.filter( punch_tag_id => data.some( punch_tag => punch_tag_id === punch_tag.id ) );
								punch_tag_widget.setValue( intersected_values );
							}
						} else {
							setDefaultData();
						}
					}
				} );

			} else {
				setDefaultData();
			}

			function setDefaultData() {
				punch_tag_widget.setValue( '' );
			}
		}

		function shouldUpdatePunchTags( current_punch_tag_ids, data ) {
			//If the data returned from the API does not contain every currently selected punch tag then we need to remove invalid tags.
			if ( current_punch_tag_ids.every( punch_tag_id => data.some( punch_tag => punch_tag.id === punch_tag_id ) ) === false ) {
				return true;
			}
		}

		//In the scenario where the user does not have permission to edit punch branch, department, job, job item and punch tag. (All of them)
		//Then the minus icon should not be centered as the <td> is too large and it looks out of place.
		if ( this.parent_controller.getPunchMode() === 'manual' ) {
			if ( this.parent_controller.timesheet_columns.length === 9 ) { // Plus and Minus icon + 7 days of the week.
				var control_icons = document.querySelectorAll( '.control-icon' );

				for ( var control_icon of control_icons ) {
					control_icon.style.textAlign = 'left';
				}
			}
		}
	}

	autoSaveManualPunch() {
		var $this = this;
		if ( this.is_saving_manual_grid ) {
			this.save_manual_grid_after_save = true;
			return;
		}
		this.wait_auto_save && clearTimeout( this.wait_auto_save );
		this.wait_auto_save = setTimeout( function() {
			if ( $this.getPunchMode() === 'manual' ) {
				ProgressBar.showOverlay();
				$this.onSaveClick();
			}
		}, 2000 );
	}

	insideEditorGetValue( isSave ) {
		var len = this.rows_widgets_array.length;
		var result = [];
		for ( var i = 0; i < len; i++ ) {
			var row = this.rows_widgets_array[i];
			for ( var j = 0; j < 7; j++ ) {
				var current_date = new Date( new Date( this.parent_controller.start_date.getTime() ).setDate( this.parent_controller.start_date.getDate() + j ) );
				var field = current_date.format();
				var common_record = {};
				if ( !row[field] ) {
					continue;
				}
				common_record.total_time = row[field].getValue();
				if ( row[field].is_changed || ( row.is_changed && row.current_edit_item[field] ) ) {
					row.branch_id && ( common_record.branch_id = row.branch_id.getValue() );
					row.department_id && ( common_record.department_id = row.department_id.getValue() );
					row.job_id && ( common_record.job_id = row.job_id.getValue() );
					row.job_item_id && ( common_record.job_item_id = row.job_item_id.getValue() );
					row.punch_tag_id && ( common_record.punch_tag_id = row.punch_tag_id.getValue() );
					common_record.date_stamp = field;
					common_record.user_id = this.parent_controller.getSelectEmployee();
					common_record.object_type_id = 10;
					common_record.override = true;
					if ( row.current_edit_item[field] && row.current_edit_item[field].hasOwnProperty('note') ) { //Make sure we support blank notes to clear out the note field.
						common_record.note = row.current_edit_item[field].note;
					}
					common_record.row = row;
					row.current_edit_item[field] && ( common_record.id = row.current_edit_item[field].id );
					result.push( common_record );
					if ( isSave ) {
						row[field].is_changed = false;
						row[field].addClass( 'is-saving-manual-grid' );
					}
				}
			}
			if ( isSave ) {
				row.is_changed = false;
			}
		}

		return result;
	}

	insideEditorRemoveRow( row ) {
		var $this = this;
		var index = row[0].rowIndex - 3;
		var widget_row = this.rows_widgets_array[index];
		var has_value = false;
		for ( var j = 0; j < 7; j++ ) {
			var current_date = new Date( new Date( this.parent_controller.start_date.getTime() ).setDate( this.parent_controller.start_date.getDate() + j ) );
			var field = current_date.format();
			if ( widget_row[field].getValue() > 0 || widget_row.current_edit_item[field] ) {
				has_value = true;
				TAlertManager.showConfirmAlert( $.i18n._( 'You are about to delete the entire week worth of time for this row. Are you sure you wish to continue?' ), '', doNext );
				break;
			}
		}
		!has_value && doNext( true );

		function doNext( flag ) {
			if ( flag ) {
				var remove_ids = [];
				for ( var j = 0; j < 7; j++ ) {
					var current_date = new Date( new Date( $this.parent_controller.start_date.getTime() ).setDate( $this.parent_controller.start_date.getDate() + j ) );
					var field = current_date.format();
					widget_row.current_edit_item[field] && ( remove_ids.push( widget_row.current_edit_item[field].id ) );
				}
				if ( remove_ids.length > 0 ) {
					ProgressBar.noProgressForNextCall();
					ProgressBar.showNanobar();
					$this.is_saving_manual_grid = true;
					$this.parent_controller.setDefaultMenu();
					$this.api.deleteUserDateTotal( remove_ids, {
						onResult: function() {
							$this.parent_controller.reLoadSubGridsSource( true );
							ProgressBar.removeNanobar();
							$this.parent_controller.setDefaultMenu();
						}
					} );
				}
				row.remove();
				$this.rows_widgets_array.splice( index, 1 );
				if ( $this.rows_widgets_array.length === 0 ) {
					$this.addRow();
				}
			}
		}
	}

	buildManualTimeSheetGrid() {
		var args = {
			branch: $.i18n._( 'Branch' ),
			department: $.i18n._( 'Department' ),
			job: $.i18n._( 'Job' ),
			task: $.i18n._( 'Task' ),
			punch_tag: $.i18n._( 'Tags' )
		};

		this.editor = Global.loadWidgetByName( FormItemType.INSIDE_EDITOR );
		this.editor.InsideEditor( {
			title: '',
			addRow: this.insideEditorAddRow,
			getValue: this.insideEditorGetValue,
			setValue: this.insideEditorSetValue,
			removeRow: this.insideEditorRemoveRow,
			parent_controller: this,
			render: getRender(),
			render_args: args,
			api: this.api_user_date_total,
			render_inline_html: true,
			row_render: getRowRender()
		} );

		function getRender() {
			return `<table class="inside-editor-render grid-inside-editor-render">
						<tr class="manual-timesheet-size-tr">
							<td style="width: 25px"></td>
							<td style="width: 25px"></td>
							<td class="branch-header" style="width: 120px"></td>
							<td class="department-header" style="width: 120px"></td>
							<td class="job-header" style="width: 120px"></td>
							<td class="job-item-header" style="width: 120px"></td>
							<td class="punch-tag-header" style="width: 120px"></td>
							<td style="width: 50px"></td>
							<td style="width: 50px"></td>
							<td style="width: 50px"></td>
							<td style="width: 50px"></td>
							<td style="width: 50px"></td>
							<td style="width: 50px"></td>
							<td style="width: 50px"></td>
						</tr>
						<tr class="title">
							<td></td>
							<td></td>
							<td class="branch-header"><%= branch %></td>
							<td class="department-header"><%= department %></td>
							<td class="job-header"><%= job %></td>
							<td class="job-item-header"><%= task %></td>
							<td class="punch-tag-header"><%= punch_tag %></td>
							<td id="day_0_date"></td>
							<td id="day_1_date"></td>
							<td id="day_2_date"></td>
							<td id="day_3_date"></td>
							<td id="day_4_date"></td>
							<td id="day_5_date"></td>
							<td id="day_6_date"></td>
						</tr>
					</table>`;
		}

		function getRowRender() {
			return `<tr class="inside-editor-row data-row">
						<td class="cell control-icon">
							<button class="plus-icon" onclick=""></button>
						</td>
						<td class="cell control-icon">
							<button class="minus-icon " onclick=""></button>
						</td>
						<td class="branch "></td>
						<td class="department "></td>
						<td class="job "></td>
						<td class="task "></td>
						<td class="punch_tag "></td>
						<td class="day-0 "></td>
						<td class="day-1 "></td>
						<td class="day-2 "></td>
						<td class="day-3 "></td>
						<td class="day-4 "></td>
						<td class="day-5 "></td>
						<td class="day-6 "></td>
					</tr>`;
		}

		var inside_editor_div = this.$( '.manual-timesheet-inside-editor-div' );
		inside_editor_div.append( this.editor );
	}

	buildCalendars() {
		var $this = this;
		if ( this.is_auto_switch ) {
			this.getManualTimeSheetData( function() {
				if ( $this.manual_timesheet_data.length > 0 ) {
					$this.buildManualTimeSheetData();
					var is_no_manual = _.isEmpty( $this.time_sheet_data_overrode_true_map );
					var is_no_punch = _.isEmpty( $this.time_sheet_data_overrode_false_map );
					if ( is_no_manual && !is_no_punch && $this.toggle_button.getValue() !== 'manual' ) {
						$this.toggle_button.setValue( 'punch' );
						$this.is_auto_switch = false;
					} else if ( !is_no_manual && is_no_punch ) {
						$this.toggle_button.setValue( 'manual' );
					}
					$this.reSetURL();
					doNext();
				} else {
					$this.getPunchMode() === 'punch' && ( $this.is_auto_switch = false );
					doNext();
				}
			} );
		} else {
			doNext();
		}

		function doNext() {
			$this.pay_period_data = $this.full_timesheet_data.pay_period_data;
			$this.pay_period_map = $this.full_timesheet_data.timesheet_dates.pay_period_date_map;
			$this.timesheet_verify_data = $this.full_timesheet_data.timesheet_verify_data;
			$this.grid_div = $( $this.el ).find( '.timesheet-grid-div' );
			// Punch grid
			$this.buildTimeSheetsColumns();
			$this.buildTimeSheetGrid();
			if ( $this.getPunchMode() === 'manual' ) {
				$this.$( '.timesheet-punch-grid-wrapper' ).hide();
				$this.$( '.manual-timesheet-inside-editor-div' ).show();
				if ( !$this.editor ) {
					$this.buildManualTimeSheetsColumns();
					$this.buildManualTimeSheetGrid();
					$this.initInsideEditorData();
				} else {
					$this.buildManualTimeSheetsColumns();
					$this.initInsideEditorData();
				}
				if ( !$this.show_job_ui || Global.getProductEdition() < 20 ) {
					$this.$( '.job-header' ).hide();
				}
				if ( !$this.show_job_item_ui || Global.getProductEdition() < 20 ) {
					$this.$( '.job-item-header' ).hide();
				}
				if ( !$this.show_punch_tag_ui || Global.getProductEdition() < 20 ) {
					$this.$( '.punch-tag-header' ).hide();
				}
				if ( !$this.show_branch_ui ) {
					$this.$( '.branch-header' ).hide();
				}
				if ( !$this.show_department_ui ) {
					$this.$( '.department-header' ).hide();
				}
			} else {
				$this.$( '.timesheet-punch-grid-wrapper' ).show();
				$this.$( '.manual-timesheet-inside-editor-div' ).hide();
			}

			$this.buildAccumulatedGrid();

			$this.buildSubGrid( 'branch_grid', 'Branch' );
			$this.buildSubGrid( 'department_grid', 'Department' );
			$this.buildSubGrid( 'job_grid', 'Job' );
			$this.buildSubGrid( 'job_item_grid', 'Task' );
			$this.buildSubGrid( 'punch_tag_grid', 'Tags' );
			$this.buildSubGrid( 'premium_grid', 'Premium' );
			$this.buildAbsenceGrid();
			$this.showGridBorders();
			$this.buildAccumulatedTotalGrid();
			$this.buildPunchNoteGrid();
			$this.buildVerificationGrid();
			//TimeSheet grid
			$this.buildTimeSheetSource(); //Create punch data
			$this.buildTimeSheetRequests();
			//Accumulated Time, Branch, Department, Job, Task, Pre
			$this.buildSubGridsSource();
			//Make sure exception rows goes after Lanuch and break create from buildSubGridsSource
			$this.buildTimeSheetExceptions();
			//Absence Grid source
			$this.buildAbsenceSource(); //Create punch data
			//Show punch notes in a grid
			$this.buildPunchNoteGridSource();
			//buildVerificationGridSource
			$this.buildVerificationGridSource();
			$this.setGridExpendButton( 'accumulated_time_grid', $.i18n._( 'Accumulated Time' ) );
			$this.setGridExpendButton( 'branch_grid', $.i18n._( 'Branch' ) );
			$this.setGridExpendButton( 'department_grid', $.i18n._( 'Department' ) );
			$this.setGridExpendButton( 'job_grid', $.i18n._( 'Job' ) );
			$this.setGridExpendButton( 'job_item_grid', $.i18n._( 'Task' ) );
			$this.setGridExpendButton( 'punch_tag_grid', $.i18n._( 'Tags' ) );
			$this.setGridExpendButton( 'premium_grid', $.i18n._( 'Premium' ) );
			$this.setGridExpendButton( 'absence_grid', $.i18n._( 'Absence' ) );
			$this.setGridExpendButton( 'punch_note_grid', $.i18n._( 'Punch Notes' ) );

			if ( $this.getPunchMode() === 'punch' ) {
				//var selection = $this.grid.getSelection(); //provides memory of selected cells
				$this.grid.setData( $this.timesheet_data_source, true );
				//$this.grid.setTimesheetSelection( selection ); //resets selection after refreshing grid data -- currently broken, see setTimesheetSelection() for details.
			}

			if ( typeof $this.accumulated_time_grid.setData == 'function' ) {
				$this.accumulated_time_grid.setData( $this.accumulated_time_source, false );
			}

			if ( typeof $this.branch_grid.setData == 'function' ) {
				$this.branch_grid.setData( $this.branch_source, false );
			}

			if ( typeof $this.department_grid.setData == 'function' ) {
				$this.department_grid.setData( $this.department_source, false );
			}

			if ( typeof $this.job_grid.setData == 'function' ) {
				$this.job_grid.setData( $this.job_source, false );
			}

			if ( typeof $this.job_item_grid.setData == 'function' ) {
				$this.job_item_grid.setData( $this.job_item_source, false );
			}

			if ( typeof $this.punch_tag_grid.setData == 'function' ) {
				$this.punch_tag_grid.setData( $this.punch_tag_source, false );
			}

			if ( typeof $this.premium_grid.setData == 'function' ) {
				$this.premium_grid.setData( $this.premium_source, false );
			}

			if ( $this.absence_grid ) {
				$this.absence_grid.setData( $this.absence_source, false );
			}

			if ( $this.accumulated_total_grid_source.length === 0 ) {
				$this.accumulated_total_grid_source.push();
			}

			$this.accumulated_total_grid.setData( $this.accumulated_total_grid_source, false );
			$this.punch_note_grid.setData( $this.punch_note_grid_source, false );
			$this.verification_grid.setData( $this.verification_grid_source, false );

			$this.setGridSize();

			$this.setTimeSheetGridPayPeriodHeaders();
			$this.setTimeSheetGridHolidayHeaders();
			$this.highLightSelectDay();
			$this.autoOpenEditViewIfNecessary();
			$this.setScrollPosition();
			$this.initRightClickMenu();
			$this.initRightClickMenu( RightClickMenuType.ABSENCE_GRID );
			$this.showWarningMessageIfAny();
			$this.setPunchModeClass();

			if ( $this.getPunchMode() != 'punch' ) {
				var cols = $this.getManualPayPeriodDefaultTrColspan();
				for ( var i = 0; i < cols; i++ ) {
					$( '.sub-grid td:nth-child(' + i + ')' ).css( 'border-right', 'none' );
				}
			}
		}
	}

	searchDone() {
		$( '.button-rotate' ).removeClass( 'button-rotate' ); //the rotate icon from search panel

		TTPromise.resolve( 'init', 'init' );
		TTPromise.wait();

		//Check this.setGridSize() where we resize the grids if a scrollbar is detected to ensure all grids remain the same width after each one is built.
		// TTPromise.wait( null, null, function () {
		//  //This was triggering JS exception: Permission denied to access property "apply" -- Seems like its no longer needed either.
		// 	$( window ).trigger( 'resize' );
		// } );
	}

	showWarningMessageIfAny() {
		var $this = this;
		var timesheet_grid_div;
		var warning_bar = $( this.el ).find( '.timesheet-warning-title-bar' );
		warning_bar.length > 0 && warning_bar.remove() && ( warning_bar = $( this.el ).find( '.timesheet-warning-title-bar' ) );
		if ( this.getPunchMode() === 'punch' ) {
			timesheet_grid_div = $( this.el ).find( '#gbox_' + this.ui_id + '_grid' );
		} else {
			timesheet_grid_div = $( this.el ).find( '.manual-timesheet-inside-editor-div' );
		}

		var user = this.getSelectEmployee( true );
		var user_pay_period_check = payPeriodCheck( user );

		//There seems to be a race condition here where if the server hasn't returned all the user data for the dropdown box (due to being slow/including many columns), "user.id" will exist, but no other object properties will.
		//  This could trigger the below error message(s) to show when they shouldn't. So now we check to make sure there is at least more than 1 object property, and we check that the object properties actually exist and are actually blank, as compared to just checking that they don't exist previously.
		if ( Global.isObject( user ) && Object.keys( user ).length > 1 && ( ( user.hasOwnProperty( 'pay_period_schedule_id' ) && user.pay_period_schedule_id == '' ) || ( user.hasOwnProperty( 'policy_group_id' ) && user.policy_group_id == '' ) || user_pay_period_check == false ) ) {
			warning_bar = $( '<div class=\'timesheet-warning-title-bar\'><span class=\'p-message\'></span><span class=\'g-message\'></span><span class=\'pp-message\'></span></div>' );
			warning_bar.insertBefore( timesheet_grid_div );

			if ( user.hasOwnProperty( 'pay_period_schedule_id' ) && user.pay_period_schedule_id == '' ) { //!user.pay_period_schedule_id
				warning_bar.children().eq( 0 ).html( $.i18n._( 'WARNING: Employee is not assigned to a pay period schedule.' ) );
			} else {
				warning_bar.children().eq( 0 ).html( '' );
			}
			if ( user.hasOwnProperty( 'policy_group_id' ) && user.policy_group_id == '' ) { //!user.policy_group_id
				warning_bar.children().eq( 1 ).html( $.i18n._( 'WARNING: Employee is not assigned to a policy group.' ) );
			} else {
				warning_bar.children().eq( 1 ).html( '' );
			}

			if ( user_pay_period_check == false ) {
				warning_bar.children().eq( 2 ).html( $.i18n._( 'WARNING: Employee has day(s) not assigned to a pay period. Please perform a pay period import to correct.' ) );
			} else {
				warning_bar.children().eq( 2 ).html( '' );
			}

		} else {
			if ( warning_bar.length > 0 ) {
				warning_bar.remove();
			}
		}

		function payPeriodCheck( user ) {
			if ( $this.start_date ) {
				var hire_date = user.hire_date;
				var termination_date = user.termination_date;

				for ( var i = 0; i < 7; i++ ) {
					var select_date = new Date( new Date( $this.start_date.getTime() ).setDate( $this.start_date.getDate() + i ) );
					var select_date_str = select_date.format();

					//Error: Uncaught TypeError: Cannot read property 'getTime' of null in interface/html5/index.php?user_name=dustin#!m=TimeSheet&date=20151214&user_id=38599&show_wage=0 line 2947
					if ( !select_date ) {
						continue;
					}

					if ( select_date.getTime() < new Date().getTime() && !$this.getPayPeriod( select_date_str ) &&
						( !hire_date || select_date.getTime() >= Global.strToDate( hire_date ).getTime() ) &&
						( !termination_date || select_date.getTime() <= Global.strToDate( termination_date ).getTime() ) ) {
						return false;
					}

				}
			}

			return true;
		}
	}

	autoOpenEditViewIfNecessary() {
		//Auto open edit view. Should set in IndexController

		switch ( LocalCacheData.current_doing_context_action ) {
			case 'edit':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onEditClick( LocalCacheData.edit_id_for_next_open_view, LocalCacheData.getAllURLArgs().t );
					LocalCacheData.edit_id_for_next_open_view = null;
				}

				break;
			case 'view':
				if ( LocalCacheData.edit_id_for_next_open_view ) {
					this.onViewClick( LocalCacheData.edit_id_for_next_open_view, LocalCacheData.getAllURLArgs().t );
					LocalCacheData.edit_id_for_next_open_view = null;
				}
				break;
			case 'new':
				if ( !this.edit_view ) {
					if ( LocalCacheData.getAllURLArgs().t === 'absence' ) {
						this.absence_model = true;
					} else {
						this.absence_model = false;
					}
					this.onAddClick();
				}
				break;
		}

		this.autoOpenEditOnlyViewIfNecessary();
	}

	getWeekDayIndexFromADate( date_string ) {

		var len = this.timesheet_columns.length;

		for ( var i = 1; i < len; i++ ) {
			var column = this.timesheet_columns[i];
			var column_date_string = Global.strToDate( column.index, this.full_format ).format();
			if ( date_string === column_date_string ) {
				return i;
			}
		}

		return 7;
	}

	setAccumulatedTotalGridPayPeriodHeaders( width ) {
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_accumulated_total_grid]' )[0] );
		table.width( width );

		var new_tr = $( '<tr class="group-column-tr"  role="rowheader"  >' +
			'</tr>' );

		var new_th = $( '<th class="group-column-th" >' +
			'<span class="group-column-label"></span>' +
			'</th>' );

		var default_th = new_th.clone();

		var week_th = new_th.clone();

		var pay_period_th = new_th.clone();

		week_th.children( 0 ).text( $.i18n._( 'Week' ) );
		pay_period_th.children( 0 ).text( $.i18n._( 'Pay Period' ) );

		new_tr.append( default_th );
		new_tr.append( week_th );
		new_tr.append( pay_period_th );

		table.find( '.rowheader' ).remove();
		table.find( 'thead' ).prepend( new_tr );
	}

	setTimeSheetGridHolidayHeaders() {
		var holiday_name_map = {};
		if ( this.full_timesheet_data && this.full_timesheet_data.holiday_data ) {
			for ( var i = 0; i < this.full_timesheet_data.holiday_data.length; i++ ) {
				var item = this.full_timesheet_data.holiday_data[i];
				var standard_date = Global.strToDate( item.date_stamp ).format( this.full_format );

				var cell = $( '<div></div>' );
				if ( this.getPunchMode() === 'manual' ) {
					cell = $( '.manual_grid_day_' + standard_date );
				} else {
					cell = $( 'div[id="jqgh_' + this.ui_id + '_grid_' + standard_date + '"]' );
				}

				if ( cell && cell.text().indexOf( item.name ) == -1 && !holiday_name_map[item.name] ) {
					cell.html( cell.html() + '<br>' + Global.htmlEncode( item.name ) );
					holiday_name_map[item.name] = true;
				}

			}
		}
	}

	getManualPayPeriodDefaultTrColspan() {
		var colspan = 2;
		if ( this.show_branch_ui ) {
			colspan++;
		}

		if ( this.show_department_ui ) {
			colspan++;
		}

		if ( this.show_job_ui ) {
			colspan++;
		}

		if ( this.show_job_item_ui ) {
			colspan++;
		}

		if ( this.show_punch_tag_ui ) {
			colspan++;
		}

		return colspan;
	}

	setTimeSheetGridPayPeriodHeaders() {
		var $this = this;
		var table,
			size_tr;
		if ( this.getPunchMode() === 'punch' ) {
			table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' )[0] );
			size_tr = $( '<tr class="size-tr" role="row" style="height: 0;" >' + '</tr>' );
		} else {
			table = this.$( '.grid-inside-editor-render' );
			table.find( '.group-column-tr' ).remove();
			size_tr = $( this.$( '.grid-inside-editor-render' ).find( 'tr' )[0] );
		}

		table.find( '.group-column-tr' ).remove();

		var new_tr = $( '<tr class="group-column-tr pay_period_header_row"></tr>' );
		var new_th = $( '<th class="group-column-th"><span class="group-column-label"></span></th>' );
		var current_trs = table.find( '.ui-jqgrid-labels' );

		// createSizeColumns was added in 2014. When manual timesheet mode was added in 2016, things were refactored and this should have been pulled out.
		// Leaving it in causes  header row solumns to be out of alignment with the timesheet punch grid by a few pixels.
		if ( this.getPunchMode() === 'punch' ) {
			createSizeColumns();
			size_tr.insertBefore( table.find( 'thead .ui-jqgrid-labels' ) );
		}

		var default_th;
		if ( Object.keys( this.pay_period_data ).length === 0 ) {
			default_th = new_th.clone();
			new_tr.append( default_th );
			if ( this.getPunchMode() === 'manual' ) {
				default_th.attr( 'colspan', this.getManualPayPeriodDefaultTrColspan() );
			}
			createNoPayPeriodColumns( 7 );
			new_tr.insertAfter( size_tr );
			return;
		}
		var current_end_index = 0;
		var last_pay_period_id;
		var column_number = 0;
		var pay_period;
		var map_array = [];
		for ( var y = 0; y < this.column_maps.length; y++ ) {
			var p_key = this.column_maps[y];
			var pay_period_id = this.pay_period_map[p_key];
			if ( !pay_period_id ) {
				pay_period_id = -1;
			}
			map_array.push( { date: p_key, time_stamp: Global.strToDate( p_key ).getTime(), id: pay_period_id } );
		}

		default_th = new_th.clone();
		new_tr.append( default_th );
		if ( this.getPunchMode() === 'manual' ) {
			default_th.attr( 'colspan', this.getManualPayPeriodDefaultTrColspan() );
		}
		for ( var j = 0; j < map_array.length; j++ ) {
			if ( !last_pay_period_id ) {
				last_pay_period_id = map_array[j].id;
				pay_period = getPayPeriod( map_array[j].id );
				column_number = column_number + 1;
			} else if ( last_pay_period_id !== map_array[j].id ) {
				if ( pay_period ) {
					createTh();
				} else {
					createNoPayPeriodColumns( column_number );
				}
				last_pay_period_id = map_array[j].id;
				pay_period = getPayPeriod( map_array[j].id );
				column_number = 1;

			} else {
				column_number = column_number + 1;
			}
			if ( j === map_array.length - 1 && column_number > 0 ) {
				if ( pay_period ) {
					createTh();
				} else {
					createNoPayPeriodColumns( column_number );
				}
			}
		}
		$( '.pay_period_header_row' ).remove();
		new_tr.insertAfter( size_tr );

		function createTh() {
			var start_date = Global.strToDate( pay_period.start_date ).format();
			var end_date = Global.strToDate( pay_period.end_date ).format();
			var colspan = column_number;
			var pay_period_th = new_th.clone();
			pay_period_th.children( 0 ).text( start_date + ' ' + $.i18n._( 'to' ) + ' ' + end_date );
			pay_period_th.attr( 'colspan', colspan );
			/* jshint ignore:start */
			if ( pay_period.status_id == 12 || pay_period.status_id == 20 ) {
				pay_period_th.css( 'background', '#EC0000' );
			} else if ( pay_period.status_id == 30 ) {
				pay_period_th.css( 'background', '#EED614' );
			}
			/* jshint ignore:end */
			new_tr.append( pay_period_th );
		}

		function getPayPeriod( id ) {
			for ( var key in $this.pay_period_data ) {
				var pay_period = $this.pay_period_data[key];
				if ( pay_period.id === id ) {
					return pay_period;
				}
			}
		}

		function createNoPayPeriodColumns( end_index ) {
			var pay_period_th = new_th.clone();
			pay_period_th.addClass( 'no_pay_period_header' );
			pay_period_th.children( 0 ).text( $.i18n._( 'No Pay Period' ) );
			pay_period_th.attr( 'colspan', end_index );
			new_tr.append( pay_period_th );
		}

		function createSizeColumns() {
			var len = current_trs.children().length;
			for ( var i = 0; i < len; i++ ) {
				var th = $( '<td class=""  role="gridcell">' + '</td>' );
				var item = current_trs.children().eq( i );
				//th.width( item.width() );
				th.height( 0 );
				th.css( 'width', item.css( 'width' ) );
				size_tr.append( th );
			}

		}
	}

	setPayPeriodHeaderSize() {

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' ) );
		var size_tr = table.find( '.size-tr' );

		if ( size_tr.length === 0 ) {
			return;
		}
		/**
		 * #2353 - tiemsheet sizing fix
		 *
		 * Due to firefox reporting th widths incorrectly via the $.width() function, the sizes must come from the (tr.jqgfirstrow) of the data table
		 * firefox also refuses to set the width of the first row of th's via the $.width() function, so we ned to ship the css values directly into the inline css using the css function
		 */
		var current_trs = table.find( '.jqgfirstrow' );
		var len = current_trs.children().length;

		for ( var i = 0; i < len; i++ ) {
			var item = current_trs.children().eq( i );
			size_tr.children().eq( i ).css( 'width', item.css( 'width' ) );
		}
	}

	highLightSelectDay( e ) {

		if ( this.highlight_header ) {
			this.highlight_header.removeClass( 'highlight-header' );
		}

		//Error: TypeError: select_date is null in interface/html5/framework/jquery.min.js?v=9.0.1-20151022-081724 line 2 > eval line 3214
		var select_date = Global.strToDate( this.start_date_picker.getValue() );
		!select_date && ( select_date = new Date() );

		if ( this.getPunchMode() === 'punch' ) {
			select_date = select_date.format( this.full_format );
			this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_date );
		} else {
			select_date = select_date.format( this.full_format );
			this.highlight_header = $( '.manual_grid_day_' + select_date );
		}

		this.highlight_header.addClass( 'highlight-header' );

		if ( $( '.timesheet-grid tr td.ui-state-highlight' ).length == 0 && !e ) {
			$( $( '.timesheet-grid tr#1 td' )[this.highlight_header.index()] ).addClass( 'ui-state-highlight' );
			$( $( '.timesheet-grid tr#1 td' )[this.highlight_header.index()] ).click(); //trigger grid selection events
		}
	}

	/* jshint ignore:start */
	setGridHeight( grid_id ) {
		var grid = this.grid_dic[grid_id];
		if ( grid.grid ) {
			grid = grid.grid;
		} else {
			return false;
		}
		var len = 0;

		switch ( grid_id ) {
			case 'timesheet_grid':
				len = this.timesheet_data_source.length;
				break;
			case 'accumulated_time_grid':
				len = this.accumulated_time_source.length;
				break;
			case 'branch_grid':
				len = this.branch_source.length;
				break;
			case 'department_grid':
				len = this.department_source.length;
				break;
			case 'job_grid':
				len = this.job_source.length;
				break;
			case 'job_item_grid':
				len = this.job_item_source.length;
				break;
			case 'punch_tag_grid':
				len = this.punch_tag_source.length;
				break;
			case 'premium_grid':
				len = this.premium_source.length;
				break;
			case 'absence_grid':
				len = this.absence_source.length;
				break;
			case 'accumulated_total_grid':
				len = this.accumulated_total_grid_source.length;
				break;
			case 'punch_note_grid':
				len = this.punch_note_grid_source.length;
				break;
			case 'verification_grid':
				len = this.verification_grid_source.length;
		}

		if ( LocalCacheData.timesheet_sub_grid_expended_dic[grid_id] === true ||
			grid_id === 'timesheet_grid' ||
			grid_id === 'accumulated_total_grid' ||
			grid_id === 'punch_note_grid' ||
			grid_id === 'verification_grid' ) {
			grid.setGridHeight( len * 22 );
		} else {
			grid.setGridHeight( 0 );

		}

		//dont't show scroll bar of grid
		grid.parent().parent().css( 'overflow', 'hidden' );

		//Do not show grid if no data in it
		if ( len === 0 && grid_id !== 'accumulated_time_grid' && grid_id !== 'verification_grid' ) {
			grid.parent().parent().parent().parent().hide();
		} else {
			grid.parent().parent().parent().parent().show();
		}
	}

	/* jshint ignore:end */

	setGridColumnsWidth() {
		//BaseViewController definition of this function resizes the main grid of a view, but we want to resize all grids in this view.
		//The BaseViewController version would be called when expanding/collapsing the search panel causing only the main grid to be resized and be misaligned with the others.
		this.setGridSize();
	}

	setGridSize() {
		var $this = this;

		//TTGrid backwards compatible.
		var $grid = this.grid;
		if ( $grid.grid ) {
			$grid = $grid.grid;
		} else {
			return false;
		}
		if ( ( !$grid || !$( $grid ).is( ':visible' ) ) && ( !this.editor || !this.editor.is( ':visible' ) ) ) {
			return;
		}

		for ( var key in this.grid_dic ) {
			if ( key != 'punch_note_grid' && key != 'accumulated_total_grid' && key != 'verification_grid' ) {
				if ( !this.grid_dic[key].grid ) {
					continue;
				}
				var grid = this.grid_dic[key].grid;
				this.setGridHeight( key ); //Set height before width so scrollbar doesn't affect things.
				grid.setGridWidth( this.getTimeSheetWidth() );
			}
		}

		this.setGridHeight( 'accumulated_total_grid' ); //can't set height on this grid. until we fix what's wrong with it.

		this.grid_dic['verification_grid'].grid.setGridWidth( 400 );
		this.setGridHeight( 'verification_grid' );

		this.setPunchNoteGridWidth( this.grid_dic['punch_note_grid'].grid );
		this.setGridHeight( 'punch_note_grid' ); //can't set height on this grid. until we fix what's wrong with it.

		let scroll_height = document.body.scrollHeight;

		let height = scroll_height;

		let timesheet_grid = document.querySelector( 'div.timesheet-grid-div' );
		if ( timesheet_grid ) { //Issue #3060 - JavaScript exceptions of trying to read getBoundingClientRect of null elements.
			height -= timesheet_grid.getBoundingClientRect().top;
		}
		let context_border = document.querySelector( 'div.context-border' );
		if ( context_border ) { //Issue #3060 - JavaScript exceptions of trying to read getBoundingClientRect of null elements.
			height -= ( scroll_height - document.querySelector( 'div.context-border' ).getBoundingClientRect().bottom );
		}
		height -= 10; //Manual fine tuning to fit better.

		this.grid_div.height( height );
		this.grid.setGridWidth( this.getTimeSheetWidth() );

		this.setPayPeriodHeaderSize();

		if ( this.getPunchMode() === 'manual' ) {
			$this.setManualTimeSheetGridSize();
		}

		//Because a scrollbar has appeared in the middle of resizing the top grids compared to the bottom grid, the page may have expanded to show the scrollbar and we may need to resize the grids (width) again to take into account.
		//This is manifested itself in misaligned columns between the two grids.
		if ( Global.isVerticalScrollBarRequired( $('.timesheet-grid-div')[0] ) ) {
			for ( var key in this.grid_dic ) {
				if ( key != 'punch_note_grid' && key != 'accumulated_total_grid' && key != 'verification_grid' ) {
					if ( !this.grid_dic[key].grid ) {
						continue;
					}
					var grid = this.grid_dic[key].grid;
					grid.setGridWidth( this.getTimeSheetWidth() );
				}
			}
		}
	}

	setPunchNoteGridWidth( grid ) {
		if ( !grid ) {
			if ( !this.punch_note_grid || !this.punch_note_grid.grid ) {
				return false;
			}
			grid = this.punch_note_grid.grid;
		}

		var grid_width = grid.width();

		var accumulated_grid_width = ( this.accumulated_total_grid && this.accumulated_total_grid.grid ) ? this.accumulated_total_grid.getWidth() : 0;
		var verification_grid_width = ( this.verification_grid && this.verification_grid.grid ) ? this.verification_grid.getWidth() : 0;

		if ( this.verification_grid_source.length !== 0 ) {
			grid_width = Math.floor( this.getTimeSheetWidth() - ( accumulated_grid_width + verification_grid_width + 7 ) );
		} else {
			grid_width = Math.floor( this.getTimeSheetWidth() - ( 7 + accumulated_grid_width ) );
		}
		grid_width = Math.abs( grid_width );

		if ( grid_width != grid.width() ) {
			//Debug.Text("Setting punch note grid width to " + grid_width, 'TimesheetViewConroller.js', 'TimesheetViewConroller', 'setGridHeight', 10);
			grid.setGridWidth( grid_width );
			$( 'td.notes_grid_td_container' ).css( 'width', '100%' );
		}
	}

	setManualTimeSheetGridSize() {
		var tr = $( this.accumulated_time_grid.grid.find( 'tr:first-child' )[0] );
		var manual_grid_tr = $( this.editor.find( 'table' ).find( 'tr:first-child' )[0] );
		var index = 0;
		for ( var i = 0, m = manual_grid_tr.children().length; i < m; i++ ) {
			var td = $( manual_grid_tr.children()[i] );
			if ( !td.is( ':visible' ) ) {
				continue;
			}
			$( td ).css( 'width', $( tr.children()[index] ).css( 'width' ) );
			index++;
		}

		this.editor.width( this.accumulated_time_grid.getGridWidth() );
	}

	onCellFormat( cell_value, related_data, row ) {
		var col_model = related_data.colModel;
		var row_id = related_data.rowid;
		var content_div = $( '<div class=\'punch-content-div\'></div>' );
		var punch_info;
		var ex_span;
		var i;
		var time_span_prefix;
		var time_span;
		var time_span_suffix;
		var punch;
		var break_span;
		var related_punch;
		var exception;
		var len;
		var text;
		var ex;
		var data;
		var currency = LocalCacheData.getCurrentCurrencySymbol();

		cell_value = Global.decodeCellValue( cell_value );
		if ( related_data.pos === 0 ) {
			if ( row.type === TimeSheetViewController.TOTAL_ROW ) {
				punch_info = $( '<span class=\'total\' style=\'font-size: 11px\'></span>' );
				if ( Global.isSet( cell_value ) ) {
					punch_info.text( cell_value );
				} else {
					punch_info.text( '' );
				}

				return punch_info.get( 0 ).outerHTML;
			} else if ( row.type === TimeSheetViewController.REGULAR_ROW ) {
				punch_info = $( '<span class=\'top-line-span\' style=\'font-size: 11px\'></span>' );
				if ( Global.isSet( cell_value ) ) {
					punch_info.text( cell_value );
				} else {
					punch_info.text( '' );
				}
				return punch_info.get( 0 ).outerHTML;
			}

			return cell_value;
		}

		if ( row.type === TimeSheetViewController.PUNCH_ROW ) {
			punch = row[col_model.name + '_data'];
			related_punch = row[col_model.name + '_related_data'];
			time_span_prefix = $( '<span class=\'punch-prefix\'></span>' );
			time_span = $( '<span class=\'punch-time\'></span>' );
			time_span_suffix = $( '<span class=\'punch-suffix\'></span>' );
			break_span = $( '<span class=\'punch-break\'></span>' );

			if ( punch ) {
				exception = punch.exception;

				var break_label = '';
				var break_label_title = '';
				if ( punch.type_id == 20 ) {
					break_label = 'L';
					break_label_title = $.i18n._('Lunch');
				} else if ( punch.type_id == 30 ) {
					break_label = 'B';
					break_label_title = $.i18n._('Break');
				}

				var label_prefix = '';
				var label_prefix_title = '';
				if ( punch.note != '' ) {
					label_prefix = '*';
					label_prefix_title = $.i18n._('Note') +': '+ punch.note;
				}

				var label_suffix = '';
				var label_suffix_title = '';
				if ( punch.id == TTUUID.not_exist_id ) {
					label_suffix = 'P';
					label_suffix_title = 'P='+ $.i18n._('Processing');
				}
				if ( punch.latitude && punch.latitude != 0 && punch.longitude && punch.longitude != 0 ) {
					label_suffix = 'G';
					label_suffix_title = 'G='+ $.i18n._('GPS Location');
				}

				if ( punch.has_image ) {
					label_suffix = label_suffix + 'F';
					label_suffix_title = label_suffix_title + ' F='+ $.i18n._('Punch Image');
				}

				if ( punch.tainted ) {
					time_span.css( 'color', '#ff0000' );
				}
			} else if ( related_punch ) {
				exception = related_punch.exception;
			}

			if ( Global.isSet( label_prefix ) && label_prefix != '' ) {
				time_span_prefix.text( label_prefix );
				time_span_prefix.attr( 'title', label_prefix_title );
				content_div.append( time_span_prefix );
			}

			if ( Global.isSet( cell_value ) ) {
				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.append( time_span );

			if ( Global.isSet( label_suffix ) && label_suffix != ''  ) {
				time_span_suffix.text( label_suffix );
				time_span_suffix.attr( 'title', label_suffix_title );
				content_div.append( time_span_suffix );
			}

			if ( Global.isSet( break_label ) && break_label != '' ) {
				break_span.text( break_label );
				break_span.attr( 'title', break_label_title );
				content_div.append( break_span );
			}

			if ( exception ) {
				len = exception.length;
				for ( var i = 0; i < len; i++ ) {
					ex = exception[i];
					ex_span = $( '<span class=\'punch-exceptions\'></span>' );
					ex_span.css( 'color', ex.exception_color );
					ex_span.text( ex.exception_policy_type_id );
					ex_span.attr( 'title', ex.exception_policy_type_id + ': ' + ex.exception_policy_type );
					content_div.prepend( ex_span );
				}
			} else {
				// ex_span = $( '<span class=\'punch-exceptions\'></span>' );
				// ex_span.text( ' ' );
				// content_div.prepend( ex_span );
			}

		} else if ( row.type === TimeSheetViewController.EXCEPTION_ROW ) {
			exception = row[col_model.name + '_exceptions'];

			if ( Global.isSet( exception ) ) {
				len = exception.length;
				for ( var i = 0; i < len; i++ ) {
					ex = exception[i];
					ex_span = $( '<span class=\'punch-exceptions-center\'></span>' );
					ex_span.css( 'color', ex.exception_color );
					ex_span.text( ex.exception_policy_type_id );
					ex_span.attr( 'title', ex.exception_policy_type_id + ': ' + ex.exception_policy_type );

					content_div.append( ex_span );
				}
			}

		} else if ( row.type === TimeSheetViewController.REQUEST_ROW ) {
			time_span = $( '<span class=\'request\'></span>' );
			if ( Global.isSet( cell_value ) ) {
				time_span.text( cell_value );
				time_span.attr( 'title', createRequestToolTip( row[col_model.name + '_request'] ) );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.TOTAL_ROW ) {

			data = row[col_model.name + '_data'];
			time_span = $( '<span class=\'total\'></span>' );

			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}

					if ( time_sheet_view_controller.wage_btn && time_sheet_view_controller.wage_btn.getValue( true ) && data.hasOwnProperty( 'total_time_amount' ) && data.total_time_amount && data.hasOwnProperty( 'hourly_rate' ) && data.hourly_rate ) {
						time_span = $( '<div class=\'total--bold time-sheet-view-wage-container\'></div>' );
						cell_value = '<span class="time-sheet-view-wage-hour-rate">' + currency + Global.MoneyRound( data.hourly_rate ) + '/hr @</span>' +
							'<span class="time-sheet-view-wage-value">' + cell_value +
							'</span ><span class="time-sheet-view-wage-amount" >= ' + currency + Global.MoneyRound( data.total_time_amount ) +
							'</span>';
					}

					if ( time_sheet_view_controller.getPunchMode() === 'punch' ) {
						if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
							time_span.addClass( 'absence-override' );
						}
					} else {
						if ( !data.override && row.key === 'worked_time' ) {
							time_span.addClass( 'absence-override' );
						}
					}
				}

				time_span.html( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.REGULAR_ROW ) {

			content_div.addClass( 'top-line' );

			data = row[col_model.name + '_data'];

			time_span = $( '<span></span>' );
			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}

					if ( time_sheet_view_controller.wage_btn && time_sheet_view_controller.wage_btn.getValue( true ) && data.hasOwnProperty( 'total_time_amount' ) && data.total_time_amount && data.hasOwnProperty( 'hourly_rate' ) && data.hourly_rate ) {
						time_span = $( '<div class=\'time-sheet-view-wage-container\'></div>' );
						cell_value = '<span class="time-sheet-view-wage-hour-rate">' + currency + Global.MoneyRound( data.hourly_rate ) + '/hr @</span>' +
							'<span class="time-sheet-view-wage-value">' + cell_value +
							'</span ><span class="time-sheet-view-wage-amount" >= ' + currency + Global.MoneyRound( data.total_time_amount ) +
							'</span>';
					}

					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}
				}

				time_span.html( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.ABSENCE_ROW ) {

			var absence = row[col_model.name + '_data'];
			time_span = $( '<span></span>' );

			if ( Global.isSet( cell_value ) ) {

				if ( absence ) {

					if ( absence.override === true ) {
						time_span.addClass( 'absence-override' );
					}

					if ( absence.note ) {
						cell_value = '*' + cell_value;
					}
				}

				time_span.text( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else if ( row.type === TimeSheetViewController.ACCUMULATED_TIME_ROW ||
			row.type === TimeSheetViewController.PREMIUM_ROW ) {
			data = row[col_model.name + '_data'];
			time_span = $( '<span  style=\'width: 100%\'></span>' );

			if ( Global.isSet( cell_value ) ) {

				if ( data ) {

					if ( data.hasOwnProperty( 'note' ) && data.note ) {
						cell_value = '*' + cell_value;
					}

					if ( time_sheet_view_controller.wage_btn && time_sheet_view_controller.wage_btn.getValue( true ) && data.hasOwnProperty( 'total_time_amount' ) && data.total_time_amount && data.hasOwnProperty( 'hourly_rate' ) && data.hourly_rate ) {
						time_span = $( '<div class=\'time-sheet-view-wage-container\'></div>' );
						cell_value = '<span class="time-sheet-view-wage-hour-rate">' + currency + Global.MoneyRound( data.hourly_rate ) + '/hr @</span>' +
							'<span class="time-sheet-view-wage-value">' + cell_value +
							'</span ><span class="time-sheet-view-wage-amount" >= ' + currency + Global.MoneyRound( data.total_time_amount ) +
							'</span>';
					}
					if ( data.hasOwnProperty( 'override' ) && data.override === true ) {
						time_span.addClass( 'absence-override' );
					}
				}

				time_span.html( cell_value );

			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );

		} else {
			time_span = $( '<span class=\'punch-time\'></span>' );
			if ( Global.isSet( cell_value ) ) {
				time_span.text( cell_value );
			} else {
				time_span.text( '' );
			}
			content_div.prepend( time_span );
		}

		function createRequestToolTip( value ) {
			var label;
			if ( Global.isArray( value ) ) {
				label = calDAndA( value );
			} else if ( Global.isObject( value ) && value.status ) {
				label = value.status; //Use the label directly from the API.
			}

			function calDAndA( array ) {
				var len = array.length;
				var a = 0;
				var d = 0;
				var p = 0;
				var label = '';
				for ( var i = 0; i < len; i++ ) {
					var item = array[i];
					if ( item.status_id == 50 ) {
						a = a + 1;
					} else if ( item.status_id == 55 ) {
						d = d + 1;
					} else if ( item.status_id == 30 ) {
						p = p + 1;
					}
				}
				if ( a > 0 ) {
					label += ' ' + $.i18n._( 'Authorized' ) + ': ' + a;
				}
				if ( p > 0 ) {
					label += ' ' + $.i18n._( 'Pending' ) + ': ' + p;
				}
				if ( d > 0 ) {
					label += ' ' + $.i18n._( 'Declined' ) + ': ' + d;
				}
				return label;
			}

			return label;
		}

		content_div.addClass( 'date-column' );

		return content_div.get( 0 ).outerHTML;
	}

	onSelectRow( grid_id, row_id, target ) {
		var $this = this;
		var row_tr = $( target ).find( 'tr#' + row_id );
		$this.timesheet_grid.grid.find( 'td.ui-state-highlight' ).removeClass( 'ui-state-highlight' );
		var cells_array = [];
		var len = 0;
		if ( grid_id === 'timesheet_grid' ) {
			cells_array = $this.select_cells_Array;
			len = $this.select_cells_Array.length;
			$this.absence_select_cells_Array = [];
		} else if ( grid_id === 'absence_grid' ) {
			cells_array = $this.absence_select_cells_Array;
			len = $this.absence_select_cells_Array.length;
			$this.select_cells_Array = [];
		} else if ( grid_id === 'accumulated_grid' ) {
			cells_array = $this.accumulated_time_cells_array;
			len = $this.accumulated_time_cells_array.length;
		} else if ( grid_id === 'premium_grid' ) {
			cells_array = $this.premium_cells_array;
			len = $this.premium_cells_array.length;
		}
		this.select_punches_array = [];
		/* jshint ignore:start */
		for ( var i = 0; i < len; i++ ) {
			var info = cells_array[i];
			row_tr = $( target ).find( '#' + info.row_id );
			var cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.addClass( 'ui-state-highlight' ).attr( 'aria-selected', true );

			if ( info.punch && info.punch.id ) {

				if ( Global.isSet( info.punch.time_stamp ) ) { //date + time number
					var date = Global.strToDate( info.punch.punch_date ).format( 'MM-DD-YYYY' );
					var date_time = date + ' ' + info.punch.punch_time;
					info.punch.time_stamp_num = Global.strToDateTime( date_time ).getTime();
				} else {
					info.punch.time_stamp_num = info.time_stamp_num; //Uer time_stamp_num from cell select setting, a date number
				}
				this.select_punches_array.push( info.punch );
				this.select_punches_array.sort( function( a, b ) {
					return Global.compare( a, b, 'time_stamp_num' );
				} );
			}
		}
		/* jshint ignore:end */
	}

	unsetSelectedCells( grid_id ) {
		if ( grid_id == 'accumulated_grid' ) {
			grid_id = 'accumulated_time_grid';
		}

		if ( this.last_clicked_grid_id && grid_id != this.last_clicked_grid_id ) {
			//Use window setTimeout to make this code asyncchronous for speed, it's the fastest way.
			//web worker : 850ms
			//inline code : 700ms
			//setTimeout: 400ms
			window.setTimeout( function( t, n ) {
				t.grid_dic[n].grid.trigger( 'reloadGrid' );
			}, 0, this, this.last_clicked_grid_id );
		}

		//this.setDefaultMenu();
		this.last_clicked_grid_id = grid_id;
	}

	getRowData( grid_id, row_id ) {
		var $this = this;

		var row_data = null;

		if ( grid_id === 'absence_grid' ) {
			row_data = $this.absence_grid.getGridParam( 'data' );
		} else if ( grid_id === 'accumulated_grid' ) {
			row_data = $this.accumulated_time_grid.getGridParam( 'data' );
		} else if ( grid_id === 'premium_grid' ) {
			row_data = $this.premium_grid.getGridParam( 'data' );
		} else { //Should be: timesheet_grid
			row_data = $this.grid.getGridParam( 'data' );
		}

		var row = false;

		for ( var i in row_data ) {
			if ( row_data[i].id == row_id ) {
				row = row_data[i];
				break;
			}
		}

		return row;
	}

	onCellSelect( grid_id, row_id, cell_index, cell_val, target, e ) {
		$( '#ribbon_view_container .context-menu:visible a' ).click();

		if ( cell_index < 0 ) {
			this.unsetSelectedCells( grid_id );
			this.setDefaultMenu();
			return true; //continue default processing.
		}

		cell_index = parseInt( cell_index );

		var $this = this;
		var row;
		var colModel;
		var data_field;
		var punch;
		var related_punch;
		var cells_array = [];
		var date;

		if ( !this.is_edit && !this.is_add ) {
			$this.absence_model = false;
		}

		row = $this.getRowData( grid_id, row_id );

		if ( grid_id === 'timesheet_grid' ) {
			cells_array = $this.select_cells_Array;

			colModel = $this.grid.getGridParam( 'colModel' );
			data_field = colModel[cell_index].name;

			if ( row.type === TimeSheetViewController.REQUEST_ROW ) {
				var filter = { filter_data: {} };
				filter.filter_data.user_id = this.getSelectEmployee();
				filter.filter_data.start_date = $this.full_timesheet_data.timesheet_dates.start_display_date;
				filter.filter_data.end_date = $this.full_timesheet_data.timesheet_dates.end_display_date;
				filter.filter_data.id = [];

				$this.unsetSelectedCells( grid_id );
				var pending_requests = 0;
				var total_requests = 0;
				if ( Global.isArray( row[data_field + '_request'] ) ) {
					for ( var n in row[data_field + '_request'] ) {
						var obj = row[data_field + '_request'][n];
						filter.filter_data.id.push( obj.id );
						if ( obj.status == $.i18n._( 'PENDING' ) ) {
							pending_requests += 1;
						}
						total_requests += 1;
					}
				} else if ( row[data_field + '_request'] ) {
					//is object;
					filter.filter_data.id.push( row[data_field + '_request'].id );
					if ( row[data_field + '_request'].status == $.i18n._( 'PENDING' ) ) {
						pending_requests = 1;
					}
					total_requests = 1;
				} else {
					return;
				}

				Global.addViewTab( this.viewId, $.i18n._( 'TimeSheet' ), window.location.href );

				if ( total_requests > 0 ) {
					if ( this.getSelectEmployee() != LocalCacheData.getLoginUser().id && pending_requests > 0 ) {
						//Handle cases where an administrator who can see all requests might click on a pending request cell and want to be taken to MyAccount -> Requests
						//rather than MyAccount -> Request Authorization, which wouldn't show anything.
						if ( this.ownerOrChildPermissionValidate( 'request', 'view_child', filter.filter_data.id ) ) {
							IndexViewController.goToView( 'RequestAuthorization', filter );
						} else if ( this.viewPermissionValidate( 'request', filter.filter_data.id ) ) {
							IndexViewController.goToView( 'Request', filter );
						}
					} else {
						//If the request isn't pending, then go to MyAccount -> Requests, and MyAccount -> Request Authorization wouldn't show anything.
						if ( this.viewPermissionValidate( 'request', filter.filter_data.id ) ) {
							IndexViewController.goToView( 'Request', filter );
						}
					}
				}
				return;
			}

			if ( row && row[data_field + '_data'] ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			related_punch = row[data_field + '_related_data'];

			date = Global.strToDate( data_field, this.full_format );
		} else if ( grid_id === 'absence_grid' ) {
			cells_array = $this.absence_select_cells_Array;

			colModel = $this.absence_grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			// Error: Uncaught TypeError: Cannot read property 'punch_info_data' of undefined in interface/html5/#!m=TimeSheet&date=20151220&user_id=null&show_wage=0 line 3761
			if ( row ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			date = Global.strToDate( data_field, this.full_format );

			$this.absence_model = true;
		} else if ( grid_id === 'accumulated_grid' ) {

			cells_array = $this.accumulated_time_cells_array;

			colModel = $this.accumulated_time_grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			if ( row ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			date = Global.strToDate( data_field, this.full_format );
		} else if ( grid_id === 'premium_grid' ) {

			cells_array = $this.premium_cells_array;

			colModel = $this.premium_grid.getGridParam( 'colModel' );

			data_field = colModel[cell_index].name;

			if ( row ) {
				punch = row[data_field + '_data'];
			} else {
				punch = null;
			}

			date = Global.strToDate( data_field, this.full_format );

		}

		if ( Global.isValidDate( date ) == false ) {
			$this.unsetSelectedCells( grid_id );
			return false;
		}

		var info;
		var row_tr;
		var cell_td;
		//Clean all select cells first
		for ( var i = 0; i < cells_array.length; i++ ) {
			info = cells_array[i];
			row_tr = $( target ).find( '#' + info.row_id );
			$( target ).find( 'tr' ).removeClass( 'ui-state-highlight' );
			cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', false );
		}

		var date_str;
		var time_stamp_num;

		// Add multiple selectiend_display_date if click cell and hold ctrl or command
		if ( e.ctrlKey || e.metaKey ) {
			var found = false;
			for ( var i = 0; i < cells_array.length; i++ ) {
				info = cells_array[i];
				if ( row_id == info.row_id && cell_index == info.cell_index ) {
					cells_array.splice( i, 1 );
					found = true;
					break;
				}
			}

			date_str = date.format();
			time_stamp_num = date.getTime();

			if ( !found ) {
				if ( grid_id === 'timesheet_grid' ) {
					punch = getCellPunch( row_id, cell_index );
					related_punch = getRelatedPunch( row_id, cell_index );
					cells_array.push( {
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						related_punch: related_punch,
						date: date_str,
						time_stamp_num: time_stamp_num
					} );

					$this.select_cells_Array = cells_array;
					$this.select_cells_Array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
				} else if ( grid_id === 'absence_grid' ) {
					cells_array.push( {
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						date: date_str,
						time_stamp_num: time_stamp_num,
						src_object_id: ( row.punch_info_id ) ? row.punch_info_id : null,
					} );

					$this.absence_select_cells_Array = cells_array;
					$this.absence_select_cells_Array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
				} else if ( grid_id === 'premium_grid' ) {
					cells_array = [
						{
							row_id: row_id,
							cell_index: cell_index,
							cell_val: cell_val,
							date: date_str,
							time_stamp_num: time_stamp_num
						}
					];

					$this.premium_cells_array = cells_array;
				}
			}
		} else if ( e.shiftKey && cells_array.length > 0 ) {
			var status_id = cells_array[0].status_id; //Get status_id of first punch so we can defaults to that on new.

			//cell.row_id is numeric here.
			var start_row_index = parseInt( cells_array[0].row_id );
			var start_cell_index = parseInt( cells_array[0].cell_index );

			var end_row_index = row_id;
			var end_cell_index = cell_index;

			if ( start_row_index > end_row_index ) {
				var tmp_row_index = start_row_index;
				start_row_index = end_row_index;
				end_row_index = tmp_row_index;
			}

			if ( start_cell_index > end_cell_index ) {
				var tmp_cell_index = start_cell_index;
				start_cell_index = end_cell_index;
				end_cell_index = tmp_cell_index;
			}

			for ( var i = 0; i < cells_array.length; i++ ) {
				info = cells_array[i];

				var tmp_row_id = parseInt( info.row_id );

				if ( tmp_row_id < start_row_index ) {
					start_row_index = tmp_row_id;
				}
				if ( tmp_row_id > end_row_index ) {
					end_row_index = tmp_row_id;
				}

				if ( info.cell_index < start_cell_index ) {
					start_cell_index = info.cell_index;
				}
				if ( info.cell_index > end_cell_index ) {
					end_cell_index = info.cell_index;
				}
			}

			//If the click is inside the existing selection, truncate the existing selection to the click.
			//Check in ScheduleViewController.js for related change
			//Make sure to check for cells_array and cells_array.length before the other checks or when the user clicks into another grid while holding shift, it throws the following error:
			//Cannot read property 'cell_index' of undefined

			var uppermost_row_index = parseInt( cells_array[0].row_id );
			var lowermost_row_index = parseInt( cells_array[cells_array.length - 1].row_id );
			var leftmost_cell_index = parseInt( cells_array[0].cell_index );
			var rightmost_cell_index = parseInt( cells_array[cells_array.length - 1].cell_index );

			if ( cells_array && cells_array.length > 0 && cells_array[cells_array.length - 1].cell_index && cells_array[0].cell_index
				&& rightmost_cell_index >= cell_index
				&& leftmost_cell_index <= cell_index
				&& lowermost_row_index >= row_id
				&& uppermost_row_index <= row_id ) {
				end_row_index = row_id;
				end_cell_index = cell_index;
			}

			//build cells_array
			cells_array = [];

			for ( var i = start_row_index; i <= end_row_index; i++ ) {
				var r_index = i;
				for ( var j = start_cell_index; j <= end_cell_index; j++ ) {
					var c_index = j;

					row_tr = $( target ).find( 'tr#' + r_index );

					cell_td = $( row_tr.find( 'td' )[c_index] );

					cell_val = cell_td[0].outerHTML;

					if ( grid_id === 'timesheet_grid' ) {
						punch = getCellPunch( i, j );
						related_punch = getRelatedPunch( i, j );

						date = Global.strToDate( data_field, this.full_format );

						date_str = date.format();
						time_stamp_num = date.getTime();

						cells_array.push( {
							row_id: r_index,
							cell_index: c_index,
							cell_val: cell_val,
							punch: punch,
							status_id: status_id,
							related_punch: related_punch,
							date: date_str,
							time_stamp_num: time_stamp_num
						} );

					} else if ( grid_id === 'absence_grid' ) {
						colModel = $this.absence_grid.getGridParam( 'colModel' );

						data_field = colModel[c_index].name;

						punch = row[data_field + '_data'];

						date = Global.strToDate( data_field, this.full_format );

						date_str = date.format();
						time_stamp_num = date.getTime();

						cells_array.push( {
							row_id: r_index, //see bug #2149
							//row_id: r_index.toString(),
							cell_index: c_index,
							cell_val: cell_val,
							punch: punch,
							date: date_str,
							time_stamp_num: time_stamp_num,
							src_object_id: ( row.punch_info_id ) ? row.punch_info_id : null,
						} );
					} else if ( grid_id === 'accumulated_grid' ) {
						cells_array = [
							{
								row_id: row_id,
								cell_index: cell_index,
								cell_val: cell_val,
								date: date_str,
								time_stamp_num: time_stamp_num
							}
						];
						$this.accumulated_time_cells_array = cells_array;
					} else if ( grid_id === 'premium_grid' ) {
						cells_array = [
							{
								row_id: row_id,
								cell_index: cell_index,
								cell_val: cell_val,
								date: date_str,
								time_stamp_num: time_stamp_num
							}
						];
						$this.premium_cells_array = cells_array;
					}

				}
			}

			if ( grid_id === 'timesheet_grid' ) {
				$this.select_cells_Array = cells_array;
				$this.select_cells_Array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
			} else if ( grid_id === 'absence_grid' ) {
				$this.absence_select_cells_Array = cells_array;
				$this.absence_select_cells_Array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
			} else if ( grid_id === 'accumulated_grid' ) {
				$this.accumulated_time_cells_array = cells_array;
				$this.accumulated_time_cells_array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
			} else if ( grid_id === 'premium_grid' ) {
				$this.premium_cells_array = cells_array;
				$this.premium_cells_array.sort( Global.m_sort_by( ['time_stamp_num', 'row_id'] ) );
			}

		} else {
			date_str = date ? date.format() : '';
			time_stamp_num = date ? date.getTime() : 0;
			if ( grid_id === 'timesheet_grid' ) {
				//get the punch data.
				punch = getCellPunch( row_id, cell_index );

				related_punch = getRelatedPunch( row_id, cell_index );

				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						status_id: ( row && row.status_id ) ? row.status_id : null,
						related_punch: related_punch,
						date: date_str,
						time_stamp_num: time_stamp_num
					}
				];

				$this.select_cells_Array = cells_array;
			} else if ( grid_id === 'absence_grid' ) {
				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						punch: punch,
						date: date_str,
						time_stamp_num: time_stamp_num,
						src_object_id: ( row.punch_info_id ) ? row.punch_info_id : null,
					}
				];

				$this.absence_select_cells_Array = cells_array;
			} else if ( grid_id === 'accumulated_grid' ) {
				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						date: date_str,
						time_stamp_num: time_stamp_num
					}
				];

				$this.accumulated_time_cells_array = cells_array;
			} else if ( grid_id === 'premium_grid' ) {
				cells_array = [
					{
						row_id: row_id,
						cell_index: cell_index,
						cell_val: cell_val,
						date: date_str,
						time_stamp_num: time_stamp_num
					}
				];

				$this.premium_cells_array = cells_array;
			}

			if ( date && date.getYear() > 0 ) {
				this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
				this.highLightSelectDay( e );
				this.reLoadSubGridsSource();
			}

			this.unsetSelectedCells( grid_id );
		}

		//now set the selected punches array
		this.select_punches_array = [];
		for ( var n in cells_array ) {
			if ( cells_array[n].punch ) {
				this.select_punches_array.push( cells_array[n].punch );
			}
		}
		this.setTimesheetGridDragAble();

		function getCellPunch( row_id, cell_index ) {
			var punch = null;
			row = null;
			for ( var i in $this.timesheet_data_source ) {
				if ( $this.timesheet_data_source[i].id == row_id ) {
					row = $this.timesheet_data_source[i];
					break;
				}
			}

			if ( row ) {
				colModel = $this.grid.getGridParam( 'colModel' );
				data_field = colModel[cell_index].name;
				punch = row[data_field + '_data'] ? row[data_field + '_data'] : null;
			}
			return punch;
		}

		function getRelatedPunch( row_id, cell_index ) {
			var related_punch = null;
			row = null;
			for ( var i in $this.timesheet_data_source ) {
				if ( $this.timesheet_data_source[i].id == row_id ) {
					row = $this.timesheet_data_source[i];
					break;
				}
			}
			//row = $this.timesheet_data_source[row_id -1 ];
			if ( row ) {
				colModel = $this.grid.getGridParam( 'colModel' );
				data_field = colModel[cell_index].name;
				related_punch = row[data_field + '_related_data'] ? row[data_field + '_related_data'] : null;
			}
			return related_punch;
		}

		return true;
	}

	get_selected_punch_array() {
	}

	buildTimeSheetRequests() {
		var request_array = this.full_timesheet_data.request_data;
		var len = request_array.length;
		var request_row_index = null;

		for ( var i = 0; i < len; i++ ) {
			var request = request_array[i];

			var date_string = Global.strToDate( request.date_stamp ).format( this.full_format );

			var row;
			//Build Exception row at bottom
			if ( !request_row_index ) {
				row = {};
				row.punch_info = $.i18n._( 'Requests' );
				row.user_id = request.user_id;
				row[date_string] = request.status;
				row[date_string + '_request'] = request;

				row.type = TimeSheetViewController.REQUEST_ROW;
				this.timesheet_data_source.push( row );
				request_row_index = this.timesheet_data_source.length - 1;
			} else {
				row = this.timesheet_data_source[request_row_index];
				if ( !Global.isSet( row[date_string + '_request'] ) ) {
					row[date_string] = request.status;
					row[date_string + '_request'] = request;
				} else {

					if ( $.type( row[date_string + '_request'] ) === 'array' ) {
						row[date_string + '_request'].push( request );

					} else {
						row[date_string + '_request'] = [row[date_string + '_request']];
						row[date_string + '_request'].push( request );
					}

					row[date_string] = calDAndA( row[date_string + '_request'] );
				}
			}

		}

		function calDAndA( array ) {
			var len = array.length;
			var a = 0;
			var d = 0;
			var p = 0;
			var label = '';
			for ( var i = 0; i < len; i++ ) {
				var item = array[i];
				if ( item.status_id == 50 ) {
					a = a + 1;
				} else if ( item.status_id == 55 ) {
					d = d + 1;
				} else if ( item.status_id == 30 ) {
					p = p + 1;
				}
			}
			if ( a > 0 ) {
				label += ' A: ' + a;
			}
			if ( p > 0 ) {
				label += ' P: ' + p;
			}
			if ( d > 0 ) {
				label += ' D: ' + d;
			}
			return label;
		}
	}

	buildTimeSheetExceptions() {
		var exception_array = this.full_timesheet_data.exception_data;

		var len = exception_array.length;
		var timesheet_data_source_len = this.timesheet_data_source.length;
		var exception_row_index = null;
		for ( var i = 0; i < len; i++ ) {
			var ex = exception_array[i];
			var date_string = Global.strToDate( ex.date_stamp ).format( this.full_format );
			var row;
			//Build Exception row at bottom
			if ( !exception_row_index ) {
				row = {};
				row.punch_info = $.i18n._( 'Exceptions' );
				row.user_id = ex.user_id;
				row[date_string] = '';
				row[date_string + '_exceptions'] = [ex];

				row.type = TimeSheetViewController.EXCEPTION_ROW;
				this.timesheet_data_source.push( row );
				exception_row_index = this.timesheet_data_source.length - 1;
			} else {
				row = this.timesheet_data_source[exception_row_index];
				if ( !Global.isSet( row[date_string + '_exceptions'] ) ) {
					row[date_string + '_exceptions'] = [ex];
				} else {
					row[date_string + '_exceptions'].push( ex );
				}
			}

			var punch;
			var j;
			if ( !Global.isFalseOrNull( ex.punch_id ) ) {

				for ( var j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];

					if ( !row[date_string] ) {
						continue;
					}

					if ( row[date_string + '_data'] ) {
						punch = row[date_string + '_data'];
					} else if ( row[date_string + '_related__data'] ) {
						punch = row[date_string + '_related_data'];
					}

					if ( punch && punch.id === ex.punch_id && !punch.exception ) {
						punch.exception = [ex];
					}

				}

			} else if ( !Global.isFalseOrNull( ex.punch_control_id ) ) {
				for ( var j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];

					if ( !row[date_string] ) {
						continue;
					}

					if ( row[date_string + '_data'] ) {
						punch = row[date_string + '_data'];
					} else if ( row[date_string + '_related__data'] ) {
						punch = row[date_string + '_related_data'];
					}

					if ( punch && punch.punch_control_id === ex.punch_control_id && !punch.exception ) {
						punch.exception = [ex];
					}

				}
			}
		}
	}

	// Make sure Totle_time go to last item
	sortAccumulatedTotalData() {

		var sort_fields = ['order', 'punch_info'];
		this.accumulated_total_grid_source.sort( Global.m_sort_by( sort_fields ) );
	}

	// Make sure total time go to last item
	sortAccumulatedTimeData() {

		var sort_fields = ['order', 'punch_info'];
		this.accumulated_time_source.sort( Global.m_sort_by( sort_fields ) );
	}

	reLoadSubGridsSource( force ) {
		// Error: Uncaught TypeError: Cannot read property 'pay_period_id' of undefined in interface/html5/#!m=TimeSheet&date=20151214&user_id=null&show_wage=0 line 4290
		if ( !this.full_timesheet_data || !this.full_timesheet_data.timesheet_verify_data ) {
			return;
		}

		if ( !force ) {
			if ( this.full_timesheet_data.timesheet_verify_data.pay_period_id === this.pay_period_map[this.getSelectDate()] ||
				( !Global.isSet( this.full_timesheet_data.timesheet_verify_data.pay_period_id ) && !this.pay_period_map[this.getSelectDate()] )
			) {
				return;
			}
		}

		this.accumulated_time_source_map = {};
		this.branch_source_map = {};
		this.department_source_map = {};
		this.job_source_map = {};
		this.job_item_source_map = {};
		this.punch_tag_source_map = {};
		this.premium_source_map = {};
		this.accumulated_total_grid_source_map = {};
		this.accumulated_time_source = [];
		this.branch_source = [];
		this.department_source = [];
		this.job_source = [];
		this.job_item_source = [];
		this.punch_tag_source = [];
		this.premium_source = [];
		this.accumulated_total_grid_source = [];
		this.verification_grid_source = [];
		var $this = this;
		var start_date_string = this.start_date_picker.getValue();
		var user_id = this.getSelectEmployee();
		this.api_timesheet.getTimeSheetTotalData( user_id, start_date_string, {
			onResult: function( result ) {
				$this.onReloadSubGridResult( result );
			}
		} );
	}

	onReloadSubGridResult( result ) {
		var $this = this;
		result = result.getResult();
		$this.full_timesheet_data.accumulated_user_date_total_data = result.accumulated_user_date_total_data;
		$this.full_timesheet_data.meal_and_break_total_data = result.meal_and_break_total_data;
		$this.full_timesheet_data.pay_period_accumulated_user_date_total_data = result.pay_period_accumulated_user_date_total_data;
		$this.full_timesheet_data.timesheet_verify_data = result.timesheet_verify_data;
		$this.full_timesheet_data.pay_period_data = result.pay_period_data;
		$this.timesheet_verify_data = $this.full_timesheet_data.timesheet_verify_data;

		$this.buildSubGridsSource();
		$this.buildPunchNoteGridSource();
		$this.buildAccumulatedTotalGrid();
		$this.buildVerificationGridSource();

		$this.accumulated_time_grid.setData( $this.accumulated_time_source, false );
		$this.branch_grid.setData( $this.branch_source, false );
		$this.department_grid.setData( $this.department_source, false );
		$this.job_grid.setData( $this.job_source, false );
		$this.job_item_grid.setData( $this.job_item_source, false );
		$this.punch_tag_grid.setData( $this.punch_tag_source, false );
		$this.premium_grid.setData( $this.premium_source, false );

		if ( $this.accumulated_total_grid_source.length === 0 ) {
			$this.accumulated_total_grid_source.push();
		}
		$this.accumulated_total_grid.setData( $this.accumulated_total_grid_source, false );

		$this.punch_note_grid.setData( $this.punch_note_grid_source, false );
		$this.verification_grid.setData( $this.verification_grid_source, false );

		$this.setGridSize();
	}

	setDefaultMenuEditIcon( context_btn, grid_selected_length ) {
		let p_id = this.getPunchPermissionType();

		if ( !this.editPermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 && this.editOwnerOrChildPermissionValidate( p_id ) && ( this.getPunchMode() === 'punch' || p_id === 'absence' ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			if( grid_selected_length !== 0 ) {
				// This ensures the edit icon is still visible when nothing is selected, but should still be disabled. (to keep consistency with old design)
				ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
			}
		}
	}

	setEditMenuAddIcon( context_btn ) {
		let p_id = this.getPunchPermissionType();

		if ( !this.addPermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.is_add == true ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteIcon( context_btn ) {
		let p_id = this.getPunchPermissionType();

		if ( !this.deletePermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || !this.deleteOwnerOrChildPermissionValidate( p_id ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteAndNextIcon( context_btn ) {
		let p_id = this.getPunchPermissionType();

		if ( !this.deletePermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || !this.deleteOwnerOrChildPermissionValidate( p_id ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuEditIcon( context_btn, pId ) {
		let p_id = this.getPunchPermissionType();

		if ( !this.editPermissionValidate( p_id ) || this.edit_only_mode || this.is_mass_editing ) {
			//Not shown in edit only mode or mass edit. Mass edit should only show mass edit (need to set that part in mass edit icon).
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

		if ( !this.is_viewing || !this.editOwnerOrChildPermissionValidate( p_id ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuDeleteIcon( context_btn, grid_selected_length ) {
		let p_id = this.getPunchPermissionType();

		if ( !this.deletePermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length >= 1 && this.deleteOwnerOrChildPermissionValidate( p_id ) && ( this.getPunchMode() === 'punch' || p_id === 'absence' ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuDeleteAndNextIcon( context_btn, grid_selected_length ) {
		let p_id = this.getPunchPermissionType();

		if ( !this.deletePermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	setDefaultMenuViewIcon( context_btn, grid_selected_length ) {
		let p_id = this.getPunchPermissionType();

		if ( !this.viewPermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 && this.viewOwnerOrChildPermissionValidate() && ( this.getPunchMode() === 'punch' || p_id === 'absence' ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuAddPunchIcon( context_btn, grid_selected_length ) {
		let p_id = 'punch';

		if ( !this.addPermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

		if ( this.getPunchMode() === 'manual' && p_id !== 'absence' ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuAddAbsenceIcon( context_btn, grid_selected_length ) {
		let p_id = 'absence';

		if ( !this.addPermissionValidate( p_id ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

		if ( this.getPunchMode() === 'manual' && p_id !== 'absence' ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuSaveIcon( context_btn, grid_selected_length ) {
		let p_id = this.getPunchPermissionType();

		if ( ( !this.addPermissionValidate( p_id ) && !this.editPermissionValidate( p_id ) ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
		if ( this.getPunchMode() === 'manual' ) {
			if ( ( !this.addPermissionValidate( p_id ) && !this.editPermissionValidate( p_id ) ) ) {
				ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
			}
			if ( this.is_saving_manual_grid ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	buildAccmulatedOrderMap( total ) {

		if ( !total ) {
			return;
		}
		for ( var key in total ) {

			for ( var key1 in total[key] ) {
				this.accmulated_order_map[key1] = total[key][key1].order;
			}

		}
	}

	buildSubGridsSource() {
		var accumulated_user_date_total_data = this.full_timesheet_data.accumulated_user_date_total_data;
		var meal_and_break_total_data = this.full_timesheet_data.meal_and_break_total_data;
		var pay_period_accumulated_user_date_total_data = this.full_timesheet_data.pay_period_accumulated_user_date_total_data;

		this.accmulated_order_map = {};

		// Save the order, will do sort after all data prepared.
		if ( accumulated_user_date_total_data.total ) {
			this.buildAccmulatedOrderMap( accumulated_user_date_total_data.total );
		}

		if ( pay_period_accumulated_user_date_total_data ) {
			this.buildAccmulatedOrderMap( pay_period_accumulated_user_date_total_data );
		}

		//Build Accumulated Total Grid Pay_period column data
		var accumulated_time = pay_period_accumulated_user_date_total_data.accumulated_time;
		var premium_time = pay_period_accumulated_user_date_total_data.premium_time;
		var absence_time = pay_period_accumulated_user_date_total_data.absence_time_taken;

		this.marked_regular_row = {};
		if ( Global.isSet( accumulated_time ) ) {
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		} else {
			accumulated_time = { total: { label: $.i18n._( 'Total Time' ), total_time: '0' } };
			this.buildSubGridsData( accumulated_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
		}

		if ( Global.isSet( premium_time ) ) {
			this.buildSubGridsData( premium_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'premium_time' );
		}

		if ( Global.isSet( absence_time ) ) {
			this.buildSubGridsData( absence_time, 'pay_period', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'absence_time' );
		}

		this.accumulated_time_grid.grid.clearGridData(); //Clear accumulate time grid data before repopulating it, otherwise the data seems to get corrupted and display odd things, especially when removing an entire week on the manual timesheet. (total time still appears even after its removed)

		//Build Accumulated Total Grid Pay_period column data end
		var column_len = this.timesheet_columns.length;
		accumulated_time = { total: { label: $.i18n._( 'Total Time' ), total_time: '0' } };
		var date_string;
		var date;

		//Start on column that is right before the 7 days of the week.
		var start = ( column_len - 7 - 1 );
		for ( var i = start; i < column_len; i++ ) {
			date_string = this.timesheet_columns[i].name ? this.timesheet_columns[i].name : '';
			if ( date_string.indexOf( 'empty_cell' ) >= 0 || date_string.indexOf( 'punch_info' ) >= 0 ) {
				continue;
			}
			this.buildSubGridsData( accumulated_time, date_string, this.accumulated_time_source_map, this.accumulated_time_source, 'accumulated_time' );
		}
		this.buildSubGridsData( accumulated_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );

		for ( var key in accumulated_user_date_total_data ) {

			//Build Accumulated Total Grid week column data
			if ( key === 'total' ) {
				var total_result = accumulated_user_date_total_data.total;
				accumulated_time = total_result.accumulated_time;
				premium_time = total_result.premium_time;
				absence_time = total_result.absence_time_taken;

				if ( Global.isSet( accumulated_time ) ) {

					this.buildSubGridsData( accumulated_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'accumulated_time' );
				}

				if ( Global.isSet( premium_time ) ) {
					this.buildSubGridsData( premium_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'premium_time' );
				}

				if ( Global.isSet( absence_time ) ) {
					this.buildSubGridsData( absence_time, 'week', this.accumulated_total_grid_source_map, this.accumulated_total_grid_source, 'absence_time' );
				}

				continue;
			}

			//Build Accumulated Total Grid week column data end
			//Build all sub grids data
			//Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/#!m=TimeSheet&date=20151117&user_id=35367&show_wage=0 line 4478
			date = Global.strToDate( key );
			if ( !date ) {
				continue;
			}
			date_string = date.format( this.full_format );

			//Error: Uncaught TypeError: Cannot read property 'accumulated_time' of undefined
			if ( typeof accumulated_user_date_total_data[key] != 'undefined' ) {
				accumulated_time = accumulated_user_date_total_data[key].accumulated_time;
				var branch_time = accumulated_user_date_total_data[key].branch_time;
				var department_time = accumulated_user_date_total_data[key].department_time;
				var job_time = accumulated_user_date_total_data[key].job_time;
				var job_item_time = accumulated_user_date_total_data[key].job_item_time;
				var punch_tag_time = accumulated_user_date_total_data[key].punch_tag_time;
				premium_time = accumulated_user_date_total_data[key].premium_time;
			} else {
				Debug.Text( 'ERROR: accumulated_user_date_total_data[key] is null or undefined!', 'TimesheetViewController.js', 'TimesheetViewController', 'buildSubGridsSource', 1 );
			}

			if ( Global.isSet( accumulated_time ) ) {
				this.buildSubGridsData( accumulated_time, date_string, this.accumulated_time_source_map, this.accumulated_time_source, 'accumulated_time' );
			}

			if ( Global.isSet( branch_time ) ) {

				this.buildSubGridsData( branch_time, date_string, this.branch_source_map, this.branch_source, 'branch_time' );
			}

			if ( Global.isSet( department_time ) ) {

				this.buildSubGridsData( department_time, date_string, this.department_source_map, this.department_source, 'department_time' );
			}

			if ( Global.isSet( job_time ) ) {

				this.buildSubGridsData( job_time, date_string, this.job_source_map, this.job_source, 'job_time' );
			}

			if ( Global.isSet( job_item_time ) ) {

				this.buildSubGridsData( job_item_time, date_string, this.job_item_source_map, this.job_item_source, 'job_item_time' );
			}

			if ( Global.isSet( punch_tag_time ) ) {

				this.buildSubGridsData( punch_tag_time, date_string, this.punch_tag_source_map, this.punch_tag_source, 'punch_tag_time' );
			}

			if ( Global.isSet( premium_time ) ) {

				this.buildSubGridsData( premium_time, date_string, this.premium_source_map, this.premium_source, 'premium_time' );
			}

		}

		this.sortAccumulatedTotalData();
		this.sortAccumulatedTimeData();

		if ( Global.isSet( meal_and_break_total_data ) ) {

			for ( var key in meal_and_break_total_data ) {
				// Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/#!m=TimeSheet&date=20151119&user_id=55338&show_wage=0 line 4527
				date = Global.strToDate( key );
				if ( !date ) {
					continue;
				}
				date_string = date.format( this.full_format );

				this.buildBreakAndLunchData( meal_and_break_total_data[key], date_string );

			}

		}
	}

	buildBreakAndLunchData( array, date_string ) {
		var row;
		for ( var key in array ) {
			if ( !this.accumulated_time_source_map[key] ) {
				row = {};
				row.punch_info = array[key].break_name;
				array[key].key = key;
				row[date_string] = Global.getTimeUnit( array[key].total_time ) + ' (' + array[key].total_breaks + ')';
				row[date_string + '_data'] = array[key];
				this.timesheet_data_source.push( row );
				this.accumulated_time_source_map[key] = row;
			} else {
				row = this.accumulated_time_source_map[key];
				if ( !row[date_string] ) {
					array[key].key = key;
					row[date_string] = Global.getTimeUnit( array[key].total_time ) + ' (' + array[key].total_breaks + ')';

					row[date_string + '_data'] = array[key];
				}

			}
		}
	}

	addCellCount( key ) {
		switch ( key ) {
			case 'branch_time':
				this.branch_cell_count = this.branch_cell_count + 1;
				break;
			case 'department_time':
				this.department_cell_count = this.department_cell_count + 1;
				break;

			case 'premium_time':
				this.premium_cell_count = this.premium_cell_count + 1;
				break;
			case 'job_time':
				this.job_cell_count = this.job_cell_count + 1;
				break;
			case 'job_item_time':
				this.task_cell_count = this.task_cell_count + 1;
				break;
			case 'punch_tag_time':
				this.punch_tag_cell_count = this.punch_tag_cell_count + 1;
				break;

		}
	}

	buildSubGridsData( array, date_string, map, result_array, parent_key ) {
		var row;

		var start_date = this.start_date_picker.getValue();

		if ( !this.marked_regular_row[start_date] ) {
			this.marked_regular_row[start_date] = false; //Only mark the first regular time row in the week, as thats where the bold top-line is going to go.
		}

		for ( var key in array ) {
			if ( !map[key] ) {
				row = {};
				row.parent_key = parent_key;
				row.key = key;

				if ( parent_key === 'accumulated_time' ) {

					if ( key === 'total' || key === 'worked_time' ) {
						row.type = TimeSheetViewController.TOTAL_ROW;
					} else if ( ( this.marked_regular_row[start_date] == false || this.marked_regular_row[start_date] == key ) && key.indexOf( 'regular_time' ) === 0 ) {
						row.type = TimeSheetViewController.REGULAR_ROW;
						this.marked_regular_row[start_date] = key;
					} else {
						row.type = TimeSheetViewController.ACCUMULATED_TIME_ROW;
					}

					if ( array[key].override ) {
						row.is_override_row = true;
					}

				} else if ( parent_key === 'premium_time' ) {
					row.type = TimeSheetViewController.PREMIUM_ROW;
				}

				if ( this.accmulated_order_map[key] ) {
					row.order = this.accmulated_order_map[key];
				}

				row.punch_info = array[key].label;

				var key_array = key.split( '_' );
				var no_id = false;
				if ( key_array.length > 1 && key_array[1] == '0' ) {
					no_id = true;
				}

				array[key].key = key;
				row[date_string] = Global.getTimeUnit( array[key].total_time );
				row[date_string + '_data'] = array[key];

				//if id == 0, put the row as first row.
				if ( no_id ) {
					result_array.unshift( row );
				} else {
					result_array.push( row );
				}

				map[key] = row;
			} else {
				row = map[key];
				if ( row[date_string] && key === 'total' ) { //Override total cell data since we set all to 00:00 at beginning
					array[key].key = key;
					row[date_string] = Global.getTimeUnit( array[key].total_time );
					row[date_string + '_data'] = array[key];

					if ( row.parent_key === 'accumulated_time' ) {
						if ( array[key].override ) {
							row.is_override_row = true;
						}
					}

				} else {

					array[key].key = key;
					row[date_string] = Global.getTimeUnit( array[key].total_time );
					row[date_string + '_data'] = array[key];

					if ( row.parent_key === 'accumulated_time' ) {
						if ( array[key].override ) {
							row.is_override_row = true;
						}
					}
				}

			}

			this.addCellCount( parent_key );
		}
	}

	timeSheetVerifyPermissionValidate() {
		if ( PermissionManager.validate( 'punch', 'verify_time_sheet' ) &&
			this.timesheet_verify_data.hasOwnProperty( 'pay_period_verify_type_id' ) &&
			this.timesheet_verify_data.pay_period_verify_type_id != 10 ) {
			return true;
		}

		return false;
	}

	buildVerificationGridSource() {
		this.verification_grid_source = [];
		var $this = this;
		var verify_action_bar = $( this.el ).find( '.verification-action-bar' );
		var verify_grid_div = $( this.el ).find( '.verification-grid-div' );
		var verify_btn = $( this.el ).find( '.verify-button' );
		var verify_title = $( this.el ).find( '.verification-grid-title' );
		var verify_des = $( this.el ).find( '.verify-description' );

		if ( this.timeSheetVerifyPermissionValidate() &&
			Global.isSet( this.timesheet_verify_data.pay_period_id ) &&
			Global.isSet( this.timesheet_verify_data.pay_period_verify_type_id ) &&
			this.timesheet_verify_data.pay_period_verify_type_id !== '10' ) {

			if ( !this.timesheet_verify_data.display_verify_button ) {
				verify_btn.css( 'display', 'none' );
				verify_title.css( 'display', 'none' );
			} else {
				verify_btn.css( 'display', 'inline-block' );
				verify_title.css( 'display', 'block' );
			}

			verify_grid_div.css( 'display', 'block' );
			verify_des.text( this.timesheet_verify_data.verification_status_display );

			if ( this.timesheet_verify_data.verification_box_color ) {
				verify_action_bar.css( 'background', this.timesheet_verify_data.verification_box_color );
			} else {
				verify_action_bar.css( 'background', '#ffffff' );
			}

			verify_btn.unbind( 'click' ).bind( 'click', Global.debounce( function VerifyTimeSheet( e ) {
				$(e.target).prop( 'disabled', true ); //Disable the verify button during processing to avoid the user from getting impatient and clicking it multiple times.

				TAlertManager.showConfirmAlert( $this.timesheet_verify_data.verification_confirmation_message, $.i18n._('TimeSheet Verification Agreement'), function( flag ) {
					if ( flag ) {
						$this.api_timesheet.verifyTimeSheet( $this.getSelectEmployee(), $this.timesheet_verify_data.pay_period_id, {
								onResult: function( result ) {
									$(e.target).prop( 'disabled', false );

									if ( result && result.isValid() ) {
										$this.search();
									} else {
										TAlertManager.showErrorAlert( result );
									}
								},
								onError: function() {
									$(e.target).prop( 'disabled', false );
								}
							} );
					} else {
						$(e.target).prop( 'disabled', false );
					}
				}, $.i18n._('I Agree'), $.i18n._( 'I Do Not Agree' ) );
			}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );
		} else {
			verify_btn.css( 'display', 'none' );
			verify_grid_div.css( 'display', 'none' );
			return;
		}

		var verification_data = this.timesheet_verify_data.verification_window_dates.start + ' ' + $.i18n._( 'to' ) + ' ' + this.timesheet_verify_data.verification_window_dates.end;

		var pay_period_data = this.pay_period_header;

		this.verification_grid_source.push( { pay_period: pay_period_data, verification: verification_data } );

		//This can be called by clicking on a different date column header, or using the top right refresh icon, so not all grids are resized at that time necessarily.
		$this.setGridHeight( 'verification_grid' );
	}

	buildPunchNoteGridSource() {
		this.punch_note_grid_source = [];
		var punch_array = this.full_timesheet_data.punch_data;
		var user_date_total_array = this.full_timesheet_data.user_date_total_data;
		var len = punch_array.length;
		var len1 = user_date_total_array.length;
		var last_control_id = '';
		var date;
		var date_string;
		for ( var i = 0; i < len; i++ ) {
			var punch = punch_array[i];
			date = Global.strToDate( punch.date_stamp );
			date_string = date.format();
			if ( punch.note && punch.punch_control_id !== last_control_id ) {
				this.punch_note_account = this.punch_note_account + 1;
				this.punch_note_grid_source.push( { note: date_string + ' @ ' + punch.punch_time + ': ' + punch.note.replace( /\n/g, ' ' ) } );
				last_control_id = punch.punch_control_id;
			}
		}
		for ( var x = 0; x < len1; x++ ) {
			var user_date_total = user_date_total_array[x];
			date = Global.strToDate( user_date_total.date_stamp );
			date_string = date.format();
			if ( user_date_total.note ) {
				this.punch_note_account = this.punch_note_account + 1;
				this.punch_note_grid_source.push( { note: date_string + ' @ ' + Global.getTimeUnit( user_date_total.total_time ) + ': ' + user_date_total.note.replace( /\n/g, ' ' ) } );
			}
		}
	}

	buildAbsenceSource() {
		var map = {};
		this.absence_source = [];
		this.absence_original_source = [];
		var absence_array = this.full_timesheet_data.user_date_total_data;
		var len = absence_array.length;
		var row;
		var row_id_counter = 1;

		for ( var i = 0; i < len; i++ ) {
			var absence = absence_array[i];

			if ( absence.object_type_id != 50 ) {
				continue;
			}

			this.absence_original_source.push( absence );
			var date = Global.strToDate( absence.date_stamp );
			var date_string = date.format( this.full_format );
			var key = absence.src_object_id + '-' + absence.pay_code_id;

			if ( !map[key] ) {
				row = {};
				row.id = row_id_counter;
				row.type = TimeSheetViewController.ABSENCE_ROW;
				row.punch_info = absence.name; //Was: absence.absence_policy
				row.punch_info_id = absence.src_object_id;
				row.user_id = absence.user_id;
				row[date_string] = Global.getTimeUnit( absence.total_time );
				row[date_string + '_data'] = absence;
				this.absence_source.push( row );
				map[key] = row;
			} else {
				row = map[key];
				if ( row[date_string] ) {
					row = {};
					row.id = row_id_counter;
					row.type = TimeSheetViewController.ABSENCE_ROW;
					row.punch_info = absence.name; //Was: absence.absence_policy
					row.punch_info_id = absence.src_object_id;
					row.user_id = absence.user_id;
					row[date_string] = Global.getTimeUnit( absence.total_time );

					row[date_string + '_data'] = absence;
					this.absence_source.push( row );
					map[key] = row;

				} else {

					this.lastDayIsOverride( date, row, absence );
					row[date_string] = Global.getTimeUnit( absence.total_time );
					row[date_string + '_data'] = absence;
				}
			}

			row_id_counter++;

			this.absence_cell_count = this.absence_cell_count + 1;

		}

		if ( this.absence_source.length === 0 ) {
			row = {};
			row.id = 1;
			row.punch_info = '';
			row.user_id = this.getSelectEmployee();
			this.absence_source.push( row );
		}
	}

	lastDayIsOverride( current_date, row, current_data ) {

		var last_date = new Date( new Date( current_date.getTime() ).setDate( current_date.getDate() - 1 ) );

		var date_str = last_date.format( this.full_format );

		var data = row[date_str + '_data'];

		if ( data && data.override && current_data.src_object_id === data.src_object_id ) {
			return true;
		}

		return false;
	}

	buildTimeSheetSource() {
		this.select_punches_array = [];
		this.timesheet_data_source = [];

		var punch_array = this.full_timesheet_data.punch_data;
		var len = punch_array.length;
		var row;
		var new_row;
		var row_id_counter = 1;
		for ( var i = 0; i < len; i++ ) {
			var punch = punch_array[i];
			// Error: TypeError: Global.strToDate(...) is null in interface/html5/framework/jquery.min.js?v=9.0.1-20151022-081724 line 2 > eval line 4869
			// Punch must have a date
			if ( !punch.date_stamp ) {
				continue;
			}
			var date = Global.strToDate( punch.date_stamp );
			var date_string = date.format( this.full_format );

			var punch_status_id = punch.status_id;

			//row 1.
			if ( i === 0 ) {
				row = {};
				row.id = row_id_counter;
				row_id_counter++;
				row.punch_info = punch.status;
				row.status_id = punch_status_id;
				row.user_id = punch.user_id;
				row[date_string] = punch.punch_time;
				row[date_string + '_data'] = punch;
				row[date_string + '_related_data'] = null;
				row.status_id = punch_status_id;
				row.type = TimeSheetViewController.PUNCH_ROW;
				this.timesheet_data_source.push( row );

				if ( punch_status_id == 10 ) {

					var our_row = {};
					our_row.punch_info = $.i18n._( 'Out' );
					our_row.user_id = punch.user_id;
					our_row[date_string] = '';
					our_row[date_string + '_data'] = null;
					our_row[date_string + '_related_data'] = punch;
					our_row.status_id = 20;
					our_row.type = TimeSheetViewController.PUNCH_ROW;
					our_row.id = row_id_counter;
					row_id_counter++;
					this.timesheet_data_source.push( our_row );

				} else {
					new_row = {};
					new_row.punch_info = $.i18n._( 'In' );
					new_row.user_id = punch.user_id;
					new_row[date_string] = '';
					new_row[date_string + '_data'] = null;
					new_row[date_string + '_related_data'] = punch;
					new_row.status_id = 10;
					new_row.type = TimeSheetViewController.PUNCH_ROW;
					new_row.id = row_id_counter;
					row_id_counter++;
					this.timesheet_data_source.splice( this.timesheet_data_source.length - 1, 0, new_row );
				}

			} else {

				var find_position = false;
				var timesheet_data_source_len = this.timesheet_data_source.length;
				for ( var j = 0; j < timesheet_data_source_len; j++ ) {
					row = this.timesheet_data_source[j];
					if ( row[date_string] ) {
						continue;
					} else if ( !row[date_string] && row[date_string + '_related_data'] ) {
						var related_punch = row[date_string + '_related_data'];

						if ( related_punch.punch_control_id === punch.punch_control_id ) {
							row[date_string] = punch.punch_time;
							row[date_string + '_data'] = punch;
							find_position = true;
							break;
						}
					} else if ( !row[date_string] && !row[date_string + '_related_data'] && punch.status_id == row.status_id ) {
						row[date_string] = punch.punch_time;
						row[date_string + '_data'] = punch;
						row[date_string + '_related_data'] = null;
						find_position = true;

						if ( punch.status_id == 10 ) {
							new_row = this.timesheet_data_source[j + 1];
							new_row[date_string] = '';
							new_row[date_string + '_data'] = null;
							new_row[date_string + '_related_data'] = punch;
						} else {
							new_row = this.timesheet_data_source[j - 1];
							new_row[date_string] = '';
							new_row[date_string + '_data'] = null;
							new_row[date_string + '_related_data'] = punch;
						}

						break;
					}
				}

				//Need add a new row
				if ( !find_position ) {
					row = {};
					row.punch_info = punch.status;
					row.user_id = punch.user_id;
					row[date_string] = punch.punch_time;
					row[date_string + '_data'] = punch;
					row[date_string + '_related_data'] = null;
					row.status_id = punch_status_id;
					row.type = TimeSheetViewController.PUNCH_ROW;
					row.id = row_id_counter;
					row_id_counter++;
					this.timesheet_data_source.push( row );

					if ( punch_status_id == 10 ) {

						new_row = {};
						new_row.punch_info = $.i18n._( 'Out' );
						new_row.user_id = punch.user_id;
						new_row[date_string] = '';
						new_row[date_string + '_data'] = null;
						new_row[date_string + '_related_data'] = punch;
						new_row.status_id = 20;
						new_row.type = TimeSheetViewController.PUNCH_ROW;
						new_row.id = row_id_counter;
						row_id_counter++;
						this.timesheet_data_source.push( new_row );

					} else {
						new_row = {};
						new_row.punch_info = $.i18n._( 'In' );
						new_row.user_id = punch.user_id;
						new_row[date_string] = '';
						new_row[date_string + '_data'] = null;
						new_row[date_string + '_related_data'] = punch;
						new_row.status_id = 10;
						new_row.type = TimeSheetViewController.PUNCH_ROW;
						new_row.id = row_id_counter;
						row_id_counter++;
						this.timesheet_data_source.splice( this.timesheet_data_source.length - 1, 0, new_row );
					}
				}
			}
		}

		row = {};
		row.punch_info = $.i18n._( 'In' );
		row.user_id = this.getSelectEmployee();
		row.status_id = 10;
		row.type = TimeSheetViewController.PUNCH_ROW;
		row.id = row_id_counter;
		row_id_counter++;
		this.timesheet_data_source.push( row );

		row = {};
		row.punch_info = $.i18n._( 'Out' );
		row.user_id = this.getSelectEmployee();
		row.status_id = 20;
		row.type = TimeSheetViewController.PUNCH_ROW;
		row.id = row_id_counter;
		row_id_counter++;
		this.timesheet_data_source.push( row );
	}

	buildTimeSheetsColumns() {
		this.timesheet_columns = [];
		if ( this.getPunchMode() === 'manual' ) {
			var cost_center_cols = [
				{ 'name': 'plus_sign', 'width': 25 },
				{ 'name': 'minus_sign', 'width': 25 }
			];

			if ( this.show_branch_ui ) {
				cost_center_cols.push( { 'name': 'branch', 'width': 125 } );
			}

			if ( this.show_department_ui ) {
				cost_center_cols.push( { 'name': 'department', 'width': 125 } );
			}

			if ( Global.getProductEdition() >= 20 ) {
				if ( this.show_job_ui ) {
					cost_center_cols.push( { 'name': 'job', 'width': 170 } );
				}

				if ( this.show_job_item_ui ) {
					cost_center_cols.push( { 'name': 'job_item', 'width': 170 } );
				}

				if ( this.show_punch_tag_ui ) {
					cost_center_cols.push( { 'name': 'punch_tag', 'width': 170 } );
				}
			}

			var last_col_index = cost_center_cols.length - 1;
			for ( var i = 0; i < cost_center_cols.length; i++ ) {
				if ( i == last_col_index ) {
					var column = {
						name: 'punch_info',
						index: 'punch_info',
						label: ' ',
						width: 170,
						sortable: false,
						title: false,
						formatter: this.onCellFormat,
						fixed: true
					};
				} else {
					var column = {
						name: 'empty_cell_' + i,
						index: 'empty_cell_' + i,
						label: ' ',
						width: cost_center_cols[i].width,
						sortable: false,
						title: false,
						fixed: true
					};
				}
				this.timesheet_columns.push( column );
			}
		} else {
			var punch_in_out_column = {
				name: 'punch_info',
				index: 'punch_info',
				label: ' ',
				//if not set to 0 in punch timesheet mode, the date column headers are a few px out of alignment and look bad.
				//see #2091 notes for link to the percent-based js fiddle
				width: 100,
				sortable: false,
				title: false,
				formatter: this.onCellFormat,
				fixed: false
			};
			this.timesheet_columns.push( punch_in_out_column );
		}

		//save full week columns map use to build no pey period column
		this.column_maps = [];
		for ( var i = 0; i < 7; i++ ) {
			var current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );
			var header_text = current_date.format( this.weekly_format );

			//Localize the day of week and month text.
			if ( LocalCacheData.getLoginData().language != 'en' && Global.isString( header_text ) ) {
				var split_header_text_array = header_text.split( ',' );
				var split_header_text_month = split_header_text_array[1].split( ' ' );
				header_text = $.i18n._( split_header_text_array[0] ) + ', ' + $.i18n._( split_header_text_month[1] ) + ' ' + split_header_text_month[2];
			}

			var data_field = current_date.format( this.full_format );

			this.column_maps.push( current_date.format() );

			var column_info = {
				resizable: false,
				name: data_field,
				index: data_field,
				label: header_text,
				width: 100,
				sortable: false,
				title: false,
				formatter: this.onCellFormat
			};
			this.timesheet_columns.push( column_info );
		}

		return this.timesheet_columns;
	}

	getDefaultDisplayColumns( callback ) {
		// Overriden to allow use of initLayout in BaseViewController, but no default display columns in this view, hence this function is 'disabled'
		callback();
	}

	setSelectLayout() {
		var $this = this;

		if ( !Global.isSet( this.grid ) ) {
			var grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid = $( this.el ).find( '#' + this.ui_id + '_grid' );
		}

		if ( !this.select_layout ) { //Set to defalt layout if no layout at all
			this.select_layout = { id: '' };
			this.select_layout.data = { filter_data: {}, filter_sort: {} };
		}

		if ( this.select_layout.data.mode ) {
			this.toggle_button.setValue( this.select_layout.data.mode );
		}

		this.wage_btn.setValue( this.select_layout.data.show_wage ? true : false );

		this.timezone_btn.setValue( this.select_layout.data.use_employee_timezone ? true : false );

		//search panel doesn't always exist.
		if ( this.search_panel ) {
			//Set Previous Saved layout combobox in layout panel
			var layouts_array = this.search_panel.getLayoutsArray();
			if ( this.previous_saved_layout_selector ) {
				this.previous_saved_layout_selector.empty();
			}

			if ( layouts_array && layouts_array.length > 0 ) {
				this.previous_saved_layout_div.css( 'display', 'inline' );

				var len = layouts_array.length;
				for ( var i = 0; i < len; i++ ) {
					var item = layouts_array[i];
					this.previous_saved_layout_selector.append( $( '<option value="' + item.id + '"></option>' ).text( item.name ) );
				}

				$( this.previous_saved_layout_selector.find( 'option' ) ).filter( function() {
					return $( this ).attr( 'value' ) === $this.select_layout.id;
				} ).prop( 'selected', true ).attr( 'selected', true );

			} else {
				if ( this.previous_saved_layout_div ) {
					this.previous_saved_layout_div.css( 'display', 'none' );
				}
			}

			//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
			if ( LocalCacheData.default_filter_for_next_open_view ) {
				this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;
				LocalCacheData.default_filter_for_next_open_view = null;
			}

			this.filter_data = this.select_layout.data.filter_data;

			this.setSearchPanelFilter( true ); //Auto change to property tab when set value to search fields.
		}

		// this.search( true ); // get punches base on userid, data and filter - commented out as this is already called in BaseViewController.initLayout()
	}

	//Start Drag
	setTimesheetGridDragAble() {
		var $this = this;

		var position = 0;
		var cells = this.grid.grid.find( '.date-column' ).parents( 'td' );
		cells.attr( 'draggable', true );

		cells.off( 'dragstart' ).on( 'dragstart', function( event ) {

			var td = event.target;
			if ( $this.select_punches_array.length < 1 || !$( td ).hasClass( 'ui-state-highlight' ) || !$this.select_drag_menu_id ) {
				return false;
			}

			var container = $( '<div class=\'drag-holder-div\'></div>' );

			var len = $this.select_punches_array.length;

			for ( var i = 0; i < len; i++ ) {
				var punch = $this.select_punches_array[i];

				var span = $( '<span class=\'drag-span\'></span>' );
				span.text( punch.status + ': ' + punch.time_stamp );
				container.append( span );
			}

			$( 'body' ).find( '.drag-holder-div' ).remove();

			$( 'body' ).append( container );

			event.originalEvent.dataTransfer.setData( 'Text', 'timesheet' );//JUST ELEMENT references is ok here NO ID

			if ( event.originalEvent.dataTransfer.setDragImage ) {
				event.originalEvent.dataTransfer.setDragImage( container[0], 0, 0 );
			}

			return true;

		} );

		cells.off( 'dragover' ).on( 'dragover', function( e ) {

			var event = e.originalEvent;

			event.preventDefault();
			var $this = this;
			var target = $( this );

			$( '.timesheet-drag-over' ).removeClass( 'timesheet-drag-over' );
			$( '.drag-over-top' ).removeClass( 'drag-over-top' );
			$( '.drag-over-center' ).removeClass( 'drag-over-center' );
			$( '.drag-over-bottom' ).removeClass( ' drag-over-bottom' );

			$( $this ).addClass( 'timesheet-drag-over' );

			//judge which area mouse on in the target cell and set proper style, Keep checking this in drag event.
			if ( event.pageY - target.offset().top <= 8 ) {
				position = -1;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-top' );
			} else if ( event.pageY - target.offset().top >= target.height() - 5 ) {
				position = 1;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-bottom' );
			} else {
				position = 0;
				target.removeClass( 'drag-over-top drag-over-center drag-over-bottom' ).addClass( 'drag-over-center' );
			}

		} );

		cells.off( 'dragend' ).on( 'dragend', function( event ) {

			$( '.timesheet-drag-over' ).removeClass( 'timesheet-drag-over' );
			$( '.drag-over-top' ).removeClass( 'drag-over-top' );
			$( '.drag-over-center' ).removeClass( 'drag-over-center' );
			$( '.drag-over-bottom' ).removeClass( ' drag-over-bottom' );
			$( 'body' ).find( '.drag-holder-div' ).remove();

		} );

		cells.off( 'drop' ).on( 'drop', function( event ) {

			event.preventDefault();
			if ( event.stopPropagation ) {
				event.stopPropagation(); // stops the browser from redirecting.
			}

			$( this ).removeClass( 'drag-over-top drag-over-center drag-over-bottom timesheet-drag-over' );
			var target_cell = event.currentTarget;
			var i = 0; //start index;

			if ( $( target_cell ).index() === 0 ) {
				return;
			}

			//Error: Uncaught TypeError: Cannot read property 'punch_date' of undefined in /interface/html5/#!m=TimeSheet&date=20141118&user_id=32916 line 4563
			if ( !$this.select_punches_array || !$this.select_punches_array[i] ) {
				return;
			}

			var punch = $this.select_punches_array[i];

			var punch_date = Global.strToDate( punch.punch_date );

			var row = $this.timesheet_data_source[target_cell.parentNode.rowIndex - 1];

			//Error: Uncaught TypeError: Cannot read property 'status_id' of undefined in /interface/html5/#!m=TimeSheet&date=20150108&user_id=1068 line 5174
			if ( !row ) {
				return;
			}

			var colModel = $this.grid.getGridParam( 'colModel' );

			var data_field = colModel[target_cell.cellIndex].name;

			var target_punch = row[data_field + '_data'];

			var target_related_punch = row[data_field + '_related_data'];

			var target_column_date = Global.strToDate( data_field, $this.full_format );

			var first_select_date = punch_date;

			var time_offset = target_column_date.getTime() - punch_date.getTime();

			var target_column_date_str = target_column_date.format();

			savePunch();

			function savePunch() {
				//Error: Uncaught TypeError: Cannot read property 'date_stamp' of undefined in /interface/html5/#!m=TimeSheet&date=20141229&user_id=39555 line 5207
				if ( !$this.select_punches_array ) {
					return;
				}

				var new_punch_id = punch.id;
				var target_id = false;
				var target_status_id = row.status_id;
				var action_type = $this.select_drag_menu_id === 'move' ? 1 : 0;

				//Issue #2008 - All in-punches need target_id to be false to ensure that each pair retains its punch_control settings.
				//Most out-punches need their target id to be the related in-punch.
				//If these conditions are not met, copying groups of punches with different punch_control data will result in all copied punches having the same punch_control data as the first punch pair.
				if ( target_punch && punch.status_id == 20 ) {
					target_id = target_punch.id;
					target_status_id = false;
				} else if ( target_related_punch ) {
					target_id = target_related_punch.id;
					if ( target_related_punch.status_id == 10 ) {
						position = 1;
					} else {
						position = -1;
					}
					target_status_id = false;
				}

				var api_punch_control = TTAPI.APIPunchControl;

				api_punch_control.dragNdropPunch( new_punch_id, target_id, target_status_id, position, action_type, target_column_date_str, {
					onResult: function( result ) {
						var result_data = result.getResult();
						//Error: Uncaught TypeError: Cannot read property 'date_stamp' of undefined in interface/html5/#!m=TimeSheet&date=20150831&user_id=129895&show_wage=0 line 5286
						if ( result && result.isValid() && $this.select_punches_array && $this.select_punches_array.length > 0 ) {
							i = i + 1;
							if ( i > $this.select_cells_Array.length - 1 ) {
								$this.search( true );
								return;
							}
							//Error: Uncaught TypeError: Cannot read property 'date_stamp' of undefined in interface/html5/#!m=TimeSheet&date=20150831&user_id=129895&show_wage=0 line 5286
							if ( !$this.select_punches_array[i] ) {
								$this.search( true );
								return;
							}
							while ( !$this.select_punches_array[i].date_stamp ) {
								i = i + 1;
								if ( i > $this.select_cells_Array.length - 1 ) {
									$this.search( true );
									return;
								}
							}
							position = 1; //put next punch below last one
							var last_date_string = target_column_date_str;
							punch = $this.select_punches_array[i];
							punch_date = Global.strToDate( punch.punch_date );
							row = $this.timesheet_data_source[target_cell.parentNode.rowIndex - 1];
							colModel = $this.grid.getGridParam( 'colModel' );
							data_field = colModel[target_cell.cellIndex].name;
							time_offset = punch_date.getTime() - first_select_date.getTime();
							//drop column date
							target_column_date = Global.strToDate( data_field, $this.full_format );
							//Real target column date str
							target_column_date_str = new Date( target_column_date.getTime() + time_offset ).format();
							target_punch = { id: result_data };
							target_related_punch = null;
							if ( target_column_date_str !== last_date_string ) {
								position = 0;
								target_punch = null;
							}
							savePunch();
						} else {
							TAlertManager.showAlert( $.i18n._( 'Unable to drag and drop punch to the specified location' ) );
							if ( i > 0 ) {
								$this.search( true );
							}
						}
					}
				} );
			}
		} );
	}

	setPunchModeClass() {
		this.$el.removeClass( 'timesheet-punch-mode' );
		this.$el.removeClass( 'timesheet-manual-mode' );
		this.getPunchMode() === 'punch' ? this.$el.addClass( 'timesheet-punch-mode' ) : this.$el.addClass( 'timesheet-manual-mode' );
	}

	initData() {
		var $this = this;
		Global.removeViewTab( this.viewId );
		var loginUser = LocalCacheData.getLoginUser();
		this.initOptions();
		ProgressBar.showOverlay();
		// Set Wage
		if ( !LocalCacheData.last_timesheet_selected_show_wage ) {
			this.wage_btn.setValue( false );
		} else {
			this.wage_btn.setValue( LocalCacheData.last_timesheet_selected_show_wage );
		}

		//Error: TypeError: Cannot read property 'show_wage' of null
		//just need to check that the variable exists before checking properties for the case of the LocalCacheData being empty
		if ( Global.isSet( LocalCacheData.getAllURLArgs() ) && LocalCacheData.getAllURLArgs().show_wage ) {
			this.wage_btn.setValue( LocalCacheData.getAllURLArgs().show_wage === '1' ? true : false );
		}

		// Set Use Employee TimeSheet
		if ( !LocalCacheData.last_timesheet_selected_timezone ) {
			this.timezone_btn.setValue( false );
		} else {
			this.timezone_btn.setValue( LocalCacheData.last_timesheet_selected_timezone );
		}

		//Error: TypeError: Cannot read property 'show_wage' of null
		//just need to check that the variable exists before checking properties for the case of the LocalCacheData being empty
		if ( Global.isSet( LocalCacheData.getAllURLArgs() ) && LocalCacheData.getAllURLArgs().timezone ) {
			this.timezone_btn.setValue( LocalCacheData.getAllURLArgs().timezone === '1' ? true : false );
		}

		// Set punch mode
		if ( !this.show_punch_mode_ui ) {
			if ( !PermissionManager.validate( this.permission_id, 'punch_timesheet' ) && !PermissionManager.validate( this.permission_id, 'manual_timesheet' ) ) {
				this.toggle_button.setValue( 'punch' );
			} else {
				if ( PermissionManager.validate( this.permission_id, 'punch_timesheet' ) ) {
					this.toggle_button.setValue( 'punch' );
				}
				if ( Global.getProductEdition() >= 15 && PermissionManager.validate( this.permission_id, 'manual_timesheet' ) ) {
					this.toggle_button.setValue( 'manual' );
				}
			}
		} else {
			if ( !LocalCacheData.last_timesheet_selected_punch_mode ) {
				this.toggle_button.setValue( 'punch' );

			} else {
				this.toggle_button.setValue( LocalCacheData.last_timesheet_selected_punch_mode );
			}
			if ( LocalCacheData.getAllURLArgs().mode ) {
				// Fix wrong value from url
				this.toggle_button.setValue( LocalCacheData.getAllURLArgs().mode === 'manual' ? 'manual' : 'punch' );
			}
		}

		if ( Global.UNIT_TEST_MODE == true ) {
			LocalCacheData.last_timesheet_selected_date = '15-Feb-18';
		}
		//replace select layout filter_data to filter set in onNavigation function when goto view from navigation context group
		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.employee_nav.setValue( LocalCacheData.default_filter_for_next_open_view.user_id );
			this.setDatePickerValue( LocalCacheData.default_filter_for_next_open_view.base_date );
		} else {
			if ( LocalCacheData.getAllURLArgs().user_id ) {
				this.employee_nav.setValue( LocalCacheData.getAllURLArgs().user_id );
			} else if ( LocalCacheData.last_timesheet_selected_user ) {
				this.employee_nav.setValue( LocalCacheData.last_timesheet_selected_user );
			} else {
				//Default set current login user as select Employee
				this.employee_nav.setValue( loginUser );
			}

			if ( !LocalCacheData.last_timesheet_selected_date ) { //Saved current select date in cache. so still select last select date when go to other view and back
				if ( LocalCacheData.current_select_date && Global.strToDate( LocalCacheData.current_select_date, 'YYYY-MM-DD' ) ) { //Select date get from URL.
					this.setDatePickerValue( Global.strToDate( LocalCacheData.current_select_date, 'YYYY-MM-DD' ).format() );
					LocalCacheData.current_select_date = '';
				} else {
					var date = new Date();
					var format = Global.getLoginUserDateFormat();
					var dateStr = date.format( format );
					this.setDatePickerValue( dateStr );
				}

			} else {
				this.setDatePickerValue( LocalCacheData.last_timesheet_selected_date );
			}
		}

		//Issue #3268 - Race condition where previous search layout will not exist in UI when expected as API for custom field data has not returned yet.
		//Timesheet overrides initData() from BaseViewController that waits on the custom field promise to resolve before continuing.
		TTPromise.wait( 'BaseViewController', 'getCustomFields', function() {
			$this.initLayout();
		} );

		this.setMoveOrDropMode( this.select_drag_menu_id ? this.select_drag_menu_id : 'move' );
	}

	setDatePickerValue( val ) {
		this.start_date_picker.setValue( val );

		var default_date = this.start_date_picker.getDefaultFormatValue();

		var user_id = this.getSelectEmployee();

		if ( user_id &&
			!this.edit_view &&
			//Removing date from the generated URLs to avoid bookmarking to stale dates by users.
			//(window.location.href.indexOf( 'date=' + default_date ) === -1 || window.location.href.indexOf( 'user_id=' + this.getSelectEmployee() === -1 )) ) {
			( window.location.href.indexOf( 'user_id=' + user_id ) === -1 ) ) {

			//var location = Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + default_date + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue();
			var location = Global.getBaseURL() + '#!m=' + this.viewId + '&user_id=' + user_id + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue();

			if ( LocalCacheData.getAllURLArgs() ) {
				for ( var key in LocalCacheData.getAllURLArgs() ) {
					//if ( key === 'm' || key === 'date' || key === 'user_id' || key === 'show_wage' || key === 'mode' ) {
					if ( key === 'm' || key === 'user_id' || key === 'show_wage' || key === 'timezone' || key === 'mode' ) {
						continue;
					}
					location = location + '&' + key + '=' + LocalCacheData.getAllURLArgs()[key];
				}
			}

			Global.setURLToBrowser( location );

		}

		LocalCacheData.last_timesheet_selected_date = val;
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Punch Branch' ),
				in_column: 1,
				field: 'branch_id',
				layout_name: 'global_branch',
				api_class: TTAPI.APIBranch,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Punch Department' ),
				field: 'department_id',
				in_column: 1,
				layout_name: 'global_department',
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Job' ),
				in_column: 2,
				field: 'job_id',
				layout_name: 'global_job',
				api_class: ( Global.getProductEdition() >= 20 ) ? TTAPI.APIJob : null,
				multiple: true,
				basic_search: ( this.show_job_item_ui && ( Global.getProductEdition() >= 20 ) ),
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Task' ),
				in_column: 2,
				field: 'job_item_id',
				layout_name: 'global_job_item',
				api_class: ( Global.getProductEdition() >= 20 ) ? TTAPI.APIJobItem : null,
				multiple: true,
				basic_search: ( this.show_job_item_ui && ( Global.getProductEdition() >= 20 ) ),
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Tags' ),
				in_column: 2,
				field: 'punch_tag_id',
				layout_name: 'global_punch_tag',
				api_class: ( Global.getProductEdition() >= 20 ) ? TTAPI.APIPunchTag : null,
				multiple: true,
				basic_search: ( this.show_punch_tag_ui && ( Global.getProductEdition() >= 20 ) ),
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	getSelectEmployee( full_item ) {
		var user = false;
		if ( this.show_navigation_box && this.employee_nav && typeof this.employee_nav.getValue == 'function' ) {
			user = this.employee_nav.getValue( true ); //Always try to get the object (not the id), however in some cases like recalculating timesheets it still seems to return the ID instead of the object.
		}

		//Convert object if it isn't already, to make logic lower down easier.
		if ( Global.isObject( user ) == false ) {
			user = { id: user };
		}

		if ( Global.isObject( user ) == false || ( Global.isObject( user ) == true && ( user.hasOwnProperty( 'id' ) == false || TTUUID.isUUID( user.id ) == false ) ) ) {
			user = LocalCacheData.getLoginUser();

			if ( Global.isObject( user ) == false || ( Global.isObject( user ) == true && ( user.hasOwnProperty( 'id' ) == false || TTUUID.isUUID( user.id ) == false ) ) ) {
				//currently logged in user object is corrupt.
				MenuManager.doLogout();
				return false;
			}
		}

		if ( full_item != true && ( Global.isObject( user ) == true && user.hasOwnProperty( 'id' ) == true && TTUUID.isUUID( user.id ) == true ) ) {
			user = user.id;
		}

		return user;
	}

	getSelectDate() {
		if ( this.start_date_picker ) {
			var retval = this.start_date_picker.getValue();

			if ( retval == 'Invalid date' ) {
				retval = new Date();
			}

			return retval;
		}

		return null;
	}

	onDeleteResult( result ) {
		var $this = this;
		$this.timesheet_grid.grid.find( 'td.ui-state-highlight' ).removeClass( 'ui-state-highlight' );
		ProgressBar.closeOverlay();
		if ( result && result.isValid() ) {
			if ( $this.edit_view ) {
				if ( LocalCacheData.current_doing_context_action === 'delete' ) {
					$this.removeEditView();
				} else if ( $this.edit_view && LocalCacheData.current_doing_context_action === 'delete_and_next' ) {
					$this.onRightArrowClick();
				}
			} else {
				this.setCurrentEditViewState( '' );
			}
		} else {
			$this.revertEditViewState();
			TAlertManager.showErrorAlert( result );
		}

		// refresh and rebuild search grid, as well as default menu
		$this.first_build = true;
		$this.search();
		$this.setDefaultMenu(); //Default menu needs to be set as we need to deactivate icons that are valid for the predeletion selection
	}

	getDeleteSelectedRecordId() {
		var retval = [];
		if ( this.edit_view ) {
			retval.push( this.current_edit_record.id );
		} else {
			for ( var i in this.select_punches_array ) {
				var item = this.select_punches_array[i];
				retval.push( item.id );
			}
		}
		return retval;
	}

	reSetURL() {
		//var args = '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue();
		var args = '#!m=' + this.viewId + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&timezone=' + this.timezone_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue();
		Global.setURLToBrowser( Global.getBaseURL() + args );
		LocalCacheData.setAllURLArgs( Global.buildArgDic( args.split( '&' ) ) );
	}

	onSaveAndContinue( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var current_api = this.getCurrentAPI();

		if ( this.is_mass_adding && this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length === 1 ) {
			this.current_edit_record.punch_date = this.current_edit_record.punch_dates[0];
		}

		current_api.setIsIdempotent( true ); //Force to idempotent API call to avoid duplicate network requests from causing errors displayed to the user.
		current_api['set' + current_api.key_name]( this.current_edit_record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result && result.isValid() ) {
					var result_data = result.getResult();
					var refresh_id;
					if ( result_data === true ) {
						refresh_id = $this.current_edit_record.id;

					} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
						refresh_id = result_data;
					}
					$this.search();
					$this.onEditClick( refresh_id, $this.getPunchPermissionType() );

					//#2295 - Re-initialize previous_absence_policy_id to ensure that previously saved values are passed correctly into the estimation of projected available balance.
					$this.previous_absence_policy_id = false;

					$this.onSaveAndContinueDone( result );
				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );

				}

			}
		} );
	}

	getSelectedRecordId( record ) {
		var retval = false;

		if ( Global.isSet( record ) ) {
			if ( Global.isObject( record ) && record.id ) {
				retval = record.id;
			} else if ( Global.isString( record ) && TTUUID.isUUID( record ) ) {
				retval = record;
			}
		} else {
			if ( this.select_punches_array.length > 0 ) {
				retval = this.select_punches_array[0].id;
			} else {
				retval = null;
			}
		}

		return retval;
	}

	getViewSelectedRecordId( record ) {
		return this.getSelectedRecordId( record );
	}

	doViewAPICall( filter, api_args ) {
		var current_api = this.getCurrentAPI();
		var callback = { onResult: this.handleViewAPICallbackResult.bind( this ) };
		if ( api_args ) {
			// If api_args specified, use api_args.filter, and ignore function filter parameter.
			api_args.push( callback );
			return current_api['get' + current_api.key_name].apply( current_api, api_args );
		} else {
			return current_api['get' + current_api.key_name]( filter, callback );
		}
	}

	onViewClick( record, type ) {
		var tmp_record_id = this.getViewSelectedRecordId( record );
		if ( Global.isFalseOrNull( tmp_record_id ) || tmp_record_id == TTUUID.not_exist_id ) {
			TAlertManager.showAlert( $.i18n._( 'This punch is still being processed, please try again later.' )  );
			ProgressBar.closeOverlay();
			return;
		}

		if ( type ) {
			if ( type === 'absence' ) {
				this.absence_model = true;
			} else {
				this.absence_model = false;
			}
		}
		super.onViewClick( record );
	}

	buildOtherFieldUI( field, label ) {

		if ( !this.edit_view_tab ) {
			return;
		}

		var form_item_input;
		var $this = this;
		var tab_punch = this.edit_view_tab.find( '#tab_punch' );
		var tab_punch_column1 = tab_punch.find( '.first-column' );

		if ( $this.edit_view_ui_dic[field] ) {
			form_item_input = $this.edit_view_ui_dic[field];
			form_item_input.setValue( $this.current_edit_record[field] );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: field } );
			var input_div = $this.addEditFieldToColumn( label, form_item_input, tab_punch_column1 );

			input_div.insertBefore( this.edit_view_form_item_dic['note'] );

			form_item_input.setValue( $this.current_edit_record[field] );
		}
		form_item_input.css( 'opacity', 1 );
		form_item_input.css( 'minWidth', 300 );

		if ( $this.is_viewing ) {
			form_item_input.setEnabled( false );
		} else {
			form_item_input.setEnabled( true );
		}
	}

	onMassEditClick() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'mass_edit';
		this.is_mass_editing = true;
		$this.openEditView();
		this.is_mass_adding = false;
		this.is_viewing = false;

		var current_api = this.getCurrentAPI();

		var filter = {};
		this.mass_edit_record_ids = [];

		$.each( this.select_punches_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value.id );
		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		current_api['getCommon' + current_api.key_name + 'Data']( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				current_api['getOptions']( 'unique_columns', {
					onResult: function( result ) {
						$this.unique_columns = result.getResult();
						current_api['getOptions']( 'linked_columns', {
							onResult: function( result1 ) {

								$this.linked_columns = result1.getResult();

								if ( $this.sub_view_mode && $this.parent_key ) {
									result_data[$this.parent_key] = $this.parent_value;
								}

								if ( !Global.isSet( result_data.time_stamp ) ) {
									result_data.time_stamp = false;
								}

								$this.current_edit_record = result_data;
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );
	}

	setSubLogViewFilter() {
		if ( !this.sub_log_view_controller ) {
			return false;
		}

		this.sub_log_view_controller.getSubViewFilter = function( filter ) {

			if ( !this.parent_view_controller.absence_model ) {
				filter['table_name_object_id'] = {
					'punch': [this.parent_edit_record.id],
					'punch_control': [this.parent_edit_record.punch_control_id]
				};
			} else {
				filter['table_name'] = 'user_date_total';
				filter['object_id'] = this.parent_edit_record.id;

			}

			return filter;
		};

		return true;
	}

	getEditSelectedRecordId( record_id ) {
		return this.getSelectedRecordId( record_id );
	}

	doEditAPICall( filter, api_args ) {
		var current_api = this.getCurrentAPI();
		var callback = { onResult: this.handleEditAPICallbackResult.bind( this ) };
		if ( api_args ) {
			// If api_args specified, use api_args.filter, and ignore function filter parameter.
			api_args.push( callback );
			return current_api['get' + current_api.key_name].apply( current_api, api_args );
		} else {
			return current_api['get' + current_api.key_name]( filter, callback );
		}
	}

	onEditClick( record_id, type ) {
		var tmp_record_id = this.getViewSelectedRecordId( record_id );
		if ( Global.isFalseOrNull( tmp_record_id ) || tmp_record_id == TTUUID.not_exist_id ) {
			TAlertManager.showAlert( $.i18n._( 'This punch is still being processed, please try again later.' )  );
			ProgressBar.closeOverlay();
			return;
		}

		if ( type ) {
			if ( type === 'absence' ) {
				this.absence_model = true;
			} else {
				this.absence_model = false;
			}
		}
		super.onEditClick( record_id );
	}

	setURL() {
		var t = this.getPunchPermissionType();
		var a = '';
		switch ( LocalCacheData.current_doing_context_action ) {
			case 'new':
			case 'edit':
			case 'view':
				a = LocalCacheData.current_doing_context_action;
				break;
			case 'copy_as_new':
				a = 'new';
				break;
		}

		var tab_name = this.edit_view_tab ? this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.getEditViewTabIndex() ).text() : '';
		tab_name = tab_name.replace( /\/|\s+/g, '' );

		//Error: Unable to get property 'id' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=8.0.0-20141117-132941 line 2234
		if ( this.current_edit_record && this.current_edit_record.id ) {

			if ( a ) {
				//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&a=' + a + '&id=' + this.current_edit_record.id + '&t=' + t +
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&a=' + a + '&id=' + this.current_edit_record.id + '&t=' + t + '&tab=' + tab_name );
			} else {
				//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&id=' + this.current_edit_record.id + '&t=' + t );
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&id=' + this.current_edit_record.id + '&t=' + t );
			}

			Global.trackView();

		} else {

			if ( a ) {
				//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&a=' + a + '&t=' + t +
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&a=' + a + '&t=' + t + '&tab=' + tab_name );
			} else {
				//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&t=' + t );
				Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&user_id=' + this.getSelectEmployee() + '&show_wage=' + this.wage_btn.getValue( true ) + '&mode=' + this.toggle_button.getValue() + '&t=' + t );
			}

		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'add': //This is caught by onContextMenuClick(), so we have to use a different id: add_punch instead.
			case 'add_punch':
				this.absence_model = false;
				this.onAddClick();
				break;
			case 'add_absence':
				this.absence_model = true;
				this.onAddClick();
				break;
			case 'move':
			case 'drag_copy':
				this.setMoveOrDropMode( id );
				break;
			case 'in_out':
				MenuManager.openSelectView( 'InOut' );
				break;
			case 'schedule':
			case 'pay_stub':
			case 'edit_employee':
			case 'edit_pay_period':
				this.onNavigationClick( id );
				break;
			case 're_calculate_timesheet':
			case 'generate_pay_stub':
				this.onWizardClick( id );
				break;
			case 'map':
				this.onMapClick( id );
				break;
			case 'accumulated_time':
				this.onAccumulatedTimeClick( id );
				break;
			case 'AddRequest':
				// Preventing TypeError: Cannot read property 'date' of undefined
				if ( this.select_cells_Array.length > 0 ) {
					this.addRequestFromTimesheetCell( id );
				}
				break;
			case 'print_summary':
			case 'print_detailed':
				this.onReportMenuClick( id );
				break;
		}
	}

	addRequestFromTimesheetCell( id ) {
		if ( Global.getProductEdition() <= 10 ) {
			TAlertManager.showAlert( Global.getUpgradeMessage() );
			return false;
		}

		var current_column_field = Global.strToDate( this.select_cells_Array[0].date ? this.select_cells_Array[0].date : this.start_date_picker.getValue() ).format( this.full_format );

		if ( this.select_cells_Array[0].punch ) {
			var punch_control_id = this.select_cells_Array[0].punch.punch_control_id;
			var current_punch_id = this.select_cells_Array[0].punch.id;
			var current_punch_status_id = this.select_cells_Array[0].punch.status_id;
			var type_id = this.select_cells_Array[0].punch.type_id;
			var user_id = this.select_cells_Array[0].punch.user_id;
		} else {
			var user_id = this.getSelectEmployee();
			var punch_control_id = null;
			var current_punch_id = null;
			var current_punch_status_id = 10;
			var type_id = 10;
		}

		var previous_punch_id = null;
		if ( !current_punch_id ) {
			//row_id is numeric here.
			if ( this.select_cells_Array[0].row_id > 1 && this.timesheet_data_source[this.select_cells_Array[0].row_id - 2] && this.timesheet_data_source[this.select_cells_Array[0].row_id - 2][current_column_field + '_data'] ) {
				previous_punch_id = this.timesheet_data_source[this.select_cells_Array[0].row_id - 2][current_column_field + '_data'].id;
				type_id = this.timesheet_data_source[this.select_cells_Array[0].row_id - 2][current_column_field + '_data'].type_id;
				var tmp_status_id = this.timesheet_data_source[this.select_cells_Array[0].row_id - 2][current_column_field + '_data'].status_id;

				// Issue #2895 - Request text would show a break / lunch out even though the last punch was an in.
				if ( current_punch_status_id == 10 && tmp_status_id == 10 && type_id != 10 ) { //Status 10=In,20=Out  -- Type: 10=Normal, 20=Lunch, 30=Break
					// This has to be a normal out punch as the last punch was an in.
					type_id = 10;
				}
			}

			//blank and has no previous punch so we need to infer status_id from the selected row's status
			if ( this.timesheet_data_source[this.select_cells_Array[0].row_id - 1] ) {
				current_punch_status_id = this.timesheet_data_source[this.select_cells_Array[0].row_id - 1].status_id;
			}
		}

		var date = this.select_cells_Array[0].time_stamp_num / 1000;
		var $this = this;
		this.api_punch.getRequestDefaultData(
			user_id,
			date,
			punch_control_id,
			previous_punch_id,
			current_punch_status_id,
			type_id,
			current_punch_id, {
				onResult: function( result ) {
					var request = result.getResult();
					IndexViewController.openEditView( $this, 'Request', request, null, 'openAddView' );
				}
			}
		);
	}

	getPayPeriod( date ) {
		var current_date = this.getSelectDate();

		//if pass a date in, use the date
		if ( date ) {
			current_date = date;
		}

		if ( this.pay_period_map && this.pay_period_map[current_date] && this.pay_period_map[current_date] != TTUUID.zero_id && TTUUID.isUUID( this.pay_period_map[current_date] ) == true ) {
			return this.pay_period_map[current_date];
		} else {
			return null;
		}
	}

	onNavigationClick( iconName ) {

		if ( !this.checkTimesheetData() ) {
			return;
		}

		var post_data;

		switch ( iconName ) {
			case 'in_out':
				IndexViewController.openEditView( LocalCacheData.current_open_primary_controller, 'InOut' );
				break;
			case 'edit_employee':
				IndexViewController.openEditView( this, 'Employee', this.getSelectEmployee() );
				break;
			case 'edit_pay_period':
				var pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					IndexViewController.openEditView( this, 'PayPeriods', pay_period_id );
				}
				break;
			case 'schedule':
				var filter = { filter_data: {} };
				var include_users = { value: [this.getSelectEmployee()] };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = this.getSelectDate();

				Global.addViewTab( this.viewId, $.i18n._( 'TimeSheet' ), window.location.href );
				IndexViewController.goToView( 'Schedule', filter );

				break;
			case 'pay_stub':
				filter = { filter_data: {} };
				var users = { value: [this.getSelectEmployee()] };
				filter.filter_data.user_id = users;

				Global.addViewTab( this.viewId, $.i18n._( 'TimeSheet' ), window.location.href );
				IndexViewController.goToView( 'PayStub', filter );

				break;
			case 'print_summary':

				filter = { time_period: {} };
				filter.time_period.time_period = 'custom_pay_period';
				filter.time_period.pay_period_id = this.timesheet_verify_data.pay_period_id;
				filter.include_user_id = [this.getSelectEmployee()];
				post_data = { 0: filter, 1: 'pdf_timesheet' };
				this.doFormIFrameCall( post_data );
				break;
			case 'print_detailed':
				filter = { time_period: {} };
				filter.time_period.time_period = 'custom_pay_period';
				filter.time_period.pay_period_id = this.timesheet_verify_data.pay_period_id;
				filter.include_user_id = [this.getSelectEmployee()];
				post_data = { 0: filter, 1: 'pdf_timesheet_detail' };
				this.doFormIFrameCall( post_data );
				break;
		}
	}

	doFormIFrameCall( postData ) {
		Global.APIFileDownload( 'APITimesheetDetailReport', 'getTimesheetDetailReport', postData );
	}

	onAccumulatedTimeClick() {
		if ( PermissionManager.checkTopLevelPermission( 'AccumulatedTime' ) ) {
			var select_date = Global.strToDate( this.getSelectDate() ).format( 'YYYY-MM-DD' );
			IndexViewController.openEditView( this, 'UserDateTotalParent', select_date );
		}
	}

	onMapClick() {
		// only trigger map load in specific product editions.
		if ( ( Global.getProductEdition() >= 15 ) ) {

			// TODO: this is repeated below, perhaps in future now that getFilterColumnsFromDisplayColumns() is commented out, this can be consolidated?
			var data = {
				filter_columns: {
					id: true,
					latitude: true,
					longitude: true,
					punch_date: true,
					punch_time: true,
					position_accuracy: true,
					user_id: true
				}
			};

			var punches = [];
			var map_options = {};

			if ( this.edit_view ) {
				punches.push( this.current_edit_record );
				if ( !this.is_viewing ) {
					// make sure that when view only (so no save) marker is not draggable, and thus no new marker can be added either.
					map_options.single_marker_draggable = true;
				}
			} else if ( this.select_punches_array && this.select_punches_array.length > 0 ) {
				var ids = [];
				this.select_punches_array.map( function( punch ) {
					if ( punch.id ) {
						ids.push( punch.id );
					}
				} );

				data.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
				if ( data.filter_data && ids.length > 0 ) {
					data.filter_data.id = ids;
				}
				// data.filter_columns = this.getFilterColumnsFromDisplayColumns()
				data.filter_columns.first_name = true;
				data.filter_columns.last_name = true;
				data.filter_columns.user_id = true;
				data.filter_columns.date_stamp = true; // #2735 - grouping punches by date_stamp instead of punch_date, to allow cross date punch controls to plot distances.
				data.filter_columns.punch_date = true;
				data.filter_columns.punch_time = true;
				data.filter_columns.time_stamp = true;
				data.filter_columns.status = true;
				data.filter_columns.punch_control_id = true;
				data.filter_columns.branch = true;
				data.filter_columns.branch_id = true;
				data.filter_columns.department = true;
				data.filter_columns.department_id = true;
				data.filter_columns.job_manual_id = true;
				data.filter_columns.job = true;
				data.filter_columns.job_id = true;
				data.filter_columns.job_item_manual_id = true;
				data.filter_columns.job_item = true; // also known as Task
				data.filter_columns.job_item_id = true;
				data.filter_columns.punch_tag = true;
				data.filter_columns.punch_tag_id = true;
				data.filter_columns.total_time = true;
				data.filter_columns.latitude = true;
				data.filter_columns.longitude = true;
				data.filter_columns.position_accuracy = true;

				punches = this.api.getPunch( data, { async: false } ).getResult();
			}

			// There is not enough detail in the 'punches' data pulled from the grid, get the full data from api, like PunchesViewController
			// Note there seems to be a multple ways to get the data e.g. select_punches_array,
			// but other view controllers use .getGridSelectIdArray() and theres also select_cells_Array.
			// TODO-future: Perhaps look at a future refactor/consolidation of all these?

			if ( Global.isArray( punches ) ) {
				import( /* webpackChunkName: "leaflet-timetrex" */ '@/framework/leaflet/leaflet-timetrex' ).then(( module )=>{
					var processed_punches_for_map = module.TTConvertMapData.processPunchesFromViewController( punches, map_options );
					IndexViewController.openEditView( this, 'Map', processed_punches_for_map );
				}).catch( Global.importErrorHandler );
			} else {
				Debug.Text( 'ERROR: Either punches is not an array, or data is empty', 'TimeSheetViewController.js', 'TimeSheetViewController', 'onMapClick', 1 );
				TAlertManager.showAlert( $.i18n._( 'Selected punches no longer exist, unable to map.' ) );
			}
		}
	}

	onWizardClick( iconName ) {

		var $this = this;
		switch ( iconName ) {
			case 're_calculate_timesheet':
				var default_data = {};
				default_data.user_id = this.getSelectEmployee();

				var pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					default_data.pay_period_id = pay_period_id;
				}
				IndexViewController.openWizard( 'ReCalculateTimeSheetWizard', default_data, function() {

					$this.onReCalTimeSheetDone();
				} );
				break;
			case 'generate_pay_stub':

				default_data = {};
				default_data.user_id = this.getSelectEmployee();

				pay_period_id = this.getPayPeriod();
				if ( pay_period_id ) {
					default_data.pay_period_id = [pay_period_id];
				} else {
					default_data.pay_period_id = [];
				}
				IndexViewController.openWizard( 'GeneratePayStubWizard', default_data, function() {
					$this.search();
				} );
				break;
		}
	}

	onReCalTimeSheetDone() {
		//Its possible the user has navigated away from the timesheet while a recalculation is in progress, if so, don't try to refresh the timesheet.
		//Also fixes: Uncaught TypeError: Failed to execute 'replaceChild' on 'Node': parameter 2 is not of type 'Node'.

		if ( MenuManager.isCurrentView( 'TimeSheet' ) ) {
			MenuManager.goToView( 'TimeSheet', true );
			//this.initData(); //Do a generic view refresh rather than just initData() as its less likely to cause problems.
		}
	}

	setMoveOrDropMode( id ) {
		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var drag_copy_icon = context_menu_array.find( icon => icon.id === 'drag_copy' );
		var move_icon = context_menu_array.find( icon => icon.id === 'move' );

		if ( drag_copy_icon === undefined || move_icon === undefined ) {
			return;
		}

		ContextMenuManager.activateMenuItem( this.determineContextMenuMountAttributes().id, drag_copy_icon.id, true );
		ContextMenuManager.activateMenuItem( this.determineContextMenuMountAttributes().id, move_icon.id, true );
		var drag_invisible = false;
		var move_invisible = false;

		if ( !this.copyPermissionValidate() ) {
			drag_invisible = true;
		}

		if ( !this.movePermissionValidate() ) {
			move_invisible = true;
		}

		if ( move_invisible && id === 'move' ) {
			ContextMenuManager.activateMenuItem( this.determineContextMenuMountAttributes().id, drag_copy_icon.id, false );
		} else {
			var icon = context_menu_array.find( icon => icon.id === id );
			ContextMenuManager.activateMenuItem( this.determineContextMenuMountAttributes().id, icon.id, false );
		}

		if ( drag_invisible && move_invisible ) {
			this.select_drag_menu_id = null;
		} else {
			this.select_drag_menu_id = id;
		}
	}

	getSelectDateArray() {

		var result = [];

		var cells_array = this.absence_model ? this.absence_select_cells_Array : this.select_cells_Array;

		var len = cells_array.length;

		var date_dic = {};
		for ( var i = 0; i < len; i++ ) {
			var item = cells_array[i];
			date_dic[item.date] = true;
		}

		for ( var key in date_dic ) {
			result.push( key );
		}

		if ( result.length === 0 ) {
			result = [this.getSelectDate()];
		}

		return result;
	}

	onAddClick( doing_save_and_new ) {
		TTPromise.add( 'TimeSheetViewController', 'addclick' );
		TTPromise.wait();
		var $this = this;
		this.setCurrentEditViewState( 'new' );
		this.is_mass_adding = true; //Is always true because we always want the user to be able to select multiple dates.
		var punch_control_id = null;
		var prev_punch_id = null;
		var related_punch = null;
		var date = this.getSelectDate();
		var status_id = 10, type_id = 10, select_cell;

		if ( !this.absence_model ) {
			if ( this.select_cells_Array.length === 1 ) {
				var select_item = this.select_cells_Array[0];
				if ( select_item.related_punch ) {
					related_punch = select_item.related_punch;
					punch_control_id = select_item.related_punch.punch_control_id;
					prev_punch_id = select_item.related_punch.id;
				} else {
					//Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/#!m=TimeSheet&date=20151006&user_id=51085&show_wage=0 line 6292
					var current_column_field = Global.strToDate( select_item.date ? select_item.date : this.start_date_picker.getValue() ).format( this.full_format );

					if ( select_item && select_item.punch && select_item.punch.punch_control_id ) {
						punch_control_id = select_item.punch.punch_control_id;
					}

					//row_id is numeric here
					if ( this.timesheet_data_source && this.timesheet_data_source[select_item.row_id - 2] ) {
						var pre_punch = this.timesheet_data_source[select_item.row_id - 2][current_column_field + '_data'];
					}

					if ( pre_punch ) {
						prev_punch_id = pre_punch.id;
					}

				}

			}
			// To use proper context menu for each punch or abseonce mode.
			this.setDefaultMenu();
			$this.openEditView();

			if ( doing_save_and_new ) {
				date = this.current_edit_record.punch_date;
				related_punch = null;
				if ( this.current_edit_record.status_id == 10 ) {
					punch_control_id = this.current_edit_record.punch_control_id;
				} else {
					punch_control_id = null;
				}

			}

			if ( this.select_cells_Array.length === 1 ) {
				select_cell = this.select_cells_Array[0];
				status_id = select_cell.status_id;

				var select_date = Global.strToDate( this.start_date_picker.getValue() );
				var new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 1 ) );
				if ( new_date.getTime() < this.start_date.getTime() ) {
					type_id = 10;
				} else {
					var row_data = this.timesheet_data_source[select_cell.row_id - 1];
					//Error: Unable to get property 'Sun-Dec-13-2015_data' of undefined or null reference in interface/html5/ line 6362
					var left_side_punch = row_data && row_data[new_date.format( this.full_format ) + '_data'];
					if ( left_side_punch ) {
						type_id = left_side_punch.type_id;
					} else {
						type_id = 10;
					}
				}
			} else {
				select_cell = this.select_cells_Array[0];
				if ( select_cell && select_cell.status_id ) {
					status_id = select_cell.status_id;
				} else {
					status_id = 10; //In
				}
			}

			this.api['get' + this.api.key_name + 'DefaultData']( this.getSelectEmployee(),
				date,
				punch_control_id,
				prev_punch_id,
				status_id,
				type_id,
				{
					onResult: function( result ) {

						var result_data = result.getResult();

						if ( !Global.isSet( result_data.time_stamp ) ) {
							result_data.time_stamp = false;
						}

						result_data.punch_date = $this.getSelectDate();

						if ( doing_save_and_new ) {
							result_data.punch_date = $this.current_edit_record.punch_date;

							if ( $this.current_edit_record.status_id == 10 ) {
								result_data.status_id = 20;
							} else {
								result_data.status_id = 10;
							}

						}

						$this.current_edit_record = result_data;
						$this.initEditView();

					}
				} );

		} else { //Absence model branch

			if ( doing_save_and_new ) {
				date = this.current_edit_record.date_stamp;
			}
			// To use proper context menu for each punch or abseonce mode.
			$this.setDefaultMenu();
			$this.openEditView();
			this.api_user_date_total['get' + this.api_user_date_total.key_name + 'DefaultData']( this.getSelectEmployee(),
				date,
				{
					onResult: function( result ) {

						var result_data = result.getResult();

						if ( !Global.isSet( result_data.time_stamp ) ) {
							result_data.time_stamp = false;
						}

						if ( Global.isSet( $this.absence_select_cells_Array[0] ) ) {
							result_data.src_object_id = $this.absence_select_cells_Array[0].src_object_id;
						}

						result_data.object_type_id = 50;

						result_data.date_stamp = $this.getSelectDate();
						$this.current_edit_record = result_data;
						$this.initEditView();

					}
				} );

		}
	}

	removeEditView() {
		super.removeEditView();
		if ( this.absence_select_cells_Array.length > 0 ) {
			this.absence_model = true;
		} else {
			this.absence_model = false;
		}
		this.setDefaultMenu();
	}

	isMassDate() {
		//Error: Unable to get property 'punch_dates' of undefined or null reference in /interface/html5/ line 6300
		if ( this.is_mass_adding && this.current_edit_record && this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length > 1 ) {
			return true;
		}

		return false;
	}

	setEditMenuSaveAndContinueIcon( context_btn ) {
		this.saveAndContinueValidate( context_btn, this.getPunchPermissionType() );

		if ( this.is_mass_editing || this.is_viewing || this.isMassDate() ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onSaveAndNewClick( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		var current_api = this.getCurrentAPI();
		LocalCacheData.current_doing_context_action = 'new';

		var record = this.current_edit_record;

		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		current_api.setIsIdempotent( true ); //Force to idempotent API call to avoid duplicate network requests from causing errors displayed to the user.
		current_api['set' + current_api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result && result.isValid() ) {
					$this.search( false );
					$this.onAddClick( true );
				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );
				}

			}
		} );
	}

	_continueDoCopyAsNew() {
		var $this = this;
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		this.is_mass_adding = true;

		if ( Global.isSet( this.edit_view ) ) {
			this.current_edit_record.id = '';

			if ( !this.absence_model ) {

				this.current_edit_record.punch_control_id = '';

				if ( this.current_edit_record.status_id == 10 ) {
					this.current_edit_record.status_id = 20;

				} else {
					this.current_edit_record.status_id = 10;
				}

				this.edit_view_ui_dic['status_id'].setValue( this.current_edit_record.status_id );
			}

			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.openEditView();
			this.initEditView();
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			ProgressBar.closeOverlay();
		}
	}

	onSaveAndCopy( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		var current_api = this.getCurrentAPI();
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		if ( this.is_mass_adding ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		this.clearNavigationData();

		current_api.setIsIdempotent( true ); //Force to idempotent API call to avoid duplicate network requests from causing errors displayed to the user.
		current_api['set' + current_api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result && result.isValid() ) {
					var result_data = result.getResult();
					$this.search( false );
					$this.onCopyAsNewClick();
				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );
				}

			}
		} );
	}

	getCurrentAPI() {
		var current_api = this.api;

		if ( this.absence_model ) {
			current_api = this.api_user_date_total;
		}

		return current_api;
	}

	generateManualTimeSheetRecordKey( item ) {
		var key = item.date_stamp + '-' + ( ( this.show_branch_ui && item.branch_id ) ? item.branch_id : TTUUID.zero_id ) +
			'-' + ( ( this.show_department_ui && item.department_id ) ? item.department_id : TTUUID.zero_id )
			+ '-' + ( ( this.show_job_ui && item.job_id && Global.getProductEdition() >= 20 ) ? item.job_id : TTUUID.zero_id ) +
			'-' + ( ( this.show_job_item_ui && item.job_item_id && Global.getProductEdition() >= 20 ) ? item.job_item_id : TTUUID.zero_id ) +
			'-' + ( ( this.show_punch_tag_ui && item.punch_tag_id && item.punch_tag_id.length > 0 && Global.getProductEdition() >= 20 ) ? item.punch_tag_id : TTUUID.zero_id ) +
			'-' + item.total_time;

		return key;
	}

	createCurrentManualGridRecordsMap( records ) {
		this.manual_grid_records_map = {};

		for ( var i = 0, m = records.length; i < m; i++ ) {
			var item = records[i];

			var key = this.generateManualTimeSheetRecordKey( item );
			if ( item.id ) {
				key = item.id + '-' + key;
			}

			this.manual_grid_records_map[key] = item.row;
			delete item.row;
		}
	}

	//Don't send records with blank total_time to the API, to better handle cases where the employees hire date is in the middle of the week and they accidently enter time on the Monday, which cause a popup validation error.
	//This allows them to get out of the scenario by simply clearing out the field or setting it back to 0.
	filterManualGridRecords( records ) {
		var retarr = Array();

		for ( var i = 0; i < records.length; i++ ) {
			var item = records[i];

			if ( item.id || ( !item.id && item.hasOwnProperty( 'total_time' ) && ( item.total_time != 0 || item.note ) ) ) { //Notes can be added to records that do not yet have hours set.
				retarr.push( item );
			}
		}

		return retarr;
	}

	onSaveClick( ignoreWarning ) {
		var $this = this;
		var record;
		// Save manual punch
		if ( this.getPunchMode() === 'manual' && !this.edit_view ) {
			var records = this.editor.getValue( true ); // reset is_changed
			if ( records.length > 0 ) {
				this.wait_auto_save && clearTimeout( this.wait_auto_save );
				this.createCurrentManualGridRecordsMap( records );
				ProgressBar.noProgressForNextCall();
				this.is_saving_manual_grid = true;
				this.setDefaultMenu();

				records = this.filterManualGridRecords( records );
				if ( records.length > 0 ) {
					this.api_user_date_total['set' + this.api_user_date_total.key_name]( records, {
						onResult: function( result ) {
							if ( !result.isValid() ) {
								TAlertManager.showErrorAlert( result );
							}

							$this.updateManualGrid();
						}
					} );

				} else {
					$this.updateManualGrid();
				}

				ProgressBar.showNanobar();
				ProgressBar.closeOverlay();
			} else {
				ProgressBar.closeOverlay();
			}

			return;
		}

		//Save normal punch
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		LocalCacheData.current_doing_context_action = 'save';
		var current_api = this.getCurrentAPI();

		if ( this.is_mass_editing ) {

			var check_fields = {};
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];

				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() ) {
						check_fields[key] = this.current_edit_record[key];
					}
				}
			}

			record = [];
			$.each( this.mass_edit_record_ids, function( index, value ) {
				var common_record = Global.clone( check_fields );
				common_record.id = value;
				record.push( common_record );

			} );
		} else {
			record = this.current_edit_record;
		}

		// Error: Uncaught TypeError: Cannot read property 'punch_dates' of null in /interface/html5/#!m=TimeSheet&date=20150323&user_id=69543 line 6448
		if ( this.is_mass_adding && this.current_edit_record ) {

			record = [];
			var dates_array = this.current_edit_record.punch_dates;

			if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
				dates_array = this.parserDatesRange( dates_array );
			}

			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( this.current_edit_record );
				delete common_record.punch_dates;
				if ( this.absence_model ) {
					common_record.date_stamp = dates_array[i];
				} else {
					common_record.punch_date = dates_array[i];
				}

				record.push( common_record );
			}
		}

		current_api.setIsIdempotent( true ); //Force to idempotent API call to avoid duplicate network requests from causing errors displayed to the user.
		current_api['set' + current_api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {

				if ( result && result.isValid() ) {
					$this.first_build = true;
					$this.search();

					$this.removeEditView();

					//#2295 - Re-initialize previous_absence_policy_id to ensure that previously saved values are passed correctly into the estimation of projected available balance.
					$this.previous_absence_policy_id = false;
				} else {
					$this.setErrorMenu();
					$this.setErrorTips( result );
				}

			}
		} );
	}

	onMapSaveClick( dataset, successCallback ) {
		this.savePunchPosition( dataset, successCallback );
	}

	savePunchPosition( moved_unsaved_markers, successCallback ) {
		if ( !moved_unsaved_markers || moved_unsaved_markers.length !== 1 ) {
			Debug.Text( 'ERROR: Invalid params/data passed to function.', 'TimesheetViewConroller.js', 'TimesheetViewConroller', 'savePunchPosition', 1 );
			return false;
		}

		if ( this.is_mass_editing == true ) {
			this.location_mass_edit_check_box.find( '.mass-edit-checkbox' )[0].checked = true;
		}

		this.setLocationValue( moved_unsaved_markers[0] );
		successCallback();
		this.is_changed = true;
		return true;
	}

	getOtherFieldTypeId() {
		var res = 15;

		if ( this.absence_model ) {
			res = 0;
		}

		return res;
	}

	/**
	 * This function is special as it handles an edit view that deals with both absences and punches.
	 * This is the only place where 2 different data layouts need to be handled by the same navigation without a change of view.
	 */
	setEditViewData() {
		var $this = this;
		var navigation_div = this.edit_view.find( '.navigation-div' );
		var navigation_widget_div = navigation_div.find( '.navigation-widget-div' );

		this.is_changed = false;

		if ( Global.isSet( this.current_edit_record.id ) && this.current_edit_record.id ) {
			//fixing both #2171 and #2227
			//preventing unclickable navigation and "cannot find property or function has of undefined."
			navigation_div.css( 'display', 'block' );
			this.navigation = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			//Set Navigation Awesomebox
			if ( this.absence_model ) {
				this.navigation.AComboBox( {
					id: this.script_name + '_navigation',
					layout_name: 'global_absence'
				} );

				if ( this.absence_original_source ) {
					this.navigation.setSourceData( this.absence_original_source );
					this.navigation.is_punch_nav = false;
				}
			} else {
				this.navigation.AComboBox( {
					id: this.script_name + '_navigation',
					layout_name: 'global_timesheet'
				} );

				if ( this.full_timesheet_data ) {
					this.navigation.setSourceData( this.full_timesheet_data.punch_data );
					this.navigation.is_punch_nav = true;
				}
			}

			this.navigation.setValue( this.current_edit_record );

			navigation_widget_div.html( this.navigation );
			// #2122 - Fixes navigation errors including: "Cannot read property 'current_page' of null" & "Cannot read property 'has' of null"
			// Prevents user clicking on drop-down to navigate to the first record then immediately clicking the left arrow which triggers the errors.
			this.setNavigation();

		} else {
			navigation_div.css( 'display', 'none' );
		}

		for ( var key in this.edit_view_ui_dic ) {

			//Set all UI field to current edit record, we need validate all UI fielld when save and validate
			if ( !Global.isSet( $this.current_edit_record[key] ) && !this.is_mass_editing ) {
				$this.current_edit_record[key] = false;
			}
		}

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {
				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget.setMassEditMode ) ) {
					widget.setMassEditMode( true );
				}

			}

			$.each( this.unique_columns, function( index, value ) {

				if ( Global.isSet( $this.edit_view_ui_dic[value] ) && Global.isSet( $this.edit_view_ui_dic[value].setEnabled ) ) {
					$this.edit_view_ui_dic[value].setEnabled( false );
				}

			} );

		}

		this.setNavigationArrowsEnabled();

		// Create this function alone because of the column value of view is different from each other, some columns need to be handle specially. and easily to rewrite this function in sub-class.
		this.setCurrentEditRecordData();

		//Init *Please save this record before modifying any related data* box
		this.edit_view.find( '.save-and-continue-div' ).SaveAndContinueBox( { related_view_controller: this } );
		this.edit_view.find( '.save-and-continue-div' ).css( 'display', 'none' );

		this.initTabData();
		this.switchToProperTab();
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets
		var $this = this;

		var tab_0_label = this.edit_view.find( 'a[ref=tab_punch]' );
		if ( tab_0_label ) {
			if ( this.absence_model ) {
				tab_0_label.text( $.i18n._( 'Absence' ) );
			} else {
				tab_0_label.text( $.i18n._( 'Punch' ) );
			}
		}

		//This needs to be done here or the user id gets stuck and subsequent punches for subordinates go to the admin's timesheet.
		this.current_edit_record.user_id = this.getSelectEmployee();

		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			var args;
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'punch_dates':
						var date_array;
						if ( !this.current_edit_record.punch_dates ) {
							date_array = this.getSelectDateArray();
							this.current_edit_record.punch_dates = date_array;
						} else {
							date_array = this.current_edit_record.punch_dates;
						}
						widget.setValue( date_array );
						break;
					case 'first_last_name':
						var select_employee = this.getSelectEmployee( true ); //Get full item
						//Error: Uncaught TypeError: Cannot read property 'first_name' of null in interface/html5/#!m=TimeSheet&date=null&user_id=null&show_wage=0&a=new&t=punch&tab=Punch line 6810
						if ( select_employee ) {
							widget.setValue( select_employee['first_name'] + ' ' + select_employee['last_name'] );
						}
						break;
					case 'total_time':
						if ( this.absence_model ) {
							var result = Global.getTimeUnit( this.current_edit_record[key] );
							widget.setValue( result );
						}
						break;
					case 'station_id':
						if ( this.current_edit_record[key] ) {
							this.setStation();
						} else {
							widget.setValue( 'N/A' );
							widget.css( 'cursor', 'default' );
						}
						break;
					case 'punch_image':
						var station_form_item = this.edit_view_form_item_dic['station_id'];
						if ( this.current_edit_record['has_image'] ) {
							this.attachElement( 'punch_image' );
							widget.setValue( ServiceCaller.getURLByObjectType( 'file_download' ) + '&object_type=punch_image&parent_id=' + this.current_edit_record.user_id + '&object_id=' + this.current_edit_record.id );

						} else {
							this.detachElement( 'punch_image' );
						}
						break;
					case 'job_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							args = {};
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
							args = {};
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
							args.filter_data = { user_id: this.current_edit_record.user_id };
							widget.setDefaultArgs( args );
						}
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'department_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							var args = {};
							args.filter_data = { user_id: this.current_edit_record.user_id, branch_id: this.current_edit_record.branch_id };
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
					case 'latitude':
					case 'longitude':
					case 'position_accuracy':
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		if ( this.absence_model ) {

			if ( this.current_edit_record.id ) {
				this.pre_total_time = this.current_edit_record.total_time;
			} else {
				this.pre_total_time = 0;
			}
		} else {
			this.pre_total_time = 0;
		}

		var actual_time_value;
		if ( this.current_edit_record.id ) {

			if ( this.current_edit_record.actual_time_stamp ) {
				actual_time_value = $.i18n._( 'Actual Time' ) + ': ' + this.current_edit_record.actual_time_stamp;
			} else {
				actual_time_value = 'N/A';
			}

		}

		this.setLocationValue( this.current_edit_record );

		this.actual_time_label.text( actual_time_value );

		this.onAvailableBalanceChange();

		this.setEditMenu(); //To make sure save & continue icon disabled correct when multi dates

		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		//can't check is_edit here because in timesheet it can be both.
		if ( this.is_viewing == true && ( this.current_edit_record.latitude == 0 || this.current_edit_record.longitude == 0 ) ) {
			$( '.widget-h-box-mapLocationWrapper' ).parents( '.edit-view-form-item-div' ).hide();
		} else {
			if ( this.show_location_ui ) {
				$( '.widget-h-box-mapLocationWrapper' ).parents( '.edit-view-form-item-div' ).show();
			}
		}
	}

	setLocationValue( location_data ) {
		if ( Global.getProductEdition() >= 15
			&& this.edit_view_ui_dic['latitude']
			&& this.edit_view_ui_dic['longitude']
			&& this.edit_view_ui_dic['position_accuracy']
		) {
			if ( location_data ) {
				this.current_edit_record.latitude = location_data.latitude;
				this.current_edit_record.longitude = location_data.longitude;
				this.current_edit_record.position_accuracy = location_data.position_accuracy;
			}
			this.edit_view_ui_dic['latitude'].setValue( this.current_edit_record.latitude );
			this.edit_view_ui_dic['longitude'].setValue( this.current_edit_record.longitude );
			this.edit_view_ui_dic['position_accuracy'].setValue( this.current_edit_record.position_accuracy ? this.current_edit_record.position_accuracy : 0 );

			if ( !this.current_edit_record.latitude && !this.is_mass_editing ) {
				this.location_wrapper.hide();
			} else {
				if ( this.show_location_ui ) {
					this.location_wrapper.show();
				}
			}
		}
	}

	onAvailableBalanceChange() {
		if ( this.current_edit_record.hasOwnProperty( 'src_object_id' ) &&
			this.current_edit_record.src_object_id && !this.is_mass_editing ) {
			this.getAvailableBalance( false );
		} else {
			this.detachElement( 'available_balance' );
		}
		this.editFieldResize();
	}

	getAvailableBalance( release_balance ) {
		if ( Global.isSet( this.current_edit_record ) == false ) {
			return;
		}

		var $this = this;
		var result_data;

		//On first run, set previous_absence_policy_id.
		if ( this.previous_absence_policy_id == false ) {
			this.previous_absence_policy_id = this.current_edit_record.src_object_id;
		}

		if ( this.absence_model ) {

			var last_date_stamp = this.current_edit_record.date_stamp;
			var total_time = this.current_edit_record.total_time;

			if ( this.is_mass_adding ) {
				last_date_stamp = this.current_edit_record.punch_dates;
				if ( $.type( last_date_stamp ) === 'string' && last_date_stamp.indexOf( ' - ' ) > 0 ) {
					last_date_stamp = this.parserDatesRange( last_date_stamp ); //Converts last_date_stamp into an array so we can get the last date below.
				}

				if ( $.type( last_date_stamp ) === 'array' && last_date_stamp.length > 0 ) {
					total_time = ( total_time * last_date_stamp.length );
					last_date_stamp = last_date_stamp[ ( last_date_stamp.length - 1 ) ];
				}
			}

			this.api_absence_policy.getProjectedAbsencePolicyBalance(
				this.current_edit_record.src_object_id,
				this.getSelectEmployee(),
				last_date_stamp,
				total_time,
				this.pre_total_time,
				this.previous_absence_policy_id,
				{
					onResult: function( result ) {
						if ( release_balance ) {
							$this.releaseBalance( result.getResult().available_balance );
						} else {
							$this.getBalanceHandler( result, last_date_stamp );

							// If the selected Absence Policy is not linked to any accrual.
							// The "Remaining Balance" button should not appear as there is no balance.
							if ( $this.is_viewing == true ) {
								$( '#release-balance-button' ).css( 'display', 'none' );
							} else if ( Global.isObject( result ) ) {
								var result_data = result.getResult();
								if ( !result_data ) {
									$( '#release-balance-button' ).css( 'display', 'none' );
								} else {
									$( '#release-balance-button' ).css( 'display', '' );
								}
							} else {
								$( '#release-balance-button' ).css( 'display', 'none' );
							}
						}
					}
				}
			);

		}
	}

	setStation() {

		var $this = this;
		var arg = { filter_data: { id: this.current_edit_record.station_id } };

		this.api_station.getStation( arg, {
			onResult: function( result ) {

				$this.station = result.getResult()[0];

				var widget = $this.edit_view_ui_dic['station_id'];
				if ( $this.station ) {
					//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in /interface/html5/#!m=TimeSheet&date=20140925 line 6017
					if ( widget ) {
						widget.setValue( $this.station.type + '-' + $this.station.description );
					}

				} else {
					if ( widget ) {
						widget.setValue( 'N/A' );
					}

					return;
				}

				if ( PermissionManager.validate( 'station', 'view' ) ||
					( PermissionManager.validate( 'station', 'view_child' ) && $this.station.is_child ) ||
					( PermissionManager.validate( 'station', 'view_own' ) && $this.station.is_owner ) ) {
					$this.show_station_ui = true;
				} else {
					$this.show_station_ui = false;
				}

				// Error: TypeError: form_item_input is undefined in interface/html5/framework/jquery.min.js?v=9.0.1-20151022-091549 line 2 > eval line 7119
				if ( $this.show_station_ui && widget ) {
					widget.css( 'cursor', 'pointer' );
				}

			}
		} );
	}

	getSelectedItems() {
		var selected_item = null;
		if ( this.edit_view ) {
			return [this.current_edit_record];
		} else {

			if ( this.select_punches_array.length > 0 ) {
				return this.select_punches_array;
			}
		}

		return [];
	}

	getSelectedItem() {

		var selected_item = null;
		if ( this.edit_view ) {
			selected_item = this.current_edit_record;
		} else {

			if ( this.select_punches_array.length > 0 ) {
				selected_item = this.select_punches_array[0];
			} else {
				selected_item = null;
			}
		}

		return Global.clone( selected_item );
	}

	addPermissionValidate( p_id ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( PermissionManager.validate( p_id, 'add' ) && this.editPermissionValidate( p_id ) ) {
			return true;
		}

		return false;
	}

	setDefaultMenu( doNotSetFocus ) {
		//TimeSheet uses a different grid than other views and needs "select_punches_array" instead of "this.getGridSelectIdArray()" for grid_selected_length
		super.setDefaultMenu( doNotSetFocus, this.select_punches_array.length );
		//Set move or drop mode after rest of menu.
		this.setMoveOrDropMode( this.select_drag_menu_id ? this.select_drag_menu_id : 'move' ); // Ensure Move/Copy selections are set when closing pop-up windows from Jump-To menu, like Add Request.
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'add_punch':
				this.setDefaultMenuAddPunchIcon( context_btn, grid_selected_length );
				if ( this.getPunchPermissionType() === 'punch' ) {
					ContextMenuManager.activateSplitButtonItem( this.determineContextMenuMountAttributes().id, context_btn.id );
				}
				break;
			case 'add_absence':
				this.setDefaultMenuAddAbsenceIcon( context_btn, grid_selected_length );
				if ( this.getPunchPermissionType() === 'absence' ) {
					ContextMenuManager.activateSplitButtonItem( this.determineContextMenuMountAttributes().id, context_btn.id );
				}
				break;
			case 'in_out':
				this.setDefaultMenuInOutIcon( context_btn, grid_selected_length );
				break;
			case 'move':
				if ( !this.movePermissionValidate( this.getPunchPermissionType() ) ) {
					ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
				}
				if ( this.getPunchMode() == 'manual' ) {
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
				} else {
					ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
				}
				break;
			case 'edit_pay_period':
				this.setDefaultMenuEditPayPeriodIcon( context_btn, grid_selected_length );
				break;
			case 'print':
				this.setDefaultMenuPrintIcon( context_btn, grid_selected_length );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
				break;
			case 'pay_stub':
				this.setDefaultMenuPayStubIcon( context_btn, grid_selected_length );
				break;
			case 're_calculate_timesheet':
				this.setDefaultMenuReCalculateTimesheet( context_btn, grid_selected_length );
				break;
			case 'generate_pay_stub':
				this.setDefaultMenuGeneratePayStubIcon( context_btn, grid_selected_length );
				break;
			case 'schedule':
				this.setDefaultMenuScheduleIcon( context_btn, grid_selected_length );
				break;
			case 'accumulated_time':
				this.setDefaultMenuAccumulatedTimeIcon( context_btn );
				break;
			case 'AddRequest':
				this.setAddRequestIcon( context_btn );
				break;
		}
	}

	setEditMenuDragCopyIcon( context_btn, grid_selected_length ) {
		if ( !this.copyPermissionValidate( this.getPunchPermissionType() ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.getPunchMode() == 'manual' ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setDefaultMenuDragCopyIcon( context_btn, grid_selected_length ) {
		if ( !this.copyPermissionValidate( this.getPunchPermissionType() ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.getPunchMode() == 'manual' ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setDefaultMenuScheduleIcon( context_btn, grid_selected_length ) {
		if ( !PermissionManager.checkTopLevelPermission( 'Schedule' ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
	}

	reCalculateEditPermissionValidate() {

		var p_id = this.permission_id;

		if ( PermissionManager.validate( p_id, 'edit' ) || this.ownerOrChildPermissionValidate( p_id, 'edit_child' ) ) {

			return true;
		}
	}

	setDefaultMenuReCalculateTimesheet( context_btn, grid_selected_length ) {

		if ( !this.reCalculateEditPermissionValidate() ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
	}

	setDefaultMenuGeneratePayStubIcon( context_btn, grid_selected_length ) {

		if ( !PermissionManager.checkTopLevelPermission( 'GeneratePayStubs' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
	}

	setDefaultMenuPayStubIcon( context_btn, grid_selected_length ) {

		if ( !PermissionManager.checkTopLevelPermission( 'PayStub' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
	}

	setDefaultMenuEditPayPeriodIcon( context_btn, grid_selected_length ) {

		if ( !this.editPermissionValidate( 'pay_period_schedule' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		var $this = this;
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		if ( $this.getPayPeriod() ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setDefaultMenuAccumulatedTimeIcon( context_btn ) {

		if ( !PermissionManager.checkTopLevelPermission( 'AccumulatedTime' ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
	}

	setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length ) {

		if ( !this.editChildPermissionValidate( 'user' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}
	}

	setDefaultMenuInOutIcon( context_btn ) {
		if ( this.addPermissionValidate( 'punch' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
            ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setEditMenuInOutIcon( context_btn ) {
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	editOwnerOrChildPermissionValidate( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
			PermissionManager.validate( p_id, 'edit' ) ||
			( selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'edit_own' ) ) ||
			( selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'edit_child' ) ) ) {

			return true;

		}

		return false;
	}

	viewOwnerOrChildPermissionValidate( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if (
			PermissionManager.validate( p_id, 'view' ) ||
			( selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'view_own' ) ) ||
			( selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'view_child' ) ) ) {

			return true;

		}

		return false;
	}

	deleteOwnerOrChildPermissionValidate( p_id, selected_item ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( !selected_item ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if (
			PermissionManager.validate( p_id, 'delete' ) ||
			( selected_item && selected_item.is_owner && PermissionManager.validate( p_id, 'delete_own' ) ) ||
			( selected_item && selected_item.is_child && PermissionManager.validate( p_id, 'delete_child' ) ) ) {

			return true;

		}

		return false;
	}

	editChildPermissionValidate( p_id, selected_item ) {
		if ( !Global.isSet( p_id ) ) {
			p_id = this.permission_id;
		}

		if ( !Global.isSet( selected_item ) ) {
			selected_item = this.getSelectEmployee( true );
		}

		if ( p_id === 'report' ) {
			return true;
		}

		if ( !PermissionManager.validate( p_id, 'enabled' ) ) {
			return false;
		}

		if ( PermissionManager.validate( p_id, 'edit' ) ||
			this.ownerOrChildPermissionValidate( p_id, 'edit_child', selected_item ) ) {

			return true;
		}

		return false;
	}

	onReportMenuClick( id ) {
		this.onNavigationClick( id );
	}

	setDefaultMenuPrintIcon( context_btn, grid_selected_length ) {

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
	}

	setEditMenuMapIcon( context_btn ) {
		super.setDefaultMenuMapIcon( context_btn );

		if ( context_btn.disabled == true ) {
			if ( this.absence_model || this.getPunchMode() == 'manual' ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		}
	}

	setEditMenuSaveAndAddIcon( context_btn ) {
		this.saveAndNewValidate( context_btn, this.getPunchPermissionType() );

		if ( this.is_viewing || this.is_mass_editing ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuMapIcon( context_btn, grid_selected_length ) {
		super.setDefaultMenuMapIcon( context_btn );

		if ( context_btn.disabled == true ) {
			if ( this.absence_model || this.getPunchMode() == 'manual' ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}
		}
	}

	setEditMenuSaveAndNextIcon( context_btn ) {
		if ( !this.editPermissionValidate( this.getPunchPermissionType() ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing || this.is_mass_adding ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuSaveAndCopyIcon( context_btn ) {
		this.saveAndNewValidate( context_btn, this.getPunchPermissionType() );

		if ( this.is_viewing || this.is_mass_editing ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuCopyAndAddIcon( context_btn ) {
		if ( !this.addPermissionValidate( this.getPunchPermissionType() ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( ( !this.current_edit_record || !this.current_edit_record.id ) || this.is_viewing || this.is_mass_adding ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}


	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'add':
			case 'add_punch':
				this.setEditMenuAddIcon( context_btn );
				if ( this.getPunchPermissionType() === 'punch' ) {
					ContextMenuManager.activateSplitButtonItem( this.determineContextMenuMountAttributes().id, context_btn.id );
				}
				break;
			case 'add_absence':
				this.setEditMenuAddIcon( context_btn );
				if ( this.getPunchPermissionType() === 'absence' ) {
					ContextMenuManager.activateSplitButtonItem( this.determineContextMenuMountAttributes().id, context_btn.id );
				}
				break;
			case 'view':
				this.setEditMenuViewIcon( context_btn );
				break;
			case 'in_out':
				this.setEditMenuInOutIcon( context_btn );
				break;
			case 'drag_copy':
				this.setEditMenuDragCopyIcon( context_btn );
				break;
			case 'move':
				if ( !this.movePermissionValidate( this.getPunchPermissionType() ) ) {
					ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
				}
				break;
			case 'accumulated_time':
				this.setDefaultMenuAccumulatedTimeIcon( context_btn );
				break;
			case 'export_excel':
				this.setDefaultMenuExportIcon( context_btn );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditEmployeeIcon( context_btn );
				break;
			case 'edit_pay_period':
				this.setDefaultMenuEditPayPeriodIcon( context_btn );
				break;
			case 're_calculate_timesheet':
				this.setDefaultMenuReCalculateTimesheet( context_btn );
				break;
			case 'generate_pay_stub':
				this.setDefaultMenuGeneratePayStubIcon( context_btn );
				break;
			case 'AddRequest':
				this.setAddRequestIcon( context_btn );
				break;
		}
	}

	enableAddRequestButton() {
		var grid_selected_id_array = this.select_cells_Array;
		var grid_selected_length = grid_selected_id_array.length;

		if ( grid_selected_length == 1 ) {
			return true;
		}
		return false;
	}

	cleanWhenUnloadView( callBack ) {
		TTVueUtils.unmountComponent( this.vue_control_bar_id );
		if ( this.manual_note_component_ids.length > 0 ) {
			this.manual_note_component_ids.forEach( ( id ) => {
				TTVueUtils.unmountComponent( id );
			} );
		}

		$( '#timesheet_view_container' ).remove();
		super.cleanWhenUnloadView( callBack );
	}

	setAddRequestIcon( context_btn, grid_selected_length ) {
		if ( Global.getProductEdition() <= 10 || !this.addPermissionValidate( 'request' ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.enableAddRequestButton() === true ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	userValueSet( val ) {
		//If the value here is null, the timesheet data will be missing therefore we want to force the value to the currently logged in user.
		//Mostly caused when developers switch instances or databases during testing.
		if ( typeof val == 'undefined' ) {
			this.employee_nav.setValue( LocalCacheData.getLoginUser() );
			Debug.Text( 'ERROR: Invalid User ID in URL. Switching to current User ID.', 'TimesheetViewConroller.js', 'TimesheetViewConroller', 'userValueSet', 10 );
		}
	}

	// Get available accrual balance for currently selected absence policy type and insert into the time field.
	releaseBalance( balance ) {

		//Balance could be positive or negative.
		//If the balance can be fully displayed in the preferred time unit, is that format.
		//Otherwise we need to release fractions of a minute, so force through "HH:MM:SS" and wrap it in quotes so it doesn't get rounded.
		if ( Global.parseTimeUnit( '"'+  Global.getTimeUnit( balance ) +'"' ) == balance ) {
			this.edit_view_ui_dic['total_time'].setValue( Global.getTimeUnit( balance ) );
		} else {
			this.edit_view_ui_dic['total_time'].setValue( '"' + Global.getTimeUnit( balance, 12 ) + '"' ); //12="HH:MM:SS"
		}

		// Trigger field onFormChange to update available balance field and other data.
		this.edit_view_ui_dic['total_time'].trigger( 'change' );
	}

	mergeJobQueueIntoTimeSheetData( timesheet_data ) {
		//Check locally if there is a queued job queue punch within the last 15 minutes (900 seconds).
		if ( LocalCacheData.getJobQueuePunchData() && LocalCacheData.getJobQueuePunchData().user_id === this.getSelectEmployee() && ( ( new Date().getTime() / 1000 ) - LocalCacheData.getJobQueuePunchData().epoch ) < ( 60 * 15 ) ) {
			//Check if punch is duplicate and alreaxy exists.
			let is_punch_exist = false;
			for( let i = 0; i < timesheet_data.punch_data.length; i++ ) {
				if( timesheet_data.punch_data[i].actual_time_stamp == LocalCacheData.getJobQueuePunchData().actual_time_stamp
					&& timesheet_data.punch_data[i].status_id == LocalCacheData.getJobQueuePunchData().status_id
					&& timesheet_data.punch_data[i].type_id == LocalCacheData.getJobQueuePunchData().type_id
				) {
					is_punch_exist = true;
				}
			}

			if ( is_punch_exist === true ) {
				//Punch already exists, remove duplicate.
				LocalCacheData.setJobQueuePunchData( null );
			} else {
				timesheet_data.punch_data.push( LocalCacheData.getJobQueuePunchData() );
			}
		}

		return timesheet_data;
	}

	getTimeSheetWidth() {
		let scroll_adjustment = Global.isVerticalScrollBarRequired( $('.timesheet-grid-div')[0] ) ? Global.getScrollbarWidth() : 0;
		return ( $( '.context-border' ).width() - scroll_adjustment );
	}
}

TimeSheetViewController.PUNCH_ROW = 1;
TimeSheetViewController.EXCEPTION_ROW = 2;
TimeSheetViewController.REQUEST_ROW = 3;
TimeSheetViewController.TOTAL_ROW = 4;
TimeSheetViewController.REGULAR_ROW = 5;
TimeSheetViewController.ABSENCE_ROW = 6;
TimeSheetViewController.ACCUMULATED_TIME_ROW = 7;
TimeSheetViewController.PREMIUM_ROW = 8;

TimeSheetViewController.html_template = `
	<div class="view time-sheet-view" id="timesheet_view_container">
		<div class="clear-both-div"></div>
		<div class="control-bar">
			<div id="vue-timesheet-control-bar"></div>
		</div>
		<div class="clear-both-div"></div>
		<div class="grid-top-border"></div>
		<div class="timesheet-grid-div">
			<div class="timesheet-punch-grid-wrapper">
				<table id="grid" class="timesheet-grid"></table>
			</div>
			<div class="inside-editor-div manual-timesheet-inside-editor-div"></div>
			<table class="accumulated-time-grid sub-grid" id="accumulated_time_grid"></table>
			<table class=" sub-grid" id="branch_grid"></table>
			<table class=" sub-grid" id="department_grid"></table>
			<table class=" sub-grid" id="job_grid"></table>
			<table class=" sub-grid" id="job_item_grid"></table>
			<table class=" sub-grid" id="premium_grid"></table>
			<table class=" sub-grid" id="absence_grid"></table>
			<table class="total_grids_div" id="total_grids_table">
				<tr style="vertical-align:top;">
					<td>
						<div class="accumulated-total-grid-title" style="display: none"><%=accumulated_time%></div>
						<table id="accumulated_total_grid"></table>
					</td>
					<td class="notes_grid_td_container">
						<table id="punch_note_grid" class="float-right"></table>
					</td>
					<td>
						<div class="verification-grid-div">
							<div class="verification-grid-title" style="display: none"><%=timesheet_verification%></div>
							<table id="verification_grid" class="float-right"></table>
							<div class="verification-action-bar">
								<span class="verify-description"></span>
								<button class="verify-button t-button" style="display: none"><%=verify%></button>
							</div>
						</div>
					</td>
				</tr>
			</table>
		</div>
		<div class="bottom-div">
			<div class="grid-bottom-border"></div>
		</div>
	</div>
`;