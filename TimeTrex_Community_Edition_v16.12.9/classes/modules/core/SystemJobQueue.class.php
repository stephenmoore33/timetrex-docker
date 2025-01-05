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
class SystemJobQueue {
	static $user_generic_status_batch_id = null;
	static $api_message_id = null;
	static $progress_bar_obj = null;

	/**
	 * @return null|ProgressBar
	 */
	static function getProgressBarObject() {
		if ( !is_object( self::$progress_bar_obj ) ) {
			self::$progress_bar_obj = new ProgressBar();
		}

		return self::$progress_bar_obj;
	}

	static function getAPIMessageID() {
		return self::$api_message_id;
	}

	static function setAPIMessageID( $value ) {
		self::$api_message_id = $value;

		return true;
	}

	static function getUserGenericStatusBatchID() {
		return self::$user_generic_status_batch_id;
	}

	static function setUserGenericStatusBatchID( $value ) {
		self::$user_generic_status_batch_id = $value;

		return true;
	}

	public static function waitUntilBatchCompleted( $batch_id, $api_message_id, $retry_timeout = 2, $timeout = 3600 ) {
		$start_epoch = time();

		$max_retry_timeout = 30;

		$tmp_retry_timeout = $retry_timeout;
		while ( 1 ) {
			$batch_status_arr = self::getBatchStatus( $batch_id );
			if ( is_array( $batch_status_arr ) ) {
				if ( $api_message_id != '' ) {
					//Update progress bar.
					Debug::Text( '  Updating Progress Bar Iteration: ' . $batch_status_arr['current_iteration'], __FILE__, __LINE__, __METHOD__, 10 );
					self::getProgressBarObject()->set( $api_message_id, $batch_status_arr['current_iteration'] );
					$progress_bar_estimated_data = self::getProgressBarObject()->calculateRemainingTime();
				}

				if ( $batch_status_arr['is_completed'] == true ) {
					if ( $api_message_id != '' ) {
						self::getProgressBarObject()->stop( $api_message_id );
					}
					break;
				}

				$running_time = ( time() - $start_epoch );
				if ( $running_time > $timeout ) {
					break;
				}

				Debug::Text( '  Sleeping: ' . $tmp_retry_timeout, __FILE__, __LINE__, __METHOD__, 10 );
				sleep( $tmp_retry_timeout );

				$tmp_retry_timeout = $progress_bar_estimated_data['next_check_time'];

				//Never let retry timeout exceed max.
				if ( $tmp_retry_timeout > $max_retry_timeout ) {
					$tmp_retry_timeout = $max_retry_timeout;
				}
			} else {
				Debug::Text( 'NOTICE: Batch does not exist... Batch ID: '. $batch_id, __FILE__, __LINE__, __METHOD__, 10 );
				return false;
			}
		}

		Debug::Text( 'Batch completed: Jobs: '. $batch_status_arr['total_iterations'] .' in '. ( time() - $start_epoch ).'s Batch ID: '. $batch_id, __FILE__, __LINE__, __METHOD__, 10 );
		return $batch_status_arr;
	}

	public static function getBatchStatus( $batch_id ) {
		$sjqlf = TTnew('SystemJobQueueListFactory');
		$retarr = $sjqlf->getBatchStatus( $batch_id );
		return $retarr;
	}

	public static function Add( $name, $batch_id, $class, $method, $args, $priority = null, $extra_data = null, $effective_date = null, $user_id = null ) {
		if ( empty( $effective_date ) ) {
			$effective_date = microtime( true );
		}

		if ( empty( $user_id ) ) {
			//$user_id might be of a job applicant. For examole when a applicant uploads their resume while logged into the recruitment portal.
			//Need to pass in zero UUID for $user_id in that case, otherwise validation will fail.
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getID();
			} else {
				$user_id = TTUUID::getZeroID();
			}
		}

		$sjqf = TTNew('SystemJobQueueFactory'); /** @var SystemJobQueueFactory $sjqf */
		$sjqf->setBatch( $batch_id );
		$sjqf->setStatus( 10 ); //10=Pending
		$sjqf->setPriority( $priority );
		$sjqf->setName( $name );
		$sjqf->setUser( $user_id );
		$sjqf->setEffectiveDate( $effective_date );
		$sjqf->setClass( $class );
		$sjqf->setMethod( $method );
		$sjqf->setArguments( $args ); //Each top level array element is an argument.
		$sjqf->setExtraData( $extra_data ); //API Message ID/UserGenericStatus Queue ID, etc...
		if ( $sjqf->isValid() ) {
			Debug::Arr( $args, '  Adding to Job Queue. Name: '. $name .' Class: '. $class .' Method: '. $method .' Effective Date: '. TTDate::getDate('DATE+TIME', $sjqf->getEffectiveDate() ) .' ('. $sjqf->getEffectiveDate() .')', __FILE__, __LINE__, __METHOD__, 10 );
			return $sjqf->Save();
		}

		return false;
	}

	public static function DeletePending( $class, $method, $batch_id, $user_id = null ) {
		if ( empty( $user_id ) ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getID();
			} else {
				$user_id = TTUUID::getZeroID();
			}
		}

		$sjqlf = TTnew('SystemJobQueueListFactory');
		$retval = $sjqlf->deletePending( $user_id, $class, $method, $batch_id );

	    return $retval;
	}

	public static function DeletePendingDuplicates( $class, $method, $user_id = null ) {
		if ( empty( $user_id ) ) {
			global $current_user;
			if ( is_object( $current_user ) ) {
				$user_id = $current_user->getID();
			} else {
				$user_id = TTUUID::getZeroID();
			}
		}

		$sjqlf = TTnew('SystemJobQueueListFactory');
		$retval = $sjqlf->deletePendingDuplicates( $user_id, $class, $method );

		return $retval;
	}

	static function sendNotificationToBrowser( $user_id = null, $payload = null ) {
		if ( $user_id == null ) {
			global $current_user;
			if ( isset($current_user) && is_object( $current_user ) ) {
				$user_id = $current_user->getId();
			}
		}

		if ( TTUUID::isUUID( $user_id ) && $user_id != TTUUID::getZeroID() ) {
			if ( $payload == null ) {
				$payload = [ 'timetrex' => [ 'event' => [ [ 'type' => 'refresh_job_queue', 'check_completed' => true ] ] ] ];
			}

			Debug::Text( '  Sending background notification to users browser to update the job queue...', __FILE__, __LINE__, __METHOD__, 10 );
			$notification_data = [
					'object_id'      => TTUUID::getZeroID(),
					'user_id'        => $user_id,
					'type_id'        => 'system',
					'object_type_id' => 0,
					'priority'       => 2, //2=High
					'title_short'    => null, //Background
					'payload'        => $payload,
					'device_id'      => [ 4 ], //Web Browser Only.
			];
			Notification::sendNotification( $notification_data );
		}

		return true;
	}

	static function Purge() {
		global $db;
		Debug::Text( 'Purging old job queues before: ' . TTDate::getDate('DATE+TIME', ( time() - 172800 ) ), __FILE__, __LINE__, __METHOD__, 10 );

		//Mark jobs stuck running for more than 12hrs as failed.
		$purge_query = 'UPDATE system_job_queue SET status_id = 50, retry_attempt = retry_attempt + 1, completed_date = extract( epoch from now() ) WHERE status_id = 20 AND run_date <= '. ( time() - 43200 ) .' AND completed_date IS NULL';
		$db->Execute( $purge_query );

		//Purge successfully completed jobs within 2 days
		//Purge failed jobs within 1 week.
		$purge_query = 'DELETE FROM system_job_queue WHERE ( status_id = 100 AND completed_date <= '. ( time() - 172800 ) .' ) OR ( status_id = 50 AND completed_date <= '. ( time() - 604800 ) .' )';
		return $db->Execute( $purge_query );
	}
}
?>
