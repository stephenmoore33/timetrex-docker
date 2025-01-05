export class RecurringScheduleControlViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#recurring_schedule_control_view_container',


			user_status_array: null,

			user_group_array: null,
			user_api: null,

			user_group_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'RecurringScheduleControlEditView.html';
		this.permission_id = 'recurring_schedule';
		this.viewId = 'RecurringScheduleControl';
		this.script_name = 'RecurringScheduleControlView';
		this.table_name_key = 'recurring_schedule_control';
		this.context_menu_name = $.i18n._( 'Recurring Schedules' );
		this.navigation_label = $.i18n._( 'Recurring Schedule' );
		this.api = TTAPI.APIRecurringScheduleControl;
		this.user_api = TTAPI.APIUser;
		this.user_group_api = TTAPI.APIUserGroup;
		this.event_bus = new TTEventBus({ view_id: this.viewId });

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status', 'user_status_id', this.user_api, function( res ) {
			res = res.getResult();
			$this.user_status_array = Global.buildRecordArray( res );
		} );

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

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_recurring_schedule': { 'label': $.i18n._( 'Recurring Schedule' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;
		var widgetContainer;

		this.navigation.AComboBox( {
			api_class: TTAPI.APIRecurringScheduleControl,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_recurring_schedule_control',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_recurring_schedule = this.edit_view_tab.find( '#tab_recurring_schedule' );

		var tab_recurring_schedule_column1 = tab_recurring_schedule.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_recurring_schedule_column1 );

		// Template
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIRecurringScheduleTemplateControl,
			allow_multiple_selection: false,
			layout_name: 'global_recurring_template_control',
			show_search_inputs: true,
			set_empty: true,
			field: 'recurring_schedule_template_control_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Template' ), form_item_input, tab_recurring_schedule_column1, '' );

		// Start Week
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'start_week', width: 40 } );
		this.addEditFieldToColumn( $.i18n._( 'Template Start Week' ), form_item_input, tab_recurring_schedule_column1 );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'start_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_recurring_schedule_column1, '', null );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'end_date' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var label = $( '<span class=\'widget-right-label\'>' + $.i18n._( '(Leave blank for no end date)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_recurring_schedule_column1, '', widgetContainer );

		// Display Weeks
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'display_weeks', width: 20 } );
		this.addEditFieldToColumn( $.i18n._( 'Display Weeks' ), form_item_input, tab_recurring_schedule_column1, '', null );

		if ( ( Global.getProductEdition() >= 15 ) ) {
			// Auto-Punch
			form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
			form_item_input.TCheckbox( { field: 'auto_fill' } );
			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
			label = $( '<span class=\'widget-right-label t-checkbox-padding-left\'> (' + $.i18n._( 'Punches employees in/out automatically' ) + ')</span>' );
			widgetContainer.append( form_item_input );
			widgetContainer.append( label );
			this.addEditFieldToColumn( $.i18n._( 'Auto-Punch' ), form_item_input, tab_recurring_schedule_column1, '', widgetContainer );
		}

		// Employees
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		var default_args = {};
		if ( ( Global.getProductEdition() >= 15 ) ) {
			form_item_input.AComboBox( {
				api_class: TTAPI.APIUser,
				allow_multiple_selection: this.is_add === true,
				layout_name: 'global_user',
				show_search_inputs: true,
				set_any: true,
				field: 'user_id',
				custom_first_label: Global.empty_item,
				addition_source_function: function( target, source_data ) {

					if ( !source_data ) {
						return source_data;
					}

					var first_item = form_item_input.createItem( TTUUID.zero_id, Global.open_item );
					source_data.unshift( first_item );
					return source_data;

				},
				added_items: [
					{ value: TTUUID.zero_id, label: Global.open_item }
				]

			} );

			default_args.permission_section = 'recurring_schedule';
			form_item_input.setDefaultArgs( default_args );
		} else {
			form_item_input.AComboBox( {
				api_class: TTAPI.APIUser,
				allow_multiple_selection: this.is_add === true,
				layout_name: 'global_user',
				show_search_inputs: true,
				set_any: true,
				custom_first_label: Global.empty_item,
				field: 'user_id'
			} );
			default_args.permission_section = 'recurring_schedule';
			form_item_input.setDefaultArgs( default_args );
		}

		this.addEditFieldToColumn( $.i18n._( 'Employees' ), form_item_input, tab_recurring_schedule_column1, '', null, true );
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
				label: $.i18n._( 'Template' ),
				in_column: 1,
				field: 'recurring_schedule_template_control_id',
				layout_name: 'global_recurring_template_control',
				api_class: TTAPI.APIRecurringScheduleTemplateControl,
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
				addition_source_function: this.onEmployeeSourceCreate,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Group' ),
				in_column: 1,
				multiple: true,
				field: 'group_id',
				layout_name: 'global_tree_column',
				tree_mode: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Title' ),
				field: 'title_id',
				in_column: 1,
				layout_name: 'global_job_title',
				api_class: TTAPI.APIUserTitle,
				multiple: true,
				basic_search: false,
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
				script_name: 'BranchView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Department' ),
				in_column: 2,
				field: 'default_department_id',
				layout_name: 'global_department',
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'DepartmentView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 2,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 2,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} )
		];
	}

	onEmployeeSourceCreate( target, source_data ) {

		if ( Global.getProductEdition() <= 10 ) {
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
					label: $.i18n._( 'Recurring Templates' ),
					id: 'recurring_template',
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
				}
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'recurring_template':
			case 'schedule':
			case 'export_excel':
				this.onNavigationClick( id );
				break;
		}
	}

	/*
 	* Common functions used by view and edit onclick logic
 	*/

	onMassEditClick() {

		var $this = this;
		$this.is_add = false;
		$this.is_viewing = false;
		$this.is_mass_editing = true;
		LocalCacheData.current_doing_context_action = 'mass_edit';
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

				if ( result_data.id ) {
					$this.onEditClick( result_data.id );
					return;
				}

				$this.openEditView();

				if ( !result_data ) {
					result_data = [];
				}

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
								$this.initEditView();

							}
						} );

					}
				} );

			}
		} );
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

	onSaveClick( ignoreWarning ) {
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}

		var record;
		//Setting is_add false too early can cause determineContextMenuMountAttributes() to have unexpected side effects. However not setting it here might have other side effects.
		//this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save';

		if ( this.is_mass_editing ) {
			var changed_fields = this.getChangedFields();
			record = this.buildMassEditSaveRecord( this.mass_edit_record_ids, changed_fields );

		} else if ( this.is_mass_adding ) {
			record = this.buildMassAddRecord( this.current_edit_record );

		} else {
			record = this.getRecordsFromUserIDs();
		}

		this.doSaveAPICall( record, ignoreWarning );
	}

	onSaveAndCopy( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var record = this.getRecordsFromUserIDs();

		this.clearNavigationData();
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndCopyResult( result );

			}
		} );
	}

	onSaveAndNewClick( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.setCurrentEditViewState( 'new' );
		var record = this.getRecordsFromUserIDs();
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndNewResult( result );

			}
		} );
	}

	onSaveAndContinue( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var record = this.getRecordsFromUserIDs();

		this.doSaveAPICall( record, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );

			}
		} );
	}

	validate( api ) {
		if ( this.enable_validation ) {
			//Allow alternate api to be validated.
			if ( api == undefined ) {
				var api = this.api;
			}

			var $this = this;
			var record = {};
			if ( this.is_mass_editing ) {
				var changed_fields = this.getChangedFields();
				record = this.buildMassEditSaveRecord( this.mass_edit_record_ids, changed_fields );
			} else {
				record = this.getRecordsFromUserIDs();
			}
			api['validate' + api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		} else {
			Debug.Text( 'Validation disabled', 'BaseViewController.js', 'BaseViewController', 'validate', 10 );
		}
	}

	getRecordsFromUserIDs() {
		var record;
		if ( Array.isArray( this.current_edit_record.user_id ) ) {
			//Convert multiple user_id recurring schedules to their own record for the API.
			record = [];
			for ( var i = 0; i < this.current_edit_record.user_id.length; i++ ) {
				var tmp_record = Global.clone( this.current_edit_record );
				tmp_record.user_id = this.current_edit_record.user_id[i];
				tmp_record = this.uniformVariable( tmp_record );
				record.push( tmp_record );
			}
		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}
		return record;
	}

	onNavigationClick( iconName ) {

		var $this = this;
		switch ( iconName ) {
			case 'schedule':

				var filter = { filter_data: {} };
				var selected_item = this.getSelectedItem();
				var include_users = null;
				var now_date = new Date();

				if ( !Global.isSet( selected_item.user_id ) ) {

					var temp_filter = {};
					temp_filter.filter_data = {};
					temp_filter.filter_data.id = [selected_item.id];
					temp_filter.filter_columns = { user_id: true };

					this.api['get' + this.api.key_name]( temp_filter, {
						onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							include_users = [result_data.user_id];

							filter.filter_data.include_user_ids = { value: include_users };
							filter.select_date = now_date.format();
							Global.addViewTab( $this.viewId, $.i18n._( 'Recurring Schedules' ), window.location.href );
							IndexViewController.goToView( 'Schedule', filter );

						}
					} );

				} else {
					include_users = [selected_item.user_id];
					filter.filter_data.include_user_ids = { value: include_users };
					filter.select_date = now_date.format();

					Global.addViewTab( this.viewId, $.i18n._( 'Recurring Schedules' ), window.location.href );
					IndexViewController.goToView( 'Schedule', filter );

				}

				break;
			case 'recurring_template':

				filter = { filter_data: {} };
				selected_item = this.getSelectedItem();

				if ( !Global.isSet( selected_item.recurring_schedule_template_control_id ) ) {

					temp_filter = {};
					temp_filter.filter_data = {};
					temp_filter.filter_data.id = [selected_item.id];

					this.api['get' + this.api.key_name]( temp_filter, {
						onResult: function( result ) {
							var result_data = result.getResult();

							if ( !result_data ) {
								result_data = [];
							}

							result_data = result_data[0];

							filter.filter_data.id = [result_data.recurring_schedule_template_control_id];

							Global.addViewTab( $this.viewId, $.i18n._( 'Recurring Schedules' ), window.location.href );
							IndexViewController.goToView( 'RecurringScheduleTemplateControl', filter );

						}
					} );

				} else {
					filter.filter_data.id = [selected_item.recurring_schedule_template_control_id];

					Global.addViewTab( this.viewId, $.i18n._( 'Recurring Schedules' ), window.location.href );
					IndexViewController.goToView( 'RecurringScheduleTemplateControl', filter );

				}

				break;
			case 'export_excel':
				this.onExportClick( 'exportRecurringScheduleControl' );
				break;
		}
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'recurring_template':
				this.setDefaultMenuRecurringTemplateIcon( context_btn, grid_selected_length );
				break;
			case 'schedule':
				this.setDefaultMenuScheduleIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setDefaultMenuRecurringTemplateIcon( context_btn, grid_selected_length, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( grid_selected_length === 1 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
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

	search( set_default_menu, page_action, page_number, callBack ) {

		if ( !Global.isSet( set_default_menu ) ) {
			set_default_menu = true;
		}

		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_sort = {};
		filter.filter_columns = this.getFilterColumnsFromDisplayColumns();
		filter.filter_items_per_page = 0; // Default to 0 to load user preference defined

		if ( this.pager_data ) {

			if ( LocalCacheData.paging_type === 0 ) {
				if ( page_action === 'next' ) {
					filter.filter_page = this.pager_data.next_page;
				} else {
					filter.filter_page = 1;
				}
			} else {

				switch ( page_action ) {
					case 'next':
						filter.filter_page = this.pager_data.next_page;
						break;
					case 'last':
						filter.filter_page = this.pager_data.previous_page;
						break;
					case 'start':
						filter.filter_page = 1;
						break;
					case 'end':
						filter.filter_page = this.pager_data.last_page_number;
						break;
					case 'go_to':
						filter.filter_page = page_number;
						break;
					default:
						filter.filter_page = this.pager_data.current_page;
						break;
				}

			}

		} else {
			filter.filter_page = 1;
		}

		if ( this.sub_view_mode && this.parent_key ) {
			this.select_layout.data.filter_data[this.parent_key] = this.parent_value;
		}
		//If sub view controller set custom filters, get it
		if ( Global.isSet( this.getSubViewFilter ) ) {

			this.select_layout.data.filter_data = this.getSubViewFilter( this.select_layout.data.filter_data );

		}

		//select_layout will not be null, it's set in setSelectLayout function
		filter.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
		filter.filter_sort = this.select_layout.data.filter_sort;

		this.last_select_ids = this.getGridSelectIdArray();

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {

				var result_data = result.getResult();
				if ( !Global.isArray( result_data ) ) {
					$this.showNoResultCover();
				} else {
					$this.removeNoResultCover();
					if ( Global.isSet( $this.__createRowId ) ) {
						result_data = $this.__createRowId( result_data );
					}

					result_data = Global.formatGridData( result_data, $this.api.key_name );
				}
				//Set Page data to widget, next show display info when setDefault Menu
				$this.pager_data = result.getPagerData();

				//CLick to show more mode no need this step
				if ( LocalCacheData.paging_type !== 0 ) {
					$this.paging_widget.setPagerData( $this.pager_data );
					$this.paging_widget_2.setPagerData( $this.pager_data );
				}

				if ( LocalCacheData.paging_type === 0 && page_action === 'next' ) {
					var current_data = $this.grid.getGridParam( 'data' );
					result_data = current_data.concat( result_data );
				}

				///Override to reset id, because id of each record is the same if employess assigned to this id.
				var len = result_data.length;

				for ( var i = 0; i < len; i++ ) {
					var item = result_data[i];
				}

				if ( $this.grid ) {
					$this.grid.setData( result_data );

					$this.reSelectLastSelectItems();
				}

				$this.setGridCellBackGround(); //Set cell background for some views

				ProgressBar.closeOverlay(); //Add this in initData
				if ( set_default_menu ) {
					$this.setDefaultMenu( true );
				}

				if ( LocalCacheData.paging_type === 0 ) {
					if ( !$this.pager_data || $this.pager_data.is_last_page ) {
						$this.paging_widget.css( 'display', 'none' );
					} else {
						$this.paging_widget.css( 'display', 'block' );
					}
				}

				if ( callBack ) {
					callBack( result );
				}

				// when call this from save and new result, we don't call auto open, because this will call onAddClick twice
				if ( set_default_menu ) {
					$this.autoOpenEditViewIfNecessary();
				}

				$this.searchDone();
			}
		} );
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.user_id = true;

		return this._getFilterColumnsFromDisplayColumns( column_filter, true );
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'recurring_template':
				this.setEditMenuRecurringTemplateIcon( context_btn );
				break;
			case 'schedule':
				this.setEditMenuScheduleIcon( context_btn );
				break;
		}
	}

	setEditMenuRecurringTemplateIcon( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuScheduleIcon( context_btn, pId ) {
		if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}

	}
}
