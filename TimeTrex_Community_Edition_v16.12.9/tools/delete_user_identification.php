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

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

if ( $argc < 2 || in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) {
	$help_output = "Usage: delete_user_identification.php [OPTIONS] [user_name]\n";
	$help_output .= "  Options:\n";
	$help_output .= "    -n [Dryrun, don't actually delete any data]\n";
	$help_output .= "    -t [Type ID, or use 'ALL' to delete all records.]\n";
	echo $help_output;
} else {
	//Handle command line arguments
	if ( in_array( '-n', $argv ) ) {
		$data['dryrun'] = true;
	} else {
		$data['dryrun'] = false;
	}

	if ( in_array( '-t', $argv ) ) {
		$data['t'] = trim( $argv[array_search( '-t', $argv ) + 1] );
	} else {
		$data['t'] = false;
	}

	if ( $data['t'] == false ) {
		echo "Type not specified, use 'ALL' to delete all identification records.\n";
		exit( 1 );
	}

	$last_arg = count( $argv ) - 1;

	if ( isset( $argv[$last_arg] ) && $argv[$last_arg] != '' ) {
		$user_name = $argv[$last_arg];

		//Get user_id from user_name
		$ulf = new UserListFactory();
		$ulf->getByUserName( $user_name );
		if ( $ulf->getRecordCount() == 1 ) {
			$u_obj = $ulf->getCurrent();

			echo "Found user " . $u_obj->getFullName() . ", attempting to delete identification information...\n";
			ob_flush();

			$uilf = new UserIdentificationListFactory();
			$uilf->StartTransaction();
			if ( strtolower( $data['t'] ) == 'all' ) {
				$uilf->getByUserId( $u_obj->getID() );
			} else {
				$uilf->getByUserIdAndTypeId( $u_obj->getID(), (int)$data['t'] );
			}

			if ( $uilf->getRecordCount() > 0 ) {
				foreach ( $uilf as $ui_obj ) {
					if ( $ui_obj->getType() != 5 ) { //Skip password history records.
						$ui_obj->setDeleted( true );
						if ( $ui_obj->isValid() ) {
							echo "  Deleting Identification Record (" . $ui_obj->getID() . ") of type '" . Option::getByKey( $ui_obj->getType(), $ui_obj->getOptions( 'type' ) ) . "' (" . $ui_obj->getType() . ") from " . $u_obj->getFullName() . "\n";
							$ui_obj->Save();
						}
					}
				}
			} else {
				echo "ERROR: No identification records to delete!\n";
			}

			if ( $data['dryrun'] == true ) {
				echo "NOTICE: Dry-run enabled, not committing changed to database!\n";
				$uilf->FailTransaction();
			}
			$uilf->CommitTransaction();
		} else if ( $ulf->getRecordCount() > 2 ) {
			echo "Found more then one user with the same user name, not deleting any data!\n";
		} else {
			echo "User name not found!\n";
		}
	}
}

echo "WARNING: You may need to wait for devices to synchronize before changes take effect...\n";
//Debug::Display();
Debug::writeToLog();
?>
