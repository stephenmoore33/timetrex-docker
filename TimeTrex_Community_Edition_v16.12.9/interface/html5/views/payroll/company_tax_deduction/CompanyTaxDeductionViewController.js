import { Global } from '@/global/Global';

export class CompanyTaxDeductionViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#company_tax_deduction_view_container', //Must set el here and can only set string, so events can work

			type_array: null,
			status_array: null,
			tax_formula_type_array: null,
			calculation_array: null,
			account_amount_type_array: null,
			yes_no_array: null,
			filter_data_type_array: null,
			apply_frequency_array: null,
			apply_payroll_run_type_array: null,
			length_of_service_unit_array: null,
			month_of_year_array: null,
			day_of_month_array: null,
			month_of_quarter_array: null,
			country_array: null,
			province_array: null,
			e_province_array: null,
			company_api: null,
			date_api: null,
			user_deduction_api: null,
			user_api: null,
			payroll_remittance_agency_api: null,
			employee_setting_grid: null,
			employee_setting_result: null,
			show_c: false,
			show_p: false,
			show_dc: false,

			province_district_array: null,

			original_current_record: null, //set when setCurrentEditRecordData, to keep the original data of the edit record

			length_dates: null,
			start_dates: null,
			end_dates: null,

			grid_parent: '.grid-div'
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );
		this.edit_view_tpl = 'CompanyTaxDeductionEditView.html';
		if ( this.sub_view_mode ) {
			this.permission_id = 'user_tax_deduction';
		} else {
			this.permission_id = 'company_tax_deduction';
		}
		this.viewId = 'CompanyTaxDeduction';
		this.script_name = 'CompanyTaxDeductionView';
		this.table_name_key = 'company_deduction';
		this.context_menu_name = $.i18n._( 'Taxes & Deductions' );
		this.navigation_label = $.i18n._( 'Taxes & Deductions' );
		this.api = TTAPI.APICompanyDeduction;
		this.date_api = TTAPI.APITTDate;
		this.company_api = TTAPI.APICompany;
		this.user_deduction_api = TTAPI.APIUserDeduction;
		this.user_api = TTAPI.APIUser;
		this.payroll_remittance_agency_api = TTAPI.APIPayrollRemittanceAgency;
		this.month_of_quarter_array = Global.buildRecordArray( { 1: 1, 2: 2, 3: 3 } );
		this.document_object_type_id = 300;

		this.render();

		//Load the FormulaBuilder as early as possible to help avoid some race conditions with input box not appearing, or appearing out of order when clicking "new" after a fresh reload.
		if ( ( Global.getProductEdition() >= 15 ) ) {
			Global.loadScript( 'global/widgets/formula_builder/FormulaBuilder.js' );
		}

		if ( this.sub_view_mode ) {
			this.buildContextMenu( true );
		} else {
			this.buildContextMenu();
		}

		//call init data in parent view
		if ( !this.sub_view_mode ) {
			this.initData();
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: []
		};

		if ( this.sub_view_mode ) {
			context_menu_model.exclude.push(
				'view',
				'save_and_new',
				'save_and_copy',
				'copy_as_new',
				'copy'
			);
		}

		return context_menu_model;
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'status', api: this.api },
			{ option_name: 'calculation', api: this.api },
			{ option_name: 'filter_date_type', api: this.api },
			{ option_name: 'apply_frequency', api: this.api },
			{ option_name: 'apply_payroll_run_type', api: this.api },
			{ option_name: 'account_amount_type', api: this.api },
			{ option_name: 'length_of_service_unit', api: this.api },
			{ option_name: 'look_back_unit', api: this.api },
			{ option_name: 'country', field_name: 'country', api: this.company_api },
			{ option_name: 'apply_payroll_run_type', api: this.api },
			{ option_name: 'yes_no', api: this.api },

			{ option_name: 'tax_formula_type', api: this.api },
		];

		this.initDropDownOptions( options );

		this.company_api.getOptions( 'district', {
			onResult: function( res ) {
				res = res.getResult();
				$this.district_array = res;
			}
		} );

		this.date_api.getMonthOfYearArray( false, {
			onResult: function( res ) {
				res = res.getResult();
				$this.month_of_year_array = Global.buildRecordArray( res );
			}
		} );
		this.date_api.getDayOfMonthArray( {
			onResult: function( res ) {
				res = res.getResult();
				$this.day_of_month_array = Global.buildRecordArray( res );
			}
		} );
	}

	//Override for: Do not show a few of the default columns when in Edit Employee sub-view "Tax" tab.
	setSelectLayout() {
		if ( this.sub_view_mode ) {
			super.setSelectLayout( ['legal_entity_legal_name', 'total_users'] );
		} else {
			super.setSelectLayout();
		}
	}

	setEditMenuSaveAndContinueIcon( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_editing || this.is_viewing || ( this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id ) ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuAddIcon( context_btn, pId ) {
		if ( !this.addPermissionValidate( pId ) || this.edit_only_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		}

		ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
	}

	// The following functions are to disable various buttons on Employee Settings tab.
	// This was due to users getting confused as to what they were deleting (employee entry in table vs tax/deduc record). See issue #2688
	disableIconOnEmployeeSettingsTab( context_btn ) {
		if ( this.getEditViewActiveTabName() === 'tab_employee_setting' ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setEditMenuDeleteIcon( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	}

	setEditMenuDeleteAndNextIcon( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	}

	setEditMenuCopyIcon( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	}

	setEditMenuCopyAndAddIcon( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	}

	setEditMenuSaveAndCopyIcon( context_btn ) {
		this.disableIconOnEmployeeSettingsTab( context_btn );
	}

	enableIconOnEmployeeSettingsTab( context_btn ) {
		if ( this.getEditViewActiveTabName() === 'tab_employee_setting' ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		}
	}

	setEditMenuExportIcon( context_btn ) {
		this.enableIconOnEmployeeSettingsTab( context_btn );
	}

	onExportClick() {
		if ( this.is_edit == true && this.getEditViewActiveTabName() === 'tab_employee_setting' ) {
			this.employee_setting_grid.grid2csv( 'export_user_deduction' );
		} else {
			super.onExportClick( 'export' + this.api.key_name );
		}
	}

	saveInsideEditorData( callBack ) {
		var $this = this;

		// #2764 do not check for this.sub_view_mode as Save icon will fail to save. Save and Save&Continue should have the same logic regardless of sub_view. See issue or commit ee0102be0f45f954a78b7f96b6cf2f2350b73dd7 context on this.sub_view_mode and save&continue.
		// if ( !this.current_edit_record || !this.current_edit_record.id || this.sub_view_mode ) {
		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			if ( Global.isSet( callBack ) ) {
				callBack();
			}
			return;
		}

		if ( !this.employee_setting_grid ) {
			return;
		}

		var data = this.employee_setting_grid.getGridParam( 'data' );
		var columns = this.employee_setting_grid.getGridParam( 'colModel' );

		for ( var i = 0; i < data.length; i++ ) {
			var item = data[i];
			if ( this.start_dates && this.start_dates.length > 0 ) {
				item.start_date = this.start_dates[i].getValue();
			}
			if ( this.length_dates && this.length_dates.length > 0 ) {
				item.length_of_service_date = this.length_dates[i].getValue();
			}
			if ( this.end_dates && this.end_dates.length > 0 ) {
				item.end_date = this.end_dates[i].getValue();
			}
			for ( var j = 1; j < columns.length; j++ ) {
				var column = columns[j];
				if ( item[column.name] === this.original_current_record[column.name] ) {
					item[column.name] = false;  //Default column setting
				}
			}
		}

		if ( data && data.length > 0 ) {
			//Only send data from the Employee Settings tab to the API that has changed.
			let changed_data = this.getChangedRecords( data, this.employee_setting_result, [] );

			if ( Array.isArray( changed_data ) && changed_data.length > 0 ) {
				this.user_deduction_api.setUserDeduction( changed_data, {
					onResult: function() {
						if ( Global.isSet( callBack ) ) {
							callBack();
						}
					}
				} );
			} else {
				//Still execute the callback so Save & Next can move to the next record when there is no Employees assigned to it
				if ( Global.isSet( callBack ) ) {
					callBack();
				}
			}
		} else {
			//Still execute the callback so Save & Next can move to the next record when there is no Employees assigned to it
			if ( Global.isSet( callBack ) ) {
				callBack();
			}
		}
	}

	onContextMenuClick( context_btn, menu_name ) {
		if ( this.select_grid_last_row ) {
			this.employee_setting_grid.grid.jqGrid( 'saveRow', this.select_grid_last_row );
			this.setDateCellsEnabled( false, this.select_grid_last_row );
			this.select_grid_last_row = null;
		}

		return super.onContextMenuClick( context_btn, menu_name );
	}

	getDeleteSelectedRecordId() {
		if ( !this.sub_view_mode ) {
			return super.getDeleteSelectedRecordId();
		} else {
			var retval = [];

			if ( this.edit_view ) {
				retval.push( this.employee_setting_result[0]?.id );
			} else {
				var args = { filter_data: {} };
				var tax_ids = this.getGridSelectIdArray().slice();
				args.filter_data.company_deduction_id = tax_ids;
				args.filter_data.user_id = this.parent_value;

				var res = this.user_deduction_api.getUserDeduction( args, true, { async: false } ).getResult();

				for ( var i = 0; i < res.length; i++ ) {
					var item = res[i];
					retval.push( item.id );
				}
			}

			return retval;
		}
	}

	doDeleteAPICall( remove_ids, callback ) {
		if ( !this.sub_view_mode ) {
			return super.doDeleteAPICall( remove_ids, callback );
		} else {
			if ( !callback ) {
				callback = {
					onResult: function( result ) {
						this.onDeleteResult( result, remove_ids );
					}.bind( this )
				};
			}
			// return this.api['delete' + this.api.key_name]( remove_ids, callback );
			return this.user_deduction_api.deleteUserDeduction( remove_ids, callback );
		}
	}

	onSaveClick( ignoreWarning, force_no_confirm = false ) {
		var $this = this;

		var record;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}

		if ( this.employee_setting_grid ) {
			var data = this.employee_setting_grid.getGridParam( 'data' );
			if ( data && data.length > 0 ) {
				let changed_data = this.getChangedRecords( data, this.employee_setting_result, [] );
				if ( Array.isArray( changed_data ) && changed_data.length > 0 ) {
					$this.is_changed = true;
				}
			}
		}

		if ( !force_no_confirm
			&&
			(
				$this.is_changed == false
				&& (!LocalCacheData.current_open_primary_controller || ( LocalCacheData.current_open_primary_controller.edit_view && LocalCacheData.current_open_primary_controller.is_changed  == false ) )
				&& (!LocalCacheData.current_open_report_controller || LocalCacheData.current_open_report_controller.is_changed == false )
				&& (!LocalCacheData.current_open_edit_only_controller || LocalCacheData.current_open_edit_only_controller.is_changed == false)
				&& (!LocalCacheData.current_open_sub_controller || ( LocalCacheData.current_open_sub_controller.edit_view && LocalCacheData.current_open_sub_controller.is_changed == false ) )
			) ) {
			this.confirm_on_exit = true;
		} else {
			this.confirm_on_exit = false;
		}

		//Setting is_add false too early can cause determineContextMenuMountAttributes() to have unexpected side effects. However not setting it here might have other side effects.
		//this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save';

		if ( this.confirm_on_exit == true ) {
			TAlertManager.showConfirmAlert( Global.not_modify_alert_message, null, function( clicked_yes ) {
				if ( clicked_yes === true ) {
					doNext();
				}
			} );
		} else {
			doNext();
		}

		function doNext() {
			if ( $this.is_mass_editing ) {
				var changed_fields = $this.getChangedFields();
				record = $this.buildMassEditSaveRecord( $this.mass_edit_record_ids, changed_fields );
			} else {
				record = $this.current_edit_record;
			}
			record = $this.uniformVariable( record );
			if ( !$this.sub_view_mode ) {
				$this.api['set' + $this.api.key_name]( record, false, ignoreWarning, {
					onResult: function( result ) {
						$this.onSaveResult( result );
					}
				} );
			} else {
				if ( !$this.current_edit_record.id ) {
					$this.user_deduction_api.setUserDeduction( record, false, ignoreWarning, {
						onResult: function( result ) {
							$this.onSaveResult( result );
						}
					} );
				} else {
					$this.saveInsideEditorData( function() {
						$this.refresh_id = $this.current_edit_record.id; // as add
						$this.search();

						$this.removeEditView();
					} );
				}
			}
		}

		this.confirm_on_exit = !this.confirm_on_exit; //If we don't do this, then if the user clicks "No", then "Cancel", it will think they made changes, so they will get another confirmation box when the click Cancel.
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
		record = this.uniformVariable( record );
		if ( !this.sub_view_mode ) {
			this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
				onResult: function( result ) {
					$this.onSaveAndContinueResult( result );
				}
			} );
		} else {
			// Only edit record can go here
			$this.saveInsideEditorData( function() {
				$this.refresh_id = $this.current_edit_record.id;
				$this.search( false );
				$this.onEditClick( $this.refresh_id, true );
			} );
		}
	}

	onSaveAndNextClick( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = false;
		this.is_changed = false;

		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save_and_next';
		record = this.uniformVariable( record );

		if ( !this.sub_view_mode ) {
			this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
				onResult: function( result ) {
					$this.onSaveAndNextResult( result );
				}
			} );
		} else {
			// Only edit record can go here
			$this.saveInsideEditorData( function() {
				$this.refresh_id = $this.current_edit_record.id;
				$this.onRightArrowClick();
				$this.search( false );
			} );
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
		if ( !this.sub_view_mode ) {
			this.api['validate' + this.api.key_name]( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		} else {
			this.user_deduction_api.validateUserDeduction( record, {
				onResult: function( result ) {
					$this.validateResult( result );
				}
			} );
		}
	}

	uniformVariable( record ) {
		if ( this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id ) ) {

			record = [];

			var selected_items = this.edit_view_ui_dic.company_tax_deduction_ids.getValue();
			for ( var i = 0; i < selected_items.length; i++ ) {
				var new_record = {};
				new_record.user_id = this.parent_value;
				new_record.company_deduction_id = selected_items[i].id;
				record.push( new_record );
			}

		}

		return record;
	}

	onSaveResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();

			if ( !this.sub_view_mode ) {
				if ( result_data === true ) {
					$this.refresh_id = $this.current_edit_record.id; // as add
				} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) { // as new
					$this.refresh_id = result_data;
				}
				$this.saveInsideEditorData( function() {
					$this.search();
					$this.onSaveDone( result );

					$this.removeEditView();
				} );
			} else {
				$this.refresh_id = null;
				$this.search();
				$this.onSaveDone( result );

				$this.removeEditView();
			}

		} else {
			$this.setErrorMenu();
			$this.setErrorTips( result );

		}
	}

	onSaveDone( result ) {
		//Clearing cache to prevent issues with import Tax/Deduction columns showing outdated cached results.
		Global.clearCache( 'getOptions_columns' );
		super.onSaveDone( result );
	}

	onSaveAndCopyResult( result ) {
		var $this = this;
		if ( result && result.isValid() ) {
			var result_data = result.getResult();
			if ( result_data === true ) {
				$this.refresh_id = $this.current_edit_record.id;

			} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
				$this.refresh_id = result_data;
			}

			$this.saveInsideEditorData( function() {
				$this.search( false );
				$this.onCopyAsNewClick();

			} );

		} else {
			$this.setErrorTips( result );
			$this.setErrorMenu();
		}
	}

	_continueDoCopyAsNew() {
		this.setCurrentEditViewState( 'new' );
		LocalCacheData.current_doing_context_action = 'copy_as_new';

		if ( Global.isSet( this.edit_view ) ) {
			if ( this.employee_setting_grid ) { //TypeError: Cannot read properties of null (reading 'clearGridData')
				this.employee_setting_grid.clearGridData();
			}

			this.edit_view_ui_dic.calculation_id.setEnabled( true );
		}
		super._continueDoCopyAsNew();
	}

	clearEditViewData() {

		for ( var key in this.edit_view_ui_dic ) {

			if ( !this.edit_view_ui_dic.hasOwnProperty( key ) ) {
				continue;
			}

			this.edit_view_ui_dic[key].setValue( null );
			this.edit_view_ui_dic[key].clearErrorStyle();
		}

		if ( this.employee_setting_grid ) {
			this.employee_setting_grid.clearGridData();
		}
	}

	checkTabPermissions( tab ) {
		var retval = false;

		switch ( tab ) {
			case 'tab_attachment':
				if ( this.subDocumentValidate() ) {
					retval = !this.sub_view_mode;
				} else {
					retval = false;
				}
				break;
			case 'tab_tax_deductions':
			case 'tab_eligibility':
			case 'tab_employee_setting':
			case 'tab_audit':
				//Don't show these tabs when under Edit Employee, Tax tab.
				if ( this.sub_view_mode ) {
					if ( tab == 'tab_employee_setting' && this.current_edit_record.id ) {
						retval = true;
					} else {
						retval = false;
					}
				} else {
					retval = true;
				}
				break;
			case 'tab5':
				if ( this.sub_view_mode ) {
					if ( tab == 'tab5' && this.current_edit_record.id ) {
						retval = false;
					} else {
						retval = true;
					}
				}
				break;
			default:
				retval = super.checkTabPermissions( tab );
				break;
		}

		return retval;
	}

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === '-1' || val === '0' ) {
			$this.province_array = [];

		} else {

			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res );

				}
			} );
		}
	}

	eSetProvince( val, refresh ) {

		var $this = this;
		var province_widget = $this.edit_view_ui_dic['province'];

		if ( !val || val === '-1' || val === '0' ) {
			$this.e_province_array = [];
			province_widget.setSourceData( [] );
		} else {
			this.company_api.getOptions( 'province', val, {
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.e_province_array = Global.buildRecordArray( res );
					province_widget.setSourceData( $this.e_province_array );
					$this.setProvinceVisibility();

				}
			} );
		}
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();
		this.current_edit_record[key] = c_value;

		switch ( key ) {

			case 'country':
				var widget = this.edit_view_ui_dic['province'];
				var widget_2 = this.edit_view_ui_dic['district'];
				widget.setValue( null );
				widget_2.setValue( null );
				this.current_edit_record.province = false;
				this.current_edit_record.district = false;
				this.setDynamicFields( null, true );
				break;
			case 'province':
				widget_2 = this.edit_view_ui_dic['district'];
				this.setDistrict( this.current_edit_record['country'] );
				widget_2.setValue( null );
				this.setDynamicFields( null, true );
				break;
			case 'calculation_id':
				this.setDynamicFields();
				break;
			case 'apply_frequency_id':
				this.onApplyFrequencyChange();
				break;
			case 'minimum_length_of_service_unit_id':
			case 'maximum_length_of_service_unit_id':
				this.onLengthOfServiceChange();
				break;
			case 'start_date':
			case 'end_date':
			case 'minimum_length_of_service':
			case 'maximum_length_of_service':
				this.resetEmployeeSettingGridColumns();
				break;
			case 'legal_entity_id':
				this.onLegalEntityChange();
				this.updateEmployeeData();
				break;
			case 'user_value10':
				this.onFormW4VersionChange();
				break;
		}

		if ( key === 'country' ) {
			this.onCountryChange();
			return;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	setCurrentEditRecordData() {
		var $this = this;

		this.original_current_record = Global.clone( this.current_edit_record );
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'country':
						this.eSetProvince( this.current_edit_record[key] );
						this.setDistrict( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		if ( $this.current_edit_record.id ) {
			$this.edit_view_ui_dic.calculation_id.setEnabled( false );
		} else {
			$this.edit_view_ui_dic.calculation_id.setEnabled( true );
		}

		this.setDynamicFields( function() {
			$this.collectUIDataToCurrentEditRecord();
			$this.onLengthOfServiceChange();
			$this.setEditViewDataDone();
			$this.edit_view_ui_dic.company_tax_deduction_ids.setGridColumnsWidths();
			$this.onLegalEntityChange();

			if ( $this.is_mass_editing ) {
				$this.hideFieldsForMassEdit();
			}
		} );

		if ( this.sub_view_mode && ( !this.current_edit_record || !this.current_edit_record.id ) ) {
			this.initCompanyTaxDeductionData();
		}
	}

	onLegalEntityChange() {
		var pra_value = this.edit_view_ui_dic.payroll_remittance_agency_id.getValue();
		var new_arg = {};
		new_arg.filter_data = { legal_entity_id: this.edit_view_ui_dic.legal_entity_id.getValue() };
		new_arg.filter_columns = this.edit_view_ui_dic.payroll_remittance_agency_id.getColumnFilter();

		var $this = this;
		if ( this.edit_view_ui_dic.legal_entity_id.getValue() != TTUUID.zero_id ) {
			this.payroll_remittance_agency_api.getPayrollRemittanceAgency( new_arg, {
				onResult: function( task_result ) {
					var data = task_result.getResult();

					if ( $this.edit_view_ui_dic.payroll_remittance_agency_id ) {
						if ( data.length > 0 ) {
							$this.edit_view_ui_dic.payroll_remittance_agency_id.setSourceData( data );

							var id_in_result = false;
							for ( var i in data ) {
								if ( data[i].id == pra_value ) {
									id_in_result = true;
									break;
								}
							}

							if ( id_in_result === false ) {
								pra_value = TTUUID.zero_id;
							}

							$this.current_edit_record.payroll_remittance_agency_id = pra_value;
							$this.edit_view_ui_dic.payroll_remittance_agency_id.setValue( pra_value );

						} else {
							$this.edit_view_ui_dic.payroll_remittance_agency_id.setValue( TTUUID.zero_id );
						}
						$this.edit_view_ui_dic.payroll_remittance_agency_id.setEnabled( true );
					}
				}
			} );
		} else {
			pra_value = TTUUID.zero_id;
			$this.edit_view_ui_dic.payroll_remittance_agency_id.setSourceData( [TTUUID.zero_id] ); //wipe the box
			$this.edit_view_ui_dic.payroll_remittance_agency_id.setValue( pra_value );
			$this.edit_view_ui_dic.payroll_remittance_agency_id.setEnabled( false );
		}
	}

	updateEmployeeData() {
		var request_data = { filter_data: {} };
		if ( this.edit_view_ui_dic && this.edit_view_ui_dic.legal_entity_id && this.edit_view_ui_dic.legal_entity_id.getValue() && this.edit_view_ui_dic.legal_entity_id.getValue() != TTUUID.zero_id ) {
			request_data.filter_data.legal_entity_id = this.edit_view_ui_dic.legal_entity_id.getValue();
		}
		if ( this.edit_view_ui_dic.user ) {
			this.edit_view_ui_dic.user.setDefaultArgs( request_data );
			this.edit_view_ui_dic.user.setSourceData( null );
		}
	}

	onLengthOfServiceChange() {

		if ( this.sub_view_mode ) {
			return;
		}

		if ( this.current_edit_record['minimum_length_of_service_unit_id'] == 50 || this.current_edit_record['maximum_length_of_service_unit_id'] == 50 ) {
			this.attachElement( 'length_of_service_contributing_pay_code_policy_id' );
		} else {
			this.detachElement( 'length_of_service_contributing_pay_code_policy_id' );
		}

		this.editFieldResize();
	}

	initCompanyTaxDeductionData() {

		var $this = this;

		var request_data = {
			filter_data: {
				legal_entity_id: [this.parent_edit_record.legal_entity_id, TTUUID.zero_id, TTUUID.not_exist_id],
				exclude_user_id: this.parent_edit_record.id //Don't show records the employee is already assinged to. Helps prevent duplicate mappings.
			},
			filter_columns: { //Make sure we limit the columns, otherwise this can be slow to load since it tries to count the number of employees assigned to each Tax/Deduction record.
				id: true,
				name: true,
			}
		};

		this.api.getCompanyDeduction( request_data, true, {
			onResult: function( result ) {
				var result_data = result.getResult();
				$this.edit_view_ui_dic.company_tax_deduction_ids.setUnselectedGridData( result_data );
			}
		} );
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onApplyFrequencyChange();
		this.initEmployeeSetting();
		this.updateEmployeeData();
	}

	setDistrict( c ) {
		var $this = this;
		var district_widget = $this.edit_view_ui_dic['district'];

		$this.province_district_array = [];
		district_widget.setSourceData( $this.province_district_array );
		if ( c ) {
			var pd_array = this.district_array[c];

			if ( pd_array ) {
				var pd_array_item = pd_array[$this.current_edit_record.province];

				if ( pd_array_item ) {
					$this.province_district_array = Global.buildRecordArray( pd_array_item );
					district_widget.setSourceData( $this.province_district_array );
				}

			}
		}

		$this.setDistrictVisibility();
	}

	hideFieldsForMassEdit() {
		this.hideAllDynamicFields();
		this.detachElement( 'df_0' );
		this.detachElement( 'calculation_id' );
		this.detachElement( 'calculation_order' );
		this.detachElement( 'pay_stub_entry_account_id' );
		this.detachElement( 'include_account_amount_type_id' );
		this.detachElement( 'include_pay_stub_entry_account' );
		this.detachElement( 'exclude_account_amount_type_id' );
		this.detachElement( 'exclude_pay_stub_entry_account' );
	}

	hideAllDynamicFields( keepC, keepP ) {

		if ( !this.edit_view ) {
			return;
		}

		if ( !keepC ) {
			this.show_c = false;
			this.detachElement( 'country' );
		}

		if ( !keepP ) {
			this.show_p = false;
			this.show_dc = false;
			this.detachElement( 'province' );
			this.detachElement( 'district' );
		}

		this.detachElement( 'df_0' );
		this.detachElement( 'df_1' );
		this.detachElement( 'df_2' );
		this.detachElement( 'df_3' );
		this.detachElement( 'df_4' );
		this.detachElement( 'df_5' );
		this.detachElement( 'df_6' );
		this.detachElement( 'df_7' );
		this.detachElement( 'df_8' );
		this.detachElement( 'df_9' );
		this.detachElement( 'df_10' );
		this.detachElement( 'df_11' );
		this.detachElement( 'df_12' );
		this.detachElement( 'df_14' );
		this.detachElement( 'df_15' );

		this.detachElement( 'df_20' );
		this.detachElement( 'df_21' );
		this.detachElement( 'df_22' );
		this.detachElement( 'df_23' );
		this.detachElement( 'df_24' );
		this.detachElement( 'df_25' );

		if ( !( Global.getProductEdition() >= 15 ) ) {
			this.detachElement( 'df_100' );
		}
	}

	initEmployeeSetting() {
		var $this = this;

		if ( !$this.edit_view ) {
			return;
		}

		if ( !this.current_edit_record || !this.current_edit_record.id ) {
			$this.employee_setting_result = [];
			//Don't display the Employee Settings grid headers when its a new record.
			//$this.setEmployeeSettingGridData( $this.buildEmployeeSettingGrid() );
			return;
		}

		// Specify which menu to use for Employee Settings tab, and use disableIconOnEmployeeSettingsTab() to disable certain icons. Related to #2688
		this.buildContextMenu( true );
		this.setEditMenu();
		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );
		var len = context_menu_array.length;
		for ( var i = 0; i < len; i++ ) {
			let context_btn = context_menu_array[i];
			let id = context_menu_array[i].id;

			if ( id === 'export_excel' ) {
				this.setEditMenuExportIcon( context_btn );
				break;
			}
		}


		var args = { filter_data: {} };

		args.filter_data.company_deduction_id = this.current_edit_record.id;

		if ( this.sub_view_mode ) {
			args.filter_data.user_id = this.parent_value;
		}

		this.user_deduction_api.getUserDeduction( args, true, {
			onResult: function( result ) {

				if ( !$this.edit_view ) {
					return;
				}

				$this.employee_setting_result = result.getResult();
				$this.setEmployeeSettingGridData( $this.buildEmployeeSettingGrid() );
			}
		} );
	}

	resetEmployeeSettingGridColumns() {
		if ( this.employee_setting_grid ) {
			var data = this.employee_setting_grid.getGridParam( 'data' );
			Global.formatGridData( data, this.api.key_name );
			this.buildEmployeeSettingGrid();
			this.employee_setting_grid.setData( data );
			this.removeEmployeeSettingNoResultCover();
			this.setEmployeeGridDateColumns();
			this.setEmployeeGridSize();
			if ( data.length < 1 && this.current_edit_record.id ) {
				this.showEmployeeSettingNoResultCover();
			}
		}
	}

	getColumnOptionsString( column_options_arr ) {
		var column_options_string = '';
		for ( var i = 0; i < column_options_arr.length; i++ ) {
			if ( i !== column_options_arr.length - 1 ) {
				column_options_string += column_options_arr[i].fullValue + ':' + column_options_arr[i].label + ';';
			} else {
				column_options_string += column_options_arr[i].fullValue + ':' + column_options_arr[i].label;
			}
		}

		return column_options_string;
	}

	/* jshint ignore:start */
	buildEmployeeSettingGrid() {
		var $this = this;
		var column_info_array = [];

		var column_info = {
			name: 'employee_number',
			index: 'employee_number',
			label: $.i18n._( 'Employee Number' ),
			width: 100,
			sortable: false,
			title: false
		};
		column_info_array.push( column_info );

		var column_info = {
			name: 'user_name',
			index: 'user_name',
			label: $.i18n._( 'Employee' ),
			width: 100,
			sortable: false,
			title: false
		};
		column_info_array.push( column_info );

		$this.api.getOptions( 'calculation_type_column_meta_data', {
			'calculation_id': $this.current_edit_record.calculation_id,
			'country': $this.current_edit_record.country,
			'province': $this.current_edit_record.province
		}, {
			//Issue #3302 - Opening tax/deduction record on Employee -> Employees would not always show correct data on first opening the record. (Only after this call was cached)
			//This call needs to be synchronous so that the column_info_array is populated with all relevant data before the grid is built and function returns.
			async: false,
			onResult: function( result ) {
				result = result.getResult();

				for ( var key in result ) {
					let meta_data = result[key];
					let dynamic_field_id = meta_data['dynamic_field_id'];

					if ( meta_data.type_id == 2100 ) {
						//$this.edit_view_ui_dic[dynamic_field_id].setSourceData( Global.buildRecordArray( meta_data.multi_select_items ) );
						column_info = {
							name: key,
							index: key,
							label: meta_data.name,
							width: meta_data.width,
							sortable: false,
							formatter: 'select',
							editable: true,
							title: false,
							edittype: 'select',
							editoptions: {
								defaultValue: meta_data.default_value, //This is required to prevent a blank cell from appearing if they haven't saved the Tax/Deduction record since the upgrade.
								value: $this.getColumnOptionsString( Global.buildRecordArray( meta_data.multi_select_items ) ),
								//dataEvents: [ {type: 'change', fn:function(e) { $this.onFormItemChange( e.target, true )}} ],
							}
						};
					} else {
						column_info = {
							name: key,
							index: key,
							label: meta_data.name,
							width: meta_data.width,
							sortable: false,
							title: false,
							editable: true,
							edittype: 'text'
						};
					}
					column_info_array.push( column_info );
				}
			}
		} );

		if ( ( this.current_edit_record.minimum_length_of_service && this.current_edit_record.minimum_length_of_service != 0 ) ||
			( this.current_edit_record.maximum_length_of_service && this.current_edit_record.maximum_length_of_service ) != 0 ) {
			column_info = {
				name: 'length_of_service_date',
				index: 'length_of_service_date',
				label: $.i18n._( 'Length of Service Date' ),
				width: 110,
				sortable: false,
				title: false,
				editable: false,
				formatter: this.onLengthDateCellFormat
			};
			column_info_array.push( column_info );
		} else {
			$( '.row-date-picker-length-of-service-date' ).remove();
		}

		if ( this.current_edit_record.start_date || this.current_edit_record.end_date ) {
			column_info = {
				name: 'start_date',
				index: 'start_date',
				label: $.i18n._( 'Start Date' ),
				width: 110,
				sortable: false,
				title: false,
				editable: false,
				formatter: this.onStartDateCellFormat
			};
			column_info_array.push( column_info );

			column_info = {
				name: 'end_date',
				index: 'end_date',
				label: $.i18n._( 'End Date' ),
				width: 110,
				sortable: false,
				title: false,
				editable: false,
				formatter: this.onEndDateCellFormat
			};
			column_info_array.push( column_info );
		} else {
			$( '.row-date-picker-start-date' ).remove();
			$( '.row-date-picker-end-date' ).remove();
		}

		//Add Exempt column to all Federal/Provincial/State/District taxes.
		if ( ( this.current_edit_record.calculation_id == 100 || this.current_edit_record.calculation_id == 200 || this.current_edit_record.calculation_id == 300 ) && this.current_edit_record.country == 'US' ) {
			column_info = {
				name: 'user_value10',
				index: 'user_value10',
				label: $.i18n._( 'Exempt' ),
				width: 30,
				sortable: false,
				formatter: 'select',
				editable: true,
				title: false,
				edittype: 'select',
				editoptions: { value: this.getColumnOptionsString( this.yes_no_array ) }
			};
			column_info_array.push( column_info );
		}

		if ( this.employee_setting_grid ) {
			this.employee_setting_grid.grid.jqGrid( 'GridUnload' );
			this.employee_setting_grid = null;
		}

		this.employee_setting_grid = new TTGrid( 'employee_setting_grid', {
			container_selector: '.edit-view-tab',
			multiselect: false,
			winMultiSelect: false,
			colModel: column_info_array,
			editurl: 'clientArray',
			onSelectRow: function( id ) {
				if ( id && !$this.is_viewing ) {

					if ( $this.select_grid_last_row ) {
						$this.employee_setting_grid.grid.jqGrid( 'saveRow', $this.select_grid_last_row );
						$this.setDateCellsEnabled( false, $this.select_grid_last_row );
					}

					$this.employee_setting_grid.grid.jqGrid( 'editRow', id, true );
					$this.setDateCellsEnabled( true, id );
					$this.select_grid_last_row = id;
				}
			},
			onEndEditRow: function( id ) {
				$this.setDateCellsEnabled( false, id );
			},
			gridComplete: function() {
				$this.setEmployeeGridSize();
			}
		}, column_info_array );

		return column_info_array;
	}

	setEditViewTabSize() {
		super.setEditViewTabSize();
		this.setEmployeeGridSize();
	}

	setDateCellsEnabled( flag, row_id ) {
		this.length_dates_dic[row_id] && this.length_dates_dic[row_id].setEnabled( flag );
		this.start_dates_dic[row_id] && this.start_dates_dic[row_id].setEnabled( flag );
		this.end_dates_dic[row_id] && this.end_dates_dic[row_id].setEnabled( flag );
	}

	onLengthDateCellFormat( cell_value, related_data, row ) {

		var form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.addClass( 'row-date-picker-length-of-service-date' );
		form_item_input.attr( 'widget-value', cell_value );
		return form_item_input.get( 0 ).outerHTML;
	}

	onStartDateCellFormat( cell_value, related_data, row ) {

		var form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.addClass( 'row-date-picker-start-date' );
		form_item_input.attr( 'widget-value', cell_value );
		return form_item_input.get( 0 ).outerHTML;
	}

	onEndDateCellFormat( cell_value, related_data, row ) {

		var form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.addClass( 'row-date-picker-end-date' );
		form_item_input.attr( 'widget-value', cell_value );
		return form_item_input.get( 0 ).outerHTML;
	}

	setEmployeeSettingGridData( column_info_array ) {

		var $this = this;
		var grid_source = [];
		if ( $.type( this.employee_setting_result ) === 'array' ) {
			grid_source = this.employee_setting_result.slice();
		}

		var len = grid_source.length;
		for ( var i = 0; i < len; i++ ) {
			var item = grid_source[i];
			item.user_name = ( ( item.user_status_id != 10 ) ? '(' + item.user_status + ') ' : '' ) + item.full_name;
			for ( var j = 1; j < column_info_array.length; j++ ) {

				var column = column_info_array[j];
				if ( !item[column.name] ) {
					item[column.name] = ( this.current_edit_record.hasOwnProperty( column.name ) && this.current_edit_record[column.name] !== false ) ? this.current_edit_record[column.name] : '';
				}
			}

		}

		$this.employee_setting_grid.setData( grid_source );
		this.removeEmployeeSettingNoResultCover();
		this.setEmployeeGridDateColumns();

		this.setEmployeeGridSize();

		if ( grid_source.length < 1 && this.current_edit_record.id ) {
			this.showEmployeeSettingNoResultCover();
		}
	}

	setEmployeeGridDateColumns() {
		var i, date_picker;
		this.length_dates = [];
		this.start_dates = [];
		this.end_dates = [];
		this.length_dates_dic = {};
		this.start_dates_dic = {};
		this.end_dates_dic = {};
		var date_pickers = $( '.row-date-picker-length-of-service-date' );
		for ( var i = 0; i < date_pickers.length; i++ ) {
			date_picker = $( date_pickers[i] ).TDatePicker( { field: 'length_of_service_date' + i } );
			date_picker.setEnabled( false );
			this.length_dates.push( date_picker );
			this.length_dates_dic[date_picker.parent().parent().attr( 'id' )] = date_picker;
		}
		date_pickers = $( '.row-date-picker-start-date' );
		for ( var i = 0; i < date_pickers.length; i++ ) {
			date_picker = $( date_pickers[i] ).TDatePicker( { field: 'start_date' + i } );
			date_picker.setEnabled( false );
			this.start_dates.push( date_picker );
			this.start_dates_dic[date_picker.parent().parent().attr( 'id' )] = date_picker;
		}
		date_pickers = $( '.row-date-picker-end-date' );
		for ( var i = 0; i < date_pickers.length; i++ ) {
			date_picker = $( date_pickers[i] ).TDatePicker( { field: 'end_date' + i } );
			date_picker.setEnabled( false );
			this.end_dates.push( date_picker );
			this.end_dates_dic[date_picker.parent().parent().attr( 'id' )] = date_picker;
		}
	}

	showEmployeeSettingNoResultCover() {

		this.removeEmployeeSettingNoResultCover();
		this.employee_setting_no_result_box = Global.loadWidgetByName( WidgetNamesDic.NO_RESULT_BOX );
		this.employee_setting_no_result_box.NoResultBox( { related_view_controller: this, is_new: false } );
		this.employee_setting_no_result_box.attr( 'id', this.ui_id + 'employee_setting_no_result_box' );
		var grid_div = this.edit_view.find( '.employee-setting-grid-div' );

		grid_div.append( this.employee_setting_no_result_box );
	}

	removeEmployeeSettingNoResultCover() {
		if ( this.employee_setting_no_result_box && this.employee_setting_no_result_box.length > 0 ) {
			this.employee_setting_no_result_box.remove();
		}
		this.employee_setting_no_result_box = null;
	}

	setEmployeeGridSize() {
		if ( !this.employee_setting_grid ) {
			return;
		}

		var tab_employee_setting = this.edit_view.find( '#tab_employee_setting_content_div' );
		this.employee_setting_grid.grid.setGridWidth( tab_employee_setting.width() );
		this.employee_setting_grid.grid.setGridHeight( tab_employee_setting.height() );
	}

	setCountryVisibility() {

		if ( this.show_c ) {
			this.attachElement( 'country' );
		} else {
			this.detachElement( 'country' );
		}
	}

	setProvinceVisibility() {
		if ( this.show_p && this.e_province_array && this.e_province_array.length > 1 ) {
			this.attachElement( 'province' );
		} else {
			this.detachElement( 'province' );
		}
	}

	setDistrictVisibility() {

		if ( this.show_dc && this.province_district_array && this.province_district_array.length > 0 ) {
			this.attachElement( 'district' );
		} else {
			this.detachElement( 'district' );
			this.current_edit_record.district = false;
		}
	}

	setDynamicFields( callBack, countryOrP ) {

		var $this = this;
		if ( !this.current_edit_record.calculation_id ) {
			this.current_edit_record.calculation_id = '10';
			this.edit_view_ui_dic.calculation_id.setValue( 10 );
		}

		var c_id = this.current_edit_record.calculation_id;

		if ( c_id == 20 ) {
			this.detachElement( 'include_account_amount_type_id' );
			this.detachElement( 'exclude_account_amount_type_id' );
		} else {
			this.attachElement( 'include_account_amount_type_id' );
			this.attachElement( 'exclude_account_amount_type_id' );
		}

		if ( !countryOrP ) {
			this.hideAllDynamicFields();
			this.api.isCountryCalculationID( c_id, {
				onResult: function( result_1 ) {
					var res_data_1 = result_1.getResult();

					if ( res_data_1 === true ) {
						$this.show_c = true;
						$this.setCountryVisibility();
						$this.api.isProvinceCalculationID( c_id, {
							onResult: function( result_2 ) {
								var res_data_2 = result_2.getResult();
								if ( res_data_2 === true ) {
									$this.show_p = true;

									if ( $this.current_edit_record.country ) {
										$this.eSetProvince( $this.current_edit_record.country );
									}

									$this.api.isDistrictCalculationID( c_id, {
										onResult: function( result_3 ) {
											var res_data_3 = result_3.getResult();

											if ( res_data_3 === true ) {
												$this.show_dc = true;

												if ( $this.current_edit_record.country ) {
													$this.setDistrict( $this.current_edit_record.country );
												}

											}

											handleDynamicFields();
										}
									} );

								} else {
									if ( $this.current_edit_record ) {
										handleDynamicFields();
									}
								}
							}
						} );

					} else {
						$this.hideAllDynamicFields();
						handleDynamicFields();
					}

				}
			} );
		} else {
			if ( !this.show_p ) {
				$this.hideAllDynamicFields( true, false );
				handleDynamicFields();
			} else {
				$this.hideAllDynamicFields( true, true );
				handleDynamicFields();
			}
		}

		function handleDynamicFields() {
			if ( !$this.edit_view ) {
				return;
			}

			if ( $this.current_edit_record.calculation_id == '100' || $this.current_edit_record.calculation_id == '200' || $this.current_edit_record.calculation_id == '300' ) {
				$this.attachElement( 'df_15' );
				$this.edit_view_form_item_dic.df_15.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Formula Type' ) );
				$this.edit_view_ui_dic.df_15.setField( 'company_value1' );
				$this.edit_view_ui_dic.df_15.setValue( $this.current_edit_record.company_value1 );
			}

			if ( $this.current_edit_record.calculation_id == '69' && Global.getProductEdition() == 10 ) {
				$this.attachElement( 'df_100' );
				$this.edit_view_ui_dic.df_100.html( Global.getUpgradeMessage() );
			} else {
				$this.api.getOptions( 'calculation_type_column_meta_data', {
					'calculation_id': $this.current_edit_record.calculation_id,
					'country': $this.current_edit_record.country,
					'province': $this.current_edit_record.province,

				}, {
					onResult: function( result ) {
						result = result.getResult();

						for ( var key in result ) {
							let meta_data = result[key];
							let dynamic_field_id = meta_data['dynamic_field_id'];

							$this.attachElement( dynamic_field_id );
							$this.edit_view_form_item_dic[dynamic_field_id].find( '.edit-view-form-item-label' ).text( meta_data.name.replace('<br>', ' ') );
							if ( meta_data.type_id == 2100 ) {
								$this.edit_view_ui_dic[dynamic_field_id].setSourceData( Global.buildRecordArray( meta_data.multi_select_items ) );
							}
							$this.edit_view_ui_dic[dynamic_field_id].setField( key );
							$this.edit_view_ui_dic[dynamic_field_id].setValue( $this.current_edit_record[key] );
						}

						if ( $this.current_edit_record.calculation_id == '69' ) { //69=Custom Formula.
							$this.attachElement( 'df_11' );
							$this.edit_view_form_item_dic.df_11.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Formula' ) );
							$this.edit_view_ui_dic.df_11.setField( 'company_value1' );
							$this.edit_view_ui_dic.df_11.setValue( $this.current_edit_record.company_value1 );

							$this.attachElement( 'df_12' );
							$this.edit_view_form_item_dic.df_12.find( '.edit-view-form-item-label' ).text( $.i18n._( 'Look Back Period' ) );
							$this.edit_view_ui_dic.df_12.setField( 'company_value2' );
							$this.edit_view_ui_dic.df_12.setValue( $this.current_edit_record.company_value2 );
							$this.edit_view_ui_dic.df_13.setField( 'company_value3' );
							$this.edit_view_ui_dic.df_13.setValue( $this.current_edit_record.company_value3 );
						}

						$this.editFieldResize( $this.getEditViewTabIndex() );
						if ( callBack ) {
							callBack();
						}
					}
				} );
			}
		}
	}

	//When its a 2020 or later Form W4, try to disable the Allowance field as its not on the form.
	//  This is most important when using the Employee Settings tab though.
	onFormW4VersionChange() {
		if ( this.current_edit_record.calculation_id == 100 && this.current_edit_record.country == 'US' ) {
			// if ( this.edit_view_ui_dic.df_20.getValue() == 2020 ) {
			// 	this.edit_view_ui_dic.df_1.setEnabled( false );
			// 	this.edit_view_ui_dic.df_21.setEnabled( true );
			// 	this.edit_view_ui_dic.df_22.setEnabled( true );
			// 	this.edit_view_ui_dic.df_23.setEnabled( true );
			// 	this.edit_view_ui_dic.df_24.setEnabled( true );
			// 	this.edit_view_ui_dic.df_25.setEnabled( true );
			// } else {
			// 	this.edit_view_ui_dic.df_1.setEnabled( true );
			// 	this.edit_view_ui_dic.df_21.setEnabled( false );
			// 	this.edit_view_ui_dic.df_22.setEnabled( false );
			// 	this.edit_view_ui_dic.df_23.setEnabled( false );
			// 	this.edit_view_ui_dic.df_24.setEnabled( false );
			// 	this.edit_view_ui_dic.df_25.setEnabled( false );
			// }
		}
	}

	onApplyFrequencyChange() {
		this.edit_view_ui_dic['apply_frequency_day_of_month1'].parent().parent().css( 'display', 'none' ); //Special fields for Semi-Monthly
		this.edit_view_ui_dic['apply_frequency_day_of_month2'].parent().parent().css( 'display', 'none' ); //Special fields for Semi-Monthly

		if ( this.current_edit_record.apply_frequency_id == 10 ||
			this.current_edit_record.apply_frequency_id == 100 ||
			this.current_edit_record.apply_frequency_id == 110 ||
			this.current_edit_record.apply_frequency_id == 120 ||
			this.current_edit_record.apply_frequency_id == 130 ||
			//Issue #3399 - When mass editing apply_frequency_id may be false, causing the fields to be shown when they shouldn't be.
			( this.is_mass_editing && this.current_edit_record.apply_frequency_id === false ) ) {

			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );
		} else if ( this.current_edit_record.apply_frequency_id == 20 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );
		} else if ( this.current_edit_record.apply_frequency_id == 25 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'block' );
		} else if ( this.current_edit_record.apply_frequency_id == 30 ) {
			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'block' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );
		} else if ( this.current_edit_record.apply_frequency_id == 35 ) {
			this.edit_view_ui_dic['apply_frequency_day_of_month1'].parent().parent().css( 'display', 'block' ); //Special fields for Semi-Monthly
			this.edit_view_ui_dic['apply_frequency_day_of_month2'].parent().parent().css( 'display', 'block' ); //Special fields for Semi-Monthly

			this.edit_view_ui_dic['apply_frequency_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_day_of_month'].parent().parent().css( 'display', 'none' );
			this.edit_view_ui_dic['apply_frequency_quarter_month'].parent().parent().css( 'display', 'none' );
		}

		this.editFieldResize();
	}

	onCalculationChange() {
	}

	buildEditViewUI() {
		TTPromise.add( 'CompanyTaxDeduction', 'buildEditViewUI' );

		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_tax_deductions': {
				'label': $.i18n._( 'Tax / Deductions' )
			},
			'tab_eligibility': { 'label': $.i18n._( 'Eligibility' ) },
			'tab_employee_setting': {
				'label': $.i18n._( 'Employee Settings' ),
				'init_callback': 'initEmployeeSetting',
				'display_on_mass_edit': false,
				'html_template': this.getCompanyTaxDeductionEmployeeSettingTabHtml()
			}, //Callback was: setEmployeeGridSize
			'tab5': {
				'label': $.i18n._( 'Tax / Deductions' ),
				'display_on_mass_edit': false
			},
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.edit_view.children().eq( 0 ).css( 'min-width', 1170 );

		this.navigation.AComboBox( {
			id: this.script_name + '_navigation',
			api_class: TTAPI.APICompanyDeduction,
			allow_multiple_selection: false,
			layout_name: 'global_deduction',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_tax_deductions = this.edit_view_tab.find( '#tab_tax_deductions' );

		var tab_tax_deductions_column1 = tab_tax_deductions.find( '.first-column' );
		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_tax_deductions_column1 );

		var form_item_input;
		var widgetContainer;
		var label;

		// Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'status_id', set_empty: false } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_tax_deductions_column1, '' );

		// Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_tax_deductions_column1 );

		//Legal entity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.TText( { field: 'legal_entity_id' } );
		form_item_input.AComboBox( {
			api_class: TTAPI.APILegalEntity,
			allow_multiple_selection: false,
			layout_name: 'global_legal_entity',
			show_search_inputs: false,
			set_empty: true,
			field: 'legal_entity_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_tax_deductions_column1 );

		//Payroll Remittance Agency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayrollRemittanceAgency,
			allow_multiple_selection: false,
			layout_name: 'global_payroll_remittance_agency',
			set_empty: true,
			field: 'payroll_remittance_agency_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Remittance Agency' ), form_item_input, tab_tax_deductions_column1 );

		//Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_tax_deductions_column1 );

		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_tax_deductions_column1, '', null, null, true );
		form_item_input.parent().width( '45%' );

		//Pay Stub Note (Public)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'pay_stub_entry_description', width: 300 } );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Note (Public)' ), form_item_input, tab_tax_deductions_column1 );

		//Calculation Settings label
		form_item_input = Global.loadWidgetByName( FormItemType.SEPARATED_BOX );
		form_item_input.SeparatedBox( { label: $.i18n._( 'Calculation Settings' ) } );
		this.addEditFieldToColumn( null, form_item_input, tab_tax_deductions_column1, '', null, true, false, 'separated_2' );

		//Calculation
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'calculation_id', set_empty: false, width: 400 } );
		form_item_input.setSourceData( $this.calculation_array );
		this.addEditFieldToColumn( $.i18n._( 'Calculation' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Dynamic Field 15
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'df_15', set_empty: false } );
		form_item_input.setSourceData( $this.tax_formula_type_array );
		this.addEditFieldToColumn( 'df_15', form_item_input, tab_tax_deductions_column1, '', null, true );

		// Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( $this.country_array );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Province
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'province' } );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// District
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'district', set_empty: false } );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'District' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Dynamic Field 0
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_0' } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> %</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( 'df_0', form_item_input, tab_tax_deductions_column1, '', widgetContainer, true );

		//Dynamic Field 20 -- Form W-4 Version (Should go above Filing Status)
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'df_20', set_empty: false } );
		this.addEditFieldToColumn( 'df_20', form_item_input, tab_tax_deductions_column1, '', null, true );

		//Dynamic Field 14 -- Filing Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'df_14', set_empty: false } ); //Don't show empty value (NONE), so a filing status will always selected.
		this.addEditFieldToColumn( 'df_14', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 1
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_1' } );
		this.addEditFieldToColumn( 'df_1', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 2
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_2' } );
		this.addEditFieldToColumn( 'df_2', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 3
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_3' } );
		this.addEditFieldToColumn( 'df_3', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 4
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_4' } );
		this.addEditFieldToColumn( 'df_4', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 5
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_5' } );
		this.addEditFieldToColumn( 'df_5', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 6
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_6' } );
		this.addEditFieldToColumn( 'df_6', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 7
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_7' } );
		this.addEditFieldToColumn( 'df_7', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 8
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_8' } );
		this.addEditFieldToColumn( 'df_8', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 9
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_9' } );
		this.addEditFieldToColumn( 'df_9', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 10
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_10' } );
		this.addEditFieldToColumn( 'df_10', form_item_input, tab_tax_deductions_column1, '', null, true );

		if ( ( Global.getProductEdition() >= 15 ) ) {
			TTPromise.add( 'CompanyTaxDeduction', 'df_11' );
			Global.loadScript( 'global/widgets/formula_builder/FormulaBuilder.js', function() {
				// Dynamic Field 11
				form_item_input = Global.loadWidgetByName( FormItemType.FORMULA_BUILDER );
				form_item_input.FormulaBuilder( {
					field: 'df_11', width: '100%', onFormulaBtnClick: function() {

						var custom_column_api = TTAPI.APIReportCustomColumn;

						custom_column_api.getOptions( 'formula_functions', {
							onResult: function( fun_result ) {
								var fun_res_data = fun_result.getResult();

								$this.api.getOptions( 'formula_variables', { onResult: onColumnsResult } );

								function onColumnsResult( col_result ) {
									var col_res_data = col_result.getResult();

									var default_args = {};
									default_args.functions = Global.buildRecordArray( fun_res_data );
									default_args.variables = Global.buildRecordArray( col_res_data );
									default_args.formula = $this.current_edit_record.company_value1;
									default_args.current_edit_record = Global.clone( $this.current_edit_record );
									default_args.api = $this.api;

									IndexViewController.openWizard( 'FormulaBuilderWizard', default_args, function( val ) {
										$this.current_edit_record.company_value1 = val;
										$this.edit_view_ui_dic.df_11.setValue( val );
									} );
								}

							}
						} );
					}
				} );

				$this.addEditFieldToColumn( 'df_11', form_item_input, tab_tax_deductions_column1, '', null, true );
				$this.detachElement( 'df_11' );
				form_item_input.parent().width( '45%' );
				TTPromise.resolve( 'CompanyTaxDeduction', 'df_11' );
			} );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
			form_item_input.TTextInput( { field: 'df_11' } );
			this.addEditFieldToColumn( 'df_11', form_item_input, tab_tax_deductions_column1, '', null, true );

			form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
			form_item_input.TText( { field: 'df_100' } );
			this.addEditFieldToColumn( 'Warning', form_item_input, tab_tax_deductions_column1, '', null, true );
		}

		//Dynamic Field 12,13
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_12', width: 30 } );
		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + ' ' + ' </span>' );

		var widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( { field: 'df_13' } );
		widget_combo_box.setSourceData( $this.look_back_unit_array );
		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );
		this.addEditFieldToColumn( 'df_12', [form_item_input, widget_combo_box], tab_tax_deductions_column1, '', widgetContainer, true );

		//Dynamic Field 21 -- Multiple Jobs or Spouse Works
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'df_21', set_empty: false } );
		this.addEditFieldToColumn( 'df_21', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 22
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_22' } );
		this.addEditFieldToColumn( 'df_22', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 23
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_23' } );
		this.addEditFieldToColumn( 'df_23', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 24
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_24' } );
		this.addEditFieldToColumn( 'df_24', form_item_input, tab_tax_deductions_column1, '', null, true );

		//  Dynamic Field 25
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'df_25' } );
		this.addEditFieldToColumn( 'df_25', form_item_input, tab_tax_deductions_column1, '', null, true );

		if ( !this.sub_view_mode ) {
			//Pay Stub Account

			var default_args = {};
			default_args.filter_data = {};
			default_args.filter_data.type_id = [10, 20, 30, 50, 80];

			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIPayStubEntryAccount,
				allow_multiple_selection: false,
				layout_name: 'global_PayStubAccount',
				show_search_inputs: true,
				set_empty: true,
				field: 'pay_stub_entry_account_id'

			} );
			form_item_input.setDefaultArgs( default_args );
			this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab_tax_deductions_column1, '', null, true );
		}

		// Calculation Order
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'calculation_order', width: 30 } );
		this.addEditFieldToColumn( $.i18n._( 'Calculation Order' ), form_item_input, tab_tax_deductions_column1, '', null, true );

		// Include Pay Stub Accounts
		var v_box = $( '<div class=\'v-box\'></div>' );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'include_account_amount_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.account_amount_type_array );

		var form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Pay Stub Account Value' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );

		if ( !this.sub_view_mode ) {
			var form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );

			form_item_input_1.AComboBox( {
				api_class: TTAPI.APIPayStubEntryAccount,
				allow_multiple_selection: true,
				layout_name: 'global_PayStubAccount',
				show_search_inputs: true,
				set_empty: true,
				field: 'include_pay_stub_entry_account'
			} );
			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );
			v_box.append( form_item );
			this.addEditFieldToColumn( $.i18n._( 'Include Pay Stub Accounts' ), [form_item_input, form_item_input_1], tab_tax_deductions_column1, null, v_box, true, true );

		}

		// Exclude Pay Stub Accounts
		v_box = $( '<div class=\'v-box\'></div>' );

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'exclude_account_amount_type_id', set_empty: false } );
		form_item_input.setSourceData( $this.account_amount_type_array );

		form_item = this.putInputToInsideFormItem( form_item_input, $.i18n._( 'Pay Stub Account Value' ) );

		v_box.append( form_item );
		v_box.append( '<div class=\'clear-both-div\'></div>' );
		if ( !this.sub_view_mode ) {
			form_item_input_1 = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input_1.AComboBox( {
				api_class: TTAPI.APIPayStubEntryAccount,
				allow_multiple_selection: true,
				layout_name: 'global_PayStubAccount',
				show_search_inputs: true,
				set_empty: true,
				field: 'exclude_pay_stub_entry_account'
			} );

			form_item = this.putInputToInsideFormItem( form_item_input_1, $.i18n._( 'Selection' ) );
			v_box.append( form_item );
			this.addEditFieldToColumn( $.i18n._( 'Exclude Pay Stub Accounts' ), [form_item_input, form_item_input_1], tab_tax_deductions_column1, null, v_box, true, true );
		}

		if ( !this.sub_view_mode ) {
			// employees
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIUser,
				allow_multiple_selection: true,
				layout_name: 'global_user',
				show_search_inputs: true,
				set_empty: true,
				field: 'user'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Employees' ), form_item_input, tab_tax_deductions_column1, '' );
		}
		// Tab1  start

		var tab_eligibility = this.edit_view_tab.find( '#tab_eligibility' );

		var tab_eligibility_column1 = tab_eligibility.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_eligibility_column1 );

		// Apply Frequency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_id', set_empty: false } );
		form_item_input.setSourceData( $this.apply_frequency_array );
		this.addEditFieldToColumn( $.i18n._( 'Apply Frequency' ), form_item_input, tab_eligibility_column1, '' );

		// Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_month', set_empty: false } );
		form_item_input.setSourceData( $this.month_of_year_array );
		this.addEditFieldToColumn( $.i18n._( 'Month' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Day of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_day_of_month', set_empty: false } );
		form_item_input.setSourceData( $this.day_of_month_array );
		this.addEditFieldToColumn( $.i18n._( 'Day of Month' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Semi-Monthly: Primary Day of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'apply_frequency_day_of_month1', set_empty: false } );
		form_item_input.setSourceData( $this.day_of_month_array );
		this.addEditFieldToColumn( $.i18n._( 'Primary Day of Month' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Semi-Monthly: Secondary Day of Month
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'apply_frequency_day_of_month2', set_empty: false } );
		form_item_input.setSourceData( $this.day_of_month_array );
		this.addEditFieldToColumn( $.i18n._( 'Secondary Day of Month' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Month of Quarter
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( { field: 'apply_frequency_quarter_month', set_empty: false } );
		form_item_input.setSourceData( $this.month_of_quarter_array );
		this.addEditFieldToColumn( $.i18n._( 'Month of Quarter' ), form_item_input, tab_eligibility_column1, '', null, true );

		// Payroll Run Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {
			field: 'apply_payroll_run_type_id',
			set_empty: true,
			customFirstItemLabel: Global.any_item
		} );
		form_item_input.setSourceData( $this.apply_payroll_run_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Payroll Run Type' ), form_item_input, tab_eligibility_column1, '' );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'start_date' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( '(Leave blank for no start date)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// End Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'end_date' } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( '(Leave blank for no end date)' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// Filter Date Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );

		form_item_input.TComboBox( {
			field: 'filter_date_type_id',
			set_empty: false,
			customFirstItemLabel: Global.any_item
		} );
		form_item_input.setSourceData( $this.filter_date_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Start/End Dates Based On' ), form_item_input, tab_eligibility_column1, '' );

		// Minimum Length Of Service
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_length_of_service', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + ' ' + ' </span>' );

		widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( { field: 'minimum_length_of_service_unit_id' } );
		widget_combo_box.setSourceData( $this.length_of_service_unit_array );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );

		this.addEditFieldToColumn( $.i18n._( 'Minimum Length Of Service' ), [form_item_input, widget_combo_box], tab_eligibility_column1, '', widgetContainer );

		// Maximum Length Of Service

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_length_of_service', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'> ' + ' ' + ' </span>' );

		widget_combo_box = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		widget_combo_box.TComboBox( { field: 'maximum_length_of_service_unit_id' } );
		widget_combo_box.setSourceData( $this.length_of_service_unit_array );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		widgetContainer.append( widget_combo_box );

		this.addEditFieldToColumn( $.i18n._( 'Maximum Length Of Service' ), [form_item_input, widget_combo_box], tab_eligibility_column1, '', widgetContainer );
		if ( !this.sub_view_mode ) {
			//Length of Service contributing pay codes.
			form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
			form_item_input.AComboBox( {
				api_class: TTAPI.APIContributingPayCodePolicy,
				allow_multiple_selection: false,
				layout_name: 'global_contributing_pay_code_policy',
				show_search_inputs: true,
				set_empty: true,
				set_default: true,
				field: 'length_of_service_contributing_pay_code_policy_id'
			} );
			this.addEditFieldToColumn( $.i18n._( 'Length Of Service Hours Based On' ), form_item_input, tab_eligibility_column1, '', null, true );
		}
		// Minimum Employee Age
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'minimum_user_age', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'years' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Minimum Employee Age' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		// Maximum Employee Age
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'maximum_user_age', width: 30 } );

		widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		label = $( '<span class=\'widget-right-label\'>' + $.i18n._( 'years' ) + '</span>' );

		widgetContainer.append( form_item_input );
		widgetContainer.append( label );
		this.addEditFieldToColumn( $.i18n._( 'Maximum Employee Age' ), form_item_input, tab_eligibility_column1, '', widgetContainer );

		//Tab 5

		var tab5 = this.edit_view_tab.find( '#tab5' );

		var tab5_column1 = tab5.find( '.first-column' );

		this.edit_view_tabs[5] = [];

		this.edit_view_tabs[5].push( tab5_column1 );

		//Permissions

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_DROPDOWN );

		var display_columns = ALayoutCache.getDefaultColumn( 'global_deduction' ); //Get Default columns base on different layout name
		display_columns = Global.convertColumnsTojGridFormat( display_columns, 'global_deduction' ); //Convert to jQgrid format

		form_item_input.ADropDown( {
			field: 'company_tax_deduction_ids',
			display_show_all: false,
			id: 'company_tax_deduction_ids',
			key: 'id',
			display_close_btn: false,
			allow_drag_to_order: false,
			display_column_settings: false
		} );
		form_item_input.addClass( 'splayed-adropdown' );
		this.addEditFieldToColumn( $.i18n._( 'Taxes / Deductions' ), form_item_input, tab5_column1, '', null, false, true );

		form_item_input.setColumns( display_columns );
//		form_item_input.setUnselectedGridData( [] );
		TTPromise.resolve( 'CompanyTaxDeduction', 'buildEditViewUI' );
	}

	setEditViewTabHeight() {
		super.setEditViewTabHeight();

		var tax_grid = this.edit_view_ui_dic.company_tax_deduction_ids;

		tax_grid.setHeight( ( this.edit_view_tab.find( '.context-border' ).height() - $( this.$el )[0].getBoundingClientRect().top ) - 20 );
	}

	putInputToInsideFormItem( form_item_input, label ) {
		var form_item = $( Global.loadWidgetByName( WidgetNamesDic.EDIT_VIEW_SUB_FORM_ITEM ) );
		var form_item_label_div = form_item.find( '.edit-view-form-item-label-div' );

		form_item_label_div.attr( 'class', 'edit-view-form-item-sub-label-div' );

		var form_item_label = form_item.find( '.edit-view-form-item-label' );
		var form_item_input_div = form_item.find( '.edit-view-form-item-input-div' );
		form_item.addClass( 'remove-margin' );

		form_item_label.text( $.i18n._( label ) );
		form_item_input_div.append( form_item_input );

		return form_item;
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
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 1,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Legal Entity' ),
				in_column: 1,
				field: 'legal_entity_id',
				layout_name: 'global_legal_entity',
				api_class: TTAPI.APILegalEntity,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Pay Stub Account' ),
				in_column: 1,
				field: 'pay_stub_entry_name_id',
				layout_name: 'global_PayStubAccount',
				api_class: TTAPI.APIPayStubEntryAccount,
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Remittance Agency' ),
				in_column: 2,
				field: 'payroll_remittance_agency_id',
				layout_name: 'global_payroll_remittance_agency',
				api_class: TTAPI.APIPayrollRemittanceAgency,
				multiple: true,
				basic_search: true,
				script_name: 'PayrollRemittanceAgencyView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Calculation' ),
				in_column: 2,
				field: 'calculation_id',
				multiple: true,
				basic_search: true,
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
				script_name: 'EmployeeView',
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
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	searchDone() {
		TTPromise.resolve( 'TaxView', 'init' );
		super.searchDone();
	}

	getCompanyTaxDeductionEmployeeSettingTabHtml() {
		return `<div id="tab_employee_setting" class="edit-view-tab-outside">
					<div class="edit-view-tab" id="tab_employee_setting_content_div">
						<div class="inside-editor-div full-width-column">
							<div class="grid-div employee-setting-grid-div">
								<table id="employee_setting_grid"></table>
							</div>
						</div>
						<div class="save-and-continue-div">
							<span class="message"></span>
							<div class="save-and-continue-button-div">
								<button class="tt-button p-button p-component" type="button">
									<span class="icon"></span>
									<span class="p-button-label"></span>
								</button>
							</div>
						</div>
					</div>
				</div>`;
	}

}

CompanyTaxDeductionViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'CompanyTaxDeduction', 'SubCompanyTaxDeductionView.html', function( result ) {

		var args = {};
		var template = _.template( result );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template( args ) );

			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_company_tax_deduction_view_controller );
			}

		}

	} );

};
