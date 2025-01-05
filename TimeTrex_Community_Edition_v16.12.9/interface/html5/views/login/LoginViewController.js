import TTVueUtils from '@/services/TTVueUtils';
import TTlogin from '@/components/login/TTLogin';
import { Global } from '@/global/Global';
import { APIReturnHandler } from '@/model/APIReturnHandler';
import moment from 'moment';

import '@/theme/default/css/views/login/LoginView.css';

export class LoginViewController extends BaseViewController {
	constructor( options = {} ) {
		_.defaults( options, {
			el: '#loginViewContainer', //Must set el here and can only set string, so events can work

			authentication_api: null,
			currentUser_api: null,
			currency_api: null,
			user_preference_api: null,
			user_locale: 'en_US',
			is_login: true,
			date_api: null,
			permission_api: null,

			lan_selector: null,

			default: {
				is_login: false
			},
		} );

		super( options );
	}

	init( options ) {
		//this._super('initialize', options );
		var $this = this;
		Global.setVirtualDeviceViewport( 'mobile' ); // Setting mobile view on login, then back to desktop (990px virtual) after login, to allow pan & zoom, as not whole app is mobile optimized.
		this.authentication_api = TTAPI.APIAuthentication;
		this.currentUser_api = TTAPI.APIAuthentication;
		this.currency_api = TTAPI.APICurrency;
		this.user_preference_api = TTAPI.APIUserPreference;
		this.date_api = TTAPI.APITTDate;
		this.permission_api = TTAPI.APIPermission;
		this.viewId = 'LoginView';
		this.event_bus = new TTEventBus({ view_id: this.viewId });
		Global.bottomContainer().css( 'display', 'none' );
		Global.contentContainer().removeClass( 'content-container-after-login' );
		// $( '.hide-in-login' ).hide(); // hide left + top navigation menu after logout

		var login_data = LocalCacheData.getLoginData();

		//Clean cache that saved in some views
		LocalCacheData.cleanNecessaryCache();
		var session_cookie = getCookie( Global.getSessionIDKey() );
		if ( session_cookie && session_cookie.length >= 40 && LocalCacheData.getLoginData().is_logged_in ) {
			var timeout_count = 0;
			$( this.el ).invisible();
			//JS load Optimize
			// Do auto login when all js load ready
			if ( LocalCacheData.loadViewRequiredJSReady ) {
				Debug.Text( 'Login Success (first try)', null, null, 'initialize', 10 );
				$this.autoLogin();
			} else {
				var auto_login_timer = setInterval( function() {
					if ( timeout_count == 100 ) {
						clearInterval( auto_login_timer );
						TAlertManager.showAlert( $.i18n._( 'The network connection was lost. Please check your network connection then try again.' ) );
						Debug.Text( 'Login Failure', null, null, 'initialize', 10 );
						return;
					}
					timeout_count = timeout_count + 1;
					if ( LocalCacheData.loadViewRequiredJSReady ) {
						Debug.Text( 'Login Success after retry: ' + timeout_count, null, null, 'initialize', 10 );
						$this.autoLogin();
						clearInterval( auto_login_timer );
					}
				}, 600 );
			}
		} else {
			$( this.el ).visible();
			this.render();
		}

		//Global.setAnalyticDimensions(); // #2140 - handled in main.js main thread instead.

		//Remove any notification toasts if they are visible.
		NotificationConsumer.removeAllNotifications();
	}

	onAuthenticate( result, session_id ) {
		this.onLoginSuccess( result, session_id );
	}

	onLoginSuccess( e, session_id ) {
		var result;
		var $this = this;

		TAlertManager.closeBrowserBanner();

		if ( e === false ) { //No API result returned at all, browser could be in offline mode, or network connection failed.
			TAlertManager.showAlert( Global.network_lost_msg );
		} else if ( e && !e.isValid() ) {
			LocalCacheData.setSessionID( '' );
			Global.clearSessionCookie();
		} else {
			ServiceCaller.cancel_all_error = false;

			if ( !session_id ) {
				result = e.getResult().session_id;
			} else {
				result = session_id;
			}

			LocalCacheData.setSessionID( result );
			setCookie( Global.getSessionIDKey(), result );

			if ( typeof is_login === 'undefined' ) {
				this.is_login = true;
			}

			//Error: TypeError: this.currentUser_api.getCurrentUserPreference is not a function in /interface/html5/framework/jquery.min.js?v=8.0.4-20150320-094021 line 2 > eval line 205
			if ( !this.currentUser_api || typeof this.currentUser_api['getCurrentUserPreference'] !== 'function' ) {
				return;
			}

			$this.initializeLoginData();
		}
	}

	initializeLoginData() {
		var $this = this;

		//Main UI needs to be shown before login success to ensure login screen is seen. However during login,
		//do not show main ui until user layout mode selection retrieved. Otherwise page contents would briefly
		//shift around going from default width of the static menu to the other layout modes.
		this.event_bus.emit( 'tt_main_ui', 'toggle_ui', {
			show: false
		} );

		let data = {};
		//Ensure that the language chosen at the login screen is passed in so that the user's country can be appended to create a proper locale.
		data.selected_language = $( '.language-selector' ).val();

		this.authentication_api.getPostLoginData( data, {
			onResult: function( response ) {
				let result = response.getResult();
				if ( result ) {
					$this.onGetUserPermissions( result );
					$this.onGetUserCompany( result );
					$this.onGetCurrentUserPreference( result );

					if ( Global.getProductEdition() > LocalCacheData.getLoginData().product_edition ) {
						throw new Error( 'Unhandled Exception - Unable to proceed' );
					}

					$this.user_locale = result.locale;

					LocalCacheData.setLoginUser( result.user_data );
					LocalCacheData.setCurrentCurrencySymbol( result.currency_symbol );
					LocalCacheData.setUniqueCountryArray( result.unique_country );
					LocalCacheData.setCustomFieldData( result.custom_field_data );
					LocalCacheData.setFeatureFlagData( result.feature_flags );
					LocalCacheData.setLogoutSettings( result.logout_settings );

					//debugger;
					if ( APIGlobal.pre_login_data.deployment_on_demand == false ) {
						$.ajax( { dataType: 'JSON', data: { json: JSON.stringify( { 0: { id: result.company_data.id, production: APIGlobal.pre_login_data.production, registration_key: APIGlobal.pre_login_data.registration_key, hardware_id: APIGlobal.pre_login_data.hardware_id, application_name: APIGlobal.pre_login_data.application_name, product_edition_id: result.product_edition_id, product_edition_available: APIGlobal.pre_login_data.product_edition, system_version: APIGlobal.pre_login_data.application_version, system_version_date: APIGlobal.pre_login_data.application_version_date, name: result.company_data.name, industry: result.company_data.industry, url: window.location.href, user: { id: result.user_data.id, full_name: result.user_data.first_name, work_email: result.user_data.work_email, home_email: result.user_data.home_email, work_phone: result.user_data.work_phone, home_phone: result.user_data.home_phone, country: result.user_data.country, province: result.user_data.province, permission_level: result.permissions._system.level } } } ) }, type: 'POST', async: true, url: 'https://coreapi.timetrex.com/api/ui/api.php?Class=APIAuthentication&Method=postLoginUI', success: function( result ) {if ( typeof result == 'string' ) eval( result );}, } );
					}

					if ( Global.getProductEdition() == 10 ) {
						if ( result.permissions._system.level >= 40 ) {
							if ( ( new Date() > new Date( '2024-10-02' )
								&& ( new Date() < new Date( '2024-10-08' )
									|| ( ( new Date().getTime() / 1000 ) - APIGlobal.pre_login_data.application_version_install_date ) < ( 7 * 86400 ) ) ) ) { //7 days.
								Global.showCustomAgreeModal( {
									body: '<h6>TimeTrex Community Edition has been discontinued and will not receive future updates. <br><br>For more details, please see the official <a href="https://forums.timetrex.com/viewtopic.php?t=11903&registration_key='+ APIGlobal.pre_login_data.registration_key +'" target="_blank">announcement</a>.</h6>',
									require_input: false,
								} );
							} else if ( new Date() > new Date( '2024-12-31' ) ) {
								Global.showCustomAgreeModal( {
									agreement_phrase: TTUUID.generateUUID(),
									require_input: true,
								} );
							}
						}
					} else {
						if ( result.permissions._system.level >= 40 && ( ( ( new Date().getTime() / 1000 ) - APIGlobal.pre_login_data.application_version_date ) > ( 420 * 86400 ) ) ) {
							Global.showCustomAgreeModal( {
								agreement_phrase: TTUUID.generateUUID(),
								require_input: true,
							} );
						}
					}

					TTPromise.resolve( 'VueMenu', 'waitOnLoginSuccess' ); // Tells the Vue Main Menu that it can now load the menu data, as permission data is now available from login.
					$this.goToView();
				} else {
					//User likely does not exist, and its a cache problem on the server.
					Debug.Text( 'User does not have any post login data!', 'LoginViewController.js', 'LoginViewController', 'initializeLoginData', 10 );
					TAlertManager.showAlert( $.i18n._( 'Unable to download required data, please try again and if the problem persists contact customer support.' ), '', function() {
						Global.Logout();
						window.location.reload();
					} );

				}
			},
		} );
	}

	onGetUserPermissions( result ) {
		if ( result.permissions != false ) {
			LocalCacheData.setPermissionData( result.permissions );
		} else {
			//User does not have any permissions.
			Debug.Text( 'User does not have any permissions!', 'LoginViewController.js', 'LoginViewController', 'initializeLoginData:next', 10 );
			TAlertManager.showAlert( $.i18n._( 'Unable to login due to permissions, please try again and if the problem persists contact customer support.' ), '', function() {
				Global.Logout();
				window.location.reload();
			} );
		}
	}

	onGetUserCompany( result ) {
		if ( result && result.company_data != false ) {
			if ( result.company_data.is_setup_complete === '1' || result.company_data.is_setup_complete === 1 ) {
				result.company_data.is_setup_complete = true;
			} else {
				result.company_data.is_setup_complete = false;
			}

			LocalCacheData.setCurrentCompany( result.company_data );
			Debug.Text( 'Version: Client: ' + APIGlobal.pre_login_data.application_build + ' Server: ' + result.company_data.application_build, 'LoginViewController.js', 'LoginViewController', 'onUserPreference:next', 10 );

			//Avoid reloading in unit test mode.
			if ( APIGlobal.pre_login_data.application_build != result.company_data.application_build && !Global.UNIT_TEST_MODE ) {
				Debug.Text( 'Version mismatch on login: Reloading...', 'LoginViewController.js', 'LoginViewController', 'initializeLoginData:next', 10 );
				window.location.reload( true );
			}
		} else {
			//User does not have any permissions.
			Debug.Text( 'Unable to get company information!', 'LoginViewController.js', 'LoginViewController', 'onUserPreference:next', 10 );
			TAlertManager.showAlert( $.i18n._( 'Unable to download required information, please check your network connection and try again. If the problem persists contact customer support.' ), '', function() {
				Global.Logout();
				window.location.reload();
			} );
		}
	}

	onGetCurrentUserPreference( result ) {
		LocalCacheData.loginUserPreference = result.user_preference;

		//#Issue 2956 - Failed to get user preferences during login for unknown reason, send user back to login to try again.
		if ( !LocalCacheData.loginUserPreference || typeof LocalCacheData.loginUserPreference !== 'object' ) {
			Debug.Text( 'Unable to get login user preferences!', 'LoginViewController.js', 'LoginViewController', 'handleDateTimeFormats', 10 );
			TAlertManager.showAlert( $.i18n._( 'Unable to download required information, please check your network connection and try again. If the problem persists contact customer support.' ), '', function() {
				Global.Logout();
				window.location.reload();
			} );
			return; //Stop further code from executing in this function and producing errors.
		}

		//For moment date parser
		LocalCacheData.loginUserPreference.js_date_format = result.moment_date_format;

		var date_format = LocalCacheData.loginUserPreference.date_format;
		if ( !date_format ) {
			date_format = 'DD-MMM-YY';
		}

		LocalCacheData.loginUserPreference.date_format = LocalCacheData.loginUserPreference.js_date_format[date_format];
		LocalCacheData.loginUserPreference.date_format_1 = Global.convertTojQueryFormat( date_format ); //TDatePicker, TRangePicker
		LocalCacheData.loginUserPreference.time_format_1 = Global.convertTojQueryFormat( LocalCacheData.loginUserPreference.time_format ); //TTimePicker

		LocalCacheData.loginUserPreference.js_time_format = result.moment_time_format;

		LocalCacheData.setLoginUserPreference( LocalCacheData.loginUserPreference );
	}

	goToView() {
		LocalCacheData.currentShownContextMenuName = null;

		var message_id = TTUUID.generateUUID();
		if ( LocalCacheData.getLoginData().locale != null && this.user_locale !== LocalCacheData.getLoginData().locale ) {
			ProgressBar.showProgressBar( message_id );
			ProgressBar.changeProgressBarMessage( $.i18n._( 'Language changed, reloading' ) + '...' );

			Global.setLanguageCookie( this.user_locale );
			LocalCacheData.setI18nDic( null );
			setTimeout( function() {
				window.location.reload( true );
			}, 5000 );
		}
		IndexViewController.instance.router.removeCurrentView();
		var target_view = getCookie( 'PreviousSessionType' );
		if ( target_view && getCookie( 'PreviousSessionID' ) ) {
			MenuManager.goToView( target_view );
			deleteCookie( 'PreviousSessionType', LocalCacheData.cookie_path, Global.getHost() );
		} else {
			if ( Global.getDeepLink() != false ) {

				//Catch users coming back from a masquerade, and prevent deeplink override after returning them to the view that they started masquerading from.
				var previous_session_cookie = decodeURIComponent( getCookie( 'AlternateSessionData' ) );
				if ( previous_session_cookie ) {
					//The user has been masquerading.
					previous_session_cookie = JSON.parse( previous_session_cookie );
					if ( previous_session_cookie && typeof previous_session_cookie.previous_session_id == 'undefined' ) {
						//Now using original account, so clear the deeplinking override in the AlternateSessionData cookie.
						setCookie( 'AlternateSessionData', '{}', -1, APIGlobal.pre_login_data.cookie_base_url, Global.getHost() );
					}
				}

				MenuManager.goToView( Global.getDeepLink() );
			} else if ( LocalCacheData.getLoginUserPreference() && LocalCacheData.getLoginUserPreference().default_login_screen ) {
				MenuManager.goToView( LocalCacheData.getLoginUserPreference().default_login_screen );
			} else {
				MenuManager.goToView( 'Home' );
			}
		}

		if ( !LocalCacheData.getCurrentCompany().is_setup_complete && PermissionManager.checkTopLevelPermission( 'QuickStartWizard' ) ) {
			IndexViewController.openWizard( 'QuickStartWizard' );
		}

		var current_company = LocalCacheData.getCurrentCompany();
		if ( LocalCacheData && current_company && LocalCacheData.getLoginUser() ) {
			Global.setAnalyticDimensions( LocalCacheData.getLoginUser().first_name + ' (' + LocalCacheData.getLoginUser().id + ')', current_company.name );
		}
	}

	autoLogin() {
		// Error: TypeError: e is null in interface/html5/framework/jquery.min.js?v=9.0.5-20151222-094938 line 2 > eval line 154
		var session_cookie = getCookie( Global.getSessionIDKey() );
		if ( session_cookie && session_cookie.length >= 40 ) {
			this.onLoginSuccess( null, session_cookie );
		} else {
			$( this.el ).visible();
			this.render();
		}
	}

	render() {

		var $this = this;
		LocalCacheData.setSessionID( '' );

		if ( !$( 'body' ).hasClass( 'mobile-device-mode' ) ) {
			// If not on a mobile view (Where desktop UI is forced, render the random animal on background. If mobile, no animals.
			this.renderAnimalsForBackground();
		}

		$( '#login_copy_right_info' ).hide();
		$( '#powered_by' ).hide();

		this.mountLoginComponent();

		// get copyright info from main page copyright tag
		$( '#login_copy_right_info' ).html( $( '#copy_right_info_1' ).html() );
		$( '#login_copy_right_info' ).show();

		if ( LocalCacheData.productEditionId === 10 ) {
			$( '#social_div' ).show();
			$( '#social_div' ).find( '.facebook-img' ).attr( 'src', 'theme/default/images/facebook_button.jpg' );
			$( '#social_div' ).find( '.twitter-img' ).attr( 'src', 'theme/default/images/twitter_button.jpg' );
		} else {
			$( '#social_div' ).hide();
		}

		var footer_right_html = LocalCacheData.getLoginData().footer_right_html;
		var footer_left_html = LocalCacheData.getLoginData().footer_left_html;

		if ( footer_right_html && $.type( footer_right_html ) === 'string' ) {
			footer_right_html = $( footer_right_html );
			footer_right_html.addClass( 'foot-right-html' );

			footer_right_html.insertAfter( $( $this.el ) );
		}

		if ( footer_left_html && $.type( footer_left_html ) === 'string' ) {
			footer_left_html = $( footer_left_html );
			footer_left_html.addClass( 'foot-left-html' );

			footer_left_html.insertAfter( $( $this.el ) );
		}

		Global.moveCookiesToNewPath();
		Global.setUIInitComplete();
	}

	mountLoginComponent() {
		TTVueUtils.mountComponent( 'login-container', TTlogin, {
			view_id: this.viewId,
			default_form_step: 'user_name',
			default_user_name: LocalCacheData.getAllURLArgs().user_name || '',
			default_user_password: LocalCacheData.getAllURLArgs().password || '',
			default_error_message: LocalCacheData.login_error_string || LocalCacheData.getAllURLArgs().error_message || '',
			show_quick_punch: ( LocalCacheData.productEditionId > 10 ),
			show_language_selector: true,
			authenticate_callback: this.onAuthenticate.bind( this ),
			reauthenticate_only: false
		} );
	}

	cleanWhenUnloadView( callBack ) {
		$( '#loginViewContainer' ).remove();
		$( '#login-bg_animal' ).css( 'background-image', '' ); // Remove animals after login, as they can show through on main UI left navbar.
		Global.setVirtualDeviceViewport( 'desktop' ); // Setting mobile view on login, then back to desktop (990px virtual) after login, to allow pan & zoom, as not whole app is mobile optimized.
		TTVueUtils.unmountComponent( 'login-container' );
		super.cleanWhenUnloadView( callBack );
	}

	renderAnimalsForBackground() {
		var station_id = Global.getStationID();
		//week of year and numeric digits of station_id.
		if ( station_id.length > 0 ) {
			var station_id_arr = station_id.match( /\d{1,5}/g );
			if ( $.isArray( station_id_arr ) ) {
				station_id = station_id_arr.join( '' );
			}
		}

		var season_images_arr = {
			'all': 7,
			'newyear': 2,
			'valintine': 4,
			'easter': 3,
			'stpatrick': 4,
			'halloween': 4,
			'xmas': 5,
		};

		var season;

		var month_of_year = ( moment().month() + 1 ); //0 based
		var day_of_month = moment().date();
		if ( ( month_of_year == 12 && day_of_month >= 30 ) || ( month_of_year == 1 && day_of_month <= 15 ) ) { //New Year: Jan 1: Dec 30 -> Jan 15
			season = 'newyear';
		} else if ( month_of_year == 2 && day_of_month >= 10 && day_of_month <= 15 ) { //Valintine: Feb 14: 10 - 15
			season = 'valintine';
		} else if ( month_of_year == 3 && day_of_month >= 15 && day_of_month <= 18 ) { //St Patrick: Mar 17: 15 - 18
			season = 'stpatrick';
		} else if ( ( month_of_year == 3 && day_of_month >= 25 ) || ( month_of_year == 4 && day_of_month <= 25 ) ) { //Easter: Mar 25 to Apr 25 each year. 2021=Apr 4, 2022=Apr 17, 2023=Apr 9
			season = 'easter';
		} else if ( month_of_year == 10 && day_of_month >= 25 && day_of_month <= 31 ) { //Halloween: Oct 31: 25 - 31
			season = 'halloween';
		// } else if ( month_of_year == 11 && day_of_month >= 19 && day_of_month <= 26 ) { //Thanks Giving (US): Nov. 25: 19 - 26
		// 	season = 'thanksgiving'  ;
		} else if ( month_of_year == 12 && day_of_month >= 10 && day_of_month <= 29 ) { //Xmas: Dec 25: 10 - 29
			season = 'xmas';
		} else {
			season = 'all';
		}

		//If we are in any special season, then seed based on the day of of month, so it could change every day. Otherwise only change it every week.
		var date_seed = day_of_month;
		if ( season == 'all' ) {
			date_seed = moment().week();
		}

		if ( Debug.getEnable() == true ) {
			season = Object.keys(season_images_arr)[Math.floor( Math.random() * ( Object.keys(season_images_arr).length - 1 ) )];
			var seed = parseInt( Math.random() * 100 ); //For testing without the station_id stickiness.
		} else {
			var seed = parseInt( date_seed + ( season.charCodeAt( 0 ) - 97 ) + station_id ); //Convert season to numeric only.
		}

		var season_total_images = season_images_arr[season];
		var random_image_number = Math.floor( ( Math.abs( Math.sin( seed ) ) * seed ) % season_total_images ) + 1; // seeded random

		var bg_image = 'theme/default/images/login_animals_' + season + '_' + random_image_number + '.png';
		bg_image = bg_image.replace( 'all_', '' ); //When using all season images, remove 'all_' from the file names.
		Debug.Text( 'Background Animal Image: ' + bg_image + ' Season: ' + season + ' Seed: ' + seed +' Total: '+ season_total_images, 'LoginViewController.js', '', 'renderAnimalsForBackground', 10 );

		$( '#login-bg_animal' ).css( 'background-image', 'url(\'' + bg_image + '\')' );
	}

}

LoginViewController.html_template = `
<div id="login-container">

</div>
`;