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
$skip_db_error_exception = true; //Skips DB error redirect
try {
	require_once( '../includes/global.inc.php' );
} catch ( Exception $e ) {
	echo 'FAIL (GENERAL FAILURE) - ' . $e->getMessage();
	exit;
}
//Debug::setVerbosity(11);

//First check if we are installing or down for maintenance, so we don't try to initiate any DB connections.
if ( ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == true )
		|| ( isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] == true ) ) {
	echo 'FAIL! (INSTALLER/DOWN FOR MAINTENANCE)';
	exit;
}

//Check license is valid. If not employees can't punch!
try {
	$obj_class = "\124\124\114\x69\x63\x65\x6e\x73\x65"; $license = @new $obj_class;
	$is_valid_license = $license->validateLicense();
	if ( $is_valid_license !== true ) {
		echo 'FAIL! (LICENSE INVALID)';
		exit;
	}
	unset( $license, $is_valid_license );
} catch ( Exception $e ) {
	echo 'FAIL! (LICENSE INVALID)';
	exit;
}

//Confirm database connection is up and maintenance jobs have run recently...
if ( PRODUCTION == true ) {
	$cjlf = TTnew( 'CronJobListFactory' ); /** @var CronJobListFactory $cjlf */
	$cjlf->getMostRecentlyRun();
	if ( $cjlf->getRecordCount() > 0 ) {
		$last_run_date_diff = time() - $cjlf->getCurrent()->getLastRunDate();
		if ( $last_run_date_diff > 1800 ) { //Must run in the last 30mins.
			echo 'FAIL! (MAINTENANCE JOBS NOT RUNNING)';
			exit;
		}
	}
	unset( $cjlf, $last_run_date_diff );

	//Check that there are not outstanding SystemJobQueue records.
	$sjqlf = TTNew('SystemJobQueueListFactory');
	$sjqlf->getOldestPendingJob( 3600 ); //Must run in the last 60mins.
	if ( $sjqlf->getRecordCount() > 0 ) {
		echo 'FAIL! (QUEUED JOBS DELAYED)';
		exit;
	}
	unset( $sjqlf, $last_run_date_diff );
}

//If caching is enabled, make sure cache directory exists and is writeable.
if ( isset( $config_vars['cache']['enable'] ) && $config_vars['cache']['enable'] == true ) {
	if ( isset( $config_vars['cache']['redis_host'] ) && $config_vars['cache']['redis_host'] != '' ) {
		$tmp_f = TTnew( 'SystemSettingFactory' ); /** @var SystemSettingFactory $tmp_f */
		$random_value = sha1( time() );
		$tmp_f->saveCache( $random_value, 'system_check' );
		$result = $tmp_f->getCache( 'system_check' );
		if ( $random_value != $result ) {
			echo 'FAIL! (REDIS CACHE)';
			exit;
		}
		$tmp_f->removeCache( 'system_check' );
		unset( $tmp_f, $random_value, $result );
	} else if ( file_exists( $config_vars['cache']['dir'] ) == false ) {
		echo 'FAIL! (CACHE DIR DOES NOT EXIST)';
		exit;
	} else {
		if ( is_writeable( $config_vars['cache']['dir'] ) == false ) {
			echo 'FAIL (CACHE DIR NOT WRITABLE)';
			exit;
		}
	}
}

if ( Misc::isSystemLoadValid() == false ) {
	echo 'FAIL! (LOAD: '. Misc::getSystemLoad() .')';
	exit;
}

//Everything is good.
echo 'OK';
?>