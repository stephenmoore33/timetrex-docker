import Decimal from 'decimal.js';

export class PayStubAmendmentViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#pay_stub_amendment_view_container',

			user_status_array: null,
			filtered_status_array: null,
			type_array: null,
			is_mass_adding: false,

			user_api: null,
			user_group_api: null,
			user_wage_api: null,
			job_api: null,
			job_item_api: null,
			department_api: null,
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		this.edit_view_tpl = 'PayStubAmendmentEditView.html';
		this.permission_id = 'pay_stub_amendment';
		this.viewId = 'PayStubAmendment';
		this.script_name = 'PayStubAmendmentView';
		this.table_name_key = 'pay_stub_amendment';
		this.context_menu_name = $.i18n._( 'Pay Stub Amendment' );
		this.navigation_label = $.i18n._( 'Pay Stub Amendment' );
		this.api = TTAPI.APIPayStubAmendment;
		this.user_api = TTAPI.APIUser;
		this.user_group_api = TTAPI.APIUserGroup;
		this.currency_api = TTAPI.APICurrency;
		this.user_wage_api = TTAPI.APIUserWage;

		if ( Global.getProductEdition() >= 20 ) {
			this.job_api = TTAPI.APIJob;
			this.job_item_api = TTAPI.APIJobItem;
			this.department_api = TTAPI.APIDepartment;
		}

		this.initPermission();
		this.render();
		this.buildContextMenu();

		this.initData();
	}

	initPermission() {
		super.initPermission();

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
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'type', api: this.api },
			{ option_name: 'status', field_name: 'user_status_id', api: this.user_api },
			{ option_name: 'filtered_status', field_name: 'status_id', api: this.api },
		];

		this.initDropDownOptions( options );

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

		this.api.getOptions( 'status', false, false, {
			onResult: function( res ) {
				var status_array = Global.buildRecordArray( res.getResult() );

				if ( !$this.sub_view_mode && $this.basic_search_field_ui_dic['status_id'] ) {
					$this.basic_search_field_ui_dic['status_id'].setSourceData( status_array );
					if ( $this.adv_search_field_ui_dic['status_id'] ) {
						$this.adv_search_field_ui_dic['status_id'].setSourceData( status_array );
					}
				}

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
			p_id = this.permission_id;
		}

		if ( PermissionManager.validate( 'job_item', 'enabled' ) &&
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

	getUserHourlyRate( user_id ) {
		var $this = this;

		if ( !user_id ) {
			user_id = this.current_edit_record['user_id'];
		}

		if ( TTUUID.isUUID( user_id ) == false || ( $.isArray( user_id ) && user_id.length != 1 ) ) {
			user_id = TTUUID.zero_id;
		}

		//Last Wage record only.
		this.user_wage_api.getUserWage( {
			filter_data: {
				user_id: user_id,
				wage_group_id: TTUUID.zero_id
			}
		}, false, true, {
			onResult: function( result ) {
				var rate = '0.00';

				var result_data = result.getResult();
				if ( result_data && result_data.length > 0 ) {
					result_data = result_data[0];

					rate = result_data.hourly_rate;
				}

				$this.edit_view_ui_dic['rate'].setValue( rate );
				$this.current_edit_record['rate'] = rate;
				$this.calcAmount();
			}
		} );
	}

	getFilterColumnsFromDisplayColumns() {

		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.user_id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.effective_date = true;

		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}
		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	}

	onReportPrintClick( key ) {
		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var ids = [];

		var user_ids = [];

		var base_date;

		var pay_period_ids = [];

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			pay_period_ids.push( $this.current_edit_record.pay_period_id );
			base_date = $this.current_edit_record.start_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				pay_period_ids.push( grid_selected_row.pay_period_id );
				base_date = grid_selected_row.start_date;
			} );
		}

		var args = { filter_data: { id: ids } };
		var post_data = { 0: args, 1: true, 2: key };

		this.doFormIFrameCall( post_data );
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
					permission_result: false, // to hide it in legacy context menu and avoid errors in legacy parsers.
					sort_order: 9050,
				},
				{
					label: $.i18n._( 'TimeSheet' ),
					id: 'timesheet',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					sort_order: 9050,
				},
				{
					label: $.i18n._( 'Pay Stubs' ),
					id: 'pay_stub',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					sort_order: 9050,
				},
				{
					label: $.i18n._( 'Edit Employee' ),
					id: 'edit_employee',
					menu_align: 'right',
					action_group: 'jump_to',
					group: 'navigation',
					sort_order: 9050,
				},
				{
					label: $.i18n._( 'Import' ),
					id: 'import_icon',
					menu_align: 'right',
					action_group: 'import_export',
					group: 'other',
					vue_icon: 'tticon tticon-file_download_black_24dp',
					permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVPayStubAmendment' ),
					sort_order: 9010
				}
			]
		};

		return context_menu_model;
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'timesheet':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'punch' );
				break;
			case 'pay_stub':
				this.setDefaultMenuViewIcon( context_btn, grid_selected_length, 'pay_stub' );
				break;
			case 'edit_employee':
				this.setDefaultMenuEditIcon( context_btn, grid_selected_length, 'user' );
				break;
			case 'print_checks':
				this.setDefaultMenuPrintChecksIcon( context_btn, grid_selected_length );
				break;
			case 'direct_deposit':
				this.setDefaultMenuDirectDepositIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setDefaultMenuImportIcon( context_btn, grid_selected_length, pId ) {
		if ( PermissionManager.checkTopLevelPermission( 'ImportCSVPayStubAmendment' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	//Remove the copy button as it can never work due to API unique constraints.
	setDefaultMenuCopyIcon( context_btn, grid_selected_length, pId ) {
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
	}

	setEditMenuCopyIcon( context_btn, grid_selected_length, pId ) {
		ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
	}

	/* jshint ignore:end */
	setDefaultMenuViewIcon( context_btn, grid_selected_length, pId ) {

		if ( pId === 'punch' || pId === 'schedule' || pId === 'pay_stub' ) {
			super.setDefaultMenuViewIcon( context_btn, grid_selected_length, pId );
		} else {

			if ( !this.viewPermissionValidate( pId ) || this.edit_only_mode ) {
				ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
			}

			if ( grid_selected_length > 0 && this.viewOwnerOrChildPermissionValidate() ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
			} else {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
			}

		}
	}

	setDefaultMenuPrintChecksIcon( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setDefaultMenuDirectDepositIcon( context_btn, grid_selected_length, pId ) {

		if ( grid_selected_length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'timesheet':
				this.setEditMenuViewIcon( context_btn, 'punch' );
				break;
			case 'pay_stub':
				this.setEditMenuViewIcon( context_btn, 'pay_stub' );
				break;
			case 'edit_employee':
				this.setEditMenuViewIcon( context_btn, 'user' );
				break;
			case 'print_checks':
			case 'direct_deposit':
				this.setEditMenuViewIcon( context_btn );
				break;
		}
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		var c_value = target.getValue();

		this.current_edit_record[key] = c_value;

		switch ( key ) {
			case 'user_id':
				if ( $.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 1 ) {
					this.is_mass_adding = true;
				} else {
					this.is_mass_adding = false;
				}
				doNotValidate = true; //Don't validate since setCurrency() triggers calcAmount(), which changes the amount field asynchronously, only then should we validate.
				this.setCurrency();
				this.setEditMenu();
				break;
			case 'type_id':
				this.onTypeChange();
				break;
			case 'rate':
			case 'units':
			case 'amount':
				if ( this.is_mass_editing ) {
					if ( target.isChecked() ) {
						this.edit_view_ui_dic['rate'].setCheckBox( true );
						this.edit_view_ui_dic['units'].setCheckBox( true );
						this.edit_view_ui_dic['amount'].setCheckBox( true );
					} else {
						this.edit_view_ui_dic['rate'].setCheckBox( false );
						this.edit_view_ui_dic['units'].setCheckBox( false );
						this.edit_view_ui_dic['amount'].setCheckBox( false );
					}
				}
				this.current_edit_record['amount'] = this.edit_view_ui_dic['amount'].getValue();
				break;
			case 'job_quick_search':
			case 'job_item_quick_search':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.onJobQuickSearch( key, c_value );
				}
				break;
			case 'job_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.setJobItemValueWhenJobChanged( target.getValue( true ), 'job_item_id', {
						status_id: 10,
						job_id: this.current_edit_record.job_id
					} );
					this.edit_view_ui_dic['job_quick_search'].setCheckBox( true );
				}
				break;
			case 'job_item_id':
				if ( ( Global.getProductEdition() >= 20 ) ) {
					this.edit_view_ui_dic['job_item_quick_search'].setValue( target.getValue( true ) ? ( target.getValue( true ).manual_id ? target.getValue( true ).manual_id : '' ) : '' );
					this.edit_view_ui_dic['job_item_quick_search'].setCheckBox( true );
					this.setPunchTagValuesWhenCriteriaChanged( this.getPunchTagFilterData(), 'punch_tag_id' );
				}
				break;
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onTypeChange() {
		if ( this.current_edit_record.type_id == 10 ) {
			this.detachElement( 'percent_amount' );
			this.detachElement( 'percent_amount_entry_name_id' );
			this.attachElement( 'rate' );
			this.attachElement( 'units' );
			this.attachElement( 'amount' );

		} else if ( this.current_edit_record.type_id == 20 ) {
			this.attachElement( 'percent_amount' );
			this.attachElement( 'percent_amount_entry_name_id' );
			this.detachElement( 'rate' );
			this.detachElement( 'units' );
			this.detachElement( 'amount' );
		}

		this.editFieldResize();
	}

	calcAmount() {
		var widget_rate = this.edit_view_ui_dic['rate'];
		var widget_units = this.edit_view_ui_dic['units'];
		var widget_amount = this.edit_view_ui_dic['amount'];

		if ( widget_rate && widget_rate.getValue().length > 0 && widget_units && widget_units.getValue().length > 0 ) {
			//widget_amount.setValue( ( parseFloat( widget_rate.getValue() ) * parseFloat( widget_units.getValue() ) ).toFixed( 2 ) ); //This fails on 17.07 * 9.50 as it rounds to 162.16 rather than 162.17
			//calc_amount = ( parseFloat( widget_rate.getValue() ) * parseFloat( widget_units.getValue() ) ); //This fails on 16.5 * 130.23
			var calc_amount = new Decimal( parseFloat( widget_rate.getValue() ) ).mul( parseFloat( widget_units.getValue() ) ).toFixed( 4 ); //Need to use Decimal() class for proper money math operations
			Debug.Text( 'Calculate Amount before rounding: ' + calc_amount, 'PayStubAmendmentViewController.js', 'PayStubAmendmentViewController', 'onFormItemKeyUp', 10 );

			var round_decimal_places;
			if ( this.currency_array && this.currency_array.round_decimal_places ) {
				round_decimal_places = this.currency_array.round_decimal_places;
			} else {
				round_decimal_places = 2;
			}
			widget_amount.setValue( Global.MoneyRound( calc_amount, round_decimal_places ) );
		} else {
			if ( widget_amount && widget_amount.getValue() == '' ) {
				widget_amount.setValue( '0.00' );
			}
		}

		if ( !this.is_mass_editing && this.edit_view_ui_dic['amount'] && this.current_edit_record ) { //Make sure this is only done when editing a single record otherwise Mass Edit will default to changing the amount to 0.00.
			this.current_edit_record['amount'] = this.edit_view_ui_dic['amount'].getValue(); //Update current record Amount, otherwise edit/save (without any changes) won't save the rounded value.
		}
	}

	onRateOrUnitChange() {
		var widget_rate = this.edit_view_ui_dic['rate'];
		var widget_units = this.edit_view_ui_dic['units'];
		var widget_amount = this.edit_view_ui_dic['amount'];

		if ( widget_rate.getValue().length > 0 || widget_units.getValue().length > 0 ) {
			widget_amount.setReadOnly( true );
		} else {
			widget_amount.setReadOnly( false );
		}
	}

	onFormItemKeyUp( target ) {
		this.onRateOrUnitChange();
		this.calcAmount();
	}

	onFormItemKeyDown( target ) {
		this.onRateOrUnitChange();
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'import_icon':
				this.onImportClick();
				break;
			case 'timesheet':
			case 'pay_stub':
			case 'edit_employee':
				this.onNavigationClick( id );
				break;
		}
	}

	onImportClick() {

		var $this = this;
		IndexViewController.openWizard( 'ImportCSVWizard', 'PayStubAmendment', function() {
			$this.search();
		} );
	}

	/* jshint ignore:start */
	onNavigationClick( iconName ) {

		var $this = this;

		var grid_selected_id_array;

		var filter = {};

		var user_ids = [];

		var ids = [];

		var base_date;

		if ( $this.edit_view && $this.current_edit_record.id ) {
			ids.push( $this.current_edit_record.id );
			user_ids.push( $this.current_edit_record.user_id );
			base_date = $this.current_edit_record.effective_date;
		} else {
			grid_selected_id_array = this.getGridSelectIdArray();
			$.each( grid_selected_id_array, function( index, value ) {
				var grid_selected_row = $this.getRecordFromGridById( value );
				ids.push( grid_selected_row.id );
				user_ids.push( grid_selected_row.user_id );
				base_date = grid_selected_row.effective_date;
			} );
		}

		var args = { filter_data: { id: ids } };

		switch ( iconName ) {
			case 'timesheet':
				if ( user_ids.length > 0 ) {
					filter.user_id = user_ids[0];
					filter.base_date = base_date;
					Global.addViewTab( $this.viewId, $.i18n._( 'Pay Stub Amendments' ), window.location.href );
					IndexViewController.goToView( 'TimeSheet', filter );
				}
				break;
			case 'pay_stub':
				if ( user_ids.length > 0 ) {
					filter.filter_data = {};
					filter.filter_data.user_id = user_ids[0];
					Global.addViewTab( $this.viewId, $.i18n._( 'Pay Stub Amendments' ), window.location.href );
					IndexViewController.goToView( 'PayStub', filter );
				}
				break;
			case 'edit_employee':
				if ( user_ids.length > 0 ) {
					IndexViewController.openEditView( this, 'Employee', user_ids[0] );
				}
				break;
		}
	}

	/* jshint ignore:end */
	onReportMenuClick( id ) {
		this.onReportPrintClick( id );
	}

	//not currently called. are we reimplementing the eft code commented out above in this class?
	doFormIFrameCall( postData ) {
		Global.APIFileDownload( this.api.className, 'get' + this.api.key_name, postData );
	}

	setCurrency() {
		var $this = this;
		if ( Global.isSet( this.current_edit_record.user_id ) ) {
			var filter = {};
			filter.filter_data = { user_id: this.current_edit_record.user_id };

			this.currency_api.getCurrency( filter, false, false, {
				onResult: function( res ) {
					res = res.getResult();
					if ( Global.isArray( res ) ) {
						$this.currency_array = res[0];
						$this.calcAmount();
					} else {
						$this.currency_array = null;
					}
				}
			} );
		}
	}

	setCurrentEditRecordData() {
		// When mass editing, these fields may not be the common data, so their value will be undefined, so this will cause their change event cannot work properly.
		this.setDefaultData( {
			'type_id': 10
		} );

		super.setCurrentEditRecordData();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.setCurrency();
		this.onTypeChange();
		this.onRateOrUnitChange();
	}

	validate() {

		var $this = this;

		var record = {};

		var records_data = null;

		if ( this.is_mass_editing ) {
			for ( var key in this.edit_view_ui_dic ) {
				//#2536 - Never send status_id to the API.
				if ( key != 'status_id' ) {
					var widget = this.edit_view_ui_dic[key];

					if ( Global.isSet( widget.isChecked ) ) {
						if ( widget.isChecked() && widget.getEnabled() ) {
							record[key] = widget.getValue();
						}

					}
				}
			}

		} else {
			record = this.uniformVariable( this.current_edit_record );
		}

		var record = this.buildMassAddRecord( record );

		this.api['validate' + this.api.key_name]( record, {
			onResult: function( result ) {
				$this.validateResult( result );

			}
		} );
	}

	removeEditView() {
		this.is_mass_adding = false;
		super.removeEditView();
	}

	buildMassAddRecord( record ) {
		if ( $.isArray( record.user_id ) ) {
			var records_data = [];
			var length = record.user_id.length;
			if ( length > 0 ) {
				for ( var i = 0; i < length; i++ ) {
					var record_data = Global.clone( record );
					record_data.user_id = record.user_id[i];
					records_data.push( record_data );
				}
				this.setEditMenu();

				return this.uniformVariable( records_data );

			} else {
				record.user_id = record.user_id.toString();
			}

		}

		return this.uniformVariable( record );
	}

	onSaveAndContinue( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_changed = false;
		this.is_add = false;
		LocalCacheData.current_doing_context_action = 'save_and_continue';

		var record = this.buildMassAddRecord( this.current_edit_record );
		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				$this.onSaveAndContinueResult( result );

			}
		} );
	}

	doSaveAPICall( record, ignoreWarning ) {
		// #2644: We have to handle the record as though its a mass_add, as the awesomebox will always return an array of user_id's. Cannot force is_mass_adding, as this affects the save&continue button disabling.
		record = this.buildMassAddRecord( record );
		super.doSaveAPICall( record, ignoreWarning );
	}

	onSaveAndCopy( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		this.is_changed = false;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save_and_copy';
		var records_data = null;
		this.clearNavigationData();

		var record = this.buildMassAddRecord( record );

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result && result.isValid() ) {
					var result_data = result.getResult();
					if ( result_data === true ) {
						$this.refresh_id = $this.current_edit_record.id;

					} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
						$this.refresh_id = result_data;
					}
					$this.search( false );
					$this.onCopyAsNewClick();
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	}

	onSaveAndNewClick( ignoreWarning ) {
		var $this = this;
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		this.is_add = true;
		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'new';

		var records_data = null;

		var record = this.buildMassAddRecord( record );

		this.api['set' + this.api.key_name]( record, false, ignoreWarning, {
			onResult: function( result ) {
				if ( result && result.isValid() ) {
					var result_data = result.getResult();
					if ( result_data === true ) {
						$this.refresh_id = $this.current_edit_record.id;

					} else if ( TTUUID.isUUID( result_data ) && result_data != TTUUID.zero_id && result_data != TTUUID.not_exist_id ) {
						$this.refresh_id = result_data;
					}
					$this.search( false );
					$this.onAddClick( true );
				} else {
					$this.setErrorTips( result );
					$this.setErrorMenu();
				}

			}
		} );
	}

	setEditMenuSaveAndContinueIcon( context_btn, pId ) {
		this.saveAndContinueValidate( context_btn, pId );

		if ( this.is_mass_adding || this.is_mass_editing || this.is_viewing || ( this.current_edit_record && Global.isArray( this.current_edit_record.user_id ) && this.current_edit_record.user_id.length > 1 ) ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	checkTabPermissions( tab ) {
		var retval = false;

		switch ( tab ) {
			case 'tab_cost_centers':
				if ( this.branchUIValidate() || this.departmentUIValidate() || this.jobUIValidate() || this.jobItemUIValidate() ) { //Only display tab if fields exists to be edited.
					retval = true;
				}
				break;
			default:
				retval = super.checkTabPermissions( tab );
				break;
		}

		return retval;
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;
		var allow_multiple_selection = false;

		var tab_model = {
			'tab_pay_stub_amendment': { 'label': $.i18n._( 'Pay Stub Amendment' ) },
			'tab_cost_centers': { 'label': $.i18n._( 'Cost Centers' ) },
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIPayStubAmendment,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_pay_stub_amendment',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start

		var tab_pay_stub_amendment = this.edit_view_tab.find( '#tab_pay_stub_amendment' );

		var tab_pay_stub_amendment_column1 = tab_pay_stub_amendment.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_pay_stub_amendment_column1 );

		if ( this.is_add ) {
			allow_multiple_selection = true;
		}

		//Employee

		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: allow_multiple_selection,
			layout_name: 'global_user',
			show_search_inputs: true,
			set_empty: true,
			field: 'user_id'
		} );

		var default_args = {};
		default_args.permission_section = 'pay_stub_amendment';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee(s)' ), form_item_input, tab_pay_stub_amendment_column1, '' );

		var args = {};
		var filter_data = {};
		filter_data.type_id = [10, 20, 30, 50, 60, 65, 80];
		args.filter_data = filter_data;

		// Pay Stub Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: false,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'pay_stub_entry_name_id',
			validation_field: 'pay_stub_entry_name'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Account' ), form_item_input, tab_pay_stub_amendment_column1 );

		// Amount Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id', set_empty: false } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Amount Type' ), form_item_input, tab_pay_stub_amendment_column1 );

		// Fixed

		// Units
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'units', width: 114, hasKeyEvent: true } );
		this.addEditFieldToColumn( $.i18n._( 'Units' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true, null, null, true );

		// Rate
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'rate', width: 114, hasKeyEvent: true } );

		var widgetContainer = $( '<div class=\'widget-h-box\'></div>' );
		widgetContainer.append( form_item_input );

		if ( !this.is_viewing ) {
			var get_hourly_rate_btn = $( '<input class=\'t-button\' style=\'margin-left: 5px; height: 25px;\' type=\'button\' value=\'' + $.i18n._( 'Get Hourly Rate' ) + '\'></input>' );
			get_hourly_rate_btn.click( function() {
				$this.getUserHourlyRate();
			} );
			widgetContainer.append( get_hourly_rate_btn );
		}

		this.addEditFieldToColumn( $.i18n._( 'Rate' ), form_item_input, tab_pay_stub_amendment_column1, '', widgetContainer, true, null, null, true );

		// Amount
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'amount', width: 114 } );
		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		// Percent

		//Percent
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'percent_amount', width: 79 } );
		this.addEditFieldToColumn( $.i18n._( 'Percent' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		args = {};
		filter_data = {};
		filter_data.type_id = [10, 20, 30, 40, 50, 60, 65];
		args.filter_data = filter_data;

		// Percent of
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIPayStubEntryAccount,
			allow_multiple_selection: false,
			layout_name: 'global_PayStubAccount',
			show_search_inputs: true,
			set_empty: true,
			field: 'percent_amount_entry_name_id'
		} );

		form_item_input.setDefaultArgs( args );
		this.addEditFieldToColumn( $.i18n._( 'Percent of' ), form_item_input, tab_pay_stub_amendment_column1, '', null, true );

		// Pay Stub Note (Public)
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );

		form_item_input.TTextInput( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Pay Stub Note (Public)' ), form_item_input, tab_pay_stub_amendment_column1 );

		form_item_input.parent().width( '45%' );
		// Description (Private)

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );

		form_item_input.TTextArea( { field: 'private_description' } );
		this.addEditFieldToColumn( $.i18n._( 'Description (Private)' ), form_item_input, tab_pay_stub_amendment_column1, '', null, null, true );

		// Effective Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );

		form_item_input.TDatePicker( { field: 'effective_date' } );
		this.addEditFieldToColumn( $.i18n._( 'Effective Date' ), form_item_input, tab_pay_stub_amendment_column1 );


		//Tab 1 start
		var tab_cost_center = this.edit_view_tab.find( '#tab_cost_centers' );
		var tab_cost_center_column1 = tab_cost_center.find( '.first-column' );
		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_cost_center_column1 );

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
		this.addEditFieldToColumn( $.i18n._( 'Branch' ), form_item_input, tab_cost_center_column1, '', null, true );

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
		this.addEditFieldToColumn( $.i18n._( 'Department' ), form_item_input, tab_cost_center_column1, '', null, true );

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
			this.addEditFieldToColumn( $.i18n._( 'Job' ), [form_item_input, job_coder], tab_cost_center_column1, '', widgetContainer, true );

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
			this.addEditFieldToColumn( $.i18n._( 'Task' ), [form_item_input, job_item_coder], tab_cost_center_column1, '', widgetContainer, true );

			if ( !this.show_job_item_ui ) {
				this.detachElement( 'job_item_id' );
			}
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

	buildSearchFields() {

		super.buildSearchFields();

		var default_args = {};
		default_args.permission_section = 'pay_stub_amendment';

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
				default_args: default_args,
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
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
				label: $.i18n._( 'Employee Status' ),
				in_column: 2,
				field: 'user_status_id',
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
				label: $.i18n._( 'Branch' ),
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
				label: $.i18n._( 'Department' ),
				field: 'department_id',
				in_column: 2,
				layout_name: 'global_department',
				api_class: TTAPI.APIDepartment,
				multiple: true,
				basic_search: true,
				adv_search: true,
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

	uniformVariable( data ) {
		if ( data.status_id ) {
			delete data.status_id;
		}
		return super.uniformVariable( data );
	}

	copyAsNewResetIds( data ) {
		data = this.uniformVariable( data );
		data.id = null;
		data.effective_date = ( new Date ).format( Global.getLoginUserDateFormat() );
		return data;
	}

}
