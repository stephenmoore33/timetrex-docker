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
class BackgroundProcess {
	var $max_processes = 1;
	var $max_process_check_sleep = 2;
	var $max_process_check_timeout = 600; //Max time to wait to run the next process
	var $process_number_digits = 6;

	var $lock_file_dir = '/tmp/';
	var $lock_file_prefix = 'background_process';
	var $lock_file_postfix = '.lock';
	var $max_lock_file_age = 86400;

	/**
	 * BackgroundProcess constructor.
	 */
	function __construct() {
		return true;
	}

	/**
	 * @return string
	 */
	function getLockFilePrefix() {
		return $this->lock_file_prefix;
	}

	/**
	 * @param $prefix
	 * @return bool
	 */
	function setLockFilePrefix( $prefix ) {
		if ( $prefix != '' ) {
			$this->lock_file_prefix = $prefix;

			return true;
		}

		return false;
	}

	/**
	 * @return string
	 */
	function getLockFileDirectory() {
		return $this->lock_file_dir;
	}

	/**
	 * @param $dir
	 * @return bool
	 */
	function setLockFileDirectory( $dir ) {
		if ( $dir != ''  ) {
			if ( file_exists( $dir ) == false ) {
				@mkdir( $dir, 0775, true );
			}

			if ( is_writable( $dir ) ) {
				$this->lock_file_dir = $dir;
			}

			return true;
		}

		return false;
	}

	/**
	 * @return int
	 */
	function getMaxProcesses() {
		return $this->max_processes;
	}

	/**
	 * @param $int
	 * @return bool
	 */
	function setMaxProcesses( $int ) {
		$int = (int)$int;

		if ( $int <= 0 ) {
			$int = 1;
		}
		$this->max_processes = $int;

		return true;
	}

	/**
	 * @param $lock_files
	 * @return int
	 */
	function getCurrentProcesses( $lock_files ) {
		if ( is_array( $lock_files ) ) {
			$retval = count( $lock_files );
		} else {
			$retval = 0;
		}

		//Debug::Text(' Current Running Processes: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param bool $include_dir
	 * @return string
	 */
	function getBaseLockFileName( $include_dir = false ) {
		if ( $include_dir == true ) {
			$retval = $this->getLockFileDirectory() . DIRECTORY_SEPARATOR;
		} else {
			$retval = '';
		}

		$retval .= $this->getLockFilePrefix() . $this->lock_file_postfix;

		//Debug::Text(' Base Lock File Name: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param $lock_files
	 * @return string
	 */
	function getNextLockFileName( $lock_files ) {
		//Lock file name example: <prefix>.lock.<process_number>
		//  ie: timeclocksync.lock.00001
		$next_process_number = 1;
		if ( is_array( $lock_files ) ) {
			sort( $lock_files ); //Process them all in order, so we can quickly determine if there are any gaps in the numbers.

			$file_counter = 1;
			foreach ( $lock_files as $lock_file ) {
				if ( preg_match( '/' . $this->getLockFilePrefix() . '\.lock\.([0-9]{1,' . $this->process_number_digits . '})/i', $lock_file, $matches ) ) {
					if ( isset( $matches[0] ) && isset( $matches[1] ) && $matches[1] != '' ) {
						if ( (int)$matches[1] > $file_counter ) { //Found gap in the list, return the current file counter.
							$next_process_number = $file_counter;
							break;
						} else {
							//No gap in the list yet, use the file_counter + 1.
							$next_process_number = ( $file_counter + 1 );
						}
					}

					$file_counter++;
				}
			}
		}

		//Pad process number to proper digits
		$next_process_number = str_pad( $next_process_number, $this->process_number_digits, '0', STR_PAD_LEFT );

		$retval = $this->getBaseLockFileName( true ) . '.' . $next_process_number;

		Debug::Text(' Next Lock File Name: '. $retval .' Total Lock Files: '. count( (array)$lock_files ), __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * Delete any lock files older then max age, incase they are stale.
	 * @param $lock_files
	 * @return bool
	 */
	function purgeLockFiles( $lock_files, $check_pids = false ) {
		clearstatcache();

		if ( is_array( $lock_files ) && count( $lock_files ) > 0 ) {
			$current_epoch = time();

			$found_own_pid = false;
			foreach ( $lock_files as $key => $lock_file ) {
				if ( $check_pids == true ) {
					$lf = new LockFile( $lock_file );
					$pid = $lf->readPIDFile( $lock_file );
					if ( $lf->getCurrentPID() == $pid ) {
						Debug::Text( ' Not purging own lock file: ' . $lock_file .' PID: '. $pid, __FILE__, __LINE__, __METHOD__, 10 );
						$found_own_pid = true;
					} else if ( $lf->isPIDRunning( $pid ) == false ) {
						Debug::Text( ' Purging stale lock file: ' . $lock_file .' PID: '. $pid, __FILE__, __LINE__, __METHOD__, 10 );
						Misc::unlink( $lock_file );
						unset( $lock_files[$key] ); //Remove from current lock file list.
					} else {
						if ( ( $pid == '~STARTING' && ( $current_epoch - @filemtime( $lock_file ) ) > 300 ) ) { //If lock file is in "STARTING" state for more than 5 minutes, consider it stale.
							Debug::Text( ' Purging stale lock file in STARTING state: ' . $lock_file .' PID: '. $pid, __FILE__, __LINE__, __METHOD__, 10 );
							Misc::unlink( $lock_file );
							unset( $lock_files[$key] ); //Remove from current lock file list.
						} else {
							Debug::Text( ' Not purging active lock file: ' . $lock_file . ' PID: ' . $pid, __FILE__, __LINE__, __METHOD__, 10 );
						}
					}
				} else if ( file_exists( $lock_file ) && ( $current_epoch - @filemtime( $lock_file ) ) > $this->max_lock_file_age && @is_writable( $lock_file ) ) {
					Debug::Text( ' Purging stale lock file based on age: ' . $lock_file, __FILE__, __LINE__, __METHOD__, 10 );
					Misc::unlink( $lock_file );
					unset( $lock_files[$key] ); //Remove from current lock file list.
				}
			}

			if ( count( $lock_files ) > 0 && $check_pids == true && $found_own_pid == false ) {
				Debug::Text( '  NOTICE! Unable to find our own PID in lock files! ', __FILE__, __LINE__, __METHOD__, 10 );
			}
		}

		return $lock_files;
	}

	/**
	 * @return array|bool
	 */
	function getLockFiles( $purge_lock_files = true ) {
		$retarr = [];

		$start_dir = $this->getLockFileDirectory();

		//Use glob() instead of Misc::getFileList() with a regex, because if there many files in the directory this is substantially faster.
		foreach ( glob( $start_dir . DIRECTORY_SEPARATOR . '*.lock.*') as $file_name ) {
			$retarr[] = $file_name;
		}

		//$regex_filter = $this->getLockFilePrefix() . '\.lock\..*';
		//$retarr = Misc::getFileList( $start_dir, $regex_filter, false );

		//Debug::Arr($retarr, ' Existing Lock Files: ', __FILE__, __LINE__, __METHOD__, 10);
		if ( $purge_lock_files == true ) {
			$retarr = $this->purgeLockFiles( $retarr, true );
		}

		return $retarr;
	}

	/**
	 * @param $cmd
	 * @return bool
	 */
	function BackgroundExec( $cmd ) {
		if ( PHP_OS == 'WINNT' ) {
			//Windows
			global $config_vars;
			if ( strpos( $config_vars['path']['php_cli'], ' ' ) === false ) {
				//No space found in command, can run in background.

				//Unfortunately start.exe won't run a command with quotes around it, so we can't reliably run in the background without some extra
				//helper scripts, as TimeTrex could be installed in a directory which contains a space.
				//Remove quotes from command as "start.exe" fails to run if they exist.
				$full_command = str_replace( '"', '', 'start /B ' . $cmd );
				Debug::Text( ' Executing Command in Background: ' . $full_command, __FILE__, __LINE__, __METHOD__, 10 );

				// Sometimes Windows complains of an invalid argument, but we haven't been able to replicate it. So silence PHP warnings for now.
				$fh = @popen( $full_command, 'r' );
				if ( is_resource( $fh ) ) {
					pclose( $fh );
				} else {
					Debug::Text( '   NOTICE: Executing Command in Background failed or did not return a resource.', __FILE__, __LINE__, __METHOD__, 10 );
				}
				unset( $fh );
			} else {
				Debug::Text( ' Executing Command in Foreground: ' . $cmd, __FILE__, __LINE__, __METHOD__, 10 );
				exec( $cmd );
			}
		} else {
			//Linux/Unix
			//exec($cmd . ' 2>&1> /dev/null &');
			exec( $cmd . ' > /dev/null &' );
		}

		return true;
	}

	/**
	 * @param $cmd
	 * @param $next_lock_file_name
	 * @return mixed
	 */
	function ReplaceCommandVariables( $cmd, $next_lock_file_name ) {
		$search_array = [
				'#lock_file#',
		];
		$replace_array = [
				$next_lock_file_name,
		];
		$retval = str_replace( $search_array, $replace_array, $cmd );

		//Debug::Text(' Before: '. $cmd, __FILE__, __LINE__, __METHOD__, 10);
		//Debug::Text(' After: '. $retval, __FILE__, __LINE__, __METHOD__, 10);

		return $retval;
	}

	/**
	 * @param $cmd
	 * @return bool
	 */
	function run( $cmd, $skip_on_max_processes = false ) {
		//Check to see how many lock files with the prefix exist already.

		$timeout_start = time();
		while ( ( time() - $timeout_start ) <= $this->max_process_check_timeout ) {
			//Count the processes (lock files) outside of a global lock first for performance reasons. Only if we are less than the total processes do we create the global lock and check again.
			$lock_files = $this->getLockFiles();
			$current_processes = $this->getCurrentProcesses( $lock_files );
			//Debug::Text(' Attempting to run command... Current Processes: '. $current_processes, __FILE__, __LINE__, __METHOD__, 10);
			if ( $current_processes < $this->getMaxProcesses() ) {
				//Debug::Text( ' Less than max processes running, checking again with locking... Current: '. $current_processes .' Max: '. $this->getMaxProcesses(), __FILE__, __LINE__, __METHOD__, 10 );

				$global_lock_file = new LockFile( $this->getBaseLockFileName( true ).'global' );
				if ( $global_lock_file->exists() == false ) {
					if ( $global_lock_file->create( false ) == true ) { //This should also be called as soon as the background process starts so its written with the proper PID.
						//Getting current number of processes needs to be wrapped in its own lock file, otherwise duplicates can be created.
						$lock_files = $this->getLockFiles();
						$current_processes = $this->getCurrentProcesses( $lock_files );
						//Debug::Text(' Attempting to run command... Current Processes: '. $current_current_processes, __FILE__, __LINE__, __METHOD__, 10);
						if ( $current_processes < $this->getMaxProcesses() ) {
							$next_lock_file_name = $this->getNextLockFileName( $lock_files );
							$cmd = $this->ReplaceCommandVariables( $cmd, $next_lock_file_name );
							Debug::Text( ' Running Command: ' . $cmd . ' Next Lock File Name: ' . $next_lock_file_name .' Processes: Current: '. $current_processes .' Max: '. $this->getMaxProcesses(), __FILE__, __LINE__, __METHOD__, 10 );

							//Initialize the lock file before executing the command, so its there instantly and avoids race conditions allowing more processes to exist than the limit is.
							$lock_file = new LockFile( $next_lock_file_name );
							$lock_file->create( true ); //Initialize only as we don't know the PID of the background process just yet, that will be written to the file once the process starts itself.
							unset( $lock_file );

							//Run command
							$this->BackgroundExec( $cmd );

							$global_lock_file->delete(); //Once the child process is spawned, then we can delete the global lock file.

							//Rather than sleep immediately waiting for the lock file to be created, keep checking for it immediately for the first 0.25 seconds so we can find it as soon as possible.
							//  This greatly speeds up short running processes.
							$file_exists_start_time = microtime( true );
							$i = 0;
							while ( true ) {
								if ( file_exists( $next_lock_file_name ) == true ) {
									Debug::Text( ' Lock file was created in: ' . ( microtime( true ) - $file_exists_start_time ) . ' returning...', __FILE__, __LINE__, __METHOD__, 10 );
									break;
								} else {
									$elapsed_file_exists_time = ( microtime( true ) - $file_exists_start_time );
									if ( $elapsed_file_exists_time > 2 ) {
										Debug::Text( 'I: ' . $i . ' Lock file not created within 2 seconds... File Name: ' . $next_lock_file_name, __FILE__, __LINE__, __METHOD__, 10 );
										break;
									} else if ( $elapsed_file_exists_time > 0.25 ) {
										Debug::Text( 'I: ' . $i . ' Waiting for lock file to be created... File Name: ' . $next_lock_file_name, __FILE__, __LINE__, __METHOD__, 10 );
										usleep( 250000 ); //.25 seconds
									}
									//else {
									//	usleep( 10 ); //This prevents the main process from spending all its CPU cycles checking file_exists() sucking up about 80% CPU on its own process.
									//}

									$i++;
								}
							}

							return true;
						} else {
							$global_lock_file->delete();
							if ( $skip_on_max_processes == true ) {
								Debug::Text( ' Max processes reached (b), not running command... Running: '. $current_processes, __FILE__, __LINE__, __METHOD__, 10 );
								return false;
							} else {
								Debug::Text( ' Too many processes (b) already running (' . $current_processes . '), sleeping for: ' . $this->max_process_check_sleep . ' before next check...', __FILE__, __LINE__, __METHOD__, 10 );
								sleep( $this->max_process_check_sleep );
							}
						}
					} else {
						//Debug::Text( '   Waiting for process counter to finish (a)...', __FILE__, __LINE__, __METHOD__, 10 );
						usleep( 250000 ); //.25 seconds
					}
				} else {
					//Debug::Text( '   Waiting for process counter to finish (b)...', __FILE__, __LINE__, __METHOD__, 10 );
					usleep( 250000 ); //.25 seconds
				}
			} else {
				if ( $skip_on_max_processes == true ) {
					Debug::Text( ' Max processes reached (a), not running command... Running: '. $current_processes, __FILE__, __LINE__, __METHOD__, 10 );
					return false;
				} else {
					Debug::Text( ' Too many processes (a) already running (' . $current_processes . '), sleeping for: ' . $this->max_process_check_sleep . ' before next check...', __FILE__, __LINE__, __METHOD__, 10 );
					sleep( $this->max_process_check_sleep );
				}
			}
		}

		Debug::Text( ' Timeout waiting for spot in process pool to open up.', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * Waits for all background processes to finish.
	 * @param int $timeout seconds.
	 * @return bool
	 */
	function wait( $timeout = 300 ) {
		$timeout_start = time();
		while ( ( time() - $timeout_start ) <= $timeout ) {
			$current_processes = $this->getCurrentProcesses( $this->getLockFiles() );
			if ( $current_processes == 0 ) {
				Debug::Text( '   All background processes are completed!', __FILE__, __LINE__, __METHOD__, 10 );
				return true;
			} else {
				Debug::Text( '   Waiting for: '. $current_processes .' background processes to be completed!', __FILE__, __LINE__, __METHOD__, 10 );
				usleep( 25000 ); //0.25 seconds
			}
		}

		return false;
	}
}

?>