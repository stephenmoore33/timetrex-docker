import '@/global/widgets/filebrowser/TImage';
import '@/global/widgets/filebrowser/TImageAdvBrowser';

export class RemittanceSourceAccountViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#remittance_source_account_view_container',

			status_array: null,
			type_array: null,
			country_array: null,
			data_format_array: null,
			ach_transaction_type_array: null,
			ach_transaction_type_data: null,
			company_api: null
		} );

		super( options );
	}

	init() {
		//this._super('initialize' );
		this.edit_view_tpl = 'RemittanceSourceAccountEditView.html';
		this.permission_id = 'remittance_source_account';
		this.viewId = 'RemittanceSourceAccount';
		this.script_name = 'RemittanceSourceAccountView';
		this.table_name_key = 'remittance_source_account';
		this.context_menu_name = $.i18n._( 'Remittance Source Accounts' );
		this.navigation_label = $.i18n._( 'Remittance Source Account' );
		this.api = TTAPI.APIRemittanceSourceAccount;
		this.company_api = TTAPI.APICompany;

		this.render();
		this.buildContextMenu();

		this.initData();

		$( '#tab_advanced_content_div .edit-view-form-item-div .edit-view-form-item-label-div' ).css( 'border-top-left-radius', '0px' );
		$( '#tab_advanced_content_div .edit-view-form-item-div:first .edit-view-form-item-label-div' ).css( 'border-top-left-radius', '5px' );
	}

	initOptions() {
		var $this = this;

		var options = [
			{ option_name: 'status', api: this.api },
			{ option_name: 'type', api: this.api },
			{ option_name: 'country', field_name: 'country', api: this.company_api },
		];

		this.initDropDownOptions( options );

		this.api.getOptions( 'ach_transaction_type', {
			onResult: function( res ) {
				var result = res.getResult();
				$this.ach_transaction_type_data = result;
				$this.ach_transaction_type_array = Global.buildRecordArray( result );
			}
		} );
	}

	getSignatureUrl() {
		var url = false;
		if ( this.current_edit_record.id ) {
			url = ServiceCaller.getURLByObjectType( 'remittance_source_account' ) + '&object_id=' + this.current_edit_record.id
		}
		Debug.Text( url, 'RemittanceSourceAccountViewController.js', 'RemittanceSourceAccountViewController', 'getSignatureUrl', 10 );
		return url;
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.file_browser.setImage( this.getSignatureUrl() );
	}

	uniformVariable( record ) {
		//ensure that the variable variable fields are set to false if they aren't showing.
		if ( this.edit_view_ui_dic && this.current_edit_record.remittance_source_account_id != TTUUID.zero_id ) { //Keep accountd data if UUID == zero_id
			for ( var i = 1; i <= 10; i++ ) {
				if ( i == 1 ) {
					if ( this.edit_view_ui_dic['country'].getValue() == 'US' ) {
						if ( this.edit_view_ui_dic['value1_2']  ) {
							record['value1'] = record['value1_2'] ? record['value1_2'] : this.edit_view_ui_dic['value1_2'].getValue();
						}
					} else {
						if ( this.edit_view_ui_dic['value1_1']  ) {
							record['value1'] = record['value1_1'] ? record['value1_1'] : this.edit_view_ui_dic['value1_1'].getValue();
						}
					}
				} else {
					if ( !this.is_mass_editing && record['value' + i] && ( typeof this.edit_view_ui_dic['value' + i] == 'undefined' ) ) {
						record['value' + i] = false;
					}
				}
			}
		}

		return record;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: ['export_excel'],
			include: [
				{
					label: '', //Empty label. vue_icon is displayed instead of text.
					id: 'other_header',
					menu_align: 'right',
					action_group: 'other',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
				},
				{
					label: $.i18n._( 'Sample File' ),
					id: 'export_export',
					action_group: 'other',
					menu_align: 'right',
					vue_icon: 'tticon tticon-file_download_black_24dp',
				}]
		};

		return context_menu_model;
	}

	setCustomDefaultMenuIcon( id, context_btn, grid_selected_length ) {
		switch ( id ) {
			case 'export_export':
				ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
				this.setMenuExportIcon( context_btn, grid_selected_length );
				break;
		}
	}

	setCustomEditMenuIcon( id, context_btn ) {
		switch ( id ) {
			case 'export_export':
				ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
				this.setMenuExportIcon( context_btn );
				break;
		}
	}

	setMenuExportIcon( context_btn ) {
		//do not show for edit screens or non-grid screens.
		if ( this.getSelectedItems().length > 0 ) {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else if ( this.edit_only_mode || this.grid == undefined || this.sub_view_mode ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false )
		} else {
			ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onExportClick() {
		var post_data = { 0: this.getGridSelectIdArray() };
		Global.APIFileDownload( this.api.className, 'testExport', post_data );
	}

	onFormItemChange( target, doNotValidate ) {
		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );
		var key = target.getField();
		var c_value = target.getValue();

		switch ( key ) {
			case 'value1_1':
			case 'value1_2':
				this.current_edit_record['value1'] = c_value;
				break;
			case 'country':
			case 'type_id':
				this.onTypeChange();
				break;
			case 'data_format_id':
				this.onDataFormatChange();
				break;
			case 'value24':
				if ( c_value != false ) {
					this.attachElement( 'value25' ).text( $.i18n._( 'Offset Description' ) );
					this.attachElement( 'value27' ).text( $.i18n._( 'Offset Routing' ) );
					this.attachElement( 'value28' ).text( $.i18n._( 'Offset Account' ) );
					if ( this.edit_view_ui_dic.value25.getValue().length == 0 ) {
						this.edit_view_ui_dic.value25.setValue( 'OFFSET' );
					}
				} else {
					this.detachElement( 'value25' );
					this.detachElement( 'value27' );
					this.detachElement( 'value28' );
				}
				break;
		}
		for ( var evud_key in this.edit_view_ui_dic ) {
			this.current_edit_record[evud_key] = this.edit_view_ui_dic[evud_key].getValue();
		}

		this.current_edit_record[key] = c_value;

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'export_export':
				this.onExportClick();
				break;
		}
	}

	onSaveClick( ignoreWarning ) {
		super.onSaveClick( ignoreWarning );
		Global.clearCache( 'getOptions_type' ); //Needs to clear cache so if they add a source account of a new type, it will immediately appear in the Type dropdown for Payment Methods.
	}

	attachElement( key ) {
		//Error: Uncaught TypeError: Cannot read property 'insertBefore' of undefined in interface/html5/views/BaseViewController.js?v=9.0.0-20150822-210544 line 6439
		if ( !this.edit_view_form_item_dic || !this.edit_view_form_item_dic[key] ) {
			return;
		}

		var place_holder = $( '.place_holder_' + key );
		this.edit_view_form_item_dic[key].insertBefore( place_holder );
		place_holder.remove();

		return $( this.edit_view_form_item_dic[key].find( '.edit-view-form-item-label' ) );
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets
		for ( var key in this.current_edit_record ) {

			if ( !this.current_edit_record.hasOwnProperty( key ) ) {
				continue;
			}

			var widget = this.edit_view_ui_dic[key];

			if ( key === 'value1' && this.current_edit_record[key] && !this.is_mass_editing ) {
				if ( Global.isSet( this.ach_transaction_type_data[this.current_edit_record[key]] ) ) {
					this.edit_view_ui_dic['value1_2'].setValue( this.current_edit_record[key] );
				} else {
					this.edit_view_ui_dic['value1_1'].setValue( this.current_edit_record[key] );
				}
			}

			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'value1_1':
					case 'value1_2':
					    break;
					case 'type_id': //popular case
						widget.setValue( this.current_edit_record[key] );
						this.onTypeChange();
						break;
					case 'data_format_id': //popular case
						widget.setValue( this.current_edit_record[key] );
						this.onDataFormatChange();
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

	onDataFormatChange() {
		var $this = this;
		var type_id = this.edit_view_ui_dic.type_id.getValue();
		var data_format_id = this.edit_view_ui_dic.data_format_id.getValue();

		//alert(' DataFormatChange: Type: '+ type_id + ' Data Format: '+ data_format_id );

		if ( type_id == false || data_format_id == false ) {
			return;
		}

		$( this.edit_view_tab.find( 'ul li' )[1] ).hide(); //Hide Advanced tab

		this.detachElement( 'value4' );
		this.detachElement( 'value5' );
		this.detachElement( 'value6' );
		this.detachElement( 'value7' );
		this.detachElement( 'value8' );
		this.detachElement( 'value9' );
		this.detachElement( 'value10' );
		this.detachElement( 'value11' );
		this.detachElement( 'value12' );
		this.detachElement( 'value13' );
		this.detachElement( 'value14' );
		this.detachElement( 'value15' );
		this.detachElement( 'value16' );
		this.detachElement( 'value17' );
		this.detachElement( 'value18' );
		this.detachElement( 'value19' );
		this.detachElement( 'value20' );
		this.detachElement( 'value21' );
		this.detachElement( 'value22' );
		this.detachElement( 'value23' );
		this.detachElement( 'value24' );
		this.detachElement( 'value25' );
		this.detachElement( 'value26' );
		this.detachElement( 'value27' );
		this.detachElement( 'value28' );
		this.detachElement( 'value29' );
		this.detachElement( 'value30' );

		this.detachElement( 'signature' );
		this.edit_view_ui_dic.value5.parent().find( '.mm_field_unit_text' ).remove();
		this.edit_view_ui_dic.value6.parent().find( '.mm_field_unit_text' ).remove();
		if ( type_id != 2000 ) {
			TTPromise.wait( null, null, function() {
				$this.edit_view_ui_dic.value5.setWidth( 200 );
				$this.edit_view_ui_dic.value6.setWidth( 200 );
			} );
		}

		if ( type_id == 2000 ) {
			if ( Global.getProductEdition() >= 15 ) { //All cheque formats.
				$( this.edit_view_tab.find( 'ul li' )[1] ).show(); //Show Advanced Tab

				this.attachElement( 'value5' ).text( $.i18n._( 'Vertical Alignment' ) );
				this.attachElement( 'value6' ).text( $.i18n._( 'Horizontal Alignment' ) );
				this.attachElement( 'signature' );

				this.edit_view_ui_dic.value5.parent().append( '<span class="mm_field_unit_text">&nbsp;mm</span>' );
				this.edit_view_ui_dic.value6.parent().append( '<span class="mm_field_unit_text">&nbsp;mm</span>' );

				TTPromise.wait( null, null, function() {
					if ( $this.edit_view_ui_dic && $this.edit_view_ui_dic.value5 ) {
						$this.edit_view_ui_dic.value5.setWidth( 42 );
					}
					if ( $this.edit_view_ui_dic && $this.edit_view_ui_dic.value6 ) {
						$this.edit_view_ui_dic.value6.setWidth( 42 );
					}
				} );
			}
		} else if ( type_id == 3000 ) {
			if ( data_format_id == 5 ) { //TimeTrex Remittances
				// this.attachElement('value5').text($.i18n._('User Name') );
				// this.attachElement('value6').text($.i18n._('API Key') );
			} else if ( data_format_id == 10 ) { //US - ACH
				$( this.edit_view_tab.find( 'ul li' )[1] ).show(); //Show Advanced Tab

				this.attachElement( 'value4' ).text( $.i18n._( 'Business Number' ) );
				this.attachElement( 'value5' ).text( $.i18n._( 'Immediate Origin' ) );
				this.attachElement( 'value6' ).text( $.i18n._( 'Immediate Origin Name' ) );
				this.attachElement( 'value7' ).text( $.i18n._( 'Immediate Dest.' ) );
				this.attachElement( 'value8' ).text( $.i18n._( 'Immediate Dest. Name' ) );
				this.attachElement( 'value9' ).text( $.i18n._( 'Trace Number' ) );
				this.attachElement( 'value10' ).text( $.i18n._( 'Discretionary Data' ) );
				this.attachElement( 'value11' ).text( $.i18n._( 'Company Name' ) );

				this.attachElement( 'value24' ).text( $.i18n._( 'Offset Transaction' ) );
				if ( this.current_edit_record.value24 == 1 ) {
					this.current_edit_record.value24 = true;
					this.attachElement( 'value25' ).text( $.i18n._( 'Offset Description' ) );
					this.attachElement( 'value27' ).text( $.i18n._( 'Offset Routing' ) );
					this.attachElement( 'value28' ).text( $.i18n._( 'Offset Account' ) );
				}
				this.attachElement( 'value29' ).text( $.i18n._( 'File Header Line' ) );
				this.attachElement( 'value30' ).text( $.i18n._( 'File Trailer Line' ) );
			} else if ( data_format_id == 20 || data_format_id == 30 || data_format_id == 50 ) { //CA - EFT
				$( this.edit_view_tab.find( 'ul li' )[1] ).show(); //Show Advanced Tab

				this.attachElement( 'value5' ).text( $.i18n._( 'Originator ID' ) );
				this.attachElement( 'value6' ).text( $.i18n._( 'Originator Short Name' ) );
				this.attachElement( 'value7' ).text( $.i18n._( 'Data Center ID' ) );
				//this.attachElement( 'value7' ).text( $.i18n._('Data Center Name') );

				this.attachElement( 'value26' ).text( $.i18n._( 'Return Institution' ) );
				this.attachElement( 'value27' ).text( $.i18n._( 'Return Transit' ) );
				this.attachElement( 'value28' ).text( $.i18n._( 'Return Account' ) );
				this.attachElement( 'value29' ).text( $.i18n._( 'File Header Line' ) );
				this.attachElement( 'value30' ).text( $.i18n._( 'File Trailer Line' ) );
			}
		}
	}

	onTypeChange() {
		var $this = this;
		var type_id = this.edit_view_ui_dic.type_id.getValue();
		var country = ( this.edit_view_ui_dic.country.getValue() && this.edit_view_ui_dic.country.getValue() != TTUUID.zero_id ) ? this.edit_view_ui_dic.country.getValue() : this.current_edit_record.country; //sometimes it's false for no reason.

		$( this.edit_view_tab.find( 'ul li' )[1] ).show(); //Show Advanced tab

		this.detachElement( 'data_format_id' );
		this.detachElement( 'last_transaction_number' );
		this.detachElement( 'value1_1' );
		this.detachElement( 'value1_2' );
		//this.detachElement( 'value1' );
		this.detachElement( 'value2' );
		this.detachElement( 'value3' );

		if ( country == false || type_id == false ) {
			return;
		}

		if ( type_id == 2000 ) {
			this.attachElement( 'last_transaction_number' ).text( $.i18n._( 'Last Check Number' ) );
		} else if ( type_id == 3000 ) {
			this.attachElement( 'last_transaction_number' ).text( $.i18n._( 'Last Batch Number' ) );

			if ( !this.is_mass_editing && country != null ) {
				if ( country == 'US' ) { //ACH
					this.attachElement( 'value1_2' ).text( $.i18n._( 'Account Type' ) );
					this.attachElement( 'value2' ).text( $.i18n._( 'Routing' ) );
					this.attachElement( 'value3' ).text( $.i18n._( 'Account' ) );
					if ( Global.isFalseOrNull( this.current_edit_record['value1'] ) ) {
						this.current_edit_record['value1'] = this.edit_view_ui_dic['value1_2'].getValue();
						this.current_edit_record['value1_2'] = this.edit_view_ui_dic['value1_2'].getValue();
					}
				} else if ( country == 'CA' ) { //Canadian EFT
					this.attachElement( 'value1_1' ).text( $.i18n._( 'Institution' ) );
					this.attachElement( 'value2' ).text( $.i18n._( 'Bank Transit' ) );
					this.attachElement( 'value3' ).text( $.i18n._( 'Account' ) );
				// Carribbean is now handled like US, as some banks use insitition/branch, and some use routing numbers. We can always obtain institution/branch from routing number, so use it as the common denominator.
				// } else if ( $.inArray( country, ['AG', 'BS', 'BB', 'BZ', 'DO', 'GY', 'HT', 'JM', 'DM', 'GD', 'KN', 'LC', 'VC', 'SR', 'TT'] ) != -1 ) { //Carribbean countries.
				// 	this.attachElement( 'value1_1' ).text( $.i18n._( 'Institution' ) );
				// 	this.attachElement( 'value2' ).text( $.i18n._( 'Bank Transit' ) );
				// 	this.attachElement( 'value3' ).text( $.i18n._( 'Account' ) );
				} else {
					this.attachElement( 'value1_2' ).text( $.i18n._( 'Account Type' ) );
					this.attachElement( 'value2' ).text( $.i18n._( 'Routing' ) );
					this.attachElement( 'value3' ).text( $.i18n._( 'Account' ) );
					if ( Global.isFalseOrNull( this.current_edit_record['value1'] ) ) {
						this.current_edit_record['value1'] = this.edit_view_ui_dic['value1_2'].getValue();
						this.current_edit_record['value1_2'] = this.edit_view_ui_dic['value1_2'].getValue();
					}
				}
			}
		}

		$( '#tab_advanced_content_div .edit-view-form-item-div .edit-view-form-item-label-div' ).css( 'border-top-left-radius', '0px' );
		$( '#tab_advanced_content_div .edit-view-form-item-div:first .edit-view-form-item-label-div' ).css( 'border-top-left-radius', '5px' );

		var $this = this;
		this.api.getOptions( 'data_format', { 'type_id': type_id, 'country': country }, {
			async: false,
			onResult: function( res ) {
				$this.attachElement( 'data_format_id' );
				var result = res.getResult();

				$this.data_format_array = Global.buildRecordArray( result );

				if ( Global.isSet( $this.basic_search_field_ui_dic['data_format_id'] ) ) {
					$this.basic_search_field_ui_dic['data_format_id'].setSourceData( $this.data_format_array );
				}

				if ( Global.isSet( $this.adv_search_field_ui_dic['data_format_id'] ) ) {
					$this.adv_search_field_ui_dic['data_format_id'].setSourceData( $this.data_format_array );
				}

				$this.edit_view_ui_dic['data_format_id'].setSourceData( $this.data_format_array );
				if ( $this.current_edit_record['data_format_id'] && result[$this.current_edit_record['data_format_id']] ) {
					$this.edit_view_ui_dic['data_format_id'].setValue( $this.current_edit_record['data_format_id'] );
				} else {
					$this.current_edit_record['data_format_id'] = $this.edit_view_ui_dic['data_format_id'].getValue();
				}

				$this.onDataFormatChange();
			}
		} );

		this.editFieldResize();
	}

	buildEditViewUI() {

		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_remittance_source_account': { 'label': $.i18n._( 'Remittance Source Account' ) },
			'tab_advanced': {
				'label': $.i18n._( 'Advanced' )
			},
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIRemittanceSourceAccount,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_remittance_source_account',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_remittance_source_account = this.edit_view_tab.find( '#tab_remittance_source_account' );
		var tab_remittance_source_account_column1 = tab_remittance_source_account.find( '.first-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_remittance_source_account_column1 );

		//Advanced tab
		var tab_advanced = this.edit_view_tab.find( '#tab_advanced' );
		var tab_advanced_column1 = tab_advanced.find( '.first-column' );
		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_advanced_column1 );

		// Legal Entity
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APILegalEntity,
			allow_multiple_selection: false,
			layout_name: 'global_legal_entity',
			field: 'legal_entity_id',
			//set_empty: true,
			set_any: true,
			show_search_inputs: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_remittance_source_account_column1, '' );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_remittance_source_account_column1, '' );

		// Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_remittance_source_account_column1 );
		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_remittance_source_account_column1 );
		form_item_input.parent().width( '45%' );

		//TYPE
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_remittance_source_account_column1, '' );

		//Country
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'country', set_empty: true } );
		form_item_input.setSourceData( $this.country_array );
		this.addEditFieldToColumn( $.i18n._( 'Country' ), form_item_input, tab_remittance_source_account_column1 );

		// Currency
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APICurrency,
			allow_multiple_selection: false,
			layout_name: 'global_currency',
			field: 'currency_id',
			set_empty: true,
			show_search_inputs: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_remittance_source_account_column1 );

		// Data Format
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'data_format_id' } );
		form_item_input.setSourceData( $this.data_format_array );
		this.addEditFieldToColumn( $.i18n._( 'Format' ), form_item_input, tab_remittance_source_account_column1, '', null, true );

		// Last Transaction Number
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'last_transaction_number', width: '60' } );
		this.addEditFieldToColumn( $.i18n._( 'Last Transaction Number' ), form_item_input, tab_remittance_source_account_column1, '', null, true );


		//generate Value# fields 1-30
		//shorter and easier to read than 150 extra lines
		for ( var i = 1; i <= 30; i++ ) {
			var width = '200';

			var type_id = this.edit_view_ui_dic.type_id.getValue();
			if ( type_id == 2000 && Global.getProductEdition() >= 15 && ( i == 5 || i == 6 ) ) { //5=Vertical Alignment, 6=Horizaontal Alignment
				width = 42;
			}

			if ( i == 29 || i == 30 ) { //29: file header line. 30: file trailer line.
				width = '500';
			}
			var tab_for_values = tab_remittance_source_account_column1;
			if ( i > 3 ) {
				tab_for_values = tab_advanced_column1;
			}

			if ( i == 1 ) { //ACH
				form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
				form_item_input.TTextInput( { field: 'value1_1', validation_field: 'value1', width: width } );
				this.addEditFieldToColumn( $.i18n._( 'Value' + i ), form_item_input, tab_for_values, '', null, true );

				form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
				form_item_input.TComboBox( { field: 'value1_2', validation_field: 'value1' } );
				form_item_input.setSourceData( $this.ach_transaction_type_array );
				this.addEditFieldToColumn( $.i18n._( 'Value' + i ), form_item_input, tab_for_values, '', null, true );
			} else {
				if ( i == 24 ) { //24: Offset Transaction
					form_item_input = Global.loadWidgetByName( FormItemType.CHECKBOX );
					form_item_input.TCheckbox( { field: 'value' + i } );
				} else {
					form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
					form_item_input.TTextInput( { field: 'value' + i, width: width } );
				}
				this.addEditFieldToColumn( $.i18n._( 'Value' + i ), form_item_input, tab_for_values, '', null, true );
			}

		}

		//Signature Upload
		if ( typeof FormData == 'undefined' ) {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_BROWSER );

			this.file_browser = form_item_input.TImageBrowser( {
				field: 'signature',
				default_width: 256,
				default_height: 47
			} );

			this.file_browser.bind( 'imageChange', function( e, target ) {
				new ServiceCaller().uploadFile( target.getValue(), 'object_type=remittance_source_account&object_id=' + $this.current_edit_record.id, {
					onResult: function( result ) {

						if ( result.toLowerCase() === 'true' ) {
							$this.file_browser.setImage( $this.getSignatureUrl() );
						} else {
							TAlertManager.showAlert( result, 'Error' );
						}
					}
				} );

			} );
		} else {
			form_item_input = Global.loadWidgetByName( FormItemType.IMAGE_AVD_BROWSER );

			this.file_browser = form_item_input.TImageAdvBrowser( {
				field: 'signature', callBack: function( form_data ) {
					new ServiceCaller().uploadFile( form_data, 'object_type=remittance_source_account&object_id=' + $this.current_edit_record.id, {
						onResult: function( result ) {

							if ( result.toLowerCase() === 'true' ) {
								$this.file_browser.setImage( $this.getSignatureUrl() );
							} else {
								TAlertManager.showAlert( result, 'Error' );
							}
						}
					} );

				}
			} );
		}

		if ( this.is_edit ) {
			this.attachElement( 'signature' );
			this.file_browser.setEnableDelete( true );
			this.file_browser.bind( 'deleteClick', function( e, target ) {
				$this.api.deleteImage( $this.current_edit_record.id, {
					onResult: function( result ) {
						$this.onDeleteImage();
					}
				} );
			} );
		} else {
			this.detachElement( 'signature' );
		}

		this.addEditFieldToColumn( $.i18n._( 'Signature' ), this.file_browser, tab_advanced_column1, '', null, true, true );
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
				adv_search: false,
				script_name: 'LegalEntityView',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
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
				label: $.i18n._( 'Name' ),
				in_column: 1,
				field: 'name',
				multiple: true,
				basic_search: true,
				adv_search: false,
				form_item_type: FormItemType.TEXT_INPUT
			} ),
			new SearchField( {
				label: $.i18n._( 'Created By' ),
				in_column: 3,
				field: 'created_by',
				layout_name: 'global_user',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: false,
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
				basic_search: true,
				adv_search: false,
				script_name: 'EmployeeView',
				form_item_type: FormItemType.AWESOME_BOX
			} )

		];
	}
}