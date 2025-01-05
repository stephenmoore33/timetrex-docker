export class StationViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#station_view_container',

			user_group_api: null,
			status_array: null,
			type_array: null,

			time_zone_array: null,
			time_clock_command_array: null,
			mode_flag_array: null,
			default_mode_flag_array: null,
			face_recognition_match_threshold_array: null,
			face_recognition_required_matches_array: null,
			poll_frequency_array: null,
			push_frequency_array: null,
			partial_push_frequency_array: null,
			group_selection_type_array: null,
			branch_selection_type_array: null,
			department_selection_type_array: null,
			user_group_array: null,

			punch_tag_api: null,
			default_punch_tag: [],
			previous_punch_tag_selection: [],

			user_preference_api: null
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'StationEditView.html';
		this.permission_id = 'station';
		this.viewId = 'Station';
		this.script_name = 'StationView';
		this.table_name_key = 'station';
		this.context_menu_name = $.i18n._( 'Station' );
		this.navigation_label = $.i18n._( 'Station' );
		this.api = TTAPI.APIStation;
		this.user_group_api = TTAPI.APIUserGroup;
		this.user_preference_api = TTAPI.APIUserPreference;

		if ( ( Global.getProductEdition() >= 20 ) ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.punch_tag_api = TTAPI.APIPunchTag;
		}

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

	initOptions( callBack ) {

		var $this = this;

		var options = [
			{ option_name: 'status', field_name: null, api: null },
			{ option_name: 'type', field_name: null, api: null },
			{ option_name: 'time_zone', field_name: 'time_zone', api: $this.user_preference_api },
			{ option_name: 'time_clock_command', field_name: null, api: null },
			{ option_name: 'poll_frequency', field_name: null, api: null },
			{ option_name: 'push_frequency', field_name: null, api: null },
			{ option_name: 'partial_push_frequency', field_name: null, api: null },
			{ option_name: 'group_selection_type', field_name: null, api: null },
			{ option_name: 'branch_selection_type', field_name: null, api: null },
			{ option_name: 'department_selection_type', field_name: null, api: null }

		];

		this.initDropDownOptions( options, function( result ) {

			$this.user_group_api.getUserGroup( '', false, false, {
				onResult: function( res ) {

					res = res.getResult();
					res = Global.buildTreeRecord( res );
					$this.user_group_array = res;

					if ( callBack ) {
						callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.
					}

				}
			} );

		} );
	}

	setCurrentEditRecordData() {
		var $this = this;
		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'type_id': 10,
			'user_group_selection_type_id': 10,
			'branch_selection_type_id': 10,
			'department_selection_type_id': 10
		} );

		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'job_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							widget.setValue( this.current_edit_record[key] );
						}
						break;
					case 'job_item_id':
						if ( ( Global.getProductEdition() >= 20 ) ) {
							var args = {};
							args.filter_data = { job_id: this.current_edit_record.job_id };
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
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}
			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	/* jshint ignore:start */
	onFormItemChange( target, doNotValidate ) {
		var $this = this;
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'type_id':
				this.onTypeChange();
				break;
			case 'job_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ), 'job_item_id', { job_id: this.current_edit_record.job_id } );
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
					this.onJobQuickSearch( key, c_value );
					this.setPunchTagValuesWhenCriteriaChanged( $this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
			case 'punch_tag_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onPunchTagQuickSearch( c_value, this.getPunchTagFilterData(), 'punch_tag_id' );

					//Don't validate immediately as onPunchTagQuickSearch is doing async API calls, and it would cause a guaranteed validation failure.
					doNotValidate = true;
				}
				break;
			case 'user_group_selection_type_id':
				this.onEmployeeGroupSelectionTypeChange();
				break;
			case 'branch_selection_type_id':
				this.onBranchSelectionTypeChange();
				break;
			case 'department_selection_type_id':
				this.onDepartmentSelectionTypeChange();
				break;

		}
		this.isDisableIncludeEmployees();
		if ( !doNotValidate ) {
			this.validate();
		}
	}

	/* jshint ignore:end */
	isDisableIncludeEmployees() {
		if ( this.edit_view_ui_dic['group'].getEnabled() || this.edit_view_ui_dic['branch'].getEnabled() || this.edit_view_ui_dic['department'].getEnabled() ) {
			this.edit_view_ui_dic['include_user'].setEnabled( true );
		} else {
			this.edit_view_ui_dic['include_user'].setEnabled( false );
		}
	}

	onEmployeeGroupSelectionTypeChange() {

		if ( parseInt( this.current_edit_record['user_group_selection_type_id'] ) == 10 ) {
			this.edit_view_ui_dic['group'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['user_group_selection_type_id'].setValue( this.current_edit_record['user_group_selection_type_id'] );
			this.edit_view_ui_dic['group'].setEnabled( true );
		}
	}

	onBranchSelectionTypeChange() {
		if ( parseInt( this.current_edit_record['branch_selection_type_id'] ) == 10 ) {

			this.edit_view_ui_dic['branch'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['branch_selection_type_id'].setValue( this.current_edit_record['branch_selection_type_id'] );
			this.edit_view_ui_dic['branch'].setEnabled( true );
		}
	}

	onDepartmentSelectionTypeChange() {
		if ( parseInt( this.current_edit_record['department_selection_type_id'] ) == 10 ) {
			this.edit_view_ui_dic['department'].setEnabled( false );
		} else {
			this.edit_view_ui_dic['department_selection_type_id'].setValue( this.current_edit_record['department_selection_type_id'] );
			this.edit_view_ui_dic['department'].setEnabled( true );
		}
	}

	onTypeChange() {
		if ( parseInt( this.current_edit_record['type_id'] ) == 100 ||
			parseInt( this.current_edit_record['type_id'] ) == 150 ||
			parseInt( this.current_edit_record['type_id'] ) == 28 ||
			parseInt( this.current_edit_record['type_id'] ) == 65 ) {

			$( this.edit_view_tab.find( 'ul li' )[2] ).show();
			var tab_2_label = this.edit_view.find( 'a[ref=tab_time_clock]' );

			if ( parseInt( this.current_edit_record['type_id'] ) == 100 ||
				parseInt( this.current_edit_record['type_id'] ) == 150 ) {
				tab_2_label.text( $.i18n._( 'TimeClock' ) );

				if ( parseInt( this.current_edit_record['type_id'] ) != 150 ) {
					this.attachElement( 'manual_command' );
					this.attachElement( 'push_frequency' );
					this.attachElement( 'partial_push_frequency' );
				} else {
					this.detachElement( 'manual_command' );
					this.detachElement( 'push_frequency' );
					this.detachElement( 'partial_push_frequency' );
				}

				this.attachElement( 'password' );
				this.attachElement( 'port' );
			} else {
				tab_2_label.text( $.i18n._( 'Mobile App' ) );
				this.detachElement( 'password' );
				this.detachElement( 'port' );
				this.detachElement( 'manual_command' );
				this.detachElement( 'push_frequency' );
				this.detachElement( 'partial_push_frequency' );
				this.detachElement( 'enable_auto_punch_status' );
			}

			this.initModeFlag();

			//#2590 - ensure field is only visible in valid types.
			if ( parseInt( this.current_edit_record['type_id'] ) == 65 ) {
				this.initDefaultModeFlag();
				this.initFacialRecognitionThesholdFields();
			} else {
				this.edit_view_ui_dic['default_mode_flag'].parents( '.edit-view-form-item-div' ).hide();
				this.edit_view_ui_dic['user_value_1'].parents( '.edit-view-form-item-div' ).hide();
				this.edit_view_ui_dic['user_value_2'].parents( '.edit-view-form-item-div' ).hide();
			}
		} else {
			$( this.edit_view_tab.find( 'ul li' )[2] ).hide();
			this.edit_view_tab.tabs( 'option', 'active', 0 );

		}

		this.editFieldResize();
	}

	initModeFlag() {
		var $this = this;
		this.api.getOptions( 'mode_flag', this.current_edit_record.type_id, true, {
			onResult: function( result ) {
				var result_data = Global.buildRecordArray( result.getResult() );

				$this.edit_view_ui_dic['mode_flag'].setSourceData( result_data );
				$this.edit_view_ui_dic['mode_flag'].setValue( $this.current_edit_record.mode_flag );

			}
		} );
	}

	initDefaultModeFlag() {
		var $this = this;
		this.api.getOptions( 'default_mode_flag', this.current_edit_record.type_id, true, {
			onResult: function( result ) {
				var result_data = Global.buildRecordArray( result.getResult() );

				$this.edit_view_ui_dic['default_mode_flag'].setSourceData( result_data );
				var value = ( $this.current_edit_record.default_mode_flag != 0 ) ? $this.current_edit_record.default_mode_flag : TTUUID.zero_id;
				$this.edit_view_ui_dic['default_mode_flag'].setValue( value );
				$this.edit_view_ui_dic['default_mode_flag'].parents( '.edit-view-form-item-div' ).show();

			}
		} );
	}

	initFacialRecognitionThesholdFields() {
		var $this = this;
		this.api.getOptions( 'face_recognition_match_threshold', {
			onResult: function( result ) {
				var result_data = Global.buildRecordArray( result.getResult() );

				$this.edit_view_ui_dic['user_value_1'].setSourceData( result_data );
				var value = ( $this.current_edit_record.user_value_1 != 0 ) ? $this.current_edit_record.user_value_1 : 0;
				$this.edit_view_ui_dic['user_value_1'].setValue( value );
				$this.edit_view_ui_dic['user_value_1'].parents( '.edit-view-form-item-div' ).show();
			}
		} );

		this.api.getOptions( 'face_recognition_required_matches', {
			onResult: function( result ) {
				var result_data = Global.buildRecordArray( result.getResult() );

				$this.edit_view_ui_dic['user_value_2'].setSourceData( result_data );
				var value = ( $this.current_edit_record.user_value_2 != 0 ) ? $this.current_edit_record.user_value_2 : 0;
				$this.edit_view_ui_dic['user_value_2'].setValue( value );
				$this.edit_view_ui_dic['user_value_2'].parents( '.edit-view-form-item-div' ).show();
			}
		} );

	}

	setEditViewDataDone() {
		var $this = this;
		super.setEditViewDataDone();

		this.onTypeChange();
		this.onEmployeeGroupSelectionTypeChange();
		this.onBranchSelectionTypeChange();
		this.onDepartmentSelectionTypeChange();
		this.isDisableIncludeEmployees();

		var runButton = this.edit_view_form_item_dic['manual_command'].find( 'button[type=\'button\']' );
		if ( $this.is_mass_editing || $this.is_viewing ) {
			this.edit_view_ui_dic['manual_command'].setEnabled( false );
			runButton.attr( 'disabled', true );
		} else {
			runButton.off( 'click' ).on( 'click', function() {
				$this.onSaveAndContinue( true );
			} );
		}
	}

	onSaveAndContinue( isRun ) {
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';
		var $this = this;
		this.is_changed = false;
		var commandData = this.edit_view_ui_dic['manual_command'].getValue();
		var commandId = this.current_edit_record.id;
		this.api['set' + this.api.key_name]( this.current_edit_record, {
			onResult: function( result ) {
				if ( isRun ) {
					$this.api['runManualCommand']( commandData, commandId, {
						onResult: function( result_1 ) {
							if ( result_1 && result_1.isValid() ) {
								var result_data = result_1.getResult();
								TAlertManager.showAlert( result_data, $.i18n._( 'Manual Command Result' ) );
								$this.onSaveAndContinueResult( result );
							} else {
								TAlertManager.showErrorAlert( result );
							}

						}
					} );
				} else {
					$this.onSaveAndContinueResult( result );
				}

			}
		} );
	}

	onSaveDone( result ) {
		if ( this.edit_only_mode && this.parent_view_controller ) {
			this.parent_view_controller.onEditStationDone( result );
		}
	}

	onBuildBasicUIFinished() {
		var station_input = this.basic_search_field_ui_dic['station_id'];

		var icon = $( '<img class="station-location" src="' + Global.getRealImagePath( 'images/location.png' ) + '">' );

		icon.insertAfter( station_input );
		icon.unbind( 'click' ).bind( 'click', function() {
			var station_id = Global.getStationID();
			if ( station_id ) {
				station_input.setValue( station_id );
			} else {
				TAlertManager.showAlert( $.i18n._( 'Current Station is not currently set.' ) );
			}
		} );
	}

	setEditMenuEditIcon( context_btn, pId ) {

		if ( !this.editPermissionValidate( pId ) ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		if ( !this.is_viewing || !this.editOwnerOrChildPermissionValidate( pId ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	openEditView( id ) {
		var $this = this;

		if ( $this.edit_only_mode ) {

			$this.initOptions( function( result ) {
				if ( !$this.edit_view ) {
					$this.is_viewing = true;
					$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
				}
				$this.getStationData( id, function( result ) {
					// Waiting for the TTAPI.API returns data to set the current edit record.
					$this.current_edit_record = result;
					//if ( !$this.editPermissionValidate() || !$this.editOwnerOrChildPermissionValidate()) {
					//	$this.is_viewing = true;
					//}

					$this.initEditView();

				} );

			} );

		} else {
			if ( !this.edit_view ) {
				this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
			}

		}
	}

	getStationData( id, callBack ) {
		var filter = {};
		filter.filter_data = {};
		filter.filter_data.id = [id];

		this.api['get' + this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();

				if ( !result_data ) {
					result_data = [];
				}
				result_data = result_data[0];

				callBack( result_data );

			}
		} );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_station': { 'label': $.i18n._( 'Station' ) },
			'tab_employee_criteria': { 'label': $.i18n._( 'Employee Criteria' ) },
			'tab_time_clock': { 'label': $.i18n._( 'TimeClock' ), 'is_multi_column': true },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		if ( !this.edit_only_mode ) {
			this.navigation.AComboBox( {
				api_class: TTAPI.APIStation,
				id: this.script_name + '_navigation',
				allow_multiple_selection: false,
				layout_name: 'global_station',
				navigation_mode: true,
				show_search_inputs: true
			} );

			this.setNavigation();
		}

		//Tab 0 start

		var tab_station = this.edit_view_tab.find( '#tab_station' );

		var tab_station_column1 = tab_station.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_station_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_station_column1, '' );

		//Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_station_column1 );

		//Station
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'station_id', width: 254 } );
		this.addEditFieldToColumn( $.i18n._( 'Station' ), form_item_input, tab_station_column1 );

		//Source
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'source', width: 289 } );
		this.addEditFieldToColumn( $.i18n._( 'Source' ), form_item_input, tab_station_column1 );

		//Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_station_column1 );

		form_item_input.parent().width( '45%' );

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
		this.addEditFieldToColumn( $.i18n._( 'Default Branch' ), form_item_input, tab_station_column1 );

		//Default Department
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input.AComboBox( {
			api_class: TTAPI.APIDepartment,
			allow_multiple_selection: false,
			layout_name: 'global_department',
			show_search_inputs: true,
			set_empty: true,
			field: 'department_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Department' ), form_item_input, tab_station_column1, '' );

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
			this.addEditFieldToColumn( $.i18n._( 'Default Job' ), [form_item_input, job_coder], tab_station_column1, '', widgetContainer );

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
				field: 'job_item_id'
			} );

			widgetContainer = $( '<div class=\'widget-h-box\'></div>' );

			var job_item_coder = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			job_item_coder.TTextInput( { field: 'job_item_quick_search', disable_keyup_event: true } );
			job_item_coder.addClass( 'job-coder' );

			widgetContainer.append( job_item_coder );
			widgetContainer.append( form_item_input );
			this.addEditFieldToColumn( $.i18n._( 'Default Task' ), [form_item_input, job_item_coder], tab_station_column1, 'last', widgetContainer );

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
			this.addEditFieldToColumn( $.i18n._( 'Default Punch Tags' ), [form_item_input, punch_tag_coder], tab_station_column1, '', widgetContainer, true );


		}

		//Tab 1 start

		var tab_employee_criteria = this.edit_view_tab.find( '#tab_employee_criteria' );
		var tab_employee_criteria_column1 = tab_employee_criteria.find( '.first-column' );

		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_employee_criteria_column1 );

		//Employee group
		var v_box = $( '<div class=\'v-box\'></div>' );

		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'user_group_selection_type_id' } );
		form_item_input.setSourceData( $this.group_selection_type_array );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			tree_mode: true,
			allow_multiple_selection: true,
			layout_name: 'global_tree_column',
			set_empty: true,
			field: 'group'
		} );
		form_item_input_1.setSourceData( $this.user_group_array );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Employee Groups' ), [form_item_input, form_item_input_1], tab_employee_criteria_column1, 'first', v_box, false, true );

		//Branches
		v_box = $( '<div class=\'v-box\'></div>' );
		//Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'branch_selection_type_id' } );
		form_item_input.setSourceData( $this.branch_selection_type_array );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIBranch,
			allow_multiple_selection: true,
			layout_name: 'global_branch',
			show_search_inputs: true,
			set_empty: true,
			field: 'branch'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Branches' ), [form_item_input, form_item_input_1], tab_employee_criteria_column1, '', v_box, false, true );

		// Departments
		v_box = $( '<div class=\'v-box\'></div>' );
		// Selection Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'department_selection_type_id' } );
		form_item_input.setSourceData( $this.department_selection_type_array );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Selection Type' ) );
		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		//Selection
		form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

		form_item_input_1.AComboBox( {
			api_class: TTAPI.APIDepartment,
			allow_multiple_selection: true,
			layout_name: 'global_department',
			show_search_inputs: true,
			set_empty: true,
			field: 'department'
		} );

		form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );

		v_box.append( form_item );

		this.addEditFieldToColumn( $.i18n._( 'Departments' ), [form_item_input, form_item_input_1], tab_employee_criteria_column1, '', v_box, false, true );

		// Include Employees
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'include_user'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Include Employees' ), form_item_input, tab_employee_criteria_column1 );

		// Exclude Employees
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: true,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'exclude_user'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Exclude Employees' ), form_item_input, tab_employee_criteria_column1, '' );

		// Tab2 start
		var tab_time_clock = this.edit_view_tab.find( '#tab_time_clock' );
		var tab_time_clock_column1 = tab_time_clock.find( '.first-column' );

		this.edit_view_tabs[2] = [];
		this.edit_view_tabs[2].push( tab_time_clock_column1 );

		// Password/COMM Key
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'password', width: 254 } );
		this.addEditFieldToColumn( $.i18n._( 'Password/COMM Key' ), form_item_input, tab_time_clock_column1, '', null, true );

		// Port
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'port', width: 254 } );
		this.addEditFieldToColumn( $.i18n._( 'Port' ), form_item_input, tab_time_clock_column1, '', null, true );

		// Force Time Zone
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'time_zone', set_empty: true } );
		form_item_input.setSourceData( $this.time_zone_array );
		this.addEditFieldToColumn( $.i18n._( 'Force Time Zone' ), form_item_input, tab_time_clock_column1 );

		// Enable Automatic Punch Status
		form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
		form_item_input.TCheckbox( { field: 'enable_auto_punch_status' } );
		this.addEditFieldToColumn( $.i18n._( 'Enable Automatic Punch Status' ), form_item_input, tab_time_clock_column1, '', null, true );

		// Manual Command
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'manual_command' } );
		form_item_input.setSourceData( $this.time_clock_command_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<button type=\'button\' class=\' t-button widget-right-label\'>' + $.i18n._( 'Run' ) + '</button>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );

		this.addEditFieldToColumn( $.i18n._( 'Manual Command' ), form_item_input, tab_time_clock_column1, '', widgetContainer, true );

		// Download Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'poll_frequency' } );
		form_item_input.setSourceData( $this.poll_frequency_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'Last Download' ) + ': ' + ' </span>' );

		var widget_text = Global.loadWidgetByName( FormItemType.TEXT );
		widget_text.TText( { field: 'last_push_date' } );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_text );

		this.addEditFieldToColumn( $.i18n._( 'Download Frequency' ), [form_item_input, widget_text], tab_time_clock_column1, '', widgetContainer );

		// Full Upload Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'push_frequency' } );
		form_item_input.setSourceData( $this.push_frequency_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'Last Upload' ) + ': ' + ' </span>' );

		widget_text = Global.loadWidgetByName( FormItemType.TEXT );
		widget_text.TText( { field: 'last_poll_date' } );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_text );
		this.addEditFieldToColumn( $.i18n._( 'Full Upload Frequency' ), [form_item_input, widget_text], tab_time_clock_column1, '', widgetContainer, true );

		// Partial Upload Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'partial_push_frequency' } );
		form_item_input.setSourceData( $this.push_frequency_array );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + $.i18n._( 'Last Upload' ) + ': </span>' );
		widget_text = Global.loadWidgetByName( FormItemType.TEXT );
		widget_text.TText( { field: 'last_partial_push_date' } );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_text );
		this.addEditFieldToColumn( $.i18n._( 'Partial Upload Frequency' ), [form_item_input, widget_text], tab_time_clock_column1, '', widgetContainer, true );

		// Last Downloaded Punch
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'last_punch_time_stamp' } );
		this.addEditFieldToColumn( $.i18n._( 'Last Downloaded Punch' ), form_item_input, tab_time_clock_column1 );

		// Configuration Modes
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			allow_multiple_selection: true,
			layout_name: 'global_option_column',
			show_search_inputs: true,
			set_empty: true,
			field: 'mode_flag'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Configuration Modes' ), form_item_input, tab_time_clock_column1, '', null, null, true );

		// Default Punch Mode
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			allow_multiple_selection: false,
			layout_name: 'global_option_column',
			show_search_inputs: true,
			set_empty: true,
			field: 'default_mode_flag'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Default Punch Mode' ), form_item_input, tab_time_clock_column1 ); //, '', null, null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			allow_multiple_selection: false,
			layout_name: 'global_option_column',
			show_search_inputs: true,
			set_empty: false,
			field: 'user_value_1'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Face Recognition Threshold' ), form_item_input, tab_time_clock_column1 ); //, '', null, null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			allow_multiple_selection: false,
			layout_name: 'global_option_column',
			show_search_inputs: true,
			set_empty: false,
			field: 'user_value_2'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Face Recognition Matches' ), form_item_input, tab_time_clock_column1 ); //, '', null, null, true );

	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [

			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 1,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Station' ),
				in_column: 1,
				field: 'station_id',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Source' ),
				in_column: 1,
				field: 'source',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Description' ),
				in_column: 2,
				field: 'description',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
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

	getPunchTagFilterData() {
		if ( !this.current_edit_record ) {
			return {};
		}

		var filter_data = {
			status_id: 10,
			user_id: TTUUID.not_exist_id,
			branch_id: this.current_edit_record.branch_id,
			department_id: this.current_edit_record.department_id,
			job_id: this.current_edit_record.job_id,
			job_item_id: this.current_edit_record.job_item_id
		};

		return filter_data;
	}

}
