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


/**
 * @package Core
 */
class LockFile {
	var $file_name = null;

	var $max_lock_file_age = 86400;
	var $use_pid = true;

	/**
	 * LockFile constructor.
	 * @param $file_name
	 */
	function __construct( $file_name ) {
		$this->file_name = $file_name;

		return true;
	}

	/**
	 * @return null
	 */
	function getFileName() {
		return $this->file_name;
	}

	/**
	 * @param $file_name
	 * @return bool
	 */
	function setFileName( $file_name ) {
		if ( $file_name != '' ) {
			$this->file_name = $file_name;

			return true;
		}

		return false;
	}

	/**
	 * @return bool|int
	 */
	function getCurrentPID() {
		if ( $this->use_pid == true && function_exists( 'getmypid' ) == true ) {
			$retval = getmypid();
			//Debug::Text( 'Current PID: ' . $retval, __FILE__, __LINE__, __METHOD__, 10 );

			return $retval;
		}

		return false;
	}

	/**
	 * @param int|string $pid Process ID
	 * @return bool|null
	 */
	function isPIDRunning( $pid ) {
		if ( $pid == '~STARTING' ) { //Used in create()
			Debug::Text( 'PID is ~STARTING, assume its running: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );
			return true;
		} else if ( $this->use_pid == true && (int)$pid > 0 && function_exists( 'posix_getpgid' ) == true ) {
			if ( posix_getpgid( $pid ) === false ) {
				Debug::Text( '  PID: '. $pid .' is NOT running!', __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			} else {
				Debug::Text( '  PID: '. $pid .' IS running!', __FILE__, __LINE__, __METHOD__, 10 );
				return true;
			}
		} else {
			if ( trim( $pid ) == '' || (int)$pid == 0 ) {
				Debug::Text( 'PID is blank or 0, assume its NOT running: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			} else {
				if ( OPERATING_SYSTEM == 'WIN' ) {
					//Debug::Text( 'Checking if PID is running on Windows: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );

					//Sometimes Windows can return: shell_exec(): Unable to execute 'tasklist.exe /FI "PID eq 13564" /FO CSV'
					//  Not sure why, but silence the warning for now.
					$processes = array_map( 'str_getcsv', explode( "\n", @shell_exec( 'tasklist.exe /FI "PID eq ' . $pid . '" /FO CSV' ) ) ); //Filter tasklist to return just the PID we are looking for in CSV format.
					array_shift( $processes );                                                                                               //Strip the first (header) off the array.
					if ( is_array( $processes ) ) {
						foreach ( $processes as $process ) {
							if ( isset( $process[1] ) && (int)$process[1] == (int)$pid ) { //PID
								Debug::Text( '  PID IS running: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );

								return true;
							}
						}

						Debug::Text( '  PID is NOT running: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );

						return false;
					} else {
						Debug::Text( 'Unable to get process list...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( '  ERROR: Unable to determine if PID is running... PID: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}
		}

		return null; //Assuming the process is still running if the file exists and PID is invalid.
	}

	/**
	 * @return bool|int
	 */
	function create( $initialize = false ) {
		//Attempt to create directory if it does not already exist.
		$file_name = $this->getFileName();

		$dir = dirname( $file_name );
		if ( file_exists( $dir ) == false ) {
			$mkdir_result = @mkdir( $dir, 0777, true ); //ugo+rwx
			if ( $mkdir_result == false ) {
				Debug::Text( 'ERROR: Unable to create lock file directory: ' . $dir, __FILE__, __LINE__, __METHOD__, 10 );
			} else {
				Debug::Text( 'WARNING: Created lock file directory as it didnt exist: ' . $dir, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		$current_pid = $this->getCurrentPID();

		//Write current PID to file, so we can check if its still running later on.
		$lock_file_pid = $this->readPIDFile( $file_name );
		if ( ( $initialize == true && $lock_file_pid === false ) || ( $initialize == false && ( $lock_file_pid == '~STARTING' || $lock_file_pid === false ) ) ) {
			//Write file with locking, this prevents duplicate lock files with the same name from being created.
			$fp = @fopen( $file_name, 'wb');
			if ( $fp ) {
				Debug::Text( ' Creating Lock File: ' . $file_name .' Initialize: '. (int)$initialize .' Existing Lock File PID: '. $lock_file_pid .' Current PID: '. $current_pid, __FILE__, __LINE__, __METHOD__, 10 );
				@flock( $fp, LOCK_EX );
				@chmod( $file_name, 0660 ); //ug+rw
				@fwrite( $fp, ( ( $initialize == true ) ? '~STARTING' : $current_pid ) ); // ~STARTING is used in isPIDRunning()
				@flock( $fp, LOCK_UN );
				@fclose( $fp );

				return true;
			} else {
				Debug::Text( ' ERROR: Unable to create Lock File: ' . $file_name .' Initialize: '. (int)$initialize, __FILE__, __LINE__, __METHOD__, 10 );
			}
		} else {
			Debug::Text( ' ERROR: Unable to create Lock File: ' . $file_name .' already exists with PID: '. $lock_file_pid .'... Initialize: '. (int)$initialize, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function delete( $check_own_pid = true ) {
		$current_pid = $this->getCurrentPID();
		if ( file_exists( $this->getFileName() ) ) {
			if ( $check_own_pid == true ) {
				$lock_file_pid = $this->readPIDFile( $this->getFileName() );
				if ( is_numeric( $lock_file_pid ) ) {
					if ( $current_pid != $lock_file_pid ) {
						Debug::Text( 'ERROR: Lock file is NOT our own, unable to delete... Lock File: ' . $this->getFileName() .' PID: '. $lock_file_pid .' Current PID: '. $current_pid, __FILE__, __LINE__, __METHOD__, 10 );
						return false;
					}
				} else {
					Debug::Text( 'Lock file does not exist or is starting... Lock File: ' . $this->getFileName() .' PID: '. $lock_file_pid .' Current PID: '. $current_pid, __FILE__, __LINE__, __METHOD__, 10 );
				}
			}

			Debug::Text( ' Deleting Lock File: ' . $this->getFileName() .' PID: '. $current_pid, __FILE__, __LINE__, __METHOD__, 10 );
			return Misc::unlink( $this->getFileName() );
		} else {
			Debug::text( ' WARNING: Failed to delete lock file, does not exist: ' . $this->getFileName() .' PID: '. $current_pid, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @param $file_name
	 * @return false|int
	 */
	function readPIDFile( $file_name ) {
		clearstatcache( true, $file_name );
		if ( file_exists( $file_name ) ) {
			$lock_file_pid = @file_get_contents( $file_name );
			if ( $lock_file_pid != '' ) {
				if ( $lock_file_pid != '~STARTING' ) {
					$lock_file_pid = (int)$lock_file_pid;
				}
				Debug::text( ' Lock file exists with PID: ' . $lock_file_pid . ' Lock File: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );

				return $lock_file_pid;
			} else {
				Debug::text( ' Lock file exists (or did) but does not contain a PID: ' . $lock_file_pid . ' Lock File: ' . $file_name, __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return false;
	}

	/**
	 * @return bool|null
	 */
	function exists() {
		//Ignore lock files older than max_lock_file_age, so if the server crashes or is rebooted during an operation, it will start again the next day.
		clearstatcache();

		$lock_file_pid = $this->readPIDFile( $this->getFileName() );
		if ( $lock_file_pid !== false ) {
			//Check to see if PID is still running or not.
			$pid_running = $this->isPIDRunning( $lock_file_pid );
			if ( $pid_running !== null ) {
				//PID result is reliable, use it.
				if ( $pid_running === false ) {
					Debug::text( ' Stale (PID not running) lock file exists with PID: ' . $lock_file_pid .' Removing Lock File: '. $this->getFileName(), __FILE__, __LINE__, __METHOD__, 10 );
					Misc::unlink( $this->getFileName() );
				} else if ( ( $pid_running == '~STARTING' && ( time() - @filemtime( $this->getFileName() ) ) > 300 ) ) { //If lock file is in "STARTING" state for more than 5 minutes, consider it stale.
					Debug::text( ' Stale lock file exists in STARTING mode... PID: ' . $lock_file_pid .' Removing Lock File: '. $this->getFileName(), __FILE__, __LINE__, __METHOD__, 10 );
					Misc::unlink( $this->getFileName() );
				}

				return $pid_running;
			} else if ( ( time() - @filemtime( $this->getFileName() ) ) > $this->max_lock_file_age ) {
				//PID result may not be reliable, fall back to using file time instead.
				return true;
			}
		}

		return false;
	}
}

?>
