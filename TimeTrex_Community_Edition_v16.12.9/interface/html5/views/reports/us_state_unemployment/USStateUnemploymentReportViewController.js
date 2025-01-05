export class USStateUnemploymentReportViewController extends ReportBaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			form_setup_states_array: null,

			form_setup_ui_dic: {},
			form_setup_data: {},

			save_form_setup_data: {}
		} );

		super( options );
	}

	initReport( options ) {
		this.script_name = 'USStateUnemploymentReport';
		this.viewId = 'USStateUnemploymentReport';
		this.context_menu_name = $.i18n._( 'US State Unemployment' );
		this.navigation_label = $.i18n._( 'Saved Report' );
		this.view_file = 'USStateUnemploymentReportView.html';
		this.api = TTAPI.APIUSStateUnemploymentReport;
		this.include_form_setup = true;
		this.form_setup_data = {};
	}

	initOptions( callBack ) {
		var options = [
			{ option_name: 'page_orientation' },
			{ option_name: 'font_size' },
			{ option_name: 'auto_refresh' },
			{ option_name: 'chart_display_mode' },
			{ option_name: 'chart_type' },
			{ option_name: 'templates' },
			{ option_name: 'setup_fields' },
			{ option_name: 'form_setup_states' }
		];

		this.initDropDownOptions( options, function( result ) {

			callBack( result ); // First to initialize drop down options, and then to initialize edit view UI.

		} );
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
			},
			exclude: [],
			include: [
				{
					label: $.i18n._( 'Save Setup' ),
					id: 'save_setup',
					group: 'form',
					vue_icon: 'tticon tticon-settings_black_24dp',
					menu_align: 'right',
					sort_order: 3000
				}
			]
		};

		return context_menu_model;
	}

	onContextMenuClick( context_btn, menu_name ) {
		var id;
		if ( Global.isSet( menu_name ) ) {
			id = menu_name;
		} else {

			if ( context_btn.disabled ) {
				return;
			}
		}

		if ( this.select_grid_last_row ) {
			this.export_grid.grid.jqGrid( 'saveRow', this.select_grid_last_row );
			this.select_grid_last_row = null;
		}

		var message_override = $.i18n._( 'Setup data for this report has not been configured yet. Please click on the Form Setup tab to do so now.' );

		switch ( id ) {
			case 'view':
				ProgressBar.showOverlay();
				this.onViewClick( null, false, message_override );
				break;
			case 'view_html':
				ProgressBar.showOverlay();
				this.onViewClick( 'html', false, message_override );
				break;
			case 'view_html_new_window':
				this.onViewClick( 'html', false, message_override );
				break;
			case 'export_excel':
				this.onViewExcelClick();
				break;
			case 'cancel':
				this.onCancelClick();
				break;
			case 'save_existed_report': //All report view
				this.onSaveExistedReportClick();
				break;
			case 'save_new_report': //All report view
				this.onSaveNewReportClick();
				break;
			case 'save_setup': //All report view
				this.onSaveSetup( $.i18n._( 'Form setup' ) );
				break;
		}
		Global.triggerAnalyticsContextMenuClick( context_btn, menu_name );
	}

	buildEditViewUI() {
		super.buildEditViewUI();

		var tab_3_label = this.edit_view.find( 'a[ref=tab_form_setup]' );
		tab_3_label.text( $.i18n._( 'Form Setup' ) );
	}

	buildFormSetupUI() {

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );

		var tab3_column1 = tab3.find( '.first-column' );

		this.edit_view_tabs[3] = [];

		this.edit_view_tabs[3].push( tab3_column1 );

		//Form setup
		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'form_setup_type', set_empty: false } );
		form_item_input.setSourceData( $this.form_setup_states_array );
		this.addEditFieldToColumn( $.i18n._( 'State' ), form_item_input, tab3_column1, '' );

		form_item_input.bind( 'formItemChange', function( e, target ) {
			$this.onFormSetupChange( target.getValue() );
		} );
	}

	onFormSetupChange( state ) {
		var $this = this;

		this.removeCurrentFormSetupUI();
		this.form_setup_ui_dic = {};

		var yes_no_arr = {
			'0': $.i18n._( 'No' ),
			'1': $.i18n._( 'Yes' )
		};

		switch ( state ) {
			case 'IA':
				$this.buildAdditionalCustomInputBox( state, 'reporting_unit_number', $.i18n._( 'Reporting Unit Number' ) );
				break;
			case 'CA':
				$this.buildAdditionalCustomInputBox( state, 'branch_code', $.i18n._( 'Branch Code' ) );
				$this.buildAdditionalCustomInputBox( state, 'wage_plan_code', $.i18n._( 'Wage Plan Code' ) );
				break;
			case 'LA':
				$this.buildAdditionalCustomInputBox( state, 'is_multiple_county_industry', $.i18n._( 'Multiple County Industry' ), yes_no_arr );
				$this.buildAdditionalCustomInputBox( state, 'is_multiple_worksite_location', $.i18n._( 'Multiple Worksite Location' ), yes_no_arr );
				$this.buildAdditionalCustomInputBox( state, 'is_multiple_worksite_indicator', $.i18n._( 'Multiple Worksite Indicator' ), yes_no_arr );
				$this.buildAdditionalCustomInputBox( state, 'occupation_classification_code', $.i18n._( 'Occupation Classification Code' ) );
				break;
			case 'CO':
				$this.buildAdditionalCustomInputBox( state, 'branch_code', $.i18n._( 'Unit / Division / Location / Plant Code' ) );
				$this.buildAdditionalCustomInputBox( state, 'is_seasonal', $.i18n._( 'Seasonal' ) );
				break;
			case 'IN':
				$this.buildAdditionalCustomInputBox( state, 'occupation_classification_code', $.i18n._( 'Occupation Classification Code' ) );
				$this.buildAdditionalCustomInputBox( state, 'designation', $.i18n._( 'Designation [FT/PT/Seasonal]' ) );
				break;
			case 'MI':
				$this.buildAdditionalCustomInputBox( state, 'multi_unit_number', $.i18n._( 'Multi-Unit Number' ) );
				break;
			case 'MN':
				$this.buildAdditionalCustomInputBox( state, 'reporting_unit_number', $.i18n._( 'Reporting Unit Number' ) );
				break;
			case 'TX':
				$this.buildAdditionalCustomInputBox( state, 'county_code', $.i18n._( 'County Code' ) );
				break;
			default:
				this.edit_view_tab.find( '#tab_form_setup .first-column' ).append( '<div id="no-setup-data" style="font-weight: bold; text-align:center">'+ $.i18n._( 'No form setup required for this state.' ) +'</div>' );
				break;
		}
	}

	/**
	 * Overridden to allow stateful form_setup formats. This ensures your changes are put into memory..
	 *
	 * @param target
	 * @param doNotDoValidate
	 */
	onFormItemChange( target, doNotValidate ) {
		if ( target && target.getField && target.getField() == 'form_setup_type' ) { // cannot read property getField of undefined
			var other = {};
			other.form_setup_type = this.current_edit_record.form_setup_type;
			other[other.form_setup_type] = {};

			// this.save_form_setup_data[other.form_setup_type] = this.getFormSetupData( other );
			this.form_setup_changed = true;
			return; //make room for the custom event above
		}
		super.onFormItemChange( target, doNotValidate );
	}

	/**
	 * Get the form setup data from the api
	 * @param res_Data
	 */
	setFormSetupData( res_data ) {
		if ( !res_data ) {
			this.show_empty_message = true;
		}

		this.save_form_setup_data = res_data;

		if ( res_data.form_setup_type && this.edit_view_ui_dic.form_setup_type ) {
			this.edit_view_ui_dic.form_setup_type.setValue( res_data.form_setup_type );
			this.onFormSetupChange( res_data.form_setup_type );
		}
	}

	buildAdditionalCustomInputBox( state, code, label, custom_dropdown_options = null ) {
		if ( !this.save_form_setup_data ) {
			this.save_form_setup_data = {};
		}

		if ( !this.save_form_setup_data[state] ) {
			this.save_form_setup_data[state] = {};
		}

		var $this = this;

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );
		var tab3_column1 = tab3.find( '.first-column' );
		this.edit_view_tabs[3] = [];
		this.edit_view_tabs[3].push( tab3_column1 );

		var field_name = 'form_setup_' + state + '_' + code;

		var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: field_name } );

		if ( !custom_dropdown_options ) {
			var h_box = $( '<div class=\'h-box\'></div>' );
			var text_box = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			text_box.css( 'margin-left', '10px' );
			text_box.TTextInput( { field: field_name + '_text' } );
			h_box.append( form_item_input );
			h_box.append( text_box );

		this.addEditFieldToColumn( $.i18n._( label ), [form_item_input, text_box], tab3_column1, '', h_box, true );
		this.setWidgetVisible( [form_item_input, text_box] );
		form_item_input.bind( 'formItemChange', ( e, target ) => {
			if ( target.getValue() === 0 ) {
				text_box.css( 'display', 'inline' );
				if ( this.save_form_setup_data[state][code + '_value'] ) {
					text_box.setValue( this.save_form_setup_data[state][code + '_value'] );
				}
			} else {
				text_box.css( 'display', 'none' );
				text_box.setValue( '' );
			}
		} );

			this.api.getOptions( 'form_setup_state_codes', {
				onResult: ( result ) => {

					var result_data = result.getResult();

					form_item_input.setSourceData( Global.buildRecordArray( result_data ) );

					if ( this.save_form_setup_data[state] && this.save_form_setup_data[state][code] ) {
						form_item_input.setValue( this.save_form_setup_data[state][code] );
					}
					form_item_input.trigger( 'formItemChange', [form_item_input, true] );
				}
			} );
		} else {
			form_item_input.setSourceData( custom_dropdown_options );
			this.addEditFieldToColumn( $.i18n._( label ), form_item_input, tab3_column1, '', null, true );
			this.setWidgetVisible( [form_item_input] );

			if ( this.save_form_setup_data[state] && this.save_form_setup_data[state][code] ) {
				form_item_input.setValue( this.save_form_setup_data[state][code] );
			}
			form_item_input.trigger( 'formItemChange', [form_item_input, true] );
		}

		this.form_setup_ui_dic[field_name] = this.edit_view_form_item_dic[field_name];
		delete this.edit_view_form_item_dic[field_name];

		this.editFieldResize( 3 );
	}

	removeCurrentFormSetupUI() {

		for ( var key in this.form_setup_ui_dic ) {
			var html_item = this.form_setup_ui_dic[key];
			if ( html_item ) {
				html_item.remove();
			}
		}

		$( '#no-setup-data' ).remove();

		//Error: Unable to get property 'find' of undefined or null reference in /interface/html5/ line 1033
		if ( !this.edit_view_tab ) {
			return;
		}

		var tab3 = this.edit_view_tab.find( '#tab_form_setup' );
		var tab3_column1 = tab3.find( '.first-column' );
		var clear_both_div = tab3_column1.find( '.clear-both-div' );

		clear_both_div.remove();
	}

	/**
	 * Gets array of properly configured values for the form_setup setup form.
	 *
	 * @param field_list Array
	 * @returns {{}|*}
	 */
	getFormSetupFieldValues( state, field_list ) {
		var ret_arr = {};

		for ( var i = 0; i < field_list.length; i++ ) {
			if ( this.edit_view_ui_dic['form_setup_' + state + '_' + field_list[i]] && !this.edit_view_ui_dic['form_setup_' + state + '_' + field_list[i]].getValue() ) {
				if ( this.edit_view_ui_dic['form_setup_' + state + '_' + field_list[i] + '_text'] ) {
					//States with text fields
					ret_arr[field_list[i]] = this.edit_view_ui_dic['form_setup_' + state + '_' + field_list[i] + '_text'].getValue();
					ret_arr[field_list[i] + '_value'] = this.edit_view_ui_dic['form_setup_' + state + '_' + field_list[i] + '_text'].getValue();
				} else {
					//States without text fields such as Louisiana custom dropdown options
					ret_arr[field_list[i]] = this.edit_view_ui_dic['form_setup_' + state + '_' + field_list[i]].getValue();
				}
			} else {
				if ( !this.edit_view_ui_dic['form_setup_' + state + '_' + field_list[i]] ) {
					ret_arr[field_list[i]] = '';
				} else {
					ret_arr[field_list[i]] = this.edit_view_ui_dic['form_setup_' + state + '_' + field_list[i]].getValue();
				}
			}
		}
		return ret_arr;
	}

	getFormData( other, for_display ) {
		if ( !other || !other.form_setup_type ) {
			return false;
		}

		other[other.form_setup_type] = this.getFormSetupFieldValues( other.form_setup_type, this.getFieldList( other.form_setup_type ) );

		if ( !this.save_form_setup_data ) {
			this.save_form_setup_data = {};
		}
		this.save_form_setup_data[other.form_setup_type] = other[other.form_setup_type];
		this.save_form_setup_data['form_setup_type'] = other.form_setup_type;

		if ( for_display ) {
			for ( var key in this.save_form_setup_data ) {
				if ( key !== false && typeof ( this.save_form_setup_data[key] ) !== 'string' ) {
					this.save_form_setup_data[key] = this.convertFormSetupValues( this.save_form_setup_data[key] );
				}
			}
		}

		return this.save_form_setup_data;
	}

	getFieldList( state ) {
		var field_list = [];

		switch ( state ) {
			case 'IA':
				field_list = ['reporting_unit_number'];
				break;
			case 'CA':
				field_list = ['branch_code', 'wage_plan_code'];
				break;
			case 'LA':
				field_list = ['is_multiple_county_industry', 'is_multiple_worksite_location', 'is_multiple_worksite_indicator', 'occupation_classification_code'];
				break;
			case 'CO':
				field_list = ['branch_code', 'is_seasonal'];
				break;
			case 'IN':
				field_list = ['occupation_classification_code', 'designation'];
				break;
			case 'MI':
				field_list = ['multi_unit_number'];
				break;
			case 'MN':
				field_list = ['reporting_unit_number'];
				break;
			case 'TX':
				field_list = ['county_code'];
				break;
		}

		return field_list;
	}

	/* jshint ignore:start */
	getFormSetupData( for_view ) {
		var other = {};
		other.form_setup_type = this.edit_view_ui_dic.form_setup_type.getValue();

		other[other.form_setup_type] = {};

		other = this.getFormData( other, true );

		// if ( !for_view && other.form_setup_type ) {
		// 	var form_setup_type = other.form_setup_type;
		// 	other = other[form_setup_type];
		// 	other.form_setup_type = form_setup_type;
		// 	other[form_setup_type] = {};
		// }

		return other;
	}

	/**
	 * Backwards compatible function for custom data to be moved from the way the api stores it to the way the form needs it.
	 *
	 * the old custom field data was stored in obj[key]
	 * new custom field data is stored in obj[key+'_value']
	 *
	 * ie. obj[company_code] is now obj[company_code_value]
	 *
	 * @param data
	 * @returns {*}
	 */
	convertFormSetupValues( data ) {
		for ( var api_data_key in data ) {
			var form_data_key = api_data_key.substr( 0, api_data_key.indexOf( '_value' ) );
			if ( api_data_key.search( '_value' ) > 0 ) {
				data[form_data_key] = data[api_data_key];
			}
		}
		//conversion for lower form_setup grid data from old format
		if ( data.form_setup_columns && !data.columns && data.form_setup_type != 0 && data.form_setup_columns[data.form_setup_type] ) {
			data.columns = {};
			data.columns = data.form_setup_columns[data.form_setup_type].columns;
		}

		return data;
	}

}