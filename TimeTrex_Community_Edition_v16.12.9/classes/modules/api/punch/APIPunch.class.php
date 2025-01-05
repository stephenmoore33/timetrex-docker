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


/*

	This class has to treat punch and punch_control data as if they are one.

*/

/**
 * @package API\Punch
 */
class APIPunch extends APIFactory {
	protected $main_class = 'PunchFactory';

	public $is_import = false;

	/**
	 * APIPunch constructor.
	 */
	public function __construct() {
		parent::__construct(); //Make sure parent constructor is always called.

		return true;
	}

	/**
	 * Get default punch data (ie: type, status, branch, department, job, task) prior to a user punching in/out. This also checks that the user is allowed to punch in/out from their station.
	 * @param string $user_id    UUID
	 * @param int $epoch         EPOCH
	 * @param string $station_id UUID
	 * @return array|bool
	 */
	function getUserPunch( $user_id = null, $epoch = null, $station_id = null ) {
		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		if ( TTUUID::isUUID( $user_id ) == false ) {
			$user_id = $this->getCurrentUserObject()->getId();
		}

		if ( $station_id == null ) {      //This is not a UUID, but the public station_id typically from the browser cookie.
			$station_id = getStationID(); //getStationID() from includes/API.inc.php
		}

		//Must call APIStation->getCurrentStation( $station_id = NULL ) first, so the Station ID cookie can be set and passed to this.
		//Check if station is allowed.
		$current_station = false;
		$slf = new StationListFactory();
		$slf->getByStationIdandCompanyId( $station_id, $this->getCurrentCompanyObject()->getId() );
		if ( $slf->getRecordCount() == 1 ) {
			$current_station = $slf->getCurrent();
			$station_type = $current_station->getType();
		}
		unset( $slf );

		Debug::Text( 'Station ID: ' . $station_id . ' User ID: ' . $user_id . ' Epoch: ' . $epoch, __FILE__, __LINE__, __METHOD__, 10 );
		if ( is_object( $current_station ) && $current_station->checkAllowed( $user_id, $station_id, $station_type, false ) == true ) { //Update the Station allowed_date/audit record when saving the punch instead. Since saving punches can now be queued, it postpones writing to the database until the last possible moment under heavy load.
			Debug::Text( 'Station Allowed! ID: ' . $current_station->getId() . ' (' . $station_id . ') User ID: ' . $user_id . ' Epoch: ' . $epoch, __FILE__, __LINE__, __METHOD__, 10 );

			//Check for punches pending in the job queue and reject new punches until they are fully processed.
			//  Even though we have client side checks on the job queue to prevent punches, we should leave this here because a queued punch could get stuck, then a real-time punch comes in before it and has potential to cause all kinds of problems.
			//  It should also help if the user opens a 2nd browser window and tries to punch there instead.
			//  However limit this check to only the last 2hrs perhaps, in case the job queue gets stuck for some reason.
			$sjqlf = TTnew('SystemJobQueueListFactory');
			$sjqlf->getByCompanyIdAndUserIdAndStatusAndClassAndMethodAndEffectiveDate( $this->getCurrentCompanyObject()->getId(), $user_id, [ 10, 20 ], 'PunchFactory', 'setUserPunchForJobQueue', ( time() - 7200 ), 1 ); //10=Pending, 20=Running
			Debug::Text( 'Job Queue Record Count: ' . $sjqlf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
			if ( $sjqlf->getRecordCount() > 0 ) {
				Debug::Text( 'Job Queue has pending punch!', __FILE__, __LINE__, __METHOD__, 10 );
				$validator_obj = new Validator();
				$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];

				$error_message = TTi18n::gettext( 'Punch is already being processed in the background, please wait for it to complete before trying again...' );
				$validator_obj->isTrue( 'user_name', false, $error_message );
				$validator[0] = $validator_obj->getErrorsArray();

				return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
			} else {
				//Get user object from ID.
				$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
				$ulf->getByIdAndCompanyId( $user_id, $this->getCurrentCompanyObject()->getId() );
				if ( $ulf->getRecordCount() == 1 ) {
					$user_obj = $ulf->getCurrent();

					$plf = TTNew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
					$data = $plf->getDefaultPunchSettings( $user_obj, $epoch, $current_station, $this->getPermissionObject() );
					$data['date_stamp'] = TTDate::getAPIDate( 'DATE', $epoch );
					$data['time_stamp'] = TTDate::getAPIDate( 'DATE+TIME', $epoch );
					$data['punch_date'] = TTDate::getAPIDate( 'DATE', $epoch );
					$data['punch_time'] = TTDate::getAPIDate( 'TIME', $epoch );
					$data['original_time_stamp'] = TTDate::getAPIDate( 'DATE+TIME', $epoch );
					$data['actual_time_stamp'] = TTDate::getAPIDate( 'DATE+TIME', $epoch );
					$data['epoch'] = $epoch;
					$data['first_name'] = $user_obj->getFirstName();
					$data['last_name'] = $user_obj->getLastName();
					$data['station_id'] = $current_station->getId();

					$data = $plf->getCustomFieldsDefaultData( $this->getCurrentCompanyObject()->getId(), $data );

					if ( isset( $data ) ) {
						//Debug::Arr( $data, 'Punch Data: ', __FILE__, __LINE__, __METHOD__, 10 );
						return $this->returnHandler( $data );
					}
				}
			}
			unset( $sjqlf );
		} else {
			Debug::Text( 'Station IS NOT Allowed! ID: ' . $station_id . ' User ID: ' . $user_id . ' Epoch: ' . $epoch, __FILE__, __LINE__, __METHOD__, 10 );
			$validator_obj = new Validator();
			$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];

			$error_message = TTi18n::gettext( 'You are not authorized to punch in or out from this station!' );
			$validator_obj->isTrue( 'user_name', false, $error_message );
			$validator[0] = $validator_obj->getErrorsArray();

			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'INVALID DATA' ), $validator, $validator_stats );
		}

		return false;
	}

	/**
	 * @param $data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setUserPunch( $data, $validate_only = false, $ignore_warning = true ) {
		if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'punch', 'punch_in_out' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Force proper settings.
		$data['user_id'] = $this->getCurrentUserObject()->getId();
		if ( isset( $data['transfer'] ) && $data['transfer'] == true ) {
			$data['type_id'] = 10;
			$data['status_id'] = 10;
		}

		$tmp_epoch = TTDate::getTime();

		//Ensure that $data['epoch'] is the master field for the timestamp.
		// However the 'time_stamp' field must always be defined as well, since that actually triggers PunchFactory::setTimeStamp() to be called and use the 'epoch' value in PunchFactory::setObjectFromArray().
		if ( ( !isset( $data['epoch'] ) || empty( $data['epoch'] ) ) && ( isset( $data['time_stamp'] ) && !empty( $data['time_stamp'] ) ) ) {
			$data['epoch'] = TTDate::parseDateTime( $data['time_stamp'] );
		} else if ( ( !isset( $data['epoch'] ) || empty( $data['epoch'] ) ) && ( !isset( $data['time_stamp'] ) || !empty( $data['time_stamp'] ) ) ) { //Neither 'epoch' or 'time_stamp' are specified.
			$data['epoch'] = $tmp_epoch;
			$data['time_stamp'] = TTDate::getDate( 'DATE+TIME', $data['epoch'] );
		} else { //'epoch' and 'time_stamp' are specified, make sure the 'time_stamp' is assigned from the 'epoch' since epoch always contains seconds and shouldn't have timezone issues. 'epoch' will be used in PunchFactory::setObjectFromArray() anyways.
			$data['time_stamp'] = TTDate::getDate( 'DATE+TIME', $data['epoch'] );
		}

		//Make sure employees don't try to circumvent the disabled timestamp field. By allowing a small variance.
		//This also prevents them from leaving the punch window open by accident, then submitting an old punch time.
		$max_variance = 300; //5minutes.
		if ( $data['epoch'] > ( $tmp_epoch + $max_variance ) || $data['epoch'] < ( $tmp_epoch - $max_variance ) ) {
			Debug::Text( 'Punch timestamp outside max variance: ' . TTDate::getDate( 'DATE+TIME', $data['epoch'] ) .' Using Current Time instead: '. TTDate::getDate( 'DATE+TIME', $tmp_epoch ), __FILE__, __LINE__, __METHOD__, 10 );
			$data['epoch'] = $tmp_epoch;
		}
		unset( $tmp_epoch, $data['punch_date'], $data['punch_time'], $data['actual_time_stamp'], $data['original_time_stamp'] ); //Only accept full time_stamp field, ignore punch_date/punch_time. This also helps prevent circumvention by the user.

		$data['_server_remote_addr'] = Misc::getRemoteIPAddress(); //Since we check $station_obj->checkAllowed() in the job queue now, we need to know the remote IP address that was also used at the time the punch was saved.

		$validator_stats = [ 'total_records' => 1, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;

		//Check if we are nearing to our max system load and start queuing punches at that point, so we can hopefully avoid reaching the max load.
		global $config_vars;
		if ( $validate_only == false && ( !isset( $config_vars['other']['enable_job_queue'] ) || $config_vars['other']['enable_job_queue'] == true ) && ( ( isset( $config_vars['other']['force_punch_queue'] ) && $config_vars['other']['force_punch_queue'] == true ) || Misc::isSystemLoadValid( 0.33 ) == false ) ) {
			SystemJobQueue::Add( TTi18n::getText( 'Saving Punch' ). ' @ '. TTDate::getDate('TIME', $data['epoch'] ), null, 'PunchFactory', 'setUserPunchForJobQueue', [ $data, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key ], 9 );

			//Because the whole point of queuing real-time punches is to reduce the immediate load on the server, don't make the user wait for it to save.
			return $this->returnHandler( true, true, false, false, false, false, false, true ); //Single valid record
		} else {
			[ $validator, $validator_stats, $key, $save_result ] = $this->getMainClassObject()->setUserPunch( $data, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key );
			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}
	}

	/**
	 * Get default punch data for creating new punches.
	 * @param string $user_id           UUID
	 * @param int $date                 EPOCH
	 * @param string $punch_control_id  UUID
	 * @param string $previous_punch_id UUID
	 * @param int $status_id            ID
	 * @param int $type_id              ID
	 * @return array
	 */
	function getPunchDefaultData( $user_id = null, $date = null, $punch_control_id = null, $previous_punch_id = null, $status_id = null, $type_id = null ) {
		$company_obj = $this->getCurrentCompanyObject();

		if ( TTUUID::isUUID( $user_id ) == false ) {
			$user_id = $this->getCurrentUserObject()->getId();
		}

		$date = TTDate::parseDateTime( $date );
		Debug::Text( 'Getting punch default data... User ID: ' . $user_id . ' Date: ' . TTDate::getDate( 'DATE', $date ) . ' Punch Control ID: ' . $punch_control_id . ' Previous Punch Id: ' . $previous_punch_id . ' Status ID: ' . $status_id . ' Type ID: ' . $type_id, __FILE__, __LINE__, __METHOD__, 10 );

		$data = [
				'status_id'     => ( $status_id != '' ) ? $status_id : 10,
				'type_id'       => ( $type_id != '' ) ? $type_id : 10,
				'user_id'       => $this->getCurrentUserObject()->getId(),
				'punch_time'    => TTDate::strtotime( '12:00 PM' ),
				'branch_id'     => $this->getCurrentUserObject()->getDefaultBranch(),
				'department_id' => $this->getCurrentUserObject()->getDefaultDepartment(),
				'job_id'        => $this->getCurrentUserObject()->getDefaultJob(),
				'job_item_id'   => $this->getCurrentUserObject()->getDefaultJobItem(),
				'punch_tag_id'  => $this->getCurrentUserObject()->getDefaultPunchTag()
		];

		$pc_obj = TTnew( 'PunchControlFactory' ); /** @var PunchControlFactory $pc_obj */
		$data = $pc_obj->getCustomFieldsDefaultData( $this->getCurrentCompanyObject()->getId(), $data );

		//If user_id is specified, use their default branch/department.
		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getByIdAndCompanyId( $user_id, $company_obj->getID() );
		if ( $ulf->getRecordCount() == 1 ) {
			$user_obj = $ulf->getCurrent();

			$data['user_id'] = $user_obj->getID();
			$data['branch_id'] = $user_obj->getDefaultBranch();
			$data['department_id'] = $user_obj->getDefaultDepartment();
			$data['job_id'] = $user_obj->getDefaultJob();
			$data['job_item_id'] = $user_obj->getDefaultJobItem();
			$data['punch_tag_id'] = $user_obj->getDefaultPunchTag();
		}
		unset( $ulf, $user_obj );

		if ( TTUUID::isUUID( $punch_control_id ) && $punch_control_id != TTUUID::getZeroID() && $punch_control_id != TTUUID::getNotExistID() ) {
			$pclf = TTnew( 'PunchControlListFactory' ); /** @var PunchControlListFactory $pclf */
			$pclf->getByIDAndCompanyID( $punch_control_id, $company_obj->getId() );
			if ( $pclf->getRecordCount() == 1 ) {
				$prev_punch_control_obj = $pclf->getCurrent();

				$data = array_merge( $data, (array)$prev_punch_control_obj->getObjectAsArray( [ 'branch_id' => true, 'department_id' => true, 'job_id' => true, 'job_item_id' => true, 'punch_tag_id' => true, 'quantity' => true, 'bad_quantity' => true, 'note' => true, 'custom_field' => true ] ) );
			}
			unset( $pclf, $prev_punch_control_obj );
		}

		//Attempt to determine the most common punch information so we can default new punches to that.
		$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
		$most_common_data = $plf->getMostCommonPunchDataByCompanyIdAndUserAndTypeAndStatusAndStartDateAndEndDate( $company_obj->getId(), $user_id, $type_id, $status_id, TTDate::getBeginWeekEpoch( $date ), TTDate::getEndWeekEpoch( $date ), true );
		if ( count( (array)$most_common_data ) == 0 ) { //Extend the date range to find some value.
			Debug::Text( 'No punches to get default default from, extend range back one more week...', __FILE__, __LINE__, __METHOD__, 10 );
			$most_common_data = $plf->getMostCommonPunchDataByCompanyIdAndUserAndTypeAndStatusAndStartDateAndEndDate( $company_obj->getId(), $user_id, $type_id, $status_id, TTDate::getBeginWeekEpoch( ( TTDate::getMiddleDayEpoch( $date ) - ( 86400 * 7 ) ) ), TTDate::getEndWeekEpoch( $date ), true );
		}
		if ( isset( $most_common_data['time_stamp'] ) ) {
			$data['punch_time'] = TTDate::getTimeLockedDate( TTDate::strtotime( $most_common_data['time_stamp'] ), $date );
			Debug::Text( '  Common Data... Time: ' . TTDate::getDATE( 'DATE+TIME', $data['punch_time'] ) . '(' . $data['punch_time'] . ')', __FILE__, __LINE__, __METHOD__, 10 );
		}

		//IF specified, get the previous punch object to determine the next punch type/status.
		if ( TTUUID::isUUID( $previous_punch_id ) && $previous_punch_id != TTUUID::getZeroID() && $previous_punch_id != TTUUID::getNotExistID() ) {
			$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
			$plf->getByCompanyIDAndId( $company_obj->getId(), $previous_punch_id );
			if ( $plf->getRecordCount() == 1 ) {
				$prev_punch_obj = $plf->getCurrent();
				Debug::Text( '  Getting previous punch data... Type ID: ' . $prev_punch_obj->getType() . ' Time: ' . TTDate::getDATE( 'DATE+TIME', $prev_punch_obj->getTimeStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

				if ( $type_id == '' ) {
					$data['type_id'] = $prev_punch_obj->getNextType();
					//$data['status_id'] = $prev_punch_obj->getNextStatus(); //JS handles this.
				}

				Debug::Arr( [ $data['punch_time'], TTDate::strtotime( $prev_punch_obj->getTimeStamp() ) ], '  Final punch default data... Type ID: ' . $data['type_id'] . ' Time: ' . TTDate::getDATE( 'DATE+TIME', $data['punch_time'] ), __FILE__, __LINE__, __METHOD__, 10 );

				if ( $data['punch_time'] < TTDate::strtotime( $prev_punch_obj->getTimeStamp() ) ) {
					$data['punch_time'] = TTDate::strtotime( $prev_punch_obj->getTimeStamp() );
				}

				Debug::Text( '  Final punch default data... Type ID: ' . $data['type_id'] . ' Status ID: '. $data['status_id'] .' Time: ' . TTDate::getDATE( 'DATE+TIME', $data['punch_time'] ), __FILE__, __LINE__, __METHOD__, 10 );
			}
			unset( $plf, $prev_punch_obj );
		}

		$data['punch_time'] = TTDate::getAPIDate( 'TIME', $data['punch_time'] ); //Convert epoch to human readable time.

		return $this->returnHandler( $data );
	}

	/**
	 * Get default punch data for creating new punches.
	 * @param string $user_id           UUID
	 * @param int $date                 EPOCH
	 * @param string $punch_control_id  UUID
	 * @param string $previous_punch_id UUID
	 * @param int $status_id
	 * @param int $type_id
	 * @param string $current_punch_id  UUID
	 * @return array
	 */
	function getRequestDefaultData( $user_id = null, $date = null, $punch_control_id = null, $previous_punch_id = null, $status_id = 10, $type_id = 10, $current_punch_id = null ) {
		if ( TTUUID::isUUID( $user_id ) == false ) {
			$user_id = $this->getCurrentUserObject()->getId();
		}

		$message = '';

		$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
		if ( isset( $current_punch_id ) ) {
			$request_type_id = 20;
			$pf_arr = $this->stripReturnHandler( $this->getPunch( [ 'filter_data' => [ 'user_id' => $user_id, 'id' => $current_punch_id ] ], true ) );
			if ( !is_array( $pf_arr ) || count( $pf_arr ) == 0 ) {
				return [ 'message' => TTi18n::getText( 'Due to [specify reason here], please correct the [Normal/Lunch/Break] [In/Out] punch at [HH:MM AM/PM] to be a [Normal/Lunch/Break] [In/Out] punch at [HH:MM AM/PM] instead.' ) ];
			}
			$pf = TTnew( 'PunchFactory' ); /** @var PunchFactory $pf */
			$pf->setObjectFromArray( $pf_arr[0] );

			$current_punch_time_text = TTDate::getDATE( 'TIME', $pf->getTimeStamp() );

			$type_text = Option::getByKey( $type_id, $plf->getOptions( 'type' ) );
			$status_text = Option::getByKey( $status_id, $plf->getOptions( 'status' ) );
			$message = TTi18n::getText( 'Due to [specify reason here], please correct the %1 %2 punch at %3 to be a %1 %2 punch at [HH:MM AM/PM] instead.', [ $type_text, $status_text, $current_punch_time_text ] );
		} else {
			$request_type_id = 10;
			//Missing punch
			$punch_data = $this->getPunchDefaultData( $user_id, $date, $punch_control_id, $previous_punch_id, $status_id, $type_id );
			if ( isset( $punch_data['api_retval'] ) ) {
				$type_text = Option::getByKey( $punch_data['api_retval']['type_id'], $plf->getOptions( 'type' ) );
				$status_text = Option::getByKey( $punch_data['api_retval']['status_id'], $plf->getOptions( 'status' ) );

				$message = TTi18n::getText( 'Due to [specify reason here], please add the missing %1 %2 punch at %3', [ $type_text, $status_text, $punch_data['api_retval']['punch_time'] ] );
			}
		}

		$data = [
				'date_stamp' => TTDate::getDATE( 'DATE', $date ),
				//'status_id' => $status_id, sets the wrong status if set.
				'type_id'    => $request_type_id,
				'user_id'    => $this->getCurrentUserObject()->getId(),
				'message'    => $message,
		];

		return $this->returnHandler( $data );
	}

	/**
	 * Get all necessary dates for building the TimeSheet in a single call, this is mainly as a performance optimization.
	 * @param int $base_date EPOCH
	 * @return array
	 * @internal param array $data filter data
	 */
	function getTimeSheetDates( $base_date ) {
		$epoch = TTDate::parseDateTime( $base_date );

		if ( $epoch == '' ) {
			$epoch = TTDate::getTime();
		}

		$start_date = TTDate::getBeginWeekEpoch( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
		$end_date = TTDate::getEndWeekEpoch( $epoch, $this->getCurrentUserPreferenceObject()->getStartWeekDay() );

		$retarr = [
				'base_date'          => $epoch,
				'start_date'         => $start_date,
				'end_date'           => $end_date,
				'base_display_date'  => TTDate::getAPIDate( 'DATE', $epoch ),
				'start_display_date' => TTDate::getAPIDate( 'DATE', $start_date ),
				'end_display_date'   => TTDate::getAPIDate( 'DATE', $end_date ),
		];

		return $retarr;
	}

	/**
	 * Get punch data for one or more punches.
	 * @param array $data filter data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getPunch( $data = null, $disable_paging = false ) {
		$data = $this->initializeFilterAndPager( $data, $disable_paging );

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'punch', 'view' ) || $this->getPermissionObject()->Check( 'punch', 'view_own' ) || $this->getPermissionObject()->Check( 'punch', 'view_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		$data['filter_data']['permission_children_ids'] = $this->getPermissionObject()->getPermissionChildren( 'punch', 'view' );

		//As a performance optimization to prevent the API from having to do additional date lookups, accept a single "date" field, that converts
		//into start/end dates.
		if ( isset( $data['filter_data']['date'] ) && $data['filter_data']['date'] != '' ) {
			$data['filter_data']['start_date'] = TTDate::getBeginWeekEpoch( $data['filter_data']['date'], $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
			$data['filter_data']['end_date'] = TTDate::getEndWeekEpoch( $data['filter_data']['date'], $this->getCurrentUserPreferenceObject()->getStartWeekDay() );
		}

		//No filter data, restrict to last pay period as a performance optimization when hundreds of thousands of punches exist.
		//The issue with this though is that the API doesn't know what the filter criteria is, so it can't display this to the user.
		//Make sure we don't apply a pay_period filter if we are looking up just one punch.
		if ( !isset( $data['filter_data']['id'] ) && !isset( $data['filter_data']['pay_period_ids'] ) && !isset( $data['filter_data']['pay_period_id'] ) && ( !isset( $data['filter_data']['start_date'] ) && !isset( $data['filter_data']['end_date'] ) ) && ( !isset( $data['filter_data']['updated_date'] ) ) ) {
			Debug::Text( 'Adding default filter data...', __FILE__, __LINE__, __METHOD__, 10 );
			$pplf = TTnew( 'PayPeriodListFactory' ); /** @var PayPeriodListFactory $pplf */
			$pplf->getByCompanyIdAndEndDate( $this->getCurrentCompanyObject()->getId(), time() ); //Use pay periods that are todays date falls within. Don't limit it to anything as they could have multiple pay period schedules.
			if ( $pplf->getRecordCount() > 0 ) {
				$data['filter_data']['pay_period_ids'] = (array)$pplf->getIDSByListFactory( $pplf );
			} else {
				//Get most recent two pay periods if there isn't a pay period that covers todays date.
				$pplf->getByCompanyId( $this->getCurrentCompanyObject()->getId(), 2 ); //Limit=2
				$pay_period_ids = array_keys( (array)$pplf->getArrayByListFactory( $pplf, false, false ) );
				if ( isset( $pay_period_ids[0] ) && isset( $pay_period_ids[1] ) ) {
					$data['filter_data']['pay_period_ids'] = [ $pay_period_ids[0], $pay_period_ids[1] ];
				}
				unset( $pay_period_ids );
			}
			unset( $pplf );
		}

		$plf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
		if ( DEPLOYMENT_ON_DEMAND == true ) {
			$plf->setQueryStatementTimeout( 60000 );
		}
		$plf->getAPISearchByCompanyIdAndArrayCriteria( $this->getCurrentCompanyObject()->getId(), $data['filter_data'], $data['filter_items_per_page'], $data['filter_page'], null, $data['filter_sort'] );
		Debug::Text( 'Record Count: ' . $plf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
		if ( $plf->getRecordCount() > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $plf->getRecordCount() );

			$this->setPagerObject( $plf );

			$retarr = [];
			foreach ( $plf as $p_obj ) {
				$retarr[] = $p_obj->getObjectAsArray( $data['filter_columns'], $data['filter_data']['permission_children_ids'] );

				$this->getProgressBarObject()->set( $this->getAPIMessageID(), $plf->getCurrentRow() );
			}

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			return $this->returnHandler( $retarr );
		}

		return $this->returnHandler( true ); //No records returned.
	}

	/**
	 * Get only the fields that are common across all records in the search criteria. Used for Mass Editing of records.
	 * @param array $data filter data
	 * @return array
	 */
	function getCommonPunchData( $data ) {
		return Misc::arrayIntersectByRow( $this->stripReturnHandler( $this->getPunch( $data, true ) ) );
	}

	/**
	 * Validate punch data for one or more punches.
	 * @param array $data punch data
	 * @return array
	 */
	function validatePunch( $data ) {
		return $this->setPunch( $data, true );
	}

	/**
	 * Set punch data for one or more punches.
	 * @param array $data punch data
	 * @param bool $validate_only
	 * @param bool $ignore_warning
	 * @return array|bool
	 */
	function setPunch( $data, $validate_only = false, $ignore_warning = true ) {
		$validate_only = (bool)$validate_only;
		$ignore_warning = (bool)$ignore_warning;

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'punch', 'edit' ) || $this->getPermissionObject()->Check( 'punch', 'edit_own' ) || $this->getPermissionObject()->Check( 'punch', 'edit_child' ) || $this->getPermissionObject()->Check( 'punch', 'add' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		if ( $this->getCurrentUserObject()->getStatus() != 10 ) { //10=Active -- Make sure user record is active as well.
			return $this->getPermissionObject()->PermissionDenied( false, TTi18n::getText( 'Employee status must be Active to modify punches' ) );
		}

		if ( $validate_only == true ) {
			Debug::Text( 'Validating Only!', __FILE__, __LINE__, __METHOD__, 10 );
			$permission_children_ids = false;
		} else {
			//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
			$permission_children_ids = $this->getPermissionChildren();
		}

		[ $data, $total_records ] = $this->convertToMultipleRecords( $data );
		Debug::Text( 'Received data for: ' . $total_records . ' Punchs', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		global $config_vars;
		if ( isset( $config_vars['other']['import_maximum_records_limit'] ) && $config_vars['other']['import_maximum_records_limit'] != '' ) {
			$maximum_record_limit = $config_vars['other']['import_maximum_records_limit'];
		} else {
			if ( DEPLOYMENT_ON_DEMAND == true ) {
				$maximum_record_limit = 1000;
			} else {
				$maximum_record_limit = 0;
			}
		}
		if ( $this->getCurrentCompanyObject()->getProductEdition() == 10 ) {
			$maximum_record_limit = 50;
		}
		if ( $maximum_record_limit > 0 && $total_records > $maximum_record_limit ) {
			Debug::Text( 'Maximum Records allowed for Import: ' . $maximum_record_limit . ' Validate Only: ' . (int)$validate_only, __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Maximum records per batch of %1 exceeded. Please reduce the number of records and try again.', [ $maximum_record_limit ] ) );
		}

		if ( $total_records > 4 && Misc::isSystemLoadValid() == false ) { //Check system load before allow any batch operations. Must continue to allow editing of a single record though.
			Debug::Text( 'ERROR: System load exceeded, preventing batch processes from starting...', __FILE__, __LINE__, __METHOD__, 10 );
			return $this->returnHandler( false, 'VALIDATION', TTi18n::getText( 'Please try again later, or reduce the number of records in your batch...' ) );
		}

		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			$transaction_function = function () use ( $data, $validate_only, $ignore_warning, $validator_stats, $validator, $save_result, $key, $total_records, $permission_children_ids ) {
				$lf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $lf */
				if ( $validate_only == false ) {                  //Only switch into serializable mode when actually saving the record.
					$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing incorrect calculations in user_date_total table.
				}
				$lf->StartTransaction(); //Wrap the entire batch of records in an array because we do lazy CalculatePolicy at the end. However during import, if one record fails, all records are rolled back.

				$recalculate_user_date_stamp = [];
				foreach ( $data as $key => $row ) {
					$primary_validator = new Validator();
					$lf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $lf */
					//$lf->StartTransaction();
					if ( isset( $row['id'] ) && $row['id'] != '' ) {
						//Modifying existing object.
						//Get punch object, so we can only modify just changed data for specific records if needed.
						//Use the special getAPIByIdAndCompanyId() function as it returns additional columns needed for mass editing.
						//These additional columns break editing a single record if we make $lf the current object.
						$lf->getAPIByIdAndCompanyId( $row['id'], $this->getCurrentCompanyObject()->getId() );
						if ( $lf->getRecordCount() == 1 ) {
							//Object exists, check edit permissions
							if (
									$validate_only == true
									||
									(
											$this->getPermissionObject()->Check( 'punch', 'edit' )
											|| ( $this->getPermissionObject()->Check( 'punch', 'edit_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getPunchControlObject()->getUser() ) === true )
											|| ( $this->getPermissionObject()->Check( 'punch', 'edit_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getPunchControlObject()->getUser(), $permission_children_ids ) === true )
									) ) {

								Debug::Text( 'Row Exists, getting current data for ID: ' . $row['id'], __FILE__, __LINE__, __METHOD__, 10 );
								//If we make the current object be $lf, it fails saving the punch because extra columns exist.
								//$lf = $lf->getCurrent();
								//$row = array_merge( $lf->getObjectAsArray( array('id' => TRUE, 'user_id' => TRUE, 'transfer' => TRUE, 'type_id' => TRUE, 'status_id' => TRUE, 'time_stamp' => TRUE, 'punch_control_id' => TRUE, 'actual_time_stamp' => TRUE, 'original_time_stamp' => TRUE, 'schedule_id' => TRUE, 'station_id' => TRUE, 'longitude' => TRUE, 'latitude' => TRUE, 'deleted' => TRUE) ), $row );
								$lf = $lf->getCurrent(); //Make the current $lf variable the current object, otherwise getDataDifferences() fails to function.
								$row = array_merge( $lf->getObjectAsArray(), $row );
							} else {
								$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Edit permission denied' ) );
							}
						} else {
							//Object doesn't exist.
							$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Edit permission denied, record does not exist' ) );
						}
					} else {
						//Adding new object, check ADD permissions.
						if ( !( $validate_only == true
								||
								( $this->getPermissionObject()->Check( 'punch', 'add' )
										&&
										(
												$this->getPermissionObject()->Check( 'punch', 'edit' )
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'punch', 'edit_own' ) && $this->getPermissionObject()->isOwner( false, $row['user_id'] ) === true ) //We don't know the created_by of the user at this point, but only check if the user is assigned to the logged in person.
												|| ( isset( $row['user_id'] ) && $this->getPermissionObject()->Check( 'punch', 'edit_child' ) && $this->getPermissionObject()->isChild( $row['user_id'], $permission_children_ids ) === true )
										)
								)
						) ) {
							$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Add permission denied' ) );
						}
					}
					Debug::Arr( $row, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

					$is_valid = $pcf_valid = $primary_validator->isValid();
					if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
						Debug::Text( 'Setting object data...', __FILE__, __LINE__, __METHOD__, 10 );

						//If no punch control id is sent, make sure its blank so setObjectFromArray can try to automatically determine it.
						//Mainly for importing.
						if ( !isset( $row['punch_control_id'] ) ) {
							$row['punch_control_id'] = false;
						}

						//Try to automatically determine punch data, mainly for importing punches.
						if ( isset( $row['time_stamp'] ) && isset( $row['user_id'] ) && ( !isset( $row['status_id'] ) || ( isset( $row['status_id'] ) && ( $row['status_id'] == '' || $row['status_id'] == 0 ) ) ) ) {
							$plf = TTNew( 'PunchListFactory' ); /** @var PunchListFactory $plf */
							$plf->getPreviousPunchByUserIDAndEpoch( $row['user_id'], $row['time_stamp'] );
							if ( $plf->getRecordCount() > 0 ) {
								$prev_punch_obj = $plf->getCurrent();
								$row['status_id'] = $prev_punch_obj->getNextStatus();
								Debug::Text( 'Automatically determine status: ' . $row['status_id'], __FILE__, __LINE__, __METHOD__, 10 );
							} else {
								$row['status_id'] = 10; //In
							}
						}

						$lf->setObjectFromArray( $row );

						//When importing punches, make sure they aren't tainted immediately. We assume if the punches are imported the employee did them originally from some other device.
						//  The audit log will still show who imported the punch in the detailed log records.
						if ( $this->is_import == true ) {
							Debug::Text( 'Imported punch, forcing created/updated by to the punch user...', __FILE__, __LINE__, __METHOD__, 10 );
							$lf->setCreatedBy( $lf->getUser() );
							$lf->setUpdatedBy( $lf->getUser() );
						}

						$is_valid = $lf->isValid( $ignore_warning );
						if ( $is_valid == true ) {
							Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
							if ( $validate_only == true ) {
								$save_result[$key] = true;
								$validator_stats['valid_records']++;
							} else {
								//Setup PunchControl object before the punch object is saved, so PunchFactory can reference it for splitting at midnight.
								unset( $row['id'] ); //ID must be removed so it doesn't get confused with PunchControlID
								Debug::Text( 'Saving PCF data... Punch Control ID: ' . $lf->getPunchControlID(), __FILE__, __LINE__, __METHOD__, 10 );
								if ( is_object( $lf ) && is_object( $lf->getPunchControlObject() ) ) {
									$pcf = $lf->getPunchControlObject(); //Use getPunchControlObject() so we get the "old_data" and audit log can properly be handled. It should already be cached anyways, so there is no SQL query.
								} else {
									$pcf = TTnew( 'PunchControlFactory' ); /** @var PunchControlFactory $pcf */
								}

								$pcf->setObjectFromArray( $row );

								// In some cases the data may have changed enough that need to revalidate again here just in case.
								//   This would usually cause the following valdation error: In punches cannot occur twice in the same punch pair, you may want to make this an out punch instead
								$is_valid = $lf->isValid( $ignore_warning );
								if ( $is_valid == true ) {
									Debug::Text( 'Saving data...', __FILE__, __LINE__, __METHOD__, 10 );
									if ( $validate_only == true ) {
										$save_result[$key] = true;
										$validator_stats['valid_records']++;
									} else {
										//Save Punch object and start on PunchControl
										$save_result[$key] = $lf->Save( false );
										if ( $save_result[$key] == true ) {
											$pcf->setId( $lf->getPunchControlID() );
											$pcf->setPunchObject( $lf );

											//This is important when adding/editing a punch, without it there can be issues calculating exceptions
											//because if a specific punch was modified that caused the day to change, smartReCalculate
											//may only be able to recalculate a single day, instead of both.
											//  **This actually breaks the case where the In punch is changed to move the shift on different days. ie: Jan 27 @ 11PM -> Jan 28th at 1AM.
											//    Everything should be handled in PunchControlFactory->setDateStamp() instead.
//											$old_date_stamp = ( is_object( $lf->getPunchControlObject() ) ) ? $lf->getPunchControlObject()->getDateStamp() : 0;
//											if ( $old_date_stamp != 0 ) {
//												Debug::Text('Setting old date stamp to: '. TTDate::getDate('DATE', $old_date_stamp ), __FILE__, __LINE__, __METHOD__, 10);
//												$pcf->setOldDateStamp( $old_date_stamp );
//											}


											$pcf->setEnableStrictJobValidation( true );
											$pcf->setEnableCalcUserDateID( true );
											$pcf->setEnableCalcTotalTime( true );
											$pcf->setEnableCalcUserDateTotal( true );
											//Before batch calculation mode was enabled...
											//$pcf->setEnableCalcSystemTotalTime( TRUE );
											//$pcf->setEnableCalcWeeklySystemTotalTime( TRUE );
											//$pcf->setEnableCalcException( TRUE );
											$pcf->setEnableCalcSystemTotalTime( false );
											$pcf->setEnableCalcWeeklySystemTotalTime( false );
											$pcf->setEnableCalcException( false );

											if ( $pcf->isValid( $ignore_warning ) ) {
												$validator_stats['valid_records']++;
												if ( $pcf->Save( false, true ) != true ) { //Force isNew() lookup.
													$is_valid = $pcf_valid = false;
												} else {
													if ( isset( $pcf->old_date_stamps ) && is_array( $pcf->old_date_stamps ) ) {
														if ( !isset( $recalculate_user_date_stamp[$pcf->getUser()] ) ) {
															$recalculate_user_date_stamp[$pcf->getUser()] = [];
														}
														$recalculate_user_date_stamp[$pcf->getUser()] = array_merge( (array)$recalculate_user_date_stamp[$pcf->getUser()], (array)$pcf->old_date_stamps );
													}
													$recalculate_user_date_stamp[$pcf->getUser()][] = $pcf->getDateStamp();
												}
												unset( $pcf );
											} else {
												$is_valid = $pcf_valid = false;
											}
										}
										Debug::Text( 'Save Result ID: ' . (int)$save_result[$key], __FILE__, __LINE__, __METHOD__, 10 );
									}
								}
							}
						}
					}

					if ( $is_valid == false ) {
						Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

						$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

						$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf, ( ( isset( $pcf ) ) ? $pcf->Validator : false ) ], ( ( $total_records > 1 && is_object( $lf->getUserObject() ) ) ? $lf->getUserObject()->getFullName() : null ) );
					} else if ( $validate_only == true ) {
						//Always fail transaction when valididate only is used, as	is saved to different tables immediately.
						$lf->FailTransaction();
					}

					//$lf->CommitTransaction();

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				}

				if ( $is_valid == true && $validate_only == false ) {
					if ( is_array( $recalculate_user_date_stamp ) && count( $recalculate_user_date_stamp ) > 0 ) {
						Debug::Arr( $recalculate_user_date_stamp, 'Recalculating other dates...', __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $recalculate_user_date_stamp as $user_id => $date_arr ) {
							$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
							$ulf->getByIdAndCompanyId( $user_id, $this->getCurrentCompanyObject()->getId() );
							if ( $ulf->getRecordCount() > 0 ) {
								$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
								$cp->setUserObject( $ulf->getCurrent() );
								$cp->addPendingCalculationDate( $date_arr );
								try {
									if ( $cp->calculate( false, -1, 60 ) == true ) { //This sets timezone itself. -- 60s lock timeout, to try and avoid an error message being displayed to the user or API caller and requiriing a retry.
										$cp->Save();
									} else {
										$lf->FailTransaction();
									}
								} catch( Exception $e ) {
									$lf->FailTransaction();

									//Show friendly error to the user so they can retry again if needed.
									$primary_validator->isTrue( 'system', false, TTi18n::gettext( 'This employee\'s timesheet is currently being recalculated, please try again' ) );
									$validator[$key++] = $this->setValidationArray( [ $primary_validator ], ( ( $total_records > 1 && is_object( $lf->getUserObject() ) ) ? $lf->getUserObject()->getFullName() : null ) );
									$validator_stats['valid_records']--;
								}
							}
						}
					} else {
						Debug::Text( 'aNot recalculating batch...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( 'bNot recalculating batch...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$lf->CommitTransaction();
				$lf->setTransactionMode(); //Back to default isolation level.

				return [ $validator, $validator_stats, $key, $save_result ];
			};

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			if ( $total_records > 100 ) { //When importing, or mass adding punches, don't retry as the transaction will be much too large.
				$retry_max_attempts = 1;
			} else if ( $total_records > 20 ) { //When importing, or mass adding punches, don't retry as the transaction will be much too large.
				$retry_max_attempts = 2;
			} else {
				$retry_max_attempts = 3;
			}

			[ $validator, $validator_stats, $key, $save_result ] = $this->getMainClassObject()->RetryTransaction( $transaction_function, $retry_max_attempts );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * Delete one or more punchs.
	 * @param array $data punch data
	 * @return array|bool
	 */
	function deletePunch( $data ) {
		if ( !is_array( $data ) ) {
			$data = [ $data ];
		}

		if ( !is_array( $data ) ) {
			return $this->returnHandler( false );
		}

		if ( $this->getPermissionObject()->checkAuthenticationType( 700 ) == false ) { //700=HTTP Auth with username/password
			return $this->getPermissionObject()->AuthenticationTypeDenied();
		}

		if ( !$this->getPermissionObject()->Check( 'punch', 'enabled' )
				|| !( $this->getPermissionObject()->Check( 'punch', 'delete' ) || $this->getPermissionObject()->Check( 'punch', 'delete_own' ) || $this->getPermissionObject()->Check( 'punch', 'delete_child' ) ) ) {
			return $this->getPermissionObject()->PermissionDenied();
		}

		//Get Permission Hierarchy Children first, as this can be used for viewing, or editing.
		$permission_children_ids = $this->getPermissionChildren();

		Debug::Text( 'Received data for: ' . count( $data ) . ' Punchs', __FILE__, __LINE__, __METHOD__, 10 );
		Debug::Arr( $data, 'Data: ', __FILE__, __LINE__, __METHOD__, 10 );

		$total_records = count( $data );
		$validator_stats = [ 'total_records' => $total_records, 'valid_records' => 0 ];
		$validator = $save_result = []; $key = false;
		if ( is_array( $data ) && $total_records > 0 ) {
			$this->getProgressBarObject()->start( $this->getAPIMessageID(), $total_records );

			$transaction_function = function () use ( $data, $validator_stats, $validator, $save_result, $total_records, $permission_children_ids ) {
				$lf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $lf */
				$lf->setTransactionMode( 'REPEATABLE READ' ); //Required to help prevent duplicate simulataneous HTTP requests from causing incorrect calculations in user_date_total table.
				$lf->StartTransaction();

				$recalculate_user_date_stamp = [];
				foreach ( $data as $key => $id ) {
					$primary_validator = new Validator();
					$lf = TTnew( 'PunchListFactory' ); /** @var PunchListFactory $lf */
					//$lf->StartTransaction();
					if ( $id != '' ) {
						//Modifying existing object.
						//Get punch object, so we can only modify just changed data for specific records if needed.
						$lf->getByIdAndCompanyId( $id, $this->getCurrentCompanyObject()->getId() );
						if ( $lf->getRecordCount() == 1 ) {
							//Object exists, check edit permissions
							//NOTE: Make sure we pass the user the punch is assigned too for proper delete_child permissions to work correctly.
							if ( $this->getPermissionObject()->Check( 'punch', 'delete' )
									|| ( $this->getPermissionObject()->Check( 'punch', 'delete_own' ) && $this->getPermissionObject()->isOwner( $lf->getCurrent()->getCreatedBy(), $lf->getCurrent()->getPunchControlObject()->getUser() ) === true )
									|| ( $this->getPermissionObject()->Check( 'punch', 'delete_child' ) && $this->getPermissionObject()->isChild( $lf->getCurrent()->getPunchControlObject()->getUser(), $permission_children_ids ) === true ) ) {
								Debug::Text( 'Record Exists, deleting record: ' . $id, __FILE__, __LINE__, __METHOD__, 10 );
								$lf = $lf->getCurrent();

								$recalculate_user_date_stamp[$lf->getPunchControlObject()->getUser()][] = TTDate::getMiddleDayEpoch( $lf->getPunchControlObject()->getDateStamp() ); //Help avoid confusion with different timezones/DST.
							} else {
								$primary_validator->isTrue( 'permission', false, TTi18n::gettext( 'Delete permission denied' ) );
							}
						} else {
							//Object doesn't exist.
							$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
						}
					} else {
						$primary_validator->isTrue( 'id', false, TTi18n::gettext( 'Delete permission denied, record does not exist' ) );
					}

					//Debug::Arr($lf, 'AData: ', __FILE__, __LINE__, __METHOD__, 10);

					$is_valid = $primary_validator->isValid();
					if ( $is_valid == true ) { //Check to see if all permission checks passed before trying to save data.
						Debug::Text( 'Attempting to delete record...', __FILE__, __LINE__, __METHOD__, 10 );
						Debug::Arr( $lf->data, 'Current Data: ', __FILE__, __LINE__, __METHOD__, 10 );

						$lf->setUser( $lf->getPunchControlObject()->getUser() );
						$lf->setDeleted( true );

						$is_valid = $lf->isValid();
						if ( $is_valid == true ) {
							$lf->setEnableCalcTotalTime( true );
							$lf->setEnableCalcUserDateTotal( true );

							//Before batch calculation mode was enabled...
							//$lf->setEnableCalcSystemTotalTime( TRUE );
							//$lf->setEnableCalcWeeklySystemTotalTime( TRUE );
							//$lf->setEnableCalcException( TRUE );
							$lf->setEnableCalcSystemTotalTime( false );
							$lf->setEnableCalcWeeklySystemTotalTime( false );
							$lf->setEnableCalcException( false );

							Debug::Text( 'Record Deleted...', __FILE__, __LINE__, __METHOD__, 10 );
							$save_result[$key] = $lf->Save();
							$validator_stats['valid_records']++;
						}
					}

					if ( $is_valid == false ) {
						Debug::Text( 'Data is Invalid...', __FILE__, __LINE__, __METHOD__, 10 );

						$lf->FailTransaction(); //Just rollback this single record, continue on to the rest.

						$validator[$key] = $this->setValidationArray( [ $primary_validator, $lf, ( ( isset( $pcf ) ) ? $pcf->Validator : false ) ], ( ( $total_records > 1 && is_object( $lf->getUserObject() ) ) ? $lf->getUserObject()->getFullName() : null ) );
					}

					//$lf->CommitTransaction();

					$this->getProgressBarObject()->set( $this->getAPIMessageID(), $key );
				}

				if ( $is_valid == true ) {
					if ( is_array( $recalculate_user_date_stamp ) && count( $recalculate_user_date_stamp ) > 0 ) {
						Debug::Arr( $recalculate_user_date_stamp, 'Recalculating other dates...', __FILE__, __LINE__, __METHOD__, 10 );
						foreach ( $recalculate_user_date_stamp as $user_id => $date_arr ) {
							$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
							$ulf->getByIdAndCompanyId( $user_id, $this->getCurrentCompanyObject()->getId() );
							if ( $ulf->getRecordCount() > 0 ) {
								$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
								$cp->setUserObject( $ulf->getCurrent() );
								$cp->addPendingCalculationDate( $date_arr );
								$cp->calculate(); //This sets timezone itself.
								$cp->Save();
							}
						}
					} else {
						Debug::Text( 'aNot recalculating batch...', __FILE__, __LINE__, __METHOD__, 10 );
					}
				} else {
					Debug::Text( 'bNot recalculating batch...', __FILE__, __LINE__, __METHOD__, 10 );
				}

				$lf->CommitTransaction();
				$lf->setTransactionMode(); //Back to default isolation level.

				return [ $validator, $validator_stats, $key, $save_result ];
			};

			$this->getProgressBarObject()->stop( $this->getAPIMessageID() );

			[ $validator, $validator_stats, $key, $save_result ] = $this->getMainClassObject()->RetryTransaction( $transaction_function );

			return $this->handleRecordValidationResults( $validator, $validator_stats, $key, $save_result );
		}

		return $this->returnHandler( false );
	}

	/**
	 * @param $data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function getMealAndBreakTotalTime( $data, $disable_paging = false ) {
		return PunchFactory::calcMealAndBreakTotalTime( $this->getPunch( $data, true ) );
	}

	/**
	 * @param string $format
	 * @param array $data
	 * @param bool $disable_paging
	 * @return array|bool
	 */
	function exportPunch( $format = 'csv', $data = null, $disable_paging = true ) {
		$result = $this->stripReturnHandler( $this->getPunch( $data, $disable_paging ) );

		return $this->exportRecords( $format, 'export_punch', $result, ( ( isset( $data['filter_columns'] ) ) ? $data['filter_columns'] : null ) );
	}
}

?>
