// import { LocalCacheData } from 'exports-loader?exports=LocalCacheData!@/global/LocalCacheData';
import { TTUUID } from '@/global/TTUUID';
import { TTAPI } from '@/services/TimeTrexClientAPI';
import { FormItemType, WidgetNamesDic } from '@/global/widgets/search_panel/FormItemType'; // TODO: duplicated in merged js files.
import { RateLimit } from '@/global/RateLimit';
import '@/global/widgets/view_min_tab/ViewMinTabBar';
import { ServiceCaller } from '@/services/ServiceCaller';
import TTEventBus from '@/services/TTEventBus';
import { HtmlTemplatesGlobal, TemplateType } from '@/services/HtmlTemplates';
import TTVueUtils from '@/services/TTVueUtils';
import TTMultiFactorAuthentication from '@/components/login/TTMultiFactorAuthentication.vue';
import TTCustomAccept from '@/components/login/TTCustomAccept.vue';
import { Decimal } from 'decimal.js';
import moment from 'moment';
// import { createApp } from 'vue'; // Currently only used by Global.initEditTest
// import TTEditView from '@/components/TTEditView'; // Used by Global.initEditTest which is currently commented out as its for testing only.

//Global variables and functions will be used everywhere
export var Global = function() {
};
Global.event_bus = new TTEventBus({ view_id: 'global' });
Global.sortOrderRegex = /^-([0-9]{3,9})-/;
Global.current_ping = -1;

Global.UNIT_TEST_MODE = false;

Global.app_min_width = 990;

Global.theme = 'default';

/**
 * UIReadyStatus:
 * 0 - Global.setUINotready() - the UI is not ready
 * 1 - Global.setUIReady() - the overlay is out of the way but ui is not done rendering
 * 2 - Global.setUIInitComplete() the overlay is done rendering
 */
Global.UIReadyStatus = 0;

Global.signal_timer = null;

Global.isScrolledIntoView = function( elem ) {
	var $elem = elem;
	var $window = $( window );
	var docViewTop = $window.scrollTop();
	var docViewBottom = docViewTop + $window.height();
	if ( !$elem.offset() ) {
		return true;
	}
	var elemTop = $elem.offset().top;
	//var elemBottom = elemTop + $elem.height();
	//((elemBottom <= (docViewBottom + 200)) && (elemTop >= docViewTop));
	return elemTop < docViewBottom;
};

//Check if the DOM (not jQuery) element requires a vertical scrollbar.
Global.isVerticalScrollBarRequired = function( element ) {
	return element && element.scrollHeight > element.clientHeight;
};

//Check if the DOM (not jQuery) element requires a horizontal scrollbar.
Global.isHorizontalScrollBarRequired = function( element ) {
	return element && element.scrollWidth > element.clientWidth;
};

//Gets the width of the browsers scrollbar. This value depends on the users OS/browser.
Global.getScrollbarWidth = function() {
	if ( LocalCacheData.getScrollbarWidth() > 0 ) {
        return LocalCacheData.getScrollbarWidth();
    }

	let scroll_div = document.createElement("div");
	scroll_div.style.visibility = 'hidden';
	scroll_div.style.overflow = 'scroll';
	document.body.appendChild(scroll_div);
	let scroll_bar_width = scroll_div.offsetWidth - scroll_div.clientWidth;
	document.body.removeChild(scroll_div);

	//If for some reason we cannot get the width, default the width to 17 which is most common value. (Windows
	if ( !scroll_bar_width ) {
        scroll_bar_width = 17;
    }

	LocalCacheData.setScrollBarWidth( scroll_bar_width );

	return scroll_bar_width;
}

//Gets the height of the browsers scrollbar. This value depends on the users OS/browser.
Global.getScrollbarHeight = function() {
	if ( LocalCacheData.getScrollbarHeight() > 0 ) {
		return LocalCacheData.getScrollbarHeight();
	}

	let scroll_div = document.createElement("div");
	scroll_div.style.visibility = 'hidden';
	scroll_div.style.overflow = 'scroll';
	document.body.appendChild(scroll_div);
	let scroll_bar_height = scroll_div.offsetHeight - scroll_div.clientHeight;
	document.body.removeChild(scroll_div);

	//If for some reason we cannot get the height, default the height to 17 which is most common value. (Windows
	if ( !scroll_bar_height ) {
		scroll_bar_height = 17;
    }

	LocalCacheData.setScrollBarHeight( scroll_bar_height );

	return scroll_bar_height;
}

Global.KEYCODES = {
	'48': '0',
	'49': '1',
	'50': '2',
	'51': '3',
	'52': '4',
	'53': '5',
	'54': '6',
	'55': '7',
	'56': '8',
	'59': '9',
	'65': 'a',
	'66': 'b',
	'67': 'c',
	'68': 'd',
	'69': 'e',
	'70': 'f',
	'71': 'g',
	'72': 'h',
	'73': 'i',
	'74': 'j',
	'75': 'k',
	'76': 'l',
	'77': 'm',
	'78': 'n',
	'79': 'o',
	'80': 'p',
	'81': 'q',
	'82': 'r',
	'83': 's',
	'84': 't',
	'85': 'u',
	'86': 'v',
	'87': 'w',
	'88': 'x',
	'89': 'y',
	'90': 'z'
};

Global.needReloadBrowser = false; // Need reload browser after set new cookie. To make router work for new session.

// this attribute use to block UI in speical case that we allow users to click part of them and block other parts.
// For example, when open edit view to block context menu.
Global.block_ui = false;

Global.sendErrorReport = function() {
	var error_string = arguments[0];
	var from_file = arguments[1];
	var line = arguments[2];
	var col = arguments[3];
	var error_obj = arguments[4]; //Error object.

	RateLimit.setID( 'sendErrorReport' );
	RateLimit.setAllowedCalls( 6 );
	RateLimit.setTimeFrame( 7200 ); //2hrs

	if ( RateLimit.check() ) {
		var captureScreenShot = function( error_msg, error_obj ) {
			if ( Global.isCanvasSupported() && typeof Promise !== 'undefined' ) { //HTML2Canvas requires promises, which IE11 does not have.
				html2canvas( document.body ).then( function( canvas ) {
					var image_string = canvas.toDataURL().split( ',' )[1];
					sourceMapStackTrace( error_msg, error_obj, image_string );
				} );
			} else {
				sourceMapStackTrace( error_msg, error_obj, null );
			}
		};

		var sourceMapStackTrace = function( error_msg, error_obj, image_string ) {
			if ( error_obj ) {
				var stacktrace_callback = function( stackframes, error_msg, error_obj, image_string ) {
					var stringified_stack = stackframes.map( function( sf ) {
						return '  ' + sf.toString(); //Indent stack trace.
					} ).join( '\n' );

					error_msg = error_msg + '\n\n\n' + 'Stack Trace (Mapped): \n' + error_obj.name + ': ' + error_obj.message + '\n' + stringified_stack;
					error_msg = error_msg + '\n\n\n' + 'Stack Trace (Raw): \n' + error_obj.stack;

					sendErrorReport( error_msg, error_obj, image_string );
				};

				var stacktrace_errback = function( error_msg, error_obj, image_string ) {
					console.error( 'ERROR: Unable to source map stack trace!' );
					sendErrorReport( error_msg, error_obj, image_string );
				};

				StackTrace.fromError( error_obj ).then( stackframes => stacktrace_callback( stackframes, error_msg, error_obj, image_string ) ).catch( error => stacktrace_errback( error_msg, error_obj, image_string ) );
			} else {
				sendErrorReport( error_msg, error_obj, image_string );
			}
		};

		var sendErrorReport = function( error_msg, error_obj, image_string ) {
			Debug.Text( 'ERROR: ' + error_msg, 'Global.js', '', 'sendErrorReport', 1 );

			var api_authentication = TTAPI.APIAuthentication;
			api_authentication.sendErrorReport( error_msg, image_string, {
				onResult: function( result ) {
					if ( !Global.dont_check_browser_cache && APIGlobal.pre_login_data.production === true && result.getResult() !== APIGlobal.pre_login_data.application_build ) {
						result = result.getResult();
						var message = $.i18n._( 'Your web browser is caching incorrect data, please press the refresh button on your web browser or log out, clear your web browsers cache and try logging in again.' ) + '<br><br>' + $.i18n._( 'Local Version' ) + ':  ' + result + '<br>' + $.i18n._( 'Remote Version' ) + ': ' + APIGlobal.pre_login_data.application_build;
						Global.dont_check_browser_cache = true;
						Global.sendErrorReport( 'Your web browser is caching incorrect data. Local Version' + ':  ' + result + ' Remote Version' + ': ' + APIGlobal.pre_login_data.application_build, ServiceCaller.root_url, '', '', '' );

						var timeout_handler = window.setTimeout( function() {
							window.location.reload( true );
						}, 120000 );

						TAlertManager.showAlert( message, '', function() {
							LocalCacheData.loadedScriptNames = {};
							Debug.Text( 'Incorrect cache... Forcing reload after JS exception...', 'Global.js', 'Global', 'cachingIncorrectData', 10 );
							window.clearTimeout( timeout_handler );
							window.location.reload( true );
						} );
					} else if ( Global.dont_check_browser_cache ) {
						Global.dont_check_browser_cache = false;
					}
				}
			} );
		};

		var login_user = LocalCacheData.getLoginUser();

		/*
		 * JavaScript exception ignore list
		 */
		if ( from_file && typeof from_file == 'string' && from_file.indexOf( 'extension://' ) >= 0 ) { //Error happened in some Chrome Extension, ignore.
			console.error( 'Ignoring javascript exception from browser extension outside of our control...' );
			return;
		}

		if ( error_string.indexOf( 'Script error' ) >= 0 || //Script error. in:  line: 0 -- Likely browser extensions or errors from injected or outside javascript.
			error_string.indexOf( 'Unspecified error' ) >= 0 || //From IE: Unspecified error. in N/A line 1
			error_string.indexOf( 'TypeError: \'null\' is not an object' ) >= 0 ||
			error_string.indexOf( '_avast_submit' ) >= 0 || //Errors from anti-virus extension
			error_string.indexOf( 'ResizeObserver loop limit exceeded' ) >= 0 ||
			error_string.indexOf( 'ResizeObserver loop completed with undelivered notifications' ) >= 0 ||
			error_string.indexOf( 'googletag' ) >= 0 || //Errors from google tag extension -- Uncaught TypeError: Cannot redefine property: googletag
			error_string.indexOf( 'NS_ERROR_' ) >= 0 ||
			error_string.indexOf( 'NS_ERROR_OUT_OF_MEMORY' ) >= 0 ||
			error_string.indexOf( 'NPObject' ) >= 0 //Error calling method on NPObject - likely caused by an extension or plugin in the browser
			) {
			console.error( 'Ignoring javascript exception outside of our control...' );
			return;
		}

		if ( Global.idle_time > 15 ) {
			Debug.Text( 'User inactive more than 15 mins, not sending error report.', 'Global.js', '', 'sendErrorReport', 1 );
			if ( typeof ( gtag ) !== 'undefined' && APIGlobal.pre_login_data.analytics_enabled === true ) {
				gtag( 'event', 'exception', {
					'exDescription': 'Session Idle: ' + error_string + ' File: ' + ( ( from_file ) ? from_file.replace( Global.getBaseURL(), '' ) : 'N/A' ) + ' Line: ' + line,
					'exFatal': false
				} )
			}

			return;
		}

		var error;

		//BUG#2066 - allow this function to be called earlier.
		var script_name = '~unknown~';
		if ( Global.isSet( LocalCacheData ) && Global.isSet( LocalCacheData.current_open_primary_controller ) && Global.isSet( LocalCacheData.current_open_primary_controller.script_name ) ) {
			script_name = LocalCacheData.current_open_primary_controller.script_name;
		}

		var pre_login_data;
		if ( APIGlobal.pre_login_data ) {
			pre_login_data = APIGlobal.pre_login_data;
		} else {
			pre_login_data = null;
		}

		var current_company_obj;
		if ( Global.isSet( LocalCacheData ) && LocalCacheData['current_company'] ) { //getCurrentCompany() which in turn calls getRequiredLocalCache(), which can call sendErroReport causing a loop. So try to prevent that by checking LocalCacheData['current_company'] first.
			current_company_obj = LocalCacheData.getCurrentCompany();
		} else {
			current_company_obj = null;
		}

		if ( login_user && Debug.varDump ) {
			error = 'Client Version: ' + APIGlobal.pre_login_data.application_build + '\n\nUncaught Error From: ' + script_name + '\n\nError: ' + error_string + ' in: ' + from_file + ' line: ' + line + ':' + col + '\n\nUser: ' + login_user.user_name + '\n\nURL: ' + window.location.href + '\n\nUser-Agent: ' + navigator.userAgent + ' ' + '\n\nCurrent Ping: ' + Global.current_ping + '\n\nIdle Time: ' + Global.idle_time + '\n\nSession ID Key: ' + LocalCacheData.getSessionID() + '\n\nCurrent User Object: \n' + Debug.varDump( login_user ) + '\n\nCurrent Company Object: \n' + Debug.varDump( current_company_obj ) + '\n\nPreLogin: \n' + Debug.varDump( pre_login_data ) + ' ';
		} else {
			error = 'Client Version: ' + APIGlobal.pre_login_data.application_build + '\n\nUncaught Error From: ' + script_name + '\n\nError: ' + error_string + ' in: ' + from_file + ' line: ' + line + ':' + col + '\n\nUser: N/A' + '\n\nURL: ' + window.location.href + ' ' + '\n\nUser-Agent: ' + navigator.userAgent;
		}

		console.error( 'JAVASCRIPT EXCEPTION:\n---------------------------------------------\n' + error + '\n---------------------------------------------' );
		debugger;

		//When not in production mode, popup alert box anytime an exception appears so it can't be missed.
		if ( APIGlobal.pre_login_data.production !== true && APIGlobal.pre_login_data.demo_mode !== true && APIGlobal.pre_login_data.sandbox !== true ) {
			alert( 'JAVASCRIPT EXCEPTION:\n---------------------------------------------\n' + error + '\n---------------------------------------------' );
		}

		if ( typeof ( gtag ) !== 'undefined' && APIGlobal.pre_login_data.analytics_enabled === true ) {
			// Send an exception hit to Google Analytics. Must be 8192 bytes or smaller.
			// Strip the domain part off the URL on 'from_file' to better account for similar errors.
			gtag( 'event', 'exception', {
				'exDescription': error_string + ' File: ' + ( ( from_file ) ? from_file.replace( Global.getBaseURL(), '' ) : 'N/A' ) + ' Line: ' + line + ':' + col,
				'exFatal': false
			} )
		}

		if ( typeof ( clarity ) !== 'undefined' && APIGlobal.pre_login_data.analytics_enabled === true ) {
			clarity('set', 'exception', 'true' );
			clarity('set', 'userId', LocalCacheData?.getLoginUser()?.id );
		}

		//Don't send error report if exception not happens in our codes.
		//from_file should always contains the root url
		//If URL is not sent by IE, assume its our own code and report the error still.
		// Modern browsers won't send error reports from other domains due to security issues now, so I think this can be removed.
		// if ( from_file && from_file.indexOf( ServiceCaller.root_url ) < 0 ) {
		// 	Debug.Text( 'Exception caught from unauthorized source, not sending report. Source: "' + ServiceCaller.root_url + '" Script: ' + from_file, 'Global.js', '', 'sendErrorReport', 1 );
		// 	return;
		// }

		if ( current_company_obj ) { //getCurrentCompany() which in turn calls getRequiredLocalCache(), which can call sendErroReport causing a loop. So try to prevent that by checking LocalCacheData['current_company'] first.
			error = error + '\n\n' + 'Product Edition: ' + current_company_obj.product_edition_id;
		}

		error = error + '\n\n\n' + 'Clicked target stacks: ' + JSON.stringify( LocalCacheData.ui_click_stack, undefined, 2 );
		error = error + '\n\n\n' + 'API stacks: ' + JSON.stringify( LocalCacheData.api_stack, undefined, 2 );

		captureScreenShot( error, error_obj );
	}
};

Global.initStaticStrings = function() {
	Global.network_lost_msg = $.i18n._( 'The network connection was lost. Please check your network connection then try again.' );

	Global.any_item = '-- ' + $.i18n._( 'Any' ) + ' --';

	Global.all_item = '-- ' + $.i18n._( 'All' ) + ' --';

	Global.root_item = $.i18n._( 'Root' );

	Global.loading_label = '...';

	Global.customize_item = '-- ' + $.i18n._( 'Customize' ) + ' --';

	Global.default_item = '-- ' + $.i18n._( 'Default' ) + ' --';

	Global.selected_item = '-- ' + $.i18n._( 'Selected' ) + ' --';

	Global.open_item = '-- ' + $.i18n._( 'Open' ) + ' --';

	Global.empty_item = '-- ' + $.i18n._( 'None' ) + ' --';

	Global.view_mode_message = $.i18n._( 'You are currently in \'View\' mode' );

	Global.view_mode_edit_message = $.i18n._( 'instead click the \'Edit\' icon to modify fields' ); //Does not start with a capital as it is appended text.

	Global.no_result_message = $.i18n._( 'No Results Found' );

	Global.save_and_continue_message = $.i18n._( 'Please save this record before modifying any related data' );

	Global.no_hierarchy_message = $.i18n._( 'No Hierarchies Defined' );

	Global.modify_alert_message = $.i18n._( 'You have modified data without saving, are you sure you want to continue and lose your changes?' );

	Global.not_modify_alert_message = $.i18n._( 'No changes were detected in this record, but saving may modify related data. If you do not want to make changes, you may use the Cancel button instead. <br><br>Would you still like to save without changes?' );

	Global.confirm_on_exit_message = $.i18n._( 'Are you sure you want to continue without saving?' );

	Global.delete_confirm_message = $.i18n._( 'You are about to delete data, once data is deleted it can not be recovered.<br>Are you sure you wish to continue?' );

	Global.delete_dashlet_confirm_message = $.i18n._( 'You are about to delete this dashlet, once a dashlet is deleted it can not be recovered.<br>Are you sure you wish to continue?' );

	Global.copy_multiple_confirm_message = $.i18n._( 'You are about to copy multiple records.<br>Are you sure you wish to continue?' );

	Global.auto_arrange_dashlet_confirm_message = $.i18n._( 'You are about to restore all dashlets to their default size/layout.<br>Are you sure you wish to continue?' );

	Global.rese_all_dashlet_confirm_message = $.i18n._( 'You are about to remove all your customized dashlets and restore them back to the defaults.<br>Are you sure you wish to continue?' );
};

Global.getUpgradeMessage = function() {
	var message = $.i18n._( 'This functionality is only available in' ) +
		' ' + LocalCacheData.getLoginData().application_name + ' ';

	if ( Global.getProductEdition() < 15 ) {
		//Do not mention professional if user is on professional edition.
		message += $.i18n._( 'Professional, Corporate, or Enterprise Editions.' );
	} else if ( Global.getProductEdition() < 20 ) {
		//Do not mention corporate if user is on corporate edition.
		message += $.i18n._( 'Corporate or Enterprise Editions.' );
	} else {
		message += $.i18n._( 'Enterprise Editions.' );
	}

	message += ' ' + $.i18n._( 'For more information please visit' ) + ' <a href="https://coreapi.timetrex.com/r?id=810" target="_blank">www.timetrex.com</a>';

	Global.trackView( 'CommunityUpgrade' );
	return message;
};

Global.doPingIfNecessary = function() {
	if ( Global.idle_time < Math.min( 15, APIGlobal.pre_login_data.session_idle_timeout / 60 ) ) { //idle_time is minutes, session_idle_timeout is seconds.
		Global.idle_time = 0;
		return;
	}

	Debug.Text( 'User is active again after idle for: ' + Global.idle_time + '... Resetting idle to 0', 'Global.js', '', 'doPingIfNecessary', 1 );
	Global.idle_time = 0;

	if ( LocalCacheData.current_open_primary_controller.viewId === 'LoginView' ) {
		return;
	}

	var api = TTAPI.APIAuthentication;
	api.isLoggedIn( false, {
		onResult: function( result ) {
			var res_data = result.getResult();

			if ( res_data !== true ) {
				//Don't do Logout here, as we need to display a "Session Expired" message to the user, which is triggered from the ServiceCaller.
				//  In order to trigger that though, we need to make an *Authenticated* API call to APIMisc.Ping(), rather than UnAuthenticated call to APIAuthentication.Ping()
				var api = TTAPI.APIMisc;
				api.ping( {
					onResult: function() {
					}
				} );
			}
		}
	} );
};

Global.setupPing = function() {
	Global.idle_time = 0;
	$( 'body' ).mousemove( Global.debounce( function setupPingMouseMoveEvent( e ) {
		Global.doPingIfNecessary();
	}, 1000 ) );
	$( 'body' ).keypress( Global.debounce( function setupPingKeyPressEvent( e ) {
		Global.doPingIfNecessary();
	}, 1000 ) );

	setInterval( timerIncrement, 60000 ); // 1 minute
	function timerIncrement() {
		Global.idle_time = Global.idle_time + 1;
		if ( Global.idle_time >= Math.min( 15, APIGlobal.pre_login_data.session_idle_timeout / 60 ) ) {
			Debug.Text( 'User is idle: ' + Global.idle_time, 'Global.js', '', 'setupPing', 1 );
		}
	}
};

Global.clearCache = function( function_name ) {
	for ( var key in LocalCacheData.result_cache ) {
		if ( key.indexOf( function_name ) >= 0 ) {
			delete LocalCacheData.result_cache[key];
		}
	}
};

Global.getHost = function( host ) {
	if ( !host ) {
		host = window.location.hostname;
	}

	//Make sure its not an IPv4 address, and if its a domain has more than 1 dot in it before parsing off the sub-domain part.
	// So both IPv4 addresses and domains like: localhost (no dot at all), mycompany.com should not be modified at all. Only: sub.mycompany.com, sub.sub2.mycompany.com
	var is_sub_domain = host.match( /\./g );
	if ( /^(([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])\.){3}([0-9]|[1-9][0-9]|1[0-9]{2}|2[0-4][0-9]|25[0-5])$/.test( host ) == false && is_sub_domain && is_sub_domain.length > 1 ) {
		host = host.substring( ( host.indexOf( '.' ) + 1 ) );
	}

	return host;
};

Global.setWidgetEnabled = function( widget, val ) {
	if ( widget ) {
		if ( !val ) {
			widget.attr( 'disabled', 'true' );
			widget.addClass( 'disable-filter' );
		} else {
			widget.removeAttr( 'disabled' );
			widget.removeClass( 'disable-filter' );
		}
	}
};

Global.createViewTabs = function() {
	//JS load Optimize
	if ( typeof WidgetNamesDic == 'undefined' ) {
		return;
	}
	if ( LocalCacheData.loadViewRequiredJSReady ) {
		if ( !LocalCacheData.view_min_tab_bar ) {
			var view_min_tab_bar = Global.loadWidgetByName( WidgetNamesDic.VIEW_MIN_TAB_BAR );
			view_min_tab_bar = $( view_min_tab_bar ).ViewMinTabBar();
			$( '.layout-content-wrapper' ).append( view_min_tab_bar );

			LocalCacheData.view_min_tab_bar = view_min_tab_bar;
		}

		LocalCacheData.view_min_tab_bar.buildTabs( LocalCacheData.view_min_map );
	}
};

Global.addViewTab = function( view_id, view_name, url ) {
	LocalCacheData.view_min_map[view_id] = view_name;

	LocalCacheData.view_min_map[view_id + '_url'] = url;

	Global.createViewTabs();
};

Global.removeViewTab = function( view_id ) {
	delete LocalCacheData.view_min_map[view_id];
	$( '#min_tab_' + view_id ).remove();
};

Global.cleanViewTab = function() {
	LocalCacheData.view_min_map = {};
	Global.createViewTabs();
};

/*
 * Capitalize the first letter of each word in a string
 */
Global.ucwords = function( str ) {
	if ( typeof str == 'string' ) { //in case null or false is passed, we should check the type.
		let words = str.split( ' ' ); // split string into words

		let result = words.map( function( word ) { // iterate over words
			return word.charAt( 0 ).toUpperCase() + word.slice( 1 ); // change case of first letter and concatenate with rest of word
		} );

		return result.join( ' ' ); // join words back into string
	}

	return str;
};

/*
 * Capitalize the first letter of a string
 */
Global.upCaseFirstLetter = function( str ) {
	if ( typeof str == 'string' ) { //in case null or false is passed, we should check the type.
		str = str.charAt( 0 ).toUpperCase() + str.slice( 1 );
	}
	return str;
};

/*
 * This function is used to calculate the width of a text string in pixels.
 *   It uses a canvas for performance reasons, as it prevents all forced reflows, and seems to be generally faster overall.
 */
Global.calculateTextWidth = function( text, options ) {
	if ( typeof options === 'undefined' ) {
		options = {};
	}

	if ( !options.fontSize ) {
		options.fontSize = '12px';
	}

	if ( !options.fontFamily ) {
		options.fontFamily = window.getComputedStyle( document.body ).fontFamily; //Use default font family from CSS.
	}

	let canvas = Global.calculateTextWidth.canvas || ( Global.calculateTextWidth.canvas = document.createElement( 'canvas' ) );
	let context = canvas.getContext( '2d' );

	if ( options.fontFamily ) {
		options.fontFamily = options.fontFamily;
	} else {
		options.fontFamily = '';
	}

	if ( options.fontWeight ) {
		options.fontWeight = options.fontWeight;
	} else {
		options.fontWeight = '';
	}

	if ( options.wordBreak ) {
		options.wordBreak = options.wordBreak;
	} else {
		options.wordBreak = '';
	}

	context.font = options.fontWeight + ' ' + options.fontSize + ' ' + options.fontFamily;

	var content_width = context.measureText( text ).width;

	if ( options.min_width && options.min_width > 0 && content_width < options.min_width ) {
		content_width = options.min_width;
	}
	if ( options.padding && options.padding > 0 ) {
		content_width = content_width + options.padding;
	}
	if ( options.max_width > 0 && content_width > options.max_width ) {
		content_width = options.max_width;
	}

	return content_width;
};

Global.strToDate = function( date_string, format ) {

	//better to use Date.parse, let's see
	if ( !Global.isSet( format ) && LocalCacheData.getLoginUserPreference() ) {
		format = LocalCacheData.getLoginUserPreference().date_format;
	}

	if ( !format ) {
		format = 'DD-MMM-YY';
	}

	var date = moment( date_string, format );
	date = date.toDate();

	//The moment will pass everything as a date. Judge if the year less 1000 than 1900 or beyond 1000 of 1900,
	//we think it's a invalid year
	if ( date.getYear() < -1000 || date.getYear() > 1000 ) {
		return null;
	}

	return date;
};

Global.strToDateTime = function( date_string ) {
	//Error: TypeError: Global.strToDateTime(...) is null in /interface/html5/framework/jquery.min.js?v=8.0.0-20141117-153515 line 4862
	if ( !date_string || !LocalCacheData.getLoginUserPreference() ) {
		return null;
	}

	var date_format = LocalCacheData.getLoginUserPreference().date_format;
	var time_format = LocalCacheData.getLoginUserPreference().js_time_format[LocalCacheData.getLoginUserPreference().time_format];
	var date = moment( date_string, date_format + ' ' + time_format ).toDate();

	return date;
	//return Date.parse( date_string );
};

//Convert all kinds of date time to mm/dd/yyyy so Date.parse can parse it correct
Global.getStandardDateTimeStr = function( date_str, time_str ) {
	//var result = Global.strToDate( date_str ).format( 'MM/DD/YYYY' ) + ' ' + time_str;

	return date_str;
};

Global.convertTojQueryFormat = function( date_format ) {
	//For moment date parser
	var jquery_date_format = {
		'd-M-y': 'dd-M-y',
		'd-M-Y': 'dd-M-yy',
		'dMY': 'ddMyy',
		'd/m/Y': 'dd/mm/yy',
		'd/m/y': 'dd/mm/y',
		'd-m-y': 'dd-mm-y',
		'd-m-Y': 'dd-mm-yy',
		'm/d/y': 'mm/dd/y',
		'm/d/Y': 'mm/dd/yy',
		'm-d-y': 'mm-dd-y',
		'm-d-Y': 'mm-dd-yy',
		'Y-m-d': 'yy-mm-dd',
		'M-d-y': 'M-dd-y',
		'M-d-Y': 'M-dd-yy',
		'l, F d Y': 'DD, MM dd yy',
		'D, F d Y': 'D, MM dd yy',
		'D, M d Y': 'D, M dd yy',
		'D, d-M-Y': 'D, dd-M-yy',
		'D, dMY': 'D, ddMyy',

		'g:i A': 'h:mm TT',
		'g:i a': 'h:mm tt',
		'G:i': 'H:mm',
		'g:i A T': 'h:mm TT',
		'G:i T': 'H:mm',

		'g:i:s A': 'h:mm:ss TT',
		'g:i:s a': 'h:mm:ss tt',
		'G:i:s': 'H:mm:ss',
		'g:i:s A T': 'h:mm:ss TT',
		'G:i:s T': 'H:mm:ss'
	};

	return jquery_date_format[date_format];
};

Global.updateUserPreference = function( callBack, message ) {
	var user_preference_api = TTAPI.APIUserPreference;
	var current_user_aou = TTAPI.APIAuthentication;

	if ( message ) {
		ProgressBar.changeProgressBarMessage( message );
	}

	current_user_aou.getCurrentUserPreference( {
		onResult: function( result ) {
			var result_data = result.getResult();
			LocalCacheData.loginUserPreference = result_data;

			user_preference_api.getOptions( 'moment_date_format', {
				onResult: function( jsDateFormatRes ) {
					var jsDateFormatResultData = jsDateFormatRes.getResult();

					//For moment date parser
					LocalCacheData.loginUserPreference.js_date_format = jsDateFormatResultData;

					var date_format = LocalCacheData.loginUserPreference.date_format;
					if ( !date_format ) {
						date_format = 'DD-MMM-YY';
					}

					LocalCacheData.loginUserPreference.date_format = LocalCacheData.loginUserPreference.js_date_format[date_format];

					LocalCacheData.loginUserPreference.date_format_1 = Global.convertTojQueryFormat( date_format ); //TDatePicker, TRangePicker
					LocalCacheData.loginUserPreference.time_format_1 = Global.convertTojQueryFormat( LocalCacheData.loginUserPreference.time_format ); //TTimePicker

					user_preference_api.getOptions( 'moment_time_format', {
						onResult: function( jsTimeFormatRes ) {
							var jsTimeFormatResultData = jsTimeFormatRes.getResult();

							LocalCacheData.loginUserPreference.js_time_format = jsTimeFormatResultData;

							LocalCacheData.setLoginUserPreference( LocalCacheData.loginUserPreference );

							if ( callBack ) {
								callBack();
							}

						}
					} );

				}
			} );
		}
	} );
};

/* jshint ignore:start */
Global.roundTime = function( epoch, round_value, round_type ) {
	if ( epoch === 0 || round_value === 0 ) {
		return epoch;
	}

	var round_type = round_type || 20;

	switch ( round_type ) {
		case 10: //Down
			epoch = ( epoch - ( epoch % round_value ) );
			break;
		case 20: //Average
		case 25: //Average (round split seconds up)
		case 27: //Average (round split seconds down)
			var tmp_round_value;
			if ( round_type == 20 || round_value <= 60 ) {
				tmp_round_value = ( round_value / 2 );
			} else if ( round_type == 25 ) { //Average (Partial Min. Down)
				tmp_round_value = Global.roundTime( ( round_value / 2 ), 60, 10 ); //This is opposite rounding
			} else if ( round_type == 27 ) { //Average (Partial Min. Up)
				tmp_round_value = Global.roundTime( ( round_value / 2 ), 60, 30 );
			}

			if ( epoch > 0 ) {
				//When doing a 15min average rounding, US law states 7mins and 59 seconds can be rounded down in favor of the employer, and 8mins and 0 seconds must be rounded up.
				//So if the round interval is not an even number, round it up to the nearest minute before doing the calculations to avoid issues with seconds.
				epoch = ( Math.floor( ( epoch + tmp_round_value ) / round_value ) * round_value );
			} else {
				epoch = ( Math.ceil( ( epoch - tmp_round_value ) / round_value ) * round_value );
			}

			break;
		case 30: //Up
			epoch = ( ( ( epoch + ( round_value - 1 ) ) / round_value ) * round_value );
			break;
	}

	return epoch;
},

	Global.parseTimeUnit = function( time_unit, format ) {
		var format, time_unit, time_units, seconds, negative_number;

		var time_unit = time_unit.toString(); //Needs to be a string so we can use .charAt and .replace below.

		if ( !format ) {
			format = LocalCacheData.getLoginUserPreference().time_unit_format;
		}
		format = parseInt( format );

		var enable_rounding = true;
		if ( time_unit.charAt( 0 ) == '"' ) {
			enable_rounding = false;
		}

		var thousands_separator = ',';
		var decimal_separator = '.';

		time_unit = time_unit.replace( new RegExp( thousands_separator, 'g' ), '' ).replace( new RegExp( ' ', 'g' ), '' ).replace( new RegExp( '"', 'g' ), '' ); //Need to use regex to replace all instances.

		switch ( format ) {
			case 10: //hh:mm
			case 12: //hh:mm:ss
				if ( time_unit.indexOf( decimal_separator ) !== -1 && time_unit.indexOf( ':' ) === -1 ) { //Hybrid mode, they passed a decimal format HH:MM, try to handle properly.
					//time_unit = Global.getTimeUnit( Global.parseTimeUnit( time_unit, 20 ), format );
					seconds = Global.parseTimeUnit( time_unit, 20 ); //Parse directly to seconds, this avoids rounding to the nearest fraction of an hour.
				} else {
					time_units = time_unit.split( ':' );

					if ( !time_units[0] ) {
						time_units[0] = 0;
					}

					if ( !time_units[1] ) {
						time_units[1] = 0;
					}

					if ( !time_units[2] ) {
						time_units[2] = 0;
					} else {
						if ( time_units[2] != 0 ) {
							enable_rounding = false; //Since seconds were specified, don't round to nearest minute.
						}
					}

					negative_number = false;
					if ( time_units[0].toString().charAt( 0 ) == '-' || time_units[0] < 0 || time_units[1] < 0 || time_units[2] < 0 ) {
						negative_number = true;
					}

					seconds = ( ( Math.abs( Math.floor( time_units[0] ) ) * 3600 ) + ( Math.abs( Math.floor( time_units[1] ) ) * 60 ) + Math.abs( time_units[2] ) ); //Allow seconds to be a decimal.

					if ( negative_number == true ) {
						seconds = ( seconds * -1 );
					}
				}
				break;
			case 20: //hours
			case 22: //hours [Precise]
			case 23: //hours [Super Precise]
				if ( time_unit.indexOf( ':' ) !== -1 ) { //Hybrid mode, they passed a decimal format HH:MM, try to handle properly.
					//time_unit = Global.getTimeUnit( Global.parseTimeUnit( time_unit, 23 ), format );
					seconds = Global.parseTimeUnit( time_unit, 10 ); //Parse directly to seconds, this avoids rounding to the nearest fraction of an hour.
				} else {
					seconds = ( time_unit * 3600 );
				}
				break;
			case 30: //minutes
				seconds = ( time_unit * 60 );
				break;
			case 40: //seconds
				seconds = time_unit;

				//Always allow decimal with seconds when parsing, so we can properly handle accrual balances with fractions of a second.
				// if ( enable_rounding == true ) {
				// 	seconds = Math.round( seconds ); //Round to nearest whole number by default.
				// }

				enable_rounding = false; //Since seconds were specified, don't round to nearest minute. Also for accruals might need to allow decimal seconds.
				break;
		}

		// Add check to prevent scientific notation which could occur with: Global.parseTimeUnit( '999999999999999999', 10 )
		if ( Math.abs( seconds ) > Number.MAX_SAFE_INTEGER ) {
			return Number.MAX_SAFE_INTEGER;
		}

		if ( enable_rounding == true ) {
			seconds = Global.roundTime( seconds, 60 );
		}

		//Debug.Text( 'Time Unit: '+ time_unit +' Retval: '+ seconds, 'Global.js', '', 'parseTimeUnit', 10 );
		return seconds;
	},

	Global.convertSecondsToHMS = function( seconds, include_seconds, exclude_hours ) {
		var negative_number = false;

		if ( seconds < 0 ) {
			negative_number = true;
		}

		seconds = Math.abs( seconds );
		var tmp_hours = Math.floor( seconds / 3600 );
		var tmp_minutes = Math.floor( ( seconds / 60 ) % 60 );
		var tmp_seconds = Decimal( seconds ).mod( 60 );

		if ( exclude_hours == true ) { //Convert hours to minutes before we pad it.
			tmp_minutes = ( ( tmp_hours * 60 ) + tmp_minutes );
			tmp_hours = 0;
		}

		if ( tmp_hours < 10 ) {
			tmp_hours = '0' + tmp_hours;
		}

		if ( tmp_minutes < 10 ) {
			tmp_minutes = '0' + tmp_minutes;
		}

		if ( tmp_seconds < 10 ) {
			tmp_seconds = '0' + tmp_seconds;
		}

		var retval;
		if ( exclude_hours == true ) {
			retval = [tmp_minutes, tmp_seconds].join( ':' );
		} else {
			if ( include_seconds == true ) {
				retval = [tmp_hours, tmp_minutes, tmp_seconds].join( ':' );
			} else {
				retval = [tmp_hours, tmp_minutes].join( ':' );
			}
		}

		if ( negative_number == true ) {
			retval = '-' + retval;
		}

		return retval;
	},

//Was: Global.secondToHHMMSS
	Global.getTimeUnit = function( seconds, format ) {
		var retval;

		//always return hh:ss. if we can't parse to float, then work with 0 tmp_seconds
		var seconds = parseFloat( seconds );
		if ( isNaN( seconds ) ) {
			seconds = 0;
		}

		//FIXES BUG#2071 - don't check the local cache data for default value, or it will fail and cause errors when unauthenticated. For example in the installer.
		var format;
		if ( !format ) {
			if ( LocalCacheData.getLoginUserPreference() ) {
				format = LocalCacheData.getLoginUserPreference().time_unit_format;
			} else {
				format = 10;
			}
		}
		format = parseInt( format );

		switch ( format ) {
			case 10:
				retval = Global.convertSecondsToHMS( seconds );
				break;
			case 12:
				retval = Global.convertSecondsToHMS( seconds, true );
				break;
			case 99: //For local use only, in progress bar always show tmp_minutes and tmp_seconds
				retval = Global.convertSecondsToHMS( Math.floor( seconds ), true, true );
				break;
			case 20:
				retval = ( seconds / 3600 ).toFixed( 2 );
				break;
			case 22:
				retval = ( seconds / 3600 ).toFixed( 3 );
				break;
			case 23:
				retval = ( seconds / 3600 ).toFixed( 4 );
				break;
			case 30:
				retval = ( seconds / 60 ).toFixed( 0 );
				break;
			case 40:
				retval = seconds;
				break;
		}

		//Debug.Text( 'Seconds: '+ seconds +' Retval: '+ retval, 'Global.js', '', 'getTimeUnit', 10 );
		return retval;
	};

Global.removeTrailingZeros = function( value, minimum_decimals ) {
	if ( !minimum_decimals ) {
		minimum_decimals = 2;
	}
	if ( value ) {
		value = parseFloat( value ); // first to remove the zero after the point.

		var trimmed_value = value.toString();

		if ( trimmed_value.indexOf( '.' ) > 0 ) {
			// If after removed has the point, then reverse it.
			var tmp_minimum_decimals = parseInt( trimmed_value.split( '' ).reverse().join( '' ) ).toString().length;
			if ( tmp_minimum_decimals >= minimum_decimals && tmp_minimum_decimals <= 4 ) {
				minimum_decimals = tmp_minimum_decimals;
			}

		}

		return value.toFixed( minimum_decimals );
	}

	return value;
};

/* jshint ignore:end */

Global.isCanvasSupported = function() {
	var elem = document.createElement( 'canvas' );
	return !!( elem.getContext && elem.getContext( '2d' ) );
};

Global.getRandomNum = function() {

	var number = Math.floor( Math.random() * 999 );//0-23

	return number;

};

/* jshint ignore:start */

Global.getScriptNameByAPI = function( api_class ) {

	if ( !api_class || !api_class.className ) {
		return null;
	}

	var script_name = '';
	switch ( api_class.className ) {
		case 'APIUser':
			script_name = 'EmployeeView';
			break;
		case 'APIBranch':
			script_name = 'BranchView';
			break;
		case 'APIDepartment':
			script_name = 'DepartmentView';
			break;
		case 'APIUserWage':
			script_name = 'WageView';
			break;
		case 'APIUserContact':
			script_name = 'UserContactView';
			break;
		case 'APIUserTitle':
			script_name = 'UserTitleView';
			break;
		case 'APIWageGroup':
			script_name = 'WageGroupView';
			break;
		case 'APILog':
			script_name = 'LogView';
			break;
		case 'APIUserGroup':
			script_name = 'UserGroupView';
			break;
		case 'APIPayStubEntryAccount':
			script_name = 'PayStubEntryAccountView';
			break;
		case 'APIPayStubEntryAccountLink':
			script_name = 'PayStubEntryAccountLinkView';
			break;
		case 'APIPayPeriod':
		case 'APIPayPeriodSchedule':
			script_name = 'PayPeriodsView';
			break;
		case 'APIAccrual':
			script_name = 'APIAccrual';
			break;
		case 'APIAccrualBalance':
			script_name = 'AccrualBalanceView';
			break;
		case 'APIException':
			script_name = 'ExceptionView';
			break;
		case 'APIJobGroup':
			script_name = 'JobGroupView';
			break;
		case 'APIJob':
			script_name = 'JobView';
			break;
		case 'APIJobItemGroup':
			script_name = 'JobItemGroupView';
			break;
		case 'APIJobItem':
			script_name = 'JobItemView';
			break;
		case 'APIJobItemAmendment':
			script_name = 'JobItemAmendment';
			break;
		case 'APIPunch':
			script_name = 'PunchesView';
			break;
		case 'APIPunchTag':
			script_name = 'PunchTagView';
			break;
		case 'APIPunchTagGroup':
			script_name = 'PunchTagGroupView';
			break;
		case 'APIRecurringScheduleControl':
			script_name = 'RecurringScheduleControlView';
			break;

		case 'APIRecurringScheduleTemplateControl':
			script_name = 'RecurringScheduleTemplateControlView';
			break;
		case 'APISchedule':
			script_name = 'ScheduleShiftView';
			break;
		case 'APIBankAccount':
			script_name = 'BankAccountView';
			break;
		case 'APICompany':
			script_name = 'CompanyView';
			break;
		case 'APICurrency':
			script_name = 'CurrencyView';
			break;
		case 'APICurrencyRate':
			script_name = 'CurrencyRate';
			break;
		case 'APIHierarchyControl':
			script_name = 'HierarchyControlView';
			break;
		case 'APIEthnicGroup':
			script_name = 'EthnicGroupView';
			break;
		case 'APICustomField':
			script_name = 'CustomFieldView';
			break;
		case 'APIPermissionControl':
			script_name = 'PermissionControlView';
			break;
		case 'APIStation':
			script_name = 'StationView';
			break;
		case 'APIDocumentRevision':
			script_name = 'DocumentRevisionView';
			break;
		case 'APIDocumentGroup':
			script_name = 'DocumentGroupView';
			break;
		case 'APIDocument':
			script_name = 'DocumentView';
			break;
		case 'APIROE':
			script_name = 'ROEView';
			break;
		case 'APIUserDefault':
			script_name = 'UserDefaultView';
			break;
		case 'APIUserPreference':
			script_name = 'UserPreferenceView';
			break;
		case 'APIKPI':
			script_name = 'KPIView';
			break;
		case 'APIUserReviewControl':
			script_name = 'UserReviewControlView';
			break;
		case 'APIQualification':
			script_name = 'QualificationView';
			break;
		case 'APIUserEducation':
			script_name = 'UserTitleView';
			break;
		case 'APIUserLanguage':
			script_name = 'UserTitleView';
			break;
		case 'APIUserLicense':
			script_name = 'UserLicenseView';
			break;
		case 'APIUserMembership':
			script_name = 'UserMembershipView';
			break;
		case 'APIUserSkill':
			script_name = 'UserSkillView';
			break;
		case 'APIJobApplicantEducation':
			script_name = 'JobApplicantEducationView';
			break;
		case 'APIJobApplicantEmployment':
			script_name = 'JobApplicantEducationView';
			break;
		case 'APIJobApplicantLanguage':
			script_name = 'JobApplicantLanguageView';
			break;
		case 'APIJobApplicantLicense':
			script_name = 'JobApplicantLicenseView';
			break;
		case 'APIJobApplicantLocation':
			script_name = 'JobApplicantLicenseView';
			break;
		case 'APIJobApplicantMembership':
			script_name = 'JobApplicantMembershipView';
			break;
		case 'APIJobApplicantReference':
			script_name = 'JobApplicantReferenceView';
			break;
		case 'APIJobApplicantSkill':
			script_name = 'JobApplicantSkillView';
			break;
		case 'APIJobApplicant':
			script_name = 'JobApplicantSkillView';
			break;
		case 'APIJobApplication':
			script_name = 'JobApplicationView';
			break;
		case 'APIJobVacancy':
			script_name = 'JobVacancyView';
			break;
		case 'APIAreaPolicy':
			script_name = 'JobVacancyView';
			break;
		case 'APIClient':
			script_name = 'ClientView';
			break;
		case 'APIClientContact':
			script_name = 'ClientContactView';
			break;
		case 'APIClientGroup':
			script_name = 'ClientGroupView';
			break;
		case 'APIClientPayment':
			script_name = 'ClientPaymentView';
			break;
		case 'APIInvoiceDistrict':
			script_name = 'InvoiceDistrictView';
			break;
		case 'APIInvoice':
			script_name = 'InvoiceView';
			break;
		case 'APITransaction':
			script_name = 'InvoiceTransactionView';
			break;
		case 'APIPaymentGateway':
			script_name = 'PaymentGatewayView';
			break;
		case 'APIProductGroup':
			script_name = 'ProductGroupView';
			break;
		case 'APIProduct':
			script_name = 'ProductView';
			break;
		case 'APIInvoiceConfig':
			script_name = 'InvoiceConfigView';
			break;
		case 'APIShippingPolicy':
			script_name = 'ShippingPolicyView';
			break;
		case 'APITaxPolicy':
			script_name = 'TaxPolicyView';
			break;
		case 'APICompanyDeduction':
			script_name = 'CompanyTaxDeductionView';
			break;
		case 'APIPayStub':
			script_name = 'PayStubView';
			break;
		case 'APIPayStubTransaction':
			script_name = 'PayStubTransactionView';
			break;
		case 'APIPayStubEntry':
			script_name = 'PayStubEntryView';
			break;
		case 'APIPayStubAmendment':
			script_name = 'PayStubAmendmentView';
			break;
		case 'APIRecurringPayStubAmendment':
			script_name = 'RecurringPayStubAmendmentView';
			break;
		case 'APIUserExpense':
			script_name = 'UserExpenseView';
			break;
		case 'APILegalEntity':
			script_name = 'LegalEntityView';
			break;
		case 'APIPayrollRemittanceAgency':
			script_name = 'PayrollRemittanceAgencyView';
			break;
		case 'APIPayrollRemittanceAgencyEvent':
			script_name = 'PayrollRemittanceAgencyViewEvent';
			break;
		case 'APIAbsencePolicy':
			script_name = 'AbsencePolicyView';
			break;
		case 'APIAccrualPolicyAccount':
			script_name = 'AccrualPolicyAccountView';
			break;
		case 'APIAccrualPolicy':
			script_name = 'AccrualPolicyView';
			break;
		case 'APIAccrualPolicyUserModifier':
			script_name = 'AccrualPolicyUserModifierView';
			break;
		case 'APIBreakPolicy':
			script_name = 'BreakPolicyView';
			break;
		case 'APIExceptionPolicyControl':
			script_name = 'ExceptionPolicyControlView';
			break;
		case 'APIExpensePolicy':
			script_name = 'ExpensePolicyView';
			break;
		case 'APIHoliday':
			script_name = 'HolidayView';
			break;
		case 'APIHolidayPolicy':
			script_name = 'HolidayPolicyView';
			break;
		case 'APIMealPolicy':
			script_name = 'MealPolicyView';
			break;
		case 'APIOvertimePolicy':
			script_name = 'OvertimePolicyView';
			break;
		case 'APIPolicyGroup':
			script_name = 'PolicyGroupView';
			break;
		case 'APIPremiumPolicy':
			script_name = 'PremiumPolicyView';
			break;
		case 'APIRecurringHoliday':
			script_name = 'RecurringHolidayView';
			break;
		case 'APIRoundIntervalPolicy':
			script_name = 'RoundIntervalPolicyView';
			break;
		case 'APISchedulePolicy':
			script_name = 'SchedulePolicyView';
			break;
		case 'APIUserReportData':
			script_name = 'UserReportDataView';
			break;
		case 'APIInstall':
			script_name = 'InstallView';
			break;
	}

	return script_name;
};

/* jshint ignore:end */

Global.isObject = function( obj ) {
	if ( obj !== null && typeof obj === 'object' ) {
		return true;
	}

	return false;
};

Global.isArray = function( obj ) {

	if ( Object.prototype.toString.call( obj ) !== '[object Array]' ) {
		return false;
	}

	return true;
};

Global.isString = function( obj ) {

	if ( Object.prototype.toString.call( obj ) !== '[object String]' ) {
		return false;
	}

	return true;
};

Global.isValidDate = function( obj ) {
	if ( obj instanceof Date && !isNaN( obj ) ) {
		return true;
	}

	return false;
};

Global.decodeCellValue = function( val ) {
	if ( !val || _.isObject( val ) ) {
		return val;
	}
	val = val.toString();
	val = val.replace( /\n|\r|(\r\n)|(\u0085)|(\u2028)|(\u2029)/g, '<br>' );
	val = val.replace( /\n|\r|(\r\n)|(\u0085)|(\u2028)|(\u2029)/g, '<br>' );
	val = Global.htmlEncode( val );
	val = val.replace( /&lt;br&gt;/g, '<br>' );

	return val;
};

Global.buildTreeRecord = function( array, parentId ) {
	var finalArray = [];

	$.each( array, function( key, item ) {
		item.expanded = true;
		item.loaded = true;

		if ( Global.isSet( parentId ) ) {
			item.parent = parentId;
		}

		finalArray.push( item );

		if ( Global.isSet( item.children ) ) {
			var childrenArray = Global.buildTreeRecord( item.children, item.id );
			finalArray = finalArray.concat( childrenArray );
		} else {
			item.isLeaf = true;
		}

	} );

	return finalArray;
};

Global.getParentIdByTreeRecord = function( array, selectId ) {

	var retval = [];
	for ( var i = 0; i < array.length; i++ ) {
		var item = array[i];
		if ( item.id.toString() === selectId.toString() ) {
			var new_row = {};
			if ( typeof item.parent != 'undefined' ) {
				new_row = { parent_id: item.parent.toString(), name: item.name };
			} else {
				new_row = { name: item.name };
			}

			//Without created and updated info, audit tab shows N/A for both
			if ( typeof item.created_by != 'undefined' ) {
				new_row.created_by = item.created_by;
				new_row.created_date = item.created_date;
				new_row.updated_by = item.updated_by;
				new_row.updated_date = item.updated_date;
			}

			retval.push( new_row );
			break;
		}
	}

	return retval;

};

Global.addFirstItemToArray = function( array, firstItemType, customLabel ) {
	//Error: Unable to get property 'unshift' of undefined or null reference in /interface/html5/global/Global.js?v=8.0.0-20141230-153942 line 903
	var label;
	if ( array ) {
		if ( firstItemType === 'any' ) {
			if ( customLabel ) {
				label = customLabel;
			} else {
				label = Global.any_item;
			}
			//#2301 - don't duplicate the --Any-- case when the array is recycled.
			if ( !array[0] || array[0].value != TTUUID.not_exist_id ) {
				array.unshift( {
					label: label,
					value: TTUUID.not_exist_id,
					fullValue: TTUUID.not_exist_id,
					orderValue: ''
				} );
			}
		} else if ( firstItemType === 'empty' ) {
			if ( customLabel ) {
				label = customLabel;
			} else {
				label = Global.empty_item;
			}
			//#2301 - don't duplicate the --None-- case when the array is recycled.
			if ( !array[0] || array[0].value != TTUUID.zero_id ) {
				array.unshift( {
					label: label,
					value: TTUUID.zero_id,
					fullValue: TTUUID.zero_id,
					orderValue: ''
				} );
			}
		}
	}

	return array;
};

//Add item on to the end of the array, but make sure its not already there and therefore never duplicated.
Global.addLastItemToArray = function( array, key, label ) {
	var label;
	if ( array ) {
		var last_array_element = array[( array.length - 1 )];
		if ( last_array_element.value != key ) {
			array.push( {
				fullValue: key,
				value: key,
				label: label,
				id: 2000
			} );
		}
	}

	return array;
};

Global.convertRecordArrayToOptions = function( array ) {
	var len = array.length;
	var options = {};

	for ( var i = 0; i < len; i++ ) {
		var item = array[i];

		options[item.value] = item.label;
	}

	return options;
};

Global.buildColumnArray = function( array ) {
	var columns = [];
	var id = 1000;

	for ( var key in array ) {
		var order_value = Global.getSortValue( key, true );
		var column = {
			label: array[key],
			value: Global.removeSortPrefix( key ),
			orderValue: order_value,
			id: id
		};
		columns.push( column );
		id = id + 1;
	}
	return columns;
};

Global.removeSortPrefixFromArray = function( array ) {
	var finalArray = {};

	if ( Global.isSet( array ) ) {

		$.each( array, function( key, item ) {
			finalArray[Global.removeSortPrefix( key )] = item;
		} );

		return finalArray;
	}

	return array;
};

Global.removeSortPrefix = function( key ) {
	if ( typeof key == 'string' && key.match( Global.sortOrderRegex ) ) {
		key = key.replace( Global.sortOrderRegex, '' );
	}
	return key;
};

Global.getSortValue = function( key, return_key_on_null ) {
	var order_value = 999;
	if ( typeof key == 'string' ) {
		var regex_result = key.match( Global.sortOrderRegex );
		if ( regex_result == null ) {
			if ( return_key_on_null === true ) {
				order_value = key;
			}
		} else if ( regex_result[1] ) {
			order_value = regex_result[1];
		} else {
			Debug.Error( 'Error: Unable to parse order_value', 'Global', 'Global', 'buildColumnArray', 10 );
		}
	}
	return order_value;
};

Global.convertToNumberIfPossible = function( val ) {
	//if value is number convert to number type
	var reg = new RegExp( '^[0-9]*$' );

	if ( reg.test( val ) && val !== '00' ) {
		val = parseFloat( val );
	}

	if ( val === '-1' || val === -1 ) {
		val = -1;
	}

	return val;
};

Global.buildRecordArray = function( array, first_item, orderType ) {
	var finalArray = [];

	if ( first_item ) {
		finalArray.push( first_item );
	}

	var id = 1000;

	if ( Global.isSet( array ) ) {

		for ( var key in array ) {
			var item = array[key];
			var value = Global.removeSortPrefix( key );
			var order_value = Global.getSortValue( key );

			// 6/4 changed id to same as value to make flex show correct data when show search result saved in html5, flex use id if it existed.
			var record = { label: item, value: value, fullValue: key, orderValue: order_value, id: value };

			id = id + 1;

			finalArray.push( record );

		}

	}

	return finalArray;

};

Global.topContainer = function() {
	return $( '#topContainer' );
};

Global.overlay = function() {
	return $( '#overlay' );
};

Global.bottomContainer = function() {
	return $( '#bottomContainer' );
};

Global.bottomFeedbackLinkContainer = function() {
	return $( '#feedbackLinkContainer' );
};

Global.showPoweredBy = function() {
	var powered_by_img = $( '#powered_by' );
	powered_by_img.show();
	powered_by_img.attr( 'src', ServiceCaller.getURLByObjectType( 'copyright' ) );
	powered_by_img.attr( 'alt', LocalCacheData.loginData.application_name + ' Workforce Management Software' );
	var powered_by_link = $( '<a target="_blank" href="https://' + LocalCacheData.getLoginData().organization_url + '"></a>' );
	powered_by_link.addClass( 'powered-by-img-seo' );
	powered_by_img.wrap( powered_by_link );
};

Global.setSignalStrength = function() {
	if ( Global.signal_timer ) {
		return;
	}
	$( '.signal-strength' ).css( 'display', 'block' );
	var status = '......';
	var average_time = 0;
	var checking_array = [];
	var single_strength = null;
	var single_strength_tooltip = null;

	setTooltip();

	setTimeout( function() {
		doPing();
	}, 10000 );
	Global.signal_timer = setInterval( function() {
		doPing();
	}, 60000 );

	function doPing() {
		if ( ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.viewId === 'LoginView' ) || Global.idle_time >= Math.min( 15, APIGlobal.pre_login_data.session_idle_timeout / 60 ) ) {
			return;
		}

		ping( ServiceCaller.base_url + 'interface/ping.html?t=' + new Date().getTime(), function( time ) {
			$( '.signal-strength-empty' ).removeClass( 'signal-strength-empty' );

			if ( checking_array.length >= 3 ) {
				checking_array.shift();
			}
			checking_array.push( time );
			var total_time = 0;
			for ( var i = 0; i < checking_array.length; i++ ) {
				total_time = checking_array[i] + total_time;
			}
			average_time = total_time / checking_array.length;
			Debug.Text( 'Current Ping: ' + time + 'ms Average: ' + average_time + 'ms Date: ' + ( new Date ).toISOString().replace( /z|t/gi, ' ' ), 'Global.js', '', 'doPing', 6 );
			Global.current_ping = average_time;
			status = $.i18n._( 'Good' );
			//do not allow signal strength variation in unit test mode
			if ( Global.UNIT_TEST_MODE == false ) {
				if ( average_time > 400 ) {
					$( '.signal-strength-pretty-strong' ).addClass( 'signal-strength-empty' );
					$( '.signal-strength-strong' ).addClass( 'signal-strength-empty' );
					$( '.signal-strength-weak' ).addClass( 'signal-strength-empty' );
					status = $.i18n._( 'Poor' );
				} else if ( average_time > 250 ) {
					$( '.signal-strength-pretty-strong' ).addClass( 'signal-strength-empty' );
					$( '.signal-strength-strong' ).addClass( 'signal-strength-empty' );
					status = $.i18n._( 'Below Average' );
				} else if ( average_time > 150 ) {
					$( '.signal-strength-pretty-strong' ).addClass( 'signal-strength-empty' );
					status = $.i18n._( 'Average' );
				}
			}

			setTooltip();

		} );
	}

	function setTooltip() {
		var html = '<div>' + $.i18n._( 'Your Network Connection is' ) + ' ' + status + ' (' + $.i18n._( 'Latency' ) + ': ' + ( average_time > 0 ? average_time.toFixed( 0 ) + 'ms' : $.i18n._( 'Calculating...' ) ) + ')' + '</div>';
		$( '.signal-strength' ).qtip( {
			id: 'single_strength',
			content: {
				text: html
			},
			position: {
				my: 'bottom left',
				at: 'top right'
			}
		} );
	}

	function ping( url, callback ) {
		var inUse, start, img, timer;
		if ( !inUse ) {
			inUse = true;
			img = new Image();
			img.onload = function() {
				var endTime = new Date().getTime();
				inUse = false;
				callback( ( endTime - start ) );

			};
			img.onerror = function( e ) {
				if ( inUse ) {
					inUse = false;
					var endTime = new Date().getTime();
					callback( ( endTime - start ) );
				}

			};
			start = new Date().getTime();
			img.src = url;
			timer = setTimeout( function() {
				if ( inUse ) {
					var endTime = new Date().getTime();
					inUse = false;
					callback( ( endTime - start ) );
				}
			}, 5000 );
		}
	}
};

Global.contentContainer = function() {
	return $( '#contentContainer' );
};

Global.bodyWidth = function() {
	return $( window ).width();
};

Global.bodyHeight = function() {
	return $( window ).height();
};

Global.hasRequireLoaded = function( script_path ) {
	var split_script_path = script_path.split( '/' );

	var id = split_script_path[split_script_path.length - 1];
	id = id.replace( '.js', '' );

	//Check alternative script names (ie: with/without the .js) when a full path is specified to see if it was loaded in different ways with requireJS and make sure its not loaded twice.
	if ( script_path.indexOf( '.js' ) == -1 ) {
		var alternative_script_path = script_path + '.js';
	} else {
		var alternative_script_path = script_path.replace( '.js', '' );
	}

	//Make sure the function is both specified and defined. This helps cases where the user is on a Slow 3G network and double clicks Attendance -> In/Out.
	//  In this case the same InOutViewController.js file is in the process of being loaded, then is cancelled,
	//  and another one tries to load and the success callback where the class is instantiated is called before it can be instantiated, causing a JS exception (ReferenceError: InOutViewController is not defined).
	//  Better double-click prevention would also help.
	// if ( typeof require === 'function' && typeof require.specified === 'function' && ( require.specified( id ) || require.specified( script_path ) || require.specified( alternative_script_path ) ) ) {
	// if ( typeof require === 'function' && typeof require.defined === 'function' && ( require.defined( id ) || require.defined( script_path ) || require.defined( alternative_script_path ) ) ) {
	// 	return true;
	// //}

	return false;
};

Global.loadScript = function( scriptPath, onResult ) {
	if ( typeof scriptPath !== 'string' ) {
		// Not ideal fix but this is to handle the scriptPath.split is not a function error in #2696. if the path is not a string, split does not exist as a function.
		// Hard to find root-cause/reproduce, so this fix is to reduce the occurances of the JS exceptions related to it.
		return false;
	}

	var async = true;
	if ( typeof ( onResult ) === 'undefined' ) {
		async = false;
	}

	if ( Global.hasRequireLoaded( scriptPath ) ) {
		if ( async ) {
			onResult();
		}
		return true;
	}

	//Ensures that the js cached scripts are not loaded twice
	if ( async ) {
		if ( LocalCacheData.loadedScriptNames[scriptPath] ) {
			onResult();
			return;
		}
	} else {
		if ( LocalCacheData.loadedScriptNames[scriptPath] ) {
			return true;
		}
	}

	var successflag = false;

	var realPath = scriptPath;

	// Mainly used in the async code, but put here to also catch duplicate declared classes in both async and synchronous calls.
	var split_script_path = realPath.split( '/' );
	var import_file_name = split_script_path[split_script_path.length - 1].replace( '.js', '' );
	//var import_path = realPath.replace('views/', '');

	//var class_exists = eval("typeof "+ import_file_name +" === 'function'");
	var class_exists = typeof window[import_file_name] === 'function';
	if ( class_exists ) {
		// This means class already exists on the window object, so it must have been already loaded.
		// DEV NOTE: This should NOT happen. If it happens, it means script is being loaded twice. Check manual loading calls like requirejs or Webpack MergeIntoSingleFilePlugin plugin
		// In all likelyhood, it is listed in the concatenation array for MergeIntoSingleFilePlugin. Best to try to remove it from there, as long as its correctly loaded on demand in all relevant places. See what else uses the class to be sure.
		Global.sendAnalyticsEvent( 'error:scriptload:duplicate_class', 'load', 'error:scriptload:duplicate:'+ scriptPath );
		Debug.Error( 'Duplicate class declaration: '+ import_file_name, 'Global.js', 'Global', 'loadScript', 1 );
		return true;
	}

	if ( Global.url_offset ) {
		realPath = Global.getBaseURL( Global.url_offset + realPath );
	}

	if ( async ) {
		Debug.Text( 'ASYNC-LOADING: ' + scriptPath, 'Global.js', 'Global', 'loadScript', 10 );

		var import_path;
		if ( scriptPath.indexOf('views') !== -1 ) {
			import_path = scriptPath.replace( 'views/', '' ).replace( '.js', '' ); // This is to ensure the variable in the dynamic webpack import() is a single variable rather than a full path.
			import( `@/views/${import_path}` ).then( ( module ) => {
				if ( module && module[import_file_name] ) {
					window[import_file_name] = module[import_file_name]; // After html2js this may not be needed anymore. But leave for now as this allows the legacy html files to trigger the 'new MyViewController()' code in their html files.

					LocalCacheData.loadedScriptNames[scriptPath] = true;
					onResult();
				} else {
					if ( import_file_name === 'debugPanelController' ) {
						// debugPanel is coded different, with no classes/constructor, so this is not a fail.
						LocalCacheData.loadedScriptNames[scriptPath] = true;
						onResult();
					} else {
						// Loading class failed.
						// If there is not an attribute matching the class on the module result, then this suggests a missing export on the class. There will also be a default attribute with an empty object to show no default classes exported.
						Debug.Error( 'Loading view class failed. Potential missing export for: ' + import_file_name, 'Global.js', 'Global', 'loadScript', 1 );

						onResult(); // To allow callbacks to work for non-module scripts like debugPanelController.
					}
				}
			} ).catch( Global.importErrorHandler );
		} else if ( scriptPath.indexOf('global/widgets') !== -1 ) {
			Debug.Text( 'SYNC-LOADING: ' + scriptPath, 'Global.js', 'Global', 'loadScript', 10 );
			import_path = scriptPath.replace('global/widgets/', '').replace( '.js', '' ); // This is to ensure the variable in the dynamic webpack import() is a single variable rather than a full path.
			import( `@/global/widgets/${import_path}` ).then( ( module ) => {
				if ( module && module[import_file_name] ) {
					window[import_file_name] = module[import_file_name];
					LocalCacheData.loadedScriptNames[scriptPath] = true;
					onResult();
				} else {
					// Loading class failed.
					// If there is not an attribute matching the class on the module result, then this suggests a missing export on the class. There will also be a default attribute with an empty object to show no default classes exported.
					// This could also be a widget that is historically meant to load synchronously with the jQuery.ajax code further down. If this is the case, refactor the callback to load the widget syncronously instead.
					Debug.Error( 'Loading widget class failed. Potential missing export for: ' + import_file_name, 'Global.js', 'Global', 'loadScript', 1 );
				}
			} ).catch( Global.importErrorHandler );
		} else {
			Debug.Error( 'Loading class failed. Unhandled file type path request: '+ scriptPath, 'Global.js', 'Global', 'loadScript', 1 );
		}

	} else {
		var calling_script = '';
		if ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.viewId ) {
			calling_script = ' from ' + LocalCacheData.current_open_primary_controller.viewId + 'ViewController';
		}
		Debug.Text( 'SYNC-LOADING: ' + scriptPath + calling_script );

		var id = scriptPath.split( '/' );
		var id = id[id.length - 1];
		id = id.replace( '.js', '' );
		if ( !window.badScripts ) {
			window.badScripts = [];
		}
		window.badScripts.push( id ); //When the page is done loading punch "badScripts into the console to see a nice array of all the scripts that were not loaded async.

		/**
		 * this seems to work, but causes the script erro at line 0 problem.
		 * try to refactor to not use jquery.ajax
		 */
		jQuery.ajax( {
			async: false,
			type: 'GET',
			url: realPath + '?v=' + APIGlobal.pre_login_data.application_build,
			crossOrigin: false,
			data: null,
			cache: true,
			success: function() {
				successflag = true;
				if ( async ) {
					LocalCacheData.loadedScriptNames[scriptPath] = true;
					onResult();
				}
			},
			error: function( jqXHR, textStatus, errorThrown ) {
				TAlertManager.showNetworkErrorAlert( jqXHR, textStatus, errorThrown );
			},
			dataType: 'script'
		} );
	}

	if ( !async ) {
		LocalCacheData.loadedScriptNames[scriptPath] = true;
		return ( successflag );
	}

};

Global.importErrorHandler = function( error ) {
	if ( error.message.toLowerCase().includes( '404' ) || error.message.toLowerCase().includes( 'not found' ) || error.message.toLowerCase().includes( 'failed' ) || error.message.toLowerCase().includes( 'unknown' ) ) {
		if ( window.script_error_shown === undefined ) {
			window.script_error_shown = 1;
			//There is no pretty errorbox at this time. You may only have basic javascript.
			//if ( confirm( 'Unable to download required data. Your internet connection may have failed. Click Ok to reload.' ) ) {
			if ( confirm( 'A new version of TimeTrex is available. Click OK to update and reload.' ) ) { //Hardcode name, as if a internet connection failure is occurring, might localcachedata might not be populated. Also unlikely to be able to translate this string.
				//For testing, so that there's time to turn internet back on after confirm is clicked.
				//window.setTimeout(function() {window.location.reload()},5000);

				//This can also happen if the user manually modifies the URL to be a bogus ViewId (ie: #!m=homeABC)
				//So try to redirect back to the home page first, otherwise try to do a browser reload.
				if ( ServiceCaller.root_url && APIGlobal.pre_login_data.base_url ) {
					Global.setURLToBrowser( ServiceCaller.root_url + APIGlobal.pre_login_data.base_url );
				} else {
					window.location.reload();
				}

			}
		}
		console.debug( error.message );
		//Stop error from bubbling up.
		// delete e; // commented out from old code as webpack complains about deleting local variable in strict mode.
	} else {
		Debug.Error( 'Error loading script during import(): ' + error, 'Global.js', 'Global', 'importErrorHandler', 1 );
		// Throw general error?
	}
};

Global.getRealImagePath = function( path ) {

	var realPath = 'theme/' + Global.theme + '/' + path;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	return realPath;
};

Global.getRibbonIconRealPath = function( icon ) {
	var realPath = 'theme/' + Global.theme + '/css/global/widgets/ribbon/icons/' + icon;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	return realPath;
};

Global.loadLanguage = function( name ) {
	var successflag = false;
	var message_id = TTUUID.generateUUID();
	ProgressBar.showProgressBar( message_id );
	var res_data = {};

	if ( LocalCacheData.getI18nDic() ) {
		ProgressBar.removeProgressBar( message_id );
		return LocalCacheData.getI18nDic();
	}

	var realPath = '../locale/' + name + '/LC_MESSAGES/messages.json' + '?v=' + APIGlobal.pre_login_data.application_build;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	jQuery.ajax( {
		async: false,
		type: 'GET',
		url: realPath,
		data: null,
		cache: true,
		converters: {
			//Because this is a dataType: script, and jquery will blindy try to eval() any result returned by the server, including a HTML 404 error message.
			// resulting in" Uncaught SyntaxError: Unexpected token < in  line 1" being triggered.
			// Instead just return the raw result and eval() it in the success function ourselves instead.
			'text script': function( text ) {
				return text;
			}
		},
		success: function( result ) {
			if ( result.startsWith( 'i18n_dictionary' ) == true ) { //Check to make sure the text returned is actually a the javascript variable we are expecting and not some server error message.
				jQuery.globalEval( result );
				if ( window.i18n_dictionary !== undefined && Global.isObject( i18n_dictionary ) ) {
					successflag = true;
				} else {
					Debug.Text( 'Unable to eval Locale dictionary: '+ name, 'Global.js', '', 'loadLanguage', 10 );
				}
			} else {
				Debug.Text( 'Unable to parse Locale: '+ name, 'Global.js', '', 'loadLanguage', 10 );
			}
		},
		error: function( jqXHR, textStatus, errorThrown ) {
			//Unable to load or parse i18n dictionary. Could be due to a 404 error?
			Debug.Text( 'Unable to load Locale: ' + errorThrown, 'Global.js', '', 'loadLanguage', 10 );
			successflag = false;
		},
		dataType: 'script'
	} );

	ProgressBar.removeProgressBar( message_id );

	if ( successflag ) {
		LocalCacheData.setI18nDic( i18n_dictionary );
	} else {
		Debug.Text( 'i18n Dictionary did not load for Locale: '+ name, 'Global.js', '', 'loadLanguage', 10 );
		LocalCacheData.setI18nDic( {} );
	}

	return successflag;
};

Global.getProductEdition = function() {
	var current_company_data = LocalCacheData.getCurrentCompany();

	if ( current_company_data && current_company_data.product_edition_id ) {
		return current_company_data.product_edition_id;
	}

	return 10; //Community
};

Global.setURLToBrowser = function( new_url ) {
	if ( new_url != window.location.href ) {
		Debug.Text( 'Changing URL to: ' + new_url, 'Global.js', 'Global', 'setURLToBrowser', 9 );
		window.location = new_url;
	}
};

Global.clone = function( obj ) {
	return jQuery.extend( true, {}, obj ); // true means deep clone, omit for shallow, false is not an option
};

Global.getFirstKeyFromObject = function( obj ) {
	for ( var key in obj ) {

		if ( obj.hasOwnProperty( key ) ) {
			return key;
		}

	}
};

Global.getFuncName = function( _callee ) {
	var _text = _callee.toString();
	var _scriptArr = document.scripts;
	for ( var i = 0; i < _scriptArr.length; i++ ) {
		var _start = _scriptArr[i].text.indexOf( _text );
		if ( _start !== -1 ) {
			if ( /^function\s*\(.*\).*\r\n/.test( _text ) ) {
				var _tempArr = _scriptArr[i].text.substr( 0, _start ).split( '\r\n' );
				return _tempArr[( _tempArr.length - 1 )].replace( /(var)|(\s*)/g, '' ).replace( /=/g, '' );
			} else {
				return _text.match( /^function\s*([^\(]+).*\r\n/ )[1];
			}
		}
	}
};

Global.concatArraysUniqueWithSort = function( thisArray, otherArray ) {
	var newArray = thisArray.concat( otherArray ).sort( function( a, b ) {
		return a > b ? 1 : a < b ? -1 : 0;
	} );

	return newArray.filter( function( item, index ) {
		return newArray.indexOf( item ) === index;
	} );
};

Global.addCss = function( path, callback ) {
	if ( LocalCacheData.loadedScriptNames[path] ) {
		if ( callback ) {
			callback();
		}
		return true;
	}
	LocalCacheData.loadedScriptNames[path] = true;
	var realPath = 'theme/' + Global.theme + '/css/' + path;
	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}
	realPath = realPath + '?v=' + APIGlobal.pre_login_data.application_build;
	Global.loadStyleSheet( realPath, callback );
};

//JS think 0 is false, so use this to get 0 correctly.
Global.isFalseOrNull = function( object ) {

	if ( object === false || object === null || object === 0 || object === '0' || object == TTUUID.zero_id ) {
		return true;
	} else {
		return false;
	}

};

Global.isSet = function( object ) {

	if ( _.isUndefined( object ) || _.isNull( object ) ) {
		return false;
	} else {
		return true;
	}

};

Global.getIconPathByContextName = function( id ) {

	switch ( id ) {
		case 'add':
			return Global.getRealImagePath( 'css/global/widgets/ribbon/icons/copy-35x35.png' );
	}
};

Global.isEmpty = function( obj ) {

	// null and undefined are "empty"
	if ( obj === null ) {
		return true;
	}

	// Assume if it has a length property with a non-zero value
	// that that property is correct.
	if ( obj.length > 0 ) {
		return false;
	}
	if ( obj.length === 0 ) {
		return true;
	}

	// Otherwise, does it have any properties of its own?
	// Note that this doesn't handle
	// toString and valueOf enumeration bugs in IE < 9
	for ( var key in obj ) {
		if ( hasOwnProperty.call( obj, key ) ) {
			return false;
		}
	}

	return true;

};

Global.convertColumnsTojGridFormat = function( columns, layout_name, setWidthCallBack ) {
	var column_info_array = [];
	var len = columns.length;

	var total_width = 0;
	for ( var i = 0; i < len; i++ ) {
		var view_column_data = columns[i];
		var column_info;

		var text_width = Global.calculateTextWidth( view_column_data.label );

		total_width = total_width + text_width;

		if ( view_column_data.label === '' ) {
			column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				key: true,
				width: 100,
				sortable: false,
				hidden: true,
				title: false
			};
		} else if ( layout_name === 'global_sort_columns' ) {

			if ( view_column_data.value === 'sort' ) {
				column_info = {
					name: view_column_data.value,
					index: view_column_data.value,
					label: view_column_data.label,
					width: 100,
					sortable: false,
					formatter: 'select',
					editable: true,
					title: false,
					edittype: 'select',
					editoptions: { value: 'asc:ASC;desc:DESC' }
				};
			} else {
				column_info = {
					name: view_column_data.value,
					index: view_column_data.value,
					label: view_column_data.label,
					width: 100,
					sortable: false,
					title: false
				};
			}

		} else {
			column_info = {
				name: view_column_data.value,
				index: view_column_data.value,
				label: view_column_data.label,
				width: 100,
				sortable: false,
				title: false
			};
		}

		column_info_array.push( column_info );
	}

	if ( setWidthCallBack ) {
		setWidthCallBack( total_width );
	}

	return column_info_array;
};
/* jshint ignore:start */
Global.loadWidgetByName = function( widgetName, raw_text ) {
	var input = false;
	var widget_path = false;
	var widget_constructor = false;
	var raw_text = false;
	switch ( widgetName ) {
		case FormItemType.COLOR_PICKER:
			input = $.fn.TColorPicker.html_template;
			break;
		case FormItemType.FORMULA_BUILDER:
			input = $.fn.FormulaBuilder.html_template;
			break;
		case FormItemType.AWESOME_BOX:
			input = $.fn.AComboBox.html_template;
			break;
		case FormItemType.AWESOME_DROPDOWN:
			input = $.fn.ADropDown.html_template;
			break;
		case FormItemType.TEXT_INPUT:
			input = $.fn.TTextInput.html_template;
			break;
		case FormItemType.PASSWORD_INPUT:
			input = $.fn.TPasswordInput.html_template;
			break;
		case FormItemType.TEXT:
			input = $.fn.TText.html_template;
			break;
		case FormItemType.CHECKBOX:
			input = $.fn.TCheckbox.html_template;
			break;
		case FormItemType.COMBO_BOX:
			input = $.fn.TComboBox.html_template;
			break;
		case FormItemType.LIST: //Does not seem to be used anywhere.
			input = $.fn.TList.html_template;
			break;
		case FormItemType.TAG_INPUT:
			input = $.fn.TTagInput.html_template;
			break;
		case FormItemType.DATE_PICKER:
		case FormItemType.RANGE_PICKER:
			input = $.fn.TDatePicker.html_template;
			break;
		case FormItemType.TIME_PICKER:
			input = $.fn.TTimePicker.html_template;
			break;
		case FormItemType.TEXT_AREA:
			input = $.fn.TTextArea.html_template;
			break;
		case FormItemType.TINYMCE_TEXT_AREA:
			input = $.fn.TTextArea.tinymce_html_template;
			raw_text = true;
			break;
		case FormItemType.SEPARATED_BOX:
			input = $.fn.SeparatedBox.html_template;
			break;
		case FormItemType.IMAGE_BROWSER:
			input = $.fn.TImageBrowser.html_template;
			break;
		case FormItemType.FILE_BROWSER:
			//To show more of a custom file browser and text we set display: none on the actual file browser.
			//The visible custom file browser when clicked will trigger the hidden file browser to open.
			input = `<div class="file-browser">
						<img class="image">
						<form enctype="multipart/form-data" class="browser-form custom-file-browser">
							<button class="p-button p-component tt-button" type="button"><span class="tticon tticon-add_black_24dp p-button-icon p-button-icon-left"></span><span class="p-button-label">Upload</span><span class="p-ink"></span></button>
							<label for="file-browser" class="file-browser-label">${$.i18n._( 'or drag file here.' )}</label>
							<input name="file_data" class="browser" type="file" style="display:none;"/>
						</form>
					</div>`;
			break;
		case FormItemType.IMAGE_AVD_BROWSER:
			input = $.fn.TImageAdvBrowser.html_template;
			widget_constructor = 'TImageAdvBrowser';
			break;
		case FormItemType.CAMERA_BROWSER:
			input = $.fn.CameraBrowser.html_template;
			break;
		case FormItemType.IMAGE_CUT:
			input = $.fn.TImageCutArea.html_template;
			break;
		case FormItemType.IMAGE:
			input = '<img class=\'t-image\'>';
			break;
		case FormItemType.INSIDE_EDITOR:
			input = $.fn.InsideEditor.html_template;
			break;
		case WidgetNamesDic.PAGING:
			// widget_path = 'global/widgets/paging/Paging.html'; // TODO: #3023: Delete this line once all widget html converted and no longer need this quick reference for the old format.
			input = $.fn.Paging2.html.paging;
			break;
		case WidgetNamesDic.PAGING_2:
			// widget_path = 'global/widgets/paging/Paging2.html'; // TODO: #3023: Delete this line once all widget html converted and no longer need this quick reference for the old format.
			input = $.fn.Paging2.html.paging2;
			break;
		case WidgetNamesDic.ERROR_TOOLTIP:
			input = $.fn.ErrorTipBox.html_template;
			break;
		case FormItemType.FEEDBACK_BOX:
			input = $.fn.TFeedback.html_template;
			break;
		case WidgetNamesDic.EDIT_VIEW_FORM_ITEM: //There is no file browser JS file for this widget.
			input = `
			<div class="edit-view-form-item-div">
				<div class="edit-view-form-item-label-div"><span class="edit-view-form-item-label"></span></div>
				<div class="edit-view-form-item-input-div"></div>
			</div>`;
			break;
		case WidgetNamesDic.EDIT_VIEW_SUB_FORM_ITEM: //There is no file browser JS file for this widget.
			input = `
			<div class="edit-view-form-item-div">
				<div class="edit-view-form-item-sub-label-div"><span class="edit-view-form-item-label"></span></div>
				<div class="edit-view-form-item-input-div"></div>
			</div>`;
			break;
		case WidgetNamesDic.NO_RESULT_BOX:
			input = $.fn.NoResultBox.html_template;
			break;
		case WidgetNamesDic.VIEW_MIN_TAB:
			input = $.fn.ViewMinTabBar.html.tab;
			break;
		case WidgetNamesDic.VIEW_MIN_TAB_BAR:
			input = $.fn.ViewMinTabBar.html.tab_bar;
			break;
	}

	if ( widget_path != false ) {
		input = Global.loadWidget( widget_path );
	}

	if ( input && raw_text == true ) {
		return input;
	} else {
		//#2571 - Error: Unable to get property 'indexOf' of undefined or null reference
		if ( input && input.indexOf( '<' ) != -1 ) {
			if ( !raw_text ) {
				input = $( input );

				if ( widget_constructor && !input[widget_constructor] ) {
					var error_string = $.i18n._( 'Class could not be found for' ) + ': ' + widgetName + '. ' + $.i18n._( 'Check that class is properly required.' );
					throw( new Error( error_string ) );
				}
			}
		} else {
			//See comment in Global.loadWidget() regarding return null return values.
			var error_string = $.i18n._( 'Network error, failed to load' ) + ': ' + widgetName + ' ' + $.i18n._( 'Result' ) + ': "' + input + '"';
			TAlertManager.showNetworkErrorAlert( { status: 999 }, error_string, null ); //Show the user an error popoup.
			throw( new Error( error_string ) ); //Halt execution and ensure that the email has a good error message because of failure of web server to provide the requested file.
		}

		return input;
	}
};

/* jshint ignore:end */

Global.loadWidget = function( url ) {
	if ( LocalCacheData.loadedWidgetCache[url] ) {
		return ( LocalCacheData.loadedWidgetCache[url] );
	}

	var realPath = url + '?v=' + APIGlobal.pre_login_data.application_build;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	var message_id = TTUUID.generateUUID();
	ProgressBar.showProgressBar( message_id );
	var successflag = false;
	var responseData = $.ajax( {
		async: false,
		type: 'GET',
		url: realPath,
		data: null,
		cache: true,
		success: function() {
			successflag = true;
		},
		error: function( jqXHR, textStatus, errorThrown ) {
			TAlertManager.showNetworkErrorAlert( jqXHR, textStatus, errorThrown );
		}
	} );

	ProgressBar.removeProgressBar( message_id );
	//Error: Uncaught ReferenceError: responseText is not defined in interface/html5/global/Global.js?v=9.0.2-20151106-092147 line 1747
	//  Upon further investigation (IRC discussions on #jQuery) it was suggested to stop using 'async: false' as that could be whats causing a null return value when we are expecting a jqXHR object.
	//  Since the ultimate goal is to refactor things so .html is embedded in the .js files anyways, may as well just wait for that.
	if ( !responseData ) {
		return null;
	} else {
		LocalCacheData.loadedWidgetCache[url] = responseData.responseText;
		return ( responseData.responseText );
	}

};

Global.removeCss = function( path ) {
	var realPath = 'theme/' + Global.theme + '/css/' + path;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	$( 'link[href=\'\' + realPath + \'?v=\' + APIGlobal.pre_login_data.application_build + \'\']' ).remove();
};

/* jshint ignore:start */

Global.getViewPathByViewId = function( viewId ) {
	var path;
	switch ( viewId ) {
		//Recruitment Portal
		case 'GridTest':
		case 'WidgetTest':
		case 'AwesomeboxTest':
			path = 'views/developer_tools/';
			break;
		case 'MyProfile':
			path = 'views/portal/hr/my_profile/';
			break;
		case 'MyJobApplication':
			path = 'views/portal/hr/my_jobapplication/';
			break;
		case 'MyProfileEmployment':
			path = 'views/portal/hr/my_profile/';
			break;

		case 'Map':
			path = 'views/attendance/map/';
			break;
		case 'ManualTimeSheet':
			path = 'views/attendance/manual_timesheet/';
			break;
		case 'Home':
			path = 'views/home/dashboard/';
			break;
		case 'PortalJobVacancyDetail':
		case 'PortalJobVacancy':
			path = 'views/portal/hr/recruitment/';
			break;
		case 'PortalLogin':
			path = 'views/portal/login/';
			break;
		case 'QuickPunchLogin':
			path = 'views/quick_punch/login/';
			break;
		case 'QuickPunch':
			path = 'views/quick_punch/punch/';
			break;
		case 'UserDateTotalParent':
		case 'UserDateTotal':
			path = 'views/attendance/timesheet/';
			break;
		case 'Product':
			path = 'views/invoice/products/';
			break;
		case 'InvoiceDistrict':
			path = 'views/invoice/district/';
			break;
		case 'PaymentGateway':
			path = 'views/invoice/payment_gateway/';
			break;
		case 'InvoiceConfig':
			path = 'views/invoice/settings/';
			break;
		case 'ShippingPolicy':
			path = 'views/invoice/shipping_policy/';
			break;
		case 'AreaPolicy':
			path = 'views/invoice/area_policy/';
			break;
		case 'TaxPolicy':
			path = 'views/invoice/tax_policy/';
			break;
		case 'ClientGroup':
			path = 'views/invoice/client_group/';
			break;
		case 'ProductGroup':
			path = 'views/invoice/product_group/';
			break;
		case 'Exception':
			path = 'views/attendance/exceptions/';
			break;
		case 'Employee':
			path = 'views/employees/employee/';
			break;
		case 'RemittanceDestinationAccount':
			path = 'views/employees/remittance_destination_account/';
			break;
		case 'Wage':
			path = 'views/company/wage/';
			break;
		case 'Login':
			path = 'views/login/';
			break;
		case 'TimeSheet':
			path = 'views/attendance/timesheet/';
			break;
		case 'InOut':
			path = 'views/attendance/in_out/';
			break;
		case 'RecurringScheduleControl':
			path = 'views/attendance/recurring_schedule_control/';
			break;
		case 'RecurringScheduleTemplateControl':
			path = 'views/attendance/recurring_schedule_template_control/';
			break;
		case 'ScheduleShift':
		case 'Schedule':
			path = 'views/attendance/schedule/';
			break;
		case 'Accrual':
			path = 'views/attendance/accrual/';
			break;
		case 'AccrualBalance':
			path = 'views/attendance/accrual_balance/';
			break;
		case 'Punches':
			path = 'views/attendance/punches/';
			break;
		case 'PunchTagGroup':
		case 'PunchTag':
			path = 'views/attendance/punch_tag/';
			break;
		case 'JobGroup':
		case 'Job':
			path = 'views/attendance/job/';
			break;
		case 'JobItemGroup':
		case 'JobItem':
			path = 'views/attendance/job_item/';
			break;
		case 'JobItemAmendment':
			path = 'views/attendance/job_item_amendment/';
			break;
		case 'UserTitle':
			path = 'views/employees/user_title/';
			break;
		case 'UserContact':
			path = 'views/employees/user_contact/';
			break;
		case 'UserPreference':
			path = 'views/employees/user_preference/';
			break;
		case 'UserGroup':
			path = 'views/employees/user_group/';
			break;
		case 'Log':
			path = 'views/core/log/';
			break;
		case 'UserDefault':
			path = 'views/employees/user_default/';
			break;
		case 'ROE':
			path = 'views/employees/roe/';
			break;
		case 'Company':
			path = 'views/company/company/';
			break;
		case 'Companies':
			path = 'views/company/companies/';
			break;
		case 'PayPeriodSchedule':
			path = 'views/payperiod/';
			break;
		case 'PayPeriods':
			path = 'views/payroll/pay_periods/';
			break;
		case 'LegalEntity':
			path = 'views/company/legal_entity/';
			break;
		case 'PayrollRemittanceAgencyEvent':
		case 'PayrollRemittanceAgency':
			path = 'views/company/payroll_remittance_agency/';
			break;
		case 'RemittanceSourceAccount':
			path = 'views/company/remittance_source_account/';
			break;
		case 'Branch':
			path = 'views/company/branch/';
			break;
		case 'GEOFence':
			path = 'views/company/geo_fence/';
			break;
		case 'Department':
			path = 'views/company/department/';
			break;
		case 'HierarchyControl':
			path = 'views/company/hierarchy_control/';
			break;
		case 'WageGroup':
			path = 'views/company/wage_group/';
			break;
		case 'EthnicGroup':
			path = 'views/company/ethnic_group/';
			break;
		case 'Currency':
		case 'CurrencyRate':
			path = 'views/company/currency/';
			break;
		case 'PermissionControl':
			path = 'views/company/permission_control/';
			break;
		case 'CustomField':
			path = 'views/company/custom_field/';
			break;
		case 'Station':
			path = 'views/company/station/';
			break;
		case 'PayStub':
			path = 'views/payroll/pay_stub/';
			break;
		case 'PayStubTransaction':
			path = 'views/payroll/pay_stub_transaction/';
			break;
		case 'GovernmentDocument':
			path = 'views/payroll/government_document/';
			break;
		case 'Request':
			path = 'views/my_account/request/';
			break;
		case 'ChangePassword':
			path = 'views/my_account/password/';
			break;
		case 'RequestAuthorization':
			path = 'views/my_account/request_authorization/';
			break;
		case 'TimeSheetAuthorization':
			path = 'views/my_account/timesheet_authorization/';
			break;
		case 'MessageControl':
			path = 'views/my_account/message_control/';
			break;
		case 'Notification':
			path = 'views/my_account/notification/';
			break;
		case 'LoginUserContact':
			path = 'views/my_account/user_contact/';
			break;
		case 'LoginUserPreference':
			path = 'views/my_account/user_preference/';
			break;
		case 'LoginUserExpense':
		case 'ExpenseAuthorization':
			path = 'views/my_account/expense/';
			break;
		case 'PayStubAmendment':
			path = 'views/payroll/pay_stub_amendment/';
			break;
		case 'RecurringPayStubAmendment':
			path = 'views/payroll/recurring_pay_stub_amendment/';
			break;
		case 'PayStubEntryAccount':
			path = 'views/payroll/pay_stub_entry_account/';
			break;
		case 'CompanyTaxDeduction':
			path = 'views/payroll/company_tax_deduction/';
			break;
		case 'UserExpense':
			path = 'views/payroll/user_expense/';
			break;
		case 'PolicyGroup':
			path = 'views/policy/policy_group/';
			break;
		case 'PayCode':
			path = 'views/policy/pay_code/';
			break;
		case 'PayFormulaPolicy':
			path = 'views/policy/pay_formula_policy/';
			break;
		case 'ContributingPayCodePolicy':
			path = 'views/policy/contributing_pay_code_policy/';
			break;
		case 'ContributingShiftPolicy':
			path = 'views/policy/contributing_shift_policy/';
			break;
		case 'RoundIntervalPolicy':
			path = 'views/policy/round_interval_policy/';
			break;
		case 'MealPolicy':
			path = 'views/policy/meal_policy/';
			break;
		case 'BreakPolicy':
			path = 'views/policy/break_policy/';
			break;
		case 'RegularTimePolicy':
			path = 'views/policy/regular_time_policy/';
			break;
		case 'ExpensePolicy':
			path = 'views/policy/expense_policy/';
			break;
		case 'OvertimePolicy':
			path = 'views/policy/overtime_policy/';
			break;
		case 'AbsencePolicy':
			path = 'views/policy/absence_policy/';
			break;
		case 'PremiumPolicy':
			path = 'views/policy/premium_policy/';
			break;
		case 'ExceptionPolicyControl':
			path = 'views/policy/exception_policy/';
			break;

		case 'RecurringHoliday':
			path = 'views/policy/recurring_holiday/';
			break;
		case 'HolidayPolicy':
			path = 'views/policy/holiday_policy/';
			break;
		case 'Holiday':
			path = 'views/policy/holiday/';
			break;
		case 'SchedulePolicy':
			path = 'views/policy/schedule_policy/';
			break;
		case 'AccrualPolicy':
		case 'AccrualPolicyAccount':
		case 'AccrualPolicyUserModifier':
			path = 'views/policy/accrual_policy/';
			break;
		case 'DocumentRevision':
		case 'Document':
		case 'DocumentGroup':
			path = 'views/document/';
			break;
		case 'About':
			path = 'views/help/';
			break;
		case 'ActiveShiftReport':
			path = 'views/reports/whos_in_summary/';
			break;
		case 'UserSummaryReport':
			path = 'views/reports/employee_information/';
			break;
		case 'SavedReport':
			path = 'views/reports/saved_report/';
			break;
		case 'ReportSchedule':
			path = 'views/reports/report_schedule/';
			break;
		case 'ScheduleSummaryReport':
			path = 'views/reports/schedule_summary/';
			break;
		case 'TimeSheetSummaryReport':
		case 'TimesheetSummaryReport':
			path = 'views/reports/timesheet_summary/';
			break;
		case 'TimesheetDetailReport':
			path = 'views/reports/timesheet_detail/';
			break;
		case 'PunchSummaryReport':
			path = 'views/reports/punch_summary/';
			break;
		case 'ExceptionSummaryReport':
			path = 'views/reports/exception_summary/';
			break;
		case 'PayStubTransactionSummaryReport':
			path = 'views/reports/pay_stub_transaction_summary/';
			break;
		case 'PayStubSummaryReport':
			path = 'views/reports/pay_stub_summary/';
			break;
		case 'KPI':
		case 'KPIGroup':
		case 'UserReviewControl':
			path = 'views/hr/kpi/';
			break;
		case 'QualificationGroup':
		case 'Qualification':
		case 'UserSkill':
		case 'UserEducation':
		case 'UserMembership':
		case 'UserLicense':
		case 'UserLanguage':
			path = 'views/hr/qualification/';
			break;
		case 'JobApplication':
		case 'JobVacancy':
		case 'JobApplicant':
		case 'JobApplicantEmployment':
		case 'JobApplicantReference':
		case 'JobApplicantLocation':
		case 'JobApplicantSkill':
		case 'JobApplicantEducation':
		case 'JobApplicantMembership':
		case 'JobApplicantLicense':
		case 'JobApplicantLanguage':
		case 'RecruitmentPortalConfig':
			path = 'views/hr/recruitment/';
			break;
		case 'PayrollExportReport':
			path = 'views/reports/payroll_export/';
			break;
		case 'GeneralLedgerSummaryReport':
			path = 'views/reports/general_ledger_summary/';
			break;
		case 'ExpenseSummaryReport':
			path = 'views/reports/expense_summary/';
			break;
		case 'AccrualBalanceSummaryReport':
			path = 'views/reports/accrual_balance_summary/';
			break;
		case 'JobSummaryReport':
			path = 'views/reports/job_summary/';
			break;
		case 'JobAnalysisReport':
			path = 'views/reports/job_analysis/';
			break;
		case 'JobInformationReport':
			path = 'views/reports/job_info/';
			break;
		case 'JobItemInformationReport':
			path = 'views/reports/job_item_info/';
			break;
		case 'InvoiceTransactionSummaryReport':
			path = 'views/reports/invoice_transaction_summary/';
			break;
		case 'RemittanceSummaryReport':
			path = 'views/reports/remittance_summary/';
			break;
		case 'T4SummaryReport':
			path = 'views/reports/t4_summary/';
			break;
		case 'T4ASummaryReport':
			path = 'views/reports/t4a_summary/';
			break;
		case 'TaxSummaryReport':
			path = 'views/reports/tax_summary/';
			break;
		case 'Form940Report':
			path = 'views/reports/form940/';
			break;
		case 'Form941Report':
			path = 'views/reports/form941/';
			break;
		case 'Form1099NecReport':
			path = 'views/reports/form1099/';
			break;
		case 'FormW2Report':
			path = 'views/reports/formw2/';
			break;
		case 'USStateUnemploymentReport':
			path = 'views/reports/us_state_unemployment/';
			break;
		case 'USPERSReport':
			path = 'views/reports/us_pers/';
			break;
		case 'USEEOReport':
			path = 'views/reports/us_eeo/';
			break;
		case 'AffordableCareReport':
			path = 'views/reports/affordable_care/';
			break;
		case 'UserQualificationReport':
			path = 'views/reports/qualification_summary/';
			break;
		case 'KPIReport':
			path = 'views/reports/review_summary/';
			break;
		case 'UserRecruitmentSummaryReport':
			path = 'views/reports/recruitment_summary/';
			break;
		case 'UserRecruitmentDetailReport':
			path = 'views/reports/recruitment_detail/';
			break;
		case 'Client':
			path = 'views/invoice/client/';
			break;
		case 'ClientContact':
			path = 'views/invoice/client_contact/';
			break;
		case 'ClientPayment':
			path = 'views/invoice/client_payment/';
			break;
		case 'InvoiceTransaction':
			path = 'views/invoice/invoice_transaction/';
			break;
		case 'Invoice':
			path = 'views/invoice/invoice/';
			break;
		case 'CustomColumn':
			path = 'views/reports/custom_column/';
			break;
		case 'AuditTrailReport':
			path = 'views/reports/audittrail/';
			break;
		case 'ReCalculateTimeSheetWizard':
			path = 'views/wizard/re_calculate_timesheet/';
			break;
		case 'GeneratePayStubWizard':
			path = 'views/wizard/generate_pay_stub/';
			break;
		case 'UserGenericStatus':
			path = 'views/wizard/user_generic_data_status/';
			break;
		case 'ProcessPayrollWizard':
			path = 'views/wizard/process_payroll/';
			break;
		case 'PayrollRemittanceAgencyEventWizardController':
			path = 'views/payroll/remittance_wizard/';
			break;
		case 'ProcessTransactionsWizardController':
			path = 'views/payroll/process_transactions_wizard/';
			break;
		case 'ImportCSVWizard':
			path = 'views/wizard/import_csv/';
			break;
		case 'JobInvoiceWizard':
			path = 'views/wizard/job_invoice/';
			break;
		case 'LoginUserWizard':
		case 'LoginUser':
			path = 'views/wizard/login_user/';
			break;
		case 'QuickStartWizard':
			path = 'views/wizard/quick_start/';
			break;
		case 'UserPhotoWizard':
			path = 'views/wizard/user_photo/';
			break;
		case 'FindAvailableWizard':
		case 'FindAvailable':
			path = 'views/wizard/find_available/';
			break;
		case 'PermissionWizard':
			path = 'views/wizard/permission_wizard/';
			break;
		case 'FormulaBuilderWizard':
			path = 'views/wizard/formula_builder_wizard/';
			break;
		case 'ReCalculateAccrualWizard':
			path = 'views/wizard/re_calculate_accrual/';
			break;
		case 'ResetPasswordWizard':
			path = 'views/wizard/reset_password/';
			break;
		case 'ShareReportWizard':
			path = 'views/wizard/share_report/';
			break;
		case 'PayCodeWizard':
			path = 'views/wizard/pay_code/';
			break;
		case 'InstallWizard':
			path = 'views/wizard/install/';
			break;
		case 'PayStubAccountWizard':
			path = 'views/wizard/pay_stub_account/';
			break;
		case 'DashletWizard':
			path = 'views/wizard/dashlet/';
			break;
		case 'ReportViewWizard':
			path = 'views/wizard/report_view/';
			break;
		case 'PortalApplyJobWizard':
			path = 'views/wizard/portal_apply_job/';
			break;
		case 'ForgotPasswordWizard':
			path = 'views/wizard/forgot_password/';
			break;
		case 'ResetForgotPasswordWizard':
			path = 'views/wizard/reset_forgot_password/';
			break;
		case 'DeveloperTools':
			path = 'views/developer_tools/';
			break;
		case 'UIKitSample':
		case 'UIKitChildSample':
			path = 'views/ui_kit_sample/';
			break;
	}
	return path;
};
/* jshint ignore:end */

//returns exact filepaths for class dependencies
Global.getViewPreloadPathByViewId = function( viewId ) {
	// DEPRECATED: Moved the loading of these preloads to post-login-main_ui-dependancies.js

	var preloads = [];
	// switch ( viewId ) {
	// 	case 'Request':
	// 	case 'RequestAuthorization':
	// 		preloads = ['views/common/AuthorizationHistoryCommon.js', 'views/common/RequestViewCommonController.js', 'views/common/EmbeddedMessageCommon.js'];
	// 		break;
	// 	case 'ExpenseAuthorization':
	// 	case 'UserExpense':
	// 	case 'LoginUserExpense':
	// 	case 'TimeSheetAuthorization':
	// 		preloads = ['views/common/AuthorizationHistoryCommon.js'];
	// 		break;
	// }
	return preloads;
};

Global.removeViewCss = function( viewId, fileName ) {
	Global.removeCss( Global.getViewPathByViewId( viewId ) + fileName );
};

Global.sanitizeViewId = function( viewId ) {
	if ( typeof viewId === 'string' || viewId instanceof String ) {
		return viewId.replace( '/', '' ).replace( '\\', '' );
	}

	return viewId;
};

Global.loadViewSource = function( viewId, fileName, onResult, sync ) {
	var viewId = Global.sanitizeViewId( viewId );
	var path = Global.getViewPathByViewId( viewId );

	if ( fileName.indexOf( '.js' ) > 0 ) {
		var preloads = Global.getViewPreloadPathByViewId( viewId );
		if ( preloads.length > 0 ) {
			for ( var p in preloads ) {
				Global.loadScript( preloads[p] );
			}
		}

		if ( path ) {
			if ( sync ) {
				return Global.loadScript( path + fileName );
			} else {
				Global.loadScript( path + fileName, onResult );
			}
		} else {
			//Invalid viewId, redirect to home page?
			console.debug( 'View does not exist! ViewId: ' + viewId + ' File Name: ' + fileName );
			if ( ServiceCaller.root_url && APIGlobal.pre_login_data.base_url ) {
				Global.setURLToBrowser( ServiceCaller.root_url + APIGlobal.pre_login_data.base_url );
			}
		}

	} else if ( fileName.indexOf( '.css' ) > 0 ) {
		Global.addCss( path + fileName );
	} else {
		if ( path ) {
			// HTML2JS
			var template_type = HtmlTemplatesGlobal.getTemplateTypeFromFilename( fileName );
			var template_options = HtmlTemplatesGlobal.getTemplateOptionsFromViewId( viewId );

			if( template_type === TemplateType.INLINE_HTML ) {
				template_options.filename = fileName; // Needed by HtmlTemplates.checkViewClassForInlineHtmlbyFilename() which uses filename, not view id.
			}
			if ( sync ) {
				// Note: for #HTML2JS This path is taken for things such as: CompanyInformation, CompanyEditView.html, and general edit views.

				// Check if we should use the new templating logic, or legacy html load.
				if( template_type !== TemplateType.LEGACY_HTML ) {
					// Use new HTML2JS template class
					return HtmlTemplatesGlobal.getTemplate( template_type, template_options, null ); // no onResult, as its syncronous.
				} else {
					// Legacy html file load for syncronous files.
					return Global.loadPageSync( path + fileName );
				}
			} else {
				// Check if we should use the new templating logic, or legacy html load.
				if( template_type !== TemplateType.LEGACY_HTML ) {
					// Use new HTML2JS template class
					HtmlTemplatesGlobal.getTemplate( template_type, template_options, onResult );
				} else {
					// Legacy html file load
					Global.loadPage( path + fileName, onResult );
				}
			}
		} else {
			//Invalid viewId, redirect to home page?
			console.debug( 'View does not exist! ViewId: ' + viewId + ' File Name: ' + fileName );
			if ( ServiceCaller.root_url && APIGlobal.pre_login_data.base_url ) {
				Global.setURLToBrowser( ServiceCaller.root_url + APIGlobal.pre_login_data.base_url );
			}
		}
	}
};

Global.loadPageSync = function( url ) {

	var realPath = url + '?v=' + APIGlobal.pre_login_data.application_build;

	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}
	var message_id = TTUUID.generateUUID();
	ProgressBar.showProgressBar( message_id );
	var successflag = false;
	var responseData = $.ajax( {
		async: false,
		type: 'GET',
		url: realPath,
		data: null,
		cache: true,
		success: function() {
			successflag = true;
		},

		error: function( jqXHR, textStatus, errorThrown ) {
			TAlertManager.showNetworkErrorAlert( jqXHR, textStatus, errorThrown );
		}
	} );

	ProgressBar.removeProgressBar( message_id );

	return ( responseData.responseText );

};

Global.loadPage = function( url, onResult ) {

	var realPath = url + '?v=' + APIGlobal.pre_login_data.application_build;
	var message_id = TTUUID.generateUUID();
	if ( Global.url_offset ) {
		realPath = Global.url_offset + realPath;
	}

	ProgressBar.showProgressBar( message_id );
	$.ajax( {
		async: true,
		type: 'GET',
		url: realPath,
		data: null,
		cache: true,
		success: function( result ) {
			ProgressBar.removeProgressBar( message_id );
			onResult( result );
		},
		error: function( jqXHR, textStatus, errorThrown ) {
			TAlertManager.showNetworkErrorAlert( jqXHR, textStatus, errorThrown );
		}
	} );

};

Global.getRootURL = function( url ) {
	if ( !url ) {
		url = location.href;
	}

	//Rather than parse the URL ourselves, lets use the URL API and build it back up from its components.
	var url_obj = new URL( url );
	var retval = url_obj.protocol + '//' + url_obj.host;

	return retval;
};

Global.getBaseURL = function( url_relative_path, include_search = true, include_hash = false ) {
	//Rather than parse the URL ourselves, lets use the URL API and build it back up from its components.
	var url_obj = new URL( location.href );
	var retval = url_obj.protocol + '//' + url_obj.host + url_obj.pathname;

	//Resolve any specified relative path here, so we can append the search component of the URL after.
	//  This is needed for the recruitment portal to work if Facebook or some other 3rd party appends search components on the URL, ie: ?test=1#!m=PortalJobVacancyDetail&id=05a45d0b-b982-2a1f-2003-21ea65522bf3&company_id=ABC
	if ( url_relative_path ) {
		retval = new URL( url_relative_path, retval ).href;
	}

	if ( include_search == true ) {
		retval += url_obj.search; //Can't put the search component back on when getting BaseURL.
	}
	if ( include_hash == true ) {
		retval += url_obj.hash;
	}


	return retval;
};

Global.isArrayAndHasItems = function( object ) {

	if ( $.type( object ) === 'array' && object.length > 0 ) {
		return true;
	}

	return false;

};

Global.isValidInputCodes = function( keyCode ) {
	var result = true;
	switch ( keyCode ) {
		case 9:
		case 16:
		case 17:
		case 18:
		case 19:
		case 20:
		case 33:
		case 34:
		// case 37:
		// case 38:
		// case 39:
		// case 40:
		case 45:
		case 91:
		case 92:
		case 93:
			result = false;
			break;
		default:
			if ( keyCode >= 112 && keyCode <= 123 ) {
				result = false;
			}
	}
	return result;
};

/* jshint ignore:start */
Global.convertLayoutFilterToAPIFilter = function( layout ) {
	var convert_filter_data = {};

	if ( !layout ) {
		return null;
	}

	var filter_data = layout.data.filter_data;

	if ( !filter_data ) {
		return null;
	}

	$.each( filter_data, function( key, content ) {
		// Cannot read property 'value' of undefined
		if ( !content ) {
			return;//continue;
		}
		if ( ( content.value instanceof Array && content.value.length > 0 ) || ( content.value instanceof Object ) ) {
			var values = [];
			var obj = content.value;
			if ( content.value instanceof Array ) {

				var len = content.value.length;
				for ( var i = 0; i < len; i++ ) {

					if ( Global.isSet( content.value[i].value ) ) {
						values.push( content.value[i].value ); //Options,
					} else if ( content.value[i].id || content.value[i].id === 0 || content.value[i].id === '0' ) {
						values.push( content.value[i].id ); //Awesomebox
					} else {
						values.push( content.value[i] ); // default_filter_data_for_next_view
					}

				}

				convert_filter_data[key] = values;
				//only add search filter which not equal to false, see if this cause any bugs
			} else if ( content.value instanceof Object ) {
				var final_value = '';
				if ( Global.isSet( content.value.value ) ) {
					final_value = content.value.value; //Options,
				} else if ( content.value.id || content.value.id === 0 || content.value.id === '0' ) {
					final_value = content.value.id; //Awesomebox
				} else {
					final_value = content.value; // default_filter_data_for_next_view
				}

				convert_filter_data[key] = final_value;

			} else if ( obj.value === false ) {
				return;//continue;
			} else {
				if ( Global.isSet( obj.value ) ) {

					convert_filter_data[key] = obj.value;
				}
			}

		} else if ( filter_data[key].value === false ) {
			return; //continue;
		} else if ( Global.isSet( filter_data[key].value ) ) {
			convert_filter_data[key] = filter_data[key].value;
		} else {
			convert_filter_data[key] = filter_data[key];
		}
	} );

	if ( LocalCacheData.extra_filter_for_next_open_view ) { //MUST removed this when close the view which used this attribute.

		for ( var key in LocalCacheData.extra_filter_for_next_open_view.filter_data ) {
			convert_filter_data[key] = LocalCacheData.extra_filter_for_next_open_view.filter_data[key];
		}

	}

	return convert_filter_data;

};
/* jshint ignore:end */

//ASC
Global.compare = function( a, b, orderKey, order_type ) {

	if ( !Global.isSet( order_type ) ) {
		order_type = 'asc';
	}

	if ( order_type === 'asc' ) {
		if ( a[orderKey] < b[orderKey] ) {
			return -1;
		}
		if ( a[orderKey] > b[orderKey] ) {
			return 1;
		}
		return 0;
	} else {
		if ( a[orderKey] < b[orderKey] ) {
			return 1;
		}
		if ( a[orderKey] > b[orderKey] ) {
			return -1;
		}
		return 0;
	}

};

Global.buildFilter = function() {
	var filterCondition = arguments[0];
	var filter = [];

	if ( filterCondition ) {

		for ( var key in filterCondition ) {
			filter[key] = filterCondition[key];
		}

	}

	return filter;

};

Global.getLoginUserDateFormat = function() {
	var format = 'DD-MMM-YY';

	if ( LocalCacheData.getLoginUserPreference() ) {
		format = LocalCacheData.getLoginUserPreference().date_format;
	}

	return format;
};
/* jshint ignore:start */
Global.formatGridData = function( grid_data, key_name ) {

	if ( $.type( grid_data ) !== 'array' ) {
		return grid_data;
	}

	for ( var i = 0; i < grid_data.length; i++ ) {
		for ( var key in grid_data[i] ) {

			if ( !grid_data[i].hasOwnProperty( key ) ) {
				return;
			}

			//Need to convert custom fields time_unit to string
			if ( key.indexOf( 'custom_field' ) === 0 && Array.isArray( LocalCacheData.current_open_primary_controller.custom_fields ) ) {
				let custom_field = LocalCacheData.current_open_primary_controller.custom_fields.find( ( field ) => {
					return field.id === key.replace( 'custom_field-', '' );
				} );

				if ( custom_field && custom_field.type_id == 1300 ) {
					if ( Global.isNumeric( grid_data[i][key] ) ) {
						grid_data[i][key] = Global.getTimeUnit( grid_data[i][key] );
					}
					continue;
				}
			}

			// The same format for all views.
			switch ( key ) {
				case 'maximum_shift_time':
				case 'new_day_trigger_time':
				case 'trigger_time':
				case 'minimum_punch_time':
				case 'maximum_punch_time':
				case 'window_length':
				case 'start_window':
				case 'round_interval':
				case 'grace':
				case 'estimate_time':
				case 'minimum_time':
				case 'maximum_time':
				case 'total_time':
				case 'start_stop_window':
					if ( Global.isNumeric( grid_data[i][key] ) ) {
						grid_data[i][key] = Global.getTimeUnit( grid_data[i][key] );
					} else {
						grid_data[i][key] = null; //Prevent string "false" from being returned when the column isn't defined on the server side.
					}
					break;
				case 'include_break_punch_time':
				case 'include_multiple_breaks':
				case 'include_lunch_punch_time':
				case 'is_default':
				case 'is_base':
				case 'auto_update':
				case 'currently_employed':
				case 'criminal_record':
				case 'immediate_drug_test':
				case 'is_current_employer':
				case 'is_contact_available':
				case 'enable_pay_stub_balance_display':
				case 'enable_login':
				case 'ytd_adjustment':
				case 'authorized':
				case 'is_reimbursable':
				case 'reimbursable':
				case 'tainted':
				case 'auto_fill':
				case 'private':
					if ( grid_data[i][key] === true ) {
						grid_data[i][key] = $.i18n._( 'Yes' );
					} else if ( grid_data[i][key] === false ) {
						grid_data[i][key] = $.i18n._( 'No' );
					}
					break;
				case 'override':
					if ( grid_data[i][key] === true ) {
						grid_data[i][key] = $.i18n._( 'Yes' );
						grid_data[i]['is_override'] = true;
					} else if ( grid_data[i][key] === false ) {
						grid_data[i][key] = $.i18n._( 'No' );
						grid_data[i]['is_override'] = false;
					}
					break;
				case 'is_scheduled':
					if ( grid_data[i][key] === '1' ) {
						grid_data[i][key] = $.i18n._( 'Yes' );
					} else if ( grid_data[i][key] === '0' ) {
						grid_data[i][key] = $.i18n._( 'No' );
					}
					break;
				case 'in_use':
					if ( grid_data[i][key] === '1' ) {
						grid_data[i][key] = $.i18n._( 'Yes' );
						grid_data[i]['is_in_use'] = true;
					} else if ( grid_data[i][key] === '0' ) {
						grid_data[i][key] = $.i18n._( 'No' );
						grid_data[i]['is_in_use'] = false;
					}
					break;
				default:
					if ( grid_data[i][key] === false ) {
						grid_data[i][key] = '';
					}
					break;
			}

			// Handle the specially format columns which are not different with others.
			switch ( key_name ) {
				case 'AccrualPolicyUserModifier':
					switch ( key ) {
						case 'annual_maximum_time_modifier':
							if ( grid_data[i]['type_id'] === 20 ) {
								grid_data[i][key] = $.i18n._( 'N/A' );
							}
							break;
					}
					break;
				case 'BreakPolicy':
				case 'MealPolicy':
				case 'Accrual':
					switch ( key ) {
						case 'amount':
							if ( Global.isNumeric( grid_data[i][key] ) ) {
								grid_data[i][key] = Global.getTimeUnit( grid_data[i][key] );
							}
							break;

					}
					break;
				case 'accrual_balance_summary':
				case 'AccrualBalance':
					switch ( key ) {
						case 'balance':
							if ( Global.isNumeric( grid_data[i][key] ) ) {
								grid_data[i][key] = Global.getTimeUnit( grid_data[i][key] );
							}
							break;

					}
					break;
				case 'RecurringScheduleControl':
					switch ( key ) {
						case 'end_date':
							if ( grid_data[i][key] === '' ) {
								grid_data[i][key] = 'Never';
							}
							break;
					}
					break;
			}

		}
	}

	return grid_data;

};
/* jshint ignore:end */

// Commented out as we have now fully refactored the old _super and __super references in the new ES6 code.
// //make backone support a simple super funciton
// Backbone.Model.prototype._super = function( funcName ) {
// 	return this.constructor.__super__[funcName].apply( this, _.rest( arguments ) );
// };
//
// //make backone support a simple super function
// Backbone.View.prototype._super = function( funcName ) {
// 	// Note: If 'Maximum call stack size exceeded' error encountered, and view is extending twice (BaseView->ReportBaseView->SomeRandomView), then make sure you define `this.real_this` at the 2nd level extend. See reportBaseViewController init for example.
// 	if ( this.real_this && this.real_this.constructor.__super__[funcName] ) {
// 		return this.real_this.constructor.__super__[funcName].apply( this, _.rest( arguments ) );
// 	} else {
// 		return this.constructor.__super__[funcName].apply( this, _.rest( arguments ) );
// 	}
//
// };
//
// //make backone support a simple super funciton for second level class
// Backbone.View.prototype.__super = function( funcName ) {
// 	if ( !this.real_this ) {
// 		this.real_this = this.constructor.__super__;
// 	}
//
// 	return this.constructor.__super__[funcName].apply( this, _.rest( arguments ) );
//
// };

/*
 * Date Format 1.2.3
 * (c) 2007-2009 Steven Levithan <stevenlevithan.com>
 * MIT license
 *
 * Includes enhancements by Scott Trenda <scott.trenda.net>
 * and Kris Kowal <cixar.com/~kris.kowal>
 *
 * Accepts a date, a mask, or a date and a mask.
 * Returns a formatted version of the given date.
 * The date defaults to the current date/time.
 * The mask defaults to dateFormat.masks.default.
 */

var dateFormat = function() {
	var token = /d{1,4}|m{1,4}|yy(?:yy)?|([HhMsTt])\1?|[LloSZ]|'[^']*"|'[^']*'/g,
		timezone = /\b(?:[PMCEA][SDP]T|(?:Pacific|Mountain|Central|Eastern|Atlantic) (?:Standard|Daylight|Prevailing) Time|(?:GMT|UTC)(?:[-+]\d{4})?)\b/g,
		timezoneClip = /[^-+\dA-Z]/g,
		pad = function( val, len ) {
			val = String( val );
			len = len || 2;
			while ( val.length < len ) {
				val = '0' + val;
			}
			return val;
		};

	// Regexes and supporting functions are cached through closure

	/* jshint ignore:start */
	return function( date, mask, utc ) {
		var dF = dateFormat;

		// You can't provide utc if you skip other args (use the 'UTC:' mask prefix)
		if ( arguments.length === 1 && Object.prototype.toString.call( date ) === '[object String]' && !/\d/.test( date ) ) {
			mask = date;
			date = undefined;
		}

		// Passing date through Date applies Date.parse, if necessary
		date = date ? new Date( date ) : new Date();
		if ( isNaN( date ) ) {
			throw SyntaxError( 'invalid date' );
		}

		mask = String( dF.masks[mask] || mask || dF.masks['default'] );

		// Allow setting the utc argument via the mask
		if ( mask.slice( 0, 4 ) === 'UTC:' ) {
			mask = mask.slice( 4 );
			utc = true;
		}

		var _ = utc ? 'getUTC' : 'get',
			d = date[_ + 'Date'](),
			D = date[_ + 'Day'](),
			m = date[_ + 'Month'](),
			y = date[_ + 'FullYear'](),
			H = date[_ + 'Hours'](),
			M = date[_ + 'Minutes'](),
			s = date[_ + 'Seconds'](),
			L = date[_ + 'Milliseconds'](),
			o = utc ? 0 : date.getTimezoneOffset(),
			flags = {
				d: d,
				dd: pad( d ),
				ddd: dF.i18n.dayNames[D],
				dddd: dF.i18n.dayNames[D + 7],
				m: m + 1,
				mm: pad( m + 1 ),
				mmm: dF.i18n.monthNames[m],
				mmmm: dF.i18n.monthNames[m + 12],
				yy: String( y ).slice( 2 ),
				yyyy: y,
				h: H % 12 || 12,
				hh: pad( H % 12 || 12 ),
				H: H,
				HH: pad( H ),
				M: M,
				MM: pad( M ),
				s: s,
				ss: pad( s ),
				l: pad( L, 3 ),
				L: pad( L > 99 ? Math.round( L / 10 ) : L ),
				t: H < 12 ? 'a' : 'p',
				tt: H < 12 ? 'am' : 'pm',
				T: H < 12 ? 'A' : 'P',
				TT: H < 12 ? 'AM' : 'PM',
				Z: utc ? 'UTC' : ( String( date ).match( timezone ) || [''] ).pop().replace( timezoneClip, '' ),
				o: ( o > 0 ? '-' : '+' ) + pad( Math.floor( Math.abs( o ) / 60 ) * 100 + Math.abs( o ) % 60, 4 ),
				S: ['th', 'st', 'nd', 'rd'][d % 10 > 3 ? 0 : ( d % 100 - d % 10 !== 10 ) * d % 10]
			};

		return mask.replace( token, function( $0 ) {
			return $0 in flags ? flags[$0] : $0.slice( 1, $0.length - 1 );
		} );
	};
	/* jshint ignore:end */
}();

// Some common format strings
dateFormat.masks = {
	'default': 'ddd mmm dd yyyy HH:MM:ss',
	shortDate: 'm/d/yy',
	mediumDate: 'mmm d, yyyy',
	longDate: 'mmmm d, yyyy',
	fullDate: 'dddd, mmmm d, yyyy',
	shortTime: 'h:MM TT',
	mediumTime: 'h:MM:ss TT',
	longTime: 'h:MM:ss TT Z',
	isoDate: 'yyyy-mm-dd',
	isoTime: 'HH:MM:ss',
	isoDateTime: 'yyyy-mm-dd\'T\'HH:MM:ss',
	isoUtcDateTime: 'UTC:yyyy-mm-dd\'T\'HH:MM:ss\'Z\''
};

// Internationalization strings
dateFormat.i18n = {
	dayNames: [
		'Sun', 'Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat',
		'Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'
	],
	monthNames: [
		'Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec',
		'January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'
	]
};

// For convenience...
Date.prototype.format = function( mask, utc ) {
	//JS Exception: Uncaught TypeError: Cannot read properties of undefined (reading 'date_format')
	if ( !Global.isSet( mask ) && LocalCacheData.getLoginUserPreference() ) {
		mask = LocalCacheData.getLoginUserPreference().date_format;
	}

	if ( !mask ) {
		mask = 'DD-MMM-YY';
	}

	var format_str = moment( this ).format( mask );

	return format_str;
};

window.RightClickMenuType = function() {

};

RightClickMenuType.LISTVIEW = '1';
RightClickMenuType.EDITVIEW = '2';
RightClickMenuType.NORESULTBOX = '3';
RightClickMenuType.ABSENCE_GRID = '4';
RightClickMenuType.VIEW_ICON = '5';

/**
 * Decoding encoded html enitities (ie: "&gt;")
 * to avoid XSS vulnerabilities do not eval anything that has gone through this function
 *
 * @param str
 * @returns {*|jQuery}
 */
Global.htmlDecode = function( str ) {
	return $( '<textarea></textarea>' ).html( str ).text();
};

Global.htmlEncode = function( str ) {
	var encodedStr = str;

	if ( encodedStr ) {
		// This replaces 'S' in 'MST' with the encoded value, which is invalid.
		// encodedStr = str.replace( /[\u00A0-\u9999<>\'"\&]/gim, function( i ) {
		// 	return '&#' + i.charCodeAt( 0 ) + ';';
		// } );
		// encodedStr = encodedStr.replace( /&#60;br&#62;/g, '<br>' );
		// return encodedStr;

		var tmp = document.createElement( 'div' );
		tmp.textContent = encodedStr;

		return tmp.innerHTML;
	} else {
		return encodedStr;
	}
};

//Sort by module

Global.m_sort_by = ( function() {
	// utility functions

	var default_cmp = function( a, b ) {

			if ( a === b ) {
				return 0;
			}

			//Speical handle OPEN option to make it always stay together
			if ( a === false || a === 'OPEN' ) {
				return -1;
			}

			if ( b === false || b === 'OPEN' ) {
				return 1;
			}

			return a < b ? -1 : 1;
		},
		getCmpFunc = function( primer, reverse ) {
			var cmp = default_cmp;
			if ( primer ) {
				cmp = function( a, b ) {
					return default_cmp( primer( a ), primer( b ) );
				};
			}
			if ( reverse ) {
				return function( a, b ) {
					return -1 * cmp( a, b );
				};
			}
			return cmp;
		};

	// actual implementation
	var sort_by = function( sort_by_array ) {
		var fields = [],
			n_fields = sort_by_array.length,
			field, name, reverse, cmp;

		// preprocess sorting options
		for ( var i = 0; i < n_fields; i++ ) {
			field = sort_by_array[i];
			if ( typeof field === 'string' ) {
				name = field;
				cmp = default_cmp;
			} else {
				name = field.name;
				cmp = getCmpFunc( field.primer, field.reverse );
			}
			fields.push( {
				name: name,
				cmp: cmp
			} );
		}

		return function( A, B ) {
			var a, b, name, cmp, result;
			for ( var i = 0, l = n_fields; i < l; i++ ) {
				result = 0;
				field = fields[i];
				name = field.name;
				cmp = field.cmp;

				result = cmp( A[name], B[name] );
				if ( result !== 0 ) {
					break;
				}
			}
			return result;
		};
	};

	return sort_by;

}() );

$.fn.invisible = function() {
	return this.each( function() {
		$( this ).css( 'opacity', '0' );
	} );
};
$.fn.visible = function() {
	return this.each( function() {
		$( this ).css( 'opacity', '1' );
	} );
};

Global.trackView = function( name, action ) {
	if ( APIGlobal.pre_login_data.analytics_enabled === true ) {
		var track_address;

		//Hostname is already sent separately, so this should just be the view/action in format:
		// '#!m=' + name + '&a=' + action
		if ( name ) {
			track_address = '#!m=' + name;

			if ( action ) {
				track_address += '&a=' + action;
			}
		} else {
			//Default to only data after (and including) the #.
			track_address = window.location.hash.substring( 1 );
		}

		//Track address is sent in sendAnalytics as the 3rd parameter.
		Global.sendAnalyticsPageview( track_address );
	}
};

Global.setAnalyticDimensions = function( user_name, company_name ) {
	if ( APIGlobal.pre_login_data.analytics_enabled === true ) {
		if ( typeof ( gtag ) !== 'undefined' ) {
			try {
				//All names must be mapped in main.js 'custom_map'
				var user_properties = {
					'application_version': APIGlobal.pre_login_data.application_version,
					'http_host': APIGlobal.pre_login_data.http_host,
					'product_edition_name': APIGlobal.pre_login_data.product_edition_name,
					'registration_key': APIGlobal.pre_login_data.registration_key,
					'primary_company_name': APIGlobal.pre_login_data.primary_company_name,
				};

				if ( user_name !== 'undefined' && user_name !== null ) {
					if ( APIGlobal.pre_login_data.production !== true ) {
						Debug.Text( 'Analytics User: ' + user_name, 'Global.js', '', 'doPing', 1 );
					}
					user_properties.user_name = user_name;
				}

				if ( company_name !== 'undefined' && company_name !== null ) {
					if ( APIGlobal.pre_login_data.production !== true ) {
						Debug.Text( 'Analytics Company: ' + company_name, 'Global.js', '', 'setAnalyticDimensions', 1 );
					}
					user_properties.company_name = company_name;
				}

				gtag( 'set', 'user_properties', user_properties );
			} catch ( e ) {
				throw e; //Attempt to catch any errors thrown by Google Analytics.
			}
		}
	}
};

Global.sendAnalyticsPageview = function( track_address ) {
	if ( APIGlobal.pre_login_data.analytics_enabled === true ) {
		// Call this delay so view load goes first
		if ( typeof ( gtag ) !== 'undefined' ) {
			setTimeout( function() {
				try {
					gtag( 'event', 'page_view', { page_path: track_address } )
				} catch ( e ) {
					throw e;
				}
			}, 500 );
		}

	}
};

/**
 * This function is used to actually submit the analytics request to google.
 * @param {string} event_category - Category relating to the event tracked, e.g. feedback or context_menu
 * @param {string} event_action - What triggered the event. E.g. click, cancel.
 * @param {string} event_label - This is often a combo of the actual value string combined with some of the above fields, for clarity. e.g. submit:feedback:sad
 */
Global.sendAnalyticsEvent = function( event_category, event_action, event_label ) {
	if ( typeof ( gtag ) !== 'undefined' && APIGlobal.pre_login_data.analytics_enabled === true ) {
		//Debug.Arr( fieldsObject, 'Sending analytics event payload. Event: ' + event_category + ', Action: ' + event_action + ', Label: ' + event_label, 'Global.js', 'Global', 'sendAnalyticsEvent', 11 );
		try {
			gtag( 'event', event_category, { action: event_action, label: event_label } )
		} catch ( e ) {
			throw e;
		}
	}
};

/**
 *
 * @param context_btn - the jQuery element that triggered the click event on the context menu
 * @param {string} menu_name - the name of the icon if the click event was triggered by the right click context menu
 */
Global.triggerAnalyticsContextMenuClick = function( context_btn, menu_name ) {
	// If more detail is needed above and beyond contextmenu name, then use 'LocalCacheData.current_open_view_id' in addition, but not instead of, as they are different.
	var dom_context_menu = LocalCacheData.currentShownContextMenuName || 'error-with-context-menu'; // '||' is for graceful fail. identify correct context menu (vs DOM search, where there could be multiple inactive context menus)
	var dom_context_menu_group;
	var button_id;
	var event_category;
	var event_action;
	var event_label;

	if ( context_btn ) {
		if ( context_btn.group && context_btn.group.label ) {
			dom_context_menu_group = context_btn.group.label;
		} else {
			dom_context_menu_group = context_btn.action_group;
		}
		event_category = 'navigation:context_menu';
		button_id = context_btn.id || 'error-with-icon';
	} else {
		// If context_btn is null, then this is likely a right click context menu call.
		event_category = 'navigation:right_click_menu';
		dom_context_menu_group = 'right_click';
		button_id = menu_name || 'error-with-rightclick-icon';
	}

	// Beautify output
	dom_context_menu = dom_context_menu.replace( 'ContextMenu', '' );
	button_id = button_id.replace( 'Icon', '' ); //Remove "icon" from button_id.

	event_action = 'click';
	event_label = dom_context_menu + ':' + dom_context_menu_group + '|' + button_id;

	// Debug.Text( 'Context Menu: Category: navigation_context_menu Action: ' + event_action + ' Label: ' + event_label, 'Global.js', 'Global', 'triggerAnalyticsContextMenuClick', 10 );
	Global.sendAnalyticsEvent( event_category, event_action, event_label );
};

/**
 *
 * @param {string} context - Explains what element triggered the event
 * @param {string} view_id - Name of the current view in which element was triggered
 */
Global.triggerAnalyticsEditViewNavigation = function( context, view_id ) {
	// context in this case can be 'left-arrow', 'right-arrow', or 'awesomebox'
	var event_action = 'click';
	var event_label = view_id + ':' + context;

	// Debug.Text( 'Context Menu: Category: navigation_edit_view_navigation Action: ' + event_action + ' Label: ' + event_label, 'Global.js', 'Global', 'triggerAnalyticsContextMenuClick', 10 );
	Global.sendAnalyticsEvent( 'navigation:edit_view_navigation', event_action, event_label );
};

/**
 *
 * @param event - the event object from the jQuery UI tabs. Currently expecting it to be triggered by the activate event
 * @param ui - the ui object from jQuery UI tabs, contains prev and target tab info
 */
Global.triggerAnalyticsTabs = function( event, ui ) {
	// activate event triggered, ensure all required values are set
	if ( event && event.type && ui && ui.newTab ) {
		var tab_target = ui.newTab.find( '.ui-tabs-anchor' ).attr( 'ref' ) || 'tab-target-error'; // '||' is for gracful fail
		var viewId = LocalCacheData.current_open_view_id || 'error-viewid'; // '||' is for graceful fail

		// Beautify output
		tab_target = tab_target.replace( 'tab_', '' );

		var event_action = 'click';
		var event_label = viewId + ':tabs:' + tab_target;

		// Debug.Text( 'Context Menu: Category: navigation_tabs Action: ' + event_action + ' Label: ' + event_label, 'Global.js', 'Global', 'triggerAnalyticsContextMenuClick', 10 );
		Global.sendAnalyticsEvent( 'navigation:tabs', event_action, event_label );
	} else {
		Global.sendAnalyticsEvent( 'error:navigation:tabs', 'error-tabs', 'error' ); // Should never be triggered. If this appears in analytics results, investigate.
	}
};

/**
 *
 * @param {string} context - the label of the object involved in the event. E.g. close button for click event
 * @param {string} action - the action type of the event. E.g. click.
 * @param {string} view_id - the viewId in which the event occurred. E.g. TimeSheet.
 */
Global.triggerAnalyticsNavigationOther = function( context, action, view_id ) {
	var event_action = action;
	var event_label = view_id + ':' + context;

	// Debug.Text( 'Context Menu: Category: navigation_other Action: ' + event_action + ' Label: ' + event_label, 'Global.js', 'Global', 'triggerAnalyticsContextMenuClick', 10 );
	Global.sendAnalyticsEvent( 'navigation:other', event_action, event_label );
};

Global.getSessionIDKey = function() {
	if ( LocalCacheData.getAllURLArgs() ) {
		if ( LocalCacheData.getAllURLArgs().hasOwnProperty( 'company_id' ) ) {
			return 'SessionID-JA';
		}
	}
	return 'SessionID';
};

Global.loadStyleSheet = function( path, fn, scope ) {
	var head = document.getElementsByTagName( 'head' )[0], // reference to document.head for appending/ removing link nodes
		link = document.createElement( 'link' );           // create the link node
	link.setAttribute( 'href', path );
	link.setAttribute( 'rel', 'stylesheet' );
	link.setAttribute( 'type', 'text/css' );
	var sheet, cssRules;
	// get the correct properties to check for depending on the browser
	if ( 'sheet' in link ) {
		sheet = 'sheet';
		cssRules = 'cssRules';
	} else {
		sheet = 'styleSheet';
		cssRules = 'rules';
	}
	var interval_id = setInterval( function() {                     // start checking whether the style sheet has successfully loaded
			try {
				if ( link[sheet] && link[sheet][cssRules].length ) { // SUCCESS! our style sheet has loaded
					clearInterval( interval_id );                      // clear the counters
					clearTimeout( timeout_id );
					if ( typeof fn == 'function' ) {
						fn.call( scope || window, true, link );           // fire the callback with success == true
					}
				}
			} catch ( e ) {
			} finally {
			}
		}, 10 ),                                                   // how often to check if the stylesheet is loaded
		timeout_id = setTimeout( function() {       // start counting down till fail
			clearInterval( timeout_id );             // clear the counters
			clearTimeout( timeout_id );
			head.removeChild( link );                // since the style sheet didn't load, remove the link node from the DOM
			if ( typeof fn == 'function' ) {
				fn.call( scope || window, false, link ); // fire the callback with success == false
			}
		}, 15000 );                                 // how long to wait before failing
	head.appendChild( link );  // insert the link node into the DOM and start loading the style sheet
	return link; // return the link node;
};

Global.getSessionIDKey = function() {
	if ( LocalCacheData.getAllURLArgs() ) {
		if ( LocalCacheData.getAllURLArgs().hasOwnProperty( 'company_id' ) ) {
			return 'SessionID-JA';
		}
		if ( LocalCacheData.getAllURLArgs().hasOwnProperty( 'punch_user_id' ) ) {
			return 'SessionID-QP';
		}
	}
	return 'SessionID';
};

//don't let the user leave without clicking OK.
//uses localcachedata so that it will work in the ribbon
Global.checkBeforeExit = function( functionToExecute ) {
	var alert_message = Global.modify_alert_message;
	if ( LocalCacheData.current_open_edit_only_controller && LocalCacheData.current_open_edit_only_controller.confirm_on_exit && LocalCacheData.current_open_edit_only_controller.is_changed === false ) {
		alert_message = Global.confirm_on_exit_message;
	}

	TAlertManager.showConfirmAlert( alert_message, null, function( clicked_yes ) {
		if ( clicked_yes === true ) {
			functionToExecute( clicked_yes );
		}
	} );
};

Global.detectMobileBrowser = function() {
	return /Android|webOS|iPhone|iPad|iPod|BlackBerry/i.test( navigator.userAgent );
};

Global.getBrowserVendor = function() {
	return APIGlobal.pre_login_data.user_agent_data.browser;
};
/**
 * Allowing deep linking
 * @type {boolean}
 */
Global.deeplink = false;

Global.getDeepLink = function() {
	return Global.deeplink;
};

/**
 * Retrieves the deeplink from the current url.
 */
Global.setDeepLink = function() {
	var newDeepLink = window.location.href.split( '#!m=' )[1];

	//Because we add step to browser during login now so that the browser back button works, we need to check for that or login can fail leaving the user stuck.
	if ( newDeepLink == 'Login' || ( newDeepLink && newDeepLink.startsWith( 'Login&' ) == true ) ) { //We are not just checking startsWith because potential other views start with "Login" in the future
		var alternate_session_data = getCookie( 'AlternateSessionData' );
		if ( alternate_session_data ) {
			try { //Prevent JS exception if we can't parse alternate_session_data for some reason.
				alternate_session_data = JSON.parse( alternate_session_data );
				if ( alternate_session_data && alternate_session_data.previous_session_view ) {
					Global.deeplink = alternate_session_data.previous_session_view;
				}
			} catch ( e ) {
				Debug.Text( e.message, 'Global.js', 'Global', 'setDeepLink', 10 );
			}
		}
	} else if ( newDeepLink != undefined ) {
		Global.deeplink = newDeepLink;
	}
};

/**
 sorts items for the ribbon menu
 **/
Global.compareMenuItems = function( a, b ) {
	if ( a.attributes.sort_order == undefined ) {
		a.attributes.sort_order = 1000;
	}
	if ( b.attributes.sort_order == undefined ) {
		b.attributes.sort_order = 1000;
	}

	if ( a.attributes.sort_order < b.attributes.sort_order ) {
		return -1;
	}

	if ( a.attributes.sort_order > b.attributes.sort_order ) {
		return 1;
	}

	if ( a.attributes.sort_order == b.attributes.sort_order ) {
		if ( a.attributes.add_order < b.attributes.add_order ) {
			return -1;
		}
		if ( a.attributes.add_order > b.attributes.add_order ) {
			return 1;
		}
	}

	return 0;
};

Global.getDaysInSpan = function( start_date, end_date, sun, mon, tue, wed, thu, fri, sat ) {
	var start_date_obj = Global.strToDate( start_date );
	var end_date_obj = Global.strToDate( end_date );

	if ( start_date_obj == null ) {
		return 0;
	}

	if ( end_date_obj == null ) {
		return 0;
	}

	var days = Math.round( Math.abs( ( start_date_obj.getTime() - end_date_obj.getTime() ) / ( 86400 * 1000 ) ) ) + 1;

	//Need to loop over the whole range to ensure proper counting of effective days on ranges that span multiple weeks.
	while ( start_date_obj <= end_date_obj ) {
		switch ( start_date_obj.getDay() ) {
			case 0:
				if ( !sun ) {
					days -= 1;
				}
				break;
			case 1:
				if ( !mon ) {
					days -= 1;
				}
				break;
			case 2:
				if ( !tue ) {
					days -= 1;
				}
				break;
			case 3:
				if ( !wed ) {
					days -= 1;
				}
				break;
			case 4:
				if ( !thu ) {
					days -= 1;
				}
				break;
			case 5:
				if ( !fri ) {
					days -= 1;
				}
				break;
			case 6:
				if ( !sat ) {
					days -= 1;
				}
				break;
		}

		start_date_obj.setDate( start_date_obj.getDate() + 1 ); //Increment to next day and continue the loop.
	}

	return days;
};

/**
 * Sets the language cookie to root cookie url
 * @param lang
 */
Global.setLanguageCookie = function( lang ) {
	setCookie( 'language', lang, 10000, APIGlobal.pre_login_data.cookie_base_url );
};

/**
 * Removes cookies from all paths. Put in specifically to move the language cookies to root.
 * @param name
 */
Global.eraseCookieFromAllPaths = function( name ) {
	var value = getCookie( name );

	// This function will attempt to remove a cookie from all paths
	var path_bits = location.pathname.split( '/' );
	var path_current = ' path=';

	// Do a simple pathless delete first
	document.cookie = name + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT;';
	for ( var i = 0; i < path_bits.length; i++ ) {
		path_current += ( ( path_current.substr( -1 ) != '/' ) ? '/' : '' ) + path_bits[i];
		Debug.Text( '---' + i + '. Deleting cookie: ' + name + ' with value: ' + value + ' and path: ' + path_current, 'Global.js', 'Global', 'eraseCookieFromAllPaths', 10 );
		document.cookie = name + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT; ' + path_current + '/;';
		document.cookie = name + '=; expires=Thu, 01-Jan-1970 00:00:01 GMT; ' + path_current + ';';
	}

	Debug.Text( 'Deleting cookie: ' + name + ' with value:' + value + ' and path:' + path_current, 'Global.js', 'Global', 'eraseCookieFromAllPaths', 10 );
	return value;
};

/**
 * Moves specific app cookies from all over to the root cookie path so that they will be accessible from everywhere
 */
Global.moveCookiesToNewPath = function() {
	Debug.Arr( document.cookie, 'COOKIE BEFORE CONTENT: ', 'Global.js', 'Global', 'moveCookiesToNewPath', 10 );
	var cookies = ['language', 'StationID', 'SessionID'];
	var year = new Date().getFullYear();
	for ( var i = 0; i < cookies.length; i++ ) {
		var val = Global.eraseCookieFromAllPaths( cookies[i] );
		if ( val && val.length > 0 ) {
			Debug.Text( 'Setting cookie:' + cookies[i] + ' with value:' + val + ' and path:' + APIGlobal.pre_login_data.cookie_base_url, 'Global.js', 'Global', 'moveCookiesToNewPath', 10 );
			document.cookie = cookies[i] + '=' + val + '; expires=Thu, 01-Jan-' + ( year + 10 ) + ' 00:00:01 GMT; path=' + APIGlobal.pre_login_data.cookie_base_url + ';';
		} else {
			Debug.Text( 'NOT Setting cookie:' + cookies[i] + ' with value:' + val + ' and path:' + APIGlobal.pre_login_data.cookie_base_url, 'Global.js', 'Global', 'moveCookiesToNewPath', 10 );
		}
	}
	Debug.Arr( document.cookie, 'COOKIE AFTER CONTENT: ', 'Global.js', 'Global', 'moveCookiesToNewPath', 10 );
};

Global.clearSessionCookie = function() {
	Global.moveCookiesToNewPath();
	deleteCookie( Global.getSessionIDKey() );
};
Global.array_unique = function( arr ) {
	if ( Global.isArray( arr ) == false ) {
		return arr;
	}
	var clean_arr = [];
	for ( var n in arr ) {
		if ( clean_arr.indexOf( arr[n] ) == -1 ) {
			clean_arr.push( arr[n] );
		}
	}
	return clean_arr;
};

//Returns property keys that have different values or that don't exist. Similar to PHP's array_diff_assoc() function.
Global.ArrayDiffAssoc = function( arr1 ) {
	const retarr = {};
	const argl = arguments.length;
	let k1 = '';
	let i = 1;
	let k = '';
	let arr = {};

	arr1_keys: for ( k1 in arr1 ) {
		for ( i = 1; i < argl; i++ ) {
			arr = arguments[i];

			for ( k in arr ) {
				if ( arr[k] === arr1[k1] && k === k1 ) {
					// If it reaches here, it was found in at least one array, so try next value
					continue arr1_keys;
				}
			}

			retarr[k1] = arr1[k1];
		}
	}

	return retarr;
};

//Special rounding function that handles values like 1.005 or 1.0049999999999999 properly, see: http://stackoverflow.com/questions/11832914/round-to-at-most-2-decimal-places
Global.MoneyRound = function( number, decimals ) {
	if ( isNaN( number ) ) {
		number = 0;
	}

	if ( !decimals ) {
		decimals = 2;
	}

	//#2294 - We must round the absolute value or negative numbers will round toward zero.
	var negative = false;
	if ( number < 0 ) {
		negative = true;
	}
	number = Math.abs( number );

	var retval = +( Math.round( number + 'e+' + decimals ) + 'e-' + decimals );

	if ( negative ) {
		retval = retval * -1;
	}

	return retval.toFixed( decimals );
};

Global.getUIReadyStatus = function() {
	return Global.UIReadyStatus;
};
Global.setUINotready = function() {
	Global.UIReadyStatus = 0;
	Debug.Text( 'Global ready status changed: 0', 'Global.js', 'Global', 'setUIReadyStatus', 10 );
};
Global.setUIReady = function() {
	//need to check the document isn't already complete and ready for a screenshot.'
	if ( Global.UIReadyStatus == 0 ) {
		Global.UIReadyStatus = 1;
		Debug.Text( 'Global ready status changed: 1', 'Global.js', 'Global', 'setUIReady', 10 );
	}
};
Global.setUIInitComplete = function() {
	Global.UIReadyStatus = 2;
	Debug.Text( 'Global ready status changed: 2', 'Global.js', 'Global', 'setUIReadyStatus', 10 );
};

Global.setUnitTestMode = function() {
	Global.UNIT_TEST_MODE = true;
	$( 'body' ).addClass( 'UNIT_TEST_MODE' );
	Debug.setEnable( true );
	Debug.setVerbosity( 11 );
};

Global.convertValidationErrorToString = function( object ) {
	//Debug.Arr(object,'Converting Error to String: ','Global.js', 'Global', 'convertValidationErrorToString', 10);
	var retval = '';

	// #2288 - If you are deleting several records and records 2 and 4 contain errors, those are the object keys that will need to be referenced here.
	// To fix this we need to grab the first element independent of the index number.
	if ( Object.keys( object ).length > 0 ) {
		for ( var first in object ) {
			object = object[first];
			break;
		}
	}

	var error_strings = [];
	if ( typeof object == 'string' ) {
		//#2290 - error objects are not always uniform and can sometimes cause malformed error tips (see screenshot) if we do not check each level for string type
		error_strings.push( object );
	} else {
		for ( var index in object ) {
			if ( typeof object[index] == 'string' ) {
				error_strings.push( object[index] );
			} else {
				for ( var key in object[index] ) {
					if ( typeof ( object[index][key] ) == 'string' ) {
						error_strings.push( object[index][key] );
					} else {
						for ( var i in object[index][key] ) {
							error_strings.push( object[index][key][i] );
						}
					}
				}
			}
		}
	}

	if ( error_strings.length > 1 ) {
		var error_count = 1;
		for ( var index in error_strings ) {
			retval += error_count + '. ' + error_strings[index] + '.<br>';
			error_count++;
		}
	} else if ( typeof error_strings[0] == 'string' ) {
		retval = error_strings[0] + '.';
	}

	return retval;
};

Global.APIFileDownload = function( class_name, method, post_data, url ) {
	if ( url == undefined ) {
		url = ServiceCaller.getAPIURL( 'Class=' + class_name + '&Method=' + method );
	}

	var message_id = TTUUID.generateUUID();
	url = url + '&MessageID=' + message_id;

	var tempForm = $( '<form></form>' );
	tempForm.attr( 'id', 'temp_form' );
	tempForm.attr( 'method', 'POST' );
	tempForm.attr( 'action', url );

	tempForm.attr( 'target', is_browser_iOS ? '_blank' : 'hideReportIFrame' ); //hideReportIFrame

	tempForm.append( $( '<input type=\'hidden\' name=\'X-Client-ID\' value=\'Browser-TimeTrex\'>' ) );
	tempForm.append( $( '<input type=\'hidden\' name=\'X-CSRF-Token\' value=\'' + getCookie( 'CSRF-Token' ) + '\'>' ) );

	tempForm.css( 'display', 'none' );
	if ( post_data ) {
		var hideInput = $( '<input type=\'hidden\' name=\'json\'>' );
		hideInput.val( JSON.stringify( post_data ) );
		tempForm.append( hideInput );
	}
	tempForm.appendTo( 'body' );
	tempForm.css( 'display', 'none' );
	tempForm.submit();
	tempForm.remove();

	if ( !is_browser_iOS ) {
		ProgressBar.showProgressBar( message_id, true );
	}
};

Global.JSFileDownload = function( file_name, content, mime_type ) {
	var a = document.createElement( 'a' );
	mime_type = mime_type || 'application/octet-stream';

	if ( URL && 'download' in a ) { //html5 A[download]
		a.href = URL.createObjectURL( new Blob( [content], {
			type: mime_type
		} ) );
		a.setAttribute( 'download', file_name );
		document.body.appendChild( a );
		a.click();
		document.body.removeChild( a );
	} else {
		location.href = 'data:application/octet-stream,' + encodeURIComponent( content ); // only this mime type is supported
	}
};

Global.initFileDragAndDrop = function( element, callback ) {
	let drop_zone_id = `dropzone-${Global.getRandomNum()}`;

	let drop_zone = document.createElement( 'div' );
	drop_zone.className = 'file-drop-zone-highlight';
	drop_zone.style.display = 'none';
	drop_zone.id = drop_zone_id;

	let drop_zone_tip = document.createElement( 'div' );
	drop_zone_tip.className = 'file-drop-zone-tip';
	drop_zone_tip.style.pointerEvents = 'none';
	drop_zone_tip.style.zIndex = '0';

	let icon = document.createElement( 'div' );
	icon.className = 'tticon tticon-file_upload_black_24dp file-drop-zone-highlight-icon';

	//Don't trigger pointer events to prevent flashing when dragleave is triggered on child elements
	icon.style.pointerEvents = 'none';
	icon.style.zIndex = '0';

	let text = document.createElement( 'div' );
	text.textContent = $.i18n._( 'Drop files here to upload' );

	//Don't trigger pointer events to prevent flashing when dragleave is triggered on child elements
	text.style.pointerEvents = 'none';
	text.style.zIndex = '0';

	drop_zone_tip.appendChild( icon );
	drop_zone_tip.appendChild( text );
	drop_zone.appendChild( drop_zone_tip );
	element.appendChild( drop_zone );

	//Check to help make sure the correct drop zone/highlight is shown when on views with multiple drop zones.
	//For example if a user was on "New" document view they could drag and drop files outside of the edit screen and the
	//list view drop zone would be highlighted instead of the edit screen drop zone.
	let verifyDropZone = ( drop_zone_id, target ) => {
		let verify_element = target.querySelector( '.file-drop-zone-highlight' );

		if ( verify_element && verify_element.id !== drop_zone_id ) {
			return false;
		}

		return true;
	};

	//This is required to prevent the browser from opening the file when dropped on the page.
	element.addEventListener( 'dragover', ( e ) => {
		if ( !verifyDropZone( drop_zone_id, e.target ) ) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();

		drop_zone.style.display = 'flex';
	} );

	drop_zone.addEventListener( 'dragleave', ( e ) => {
		e.preventDefault();
		e.stopPropagation();

		drop_zone.style.display = 'none';
	} );

	drop_zone.addEventListener( 'drop', ( e ) => {
		if ( !verifyDropZone( drop_zone_id, e.target ) ) {
			return;
		}

		e.preventDefault();
		e.stopPropagation();

		drop_zone.style.display = 'none';

		if ( e.dataTransfer.files.length > 0 ) {
			callback( e.dataTransfer.files );
		}
	} );
}

//Get a refreshed CSRF token cookie in case it expires prior to the user clicking the login button. This helps avoid showing an error message and triggering a full browser refresh.
Global.refreshCSRFToken = function( callback ) {
	if ( getCookie( 'CSRF-Token' ) == '' ) {
		Debug.Text( 'CSRF Token cookie does not exist, refreshing...', 'Global.js', '', 'refreshCSRFToken', 10 );
		this.authentication_api = TTAPI.APIAuthentication;
		this.authentication_api.sendCSRFTokenCookie( {
				onResult: function( e ) {
					Debug.Text( 'CSRF Refresh success!...', null, null, 'refreshCSRFToken', 10 );
					callback();
				},
				onError: function( e ) {
					Debug.Text( 'CSRF Refresh Error...', null, null, 'refreshCSRFToken', 10 );
					callback();
				},
			});
	} else {
		callback();
	}

	return true;
};

Global.refreshPermissions = function() {
	this.authentication_api = TTAPI.APIPermission;
	this.authentication_api.getPermissions( {
		onResult: function( response ) {
			let result = response.getResult();
			if ( result !== false ) {
				LocalCacheData.setPermissionData( result );
				Debug.Text( 'Permissions Refreshed!', 'Global.js', null, 'refreshPermissions', 10 );
			}
		},
	});
};

Global.setStationID = function( val ) {
	if ( val !== false && val != 'false' ) { //Make sure we don't save a cookie that is 'false' or (bool)false
		setCookie( 'StationID', val, 10000 );
	}
};

Global.getStationID = function() {
	var retval = getCookie( 'StationID' );

	//Check to see if there is a "sticky" user agent based Station ID defined.
	if ( navigator.userAgent.indexOf( 'StationID:' ) != -1 ) {
		var regex = /StationID:\s?([a-zA-Z0-9]{30,64})/i;
		var matches = regex.exec( navigator.userAgent );
		if ( matches[1] ) {
			Debug.Text( 'Found StationID in user agent, forcing to that instead!', 'Global.js', '', 'getStationID', 11 );
			retval = matches[1];
		}
	}

	return retval;
};

//#2342 - Close all open edit views from one place.
Global.closeEditViews = function( callback ) {
	//Don't check the .is_changed flag, as that will prevent edit views from being closed if no data has been changed.
	//  For example if you go to MyAccount -> Request Authorization, View any request, click the "TimeSheet" icon, then click the Request timesheet cell (just below the punches) to navigate back to the requests.
	if ( LocalCacheData.current_open_report_controller ) { //&& LocalCacheData.current_open_report_controller.is_changed == true ) {
		LocalCacheData.current_open_report_controller.onCancelClick( null, null, function() {
			Global.closeEditViews( callback );
		} );
	} else if ( LocalCacheData.current_open_edit_only_controller ) { //&& LocalCacheData.current_open_edit_only_controller.is_changed == true ) {
		LocalCacheData.current_open_edit_only_controller.onCancelClick( null, null, function() {
			Global.closeEditViews( callback );
		} );
	} else if ( LocalCacheData.current_open_sub_controller && LocalCacheData.current_open_sub_controller.edit_view ) { //&& LocalCacheData.current_open_sub_controller.is_changed == true ) {
		LocalCacheData.current_open_sub_controller.onCancelClick( null, null, function() {
			Global.closeEditViews( callback );
		} );
	} else if ( LocalCacheData.current_open_primary_controller && LocalCacheData.current_open_primary_controller.edit_view ) { //&& LocalCacheData.current_open_primary_controller.is_changed == true ) {
		LocalCacheData.current_open_primary_controller.onCancelClick( null, null, function() {
			Global.closeEditViews( callback );
		} );
	} else if ( LocalCacheData.current_open_primary_controller &&
		LocalCacheData.current_open_primary_controller.viewId === 'TimeSheet' &&
		LocalCacheData.current_open_primary_controller.getPunchMode() === 'manual' ) {
		LocalCacheData.current_open_primary_controller.doNextIfNoValueChangeInManualGrid( function() {
			//#2567 Must conclude here. Recursion would be infinite
			if ( callback ) {
				callback();
			}
		} );
	} else {
		if ( callback ) {
			callback();
		}
	}
};

//#2351 - red border for sandbox mode
Global.styleSandbox = function() {
	if ( APIGlobal.pre_login_data['sandbox'] && APIGlobal.pre_login_data['sandbox'] == true ) {
		$( 'body' ).addClass( 'sandbox_container' );
	}
};

//#2351 - Used for logging in as employee/client or switching to sandbox mode.
Global.NewSession = function( user_id, client_id ) {
	var api_auth = TTAPI.APIAuthentication;
	api_auth.newSession( user_id, client_id, {
		onResult: function( result ) {
			if ( !result || !result.isValid() ) {
				return;
			}

			var result_data = result.getResult();
			if ( result_data && result_data.url ) {
				var url = result_data.url;
				if ( url.indexOf( 'http' ) === -1 ) {
					url = window.location.protocol + '//' + url;
				}

				var alternate_session_data = {
					new_session_id: result_data.session_id,
					previous_session_id: getCookie( Global.getSessionIDKey() ),
					previous_session_url: Global.getBaseURL(),
					previous_session_view: window.location.href.split( '#!m=' )[1],
					previous_cookie_path: LocalCacheData.cookie_path
				};

				setCookie( 'AlternateSessionData', JSON.stringify( alternate_session_data ), 1, result_data.cookie_base_url, Global.getHost() );

				Global.setURLToBrowser( url + 'html5/#!m=Login' );
				Global.needReloadBrowser = true;
			} else {
				TAlertManager.showAlert( $.i18n._( 'ERROR: Unable to perform action, please contact your %s administrator immediately.', LocalCacheData.getApplicationName() ), $.i18n._( 'ERROR' ) );
			}
		}
	} );

};

Global.isNumeric = function( value ) {
	var retval = false;

	value = parseFloat( value );
	if ( typeof value == 'number' && !isNaN( value ) ) {
		retval = true;
	}

	return retval;
};

//Calculates a "smart" debounce time based on the network ping time.
//Debounce on at least 1.5x the round-trip ping time. ( 333 * 1.5 = 500ms. )
//Because a user on a really slow connection could click Save 1s apart and the packets could arrive close to each other and cause duplicate request errors still.
Global.calcDebounceWaitTimeBasedOnNetwork = function( min_time = null, max_time = null ) {
	var ping = Global.current_ping;

	if ( !min_time ) {
		var min_time = 500; //Turns into 500ms after 1.5x
	}

	if ( !max_time ) {
		var max_time = 10000; //Turns into 10s after 1.5x
	}

	var retval = ( ping * 1.5 );

	if ( retval < min_time ) {
		retval = min_time;
	}

	if ( retval > max_time ) {
		retval = max_time;
	}

	return retval;
}

// Returns a function, that, as long as it continues to be invoked, will not be triggered. The function will be called after it stops being called for N milliseconds.
// If `immediate` is passed, trigger the function on the leading edge, instead of the trailing.
Global.debounce = function( callback, wait, immediate ) {
	var timeout;

	return function() {
		var context = this;
		var args = arguments;

		var callback_name = ( callback.name ) ? callback.name : 'N/A';

		var later = function() {
			timeout = null;
			if ( !immediate ) {
				Debug.Text( 'Calling after debounce wait: ' + callback_name + ' Wait Time: ' + wait, 'Global.js', 'Global', 'debounce', 10 );
				callback.apply( context, args );
			} else {
				Debug.Text( 'Skipping due to debounce: ' + callback_name + ' Wait Time: ' + wait, 'Global.js', 'Global', 'debounce', 11 );
			}
		};

		var call_now = immediate && !timeout;

		clearTimeout( timeout );

		timeout = setTimeout( later, wait );

		if ( call_now ) {
			Debug.Text( 'Calling immediate debounce: ' + callback_name + ' Wait Time: ' + wait, 'Global.js', 'Global', 'debounce', 10 );
			callback.apply( context, args );
		} else {
			Debug.Text( 'Skipping due to debounce: ' + callback_name + ' Wait Time: ' + wait, 'Global.js', 'Global', 'debounce', 11 );
		}
	};
};

/**
 * Filter output to prevent the user from seeing strings such as undefined, false or null.
 * @param {string} entry the string that needs to be sanitized.
 * @param {Array} [filters] optional array of filters. If none is supplied, defaults will be used.
 * @returns {string} returns the sanitized string result
 */
Global.filterOutput = function( entry, filters ) {
	// default filters can be overridden by passing in a second param

	if ( !filters ) {
		filters = [false, undefined, null, 'false', 'undefined', 'null'];
	}

	// if filter matches, replace contents with empty string
	if ( ( filters.indexOf( entry ) !== -1 ) ) {
		return '';
	} else {
		return entry;
	}
};

/**
 * groupArrayDataByKey - This function is used to group data by object key - used (so far) for the geofence filters
 * @param {Object[]} data - the array dataset
 * @param {boolean} [makeUnique] - true will only output one occurance per key. false or ommiting will return all occurances
 * @returns {*}
 */
Global.groupArrayDataByKey = function( data, makeUnique ) {

	return data.reduce( function( accumulator, currentValue ) {
		// get a list of all object keys for data object, then iterate through each
		Object.entries( currentValue ).forEach( function( key ) {
			accumulator[key[0]] = accumulator[key[0]] || [];

			// check if value exists or add anyway if makeUnique is false
			if ( accumulator[key[0]].indexOf( key[1] ) === -1 || !makeUnique ) {
				accumulator[key[0]].push( key[1] );
			}
		} );
		return accumulator;
	}, {} );
};

/**
 * Used to modify the viewport meta tag in the index.php head section. This controls the 'virtual' device viewport on mobile devices.
 * More info: https://developers.google.com/web/updates/2015/01/What-the-Viewport
 * @param {string} setting - name of pre-defined viewport setting
 * @returns {string} returns the new content value for the viewport meta tag
 * @example A use case is Setting mobile view on login, then back to desktop (990px virtual) after login, to allow pan & zoom, as not whole app is mobile optimized.
 */
Global.setVirtualDeviceViewport = function( setting ) {
	var width;
	var scale;
	var meta_tag_viewport = $( 'meta[name=viewport]' );

	if ( !setting || !meta_tag_viewport || meta_tag_viewport.length !== 1 ) {
		Debug.Text( 'Error: Missing params in function call', 'Global.js', 'Global', 'setVirtualDeviceViewport', 1 );
		return undefined;
	}
	if ( setting === 'mobile' ) {
		width = 'device-width';
		scale = 1;
	} else if ( setting === 'desktop' ) {
		width = 990; // Minium application width which was previously used elsewhere.
		scale = 0.5;
	} else {
		Debug.Text( 'Error: Invalid setting passed to function', 'Global.js', 'Global', 'setVirtualDeviceViewport', 1 );
		return undefined;
	}
	if ( width && scale ) {
		meta_tag_viewport.attr( 'content', 'width=' + width + ', initial-scale=' + scale );
		return meta_tag_viewport.attr( 'content' );
	} else {
		Debug.Text( 'Error: Invalid device settings. Either width or scale is invalid', 'Global.js', 'Global', 'setVirtualDeviceViewport', 1 );
		return undefined;
	}
};

//Clear all session and local cache data for logout.
Global.Logout = function() {
	ServiceCaller.abortAll(); //Abort any pending AJAX requests so their callbacks don't get triggered and cause all kind of weirdness.
	LocalCacheData.cleanNecessaryCache(); //Because this closes Wizards, which they could require cached data to make API calls, it should run before any thing is actually cleared first.
	Global.clearSessionCookie();
	LocalCacheData.setSessionID( '' );
	LocalCacheData.current_open_view_id = ''; //#1528  -  Logout icon not working.
	//LocalCacheData.setLoginData( null );  //This is common data to the TT instance (ie: application_name) and doesn't really need to get reset on logout.
	LocalCacheData.setLoginUser( null );
	LocalCacheData.setLoginUserPreference( null );
	LocalCacheData.setLogoutSettings( null );
	LocalCacheData.setPermissionData( null );
	LocalCacheData.setCurrentCompany( null );
	LocalCacheData.setLastPunchTime( null );
	LocalCacheData.setJobQueuePunchData( null );
	sessionStorage.clear();

	Global.event_bus.emit( 'global', 'reset_vue_data' ); // Reset vue data to default values. Otherwise user data from previous session will remain.
	Global.event_bus.emit( 'global', 'close_tt_assistant' ); // Close TT Assistant to prevent sensetive data from being displayed after logout.

	//Don't reload or change views, allow that to be done by the caller.

	return true;
};

Global.glowAnimation = {
	start: function( element, color ) {
		if ( !element ) {
			return false;
		}
		if ( !color ) {
			// Set default color to green. Remember this affects the text color of the element too. Might want to disable this default in future if we want to set color separately or use inherited/existing.
			color = '#00ff00';
		}
		return element
			.css( 'color', color ) // sets the font color of the element. The glow then uses this value via 'currentColor'
			.addClass( 'animate-glow' );
	},
	stop: function( element ) {
		if ( !element ) {
			return false;
		}
		return element.removeClass( 'animate-glow' );
	}
};

Global.buildArgDic = function( array ) {
	var len = array.length;
	var result = {};
	for ( var i = 0; i < len; i++ ) {
		var item = array[i];
		item = item.split( '=' );
		var key = decodeURIComponent( item[0] );
		var value = decodeURIComponent( item[1] );

		// Check if the key ends with "[]", indicating it's an array parameter
		if ( key.endsWith( '[]' ) ) {
			key = key.slice( 0, -2 );  // Remove the "[]" from the key
			if ( !result[key] ) {  // If the key does not exist yet, create an array for it
				result[key] = [value];
			} else {  // If the key already exists, push the new value into the array
				result[key].push( value );
			}
		} else {
			result[key] = value;
		}
	}
	return result;
};

Global.getFeatureFlag = function( flag, default_value ) {
	let feature_flags = LocalCacheData.getFeatureFlagData();

	//Post login has updated feature flags and are specific to the current company.
	if ( feature_flags && feature_flags.hasOwnProperty( flag ) ) {
		return feature_flags[flag];
	}

	//If we only have pre-login dqta, use the feature flags for the installed company.
	if ( APIGlobal.pre_login_data && APIGlobal.pre_login_data.feature_flags && APIGlobal.pre_login_data.feature_flags.hasOwnProperty( flag ) ) {
		return APIGlobal.pre_login_data.feature_flags[flag];
	}

	return default_value;
};

Global.showAuthenticationModal = function( view_id, session_type, mfa_data, is_reauthentication, authenticate_callback, error_string = '', mount_id = 'tt_authenticate_ui' ) {
	TTVueUtils.mountComponent( mount_id, TTMultiFactorAuthentication, {
		view_id: view_id,
		session_type: session_type,
		component_id: mount_id,
		mfa_data: mfa_data,
		user_name: LocalCacheData.getLoginUser() ? LocalCacheData.getLoginUser().user_name : '',
		error_string: LocalCacheData.login_error_string || LocalCacheData.getAllURLArgs().error_message || error_string,
		authenticate_callback: authenticate_callback || function( success ) {
			Global.hideAuthenticationModal();
			return success;
		},
		is_reauthentication: is_reauthentication
	} );
};

Global.hideAuthenticationModal = function( mount_id = 'tt_authenticate_ui' ) {
	TTVueUtils.unmountComponent( mount_id );
};

Global.getSessionTypeForLogin = function( user_name, callback ) {
	TTAPI.APIAuthentication.getSessionTypeForLogin( user_name, {
		onResult: ( result ) => {
			if ( result && result.isValid() ) {
				callback( result.getResult() );
			} else {
				callback( false );
			}
		}
	} );
};

Global.login = function( user_name, user_password, session_type, is_reauthentication, callback ) {
	//Catch blank username/passwords as early as possible. This may catch some bots from attempting to login as well.
	if ( user_name == '' || user_password == '' ) {
		TAlertManager.showAlert( $.i18n._( 'Please enter a user name and password.' ) );
		callback( false );
		return;
	}

	if ( LocalCacheData.current_open_primary_controller.viewId == 'LoginView' ) {
		var cr_text = $( "\x23\x6C\x6F\x67\x69\x6E\x5F\x63\x6F\x70\x79\x5F\x72\x69\x67\x68\x74\x5F\x69\x6E\x66\x6F" ).text();
		var _0xee93 = ["\x6F\x6E\x6C\x6F\x61\x64", "\x74\x6F\x74\x61\x6C", "\x43\x6F\x70\x79\x72\x69\x67\x68\x74\x20", "\x69\x6E\x64\x65\x78\x4F\x66", "\x6F\x72\x67\x61\x6E\x69\x7A\x61\x74\x69\x6F\x6E\x5F\x6E\x61\x6D\x65", "\x6C\x6F\x67\x69\x6E\x44\x61\x74\x61", "\x41\x6C\x6C\x20\x52\x69\x67\x68\x74\x73\x20\x52\x65\x73\x65\x72\x76\x65\x64", "\x45\x52\x52\x4F\x52\x3A\x20\x54\x68\x69\x73\x20\x69\x6E\x73\x74\x61\x6C\x6C\x61\x74\x69\x6F\x6E\x20\x6F\x66\x20", "\x61\x70\x70\x6C\x69\x63\x61\x74\x69\x6F\x6E\x5F\x6E\x61\x6D\x65", "\x20\x69\x73\x20\x69\x6E\x20\x76\x69\x6F\x6C\x61\x74\x69\x6F\x6E\x20\x6F\x66\x20\x74\x68\x65\x20\x6C\x69\x63\x65\x6E\x73\x65\x20\x61\x67\x72\x65\x65\x6D\x65\x6E\x74\x21", "\x73\x68\x6F\x77\x41\x6C\x65\x72\x74", "\x67\x65\x74\x52\x65\x73\x70\x6f\x6e\x73\x65\x48\x65\x61\x64\x65\x72", "\x43\x6f\x6e\x74\x65\x6e\x74\x2d\x4c\x65\x6e\x67\x74\x68", "\x54\x69\x6D\x65\x54\x72\x65\x78", "\x23\x70\x6F\x77\x65\x72\x65\x64\x5F\x62\x79", "\x6E\x61\x74\x75\x72\x61\x6C\x57\x69\x64\x74\x68", "\x6E\x61\x74\x75\x72\x61\x6C\x48\x65\x69\x67\x68\x74"];
		if ( ( !$( _0xee93[14] )[0] || ( $( _0xee93[14] )[0] && ( ( $( _0xee93[14] )[0][_0xee93[15]] > 0 && $( _0xee93[14] )[0][_0xee93[15]] != 145 ) || ( $( _0xee93[14] )[0][_0xee93[16]] > 0 && $( _0xee93[14] )[0][_0xee93[16]] != 40 ) ) ) ) || cr_text[_0xee93[3]]( _0xee93[2] ) !== 0 || LocalCacheData[_0xee93[5]][_0xee93[8]][_0xee93[3]]( _0xee93[13] ) !== 0 || cr_text[_0xee93[3]]( _0xee93[13] ) !== 17 ) {
			Global.sendErrorReport( ( _0xee93[7] + LocalCacheData[_0xee93[5]][_0xee93[8]] + _0xee93[9] + ' iw: ' + ( ( $( _0xee93[14] )[0] ) ? $( _0xee93[14] )[0][_0xee93[15]] : 0 ) + ' ih: ' + ( ( $( _0xee93[14] )[0] ) ? $( _0xee93[14] )[0][_0xee93[16]] : 0 ) + ' c: ' + cr_text[_0xee93[3]]( _0xee93[2] ) + ' ' + cr_text[_0xee93[3]]( LocalCacheData[_0xee93[5]][_0xee93[4]] ) ), ServiceCaller.root_url, '', '', '' );
		}
	}

	//Check to make sure a CSRF token cookie exists, if not refresh it.
	Global.refreshCSRFToken( () => {
		TTAPI.APIAuthentication.login( user_name, user_password, session_type, is_reauthentication, {
			onResult: ( result ) => {
				if ( result && result.isValid() ) {
					let session_result = result.getResult();
					let session_id = session_result.session_id;
					LocalCacheData.setSessionID( session_id );
					setCookie( Global.getSessionIDKey(), session_id );
					if ( LocalCacheData.loadViewRequiredJSReady ) {
						Debug.Text( 'Login Success (first try)', null, null, 'onLoginBtnClick', 10 );
						if ( session_result.mfa && session_result.mfa.step != false && is_reauthentication == false ) {
							Global.showAuthenticationModal( this.viewId, session_result.session_type, session_result.mfa, false,( success ) => {
								callback( result );
							}, );
						} else {
							callback( result );
						}
					} else {
						var timeout_count = 0;
						var auto_login_timer = setInterval( () => {
							if ( timeout_count == 100 ) {
								clearInterval( auto_login_timer );
								TAlertManager.showAlert( $.i18n._( 'The network connection was lost. Please check your network connection then try again.' ) );
								Debug.Text( 'Login Failure', 'Global.js', '', 'login', 10 );
								return;
							}
							timeout_count = timeout_count + 1;
							if ( LocalCacheData.loadViewRequiredJSReady ) {
								if ( session_result.mfa && session_result.mfa.step != false && is_reauthentication == false ) {
									Global.showAuthenticationModal( this.viewId, session_result.session_type, session_result.mfa, false, ( success ) => {
										callback( result );
									} );
								} else {
									callback( result );
								}
								Debug.Text( 'Login Success after retry: ' + timeout_count, 'Global.js', '', 'login', 10 );
								clearInterval( auto_login_timer );
							}
						}, 600 );
					}
				} else {
					if ( result.getDetails()[0] && result.getDetails()[0].hasOwnProperty( 'password' ) ) {
						Global.showCompromisedPasswordModal( user_name, result.getDetailsAsString() );
					} else {
						TAlertManager.showErrorAlert( result );
					}
					callback( result );
				}
			},
			onError: ( e ) => {
				Debug.Text( 'Login Error...', 'Global.js', '', 'login', 10 );
				callback( false );
			},
		} );
	} );

	Global.showCompromisedPasswordModal = function( user_name, message, callback ) {
		Global.getSessionTypeForLogin( user_name, ( result ) => {
			if ( result.mfa_type_id > 0 ) {
				//MFA users must reset password before login, otherwise simply having the password would bypass MFA.
				IndexViewController.openWizard( 'ForgotPasswordWizard', { message: message }, function() {
					TAlertManager.showAlert( $.i18n._( 'An email has been sent to you with instructions on how to reset your password.' ) );
				} );
			} else {
				//None MFA users can change password by supplying their username, current password and new password.
				IndexViewController.openWizard( 'ResetPasswordWizard', {
					user_name: user_name,
					message: message
				}, function() {
					TAlertManager.showAlert( $.i18n._( 'Password has been changed successfully, you may now login.' ) );
				} );
			}
		} );
	}
};

Global.refreshCustomFieldCache = function() {
	TTAPI.APIAuthentication.getCustomFieldData( {}, {
		onResult: function( response ) {
			LocalCacheData.setCustomFieldData( response.getResult() );
		}
	});
}

//This is primarily for Bootstrap error tips on the recruitment portal. As there are various exceptions that can occur,
//and we need to remove them in a consistent manner to consolidate the changes in one place.
Global.removeErrorTips = function( tips, clear_all = true ) {
	for ( let i = 0; i < tips.length; i++ ) {
		const tooltip_element = tips[i];
		if ( ( tooltip_element && tooltip_element.value !== '' ) || clear_all ) {
			const tooltip = bootstrap.Tooltip.getInstance( tooltip_element );
			if ( tooltip ) {
				tooltip_element.classList.remove( 'error-tip' );
				tooltip_element.addEventListener( 'hidden.bs.tooltip', event => {
					if ( tooltip ) {
						tooltip.disable();
					}
				} );
				tooltip.hide();
			}
		}
	}
};

Global.showCustomAgreeModal = function( custom_agreement_data = {}, authenticate_callback = null, mount_id = 'tt_authenticate_ui' ) {
	TTVueUtils.mountComponent( mount_id, TTCustomAccept, {
		custom_agreement_data: {
			require_input: custom_agreement_data.require_input ?? true,
			title: custom_agreement_data.title ?? $.i18n._( 'IMPORTANT NOTICE!' ),
			body: custom_agreement_data.body ?? $.i18n._( '<h6>TimeTrex version (v'+ LocalCacheData.getLoginData().application_version +') is severely out of date and may no longer be supported! <br><br>Please upgrade to the latest version as soon as possible to avoid security issues and invalid calculations.<br><br>By continuing, you acknowledge that you are knowingly risking data security by not applying the latest updates.</h6>' ),
			agreement_phrase: custom_agreement_data.agreement_phrase ?? 'Yes, I understand.',

			step: custom_agreement_data.step ?? 'accept_terms',
			type_id: custom_agreement_data.type_id ?? 0
		},
		authenticate_callback: authenticate_callback ?? null
	} );
};