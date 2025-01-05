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
 * @package Modules\SystemJobQueue
 */
class SystemJobQueueFactory extends Factory {
	protected $table = 'system_job_queue';

	public $user_obj = null;
	public $background_process_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'batch_id' )->setFunctionMap( 'Batch' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'status_id' )->setFunctionMap( 'Status' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'priority' )->setFunctionMap( 'Priority' )->setType( 'smallint' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'text' )->setIsNull( false ),
							TTSCol::new( 'user_id' )->setFunctionMap( 'User' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'effective_date' )->setFunctionMap( 'EffectiveDate' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'queued_date' )->setFunctionMap( 'QueuedDate' )->setType( 'numeric' )->setIsNull( false ),
							TTSCol::new( 'run_date' )->setFunctionMap( 'RunDate' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'completed_date' )->setFunctionMap( 'CompletedDate' )->setType( 'numeric' )->setIsNull( true ),
							TTSCol::new( 'retry_attempt' )->setFunctionMap( 'RetryAttempt' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'retry_max_attempt' )->setFunctionMap( 'RetryMaxAttempt' )->setType( 'integer' )->setIsNull( false ),
							TTSCol::new( 'class' )->setFunctionMap( 'Class' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'method' )->setFunctionMap( 'Method' )->setType( 'varchar' )->setIsNull( true ),
							TTSCol::new( 'extra_data' )->setFunctionMap( 'ExtraData' )->setType( 'json' )->setIsNull( true ),
							TTSCol::new( 'arguments' )->setFunctionMap( 'Arguments' )->setType( 'json' )->setIsNull( true ),
							TTSCol::new( 'return_data' )->setFunctionMap( 'ReturnData' )->setType( 'json' )->setIsNull( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'user_id' )->setType( 'uuid_list' )->setColumn( 'a.user_id' )->setMulti( true ),
							TTSSearchField::new( 'status_id' )->setType( 'numeric_list' )->setColumn( 'a.status_id' )->setMulti( true )
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {
			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APISystemJobQueue' )->setMethod( 'getSystemJobQueue' )
									->setSummary( 'Get system job queue records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' )
					)
			);
		}

		return $schema_data;
	}

	/**
	 * @param $name
	 * @param array $parent
	 * @return array|bool|null
	 */
	function _getFactoryOptions( $name, $params = null ) {
		$retval = null;
		switch ( $name ) {
			case 'status':
				//If these are changed, also change SystemJobQueueListFactory->getBatchStatus()
				$retval = [
						10 => TTi18n::gettext( 'Pending' ),
						20 => TTi18n::gettext( 'Running' ),
						50 => TTi18n::gettext( 'Failed' ),
						100 => TTi18n::gettext( 'Completed' ),
				];
				break;
			case 'columns':
				$retval = [
						'-1202-name'           => TTi18n::gettext( 'Name' ),
						'-1230-priority'       => TTi18n::gettext( 'Priority' ),
						'-2010-effective_date' => TTi18n::gettext( 'Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'name',
						'priority',
						'effective_date',
				];
				break;
		}

		return $retval;
	}

	/**
	 * @param $data
	 * @return array
	 */
	function _getVariableToFunctionMap( $data ) {
		$variable_function_map = [
				'id'                => 'ID',
				'batch_id'          => 'Batch',
				'status_id'         => 'Status',
				'priority'          => 'Priority',
				'name'              => 'Name',
				'user_id'           => 'User',
				'effective_date'    => 'EffectiveDate',
				'created_date'      => 'CreatedDate',
				'run_date'          => 'RunDate',
				'completed_date'    => 'CompletedDate',
				'retry_attempt'     => 'RetryAttempt',
				'retry_max_attempt' => 'RetryMaxAttempt',
				'class'             => 'Class',
				'method'            => 'Method',
				'arguments'         => 'Arguments',
				'extra_data'        => 'ExtraData',
				'return_data'       => 'ReturnData',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|mixed
	 */
	function getBatch() {
		return $this->getGenericDataValue( 'batch_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setBatch( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'batch_id', $value );
	}

	/**
	 * @return int
	 */
	function getStatus() {
		return $this->getGenericDataValue( 'status_id' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setStatus( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'status_id', $value );
	}

	/**
	 * @return int
	 */
	function getPriority() {
		return $this->getGenericDataValue( 'priority' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setPriority( $value ) {
		// 25=Real-Time (user waiting on it)
		// 50=Normal
		// 120=Background batch job.
		$value = (int)$value;

		return $this->setGenericDataValue( 'priority', $value );
	}

	/**
	 * @return int
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
	 * @return object|bool
	 */
	function getUserObject() {
		return $this->getGenericObject( 'UserListFactory', $this->getUser(), 'user_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getUser() {
		return $this->getGenericDataValue( 'user_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setUser( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'user_id', $value );
	}

	/**
	 * @return int
	 */
	function getEffectiveDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'effective_date' );
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
	 * @param $epoch
	 * @return bool
	 */
	function setEffectiveDate( $value ) {
		return $this->setGenericDataValue( 'effective_date', $value );
	}

	/**
	 * @return int
	 */
	function getQueuedDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'queued_date' );
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
	 * @param $epoch
	 * @return bool
	 */
	function setQueuedDate( $value = null ) {
		//This needs to support millisecond resolution, so we were forced to use integer datatypes instead of timestamp in the DB due to ADODB having difficulty with it.
		return $this->setGenericDataValue( 'queued_date', $value );
	}

	/**
	 * @return int
	 */
	function getRunDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'run_date' );
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
	 * @param $epoch
	 * @return bool
	 */
	function setRunDate( $value ) {
		//This needs to support millisecond resolution, so we were forced to use integer datatypes instead of timestamp in the DB due to ADODB having difficulty with it.
		return $this->setGenericDataValue( 'run_date', $value );
	}

	/**
	 * @return int
	 */
	function getCompletedDate( $raw = false ) {
		$value = $this->getGenericDataValue( 'completed_date' );
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
	 * @param $epoch
	 * @return bool
	 */
	function setCompletedDate( $value ) {
		//This needs to support millisecond resolution, so we were forced to use integer datatypes instead of timestamp in the DB due to ADODB having difficulty with it.
		return $this->setGenericDataValue( 'completed_date', $value );
	}

	/**
	 * @return int
	 */
	function getRetryAttempt() {
		return $this->getGenericDataValue( 'retry_attempt' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRetryAttempt( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'retry_attempt', $value );
	}

	/**
	 * @return int
	 */
	function getRetryMaxAttempt() {
		return $this->getGenericDataValue( 'retry_max_attempt' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setRetryMaxAttempt( $value ) {
		$value = (int)$value;

		return $this->setGenericDataValue( 'retry_max_attempt', $value );
	}

	/**
	 * @return int
	 */
	function getClass() {
		return $this->getGenericDataValue( 'class' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setClass( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'class', $value );
	}

	/**
	 * @return int
	 */
	function getMethod() {
		return $this->getGenericDataValue( 'method' );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setMethod( $value ) {
		$value = trim( $value );

		return $this->setGenericDataValue( 'method', $value );
	}

	/**
	 * @return int
	 */
	function getExtraData() {
		return json_decode( $this->getGenericDataValue( 'extra_data' ), true );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setExtraData( $value ) {
		return $this->setGenericDataValue( 'extra_data', json_encode( $value ) );
	}

	/**
	 * @return int
	 */
	function getArguments() {
		return json_decode( $this->getGenericDataValue( 'arguments' ), true );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setArguments( $value ) {
		return $this->setGenericDataValue( 'arguments', json_encode( $value ) );
	}

	/**
	 * @return int
	 */
	function getReturnData() {
		return json_decode( $this->getGenericDataValue( 'return_data' ), true );
	}

	/**
	 * @param $value
	 * @return bool
	 */
	function setReturnData( $value ) {
		return $this->setGenericDataValue( 'return_data', json_encode( $value ) );
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//

		if ( $this->getDeleted() == false ) {
			if ( $this->getUser() != TTUUID::getZeroID() ) {
				$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$this->Validator->isResultSetWithRows( 'user',
													   $ulf->getByID( $this->getUser() ),
													   TTi18n::gettext( 'Invalid Employee' )
				);
			}

			if ( $this->getName() != '' ) {
				$this->Validator->isLength( 'name',
											$this->getName(),
											TTi18n::gettext( 'Name is invalid' ),
											1, 200
				);
			}

			// Status
			if ( $this->getStatus() != '' ) {
				$this->Validator->inArrayKey( 'status',
											  $this->getStatus(),
											  TTi18n::gettext( 'Incorrect Status' ),
											  $this->getOptions( 'status' )
				);
			}

			// Priority
			$this->Validator->isGreaterThan( 'priority',
											 $this->getPriority(),
											 TTi18n::gettext( 'Priority must be 1 or higher' ),
											 1
			);
			if ( $this->Validator->isError( 'priority' ) == false ) {
				$this->Validator->isLessThan( 'priority',
											  $this->getPriority(),
											  TTi18n::gettext( 'Priority must be less than 128' ),
											  128
				);
			}

			// Effective Date (must always be specified)
			$this->Validator->isDate( 'effective_date',
									  $this->getEffectiveDate(),
									  TTi18n::gettext( 'Invalid effective date' )
			);
		}

		return true;
	}

	/**
	 * @return bool
	 */
	function preValidate() {
		if ( $this->getStatus() == '' ) {
			$this->setStatus( 10 ); //Pending
		}

		if ( $this->getPriority() == '' ) {
			$this->setPriority( 50 ); //Middle priority
		}

		if ( $this->getQueuedDate() == '' ) {
			$this->setQueuedDate( microtime( true ) );
		}

		if ( $this->getRetryAttempt() == '' ) {
			$this->getRetryAttempt( 0 );
		}

		if ( $this->getRetryMaxAttempt() == '' ) {
			$this->getRetryMaxAttempt( 0 );
		}

		return true;
	}

	function postSave() {
		$this->notifyWorkers(); //Send PostgreSQL notify out to workers.

		return true;
	}

	/**
	 * @param $data
	 * @return bool
	 */
	function setObjectFromArray( $data ) {
		if ( is_array( $data ) ) {
			$variable_function_map = $this->getVariableToFunctionMap();
			foreach ( $variable_function_map as $key => $function ) {
				if ( isset( $data[$key] ) ) {

					$function = 'set' . $function;
					switch ( $key ) {
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			//$this->setCreatedAndUpdatedColumns( $data );

			return true;
		}

		return false;
	}

	/**
	 * @param null $include_columns
	 * @return array
	 */
	function getObjectAsArray( $include_columns = null ) {
		$data = [];
		$variable_function_map = $this->getVariableToFunctionMap();
		if ( is_array( $variable_function_map ) ) {
			foreach ( $variable_function_map as $variable => $function_stub ) {
				if ( $include_columns == null || ( isset( $include_columns[$variable] ) && $include_columns[$variable] == true ) ) {

					$function = 'get' . $function_stub;
					switch ( $variable ) {
						case 'effective_date':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE+TIME', $this->$function() );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = $this->$function();
							}
							break;
					}
				}
			}
			//$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @return bool
	 */
	function getDeleted() {
		return false;
	}

	/**
	 * @param $bool
	 * @return bool
	 */
	function setDeleted( $bool ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setCreatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getCreatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setCreatedBy( $id = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setUpdatedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getUpdatedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setUpdatedBy( $id = null ) {
		return false;
	}


	/**
	 * @return bool
	 */
	function getDeletedDate() {
		return false;
	}

	/**
	 * @param int $epoch EPOCH
	 * @return bool
	 */
	function setDeletedDate( $epoch = null ) {
		return false;
	}

	/**
	 * @return bool
	 */
	function getDeletedBy() {
		return false;
	}

	/**
	 * @param string $id UUID
	 * @return bool
	 */
	function setDeletedBy( $id = null ) {
		return false;
	}

	function notifyWorkers( $payload = null ) {
		global $db;

		//For each unique payload, a new event gets sent. This can cause a backlog of events that the workers are waiting on.
		return $db->Execute( 'NOTIFY "system_job_queue", \''. $payload .'\'');
	}

	function getBackgroundProcessObject() {
		if ( is_object( $this->background_process_obj ) ) {
			 return $this->background_process_obj;
		} else {
			$bp = new BackgroundProcess();
			$bp->setMaxProcesses( $this->getMaxProcesses() );

			global $config_vars;

			//Allow specifying a separate directory to store QueueWorker lock files in that might be more performant than the regular cache directory. (ie: not a network share)
			if ( isset( $config_vars['cache']['queue_worker_dir'] ) && $config_vars['cache']['queue_worker_dir'] != '' ) {
				$lock_file_dir = $config_vars['cache']['queue_worker_dir'];
			} else {
				$lock_file_dir = $config_vars['cache']['dir'];
			}
			$bp->setLockFileDirectory( $lock_file_dir );

			$bp->setLockFilePrefix( 'SystemQueueWorker' );

			$this->background_process_obj = $bp;

			return $this->background_process_obj;
		}
	}

	function getMaxProcesses() {
		global $config_vars;
		if ( !isset( $config_vars['other']['max_processes'] ) ) {
			$config_vars['other']['max_processes'] = 2;
		}

		return $config_vars['other']['max_processes'];
	}

	function getRunningWorkerProcesses( $check_pids = false ) {
		$bp = $this->getBackgroundProcessObject();
		$lock_files = $bp->getLockFiles();
		$current_processes = $bp->getCurrentProcesses( $lock_files );

		//If more than process is running and flag is set, lets check that the PID in each lock file is in fact running.
		//  This will help catch cases where the process crashed and the stale lock file still exists.
		if ( $current_processes > 0 && $check_pids == true ) {
			$lock_files = $bp->purgeLockFiles( $lock_files, $check_pids );
			$current_processes = $bp->getCurrentProcesses( $lock_files );
		}

		Debug::text( '  Current Running Worker Processes: ' . $current_processes, __FILE__, __LINE__, __METHOD__, 10 );
		return $current_processes;
	}

	function startWorkerProcess( $worker_script ) {
		global $config_vars;

		$bp = $this->getBackgroundProcessObject();

		//Execute self to generate each report in its own process.
		$command = '"' . $config_vars['path']['php_cli'] . '" "' . $worker_script . '" "#lock_file#"';
		Debug::text( '  Spawning new QueueWorker process: Command: ' . $command, __FILE__, __LINE__, __METHOD__, 10 );
		$retval = $bp->run( $command, true ); //If max processes reached, just exit immediately.
		if ( $retval !== true ) {
			Debug::text( '  NOTICE: Unable to start new background process... Max processes or Lock file perhaps?', __FILE__, __LINE__, __METHOD__, 10 );
		}

		return $retval;
	}

	function lock() {
		global $db;
		Debug::Text( '  Locking Job: ID: ' . $this->getId() .' Class: '. $this->getClass() .' Method: '. $this->getMethod(), __FILE__, __LINE__, __METHOD__, 10 );
		$query = 'UPDATE system_job_queue SET status_id = 20, run_date = '. microtime( true ) .' WHERE id = '. $this->db->qstr( $this->getId() ) .'';
		return $db->Execute( $query ); //Make sure we commit the transaction outside of this function, but immediately after its run.
	}

	function run() {
		global $current_user, $current_user_prefs, $current_company; //Set global variables so its easy to access this data within the job queue runner function itself.
		Debug::Text( '  Running Job: ID: ' . $this->getId() .' Class: '. $this->getClass() .' Method: '. $this->getMethod() .' Effective Date: '. TTDate::getDate( 'DATE+TIME', $this->getEffectiveDate() ) .' User: '. $this->getUser() .' Current User: '. ( ( isset( $current_user ) && is_object( $current_user ) ) ? $current_user->getId() : 'NONE' ), __FILE__, __LINE__, __METHOD__, 10 );

		// $current_user, $current_user_prefs are unset at the bottom of this function as well.
		$current_user = $current_user_prefs = $current_company = null;  //Make sure user/preferences don't get carried over into another job. They are unset at the bottom of this function as well. -- unset() doesn't work on global variables.

		//Quick way to clear all memory cache.
		// This is needed prior to each job running, as this could be done in a long running process, and any cached data from minutes or hours ago wouldn't have been refreshed if it was cleared in another process.
		// We don't want to disable memory cache completely though, as this can be helpful within resource heavy jobs themselves.
		// Essentially this will cause similar caching behavior as normal short running web requests do. Cache is initialized fresh before each one.
		// **NOTE: This is done at the end of the job to free up memory too.
		$this->cache->_memoryCachingArray = [];
		$this->cache->_memoryCachingCounter = 0;

		//Whenever a job is assigned to a user, be sure to apply user preferences.
		if ( TTUUID::isUUID( $this->getUser() ) && $this->getUser() != TTUUID::getZeroID() ) {
			$ulf = TTnew('UserListFactory');
			$ulf->getById( $this->getUser() );
			if ( $ulf->getRecordCount() > 0 ) {
				$current_user = $ulf->getCurrent();

				$current_user_prefs = $current_user->getUserPreferenceObject();
				if ( is_object( $current_user_prefs ) ) {
					$current_user_prefs->setDateTimePreferences();
					TTi18n::setLanguage( $current_user_prefs->getLanguage() );
				}

				TTi18n::setCountry( $current_user->getCountry() );
				TTi18n::setLocale(); //Sets master locale
			} else {
				Debug::Text( '    ERROR: User does not exist! ID: '. $this->getUser(), __FILE__, __LINE__, __METHOD__, 10 );
			}
			unset( $ulf );
		} else {
			//Force default timezone and user preferences, otherwise they could get corrupted between job runs.
			TTDate::setTimeZone();
			TTDate::setDateFormat( 'd-M-y' );
			TTDate::setTimeFormat( 'g:i A' );
			TTDate::setTimeUnitFormat( 10 );
			TTi18n::setLanguage( 'en' );
			TTi18n::chooseBestLocale();
		}

		//$this->StartTransaction();  //Leave transaction handling to the child function itself.

		//Extra data must be set *before* the job is run.
		//$extra_data = $this->getExtraData();
		//if ( isset( $extra_data['api_message_id'] ) && $extra_data['api_message_id'] != '' ) {
		//	SystemJobQueue::setAPIMessageID( $extra_data['api_message_id'] );
		//}
		//
		//if ( isset( $extra_data['user_generic_status_batch_id'] ) && $extra_data['user_generic_status_batch_id'] != '' ) {
		//	SystemJobQueue::setUserGenericStatusBatchID( $extra_data['user_generic_status_batch_id'] );
		//} else if ( $this->getBatch() != '' ) {
		//	SystemJobQueue::setUserGenericStatusBatchID( $this->getBatch() );
		//}
		SystemJobQueue::setUserGenericStatusBatchID( $this->getBatch() );
		Debug::Text( '    Batch ID: '. $this->getBatch() .' Running as Current User: '. ( ( isset( $current_user ) && is_object( $current_user ) ) ? $current_user->getId() : 'NONE' ), __FILE__, __LINE__, __METHOD__, 10 );

		global $db;

		try {
			if ( !( class_exists( $this->getClass() ) && method_exists( $this->getClass(), $this->getMethod() ) ) )  {
				throw new Exception( 'ERROR: Class or Method does not exist: Class: ' . $this->getClass() .' Method: '. $this->getMethod() .' Job Queue ID: '. $this->getId() );
			}

			$retval = call_user_func_array( [ $this->getClass(), $this->getMethod() ], (array)$this->getArguments() );
			Debug::Arr( $retval, '    Return Data: ', __FILE__, __LINE__, __METHOD__, 10 );

			$query = 'UPDATE system_job_queue SET status_id = 100, completed_date = '. microtime( true ) .', return_data = '. $this->db->qstr( json_encode( $retval ) ) .' WHERE id = '. $this->db->qstr( $this->getId() ) .'';
			$retval = $db->Execute( $query ); //Make sure we commit the transaction outside of this function, but immediately after its run.
		} catch ( Exception $e ) {
			Debug::Text( '    ERROR: Job triggered exception: '. $e->getMessage(), __FILE__, __LINE__, __METHOD__, 10 );

			//Exception triggered, set job as failed.
			$query = 'UPDATE system_job_queue SET status_id = 50, completed_date = '. microtime( true ) .', return_data = '. $this->db->qstr( json_encode( $e->getMessage() ) )  .' WHERE id = '. $this->db->qstr( $this->getId() ) .'';
			$retval = $db->Execute( $query ); //Make sure we commit the transaction outside of this function, but immediately after its run.
		}

		//Clean-up any variables that should never be carried over to the next job.
		$current_user = $current_user_prefs = $current_company = null; //Make sure user/preferences don't get carried over into another job. -- unset() doesn't work on global variables.

		//$this->CommitTransaction(); //Leave transaction handling to the child function itself.

		// **NOTE: This is done at the start of each job too. See that for more comments.
		$this->cache->_memoryCachingArray = [];
		$this->cache->_memoryCachingCounter = 0;

		return $retval;

	}
}