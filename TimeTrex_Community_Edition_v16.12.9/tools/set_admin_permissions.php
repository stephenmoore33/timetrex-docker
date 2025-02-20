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
	$help_output = "Usage: set_admin_permissions.php [user_name]\n";
	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = count( $argv ) - 1;

	if ( isset( $argv[$last_arg] ) && $argv[$last_arg] != '' ) {
		$user_name = $argv[$last_arg];
		//Get user_id from user_name
		$ulf = new UserListFactory();
		$ulf->getByUserName( $user_name );
		if ( $ulf->getRecordCount() == 1 ) {
			echo "Found user, applying administrator permissions and re-activating...\n";
			ob_flush();

			$u_obj = $ulf->getCurrent();

			//Set employee record as active, disable MFA and enable login.
			$u_obj->setStatus( 10 ); //Active
			$u_obj->setEnableLogin( true );
			$u_obj->setLoginExpireDate( '' ); //Clear out expiration date
			$u_obj->getMultiFactorType( 0 ); //Disable MFA
			if ( $u_obj->isValid() == true ) {
				$u_obj->Save( false ); //Keep user object around for later use.
			} else {
				echo "WARNING: Unable to re-activate employee record!\n";
			}

			//Create new Permission Group just for this purpose.
			$pf = new PermissionFactory();
			$pf->StartTransaction();

			//Apply all preset flags, including 0 => "system"
			$preset_flags = array_merge( [ 0 ], array_keys( $pf->getOptions( 'preset_flags' ) ) );

			$pcf = new PermissionControlFactory();
			$pcf->setCompany( $u_obj->getCompany() );
			$pcf->setLevel( 100 );
			$pcf->setName( 'Administrator Fix (' . rand( 1, 1000 ) . ')' );
			$pcf->setDescription( 'Created by set_admin_permissions.php' );
			if ( $pcf->isValid() ) {
				$pcf_id = $pcf->Save( false );

				$pcf->setUser( [ $u_obj->getId() ] );

				$pcf->Save();

				if ( $pf->applyPreset( $pcf_id, 40, $preset_flags ) == true ) {
					echo "Success!\n";
				}
			}
			//$pf->FailTransaction();
			$pf->CommitTransaction();
		} else if ( $ulf->getRecordCount() > 2 ) {
			echo "Found more then one user with the same user name, not updating permissions!\n";
		} else {
			echo "User name not found!\n";
		}
	}
}

//Debug::Display();
Debug::writeToLog();
?>
