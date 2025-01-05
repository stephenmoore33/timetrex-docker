import '@/global/widgets/filebrowser/TImage';

export class PunchesViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#punches_view_container',

			// _required_files: {
			// 	10: ['TImage'],
			// 	15: ['leaflet-timetrex']
			// },
			// TODO: breakdown leaflet-timetrex so only the convert functions are needed in ViewControllers.

			old_type_status: {},

			user_api: null,
			user_group_api: null,
			api_station: null,
			type_array: null,

			punch_tag_api: null,
			default_punch_tag: [],
			previous_punch_tag_selection: [],

			actual_time_label: null,

			location_mass_edit_check_box: null,
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PunchesEditView.html';
		this.permission_id = 'punch';
		this.viewId = 'Punches';
		this.script_name = 'PunchesView';
		this.table_name_key = 'punch';
		this.context_menu_name = $.i18n._( 'Punches' );
		this.navigation_label = $.i18n._( 'Punch' );
		this.api = TTAPI.APIPunch;
		this.user_api = TTAPI.APIUser;
		this.user_group_api = TTAPI.APIUserGroup;

		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.punch_tag_api = TTAPI.APIPunchTag;
		}

		this.api_station = TTAPI.APIStation;

		this.initPermission();
		this.render();

		this.buildContextMenu();
		this.initData();
	}

	jobUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( 'job', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job' ) ) {
			return true;
		}
		return false;
	}

	jobItemUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( 'job_item', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_job_item' ) ) {
			return true;
		}
		return false;
	}

	punchTagUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( 'punch_tag', 'enabled' ) &&
			PermissionManager.validate( p_id, 'edit_punch_tag' ) ) {
			return true;
		}
		return false;
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

	transferUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_transfer' ) ) {
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

	locationUIValidate( p_id ) {

		if ( !p_id ) {
			p_id = 'punch';
		}

		if ( PermissionManager.validate( p_id, 'edit_location' ) ) {
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

	//Speical permission check for views, need override
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
	}

	initOptions() {
		var $this = this;

		this.initDropDownOption( 'type' );

		this.initDropDownOption( 'status', 'status_id', this.api, null, 'status_array' );

		this.initDropDownOption( 'status', 'user_status_id', this.user_api, null, 'user_status_array' );

		this.user_group_api.getUserGroup( '', false, false, {
			onResult: function( res ) {
				res = res.getResult();

				res = Global.buildTreeRecord( res );
				$this.user_group_array = res;
				$this.basic_search_field_ui_dic['group_id'].setSourceData( res );
				$this.adv_search_field_ui_dic['group_id'].setSourceData( res );

			}
		} );
	}

	onEditStationDone() {
		this.setStation();
	}

	setStation() {

		var $this = this;
		var arg = { filter_data: { id: this.current_edit_record.station_id } };

		this.api_station.getStation( arg, {
			onResult: function( result ) {
				$this.station = result.getResult()[0];
				var widget = $this.edit_view_ui_dic['station_id'];
				widget.setValue( $this.station.type + '-' + $this.station.description );
				widget.css( 'cursor', 'pointer' );

			}
		} );
	}

	uniformVariable( records ) {
		if ( this.is_mass_editing == false ) {
			records = this.buildMassAddRecord( records );
		}

		if ( Array.isArray( records ) ) {
			for ( let i = 0; i < records.length; i++ ) {
				if ( !records[i].hasOwnProperty( 'time_stamp' ) ) {
					records[i].time_stamp = false;
				}
			}
		} else if ( !records.hasOwnProperty( 'time_stamp' ) ) {
			records.time_stamp = false;
		}

		return records;
	}

	getCustomFieldReferenceField() {
		return 'note';
	}

	buildEditViewUI() {

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_punch': { 'label': $.i18n._( 'Punch' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		var form_item_input;
		var widgetContainer;

		this.navigation.AComboBox( {
			api_class: TTAPI.APIPunch,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_punch',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_punch = this.edit_view_tab.find( '#tab_punch' );

		var tab_punch_column1 = tab_punch.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_punch_column1 );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'punch';
		form_item_input.setDefaultArgs( default_args );
		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_punch_column1, '', null, true );

		// Time
		form_item_input = Global.loadWidgetByName( FormItemType.TIME_PICKER );

		form_item_input.TTimePicker( { field: 'punch_time', validation_field: 'time_stamp' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		this.actual_time_label = $( '<span class=\'widget-right-label\'></span>' );
		widgetContainer.append( form_item_input );
		widgetContainer.append( this.actual_time_label );
		this.addEditFieldToColumn( $.i18n._( 'Time' ), form_item_input, tab_punch_column1, '', widgetContainer );

		//Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( { field: 'punch_date', validation_field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Mass Add Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TRangePicker( { field: 'punch_dates', validation_field: 'date_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Date' ), form_item_input, tab_punch_column1, '', null, true );

		//Transfer
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'transfer' } );
		this.addEditFieldToColumn( $.i18n._( 'Transfer' ), form_item_input, tab_punch_column1, '', null, true );
		if ( this.show_transfer_ui == false || this.is_add == false ) {
			this.detachElement( 'transfer' );
		}

		// Punch

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		var check_box = Global.loadWidgetByName( FormItemType.CHECKBOX );
		check_box.TCheckbox( { field: 'disable_rounding' } );
		var label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'Disable Rounding' ) + '</span>' );
		widgetContainer.append( form_item_input );

		// Check if view only mode. To prevent option appearing but disabled, as disabled checkboxes are not very clear - same in TimeSheetViewController
		if ( this.is_viewing || PermissionManager.validate( 'punch', 'edit_disable_rounding' ) == false ) {
			// dev-note: not sure if we need to pass widgetContainer here, or if we can omit if its only one element now (due to the if is_viewing).
			// to be safe, will continue to use widgetContainer for this case. We only want to affect viewing mode (hide rounding checkbox), less risk of regression to keep widget container in.
			this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), form_item_input, tab_punch_column1, '', widgetContainer, true );
		} else {
			widgetContainer.append( label );
			widgetContainer.append( check_box );
			this.addEditFieldToColumn( $.i18n._( 'Punch Type' ), [form_item_input, check_box], tab_punch_column1, '', widgetContainer, true );
		}

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
			field: 'branch_id',
			addition_source_function: ( function( target, source_data ) {
				return $this.onSourceDataCreate( target, source_data );
			} ),
			added_items: [
				{ value: TTUUID.not_exist_id, label: Global.default_item },
				{ value: 'ffffffff-ffff-ffff-ffff-000000000002', label: $.i18n._( '-- Current Shift --' ) }
			]
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
			field: 'department_id',
			addition_source_function: ( function( target, source_data ) {
				return $this.onSourceDataCreate( target, source_data );
			} ),
			added_items: [
				{ value: TTUUID.not_exist_id, label: Global.default_item },
				{ value: 'ffffffff-ffff-ffff-ffff-000000000002', label: $.i18n._( '-- Current Shift --' ) }
			]
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
					{ value: 'ffffffff-ffff-ffff-ffff-000000000002', label: $.i18n._( '-- Current Shift --' ) }
				]
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
					{ value: 'ffffffff-ffff-ffff-ffff-000000000002', label: $.i18n._( '-- Current Shift --' ) }
				]
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

		if ( ( Global.getProductEdition() >= 20 ) ) {
			// Quantity
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

		if ( !this.show_note_ui ) {
			this.detachElement( 'note' );
		}

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

			var map_icon = $( '<img class="widget-h-box-mapIcon" src="framework/leaflet/images/marker-icon-red.png" >' ); // TODO, fix image location so that its not in a library folder incase library removed.

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

		// Station
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
		this.addEditFieldToColumn( $.i18n._( 'Split Existing Punches' ), form_item_input, tab_punch_column1, '', null, true );
		if ( this.is_add == false ) {
			this.detachElement( 'split_punch_control' );
		}

		//Punch Image
		form_item_input = Global.loadWidgetByName( FormItemType.IMAGE );
		form_item_input.TImage( { field: 'punch_image' } );
		this.addEditFieldToColumn( $.i18n._( 'Image' ), form_item_input, tab_punch_column1, '', null, true, true );

		if ( this.is_mass_editing ) {
			this.detachElement( 'punch_image' );
			this.detachElement( 'user_id' );
		}
	}

	onSourceDataCreate( target, source_data ) {
		var display_columns = target.getDisplayColumns();
		var first_item = {};
		var second_item = {};

		$.each( display_columns, function( index, content ) {
			first_item.id = TTUUID.not_exist_id;
			first_item[content.name] = Global.default_item;

			second_item.id = 'ffffffff-ffff-ffff-ffff-000000000002';
			second_item[content.name] = $.i18n._( '-- Current Shift --' );

			return false;
		} );

		//Error: Object doesn't support property or method 'unshift' in /interface/html5/line 6953
		if ( !source_data || $.type( source_data ) !== 'array' ) {
			source_data = [];
		}

		source_data.unshift( second_item );
		source_data.unshift( first_item );

		return source_data;
	}

	//set widget disablebility if view mode or edit mode
	setEditViewWidgetsMode() {
		var did_clean_dic = {};
		for ( var key in this.edit_view_ui_dic ) {
			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}
			var widget = this.edit_view_ui_dic[key];
			var widgetContainer = this.edit_view_form_item_dic[key];
			widget.css( 'opacity', 1 );
			var column = widget.parent().parent().parent();
			var tab_id = column.parent().attr( 'id' );
			if ( !column.hasClass( 'v-box' ) ) {
				if ( !did_clean_dic[tab_id] ) {
					did_clean_dic[tab_id] = true;
				}
			}
			switch ( key ) {
				case 'punch_dates':
					if ( !this.is_mass_editing && ( this.isMassAdding() || !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) ) {
						this.attachElement( key );
						widget.css( 'opacity', 1 ); //show
					} else {
						this.detachElement( key );
						widget.css( 'opacity', 0 ); //hide
					}
					break;
				case 'punch_date':
					if ( !this.is_mass_editing && ( this.isMassAdding() || !this.current_edit_record.id || this.current_edit_record.id == TTUUID.zero_id ) ) {
						this.detachElement( key );
						widget.css( 'opacity', 0 ); //hide - opposite from above
					} else {
						this.attachElement( key );
						widget.css( 'opacity', 1 ); //show
					}
					break;
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
			if ( this.original_note == '' ) {
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

			record.id = this.mass_edit_record_ids[0];
			record = this.uniformVariable( record );

		} else {
			record = this.current_edit_record;
			record = this.uniformVariable( record );
		}

		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );

			}
		} );
	}

	// TODO: not ideal to need to have this here. want to use the base view version,
	//  but need this in order to prevent it using the uniformVariable function in BaseViewController version,
	//  as Punches uniformVariable function does something additional
	buildMassEditSaveRecord( mass_edit_record_ids, changed_fields ) {
		var $this = this;
		var mass_records = [];
		$.each( mass_edit_record_ids, function( index, value ) {
			var common_record = Global.clone( changed_fields );
			common_record.id = value;
			mass_records.push( common_record );
		} );
		return mass_records;
	}

	buildMassAddRecord( current_edit_record ) {
		var record = [];
		var dates_array = current_edit_record.punch_dates;

		if ( dates_array && dates_array.indexOf( ' - ' ) > 0 ) {
			dates_array = this.parserDatesRange( dates_array );
		}

		if ( dates_array ) {
			for ( var i = 0; i < dates_array.length; i++ ) {
				var common_record = Global.clone( current_edit_record );
				delete common_record.punch_dates;
				common_record.punch_date = dates_array[i];
				var user_id = this.current_edit_record.user_id;

				if ( Global.isArray( user_id ) ) {
					for ( var j = 0; j < user_id.length; j++ ) {
						var final_record = Global.clone( common_record );
						final_record.user_id = this.current_edit_record.user_id[j];
						record.push( final_record );
					}
				} else {
					record.push( common_record );
				}
			}
		}

		return record;
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

	setCurrentEditRecordData() {
		var $this = this;
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'punch_dates':
						var date_array;
						if ( !this.current_edit_record.punch_dates ) {
							date_array = [this.current_edit_record['punch_date']];
							this.current_edit_record.punch_dates = date_array;
						} else {
							date_array = this.current_edit_record.punch_dates;
						}
						widget.setValue( date_array );
						break;
					case 'country': //popular case
						this.setCountryValue( widget, key );
						break;
					case 'enable_email_notification_message':
						widget.setValue( this.current_edit_record[key] );
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
					case 'punch_tag_quick_search':
						break;
					case 'job_quick_search':
//						widget.setValue( this.current_edit_record['job_id'] ? this.current_edit_record['job_id'] : 0 );
						break;
					case 'job_item_quick_search':
//						widget.setValue( this.current_edit_record['job_item_id'] ? this.current_edit_record['job_item_id'] : 0 );
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
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		var actual_time_value;
		if ( this.current_edit_record.id ) {

			if ( this.current_edit_record.actual_time_stamp ) {
				actual_time_value = $.i18n._( 'Actual Time' ) + ': ' + this.current_edit_record.actual_time_stamp;
			} else {
				actual_time_value = 'N/A';
			}

		}
		this.actual_time_label.text( actual_time_value );

		this.collectUIDataToCurrentEditRecord();
		this.setLocationValue();

		this.setEditViewDataDone();
		this.isEditChange();
	}

	setLocationValue( location_data ) {
		if ( Global.getProductEdition() >= 15 ) {
			if ( location_data ) {
				this.current_edit_record.latitude = location_data.latitude;
				this.current_edit_record.longitude = location_data.longitude;
				this.current_edit_record.position_accuracy = location_data.position_accuracy; //If position is manually modified, it should always be set to 0m.
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

	isEditChange() {

		if ( this.current_edit_record.id || this.is_mass_editing ) {
			this.edit_view_ui_dic['user_id'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_id'].setEnabled( true );
		}
	}

	//set tab 0 visible after all data set done. This be hide when init edit view data
	setEditViewDataDone() {
		// Remove this on 14.9.14 because adding tab url support, ned set url when tab index change and
		// need know what's current doing action. See if this cause any problem
		//LocalCacheData.current_doing_context_action = '';
		this.setTabOVisibility( true );

		if ( this.is_edit == true ) {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( false );
		} else {
			this.edit_view_ui_dic.user_id.setAllowMultipleSelection( true );
		}

		if ( this.is_viewing == true && ( this.current_edit_record.latitude == 0 || this.current_edit_record.longitude == 0 ) ) {
			$( '.widget-h-box-mapLocationWrapper' ).parents( '.edit-view-form-item-div' ).hide();
		} else {
			if ( this.show_location_ui ) {
				$( '.widget-h-box-mapLocationWrapper' ).parents( '.edit-view-form-item-div' ).show();
			}
		}

		this.navigation.setValue( this.current_edit_record.id );

		$( '.edit-view-tab-bar' ).css( 'opacity', 1 );
		TTPromise.resolve( 'init', 'init' );
	}

	setSubLogViewFilter() {
		if ( !this.sub_log_view_controller ) {
			return false;
		}

		this.sub_log_view_controller.getSubViewFilter = function( filter ) {
			filter['table_name_object_id'] = {
				'punch': [this.parent_edit_record.id],
				'punch_control': [this.parent_edit_record.punch_control_id]
			};

			return filter;
		};

		return true;
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

	onAddResult( result ) {
		var $this = this;
		var result_data = result.getResult();

		if ( !result_data ) {
			result_data = [];
		}

		result_data.company = LocalCacheData.current_company.name;
		result_data.punch_date = ( new Date() ).format();

		if ( $this.sub_view_mode && $this.parent_key ) {
			result_data[$this.parent_key] = $this.parent_value;
		}

		$this.current_edit_record = result_data;
		$this.initEditView();
	}

	buildSearchFields() {

		super.buildSearchFields();
		var default_args = { permission_section: 'punch' };
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
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				layout_name: 'global_user',
				default_args: default_args,
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
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
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
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
				label: $.i18n._( 'Title' ),
				in_column: 2,
				field: 'title_id',
				layout_name: 'global_user_title',
				api_class: TTAPI.APIUserTitle,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Punch Branch' ),
				in_column: 2,
				field: 'branch_id',
				layout_name: 'global_branch',
				api_class: TTAPI.APIBranch,
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Punch Department' ),
				in_column: 2,
				field: 'department_id',
				layout_name: 'global_department',
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: true,
				adv_search: true,
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

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['copy'],
			include: [
				{
					label: $.i18n._( 'Jump To' ),
					id: 'jump_to_header',
					menu_align: 'right',
					action_group: 'jump_to',
					action_group_header: true,
					sort_order: 9050,
					permission_result: false // to hide it in legacy context menu and avoid errors in legacy parsers.
				},
				{
					label: $.i18n._( 'TimeSheet' ),
					id: 'timesheet',
					menu_align: 'right',
					action_group: 'jump_to',
					sort_order: 9050,
					group: 'navigation',
					},
				{
					label: $.i18n._( 'Edit Employee' ),
					id: 'edit_employee',
					menu_align: 'right',
					action_group: 'jump_to',
					sort_order: 9050,
					group: 'navigation',
					}
			]
		};

		if ( Global.getProductEdition() >= 15 ) {
			context_menu_model.include.push(
				{
					label: $.i18n._( 'Map' ),
					id: 'map',
					menu_align: 'right',
					group: 'other',
					vue_icon: 'tticon tticon-map_black_24dp',
					sort_order: 8000,
				},
				{
					label: $.i18n._( 'Import' ),
					id: 'import_icon',
					menu_align: 'right',
					action_group: 'import_export',
					group: 'other',
					vue_icon: 'tticon tticon-file_download_black_24dp',
					sort_order: 9010
				}
			);
		}

		return context_menu_model;
	}

	onMapClick() {
		// only trigger map load in specific product editions.
		if ( ( Global.getProductEdition() >= 15 ) ) {
			ProgressBar.showProgressBar();

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

			if ( this.is_edit || this.is_mass_editing || this.is_add ) {
				//when editing, if the user reloads, the grid's selected id array become the whole grid.
				//to avoid mapping every punch in that scenario we need to grab the current_edit_record, rather than pull data from getGridSelectIdArray()
				//check for mass edit as well. <-- not sure what this refers to, assuming the same happens in mass edit, but maps are disabled on mass edit atm.
				punches.push( this.current_edit_record );
				// from the edit view we want to allow single markers to be draggable.
				if ( !this.is_viewing ) {
					// make sure that when view only (so no save) marker is not draggable, and thus no new marker can be added either.
					map_options.single_marker_draggable = true;
				}
			} else {
				var ids = this.getGridSelectIdArray();
				// from the map icon on the ribbon bar we want to PREVENT single markers being draggable. As this is intended as a read only view.
				map_options.single_marker_draggable = false;

				data.filter_data = Global.convertLayoutFilterToAPIFilter( this.select_layout );
				if ( ids.length > 0 ) {
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
				data.filter_columns.punch_tag_id = true;
				data.filter_columns.total_time = true;
				data.filter_columns.latitude = true;
				data.filter_columns.longitude = true;
				data.filter_columns.position_accuracy = true;

				punches = this.api.getPunch( data, { async: false } ).getResult();
			}

			import( /* webpackChunkName: "leaflet-timetrex" */ '@/framework/leaflet/leaflet-timetrex' ).then(( module )=>{
				var processed_punches_for_map = module.TTConvertMapData.processPunchesFromViewController( punches, map_options );
				IndexViewController.openEditView( this, 'Map', processed_punches_for_map );
			}).catch( Global.importErrorHandler );
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'timesheet':
			case 'edit_employee':
				this.onNavigationClick( id );
				break;
			case 'map':
				this.onMapClick();
				break;
			case 'import_icon':
				this.onImportClick();
				break;
		}
	}

	onImportClick() {
		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'Punch', function() {
			$this.search();
		} );
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'timesheet':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'user' );
				break;
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'timesheet':
				this.setEditMenuNavViewIcon( context_btn, 'punch' );
				break;
			case 'edit_employee':
				this.setEditMenuNavEditIcon( context_btn, 'user' );
				break;
		}
	}
	onNavigationClick( iconName ) {
		var $this = this;
		var filter;
		var temp_filter;
		var grid_selected_id_array;
		var grid_selected_length;

		switch ( iconName ) {
			case 'timesheet':
				filter = { filter_data: {} };
				if ( Global.isSet( this.current_edit_record ) ) {

					filter.user_id = this.current_edit_record.user_id;
					filter.base_date = this.current_edit_record.punch_date;
					Global.addViewTab( this.viewId, $.i18n._( 'Punches' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						var selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
						temp_filter.filter_data.id = [selectedId];

						this.api['get' + this.api.key_name]( temp_filter, {
							onResult: function( result ) {

								var result_data = result.getResult();

								if ( !result_data ) {
									result_data = [];
								}

								result_data = result_data[0];

								filter.user_id = result_data.user_id;
								filter.base_date = result_data.punch_date;

								Global.addViewTab( $this.viewId, $.i18n._( 'Punches' ), window.location.href );
								IndexViewController.goToView( 'TimeSheet', filter );

							}
						} );
					}

				}

				break;

			case 'edit_employee':
				filter = { filter_data: {} };
				if ( Global.isSet( this.current_edit_record ) ) {
					IndexViewController.openEditView( this, 'Employee', this.current_edit_record.user_id );
				} else {
					temp_filter = {};
					grid_selected_id_array = this.getGridSelectIdArray();
					grid_selected_length = grid_selected_id_array.length;

					if ( grid_selected_length > 0 ) {
						selectedId = grid_selected_id_array[0];

						temp_filter.filter_data = {};
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
		}
	}

	setEditMenuSaveAndContinueIcon( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || this.isMassDateOrMassUser() ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	copyAsNewResetIds( data ) {
		//override where needed.
		data.id = '';
		data.punch_control_id = ''; //Clear the punch_control_id record as well so we don't force the punch to be assigned to it.
		return data;
	}

	_continueDoCopyAsNew() {
		var $this = this;
		this.setCurrentEditViewState( 'new' );
		LocalCacheData.current_doing_context_action = 'copy_as_new';
		if ( Global.isSet( this.edit_view ) ) {
			this.current_edit_record = this.copyAsNewResetIds( this.current_edit_record );
			var navigation_div = this.edit_view.find( '.navigation-div' );
			navigation_div.css( 'display', 'none' );
			this.openEditView();
			this.initEditView();
			this.setEditMenu();
			this.setTabStatus();
			this.is_changed = false;
			ProgressBar.closeOverlay();
		} else {
			super._continueDoCopyAsNew();
		}
	}

	isMassDateOrMassUser() {
		if ( this.isMassAdding() ) {
			if ( this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length > 1 ) {
				return true;
			}

			if ( this.current_edit_record.user_id && this.current_edit_record.user_id.length > 1 ) {
				return true;
			}

			return false;
		}

		return false;
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
		record = this.uniformVariable( record );

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
		var record = this.current_edit_record;
		record = this.uniformVariable( record );
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndNewResult( result );

			}
		} );
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

	onSaveAndContinue( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';

		if ( this.isMassAdding() ) {

			if ( this.current_edit_record.punch_dates && this.current_edit_record.punch_dates.length === 1 ) {
				this.current_edit_record.punch_date = this.current_edit_record.punch_dates[0];
			}

			if ( this.current_edit_record.user_id && this.current_edit_record.user_id.length === 1 ) {
				this.current_edit_record.user_id = this.current_edit_record.user_id[0];
			}

		}

		this.current_edit_record = this.uniformVariable( this.current_edit_record );

		this.api['set' + this.api.key_name]( this.current_edit_record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );
			}
		} );
	}

	onFormItemChange( target, doNotValidate ) {

		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();

		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'user_id':
				this.setEditMenu();
				break;
			case 'punch_date':
				this.current_edit_record.punch_dates = [c_value];
				break;
			case 'punch_dates':
				this.setEditMenu();
				break;
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
			case 'user_id':
			case 'branch_id':
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
			default:
				this.current_edit_record[key] = c_value;
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onMapSaveClick( dataset, successCallback ) {
		this.savePunchPosition( dataset, successCallback );
	}

	savePunchPosition( moved_unsaved_markers, successCallback ) {
		if ( !moved_unsaved_markers || moved_unsaved_markers.length !== 1 ) {
			Debug.Text( 'ERROR: Invalid params/data passed to function.', 'PunchesViewController.js', 'PunchesViewController', 'savePunchPosition', 1 );
			return false;
		}

		if ( this.is_mass_editing == true ) {
			this.location_mass_edit_check_box.find( '.mass-edit-checkbox' )[0].checked = true;
		}

		// Regardless of record type, we want to just pass the value back, rather than a api save from map, then another save from parent view.
		// Map info will only be saved if user clicks save on the parent edit view.
		this.setLocationValue( moved_unsaved_markers[0] );
		successCallback();
		this.is_changed = true;
		return true;
	}

	getSelectEmployee( full_item ) {
		var user;
		if ( full_item ) {
			user = LocalCacheData.getLoginUser();
		} else {
			user = LocalCacheData.getLoginUser().id;
		}
		return user;
	}

	getFilterColumnsFromDisplayColumns( column_filter, enable_system_columns ) {
		if ( column_filter == undefined ) {
			column_filter = {};
		}
		column_filter.latitude = true;
		column_filter.longitude = true;
		return this._getFilterColumnsFromDisplayColumns( column_filter, enable_system_columns );
	}

	isMassAdding() {
		if ( this.current_edit_record && ( Array.isArray( this.current_edit_record.user_id ) === true
			|| ( Array.isArray( this.current_edit_record.punch_dates ) === true && this.current_edit_record.punch_dates.length > 1 ) ) ) {
			return true;
		} else {
			return false;
		}
	}
}
