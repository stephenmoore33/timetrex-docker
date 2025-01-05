<?php
/*
 * $License$
 */

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

//Force flush after each output line.
ob_implicit_flush( true );
ob_end_flush();

if ( $argc < 2 || ( isset( $argv[1] ) && in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) ) {
	$help_output = "Usage: send_system_diagnostics.php [OPTIONS]\n";
	$help_output .= "\n";
	$help_output .= "  Options:\n";
	$help_output .= "    --enable		Enable diagnostic mode\n";
	$help_output .= "    --disable		Disable diagnostic mode\n";
	$help_output .= "    --upload		Upload diagnostic information to TimeTrex\n";

	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = ( count( $argv ) - 1 );

	if ( in_array( '--enable', $argv ) ) {
		$sd_obj = TTnew( 'SystemDiagnostic' ); /** @var SystemDiagnostic $sd_obj */
		$sd_obj->setSystemDiagnostic( true );
		echo "Diagnostic mode is now enabled.\n";
	} else if ( in_array( '--disable', $argv ) ) {
		$sd_obj = TTnew( 'SystemDiagnostic' ); /** @var SystemDiagnostic $sd_obj */
		$sd_obj->setSystemDiagnostic( false );
		echo "Diagnostic mode is now disabled.\n";
	} else if ( in_array( '--upload', $argv ) ) {
		if ( isset( $config_vars['other']['primary_company_id'] ) ) {
			$company_id = $config_vars['other']['primary_company_id'];

			$clf = TTnew( 'CompanyListFactory' ); /** @var CompanyListFactory $clf */
			$current_company = $clf->getByID( $company_id )->getCurrent();

			$sd_obj = TTnew( 'SystemDiagnostic' ); /** @var SystemDiagnostic $sd_obj */
			$sd_obj->uploadSystemDiagnostic( $current_company, true );
			echo "Done!\n";
		} else {
			echo "ERROR: Unable to determine primary company!\n";
		}
	} else {
		echo "ERROR: Unknown command!\n";
	}
}

//Debug::Display();
Debug::writeToLog();
?>
