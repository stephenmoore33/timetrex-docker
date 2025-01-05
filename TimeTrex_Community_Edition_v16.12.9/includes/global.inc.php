<?php
/*********************************************************************************
 *
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2021 TimeTrex Software Inc.
 *
 * This program is free software; you can redistribute it and/or modify it under
 * the terms of the GNU Affero General Public License version 3 as published by
 * the Free Software Foundation with the addition of the following permission
 * added to Section 15 as permitted in Section 7(a): FOR ANY PART OF THE COVERED
 * WORK IN WHICH THE COPYRIGHT IS OWNED BY TIMETREX, TIMETREX DISCLAIMS THE
 * WARRANTY OF NON INFRINGEMENT OF THIRD PARTY RIGHTS.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
 * FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public License for more
 * details.
 *
 *
 * You should have received a copy of the GNU Affero General Public License along
 * with this program; if not, see http://www.gnu.org/licenses or write to the Free
 * Software Foundation, Inc., 51 Franklin Street, Fifth Floor, Boston, MA
 * 02110-1301 USA.
 *
 *
 * You can contact TimeTrex headquarters at Unit 22 - 2475 Dobbin Rd. Suite
 * #292 West Kelowna, BC V4T 2E9, Canada or at email address info@timetrex.com.
 *
 *
 * The interactive user interfaces in modified source and object code versions
 * of this program must display Appropriate Legal Notices, as required under
 * Section 5 of the GNU Affero General Public License version 3.
 *
 *
 * In accordance with Section 7(b) of the GNU Affero General Public License
 * version 3, these Appropriate Legal Notices must retain the display of the
 * "Powered by TimeTrex" logo. If the display of the logo is not reasonably
 * feasible for technical reasons, the Appropriate Legal Notices must display
 * the words "Powered by TimeTrex".
 *
 ********************************************************************************/

date_default_timezone_set( 'GMT' ); //Default timezone to UTC until we can at least determine or set another one. This should also prevent: PHP ERROR - WARNING(2): getdate(): It is not safe to rely on the system's timezone settings. You are *required* to use the date.timezone setting or the date_default_timezone_set() function

//Other than when using the installer, disable URL-aware fopen wrappers like: http:// just as a fail safe. All user input must still be sanitized of course.
ini_set( 'allow_url_fopen', 0 );

//BUG in PHP 5.2.2 that causes $HTTP_RAW_POST_DATA not to be set. Work around it.
//This is deprecated in PHP v5.6 and removed in PHP v7, so switch to just always populating it.
$HTTP_RAW_POST_DATA = file_get_contents( 'php://input' );

if ( !isset( $_SERVER['HTTP_HOST'] ) ) {
	$_SERVER['HTTP_HOST'] = 'localhost';
}

ob_start(); //Take care of GZIP in Apache

if ( ini_get( 'max_execution_time' ) < 1800 ) {
	ini_set( 'max_execution_time', 1800 );
}

define( 'APPLICATION_VERSION', '16.12.9' );
define( 'APPLICATION_VERSION_DATE', 1727049600 ); //Release date of version. CMD: php -r 'echo "\n". strtotime("23-Sep-2024")."\n\n";'
define( 'APPLICATION_BUILD', APPLICATION_VERSION . '-' . trim( file_get_contents( __DIR__ . '/../BUILD.VERSION' ) ) );

if ( strtoupper( substr( PHP_OS, 0, 3 ) ) == 'WIN' ) {
	define( 'OPERATING_SYSTEM', 'WIN' );
} else {
	define( 'OPERATING_SYSTEM', 'LINUX' );
}

/*
	Find Config file.
	Can use the following line in .htaccess or Apache virtual host definition to define a config file outside the document root.
	SetEnv TT_CONFIG_FILE /etc/timetrex/timetrex.ini.php

	Or from the CLI:
	export TT_CONFIG_FILE=/etc/timetrex/timetrex.ini.php
*/
if ( isset( $_SERVER['TT_CONFIG_FILE'] ) && $_SERVER['TT_CONFIG_FILE'] != '' ) {
	define( 'CONFIG_FILE', $_SERVER['TT_CONFIG_FILE'] );
} else {
	define( 'CONFIG_FILE', dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'timetrex.ini.php' );
}

/*
	Config file outside webroot.
*/
if ( file_exists( CONFIG_FILE ) ) {
	if ( isset( $config_vars ) ) { //Allow config_var to be overwritten before global.inc.php is loaded. Used in QueueWorker.php for forcing a persistent connection.
		$config_vars = array_merge_recursive( parse_ini_file( CONFIG_FILE, true ), $config_vars );
	} else {
		$config_vars = parse_ini_file( CONFIG_FILE, true );
	}
	if ( $config_vars === false ) {
		echo "ERROR: Config file (" . CONFIG_FILE . ") contains a syntax error! If your passwords contain special characters you need to wrap them in double quotes, ie:<br>\n password = \"test!1!me\"\n";
		exit( 1 );
	}
} else {
	echo "ERROR: Config file (" . CONFIG_FILE . ") does not exist or is not readable!\n";
	exit( 1 );
}

if ( defined( 'PRODUCTION' ) == false ) {
	if ( isset( $config_vars['debug']['production'] ) && $config_vars['debug']['production'] == 1 ) {
		define( 'PRODUCTION', true );
	} else {
		define( 'PRODUCTION', false );
	}
}

if ( defined( 'DEMO_MODE' ) == false ) {
	if ( isset( $config_vars['other']['demo_mode'] ) && $config_vars['other']['demo_mode'] == 1 ) {
		define( 'DEMO_MODE', true );
	} else {
		define( 'DEMO_MODE', false );
	}
}
//**REMOVING OR CHANGING THIS APPLICATION NAME AND ORGANIZATION URL IS IN STRICT VIOLATION OF THE LICENSE AND COPYRIGHT AGREEMENT**//
define( 'APPLICATION_NAME', ( PRODUCTION == false && DEMO_MODE == false ) ? 'TimeTrex-Debug' : 'TimeTrex' );
define( 'ORGANIZATION_NAME', 'TimeTrex' );
define( 'ORGANIZATION_URL', 'www.TimeTrex.com' );
if ( isset( $config_vars['other']['deployment_on_demand'] ) && $config_vars['other']['deployment_on_demand'] == 1 ) {
	define( 'DEPLOYMENT_ON_DEMAND', true );
} else {
	define( 'DEPLOYMENT_ON_DEMAND', false );
}

if ( isset( $config_vars['other']['primary_company_id'] ) && $config_vars['other']['primary_company_id'] != '' ) {
	define( 'PRIMARY_COMPANY_ID', (string)$config_vars['other']['primary_company_id'] );
} else {
	define( 'PRIMARY_COMPANY_ID', false );
}

//Windows doesn't define LC_MESSAGES, so lets do it manually here.
if ( defined( 'LC_MESSAGES' ) == false ) {
	define( 'LC_MESSAGES', 6 );
}

if ( PRODUCTION == TRUE ) {
	error_reporting( E_ALL & ~E_DEPRECATED & ~E_STRICT ); //Disable DEPRECATED & STRICT notices when in production.
}

/**
 * Converts human readable size to bytes.
 * @param int|float|string $size Human readable size, ie: 1K, 1M, 1G
 * @return int
 */
function convertHumanSizeToBytes( $size ) {
	$retval = (int)str_ireplace( [ 'G', 'M', 'K' ], [ '000000000', '000000', '000' ], $size );
	//Debug::text('Input Size: '. $size .' Bytes: '. $retval, __FILE__, __LINE__, __METHOD__, 9);
	return $retval;
}

//If memory limit is set below the minimum required, just bump it up to that minimum. If its higher, keep the higher value.
$memory_limit = convertHumanSizeToBytes( ini_get( 'memory_limit' ) );
if ( $memory_limit >= 0 && $memory_limit < 512000000 ) { //Use * 1000 rather than * 1024 for easier parsing of G, M, K -- Make sure we consider -1 as the limit.
	ini_set( 'memory_limit', '512000000' );
}
unset( $memory_limit );

//IIS 5 doesn't seem to set REQUEST_URI, so attempt to build one on our own
//This also appears to fix CGI mode.
//Inspired by: http://neosmart.net/blog/2006/100-apache-compliant-request_uri-for-iis-and-windows/
if ( !isset( $_SERVER['REQUEST_URI'] ) ) {
	if ( isset( $_SERVER['SCRIPT_NAME'] ) ) {
		$_SERVER['REQUEST_URI'] = $_SERVER['SCRIPT_NAME'];
	} else if ( isset( $_SERVER['PHP_SELF'] ) ) {
		$_SERVER['REQUEST_URI'] = $_SERVER['PHP_SELF'];
	}

	if ( isset( $_SERVER['QUERY_STRING'] ) && $_SERVER['QUERY_STRING'] != '' ) {
		$_SERVER['REQUEST_URI'] .= '?' . $_SERVER['QUERY_STRING'];
	}
}

//HTTP Basic authentication doesn't work properly with CGI/FCGI unless we decode it this way.
if ( isset( $_SERVER['HTTP_AUTHORIZATION'] ) && $_SERVER['HTTP_AUTHORIZATION'] != '' && stripos( php_sapi_name(), 'cgi' ) !== false ) {
	//<IfModule mod_rewrite.c>
	//RewriteEngine on
	//RewriteRule .* - [E=HTTP_AUTHORIZATION:%{HTTP:Authorization},L]
	//</IfModule>
	//Or this instead:
	//SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1
	[ $_SERVER['PHP_AUTH_USER'], $_SERVER['PHP_AUTH_PW'] ] = array_pad( explode( ':', base64_decode( substr( $_SERVER['HTTP_AUTHORIZATION'], 6 ) ) ), 2, null );
}


require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . 'ClassMap.inc.php' );

/**
 * @param $name
 * @return bool
 */
function TTAutoload( $name ) {
	global $global_class_map, $profiler; //$config_vars needs to be here, otherwise TTPDF can't access the cache_dir.

	//if ( isset( $profiler ) ) {
	//	$profiler->startTimer( '__autoload' );
	//}

	if ( isset( $global_class_map[$name] ) ) {
		$file_name = Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . $global_class_map[$name];
	} else {
		//If the class name contains "plugin", try to load classes directly from the plugins directory.
		if ( $name == 'PEAR' ) {
			return false; //Skip trying to load PEAR class as it fails anyways.
		} else if ( strpos( $name, 'Plugin' ) === false ) {
			$file_name = $name . '.class.php';
		} else {
			$file_name = Environment::getBasePath() . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'plugins' . DIRECTORY_SEPARATOR . str_replace( 'Plugin', '', $name ) . '.plugin.php';
		}
	}

	//Use include_once() instead of require_once so the installer doesn't Fatal Error without displaying anything.
	//include_once() is redundant in __autoload.
	//Debug::Text('Autoloading Class: '. $name .' File: '. $file_name, __FILE__, __LINE__, __METHOD__,10);
	//Debug::Arr(Debug::BackTrace(), 'Backtrace: ', __FILE__, __LINE__, __METHOD__,10);
	//Remove the following @ symbol to help in debugging parse errors.
	if ( file_exists( $file_name ) === true ) {
		include( $file_name );
	} else {
		return false; //File doesn't exist, could be external library or just incorrect name.
	}

	//if ( isset( $profiler ) ) {
	//	$profiler->stopTimer( '__autoload' );
	//}

	return true;
}

spl_autoload_register( 'TTAutoload' ); //Registers the autoloader mainly for use with PHPUnit

/**
 * The basis for the plugin system, instantiate all classes through this, allowing the class to be overloaded on the fly by a class in the plugin directory.
 * ie: $uf = TTNew( 'UserFactory' ); OR $uf = TTNew( 'UserFactory', $arg1, $arg2, $arg3 );
 * @param $class_name
 * @return string
 */
function TTgetPluginClassName( $class_name ) {
	global $config_vars;

	//Check if the plugin system is enabled in the config.
	if ( isset( $config_vars['other']['enable_plugins'] ) && $config_vars['other']['enable_plugins'] == 1 ) {
		//Classes must be alpha numeric, otherwise exit out early if someone is trying to pass in crazy class names as a potential attack.
		//  This also must ensure that no class name contains '../../' so an attacker can't attempt to access files outside the plugin directory.
		//  Some TimeClock classes have underscores in the name, so we need to use the actual PHP regex for class names as stated here: https://www.php.net/manual/en/language.oop5.basic.php
		if ( preg_match( '/^[a-zA-Z_\x80-\xff][a-zA-Z0-9_\x80-\xff]*$/', $class_name ) !== 1 ) { //Was using: ctype_alnum( $class_name ) == false -- But that doesn't allow underscores and is less than 10% faster than a complex regex.
			Debug::Text( 'WARNING: Class name contains invalid characters: ' . $class_name, __FILE__, __LINE__, __METHOD__, 1 );
			return false;
		}

		$plugin_class_name = $class_name . 'Plugin';

		//This improves performance greatly for classes with no plugins.
		//But it may cause problems if the original class was somehow loaded before the plugin.
		//However the plugin wouldn't apply to it anyways in that case.
		//
		//Due to a bug that would cause the plugin to not be properly loaded if both the Factory and ListFactory were loaded in the same script
		//we need to always reload the plugin class if the current class relates to it.
		$is_class_exists = class_exists( $class_name, false );
		if ( $is_class_exists == false || ( $is_class_exists == true && stripos( $plugin_class_name, $class_name ) !== false ) ) {
			if ( class_exists( $plugin_class_name, false ) == false ) {
				//Class file needs to be loaded.
				$plugin_directory = Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'plugins';
				$plugin_class_file_name = $plugin_directory . DIRECTORY_SEPARATOR . $class_name . '.plugin.php';
				//Debug::Text('Plugin System enabled! Looking for class: '. $class_name .' in file: '. $plugin_class_file_name, __FILE__, __LINE__, __METHOD__,10);
				if ( file_exists( $plugin_class_file_name ) ) {
					@include_once( $plugin_class_file_name );
					$class_name = $plugin_class_name;
					Debug::Text( 'Found Plugin: ' . $plugin_class_file_name . ' Class: ' . $class_name, __FILE__, __LINE__, __METHOD__, 10 );
				}
			} else {
				//Class file is already loaded.
				$class_name = $plugin_class_name;
			}
		}
		//else {
		//Debug::Text('Plugin not found...', __FILE__, __LINE__, __METHOD__, 10);
		//}
	}
	//else {
	//Debug::Text('Plugins disabled...', __FILE__, __LINE__, __METHOD__, 10);
	//}

	return $class_name;
}

/**
 * Instantiates a new object using plugins if they exist.
 * @template Object
 * @param class-string<Object> $class_name
 * @return Object
 */
function TTnew( $class_name, ...$params ) { //Unlimited arguments are supported.
	$class_name = TTgetPluginClassName( $class_name );

	return new $class_name( ...$params );
}

//Force no caching of file.
function forceNoCacheHeaders() {
	//CSP headers break many things at this stage, unless "unsafe" is used for almost everything.
	header( 'Content-Security-Policy: frame-ancestors \'self\'; default-src * \'unsafe-inline\'; script-src \'unsafe-eval\' \'unsafe-inline\' \'self\' http://localhost:5173 *.timetrex.com *.google-analytics.com *.googletagmanager.com *.doubleclick.net *.googleapis.com *.gstatic.com *.google.com *.clarity.ms; img-src \'self\' map.timetrex.com:3128 *.mapbox.com *.timetrex.com *.google-analytics.com *.googletagmanager.com *.doubleclick.net *.googleapis.com *.gstatic.com *.google.com *.clarity.ms data: blob:' );

	//Help prevent XSS or frame clickjacking.
	header( 'X-XSS-Protection: 1; mode=block' );
	header( 'X-Frame-Options: SAMEORIGIN' );

	//Reduce MIME-TYPE security risks.
	header( 'X-Content-Type-Options: nosniff' );

	//Turn caching off.
	header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
	header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s' ) . ' GMT' );
	header( 'Cache-Control: no-store' );
	header( 'Vary: *' ); //Required for Safari to not cache APIGlobal.js.php during a refresh (after logged in). Without this, a refresh would redirect the user to the login screen even if they were logged in.

	//Only when force_ssl is enabled and the user is using SSL, include the STS header.
	global $config_vars;
	if ( isset( $config_vars['other']['force_ssl'] ) && ( $config_vars['other']['force_ssl'] == true ) && Misc::isSSL( true ) == true ) {
		header( 'Strict-Transport-Security: max-age=31536000; includeSubdomains' );
	}
}

/**
 * Function to force browsers to cache certain files.
 * @param string $file_name
 * @param int $mtime
 * @param string $etag
 * @return bool
 */
function forceCacheHeaders( $file_name = null, $mtime = null, $etag = null ) {
	if ( $file_name == '' ) {
		$file_name = $_SERVER['SCRIPT_FILENAME'];
	}

	if ( $mtime == '' ) {
		$file_modified_time = filemtime( $file_name );
	} else {
		$file_modified_time = $mtime;
	}

	if ( $etag != '' ) {
		$etag = trim( $etag );
	}

	//Help prevent XSS or frame clickjacking.
	header( 'X-XSS-Protection: 1; mode=block' );
	header( 'X-Frame-Options: SAMEORIGIN' );

	//For some reason even with must-revalidate the browsers won't check ETag every page load.
	//So some pages may get cached for an hour or two regardless of ETag changes.
	header( 'Cache-Control: must-revalidate, max-age=0' );
	header( 'Cache-Control: private', false );
	header( 'Expires: Mon, 26 Jul 1997 05:00:00 GMT' );
	//Check eTag first, then last modified time.
	if ( ( isset( $_SERVER['HTTP_IF_NONE_MATCH'] ) && trim( $_SERVER['HTTP_IF_NONE_MATCH'] ) == $etag )
			|| ( !isset( $_SERVER['HTTP_IF_NONE_MATCH'] )
					&& isset( $_SERVER['HTTP_IF_MODIFIED_SINCE'] )
					&& strtotime( $_SERVER['HTTP_IF_MODIFIED_SINCE'] ) == $file_modified_time ) ) {
		//Cached page, send 304 code and exit.
		header( 'HTTP/1.1 304 Not Modified' );
		//Header('Connection: close'); //This closes keep-alive connections to close, shouldn't be needed and just slows things down.
		ob_clean();
		exit; //File is cached, don't continue.
	} else {
		//Not cached page, add headers to assist caching.
		if ( $etag != '' ) {
			header( 'ETag: ' . $etag );
		}
		header( 'Last-Modified: ' . gmdate( 'D, d M Y H:i:s', $file_modified_time ) . ' GMT' );
	}

	return true;
}

//See Authentication::checkValidCSRFToken() for more comments on how this is checked.
function sendCSRFTokenCookie() {
	$csrf_token = sha1( TTUUID::generateUUID() );

	//NOTE: If the user went to the secure HTTPS version of the domain and set this cookie, then tried to go to the non-secure HTTP version without first clearing cookies,
	//      the browser likely won't allow the non-secure cookie to overwrite the secure version of the cookie. You can see this error message in the developer tools, network tab, HEADERS RESPONSE section.
	setcookie( 'CSRF-Token', $csrf_token . '-' . sha1( $csrf_token . TTPassword::getPasswordSalt() ), ( time() + 157680000 ), Environment::getCookieBaseURL(), '', Misc::isSSL( true ), false ); //Must not be HTTP only, as javascript needs to read this. Really should send the "SameSite=strict" flag, however PHP v7.3 and older handle this in different ways: https://stackoverflow.com/questions/39750906/php-setcookie-samesite-strict

	return true;
}

/* @formatter:off */
define('TT_PRODUCT_COMMUNITY', 10 ); define('TT_PRODUCT_PROFESSIONAL', 15 ); define('TT_PRODUCT_CORPORATE', 20 ); define('TT_PRODUCT_ENTERPRISE', 25 );
function getTTProductEdition() { global $TT_PRODUCT_EDITION; if ( isset($TT_PRODUCT_EDITION) && $TT_PRODUCT_EDITION > 0 ) { return $TT_PRODUCT_EDITION; } elseif ( file_exists( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'expense'. DIRECTORY_SEPARATOR .'UserExpenseFactory.class.php') ) { $TT_PRODUCT_EDITION = TT_PRODUCT_ENTERPRISE; return $TT_PRODUCT_EDITION; } elseif ( file_exists( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'job'. DIRECTORY_SEPARATOR .'JobFactory.class.php') ) { $TT_PRODUCT_EDITION = TT_PRODUCT_CORPORATE; return $TT_PRODUCT_EDITION; } elseif ( file_exists( Environment::getBasePath() .'classes'. DIRECTORY_SEPARATOR .'modules'. DIRECTORY_SEPARATOR .'time_clock'. DIRECTORY_SEPARATOR .'TimeClock.class.php') ) { $TT_PRODUCT_EDITION = TT_PRODUCT_PROFESSIONAL; return $TT_PRODUCT_EDITION; } return TT_PRODUCT_COMMUNITY; }
function getTTProductEditionName() { switch( getTTProductEdition() ) { case 15: $retval = 'Professional'; break; case 20: $retval = 'Corporate'; break; case 25: $retval = 'Enterprise'; break; default: $retval = 'Community'; break; } return $retval; }
/* @formatter:on */

function TTsaveRequestMetrics() {
	global $config_vars;
	if ( function_exists( 'memory_get_usage' ) ) {
		$memory_usage = memory_get_usage();
	} else {
		$memory_usage = 0;
	}
	file_put_contents( $config_vars['other']['request_metrics_log'], ( ( microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] ) * 1000 ) . ' ' . $memory_usage . "\n", FILE_APPEND ); //Write each response in MS to log for tracking performance
}

//This has to be first, always.
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Environment.class.php' );

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Profiler.class.php' );
$profiler = new Profiler( true );

set_include_path(
		'.' . PATH_SEPARATOR .
		Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'core' .
		PATH_SEPARATOR . Environment::getBasePath() . 'classes' .
		PATH_SEPARATOR . Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'plugins' .
		//PATH_SEPARATOR . get_include_path() . //Don't include system include path, as it can cause conflicts with other packages bundled with TimeTrex. However the bundled PEAR.php must check for class_exists('PEAR') to prevent conflicts with PHPUnit.
		PATH_SEPARATOR . Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'pear' .
		PATH_SEPARATOR . Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'pear' . DIRECTORY_SEPARATOR . 'pear' ); //Put PEAR path at the end so system installed PEAR is used first, this prevents require_once() from including PEAR from two directories, which causes a fatal error.

require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Exception.class.php' );
require_once( Environment::getBasePath() . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'core' . DIRECTORY_SEPARATOR . 'Debug.class.php' );
require_once( Environment::getBasePath() . 'vendor' . DIRECTORY_SEPARATOR . 'autoload.php' ); //Composer autoloader.
																																																																																				/* @formatter:off */ /* REMOVING OR CHANGING THIS COPYRIGHT NOTICE IS IN STRICT VIOLATION OF THE LICENSE AND COPYRIGHT AGREEMENT -- Please don't steal from hard working volunteers. */ if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) { define( 'COPYRIGHT_NOTICE', 'Copyright &copy; '. date('Y') .' <a href="https://'. ORGANIZATION_URL .'" class="footerLink">'. ORGANIZATION_NAME .'</a>. The Program is free software provided AS IS, without warranty. Licensed under <a href="http://www.fsf.org/licensing/licenses/agpl-3.0.html" class="footerLink" target="_blank">AGPLv3.</a>' ); } else { define( 'COPYRIGHT_NOTICE', 'Copyright &copy; '. date('Y') .' <a href="https://'. ORGANIZATION_URL .'" class="footerLink">'. ORGANIZATION_NAME .'</a>.' ); } /* @formatter:on */
Debug::setEnable( (bool)( $config_vars['debug']['enable'] ?? false ) );
Debug::setEnableDisplay( (bool)( $config_vars['debug']['enable_display'] ?? false ) );
Debug::setBufferOutput( (bool)( $config_vars['debug']['buffer_output'] ?? false ) );
Debug::setEnableLog( (bool)( $config_vars['debug']['enable_log'] ?? false ) );
Debug::setVerbosity( (int)( $config_vars['debug']['verbosity'] ?? 0 ) );

if ( Debug::getEnable() == true && Debug::getEnableDisplay() == true ) {
	ini_set( 'display_errors', 1 );
	ini_set( 'display_startup_errors', 1 );
} else {
	ini_set( 'display_errors', 0 );
	ini_set( 'display_startup_errors', 0 );
}

if ( function_exists('TTShutdown') == false ) { //Could be created from other include files outside of this project.
	//Function that is called on shutdown from register_shutdown_function().
	function TTShutdown() {
		//Operations to be handled *before* the request response is sent back to the user/browser.
		ignore_user_abort( true );

		if ( function_exists( 'fastcgi_finish_request' ) ) {
			fastcgi_finish_request(); //Send data to user, so they aren't waiting for the below code to finish.
		}

		//Operations to be handled *after* the request response is sent back to the user/browser.
		//  Can queue up longer running operations to be run here if needed, without delaying the user.
		Debug::text( 'Server Response Time: ' . ( microtime( true ) - $_SERVER['REQUEST_TIME_FLOAT'] ), __FILE__, __LINE__, __METHOD__, 10 );

		Debug::showSQLProfile();
		Debug::showCacheProfile();

		global $config_vars;
		if ( PHP_SAPI != 'cli' && isset( $config_vars['other']['request_metrics_log'] ) && $config_vars['other']['request_metrics_log'] != '' ) {
			TTsaveRequestMetrics();
		}

		Debug::Text( 'Shutting down completely...', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Shutdown();

		Debug::writeToLog();
		Debug::Display(); //This only does anything if display is actually turned on.

		return true;
	}
}
//Register PHP error handling functions as early as possible.
register_shutdown_function( 'TTShutdown' );
set_error_handler( [ 'Debug', 'ErrorHandler' ] );


Debug::Text( 'URI: ' . ( isset( $_SERVER['REQUEST_URI'] ) ? $_SERVER['REQUEST_URI'] : 'N/A' ) . ' IP Address: ' . Misc::getRemoteIPAddress(), __FILE__, __LINE__, __METHOD__, 10 );
Debug::Text( 'USER-AGENT: ' . ( isset( $_SERVER['HTTP_USER_AGENT'] ) ? $_SERVER['HTTP_USER_AGENT'] : 'N/A' ) .' Accept-Lang: '. ( isset( $_SERVER['HTTP_ACCEPT_LANGUAGE'] ) ? $_SERVER['HTTP_ACCEPT_LANGUAGE'] : 'N/A' ), __FILE__, __LINE__, __METHOD__, 10 );
Debug::Text( 'Version: ' . APPLICATION_VERSION . ' (PHP: v' . phpversion() . ' ['. PHP_INT_SIZE .']) Edition: ' . getTTProductEdition() . ' Production: ' . (int)PRODUCTION . ' Server: ' . ( isset( $_SERVER['SERVER_ADDR'] ) ? $_SERVER['SERVER_ADDR'] : 'N/A' ) . ' OS: ' . OPERATING_SYSTEM . ' Database: Type: ' . ( isset( $config_vars['database']['type'] ) ? $config_vars['database']['type'] : 'N/A' ) . ' Name: ' . ( isset( $config_vars['database']['database_name'] ) ? $config_vars['database']['database_name'] : 'N/A' ) . ' Config: ' . CONFIG_FILE . ' Demo Mode: ' . (int)DEMO_MODE, __FILE__, __LINE__, __METHOD__, 10 );

if ( function_exists( 'bcscale' ) ) {
	bcscale( 10 );
}

if ( PHP_SAPI != 'cli' ) {
	//Make sure we are using SSL if required.
	if ( ( isset( $config_vars['other']['force_ssl'] ) && $config_vars['other']['force_ssl'] == true ) && Misc::isSSL( true ) == false && isset( $_SERVER['HTTP_HOST'] ) && isset( $_SERVER['REQUEST_URI'] ) && !isset( $disable_https ) && php_sapi_name() != 'cli' ) {
		//Prevent HTML tags from being included in the URL prior to redirecting to the HTTPS version.
		//This helps avoid potential XSS attacks, even though its simply a redirect and gets handled properly afterwards. ie: http://demo.timetrex.com/?<script>test</script>
		Redirect::Page( 'https://' . $_SERVER['HTTP_HOST'] . strip_tags( html_entity_decode( $_SERVER['REQUEST_URI'] ) ) );
		exit;
	}

	if ( PRODUCTION == true ) {
		$origin_url = ( Misc::isSSL( true ) == true ) ? 'https://' . Misc::getHostName( false ) : 'http://' . Misc::getHostName( false );
	} else {
		$origin_url = '*';
	}
	header( 'Access-Control-Allow-Origin: ' . $origin_url );
	header( 'Access-Control-Allow-Headers: Content-Type, REQUEST_URI_FRAGMENT' );
	unset( $origin_url );
}

require_once( 'Database.inc.php' );
require_once( 'Cache.inc.php' ); //Put cache after Database so we can handle our own DB caching.
?>
