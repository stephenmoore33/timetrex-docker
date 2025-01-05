<?php
/*********************************************************************************
 *
 * TimeTrex is a Workforce Management program developed by
 * TimeTrex Software Inc. Copyright (C) 2003 - 2025 TimeTrex Software Inc.
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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'classes' . DIRECTORY_SEPARATOR . 'modules' . DIRECTORY_SEPARATOR . 'api' . DIRECTORY_SEPARATOR . 'client' . DIRECTORY_SEPARATOR . 'TimeTrexClientAPI.class.php' );

//Examples:
//  To add a background event to a notification, include a JSON payload and an array of events under the timetrex key.
//  Below are a few example payloads to trigger background events.

//  **Send notification that launches In/Out view with pre-populated data for a break and out punch. No notification is saved.**
//  Payload:   {"timetrex":{"event":[{"type":"open_view","action":"edit","view_name":"InOut","data":{"type_id":30,"status_id":20}}]}}
//  Type:      open_view
//  View_name: InOut
//  Action:    edit // edit, add, view
//  Data:      {"type_id":30,"status_id":20}
//  Full Command: send_push_notification.php -api_key <key> -user_id <UUID> -type_id system -device_id 4 -payload '{"timetrex":{"event":[{"type":"open_view_immediate","action":"edit","view_name":"InOut","data":{"type_id":30,"status_id":20}}]}}'

//  **Send notification that opens MyAccount -> Request, Add. No notification is saved.**
//  Payload:   {"timetrex":{"event":[{"type":"open_view","action":"edit","view_name":"Contact Information","data":{}}]}}
//  Type:      open_view
//  View_name: Request
//  Action:    add // edit, add, view
//  Data:      {}
//  Full Command: send_push_notification.php -api_key <key> -user_id <UUID> -type_id system -device_id 4 -payload '{"timetrex":{"event":[{"type":"open_view","action":"add","view_name":"Request","data":{"type_id":30}}]}}'

//  **Send notification that opens MyAccount -> Contact Information. No notification is saved.**
//  Payload:   {"timetrex":{"event":[{"type":"open_view","action":"edit","view_name":"Contact Information","data":{}}]}}
//  Type:      open_view
//  View_name: Contact Information
//  Action:    edit // edit, add, view
//  Data:      {}
//  Full Command: send_push_notification.php -api_key <key> -user_id <UUID> -type_id system -device_id 4 -payload '{"timetrex":{"event":[{"type":"open_view","action":"edit","view_name":"Contact Information","data":{}}]}}'

//  **Send notification that redirects to a URL**
//  Payload:   {"timetrex":{"event":[{"ask":1,"text":"Sending you to google.","link":"https://www.google.com/"}]}}
//  Ask:       1 // 0=dont ask, 1=ask (If user receives a prompt)
//  Text:      Sending you to google. //Any text if user is getting an ask prompt.
//  Link:      https://www.google.com/
//  Full Command: send_push_notification.php -api_key <key> -user_id <UUID> -type_id system -device_id 4 -payload '{"timetrex":{"event":[{"type":"redirect","ask":1,"text":"Sending you to google.","link":"https://www.google.com/"}]}}'

//  **Send a system notification with a title/body for "System going down for maintenance at 4PM".**
//  No payload required.
//  Full Command: send_push_notification.php -api_key <key> -user_id <UUID> -type_id system -device_id 4 -title_short 'System Maintenance' -body_short 'System going down for maintenance at 4PM'

//  send_push_notification.php -api_key <key> -user_id <UUID> -type_id system -device_id 4 -payload ""
if ( $argc < 2 || in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) {
	$help_output = "Usage: send_push_notification.php [OPTIONS]\n";
	$help_output .= "\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -server <URL>				URL to API server\n";
	$help_output .= "    -username <username>		API username\n";
	$help_output .= "    -password <password>		API password\n";
	$help_output .= "    -api_key <key>				API key to use instead of username/password\n";
	$help_output .= "    -user_id <user_id>		    Destination Employee\n";
	$help_output .= "    -type_id <type_id>		    Type (ie: system)\n";
	$help_output .= "    -object_type_id <object_type_id>		Object Type ID (ie: 0=System)\n";
	$help_output .= "    -object_id <object_id>		Object ID\n";
	$help_output .= "    -title_short <title>		Title (Short)\n";
	$help_output .= "    -title_long <title>		Title (Long)\n";
	$help_output .= "    -body_short <body>		    Body (Short)\n";
	$help_output .= "    -body_long <body>		    Body (Long)\n";
	$help_output .= "    -priority <priority>		Priority (ie: 1=Critical, 2=High, 5=Normal, 10=Low)\n";
	$help_output .= "    -device_id <device_id>		Devices (ie: 4=Web Browser, 256=Work Email, 512=Home Email, 32768=App)\n";
	$help_output .= "    -payload <payload>         (Optional) Raw Payload in JSON format\n";

	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count( $argv ) - 1;

	if ( in_array( '-server', $argv ) ) {
		$api_url = trim( $argv[( array_search( '-server', $argv ) + 1 )] );
	} else {
		$api_url = false;
	}

	if ( in_array( '-username', $argv ) ) {
		$username = trim( $argv[( array_search( '-username', $argv ) + 1 )] );
	} else {
		$username = false;
	}

	if ( in_array( '-password', $argv ) ) {
		$password = trim( $argv[( array_search( '-password', $argv ) + 1 )] );
	} else {
		$password = false;
	}

	if ( in_array( '-api_key', $argv ) ) {
		$api_key = trim( $argv[array_search( '-api_key', $argv ) + 1] );
	} else {
		$api_key = false;
	}

	if ( in_array( '-user_id', $argv ) ) {
		$user_id = trim( $argv[array_search( '-user_id', $argv ) + 1] );
	} else {
		$user_id = false;
	}

	if ( in_array( '-type_id', $argv ) ) {
		$type_id = trim( $argv[array_search( '-type_id', $argv ) + 1] );
	} else {
		$type_id = 'system';
	}

	if ( in_array( '-object_type_id', $argv ) ) {
		$object_type_id = trim( $argv[array_search( '-object_type_id', $argv ) + 1] );
	} else {
		$object_type_id = false;
	}

	if ( in_array( '-object_id', $argv ) ) {
		$object_id = trim( $argv[array_search( '-object_id', $argv ) + 1] );
	} else {
		$object_id = false;
	}

	if ( in_array( '-title_short', $argv ) ) {
		$title_short = trim( $argv[array_search( '-title_short', $argv ) + 1] );
	} else {
		$title_short = false;
	}

	if ( in_array( '-title_long', $argv ) ) {
		$title_long = trim( $argv[array_search( '-title_long', $argv ) + 1] );
	} else {
		$title_long = false;
	}

	if ( in_array( '-body_short', $argv ) ) {
		$body_short = trim( $argv[array_search( '-body_short', $argv ) + 1] );
	} else {
		$body_short = false;
	}

	if ( in_array( '-body_long', $argv ) ) {
		$body_long = trim( $argv[array_search( '-body_long', $argv ) + 1] );
	} else {
		$body_long = false;
	}

	if ( in_array( '-priority', $argv ) ) {
		$priority = trim( $argv[array_search( '-priority', $argv ) + 1] );
	} else {
		$priority = 5; //5=Normal
	}

	if ( in_array( '-device_id', $argv ) ) {
		$device_id = [ trim( $argv[array_search( '-device_id', $argv ) + 1] ) ];
		if ( strpos( $device_id[0], ',' ) !== false ) {
			$device_id = explode( ',', $device_id[0] );
		}
	} else {
		$device_id = [ 4 ]; //Default to Web Browser, Work Email, and Mobile App.
	}

	if ( in_array( '-payload', $argv ) ) {
		$raw_payload = json_decode( trim( $argv[array_search( '-payload', $argv ) + 1] ), true );
		$raw_payload_json_error = json_last_error();
		if ( $raw_payload_json_error !== JSON_ERROR_NONE ) {
			echo 'ERROR: Invalid JSON: ';
			switch ( $raw_payload_json_error ) {
				case JSON_ERROR_NONE:
					echo 'No errors';
					break;
				case JSON_ERROR_DEPTH:
					echo 'Maximum stack depth exceeded';
					break;
				case JSON_ERROR_STATE_MISMATCH:
					echo 'Underflow or the modes mismatch';
					break;
				case JSON_ERROR_CTRL_CHAR:
					echo 'Unexpected control character found';
					break;
				case JSON_ERROR_SYNTAX:
					echo 'Syntax error, malformed JSON';
					break;
				case JSON_ERROR_UTF8:
					echo 'Malformed UTF-8 characters, possibly incorrectly encoded';
					break;
				default:
					echo 'Unknown error';
					break;
			}
			echo "\n";
			exit( 254 );
		}
	} else {
		$raw_payload = null;
	}

	$TIMETREX_URL = $api_url;

	if ( isset( $api_key ) && $api_key != '' ) {
		$TIMETREX_SESSION_ID = $api_key;
		$api_session = new TimeTrexClientAPI();
		//if ( $api_session->isLoggedIn() == false ) {
		//	echo "API Key is incorrect!\n";
		//	exit( 1 );
		//}
	} else {
		$api_session = new TimeTrexClientAPI();
		$api_session->Login( $username, $password );
		if ( $TIMETREX_SESSION_ID == false ) {
			echo "API Username/Password is incorrect!\nIf multifactor authentication is enabled, login to TimeTrex and go to Profile -> Security / Passwords, More (...) -> Register API Key.\n";
			exit( 1 );
		}
		//echo "Session ID: $TIMETREX_SESSION_ID\n";
	}

	$notification_data = [
		'user_id' => $user_id,
		'device_id' => $device_id,
		'type_id' => $type_id,
		'object_type_id' => $object_type_id,
		'object_id' => $object_id,
		'title_short' => $title_short,
		'title_long' => $title_long,
		'body_short' => $body_short,
		'body_long' => $body_long,
		'priority_id'  => $priority,
		'payload' => $raw_payload,
	];

	$notification_obj = new TimeTrexClientAPI( 'Notification' );
	$notification_obj->setIdempotentKey( false ); //Turn off idempotenacy.
	$notification_result = $notification_obj->sendNotification( $notification_data );
	$retval = $notification_result->getResult();
	if ( $retval !== true ) {
		echo "ERROR: Unable to send notification!\n";
		exit ( 1 );
	}
}
?>
