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

if ( $argc < 1 || ( isset( $argv[1] ) && in_array( $argv[1], [ '--help', '-help', '-h', '-?' ] ) ) ) {
	$help_output = "Usage: benchmark_job_queue.php [options]\n";
	$help_output .= "    -type				[NoOp,ReCalculateTimeSheet]\n";
	$help_output .= "    -max_jobs	    	Number of jobs to create\n";
	echo $help_output;
} else {
	//Handle command line arguments
	$last_arg = ( count( $argv ) - 1 );

	if ( in_array( '-type', $argv ) ) {
		$type = trim( $argv[array_search( '-type', $argv ) + 1] );
	}

	if ( in_array( '-max_jobs', $argv ) ) {
		$max_jobs = trim( $argv[array_search( '-max_jobs', $argv ) + 1] );
	} else {
		$max_jobs = 100;
	}

	//Force flush after each output line.
	ob_implicit_flush( true );
	ob_end_flush();


	if ( PRODUCTION == true ) {
		echo "ERROR: Must not be run on a production instance, as data could be altered!\n";
		exit(255);
	}

	//
	//  Create jobs.
	//
	SystemJobQueue::purge();

	echo "Generating Jobs...\n";

	$generate_jobs_start_epoch = microtime( true );

	$sjqf = new SystemJobQueueFactory();
	$sjqf->StartTransaction();
	for( $i = 0; $i < $max_jobs; $i++ ) {
		$sjqf->setStatus( 10 ); //10=Pending
		$sjqf->setName( 'Job' . time() );
		$sjqf->setUser( TTUUID::getZeroID() );
		$sjqf->setEffectiveDate( microtime( true ) );
		$sjqf->setClass( 'Misc' );
		$sjqf->setMethod( 'ArrayAvg' );
		$sjqf->setArguments( [ 0 => [ 1, 2, 3 ] ] ); //Each top level array element is an argument.
		if ( $sjqf->isValid() ) {
			$sjqf->Save();
		}
	}

	$generated_time = ( ( microtime(true) - $generate_jobs_start_epoch ) );
	echo "  Completed in ". round( $generated_time, 4 ) ."s Rate: ". round( ( $max_jobs / $generated_time ), 4 ) ."/s\n";

	//Before commiting transaction, count how many jobs are pending.
	$sjqlf = new SystemJobQueueListFactory();
	$initial_queue_status = $sjqlf->getQueueStatus();
	$sjqf->CommitTransaction();

	//
	// Monitor how many jobs are being processed per second.
	//

	$initial_pending_jobs = $initial_queue_status[10];

	$monitor_start_epoch = microtime( true );

	global $config_vars;
	if ( !isset( $config_vars['other']['max_processes'] ) ) {
		$config_vars['other']['max_processes'] = 2;
	}

	echo "Monitoring Completed Jobs: ". date('r') ." Total Worker Processes: ". $config_vars['other']['max_processes'] ."\n";
	$i = 0;
	while( 1 ) {
		$queue_status_arr = $sjqlf->getQueueStatus();

		$jobs_completed = ( $initial_pending_jobs - $queue_status_arr[10] );
		$elapsed_time_seconds = ( ( microtime( true ) - $monitor_start_epoch ) );
		if ( $jobs_completed > 0 ) {
			$processing_rate = ( $jobs_completed / $elapsed_time_seconds );
		} else {
			$processing_rate = 0;
		}

		echo "  Pending Jobs: ". $queue_status_arr[10] .' Completed: '. $jobs_completed .' Elapsed Time: '. round( $elapsed_time_seconds, 4 ) .'s Rate: '. round( $processing_rate, 4 ) ."/s \n";
		if ( $queue_status_arr[10] == 0 ) {
			echo "All Jobs Completed: ". date('r') ." Total Worker Processes: ". $config_vars['other']['max_processes'] ."\n";
			break;
		}

		if ( $i < 100 ) {
			usleep( 100000 );
		} else {
			sleep( 1 );
		}
		$i++;
	}

}
echo "Done...\n";
Debug::WriteToLog();
//Debug::Display();
?>
