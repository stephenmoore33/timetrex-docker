export class ScheduleShiftViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#schedule_shift_view_container',


			schedule_status_array: null,

			user_status_array: null,

			user_group_array: null,

			user_api: null,
			user_group_api: null,
			date_api: null,
			absence_policy_api: null,

			job_api: null,
			job_item_api: null,
			department_api: null,
			default_punch_tag: [],
			previous_punch_tag_selection: [],

			total_time: null,
			pre_total_time: 0,

			is_mass_adding: false
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'ScheduleShiftEditView.html';
		this.permission_id = 'schedule';
		this.viewId = 'ScheduleShift';
		this.script_name = 'ScheduleShiftView';
		this.table_name_key = 'schedule';
		this.context_menu_name = $.i18n._( 'Schedule Shift' );
		this.navigation_label = $.i18n._( 'Schedule' );
		this.api = TTAPI.APISchedule;
		this.absence_policy_api = TTAPI.APIAbsencePolicy;
		this.user_api = TTAPI.APIUser;
		this.user_group_api = TTAPI.APIUserGroup;

		if ( Global.getProductEdition() >= 20 ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.punch_tag_api = TTAPI.APIPunchTag;
			this.department_api = TTAPI.APIDepartment;
		}

		this.date_api = TTAPI.APITTDate;

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['copy'],
			include: []
		};

		return context_menu_model;
	}

	onSaveClick( ignoreWarning ) {
		// this function will shortly be deprecated and replaced by BaseViewController.onViewClick(), but this intermediatiary step of reorganizing the code
		// is to help debug in future by seeing how the current function was updated to match baseview.
		// initially, the code looped over all the mass edit records, and then for each record, it would loop over the changed fields and set them on that record.
		// instead, we will now just loop over the changed fields once, and then loop over the mass edit records, cloning this changed set of fields.
		// The clone itself is probably yet another loop internally, but this at least makes it easier to read and also allows the merge to use the common base view controller function.

		var $this = this;
		var record;
		if ( this.is_mass_editing ) {
			record = [];

			// loop over all fields on the form, and track the ones that have changed
			var mass_record_shared = {};
			for ( var key in this.edit_view_ui_dic ) {

				if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
					continue;
				}
				var widget = this.edit_view_ui_dic[key];
				if ( Global.isSet( widget.isChecked ) ) {
					if ( widget.isChecked() && widget.getEnabled() ) {
						mass_record_shared[key] = widget.getValue();
					}
				}
			}

			// loop over all the selected id's for the mass edit, and apply a copy of the changed fields to each

			for ( var i = 0; i < this.mass_edit_record_ids.length; i++ ) {
				//create new copy of the fields that have changed.
				var mass_record_single = Global.clone( mass_record_shared );
				mass_record_single['id'] = $this.mass_edit_record_ids[i];

				// push the single record into the array of mass edit records to be saved
				record.push( mass_record_single );
			}
		} else {
			this.collectUIDataToCurrentEditRecord();
			record = this.uniformVariable( this.current_edit_record );
		}

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {

				$this.onSaveResult( result );

			}
		} );
	}

	//Make sure this.current_edit_record is updated before validate
	validate() {
		var $this = this;
		var record = {};
		if ( this.is_mass_editing ) {
			var record = [];
			for ( var i = 0; i < this.mass_edit_record_ids.length; i++ ) {
				var mass_record = {};
				mass_record['id'] = $this.mass_edit_record_ids[i];
				for ( var key in this.edit_view_ui_dic ) {

					if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
						continue;
					}
					var widget = this.edit_view_ui_dic[key];
					if ( Global.isSet( widget.isChecked ) ) {
						if ( widget.isChecked() && widget.getEnabled() ) {
							mass_record[key] = widget.getValue();
						}
					}
				}
				record.push( mass_record );
			}
		} else {
			this.collectUIDataToCurrentEditRecord();
			record = this.uniformVariable( this.current_edit_record );
		}

		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );
			}
		} );
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
			p_id = 'schedule';
		}

		if ( PermissionManager.validate( 'job_item', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	}

	punchTagUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'schedule';
		}

		if ( PermissionManager.validate( 'punch_tag', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_punch_tag' ) ) {
			return true;
		}
		return false;
	}

	branchUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'schedule';
		}

		if ( PermissionManager.validate( p_id, 'edit_branch' ) ) {
			return true;
		}
		return false;
	}

	departmentUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'schedule';
		}

		if ( PermissionManager.validate( p_id, 'edit_department' ) ) {
			return true;
		}
		return false;
	}

	initPermission() {
		super.initPermission();

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
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'status', 'status_id', this.api, function( res ) {
			res = res.getResult();

			$this.schedule_status_array = Global.buildRecordArray( res );

		} );

		this.initDropDownOption( 'status', 'user_status_id', this.user_api, function( res ) {
			res = res.getResult();

			$this.user_status_array = Global.buildRecordArray( res );
		} );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;

				$this.adv_search_field_ui_dic['group_ids'].setSourceData( res );

			}
		} );
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

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_schedule': { 'label': $.i18n._( 'Schedule' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;
		var widgetContainer;

		this.navigation.AComboBox( {
			api_class: TTAPI.APISchedule,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_schedule_schedule',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_schedule = this.edit_view_tab.find( '#tab_schedule' );

		var tab_schedule_column1 = tab_schedule.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_schedule_column1 );

		// Employee
		// Employees
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: !this.checkOpenPermission(),
			set_special_empty: true,
			field: 'user_id',
			addition_source_function: ( function( target, source_data ) {
				return $this.onEmployeeSourceCreate( target, source_data );
			} )

		} );
		var default_args = {};
		default_args.permission_section = 'schedule';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_schedule_column1, '' );

		// Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.schedule_status_array );
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

		// Available Balance
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'available_balance' } );

		widgetContainer = $( '<div class=\'widget-h-box available-balance-h-box\'></div>' );
		this.available_balance_info = $( '<span class="available-balance-info tticon tticon-info_black_24dp"></span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( this.available_balance_info );

		this.addEditFieldToColumn( $.i18n._( 'Available Balance' ), form_item_input, tab_schedule_column1, '', widgetContainer, true );

		// Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'start_date_stamp', validation_field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', null );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TRangePicker( { field: 'start_date_stamps', validation_field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_schedule_column1, '', null, true );

		// In
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );

		form_item_input.TTimePicker( { field: 'start_time' } );
		this.addEditFieldToColumn( $.i18n._( 'In' ), form_item_input, tab_schedule_column1, '', null );

		// Out

		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );

		form_item_input.TTimePicker( { field: 'end_time' } );

		this.addEditFieldToColumn( $.i18n._( 'Out' ), form_item_input, tab_schedule_column1, '', null );

		// Total
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'total_time' } );
		this.addEditFieldToColumn( $.i18n._( 'Total' ), form_item_input, tab_schedule_column1 );

		// Schedule Policy
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

		// Branch
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
			added_items: [
				{ value: TTUUID.not_exist_id, label: Global.default_item },
			]
		} );
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_schedule_column1, '', null, true );

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
			field: 'department_id',
			addition_source_function: ( function( target, source_data ) {
				return $this.onSourceDataCreate( target, source_data );
			} ),
			added_items: [
				{ value: TTUUID.not_exist_id, label: Global.default_item },
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

			// Task
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
					return $this.onSourceDataCreate( target, source_data );
				} ),
				added_items: [
					{ value: TTUUID.not_exist_id, label: Global.default_item },
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

		if ( this.is_mass_editing ) {
			this.detachElement( 'total_time' );
		} else {
			this.attachElement( 'total_time' );
		}
	}

	onSourceDataCreate( target, source_data ) {
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

	setEditViewWidgetsMode() {
		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			var widgetContainer = this.edit_view_form_item_dic[key];
			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean_dic[tab_id] ) {
					did_clean_dic[tab_id] = true;
				}
			}
			switch ( key ) {
				case 'start_date_stamps':
					if ( ( !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) && !this.is_mass_editing && !this.is_edit ) {
						this.attachElement( key );
						widget.parents( '.edit-view-form-item-div' ).show();
					} else {
						this.detachElement( key );
						widget.parents( '.edit-view-form-item-div' ).hide();
					}
					break;
				case 'start_date_stamp':
					if ( ( !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) && !this.is_mass_editing && !this.is_edit ) {
						this.detachElement( key );
						widget.parents( '.edit-view-form-item-div' ).hide();
					} else {
						this.attachElement( key );
						widget.parents( '.edit-view-form-item-div' ).show();
					}
					break;
				default:
					widget.css( 'opacity', 1 );
					break;
			}

			// #2831 Must check is_viewing, otherwise all fields are editable in View mode. Bug originated in 886a33c71174c10c0699cbf869a3cf2ebd200591
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

	exchangeBalance() {

		var b_div = $( $( this ).next() );
		if ( b_div.css( 'display' ) === 'block' ) {
			b_div.css( 'display', 'none' );
		} else if ( b_div.css( 'display' ) === 'none' ) {
			b_div.css( 'display', 'block' );

		}
	}

	onAddResult( result ) {
		var $this = this;
		var result_data = result.getResult();
		if ( !result_data ) {
			result_data = [];
		}
		result_data.company = LocalCacheData.current_company.name;
		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}
		// Default to Open
		result_data === true && ( result_data = { user_id: TTUUID.zero_id } );
		!result_data.user_id && ( result_data.user_id = TTUUID.zero_id );
		$this.current_edit_record = result_data;
		$this.initEditView();
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

	buildSearchFields() {

		super.buildSearchFields();
		var $this = this;
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
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				addition_source_function: ( function( target, source_data ) {
					return $this.onEmployeeSourceCreate( target, source_data );
				} ),
				adv_search: true,
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
				label: $.i18n._( 'Start Date' ),
				in_column: 1,
				field: 'start_date',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),
			new SearchField( {
				label: $.i18n._( 'End Date' ),
				in_column: 1,
				field: 'end_date',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.DATE_PICKER
			} ),

			new SearchField( {
				label: $.i18n._( 'Employee Status' ),
				in_column: 1,
				field: 'user_status_id',
				multiple: true,
				basic_search: false,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Default Branch' ),
				in_column: 1,
				field: 'default_branch_id',
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
				field: 'default_department_id',
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
				basic_search: false,
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
				label: $.i18n._( 'Schedule Branch' ),
				in_column: 2,
				field: 'branch_id',
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
				in_column: 2,
				field: 'department_id',
				layout_name: 'global_department',
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'DepartmentView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Schedule Policy' ),
				in_column: 2,
				field: 'schedule_policy_id',
				layout_name: 'global_schedule',
				api_class: TTAPI.APISchedulePolicy,
				multiple: true,
				basic_search: true,
				adv_search: true,
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
					//Don't validate immediately as onJobQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
			case 'punch_tag_quick_search':
				if ( Global.getProductEdition() >= 20 ) {
					this.onPunchTagQuickSearch( c_value, this.getPunchTagFilterData(), 'punch_tag_id' );

					//Don't validate immediately as onPunchTagQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
			case 'absence_policy_id':
			case 'status_id':
				this.onStatusChange();
				break;
			case 'user_id':
				this.edit_view_ui_dic['absence_policy_id'].setSourceData( null );
				this.edit_view_ui_dic['absence_policy_id'].setValue( false );
				this.current_edit_record['absence_policy_id'] = false;
				this.getAbsencePolicy( this.current_edit_record[key] );

				if ( $.isArray( this.current_edit_record[key] ) && this.current_edit_record[key].length > 1 ) {
					this.is_mass_adding = true;
				} else {
					this.is_mass_adding = false;
				}
				this.setEditMenu();

				if ( Global.getProductEdition() >= 20 ) {
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' )
				}
				break;
		}

		this.getScheduleTotalTime();
		this.getProjectedAbsencePolicyBalance();

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	getProjectedAbsencePolicyBalance() {
		$( this.edit_view_form_item_dic['available_balance'].find( '.schedule-view-balance-info' ) ).css( 'display', 'none' );

		if ( this.current_edit_record['absence_policy_id'] && this.edit_view_form_item_dic['absence_policy_id'].css( 'display' ) === 'block' && !this.is_mass_editing ) {
			var user_id;
			if ( Global.isArray( this.current_edit_record['user_id'] ) && this.current_edit_record['user_id'].length === 1 ) {
				user_id = this.current_edit_record['user_id'][0];
			} else if ( this.current_edit_record['user_id'] ) {
				user_id = this.current_edit_record['user_id'];
			} else {
				return;
			}

			var date_stamp = this.current_edit_record['date_stamp'];
			var result_data = this.absence_policy_api.getProjectedAbsencePolicyBalance( this.current_edit_record['absence_policy_id'], user_id, date_stamp, this.total_time, this.pre_total_time, { async: false } );

			result_data = result_data ? result_data : {
				available_balance: 0,
				current_time: 0,
				remaining_balance: 0,
				projected_balance: 0,
				projected_remaining_balance: 0
			};

			this.getBalanceHandler( result_data, date_stamp );
		}
		this.onStatusChange();
	}

	getAbsencePolicy( user_ids ) {
		var args = { filter_data: {} };
		args.filter_data.user_id = user_ids;
		this.edit_view_ui_dic['absence_policy_id'].setDefaultArgs( args );
	}

	uniformVariable( records ) {
		var new_records = [];
		if ( Global.isArray( records.user_id ) && records.user_id.length > 0 ) {
			var user_ids = records.user_id;
			for ( var key in user_ids ) {
				var tmp_records = Global.clone( records );
				tmp_records.user_id = user_ids[key];
				new_records.push( tmp_records );
			}
		} else {
			new_records.push( this.current_edit_record );
		}

		if ( this.current_edit_record.start_date_stamps && new_records.length > 1 ) {
			//Allowing multiple dates
			var edit_record = [];
			for ( var ur in new_records ) {
				var dates = this.uniformDates( new_records[ur].start_date_stamps, new_records[ur] );

				if ( Array.isArray( new_records[ur].start_date_stamps ) ) {
					dates = new_records[ur].start_date_stamps;
				} else {
					dates = this.parserDatesRange( new_records[ur].start_date_stamps );
				}

				for ( var d in dates ) {
					var new_record = {};
					for ( var er in new_records[ur] ) {
						if ( er == 'start_date_stamps' ) {
							continue;
						}
						new_record[er] = new_records[ur][er];
					}
					new_record.start_date_stamp = dates[d];
					new_record.date_stamp = dates[d];
					edit_record.push( new_record );
				}
			}
		} else {
			edit_record = this.current_edit_record;
			if ( $.isArray( edit_record.start_date_stamp ) && edit_record.start_date_stamp.length == 1 ) {
				edit_record.start_date_stamp = edit_record.start_date_stamp[0];
			}
		}

		return edit_record;
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
		return dates;
	}

	uniformDates( date_range, record ) {
		var dates = [];
		if ( !Array.isArray( date_range ) && date_range.indexOf( ' - ' ) == -1 ) {
			dates = [date_range];
		} else if ( Array.isArray( date_range ) ) {
			dates = date_range;
		} else {
			dates = this.parserDatesRange( date_range );
		}

		return dates;
	}

	setCurrentEditRecordData() {
		var $this = this;
		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'status_id': 10
		} );

		if ( this.current_edit_record.id || this.is_mass_editing ) {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( false );
		} else {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( true );
		}

		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) || key === 'date_stamp' ) {
				switch ( key ) {
					case 'start_date_stamp':
					case 'start_date_stamps':
						if ( !this.current_edit_record[key] ) {
							continue;
						}
						var date_array;
						if ( !this.current_edit_record.id ) {
							date_array = this.current_edit_record.start_date_stamps;
						} else {
							if ( Array.isArray( this.current_edit_record.start_date_stamp ) == false ) {
								date_array = [this.current_edit_record.start_date_stamp];
							}
						}
						this.current_edit_record.start_date_stamp = date_array;
						widget.setValue( date_array );
						break;
					case 'total_time':
						this.pre_total_time = ( this.is_add ) ? 0 : this.current_edit_record[key];
						break;
					case 'job_id':
						if ( Global.getProductEdition() >= 20 ) {
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

							var args = {};
							args.filter_data = $this.getPunchTagFilterData();
							widget.setDefaultArgs( args );
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
					case 'user_id':
						widget.setValue( this.current_edit_record[key] );
						this.getAbsencePolicy( this.current_edit_record[key] );
						break;
					case 'date_stamp':
						this.current_edit_record['start_date_stamp'] = this.current_edit_record[key];
						this.current_edit_record['start_date_stamps'] = this.current_edit_record[key];
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

	getScheduleTotalTime() {
		if ( this.is_mass_editing ) {
			return;
		}

		var startTime, endTime, date_stamp;
		if ( this.current_edit_record['start_date_stamps'] ) {
			date_stamp = this.uniformDates( this.current_edit_record['start_date_stamps'], this.current_edit_record )[0];
		} else {
			date_stamp = this.current_edit_record['date_stamp'];
		}

		var startTime = date_stamp + ' ' + this.current_edit_record['start_time'];
		var endTime = date_stamp + ' ' + this.current_edit_record['end_time'];
		var schedulePolicyId = ( this.current_edit_record['schedule_policy_id'] ) ? this.current_edit_record['schedule_policy_id'] : '';

		if ( !schedulePolicyId ) {
			return;
		}

		var user_id = this.current_edit_record.user_id;

		var result = this.api.getScheduleTotalTime( startTime, endTime, schedulePolicyId, user_id, { async: false } );
		if ( result.isValid() && result.getResult() ) {
			this.total_time = result.getResult();

			var total_time = Global.getTimeUnit( this.total_time );

			this.edit_view_ui_dic['total_time'].setValue( total_time );

			this.current_edit_record['total_time'] = total_time;
		}
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onStatusChange();
		this.getScheduleTotalTime();
		this.setEditViewWidgetsMode();
		this.getProjectedAbsencePolicyBalance();
	}

	onStatusChange() {
		if ( this.current_edit_record['status_id'] == 20 ) {
			this.attachElement( 'absence_policy_id' );
			if ( this.current_edit_record['absence_policy_id'] && this.current_edit_record['absence_policy_id'] != TTUUID.zero_id ) {
				this.attachElement( 'available_balance' );
			} else {
				this.detachElement( 'available_balance' );
			}
		} else {
			this.detachElement( 'absence_policy_id' );
			this.detachElement( 'available_balance' );
		}

		this.editFieldResize();

	}
}
