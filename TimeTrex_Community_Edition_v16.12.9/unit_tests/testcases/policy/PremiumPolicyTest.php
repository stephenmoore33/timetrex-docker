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

class PremiumPolicyTest extends PHPUnit\Framework\TestCase {
	protected $company_id = null;
	protected $legal_entity_id = null;
	protected $user_id = null;
	protected $branch_ids = null;
	protected $department_ids = null;
	protected $absence_policy_id = null;
	protected $policy_ids = [];
	protected $pay_period_schedule_id = null;
	protected $pay_period_objs = null;
	protected $pay_stub_account_link_arr = null;

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

		$dd->createPayStubAccount( $this->company_id );
		$this->createPayStubAccounts();
		//$this->createPayStubAccrualAccount();
		$dd->createPayStubAccountLink( $this->company_id );
		$this->getPayStubAccountLinkArray();

		$dd->createUserWageGroups( $this->company_id );

		$this->branch_ids[] = $dd->createBranch( $this->company_id, 10 );
		$this->branch_ids[] = $dd->createBranch( $this->company_id, 20 );

		$this->department_ids[] = $dd->createDepartment( $this->company_id, 10 );
		$this->department_ids[] = $dd->createDepartment( $this->company_id, 20 );

		$this->user_id = $dd->createUser( $this->company_id, $this->legal_entity_id, 100, null, $this->branch_ids[0] );

		//$this->createPayPeriodSchedule();
		//$this->createPayPeriods();
		//$this->getAllPayPeriods();

		$this->policy_ids['pay_formula_policy'][100] = $this->createPayFormulaPolicy( $this->company_id, 100 ); //Reg 1.0x
		$this->policy_ids['pay_formula_policy'][200] = $this->createPayFormulaPolicy( $this->company_id, 200 ); //OT 1.5x

		$this->policy_ids['pay_code'][100] = $dd->createPayCode( $this->company_id, 100, $this->policy_ids['pay_formula_policy'][100] ); //Regular
		$this->policy_ids['pay_code'][190] = $dd->createPayCode( $this->company_id, 190 ); //Lunch
		$this->policy_ids['pay_code'][192] = $dd->createPayCode( $this->company_id, 192 ); //Break
		$this->policy_ids['pay_code'][200] = $dd->createPayCode( $this->company_id, 200, $this->policy_ids['pay_formula_policy'][200] ); //Overtime1
		$this->policy_ids['pay_code'][300] = $dd->createPayCode( $this->company_id, 300 ); //Prem1
		$this->policy_ids['pay_code'][310] = $dd->createPayCode( $this->company_id, 310 ); //Prem2
		$this->policy_ids['pay_code'][900] = $dd->createPayCode( $this->company_id, 900 ); //Vacation
		$this->policy_ids['pay_code'][910] = $dd->createPayCode( $this->company_id, 910 ); //Bank
		$this->policy_ids['pay_code'][920] = $dd->createPayCode( $this->company_id, 920 ); //Sick

		$this->policy_ids['contributing_pay_code_policy'][10] = $dd->createContributingPayCodePolicy( $this->company_id, 10, [ $this->policy_ids['pay_code'][100] ] ); //Regular
		$this->policy_ids['contributing_pay_code_policy'][12] = $dd->createContributingPayCodePolicy( $this->company_id, 12, [ $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192], $this->policy_ids['pay_code'][200] ] ); //Regular+Meal/Break+Overtime
		$this->policy_ids['contributing_pay_code_policy'][14] = $dd->createContributingPayCodePolicy( $this->company_id, 14, [ $this->policy_ids['pay_code'][100], $this->policy_ids['pay_code'][190], $this->policy_ids['pay_code'][192], $this->policy_ids['pay_code'][900] ] ); //Regular+Meal/Break+Absence
		$this->policy_ids['contributing_pay_code_policy'][90] = $dd->createContributingPayCodePolicy( $this->company_id, 90, [ $this->policy_ids['pay_code'][900] ] ); //Absence
		$this->policy_ids['contributing_pay_code_policy'][99] = $dd->createContributingPayCodePolicy( $this->company_id, 99, $this->policy_ids['pay_code'] ); //All Time

		$this->policy_ids['contributing_shift_policy'][10] = $dd->createContributingShiftPolicy( $this->company_id, 10, $this->policy_ids['contributing_pay_code_policy'][10] ); //Regular
		$this->policy_ids['contributing_shift_policy'][12] = $dd->createContributingShiftPolicy( $this->company_id, 20, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$this->absence_policy_id = $dd->createAbsencePolicy( $this->company_id, 10, $this->policy_ids['pay_code'][100] );

		$this->policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][100] );
		$this->policy_ids['overtime'][] = $dd->createOverTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][200] );

		$this->assertTrue( TTUUID::isUUID( $this->company_id ) );
		$this->assertTrue( TTUUID::isUUID( $this->user_id ) );
	}

	public function tearDown(): void {
		Debug::text( 'Running tearDown(): ', __FILE__, __LINE__, __METHOD__, 10 );
	}

	function getPayStubAccountLinkArray() {
		$this->pay_stub_account_link_arr = [
				'total_gross'           => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Gross' ),
				'total_deductions'      => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Total Deductions' ),
				'employer_contribution' => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Employer Total Contributions' ),
				'net_pay'               => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 40, 'Net Pay' ),
				'regular_time'          => CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 10, 'Regular Time' ),
		];

		return true;
	}

	function createPayStubAccounts() {
		Debug::text( 'Saving.... Employee Deduction - Other', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'Other' );
		$pseaf->setOrder( 290 );

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text( 'Saving.... Employee Deduction - Other2', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'Other2' );
		$pseaf->setOrder( 291 );

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text( 'Saving.... Employee Deduction - EI', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'EI' );
		$pseaf->setOrder( 292 );

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		Debug::text( 'Saving.... Employee Deduction - CPP', __FILE__, __LINE__, __METHOD__, 10 );
		$pseaf = new PayStubEntryAccountFactory();
		$pseaf->setCompany( $this->company_id );
		$pseaf->setStatus( 10 );
		$pseaf->setType( 20 );
		$pseaf->setName( 'CPP' );
		$pseaf->setOrder( 293 );

		if ( $pseaf->isValid() ) {
			$pseaf->Save();
		}

		//Link Account EI and CPP accounts
		$pseallf = new PayStubEntryAccountLinkListFactory();
		$pseallf->getByCompanyId( $this->company_id );
		if ( $pseallf->getRecordCount() > 0 ) {
			$pseal_obj = $pseallf->getCurrent();
			$pseal_obj->setEmployeeEI( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'EI' ) );
			$pseal_obj->setEmployeeCPP( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $this->company_id, 20, 'CPP' ) );
			$pseal_obj->Save();
		}


		return true;
	}

	function createPayPeriodSchedule( $maximum_shift_time = ( 30 * 3600 ) ) {
		$ppsf = new PayPeriodScheduleFactory();

		$ppsf->setCompany( $this->company_id );
		//$ppsf->setName( 'Bi-Weekly'.rand(1000,9999) );
		$ppsf->setName( 'Bi-Weekly' );
		$ppsf->setDescription( 'Pay every two weeks' );
		$ppsf->setType( 20 );
		$ppsf->setStartWeekDay( 0 );


		$anchor_date = TTDate::getBeginWeekEpoch( TTDate::getBeginYearEpoch() ); //Start 6 weeks ago

		$ppsf->setAnchorDate( $anchor_date );

		$ppsf->setStartDayOfWeek( TTDate::getDayOfWeek( $anchor_date ) );
		$ppsf->setTransactionDate( 7 );

		$ppsf->setTransactionDateBusinessDay( true );
		$ppsf->setTimeZone( 'America/Vancouver' );

		$ppsf->setDayStartTime( 0 );
		$ppsf->setNewDayTriggerTime( ( 4 * 3600 ) );
		$ppsf->setMaximumShiftTime( $maximum_shift_time ); //Need to make this longer than 24hrs for some tests.
		$ppsf->setShiftAssignedDay( 10 );
		//$ppsf->setContinuousTime( (4*3600) );

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

	function createPayPeriods( $initial_date = false ) {
		$max_pay_periods = 35;

		$ppslf = new PayPeriodScheduleListFactory();
		$ppslf->getById( $this->pay_period_schedule_id );
		if ( $ppslf->getRecordCount() > 0 ) {
			$pps_obj = $ppslf->getCurrent();

			$end_date = null;
			for ( $i = 0; $i < $max_pay_periods; $i++ ) {
				if ( $i == 0 ) {
					if ( $initial_date !== false ) {
						$end_date = $initial_date;
					} else {
						$end_date = TTDate::getBeginWeekEpoch( TTDate::incrementDate( time(), -42, 'day' ) );
					}
				} else {
					$end_date = ( $end_date + ( ( 86400 * 14 ) ) );
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

	function createHolidayPolicy( $company_id, $type ) {
		$hpf = new HolidayPolicyFactory();
		$hpf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$hpf->setName( 'Default' );
				$hpf->setType( 10 );

				$hpf->setDefaultScheduleStatus( 10 );
				$hpf->setMinimumEmployedDays( 0 );
				$hpf->setMinimumWorkedPeriodDays( 0 );
				$hpf->setMinimumWorkedDays( 0 );
				$hpf->setAverageTimeDays( 10 );
				$hpf->setAverageTimeWorkedDays( true );
				$hpf->setIncludeOverTime( true );
				$hpf->setIncludePaidAbsenceTime( true );
				$hpf->setForceOverTimePolicy( true );

				$hpf->setMinimumTime( 0 );
				$hpf->setMaximumTime( 0 );

				$hpf->setAbsencePolicyID( $this->absence_policy_id );
				//$hpf->setRoundIntervalPolicyID( $data['round_interval_policy_id'] );

				break;
		}

		$hpf->setHolidayDisplayDays( 371 );

		if ( $hpf->isValid() ) {
			$insert_id = $hpf->Save();
			Debug::Text( 'Holiday Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Holiday Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createHoliday( $company_id, $type, $date, $holiday_policy_id ) {
		$hf = new HolidayFactory();

		switch ( $type ) {
			case 10:
				$hf->setHolidayPolicyId( $holiday_policy_id );
				$hf->setDateStamp( $date );
				$hf->setName( 'Test1' );

				break;
		}

		if ( $hf->isValid() ) {
			$insert_id = $hf->Save();
			Debug::Text( 'Holiday ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Holiday!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createSchedule( $user_id, $date_stamp, $data = null ) {
		$sf = TTnew( 'ScheduleFactory' ); /** @var ScheduleFactory $sf */
		$sf->setCompany( $this->company_id );
		$sf->setUser( $user_id );
		//$sf->setUserDateId( UserDateFactory::findOrInsertUserDate( $user_id, $date_stamp) );

		if ( isset( $data['replaced_id'] ) ) {
			$sf->setReplacedId( $data['replaced_id'] );
		}

		if ( isset( $data['status_id'] ) ) {
			$sf->setStatus( $data['status_id'] );
		} else {
			$sf->setStatus( 10 );
		}

		if ( isset( $data['schedule_policy_id'] ) ) {
			$sf->setSchedulePolicyID( $data['schedule_policy_id'] );
		}

		if ( isset( $data['absence_policy_id'] ) ) {
			$sf->setAbsencePolicyID( $data['absence_policy_id'] );
		}
		if ( isset( $data['branch_id'] ) ) {
			$sf->setBranch( $data['branch_id'] );
		}
		if ( isset( $data['department_id'] ) ) {
			$sf->setDepartment( $data['department_id'] );
		}

		if ( isset( $data['job_id'] ) ) {
			$sf->setJob( $data['job_id'] );
		}

		if ( isset( $data['job_item_id'] ) ) {
			$sf->setJobItem( $data['job_item_id'] );
		}

		if ( isset( $data['punch_tag_id'] ) ) {
			$sf->setPunchTag( $data['punch_tag_id'] );
		}

		if ( $data['start_time'] != '' ) {
			$start_time = strtotime( $data['start_time'], $date_stamp );
		}
		if ( $data['end_time'] != '' ) {
			Debug::Text( 'End Time: ' . $data['end_time'] . ' Date Stamp: ' . $date_stamp, __FILE__, __LINE__, __METHOD__, 10 );
			$end_time = strtotime( $data['end_time'], $date_stamp );
			Debug::Text( 'bEnd Time: ' . $data['end_time'] . ' - ' . TTDate::getDate( 'DATE+TIME', $data['end_time'] ), __FILE__, __LINE__, __METHOD__, 10 );
		}

		$sf->setStartTime( $start_time );
		$sf->setEndTime( $end_time );

		if ( $sf->isValid() ) {
			$sf->setEnableReCalculateDay( true );
			$insert_id = $sf->Save();
			Debug::Text( 'Schedule ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Schedule!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPunchData() {
		global $dd;

		$punch_date = $this->pay_period_objs[0]->getStartDate();
		$end_punch_date = $this->pay_period_objs[0]->getEndDate();
		$i = 0;
		while ( $punch_date <= $end_punch_date ) {
			$date_stamp = TTDate::getDate( 'DATE', $punch_date );

			//$punch_full_time_stamp = strtotime($pc_data['date_stamp'].' '.$pc_data['time_stamp']);
			$dd->createPunchPair( $this->user_id,
								  strtotime( $date_stamp . ' 08:00AM' ),
								  strtotime( $date_stamp . ' 11:00AM' ),
								  [
										  'in_type_id'    => 10,
										  'out_type_id'   => 10,
										  'branch_id'     => 0,
										  'department_id' => 0,
										  'job_id'        => 0,
										  'job_item_id'   => 0,
								  ]
			);
			$dd->createPunchPair( $this->user_id,
								  strtotime( $date_stamp . ' 11:00AM' ),
								  strtotime( $date_stamp . ' 1:00PM' ),
								  [
										  'in_type_id'    => 10,
										  'out_type_id'   => 20,
										  'branch_id'     => 0,
										  'department_id' => 0,
										  'job_id'        => 0,
										  'job_item_id'   => 0,
								  ]
			);

			$dd->createPunchPair( $this->user_id,
								  strtotime( $date_stamp . ' 2:00PM' ),
								  strtotime( $date_stamp . ' 6:00PM' ),
								  [
										  'in_type_id'    => 20,
										  'out_type_id'   => 10,
										  'branch_id'     => 0,
										  'department_id' => 0,
										  'job_id'        => 0,
										  'job_item_id'   => 0,
								  ]
			);

			$punch_date += 86400;
			$i++;
		}
		unset( $punch_options_arr, $punch_date, $user_id );
	}

	function getUserDateTotalArray( $start_date, $end_date ) {
		$udtlf = new UserDateTotalListFactory();

		$date_totals = [];

		//Get only system totals.
		//$udtlf->getByCompanyIDAndUserIdAndStatusAndStartDateAndEndDate( $this->company_id, $this->user_id, 10, $start_date, $end_date);
		$udtlf->getByCompanyIDAndUserIdAndObjectTypeAndStartDateAndEndDate( $this->company_id, $this->user_id, [ 5, 20, 30, 40, 100, 110 ], $start_date, $end_date );
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

						'start_time_stamp' => $udt_obj->getStartTimeStamp(),
						'end_time_stamp'   => $udt_obj->getEndTimeStamp(),

						//'start_time_stamp_display' => TTDate::getDate('DATE+TIME', $udt_obj->getStartTimeStamp() ),
						//'end_time_stamp_display' => TTDate::getDate('DATE+TIME', $udt_obj->getEndTimeStamp() ),

						'quantity'     => $udt_obj->getQuantity(),
						'bad_quantity' => $udt_obj->getBadQuantity(),

						'hourly_rate'             => $udt_obj->getHourlyRate(),
						'hourly_rate_with_burden' => $udt_obj->getHourlyRateWithBurden(),
						//Override only shows for SYSTEM override columns...
						//Need to check Worked overrides too.
						'tmp_override'            => $udt_obj->getOverride(),
				];
			}
		}

		return $date_totals;
	}

	function createPayCode( $company_id, $type, $pay_formula_policy_id = 0 ) {
		$pcf = TTnew( 'PayCodeFactory' ); /** @var PayCodeFactory $pcf */
		$pcf->setCompany( $company_id );

		switch ( $type ) {
			case 100:
				$pcf->setName( 'Premium1' );
				//$pcf->setRate( '1.5' );
				break;
			case 110:
				$pcf->setName( 'Premium2' );
				//$pcf->setRate( '2.0' );
				break;
			case 120:
				$pcf->setName( 'Premium3' );
				//$pcf->setRate( '2.5' );
				break;
			case 200:
				$pcf->setName( 'Premium4' );
				//$pcf->setRate( '1.5' );
				break;
		}

		$pcf->setCode( md5( $pcf->getName() ) );
		$pcf->setType( 10 ); //Paid
		$pcf->setPayFormulaPolicy( $pay_formula_policy_id );
		$pcf->setPayStubEntryAccountID( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName( $company_id, 10, 'Over Time 1' ) );

		if ( $pcf->isValid() ) {
			$insert_id = $pcf->Save();
			Debug::Text( 'Pay Code ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Code!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createPayFormulaPolicy( $company_id, $type, $accrual_policy_account_id = 0, $wage_source_contributing_shift_policy_id = 0, $time_source_contributing_shift_policy_id = 0 ) {
		$pfpf = TTnew( 'PayFormulaPolicyFactory' ); /** @var PayFormulaPolicyFactory $pfpf */
		$pfpf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$pfpf->setName( 'None ($0)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 0 );
				break;
			case 100:
				$pfpf->setName( 'Regular' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 200:
				$pfpf->setName( 'OverTime (1.5x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 1.5 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 210:
				$pfpf->setName( 'OverTime (2.0x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 2.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 220:
				$pfpf->setName( 'OverTime (2.5x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 2.5 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 510:
				$pfpf->setName( 'OverTime (4.0x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setRate( 4.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				break;
			case 1200: //Overtime averaging.
				$pfpf->setName( 'OverTime Avg (1.0x)' );
				$pfpf->setPayType( 10 ); //Pay Multiplied By Factor
				$pfpf->setWageSourceType( 30 ); //Average of contributing pay codes.
				$pfpf->setWageSourceContributingShiftPolicy( $wage_source_contributing_shift_policy_id );
				$pfpf->setTimeSourceContributingShiftPolicy( $time_source_contributing_shift_policy_id );
				$pfpf->setRate( 1.0 );
				$pfpf->setAccrualPolicyAccount( $accrual_policy_account_id );
				$pfpf->setAccrualRate( 1.0 );
				$pfpf->setAccrualBalanceThreshold( (86400 * 999) ); //Don't use default lower threshold of 0.
				break;
		}

		if ( $pfpf->isValid() ) {
			$insert_id = $pfpf->Save();
			Debug::Text( 'Pay Formula Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Pay Formula Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
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
			case 120: //AutoDeduct 1hr
				$mpf->setName( 'AutoDeduct 1hr' );
				$mpf->setType( 10 );
				$mpf->setTriggerTime( ( 3600 * 6 ) );
				$mpf->setAmount( 3600 );
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

	function createContributingShiftPolicy( $company_id, $type, $contributing_pay_code_policy_id, $holiday_policy_id = null ) {
		$cspf = TTnew( 'ContributingShiftPolicyFactory' ); /** @var ContributingShiftPolicyFactory $cspf */
		$cspf->setCompany( $company_id );

		switch ( $type ) {
			case 10:
				$cspf->setName( 'Regular Shifts' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;
			case 20:
				$cspf->setName( 'Regular Shifts + Meal/Break' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;
			case 30:
				$cspf->setName( 'Regular+Overtime' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;
			case 40:
				$cspf->setName( 'Regular+Overtime+Absence' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				break;
			case 100:
				$cspf->setName( 'Holiday (Midnight to Midnight)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '12:00AM' ) );
				$cspf->setFilterEndTime( strtotime( '11:59:59PM' ) );

				$cspf->setMon( false );
				$cspf->setTue( false );
				$cspf->setWed( false );
				$cspf->setThu( false );
				$cspf->setFri( false );
				$cspf->setSat( false );
				$cspf->setSun( false );

				$cspf->setIncludeHolidayType( 20 ); //Always on Holidays (eligible or not)
				$cspf->setIncludeShiftType( 100 ); //Partial Shifts
				break;
			case 110:
				$cspf->setName( 'Holiday (1PM to 5PM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '1:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '5:00PM' ) );

				$cspf->setMon( false );
				$cspf->setTue( false );
				$cspf->setWed( false );
				$cspf->setThu( false );
				$cspf->setFri( false );
				$cspf->setSat( false );
				$cspf->setSun( false );

				$cspf->setIncludeHolidayType( 20 ); //Always on Holidays (eligible or not)
				$cspf->setIncludeShiftType( 100 ); //Partial Shifts
				break;
			case 200:
				$cspf->setName( 'Regular+Meal/Break' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 200 ); //Full Shift (Shift Must Start & End)
				break;
			case 210: //Shift Start
				$cspf->setName( 'Shift Start (3PM to 11PM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '3:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '11:00PM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 210 ); //Full Shift (Shift Must Start)
				break;
			case 220: //Shift End
				$cspf->setName( 'Shift End (3PM to 11PM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '3:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '11:00PM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 220 ); //Full Shift (Shift Must End)
				break;
			case 230: //Majority Shift
				$cspf->setName( 'Majority Shift (3PM to 11PM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '3:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '11:00PM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 230 ); //Full Shift (Majority of Shift)
				break;
			case 231: //Majority Shift - Specific days, and spanning midnight.
				$cspf->setName( 'Majority Shift (10PM to 6AM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '10:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '6:00AM' ) );

				$cspf->setMon( false );
				$cspf->setTue( false );
				$cspf->setWed( false );
				$cspf->setThu( false );
				$cspf->setFri( false );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 230 ); //Full Shift (Majority of Shift)
				break;

			case 300: //Split Shifts (Partial w/Limits)
				$cspf->setName( 'Split w/Limit Shift (7AM to 3PM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '7:00AM' ) );
				$cspf->setFilterEndTime( strtotime( '3:00PM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 150 ); //Split Shifts (Partial w/Limits)
				$cspf->setMinimumTimeInThisShift( ( 1 * 3600 ) );
				$cspf->setMinimumTimeIntoThisShift( ( 0 * 3600 ) );
				$cspf->setMaximumTimeIntoNextShift( ( 4 * 3600 ) );
				break;
			case 301: //Split Shifts (Partial w/Limits)
				$cspf->setName( 'Split w/Limit Shift (3PM to 11PM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '3:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '11:00PM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 150 ); //Split Shifts (Partial w/Limits)
				$cspf->setMinimumTimeInThisShift( ( 1 * 3600 ) );
				$cspf->setMinimumTimeIntoThisShift( ( 4 * 3600 ) );
				$cspf->setMaximumTimeIntoNextShift( ( 4 * 3600 ) );
				break;
			case 302: //Split Shifts (Partial w/Limits)
				$cspf->setName( 'Split w/Limit Shift (11PM to 7AM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '11:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '7:00AM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 150 ); //Split Shifts (Partial w/Limits)
				$cspf->setMinimumTimeInThisShift( ( 1 * 3600 ) );
				$cspf->setMinimumTimeIntoThisShift( ( 4 * 3600 ) );
				$cspf->setMaximumTimeIntoNextShift( ( 0 * 3600 ) );
				break;
			case 303: //Split Shifts (Partial w/Limits)
				$cspf->setName( 'Split w/Limit Shift (11PM to 7AM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '11:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '7:00AM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 150 ); //Split Shifts (Partial w/Limits)
				$cspf->setMinimumTimeInThisShift( ( 1.25 * 3600 ) );
				$cspf->setMinimumTimeIntoThisShift( ( 4 * 3600 ) );
				$cspf->setMaximumTimeIntoNextShift( ( 4 * 3600 ) );
				break;
			case 304: //Split Shifts (Partial w/Limits)
				$cspf->setName( 'Split w/Limit Shift (11PM to 8AM)' );
				$cspf->setContributingPayCodePolicy( $contributing_pay_code_policy_id );

				$cspf->setFilterStartTime( strtotime( '11:00PM' ) );
				$cspf->setFilterEndTime( strtotime( '8:00AM' ) );

				$cspf->setMon( true );
				$cspf->setTue( true );
				$cspf->setWed( true );
				$cspf->setThu( true );
				$cspf->setFri( true );
				$cspf->setSat( true );
				$cspf->setSun( true );

				$cspf->setIncludeHolidayType( 10 ); //Have no effect
				$cspf->setIncludeShiftType( 150 ); //Split Shifts (Partial w/Limits)
				$cspf->setMinimumTimeInThisShift( ( 1.25 * 3600 ) );
				$cspf->setMinimumTimeIntoThisShift( ( 4 * 3600 ) );
				$cspf->setMaximumTimeIntoNextShift( ( 0 * 3600 ) );
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
			case 10: //Applies in all cases
				$ppf->setName( 'Basic (Apply Always)' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( null );
				$ppf->setEndDate( null );

				$ppf->setStartTime( null );
				$ppf->setEndTime( null );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 11: //Applies in all cases
				$ppf->setName( 'Basic (Apply Always) [B]' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( null );
				$ppf->setEndDate( null );

				$ppf->setStartTime( null );
				$ppf->setEndTime( null );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 90: //Basic Min/Max only.
				$ppf->setName( 'Min/Max Only' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( null );
				$ppf->setEndDate( null );

				$ppf->setStartTime( null );
				$ppf->setEndTime( null );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 3600 );
				$ppf->setMaximumTime( 7200 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );
				break;
			case 91: //Basic Min/Max only. as Advanced Type
				$ppf->setName( 'Min/Max Only' );
				$ppf->setType( 100 ); //Advanced Type.

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( null );
				$ppf->setEndDate( null );

				$ppf->setStartTime( null );
				$ppf->setEndTime( null );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 3600 );
				$ppf->setMaximumTime( 7200 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );
				break;
			case 95: //Basic Min/Max Only on Shift Differential and Per Punch Pair
				$ppf->setName( 'Min/Max Only' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( null );
				$ppf->setEndDate( null );

				$ppf->setStartTime( null );
				$ppf->setEndTime( null );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 3600 );
				$ppf->setMaximumTime( 3600 );
				$ppf->setMinMaxTimeType( 30 ); //Per Punch Pair

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
			case 100:
				$ppf->setName( 'Start/End Date Only' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
				$ppf->setEndDate( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ); //2nd & 3rd days.

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );
				break;
			case 110:
				$ppf->setName( 'Start/End Date+Effective Days' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
				$ppf->setEndDate( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ); //2nd & 3rd days.

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 1
						|| TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ) == 1 ) {
					$ppf->setMon( true );
				} else {
					$ppf->setMon( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 2
						|| TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ) == 2 ) {
					$ppf->setTue( true );
				} else {
					$ppf->setTue( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 3
						|| TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ) == 3 ) {
					$ppf->setWed( true );
				} else {
					$ppf->setWed( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 4
						|| TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ) == 4 ) {
					$ppf->setThu( true );
				} else {
					$ppf->setThu( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 5
						|| TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ) == 5 ) {
					$ppf->setFri( true );
				} else {
					$ppf->setFri( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 6
						|| TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ) == 6 ) {
					$ppf->setSat( true );
				} else {
					$ppf->setSat( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 0
						|| TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ) == 0 ) {
					$ppf->setSun( true );
				} else {
					$ppf->setSun( false );
				}

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 120:
				$ppf->setName( 'Time Based/Evening Shift w/Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '7:00 PM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 122:
				$ppf->setName( 'Time Based/Evening Shift w/Partial+Span Midnight' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '6:00 PM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '3:00 AM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 123:
				$ppf->setName( 'Time Based/Weekend Day Shift w/Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '7:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '7:00 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( false );
				$ppf->setTue( false );
				$ppf->setWed( false );
				$ppf->setThu( false );
				$ppf->setFri( false );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 124: //Same as above type: 122, only Advanced type.
				$ppf->setName( 'Time Based/Evening Shift w/Partial+Span Midnight' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '6:00 PM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '3:00 AM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 125: //Same as above type: 123, only Advanced type.
				$ppf->setName( 'Time Based/Weekend Day Shift w/Partial' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '7:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '7:00 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( false );
				$ppf->setTue( false );
				$ppf->setWed( false );
				$ppf->setThu( false );
				$ppf->setFri( false );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 126: //Same as above type: 122, only Advanced type.
				$ppf->setName( 'Time Based/Evening Shift w/Partial+Span Midnight' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '10:00 PM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '12:00 AM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 127: //Same as above type: 122, only Advanced type.
				$ppf->setName( 'Time Based/Evening Shift w/Partial+Span Midnight' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( false );
				$ppf->setTue( false );
				$ppf->setWed( false );
				$ppf->setThu( false );
				$ppf->setFri( false );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 128: //Same as above type: 122, only Advanced type.
				$ppf->setName( 'Time Based/Evening Shift w/Partial+Span Midnight' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '10:00 AM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 129:
				$ppf->setName( 'Effective Days Only w/Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( false );
				$ppf->setTue( false );
				$ppf->setWed( false );
				$ppf->setThu( false );
				$ppf->setFri( false );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 20 ); //Always on holidays. This is key to test for a specific bug.

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 130:
				$ppf->setName( 'Time Based/Evening Shift w/o Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '7:00 PM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( false );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 132:
				$ppf->setName( 'Time Based/Evening Shift w/o Partial+Span Midnight' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '6:00 PM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '3:00 AM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( false );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 133:
				$ppf->setName( 'Time Based/Morning Shift w/Partial' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '6:00 AM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( false );
				$ppf->setWed( false );
				$ppf->setThu( false );
				$ppf->setFri( false );
				$ppf->setSat( false );
				$ppf->setSun( false );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				break;
			case 140:
				$ppf->setName( 'Daily Hour Based' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( ( 3600 * 5 ) );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 150:
				$ppf->setName( 'Weekly Hour Based' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( ( 3600 * 9 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 160:
				$ppf->setName( 'Daily+Weekly Hour Based' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( ( 3600 * 3 ) );
				$ppf->setWeeklyTriggerTime( ( 3600 * 9 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 170:
				$ppf->setName( 'Time+Daily+Weekly Hour Based' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '7:00 PM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( ( 3600 * 5 ) );
				$ppf->setWeeklyTriggerTime( ( 3600 * 9 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				//$ppf->setExcludeDefaultBranch( FALSE );
				//$ppf->setExcludeDefaultDepartment( FALSE );
				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 200:
				$ppf->setName( 'Branch Differential' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 202:
				$ppf->setName( 'Any Branch Differential Excluding Default' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( true ); //Exclude Default.
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 10 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 203:
				$ppf->setName( 'Included Branch Differential Excluding Default' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( true ); //Exclude Default.
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 204:
				$ppf->setName( 'Excluded Branch Differential Excluding Default' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( true ); //Exclude Default.
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 30 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 210:
				$ppf->setName( 'Branch/Department Differential' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 212:
				$ppf->setName( 'Branch/Department Differential w/Minimum' );
				$ppf->setType( 20 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 3600 );
				$ppf->setMaximumTime( 3600 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				//$ppf->setJobGroupSelectionType( 10 );
				//$ppf->setJobSelectionType( 10 );
				//$ppf->setJobItemGroupSelectionType( 10 );
				//$ppf->setJobItemSelectionType( 10 );

				break;
			case 300:
				$ppf->setName( 'Meal Break' );
				$ppf->setType( 30 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );

				$ppf->setDailyTriggerTime( ( 3600 * 5 ) );
				$ppf->setMaximumNoBreakTime( ( 3600 * 5 ) );
				$ppf->setMinimumBreakTime( 1800 );

				$ppf->setMinimumTime( 1800 );
				$ppf->setMaximumTime( 1800 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 301:
				$ppf->setName( '30min Meal Break (10hr shift)' );
				$ppf->setType( 30 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );

				$ppf->setDailyTriggerTime( ( 3600 * 10 ) );
				$ppf->setMaximumNoBreakTime( ( 3600 * 10 ) );
				$ppf->setMinimumBreakTime( 1800 );

				$ppf->setMinimumTime( 1800 );
				$ppf->setMaximumTime( 1800 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 302:
				$ppf->setName( '1hr Meal Break (10hr shift)' );
				$ppf->setType( 30 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );

				$ppf->setDailyTriggerTime( ( 3600 * 10 ) );
				$ppf->setMaximumNoBreakTime( ( 3600 * 10 ) );
				$ppf->setMinimumBreakTime( 1800 );

				$ppf->setMinimumTime( 3600 );
				$ppf->setMaximumTime( 3600 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 350:
				$ppf->setName( 'Minimum Shift Time' );
				$ppf->setType( 50 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setMinimumShiftTime( ( 4 * 3600 ) );
				$ppf->setMinimumTimeBetweenShift( ( 8 * 3600 ) );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 351:
				$ppf->setName( 'Minimum Shift Time+Differential' );
				$ppf->setType( 50 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setMinimumShiftTime( ( 4 * 3600 ) );
				$ppf->setMinimumTimeBetweenShift( ( 8 * 3600 ) );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );
				break;
			case 352:
				$ppf->setName( 'Minimum Shift Time (0 Time Between Shifts)' );
				$ppf->setType( 50 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setMinimumShiftTime( ( 4 * 3600 ) );
				$ppf->setMinimumTimeBetweenShift( ( 0 * 3600 ) );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 400:
				$ppf->setName( 'Holiday (Basic)' );
				$ppf->setType( 90 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setIncludePartialPunch( true );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumNoBreakTime( 0 );
				//$ppf->setMinimumBreakTime(  0 );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 410:
				$ppf->setName( 'Start/End Date+Effective Days+Always Holiday' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
				$ppf->setEndDate( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ); //2nd & 3rd days.

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( false );
				$ppf->setTue( false );
				$ppf->setWed( false );
				$ppf->setThu( false );
				$ppf->setFri( false );
				$ppf->setSat( false );
				$ppf->setSun( false );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 20 ); //Always on holidays
				break;
			case 412:
				$ppf->setName( 'Start/End Date+Effective Days+Never Holiday' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
				$ppf->setEndDate( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) ); //2nd & 3rd days.

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 30 ); //Never on holidays
				break;
			case 414:
				$ppf->setName( 'Weekly+Never Holiday' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( ( 3600 * 40 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 30 ); //Never on Holiday
				break;
			case 500:
				$ppf->setName( 'Daily Before/After Time 8-10hrs' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( ( 8 * 3600 ) );
				$ppf->setMaximumDailyTriggerTime( ( 10 * 3600 ) );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 501:
				$ppf->setName( 'Daily Before/After Time 10-11hrs' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( ( 10 * 3600 ) );
				$ppf->setMaximumDailyTriggerTime( ( 11 * 3600 ) );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 510:
				$ppf->setName( 'Weekly Before/After Time 20-30hrs' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( ( 20 * 3600 ) );
				$ppf->setMaximumWeeklyTriggerTime( ( 30 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 511:
				$ppf->setName( 'Weekly Before/After Time 30-40hrs' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( ( 30 * 3600 ) );
				$ppf->setMaximumWeeklyTriggerTime( ( 40 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 520:
				$ppf->setName( 'Daily After 8/Weekly Before 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( ( 8 * 3600 ) );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( ( 40 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 521:
				$ppf->setName( 'Daily After 8/Weekly After 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( ( 8 * 3600 ) );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( ( 40 * 3600 ) );
				$ppf->setMaximumWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 522:
				$ppf->setName( 'Daily Before 8/Weekly After 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( ( 8 * 3600 ) );
				$ppf->setWeeklyTriggerTime( ( 40 * 3600 ) );
				$ppf->setMaximumWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 523:
				$ppf->setName( 'Weekly Before 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( ( 40 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 524:
				$ppf->setName( 'Daily Before 8/Weekly Before 40' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( '' );
				$ppf->setEndTime( '' );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( ( 8 * 3600 ) );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( ( 40 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 525:
				$ppf->setName( 'Weekly Before 40 + Differential' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 );

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '7:00 AM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( ( 40 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setIncludeHolidayType( 10 ); //No effect.
				break;
			case 600:
				$ppf->setName( 'Last second of day' );
				$ppf->setType( 10 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setStartDate( '' );
				$ppf->setEndDate( '' );

				$ppf->setStartTime( TTDate::parseDateTime( '12:00 AM' ) );
				$ppf->setEndTime( TTDate::parseDateTime( '11:59 PM' ) );

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );

				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 1 ) {
					$ppf->setMon( true );
				} else {
					$ppf->setMon( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 2 ) {
					$ppf->setTue( true );
				} else {
					$ppf->setTue( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 3 ) {
					$ppf->setWed( true );
				} else {
					$ppf->setWed( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 4 ) {
					$ppf->setThu( true );
				} else {
					$ppf->setThu( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 5 ) {
					$ppf->setFri( true );
				} else {
					$ppf->setFri( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 6 ) {
					$ppf->setSat( true );
				} else {
					$ppf->setSat( false );
				}
				if ( TTDate::getDayOfWeek( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) ) == 0 ) {
					$ppf->setSun( true );
				} else {
					$ppf->setSun( false );
				}

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day
				break;
			case 700:
				$ppf->setName( 'Advanced Active After + Differential' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setDailyTriggerTime( ( 3600 * 8 ) );
				$ppf->setWeeklyTriggerTime( 0 );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
			case 723: //Same as 724
				$ppf->setName( 'Advanced Weekly Before 40A + Diff' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( ( 40 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
			case 724: //Same as 723
				$ppf->setName( 'Advanced Weekly Before 40B + Diff' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( 0 );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( ( 40 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
			case 729:
				$ppf->setName( 'Advanced Daily Before 8/Weekly Before 40 + Diff' );
				$ppf->setType( 100 );

				$ppf->setPayType( 10 ); //Pay Multiplied by factor

				$ppf->setDailyTriggerTime( 0 );
				$ppf->setMaximumDailyTriggerTime( ( 8 * 3600 ) );
				$ppf->setWeeklyTriggerTime( 0 );
				$ppf->setMaximumWeeklyTriggerTime( ( 40 * 3600 ) );

				$ppf->setMon( true );
				$ppf->setTue( true );
				$ppf->setWed( true );
				$ppf->setThu( true );
				$ppf->setFri( true );
				$ppf->setSat( true );
				$ppf->setSun( true );

				$ppf->setIncludePartialPunch( true );
				//$ppf->setMaximumNoBreakTime( $data['maximum_no_break_time'] );
				//$ppf->setMinimumBreakTime( $data['minimum_break_time'] );

				$ppf->setMinimumTime( 0 );
				$ppf->setMaximumTime( 0 );
				$ppf->setMinMaxTimeType( 10 ); //Per Day

				$ppf->setExcludeDefaultBranch( false );
				$ppf->setExcludeDefaultDepartment( false );

				$ppf->setBranchSelectionType( 20 );
				$ppf->setDepartmentSelectionType( 20 );

				break;
		}

		$ppf->setContributingShiftPolicy( $contributing_shift_policy_id );
		$ppf->setPayCode( $pay_code_id );

		if ( $ppf->isValid() ) {
			$insert_id = $ppf->Save( false );
			Debug::Text( 'Premium Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			switch ( $type ) {
				case 95:
					Debug::Text( 'Post Save Data...', __FILE__, __LINE__, __METHOD__, 10 );
					$ppf->setBranch( [ $this->branch_ids[0] ] );
					$ppf->setDepartment( [ $this->department_ids[0] ] );
					break;
				case 200:
					Debug::Text( 'Post Save Data...', __FILE__, __LINE__, __METHOD__, 10 );
					$ppf->setBranch( [ $this->branch_ids[0] ] );
					break;
				case 203:
					Debug::Text( 'Post Save Data...', __FILE__, __LINE__, __METHOD__, 10 );
					$ppf->setBranch( [ $this->branch_ids[1] ] );
					break;
				case 204:
					Debug::Text( 'Post Save Data...', __FILE__, __LINE__, __METHOD__, 10 );
					$ppf->setBranch( [ $this->branch_ids[0] ] );
					break;
				case 210:
				case 212:
				case 351:
					Debug::Text( 'Post Save Data...', __FILE__, __LINE__, __METHOD__, 10 );
					$ppf->setBranch( [ $this->branch_ids[0] ] );
					$ppf->setDepartment( [ $this->department_ids[0] ] );
					break;
				case 700:
					Debug::Text( 'Post Save Data...', __FILE__, __LINE__, __METHOD__, 10 );
					$ppf->setBranch( [ $this->branch_ids[0] ] );
					$ppf->setDepartment( [ $this->department_ids[0] ] );
					break;
				case 723:
					Debug::Text( 'Post Save Data...', __FILE__, __LINE__, __METHOD__, 10 );
					$ppf->setBranch( [ $this->branch_ids[0] ] );
					$ppf->setDepartment( [ $this->department_ids[0] ] );
					break;
				case 724: //Same as 729.
				case 729:
					Debug::Text( 'Post Save Data...', __FILE__, __LINE__, __METHOD__, 10 );
					$ppf->setBranch( [ $this->branch_ids[1] ] );
					$ppf->setDepartment( [ $this->department_ids[1] ] );
					break;
			}

			Debug::Text( 'Post Save...', __FILE__, __LINE__, __METHOD__, 10 );
			$ppf->Save();

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Premium Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	function createOverTimePolicy( $company_id, $type, $contributing_shift_policy_id = 0, $pay_code_id = 0 ) {
		$otpf = TTnew( 'OverTimePolicyFactory' ); /** @var OverTimePolicyFactory $otpf */
		$otpf->setCompany( $company_id );

		switch ( $type ) {
			case 100:
				$otpf->setName( 'Daily (>0hrs)' );
				$otpf->setType( 10 );
				$otpf->setTriggerTime( ( 3600 * 0 ) );
				$otpf->setContributingShiftPolicy( $contributing_shift_policy_id );
				$otpf->setPayCode( $pay_code_id );
				//$otpf->setRate( '1.0' );
				//$otpf->setPayStubEntryAccountId( CompanyDeductionFactory::getPayStubEntryAccountByCompanyIDAndTypeAndFuzzyName($company_id, 10, 'Over Time 2') );

				//$otpf->setAccrualPolicyId( $accrual_policy_id );
				//$otpf->setAccrualRate( '1.0' );
				break;
		}

		if ( $otpf->isValid() ) {
			$insert_id = $otpf->Save();
			Debug::Text( 'Overtime Policy ID: ' . $insert_id, __FILE__, __LINE__, __METHOD__, 10 );

			return $insert_id;
		}

		Debug::Text( 'Failed Creating Overtime Policy!', __FILE__, __LINE__, __METHOD__, 10 );

		return false;
	}

	/*
	 Tests:
		No Premium
		Min/Max time.
		Day Based
		Day Based+Effective Days
		Time Based w/No Partial punches
		Time Based w/Partial punches
		Daily Hour Based
		Weekly Hour Based
		Daily+Weekly Hour Based
		Time+Hour Based Premium
		Shift Differential Branch
		Shift Differential Department
		Shift Differential Branch+Department
		Shift Differential Job
		Shift Differential Task
		Shift Differential Job+Task
		Meal Break
		Advanced Time+Hour+Branch+Department+Job
	*/

	/**
	 * @group PremiumPolicy_testNoPremiumPolicyA
	 */
	function testNoPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate'] );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate_with_burden'] );

		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][1]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyA
	 */
	function testMinMaxPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate'] );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate_with_burden'] );

		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][1]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][2]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyB
	 */
	function testMinMaxPremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 9:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 1.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1.5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyC
	 */
	function testMinMaxPremiumPolicyC() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyD
	 */
	function testMinMaxPremiumPolicyD() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyE
	 */
	function testMinMaxPremiumPolicyE() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:15AM' ),
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
							  strtotime( $date_stamp . ' 8:30AM' ),
							  strtotime( $date_stamp . ' 8:45AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 0.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 900 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 900 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 900 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2700 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyF
	 */
	function testMinMaxPremiumPolicyF() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 90, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:30AM' ),
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
							  strtotime( $date_stamp . ' 9:00AM' ),
							  strtotime( $date_stamp . ' 11:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.5 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1800 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 5400 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyA2
	 */
	function testMinMaxPremiumPolicyA2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 0.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyB2
	 */
	function testMinMaxPremiumPolicyB2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 9:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 1.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyC2
	 */
	function testMinMaxPremiumPolicyC2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyD2
	 */
	function testMinMaxPremiumPolicyD2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyE2
	 */
	function testMinMaxPremiumPolicyE2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:15AM' ),
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
							  strtotime( $date_stamp . ' 8:30AM' ),
							  strtotime( $date_stamp . ' 8:45AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 0.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 900 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 900 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 900 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2700 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyF2
	 */
	function testMinMaxPremiumPolicyF2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 91, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:30AM' ),
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
							  strtotime( $date_stamp . ' 9:00AM' ),
							  strtotime( $date_stamp . ' 11:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.5 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1800 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 5400 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyPerPunchPairA
	 */
	function testMinMaxPremiumPolicyPerPunchPairA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 95, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate'] );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate_with_burden'] );

		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][1]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//No Premiums because Branch/Department doesn't match.
//		//Premium
//		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 40 ); //40=Premium
//		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1 * 3600) );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['hourly_rate'], 21.50 );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyPerPunchPairB
	 */
	function testMinMaxPremiumPolicyPerPunchPairB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 95, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:30AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate'] );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate_with_burden'] );

		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][1]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][2]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinMaxPremiumPolicyPerPunchPairC
	 */
	function testMinMaxPremiumPolicyPerPunchPairC() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 95, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:30AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:30AM' ),
							  strtotime( $date_stamp . ' 9:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0, //This should not match premium criteria.
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00AM' ),
							  strtotime( $date_stamp . ' 9:30AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:30AM' ),
							  strtotime( $date_stamp . ' 10:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 2.0 * 3600 ) );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate'] );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate_with_burden'] );

		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][1]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][2]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['object_type_id'] );              //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.5 * 3600 ) );
		//$this->assertEquals( 21.50, $udt_arr[$date_epoch][3]['hourly_rate'] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );
		////Regular Time-- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][4]['object_type_id'] );              //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 0.5 * 3600 ) );
		//$this->assertEquals( 21.50, $udt_arr[$date_epoch][4]['hourly_rate'] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1.0 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][3]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 1 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][4]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		////Premium
		//$this->assertEquals( 40, $udt_arr[$date_epoch][5]['object_type_id'] );              //40=Premium
		//$this->assertEquals( $udt_arr[$date_epoch][5]['pay_code_id'], $policy_ids['pay_code'][0] );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 1 * 3600 ) );
		//$this->assertEquals( 21.50, $udt_arr[$date_epoch][5]['hourly_rate'] );
		//$this->assertEquals( $udt_arr[$date_epoch][5]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );
		////Premium
		//$this->assertEquals( 40, $udt_arr[$date_epoch][6]['object_type_id'] );              //40=Premium
		//$this->assertEquals( $udt_arr[$date_epoch][6]['pay_code_id'], $policy_ids['pay_code'][0] );
		//$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], ( 1 * 3600 ) );
		//$this->assertEquals( 21.50, $udt_arr[$date_epoch][6]['hourly_rate'] );
		//$this->assertEquals( $udt_arr[$date_epoch][6]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][5]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][5]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 2 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][5]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Make sure no other hours
		$this->assertCount( 6, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testEffectiveDaysOnlyPremiumPolicyA
	 */
	function testEffectiveDaysOnlyPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 129, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testEffectiveDaysOnlyPremiumPolicyB
	 */
	function testEffectiveDaysOnlyPremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 129, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium Time = NONE

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDatePremiumPolicyA
	 */
	function testDatePremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testEffectiveDatePremiumPolicyA
	 */
	function testEffectiveDatePremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyA
	 */
	function testTimeBasedPartialPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 120, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 10:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyB
	 */
	function testTimeBasedPartialPremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 122, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00PM' ),
							  strtotime( $date_stamp2 . ' 2:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyB2
	 */
	function testTimeBasedPartialPremiumPolicyB2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 124, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00PM' ),
							  strtotime( $date_stamp2 . ' 2:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyC
	 */
	function testTimeBasedPartialPremiumPolicyC() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 122, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
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
							  strtotime( $date_stamp . ' 6:30PM' ),
							  strtotime( $date_stamp2 . ' 1:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyC2
	 */
	function testTimeBasedPartialPremiumPolicyC2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 124, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:00PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
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
							  strtotime( $date_stamp . ' 6:30PM' ),
							  strtotime( $date_stamp2 . ' 1:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyD
	 */
	function testTimeBasedPartialPremiumPolicyD() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 122, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 4:00AM' ),
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
							  strtotime( $date_stamp . ' 5:00PM' ),
							  strtotime( $date_stamp . ' 8:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 2 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyD2
	 */
	function testTimeBasedPartialPremiumPolicyD2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 124, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 4:00AM' ),
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
							  strtotime( $date_stamp . ' 5:00PM' ),
							  strtotime( $date_stamp . ' 8:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 2 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyE
	 */
	function testTimeBasedPartialPremiumPolicyE() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 122, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 5:00PM' ),
							  strtotime( $date_stamp2 . ' 4:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 11 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyE2
	 */
	function testTimeBasedPartialPremiumPolicyE2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 124, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 5:00PM' ),
							  strtotime( $date_stamp2 . ' 4:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 11 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyF
	 */
	function testTimeBasedPartialPremiumPolicyF() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 123, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ), //Friday evening
							  strtotime( $date_stamp2 . ' 9:00AM' ), //Saturday morning.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 15 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 15 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyF2
	 */
	function testTimeBasedPartialPremiumPolicyF2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 125, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ), //Friday evening
							  strtotime( $date_stamp2 . ' 9:00AM' ), //Saturday morning.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 15 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 15 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyF3
	 */
	function testTimeBasedPartialPremiumPolicyF3() {
		//Test creating punches in one timezone, then recalculating them in another timezone to make sure they are proper.
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		TTDate::setTimeZone( 'America/Vancouver' );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 125, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 5:00AM' ),
							  strtotime( $date_stamp . ' 8:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 15 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 15 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 12 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		$ulf = TTNew( 'UserListFactory' ); /** @var UserListFactory $ulf */
		$ulf->getById( $this->user_id );
		if ( $ulf->getRecordCount() > 0 ) {
			TTDate::setTimeZone( 'America/New_York' );
			UserDateTotalFactory::reCalculateDay( $ulf->getCurrent(), $date_epoch, true );
			TTDate::setTimeZone( 'America/Vancouver' );
		} else {
			$this->assertTrue( false );
		}

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 15 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 15 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 12 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyG
	 */
	function testTimeBasedPartialPremiumPolicyG() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 123, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 7, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ), //Sunday evening
							  strtotime( $date_stamp2 . ' 9:00AM' ), //Monday morning.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 15 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 15 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyG2
	 */
	function testTimeBasedPartialPremiumPolicyG2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 125, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 7, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ), //Sunday evening
							  strtotime( $date_stamp2 . ' 9:00AM' ), //Monday morning.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 15 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 15 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedPartialPremiumPolicyH
	 */
	function testTimeBasedPartialPremiumPolicyH() {
		global $dd;

		$this->createPayPeriodSchedule( ( 12 * 3600 ) ); //Must be 24hrs or less to test this case properly.
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 133, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '05-Nov-2023' ) ); //DST transition date.
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		//Test punching in before the premium start time, and out after the premium end time.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:00PM' ), //Friday evening
							  strtotime( $date_stamp2 . ' 6:00AM' ), //Saturday morning.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedNoPartialPremiumPolicyA
	 */
	function testTimeBasedNoPartialPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 130, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 10:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedNoPartialPremiumPolicyB
	 */
	function testTimeBasedNoPartialPremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 125, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 1:00PM' ),
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
							  strtotime( $date_stamp . ' 2:00PM' ),
							  strtotime( $date_stamp . ' 7:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedNoPartialPremiumPolicyC
	 */
	function testTimeBasedNoPartialPremiumPolicyC() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:30PM' ),
							  strtotime( $date_stamp . ' 5:30PM' ),
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
							  strtotime( $date_stamp . ' 7:00PM' ),
							  strtotime( $date_stamp . ' 10:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}


	/**
	 * @group PremiumPolicy_testTimeBasedNoPartialPremiumPolicyD
	 */
	function testTimeBasedNoPartialPremiumPolicyD() {
		//Put a 5hr gap between the two punch pairs to signify a new shift starting, so premium does kick in.
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
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

		//This starts a new shift due to the gap between the NORMAL OUT and NORMAL IN punches.
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00PM' ),
							  strtotime( $date_stamp . ' 10:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedNoPartialPremiumPolicyE
	 */
	function testTimeBasedNoPartialPremiumPolicyE() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 5:00AM' ),
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
							  strtotime( $date_stamp . ' 7:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//v9.2.X changed to ensure that if Partial Punches was DISABLED, the entire shift had to fall within the differential times.
		//Premium
		//$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 40 ); //40=Premium
		//$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (4*3600) );
		//Premium
		//$this->assertEquals( $udt_arr[$date_epoch][4]['object_type_id'], 40 ); //40=Premium
		//$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (4*3600) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedNoPartialPremiumPolicyF
	 */
	function testTimeBasedNoPartialPremiumPolicyF() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
							  strtotime( $date_stamp2 . ' 5:00AM' ),
							  strtotime( $date_stamp2 . ' 9:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//v9.2.X changed to ensure that if Partial Punches was DISABLED, the entire shift had to fall within the differential times.
		//Premium
		//$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 40 ); //40=Premium
		//$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (4*3600) );
		//Premium
		//$this->assertEquals( $udt_arr[$date_epoch][4]['object_type_id'], 40 ); //40=Premium
		//$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		//$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (4*3600) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeBasedNoPartialPremiumPolicyG
	 */
	function testTimeBasedNoPartialPremiumPolicyG() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );

		//6P - 3A
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 132, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_stamp2 = TTDate::getDate( 'DATE', TTDate::incrementDate( $date_epoch, 1, 'day' ) );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp . ' 10:00PM' ),
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
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 4 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDailyHourPremiumPolicyA
	 */
	function testDailyHourPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 140, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testWeeklyHourPremiumPolicyA
	 */
	function testWeeklyHourPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 150, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDailyWeeklyHourPremiumPolicyA
	 */
	function testDailyWeeklyHourPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 160, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testTimeDailyWeeklyHourPremiumPolicyA
	 */
	function testTimeDailyWeeklyHourPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 170, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 10:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testBranchDifferentialPremiumPolicyA
	 */
	function testBranchDifferentialPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 200, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][5]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][5]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 4 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 6, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testBranchDifferentialPremiumPolicyB
	 */
	function testBranchDifferentialPremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 202, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0], //Default
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1], //Not default.
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testBranchDifferentialPremiumPolicyC
	 */
	function testBranchDifferentialPremiumPolicyC() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 203, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0], //Default
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1], //Not default.
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testBranchDifferentialPremiumPolicyD
	 */
	function testBranchDifferentialPremiumPolicyD() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 204, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0], //Default
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1], //Not default.
									  'department_id' => 0,
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testBranchDepartmentDifferentialPremiumPolicyA
	 */
	function testBranchDepartmentDifferentialPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 100, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 110, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 210, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][5]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][5]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 4 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 6, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testBranchDepartmentDifferentialPremiumPolicyB
	 */
	function testBranchDepartmentDifferentialPremiumPolicyB() {
		//
		//Test where premium policy differential DOES match.
		//
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 212, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testBranchDepartmentDifferentialPremiumPolicyC
	 */
	function testBranchDepartmentDifferentialPremiumPolicyC() {
		//
		//Test where premium policy differential DOES NOT match.
		//
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 212, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => 0, //Should not match
									  'department_id' => 0, //Should not match
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMealPremiumPolicyA
	 */
	function testMealPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 3:00PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (7*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (7*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (0.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	/**
	 * @group PremiumPolicy_testMealPremiumPolicyB
	 */
	function testMealPremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( 	$this->user_id,
								 strtotime($date_stamp.' 8:00AM'),
								 strtotime($date_stamp.' 12:00PM'),
								 array(
										 'in_type_id' => 10,
										 'out_type_id' => 10,
										 'branch_id' => 0,
										 'department_id' => 0,
										 'job_id' => 0,
										 'job_item_id' => 0,
								 ),
								 TRUE
		);

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( 	$this->user_id,
								 strtotime($date_stamp.' 12:45PM'),
								 strtotime($date_stamp.' 3:45PM'),
								 array(
										 'in_type_id' => 10,
										 'out_type_id' => 10,
										 'branch_id' => 0,
										 'department_id' => 0,
										 'job_id' => 0,
										 'job_item_id' => 0,
								 ),
								 TRUE
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (7*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	/**
	 * @group PremiumPolicy_testMealPremiumPolicyC
	 */
	function testMealPremiumPolicyC() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$this->policy_ids['pay_formula_policy'][1200] = $this->createPayFormulaPolicy( $this->company_id, 1200, 0, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['contributing_shift_policy'][12] ); //OT Averaging 1.5x

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $this->policy_ids['pay_formula_policy'][1200] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								$this->policy_ids['overtime'], //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								 strtotime($date_stamp.' 8:00AM'),
								 strtotime($date_stamp.' 5:00PM'),
								 array(
										 'in_type_id' => 10,
										 'out_type_id' => 10,
										 'branch_id' => 0,
										 'department_id' => 0,
										 'job_id' => 0,
										 'job_item_id' => 0,
								 ),
								 TRUE
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (9*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (8*3600) );
		$this->assertEquals( $udt_arr[$date_epoch][1]['hourly_rate'], 21.50 );
		//OverTime
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 30 ); //30=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][200] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		$this->assertEquals( $udt_arr[$date_epoch][2]['hourly_rate'], 32.25 );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (0.5*3600) );
		$this->assertEquals( $udt_arr[$date_epoch][3]['hourly_rate'], 22.6944 ); //This is the average of the Reg+OT divided by hours worked.

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}

	/**
	 * @group PremiumPolicy_testMealPremiumPolicyD1
	 */
	function testMealPremiumPolicyD1() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 301, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								 strtotime($date_stamp.' 8:00AM'),
								 strtotime($date_stamp.' 8:00PM'),
								 array(
										 'in_type_id' => 10,
										 'out_type_id' => 10,
										 'branch_id' => 0,
										 'department_id' => 0,
										 'job_id' => 0,
										 'job_item_id' => 0,
								 ),
								 TRUE
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );
		//Premium Time3
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (0.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}

	/**
	 * @group PremiumPolicy_testMealPremiumPolicyD2
	 */
	function testMealPremiumPolicyD2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 300, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 302, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								 strtotime($date_stamp.' 8:00AM'),
								 strtotime($date_stamp.' 8:00PM'),
								 array(
										 'in_type_id' => 10,
										 'out_type_id' => 10,
										 'branch_id' => 0,
										 'department_id' => 0,
										 'job_id' => 0,
										 'job_item_id' => 0,
								 ),
								 TRUE
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (12*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (12*3600) );
		//Premium Time3A
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (0.5*3600) );
		//Premium Time3B
		$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (0.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}
	/**
	 * @group PremiumPolicy_testMinimumShiftTimeA
	 */
	function testMinimumShiftTimeA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 350, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00AM' ),
							  strtotime( $date_stamp . ' 8:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:30PM' ),
							  strtotime( $date_stamp . ' 11:30PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 2 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );


		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinimumShiftTimeB
	 */
	function testMinimumShiftTimeB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//$policy_ids['overtime'][] = $dd->createOverTimePolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] ); //Daily >8

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 352, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								$this->policy_ids['overtime'], //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Test case where we just switch into overtime so the amount of time in OT is less than the minimum shift.
		// This helps to test the getShiftData() function.

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
							  strtotime( $date_stamp . ' 11:30AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.5 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][0]['start_time_stamp'], strtotime( $date_stamp . ' 6:00AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][0]['end_time_stamp'], strtotime( $date_stamp . ' 3:00PM' ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][1]['start_time_stamp'], strtotime( $date_stamp . ' 11:30AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][1]['end_time_stamp'], strtotime( $date_stamp . ' 2:30PM' ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][2]['start_time_stamp'], strtotime( $date_stamp . ' 6:00AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][2]['end_time_stamp'], strtotime( $date_stamp . ' 11:00AM' ) );
		//Overtime
		$this->assertEquals( 30, $udt_arr[$date_epoch][3]['object_type_id'] );              //30=Overtime
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][200] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.5 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][3]['start_time_stamp'], strtotime( $date_stamp . ' 2:30PM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][3]['end_time_stamp'], strtotime( $date_stamp . ' 3:00PM' ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 0.50 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][4]['start_time_stamp'], strtotime( $date_stamp . ' 3:00PM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][4]['end_time_stamp'], strtotime( $date_stamp . ' 3:30PM' ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinimumShiftTimeB
	 */
	/* //No longer handling Shift Differential in Minimum Shift premium policies, use Contributing Shifts for that instead.
	function testMinimumShiftTimeB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 351 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ) +86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 8:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (2*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00AM'),
								strtotime($date_stamp.' 1:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (4.5*3600) );
		//Regular Time1
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2*3600) );
		//Regular Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (2.5*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 9:30PM'),
								strtotime($date_stamp.' 11:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (6.5*3600) );
		//Regular Time1
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Regular Time2
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (4*3600) );
		//Premium Time1
		$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (2*3600) );
		//Premium Time2
		$this->assertEquals( $udt_arr[$date_epoch][4]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], (2*3600) );


		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 5 );


		//
		// Day2
		//
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 11:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		//$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		//
		// Day3
		//
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' );
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 8:00AM'),
								strtotime($date_stamp.' 11:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (1*3600) );
		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		return TRUE;
	}
	*/

	/**
	 * @group PremiumPolicy_testMinimumShiftTimeC1
	 */
	function testMinimumShiftTimeC1() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 352, $this->policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								$this->policy_ids['overtime'], //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][0]['start_time_stamp'], strtotime( $date_stamp . ' 6:00AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][0]['end_time_stamp'], strtotime( $date_stamp . ' 1:00PM' ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][1]['start_time_stamp'], strtotime( $date_stamp . ' 12:00PM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][1]['end_time_stamp'], strtotime( $date_stamp . ' 1:00PM' ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][2]['start_time_stamp'], strtotime( $date_stamp . ' 6:00AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][2]['end_time_stamp'], strtotime( $date_stamp . ' 11:00AM' ) );
		//Lunch Time
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['object_type_id'] );              //100=Lunch
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][190] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( -1 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][3]['start_time_stamp'], strtotime( $date_stamp . ' 11:00AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][3]['end_time_stamp'], strtotime( $date_stamp . ' 12:00PM' ) );


		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testMinimumShiftTimeC2
	 */
	function testMinimumShiftTimeC2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['meal'][] = $this->createMealPolicy( $this->company_id, 120, $this->policy_ids['pay_code'][190], 100 );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 352, $this->policy_ids['contributing_shift_policy'][10], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								$policy_ids['meal'], //Meal
								null, //Exception
								null, //Holiday
								$this->policy_ids['overtime'], //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00AM' ),
							  strtotime( $date_stamp . ' 1:30PM' ), //30 minute after the meal deduction occurs, so there should be a regular time UDT record for just 1 min.
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6.5 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][0]['start_time_stamp'], strtotime( $date_stamp . ' 6:00AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][0]['end_time_stamp'], strtotime( $date_stamp . ' 1:30PM' ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][1]['start_time_stamp'], strtotime( $date_stamp . ' 12:00PM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][1]['end_time_stamp'], strtotime( $date_stamp . ' 1:30PM' ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][2]['start_time_stamp'], strtotime( $date_stamp . ' 6:00AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][2]['end_time_stamp'], strtotime( $date_stamp . ' 11:00AM' ) );
		//Lunch Time
		$this->assertEquals( 100, $udt_arr[$date_epoch][3]['object_type_id'] );              //100=Lunch
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][190] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( -1 * 3600 ) );
		$this->assertEquals( $udt_arr[$date_epoch][3]['start_time_stamp'], strtotime( $date_stamp . ' 11:00AM' ) );
		$this->assertEquals( $udt_arr[$date_epoch][3]['end_time_stamp'], strtotime( $date_stamp . ' 12:00PM' ) );


		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testHolidayPremiumPolicyA
	 */
	function testHolidayPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 400, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 12:00PM' ),
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

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:45PM' ),
							  strtotime( $date_stamp . ' 3:45PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );


		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 4 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}


	/**
	 * @group PremiumPolicy_testHolidayPremiumPolicyB
	 */
	function testHolidayPremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch1 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp1 = TTDate::getDate( 'DATE', $date_epoch1 );

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 400, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp1 . ' 6:00PM' ),
							  strtotime( $date_stamp . ' 2:00AM' ),
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


		$udt_arr = $this->getUserDateTotalArray( $date_epoch1, $date_epoch1 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch1][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch1][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], ( 2 * 3600 ) );


		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch1] );

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 2:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}


	/**
	 * @group ContributingShiftHolidayPremiumPolicyA
	 */
	function testContributingShiftHolidayPremiumPolicyA() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch1 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp1 = TTDate::getDate( 'DATE', $date_epoch1 );

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$date_epoch3 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp3 = TTDate::getDate( 'DATE', $date_epoch3 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$this->policy_ids['contributing_shift_policy'][100] = $this->createContributingShiftPolicy( $this->company_id, 100, $this->policy_ids['contributing_pay_code_policy'][12], $policy_ids['holiday'][0] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][100], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp1 . ' 6:00PM' ),
							  strtotime( $date_stamp . ' 2:00AM' ),
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


		$udt_arr = $this->getUserDateTotalArray( $date_epoch1, $date_epoch1 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch1][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch1][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], ( 2 * 3600 ) );


		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch1] );

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 2:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                                                                                                                   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );                                                                                                                                  //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );                                                                                                                                  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// Punch Pair 3
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp2 . ' 6:00PM' ),
							  strtotime( $date_stamp3 . ' 2:00AM' ),
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
		$udt_arr = $this->getUserDateTotalArray( $date_epoch2, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );                                                                                                                                  //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch2][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch2][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch2][1]['object_type_id'] );                                                                                                                                 //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch2][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch2][1]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch2] );

		return true;
	}

	/**
	 * @group ContributingShiftHolidayPremiumPolicyB
	 */
	function testContributingShiftHolidayPremiumPolicyB() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch1 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp1 = TTDate::getDate( 'DATE', $date_epoch1 );

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$this->policy_ids['contributing_shift_policy'][100] = $this->createContributingShiftPolicy( $this->company_id, 100, $this->policy_ids['contributing_pay_code_policy'][12], $policy_ids['holiday'][0] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][100], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp1 . ' 3:00PM' ),
							  strtotime( $date_stamp1 . ' 11:00PM' ),
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


		$udt_arr = $this->getUserDateTotalArray( $date_epoch1, $date_epoch1 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['type_id'], 40 );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], (2*3600) );


		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch1] );

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                                                                                                                   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );                                                                                                                                  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// Punch Pair 3
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp2 . ' 3:00PM' ),
							  strtotime( $date_stamp2 . ' 11:00PM' ),
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
		$udt_arr = $this->getUserDateTotalArray( $date_epoch2, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );                                                                                                                                  //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch2][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch2][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch2][1]['object_type_id'] );                                                                                                                                 //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch2][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch2][1]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch2] );

		return true;
	}

	/**
	 * @group ContributingShiftHolidayPremiumPolicyC
	 */
	function testContributingShiftHolidayPremiumPolicyC() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch1 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp1 = TTDate::getDate( 'DATE', $date_epoch1 );

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$this->policy_ids['contributing_shift_policy'][110] = $this->createContributingShiftPolicy( $this->company_id, 110, $this->policy_ids['contributing_pay_code_policy'][12], $policy_ids['holiday'][0] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][110], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp1 . ' 1:00PM' ),
							  strtotime( $date_stamp1 . ' 9:00PM' ),
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


		$udt_arr = $this->getUserDateTotalArray( $date_epoch1, $date_epoch1 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['type_id'], 40 );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], (2*3600) );


		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch1] );

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 9:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                                                                                                                   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );                                                                                                                                  //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );                                                                                                                                  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// Punch Pair 3
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp2 . ' 1:00PM' ),
							  strtotime( $date_stamp2 . ' 9:00PM' ),
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
		$udt_arr = $this->getUserDateTotalArray( $date_epoch2, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );                                                                                                                                  //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch2][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch2][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch2][1]['object_type_id'] );                                                                                                                                 //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch2][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch2][1]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch2] );

		return true;
	}

	/**
	 * @group ContributingShiftHolidayPremiumPolicyD
	 */
	function testContributingShiftHolidayPremiumPolicyD() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch1 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp1 = TTDate::getDate( 'DATE', $date_epoch1 );

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$this->policy_ids['contributing_shift_policy'][110] = $this->createContributingShiftPolicy( $this->company_id, 110, $this->policy_ids['contributing_pay_code_policy'][12], $policy_ids['holiday'][0] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][110], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp1 . ' 11:00AM' ),
							  strtotime( $date_stamp1 . ' 7:00PM' ),
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


		$udt_arr = $this->getUserDateTotalArray( $date_epoch1, $date_epoch1 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['type_id'], 40 );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], (2*3600) );


		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch1] );

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00AM' ),
							  strtotime( $date_stamp . ' 7:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                                                                                                                   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );                                                                                                                                  //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );                                                                                                                                  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// Punch Pair 3
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp2 . ' 11:00AM' ),
							  strtotime( $date_stamp2 . ' 7:00PM' ),
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
		$udt_arr = $this->getUserDateTotalArray( $date_epoch2, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );                                                                                                                                  //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch2][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch2][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch2][1]['object_type_id'] );                                                                                                                                 //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch2][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch2][1]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch2] );

		return true;
	}

	/**
	 * @group ContributingShiftHolidayPremiumPolicyE
	 */
	function testContributingShiftHolidayPremiumPolicyE() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch1 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp1 = TTDate::getDate( 'DATE', $date_epoch1 );

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$this->policy_ids['contributing_shift_policy'][110] = $this->createContributingShiftPolicy( $this->company_id, 110, $this->policy_ids['contributing_pay_code_policy'][12], $policy_ids['holiday'][0] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][110], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp1 . ' 8:00AM' ),
							  strtotime( $date_stamp1 . ' 4:00PM' ),
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


		$udt_arr = $this->getUserDateTotalArray( $date_epoch1, $date_epoch1 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 8 * 3600 ) );

		//Premium Time1
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['status_id'], 10 );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['type_id'], 40 );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], (2*3600) );


		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch1] );

		//
		// Punch Pair 2
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                                                                                                                   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 3 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                                                                                                  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );


		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );                                                                                                                                  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// Punch Pair 3
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp2 . ' 8:00AM' ),
							  strtotime( $date_stamp2 . ' 4:00PM' ),
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
		$udt_arr = $this->getUserDateTotalArray( $date_epoch2, $date_epoch2 );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch2][0]['object_type_id'] );                                                                                                                                  //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch2][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch2][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch2][1]['object_type_id'] );                                                                                                                                 //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch2][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch2][1]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch2] );

		return true;
	}


	/**
	 * @group ContributingShiftHolidayPremiumPolicyF
	 */
	function testContributingShiftHolidayPremiumPolicyF() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		//Test ContributingShiftPolicy with partial days and overtime policies, which has caused PHP FATAL errors in the past.

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch1 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp1 = TTDate::getDate( 'DATE', $date_epoch1 );

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['contributing_shift_policy'][110] = $this->createContributingShiftPolicy( $this->company_id, 110, $this->policy_ids['contributing_pay_code_policy'][12], $policy_ids['holiday'][0] ); //Regular+Meal/Break

		$policy_ids['overtime'][] = $this->createOverTimePolicy( $this->company_id, 100, $policy_ids['contributing_shift_policy'][110], $policy_ids['pay_code'][2] );

		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][110], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								$policy_ids['overtime'], //OT
								null, //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp1 . ' 4:30PM' ),
							  strtotime( $date_stamp . ' 1:30PM' ),
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


		$udt_arr = $this->getUserDateTotalArray( $date_epoch1, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['status_id'] );
		$this->assertEquals( 10, $udt_arr[$date_epoch1][0]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][0]['total_time'], ( 21 * 3600 ) );

		//Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 0.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch1][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch1][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][1]['total_time'], ( 20.5 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch1][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch1][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], ( 13.5 * 3600 ) );

		//OverTime1
		$this->assertEquals( 10, $udt_arr[$date_epoch1][2]['status_id'] );
		$this->assertEquals( 30, $udt_arr[$date_epoch1][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch1][2]['total_time'], ( 0.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch1] );

		return true;
	}


	/**
	 * @group PremiumPolicy_testHolidayDatePremiumPolicyA
	 */
	function testHolidayDatePremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 410, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );


		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testHolidayDatePremiumPolicyB
	 */
	function testHolidayDatePremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		//Holiday
		$policy_ids['holiday'][] = $this->createHolidayPolicy( $this->company_id, 10 );
		$this->createHoliday( $this->company_id, 10, $date_epoch, $policy_ids['holiday'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 412, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								$policy_ids['holiday'], //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null,
								null,
								$policy_ids['holiday'],
								null,
								$policy_ids['premium'],
								null,
								[ $this->user_id ] );


		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testWeeklyHourNeverHolidayPremiumPolicyA
	 */
	function testWeeklyHourNeverHolidayPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 414, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day4
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day5
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day6
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}


	/**
	 * @group PremiumPolicy_testDailyHourBeforePremiumPolicyA
	 */
	function testDailyHourBeforePremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 500, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 501, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 8:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 12 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 2 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testWeeklyHourBeforePremiumPolicyA
	 */
	function testWeeklyHourBeforePremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 510, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 511, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day4
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 3 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		//
		// Day5
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day6
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testLastSecondOfDayDatePremiumPolicyA
	 */
	function testLastSecondOfDayDatePremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 600, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 6:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDailyAndWeeklyHourBeforeAfterPremiumPolicy
	 */
	function testDailyAndWeeklyHourBeforeAfterPremiumPolicy() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 520, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 521, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 522, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day4
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day5
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Premium Time3
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		//
		// Day6
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time3
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testAdvancedActiveAfterWithDifferentialA
	 */
	/* //Contributing shifts handles this now.
	function testAdvancedActiveAfterWithDifferentialA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		//Test to make sure active after Daily time includes all worked time, not just time matching the differential criteria.
		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][]  = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][]  = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][]  = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 520, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 521, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 522, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL, //Meal
									NULL, //Exception
									NULL, //Holiday
									NULL, //OT
									$policy_ids['premium'], //Premium
									NULL, //Round
									array($this->user_id), //Users
									NULL, //Break
									NULL, //Accrual
									NULL, //Expense
									NULL, //Absence
									$this->policy_ids['regular'] //Regular
									);
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 700 );

		//Create Policy Group
		$dd->createPolicyGroup( 	$this->company_id,
									NULL,
									NULL,
									NULL,
									NULL,
									$policy_ids['premium'],
									NULL,
									array($this->user_id) );

		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = $this->pay_period_objs[0]->getStartDate() +86400;
		$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 6:00AM'),
								strtotime($date_stamp.' 9:00AM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (3*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (3*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 2 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 11:00AM'),
								strtotime($date_stamp.' 1:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => 0,
											'department_id' => 0,
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (5.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (3*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 3 );

		$dd->createPunchPair( 	$this->user_id,
								strtotime($date_stamp.' 3:30PM'),
								strtotime($date_stamp.' 6:30PM'),
								array(
											'in_type_id' => 10,
											'out_type_id' => 10,
											'branch_id' => $this->branch_ids[0],
											'department_id' => $this->department_ids[0],
											'job_id' => 0,
											'job_item_id' => 0,
										),
								TRUE
								);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( $udt_arr[$date_epoch][0]['object_type_id'], 5 ); //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], (8.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][1]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], (2.5*3600) );
		//Regular Time
		$this->assertEquals( $udt_arr[$date_epoch][2]['object_type_id'], 20 ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (6*3600) );
		//Premium Time
		$this->assertEquals( $udt_arr[$date_epoch][3]['object_type_id'], 40 ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], (0.5*3600) );

		//Make sure no other hours
		$this->assertEquals( count($udt_arr[$date_epoch]), 4 );

		return TRUE;
	}
	*/

	/**
	 * @group PremiumPolicy_testDSTA
	 */
	function testDSTA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 126, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = $this->pay_period_objs[0]->getStartDate() +(86400*2);
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '10-Mar-2013' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '11-Mar-2013' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 6:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTA2
	 */
	function testDSTA2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 126, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = $this->pay_period_objs[0]->getStartDate() +(86400*2);
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '10-Mar-2013' ) );              //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTA3
	 */
	function testDSTA3() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 126, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = $this->pay_period_objs[0]->getStartDate() +(86400*2);
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '11-Mar-2013' ) );              //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTA4
	 */
	function testDSTA4() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 128, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = $this->pay_period_objs[0]->getStartDate() +(86400*2);
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '10-Mar-2013' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '11-Mar-2013' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:30PM' ),
							  strtotime( $date_stamp2 . ' 9:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 11 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 9.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTA5
	 */
	function testDSTA5() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 127, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// March
		//
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '08-Mar-2015' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '08-Mar-2015' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00AM' ),
							  strtotime( $date_stamp2 . ' 2:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// November
		//
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '01-Nov-2015' ) );  //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '01-Nov-2015' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00AM' ),
							  strtotime( $date_stamp2 . ' 2:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTA6
	 */
	function testDSTA6() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 127, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// March
		//
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '06-Mar-2015' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '07-Mar-2015' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) ); //9hr day in total, minus 1hr time change.
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );       //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );       //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3 * 3600 ) ); //Premium Policy is only active on Sat&Sun, so only 3hrs are on Sat.

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );



		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '07-Mar-2015' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '08-Mar-2015' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) ); //9hr day in total, minus 1hr time change.
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );       //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );       //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );



		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '08-Mar-2015' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '09-Mar-2015' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) ); //9hr day in total, minus 1hr time change.
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );       //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );       //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// November
		//
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '30-Oct-2015' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '31-Oct-2015' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) ); //9hr day in total, plus 1hr time change.
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );        //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );        //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );



		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '31-Oct-2015' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '01-Nov-2015' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) ); //9hr day in total, plus 1hr time change.
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );        //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );        //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 10 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );



		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '01-Nov-2015' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '02-Nov-2015' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) ); //9hr day in total, plus 1hr time change.
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );        //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );        //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTB
	 */
	function testDSTB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 126, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = $this->pay_period_objs[0]->getStartDate() +(86400*2);
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '02-Nov-2013' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '03-Nov-2013' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 6:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTB2
	 */
	function testDSTB2() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 126, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = $this->pay_period_objs[0]->getStartDate() +(86400*2);
		//$date_epoch = strtotime('02-Nov-2013'); //Use current year
		//$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '02-Nov-2013' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );  //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTB3
	 */
	function testDSTB3() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 126, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//$date_epoch = $this->pay_period_objs[0]->getStartDate() +(86400*2);
		//$date_epoch = strtotime('02-Nov-2013'); //Use current year
		//$date_stamp = TTDate::getDate('DATE', $date_epoch );

		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '03-Nov-2013' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );  //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] ); //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] ); //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTB4
	 */
	function testDSTB4() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime( '01-Jan-2013' ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 128, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//$date_epoch = $this->pay_period_objs[0]->getStartDate() +(86400*2);
		$date_epoch = TTDate::getMiddleDayEpoch( strtotime( '03-Nov-2013' ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( strtotime( '04-Nov-2013' ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 10:30PM' ),
							  strtotime( $date_stamp2 . ' 9:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );   //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );  //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 11 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );  //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 9.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDSTZ1
	 */
	function testDSTZ1() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( TTDate::getBeginYearEpoch( time() ) );
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 127, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// March
		//
		$date_epoch = TTDate::getMiddleDayEpoch( ( TTDate::getMiddleDayEpoch( strtotime( 'Second Sunday March 0' ) ) - 86400 ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( ( TTDate::getMiddleDayEpoch( strtotime( 'Second Sunday March 0' ) ) ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                              			//5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );                                                //9hr day in total, minus 1hr time change.
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );                                                      //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );                                                      //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// November
		//
		$date_epoch = TTDate::getMiddleDayEpoch( ( TTDate::getMiddleDayEpoch( strtotime( 'First Sunday November 0' ) ) - 86400 ) ); //Use current year
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = TTDate::getMiddleDayEpoch( ( TTDate::getMiddleDayEpoch( strtotime( 'First Sunday November 0' ) ) ) ); //Use current year
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );                                                //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) ); //9hr day in total, plus 1hr time change.
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );        //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );        //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 10 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDailyAndWeeklyBeforePremiumPolicyA
	 */
	function testDailyAndWeeklyBeforePremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 523, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 524, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 10 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 10 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 10 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		//
		// Day4
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		//
		// Day5 (a)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 9:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 1.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1.5 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		//
		// Day5 (a)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:00PM' ),
							  strtotime( $date_stamp . ' 3:30PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 5.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3.5 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.5 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 1.5 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][5]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][5]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 0.5 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][6]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][6]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][6]['total_time'], ( 1.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 7, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDailyAndWeeklyBeforePremiumPolicyB
	 */
	function testDailyAndWeeklyBeforePremiumPolicyB() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 723, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 729, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day4
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 4:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day5 (a)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 9:30AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 1.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 1.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day5 (a)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:00PM' ),
							  strtotime( $date_stamp . ' 3:30PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => $this->department_ids[1],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 5.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3.5 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.5 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 1.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testDailyAndWeeklyBeforePremiumPolicyC
	 */
	function testDailyAndWeeklyBeforePremiumPolicyC() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 723, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 724, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day4
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 6 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (a)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 5:00AM' ),
							  strtotime( $date_stamp . ' 7:30AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 2.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.5 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (b)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:30AM' ),
							  strtotime( $date_stamp . ' 9:00AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => $this->department_ids[1],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 4.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.5 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1.5 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (c)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:30AM' ),
							  strtotime( $date_stamp . ' 4:30PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => $this->department_ids[1],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][3]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7.0 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 1.5 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][5]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][5]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][5]['total_time'], ( 2.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 6, $udt_arr[$date_epoch] );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//

		return true;
	}

	/**
	 * @group PremiumPolicy_testDailyAndWeeklyBeforePremiumPolicyD
	 */
	function testDailyAndWeeklyBeforePremiumPolicyD() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 723, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 724, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][1] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		//$date_epoch = TTDate::getBeginWeekEpoch( time() );
		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 6:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day4
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 8:00AM' ),
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 6 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (a)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 5:00AM' ),
							  strtotime( $date_stamp . ' 7:30AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[0],
									  'department_id' => $this->department_ids[0],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 2.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.5 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (b)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:30AM' ),
							  strtotime( $date_stamp . ' 9:30AM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => $this->department_ids[1],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 4.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 2.5 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1.5 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2.5 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//
		//
		// Day5 (c)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:30AM' ),
							  strtotime( $date_stamp . ' 4:30PM' ),
							  [
									  'in_type_id'    => 10,
									  'out_type_id'   => 10,
									  'branch_id'     => $this->branch_ids[1],
									  'department_id' => $this->department_ids[1],
									  'job_id'        => 0,
									  'job_item_id'   => 0,
							  ],
							  true
		);

		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11.5 * 3600 ) );
		////Regular Time
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.5 * 3600 ) );
		////Regular Time
		//$this->assertEquals( 20, $udt_arr[$date_epoch][3]['object_type_id'] );              //20=Regular
		//$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 7.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 9.0 * 3600 ) );

		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 1.5 * 3600 ) );
		//Premium Time2
		$this->assertEquals( 40, $udt_arr[$date_epoch][4]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][4]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][4]['total_time'], ( 2.5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 5, $udt_arr[$date_epoch] );

		//
		// This is a special case where the premium time trigger must match *exactly* what the premium policy specifies to test < vs <=
		//

		return true;
	}

	/**
	 * @group PremiumPolicy_testWeeklyBeforeAndDifferentialPremiumPolicyA
	 */
	function testWeeklyBeforeAndDifferentialPremiumPolicyA() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 525, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);


		//
		// Day1
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 10 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 10 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day4
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 9:00AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day5 (a)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:30AM' ),
							  strtotime( $date_stamp . ' 10:30AM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		//
		// Day5 (a)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:30PM' ),
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
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4.0 * 3600 ) );
		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4.5 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 0.5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}


	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeA
	 */
	function testContributingShiftIncludeShiftTypeA() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][230] = $this->createContributingShiftPolicy( $this->company_id, 230, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][230], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeA2
	 */
	function testContributingShiftIncludeShiftTypeA2() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][230] = $this->createContributingShiftPolicy( $this->company_id, 230, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][230], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 12:00PM' ),
							  strtotime( $date_stamp . ' 8:00PM' ),
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
		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeA3
	 */
	function testContributingShiftIncludeShiftTypeA3() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][230] = $this->createContributingShiftPolicy( $this->company_id, 230, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][230], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeA4
	 */
	function testContributingShiftIncludeShiftTypeA4() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][230] = $this->createContributingShiftPolicy( $this->company_id, 231, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][230], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00PM' ), //10PM - 6AM is premium filter times.
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeA4B
	 */
	function testContributingShiftIncludeShiftTypeA4B() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime('01-Aug-21') ); //Can't be anywhere near DST changeover, otherwise it will cause day total time to change by one hour.
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][230] = $this->createContributingShiftPolicy( $this->company_id, 231, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][230], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00PM' ), //10PM - 6AM is premium filter times.
							  strtotime( $date_stamp2 . ' 5:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00PM' ),
							  strtotime( $date_stamp2 . ' 5:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		//
		// Day3 (Fri)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00PM' ),
							  strtotime( $date_stamp2 . ' 5:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day4 (Sat)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 7, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00PM' ),
							  strtotime( $date_stamp2 . ' 5:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeA4C
	 */
	function testContributingShiftIncludeShiftTypeA4C() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods( strtotime('01-Aug-21') ); //Can't be anywhere near DST changeover, otherwise it will cause day total time to change by one hour.
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][230] = $this->createContributingShiftPolicy( $this->company_id, 231, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][230], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Day 1 (Evening)
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00PM' ), //10PM - 6AM is premium filter times.
							  strtotime( $date_stamp2 . ' 1:00AM' ),
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
							  strtotime( $date_stamp2 . ' 1:00AM' ), //10PM - 6AM is premium filter times.
							  strtotime( $date_stamp2 . ' 5:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8 * 3600 ) );

		////Premium Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4 * 3600 ) );
		////Premium Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		//$this->assertEquals( 40, $udt_arr[$date_epoch][3]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 4 * 3600 ) );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		//
		// Day2
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00PM' ),
							  strtotime( $date_stamp . ' 11:30PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 2.5 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.5 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		//
		// Day3
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 5:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		//
		// Day4 (Sat) (Morning)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 7, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 1:00AM' ),
							  strtotime( $date_stamp . ' 5:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );

		$this->assertCount( 2, $udt_arr[$date_epoch] );


		//
		// Day4 (Sat) (Evening)
		//
		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 7, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 9:00PM' ),
							  strtotime( $date_stamp2 . ' 5:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );
		//Premium Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 8 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeB
	 */
	function testContributingShiftIncludeShiftTypeB() {
		//Test TieBreaker on Majority Shift.
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][230] = $this->createContributingShiftPolicy( $this->company_id, 230, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][230], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00AM' ),
							  strtotime( $date_stamp . ' 7:00PM' ),
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
		//Premium Time1 - TieBreaker goes to In punch, so no premium applied.
//		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8*3600) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeB2
	 */
	function testContributingShiftIncludeShiftTypeB2() {
		//Test TieBreaker on Majority Shift.
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][230] = $this->createContributingShiftPolicy( $this->company_id, 230, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][230], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		//Premium Time1 - TieBreaker goes to In punch, so premium *IS* applied.
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeC1
	 */
	function testContributingShiftIncludeShiftTypeC1() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][210] = $this->createContributingShiftPolicy( $this->company_id, 210, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][210], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeC2
	 */
	function testContributingShiftIncludeShiftTypeC2() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][210] = $this->createContributingShiftPolicy( $this->company_id, 210, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][210], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:30PM' ),
							  strtotime( $date_stamp . ' 11:30PM' ),
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
		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeC3
	 */
	function testContributingShiftIncludeShiftTypeC3() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][210] = $this->createContributingShiftPolicy( $this->company_id, 210, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][210], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:30PM' ),
							  strtotime( $date_stamp . ' 10:30PM' ),
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
		//Premium Time1 - Started outside shift, no premium.
//		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8*3600) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeD1
	 */
	function testContributingShiftIncludeShiftTypeD1() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][220] = $this->createContributingShiftPolicy( $this->company_id, 210, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][220], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeD2
	 */
	function testContributingShiftIncludeShiftTypeD2() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][220] = $this->createContributingShiftPolicy( $this->company_id, 220, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][220], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 2:30PM' ),
							  strtotime( $date_stamp . ' 10:30PM' ),
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
		//Premium Time1
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeD3
	 */
	function testContributingShiftIncludeShiftTypeD3() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][220] = $this->createContributingShiftPolicy( $this->company_id, 220, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][220], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:30PM' ),
							  strtotime( $date_stamp . ' 11:30PM' ),
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
		//Premium Time1 - Started outside shift, no premium.
//		$this->assertEquals( $udt_arr[$date_epoch][2]['status_id'], 10 );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['type_id'], 40 );
//		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], (8*3600) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeShift1
	 */
	function testContributingShiftIncludeShiftTypeShift1() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][301] = $this->createContributingShiftPolicy( $this->company_id, 301, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][302] = $this->createContributingShiftPolicy( $this->company_id, 302, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 999, $this->policy_ids['contributing_shift_policy'][12], $this->policy_ids['pay_code'][100] ); //Regular Time (Catch All)
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][300], $policy_ids['pay_code'][0] );
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 21, $this->policy_ids['contributing_shift_policy'][301], $policy_ids['pay_code'][1] );
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 22, $this->policy_ids['contributing_shift_policy'][302], $policy_ids['pay_code'][2] );

		$policy_ids['premium'] = [];
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][220], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00AM' ),
							  strtotime( $date_stamp . ' 2:45PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7.75 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00AM' ),
							  strtotime( $date_stamp . ' 3:15PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.25 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.25 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00AM' ),
							  strtotime( $date_stamp . ' 6:45PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 11.75 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00AM' ),
							  strtotime( $date_stamp . ' 7:00PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4.00 * 3600 ) );
		//Regular Time (2)
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00AM' ),
							  strtotime( $date_stamp . ' 7:15PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.25 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4.25 * 3600 ) );
		//Regular Time (2)
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );



		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:15AM' ),
							  strtotime( $date_stamp . ' 2:00PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7.00 * 3600 ) );
		//Regular Time (Catch All)
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.75 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );



		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 7, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:00AM' ),
							  strtotime( $date_stamp . ' 1:45PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6.75 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeShift2
	 */
	function testContributingShiftIncludeShiftTypeShift2() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][301] = $this->createContributingShiftPolicy( $this->company_id, 301, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][302] = $this->createContributingShiftPolicy( $this->company_id, 302, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][300], $policy_ids['pay_code'][0] );
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 21, $this->policy_ids['contributing_shift_policy'][301], $policy_ids['pay_code'][1] );
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 22, $this->policy_ids['contributing_shift_policy'][302], $policy_ids['pay_code'][2] );

		$policy_ids['premium'] = [];
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][220], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$policy_ids['regular'] //Regular
		);

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 10:45PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7.75 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp . ' 11:15PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.25 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.25 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp2 . ' 2:45AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11.75 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][1] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 2.75 * 3600 ) );
		////Regular Time B -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 9.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 11.75 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );


		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp2 . ' 3:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.00 * 3600 ) );
		////Regular Time 1 -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		////Regular Time 1B -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3.00 * 3600 ) );
		//Regular Time 1
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4.00 * 3600 ) );
		//Regular Time 2
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 3:00PM' ),
							  strtotime( $date_stamp2 . ' 3:15AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.25 * 3600 ) );
		////Regular Time 1 -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		////Regular Time 1B -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3.25 * 3600 ) );
		//Regular Time 1
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4.25 * 3600 ) );
		//Regular Time 2
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeShift3
	 */
	function testContributingShiftIncludeShiftTypeShift3() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][301] = $this->createContributingShiftPolicy( $this->company_id, 301, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][302] = $this->createContributingShiftPolicy( $this->company_id, 302, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][300], $policy_ids['pay_code'][0] );
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 21, $this->policy_ids['contributing_shift_policy'][301], $policy_ids['pay_code'][1] );
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 22, $this->policy_ids['contributing_shift_policy'][302], $policy_ids['pay_code'][2] );

		$policy_ids['premium'] = [];
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][220], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$policy_ids['regular'] //Regular
		);

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 6:45AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 7.75 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		////Regular Time B -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 6.75 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 7.75 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 7:00AM' ),
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

		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		////Regular Time B -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 2, $udt_arr[$date_epoch] );



		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 2, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 7:15AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 8.25 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		////Regular Time B -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.00 * 3600 ) );
		//Regular Time 2
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 0.25 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );



		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 3, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 10:45AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 11.75 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		////Regular Time B -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.00 * 3600 ) );
		//Regular Time 2
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3.75 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 4, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );


		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 11:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.00 * 3600 ) );
		////Regular Time
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		////Regular Time B
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.00 * 3600 ) );
		//Regular Time 2
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );


		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 5, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 6, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 11:00PM' ),
							  strtotime( $date_stamp2 . ' 11:15AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 12.25 * 3600 ) );
		////Regular Time -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 1.00 * 3600 ) );
		////Regular Time B -- This was compacted out with compactUserDateTotalDataBasedOnTimeStamps()
		//$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		//$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		//$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 7.00 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 8.00 * 3600 ) );
		//Regular Time 2
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 4.25 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeShift4
	 */
	function testContributingShiftIncludeShiftTypeShift4() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][300] = $this->createContributingShiftPolicy( $this->company_id, 300, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][301] = $this->createContributingShiftPolicy( $this->company_id, 301, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][302] = $this->createContributingShiftPolicy( $this->company_id, 302, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 20, $this->policy_ids['contributing_shift_policy'][300], $policy_ids['pay_code'][0] );
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 21, $this->policy_ids['contributing_shift_policy'][301], $policy_ids['pay_code'][1] );
		$policy_ids['regular'][] = $dd->createRegularTimePolicy( $this->company_id, 22, $this->policy_ids['contributing_shift_policy'][302], $policy_ids['pay_code'][2] );

		$policy_ids['premium'] = [];
		//$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][220], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$policy_ids['regular'] //Regular
		);

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );
		$date_epoch2 = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' ) );
		$date_stamp2 = TTDate::getDate( 'DATE', $date_epoch2 );

		//
		// Punch Pair 1
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 7:00AM' ),
							  strtotime( $date_stamp . ' 3:00PM' ),
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

		//
		// Punch Pair 1 -- Test with split shifts still meeting the critera.
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 6:30PM' ),
							  strtotime( $date_stamp . ' 11:00PM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 4.50 * 3600 ) );
		//Regular Time B
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 8.00 * 3600 ) );
		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group PremiumPolicy_testContributingShiftIncludeShiftTypeShift5
	 */
	function testContributingShiftIncludeShiftTypeShift5() {
		if ( getTTProductEdition() == TT_PRODUCT_COMMUNITY ) {
			return true;
		}

		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();


		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 120, $policy_ids['pay_formula_policy'][0] );

		$this->policy_ids['contributing_shift_policy'][302] = $this->createContributingShiftPolicy( $this->company_id, 303, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break
		$this->policy_ids['contributing_shift_policy'][303] = $this->createContributingShiftPolicy( $this->company_id, 304, $this->policy_ids['contributing_pay_code_policy'][12] ); //Regular+Meal/Break

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][302], $policy_ids['pay_code'][1] );
		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 11, $this->policy_ids['contributing_shift_policy'][303], $policy_ids['pay_code'][2] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								null, //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = ( TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 0, 'day' ) );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		//
		// Punch Pair 1 -- Test with split shifts still meeting the critera.
		//
		$dd->createPunchPair( $this->user_id,
							  strtotime( $date_stamp . ' 5:00AM' ),
							  strtotime( $date_stamp . ' 11:00AM' ),
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
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 6 * 3600 ) );
		//Regular Time
		$this->assertEquals( 10, $udt_arr[$date_epoch][1]['status_id'] );
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 6 * 3600 ) );
		//Premium 3
		$this->assertEquals( 10, $udt_arr[$date_epoch][2]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][2] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 3.00 * 3600 ) );
		//Premium 2
		$this->assertEquals( 10, $udt_arr[$date_epoch][3]['status_id'] );
		$this->assertEquals( 40, $udt_arr[$date_epoch][3]['type_id'] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['pay_code_id'], $policy_ids['pay_code'][1] );
		$this->assertEquals( $udt_arr[$date_epoch][3]['total_time'], ( 2.00 * 3600 ) );

		//Make sure no other hours
		$this->assertCount( 4, $udt_arr[$date_epoch] );

		return true;
	}

	/**
	 * @group testContributingShiftgetFilterTimeStampRanges
	 */
	function testContributingShiftgetFilterTimeStampRanges() {
		$cspf = new ContributingShiftPolicyFactory();
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 0, $filter_time_stamp_ranges );


		//Test every day in the week.
		$cspf = new ContributingShiftPolicyFactory();
		$cspf->setSun( true );
		$cspf->setMon( true );
		$cspf->setTue( true );
		$cspf->setWed( true );
		$cspf->setThu( true );
		$cspf->setFri( true );
		$cspf->setSat( true );
		$cspf->setIncludeHolidayType( 10 ); //Have no effect
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 7, $filter_time_stamp_ranges );
		$valid_arr = [
				0 =>
						[
								'start_time_stamp' => 1623567600,
								'end_time_stamp'   => 1623567600,
						],
				1 =>
						[
								'start_time_stamp' => 1623654000,
								'end_time_stamp'   => 1623654000,
						],
				2 =>
						[
								'start_time_stamp' => 1623740400,
								'end_time_stamp'   => 1623740400,
						],
				3 =>
						[
								'start_time_stamp' => 1623826800,
								'end_time_stamp'   => 1623826800,
						],
				4 =>
						[
								'start_time_stamp' => 1623913200,
								'end_time_stamp'   => 1623913200,
						],
				5 =>
						[
								'start_time_stamp' => 1623999600,
								'end_time_stamp'   => 1623999600,
						],
				6 =>
						[
								'start_time_stamp' => 1624086000,
								'end_time_stamp'   => 1624086000,
						],
		];
		$this->assertEquals( $valid_arr, $filter_time_stamp_ranges );


		//Test filter start/end dates.
		$cspf = new ContributingShiftPolicyFactory();
		$cspf->setFilterStartDate( strtotime('13-Jun-21') );
		$cspf->setFilterEndDate( strtotime('19-Jun-21') );
		$cspf->setSun( true );
		$cspf->setMon( true );
		$cspf->setTue( true );
		$cspf->setWed( true );
		$cspf->setThu( true );
		$cspf->setFri( true );
		$cspf->setSat( true );
		$cspf->setIncludeHolidayType( 10 ); //Have no effect
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 7, $filter_time_stamp_ranges );
		$valid_arr = [
				0 =>
						[
								'start_time_stamp' => 1623567600,
								'end_time_stamp'   => 1623567600,
						],
				1 =>
						[
								'start_time_stamp' => 1623654000,
								'end_time_stamp'   => 1623654000,
						],
				2 =>
						[
								'start_time_stamp' => 1623740400,
								'end_time_stamp'   => 1623740400,
						],
				3 =>
						[
								'start_time_stamp' => 1623826800,
								'end_time_stamp'   => 1623826800,
						],
				4 =>
						[
								'start_time_stamp' => 1623913200,
								'end_time_stamp'   => 1623913200,
						],
				5 =>
						[
								'start_time_stamp' => 1623999600,
								'end_time_stamp'   => 1623999600,
						],
				6 =>
						[
								'start_time_stamp' => 1624086000,
								'end_time_stamp'   => 1624086000,
						],
		];
		$this->assertEquals( $valid_arr, $filter_time_stamp_ranges );


		//Test filter start/end dates.
		$cspf = new ContributingShiftPolicyFactory();
		$cspf->setFilterStartDate( strtotime('10-Jun-21') );
		$cspf->setFilterEndDate( strtotime('21-Jun-21') );
		$cspf->setSun( true );
		$cspf->setMon( true );
		$cspf->setTue( true );
		$cspf->setWed( true );
		$cspf->setThu( true );
		$cspf->setFri( true );
		$cspf->setSat( true );
		$cspf->setIncludeHolidayType( 10 ); //Have no effect
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 7, $filter_time_stamp_ranges );
		$valid_arr = [
				0 =>
						[
								'start_time_stamp' => 1623567600,
								'end_time_stamp'   => 1623567600,
						],
				1 =>
						[
								'start_time_stamp' => 1623654000,
								'end_time_stamp'   => 1623654000,
						],
				2 =>
						[
								'start_time_stamp' => 1623740400,
								'end_time_stamp'   => 1623740400,
						],
				3 =>
						[
								'start_time_stamp' => 1623826800,
								'end_time_stamp'   => 1623826800,
						],
				4 =>
						[
								'start_time_stamp' => 1623913200,
								'end_time_stamp'   => 1623913200,
						],
				5 =>
						[
								'start_time_stamp' => 1623999600,
								'end_time_stamp'   => 1623999600,
						],
				6 =>
						[
								'start_time_stamp' => 1624086000,
								'end_time_stamp'   => 1624086000,
						],
		];
		$this->assertEquals( $valid_arr, $filter_time_stamp_ranges );


		//Test filter start/end dates.
		$cspf = new ContributingShiftPolicyFactory();
		$cspf->setFilterStartDate( strtotime('14-Jun-21') );
		$cspf->setFilterEndDate( strtotime('18-Jun-21') );
		$cspf->setSun( true );
		$cspf->setMon( true );
		$cspf->setTue( true );
		$cspf->setWed( true );
		$cspf->setThu( true );
		$cspf->setFri( true );
		$cspf->setSat( true );
		$cspf->setIncludeHolidayType( 10 ); //Have no effect
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 5, $filter_time_stamp_ranges );
		$valid_arr = [
				0 =>
						[
								'start_time_stamp' => 1623654000,
								'end_time_stamp'   => 1623654000,
						],
				1 =>
						[
								'start_time_stamp' => 1623740400,
								'end_time_stamp'   => 1623740400,
						],
				2 =>
						[
								'start_time_stamp' => 1623826800,
								'end_time_stamp'   => 1623826800,
						],
				3 =>
						[
								'start_time_stamp' => 1623913200,
								'end_time_stamp'   => 1623913200,
						],
				4 =>
						[
								'start_time_stamp' => 1623999600,
								'end_time_stamp'   => 1623999600,
						],
		];
		$this->assertEquals( $valid_arr, $filter_time_stamp_ranges );


		//Test filter start/end dates.
		$cspf = new ContributingShiftPolicyFactory();
		$cspf->setFilterStartDate( strtotime('10-Jun-21') );
		$cspf->setFilterEndDate( strtotime('14-Jun-21') );
		$cspf->setSun( true );
		$cspf->setMon( true );
		$cspf->setTue( true );
		$cspf->setWed( true );
		$cspf->setThu( true );
		$cspf->setFri( true );
		$cspf->setSat( true );
		$cspf->setIncludeHolidayType( 10 ); //Have no effect
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 2, $filter_time_stamp_ranges );
		$valid_arr = [
				0 =>
						[
								'start_time_stamp' => 1623567600,
								'end_time_stamp'   => 1623567600,
						],
				1 =>
						[
								'start_time_stamp' => 1623654000,
								'end_time_stamp'   => 1623654000,
						],

		];
		$this->assertEquals( $valid_arr, $filter_time_stamp_ranges );


		//Test filter start/end dates.
		$cspf = new ContributingShiftPolicyFactory();
		$cspf->setFilterStartDate( strtotime('18-Jun-21') );
		$cspf->setFilterEndDate( strtotime('21-Jun-21') );
		$cspf->setSun( true );
		$cspf->setMon( true );
		$cspf->setTue( true );
		$cspf->setWed( true );
		$cspf->setThu( true );
		$cspf->setFri( true );
		$cspf->setSat( true );
		$cspf->setIncludeHolidayType( 10 ); //Have no effect
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 2, $filter_time_stamp_ranges );
		$valid_arr = [
				0 =>
						[
								'start_time_stamp' => 1623999600,
								'end_time_stamp'   => 1623999600,
						],
				1 =>
						[
								'start_time_stamp' => 1624086000,
								'end_time_stamp'   => 1624086000,
						],
		];
		$this->assertEquals( $valid_arr, $filter_time_stamp_ranges );


		//Test filter start/end times.
		$cspf = new ContributingShiftPolicyFactory();
		$cspf->setFilterStartDate( strtotime('13-Jun-21') );
		$cspf->setFilterEndDate( strtotime('19-Jun-21') );
		$cspf->setFilterStartTime( strtotime('10-Jun-21 6:00AM') );
		$cspf->setFilterEndTime( strtotime('10-Jun-21 6:00PM') );
		$cspf->setSun( true );
		$cspf->setMon( true );
		$cspf->setTue( true );
		$cspf->setWed( true );
		$cspf->setThu( true );
		$cspf->setFri( true );
		$cspf->setSat( true );
		$cspf->setIncludeHolidayType( 10 ); //Have no effect
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 7, $filter_time_stamp_ranges );
		$valid_arr = [
				0 =>
						[
								'start_time_stamp' => 1623589200,
								'end_time_stamp'   => 1623632400,
						],
				1 =>
						[
								'start_time_stamp' => 1623675600,
								'end_time_stamp'   => 1623718800,
						],
				2 =>
						[
								'start_time_stamp' => 1623762000,
								'end_time_stamp'   => 1623805200,
						],
				3 =>
						[
								'start_time_stamp' => 1623848400,
								'end_time_stamp'   => 1623891600,
						],
				4 =>
						[
								'start_time_stamp' => 1623934800,
								'end_time_stamp'   => 1623978000,
						],
				5 =>
						[
								'start_time_stamp' => 1624021200,
								'end_time_stamp'   => 1624064400,
						],
				6 =>
						[
								'start_time_stamp' => 1624107600,
								'end_time_stamp'   => 1624150800,
						],
		];
		$this->assertEquals( $valid_arr, $filter_time_stamp_ranges );


		//Test filter start/end times.
		$cspf = new ContributingShiftPolicyFactory();
		$cspf->setFilterStartDate( strtotime('13-Jun-21') );
		$cspf->setFilterEndDate( strtotime('19-Jun-21') );
		$cspf->setFilterStartTime( strtotime('10-Jun-21 6:00PM') );
		$cspf->setFilterEndTime( strtotime('10-Jun-21 6:00AM') );
		$cspf->setSun( false );
		$cspf->setMon( true );
		$cspf->setTue( false );
		$cspf->setWed( true );
		$cspf->setThu( false );
		$cspf->setFri( true );
		$cspf->setSat( false );
		$cspf->setIncludeHolidayType( 10 ); //Have no effect
		$filter_time_stamp_ranges = iterator_to_array( $cspf->getFilterTimeStampRanges( strtotime('13-Jun-21'), strtotime('19-Jun-21') ) ); //Sun -> Sat
		$this->assertCount( 3, $filter_time_stamp_ranges );
		$valid_arr = [
				0 =>
						array (
								'start_time_stamp' => 1623718800,
								'end_time_stamp' => 1623762000,
						),
				1 =>
						array (
								'start_time_stamp' => 1623891600,
								'end_time_stamp' => 1623934800,
						),
				2 =>
						array (
								'start_time_stamp' => 1624064400,
								'end_time_stamp' => 1624107600,
						),
		];
		$this->assertEquals( $valid_arr, $filter_time_stamp_ranges );
	}

	function testPremiumPolicyOnWeekWithOnlyScheduleAbsence() {
		global $dd;

		$this->createPayPeriodSchedule();
		$this->createPayPeriods();
		$this->getAllPayPeriods();

		$policy_ids['pay_formula_policy'][] = $this->policy_ids['pay_formula_policy'][100]; //Reg1.0

		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 100, $policy_ids['pay_formula_policy'][0] );
		$policy_ids['pay_code'][] = $this->createPayCode( $this->company_id, 110, $policy_ids['pay_formula_policy'][0] );

		$policy_ids['premium'][] = $this->createPremiumPolicy( $this->company_id, 10, $this->policy_ids['contributing_shift_policy'][12], $policy_ids['pay_code'][0] );

		//Create Policy Group
		$dd->createPolicyGroup( $this->company_id,
								null, //Meal
								null, //Exception
								null, //Holiday
								null, //OT
								$policy_ids['premium'], //Premium
								null, //Round
								[ $this->user_id ], //Users
								null, //Break
								null, //Accrual
								null, //Expense
								[ $this->absence_policy_id ], //Absence
								$this->policy_ids['regular'] //Regular
		);

		$date_epoch = TTDate::incrementDate( TTDate::getMiddleDayEpoch( $this->pay_period_objs[0]->getStartDate() ), 1, 'day' );
		$date_stamp = TTDate::getDate( 'DATE', $date_epoch );

		$schedule_id = $this->createSchedule( $this->user_id, $date_epoch, [
				'status_id'          => 20, //Absence
				'absence_policy_id'  => $this->absence_policy_id,
				'schedule_policy_id' => TTUUID::getZeroID(),
				'start_time'         => '8:00AM',
				'end_time'           => '5:00PM',
		] );


		$udt_arr = $this->getUserDateTotalArray( $date_epoch, $date_epoch );
		//print_r($udt_arr);

		//Total Time
		$this->assertEquals( 5, $udt_arr[$date_epoch][0]['object_type_id'] );               //5=System Total
		$this->assertEquals( $udt_arr[$date_epoch][0]['pay_code_id'], TTUUID::getZeroID() );
		$this->assertEquals( $udt_arr[$date_epoch][0]['total_time'], ( 9 * 3600 ) );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate'] );
		$this->assertEquals( 0, $udt_arr[$date_epoch][0]['hourly_rate_with_burden'] );

		//Regular Time
		$this->assertEquals( 20, $udt_arr[$date_epoch][1]['object_type_id'] );              //20=Regular
		$this->assertEquals( $udt_arr[$date_epoch][1]['pay_code_id'], $this->policy_ids['pay_code'][100] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['total_time'], ( 9 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][1]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][1]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Premium
		$this->assertEquals( 40, $udt_arr[$date_epoch][2]['object_type_id'] );              //40=Premium
		$this->assertEquals( $udt_arr[$date_epoch][2]['pay_code_id'], $policy_ids['pay_code'][0] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['total_time'], ( 9 * 3600 ) );
		$this->assertEquals( 21.50, $udt_arr[$date_epoch][2]['hourly_rate'] );
		$this->assertEquals( $udt_arr[$date_epoch][2]['hourly_rate_with_burden'], ( 21.50 * 1.135 ) );

		//Make sure no other hours
		$this->assertCount( 3, $udt_arr[$date_epoch] );

		return true;
	}
}

?>