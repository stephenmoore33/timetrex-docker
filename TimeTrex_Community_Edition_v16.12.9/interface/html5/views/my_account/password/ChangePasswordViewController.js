export class ChangePasswordViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			showPassword: null,

			showPhonePassword: null,
			mfa_type_array: null,
			has_authenticated: false,
			api_authentication: null,
			api_misc: null,
			api_notification_device_token: null,

			result_data: []
		} );

		super( options );
	}

	init( options ) {

		//this._super('initialize', options );

		this.permission_id = 'user';
		this.viewId = 'ChangePassword';
		this.script_name = 'ChangePasswordView';
		this.context_menu_name = $.i18n._( 'Passwords / Security' );
		this.api = TTAPI.APIUser;
		this.api_authentication = TTAPI.APIAuthentication;
		this.api_notification_device_token = TTAPI.APINotificationDeviceToken;
		this.api_misc = TTAPI.APIMisc;

		this.initPermission();

		this.render();

		this.initData();
	}

	initOptions( callback ) {
		var options = [{ option_name: 'mfa_type' },];

		this.initDropDownOptions( options, callback );
	}

	initPermission() {
		super.initPermission();

		if ( PermissionManager.validate( 'user', 'edit_own_password' ) ) {
			this.showPassword = true;
		} else {
			this.showPassword = false;
		}

		if ( PermissionManager.validate( 'user', 'edit_own_phone_password' ) ) {
			this.showPhonePassword = true;
		} else {
			this.showPhonePassword = false;
		}
	}

	render() {
		super.render();
	}

	getCustomContextMenuModel() {
		var context_menu_model = {
			groups: {
				trusted: {
					label: $.i18n._( 'Trusted Device' ), id: this.viewId + 'trusted', sort_order: 8000
				}
			},
			exclude: ['default'],
			include: [
				'save',
				'cancel',
				{
					label: '', //Empty label. vue_icon is displayed instead of text.
					id: 'other_header',
					menu_align: 'right',
					action_group: 'other',
					action_group_header: true,
					vue_icon: 'tticon tticon-more_vert_black_24dp',
				},
				{
					label: $.i18n._( 'Reauthenticate' ),
					id: 'reauthenticate',
					menu_align: 'right',
					action_group: 'other'
				},
				{
					label: $.i18n._( 'Register API Key' ),
					id: 'register_api_key',
					menu_align: 'right',
					action_group: 'other'
				},
				{
					label: $.i18n._( 'Remove All Trusted Devices' ),
					id: 'remove_all_trusted_devices',
					menu_align: 'right',
					action_group: 'other'
				},
				{
					label: $.i18n._( 'Sign Out All Sessions' ),
					id: 'logout_all_sessions',
					menu_align: 'right',
					action_group: 'other'
				},
			]
		};

		return context_menu_model;
	}

	onCustomContextClick( id ) {
		switch ( id ) {
			case 'remove_all_trusted_devices':
				this.removeAllTrustedDevices();
				break;
			case 'reauthenticate':
				Global.showAuthenticationModal( this.viewId, 'user_name_multifactor', {
					step: 'password',
					type_id: 10,
					user_action_message: ''
				}, true, ( result ) => {
					Global.hideAuthenticationModal();
					if ( result.status === true ) {
						this.has_authenticated = true;
					}
				} );
				break;
			case 'register_api_key':
				this.registerAPIKey();
				break;
			case 'logout_all_sessions':
				this.logoutAllSessions();
				break;
		}
	}

	registerAPIKey() {
		this.api_authentication.registerAPIKeyForCurrentUser( {
			onResult: ( result ) => {
				if ( result && result.isValid() ) {
					var key = result.getResult();
					TAlertManager.showFlexAlert( $.i18n._( 'API Key' ), $.i18n._( 'Below is a new API key for ' ) + LocalCacheData.getLoginUser().user_name + $.i18n._( ' Please copy or write it down for safe keeping, as you will not be able to see it again after this.' ), 'text', key, null, 345, $.i18n._( 'Close' ) );
				}
			}
		} );
	}

	logoutAllSessions() {
		TAlertManager.showConfirmAlert( $.i18n._( 'Which sessions do you want to sign out? Note that your current session will not be signed out.' ), $.i18n._( 'Sign Out' ), ( flag ) => {
			this.api_authentication.logoutAllSessions( !flag, {
				onResult: ( result ) => {
					if ( result && result.isValid() ) {
						TAlertManager.showAlert( $.i18n._( 'Sessions have been logged out.' ) );
					}
				}
			} );
		}, $.i18n._( 'Browser/App' ), $.i18n._( '+API Keys' ) );
	}

	removeAllTrustedDevices() {
		this.api_authentication.removeAllTrustedDevices( {
			onResult: ( result ) => {
				TAlertManager.showAlert( $.i18n._( 'All trusted devices have been removed.' ), $.i18n._( 'Trusted Device' ) );
			}
		} );
	}

	saveValidate( context_btn, p_id ) {
		// always show
	}

	setCurrentEditRecordData() {
		//Set current edit record data to all widgets

		for ( var key in this.current_edit_record ) {
			var widget = this.edit_view_ui_dic[key];
			if ( Global.isSet( widget ) ) {
				switch ( key ) {
					case 'user_name':
						widget.setValue( LocalCacheData.loginUser.user_name );
						break;
					case 'phone_id':

						if ( !LocalCacheData.loginUser.phone_id ) {
							widget.setValue( $.i18n._( 'Not Specified' ) );
						} else {
							widget.setValue( LocalCacheData.loginUser.phone_id );
						}

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

	openEditView() {

		var $this = this;

		if ( $this.edit_only_mode && ( this.showPassword || this.showPhonePassword ) ) {

			$this.buildContextMenu();

			$this.initOptions( () => {
				if ( !$this.edit_view ) {
					$this.initEditViewUI( 'ChangePassword', 'ChangePasswordEditView.html' );
				}

				$this.getUserPasswordData( function( result ) {
					$this.current_edit_record = result;
					$this.initEditView();
				} );
			} );

		}
	}

	getUserPasswordData( callBack ) {
		var $this = this;
		var filter = {};
		filter.filter_data = {};
		filter.filter_data.id = LocalCacheData.loginUser.id;
		filter.filter_columns = { id: true, mfa_type_id: true };

		$this.api['get' + $this.api.key_name]( filter, {
			onResult: function( result ) {
				var result_data = result.getResult();
				if ( Global.isSet( result_data[0] ) ) {
					callBack( result_data[0] );
				}
			}
		} );
	}

	checkTabPermissions( tab ) {
		var retval = false;

		switch ( tab ) {
			case 'tab_web_password':
				if ( this.showPassword ) {
					retval = true;
				}
				break;
			case 'tab_quick_punch_password':
				if ( this.showPhonePassword ) {
					retval = true;
				}
				break;
			case 'tab_multifactor':
				//Current edit record is not set during initPermission, so we need to check if it's set here.
				if ( this.current_edit_record.mfa_type_id && this.current_edit_record.mfa_type_id != 1000 ) {
					retval = true;
				}
				break;
			default:
				retval = super.checkTabPermissions( tab );
				break;
		}

		return retval;
	}

	onFormItemChange( target, doNotValidate ) {

		this.setIsChanged( target );
		this.setMassEditingFieldsWhenFormChange( target );

		var key = target.getField();
		//this.current_edit_record[key] = target.getValue();
		var c_value = target.getValue();

		switch ( key ) {
			case 'mfa_type_id':
				if ( this.current_edit_record.mfa_type_id == 1000 ) {
					//This tab should never be displayed if SAML is enabled. However, if user is already on this tab when SAML is enabled, we prevent them switching MFA types.
					target.setValue( 0 ); //Do not allow dropdown to change, show disabled.
					this.current_edit_record.mfa_type_id = 1000; //Keep mfa as 1000
					TAlertManager.showAlert( $.i18n._( 'SAML is enabled, you cannot change MFA settings.' ), $.i18n._( 'Error' ) );
				} else if ( this.current_edit_record.mfa_type_id != c_value && c_value != 0 && this.current_edit_record.mfa_type_id != 0 ) {
					//setUser() API does not allow changing mfa_type_id and we need to display this error here. (Validation also occurs server side)
					target.setValue( this.current_edit_record.mfa_type_id ); //Do not allow dropdown to change, user must select disable.
					TAlertManager.showAlert( $.i18n._( 'You must disable multifactor before switching to a different type.' ), $.i18n._( 'Error' ) );
				} else {
					this.onMfaTypeChange( c_value, this.current_edit_record.mfa_type_id );
				}
				break;
			default:
				break;
		}

		this.current_edit_record[key] = c_value;
	}

	toggleSaveButton( show_button ) {
		var context_menu_array = ContextMenuManager.getMenuModelByMenuId( this.determineContextMenuMountAttributes().id );

		context_menu_array.forEach( ( context_btn ) => {
			if ( context_btn.id === 'save' ) {
				ContextMenuManager.disableMenuItem( this.determineContextMenuMountAttributes().id, context_btn.id, show_button );
			}
		} );
	}

	onMfaTypeChange( mfa_type_id, old_mfa_type_id ) {
		//Disable "Save" button as the user may quickly press it before the QR code is generated, causing them to miss instructions.
		this.toggleSaveButton( false );

		//Validate current user to make sure mfa settings can be saved
		this.api.validateUser( { id: LocalCacheData.getLoginUser().id }, {
			onResult: ( result ) => {
				if ( result && result.isValid() == false ) {
					let details = result.getDetails();
					let error_string = $.i18n._( 'Validation Error. Unable to turn on Multifactor Authentication.<br><br>' );

					if ( Global.isArray( details ) || typeof details === 'object' ) {
						error_string += Global.convertValidationErrorToString( details );
					} else {
						error_string += result.getDescription();
					}

					this.toggleSaveButton( true );
					TAlertManager.showAlert( error_string, $.i18n._( 'Error' ) );
				} else {
					if ( mfa_type_id == 0 ) { //Disabling MFA, the server will determine which MFA needs to be disabled
						this.toggleSaveButton( true );
						this.onSaveClick( false, true ); //Trigger save to instigate the disabling of authentication.
					} else if ( mfa_type_id == 10 ) { //Mobile app
						this.showMfaInstructions( true ); //Internal TimeTrex MFA
					} else if ( mfa_type_id == 100 ) { //Webauthn passkeys
						TTWebauthn.enrollCurrentUser( () => {
							this.toggleSaveButton( true );
							this.onSaveClick();
						} );
					}
				}

				//If we need to check app token exists
				// let data = {};
				// data.filter_data = { id: LocalCacheData.getLoginUser().id };
				// this.api_notification_device_token.getNotificationDeviceToken( data, {
				// 	onResult: ( res ) => {
				// 		result = res.getResult();
				// 		if ( Array.isArray( result ) && result.length > 0 ) {
				// 			this.showMfaInstructions( true );
				// 		} else {
				// 			this.showMfaInstructions( false );
				// 		}
				// 	}
				// } );
				// }
			}
		} );
	}

	showMfaInstructions( has_notification_device_token ) {
		this.api_misc.generateQRCode( JSON.stringify( {
			server_url: Global.getBaseURL( null, false ).replace( '/interface/html5/', '' ), //App does not need the /interface/html5/ part of the URL
			user_name: LocalCacheData.getLoginUser().user_name
		} ), {
			onResult: ( res ) => {
				this.toggleSaveButton( true );

				let result = res.getResult();
				TAlertManager.showModalAlert( 'multifactor_authentication', 'download_instructions', ( flag ) => {
					if ( flag === true ) {
						this.onSaveClick();
					}
				}, result );
			}
		} );
	}

	onSaveClick( ignoreWarning, disable_mfa ) {
		var $this = this;

		var record = this.current_edit_record;
		LocalCacheData.current_doing_context_action = 'save';
		if ( !Global.isSet( ignoreWarning ) ) {
			ignoreWarning = false;
		}
		if ( !Global.isSet( disable_mfa ) ) {
			disable_mfa = false;
		}
		this.clearErrorTips();

		var key = this.getEditViewTabIndex();
		if ( key === 0 ) {
			$this.saveWebPassword( record, function( result ) {
				if ( result && result.isValid() ) {
					$this.removeEditView();
				} else {
					$this.showErrorTips( result, 0 );
				}

			} );
		} else if ( key === 1 ) {
			$this.savePhonePassword( record, function( result ) {
				if ( result && result.isValid() ) {
					$this.removeEditView();
				} else {
					$this.showErrorTips( result, 1 );
				}
			} );
		} else if ( key === 2 ) {
			$this.saveMultiFactorSettings( disable_mfa, function( result ) {
				if ( result && result.isValid() ) {
					$this.removeEditView();
				} else {
					$this.showErrorTips( result, 1 );
				}
			} );
		}
	}

	showErrorTips( result, index ) {

		var details = result.getDetails();
		var error_list = details;
		var tabKey;

		var found_in_current_tab = false;
		for ( var key in error_list ) {
			if ( parseInt( index ) === 0 ) {

				if ( this.current_edit_record['web.password'] ) {
					tabKey = 'web.' + key;
				} else {
					continue;
				}

			}

			if ( parseInt( index ) === 1 ) {
				if ( this.current_edit_record['phone.password'] ) {
					tabKey = 'phone.' + key.replace( 'phone_', '' );
				} else {
					continue;
				}

			}

			if ( !error_list.hasOwnProperty( key ) ) {
				continue;
			}

			if ( !Global.isSet( this.edit_view_ui_dic[tabKey] ) ) {
				continue;
			}

			if ( this.edit_view_ui_dic[tabKey].is( ':visible' ) ) {
				this.edit_view_ui_dic[tabKey].setErrorStyle( error_list[key], true );
				found_in_current_tab = true;
			}

			this.edit_view_error_ui_dic[tabKey] = this.edit_view_ui_dic[tabKey];

		}

		if ( !found_in_current_tab ) {

			this.showEditViewError( result );

		}
	}

	saveWebPassword( record, callBack ) {
		var $this = this;
		this.api['changePassword']( record['web.password'], record['web.password2'], 'user_name', {
			onResult: function( result ) {
				callBack( result );
			}
		} );
	}

	savePhonePassword( record, callBack ) {
		var $this = this;
		this.api['changePassword']( record['phone.password'], record['phone.password2'], 'quick_punch_id', {
			onResult: function( result ) {
				callBack( result );
			}
		} );
	}

	saveMultiFactorSettings( disable_mfa, callBack ) {
		//When disabling MFA set requested mfa_type_id to 0 as server will determine which mfa method needs to be disabled.
		//We do not this.current_edit_record.mfa_type_id or the dropdown until the MFA method has been verified.
		this.api.setMultiFactorSettings( disable_mfa ? 0 : this.current_edit_record.mfa_type_id, {
			onResult: ( result ) => {
				callBack( result );
			}
		} );
	}

	buildEditViewUI() {
		var $this = this;
		super.buildEditViewUI();

		var tab_model = {
			'tab_web_password': { 'label': $.i18n._( 'Web Password' ) },
			'tab_quick_punch_password': { 'label': $.i18n._( 'Quick Punch Password' ) },
			'tab_multifactor': {
				'label': $.i18n._( 'Multifactor Authentication' ),
				init_callback: 'initMultifactorView',
				html_template: this.getMultifactorTabHtml()
			},
		};
		this.setTabModel( tab_model );

		//Tab 0 start

		var tab_web_password = this.edit_view_tab.find( '#tab_web_password' );

		var tab_web_password_column1 = tab_web_password.find( '.first-column' );

		this.edit_view_tabs[0] = [];

		this.edit_view_tabs[0].push( tab_web_password_column1 );

		// User Name
		var form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'user_name' } );
		this.addEditFieldToColumn( $.i18n._( 'User Name' ), form_item_input, tab_web_password_column1, '' );

		// New Password
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( { field: 'web.password', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'New Password' ), form_item_input, tab_web_password_column1 );

		// New Password(confirm)
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( { field: 'web.password2', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'New Password (Confirm)' ), form_item_input, tab_web_password_column1, '' );

		//Tab 1 start

		var tab_quick_punch_password = this.edit_view_tab.find( '#tab_quick_punch_password' );

		var tab_quick_punch_password_column1 = tab_quick_punch_password.find( '.first-column' );

		this.edit_view_tabs[1] = [];

		this.edit_view_tabs[1].push( tab_quick_punch_password_column1 );

		// Quick Punch ID
		form_item_input = Global.loadWidgetByName( FormItemType.TEXT );
		form_item_input.TText( { field: 'phone_id' } );
		this.addEditFieldToColumn( $.i18n._( 'Quick Punch ID' ), form_item_input, tab_quick_punch_password_column1, '' );

		// New Password
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( { field: 'phone.password', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'New Quick Punch Password' ), form_item_input, tab_quick_punch_password_column1 );

		// New Password(confirm)
		form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
		form_item_input.TPasswordInput( { field: 'phone.password2', width: 200 } );
		this.addEditFieldToColumn( $.i18n._( 'New Quick Punch Password (Confirm)' ), form_item_input, tab_quick_punch_password_column1, '' );

		//Tab 2 start

		var tab_multifactor = this.edit_view_tab.find( '#tab_multifactor' );
		var tab_multifactor_column1 = tab_multifactor.find( '.first-column' );

		this.edit_view_tabs[1] = [];
		this.edit_view_tabs[1].push( tab_multifactor_column1 );

		// Multifactor Authentication Type
		form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
		form_item_input.TComboBox( { field: 'mfa_type_id' } );
		form_item_input.setSourceData( this.mfa_type_array );
		this.addEditFieldToColumn( $.i18n._( 'Multifactor Type' ), form_item_input, tab_multifactor_column1, '' );
	}

	initMultifactorView() {
		if ( ( Global.getProductEdition() >= 15 ) ) {
			this.edit_view_tab.find( '#tab_multifactor' ).find( '.first-column' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'none' );
		} else {
			this.edit_view_tab.find( '#tab_multifactor' ).find( '.first-column' ).css( 'display', 'none' );
			this.edit_view.find( '.permission-defined-div' ).css( 'display', 'block' );
			this.edit_view.find( '.permission-message' ).html( Global.getUpgradeMessage() );
		}
	}

	getMultifactorTabHtml() {
		return `
		<div id="tab_multifactor" class="edit-view-tab-outside">
			<div class="edit-view-tab" id="tab_multifactor_content_div">
				<div class="first-column full-width-column"></div>
				<div class="save-and-continue-div permission-defined-div">
					<span class="message permission-message"></span>
				</div>
			</div>
		</div>`;
	}
}