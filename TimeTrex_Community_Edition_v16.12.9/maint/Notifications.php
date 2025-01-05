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

/*
 * Handle sending post dated notifications and notifications that failed to send
 *
 */
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

$ndtlf = TTnew( 'NotificationListFactory' ); /** @var NotificationListFactory $ndtlf */
$ndtlf->getPending();
if ( $ndtlf->getRecordCount() > 0 ) {
	Debug::Text( '  Pending notifications, or ones that need to be resent: '. $ndtlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
	foreach ( $ndtlf as $n_obj ) { /** @var NotificationFactory $n_obj */
		$payload = $n_obj->getPayloadData();

		$attempt_send = false;
		if ( isset( $payload['retries'] ) ) {
			// This is at least the first retry attempt - a post dated notification will not have retry data yet.
			$time_difference = ( TTDate::getTime() - $payload['retries']['last_attempt_date'] );
			if ( $payload['retries']['attempts'] == 1 && $time_difference >= 120 ) { //2 minutes
				$attempt_send = true;
			} else if ( $payload['retries']['attempts'] == 2 && $time_difference >= 900 ) { // 15 minutes
				$attempt_send = true;
			} else if ( $payload['retries']['attempts'] == 3 && $time_difference >= 3600 ) { // 1 hour
				$attempt_send = true;
			} else if ( $payload['retries']['attempts'] == 4 && $time_difference >= 14400 ) { // 4 hours
				$attempt_send = true;
			} else if ( $payload['retries']['attempts'] == 5 && $time_difference >= 28800 ) { // 8 hours
				$attempt_send = true;
			} else if ( $payload['retries']['attempts'] == 6 && $time_difference >= 86400 ) { // 24 hours
				$attempt_send = true;
			} else if ( $payload['retries']['attempts'] >= 7 && $time_difference >= 172800 ) { // 48 hours
				$attempt_send = true;
			}
		} else {
			// Post dated messages have no retry data yet and this is the first send attempt
			$attempt_send = true;
		}

		if ( $attempt_send == true && $n_obj->isValid() ) {
			$n_obj->Save();
		}
	}
} else {
	Debug::Text( '  No pending notifications, or ones that need to be resent...', __FILE__, __LINE__, __METHOD__, 10 );
}
?>