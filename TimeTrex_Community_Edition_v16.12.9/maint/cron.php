<?php /** @noinspection PhpUndefinedVariableInspection */
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
 * Cron replica
 * Run this script every minute from the real cron.
 *
 */
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

if ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == true ) {
	Debug::text( 'CRON: Installer is enabled, skipping cron jobs for now...', __FILE__, __LINE__, __METHOD__, 0 );
} else if ( isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] == true ) {
	Debug::text( 'CRON: System is down for maintenance, skipping cron jobs for now...', __FILE__, __LINE__, __METHOD__, 0 );
} else {
	if ( isset( $config_vars['path']['php_cli'] ) && $config_vars['path']['php_cli'] != '' ) {
		//$current_epoch = strtotime('28-Mar-08 1:30 PM');
		$current_epoch = TTDate::getTime();

		$executed_jobs = 0;

		if ( strpos( $config_vars['database']['host'], ',' ) !== false ) {
			$db = $db->getConnection( 'write' ); //Force connection to master/write database, so status_id can't get out-of-sync and cause duplicate jobs to run at the same time.
		}

		$cjlf = new CronJobListFactory();
		$job_arr = $cjlf->getArrayByListFactory( $cjlf->getAll() );
		if ( is_array( $job_arr ) ) {
			$total_jobs = count( $job_arr );

			foreach ( $job_arr as $job_id => $job_name ) {
				//Get each cronjob row again individually incase the status has changed, since we wait for execution to occur in some cases and these can be relatively long running.
				$cjlf = new CronJobListFactory();
				$cjlf->getById( $job_id ); //Let Execute determine if job is running or not so it can find orphans. -- This should also *not* be cached in case the same job is run multiple times due to a invalid cached status_id.
				if ( $cjlf->getRecordCount() > 0 ) {
					foreach ( $cjlf as $cjf_obj ) {
						//Debug::text('Checking if Job ID: '. $job_id .' is scheduled to run...', __FILE__, __LINE__, __METHOD__, 0);
						if ( $cjf_obj->isScheduledToRun( $current_epoch ) == true ) {
							$executed_jobs++;
							$cjf_obj->Execute( $config_vars['path']['php_cli'], dirname( __FILE__ ) );
						}
					}
				}
			}
		} else {
			$total_jobs = 0;
		}

		//Make sure a QueueWorker is launched each time this is executed.
		//  **EVEN IF JOB QUEUE IS DISABLED, AS THERE COULD BE LATENT JOBS STILL IN THE QUEUE THAT NEED TO BE PROCESSED IF THE JOB QUEUE WAS DISABLED**
		//  Regardless if there are any jobs executed, since the QueueWorker might be used for real-time jobs trigger by the UI.
		$bp = new BackgroundProcess();
		$command = '"' . $config_vars['path']['php_cli'] . '" "'. dirname( __FILE__ ) . DIRECTORY_SEPARATOR .'QueueWorker.php"'; //Make sure we use full path, otherwise the file might not be found on Windows especially.
		$bp->BackgroundExec( Misc::getEnvironmentVariableConfigFile() . $command );  //Skips all max process checks and just launch the queue worker immediately in the background.
		unset( $bp, $command );

		echo "NOTE: Jobs are scheduled to run at specific times each day, therefore it is normal for only some jobs to be queued each time this file is run.\n";
		echo "Jobs Queued For Running: $executed_jobs of $total_jobs\n";
		Debug::text( 'CRON: Jobs Queued For Running: ' . $executed_jobs . ' of ' . $total_jobs, __FILE__, __LINE__, __METHOD__, 0 );
	} else {
		echo "ERROR: timetrex.ini.php does not define 'php_cli' option in the [path] section. Unable to run maintenance jobs!\n";
		Debug::text( 'PHP_CLI not defined in timetrex.ini.php file.', __FILE__, __LINE__, __METHOD__, 0 );
	}

	//Save file to log directory with the last executed date, so we know if the CRON daemon is actually calling us.
	$file_name = $config_vars['path']['log'] . DIRECTORY_SEPARATOR . 'timetrex_cron_last_executed.log';
	@file_put_contents( $file_name, TTDate::getDate( 'DATE+TIME', time() ) . "\n" );
}
?>