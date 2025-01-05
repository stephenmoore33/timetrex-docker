<?php /** @noinspection PhpMissingDocCommentInspection */

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

class MealBreakPolicyTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $user_id = null;
	protected $branch_ids = [];
	protected $department_ids = [];
	protected $policy_ids = [];
	protected $pay_period_schedule_id = null;
	protected $pay_period_objs = null;

	public function setUp(): void {
		global $dd;
		Debug::text( 'Running setUp(): ', __FILE__, __LINE__, __METHOD__, 10 );

		TTDate::setTimeZone( 'America/Vancouver', true ); //Due to being a singleton and PHPUnit resetting the state, always force the timezone to be set.

		$dd = new DemoData();
		$dd->setEnableQuickPunch( false ); //Helps prevent duplicate punch IDs and validation failures.
		$dd->setUserNamePostFix( '_' . uniqid( '', true ) ); //Needs to be super random to prevent conflicts and random failing tests.
		$this->company_id = $dd->createCompany();
		$this->legal_entity_id = $dd->createLegalEntity( $this->company_id, 10 );
		Debug::text( 'Company ID: ' . $this->company_id, __FILE__, __LINE__, __METHOD__, 10 );

		//$dd->createPermissionGroups( $this->company_id, 40 ); //Administrator only.

		$dd->createCurrency( $this->company_id, 10 );

		$dd->createUserWageGroups( $this->company_id );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100 );

		$this->branch_ids[] = $dd->createBranch( $this->company_id, 10 );
		$this->branch_ids[] = $dd->createBranch( $this->company_id, 20 );

		$this->department_ids[] = $dd->createDepartment( $this->company_id, 10 );
		$this->department_ids[] = $dd->createDepartment( $this->company_id, 20 );

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$this->policy_ids['pay_formula_policy'][100] = $dd->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x

		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular
		$this->policy_ids['pay_code'][190] = $dd->createPayCode( $this->company_id, 190, $this->policy_ids['pay_formula_policy'][100] ); //Lunch
		$this->policy_ids['pay_code'][192] = $dd->createPayCode( $this->company_id, 192 ); //Break
		$this->policy_ids['pay_code'][300] = $dd->createPayCode( $this->company_id, 300 ); //Prem1
		$this->policy_ids['pay_code'][310] = $dd->createPayCode( $this->company_id, 310 ); //Prem2
		$this->policy_ids['pay_code'][900] = $dd->createPayCode( $this->company_id, 900 ); //Vacation
		$this->policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910 ); //Bank
		$this->policy_ids['pay_code'][920] = $dd->createPayCode( $this->company_id, 920 ); //Sick

		$this->policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $this->policy_ids['pay_code'][100] ] ); //Regular
		$this->policy_ids['contributing_pay_code_policy'][12] = $dd->createContributingPayCodePolicy( $this->company_id, 12, [ $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192] ] ); //Regular+Meal/Break
		$this->policy_ids['contributing_pay_code_policy'][14] = $dd->createContributingPayCodePolicy( $this->company_id, 14, [ $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192], $this->policy_ids['pay_code'][900] ] ); //Regular+Meal/Break+Absence
		$this->policy_ids['contributing_pay_code_policy'][90] = $dd->createContributingPayCodePolicy( $this->company_id, 90, [ $this->policy_ids['pay_code'][900] ] ); //Absence
		$this->policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $this->policy_ids['pay_code'] ); //All Time

		$this->policy_ids['contributing_shift_policy'][12] = $dd->createContributingShiftPolicy( $this->company_id, 10, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$this->policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['absence_policy'][10] = $dd->createAbsencePolicy( $this->company_id, 10, $this->policy_ids['pay_code'][900] ); //Vacation

		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );
		$this->assertTrue( TTUUID::isUUID( $this->user_id ) );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function createPayPeriodSchedule() {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		//$ppsf->setName( 'Bi-Weekly'.rand(1000,9999) );
		$ppsf->setName( 'Bi-Weekly' );
		$ppsf->setDescription( 'Pay every two weeks' );
		$ppsf->setType( 20 );
		$ppsf->setStartWeekDay( 0 );

		$anchor_date = TTDate::getBeginWeekEpoch( TTDate::incrementDate( time(), -42, 'day' ) ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( true );
		$ppsf->setTimeZone( 'America/Vancouver' );

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( ( 4 * 3600 ) );
		$ppsf->setMaximumShiftTime( ( 16 * 3600 ) );
		$ppsf->setShiftAssignedDay( 10 );

		$ppsf->setEnableInitialPayPeriods( false );
		if ( $ppsf->isValid() ) {
			$insert_id = $ppsf->Save( false );
			Debug::Text( 'Pay Period Schedule ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			$ppsf->setUser( [ $this->user_id ] );
			$ppsf->Save();

			$this->pay_period_schedule_id = $insert_id;

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Period Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPayPeriods() {
		$max_pay_periods = 35;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					$end_date = TTDate::getBeginWeekEpoch( TTDate::incrementDate( time(), -42, 'day' ) );
				} else {
					$end_date = TTDate::incrementDate( $end_date, 14, 'day' );
				}

				Debug::Text( 'I: ' . $i . ' End Date: ' . TTDate::getDate( 'DATE+TIME', $end_date ), __FILE__, __LINE__, __METHOD__, 10 );

				$pps_obj->createNextPayPeriod( $end_date, ( 86400 + 3600 ), false ); //Don't import punches, as that causes deadlocks when running tests in parallel.
			}
		}

		return true;
	}

	function getAllPayPeriods() {
		$pplf = new PayPeriodListFactory();
		//$pplf->getByCompanyId( $this->company_id );
		$pplf->getByPayPeriodScheduleId( $this->pay_period_schedule_id );
		if ( $pplf->getRecordCount() > 0 ) {
			foreach ( $pplf as $pp_obj ) {
				Debug::text( 'Pay Period... Start: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );

				$this->pay_period_objs[] = $pp_obj;
			}
		}

		$this->pay_period_objs = array_reverse( $this->pay_period_objs );

		return true;
	}

	function getCurrentPayPeriod( $epoch = null ) {
		if ( $epoch == '' ) {
			$epoch = time();
		}

		$this->getAllPayPeriods(); //This doesn't return the pay periods, just populates an array and returns TRUE.
		$pay_periods = $this->pay_period_objs;
		if ( is_array( $pay_periods ) ) {
			foreach ( $pay_periods as $pp_obj ) {
				if ( $pp_obj->getStartDate() <= $epoch && $pp_obj->getEndDate() >= $epoch ) {
					Debug::text( 'Current Pay Period... Start: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getStartDate() ) . ' End: ' . TTDate::getDate( 'DATE+TIME', $pp_obj->getEndDate() ), __FILE__, __LINE__, __METHOD__, 10 );

					return $pp_obj;
				}
			}
		}

		Debug::text( 'Current Pay Period not found! Epoch: ' . TTDate::getDate( 'DATE+TIME', $epoch ), __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function getUserDateTotalArray( $start_date, $end_date ) {
		$udtlf = new UserDateTotalListFactory();

		$date_totals = [];

		//Get only system totals.
		$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->company_id, $this->user_id, [ 5, 20, 25, 30, 40, 100, 110 ], $start_date, $end_date );
		if ( $udtlf->getRecordCount() > 0 ) {
			foreach ( $udtlf as $udt_obj ) {
				$date_totals[$udt_obj->getDateStamp()][] = [
						'date_stamp'    => $udt_obj->getDateStamp(),
						'id'            => $udt_obj->getId(),

						//Keep legacy status_id/type_id for now, so we don't have to change as many unit tests.
						'status_id'     => $udt_obj->getStatus(),
						'type_id'       => $udt_obj->getType(),
						'src_object_id' => $udt_obj->getSourceObject(),

						'object_type_id' => $udt_obj->getObjectType(),
						'pay_code_id'    => $udt_obj->getPayCode(),

						'branch_id'     => $udt_obj->getBranch(),
						'department_id' => $udt_obj->getDepartment(),
						'total_time'    => $udt_obj->getTotalTime(),
						'name'          => $udt_obj->getName(),

						'quantity'     => $udt_obj->getQuantity(),
						'bad_quantity' => $udt_obj->getBadQuantity(),

						'hourly_rate'  => $udt_obj->getHourlyRate(),

						'start_time_stamp' => $udt_obj->getStartTimeStamp(),
						'end_time_stamp'   => $udt_obj->getEndTimeStamp(),

						//Override only shows for SYSTEM override columns...
						//Need to check Worked overrides too.
						'tmp_override' => $udt_obj->getOverride(),
				];
			}
		}

		return $date_totals;
	}

	function createMealPolicy( $company_id, $type, $pay_code_id = 0, $allocation_type_id = 10 ) {
		if ( $pay_code_id == 0 ) {
			$pay_code_id = $this->policy_ids['pay_code'][100];
		}

		$mpf = new MealPolicyFactory();
		$mpf->setCompany( $company_id );

		switch ( $type ) {
			case 100: //Normal 1hr lunch
				$mpf->setName( 'Normal' );
				$mpf->setType( 20 );
				$mpf->setTriggerTime( ( 3600 * 6 ) );
				$mpf->setAmount( 3600 );
				$mpf->setIncludeLunchPunchTime( false );
				$mpf->setPayCode( $pay_code_id );
				break;
			case 110: //AutoAdd 1hr
				$mpf->setName( 'AutoAdd 1hr' );
				$mpf->setType( 15 );
				$mpf->setTriggerTime( ( 3600 * 6 ) );
				$mpf->setAmount( 3600 );
				$mpf->setIncludeLunchPunchTime( false );
				$mpf->setPayCode( $pay_code_id );
				break;
			case 115: //AutoAdd 1hr
				$mpf->setName( 'AutoAdd 1hr' );
				$mpf->setType( 15 );
				$mpf->setTriggerTime( ( 3600 * 6 ) );
				$mpf->setAmount( 3600 );
				$mpf->setIncludeLunchPunchTime( true );
				$mpf->setPayCode( $pay_code_id );
				break;
			case 120: //AutoDeduct 1hr
				$mpf->setName( 'AutoDeduct 0.5hr' );
				$mpf->setType( 10 );
				$mpf->setTriggerTime( ( 3600 * 6 ) );
				$mpf->setAmount( 3600 );
				$mpf->setIncludeLunchPunchTime( false );
				$mpf->setPayCode( $pay_code_id );
				break;
			case 130: //AutoDeduct 0.5hr after 5.00
				$mpf->setName( 'AutoDeduct 1hr' );
				$mpf->setType( 10 );
				$mpf->setTriggerTime( ( 3600 * 5 ) );
				$mpf->setAmount( 1800 );
				$mpf->setIncludeLunchPunchTime( false );
				$mpf->setPayCode( $pay_code_id );
				break;
		}

		$mpf->setAllocationType( $allocation_type_id );

		if ( $mpf->isValid() ) {
			$insert_id = $mpf->Save();
			Debug::Text( 'Meal Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Meal Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}


	function createBreakPolicy( $company_id, $type, $allocation_type_id = 10, $pay_code_id = null ) {
		if ( $pay_code_id == '' ) {
			$pay_code_id = $this->policy_ids['pay_code'][100];
		}

		$bpf = new BreakPolicyFactory();
		$bpf->setCompany( $company_id );

		switch ( $type ) {
			case 100: //Normal 15min break
				$bpf->setName( 'Normal' );
				$bpf->setType( 20 );
				$bpf->setTriggerTime( ( 3600 * 6 ) );
				$bpf->setAmount( 60 * 15 );
				$bpf->setIncludeBreakPunchTime( false );
				$bpf->setIncludeMultipleBreaks( false );
				$bpf->setPayCode( $pay_code_id );
				break;
			case 110: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( ( 3600 * 1 ) );
				$bpf->setAmount( 60 * 15 );
				$bpf->setIncludeBreakPunchTime( false );
				$bpf->setIncludeMultipleBreaks( false );
				$bpf->setPayCode( $pay_code_id );
				break;
			case 115: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Punch Time)' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( ( 3600 * 1 ) );
				$bpf->setAmount( 60 * 15 );
				$bpf->setIncludeBreakPunchTime( true );
				$bpf->setIncludeMultipleBreaks( false );
				$bpf->setPayCode( $pay_code_id );
				break;

			case 120: //AutoDeduct 15min
				$bpf->setName( 'AutoDeduct 15min' );
				$bpf->setType( 10 );
				$bpf->setTriggerTime( ( 3600 * 6 ) );
				$bpf->setAmount( 15 * 60 );
				$bpf->setIncludeBreakPunchTime( false );
				$bpf->setIncludeMultipleBreaks( false );
				$bpf->setPayCode( $pay_code_id );
				break;


			case 150: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Both)' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( ( 3600 * 1 ) );
				$bpf->setAmount( 60 * 15 );
				$bpf->setIncludeBreakPunchTime( true );
				$bpf->setIncludeMultipleBreaks( true );
				$bpf->setPayCode( $pay_code_id );
				break;
			case 152: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Both) [2]' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( ( 3600 * 3 ) );
				$bpf->setAmount( 60 * 15 );
				$bpf->setIncludeBreakPunchTime( true );
				$bpf->setIncludeMultipleBreaks( true );
				$bpf->setPayCode( $pay_code_id );
				break;
			case 154: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Both) [3]' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( ( 3600 * 5 ) );
				$bpf->setAmount( 60 * 15 );
				$bpf->setIncludeBreakPunchTime( true );
				$bpf->setIncludeMultipleBreaks( true );
				$bpf->setPayCode( $pay_code_id );
				break;
			case 156: //AutoAdd 15min
				$bpf->setName( 'AutoAdd 15min (Include Both) [4]' );
				$bpf->setType( 15 );
				$bpf->setTriggerTime( ( 3600 * 10 ) );
				$bpf->setAmount( 60 * 15 );
				$bpf->setIncludeBreakPunchTime( true );
				$bpf->setIncludeMultipleBreaks( true );
				$bpf->setPayCode( $pay_code_id );
				break;
		}

		$bpf->setAllocationType( $allocation_type_id );

		if ( $bpf->isValid() ) {
			$insert_id = $bpf->Save();
			Debug::Text( 'Break Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Break Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createContributingShiftPolicy( $company_id, $type, $contributing_pay_code_policy_id, $holiday_policy_id = null ) {
		$cspf = TTnew( 'ContributingShiftPolicyFactory' ); /** @var ContributingShiftPolicyFactory $cspf */
		$cspf->setCompany( $company_id );

		switch ( $type ) {
			case 300: //Split Shift (Partial)
				$cspf->setName( 'Shift Start (2:45PM to 11PM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '2:45PM' ) );
				$cspf->setFilterEndTime( strtotime( '11:00PM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 100 ); //Split Shift (Partial)
				break;
			case 350: //Split Shift (Partial)
				$cspf->setName( 'Shift Start (11PM to 11:59AM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '11:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '11:59PM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 100 ); //Split Shift (Partial)
				break;
			case 351: //Split Shift (Partial)
				$cspf->setName( 'Shift Start (12AM to 7AM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '12:00AM' ) );
				$cspf->setFilterEndTime( strtotime( '7:00AM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 100 ); //Split Shift (Partial)
				break;

		}

		if ( $cspf->isValid() ) {
			$insert_id = $cspf->Save( false );
			Debug::Text( 'Contributing Shift Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			if ( $holiday_policy_id != '' ) {
				$cspf->setHolidayPolicy( $holiday_policy_id );
				if ( $cspf->isValid() ) {
					$cspf->Save();
				}
			}

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Contributing Shift Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPremiumPolicy( $company_id, $type, $contributing_shift_policy_id = 0, $pay_code_id = 0 ) {
		$ppf = new PremiumPolicyFactory();
		$ppf->setCompany( $company_id );

		switch ( $type ) {
			case 300:
				$ppf->setName( 'Evening Differential (Initial)' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				//$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				//$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( ( 3.99 * 3600 ) );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( ( 4 * 3600 ) );
				$ppf->setMaximumTime( ( 4 * 3600 ) );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				break;
			case 301:
				$ppf->setName( 'Evening Differential' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				//$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				//$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( (4 * 3600 ) );
				$ppf->setWeeklyTriggerTime( (0 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( ( 0 * 3600 ) );
				$ppf->setMaximumTime( ( 0 * 3600 ) );
				$ppf->setMinMaxTimeType( 10 ); //Per Day


				break;
		}

		$ppf->setContributingShiftPolicy( $contributing_shift_policy_id );
		$ppf->setPayCode( $pay_code_id );

		if ( $ppf->isValid() ) {
			$insert_id = $ppf->Save( false );
			Debug::Text( 'Premium Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			Debug::Text( 'Post Save...', __FILE__, __LINE__, __METHOD__, 10 );
			$ppf->Save();

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Premium Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/*
	 Tests:
		Meal:
			No Meal Policy at all.
			1x Normal Meal
			1x AutoAdd Meal
			1x AudoDeduct Meal
			1x AutoAdd Meal with Include Punch Time for Lunch.

		Break:
			No Break Policy at all.
			1x Normal Break
			1x AutoAdd Break
			1x AudoDeduct Break
			1x AutoAdd Break with Include Punch Time for Break.

			3x AutoAdd Break
			3x AudoDeduct Break
			3x AutoAdd Break with Include Punch Time for Break and Multiple
	*/

	/**
	 * @group MealBreakPolicy_testNoMealPolicyA
	 */
	function testNoMealPolicyA() {
		global $dd;

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time (Part 1)
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		//Regular Time (Part 2)
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testNormalMealPolicyA
	 */
	function testNormalMealPolicyA() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time (Part 1)
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		//Regular Time (Part 2)
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMealPolicyA
	 */
	function testAutoAddMealPolicyA() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.5 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 4.5 * 3600 ) );
		//Regular Time -- After all compaction
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );


		////Lunch Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 0.5 * 3600 ) );
		////Lunch Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 100, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 0.5 * 3600 ) );
		//Lunch Taken -- After all compaction.
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1.0 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMealPolicyB
	 */
	function testAutoAddMealPolicyB() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 115 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:30PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 847, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		////$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		////$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		////$this->assertEquals( 953, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		////$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( ( 4 * 3600 ) + 953 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 4.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );


		////Lunch Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 847, $udt_arr[$date_epoch][4]['total_time'] );
		////Lunch Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 100, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( 953, $udt_arr[$date_epoch][5]['total_time'] );
		//Lunch Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( 1800, $udt_arr[$date_epoch][2]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMealPolicyC
	 */
	function testAutoAddMealPolicyC() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 115 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:30PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.5 * 3600 ) );

		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 1681, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 1919, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 3600, $udt_arr[$date_epoch][1]['total_time'] );


		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3.5 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 4.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4.5 * 3600 ) );


		////Lunch Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 100, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( 1681, $udt_arr[$date_epoch][5]['total_time'] );
		////Lunch Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 100, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( 1919, $udt_arr[$date_epoch][6]['total_time'] );
		//Lunch Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( 3600, $udt_arr[$date_epoch][3]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyA
	 */
	function testAutoDeductMealPolicyA() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Lunch Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( -1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyB
	 */
	function testAutoDeductMealPolicyB() {
		global $dd;

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 1:00PM' ), //Less than 6hrs so no autodeduct occurs
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}


	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyC
	 */
	function testAutoDeductMealPolicyC() {
		global $dd;

		//This tests a bug found that preventing regular time from being calculated when using a Contributing Shift Policy using Type=200 (Must Start and End), and a 30min auto-meal break active after 7.25hrs.
		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 4:00PM' ), //Less than 6hrs so no autodeduct occurs
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 6:00AM' ), //More than 6hrs so autodeduct occurs
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( 9720, $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( 22680, $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( -2520, $udt_arr[$date_epoch][3]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( -1080, $udt_arr[$date_epoch][4]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}



	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationA
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationA() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( ( 3 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 5:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( -3600, $udt_arr[$date_epoch][3]['total_time'] );


		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationB1
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationB1() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 2:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 6:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 6:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 4 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( -3600, $udt_arr[$date_epoch][3]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationB2A
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationB2A() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 2:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 6:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 6:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 4 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( -3600, $udt_arr[$date_epoch][3]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}


	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationB2B
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationB2B() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 6:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 3:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 1 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 3:00PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 6:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 3 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][3]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][4]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		$this->assertEquals( -3600, $udt_arr[$date_epoch][4]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationB3
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationB3() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 1:30PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:30PM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 6:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 6:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 4 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:30PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( -1800, $udt_arr[$date_epoch][3]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][4]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:30PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		$this->assertEquals( -1800, $udt_arr[$date_epoch][4]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationFallDSTA
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationFallDSTA() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 130, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '2023-11-04 12:00:00' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '2023-11-05 12:00:00' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:15PM' ),
							  strtotime( $date_stamp2 . ' 8:15AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( ( 4.25 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:15PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( ( 8.25 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 1:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 8:15AM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( -1800, $udt_arr[$date_epoch][3]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] ); //Lunch gets pushed back an hour because it overlaps the DST transition.
		$this->assertEquals( strtotime( $date_stamp2 . ' 1:00AM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );


		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationFallDSTB
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationFallDSTB() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 130, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '2023-11-04 12:00:00' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '2023-11-05 12:00:00' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:15PM' ),
							  strtotime( $date_stamp2 . ' 9:15AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( ( 3.25 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 9:15PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( ( 9.25 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 1:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 9:15AM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( -1800, $udt_arr[$date_epoch][3]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 1:00AM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );


		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationFallDSTC
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationFallDSTC() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 130, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '2023-11-04 12:00:00' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '2023-11-05 12:00:00' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:15PM' ),
							  strtotime( $date_stamp2 . ' 10:15AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( ( 4.75 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:15PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 2:00AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( ( 7.75 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 2:30AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 10:15AM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( -1800, $udt_arr[$date_epoch][3]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 2:00AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 2:30AM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );


		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationFallDSTD
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationFallDSTD() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 130, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '2023-11-04 12:00:00' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '2023-11-05 12:00:00' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//If punches exist at: In: 05-Nov-22 @ 6:30PM Out: 06-Nov-22 @ 6:30AM (DST transition day) with a auto-deduct meal policy at exactly 7hrs, it can actually result in 1:30AM PST and 1:00AM PST instead, causing incorrect total time. So handling the offset should correct for that and force both start/end timestamps into the later timezone.
		//  It appears as though we don't need to do the same adjustment on the auto-add side.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00PM' ),
							  strtotime( $date_stamp2 . ' 9:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( ( 3.50 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 9:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( ( 9 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 1:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 9:00AM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( -1800, $udt_arr[$date_epoch][3]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 1:00AM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );


		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}


	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationB4
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationB4() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 1:59PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:59PM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 6:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 6:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 4 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:59PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( -3540, $udt_arr[$date_epoch][3]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][4]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:59PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		$this->assertEquals( -60, $udt_arr[$date_epoch][4]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationC
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationC() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 1:15PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:15PM' ), //Transfer right after the lunch active after time, but only for less than the total lunch time, so it gets zero'd out completely.
							  strtotime( $date_stamp . ' 1:50PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:50PM' ), //Transfer right after the lunch active after time, but only for less than the total lunch time, so it gets zero'd out completely.
							  strtotime( $date_stamp . ' 2:15PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:15PM' ), //Transfer right after the lunch active after time, but only for less than the total lunch time, so it gets zero'd out completely.
							  strtotime( $date_stamp . ' 6:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:15PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 0.25 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:15PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 6:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 3.75 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][3]['total_time'] );

		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][4]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:15PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:50PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		$this->assertEquals( ( -2100 ), $udt_arr[$date_epoch][4]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][5]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][5]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][5]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:15PM' ), $udt_arr[$date_epoch][5]['end_time_stamp'] );
		$this->assertEquals( ( -0.25 * 3600 ), $udt_arr[$date_epoch][5]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][6]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][6]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:50PM' ), $udt_arr[$date_epoch][6]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][6]['end_time_stamp'] );
		$this->assertEquals( ( -600 ), $udt_arr[$date_epoch][6]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 7, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationD1
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationD1() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:30AM' ),
							  strtotime( $date_stamp . ' 12:30PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:30PM' ),
							  strtotime( $date_stamp . ' 1:30PM' ), //More than 6hrs, but less than 6hrs + 60min lunch. Should switch into Proportional Distribution automatically.
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 7:30AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:30PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][2]['branch_id'] ); //Should be the branch of the 2nd punch pair where the meal exactly falls within.
		$this->assertEquals( strtotime( $date_stamp . ' 12:30PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:30PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( -3600, $udt_arr[$date_epoch][2]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationD2
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationD2() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:30AM' ),
							  strtotime( $date_stamp . ' 12:50PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:50PM' ),
							  strtotime( $date_stamp . ' 1:30PM' ), //More than 6hrs, but less than 6hrs + 60min lunch. Should switch into Proportional Distribution automatically.
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 7:30AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:30PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:50PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:30PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( -2400, $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:30PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:50PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( -1200, $udt_arr[$date_epoch][3]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationE1
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationE1() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//Check the split shift scenario with a large gap between the shifts right where 6hr mark would normally occur.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 4:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 6:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 6:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 4 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 4:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 9:00AM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 9:00AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:00AM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( -3600, $udt_arr[$date_epoch][3]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationE2
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationE2() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//Check the split shift scenario with a large gap between the shifts right where 6hr mark would normally occur.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 4:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 4:00PM' ),
							  strtotime( $date_stamp . ' 8:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 4:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 4 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 4:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 9:00AM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 9:00AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:00AM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( -3600, $udt_arr[$date_epoch][3]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationF1
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationF1() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift
		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Shift Differential

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['premium'][300] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][300] );
		$this->policy_ids['premium'][301] = $this->createPremiumPolicy( $this->company_id, 301, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][310] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$this->policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//Check the split shift scenario with a large gap between the shifts right where 6hr mark would normally occur.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:45AM' ),
							  strtotime( $date_stamp . ' 9:45AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:45AM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 7:15PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11.5 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1 * 3600 ) );
		//$this->assertEquals( strtotime( $date_stamp . ' 1:45PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		//$this->assertEquals( strtotime( $date_stamp . ' 2:45PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 9:45AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 11:45AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 6:45AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 9:45AM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 6.5 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 12:45PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 7:15PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 6:45PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 7:15PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][5]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 4 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 3:15PM' ), $udt_arr[$date_epoch][5]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 7:15PM' ), $udt_arr[$date_epoch][5]['end_time_stamp'] );
		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][6]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], ( -1 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 11:45AM' ), $udt_arr[$date_epoch][6]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:45PM' ), $udt_arr[$date_epoch][6]['end_time_stamp'] );

		//Make sure no other hours
		$this->assertCount( 7, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationF2A
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationF2A() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift
		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Shift Differential

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['premium'][300] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][300] );
		$this->policy_ids['premium'][301] = $this->createPremiumPolicy( $this->company_id, 301, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][310] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$this->policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//Check spanning midnight that causes the meal time to have to split a UDT record into three pieces.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:30PM' ),
							  strtotime( $date_stamp2 . ' 7:15AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10.75 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1 * 3600 ) );
		//$this->assertEquals( strtotime( $date_stamp . ' 11:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		//$this->assertEquals( strtotime( $date_stamp2 . ' 12:00AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		////Regular Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		//$this->assertEquals( strtotime( $date_stamp2 . ' 12:00AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		//$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 5.0 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 7:30PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5.75 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp2 . ' 1:30AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 7:15AM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( -1 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp2 . ' 12:30AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 1:30AM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationF2B
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationF2B() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift
		$this->policy_ids['contributing_shift_policy'][350] = $this->createContributingShiftPolicy( $this->company_id, 350, $this->policy_ids['contributing_pay_code_policy'][12] ); //11P - 11:59P
		$this->policy_ids['contributing_shift_policy'][351] = $this->createContributingShiftPolicy( $this->company_id, 351, $this->policy_ids['contributing_pay_code_policy'][12] ); //12A - 7A

		$this->policy_ids['pay_code'][101] = $dd->createPayCode( $this->company_id, 101 ); //Regular B
		$this->policy_ids['pay_code'][102] = $dd->createPayCode( $this->company_id, 102 ); //Regular C

		$this->policy_ids['regular'][350] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][350], $this->policy_ids['pay_code'][101] );
		$this->policy_ids['regular'][351] = $dd->createRegularTimePolicy( $this->company_id, 21, $this->policy_ids['contributing_shift_policy'][351], $this->policy_ids['pay_code'][101] );
		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 22, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 130, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100], $this->policy_ids['regular'][350], $this->policy_ids['regular'][351] ]//Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//Check spanning midnight that causes the meal time to have to split a UDT record into three pieces.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:59PM' ),
							  strtotime( $date_stamp2 . ' 7:38AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], 43740 );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], 2280 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 7:00AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 7:38AM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], 14460 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( strtotime( $date_stamp . ' 6:59PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 11:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], 1740 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][101] );
		$this->assertEquals( strtotime( $date_stamp . ' 11:00PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 11:29PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 25260 );
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $this->policy_ids['pay_code'][101] );
		$this->assertEquals( strtotime( $date_stamp . ' 11:59PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp2 . ' 7:00AM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], 60 );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $this->policy_ids['pay_code'][101] );
		//$this->assertEquals( strtotime( $date_stamp . ' 11:59PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		//$this->assertEquals( strtotime( $date_stamp2 . ' 12:00AM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );


		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][5]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( -0.50 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][5]['pay_code_id'], $this->policy_ids['pay_code'][190] );
		$this->assertEquals( strtotime( $date_stamp . ' 11:29PM' ), $udt_arr[$date_epoch][5]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 11:59PM' ), $udt_arr[$date_epoch][5]['end_time_stamp'] );

		//Make sure no other hours
		$this->assertCount( 6, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationF3
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationF3() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift
		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Shift Differential

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['premium'][300] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][300] );
		$this->policy_ids['premium'][301] = $this->createPremiumPolicy( $this->company_id, 301, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][310] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$this->policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//Check the split shift scenario with a large gap between the shifts right where 6hr mark would normally occur.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:45AM' ),
							  strtotime( $date_stamp . ' 1:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 8:15PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11.5 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1 * 3600 ) );
		//$this->assertEquals( strtotime( $date_stamp . ' 1:45PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		//$this->assertEquals( strtotime( $date_stamp . ' 2:45PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 5.0 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 7:45AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:45PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6.5 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 1:45PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:15PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1.5 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 6:45PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:15PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 4 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 4:15PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:15PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][5]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( -0.75 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][5]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:45PM' ), $udt_arr[$date_epoch][5]['end_time_stamp'] );
		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][6]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], ( -0.25 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 12:45PM' ), $udt_arr[$date_epoch][6]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][6]['end_time_stamp'] );

		//Make sure no other hours
		$this->assertCount( 7, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationF4
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationF4() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift
		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Shift Differential

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['premium'][300] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][300] );
		$this->policy_ids['premium'][301] = $this->createPremiumPolicy( $this->company_id, 301, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][310] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$this->policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//Check the split shift scenario with a large gap between the shifts right where 6hr mark would normally occur.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:45AM' ),
							  strtotime( $date_stamp . ' 1:30PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:30PM' ), //Transfer right at the lunch active after time.
							  strtotime( $date_stamp . ' 8:15PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11.5 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1 * 3600 ) );
		//$this->assertEquals( strtotime( $date_stamp . ' 1:45PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		//$this->assertEquals( strtotime( $date_stamp . ' 2:45PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 5.0 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 7:45AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:45PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6.5 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 1:45PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:15PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1.5 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 6:45PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:15PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 4 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 4:15PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:15PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][5]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( -0.75 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 12:45PM' ), $udt_arr[$date_epoch][5]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:30PM' ), $udt_arr[$date_epoch][5]['end_time_stamp'] );
		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][6]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], ( -0.25 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 1:30PM' ), $udt_arr[$date_epoch][6]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:45PM' ), $udt_arr[$date_epoch][6]['end_time_stamp'] );

		//Make sure no other hours
		$this->assertCount( 7, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationF5
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationF5() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift
		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Shift Differential

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );

		$this->policy_ids['premium'][300] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][300] );
		$this->policy_ids['premium'][301] = $this->createPremiumPolicy( $this->company_id, 301, $this->policy_ids['contributing_shift_policy'][300], $this->policy_ids['pay_code'][310] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$this->policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								$this->policy_ids['absence_policy'], //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) + ( 86400 * 2 ) ); //If this the beginning of the week (Sun), it fails on weeks where DST changes.
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch3 = TTDate::getBeginDayEpoch( ( $date_epoch - 86400 - 3601 ) ); //Before the above date.
		$date_stamp3 = TTDate::getDate( 'DATE', $date_epoch3 );

		//Test a working shift with meal deducted, along with an on-call absence for 12hrs. The meal should not reduce the oncall absence as it already reduces the working shift.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createAbsence( $this->user_id, $date_epoch, ( 12 * 3600 ), $this->policy_ids['absence_policy'][10] );

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 20 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 5:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );

		//Absence
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 25, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 12 * 3600 ) );
		$this->assertEquals( strtotime( $date_stamp3 . ' 8:00PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );

		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], -3600 );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationG
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationG() {
		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift

		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );


		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100] ] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = TTDate::getBeginDayEpoch( ( $date_epoch + 86400 + 3601 ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 1:30PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:30PM' ), //Lunch happens from 1P - 2P
							  strtotime( $date_stamp . ' 9:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][1]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][2]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 3:30PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:30PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );
		$this->assertEquals( ( 5 * 3600 ), $udt_arr[$date_epoch][2]['total_time'] );
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $this->branch_ids[0], $udt_arr[$date_epoch][3]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:30PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );
		$this->assertEquals( -1800, $udt_arr[$date_epoch][3]['total_time'] ); //This is proportional distribution, since lunch can't be deducted from any specific UDT record and they aren't transfer punches.
		//Regular Time (Lunch deduct)
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $this->branch_ids[1], $udt_arr[$date_epoch][4]['branch_id'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:30PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );
		$this->assertEquals( -1800, $udt_arr[$date_epoch][4]['total_time'] ); //This is proportional distribution, since lunch can't be deducted from any specific UDT record and they aren't transfer punches.

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductMealPolicyAtActiveTimeAllocationH
	 */
	function testAutoDeductMealPolicyAtActiveTimeAllocationH() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		require_once( dirname(__FILE__) . DIRECTORY_SEPARATOR .'PremiumPolicyTest.php');
		$pp_test_obj = new PremiumPolicyTest();
		$this->policy_ids['contributing_shift_policy'][100] = $pp_test_obj->createContributingShiftPolicy( $this->company_id, 200, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break -- Full Shift
		$this->policy_ids['contributing_shift_policy'][350] = $this->createContributingShiftPolicy( $this->company_id, 350, $this->policy_ids['contributing_pay_code_policy'][12] ); //11P - 11:59P
		$this->policy_ids['contributing_shift_policy'][351] = $this->createContributingShiftPolicy( $this->company_id, 351, $this->policy_ids['contributing_pay_code_policy'][12] ); //12A - 7A

		$this->policy_ids['pay_code'][101] = $dd->createPayCode( $this->company_id, 101 ); //Regular B
		$this->policy_ids['pay_code'][102] = $dd->createPayCode( $this->company_id, 102 ); //Regular C

		$this->policy_ids['regular'][350] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][350], $this->policy_ids['pay_code'][101] );
		$this->policy_ids['regular'][351] = $dd->createRegularTimePolicy( $this->company_id, 21, $this->policy_ids['contributing_shift_policy'][351], $this->policy_ids['pay_code'][101] );
		$this->policy_ids['regular'][100] = $dd->createRegularTimePolicy( $this->company_id, 22, $this->policy_ids['contributing_shift_policy'][100], $this->policy_ids['pay_code'][100] );

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 ); //100=At Active Time Allocation.

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								[ $this->policy_ids['regular'][100], $this->policy_ids['regular'][350], $this->policy_ids['regular'][351] ]//Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Check shift length that is exactly 6 hours, and 1hr should be deducted from the end of it.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 2:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 5 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( strtotime( $date_stamp . ' 8:00AM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );

		//Lunch Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 100, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( -1 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][190] );
		$this->assertEquals( strtotime( $date_stamp . ' 1:00PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	//
	// Break Policy
	//

	/**
	 * @group MealBreakPolicy_testNoBreakPolicyA
	 */
	function testNoBreakPolicyA() {
		global $dd;

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:15AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:15PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2.25 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testNormalBreakPolicyA
	 */
	function testNormalBreakPolicyA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 100 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:15AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:15PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2.25 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );


		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddBreakPolicyA
	 */
	function testAutoAddBreakPolicyA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 110 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:15AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:15PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.25 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 196, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 225, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( 225, $udt_arr[$date_epoch][3]['total_time'] );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 254, $udt_arr[$date_epoch][4]['total_time'] );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 1.75 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 2 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], ( ( 2 * 3600 ) + 254 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][7]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][7]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], ( 2.25 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.25 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 4.00 * 3600 ) );


		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][8]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][8]['type_id'] );
		//$this->assertEquals( 196, $udt_arr[$date_epoch][8]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][9]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][9]['type_id'] );
		//$this->assertEquals( 225, $udt_arr[$date_epoch][9]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][10]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][10]['type_id'] );
		//$this->assertEquals( 225, $udt_arr[$date_epoch][10]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][11]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][11]['type_id'] );
		//$this->assertEquals( 254, $udt_arr[$date_epoch][11]['total_time'] );
		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 0.25 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddBreakPolicyB
	 */
	function testAutoAddBreakPolicyB() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 115 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:06AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:15PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.25 * 3600 ) );

		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 83, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 88, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( 88, $udt_arr[$date_epoch][3]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 101, $udt_arr[$date_epoch][4]['total_time'] );
		////Regular Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 360, $udt_arr[$date_epoch][1]['total_time'] );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.25 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 4 * 3600 ) );


		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][9]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][9]['type_id'] );
		//$this->assertEquals( 83, $udt_arr[$date_epoch][9]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][10]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][10]['type_id'] );
		//$this->assertEquals( 88, $udt_arr[$date_epoch][10]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][11]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][11]['type_id'] );
		//$this->assertEquals( 88, $udt_arr[$date_epoch][11]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][12]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][12]['type_id'] );
		//$this->assertEquals( 101, $udt_arr[$date_epoch][12]['total_time'] );
		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( 360, $udt_arr[$date_epoch][4]['total_time'] );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddBreakPolicyC
	 */
	function testAutoAddBreakPolicyC() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 115 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:21AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 20,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
							  [
									  'in_type_id'    => 20,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:15PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.15 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 187, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 227, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( 227, $udt_arr[$date_epoch][3]['total_time'] );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 259, $udt_arr[$date_epoch][4]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], ( ( 2 * 3600 ) + 259 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][7]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][7]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], ( 2.25 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.65 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 2.25 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2.25 * 3600 ) );


		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][8]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][8]['type_id'] );
		//$this->assertEquals( 187, $udt_arr[$date_epoch][8]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][9]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][9]['type_id'] );
		//$this->assertEquals( 227, $udt_arr[$date_epoch][9]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][10]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][10]['type_id'] );
		//$this->assertEquals( 227, $udt_arr[$date_epoch][10]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][11]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][11]['type_id'] );
		//$this->assertEquals( 259, $udt_arr[$date_epoch][11]['total_time'] );
		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][5]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 0.25 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 6, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductBreakPolicyA
	 */
	function testAutoDeductBreakPolicyA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 5:15PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		/*
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (-0.25*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 20 );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (9.25*3600) );
		//Break Time Taken
		$this->assertEquals( $udt_arr[$date_epoch][3]['status_id'], 10 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['type_id'], 110 );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (-0.25*3600) );
		*/

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( -0.25 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoDeductBreakPolicyB
	 */
	function testAutoDeductBreakPolicyB() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 120 );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 1:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMultipleBreakPolicyA
	 */
	function testAutoAddMultipleBreakPolicyA() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:15AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:15PM' ),
							  strtotime( $date_stamp . ' 3:15PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:15PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9.5 * 3600 ) );

		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 174, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 174, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( 199, $udt_arr[$date_epoch][3]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 199, $udt_arr[$date_epoch][4]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( 228, $udt_arr[$date_epoch][5]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( 228, $udt_arr[$date_epoch][6]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][7]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][7]['type_id'] );
		//$this->assertEquals( 299, $udt_arr[$date_epoch][7]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][8]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][8]['type_id'] );
		//$this->assertEquals( 299, $udt_arr[$date_epoch][8]['total_time'] );
		////Regular Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.50 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9.5 * 3600 ) );

		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][13]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][13]['type_id'] );
		//$this->assertEquals( 174, $udt_arr[$date_epoch][13]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][14]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][14]['type_id'] );
		//$this->assertEquals( 199, $udt_arr[$date_epoch][14]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][15]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][15]['type_id'] );
		//$this->assertEquals( 228, $udt_arr[$date_epoch][15]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][16]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][16]['type_id'] );
		//$this->assertEquals( 299, $udt_arr[$date_epoch][16]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][17]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][17]['type_id'] );
		//$this->assertEquals( 174, $udt_arr[$date_epoch][17]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][18]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][18]['type_id'] );
		//$this->assertEquals( 199, $udt_arr[$date_epoch][18]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][19]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][19]['type_id'] );
		//$this->assertEquals( 228, $udt_arr[$date_epoch][19]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][20]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][20]['type_id'] );
		//$this->assertEquals( 299, $udt_arr[$date_epoch][20]['total_time'] );
		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.25 * 3600 ) );
		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.25 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMultipleBreakPolicyB
	 */
	function testAutoAddMultipleBreakPolicyB() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:06AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:21PM' ),
							  strtotime( $date_stamp . ' 3:15PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:15PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		////Total Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		//$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9.5 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 151, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 159, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( 180, $udt_arr[$date_epoch][3]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 188, $udt_arr[$date_epoch][4]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( 198, $udt_arr[$date_epoch][5]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( 226, $udt_arr[$date_epoch][6]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][7]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][7]['type_id'] );
		//$this->assertEquals( 230, $udt_arr[$date_epoch][7]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][8]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][8]['type_id'] );
		//$this->assertEquals( 288, $udt_arr[$date_epoch][8]['total_time'] );


		////Regular Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.45 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9.5 * 3600 ) );

		////Break Time Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][13]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][13]['type_id'] );
		//$this->assertEquals( 151, $udt_arr[$date_epoch][13]['total_time'] );
		////Break Time Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][14]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][14]['type_id'] );
		//$this->assertEquals( 159, $udt_arr[$date_epoch][14]['total_time'] );
		////Break Time Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][15]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][15]['type_id'] );
		//$this->assertEquals( 180, $udt_arr[$date_epoch][15]['total_time'] );
		////Break Time Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][16]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][16]['type_id'] );
		//$this->assertEquals( 230, $udt_arr[$date_epoch][16]['total_time'] );
		////Break Time Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][17]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][17]['type_id'] );
		//$this->assertEquals( 188, $udt_arr[$date_epoch][17]['total_time'] );
		////Break Time Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][18]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][18]['type_id'] );
		//$this->assertEquals( 198, $udt_arr[$date_epoch][18]['total_time'] );
		////Break Time Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][19]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][19]['type_id'] );
		//$this->assertEquals( 226, $udt_arr[$date_epoch][19]['total_time'] );
		////Break Time Taken
		//$this->assertEquals( 10, $udt_arr[$date_epoch][20]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][20]['type_id'] );
		//$this->assertEquals( 288, $udt_arr[$date_epoch][20]['total_time'] );

		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.1 * 3600 ) );
		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.35 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMultipleBreakPolicyC
	 */
	function testAutoAddMultipleBreakPolicyC() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);
		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:45AM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9.5 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 205, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 205, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( 205, $udt_arr[$date_epoch][3]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 695, $udt_arr[$date_epoch][4]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( 695, $udt_arr[$date_epoch][5]['total_time'] );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( 695, $udt_arr[$date_epoch][6]['total_time'] );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], ( ( 2 * 3600 ) + 695 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][7]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][7]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][7]['total_time'], ( 6.75 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9.5 * 3600 ) );


		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][8]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][8]['type_id'] );
		//$this->assertEquals( 205, $udt_arr[$date_epoch][8]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][9]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][9]['type_id'] );
		//$this->assertEquals( 695, $udt_arr[$date_epoch][9]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][10]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][10]['type_id'] );
		//$this->assertEquals( 205, $udt_arr[$date_epoch][10]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][11]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][11]['type_id'] );
		//$this->assertEquals( 695, $udt_arr[$date_epoch][11]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][12]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][12]['type_id'] );
		//$this->assertEquals( 205, $udt_arr[$date_epoch][12]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][13]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][13]['type_id'] );
		//$this->assertEquals( 695, $udt_arr[$date_epoch][13]['total_time'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.75 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMultipleBreakPolicyD
	 */
	function testAutoAddMultipleBreakPolicyD() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:51AM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9.4 * 3600 ) );
		////Regular Time  -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 208, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 208, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( 208, $udt_arr[$date_epoch][3]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 692, $udt_arr[$date_epoch][4]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( 692, $udt_arr[$date_epoch][5]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( 692, $udt_arr[$date_epoch][6]['total_time'] );
		////Regular Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6.65 * 3600 ) );


		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][9]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][9]['type_id'] );
		//$this->assertEquals( 208, $udt_arr[$date_epoch][9]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][10]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][10]['type_id'] );
		//$this->assertEquals( 692, $udt_arr[$date_epoch][10]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][11]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][11]['type_id'] );
		//$this->assertEquals( 208, $udt_arr[$date_epoch][11]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][12]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][12]['type_id'] );
		//$this->assertEquals( 692, $udt_arr[$date_epoch][12]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][13]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][13]['type_id'] );
		//$this->assertEquals( 208, $udt_arr[$date_epoch][13]['total_time'] );
		////Break Time Taken -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][14]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][14]['type_id'] );
		//$this->assertEquals( 692, $udt_arr[$date_epoch][14]['total_time'] );
		//Break Time Taken
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.75 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMultipleBreakPolicyE
	 */
	function testAutoAddMultipleBreakPolicyE() {
		global $dd;

		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154 );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156 ); //This one shouldn't apply

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:06AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:06PM' ),
							  strtotime( $date_stamp . ' 2:15PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:21PM' ),
							  strtotime( $date_stamp . ' 4:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 4:36PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9.5 * 3600 ) );
		////Regular Time  -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( 56, $udt_arr[$date_epoch][1]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( 92, $udt_arr[$date_epoch][2]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( 112, $udt_arr[$date_epoch][3]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		//$this->assertEquals( 118, $udt_arr[$date_epoch][4]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][5]['type_id'] );
		//$this->assertEquals( 127, $udt_arr[$date_epoch][5]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][6]['type_id'] );
		//$this->assertEquals( 127, $udt_arr[$date_epoch][6]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][7]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][7]['type_id'] );
		//$this->assertEquals( 187, $udt_arr[$date_epoch][7]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][8]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][8]['type_id'] );
		//$this->assertEquals( 197, $udt_arr[$date_epoch][8]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][9]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][9]['type_id'] );
		//$this->assertEquals( 212, $udt_arr[$date_epoch][9]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][10]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][10]['type_id'] );
		//$this->assertEquals( 212, $udt_arr[$date_epoch][10]['total_time'] );
		////Regular Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.4 * 3600 ) );

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9.5 * 3600 ) );

		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][16]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][16]['type_id'] );
		//$this->assertEquals( 56, $udt_arr[$date_epoch][16]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][17]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][17]['type_id'] );
		//$this->assertEquals( 112, $udt_arr[$date_epoch][17]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][18]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][18]['type_id'] );
		//$this->assertEquals( 118, $udt_arr[$date_epoch][18]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][19]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][19]['type_id'] );
		//$this->assertEquals( 127, $udt_arr[$date_epoch][19]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][20]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][20]['type_id'] );
		//$this->assertEquals( 127, $udt_arr[$date_epoch][20]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][21]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][21]['type_id'] );
		//$this->assertEquals( 92, $udt_arr[$date_epoch][21]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][22]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][22]['type_id'] );
		//$this->assertEquals( 187, $udt_arr[$date_epoch][22]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][23]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][23]['type_id'] );
		//$this->assertEquals( 197, $udt_arr[$date_epoch][23]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][24]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][24]['type_id'] );
		//$this->assertEquals( 212, $udt_arr[$date_epoch][24]['total_time'] );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][25]['status_id'] );
		//$this->assertEquals( 110, $udt_arr[$date_epoch][25]['type_id'] );
		//$this->assertEquals( 212, $udt_arr[$date_epoch][25]['total_time'] );

		//Break Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.1 * 3600 ) );
		//Break Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.1 * 3600 ) );
		//Break Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 0.1 * 3600 ) );
		//Break Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][5]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 0.1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 6, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group MealBreakPolicy_testAutoAddMultipleBreakPolicyF
	 */
	function testAutoAddMultipleBreakPolicyF() {
		global $dd;

		$this->policy_ids['pay_code'][193] = $dd->createPayCode( $this->company_id, 193 ); //Break2
		$this->policy_ids['pay_code'][194] = $dd->createPayCode( $this->company_id, 194 ); //Break3
		$this->policy_ids['pay_code'][195] = $dd->createPayCode( $this->company_id, 195 ); //Break4



		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 150, 10, $this->policy_ids['pay_code'][192] );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 152, 10, $this->policy_ids['pay_code'][193] );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 154, 10, $this->policy_ids['pay_code'][194] );
		$policy_ids['break'][] = $this->createBreakPolicy( $this->company_id, 156, 10, $this->policy_ids['pay_code'][195] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								$policy_ids['break'], //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::getMiddleDayEpoch( TTDate::getBeginWeekEpoch( time() ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 5:43AM' ),
							  strtotime( $date_stamp . ' 10:08AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:31AM' ),
							  strtotime( $date_stamp . ' 12:12PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:34PM' ),
							  strtotime( $date_stamp . ' 2:24PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:44PM' ),
							  strtotime( $date_stamp . ' 3:14PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 30,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:20PM' ),
							  strtotime( $date_stamp . ' 5:00PM' ),
							  [
									  'in_type_id'    => 30,
									  'out_type_id'   => 10,
									  'branch_id'     => 0,
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch][0]['type_id'] );
		$this->assertEquals( 38160, $udt_arr[$date_epoch][0]['total_time'] );

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( 1800, $udt_arr[$date_epoch][1]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:44PM' ), $udt_arr[$date_epoch][1]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 3:14PM' ), $udt_arr[$date_epoch][1]['end_time_stamp'] );

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( 6000, $udt_arr[$date_epoch][2]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 3:20PM' ), $udt_arr[$date_epoch][2]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 5:00PM' ), $udt_arr[$date_epoch][2]['end_time_stamp'] );

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( 6060, $udt_arr[$date_epoch][3]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:31AM' ), $udt_arr[$date_epoch][3]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:12PM' ), $udt_arr[$date_epoch][3]['end_time_stamp'] );

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][4]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][4]['type_id'] );
		$this->assertEquals( 7500, $udt_arr[$date_epoch][4]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:34PM' ), $udt_arr[$date_epoch][4]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:39PM' ), $udt_arr[$date_epoch][4]['end_time_stamp'] );

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][5]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][5]['type_id'] );
		$this->assertEquals( 16800, $udt_arr[$date_epoch][5]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 5:43AM' ), $udt_arr[$date_epoch][5]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:23AM' ), $udt_arr[$date_epoch][5]['end_time_stamp'] );

		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][6]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][6]['type_id'] );
		$this->assertEquals( 900, $udt_arr[$date_epoch][6]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:24PM' ), $udt_arr[$date_epoch][6]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 2:39PM' ), $udt_arr[$date_epoch][6]['end_time_stamp'] );


		//Break1
		$this->assertEquals( 10, $udt_arr[$date_epoch][7]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][7]['type_id'] );
		$this->assertEquals( 900, $udt_arr[$date_epoch][7]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:19PM' ), $udt_arr[$date_epoch][7]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:34PM' ), $udt_arr[$date_epoch][7]['end_time_stamp'] );

		//Break2
		$this->assertEquals( 10, $udt_arr[$date_epoch][8]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][8]['type_id'] );
		$this->assertEquals( 420, $udt_arr[$date_epoch][8]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:12PM' ), $udt_arr[$date_epoch][8]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 12:19PM' ), $udt_arr[$date_epoch][8]['end_time_stamp'] );

		//Break3
		$this->assertEquals( 10, $udt_arr[$date_epoch][9]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][9]['type_id'] );
		$this->assertEquals( 480, $udt_arr[$date_epoch][9]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:23AM' ), $udt_arr[$date_epoch][9]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:31AM' ), $udt_arr[$date_epoch][9]['end_time_stamp'] );

		//Break4
		$this->assertEquals( 10, $udt_arr[$date_epoch][10]['status_id'] );
		$this->assertEquals( 110, $udt_arr[$date_epoch][10]['type_id'] );
		$this->assertEquals( 900, $udt_arr[$date_epoch][10]['total_time'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:08AM' ), $udt_arr[$date_epoch][10]['start_time_stamp'] );
		$this->assertEquals( strtotime( $date_stamp . ' 10:23AM' ), $udt_arr[$date_epoch][10]['end_time_stamp'] );

		//Make sure no other hours
		$this->assertCount( 11, $udt_arr[$date_epoch] );

		return true;
	}


	function testFindPunchDataGapAfterTimeStamp() {
		$p_obj = TTnew('PunchFactory');
		$p_obj->setStatus( 10 ); //10=In, 20=Out
		$p_obj->setType( 10 ); //10=Normal, 30=Break
		$p_obj->setTimeStamp( strtotime( '2024-02-01 8:00AM' ) );
		$punch_data[] = $p_obj;

		$p_obj = TTnew('PunchFactory');
		$p_obj->setStatus( 20 ); //10=In, 20=Out
		$p_obj->setType( 30 ); //10=Normal, 30=Break
		$p_obj->setTimeStamp( strtotime( '2024-02-01 9:00AM' ) );
		$punch_data[] = $p_obj;

		$p_obj = TTnew('PunchFactory');
		$p_obj->setStatus( 10 ); //10=In, 20=Out
		$p_obj->setType( 30 ); //10=Normal, 30=Break
		$p_obj->setTimeStamp( strtotime( '2024-02-01 9:15AM' ) );
		$punch_data[] = $p_obj;

		$p_obj = TTnew('PunchFactory');
		$p_obj->setStatus( 20 ); //10=In, 20=Out
		$p_obj->setType( 30 ); //10=Normal, 30=Break
		$p_obj->setTimeStamp( strtotime( '2024-02-01 2:00PM' ) );
		$punch_data[] = $p_obj;

		$p_obj = TTnew('PunchFactory');
		$p_obj->setStatus( 10 ); //10=In, 20=Out
		$p_obj->setType( 30 ); //10=Normal, 30=Break
		$p_obj->setTimeStamp( strtotime( '2024-02-01 2:15PM' ) );
		$punch_data[] = $p_obj;

		$p_obj = TTnew('PunchFactory');
		$p_obj->setStatus( 20 ); //10=In, 20=Out
		$p_obj->setType( 10 ); //10=Normal, 30=Break
		$p_obj->setTimeStamp( strtotime( '2024-02-01 5:00PM' ) );
		$punch_data[] = $p_obj;

		$calc_policy_obj = TTnew('CalculatePolicy');

		//Find meal/break gaps
		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 8:00AM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 9:00AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 9:15AM' ), $gap['end'] );
		$this->assertEquals( 900, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 9:05AM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 9:05AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 9:15AM' ), $gap['end'] );
		$this->assertEquals( 600, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 9:14AM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 9:14AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 9:15AM' ), $gap['end'] );
		$this->assertEquals( 60, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 9:15AM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 2:00PM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 2:15PM' ), $gap['end'] );
		$this->assertEquals( 900, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 2:00PM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 2:00PM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 2:15PM' ), $gap['end'] );
		$this->assertEquals( 900, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 2:05PM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 2:05PM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 2:15PM' ), $gap['end'] );
		$this->assertEquals( 600, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 4:55PM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 5:00PM' ), $gap['start'] ); //Return last punch
		$this->assertEquals( null, $gap['end'] );
		$this->assertEquals( null, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 5:05PM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 5:05PM' ), $gap['start'] );
		$this->assertEquals( null, $gap['end'] );
		$this->assertEquals( null, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 6:05PM' ), 20 );
		$this->assertEquals( strtotime( '2024-02-01 6:05PM' ), $gap['start'] );
		$this->assertEquals( null, $gap['end'] );
		$this->assertEquals( null, $gap['total_time'] );

		//Find working parts.

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 7:30AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 8:00AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 9:00AM' ), $gap['end'] );
		$this->assertEquals( 3600, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 8:00AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 8:00AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 9:00AM' ), $gap['end'] );
		$this->assertEquals( 3600, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 8:05AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 8:05AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 9:00AM' ), $gap['end'] );
		$this->assertEquals( 3300, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 8:55AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 8:55AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 9:00AM' ), $gap['end'] );
		$this->assertEquals( 300, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 8:59AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 8:59AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 9:00AM' ), $gap['end'] );
		$this->assertEquals( 60, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 9:00AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 9:15AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 2:00PM' ), $gap['end'] );
		$this->assertEquals( 17100, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 9:05AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 9:15AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 2:00PM' ), $gap['end'] );
		$this->assertEquals( 17100, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 9:15AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 9:15AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 2:00PM' ), $gap['end'] );
		$this->assertEquals( 17100, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 9:20AM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 9:20AM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 2:00PM' ), $gap['end'] );
		$this->assertEquals( 16800, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 4:55PM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 4:55PM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 5:00PM' ), $gap['end'] );
		$this->assertEquals( 300, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 5:00PM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 5:00PM' ), $gap['start'] );
		$this->assertEquals( null, $gap['end'] );
		$this->assertEquals( null, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 5:05PM' ), 10 );
		$this->assertEquals( strtotime( '2024-02-01 5:05PM' ), $gap['start'] );
		$this->assertEquals( null, $gap['end'] );
		$this->assertEquals( null, $gap['total_time'] );


		//Other cases
		unset( $punch_data );

		$p_obj = TTnew('PunchFactory');
		$p_obj->setStatus( 10 ); //10=In, 20=Out
		$p_obj->setType( 10 ); //10=Normal, 30=Break
		$p_obj->setTimeStamp( strtotime( '2024-02-01 8:00AM' ) );
		$punch_data[] = $p_obj;

		$p_obj = TTnew('PunchFactory');
		$p_obj->setStatus( 20 ); //10=In, 20=Out
		$p_obj->setType( 10 ); //10=Normal, 30=Break
		$p_obj->setTimeStamp( strtotime( '2024-02-01 5:00PM' ) );
		$punch_data[] = $p_obj;

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 8:00AM' ), 20, 900 );
		$this->assertEquals( strtotime( '2024-02-01 5:00PM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 5:15PM' ), $gap['end'] );
		$this->assertEquals( 900, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 5:15PM' ), 20, 900 );
		$this->assertEquals( strtotime( '2024-02-01 5:15PM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 5:30PM' ), $gap['end'] );
		$this->assertEquals( 900, $gap['total_time'] );

		$gap = $calc_policy_obj->findPunchDataGapAfterTimeStamp( $punch_data, strtotime( '2024-02-01 5:30PM' ), 20, 900 );
		$this->assertEquals( strtotime( '2024-02-01 5:30PM' ), $gap['start'] );
		$this->assertEquals( strtotime( '2024-02-01 5:45PM' ), $gap['end'] );
		$this->assertEquals( 900, $gap['total_time'] );
	}
}

?>
