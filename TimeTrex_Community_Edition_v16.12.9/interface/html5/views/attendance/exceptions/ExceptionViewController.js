export class ExceptionViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#exception_view_container',
			status_array: null,


		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'ExceptionEditView.html';
		this.permission_id = 'punch';
		this.viewId = 'Exception';
		this.script_name = 'ExceptionView';
		this.context_menu_name = $.i18n._( 'Exceptions' );
		this.navigation_label = $.i18n._( 'Exception' );
		this.api = TTAPI.APIException;

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'edit_employee':
			case 'edit_pay_period':
			case 'edit_pay_period_schedule':
			case 'schedule':
			case 'timesheet':
			case 'send_message':
				this.onNavigationClick( id );
				break;
		}
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'timesheet':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
				break;
			case 'schedule':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'schedule' );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditEmployeeIcon( context_btn, grid_selected_length );
				break;
			case 'edit_pay_period_schedule':
				this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'pay_period_schedule' );
				break;
			case 'edit_pay_period':
				this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'pay_period_schedule' );
				break;
			case 'send_message':
				this.setDefaultMenuSendMessageIcon( context_btn, grid_selected_length );
				break;
		}
	}

	initPermission() {

		super.initPermission();

		if ( PermissionManager.validate( this.permission_id, 'view' ) || PermissionManager.validate( this.permission_id, 'view_child' ) ) {
			this.show_search_tab = true;
		} else {
			this.show_search_tab = false;
		}
	}

	autoOpenEditViewIfNecessary() {
		//Auto open edit view. Should set in IndexController
		//Don't have any edit view
		//Error: Uncaught TypeError: undefined is not a function in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-084605 line 2751
		this.autoOpenEditOnlyViewIfNecessary();
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

	setDefaultMenuSendMessageIcon( context_btn, grid_selected_length, pId ) {
		if ( grid_selected_length > 0 ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onNavigationClick( iconName ) {
		var select_item = this.getSelectedItem();
		//There are cases where select_item might be null. The export button for example.
		if ( select_item != null ) {
			var user_id = select_item.user_id;
		}
		switch ( iconName ) {
			case 'edit_employee':
				IndexViewController.openEditView( this, 'Employee', user_id );
				break;
			case 'edit_pay_period':
				var pay_period_id = select_item.pay_period_id;
				if ( pay_period_id ) {
					IndexViewController.openEditView( this, 'PayPeriods', pay_period_id );
				}
				break;
			case 'edit_pay_period_schedule':
				var pay_period_schedule_id = select_item.pay_period_schedule_id;
				if ( pay_period_schedule_id ) {
					IndexViewController.openEditView( this, 'PayPeriodSchedule', pay_period_id );
				}
				break;
			case 'schedule':
				var filter = { filter_data: {} };
				var include_users = { value: [user_id] };
				filter.filter_data.include_user_ids = include_users;
				filter.select_date = select_item.date_stamp;
				Global.addViewTab( this.viewId, $.i18n._( 'Exception' ), window.location.href );
				IndexViewController.goToView( 'Schedule', filter );
				break;
			case 'timesheet':
				filter = { filter_data: {} };
				filter.user_id = user_id;
				filter.base_date = select_item.date_stamp;
				Global.addViewTab( this.viewId, $.i18n._( 'Exception' ), window.location.href );
				IndexViewController.goToView( 'TimeSheet', filter );
				break;
			case 'send_message':
				LocalCacheData.default_filter_for_next_open_view = { to_user_id: _.uniq( this.getSelectedItems().map( exception => exception.user_id ) ) };
				IndexViewController.openEditView( this, 'MessageControl', null, 'onAddClick' );
				break;
		}
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'status', field_name: 'user_status_id', api: TTAPI.APIUser },
			{ option_name: 'severity', api: TTAPI.APIExceptionPolicy },
			{ option_name: 'type', field_name: 'exception_policy_type_id', api: TTAPI.APIExceptionPolicy },
		];

		this.initDropDownOptions( options );

		var user_group_api = TTAPI.APIUserGroup;
		user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {

				res = res.getResult();
				res = Global.buildTreeRecord( res );

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['group_id'] ) {
					$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
					$this.adv_search_field_ui_dic['group_id'].setSourceData( res );
				}

			}
		} );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['default'],
			include: [
				{
					label: $.i18n._( 'Send Message' ),
					id: 'send_message',
					menu_align: 'right',
					vue_icon: 'tticon tticon-send_black_24dp',
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
					label: $.i18n._( 'Schedules' ),
					id: 'schedule',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Edit Employee' ),
					id: 'edit_employee',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Edit Pay Period' ),
					id: 'edit_pay_period',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Edit PP Schedule' ),
					id: 'edit_pay_period_schedule',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
				},
				{
					label: $.i18n._( 'Export' ),
					id: 'export_excel',
					group: 'other',
					vue_icon: 'tticon tticon-file_upload_black_24dp',
					sort_order: 9000
				}
			]
		};

		return context_menu_model;
	}

	getSearchPanelFilter( getFromTabIndex, save_temp_filter ) {

		if ( Global.isSet( getFromTabIndex ) ) {
			var search_tab_select_index = getFromTabIndex;
		} else {
			search_tab_select_index = this.search_panel.getSelectTabIndex();
		}

//		var basic_fields_len = this.search_fields.length;
		var target_ui_dic = null;

		if ( search_tab_select_index === 0 ) {
			this.filter_data = [];
			target_ui_dic = this.basic_search_field_ui_dic;
		} else if ( search_tab_select_index === 1 ) {
			this.filter_data = [];
			target_ui_dic = this.adv_search_field_ui_dic;
		} else {
			return;
		}

		var $this = this;
		$.each( target_ui_dic, function( key, content ) {

			$this.filter_data[key] = { field: key, id: '', value: target_ui_dic[key].getValue( true ) };

			if ( key === 'show_pre_mature' && $this.filter_data[key].value !== true ) {

				delete $this.filter_data[key];
				return false;
			}

			if ( $this.temp_basic_filter_data ) {
				$this.temp_basic_filter_data[key] = $this.filter_data[key];
			}

			if ( $this.temp_adv_filter_data ) {
				$this.temp_adv_filter_data[key] = $this.filter_data[key];
			}
		} );

		if ( save_temp_filter ) {
			if ( search_tab_select_index === 0 ) {
				$this.temp_basic_filter_data = Global.clone( $this.filter_data );
			} else if ( search_tab_select_index === 1 ) {
				$this.temp_adv_filter_data = Global.clone( $this.filter_data );
			}

		}
	}

	onGridDblClickRow() {
		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var len = context_menu_array.length;

		var need_break = false;

		for ( var i = 0; i < len; i++ ) {

			if ( need_break ) {
				break;
			}

			let context_btn = context_menu_array[i];
			let id = context_menu_array[i].id;

			switch ( id ) {
				case 'timesheet':
					if ( context_btn.visible && !context_btn.disabled ) {
						this.onNavigationClick( 'timesheet' );
						return;
					}
					break;
			}
		}

		for ( var i = 0; i < len; i++ ) {

			if ( need_break ) {
				break;
			}

			let context_btn = context_menu_array[i];
			let id = context_menu_array[i].id;

			switch ( id ) {
				case 'schedule':
					need_break = true;
					if ( context_btn.visible && !context_btn.disabled ) {
						this.onNavigationClick( 'schedule' );
						return;
					}
					break;
			}
		}
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Pay Period' ),
				in_column: 1,
				field: 'pay_period_id',
				layout_name: 'global_Pay_period',
				api_class: TTAPI.APIPayPeriod,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Severity' ),
				in_column: 1,
				field: 'severity_id',
				multiple: true,
				adv_search: true,
				basic_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Exception' ),
				in_column: 1,
				field: 'exception_policy_type_id',
				multiple: true,
				adv_search: true,
				basic_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
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
				label: $.i18n._( 'Branch' ),
				in_column: 2,
				field: 'branch_id',
				layout_name: 'global_branch',
				api_class: TTAPI.APIBranch,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Department' ),
				field: 'department_id',
				in_column: 2,
				layout_name: 'global_department',
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Title' ),
				in_column: 3,
				field: 'title_id',
				layout_name: 'global_job_title',
				api_class: TTAPI.APIUserTitle,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Show Pre-Mature' ),
				in_column: 3,
				field: 'show_pre_mature',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.CHECKBOX
			} )

		];
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.user_id = true;
		column_filter.date_stamp = true;
		column_filter.pay_period_id = true;
		column_filter.pay_period_schedule_id = true;
		column_filter.exception_color = true;
		column_filter.exception_background_color = true;

		return this._getFilterColumnsFromDisplayColumns( column_filter, true );
	}

	setGridCellBackGround() {
		var data = this.grid?.getGridParam( 'data' );
		//Error: TypeError: data is undefined in /interface/html5/framework/jquery.min.js?v=7.4.6-20141027-074127 line 2 > eval line 70
		if ( !data ) {
			return;
		}

		var len = data.length;

		for ( var i = 0; i < len; i++ ) {
			var item = data[i];

			if ( item.exception_background_color ) {
				var severity = $( 'tr[id=\'' + item.id + '\']' ).find( 'td[aria-describedby="' + this.ui_id + '_grid_severity"]' );
				severity.css( 'background-color', item.exception_background_color );
				severity.css( 'font-weight', 'bold' );
			}

			if ( item.exception_color ) {
				var code = $( 'tr[id=\'' + item.id + '\']' ).find( 'td[aria-describedby="' + this.ui_id + '_grid_exception_policy_type_id"]' );
				code.css( 'color', item.exception_color );
				code.css( 'font-weight', 'bold' );
			}

		}
	}
}

ExceptionViewController.loadView = function() {

	Global.loadViewSource( 'Exception', 'ExceptionView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		Global.contentContainer().html( template( args ) );
	} );

};