export class RemittanceDestinationAccountViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#remittance_destination_account_view_container',

			status_array: null,
			type_array: null,
			priority_array: [],
			amount_type_array: null,
			ach_transaction_type_array: null,
			ach_transaction_type_data: null,
			remittance_source_account_array: null,
			company_api: null,
			user_default_api: null,
			user_api: null,
			legal_entity_id: false,
			remittance_source_account_api: null,
			sub_document_view_controller: null,
			document_object_type_id: null,
			is_first_load: true,
			is_subview: false,
			remittance_source_account_country_map: {},
		} );

		super( options );
	}

	init( options ) {
		this.type_array = [];
		//this._super('initialize', options );
		this.edit_view_tpl = 'RemittanceDestinationAccountEditView.html';
		this.permission_id = 'remittance_destination_account';
		this.viewId = 'RemittanceDestinationAccount';
		this.script_name = 'RemittanceDestinationAccountView';
		this.table_name_key = 'remittance_destination_account';
		this.context_menu_name = $.i18n._( 'Pay Methods' );
		this.navigation_label = $.i18n._( 'Pay Methods' );
		this.document_object_type_id = 320;
		this.api = TTAPI.APIRemittanceDestinationAccount;
		this.company_api = TTAPI.APICompany;
		this.user_api = TTAPI.APIUser;
		this.user_default_api = TTAPI.APIUserDefault;
		this.remittance_source_account_api = TTAPI.APIRemittanceSourceAccount;

		this.render();
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

	initOptions() {
		var $this = this;
		this.initDropDownOption( 'status' );
		this.initDropDownOption( 'amount_type' );
		this.api.getOptions( 'priority', {
			onResult: function( res ) {
				$this.priority_array = res.getResult();
			}
		} );
		this.api.getOptions( 'ach_transaction_type', {
			onResult: function( res ) {
				var result = res.getResult();
				$this.ach_transaction_type_data = result;
				$this.ach_transaction_type_array = Global.buildRecordArray( result );
			}
		} );

		this.remittance_source_account_api.getOptions( 'type', {
			onResult: function( result ) {
				result = result.getResult();
				//Prevent exception when in subgrid mode: "TypeError: $this.basic_search_field_ui_dic.type_id is undefined"
				if ( $this.basic_search_field_ui_dic && $this.basic_search_field_ui_dic['type_id'] ) {
					$this.basic_search_field_ui_dic['type_id'].setSourceData( Global.buildRecordArray( result ) );
				}
				if ( $this.adv_search_field_ui_dic && $this.adv_search_field_ui_dic['type_id'] ) {
					$this.adv_search_field_ui_dic['type_id'].setSourceData( Global.buildRecordArray( result ) );
				}
			}
		} );
	}

	getDefaultDisplayColumns( callBack ) {

		var $this = this;
		this.api.getOptions( 'default_display_columns', {
			onResult: function( columns_result ) {

				var columns_result_data = columns_result.getResult();
				$this.default_display_columns = [];

				for ( var n in columns_result_data ) {
					if ( $this.is_subview == true && ( columns_result_data[n] == 'user_first_name' || columns_result_data[n] == 'user_last_name' ) ) {
						continue;
					} else {
						$this.default_display_columns.push( columns_result_data[n] );
					}
				}

				if ( callBack ) {
					callBack();
				}

			}
		} );
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

		var $this = this;
		//Set current edit record data to all widgets
		// First to get legal_entity_id

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
					case 'amount_type_id': //popular case
						this.onAmountTypeChange( this.current_edit_record[key] );
						widget.setValue( this.current_edit_record[key] );
						break;
					case 'value1_1':
					case 'value1_2':
					case 'type_id':
						break;
					case 'currency_id':
					case 'remittance_source_account_id':
						widget.setValue( this.current_edit_record[key] );
						this.detachElement( key );
						break;
					default:
						widget.setValue( this.current_edit_record[key] );
						break;
				}

			}
		}

		//Only allow changing employee if they have permissions to do so.
		if ( !( PermissionManager.validate( this.permission_id, 'edit' ) || PermissionManager.validate( this.permission_id, 'edit_child' ) ) ) {
			$this.current_edit_record['user_id'] = LocalCacheData.getLoginUser().id;
			$this.edit_view_ui_dic.user_id.setValue( LocalCacheData.getLoginUser().id );
			this.edit_view_ui_dic.user_id.setEnabled( false );
		}

		this.getLegalEntity();
		this.setEditViewDataDone();
	}

	setEditViewDataDone() {
		super.setEditViewDataDone();
		this.getTypeOptions();
		this.onTypeChange();
		this.getRemittanceSourceAccount();
		this.edit_view_ui_dic.legal_entity_id.setEnabled( false );
	}

	getTypeOptions() {
		var $this = this;

		var params = {};
		if ( this.is_mass_editing ) {
			params.company_id = LocalCacheData.getLoginUser().company_id;
		} else {
			params.legal_entity_id = this.current_edit_record['legal_entity_id'];
		}

		this.api.getOptions( 'type', params, {
			async: false,
			onResult: function( res ) {

				var result = res.getResult();
				if ( !result ) {
					result = [];
				}
				$this.type_array = Global.buildRecordArray( result );

				$this.edit_view_ui_dic['type_id'].setSourceData( $this.type_array );
				if ( $this.current_edit_record['type_id'] && result[$this.current_edit_record['type_id']] ) {
					$this.edit_view_ui_dic['type_id'].setValue( $this.current_edit_record['type_id'] );
				} else {
					$this.current_edit_record['type_id'] = $this.edit_view_ui_dic['type_id'].getValue();
				}

				$this.onTypeChange( $this.current_edit_record['type_id'] );
			}
		} );
	}

	getLegalEntity() {
		var $this = this;

		if ( this.edit_view_ui_dic && this.edit_view_ui_dic['user_id'] && this.edit_view_ui_dic['user_id'].getValue() != TTUUID.zero_id ) {
			var user_id = this.edit_view_ui_dic['user_id'].getValue();
			if ( !Global.isSet( user_id ) || Global.isFalseOrNull( user_id ) ) {
				user_id = this.current_edit_record['user_id'];
			}

			var user_args = {};
			user_args.filter_data = {};
			user_args.filter_columns = {
				id: true,
				legal_entity_id: true,
				currency_id: true
			};
			user_args.filter_data.id = user_id;
			this.user_api.getUser( user_args, {
				async: false, onResult: function( res ) {
					if ( res && res.isValid() ) {
						var result = res.getResult()[0];
						if ( Global.isSet( result.legal_entity_id ) && result.legal_entity_id !== 0 ) {
							$this.current_edit_record['legal_entity_id'] = result.legal_entity_id;
							$this.edit_view_ui_dic.legal_entity_id.setValue( result.legal_entity_id );
						}
						if ( Global.isSet( result.currency_id ) && !Global.isSet( $this.current_edit_record['currency_id'] ) ) {
							$this.current_edit_record['currency_id'] = result.currency_id;
							$this.edit_view_ui_dic.currency_id.setValue( result.currency_id );
						}

					}
				}
			} );

			if ( !Global.isSet( this.current_edit_record['legal_entity_id'] ) || Global.isFalseOrNull( this.current_edit_record['legal_entity_id'] ) ) {
				this.user_default_api['get' + this.user_default_api.key_name]( {
					async: false, onResult: function( res ) {
						var result = res.getResult();
						$this.current_edit_record['legal_entity_id'] = result[0]['legal_entity_id'];
						$this.edit_view_ui_dic.legal_entity_id.setValue( result[0]['legal_entity_id'] );
					}
				} );
			}
		} else {
			$this.current_edit_record['legal_entity_id'] = TTUUID.zero_id;
			$this.edit_view_ui_dic.legal_entity_id.setValue( TTUUID.zero_id );
		}
	}

	getRemittanceSourceAccount() {

		var $this = this;

		var type_id = this.edit_view_ui_dic['type_id'].getValue();
		var legal_entity_id = this.edit_view_ui_dic['legal_entity_id'].getValue();

		if ( !Global.isSet( type_id ) || Global.isFalseOrNull( type_id ) ) {
			type_id = this.current_edit_record['type_id'];
		}

		var source_account_args = {};
		source_account_args.filter_data = {};
		source_account_args.filter_data.type_id = type_id;

		if ( this.is_mass_editing ) {
			source_account_args.filter_data.company_id = [LocalCacheData.getLoginUser().company_id, TTUUID.not_exist_id];
		} else {
			source_account_args.filter_data.legal_entity_id = [legal_entity_id, TTUUID.not_exist_id];
		}

		$this.edit_view_ui_dic['remittance_source_account_id'].setValue( 0 );
		$this.edit_view_ui_dic['remittance_source_account_id'].setSourceData( null );
		$this.edit_view_ui_dic['remittance_source_account_id'].setDefaultArgs( source_account_args );

		this.remittance_source_account_api.getRemittanceSourceAccount( source_account_args, {
			async: false, onResult: function( res ) {
				var result = res.getResult();
				if ( !result ) {
					result = [];
				}
				$this.remittance_source_account_array = result;
				$this.edit_view_ui_dic['remittance_source_account_id'].setSourceData( $this.remittance_source_account_array );

				var key = false;
				for ( var index in result ) {
					if ( !result.hasOwnProperty( index ) ) {
						continue;
					}
					if ( result[index].id != TTUUID.zero_id && result[index].id != TTUUID.not_exist_id && !key ) {
						key = index;
					}

					if ( $this.current_edit_record['remittance_source_account_id'] ) {
						if ( $this.current_edit_record['remittance_source_account_id'] == result[index].id ) {
							$this.edit_view_ui_dic['remittance_source_account_id'].setValue( $this.current_edit_record['remittance_source_account_id'] );
						}
					}

				}

				if ( typeof result == 'object' && !$this.current_edit_record.id ) {
					if ( $this.is_first_load || $this.current_edit_record.type_id == 0 ) {
						$this.is_first_load = false;
						if ( $this.edit_view_ui_dic['remittance_source_account_id'].getValue() !== 0 && Global.isFalseOrNull( $this.edit_view_ui_dic['remittance_source_account_id'].getValue() ) ) {
							$this.edit_view_ui_dic['remittance_source_account_id'].setValue( result[key].id );
						}
					} else {
						if ( Global.isFalseOrNull( $this.edit_view_ui_dic['remittance_source_account_id'].getValue() ) ) {
							$this.edit_view_ui_dic['remittance_source_account_id'].setValue( result[key].id );
						}
					}
				}

				$this.onTypeChange();
			}
		} );

		$this.current_edit_record['remittance_source_account_id'] = $this.edit_view_ui_dic['remittance_source_account_id'].getValue();
	}

	uniformVariable( record ) {
		//ensure that the variable variable fields are set to false if they aren't showing.
		if ( this.edit_view_ui_dic && this.current_edit_record.remittance_source_account_id != TTUUID.zero_id ) { //Keep accountd data if UUID == zero_id
			for ( var i = 1; i <= 10; i++ ) {
				if ( i == 1 ) {
					if ( this.edit_view_ui_dic['value1_1'] && this.edit_view_ui_dic['value1_1'].is( ':visible' ) ) {
						record['value1'] = record['value1_1'] ? record['value1_1'] : this.edit_view_ui_dic['value1_1'].getValue();
					} else if ( this.edit_view_ui_dic['value1_2'] && this.edit_view_ui_dic['value1_2'].is( ':visible' ) ) {
						record['value1'] = record['value1_2'] ? record['value1_2'] : this.edit_view_ui_dic['value1_2'].getValue();
					} else {
						if ( !this.is_mass_editing ) {
							record['value1'] = false;
						}
					}

				} else {
					if ( !this.is_mass_editing && record['value' + i] && ( typeof this.edit_view_ui_dic['value' + i] == 'undefined' || ( this.edit_view_ui_dic['value' + i] && this.edit_view_ui_dic['value' + i].is( ':visible' ) == false ) ) ) {
						record['value' + i] = false;
					}
				}
			}
		}

		if ( !this.is_mass_editing ) {
			record.legal_entity_id = this.current_edit_record['legal_entity_id'];
		}

		return record;
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			exclude: [],
			include: [{
				label: $.i18n._( 'Import' ),
				id: 'import_icon',
				menu_align: 'right',
				action_group: 'import_export',
				group: 'other',
				vue_icon: 'tticon tticon-file_download_black_24dp',
				permission_result: PermissionManager.checkTopLevelPermission( 'ImportCSVEmployeeBankAccount' ),
				permission: null,
				sort_order: 9010
			}]
		};

		return context_menu_model;
	}

	setDefaultMenuImportIcon( context_btn, grid_selected_length, pId ) {
		if ( PermissionManager.checkTopLevelPermission( 'ImportCSVEmployeeBankAccount' ) === true ) {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, true );
		} else {
			ContextMenuManager.hideMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, false );
		}
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'import_icon':
				this.onImportClick();
				break;
		}
	}

	onImportClick() {
		var $this = this;

		IndexViewController.openWizard( 'ImportCSVWizard', 'RemittanceDestinationAccount', function() {
			$this.search();
		} );
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
			case 'remittance_source_account_id':
			case 'country':
			case 'type_id':
				this.onTypeChange( c_value );
				break;
			case 'user_id':
				this.getLegalEntity();
				this.getTypeOptions();
				this.edit_view_ui_dic['currency_id'].setValue( this.current_edit_record['currency_id'] );
				break;
			case 'amount_type_id':
				this.onAmountTypeChange( c_value );
				break;
		}

		//below the switch to ensure that fields are populated first.
		if ( key === 'type_id' || key === 'user_id' ) {
			this.getRemittanceSourceAccount();
		}
		this.current_edit_record[key] = c_value;

		if ( !doNotValidate ) {
			this.validate();
		}
	}

	onTypeChange( arg ) {

		var $this = this;
		if ( !Global.isSet( arg ) || Global.isFalseOrNull( arg ) ) {

			if ( !Global.isSet( this.current_edit_record['type_id'] ) || Global.isFalseOrNull( this.current_edit_record['type_id'] ) ) {
				this.current_edit_record['type_id'] = 2000;
			}

			arg = this.current_edit_record['type_id'];
		}

		this.detachElement( 'value1_1' );
		this.detachElement( 'value1_2' );
		this.detachElement( 'value2' );
		this.detachElement( 'value3' ); //ALWAYS STORE ACCOUNT HERE. We encrypt this field.
		this.detachElement( 'value4' );
		this.detachElement( 'value5' );
		this.detachElement( 'value6' );
		this.detachElement( 'value7' );
		this.detachElement( 'value8' );
		this.detachElement( 'value9' );
		this.detachElement( 'value10' );

		var country = null;
		if ( this.edit_view_ui_dic.type_id.getValue() == 3000 && this.edit_view_ui_dic.remittance_source_account_id.getValue() != TTUUID.zero_id ) {
			if ( this.edit_view_ui_dic.remittance_source_account_id.getValue() ) {
				let rsa_id = this.edit_view_ui_dic.remittance_source_account_id.getValue();
				if ( this.remittance_source_account_country_map[rsa_id] ) {
					country = this.remittance_source_account_country_map[rsa_id];
				} else {
					country = this.remittance_source_account_api.getRemittanceSourceAccount( { filter_data: { id: rsa_id } }, { async: false } ).getResult()[0].country;
					this.remittance_source_account_country_map[rsa_id] = country;
				}
			}

			if ( this.is_mass_editing == true ) {
				this.attachElement( 'value2' ).text( $.i18n._( 'Transit/Routing' ) );
			} else if ( !this.is_mass_editing && country != null ) {
				if ( country == 'US' ) {
					this.attachElement( 'value1_2' ).text( $.i18n._( 'Account Type' ) );
					this.attachElement( 'value2' ).text( $.i18n._( 'Routing' ) );
					this.attachElement( 'value3' ).text( $.i18n._( 'Account' ) );
					if ( Global.isFalseOrNull( this.current_edit_record['value1'] ) ) {
						this.current_edit_record['value1'] = this.edit_view_ui_dic['value1_2'].getValue();
						this.current_edit_record['value1_2'] = this.edit_view_ui_dic['value1_2'].getValue();
					}
				} else if ( country == 'CA' ) {
					this.attachElement( 'value1_1' ).text( $.i18n._( 'Institution' ) );
					this.attachElement( 'value2' ).text( $.i18n._( 'Bank Transit' ) );
					this.attachElement( 'value3' ).text( $.i18n._( 'Account' ) );
					this.current_edit_record['value1'] = this.edit_view_ui_dic['value1_1'].getValue();
					this.current_edit_record['value1_1'] = this.edit_view_ui_dic['value1_1'].getValue();
				// Carribbean is now handled like US, as some banks use insitition/branch, and some use routing numbers. We can always obtain institution/branch from routing number, so use it as the common denominator.
				// } else if ( $.inArray( country, ['AG', 'BS', 'BB', 'BZ', 'DO', 'GY', 'HT', 'JM', 'DM', 'GD', 'KN', 'LC', 'VC', 'SR', 'TT'] ) != -1 ) { //Carribbean countries.
				// 	this.attachElement( 'value1_1' ).text( $.i18n._( 'Institution' ) );
				// 	this.attachElement( 'value2' ).text( $.i18n._( 'Bank Transit' ) );
				// 	this.attachElement( 'value3' ).text( $.i18n._( 'Account' ) );
				// 	this.current_edit_record['value1'] = this.edit_view_ui_dic['value1_1'].getValue();
				// 	this.current_edit_record['value1_1'] = this.edit_view_ui_dic['value1_1'].getValue();
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

		this.editFieldResize();
	}

	onAmountTypeChange( arg ) {
		var $this = this;
		if ( !Global.isSet( arg ) || Global.isFalseOrNull( arg ) ) {

			if ( !Global.isSet( this.current_edit_record['amount_type_id'] ) || Global.isFalseOrNull( this.current_edit_record['amount_type_id'] ) ) {
				this.current_edit_record['amount_type_id'] = 10;
			}

			arg = this.current_edit_record['amount_type_id'];
		}

		this.detachElement( 'amount' );
		this.detachElement( 'percent_amount' );

		if ( arg == 10 || arg == 15) {
			this.attachElement( 'percent_amount' );
		} else if ( arg == 20 ) {
			this.attachElement( 'amount' );
		}

		this.editFieldResize();
	}

	buildEditViewUI() {

		super.buildEditViewUI();
		var $this = this;

		var tab_model = {
			'tab_remittance_destination_account': { 'label': $.i18n._( 'Pay Methods' ) },
			'tab_attachment': true,
			'tab_audit': true,
		};
		this.setTabModel( tab_model );

		this.navigation.AComboBox( {
			api_class: TTAPI.APIRemittanceDestinationAccount,
			id: this.script_name + '_navigation',
			allow_multiple_selection: false,
			layout_name: 'global_remittance_destination_account',
			navigation_mode: true,
			show_search_inputs: true
		} );

		this.setNavigation();

		//Tab 0 start
		var tab_remittance_destination_account = this.edit_view_tab.find( '#tab_remittance_destination_account' );
		var tab_remittance_destination_account_column1 = tab_remittance_destination_account.find( '.first-column' );
		this.edit_view_tabs[0] = [];
		this.edit_view_tabs[0].push( tab_remittance_destination_account_column1 );

		// Legal entity
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APILegalEntity,
			allow_multiple_selection: false,
			layout_name: 'global_legal_entity',
			field: 'legal_entity_id',
			set_empty: true,
			show_search_inputs: true
		} );
		this.addEditFieldToColumn( $.i18n._( 'Legal Entity' ), form_item_input, tab_remittance_destination_account_column1, '' );

		// Employee
		form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIUser,
			allow_multiple_selection: false,
			layout_name: 'global_user',
			field: 'user_id',
			set_empty: true, //Must be true for when administrators add payment methods under Employee -> Payment Methods.
			show_search_inputs: true
		} );

		var default_args = {};
		default_args.permission_section = 'remittance_destination_account';
		form_item_input.setDefaultArgs( default_args );

		this.addEditFieldToColumn( $.i18n._( 'Employee' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

		//Status
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'status_id' } );
		form_item_input.setSourceData( $this.status_array );
		this.addEditFieldToColumn( $.i18n._( 'Status' ), form_item_input, tab_remittance_destination_account_column1, '' );

		// Name
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'name', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Name' ), form_item_input, tab_remittance_destination_account_column1 );
		form_item_input.parent().width( '45%' );

		// Description
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_AREA );
		form_item_input.TTextArea( { field: 'description', width: '100%' } );
		this.addEditFieldToColumn( $.i18n._( 'Description' ), form_item_input, tab_remittance_destination_account_column1 );
		form_item_input.parent().width( '45%' );

		//TYPE
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'type_id' } );
		form_item_input.setSourceData( $this.type_array );
		this.addEditFieldToColumn( $.i18n._( 'Type' ), form_item_input, tab_remittance_destination_account_column1, '' );

		// Remittance Source Account
		var form_item_input = Global.loadWidgetByName( FormItemType.AWESOME_BOX );
		form_item_input.AComboBox( {
			api_class: TTAPI.APIRemittanceSourceAccount,
			allow_multiple_selection: false,
			layout_name: 'global_remittance_source_account',
			field: 'remittance_source_account_id',
			set_empty: true,
			show_search_inputs: true
		} );

		this.addEditFieldToColumn( $.i18n._( 'Remittance Source Account' ), form_item_input, tab_remittance_destination_account_column1, '' );

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
		this.addEditFieldToColumn( $.i18n._( 'Currency' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

		// Priority
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'priority' } );
		form_item_input.setSourceData( $this.priority_array );
		this.addEditFieldToColumn( $.i18n._( 'Priority' ), form_item_input, tab_remittance_destination_account_column1, '' );

		// Amount TYPE
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'amount_type_id' } );
		form_item_input.setSourceData( $this.amount_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Amount Type' ), form_item_input, tab_remittance_destination_account_column1, '' );

		//Amount
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'amount', width: 50 } );
		this.addEditFieldToColumn( $.i18n._( 'Amount' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

		//Percent
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
		form_item_input.TTextInput( { field: 'percent_amount', width: 79 } );
		this.addEditFieldToColumn( $.i18n._( 'Percent' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

		if ( this.is_mass_editing ) {
			// Value2
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value2', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value2' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );
		} else { //Never show these fields for mass edit as they should never be mass editable.
			// Value1
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value1_1', validation_field: 'value1', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value1' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox( { field: 'value1_2', validation_field: 'value1' } );
			form_item_input.setSourceData( $this.ach_transaction_type_array );
			this.addEditFieldToColumn( $.i18n._( 'Account Type' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// Value2
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value2', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value2' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// Value3
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value3', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value3' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// Value4
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value4', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value4' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// Value5
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value5', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value5' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// the below are all non-display

			// Value6
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value6', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value6' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// Value7
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value7', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value7' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// Value8
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value8', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value8' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// Value9
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value9', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value9' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );

			// Value10
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput( { field: 'value10', width: 149 } );
			this.addEditFieldToColumn( $.i18n._( 'Value10' ), form_item_input, tab_remittance_destination_account_column1, '', null, true );
		}
	}

	buildSearchFields() {

		super.buildSearchFields();
		this.search_fields = [
			new SearchField( {
				label: $.i18n._( 'Legal Entity' ),
				in_column: 1,
				field: 'legal_entity_id',
				api_class: TTAPI.APILegalEntity,
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_legal_entity',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Employee' ),
				in_column: 1,
				field: 'user_id',
				api_class: TTAPI.APIUser,
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_user',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Source Account' ),
				in_column: 1,
				field: 'remittance_source_account_id',
				api_class: TTAPI.APIRemittanceSourceAccount,
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_remittance_source_account',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Status' ),
				in_column: 2,
				field: 'status_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Type' ),
				in_column: 2,
				field: 'type_id',
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_option_column',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Currency' ),
				in_column: 2,
				field: 'currency_id',
				api_class: TTAPI.APICurrency,
				multiple: true,
				basic_search: true,
				adv_search: true,
				layout_name: 'global_currency',
				form_item_type: FormItemType.AWESOME_BOX
			} ),
			new SearchField( {
				label: $.i18n._( 'Transit/Routing' ),
				in_column: 3,
				field: 'value2',
				basic_search: false,
				adv_search: true,
				form_item_type: FormItemType.TEXT_INPUT
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

	searchDone() {
		super.searchDone();
		TTPromise.resolve( 'PaymentMethodsView', 'init' );
	}
}

RemittanceDestinationAccountViewController.loadSubView = function( container, beforeViewLoadedFun, afterViewLoadedFun ) {

	Global.loadViewSource( 'RemittanceDestinationAccount', 'SubRemittanceDestinationAccountView.html', function( result ) {
		var args = {};
		var template = _.template( result, args );

		if ( Global.isSet( beforeViewLoadedFun ) ) {
			beforeViewLoadedFun();
		}

		if ( Global.isSet( container ) ) {
			container.html( template );
			if ( Global.isSet( afterViewLoadedFun ) ) {
				afterViewLoadedFun( sub_remittance_destination_account_view_controller );
			}
		}
	} );
};