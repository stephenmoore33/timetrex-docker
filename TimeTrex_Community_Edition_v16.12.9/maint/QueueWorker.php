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

$my_worker_pid = getmypid();
if ( function_exists( 'proc_nice' ) ) {
	proc_nice( 19 ); //Low priority.
}

$config_vars['database']['persistent_connections'] = true; //Force persistent connections so LISTEN/NOTIFY works properly.

require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );
require_once( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'CLI.inc.php' );

//This must go below any includes, as some of them set the max execution time to 1800, and we need to override that.
//We do reset this after each job is run below too.
ini_set( 'max_execution_time', 0 );

function getRequirementData() {
	global $__initial_requirement_data;

	clearstatcache( true, CONFIG_FILE );
	clearstatcache( true, __FILE__ );
	clearstatcache( true, dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' );

	$retarr = [
			'self' => filemtime( __FILE__ ),
			'global' => filemtime( dirname( __FILE__ ) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR . 'includes' . DIRECTORY_SEPARATOR . 'global.inc.php' ),
			'config' => filemtime( CONFIG_FILE ),

			//Can't use these as we need to reload the global.inc.php for them to be updated.
			//'application_version' => APPLICATION_VERSION,
			//'application_version_date' => APPLICATION_VERSION_DATE,
	];

	if ( !isset( $__initial_requirement_data ) ) {
		$__initial_requirement_data = $retarr;
	}

	return $retarr;
}

function isValidIdleRequirements() {
	Debug::Text( 'Checking valid requirements...', __FILE__, __LINE__, __METHOD__, 10 );

	global $config_vars;
	if ( isset( $config_vars['other']['enable_job_queue'] ) && $config_vars['other']['enable_job_queue'] != true ) { //If job queue is disabled, fail out early.
		Debug::Text( '  Job Queue is disabled in .ini file...', __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	//Check that we aren't down for maintenance (.ini file changing could be the same?)

	//Check that memory usage hasn't exceeded maximum limit.
	if ( memory_get_usage( true ) > 100000000 ) {
		Debug::Text( '    Memory usage exceeds maximum!', __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	if ( ( time() - $_SERVER['REQUEST_TIME'] ) > 14400 ) { //Keep this low (4hrs) initially as we are seeing fairly high PGSQL memory usage for these idle connections for some reason. Could be a memory leak in PGSQL v12 or older?
		Debug::Text( '    Process started more than 4hrs ago!', __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	}

	//Check that files haven't changed out from underneath us.
	global $__initial_requirement_data;
	$requirement_data = getRequirementData();
	$requirement_data_diff = array_diff_assoc( $__initial_requirement_data, $requirement_data );
	if ( !empty( $requirement_data_diff ) ) {
		//Debug::Arr( $__initial_requirement_data, 'Initial Requirement Data: ', __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr( $requirement_data, 'Current Requirement Data: ', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Text( '  Files have changed or application was upgraded!', __FILE__, __LINE__, __METHOD__, 10 );
		return false;
	} else {
		Debug::Text( 'Current Requirement Data all matches.', __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr( $__initial_requirement_data, 'Initial Requirement Data: ', __FILE__, __LINE__, __METHOD__, 10 );
		//Debug::Arr( $requirement_data, 'Current Requirement Data: ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	return true;
}

function shutdownQueueWorker( $sjqlf, $bg_lock_file, $jobs_executed, $execution_time, $my_worker_pid, $worker_db_connection, $force = false, $restart_worker = false ) {
	if ( $force == true ) {
		$exit = true;
	} else {
		$exit = false;

		sleep( rand( 0, 5 ) ); //Random sleep so we aren't checking for worker processes at the exact same time.

		$workder_db_connection_status = pg_connection_status( $worker_db_connection );
		if ( $workder_db_connection_status === PGSQL_CONNECTION_OK ) {
			try {
				$total_running_jobs = $sjqlf->getRunning();                       //Total running jobs in the entire queue, across all servers.
				$current_running_processes = $sjqlf->getRunningWorkerProcesses(); //This is running processes on the current server, not total running jobs in the entire queue.
				if ( $current_running_processes > 1 && $current_running_processes >= ( $total_running_jobs + 1 ) ) { //Make sure there is always at least 1 extra queue worker than running jobs, in case they are only long running jobs.
					Debug::text( 'PID: ' . $my_worker_pid . ' Shutdown: Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . $execution_time['idle'] . ' Running Processes: ' . $current_running_processes . ' Total Running Jobs: ' . $total_running_jobs, __FILE__, __LINE__, __METHOD__, 10 );
					$exit = true;
				} else {
					if ( isValidIdleRequirements() == true ) {
						//Check if our lock file is still valid.
						if ( $bg_lock_file->getCurrentPID() == $bg_lock_file->readPIDFile( $bg_lock_file->getFileName() ) ) {
							Debug::text( 'PID: ' . $my_worker_pid . ' Shutdown: Unable due to last process, or running processes... Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . $execution_time['idle'] . ' Running Processes: ' . $current_running_processes . ' Total Running Jobs: ' . $total_running_jobs, __FILE__, __LINE__, __METHOD__, 10 );
						} else {
							Debug::text( 'PID: ' . $my_worker_pid . ' Shutdown: ERROR! Own lock file is stale/invalid... Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . $execution_time['idle'] . ' Running Processes: ' . $current_running_processes . ' Total Running Jobs: ' . $total_running_jobs, __FILE__, __LINE__, __METHOD__, 10 );
							$exit = true;
						}
					} else {
						Debug::text( 'PID: ' . $my_worker_pid . ' Shutdown even though last process due to requirements mismatch... Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . $execution_time['idle'] . ' Running Processes: ' . $current_running_processes . ' Total Running Jobs: ' . $total_running_jobs . ' Restart: ' . (int)$restart_worker, __FILE__, __LINE__, __METHOD__, 10 );
						$exit = true;
					}
				}
			} catch ( Exception $e ) {
				Debug::text( 'PID: ' . $my_worker_pid . ' Shutdown: ERROR! Unable to check for running processes: ' . $e->getMessage() . ' - Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . $execution_time['idle'], __FILE__, __LINE__, __METHOD__, 10 );
				$exit = true;
			}
		} else {
			Debug::text( '  WARNING: Database connection was lost, shutting down...', __FILE__, __LINE__, __METHOD__, 10 );
			$exit = true;
		}
	}

	Debug::writeToLog();
	if ( $exit == true ) {
		//Delete background pool lock file.
		if ( isset( $bg_lock_file ) ) {
			$bg_lock_file->delete();
		}

		//Try to close the DB connection, even though its persistent and might not actually close it.
		@pg_close(  $worker_db_connection );

		if ( $restart_worker == true ) {
			$sjqlf = new SystemJobQueueListFactory();
			$sjqlf->startWorkerProcess( __FILE__ );
		}

		//Disconnect from the database. Especially important to prevent persistent connections from stacking up.
		//  Because pg_close() is called above, this can try to close the connection twice, which could cause PHP fatal errors. Doesn't seem to be an easy way to check for that case either.
		//global $db;
		//if ( is_object( $db ) ) {
		//	$db->Close();
		//}

		Debug::Display();
		exit(0);
	}

	if ( Debug::getPHPErrors() == 0 ) {
		Debug::clearBuffer();
	}

	return true;
}

//First CLI option is the background process lock file name.
if ( isset( $argv[1] ) && $argv[1] != '' ) {
	$background_process_lock_file_name = $argv[1];
	Debug::text( 'Spawned queue worker using Background Lock File Name: ' . $background_process_lock_file_name, __FILE__, __LINE__, __METHOD__, 10 );

	//Create lock file for background pooling.
	$bg_lock_file = new LockFile( $background_process_lock_file_name );
	if ( $bg_lock_file->create() == false ) {
		Debug::text( 'ERROR: Unable to create lock file, exiting...', __FILE__, __LINE__, __METHOD__, 10 );
		exit(0);
	}

	//This must go after lock file created, so we can clear the lock file
	if ( ( isset( $config_vars['other']['installer_enabled'] ) && $config_vars['other']['installer_enabled'] == 1 ) || ( isset( $config_vars['other']['down_for_maintenance'] ) && $config_vars['other']['down_for_maintenance'] == 1 ) ) {
		Debug::text( 'Installer Enable or Down for Maintenance, not processing job queue...', __FILE__, __LINE__, __METHOD__, 10 );
		if ( isset( $bg_lock_file ) ) {
			$bg_lock_file->delete();
		}
		//Debug::writeToLog(); //Handled in TTShutdown now.
		//Debug::Display(); //Handled in TTShutdown now.
		exit(0);
	}

	getRequirementData(); //Save initial requirement data so we can check against to later to see if anything changed.

	$idle_sleep = 20000; //microseconds, 100000 = 0.1 second.
	$idle_timeout_force_check = 30; //Seconds to force pending job check without notify being received. **THIS IS CRITICAL FOR REAL-TIME EXCEPTIONS OR FUTURE DATED JOBS**
	$idle_timeout_shutdown = 60; //Seconds to shutdown a queue worker process.
	$max_executed_jobs = 1000;

	//Randomize some of the timeouts so processes can't get "synced" together, and all start/stop at the same time, or always check for new jobs at the same time.
	$idle_sleep += rand( ( $idle_sleep * -0.20 ), ( $idle_sleep * 0.20 ) );
	$idle_timeout_force_check += rand( ( $idle_timeout_force_check * -0.20 ), ( $idle_timeout_force_check * 0.40 ) );
	$idle_timeout_shutdown += rand( ( $idle_timeout_shutdown * -0.20 ), ( $idle_timeout_shutdown * 0.20 ) );
	$max_executed_jobs += rand( ( $max_executed_jobs * -0.20 ), ( $max_executed_jobs * 0.20 ) );
	$max_executed_jobs = ( $max_executed_jobs < 1 ) ? 1 : $max_executed_jobs; //Make sure we never go below 1 as the max jobs to execute.
	$system_load_throttle_threshold_multiplier = 0.75; //This is a multiplier on the max system load. (ie: 90% of max load)
	$system_load_throttle_sleep_max = 750000; //Max amount of time to sleep when system load reaches the max threshold, in micro seconds. (ie: 500000 = 0.5 seconds)

	//Check if we are using load balancing or not.
	if ( strpos( $config_vars['database']['host'], ',' ) !== false ) {
		$worker_db_connection = $db->getConnection( 'write' )->_connectionID;
	} else {
		$worker_db_connection = $db->_connectionID;
	}

	$jobs_executed = 0;
	$last_received_notification = null;
	$force_job_check = true; //On startup always force a check for a new job.
	$idle_timeout_force_epoch = null;
	$execution_time = [ 'active' => 0, 'idle' => 0 ];

	Debug::text( '  Listening for Pending Jobs...', __FILE__, __LINE__, __METHOD__, 10 );
	$db->Execute( 'SET SESSION idle_in_transaction_session_timeout = 300000'); //5min in milliseconds -- If connections happen to start leaking, try to prevent them from stacking up.
	$db->Execute( 'LISTEN "system_job_queue"');

	Debug::writeToLog();
	if ( Debug::getPHPErrors() == 0 ) {
		Debug::clearBuffer();
	}

	$sjqlf = new SystemJobQueueListFactory();
	$max_allowed_processes = $sjqlf->getMaxProcesses();

	$i = 0;
	while( 1 ) {
		$loop_start_microtime = microtime( true );
		$_SERVER['REQUEST_TIME_FLOAT'] = microtime( true ); //This restarts the DEBUG time on each loop as if its a separate request. This helps prevent long request WARNING from being triggered like in reports.

		if ( $force_job_check !== true ) {
			$notify = pg_get_notify( $worker_db_connection );
			if ( isset( $notify['payload'] ) && $notify['payload'] != '' ) {
				Debug::Text( ' PG Notify Payload: '. $notify['payload'], __FILE__, __LINE__, __METHOD__, 10 );
				if ( strtolower( $notify['payload'] ) == 'exit' ) {
					Debug::text( '  WARNING: Notify command to exit received, shutting down...', __FILE__, __LINE__, __METHOD__, 10 );
					shutdownQueueWorker( $sjqlf, $bg_lock_file, $jobs_executed, $execution_time, $my_worker_pid, $worker_db_connection, true ); //Force immediate shutdown
				}
			}
		} else {
			$notify = false;
		}

		if ( ( $force_job_check == true || $notify !== false ) ) {
			//If only a max of 1 process is allowed, it must disable max executed jobs,
			// since if we exit the last process we can't restart it right away because the lock file isn't deleted until we exit, and the have to start a new process before we exit. (chicken and egg problem)
			if ( $jobs_executed <= $max_executed_jobs || $max_allowed_processes == 1 ) {

				$max_system_load = Misc::getMaxSystemLoad();
				$current_system_load = Misc::getSystemLoad();
				$system_load_throttle_threshold = ( $max_system_load * $system_load_throttle_threshold_multiplier );
				if ( ( $current_system_load <= $max_system_load ) ) {
					$force_job_check = false;

					$last_received_notification = $loop_start_microtime;
					Debug::text( 'PID: ' . $my_worker_pid . ' Forced Job Check... Jobs Executed: ' . $jobs_executed . ' Force: ' . (int)$force_job_check . ' Idle Time: ' . $execution_time['idle'] .' Lock File: '. $background_process_lock_file_name, __FILE__, __LINE__, __METHOD__, 10 );

					//Check to make sure the database connection is still operational before getting next pending job.
					$workder_db_connection_status = pg_connection_status( $worker_db_connection );
					if ( $workder_db_connection_status !== PGSQL_CONNECTION_OK ) {
						Debug::text( '  WARNING: Database connection was lost, shutting down...', __FILE__, __LINE__, __METHOD__, 10 );
						shutdownQueueWorker( $sjqlf, $bg_lock_file, $jobs_executed, $execution_time, $my_worker_pid, $worker_db_connection, true ); //Force immediate shutdown
					}

					$sjqlf->getPendingAndLock( ( time() + 1 ) ); //Single SQL query is about 2-3x faster than separate queries for selecting and locking.
					if ( $sjqlf->getRecordCount() > 0 ) {
						foreach ( $sjqlf as $sjq_obj ) { /** @var SystemJobQueueFactory $sjq_obj */
							Debug::text( '  Found Pending Jobs: ' . $sjqlf->getRecordCount() .' Notify: '. ( ( $notify !== false ) ? 'Y' : 'N' ) .' Force: '. (int)$force_job_check .' Load: '. $current_system_load, __FILE__, __LINE__, __METHOD__, 10 );

							//Whenever we are busy working on one job, make sure there is always another process ready to handle other ones, up to the max process limit of course.
							//  This must happen after we lock the row, otherwise the child worker that spawns will just take the job instead.
							//  Only spawn new worker processes when the load is less than half the max though, as that will just increase the load and likely slow everything down with multiple processes just waiting on the load coming down.
							// Since every time we try to start a new worker process, we have to check/count lock files, only start new processes when we are notified of new jobs,
							//    or when we run the first job after spawning.
							if ( ( $notify !== false || $force_job_check == true || $jobs_executed == 0 ) && $current_system_load <= $system_load_throttle_threshold ) {
								$sjqlf->startWorkerProcess( __FILE__ );
							}

							$sjq_obj->Run();

							$jobs_executed++;

							ini_set( 'max_execution_time', 0 ); //Reset max execution time after each job, as some jobs might set a specific max execution time themselves, and we need to reset it as soon as its done.
						}

						$execution_time['active'] += microtime( true ) - $loop_start_microtime;
						$force_job_check = true; //Make sure we check for more jobs on the immediate next loop.
					} else {
						Debug::text( '  Pending Jobs: ' . $sjqlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );

						//Received notification, but all jobs have all been processed. Clear out pg_get_notify() queue in case there is a backlog.
						$clear_queue = null;
						$cleared_backlogged_queue = -1;
						while ( $clear_queue !== false ) {
							$clear_queue = pg_get_notify( $worker_db_connection );
							$cleared_backlogged_queue++;
						}

						if ( $cleared_backlogged_queue > 0 ) {
							Debug::Text( '    Cleared ' . $cleared_backlogged_queue . ' backlogged notifications...', __FILE__, __LINE__, __METHOD__, 10 );
							$force_job_check = true; //Force one last check after notify backlog is cleared.
						}

						$execution_time['idle'] += microtime( true ) - $loop_start_microtime;
					}

					//Check if we are approaching max system load and throttle.
					if ( $current_system_load >= $system_load_throttle_threshold ) {
						$throttle_sleep_timeout = (int)TTMath::reScaleRange( $current_system_load, $system_load_throttle_threshold, $max_system_load, 0, $system_load_throttle_sleep_max );
						$throttle_sleep_timeout += (int)rand( $throttle_sleep_timeout * -0.10, $throttle_sleep_timeout * 0.10 );
						Debug::Text( '    Load Throttle Sleep Timeout (microseconds): ' . $throttle_sleep_timeout .' Current Load: '. $current_system_load .' Threshold: '. $system_load_throttle_threshold, __FILE__, __LINE__, __METHOD__, 10 );
						usleep( $throttle_sleep_timeout );
					}
				} else { //Max Load
					shutdownQueueWorker( $sjqlf, $bg_lock_file, $jobs_executed, $execution_time, $my_worker_pid, $worker_db_connection );
					sleep( rand( 30, 90 ) ); //Make sure we are distributing the job checks.
					$execution_time['idle'] += microtime( true ) - $loop_start_microtime;
				}
			} else { //Max Executed Jobs
				Debug::text( 'PID: ' . $my_worker_pid . ' Maximum jobs executed, shutdown... Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . $execution_time['idle'] .' Lock File: '. $background_process_lock_file_name, __FILE__, __LINE__, __METHOD__, 10 );
				shutdownQueueWorker( $sjqlf, $bg_lock_file, $jobs_executed, $execution_time, $my_worker_pid, $worker_db_connection, false, true );
				$force_job_check = true; //Force job check next as that will also cause another job to spawn.

				//Reduce by 10, so if for some reason we didn't exit (ie: not enough running workers), we will execute at least 10 more jobs before getting back to here.
				//  Otherwise we could get stuck in a loop where every iteration we are at the max executed jobs and we constantly call shutdownQueueWorker() which has a random sleep, and might not exit on the next iteration anyways.
				$jobs_executed -= 10;
			}

			Debug::writeToLog();
			if ( Debug::getPHPErrors() == 0 ) {
				Debug::clearBuffer();
			}
		} else {
			usleep($idle_sleep); //Can't see any CPU used when monitoring 'top' at these settings.
			$execution_time['idle'] += microtime( true ) - $loop_start_microtime;
		}

		//If a worker is started and on its first loop jobs_executed=0, assume there is nothing to do and attempt to shutdown immediately if there are more than 1 processes running.
		// Its likely we were started from CRON every minute in that case, so no point in keeping processes running that aren't needed.
		if ( $execution_time['idle'] > $idle_timeout_shutdown || ( $i == 0 && $jobs_executed == 0 ) ) {
			Debug::text( 'PID: ' . $my_worker_pid . ' Idle... Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . $execution_time['idle'] .' I: '. $i .' Lock File: '. $background_process_lock_file_name, __FILE__, __LINE__, __METHOD__, 10 );
			shutdownQueueWorker( $sjqlf, $bg_lock_file, $jobs_executed, $execution_time, $my_worker_pid, $worker_db_connection );
			$execution_time['idle'] = 0;
		} else {
			//Since this loop is triggered multiple time per second, we need to jump through hoops to prevent the below from being triggered 10x in a row in the same second that idle first becomes active.
			if ( ( $idle_timeout_force_epoch == null || floor( microtime( true ) - $idle_timeout_force_epoch ) > 1 ) && (int)$execution_time['idle'] > 1 && ( (int)$execution_time['idle'] % $idle_timeout_force_check ) == 0 ) {
				Debug::text( 'PID: ' . $my_worker_pid . ' Idle wait... Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . (int)$execution_time['idle'] .' Mod: '. ( (int)$execution_time['idle'] % $idle_timeout_force_check ) .' I: '. $i .' Lock File: '. $background_process_lock_file_name, __FILE__, __LINE__, __METHOD__, 10 );

				if ( isValidIdleRequirements() == false ) {
					Debug::text( 'PID: ' . $my_worker_pid . ' Idle wait invalid requirements... Jobs Executed: ' . $jobs_executed . ' Idle Time: ' . $execution_time['idle'], __FILE__, __LINE__, __METHOD__, 10 );
					shutdownQueueWorker( $sjqlf, $bg_lock_file, $jobs_executed, $execution_time, $my_worker_pid, $worker_db_connection );
				}

				$force_job_check = true;
				$idle_timeout_force_epoch = $loop_start_microtime;
			}
		}

		$i++;
	}
} else {
	$current_system_load = Misc::getSystemLoad();
	if ( $current_system_load < Misc::getMaxSystemLoad() ) {
		$sjqlf = new SystemJobQueueListFactory();
		//When being called from cron, only launch new processes if we are not at the maximum number of workers already.
		//   This is needed so if there is only one worker and its running a long task (ie: backup/DB purge) and all other workers stopped due to idle,
		//   we need to launch another worker to process any new pending jobs that are created without having to wait for the long task to finish.
		// Also check PIDs to ensure processes are running too incase any crashed.
		$sjqlf->startWorkerProcess( __FILE__ );

		//if ( $sjqlf->getRunningWorkerProcesses( true ) < 1 ) {
		//	Debug::text( 'PID: ' . $my_worker_pid . ' Started from cron, spawning first worker process...', __FILE__, __LINE__, __METHOD__, 10 );
		//	$sjqlf->startWorkerProcess( __FILE__ );
		//}
	} else {
		Debug::text( ' WARNING: System load too high, not spawning new queue worker process... Current Load: '. $current_system_load, __FILE__, __LINE__, __METHOD__, 10 );
	}
}
?>