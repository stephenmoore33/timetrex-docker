import TTVueUtils from '@/services/TTVueUtils';
import ScheduleControlBar from '@/components/schedule/ScheduleControlBar';
import { Global } from '@/global/Global';

export class ScheduleViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#schedule_view_container', //Must set el here and can only set string, so events can work

			user_group_api: null,

			status_array: null,

			user_group_array: null,

			toggle_button: null,

			start_date_picker: null,

			start_date: null,

			end_date: null,

			full_schedule_data: null,

			schedule_columns: null,

			full_format: 'ddd-MMM-DD-YYYY',

			weekly_format: 'ddd, MMM DD',

			final_schedule_data_array: [],

			has_date_array: [],

			no_date_array: [],

			shift_key_name_array: [],

			select_cells_Array: [], //Timesheet grid

			select_all_shifts_array: [], //Timesheet grid.

			select_shifts_array: [], //Timesheet grid.

			select_recurring_shifts_array: [], //Timesheet grid.

			all_employee_btn: null,

			daily_totals_btn: null,

			weekly_totals_btn: null,

			strict_range_btn: null,

			month_date_row_array: null,

			month_date_row_tr_ids: null, // month date tr id in grid table

			month_date_row_position: null, //month date tr position in table

			month_current_header_number: 0, //0 is default column header

			day_header_width: 0,

			day_hour_width: 40,

			select_drag_menu_id: '', //Do drag move or copy

			is_override: false,

			notify_user_schedule_change: false,

			scroll_position: 0,

			selected_user_ids: [],

			prev_total_time: 0,

			prev_status_id: null,

			is_mass_adding: false,

			calculate_cell_number: 0,

			scroll_interval: null,

			scroll_unit: 0,

			holiday_data_dic: {},

			absence_policy_api: null,

			job_api: null,
			job_item_api: null,
			user_api: null,
			department_api: null,
			punch_tag_api: null,
			default_punch_tag: [],
			previous_punch_tag_selection: [],

			year_mode_original_date: null, //set this when search for yer mode with use_date_picker true, so Keep select date in ritict mode
			events: {},
			vue_control_bar_id: ''
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.permission_id = 'schedule';
		this.script_name = 'ScheduleView';
		this.viewId = 'Schedule';
		this.table_name_key = 'schedule';
		this.context_menu_name = $.i18n._( 'Schedules' );
		this.navigation_label = $.i18n._( 'Schedule' );
		this.api = TTAPI.APISchedule;
		this.user_group_api = TTAPI.APIUserGroup;
		this.absence_policy_api = TTAPI.APIAbsencePolicy;
		this.company_api = TTAPI.APICompany;
		this.user_api = TTAPI.APIUser;
		this.currency_api = TTAPI.APICurrency;
		this.event_bus = new TTEventBus({ view_id: this.viewId });


		if ( Global.getProductEdition() >= 20 ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.punch_tag_api = TTAPI.APIPunchTag;
			this.department_api = TTAPI.APIDepartment;
		}

		this.api_absence_policy = TTAPI.APIAbsencePolicy;

		this.scroll_position = 0;

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	jobUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) &&
			( Global.getProductEdition() >= 20 ) ) {
			return true;
		}
		return false;
	}

	jobItemUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( 'job_item', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	}

	punchTagUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( 'punch_tag', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	}

	branchUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	}

	departmentUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	}

	unscheduledEmployeeUIValidate() {

		if ( PermissionManager.validate( 'schedule', 'view' ) || PermissionManager.validate( 'schedule', 'view_child' ) ) {
			return true;
		}
		return false;
	}

	//Speical permission check for views, need override
	initPermission() {
		super.initPermission();

		this.show_search_tab = true;
		//See buildSearchFields() for additional permission checks.
		// if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
		// 	this.show_search_tab = true;
		// } else {
		// 	this.show_search_tab = false;
		// }

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
	}

	//only be call once when open this view
	initData() {
		var $this = this;

		//Remove tab if any
		Global.removeViewTab( this.viewId );
		ProgressBar.showOverlay();
		this.initOptions();

		//For regular employees who currently can't see the "Saved Search and Layout" tab, try to be smarter about what columns they do see by default.
		this.default_display_columns = [];
		if ( PermissionManager.validate( this.permission_id, 'edit_branch' ) ) {
			this.default_display_columns.push( 'branch' );
		}
		if ( PermissionManager.validate( this.permission_id, 'edit_department' ) ) {
			this.default_display_columns.push( 'department' );
		}
		if ( Global.getProductEdition() >= 20 ) {
			if ( PermissionManager.validate( this.permission_id, 'edit_job' ) ) {
				this.default_display_columns.push( 'job' );
			}
			if ( PermissionManager.validate( this.permission_id, 'edit_job_item' ) ) {
				this.default_display_columns.push( 'job_item' );
			}
		}

		var date = new Date();

		if ( Global.UNIT_TEST_MODE == true ) {
			LocalCacheData.last_schedule_selected_date = '15-Feb-18';
		}

		var format = Global.getLoginUserDateFormat();
		var dateStr = date.format( format );

		if ( !LocalCacheData.last_schedule_selected_date ) {
			if ( LocalCacheData.current_select_date && Global.strToDate( LocalCacheData.current_select_date, 'YYYY-MM-DD' ) ) { //Select date get from URL.
				this.setDatePickerValue( Global.strToDate( LocalCacheData.current_select_date, 'YYYY-MM-DD' ).format() );
				LocalCacheData.current_select_date = '';
			} else {
				this.setDatePickerValue( dateStr );
			}
		} else {
			this.setDatePickerValue( LocalCacheData.last_schedule_selected_date );
		}

		this.setMoveOrDropMode( this.select_drag_menu_id ? this.select_drag_menu_id : 'move' );
		//Issue #3280 - Race condition where previous search layout will not exist in UI when expected as API for custom field data has not returned yet.
		//ScheduleViewController overrides initData() from BaseViewController that waits on the custom field promise to resolve before continuing.
		TTPromise.wait( 'BaseViewController', 'getCustomFields', function() {
			$this.getAllColumns( function() {
				$this.initLayout();
			} );
		} );
	}

	setToggleButtonValue( val ) {

		if ( this.toggle_button ) {
			this.toggle_button.setValue( val );

			this.setToggleButtonUrl();
		}
	}

	setToggleButtonUrl() {

		var mode = this.getMode();
		var default_date = this.start_date_picker.getDefaultFormatValue();
		if ( !this.edit_view ) {
			Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&mode=' + mode );
		}
	}

	setDatePickerValue( val ) {

		this.start_date_picker.setValue( val );

		//this.setDateUrl();

		LocalCacheData.last_schedule_selected_date = val;
	}

	getAllColumns( callBack ) {

		var $this = this;
		this.api.getOptions( 'columns', {
			onResult: function( columns_result ) {
				var columns_result_data = columns_result.getResult();
				$this.all_columns = Global.buildColumnArray( columns_result_data );

				$this.api.getOptions( 'group_columns', {
					onResult: function( group_columns_result ) {

						var all_columns = Global.buildColumnArray( columns_result_data );
						var group_columns_result_data = group_columns_result.getResult();

						var final_all_columns = [];

						var all_len = all_columns.length;
						var group_len = group_columns_result_data.length;

						for ( var i = 0; i < group_len; i++ ) {
							var group_column_id = group_columns_result_data[i];
							for ( var j = 0; j < all_len; j++ ) {
								var column = all_columns[j];
								if ( column.value === group_column_id ) {
									final_all_columns.push( column );
									break;
								}
							}
						}

						$this.all_columns = final_all_columns;

						$this.column_selector.setUnselectedGridData( $this.all_columns );
						if ( callBack ) {
							callBack();
						}

					}
				} );

			}
		} );
	}

	initLayout() {
		var $this = this;
		$this.getAllLayouts( function() {
			$this.setSelectLayout();
			$this.search( true, false ); //Make sure we setDefaultMenu is TRUE so autoOpenEditViewIfNecessary() is called.
		} );
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status', '', this.api );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;

				if ( $this.basic_search_field_ui_dic['group_ids'] ) {
					$this.basic_search_field_ui_dic['group_ids'].setSourceData( res );
				}
				if ( $this.adv_search_field_ui_dic['group_ids'] ) {
					$this.adv_search_field_ui_dic['group_ids'].setSourceData( res );
				}
			}
		} );
	}

	getSelectDate() {
		var retval = this.start_date_picker?.getValue() ?? 'Invalid date';

		if ( retval == 'Invalid date' ) {
			retval = new Date();
		}

		return retval;
	}

	getGridSelectIdArray() {

		var result = [];
		var len = this.select_all_shifts_array.length;
		for ( var i = 0; i < len; i++ ) {
			var item = this.select_all_shifts_array[i];
			if ( item.id && item.id != TTUUID.zero_id && item.id != TTUUID.not_exist_id ) {
				result.push( item.id );
			}

		}

		return result;
	}

	getSelectedItem() {

		var selected_item = null;
		if ( this.edit_view ) {
			selected_item = this.current_edit_record;
		} else {

			if ( this.select_all_shifts_array.length > 0 ) {
				selected_item = this.select_all_shifts_array[0];
			}

		}

		if ( selected_item ) {
			return Global.clone( selected_item );
		} else {
			return null;
		}
	}

	_continueDoCopyAsNew() {
		var $this = this;
		this.setCurrentEditViewState( 'new' );
		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {

			this.current_edit_record.id = '';
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.setCurrentEditRecordData(); // Reset data to widgets to reset all widgets stat
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			ProgressBar.closeOverlay();

		} else {

			var filter = {};
			var grid_selected_id_array = this.getGridSelectIdArray();
			var grid_selected_length = grid_selected_id_array.length;

			if ( grid_selected_length > 0 ) {
				var selectedId = grid_selected_id_array[0];
			} else {
				var select_shift = Global.clone( $this.select_all_shifts_array[0] );
				select_shift = $this.resetSomeFields( select_shift );
				$this.current_edit_record = select_shift;
				$this.openEditView();
				$this.initEditView();
				return;

			}

			filter.filter_data = {};
			filter.filter_data.id = [selectedId];

			this.api['get' + this.api.key_name]( filter, {
				onResult: function( result ) {

					var result_data = result.getResult();

					//#2571 - result_data is undefined (when result_data === true there is no result[0])
					if ( !result_data || result_data === true ) {
						TAlertManager.showAlert( $.i18n._( 'Record does not exist' ) );
						$this.onCancelClick();
						return;
					}

					$this.openEditView(); // Put it here is to avoid if the selected one is not existed in data or have deleted by other pragram. in this case, the edit view should not be opend.

					result_data = result_data[0];

					result_data.id = '';

					if ( $this.sub_view_mode && $this.parent_key ) {
						result_data[$this.parent_key] = $this.parent_value;
					}

					$this.current_edit_record = result_data;
					$this.initEditView();

				}
			} );
		}
	}

	onViewClick( record, noRefreshUI ) {
		this.setCurrentEditViewState( 'view' );

		var record_id = this.getViewSelectedRecordId( record );
		if ( Global.isFalseOrNull( record_id ) ) {
			TTPromise.add( 'Schedule', 'init' );
			this.openEditView();
			var select_shift = Global.clone( this.select_all_shifts_array[0] );
			select_shift = this.resetSomeFields( select_shift );
			this.current_edit_record = select_shift;

			var $this = this;
			TTPromise.wait( 'Schedule', 'init', function() {
				$this.initEditView();
			} );
			return;
		}
		this.setCurrentSelectedRecord( record_id );

		this.openEditView();

		var filter = this.getAPIFilters();

		return this.doViewAPICall( filter );
	}

	getCommonFields() {
		var baseRecord;
		$.each( this.select_all_shifts_array, function( index, value ) {
			if ( !baseRecord ) {
				baseRecord = Global.clone( value );
				return true;
			}
			for ( var key in value ) {
				baseRecord[key] !== value[key] && delete baseRecord[key];
			}
		} );

		return baseRecord;
	}

	onMassEditClick() {
		var $this = this;
		var filter = {};
		var grid_selected_id_array = [];
		this.setCurrentEditViewState( 'mass_edit' );
		this.mass_edit_record_ids = [];

		grid_selected_id_array = this.getGridSelectIdArray();
		$this.openEditView();

		$.each( grid_selected_id_array, function( index, value ) {
			$this.mass_edit_record_ids.push( value );
		} );

		$this.selected_user_ids = [];
		$.each( this.select_all_shifts_array, function( index, value ) {
			var shift = value;
			if ( shift.hasOwnProperty( 'user_id' ) ) {
				$this.selected_user_ids.push( shift.user_id );
			}

		} );

		filter.filter_data = {};
		filter.filter_data.id = this.mass_edit_record_ids;

		if ( this.mass_edit_record_ids.length !== this.select_all_shifts_array.length ) {
			onMassEditResult( this.getCommonFields() );
			return;
		}

		this.api['getCommon' + this.api.key_name + 'Data']( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}
				onMassEditResult( result_data );

			}
		} );

		function onMassEditResult( result_data ) {
			$this.api['getOptions']( 'unique_columns', {
				onResult: function( result ) {
					$this.unique_columns = result.getResult();
					$this.api['getOptions']( 'linked_columns', {
						onResult: function( result1 ) {

							$this.linked_columns = result1.getResult();

							if ( $this.sub_view_mode && $this.parent_key ) {
								result_data[$this.parent_key] = $this.parent_value;
							}
							$this.current_edit_record = result_data;
							$this.is_mass_editing = true;
							$this.initEditView();

						}
					} );

				}
			} );
		}
	}

	onEditClick( record_id, noRefreshUI ) {
		// #2644: Note: This code fixes a bug where the edit screen is not opened on first try, but is on second. Likely due to the TTPromise and potential race conditions. See commit history for details.
		TTPromise.add( 'Schedule', 'init' );
		this.setCurrentEditViewState( 'edit' );
		this.openEditView();

		record_id = this.getEditSelectedRecordId( record_id );
		if ( Global.isFalseOrNull( record_id ) ) {
			this.openEditView();
			var select_shift = Global.clone( this.select_all_shifts_array[0] );
			select_shift = this.resetSomeFields( select_shift );
			this.current_edit_record = select_shift;
			this.current_edit_record.user_ids = [this.current_edit_record.user_id]; //#2610 - ensure that edit record is properly formed in respect to user_ids
			this.is_viewing = false;

			var $this = this;
			TTPromise.wait( 'Schedule', 'init', function() {
				$this.initEditView();
			} );
			return;
		}
		this.setCurrentSelectedRecord( record_id );

		var filter = this.getAPIFilters();

		return this.doEditAPICall( filter );
	}

	doDeleteAPICall( remove_ids, callback ) {
		if ( !callback ) {
			callback = {
				onResult: function( result ) {
					ProgressBar.closeOverlay();
					doNext.call( this, result );
					if ( result && result.isValid() ) {
						this.onDeleteDone( result );
						if ( this.edit_view ) {
							this.removeEditView();
						} else {
							this.setCurrentEditViewState( '' );
						}
					} else {
						TAlertManager.showErrorAlert( result );
					}
				}.bind( this )
			};
		}
		if ( remove_ids && remove_ids.length > 0 ) {
			return this.api['delete' + this.api.key_name]( remove_ids, callback );
		} else {
			doNext.call( this, {
				isValid: function() {
					return false;
				}
			} );
		}

		function doNext( result ) {
			//Issue #3034 - TypeError: Cannot read properties of undefined (reading 'id').
			//Because deleting recurring schedules does not go through the normal pathway, we need to reset state
			//so that Vue does not decide we on an edit view and retrieve the wrong context menu array.
			//Otherwise JavaScript exceptions may trigger when reading an empty context menu array during other actions.
			if ( this.edit_view ) {
				this.removeEditView();
			} else {
				this.setCurrentEditViewState( '' );
			}

			//Since we can't delete recurring schedules, we need to override them as absent without a absence policy instead.
			var recurring_delete_shifts_array = [];
			for ( var i = 0; i < this.select_cells_Array.length; i++ ) {
				if ( this.select_cells_Array[i].shift ) {
					this.select_cells_Array[i].shift.status_id = '20'; //Set shift to absent.
					recurring_delete_shifts_array.push( this.select_cells_Array[i].shift );
				}
			}

			if ( recurring_delete_shifts_array.length > 0 ) {
				this.api.setSchedule( recurring_delete_shifts_array, {
					onResult: function() {
						this.search();
					}.bind( this )
				} );
			} else {
				if ( result && result.isValid() ) {
					this.search();
				}
			}
		}
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
				'save_and_next',
				'delete_and_next',
				'copy'
			],
			include: [
				// Note Drag&Drop icons will be conditionally prepended here later in code for list view modes.
				{
					label: $.i18n._( 'Jump To' ),
					id: 'jump_to_header',
					menu_align: 'right',
					action_group: 'jump_to',
					action_group_header: true,
					permission_result: false, // to hide it in legacy context menu and avoid errors in legacy parsers.
					sort_order: 9030
				},
				{
					label: $.i18n._( 'TimeSheet' ),
					id: 'timesheet',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					permission_result: true,
					sort_order: 9030
				},
				{
					label: $.i18n._( 'Edit Employee' ),
					id: 'edit_employee',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					permission_result: true,
					sort_order: 9030
				},
				{
					label: $.i18n._( 'Import' ),
					id: 'import_icon',
					menu_align: 'right',
					action_group: 'import_export',
					group: 'other',
					vue_icon: 'tticon tticon-file_download_black_24dp',
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVSchedule' ),
					sort_order: 9025
				}
			]
		};

		// Edit Views

		context_menu_model.include.push( {
			label: $.i18n._( 'Print' ),
			id: 'print',
			action_group_header: true,
			action_group: 'print',
			menu_align: 'right',
			sort_order: 9020,
			} );

		context_menu_model.include.push( {
			label: $.i18n._( 'Individual Schedules' ),
			id: 'pdf_schedule',
			action_group: 'print',
			menu_align: 'right',
			sort_order: 9020
		} );

		context_menu_model.include.push(
			{
				label: $.i18n._( 'Find Available' ),
				id: 'find_available',
				vue_icon: 'tticon tticon-search_black_24dp',
				menu_align: 'right',
				permission_result: true,
				show_on_right_click: true,
				sort_order: 7000
			}
		);

		if ( PermissionManager.validate( 'request', 'add' ) ) {
			context_menu_model.include.push( {
				label: $.i18n._( 'Add Request' ),
				id: 'AddRequest',
				vue_icon: 'tticon tticon-post_add_black_24dp',
				menu_align: 'right',
				permission_result: true,
				permission: true,
				show_on_right_click: true,
				sort_order: 7010
			} );
		}

		if ( !this.is_edit && !this.is_mass_editing && !this.is_viewing ) {
			context_menu_model.include.unshift( // Add to begginning of array to preserve item order.
				{
					label: $.i18n._( 'Drag & Drop: Move' ),
					id: 'move',
					action_group: 'drag_and_drop',
					multi_select_group: 1,
					menu_align: 'right',
					group: 'drag_and_drop',
					permission_result: true,
					sort_order: 8050
				},
				{
					label: $.i18n._( 'Drag & Drop: Copy' ),
					id: 'drag_copy',
					action_group: 'drag_and_drop',
					menu_align: 'right',
					multi_select_group: 1,
					group: 'drag_and_drop',
					permission_result: true,
					sort_order: 8060
				},
				{
					label: $.i18n._( 'Drag & Drop: Swap' ),
					id: 'swap',
					action_group: 'drag_and_drop',
					menu_align: 'right',
					multi_select_group: 1,
					group: 'drag_and_drop',
					permission_result: true,
					sort_order: 8070
				},
				{
					id: 'separator_drag_and_drop_1',
					action_group: 'drag_and_drop',
					menu_align: 'right',
					separator: true,
					sort_order: 8080
				},
				{
					label: $.i18n._( 'Drag & Drop: Overwrite' ),
					id: 'override',
					action_group: 'drag_and_drop',
					multi_select_group: 2,
					menu_align: 'right',
					group: 'drag_and_drop',
					permission_result: true,
					sort_order: 8090
				},
			);

			if ( Global.getProductEdition() >= 15 ) {
				context_menu_model.include.unshift(
					{
						label: $.i18n._( 'Drag & Drop: Notify Employee' ),
						id: 'notify_user_schedule_change',
						action_group: 'drag_and_drop',
						multi_select_group: 3,
						menu_align: 'right',
						group: 'drag_and_drop',
						permission_result: true,
						sort_order: 8100
					} );
			}

		}

		if ( PermissionManager.validate( 'schedule', 'view' ) || PermissionManager.validate( 'schedule', 'view_child' ) ) {
			context_menu_model.include.push(
				{
					label: $.i18n._( 'Group - Combined' ),
					id: 'pdf_schedule_group_combined',
					action_group: 'print',
					menu_align: 'right',
					sort_order: 9020,
				},
				{
					label: $.i18n._( 'Group - Separated' ),
					id: 'pdf_schedule_group',
					action_group: 'print',
					menu_align: 'right',
					sort_order: 9020,
				},
				{
					label: $.i18n._( 'Group - Separated (Page Breaks)' ),
					id: 'pdf_schedule_group_pagebreak',
					action_group: 'print',
					menu_align: 'right',
					sort_order: 9020,
				}
			);
		}

		context_menu_model.include.push(
		);

		if ( ( PermissionManager.validate( 'punch', 'add' ) && ( PermissionManager.validate( 'punch', 'edit' ) || PermissionManager.validate( 'punch', 'edit_child' ) ) ) ) {
			context_menu_model.include.push(
				{
					label: '', //Empty label. vue_icon is displayed instead of text.
					id: 'other_header',
					action_group: 'other',
					menu_align: 'right',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
					sort_order: 9050
				},
				{
					label: $.i18n._( 'Auto Punch' ),
					id: 'AutoPunch',
					action_group: 'other',
					menu_align: 'right',
					group: 'other',
					permission_result: true,
					permission: true,
					show_on_right_click: true,
					sort_order: 9050
				} );
		}

		return context_menu_model;
	}

	parseCustomContextModelForEditViews( context_menu_model ) {

		context_menu_model = super.parseCustomContextModelForEditViews( context_menu_model );

		if ( this.determineContextMenuMountAttributes().menu_type === 'editview_contextmenu' ) {
			context_menu_model.exclude.push(
				'move',
				'drag_copy',
				'swap',
				'override',
				'notify_user_schedule_change',
				'print',
				'pdf_schedule',
				'pdf_schedule_group_combined',
				'pdf_schedule_group',
				'pdf_schedule_group_pagebreak'
			);
		}

		return context_menu_model;
	}

	onReportMenuClick( id ) {
		this.onNavigationClick( id );
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'move':
			case 'drag_copy':
			case 'swap':
				this.setMoveOrDropMode( id );
				break;
			case 'override':
				this.onOverrideClick();
				break;
			case 'notify_user_schedule_change':
				this.onNotifyUserScheduleChangeClick();
				break;
			case 'edit_employee':
			case 'timesheet':
				this.onNavigationClick( id );
				break;
			case 'find_available':
				this.onFindAvailableClick( id );
				break;
			case 'AutoPunch':
				this.addPunchesFromScheduledShifts( id );
				break;
			case 'AddRequest':
				this.addRequestFromScheduledShifts( id );
				break;
			case 'import_icon':
				this.onImportClick();
				break;
			case 'pdf_schedule':
			case 'pdf_schedule_group':
			case 'pdf_schedule_group_combined':
			case 'pdf_schedule_group_pagebreak':
				this.onReportMenuClick( id );
				break;
		}
	}

	addRequestFromScheduledShifts( id ) {
		if ( Global.getProductEdition() <= 10 ) {
			TAlertManager.showAlert( Global.getUpgradeMessage() );
			return false;
		}

		if ( shift_array && shift_array.length <= 0 ) {
			return false;
		}

		var shift_array = this.select_cells_Array;
		var first_shift = ( shift_array[0] ) ? shift_array[0] : null;
		var last_shift = ( shift_array[shift_array.length - 1] ) ? shift_array[shift_array.length - 1] : null;
		if ( !first_shift || !last_shift ) {
			return false;
		}

		var request = this.api.getScheduleDefaultData( this.select_cells_Array, { async: false } ).getResult();
		var shift_status = 10;
		var type_id = 40;

		var mon = false, tue = false, wed = false, thu = false, fri = false, sat = false, sun = false;

		for ( var w in shift_array ) {
			if ( first_shift.shift == undefined && shift_array[w].shift ) {
				//Set the archetype to the first day with a shift.
				first_shift = shift_array[w];
			}

			//Set selected days of the week.
			var d = new Date( shift_array[w].time_stamp_num );
			switch ( d.getDay() ) {
				case 0:
					sun = true;
					break;
				case 1:
					mon = true;
					break;
				case 2:
					tue = true;
					break;
				case 3:
					wed = true;
					break;
				case 4:
					thu = true;
					break;
				case 5:
					fri = true;
					break;
				case 6:
					sat = true;
					break;
			}

			// delete ( d ); #2792 In the new ES6 conversion, delete is causing errors, but technically should never have worked anyway. Potentially never did anything. Could maybe delete. Try null first.
			d = null;
		}

		request.mon = mon;
		request.tue = tue;
		request.wed = wed;
		request.thu = thu;
		request.fri = fri;
		request.sat = sat;
		request.sun = sun;

		if ( first_shift && first_shift.date ) {
			var start_date = first_shift.date;
		}

		if ( last_shift && last_shift.date ) {
			var end_date = last_shift.date;
		}

		if ( first_shift ) {
			if ( first_shift.shift && first_shift.shift.status_id == 10 && first_shift.shift.user_id && first_shift.shift.user_id != TTUUID.zero_id ) {
				shift_status = 20;
				type_id = 30;
			}
		}

		request.status_id = shift_status;
		request.type_id = type_id;
		request.user_id = LocalCacheData.getLoginUser().id;
		request.full_name = LocalCacheData.getLoginUser().full_name;
		if ( start_date ) {
			request.date_stamp = start_date;
			request.start_date = start_date;
		}

		if ( end_date ) {
			request.end_date = end_date;
		}

		if ( first_shift.start_time ) {
			request.start_time = first_shift.start_time;
		}

		if ( first_shift.end_time ) {
			request.end_time = first_shift.end_time;
		}

		if ( first_shift.branch_id ) {
			request.branch_id = first_shift.branch_id;
		}

		if ( first_shift.department_id ) {
			request.department_id = first_shift.department_id;
		}

		if ( first_shift.job_id ) {
			request.job_id = first_shift.job_id;
		}

		if ( first_shift.job_item_id ) {
			request.job_item_id = first_shift.job_item_id;
		}

		if ( first_shift.punch_tag_id ) {
			request.punch_tag_id = first_shift.punch_tag_id;
		}

		IndexViewController.openEditView( this, 'Request', request, 'openAddView' );
	}

	addPunchesFromScheduledShifts( id ) {

		if ( Global.getProductEdition() <= 10 ) {
			TAlertManager.showAlert( Global.getUpgradeMessage() );
			return false;
		}
		if ( this.select_cells_Array == undefined || this.select_cells_Array.length < 1 ) {
			TAlertManager.showAlert( 'No schedules selected. You can\'t autopunch no schedules.' );
			return false;
		}

		var shift_array = this.select_cells_Array;
		var schedules = {};
		var users = [];
		schedules.schedule = [];
		schedules.recurring = [];

		for ( var i = 0; i < shift_array.length; i++ ) {
			if ( shift_array[i].shift != undefined ) { //avoid when no user scheduled.
				if ( shift_array[i].shift.id
					&& shift_array[i].shift.id != TTUUID.zero_id
					&& shift_array[i].shift.id != TTUUID.not_exist_id
				) {
					schedules.schedule.push( shift_array[i].shift.id );
				} else if ( shift_array[i].shift.recurring_schedule_id
					&& shift_array[i].shift.recurring_schedule_id != TTUUID.not_exist_id
				) {
					schedules.recurring.push( shift_array[i].shift.recurring_schedule_id );
				}
				users.push( shift_array[i].shift.user_id );
			}
		}

		this.api.addPunchesFromScheduledShifts( schedules, {
			onResult: function( result ) {
				if ( result && result.isValid() ) {
					UserGenericStatusWindowController.open( result.getAttributeInAPIDetails( 'user_generic_status_batch_id' ), [LocalCacheData.getLoginUser().id] );
				} else {
					TAlertManager.showErrorAlert( result );
				}
			}
		} );
	}

	getSelectEmployee() {
		var shift = this.select_cells_Array[0];

		//Error: Uncaught TypeError: Cannot read property 'user_id' of undefined in /interface/html5/#!m=Schedule&date=20141117&mode=week&a=new&tab=Schedule line 1116
		if ( !shift || shift.user_id == TTUUID.zero_id ) {
			shift = { user_id: LocalCacheData.getLoginUser().id };
		} else if ( shift.user_id && shift.user_id != TTUUID.zero_id ) {
			shift = { user_id: shift.user_id };
		}

		if ( this.edit_view && this.current_edit_record ) {
			shift.user_id = this.current_edit_record.user_id;
		}

		return shift.user_id;
	}

	onFindAvailableClick() {
		if ( Global.getProductEdition() <= 10 ) {
			TAlertManager.showAlert( Global.getUpgradeMessage() );
			return;
		}

		var $this = this;
		var args = {};
		args.selected = [];
		var len = this.select_all_shifts_array.length;
		for ( var i = 0; i < len; i++ ) {
			var item = this.select_all_shifts_array[i];
			args.selected.push( item );
		}

		LocalCacheData.extra_filter_for_next_open_view = {};
		LocalCacheData.extra_filter_for_next_open_view.filter_data = args;

		IndexViewController.openWizard( 'FindAvailableWizard', null, function( employee_id, notify_users ) {
			$this.onFindAvailableClose( employee_id, args.selected, notify_users );
		} );
	}

	onFindAvailableClose( employee_id, shift_array, notify_users ) {
		var $this = this;
		var len = shift_array.length;
		for ( var i = 0; i < len; i++ ) {
			var item = shift_array[i];
			item.user_id = employee_id;
			item.replaced_id = item.id;
			item.notify_user_schedule_change = notify_users;
			delete item.id;
		}

		this.api.setSchedule( shift_array, {
			onResult: function( result ) {
				if ( !result.isValid() ) {
					TAlertManager.showErrorAlert( result );
				}
				$this.search();
			}
		} );
	}

	onImportClick() {
		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'Schedule', function() {
			$this.search();
		} );
	}

	onNavigationClick( iconName ) {

		if ( !this.checkScheduleData() ) {
			return;
		}

		var post_data;

		switch ( iconName ) {
			case 'edit_employee':
				IndexViewController.openEditView( this, 'Employee', this.getSelectEmployee() );
				break;
			case 'timesheet':
				var filter = { filter_data: {} };
				filter.user_id = this.getSelectEmployee();

				if ( this.edit_view ) {
					filter.base_date = this.current_edit_record.date_stamp;
				} else {
					filter.base_date = this.start_date_picker.getValue();
				}
				Global.addViewTab( this.viewId, $.i18n._( 'Schedules' ), window.location.href );
				IndexViewController.goToView( 'TimeSheet', filter );

				break;
			case 'pdf_schedule':
			case 'pdf_schedule_group_combined':
			case 'pdf_schedule_group':
			case 'pdf_schedule_group_pagebreak':
				filter = Global.convertLayoutFilterToAPIFilter( this.select_layout );

				if ( !filter ) {
					filter = {};
				}
				filter.time_period = {};
				filter.time_period.time_period = 'custom_date';
				filter.time_period.start_date = this.full_schedule_data.schedule_dates.start_display_date;
				filter.time_period.end_date = this.full_schedule_data.schedule_dates.end_display_date;

				if ( filter.time_period.start_date == filter.time_period.end_date ) {
					var new_end_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + 6 ) );
					filter.time_period.end_date = new_end_date.format();
				}

				post_data = { 0: filter, 1: iconName };
				this.doFormIFrameCall( post_data );
				break;

		}
	}

	doFormIFrameCall( postData ) {
		Global.APIFileDownload( 'APIScheduleSummaryReport', 'getScheduleSummaryReport', postData );
	}

	setScheduleGridDragAble() {
		var mode = this.getMode();

		switch ( mode ) {
			case ScheduleViewControllerMode.DAY:
				this.setWeekModeDragAble();
				break;
			case ScheduleViewControllerMode.WEEK:
				this.setWeekModeDragAble();
				break;
			case ScheduleViewControllerMode.MONTH:
				this.setWeekModeDragAble();
				break;
			case ScheduleViewControllerMode.YEAR:
				this.setWeekModeDragAble();
				break;
		}

		var $this = this;
		//set bottom drag to scroll area
		$( '.schedule-grid-div' ).off( 'dragover' ).on( 'dragover', function( e ) {

			var grid_div = $( '.schedule-grid-div' );
			var grid_pos = grid_div.offset().top;

			var mouse_y = e.originalEvent.clientY;
			var grid_height = grid_div.height();

			if ( mouse_y > ( grid_pos + grid_height ) ) {
				$this.scroll_unit = mouse_y - ( grid_pos + grid_height );
				if ( !$this.scroll_interval ) {
					$this.scroll_interval = setInterval( function() {
						var div = $this.grid.grid.parent().parent();
						div.scrollTop( div.scrollTop() + $this.scroll_unit );
					}, 50 );
				}
			} else if ( mouse_y < ( grid_pos + 15 ) && mouse_y > ( grid_pos - 50 ) ) {
				$this.scroll_unit = ( grid_pos + 15 ) - mouse_y;
				if ( !$this.scroll_interval ) {
					$this.scroll_interval = setInterval( function() {
						var div = $this.grid.grid.parent().parent();
						div.scrollTop( div.scrollTop() - $this.scroll_unit );
					}, 50 );
				}
			} else {
				clearInterval( $this.scroll_interval );
				$this.scroll_interval = null;
			}

		} );

		$( '.schedule-grid-div' ).off( 'dragend' ).on( 'dragend', function( e ) {

			if ( $this.scroll_interval ) {
				clearInterval( $this.scroll_interval );
				$this.scroll_interval = null;
			}

		} );

		$( '.schedule-grid-div td' ).unbind( 'dragenter' ).bind( 'dragenter', function( event ) {
			event.preventDefault();
			$( '.schedule-drag-over' ).removeClass( 'schedule-drag-over' );

			if ( $( this ).attr( 'draggable' ) || $( this ).parents( 'td' ).attr( 'draggable' ) ) {
				$( this ).addClass( 'schedule-drag-over' );
			}
		} );
	}

	setWeekModeDragAble() {
		var $this = this;
		var position = 0;

		var cells = this.grid.grid.find( '.date-column' ).parents( 'td' );

		cells.attr( 'draggable', true );

		cells.unbind( 'dragstart' ).bind( 'dragstart', function( event ) {
			var td = event.target;
			if ( $this.select_all_shifts_array.length < 1 || !$( td ).hasClass( 'ui-state-highlight' ) || !$this.select_drag_menu_id ) {
				return false;
			}

			var container = $( '<div class=\'drag-holder-div\'></div>' );

			var len = $this.select_all_shifts_array.length;

			for ( var i = 0; i < len; i++ ) {
				var shift = $this.select_all_shifts_array[i];
				var span = $( '<span class=\'drag-span\'></span>' );

				if ( shift.status_id == 20 ) {
					span.text( $this.getAbsenceCellValue( shift ) );
				} else {
					span.text( shift.start_time + ' - ' + shift.end_time );
				}

				container.append( span );
			}

			$( 'body' ).find( '.drag-holder-div' ).remove();

			$( 'body' ).append( container );

			event.originalEvent.dataTransfer.setData( 'Text', 'schedule' );//JUST ELEMENT references is ok here NO ID

			if ( event.originalEvent.dataTransfer.setDragImage ) {
				event.originalEvent.dataTransfer.setDragImage( container[0], 0, 0 );
			}

			return true;
		} );

		cells.unbind( 'drop' ).bind( 'drop', function( event ) {
			event.preventDefault();
			if ( event.stopPropagation ) {
				event.stopPropagation(); // stops the browser from redirecting.
			}

			$( '.drag-holder-div' ).remove();

			var target_empty_row = false;
			var delete_old_items = false;

			var new_shifts_array = [];
			var delete_shifts_array = [];
			var recurring_delete_shifts_array = [];

			var target_cell = event.currentTarget;

			var selected_shifts = $this.select_cellls_and_shifts_array;
			//Error: Uncaught TypeError: Cannot read property 'length' of undefined in interface/html5/#!m=Schedule&date=20151213&mode=week line 1420
			if ( !selected_shifts ) {
				return;
			}
			var first_target_row_index;
			var first_target_cell_index;

			first_target_row_index = target_cell.parentNode.rowIndex - 1;
			first_target_cell_index = target_cell.cellIndex;

			var row_index_offset = 0;
			var cell_index_offset = 0;

			var first_selected_row_index;
			var first_selected_cell_index;

			var colModel = $this.grid.grid.getGridParam( 'colModel' );

			if ( $this.select_drag_menu_id === 'move' ) {
				delete_old_items = true;
			} else {
				delete_old_items = false;
			}

			var len = selected_shifts.length;

			for ( var i = 0; i < len; i++ ) {
				var cell = selected_shifts[i];
				var shift;

				if ( i === 0 ) {
					first_selected_row_index = cell.row_id;
					first_selected_cell_index = cell.cell_index;
				} else {
					if ( !target_empty_row ) {
						row_index_offset = cell.row_id - first_selected_row_index;
					}
					cell_index_offset = cell.cell_index - first_selected_cell_index;
				}

				if ( cell.shift ) {
					shift = cell.shift;
				} else {
					var target_row_index = first_target_row_index + row_index_offset;
					var target_cell_index = first_target_cell_index + cell_index_offset;
					if ( target_cell_index > colModel.length - 1 ) {
						continue;
					}
					var target_data = $this.getDataByCellIndex( target_row_index, target_cell_index );
					var target_row = $this.schedule_source[target_row_index];

					if ( !target_row || !target_row.user_id ) {
						target_empty_row = true;
					}
					continue;
				}

				shift.branch_id = shift.branch ? shift.branch_id : '';
				shift.department_id = shift.department ? shift.department_id : '';
				shift.job_id = shift.job_id ? shift.job_id : '';
				shift.job_item_id = shift.job_item_id ? shift.job_item_id : '';
				shift.punch_tag_id = shift.punch_tag_id ? shift.punch_tag_id : '';

				target_row_index = first_target_row_index + row_index_offset;
				target_cell_index = first_target_cell_index + cell_index_offset;

				if ( target_cell_index > colModel.length - 1 ) {
					continue;
				}

				target_data = $this.getDataByCellIndex( target_row_index, target_cell_index );
				target_row = $this.schedule_source[target_row_index];

				if ( !target_row || !target_row.user_id ) {
					target_empty_row = true;
				}

				if ( target_row ) {
					if ( target_row.type === ScheduleViewControllerRowType.DATE ) {
						break;
					}

					if ( !target_data || target_empty_row ) {
						var date_stamp;

						//Error: TypeError: colModel[target_cell_index] is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141230-153210 line 2 > eval line 1443
						if ( colModel ) {
							if ( $this.getMode() === ScheduleViewControllerMode.MONTH ) {
								//Error: "TypeError: Cannot read property 'format' of null"
								// when user drags a scedule to a non-date grid element we need to quietly fail
								var related_date = $this.getCellRelatedDate( target_row_index, colModel, target_cell_index, colModel[target_cell_index].name );
								if ( related_date ) {
									date_stamp = related_date.format();
								}
							} else {
								date_stamp = Global.strToDate( colModel[target_cell_index].name, $this.full_format ).format();
							}
						}

						if ( !date_stamp || date_stamp == 'Invalid date' ) {
							continue;
						}
						target_data = {};

						if ( !target_row.user_id ) { //Only happens in month mode;
							target_data = shift;
							target_data.date_stamp = date_stamp;
							target_data.start_date_stamp = date_stamp;
						} else {
							target_data.user_id = target_row.user_id;
							target_data.branch = target_row.branch;
							target_data.branch_id = target_row.branch ? target_row.branch_id : '';
							target_data.schedule_policy_id = target_row.schedule_policy_id;
							target_data.department_id = target_row.department ? target_row.department_id : '';
							target_data.department = target_row.department;
							target_data.job_id = target_row.job_id ? target_row.job_id : '';
							target_data.job = target_row.job;
							target_data.job_item_id = target_row.job_item_id ? target_row.job_item_id : '';
							target_data.punch_tag_id = target_row.punch_tag_id ? target_row.punch_tag_id : '';
							target_data.job_item = target_row.job_item;
							target_data.date_stamp = date_stamp;
							target_data.start_date_stamp = date_stamp;
						}
					}

				} else {
					continue;
				}

				var new_shift = Global.clone( shift );

				if ( $this.select_drag_menu_id !== 'swap' ) {
					new_shift.id = '';
					new_shift.date_stamp = target_data.date_stamp;
					new_shift.start_date_stamp = target_data.start_date_stamp;
					new_shift.user_id = target_data.user_id;
					// When dragging an open shift to an empty cell in a user row with no branch column visible, the branch id value now defaults to user default branch id
					new_shift.branch_id = target_data.branch ? target_data.branch_id : TTUUID.not_exist_id;
					new_shift.department_id = target_data.department ? target_data.department_id : TTUUID.not_exist_id;
					new_shift.job_id = target_data.job_id ? target_data.job_id : TTUUID.not_exist_id;
					new_shift.job_item_id = target_data.job_item_id ? target_data.job_item_id : TTUUID.not_exist_id;
					new_shift.punch_tag_id = target_data.punch_tag_id ? target_data.punch_tag_id : TTUUID.not_exist_id;

					if ( $this.is_override ) {
						new_shift.overwrite = true;
					}

					if ( $this.notify_user_schedule_change ) {
						new_shift.notify_user_schedule_change = true;
					}

					new_shifts_array.push( new_shift );
					if ( shift.id && shift.id != TTUUID.zero_id ) {
						delete_shifts_array.push( shift.id );
					} else if ( shift.user_id != TTUUID.zero_id && shift.user_id != TTUUID.not_exist_id ) {
						//If dragging (move) a recurring shift assigned to a user and dropping on another user, switch the source shift to Absent in the process, otherwise both shifts will exist as being worked.
						//  However when dragging from a OPEN shift as the source, that isn't required, as the OPEN shift will automatically be filled.
						shift.status_id = '20';
						recurring_delete_shifts_array.push( shift );
					} else if ( shift.user_id == TTUUID.zero_id ) {
						delete_old_items = false; //Never delete old items when the source is a OPEN shift.
					}

				} else {
					var temp_selected_data = Global.clone( new_shift );
					var temp_target_data = Global.clone( target_data );

					if ( !temp_target_data.start_date ) {
						continue;
					}

					for ( var key in target_data ) {
						if ( key !== 'id' &&
							key !== 'user_id' &&
							key !== 'date_stamp' &&
							key !== 'start_date_stamp' &&
							key !== 'branch_id' &&
							key !== 'department_id' &&
							key !== 'job_id' &&
							key !== 'job_item_id' &&
							key !== 'punch_tag_id' &&
							key !== 'branch' &&
							key !== 'department' &&
							key !== 'job' &&
							key !== 'job_item' &&
							key !== 'schedule_policy_id' ) {

							target_data[key] = temp_selected_data[key];
							new_shift[key] = temp_target_data[key];
						}
					}

					// When dragging an open shift to an empty cell in a user row with no branch column visible, the branch id value now defaults to user default branch id
					target_data.branch_id = target_data.branch ? target_data.branch_id : TTUUID.not_exist_id;
					target_data.department_id = target_data.department ? target_data.department_id : TTUUID.not_exist_id;
					target_data.job_id = target_data.job_id ? target_data.job_id : TTUUID.not_exist_id;
					target_data.job_item_id = target_data.job_item_id ? target_data.job_item_id : TTUUID.not_exist_id;
					target_data.punch_tag_id = target_data.punch_tag_id ? target_data.punch_tag_id : TTUUID.not_exist_id;

					new_shifts_array.push( target_data );
					new_shifts_array.push( new_shift );
				}

			}

			if ( new_shifts_array.length > 0 ) {
				$this.api.setSchedule( new_shifts_array, {
					onResult: function( res ) {
						if ( res && res.isValid() ) {
							if ( delete_old_items ) {
								if ( delete_shifts_array.length > 0 ) {
									$this.api.deleteSchedule( delete_shifts_array, {
										onResult: function() {
											if ( recurring_delete_shifts_array.length > 0 ) {
												$this.api.setSchedule( recurring_delete_shifts_array, {
													onResult: function() {
														$this.search();
													}
												} );
											} else {
												$this.search();
											}
										}
									} );
								} else if ( recurring_delete_shifts_array.length > 0 ) {
									$this.api.setSchedule( recurring_delete_shifts_array, {
										onResult: function() {
											$this.search();
										}
									} );
								} else {
									$this.search();
								}
							} else {
								$this.search();
							}
						} else {
							TAlertManager.showErrorAlert( res );
						}
					}
				} );
			}

		} );

		cells.unbind( 'dragenter' ).bind( 'dragenter', function( event ) {
			event.preventDefault();
		} );

		cells.unbind( 'dragover' ).bind( 'dragover', function( event ) {
			event.preventDefault(); //Must prevent tihs

		} );
		cells.unbind( 'dragend' ).bind( 'dragend', function( event ) {

			$( '.drag-holder-div' ).remove();
			$( '.schedule-drag-over' ).removeClass( 'schedule-drag-over' );

		} );
	}

	resetSomeFields( item ) {
		item.branch_id = item.branch ? item.branch_id : '';
		item.department_id = item.department ? item.department_id : '';
		item.job_id = item.job ? item.job_id : '';
		item.job_item_id = item.job_item ? item.job_item_id : '';
		item.punch_tag_id = item.punch_tag_id ? item.punch_tag_id : [];

		return item;
	}

	_createParametersForAdd() {
		var result = [], user;
		if ( this.select_cells_Array.length > 0 ) {
			for ( var i = 0, n = this.select_cells_Array.length; i < n; i++ ) {
				var item = this.select_cells_Array[i];
				user = {};
				user.user_id = item.user_id;
				user.branch_id = item.branch_id;
				user.department_id = item.department_id;
				user.job_id = item.job_id;
				user.job_item_id = item.job_item_id;
				user.punch_tag_id = item.punch_tag_id;
				user.date = item.date;
				result.push( user );
			}
		}

		if ( result.length < 1 ) {
			var login_user = LocalCacheData.getLoginUser();
			user = {};
			user.user_id = login_user.id;
			user.branch_id = login_user.branch_id;
			user.department_id = login_user.department_id;
			user.job_id = login_user.job_id;
			user.job_item_id = login_user.job_item_id;
			user.punch_tag_id = login_user.punch_tag_id;
			user.date = this.getSelectDate();
			result.push( user );
		}
		return result;
	}

	onAddClick( doing_save_and_new ) {

		var $this = this;
		this.setCurrentEditViewState( 'new' );

		if ( this.select_cells_Array.length > 1 ) {
			this.is_mass_adding = true;
		}

		$this.openEditView();

		var args;
		if ( !doing_save_and_new ) {
			args = this._createParametersForAdd();
		} else {
			args = [
				{
					user_id: this.current_edit_record.user_id,
					branch_id: this.current_edit_record.branch_id,
					department_id: this.current_edit_record.department_id,
					job_id: this.current_edit_record.job_id,
					job_item_id: this.current_edit_record.job_item_id,
					punch_tag_id: this.current_edit_record.punch_tag_id,
					date: this.current_edit_record.date_stamp
				}
			];
		}

		this.api['get' + this.api.key_name + 'DefaultData']( args, {
			onResult: function( result ) {
				var select_shift;
				var result_data = result.getResult();

				select_shift = result_data;
				if ( $this.select_cells_Array.length >= 1 ) {
					for ( var i = 0, n = args.length; i < n; i++ ) {
						var item = args[i];
						if ( i == 0 ) {
							select_shift.branch_id = item.branch_id;
							select_shift.department_id = item.department_id;
							select_shift.job_id = item.job_id;
							select_shift.job_item_id = item.job_item_id;
							select_shift.punch_tag_id = item.punch_tag_id;
						} else {
							( select_shift.branch_id !== item.branch_id && select_shift.branch_id !== '-2' ) ? select_shift.branch_id = '-2' : item.branch_id;
							( select_shift.department_id !== item.department_id && select_shift.department_id !== '-2' ) ? select_shift.department_id = '-2' : item.department_id;
							( select_shift.job_id !== item.job_id && select_shift.job_id !== '-2' ) ? select_shift.job_id = '-2' : item.job_id;
							( select_shift.job_item_id !== item.job_item_id && select_shift.job_item_id !== '-2' ) ? select_shift.job_item_id = '-2' : item.job_item_id;
							( select_shift.punch_tag_id !== item.punch_tag_id && select_shift.punch_tag_id !== '-2' ) ? select_shift.punch_tag_id = '-2' : item.punch_tag_id;
						}
					}
				}

				if ( !doing_save_and_new ) {
					select_shift.date_stamp = $this.getSelectDate();

				} else {
					var temp_date = Global.strToDate( $this.current_edit_record.date_stamp );
					select_shift.date_stamp = new Date( new Date( temp_date.getTime() ).setDate( temp_date.getDate() + 1 ) ).format();

				}

				if ( !select_shift.start_date_stamp ) {
					select_shift.start_date_stamp = select_shift.date_stamp;
				}

				select_shift.id = '';

				if ( $this.sub_view_mode && $this.parent_key ) {
					result_data[$this.parent_key] = $this.parent_value;
				}

				$this.current_edit_record = select_shift;
				$this.initEditView();

			}
		} );
	}

	openEditView() {

		if ( !this.edit_view ) {
			this.initEditViewUI( 'Schedule', 'ScheduleEditView.html' );
		}
		this.previous_absence_policy_id = false;
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

	setJobValueWhenUserChanged( job, job_id_col_name, filter_data ) {
		var $this = this;

		//Error: Uncaught TypeError: Cannot set property 'job_item_id' of null in /interface/html5/#!m=TimeSheet&date=20150126&user_id=54286 line 6785
		if ( !$this.current_edit_record ) {
			return;
		}

		if ( this.edit_view_ui_dic['user_ids'] && this.edit_view_ui_dic['user_ids'].is( ':visible' ) ) {
			filter_data['user_id'] = this.edit_view_ui_dic['user_ids'].getValue();

			//If more than one user is selected, don't filter by user_id at all, show all jobs and let the validation system handle it.
			if ( filter_data['user_id'].length == 1 ) {
				filter_data['user_id'] = filter_data['user_id'][0];
			} else {
				filter_data['user_id'] = false;
			}
		} else {
			filter_data['user_id'] = this.current_edit_record['user_id'];
		}

		var job_widget = $this.edit_view_ui_dic[job_id_col_name];
		var current_job_id = job_widget.getValue();
		job_widget.setSourceData( null ); //Clear out source data so its reloaded when the Job dropdown is expanded again.
		job_widget.setCheckBox( true );
		this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );

		var args = {};
		args.filter_data = filter_data;
		$this.edit_view_ui_dic[job_id_col_name].setDefaultArgs( args );

		return;
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
				if ( Global.getProductEdition() >= 20 ) {
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
				if ( Global.getProductEdition() >= 20 ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
			case 'punch_tag_id':
				if ( Global.getProductEdition() >= 20 ) {
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
				if ( Global.getProductEdition() >= 20 ) {
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
			case 'department_id':
				if ( Global.getProductEdition() >= 20 ) {
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
				if ( Global.getProductEdition() >= 20 ) {
					this.onJobQuickSearch( key, c_value );
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
			case 'punch_tag_quick_search':
				if ( Global.getProductEdition() >= 20 ) {
					this.onPunchTagQuickSearch( c_value, this.getPunchTagFilterData(), 'punch_tag_id' );

					//Don't validate immediately as onPunchTagQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
			case 'status_id':
				this.onTypeChange( true );
				break;
			case 'user_id':
			case 'user_ids':
				this.setEditMenu();

				this.setAbsencePolicyWhenUserChanged();

				if ( Global.getProductEdition() >= 20 ) {
					if ( this.edit_view_ui_dic['job_id'] ) {
						this.setJobValueWhenUserChanged( this.edit_view_ui_dic['job_id'].getValue( true ), 'job_id', {
							status_id: 10,
							user_id: this.edit_view_ui_dic[key].getValue(),
						} );
					}

					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				return;
			case 'start_date_stamps':
				this.setEditMenu();
				this.current_edit_record['start_date_stamp'] = c_value;
				break;

		}

		if ( key == 'absence_policy_id' ) {
			this.previous_absence_policy_id = this.current_edit_record.absence_policy_id;
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

				var startTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['start_time'];
				var endTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['end_time'];
				var schedulePolicyId = this.current_edit_record['schedule_policy_id'];
				var user_id = this.current_edit_record.user_id;

				this.api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, user_id, {
					onResult: function( total_time ) {

						//Uncaught TypeError: Cannot set property 'total_time' of null
						//Error: Uncaught TypeError: Cannot read property 'setValue' of undefined in interface/html5/#!m=Schedule&date=20160202&mode=week&a=new&tab=Schedule line 1799
						if ( !$this.edit_view || !$this.current_edit_record || !$this.edit_view_ui_dic['total_time'] ) {
							return;
						}

						//Fixed exception that total_time is null
						if ( total_time && total_time.isValid() && total_time.getResult() ) {
							total_time = total_time.getResult();
						} else {
							total_time = $this.current_edit_record.total_time ? $this.current_edit_record.total_time : 0;
						}

						$this.current_edit_record.total_time = total_time;
						total_time = Global.getTimeUnit( total_time );
						$this.edit_view_ui_dic['total_time'].setValue( total_time );

						$this.onAvailableBalanceChange();

					}
				} );
//

			} else {
				this.onAvailableBalanceChange();
			}

		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setAbsencePolicyWhenUserChanged() {

		var $this = this;
		var absence_widget = $this.edit_view_ui_dic['absence_policy_id'];
		absence_widget.setSourceData( null );
		var old_value = absence_widget.getValue();

		var args = {};
		args.filter_data = { id: old_value };

		args = this.setAbsencePolicyFilter( args );

		if ( old_value ) {

			$this.absence_policy_api.getAbsencePolicy( args, {
				onResult: function( task_result ) {

					// Returning early to help mitigate #2889 - "Error: Uncaught TypeError: Cannot set property 'absence_policy_id' of null"
					// This can happen when the user saves or leaves the page before the API call is completed.
					if ( $this.current_edit_record === null || $this.current_edit_record === undefined ) {
						return;
					}

					var data = task_result.getResult();

					if ( data.length > 0 ) {
						absence_widget.setValue( old_value );
						$this.current_edit_record.absence_policy_id = old_value;
					} else {
						absence_widget.setValue( false );
						$this.current_edit_record.absence_policy_id = false;
					}
					$this.onAvailableBalanceChange();
					$this.validate();

				}
			} );
		} else {
			this.onAvailableBalanceChange();
			this.validate();
		}
	}

	//Make sure this.current_edit_record is updated before validate
	validate() {
		var $this = this;
		var record = {};
		if ( this.is_mass_adding ) {
			record = [];
			$.each( this.select_cells_Array, function( index, value ) {
				if ( value.hasOwnProperty( 'user_id' ) && value.hasOwnProperty( 'date' ) && value.date ) {
					var commonRecord = Global.clone( $this.current_edit_record );
					delete commonRecord.user_ids;
					delete commonRecord.start_dates;
					commonRecord.id = '';
					commonRecord.user_id = value.user_id;
					commonRecord.start_date_stamp = value.date;
					commonRecord = $this.buildMassAddRecord( commonRecord );
					record.push( commonRecord );
				}
			} );
		} else if ( this.is_mass_editing ) {
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

			if ( this.mass_edit_record_ids.length > 0 ) {
				var checkFields = record;
				record = [];
				$.each( this.mass_edit_record_ids, function( index, value ) {
					var commonRecord = Global.clone( checkFields );
					commonRecord.id = value;
					commonRecord = $this.processAddRecord( commonRecord );
					record.push( commonRecord );
				} );
				$.each( this.select_all_shifts_array, function( index, value ) {
					if ( !value.id || value.id == TTUUID.zero_id ) {
						var commonRecord = Global.clone( value );
						for ( var key in checkFields ) {
							commonRecord[key] = checkFields[key];
						}
						commonRecord = $this.processAddRecord( commonRecord );
						record.push( commonRecord );
					}
				} );
				record = this.getRecordsFromUserIDs( record );
			} else {
				var record_array = [];
				$.each( this.select_all_shifts_array, function( index, value ) {
					if ( !value.id || value.id == TTUUID.zero_id ) {
						var commonRecord = Global.clone( value );
						for ( var key in record ) {
							commonRecord[key] = record[key];
						}
						commonRecord = $this.processAddRecord( commonRecord );
						record_array.push( commonRecord );
					}
				} );
				if ( record_array.length < 1 ) {
					if ( this.select_cells_Array.length > 0 ) {
						$this.processAddRecord( record );
						record = this.getRecordsFromUserIDs( [record] );
					}
				} else {
					record = record_array;
					record = this.getRecordsFromUserIDs( record );
				}
			}

		} else {
			//Error: Uncaught TypeError: Cannot read property 'indexOf' of undefined in interface/html5/#!m=Schedule&date=20151204&mode=day line 1954
			if ( this.current_edit_record && this.current_edit_record.start_date_stamp &&
				( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
					$.type( this.current_edit_record.start_date_stamp ) === 'array' ) ) {
				if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
					this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
				}
				record = [];
				for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
					var commonRecord = Global.clone( $this.current_edit_record );
					commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];
					if ( this.select_cells_Array.length > 0 ) {
						$this.processAddRecord( commonRecord );
					}
					record.push( commonRecord );
				}
				record = this.getRecordsFromUserIDs( record );
			} else {
				record = Global.clone( this.current_edit_record );
				if ( this.select_cells_Array.length > 0 ) {
					$this.processAddRecord( record );
				}
				record = this.getRecordsFromUserIDs( [record] );
			}

		}
		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );
			}
		} );
	}

	getRecordsFromUserIDs( record ) {
		var result = [];

		for ( var j = 0; j < record.length; j++ ) {
			var common_record = record[j];

			if ( common_record.user_ids && common_record.user_ids.length > 0 ) {
				for ( var y = 0; y < common_record.user_ids.length; y++ ) {
					var user_id = common_record.user_ids[y];
					if ( Global.isObject( user_id ) && user_id.id ) {
						user_id = user_id.id;
					}

					var new_common_record = Global.clone( common_record );
					new_common_record.user_id = user_id;
					result.push( new_common_record );

				}
			} else {

				if ( ( !this.current_edit_record || !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) && !this.is_mass_editing ) {
					common_record.user_id = TTUUID.zero_id;
				}

				result.push( common_record );

			}

		}

		return result;
	}

	onSaveAndCopy( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.current_edit_record;
		record = this.processAddRecord( record );
		record = this.getRecordsFromUserIDs( [record] );

		if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamp ) === 'array' ) {

			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
				this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
			}

			record = [];
			for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];
				commonRecord = this.processAddRecord( commonRecord );
				record.push( commonRecord );
			}
			record = this.getRecordsFromUserIDs( record );
		}

		this.clearNavigationData();
		this.api['set' + this.api.key_name]( record, false, false, ignoreWarning, {
			onResult: function( result ) {
				if ( $this.current_edit_record ) {
					var current_date_str = $this.current_edit_record.start_date_stamp;

					if ( $.type( current_date_str ) === 'array' ) {
						current_date_str = current_date_str[current_date_str.length - 1];
					}

					var current_date = Global.strToDate( current_date_str );
					var next_date = new Date( new Date( current_date.getTime() ).setDate( current_date.getDate() + 1 ) );

					$this.current_edit_record.start_date_stamp = next_date.format();

					$this.onSaveAndCopyResult( result );
				}
			}
		} );
	}

	onSaveAndNewClick( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.setCurrentEditViewState( 'new' );
		var record = this.current_edit_record;
		record = this.processAddRecord( record );
		record = this.getRecordsFromUserIDs( [record] );

		if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamp ) === 'array' ) {

			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
				this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
			}

			record = [];
			for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];
				commonRecord = this.processAddRecord( commonRecord );
				record.push( commonRecord );
			}
			record = this.getRecordsFromUserIDs( record );
		}

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndNewResult( result );
			}
		} );
	}

	buildSelectedCellsRecord() {
		var $this = this;
		var retval = [];
		$.each( this.select_cells_Array, function( index, value ) {
			if ( value.hasOwnProperty( 'user_id' ) && value.hasOwnProperty( 'date' ) && value.date ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				delete commonRecord.user_ids;
				delete commonRecord.start_dates;
				commonRecord.id = '';
				commonRecord.user_id = value.user_id;
				commonRecord.start_date_stamp = value.date;
				commonRecord = $this.buildMassAddRecord( commonRecord );

				retval.push( commonRecord );
			}

		} );

		return retval;
	}

	buildMassAddRecord( record ) {
		var massAddArgs = this._createParametersForAdd();
		for ( var i = 0, n = massAddArgs.length; i < n; i++ ) {
			var item = massAddArgs[i];
			if ( record.user_id === item.user_id ) {
				record.branch_id == '-2' ? ( record.branch_id = item.branch_id ) : record.branch_id;
				record.department_id == '-2' ? ( record.department_id = item.department_id ) : record.department_id;
				record.job_id == '-2' ? ( record.job_id = item.job_id ) : record.job_id;
				record.job_item_id == '-2' ? ( record.job_item_id = item.job_item_id ) : record.job_item_id;
				record.punch_tag_id == '-2' ? ( record.punch_tag_id = item.punch_tag_id ) : record.punch_tag_id;
			}
		}
		return record;
	}

	processAddRecord( record ) {
		var massAddArgs = this._createParametersForAdd();
		for ( var i = 0, n = massAddArgs.length; i < n; i++ ) {
			var item = massAddArgs[i];
			record.branch_id == '-2' ? ( record.branch_id = item.branch_id ) : record.branch_id;
			record.department_id == '-2' ? ( record.department_id = item.department_id ) : record.department_id;
			record.job_id == '-2' ? ( record.job_id = item.job_id ) : record.job_id;
			record.job_item_id == '-2' ? ( record.job_item_id = item.job_item_id ) : record.job_item_id;
			record.punch_tag_id == '-2' ? ( record.punch_tag_id = item.punch_tag_id ) : record.punch_tag_id;
			break;
		}
		return record;
	}

	getSelectedId( record, field, massAddArgs ) {
		for ( var i = 0, n = massAddArgs.length; i < n; i++ ) {
			var item = massAddArgs[i];
			if ( record.user_id === item.user_id ) {
				record[field] = item[field];
			}
		}
	}

	onSaveAndContinue( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.current_edit_record;
		record = this.processAddRecord( record );
		record = this.uniformVariable( record );

		if ( this.current_edit_record.start_date_stamp && ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 || $.type( this.current_edit_record.start_date_stamp ) === 'array' ) ) {
			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
				this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
			}

			record = [];
			for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];
				commonRecord = this.processAddRecord( commonRecord );
				record.push( commonRecord );
			}
			record = this.getRecordsFromUserIDs( record );
		} else {
			record = this.getRecordsFromUserIDs( [record] );
		}

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.previous_absence_policy_id = false;
				$this.onSaveAndContinueResult( result );

			}
		} );
	}

	onSaveClick( ignoreWarning ) {
		var $this = this;
		var record;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		LocalCacheData.current_doing_context_action = 'save';

		if ( this.is_mass_adding ) {
			record = this.buildSelectedCellsRecord();
		} else if ( this.is_mass_editing ) {

			var checkFields = this.getChangedFields();

			record = [];

			$.each( this.mass_edit_record_ids, function( index, value ) {
				var commonRecord = Global.clone( checkFields );
				commonRecord.id = value;
				commonRecord = $this.processAddRecord( commonRecord );
				record.push( commonRecord );

			} );

			$.each( this.select_all_shifts_array, function( index, value ) {
				if ( !value.id || value.id == TTUUID.zero_id ) {
					var commonRecord = Global.clone( value );
					for ( var key in checkFields ) {
						commonRecord[key] = checkFields[key];
					}
					commonRecord = $this.processAddRecord( commonRecord );
					record.push( commonRecord );
				}

			} );

		} else if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamp ) === 'array' ) {

			if ( this.current_edit_record.start_date_stamp.indexOf( ' - ' ) > 0 ) {
				this.current_edit_record.start_date_stamp = this.parserDatesRange( this.current_edit_record.start_date_stamp );
			}

			record = [];
			for ( var i = 0; i < this.current_edit_record.start_date_stamp.length; i++ ) {
				var commonRecord = Global.clone( $this.current_edit_record );
				commonRecord.start_date_stamp = this.current_edit_record.start_date_stamp[i];
				commonRecord = $this.processAddRecord( commonRecord );
				record.push( commonRecord );
			}

			record = this.getRecordsFromUserIDs( record );

		} else {

			record = this.current_edit_record;
			record = $this.processAddRecord( record );
			record = this.getRecordsFromUserIDs( [record] );

		}

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result && result.isValid() ) {
					var result_data = result.getResult();
					//#2571 - Cannot read property 'id' of null
					if ( result_data === true && $this.current_edit_record ) {
						$this.refresh_id = $this.current_edit_record.id;
					} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
						$this.refresh_id = result_data;
					}
					$this.search( false, false );
					$this.previous_absence_policy_id = false;

					$this.removeEditView();

				} else {
					//BUG#2073 - Pulled out the error message box that was showing the result array as its "toString" representation. ([object][object]);
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	}

	removeEditView() {
		super.removeEditView();

		this.setMoveOrDropMode( this.select_drag_menu_id ? this.select_drag_menu_id : 'move' );
		this.selected_user_ids = [];
		this.is_mass_adding = false;
	}

	setEditMenuSaveAndContinueIcon( context_btn ) {
		this.saveAndContinueValidate( context_btn );

		if ( this.is_mass_editing || this.is_viewing || this.is_mass_adding || this.isMassEmployeeOrDate() ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	isMassEmployeeOrDate() {

		if ( this.current_edit_record && this.current_edit_record.start_date_stamps && ( this.current_edit_record.start_date_stamps.indexOf( ' - ' ) > 0 ||
			$.type( this.current_edit_record.start_date_stamps ) === 'array' && this.current_edit_record.start_date_stamps.length > 1
		) ) {
			return true;
		}

		if ( this.current_edit_record && this.current_edit_record.user_ids && this.current_edit_record.user_ids.length > 1 ) {
			return true;
		}

		return false;
	}

	setEditMenuSaveAndAddIcon( context_btn ) {
		this.saveAndNewValidate( context_btn );

		if ( this.is_viewing || this.is_mass_editing || this.is_mass_adding ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuSaveAndCopyIcon( context_btn ) {
		this.saveAndCopyValidate( context_btn );

		if ( this.is_viewing || this.is_mass_editing || this.is_mass_adding ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onTypeChange( getRate ) {
		if ( this.current_edit_record.status_id == 20 ) {
			this.attachElement( 'absence_policy_id' );
		} else {
			this.detachElement( 'absence_policy_id' );
		}
	}

	setEditViewData() {

		var $this = this;
		super.setEditViewData(); //Set Navigation
		$this.onTypeChange( false );
	}

	checkOpenPermission() {
		if ( Global.getProductEdition() >= 15 && PermissionManager.validate( 'schedule', 'view_open' ) ) {
			return true;
		}

		return false;
	}

	getCustomFieldReferenceField() {
		return 'note';
	}

	buildEditViewUI() {

		var $this = this;

		var form_item_input;
		var widgetContainer;

		// #VueContextMenu# After we add the edit_view to the page in initEditViewUI(), add the context menu (Vue needs a valid id in dom)
		if( ContextMenuManager.getMenu( this.determineContextMenuMountAttributes().id ) === undefined ) {
			this.buildContextMenu();
		} else {
			Debug.Warn( 'Context Menu ('+ this.determineContextMenuMountAttributes().id +') already exists for: '+ this.viewId, 'ScheduleViewController.js', 'ScheduleViewController', 'buildEditViewUI', 10 );
		}

		this.edit_view_close_icon = this.edit_view.find( '.close-icon' );
		this.edit_view_close_icon.hide();
		this.edit_view_close_icon.click( function() {
			$this.onCloseIconClick();
		} );

		var tab_model = {
			'tab_schedule': { 'label': $.i18n._( 'Schedule' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		//Tab 0 start

		var tab_schedule = this.edit_view_tab.find( '#tab_schedule' );

		var tab_schedule_column1 = tab_schedule.find( '.first-column' );

		//Employee

		var production_edition_id = Global.getProductEdition();
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: !this.checkOpenPermission(),
			set_open: this.checkOpenPermission(),
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'schedule';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_schedule_column1, '', null, true );

		//Mass Add Employees

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: !this.checkOpenPermission(),
			set_open: this.checkOpenPermission(),
			addition_source_function: ( function( target, source_data ) {
				return $this.onEmployeeSourceCreate( target, source_data );
			} ),
			field: 'user_ids'
		} );

		default_args = {};
		default_args.permission_section = 'schedule';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_schedule_column1, '', null, true );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_schedule_column1 );

		//Absence Policy
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIAbsencePolicy,
			allow_multiple_selection: false,
			layout_name: 'global_absences',
			show_search_inputs: true,
			set_empty: true,
			field: 'absence_policy_id'
		} );

		form_item_input.customSearchFilter = function( filter ) {
			return $this.setAbsencePolicyFilter( filter );
		};

		this.addEditFieldToColumn( $.i18n._( 'Absence Policy' ), form_item_input, tab_schedule_column1, '', null, true );

		//Available Balance
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'available_balance' } );

		widgetContainer = $( '<div class=\'widget-h-box available-balance-h-box\'></div>' );
		this.available_balance_info = $( '<span class="available-balance-info tticon tticon-info_black_24dp"></span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( this.available_balance_info );

		this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), form_item_input, tab_schedule_column1, '', widgetContainer, true );

		if ( !this.current_edit_record || ( this.current_edit_record.user_ids && this.current_edit_record.user_ids.length > 1 ) ) {
			this.detachElement( 'available_balance' );
		}

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'start_date_stamp', validation_field: 'date_stamp' } );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', null, true );

		//Dates
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TRangePicker( { field: 'start_date_stamps' } );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', null, true );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			allow_multiple_selection: true,
			layout_name: 'global_option_column',
			show_search_inputs: false,
			set_empty: true,
			field: 'start_dates'
		} );

		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', null, true );

		//Start Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'start_time' } );

		this.addEditFieldToColumn( $.i18n._( 'In' ), form_item_input, tab_schedule_column1, '', null, true );

		//End Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );
		form_item_input.TTimePicker( { field: 'end_time' } );

		this.addEditFieldToColumn( $.i18n._( 'Out' ), form_item_input, tab_schedule_column1, '', null, true );

		//Total
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'total_time' } );
		form_item_input.css( 'cursor', 'pointer' );
		this.addEditFieldToColumn( $.i18n._( 'Total' ), form_item_input, tab_schedule_column1 );

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
		this.addEditFieldToColumn( $.i18n._( 'Schedule Policy' ), form_item_input, tab_schedule_column1 );

		//Default Branch
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIBranch,
			allow_multiple_selection: false,
			layout_name: 'global_branch',
			show_search_inputs: true,
			set_empty: true,
			field: 'branch_id',
			addition_source_function: ( function( target, source_data ) {
				return $this.onSourceDataCreate( target, source_data );
			} ),
			//FIXME: Follow -2 to the API do not switch to UUID unless absolutely necessary?
			added_items: [
				{ value: TTUUID.not_exist_id, label: Global.default_item },
				{ value: '-2', label: Global.selected_item }
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_schedule_column1, '', null, true );

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
			field: 'department_id',
			addition_source_function: ( function( target, source_data ) {
				return $this.onSourceDataCreate( target, source_data );
			} ),
			added_items: [
				{ value: TTUUID.not_exist_id, label: Global.default_item },
				{ value: '-2', label: Global.selected_item }
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_schedule_column1, '', null, true );

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
				addition_source_function: ( function( target, source_data ) {
					return $this.onSourceDataCreate( target, source_data );
				} ),
				added_items: [
					{ value: TTUUID.not_exist_id, label: Global.default_item },
					{ value: '-2', label: Global.selected_item }
				]
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_coder.TTextInput( { field: 'job_quick_search', disable_keyup_event: true } );
			job_coder.addClass( 'job-coder' );

			widgetContainer.append( job_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_schedule_column1, '', widgetContainer, true );

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
				addition_source_function: ( function( target, source_data ) {
					return $this.onSourceDataCreate( target, source_data );
				} ),
				added_items: [
					{ value: TTUUID.not_exist_id, label: Global.default_item },
					{ value: '-2', label: Global.selected_item }
				]
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true } );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_schedule_column1, '', widgetContainer, true );

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
				field: 'punch_tag_id',
				addition_source_function: ( function( target, source_data ) {
					return $this.onSourceDataCreatePunchTag( target, source_data );
				} ),
				added_items: [
					{ value: TTUUID.not_exist_id, label: Global.default_item }
				]
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var punch_tag_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			punch_tag_coder.TTextInput( { field: 'punch_tag_quick_search', disable_keyup_event: true } );
			punch_tag_coder.addClass( 'job-coder' );

			widgetContainer.append( punch_tag_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Tags' ), [form_item_input, punch_tag_coder], tab_schedule_column1, '', widgetContainer, true );


			if ( !this.show_punch_tag_ui ) {
				this.detachElement( 'punch_tag_id' );
			}
		}

		//Note
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'note', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Note' ), form_item_input, tab_schedule_column1, '', null, null, true );
		form_item_input.parent().width( '45%' );

		if ( Global.getProductEdition() >= 15 && this.is_viewing == false ) {
			//Notify Employee
			form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_input.TCheckbox( { field: 'notify_user_schedule_change' } );
			this.addEditFieldToColumn( $.i18n._( 'Notify Employee' ), form_item_input, tab_schedule_column1, '', null, true );
		}

		TTPromise.resolve( 'Schedule', 'init' );
	}

	setDefaultMenuDeleteIcon( context_btn, grid_selected_length ) {
		if ( !this.deletePermissionValidate( 'schedule' ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length > 0 && this.deleteOwnerOrChildPermissionValidate( 'schedule' ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onAvailableBalanceChange() {
		if ( this.current_edit_record.status_id == 20
			&& this.current_edit_record.hasOwnProperty( 'absence_policy_id' )
			&& this.current_edit_record.absence_policy_id && !this.is_mass_editing ) {
			this.getAvailableBalance();
		} else {
			this.detachElement( 'available_balance' );
		}
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

	getSelectUsersArray() {
		var result = [];
		var cells_array = this.select_cells_Array;
		var len = cells_array.length;
		var date_dic = {};

		for ( var i = 0; i < len; i++ ) {
			var item = cells_array[i];

			// If select empty cell with no user inside, set user_id to 0 as OPEN
			if ( !item.user_id ) {
				item.user_id = TTUUID.zero_id;
			}

			date_dic[item.user_id] = true;
		}

		for ( var key in date_dic ) {
			result.push( key );
		}

		if ( result.length === 0 ) {
			result.push( this.getDefaultUser() );
		}

		return result;
	}

	getSelectDateArray() {
		var result = [];

		var cells_array = this.select_cells_Array;

		var len = cells_array.length;

		var date_dic = {};
		for ( var i = 0; i < len; i++ ) {
			var item = cells_array[i];

			if ( item.date ) {
				date_dic[item.date] = true;
			}

		}

		for ( var key in date_dic ) {
			result.push( key );
		}

		return result;
	}

	getAvailableBalance() {
		if ( Global.isSet( this.current_edit_record ) == false ) {
			return;
		}

		var $this = this;
		var user_id = this.current_edit_record.user_id;
		var total_time = this.current_edit_record.total_time;
		var last_date_stamp = this.current_edit_record.start_date_stamp;

		//On first run, set previous_absence_policy_id.
		if ( this.previous_absence_policy_id == false ) {
			this.previous_absence_policy_id = this.current_edit_record.absence_policy_id;
		}

		//For mass adding case, select multiple cells and click new
		if ( this.is_mass_adding ) {
			if ( this.current_edit_record.user_ids.length > 1 ) {
				this.detachElement( 'available_balance' );
				return;
			} else {
				user_id = this.current_edit_record.user_ids[0];
				if ( !user_id ) {
					this.detachElement( 'available_balance' );
					return;
				}
			}

			total_time = total_time * this.current_edit_record.start_dates.length;
			last_date_stamp = this.current_edit_record.start_dates[this.current_edit_record.start_dates.length - 1];
		} else {
			//get dates from date ranger
			if ( $.type( last_date_stamp ) === 'string'  && last_date_stamp.indexOf( ' - ' ) > 0 ) {
				last_date_stamp = this.parserDatesRange( last_date_stamp ); //Converts last_date_stamp into an array.
			}

			if ( $.type( last_date_stamp ) === 'array' && last_date_stamp.length > 0 ) {
				total_time = ( total_time * last_date_stamp.length );
				last_date_stamp = last_date_stamp[ ( last_date_stamp.length - 1 ) ];
			}

			if ( ( !this.current_edit_record || !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) && !this.is_mass_editing ) {
				if ( this.current_edit_record.user_ids.length < 1 || this.current_edit_record.user_ids.length > 1 ) {
					this.detachElement( 'available_balance' );
					return;
				} else {
					user_id = this.current_edit_record.user_ids[0];
					if ( !user_id ) {
						this.detachElement( 'available_balance' );
						return;
					}
				}
			}
		}

		//Check if the user is editing a existing record that was originally Status=Working, and changing it to Status=Absent, in that case we can't adjust for the existing total time and must clear it out.
		if ( this.current_edit_record && this.current_edit_record.id && this.prev_status_id == 10 && this.edit_view_ui_dic['status_id'].getValue() == 20 ) {
			this.prev_total_time = 0;
		}

		if ( this.current_edit_record.absence_policy_id != TTUUID.zero_id ) {
			this.api_absence_policy.getProjectedAbsencePolicyBalance(
				this.current_edit_record.absence_policy_id,
				user_id,
				last_date_stamp,
				total_time,
				this.prev_total_time,
				this.previous_absence_policy_id, {
					onResult: function( result ) {
						$this.getBalanceHandler( result, last_date_stamp );
					}
				}
			);
		} else {
			this.getBalanceHandler( false, last_date_stamp );
		}
	}

	buildSearchAndLayoutUI() {
		var layout_div = this.search_panel.find( 'div #saved_layout_content_div' );

		//Display Columns

		var form_item = $( $.fn.SearchPanel.html.form_item );
		var form_item_label = form_item.find( '.form-item-label' );
		var form_item_input_div = form_item.find( '.form-item-input-div' );

		this.column_selector = Global.loadWidgetByName( FormItemType.AWESOME_DROPDOWN );

		this.column_selector = this.column_selector.ADropDown( {
			display_show_all: false,
			id: 'column_selector',
			key: 'value',
			allow_drag_to_order: true,
			display_close_btn: false,
			display_column_settings: false,
			static_height: 150
		} );

		form_item_label.text( $.i18n._( 'Display Columns' ) );
		form_item_input_div.append( this.column_selector );

		layout_div.append( form_item );

		layout_div.append( '<div class=\'clear-both-div\'></div>' );

		this.column_selector.setColumns( [
			{ name: 'label', index: 'label', label: $.i18n._( 'Column Name' ), width: 100, sortable: false }
		] );

		//Save and update layout

		form_item = $( $.fn.SearchPanel.html.form_item );
		form_item_label = form_item.find( '.form-item-label' );
		form_item_input_div = form_item.find( '.form-item-input-div' );

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

	setCurrentEditRecordData() {
		var $this = this;
		if ( this.is_mass_adding ) {
			this.attachElement( 'start_dates' );
			this.detachElement( 'start_date_stamp' );
			this.detachElement( 'start_date_stamps' );

			this.attachElement( 'user_ids' );
			this.detachElement( 'user_id' );

			this.edit_view_ui_dic.start_dates.setEnabled( false );
			this.edit_view_ui_dic.user_ids.setEnabled( false );

		} else {
			this.detachElement( 'start_dates' );

			if ( ( this.current_edit_record.id && this.current_edit_record.id != TTUUID.zero_id ) || this.is_mass_editing ) {
				this.attachElement( 'start_date_stamp' );
				this.detachElement( 'start_date_stamps' );
				this.detachElement( 'user_ids' );
				this.attachElement( 'user_id' );

			} else {
				this.attachElement( 'start_date_stamps' );
				this.detachElement( 'start_date_stamp' );
				this.current_edit_record.start_date_stamps = this.current_edit_record.start_date_stamp;

				this.attachElement( 'user_ids' );
				this.detachElement( 'user_id' );
			}

		}

		this.prev_total_time = 0;

		//When editing an existing record, store the original status_id so we can later use it when calculating available balance.
		if ( this.current_edit_record.id && this.current_edit_record.id != TTUUID.zero_id ) {
			this.prev_status_id = this.current_edit_record['status_id'];
		}

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_ids':
						if ( widget.is( ':visible' ) ) {
							var user_array = this.getSelectUsersArray();
							this.current_edit_record[key] = user_array;
							widget.setValue( user_array );
						}
						break;
					case 'start_dates':
						var date_array = this.getSelectDateArray();
						this.current_edit_record[key] = date_array;
						date_array = Global.buildRecordArray( date_array );

						widget.setSourceData( date_array );
						widget.setValue( date_array );
						break;
					case 'total_time':
						//Don't set when copy as new
						if ( this.current_edit_record.id && this.current_edit_record.id != TTUUID.zero_id ) {
							this.prev_total_time = this.current_edit_record[key];
						}
						var startTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['start_time'];
						var endTime = this.current_edit_record['date_stamp'] + ' ' + this.current_edit_record['end_time'];
						var schedulePolicyId = this.current_edit_record['schedule_policy_id'];
						var user_id = this.current_edit_record.user_id;
						var total_time = this.api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, user_id, { async: false } );
						// Error: Uncaught TypeError: Cannot read property 'getResult' of undefined in interface/html5/#!m=Schedule&date=20160201&mode=week&a=new&tab=Schedule
						if ( total_time && total_time.isValid() && total_time.getResult() ) {
							total_time = total_time.getResult();
						} else {
							total_time = false;
						}
						this.current_edit_record.total_time = total_time;
						widget.setValue( Global.getTimeUnit( total_time ) );
						break;
					case 'job_id':
						if ( Global.getProductEdition() >= 20 ) {
							var user_id = false;
							if ( this.edit_view_ui_dic['user_ids'] && this.edit_view_ui_dic['user_ids'].is( ':visible' ) ) {
								user_id = this.getSelectUsersArray();

								//If more than one user is selected, don't filter by user_id at all, show all jobs and let the validation system handle it.
								if ( user_id.length == 1 ) {
									user_id = user_id[0];
								} else {
									user_id = false;
								}
							} else {
								user_id = this.current_edit_record['user_id'];
							}

							var args = {};
							args.filter_data = {
								status_id: 10,
								user_id: user_id,
								punch_branch_id: this.current_edit_record.branch_id,
								punch_department_id: this.current_edit_record.department_id
							};
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_item_id':
						if ( Global.getProductEdition() >= 20 ) {
							var args = {};
							args.filter_data = { status_id: 10, job_id: this.current_edit_record.job_id };
							widget.setDefaultArgs( args );
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'punch_tag_id':
						if ( Global.getProductEdition() >= 20 ) {
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
						if ( Global.getProductEdition() >= 20 ) {
							var args = {};
							args.filter_data = { user_id: this.current_edit_record.user_id };
							widget.setDefaultArgs( args );
						}
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'department_id':
						if ( Global.getProductEdition() >= 20 ) {
							var args = {};
							args.filter_data = { user_id: this.current_edit_record.user_id, branch_id: this.current_edit_record.branch_id };
							widget.setDefaultArgs( args );
						}
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'punch_tag_quick_search':
						break;
					case 'job_quick_search':
//						widget.setValue( this.current_edit_record['job_id'] ? this.current_edit_record['job_id'] : 0 );
						break;
					case 'job_item_quick_search':
//						widget.setValue( this.current_edit_record['job_item_id'] ? this.current_edit_record['job_item_id'] : 0 );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.onAvailableBalanceChange();
		this.setEditViewDataDone();
	}

	setAbsencePolicyFilter( filter ) {
		if ( !filter.filter_data ) {
			filter.filter_data = {};
		}

		if ( !this.is_mass_editing ) {
			if ( !this.current_edit_record || !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) {
				filter.filter_data.user_id = this.current_edit_record?.user_ids || null;
			} else {
				filter.filter_data.user_id = this.current_edit_record.user_id;
			}
		} else {
			if ( this.edit_view_ui_dic.user_id.isChecked() ) {
				filter.filter_data.user_id = this.current_edit_record.user_id;
			} else {
				filter.filter_data.user_id = this.selected_user_ids;
			}
		}

		if ( filter.filter_columns ) {
			filter.filter_columns.absence_policy = true;
		}

		return filter;
	}

	setDefaultMenuCopyIcon( context_btn, grid_selected_length ) {
		if ( !this.copyPermissionValidate() ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length >= 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuCopyAsNewIcon( context_btn, grid_selected_length ) {
		if ( ( !this.copyAsNewPermissionValidate() ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}


	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'import_icon':
				this.setEditMenuImportIcon( context_btn );
				break;
			case 'AutoPunch':
				this.setEditMenuAutoPunchIcon( context_btn );
				break;
			case 'AddRequest':
				this.setEditMenuAddRequestIcon( context_btn );
				break;
			case 'find_available':
				this.setEditMenuFindAvailableIcon( context_btn );
				break;
		}
	}

	_getGridSelectedLength() {
		var result = 0;
		result = this.select_all_shifts_array.length;

		return result;
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'move':
				if ( !this.movePermissionValidate() ) {
					ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
				}
				break;
			case 'drag_copy':
				if ( !this.copyPermissionValidate() ) {
					ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
				}
				break;
			case 'swap':
				if ( !this.editPermissionValidate() ) {
					ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
				}
				break;
			case 'override':
				if ( ( !this.editPermissionValidate() && !this.movePermissionValidate() && !this.copyPermissionValidate() ) ) {
					ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
				}
				break;
			case 'edit_employee':
				this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
				break;
			case 'import_icon':
				this.setDefaultMenuImportIcon( context_btn, grid_selected_length );
				break;
			case 'timesheet':
				this.setDefaultMenuEditTimesheetIcon( context_btn, grid_selected_length );
				break;
			case 'find_available':
				this.setDefaultMenuFindAvailabletIcon( context_btn, grid_selected_length );
				break;
			case 'AutoPunch':
				this.setAutoPunchIcon( context_btn, grid_selected_length );
				break;
			case 'AddRequest':
				this.setAddRequestIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setDefaultMenu( doNotSetFocus ) {
		//Schedule uses a different grid than other views and needs "select_all_shifts_array.length" instesd of "this.getGridSelectIdArray()" for grid_selected_length
		super.setDefaultMenu( doNotSetFocus, this._getGridSelectedLength() );
		//Set move or drop mode after rest of menu.
		this.setMoveOrDropMode( this.select_drag_menu_id ? this.select_drag_menu_id : 'move' ); // Ensure Move/Copy selections are set when closing pop-up windows from Jump-To menu, like Add Request.
	}

	enableAddRequestButton() {
		var schedules = [];
		//var grid_selected_id_array = this.getGridSelectIdArray();
		if ( !this.select_cellls_and_shifts_array ) {
			return false;
		}
		var grid_selected_id_array = this.select_cellls_and_shifts_array;
		var grid_selected_length = grid_selected_id_array.length;

		if ( grid_selected_length == 1 ) {
			return true;
		}
		if ( grid_selected_length == 0 ) {
			return false;
		}

		var schedules = this.select_cells_Array;
		var first = schedules[0];

		for ( var n = 1; n < schedules.length; n++ ) {
			if ( schedules[n].user_id && first.user_id != schedules[n].user_id ) {
				Debug.Text( 'mismatch on user_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
				return false;
			}

			//do not test blank cells beyond user_id
			if ( schedules[n].shift == undefined ) {
				continue;
			}

			if ( ( first.shift && schedules[n].shift ) ) {
				if ( first.shift.start_time != schedules[n].shift.start_time ) {
					Debug.Text( 'mismatch on start_time', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}
				if ( first.shift.end_time != schedules[n].shift.end_time ) {
					Debug.Text( 'mismatch on end_time', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}
				if ( first.shift.branch_id != schedules[n].shift.branch_id ) {
					Debug.Text( 'mismatch on branch_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}
				if ( first.shift.department_id != schedules[n].shift.department_id ) {
					Debug.Text( 'mismatch on department_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}
				if ( first.shift.job_id != schedules[n].shift.job_id ) {
					Debug.Text( 'mismatch on job_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}
				if ( first.shift.job_item_id != schedules[n].shift.job_item_id ) {
					Debug.Text( 'mismatch on job_item_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}
				if ( first.shift.punch_tag_id != schedules[n].shift.punch_tag_id ) {
					Debug.Text( 'mismatch on punch_tag_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}

				if ( first.shift.schedule_policy_id != schedules[n].shift.schedule_policy_id ) {
					Debug.Text( 'mismatch on schedule_policy_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}

				if ( first.shift.status_id != schedules[n].shift.status_id ) {
					Debug.Text( 'mismatch on status_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}
				if ( first.shift.status_id == 20 && first.shift.absence_policy_id != schedules[n].shift.absence_policy_id ) {
					Debug.Text( 'mismatch on absence_policy_id', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
					return false;
				}
				//if the first one is a blank in a selection that includes shifts, we need to update the compared record to one with a shift.
			} else if ( first.shift == undefined && schedules[n].shift ) {
				first = schedules[n];
			}
		}

		Debug.Text( 'All Selected Schedules Match', 'ScheduleViewController.js', 'ScheduleViewController', 'enableAddRequestButton', 10 );
		return true;
	}

	setAutoPunchIcon( context_btn, grid_selected_length ) {
		if ( grid_selected_length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuAutoPunchIcon( context_btn, grid_selected_length ) {
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
	}

	setEditMenuAddRequestIcon( context_btn, grid_selected_length ) {
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
	}

	setEditMenuFindAvailableIcon( context_btn, grid_selected_length ) {
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
	}

	setDefaultMenuFindAvailabletIcon( context_btn, grid_selected_length ) {
		if ( !this.editChildPermissionValidate() ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length >= 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuEditTimesheetIcon( context_btn, grid_selected_length ) {

		if ( this.select_cells_Array.length === 1 && TTUUID.isUUID( this.select_cells_Array[0].user_id ) && this.select_cells_Array[0].user_id != TTUUID.zero_id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuImportIcon( context_btn, grid_selected_length, pId ) {
		if ( PermissionManager.checkTopLevelPermission( 'ImportCSVSchedule' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length ) {

		if ( !this.editChildPermissionValidate( 'user' ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( this.select_cells_Array.length === 1 && TTUUID.isUUID( this.select_cells_Array[0].user_id ) && this.select_cells_Array[0].user_id != TTUUID.zero_id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onUpdateLayout() {

		var selectId = $( this.previous_saved_layout_selector ).children( 'option:selected' ).attr( 'value' );
		var layout_name = $( this.previous_saved_layout_selector ).children( 'option:selected' ).text();

		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data =   Global.convertLayoutFilterToAPIFilter( { data: { filter_data:  this.getValidSearchFilter() } } );

		var args = {};
		args.id = selectId;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.mode = Global.upCaseFirstLetter( this.getMode() );
		args.data.strictRange = this.settings_menu.getButton( 'strict_range_btn' ).active;
		args.data.weeklyTotals = this.settings_menu.getButton( 'weekly_totals_btn' ).active;
		args.data.dailyTotals = this.settings_menu.getButton( 'daily_totals_btn' ).active;
		args.data.showAllEmp = this.settings_menu.getButton( 'all_employee_btn' ).active;

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

		if ( Global.isSet( default_layout_name ) ) {
			var layout_name = default_layout_name;
		} else {
			layout_name = this.save_search_as_input.getValue();
		}

		if ( !layout_name || layout_name.length < 1 ) {
			return;
		}

		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data =  Global.convertLayoutFilterToAPIFilter( { data: { filter_data: this.getValidSearchFilter() } } );

		var args = {};
		args.script = this.script_name;
		args.name = layout_name;
		args.is_default = false;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.mode = Global.upCaseFirstLetter( this.getMode() );
		args.data.strictRange = this.settings_menu.getButton( 'strict_range_btn' ).active;
		args.data.weeklyTotals = this.settings_menu.getButton( 'weekly_totals_btn' ).active;
		args.data.dailyTotals = this.settings_menu.getButton( 'daily_totals_btn' ).active;
		args.data.showAllEmp = this.settings_menu.getButton( 'all_employee_btn' ).active;

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

	onClearSearch() {
		var do_update = false;
		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			var default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains(\'' + BaseViewController.default_layout_name + '\')' ).attr( 'value' );
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

			this.column_selector.setSelectGridData( this.default_display_columns );

			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;

		}

		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data =  Global.convertLayoutFilterToAPIFilter( { data: { filter_data: this.getValidSearchFilter() } } );

		if ( do_update ) {
			var args = {};
			args.id = default_layout_id;
			args.data = {};
			args.data.display_columns = selected_display_columns;
			args.data.filter_data = filter_data;
		}

		args.data.mode = this.getMode();

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

	getSearchPanelDisplayColumns() {
		var display_columns = [];

		var select_items = this.column_selector.getSelectItems();

		if ( select_items && select_items.length > 0 ) {
			$.each( select_items, function( index, content ) {
				display_columns.push( content.value );
			} );
		}

		return display_columns;
	}

	onSearch( save_only ) {
		this.temp_adv_filter_data = null;
		this.temp_basic_filter_data = null;

		this.getSearchPanelFilter();

		if ( this.search_panel.getLayoutsArray() && this.search_panel.getLayoutsArray().length > 0 ) {
			var default_layout_id = $( this.previous_saved_layout_selector ).children( 'option:contains(\'' + BaseViewController.default_layout_name + '\')' ).attr( 'value' );

			if ( !default_layout_id ) {
				this.onSaveNewLayout( BaseViewController.default_layout_name );
				return;
			}
			var layout_name = BaseViewController.default_layout_name;

		} else {
			this.onSaveNewLayout( BaseViewController.default_layout_name );
			return;
		}

		var selected_display_columns = this.getSearchPanelDisplayColumns();
		var filter_data =  Global.convertLayoutFilterToAPIFilter( { data: { filter_data: this.getValidSearchFilter() } } );

		var args = {};
		args.id = default_layout_id;
		args.data = {};
		args.data.display_columns = selected_display_columns;
		args.data.filter_data = filter_data;
		args.data.mode = Global.upCaseFirstLetter( this.getMode() );
		args.data.strictRange = this.settings_menu.getButton( 'strict_range_btn' ).active;
		args.data.weeklyTotals = this.settings_menu.getButton( 'weekly_totals_btn' ).active;
		args.data.dailyTotals = this.settings_menu.getButton( 'daily_totals_btn' ).active;
		args.data.showAllEmp = this.settings_menu.getButton( 'all_employee_btn' ).active;

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

	setSelectLayout() {

		var $this = this;

		if ( !this.select_layout ) { //Set to defalt layout if no layout at all
			this.select_layout = { id: '' };
			this.select_layout.data = { filter_data: {}, filter_sort: {} };
			this.select_layout.data.display_columns = this.default_display_columns;
		}

		var layout_data = this.select_layout.data;

		var display_columns = this.buildDisplayColumns( layout_data.display_columns );

		if ( Global.isSet( layout_data.mode ) && layout_data.mode != '' ) {
			$this.setToggleButtonValue( layout_data.mode.toLowerCase() );
		} else if ( LocalCacheData.getAllURLArgs() && LocalCacheData.getAllURLArgs().mode ) {
			$this.setToggleButtonValue( LocalCacheData.getAllURLArgs().mode );
		} else {
			$this.setToggleButtonValue( ScheduleViewControllerMode.WEEK );
		}

		if ( layout_data.hasOwnProperty( 'strictRange' ) ) {
			if ( layout_data.strictRange ) {
				this.settings_menu.activateButton( 'strict_range_btn' );
			} else {
				this.settings_menu.deactivateButton( 'strict_range_btn' );
			}
		} else {
			this.settings_menu.activateButton( 'strict_range_btn' );
		}

		if ( layout_data.weeklyTotals ) {
			this.settings_menu.activateButton( 'weekly_totals_btn' );
		} else {
			this.settings_menu.deactivateButton( 'weekly_totals_btn' );
		}

		if ( layout_data.dailyTotals ) {
			this.settings_menu.activateButton( 'daily_totals_btn' );
		} else {
            this.settings_menu.deactivateButton( 'daily_totals_btn' );
        }

		if ( layout_data.showAllEmp ) {
			this.settings_menu.activateButton( 'all_employee_btn' );
		} else {
            this.settings_menu.deactivateButton( 'all_employee_btn' );
        }

		//Set Display Column in layout panel
		this.column_selector.setSelectGridData( display_columns );

		//Set Previoous Saved layout combobox in layout panel
		var layouts_array = this.search_panel.getLayoutsArray();

		this.previous_saved_layout_selector.empty();
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
			this.previous_saved_layout_div.css( 'display', 'none' );
		}

		if ( LocalCacheData.default_filter_for_next_open_view ) {
			this.select_layout.data.filter_data = LocalCacheData.default_filter_for_next_open_view.filter_data;

			this.setDatePickerValue( LocalCacheData.default_filter_for_next_open_view.select_date );

			this.select_layout.data.mode = 'Week';
			$this.setToggleButtonValue( layout_data.mode.toLowerCase() );

			LocalCacheData.default_filter_for_next_open_view = null;
		}

		this.filter_data = this.select_layout.data.filter_data;

		this.setSearchPanelFilter( true );
	}

	getMode() {
		if ( this.toggle_button ) {
			return this.toggle_button.getValue();
		}
	}

	search( setDefaultMenu, use_date_picker_date ) {
		//Issue #3095 - Race condition where a user switches views while the schedule is still loading can cause an exception.
		if ( !( LocalCacheData.current_open_primary_controller.viewId === 'Schedule' || LocalCacheData.current_open_primary_controller.viewId === 'FindAvailable' ) ) {
			return;
		}

		this.clearSelection( setDefaultMenu ); //Clear selection on search, as we aren't re-populating it anyways, and causes a problem if you select 2 cells, click top-right refresh icon, then click New icon, it thinks the selection still exists.
		this.setActionsButtonStatus();
		this.final_schedule_data_array = [];

		var $this = this;
		var filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		var start_date_string;

		var mode = this.getMode();

		if ( this.settings_menu.getButton( 'all_employee_btn' ).active ) {
			filter_data.include_all_users = true;
		}

		var strict = this.settings_menu.getButton( 'strict_range_btn' ).active;

		if ( use_date_picker_date || !this.end_date ) {
			if ( this.start_date === null || use_date_picker_date ) {
				start_date_string = this.start_date_picker.getValue();
			} else {
				start_date_string = $this.start_date.format();
			}

			if ( mode === ScheduleViewControllerMode.YEAR ) {
				this.year_mode_original_date = start_date_string;
			}
		} else {
			if ( strict ) {
				if ( mode === ScheduleViewControllerMode.YEAR ) {
					start_date_string = this.year_mode_original_date ? this.year_mode_original_date : this.start_date_picker.getValue();
				} else if ( mode === ScheduleViewControllerMode.MONTH ) {
					start_date_string = new Date( new Date( $this.end_date.getTime() ).setDate( $this.end_date.getDate() - 15 ) ).format();
				} else {
					start_date_string = $this.end_date.format();
				}
			} else {
				start_date_string = $this.start_date.format();
			}
		}

		if ( mode === null || mode == '' || start_date_string == '' || start_date_string == 'Invalid date' ) {
			//Triggering an error report to hopefully gather more information on what is happening to cause this.
			//Used to cause: PHP ERROR - WARNING(2): Undefined variable $start_date
			Global.sendErrorReport( 'ScheduleViewController invalid mode/date detected.' );
			TAlertManager.showAlert( $.i18n._( 'Invalid schedule mode or date. Please try again.' ) );
		} else {
			LocalCacheData.last_schedule_selected_date = start_date_string;

			ProgressBar.showOverlay();
			this.api.getCombinedSchedule( { filter_data: filter_data }, start_date_string, mode, strict, {
				onResult: function( result ) {
					//Issue #3095 - Race condition where a user switches views while the schedule is still loading can cause an exception.
					if ( !( LocalCacheData.current_open_primary_controller.viewId === 'Schedule' || LocalCacheData.current_open_primary_controller.viewId === 'FindAvailable' ) ) {
						return;
					}
					$this.full_schedule_data = result.getResult();

					//Error: Unable to get property 'start_display_date' of undefined or null reference in /interface/html5/ line 3805
					if ( $this.full_schedule_data === true || !$this.full_schedule_data || !$this.full_schedule_data.schedule_dates ) {
						return;
					}
					$this.start_date = Global.strToDate( $this.full_schedule_data.schedule_dates.start_display_date );
					$this.end_date = Global.strToDate( $this.full_schedule_data.schedule_dates.end_display_date );

					$this.buildCalendars();

					if ( setDefaultMenu ) {
						$this.setDefaultMenu( true );
						$this.autoOpenEditViewIfNecessary();
					}

					$this.searchDone();

					$this.setWeekModeDragAble();

				}
			} );
		}
	}

	searchDone() {
		// this.setGridColumnsWidth();
		// this.setGridSize();
		$( '.button-rotate' ).removeClass( 'button-rotate' );
		TTPromise.resolve( 'init', 'init' );
	}

	getLastDateOfRow( row ) {
		var start_day = LocalCacheData.getLoginUserPreference().start_week_day == 0 ? 7 : LocalCacheData.getLoginUserPreference().start_week_day;
		return row[( start_day - 1 ) + '_time'];
	}

	setActionsButtonStatus() {
		if ( !this.settings_menu ) {
			return;
		}

		var mode = this.getMode();

		this.settings_menu.enableButton( 'weekly_totals_btn' );
		this.settings_menu.enableButton( 'strict_range_btn' );
		this.settings_menu.enableButton( 'daily_totals_btn' );
		this.settings_menu.enableButton( 'all_employee_btn' );

		switch ( mode ) {
			case ScheduleViewControllerMode.DAY:
				this.settings_menu.disableButton( 'weekly_totals_btn' );
				this.settings_menu.disableButton( 'strict_range_btn' );
				break;
			case ScheduleViewControllerMode.WEEK:
				break;
			case ScheduleViewControllerMode.MONTH:
				break;
			case ScheduleViewControllerMode.YEAR:
				this.settings_menu.disableButton( 'weekly_totals_btn' );
				this.settings_menu.disableButton( 'daily_totals_btn' );
				break;
		}
	}

	onOverrideClick() {
		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var override_icon = context_menu_array.find( icon => icon.id === 'override' );

		if ( override_icon.active ) {
			this.is_override = true;
		} else {
			this.is_override = false;
		}
	}

	onNotifyUserScheduleChangeClick() {
		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var notify_user_schedule_change_icon = context_menu_array.find( icon => icon.id === 'notify_user_schedule_change' );

		if ( notify_user_schedule_change_icon.active ) {
			this.notify_user_schedule_change = true;
		} else {
			this.notify_user_schedule_change = false;
		}
	}

	setMoveOrDropMode( id ) {
		//Drag and drop context menu does not exist on edit view and thus this code does not need to be ran. Otherwise would cause JavaScript exception.
		if ( this.is_edit || this.is_mass_editing || this.is_add ) {
			return;
		}

		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var drag_copy_icon = context_menu_array.find( icon => icon.id === 'drag_copy' );
		var move_icon = context_menu_array.find( icon => icon.id === 'move' );
		var swap_icon = context_menu_array.find( icon => icon.id === 'swap' );

		if ( drag_copy_icon === undefined || move_icon === undefined || swap_icon === undefined ) {
			return;
		}

		ContextMenuManager.activateMenuItem( this.determineContextMenuMountAttributes().id, drag_copy_icon.id, true );
		ContextMenuManager.activateMenuItem( this.determineContextMenuMountAttributes().id, move_icon.id, true );
		ContextMenuManager.activateMenuItem( this.determineContextMenuMountAttributes().id, swap_icon.id, true );

		var drag_invisible = false;
		var move_invisible = false;
		var swap_invisible = false;

		if ( !this.copyPermissionValidate() ) {
			drag_invisible = true;
		}

		if ( !this.movePermissionValidate() ) {
			move_invisible = true;
		}

		if ( !this.editPermissionValidate() ) {
			swap_invisible = true;
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

	setHolidayDataDic() {

		if ( this.full_schedule_data.holiday_data ) {
			for ( var i = 0; i < this.full_schedule_data.holiday_data.length; i++ ) {
				var item = this.full_schedule_data.holiday_data[i];
				var standard_date = Global.strToDate( item.date_stamp ).format( this.weekly_format );
				this.holiday_data_dic[standard_date] = item;
			}
		}
	}

	buildCalendars( do_not_hide ) {
		var $this = this;
		this.grid_div = $( this.el ).find( '.schedule-grid-div' );
		this.setHolidayDataDic();

		this.buildScheduleColumns();
		this.buildScheduleSource();
		this.buildScheduleGrid();

		this.setGridColumnsWidth(); //There is no grid data populated when this runs, so it only sizes columns to the label length, not longest row data length.
		this.setGridSize();

		//Only work when year mode
		this.setYearGroupHeader();
		this.showGridBorders();

		var start = 0;
		var page = 1;
		var page_num = 10;

		//this.grid will be empty when first time int this function, so put this judge here instead at begin.
		//Error: Uncaught TypeError: Cannot call method 'clearGridData' of null in /interface/html5/index.php?desktop=1#!m=Schedule&date=20150118&mode=week line 6944
		if ( !this.grid ) {
			return;
		}

		this.grid.clearGridData();

		var j = 0;

		if ( !do_not_hide ) {
			this.grid.grid.css( 'opacity', 0 );
		}

		//Error: TypeError: $this.schedule_source is undefined in interface/html5/framework/jquery.min.js?v=9.0.6-20151231-155042 line 2 > eval line 3904
		if ( !this.schedule_source ) {
			return;
		}
		var $this = this;
		addGridData();

		// Add 200 record a time so UI not block.
		var interval = setInterval( function() {
			if ( j < $this.schedule_source.length ) {
				addGridData();
			} else {
				doNext();
			}
		}, 10 );

		function addGridData() {
			for ( var i = j; i < j + 200; i++ ) {
				if ( i < $this.schedule_source.length ) {
					var item = $this.schedule_source[i];
					$this.grid.grid.addRowData( i + 1, item );
				} else {
					break;
				}
			}

			j = i;
		}

		function doNext() {
			if ( !do_not_hide ) {
				$this.grid.grid.css( 'opacity', 1 );
			}
			clearInterval( interval );

			if ( $this.getMode !== ScheduleViewControllerMode.YEAR ) {
				$this.setScheduleGridRowSpan();
			}

			$this.highLightSelectDay();

			//Only work when month mode
			$this.setMonthDateRowPosition();

			$this.setScheduleGridDragAble();

			$this.setScrollPosition();

			//$this.autoOpenEditViewIfNecessary();

			$this.setMonthDateRowBackGround();

			$this.setWeeklyTotalHeader();
		}
	}

	getDefaultUser() {
		var default_user_id = false;
		if ( this.schedule_source && this.schedule_source.length === 1 && this.schedule_source[0].user_id != '' ) {
			//case where only one user has a schedule on the sheet
			default_user_id = this.schedule_source[0].user_id;
		} else if ( this.schedule_source
			&& this.schedule_source.length === 1
			&& typeof this.filter_data == 'object' // #2571 - Uncaught TypeError: This.filter_data.include_user_id is undefined
			&& typeof this.filter_data.include_user_ids == 'object' // #2571 - Uncaught TypeError: Cannot read property 'value' of undefined
			&& this.filter_data.include_user_ids.value
			&& this.filter_data.include_user_ids.value.length === 1 ) {
			//case where one user is selected in include_users but does not have a schedule attributed to them (new users for example)
			default_user_id = this.filter_data.include_user_ids.value[0];
		} else {
			// #2740 Before defaulting to logged in user, check to see if user_id is set on the current_edit_record, as Save&Copy clears grid selection, so we need to re-set it.
			if ( this.current_edit_record.user_id && this.current_edit_record.user_id != '' ) {
				default_user_id = this.current_edit_record.user_id;
			} else {
				default_user_id = LocalCacheData.getLoginUser().id;
			}
		}

		return default_user_id;
	}

	setWeeklyTotalHeader() {
		var show_weekly_total = this.settings_menu.getButton( 'weekly_totals_btn' ).active;
		$( '.size-tr' ).remove();
		$( '.group-tr' ).remove();
		if ( !show_weekly_total || this.settings_menu.getButton( 'weekly_totals_btn' ).disabled === true ) {
			return;
		}

		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' ) );

		var size_tr = $( '<tr class="size-tr" >' +
			'</tr>' );

		var new_tr = $( '<tr class="group-column-tr group-tr" >' +
			'</tr>' );

		var new_th = $( '<th class="group-column-th"  >' +
			'<span class="group-column-label"></span>' +
			'</th>' );

		var current_trs = table.find( '.jqgfirstrow' );
		createSizeColumns();

		var column_length = this.grid.grid.getGridParam( 'colModel' ).length;
		createColumn( column_length - 5, '' );
		createColumn( 5, $.i18n._( 'Total' ) );

		size_tr.insertBefore( table.find( '.ui-jqgrid-labels' ) );
		new_tr.insertBefore( table.find( '.ui-jqgrid-labels' ) );

		function createSizeColumns() {
			var len = current_trs.children().length;

			for ( var i = 0; i < len; i++ ) {
				var td = $( '<th class="" style="border-right: 1px solid #dddddd" >' + '</th>' );
				var item = current_trs.children().eq( i );

				/**
				 * #2353 - schedule sizing fix
				 *
				 * Due to firefox reporting th  incorrectly via the $.width() function, the sizes must come from the (tr.jqgfirstrow) of the data table
				 * firefox also refuses to set the width of the first row of th's via the $.width() function, so we need to ship the css values directly into the inline css using the css function
				 */
				td.css( 'width', item.css( 'width' ) );

				td.height( 0 );
				size_tr.append( td );
			}

		}

		function createColumn( end_index, text ) {
			var pay_period_th = new_th.clone();

			pay_period_th.children( 0 ).text( text );
			pay_period_th.attr( 'colspan', end_index );

			new_tr.append( pay_period_th );
		}

		this.setGridHeight(); //Since we are changing the header height, resize the grid to fit.
	}

	setMonthDateRowBackGround() {
		if ( this.getMode() === ScheduleViewControllerMode.MONTH ) {
			$( this.el ).find( '.month-date-cell' ).parent().css( 'background-color', '#375979' );
		}
	}

	setScrollPosition() {
		if ( this.scroll_position > 0 ) {
			this.grid.grid.parent().parent().scrollTop( this.scroll_position );
		}
	}

	setYearGroupHeader() {

		if ( this.getMode() !== ScheduleViewControllerMode.YEAR ) {
			return;
		}

		$( '.schedule-year-group-header' ).remove();
		var table = $( $( this.el ).find( 'table[aria-labelledby=gbox_' + this.ui_id + '_grid]' )[0] );
		var new_tr = $( '<tr class="group-column-tr schedule-year-group-header" >' +
			'</tr>' );

		var new_th = $( '<th class="group-column-th"  >' +
			'<span class="group-column-label"></span>' +
			'</th>' );

		var default_tr = new_tr.clone();

		$( table.children()[0] ).prepend( default_tr );

		//Build first row to correct width for span columns which in second row, table width decided by first row
		var datesTHs = table.find( 'th' );
		var len = datesTHs.length;
		for ( var i = 0; i < len; i++ ) {
			var th = $( datesTHs[i] );

			var default_th = th.clone();
			default_th.attr( 'id', '' );
			default_th.empty();
			default_th.attr( 'row', '' );
			default_th.height( 0 );
			default_tr.append( default_th );

		}

		var first_tr = $( $( table.children()[0] ).children()[0] );

		var start = this.select_layout.data.display_columns.length + 1;
		//Create group column header
		default_tr = new_tr.clone();
		default_th = new_th.clone();
		default_th.attr( 'colspan', start );
		default_tr.append( default_th );
		default_tr.insertAfter( first_tr );

		var current_month = null;
		var current_date = null;
		var same_month_count = 0;

		for ( var i = start; i < len; i++ ) {
			th = $( datesTHs[i] );
			var id_split_array = th.attr( 'id' ).split( '_' );
			var date_str = id_split_array[id_split_array.length - 1];

			if ( date_str === 'shifts' || date_str === 'absences' || date_str === 'total_time' || date_str === 'total_time_wage' ) {
				var month = '-1';
			} else {
				month = Global.strToDate( date_str, this.full_format ).getMonth();
			}

			if ( !Global.isSet( current_month ) ) {
				current_month = month;
				current_date = date_str;
				same_month_count = 1;

			} else {
				var month_header_text = Global.strToDate( current_date, this.full_format ).format( 'MMM' );
				if ( month === current_month && i !== len - 1 ) {
					same_month_count = same_month_count + 1;

				} else {

					if ( i === len - 1 ) {
						if ( month !== current_month && !isNaN( month ) ) {
							default_th.children( 0 ).text( month_header_text );
							default_th.attr( 'colspan', same_month_count );
							default_tr.append( default_th );

							current_month = month;
							current_date = date_str;
							same_month_count = 1;

							default_th = new_th.clone();
							default_th.children( 0 ).text( month_header_text );
							default_th.attr( 'colspan', same_month_count );
							default_tr.append( default_th );

						} else {
							same_month_count = same_month_count + 1;

							default_th = new_th.clone();
							default_th.children( 0 ).text( month_header_text );
							default_th.attr( 'colspan', same_month_count );
							default_tr.append( default_th );

						}
					} else {
						default_th = new_th.clone();
						default_th.children( 0 ).text( month_header_text );
						default_th.attr( 'colspan', same_month_count );
						default_tr.append( default_th );

						current_month = month;
						current_date = date_str;
						same_month_count = 1;
					}

					if ( month === '-1' ) {
						default_th = new_th.clone();
						default_th.children( 0 ).text( '' );
						default_th.attr( 'colspan', len - i );
						default_tr.append( default_th );
						break;
					}

				}
			}

		}
	}

	setMonthDateRowPosition() {

		if ( this.getMode() !== ScheduleViewControllerMode.MONTH ) {
			return;
		}

		var $this = this;
		this.month_date_row_position = {};
		var i = 0;
		for ( var key in this.month_date_row_tr_ids ) {
			this.month_date_row_position[i] = this.grid.grid.find( '#' + key ).position().top;
			i = i + 1;
		}

		this.grid.grid.parent().parent().scroll( function() {
			var top = $( this ).scrollTop();

			var start_day = $this.start_date.getDay();

			if ( top < $this.month_date_row_position[0] && $this.month_current_header_number !== 0 ) {

				//Deal with header before first date row.
				$this.month_date_row_tr_ids = {};
				$this.month_current_header_number = 0;
				for ( var i = 0; i < 7; i++ ) {
					var current_date = new Date( new Date( $this.start_date.getTime() ).setDate( $this.start_date.getDate() + i ) );
					var header_text = current_date.format( $this.weekly_format );
					var header_container = $( $this.el ).find( '#' + $this.ui_id + '_grid_' + data_field );
					var data_field = ( ( start_day + i ) % 7 );
					var header = $( $this.el ).find( '#jqgh_' + $this.ui_id + '_grid_' + data_field );
					header.text( header_text );
					header_container.removeClass( 'highlight-header' );

				}

				$this.highLightSelectDay();

			} else if ( top > $this.month_date_row_position[0] && top < $this.month_date_row_position[1] && $this.month_current_header_number !== 1 ) {
				setHeaderText( 0, 1 );

			} else if ( top > $this.month_date_row_position[1] && top < $this.month_date_row_position[2] && $this.month_current_header_number !== 2 ) {
				setHeaderText( 1, 2 );
			} else if ( top > $this.month_date_row_position[2] && top < $this.month_date_row_position[3] && $this.month_current_header_number !== 3 ) {
				setHeaderText( 2, 3 );
			} else if ( top > $this.month_date_row_position[3] && $this.month_current_header_number !== 4 ) {
				setHeaderText( 3, 4 );
			}

			function setHeaderText( index, headerNumber ) {
				$this.month_date_row_tr_ids = {};
				$this.month_current_header_number = headerNumber;
				var date_row = $this.month_date_row_array[index];
				for ( var i = 0; i < 7; i++ ) {
					var data_field = ( ( start_day + i ) % 7 );
					var header = $( $this.el ).find( '#jqgh_' + $this.ui_id + '_grid_' + data_field );
					var header_container = $( $this.el ).find( '#' + $this.ui_id + '_grid_' + data_field );
					header_container.removeClass( 'highlight-header' );
					var full_date = date_row[data_field + '_full_date'];
					var date_cell = $( $this.el ).find( '#' + $this.ui_id + '_grid_' + full_date );

					if ( date_cell.hasClass( 'highlight-header' ) ) {
						header_container.addClass( 'highlight-header' );
					}
					header.html( date_row[data_field] );

				}
			}

		} );
	}

	setGridHeight() {
		let scroll_height = document.body.scrollHeight;

		let height = scroll_height;

		let table_body = document.querySelector( 'div.ui-jqgrid-bdiv.ui-corner-bottom' );
		let context_border = document.querySelector( 'div.context-border' );
		//Issue #3060 - JavaScript exceptions of trying to read getBoundingClientRect of null elements.
		if ( !table_body || !context_border ) {
			return scroll_height;
		}

		height -= table_body.getBoundingClientRect().top;
		height -= ( scroll_height - context_border.getBoundingClientRect().bottom );
		height -= 10; //Manual fine tuning to fit better.

		if ( this.getMode() != ScheduleViewControllerMode.DAY ) {
			var grid_div = $( '.schedule-view .grid-div .ui-jqgrid-bdiv' );
			if ( grid_div && grid_div.length > 0 && grid_div[0].scrollWidth > ( $( '.context-border' ).width() + 2 ) ) { //this plus 2 is because sometimes the scroll width is larger than the view width with no scrollbar
				height -= Global.getScrollbarWidth(); //scrollbar compensation
			}
		}

		this.grid.setGridHeight( height );
		return height;
	}

	setGridColumnsWidth() {
		var $this = this;
		switch ( this.getMode() ) {
			case ScheduleViewControllerMode.DAY:
				var day_column_index;

				//Calculate the exact width of each column that isn't where the shift times are displayed.
				//Then any remaining width can be allocated to the shift times column.
				var column_padding = 10;
				//var grid_data = this.grid.getData();
				var grid_data = $this.schedule_source; //Must use the original source data, as the this.grid.getData() is not populated yet.
				for ( var i in this.schedule_columns ) {
					if ( this.schedule_columns[i].is_static_size == true || this.schedule_columns[i].name == 'scrollbar_spacer' ) {
						this.schedule_columns[i].fixed = true;
						if ( this.schedule_columns[i].name != 'scrollbar_spacer' ) {
							day_column_index = i;
						}
					} else {
						//size column based on text.
						var column_width = Global.calculateTextWidth( this.schedule_columns[i].label ) + column_padding;

						if ( grid_data && grid_data.length > 0 ) {
							for ( var row in grid_data ) {
								var new_col_width = Global.calculateTextWidth( grid_data[row][this.schedule_columns[i].name] );
								if ( new_col_width > column_width ) {
									column_width = new_col_width;
								}
							}
						}

						this.schedule_columns[i].width = column_width;
						this.schedule_columns[i].fixed = true;
					}
				}

				this.calculateScheduleWidth();
				$( '.day_hour_div .day_hour_span' ).width( this.day_hour_width );
				var day_column_width = ( this.day_hour_width * $( '.day_hour_div .day_hour_span' ).length ); // 60 is desired width of each day span

				this.schedule_columns[day_column_index].width = day_column_width;

				this.grid.setGridColumnsWidth( this.schedule_columns );
				break;
			default:
				var column_padding = 15; //Slightly larger padding eliminates a problem with the YEAR mode schedule being off the screen when the employee name is long like: "Franklin McMichaels"
				var columns_width = 0;

				//Day columns must always be the same width, only make the employee name and other static columns variable width.
				var max_width = $( '.context-border' ).width();

				var grid_data = $this.schedule_source; //Must use the original source data, as the this.grid.getData() is not populated yet.
				for ( var i in this.schedule_columns ) {
					if ( !this.schedule_columns[i].is_static_size || this.schedule_columns[i].is_static_size == false ) {
						//size column based on text.
						var column_width = Global.calculateTextWidth( this.schedule_columns[i].label, { padding: column_padding } );

						if ( grid_data && grid_data.length > 0 ) {
							var label_cache = [];

							for ( var row in grid_data ) {
								var label = grid_data[row][this.schedule_columns[i].name];
								if ( !label_cache[label]) { //Cache labels we have already calculated the size on, so we don't do it over and over again.
									var new_col_width = Global.calculateTextWidth( label, { padding: column_padding } );
									if ( new_col_width > column_width ) {
										column_width = new_col_width;
									}
									label_cache[label] = true;
								}
							}

							label_cache = null;
						}

						this.schedule_columns[i].width = column_width;
						this.schedule_columns[i].fixed = true;
					}

					columns_width += this.schedule_columns[i].width;
				}

				//Resize first column to fill screen.
				if ( this.schedule_columns[0] && columns_width < max_width ) {
					this.schedule_columns[0].width += ( max_width - columns_width );
				}

				this.grid.setGridColumnsWidth( this.schedule_columns );

				if ( this.getMode() == ScheduleViewControllerMode.YEAR ) {
					this.setYearGroupHeader(); //Must go after the column widths are changed.
				}

				break;
		}

		this.setGridHeight();
	}

	buildScheduleSource() {
		this.no_date_array = [];
		this.has_date_array = [];
		this.final_schedule_data_array = [];
		var mode = this.getMode();

		this.no_date_array = this.buildNoDateArray();
		this.has_date_array = this.buildHasDateArray();
		var has_date_temp_array_length = this.has_date_array.length;
		var sort_fields = this.buildSortFields();
		this.has_date_array.sort( Global.m_sort_by( sort_fields ) );
		this.no_date_array.sort( Global.m_sort_by( sort_fields ) );

		if ( mode !== ScheduleViewControllerMode.MONTH ) {
			this.final_schedule_data_array = this.no_date_array.concat( this.has_date_array );
		}

		switch ( this.getMode() ) {
			case ScheduleViewControllerMode.WEEK:
				this.buildWeeklySource();
				break;
			case ScheduleViewControllerMode.MONTH:
				this.buildMonthSource();
				break;
			case ScheduleViewControllerMode.YEAR:
				this.buildYearlySource();
				break;
			case ScheduleViewControllerMode.DAY:
				this.buildDailySource();
				break;
		}
	}

	buildShiftKey( shift ) {

		var key = '';
		for ( var i = 0; i < this.shift_key_name_array.length; i++ ) {
			var field_name = this.shift_key_name_array[i];
			var column_name = this.shift_key_name_array[i].replace( '_id', '' ); //judge if shit has correct field value
			if ( column_name !== 'user' ) {
				if ( shift[column_name] ) {
					key = shift[field_name] + '-' + key;
				} else {
					key = 0 + '-' + key;
				}
			} else {
				key = shift[field_name] + '-' + key;
			}
		}
		return key;
	}

	buildMonthSource() {
		var $this = this;
		var date_row_index = 0;
		var month_week_data_index = 0;
		var date_row = this.month_date_row_array[date_row_index];
		var start_day = this.start_date.getDay(); //start from first date row, not include column
		var first_day_time = date_row[start_day + '_time'];
		var month_week_data_array = [];
		var has_date_array = this.has_date_array.slice();
		for ( var j = 0; j < 5; j++ ) {
			var current_week_array = [];
			var len = has_date_array.length;
			var is_last_row = false;
			for ( var i = 0; i < len; i++ ) {

				var shift = has_date_array[i];
				var date = Global.strToDate( shift.date_stamp );
				var time = date.getTime();

				if ( time < first_day_time ) {

					has_date_array.splice( i, 1 );

					i = i - 1;
					len = len - 1;

					current_week_array.push( shift );
				}
			}

			if ( this.settings_menu.getButton( 'all_employee_btn' ).active ) {
				if ( j === 0 ) {
					current_week_array = this.no_date_array.slice().concat( current_week_array );
				} else {
					// only first week empty users are comming from API, calculate all other weeks data
					var no_date_array = this.buildMonthWeekNoDateArray( current_week_array );
					current_week_array = no_date_array.slice().concat( current_week_array );
				}
			}

			month_week_data_array[month_week_data_index] = current_week_array;
			if ( date_row_index > 2 ) {
				is_last_row = true;
			} else if ( date_row_index === 2 && !this.month_date_row_array[3].hasOwnProperty( 0 ) ) {
				is_last_row = true;
			}
			if ( !is_last_row ) {
				date_row_index = date_row_index + 1;
				month_week_data_index = month_week_data_index + 1;
				date_row = this.month_date_row_array[date_row_index];
				first_day_time = date_row[start_day + '_time'];
			} else {
				month_week_data_index = month_week_data_index + 1;

				//Don't use this.end_date because the end date may larger than the last day of this month. Use the last date in date row
				var end_date_time = this.getLastDateOfRow( this.month_date_row_array[date_row_index] );
				var end_date = new Date( end_date_time );
				first_day_time = new Date( end_date.setDate( end_date.getDate() + 1 ) ).getTime();
			}
		}

		buildMonthWeeklyData( -1, month_week_data_array[0] );
		buildMonthWeeklyData( 0, month_week_data_array[1] );
		buildMonthWeeklyData( 1, month_week_data_array[2] );
		buildMonthWeeklyData( 2, month_week_data_array[3] );
		buildMonthWeeklyData( 3, month_week_data_array[4] );

		function buildMonthWeeklyData( rowIndex, source_array ) {
			if ( source_array.length < 1 ) {
				buildEmptyRow( rowIndex );
				return;
			}
			var map = {};
			var len = source_array.length;
			var push_to_last = false;

			if ( rowIndex === $this.month_date_row_array.length - 1 ) {
				push_to_last = true;
			} else {
				var date_row = $this.month_date_row_array[rowIndex + 1];
				var index = 0;
			}

			for ( var i = 0; i < len; i++ ) {
				var shift = source_array[i];
				var date_string;
				if ( shift.date_stamp ) {
					var date = Global.strToDate( shift.date_stamp );

					date_string = date.getDay();
				}

				var key = $this.buildShiftKey( shift );

				// each row of schedule data, start from first row
				if ( !map[key] ) {
					var row = {};
					row.user_full_name = shift.user_full_name;
					row.last_name = shift.last_name;
					row.user_id = shift.user_id;
					row.branch_id = shift.branch_id;
					row.department_id = shift.department_id;
					row.schedule_policy_id = shift.schedule_policy_id;
					row.job_id = shift.job_id;
					row.job_item_id = shift.job_item_id;
					row.punch_tag_id = shift.punch_tag_id;

					var display_columns = $this.select_layout.data.display_columns;
					var display_columns_len = display_columns.length;

					for ( var j = 0; j < display_columns_len; j++ ) {
						var field_name = display_columns[j];
						row[field_name] = shift[field_name] ? shift[field_name] : '';
					}

					if ( date_string >= 0 ) {
						if ( shift.status_id == 20 ) {

							row[date_string] = $this.getAbsenceCellValue( shift );
						} else {
							row[date_string] = shift.start_time + ' - ' + shift.end_time;
						}
						row[date_string + '_data'] = shift;
					}

					if ( !push_to_last ) {
						index = $this.schedule_source.indexOf( date_row );
						$this.schedule_source.splice( index, 0, row );
						map[key] = [index];
					} else {
						$this.schedule_source.push( row );
						map[key] = [$this.schedule_source.length - 1];
					}

				} else {
					// if one row already created, go to here to create cells in this row

					var find_position = false;
					for ( var x = 0; x < map[key].length; x++ ) {
						var row_index = map[key][x];
						row = $this.schedule_source[row_index];
						if ( row[date_string] ) {
							continue;
						} else {

							if ( date_string >= 0 ) {
								if ( shift.status_id == 20 ) {

									row[date_string] = $this.getAbsenceCellValue( shift );
								} else {
									row[date_string] = shift.start_time + ' - ' + shift.end_time;
								}
								row[date_string + '_data'] = shift;
							}

							find_position = true;
							break;
						}
					}

					if ( !find_position ) {
						row = {};
						row.user_full_name = shift.user_full_name;
						row.last_name = shift.last_name;
						row.user_id = shift.user_id;
						row.branch_id = shift.branch_id;
						row.department_id = shift.department_id;
						row.schedule_policy_id = shift.schedule_policy_id;
						row.job_id = shift.job_id;
						row.job_item_id = shift.job_item_id;
						row.punch_tag_id = shift.punch_tag_id;

						display_columns = $this.select_layout.data.display_columns;
						display_columns_len = display_columns.length;

						for ( var j = 0; j < display_columns_len; j++ ) {
							field_name = display_columns[j];
							row[field_name] = shift[field_name] ? shift[field_name] : '';
						}

						if ( date_string >= 0 ) {
							if ( shift.status_id == 20 ) {

								row[date_string] = $this.getAbsenceCellValue( shift );
							} else {
								row[date_string] = shift.start_time + ' - ' + shift.end_time;
							}
							row[date_string + '_data'] = shift;

						}

						if ( !push_to_last ) {
							index = $this.schedule_source.indexOf( date_row );
							$this.schedule_source.splice( index, 0, row );
							map[key].push( index );
						} else {
							$this.schedule_source.push( row );
							map[key].push( $this.schedule_source.length - 1 );
						}

					}

				}

			}
			if ( source_array.length > 0 ) {
				buildEmptyRow( rowIndex, true );
			}
		}

		function buildEmptyRow( date_row_index, add_last ) {
			if ( add_last ) {
				date_row_index = date_row_index + 1;
			}
			if ( date_row_index === -1 ) {
				var index = 0;
			} else {
				var date_row = $this.month_date_row_array[date_row_index];
				if ( add_last ) {
					if ( date_row ) {
						index = $this.schedule_source.indexOf( date_row );
					} else {
						index = $this.schedule_source.length;
					}
				} else {
					index = $this.schedule_source.indexOf( date_row ) + 1;
				}
			}
			var row = $this.getEmptyWeeklyRow();
			row.type = ScheduleViewControllerRowType.EMPTY;
			$this.schedule_source.splice( index, 0, row );
		}

		this.showDailyTotal();
		this.showWeeklyTotal();
	}

	buildYearlySource() {
		var $this = this;
		var map = {};
		this.schedule_source = [];

		var len = this.final_schedule_data_array.length;

		if ( len < 1 ) {
			buildEmptyRow();
			return;
		}

		for ( var i = 0; i < len; i++ ) {
			var shift = this.final_schedule_data_array[i];
			var date_string = '';
			if ( shift.date_stamp ) {
				var date = Global.strToDate( shift.date_stamp );
				date_string = date.format( this.full_format );
			}

			var key = this.buildShiftKey( shift );

			// each row of schedule data, start from first row
			if ( !map[key] ) {
				var row = {};
				row.user_full_name = shift.user_full_name;
				row.last_name = shift.last_name;
				row.user_id = shift.user_id;
				row.branch_id = shift.branch_id;
				row.department_id = shift.department_id;
				row.schedule_policy_id = shift.schedule_policy_id;
				row.job_id = shift.job_id;
				row.job_item_id = shift.job_item_id;
				row.punch_tag_id = shift.punch_tag_id;

				var display_columns = this.select_layout.data.display_columns;
				var display_columns_len = display_columns.length;

				for ( var j = 0; j < display_columns_len; j++ ) {
					var field_name = display_columns[j];
					row[field_name] = shift[field_name] ? shift[field_name] : '';
				}

				if ( date_string ) {
					row[date_string] = shift.status_id == 10 ? 'S' : 'A';
					row[date_string + '_data'] = shift;
				}

				this.schedule_source.push( row );
				map[key] = [this.schedule_source.length - 1];
			} else {

				var find_position = false;
				for ( var x = 0; x < map[key].length; x++ ) {
					var row_index = map[key][x];
					row = this.schedule_source[row_index];
					if ( row[date_string] ) {
						continue;
					} else {

						if ( date_string ) {
							row[date_string] = shift.status_id == 10 ? 'S' : 'A';
							row[date_string + '_data'] = shift;
						}

						find_position = true;
						break;
					}
				}

				if ( !find_position ) {
					row = {};
					row.user_full_name = shift.user_full_name;
					row.last_name = shift.last_name;
					row.user_id = shift.user_id;
					row.branch_id = shift.branch_id;
					row.department_id = shift.department_id;
					row.schedule_policy_id = shift.schedule_policy_id;
					row.job_id = shift.job_id;
					row.job_item_id = shift.job_item_id;
					row.punch_tag_id = shift.punch_tag_id;

					display_columns = this.select_layout.data.display_columns;
					display_columns_len = display_columns.length;

					for ( var j = 0; j < display_columns_len; j++ ) {
						field_name = display_columns[j];
						row[field_name] = shift[field_name] ? shift[field_name] : '';
					}

					if ( date_string ) {
						row[date_string] = shift.status_id == 10 ? 'S' : 'A';
						row[date_string + '_data'] = shift;
					}

					this.schedule_source.push( row );
					map[key].push( this.schedule_source.length - 1 );
				}

			}

		}

		function buildEmptyRow() {

			var row = {};
			row.user_full_name = '';
			row.last_name = '';
			row.user_id = '';
			row.branch_id = '';
			row.type = ScheduleViewControllerRowType.EMPTY;
			$this.schedule_source.push( row );
		}

		this.showWeeklyTotal();
	}

	buildDailyHeaders() {
		var $this = this;
		var col_model = this.schedule_columns;
		var label_column = col_model[this.select_layout.data.display_columns.length + 1];
		var first_time = -1;
		var last_time = -1;
		var first_date_time = '';
		var last_date_time = '';
		var first_time_str = '';
		var last_time_str = '';
		var len = this.has_date_array.length;
		if ( len === 0 ) {
			var res = this.api.getScheduleDefaultData( { async: false } );
			var data = res.getResult();
			var selected_date_str = $this.getSelectDate();
			first_date_time = selected_date_str + ' ' + data.start_time;
			last_date_time = selected_date_str + ' ' + data.end_time;
			doNext();
			return;
		}
		for ( var i = 0; i < len; i++ ) {
			var item = this.has_date_array[i];

			if ( first_time === -1 ) {
				first_time = item.start_time_stamp;
				first_date_time = item.start_date;
				first_time_str = item.start_time;
			} else if ( item.start_time_stamp < first_time ) {
				first_time = item.start_time_stamp;
				first_date_time = item.start_date;
				first_time_str = item.start_time;
			}

			if ( last_time === -1 ) {
				last_time = item.end_time_stamp;
				last_date_time = item.end_date;
				last_time_str = item.end_time;
			} else if ( item.end_time_stamp > last_time ) {
				last_time = item.end_time_stamp;
				last_date_time = item.end_date;
				last_time_str = item.end_time;
			}
		}

		first_date_time = Global.getStandardDateTimeStr( first_date_time, first_time_str );
		last_date_time = Global.getStandardDateTimeStr( last_date_time, last_time_str );

		doNext();

		function doNext() {
			var current_date_time = new Date( Global.strToDateTime( first_date_time ).getTime() - 3600000 );
			last_date_time = Global.strToDateTime( last_date_time );
			var min = current_date_time.getMinutes() * 60000;
			var time_span = $( '<div class=\'day_hour_span\'></div>' );
			if ( min > 0 ) {
				current_date_time = new Date( current_date_time.getTime() - min );
			}
			$this.day_mode_start_date_time = current_date_time;
			var time_offset = ( last_date_time.getTime() - current_date_time.getTime() ) / 3600000;
			var header_container = $( '<div class=\'day_hour_div\'></div>' );
			var day_column = $( '<div class=\'day-column\'></div>' );
			var day = $this.start_date.format( $this.weekly_format );
			day = $this.setHolidayHeader( day, true );
			day_column.text( day );
			var time_columns = $( '<div style="padding:0px !important"></div>' );
			var time_string = '';

			//var time_format_string = 'hh:mm A';
			// var time_format_string = 'h A';
			var time_format_string = 'hA';
			for ( var i = 0; i < time_offset; i++ ) {
				var current_hour_text = time_span.clone();
				current_hour_text = current_hour_text.text( current_date_time.format( time_format_string ) );
				if ( i < time_offset - 1 ) {
					time_string = time_string + current_date_time.format( time_format_string ) + '|';
				} else {
					time_string = time_string + current_date_time.format( time_format_string );
				}
				time_columns.append( current_hour_text );
				current_date_time = new Date( current_date_time.getTime() + 3600000 );
			}
			header_container.append( day_column );
			header_container.append( time_columns );
			$this.buildTotalShiftDic( time_string );
			label_column.label = header_container[0].outerHTML;
			// Include padding.
			label_column.width = $this.day_hour_width * time_offset + 40;
			$this.day_header_width = label_column.width;

		}
	}

	//Build dic that contains all daily hours
	buildTotalShiftDic( timeHeader ) {
		var label_array = timeHeader.split( '|' );
		this.total_shifts_dic = {};
		for ( var i = 0; i < label_array.length; i++ ) {
			var shift_item = { sort_order: i, key: label_array[i], value: 0 };
			this.total_shifts_dic[label_array[i]] = shift_item;
		}
	}

	buildDailySource() {

		var $this = this;
		this.buildDailyHeaders();

		var map = {};
		this.schedule_source = [];

		var len = this.final_schedule_data_array.length;

		if ( len < 1 ) {
			buildEmptyRow();
			return;
		}

		for ( var i = 0; i < len; i++ ) {
			var shift = this.final_schedule_data_array[i];
			var date_string = '';
			if ( shift.date_stamp ) {
				var date = Global.strToDate( shift.date_stamp );
				date_string = date.format( this.full_format );
			}

			var key = this.buildShiftKey( shift );

			// each row of schedule data, start from first row
			if ( !map[key] ) {
				var row = {};
				row.user_full_name = shift.user_full_name;
				row.last_name = shift.last_name;
				row.user_id = shift.user_id;
				row.branch_id = shift.branch_id;
				row.department_id = shift.department_id;
				row.schedule_policy_id = shift.schedule_policy_id;
				row.job_id = shift.job_id;
				row.job_item_id = shift.job_item_id;
				row.punch_tag_id = shift.punch_tag_id;
				row.total = Global.getTimeUnit( shift.total_time );

				var display_columns = this.select_layout.data.display_columns;
				var display_columns_len = display_columns.length;

				for ( var j = 0; j < display_columns_len; j++ ) {
					var field_name = display_columns[j];
					row[field_name] = shift[field_name] ? shift[field_name] : '';
				}

				if ( date_string ) {
					if ( shift.status_id == 20 ) {

						row[date_string] = $this.getAbsenceCellValue( shift );
					} else {
						row[date_string] = shift.start_time + ' - ' + shift.end_time;
					}
					row[date_string + '_data'] = shift;
				}

				this.schedule_source.push( row );
				map[key] = [this.schedule_source.length - 1];
			} else {
				// if one row already created, go to here to create cells in this row

				var find_position = false;
				for ( var x = 0; x < map[key].length; x++ ) {
					var row_index = map[key][x];
					row = this.schedule_source[row_index];
					if ( row[date_string] ) {
						continue;
					} else {

						if ( date_string ) {
							if ( shift.status_id == 20 ) {

								row[date_string] = $this.getAbsenceCellValue( shift );
							} else {
								row[date_string] = shift.start_time + ' - ' + shift.end_time;
							}
							row[date_string + '_data'] = shift;
						}

						find_position = true;
						break;
					}
				}

				if ( !find_position ) {
					row = {};
					row.user_full_name = shift.user_full_name;
					row.last_name = shift.last_name;
					row.user_id = shift.user_id;
					row.branch_id = shift.branch_id;
					row.department_id = shift.department_id;
					row.schedule_policy_id = shift.schedule_policy_id;
					row.job_id = shift.job_id;
					row.job_item_id = shift.job_item_id;
					row.punch_tag_id = shift.punch_tag_id;
					row.total = Global.getTimeUnit( shift.total_time );

					display_columns = this.select_layout.data.display_columns;
					display_columns_len = display_columns.length;

					for ( var j = 0; j < display_columns_len; j++ ) {
						var field_name = display_columns[j];
						row[field_name] = shift[field_name] ? shift[field_name] : '';
					}

					if ( date_string ) {
						if ( shift.status_id == 20 ) {

							row[date_string] = $this.getAbsenceCellValue( shift );
						} else {
							row[date_string] = shift.start_time + ' - ' + shift.end_time;
						}
						row[date_string + '_data'] = shift;
					}

					this.schedule_source.push( row );
					map[key].push( this.schedule_source.length - 1 );
				}

			}

		}

		function buildEmptyRow() {

			var row = $this.getEmptyWeeklyRow();
			row.type = ScheduleViewControllerRowType.EMPTY;
			$this.schedule_source.push( row );
		}

		this.showDailyTotal();
	}

	buildWeeklySource() {
		var $this = this;
		var map = {};
		this.schedule_source = [];

		var len = this.final_schedule_data_array.length;

		if ( len < 1 ) {
			buildEmptyRow();
			return;
		}

		for ( var i = 0; i < len; i++ ) {
			var shift = this.final_schedule_data_array[i];
			var date_string = '';
			if ( shift.date_stamp ) {
				var date = Global.strToDate( shift.date_stamp );
				date_string = date ? date.format( this.full_format ) : null;
			}

			var key = this.buildShiftKey( shift );

			// each row of schedule data, start from first row
			if ( !map[key] ) {
				var row = {};
				row.user_full_name = shift.user_full_name;
				row.last_name = shift.last_name;
				row.user_id = shift.user_id;
				row.branch_id = shift.branch_id;
				row.department_id = shift.department_id;
				row.schedule_policy_id = shift.schedule_policy_id;
				row.job_id = shift.job_id;
				row.job_item_id = shift.job_item_id;
				row.punch_tag_id = shift.punch_tag_id;

				var display_columns = this.select_layout.data.display_columns;
				var display_columns_len = display_columns.length;

				for ( var j = 0; j < display_columns_len; j++ ) {
					var field_name = display_columns[j];
					row[field_name] = shift[field_name] ? shift[field_name] : '';
				}

				if ( date_string ) {

					if ( shift.status_id == 20 ) {

						row[date_string] = $this.getAbsenceCellValue( shift );
					} else {
						row[date_string] = shift.start_time + ' - ' + shift.end_time;
					}

					row[date_string + '_data'] = shift;
				}

				this.schedule_source.push( row );
				map[key] = [this.schedule_source.length - 1];
			} else {
				// if one row already created, go to here to create cells in this row

				var find_position = false;
				for ( var x = 0; x < map[key].length; x++ ) {
					var row_index = map[key][x];
					row = this.schedule_source[row_index];
					if ( row[date_string] ) {
						continue;
					} else {

						if ( date_string ) {
							if ( shift.status_id == 20 ) {

								row[date_string] = $this.getAbsenceCellValue( shift );
							} else {
								row[date_string] = shift.start_time + ' - ' + shift.end_time;
							}
							row[date_string + '_data'] = shift;
						}

						find_position = true;
						break;
					}
				}

				if ( !find_position ) {
					row = {};
					row.user_full_name = shift.user_full_name;
					row.last_name = shift.last_name;
					row.user_id = shift.user_id;
					row.branch_id = shift.branch_id;
					row.department_id = shift.department_id;
					row.schedule_policy_id = shift.schedule_policy_id;
					row.job_id = shift.job_id;
					row.job_item_id = shift.job_item_id;
					row.punch_tag_id = shift.punch_tag_id;

					display_columns = this.select_layout.data.display_columns;
					display_columns_len = display_columns.length;

					for ( var j = 0; j < display_columns_len; j++ ) {
						field_name = display_columns[j];
						row[field_name] = shift[field_name] ? shift[field_name] : '';
					}

					if ( date_string ) {

						if ( shift.status_id == 20 ) {

							row[date_string] = $this.getAbsenceCellValue( shift );
						} else {
							row[date_string] = shift.start_time + ' - ' + shift.end_time;
						}
						row[date_string + '_data'] = shift;
					}

					this.schedule_source.push( row );
					map[key].push( this.schedule_source.length - 1 );
				}

			}

		}

		function buildEmptyRow() {

			var row = $this.getEmptyWeeklyRow();
			row.type = ScheduleViewControllerRowType.EMPTY;
			$this.schedule_source.push( row );
		}

		this.showDailyTotal();
		this.showWeeklyTotal();
	}

	getAbsenceCellValue( shift ) {

		var result;
		if ( shift.absence_policy ) {
			if ( shift.note ) {
				result = '*' + shift.absence_policy;
			} else {
				result = shift.absence_policy;
			}
		} else {
			if ( shift.note ) {
				result = '*' + 'N/A';
			} else {
				result = 'N/A';
			}
		}

		return result;
	}

	buildSortFields() {
		var sort_by_fields = [];

		//Error: Uncaught TypeError: Cannot read property 'data' of null in /interface/html5/#!m=Schedule line 5169
		if ( this.select_layout && this.select_layout.data ) {
			var display_columns = this.select_layout.data.display_columns;
			var display_columns_len = display_columns.length;
			if ( display_columns_len > 0 ) {
				sort_by_fields = display_columns.slice();
			}
		}

		sort_by_fields.push( 'user_full_name' );

		sort_by_fields.push( { name: 'start_time_stamp', primer: parseFloat, reverse: false } );

		return sort_by_fields;
	}

	buildMonthWeekNoDateArray( current_week_array ) {
		var has_date_user_map = {};
		var result = [];

		for ( var i = 0, ii = current_week_array.length; i < ii; i++ ) {
			var item = Global.clone( current_week_array[i] );
			has_date_user_map[item.user_id] = true;
		}

		for ( var key in this.all_user_map ) {
			if ( !has_date_user_map[key] ) {
				var item = Global.clone( this.all_user_map[key] );
				item.date_stamp = false;
				result.push( item );
			}
		}

		return result;
	}

	buildNoDateArray() {
		var records = [];
		var sort_array = [];
		var sort_item;
		var schedule_data = this.full_schedule_data.schedule_data;
		for ( var date_key in schedule_data ) {
			sort_item = {};
			sort_item.value = schedule_data[date_key];
			sort_item.sort_key = date_key;
			sort_array.push( sort_item );
		}

		var len = sort_array.length;
		for ( var i = 0; i < len; i++ ) {
			var date_item = sort_array[i];
			for ( var item_key in date_item.value ) {
				var item = date_item.value[item_key];
				item = this.replaceFalseToEmptyStringForSortFields( item );
				if ( !item.date_stamp ) {
					records.push( date_item.value[item_key] );
				}

			}

		}
		return records;
	}

	buildHasDateArray() {
		var records = [];
		this.all_user_map = {};
		var sort_array = [];
		var sort_item;
		var schedule_data = this.full_schedule_data.schedule_data;

		for ( var date_key in schedule_data ) {
			sort_item = {};
			sort_item.value = schedule_data[date_key];
			sort_item.sort_key = date_key;
			sort_array.push( sort_item );
		}
		var len = sort_array.length;
		for ( var i = 0; i < len; i++ ) {
			var date_item = sort_array[i];
			for ( var item_key in date_item.value ) {
				var item = date_item.value[item_key];
				item = this.replaceFalseToEmptyStringForSortFields( item );
				this.all_user_map[item.user_id] = Global.clone( item );
				if ( item.date_stamp ) {
					records.push( date_item.value[item_key] );
				}

			}

		}
		return records;
	}

	replaceFalseToEmptyStringForSortFields( item ) {
		if ( !item.branch ) {
			item.branch = '';
		}

		if ( !item.department ) {
			item.department = '';
		}

		if ( !item.default_branch ) {
			item.default_branch = '';
		}

		if ( !item.default_department ) {
			item.default_department = '';
		}

		if ( !item.job ) {
			item.job = '';
		}

		if ( !item.job_item ) {
			item.job_item = '';
		}

		if ( !item.title ) {
			item.title = '';
		}

		return item;
	}

	checkIsSelectedCell( row_id, cell_index ) {
		for ( var i = 0, m = this.select_cells_Array.length; i < m; i++ ) {
			var cell = this.select_cells_Array[i];
			if ( cell.row_id.toString() === row_id.toString() && cell.cell_index.toString() === cell_index.toString() ) {
				return true;
			}
		}

		return false;
	}

	buildScheduleGrid() {
		var $this = this;
		var grid;
		var grid_id = 'grid';

		if ( !this.grid ) {
			grid = $( this.el ).find( '#grid' );

			grid.attr( 'id', this.ui_id + '_grid' );  //Grid's id is ScriptName + _grid

			grid_id = this.ui_id + '_grid';
		} else {
			this.grid.grid.jqGrid( 'GridUnload', true );
			delete this.grid;
			this.grid = null;
		}

		grid_id = this.ui_id + '_grid';

		if ( !this.schedule_columns || this.schedule_columns.length == 0 ) {
			this.buildScheduleColumns();
		}

		this.grid = new TTGrid( grid_id, {
			draggble: true,
			altRows: true,
			data: [],
			datatype: 'local',
			sortable: false,
			scrollOffset: 0,
			rowNum: 10000,
			hoverrows: false,
			multiselectPosition: 'none',
			ondblClickRow: function() {
				$this.onGridDblClickRow();
			},
			onSelectRow: function( row_id, flag, e ) {
				var row_tr = $( this ).find( '#' + row_id );
				row_tr.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', true );
				return false;
			},
			onRightClickRow: function( row_id, iRow, cell_index, e ) {
				if ( !$this.checkIsSelectedCell( row_id, cell_index ) ) {
					var cell_val = $( e.target ).closest( 'td,th' ).html();
					var row_tr = $( this ).find( '#' + row_id );
					row_tr.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', true );
					$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );
				}
			},
			onCellSelect: function( row_id, cell_index, cell_val, e ) {
				$this.onCellSelect( 'timesheet_grid', row_id, cell_index, cell_val, this, e );
			},
			colNames: [],
			//colModel: this.schedule_columns,
			viewrecords: true,
			winMultiSelect: false,
			setGridSize: function() {
				$this.setGridHeight();
			},
			onResizeGrid: function() {
				$this.onResizeGrid(); //Because we have the daily/weekly totals and mode buttons, we need custom grid height logic.
			}
		}, this.schedule_columns );

		this.grid.grid.parent().parent().scrollLeft( 1000 );

		this.grid.grid.parent().parent().scroll( function( e ) {
			$this.scroll_position = $( e.target ).scrollTop();
		} );

		this.bindGridColumnEvents();
	}

	onResizeGrid() {
		if ( this.getMode() == ScheduleViewControllerMode.DAY ) {
			//Rebuild the special shift sizes
			this.buildCalendars();
		} else {
			this.setGridColumnsWidth();
		}

		this.setGridHeight();
	}

	//Bind column click event to change sort type and save columns to t_grid_header_array to use to set column style (asc or desc)
	bindGridColumnEvents() {
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

			var date;
			var mode = $this.getMode();

			if ( mode === ScheduleViewControllerMode.MONTH ) {
				var colModel = $this.grid.getGridParam( 'colModel' );
				date = $this.getCellRelatedDate( 1, colModel, $( this ).parent().index(), field );
			} else {
				date = Global.strToDate( field, $this.full_format );
			}

			if ( date && date.getYear() > 0 ) {
				$this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
				$this.highLightSelectDay();
			}

		}
	}

	buildMonthCell( cell_value, related_data, row, is_day_column ) {
		var col_models = this.grid.getGridParam( 'colModel' );
		var col_model = related_data.colModel;
		var content_div = $( '<div class=\'schedule-content-div\'></div>' );
		if ( is_day_column ) {
			content_div.addClass( 'date-column' );
		}
		var time_span = $( '<span class=\'schedule-time\' ></span>' );
		var item = row[col_model.name + '_data'];
		var full_date_str = row[col_model.name + '_full_date'];

		switch ( row.type ) {
			case ScheduleViewControllerRowType.TOTAL:
				time_span = $( '<span class=\'schedule-time total\'></span>' );
				if ( this.select_layout.data.display_columns.indexOf( col_model.index ) === -1 && ['user_full_name', 'shifts', 'absences', 'total_time', 'total_time_wage'].indexOf( col_model.index ) === -1 ) {
					if ( !cell_value ) {
						var cell_value = { shifts: 0, absences: 0, total_time: 0, total_time_wage: 0 };
					}

					var total_span = $( '<span class=\'schedule-time total\'></span>' );
					var currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span.text( 'S: ' + cell_value.shifts + ' A: ' + cell_value.absences );
					total_span.text( Global.getTimeUnit( cell_value.total_time ) + ' = ' + currency + Global.MoneyRound( cell_value.total_time_wage ) );

					content_div.prepend( total_span );
					content_div.prepend( time_span );
					content_div.css( 'height', 'auto' );
				} else {
					time_span.text( cell_value );
					content_div.prepend( time_span );

					if ( related_data.pos === col_models.length - 1 ) {
						content_div.css( 'padding-right', '15px' );
					}
				}
				break;
			case ScheduleViewControllerRowType.DATE:
				time_span.addClass( 'date' );
				content_div.addClass( 'month-date-cell' );
				if ( cell_value ) {

					time_span.html( cell_value );

					content_div.attr( 'id', this.ui_id + '_grid_' + full_date_str );

				} else {
					time_span.addClass( 'empty-date' );
					time_span.text( '.' );
				}

				content_div.prepend( time_span );

				break;
			case ScheduleViewControllerRowType.EMPTY:

				if ( !Global.isSet( cell_value ) ) {
					time_span.text( '' );
				}

				time_span.addClass( 'empty' );

				content_div.prepend( time_span );

				break;
			default:
				if ( Global.isSet( item ) ) {

					if ( !Global.isSet( item.id ) || !item.id || ( item.id && item.id == TTUUID.zero_id ) ) {
						time_span.addClass( 'no-id' );
					}

					if ( item.status_id == 20 ) {
						time_span.addClass( 'red' );
					}

					if ( item.user_id === TTUUID.zero_id ) {
						content_div.addClass( 'yellow-outline' );
					}

				}

				if ( Global.isSet( cell_value ) ) {
					if ( Global.isSet( item ) && item.note && cell_value.indexOf( '*' ) == -1 ) {
						cell_value = '*' + cell_value;
					}
					time_span.text( cell_value );

					if ( related_data.pos === col_models.length - 1 ) {
						content_div.css( 'padding-right', '15px' );
					}

				} else {
					time_span.text( '' );
				}
				content_div.prepend( time_span );
				break;
		}

		return content_div.get( 0 ).outerHTML;
	}

	buildYearCell( cell_value, related_data, row, is_day_column ) {
		var col_models = this.grid.getGridParam( 'colModel' );
		var col_model = related_data.colModel;
		var content_div = $( '<div class=\'schedule-content-div\'></div>' );

		if ( cell_value && cell_value.length == 1 && row.user_full_name == $.i18n._( 'OPEN' ) ) { //#2353 - only way to match open shifts.
			content_div.addClass( 'yellow-outline' );
		}

		if ( is_day_column ) {
			content_div.addClass( 'date-column' );
		}

		if ( !cell_value ) {
			//performance hack to speed up dom and rendering of year mode sheet
			return content_div.get( 0 ).outerHTML;
		}
		var time_span = $( '<span class=\'schedule-time\'></span>' );
		var item = row[col_model.name + '_data'];

		switch ( row.type ) {
			case ScheduleViewControllerRowType.EMPTY:
				time_span.addClass( 'empty' );

				content_div.prepend( time_span );

				break;
			default:
				if ( col_model.index >= 0 ) {
					if ( item && ( !item.id || ( item.id && item.id == TTUUID.zero_id ) ) ) {
						time_span.addClass( 'no-id' );
					}

					if ( cell_value === 'A' ) {
						time_span.addClass( 'absence-cell' );
					}
				}

				if ( Global.isSet( cell_value ) ) {
					time_span.text( cell_value );
				} else {
					time_span.text( '' );
				}

				// if ( related_data.pos === col_models.length - 1 ) {
				// 	content_div.css( 'padding-right', '15px' );
				// }

				//
				// if ( item && row.full_user_name == $.i18n._('OPEN') ) {
				// 	content_div.removeClass('date-column')
				// 	content_div.addClass( 'yellow-outline' );
				// }

				content_div.prepend( time_span );
				break;

		}

		return content_div[0].outerHTML;
	}

	calculateScheduleWidth() {
		//Calculate width of all static columns like employee name, departments, total, etc... So we know how much room is left for the hours.
		var static_width = 0;
		for ( var i in this.schedule_columns ) {
			if ( !this.schedule_columns[i].is_static_size || this.schedule_columns[i].name == 'total' || this.schedule_columns[i].name == 'scrollbar_spacer' ) {
				static_width += this.schedule_columns[i].width;
			}
		}

		if ( this.grid_div[0] && this.grid && ( Global.isVerticalScrollBarRequired( this.grid_div[0] ) || this.grid.getGridParam().height < this.grid_div[0].offsetHeight ) ) {
			//This stops showing a redundant horizontal scrollbar that only exists to show the vertical scrollbar.
			static_width += Global.getScrollbarWidth();
		}

		this.day_hour_width = Math.floor( ( $( '.context-border' ).innerWidth() - static_width ) / $( '.day_hour_div .day_hour_span' ).length );
		Debug.Text( 'Day Hour Width: ' + this.day_hour_width + ' Static Width: ' + static_width, 'ScheduleViewController.js', 'ScheduleViewController', 'calculateScheduleWidth', 10 );

		if ( this.day_hour_width < 40 ) {
			this.day_hour_width = 40;
		}
		return this.day_hour_width;
	}

	buildDayCell( cell_value, related_data, row, is_day_column ) {
		var $this = this;
		var col_model = related_data.colModel;
		var content_div = $( '<div class=\'schedule-content-div\'></div>' );
		if ( is_day_column ) {
			content_div.addClass( 'date-column' );
		}
		var time_span = $( '<span class=\'schedule-time\'></span>' );
		var item = row[col_model.index + '_data'];

		switch ( row.type ) {
			case ScheduleViewControllerRowType.TOTAL:

				if ( cell_value && Global.isSet( cell_value.total_time ) ) {
					var total_div = $( '<div  style=\'text-align: left\'></div>' );
					var currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span = $( '<span class=\'schedule-time total\'></span>' );
					time_span.text( 'S: ' + cell_value.shifts + ' A: ' + cell_value.absences + ' ' + Global.getTimeUnit( cell_value.total_time ) + ' = ' + currency + cell_value.total_time_wage );
					content_div.prepend( time_span );

					if ( cell_value.total_shifts_dic ) {
						var shifts_array = [];
						for ( var key in cell_value.total_shifts_dic ) {
							shifts_array.push( cell_value.total_shifts_dic[key] );
						}

						shifts_array = shifts_array.sort( function( a, b ) {

							return Global.compare( a, b, 'sort_order' );

						} );

						for ( var i = 0; i < shifts_array.length; i++ ) {
							var item = shifts_array[i];
							var span = $( '<span class="day_hour_span"></span>' );
							span.text( item.value );
							span.width( this.day_hour_width );
							total_div.append( span );
						}
						content_div.prepend( total_div );
					}

					content_div.css( 'height', 'auto' );

				} else if ( cell_value ) {
					time_span = $( '<span class=\'schedule-time total\'></span>' );
					time_span.text( cell_value );
					content_div.prepend( time_span );
				} else if ( col_model.display_total_column == true ) {
					currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span = $( '<span class=\'schedule-time total\'></span>' );
					time_span.text( 'S: 0 A: 0 00:00 = ' + currency + '0.00' );

					content_div.prepend( time_span );
					content_div.css( 'height', 'auto' );
				}

				break;
			case ScheduleViewControllerRowType.EMPTY:
				if ( !Global.isSet( cell_value ) ) {
					time_span.text( '' );
				}

				time_span.addClass( 'empty' );

				content_div.prepend( time_span );

				break;
			default:

				if ( Global.isSet( item ) ) {
					content_div.removeClass( 'schedule-content-div' ).addClass( 'schedule-content-day-div' );

					if ( related_data.rowId % 2 === 0 ) {
						time_span.removeClass( 'schedule-time' ).addClass( 'schedule-day-time even' );
					} else {
						time_span.removeClass( 'schedule-time' ).addClass( 'schedule-day-time' );
					}

					var width = $this.getDayShiftWidth( item );
					time_span.width( width );
					time_span.css( 'left', $this.getDayShiftOffset( item ) );

					if ( !Global.isSet( item.id ) || !item.id || item.id == TTUUID.zero_id ) {
						time_span.addClass( 'no-day-id' );
					}

					if ( item.status_id == 20 ) {
						time_span.removeClass( 'even' );
						time_span.addClass( 'red-bg' );
					}

					if ( item.user_id === TTUUID.zero_id ) {
						content_div.addClass( 'yellow-outline' );
					}
				}

				if ( Global.isSet( cell_value ) ) {
					if ( Global.isSet( item ) && item.note && cell_value.indexOf( '*' ) == -1 ) {
						cell_value = '*' + cell_value;
					}
					time_span.text( cell_value );
				} else {
					time_span.text( '' );
				}
				content_div.prepend( time_span );
				break;

		}

		return content_div.get( 0 ).outerHTML;
	}

	getDayShiftOffset( shift ) {

		var start_date_time = Global.strToDateTime( Global.getStandardDateTimeStr( shift.start_date, shift.start_time ) );
		var offset = ( start_date_time.getTime() - this.day_mode_start_date_time.getTime() ) / 3600000;

		return ( offset * this.day_hour_width );
	}

	getDayShiftWidth( shift ) {
		var start_date_time = Global.strToDateTime( Global.getStandardDateTimeStr( shift.start_date, shift.start_time ) );
		var end_date_time = Global.strToDateTime( Global.getStandardDateTimeStr( shift.end_date, shift.end_time ) );
		var offset = ( end_date_time.getTime() - start_date_time.getTime() ) / 3600000;

		//Debug.Text( 'Using Day Hour Width: '+ this.day_hour_width , 'ScheduleViewController.js', 'ScheduleViewController', 'getDayShiftWidth', 10 );
		return ( offset * this.day_hour_width );
	}

	buildWeekCell( cell_value, related_data, row, is_day_column ) {
		var col_models = this.grid.getGridParam( 'colModel' );

		var col_model = related_data.colModel;
		var content_div = $( '<div class=\'schedule-content-div\'></div>' );
		if ( is_day_column ) {
			content_div.addClass( 'date-column' );
		}
		var time_span = $( '<span class=\'schedule-time\'></span>' );
		var item = row[col_model.index + '_data'];

		switch ( row.type ) {
			case ScheduleViewControllerRowType.TOTAL:
				time_span = $( '<span class=\'schedule-time total\'></span>' );
				if ( this.select_layout.data.display_columns.indexOf( col_model.index ) === -1 && ['user_full_name', 'shifts', 'absences', 'total_time', 'total_time_wage'].indexOf( col_model.index ) === -1 ) {
					if ( !cell_value ) {
						var cell_value = { shifts: 0, absences: 0, total_time: 0, total_time_wage: 0 };
					}

					var total_span = $( '<span class=\'schedule-time total\'></span>' );
					var currency = LocalCacheData.getCurrentCurrencySymbol();
					time_span.text( 'S: ' + cell_value.shifts + ' A: ' + cell_value.absences );
					total_span.text( Global.getTimeUnit( cell_value.total_time ) + ' = ' + currency + Global.MoneyRound( cell_value.total_time_wage ) );

					content_div.prepend( total_span );
					content_div.prepend( time_span );
					content_div.css( 'height', 'auto' );
				} else {
					time_span.text( cell_value );
					content_div.prepend( time_span );

					if ( related_data.pos === col_models.length - 1 ) {
						content_div.css( 'padding-right', '15px' );
					}
				}
				break;
			case ScheduleViewControllerRowType.EMPTY:
				if ( !Global.isSet( cell_value ) ) {
					time_span.text( '' );
				}

				time_span.addClass( 'empty' );

				content_div.prepend( time_span );

				break;
			default:
				if ( Global.isSet( item ) ) {
					if ( !Global.isSet( item.id ) || !item.id || ( item.id && item.id == TTUUID.zero_id ) ) {
						time_span.addClass( 'no-id' );
					}

					if ( item.status_id == 20 ) {
						time_span.addClass( 'red' );
					}

					if ( item.user_id === TTUUID.zero_id ) {
						content_div.addClass( 'yellow-outline' );
					}

				}

				if ( Global.isSet( cell_value ) ) {

					if ( Global.isSet( item ) && item.note && cell_value.indexOf( '*' ) == -1 ) {
						cell_value = '*' + cell_value;
					}

					if ( related_data.pos === col_models.length - 1 ) {
						content_div.css( 'padding-right', '15px' );
					}

					time_span.text( cell_value );
				} else {
					time_span.text( '' );
				}

				content_div.prepend( time_span );

				break;

		}

		return content_div.get( 0 ).outerHTML;
	}

	onCellFormat( cell_value, related_data, row ) {
		//cell_value = Global.decodeCellValue( cell_value );
		/**
		 * FIXES BUG #1999: removed because it was double-encoding values in an attempt to avoid xss attacks.
		 * the following functions handle the needed encoding using a pseudo div element and .outerHTML()
		 * with this function in place, html encoded values come through to jqgrid double-encoded in the schedule view.
		 **/

		var retval = '';
		var $this = this;
		var is_day_column = true;

		if ( related_data.colModel.index == 'user_full_name' ) { //always part of grid.
			is_day_column = false;
		} else {
			for ( var n in this.all_columns ) {
				if ( related_data.colModel.label == this.all_columns[n].label ) {
					is_day_column = false;
					break;
				}
			}
		}
		switch ( this.getMode() ) {
			case ScheduleViewControllerMode.WEEK:
				return this.buildWeekCell( cell_value, related_data, row, is_day_column );
			case ScheduleViewControllerMode.MONTH:
				return this.buildMonthCell( cell_value, related_data, row, is_day_column );
			case ScheduleViewControllerMode.YEAR:
				return this.buildYearCell( cell_value, related_data, row, is_day_column );
			case ScheduleViewControllerMode.DAY:
				return this.buildDayCell( cell_value, related_data, row, is_day_column );
		}

		return '';
	}

	onSelectRow( grid_id, row_id, target ) {
		var $this = this;
		var row_tr = $( target ).find( '#' + row_id );
		row_tr.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', true );

		var cells_array = $this.select_cells_Array;
		var len = $this.select_cells_Array.length;

		this.select_all_shifts_array = [];
		this.select_shifts_array = [];
		this.select_recurring_shifts_array = [];
		this.select_cellls_and_shifts_array = [];
		for ( var i = 0; i < len; i++ ) {
			var info = cells_array[i];
			row_tr = $( target ).find( '#' + info.row_id );
			var cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.addClass( 'ui-state-highlight' ).attr( 'aria-selected', true );
			info.row_id = info.row_id - 0;
			if ( info.shift ) {
				if ( Global.isSet( info.shift.start_date ) ) { //date + time number
					info.shift.start_date_num = Global.strToDateTime( info.shift.start_date ).getTime();

				} else {
					info.shift.start_date_num = info.time_stamp_num; //Uer time_stamp_num from cell select setting, a date number
				}
				info.shift.row_index = info.row_id - 1;
				info.shift.cell_index = info.cell_index - 1;

				info.shift.orginal_row_index = info.row_id;
				info.shift.orginal_cell_index = info.cell_index;

				this.select_all_shifts_array.push( info.shift );
				this.select_cellls_and_shifts_array.push( info );

				if ( info.shift.id && info.shift_id != TTUUID.zero_id ) {
					this.select_shifts_array.push( info.shift );
				} else {
					this.select_recurring_shifts_array.push( info.shift );
				}

				this.select_all_shifts_array.sort( function( a, b ) {
					if ( a.cell_index < b.cell_index ) {
						return -1;
					}
					if ( a.cell_index > b.cell_index ) {
						return 1;
					}

					if ( a.cell_index === b.cell_index ) {
						if ( a.row_index < b.row_index ) {
							return -1;
						}
						if ( a.row_index > b.row_index ) {
							return 1;
						}
					}

					return 0;

				} );
			} else {
				this.select_cellls_and_shifts_array.push( info );
			}

			this.select_cellls_and_shifts_array.sort( function( a, b ) {
				if ( a.cell_index < b.cell_index ) {
					return -1;
				}
				if ( a.cell_index > b.cell_index ) {
					return 1;
				}

				if ( a.cell_index === b.cell_index ) {
					if ( a.row_id < b.row_id ) {
						return -1;
					}
					if ( a.row_id > b.row_id ) {
						return 1;
					}
				}

				return 0;

			} );

		}

		this.setDefaultMenu();
	}

	getCellRelatedDate( row_index, col_model, cell_index, data_field ) {
		var date;
		var date_row_1_index = this.schedule_source.indexOf( this.month_date_row_array[0] );
		var date_row_2_index = this.schedule_source.indexOf( this.month_date_row_array[1] );
		var date_row_3_index = this.schedule_source.indexOf( this.month_date_row_array[2] );
		var date_row_4_index = this.schedule_source.indexOf( this.month_date_row_array[3] );

		if ( row_index < date_row_1_index ) {
			date = Global.strToDate( col_model[cell_index].index, this.full_format );
		} else if ( row_index >= date_row_1_index && row_index < date_row_2_index ) {
			date = Global.strToDate( this.month_date_row_array[0][data_field + '_full_date'], this.full_format );
		} else if ( row_index >= date_row_2_index && row_index < date_row_3_index ) {
			date = Global.strToDate( this.month_date_row_array[1][data_field + '_full_date'], this.full_format );
		} else if ( row_index >= date_row_3_index && row_index < date_row_4_index ) {
			date = Global.strToDate( this.month_date_row_array[2][data_field + '_full_date'], this.full_format );
		} else if ( row_index >= date_row_4_index ) {
			date = Global.strToDate( this.month_date_row_array[3][data_field + '_full_date'], this.full_format );
		}

		return date;
	}

	getDataByCellIndex( row_index, cell_index ) {
		var $this = this;
		var row = $this.schedule_source[row_index];
		var colModel = $this.grid.getGridParam( 'colModel' );

		//Error: TypeError: row is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141117-134330 line 2 > eval line 5952
		//Error: TypeError: colModel[cell_index] is undefined in /interface/html5/framework/jquery.min.js?v=8.0.0-20141117-134330 line 2 > eval line 5951
		if ( !colModel || !colModel[cell_index] || !row ) {
			return null;
		}

		var data_field = colModel[cell_index].name;
		var data = row[data_field + '_data'];

		return data;
	}

	onCellSelect( grid_id, row_id, cell_index, cell_val, target, e ) {
		$( '#ribbon_view_container .context-menu:visible a' ).click();

		if ( cell_index < 0 ) {
			return;
		}

		var $this = this;
		var len = 0;
		var row;
		var colModel;
		var data_field;
		var shift;
		var cells_array = [];
		var date;
		var mode = this.getMode();

		cells_array = $this.select_cells_Array;

		len = $this.select_cells_Array.length;

		row = $this.schedule_source[row_id - 1];

		var row_index = $this.schedule_source.indexOf( row );

		colModel = $this.grid.getGridParam( 'colModel' );

		data_field = colModel[cell_index].name;

		shift = row[data_field + '_data'];

		if ( mode === ScheduleViewControllerMode.MONTH ) {
			date = $this.getCellRelatedDate( row_index, colModel, cell_index, data_field );
		} else {
			date = Global.strToDate( data_field, this.full_format );
		}

		if ( !date || date.getTime() < -1 ) {
			date = new Date();
		}

		//Clean all select cells first
		for ( var i = 0; i < len; i++ ) {
			var info = cells_array[i];
			var row_tr = $( target ).find( '#' + info.row_id );
			var cell_td = $( row_tr.find( 'td' )[info.cell_index] );
			cell_td.removeClass( 'ui-state-highlight' ).attr( 'aria-selected', false );
		}

		if ( date ) {
			var date_str = date.format();
			var time_stamp_num = Global.strToDate( date_str ).getTime();
		} else {
			date_str = '';
			time_stamp_num = 0;
		}
		// Add multiple selection if click cell and hold ctrl or command
		if ( e.ctrlKey || e.metaKey ) {
			var found = false;
			for ( var i = 0; i < len; i++ ) {
				info = cells_array[i];
				// row id should be number
				if ( parseInt( row_id ) === info.row_id && cell_index === info.cell_index ) {
					cells_array.splice( i, 1 );
					found = true;
					break;
				}
			}

			if ( !found ) {

				cells_array.push( {
					row_id: row_id,
					cell_index: cell_index,
					cell_val: cell_val,
					shift: shift,
					date: date_str,
					time_stamp_num: time_stamp_num,
					user_id: row.user_id,
					branch_id: row.branch_id,
					department_id: row.department_id,
					job_id: row.job_id,
					job_item_id: row.job_item_id,
					punch_tag_id: row.punch_tag_id
				} );

				$this.select_cells_Array = cells_array;

				this.select_cells_Array.sort( function( a, b ) {

					return Global.compare( a, b, 'time_stamp_num' );

				} );

			}
		} else if ( e.shiftKey ) {
			var start_row_index = row_id;
			var start_cell_index = cell_index;

			var end_row_index = row_id;
			var end_cell_index = cell_index;

			for ( var i = 0; i < len; i++ ) {
				info = cells_array[i];

				if ( parseInt( info.row_id ) < parseInt( start_row_index ) ) {
					start_row_index = info.row_id;
				} else if ( parseInt( info.row_id ) > parseInt( end_row_index ) ) {
					end_row_index = info.row_id;
				}

				if ( parseInt( info.cell_index ) < parseInt( start_cell_index ) ) {
					start_cell_index = info.cell_index;
				} else if ( parseInt( info.cell_index ) > parseInt( end_cell_index ) ) {
					end_cell_index = info.cell_index;
				}
			}

			//If the click is inside the existing selection, truncate the existing selection to the click.
			//Check in TimeSheetViewController.js for related change
			if ( cells_array[cells_array.length - 1] && cells_array[0] && cells_array[cells_array.length - 1].cell_index >= cell_index && cells_array[0].cell_index <= cell_index && cells_array[cells_array.length - 1].row_id >= row_id && cells_array[0].row_id <= row_id ) {
				end_row_index = row_id;
				end_cell_index = cell_index;
			}

			start_row_index = parseInt( start_row_index );
			end_row_index = parseInt( end_row_index );
			cells_array = [];

			for ( var i = start_row_index; i <= end_row_index; i++ ) {
				var r_index = i;
				for ( var j = start_cell_index; j <= end_cell_index; j++ ) {
					var c_index = j;

					row_tr = $( target ).find( '#' + r_index );

					cell_td = $( row_tr.find( 'td' )[c_index] );

					cell_val = cell_td[0].outerHTML;

					row = $this.schedule_source[r_index - 1];

					row_index = $this.schedule_source.indexOf( row );

					colModel = $this.grid.getGridParam( 'colModel' );

					data_field = colModel[c_index].name;

					shift = row[data_field + '_data'];

					if ( mode === ScheduleViewControllerMode.MONTH ) {
						date = $this.getCellRelatedDate( row_index, colModel, c_index, data_field );
					} else {
						date = Global.strToDate( data_field, this.full_format );
					}

					if ( date && date.getTime() > 0 ) {
						date_str = date.format();
						time_stamp_num = Global.strToDate( date_str ).getTime();
					} else {
						date_str = '';
						time_stamp_num = 0;
					}

					cells_array.push( {
						row_id: r_index.toString(),
						cell_index: c_index,
						cell_val: cell_val,
						shift: shift,
						date: date_str,
						time_stamp_num: time_stamp_num,
						user_id: row.user_id,
						branch_id: row.branch_id,
						department_id: row.department_id,
						job_id: row.job_id,
						job_item_id: row.job_item_id,
						punch_tag_id: row.punch_tag_id
					} );

				}
			}

			$this.select_cells_Array = cells_array;

			this.select_cells_Array.sort( function( a, b ) {

				return Global.compare( a, b, 'time_stamp_num' );

			} );

		} else {

			cells_array = [
				{
					row_id: row_id,
					cell_index: cell_index,
					cell_val: cell_val,
					shift: shift,
					date: date_str,
					time_stamp_num: time_stamp_num,
					user_id: row.user_id,
					branch_id: row.branch_id,
					department_id: row.department_id,
					job_id: row.job_id,
					job_item_id: row.job_item_id,
					punch_tag_id: row.punch_tag_id
				}
			];

			$this.select_cells_Array = cells_array;

			if ( date && date.getYear() > 0 ) {
				this.setDatePickerValue( date.format( Global.getLoginUserDateFormat() ) );
				this.highLightSelectDay();
			}

		}

		$this.onSelectRow( grid_id, row_id, target );

		var target_row_tr = $( target ).find( '#' + row_id );
		var target_row_td = $( target_row_tr.find( 'td' )[cell_index] );

		if ( target_row_td.attr( 'infor_column' ) ) {
			var target_row_index = target_row_tr.index();
			var rowspan = parseInt( target_row_td.attr( 'rowspan' ) );

			if ( isNaN( rowspan ) ) {
				rowspan = 1;
			}

			var last_row = target_row_tr.parent().children().eq( ( target_row_index + rowspan - 1 ) );
			var last_td = last_row.children().eq( ( last_row.children().length - 1 ) );

			var last_row_id = last_row.attr( 'id' );
			var last_cell_id = ( last_row.children().length - 1 );
			var last_cell_value = last_td.find( '.schedule-time' ).text();

			$this.onCellSelect( 'timesheet_grid', last_row_id,
				last_cell_id,
				last_cell_value,
				this.grid.grid,
				{ shiftKey: true } );

		}
	}

	highLightSelectDay() {

		var mode = this.getMode();

		$( '.highlight-header' ).removeClass( 'highlight-header' ); //Clean all hight light header or date row

		if ( mode === ScheduleViewControllerMode.MONTH ) {
			var select_date = Global.strToDate( this.start_date_picker.getValue() );
			var select_day = select_date.getDay(); // column index is day number for month mode
			select_date = select_date.format( this.full_format );

			var header = $( '#' + this.ui_id + '_grid_' + select_day );
			var header_text = $( header.children()[1] ).text();	 //get current column header value

			this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_date ); //get date row

			if ( this.highlight_header.length !== 1 ) {
				this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_day ); //get header
			} else {
				if ( header_text === this.highlight_header.text() ) {
					$( '.highlight-header' ).removeClass( 'highlight-header' );
					header.addClass( 'highlight-header' );
				}
			}

		} else {
			select_date = Global.strToDate( this.start_date_picker.getValue() );
			//Error: Uncaught TypeError: Cannot read property 'format' of null in interface/html5/#!m=Schedule&date=null&mode=week line 6295
			if ( !select_date ) {
				select_date = new Date();
				this.setDatePickerValue( select_date.format() );
			}
			select_date = select_date.format( this.full_format );
			this.highlight_header = $( '#' + this.ui_id + '_grid_' + select_date );
		}

		if ( mode !== ScheduleViewControllerMode.DAY ) {

			if ( mode === ScheduleViewControllerMode.MONTH ) {
				this.highlight_header.parent().addClass( 'highlight-header' );
			}

			this.highlight_header.addClass( 'highlight-header' );

		}
	}

	buildAllModeCommonColumns() {
		var $this = this;
		this.shift_key_name_array = ['user_id'];
		var display_columns = this.buildDisplayColumns( this.select_layout.data.display_columns );

		var len = display_columns.length;

		for ( var i = 0; i < len; i++ ) {
			var column = display_columns[i];
			var column_info = {
				name: column.value,
				index: column.value,
				label: column.label,
				width: 122,
				sortable: false,
				title: false,
				fixed: true,
				resizable: false,
				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
				},
				cellattr: function( rowId, tv, rawObject, cm, rdata ) {
					var field_name = cm.index;
					return 'class="' + field_name + '_cell" infor_column="true"';
				}
			};
			this.schedule_columns.push( column_info );
			this.shift_key_name_array.push( column.value + '_id' );
		}

		var employee_column = {
			name: 'user_full_name',
			index: 'user_full_name',
			label: $.i18n._( 'Employee' ),
			width: 122,
			sortable: false,
			title: false,
			fixed: true,
			resizable: false,
			formatter: function() {

				return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
			},
			cellattr: function( rowId, tv, rawObject, cm, rdata ) {
				var field_name = cm.index;
				return 'class="' + field_name + '_cell" infor_column="true"';
			}
		};

		this.schedule_columns.push( employee_column );
	}

	buildMonthRows() {

		var month_days = ( this.end_date.getTime() - this.start_date.getTime() ) / 86400000 + 1;
		var start_day = new Date( this.start_date.getTime() ).getDay();
		this.schedule_source = [];
		var start_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + 7 ) );

		this.month_date_row_array = [];

		var row_num = 1;
		var z = 0; //day offset

		while ( row_num < 5 ) {
			var current_day = start_day;
			var row = this.getEmptyWeeklyRow();
			row.type = ScheduleViewControllerRowType.DATE;

			for ( var i = 0; i < 7; i++ ) {
				var current_date = new Date( new Date( start_date.getTime() ).setDate( start_date.getDate() + z ) );

				if ( current_date.getTime() > this.end_date.getTime() ) {
					break;
				}

				row[current_day] = current_date.format( this.weekly_format );

				row[current_day] = this.setHolidayHeader( row[current_day] );
				row[current_day + '_full_date'] = current_date.format( this.full_format );
				row[current_day + '_time'] = current_date.getTime();

				current_day = current_day + 1;

				if ( current_day === 7 ) {
					current_day = 0;
				}

				z = z + 1;
			}

			this.month_date_row_array.push( row );
			row_num = row_num + 1;

			this.schedule_source.push( row );

		}
	}

	getEmptyWeeklyRow() {
		var row = {};
		row.user_full_name = '';
		row.last_name = '';
		row.user_id = '';
		row.branch_id = '';
		row.department_id = '';
		row.schedule_policy_id = '';
		row.job_id = '';
		row.job_item_id = '';
		row.punch_tag_id = [];

		var display_columns = this.select_layout.data.display_columns;
		var display_columns_len = display_columns.length;

		for ( var j = 0; j < display_columns_len; j++ ) {
			var field_name = display_columns[j];
			row[field_name] = '';
		}

		return row;
	}

	buildMonthColumns() {
		var $this = this;
		this.schedule_columns = [];
		this.buildAllModeCommonColumns();

//		var current_date = new Date( this.start_date.getTime() );

		this.month_date_row_tr_ids = {};
		for ( var i = 0; i < 7; i++ ) {
			var temp_start_date = new Date( this.start_date.getTime() );
			var current_date = new Date( temp_start_date.setDate( temp_start_date.getDate() + i ) );
			var start_day = current_date.getDay();
			var header_text = current_date.format( this.weekly_format );
			var data_field = start_day;
			if ( data_field === 7 ) {
				data_field = 0;
			}

			header_text = this.setHolidayHeader( header_text );

			var full_data_field = current_date.format( this.full_format );

			var column_info = {
				resizable: false,
				name: data_field.toString(), //Needed for jqgrid otherwise it thinks its an index lookup.
				index: full_data_field,
				label: header_text,
				width: 150,
				fixed: true,
				sortable: false,
				title: false,
				formatter: function() {
					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
				},
				cellattr: function( rowId, tv, rawObject, cm, rdata ) {
					if ( rawObject.type === ScheduleViewControllerRowType.DATE ) {
						$this.month_date_row_tr_ids[rowId] = true;
					}
				},

				is_static_size: true
			};
			this.schedule_columns.push( column_info );

		}

		this.buildWeeklyTotalColumns();

		this.schedule_columns.push( {
			name: 'scrollbar_spacer',
			index: 'scrollbar_spacer',
			label: '',
			width: Global.getScrollbarWidth(),
			sortable: false,
			title: false,
			fixed: true,
			is_static_size: true, //Used to skip our own auto-sizing.
		} );

		this.buildMonthRows();
	}

	getDayByDayNum( day ) {

		var day;
		switch ( day ) {
			case 0:
				day = $.i18n._( 'S' );
				break;
			case 1:
				day = $.i18n._( 'M' );
				break;
			case 2:
				day = $.i18n._( 'T' );
				break;
			case 3:
				day = $.i18n._( 'W' );
				break;
			case 4:
				day = $.i18n._( 'T' );
				break;
			case 5:
				day = $.i18n._( 'F' );
				break;
			case 6:
				day = $.i18n._( 'S' );
				break;
		}

		return day;
	}

	buildYearColumns() {
		var $this = this;
		this.schedule_columns = [];

		this.buildAllModeCommonColumns();

		var current_date = new Date( this.start_date.getTime() );
		var end_date = new Date( this.end_date.getTime() );

		var i = 0;
		while ( current_date.format( this.full_format ) !== end_date.format( this.full_format ) ) {

			current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );
			var header_text = current_date.format( this.weekly_format );
			var data_field = current_date.format( this.full_format );

			var day = this.getDayByDayNum( current_date.getDay() );

			var column_info = {
				name: data_field,
				index: i,
				label: day + '<br>' + current_date.getDate(),
				width: 22,
				sortable: false,
				title: false,
				fixed: true,
				is_static_size: true, //Used to skip our own auto-sizing.

				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
				}
			};
			this.schedule_columns.push( column_info );

			i = i + 1;
		}

		this.schedule_columns.push( {
			name: 'scrollbar_spacer',
			index: i,
			label: '',
			width: Global.getScrollbarWidth(),
			sortable: false,
			title: false,
			fixed: true,
			is_static_size: true, //Used to skip our own auto-sizing.
		} );

		//this.buildWeeklyTotalColumns();
	}

	buildDayColumns() {
		var $this = this;
		this.schedule_columns = [];

		this.buildAllModeCommonColumns();

		var current_date = new Date( this.start_date.getTime() );
		var header_text = current_date.format( this.weekly_format );
		var data_field = current_date.format( this.full_format );

		var column_info = {
			fixed: true,
			resizable: false,
			width: 500,
			name: data_field,
			index: data_field,
			label: 'daily_header_replace',
			is_static_size: true,
			sortable: false,
			title: false,
			display_total_column: true,
			formatter: function() {

				return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
			}
		};
		this.schedule_columns.push( column_info );

		column_info = {
			fixed: true,
			resizable: false,
			width: 122,
			name: 'total',
			index: 'total',
			label: 'Total Time',
			sortable: false,
			title: false,
			formatter: function() {

				return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
			}
		};
		this.schedule_columns.push( column_info );

		this.schedule_columns.push( {
			name: 'scrollbar_spacer',
			index: 'scrollbar_spacer',
			label: '',
			width: Global.getScrollbarWidth(),
			sortable: false,
			title: false,
			fixed: true,
			//is_static_size: false, //Used to skip our own auto-sizing.
		} );
	}

	buildWeekColumns() {

		var $this = this;
		this.schedule_columns = [];

		this.buildAllModeCommonColumns();

		//Error: Uncaught TypeError: Cannot read property 'getTime' of null in /interface/html5/#!m=Schedule&date=20141208&mode=week line 6580
		if ( !this.start_date ) {
			return;
		}

		var current_date = new Date( this.start_date.getTime() );
		var end_date = new Date( this.end_date.getTime() );

		var i = 0;
		var set_fixed_width = false;
//		if ( this.schedule_columns.length === 1 ) {
//			set_fixed_width = false;
//		}

		while ( current_date.format( this.full_format ) !== end_date.format( this.full_format ) ) {

			current_date = new Date( new Date( this.start_date.getTime() ).setDate( this.start_date.getDate() + i ) );

			var header_text = current_date.format( this.weekly_format );
			var data_field = current_date.format( this.full_format );

			header_text = this.setHolidayHeader( header_text );

			var column_info = {
				resizable: false,
				name: data_field.toString(), //Needed for jqgrid otherwise it thinks its an index lookup.
				index: data_field,
				label: header_text,
				width: 150,
				fixed: true,
				sortable: false,
				title: false,
				formatter: function() {

					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
				},
				is_static_size: true
			};

			this.schedule_columns.push( column_info );

			i = i + 1;

		}

		this.buildWeeklyTotalColumns();

		this.schedule_columns.push( {
			name: 'scrollbar_spacer',
			index: 'scrollbar_spacer',
			label: '',
			width: Global.getScrollbarWidth(),
			sortable: false,
			title: false,
			fixed: true,
			is_static_size: true, //Used to skip our own auto-sizing.
		} );

		return this.schedule_columns;
	}

	setHolidayHeader( header_text, inLine ) {

		if ( this.holiday_data_dic ) {
			if ( this.holiday_data_dic[header_text] ) {

				if ( inLine ) {
					header_text = header_text + ' (' + Global.htmlEncode( this.holiday_data_dic[header_text].name ) + ')';
				} else {
					header_text = header_text + '<br>' + Global.htmlEncode( this.holiday_data_dic[header_text].name );
				}

			}
		}

		return header_text;
	}

	buildWeeklyTotalColumns() {
		var $this = this;
		var show_weekly_total = this.settings_menu.getButton( 'weekly_totals_btn' ).active;

		var is_fixed = false;

		if ( show_weekly_total ) {
			var shifts_column = {
				name: 'shifts',
				index: 'shifts',
				label: $.i18n._( 'Shifts' ),
				width: 50,
				sortable: false,
				title: false,
				formatter: function() {
					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
				},
				fixed: true
			};

			var absences_column = {
				name: 'absences',
				index: 'absences',
				label: $.i18n._( 'Absences' ),
				width: 70,
				sortable: false,
				title: false,
				formatter: function() {
					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
				},
				fixed: true
			};

			var total_time = {
				name: 'total_time',
				index: 'total_time',
				label: $.i18n._( 'Total Time' ),
				width: 70,
				sortable: false,
				title: false,
				formatter: function() {
					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
				},
				fixed: true
			};

			var total_time_wage = {
				name: 'total_time_wage',
				index: 'total_time_wage',
				label: $.i18n._( 'Wages' ),
				width: 90,
				sortable: false,
				title: false,
				formatter: function() {
					return $this.onCellFormat( arguments[0], arguments[1], arguments[2], arguments[3] );
				},
				fixed: true
			};

			this.schedule_columns.push( shifts_column );
			this.schedule_columns.push( absences_column );
			this.schedule_columns.push( total_time );
			this.schedule_columns.push( total_time_wage );
		}
	}

	buildScheduleColumns() {
		this.shift_key_name_array = ['user_id'];
		this.schedule_columns = [];

		var mode = this.getMode();

		switch ( mode ) {
			case ScheduleViewControllerMode.WEEK:
				this.buildWeekColumns();
				break;
			case ScheduleViewControllerMode.MONTH:
				this.buildMonthColumns();
				break;
			case ScheduleViewControllerMode.YEAR:
				this.buildYearColumns();
				break;
			case ScheduleViewControllerMode.DAY:
				this.buildDayColumns();
				break;
		}
	}

	setScheduleGridRowSpan() {

		var $this = this;

		var display_columns = this.select_layout.data.display_columns;

		var display_columns_len = display_columns.length;

		for ( var i = 0; i < display_columns_len; i++ ) {
			var column_name = display_columns[i];

			startSet( column_name );
		}

		startSet( 'user_full_name' );

		function startSet( key ) {
			var cells = $this.grid.grid.find( '[aria-describedby=' + $this.ui_id + '_grid_' + key + ']' );

			var len = cells.length;

			var count = 0;

			var last_val = null;

			var last_cell = null;

			var need_remove_cells = [];

			var start = len - 1;

			setRows();

			function setRows() {
				for ( var i = start; i >= 0; i-- ) {
					var cell = $( cells[i] );
					var cell_val = $( cells[i] ).children().eq( 0 ).children().eq( 0 ).text();

					if ( i === len - 1 ) {
						last_val = cell_val;
						count = count + 1;
						last_cell = cell;
					} else if ( last_val !== cell_val ) {
						last_val = cell_val;
						last_cell.attr( 'rowspan', count );

						for ( var j = 0; j < need_remove_cells.length; j++ ) {
							var need_removed_cell = need_remove_cells[j];
//							need_removed_cell.addClass( 'need-remove' );
							var node = need_removed_cell[0];
							if ( node.parentNode ) {
								node.style.display = 'none';
							}

						}

						need_removed_cell = [];
						count = 1;
						last_cell = cell;

					} else if ( i === 0 ) {
						count = count + 1;
						need_remove_cells.push( last_cell );
						if ( count > 1 ) {
							cell.attr( 'rowspan', count );
							for ( var j = 0; j < need_remove_cells.length; j++ ) {
								need_removed_cell = need_remove_cells[j];
//								need_removed_cell.addClass( 'need-remove' );
								node = need_removed_cell[0];
								if ( node.parentNode ) {
									node.style.display = 'none';
								}

							}
						}

					} else {
						count = count + 1;
						need_remove_cells.push( last_cell );
						last_cell = cell;
					}

				}

			}

		}
	}

	onSetSearchFilterFinished() {
	}

	onBuildBasicUIFinished() {
	}

	onBuildAdvUIFinished() {
	}

	// setDateUrl() {
	// 	var $this = this;
	// 	if ( !$this.edit_view ) {
	//
	// 		var mode = this.getMode();
	// 		var default_date = $this.start_date_picker.getDefaultFormatValue();
	//
	// 		if ( mode ) {
	// 			window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date + '&mode=' + mode;
	// 		} else {
	// 			if ( LocalCacheData.getAllURLArgs() && LocalCacheData.getAllURLArgs().mode ) {
	// 				$this.setToggleButtonValue( LocalCacheData.getAllURLArgs().mode );
	// 				mode = this.getMode();
	// 				window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date + '&mode=' + mode;
	// 			} else {
	// 				window.location = Global.getBaseURL() + '#!m=' + $this.viewId + '&date=' + default_date;
	// 			}
	// 		}
	//
	// 	}
	// },

	reSetURL() {
		var mode = this.getMode();
		var args;
		if ( mode ) {
			//args = '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode;
			args = '#!m=' + this.viewId + '&mode=' + mode;
			Global.setURLToBrowser( Global.getBaseURL() + args );
		} else {
			//args = '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue();
			args = '#!m=' + this.viewId;
			Global.setURLToBrowser( Global.getBaseURL() + args );
		}
		LocalCacheData.setAllURLArgs( Global.buildArgDic( args.split( '&' ) ) );
	}

	setURL() {
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

		if ( this.canSetURL() ) {
			var tab_name = this.edit_view_tab ? this.edit_view_tab.find( '.edit-view-tab-bar-label' ).children().eq( this.getEditViewTabIndex() ).text() : '';
			tab_name = tab_name.replace( /\/|\s+/g, '' );

			var mode = this.getMode();

			if ( this.current_edit_record && this.current_edit_record.id && this.current_edit_record.id != TTUUID.zero_id ) {
				if ( a ) {

					//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode + '&a=' + a + '&id=' + this.current_edit_record.id +
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&mode=' + mode + '&a=' + a + '&id=' + this.current_edit_record.id +
						'&tab=' + tab_name );

				} else {
					//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode + '&id=' + this.current_edit_record.id );
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&mode=' + mode + '&id=' + this.current_edit_record.id );

				}

				Global.trackView( this.viewId, LocalCacheData.current_doing_context_action );

			} else {
				if ( a ) {

					//Edit a record which don't have id, schedule view Recurring Scedule
					if ( a === 'edit' ) {
						//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode + '&a=' + 'new' +
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&mode=' + mode + '&a=' + 'new' +
							'&tab=' + tab_name );
					} else {
						//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode + '&a=' + a +
						Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&mode=' + mode + '&a=' + a +
							'&tab=' + tab_name );
					}

				} else {
					//Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&date=' + this.start_date_picker.getDefaultFormatValue() + '&mode=' + mode );
					Global.setURLToBrowser( Global.getBaseURL() + '#!m=' + this.viewId + '&mode=' + mode );
				}
			}

		}
	}

	clearSelection( set_default_menu ) {
		if ( this.grid && this.grid.grid ) {
			this.grid.grid.jqGrid( 'resetSelection' );
		}

		this.select_cells_Array = [];
		this.select_cellls_and_shifts_array = [];
		this.select_all_shifts_array = [];

		//Issue #3067 - Causes incorrect context menu to be set when saving a schedule which produces context menu flashing
		//and button state to change. However this call is required to properly reset mass edit and other context menu buttons.
		if ( set_default_menu || set_default_menu === undefined ) {
			this.setDefaultMenu();
		}
	}

	render() {
		var $this = this;
		super.render();

		// Vue Schedule Settings Initialization

		var schedule_settings_options = [
			{
				label: $.i18n._( 'Daily Totals' ),
				id: 'daily_totals_btn',
				no_group_label: true,
				vue_icon: 'tticon tticon-settings_black_24dp',
				action_group: 'schedule_settings',
				multi_select_group: 2,
				visible: true,
				command: () => {
					//Error: Uncaught TypeError: Cannot call method 'clearGridData' of null in /interface/html5/index.php?desktop=1#!m=Schedule&date=20150118&mode=week line 6944
					if ( !this.checkScheduleData() || !this.grid || !this.grid.grid ) {
						return;
					}

					this.buildCalendars( true );
					this.onResizeGrid();
					this.setScheduleGridRowSpan();
					this.setMonthDateRowPosition();
					this.setGridColumnsWidth();
					this.onSearch( true ); //Trigger saving of saved search & layout.
				}
			},
			{
				label: $.i18n._( 'Weekly Totals' ),
				id: 'weekly_totals_btn',
				no_group_label: true,
				vue_icon: 'tticon tticon-settings_black_24dp',
				action_group: 'schedule_settings',
				multi_select_group: 3,
				visible: true,
				command: () => {
					if ( !this.checkScheduleData() ) {
						return;
					}

					this.buildCalendars();
					this.onResizeGrid();
					this.onSearch( true ); //Trigger saving of saved search & layout.
				}
			},
			{   //Moved to second last item in dropdown as errors were triggering on the VueJS side when visible was set to false, and it was first item in the dropdown.
				label: $.i18n._( 'Show Unscheduled Employees' ),
				id: 'all_employee_btn',
				no_group_label: true,
				vue_icon: 'tticon tticon-settings_black_24dp',
				action_group: 'schedule_settings',
				multi_select_group: 1,
				visible: this.unscheduledEmployeeUIValidate(),
				// visible: PermissionManager.checkTopLevelPermission( 'Wage' ),
				command: () => {
					this.search();
					this.onSearch( true ); //Trigger saving of saved search & layout.
				}
			},
			{
				label: $.i18n._( 'Strict Range' ),
				id: 'strict_range_btn',
				active: true,
				no_group_label: true,
				vue_icon: 'tticon tticon-settings_black_24dp',
				action_group: 'schedule_settings',
				multi_select_group: 4,
				visible: true,
				command: () => {
					$this.search( false, true );
					$this.onSearch( true ); //Trigger saving of saved search & layout.
				}
			},
		];

		var schedule_mode_options = [
			{ label: $.i18n._( 'Day' ), value: 'day' },
			{ label: $.i18n._( 'Week' ), value: 'week' },
			{ label: $.i18n._( 'Month' ), value: 'month' },
			{ label: $.i18n._( 'Year' ), value: 'year' }
		];

		// Init Vue control bar

		this.vue_control_bar_mount_id = 'vue-schedule-control-bar';
		var root_props = {
			view_id: this.viewId,
			schedule_settings_options: schedule_settings_options,
			schedule_mode_options: schedule_mode_options,
		}

		TTVueUtils.mountComponent( this.vue_control_bar_mount_id, ScheduleControlBar, root_props );

		var date_chooser_div = $( '.schedule-view .date-chooser-div' );

		//The Vue ScheduleControlBar may already contain a date picker from a previous page load.
		//The parent div needs all children removed to prevent multiple date pickers from being added.
		date_chooser_div.empty(); //TODO: Temp hack until root cause is found. Seems to happen on webpack prod but not build:dev:watch.

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
			var mode = $this.getMode();
			var new_date;
			var select_date = $this.start_date;

			$this.clearSelection();

			if ( !select_date ) {
				return;
			}

			switch ( mode ) {
				case ScheduleViewControllerMode.WEEK:
					var select_date = Global.strToDate( ( ( $this.getSelectDate() ) ? $this.getSelectDate() : new Date().format() ) );
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 7 ) );
					break;
				case ScheduleViewControllerMode.YEAR:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 56 ) ); //8 weeks.
					break;
				case ScheduleViewControllerMode.DAY:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 1 ) );
					break
				case ScheduleViewControllerMode.MONTH:
					if ( $this.settings_menu.getButton( 'strict_range_btn' ).active ) {
						new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 1 ) );
					} else {
						new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 36 ) ); //5 weeks + 1 day same as going forward
                    }
					break;
				default:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() - 1 ) );
					break;
			}
			$this.setDatePickerValue( new_date.format() );
			//$this.setDateUrl();

			//$this.buildCalendars();
			$this.search( false, true );
		} );

		date_right_arrow.bind( 'click', function() {
			var mode = $this.getMode();
			var select_date = $this.end_date;
			var new_date;
			if ( !select_date ) {
				return;
			}

			$this.clearSelection();

			switch ( mode ) {
				case ScheduleViewControllerMode.WEEK:
					var select_date = Global.strToDate( ( ( $this.getSelectDate() ) ? $this.getSelectDate() : new Date().format() ) );
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 7 ) );
					break;
				case ScheduleViewControllerMode.YEAR:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 56 ) ); //8 weeks
					break;
				case ScheduleViewControllerMode.DAY:
					if ( $this.settings_menu.getButton( 'strict_range_btn' ).active ) {
                        new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 1 ) );
                    } else {
                        new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() ) );
                    }
					break;
				case ScheduleViewControllerMode.MONTH:
				default:
					new_date = new Date( new Date( select_date.getTime() ).setDate( select_date.getDate() + 1 ) );
					break;
			}

			$this.setDatePickerValue( new_date.format() );
			//$this.setDateUrl();

			//$this.buildCalendars();
			$this.search( false, true );
		} );

		this.start_date_picker.bind( 'formItemChange', function() {
			//$this.setDateUrl();
			$this.clearSelection();
			$this.search( false, true );
		} );

		//Overwrite event listener on layout selector to prevent wrong start date issues by passing true on user_date_picker_date in search().
		$( this.search_panel.find( '#layout_selector' ) ).off( 'change' ).on( 'change', function() {
			$this.layout_changed = true;

			var selectId = $this.search_panel.find( '#layout_selector' ).find( 'option:selected' ).attr( 'value' );
			var layouts_array = $this.search_panel.getLayoutsArray();
			var len = layouts_array.length;
			for ( var i = 0; i < len; i++ ) {
				var item = layouts_array[i];

				if ( item.id == selectId ) {
					$this.select_layout = item;
					$this.setSelectLayout();
					$this.search( false, true );
					break;
				}

			}
			Global.triggerAnalyticsNavigationOther( 'searchpanel:layout_change', 'click', $this.viewId );
		} );

		// Vue dummy interface objects for legacy<->new vue menus.
		// TODO: Improve this by replacing with ES6 classes, and have a parent class of jQueryDummy, with methods for get, set etc, but tie them in with the view id / option bar id, and button id.

		this.toggle_button = (function( context ) {
			var _value = 'week';
			context.event_bus.on( context.vue_control_bar_mount_id, 'scheduleModeOnChange', updateValueOnChange, TTEventBusStatics.AUTO_CLEAR_ON_EXIT );


			function updateValueOnChange( event_data ) {
				Debug.Text( 'Toggle button value update! Change event received. ('+ event_data +').', 'ScheduleViewController.js', 'ScheduleViewController', 'updateValueOnChange', 10 );
				//Issue #3376 - Selecting the same date mode twice in a row causes null to be set as date mode value causing later exceptions.
				//This can happen due to double clicks or other yet discovered reasons. I have only reproduced the bug by selecting the same date mode twice in a row.
				//However, it is possible that the bug can be reproduced by other means and this fix should hopefully catch them.
				if ( !event_data.value ) {
					$this.toggle_button.setValue( $this.getMode() );
					return;
				}

				_value = event_data.value;
			}

			function getValue() {
				return _value;
			}

			function setValue( new_value ) {
				_value = new_value; // Set locally in the button immediately, because setToggleButtonValue triggers setToggleButtonUrl which gets the value before the original set command has a chance to send the update to vue and back again to the dummy interface.

				context.event_bus.emit( context.vue_control_bar_mount_id, 'setValue', {
					key: 'schedule_mode_options_selected',
					value: new_value
				});
			}

			return {
				getValue: getValue,
				setValue: setValue
			}
		})(this);

		// schedule_settings_options

		this.settings_menu = {
			//If issues happen with race conditions and execution ordeer, you can locally set the array value to the same
			// as what you are about to set the Vue component to, but this might have unexpected effects on the Vue reactive data.
			getButton( id ) {
				return schedule_settings_options.find( element => element.id === id );
			},
			enableButton( id ) {
				// set disable to false
				$this.event_bus.emit( $this.vue_control_bar_mount_id, 'setScheduleSettingsState', {
					item_id: id,
					item_field: 'disabled',
					item_value: false
				});
			},
			disableButton( id ) {
				// set disable to true
				$this.event_bus.emit( $this.vue_control_bar_mount_id, 'setScheduleSettingsState', {
					item_id: id,
					item_field: 'disabled',
					item_value: true
				});
			},
			activateButton( id ) {
				// set active to true
				$this.event_bus.emit( $this.vue_control_bar_mount_id, 'setSettingActive', {
					item_id: id
				});
			},
			deactivateButton( id ) {
				// set active to false
				$this.event_bus.emit( $this.vue_control_bar_mount_id, 'setSettingDeactivated', {
					item_id: id
				});
			}
		}

		function scheduleModeOnChangeHandler( event_data ) {
			Debug.Text( 'Schedule mode change event received. ('+ event_data +').', 'ScheduleViewController.js', 'ScheduleViewController', 'scheduleModeOnChangeHandler', 10 );

			$this.scroll_position = 0;
			$this.select_all_shifts_array = [];
			$this.select_shifts_array = [];
			$this.select_recurring_shifts_array = [];

			$this.setToggleButtonUrl();
			$this.search( true, true );
			$this.onSearch( true ); //Trigger saving of saved search & layout, but do not search to stop double search calls.
		}

		this.event_bus.on( this.vue_control_bar_mount_id, 'scheduleModeOnChange', scheduleModeOnChangeHandler, TTEventBusStatics.AUTO_CLEAR_ON_EXIT );

	}

	checkScheduleData() {
		if ( this.full_schedule_data === true ) {
			return false;
		}

		return true;
	}

	showWeeklyTotal() {
		var show_weekly_total = this.settings_menu.getButton( 'weekly_totals_btn' ).active && !this.settings_menu.getButton( 'weekly_totals_btn' ).disabled;

		if ( !show_weekly_total ) {
			return;
		}

		var shifts = 0;
		var absences = 0;
		var total_time = 0;
		var total_wage = 0;
//		  var is_date_row = false;
//		  var current_key = [];

		var mode = this.getMode();

		var len = this.schedule_source.length;

		for ( var i = 0; i < len; i++ ) {
			var row = this.schedule_source[i];

			if ( row.type === ScheduleViewControllerRowType.DATE ) {
				continue;
			}

			for ( var key in row ) {

				//As data comes from the grid, we can't be sure of the types of any data within it. (Same goes for item below)
				var data = Global.isSet( row[key] ) ? row[key] : '';

				if ( Global.isSet( data.user_id ) || ( Global.isArray( data ) && mode === ScheduleViewControllerMode.YEAR && data.every( item => item.total_time_wage ) ) ) {

					if ( mode === ScheduleViewControllerMode.YEAR ) {
						var data_len = data.length;
						for ( var j = 0; j < data_len; j++ ) {
							var item = data[j];
							item.total_time_wage = parseFloat( item.total_time_wage );
							item.total_time = parseFloat( item.total_time );

							if ( Global.isSet( item.user_id ) ) {
								total_wage = ( item.total_time_wage + total_wage );
								if ( item.status_id == 10 ) {
									total_time = ( item.total_time + total_time );
									shifts = shifts + 1;
								} else if ( data.status_id == 20 && data.absence_policy_id != TTUUID.zero_id && data.absence_policy_id != TTUUID.not_exist_id ) { //&& data.total_time_wage != 0
									total_time = ( item.total_time + total_time );
									absences = absences + 1;
								}
							}
						}
					} else {
						data.total_time_wage = parseFloat( data.total_time_wage );
						data.total_time = parseFloat( data.total_time );
						total_wage = ( data.total_time_wage + total_wage );
						if ( data.status_id == 10 ) {
							total_time = ( data.total_time + total_time );
							shifts = shifts + 1;
						} else if ( data.status_id == 20 && data.absence_policy_id != TTUUID.zero_id && data.absence_policy_id != TTUUID.not_exist_id ) { //&& data.total_time_wage != 0
							total_time = ( data.total_time + total_time );
							absences = absences + 1;
						}
					}

				} else if ( Global.isSet( data.shifts ) ) {
					data.total_time_wage = parseFloat( data.total_time_wage );
					data.total_time = parseFloat( data.total_time );

					total_time = ( data.total_time + total_time );
					total_wage = ( data.total_time_wage + total_wage );

					shifts = shifts + data.shifts;
					absences = absences + data.absences;
				} else if ( row.type !== ScheduleViewControllerRowType.DATE ) {

				} else {
//					  current_key[key] = row[key];
				}
			}

			row.total_time = Global.getTimeUnit( total_time );
			row.total_time_wage = LocalCacheData.getCurrentCurrencySymbol() + Global.MoneyRound( total_wage );
			//ViewManagerUtil.getTimeUnit(totalTime);
			row.shifts = shifts;
			row.absences = absences;

			total_time = 0;
			total_wage = 0;
			shifts = 0;
			absences = 0;
		}
	}

	buildTotalShiftsValues( total_shifts_dic, currentItem ) {
		var start_date = Global.strToDateTime( currentItem.start_date );
		var end_date = Global.strToDateTime( currentItem.end_date );
		var start_time_min = start_date.getMinutes();
		var end_time_min = end_date.getMinutes();

		start_date.setMinutes( 0 );
		end_date.setMinutes( 0 );

		var start_time = start_date.format( 'hA' );
		var end_time = end_date.format( 'hA' );

		var time_offset = Math.ceil( getTimeOffset( start_date, end_date ) / 60 / 60 );

		var rest_time;
		if ( time_offset < 1 ) {

			total_shifts_dic[end_time].value = total_shifts_dic[end_time].value + Number( ( end_time_min / 60 ).toFixed( 0 ) );
		} else {
			if ( start_time_min == 0 ) {

				total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + 1;
			} else {
				total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + Number( ( ( 60 - start_time_min ) /
					60 ).toFixed( 2 ) );
			}

			for ( var i = 1; i < time_offset; i++ ) {
				start_date.setHours( start_date.getHours() + 1 );
				//start_time = start_date.format( 'hh:mm A' );
				start_time = start_date.format( 'hA' );

				if ( !total_shifts_dic.hasOwnProperty( start_time ) ) {
					continue;
				}

				if ( i == time_offset - 1 ) {
					if ( end_time_min > 0 ) {
						total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + Number( ( end_time_min /
							60 ).toFixed( 2 ) );
					} else {
						total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + 1;
					}
				} else {
					total_shifts_dic[start_time].value = total_shifts_dic[start_time].value + 1;
				}

			}

		}

		function getTimeOffset( startDate, endDate ) {
			if ( !startDate ) {
				startDate = new Date();
			}
			var sec = ( endDate.getTime() - startDate.getTime() ) / 1000;
			return sec.toFixed( 0 );
		}
	}

	showDailyTotal() {
		var show_daily_total = this.settings_menu.getButton( 'daily_totals_btn' ).active && !this.settings_menu.getButton( 'daily_totals_btn' ).disabled;

		if ( !show_daily_total ) {
			return;
		}

		var start = true;
		var total_row = null;
		var column_keys = [];
		var over_all_total_row = {};

		var display_columns = this.select_layout.data.display_columns;
		var display_columns_len = display_columns.length;

		for ( var i = 0; i < display_columns_len; i++ ) {
			var column_name = display_columns[i];

			column_keys.push( { key: column_name, row: null, value: null } );

		}

		var column_keys_len = column_keys.length;

		for ( var i = 0; i < this.schedule_source.length; i++ ) {
			var row = this.schedule_source[i];
			if ( start ) {

				for ( var j = 0; j < column_keys_len; j++ ) {
					var column_key = column_keys[j];
					total_row = { type: ScheduleViewControllerRowType.TOTAL };
					total_row[column_key.key] = $.i18n._( 'Totals' );
					column_key.row = total_row;
					column_key.value = row[column_key.key];

				}

				over_all_total_row = { type: ScheduleViewControllerRowType.TOTAL };
				over_all_total_row.user_full_name = $.i18n._( 'Overall Totals' );
			}

			for ( var y = column_keys_len - 1; y >= 0; y-- ) {

				column_key = column_keys[y];

				if ( ( row[column_key.key] !== column_key.value && i > 0 ) && !start && i !== 0 ) {

					this.schedule_source.splice( i, 0, column_key.row );
					i = i + 1;
					total_row = { type: ScheduleViewControllerRowType.TOTAL };
					total_row[column_key.key] = $.i18n._( 'Totals' );
					column_key.row = total_row;
					column_key.value = row[column_key.key];
				}

			}

			if ( start ) {
				start = false;
			}
			if ( row.type === ScheduleViewControllerRowType.DATE ) { //do not calculate date row
				this.schedule_source.splice( i, 0, over_all_total_row );
				over_all_total_row = { type: ScheduleViewControllerRowType.TOTAL };
				over_all_total_row.user_full_name = $.i18n._( 'Overall Totals' );
				i = i + 1;

				continue;
			}

			for ( var key in row ) {

				var data = Global.isSet( row[key] ) ? row[key] : '';
				for ( var x = 0; x < column_keys_len; x++ ) {

					column_key = column_keys[x];
					total_row = column_key.row;

					//Total rows for each columns
					if ( data && Global.isSet( data.user_id ) ) {

						var no_data_key = key.replace( '_data', '' );

						var total_row_key_data = total_row[no_data_key];

						if ( !total_row_key_data || !Global.isSet( total_row_key_data.total_time ) ) {
							total_row[no_data_key] = {};
							total_row_key_data = total_row[no_data_key];
							total_row_key_data.total_time = 0;
							total_row_key_data.total_shifts_dic = Global.clone( this.total_shifts_dic );
						} else {
							//#2381 - total_time can be a string from the API
							total_row_key_data.total_time = parseInt( total_row_key_data.total_time );
						}

						if ( !Global.isSet( total_row_key_data.shifts ) ) {
							total_row_key_data.shifts = 0;
						}

						if ( !Global.isSet( total_row_key_data.absences ) ) {
							total_row_key_data.absences = 0;
						}

						if ( !Global.isSet( total_row_key_data.total_time_wage ) ) {
							total_row_key_data.total_time_wage = 0;
						}

						var row_data = row[no_data_key + '_data'];
						//#2381 - total_time can be a string from the API
						row_data.total_time = parseInt( row_data.total_time );

						total_row_key_data.total_time_wage = Global.MoneyRound( parseFloat( parseFloat( row_data.total_time_wage ) + parseFloat( total_row_key_data.total_time_wage ) ) );
						if ( row_data.status_id == 10 ) {
							total_row_key_data.total_time = parseFloat( row_data.total_time ) + parseFloat( total_row_key_data.total_time );
							total_row_key_data.shifts = total_row_key_data.shifts + 1;
							if ( this.getMode() === ScheduleViewControllerMode.DAY ) {
								this.buildTotalShiftsValues( total_row_key_data.total_shifts_dic, row[key] );
							}
						} else if ( row_data.status_id == 20 && row_data.absence_policy_id != TTUUID.zero_id && row_data.absence_policy_id != TTUUID.not_exist_id ) { //&& row_data.total_time_wage != 0
							total_row_key_data.total_time = parseFloat( row_data.total_time ) + parseFloat( total_row_key_data.total_time );
							total_row_key_data.absences = total_row_key_data.absences + 1;
						}

					}

				}

				//Total rows for all employees

				if ( data && Global.isSet( data.user_id ) ) {

					no_data_key = key.replace( '_data', '' );

					total_row_key_data = over_all_total_row[no_data_key];

					if ( !total_row_key_data || !Global.isSet( total_row_key_data.total_time ) ) {
						over_all_total_row[no_data_key] = {};
						total_row_key_data = over_all_total_row[no_data_key];
						total_row_key_data.total_time = 0;
						total_row_key_data['total_shifts_dic'] = Global.clone( this.total_shifts_dic );
					}

					if ( !Global.isSet( total_row_key_data.shifts ) ) {
						total_row_key_data.shifts = 0;
					}

					if ( !Global.isSet( total_row_key_data.absences ) ) {
						total_row_key_data.absences = 0;
					}

					if ( !Global.isSet( total_row_key_data.total_time_wage ) ) {
						total_row_key_data.total_time_wage = 0;
					}

					row_data = row[no_data_key + '_data'];

					total_row_key_data.total_time_wage = Global.MoneyRound( parseFloat( parseFloat( row_data.total_time_wage ) + parseFloat( total_row_key_data.total_time_wage ) ) );
					if ( row_data.status_id == 10 ) {
						total_row_key_data.total_time = parseFloat( row_data.total_time ) + parseFloat( total_row_key_data.total_time );
						total_row_key_data.shifts = total_row_key_data.shifts + 1;
						if ( this.getMode() === ScheduleViewControllerMode.DAY ) {
							this.buildTotalShiftsValues( total_row_key_data.total_shifts_dic, row[key] );
						}
					} else if ( row_data.status_id == 20 && row_data.absence_policy_id != TTUUID.zero_id && row_data.absence_policy_id != TTUUID.not_exist_id ) { //&& row_data.total_time_wage != 0
						total_row_key_data.total_time = parseFloat( row_data.total_time ) + parseFloat( total_row_key_data.total_time );
						total_row_key_data.absences = total_row_key_data.absences + 1;
					}

				}
			}

			if ( i === this.schedule_source.length - 1 ) {
				for ( var j = column_keys.length - 1; j >= 0; j-- ) {
					this.schedule_source.push( column_keys[j].row );
				}
				this.schedule_source.push( over_all_total_row );
				break;
			}
		}
	}

	buildSearchFields() {
		super.buildSearchFields();
		var $this = this;

		var default_args = { permission_section: 'schedule' };

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.search_fields = [
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
					label: $.i18n._( 'Schedule Branch' ),
					in_column: 1,
					field: 'schedule_branch_ids',
					layout_name: 'global_branch',
					api_class: TTAPI.APIBranch,
					multiple: true,
					basic_search: true,
					adv_search: true,
					script_name: 'BranchView',
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Schedule Department' ),
					in_column: 1,
					field: 'department_ids',
					layout_name: 'global_department',
					api_class: TTAPI.APIDepartment,
					multiple: true,
					basic_search: true,
					adv_search: true,
					script_name: 'DepartmentView',
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Default Branch' ),
					in_column: 1,
					field: 'default_branch_ids',
					layout_name: 'global_branch',
					api_class: TTAPI.APIBranch,
					multiple: true,
					basic_search: false,
					adv_search: true,
					script_name: 'BranchView',
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Default Department' ),
					in_column: 1,
					field: 'default_department_ids',
					layout_name: 'global_department',
					api_class: TTAPI.APIDepartment,
					multiple: true,
					basic_search: false,
					adv_search: true,
					script_name: 'DepartmentView',
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Group' ),
					in_column: 2,
					multiple: true,
					field: 'group_ids',
					layout_name: 'global_tree_column',
					tree_mode: true,
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
					basic_search: true,
					adv_search: true,
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Job' ),
					in_column: 2,
					field: 'job_id',
					layout_name: 'global_job',
					api_class: ( Global.getProductEdition() >= 20 ) ? TTAPI.APIJob : null,
					multiple: true,
					basic_search: false,
					adv_search: ( this.show_job_ui && ( Global.getProductEdition() >= 20 ) ),
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Task' ),
					in_column: 2,
					field: 'job_item_id',
					layout_name: 'global_job_item',
					api_class: ( Global.getProductEdition() >= 20 ) ? TTAPI.APIJobItem : null,
					multiple: true,
					basic_search: false,
					adv_search: ( this.show_job_item_ui && ( Global.getProductEdition() >= 20 ) ),
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Punch Tags' ),
					in_column: 2,
					field: 'punch_tag_id',
					layout_name: 'global_punch_tag',
					api_class: ( Global.getProductEdition() >= 20 ) ? TTAPI.APIPunchTag : null,
					multiple: true,
					basic_search: false,
					adv_search: ( this.show_punch_tag_ui && ( Global.getProductEdition() >= 20 ) ),
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Recurring Template' ),
					field: 'recurring_schedule_template_control_id',
					in_column: 3,
					layout_name: 'global_recurring_schedule_control',
					api_class: TTAPI.APIRecurringScheduleTemplateControl,
					multiple: true,
					basic_search: false,
					adv_search: true,
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Absence Policy' ),
					field: 'absence_policy_id',
					in_column: 3,
					layout_name: 'global_absences',
					api_class: TTAPI.APIAbsencePolicy,
					multiple: true,
					basic_search: false,
					adv_search: true,
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Include Employees' ),
					in_column: 3,
					field: 'include_user_ids',
					layout_name: 'global_user',
					api_class: TTAPI.APIUser,
					multiple: true,
					basic_search: false,
					adv_search: true,
					default_args: default_args,
					addition_source_function: ( function( target, source_data ) {
						return $this.onEmployeeSourceCreate( target, source_data );
					} ),
					form_item_type: FormItemType.AWESOME_BOX
				} ),

				new SearchField( {
					label: $.i18n._( 'Exclude Employees' ),
					in_column: 3,
					field: 'exclude_user_ids',
					layout_name: 'global_user',
					api_class: TTAPI.APIUser,
					multiple: true,
					basic_search: false,
					adv_search: true,
					default_args: default_args,
					addition_source_function: ( function( target, source_data ) {
						return $this.onEmployeeSourceCreate( target, source_data );
					} ),
					form_item_type: FormItemType.AWESOME_BOX
				} )
			];
		} else {
			//Allow regular employees to add job/task columns if needed, and do some other basic searches.
			this.search_fields = [];

			this.search_fields.push(
				new SearchField( {
					label: $.i18n._( 'Status' ),
					in_column: 1,
					field: 'status_id',
					multiple: true,
					basic_search: true,
					adv_search: false,
					layout_name: 'global_option_column',
					form_item_type: FormItemType.AWESOME_BOX
				} ) );

			//Check punch permissions rather than schedule, since this is a regular employee who likely wouldn't have
			if ( PermissionManager.validate( 'punch', 'edit_branch' ) || PermissionManager.validate( this.permission_id, 'edit_branch' ) ) {
				this.search_fields.push(
					new SearchField( {
						label: $.i18n._( 'Schedule Branch' ),
						in_column: 1,
						field: 'schedule_branch_ids',
						layout_name: 'global_branch',
						api_class: TTAPI.APIBranch,
						multiple: true,
						basic_search: true,
						adv_search: false,
						script_name: 'BranchView',
						form_item_type: FormItemType.AWESOME_BOX
					} ) );
			}

			if ( PermissionManager.validate( 'punch', 'edit_department' ) || PermissionManager.validate( this.permission_id, 'edit_department' ) ) {
				this.search_fields.push(
					new SearchField( {
						label: $.i18n._( 'Schedule Department' ),
						in_column: 1,
						field: 'department_ids',
						layout_name: 'global_department',
						api_class: TTAPI.APIDepartment,
						multiple: true,
						basic_search: true,
						adv_search: false,
						script_name: 'DepartmentView',
						form_item_type: FormItemType.AWESOME_BOX
					} ) );
			}

			//Could be permission issues with this, so disable for now.
			// if ( Global.getProductEdition() >= 20 && ( PermissionManager.validate( 'punch', 'edit_job' ) || PermissionManager.validate( this.permission_id, 'edit_job' ) ) ) {
			// 	this.search_fields.push(
			// 		new SearchField( {
			// 			label: $.i18n._( 'Job' ),
			// 			in_column: 2,
			// 			field: 'job_id',
			// 			layout_name: 'global_job',
			// 			api_class: ( Global.getProductEdition() >= 20 ) ? TTAPI.APIJob : null,
			// 			multiple: true,
			// 			basic_search: true,
			// 			adv_search: false,
			// 			form_item_type: FormItemType.AWESOME_BOX
			// 		} ) );
			// }
			//
			// if ( Global.getProductEdition() >= 20 && ( PermissionManager.validate( 'punch', 'edit_job_item' ) || PermissionManager.validate( this.permission_id, 'edit_job_item' ) ) ) {
			// 	this.search_fields.push(
			// 		new SearchField( {
			// 			label: $.i18n._( 'Task' ),
			// 			in_column: 2,
			// 			field: 'job_item_id',
			// 			layout_name: 'global_job_item',
			// 			api_class: ( Global.getProductEdition() >= 20 ) ? TTAPI.APIJobItem : null,
			// 			multiple: true,
			// 			basic_search: true,
			// 			adv_search: false,
			// 			form_item_type: FormItemType.AWESOME_BOX
			// 		} ) );
			// }

		}
	}

	onSourceDataCreate( target, source_data ) {

		//if ( !this.is_mass_adding ) {
		//	return source_data;
		//}
		var $this = this;
		var display_columns = target.getDisplayColumns();
		var first_item = {};
		var second_item = {};

		//FIXME: what should we do about -2?

		$.each( display_columns, function( index, content ) {
			first_item.id = TTUUID.not_exist_id;
			first_item[content.name] = Global.default_item;
			if ( $this.select_cells_Array.length > 0 && !$this.is_mass_editing ) {
				second_item.id = '-2';
				second_item[content.name] = Global.selected_item;
			}
			return false;
		} );

		//Error: Object doesn't support property or method 'unshift' in /interface/html5/line 6953
		if ( !source_data || $.type( source_data ) !== 'array' ) {
			source_data = [];
		}
		if ( this.select_cells_Array.length > 0 && !$this.is_mass_editing ) {
			source_data.unshift( second_item );
		}
		source_data.unshift( first_item );

		return source_data;
	}

	onSourceDataCreatePunchTag( target, source_data ) {

		//if ( !this.is_mass_adding ) {
		//	return source_data;
		//}
		var $this = this;
		var display_columns = target.getDisplayColumns();
		var first_item = {};

		$.each( display_columns, function( index, content ) {
			first_item.id = TTUUID.not_exist_id;
			first_item[content.name] = Global.default_item;
			return false;
		} );

		//Error: Object doesn't support property or method 'unshift' in /interface/html5/line 6953
		if ( !source_data || $.type( source_data ) !== 'array' ) {
			source_data = [];
		}

		source_data.unshift( first_item );

		return source_data;
	}

	onEmployeeSourceCreate( target, source_data ) {

		if ( !this.checkOpenPermission() ) {
			return source_data;
		}

		var display_columns = target.getDisplayColumns();

		var first_item = {};
		$.each( display_columns, function( index, content ) {

			first_item.id = TTUUID.zero_id;
			first_item[content.name] = Global.open_item;

			return false;
		} );

		//Error: Object doesn't support property or method 'unshift' in /interface/html5/line 6953
		if ( !source_data || $.type( source_data ) !== 'array' ) {
			source_data = [];
		}
		source_data.unshift( first_item );

		return source_data;
	}

	addOPenField( target, source_data ) {
		var open_field = {};
	}

	cleanWhenUnloadView( callBack ) {
		this.event_bus.autoClear(); // TODO: Once Schedule proof of concept works, move this to the BaseView function to apply to all views.
		TTVueUtils.unmountComponent( this.vue_control_bar_mount_id );
		$( '#schedule_view_container' ).remove();
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
}

ScheduleViewController.html_template = `
<div class="view schedule-view" id="schedule_view_container">
	<div class="clear-both-div"></div>
	<div id="vue-schedule-control-bar"></div>
	<div class="clear-both-div"></div>
	<div class="grid-top-border schedule-top-border"></div>
	<div class="grid-div schedule-grid-div">
		<table id="grid"></table>
	</div>
	<div class="grid-bottom-border"></div>
</div>`;

var ScheduleViewControllerRowType = function() {

};

ScheduleViewControllerRowType.TOTAL = 1;
ScheduleViewControllerRowType.DATE = 2;
ScheduleViewControllerRowType.EMPTY = 3;

var ScheduleViewControllerMode = function() {

};

ScheduleViewControllerMode.DAY = 'day';
ScheduleViewControllerMode.WEEK = 'week';
ScheduleViewControllerMode.MONTH = 'month';
ScheduleViewControllerMode.YEAR = 'year';

