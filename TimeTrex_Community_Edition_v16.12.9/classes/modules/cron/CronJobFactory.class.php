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
 * @package Modules\Cron
 */
class CronJobFactory extends Factory {
	protected $table = 'cron';
	protected $pk_sequence_name = 'cron_id_seq'; //PK Sequence name

	protected $execute_flag = false;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'minute' )->setFunctionMap( 'Minute' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'hour' )->setFunctionMap( 'Hour' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'day_of_month' )->setFunctionMap( 'DayOfMonth' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'month' )->setFunctionMap( 'Month' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'day_of_week' )->setFunctionMap( 'DayOfWeek' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'command' )->setFunctionMap( 'Command' )->setType( 'varchar' )->setIsNull( false ),
							TTSCol::new( 'last_run_date' )->setFunctionMap( 'LastRunDate' )->setType( 'timestamptz' )->setIsNull( true ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No Search Fields.
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			//No API Methods.
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param null|mixed $parent
	 * @return array|null
	 */
	function _getFactoryOptions( $name, $params = null ) {

		$retval = null;
		switch ( $name ) {
			case 'limit':
				$retval = [
						'minute'       => [ 'min' => 0, 'max' => 59 ],
						'hour'         => [ 'min' => 0, 'max' => 23 ],
						'day_of_month' => [ 'min' => 1, 'max' => 31 ],
						'month'        => [ 'min' => 1, 'max' => 12 ],
						'day_of_week'  => [ 'min' => 0, 'max' => 7 ],
				];
				break;
			case 'status':
				$retval = [
						10 => TTi18n::gettext( 'READY' ),
						20 => TTi18n::gettext( 'RUNNING' ),
				];
				break;
		}

		return $retval;
	}

	/**
	 * @return bool|int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)trim( $value );

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getName() {
		return $this->getGenericDataValue( 'name' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setName( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'name', $value );
	}

	/**
	 * @param $value_arr
	 * @param $limit_arr
	 * @return bool
	 * @noinspection PhpUnusedLocalVariableInspection
	 */
	function isValidLimit( $value_arr, $limit_arr ) {
		if ( is_array( $value_arr ) && is_array( $limit_arr ) ) {
			foreach ( $value_arr as $value ) {
				if ( $value == '*' ) {
					$retval = true;
				}

				if ( $value >= $limit_arr['min'] && $value <= $limit_arr['max'] ) {
					$retval = true;
				} else {
					return false;
				}
			}

			return $retval;
		}

		return false;
	}

	/**
	 * @return bool|mixed
	 */
	function getMinute() {
		return $this->getGenericDataValue( 'minute' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMinute( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'minute', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getHour() {
		return $this->getGenericDataValue( 'hour' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setHour( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'hour', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDayOfMonth() {
		return $this->getGenericDataValue( 'day_of_month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDayOfMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'day_of_month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getMonth() {
		return $this->getGenericDataValue( 'month' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMonth( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'month', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getDayOfWeek() {
		return $this->getGenericDataValue( 'day_of_week' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setDayOfWeek( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'day_of_week', $value );
	}

	/**
	 * @return bool|mixed
	 */
	function getCommand() {
		return $this->getGenericDataValue( 'command' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setCommand( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'command', $value );
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getLastRunDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'last_run_date' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				return TTDate::strtotime( $value );
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setLastRunDate( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		return $this->setGenericDataValue( 'last_run_date', $value );
	}

	/**
	 * @param $bool
	 */
	private function setExecuteFlag( $bool ) {
		$this->execute_flag = (bool)$bool;
	}

	/**
	 * @return bool
	 */
	private function getExecuteFlag() {
		return $this->execute_flag;
	}

	/**
	 * @return bool
	 */
	function isSystemLoadValid() {
		return Misc::isSystemLoadValid();
	}

	/**
	 * Check if job is scheduled to run right NOW.
	 * If the job has missed a run, it will run immediately.
	 * @param int $epoch         EPOCH
	 * @param int $last_run_date EPOCH
	 * @return bool
	 */
	function isScheduledToRun( $epoch = null, $last_run_date = null ) {
		//Debug::text('Checking if Cron Job is scheduled to run: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $epoch == '' ) {
			$epoch = time();
		}

		//Debug::text('Checking if Cron Job is scheduled to run: '. $this->getName(), __FILE__, __LINE__, __METHOD__, 10);
		if ( $last_run_date == '' ) {
			$last_run_date = (int)$this->getLastRunDate();
		}

		Debug::text( ' Name: ' . $this->getName() . ' Current Epoch: ' . TTDate::getDate( 'DATE+TIME', $epoch ) . ' Last Run Date: ' . TTDate::getDate( 'DATE+TIME', $last_run_date ) .' Status: '. $this->getStatus(), __FILE__, __LINE__, __METHOD__, 10 );

		return Cron::isScheduledToRun( $this->getMinute(), $this->getHour(), $this->getDayOfMonth(), $this->getMonth(), $this->getDayOfWeek(), $epoch, $last_run_date );
	}

	/**
	 * @param int $current_epoch
	 * @param string $job_id UUID
	 */
	static function ExecuteForJobQueue( $job_id, $command ) {
		$retval = true;

		//Get each cronjob row again individually incase the status has changed.
		$cjlf = new CronJobListFactory();
		$cjlf->getById( $job_id ); //Let Execute determine if job is running or not so it can find orphans.
		if ( $cjlf->getRecordCount() > 0 ) {
			foreach ( $cjlf as $cjf_obj ) {
				$retval = $cjf_obj->ExecuteCommand( $command ); //Run command no matter when it was scheduled to run, in case the job queue is delayed.
			}
		}

		return $retval;
	}

	function ExecuteCommand( $command ) {
		Debug::text( 'Command: ' . $command, __FILE__, __LINE__, __METHOD__, 10 );

		$start_time = microtime( true );
		exec( $command, $output, $retcode );
		Debug::Arr( $output, 'Time: ' . ( microtime( true ) - $start_time ) . 's - Command RetCode: ' . $retcode . ' Output: ', __FILE__, __LINE__, __METHOD__, 10 );

		TTLog::addEntry( $this->getId(), 500, TTi18n::getText( 'Executing Cron Job' ) . ': ' . $this->getID() . ' ' . TTi18n::getText( 'Command' ) . ': ' . $command . ' ' . TTi18n::getText( 'Return Code' ) . ': ' . $retcode, null, $this->getTable() );

		return $retcode;
	}

	/**
	 * Executes the CronJob
	 * @param null $php_cli
	 * @param null $dir
	 * @return bool
	 */
	function Execute( $php_cli = null, $dir = null ) {
		global $config_vars;
		$lock_file = new LockFile( $config_vars['cache']['dir'] . DIRECTORY_SEPARATOR . $this->getName() . '.lock' );

		//Check job last updated date, if its more then 12hrs and its still in the "running" status,
		//chances are its an orphan. Change status.
		//if ( $this->getStatus() != 10 AND $this->getUpdatedDate() > 0 AND $this->getUpdatedDate() < (time() - ( 6 * 3600 )) ) {
		if ( $this->getStatus() != 10 && $this->getUpdatedDate() > 0 ) {
			Debug::text( 'Status: '. $this->getStatus() .' Updated Date: '. TTDate::getDate('DATE+TIME', $this->getUpdatedDate() ), __FILE__, __LINE__, __METHOD__, 10 );

			$clear_lock = false;
			if ( $lock_file->exists() == false ) {
				Debug::text( 'ERROR: Job PID is not running assuming its an orphan, marking as ready for next run.', __FILE__, __LINE__, __METHOD__, 10 );
				$clear_lock = true;
			} else if ( $this->getUpdatedDate() < ( time() - ( 6 * 3600 ) ) ) {
				Debug::text( 'ERROR: Job has been running for more then 6 hours! Assuming its an orphan, marking as ready for next run.', __FILE__, __LINE__, __METHOD__, 10 );
				$clear_lock = true;
			}

			if ( $clear_lock == true ) {
				$this->setStatus( 10 );
				$this->Save( false );
				$lock_file->delete();
			}

			unset( $clear_lock );
		}

		if ( !is_executable( $php_cli ) ) {
			Debug::text( 'ERROR: PHP CLI is not executable: ' . $php_cli, __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		if ( $this->isSystemLoadValid() == false ) {
			Debug::text( 'System load is too high, skipping...', __FILE__, __LINE__, __METHOD__, 10 );

			return false;
		}

		//Cron script to execute
		$script = $dir . DIRECTORY_SEPARATOR . $this->getCommand();

		if ( $this->getStatus() == 10 && $lock_file->exists() == false ) {
			$lock_file->create();

			$this->setExecuteFlag( true ); //Does not get saved to the database.

			Debug::text( 'Job is NOT currently running, running now...', __FILE__, __LINE__, __METHOD__, 10 );
			//Mark job as running
			$this->setStatus( 20 ); //Running
			$this->setLastRunDate( TTDate::roundTime( time(), 60, 30 ) ); //Set last run date when job is marked as running, so for long running processes it won't attempt to get executed multiple times while its running.
			$this->Save( false );

			//Even if the file does not exist, we still need to "pretend" the cron job ran (set last ran date) so we don't
			//display the big red error message saying that NO jobs have run in the last 24hrs.
			if ( file_exists( $script ) ) {
				$command = '"' . $php_cli . '" "' . $script . '"';
				Debug::text( 'Command: ' . $command, __FILE__, __LINE__, __METHOD__, 10 );

				if ( !isset( $config_vars['other']['enable_job_queue'] ) || $config_vars['other']['enable_job_queue'] == true ) {
					//**IMPORTANT: The CRON table must update the LastRunDate immediately before the command in **queued** (rather than after its executed from the queue itself), otherwise cron.php will continue to try to run it over and over again every 1 minute if its a long running task.
					SystemJobQueue::Add( TTi18n::getText( 'CRON' ) .': '. $this->getName(), null, 'CronJobFactory', 'ExecuteForJobQueue', [ $this->getId(), Misc::getEnvironmentVariableConfigFile() . $command ], 100 );
				} else {
					if ( DEPLOYMENT_ON_DEMAND == true && !( !isset( $config_vars['other']['enable_job_queue'] ) || $config_vars['other']['enable_job_queue'] == true ) ) { //In cases where many instances may be triggering jobs at the same time, add a random sleep to stagger them.
						$sleep_timer = rand( 0, 120 );
						Debug::text( ' Random Sleep: ' . $sleep_timer, __FILE__, __LINE__, __METHOD__, 10 );
						sleep( $sleep_timer );
					}

					$this->ExecuteCommand( Misc::getEnvironmentVariableConfigFile() . $command );
				}
			} else {
				Debug::text( 'WARNING: File does not exist, skipping: ' . $script, __FILE__, __LINE__, __METHOD__, 10 );
			}

			$this->setStatus( 10 ); //Ready
			$this->Save( false );

			$this->setExecuteFlag( false ); //This does not get saved to the database, so it needs to come after Save().

			$lock_file->delete();

			return true;
		} else {
			Debug::text( 'Job is currently running, skipping...', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return false;
	}

	/**
	 * @return bool
	 */
	function Validate() {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Status
		$this->Validator->inArrayKey( 'status',
									  $this->getStatus(),
									  TTi18n::gettext( 'Incorrect Status' ),
									  $this->getOptions( 'status' )
		);
		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is invalid' ),
									1, 250
		);
		// Minute
		$this->Validator->isLength( 'minute',
									$this->getMinute(),
									TTi18n::gettext( 'Minute is invalid' ),
									1, 250
		);
		// Hour
		$this->Validator->isLength( 'hour',
									$this->getHour(),
									TTi18n::gettext( 'Hour is invalid' ),
									1, 250
		);
		// Day of Month
		$this->Validator->isLength( 'day_of_month',
									$this->getDayOfMonth(),
									TTi18n::gettext( 'Day of Month is invalid' ),
									1, 250
		);
		// Month
		$this->Validator->isLength( 'month',
									$this->getMonth(),
									TTi18n::gettext( 'Month is invalid' ),
									1, 250
		);
		// Day of Week
		$this->Validator->isLength( 'day_of_week',
									$this->getDayOfWeek(),
									TTi18n::gettext( 'Day of Week is invalid' ),
									1, 250
		);
		// Command
		$this->Validator->isLength( 'command',
									$this->getCommand(),
									TTi18n::gettext( 'Command is invalid' ),
									1, 250
		);
		// last run
		if ( $this->getLastRunDate() !== false ) {
			$this->Validator->isDate( 'last_run',
									  $this->getLastRunDate(),
									  TTi18n::gettext( 'Incorrect last run' )
			);
		}
		//
		// ABOVE: Validation code moved from set*() functions.
		//
		return true;
	}

	/**
	 * @return bool
	 */
	function preSave() {
		if ( $this->getStatus() == '' ) {
			$this->setStatus( 10 ); //Ready
		}

		if ( $this->getMinute() == '' ) {
			$this->setMinute( '*' );
		}

		if ( $this->getHour() == '' ) {
			$this->setHour( '*' );
		}

		if ( $this->getDayOfMonth() == '' ) {
			$this->setDayOfMonth( '*' );
		}

		if ( $this->getMonth() == '' ) {
			$this->setMonth( '*' );
		}

		if ( $this->getDayOfWeek() == '' ) {
			$this->setDayOfWeek( '*' );
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		$this->removeCache( $this->getId() );

		return true;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		if ( $this->getExecuteFlag() == false ) {
			return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Cron Job' ), null, $this->getTable() );
		}

		return true;
	}
}

?>
