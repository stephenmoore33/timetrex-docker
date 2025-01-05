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
 * @package Modules\Holiday
 */
class HolidayFactory extends Factory {
	protected $table = 'holidays';
	protected $pk_sequence_name = 'holidays_id_seq'; //PK Sequence name

	protected $holiday_policy_obj = null;

	function _getSchemadata( ?array $filter = null ): ?TTS {
		$schema_data = new TTS( $this );

		if ( empty( $filter ) || in_array( 'database', $filter, true ) ) {
			$schema_data->setColumns(
					TTSCols::new(
							TTSCol::new( 'id' )->setFunctionMap( 'ID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'holiday_policy_id' )->setFunctionMap( 'HolidayPolicyID' )->setType( 'uuid' )->setIsNull( false ),
							TTSCol::new( 'date_stamp' )->setFunctionMap( 'DateStamp' )->setType( 'date' )->setIsNull( false ),
							TTSCol::new( 'name' )->setFunctionMap( 'Name' )->setType( 'varchar' )->setIsNull( false ),
					)->addCreatedAndUpdated()->addDeleted()
			);
		}

		if ( empty( $filter ) || in_array( 'fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {
			//No UI Fields.
		}

		if ( empty( $filter ) || in_array( 'search_fields', $filter, true ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setSearchFields(
					TTSSearchFields::new(
							TTSSearchField::new( 'id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),
							TTSSearchField::new( 'exclude_id' )->setType( 'uuid' )->setColumn( 'a.id' )->setMulti( true ),

							TTSSearchField::new( 'holiday_policy_id' )->setType( 'uuid' )->setColumn( 'a.holiday_policy_id' )->setMulti( true ),

							TTSSearchField::new( 'user_id' )->setType( 'uuid' )->setColumn( 'pguf.user_id' )->setMulti( true ),

							TTSSearchField::new( 'name' )->setType( 'text' )->setColumn( 'a.name' ),

							TTSSearchField::new( 'start_date' )->setType( 'date' )->setColumn( 'a.date_stamp' ),

							TTSSearchField::new( 'end_date' )->setType( 'date' )->setColumn( 'a.date_stamp' ),
					)
			);
		}

		if ( empty( $filter ) || in_array( 'api_methods', $filter, true ) ) {

			$schema_data->setAPIMethods(
					TTSAPIs::new(
							TTSAPI::new( 'APIHoliday' )->setMethod( 'getHoliday' )
									->setSummary( 'Get holiday records.' )
									->setArgs( [ 'data' => [ 'filter_data' => $schema_data->getSearchFields(), 'filter_columns' => $schema_data->getFields() ] ] )
									->setArgsModelDescription( 'Under the "data" arg there must be at least one or more "filter_data" elements specified, and one or more "filter_columns" elements which define the minimum necessary columns of data to return for carrying out other actions. For example if looking up the "id" of a record, just the "id" filter_columns needs to be specified with a value of true.' ),
							TTSAPI::new( 'APIHoliday' )->setMethod( 'setHoliday' )
									->setSummary( 'Add or edit holiday records. Will return the record UUID upon success, or a validation error if there is a problem.' )
									->setArgs( [ 'data' => $schema_data->getFields() ] ),
							TTSAPI::new( 'APIHoliday' )->setMethod( 'deleteHoliday' )
									->setSummary( 'Delete holiday records by passing in an array of UUIDs.' )
									->setArgs( new TTSFields(
													   TTSField::new( 'data' )->setType( 'multi-dropdown' )->setLabel( TTi18n::getText( 'IDs to Delete' ) )->setDataSource( TTSAPI::new( 'APIHoliday' )->setMethod( 'getHoliday' ) ),
											   ) ),
							TTSAPI::new( 'APIHoliday' )->setMethod( 'getHolidayDefaultData' )
									->setSummary( 'Get default holiday data used for creating new holidays. Use this before calling setHoliday to get the correct default data.' ),
					),
			);
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
			case 'columns':
				$retval = [
						'-1010-name'       => TTi18n::gettext( 'Name' ),
						'-1020-date_stamp' => TTi18n::gettext( 'Date' ),

						'-2000-created_by'   => TTi18n::gettext( 'Created By' ),
						'-2010-created_date' => TTi18n::gettext( 'Created Date' ),
						'-2020-updated_by'   => TTi18n::gettext( 'Updated By' ),
						'-2030-updated_date' => TTi18n::gettext( 'Updated Date' ),
				];
				break;
			case 'list_columns':
				$retval = Misc::arrayIntersectByKey( $this->getOptions( 'default_display_columns' ), Misc::trimSortPrefix( $this->getOptions( 'columns' ) ) );
				break;
			case 'default_display_columns': //Columns that are displayed by default.
				$retval = [
						'name',
						'date_stamp',
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
				'holiday_policy_id' => 'HolidayPolicyID',
				'date_stamp'        => 'DateStamp',
				'name'              => 'Name',
				'deleted'           => 'Deleted',
		];

		return $variable_function_map;
	}

	/**
	 * @return bool|HolidayPolicyFactory
	 */
	function getHolidayPolicyObject() {
		return $this->getGenericObject( 'HolidayPolicyListFactory', $this->getHolidayPolicyID(), 'holiday_policy_obj' );
	}

	/**
	 * @return bool|mixed
	 */
	function getHolidayPolicyID() {
		return $this->getGenericDataValue( 'holiday_policy_id' );
	}

	/**
	 * @param string $value UUID
	 * @return bool
	 */
	function setHolidayPolicyID( $value ) {
		$value = TTUUID::castUUID( $value );

		return $this->setGenericDataValue( 'holiday_policy_id', $value );
	}

	/**
	 * @param int $date_stamp EPOCH
	 * @return bool
	 */
	function isUniqueDateStamp( $date_stamp ) {
		$ph = [
				'policy_id'  => $this->getHolidayPolicyID(),
				'date_stamp' => $this->db->BindDate( $date_stamp ),
		];

		$query = 'select id from ' . $this->getTable() . '
					where holiday_policy_id = ?
						AND date_stamp = ?
						AND deleted=0';
		$date_stamp_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $date_stamp_id, 'Unique Date Stamp: ' . $date_stamp, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $date_stamp_id === false ) {
			return true;
		} else {
			if ( $date_stamp_id == $this->getId() ) {
				return true;
			}
		}

		return false;
	}

	/**
	 * @param bool $raw
	 * @return bool|int
	 */
	function getDateStamp( $raw = false ) {
		$value = $this->getGenericDataValue( 'date_stamp' );
		if ( $value !== false ) {
			if ( $raw === true ) {
				return $value;
			} else {
				if ( !is_numeric( $value ) ) {                                         //Optimization to avoid converting it when run in CalculatePolicy's loops
					$value = TTDate::getMiddleDayEpoch( TTDate::strtotime( $value ) ); //Make sure we use middle day epoch when pulling the value from the DB the first time, to match setDateStamp() below. Otherwise setting the datestamp then getting it again before save won't match the same value after its saved to the DB.
					$this->setGenericDataValue( 'date_stamp', $value );
				}

				return $value;
			}
		}

		return false;
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setDateStamp( $value ) {
		$value = ( !is_int( $value ) && $value !== null ) ? trim( $value ) : $value;//Dont trim integer values, as it changes them to strings.

		if ( $value > 0 ) {
			if ( $this->getDateStamp() !== $value && $this->getOldDateStamp() != $this->getDateStamp() ) {
				Debug::Text( ' Setting Old DateStamp... Current Old DateStamp: ' . (int)$this->getOldDateStamp() . ' Current DateStamp: ' . (int)$this->getDateStamp(), __FILE__, __LINE__, __METHOD__, 10 );
				$this->setOldDateStamp( $this->getDateStamp() );
			}
		}

		return $this->setGenericDataValue( 'date_stamp', TTDate::getMiddleDayEpoch( $value ) );
	}

	/**
	 * @return bool
	 */
	function getOldDateStamp() {
		return $this->getGenericTempDataValue( 'old_date_stamp' );
	}

	/**
	 * @param int $value EPOCH
	 * @return bool
	 */
	function setOldDateStamp( $value ) {
		Debug::Text( ' Setting Old DateStamp: ' . TTDate::getDate( 'DATE', $value ), __FILE__, __LINE__, __METHOD__, 10 );

		return $this->setGenericTempDataValue( 'old_date_stamp', TTDate::getMiddleDayEpoch( $value ) );
	}

	/**
	 * @param $name
	 * @return bool
	 */
	function isUniqueName( $name ) {
		//BindDate() causes a deprecated error if date_stamp is not set, so just return TRUE so we can throw a invalid date error elsewhere instead.
		//This also causes it so we can never have a invalid date and invalid name validation errors at the same time.
		if ( $this->getDateStamp() == '' ) {
			return true;
		}

		$name = trim( $name );
		if ( $name == '' ) {
			return false;
		}

		//When a holiday gets moved back/forward due to falling on weekend, it can throw off the check to see if the holiday
		//appears in the same year. For example new years 01-Jan-2011 gets moved to 31-Dec-2010, its in the same year
		//as the previous New Years day or 01-Jan-2010, so this check fails.
		//
		//I think this can only happen with New Years, or other holidays that fall within two days of the new year.
		//So exclude the first three days of the year to allow for weekend adjustments.
		$ph = [
				'policy_id'   => $this->getHolidayPolicyID(),
				'name'        => TTi18n::strtolower( $name ),
				'start_date1' => $this->db->BindDate( ( TTDate::getBeginYearEpoch( $this->getDateStamp() ) + ( 86400 * 3 ) ) ),
				'end_date1'   => $this->db->BindDate( TTDate::getEndYearEpoch( $this->getDateStamp() ) ),
				'start_date2' => $this->db->BindDate( ( $this->getDateStamp() - ( 86400 * 15 ) ) ),
				'end_date2'   => $this->db->BindDate( ( $this->getDateStamp() + ( 86400 * 15 ) ) ),
		];

		$query = 'select id from ' . $this->getTable() . '
					where holiday_policy_id = ?
						AND lower(name) = ?
						AND
							(
								(
								date_stamp >= ?
								AND date_stamp <= ?
								)
							OR
								(
								date_stamp >= ?
								AND date_stamp <= ?
								)
							)
						AND deleted=0';
		$name_id = $this->db->GetOne( $query, $ph );
		Debug::Arr( $name_id, 'Unique Name: ' . $name, __FILE__, __LINE__, __METHOD__, 10 );

		if ( $name_id === false ) {
			return true;
		} else {
			if ( $name_id == $this->getId() ) {
				return true;
			}
		}

		return false;
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
	 * ignore_after_eligibility is used when scheduling employees as absent on a holiday, since they haven't worked after the holiday
	 * when the schedule is created, it will always fail.
	 * @param string $user_id UUID
	 * @param bool $ignore_after_eligibility
	 * @return bool
	 */
	function isEligible( $user_id, $ignore_after_eligibility = false ) {
		if ( $user_id == '' ) {
			return false;
		}

		$original_time_zone = TTDate::getTimeZone(); //Store current timezone so we can return to it after.

		$ulf = TTnew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $user_id );
		if ( $ulf->getRecordCount() == 1 ) {
			$user_obj = $ulf->getCurrent();

			//Use CalculatePolicy to determine if they are eligible for the holiday or not.
			$flags = [
					'meal'                => false,
					'undertime_absence'   => false,
					'break'               => false,
					'holiday'             => true,
					'schedule_absence'    => false,
					'absence'             => false,
					'regular'             => false,
					'overtime'            => false,
					'premium'             => false,
					'accrual'             => false,
					'exception'           => false,

					//Exception options
					'exception_premature' => false, //Calculates premature exceptions
					'exception_future'    => false, //Calculates exceptions in the future.

					//Calculate policies for future dates.
					'future_dates'        => false, //Calculates dates in the future.
					'past_dates'          => false, //Calculates dates in the past. This is only needed when Pay Formulas that use averaging are enabled?*
			];
			$cp = TTNew( 'CalculatePolicy' ); /** @var CalculatePolicy $cp */
			$cp->setFlag( $flags );
			$cp->setUserObject( $user_obj );
			$cp->getRequiredData( $this->getDateStamp(), $this->getDateStamp() );

			$retval = $cp->isEligibleForHoliday( $this->getDateStamp(), $this->getHolidayPolicyObject(), $ignore_after_eligibility );

			TTDate::setTimeZone( $original_time_zone ); //Store current timezone so we can return to it after.

			return $retval;
		}

		Debug::text( 'ERROR: Unable to get user object...', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/**
	 * @param bool $ignore_warning
	 * @return bool
	 */
	function Validate( $ignore_warning = true ) {
		//
		// BELOW: Validation code moved from set*() functions.
		//
		// Holiday Policy
		$hplf = TTnew( 'HolidayPolicyListFactory' ); /** @var HolidayPolicyListFactory $hplf */
		$this->Validator->isResultSetWithRows( 'holiday_policy',
											   $hplf->getByID( $this->getHolidayPolicyID() ),
											   TTi18n::gettext( 'Holiday Policy is invalid' )
		);
		// Date stamp
		$this->Validator->isDate( 'date_stamp',
								  $this->getDateStamp(),
								  TTi18n::gettext( 'Incorrect date' )
		);
		if ( $this->Validator->isError( 'date_stamp' ) == false ) {
			$this->Validator->isTrue( 'date_stamp',
									  $this->isUniqueDateStamp( $this->getDateStamp() ),
									  TTi18n::gettext( 'Date is already in use by another Holiday' )
			);
		}

		// Name
		$this->Validator->isLength( 'name',
									$this->getName(),
									TTi18n::gettext( 'Name is invalid' ),
									2, 50
		);

		$this->Validator->isHTML( 'name',
								  $this->getName(),
								  TTi18n::gettext( 'Name contains invalid special characters' ),
		);

		if ( $this->Validator->isError( 'name' ) == false ) {
			$this->Validator->isTrue( 'name',
									  $this->isUniqueName( $this->getName() ),
									  TTi18n::gettext( 'Name is already in use in this year, or within 30 days' )
			);
		}


		//
		// ABOVE: Validation code moved from set*() functions.
		//
		if ( $this->Validator->hasError( 'date_stamp' ) == false && $this->getDateStamp() == '' ) {
			$this->Validator->isTrue( 'date_stamp',
									  false,
									  TTi18n::gettext( 'Date is invalid' ) );
		}


		return true;
	}

	/**
	 * @return bool
	 */
	function postSave() {
		//ReCalculate Recurring Schedule records based on this holiday, assuming its not older than a week.
		//   Since recurring schedules still exist for up to a week in the past typically, we still want to recalculate them if the holiday has already past,
		//   otherwise the schedule won't be updated and when the user tries to manually delete the scheduled absence shift, it will appear like its not being deleted because the recurring schedule will still show it.
		$cutoff_date = TTDate::incrementDate( TTDate::getMiddleDayEpoch( time() ), -1, 'week' );
		Debug::text( 'Cutoff Date: ' . TTDate::getDate( 'DATE', $cutoff_date ), __FILE__, __LINE__, __METHOD__, 10 );

		if ( TTDate::getMiddleDayEpoch( $this->getDateStamp() ) >= TTDate::getMiddleDayEpoch( $cutoff_date )
				|| ( $this->getOldDateStamp() != '' && TTDate::getMiddleDayEpoch( $this->getOldDateStamp() ) >= TTDate::getMiddleDayEpoch( $cutoff_date ) ) ) {
			Debug::text( 'Holiday is less than a week old, or in the future, try to recalculate recurring schedules on this date: ' . TTDate::getDate( 'DATE', $this->getDateStamp() ) . ' Old Date: ' . TTDate::getDate( 'DATE', $this->getOldDateStamp() ), __FILE__, __LINE__, __METHOD__, 10 );

			$date_ranges = [];
			if ( $this->getOldDateStamp() != '' && TTDate::getMiddleDayEpoch( $this->getDateStamp() ) != TTDate::getMiddleDayEpoch( $this->getOldDateStamp() ) ) {
				$date_ranges[] = [ 'start_date' => TTDate::getBeginDayEpoch( $this->getOldDateStamp() ), 'end_date' => TTDate::getEndDayEpoch( $this->getOldDateStamp() ) ];
			}

			$date_ranges[] = [ 'start_date' => TTDate::getBeginDayEpoch( $this->getDateStamp() ), 'end_date' => TTDate::getEndDayEpoch( $this->getDateStamp() ) ];

			$system_job_queue_batch_id = TTUUID::generateUUID();
			foreach ( $date_ranges as $date_range ) {
				$start_date = $date_range['start_date'];
				$end_date = $date_range['end_date'];
				Debug::text( 'Recalculating Recurring Schedules... Start Date: ' . TTDate::getDate( 'DATE', $start_date ) . ' End Date: ' . TTDate::getDate( 'DATE', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				//Get existing recurring_schedule rows on the holiday day, so we can figure out which recurring_schedule_control records to recalculate.
				$recurring_schedule_control_ids = [];

				$rslf = TTnew( 'RecurringScheduleListFactory' ); /** @var RecurringScheduleListFactory $rslf */
				$rslf->getByCompanyIDAndStartDateAndEndDateAndNoConflictingSchedule( $this->getHolidayPolicyObject()->getCompany(), $start_date, $end_date );
				Debug::text( 'Recurring Schedule Record Count: ' . $rslf->getRecordCount(), __FILE__, __LINE__, __METHOD__, 10 );
				if ( $rslf->getRecordCount() > 0 ) {
					foreach ( $rslf as $rs_obj ) {
						if ( TTUUID::isUUID( $rs_obj->getRecurringScheduleControl() ) && $rs_obj->getRecurringScheduleControl() != TTUUID::getZeroID() && $rs_obj->getRecurringScheduleControl() != TTUUID::getNotExistID() ) {
							$recurring_schedule_control_ids[] = $rs_obj->getRecurringScheduleControl();
						}
					}
				}
				$recurring_schedule_control_ids = array_unique( $recurring_schedule_control_ids );
				Debug::Arr( $recurring_schedule_control_ids, 'Recurring Schedule Control IDs: ', __FILE__, __LINE__, __METHOD__, 10 );

				if ( count( $recurring_schedule_control_ids ) > 0 ) {
					//
					//**THIS IS DONE IN recalculateRecurringScheduleForJobQueue, RecurringScheduleControlFactory, RecurringScheduleTemplateControlFactory, HolidayFactory postSave() as well.
					//
					global $config_vars;
					if ( ( !isset( $config_vars['other']['enable_job_queue'] ) || $config_vars['other']['enable_job_queue'] == true ) && ( !defined( 'UNIT_TEST_MODE' ) || UNIT_TEST_MODE === false ) ) {
						foreach( $recurring_schedule_control_ids as $recurring_schedule_control_id ) {
							SystemJobQueue::Add( TTi18n::getText( 'Recalculating Recurring Schedule' ), $system_job_queue_batch_id, 'RecurringScheduleFactory', 'recalculateRecurringScheduleForJobQueue', [ $this->getHolidayPolicyObject()->getCompany(), $recurring_schedule_control_id, null, $start_date, $end_date, false, true ], 90 );
						}
					} else {
						$rsf = TTnew( 'RecurringScheduleFactory' ); /** @var RecurringScheduleFactory $rsf */
						$rsf->StartTransaction();
						$rsf->clearRecurringSchedulesFromRecurringScheduleControl( $recurring_schedule_control_ids, $start_date, $end_date );
						$rsf->addRecurringSchedulesFromRecurringScheduleControl( $this->getHolidayPolicyObject()->getCompany(), $recurring_schedule_control_ids, $start_date, $end_date );
						$rsf->CommitTransaction();
					}
				}
			}
		} else {
			Debug::text( 'Holiday is older than a week or not in the future...', __FILE__, __LINE__, __METHOD__, 10 );
		}

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
						case 'date_stamp':
							if ( method_exists( $this, $function ) ) {
								$this->$function( TTDate::parseDateTime( $data[$key] ) );
							}
							break;
						default:
							if ( method_exists( $this, $function ) ) {
								$this->$function( $data[$key] );
							}
							break;
					}
				}
			}

			$this->setCreatedAndUpdatedColumns( $data );

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
						case 'date_stamp':
							if ( method_exists( $this, $function ) ) {
								$data[$variable] = TTDate::getAPIDate( 'DATE', $this->$function() );
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
			$this->getCreatedAndUpdatedColumns( $data, $include_columns );
		}

		return $data;
	}

	/**
	 * @param $log_action
	 * @return bool
	 */
	function addLog( $log_action ) {
		return TTLog::addEntry( $this->getId(), $log_action, TTi18n::getText( 'Holiday' ), null, $this->getTable(), $this );
	}

}

?>
