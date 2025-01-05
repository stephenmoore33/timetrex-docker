export class PayrollRemittanceAgencyViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#payroll_remittance_agency_view_container',



			status_array: null,
			type_array: null,
			agency_array: null,
			country_array: null,
			province_array: null,
			district_array: null,

			remittance_source_account_array: null,
			sub_event_view_controller: null,

			e_province_array: null,
			company_api: null,
			remittance_source_account_api: null,
			always_week_day_array: null,

			date_api: null
		} );

		super( options );
	}

	init() {
		this.edit_view_tpl = 'PayrollRemittanceAgencyEditView.html';
		this.permission_id = 'payroll_remittance_agency';
		this.viewId = 'PayrollRemittanceAgency';
		this.script_name = 'PayrollRemittanceAgencyView';
		this.table_name_key = 'payroll_remittance_agency';
		this.context_menu_name = $.i18n._( 'Remittance Agencies' );
		this.navigation_label = $.i18n._( 'Remittance Agency' );
		this.api = TTAPI.APIPayrollRemittanceAgency;
		this.company_api = TTAPI.APICompany;
		this.date_api = TTAPI.APITTDate;
		this.remittance_source_account_api = TTAPI.APIRemittanceSourceAccount;

		this.render();
		this.buildContextMenu();

		this.initData();
	}

	//Don't initOptions if edit_only_mode. Do it in sub views
	initOptions( callback ) {
		var $this = this;

		var options = [
			{ option_name: 'status', api: this.api },
			{ option_name: 'type', api: this.api },
			{ option_name: 'country', field_name: 'country', api: this.company_api },
			{ option_name: 'always_week_day', field_name: 'always_week_day', api: this.api },
		];

		this.initDropDownOptions( options, callback );
	}

	openEditView( record_id ) {

		var $this = this;
		if ( this.edit_only_mode ) {

			this.initOptions( function( result ) {
				if ( !$this.edit_view ) {
					$this.initEditViewUI( $this.viewId, $this.edit_view_tpl );
				}

				var filter = {};
				filter.filter_data = { id: record_id };

				$this.api.getPayrollRemittanceAgency( filter, {
					onResult: function( result ) {
						result = result.getResult()[0];
						//Error: Uncaught TypeError: Cannot read property 'user_id' of null in interface/html5/#!m=TimeSheet&date=20150915&user_id=42175&show_wage=0 line 79
						if ( !result ) {
							TAlertManager.showAlert( $.i18n._( 'Invalid agency id' ) );
							$this.onCancelClick();
						} else {
							// Waiting for the TTAPI.API returns data to set the current edit record.
							$this.current_edit_record = result;

							$this.initEditView();
						}

					}
				} );
			} );
		} else {
			super.openEditView();
		}
	}

	getFilterColumnsFromDisplayColumns() {
		var column_filter = {};
		column_filter.is_owner = true;
		column_filter.id = true;
		column_filter.is_child = true;
		column_filter.in_use = true;
		column_filter.first_name = true;
		column_filter.last_name = true;
		column_filter.type_id = true;
		column_filter.country = true;
		column_filter.province = true;
		column_filter.district = true;

		// Error: Unable to get property 'getGridParam' of undefined or null reference
		var display_columns = [];
		if ( this.grid ) {
			display_columns = this.grid.getGridParam( 'colModel' );
		}
		//Fixed possible exception -- Error: Unable to get property 'length' of undefined or null reference in /interface/html5/views/BaseViewController.js?v=7.4.3-20140924-090129 line 5031
		if ( display_columns ) {
			var len = display_columns.length;

			for ( var i = 0; i < len; i++ ) {
				var column_info = display_columns[i];
				column_filter[column_info.name] = true;
			}
		}

		return column_filter;
	}

	onSetSearchFilterFinished() {
		var combo;
		var select_value;
		if ( this.search_panel.getSelectTabIndex() === 0 ) {
			combo = this.basic_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setProvince( select_value );
		} else if ( this.search_panel.getSelectTabIndex() === 1 ) {
			combo = this.adv_search_field_ui_dic['country'];
			select_value = combo.getValue();
			this.setProvince( select_value );
		}
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['export_excel'],
			include: []
		};

		return context_menu_model;
	}

	onBuildAdvUIFinished() {

		this.adv_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.adv_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.adv_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	onBuildBasicUIFinished() {
		this.basic_search_field_ui_dic['country'].change( $.proxy( function() {
			var combo = this.basic_search_field_ui_dic['country'];
			var selectVal = combo.getValue();

			this.setProvince( selectVal );

			this.basic_search_field_ui_dic['province'].setValue( null );

		}, this ) );
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		switch ( key ) {
			case 'country':
			case 'province':
				if ( c_value.toString() === this.current_edit_record[key].toString() ) {
					return;
				}
				break;
			case 'type_id':
				if ( c_value.toString() === this.current_edit_record[key].toString() ) {
					return;
				}
				this.onTypeChange( c_value );
				break;
			case 'agency_id':
				this.onAgencyIdChange();
				break;
		}

		this.current_edit_record[key] = c_value;

		if ( key === 'country' || key === 'type_id' || key === 'province' || key === 'district' ) {

			if ( key != 'province' ) {
				this.detachElement( 'province' );
				this.eSetProvince( this.current_edit_record['country'] );
			}
			if ( key != 'district' ) {
				this.detachElement( 'district' );
				this.setDistrict( this.current_edit_record['country'], this.current_edit_record['province'] );
			}

			this.current_edit_record['type_id'] = this.edit_view_ui_dic['type_id'].getValue();
			this.current_edit_record['country'] = this.edit_view_ui_dic['country'].getValue();
			this.current_edit_record['province'] = this.edit_view_ui_dic['province'].getValue() ? this.edit_view_ui_dic['province'].getValue() : '00';
			this.current_edit_record['district'] = this.edit_view_ui_dic['district'].getValue() ? this.edit_view_ui_dic['district'].getValue() : '00';

			this.getAgencyOptions();
		}

		if ( key === 'legal_entity_id' ) {
			this.getRemittanceSourceAccount();
		}

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onAgencyIdChange() {
		var id_field_array_api_params = { 'agency_id': this.edit_view_ui_dic.agency_id.getValue() };
		var id_field_array = this.api.getOptions( 'agency_id_field_labels', id_field_array_api_params, { async: false } ).getResult();

		this.detachElement( 'primary_identification' );
		this.detachElement( 'secondary_identification' );
		this.detachElement( 'tertiary_identification' );

		for ( var key in id_field_array ) {
			this.attachElement( key );
			this.edit_view_form_item_dic[key].find( '.edit-view-form-item-label' ).text( id_field_array[key] );
		}
		this.editFieldResize();
	}

	getAgencyOptions() {
		var params = {
			'type_id': this.current_edit_record['type_id'],
			'country': this.current_edit_record['country'],
			'province': this.current_edit_record['province'],
			'district': this.current_edit_record['district']
		};
		var $this = this;
		this.api.getOptions( 'agency', params, {
			async: false,
			onResult: function( res ) {
				var result = res.getResult();
				if ( !result ) {
					result = [];
				}
				$this.agency_array = Global.buildRecordArray( result );

				if ( Global.isSet( $this.basic_search_field_ui_dic['agency_id'] ) ) {
					$this.basic_search_field_ui_dic['agency_id'].setSourceData( Global.buildRecordArray( result ) );
				}

				if ( Global.isSet( $this.adv_search_field_ui_dic['agency_id'] ) ) {
					$this.adv_search_field_ui_dic['agency_id'].setSourceData( Global.buildRecordArray( result ) );
				}

				$this.edit_view_ui_dic['agency_id'].setSourceData( $this.agency_array );
				if ( $this.current_edit_record['agency_id'] && res[$this.current_edit_record['agency_id']] ) {
					$this.edit_view_ui_dic['agency_id'].setValue( $this.current_edit_record['agency_id'] );
				} else {
					$this.current_edit_record['agency_id'] = $this.edit_view_ui_dic['agency_id'].getValue();
				}
				$this.onAgencyIdChange();
			}
		} );
	}

	getRemittanceSourceAccount() {
		var $this = this;

		var legal_entity_id = this.edit_view_ui_dic['legal_entity_id'].getValue();

		if ( !Global.isSet( legal_entity_id ) || Global.isFalseOrNull( legal_entity_id ) ) {
			legal_entity_id = this.current_edit_record['legal_entity_id'];
		}

		if ( TTUUID.isUUID( legal_entity_id ) ) {
			var source_account_args = {};
			source_account_args.filter_data = {};
			source_account_args.filter_data.legal_entity_id = legal_entity_id;
			source_account_args.filter_columns = {};
			source_account_args.filter_columns.id = true;
			source_account_args.filter_columns.name = true;

			$this.remittance_source_account_api.getRemittanceSourceAccount( source_account_args, {
				onResult: function( result ) {
					result = result.getResult();
					$this.remittance_source_account_array = result;
					$this.edit_view_ui_dic['remittance_source_account_id'].setSourceData( result );
				}
			} );
		}
	}

	onTypeChange( arg ) {
		if ( !Global.isSet( arg ) || Global.isFalseOrNull( arg ) ) {
			if ( !Global.isSet( this.current_edit_record['type_id'] ) || Global.isFalseOrNull( this.current_edit_record['type_id'] ) ) {
				this.current_edit_record['type_id'] = 10;
			}
		}

		this.editFieldResize();
	}

	setDistrict( c, p ) {
		var $this = this;

		if ( this.edit_view_ui_dic.type_id.getValue() == 30 ) {
			this.api.getDistrictOptions( c, p, {
				async: false, onResult: function( res ) {
					res = res.getResult();
					//#2486 When there are no provinces for 3rd party agencies in countries outside those with scripted agencies, we must hide the field
					if ( Object.keys( res ).length == 0 || Object.keys( res ).length == 1 && res['00'] ) {
						$this.current_edit_record['district'] = '00';
						return;
					}

					if ( !res ) {
						res = [];
					}
					$this.attachElement( 'district' );

					var first_element = null;
					if ( res.length <= 1 ) {
						first_element = {
							label: '-- ' + $.i18n._( 'Other' ) + ' --',
							value: '00',
							fullValue: '00',
							orderValue: -1,
							id: '00'
						};
					}
					$this.district_array = Global.buildRecordArray( res, first_element );

					$this.edit_view_ui_dic['district'].setSourceData( $this.district_array );

					if ( $this.current_edit_record['district'] && res[$this.current_edit_record['district']] ) {
						$this.edit_view_ui_dic['district'].setValue( $this.current_edit_record['district'] );
					} else {

						var district = $this.edit_view_ui_dic['district'].getValue();
						if ( district == TTUUID.not_exist_id || district == TTUUID.zero_id || district == false || district == null ) {
							district = '00';
							$this.edit_view_ui_dic['district'].setValue( district );
						}
						$this.current_edit_record['district'] = district;
					}
				}
			} );
		} else {
			$this.current_edit_record['district'] = '00';
			$this.edit_view_ui_dic['district'].setValue( '00' );
		}
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		this.collectUIDataToCurrentEditRecord();
		this.setEditViewDataDone();
	}

	eSetProvince( val, refresh ) {
		var $this = this;

		if ( this.edit_view_ui_dic.type_id.getValue() > 10 ) {
			this.api.getProvinceOptions( val, {
				async: false, onResult: function( result ) {
					result = result.getResult();

					if ( !result ) {
						result = [];
					}

					//#2486 When there are no provinces for 3rd party agencies in countries outside those with scripted agencies, we must hide the field
					if ( $this.edit_view_ui_dic.type_id.getValue() < 20 || ( Object.keys( result ).length == 0 || Object.keys( result ).length == 1 && result['00'] ) ) {
						$this.current_edit_record['province'] = '00';
						//Field stays hidden. value is set to '00'
						return;
					}

					$this.attachElement( 'province' );

					var first_element = null;
					if ( result.length <= 1 ) {
						first_element = {
							label: '-- ' + $.i18n._( 'Other' ) + ' --',
							value: '00',
							fullValue: '00',
							orderValue: -1,
							id: '00'
						};
					}
					$this.province_array = Global.buildRecordArray( result, first_element );
					$this.edit_view_ui_dic['province'].setSourceData( $this.province_array );

					if ( $this.current_edit_record['province'] && Global.isSet( result[$this.current_edit_record['province']] ) ) {
						$this.edit_view_ui_dic['province'].setValue( $this.current_edit_record['province'] );
					} else {
						var province = $this.edit_view_ui_dic['province'].getValue();
						if ( province == TTUUID.not_exist_id || province == TTUUID.zero_id || province == false || province == null ) {
							province = '00';
						}
						$this.current_edit_record['province'] = province;

					}

				}
			} );
		} else {
			$this.current_edit_record['province'] = '00';
			$this.edit_view_ui_dic['province'].setValue( '00 ' );
		}
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.onTypeChange();

		this.detachElement( 'province' );
		this.detachElement( 'district' );
		this.eSetProvince( this.current_edit_record['country'] );
		this.setDistrict( this.current_edit_record['country'], this.current_edit_record['province'] );

		this.getAgencyOptions();
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var $this = this;

		var tab_model = {
			'tab_payroll_remittance_agency': { 'label': $.i18n._( 'Remittance Agency' ), 'is_multi_column': true },
			'tab_payroll_remittance_agency_event': {
				'label': $.i18n._( 'Events' ),
				'init_callback': 'initSubEventView',
				'display_on_mass_edit': false,
				'html_template': this.getPayrollRemittanceAgencyEventTabHtml()
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		if ( this.navigation ) {
			this.navigation.AComboBox( {
				api_class: TTAPI.APIPayrollRemittanceAgency,
				id: this.script_name + '_navigation',
				allow_multiple_selection: false,
				layout_name: 'global_payroll_remittance_agency',
				navigation_mode: true,
				show_search_inputs: true
			} );
			this.setNavigation();
		}

		var tab_payroll_remittance_agency = this.edit_view_tab.find( '#tab_payroll_remittance_agency' );
		var tab_payroll_remittance_agency_column1 = tab_payroll_remittance_agency.find( '.first-column' );
		var tab_payroll_remittance_agency_column2 = tab_payroll_remittance_agency.find( '.second-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_payroll_remittance_agency_column1 );
		this.edit_view_tabs[0].push( tab_payroll_remittance_agency_column2 );

		// tab_employee_contact column1

		// Legal Entity
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APILegalEntity,
			allow_multiple_selection: false,
			layout_name: 'global_legal_entity',
			field: 'legal_entity_id',
			set_empty: true,
			show_search_inputs: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_payroll_remittance_agency_column1, '' );

		// Status

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'status_id'
		} );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_payroll_remittance_agency_column1 );

		// Name

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_payroll_remittance_agency_column1 );
		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_payroll_remittance_agency_column1 );
		form_item_input.parent().width( '45%' );

		// Type

		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			field: 'type_id'
		} );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_payroll_remittance_agency_column1 );

		// Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			set_empty: false,
			field: 'country'
		} );
		form_item_input.setSourceData( $this.country_array );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_payroll_remittance_agency_column1 );

		// Province/State
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			set_empty: false,
			field: 'province'
		} );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'Province/State' ), form_item_input, tab_payroll_remittance_agency_column1, '', null, true );

		// District
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			set_empty: false,
			field: 'district'
		} );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'District' ), form_item_input, tab_payroll_remittance_agency_column1, '', null, true );

		// Agency
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( {
			set_empty: false,
			field: 'agency_id'
		} );
		form_item_input.setSourceData( [] );
		this.addEditFieldToColumn( $.i18n._( 'Agency' ), form_item_input, tab_payroll_remittance_agency_column1 );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'primary_identification', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Primary Identification' ), form_item_input, tab_payroll_remittance_agency_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'secondary_identification', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Secondary Identification' ), form_item_input, tab_payroll_remittance_agency_column1, '', null, true );

		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'tertiary_identification', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'Tertiary Identification' ), form_item_input, tab_payroll_remittance_agency_column1, '', null, true );

		//prevent flashing province and district on addclick.
		this.detachElement( 'primary_identification' );
		this.detachElement( 'secondary_identification' );
		this.detachElement( 'tertiary_identification' );

		// column2

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {
			field: 'start_date'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Start Date' ), form_item_input, tab_payroll_remittance_agency_column2 );

		// Start Date
		form_item_input = Global.loadWidgetByName( FormItemType.DATE_PICKER );
		form_item_input.TDatePicker( {
			field: 'end_date'
		} );
		this.addEditFieldToColumn( $.i18n._( 'End Date' ), form_item_input, tab_payroll_remittance_agency_column2 );

		// Contact
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			field: 'contact_user_id',
			set_empty: true,
			show_search_inputs: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Contact' ), form_item_input, tab_payroll_remittance_agency_column2, '' );

		// Remittance Source Account
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIRemittanceSourceAccount,
			allow_multiple_selection: false,
			layout_name: 'global_remittance_source_account',
			field: 'remittance_source_account_id',
			set_empty: true,
			show_search_inputs: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Remittance Source Account' ), form_item_input, tab_payroll_remittance_agency_column2, '' );

		// Always On Business Day
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'always_week_day_id' } );
		form_item_input.setSourceData( this.always_week_day_array );
		this.addEditFieldToColumn( $.i18n._( 'Events Always On Business Day' ), form_item_input, tab_payroll_remittance_agency_column2 );

		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIRecurringHoliday,
			allow_multiple_selection: true,
			layout_name: 'global_recurring_holiday',
			show_search_inputs: true,
			set_empty: true,
			field: 'recurring_holiday_policy_id'
		} );
		this.addEditFieldToColumn( $.i18n._( 'Recurring Holidays' ), form_item_input, tab_payroll_remittance_agency_column2 );

		//prevent flashing province and district on addclick.
		this.detachElement( 'province' );
		this.detachElement( 'district' );
	}

	setProvince( val, m ) {
		var $this = this;

		if ( !val || val === TTUUID.not_exist_id || val === TTUUID.zero_id ) {
			$this.province_array = [];
			this.adv_search_field_ui_dic['province'].setSourceData( [] );
			this.basic_search_field_ui_dic['province'].setSourceData( [] );

		} else {
			this.company_api.getOptions( 'province', val, {
				async: false,
				onResult: function( res ) {
					res = res.getResult();
					if ( !res ) {
						res = [];
					}

					$this.province_array = Global.buildRecordArray( res, {
						label: '-- ' + $.i18n._( 'Other' ) + ' --',
						value: '00',
						fullValue: '00',
						orderValue: -1,
						id: '00'
					} );
					$this.adv_search_field_ui_dic['province'].setSourceData( $this.province_array );
					$this.basic_search_field_ui_dic['province'].setSourceData( $this.province_array );

				}
			} );
		}
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Legal Entity' ),
				in_column: 1,
				field: 'legal_entity_id',
				layout_name: 'global_legal_entity',
				api_class: TTAPI.APILegalEntity,
				multiple: true,
				basic_search: true,
				adv_search: true,
				script_name: 'LegalEntityView',
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
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Country' ),
				in_column: 2,
				field: 'country',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.COMBO_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Province/State' ),
				in_column: 2,
				field: 'province',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 3,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),

			new SearchField( {
				label: $.i18n._( 'Updated By' ),
				in_column: 3,
				field: 'updated_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: false,
				adv_search: true,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}

	initSubEventView() {
		var $this = this;

		if ( !this.current_edit_record.id ) {
			TTPromise.resolve( 'BaseViewController', 'onTabShow' ); //Since search() isn't called in this case, and we just display the "Please Save This Record ..." message, resolve the promise.
			return;
		}

		if ( this.sub_event_view_controller ) {
			this.sub_event_view_controller.buildContextMenu( true );
			this.sub_event_view_controller.setDefaultMenu();
			$this.sub_event_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_event_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_event_view_controller.initData(); //Init data in this parent view
			return;
		}

		Global.loadScript( 'views/company/payroll_remittance_agency/PayrollRemittanceAgencyEventViewController.js', function() {
			if ( !$this.edit_view_tab ) {
				return;
			}

			var tab_event = $this.edit_view_tab.find( '#tab_payroll_remittance_agency_event' );
			var firstColumn = tab_event.find( '.first-column-sub-view' );
			Global.trackView( 'Sub' + 'PayrollRemittanceEvent' + 'View' );
			PayrollRemittanceAgencyEventViewController.loadSubView( firstColumn, beforeLoadView, afterLoadView );

		} );

		function beforeLoadView() {

		}

		function afterLoadView( subViewController ) {

			$this.sub_event_view_controller = subViewController;
			$this.sub_event_view_controller.parent_key = 'payroll_remittance_agency_id';
			$this.sub_event_view_controller.parent_value = $this.current_edit_record.id;
			$this.sub_event_view_controller.parent_edit_record = $this.current_edit_record;
			$this.sub_event_view_controller.parent_view_controller = $this;
			$this.sub_event_view_controller.postInit = function() {
				this.initData();
			};
		}
	}

	onCancelClick() {
		super.onCancelClick();
		this.sub_event_view_controller = null;
	}

	onSaveClick( ignore_warning ) {
		super.onSaveClick( ignore_warning );
		this.sub_event_view_controller = null;
	}

	uniformVariable( data ) {
		if ( data ) {
			super.collectUIDataToCurrentEditRecord();

			if ( this.is_mass_editing != true ) {
				data.payroll_remittance_agency_id = this.parent_value;

				if ( data.type_id != 30 || data.district == TTUUID.not_exist_id || data.district == TTUUID.zero_id || data.district == false || data.district == null ) {
					data.district = '00';
				}

				if ( data.type_id <= 10 || data.province == TTUUID.not_exist_id || data.province == TTUUID.zero_id || data.province == false || data.province == null ) {
					data.province = '00';
				}

				var agency_identifier = '0000';
				if ( data.agency_id ) {
					agency_identifier = data.agency_id.slice( -4 );
				}
				data.agency_id = data.type_id + ':' + data.country + ':' + data.province + ':' + data.district + ':' + agency_identifier;
			}
		}

		return data;
	}

	getPayrollRemittanceAgencyEventTabHtml() {
		return `<div id="tab_payroll_remittance_agency_event" class="edit-view-tab-outside-sub-view">
					<div class="edit-view-tab" id="tab_payroll_remittance_agency_event_content_div">
						<div class="first-column-sub-view">
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
