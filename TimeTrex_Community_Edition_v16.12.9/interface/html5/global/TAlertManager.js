export var TAlertManager = ( function() {

	var view = null;

	var isShownNetworkAlert = false;

	var closeBrowserBanner = function() {
		$( '.browser-banner' ).remove();
	};

	var showBrowserTopBanner = function() {
		// if ( ie && ie <= 11 ) {
		// 	var div = $( '<div class="browser-banner"><a href="https://www.timetrex.com/supported-web-browsers" target="_blank"><span id="browser-top-banner" class="label"><strong>WARNING</strong>: ' + LocalCacheData.getLoginData().application_name + ' will no longer support <strong>Internet Explorer 11</strong> effective <strong>January 14th, 2020</strong>.<br><strong>Please upgrade to Microsoft Edge, Chrome or FireFox immediately to continue using TimeTrex.</strong></span></a></div>' );
		// 	$( 'body' ).append( div );
		// }
	};

	var showNetworkErrorAlert = function( jqXHR, textStatus, errorThrown ) {
		//#2514 - status 0 is caused by browser cancelling the request. There is no status because there was no request.
		if ( jqXHR.status == 0 ) {
			if ( APIGlobal.pre_login_data.production !== true ) {
				console.error( 'Browser cancelled request... jqXHR: Status=0' );
			}
			return;
		}

		if ( textStatus == 'parsererror' ) {
			Global.sendErrorReport( textStatus + ' (' + jqXHR.status + '): "' + errorThrown + '" FROM TAlertManager::showNetworkErrorAlert():\n\n' + ( jqXHR.responseText ? jqXHR.responseText : 'N/A' ), false, false, jqXHR );
			return;
		}

		if ( !isShownNetworkAlert ) {
			TAlertManager.showAlert( Global.network_lost_msg + '<br><br>' + 'Error: ' + textStatus + ' (' + jqXHR.status + '): <br>"' + errorThrown + '"' + '<br><hr>' + ( jqXHR.responseText ? jqXHR.responseText : 'N/A' ) + ' (' + jqXHR.status + ')', 'Error', function() {
				isShownNetworkAlert = false;
			} );
			isShownNetworkAlert = true;
			Global.sendAnalyticsEvent( 'alert-manager', 'error:network', 'network-error: jqXHR-status: ' + jqXHR.status + ' Error: ' + textStatus );
		}
	};

	var showPreSessionAlert = function() {
		var result = $( '<div class="session-alert"> ' +
			'<span class="close-icon">X</span>' +
			'<span class="content"></span>' +
			'</div>' );
		setTimeout( function() {
			$( 'body' ).append( result );
			result.find( '.content' ).html( $.i18n._( 'Previous Session' ) );
			var button = result.find( '.close-icon' );

			button.bind( 'click', Global.debounce( function ConfirmRemovePreSession( e ) {
				removePreSession();
			}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );

			result.bind( 'click', Global.debounce( function ConfirmBackToPreSession( e ) {
				backToPreSession();
			}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );
		}, 100 );

		function removePreSession() {
			result.remove();
			result = null;

			deleteCookie( 'AlternateSessionData', LocalCacheData.cookie_path, Global.getHost() );
		}

		function backToPreSession() {
			var host = Global.getHost();
			try { //Prevent JS exception if we can't parse alternate_session_data for some reason.
				var alternate_session_data = JSON.parse( getCookie( 'AlternateSessionData' ) );
				if ( !alternate_session_data ) {
					Debug.Text( 'No alternate_session_data exists.', 'TAlertManager.js', 'TAlertManager', 'backToPreSession', 10 );
					return;
				}
			} catch ( e ) {
				Debug.Text( e.message, 'TAlertManager.js', 'showPreSessionAlert', 'backToPreSession', 10 );
				return;
			}

			var url = alternate_session_data.previous_session_url;
			var previous_cookie_path = alternate_session_data.previous_cookie_path;

			alternate_session_data = {
				new_session_id: alternate_session_data.previous_session_id,
				previous_session_view: alternate_session_data.previous_session_view
			};

			setCookie( 'AlternateSessionData', JSON.stringify( alternate_session_data ), 1, previous_cookie_path, host );

			Global.setURLToBrowser( url + '#!m=Login' );
			Global.needReloadBrowser = true;

			result.remove();
			result = null;
		}
	};

	var showErrorAlert = function( result ) {
		var details = result.getDetails();

		if ( details.hasOwnProperty( 'error' ) ) {

		}
		if ( !details ) {
			details = result.getDescription(); // If the details is empty, try to get description to show.
		}
		var error_string = '';

		if ( Global.isArray( details ) || typeof details === 'object' ) {
			error_string = Global.convertValidationErrorToString( details );
		} else {

			error_string = details;
		}

		showAlert( error_string, 'Error' );

	};

	var showWarningAlert = function( result, callBack ) {
		var details = result.getDetails();
		var ul_container = $( '<ol>' );
		if ( Global.isArray( details ) || typeof details === 'object' ) {
			$.each( details, function( index, val ) {
				if ( val.hasOwnProperty( 'warning' ) ) {
					val = val.warning;
				}
				for ( var key in val ) {
					var li = $( '<li>' );
					var child_val = val[key];
					var has_child = false;
					for ( var child_key in child_val ) {
						if ( child_val.hasOwnProperty( child_key ) ) {
							has_child = true;
							li = $( '<li>' );
							li.append( child_val[child_key] );
							ul_container.append( li );
						}
					}
					if ( !has_child ) {
						li.append( val[key] );
						ul_container.append( li );
					}
				}
			} );
		}
		var div = $( '<div>' );
		var p = $( '<p>' );
		p.append( $.i18n._( 'Are you sure you wish to save this record without correcting the above warnings?' ) );
		div.append( ul_container );
		div.append( p );
		showConfirmAlert( div[0], $.i18n._( 'Warning' ), callBack, $.i18n._( 'Save' ), $.i18n._( 'Cancel' ) );
	};

	var showAlert = function( content, title, callBack ) {
		if ( !title ) {
			title = $.i18n._( 'Message' );
		}

		var result = $( '<div class="t-alert">' +
			'<div class="content-div"><span class="content"></span></div>' +
			'<span class="title"></span>' +
			'<div class="bottom-bar">' +
			'<button class="t-button" id="t-alert-close">Close</button>' +
			'</div>' +
			'</div>' );
		setTimeout( function() {
			if ( view !== null ) {

				var cContent = view.find( '.content' ).text();

				if ( cContent === content ) {
					return;
				}

				remove();

			}
			view = result;
			$( 'body' ).append( result );
			result.find( '.title' ).text( title );
			result.find( '.content' ).html( content );
			var button = result.find( '.t-button' );
			button.bind( 'click', Global.debounce( function ConfirmClose( e ) {
				remove();
				if ( callBack ) {
					callBack();
				}
			}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );
			button.focus();
			button.bind( 'keydown', function( e ) {
				e.stopPropagation();
				if ( e.keyCode === 13 ) {
					remove();
					if ( callBack ) {
						callBack();
					}
				}
			} );

			Global.setUIInitComplete();
		}, 100 );

	};

	var showConfirmAlert = function( content, title, callBackFunction, yesLabel, noLabel ) {

		if ( !Global.isSet( title ) ) {
			title = $.i18n._( 'Message' );
		}

		if ( !Global.isSet( yesLabel ) ) {
			yesLabel = $.i18n._( 'Yes' );
		}

		if ( !Global.isSet( noLabel ) ) {
			noLabel = $.i18n._( 'No' );
		}

		if ( view !== null ) {

			var cContent = view.find( '.content' ).text();

			if ( cContent === content ) {
				return;
			}

			remove();
		}
		var result = $( '<div class="confirm-alert"> ' +
			'<div class="content-div"><span class="content"></span></div>' +
			'<span class="title"></span>' +
			'<div class="bottom-bar">' +
			'<button id="yesBtn" class="t-button bottom-bar-yes-btn"></button>' +
			'<button id="noBtn" class="t-button"></button>' +
			'</div>' +
			'</div>' );
		view = result;
		$( 'body' ).append( result );

		result.find( '#yesBtn' ).text( yesLabel );
		result.find( '#noBtn' ).text( noLabel );
		result.find( '.title' ).text( title );

		result.find( '.content' ).html( content );

		result.find( '#yesBtn' ).bind( 'click', Global.debounce( function AcceptConfirm( e ) {
			remove();
			callBackFunction( true );

		}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );

		result.find( '#noBtn' ).bind( 'click', Global.debounce( function DeclineConfirm( e ) {
			remove();
			callBackFunction( false );

		}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );

		Global.setUIInitComplete();
	};

	var showFlexAlert = function( title, content, form_type, form_data, callBackFunction, width, continue_label, cancel_label ) {
		Global.setUINotready();

		var show_continue_button = true;
		var show_cancel_button = true;

		if ( !continue_label ) {
			continue_label = $.i18n._( 'Continue' );
		}

		if ( !cancel_label ) {
			cancel_label = $.i18n._( 'Cancel' );
		}

		var result = $( '<div class="confirm-alert"> ' +
			'<div><span class="content"></span></div>' +
			'<span class="title"></span>' +
			'<div style="margin-top: 2rem; margin-bottom: 2rem" id="form-holder"></div>' +
			'<div style="margin-bottom: 1rem" class="bottom-bar">' +
			'<button id="yesBtn" class="t-button bottom-bar-yes-btn"></button>' +
			'<button id="noBtn" class="t-button"></button>' +
			'</div>' +
			'</div>' )

		if ( width ) {
			result.css( 'width', width );
		}

		view = result;
		$( 'body' ).append( result );

		if ( form_type === 'dropdown' ) {
			var form_item_input = Global.loadWidgetByName( FormItemType.COMBO_BOX );
			form_item_input.TComboBox();
			form_item_input.setSourceData( form_data );

			result.find( '#yesBtn' ).bind( 'click', Global.debounce( function AcceptConfirmDropdown( e ) {
				remove();
				callBackFunction( result.find( 'select' ).val() );
			}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );
		} else if ( form_type === 'password' ) {
			form_item_input = Global.loadWidgetByName( FormItemType.PASSWORD_INPUT );
			form_item_input.TPasswordInput();

			result.find( '#yesBtn' ).bind( 'click', Global.debounce( function AcceptConfirmPassword( e ) {
				remove();
				callBackFunction( result.find( '[type=password]' ).val() );
			}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );
		} else if ( form_type === 'text' ) {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput();
			form_item_input.setValue( form_data );

			if ( width ) {
				form_item_input.width( ( width - 20 ) );
			}

			show_cancel_button = false;

			result.find( '#yesBtn' ).bind( 'click', Global.debounce( function AcceptConfirmText( e ) {
				remove();
			}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );
		} else if ( form_type === 'word_match' ) {
			form_item_input = Global.loadWidgetByName( FormItemType.TEXT_INPUT );
			form_item_input.TTextInput();

			result.find( '#yesBtn' ).bind( 'click', Global.debounce( function AcceptConfirmWordMatch( e ) {
				if ( result.find( '[type=text]' ).val() === form_data ) {
					remove();
					callBackFunction( true );
				} else {
					callBackFunction( false );
				}
			}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );
		}

		result.find( '#form-holder' ).append( form_item_input );

		result.find( '#noBtn' ).bind( 'click', Global.debounce( function DeclineConfirm( e ) {
			remove();
			callBackFunction( false );
		}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );

		result.find( '#yesBtn' ).text( continue_label );
		result.find( '#noBtn' ).text( cancel_label );

		result.find( '.title' ).text( title );
		result.find( '.content' ).html( content );

		if ( show_continue_button == false ) {
			result.find( '#yesBtn' ).hide();
		}

		if ( show_cancel_button == false ) {
			result.find( '#noBtn' ).hide();
		}

		Global.setUIInitComplete();
	};


	var showModalAlert = function( category, step, callBackFunction, img_src ) {
		let top_image = '';
		let title = '';
		let content = '';
		let additional_body_style = '';
		let additional_modal_style = '';
		let button_label = $.i18n._( 'Yes' );
		let button_color = '#228b22';
		let button_disabled = false;
		let creation_callback = null;

		if ( category === 'push_notification' ) {
			top_image = '<img class ="modal-alert-image" src ="' + Global.getRealImagePath( 'images/bell_permissions.svg' ) + '">';
			title = $.i18n._( 'Turn on Notifications' );

			switch ( step ) {
				case 'ask':
					button_label = $.i18n._( 'Yes, turn on notifications!' );
					content = LocalCacheData.getCurrentCompany().name + ' ' + $.i18n._( 'wants permission to notify you of important messages or alerts related to your employment. You can change your notification settings at anytime.' );
					break;
				case 'wait_for_permission':
					button_label = $.i18n._( 'I don\'t see it?' );
					button_color = '#AE0000';
					// Some browsers by default block push notification permission we need to detect them to show user a different prompt.
					if ( NotificationConsumer.detectBrowserNeedsExtraPermission() === true || Notification.permission === 'denied' ) {
						// User needs to enable push notifications on the browser.
						content = $.i18n._( 'A popup should appear on your screen, click "ALLOW" to enable notifications. If you don\'t see it, you may need to enable notifications in your browser settings.' );
					} else {
						content = $.i18n._( 'A popup should appear on your screen, click "ALLOW" to enable notifications.' );
					}
					break;
				case 'help_text':
					button_label = $.i18n._( 'Ok, done!' );
					if ( Global.getBrowserVendor() === 'Edge' ) {
						content = $.i18n._( '1. Click the icon to the left of the address (URL) bar to view settings.<br>' +
							'2. Click "Permissions for this Site"<br>' +
							'3. To the right of "Notifications" set the option to "ALLOW".' );
					} else if ( Global.getBrowserVendor() === 'Firefox' ) {
						content = $.i18n._( '1. Click the icon to the left of the address (URL) bar to view settings.<br>' +
							'2. Click the "X" next Notifications to remove blocked permissions and then refresh the browser.<br>' );
					} else {
						content = $.i18n._( '1. Click the icon to the left of the address (URL) bar to view settings.<br>' +
							'2. Click "Site settings"<br>' +
							'3. To the right of "Notifications" set the option to "ALLOW".' );
					}
					break;
			}
		} else if ( category === 'multifactor_authentication' ) {
			title = $.i18n._( 'Multifactor Authentication Instructions' );

			switch ( step ) {
				case 'download_instructions':
					button_label = $.i18n._( 'Ok' );
					button_color = '#426d9d';
					additional_body_style = 'style="display: block; padding-left: 2rem; padding-right: 2rem; font-size: 1.1rem;"';

					content = '<br>';
					content += '1.' + $.i18n._( 'Please download the TimeTrex app from the App Store on your device.' ) + '<br><br>';
					content += '2.' + $.i18n._( ' Once installed, on the first step of the "Setup Wizard", tap the "QR Code" icon at the top right to scan the below QR Code.' ) + '<br>';
					content += '<img class ="modal-alert-image" style="margin-top: 10px;" src ="' + img_src + '">';
					break;
			}
		} else if ( category === 'custom_agreement' ) {
			title = $.i18n._( 'Custom Agreement' );

			switch ( step ) {
				case 'accept_terms':
					button_label = $.i18n._( 'Continue' );
					//greyed out
					button_color = '#808080';
					additional_body_style = 'style="display: block; padding-left: 2rem; padding-right: 2rem; font-size: 1.1rem;"';
					additional_modal_style = 'style="height: 38rem;"';
					button_disabled = true;

					content = '<br>';
					content += `1. ${$.i18n._( 'Please read the agreement below.' )}<br><br>
								2. ${$.i18n._( 'If you accept the terms, type "Yes, I agree" in the box below and click "Continue".' )}<br><br>
								
								<div style="margin-top: 10px; margin-bottom: 10px; padding-left: 2rem; padding-right: 2rem;">
									<textarea id="custom_agreement_text" style="width: 100%; height: 200px; resize: none;" readonly>
										Lorem ipsum dolor sit amet, consectetur adipiscing elit
										Vestibulum nec odio ipsum. Suspendisse cursus malesuada facilisis.
										Proin ut ligula vel nunc egestas porttitor. Morbi lectus risus,
									</textarea>
								</div>
								
								<div style="margin-top: 10px; padding-left: 2rem; padding-right: 2rem;">
									<input type="text" id="custom_agreement_input" style="width: 100%; height: 30px; resize: none;" placeholder="Type 'Yes, I agree' here"">
								</div>`;

					creation_callback = () => {
						$('#custom_agreement_input').on('input', function() {
							checkCustomAgreementInput(this);
						});
					}
					break;
			}
		}

		Global.setUINotready();

		var result = $( '<div class="modal-alert"> ' +
			'<div class="modal-alert-content" ' + additional_modal_style + '>' +
			'<span class="modal-alert-close">×</span>' +
			top_image +
			'<h2 class="modal-alert-title"></h2>' +
			'<div class="modal-alert-body" ' + additional_body_style + '></div>' +
			'<button type="submit" class="permission-button-yes" style="background: ' + button_color + '" ' + ( button_disabled ? 'disabled' : '' ) + '></button>' +
			'</div>' +
			'</div>' );
		view = result;
		$( 'body' ).append( result );

		result.find( '.permission-button-yes' ).text( button_label );
		result.find( '.modal-alert-title' ).text( title );

		result.find( '.modal-alert-body' ).html( content );

		result.find( '.permission-button-yes' ).bind( 'click', Global.debounce( function AcceptConfirm( e ) {
			remove();
			if ( callBackFunction ) {
				callBackFunction( true );
			}
			Global.setUIReady();
		}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );

		result.find( '.modal-alert-close' ).bind( 'click', Global.debounce( function DeclineConfirm( e ) {
			remove();
			if ( callBackFunction ) {
				callBackFunction( false );
			}
			Global.setUIReady();
		}, Global.calcDebounceWaitTimeBasedOnNetwork(), true ) );

		Global.setUIInitComplete();

		if (creation_callback) {
			creation_callback();
		}
	};

	var checkCustomAgreementInput = function( input ) {
		if ( input.value === 'Yes, I agree' ) {
			$( '.permission-button-yes' ).prop( 'disabled', false );
			$( '.permission-button-yes' ).css( 'background', '#426d9d' );
		} else {
			$( '.permission-button-yes' ).prop( 'disabled', true );
			$( '.permission-button-yes' ).css( 'background', '#808080' );
		}
	}

	var remove = function() {

		if ( view ) {
			view.remove();
			view = null;
		}

	};

	return {
		showBrowserTopBanner: showBrowserTopBanner,
		closeBrowserBanner: closeBrowserBanner,
		showConfirmAlert: showConfirmAlert,
		showModalAlert: showModalAlert,
		showAlert: showAlert,
		showErrorAlert: showErrorAlert,
		showPreSessionAlert: showPreSessionAlert,
		showFlexAlert: showFlexAlert,
		showWarningAlert: showWarningAlert,
		showNetworkErrorAlert: showNetworkErrorAlert
	};

} )();
