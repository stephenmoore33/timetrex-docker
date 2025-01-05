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

$disable_database_connection = true;
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

if ( $argc < 1 || ( isset( $argv[1] ) && in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) ) {
	$help_output = "Usage: check_ssl.php -show_certificate\n";
	echo $help_output;
} else {
	if ( in_array( '-show_certificate', $argv ) ) {
		$show_certificate = true;
	} else {
		$show_certificate = false;
	}

	//Force flush after each output line.
	ob_implicit_flush( true );
	ob_end_flush();

	$url = 'https://coreapi.timetrex.com';
	//$url = 'https://expired.badssl.com/';
	//$url = 'https://untrusted-root.badssl.com/';

	// Check if SSL certificate authority bundle works with CURL
	//   **NOTE: This can't be moved to the Install() object and checked during install, because if the certificate becomes invalid, it will never upgrade to a newer version which could fix the problem.
	function test_curl_ssl_certificate( $url ) {
		// Initialize cURL session
		$ch = curl_init( $url );

		// Set cURL options
		curl_setopt( $ch, CURLOPT_RETURNTRANSFER, true );  // Return output as a string
		curl_setopt( $ch, CURLOPT_SSL_VERIFYPEER, true );  // Enable SSL certificate verification
		curl_setopt( $ch, CURLOPT_SSL_VERIFYHOST, 2 );     // Check the existence of a common name and also verify that it matches the hostname provided

		// Execute cURL session and get any errors
		curl_exec( $ch );
		$errorNo = curl_errno( $ch );

		// Close cURL session
		curl_close( $ch );

		// Check if there was an SSL verification error
		if ( $errorNo !== 0 ) {
			echo "  ERROR (CURL): " . curl_error( $ch ) . "\n";

			return false;
		}

		return true; // Valid certificate
	}

	// Check if SSL certificate authority bundle works with PHP
	//   **NOTE: This can't be moved to the Install() object and checked during install, because if the certificate becomes invalid, it will never upgrade to a newer version which could fix the problem.
	function test_php_ssl_certificate( $url, $show_certificate = false ) {
		$contextOptions = [
				'ssl' => [
						'verify_peer'       => true,      // Require verification of SSL certificate
						'verify_peer_name'  => true,      // Require verification of peer name
						'allow_self_signed' => false,     // Disallow self-signed certificates
						'capture_peer_cert' => true,      // Capture the peer's SSL certificate
				],
		];

		$context = stream_context_create( $contextOptions );
		$output = @file_get_contents( $url, false, $context );

		// Extract the certificate details from the context
		$params = stream_context_get_params( $context );
		$certificate = openssl_x509_parse( $params['options']['ssl']['peer_certificate'] );

		if ( $show_certificate == true ) {
			echo "Certificate details: START\n";
			echo "--------------------------------------------------\n";
			print_r( $certificate );
			echo "--------------------------------------------------\n";
			echo "Certificate details: END\n\n";
		}

		if ( $output === false ) {
			echo "  ERROR (PHP): " . error_get_last()['message'] . "\n";

			return false;
		}

		return true;
	}

	if ( test_php_ssl_certificate( $url, $show_certificate ) == true ) {
		echo "SUCCESS: Valid PHP SSL certificate authority bundle.\n";
	} else {
		echo "FAILED: Invalid PHP SSL certificate authority bundle!\n";
		// Get the location of the CA bundle used by the PHP cURL extension
		$curl_cainfo = ini_get( 'curl.cainfo' );
		echo "CA bundle for PHP cURL extension: " . ( $curl_cainfo ? : 'Not set' ) . "\n";
	}
	echo "\n";

	if ( test_curl_ssl_certificate( $url ) == true ) {
		echo "SUCCESS: Valid CURL SSL certificate authority bundle.\n";
	} else {
		echo "FAILED: Invalid CURL SSL certificate authority bundle!\n";
		// Get the location of the CA bundle used by PHP's stream handler
		$openssl_cafile = ini_get( 'openssl.cafile' );
		echo "  CA bundle for PHP's stream handler: " . ( $openssl_cafile ? : 'Not set' ) . "\n";
	}
	echo "\n";

	$ttsc = new TimeTrexSoapClient();
	if ( $ttsc->Ping() == true ) {
		echo "SUCCESS: Communication to TimeTrex license server.\n";
	} else {
		echo "FAILED: Unable to communicate with TimeTrex license server!\n";
	}
}
Debug::WriteToLog();
Debug::Display();
?>
