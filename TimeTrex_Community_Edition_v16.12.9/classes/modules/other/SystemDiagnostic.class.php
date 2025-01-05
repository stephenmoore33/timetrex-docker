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
 * @package Other\SystemDiagnostic
 */
class SystemDiagnostic {

	private $cli_mode = false;
	private $progress_bar_obj = null;
	private $api_message_id = null;
	private $curl_last_progress = null; //Progress of last time CURL reported.
	private $curl_progress_start = null; //Time CURL transfer started.

	/**
	 * @param $toggle
	 * @return bool
	 */
	static function setSystemDiagnostic( $toggle ) {
		$install_obj = new Install();
		$install_obj->writeConfigFile( [ 'debug' => [ 'enable' => $toggle, 'enable_log' => $toggle, 'verbosity' => 10 ] ] );

		return true;
	}

	/**
	 * @param $c_obj
	 * @param $cli_mode
	 * @return bool
	 */
	function uploadSystemDiagnostic( $c_obj, $cli_mode ) {
		$install_obj = new Install();
		if ( $install_obj->checkDiskSpace() !== 0 ) {
			Debug::Text( 'ERROR: Not enough disk space.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		$this->cli_mode = $cli_mode;

		//Set script execution time to 24 hours. Users may have slow upload speed and large log files.
		set_time_limit( 86400 );

		global $config_vars;
		if ( !isset( $config_vars['cache']['dir'] ) ) { //Just in case the cache directory is not set.
			$config_vars['cache']['dir'] = Environment::getBasePath();
		}
		$temp_dir = $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'system_diagnostics' . DIRECTORY_SEPARATOR;
		//Check if tempdir is writable.
		if ( $install_obj->checkWritableCacheDirectory() !== 0 ) {
			Debug::Text( 'ERROR: Cache directory is not writable.', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//Create lock file so that this function cannot be ran more than once simultaneously.
		$lock_file = new LockFile( $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . 'system_diagnostic.lock' );

		if ( $lock_file->exists() == false ) {
			if ( $lock_file->create() == true ) {
				Debug::text( 'System diagnostic lock file created. Starting process of gathering system logs.', __FILE__, __LINE__, __METHOD__, 10 );

				$this->getProgressBarObject()->start( $this->getAPIMessageID(), 104, null, TTi18n::getText( 'Starting Upload Process...' ) ); //104 = 4 steps plus 100 for upload progress.

				$this->cleanUpTempDir( $temp_dir, false );

				$registration_key = SystemSettingFactory::getSystemSettingValueByKey( 'registration_key' );
				$zip_name = Misc::sanitizeFileName( $c_obj->getName() . '-' . date( 'Ymd-Hmi' ) .'-'. ( ( $registration_key != '' ) ? $registration_key : '0000000000000000000000000000000000000000' ) );
				$zip_file = $temp_dir . $zip_name . '.zip';

				if ( OPERATING_SYSTEM === 'WIN' ) {
					$apache_log_name = Environment::getBasePath() . '..' . DIRECTORY_SEPARATOR . 'apache2'. DIRECTORY_SEPARATOR . 'log' . DIRECTORY_SEPARATOR .'error.log';
				} else {
					$apache_log_name = '/var/log/apache2/error.log';
				}

				if ( OPERATING_SYSTEM === 'WIN' ) {
					$installer_log_name = Environment::getBasePath() . '..' . DIRECTORY_SEPARATOR .'install.log';
				} else {
					$installer_log_name = null;
				}

				if ( OPERATING_SYSTEM === 'WIN' ) {
					$sql_log_name = Environment::getBasePath() . '..' . DIRECTORY_SEPARATOR .'upgrade_sql_error_timetrex.log';
				} else {
					$sql_log_name = null;
				}

				$timetrex_log_name = $config_vars['path']['log'] . DIRECTORY_SEPARATOR . 'timetrex.log';

				//Using file_exists instead of is_dir as file_exists checks for both files and directories incase of name collision.
				if ( !file_exists( $temp_dir ) ) {
					mkdir( $temp_dir );
				}

				if ( $this->cli_mode === true ) {
					echo "Collecting Log Files...\n";
				} else {
					$this->getProgressBarObject()->set( $this->getAPIMessageID(), 1, TTi18n::getText( 'Collecting Log Files...' ) );
				}

				$zip = new ZipArchive;
				if ( $zip->open( $zip_file, ZipArchive::CREATE ) === true ) {
					//Create phpInfo() log.
					ob_start();
					phpinfo();
					$php_info = ob_get_contents();
					ob_end_clean();
					//Remove HTML formatting.
					$php_info = strip_tags( $php_info );
					//Remove CSS styling from top of output.
					$php_info = strstr( $php_info, 'PHP Version' );
					$zip->addFromString( 'log'. DIRECTORY_SEPARATOR . 'php_info.log', $php_info );

					global $db;

					//Create system_info.log
					$system_info = 'Operating System: '. php_uname() . PHP_EOL;
					$system_info .= 'PostgreSQL: ' . Debug::varDump( $db->ServerInfo( true ) ) . PHP_EOL;
					$system_info .= 'Total Disk Space: ' . round( disk_total_space( dirname( __FILE__ ) ) / 1024 / 1024 / 1024 ) . 'gb Free Space: ' . round( disk_free_space( dirname( __FILE__ ) ) / 1024 / 1024 / 1024 ) . 'gb' . PHP_EOL;
					$zip->addFromString( 'log'. DIRECTORY_SEPARATOR . 'system_info.log', $system_info );

					//Zip apache log files.
					if ( file_exists( $apache_log_name ) ) {
						$zip->addFile( $apache_log_name, 'log'. DIRECTORY_SEPARATOR . basename( $apache_log_name ) );
					} else {
						Debug::Text( 'Unable to locate Apache log files. May be caused by permission issues, or it doesn\'t exist: ' . $apache_log_name, __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( file_exists( $installer_log_name ) ) {
						$zip->addFile( $installer_log_name, 'log'. DIRECTORY_SEPARATOR . basename( $installer_log_name ) );
					} else {
						Debug::Text( 'Unable to locate Windows Installer log files. May be caused by permission issues, or it doesn\'t exist: ' . $installer_log_name, __FILE__, __LINE__, __METHOD__, 10 );
					}

					if ( file_exists( $sql_log_name ) ) {
						$zip->addFile( $sql_log_name, 'log'. DIRECTORY_SEPARATOR . basename( $sql_log_name ) );
					} else {
						Debug::Text( 'Unable to locate SQL error log files. May be caused by permission issues, or it doesn\'t exist: ' . $sql_log_name, __FILE__, __LINE__, __METHOD__, 10 );
					}

					//Include all TimeTrex files.
					$root_path = realpath( Environment::getBasePath() );
					$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root_path ), RecursiveIteratorIterator::LEAVES_ONLY );
					foreach ( $files as $name => $file ) {
						// Skip directories (they would be added automatically)
						if ( !$file->isDir() && strpos( $file->getRealPath(), '.git') === false ) {
							// Add current file to archive under the 'timetrex' directory.
							$zip->addFile( $file->getRealPath(), str_replace( '\\', '/', 'timetrex'. DIRECTORY_SEPARATOR . substr( $file->getRealPath(), strlen( $root_path ) + 1 ) ) ); //Replace all backslashes from Windows with forward slashes as recommended in: https://www.php.net/manual/en/ziparchive.addfile.php
						}
					}
					unset( $files, $file_path, $relative_path, $root_path );

					//Zip TimeTrex log files -- Do this last so we can get as many log lines into it as possible.
					//  Include all logs in the log directory recursively so it includes station logs too.
					Debug::Text( '  Writing debug buffer to log prior to uploading...', __FILE__, __LINE__, __METHOD__, 10 );
					Debug::writeToLog(); //Write all previous debug lines to log before uploading it. **Keep this here, even though its in TTShutdown() as well**

					$root_path = realpath( $config_vars['path']['log'] . DIRECTORY_SEPARATOR );
					$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $root_path ), RecursiveIteratorIterator::LEAVES_ONLY );
					foreach ( $files as $name => $file ) {
						// Skip directories (they would be added automatically)
						if ( !$file->isDir() ) {
							$zip->addFile( $file->getRealPath(), str_replace( '\\', '/', 'log'. DIRECTORY_SEPARATOR . substr( $file->getRealPath(), strlen( $root_path ) + 1 ) ) ); //Replace all backslashes from Windows with forward slashes as recommended in: https://www.php.net/manual/en/ziparchive.addfile.php
						}
					}
					unset( $files, $file_path, $relative_path, $root_path );

					if ( $this->cli_mode === true ) {
						echo "Compressing Logs...\n";
					} else {
						$this->getProgressBarObject()->set( $this->getAPIMessageID(), 2, TTi18n::getText( 'Compressing Logs...' ) );
					}

					$retval = $zip->close();

					if ( $retval === true && file_exists( $zip_file ) ) {

						if ( $this->cli_mode === true ) {
							echo "Securely Uploading Logs...\n";
						} else {
							$this->getProgressBarObject()->set( $this->getAPIMessageID(), 3, TTi18n::getText( 'Securely Uploading Logs...' ) );
						}

						$fp = fopen( $zip_file, 'r' );

						$curl_handler = curl_init();
						curl_setopt( $curl_handler, CURLOPT_URL, 'https://nextcloud.timetrex.com/s/6NePJBkdqGeLaDM' );
						curl_setopt( $curl_handler, CURLOPT_SSL_VERIFYPEER, false ); //False to avoid curl error - "unable to get local issuer certificate."
						curl_setopt( $curl_handler, CURLOPT_RETURNTRANSFER, true );
						$curl_retval = curl_exec( $curl_handler );

						$doc = new DOMDocument();
						//LIBXML_NOERROR to ignore HTML errors on the page we are loading.
						$doc->loadHTML( $curl_retval, LIBXML_NOERROR );
						$head = $doc->getElementsByTagName( 'head' );
						if ( isset( $head[0] ) ) {
							$request_token = $head[0]->getAttribute( 'data-requesttoken' );
							$basic_authorization_token = base64_encode( $doc->getElementById( 'sharingToken' )->getAttribute( 'value' ) . ':' );
						}

						curl_setopt( $curl_handler, CURLOPT_URL, 'https://nextcloud.timetrex.com/public.php/webdav/' . $zip_name . '.zip' );
						curl_setopt( $curl_handler, CURLOPT_HTTPHEADER, [
								'requesttoken: ' . $request_token,
								'authorization: Basic ' . $basic_authorization_token,
						] );
						curl_setopt( $curl_handler, CURLOPT_PUT, true );
						curl_setopt( $curl_handler, CURLOPT_INFILESIZE, filesize( $zip_file ) );
						curl_setopt( $curl_handler, CURLOPT_INFILE, $fp );
						curl_setopt( $curl_handler, CURLOPT_NOPROGRESS, false );
						curl_setopt( $curl_handler, CURLOPT_PROGRESSFUNCTION, [ $this, 'updateUploadProgress' ] );
						curl_setopt( $curl_handler, CURLOPT_TIMEOUT, 0 );
						curl_setopt( $curl_handler, CURLINFO_HEADER_OUT, true );

						$this->curl_progress_start = microtime( true );
						$curl_retval = curl_exec( $curl_handler );
						Debug::Text( 'CURL Return Response: ' . Debug::varDump( $curl_retval ), __FILE__, __LINE__, __METHOD__, 10 );

						if ( $this->cli_mode === true ) {
							echo 'Finished Uploading Logs...'."\n";
						} else {
							$this->getProgressBarObject()->set( $this->getAPIMessageID(), 104, TTi18n::getText( 'Finished Uploading Logs...' ) );
						}

						if ( curl_errno( $curl_handler ) ) {
							$error_msg = curl_error( $curl_handler );
							Debug::Text( 'CURL ERROR: ' . $error_msg, __FILE__, __LINE__, __METHOD__, 10 );
						} else {
							Debug::Text( 'CURL Upload success.', __FILE__, __LINE__, __METHOD__, 10 );
						}
						curl_close( $curl_handler );

						$this->cleanUpTempDir( $temp_dir, true );
					}
				} else {
					return false;
				}
			}
			$lock_file->delete();
		} else {
			Debug::text( 'Skipping... uploading system diagnostics. Lock file exists...', __FILE__, __LINE__, __METHOD__, 10 );
			if ( $this->cli_mode === true ) {
				echo "NOTICE: Lock file exists, diagnostics already being collected elsewhere, skipping...\n";
			}

			return false;
		}

		return true;
	}


	/**
	 * @param $temp_dir
	 * @param $remove_temp_dir
	 * @return bool
	 */
	function cleanUpTempDir( $temp_dir, $remove_temp_dir ) {
		Debug::Text( 'Cleaning up Temp Dir: ' . $temp_dir, __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_dir( $temp_dir ) ) {
			if ( $this->cli_mode === true ) {
				echo "Cleaning Up...\n";
			} else {
				$this->getProgressBarObject()->set( $this->getAPIMessageID(), 3, TTi18n::getText( 'Cleaning Up' ) );
			}

			$files = new RecursiveIteratorIterator( new RecursiveDirectoryIterator( $temp_dir, RecursiveDirectoryIterator::SKIP_DOTS ), RecursiveIteratorIterator::CHILD_FIRST );
			foreach ( $files as $fileinfo ) {
				if ( $fileinfo->isDir() === true ) {
					@rmdir( $fileinfo->getRealPath() );
				} else {
					Misc::unlink( $fileinfo->getRealPath() );
				}
			}

			if ( $remove_temp_dir === true ) {
				//Since windows deleting files is async, its likely rare that the directory can be deleted, even after a sleeping for 10 seconds.
				@rmdir( $temp_dir );
			}

			clearstatcache(); //Clear any stat cache when done.
			Debug::Text( 'Done cleaning up Temp Dir: ' . $temp_dir, __FILE__, __LINE__, __METHOD__, 10 );
		}

		return true;
	}

	/**
	 *    Updates API or CLI with upload progress as a percent.
	 */
	function updateUploadProgress( $curl_handler, $download_file_size, $downloaded, $upload_file_size, $uploaded ) {
		//Debug::text( '  Download File Size: '. $download_file_size .' Downloaded: '. $downloaded .' Upload File Size: '. $upload_file_size .' Uploaded: '. $uploaded, __FILE__, __LINE__, __METHOD__, 10 );
		//echo '  Download File Size: '. $download_file_size .' Downloaded: '. $downloaded .' Upload File Size: '. $upload_file_size .' Uploaded: '. $uploaded ."\n";
		if ( $upload_file_size > 0 ) { //Prevent division by 0.
			$elapsed_upload_time = ( microtime( true ) - $this->curl_progress_start );
			if ( $elapsed_upload_time == 0 ) {
				$bytes_per_second = $uploaded;
			} else {
				$bytes_per_second = ( $uploaded / $elapsed_upload_time );
			}
			$kilobytes_per_second = round( $bytes_per_second / 1024, 2 );

			$progress = round( ( $uploaded / $upload_file_size ) * 100 );

			if ( $this->curl_last_progress === null || $progress !== $this->curl_last_progress ) { //Only update progress when it changes.
				if ( $this->cli_mode === true ) {
					if ( $progress % 10 == 0 ) {
						echo 'Securely Uploading Logs... ' . $progress . '% - ' . $kilobytes_per_second . "KB/s\n";
					}
				} else {
					//Add 3 to the progress because there are 3 steps before it.
					$this->getProgressBarObject()->set( $this->getAPIMessageID(), ( floor( $progress ) + 3 ), TTi18n::getText( 'Securely Uploading Logs...' ) . ' ' . $progress . '% - '. $kilobytes_per_second .'KB/s' );
				}
			}

			$this->curl_last_progress = $progress;
		}
	}

	/**
	 * @param object $obj
	 * @return bool
	 */
	function setProgressBarObject( $obj ) {
		if ( is_object( $obj ) ) {
			$this->progress_bar_obj = $obj;

			return true;
		}

		return false;
	}

	/**
	 * @return null|ProgressBar
	 */
	function getProgressBarObject() {
		if ( !is_object( $this->progress_bar_obj ) ) {
			$this->progress_bar_obj = new ProgressBar();
		}

		return $this->progress_bar_obj;
	}

	/**
	 * Returns the API messageID for each individual call.
	 * @return bool|null
	 */
	function getAPIMessageID() {
		if ( $this->api_message_id != null ) {
			return $this->api_message_id;
		}

		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setAPIMessageID( $id ) {
		Debug::Text( 'API Message ID: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
		if ( $id != '' ) {
			$this->api_message_id = $id;

			return true;
		}

		return false;
	}
}

?>
