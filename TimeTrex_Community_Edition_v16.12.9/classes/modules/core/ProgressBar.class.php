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

//
//http://danielmclaren.net/2008/08/13/tracking-progress-of-a-server-side-action-in-flashflex
//
class ProgressBar {
	protected $obj = null;
	protected $last_data = null; //Copy of the data the last time we saw it.

	var $default_key = null;
	private $key_prefix = 'progress_bar_';

	var $update_iteration = 1;        //This is how often we actually update the progress bar, even if the function is called more often.
	var $update_interval = 30;        //This is how often in seconds we actually update the progress bar, even if the function is called more often.
	private $last_update_time = null; //Local last update time, to help update at least every X seconds for long running tasks.

	/**
	 * ProgressBar constructor.
	 */
	function __construct() {
		return true;
	}

	/**
	 * Lazy load the SharedMemory object because if its using REDIS it would make a connection immediately rather than when its actually needed.
	 * @return false|SharedMemory|null
	 */
	function getSharedMemoryObject() {
		if ( is_object( $this->obj ) ) {
			return $this->obj;
		} else {
			try {
				$this->obj = new SharedMemory();

				return $this->obj;
			} catch ( Exception $e ) {
				Debug::text( 'ERROR: Caught Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 9 );

				return false;
			}
		}
	}

	/**
	 * Allow setting a default key so we don't have to pass the key around outside of this object.
	 * @param $key
	 */
	function setDefaultKey( $key ) {
		$this->default_key = $key;
	}

	/**
	 * @return null
	 */
	function getDefaultKey() {
		return $this->default_key;
	}

	/**
	 * @param $key
	 * @param null $msg
	 * @return bool
	 */
	function error( $key, $msg = null ) {
		Debug::text( 'error: \'' . $key . ' Key: ' . $key . '(' . microtime( true ) . ') Message: ' . $msg, __FILE__, __LINE__, __METHOD__, 9 );

		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return false;
			}
		}

		if ( !is_object( $this->getSharedMemoryObject() ) ) { //If there is an error getting the shared memory object, cancel out early.
			return false;
		}

		if ( $msg == '' ) {
			$msg = TTi18n::getText( 'Processing...' );
		}

		try {
			$progress_bar_arr = $this->obj->get( $this->key_prefix . $key );
			if ( !is_array( $progress_bar_arr ) ) {
				$progress_bar_arr = []; //Ensure we don't convert FALSE to an array, as that is deprecated.
			}
			$progress_bar_arr['status_id'] = 9999;
			$progress_bar_arr['message'] = $msg;

			$this->obj->set( $this->key_prefix . $key, $progress_bar_arr );

			return true;
		} catch ( Exception $e ) {
			Debug::text( 'ERROR: Caught Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 9 );

			return false;
		}
	}

	/**
	 * @param $key
	 * @param int $total_iterations
	 * @param int $update_iteration EPOCH
	 * @param null $msg
	 * @return bool
	 */
	function start( $key, $total_iterations = 100, $update_iteration = null, $msg = null, $private_data = null ) {
		Debug::text( 'start: \'' . $key . '\' Iterations: ' . $total_iterations . ' Update Iterations: ' . $update_iteration . ' Key: ' . $key . '(' . microtime( true ) . ') Message: ' . $msg, __FILE__, __LINE__, __METHOD__, 9 );

		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return false;
			}
		}

		if ( $total_iterations <= 1 ) {
			return false;
		}

		if ( !is_object( $this->getSharedMemoryObject() ) ) { //If there is an error getting the shared memory object, cancel out early.
			return false;
		}

		if ( $update_iteration == '' ) {
			$this->update_iteration = ceil( $total_iterations / 20 ); //Update every 5%.
		} else {
			$this->update_iteration = $update_iteration;
		}

		if ( $msg == '' ) {
			$msg = TTi18n::getText( 'Processing...' );
		}

		$epoch = microtime( true );

		$progress_bar_arr = [
				'status_id'         => 10,
				'start_time'        => $epoch,
				'current_iteration' => 0,
				'total_iterations'  => $total_iterations,
				'last_update_time'  => $epoch,
				'message'           => $msg,
				'private_data'      => ( ( $private_data !== null ) ? json_encode( $private_data ) : null ),
		];
		try {
			$this->obj->set( $this->key_prefix . $key, $progress_bar_arr );

			return true;
		} catch ( Exception $e ) {
			Debug::text( 'ERROR: Caught Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 9 );

			return false;
		}
	}

	/**
	 * @param $key
	 * @return bool
	 */
	function delete( $key ) {
		return $this->stop( $this->key_prefix . $key );
	}

	/**
	 * @param $key
	 * @return bool
	 */
	function stop( $key ) {
		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return false;
			}
		}

		if ( !is_object( $this->getSharedMemoryObject() ) ) { //If there is an error getting the shared memory object, cancel out early.
			return false;
		}

		try {
			return $this->obj->delete( $this->key_prefix . $key );
		} catch ( Exception $e ) {
			Debug::text( 'ERROR: Caught Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 9 );

			return false;
		}
	}

	/**
	 * @param $key
	 * @param int $current_iteration
	 * @param null $msg
	 * @return bool
	 */
	function set( $key, $current_iteration, $msg = null, $private_data = null ) {
		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return false;
			}
		}

		if ( !is_object( $this->getSharedMemoryObject() ) ) { //If there is an error getting the shared memory object, cancel out early.
			return false;
		}

		$current_iteration = (int)$current_iteration; //Its possible a user could pass in a corrupted array that passes strings to this.

		//Add quick IF statement to short circuit any work unless we meet the update_iteration, ie: every X calls do we actually do anything.
		//When processing long batches though, we need to update every iteration for the first 10 iterations so we can get an accruate estimated time for completion.
		//  Also check for the update_interval as the maximum amount of time that can elapse before updating the progress bar. But only
		if ( $current_iteration <= 10 || ( $current_iteration % $this->update_iteration ) == 0 || ( time() - $this->last_update_time ) > $this->update_interval ) {
			//Debug::text('set: '. $key .' Iteration: '. $current_iteration, __FILE__, __LINE__, __METHOD__, 9);

			try {
				$progress_bar_arr = $this->obj->get( $this->key_prefix . $key );

				if ( $progress_bar_arr != false
						&& is_array( $progress_bar_arr )
						&& $current_iteration >= 0
						&& ( isset( $progress_bar_arr['total_iterations'] ) && $current_iteration <= $progress_bar_arr['total_iterations'] ) ) {

					/*
					if ( PRODUCTION == FALSE AND isset($progress_bar_arr['total_iterations']) AND $progress_bar_arr['total_iterations'] >= 1 ) {
						//Add a delay based on the total iterations so we can test the progressbar more often
						$total_delay = 15000000; //10seconds
						usleep( ( ($total_delay / $progress_bar_arr['total_iterations']) * $this->update_iteration));
					}
					*/

					$progress_bar_arr['current_iteration'] = $current_iteration;
					$progress_bar_arr['last_update_time'] = microtime( true );
					$this->last_update_time = $progress_bar_arr['last_update_time'];
				}

				if ( $msg != '' ) {
					$progress_bar_arr['message'] = $msg;
				}

				if ( $private_data !== null ) {
					$progress_bar_arr['private_data'] = json_encode( $private_data );
				}

				$this->last_data = $progress_bar_arr;
				return $this->obj->set( $this->key_prefix . $key, $progress_bar_arr );
			} catch ( Exception $e ) {
				Debug::text( 'ERROR: Caught Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 9 );

				return false;
			}
		}

		return true;
	}

	/**
	 * @return array|null
	 */
	function getLastData() {
		return $this->last_data;
	}

	function calculateRemainingTime() {
		$data = $this->getLastData();

		if ( isset( $data['current_iteration'] ) && $data['current_iteration'] > 0 ) {
			$time_per_iteration = ( ( $data['last_update_time'] - $data['start_time'] ) / $data['current_iteration'] );
			$time_since_last_update = ( microtime( true ) - $data['last_update_time'] );

			$estimate_iterations_since_last_update = 0;
			if ( $time_per_iteration > 0 ) {
				$estimate_iterations_since_last_update = floor( ( $time_since_last_update / $time_per_iteration ) );
			}

			$estimate_completed_iterations = ( $data['current_iteration'] + $estimate_iterations_since_last_update );
			if ( $estimate_completed_iterations > $data['total_iterations'] ) {
				$estimate_completed_iterations = $data['total_iterations'];
			}
			$remaining_time = ( $time_per_iteration * ( $data['total_iterations'] - $estimate_completed_iterations ) );

			$next_check_by_time = ( $remaining_time > 5 ) ? floor( ( $remaining_time / 5 ) ) : 1;
			if ( $next_check_by_time > 30 ) {
				$next_check_by_time = 30;
			}
			//Debug::text( '    Elapsed TimeTime: '. ( microtime( true ) - $data['start_time'] ) .' Elapsed Iteration Time: '. ( $data['last_update_time'] - $data['start_time'] ), __FILE__, __LINE__, __METHOD__, 9 );
			//Debug::text( '    Time Per Iteration: '. $time_per_iteration .' Time Since Last Update: '. $time_since_last_update .' Estimated: Iterations Since Last Update: '. $estimate_iterations_since_last_update .' Completed Iterations: '. $estimate_completed_iterations .' Iterations: Total: '. $data['total_iterations'] .' Current: '. $data['current_iteration'], __FILE__, __LINE__, __METHOD__, 9 );
		} else {
			$remaining_time = 0;
			if ( isset( $data['start_time'] ) ) {
				$next_check_by_time = ( ( microtime( true ) - $data['start_time'] ) / 5 );
				if ( $next_check_by_time < 1 ) {
					$next_check_by_time = 1;
				}
			} else {
				$next_check_by_time = 1;
			}
		}

		//Debug::text( '  Remaining Time: ' . $remaining_time .' Next Check: '. $next_check_by_time, __FILE__, __LINE__, __METHOD__, 9 );
		return [ 'remaining_time' => $remaining_time, 'next_check_time' => $next_check_by_time ];
	}

	/**
	 * @param $key
	 * @return array|bool
	 */
	function get( $key ) {
		if ( $key == '' ) {
			$key = $this->getDefaultKey();
			if ( $key == '' ) {
				return false;
			}
		}

		if ( !is_object( $this->getSharedMemoryObject() ) ) { //If there is an error getting the shared memory object, cancel out early.
			return false;
		}

		try {
			$retarr = $this->obj->get( $this->key_prefix . $key );
			if ( is_array( $retarr ) ) {
				$this->last_data = $retarr;

				//Calculate time remaining.
				$retarr = array_merge( $retarr, $this->calculateRemainingTime() );
			}

			return $retarr;
		} catch ( Exception $e ) {
			Debug::text( 'ERROR: Caught Exception: ' . $e->getMessage(), __FILE__, __LINE__, __METHOD__, 9 );

			return false;
		}
	}

	/**
	 * @param $key
	 * @param int $total_iterations
	 * @return bool
	 */
	function test( $key, $total_iterations = 10 ) {
		Debug::text( 'testProgressBar: ' . $key . ' Iterations: ' . $total_iterations, __FILE__, __LINE__, __METHOD__, 9 );

		$this->start( $key, $total_iterations );

		for ( $i = 1; $i <= $total_iterations; $i++ ) {
			$this->set( $key, $i );
			sleep( rand( 1, 2 ) );
		}

		$this->stop( $key );

		return true;
	}
}

?>
